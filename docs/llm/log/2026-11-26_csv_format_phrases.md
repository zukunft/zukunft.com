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
