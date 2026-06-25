# Named constants — no magic literals

Detail for the constant rules in `CLAUDE.md` → "Structure & style".

## Always use named constants

Every numeric or string value that has a defined constant is referenced via that
constant, never as a literal — IDs, URL parameters, field names, any value with
a canonical name in the codebase.

- **Wrong**: `'61'`, `'m'`, `'?'`, `'/http/view.php'`
- **Right**: `views::LOGIN_ID`, `url_var::MASK`, `url_var::PAR`, `api::MAIN_SCRIPT`

When a constant from another class cannot yet be referenced (missing `use` or
include chain), add a `// TODO: replace literal with ConstClass::CONST_NAME`
comment so the gap is tracked.

## Pass a class name as `::class`, never as a string literal

When a value identifies a class — a parameter, a `match`/`switch` key, a lookup
key into a class→x map — use the `::class` constant of the class, never the bare
name string. The `::class` form is checked by the IDE and updated automatically
on a rename, so renaming a class is one edit instead of a hunt for every `'word'`
string. Derive the short name from it with `library::class_to_name($dbo::class)`
rather than writing the noun out.

- **Wrong** — bare class noun, silently breaks if the class is renamed:
```php
$name = 'word';
$base = $views->object_to_base('triple');
```
- **Right** — the class constant, follows the rename:
```php
$name = library::class_to_name($dbo::class);   // 'word'
$base = $views->object_to_base(library::class_to_name($dbo::class));
```

A `match`/`switch` that maps a class to something keys on `$obj::class` (the FQN)
where it can; the few places that must compare against the short noun (e.g. a map
keyed by `class_to_name` output) are the documented exception and should carry a
`// TODO` to key on `::class` instead.

## Use a named icon constant — no inline icon literals

Every frontend icon css class string (Font Awesome `fas`/`far`/`fab` or any
other set) is a constant in `src/main/php/web/const/icons.php` (the `icons`
class). Use the constant so an icon-set change is one place and every icon is
greppable by its constant name.

- **Wrong**: `'<i class="fas fa-edit"></i>'`
- **Right**: `'<i class="' . icons::EDIT . '"></i>'`

When a needed icon is not yet declared, add a constant first (one per full css
class string, e.g. `const string EDIT = 'fas fa-edit';`) then use it.

## All filesystem paths live in a `paths.php` const file

Every directory or path fragment the code uses is a constant in one of the three
`paths.php` files, never an inline string literal:

- `src/main/php/cfg/const/paths.php` — backend php script paths
- `src/main/php/web/const/paths.php` — frontend php script paths
- `src/test/php/const/paths.php` — test script and test resource paths

Build a longer path by composing the existing consts (`self::HTML . 'workflow' .
DIRECTORY_SEPARATOR`) so a moved folder is one edit and every path is greppable.

- **Wrong**: `test_paths::HTML . 'workflow/' . $name`
- **Right**: add `const string WORKFLOW = self::HTML . 'workflow' . DIRECTORY_SEPARATOR;`
  then `test_paths::WORKFLOW . $name`

Only a leaf file name or a folder segment built from a runtime value (e.g. a
folder named after a test object) may stay inline at the call site.

## Link code to DB rows by `code_id` only — `*_NAME` / `*_ID` are test-only

Every record in `src/main/php/shared/types/verbs.php` and
`src/main/php/shared/const/*` (words, triples, views, formulas, sources, refs)
comes with sibling consts. For the verb "is symbol for":

| const | example | meaning |
|---|---|---|
| `SYMBOL` | `"symbol"` | the **code_id** — canonical, install-stable lookup key |
| `SYMBOL_NAME` | `"is symbol for"` | user-facing display name |
| `SYMBOL_ID` | `29` | DB row id from the **initial seed** |
| `SYMBOL_COM` | `"…"` | tooltip / description |
| `SYMBOL_PLURAL` / `_REVERSE` / ... | various | language-specific declensions |

**Rule**: to link to a concrete DB row at runtime — load it, filter by it,
compare against it — reference the **`code_id` const only** (`verbs::SYMBOL`,
`words::CHF`, `triples::CITY_ZH`, `views::WORD`). Look the row up through the
cached resolver and read id/name/type from the returned object. The `*_NAME`,
`*_ID`, `*_COM`, `*_PLURAL`, `*_REVERSE` siblings are reserved for **system
tests** asserting against seed data — never production code.

- **Right** — look up by code_id, read the runtime id from the resolved object:
```php
$symbol_vrb = $sys->typ_lst->vrb->get_verb(verbs::SYMBOL);
$trp_lst->load_by_phr($phr, $symbol_vrb, foaf_direction::BOTH);
```
- **Wrong** — hardcoded numeric id couples to the seed, breaks on any re-seeded / imported pod:
```php
if ($phr->verb_id === verbs::SYMBOL_ID) { … }   // SYMBOL_ID is test-only
```
- **Wrong** — display name as a lookup key breaks on rename/translation:
```php
$wrd->load_by_name(verbs::SYMBOL_NAME);   // SYMBOL_NAME is the user-facing label
```
- **Right (test)** — fixtures may assert the seed matches expected siblings:
```php
$t->assert($wrd->id(), words::CHF_ID);
$t->assert($wrd->name(), words::CHF);
```

**Why**: `_ID` is the id the initial seed assigns; `_NAME` is the current display
name. Both drift per pod — the id when seeded under a different version, the name
on rename/translation. The `code_id` is the stable identity that survives
migrations, renames, and pod-to-pod import/export, because the import resolver
matches by code_id alone.

## config.yaml keys are at most two space-separated words

Every key in `src/main/resources/config.yaml` imports as a **word** (one token)
or a **triple** (exactly two tokens, from-verb-to). Three or more tokens cannot
be auto-imported — `import::yaml_data_object_map_triple` fails with
`"<key>" is unexpect number of words for a triple (max 2 words are expected)`.

**Rule**: split a 3+-token key into nested parent/child levels, each one or two
tokens, and register the matching const in `shared/const/words.php` (one token)
or `shared/const/triples.php` (two tokens). The runtime lookup
(`$cfg->get_by([leaf, …, root])`) walks the same path.

- **Wrong** — three-token key, fails on import:
```yaml
related per verb:
  sys-conf-value: 2
```
- **Right** — nested one-token + two-token keys:
```yaml
related:
  per verb:
    sys-conf-value: 2
```
with `words::RELATED = 'related'` and `triples::PER_VERB = 'per verb'` registered
so the coding-rule test (`src/test/php/unit/coding_rule_tests.php`) keeps passing.

Meta keys consumed by the loader itself (`tooltip-comment`, `sys-conf-value`,
`source-name`, `source-description`, `pod-user-config`) are skipped by the check
and may contain dashes and multiple tokens.
