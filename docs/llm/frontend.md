# Frontend (`web/`) conventions

Detail for the "Frontend" rules in `CLAUDE.md`.

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
