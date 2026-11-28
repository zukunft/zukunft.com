# sample setup of the ABB share decision (2026-11-28)

The task "share_buy_sell_abb" of `docs/llm/pending.md` (section "Calculator editor").

## 1. the ABB share pages next to their sample with the input cases standard, high and low

### Request

"similar to pv_lifetime_balance_js.html and pv_lifetime_balance_sample.html integrate share_buy_sell_abb.html and
share_buy_sell_abb_sample.html into the test setup. This should include a 'high' and 'low' scenario like
portfolio_split_calculator_diff_high.md"

Asked: the names of the generated pages; answer: `share_buy_sell_abb_no_js.html` and `share_buy_sell_abb_js.html`
like the PV lifetime balance and the portfolio split; the hand made `share_buy_sell_abb.html`, which differs from the
sample only by one label and which no test writes, stays untouched for the developer to delete.

Checked before the first edit:
- the use case `share_buy_sell_abb.json` is imported by the db setup (`files::USE_CASE_FILES_APPENDED`) and shows
  the triple "ABB share decision" (decision of ABB share) with the view Calculator
- the labels of its calculator inputs and assumptions are the same texts as the labels of the sample (e.g. "Share
  price", "Fair price-earnings ratio", "Weight: analyst consensus"), so the cases find each field on both pages
  (`use_case_cases.js` `fieldOf` reads the first text of the label)
- the sample fills its sensitivity table by its script, so its diff gets the cases script with a case `unchanged`
  like the portfolio split
- the setup steps of `docs/deployment.md` "use case pages and their samples"

Planned:
- `test_files`: the page, sample, diff and cases names and the cases standard, high and low
- `triple_names`: `ABB_SHARE_DECISION`
- `system_views_read_tests`: `run_share_buy_sell_abb` (the pages without and with javascript),
  `run_share_buy_sell_abb_diff` and `run_share_buy_sell_abb_cases`, added to `run`; the cases test checks every
  field on both pages, that the high and the low case change a row of the generated page (positive) and that the
  standard case, which types the numbers of the sample, changes no row of the sample (negative)
- `src/test/js/share_buy_sell_abb_cases.js`: unchanged, standard with the numbers of the sample, high and low with
  every input and assumption changed within the limits of the sample
- `a_selected_test`: the three run functions as comments like the other use cases
- `docs/deployment.md`: the row of the table; `docs/llm/pending.md`: the task removed

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/test/php/const/files.php`, `src/test/php/const/triple_names.php` | — | the names of the ABB pages, reports and cases | the test setup |
| `src/test/php/unit_read/system_views_read_tests.php` | — | `run_share_buy_sell_abb`, `_diff`, `_cases` | the pages, their differences to the sample and the cases |
| `src/test/js/share_buy_sell_abb_cases.js` | — | the cases unchanged, standard, high and low | the numbers that the browser types into both pages |
| `src/test/php/unit_write/a_selected_test.php` | — | the three run functions as comments | to be switched on for a selected run |
| `docs/deployment.md`, `docs/llm/pending.md` | the row with "no test writes it yet"; the task | the row with the pages, the report and the cases; the task removed | the documentation |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/use_cases/share_buy_sell_abb_no_js.html`, `share_buy_sell_abb_js.html` | — | the new pages | written by the first run |
| `src/test/resources/web/html/use_cases/share_buy_sell_abb_diff.md`, `_diff_standard.md`, `_diff_high.md`, `_diff_low.md` | — | the new reports | the first run writes the baseline, which the developer accepts |
| the ids | — | unchanged | no import file changes, so no re-pin |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md` | — | the new functions | generated reports |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|

### Lessons learned by llm (2026-11-28)
