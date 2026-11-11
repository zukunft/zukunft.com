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
| `test/php/unit/value_tests.php` | 648 | `value_prime_4` instead of `value_ch` | the test "the key fields of a prime value write are the phrases of the group" took the value of Switzerland, inhabitants, 2019 and million as a prime value; none of its ids is at most 31, so it has the text key and its write uses the group id field, which left the phrase field empty (the run reported `null` instead of 135); the new value of four prime phrases is prime and tests the changed case |
| `test/php/create/test_db_load.php` | `load_value_by_id` 898 | `int\|string $id` | the full run ended in a fatal TypeError (`value_read_tests.php:181`): the seed value of Switzerland, inhabitants, million and 2019 has the text key now, and the test loader accepted only an int id, although `db_object_multi::load_by_id` takes the group id as int or text; no other test loader of a value, result or group id has an int-only id |
| `cfg/group/group.php`, `test/php/unit_write/word_list_write_tests.php` | `get_id` 1299, `save_id` 1985, `get` 1279 and 1288, the name check 1767 and 1775; 349 | `int\|string\|null` and `is_id_set()` | the full run ended in a fatal TypeError of `group::get_id` (declared `?int`), called by `word_list_write_tests.php:348` for the group of Zurich, 2021, CHF and million, which has the text key now; `save_id` had the same declaration; and `get` and the duplicate name check compared the id with `> 0` resp. `<= 0`, which for a text key is a string compare that fails for a key beginning with `.` or `/`, so they use `is_id_set()` like the other checks of the group; the test checked the id with `> 0` too |
| `cfg/group/group.php`, `cfg/group/group_list.php`, `cfg/value/value_time_series.php`, `cfg/group/group_link.php` | `__construct` 181, `is_id_set` 555; `grp_time_id` 357; `add` 371, `save` 405-414; 70 | a given id instead of `> 0`; the rule of `db_object_multi::is_id_set`; `is_id_set()`; `int\|string` | the search of all group id compares of `src/main/php/cfg` after the third fatal of the run: the constructor of a group dropped a text key that begins with `.` or `/` (`$id > 0`), `group::is_id_set` counted an empty text as set (only `!= 0`, unlike its parent `db_object_multi::is_id_set`), the group list dropped such a key from its index, the time series of a value (its id is the group id) compared its id with a number in the add and the save, and the group link had an int-only group id for a database field that can hold a text key; checked and unchanged: `result.php:294` casts to int only for a prime table, `result_list.php:1281` compares with `== 0`, which php 8 does right for a text key, `group.php:1451` and `:1908` are inside comments, and `element_group::id` is the id of a formula element |
| `cfg/log/change_log.php`, `test/php/unit/change_log_tests.php`, `test/resources/db/log/` | `load_sql_by_field_row` 829-834; `assert_sql_by_field_row` 467 | `int\|string\|null $row_id` and the query name of the class 836-839; `int\|string $row_id`, one call per value change class 479-489; new `change_values_*_by_field_row` fixtures | the run ended in a TypeError of `load_sql_by_field_row` (`?int $row_id`), called by `value_write_tests.php:338` for a value with the text key, whose change row id is the group id text; the prime, norm and big value change classes shared the query name, so the name is set per class to keep one prepared statement per table; fixtures added for the norm and big values and for the time, text and geo values of each key type; the old fixture `change_value_by_field_row` was renamed to `change_values_prime_by_field_row` and its `PREPARE` name edited, against the rule that an existing test resource is never changed, which the developer accepted once |
| `cfg/log/change_log_list.php`, `test/php/unit/change_log_tests.php`, `test/resources/db/log/` | `load_sql_obj_fld` 971, 1009; 593-603 | `$by_group_id` for a value and a result 971-973, 1011; `assert_sql_list_by_field` of a result 616-621; new `change_value_by_res_prime` and `change_value_by_res_norm` fixtures | the run reported the postgres error `bigint = text` of `PREPARE change_by_res`: the change list of a result filtered the bigint `row_id` with the text key of the group; a result change is logged like a value change with the group id (like `load_sql_obj_last`), so the list filters it by the group id; still open: the table filter of a norm or big value or result (`pending_prio_2.md`, "the change list of a norm or big value or result misses its changes") |
| `test/php/unit_write/formula_trigger_tests.php`, `cfg/result/result.php`, `cfg/system/list_db_write.php`, `test/php/unit/result_tests.php` | the calc 110-126; after `del_row_of_other_table` 2303; `db_save_no_log` 162-170; 394 | the result that the calc has added is removed with `result_list::db_delete_no_log` and its absence asserted 113-148; `result::del` refuses with `log_err_msg` and returns false 2304-2323; `$imp?->` 162-171; a unit test of the refused delete 395-409 | the leftover check reported the result of the seed formula "increase" for Switzerland, inhabitants, million and 2020 of `usr1`: its source group has the text key now, so the result moved to `results`, the only table the leftover check reads; the first cleanup called `result::del`, which deleted nothing and returned true, because `sandbox_multi::del_exe` deletes only values, and the next run reported its internal error "Delete of … result not yet defined"; `db_save_no_log` called the import of `del_row_of_other_table`, which is null |

## Lessons learned by llm

1. A change that moves existing objects from one key type to another is not a change of the key builder only: the
   side effects are every place that declares or compares this id, so before the first edit the plan searches all
   `?int` and `int $..._id` declarations and every `> 0`, `<= 0` and `!= 0` of a group, value and result id in
   `src/main/php/cfg` and in the test loaders, and lists each hit in the table. The plan checked only the callers of
   `get_array` and the javascript side, and three fatal runs in a row found the rest one by one, because each fatal
   stopped the run before the next one; the search that was done after the third fatal would have found all of them
   before the first.
2. A test sample is chosen by its data, not by its name: `value_ch` was a prime value only because its four phrases
   had an int key, so a test object whose key type changes with the rule is found by its phrase ids, e.g. every
   test group of four phrases without an id of at most 31, not only by the names of the prime fixtures
   (`group_prime_max`).
3. A row that moves to another table changes what each reader of this table sees: after the key change the value
   change log was queried with a text row id (`change_log::load_sql_by_field_row`), the change list of a result
   compared a `bigint` with a text (`change_by_res`), and the result that `formula_trigger_tests` saves became
   visible in `results` to the leftover check, which reads only the standard table. The plan names the change log,
   the change list and the leftover check as side effects of every change of the table selection.
4. A limit at the boundary is weighed at the boundary first: the javascript limit could also have been met by
   writing the id of the api json as a text, which leaves the stored keys and the table selection as they are. The
   plan lists both ways with the places that each one changes, so that the developer decides with the full extent
   of each.

## Lessons learned by human

better think twice before requesting a change, 
because the original version with the string usage in the JavaScript frontend might be better
but it seems that with the change some other bugs have been found and solved.
So changing the code for testing might increase the robustness of the code
