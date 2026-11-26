# target share spread (2026-11-26)

Part of #247, step 3, change 2 of 5 (`docs/llm/pending.md`): the difference of the target shares by the instances of
`child portfolio`. The plan was started before the first edit, so its tables are the plan; "Unexpected changes"
stays empty until the developer has run the tests.

## Request

"see /docs/llm/coding.md and try to fix 'Missing in the generated page (5) - field: Target weight third child -
warning: Weights differ, so every child is rounded on its own deviation. - table: CHF | Parent | First child | Second
child | Third child | Total - table: # | Position | ... | Third | Parent | ... - button: (3)' in
src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_add_child.md". Change 1 is confirmed by the run
of 2026-11-26 (the reports `_high.md`, `_low.md`, `_over.md` and `_differ.md` are back at 0 differences after
`test/reset_db.php`), so the next change of the full step 3 is change 2. The five missing items need the buttons
(change 5) and the results of a new instance (change 4), which need the results by the instances of change 2 and 3.

The plan of change 2 was a result per child, its deviation from the target share of the first child, and their sum.
A result of the second child cannot read the number of the first child, because a plain operand takes only a number
whose phrases are all in the context (`data_object::number_by_names`, `formula_calc::value_of`), and the target share
of the first child has no phrase of the second child. Decision of the developer (2026-11-26): two new list functions
instead, `largest` and `smallest`, in all evaluators; `min` and `max` keep their form with arguments, because the
walk formulas use them with calculated arguments, e.g. `max( round( ... ) * "lot size" ; 0 )`, which a list function
refuses (`arithmetic::list_args_are_plain`).

Like the target share of the children (change 1), a list and the plain operand `mirror` cannot share one context: a
list takes only the numbers with every phrase of the context, and the target share of a child has no `mirror`. So the
spread is a result of its own, `"child","target share difference" = largest( "child portfolio" ) - smallest( "child
portfolio" )` in the context `example portfolio split, target share, percent`, and `target share difference formula`
takes it as a plain operand: `"target share difference" = "mirror" * "child","target share difference"`.

A list also takes a number that has its operand phrase itself (`phrase_kinds::has_all`), so the sum of change 1,
`"child portfolio","target share"` = 0.7, would be one of the target shares of `largest( "child portfolio" )` and the
spread would be 0.35 instead of 0. So the results over the children are named by the word `child`, which no number
had before and which is no kind of `child portfolio`: `"child","target share"` (change 1, also in the operands of
the excess and of the parent) and `"child","target share difference"`. No new phrase and no re-pin; the walk formulas
keep their operand `"target share difference"`, whose context has no `child`, so they select the number of the
difference and never the spread, and the warning takes the number with the fewest phrases (`cellEntry`), which is the
difference.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/shared/const/chars.php`, `src/main/php/shared/calc/arithmetic.php` (`list_number`), `src/main/php/web/formula/formula_calc.php` (`TOKEN_PATTERN`), `src/main/resources/script/calculator.js`, `calculator_json.js` and `formula_editor.js` (`LIST_NUMBER` and the patterns of the function names), with the positive and negative cases in `src/test/php/unit/math_tests.php` and `src/test/php/utils/math_function_report.php` | the list functions `sum`, `count`, `mean` | also `largest` and `smallest`: the largest and the smallest number of a list | the spread of the target shares of any number of children |
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` (the formula `target share difference formula`, a new formula `target share spread of the children` and their calc-validations; the result of `target share of the children` and the operands of `target share excess formula` and `target share of the parent`) | `"mirror" * ( max( "first child portfolio" ; "second child portfolio" ) - min( "first child portfolio" ; "second child portfolio" ) )` in the context with both children; `"child portfolio","target share"` | `"child","target share difference" = largest( "child portfolio" ) - smallest( "child portfolio" )` (0 in the context `example portfolio split, target share, percent`) and `"mirror" * "child","target share difference"` (0 in the context `example portfolio split, child, mirror, assumed, target share difference, percent`); `"child","target share"` | a third child is part of the difference; the sum of change 1 is no number of a list over the child portfolios |
| `docs/llm/pending.md`, `docs/json_structure.md`, `docs/llm/json_values.md` | change 2 open; the list functions `sum`, `count`, `mean` | change 2 done, waiting for the run; also `largest` and `smallest`, and that a number with the operand phrase itself is in the list | the task list and the description of the list functions |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `docs/math.md` | the list functions `sum`, `count`, `mean` | also `largest` and `smallest` | the generated report of the math functions (`php8.4 bin/math_functions.php`), checked by the test run |
| `src/test/resources/api/phrase_data/phrase_data_portfolio_split.json`, `portfolio_split_calculator_js.html`, `portfolio_split_calculator_no_js.html`, `src/test/resources/unit/formula/list.csv`, `src/test/resources/unit/result/list.csv` | — | — | the expression of the difference, the new formula and its result; the numbers stay 0 |
| the case reports of `portfolio_split_calculator` | as before | as before | the numbers stay; the reports of `add_child` and `remove_child` change only with change 4 and 5 |
| the ids | — | — | a formula is added, but no word or triple, so no re-pin; the changed context of the stored result of the difference needs `test/reset_db.php` before `test/test_part.php` (change 1) |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|

## Lessons learned by llm (2026-11-26)

