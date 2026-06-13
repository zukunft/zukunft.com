### fermi live-adjust (the sign-flip loop)

Goal of this whole block: a visitor changes ONE assumed input value of a Fermi
estimate and sees the dependent result — and its sign — recompute live, without
a page reload and without a developer rebuilding anything. Success metric is not
"feature complete" but "one conversation partner changes the sign-determining
value and does not stall". Build the smallest path that delivers that loop;
defer everything generic. Work the prompts in order — each is independently
shippable and testable, and each starts by writing the unit test first
(`src/test/php/unit_ui/...` for rendering, `src/test/php/unit_workflow/...` for
the edit path), per `docs/llm/testing.md`. Use the `fermi_discussion.json`
import fixture (the status-quo-harm / rollback / VoI chains) as the live data —
the sign-determining input is the value tagged `overstimulation gap` + `factor`
+ `assumed value`, whose change flips `rollback net balance` between a wash and
  a clear win.

1. Write a unit test in `src/test/php/unit_ui/fermi_ui_tests.php` that asserts a
   single result value renders together with its full input chain: build a
   `result_ui` from a `create/test_result::sign_flip_chain_ui()` factory (do not
   repeat the class object name in the factory method), positive test asserts
   the snapshot fragment in `object_pages/fermi_chain.html` shows the result
   number, the formula name, and each input value with its provenance qualifier
   (`measured value` / `assumed value`) visible; negative test asserts an empty
   input chain reports the documented empty result, not just "no exception".
   Reuse `web/value/value.php::value()` and `web/result/result.php::display()`
   for the rendering — do not duplicate their html.

2. Add to `web/result/result.php` a `chain_ui(phrase_list $context, string $back = '')`
   method that returns the html of one result plus the list of input values it
   was computed from, each input rendered via the existing
   `value::value_edit($back)` so the input is already a click-to-edit link. Read
   the "how many inputs to show" limit from `$ui->cfg` (new pod config value,
   never `new config()`); pass `$size`/`$page` as explicit parameters, never
   from superglobals. Single-exit: assemble into one `$result` string and return
   it once. Provenance marker comes from the value's qualifier phrase, taken
   from the `words` group — never from a free-text note field.

3. Extend the value-edit workflow (the `mask`/`confirm` path already sketched in
   the "workflow" block above, the `view.php?mask=...&confirm=1` url) so that on
   confirm of a value that is an INPUT to at least one formula, the affected
   results are recomputed and their new numbers returned in the same response.
   Write the workflow test first in
   `src/test/php/unit_workflow/fermi_recalc_tests.php`: set the `overstimulation
   gap` input, confirm, assert that `rollback net balance` is recomputed to the
   expected reproduced number (use the value from the `calc-validation` block of
   the import fixture as the oracle, so the test fails loudly if the engine and
   the fixture ever diverge). Recompute uses the existing
   `web/formula/expression.php` element evaluation against the backend
   `cfg/formula/formula.php::calc` path — do not write a second evaluator in the
   frontend. On any reproduction mismatch, route a translatable error via `$msg`
   (new `msg_id` case in `messages.php` with en/de text), do not silently show a
   stale number.

4. Add an async (no full page reload) recompute endpoint: a thin arm on the
   existing `api/value` / `api/resultList` controllers that accepts one changed
   input value and returns the recomputed dependent results as json (the api
   json the frontend view-model already consumes). Move any inline SQL out of
   `web/` into prepared, parameterized statements in the model layer (a
   string-concatenated `WHERE ... = $id` is an injection risk). Test first:
   positive asserts the changed input yields the reproduced result number,
   negative asserts an unknown input phrase returns the documented `$msg` error,
   not a DB fallback (`import_mapper` rule: resolve only from the passed-in data
   object, never read the DB to paper over a missing reference).

5. Wire the front-end so editing an `assumed value` input in the `chain_ui`
   posts to the recompute endpoint and swaps in the new result number and its
   sign in place. Keep it to the one chain — no four-column layout, no tab
   switch, no miniature preview (those stay in the generic `word frontend` block
   and are NOT on this critical path). The only visible state that must change
   live is: the edited input number, the dependent result number, and a sign
   indicator (e.g. result styled differently when it crosses zero). Reuse the
   existing user-vs-standard styling (`styles::STYLE_USER`) so a visitor's
   override is visually distinct from the stored standard, mirroring
   `value::value()`.

6. Self-test the loop before inviting anyone: load the imported
   `fermi_discussion.json`, open the `rollback net balance` chain, and change the
   `overstimulation gap` input from 100 down through 30 to 10. Assert (manually
   and as a final workflow test) that the net result moves from ~2.2M HTP toward
   ~59.8M HTP and that the sign indicator changes as the wash becomes a clear
   win. If the change is visible and immediate, the live-adjust loop is done —
   independent of how much of the rest of `pending.md` is finished. If it is not
   immediate, the latency-killing gap is in one of steps 2–5; fix that before
   adding any generic feature.
