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

### Follow-up request of 2026-11-28

"try to find out what is consuming the most time here" for the start page of the global problems
(`view.php?z=0`, the step `wf4_edit_back` of the workflow test add_triple_wf4) with "url to html: 10.7072 sec [table
result check: 10.2908 sec, ...]" and "api_wait: 28.2466 sec [api list response: 8.5003 sec, db_map: 7.3696 sec,
db_read: 6.4867 sec, ...]", "and try to improve the speed or at least measure the time consuming part in more
details so that the cause can be found".

Found before the first edit: a step has no api wait (`data_object::end_html_step`), so the 10.3 seconds of
`check_results` are the calculation in the frontend: finding the missing operands of each result, merging the page
list, calculating each result and its expression with numbers. A result that the table alone can calculate was
calculated twice with the same operands, once to decide whether the page list is needed and once for its number.

### Follow-up request of 2026-11-28 (order)

"in a message like this '3251.6930 timeout: web/html/workflow_write/add_value_details_wf34/... section
not_specified: 0.0021 sec, url to action: 0 sec, url to html: 0.0981 sec [...], load page: 0.0002 sec, api_wait:
3.1894 sec [...], test diff: 0 sec, took 3.2898 seconds' it should start with the part that consumes the most time
with the details if available": `system_time_list::section_report` lists the categories in the order of their first
booking, while `report` of the whole run and the steps in the brackets are already sorted with the longest first.

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/main/php/web/formula/formula_calc.php` (`value_of`, new `candidates` and `name_index`), `src/main/php/shared/helper/ListOf.php` (new `changes`), `src/test/php/unit_ui/use_case_ui_tests.php` (the frontend calculation) | `value_of` compares every value and result of the list with each operand | only the values and results of the list that name one phrase of the operand, by an index per list by phrase name, which is built again after a change of the list | the run of 2026-11-28 after the split of the result check: "result check missing operands: 6.3759 sec, result check expression: 3.5476 sec, result check calculation: 3.4038 sec" of the start page; all three find each operand by `value_of`, which compared all numbers of the page list with two `array_diff` each; a number is an operand only if it names every phrase of the operand, so the smallest bucket of these phrases holds every candidate in the order of the list and the result is the same; the counter of `ListOf` is the only place where the entries of a list change (`set_lst`, `add_direct`, `unset`); `list_values` keeps its loop, because a kind of a phrase also matches; the test: a number added to the list after a calculation is an operand of the next one (positive) and a copy of the list made before the change keeps its operand (negative) |
| `src/main/php/cfg/system/system_time_list.php` (`section_report`), `src/test/php/unit/sys_log_tests.php` | the categories of a section in the order of their first booking | the longest first, each with its steps in brackets as before | the follow-up request of 2026-11-28 (order): a timeout line starts with the slow part e.g. "api_wait: 3.1894 sec [api list response: 3.1403 sec, ...]"; the test: the longest category is the first of the section (positive) and a short one is not (negative); the next run confirmed it, e.g. "section url to html: 13.9744 sec [...], api_wait: 5.5188 sec [...], not_specified: 0.0006 sec, ..." |
| `src/main/php/web/component/execute/ui_list.php` (`check_results`, `add_missing_operand_values`, new step consts) | a result that the table alone can calculate is calculated twice; the result check as one step | calculated once; the result check split into the nested steps "result check missing operands", "result check page list", "result check calculation" and "result check expression", and a debug entry with the number of results and of missing phrases | the follow-up request of 2026-11-28: the next timeout line names the part of the 10.3 seconds; the api wait of 28 seconds is booked by the backend parts already and is the loads of the missing operand values and results, which the line of the next run shows next to the new steps. The review of 2026-11-28 moved the search of the step "result check missing operands" from a closure with a reference into the new `phrases_to_load`, which returns the phrases and their contexts. The run of `test/test_part.php` with only the timing tests then gave for `wf4_edit_back_edit_save_cancel` and `wf6_show_edit_back` "url to html: 13.9 sec [result check missing operands: 6.4 sec, result check expression: 3.5 sec, result check calculation: 3.4 sec, ...]", so the three parts are about the same and together more than the 10.3 seconds of the result check before: the calculation once instead of twice gave no measurable gain, and the time is the search of the operands, which all three parts repeat, so the index of the next row is the fix to measure |

### Lessons learned by llm (2026-11-25)
