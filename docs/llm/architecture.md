# Architecture & reference

Reference material — read when navigating unfamiliar code or when a naming /
structure rule in `CLAUDE.md` applies. Not an always-on rule set.

## Project overview

zukunft.com is a "Giant Global Graph" browser — a PHP/PostgreSQL web app that
lets users build personal OLAP cubes using words, triples (RDF-like
subject-verb-object), formulas, and values. The core concept is "calculating
with words." Requires PHP 8.4+ and PostgreSQL (MySQL also supported). Minimum
server: LAPP/LAMP stack.

## Source layout

```
src/main/php/
  cfg/          ← backend model (domain objects, DB layer)
    db/         ← SQL abstraction: sql_db, sql_creator, sql_par, sql_type, etc.
    sandbox/    ← user-sandbox superclass hierarchy
    word/       ← word, triple (+ _db, _list variants)
    phrase/     ← phrase, term, group (aggregations of words/triples)
    verb/       ← predicates connecting two words
    value/      ← numeric values set by users
    formula/    ← calculation rules
    result/     ← calculated formula results
    view/       ← display masks
    component/  ← parts of a view
    ref/        ← external references and sources
    user/       ← user accounts and permissions
    log/        ← change logging
    const/      ← path constants (paths.php), definitions
    helper/     ← base class hierarchy (db_object, db_object_seq_id, etc.)
    system/     ← system-level objects and jobs
  shared/       ← shared between backend and frontend (enums, types, library)
  api/          ← API message layer (api_message, controller, ui_config)
  web/          ← HTML frontend (mirrors cfg/ structure)
    html/       ← base HTML helpers (html_base, button, table, etc.)
    word/, verb/, view/, ... ← per-object frontend classes
    const/      ← frontend path constants
  service/      ← services (config, math)
  utils/        ← utilities

src/test/php/
  unit/         ← pure unit tests (no DB)
  unit_read/    ← DB read tests
  unit_write/   ← DB read+write tests
  unit_ui/      ← page-based UI snapshot tests
  PHPUnit/      ← PHPUnit-compatible tests (nascent)
  utils/        ← test infrastructure (test_base, test_cleanup, all_tests)
  create/       ← test object factories (test_words, test_verbs, etc.)
  const/        ← test path constants

src/main/resources/
  db/setup/     ← DDL SQL for initial DB setup
  db/upgrade/   ← incremental upgrade scripts
  db_code_links/← CSV files mapping code_ids to DB types/actions/fields
  application.yaml, config.yaml ← system configuration
  messages/, translations/ ← translation strings
  openapi/      ← API spec

http/           ← HTTP-accessible PHP pages (login, word_add, value_edit, etc.)
api/            ← external API endpoints
test/           ← test runner entry points
```

## Domain object terminology

These nouns have precise, non-interchangeable meanings — use them exactly:

- **word** — a single word, used for better assignments
- **verb** — a predicate to connect two words
- **triple** — combine two words or triples with a verb
- **source** — import-only data source
- **ref** — im- and export to external systems
- **value** — a number for calculation
- **group** — a list of words or triples
- **formula** — an expression for calculation
- **result** — the numeric result of a formula
- **view** — a named display mask
- **component** — a part of a display mask
- **element** — a part of a formula (`cfg/element/element.php`), never a part of a view

A part of a view is a **component** — in the code, in a comment and in every text
shown to the user. Never call it an *element*: `element` is the cached part of a
formula expression, so "view element" reads as if a formula were meant. The html
`<div>`, `<input>` and `<select>` tags stay *elements*, because that is what the
html standard calls them, and a *list element* is fine where a list is meant.

Two collective nouns build on the above and must not be confused:

- **phrase** = a **word** or a **triple** (the things combined into a group and used to address a value).
- **term** = a **word**, **verb**, **triple**, or **formula** (everything that can appear in a formula expression).

Every phrase is a term, but a verb and a formula are terms that are **not**
phrases. When describing why two objects of different classes must not share a
name or id, pick the noun that actually covers both classes — e.g. a triple and
a formula are both *terms* (not *phrases*, because a formula is not a phrase).

## Key architectural patterns

**User Sandbox**: Every main object (`word`, `triple`, `value`, `formula`,
`view`, `component`) extends the `sandbox` hierarchy. Changes by one user never
overwrite shared data; user-specific overrides are stored in `*_user` tables.

**Admin protection does not block user changes**: an object protected at admin
level (or higher) can still be changed by a normal user — the change creates
the user's own sandbox overlay like any other edit. The only thing the admin
protection protects is the standard object of the owner: a normal user cannot
take over the ownership (`sandbox::take_ownership`) and cannot raise or reduce
the protection level (`sandbox::check_protection` in the save path). So never
show a "can be changed only by an administrator" style message just because an
object is admin protected: on a display view the user does not want to change
anything, so the message is irrelevant, and on an edit view it is wrong because
the user *can* change the object (as a personal overlay). The protection
messages that are correct to show are the save-path warnings when a normal
user tries to change the protection level itself (`PROTECTION_RAISE_DENIED`,
`PROTECTION_REDUCE_DENIED`).

**Configuration follows the user sandbox**: `config.yaml` is only the seed of
the system configuration, which lives in the database as normal values on the
config phrases (`config_numbers`, the global `$cfg`); beside it the low-level
program `config` table (database version, site name) works even when the rest
of the database is broken. Configuration values are sandbox values, so **a
normal user can change configuration values too** — the change creates the
user's own overlay like any other value change, and only the pod-level keys
(the admin keywords and triples in `config_numbers`) are admin protected.
A function that writes a configuration value therefore never assumes who is
asking (e.g. never hard-wires the system user); it takes the requesting user
from the caller — via the `user_message` or a `user` parameter — and lets the
normal permission checks decide.

**Inheritance chain**:
```
db_object → db_object_seq_id → db_object_seq_id_user → sandbox → sandbox_named → sandbox_typed → word/formula/view/...
                                                              → sandbox_link → triple/formula_link/component_link/...
                                                              → sandbox_value → value/result
```

**DB abstraction**: `sql_db` wraps both PostgreSQL and MySQL. SQL statements are
built by `sql_creator` using `sql_par` (parameters), `sql_type` (query types),
and `sql_where` objects — never by string concatenation in business logic.

**DB read result contract**: `sql_db::get1()` (and the `fetch*` functions behind
it) distinguishes three results on purpose: an **array** is the row found,
**null / an empty array** means no row matched (the normal "not found"), and
**`false`** means the query itself failed — e.g. a corrupted database or a
select against an outdated schema. Never convert the `false` to null inside the
db layer: it is the signal that lets the calling function show the user a
helpful error message and log the problem instead of dying with a fatal error.
A load function therefore guards the `false` case *before* passing the row to
`row_mapper(?array)` (feeding `false` into the mapper is a TypeError, which is
exactly the fatal break this contract is meant to prevent).

**Database cache (`db_cache`)**: precollected api json (system config, frontend
config, user config, system types, …) so that a request does not have to rebuild
it from the single values. Three rules hold for every cache entry:

- **One entry per cache type and user — unless the content is the same for
  everybody.** The config values are sandbox values that each user can
  overwrite, so a config cache entry is only valid for the user it was written
  for (`db_cache::load_by_type_and_user`). The types and system views are not
  user specific, so `ui_config` keys its entry by type alone — and it must:
  `rest_call::api_curl_call` sends **no session**, so every api call arrives as
  the ip user and a per-user entry would miss on every single call. Before
  keying a cache by user, check who actually reaches that api script. The other
  side of the coin: an entry shared by all users must not contain user-specific
  data — an api message header carrying the writing user's id and name has to be
  rebuilt per call instead of being cached with the body.
  A write loads the existing entry of that type (and user) first
  and updates it — creating a fresh `db_cache` object and calling `save()` adds
  a *second* row for every request, because `save()` inserts whenever the object
  has no db id.
- **`last_update` is the time of the data snap, never the time of the write.**
  Take the timestamp *before* reading the data that is being cached and store
  that: a change made while the data is being read is then outside the snap and
  the next reader refreshes, instead of the cache claiming to contain a change
  it missed. The same holds when the cache age is compared to a source file
  (`config_numbers::is_file_cache_valid` compares the cache file mtime with the
  `config.yaml` mtime). `db_cache::is_outdated()` is the single place that
  decides whether an entry is still young enough (`CACHE_MAX_AGE`) to be used.
- **A cache entry is used only if it is not outdated.** Do not check validity
  only when the read fails — that inverts the logic and serves stale data
  forever.
- **Every cache has a pod switch.** Each `db_cache_types` row and the
  `db_cache_pages` table can be switched off in `config.yaml`
  (`database > cache > ... > allowed`); the phrase names per switch are the
  `config_numbers::CACHE_ALLOWED_NAMES` / `CACHE_PAGES_ALLOWED_NAMES` consts and
  `config_numbers::cache_allowed()` / `page_cache_allowed()` answer them. A pod
  without the switch uses the cache — only an explicit `false` switches it off
  (e.g. to debug with always fresh data), so the accessors use no `get_by`
  fallback (it would overwrite the explicit false). A new switch's name set must
  not be a subset of any other switch's set, because the config lookup is a
  contains-all match (`value_base::match_all`).

**The config api message contains all config values**: `config_numbers::load_cfg`
loads the complete config tree and sends it with its phrases, also for the
frontend and the user config part; the part only selects the cache entry.
The extra context is expected to be useful for the frontend and the complete
config is expected to stay small. `value_list::filter_by_phrase` is ready to
reduce the message to one config part if the config ever gets too big — it must
then be called *after* `load_phrases()`, because it matches on the loaded
`$val->phr_lst`.

**The api type list stays a caller parameter, also where only one list is cached**:
a cache entry may only be given to a caller that asked for exactly the content
that was cached, so a cached api endpoint compares the requested type list with
the cached one and skips read *and* write on any other list rather than serving
the wrong shape. Keep the list a parameter anyway — it is the switch for the two
directions such a message is expected to move in: **faster** (drop
`api_types::HEADER` and the message loses the pod name, class, user id and name,
version and timestamp — smaller for a frontend that already knows all of it) or
**safer** (keep the header so every response says which pod, program version and
user it was created for, which lets the frontend detect a message meant for
somebody else or for an outdated pod). When a second list becomes worth caching,
cache it under its own `db_cache` type — never widen the match.

**API layer**: Backend objects produce JSON via `api_json()` for the frontend.
Frontend `web/` objects consume these via `api_mapper()`. Import/export JSON uses
names (never DB IDs) for portability between pods.

**Frontend and backend are two independent apps**: the target architecture is a
frontend and a backend that are complete and independent of each other, talking
only over the API. The frontend renders pages and never reaches into the backend
except through an api call; the backend answers api calls and knows nothing about
html. Each must be deployable, startable and testable on its own — the frontend
must be able to render against a *remote* backend pod, and the backend must be
able to serve a different frontend (e.g. the planned JS app) without a change.

This splits the request bootstrap in two, and the split is deliberate:

| | `web/frontend.php` | `cfg/application.php` |
|---|---|---|
| serves | the pure html php frontend | the backend, i.e. the api calls |
| entry points | `http/view.php`, `about.php`, `setup.php` | `api/**` scripts |
| lifecycle | `frontend::start()` / `frontend::end()` | `application::start()` / `start_api()` / `end()` |

Use `frontend.php` **only** from the html frontend and `application.php` **only**
from the backend api. Because both bootstrap a request, they overlap today —
session start and hardening, TLS enforcement, the session token, opening the
database and the timing switches exist in both, and `frontend.php` still has the
deprecated direct-DB bootstrap that the API is meant to replace. Treat that
overlap as the cost of the not-yet-finished split, not as a reason to merge them:
the duplication disappears when the frontend no longer opens a database at all.

The practical trap: the two classes have **same-named methods with different
signatures** (`start()` takes `(code_name, echo_env, restart)` in `application`
but `(code_name, msg, url_arr)` in `frontend`). Anything added to one silently
misses the other, so a change to the request lifecycle — a new guard, a debug
message, a timing switch — must be applied to both until the split is complete.

**Path constants**: All file paths are class constants in
`src/main/php/cfg/const/paths.php` (backend) and
`src/main/php/web/const/paths.php` (frontend). `ROOT_PATH` is set in
`test/test_const.php` or equivalent entry points.

**Namespace**: `Zukunft\ZukunftCom\` (PSR-4, maps to `src/`).

**Change log — special case for `sys_log`**: Every other sandbox object writes
its insert *and* every later change to the change log (`sql_type::LOG`). A
`sys_log` row is the exception in both directions:

- **Insert is never logged.** A new `sys_log` row is the change record itself
  (an error/warning the system just noticed). Writing a "we wrote a sys_log
  row" entry into the change log would log the log — noise that buries the
  signal and inflates the change table without telling anyone anything new.
- **Every update is logged.** Once a `sys_log` row exists, a status change
  (e.g. an operator marks it `closed`) is a *human-meaningful* state change
  that needs the same audit trail as any other domain edit — who changed it,
  when, and what the previous value was — so investigations later can
  reconstruct the operator's action.

`sys_log::save()` encodes this by branching on `has_db_id()`: it tags the
insert path with `sql_type::NO_LOG` and the update path with `sql_type::LOG`.
The short-circuit `sys_log::insert()` entry point (used by the in-process
error logger at `cfg/log_text/text_log_functions.php`) calls `sql_insert(...)`
with the empty `sql_type_list` default, which `do_log()` reports as `false` —
same effective semantics. Don't add a `sql_type::LOG` flag at either insert
site, and don't drop the `LOG` flag from `save()`'s update branch.

**Change log field registry**: a logged write (`sql_type::LOG`) can only
reference fields that are registered in
`src/main/resources/db_code_links/change_fields.csv`, keyed by the table id
from `change_tables.csv`. When a logged insert or update fails for a single
field — e.g. `log_err` reports `Cannot add field name "<field>" for table id
<n>` (raised in `cfg/log/change_log.php`), or the code-link load of a type
table breaks on an extra column — check first that the field has a
`change_field_id,<field>,<table_id>` row in `change_fields.csv` and add it
with the next free id if missing. Example: logged writes of
`sys_log_statuum.action` only worked after adding `888,action,114`
(114 = `sys_log_statuum` in `change_tables.csv`). The same applies whenever a
new loggable column is added to any table: register it in `change_fields.csv`
in the same change.

## Standard object sections (in file order)

Each main object file follows this section order:
1. db const — DB field name constants (often moved to a `*_db` companion class)
2. preserved — system-reserved names
3. object vars — properties in DB field order
4. construct and map — `row_mapper()` from DB row
5. set and get — property accessors
6. preloaded — type/cache access
7. load — DAO functions (`load_by_name`, `load_by_id`, etc.)
8. load sql — SQL statement builders
9. cast / api — `api_json()`, `api_mapper()`
10. im- and export — `export_json($msg, )`, `import_mapper()`
11. save — `save()`, `insert()`, `update()`, `delete()`
12. sql write — `sql_insert()`, `sql_update()`, `sql_delete()`
13. info / internal / debug — `name()`, `dsp_id()`, helpers

**Within each section, order the functions top down**: the public, frequently
called entry points at the top, the rarely used and private helpers they
delegate to at the bottom (e.g. `load_by_phrase` before `load_sql_by_phrase`).
A reader scanning a section meets the high-level function before the detail it
calls, and the most common entry point is found first.

## Separate loading from saving — the save path never loads

Reading data from the database and writing it back are concentrated in their own
functions and kept clearly separated. The **only** database read on the save path
is the initial reload at the top of `save()` — `get_similar()` and the filling of
`$db_rec` (the database record as it was before the change). After that, every
function `save()` reaches — change detection, the SQL builders, the change-log
helpers — works purely on the in-memory objects and that already-loaded `$db_rec`;
none of them load from the database.

Concretely, `db_fields_changed()` (and the `add_user` / `add_link_field` /
`add_type_field` helpers it calls) must never call a `load_*` function: the names
and ids it logs come from the objects already in memory. If a referenced object's
name is missing at save time (e.g. a triple's from/to phrase loaded by id only),
fix the **load** that built the object so it arrives complete — never load inside
the save path to patch it up.

Why: a stray load during save makes the write depend on database state mid-change,
hides ordering bugs (an object reaching `save()` half-loaded), and couples the two
responsibilities so neither can be reasoned about or tested in isolation.

## The readiness ladder — an object without a db id is normal, not invalid

A list can be a place where objects live **before** they are written, so an object
without its own database id belongs in it. The id of a row is assigned by the
insert; requiring it earlier would mean nothing could ever be prepared in memory.
Three different questions are therefore asked with three different functions, and
mixing them up is the recurring defect:

| Question | Function | True when |
|---|---|---|
| may it be held in a list / could it become writable? | `can_be_ready($msg)` | the objects it points to exist (a name is enough); their ids may still be missing |
| may it be written **now**? | `db_ready($msg)` | every object it points to has a database id; its **own** id is *not* required |
| has it been written already? | `is_valid()` / `id() != 0` | the row exists in the database |

For a link (`triple`, `term_view`, `component_link`, `formula_link`, `ref`) this
means precisely:

- **own id missing, linked object ids set** → `db_ready` is **true**: this is a
  new link, and the insert is what gives it its id.
- **a linked object id missing** → `db_ready` is **false**: the link cannot be
  written, because there is nothing to point at. It stays in the list and the
  reason is recorded on `$msg`.
- the linked objects are saved by their own save pass; when they come back with
  ids, the next pass finds the link `db_ready` and writes it. An import therefore
  runs several passes, and "not ready yet" can be a **normal** intermediate state, not
  an error — see `docs/llm/dependent-errors.md` for why that notice must not reach
  a caller's `is_ok()` gate. "not ready yet" is only an error if this message remains after the last try.

`*_list::get_ready()` e.g. `triple_list::get_ready()` is the pattern to copy: it filters by `db_ready()` and
collects with `add_by_key()` — for named objects a **name based** key, the object's own id only if set already. For non named objects a unique key is generated base on other fields.
`list_db_write::sql_insert_call_with_par()` re-checks `db_ready()` as the second
line of defence right before the insert is built.

So list membership and duplicate detection are decided by the object's key (its
name, or the linked objects of a link), and only the write is decided by
`db_ready()`. A list that gates membership on the object's own id silently drops
everything an import prepares — and if the add still reports "added", the caller
cannot even see it (the open case in `docs/llm/pending_prio_2.md`).

## A list is not a set — a repeated entry can be the data

A list may hold the same object more than once, and where it does the repetition
*is* the information:

- a **view** uses the same **component** several times (once per position), so
  the component list of a view legitimately contains one entry per usage;
- a **verb list** built from a **triple list** counts how often each verb is
  used, so the same verb appears once per triple.

The duplicate check is therefore a **parameter of the add, never a fixed rule**:
`add_obj($obj, $allow_duplicates, $msg)` (`shared/helper/ListOfIdObjects.php`,
`web/sandbox/sandbox_list_named.php`, `web/types/type_list.php`).

- `$allow_duplicates = false` — the list is a set: the repeat is refused and
  reported as `msg_id::LIST_DOUBLE_ENTRY`. This is the default, because most
  lists map unique database rows.
- `$allow_duplicates = true` — the repeat is the data: it is added and **nothing
  is reported**; a double entry message here would be a false alarm.

The caller decides, because only the caller knows what the list means: a list
filled from an api message of unique rows passes `false`, a usage or position
list passes `true`. Never hard-code the check inside the list class.

Both branches are behaviour, so **both need a test**: one that the repeat is
refused and reported, and one that it is kept and silent (`testing.md`, "a
positive and a negative test for every feature").

## Standard function names

| Function | Purpose |
|---|---|
| `load_by_*` | Load object from DB by a unique key |
| `save` | Insert or update in DB (top-level) |
| `del` / `remove` | Delete or exclude object |
| `row_mapper` | Populate object from DB row |
| `api_mapper` | Populate object from frontend API JSON |
| `api_json` | Serialize to frontend API JSON |
| `export_json` | Serialize for pod-to-pod export (uses names, not IDs) |
| `dsp_id` | Debug string with name + IDs (never calls debug functions itself) |
| `name` | User-facing object name |
| `sql_insert` / `sql_update` / `sql_delete` | Create SQL statement objects |

**Spell function names out in full** — `load_by_phrase_list`, not `load_by_phr_lst`.
This is the opposite of the variable rule below: variables use the 3-letter
abbreviations, but a function name is read at a call site that already costs far
more (a call is a real operation) than the few extra characters, so the clarity
of the full word wins. So use the spelled-out object name in `load_by_*` and
similar verbs (`load_by_phrase`, not `load_by_phr`); reserve the abbreviations
for variables.

**Name a thing by the broadest concept that already covers its parts** — don't
enumerate items that a single term already implies. A list shown "without
subtitles" needs no "and symbols" suffix because the subtitle category already
includes the symbols; `related phrases without subtitles` beats `related phrases
without symbols and subtitle`. Shorter and one fewer thing to keep in sync when
the parts change.

## Naming conventions

Short variable prefixes (see also `docs/llm/coding.md`). The canonical,
complete registry of these names is the property list in
`cfg/helper/type_lists.php` — when you add a preloaded type there, add its
abbreviation here too, and reuse an existing one rather than inventing a new
spelling for the same object.

Core objects:
- `wrd` word, `val` value, `frm` formula, `vrb` verb, `trp` triple
- `phr` phrase, `grp` group, `trm` term, `res` result, `src` source, `ref` reference
- `msk`/`cmp` view/component, `usr` user, `sc` sql_creator, `cac` cache
- `shr` share, `ptc` protection, `lan` language, `job` job
- `sty` style, `pos` position, `mrl` view relation, `elm` element
- `cng` change (change log), `sys` system

Combinable parts (suffixes / second components):
- `lst` list, `typ` type, `lnk` link, `sta` status, `lvl` level
- `act` action, `tbl` table, `fld` field, `fnc` function
- `pro` profile, `for` form

Variable names should be one of these 3-letter abbreviations or a combination —
never the spelled-out object name. Combine by joining parts: `$t_wrd` (test +
word factory), `$frm_lnk` (formula + link), `$phr_lst` (phrase + list),
`$shr_typ` (share + type), `$ptc_typ` (protection + type). Only `$i` is allowed
as a single-character name, for loops.

Object file suffixes:
- `*_db.php` — DB field constants for an object
- `*_list.php` — collection class
- Frontend (`web/`): `*_dsp` display class, `*_min` minimal API

### Suggested variable name in class header

Every class file declares its suggested variable name in the opening file
docblock, on its own line immediately after the one-line description:

```
    web/sandbox/sandbox_link.php - extends the frontend sandbox object for links
    ----------------------------

    $sbx_lnk is the suggested var name
```

The abbreviation matches the 3-letter prefix convention (`$wrd` word, `$frm`
formula, `$msk` view/mask). For compound names combine the parts: `$sbx_lnk`
sandbox_link, `$frm_lnk` formula_link, `$cmp_lnk` component_link.

### Use the suggested var name — deviations are the exception

Once a class declares its suggested var name, **every** variable holding an
instance of that class uses it. Deviate only for a genuinely good reason — the
common one is that **two instances of the same class share a scope** (`$src1` and
`$src2`, `$frm_this` and `$frm_next`, a `$db_rec` reload compared against the
object), where a second, meaningful name is clearer than `$src` / `$src2`. "It
read fine at the time" is not a reason; reach for the suggested name first.

The check `coding_rule_tests::php_class_name_check` scans the source and writes
every deviation to the generated `docs/code_object_name_exceptions.md` (never edit
that file by hand — it is regenerated from the code). That list is the scoreboard:
it must stay **short**, and a rename that removes a line from it is the right
direction. A class whose exception list is long signals variables that should be
renamed back to the suggested name.

A `user_message` is always **`$msg`** — the single most-passed object in the code
(the append-only message threaded from the entry point, see
[state-and-messages.md](state-and-messages.md)). Do not introduce `$usr_msg`,
`$sys_msg`, `$db_msg` and the like for a lone message in a scope; use `$msg`. A
second message buffer that genuinely coexists with `$msg` in one function (a local
buffer merged back into the threaded `$msg`) is the sanctioned deviation and keeps
a distinct name.
