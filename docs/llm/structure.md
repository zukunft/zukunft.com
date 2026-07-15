# Function structure

Detail for the structure rules in `CLAUDE.md` → "Structure & style".

## One logical element per line — three at most

Write code a human reads at a glance. A line carries **one logical element —
three at most**: one assignment, one call, one condition. When a line packs more,
split it into named steps. Fewer lines is still better (don't pad a simple
expression across many lines), so the goal is the *fewest* lines on which *each*
line still reads at a glance — minimise lines subject to that limit, never by
cramming.

Wrong — five logical elements on one line (ternary, two getters, two calls, a
concat); the reader has to unpack it:

```php
$title = $trp->get_from() != null ? $trp->get_from()->name_link() . ' ' . $trp->get_verb()->name_link() : '';
```

Right — each line does one thing, named for what it is:

```php
$from = $trp->get_from();
$title = '';
if ($from != null) {
    $title = $from->name_link() . ' ' . $trp->get_verb()->name_link();
}
```

Better still — a small function is not a sin. Pushing a two-step chain like
`get_verb()->name_link()` behind a name on the owning class turns the call site
into one self-describing element and lets the next reader (and every other call
site) skip the detail:

```php
$title = $from->name_link() . ' ' . $trp->verb_name_link();
// or, if the verb has no other kind of link, just $trp->verb_link()
```

So the cure for a crowded line is often a well-named helper, not only a local
variable — naming the *operation* beats inlining it. (This is the same DRY move
as the always-on "a 3+ step call chain belongs behind a function on the owning
class" rule in `docs/llm/dry.md`; it costs a method but each call site reads at a
glance.)

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

## Validate inside the function, not before the call

A function validates its own input — as a top-of-function guard clause (see the
exception above) — instead of the caller checking the arguments first. The call
site stays one short line, and the check lives in exactly one place that every
caller (now and future) goes through, so a precondition can never be forgotten at
a new call site.

```php
// Wrong — the caller validates, so the check is duplicated at every call site and
// the call is no longer a single short statement
if ($id > 0 and $name != '') {
    $wrd = $this->load_and_link($id, $name);
}

// Right — load_and_link guards its own preconditions; the call stays short
$wrd = $this->load_and_link($id, $name);

function load_and_link(int $id, string $name): ?word
{
    $result = null;
    if ($id > 0 and $name != '') {
        // ... the real work ...
    }
    return $result;
}
```

This is the same move as "push a 3+ step chain behind a function" in
`docs/llm/dry.md`: the validation is part of the operation, so it belongs with the
operation, not scattered across the callers. If a check is genuinely the caller's
business (it changes which function the caller calls, not merely whether the call
is safe), it stays with the caller — but a precondition of *this* function lives
*in* this function.

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

## Never fail silently — record the reason on `$msg`

A function that carries a `user_message $msg` (or `Message`) and can reject, skip
or abort **must record why on `$msg`** before returning the failure, so the caller
and the frontend can surface it. Returning `false` / a default while leaving
`$msg` untouched is a silent failure: the caller sees "not ok" with nothing to
show and the user sees nothing at all.

- Every rejecting branch adds a specific `msg_id` (via `$msg->add(...)` /
  `add_warning_with_vars(...)`); a guard that returns `$msg->is_ok()` must have
  set a message on every path that makes it not-ok. Never make `$msg` not-ok
  without a matching entry.
- Cover *every* fallback branch, not just some. Whenever a value the user supplied
  is missing or invalid and you assign an empty stand-in (`new verb()`, `''`, `0`,
  `null`) in its place, that stand-in is itself a user-relevant issue — add the
  matching `$msg` entry on *that* branch too. Surfacing one fallback while leaving
  a sibling fallback silent is the same silent failure with a gap.
- The entry must carry human-readable text — a `msg_id` that renders to an empty
  string is still a silent failure (the caller `add_message('')`s nothing). Verify
  the case resolves to real text (e.g. a reserved-name rejection names the object
  and the reason).
- The failure must reach the user: a backend write that reports "not ok" has to
  propagate into the frontend `$msg` (see `docs/llm/state-and-messages.md`), not
  be swallowed by a conversion or an `if ($msg->is_ok())` gate that hides it.

This is the `$msg` counterpart of "log the unexpected branch": `log_err` makes an
*internal* inconsistency visible to developers; a `$msg` entry makes a *user-
facing* rejection visible to the user. A real regression this caught: a triple
add was blocked by the reserved-name check, but the rejection message rendered
empty and never reached the UI, so the confirmed save silently did nothing.

### Take `$msg` when the error is the user's to see

The rule above covers functions that *already* carry `$msg`. A function that can
produce an error a **user** needs to act on must go one step further and **take a
`user_message $msg` parameter** in the first place — even a low-level helper — so
it can record the specific problem *and its suggested solution* for the frontend,
instead of only `log_err`-ing it (developer-only) or swallowing it. When a helper
that can raise a user error has no `$msg`, add the parameter and thread it from
the callers rather than downgrading a user error to a silent `log_err`.

Which channel to use follows the cause, not the severity:

- **`log_err` (no `$msg`)** — an *internal* inconsistency the user cannot fix: a
  cache not loaded, a missing method overwrite, an id that should never be 0.
- **`$msg` (add a `msg_id`)** — a cause the user chose or can correct: a name
  already in use, a reserved name, a missing verb on the triple they are adding.
  These need `$msg` so the reason *and the fix* reach the person who can act on it.

Example: `triple::verb_from_api_json()` maps a verb from an api message; a missing
or unknown verb makes the user's triple invalid, so it should take `$msg` and add
a "verb missing — please pick a verb" message, not just `log_err` and return an
empty verb. And it must do so on *every* branch that falls back to `new verb()`
(the missing id, the id `0`, and the null value) — each of those empty verbs is
the same user-relevant problem, so none may be left silent.

## Whatever happens, avoid an uncaught PHP fatal

The general rule behind all the error-handling rules above: **whatever happens —
a corrupted database, an outdated schema, bad input, a missing config — an
uncaught PHP fatal (TypeError, UnhandledMatchError, uncaught exception, call on
null) is avoided**, because a fatal kills the script before it can do the three
duties of error handling:

1. write the error to the system log (`sys_log`), so it is not lost,
2. inform the admin, so the cause gets fixed,
3. show the user a helpful message instead of a white page.

So guard the value before the call that would fatal on it (e.g. check the
db-read result for `false` *before* passing it to `row_mapper(?array)` — see
the DB read result contract in `docs/llm/architecture.md`), catch exceptions at
the layer boundary and convert them to a `log_err`/`log_fatal` plus a `$msg`
entry, and give every `match()` a default arm that logs the unexpected case.
Failing loudly is right — but through the logging and `$msg` channels above,
never by letting the language kill the process.

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