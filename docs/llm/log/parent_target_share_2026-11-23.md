# parent target share (2026-11-23)

Part of #247, the step "check" of the portfolio split page compared with its sample in `docs/llm/pending.md`.

## Request

Add the parent target share formula, so that the read only field "Target weight parent" of the portfolio split page
follows the target weights of the children like the sample: in the cases high, low and over of
`portfolio_split_calculator_diff_*.md` the generated page keeps 30 %, the sample shows 100 % minus the children,
e.g. 10 % for 45 % and 45 %.

The share of the parent is a stored value (0.3, source "split agreement"), which an input of the children cannot
change. The formula takes the same operands as the validated "target share excess formula", so that the backend finds
them the same way, and names "target share" on the left side, because the backend removes the operand phrases and the
assigned word from the phrases of the result.

### Follow-up request of 2026-11-24

"the differences in src/test/resources/web/html/use_cases/portfolio_split_calculator_diff.md are gone, which is fine.
Try now to reduce the differences in src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_differ.md.
If src/test/resources/web/html/use_cases/portfolio_split_calculator_sample.html contains something strange, it is also
allowed to change this."

Decision: in the case differ (30 % and 40 % with mirroring asked) the generated walk gives the second child the count of
the first child in all 42 rows, while the sample mirrors only equal weights (`readParams`: `mirrorAsked && sameWeight`),
as the word `mirror` also says ("needs equal target shares"). So the formula "walk count of the second child" mirrors
only if the existing "target share difference" is zero, which is the same condition that hides the warning of the
difference. The sample is right here and stays unchanged. Left open: the exchange rate field and the bond price decimals
(both in `docs/llm/pending.md`, "check") and the realised rows, which are modelled values until a formula can sum over
the rows (step 9, part 5).

"in pending.md for '#### check' ... Button colours ... Font ... the generated version is fine and the sample should be
adjusted"

Decision: the sample linked its stylesheets as `/external_lib/...` and `/main/resources/...`, which do not resolve for a
snapshot file, so it showed the bootstrap buttons and the browser font; it now links them from `http://localhost/` like
the other samples (see `docs/llm/coding.md`, the html snapshot rule), and the two bullets are removed from `pending.md`.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` | the value `example portfolio split, parent portfolio, target share, percent` = 0.3 | the formula "target share of the parent" `"parent portfolio","target share" = "original" - "first child portfolio" - "second child portfolio"` appended at the end of the formulas, assigned to target share; the value removed; its calc-validation next to the one of the target share excess | the parent keeps what the children leave, so the field and the target value of the parent follow the inputs of the children |
| `src/main/php/web/component/execute/ui_list.php` | `calculator_read_only` shows only a value; `field_source_refs` names the source of a value or of its basis values | a read only field without a value shows the one result of its phrase with the field value phrases (`calculator_read_only_result`); the source refs of a result are the ones of its operands (`formula_calc::operands`), so that the hint keeps the "[1]" of the split agreement; tests in `unit_ui/use_case_ui_tests.php` | the page without javascript shows the calculated share of the parent |
| `src/main/resources/script/calculator_json.js` | `readOnlyField` shows only a value and is not drawn again after an input change; `operandValues` finds the operands of all page results in one loop | the same result fallback and source refs as `ui_list`; `redrawResults` draws the read only fields again; the operands of one result in `resultOperands`, which `operandValues` calls | the page with javascript shows the share of the parent that the inputs of the children leave |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the formula ids of `ahv_vat_vote.json`, `share_buy_sell_abb.json` and `table_editor_sample.json` | — | — | the new formula of the portfolio split takes the next formula id, and these use cases are imported after it (`files::USE_CASE_FILES_APPENDED`); a use case row is selected by its name, so only snapshots with the ids change |
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_*.html`, `portfolio_split_calculator_diff_*.md` | — | — | the parent field and the target value of the parent follow the children in the cases high, low and over; written by the run |
| the calc-validation of the target value of the parent (`target value of a beneficiary`) | — | — | its operand target share of the parent is a result now; if the backend does not calculate a formula on a result, the import reports the calc-validation |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/test/php/unit_write/a_selected_test.php` | `horizontal_tests`, `formula_calc_tests`, `expression_tests` (namespace `unit`) and `horizontal_ui_tests` (namespace `unit_ui`) are switched on, but the file has no `use` line for them | the four `use` lines in their blocks; a read-only scan of every `new` of the file and the inspection of the ide find no other class without its `use` line (`db_check` stands in a comment block) | the run of `test/test_part.php` of 2026-11-23 stopped with "Class "...\unit_write\horizontal_tests" not found" at line 363: the classes are included by `test_base.php`, but without the `use` line PHP looks for them in the namespace of `a_selected_test`; the four lines were switched on before this change (e4988d819 for `horizontal_tests`), so `test_part.php` has not run since, and when I switched on `use_case_ui_tests` for the operand fix I have added its `use` line but not checked the other switched-on tests of the file |
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` | the formula "walk count of the second child" takes the count of the first child if `"mirror" = 1`; the 42 calc-validations of the formula without the phrases of the difference | `if( "mirror" = 1 & "target share difference" = 0 ; ...` and its description; `"target share difference", "percent"` added to the context of the 42 calc-validations, so that the import and the page find the operand; the stored numbers stay, because the difference of 35 % and 35 % is 0 | follow-up of 2026-11-24: in the case differ the generated walk mirrors 30 % and 40 %, which gives the parent the count of the second child (42 walk rows and 4 rows of the check table of `portfolio_split_calculator_diff_differ.md`) |
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_sample.html` | the three stylesheet links start with `/` | they start with `http://localhost/`, the site css as `src/main/resources/style/style_html.css` | follow-up of 2026-11-24: the developer accepts the grey buttons and the font of the generated page; the sample differed only because its links did not load the stylesheets |
| `src/test/resources/web/html/use_cases/CBAM_tree_calculator_sample.html` | the three stylesheet links start with `/` | they start with `http://localhost/` like the portfolio split sample | follow-up of 2026-11-24: the only other sample with the same links, fixed on request of the developer |
| `src/main/resources/script/calculator_json.js` | `percentText` shows every percent from 10 % on as a whole number | only a result; a stored value keeps the percent decimals like `sandbox_value::val_formatted` | follow-up of 2026-11-24: the developer keeps the php and the "100.5 %" of the page without javascript and of the sample, which the js page showed as "100 %" (ConfedBond30, NestleBond27); the note in `pending.md` had blamed `calculator decimals`, but the page without javascript already showed the decimals |
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` | the calc-validation of "target share of the parent" after the four of "target value of a beneficiary" | moved before them | the run of 2026-11-24 stopped the import with "the imported result 5883270 of ... parent portfolio, target value ... does not match the result 19610900": the calc-validation checks in file order and takes a reproduced result only as an operand after it is checked, so the target value of the parent found only the value 100 % of the whole portfolio; the split deviation of the parent was then reported as its consequence |
| `src/main/php/cfg/helper/data_object.php` | `number_by_names` takes the first value and a checked result only if no value matches | the values and the checked results in one list, the number with the fewest phrases that neither the operand nor the result names, on a tie the first (so a value before a result); tests `calc_validation_operand_choice.json` in `unit/import_tests.php` and the line in `docs/llm/json_values.md` | the same run: also after the move the value 100 % comes before the result 0.3 of the parent; the page already chose by the phrases of the result, so the import did not check what the page shows |
| `src/main/php/web/formula/formula_calc.php`, `src/main/resources/script/calculator_json.js` | `value_of` and `operandEntry` take the number with the most phrases of the result (the operand fix of 2026-11-23) | the number with the fewest phrases that neither the operand nor the result names, like the import | the same run: every payback of `pv_lifetime_balance_no_js.html` shows "12'482 a", because the gross avoided emission over the lifetime names `lifetime` and two phrases of the payback and so has beaten the lifetime itself; a read-only check of every calc-validation of the import files finds no other number that the new rule changes |

## Lessons learned by llm (2026-11-24)

- A rule that chooses between several numbers is checked against every file that uses it before the change, not only
  against the case that asked for it: the "most phrases of the result" fixed the portfolio split and broke the PV
  payback, and a read-only script over the calc-validations would have shown that before the run.
- The page and the import calc-validation choose an operand by one rule; when one of them changes, the other one
  changes in the same step.

## Lessons learned by llm

- A switched-on line in `a_selected_test.php` is only runnable if the class has its `use` line in the file; when one
  test is switched on, scan every `new` of the file for a missing `use` line, because `test_part.php` stops at the
  first one.
