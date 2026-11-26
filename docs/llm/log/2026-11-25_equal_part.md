# equal part (2026-11-25)

Part of #247, part 3 of step 9 of the portfolio split page in `docs/llm/pending.md`: the equal part of a holding when
the rounded counts of the children exceed it.

This log is started before the first edit, so the tables below are the plan; "Unexpected changes" stays empty until
the developer has run the tests.

## Request

"see /docs/llm/coding.md and try to fix part 3 of step 9"

Part 3 has three open points: the equal part of a holding when the rounded counts exceed it, a formula by the
instances (`first child` and `second child` are named in the formulas) and a rounding rule for a tie. Only the first
one shows in the case reports: `portfolio_split_calculator_diff_over.md` lists 54 differences, and every other case
report lists 0. The case over asks target shares above 100 %, so both children round up to more than the holding:
the sample (`split` of `portfolio_split_calculator_sample.html`) then gives every child at most
`floor( original / children / lot size ) * lot size` ("If the rounded counts together exceed the position, every child
gets at most an equal part in round lots"), the generated page gives each child its rounded count and the parent a
negative count (LISP: sample 5, 5 and 0, generated page 6, 6 and -2). The carried deviation, the targets of the next
rows and the realised rows follow. The instances belong to the add child task (step 3), and the tie shows in no
report, so both stay open.

The formula language has `round` but no `floor` and no `>`. The developer decided on 2026-11-25: a new function
`floor` in all five evaluators (`docs/llm/coding.md`: a new math function), not an emulation with `round`. The
condition "the rounded counts exceed the holding" is `max( sum - original - lot size / 1000000 ; 0 ) = 0` for "not
over", because the condition compares exactly and a sum of two counts of a lot of 0.01 has float noise, e.g. two
mirrored halves of a cash holding at 50 % each, which the sample does not cap.

The 42 stored rows of the standard case never reach the cap (the smallest parent count is 2), so the stored numbers
and the calc-validation numbers stay.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/shared/const/chars.php` (`FUNC_FLOOR`, `FUNC_ONE_NUMBER`), `src/main/php/shared/calc/arithmetic.php` (`one_number`), `src/main/php/web/formula/formula_calc.php` (`TOKEN_PATTERN`), `src/main/resources/script/calculator.js`, `calculator_json.js` and `formula_editor.js` (`ONE_NUMBER`, the token patterns), `src/test/php/utils/math_function_report.php` (`FUNCTIONS`, `EDGE_CASES`), `src/test/php/unit/math_tests.php`, `src/main/resources/translations/en.yaml` and `de.yaml` (`formula_function_missing`), `docs/json_structure.md` | no function that rounds down | `floor( x )`, the largest whole number not above x, in the backend (`calc_internal.php` takes `FUNC_ONE_NUMBER`), the page check, the import check and the three scripts; tests: `floor( 2.7 )`, `floor( -2.5 )`, `floor( 3 )` and the texts `floor( 1 ; 2 )`, `floor 2`, `floor( 2` without a number | the equal part of a holding in round lots, the same in every evaluator |
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` (the formulas "walk count of the first child" and "walk count of the second child" and the 42 calc-validations of the first) | the rounded count of a child, at most the holding | the rounded count of a child, and if the rounded counts of both children exceed the holding at most `floor( "original" / 2 / "lot size" ) * "lot size"`; the first child names its target as `"target quantity","first child portfolio"` and its 42 calc-validations get the phrases of the second child (`example portfolio split`, `mirror`, `assumed`, `target share difference`, `percent`, `second child portfolio`), like the ones of the second child | the case over walks like the sample; the parent keeps the rest, which is never negative |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_over.md` | 54 differences | 0 differences | the walk of the case over caps the children like the sample |
| the other case reports | 0 differences | 0 differences | their rounded counts never exceed the holding, or the sample caps them the same way |
| `src/test/resources/api/phrase_data/phrase_data_portfolio_split.json`, `portfolio_split_calculator_js.html`, `portfolio_split_calculator_no_js.html` | — | — | the expressions of the two formulas and the context of the 42 results of the first child; the numbers stay |
| `docs/math.md` | — | — | `php bin/math_functions.php` lists `floor` with its example and the edge cases `floor( -2.5 )` and `floor( 2.5 )` |
| the snapshots of the formula editor | — | — | the button list after a press on more (`MORE_FUNCTIONS`) takes the keys of `ONE_NUMBER`, so it shows `floor` |
| the ids | — | — | no word, triple or formula is added, so no id shifts and no re-pin |
| `docs/llm/pending.md` | part 3 open | the equal part done, waiting for the run | the instances and the tie stay open |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `docs/math.md` | without `floor` | the row of `floor` and the edge cases `floor( -2.5 )`, `floor( 2.5 )` and `floor( 1 ; 2 )`, the same in all five evaluators | expected, but not written by the run: the run reported "check that the docs with the math functions is updated", because `docs/math.md` is written only by `bin/math_functions.php`; written with `php8.4 bin/math_functions.php` (see the lessons) |
| `src/test/resources/unit/formula/list.csv` | the two count formulas without the cap | the expressions with the cap in the db form (`{t4579}=if(max(...` ) | the plan named no `list.csv`, because no id shifts, but the csv has the expression of each formula |
| `src/test/resources/unit/result/list.csv` | the 84 results of the child counts with the group of their old context | the same 84 results with the group of the new context (the phrases of the second child in the first child context); every number is unchanged | the plan named no `list.csv`; a result row has the group of its context, which the 42 first child calc-validations changed |
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_no_js.html` | 2.8 MB | 4.0 MB, about 29'000 lines more | expected as "the expressions of the two formulas", but not the size: the page holds the tokens of the formula per result, and the two count formulas repeat the rounded count of a child three or four times, so each of the 84 results has a long token list; a separate result "rounded count" per child would make the expressions short, but needs a new word and 84 calc-validations more |
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_over.md` | 54 differences | 0 differences | as expected |
| `src/test/resources/import/wikipedia/cache/live_Health.json` | `tid` of the last live call | a new `tid` | not caused by this change: the live cache of the wikipedia import is written by each run |

## Lessons learned by llm (2026-11-25)

- `docs/math.md` is written by `php bin/math_functions.php`, not by the test run (`AUTO_UPDATE_TEST_FILES` is
  `false`), so a new math function fails "check that the docs with the math functions is updated" until the report
  is written again; the default `php` of the developer pc is 8.5 without mbstring, so the report is written with
  `php8.4 bin/math_functions.php` in the same change as the new function.
- A changed formula expression or calc-validation context changes the `list.csv` of the formulas and of the
  results even if no id shifts, so the side effects table names them.
- A long formula is repeated per result in the page without javascript (its tokens), so a sub-expression that a
  formula repeats is better a result of its own when the formula has many results.
