# DRY & reduce to the max

Detail for the DRY rules in `CLAUDE.md`. These two override convenience — they
are critical, not nice-to-haves.

- **DRY**: every piece of logic lives in exactly one place. When behaviour
  already exists, **call the existing function** — never copy its body.
  Intentional repetition is allowed only when explicitly justified.
- **Reduce to the max**: write the least code that does the job. Remove
  duplication, dead code, redundant guards, and needless indirection rather than
  adding more.

A concrete reason beyond aesthetics: a single source location can carry
behaviour that *must* exist only once. `test_base::update_path_file()` is the one
place that writes an accepted test target file, and the one place a developer
sets a debugging breakpoint. If a caller re-implements the `file_put_contents(...)`
itself, that breakpoint no longer covers it, errors are reported inconsistently,
and a later change has to be made in two places.

- **Right**: `$this->env->update_path_file($csv_file_path, $target);`
- **Wrong**: re-inlining `if (file_put_contents($csv_file_path, $target) === false) { log_err(...); }` in the caller

So before writing new code, look for an existing function (and the right
object/instance to call it on, e.g. an injected `test_cleanup`/`test_base` env)
and reuse it.

## Prefer an existing class function over an inline check

When a class already exposes a function that answers a question, **call that
function instead of re-deriving the answer inline**. The method is the single
source of truth for what the question means, so a later change (a new subtype, a
renamed class, an extra condition) flows to every caller automatically.

- **Right**: `if (!$rel_phr->is_triple()) { … }` — ask the phrase itself
- **Wrong**: `if (!($rel_phr->obj() instanceof triple)) { … }` — re-implements `is_triple()` inline

This holds for any predicate, getter, or cast a class provides (`is_word()`,
`is_triple()`, `has_parent()`, `triple()`, ...): reach for the existing function
first, add a new one only when none fits.

## A call chain of more than two steps belongs behind a dedicated function

When reaching a value needs **more than two** chained calls / property accesses,
don't inline that chain at the call site — add a dedicated function on the class
that owns the starting object. The navigation then has one well-named home, the
intermediate-null handling lives in one place, and every caller reads the intent
instead of re-walking the path. Two steps inline are fine (`$obj->name()`,
`$phr->get_verb()`); three or more are the trigger.

- **Right**: `$rel_phr->get_verb_id()` — add `get_verb_id()` if it doesn't exist yet
- **Wrong**: `$rel_phr->is_triple() ? $rel_phr->obj()->get_verb()?->id() : null` — a three-step chain plus a guard, re-walked at every call site

Build the new function on top of steps the class already exposes (here
`get_verb_id()` is just `return $this->get_verb()?->id();`), so it stays a thin
single-source-of-truth wrapper, not a re-implementation.

## Push common function logic to the parent class

When a function appears in two or more sibling classes with partially shared
logic, extract the shared part into the parent; each child calls
`parent::functionName()` before (or after) adding its own fields.

`api_array()` illustrates this across the inheritance chain — each level adds
only the fields it owns:

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
    $vars = parent::api_array();
    $vars[json_fields::TYPE] = $this->type_id();
    return $vars;
}

// word — adds plural and parent word
function api_array(): array
{
    $vars = parent::api_array();
    $vars[json_fields::PLURAL] = $this->get_plural();
    if ($this->has_parent()) {
        $vars[json_fields::PARENT] = $this->parent()->api_array();
    }
    return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
}
```

The same rule applies to all layered functions: `url_mapper()`, `api_mapper()`,
`api_json()`, `sql_insert()`, `sql_update()`, etc. — each child calls the parent
version first, then extends.

## Two near-identical functions collapse into one parameterised helper

When two functions differ only in a few constant values, don't keep two copies of
the body — move the shared logic into one helper that takes the differences as
parameters, and let each entry point be a thin wrapper that supplies its constants.
This is the same-class sibling of "push common logic to the parent": no inheritance
is involved, just one private function instead of a copied loop.

The coding-rule checks `php_web_only_allowed_globals_tests()` and
`php_cfg_only_allowed_globals_tests()` walked a directory and flagged every
disallowed `global $x` declaration with identical loops; only the path, the
allowed-name set, and the message text differed. The body now lives once in
`php_only_allowed_globals_tests($t, $base_path, $allowed, $rule_msg)`:

```php
// the single place the scan logic lives
private function php_only_allowed_globals_tests(
    test_cleanup $t, string $base_path, array $allowed, string $rule_msg): void
{
    // ... walk every file under $base_path, assert for any `global $x` not in $allowed ...
}

// web/ check — supplies only its constants
function php_web_only_allowed_globals_tests(test_cleanup $t): void
{
    $this->php_only_allowed_globals_tests(
        $t, paths::WEB, ['ui_sys', 'mtr'],
        'web/ must declare only $ui_sys and $mtr as globals');
}

// cfg/ check — same body, different constants
function php_cfg_only_allowed_globals_tests(test_cleanup $t): void
{
    $this->php_only_allowed_globals_tests(
        $t, paths::MODEL, ['sys', 'db_con', 'cfg', 'cac', 'mtr', 'debug'],
        'cfg/ must declare only $sys, $db_con, $cfg, $cac, $mtr and $debug as globals');
}
```

A later change to the scan (e.g. skip commented-out lines) is then made once and
covers both rules, and adding a third scope is a one-line wrapper, not another
copied loop. The trigger is duplicated *behaviour*: if you are about to paste a
function and tweak a literal or two, extract the helper instead.

## DRY applies to test fixtures too

A frontend (`_ui`) test fixture must not hand-rebuild an object with duplicated
id/name literals. Build the object with its **backend test creation function**
and convert it to the frontend object via the `api_json()` round-trip. The
object's identity then lives in exactly one place — the backend factory.

- **Right**: build a backend `phrase_list` from the backend factories and
  convert once — `$lst->add($t_trp->zh_city()->phrase()); … return new phrase_list_ui($lst->api_json());`
- **Wrong**: `related_phrase_ui(triples::CITY_ZH_ID, 'city')` — re-states the id and a hand-picked label, so the same object lives in two places and can drift
