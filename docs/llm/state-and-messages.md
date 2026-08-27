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
exception carries a comment **behind the creation on the same line** saying why a
local message is needed — not on the line above, where it reads as a comment on
the block that follows instead of on the creation itself:

```php
$lvl_msg = new user_message($msg->usr); // a buffer, because a failed level is retried
…
$msg->merge($lvl_msg);
```

Keep it to one short line. Longer rationale (which caller, which gate, why the
threading would break) belongs in the function's docblock, not stacked above the
creation. The only creations that still carry a comment *above* them are blocks
of sibling buffers declared on consecutive lines, which share one comment.

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

A block of buffers that belong together (the per-level messages of an import
loop) is declared on consecutive lines and shares one comment above the block —
the check understands that, so don't repeat the same sentence five times.

### A created message must reach the caller — a message is never lost

A comment says *why* a message is created; it does not say that anybody ever
reads it. So the second half of the rule: whatever a created message collects
**leaves the function again**. Exactly four endings count.

```php
$sub_msg = new user_message($msg->usr); // a buffer of the retried level
$this->save_level($sub_msg);
$msg->merge($sub_msg);                  // 1. merged into the caller's message
```

```php
return $sub_msg;                        // 2. returned to the caller
if ($sub_msg->is_ok()) { … }            // 3. read here to steer the branch
$this->msg = new user_message();        // 4. kept in an object field the caller reads
```

In test code an `assert_msg($test_name, $msg)` is ending 3: the assert reads
the message and a failing run is the report, so an asserted test buffer needs
no further merge or return (see "the assert is the reader" in `testing.md`).

Everything else is a lost message. The worst form has no name at all, because
then not even a later line could read it:

```php
$this->set_type_id($id, new user_message($usr));   // wrong - created only to fill a required parameter
```

The call reports its permission error into a message that dies on the same line.
Thread the caller's `$msg` in instead — the required parameter is asking for the
message of the request, not for *a* message.

A drop that is genuinely intended (a property hook that takes no parameter, a
deprecated display path with no caller message, the log writer itself) says so
with the words **`not reported`** in the comment behind the creation:

```php
$dsp_msg = new user_message(); // not reported: a property hook takes no caller message
```

That marker is the only thing that silences the check, so a drop is always a
deliberate, reviewable decision instead of an oversight. The same
`php_user_message_creation_tests` run lists every still-lost message under
"messages that never reach the caller" in
`docs/code_user_message_exceptions.md`; like the other sections it is a work
list that shrinks, and a new lost message fails the test by changing the doc.

### `$msg` is never null — no `?user_message $msg = null` parameter

A `user_message` parameter is **required**. Neither of the two ways to make it
optional is allowed:

```php
function f(..., user_message $msg = new user_message())   // wrong - hidden drop
function f(..., ?user_message $msg = null)                // wrong - explicit drop
```

The first creates a message nobody reads, so everything the function reports is
thrown away. The second is the same loss written out honestly: `$msg?->add(...)`
reads as "report this if somebody is listening", and at exactly the call sites
that matter nobody is. Making the drop visible in the signature does not stop it
from being a drop — the second form is not a smaller version of the first, it is
the identical bug with better documentation.

There is also nothing for a null to mean. A request has **one** message, created
by the entry point and threaded from there (see above), so at any point below the
entry point a message exists. A parameter that admits `null` is describing a state
the architecture does not have.

So when a caller has no message to pass, the answer is never to make the parameter
optional — it is to give **that caller** a message too:

- the caller takes `user_message $msg` as well and threads it from *its* caller, or
- the caller *is* an entry point (an http/api script, a cron job, a test builder),
  and creates the one message there, with the trailing comment the creation rule
  requires.

The cascade terminates at an entry point every time, which is what makes it safe
to keep pulling.

Two mechanics come up while doing this:

- **PHP forbids a required parameter after an optional one.** When `$msg` lands
  behind `bool $allow_duplicates = false`, either move `$msg` in front of the
  optional parameters or make those required too — do not "solve" it with a
  default.
- **An override cannot add a required parameter** its parent does not have. If
  `child::add()` needs `$msg` and `parent::add()` has no such parameter, the whole
  override family (the base plus every child) takes the parameter in one commit.
  A nullable parameter is not the way around this — it just hides the split.

### "The message is my return value" is not an exception — take `$msg` instead

The most common shape below the entry point is a function that creates a message,
fills it and returns it:

```php
function fill_by_name(triple_list $db_lst, bool $fill_all = false): user_message
{
    $msg = new user_message();      // <- the exception
    …
    return $msg;
}
```

This looks legitimate — the message *is* the result — but it moves the decision
to every caller, and a caller that ignores the return silently drops every error
in it. Take the message as a parameter and return `bool` instead:

```php
function fill_by_name(
    triple_list  $db_lst,
    user_message $msg,
    bool         $fill_all = false
): bool
{
    …
    return $msg->is_ok();
}
```

**Two checks before threading such a family**, both of which decide per call
site, not once for the function:

1. **Which message belongs here?** Inside an import level loop the right target
   is the per-level buffer (`$msg_chk`), not the request message — a problem that
   a later level resolves must not survive in the request message.
2. **Is the callee's reporting wanted at this call site at all?** If the caller
   already reports the same thing afterwards (`$this->report_missing($msg)`), or
   the call is a pure cache fill, pass the existing `report_missing`-style flag as
   `false` rather than letting a premature diagnostic reach the user. Threading
   `$msg` into a function whose message fires on a *normal* path turns a silent
   drop into user-visible noise — check what the callee adds and when.

Because the signature is shared, the whole override family changes together (the
base class plus every child), so this is one commit per family, callers included.

#### The `return $msg->is_ok()` trap

Nearly 400 functions end with `return $msg->is_ok();`. That is correct when the
message is the function's own, but it means **the verdict is about the message,
not about the object** — so passing a shared `$msg` into such a function makes it
answer the wrong question, and it also dumps its findings into the caller's
message.

Both halves bite at once. `sandbox_link::can_be_ready($msg)` is a real example:

- with a shared `$msg` that already carries an unrelated error it returns
  `false` for a perfectly valid link, and
- during an import — where ids are filled in a later step, so "not ready yet" is
  the **normal** state — it adds `FROM_MISSING` / `TO_MISSING` notices to the
  import message.

The second one is unforgiving, because the import steps deliberately guard
dependent work with `if ($msg->is_ok())`: one notice added on a normal path and
**every following object of that import is silently dropped**. That is exactly
how threading `$msg` into `sandbox_link_list::add_link_by_key` emptied the
portfolio import and broke `import_tests`' "distinct impact for each main stock
triple".

Note which half was the defect. The `if ($msg->is_ok())` guard is *wanted* — it
is how a step avoids reporting errors that are only a consequence of an earlier
error, see `docs/llm/dependent-errors.md`. What broke the import was feeding that
guard a message it should never have seen: a condition that is normal at that
point, added as a certain error.

So before threading a message into a readiness / validity check, ask what it
reports **on the normal path**. Give the check its own message so its verdict is
about the object, and merge that message into the caller's only when the outcome
is a real rejection the user must see — an import caller passes its own buffer
even then.

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

**Crossing the boundary is a `merge()`, and it drops the backend-only parts.**
There is no mapper from a backend message to a frontend one; the boundary points
(e.g. `frontend.php` after a direct backend call) simply do
`$msg_ui->merge($msg)`, which works because both classes extend the shared
`Message`. `web\user_message::merge()` copies the message texts, the var
messages and the status — but **not** the `msg_id_lst`, the info and type
messages, the `checksum`, the `db_row_id_lst` or `added_depending`, which
`cfg\user_message::merge()` does copy.

**That loss is accepted for the moment** — including that an id-only message
(`add_id()`) has no text left after the merge, because `text()` falls back to
the `msg_id_lst` that was not copied. The db-related parts of the loss —
`db_row_id_lst`, `checksum` and `added_depending` — do not belong on a user
message at all: they are save/import bookkeeping and are to move to the
`sql_message` object (`cfg/db/sql_message.php`, which already carries the same
fields). Once they live there, the frontend message only has to carry what the
user reads, and the `merge()` at the boundary is complete by construction. So do
not "fix" the frontend `merge()` by copying the db ids over — the direction is
to take them off the user message instead.

The full mapping does exist for the real api boundary: `cfg\user_message::api_json($msg)`
sends `USER_MESSAGES`, `USER_MESSAGES_WITH_VARS`, `USER_MESSAGES_STATUS` and the
user, and `web\user_message::api_mapper($json_array, $msg)` reads them back.

**A user that is the *subject* of the call stays an explicit parameter.** The
rule covers the *requesting* user only. When a function operates *on* a user —
`user::no_diff($usr_to_compare, $msg)`, `sandbox::take_ownership($new_owner,
$msg)`, `user_list::save(...)` — that user is normal payload and keeps its own
parameter; `$msg->usr` stays the one who triggered the operation. If a signature
has both, check which is which before removing one.

### Frontend messages go to the session user and are shown with the next page view

In the frontend the receiver of a message is **always the session user**: every
frontend object exists only for the user of the request (the one exception is an
admin who explicitly views the objects of another user, and even then the admin
is the one reading the report). And the report channel always exists: every
rendered page carries the placeholder `api::USER_MSG_PLACEHOLDER`
(`<!--usr_msg-->`), which the page render fills with the messages collected up
to that point — so a message raised anywhere below is shown with the **next
page view**, without any extra wiring.

Two consequences for frontend and shared display code:

- **"No user to report to" is never a reason to drop a message.** The caller
  chain ends at a page render that has the placeholder, so the fix is always to
  return the message (or take the caller's `$msg`), never to swallow it — e.g.
  the `shared/calc/expression.php` display helpers should hand their buffer up
  to the caller; until they are threaded, their `not reported` marker names
  this section as the reason.
- **`$msg->usr` does not have to be set for a frontend message to be
  reportable.** The session user is implicit in the frontend, so a missing
  `->usr` only limits permission checks, not the reporting — the message still
  reaches the placeholder of the next page view.

### Permissions come from the profile — the user `code_id` only selects a user

Every permission decision on a user derives from the **`profile_id`** and the
profile hierarchy behind it: `is_admin()`, `is_system()`, `is_system_test()`,
`is_developer()`, `is_unique()`, and every `can_set_*` build on the profile
only. The user **`code_id`** is a *selector*: it identifies one specific
reserved user (`system`, `admin`, `test` vs `test_partner`, …) so code can pick
"system test user 1" over "user 2" — it never grants or removes a right.

- **Right** — the privilege check reads the profile:

```php
function is_system_test(): bool
{
    ...
    if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::TEST)) {
        $result = true;
    }
    ...
}
```

- **Wrong** — deriving the privilege from the selector:

```php
if ($this->code_id == users::SYSTEM_TEST_CODE_ID) {
    $result = true;   // a code id must never widen a permission
}
```

**Why**: the profile is the single, auditable permission model (each profile has
a right level, and `can_set_profile` guards escalation); a second, code_id-based
path would bypass those guards — any code that can set a code id could then mint
privileges. If a check needs "the test users", express it as the TEST *profile*;
the code_id stays what it is everywhere else in the system: the stable key to
*find* a row, not a property of it.

### A code id is only shown to an admin or a developer

The code id of an object (e.g. of a source) links a database row to program
code, so it is an internal detail that only an admin or a developer needs to
see. Every page and form hides it for every other profile — including the
system and test profiles, which could technically *set* it but do not browse
pages, so the rendered test snapshots stay free of the field.

Seeing and changing are two separate gates on the frontend user:

- `can_see_code_id()` — admin or developer: the code id (value or field) may be
  rendered at all
- `can_set_code_id()` — system, test or developer (mirrors the backend):
  the user gets the *input* field; a user who may only see it (an admin) gets
  the code id as read-only labeled text

`system_form::form_field_code_id()` is the reference implementation: hidden
below `can_see_code_id()`, editable only with `can_set_code_id()` on top. A new
renderer that shows a code id anywhere must use the same two gates.

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

The one exception is a **test**, which owns its message instead of receiving it:
there `$msg->reset()` after each checked assert is wanted, so that every test
finds its own issues (`testing.md`, "created once and reset after each checked
test").

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

## Url field names and field values, and why both are unique

`url_var.php` holds two kinds of const, and telling them apart is the first step
to reading it:

- a **url field name** (the key left of the `=`), e.g. `MASK = 'm'`,
  `ACTION = 'a'`, `REFRESH = 'fr'`; `PRE = '8'` and `BACK = '9'` are the two
  field name *prefixes*
- a **value that one named field can carry** (right of the `=`), e.g.
  `CRUD_CREATE` is a value of the `ACTION` field and `REFRESH_LATEX` a value of
  the `REFRESH` field

So `ACTION = 'a'` and `CRUD_CREATE = 'a'` are not the same thing used twice: the
first names the field, the second is one of the values that field accepts, and
the url that creates an object reads `?a=a`. The class is one namespace only in
the sense that both kinds live in it — the url itself keeps them apart by
position.

A value const still gets its **own unique string, built by repeating its field
name**, so that reading a url and searching the code both stay unambiguous:

- **Right**: `REFRESH = 'fr'` with `REFRESH_EXPRESSION = 'fre'`,
  `REFRESH_LATEX = 'frx'`, `REFRESH_TERMS = 'frt'` — `?fr=frx` says at a glance
  which field carries which value, and a search for `frx` finds one place
- **Wrong**: `REFRESH_EXPRESSION = 'e'` — a bare letter reads like a field name
  of its own, and the next single-letter field then shares its string

Check a new const against the whole file before adding it, e.g. with
`grep "^\s*const string" src/main/php/shared/url_var.php | grep -o "= '[^']*'" | sort | uniq -d`
(the leading `const` filter skips the commented-out placeholder lines).

### Every url var also needs its human name and the mapping

The same url exists in a short standard format (`?m=25&id=1&fr=frx`) and in a
human-readable format (`?mask_id=formula_edit&id=1&refresh=latex_from_expression`),
so a new const is only half done without its `*_HUMAN` twin and the map entry
that `web/helper/url_mapper.php` converts with. Without the map entry every
`standard_url_to_human()` call reports the key as `URL_MAP_MISSING`.

A new **field name** needs two things:

1. `<NAME>_HUMAN` beside the short const in `url_var.php`
2. a `[self::<NAME>_HUMAN, self::<NAME>]` row in `url_var::HUMAN_TO_STD`

A new **field value** needs three more, because a value is converted by its own
map (see `ACTION` / `STEP` as the existing examples):

3. `<NAME>_HUMAN` for each value; a human value names what it does rather than
   repeating the field, e.g. `REFRESH_LATEX_HUMAN = 'latex_from_expression'`,
   which keeps it readable and unique at the same time
4. a `HUMAN_TO_STD_<FIELD>_VAL` const mapping standard value → human value
5. one branch per direction in `url_mapper.php`: in `map_standard_to()` (short →
   human) and in `map_url_to_standard()` (human → short), each calling a
   `map_std_<field>_to()` / `map_human_<field>_to_std()` wrapper

`POD_TO_STD` only carries the mask and the step, because the pod-independent url
is the minimal interchange format — a transient form parameter is not added there.

Some older consts still share a string, nearly all of them a field name and a
value of another field: `ACTION = 'a'` / `CRUD_CREATE = 'a'`, `VIEW = 'd'` /
`CRUD_DELETE = 'd'`, `RESULT = 'r'` / `CRUD_READ = 'r'`, `USER = 'u'` /
`CRUD_UPDATE = 'u'`. Those urls are unambiguous, but the code is harder to read
and to search, so they are debt and not a pattern to copy. The one real field
name clash is `STEP_HUMAN = 'step'` / `STEP_POD = 'step'`, which is the same
field in the long and in the pod url format. Renaming any of them changes the
url contract and needs its own change (see `docs/llm/pending_prio_2.md`).

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
