# list kinds (2026-11-24)

Part of #247, the second of the three changes of part 5 of step 9 of the portfolio split page in `docs/llm/pending.md`
(the first is `docs/llm/log/2026-11-24_sum_over_results.md`).

## Request

"try to solve '## Different (6) - row: Realised / Stocks, cells: the sample shows "2'930'550 | 2'930'550 | 3'907'400 |
9'768'500" and the generated page "2'920'474 | 3'424'013 | 3'424'013 | 9'768'500" ...'" of
`portfolio_split_calculator_diff_differ.md`.

The run after the first change removed the exchange rate lines of every case report and left the six realised rows. A
realised row sums the walk rows of one kind of holding, e.g. the stocks, but a walk row names its holding (LISP) and
only the triple `LISP is a stock` names the kind. So a list function takes a number for a phrase of its operand or of
its context also if the number has a phrase that is a kind of it, over several levels of `is a` (LISP is a stock is a
holding). The third change adds the formulas to `portfolio_split_calculator.json`.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/shared/calc/phrase_kinds.php` (new), `src/main/php/web/formula/formula_calc.php`, `src/main/php/web/component/execute/ui_list.php`, `src/main/resources/script/calculator_json.js` | `list_values` and `listEntries` take a number only if it has every phrase of the operand and the context | `phrase_kinds::has_all` also accepts a phrase that is a kind of the wanted phrase; `formula_calc` gets the `is a` links of the page from `ui_list::kind_names` like the follower links; `listEntries` with `hasAllKinds` over the triples of the seed; tests in `unit/formula_calc_tests.php` and `unit_ui/use_case_ui_tests.php` | the page sums the walk rows of the stocks |
| `src/main/php/cfg/helper/data_object.php` | `is_list_number` takes a number with every phrase of the operand and the context | with the `is a` links of the triples of the file (`kind_names`); tests with a new file in `src/test/resources/import/unit_tests/` in `unit/import_tests.php` | the import checks a sum over the rows of a kind |
| `src/main/php/cfg/formula/formula.php` | `fill_list_element_group` loads the numbers with all phrases of the list | a phrase with `is a` children is left out of the load and checked by `phrase_kinds` with the children (`list_kinds`); tests in `unit/formula_tests.php` | the backend calculates the same number as the page |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the lists of the use cases | — | — | a list now also takes a number of a kind of a phrase of its context; the only checked list formula is in `CBAM_setup.json`, which is refused anyway (see `2026-11-24_sum_over_results.md`) |
| the backend calculation time of a list | — | — | one read of the `is a` children per phrase of a list, because the children decide which numbers are loaded |

## Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/test/php/unit_write/a_selected_test.php` | the html ui tests run with the request cache that the api has loaded from the database (`$ui_sys = $ui->dto` of "api based ui tests") | `ui_test_cache` and `init_unit_tests` before the ui tests, like `all_ui_tests::run` | the run of `test/test_part.php` of 2026-11-24 reported 13 errors of `component_ui_tests`, e.g. "the calculation formula of a component is shown with a link ... actual: formula: , expected: scale minute to sec": the tests expect the formulas, phrases, components and sample phrases of the unit test cache, which the database cache does not hold by these ids; `component_ui_tests` is switched on in the selection since e4988d819 (2026-11-09), but `test_part.php` has stopped before the ui tests since then (see the lesson of `2026-11-23_parent_target_share.md`), so this is the first run that reached them; not caused by the list change |
| `src/test/resources/web/html/object_pages/triple.html` | the committed snapshot without a title at the verb links | the next run of `test_part.php` reported the title version and wrote the committed version again, so no diff is left | the run before the fix of `a_selected_test` rendered the triple page with the database verbs, whose descriptions are the titles of "is part of" and "can be", and wrote them into the snapshot; the note of `2026-11-24_sum_over_results.md` about these titles names the cause wrongly: they came from this cache, not from an unrelated change |

## Lessons learned by llm (2026-11-24)

- `a_selected_test` repeats the sections of `all_tests`, so a test switched on in a section needs the same preparation
  as in its home runner (here `all_ui_tests::run` with `ui_test_cache`); when a test of another section is switched
  on, compare the setup lines of both runners before the run.
