# rounding noise, cell result test and table step times (2026-11-25)

Three changes of 2026-11-25, each with its own plan: the rounding noise (part 3 of step 9 of the portfolio split
page in `docs/llm/pending.md`), the unit test of a cell with a result (part 4 of step 9) and the time of the steps
of a table in the time report. Each plan was started before the first edit of its change, so its tables are the
plan; its "Unexpected changes" stay empty until the developer has run the tests.

## 1. rounding noise

Part of #247, part 3 of step 9: decision 1, the rounding of a tie.

### Request

"see /docs/llm/coding.md and review '## Calculator editor ### portfolio split page compared with its sample' in
pending.md and remove the task already done and do the next task"; the next task that an LLM can do is the rounding
noise of decision 1 ("keep the stored numbers and try to avoid the rounding noise"), because the check of part 1
needs a calculation job of the developer.

`round` and `floor` take their argument as calculated, and `calculator_json.js` writes every operand with 12
decimals (`toFixed(12)`), so a tie with float noise below 5e-13 can round up in one evaluator and down in another,
e.g. 420.49999999999999 is 420 in the backend and 421 on the page.

A simulation of the walk of the sample (its `split` function in node, read only) shows six ties with noise, all in
the case standard: SCHP 420.49999999999926, STMN 874.49999999999841 and ABBN 1925.4999999999966, each for both
children, about 7e-13 below the tie; the cases low, high, over and differ have none. The sample rounds them down,
and today every evaluator also gives 420 there. The plan of `pending.md` (round the argument to the 8 noise
decimals of `number_format`) would make them real ties that round up, so the recalculated case standard would
differ from the sample from SCHP on.

Decision of the developer (2026-11-25): round the argument of `round` and `floor` to 12 decimals in all five
evaluators, the same 12 decimals that `calculator_json.js` uses for an operand. The evaluators then agree on a tie
with noise below 5e-13, the three ties of the sample stay rounded down, the stored numbers stay and the case reports
stay at 0. The scripts round with `Number(x.toFixed(12))`, which rounds the exact binary value like php `round`,
and not with a multiplication by 1e12, which loses digits for a large number such as 30'000'000 JPY.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/shared/calc/arithmetic.php` (`one_number`, new const `ROUND_NOISE_DECIMALS`) | `round($num)`, `floor($num)` | `round(round($num, 12))`, `floor(round($num, 12))` | the backend (`calc_internal::one_number`), the page check and the import check take `arithmetic::one_number` |
| `src/main/resources/script/calculator.js`, `calculator_json.js`, `formula_editor.js` (`ONE_NUMBER`) | `round` and `floor` of the argument | of the argument rounded to 12 decimals (`Number(x.toFixed(12))`) | the same tie in all five evaluators |
| `src/test/php/unit/math_tests.php`, `src/test/php/utils/math_function_report.php` (`EDGE_CASES`) | — | positive: `round( 1.005 * 1000 / 10 )` is 101 (calculated 100.49999999999999) and `floor( 0.29 * 100 )` is 29 (calculated 28.999999999999996); negative: `round( 420.49999999999926 )` is 420 (the tie of the sample stays) and `round( 420.499999 )` is 420 | the tests first; the report checks the three scripts too |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `docs/math.md` | — | the three new edge cases, the same in all five evaluators | written with `php8.4 bin/math_functions.php` in this change |
| the case reports of `portfolio_split_calculator` | 0 differences | 0 differences | no case reaches a tie with noise below 5e-13 (simulation above) |
| the results and the snapshots | — | — | no stored number is at a tie with noise below 5e-13, so no result changes |
| `docs/llm/pending.md` | decision 1 with 8 decimals | 12 decimals, the task done, waiting for the run | |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/test/php/utils/math_function_report.js` (`CALC_CONSTS`, `JSON_CONSTS`, `EDITOR_CONSTS`) | the consts of the scripts up to `ONE_NUMBER` | + `ROUND_NOISE_DECIMALS` and `WITHOUT_NOISE` before `ONE_NUMBER` | the report loads only the listed one line consts of each script, so the three scripts gave "fatal: ReferenceError" for every `round` and `floor` in `php8.4 bin/math_functions.php` |

Before the change `php8.4 bin/math_functions.php` showed the difference that the change removes: `round( 1.005 * 1000 / 10 )`
was 101 in the backend and 100 in the page check and the three scripts, `floor( 0.29 * 100 )` 29 and 28. After the
change all five evaluators give 101, 29 and 420 (`round( 420.49999999999926 )`), and the report lists the same 10
edge cases with a different result as before.

### Lessons learned by llm (2026-11-25)

- A const that a function of `ONE_NUMBER` uses is added to the const lists of `math_function_report.js`, because the
  report loads each script only by its listed one line consts.
- Before a rounding rule is changed, a simulation of the sample with the case inputs shows the ties that the rule
  would move; here it showed that 8 decimals would have broken the case standard.

## 2. cell result test

Part of #247, part 4 of step 9: the unit test of a cell with a result.

### Request

"see /docs/llm/coding.md and do the next open task of '## Calculator editor ### portfolio split page compared with
its sample' in pending.md and remove the task already done". The rounding noise is done (all case reports stay at 0),
the check of part 1 needs a calculation job of the developer, so the next task is part 4: a cell of a table with
`calculator page independent` reads the one result of its phrases if no one value qualifies
(`ui_list::calculator_general_value` with `$with_results`), which the case reports cover, but no unit test does.

The function exists and is not changed; the tests describe its behaviour, so a failing test is a bug of the
function and not of the test.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/test/php/unit_ui/use_case_ui_tests.php` (after the cell text tests of a share) | no test of `calculator_general_value` | positive: a cell of a page independent table reads the one result of its phrases (`result_zh_year_ui`) and, with the phrase of another column skipped, the one result of two; negative: a cell of a page table reads no result, a value is read before a result with the same phrases, two results are no one result | the unit test of part 4 |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `docs/code_test_coverage.md` | `calculator_general_value` without a unit test call | with unit test calls | the coverage report counts the new calls |
| `docs/llm/pending.md` | part 4 open | part 4 done, waiting for the run | |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|

### Lessons learned by llm (2026-11-25)

## 3. table step times

### Request

"see /docs/llm/coding.md and it look to me, that the execution time of '1148.7098 url write word url_to_action & next
url ... 1339.5144 timeout: ... so it can be deleted ... url to html: 106.4077 sec [calc_sheet: 106.2737 sec, ...]'
if very long. Can you add more time splitting to 'calc_sheet: 106.2737 sec' so that the reason could be better
detected?"

`calc_sheet` is the component type of the start page table; `view_exe` books the render time of each component
without its api wait and without the components within it under its type code id (`data_object::start_html_step`
and `end_html_step`), and the time report shows these steps in the bracket of `url to html`. The component calls
`ui_list::start_list`, which loads the start page data and creates the table with `table_with_related_columns`:
the numbers, the own columns, the results, the check of the results (the page calculates every stored result
again), the sources, the column and sort definitions, the table html and the charts. A nested html step is booked
under its own name and taken out of the outer one, so each part of the table gets its own time in the same bracket
and `calc_sheet` keeps only the time that no step covers. The api wait is already taken out of every step.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/web/helper/data_object.php` | `start_html_step`, `end_html_step` | + `html_step(string $name, callable $step): mixed`, which times one step with them and returns its result | one call per timed step instead of a start and an end call |
| `src/main/php/web/component/execute/ui_list.php` (`start_list`, `table_with_related_columns`, new private `html_step` and the step name consts) | the steps without an own time | each step through `html_step` with a name e.g. `table result check`, without a change of the result | the time of `calc_sheet` and of every other table component split into its steps |
| `src/test/php/unit_ui/base_ui_tests.php` | — | positive: a step returns the result of its function and is booked under its name and out of the component around it; negative: a step without a component around it is still booked | the tests first, next to the tests of the html steps |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the timeout lines of the test log | `[calc_sheet: 106 sec, ...]` | `[table result check: ..., table html: ..., calc_sheet: ..., ...]` | the steps are booked under their own names |
| the html snapshots | — | — | no change: the html is the same |
| `docs/code_test_coverage.md` | — | — | the new function and its test calls |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|

### Lessons learned by llm (2026-11-25)
