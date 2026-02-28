# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

zukunft.com is a "Giant Global Graph" browser — a PHP/PostgreSQL web application that lets users build personal OLAP cubes using words, triples (RDF-like subject-verb-object), formulas, and values. The core concept is "calculating with words."

Requires **PHP 8.4+** and PostgreSQL (MySQL also supported). Minimum server: LAPP/LAMP stack.

## Running and Testing

### Docker (preferred for development)
```bash
docker-compose up -d
# App at http://localhost:8080/http/view.php
# Adminer at http://localhost:8081
```

### Direct install on Debian
```bash
sudo ./install.sh
```

### Tests (browser-based — run via HTTP, not CLI)
Tests are accessed via the web server. The main entry points in `test/`:
- `test/test_unit.php` — unit tests only (no DB writes)
- `test/test.php` — all tests (unit + integration, includes DB read/write)
- `test/test_coding_rules.php` — code consistency checks
- `test/test_horizontal.php` — horizontal (cross-object) tests

To run a specific test class, the test files in `src/test/php/unit/`, `src/test/php/unit_read/`, and `src/test/php/unit_write/` are included by the top-level runners. The `a_selected_test.php` file in the PHPUnit directory can be used to run a single selected test.

### Composer
```bash
composer install       # install dependencies
composer dump-autoload # refresh PSR-4 autoloader
```

## Architecture

### Source Layout

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
  PHPUnit/      ← PHPUnit-compatible tests (nascent)
  utils/        ← test infrastructure (test_base, test_cleanup, all_tests)
  create/       ← test object factories (test_words, test_verbs, etc.)
  const/        ← test path constants

src/main/resources/
  db/setup/     ← DDL SQL for initial DB setup
  db/upgrade/   ← incremental upgrade scripts
  db_code_links/← CSV files mapping code_ids to DB types/actions/fields
  application.yaml, config.yaml ← system configuration
  messages/     ← translation strings
  openapi/      ← API spec

http/           ← HTTP-accessible PHP pages (login, word_add, value_edit, etc.)
api/            ← external API endpoints
test/           ← test runner entry points
```

### Key Architectural Patterns

**User Sandbox**: Every main object (`word`, `triple`, `value`, `formula`, `view`, `component`) extends the `sandbox` hierarchy. Changes by one user never overwrite shared data; user-specific overrides are stored in `*_user` tables.

**Inheritance chain**:
```
db_object → db_object_seq_id → db_object_seq_id_user → sandbox → sandbox_named → sandbox_typed → word/formula/view/...
                                                                 → sandbox_link → triple/formula_link/component_link/...
                                                                 → sandbox_value → value/result
```

**DB abstraction**: `sql_db` wraps both PostgreSQL and MySQL. SQL statements are built by `sql_creator` using `sql_par` (parameters), `sql_type` (query types), and `sql_where` objects — never by string concatenation in business logic.

**API layer**: Backend objects produce JSON via `api_json()` for the frontend. Frontend `web/` objects consume these via `api_mapper()`. Import/export JSON uses names (never DB IDs) for portability between pods.

**Path constants**: All file paths are defined as class constants in `src/main/php/cfg/const/paths.php` (backend) and `src/main/php/web/const/paths.php` (frontend). The root constant `ROOT_PATH` is set in `test/test_const.php` or equivalent entry points.

**Namespace**: `Zukunft\ZukunftCom\` (PSR-4, maps to `src/`)

### Standard Object Sections (in file order)

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

### Standard Function Names

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

### Naming Conventions

Short variable prefixes (from `docs/code_guidelines.md`):
- `wrd` word, `val` value, `frm` formula, `vrb` verb, `trp` triple
- `phr` phrase, `grp` group, `trm` term, `res` result, `src` source, `ref` reference
- `msk`/`cmp` view/component, `usr` user, `sc` sql_creator, `cac` cache
- `lst` list, `typ` type, `lnk` link, `elm` element

Object file suffixes:
- `*_db.php` — DB field constants for an object
- `*_list.php` — collection class
- Frontend (`web/`): `*_dsp` display, `*_min` minimal API

### Deployment Branch Strategy

`feature/*` → `develop` → `release` → `master`

Commit messages reference issue numbers: e.g. `fix auth flow as part of fix #232`.

## Coding Principles

- **DRY**: one point of change (intentional repetition allowed)
- **Test first**: write unit test before implementation; each facade function needs a unit test
- **Best guess**: on incomplete data, use assumptions to complete the process and report them upward — never silently fail
- **Minimal dependencies**: keep external packages to a minimum
- **Log all user changes**: every user action is logged with undo/redo support
- **Small classes**: split when classes get too large; most important functions at the top