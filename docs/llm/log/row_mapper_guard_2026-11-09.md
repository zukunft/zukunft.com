# row mapper guard (2026-11-09)

Prompt "a fresh message for each row of a list mapping and no mapping after an earlier error" of
`docs/llm/pending.md`. The tables of the expected changes were written before the first code edit; the line
numbers are those of the start state (commit `bc90e700e` plus the uncommitted docs of the two log examples).

## Request

`docs/llm/dependent-errors.md`: a mapping does not start if `$msg` already carries an error, returns true only
if it has mapped the row, and each try of an independent entry gets a fresh message that is merged afterwards
and never reads the shared request message. The steps belong into one change, because the guard of step 2
without step 1 would add the rows after a row with an error as empty objects to the list.

1. **Fresh message per row:** `sandbox_list::rows_mapper_obj` (and every other list loop that maps rows) maps
   each row with its own message (with the buffer comment), merges it into `$msg` after the row and adds the
   object to the list only if its mapping has returned true.
2. **No mapping after an earlier error:** `db_object_seq_id::row_mapper` starts with a guard that returns false
   without setting the id if `$msg` is not ok; `sandbox::row_mapper_sandbox` returns true only if a row has
   been mapped (id set and `$msg` ok), so its docblock and the comment about the half mapped object change.
3. **`if ($result)` in the child mappers:** `component.php`, `component_link.php`, `formula_link.php` and
   `formula_map.php` continue with `if ($result)` after the parent mapper instead of `if ($this->id() != 0)`
   and drop the "TODO Prio 1 can result be false" line.
4. **Not saved, not added:** an entry whose mapping has returned false is neither saved nor added to a list of
   objects that are saved; check the import and the save path for callers that continue with such an entry.
5. **Tests:** add a test to `formula_link_tests`, `term_view_tests` and `formula_tests` that a row with an
   earlier error on the message is not mapped at all (result false, id 0) and its negative with a clean
   message; a list test checks that the row after a row with a mapping error is mapped completely and that
   the row with the error is not added.

## Expected changes

The request has five steps, so under the rule of at most three expected changes it would be split into several
changes; this log was started before the rule, so it lists the three code steps here and the check of step 4 and
the tests of step 5 as side effects below.

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/sandbox/sandbox_list.php` | `rows_mapper_obj` 140-172 | same function | step 1: a buffer message per row, the object added only if its mapping returned true |
| `cfg/helper/db_object_seq_id.php`, `cfg/sandbox/sandbox.php` | `row_mapper` 140-154, `row_mapper_sandbox` 330-378 | same functions | step 2: an earlier error resets the id and returns false; a missing row or a row without id is reported with the new `msg_id::DB_ROW_ID_MISSING`, so that the return `$msg->is_ok()` is false for every row that has not been mapped |
| `cfg/component/component.php`, `component_link.php`, `cfg/formula/formula_link.php`, `formula_map.php` | the row mapper of each | same functions | step 3: continue with `if ($result)` and return `$msg->is_ok()` |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `cfg/helper/type_object.php` | `row_mapper_typ_obj` 186-206 | same function | the same guard, because it maps its fields without asking the parent result; a row without the standard id field (the update row of a code link csv) skips the parent mapper, which would report it |
| `shared/enum/messages.php`, `resources/translations/en.yaml`, `de.yaml` | after `TERM_ROW_ID_MISSING` | new case and texts | `DB_ROW_ID_MISSING` for step 2; not `log_err_msg`, because `log_err` opens a database connection in the unit tests |
| `cfg/system/sys_log.php` | `load_by_id` 356-372 | same function | the only loader that gave a missing row to the mapper; it maps only a found row now, because step 2 reports a missing row |
| `test/php/unit/sys_log_tests.php` | 519-523 | same block | the test that maps a missing row also asserts that it is reported |
| `cfg/formula/formula_list.php` | `rows_mapper` 118-157 | same function | the same per row buffer as step 1 |
| list loops with their own mapping (`change_log_link_list::load` 180, `change_log_list::load` 1124, `user_list::load` 329, `view_sys_list` 118, `ip_range_list::load` 112, `job_list` 230, `source_list::load` 159, `term_list::load` 222, `system_time_slow_list` 96, `sys_log_list` 176, `triple_list` 240-279, `phrase_list::load_linked_phrases` 962, `verb_list` 203 and 236, `word_list::load` 446 and `load_linked_words` 485) | each loop | same loop | the same per row buffer; the loops of the multi key objects (`value_list::rows_mapper_multi` 151, `group_list::load_by_phr` 126, `result_list::load` 273, `figure_list` 132) get the per row buffer too, because step 1 names every list loop |
| child mappers of the seq id objects that use `if ($this->id() != 0)` (`sandbox_code_id`, `sandbox_named`, `sandbox_typed`, `sandbox_link_named`, `word`, `triple`, `element`, `change`, `change_link`, `change_value`, `db_cache`, `db_cache_page`, `db_object_seq_id_user`, `group_link`, `user`, `user_profile`, `user_log`, `sys_log`, `system_time`, `job`, `ip_range`, `verb::row_mapper_verb`) | the mapper of each | same function | `$result = parent::…; if ($result)`, return `$msg->is_ok()`; the comments "independent of the message state" go, because the guard makes them wrong |
| child mappers that already use `if ($result)` (`view`, `view_relation`, `term_view`, `ref`, `source`) | — | — | no change, they already return `$msg->is_ok()` |
| `test/php/unit/formula_link_tests.php`, `formula_tests.php`, `term_view_tests.php` | row mapper section | same section | step 5: not mapped after an earlier error (false, id 0) and the negative with a clean message |
| a list unit test (`word_list_tests.php`) | new block | — | step 5: the row after a row with a mapping error is mapped and the row with the error is not added |
| `docs/llm/pending.md` | 3-26 | removed | the prompt is done |
| `test/php/unit_read/word_read_tests.php` | 354-363 | same block | the regression test "the triple verb is mapped even if the message is not ok before the load" asserts the old rule; it becomes the test that the triple is not loaded at all |
| `cfg/sandbox/sandbox.php` | `load_standard` 1210-1222, `load_user_changes` 1250-1262 | — | they add a `LOAD_*_MAPPING_FAILED` message if the mapper returns false; checked: with an earlier error this already happened, now also for a row without id, which is a real problem |
| `cfg/helper/db_object_seq_id.php` | `import_obj` 417-440, `cfg/sandbox/sandbox.php` `save` 3098 | — | checked for step 4: both stop on `$msg->is_ok()`, so an entry with a failed mapping is not saved; no change expected |
| `cfg/import/import.php` | the loops of the json sections 660-760 | — | the import passes one message to all entries, so after the first error the loads of the later entries map nothing; this already stops at `import_obj`, the per entry message with the error limit is `docs/llm/pending_prio_2.md` "error limit for independent import entries" |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | switch on the unit tests of the changed mappers and lists, the read tests of words and triples and the coding rule tests |
| `docs/code_user_message_exceptions.md` | — | — | generated; every new buffer carries its comment, so no new entry is expected |
| `cfg/phrase/phrase.php` | `row_mapper_sandbox` 285-372 | 290-381 | the same guard and the same `DB_ROW_ID_MISSING` for a row without id: the phrase maps its word or triple from the row itself without the parent chain, so the base mapper does not reach it |
| `cfg/phrase/term.php` | `row_mapper` 288-310, `row_mapper_sandbox` 315-360 | same functions | the same guard, and `DB_ROW_ID_MISSING` in `row_mapper`; without the guard the term sets the id again after its word, triple, formula or verb has refused the mapping |
| `cfg/helper/db_object_seq_id.php`, `cfg/sandbox/sandbox.php` | `load` 312-325, 1164-1178 | 318-327, 1166-1180 | the comment said that a found row sets the id "independent of the message state", which is the old rule |
| `cfg/word/triple_list.php` | `load` 249-274 | 249-280 | the excluded check is combined with the mapping result, so that an unmapped row is not logged as excluded |
| `test/php/unit_read/word_read_tests.php` | 129-139 | 129-145 | a second regression test of the old rule, for the word name; its comment records why the old rule was made: a load after an earlier error dropped the name and let a triple import fail |
| `test/php/utils/test_base.php` | after `assert_reset` | `assert_row_mapper_guard` 6090, `map_list_rows` 6121 | one helper for the three guard tests instead of the same eight lines three times; the list test needs the protected rows mapper of a list |
| `test/php/create/test_formulas.php`, `test_views.php` | after `formula`, `formula_link_increase`, `term_view` | `formula_row` 143, `formula_link_row` 567 … `formula_link_to_row` 587, `term_view_row` 702 | the rows of the tests from the factories, built from the factory objects, so no new id const is needed |
| `test/php/unit/formula_link_tests.php` | 217 | 218-234 | the list test is here and not in `word_list_tests`: a word row cannot fail in a unit test without the database or `log_err`, a formula link row with an untrimmed joined name can |
| `test/php/unit_write/a_selected_test.php` | uses 98-110, `run` 232-279, 481-513 | 98-166, 288-335, 537-569 | 58 `use` lines and 58 switched on tests, because every load runs through the changed mappers; the tests of the last commit stay switched on for the same reason |
| `docs/llm/pending_prio_2.md` | end | new section | the guard of the multi key mappers (values, results, groups, figures) and the broken `group_list::rows_mapper` |
| any test that loads with a `$msg` that still carries the error of an earlier negative test | — | — | the load now maps nothing, so such a test fails by design; the fix is the missing `$msg->reset()` after the earlier assert, not a change of the mapper |
| any request path that loads after an error on the request message, e.g. an api call that reloads the object after a failed save to answer with it | — | — | the reload now maps nothing, so the answer may be empty instead of the stored object; the base rule wants this, but a write or api test may show it |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | the tests switched on for this change were off in `test_part.php` before, so a failure that is older than this change can show up there too |

## Unexpected changes

Empty until the developer has run the tests (`a_selected_test::run` with `test/test_part.php`, then `test/test.php`).

## Lessons learned

Written after the unexpected changes are known.
