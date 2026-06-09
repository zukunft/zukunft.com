# Testing

Detail for the "Testing rules" in `CLAUDE.md`.

## At least one positive and one negative test per function

Every function — at minimum every **new** function — has at least one
**positive** unit test (expected input → expected result) and one **negative**
test (invalid/missing/boundary input rejected or handled gracefully). A
happy-path-only function counts as untested for review.

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

### Test subheaders are short but unique

A `$t->subheader(...)` label names the test section in the run output — keep it
**as short as possible while staying unique** within the file. The full
behaviour belongs in the per-assertion `$test_name` strings.

- **Wrong**: `$t->subheader($ts . 'category subtitle renders below the title for a SYMBOL-typed related entry');`
- **Right**: `$t->subheader($ts . 'category subtitle');`

Don't collapse two sibling sections to the same label; keep just enough of the
distinguishing word (`'category subtitle'` vs `'category subtitle (multi)'`).

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
- **`AUTO_UPDATE_HTML` is `false`** — fail the run if `files::AUTO_UPDATE_HTML`
  is `true`, so a forgotten `true` cannot land on a branch.

Each check is one positive + one negative test in `coding_rule_tests.php` (the
positive proves the check catches a known bad fixture line; the negative proves
it tolerates a known good fixture line), matching the per-function discipline
above.
