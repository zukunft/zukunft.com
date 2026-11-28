# portfolio csv, child cases and the instances of the children (2026-11-25)

Seven changes of 2026-11-25 and 2026-11-26 of the portfolio split page in `docs/llm/pending.md`, each with its own
plan: the import json from the csv of a bank statement ("import"), the input cases that add a child and remove one
(step 2), the target shares by the instances of `child portfolio` (step 3, change 1 of 5), the walk counts by the
instances (step 3, change 3 of 5), the list rule that the count of the parent of change 3 needs, the button "Add
child" in the browser (step 3, change 4 of 5) and the button "Remove the ..." in the browser (step 3, change 5 of
6); change 2 of step 3 has its own log
(`docs/llm/log/2026-11-26_target_share_spread.md`). The changes of 2026-11-27 follow in the same file: the save of an
added and a removed child (section 8, change 6a), the results of a saved child (section 9, change 6b), the first page
of the pdf report (section 10) and the exchange rates of the page (sections 11 to 13, the task "range of FX rates",
of which sections 10 to 13 had a log file of their own before). Each plan was started before the first edit of
its change, so its tables are the plan; its "Unexpected changes" stay empty until the developer has run the tests.

## 1. portfolio csv

Part of #247, "import": a zukunft.com import json from the csv of a bank statement of the positions.

### Request

"see /docs/llm/coding.md and do the next open task of '## Calculator editor ### portfolio split page compared with
its sample' in pending.md and remove the task already done". Part 4 of step 9 is done (commit 0169999eb), part 1
needs a calculation job of the developer, so the next task is "import": "based on a csv and a list of context
phrases e.g. 'example portfolio split' a zukunft import json should be created ... so create the csv file and as a
unit test result create or update a zukunft.com json that can be imported; create a similar csv for the existing
portfolio split test data that creates a new json with a different name that can be compared with the existing
json". The csv of the request is generated and has no connection to any real life case.

Decisions of the developer (2026-11-25):
- the word of a holding is the instrument number of the csv, the ISIN of a security or the account number of a
  cash position, and the instrument name is its description and its `calculator hint`, because a name can repeat
  (two `Oak Lu Fd Gl RhC` with two ISINs)
- the csv has no lot size, so each holding gets an `assumed` lot size by its kind: 1 share for a stock, 1000 of the
  face value for a bond and 0.01 for cash
- the json has only the holdings, the calculator page stays in `portfolio_split_calculator.json`

Decisions of the LLM:
- the asset class of the csv gives the kind: `Equities and similar positions` and `Alternative investments` a
  stock, `Bonds and similar positions` a bond, `Cash and short-term investments` cash; a position of another asset
  class is left out and reported with `msg_id::IMPORT_UNKNOWN_ELEMENT`
- a bond position whose market value is the quantity times the price and not the face value times the price in
  percent, e.g. the bond fund `LU0854923223` with 934.784 units of 150.269 CHF, is a stock (units with a price),
  because its value is not a percent of a face value
- the values are written like the holdings of `portfolio_split_calculator.json`: the quantity as `shares,
  original`, `face value, <currency>, original` or `<currency>, original`, the price as `stock price, <currency>` or
  `bond price, percent`, the lot size with `assumed`, the market value of the bank as `holding value, CHF, expected`
  (the check of the calculated value) and per foreign currency the `exchange rate, CHF per <currency>` of the csv;
  the source of every value is the given source name
- the holdings follow each other with `is follower of` from the largest lot value (unit value in CHF times the lot
  size) to the smallest, like the walk order of the page; the bank order of the csv as a second order is left out,
  because an alternative chain cannot say that the first holding of the bank order has no previous holding
- each holding `is part of` the first context phrase, and the context phrases are words of the file
- every base word that a value uses is declared name-only and every borrowed triple verbatim like in
  `portfolio_split_calculator.json` (`docs/llm/json_structure.md` *Self-consistency*), because the import resolves a
  phrase only from its own file
- the csv of the existing test data has the ticker as the instrument (e.g. `LISP`), so its json uses the word names of
  `portfolio_split_calculator.json` and can be compared with it; it differs at the lot size of the three CHF bonds
  (5000 there, the assumed 1000 here)

Check of the LLM before the run (the converter in a scratch script with `php8.4`, read only): the sample csv has 69
positions, ABB `CH0012221716` is a stock of 6000 shares at 61.74 CHF, `US742718FY05` a bond, the bond fund
`LU0854923223` a stock, `2120.814.01` cash in EUR at 0.9323, the json has 68 walk steps and no message, and a
position of the asset class `Derivatives` is left out with `Unknown element "Derivatives"`. The json of the csv of
the existing test data has the same number for 90 of its 125 values as `portfolio_split_calculator.json`; the other
35 are the lot sizes, which are `assumed` here (and 1000 instead of 5000 for the three CHF bonds), and 38 of its 41
walk steps are the same, the 3 others follow from the lot size of the bonds. Two more known differences: a cash
holding of the existing data e.g. `GBP cash` is a triple there and a word here, and the cash position `N987272-` of
the sample has a market value of the bank (270571.28 CHF) that is not its amount times the exchange rate
(320000 EUR * 0.9323), so its `holding value, CHF, expected` will differ from the calculation.

#### Follow-up request of 2026-11-26

"fix findings 3 and 4 in convert_portfolio_csv.php" of the review: `holding` hid a bad cell (an empty or zero
exchange rate became 1, a cell that is not a number became 0, a short line was filled with empty cells), and the
message of an unknown asset class named only the asset class. Decisions of the LLM: a position is checked before it
becomes a holding and is left out with one message per reason that names the csv (by the source name), the line, the
position and the cell; a line with another number of cells than the header row is left out, because a comma in a
name without quotes shifts every later cell; a csv without the header row is reported, because it gave an empty json
without a message; an empty price currency stays the currency of the position and an empty instrument name an empty
description. The mismatch of the market value of `N987272-` is not reported here, because the page checks the
calculated holding value against `holding value, CHF, expected`.

"fix finding 5 in convert_portfolio_csv.php": the borrowed triples were copies with literal json keys, verbs, ref
types and `measure_unit`, so a change of the home file would not reach the json. Decision of the developer
(2026-11-26): read them from the home file `portfolio_split_calculator.json` (`files::PORTFOLIO_SPLIT_CALCULATOR_FILE`)
instead of a constants-plus-drift-test variant; only their from, verb and to stay as consts. Found by the check of the
LLM: the currency pairs were the triples of the exchange rates (`CHF per 0.9323`) and not of the currencies, because
`triples` read the values of `foreign_currencies` instead of its keys, so the json declared no `CHF per EUR` although
its values use it.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/cfg/import/convert_portfolio_csv.php` (new) | — | `positions` (the rows below the header row of the csv as named cells), `holding` (the kind, the quantity, the price, the currencies, the exchange rate and the market value of one position) and `convert` (the import json of the holdings for the context phrases and the source) | a zukunft.com import json from the csv of a bank statement |
| `src/test/resources/import/portfolio_csv/bank_positions_sample.csv`, `bank_positions_portfolio_split.csv` (new), `src/test/php/const/files.php` | — | the csv of the request and the csv of the 42 holdings of the existing test data in the bank order, with their consts and the consts of the two json files | the inputs of the unit test |
| `src/test/php/unit/import_tests.php` | — | positive: a header row and its rows, a stock, a bond in percent, a bond fund in units and a cash position, the values of a stock and the exchange rate of a currency, one follower per holding except the first; negative: a csv without a header row has no position, a position of an unknown asset class is left out and reported; the two json files as `assert_file` results; both json files are read by `import::get_data_object` without the database, without a message and with all their values, and negative: the json without the triple `stock price` is not read completely without a message | the tests first; the run writes the two json files |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/import/portfolio_csv/bank_positions_sample.json`, `bank_positions_portfolio_split.json` | — | written by the run with `AUTO_UPDATE_TEST_FILES` | the expected result of the unit test is a generated file |
| `docs/code_test_coverage.md`, `docs/code_functions_all.md`, `docs/code_object_name_exceptions.md` | — | — | a new class with its tests |
| `docs/llm/pending.md` | "import" open | done, waiting for the run | |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/test/php/unit/import_tests.php` | `new import()->get_data_object(...)` | one `import` with `$csv_imp->usr = $t->usr1` for the three reads | the run stopped with "data_object::__construct(): Argument #1 ($usr) must be of type user, null given": `get_data_object` creates the data object for the user of the import, which `put_json` sets from the message and a direct call has to set itself |
| `src/test/php/unit/import_tests.php` | negative: the json without the triple `stock price` gives a message or fewer values | positive: `stock price` is a triple of the data object; negative: without its declaration it is no triple | the run failed with "... but a json without the triple "stock price" is not read completely without a message": `phrase_list::import_mapper` adds a phrase that the file does not declare as a name, without a message and without dropping the value, so the declaration decides whether the phrase is the triple and not whether the value is read |
| `src/test/php/unit/import_tests.php` | the count of the values with the default timeout | with `TIMEOUT_LIMIT_IMPORT` | "timeout: ... and with all its values ... import: 0.3469 sec": the read of the json is charged to the next assert |
| `src/test/resources/import/portfolio_csv/bank_positions_sample.json`, `bank_positions_portfolio_split.json` | — | written by the first run | as planned; the first run reports "file ... does not exist" once as an internal error, because the expected file is new |
| `src/main/php/cfg/import/convert_portfolio_csv.php` | `positions($csv)` as a list; `holding` with the exchange rate 1 for an empty or zero cell; `convert` reports `IMPORT_UNKNOWN_ELEMENT` with the asset class | `positions($csv, $source, $msg)` by the line number, reports a csv without the header row and leaves out a line with another number of cells; `position_is_valid` (`COLS_REQUIRED`, `COLS_NUMBER`) leaves out a position of an asset class without a kind, with an empty cell, a cell that is not a number or an exchange rate not above zero; `cells_by_column` | follow-up of 2026-11-26: review findings 3 and 4, a bad cell was hidden and the message did not name the position |
| `src/main/php/shared/enum/messages.php`, `src/main/resources/translations/en.yaml`, `de.yaml` | — | `IMPORT_CSV_NO_HEADER`, `IMPORT_CSV_LINE_CELLS`, `IMPORT_CSV_UNKNOWN_KIND`, `IMPORT_CSV_CELL_EMPTY`, `IMPORT_CSV_CELL_NOT_NUMBER`, `IMPORT_CSV_RATE_NOT_POSITIVE` | each names the csv, the line and the position or the cell, and the solution |
| `src/test/php/unit/import_tests.php` | the positions as a list; the unknown asset class checked by "Derivatives" | the positions by their line; the message of a csv without the header row; the five bad lines in a loop, each left out and named in the message | the tests of the follow-up; checked before the run with a read-only scratch script (`php8.4`, no database): both json files stay the same and each bad line gives only its message |
| `src/main/php/cfg/import/convert_portfolio_csv.php` | `BASE_TRIPLES` and `CURRENCY_PAIR_DESCRIPTIONS` as copies with literal keys, verbs, ref types and `CURRENCY_PAIR_TYPE = 'measure_unit'`; `triples` loops over the rates of `foreign_currencies` | `BASE_TRIPLES` as the from, verb and to of each borrowed triple (`words`, `triples`, `verbs` and the `KIND_*` consts), copied verbatim by `home_triples`, `borrowed_triples` and `home_triple` from `files::PORTFOLIO_SPLIT_CALCULATOR_FILE`, an unreadable file or a missing triple reported with `log_err_msg`; `currency_pair` from the home file, else the general description with `phrase_types::MEASURE`; `triples` loops over the currencies | follow-up of 2026-11-26: review finding 5 and the currency pair bug found by its check |
| `src/test/php/unit/import_tests.php` | six messages, `assert_true(... != null)`, nested keys, counts and calls in the assert lines, `$by_instrument` without a guard | one `$csv_msg` reset after each assert that reads it (also asserting the positions and the conversion of the portfolio split csv, whose messages were not read), `assert_contains` / `assert_contains_not` on the triple names, every compared value in a named variable, `$by_instrument` empty for a missing instrument, `$test_name` first | follow-up of 2026-11-26: the minor points of the review in the test code |
| `src/test/php/unit/import_tests.php` | — | `CSV_PAIR_EUR_COM`, `CSV_PAIR_NOK_COM`: the unit of the EUR rate verbatim from the home file, and negative: the general description of a NOK pair that the home file does not have; `$csv_head`, `$csv_tail` moved up for it | the tests of the follow-up |
| `src/test/resources/import/portfolio_csv/bank_positions_sample.json`, `bank_positions_portfolio_split.json` | the currency pairs `CHF per 0.9323` etc. | `CHF per EUR` etc. verbatim from the home file (3 and 6 triples); the words, the values and the other triples stay the same (read-only scratch check) | expected: the next run reports the two `assert_file` tests as failed, and the developer regenerates the files |

### Lessons learned by llm (2026-11-25)

- A test that calls `import::get_data_object` directly sets `import::usr` first, like the other import tests
  (`$imp->usr = $t->usr_dev`), because only `put_json` takes the user from the message.
- The import of a file never fails on a phrase that the file does not declare: it adds the name as a phrase
  without a message, so a test of the self-consistency checks the type of the phrase, e.g. that a borrowed name is
  a triple, and not a message.

## 2. child cases

Part of #247, step 2: the input cases that add a child and remove one.

### Request

"see /docs/llm/coding.md and do the next open task of '## Calculator editor ### portfolio split page compared with
its sample' in pending.md and remove the task already done". The import of the bank csv waits for its run, and the
developer has added "TODO Prio 1 should be based on phrases without specific code" to `convert_portfolio_csv.php`,
which is a task of its own; part 1 of step 9 needs a calculation job of the developer, so the next task is step 2:
"Add an add two children and remove one child test case similar to the high and low test cases so that all main test
cases are covered", with the case files for "the three children case" and for "remove second child so that child 1
and 3 are remaining" (step 3).

`use_case_cases.js` types the numbers of a case into the fields of the sample and of the generated page and compares
what both show. The sample adds a child with the button "Add child" (the new child gets the weight of the first child
if the parent can give it, so 30 % next to 35 % and 35 %) and removes one with the button "Remove the second child";
after the remove the children are renumbered, so the former third child is the second one. The generated page has the
same buttons ("Add child" as text, "Remove the …" as title and aria label), but without a function until step 3, so
the report of each new case lists what the sample shows after the press, which is the prompt for step 3.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/test/js/use_case_cases.js` | a case only types numbers | a case can press buttons first: the list under the key `press` names each button by its text, its title or its aria label; a button that the page does not have is listed as missing like a field | the cases that add or remove a child |
| `src/test/js/portfolio_split_calculator_cases.js`, `src/test/php/const/files.php` (`PORTFOLIO_CASES`) | standard, low, high, over and differ | + `add_child` (press "Add child": three children) and `remove_child` (press "Add child" and "Remove the second child": the first and the former third child) | all main test cases of the children |
| `docs/llm/testing.md` | — | the key `press` of a case | the next case with a button |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_add_child.md`, `portfolio_split_calculator_diff_remove_child.md` (new) | — | the differences of the two cases, written by the run | the generated page has no function of the buttons yet (step 3) |
| the other case reports | 0 differences | 0 differences | their cases have no `press` |
| `docs/llm/pending.md` | step 2 open | done, waiting for the run; the TODO of the csv import as a task | |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|

### Lessons learned by llm (2026-11-25)

## 3. target share instances

Part of #247, step 3, change 1 of 5: the target shares by the instances of `child portfolio`.

### Request

"see /docs/llm/coding.md and try to fix some of 'Missing in the generated page (5) - field: Target weight third child
- warning: Weights differ, so every child is rounded on its own deviation. - table: CHF | Parent | First child |
Second child | Third child | Total - table: # | Position | ... | Third | Parent | ... - button: (3)' in
src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_add_child.md". Decision of the developer
(2026-11-25): the full step 3, split into five changes (`docs/llm/pending.md` step 3).

Six formulas of `portfolio_split_calculator.json` name the two children, so a third child is in none of them. The
list functions take the rows of a kind (part 5 of step 9, e.g. `sum( "realised holding value" )` per holding kind),
so `sum( "child portfolio" )` in the context `example portfolio split, target share, percent` is the sum of the
target shares of every child portfolio: `first child portfolio` and `second child portfolio` are a `child portfolio`,
the parent portfolio is not. The context of the result must not name one child or `original`, because a list takes
only the numbers with all phrases of the context; `original` is the operand of the target share of the whole
portfolio (`example portfolio split, target share, original, percent` = 1), which the operand selects without it
being in the context.

This change takes the two formulas that are a sum: `target share excess formula` and `target share of the parent`.
The numbers stay the same (-0.3 and 0.3 with 35 % and 35 %), so the case reports stay as they are; with a third child
these two results include it. `target share difference formula` (max minus min) needs a result per child (change 2),
and the walk counts need the result `rounded count` (change 3).

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` (the formulas `target share excess formula` and `target share of the parent` and their two calc-validations) | `"first child portfolio" + "second child portfolio" - "original"` and `"original" - "first child portfolio" - "second child portfolio"`, the context with both children and `original` | `sum( "child portfolio" ) - "original"` and `"original" - sum( "child portfolio" )`, the context `example portfolio split`, `target share`, `percent` | a third child is part of the excess and of the share of the parent |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/api/phrase_data/phrase_data_portfolio_split.json`, `portfolio_split_calculator_js.html`, `portfolio_split_calculator_no_js.html`, `src/test/resources/unit/formula/list.csv`, `src/test/resources/unit/result/list.csv` | — | — | the expressions of the two formulas and the context of their two results; the numbers stay |
| the case reports of `portfolio_split_calculator` | as before | as before | the numbers stay; the reports of `add_child` and `remove_child` change only with change 4 and 5 |
| the ids | — | — | no word, triple or formula is added, so no re-pin |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` | `sum( "child portfolio" ) - "original"` and `"original" - sum( "child portfolio" )` in the context `example portfolio split, target share, percent` | the new formula `target share of the children` (`"child portfolio","target share" = sum( "child portfolio" )`, appended at the end of the formulas, its calc-validation 0.7 before the two others) and the two formulas with the plain operand `"child portfolio","target share"` in the context `example portfolio split, child portfolio, original, target share, percent` | the import of the run failed with "the value for "original" to validate the result of ... "parent portfolio","target share" ... is missing in the import message" and the follow-ups at the target value (19610900 instead of 5883270) and the split deviation of the parent: a plain operand takes only a number whose phrases are all in the context (`data_object::number_by_names`, the same rule as `formula_calc::value_of`), and a list only the numbers with every phrase of the context, so a list and `original` cannot share one context; a formula is added, but no phrase, so the ids of the words and triples stay |

| `src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_high.md`, `_low.md`, `_over.md` | 0 differences | 4 differences each: the target weight and the target value of the parent stay at the stored 30 %, and the warning of the excess is missing in the case over | the run without a database recreate: the page seed still has the old context of the stored results `target share of the parent` and `target share excess` (`example portfolio split, first child portfolio, ..., original`), because the re-import of `portfolio_split_calculator.json` has not taken over the new context of their calc-validations, so the page finds no number for `"child portfolio","target share"` in the old context and keeps the stored number; to be checked after `test/reset_db.php` |

### Lessons learned by llm (2026-11-25)

- A re-import of a use case without a database recreate keeps the context (the source group) of a stored result,
  so a changed context of a calc-validation needs `test/reset_db.php` before `test_part.php`; whether the import
  should update the context is open.
- A formula with a list and a plain operand of another phrase group needs two formulas: the context of a plain
  operand must hold all phrases of its number, the context of a list must be in every number of the list.

## 4. walk count by the instances

Part of #247, step 3, change 3 of 5: the counts of the walk by the instances of `child portfolio`.

### Request

"do the next open task of the portfolio split page", with the "Missing in the generated page (5)" of
`portfolio_split_calculator_diff_add_child.md` selected. These five items need the buttons (change 5) and the
results of a new instance (change 4), which need the formulas by the instances; change 1 and 2 (the target share sum
and spread) are done, so the next one is change 3: the walk counts.

Decisions of the developer (2026-11-26):
1. the mirror branch (`"mirror" = 1 & "target share difference" = 0` gives the count of the first child) is dropped
   from the walk: it applies only to equal target shares, and then every child already has the same count, because
   each child calculates its count with the same operations on the same numbers; the checkbox keeps its effect on
   `target share difference` and on the warning "Weights differ ..."
2. change 3 is one change with one run, not two

The six formulas that named both children are now four formulas by the instances plus three results over the
children. A list and a plain operand cannot share one context (`docs/llm/log/2026-11-26_target_share_spread.md`), so
each sum or count over the children is a result of its own, named by the word `child`:
- `walk rounded count of a child`: `"rounded count" = min( max( round( "target quantity" / "lot size" ) * "lot size"
  ; 0 ) ; "original" )`, the rounded count of each child ("rounded count of a child in the portfolio split walk" of
  `docs/llm/pending_prio_2.md`)
- `walk rounded count of the children`: `"child","rounded count" = sum( "child portfolio" )` per row
- `number of the children`: `"child","count" = count( "child portfolio" )` over the target shares, 2 for the sample
- `walk count of a child` replaces the formulas of the first and the second child: `"split count" = if( max(
  "child","rounded count" - "original" - "lot size" / 1000000 ; 0 ) = 0 ; "rounded count" ; min( "rounded count" ;
  floor( "original" / "child","count" / "lot size" ) * "lot size" ) )`
- `walk count of the children`: `"child","split count" = sum( "child portfolio" )` per row
- `walk count of the parent`: `"split count" = "original" - "child","split count"`

Checked before the first edit with a read-only scratch script: in all 42 rows the rounded count of each child is its
stored split count and no row reaches the cap, so the stored counts, the counts of the parent and every later number
stay the same. The plain operand `"rounded count"` selects the number of the child and not the sum, because the sum
has the phrase `child`, which the result does not name (`formula_calc::value_of`, the fewest foreign phrases).

Found by the same scratch script while planning, before the run: the sum `"child","split count"` also takes the
expected allocations of the split agreement for LISP and LISN, which needs the list rule of section 5; and the lot
size of a cash row is an `assumed` value, so the contexts of the rounded count and of the count of a cash row name
`assumed`, which the old contexts had by the mirror.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` (words, triples, formulas) | the formulas `walk count of the first child`, `walk count of the second child` with both children and the mirror; `walk count of the parent` with both children | the word `rounded` and the triple `rounded count` at the end of their blocks; `walk rounded count of a child` and `walk count of a child` in place of the two child formulas; `walk count of the parent` by `"child","split count"`; `walk rounded count of the children`, `number of the children` and `walk count of the children` at the end of the formulas | the counts of any number of children; the new objects at the end, so that no formula of the file gets another id |
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` (calc-validation) | per row the counts of the first and the second child (84) with the context of the mirror; per row the count of the parent with both children (42) | per row the rounded count of each child, their sum, the count of each child by `walk count of a child` and the sum of the counts (252); the count of the parent with `child`; one `number of the children` after the spread | the stored results of the new formulas, so that the browser calculates them again after an input change; the numbers stay |
| `docs/llm/pending.md`, `docs/llm/pending_prio_2.md` | change 3 open; "rounded count of a child in the portfolio split walk" open | change 3 done, waiting for the run; the prio 2 entry done with change 3 | the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the ids and `src/test/resources/unit/*/list.csv` | — | — | the new word and triple shift the ids of the words and triples of every later import file: `test/reset_db.php` and `test/test_re_pin.php` before `test/test_part.php`; the formula, result and group lists get the new formulas and results |
| `portfolio_split_calculator_js.html`, `portfolio_split_calculator_no_js.html`, `src/test/resources/api/phrase_data/phrase_data_portfolio_split.json` | — | — | the new formulas and results; the no javascript page gets smaller, because the count formulas hold the rounded count once instead of four times |
| the case reports of `portfolio_split_calculator` | 0 differences | 0 differences | the counts stay; `_add_child.md` and `_remove_child.md` change only with change 4 and 5 |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_high.md`, `_low.md`, `_over.md`, `_differ.md` | 0 differences | 50 differences in the high report after the first run of 2026-11-26 (about 11:05): the parent keeps its stored counts, so its realised values and its weight of 30 % stay, while both children are calculated again | the run without `test/reset_db.php`: the database still had the removed formulas `walk count of the first child` and `walk count of the second child` and the old context of the count of the parent without `child`, so the page found no number for `"child","split count"` and kept the stored number; the run of 11:47 after `test/reset_db.php` and `test/test_re_pin.php` gives 0 differences in all these reports, so no file was changed for it |

### Lessons learned by llm (2026-11-26)

- The second time in two days that a changed context of a calc-validation showed up as many differences of numbers
  instead of one clear message: the page keeps the stored number of a result whose operand is missing without telling
  the user, so a missing operand looks like a wrong calculation. A change that renames a formula or changes the
  context of a calc-validation names `test/reset_db.php` as a must in its report, not only as a side effect.

## 5. list without the more specific number

Part of #247, a prerequisite of step 3, change 3 (section 4).

### Request

Found while planning change 3: the count of the parent by the instances, `"split count" = "original" -
"child","split count"` with `"child","split count" = sum( "child portfolio" )`, would also sum the expected
allocations of the split agreement for LISP and LISN (`LISP, split count, first child portfolio, expected`), because a
list takes every number that has at least the wanted phrases, so the sum would be 16 instead of 8. Leaving out
`expected` in every list is wrong, because `expected` is also the probability weighted value of `solution_prio.json`,
a fact that a list sums. Decision of the developer (2026-11-26): a list leaves out a number whose phrases include all
phrases of another number of the list, like the fewest foreign phrases of a plain operand, as a change of its own
before change 3, in all evaluators with tests and a check of every list of the data.

Checked before the first edit with a read-only scratch script: no list of a calc-validation of any import file
(`src/main/resources/messages`, `src/test/resources/import`) has a number that includes all phrases of another, so
no stored result changes; the hand made fixtures of `formula_tests` and `use_case_ui_tests` have such numbers and are
changed so that their numbers differ by a phrase each, which keeps what they test.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/shared/calc/phrase_kinds.php`, `src/main/php/cfg/formula/formula.php` (`list_numbers`), `src/main/php/web/formula/formula_calc.php` (`list_values`), `src/main/php/cfg/helper/data_object.php` (`list_numbers`) | a list takes each number with all wanted phrases, a second number of the same phrases only once | `phrase_kinds::with_fewest_phrases`: without a number whose phrases include all phrases of another number of the list, called by the backend, the page and the import | one rule for the three php evaluators |
| `src/main/resources/script/calculator_json.js` (`listEntries`), `src/main/resources/script/formula_editor.js` (`listEntries`) | the same as the php before | `withFewestPhrases` with the same rule | the browser calculates the same list |
| `src/test/php/unit/formula_calc_tests.php`, `src/test/php/unit/formula_tests.php`, `src/test/php/unit_ui/use_case_ui_tests.php`, `src/test/php/unit/import_tests.php`, `src/test/php/create/test_values.php`, `docs/json_structure.md`, `docs/llm/json_values.md` | the list fixtures with a value of only the wanted phrases next to a more specific number | the fixtures with numbers that differ by a phrase each; positive and negative tests of the rule for the helper, the backend, the page and the import; the rule in the two docs | the tests and the description of the list functions |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the snapshots and the stored results | — | — | none, because no list of the data has such a number (scratch check); a changed snapshot outside the portfolio split points to a list that the check missed |
| `docs/code_test_coverage.md`, `docs/code_functions_all.md` | — | — | the new function, regenerated by the test run |

### Unexpected changes

None found: after the run of 2026-11-26 11:47 the case reports of the portfolio split stay at 0 differences, and the
changes were committed with 12592d34f.

| file | old | new | reason |
|---|---|---|---|

### Lessons learned by llm (2026-11-26)

## 6. add child in the browser

Part of #247, step 3, change 4 of 5: the button "Add child" adds the next child on the javascript page and the page
calculates its results.

### Request

"do the next open task of the portfolio split page", with the "Missing in the generated page (5)" of
`portfolio_split_calculator_diff_add_child.md` selected (the field of the third child, the warning "Weights differ",
the result table and the walk with a third child column, the three remove buttons).

Decisions of the developer (2026-11-26):
1. change 4 is the button "Add child" in the browser with the results of the new child, so that its run shows the
   case report `_add_child.md`; change 5 is the remove button and the logged save of a new child
2. the name of a new child is declared in the json: the repeat triple `has a` the children that the button can add,
   without their `is a child portfolio` triple, and the button takes the next one that is no child yet

Planned before the first edit:
- only the third child is declared: the page orders the instances by their label (`sorted` in `calculator_json.js`),
  so a declared `fourth child` would be shown before the `second child`; more children need an order of the
  instances first (`docs/llm/pending_prio_2.md`); after the third child the button is disabled, like the sample after
  its last ordinal
- the new child gets the weight of the first child if the other instances leave enough of 100 %, else the rest, like
  the sample; a field that is no share gets the number of the first child
- the results of the new child are copies of the results of the first child with the new child instead of the first
  one in their phrases and their context and without a stored number, so that the page calculates them; a result whose
  formula names the first child is not copied, because it calculates only the first child; the sums over the children
  take the new child, because its `is a child portfolio` triple makes it a kind of `child portfolio` (`withKinds`)
- the new field is shown without its save vars, so that the save leaves it out (`frontend::calculator_changes`) until
  change 5 saves the child with its phrase, its triple and its weight

Checked after the edits and before the run, with a read-only scratch script without database: `calculator_json.js`
in node with a minimal stand in of the DOM, the seed of the snapshot `portfolio_split_calculator_js.html` of 11:46
plus the six new triples and the word of the json as the phrase data walk should deliver them, and a press of "Add
child" through its click handler. Every item of the report `_add_child.md` of 2026-11-25 is then the one of the
sample: the field "Target weight third child", the warning "Weights differ, so every child is rounded on its own
deviation.", the result table and the walk with the third child column, three remove buttons, the parent at 0 % and
all 56 rows of the table, the check table and the walk with the numbers of the sample (e.g. LISP 3, 3, 3 and 1 by the
equal part). Not checked by it: whether the phrase data walk delivers the new triples, and the browser itself.

#### Follow-up request of 2026-11-26

"try to solve the findings" of the review of change 4: (1) the id shift belongs in "Unexpected changes" (done
there), (2) the object literals of `copyResults` and `newInstanceEntry` pack too much into one line, (3) the name
`formulaNames` reads like a list getter and its line packs three fallbacks, (4) a save after "Add child" leaves out
the new child without telling the user, (5) a new child without a number is only reported on the console.

Planned: one field per line in the two object literals; `formulaHasOperand` with the formula in a named step; a hint
below the field of an added instance that the save does not keep it yet, and in front of it that it has no number
because the first instance has none; two new message ids with en and de texts for the page script. The console
warning stays, because an error message is never removed. The hint is a second hint span of the field, and the case
report compares only the first one, so `_add_child.md` stays at 0.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` (words, triples) | the first and the second child | the word `third` and the triples `third child`, `third child portfolio`, its label, the short walk column `Third` and the repeat triple `has a` `third child portfolio`, at the end of their blocks | the name, the labels and the column of the child that "Add child" adds; at the end, so that no object of the file gets another id |
| `src/main/resources/script/calculator_json.js` (`repeatFields`, new `addInstance` with its helpers, the click listener) | the button without a function | the button adds the next declared instance with its weight and the copies of the results of the first instance, draws the fields of the repeated input again and calculates the results; disabled without a next instance | the field, the warning, the tables and the remove buttons of the third child |
| `docs/json_structure.md`, `docs/llm/pending.md`, `docs/llm/pending_prio_2.md` | the repeat keyword without new instances; change 4 open | the declared instances of the repeat triple; change 4 done, waiting for the run; the order of more instances | the description and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the ids and `src/test/resources/unit/*/list.csv` | — | — | the new word and triples shift the ids of the words and triples of every later import file: `test/reset_db.php` and `test/test_re_pin.php` before `test/test_part.php` |
| `portfolio_split_calculator_js.html`, `src/test/resources/api/phrase_data/phrase_data_portfolio_split.json` | — | — | the seed gets the third child and its labels by the walk over the `has a` items; the page without javascript stays the same, because the third child is no instance |
| `portfolio_split_calculator_diff_add_child.md` | 5 missing, 57 different, 2 only in the generated page | the five missing items and the two tables found; differences in the numbers can stay where the sample calculates its own way (to be read after the run) | the button works |
| `portfolio_split_calculator_diff_remove_child.md` | 1 missing, 55 different | changes too, because it presses "Add child" first; the remove button still has no function (change 5) | — |
| the other case reports | 0 differences | 0 differences | they press no button |

### Unexpected changes

After the run of 2026-11-26 14:49 (`test/reset_db.php`, `test/test_re_pin.php`, `test/test_part.php`) the report
`_add_child.md` has 0 differences, the other case reports without a button stay at 0, and the seed of
`portfolio_split_calculator_js.html` has the new triples with their labels, so the phrase data walk delivers them.

| file | old | new | reason |
|---|---|---|---|
| `src/test/resources/unit/triple/list.csv` and the snapshots with a triple id, `index.html`, `portfolio_split` | the expected row says: at the end, so that no object of the file gets another id | `third child` gets the id 4618 before `first child portfolio`, and the other five new triples get ids between the triples of the file too (4684, 4728, 4845, 4846, 4849), so every triple of the portfolio split file after 4618 shifts by one to five, e.g. the page triple `example portfolio split` from -4620 to -4621 in the pinned link of `index.html` and in `portfolio_split`; the triples of the later files shift by six | the importer does not write the triples in the order of the json: `third child` links two words and is written before the triples that link a triple, like `first child portfolio`; no `*_ID` const pins one of the shifted ids, so `test/test_re_pin.php` changed no const; the word `third` is no new word, because `solution_prio.json` has it already, so the word ids stay |
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_no_js.html` | stays the same | the visible page stays the same, but the seed json of the page has the word `third` and the six triples, besides the shifted ids | the seed is the phrase data of the page also without javascript |
| `src/test/resources/api/phrase_data/phrase_data_portfolio_split.json` | gets the third child | unchanged | the file is the input of the calculator report test of `phrase_data_ui_tests` (last changed 2026-11-18) and the run never writes it |
| `src/test/resources/web/html/use_cases/portfolio_split_calculator_diff_remove_child.md` | 1 missing, 55 different | 2 missing, 57 different: the generated page shows the parent at 0 % and the three children, so the two tables of the sample with only the first and the second child are missing | as the expected row says: the button "Add child" works, the remove button has no function until change 5 |
| `src/main/resources/script/calculator_json.js` (`newInstanceEntry`, `copyResults`, `formulaNames`) | object literals with six and eight fields in one line; `formulaNames` with three fallbacks in one line | one field per line; `formulaHasOperand` reads the formula in a named step | follow-up of 2026-11-26, findings 2 and 3 of the review |
| `src/main/resources/script/calculator_json.js` (`numberField`, new `addedHint`, `addInstance`), `src/main/php/shared/enum/messages.php`, `src/main/php/web/component/execute/system_page.php` (`CALC_PAGE_TEXT_IDS`), `src/main/resources/translations/en.yaml`, `de.yaml` | the field of an added instance without a hint of its own | a hint below the field that the save does not keep it yet, and that it has no number if the first instance has none (`calculator_added_not_saved`, `calculator_added_no_number`) | follow-up of 2026-11-26, findings 4 and 5: the user sees what the save leaves out; expected after the run: the texts of the page script in the js snapshots of every calculator page, the case reports unchanged |

### Lessons learned by llm (2026-11-26)

- The id of a new triple is not its position in the json: the importer writes a triple of two words before the
  triples that link a triple, so a triple appended at the end can still shift the ids of the file itself. Before
  claiming that no id of the file changes, check where `list.csv` puts a triple of the same kind.
- Before listing a fixture as a side effect, check that the run writes it; an input fixture of a test stays as it is.

## 7. remove child in the browser

Part of #247, step 3, change 5 of 6: the button "Remove the ..." removes an instance on the javascript page and the
page calculates without it.

### Request

"do the next open task in pending.md": change 5 of step 3.

Decisions of the developer (2026-11-26):
1. change 5 is only the remove on the page, so that `_remove_child.md` can reach its target; the logged save of an
   added or a removed child becomes change 6 with its own plan and run
2. a child keeps the name of its phrase after a remove: the case `remove_child` adds the third child and removes the
   second, and the page shows the first and the third child, while the hand made sample numbers the children by their
   position and shows the former third child as "second child"; these label differences stay in `_remove_child.md`
   on purpose

Planned before the first edit:
- the remove button names its instance (`data-remove`), and the click takes the instance from the page: its `is a`
  triple of the repeated phrase, so that it is no instance and no column any more, and every number and result whose
  phrases or context name it, so that no sum and no table takes it; the page then draws the fields of the repeated
  input again and calculates the results
- a removed instance that the add button has added is gone completely and the add button can add it again; a
  removed instance of the database stays in the database, because the save leaves out its number without its field,
  so a hint below the fields of the repeated input names it ("Removed on this page only, a save keeps: ...") until
  change 6
- the fields of a repeated input are drawn again by its part name, so that the remove works also without an add button
- the numbers of the case report after the run: the first child at 35 % and the third at 30 %, so the parent at 35 %
  like the sample; the differences left are the labels "third child" and "Third" of decision 2

Checked after the edits and before the run, with the read-only scratch script `sim_remove_child.js` (node, a
minimal stand in of the DOM, the seed of the snapshot of 14:49): after "Add child" and "Remove the second child"
the fields "Target weight first child" 35 and "Target weight third child" 30, the hint "Removed on this page only, a
save keeps: second child", the warning "Weights differ", the result table "Parent | First child | Third child" with
the numbers of the sample (e.g. Stocks 3'413'937 | 3'424'013 | 2'930'550) and the walk with the numbers of the sample
(e.g. LISP 4 | 3 | 3, LISN 9 | 12 | 19); removing the added third child instead gives the page of the start again
with "Add child" enabled.

#### Follow-up request of 2026-11-26

"try to avoid" the five missing and three additional items of `_remove_child.md`, "if possible": the field "Target
weight second child", the two check rows of the second child and the two tables with a "Second child" column are
missing, and the page shows the third child instead. This replaces decision 2.

Planned: the instances of a repeated input keep their place, as the sample numbers the children by their position.
The remove button no longer takes its own instance from the page. Instead, each field from the removed one to the
second last takes the number of the field behind it, and the last instance is removed:
- an entry of the database keeps its id, so its number changes like an input that the save writes
- if the instance behind has no number, the field in front has none either
- if the field in front has no number but the one behind has, it gets an added number like a new instance
- the hint and the bookkeeping of the added and the removed instances go by the last instance

So after "Add child" and "Remove the second child" the page shows the first child at 35 % and the second at 30 %,
and the second child keeps its check rows. The changes are listed as rows of "Unexpected changes" below.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/script/calculator_json.js` (`removeButton`, `redrawRepeatFields`, `repeatFields`, new `removeInstance` with its helpers, the click listener) | the remove button without a function | the button takes its instance, its numbers and its results from the page and calculates again; a hint names the removed instances of the database | the tables of `_remove_child.md` with the first and the third child and the numbers of the sample |
| `src/main/php/shared/enum/messages.php`, `src/main/php/web/component/execute/system_page.php` (`CALC_PAGE_TEXT_IDS`), `src/main/resources/translations/en.yaml`, `de.yaml` | — | `calculator_removed_not_saved` | the text of the hint |
| `docs/json_structure.md`, `docs/llm/pending.md` | the repeat keyword with the add button; change 5 open | the remove button on the page; change 5 done, waiting for the run, change 6 the logged save, decision 2 | the description and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `portfolio_split_calculator_js.html` and the js snapshots of the other calculator pages | — | — | the new text in the texts of the page script, and `data-remove` on each remove button of a repeated input |
| `portfolio_split_calculator_diff_remove_child.md` | 2 missing, 57 different | only the differences of decision 2: the field "Target weight third child" instead of "second child", the columns "Third child" and "Third", and the two check rows of the second child (`LISN share` and `LISP share`) missing, because the page removes the expected values of the removed second child and the third child has none, while the sample shows them for its renumbered second child; the numbers as in the sample | the remove button works |
| the other case reports | 0 differences | 0 differences | they press no remove button |
| the ids | — | — | none: no import file changes, so `test/test_part.php` without `test/reset_db.php` |

### Unexpected changes

The run of 2026-11-26 (`test/reset_db.php`, `test/test_re_pin.php`, `test/test_part.php`) covered this change
together with change 1 of `docs/llm/log/2026-11-26_csv_format_phrases.md` and with `files::AUTO_UPDATE_TEST_FILES`
set to `true`. So the snapshots were overwritten instead of failing, and this table compares the regenerated files
with the plan. `_remove_child.md` is as the expected row says:
- "Different (0)", so the numbers equal the sample.
- "Missing (5)": the field "Target weight second child", the two check rows of the second child and the two tables
  with a "Second child" column.
- "Only in the generated page (3)": the field "Target weight third child" and the two tables with a "Third child"
  column.

The other case reports did not change.

| file | old | new | reason |
|---|---|---|---|
| `portfolio_split_calculator_js.html`, `npp_ch_vote_js.html`, `pv_lifetime_balance_js.html`, `calculator_editor.html`, `npp_ch_vote_editor.html` | the new text and `data-remove` on each remove button | only the three new texts in the settings of the page script (`calculator_added_not_saved` and `calculator_added_no_number` of the follow-up of section 6 too), twice in the two editor pages; no `data-remove` | a js snapshot holds the seed and the settings, the script links `calculator_json.js` and draws the buttons in the browser, so a drawn attribute never shows in a snapshot; the editors carry the page settings twice, like `calculator_remove` |
| the ids, `src/test/resources/unit/*/list.csv`, `src/test/php/const/triple_names.php`, `index.html`, `nnp`, `pv`, `portfolio_split` | none | shifted | not by this change: change 1 of `docs/llm/log/2026-11-26_csv_format_phrases.md` was in the same run and its log names the shift |
| `docs/code_user_message_exceptions.md` | — | `system_page.php:2258` → `:2261` | the generated report names the line of each unexplained message, and the three new ids of `CALC_PAGE_TEXT_IDS` (one of this change, two of the follow-up of section 6) move it by three lines |
| `src/main/resources/script/calculator_json.js` (`removeInstance`, new `moveNumber` and `removeEntry`) | the remove button takes its own instance from the page, and the page shows the first and the third child (decision 2) | each field from the removed one on takes the number of the field behind it, and the last instance is taken from the page; `removeEntries`, the hint and the added and removed instances go by the last instance | follow-up of 2026-11-26: the children keep their place like in the sample. The read-only scratch script `sim_remove_child.js` gives, after "Add child" and "Remove the second child", the fields "first child" 35 and "second child" 30, the tables "Parent \| First child \| Second child" and "First \| Second" with the numbers of the sample, and the check rows "LISN share, second child portfolio" 9 \| 12 and "LISP share, second child portfolio" 4 \| 3, both "differs" as in the sample. Removing the added third child brings back the start page; removing the first child moves 35 and 30 up; removing a child of the database without an add names it in the hint. Expected after the run: `_remove_child.md` with 0 missing, 0 different and nothing only in the generated page |
| `src/main/resources/script/calculator_json.js` (the pool of the values, `saveVars`) | the save baseline (`8`-prefixed var) of a number field is the number of its entry | the stored number of the value, kept at the load like the stored number of a result | found with the follow-up: a redraw of the fields of a repeated input wrote a changed number as the baseline, so the save saw no change and kept the old number in the database. This happened with a number that a remove has moved, and also with a number that the user changed before pressing "Add child" (change 4); the simulation shows the baseline 35 behind the moved 30 |
| `docs/json_structure.md` (the repeat row), `docs/llm/pending.md` | an instance keeps the name of its phrase; decision 2 | the instances keep their place; the follow-up waiting for the run | follow-up of 2026-11-26 |
| `src/main/resources/script/calculator_json.js` (`moveNumber`, `removeInstance`, `addedHint`, new `movedInstances`), `src/main/php/shared/enum/messages.php`, `src/main/php/web/component/execute/system_page.php` (`CALC_PAGE_TEXT_IDS`), `src/main/resources/translations/en.yaml`, `de.yaml` | a field whose number a remove has moved without a number of the database behind it shows a number that the save does not write, or none while the database keeps one, without a hint | the instance is kept in `movedInstances`, and its field gets the hint `calculator_moved_not_saved` "Moved on this page only: a save does not change the number of the database yet."; the instance leaves the set when a remove takes it from the page | finding 2 of the review of 2026-11-26: never fail silently. The simulation without the target share of the first child in the seed showed 35 for the first child after "Remove the first child", without save vars and without a hint. Expected after the run: the new text in the settings of the js snapshots, the case reports unchanged |
| `src/main/resources/script/calculator_json.js` (`removeInstance`) | the name of the instance read inside the `indexOf` call with a ternary | a named step `instance` | finding 4 of the review: one logical element per line |

The second run of 2026-11-26 (`test/reset_db.php`, `test/test_re_pin.php`, `test/test_part.php`, again with
`files::AUTO_UPDATE_TEST_FILES` set to `true`) covered the follow-up rows above, together with
`docs/llm/log/2026-11-26_scale_font_icons.md` and the follow-up of `docs/llm/log/2026-11-26_csv_format_phrases.md`.
As the follow-up rows expect:
- `_remove_child.md` has 0 missing, 0 different and nothing only in the generated page.
- The other case reports did not change.
- The text `calculator_moved_not_saved` is in the settings of `portfolio_split_calculator_js.html`,
  `npp_ch_vote_js.html` and `pv_lifetime_balance_js.html`, and twice in `calculator_editor.html` and
  `npp_ch_vote_editor.html`.

The moves, the save baseline and the moved hint are drawn by the script, so no snapshot shows them; the simulations
above are what checks them.

| file | old | new | reason |
|---|---|---|---|
| `docs/code_user_message_exceptions.md` | — | `system_page.php:2261` → `:2262` | the new id of finding 2 in `CALC_PAGE_TEXT_IDS` moves the line by one; the finding 2 row missed it, although the lessons below name it |

### Lessons learned by llm (2026-11-26)

- A snapshot of a javascript page has the data of the page, not the html that the script draws: before listing an
  attribute of a drawn element as a side effect, check that the snapshot has the html of that element; the case
  reports are what show the effect of the script.
- A generated report that names a line number changes with every line added above it; list it as a side effect of
  each change to a file that the report names.
- "No id change" holds only for a run of this change alone; when two changes share one run, the plan names the
  other change, so that the shift is not read as a side effect of this one.

### Lessons learned by human

has been fine, but test still takes long

## 8. save of an added and a removed child

Part of #247, step 3, change 6a of 6: the save keeps a child that "Add child" has added and removes a child that
"Remove the ..." has taken from the page.

### Request

"do the next open task in pending.md": the new task "report with three and more children" (the pdf shows only
two). The report already takes every child of the database (`calculator::columns` by the `is a child portfolio`
instances); a child added in the browser is missing, because the save leaves it out until change 6 of step 3, and
the report link sends only the phrase id.

Decisions of the developer (2026-11-27):
1. change 6 now, before the report
2. split into 6a, this section: the logged save of an added child and the logged delete of the `is a` triple of a
   removed child; and 6b, the next change: the server page and the report calculate the results of an instance
   without stored results, like the browser does for a new instance, because the server checks only stored results
   (`ui_list::check_results`) and nothing writes the results of a new instance, so without 6b the pdf shows the
   column of a saved third child without positions

Planned before the first edit:
- the page (`calculator_json.js`) sends with the save, per field key of the instance (its phrase id):
  - a number without a database row (an added instance, or a number that a remove has moved into a field without
    one) its phrase ids as `pl<key>` (`url_var::PHRASE_LIST`) instead of the group id and the shown number
  - an added instance the phrase id of the repeated phrase as `pt<key>` (`url_var::PHRASE_TO`), for its `is a` triple
  - a removed instance of the database the id of its `is a` triple as `t<key>` (`url_var::TRIPLE`) and its label as
    `k<key>`, in the hint of the removed instances
- `frontend::action_calculator_save` adds the `is a` triples, then the new values, then saves the changed numbers
  as today, and then deletes the removed `is a` triples, all by the logged writes of the objects (`add_via_api`,
  `update`, `del`); the parsing is in the public `calculator_new_links`, `calculator_new_values` and
  `calculator_removed_links`, which the unit tests call like `calculator_changes`
- the confirmation names a new number with `calculator_saved_new` and a removed instance with
  `calculator_removed_saved`; an added instance without a number gets no confirmation of its own
- the hints stay until the save, so their texts say "until the save": `calculator_added_not_saved` and
  `calculator_removed_not_saved`; the moved hint is only for a number of the database that a remove has taken
  from a field, because the save does not exclude that value yet (open, `docs/llm/pending.md`)
- not in this change: the results of a saved new instance (6b), a new case with an added child, and the values of a
  removed child, which stay in the database without an instance

### Follow-up request of 2026-11-27

"try to fix" findings 3, 4, 5 and 7 of the review of 2026-11-27:
3. no test of the save vars that the script draws: the headless browser test of the portfolio split cases checks the
   hidden inputs in the html of the generated page after a case: `pt` and `pl` of the added child of `add_child`,
   `t` of a removed child of the database of the new case `remove_saved_child` (only "Remove the second child"), and
   as the negative test neither `t` nor `pt` after `remove_child`, which removes the added child again. The new case
   is not in `files::PORTFOLIO_CASES`, so it gets no case report
4. a failed write hides the confirmations of the writes that have succeeded: they are shown also next to the error
5. an added instance whose number is empty is saved silently: its `is a` triple gets the confirmation
   `calculator_added_saved` "<label> added without a number"; the script sends the label for it also without an entry
7. a new number field without its number is an internal inconsistency: `log_err_msg_ui` instead of `log_warning`

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/script/calculator_json.js` (`numberField`, new `newSaveVars`, `removedHint`, `addInstance`, `removeInstance`, `removeKindLink`, `moveNumber`, `movesUnsaved` as `takesStored`), `src/main/resources/translations/en.yaml`, `de.yaml` (the two hint texts) | an added number and an added or removed instance without save vars, the hints "a save does not keep it yet" | the save vars of an added number, of an added and of a removed instance; an instance of the database that is added again after a remove gets its triple back; the hints until the save | decision 1 |
| `src/main/php/web/frontend.php` (`action_calculator_save`, new `calculator_new_links`, `calculator_new_values`, `calculator_removed_links`), `src/main/php/shared/enum/messages.php`, `en.yaml`, `de.yaml` (`calculator_saved_new`, `calculator_removed_saved`) | only the changed numbers are saved | the `is a` triples of the added instances, their numbers and the delete of the `is a` triple of a removed instance | decision 1 |
| `src/test/php/unit_ui/use_case_ui_tests.php` (`calculator_save_tests`), `docs/json_structure.md` (the repeat row), `docs/llm/pending.md` | — | the tests of the three parsers with a negative test each; the save of a repeated input; change 6a done, 6b next | the tests and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the js snapshots and the editor snapshots of the calculator pages | — | the two new hint texts in the settings of the page script | the texts are in the settings of each page |
| the case reports `_add_child.md`, `_remove_child.md` and the others | 0 differences | 0 differences | the save vars are hidden inputs, and a case report compares the texts |
| `docs/code_test_coverage.md`, `docs/code_functions_all.md` | — | the three new functions | generated reports |
| the ids | — | unchanged | no import file changes, so `test/test_part.php` without `test/reset_db.php` |

### Unexpected changes

The run of 2026-11-27 (`test/test_part.php`, with `files::AUTO_UPDATE_TEST_FILES` set to `true` and back to
`false` after it) covered this change together with change 2 of `docs/llm/log/2026-11-26_csv_format_phrases.md`.
It stopped right after `use_case_ui_tests` with the fatal `Class "...phrase_data_ui_tests" not found` at
`a_selected_test.php:709`. So the parser tests of this change ran, but the write workflow tests behind it did not,
e.g. `value_write_url_tests` with the save of the calculator. Whether the tests passed is in the output of the run.

As planned:
- The two hint texts are in the settings of `portfolio_split_calculator_js.html`, `npp_ch_vote_js.html` and
  `pv_lifetime_balance_js.html`, and twice in `calculator_editor.html` and `npp_ch_vote_editor.html`.
- The case reports did not change.
- The ids did not change.

The save vars are drawn by the script, so no snapshot shows them; the read-only scratch simulation of the plan is
what checks them so far.

| file | old | new | reason |
|---|---|---|---|
| `src/test/php/unit_write/a_selected_test.php` | `phrase_data_ui_tests` called without its include and its use | the include of `phrase_data_ui_tests.php` and its use | not by this change: b46d138d2 (section 6) switched the test on in the selected tests, but only `all_ui_tests.php` loads the class; the fatal stopped the run. A bug fix |
| `docs/code_test_coverage.md` | the three new functions | the three parsers have two calls or more; `calculator_saved_new_text` has none and `calculator_removed_text` has one | the plan missed the two public text helpers; `calculator_saved_new_text` is called only by the save with `$do_it`, which no unit test runs |
| `docs/code_functions_all.md` | the three new functions | the five new public functions of `frontend`, each "in a section not yet defined" (the report suggests `execute`), and as the description of `calculator_removed_text` its first `@param` line | the two text helpers are public for the tests; the report has no section rule for the new functions yet, and `calculator_removed_text` starts its docblock with `@param`. Not changed: a generated report is a signal |
| `docs/code_object_name_exceptions.md` | — | `$add_act_msg` in the list of `user_message` | the var of the new test of the save with an added instance is not a known name of a message |
| `src/main/php/cfg/log/change_log_list.php` (new `change_class` and `group_size`, used by `load_sql_obj_fld` and `load_sql_obj_last`), `src/test/php/utils/test_base.php` (`delete_change_log_of_obj`), `src/test/php/unit/change_log_tests.php`; generated: `$log_named` no longer in `docs/code_object_name_exceptions.md`, `change_class` in `docs/code_functions_all.md` | the cleanup deletes the loaded change log entries from the table of the class of each entry | the cleanup deletes them from the table that they are loaded from | not by this change: `test/test.php` of 2026-11-27 failed "view component name change" in `change_log_read_tests.php`. Since e8f5b7ba1, the value cleanup of `value_write_url_tests` deleted the ids of the value changes from the `changes` table, because the list loads every entry as a `change`, and with them e.g. the name change of component 1. A bug fix; the deleted rows come back only with `test/reset_db.php` |

Follow-up of 2026-11-27 (findings 3, 4, 5 and 7 of the review), after the run above:

| file | old | new | reason |
|---|---|---|---|
| `src/test/js/portfolio_split_calculator_cases.js`, `src/test/php/const/files.php` (`PORTFOLIO_CASE_REMOVE_SAVED_CHILD`), `src/test/php/unit_read/system_views_read_tests.php` (`run_portfolio_split_calculator_cases`, new `assert_calculator_save_vars` and `hidden_vars`) | the save vars only checked by a scratch simulation | the hidden inputs `pt`, `pl` and `t` in the html of the generated page after `add_child` and after the new case `remove_saved_child`, and none of `t` and `pt` after `remove_child` | finding 3; expected after the run: no new case report, because the new case is not in `files::PORTFOLIO_CASES` |
| `src/main/php/web/frontend.php` (`action_calculator_save`, `user_msg_html`) | the confirmations only if every write has succeeded, and the notification shows the html info only without a message | the confirmations of the writes that have succeeded also next to the error of a failed one, and the notification shows an html info below the message | finding 4; found while fixing it: `user_msg_html` shows only one text, so a confirmation next to an error would not have been shown. This applies to every page with an html info and a message, also the missing suggestions of the add word view (`web/word/word.php` `add_missing_suggestions`); expected after the run: a snapshot of the add word view with a message, if there is one, gets the missing suggestions below it |
| `src/main/php/web/frontend.php` (`action_calculator_save`, new `calculator_added_text`), `src/main/php/shared/enum/messages.php`, `en.yaml`, `de.yaml` (`calculator_added_saved`), `src/main/resources/script/calculator_json.js` (`numberField`), `src/test/php/unit_ui/use_case_ui_tests.php` | an added instance without a number saved without a confirmation | "<label> added without a number"; the script sends the label `k` of an added instance also without an entry | finding 5; the new unit tests also call `calculator_saved_new_text`, which had none, and `calculator_removed_text` got the description line that the function report has missed; expected after the run: the snapshots unchanged, because the text is a server text and the label a drawn hidden input. The read-only scratch simulation `sim_save_vars.js` gives `pt-4700=-4542` and `pl-4700=231,-4637,-4700,-4466` for `add_child`, `t-4636=4704` for `remove_saved_child` and neither `t` nor `pt` for `remove_child` |
| `src/main/php/web/frontend.php` (`calculator_new_values`) | `log_warning` for a new field without its number | `log_err_msg_ui` | finding 7: an internal inconsistency that the user cannot fix |

The run of 2026-11-27 of the follow-up failed the test "... and an issue of the request is shown instead of the html
info" of `system_view_ui_tests.php`: the fix of finding 4 showed every html info below an issue, but that test pins
the rule that an issue is shown instead of the html info, e.g. instead of the missing suggestions of the add word
view. So the rule stays, and the confirmation of the writes that have succeeded is an info of its own:

| file | old | new | reason |
|---|---|---|---|
| `src/main/php/web/user/user_message.php` (new `done_html`, `add_done_html`, `get_last_done_html`, `has_info`, `reset`), `src/main/php/web/frontend.php` (`user_msg_html`, `action_calculator_save`) | every html info below an issue (fix of finding 4) | the html info only without an issue as before; the confirmation of the writes that have succeeded (`add_done_html`, added only after the write) also below an issue | the failed test; explicit is better than implicit: only a caller that has written adds a done confirmation |
| `src/test/php/unit_ui/system_view_ui_tests.php`, `src/test/php/unit_write_workflow/value_write_url_tests.php` | — | a done confirmation is shown below an issue (positive), and the html info is not (the existing negative test); the calculator write test reads the done confirmation | the tests of the new info |

The run of 2026-11-27 that started at 08:06 (`test/test_part.php`, with `files::AUTO_UPDATE_TEST_FILES` set to
`true`) covered the follow-up rows above together with section 9 and the follow-up of section 2 of
`docs/llm/log/2026-11-26_csv_format_phrases.md`; the pasted output of that run names no failure of these rows, so
whether the save var asserts of the browser test have passed is in the rest of its output. As the rows expect:
- `calculator_saved_new_text` and `calculator_added_text` have one unit test call each, and `calculator_removed_text`
  has its description line in `docs/code_functions_all.md`.
- No snapshot of a calculator page changed by the new texts or the save var of the label.

| file | old | new | reason |
|---|---|---|---|
| `docs/code_functions_all.md` | — | `calculator_added_text` "in a section not yet defined" (the report suggests `execute`), `add_done_html` (suggested `add`) and `get_last_done_html` ("expected to be set and get") | the report has no section rule for the new functions yet; not changed, a generated report is a signal |
| `docs/code_object_name_exceptions.md` | — | `$new_val` in the list of `value` | the var of the new test of `calculator_saved_new_text` in `use_case_ui_tests.php` is not a known name of a value |
| `src/test/resources/web/html/views_by_object/ref/ref_default_ref_29.html` | the history row "added Q167" of 26-12-2022 by zukunft.com system | without it | not by this change: one of the rows of the `changes` table that the value cleanup has deleted by the ids of value changes before the fix of section 8 (`change_log_list::change_class`); the run wrote the snapshot without the row because of the auto update, so it needs a restore by the developer, and the row comes back only with `test/reset_db.php` |
| `src/test/resources/import/wikidata/cache/Q72.json` | the label "Tsúri (engumba)" of 2026-11-24 | "Tsúri" of 2026-11-27 | not by this change: a run that reaches wikidata updates its cache like the live caches of the memory |

### Lessons learned by llm (2026-11-27)

- A test that `a_selected_test.php` switches on needs its include and its use there too, because `php -l` does not
  find a missing class; a fatal there stops the run, so the tests behind it are not covered by that run.
- Every new public function is a row of the coverage side effect, also a text helper that is public only for its
  test; a function that only the save with `$do_it` calls needs a unit test call of its own.
- A cleanup that deletes by ids takes the table from the query that has loaded the ids, never from the class of the
  loaded object, because an id is unique only within its table.

## 9. the results of a saved child

Part of #247, step 3, change 6b of 6: a child that the save has added (change 6a) is an instance of the database
without stored results, because nothing calculates the results of a new instance in the backend; so its column
has no positions on the server page and in the pdf report, and after a reload also on the javascript page.

### Request

"do the next open task in pending.md 'portfolio split'": change 6b, next by the decision of 2026-11-27 (section 8).

Decision of the developer (2026-11-27): all three pages, also the javascript page, whose script copies the results
of the first instance only for "Add child" (`copyResults`).

Planned before the first edit:
- the script (`calculator_json.js`) at the load: for each repeated input, each instance without any result gets the
  results of the first instance with results, like an added one (`copyResults`: the instance in the phrases and the
  context instead of the first one, without a result whose formula names the first one); if it has copied any, the
  page calculates all results again, like after "Add child" (`recalculate`), so that the sums over the children
  include the saved child. A page without such an instance is drawn as today, so its case reports do not change
- the server (`ui_list::calculator_numbers`, which the page without javascript and the pdf report use) does the same
  after `check_results`: the copies are calculated in passes by `formula_calc::result`, a copy only once its
  operands are numbers of the page, e.g. the walk row after the row before it; a copy whose operands never come is
  left out with a warning; then `check_results` again, so that a result over all children includes the copies. A
  copy has no database id and is added as a duplicate of the list
- the tests: `phrase_data_ui_tests` with `phrase_data_portfolio_split.json` without the results of the second child,
  whose copies must have the stored numbers of the second child, the walk of the second child too (positive), and
  the full fixture without any copy (negative); the headless browser test with a copy of the generated javascript
  page without the results of the second child in its seed, which must show the same page as the full one
- not in this change: an instance with some results but not all (it is not copied), and a backend job that stores
  the results of a new instance

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/script/calculator_json.js` (new `addMissingInstanceResults` and `hasResults`, the start of the page) | the results of an instance are copied only by "Add child" | at the load also for an instance of the database without results, and then all results calculated again | decision of 2026-11-27 |
| `src/main/php/web/component/execute/ui_list.php` (`calculator_numbers`, new `add_instance_results`, `has_results`, `instance_results`, `formula_names`, `replaced_group`, `calculate_copies`, `chain_depth`, `rows_before`), `src/main/php/web/component/execute/calculator.php` (new `repeated_phrases`, used by `is_repeated`), `src/main/php/shared/enum/messages.php` (`CALCULATOR_INSTANCE_RESULTS_MISSING`) | the page without javascript and the report show the stored results only | the copied results of an instance without results, calculated with the numbers of the page; the copies are sorted by the position of their row in the follower chain, so that one pass mostly calculates a whole walk, because each operand scans the list | change 6b |
| `src/test/php/unit_ui/phrase_data_ui_tests.php` (and the seed of `portfolio_split_calculator_js.html` instead of the old `phrase_data_portfolio_split.json` of 2026-11-16, which still has the formula "walk count of the second child"), `src/test/php/unit_read/system_views_read_tests.php` (new `assert_instance_results`, `differing_items`), `src/test/php/utils/test_base.php` (new `page_seed`, `with_page_seed`), `src/test/php/const/triple_names.php` (`PORTFOLIO_SECOND_CHILD`), `docs/json_structure.md` (the repeat row), `docs/llm/pending.md` | — | the tests above; change 6b done and waiting for the run | the tests and the task list. The read-only scratch simulation `sim_missing.js` draws the same result tables from the seed without the 217 results of the second child as from the full seed |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the snapshots and the case reports | — | unchanged | every instance of the test data has its stored results, so nothing is copied |
| `docs/code_test_coverage.md`, `docs/code_functions_all.md`, `docs/code_object_name_exceptions.md` | — | the new functions, and maybe the vars `$rep`, `$ins` and `$copy` | generated reports |
| the ids | — | unchanged | no import file changes, so `test/test_part.php` without `test/reset_db.php` |
| the time of the unit ui tests | — | a few seconds more | the new test calculates the numbers of the portfolio split page twice |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/main/resources/translations/de.yaml` | — | the German text of `CALCULATOR_INSTANCE_RESULTS_MISSING` by its English text as the key, next to `CALCULATOR_CELL_AMBIGUOUS` | the run of 2026-11-27 failed "the translation of de has a text for every message": a message whose id is its English text needs its German text in `de.yaml`, which the plan missed |
| `src/main/php/web/formula/formula_calc.php` (`value_of`) | the result that is calculated is excluded as its own operand by its id | by the object, and by the id only for a database id | the run of 2026-11-27 failed "a child without stored results gets the results of the first child" with 45 of 217: every copy has the id 0, so each copy excluded all the other copies as its operands, and only the copies whose operands are stored numbers got a number (e.g. the target quantity of the first row, but not its rounded count) |
| `src/main/php/web/component/execute/ui_list.php` (`calculate_copies`, new `calculated_open`) | passes over all copies, sorted by the row | row by row from the first one: the copies of a row in passes until none more, e.g. the target quantity, the rounded count, the split count and the deviation of a row, then the next row with the copies left open | the same failed test took 64 seconds: a walk row depends on the copies of its own row in an order that the sort by the row does not give, so each pass over all copies would advance one step of one row |
| `src/main/php/web/component/execute/ui_list.php` (`calculate_copies`, `calculated_open`), `src/main/php/web/formula/formula_calc.php` (`with_numbers`) | a copy is added to the numbers of the page once it is calculated | all copies are added first, and a result without a number is a missing operand and never the number 0 | the next run of 2026-11-27 had a number for every copy, but 81 of 217 differed from the stored ones from SCHP on (split count 420 / 421): an operand selects the number with the fewest foreign phrases, so while the copy of the rounded count of a child was not in the list yet, the split count of the child took the stored "child", "rounded count" of all children instead and was never calculated again; the script has its copies in the list from the start and calculates an operand first (`calculated`). So a copy that stays without a number is in the list without a number, an empty cell next to the warning, and no longer left out as planned |
| `src/test/php/utils/test_base.php` (`write_value_cleanup`, `cleanup_change_log_value`) | each step of the value cleanup and then the cleanup of the change log use the same group object | each step of the value cleanup gets its own copy of the group, and a test value whose group has no id is reported instead of skipped silently | not by this change: the run of 2026-11-27, the first one with the write workflow tests since the fatal of section 8, failed "the value of System Test Word, System Test Word To has no change log of an earlier run" (3, 2 and 2 entries) and, with `files::AUTO_UPDATE_TEST_FILES` set to `true`, wrote the history of the earlier run ("added 123.456", "changed to 123456", "deleted") into five snapshots of `workflow_write`. `load_by_grp` gives the value the group of the cleanup (`set_grp`), and the id hook of `sandbox_multi` sets the group id with the value id, so the `reset` and the `del` of the value cleanup have set the id of that group to 0, and `cleanup_change_log_value` skipped the change log of the value; since 12592d34f the check no longer repairs it. The five snapshots are written again by the next run with the auto update, or restored by the developer |
| `src/main/php/cfg/db/sql.php` (`NAME_EXT_NO_VALUE`), `src/main/php/cfg/sandbox/sandbox_value.php` (`sql_delete`), `src/test/php/unit/value_tests.php` | the logged delete of a value without a number has the same function name as the one of a value with a number | the name gets `_no_value`, e.g. `value_prime_p3_delete_log_no_value_user` | not by this change: the next run had 18 internal errors "function value_prime_p3_delete_log_user(...) does not exist" and the same for `value_delete_log_user`, in the cleanup of the test values for the users 3, 4 and 6. With its own copy of the group (row above), `write_value_cleanup_force` finds the value and removes the user row of each test user, also of a user without a number; such a delete logs no old value (`sql_creator::sql_func_log_value`), so its function has a parameter less, but a connection creates a function only once by its name (`sql_db::has_query`), so the call found only the function of a delete with a number. In production each request has its own connection, where `CREATE OR REPLACE FUNCTION` adds the other variant as an overload; no sql test file has a delete without a number, so no expected file changes |
| `src/main/php/web/formula/formula_calc.php` (`with_numbers`, new `is_open_copy`) | every result without a number is a missing operand | only a result without a number and without a database id, which is a copy that is not calculated yet | the next run failed "show formula view" of `views_by_id/formula` 8, 28, 36 and 44: the results of the potential loss of the global problems lost the tooltip with their numbers ("5.5" = 110.05 * 5 %) and got the mark of an assumption, because one of their operands matches a stored result without a number, which counted as 0 before; the rule of the copies reached every server page. With the auto update the run wrote the four snapshots without the tooltip, so they need a restore by the developer or the next run. The same run, which started at about 16:53 with the code before this fix of 17:12, also failed "api-> view Calculator" of the PV lifetime balance without javascript after 343 seconds with a page without its main part, and so `pv_lifetime_balance_no_js.html` and `pv_lifetime_balance_diff.md` (65 missing); the page has no repeated input, so only this rule of `formula_calc` can have changed it, most likely by the missing operands that `check_results` then loads one by one |
| `src/main/php/web/component/execute/ui_list.php` (`calculate_copies`, new `fresh_operands`, `calculated_in_rows`, `with_stored_numbers`) | the copies are calculated with the stored numbers of the page | with the fresh numbers of the stored results, in a list of their own for the copies, so that the page shows the stored numbers | the next run had 27 of 217 different from SCHP on (420 / 421). The read-only scratch scripts `walk_php.php` and `walk_js.js` give the same walk of the second child in php and in javascript with the stored numbers (SCHP target quantity 420.49999999999977, 2.3e-13 below the tie, so 421), so the engines are equal; the script of the page and the backend calculate with the fresh numbers of the stored results, e.g. the holding value of GBP cash 225999.99999999997 instead of 226000, whose float noise brings the tie 7e-13 below 420.5, which rounds down as the sample and decision 1 of 2026-11-25 keep it. Decision of the developer (2026-11-27): fresh operands. A stored result that the page cannot calculate keeps its stored number, like in the script. With only the three fresh holding values of GBP cash, DKK cash and ConfedBond30, `walk_php_fresh.php` gives the numbers of the script and of the stored second child (SCHP 420, SIKA 456). The fresh calculation runs only for a page with copies, but there it calculates every stored result again, so the page without javascript and the pdf of such a page take longer |
| `src/main/php/web/component/execute/ui_list.php` (`calculated_in_rows`) | the results without a row only after the last row | the results without a row also before the first row | the next run took 513 seconds for "a child without stored results gets the results of the first child": with the fresh operands the count of the children ("child", "count") is a result without a number and without a row, so it was calculated only after the last row, and every split count, which uses it, and with it the whole walk stayed open and was tried again in each of the 42 rows, each time with a scan of the whole list per operand. Decision of the developer (2026-11-27): calculate the results without a row once at the start too; the numbers do not change, only the order |
| `src/test/resources/web/html/workflow_write/` (`wf4_edit_back`, `wf4_edit_back_edit_save_cancel`, `wf6_show_edit_back`) | the tooltips of the potential loss of the global problems | without them | not by a change of its own: the run that started at about 16:53 still had the rule of every result without a number (row of `is_open_copy` above) and wrote these three pages between 17:49 and 17:50 with the auto update; they need a restore or the next run |
| 34 snapshots of `src/test/resources/web/html/workflow_write/` (wf1, wf2, wf3, wf4, wf5, wf6, wf17, wf18, wf42, wf44, wf45, wf47) and `object_pages/triple.html` | — | five more suggestions "Hanseatic city", "big city", "college town", "urban district of Bremen" and "urban municipality in Germany" | not by this change: `object_pages/triple.html` has them since the run of 14:22, before the changes of the calculation, and no code of this section creates a suggestion, so most likely a newer answer of wikidata (the cache of `Q72.json` changed too); to check by the developer whether the snapshots should keep them |
| `src/main/php/web/component/execute/ui_list.php` (`calculator_numbers`, new `STEP_CALC_CHECK` and `STEP_CALC_COPIES`, `check_results`, `calculate_copies`) | the calculation of the results of a page is part of the section of the caller, e.g. "not_specified" with 513 seconds | the two steps "calculator result check" and "calculator copies" in the url to html time of a page (`ui_list::html_step`, like `STEP_TABLE_CHECK`), a debug entry of `calculate_copies` with the number of the copies, the fresh results, the rounds and the open copies, and one of `check_results` with the size of the table | request of 2026-11-27: more entries, so that a delay can be checked in detail. First planned as two sections of `system_time_type` with `$sys->times`, but the coding rule test "web/ must declare only $ui_sys and $mtr as globals" failed for `ui_list.php`, so the steps use the frontend cache like the other steps of the page; the two sections and their rows in `system_time_types.csv` are removed again |

| `src/main/php/web/component/execute/ui_list.php` (`fresh_operands`, `with_stored_numbers`, `calculate_copies`), `src/main/php/web/formula/formula_calc.php` (`is_same_result`, `is_open_copy`, new `has_db_id`) | a fresh result that falls back to its stored number keeps the id 0; a stale comment names the removed section calc_copies; the check of a database id twice | the fallback gets the database id back with the stored number; the comment names the step "calculator copies"; the check in `has_db_id` | the findings of the review of 2026-11-27: with the id 0, a stored result without a number stayed a missing operand (`is_open_copy`) on a page with copies, while it is the number 0 on a page without copies |

The runs of 2026-11-27 since the fixes of the copies:
- the run that started at about 16:53 (with the code before 17:12): the formula pages, the PV page without javascript
  and the three workflow pages of the rows above, and the 18 internal errors of the delete without a number
- the run that started at about 17:53 (with the code of 17:12): `views_by_id/formula` 8, 28, 36 and 44 (17:58) and
  `pv_lifetime_balance_no_js.html` and `pv_lifetime_balance_diff.md` (18:12) are written like the commit again, so
  the narrowed rule of `is_open_copy` is confirmed; "a child without stored results gets the results of the first
  child" took 513 seconds, and the copies differed from SCHP on in 27 numbers. The fresh operands and the results
  without a row before the first row wait for the next run
| file | old | new | reason |
|---|---|---|---|
| `docs/code_test_coverage.md` | the new functions | `repeated_phrases` with 0 unit test calls; the private functions of `ui_list` are not listed | `repeated_phrases` is called only by `ui_list::add_instance_results` and `calculator::is_repeated`, which the unit tests reach through the page, but not directly |
| `docs/code_functions_all.md` | the new functions | `repeated_phrases` "in a section not yet defined" (the report suggests `texts and flags`) | the report has no section rule for the new function yet; not changed, a generated report is a signal |

The runs of 2026-11-27 covered this change in three steps, and each failure above is the reason of its row:
- the run that started at about 07:25 (the `docs/code_*` reports of 07:25): the missing German text, and 45 of 217
  copies calculated in 64 seconds; it ran with the code before the fix of the html info of section 8, so its failure
  of that test is not a failure of the fix
- the run that started at 08:06: every copy calculated, but 81 of 217 different from SCHP on, the history of the
  test values and the five `workflow_write` snapshots, and the ref snapshot of section 8
- the fixes of the copies added first (08:59) and of the copy of the group in the value cleanup (09:05) wait for the
  next run, which should also remove the history of the earlier runs at its start; the `phrase_data_ui_tests`
  results, the browser test of the javascript page and the case reports are in its output

### Lessons learned by llm (2026-11-27)

- A calculation that adds a result to its numbers only once it is calculated lets an operand take another number
  in the meantime, e.g. a sum that names the same phrases; every result that is still to be calculated belongs in
  the list from the start, as a missing operand while it has no number.
- A result that is excluded as its own operand by its id excludes every other object without an id too; a copy
  without a database id is compared as the object.
- An object that a load hands to another object (`load_by_grp` and `set_grp`) is changed by the reset and the
  delete of that object; a cleanup that needs the object afterwards passes a copy.
- A pasted failure can come from a run that has started before the fix; the write time of the `docs/code_*` reports
  tells when the run started.

## 10. the first portfolio on the first page of the pdf report

Part of #247, the task "first page of PDF report" of `docs/llm/pending.md`.

### Request

"do the next open task in pending.md 'portfolio split' and remove the task done already": the tasks 6a and 6b of
"report with three and more children" are done and wait for a run, so the next open task is "first page of PDF
report": "The first page of the PDF report contains only the headline e.g. 'Portfolio Split Calculator'. It should
also show the first child on same page, so remove the 'next page' between 'Portfolio Split Calculator' and First
child portfolio."

Checked before the first edit:
- `calculator_report::pdf` writes the logo and the title on the first page and then `add_positions`, which starts
  each table of the positions of a portfolio with `pdf_writer::page_break(true)`; the page break starts a new page if
  the page has content, so also for the first table, because the title is on the page
- the report is created as portrait (`new pdf_writer($msg, true)`), so the first table is on a portrait page also
  without the break
- a table that is longer than the rest of the page goes on to the next page by itself (`pdf_writer::table`)

Planned:
- `calculator_report::add_positions` starts a new page only for the second and every further table of the positions,
  so the first portfolio follows the title on the first page, and every other portfolio stays on a portrait page of
  its own
- in `phrase_data_ui_tests.php` (calculator report): the first portfolio is on the page of the title (positive), and
  the existing tests keep the second portfolio off that page (negative) and each portfolio on a portrait page

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/web/component/execute/calculator_report.php` (`add_positions`) | a page break before each table of the positions | a page break before the second and the later tables only | the task of the developer |
| `src/test/php/unit_ui/phrase_data_ui_tests.php` (calculator report) | — | the first portfolio on the page of the title | the test of the change |
| `docs/llm/pending.md` | the task "first page of PDF report" | removed as done, waiting for the run | the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the pdf reports of the tests | — | one page less | the title and the first portfolio share a page; a pdf is not a snapshot of the tests, so no file changes |
| the snapshots and the case reports | — | unchanged | the html of the pages is not changed |
| the ids | — | unchanged | no import file changes, so `test/test_part.php` without `test/reset_db.php` |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|

### Lessons learned by llm (2026-11-27)

## 11. the limits of the input item

Part of #247, the task "range of FX rates" of `docs/llm/pending.md` (portfolio split page compared with its sample):
"remove the limits on FX rates so that all FX rates could be save and add a separate button to save FX rates."
Three changes (sections 11 to 13), each with a run before the next one.

### Request

The next open task of `docs/llm/pending.md` "portfolio split".

Checked before the first edit:
- the page shows one field "Exchange rate" with the DKK rate 0.1304 and `min="0.6" max="1.1" step="0.01"`, in the
  page without javascript and in the script; a number below its minimum makes the whole calculator form invalid, so
  the browser refuses every save of the page, also of the target shares
- the limits are the values `[exchange rate, field limit down] = 0.6`, `[exchange rate, field limit up] = 1.1` and
  `[exchange rate, field step] = 0.01` of `share_buy_sell_abb.json`, meant for the USD/CHF rate of the ABB page, but
  set on the shared phrase "exchange rate", so every page with that phrase gets them; both renderers read a limit only
  by the phrase (`ui_list::field_property`, `calculator_json.js fieldProperty`)
- the hand made sample has the one field and a box with an input for each currency (EUR, USD, GBP, JPY, DKK, SEK),
  `step="any"`, and an "Import FX rates" button

Decisions of the developer (2026-11-27):
1. limits per input item: a limit of the input item comes before the limit of the phrase, and the ABB use case moves
   its three limits to its item "ABB share decision assumption exchange rate", so the ABB page keeps its range and
   the portfolio split page has none (this section)
2. one field per rate: the exchange rate assumption shows a number field for each value of the phrase, named by its
   unit e.g. "CHF per EUR", like the box of the sample (section 12)
3. a button "Save FX rates" below the rates saves only the rates, and the main "Save" saves the inputs without them
   (section 13)

Planned before the first edit:
- `ui_list::calculator_number_field` reads each limit and the step by the new `item_property`: the value of the input
  item and the property first, else the value of the field phrase and the property (`field_property`)
- `calculator_json.js numberField` the same by the new `itemProperty`
- `share_buy_sell_abb.json`: the three values of the limits named by the item "ABB share decision assumption exchange
  rate" instead of the phrase "exchange rate"
- tests: in `phrase_data_ui_tests.php` with the seed of the javascript snapshot and limit values of the test, the limit
  of an input item before the limit of its phrase (positive) and the limit of the phrase for an item without its own
  (negative); the read-only scratch simulation `sim_fx2.js` draws the rate without limits and the target shares of
  the children with their limits 0 to 100
- not in this change: the fields of the other rates (section 12) and the button (section 13)

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/web/component/execute/ui_list.php` (`calculator_number_field`, new `item_property`), `src/main/resources/script/calculator_json.js` (`numberField`, new `itemProperty`) | the limits of a field by its phrase | by the input item first, then by its phrase | decision 1 |
| `src/main/resources/messages/use_cases/share_buy_sell_abb.json` | the limits of the shared phrase "exchange rate" | the limits of the ABB item | decision 1 |
| `src/test/php/unit_ui/phrase_data_ui_tests.php` (new `assumption_fields`), `src/test/php/const/triple_names.php` (`PORTFOLIO_EXCHANGE_RATE_ITEM`), `docs/json_structure.md` (field limits), `docs/llm/pending.md` | — | the tests; the rule; change 1 done, waiting for the run | the tests and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `portfolio_split_calculator_no_js.html`, `portfolio_split_calculator_js.html` (the seed) | `min="0.6" max="1.1" step="0.01"` of the rate, the three limit values of "exchange rate" in the seed | the rate without limits; the seed without the ABB limits | the limits are of the ABB item now |
| the ABB snapshots | the rate with its limits | unchanged | the limits come from its item |
| the ids, `src/test/resources/unit/value/list.csv` and the snapshots with a value id | — | three values with a new group | the values name the item instead of the phrase: `test/reset_db.php` and `test/test_re_pin.php` before `test/test_part.php` |
| the case reports of the portfolio split page | 0 differences | 0 differences | the sample has no limits for the rate |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/test/resources/unit/value/list.csv` | — | no row of the three limits of the ABB item | the run of 2026-11-28 after `test/reset_db.php` drew the rate of the portfolio split page without limits in both snapshots (`n-392` without `min`, `max` and `step`) and removed the three limit values of "exchange rate" from the seed, as expected; but the fixed value list got only the 9 values of the csv definition of section 3 of `2026-11-26_csv_format_phrases.md`, and neither lost the rows of the old group nor got the new one, so the limit values are not part of that list; the cause is not checked yet |
| `src/main/php/web/component/execute/ui_list.php`, `src/main/php/cfg/import/convert_csv.php`, `src/main/php/cfg/export/phrase_data.php` | — | — | the review of 2026-11-28 after the run found no bug in this change; its small fixes are in the rows of section 3 of `2026-11-26_csv_format_phrases.md` |

### Lessons learned by llm (2026-11-27)

- A row in "Changes that the expected changes might cause" for a generated list needs a look at which objects the
  list holds, before expecting a row of a moved value there (2026-11-28).

## 12. one field per exchange rate

Part of #247, decision 2 of section 11 of the task "range of FX rates" of `docs/llm/pending.md`.

### Request

The next open task of `docs/llm/pending.md`, after the run of section 11 of 2026-11-28.

Checked before the first edit:
- the item "portfolio split assumption exchange rate" shows one field `n-<phrase id of exchange rate>` with the DKK
  rate, because the six values `[exchange rate, CHF per EUR]` ... `[exchange rate, CHF per SEK]` rank the same in
  `ui_list::calculator_value` and `calculator_json.js fieldEntry`, and the preferred order picks CHF per DKK
- the save reads every field by its key (`frontend::calculator_changes`), so a field keyed by the value id needs no
  change of the save; the assumption fields of other pages are keyed by the value id already
  (`ui_list::assumption_field`)

Decisions of the developer (2026-11-28):
1. all exchange rates are shown, without a new keyword: the values of a field phrase that rank the same and differ
   only by their unit get one field each, keyed by the value id
2. the label stays "Exchange rate" and the unit e.g. "CHF per EUR" follows each input, like the current field once per
   rate

Planned before the first edit:
- `ui_list`: the new `calculator_unit_values` (the best values of `calculator_value`, moved to the new
  `calculator_best_values`, if they are more than one and differ only by their unit, else none) and
  `calculator_number_fields` (one `calculator_number` per unit value, else the one field); `calculator_number` gets
  the unit value as an optional parameter and keys the field by its id; the same in `calculator_field_plan` and
  `calculator_source_ids`, so the page of calculator.js and the source numbers follow the fields
- `calculator_json.js`: the same by `bestEntries`, `unitEntries` and `numberFields`; `numberField` takes the unit
  entry; a unit field is no field of a preset (`inputPhrases`), because a preset selects by the field phrase only
- tests in `phrase_data_ui_tests.php` with the seed of the javascript snapshot: a field per exchange rate (positive)
  and one field if a value of the same rank differs by another phrase e.g. `[exchange rate, million]` (negative)
- not in this change: the button "Save FX rates" (section 13)

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/web/component/execute/ui_list.php` (`calculator_fields`, `calculator_number`, `calculator_value`, `calculator_field_plan`, `calculator_source_ids`, new `calculator_best_values`, `calculator_unit_values`, `differ_only_by_unit`, `calculator_number_fields`) | one field per item | one field per value that differs only by its unit | decision 1 and 2 |
| `src/main/resources/script/calculator_json.js` (`fields`, `numberField`, `fieldEntry`, `sourceNames`, new `bestEntries`, `unitEntries`, `numberFields`) | one field per item | the same as the page without javascript | decision 1 and 2 |
| `src/test/php/unit_ui/phrase_data_ui_tests.php`, `docs/json_structure.md` (the fields of a calculator), `docs/llm/pending.md` | — | the tests; the rule; change 2 done, waiting for the run | the tests and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `portfolio_split_calculator_no_js.html`, `portfolio_split_calculator_js.html` | one field `n-392` "Exchange rate" with CHF per DKK | six fields `n-<group id>` "Exchange rate" in the preferred order of `sandbox_value::sorted` (CHF per DKK first, as today), each with its tag and hint | decision 1 and 2 |
| the case reports of the portfolio split page | 0 differences | 0 differences, or the fields of the sample box as a difference | the sample has the one field and a box with the currencies |
| the snapshots of the other calculator pages | — | unchanged | no other item has values of the same rank that differ only by their unit; a changed page shows such an item |
| the ids | — | unchanged | no import file changes, so no re-pin |

### Unexpected changes

### Lessons learned by llm (2026-11-28)

## 13. the button "Save FX rates"

Planned for after the run of section 12, decision 3.

## 14. the button "Copy as CSV"

Part of #247, step 7 of `docs/llm/pending.md` (portfolio split page compared with its sample): "the buttons
'Download', 'Import', 'Import FX rates', 'Copy as zukunft.com JSON' and 'Copy as CSV' have no function: each function
is a task of its own".

### Request

The next open task of `docs/llm/pending.md` that does not wait for a run (2026-11-28).

Checked before the first edit:
- the triple from the detail table "split walk" to `calculator copy` shows the two plain buttons "Copy as zukunft.com
  JSON" and "Copy as CSV" below the walk, without a function, on the page without javascript
  (`ui_list::calculator_buttons`) and on the javascript page (`calculator_json.js buttons`)
- the sample copies the walk to the clipboard and shows the text selected in a text box if copying is blocked; its
  csv has `;` between the cells and the numbers without format

Decisions of the developer (2026-11-28):
1. the button "Copy as CSV" first; "Copy as zukunft.com JSON" stays without a function
2. the csv is the table as the page shows it, with the numbers without format e.g. 8999999.9 and `;` between the
   cells; without javascript the button opens a collapsed box with the csv text to select

Planned before the first edit:
- `ui_list`: the new `calculation_detail_csv` (the head and the rows of `calculation_detail_table` as csv, the rows by
  the new `calculator_detail_rows`, which `calculation_detail_table` uses too), `calculator_csv_line` (one line, a
  cell with `;`, `"` or a line break quoted with doubled `"`) and `calculator_copy_box`; `calculator_buttons` takes
  the csv and shows the box instead of the plain button; a number is written by `html_base::number_value` (at most
  12 decimals, the noise limit of the calculator), a share as its stored ratio e.g. 0.35
- `html_base`: the new `text_area_read_only` with the new const `TEXTAREA`, which escapes its text once
- `styles::CALC_COPY` as the marker of the box, which `calculator_report::SKIPPED_CLASSES` leaves out of the pdf;
  the width by bootstrap `w-100`, so no new css rule
- `calculator_json.js`: the same box by `detailCsv` and `csvLine` from the numbers that the page shows after an input
  change; opening the box selects the text and copies it to the clipboard (`toggleCopy`), so a blocked copy leaves the
  selected text to copy by hand
- tests in `phrase_data_ui_tests.php`: the csv line of plain cells and of a cell with `;` and `"` (positive and
  negative), the box below the walk with its head line and a number without format (positive) and no box below a
  table without `calculator copy` e.g. the holdings (negative)
- not in this change: "Copy as zukunft.com JSON"; the summary of the box looks like a link and not like a button

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/web/component/execute/ui_list.php` (`calculation_detail_tables`, `calculation_detail_table`, `calculator_buttons`, new `calculator_detail_rows`, `calculation_detail_csv`, `calculator_csv_line`, `calculator_csv_number`, `calculator_copy_box`), `src/main/php/web/html/html_base.php` (new `TEXTAREA`, `text_area_read_only`), `src/main/php/web/html/styles.php` (new `CALC_COPY`), `src/main/php/web/component/execute/calculator_report.php` (`SKIPPED_CLASSES`) | a plain button "Copy as CSV" | a box with the csv of the table | decision 1 and 2 |
| `src/main/resources/script/calculator_json.js` (`detailTables`, `buttons`, new `detailCsv`, `csvLine`, `csvNumber`, `copyBox`, `toggleCopy`) | a plain button | the same box, copied to the clipboard on opening | decision 1 and 2 |
| `src/test/php/unit_ui/phrase_data_ui_tests.php`, `docs/llm/pending.md` | — | the tests; "Copy as CSV" done, waiting for the run | the tests and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `portfolio_split_calculator_no_js.html`, `portfolio_split_calculator_js.html` | the button "Copy as CSV" | the box with the csv of the walk | decision 2 |
| the case reports of the portfolio split page | 0 differences | "Copy as CSV" as a difference, or none | the sample has a button and a hidden text box |
| the pdf report | — | unchanged | the box is skipped like the buttons |
| the ids | — | unchanged | no import file changes, so no re-pin |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/main/php/web/html/html_base.php` (`details`), `src/main/php/web/component/execute/ui_list.php` (`calculator_copy_box`), `src/main/resources/script/calculator_json.js` (`copyBox`), `src/test/php/utils/html_page_diff.php` (`page_items`), `src/test/php/unit_read/system_views_read_tests.php` | the toggle of the csv box as a plain summary | the summary with the button style, which the compare reads as a button | the run of 2026-11-28 reported in every case report of the portfolio split page "Missing in the generated page: button: Copy as CSV" and "Only in the generated page: heading: Copy as CSV", because the compare reads every summary as a heading; a summary with the button style is a button for the reader like the link of the report, which also closes the open point that the toggle looked like a link. The same reports list "Only in the generated page: field: Exchange rate (2)" to "(6)", the consequence of decision 2 of section 12: the sample has the other rates in a box without the class field, which the compare does not read |

### Lessons learned by llm (2026-11-28)

- A planned row "a difference, or none" for a case report needs a look at how the compare reads the new element
  (`html_page_diff::page_items`), before the run shows it (2026-11-28).

## 15. the portfolio currency and the box of the exchange rates

Part of #247, the task "range of FX rates" of `docs/llm/pending.md`, item 4: "now adjust the generated page so that it
matches the sample" (2026-11-28), after the developer has changed the exchange rates of
`portfolio_split_calculator_sample.html`.

### Request

Checked before the first edit:
- the sample starts the exchange rates with the field "Portfolio currency": a selector with CHF selected and the
  currencies of the portfolio as options, by their value in the portfolio, and the hint "per unit of currency, bank
  report [1]"; then the box `fx` with an input per rate labelled by its currency (EUR, USD, GBP, JPY, DKK, SEK), 16px
  below it the button "Import FX rates"; the sample has no field "Exchange rate" any more
- the generated page shows the six fields "Exchange rate" of section 12 and no portfolio currency
- a selector is defined by data like "walk order": `walk order` · `can be` · `phrase selector`, `has a` options, the
  page phrase `uses` the selected option; the options without a follower chain are sorted by their label
- `ui_list::calculator_option_value` falls back to the own value of an option, which is right for an option that is a
  field phrase e.g. residual mix is a replacement mix, but would give a `has a` option e.g. JPY the number of
  `[JPY cash, JPY, original]`; the walk order gets none only because its options have many numbers
- `ui_list::calculator_selector` shows its hint without the `{source:...}` placeholder, unlike the number field and
  the selector of `calculator_json.js`

Decisions of the developer (2026-11-28):
1. a new phrase "portfolio currency" (currency of portfolio) with its own selector field type, so that no other page
   with "currency" changes; a re-pin follows
2. the options in alphabetic order for now; the order by the value of each currency in the portfolio is a task of its
   own (`docs/llm/pending_prio_2.md`)
3. one change for the selector and the box

Planned before the first edit:
- `portfolio_split_calculator.json`, after the triple "portfolio split assumptions label": the triple "portfolio
  currency", its field type, the 7 options `portfolio currency` · `has a` · CHF ... USD, `example portfolio split` ·
  `uses` · CHF, the item "portfolio split assumption portfolio currency" with the hint "per unit of currency, bank
  report {source:bank reporting bank leu client xy}" and the follower triple that puts the exchange rate item behind
  it; the hint "CHF per unit of currency, bank report" of the exchange rate item goes, because its text is now the
  hint of the portfolio currency
- `ui_list`: the values of section 12 are one box `styles::CALC_RATE_BOX` (`fx`) with a label and a number input per
  value instead of a field each (new `calculator_unit_box`, `calculator_unit_box_label`: the to of a unit triple e.g.
  EUR of CHF per EUR, else the unit), the limits of a number input by the new `calculator_number_input` for both,
  and the unit parameter of `calculator_number` goes; `calculator_option_value` takes the own value of an option
  only for the options that are field phrases (new `calculator::options_are_kinds`); the hint of a selector by the
  new `calculator_field_hint`, which the number field uses too
- `calculator_json.js`: the same by `unitBox`, `unitBoxLabel`, `limitAttrs`, `optionsAreKinds` and the parameter of
  `optionEntry`, and `numberField` without its unit entry
- `style_html.css`: the rule `.use-case .fx` of the sample, because no rule shows labels and inputs in two columns
- tests in `phrase_data_ui_tests.php`: the box with a rate labelled by its currency (positive) and one field for
  values that differ by another phrase (negative); the selector with CHF selected (positive) and an option without
  the number of its cash e.g. JPY (negative)
- not in this change: the order of the options by their value; the function of the selector; the step of a number
  input without a step (`html_base::input_number` writes none, so the browser refuses e.g. 0.13 for a field that
  shows 0.1304)

#### Follow-up request of 2026-11-28

"add the pinned view description to this re-pin": the task "the description of a pinned view" of
`docs/llm/pending.md`, which waits for the next re-pin. Checked before the edit: a text value is a `text` column in
postgres and mysql, so the 450 chars fit; the yaml import makes the key "description" a word with a text value like
"tooltip", only `tooltip-comment` is a description of the phrase before it. The start page still shows only the url,
the view name, the name and the tooltip of a pinned view (`html_base::start_page`); showing the description is a task
of its own.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/portfolio_split_calculator.json` | no portfolio currency; the hint of the exchange rate item | the portfolio currency with its selector, options, item and hint | decision 1 and 2 |
| `src/main/php/web/component/execute/ui_list.php`, `src/main/php/web/component/execute/calculator.php`, `src/main/php/web/html/styles.php`, `src/main/resources/script/calculator_json.js`, `src/main/resources/style/style_html.css` | six fields "Exchange rate"; the own value of every option; a selector hint without placeholder | the box of the rates; the own value only of an option that is a field phrase; the placeholder in the selector hint | the sample |
| `src/test/php/unit_ui/phrase_data_ui_tests.php`, `docs/json_structure.md`, `docs/llm/pending.md`, `docs/llm/pending_prio_2.md`; for the follow-up `src/main/resources/config.yaml` (the key `description` of the pinned view "problems") and `src/main/php/shared/const/words.php` (`DESCRIPTION`) | — | the tests; the rules; item 4 done, waiting for the run; the order by value; the description of the pinned view | the tests and the task lists; the follow-up request |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the ids of every word after the config import, the `list.csv` files of the words, triples and values, and the snapshots with such an id | — | shifted by one word: the config import makes the key "description" a word before `csv_keywords.json` declares it; the config has one text value more | the follow-up; the same re-pin |
| the ids of every seed object after "portfolio split assumptions label" of `portfolio_split_calculator.json`, the `list.csv` files and the snapshots with an id | — | shifted by the new triples and the removed hint | `test/reset_db.php` and `test/test_re_pin.php` before `test/test_part.php` |
| `portfolio_split_calculator_no_js.html`, `portfolio_split_calculator_js.html` | six fields "Exchange rate" | the field "Portfolio currency" and the box of the rates | the sample |
| the case reports of the portfolio split page | five "Exchange rate" fields only in the generated page | 0 differences | the compare reads the selected option and the hint, not the order of the options and not the box |
| the snapshots of the PV pages with the selectors yield factor and replacement mix | — | unchanged | their options keep their numbers: by the value of field and option, or as kinds by their own value |

### Unexpected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/web/component/execute/calculator_report.php` (new `add_rate_box`, `add_node`), `src/test/php/unit_ui/phrase_data_ui_tests.php` (the report and the definition report tests) | — | a line per rate of the box e.g. "EUR 0.98 CHF per EUR" in the definition pdf of the calculator editor; the page report without it | named in neither table; the run of 2026-11-28 reported "... each rate of the box of the exchange rates" as failed, because the test read the report of the page, which shows the results without the assumption fields (`calculator_report::pdf`); only the definition pdf renders them (`calculator_definition_report::pdf` by `system_page::calculator_input`), so the positive test reads that one and the page report keeps the negative |

### Lessons learned by llm (2026-11-28)

- Before a test of a pdf part, follow which html the pdf renders: the page report and the definition report share the
  html reader (`calculator_report::add_html`), but not the parts of the page that they hand to it.
