# Function structure

Detail for the structure rules in `CLAUDE.md` → "Structure & style".

## Single return per function

Every function has exactly one `return`, at the end. Assign the result to a
named variable (`$result`, `$next_url`, ...) and return it at the bottom.

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

**Exception**: guard clauses at the very top (e.g. `if ($x === null) { return ''; }`)
are allowed when they protect a precondition that makes the rest of the body
meaningless. Everything else flows to the single return.

## Log the unexpected branch instead of returning silently

When the single return collapses several early `return ''` / `return null`
guards into one flow, decide for each guard whether its condition is *normal* or
*unexpected*:

- A **normal** empty result just leaves `$result` at its default — no log.
  Examples: an object type that simply has no related phrases; an optional list
  not loaded yet.
- An **unexpected** condition — one that should never occur when callers and
  data are consistent — calls `log_err(...)` before falling through to the
  default, so the inconsistency is visible instead of disappearing into an empty
  string (the "Best guess: never silently fail" principle applied to the
  single-return form). Examples: a type cache not loaded; a `CATEGORY_VERBS`
  code_id missing from the cache; an object exposing `phrases_related` but no
  `phrase()` method.

```php
$result = '';
if (property_exists($dbo, 'phrases_related') && $dbo->phrases_related !== null) {
    if (method_exists($dbo, 'phrase')) {
        $result = $dbo->phrases_related->category_subtitle($dbo->phrase());
    } else {
        // exposes phrases_related but no phrase() — inconsistent, so log it
        log_err('the object ' . $dbo->dsp_id() . ' exposes phrases_related but no phrase() method');
    }
}
return $result;
```
