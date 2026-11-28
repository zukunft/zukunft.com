# csv import defined by phrases (2026-11-26)

Part of #247, the task "csv import defined by phrases" of `docs/llm/pending.md`: `convert_portfolio_csv.php` is
replaced by a general `convert_csv.php` whose parameters are words, triples and values. Three changes, each with its
own section and a run before the next one; this log has change 1, the keywords and the definition json, and
change 2, `convert_csv`.

## 1. the keywords and the definition json

### Request

"do the next open task in pending.md": step 3 change 6 of the portfolio split page waits for the run of change 5,
so the developer chose the phase 1 task "import", the TODO of the developer in `convert_portfolio_csv.php` "should be
based on phrases without specific code".

Decisions of the developer (2026-11-26):
1. the csv keywords are a system file with code ids, like `calculator_keywords.json`: `csv_keywords.json` at the end
   of `files::SYSTEM_DATA_APPENDED_FILES`, each keyword a const with its code id in `triples.php`; this shifts the
   ids of the use case files that are imported after it, so `test/reset_db.php` and `test/test_re_pin.php` before
   `test/test_part.php`
2. the definition json of the format of the bank statement is a test resource first
   (`src/test/resources/import/portfolio_csv/bank_position_csv.json`), not imported into the database; a production
   import file follows when `convert_csv` replaces `convert_portfolio_csv` (change 3)
3. a cell text of the bank (a header, an asset class) is the description of a triple, compared with the complete
   cell, like a calculator label, so that the phrase names stay lower case

Planned before the first edit:
- the patterns of the calculator keywords: a property or a flag is a triple from an object to a keyword, a list is a
  group triple to a keyword whose items are the triples from the group; the columns and the kinds are lists, because
  a triple per column from the format to the same keyword would repeat its from, verb and to, which an import refuses
- the keywords `csv header start`, `csv column`, `csv required`, `csv number`, `csv positive`, `csv fallback`, `csv
  row name`, `csv row description`, `csv kind`, `csv context` and `csv optional`, each with the rule in its
  description, and `csv (system)` with the rules of the templates
- a value of a definition is a template: per row each column item is the phrase of its cell, the number column times
  the number of the template is the number (a scale e.g. 0.01 for the price of a bond in percent), a template without
  a number column writes its own number (a default e.g. the lot size), a kind phrase limits the template to the rows
  of that kind, and a template without the row name column is written once per phrases (the exchange rates); no
  new placeholder syntax is needed for the phrases of a value, because a column is a phrase
- a triple of a definition from or to a column item is a template too: the hint of a holding, the part of the
  portfolio by `csv context` and the currency pair from the reference currency column per the price currency column;
  its description names a cell by `{<header text>}`, the one new syntax, which gets its rule in `docs/json_structure.md`
- the asset class "Alternative investments" maps to the new triple `alternative investment` `is a` `stock` and not to
  `stock` directly, because two items of a list from the same group to the same phrase would repeat a key
- not defined by phrases, as `docs/llm/pending.md` says: the walk by the lot value and the bond fund priced per unit;
  change 2 decides on them

#### Follow-up request of 2026-11-26

"do" findings 2 to 4 of the review after the run:
- Finding 2: the negative test packs a filter with a closure into one line, and it pins the silent fallback of the
  importer (an undeclared keyword becomes a word without a message) instead of a reported outcome.
- Finding 3: the rule "a template whose name starts with the name of its from column gets the row cell" compares a
  prefix, which rule 3 forbids.
- Finding 4: `docs/llm/pending.md` still calls change 5 of step 3 and this change "waiting for the run".

Decisions of the developer (2026-11-26):
1. the name of a written triple is marked explicitly by a 12th keyword `csv name`: the triple from a triple template
   to it has the name of the written triple as its description, with the existing placeholder, e.g. `{Instrument}
   hint`; a template without it writes no name
2. the negative test moves to change 2, where `convert_csv` reports a definition without the keyword `csv column`
   with a `msg_id`

Planned: the triple `csv name` appended at the end of `csv_keywords.json` with its const and code id, so that only
the ids after the system file shift by one more (again `test/reset_db.php` and `test/test_re_pin.php`); in
`bank_position_csv.json` the keyword repeated and the triple `bank position csv instrument hint name`; the prefix
rule replaced in the description of `csv (system)` and in `docs/json_structure.md`; the negative test removed from
`import_tests.php` and planned in change 2; `docs/llm/pending.md` without the done change 5.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/csv_keywords.json` (new), `src/main/php/cfg/const/files.php` (`CSV_KEYWORDS_FILE`, `SYSTEM_DATA_APPENDED_FILES`), `src/main/php/shared/const/triples.php` (`CSV_*` consts, `CSV_KEYWORDS`) | — | the keywords with their code ids and rules, imported as the last appended system file | decision 1 |
| `src/test/resources/import/portfolio_csv/bank_position_csv.json` (new), `src/test/php/const/files.php` (`IMPORT_PORTFOLIO_CSV_FORMAT`), `src/test/php/unit/import_tests.php` | — | the format of the bank statement of the positions as phrases and value templates; the test that `import::get_data_object` reads it without a message and with its columns, and the negative test without the declaration of the keyword `csv column` | decisions 2 and 3 |
| `docs/json_structure.md`, `docs/llm/pending.md` | — | the section of a csv format with the template rules and the `{<header text>}` syntax; change 1 done, waiting for the run | the description and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the ids and `src/test/resources/unit/*/list.csv` | — | — | the new words and triples of `csv_keywords.json` shift the ids of the use case files imported after it (`files::USE_CASE_FILES_APPENDED`) and of the test data: `test/reset_db.php` and `test/test_re_pin.php` before `test/test_part.php` |
| the snapshots with an id of a use case phrase, `index.html`, `portfolio_split` and the other pinned links | — | — | the id shift |
| `docs/json_findings.md` | — | — | the json validation reads the new system file; a finding is a reason to change the file |
| the word `csv` | home `table_editor_sample.json` | home `csv_keywords.json` | it is imported earlier now; the same description, so the import of `table_editor_sample.json` changes nothing |

### Unexpected changes

The run of 2026-11-26 (`test/reset_db.php`, `test/test_re_pin.php`, `test/test_part.php`) covered this change
together with change 5 of step 3 of the portfolio split page (section 7 of
`docs/llm/log/2026-11-25_csv_child_shares.md`) and with `files::AUTO_UPDATE_TEST_FILES` set to `true`. So the
snapshots were overwritten instead of failing, and this table compares the regenerated files with the plan; whether
the new tests of `import_tests.php` passed is in the output of the run, not in a file.

As planned:
- The 15 new triples shift the triple ids of the later files by 15; `zukunft.com system` is repeated verbatim and
  gets no new id.
- The word `csv` moves to its new home with the same description.
- `docs/json_findings.md` has no new finding.
- The table editor snapshots got no csv keyword (only shifted ids), so the phrase data walk does not reach the
  keywords from the word `csv`, and `triples::CSV_KEYWORDS` needs no place in `phrase_data::keyword_ids`.

| file | old | new | reason |
|---|---|---|---|
| `src/test/resources/unit/word/list.csv` and the snapshots with a word id | the new words shift the ids | three new words (`description`, `fallback`, `kind`); the 13 other words of the file exist already, and two of them get a new id: `csv` from 2881 to 2445 and `required` from 2799 to 2446. So the words of the later files shift by five up to the old `required`, then by four up to the old `csv`, then by three | the id of a word is the position of its first import, so a word that a later file declares first moves to the new system file; a word that an earlier file declares keeps its id |
| `src/test/php/const/triple_names.php` | — | `SHARE_RATIO_ID` 3789 → 3804, `SHARE_SYMBOL_ID` 3841 → 3856 | `test/test_re_pin.php` re-pinned the two consts of `pv_lifetime_balance.json` by the shift of 15 |
| `src/test/resources/web/html/use_cases/table_editor.html` | the word `csv`: the import of `table_editor_sample.json` changes nothing | the seed json of the page lists `csv` 22 entries earlier | the seed lists the words by their id, and `csv` has a lower id now |
| `docs/llm/pending.md`, `docs/llm/log/2026-11-25_csv_child_shares.md` | — | change 5 of step 3 and its log | not by this change: change 5 was in the same run and section 7 of its log has its results |
| `src/main/resources/messages/csv_keywords.json`, `src/main/php/shared/const/triples.php` (`CSV_NAME`, `CSV_KEYWORDS`) | the name of a written triple by the prefix of the template name | the 12th keyword `csv name` at the end of the file, and the description of `csv (system)` names it instead of the prefix rule | follow-up of 2026-11-26, finding 3 and decision 1; expected after the next run: the triple ids after the system file shift by one more (`test/reset_db.php`, `test/test_re_pin.php`), the word `name` exists already |
| `src/test/resources/import/portfolio_csv/bank_position_csv.json` | the hint template named by the prefix | the keyword `csv name` repeated and the triple `bank position csv instrument hint name` with the description `{Instrument} hint` | follow-up, decision 1; a test resource, so no id shift |
| `docs/json_structure.md` *csv format*, `docs/llm/json_structure.md` | the prefix rule | the keyword row `name` and the template rule by `csv name` | follow-up, decision 1 |
| `src/test/php/unit/import_tests.php` | the negative test without the declaration of `csv column` | removed, with a comment that it comes with `convert_csv` | follow-up, finding 2 and decision 2: it packed a filter into one line and pinned the silent fallback of the importer |
| `docs/llm/pending.md` | change 5 of step 3 and this change waiting for the run | change 5 removed as done; this change done with its follow-up waiting for the run; the negative test planned in change 2 | follow-up, finding 4 |

The second run of 2026-11-26 (`test/reset_db.php`, `test/test_re_pin.php`, `test/test_part.php`, again with
`files::AUTO_UPDATE_TEST_FILES` set to `true`) covered the follow-up, together with
`docs/llm/log/2026-11-26_scale_font_icons.md` and the follow-up of section 7 of
`docs/llm/log/2026-11-25_csv_child_shares.md`. As the follow-up rows expect:
- `csv name` is triple 3759, and the triples after the system file shift by one more. Examples: the pinned links of
  `index.html`, `pv`, `nnp` and `portfolio_split`; `SHARE_RATIO_ID` 3804 → 3805 and `SHARE_SYMBOL_ID` 3856 → 3857,
  re-pinned in `triple_names.php`; the api fixtures `type_lists.json`, `ui_config.json` and
  `phrase_list_without_link_by_verb_id.json`.
- `src/test/resources/unit/word/list.csv` did not change, because the word `name` exists already.
- `docs/json_findings.md` has no new finding.

Whether the tests of `bank_position_csv.json` passed with the triple to `csv name` is in the output of the run. The
shift of the three hint triples of the portfolio split page is in the log of that change. No further unexpected
change.

### Lessons learned by llm (2026-11-26)

- A new system file moves the id of every word that it declares before a later file does, also of an existing word
  (`required`); before planning the id shift, check which words of the new file are declared already, and in which
  file.
- A list sorted by id changes its order when an id moves, so a "nothing changes" for a moved word holds for its data,
  not for its position in a snapshot.
- With `files::AUTO_UPDATE_TEST_FILES` set to `true`, a run writes the snapshots instead of failing, so the log can
  confirm only what the files show; the result of a unit test needs the output of the run.

## 2. convert_csv

### Request

"do the next open task in pending.md and remove the task done already": change 1 and its follow-up `csv name` are
done with the run of 2026-11-26, so the next task is change 2, `convert_csv`.

Decisions of the developer (2026-11-26):
1. the walk: `convert_csv` writes no `is follower of` chain. The lot value order is a calculation and not a fact of
   the csv: the csv order matches it in only 11 of 42 places of `bank_positions_portfolio_split.csv` and in none of
   `bank_positions_sample.csv`. The walk of an imported portfolio by the lot value becomes a task of its own.
2. the bond fund `LU0854923223`: the asset class of the bank is kept, so it is a bond. The flag of a holding whose
   holding value differs from its `holding value, expected` becomes a task of its own, a formula of the page.
3. `convert_csv` reads the definition as the decoded import json, the array that `import::get_data_object` takes,
   so that the declarations are copied verbatim; change 3 gets this array from the database by the export of the
   format phrase.

Planned before the first edit:
- `convert_csv::convert($fmt, $format, $csv, $context, $source, $msg, $timestamp)`: the format is the phrase of
  the definition, e.g. `bank position csv`, whose triples to `csv header start` and `csv column` are found by the
  keyword names of `triples::CSV_*`; a definition without one of them is reported with the new
  `import_csv_format_keyword_missing`, and a value template with more than one number column with the new
  `import_csv_template_numbers`
- the rows, the checks and the messages as in `convert_portfolio_csv`, but by the columns and the flags of the
  definition: the existing `import_csv_*` messages stay, also their texts about the asset class and the exchange rate,
  because the bank csv is the only format so far
- the templates by the rules of `docs/json_structure.md` *csv format*, with the row word and its `is a` triple to
  its kind; the words and triples that the result uses are copied from the declarations of the definition, also the
  declarations that they use, and every declared triple between two copied phrases, e.g. `stock is a holding`; a
  cell that the definition does not declare is a word with its name only
- in `import_tests.php` the tests of `convert_csv` next to those of `convert_portfolio_csv`, which stays until
  change 3: the shares of a stock, the exchange rate, the currency pair verbatim and with the general description,
  the hint by `csv name`, the definition without `csv column` (moved here from change 1) and the bad rows; and per
  test csv a report of the difference to the json of `convert_portfolio_csv`, compared as sets per section, so that
  each difference is a decision of the developer

### Follow-up request of 2026-11-27

"try to fix" findings 6 and 8 of the review of 2026-11-27:
6. a cell with the flag `csv positive` below zero is reported with the text of the exchange rate: `convert_csv` gets
   the new message `import_csv_cell_not_positive`, which names the cell but no column; `convert_portfolio_csv` keeps
   its message until change 3. This changes the plan above, which kept the texts about the exchange rate
8. the number of a value is cut to 14 significant digits by the php setting `precision`, and not "never rounded":
   the 14 digits stay, because `round()` of php 8.4 leaves the noise of the float in the 16th digit (e.g.
   `451509.3745520001`), but explicitly by `number_text` with at most 10 decimals, so that the json is the same as
   that of `convert_portfolio_csv` and does not depend on the php setting

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/cfg/import/convert_csv.php` (new), `src/main/php/shared/enum/messages.php`, `src/main/resources/translations/en.yaml`, `de.yaml` | — | the conversion of a csv by the phrases of its format, with two messages for a definition that it cannot use | the TODO of the developer in `convert_portfolio_csv.php` |
| `src/test/php/unit/import_tests.php`, `src/test/php/const/files.php` (the two difference reports) | the negative test of the definition planned for this change | the positive and the negative tests of `convert_csv` and the two reports | decisions 1 to 3 |
| `docs/json_structure.md` *csv format*, `docs/llm/pending.md` | the rules of the templates | the copy of the declarations, the walk and the kind of the bank; change 2 done and waiting for the run, the walk and the bond fund as tasks | the description and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/import/portfolio_csv/bank_positions_sample_diff.md`, `bank_positions_portfolio_split_diff.md` (new) | — | the differences: no walk step, the alternative investments `is a alternative investment` instead of `stock`, the bond fund as a bond, the base words by the declarations | written by the run with `files::AUTO_UPDATE_TEST_FILES`; without it the first run fails, because the reports do not exist yet |
| `docs/code_test_coverage.md`, `docs/code_functions_all.md` | — | the functions of `convert_csv` | generated reports |
| the ids, the snapshots | — | unchanged | no import file changes, so `test/test_part.php` without `test/reset_db.php` |

### Unexpected changes

The run of 2026-11-27 (`test/test_part.php`, with `files::AUTO_UPDATE_TEST_FILES` set to `true` and back to
`false` after it) covered this change together with change 6a of step 3 of the portfolio split page (section 8 of
`docs/llm/log/2026-11-25_csv_child_shares.md`). It stopped later with a fatal of `a_selected_test.php`, which
section 8 names, but `import_tests` runs before it, so the two reports were written. Whether the new tests of
`import_tests.php` passed is in the output of the run, not in a file.

As planned:
- `bank_positions_portfolio_split_diff.md` has only the 41 `is follower of` triples of the walk (decision 1).
- `bank_positions_sample_diff.md` has these differences (decision 2 and the copy of the declarations):
  - the 68 triples of the walk
  - the bond fund `LU0854923223` and the alternative investment `CH0047533549` `is a stock` only by
    `convert_portfolio_csv`
  - only by `convert_csv`: the two declared triples of `alternative investment` with their words `investment` and
    `alternative`, `LU0854923223 is a bond (finance)` and `CH0047533549 is a alternative investment`
  - the three values of the bond fund as a bond (face value 934.784 CHF, bond price 1.50269 percent, lot size
    1000 CHF) instead of as a stock. By these, the market value is 1'404.69 CHF instead of the 140'469.06 CHF of the
    bank, which is the case of the flag task in `docs/llm/pending.md`.
- No difference in the selection, the sources and the other words and values.
- The ids and the snapshots did not change by this change.

| file | old | new | reason |
|---|---|---|---|
| `docs/code_objects_all.md` | — | the line of `convert_csv` with its var `$fmt_conv` | the generated object report lists every class; the plan named only the two function reports |
| `docs/code_functions_all.md` | the functions of `convert_csv` | `convert` "in a section not yet defined" (the report suggests `convert`), and as the description of `convert` its first `@param` line | the docblock of `convert` starts with `@param`, so the report has no description line for it; the report has no section rule for the new function yet. Not changed: a generated report is a signal |

Follow-up of 2026-11-27 (findings 6 and 8 of the review), after the run above:

| file | old | new | reason |
|---|---|---|---|
| `src/main/php/cfg/import/convert_csv.php` (`row_is_valid`), `src/main/php/shared/enum/messages.php`, `src/main/resources/translations/en.yaml`, `de.yaml` (`import_csv_cell_not_positive`), `src/test/php/unit/import_tests.php` | a positive cell below zero reported as an exchange rate | the new message names the cell and that it must be above zero | finding 6; the bad row test of the exchange rate keeps its text part, and a new assert checks that the text names no exchange rate |
| `src/main/php/cfg/import/convert_csv.php` (`written_value`, new `number_text`, new `SIGNIFICANT_DIGITS`) | `(string)round($number, 10)` with the comment "never rounded" | 14 significant digits with at most 10 decimals explicitly, without the php setting and without an exponent | finding 8; `sprintf` and not `number_format`, because `number_format` rounds about one of 200 half digits differently. The read-only scratch check `number_text_check.php` gives the same text as `(string)round()` for 309'930 numbers (random ones of every size and the products of the cells of both test csv), apart from the 2'074 that the cast writes with an exponent; the scratch conversion of both test csv gives the same json as before. Expected after the run: the two difference reports unchanged. Also `convert` got the description line that the function report has missed |

The run of 2026-11-27 that started at 08:06 (`test/test_part.php`, with `files::AUTO_UPDATE_TEST_FILES` set to
`true`) covered the follow-up together with sections 8 and 9 of `docs/llm/log/2026-11-25_csv_child_shares.md`. As
the rows expect:
- `bank_positions_sample_diff.md` and `bank_positions_portfolio_split_diff.md` did not change, so the explicit 14
  significant digits write the same numbers as the cast before.
- `convert` has its description line in `docs/code_functions_all.md`.

The pasted output of the run names no failure of the import tests, so whether the new assert of the positive cell
has passed is in the rest of its output. No further unexpected change.

### Lessons learned by llm (2026-11-26)

- A public function gets a description line before its first `@param`, because the function report takes the first
  line of the docblock as the description (2026-11-27).

## 3. the definition as a use case file and without convert_portfolio_csv

### Request

"do the next open task in pending.md 'portfolio split'": the next change of the exchange rates waits for the run of
its first change, so the next open task of phase 1 is "import", change 3 of "csv import defined by phrases".

Checked before the first edit:
- neither `convert_portfolio_csv` nor `convert_csv` is called outside `src/test/php/unit/import_tests.php`, and the
  fixtures of the csv (`bank_positions_sample.json`, `bank_positions_portfolio_split.json`, the two diff reports) are
  read only there
- a new use case file is added at the end of `files::USE_CASE_FILES_APPENDED`, so its phrases get the next free ids
  and no pinned id moves; the definition has no code id, as a use case file must not
- `phrase_data::export_json` gives the data of a phrase in the import format by the walk on its from side; the export
  has the database id of each object, which `convert_csv` copies with a declaration, so only the values of the
  conversions can be compared

Decisions of the developer (2026-11-27):
1. start this change now, in parallel with the run of the first change of the exchange rates (section 11 of
   `docs/llm/log/2026-11-25_csv_child_shares.md`), because both need `test/reset_db.php`
2. `bank_position_csv.json` becomes a use case file, imported at the setup after the other use case files, so that the
   format phrase is in the database; the unit test reads the file, and a db read test converts the sample csv with the
   definition exported from the database
3. the two fixtures become the json of `convert_csv`, written by the next run with the auto update; the two diff
   reports, their helpers and the tests of `convert_portfolio_csv` are removed

Planned before the first edit:
- the definition moves to `src/main/resources/messages/use_cases/bank_position_csv.json` with the new
  `files::BANK_POSITION_CSV_FILE` at the end of `files::USE_CASE_FILES_APPENDED`; `test_files::IMPORT_PORTFOLIO_CSV_FORMAT`
  goes
- `convert_portfolio_csv.php` goes with its tests in `import_tests.php`; the tests of `convert_csv` keep the inputs of
  the old block (the csv, the source, the bad rows, the NOK pair) with the column texts as consts of the test
  instead of the consts of the old class, and compare the json of both test csv with the fixtures instead of the diff
  reports; the import of the json keeps its tests, also "stock price" with and without its declaration; the message
  `import_csv_rate_not_positive`, which only the old class uses, goes with its texts
- `phrase_data_read_tests`: the export of the format phrase "bank position csv", with which `convert_csv` converts
  the sample csv to the values of the conversion with the file (positive); a definition export of another phrase e.g.
  the portfolio split page cannot be used as a format (negative)
- not in this change: a page or an api that converts an uploaded csv (the "Import" button of step 7)

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/resources/messages/use_cases/bank_position_csv.json` (moved from `src/test/resources/import/portfolio_csv/`), `src/main/php/cfg/const/files.php`, `src/test/php/const/files.php` | the definition as a test resource | the definition as the last use case file of the setup | decision 2 |
| `src/main/php/cfg/import/convert_portfolio_csv.php` (removed), `src/test/php/unit/import_tests.php`, `src/test/php/const/files.php`, the two diff reports (removed), `src/main/php/shared/enum/messages.php`, `en.yaml`, `de.yaml`, `src/main/php/cfg/import/convert_csv.php` (the comments) | two converters and the diff of their json | `convert_csv` only, its json compared with the fixtures | decision 3 |
| `src/test/php/unit_read/phrase_data_read_tests.php`, `docs/json_structure.md` (csv format), `docs/llm/pending.md` | — | the conversion with the exported definition; the home of the definition; change 3 done, waiting for the run | decision 2, the docs and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/import/portfolio_csv/bank_positions_sample.json`, `bank_positions_portfolio_split.json` | the json of `convert_portfolio_csv` | the json of `convert_csv` | written by the run with the auto update; the differences are the ones of the two removed reports |
| `src/test/resources/unit/word/list.csv`, `unit/triple/list.csv`, `unit/value/list.csv` | — | the new phrases and the value templates of the definition at the end | the next free ids, so `test/reset_db.php`; no pinned id moves, so no re-pin for this change |
| `docs/json_findings.md`, `docs/code_*` | — | a finding of the new use case file, if any; the removed class | generated reports |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|
| `src/main/php/web/component/execute/ui_list.php` (`ranked_rows`, new `has_numbers`), `src/main/resources/script/calculator_json.js` (`rankedRows`), `src/test/php/unit_ui/phrase_data_ui_tests.php` | every leaf of the `is a` and `is part of` tree below the phrase of a table is a row | only a leaf with a number of the page is a row | the run of 2026-11-27 after `test/reset_db.php` gave every case report of the portfolio split page "Only in the generated page (2)": the rows "# / 43" and "Position / Alternative investment" of the holdings and the walk. The new use case file declares `alternative investment` · `is a` · `stock`, so that `convert_csv` gives an alternative investment the stock templates; in the shared database the class is a leaf below holding, because nothing is an alternative investment there, and so a row without any number. Decision of the developer (2026-11-27): a class without any number of the page is no row, and the sample stays; a class shows as soon as a holding is one, e.g. by an import of the csv. The review of 2026-11-28 split the filter of `ranked_rows` into two lines |
| `src/main/php/cfg/const/files.php` (new `USE_CASE_FILES_AFTER_XBRL`), `src/main/php/cfg/db/sql_db.php` (the db setup), `src/test/php/utils/json_validation.php` | `bank_position_csv.json` at the end of `files::USE_CASE_FILES_APPENDED` | the last import of the db setup, after the XBRL fileset | the same run failed "... and are the saved fixed list" of `phrase_list_read_tests.php` (`list_is_a.csv`): the phrase ids of the triples of the XBRL fileset of ABB moved by the 45 new triples of the definition, e.g. "low voltage products (sector)" from -6883 to -6928, because the XBRL fileset is imported after the use case files as the last step of the setup; the plan "no pinned id moves" missed that the end of `USE_CASE_FILES_APPENDED` is not the end of the setup. With the definition after the XBRL fileset its phrases get the next free ids, so the next `test/reset_db.php` gives the old ids again |
| `src/main/php/cfg/import/convert_csv.php` (`set_templates`, new `is_template`) | every value of the definition is a template | only a value that names a column item or a triple template | the next run failed "convert_csv converts the sample csv with the exported definition to the values of the import file": the export of the format phrase has also values of other use cases that name its phrases, e.g. "2025, USD per EUR, assumed, exchange rate = 1.09" and "field step, price = 0.5", which `convert_csv` wrote as constant templates; each of the 10 templates of the file names a column item. The review of 2026-11-28 moved the check of a triple template name to `is_template_name` and gave the closure a typed parameter |
| `src/main/php/cfg/export/phrase_data.php` (`phrase_vars`, new `parent_triple_ids`) | the phrases of the values and the sides of their triples | also the `is a` triples from a phrase of the values to a parent that the completed data has already, e.g. `stock` · `is a` · `holding` with holding as a side of holding value; only links between phrases of the data, so that the export of a page does not grow by new phrases, e.g. beyond the tolerance of "not much more objects than the import file" of the table editor | the same run failed "the phrase data of a csv format contains every triple of its import file": 6 triples missing, e.g. "stock is a holding", "value kind of face", "value kind of market" and "start of header". Decision of the developer (2026-11-28): extend the export walk. A link without a name is no phrase of a value, so the walk reached the kinds but not their parents. Expected after the run: the phrase data fixtures and the seeds of the javascript pages get the `is a` parents of their value phrases, and "face value", "market value" and "header start" are checked again: if they are still missing, the database has them as something else than the triple of the file, e.g. a word of the same name. The review of 2026-11-28 keeps the link ids out of the ids that select the formulas, because a link without a name has no formula |
| `portfolio_split_calculator_no_js.html`, `portfolio_split_calculator_js.html` (the seed) | the phrases and values of the portfolio split use case | also the triples and the value templates of the csv format, e.g. "bank position csv columns has a bank position csv instrument" and "bank position csv currency, assumed, lot size = 0.01", about 270 lines more | the run of 2026-11-28 after `test/reset_db.php`, before the export change above: the definition names phrases of the page, e.g. stock, bond, lot size and currency, so the walk of the page reaches its values and their triples; the html of the page is the same apart from the rate of section 11 of `2026-11-25_csv_child_shares.md`; written by the auto update; the case reports show 0 differences, but are from 01:00, before this run, so the next run confirms them |
| `src/main/php/cfg/export/phrase_data.php` (`parent_triple_ids`), `src/test/php/unit_read/phrase_data_read_tests.php` (from walk of a csv format) | the `is a` links from a phrase of the values to a phrase of the data; the test compares the found triples with the triples of the file | the `is a` links between any two phrases of the data; the test lists the triples of the file that the export misses | the run of 2026-11-28 after the export change still failed both tests: 3 triples missing, and `convert_csv` with the exported definition missed 3 values. The sample csv has one alternative investment (CH0047533549) and the file has three stock templates (shares, stock price, lot size), so the db definition lacks `alternative investment` · `is a` · `stock`: alternative investment is no phrase of a value, only the to side of the format triple "bank position csv alternative investments", so the filter on the value phrases left the link out. The diff of the triple test showed only the last triples of the sorted file list, e.g. "zukunft.com is a system", which the export has, so the test names the missing triples now; the other two are named by the next run |
| `src/main/php/cfg/export/phrase_data.php` (`keyword_ids`, `next_phrases`, new `is_context_link`), `src/test/php/unit_read/phrase_data_read_tests.php` | the walk adds a triple of a walked phrase only for a calculator verb e.g. has a; the conversion with the exported definition within the time limit of a normal function | also a triple to the keyword csv context e.g. "bank position csv portfolio part"; the time limit of a file function | the run of 2026-11-28 after the `is a` links between the phrases of the data named the 2 missing triples: "bank position csv instrument is part of csv context" and "csv (system) has a context" (csv context); the walk reaches the column "bank position csv instrument" and its has a triples, but "is part of" is no calculator verb, so the triple template that makes each position part of the portfolio of the conversion (`convert_csv::row_phrase`) and its keyword were missing; the conversion test compares only the values, so it passed without the "is part of" triples of the positions; no other page has a triple to csv context, so their seeds stay the same; the conversion test reads the csv file and converts it twice, so it took 0.069 of the 0.03 seconds of a normal function |
| the seeds in the snapshots of the other use case pages: `pv_lifetime_balance_no_js.html`, `pv_lifetime_balance_js.html`, `calculator_editor.html`, `formula_editor.html` (462 lines each), `npp_ch_vote.html`, `npp_ch_vote_js.html`, `npp_ch_vote_editor.html` (63 lines each), `table_editor_global_warming_solution.html` (126), `table_editor.html` (14), the portfolio split pages (about 17 more) | the phrases and the triples of the walk | also the `is a` triples between the phrases of the page, e.g. 60 times "SK-811 01 (postal code)" on the PV pages, "2025 (year)", "Switzerland (country)" and "Olkiluoto 3 (EPR)" | the run of 2026-11-28 10:37 with the first version of `parent_triple_ids` (from a phrase of the values to a parent in the data): a postal code, a year or a country of a value has its own named `is a` triple, which is no phrase of a value and so was not in the data before; written by the auto update; the version of 11:11 (from every phrase of the data) can add more of them on the next run; the html of the pages is the same and the table editor stayed within its tolerance of "not much more objects than the import file", because no failure of it was reported |
| `docs/code_object_name_exceptions.md` | `convert_portfolio_csv: $conv_csv` | `convert_csv: $conv, $other_conv` | the same run failed "check that the object name exceptions doc is updated" and wrote the report again: the negative test of the template filter in `import_tests.php` creates a second converter as `$other_conv`; `convert_csv` has no suggested var name, so it is an entry of "Classes without a suggested var name" |
| 22 snapshots of `src/test/resources/web/html/workflow/` and `workflow_write/` e.g. `add_triple_wf4/wf4_edit_back_edit.html`, `src/test/resources/unit/phrase/list_is_a.csv` | — | the option "bank position" (-8704) in the from and the to phrase list of the triple forms; the row `-8705, alternative investment stock` at the end | the same run: the new triples are in the shared database, so every phrase selector lists them; the id of the new triple is the next free one, as planned for the list files; written by the auto update |

### Lessons learned by llm (2026-11-27)

- A new use case file in the shared database reaches the pages of the other use cases through every phrase it shares
  with them, by the export walk, the phrase selectors and the rows of a table; the plan of a new file lists these
  shared phrases first, so that "Changes that the expected changes might cause" names the seeds and the selectors
  (2026-11-28).
- A change of the export walk changes the seed of every page, not only of the page it is made for; the page with the
  most phrases of one kind e.g. the postal codes of the PV page shows the size of the change, so it is checked before
  the first edit (2026-11-28).
