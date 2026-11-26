# fx import button (2026-11-24)

Part of #247, step 8 of the portfolio split page in `docs/llm/pending.md`.

## Request

"start step 8 of the portfolio split in pending.md"

The decision of the developer of 2026-11-24 in step 8: `portfolio_split_calculator_js.html` has an "Import" button,
which replaces "Import portfolio"; below it is the section "Exchange rates", which gets an "Import FX rates" button.
So the section "Inputs from" goes, on the generated pages and in the sample.

Both renderers already show the file import of the assumption list (`system_page` calls `ui_list::calculator_buttons`
with the group `calculator assumption`, `calculator_json.js` calls `buttons(group(KW.assumption))`), so the change
needs no code: a triple of the assumptions with `calculator file import` and its label, like the "Import" of the
inputs.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json`, the last triples | the 7 triples of the section "Inputs from" (`portfolio split imports` with its label, `portfolio split import portfolio` and `portfolio split import exchange rate` with their labels and order) | the triple `portfolio split assumptions import` (portfolio split assumptions, has a, calculator file import) and its label "Import FX rates" | the generated pages show "Import FX rates" in the section "Exchange rates" and no section "Inputs from" |
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_sample.html` | the section "Inputs from" with "Import portfolio" and "Import FX rates" | the button "Import FX rates" in the section "Exchange rates" below the rates | the sample shows the decision |
| `docs/llm/pending.md`, step 8 | the decision | done, waiting for the run | the plan |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `portfolio_split_calculator_js.html`, `portfolio_split_calculator_no_js.html` | — | — | the section "Inputs from" goes and the button "Import FX rates" shows in "Exchange rates"; the case reports stay, because the sample changes the same way |
| the phrase ids of the use cases imported after this file and the `list.csv` files and snapshots that show them | — | — | 5 triples less at the end of the file, so the later triples get an id 5 lower |
| the triples `portfolio file` and `exchange rate file` | — | — | no triple of the page names them any more; they stay, because removing them shifts the ids of all later triples of this file |
| `src/test/resources/api/phrase_data/phrase_data_portfolio_split.json` | — | — | the fixture of the pdf report test still holds the 7 triples; the pdf shows no import button, so its test should not change; if the run rewrites the fixture, that is the new phrase data |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|

## Lessons learned by llm (2026-11-24)
