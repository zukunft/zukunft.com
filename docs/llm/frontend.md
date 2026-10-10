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
- A popup menu is the html `details` element, which keeps the open state itself:
  `html_base::popup_menu` wraps the always shown label in the `summary` and the
  entries in a list, and the `.<name>-menu` rules in `style_html.css` float the
  list below the label (the header menus and the "…" column menu of a value table).
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

- The values table tail calls the **same page** with the next list size
  (`value_list::more_url`): the url var `url_var::DISPLAY_LIST_SIZE` (`dls`,
  human `display_list_size`) names the rows shown, `url_var::DISPLAY_LIST_PAGE`
  (`dlp`, `display_list_page`) the page of a list longer than that size, and the
  tail raises the size to `select: more: entries` and then to all rows
  (`value_list::next_row_limit`). Both vars are `url_var::PAGE_VARS`, so a back
  link returns to the list as the user has expanded it. Only a table whose page is
  not known falls back to the `phrase_values` view of the page phrase
  (`value_list::more_tail`).
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

## The simple table and its "…" menu

A value table has a simple and a full version, like a list has a short and a
more version. The simple version shows the columns of the mayor tier only, one
unit per column — the unit that most numbers of the column have, so that one
bigger number in another unit cannot hide the unit of every other row
(`value_list::split_by_unit`) — and each cell the number without its probability range; the
full version shows every tier, every unit and the range behind each number. A
further unit of a column states the same measure a second way, so it is a minor
column at most (`value_list::column_tier`), and it is shown only if it has a
number in every row of its first unit, e.g. the potential loss of a problem in
trillion EUR, in percent of the GDP and in percent of the happy time points; a
unit that only some rows have, e.g. the potential loss in htp of global warming
alone, is left to the full version (`value_list::unit_column_complete`).

A version that leaves out a tier shows exactly the columns of its tiers, never a
column of a lower tier, and at most the configured number of columns
(`select: columns: entries` in `config.yaml`, counting the row name column); if
the definitions put more columns into the shown tiers, the last columns of the
lowest tier are left to the full version. So which version shows a column is
decided by its tier in the definitions: e.g. on the start page the main version
shows the reason and its loss, the minor version adds the loss in percent, the
initial effort and the loss reduction, and the reward ratio, a marginal column,
is shown by the full version only. The start page opens with the simple version. The last header cell of every table,
the full one included, is the "…" menu (`value_list::columns_menu`, built with
`html_base::popup_menu`), which lets the reader pick the version directly
instead of stepping through them — and from the full table it is the only way
back to the fewer columns.

The menu sets `url_var::DISPLAY_LIST_COLUMNS` (`dlc`, human
`display_list_columns`). That number says how many tiers of
`triples::SYSTEM_COLUMN_TIERS` (mayor, main, minor, marginal) the table leaves
out from the bottom, so it names one entry per tier
(`value_list::COLUMN_TIER_NAMES`): `COLUMN_TIERS_EX_MAIN` (3, "mayor") →
`COLUMN_TIERS_EX_MINOR` (2, "main") → `COLUMN_TIERS_EX_MARGINAL` (1, "minor")
→ `COLUMN_TIERS_ALL` (0, "all"). The entry is named by the tier alone, because
the menu header says what is selected. Each entry exists twice, once with
`url_var::DISPLAY_LIST_RANGE` (`dlr`, `display_list_range`) off and once on
(`msg_id::TABLE_COLUMNS_WITH_RANGE`), so every version of the table is one
click away. Both vars are `url_var::PAGE_VARS` and part of the page cache key,
so every selection is a cached page of its own like the more version of a list.
A page that is not known renders the "…" as plain text, the same gap as for the
list tail.

A tier also says on which screen a column is shown: the css class of
`value_list::column_style` hides a main column on a small screen
(`styles::COL_MAIN`), a minor column below a wide screen (`COL_MINOR`) and a
marginal column below the widest one (`COL_MARGINAL`).

The reason of a problem is a phrase column like the solution: `column reason`
puts the word `reason` into the main tier and `<reason> is a reason` links a
reason to it, e.g. `climate gas emissions` for global warming. The loss that a
reason causes is a value column of its own, the triple `potential loss of
reason` in the main tier: its values carry the problem, the word `reason`, the
reason and `potential loss`, so `value_list::column_parts` takes them before the
`potential loss` column of the problem instead of merging both numbers into one
cell. The reason is the next main column after the problem and the solution
follows the reason, so both reason columns stand between the loss and the
solution of the problem. The main tier holds only these two columns; every
other defined column below the mayor tier of the start page is minor or marginal.

A phrase cell names one phrase: if the values of a row name more than one
phrase for the column, e.g. several reasons of global warming, the cell shows
the phrase of the value with the biggest number (`value_list::shown_phrases`),
followed by `, ...`. The `, ...` links to the table page (`views::TABLE_ID`) of the triple
`<column phrase> of <row phrase>` if the definitions name one, e.g. `global
warming reason` with `<reason> is a global warming reason` for each reason,
else to the page of the row phrase (`value_list::phrase_cell`). The `, ...` is
shown too if that triple links more phrases than the one shown, e.g. `global
warming solution` with a second solution whose gain belongs to a reason and is
therefore no number of the row. The value cells
of the row show only the numbers of the phrase shown, e.g. the gain of the
solution shown and not also the gain of another one, so that no number of a
hidden phrase stands beside the phrase shown. A value that names the phrases
of two phrase columns, e.g. the gain of the solution of a reason, is about the
first phrase and not about the row, so the row leaves it to the table of that
phrase, e.g. the reasons page, and it changes no column of the table
(`value_list::without_values_of_two_phrase_columns`). A linked phrase that is a
defined column itself, e.g. `potential gain` of the chart triple `potential
gain and potential loss`, names a column and not a row phrase, so it does not count.

The table of such a triple has rows named by the linked phrases, e.g. the
reasons: its phrase has no value of its own, so the table component loads the
values of the linked phrases like the start page (`ui_list::child_values`), and
the phrase that names the rows, e.g. `reason`, heads the row column and is no
phrase column of that table. A defined column built from it, e.g. `potential
loss of reason`, is the column of its other part there, e.g. the mayor column
`potential loss`, because the row already names the reason
(`value_list::without_row_column`). The table page is a phrase page like the
calculator (`views::PHRASE_MASKS_IDS`), so it is called with the phrase id,
e.g. `-1631` for the triple `global warming reason`.

A row of the simple table whose numbers are all in a column of the full version
would be empty, e.g. the reward ratio row of a problem, so the ranking of the
start page is built with the `value_rows_only` option of
`value_list::table_by_related_columns`, which drops a row without a number in a
shown column before the row cut, so that such a row uses up none of the shown
rows. A phrase column names a phrase of the row and no number, so a row with
only phrase cells is dropped as well. Every other table keeps such a row, so
the option is a default of the component (`ui_list::start_list`) and not a rule
of the table.

## A table as a chart is another rendering of the same rows

`value_list::table_to_svg` draws the table of `table_by_related_columns` as an
inline svg. Both build the same `table_model` (`value_list::table_model`: the
rows, columns, units and ranges before anything is rendered), so a chart never
shows a row or a number that the table does not have, and the parameters of the
chart are the ones of the table. `chart_types` says only how the rows are drawn
(`web/value/table_chart.php`):

- `RANGE_BARS` — one row per table row, the biggest number on top, with a dot
  for the number and a bar for its probability range; a log scale is used when
  the numbers differ by more than `table_chart::LOG_RATIO`, so the small
  problems stay visible beside the big ones.
- `SCATTER` — one numbered point per table row placed by two numbers of the
  row, explained by a legend of the phrase column (e.g. the solution) and the
  row phrase.

The plotted columns are named by `$chart_cols` with the names that also select
a table column (the column phrase, its header name or a part of a triple
column, `table_model::col_names`), the y axis first; without names the first
value columns are plotted. A name that selects no column is reported via
`msg_id::CHART_COLUMN_NOT_FOUND` and nothing is drawn, never a chart of another
column. Every row carries the numbers of every value column as its tooltip in
the format of the table cell (`5.5a trillion EUR (2.2 – 13.75)`), so the chart
tells the reader the same as the full table.

Which charts a table offers is data, defined in `solution_prio.json` like the
column tiers: a chart type word carries the `chart_types` value as its
`code_id` (`range bars` → `range_bars`, `scatter plot` → `scatter`), a chart is
the triple `<chart type> of <column>` (`range bars of potential loss`) or, for
two columns, `<chart type> of <column> and <column>` with the y column first
(`scatter plot of potential gain and initial effort`), and the chart is
assigned to a chart type tier with `<chart> can be <tier>`: the charts of
`triples::SYSTEM_CHART_TYPE_DEFAULT` are shown beside each other when the
reader asks to see the table as a chart, the charts of
`SYSTEM_CHART_TYPE_ALTERNATIVE` can be selected instead (not wired yet).
A table that shows the mayor columns only, e.g. the initial start page, shows
the charts of `SYSTEM_CHART_TYPE_MAYOR` instead of the default ones, because a
default chart may plot a column that this table does not show: the start page
shows the leverage plot (`leverage plot` → `leverage_plot`) of the potential
gain against the potential loss, one labelled point per problem with the
quadrants of the big and small numbers and the dashed lines of an equal gain
per loss, like `global-problems-top4_scatter.svg`; without a mayor chart the
default charts are shown. These definitions are in `start_page_charts.json`,
which `files::SYSTEM_DATA_APPENDED_FILES` imports as the last step of the db
setup, so that no pinned database id moves.
`phrase_list::chart_definitions` reads the default charts out of the cached
definitions (`load_chart_definitions`, `CHART_LEVELS` relation levels from the
tier keyword: the tiers and the chart assignments) and
`ui_list::table_with_related_columns` draws them with `table_to_svg` in a
`styles::CHART_ROW` below or instead of the table. The chart triple is the from
side of its assignment and not a list entry, so the frontend reads the chart
type word and the column pair nested in it; the api (`load_linked_sides`) loads
the sides of a nested triple completely, a nested triple with its own sides and
a nested word with its code id, because the triple read names a word without it.

The rows of a table follow the impact unless the url asks for another order:
`url_var::DISPLAY_LIST_ORDER` (`dlo`, human `display_list_order`) names the
phrase id of a column and the condition (`table_orders`), e.g.
`dlo=-128.numeric_desc` for the biggest potential loss first;
`DISPLAY_LIST_ORDER_SUB` (`dlo2`) sorts the rows that the prime order leaves
equal and `DISPLAY_LIST_ORDER_SUB_SUB` (`dlo3`) the rows that both leave equal;
the rows that every order leaves equal keep the impact order. A condition is
`numeric` (the number of the cell), `alpha` (the name of the phrase or the text
of a text value) or `alpha_parent` (the names of the parents within the related
phrases before the name, so the rows are grouped by their parent), each as
`_asc` or `_desc`; a row without a key in the column is last for both
directions. The row column is named by its head phrase, a phrase column by its
phrase and a value column by its phrase. `value_list::row_orders` reads the
url, `table_model::sort_rows` sorts before the rows are cut, so the charts draw
the same order, the three vars are page vars and part of the page cache key, and
an order that names no column of the table or an unknown condition is reported
as the warning `TABLE_ORDER_UNKNOWN`, because the table is still shown with the
impact order. Each column header ends with the `icons::SORT_UP` and
`SORT_DOWN` links (`styles::SORT_ICON`, the shown order `SORT_ACTIVE`) that
`value_list::sort_icons` builds: numeric for a value column, alpha for the row
and a phrase column, and `order_url` moves the orders shown one step down,
so a click on another column keeps the shown order as the sub order.

The default order of a table is data too, defined in `solution_prio.json` like
the charts: a condition word carries the `table_orders` value as its `code_id`
(`numeric descending` → `numeric_desc`), an order is the triple
`<condition> of <column>` (`numeric descending of potential loss`) and is
assigned to the default tier with `<order> can be <tier>`
(`triples::SYSTEM_SORT_ORDER_DEFAULT`); the first default order is the prime
order, the next ones the sub orders. `phrase_list::sort_definitions` reads them
out of the cached definitions (`load_sort_definitions`, `SORT_LEVELS`, added to
the request cache by `ui_list::add_sort_definitions` with the column
definitions) and `value_list::row_orders` uses them only if the url names no
order, so `view.php?m=1` shows the biggest potential loss on top and a url with
an order wins. The orders shown are kept in `table_model::orders`, which marks
the active sort icon and seeds the links of the other icons.

The "…" menu of the table has two sub headers (`styles::MENU_HEADER`, not
bold): "columns and values" above the column tier entries and "as" above the
entries `table`, `chart` and `table + chart` (`table_forms`), which set
`url_var::DISPLAY_LIST_AS` (`dla`, human `display_list_as`), a page var and part
of the page cache key like the other list vars. Shown as a chart only, the
table is replaced by its charts below a `styles::CHART_HEAD` line that keeps
the header of the table centred and puts the "…" menu in the top right corner
(`styles::CHART_CORNER`), because the menu is the only way back to the table.

## The value quality is a mark behind the number, never a phrase before it

A value quality phrase (phrase type `value_quality`, e.g. `assumed`) says how a
number has been found, not what it is about, so it is never named with the
phrases before the number: `sandbox_value::phrase_link_list`,
`value::links_and_measure` and the row names of `value_list` leave it out
(`phrase_list::value_quality_phrases`, `value_list::is_marker`). Instead
`sandbox_value::quality_mark` adds a grey superscript behind the number with a
translated tooltip:

| mark | when                                              | tooltip message                       |
|------|---------------------------------------------------|---------------------------------------|
| `a`  | the value carries `assumed`                       | `msg_id::QUALITY_MARK_ASSUMED`        |
| `-`  | a value names no source (a result never)          | `msg_id::QUALITY_MARK_NO_SOURCE`      |
| `+`  | the value carries `peer reviewed by quality journal` | `msg_id::QUALITY_MARK_PEER_REVIEWED` |

One number shows one mark, checked in this order: an assumed number has no source
on purpose, so its `a` says more than the `-`, and a missing source makes a peer
review claim unverifiable, so the `-` wins over the `+`. A result is calculated by
a formula and has no source to miss (`is_source_missing` is overwritten by the
value only). Every place that shows a number with its phrases calls the mark
right behind the number (`value_edit`, `name_link`, `links_and_measure`), so a
new display of a number reuses one of these instead of formatting the number
itself.
