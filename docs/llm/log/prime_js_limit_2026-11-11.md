# prime js limit (2026-11-11)

The prompt "prime group id within 2^53" of `docs/llm/pending.md`. The tables were written before the first code
edit; the line numbers are those of the start state (commit `52eb329b1`); the files of the plan are unchanged
since commit `8b60ac85d`, which the prompt names.

## Request

A prime group id is four slots of 16 bits, each a sign bit and a 15 bit phrase id, filled from the left with empty
slots and sorted with the highest id first (`group_id::get_id_by_ids`, `cfg/group/group_id.php:110-135`,
`cfg/group/id.php::id_lst_to_int` 67). An id above 2^53 - 1 (`9007199254740991`) is rounded by javascript: the api
json writes the value id as a number (`cfg/sandbox/sandbox_value.php:1650`) and `calculator_json.js:172` and
`:175` read it with `JSON.parse`, e.g. the value id `import_tests::TV_GROUP_ID` 81495585085949938 of word 289 and
the triples 1997, 1999 and 2034. The id is at most the limit exactly if the first slot is below 32.

Decided by the developer:
- a group of up to three phrases stays prime with its id of today (the first slot stays empty)
- a group of four phrases stays prime only if one of its positive ids is at most 31: the smallest positive id goes
  into the first slot and the other three follow with the highest first, so that the same set of phrases always
  gives the same key; the first slot needs a positive id, because its sign bit is the sign bit of the 64-bit
  integer; any other group of four gets the text key and with it the standard tables
- the id of every prime group of four phrases changes, also of those that stay prime
- no pod with data exists, so no upgrade of the stored keys: `test/reset_db.php` writes every key new
- the value key and the result key are done as one change with one re-pin run

The table is chosen from the key type alone (`group_id::is_prime`, `:271`), except for a result, whose table is
chosen from the number of its phrases (`sandbox_value::is_prime` and `is_main`, `sandbox_value.php:670-698`).

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/group/group_id.php`, `docs/llm/group_id.md`, `test/php/unit/group_tests.php`, `test/php/unit/import_tests.php` | `get_id_by_ids` 110-135; 12; 95-135; 98-102 | the const `JS_INT_MAX`, a named check e.g. `fits_js_int(array $id_lst)` and the slot order of the decision; the prime row and the slots of the doc; the tests | the value key is at most `JS_INT_MAX`; the tests, each with its negative: the ids of up to three phrases stay (e.g. `[32765,32766,32767]` 140735340773373), four phrases with a positive id of at most 31 get the new int key with it in the first slot, four phrases without it the text key (e.g. `[32767,32766,32765,32764]`), the boundary `[31,32765,32766,32767]` (int key) and `[32,32765,32766,32767]` (text key), `[-5,40,41,42]` the text key, the decoding of each new key gives the same set of ids, and the decoding keeps the order of today, the highest id first (`int_array` sorts the ids of the slots), so that no caller of `get_array` sees another order; `import_tests::TV_GROUP_ID` tests that `update_put::db_id` turns the numeric text of the editor into a number, so it keeps this purpose with the key of word 289 and the triples 1997 and 1999 (three phrases, 1243523942351), and the group of the four phrases (with 2034) is a case of `group_tests` with the text key |
| `cfg/group/result_id.php`, `cfg/sandbox/sandbox_value.php` | `get_id` 119-150; `is_prime` 670-682, `is_main` 684-698 | no change | found before the first edit: `result_id::get_id` has no caller (only the consts of `result_id` are used), because the key of a result is the group id of its phrases (`sandbox_value::grp_key_id`, `:651`), which `group_id::get_id_by_ids` builds with the rule of the first row; a result is prime only with up to three phrases (`is_prime`, `:677`), whose key keeps the first slot empty and stays within the limit, so the result needs no own check and the table selection stays |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| the callers of `group_id::get_array` (`group_id.php:164`, e.g. `group.php:211`, `sandbox_multi.php:1047`, `value_list.php:818`) and `int2key` (`:322`) | — | — | no change: `int_array` returns the ids sorted with the highest first as today, so the order of the decoded ids does not depend on the new order of the slots |
| `cfg/group/result_id.php` | `two_int_group_id` 177 | — | no change: the two int keys of the main tables are joined to a text by `implode`, so they are no javascript number |
| `web/`, `calculator_json.js` | — | — | no change: the frontend takes the id from the api json; with every int key at most `JS_INT_MAX` the number of `JSON.parse` is exact |
| `test/resources/unit/value/list.csv`, `unit/result/list.csv`, the api messages and the html snapshots with a value or result id of four phrases resp. a formula id above 31, the pinned group ids | — | regenerated | the seed values and results with such a key get the new int key or the text key and the standard table; regenerated by the re-pin run (first `test/reset_db.php`, then at least twice `test/test.php`) and reviewed by the developer |
| `test/php/create/test_groups.php`, `test/php/create/test_values.php`, `test/php/unit/value_tests.php` | `group_prime_max` 243; `text_value_prime` 253, `value_prime_max` 290; 806 | + `group_prime_4`, + `value_prime_4`; `text_value_prime` with `group_prime_4`; the sql tests of four prime phrases with `value_prime_4` | the test group of four prime phrases (`group_prime_max`: Zurich, inhabitants, 2019 and million, ids 228, 212, 158 and 135) has no id of at most 31, so it gets the text key; decided by the developer: it stays as it is and a new group of four prime phrases with the word mathematics (id 1, `test_phrases::phrase_list_prime`) tests the prime table of four phrases; a unit test checks that the new value is prime and `value_prime_max` is not |
| the sql fixtures of four prime phrases, e.g. `test/resources/db/value/value_prime_p4_*.sql` and `value_text_prime_p4_*.sql`, and of `group_tests` with `phrase_list_prime` | — | regenerated | the new test value has other phrase ids and the key of the new slot order (e.g. `phrase_list_prime` gets 1 instead of 2 in the first slot) |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md` | — | — | generated; the new check of `group_id` |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | switched on: `group_tests` (the key rules), `import_tests` (`TV_GROUP_ID`, with its use), `value_tests` and `result_tests`, `coding_rule_tests` with `run_html_snapshots`; switched off the tests of the committed change `52eb329b1`: `base_ui_tests`, `login_url_tests`, `element_write_tests`, `formula_write_tests` and `view_write_tests` |
| `docs/llm/pending.md` | the prompt "prime group id within 2^53" | — | removed once done |

The test runs: first a `test/test_part.php` run with only the unit tests of the keys, which shows a wrong slot rule
within minutes, then one full run with re-pin for both expected changes.

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|

Written after the unexpected changes are known.

## Lessons learned by llm

Written after the unexpected changes are known.

## Lessons learned by human
