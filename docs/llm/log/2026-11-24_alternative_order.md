# alternative order (2026-11-24)

Part of #247, part 2 of step 9 of the portfolio split page in `docs/llm/pending.md`: the walk order selector.

This log is started before the first edit, so the tables below are the plan; "Unexpected changes" stays empty until
the developer has run the tests.

## Request

"see /docs/llm/coding.md and try to fix some of" the 35 differences of
`src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_high.md`, "do all in one change and create a
verb 'is alternative follower of' for a alternative / second order".

All 35 differences have one cause: the high case selects "Bank report order", but the walk formulas read `prior`
from the only follower chain of the holdings, which is in the order of the largest lot value, and the rows of the
walk are ranked by `lot value in CHF`; an option without a number (`phrase<id>`) is no number for
`calculator_json.js` `applyInput`, so the selector changes nothing. The sample walks the stocks first and the bonds as
the rows 31 to 35; the carried deviation and every count after the first moved row differ, and the realised rows
follow.

The developer decided on 2026-11-24: a second chain ("Second chain") in one change, with its own verb, so that every
reader of `is follower of` keeps the main chain. The bank report order differs from the lot value chain only at
five phrases: GIVN after LISN, GEBN after PGHN, ConfedBond30 after UBSG, BundDE30 after RocheBond28 and GBP cash
after TreasuryUS27; every other phrase has the same previous phrase in both orders, so the alternative chain is these
five steps and a phrase without an alternative step keeps its `is follower of` step. The option that walks the
alternative chain is marked by a calculator flag, because a rule like "the second option" would also hit the phrase
selectors of `pv_lifetime_balance.json`.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/verbs.json`, `src/main/php/shared/types/verbs.php` (consts, `CALCULATOR_VERBS`), `src/main/php/cfg/verb/verb_list.php` (dummy list), `src/test/php/create/test_pinned_ids.php`, `docs/llm/json_structure.md`; `src/main/resources/messages/calculator_keywords.json`, `src/main/php/shared/const/triples.php` (`CALCULATOR_ALTERNATIVE_ORDER`, `CALCULATOR_KEYWORDS`), `src/main/php/web/component/execute/system_page.php` (keyword and verb of the script), `src/test/php/unit/import_tests.php`, `docs/json_structure.md` | no verb and no keyword for a second order | the verb `is alternative follower of` (id 62, appended) and the flag `calculator alternative order`, which the phrase data sends to the page | a second order of the same phrases without a second previous phrase in the `is follower of` chain |
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` | one follower chain in the lot value order | the five alternative steps of the bank report order after the cash walk steps, and `walk order option bank statement` has a `calculator alternative order` | the bank report order is data |
| `src/main/resources/script/calculator_json.js` (`selectorField`, `applyInput`, new `walksAlternative` and `followTriples`, `previousInList`, `sorted`, `followerLinks`, `rankedRows`) | an option without a number changes nothing | choosing a flagged option walks the alternative steps for the rows and for `prior`, and the ranking of the table is skipped, because it orders the main chain; choosing another option of the same selector walks the main chain again | the high case walks in the bank report order like the sample |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_high.md` | 35 differences | 0 differences in the rows of the walk and the realised rows | the generated page walks in the order of the sample; a remaining difference would be the rounding of a tie of part 3 |
| the ids of the triples of `portfolio_split_calculator.json` after the cash walk steps and of the files imported after it, the `list.csv` files, the snapshots and the pinned ids | — | — | in `portfolio_split_calculator.json` the word `alternative`, the borrowed keyword triples `alternative order` and `calculator alternative order`, the five steps and the flag, so one word and eight triples more in the middle of the file; in `calculator_keywords.json` two words and two triples more at the end; the re-pin updates them |
| `src/test/resources/api/phrase_data/phrase_data_portfolio_split.json`, `portfolio_split_calculator_js.html`, `portfolio_split_calculator_no_js.html` | — | — | the phrase data and the seed of the page have the five alternative steps and the flag; the shown order of the stored selection (lot value) stays |
| `src/test/resources/api/type_lists/type_lists.json`, `src/test/resources/api/ui_config/ui_config.json`, `src/test/resources/unit/verb/list_*.csv` | — | — | the new verb |
| the backend (`element_group::prior_names`), the import check (`data_object::prior_names`), the php page (`calculator.php`, `ui_list::prior_names`, `ui_list::ranked_rows`), `formula_editor.js` and `table_editor.js` | — | — | unchanged: they read only `is follower of`, so they walk the main chain, which is the order that the stored example uses; the gap for a page that `uses` the alternative option is recorded in `docs/llm/pending_prio_2.md` |
| the test run | — | — | a new verb needs the db setup, so `test/test.php` with re-pin instead of `test/test_part.php` |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/test/resources/web/html/views_by_id/word/47_word_add_1.html`, `src/test/resources/web/html/views_by_id/triple/50_triple_add_1.html` | the verb selector without the new verb | `<option value="62">is alternative follower of</option>` in the verb selector | the add views list every verb; the test run reported "ERROR add word view" and "ERROR add triple view" and updated both snapshots, so nothing to fix in the code |
| `src/test/php/const/triple_names.php` (`SHARE_RATIO_ID`, `SHARE_SYMBOL_ID`) | 3787, 3839 | 3789, 3841 | the two triples of `calculator_keywords.json` shift the later triples by 2, as the side effects table expects; the run reported "the pinned triple ids match" with these two, and `docs/pinned_id_findings.md` names the same new ids, so the consts are set as the re-pin would set them |

The test run of 2026-11-25 shows `portfolio_split_calculator_diff_high.md` with 0 differences, so the high case walks
in the bank report order like the sample.

## Lessons learned by llm (2026-11-24)

- A new verb is not only in the verb fixtures (`type_lists.json`, `ui_config.json`, `list_*.csv`): every view with a
  verb selector lists it, so the html snapshots of the add and edit views of a word and a triple change too; the
  side effects table of a new verb should name them.
