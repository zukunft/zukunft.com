# realised formulas (2026-11-24)

Part of #247, the third of the three changes of part 5 of step 9 of the portfolio split page in `docs/llm/pending.md`
(the first two are `docs/llm/log/sum_over_results_2026-11-24.md` and `docs/llm/log/list_kinds_2026-11-24.md`).

## Request

"the tests passed, mark step 2 done and start step 3"

The six realised rows of `portfolio_split_calculator_diff_differ.md` are stored modelled values of the 35 / 35 split.
Now that a list function takes results and the numbers of a kind, formulas calculate them from the walk, so the page
calculates them again after an input change.

The cells of the result table select by the page phrase, the row phrase and the column phrase (`resultCell`), so each
realised number names `example portfolio split`; a sum takes only the numbers with all phrases of its context, so the
value per row that the sums add names it too, although the walk rows do not. A read-only check of the 126 stored
counts times the unit values gives exactly the 16 modelled numbers per kind and in total, so the stored numbers do not
change.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json`, triples and formulas | — | the triple `realised holding value` (holding value, kind of, realised) after `realised value`; the formulas `realised holding value` (`"realised holding value" = "split count" * "unit value"`), `realised value of a beneficiary` and `realised value of the portfolio` (`sum( "realised holding value" )`, the second with `"original"` on the left side) and `realised share of a beneficiary` (`"realised share","percent" = "realised value" / "portfolio value"`) at the end of the formulas | the realised value of a beneficiary per kind of holding and in total, of the whole portfolio and the realised share follow the walk |
| the same file, values and calc-validation | the 20 values with `modelled`; the 3 calc-validations of the split deviation with `modelled` in the context before the walk | the values removed; at the end of the calc-validation the 126 realised holding values (each holding and beneficiary), the 16 sums, the 3 realised shares and the 4 split deviations without `modelled`, in this order, because a check takes a result as an operand only after it is checked | the import checks every new number, which the page shows and calculates again |
| the same file, the description of the use case | "The totals per kind of holding, the realised value and the realised share are stored as modelled values, because a formula cannot sum over the rows of the walk yet" and the items (16) and (17) | the formulas that sum the walk rows of a kind | the description says what the file does |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the phrase ids after the new triple and the formula ids of the use cases imported later (`ahv_vat_vote.json`, `share_buy_sell_abb.json`, `table_editor_sample.json`) | — | — | one triple more in this file and four formulas more; the use case rows are selected by name, so only the snapshots and the `list.csv` files with the ids change |
| `portfolio_split_calculator_*.html`, `portfolio_split_calculator_diff_*.md` | — | — | the realised rows follow the walk of each case, so the six lines of the differ report and the realised lines of the high, low and over reports change; the notes of the page list the four formulas |
| the word `modelled` | — | — | no value of the file names it any more; the word stays, because removing it shifts the ids of the later words |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` | the formula `realised holding value` and its 126 calc-validations | the formula `realised holding value formula`, like `split deviation formula` of the same file | the run of 2026-11-24 stopped the import with "the formula name "realised holding value" is also used as a triple name, which is not allowed because both are terms": I gave the formula the name of the new triple, which is the phrase of its result; a read-only check of all formula names of the file against its words and triples finds no other clash |

## Lessons learned by llm (2026-11-24)

- A formula is a term like a word and a triple, so its name is never the name of a phrase, least of all the phrase of
  its own result; before a new formula is added, check its name against the phrases of the file (the file names such
  formulas with "formula" at the end).
