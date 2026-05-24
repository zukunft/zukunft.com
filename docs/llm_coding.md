# LLM Coding Guide

This file provides guidance to LLM AI (like claude.ai/code) when working with code in this repository.

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

### Domain object terminology

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

- **phrase** = a **word** or a **triple** (the things that can be combined into a group and used to address a value).
- **term** = a **word**, **verb**, **triple**, or **formula** (everything that can appear in a formula expression).

So every phrase is a term, but a verb and a formula are terms that are **not** phrases. When describing why two objects of different classes must not share a name or id, pick the noun that actually covers both classes — e.g. a triple and a formula are both *terms* (not *phrases*, because a formula is not a phrase).

### The `percent` measure auto-scales

The `percent` measure word is auto-scaled by the formula engine: a formula whose result is assigned to `percent` and that computes a ratio (e.g. `( "this" - "prior" ) / "prior"`) is shown to the user as a percentage **without** an explicit `* 100` in the expression. So do not "fix" such formulas by adding `* 100` — the missing factor is intentional, and the scaling happens via the `percent` measure, not the expression.

### Some symbols and abbreviations are intentionally ambiguous

A short symbol can be the alias of more than one phrase on purpose. For example `m` is the symbol for the SI unit `metre` (in `units.json`) and at the same time the abbreviation for `million` (in `scaling.json`). This is **by design**: the context — the other phrases in the value's group — disambiguates which meaning applies. Do not "fix" such overlaps by forcing the symbol to be unique or by renaming one side; only flag a genuine, unintended collision (e.g. a formula name equal to a triple name).

### Disambiguate an ambiguous word with qualifier triples

When a single word can mean more than one thing, the word stays **defined once**, and each distinct meaning is made unique by a **qualifier triple** — never by duplicating or renaming the word. The bare word is then only the shared label; the data always references the unambiguous triple, not the word.

Take `second`, which can be a time unit or a ranking number:

1. Define the word `second` **once**, with a description that states it has several usages (e.g. *"used both as the SI time unit and as the ranking number; reference the qualifier triple, not this word"*).
2. Create one triple per meaning: `second (time unit)` and `second (ranking number)`. The disambiguation is expressed with the verb **`must be one of`** (a new verb for exactly this purpose), so each meaning `must be one of` the readings of `second`.
3. In all data (import JSON, values, formulas, links) reference **only the triples**, never the bare word `second`, so the meaning is always unambiguous.

This is the word-level counterpart of the intentional symbol ambiguity above: a symbol may *alias* several phrases on purpose, but an ambiguous *word* is resolved by pinning each meaning to its own qualifier triple.

#### Display rule: show the original word, put the qualifier in the tooltip

Although the data references the qualifier triple, the user should still see the short, familiar word. So when a qualifier triple is shown on a page or a page section, render **only the original word** and move the qualifier into the tooltip:

- **Right**: `second (time unit)` is displayed as `second`, and the `time unit` qualifier appears only inside the mouse-over tooltip (e.g. as part of the title text).
- **Wrong**: printing the full triple name `second (time unit)` inline on the page.

This keeps pages readable while the underlying reference stays unambiguous; the qualifier is recoverable on hover for the user who needs it.

### A triple's `from`/`verb`/`to` combination must be unique within an import

Within the same import JSON a triple is identified by its `from` + `verb` + `to` combination, so that same combination must not be reused for two triples with **different names or meanings** — the duplicate key leads to an ambiguous id assignment during import (the same triple cannot resolve to two names).

When two distinct concepts would otherwise share the same `from`/`verb`/`to`, introduce an intermediate **building-block triple** and point one concept at it so each key stays unique. For example `Newton` `name of` `law` was used for both *Newton's second law* and *Newton's law of gravitation*; the fix is to first create the triple `second law` (= `second` `kind of` `law`) and then build *Newton's second law* as `Newton` `name of` `second law`, so its key (`Newton`, `name of`, `second law`) differs from the gravitation triple's (`Newton`, `name of`, `law`). When such a building block already exists implicitly (e.g. `first`/`second` `kind of` `law` was also used directly by the thermodynamics laws), rebuild those users on top of the new block too (e.g. `first law of thermodynamics` = `first law` `of` `thermodynamics`) so no key is duplicated.

This is distinct from the intentional symbol ambiguity above: the same **name** may alias several phrases on purpose, but the same **triple key** must never map to two different triple names.

#### A triple whose `from` (or `to`) is a named triple must have its own explicit `name`

When a triple references another **named** triple as its `from` (or `to`) — typically an `is part of` membership triple built on a named law/concept triple — it **must carry an explicit, unique `name`**. If `name` is omitted, the import cannot build a distinct generated name for it (the referenced triple's name has not been resolved yet at that point), and it ends up reusing the same name as the previous (referenced) triple — and two triples must not share a name, so the import fails.

- **Wrong** — the second triple has no `name`, so it collides with the first triple's name `Faraday's law of electrolysis`:
```json
{ "name": "Faraday's law of electrolysis", "from": "Faraday", "verb": "name of", "to": "law" },
{ "from": "Faraday's law of electrolysis", "verb": "is part of", "to": "electrochemistry" }
```
- **Right** — give the membership triple its own name:
```json
{ "name": "Faraday's law", "from": "Faraday", "verb": "name of", "to": "law" },
{ "name": "Faraday's law of electrolysis",
  "from": "Faraday's law", "verb": "is part of", "to": "electrochemistry" }
```

A membership triple whose `from` is a plain **word** (e.g. `force` `is part of` `mechanics`) can stay unnamed — the word's name is available, so a distinct name is generated. The clash arises only when the `from`/`to` is itself a named triple.

### Assign an import formula to a same-file input phrase, not its result

In an import JSON a formula is linked to its phrases via `assigned_word` (a single phrase) or the `assigned` array (several phrases). Assign the formula to the **phrase(s) the formula uses as input**, **never** to the result it computes. The assignment is what makes the formula *applicable*: a formula is offered wherever an assigned phrase has a value. For example `"pH" = - log("hydrogen ion concentration")` is assigned to `hydrogen ion concentration` (the input), so that wherever a hydrogen-ion concentration is known the pH can be computed. Assigning it to `pH` (the result) would be wrong — you would already need the pH to discover the formula that produces it.

Use `assigned_word` for a single input phrase and the `assigned` json array when the formula has **several** input phrases — list every input phrase in the array.

- **Right (single)**: `{"name": "definition of pH", "expression": "\"pH\" = - log(\"hydrogen ion concentration\")", "assigned_word": "hydrogen ion concentration"}`
- **Right (several)**: `{"name": "self-ionization of water", "expression": "\"pH\" + \"pOH\" = 14", "assigned": ["pH", "pOH"]}`
- **Wrong**: assigning either formula to its result

**Import files must be strictly self-consistent: every phrase a formula is assigned to — whether via `assigned_word` or in the `assigned` array — must be defined in the same import file.** Each file is imported with its own per-file cache, so an assigned phrase that lives in another file cannot be resolved — never assign a formula to a phrase another file owns. If a formula's inputs are all defined elsewhere — e.g. a chemistry formula `"molar mass" = "mass" / "mole"` whose inputs `mass` and `mole` belong to the units/physics data and must **not** be redefined — then either define that formula in the file that owns its inputs, or leave it **unassigned** in this file.

The same self-consistency applies to **triples**: every `from` and `to` phrase of a triple must be defined in the same import file too, because triples are also resolved from the per-file import cache. When a file only *references* a base word it does not own — e.g. `chemistry` `is part of` `science`, or a named-law triple `Avogadro` `name of` `law` — re-declare that base word in the file with a **name-only** entry `{"name": "science"}` / `{"name": "law"}` (this is exactly how `physics.json` re-declares the units `kg`, `metre`, `second` it uses). On import the name-only entry merges with the word's canonical definition in its home file, so no data is duplicated or overwritten.

This self-consistency requirement is about the import resolution (assignments and triples). A formula's `expression` may still reference phrases from earlier base files (e.g. every physics formula uses units such as `kg` and `metre`); those are resolved when the formula is calculated, not via the per-file import cache.

### Qualify a value as specifically as possible — prefer triples built from single words

A value's `words` array is the phrase group the number belongs to. **Always describe a value as specifically as the data allows: add as many qualifying phrases as possible so the number is never ambiguous.** A bare `{"words": ["price"], "number": "20"}` claims that *the* price is 20 — which is meaningless on its own. Add the context that makes it true (which dataset, which entity, which period, which source, …).

Express each qualifier as a **phrase that is itself built from single words**, and **prefer a triple over a flat extra word**:

- Define the individual words (`economics`, `textbook`, `example`).
- Combine them with **existing verbs** into triples, building up from single words. Because each triple's `to` (or `from`) is itself a named triple, give it an explicit `name` (per the rule above):
  - `{"from": "textbook", "verb": "of", "to": "economics", "name": "economics textbook"}`
  - `{"from": "example", "verb": "of", "to": "economics textbook", "name": "economics textbook example"}`
- Reference the resulting triple by its name in the value's `words` array.

This turns a vague value into a precise one:

- **Vague**: `{"words": ["price"], "number": "20", "share": "public", "source": "economics textbook example"}`
- **Specific**: `{"words": ["price", "economics textbook example"], "number": "20", "source": "economics textbook example"}`

**`"share": "public"` is the default and must be omitted** — only add `share` when it differs from `public`.

### `import_mapper` maps from the dto only — never read the database

`import_mapper` (and the helpers it calls such as `import_map_names`) must **only map the object from the json and resolve its references from the passed-in `data_object` (the per-file import cache, `$dto`). It must never read from the database.** If a referenced phrase, word, triple, source, etc. is not present in the dto, **add a translatable error to `$msg`** (e.g. `msg_id::IMPORT_FORMULA_ASSIGN_PHRASE_MISSING`) — do **not** load it from the database and do **not** silently create a placeholder object.

This keeps the import deterministic and each file self-consistent: everything a file references must be present in that file's import (re-declared name-only if it is a base word, per the self-consistency rule above), and the mapper fails loudly when it is not. Any database access — loading existing rows, cross-file lookups, merging — belongs to the **save** step, not to `import_mapper`.

### A view component's `ui_msg_code_id` is globally unique — never reuse one on a new component

The `components` table has a unique key `components_ui_msg_code_id_uk` on `ui_msg_code_id` (it does **not** cover `ui_msg_code_id_vars` or `ui_msg_code_id_exception`, and NULL is allowed many times). So a `ui_msg_code_id` effectively identifies one single component. Two component definitions in the import JSON must never carry the same `ui_msg_code_id` under **different** `code_id`s — the import then tries to `INSERT` a second row with a duplicate `ui_msg_code_id` and the save fails with `duplicate key value violates unique constraint "components_ui_msg_code_id_uk"`.

Because the import `$dto` is **per file** (`get_data_object()` builds a fresh `data_object` for every json file and a view-component link resolves only via `$dto->get_component_by_name()`), a view defined in one file (e.g. `base_views.json`) cannot link a component that is only defined in another file (e.g. `system_views.json`). The fix is the component counterpart of the name-only base-word re-declaration: **re-declare the existing system component in the file with its exact canonical `name` + `code_id` (and the same `type`/`ui_msg_code_id*` fields)**, then link it by that name. On save the component is matched by its `code_id` and **merged** (updated in place) instead of inserted, so the unique `ui_msg_code_id` is not duplicated.

- **Wrong** — a new component with a fresh `code_id` but a borrowed `ui_msg_code_id` (fails on import):
```json
{ "name": "word values subtitle", "type": "system_sub_title",
  "code_id": "word_default_values_subtitle", "ui_msg_code_id": "system_sub_title_values" }
```
- **Right** — re-declare the canonical component so the save merges by `code_id`:
```json
{ "name": "system sub title values", "type": "system_sub_title",
  "code_id": "system_sub_title_values", "ui_msg_code_id": "system_sub_title_values" }
```

This mirrors the "define once, link many" pattern already used inside `system_views.json`, where a single subtitle component (e.g. `system sub title values`) is defined once and referenced by name from many views.

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

### Suggested variable name in class header

Every class file must declare its suggested variable name in the opening file docblock, on its own line immediately after the one-line file description, using the format:

```
$<abbr> is the suggested var name
```

Example from `web/sandbox/sandbox_link.php`:
```
    web/sandbox/sandbox_link.php - extends the frontend sandbox object for links
    ----------------------------

    $sbx_lnk is the suggested var name
```

The abbreviation must match the three-letter prefix convention in `docs/code_guidelines.md` (e.g. `$wrd` for word, `$frm` for formula, `$msk` for view/mask). For compound names combine the parts: `$sbx_lnk` for sandbox_link, `$frm_lnk` for formula_link, `$cmp_lnk` for component_link.

### Deployment Branch Strategy

`feature/*` → `develop` → `release` → `master`

Commit messages reference issue numbers: e.g. `fix auth flow as part of fix #232`.

## Coding Principles

- **DRY — critical**: one point of change. Never repeat a piece of logic; call the existing function instead of copying its body (intentional repetition is allowed only when explicitly justified). See "DRY and 'reduce to the max' are critical".
- **Reduce to the max — critical**: prefer the smallest possible amount of code; remove duplication, dead code and needless indirection. See "DRY and 'reduce to the max' are critical".
- **Push common logic to parent**: if two or more sibling classes contain the same code in a function, move that code to the shared parent. Child implementations call `parent::functionName()` and then extend with their own fields only. See the `api_array()` pattern below.
- **Test first**: write unit test before implementation; every function needs at least one positive and one negative unit test (see "At least one positive and one negative test per function")
- **Best guess**: on incomplete data, use assumptions to complete the process and report them upward — never silently fail
- **Minimal dependencies**: keep external packages to a minimum
- **Log all user changes**: every user action is logged with undo/redo support
- **Small classes**: split when classes get too large; most important functions at the top
- **No single-character variable names**: prefer short but readable names — 3-letter abbreviations are ideal (`$val`, `$key`, `$fmt`); only $i for loops is allowed as single character var name
- **Document parameters**: every parameter gets a `@param` line in the docblock explaining its purpose and the effect of each meaningful value (e.g. what `true`/`false` does)

### DRY and "reduce to the max" are critical

These two are not nice-to-haves — they are critical and override convenience:

- **DRY (don't repeat yourself)**: every piece of logic lives in exactly one place. When you need behaviour that already exists, **call the existing function** — never copy its body. If the same code would appear in two siblings, push it to the shared parent (see below).
- **Reduce to the max**: write the least code that does the job. Remove duplication, dead code, redundant guards and needless indirection rather than adding more.

A concrete reason this matters beyond aesthetics: a single source location can carry behaviour that *must* exist only once. For example `test_base::update_path_file()` is the one place that writes an accepted test target file, and it is the one place a developer sets a debugging breakpoint (`// TODO always set a breakpoint here`). If a caller such as `test_db_load::csv_recreate()` re-implements the `file_put_contents(...)` itself, that breakpoint no longer covers it, errors are reported inconsistently, and a later change has to be made in two places.

- **Right**: `$this->env->update_path_file($csv_file_path, $target);`
- **Wrong**: re-inlining `if (file_put_contents($csv_file_path, $target) === false) { log_err(...); }` in the caller

So before writing new code, look for an existing function (and the right object/instance to call it on, e.g. an injected `test_cleanup`/`test_base` env) and reuse it.

### Allowed global variables

The project uses a small, fixed set of globals (see `docs/todo.md`). No other globals should be introduced:

| Global   | Purpose |
|----------|---------|
| `$sys` | Execution times, type cache, system config (rarely changes, not user-specific) |
| `$db_con` | Database connection |
| `$cfg` | User-specific configuration numbers (changes more often than types) |
| `$cac` | Backend cache of user-specific `data_object` |
| `$ui_cac` | Frontend cache including the session user |
| `$mtr` | Message translation — created **once** in `http/view.php`; language resolved in this priority order: (1) `url_var::LANGUAGE` URL parameter, (2) session variable, (3) user config (`$cfg`), (4) default language |
| `$t` | Base test object (assert + cleanup helpers) |
| `$t_sys` | Error counting and execution times for tests |
| `$debug` | Activates additional logging levels |

### Push common function logic to the parent class

When a function appears in two or more sibling classes with partially shared logic, extract the shared part into the parent and have each child call `parent::functionName()` before (or after) adding its own fields.

The `api_array()` function illustrates this across the full inheritance chain. Each level adds only the fields it owns:

```php
// sandbox — adds share/protection/excluded
function api_array(): array
{
    $vars = parent::api_array();  // db_object adds the id
    if ($this->share_id != null) { $vars[json_fields::SHARE] = $this->share_id; }
    if ($this->protection_id != null) { $vars[json_fields::PROTECTION] = $this->protection_id; }
    if ($this->excluded != null) { $vars[json_fields::EXCLUDED] = $this->excluded; }
    return $vars;
}

// sandbox_named — adds name and description
function api_array(): array
{
    $vars = parent::api_array();  // inherits id + share + protection + excluded
    $vars[json_fields::NAME] = $this->name();
    $vars[json_fields::DESCRIPTION] = $this->get_description();
    return $vars;
}

// sandbox_typed — adds type_id
function api_array(): array
{
    $vars = parent::api_array();  // inherits everything above
    $vars[json_fields::TYPE] = $this->type_id();
    return $vars;
}

// word — adds plural and parent word
function api_array(): array
{
    $vars = parent::api_array();  // inherits id + share + protection + name + description + type
    $vars[json_fields::PLURAL] = $this->get_plural();
    if ($this->has_parent()) {
        $vars[json_fields::PARENT] = $this->parent()->api_array();
    }
    return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
}
```

The same rule applies to all other layered functions: `url_mapper()`, `api_mapper()`, `api_json()`, `sql_insert()`, `sql_update()`, etc. — each child calls the parent version first, then extends.

### Always use named constants — no magic literals

Every numeric or string value that has a defined constant must be referenced via that constant, never as a literal. This applies to IDs, URL parameters, field names, and any other value with a canonical name in the codebase.

- **Wrong**: `'61'`, `'m'`, `'?'`, `'/http/view.php'`
- **Right**: `views::LOGIN_ID`, `url_var::MASK`, `url_var::PAR`, `api::MAIN_SCRIPT`

When a constant from another class cannot yet be referenced (e.g. due to a missing `use` or include chain), add a `// TODO: replace literal with ConstClass::CONST_NAME` comment so the gap is tracked and fixed in a follow-up.

### Back-navigation parameter convention

Back navigation (where to redirect after an action completes) is encoded as **`'9'`-prefixed URL parameters**, never as a standalone `url_var::BACK` parameter.

- **Right**: `?9m=2&9id=5&9z=0` — each original key is prefixed with `'9'` (`url_var::BACK`); `html_base::back_url_part()` builds this; `html_base::url_par_from_back_part()` strips the prefix on the receiving end
- **Wrong**: `?9=http%3A%2F%2F...` — do not use `url_var::BACK` as a standalone field name carrying a full URL string; do not emit `form_hidden(url_var::BACK, $someUrl)`

`url_var::BACK = '9'` is a **prefix character**, not a parameter name. Legacy code that reads `$url_array[url_var::BACK]` directly must be migrated to the prefixed-key pattern.

### User-message accumulation convention

`$msg` is created **once** in `http/view.php` as `new user_message()` and is the single collector for every message shown to the user during a request. It is passed as an explicit parameter (named `$msg`) to every function that may need to report a warning, error, or info notice.

- **Right**: `function url_to_action(array $url_array, user $usr_dsp, user_message $msg, ...): array`
- **Wrong**: creating `new user_message()` inside a helper and returning or echoing the message directly

This ensures all messages bubble up to the single rendering point in `view.php`. The same object is named `$msg` in the backend; it is the one created in `http/view.php` and threaded down — never a second instance.

#### Never overwrite or reset the accumulated messages

A function that receives `$msg` (or old `$usr_msg`) as a parameter may only **add** to it. It must never replace, clear, reset, or re-create the object, because doing so silently discards warnings and errors that earlier code already recorded for the user.

- **Right**: `$msg->add(msg_id::SOME_CASE, []);` — append only; earlier messages are preserved
- **Right**: `$msg->merge($sub_msg);` — fold a locally collected sub-message back into the shared object
- **Right**: `$sub_msg = new user_message();` inside a function if later merged with the received `$msg`
- **Wrong**: `$msg = new user_message();` inside a function that received `$msg` — drops everything accumulated so far
- **Wrong**: `$msg->reset();` / clearing the message list on a parameter object

**Why:** every function shares the single instance created in `http/view.php`, and `is_ok()` is read by callers to decide control flow (e.g. the import loop only adds a triple to the cache when `import_mapper()` returns `$usr_msg->is_ok()`). If a downstream function overwrites or resets the object, earlier errors vanish from the notification bar and the `is_ok()` signal becomes wrong — which previously caused a whole import to silently drop every object after the first failed one.

**How to apply:** when a function needs a throw-away message buffer (e.g. to test whether a sub-step succeeded without polluting the shared object), create a **separate local** `user_message` and `merge()` the relevant part back into the shared one — do not reassign or reset the passed-in parameter.

#### All user-facing messages must use a translatable `msg_id`

Every message added to `$msg` must be added via `$msg->add(msg_id::SOME_CASE, [])`, never via `add_message(string)` or `add_message_text(string)`. The plain-string methods bypass the translation system and break serialisation — the message will not survive the `api_array()` round-trip and will not reach the frontend notification bar.

- **Right**: `$msg->add(msg_id::SIGNUP_ERR_NAME_EXISTS, []);`
- **Wrong**: `$msg->add_message($mtr->txt(msg_id::SIGNUP_ERR_NAME_EXISTS));`
- **Wrong**: `$msg->add_message_text('User name already exists');`

Every new user-visible string must have:
1. A `case` in `src/main/php/shared/enum/messages.php`
2. An English translation entry in `src/main/resources/translations/en.yaml`
3. A German translation entry in `src/main/resources/translations/de.yaml` (and any other active locale file)

### Pass mutable state as explicit parameters, never via globals or return-value side effects

Any object that a function may update must be declared as an explicit parameter. The caller owns the object and can observe the change after the call. Use PHP pass-by-reference (`&`) when the function must replace the variable itself (e.g. reassigning `$usr_backend = $db_usr`); update-in-place via method calls (e.g. `$usr_ui->set_from_json(...)`) does not require `&` because objects are already passed by handle.

- **Right**: `function action_login(..., user_backend &$usr_backend, user_ui &$usr_ui, ...)`  — caller's `$usr` and `$usr_dsp` are updated after a successful login
- **Wrong**: reading or writing a global variable inside the function to propagate the change

### Test assertion style

Declare the test name as a named variable on its own line before the assertion, then pass it to `$t->assert*()`. This keeps the description readable and makes the assertion call itself compact.

```php
// Right
$test_name = 'login page with failed login shows notification bar';
$t->assert_text_contains($test_name, $login_html, '<div class="alert alert-warning notification-bar">');

// Wrong — inline string makes the line too long and harder to scan
$t->assert_text_contains('login page with failed login shows notification bar', $login_html, '<div ...>');
```

This applies to all `$t->assert*()` variants: `assert`, `assert_html`, `assert_html_page`, `assert_text_contains`, etc.

### `use` and `include_once` ordering

Every PHP source file that uses classes from other namespaces must follow this three-block structure:

**Block 1 — path-constant `use` statements** (before any `include_once`):
Import only the path-constant classes needed to build the `include_once` paths. Order: `cfg` paths → `web` paths → `shared` paths → test paths → any other paths.

```php
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
```

**Block 2 — `include_once` statements**:
List all file includes, using the path constants from Block 1.

```php
include_once paths::API_OBJECT . 'api_message.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'sandbox_link.php';
```

**Block 3 — class `use` statements** (after all `include_once`):
Import all class names used in this file. Order: `cfg`/`api` → `web` → `shared`. Within each group, sort entries alphabetically by their fully-qualified class name.

```php
// cfg / api group (alphabetic within)
use Zukunft\ZukunftCom\main\php\api\api_message;
// web group (alphabetic within)
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
// shared group (alphabetic within)
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
```

### Test object creation

All objects used in tests must be created by a factory function in `src/test/php/create/`. Each domain type has its own factory class — `test_words`, `test_formulas`, `test_views`, `test_languages`, etc. — with named methods that return pre-configured objects with well-known field values.

- **Right**: `$obj = $t_frm->formula_link_filled();` — uses the shared factory; any change to the test fixture is made in one place
- **Wrong**: `$frm_lnk = new formula_link($usr); $frm_lnk->set_id(99); ...` — ad-hoc construction scattered across test files makes fixtures hard to maintain and diverge silently

When a needed factory method does not yet exist, add it to the appropriate `test_*.php` file in `src/test/php/create/` before writing the test. The `test_mappers.php` class coordinates factory calls when a test needs to resolve a class name to a test object at runtime.

### At least one positive and one negative test per function

Every function — and at minimum every **new** function — must have at least one **positive** unit test (the expected input produces the expected result) and at least one **negative** unit test (an invalid, missing, or boundary input is rejected or handled gracefully). A function that only ever has a happy-path test is considered untested for review purposes.

- **Positive**: the documented "good" case returns the documented result, e.g. `body_search()` with a populated word list returns the matching links.
- **Negative**: the failure or edge case is exercised, e.g. `body_search()` with a `null` list returns an empty string instead of erroring; an `import_mapper()` with a missing mandatory field adds the expected `msg_id` and `is_ok()` becomes false.

The negative test must assert the *reported* outcome (the `user_message` / `msg_id`, the empty result, the `false` return), never just that "no exception was thrown" — silent failure is itself a defect (see the "Best guess" principle).

### Tests that depend on data files must be reproducible from a single point of change

A test that relies on a generated artifact — a JSON import file, an SQL snapshot, an HTML snapshot — must be able to **recreate that artifact from code**, so the artifact never silently diverges from the constants it was built from. Apply the one-point-of-change concept: the artifact references a value through a shared constant, and regenerating the artifact uses the same constant.

This matters most for **import round-trip tests** that create an object, then delete it during cleanup. The name used in the import JSON must come from a **reserved test constant** — for words, a reserved test word in `src/main/php/shared/const/words.php` — never a hard-coded string literal in the JSON.

- **Right**: the test word in the import JSON is generated from `words::SOME_RESERVED_TEST_WORD`, and the same const drives the cleanup `del()` and any re-generation of the import file. If the const value changes, regenerating the import file picks up the new value automatically and the test still passes.
- **Wrong**: the import JSON hard-codes `"Heron"` (or any literal) while the cleanup deletes `words::HERON` — the two drift apart the moment the const changes, the cleanup misses the row, and later runs fail on a leftover duplicate.

So when adding such a test: (1) add a reserved test entry to `words.php` (or the matching `*_const` file) if none fits, (2) build the import file's name from that const, and (3) use the same const for the post-test cleanup and for any script that regenerates the import file.

### Page-based UI tests for component-type renderers

Every UI rendering function dispatched from `web/component/component_exe.php` — every arm of its `match ($tc_id)` block, e.g. `admin_jobs_delayed()` in `web/component/execute/system_page.php` — must have a page-based test that captures its HTML output. Tests live in `src/test/php/unit_ui/<topic>_ui_tests.php` and are invoked from a `run(test_cleanup $t): void` method that snapshots the rendered fragment via `$t->html_page_test(...)`.

- **Right**: `admin_jobs_delayed()` is exercised by `job_ui_tests::run()`, which builds a small page (heading + the rendered HTML) and snapshots it. Any change to the function's output then either confirms the new snapshot or surfaces as a test failure.
- **Wrong**: adding a new component-type renderer to the `component_exe.php` match arm without a corresponding entry in any `*_ui_tests::run()` — the HTML output goes untested and regressions are silent.

Pick the topic file by the domain the renderer belongs to (jobs → `job_ui_tests`, sources → `source_ui_tests`, references → `reference_ui_tests`, etc.). When a renderer fits no existing topic, add a new `*_ui_tests.php` file in `src/test/php/unit_ui/` rather than overloading an unrelated one.

### Single return per function

Every function must have exactly one `return` statement, placed at the end. Assign the result to a named variable (`$result`, `$next_url`, etc.) and return it at the bottom.

```php
// Right
function action_login(...): array
{
    $next_url = [...];
    if ($logged_in) {
        $next_url = $back_array;
    }
    return $next_url;
}

// Wrong — multiple early returns make control flow hard to follow
function action_login(...): array
{
    if ($logged_in) {
        return $back_array;
    }
    return $login_url;
}
```

Exception: guard clauses at the very top of a function (e.g. `if ($x === null) { return ''; }`) are allowed when they protect against a precondition that makes the rest of the body meaningless. Everything else must flow to a single return.

### Unit-testability rule (applies to every function and method)

Every function must be fully unit-testable. This means:

- **No PHP superglobals inside functions**: never read `$_GET`, `$_POST`, `$_SESSION`, `$_SERVER`, or any other superglobal inside a class method or standalone function. The long-term target is a single HTTP entry point (`http/view.php`) that reads superglobals once and passes them down as explicit parameters.
- **Allowed globals inside functions**: the fixed globals listed above (`$sys`, `$cfg`, `$mtr`, etc.) may be used inside functions because tests initialise the same globals at start-up, making the behaviour reproducible without parameter injection.
- **No other hidden globals**: any global not in the list above must be passed as an explicit parameter instead.

The rationale: a function that reaches outside the allowed globals cannot be called in a test without replicating the full request environment. The fixed global set is small enough that every test runner can initialise it once and all functions stay independently testable.

## Pre-commit checklist

Before every commit, verify the following:

### `files::AUTO_UPDATE_HTML` must be `false`

`src/test/php/const/files.php` contains the constant `files::AUTO_UPDATE_HTML`. When set to `true`, failing HTML snapshot tests silently overwrite the expected files instead of failing — masking regressions. It must be `false` before committing.

- **Wrong**: `CONST bool AUTO_UPDATE_HTML = true;`
- **Right**: `CONST bool AUTO_UPDATE_HTML = false;`

### HTML snapshot files in `src/test/resources/web/` are verified by the test scripts

The HTML files under `src/test/resources/web/html/` are snapshot fixtures checked automatically by test runners such as `test/test.php`. The LLM does not need to manually review their content for correctness — the tests do that. When the LLM changes PHP code that affects HTML output, it should update the corresponding snapshot to match the new output, but it does not need to audit the full file for unrelated issues.

### No secrets in the commit

Never commit real credentials, API keys, or actual passwords — not in source files, test fixtures, config files, or commit messages.

- Dummy/placeholder passwords are allowed but must be **explicitly labelled** as such, e.g. `'dummy_password_123'`, `'test_pw_placeholder'`, or accompanied by a comment like `// dummy password for tests only`.
- A value that looks like a real password with no label is not acceptable even if it happens to be fake.
- If a secret was accidentally staged, remove it before committing — do not rely on a follow-up "remove secret" commit on a public branch.