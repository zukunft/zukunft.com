# portfolio csv, child cases and the instances of the children (2026-11-25)

Five changes of 2026-11-25 and 2026-11-26 of the portfolio split page in `docs/llm/pending.md`, each with its own
plan: the import json from the csv of a bank statement ("import"), the input cases that add a child and remove one
(step 2), the target shares by the instances of `child portfolio` (step 3, change 1 of 5), the walk counts by the
instances (step 3, change 3 of 5) and the list rule that the count of the parent of change 3 needs; change 2 of step 3
has its own log (`docs/llm/log/2026-11-26_target_share_spread.md`). Each plan was started before the first edit of
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

### Lessons learned by llm (2026-11-26)

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

| file | old | new | reason |
|---|---|---|---|

### Lessons learned by llm (2026-11-26)
