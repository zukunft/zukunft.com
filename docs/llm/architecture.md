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
- **component** — parts of a display mask

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

**Inheritance chain**:
```
db_object → db_object_seq_id → db_object_seq_id_user → sandbox → sandbox_named → sandbox_typed → word/formula/view/...
                                                              → sandbox_link → triple/formula_link/component_link/...
                                                              → sandbox_value → value/result
```

**DB abstraction**: `sql_db` wraps both PostgreSQL and MySQL. SQL statements are
built by `sql_creator` using `sql_par` (parameters), `sql_type` (query types),
and `sql_where` objects — never by string concatenation in business logic.

**API layer**: Backend objects produce JSON via `api_json()` for the frontend.
Frontend `web/` objects consume these via `api_mapper()`. Import/export JSON uses
names (never DB IDs) for portability between pods.

**Path constants**: All file paths are class constants in
`src/main/php/cfg/const/paths.php` (backend) and
`src/main/php/web/const/paths.php` (frontend). `ROOT_PATH` is set in
`test/test_const.php` or equivalent entry points.

**Namespace**: `Zukunft\ZukunftCom\` (PSR-4, maps to `src/`).

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
10. im- and export — `export_json()`, `import_mapper()`
11. save — `save()`, `insert()`, `update()`, `delete()`
12. sql write — `sql_insert()`, `sql_update()`, `sql_delete()`
13. info / internal / debug — `name()`, `dsp_id()`, helpers

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

## Naming conventions

Short variable prefixes (see also `docs/code_guidelines.md`). The canonical,
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
