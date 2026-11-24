# sum over results (2026-11-24)

Part of #247, part 5 of step 9 of the portfolio split page in `docs/llm/pending.md`.

## Request

"try to solve '... src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_differ.md ... row: Realised /
Stocks, cells: the sample shows "2'930'550 | 2'930'550 | 3'907'400 | 9'768'500" and the generated page "2'920'474 |
3'424'013 | 3'424'013 | 9'768'500" ...'"; asked whether to keep the realised rows modelled until the add child task,
the developer answered "Start it now".

The six realised rows (the value per kind of holding, the portfolio value, the realised weight and the deviation from the
target) are stored modelled values of the 35 / 35 split, so no input of the page changes them. To calculate them, a
formula sums over the rows of the walk, whose numbers are results. That needs three changes, each with a test run before
the next one:

1. this log: a list function (`sum`, `count`, `mean`) takes the results of its operand too, in every evaluator
2. a list function takes the rows of a kind e.g. the holdings that are a stock, because a row names its holding (LISP)
   and `LISP is a stock` names the kind
3. the formulas in `portfolio_split_calculator.json`: the value that a beneficiary receives of a holding per row, its sum
   per kind of holding, the realised value and the realised share instead of the modelled values

The rule of a list: all values with the operand and the context phrases, then each result whose phrases no value of the
list has, because a stored value wins over a result of the same phrases, the same as for one operand
(`element_group::add_figure`); never the result that is calculated, and never a second number of the same fact
(`words::LIST_FUNCTION_EXCLUDED`).

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/web/formula/formula_calc.php`, `src/main/resources/script/calculator_json.js` | `list_values` and `listEntries` take only the stored values; `calculated` uses the stored number of a list entry | the values and then the results whose phrases no value of the list has, without the result that is calculated; `calculated` takes the calculated number of a result in a list like of one operand (`operandNumber`); `listEntries` selects with `priorSelect` like `values_of`; tests in `unit_ui/use_case_ui_tests.php` | the page sums the walk results that the browser calculates again after an input change |
| `src/main/php/cfg/helper/data_object.php` | `expression_with_values` replaces every operand by one number, also in a list function | an operand in a list function by all values and checked results of the context with the same rule; tests with a new file in `src/test/resources/import/unit_tests/` in `unit/import_tests.php` | the import checks a sum over the results of the file, which the page shows |
| `src/main/php/cfg/formula/formula.php` | `fill_list_element_group` loads only the values of the phrase list | also the results of the phrase list (`result_list::load_by_phrase_list`) with the same rule | the backend calculates the same number as the page, which the double calculation check compares |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the use cases with `sum`, `count` or `mean` | — | — | a list that has a result of its operand next to the values now counts it; a read-only scan of the import files finds a checked list formula only in `CBAM_setup.json` |
| `src/test/resources/import/CBAM_setup.json` | its calc-validation of the specific embedded emissions reports the missing values | also "cannot be calculated" (`FORMULA_LIST_ARGUMENT`) | `sum ( "mass fraction" * "specific emissions of component" )` has a calculation inside the list, which the backend refuses too; the file has no value for any operand of the check, so it fails before and after |
| the backend calculation order | — | — | a sum over results needs the results first; if the batch calculates the sum before them, the sum misses rows and the double calculation check reports the difference |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|

The run of 2026-11-24 changed the case reports only by the exchange rate lines (`parent_target_share_2026-11-23.md`);
the other rewritten snapshots (`object_pages/triple.html`, `views_by_object/ref/ref_default_ref_29.html` and the wf17
workflow) show verb descriptions and a ref row, which no list function produces.
