# result unit (2026-11-23)

Part of #247. Started before the first edit.

## Request

In the calculator of the Swiss vote on new nuclear power plants (`http/view.php?m=16&id=-4015`,
`src/main/resources/messages/use_cases/npp_ch_vote.json`) add a unit to 'Annual production'.

The row shows 12.14 without TWh, because `result::save_if_updated` removes every measure phrase from the result of a
formula with a division ("simplified version"), and `"annual production" = "capacity" * "capacity factor" * 8760 / 1000`
divides; the calc-validation of the file already expects `["annual production", "calculated", "TWh"]`. Besides, `TWh`
is declared without a type, so a cell would not show it as a unit (`ui_list::unit_name` takes a `measure_unit` only).

Decision of the developer of 2026-11-23: the formula names the unit of its result. A measure phrase on the left side of
an expression, e.g. `"annual production" "TWh" = ...`, is the unit of the result and survives the division rule, which
then only removes the measures of the operands.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/formula/formula.php`, `cfg/result/result.php` | `apply_result_phrases` removes the operand measures only for a percent result; the division rule in `save_if_updated` after the result phrases | `apply_result_phrases` removes the operand measures for a division, a percent result and a result phrase with a unit, before it adds the result phrases; the division rule in `save_if_updated` removed | a unit that the formula names stays the unit of its result |
| `src/main/resources/messages/use_cases/npp_ch_vote.json` | `TWh` without type; `"annual production" = ...` | `TWh` with `"type": "measure_unit"`; `"annual production" "TWh" = ...` | the result of the annual production carries TWh and the page shows it |
| `test/php/unit/formula_tests.php` | — | the unit tests of `apply_result_phrases` | the unit named by the formula is kept after a division, and negative: a division without a named unit removes the operand measures |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/use_cases/npp_ch_vote*.html`, the diff reports, the phrase data fixtures | — | — | the annual production row shows TWh, and the other TWh numbers (e.g. the electricity demand and the winter net import) now show their unit, because `TWh` is typed; written by the run |
| the result rows of `npp_ch_vote` in the database and `unit/result/list.csv` | — | — | the group of the annual production result gets `TWh`, so its group id changes; re-pin if a test pins it |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
