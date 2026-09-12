# Dependent errors — when a step may stop collecting

Detail for the `user_message` rules in `docs/llm/state-and-messages.md`. That
document says *how* a message travels; this one says *when a step is allowed to
stop and not report any further*.

## The goal: as many certain errors as possible, and no guesses

`$msg` collects errors so the user can fix several of them in one round trip.
Showing one error per retry is bad — but so is showing errors the user cannot
trust, because chasing an error that was never real costs more time than a
second retry.

So the errors of one request split in two:

- an **independent error** is real no matter what else failed. It is worth
  reporting even if the request already has other errors.
- a **dependent error** exists *only because* an earlier step failed, and may
  well disappear once that earlier error is fixed. Reporting it sends the user
  after a ghost.

The target is: **report every independent error, suppress the dependent ones.**

The test question for a step is therefore not "did anything go wrong so far?" but:

> If the user fixes the errors already collected, can the errors of *this* step
> still be there?

- **Yes, unchanged** → the step's errors are independent. Run it, always.
- **Maybe not** → the step's errors are dependent. Guard the step with
  `if (<the step it depends on>->is_ok())`.

## Example: one import file with three errors

```json
{
  "words": [
    { "name": "Zurich", "type": "not-a-type" },
    { "name": "inhabitants" }
  ],
  "sources": [
    { "url": "https://www.zh.ch/statistik" }
  ],
  "values": [
    { "words": ["Zurich", "inhabitants"], "number": 421878 }
  ]
}
```

Three things are wrong, and they are not of the same kind:

| # | error | kind | why |
|---|-------|------|-----|
| A | the word `Zurich` has an unknown type `not-a-type` | independent | the type is wrong on its own terms; no other fix changes that |
| B | the source has no `name` | independent | unrelated to the words; still wrong after A is fixed |
| C | the value cannot resolve the phrase `Zurich` | **dependent on A** | the phrase is missing *because* A stopped the word from being created |

The user must see **A and B**. Showing C as well is actively harmful: it points
at the `values` section, which is correct, and the user cannot tell that fixing
the word type will make it vanish. After fixing A the import may still fail on
some *genuine* value error — but that one is only certain once A is gone.

So the `values` step is guarded, and the `sources` step is not.

## Rule 1 — gate on the step you depend on, not on the request message

This is the rule that decides whether the concept works or backfires. C depends
on **A**, not on "anything that went wrong". If the value step asks the *request*
message, then B — a completely unrelated source error — also suppresses it, and
the user loses a real independent error for no reason.

```php
// Wrong — the values step gates on everything collected so far,
// so a bad source name hides every value error although values do not
// depend on sources at all
$msg->merge($this->dto_get_words($wrd_array, $dto, $msg, $wrd_per_sec));
$msg->merge($this->dto_get_sources($src_array, $dto, $msg, $src_per_sec));
if ($msg->is_ok()) {
    $msg->merge($this->dto_get_values($val_array, $dto, $msg, $val_per_sec));
}
```

```php
// Right — the words step gets a scoped message, so the values step can ask
// the only question that matters: "did the step I depend on fail?"
$wrd_msg = new user_message(); // scoped, so the value step below can test the words step alone
$this->dto_get_words($wrd_array, $dto, $wrd_msg, $wrd_per_sec);
$msg->merge($wrd_msg);

// sources are independent of the words, so they are never suppressed
$msg->merge($this->dto_get_sources($src_array, $dto, $msg, $src_per_sec));

// values resolve the phrases that the words step creates, so a missing phrase
// here is only a certain error once the words step succeeded
if ($wrd_msg->is_ok()) {
    $msg->merge($this->dto_get_values($val_array, $dto, $msg, $val_per_sec));
} else {
    $msg->add(msg_id::IMPORT_STEP_SKIPPED, [
        msg_id::VAR_NAME => json_fields::VALUES
    ], true); // ok = true: inform, but do not suppress the steps after this one
}
```

`msg_id::IMPORT_STEP_SKIPPED` is the message this pattern needs, so it renders as

> *values has not been checked yet, because the errors above would probably cause
> follow-up errors here. Please fix the errors above and import again.*

It names the step, says the list is deliberately incomplete, and gives the reason
— all three of which the user needs to read the result correctly. It is always
added with `ok = true`: it explains a suppression, it must never cause one.

The scoped message is the whole point: a step can only be skipped *for a reason*,
and the reason has to be nameable. `if ($msg->is_ok())` over the request message
names no reason — it just means "something, somewhere".

A step may of course depend on more than one earlier step; then it tests each of
them (`if ($wrd_msg->is_ok() and $trp_msg->is_ok())`), which is still a named
dependency and still lets every unrelated error through.

## Rule 2 — only a certain error may close a gate

A gate is only as good as the message it reads. Any entry that lands in a gating
message with `ok = false` suppresses everything that depends on it — so a message
added on a **normal** path silently switches off unrelated work.

`Message::add()` takes exactly this flag:

```php
function add(?msg_id $msg_id, array $var_lst, bool $ok = false): void
```

- `$ok = false` (the default) — a real error: the text is shown **and** the
  message becomes not-ok, so dependent steps are skipped.
- `$ok = true` — the text is shown, the status stays ok, nothing is suppressed.
  This is the channel for a notice, a progress remark, or a condition that is
  normal at this point in the process.

The regression this rule exists for: during an import, links are resolved in a
later pass, so a triple whose `from` is not filled **yet** is the normal state,
not an error. `sandbox_link::can_be_ready()` nevertheless added `FROM_MISSING` /
`TO_MISSING` to the shared import message with the default `ok = false`. Every
gate after it read a not-ok message, so the following import sections were
skipped, and an import that had nothing wrong with it produced an almost empty
result — with the correct suppression machinery working exactly as designed on a
false input.

Two ways out, and the choice depends on who needs to know:

- the check gets **its own message**, so its verdict is about the object and
  never reaches a gate it should not close (this is what
  `sandbox_link_list::add_link_by_key()` does with `$rdy_msg`);
- or the notice is added with **`ok = true`**, when the user should still read it.

Note how `merge()` carries the flag:

```php
function merge(Message $msg_to_add): void
{
    foreach ($msg_to_add->get_all_var_messages() as $msg_var) {
        $this->add($msg_var[0], $msg_var[1], $msg_to_add->is_ok());
    }
}
```

The **whole** source message's status is applied to **every** entry it copies. So
a buffer that holds one real error and one friendly notice merges as all-error —
a mixed buffer loses the distinction. Keep a message that gates one step free of
anything that is not a certain error of that step.

## Rule 3 — a skipped step says that it was skipped

Suppression is a promise to the user: "these are the errors that are certainly
real." The other half of that promise is telling them the list is not complete.
Without it, the user fixes A and B, re-runs, and meets an error they have never
seen before — which reads as *the fix broke something else*.

So the `else` branch of a gate is never empty. It adds a notice (`ok = true`, per
rule 2) naming the step that was not checked, exactly like a compiler that ends
with "further errors suppressed".

This also keeps the difference between the two states that otherwise look
identical from the outside:

- the value list is empty because the file has **no** values → nothing to say;
- the value list is empty because the step was **not run** → say so.

Silence turns the second into the first, and an import that skipped half its work
then reports as a success with a small result.

## Rule 4 — the gate and the verdict are different questions

`if ($msg->is_ok())` asks **"should this step run?"**. `return $msg->is_ok()`
answers **"what happened in this step?"**. When both read the same shared
message, the two questions collapse into one variable, and the function can no
longer tell "I did the work and it was fine" from "I never ran".

Keep them apart — the gate reads the message of the step depended on, the return
reports this step's own outcome:

```php
function dto_get_values(array $json_array, data_object $dto, user_message $msg): bool
{
    $added = false;                     // what happened here
    if ($this->words_ok($dto)) {        // should this run
        // ... the real work, reporting into $msg ...
        $added = true;
    }
    return $added;
}
```

A boolean that means "added" cannot be confused with "the request has no errors",
so a later caller cannot mistake a suppressed step for a successful one. The same
applies to the list `add*` functions: `return $msg->is_ok()` there reports the
state of a message that the caller also writes into, while the caller wants to
know whether the object went into the list.

## Checklist before adding `if ($msg->is_ok())`

1. **Which earlier step do these errors depend on?** If the answer is "none",
   do not gate — the errors are independent and the user should see them.
2. **Is the gated message scoped to that step?** Gating on the request message
   suppresses unrelated independent errors (rule 1).
3. **Does that message hold only certain errors?** Normal-path notices go in with
   `ok = true`, or into a message of their own (rule 2).
4. **Does the `else` branch tell the user the step was skipped?** (rule 3)
5. **Can a caller tell "skipped" from "done, nothing found"?** (rule 4)

## Where this does not apply

- **A pure abort.** One guard at the top of a pipeline over a message scoped to
  that whole pipeline is not suppression of dependent errors, it is a
  precondition — `import::get_data_object()` checks the file header first,
  because importing the body of a malformed file produces only noise. Fine as is.
- **A write.** A gate decides whether to *examine* more, and skipping an
  examination costs the user a retry. Skipping a *save* costs them their data, so
  a save that does not happen is reported as a failed save (`IMPORT_NOT_SAVED`),
  never as a quietly skipped step.
- **Independent siblings in a loop.** Rows of a list do not depend on each other:
  a bad row 3 must not stop rows 4..n from being checked. Use a per-row buffer and
  merge each one, so the user gets every bad row at once.