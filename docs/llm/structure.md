# Function structure

Detail for the structure rules in `CLAUDE.md` → "Structure & style".

## One exit per function and loop — no `break` or `continue`

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

The same reasoning bans `break` and `continue` inside loops: a jump out of the
middle of a block is spaghetti control flow, the reader can no longer assume the
loop body runs top to bottom. Wrap the work in the positive condition instead of
skipping with `continue`, and let the loop's own condition (or a flag variable)
end it instead of `break`.

```php
// Wrong — continue jumps out of the middle of the body
foreach ($frm_lst as $frm) {
    if ($frm->id() != 0) {
        continue;
    }
    $msg->add(...);
    // ... more work ...
}

// Right — the work lives inside the positive condition
foreach ($frm_lst as $frm) {
    if ($frm->id() == 0) {
        $msg->add(...);
        // ... more work ...
    }
}
```

**Exception**: guard clauses at the very top of a *function* (e.g.
`if ($x === null) { return ''; }`) are allowed when they protect a precondition
that makes the rest of the body meaningless. Everything else flows to the single
return; loops have no equivalent exception.

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

## Keep a function shorter than a screen page

A function body should fit on **one screen page** (~50 lines) whenever possible —
short enough that a reader sees the whole control flow without scrolling. When a
function grows past that, extract a private helper named after *what* the block
does (`save_results`, `save_components`, `check_formula_name_collision`) so the
host function reads as a sequence of named steps.

The orchestrator function then stays a flat list of step calls — each step's
detail lives one click away in its own helper. The smell to act on isn't a hard
line count; it's "I have to scroll to see what this function does."

- **Right** — `data_object::save()` is a sequence of `save_words(...)`,
  `save_triples(...)`, `save_sources(...)`, `save_formulas(...)`,
  `save_results(...)`, `save_components(...)`, `save_views(...)`. Each helper
  owns one concern: its own config lookup, its own emptiness check, its own
  `step_start` / `step_end` framing. Adding a new save step is one new helper +
  one new line in the orchestrator.
- **Wrong** — inlining each save block (config-load + emptiness check +
  step_start + the save call + step_end + the else-log line) directly inside
  `save()`, so every new object type adds ~8 more lines to a function that is
  already several screens long.

This rule reinforces the DRY rules in `docs/llm/dry.md` — when two siblings'
save blocks differ only in the object type, the difference belongs in a helper
parameter, not in two copy-pasted blocks.
