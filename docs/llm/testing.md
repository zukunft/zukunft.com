# Testing

Detail for the "Testing rules" in `CLAUDE.md`.

## Write the tests first — a positive and a negative test for every feature

**Every** function — including every new one — is covered by tests that are
written **first**, before the function body exists, so the function is shaped by
its expected behaviour. Cover **every feature** of the function: each behaviour,
branch and meaningful kind of input gets its own **positive** test (expected
input → expected result) **and** its own **negative** test (invalid / missing /
boundary input rejected or handled gracefully). One happy-path test, or testing
only some of the features, counts as untested for review.

- **Positive**: the documented good case returns the documented result, e.g.
  `body_search()` with a populated word list returns the matching links.
- **Negative**: the failure/edge case is exercised, e.g. `body_search()` with a
  `null` list returns an empty string instead of erroring; `import_mapper()` with
  a missing mandatory field adds the expected `msg_id` and `is_ok()` becomes
  false.

The negative test asserts the *reported* outcome (the `user_message`/`msg_id`,
the empty result, the `false` return), never merely "no exception was thrown" —
silent failure is itself a defect ("Best guess" principle).

### Functions that cannot be unit-tested need a db read or db write test

Some functions can't be exercised by a pure unit test in
`src/test/php/unit/` because they reach beyond their parameters — hit the DB,
trigger a REST roundtrip, depend on the seeded type cache. **Skipping isn't an
option**; the test lives one tier up:

- **DB read test** in `src/test/php/unit_read/<topic>_read_tests.php` — for
  functions that load without modifying (`load_by_id`, `load_by_name`,
  `load_by_id_with_related`, list-loaders, cache-resolving helpers). Assert
  against seeded fixture data (`words::CHF`, `triples::CITY_ZH`).
- **DB write test** in `src/test/php/unit_write/<topic>_write_tests.php` — for
  insert/update/delete (`save`, `del`, `insert`, `update`, `add_via_api`). Clean
  up the created row via the reserved-name pattern so a re-run finds a clean
  slate.

The positive/negative discipline carries over:
- **Positive**: a seeded row loads/saves cleanly and the side-effect (populated
  field, inserted row, merged json) is what the docblock promises.
- **Negative**: an unknown id / missing row / pre-existing duplicate produces the
  documented null/0/false/`is_ok()==false` outcome — and the test asserts on that
  reported outcome, never on "no exception was thrown".

Pick the tier by what the function does: read-only → `unit_read`; mutating →
`unit_write`; pure (no DB, no REST, no global cache lookup) → keep it in
`src/test/php/unit/`. Functions touching both (e.g. a `save()` that first loads
to merge) belong in `unit_write` because the write side dominates cleanup needs.

## No ad-hoc database access — only the standard interface and the `/test` scripts

For testing, never create any temporary script that reads or writes data from
the database — no one-off `psql`/SQL statements, no throwaway PHP probe files,
no direct table queries "just to check". The database is accessed **only** via
the already created standard interface (the model objects' `load_*`, `save`,
`del` methods) and via the existing scripts in `/test`.

This holds for every step of a test's life:

- **Setup**: missing fixture data is created through the model objects or an
  import file run by a `/test` script — never by hand-written `INSERT`s.
- **Verification**: whether an import or cleanup worked is asserted by a test in
  the matching tier (`unit_read`/`unit_write`) using `load_by_name()` & co. —
  never by querying tables directly.
- **Cleanup / healing**: leftover test data is removed via the objects' `del()`
  in the test's cleanup (fix the cleanup if it misses rows) — never by a manual
  `DELETE`.

The reasons: a direct SQL statement bypasses the user sandbox overlays, the
change log and the caches, so it can leave the database in a state the
application itself can never produce; and whatever the temp script checked or
fixed is lost — encoded as a proper test or cleanup it keeps working for every
future run.

- **Right**: the write test records which words/triples were missing before the
  import and removes exactly those via `del()` in its cleanup; a failed cleanup
  is fixed in the test and re-run.
- **Wrong**: `psql -c "delete from triples where triple_id = 3985"` to remove a
  row a cleanup missed, or a `/tmp/probe.php` that opens the DB to inspect a
  table.

## A deleted test object takes its change log entries with it

Every write test changes rows through the model, so every change is recorded in
the change log. When a cleanup deletes a test row, the change log entries of
that row are deleted too — otherwise they keep pointing to a row that no longer
exists and stay as noise in the change log of every database a test has run on.

The helpers live in `test_base` and load the entries via the model
(`change_log_list`), so each entry is removed from its correct `change*` table
(the shared core is `test_base::delete_change_log_of_obj`):

- `cleanup_change_log($sbx, $names)` for named objects (word, triple, ...) and
  type rows (e.g. a sys log function)
- `cleanup_change_log_value(...)` / `cleanup_change_log_ref(...)` /
  `cleanup_change_log_group(...)` for the id-based objects; they only act if a
  related phrase is a reserved test row, so real data is never touched
- `cleanup_change_log_deleted()` for entries whose row is already gone

The generic `test_objects::cleanup_objects()` already calls them; a new cleanup
path does the same **before** the `del()` of the row, because after the delete
the row can no longer be loaded to confirm by its reserved name that it was a
test row.

As the overall safety net `test_cleanup::check_cleanup` verifies at the end of a
test run via `resources/db/cleanup/test_changes.sql` that no change log entry
with the reserved test name pattern is left — after purging the entries that the
cleanup `del()` calls themselves have written about the test rows.

- **Right**: the cleanup first calls `$t->cleanup_change_log($wrd, $names)` and
  then deletes the test words via `del()`.
- **Wrong**: the cleanup only deletes the rows and leaves `changes` entries
  pointing to the deleted test words — or removes the entries with a manual
  `DELETE`.

## An LLM never runs the `/test/*` scripts — the developer does

The predefined test scripts in `/test/*` — `test.php`, `test_unit.php`,
`test_coding_rules.php`, `test_horizontal.php`, `reset_db.php`, ... — are
**never executed by an LLM**, neither via the CLI nor over HTTP. Running them
needs a local deployment (a served checkout, a populated database, an admin
session), and deploying is never an LLM task; `test.php` also *writes* to the
database, so a run against a half-deployed checkout leaves state no application
path can produce.

The LLM's job ends with the code: write the tests, review them, lint the
changed files (`php -l` is fine — it executes nothing). Then ask the developer
to run the suite and report the results, and fix what the report shows. If a
run seems to "work" from the LLM environment, that is not permission — it means
the environment accidentally reaches a deployment it should not touch.

## Test object creation

All objects used in tests come from a factory function in
`src/test/php/create/`. Each domain type has its own factory class —
`test_words`, `test_formulas`, `test_views`, `test_languages` — with named
methods returning pre-configured objects with well-known field values.

- **Right**: `$obj = $t_frm->formula_link_filled();`
- **Wrong**: `$frm_lnk = new formula_link($usr); $frm_lnk->set_id(99); ...`

This applies to **every** named test object, including small frontend/unit-test
fixtures. A renderer test needing a word with a plural and a phrase type obtains
it from `test_words` (add `word_with_plural()` if none fits), not
`new word(); $w->set_name('apple'); $w->set_plural('apples');`. When a factory
method doesn't exist yet, add it to the appropriate `test_*.php` in
`src/test/php/create/` before writing the test. `test_mappers.php` coordinates
factory calls when a test resolves a class name to a test object at runtime.

**Don't-repeat-yourself applies here too.** A multi-step object setup belongs in
one named factory method, not written inline in a test (and never copied into a
second test). If building the test object takes more than a line, it is a
factory method waiting to be named — e.g. a Zurich word with related phrases, a
non-default type, share and protection is `test_words::zh_full_ui()`, not five
set-up lines in the test.

### The user of a test comes from the test environment — never `global $usr`

Nearly every test object needs a `user`, and it always comes from the test
environment `$t` (`utils/test_base.php`), never from a global:

| test user           | purpose                                                          |
|---------------------|------------------------------------------------------------------|
| `$t->usr1`          | the main test user                                                |
| `$t->usr2`          | the second user, to test the user sandbox (overlay, exclude)      |
| `$t->usr_normal`    | standard profile, to test that a privileged function denies       |
| `$t->usr_admin`     | admin profile, to test that an admin function allows              |
| `$t->usr_system`    | system profile, for the system-level functions                    |
| `$t->usr_dev`       | the virtual dev user allowed to set a `code_id`                   |
| `$t->usr_test_admin`| the admin used by the write tests                                 |
| `$t->usr_signup`    | the system user that adds new users                               |

Inside a `create/test_*.php` factory the same environment is reached through the
injected `$this->env` (`new word($this->env->usr1)`). A `static` factory has no
environment, so it takes the user from `test_users`
(`new word(test_users::user_sys_test())`) — not from a global either.

When a test needs a *user object itself* as the fixture (a specific profile,
code id or filled fields), that user also comes from a `test_users` factory
method (`$t_usr->user_sys_test()`, `$t_usr->user_ip()`, …) — never from an
inline `new user(); $usr->set(...); $usr->code_id = ...;` block in the test;
the general test-object-creation rule above applies to users too.

**An import test uses one user for the import object *and* its message.** An
`import` carries its own `$imp->usr` (the objects it builds get that user) while
the privilege checks read `$msg->usr`. When the two differ, the import fails in
two ways that are easy to misread:

- a *privilege* check denies (e.g. `set_code_id` for a fixture with a `code_id`,
  which needs `$t->usr_dev`), and because `import_mapper` returns
  `$msg->is_ok()` on the shared append-only message, **every later object of the
  same import is dropped too**;
- a *user mismatch* between an object and the list it is added to makes
  `sandbox_link_list::add_link_by_key` skip the entry **without any message
  reaching the caller** — e.g. every component link of a view silently
  disappears, so a view consistency check (row balance, positions) finds an
  empty list and reports nothing at all.

```php
// right: the same user drives the import and the permission checks
$imp = new import(test_files::SYSTEM_CONFIG_SAMPLE);
$imp->usr = $t->usr_dev;
$msg = new user_message($t->usr_dev);
```

So when an import assert suddenly sees an *empty* message where it expects a
consistency warning, suspect the user pairing before the check itself.

- **Right**: `$wrd = new word($t->usr1);` / `$wrd = new word($this->env->usr1);`
- **Wrong**: `global $usr; $wrd = new word($usr);`

`$usr` is not in the allowed global set (see
[state-and-messages.md](state-and-messages.md#allowed-global-variables)) — the
requesting user is always an explicit parameter. In a test it additionally makes
the result depend on whichever session happens to run the test, so the same test
can pass locally and fail in a clean run, and a sandbox test cannot tell the two
users apart.

### Never create a test object as the system user — use `$t->usr1`

A test object (a word, triple, value, source, view, …) is **created by the
normal user `$t->usr1`** — that is the standard owner for all test data. Reach
for a different user only for a specific reason:

- `$t->usr2` — to test a second user's sandbox (overlay, exclude, the change is
  private to one user).
- `$t->usr_admin` — only when the create genuinely needs a privilege a normal
  user lacks (e.g. an admin-only function under test). Note this is *not* the
  case for `set_code_id`: `user::can_set_code_id()` allows the system, test, log
  and dev profiles but **not** admin, and `$t->usr1` carries the test profile, so
  the normal user already sets a `code_id` — pass `$t->usr1` there too.

Never create a test object as **`$t->usr_system`**. The system user (id 1) is for
exercising *system-level functions*, not for owning ordinary test data. A word
owned by the system user cannot be removed by the normal-user fallback cleanup
(a non-owner delete only writes a private exclusion, leaving the base row), so it
surfaces as a `check_cleanup` leftover; and because the system user is a
bootstrap identity, an aliasing bug that resets `$t->usr_system` to id 0 turns
every such create into a `changes.user_id` foreign-key failure deep in the run.

- **Right**: `$wrd = $t_db->test_word(word_names::TEST_EARNING);` (defaults to `$t->usr1`)
- **Wrong**: `$wrd = $t_db->test_word(word_names::TEST_EARNING, null, $t->usr_system);`

The rule targets *ephemeral* test objects — the ones a test creates and the
cleanup removes afterwards. **Permanent fixture data** that is deliberately never
cleaned up (the years `2013`…, alongside the imported fundamental words like
`mathematics`) may be system-owned, exactly like those fundamentals: there is no
per-test cleanup to block, so the reason for the rule does not apply. Keep that a
narrow, commented exception (see `zu_test_time_setup`), not a habit — if the
object is ever deleted by a cleanup, it is ephemeral and belongs to `$t->usr1`.

Two related uses are also *not* covered by this rule, because the system user is
not the owner of a sandbox object there: the actor/author field of a `change` or
`sys_log` fixture (a fixed system actor for a log-rendering test), and the
requesting user of a delete of a protected class a normal user may not remove.

### Populated list / collection fixtures come from a factory too

A test that builds a populated list inline —
`new phrase_list_ui(); $lst->add_with_verb($x, verbs::SYMBOL_ID);` — is ad-hoc
construction in disguise: the list's shape (entries, verb, order) scatters into
the test body. Add a factory in the matching `create/test_*.php` (a `phrase_list`
in `test_phrases.php`, a `word_list` in `test_words.php`) that assembles the list
from shared consts.

- **Wrong**:
```php
$sym_lst = new phrase_list_ui();
$sym_lst->add_with_verb($t_wrd->swiss_franc_ui()->phrase(), verbs::SYMBOL_ID);
$chf->phrases_related = $sym_lst;
```
- **Right** — list shape in `test_phrases::list_chf_symbol_ui()` (built from
  `words::SWISS_FRANC` + `verbs::SYMBOL`):
```php
$chf->phrases_related = $t_phr->list_chf_symbol_ui();
```

### Test url arrays come from a factory object via to_url_array($msg)

A url test (e.g. `unit_workflow/*_url_tests.php`) never hand-builds the object
fields of a `$url_arr`: it starts from a factory object's `to_url_array($msg)`
(`$t_wrd->word_dsp()->to_url_array($msg)`) or a factory url helper
(`test_words::word_new_url()`, `word_add_url()`, `change_url_array()`, ...) and
adds only the request context — mask, action, step, back, user. A hand-built key
list duplicates the object's field mapping in the test body, and the factory
would no longer show centrally which test objects each test uses; keeping the
build in the factory also means a field added to the object reaches every url
test through one change. To vary a field, change it on the factory object
(`$wrd->set_description(...)`) before calling `to_url_array($msg)`, never by
patching the array. The only urls built without a factory object are those that
carry no object at all (e.g. a search pattern url).

- **Wrong**:
```php
$url_arr = [];
$url_arr[url_var::MASK] = views::WORD_EDIT_ID;
$url_arr[url_var::ID] = word_names::MATH_ID;
$url_arr[url_var::NAME] = word_names::MATH;
```
- **Right** — the object fields come from the factory, the test adds the context:

```php
$url_arr = $t_wrd->word_dsp()->to_url_array($msg_ui);
$url_arr[url_var::MASK] = views::WORD_EDIT_ID;
```

### Factory names don't repeat the object word

Inside a `create/test_*.php` factory the object type is fixed by the class, so
the method name must **not** repeat it. `test_phrases` is called through `$t_phr`,
so `$t_phr->list_chf_symbol_ui()` already reads as "a phrase-list for the CHF
symbol" — prefixing with `phrase_` just stutters. Name for what distinguishes the
method *within* the class.

- **Wrong**: `test_phrases::phrase_list_chf_symbol_ui()` → `$t_phr->phrase_list_chf_symbol_ui()`
- **Right**: `test_phrases::list_chf_symbol_ui()` → `$t_phr->list_chf_symbol_ui()`
- Same elsewhere: `test_words` CHF factory is `chf()` / `chf_ui()` (not
  `word_chf()`); `test_triples` Pi triple is `pi()` (not `triple_pi()`).

Older factories carrying the redundant word (`test_words::word_chf()`,
`test_phrases::phrase_list()`, `test_triples::triple_pi()`) predate this rule and
drop the prefix when next touched.

### Use only `RESERVED_NAMES` for named test objects

Every named object type has a `RESERVED_NAMES` list in
`src/main/php/shared/const/<type>.php` — `words::RESERVED_NAMES`,
`triples::RESERVED_NAMES`, `views::RESERVED_NAMES`, `formulas::RESERVED_NAMES`,
`sources::RESERVED_NAMES`, `users::RESERVED_NAMES`,
`components::RESERVED_COMPONENTS`, `groups::RESERVED_GROUP_NAMES`,
`refs::RESERVED_REFERENCES_TYPES`/`_KEYS`. These names are guaranteed not to be
renamed and are reserved for testing.

Test objects — ad-hoc fixtures and `create/test_*.php` factory methods — must
reference names **only** through these constants (or the individual consts they
list, `words::PI`, `views::WORD_DEFAULT`), never a free-form string literal.
Hand-typed names like `'apple'`, `'second'`, `'foo bar'` are wrong even when they
work today: the string can gain meaning in real data, the reserved-list cleanup
misses it, and an import round-trip diverges silently.

- **Right**: `$wrd = $t_wrd->word_pi();` returning a word built from `words::PI`,
  asserting against `words::PI`
- **Wrong**: `$wrd = new word_ui(); $wrd->set_name('apple');` — even for a
  one-line renderer test

When no reserved name fits, add a new const to the type's `*.php` and append it
to `RESERVED_NAMES` before writing the test, so cleanup and round-trips pick it
up.

### Database ids in tests are named consts too

The "names only through constants" rule applies equally to the **database id**: a
test never hands a raw numeric id to a constructor or helper. A literal id is a
magic literal (it has a `*_ID` const) **and** silently collides — a test writing
`related_phrase(259, 'Swiss Franc')` was using `259`, actually `words::USD_ID`,
so the rendered link pointed at the wrong row while the assertion still passed.

Two parts:

1. **The id is a `*_ID` const** — `words::SWISS_FRANC_ID`, not `269`; the name is
   `words::SWISS_FRANC`, not `'Swiss franc'`. If neither exists, add the
   `<NAME>` / `<NAME>_ID` pair (plus `BASE_*` and id→name map entries) before
   writing the test.
2. **The object comes from a `create/test_*.php` factory**, not a local
   `private static function` taking `(int $id, string $name)` — a per-test helper
   fabricating from an id/name pair is `new word_ui(); set_id(); set_name();` in
   disguise. Add/reuse a factory like `test_words::word_swiss_franc()` /
   `word_swiss_franc_dsp()` and call `$t_wrd->word_swiss_franc_dsp()->phrase()`.

- **Wrong**: `$swiss_franc = self::related_phrase(259, 'Swiss Franc');`
- **Right**: `$swiss_franc = $t_wrd->word_swiss_franc_dsp()->phrase();` — asserts
  against the same consts (`'…&id=' . words::SWISS_FRANC_ID . '">' . words::SWISS_FRANC . '</a>'`)

### Don't add a new builder when existing factories cover it

DRY applies to the `create/test_*.php` factories themselves: if `test_words`,
`test_verbs` and `test_triples` together already produce every part of an object,
don't add a new builder that re-creates it from raw parameters. A helper like
`test_phrases::triple_phrase_ui(int $trp_id, string $trp_name, phrase_ui $from,
int $verb_id, phrase_ui $to)` looks neutral, but every call site has to *re-state*
the triple's identity — bypassing the named factories (`test_triples::zh_city()`,
`test_verbs::verb_is()`, `test_words::chf()`) where that identity is already
defined.

When you need a fixture wrapping a specific triple, compose the existing backend
factories and convert via the api json round-trip (see
[dry.md "DRY applies to test fixtures too"](dry.md#dry-applies-to-test-fixtures-too)).
If no factory fits, add one — e.g. `test_triples::chf_symbol_swiss_franc()` — so the
canonical id, name, from, verb and to all live in **one** place.

- **Wrong**: `triple_phrase_ui(triples::PI_SYMBOL_ID, 'CHF is symbol for Swiss franc',
  $t_wrd->chf_ui()->phrase(), verbs::SYMBOL_ID, $t_wrd->swiss_franc_ui()->phrase())` —
  five raw fields per call, including a hand-typed triple name
- **Right** — add `test_triples::chf_symbol_swiss_franc()` if missing, then
  `$lst->add($t_trp->chf_symbol_swiss_franc()->phrase()); return new phrase_list_ui($lst->api_json());`

## Test assertion style

Declare the test name as a named variable on its own line before the assertion,
then pass it to `$t->assert*()`.

```php
// Right
$test_name = 'login page with failed login shows notification bar';
$t->assert_text_contains($test_name, $login_html, '<div class="alert alert-warning notification-bar">');

// Wrong — inline string makes the line too long
$t->assert_text_contains('login page with failed login shows notification bar', $login_html, '<div ...>');
```

### A test block runs name → prepare → call → assert

Every single test reads the same way top to bottom, in four parts and in this
order:

1. **name** — `$test_name = '…';` is the **first** line of the block.
2. **prepare** — build the fixtures / input the call needs (from the
   `create/test_*.php` factories, never inline construction).
3. **call** — invoke the function under test, capturing its result.
4. **assert** — an `assert*()` is the **last** line of the block; every block
   ends on its assertion, and nothing follows it except the next block's
   `$test_name`.

```php
// Right — name, then prepare, then call, then assert
$test_name = 'category subtitle uses the SYMBOL verb name verbatim';   // 1. name first
$chf_sym = $t_wrd->word_chf_dsp();                                     // 2. prepare
$title_sym = $form->title_of_named_with_edit_link($chf_sym);          // 3. call under test
$t->assert_text_contains($test_name, $title_sym, verbs::SYMBOL_NAME); // 4. assert last
```

Keep the four parts in this order so a reader sees the input, the action and the
expected outcome in one glance. A block that prepares or computes *after* the
assertion, or asserts *before* the call, hides what it is proving — split it into
ordered blocks instead, each with its own `$test_name` first and its `assert*()`
last.

The `$test_name` is declared **first — at the top of the test setup, before any
test variables are built** — not just before the assertion line. Reassign it
again right before each later assertion in the same block.

```php
// Right — name first, then the fixture, then the assertion
$t->subheader($ts . 'category subtitle');
$test_name = 'category subtitle uses the SYMBOL verb name verbatim';
$chf_sym = $t_wrd->word_chf_dsp();
$chf_sym->phrases_related = $t_phr->list_chf_symbol_ui();
$title_sym = $form->title_of_named_with_edit_link($chf_sym);
$t->assert_text_contains($test_name, $title_sym, verbs::SYMBOL_NAME);
```

### The test `$msg` is created once and reset after each checked test

Like `$test_name`, the `user_message $msg` a test threads into the functions
under test is created **once**, at the block's init, and **reused** for every
call in the block:

```php
// init
$t_db = new test_db_load($this);
$msg = new user_message();
// ...
$usr->load_by_profile_code(user_profiles::TEST, $msg);   // same $msg
// ...
$t->cleanup($msg);                                        // still the same $msg
```

`$msg` is append-only in production — `http/view.php` creates the one request
`user_message` and nothing below it ever resets or re-creates it (see
`state-and-messages.md`). Tests are the **only** place `$msg->reset()` is
allowed, and there it is not the exception but the rule: **reset the message
after every test whose result has been checked with an assert**, so the next
test starts from a clean, `is_ok()` message.

```php
// negative test — the call is supposed to report an error
$test_name = 'adding a triple with the name of a word is rejected';
$t->assert_false($test_name, $trp->save($msg_ui)->is_ok());
$msg_ui->reset();   // the assert has read it, so clear it for the next test

// the next test starts from a clean $msg
$test_name = 'a valid triple is saved';
$t->assert_true($test_name, $trp_ok->save($msg_ui)->is_ok());
$msg_ui->reset();
```

**Why**: a test run should surface as many **independent** issues as possible.
A message that still carries the reports of the previous test makes the next
failure unreadable (the assert prints the whole accumulated text) and, worse,
can suppress the next test entirely: functions that gate their work on the
message they were given — `if ($msg->is_ok())` in the expression parser, the
`db_ready()` / `can_be_ready()` loops (`state-and-messages.md`, "The
`return $msg->is_ok()` trap") — do nothing when handed a message that failed
earlier. The result is an empty list and an assert that fails for a reason that
has nothing to do with what it tests.

The reset belongs **after the assert**, never before it: the assert is the
reader of the message (see the next section), so clearing it earlier throws away
what the test is supposed to check. Keep the reset out of a test that
deliberately continues to work on the collected state (a workflow snapshot
building one message over several steps) — there the accumulation *is* the
subject of the test.

Never spin up a fresh `new user_message()` mid-block just to "start clean" —
reset the one block `$msg` so a single object carries the block's history.
`reset()` keeps the user (`reset(keep_usr = true)`), so re-setting `$msg->usr`
after it is unnecessary.

### In a test the assert is the reader — an asserted `$msg` is fully handled

The "a message must reach the caller" rule of `state-and-messages.md` asks that
somebody *reads* what a message collects. In a test that reader is the
assertion: `$t->assert_msg($test_name, $msg)` reads the state (`is_ok()`) and,
on failure, the text — and failing the run **is** the report. There is no
request user above a test, so nothing more has to happen with the buffer:

```php
$cac_msg = new user_message();
$tl->ui_test_cache($t->usr1, $t, $cac_msg);   // asserts $cac_msg internally
// done - no merge, no return, no further read needed
```

This also holds when the assert sits inside the called helper (as in
`test_lib::ui_test_cache`): the caller creates the buffer, the helper fills and
asserts it, and neither side needs to hand the content anywhere else. Judge a
test-side message by whether an assert consumes it, not by whether the
immediate caller merges its return.

### Keep `$test_name` short but unique — don't repeat the subheader

The `$test_name` only has to make the assertion **uniquely identifiable within
its block**. Keep it short and do **not** repeat context that the enclosing
`$t->subheader(...)` (or `$t->name`) already shows — the failure output prints
the subheader above the test name, so repeating the function or topic is noise.

```php
// Right — the subheader already says "title_named", so the name just adds the case
$t->subheader($ts . 'title_named');
$test_name = 'shows the object name';
$t->assert_text_contains($test_name, $title, $wrd->name());
$test_name = 'wraps the heading in the heading-line div';
$t->assert_text_contains($test_name, $title, styles::HEADING_LINE);

// Wrong — repeats "system_form->title_named" that the subheader already shows
$test_name = 'system_form->title_named shows the object name';
```

### Pass only `$test_name` — don't re-concatenate `$t->name`

The `$t` object already holds the current section name in `$t->name` (set once
per `run()`, e.g. `$t->name = 'word->';`). An `$t->assert*()` call passes **only
the distinguishing `$test_name`** and lets the assertion prepend `$this->name`
itself — don't write `$t->name . $test_name`.

- **Wrong**: `$t->assert_text_contains($t->name . $test_name, $title_sym, verbs::SYMBOL_NAME);`
- **Right**: `$t->assert_text_contains($test_name, $title_sym, verbs::SYMBOL_NAME);`

Where a helper doesn't yet prepend `$this->name`, **fix the helper once** rather
than keep concatenating at call sites. Existing `$t->name . $test_name` calls
predate this rule and drop the prefix when next touched.

### Prefer the specific `assert_*` variant — keep the call a one-liner

When a dedicated helper expresses the check, use it directly with raw values —
**don't** wrap a generic `assert_true()`/`assert_false()` around a
`str_contains()`, `strpos()`, `count()`, or comparison. The specific variant
(`assert_text_contains`, `assert_text_not_contains`, `assert_greater`,
`assert_contains`) reports a useful diff on failure and states intent. A generic
`assert_true(…, str_contains(…))` only reports "expected true, got false".

- **Wrong**: `$t->assert_true($test_name, str_contains($title_sym, verbs::SYMBOL_NAME));`
- **Right**: `$t->assert_text_contains($test_name, $title_sym, verbs::SYMBOL_NAME);`

Two follow-ons:
- Collapse a defensive compound into the single substring that subsumes it.
  `(str_contains($h,'<h4>CHF</h4>') or str_contains($h,'<h2>CHF</h2>')) and !str_contains($h,'>CHF <a')`
  is really "the heading is exactly `<h4>CHF</h4>`" → `assert_text_contains($name, $h, '<h4>CHF</h4>')`.
- Only when the check is genuinely **positional/relational** with no substring
  equivalent — e.g. "the edit icon appears *after* the closing heading tag"
  (`$edit_pos > $heading_end`) — is a generic `assert_true` with the comparison
  acceptable. Reach for it last. (Still feed it named consts:
  `strpos($html, icons::EDIT)`, not `'fas fa-edit'`.)

### Read a `user_message` result with `$msg->text()`, not `all_message_text()`

A `user_message` is append-only and threaded across a whole request, so by the
time a test inspects it, it can carry messages from **several** operations. Assert
on **`$msg->text()`** — the single most-useful (last, translated) message — which
is what the user actually sees. Avoid **`all_message_text()`** (the concatenation
of every accumulated message) and the raw `get_last_message*()` getters as the
assertion target: concatenation makes the test brittle, and it couples the test to
code paths it does not exercise.

This is not hypothetical: an import test asserting the whole message text broke the
moment an unrelated "not yet supported" notice was added on the same `$msg` — the
notice shifted the last message and buried the genuine `"Unknown element test"` the
test was checking. `$msg->text()` would have stayed pinned to the message that
matters.

```php
// Right — the one message the user sees; robust to unrelated messages on the same $msg
$test_name = 'import of an unknown element is reported';
$imp->put_json_direct($json_str, $msg_ui);
$t->assert($test_name, $msg_ui->text(), 'Unknown element "test"');

// Wrong — asserts the concatenation of every message, so any unrelated add elsewhere breaks it
$t->assert($test_name, $msg_ui->all_message_text(), 'Unknown element "test"');
```

Assert `all_message_text()` only when the test genuinely verifies that a **set** of
messages is present (e.g. a batch that must report each of several failures), and
even then prefer `assert_text_contains` on the specific expected fragment over an
equality on the whole blob.

### Test subheaders are short but unique

A `$t->subheader(...)` label names the test section in the run output — keep it
**as short as possible while staying unique** within the file. The full
behaviour belongs in the per-assertion `$test_name` strings.

- **Wrong**: `$t->subheader($ts . 'category subtitle renders below the title for a SYMBOL-typed related entry');`
- **Right**: `$t->subheader($ts . 'category subtitle');`

Don't collapse two sibling sections to the same label; keep just enough of the
distinguishing word (`'category subtitle'` vs `'category subtitle (multi)'`).

## Generated files are rewritten by the run, never by hand and never by the LLM

Every file that says `do not edit manually` — `docs/code_object_name_exceptions.md`,
`docs/code_user_message_exceptions.md`, `docs/code_test_coverage.md`,
`docs/json_findings.md`, `docs/code_functions_all.md` — and every regenerated
baseline (the `list.csv` of `src/test/resources/unit/<class>/`, the html snapshots,
the api fixtures) is written by `test/test.php` with `AUTO_UPDATE_TEST_FILES` set to
`true` and compared with the flag set to `false`. A test that reports such a file as
outdated says that the code or the data behind it changed; it is never a request to
edit the file:

- if the change is unwanted (e.g. a `user_message` created under a name other than
  `$msg` without a strong reason), fix the cause in the code or the data
- if the change is intended (a new buffer with a documented reason, a new phrase,
  a new view), leave the file alone: the next run with the flag regenerates it and
  the diff of that run shows the change for review

An LLM patching the generated line by hand masks the signal, and the next run
overwrites the edit anyway.

## Tests that depend on data files must be reproducible from a single point of change

A test relying on a generated artifact — a JSON import file, an SQL snapshot, an
HTML snapshot — must **recreate that artifact from code**, so it never silently
diverges from the constants it was built from. The artifact references a value
through a shared constant, and regenerating uses the same constant.

This matters most for **import round-trip tests** that create an object then
delete it during cleanup. The name in the import JSON must come from a **reserved
test constant** — for words, a reserved test word in
`src/main/php/shared/const/words.php` — never a hard-coded literal.

- **Right**: the test word in the import JSON is generated from
  `words::SOME_RESERVED_TEST_WORD`, and the same const drives the cleanup `del()`
  and any re-generation of the import file.
- **Wrong**: the import JSON hard-codes `"Heron"` while cleanup deletes
  `words::HERON` — they drift the moment the const changes, cleanup misses the
  row, later runs fail on a leftover duplicate.

When adding such a test: (1) add a reserved test entry to `words.php` (or the
matching `*_const` file) if none fits, (2) build the import file's name from that
const, and (3) use the same const for cleanup and for any regeneration script.

## The full load never contains data that needs a system user

`test/test_full_load.php` imports with the normal test user (`import_base_data($t->usr1)`
and `import_test_data($t->usr1)`), so **no file of `files::BASE_DATA_FILES`,
`BASE_DATA_PATH_FILES`, `SAMPLE_VIEW_DATA_FILES` or of the `test_files::TEST_DATA_*`
lists may contain data that only a system user may write.** That is `code_id` and
`ui_msg_code_id`: `sandbox_code_id::set_code_id()` and the component setters check
`user::can_set_code_id()` / `can_set_ui_msg_id()`, which pass only for the system, the
system-test and the developer profile. A `protection` needs no privilege and is fine.

The refusal is not a single message: the import stops at the object whose code id it
cannot write, so every later object of that file is reported as missing
("component with name … missing when importing json part …") and the real cause sits
in the first line of a very long error.

Data that needs a code id belongs in `files::SYSTEM_DATA_FILES`, which
`import_file::import_system_data()` imports as the system user. Keep it out of the full
load, and if a sample file mixes both, split the code-id part into the system data.

## Phrase id consts are re-baselined from the regenerated list.csv, never guessed

`src/test/php/const/{word,triple,verb}_names.php` pin the **seed database ids**
of the test phrases. Those ids come from the insert order of the import files, so
**adding or removing a phrase in a seed import shifts every id after it** — a new
word in `base_phrases.json`, a split of one multi-word word into a triple, an
extra file in `files::BASE_DATA_PATH_FILES`, all of them.

The cheapest re-baseline is the one that is not needed: a change to a seed
import stays as small as the task asks for, so that the object count of every
class stays the same. Never reformat a seed file and never add or remove a
word, triple or other object on the side — see *Change as little as the task
asks for* in `docs/llm/json_structure.md`.

The authority for the new ids is `src/test/resources/unit/<class>/list.csv`. That
file is not hand-maintained: `test_db_load::csv_recreate()` dumps the whole table
with `sql_db::csv_from_class()` after a database reset. So the sequence is
**reset → regenerate the csv → read the new ids out of it → update the consts**.
Never derive an id by counting entries in the JSON or by assuming a shift of one.

Two traps that make the drift look smaller than it is:

- `library::diff_msg` compares the **keys** of two arrays; a const id that still
  exists in the database under a *different* name is therefore not reported. A
  reported "5 missing / 5 unexpected" can be seven real drifts — always
  recompute the full name→id mapping instead of patching only what the message
  lists.
- The csv holds only rows created by the seed. A phrase created later by a test
  write (ids well above the seed range) is legitimately absent, so "not in the
  csv" does not mean "wrong const".

Appending to `verbs.json` is the one id-safe case: verbs are their own table and
their id is the insert position of each **distinct** `code_id`, so a new verb at
the end takes the next free id and moves nothing. (A repeated `code_id` merges
instead of inserting, which is why the const ids can sit one below their line
position in the file.)

## A fixture phrase carries the real id, the real class and the real name

Re-baselining the id const is only half of it: the **factory** has to match the
database row as well, in three more ways. All three fail silently — the test
renders *something*, just not the right thing.

**The id must be set, not only the name.** `combine_object::api_json_array()`
writes the `OBJECT_CLASS` field only `if ($obj->id() != 0)`, and the frontend
`phrase::api_mapper()` needs exactly that field to decide between a word and a
triple. So a fixture built with `set_name()` alone survives as a top-level
object but collapses to an empty phrase as soon as it is nested — as a triple's
`from`/`to`, or in a phrase list. The symptom is an anchor with no text and the
wrong tooltip (`<a href="…"></a>` instead of `<a … title="…">Swiss franc</a>`),
because `triple::get_link_by_verb()` falls through to its last-resort title.
Every phrase used as a side of another phrase therefore needs `set(<id>, <name>)`.

**The class must match the database.** When a multi-word word is split, the
phrase changes class: `Swiss franc` stops being a word and becomes the triple
`franc kind of Swiss`. The const then moves from `word_names` to `triple_names`
and the factory from `test_words` to `test_triples` — a word-typed stand-in that
merely carries the right *name* re-introduces the id-0 bug above. If the frontend
class lacks a method the test needs, add the method (see
"Behaviour shared by word and triple belongs on the phrase" in
`docs/llm/frontend.md`); do not keep the fixture in the wrong class.

**An unnamed triple's name is the generated one.** Most import triples carry no
`name`, so the database stores what `triple::generate_name()` produces, and that
is **not** `<from> <verb> <to>` for the `is` verb — it is `<from> (<to>)`:

```php
// the import file only has {"from": "Swiss franc", "verb": "is a", "to": "currency"}
$trp->set(triple_names::SWISS_FRANC_CURRENCY_ID, triple_names::SWISS_FRANC_CURRENCY);
//                                               // = 'Swiss franc (currency)'
// not: triple_names::SWISS_FRANC . ' ' . verbs::IS_NAME . ' ' . word_names::CURRENCY
```

Only the other verbs use the `<from> <verb> <to>` form. Read the name out of
`list.csv` together with the id rather than composing it.

**The description is copied verbatim from the owning import file.** A `*_COM`
const is not a place to write a shorter or nicer text: the same description ends
up in a rendered tooltip and in a database read, so a hand-written summary makes
the unit test and the read test disagree. Take the string from the file that owns
the phrase (the first one importing it) and paste it unchanged.

## A html snapshot page needs the local pod for its styles and fonts

A page under `src/test/resources/web/html/` loads its stylesheets from
`http://localhost/` (the `THIS_URL` fallback), so the rsync copy in `/var/www/html`
must be served there. The icons additionally need the font CORS header of
`external_lib/.htaccess`, because a `@font-face` request is always CORS-gated and a
snapshot is often opened from another origin (file system or ide preview server) —
without the header the icon glyphs stay invisible while everything else looks fine.
The one-time apache setup (AllowOverride, mod_headers): `docs/deployment.md`,
section *local web server for the api tests and the html snapshots*.

## Never edit an existing test resource — only add

Everything under `src/test/resources/` (HTML/SQL snapshots, dummy-cache JSON,
fixture CSVs, import files) is **read-only to a code change**. This includes every
previously created snapshot — e.g. the workflow files like
`src/test/resources/web/html/workflow/change_word_wf2/wf2_show_edit_back_edit.html`
and their `_url.txt` siblings. When your change makes one of these fixtures stale,
**do not** overwrite it — not by hand and not by flipping
`AUTO_UPDATE_TEST_FILES` to `true`. Leave the test failing and the fixture
untouched; the failing snapshot diff is exactly the evidence the next step needs.

**Why an LLM must never touch them**: the developer diffs the *committed* baseline
against the new test output to see precisely what the LLM's code change altered —
the snapshot is the human's audit trail of the change. If the LLM rewrites the
baseline to match its own new output, that diff goes silent and the developer can
no longer tell what changed (or whether it was intended). Regenerating a baseline
is the developer's deliberate act of accepting the new output, never the LLM's.

The switch `src/test/php/const/files.php::AUTO_UPDATE_TEST_FILES` **must always
stay `false` and must never be changed by an LLM** — not even temporarily and
reverted within the same change. Setting it to `true` is how the *existing
scripts* (run deliberately by a human) or a *human code reviewer* regenerate the
baselines; it is never part of an automated code edit. Treat the constant as
read-only just like the resource files it controls.

Existing resources are regenerated only by **the existing scripts** (the
`AUTO_UPDATE_TEST_FILES` mechanism, run deliberately) or by a **human code
reviewer**, who compares the regenerated fixture against the code change to
confirm the new output is intended. If the LLM both changes the code *and*
rewrites the fixture to match, that confirmation step is lost — the snapshot can
no longer disagree with the code.

You may freely **add** new resource files (a new `object_pages/<name>.html`, a
new SQL fixture for a new query, a new import file) — adding introduces no risk
of silently masking a regression in an existing baseline.

- **Right**: PHP change alters a rendered page → the matching
  `object_pages/*.html` test fails → you commit the code change with the test
  red (or the failure noted), and the reviewer/script regenerates the snapshot.
- **Right**: a brand-new renderer → you *add* its `object_pages/<name>.html`.
- **Wrong**: setting `AUTO_UPDATE_TEST_FILES = true`, running the suite to
  overwrite the stale snapshots, then committing the regenerated fixtures.
- **Wrong**: hand-editing a `views_by_id/*.html` or `db/**/*.sql` fixture so the
  assertion passes again.

### The page footer shows the minor version in a test run

The footer of every page contains the program version, and about 400 html snapshots
contain the footer. To keep a micro release (the build number raised with every commit)
from changing all of them, `test/test_const.php` defines `SYSTEM_TEST_RUN`, so that
`SYSTEM_PAGE_VERSION` (`src/main/php/cfg/const/env.php`) is the minor version `0.0.3`
instead of the micro version `0.0.3.0`. Never remove that flag and never write a version
into a snapshot by hand. A **minor** release does change the snapshots on purpose - the
version of the json format and of the database has changed - and then the developer
regenerates them. → `docs/llm/versions.md`

## Horizontal round-trip tests refill a not-transported field only when it is null

The horizontal ui tests (`unit_ui/horizontal_ui_tests.php`) round-trip a filled
backend object through the form url, the frontend mappers and the api json back
into a backend object and diff it against the original. A field that no form
transports (e.g. the user ip address, login times, type and status) is
backfilled from the original before the diff — but **only if the refilled value
is null**, meaning "not transported". An unconditional backfill would overwrite
a wrongly mapped real value with the expected one, so the diff could never
catch a mapper that fabricates or distorts the field (this is how the guest and
active default fabrication in `user::api_mapper` stayed unnoticed; see
docs/llm/constants.md "Default values are resolved at the point of use").

- **Right**:
```php
if ($refilled_obj->type_id === null) {
    $refilled_obj->type_id = $filled_obj->type_id;
}
```
- **Wrong** — masks a mapper that sets a wrong non-null value:
```php
$refilled_obj->type_id = $filled_obj->type_id;
```

## Page-based UI tests for component-type renderers

Every UI rendering function dispatched from `web/component/component_exe.php` —
every arm of its `match ($tc_id)` block, e.g. `admin_jobs_delayed()` in
`web/component/execute/system_page.php` — must have a page-based test capturing
its HTML output. Tests live in `src/test/php/unit_ui/<topic>_ui_tests.php`,
invoked from a `run(test_cleanup $t): void` method that snapshots the rendered
fragment via `$t->html_page_test(...)`.

- **Right**: `admin_jobs_delayed()` is exercised by `job_ui_tests::run()`, which
  builds a small page (heading + rendered HTML) and snapshots it.
- **Wrong**: adding a new component-type renderer to the `component_exe.php`
  match arm without a corresponding `*_ui_tests::run()` entry.

Pick the topic file by domain (jobs → `job_ui_tests`, sources →
`source_ui_tests`, references → `reference_ui_tests`). When a renderer fits no
existing topic, add a new `*_ui_tests.php` rather than overloading an unrelated
one.

### Every HTML-returning function in a frontend class contributes to an `object_pages` snapshot

The page-based rule isn't limited to `component_exe.php` match arms — it applies
to **every function in `src/main/php/web/`** whose return type is HTML. For each,
a unit test appends a fragment to the `$test_page` that a `*_ui_tests::run()`
snapshots into `src/test/resources/web/html/object_pages/<name>.html`. A frontend
HTML-returning function with no entry in any `object_pages/*.html` snapshot is
untested for review — its output drifts silently when the html, css class, or
wrapped renderer changes.

When the same new renderer must be exercised across many object pages (a generic
title/footer appearing on `word`, `triple`, `formula`, …), do **not** copy three
lines into ten test files. Add a reusable helper in
`src/test/php/utils/test_base.php` and have each `*_ui_tests::run()` call it with
its main `db_object`. This keeps the per-file change to one line.

- **Right (worked example — TITLE_NAMED_EDIT, component type id 192)**: the new
  `system_form::title_of_named_with_edit_link(db_object $dbo)` renderer is
  exercised by the helper `test_base::dsp_title_named_edit(db_object $dbo)` which
  wraps the renderer with an `h2` heading. Each of `word_ui_tests`,
  `triple_ui_tests`, `formula_ui_tests`, `verb_ui_tests`, `view_ui_tests`,
  `component_ui_tests`, `source_ui_tests`, `reference_ui_tests`, `value_ui_tests`
  and `result_ui_tests` appends `$test_page .= $t->dsp_title_named_edit($main_dbo);`
  immediately before its `html_page_test(...)` call, so the renderer ends up in
  all ten `object_pages/{...}.html` snapshots in one shot.
- **Wrong**: adding a new HTML-returning function without any matching
  `*_ui_tests` change, so no `object_pages/*.html` covers its output.

When the new renderer doesn't apply to an object type (e.g. no `VIEW_EDIT`
constant the renderer relies on), skip that test rather than forcing the call —
and either add the missing piece to the object (preferred, so all object pages
stay consistent) or document the skip in the helper's docblock.

## Unit workflow tests snapshot every step

A unit workflow test (`src/test/php/unit_workflow/`) simulates a sequence of user
actions and **saves and checks the rendered HTML after every step** against a
fixture under `src/test/resources/web/html/workflow/`. Every intermediate page is
a snapshot (compared with `assert_html_page`, i.e. through `assert_file`), so a
workflow whose HTML drifts is caught exactly like an `object_pages` snapshot.

Naming is fixed so the fixture for any step is mechanical to locate:

- Each workflow has a **unit name** (e.g. `change_word`) and a **unit id** (e.g.
  `2`); its folder is `<name>_wf<id>`, so `change_word` id `2` →
  `src/test/resources/web/html/workflow/change_word_wf2/`.
- Inside that folder every page filename starts with `wf<id>` followed by the
  **cumulative** user-action names joined by `_`. The first action `show`
  (display the test word in its default word view) gives `wf2_show`; after the
  further actions `edit`, `save`, `confirm` the page is
  `wf2_show_edit_save_confirm`. Each step appends its action to the previous
  step's filename — never a per-step standalone name.
- A user action is passed as a **named const**, never a bare string, and that
  const is the **first parameter** of the `url_user_reaction` call that performs
  the step. The same action const names the segment appended to the snapshot
  filename.

- **Right**: the `save` step is driven by `url_user_reaction(<action const>, …)`
  and its HTML is checked with `$t->assert_html_page($test_name, $html,
  test_paths::HTML . 'workflow/change_word_wf2/wf2_show_edit_save')` (the path is
  relative to the test resource root; `assert_html_page` adds the `.html`
  extension).
- **Wrong**: asserting only the final page (skipping the intermediate
  `wf2_show`, `wf2_show_edit`, … snapshots), passing the action as a literal
  string, or naming a step file by its action alone (`wf2_save`) instead of the
  cumulative path.

Never overwrite an existing `workflow/` fixture to make a step pass — leave it
failing for the scripts / reviewer to regenerate (see the resource-file rule
above).

Write workflows use the parallel folder `workflow_write`. A read-only workflow
test (`src/test/php/unit_workflow/`, run with `do_it = false`, no database
change) snapshots into `src/test/resources/web/html/workflow/`; the matching
**write** workflow test (`src/test/php/unit_write_workflow/`, run with
`do_it = true` so the change is actually persisted) snapshots into
`src/test/resources/web/html/workflow_write/` with the **same**
`<name>_wf<id>/wf<id>_<cumulative-actions>` folder-and-file structure. The two
folders mirror each other so the read-only and write runs of the same workflow
are easy to compare; the write run additionally proves the database side
effect of the confirmed step.

## Every machine-checkable coding rule has a coded test

A coding rule in `docs/llm/` is only enforced when there is an automated test
that fails when the rule is violated. Reviewer attention is not a substitute —
the rule must be expressed as code in
`src/test/php/unit/coding_rule_tests.php` (run via
`test/test_coding_rules.php`), so every PR sees the violation before merge.

When you add or change a rule whose violations can be detected by static
inspection of the source tree (file layout, global usage, naming, section
order, forbidden patterns), add the matching check in the same change. Without
it the rule is a wish, not a rule.

Worked examples of rules that belong here:

- **Frontend globals** — files under `src/main/php/web/**` may only read the
  frontend-scoped globals `$ui_sys` and `$mtr` (see
  `docs/llm/state-and-messages.md`). The test greps the tree for `global $sys`,
  `global $db_con`, `global $cfg`, `global $cac` in `web/**` and fails on any
  hit. Mirror checks for backend (`src/main/php/cfg/**` must not read
  `$ui_sys`/`$mtr`) and tests (`$t`/`$t_sys` only in `src/test/**`).
- **Allowed-global set is closed** — fail if a `global $X` appears anywhere with
  `$X` outside the table in `state-and-messages.md`.
- **No PHP superglobals inside functions** — fail on `$_GET`/`$_POST`/`$_SESSION`
  reads outside `http/*.php` entry points.
- **Frontend icons come from `web/const/icons.php`** — fail on inline
  `fas fa-*` strings in `web/**` (already a documented rule).
- **`AUTO_UPDATE_TEST_FILES` is `false`** — fail the run if
  `files::AUTO_UPDATE_TEST_FILES` is `true`, so a forgotten (or LLM-introduced)
  `true` cannot land on a branch.

Each check is one positive + one negative test in `coding_rule_tests.php` (the
positive proves the check catches a known bad fixture line; the negative proves
it tolerates a known good fixture line), matching the per-function discipline
above.
