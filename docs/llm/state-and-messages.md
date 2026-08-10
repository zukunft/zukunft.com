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
| `$debug`  | any      | Per-request debug level from the `debug` URL param; activates additional logging (dev only) — see `docs/llm/debugging.md`                                               |

Code in the wrong scope must not read these globals — frontend renderers
(`src/main/php/web/**`) may not touch `$sys`/`$db_con`/`$cfg`/`$cac`; backend
code (`src/main/php/cfg/**`) may not touch `$ui_sys`/`$mtr`; tests own
`$t`/`$t_sys`, production code does not.

`$usr` is deliberately **not** in the list: the requesting user changes per
request, so it is always an explicit parameter — the session user comes from
`$ui_sys` in the frontend, everything below receives it from its caller. A test
takes it from the test environment (`$t->usr1`, `$t->usr2`, …), see
[testing.md](testing.md#the-user-of-a-test-comes-from-the-test-environment--never-global-usr).

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
error, or info notice — and, because it also carries the requesting user (see
the next section), to every function that needs to know who is asking.

- **Right**: `function url_to_action(array $url_array, user_message $msg, ...): array`
- **Wrong**: creating `new user_message()` inside a helper and returning/echoing the message directly

### Created once at the entry point — every other creation is a commented exception

Outside tests, a `user_message` is created **once per request** and only by the
entry point that answers it: `http/view.php` and the other `http/` scripts for
the frontend, `api/*/index.php` for the backend. Everything below receives that
one instance as `$msg`.

A creation below an entry point is therefore an **exception**, and every
exception carries a comment directly above it (or trailing on the same line)
saying why a local message is needed:

```php
// a local buffer, because a failed level is retried on the next import level and
// only the last level's result is merged into the request message
$lvl_msg = new user_message($msg->usr);
…
$msg->merge($lvl_msg);
```

The legitimate reasons are narrow: a **buffer that is merged back**, a message
for a **different user** (a system-user bootstrap), or a **sub-result the caller
inspects** with `is_ok()`. A fresh message that is neither merged nor inspected
silently drops every error it collects — that is the bug this rule prevents.

A `user_message $msg = new user_message()` **default parameter value** is the
same drop in disguise: a caller that passes nothing loses its messages. Treat it
as a threading gap, not as a solution.

The coded check is `coding_rule_tests::php_user_message_creation_tests`, which
scans the library trees (backend model, api objects, frontend, shared — the
entry points sit outside them) and regenerates
`docs/code_user_message_exceptions.md` with every still-unexplained creation and
every parameter default. The doc is the work list: it must shrink with each
threading pass and a new unexplained creation fails the test by changing it.

### The requesting user is set on `$msg` by the http entry point

The **http entry point** (`http/view.php`, and every other script under `http/`
that answers a request) is the **only** place that determines who is asking. It
loads the session user once and stores it on the request's `user_message`:

```php
$msg = new user_message();          // the single message of this request
…
$usr = new user;                    // backend user from session / ip
$web_txt .= $usr->get();
if ($usr->id() > 0) {
    $usr_ui = new user_ui();
    $usr_ui->set_from_json($usr->api_json(), $msg);
    $msg->usr = $usr_ui;            // <- the requesting user, set exactly once
    …
}
```

From there on **every function that needs to know who is asking takes `$msg` as
a parameter and reads `$msg->usr`** — it never receives the requesting user as a
second parameter, never reads it from a global, and never re-derives it from the
session.

- **Right**: `function check_preserved(user_message $msg): user_message { … if ($msg->usr->is_admin()) … }`
- **Wrong**: `function check_preserved(user $usr_req, user_message $msg)` — the
  requesting user duplicated next to the message that already carries it
- **Wrong**: `global $sys; $usr_req = $sys->usr_req;` inside a function
- **Wrong**: reading `$_SESSION[url_var::SESSION_USER_ID]` below the entry point

**Set it as early as possible**, directly after the user is known and *before*
any branch decides what to do: the blocked-request branch, the cached-page fast
path and the live rendering all need the requesting user, so a late assignment
leaves the early paths without one. A `$msg->usr` that is `null` must therefore
be treated as "no user known" (fall back to the least privilege), never as a
normal case to fabricate a user for.

**Why**: the requesting user is what every permission decision, every change-log
row and every sandbox write depends on. Kept next to the message, it travels
with the one object those functions already receive — so the permission check
and the message explaining its outcome cannot drift apart, and a function can
never be called with a user other than the one that made the request. It also
keeps the signatures short: one parameter instead of two, and no function has to
be re-threaded when a new check needs the user.

**Each layer holds its own user object.** The two `user_message` classes are
distinct and so are the users they carry:

| message class                  | `->usr` type            | set in                     |
|--------------------------------|-------------------------|----------------------------|
| `web\user\user_message`        | `web\user\user` (`user_ui`) | `http/view.php`        |
| `cfg\user\user_message`        | `cfg\user\user` (backend)   | the api / backend entry |

A frontend function reads the frontend user from its frontend message, a backend
function the backend user from its backend message; the two are never mixed.

**A user that is the *subject* of the call stays an explicit parameter.** The
rule covers the *requesting* user only. When a function operates *on* a user —
`user::no_diff($usr_to_compare, $msg)`, `sandbox::take_ownership($new_owner,
$msg)`, `user_list::save(...)` — that user is normal payload and keeps its own
parameter; `$msg->usr` stays the one who triggered the operation. If a signature
has both, check which is which before removing one.

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
`api_array($msg)` round-trip to the frontend notification bar.

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
