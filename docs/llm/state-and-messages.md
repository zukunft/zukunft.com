# State, globals & user messages

Detail for the "State & messages" and "Unit-testability" rules in `CLAUDE.md`.

## Allowed global variables

The project uses a small fixed set of globals (see also `docs/todo.md`). No
others may be introduced.

| Global    | Scope    | Purpose                                                                                                                                                                 |
|-----------|----------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$sys`    | backend  | Execution times, type cache, system config (rarely changes, not user-specific)                                                                                          |
| `$db_con` | backend  | Database connection                                                                                                                                                     |
| `$cfg`    | backend  | User-specific configuration numbers (changes more often than types)                                                                                                     |
| `$cac`    | backend  | Backend cache of user-specific `data_object`                                                                                                                            |
| `$mtr`    | backend  | the message translatro is used in the backend only to get the english text of a message_id                                                                              |
| `$ui_sys` | frontend | Frontend cache including the session user and the user config                                                                                                           |
| `$mtr`    | frontend | Message translation — created **once** in `http/view.php`; language priority: (1) `url_var::LANGUAGE` URL param, (2) session var, (3) user config (`$cfg`), (4) default |
| `$t`      | tests    | Base test object (assert + cleanup helpers)                                                                                                                             |
| `$t_sys`  | tests    | Error counting and execution times for tests                                                                                                                            |
| `$debug`  | any      | Activates additional logging levels                                                                                                                                     |

Code in the wrong scope must not read these globals — frontend renderers
(`src/main/php/web/**`) may not touch `$sys`/`$db_con`/`$cfg`/`$cac`; backend
code (`src/main/php/cfg/**`) may not touch `$ui_sys`/`$mtr`; tests own
`$t`/`$t_sys`, production code does not.

## Unit-testability

Every function must be fully unit-testable:

- **No PHP superglobals inside functions**: never read `$_GET`, `$_POST`,
  `$_SESSION`, `$_SERVER`, or any superglobal inside a method or standalone
  function. The long-term target is a single HTTP entry point (`http/view.php`)
  that reads superglobals once and passes them down as explicit parameters.
- **Allowed globals inside functions**: the fixed globals above may be used,
  because tests initialise the same globals at start-up, making behaviour
  reproducible without parameter injection.
- **Never pass an allowed global as a parameter**: since the fixed globals are
  reachable via `global $x` everywhere (and tests initialise them), a function
  that needs `$sys`/`$db_con`/`$cac`/… declares `global $sys;` in its body — it
  must not accept the global as a function parameter. Threading an allowed global
  through signatures is redundant indirection that forces every caller to
  re-supply what is already globally available.
- **No other hidden globals**: any global not in the list above is passed as an
  explicit parameter.

Rationale: a function reaching outside the allowed globals cannot be called in a
test without replicating the full request environment. The fixed set is small
enough that every test runner initialises it once and all functions stay
independently testable.

## Pass mutable state as explicit parameters

Any object a function may update is declared as an explicit parameter; the caller
owns it and observes the change after the call. Use pass-by-reference (`&`) only
when the function must replace the variable itself (reassigning
`$usr_backend = $db_usr`); update-in-place via method calls
(`$usr_ui->set_from_json(...)`) does not need `&` because objects are passed by
handle.

- **Right**: `function action_login(..., user_backend &$usr_backend, user_ui &$usr_ui, ...)`
- **Wrong**: reading or writing a global to propagate the change

### Don't pass a stateless helper — instantiate it locally

The inverse rule: if a parameter object would arrive **freshly constructed** at
every call and the function uses it only as a stateless helper, **drop the
parameter and create a fresh instance inside the function**.

A stateless helper: has no caller-unique properties (no buffered output, user,
language, per-request config), is cheap to construct (`new html_base()` is one
allocation), and would be initialised the same way at every call site. When all
three hold, it belongs inside the function.

- **Wrong** — `html_base` threaded through a chain even though no caller pre-configures it:

```php
function category_html(html_base $html): string { … $html->url_new(views::PHRASE_ID, $phr->id()) … }
function category_subtitle(db_object $dbo, html_base $html): string { return $related->category_html($html); }
function title_of_named_with_edit_link(db_object $dbo): string {
    $html = new html_base();
    $subtitle = $this->category_subtitle($dbo, $html);
    …
}
```

- **Right** — created where used; one less parameter at every step:

```php
function category_html(): string { $html = new html_base(); … }
function category_subtitle(db_object $dbo): string { return $related->category_html(); }
function title_of_named_with_edit_link(db_object $dbo): string {
    $subtitle = $this->category_subtitle($dbo);
    …
}
```

Counter-example — when the helper **does** carry per-call state (`data_object
$dto` is the request-scoped import cache, `user_message $msg` accumulates
per-request errors, `sql_creator $sc` has caller-set database state), the
previous rule wins: keep it as a parameter.

## User-message accumulation

`$msg` is created **once** in `http/view.php` as `new user_message()` — the
single collector for every message shown during a request. It is passed as an
explicit parameter (named `$msg`) to every function that may report a warning,
error, or info notice.

- **Right**: `function url_to_action(array $url_array, user $usr_dsp, user_message $msg, ...): array`
- **Wrong**: creating `new user_message()` inside a helper and returning/echoing the message directly

### Never overwrite or reset the accumulated messages

A function receiving `$msg` may only **add** to it — never replace, clear, reset,
or re-create it, which silently discards earlier warnings/errors.

- **Right**: `$msg->add(msg_id::SOME_CASE, []);` — append only
- **Right**: `$msg->merge($sub_msg);` — fold a local sub-message back in
- **Right**: `$sub_msg = new user_message();` inside a function if later merged
- **Wrong**: `$msg = new user_message();` inside a function that received `$msg`
- **Wrong**: `$msg->reset();` on a parameter object

**Why**: every function shares the single instance, and `is_ok()` drives caller
control flow (the import loop only caches a triple when `import_mapper()` returns
`$usr_msg->is_ok()`). Overwriting/resetting makes earlier errors vanish and the
`is_ok()` signal wrong — which previously caused an import to silently drop every
object after the first failure. When you need a throw-away buffer, create a
**separate local** `user_message` and `merge()` the relevant part back.

### All user-facing messages use a translatable msg_id

Every message added to `$msg` goes via `$msg->add(msg_id::SOME_CASE, [])`, never
`add_message(string)` or `add_message_text(string)` — the plain-string methods
bypass translation and break serialisation, so the message won't survive the
`api_array()` round-trip to the frontend notification bar.

- **Right**: `$msg->add(msg_id::SIGNUP_ERR_NAME_EXISTS, []);`
- **Wrong**: `$msg->add_message($mtr->txt(msg_id::SIGNUP_ERR_NAME_EXISTS));`
- **Wrong**: `$msg->add_message_text('User name already exists');`

Every new user-visible string needs:

1. A `case` in `src/main/php/shared/enum/messages.php`
2. An English entry in `src/main/resources/translations/en.yaml`
3. A German entry in `src/main/resources/translations/de.yaml` (and any other active locale)

### Translate db field / table / action / json field names at display time

A database field, table or change-action name, or a json field name, shown to
the user is never displayed as its raw `code_id` / field key; it is translated
through the matching `Translator` helper, which prefixes the `code_id` and looks
up the message id:

- `$mtr->text_db_field($code_id)` — field name (`change_fields.csv` → `system_db_field_*`)
- `$mtr->text_db_table($code_id)` — table name (`change_tables.csv` → `system_db_table_*`)
- `$mtr->text_db_action($code_id)` — change action (`change_actions.csv` → `system_db_action_*`)
- `$mtr->text_json_field($json_field)` — json field name (`json_fields.php`); maps the json field to its db field via `json_fields::json_field_to_db_field` and reuses the db field translation

**Call the helper as late as possible — at the point of display, not earlier.**
Pass and store the raw `code_id` / json field key through the model, api and url
layers; only the final HTML/text renderer turns it into a localized string.
Translating early freezes one language into data that is cached, serialised and
re-shown to users in other languages.

- **Right**: `$html .= $mtr->text_db_field($fld_code_id);` in the renderer
- **Wrong**: storing `$mtr->text_db_field($fld_code_id)` on the object, then
  rendering that pre-translated string later

Each name still needs its `messages.php` case plus en/de translations (see
above); the helper only resolves the `code_id` to that message id. A json field
without its own message id falls back to its db field translation (identity map),
so most json fields need no extra entry.

## Back-navigation parameter convention

Back navigation (where to redirect after an action) is encoded as
**`'9'`-prefixed URL parameters**, never a standalone `url_var::BACK` parameter.

- **Right**: `?9m=2&9id=5&9z=0` — each key prefixed with `'9'` (`url_var::BACK`);
  `html_base::back_url_part()` builds it, `html_base::url_par_from_back_part()`
  strips the prefix on receipt
- **Wrong**: `?9=http%3A%2F%2F...` — don't use `url_var::BACK` as a standalone
  field carrying a full URL; don't emit `form_hidden(url_var::BACK, $someUrl)`

`url_var::BACK = '9'` is a **prefix character**, not a parameter name. Legacy
code reading `$url_array[url_var::BACK]` directly must migrate to the
prefixed-key pattern.

## Edit-view baseline parameter convention (concurrent-edit protection)

An edit view must carry, alongside each editable field, the **database value
that field had when the view was opened**, encoded as a **`'8'`-prefixed URL
parameter** (`url_var::PRE`). This baseline is what lets a save detect the
*real* user change requests instead of blindly writing back the whole form.

- **Right**: an edit view for word `259` emits both the live field and its
  opening value, e.g. `?id=259&Name=USD&8Name=USD&Description=new&8Description=old`
  — each `'8'`-prefixed key (`url_var::PRE`) is the value shown when the mask
  was rendered
- **Wrong**: saving every submitted field unconditionally — this overwrites
  fields the user never touched, clobbering a concurrent change another user
  made while the edit view was open

On save, compare each submitted field against its `'8'`-prefixed baseline and
**write only the fields that actually differ from the baseline**. A field the
user did not change is left as it currently stands in the database, even if a
second user changed it in the meantime. This is optimistic-concurrency / lost-
update protection at field granularity.

`url_var::PRE = '8'` is a **prefix character**, not a parameter name — the same
prefix mechanism as `url_var::BACK = '9'`.

Worked example — two users editing the same phrase:

1. `user_a` opens the edit view (baseline captured as `'8'`-prefixed params)
2. `user_b` opens the edit view and changes the phrase type
3. `user_b` presses save; the changed phrase type is written to the database
4. `user_a` changes the description and presses save
5. `user_a`'s submitted values are diffed against the `'8'`-baseline from step 1;
   only the description differs, so only the description is updated
6. the phrase type is left as `user_b` set it — `user_a`'s save does not revert it
