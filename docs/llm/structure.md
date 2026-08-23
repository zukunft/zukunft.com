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
    $msg_ui->add(...);
    // ... more work ...
}

// Right — the work lives inside the positive condition
foreach ($frm_lst as $frm) {
    if ($frm->id() == 0) {
        $msg_ui->add(...);
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

## 100% correct — never a shortcut, tell the user it takes longer instead

The target of this code is to be **100% correct**. Not "correct for the data
seen so far", not "correct enough to pass the test at hand" — correct for every
input the type allows. A shortcut that is right in the common case is a defect
that has not fired yet, and shipping it is worse than shipping nothing, because
the wrong result now carries the authority of a finished job.

So a comparison uses the **complete** value. Never compare a prefix, a cut, a
hash, a rounded number or a normalised copy of what should be compared, and
never use a shortened text as a map key: the moment two values differ only
behind the cut, the code reports them as equal and the difference is lost. The
same holds for a check that "usually" finds every case, a loop that stops after
the first N entries and a sample that stands in for a set.

Shortening the **display** is a different thing and it is allowed: a report line
that stays readable is worth a cut, as long as the cut happens on the way to the
screen and never on the way into a comparison.

The counter-example that named this rule: the json findings report cut every
description after 120 characters, and the cross-file description check used that
cut text as the key of its comparison map. Two descriptions that share their
first 120 characters were silently declared identical, so the check reported
"no finding" for a conflict that stops an import. The fix is not a longer cut:
`json_validation::cut()` is called by the display of a finding only, while
`description_by_name()` keys its map by the full description — cut once, at the
end, and never before the compare.

Cost is never the reason to cut a check. If the complete job needs more time,
more memory or more passes over the data, **do the complete job and tell the
user that it takes longer**. "This scan reads all 183 files twice and takes
about a minute" is an acceptable answer. Silently checking half of them is not.
If a complete solution is genuinely out of reach in this change, say so plainly
and record the gap in `docs/llm/pending_prio_2.md` — an explicit, visible gap is
honest, a quiet approximation is not.

The check for the review: "on which input does this give the wrong answer?" If
that input exists, the code is not finished, however unlikely the input looks.

## Fix the pattern, not the instance — no unexplained asymmetry

The target of every fix is **error-free code**, not a silenced error message.
A defect that was found in one place usually lives in a *pattern* — a pair of
symmetric branches, a family of sibling classes, the same loop over another
list. Fixing only the instance that happened to fail leaves the same bug
dormant in its twins, protected by nothing but the coincidence that no data has
hit them yet.

So when the cause of a defect is understood, apply the corrected rule to every
place that shares the structure, **in the same change**:

- the **symmetric branch**: `sandbox_link::db_ready` skipped the validity check
  of a link end when the verb allows an absent end — found and fixed on the
  `from` side. The `to` side had the identical structure and was only safe
  because the single `needs_to() == false` class happens to have no target
  object at all; one new override would have reintroduced the bug. Both sides
  now encode the one rule: the verb can excuse an *absent* link end, never an
  *unresolved* one.
- the **sibling classes**: a gate defect confirmed in `formula_list::get_ready`
  is the same defect in `triple_list::get_ready` and
  `component_list::get_ready` — fix them together or record the remainder as an
  explicit work item, never leave them silently different.
- the **same pattern elsewhere**: after correcting a call signature at the
  failing call site, sweep for the other call sites of the same function before
  reporting done.

If a counterpart is *deliberately* left different — the semantics really do
differ, or the twin fix needs its own test run — the difference is not left to
be rediscovered: the code comment at the asymmetric place says why, or the
remaining places are listed in `docs/llm/pending_prio_2.md`. An asymmetry
without an explanation reads as an oversight, because it usually is one.

The check for the review: "would this fix have prevented the *next* failure of
the same kind, or only re-labelled the last one?"

## The smallest diff that fulfils the task — never rename or delete unasked

"Reduce to the max" is about the **resulting code**, so that a human reads it
easily. This rule is about the **change**, and it exists for a different
reason: **less work and less risk for the developer**. Every changed line has
to be read and can break something, so the fewest changed lines is the fewest
things that can go wrong.

The rule is aimed at a habit of llm models rather than of humans: a model
changes code readily — renaming, restructuring, tidying on the way past — and
writes long comments while doing it. A human working on the same task stops at
the task. So the target is the smallest diff that fulfils it: touch what the
task needs and nothing else.

So an existing name stays as it is unless the task asks for it. Do not rename
or delete a function, a const, a variable, a db field, a code_id, a view or a
file just because a better name became visible while working nearby, and do not
"clean up" something that the task merely happened to touch. Renaming is the
most tempting of these and the most expensive: it breaks the developer's search
for the old name, it turns a two-line change into a file-wide diff, and it
hides the actual fix among mechanical edits. When a better name is genuinely
worth having, **say so in the final report or record it in
`docs/llm/pending_prio_2.md`** and leave the code alone — a proposal costs the
developer one sentence, an unasked rename costs a review.

The same holds for deleting something that has become unused: "no code
references it any more" is not proof that it is dead. A url var, a `code_id`, a
message id or a json field is an external contract that lives in bookmarks,
saved links, imports and databases, so retiring one is a decision for the
developer.

Two counter-examples from one change, both while adding the missing fields of
the formula link default page:

- `view_relation::relation_type()` was renamed to `link_type()` so that one
  generic `show_link_type()` could serve every link class. The generalisation
  was reasonable; the rename was not requested, and the new function could have
  been added without touching the existing name. It put an unrelated class into
  the diff of a formula-link task.
- `url_var::FORMULA_LINK_PRIO` lost its last php reference when the form field
  was pointed at the url var that the mapper actually reads, so it was deleted
  as dead code. It was not dead — it is a documented url key in the
  human-readable url map — and the half-finished deletion left `url_var.php`
  referencing two consts that no longer existed, so the file would not load.
  The task was to fix the form field, not to retire a url key.

This rule does **not** weaken "fix the pattern, not the instance" above. The
distinction is defect versus improvement: when a *defect* is understood, the
corrected rule goes to every place that shares the broken structure, in the
same change, because those places are broken too. An *improvement* — a nicer
name, a tidier signature, a const that could be dropped — is not a defect and
does not travel.

The check for the review: "does every file in this diff have to be here for the
task to be done?" If a file is there only because something in it could be
better, take it out and write the suggestion down instead.

### Short comments — one line saying why

A small diff is only less risk if the developer can see at a glance that the
code is right. Two things make that possible: **one logical statement per
line** (rule 2), and **short comments**.

The best comment is one short line that says what the code is for. It explains
the *why* — the intent, the reason this case exists — because the *what* is
already in the code below it. A comment that restates the code adds a line to
read without adding anything to know, and a paragraph above a two-line function
hides the function.

Long comments are the same llm habit as unasked renames: they feel like care
and they cost the reader. So when a comment grows past a line or two, ask what
it is doing: if it explains a rule that holds for the whole class, it belongs in
the class docblock; if it explains a decision, it belongs in `docs/`, with one
line pointing there; if it re-tells the code, delete it. The exceptions that
earn more than a line are already named elsewhere in these docs — the comment
behind a `new user_message(` saying why it exists, and the note at a deliberate
asymmetry saying why the twin is different.

The check for the review: "does this comment tell me something the next line
does not?"

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
  Recording the reason on a message that nobody reads is the same silent failure
  one level later, so a message created below the entry point is merged, returned,
  read or kept — see "A created message must reach the caller" in
  `docs/llm/state-and-messages.md`, which `php_user_message_creation_tests` checks.

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

### `log_err` alone is the transitional channel — the target is `log_err_msg`

Even the first channel above is only half the answer. A bare `log_err` tells the
**admin** what broke and leaves the **user** staring at a page that silently did
nothing. `log_err_msg($txt, $msg)` does both in one call: the technical text goes
to `sys_log` exactly as before, and the user gets `msg_id::INTERNAL` with the log
link — a generic "something internal failed, reference X", never the internals
themselves. `log_warning_msg($txt, $msg)` is the same for the warning level
(`msg_id::INTERNAL_WARNING`, added with `ok = true` so it does not abort).

So the long-term target is that **almost every `log_err` becomes `log_err_msg`**.
The table above still decides *which message the user sees* — a specific `msg_id`
when the user can fix it, the generic internal notice when they cannot — but
"nobody tells the user at all" stops being an option.

This is a migration, not a rewrite: the bare `log_err` calls still outnumber the
`log_err_msg` ones by roughly ten to one, and converting them in one sweep would
be untestable. Do it opportunistically instead:

> **When you change anything in a function that contains a `log_err`, give that
> function a `user_message $msg` parameter (threaded from its callers) and switch
> its `log_err` calls to `log_err_msg`.** Adding the parameter is the point — once
> `$msg` is in the signature, the switch is one word per call.

Two cases stay on the bare `log_err`, and both are recognisable:

- the function has **no caller that could hold a `$msg`** — an entry point, a
  bootstrap step, a cron job, or a display function that only returns HTML;
- the message would fire on a **normal** path, where a notice is noise rather
  than information (a not-yet-filled id during an import, see
  `docs/llm/state-and-messages.md` on which message belongs where).

Before threading, check what the function reports on the happy path — turning a
silent drop into user-visible noise is the failure mode this rule can cause.

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

### A `Throwable` never travels

The target is that **no `Throwable` is part of the program flow**: an exception
is not a return value, not a control-flow tool and never a way to hand a problem
to the caller. A function neither throws to signal a result nor declares
`@throws` so that somebody further out deals with it — it reports through
`user_message` and returns.

That means an exception is caught **as early as possible**: the `try` wraps the
single statement that can raise one (the `new DateTime($txt)`, the json decode,
the third-party call), not a whole block and not a wrapper three layers up. The
handler then does two things and the exception is gone:

1. turn it into a **translatable message** — a `msg_id` case with en/de
   translations added to `$msg`, never the raw exception text, which is english,
   internal and often contains a file path (see `docs/llm/state-and-messages.md`);
2. if the cause is **unexpected** — something the user cannot have caused and
   cannot fix — call `log_err_msg($txt, $msg)`, which writes the error to the
   system log for the admin *and* puts the generic internal notice with the log
   link on `$msg`, so all three duties above are done in one call.

A cause the user *can* fix (a malformed date they typed, a value out of range)
needs no `log_err_msg`: it is a normal user message with the specific `msg_id`
that says what to do, because an entry in the admin log that nobody has to act
on only hides the real errors.

- **Right** — the `try` around the single `new DateTime($time_str)` in
  `change_log::set_time_str`: the one statement that can raise is wrapped, so
  the exception never leaves the setter. What is still missing there is the
  second half of the rule — the `catch` only calls `log_err(...)`, so the user
  learns nothing; with a `user_message $msg` parameter it becomes a
  `log_err_msg()` (or a specific `msg_id` if the string came from user input).
- **Wrong** — letting the `DateTime` constructor throw out of the mapper so that
  the api entry point (or nothing at all) catches it: the response ends
  mid-json, the user gets a white page and the reason is only in the web server
  log, which the pod admin cannot read.

The exception handlers registered at the entry points (`api/api_const.php`,
`test/*`) are the **last** safety net for the case this rule was missed, not the
place where exceptions are meant to be handled. A `Throwable` arriving there is
a defect in the layer that let it travel.

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