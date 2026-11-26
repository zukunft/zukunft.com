# portfolio csv, child cases and target share instances (2026-11-25)

Three changes of 2026-11-25 of the portfolio split page in `docs/llm/pending.md`, each with its own plan: the import
json from the csv of a bank statement ("import"), the input cases that add a child and remove one (step 2) and the
target shares by the instances of `child portfolio` (step 3, change 1 of 5). Each plan was started before the first
edit of its change, so its tables are the plan; its "Unexpected changes" stay empty until the developer has run the
tests.

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
