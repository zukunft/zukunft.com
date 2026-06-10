# CLAUDE.md

Guidance for Claude Code working in this repository. This file is the always-on
rule index: read it fully every session. The rules below are imperative and
non-negotiable. The "why", worked examples, and edge cases live in `docs/llm/` —
open the linked file only when a rule applies to the change you are making.

**zukunft.com** is a PHP 8.4+ / PostgreSQL "Giant Global Graph" browser: users
build personal OLAP cubes from words, triples, formulas, and values
("calculating with words"). Architecture, source layout, and domain
terminology: `docs/llm/architecture.md`. Read it before navigating unfamiliar code.

## The most relevant rule of all

> Il semble que la perfection soit atteinte non quand il n'y a plus rien à ajouter,
> mais quand il n'y a plus rien à retrancher.
> — Antoine de Saint-Exupéry

Perfection is reached not when there is nothing left to add, but when there is
nothing left to remove. Prefer the smallest change that does the job: fewer
lines, fewer functions, fewer assertions, fewer parameters. When in doubt, leave
it out — every rule below is subordinate to this one.

## Build / test / commit

```bash
docker-compose up -d          # app: http://localhost:8080/http/view.php  adminer: :8081
composer install              # dependencies
composer dump-autoload        # refresh PSR-4 autoloader
```

Tests run over HTTP, not CLI: `test/test_unit.php` (unit, no DB),
`test/test.php` (all), `test/test_coding_rules.php` (consistency checks),
`test/test_horizontal.php`. Single class via `a_selected_test.php` in PHPUnit dir.

Branches: `feature/*` → `develop` → `release` → `master`. Commit messages
reference issues, e.g. `fix auth flow as part of fix #232`.

## Always-on rules

Each rule is one line. When one governs your current edit, open the linked
detail file. Order is by how often they fire, not importance.

### Structure & style
- One `return` per function, at the end, into a named variable; top-of-function guard clauses excepted. → `docs/llm/structure.md`
- An unexpected fall-through branch calls `log_err(...)` before the default; a normal-empty one does not. → `docs/llm/structure.md`
- Function bodies fit on one screen page (~50 lines); extract named helpers (`save_results`, `save_components`) when an orchestrator outgrows that. → `docs/llm/structure.md`
- No magic literals: every value with a named constant is referenced by it (IDs, URL params, field names, icons). → `docs/llm/constants.md`
- Link code to DB rows by the `code_id` const only; `*_NAME` / `*_ID` siblings are test-only. → `docs/llm/constants.md`
- Icons come from `web/const/icons.php` constants, never inline `fas fa-*` strings. → `docs/llm/constants.md`
- Files order `use`/`include_once` in three blocks (path-`use` → `include_once` → class-`use`, alphabetic). → `docs/llm/file-layout.md`
- Main object files follow the standard section order; functions use the standard names. → `docs/llm/architecture.md`
- Variable names are the 3-letter abbreviations (or combinations); only `$i` may be single-char. → `docs/llm/architecture.md`
- Every class file declares its suggested `$abbr` var name in the opening docblock. → `docs/llm/architecture.md`
- Every parameter gets a `@param` line stating its purpose and the effect of each meaningful value.
- `@param` / `@return` descriptions stay on one line where possible; longer rationale belongs in a `docs/` file the docblock can point to.

### DRY / reduce to the max (critical)
- Logic lives in exactly one place: call the existing function, never copy its body. → `docs/llm/dry.md`
- Ask an existing predicate/getter (`is_triple()`) instead of re-deriving it inline. → `docs/llm/dry.md`
- A call chain of 3+ steps belongs behind a dedicated function on the owning class. → `docs/llm/dry.md`
- Shared sibling-class logic is pushed to the parent; children call `parent::fn()` then extend. → `docs/llm/dry.md`
- Prefer the smallest code that works: remove duplication, dead code, redundant guards, needless indirection.

### State & messages
- Mutable state passes as explicit parameters (`&` only when the variable itself is reassigned); never via globals or return side effects. → `docs/llm/state-and-messages.md`
- A stateless, freshly-constructed helper (`new html_base()`) is instantiated locally, not threaded as a parameter. → `docs/llm/state-and-messages.md`
- Only the fixed global set is allowed (`$sys $db_con $cfg $cac $ui_sys $mtr $t $t_sys $debug`); introduce no others. → `docs/llm/state-and-messages.md`
- `$msg` (the single `user_message` from `http/view.php`) is append-only: never overwrite, reset, or re-create it; use a local buffer + `merge()`. → `docs/llm/state-and-messages.md`
- User-facing messages use `$msg->add(msg_id::X, [])` with a `messages.php` case + en/de translations; never `add_message(string)`. → `docs/llm/state-and-messages.md`
- Back-navigation is `'9'`-prefixed URL params (`url_var::BACK` is a prefix char), never a standalone `BACK` field. → `docs/llm/state-and-messages.md`

### Frontend (`web/`)
- `web/` class properties are `public`; custom set/get uses PHP 8.4 inline property hooks, not `get_x()`/`set_x()` methods. → `docs/llm/frontend.md`
- Any function returning/operating on a frontend object ends in `_ui` (`_dsp` is the display-class suffix only). → `docs/llm/frontend.md`
- Frontend config values always come from the request cache `$ui_sys->cfg`; never `new config()` in `web/`. → `docs/llm/frontend.md`

### Unit-testability
- No PHP superglobals inside functions (`$_GET/$_POST/$_SESSION/$_SERVER/...`); the allowed fixed globals are the only exception. → `docs/llm/state-and-messages.md`
- Any global not in the allowed set is passed as an explicit parameter.

## Domain & import rules

These fire only when touching domain objects or import JSON. JSON import
file-format detail and worked examples: `docs/llm/json_structure.md`. Domain
noun definitions: `docs/llm/architecture.md`.

- Use the domain nouns exactly: word, verb, triple, source, ref, value, group, formula, result, view, component. `phrase` = word|triple; `term` = word|verb|triple|formula. Every phrase is a term; a verb/formula is a term but not a phrase.
- `percent`-measure formulas auto-scale: never add `* 100` to a ratio assigned to `percent`.
- Symbols/abbreviations may alias several phrases on purpose (`m` = metre = million); only flag genuine unintended collisions, never force-uniquify.
- Disambiguate an ambiguous *word* with qualifier triples via the `must be one of` verb — define the word once, reference the triples; display the bare word, qualifier in the tooltip.
- A triple's `from`/`verb`/`to` key is unique within an import; split a clashing key with an intermediate building-block triple.
- A triple whose `from`/`to` is a *named* triple must carry its own explicit `name` — but never repeat the auto-generated `<from> <verb> <to>` as the `name` (the importer builds that for you; only set `name` when it differs or would clash).
- Phrase names start lower-case unless the first token is a proper noun / ticker / acronym; sentence-case caption copies (`"Gross profit"`) split the same concept in two.
- Import files are self-consistent: every assigned phrase, and every triple `from`/`to`, is defined in the same file (re-declare base words name-only).
- Assign an import formula to its *input* phrase(s) (`assigned_word` / `assigned`), never to its result.
- Qualify a value as specifically as the data allows; build qualifiers as triples from single words; omit `"share":"public"` (the default).
- `import_mapper` maps from the `$dto` only — never reads the DB; a missing reference adds a `msg_id` error, no DB load, no placeholder.
- A component's `ui_msg_code_id` is globally unique; re-declare an existing component by its canonical `code_id` to merge, never borrow its `ui_msg_code_id` on a new `code_id`.
- A `sys_log` row insert is never written to the change log; an update of an existing `sys_log` row is always written to the change log. → `docs/llm/architecture.md`
- Every field written with `sql_type::LOG` needs a row in `db_code_links/change_fields.csv` (field name + `change_tables.csv` table id); a per-field change log error usually means that row is missing. → `docs/llm/architecture.md`

## Testing rules

Detail and worked examples: `docs/llm/testing.md`.

- Write the test first. Every function has ≥1 positive and ≥1 negative test; a happy-path-only function counts as untested.
- The negative test asserts the *reported* outcome (`msg_id` / empty / `false`), never merely "no exception thrown".
- Pick the tier by what the function does: pure → `unit/`; DB read → `unit_read/`; DB write/REST/cache → `unit_write/`.
- All test objects come from a `create/test_*.php` factory — single objects and populated lists alike, never inline construction.
- Factory method names don't repeat the class's object word (`test_phrases::list_chf_symbol_ui`, not `phrase_list_...`).
- Named test objects use only `RESERVED_NAMES` consts; DB ids in tests are `*_ID` consts; add the const + reserved entry before writing the test if none fits.
- `$test_name` is a named variable declared first (top of the block), reused before each later assertion.
- Keep `$test_name` short but unique; don't repeat context the enclosing `$t->subheader(...)` (or `$t->name`) already shows.
- Pass only `$test_name` to `$t->assert*()`; let the helper prepend `$t->name` — don't concatenate it.
- Use the specific `assert_*` variant (`assert_text_contains`, ...), not a generic `assert_true(str_contains(...))`.
- `$t->subheader(...)` labels are as short as possible while staying unique.
- Data-file-dependent tests recreate the artifact from a shared const (one point of change), e.g. import-JSON names from a reserved test word.
- Every component-type renderer arm in `component_exe.php` has a page-based test in `unit_ui/<topic>_ui_tests.php`.
- Every HTML-returning function in `web/` contributes a fragment to an `object_pages/<name>.html` snapshot; cross-object renderers go through a `test_base` helper.
- Every machine-checkable coding rule (e.g. frontend code may only read `$ui_sys`/`$mtr`) has a coded check in `unit/coding_rule_tests.php`; reviewer attention is not a substitute. → `docs/llm/testing.md`

## Pre-commit checklist

- `files::AUTO_UPDATE_HTML` must be `false` (true silently overwrites failing HTML snapshots).
- When PHP changes affect HTML output, update the matching `src/test/resources/web/html/` snapshot; the test scripts verify the rest — don't hand-audit fixtures.
- No real secrets anywhere (source, fixtures, config, commit messages). Dummy passwords must be explicitly labelled; remove an accidentally-staged secret before committing, not in a follow-up.
- Run `test/test_coding_rules.php`; fix what it reports.
- `test/test.php` must run without error; fix any failure before committing.
