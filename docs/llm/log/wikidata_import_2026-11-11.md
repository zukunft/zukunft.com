# wikidata import (2026-11-11)

The prompt "wikidata link" of `docs/llm/pending.md`: the numeric values of a phrase are imported from wikidata the
way the description is taken from wikipedia, with a confirm page before anything is written. The tables were
written before the first code edit; the line numbers are those of the start state (commit `b98d0ccd1`, the files of
the plan are unchanged in the working tree). The six steps of the prompt are six changes, each its own commit, but
all of them use this one log file, as the developer has decided in the prompt (`pending.md:88`), so the table
"Expected changes" has one row per step instead of at most three.

## Request

The converter `cfg/import/import_wikidata.php::convert` (`:234-275`) turns the entity json and the property json of
wikidata into an import json, and `unit/wikidata_tests.php` checks it offline on the two examples in
`test/resources/import/wikidata/cache/unit/`: Q72, the population (P1082) of Zurich, and Q4917, the price (P2284) of
the US dollar. The converter reads the labels of the units and criteria from a `test_paths` folder
(`item_label`, `:462-478`), skips some claims without a message, names every phrase by its wikidata label instead of
the phrase of the pod with the same wikidata reference, writes a unit of its own as a triple "entity in unit"
(`import_json`, `:320-350`), and no user can start it: the refresh icon of a wikidata reference only creates a
refresh job (`web/ref/ref.php::refresh_job_link`, `:574`).

The target: the refresh icon of the wikidata reference of a phrase opens an import preview with the words, triples
and values that wikidata would add, and only the confirmed import writes them, as the user and with the source
wikidata (`sources::WIKIDATA`). Open decisions of the developer, asked before the code of the step that needs them:

- step 3: which seed words get a property reference, e.g. inhabitants with P1082 and price with P2284, and whether
  the seed keeps one wikidata reference per wikidata id (Q4917 is the reference of the two seed triples "US dollar"
  and "U.S. dollar", `system_unit_test_data/currencies.json:792-822`)
- step 4: whether a price that matches only the reverse currency pair is reported, or imported as 1/x with the
  reverse pair

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| step 1: `cfg/import/import_wikidata.php`, `test/php/const/files.php`, `test/php/unit/wikidata_tests.php`, `test/php/unit_write/wikidata_write_tests.php`, new fixtures `cache/unit/Q4916.json`, `Q56229488.json`, `Q56229478.json`, `Q0.json` | the constructor; `request_json` 150; `store_text` 185-189; `convert_to_file` 280-284; `item_label` 462-478; `use ... paths as test_paths` 55; files 534-540; wikidata_tests 67 | a constructor parameter `string $fixture_path = ''` and one private `read_json(string $id, user_message $msg): string` with a new `msg_id` for a missing fixture; `store_text` and `convert_to_file` get the folder as a required parameter; the test path import goes; the four `test_files` consts | the backend never reads a test folder and an offline unit test never sends a live request; the tests: the two example targets are unchanged, an item without fixture file is reported and nothing is read from wikidata |
| step 2: `cfg/import/import_wikidata.php`, `shared/enum/messages.php`, `translations/en.yaml`, `translations/de.yaml`, a new hand-made fixture with its target in `cache/unit/` | the consts 96-99; `claim_import` 405-455; `claim_year` 520-528; `convert` 234-275; messages 979-988 | the consts `WD_SNAKTYPE`, `WD_SNAKTYPE_VALUE`, `WD_RANK`, `WD_RANK_DEPRECATED`, `WD_PRECISION`, `WD_PRECISION_YEAR = 9`, `WD_DETERMINATION_METHOD = 'P459'`; the named checks `has_value`, `is_deprecated`, `has_unknown_qualifier`; `claim_year` with `user_message $msg`, the sign and all digits of the year; `values_without_doubles(array $values, user_message $msg): array`; `WIKIDATA_NO_VALUE`, `WIKIDATA_DEPRECATED`, `WIKIDATA_TIME_PRECISION`, `WIKIDATA_QUALIFIER_UNKNOWN`, `WIKIDATA_SAME_WORDS` | no claim is skipped without a message (`docs/llm/coding.md`); the amount is passed on as the complete text of wikidata; the tests: one claim per rule, each checked by its message, the claims next to it are imported; the two example targets are unchanged |
| step 3: `cfg/ref/ref_list.php`, `cfg/import/import_wikidata.php`, new sql fixtures in `test/resources/db/ref/`, the ref list unit test, `test/php/unit/wikidata_tests.php` | `load_by_ids` 176, `load_sql_by_ids` 240; `convert` 234; `label` 372; `item_label` 462 | `load_by_type_and_keys(int $typ_id, array $keys, user_message $msg): bool` with `load_sql_by_type_and_keys`; `convert` gets the map `array $phr_names` of wikidata id to phrase name | the entity, the property, the units and the criteria get the name of the phrase of the pod with this wikidata reference, only an id without phrase becomes a new word; an id with more than one phrase is reported and its values are not imported; the tests: the sql of postgres and mysql, a map gives the names of the pod, an empty map the two example targets, an id with two phrases is reported |
| step 4: `resources/messages/system_unit_test_data/currencies.json`, `cfg/import/import_wikidata.php`, `shared/enum/messages.php`, the translations, a new fixture next to `cache/unit/import_Q4917.json`, a db read test | the triples of currencies.json 766; `unit_phrase_name` 505-516; `import_json` 320-350; `PART_TRIPLE_UNITS` 103; `IN_VERB` 106 | the listed currency pairs as triples of the type `measure_unit` like "CHF per USD" (`use_cases/share_buy_sell_abb.json:879-885`); `convert` gets the pairs as a second map; `WIKIDATA_CURRENCY_PAIR_MISSING`; `import_json` writes no triples, `PART_TRIPLE_UNITS` and `IN_VERB` go | the price of a currency in another currency is a value of the pair triple "unit per entity", because a unit as a phrase of its own does not say which currency is the base; the pairs are listed in the seed and never derived or created by the import, because the market convention has exceptions that change; the tests: the new Q4917 target with "EUR per USD", a price without listed pair is reported, each seed pair is a `measure_unit` triple of two ISO 4217 codes |
| step 5: `shared/url_var.php`, `web/ref/ref.php`, a new `api/wikidataImport/index.php`, `web/frontend.php`, the component of the import preview, `resources/messages/system_views.json`, the admin view "Import from Wikidata", the ref ui tests, a new `object_pages` snapshot | `REFRESH_WIKIPEDIA` 167, its human name 671, the url map 1043; the wikidata branch 545, `wikipedia_refresh_link` 559-566, `refresh_job_link` 574; `url_with_wikipedia_refresh` 3262, its call 1164; `views::IMPORT_PREVIEW_ID` 459, `views::IMPORT_WIKIDATA_ID` 465 | `url_var::REFRESH_WIKIDATA` with its human name; `wikidata_refresh_link`; the controller answers a GET with the phrase id with the import json of `convert` and its messages; `frontend::url_with_wikidata_refresh`; the preview lists the new words, the new values, the unchanged values and the conflicts, and its confirm sends the import json with PUT to `api/json/index.php` (67-75) with the source wikidata (`json_fields::SOURCE_NAME`) | the user starts the import from the reference row and sees what is added before it is written; a value with another number is a conflict that is never overwritten without the user; without login the icon is grey; the tests: the url of the refresh icon, the preview page of Q72 in test mode, so without wikidata |
| step 6: `test/php/const/workflows.php`, a new workflow `wikidata_import` in `unit_write_workflow`, `test/php/unit_write/wikidata_write_tests.php` | `WF_WIKIDATA_REFRESH` 380-381 | the const and number of `WF_WIKIDATA_IMPORT`; the workflow presses the refresh icon of the wikidata reference of Zurich, sees the preview and confirms; the live convert of Q72 and Q4917 with the two maps of the seed | the import is tested end to end; the cleanup removes the imported values, words and references with the logged `del()`; only the page before the refresh is a snapshot, because the live data of wikidata changes, and the write test compares the live convert with the last run like `ref_write_url_tests` |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `test/resources/import/wikidata/cache/unit/import_Q4917.json` | — | — | no change: step 4 adds the new target next to it, the developer retires the old one (`docs/llm/testing.md`, only add) |
| the pinned ids of the seed rows after the currency triples, `test/php/create/test_pinned_ids.php` and the snapshots with these ids | — | regenerated | the new pair triples of step 4 shift the seed ids after them, which the re-pin run takes up |
| `test/resources/db/ref/` | — | new | the sql fixtures of `load_sql_by_type_and_keys` (step 3) |
| `test/resources/web/html/` of the ref rows and the reference ui tests | — | regenerated | the refresh icon of a wikidata reference gets another url (step 5); reviewed by the developer |
| `test/resources/web/html/workflow_write/wikidata_import_*/` | — | new | the snapshot of the page before the refresh (step 6) |
| `cfg/import/import_wikidata.php` callers, e.g. the job of `refresh_job_link` and `views::IMPORT_WIKIDATA_ID` | — | — | each caller of `convert` builds the two maps and passes the reader folder; found by a search before the edit of step 1 and step 3 |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md`, `docs/code_user_message_exceptions.md` | — | — | generated; the new functions and the new messages |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | switched on per step: `wikidata_tests` and the ref list tests (steps 1 to 3), `coding_rule_tests` (the new messages and their translations), the ref ui and workflow tests (steps 5 and 6) |
| `docs/llm/pending.md` | the prompt "wikidata link" | — | removed once done |

Steps 1 to 3 change no seed and no id, so a `test/test_part.php` run with `wikidata_tests` and the ref list tests
is enough; step 4 changes the seed and needs the re-pin run (`test/reset_db.php`, then `test/test.php` twice);
steps 5 and 6 with the ui, workflow and write tests, and the full `test/test.php` before each commit.

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|

Written after the unexpected changes are known.

## Lessons learned by llm

Written after the unexpected changes are known.

## Lessons learned by human
