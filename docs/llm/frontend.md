# Frontend (`web/`) conventions

Detail for the "Frontend" rules in `CLAUDE.md`.

## Pure HTML, no JavaScript

The `web/` frontend renders **plain HTML and CSS only — no JavaScript**. Anything
interactive must work without a script: use native form posts, links, and CSS
state selectors (`:target`, `:checked`, `:hover`, `:focus-within`) instead of a
client-side handler.

- Tab switching is CSS `:target` keyed on the url fragment (`html_base::tab_box`
  renders one `.css-tab` section per tab; the `.css-tab:target` rules in
  `style_html.css` show the matched content **and** highlight its label, first tab
  default): a link to `…#changes` opens the "Changes" tab — no script needed.
- Never emit a `<script>` tag or an inline event handler (`onclick=…`, `data-toggle`
  for a JS plugin, …) from a `web/` renderer.

A separate JavaScript frontend (likely Vue.js or React) is planned for later as
its own app consuming the same api; that is a future, additional client and does
not relax this rule for the current server-rendered HTML frontend.

## Public properties + PHP 8.4 property hooks

In classes under `src/main/php/web/` (the HTML frontend layer), object
properties are declared `public`. Frontend objects are thin view-models
populated from the backend api json and consumed by renderers; trivial private
fields with one-line `get_x()`/`set_x()` only add boilerplate. Direct property
access (`$wrd->plural`) is the intended style.

When a property genuinely needs non-trivial set/get behaviour (validation, lazy
computation, normalisation), express it with **PHP 8.4 property hooks declared
inline on the property**, not separate methods. The hook keeps the custom
behaviour at the declaration, and callers still use `$obj->prop` /
`$obj->prop = …` — no second API to keep in sync.

- **Right** — public property with inline hooks for the non-standard part:
```php
public ?string $plural = null {
    get => $this->plural;
    set => $this->plural = trim($value);
}
```
- **Right** — plain public property when no custom logic is needed:
```php
public ?float $weight = null;
```
- **Wrong** — `private` field with hand-written accessors that add nothing:
```php
private ?string $plural = null;
public function get_plural(): ?string { return $this->plural; }
public function set_plural(?string $v): void { $this->plural = $v; }
```

Backend (`cfg/`) classes are **not** covered: they keep `private` fields and
explicit accessors because they enforce user-sandbox, log, and DB-write
invariants on every change. Apply the public-property + inline-hook rule only to
`web/`.

## Frontend / UI functions end with `_ui`

Any function that builds, returns, or operates on a **frontend (UI) object** ends
with the `_ui` suffix, so a reader can tell at the call site whether they get a
backend (`cfg/`) or frontend (`web/`) object without checking the return type.
This matters most in test factories where a backend and frontend variant of the
same fixture sit side by side.

- **Right**: `test_words::word_swiss_franc()` returns the backend `word`;
  `test_words::swiss_franc_ui()` returns the frontend `word_ui`
- **Wrong**: `word_swiss_franc_dsp()` for a frontend factory — `_dsp` is reserved
  for the display *class* suffix (`word_dsp`), not "returns a UI object"; use
  `_ui`

When a backend and frontend factory of the same fixture both exist, pair them as
`<name>()` (backend) and `<name>_ui()` (frontend) — `word_chf()` / `chf_ui()`,
`word_swiss_franc()` / `swiss_franc_ui()`. Older `*_dsp` helpers (`word_dsp()`,
`word_chf_dsp()`, `word_zh_dsp()`) predate this rule and should be renamed to the
`_ui` ending when next touched.

## Config values come from `$ui_sys->cfg`

Frontend code reads user config values (formatting, list limits, ...) only from
the request cache `$ui_sys->cfg`, never via `new config()`:

```php
global $ui_sys;
$limit = $ui_sys->cfg->get_by([words::ROW, words::LIMIT], def::FALLBACK_DB_PAGE_ROWS);
```

`http/view.php` creates and loads the cache once at request start;
`test_lib::ui_test_cache()` sets an empty one for unit tests, so the getters
return the shared defaults. A `config` constructed anywhere else is an *empty*
value list: `get_by()` silently returns the fallback instead of the user setting,
and the per-request load from the backend is bypassed. The rule is enforced by
`coding_rule_tests::php_web_config_from_cache_tests`.

## The frontend never accesses the database — load via the API

Code under `src/main/php/web/**` must not open or query the database. It never
declares `global $db_con`, never builds SQL (`sql_db` / `sql_creator`), and never
calls a backend (`cfg/`) model load function. Everything a frontend object needs
is requested from the backend through the API and mapped from the returned JSON:

```php
$data = array($url_var => $id);
$rest = new rest_call();
$json_body = $rest->api_get($class, $data);
$this->api_mapper($json_body);
```

Why: the frontend must stay pod-independent (it can render against a *remote*
backend pod over the API, not just the local database) and fully unit-testable
without a database — tests feed the dummy cache or a stored api-json fixture
instead of a live connection. A direct DB read also bypasses the api-version and
permission handling that the API layer applies.

This overlaps the allowed-globals rule (`web/` may read only `$ui_sys` / `$mtr`,
never `$db_con`; see `state-and-messages.md`) and is enforced by
`coding_rule_tests::php_web_only_allowed_globals_tests`.

The single current exception is `web/frontend.php`, whose **deprecated**
direct-DB bootstrap (`start` / `open_db` / `load_cache`) still opens a connection
and is therefore the one file excluded from the coded check. It is being migrated
to the API (`TODO Prio 1` in that test); once done, the exception is removed and
no `web/` file touches the database at all.

## Paired HTML tags go through an `html_base` function that uses a tag const

Any element that has an opening **and** closing tag (`<form>…</form>`,
`<div>…</div>`, `<table>…</table>`, `<label>…</label>`, …) is emitted by a
function on `html_base`, never by writing the literal tags inline at the call
site. The function builds both tags from a **tag constant** (`self::FORM`,
`self::DIV`, …), so the open and close can never drift apart and a renamed tag
changes in one place.

```php
// right — the wrapper owns both tags and builds them from the const
function fr(string $row_text): string
{
    return '<' . self::DIV . ' ' . self::CLASS_HTML . '="' . rest_ctrl::CLASS_FORM_ROW . '">'
        . $row_text . '</' . self::DIV . '>';
}
// call site:
$html->fr($detail_fields);

// wrong — literal tags inline; the open/close can get separated and left unbalanced
$result .= '<form action="/http/view.php">' . $fields;   // … and a '</form>' somewhere far away
```

Why: emitting a lone `<form>`/`<div>` and its matching close from different
places (or different component arms) is exactly how a page ends up with an
unclosed element — the `all_component_types` catalog hit this because layout
components rendered a half tag each. A single wrapper that returns the complete
element (or a matched `*_start()` / `*_end()` pair when the body must stream in
between, like `form_start()` / `form_end()`) keeps every page balanced. Add the
tag const first if one does not exist yet; never inline a raw `<tag>` string.

This is the markup-level case of the always-on "no magic literals" /
"icons come from constants" rules — the tag name is the literal, the wrapper is
the single place it lives.

## Form field `name` is the url var, `id` is the human label

Every HTML input rendered by `html_base::input()` (and therefore by
`form_field()`, `form_hidden()`, `form_back()`, `form_confirm()`, …) carries two
distinct attributes with two distinct jobs — never mix them up:

- **`name`** is the **submitted key**, so it must be the **url var** (`url_var::*`
  passed as `$url_id`, e.g. `m`, `k`, `o`, `lp`, `9`, `z`). The browser posts
  `name=value` pairs; those keys are what `url_mapper::url_to_standard()` reads.
- **`id`** is **user-readable** and is derived from the translated label
  (`$mtr->txt($msg_id)`, lowercased, e.g. `mask`, `name`, `description`). It only
  identifies the element on the page and pairs with the `<label for>`.

```html
<!-- right -->
<input class="form-control" type="hidden" name="m" id="mask" value="3">
<!-- wrong: the label text became the submit key -> url mapper can't map "mask" -->
<input class="form-control" type="hidden" name="mask" id="m" value="3">
```

Using the translated label as `name` is the classic break: a label like `Name`
or `mask` is not a url var, so the submitted URL produces
`url mapper for "Name" is missing` / `url key "mask_id" is missing` and the save
action never reaches the right view. The label belongs in `id` (and the visible
`<label>`), never in `name`.

Keep the label/input pair consistent: `form_field()` calls `label($name)` with an
empty `for`, so `label()` derives `for=strtolower($name)`, which equals the input
`id` (`strtolower($mtr->txt($msg_id))`). If you build a label and input by hand,
use the same lowercased label text for both `for` and `id`.

The matching dropdowns/selectors (share `s`, protection `sp`, phrase type `py`,
view `d`) already emit the url var as `name` directly — follow that when adding a
new form element.

## Always sort lists before rendering them

Every list shown on a frontend page must be sorted by a **deterministic key**
before it is turned into HTML. The API and the database return rows in no
guaranteed order, so an unsorted list renders in whatever order the rows happen
to arrive — which differs between pods, query plans, and runs. That makes the
HTML snapshot tests (`object_pages/*.html`, `views_by_*/*.html`) volatile: they
pass on one run and fail on the next for no real change, and a genuine
regression hides in the noise.

Pick the key that matches the list's purpose and is reproducible:

- **impact** (system-calculated relevance) for "most relevant first" lists, e.g.
  the related phrases, values, and formulas on the default word page —
  `phrase_list::sort_by_impact()`, `value_list::sort_by_impact()`; ties must
  still resolve deterministically, so fall back to name or id when impacts are
  equal.
- **name** for alphabetical pick lists and selectors.
- **id** (or another stable unique field) as the last-resort tie-breaker so the
  order is total, never partial.

```php
// right: sort, then render
$val_lst->sort_by_impact();
return $val_lst->list($phr_lst);

// wrong: render whatever order the api returned
return $val_lst->list($phr_lst);
```

This applies to every renderer in `web/` that outputs more than one row
(tables, link lists, option lists, related-object lists). When you add a new
list-rendering function, sort inside it (or require the caller to pass an
already-sorted list and assert it) — do not rely on the upstream load order.
A new `object_pages/<name>.html` fragment that reorders between runs is the
signal that a sort is missing.
