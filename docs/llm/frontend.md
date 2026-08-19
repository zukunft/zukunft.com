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

The type preload of this bootstrap (and of `application::start_api`) uses the
**cached types json**: `type_lists::load_cached` fills all type lists with one
read of the `db_cache` `types` entry — the same api message that
`ui_config::write_db_cache` stores for the frontend — and only falls back to
one select per type list when the entry is missing or outdated
(`db_cache::is_outdated`). The fill goes through `api_mapper(..., trusted: true)`:
the `$trusted` flag marks json from the own database and also restores the
fields an api message of a frontend must never change (the `code_id`, the verb
usage/impact). The pod switch for the types cache is a config value and the
config loads *after* the types, so the bootstrap calls
`type_lists::reload_if_cache_denied` once the config is known. A caller that
needs guaranteed fresh types (e.g. `ui_config::reload`, the test bootstrap)
keeps using `load_type_lists` / `type_lists::load`.

## `frontend.php` boots the html frontend, `application.php` the api backend

The target is a frontend and a backend that are complete and independent of each
other and talk only over the API (see `architecture.md`). The request bootstrap is
therefore split in two, and each side uses only its own:

- `web/frontend.php` — the **pure html php frontend**: `http/view.php`,
  `http/about.php`, `http/setup.php` call `frontend::start()` / `frontend::end()`.
- `cfg/application.php` — the **backend**, i.e. the api calls: the `api/**`
  scripts call `application::start_api()` / `start_api_core()` / `end_api()`.

Never call `application::start()` from a `web/` entry point, and never call
`frontend::start()` from an api script.

Because both bootstrap a request they **overlap today** — session start and
hardening, TLS enforcement, the session token, opening the database and the
timing switches exist in both files. That overlap is the cost of the unfinished
split (it disappears once the frontend stops opening a database), not a signal to
merge the two.

Watch out when changing the request lifecycle: the two classes have same-named
methods with **different signatures** —

```php
application::start(string $code_name, bool $echo_env = false, bool $restart = false)
frontend::start(string $code_name, Message $msg = new Message(), array $url_arr = [])
```

so a new guard, debug message or timing switch added to one silently misses the
other. Until the split is done, apply such a change to **both**.

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

## Behaviour shared by word and triple belongs on the phrase

`web/word/word.php` and `web/word/triple.php` are siblings — both extend
`sandbox_code_id` — so a method written on one is simply missing on the other.
That is fine for what is genuinely word-specific (a plural, a type selector), but
relations are not: "the parents of", "the children of", "the other phrases that
share an `is a` parent with this one" describe a **phrase**, and a triple is as
much a phrase as a word is.

So the logic lives once on `web/phrase/phrase.php`, and `word` and `triple` each
keep a thin delegate:

```php
// web/word/triple.php — same three lines in web/word/word.php
function similar(user_message $msg, ?phrase_list $phr_lst = null): phrase_list
{
    return $this->phrase()->similar($msg, $phr_lst);
}
```

The delegates matter: they keep every existing `$wrd->similar(…)` call site
working and let a caller stay typed on the concrete class. Do **not** push the
method up into `sandbox_code_id` instead — views, formulas, components and
sources extend it too and have no `phrase()`. Do **not** reach for a trait
either; the project uses none, and one file of shared methods would drift out of
the class it belongs to.

The practical trigger is a fixture that cannot change class: if a page-object
factory is stuck returning a `word` because "the frontend triple has no
`similar()`", the missing method is the bug, not the fixture. Renderers are
already prepared for this — `ui_list::parents_of_word()` and friends take
`word|db_object`, and `system_form::title_phrase()` dispatches on the class — so
the only thing to add is the delegate.

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

## A page never fills the screen — the messages below it must stay visible

The user messages are rendered **below the view** (`<!--usr_msg-->` in the page
skeleton), so a page that fills the whole screen hides them: the user acts, the
page reports the result, and the report is one scroll below the fold where
nobody looks. A page must therefore stay short enough that the message area is
visible without scrolling.

The consequence for every list renderer: **each list is limited on its own**, not
only the page as a whole.

- A limit applies **per group**, not just to the ungrouped rest. A grouped list
  (`value_list::list_most_relevant`: time groups, phrase groups, then the rest by
  impact) shortens every single group with its own `… and n more`, because one
  phrase with a hundred values would otherwise fill the screen even though the
  final section is limited. `group_block()` is the pattern to copy.
- The limit is the configured one (`config.yaml`, see the section above), never a
  literal, so an admin can tune how much a page shows.
- The same holds for a new list component: if it can grow with the data, it needs
  a limit and a tail, even when it sits next to lists that already have one.

When you add or change a page renderer, ask what the page looks like for the
object with the *most* data, not for the test fixture.

## Short, more and all — the three versions of a list

Every list a page shows exists in three versions. Which one is rendered depends
on how often the user has asked for more:

| version | entries | tail |
|---|---|---|
| **short** (default) | 5 | `… and n more` → the more version |
| **more** (after one click) | 20 | `… and n more` → the all version |
| **all** (after the second click) | the whole list, paged | prev / next buttons |

- `n` is the number of **extra** items, not the total.
- Both counts are **configuration, never literals**: 5 is `select: initial:
  entries` and 20 is `select: more: entries` in `config.yaml`. In `web/` they are
  read through the request cache, `$ui_sys->cfg->get_by([...], $msg, <fallback
  const>)` — never `new config()` and never an inline `5` / `20`.
  `value_list::configured_limit()` is the pattern to copy: a named private helper
  that asks the cache and falls back to a const when the config is not loaded.
- The **all** version is paged (prev / next) and serves its rows from the screen
  cache. It is offered only while the list stays below the *max frontend list
  size* of 2'000; above that the user narrows the selection instead, because a
  page with more rows than that is neither readable nor worth caching.
  `change_log_list::tr_page_nav()` already builds that footer row from
  `icons::PAGE_BACK` / `icons::PAGE_FORWARD` — the icons are there, the
  navigation still has to be wired.
- Which version is shown is url state like every other frontend state — no
  JavaScript toggles it (see "Pure HTML, no JavaScript").
- The version does not change the order: the same deterministic key sorts all
  three, so the first 5 of the short list are the first 5 of the all list (see
  "Always sort lists before rendering them").

`config.yaml` carries `select: initial: entries` and `select: more: entries`
today; the *max frontend list size* key for the 2'000 bound is still missing and
has to be added together with the paged version.

## "… more" is always a link that shows more

When a list is truncated to its configured limit, the "… and n more" tail is a
**link to the next version of the list** (short → more → all) — never dead text.
A count that cannot be clicked tells the user something exists and gives no way
to see it.

- The values list tail links to the `phrase_values` view of the page phrase
  (`value_list::more_tail`): `url_new(views::PHRASE_VALUES_ID, $phr->id())`.
- The related-phrases "…" in a page title links to the `word_related` view
  (`phrase_list.php`, `views::WORD_RELATED_ID`) — the same pattern.
- Build the tail text from the message ids (`msg_id::THREE_POINTS`,
  `msg_id::AND_MORE_BEFORE`, `msg_id::MORE`), never from an inline
  `' ... and ' . $n . ' more'` literal, so the text is translated.

Only when no target object is known that could select the full list (e.g. the
unit list, which does not know the page phrase) may the tail stay plain text —
and that is a gap to close by threading the context, not a licence to skip the
link. When adding a new truncated list, pick (or create) the "show all" view
first, then wire the tail to it.
