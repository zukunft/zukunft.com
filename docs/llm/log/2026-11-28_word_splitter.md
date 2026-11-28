# word splitter of the wikipedia table conversion (2026-11-28)

The task "later" of `docs/llm/pending.md`: "add the word splitter to convert_wikipedia_table.php", with a positive and
a negative test (size S, no re-pin).

## 1. the years of a column name

### Request

"do the word splitter task in pending.md": the prompt "the word splitter (size S). One agent: "later", the word
splitter in `convert_wikipedia_table.php` with a positive and a negative test."

Checked before the first edit:
- the TODO of `convert_wikipedia_table::convert_wiki_json` names the split: "add a word splitter to separate e.g.
  "Growth rate (2019–2022)" to "Growth rate", "2019" and "2022""
- `convert_wiki_json` uses each header name of the table as one word and as a phrase of each value of the column, e.g.
  the value 0.2 of "U.S. dollar" and "Growth rate (2019–2022)" of the currency table of the test
- only the unit test of `import_tests.php` converts with it ("wikipedia data table json to zukunft.com JSON"), which
  compares the result with `src/test/resources/import/wikipedia/currency-convert.json`; the db setup does not run the
  converter, so no import file and no id changes
- the other conversion (`convert`) gives a year the type time, e.g. `{"name": "2023", "type": "time"}` in
  `democracy_index_table.json`

Planned:
- `convert_wiki_json` splits a header name that ends with a year or a range of years in round brackets e.g.
  "Growth rate (2019–2022)" or "Population (2020)" into the name without the brackets and the years; the name is the
  word of the column and a phrase of its values instead of the whole header name, and each year is a word of the type
  time and a phrase of the values of the column
- a bracket with another text stays part of the name e.g. "English short name  (using title case)" of the country
  table, because only a year is a phrase of its own
- the triples of a column with text cells name the column by the name without the years, like the values
- tests in `import_tests.php`: the value of the growth rate of the U.S. dollar has the phrases "Growth rate", "2019"
  and "2022" and the years are words of the type time (positive); the name of a column with another text in brackets
  stays as it is, which the existing test of the country table checks with its unchanged fixture (negative)

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/cfg/import/convert_wikipedia_table.php` (`convert_wiki_json`, new `split_name`, new const `YEARS_IN_BRACKETS`) | a header name is one word | the name without the years in brackets and each year as a word of the type time | the task |
| `src/test/resources/import/wikipedia/currency-convert.json` | the word "Growth rate (2019–2022)" and the 40 values with it | the words "Growth rate", "2019" and "2022" and the values with the three phrases | the expected json of the conversion |
| `src/test/php/unit/import_tests.php`, `docs/llm/pending.md` | — | the positive test of the split; the task removed | the test and the task list |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/import/wikipedia/country-ISO-3166-wiki.json` and its fixture `country-ISO-3166.json` | — | unchanged | no header name ends with a year in brackets |
| `src/main/resources/messages/system_unit_test_data/currencies.json`, `src/test/resources/import/wikipedia/currency.json` | — | unchanged | they still have "Growth rate (2019–2022)", because they are no output of this conversion |
| the ids, the snapshots | — | unchanged | the converter is not part of the db setup |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md` | — | the new function | generated reports |
| `docs/json_findings.md` | the findings of `currency-convert.json` e.g. `["Argentine peso","Growth rate (2019–2022)"]` | the same findings with the phrases "Growth rate", "2019" and "2022" | the generated report lists the values without a source of the test files with their phrases |

### Unexpected changes

| file | old | new | reason |
|---|---|---|---|

### Lessons learned by llm (2026-11-28)
