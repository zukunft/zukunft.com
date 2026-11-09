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

| file | old lines | new lines | what and why |
|---|---|---|---|
| `test/php/unit_write/word_write_tests.php` | after the "rejected if an unique key differs" assert (399) | 400 | `test_part.php` stopped with a fatal: the protection test loaded the word with the `$msg` that still carried the rejection of the test before, so the load mapped nothing (the side effect listed above for tests that miss a `$msg->reset()`) |
| `cfg/sandbox/sandbox_named.php` | `get_term` 565-570 | same function | the word without name of that load reached `term::load_by_name(null)`, an uncaught `TypeError`; a named object without name is now reported with `msg_id::NAME_EMPTY` instead, because `update_put` also calls `get_similar` with an object from the api json |
| `cfg/word/triple_list.php` | `load` 261-263 | same lines | the config import of `test_part.php` failed with "A phrase could not be read, because the row mapper got no database row with an id in the field from_phrase_id": a triple without from is valid (`triples.from_phrase_id` is nullable, e.g. a symbol assigned to a triple), but the list mapped the from phrase also for a null id, which the phrase mapper now reports; the error on the shared import message then stopped every later load of the import; the first fix (`!== null`) was not enough, the next run showed the same error, so the rows carry 0 (`sandbox_link::from_id()` returns 0 for a missing from) and the check is `!= 0` like the loose check of `triple::row_mapper_sandbox` |
| `cfg/import/import_file.php`, `cfg/phrase/phrase.php`, `cfg/phrase/term.php` | `failed` 524-529, the class check of the import mapper 945-948, 1458-1461 | same lines | the same run showed "failed because z$VarSummary$z.": `IMPORT_FAILED` expects `VAR_SUMMARY`, but got `VAR_ERROR_TEXT` or no var, so the reason was lost |
| `cfg/db/db_check.php` | `db_check` 118 | removed | the same run ended with the fatal "Call to undefined method config::ip_user_can_change()": `db_check` declared `global $cfg` and overwrote the global number config with the service config, which `user::is_blocked` then used; `$cfg` is now local like in every other user of the service config |
| `cfg/helper/config_numbers.php` | `load_cfg` 527-529 | same function | the setup of an empty database logged "the values of (word_id not set) … cannot be loaded, because the phrase has no id": the system user import loads the config before `config.yaml` is imported, so the phrase "system configuration" is not found; the values are loaded only for a found phrase now, and the missing config stays reported as `CONFIG_EMPTY` |
| `cfg/import/import_file.php` | `json_file` 108-111 | same lines | the `CONFIG_EMPTY` error of the config load went onto the import message, so with the guard every load of the system user import during the setup mapped nothing; the config is loaded with a buffer message whose complete text is added as an info (a warning status is not ok and would stop the import too), so the import uses the fallback values and the message is kept |
| `cfg/import/import_file.php` | `import_config_yaml` 256-289 | same block | the validation runs also after a failed config import and loaded with the message of that failure, so with the guard it mapped nothing and reported "only 0 are in the database" and every config phrase as missing; the config load and the load of the target values use an own buffer each, merged into the import message right after the loads |
| `test/resources/api/type_lists/type_lists.json` | 1-36152 | 1-3 | not changed by the LLM: a run with the `from_phrase_id` error of `triple_list` overwrote the fixture with the error answer of the api call (`{"message": "A phrase could not be read …"}`), so `test_lib::ui_test_cache` built the frontend type lists from an empty answer and reported "the frontend test cache … is created without errors" with an empty text; the developer restores it from git, the writer is not known yet |
| `test/php/utils/test_api.php`, `test/php/create/test_db_load.php` | `assert_api_compare` 1186, 1205; `api_json_usable` 1532-1552 | `api_json_usable` moved to `test_api` | the api tests of `update_files_with_not_yet_fixed_db_id` failed, because their expected files held the error answer `{"message": "A phrase could not be read …"}` of an earlier run; `assert_api_compare` writes the api answer to the expected file with `AUTO_UPDATE_TEST_FILES` without the check that `test_db_load` does before its writes, so an error answer overwrote the fixtures (most likely also `type_lists.json`); both writes now use the same `api_json_usable` check |
| `test/php/unit/type_tests.php` | `run` 93, the csv row without id 510-516 | 94, 510-528 | `reset_db.php` ended with "1 internal errors": the restored `log_err_msg('id field is missing for …')` of `type_object::row_mapper_typ_obj` fires in the negative unit test of a csv row without id column; the test expects this error with the new `test_base::assert_log_err`, which sets the new `$sys->log_test_only` (`system_object`), so that `log_err` neither writes nor counts the error and keeps its text for the assert; the other cases are listed in `pending_prio_2.md` "expected internal errors in tests only via `$sys->log_test_only`" |
| `cfg/formula/formula_link.php` | `reload_objects` 856-858 | 856-860 | `formula_read_tests` "the phrase of a formula link is reloaded also if the message already reports a problem" failed with an empty phrase name: `reload_objects` loads the phrase also after a failed formula load, because e.g. the delete log names the phrase and its sql function is created only once per name, but with the guard the phrase load on the message of that failure mapped nothing; the phrase load is independent of the formula load, so it gets an own buffer message, merged right after; the test is unchanged |
| `test/php/unit_write/triple_write_tests.php` | cleanup 109-111, second user 139-148 | 109-112, 140-151 | "check if the link is shown correctly also for the second user" got an empty name: the load of the second user ran on `$msg2`, which the cleanup of a possible leftover (load and del of the second user) or the not asserted save of the second user had made not ok, so with the guard the load mapped nothing; the cleanup uses an own message that is not reported and the save of the second user is asserted, so that a failing save names its reason; the next run showed the save as the cause: it reused the object of the first user after `set_name('')`, which `sandbox::save` reports as a duplicate of itself (an older bug, see `pending_prio_2.md` "a triple whose own name is cleared is reported as a duplicate of itself"), so the second user now saves an own object loaded by the link |
| `test/php/utils/test_cleanup.php` | `check_cleanup` 627-650 | 627-652 | the write tests ended with "there are still unexpected system test rows in the database that could NOT be removed" for the test words and components: `check_cleanup` put the leftover report of the first check on `$msg` and then called `cleanup($msg)`, so with the guard every load of the cleanup mapped nothing and nothing was removed; the first check reports to an own message, which is merged after the second check, so the cleanup starts clean and the final report text is unchanged |
| `test/php/utils/test_cleanup.php` | `cleanup` 235-472 | 235-508 | after the `check_cleanup` fix the test components and "System Test Word" were still left: the loads of `cleanup` ran on the shared `$msg`, into which e.g. the view delete buffer is merged, so after the first error of a cleanup step every later load mapped nothing; each load and delete step now uses an own buffer message merged after the step, which replaces the `$msg->reset(true)` workarounds before the word and formula deletes and the "TODO Prio 0 use a local usr_msg for all del calls"; an error of a step now stays on `$msg` instead of being reset away |
| `test/php/unit_write/value_write_tests.php`, `test/php/utils/test_cleanup.php` | `run` 103, 326-393, 552, 603-605; `cleanup_test_values` 567-576, `cleanup` 199 | same places | with the cleanup working again, `check_cleanup` of the value tests reported "System Test Word" as a test row that has been removed only by the full cleanup: the values of the value tests were only remembered in the shared `test_val_ids` for the full cleanup, so the word cleanup at the end of the value tests could only exclude the word that a value still uses (`word::used_by_data` of commit e4988d819); the value tests keep their own list of added values and remove them with `cleanup_test_values`, which now takes the list of ids, before their test words; checked the other write tests that fill `test_val_ids`: `update_put_write_tests` removes its values by the undo of each put and `value_write_url_tests` by the phrase groups of its three workflows, so both already remove what they create |
| `test/php/utils/test_cleanup.php` | `created_row_ids` 104-122, `cleanup_check_query` 720-746 | `created_row_keys`, `created_row_key`, `is_name_field`, `cleanup_check_query` | the formula tests reported "System Test Word Share" as a removed leftover: the word is a row of `create_test_db_entries`, which `check_cleanup` skipped by its remembered id, but `word_write_tests` deletes it on purpose and `formula_write_tests` adds it again with a new id; a created row is now recognised by its name column (the first non-empty `*_name` or `name_*` column, e.g. `name_generated` of an unnamed triple) and only a row without a name, e.g. of the change log, by its id; the `continue` of the check loop is replaced by the positive condition |
| `test/php/unit_write_workflow/word_write_url_tests.php` | `url_to_action_write_tests` 158-167 | 158-173 | "... so it can be deleted" found the added word again (2810 != 0) without a message: the add and the delete url ran with one request message, which the frontend `db_object::del` converts into the backend message, so with the guard an error of the add (or of the setup before it) stops the reload of `sandbox::del`, which only logs a warning and deletes nothing; the delete runs with an own message like its own http request, and the add and the delete report their message, so that the reason is shown if the delete still fails; the next run disproved the earlier message: the add is ok, but the delete with its own message is not ok with an empty text, because the backend message reaches the frontend via `user_message::api_array`, which carries only the message ids, the messages with vars and the status, so a plain text of `add_message_text` (e.g. "Delete of … failed due to …" of `sql_db::delete` or the texts of `sandbox::del`) is lost; the cause of the failed delete is the missing id error of `db_object::url_mapper` (see its row) |
| `test/php/utils/test_system.php` | `run_system_test` 93 | 93-95 | "user->load for id 204" showed an empty user link with `ue=0`: the IP blocking test before it puts the expected "Your IP … is blocked" error on the shared `$msg`, so with the guard both user loads of the next test mapped nothing; the block check uses an own message |
| `shared/helper/MapObject.php`, `test/php/utils/test_base.php` | `convertMsgToUi` 262-267, `assert_msg` 947-963 | `convertMsgToUi` 262-271, `assert_msg` 947-967 | to name the cause of the failed word delete url: the conversion of the backend message into the frontend message keeps the plain texts of `add_message_text`, which `user_message::api_array` does not carry yet, and `assert_msg` shows the last plain text of a frontend message if the shared `text()` is empty |
| `cfg/helper/db_object_seq_id.php`, `cfg/phrase/phrase.php`, `cfg/phrase/term.php` | `row_mapper` 143-144, `row_mapper_sandbox` 292-294, `row_mapper` 292-294 | same places | found in the review: the change to a single return at the end had moved the id reset into `if ($msg->is_ok())`, so after an earlier error a reused object kept its old id and e.g. looked loaded; the reset is again the first statement, as before the change, and the return stays at the end |
| `cfg/sandbox/sandbox_named.php` | `check_save` 919-922 | same function | the save of a named object without name stops with `msg_id::NAME_EMPTY` before `get_similar`, because the message of `get_term` goes to the local `$sim_msg` of `save()`, which is merged only for a found duplicate |
| `web/sandbox/db_object.php` | `url_mapper` 181-184 | 181-189 | cause of the failed word delete url, named by the plain text now shown: the delete url right after the add has the name but not the id, which `frontend::action_crud` resolves by name on purpose; `url_mapper` still reported "Mandatory field id missing in form url array", which the backend `del` gets with the converted message, so its guards stopped the delete; the missing id is no longer reported for a delete with a name, it stays an error for every other url without an id |

## Lessons learned by llm

1. **The guard turns every shared message into a dependency.** About ten of the 23 unexpected changes are the
   same case: several independent steps run with one `$msg`, and an earlier error, often an expected one, stops
   every later load. The expected table had only one generic row for the tests without `$msg->reset()`, but
   the case was also in the cleanup, the setup import, the config validation, the system test and the
   in-process url workflows. For the expected table: before the first test, search for every function that runs
   several loads with one message (cleanup, import, workflow, a test with a negative case before a load) and
   list each one with its buffer message.
2. **A rule that lets the message decide brings out the errors that nobody saw.** As long as the mappers
   ignored the message state, a wrong error had no effect: the missing id error of `url_mapper` for a delete
   by name had been there since July, the wrong var of `IMPORT_FAILED`, the `global $cfg` of `db_check` and
   the from phrase of a triple without from never showed. With the guard each one stops the flow, and fixing
   them is most of the work. For the expected table: a change that makes the message state decide what runs
   next lists "errors that already are on the message" as a cause to look for, not only the new code.
3. **First make the error visible, then guess the cause.** The word delete url took three runs: my first
   cause (the error of the add on the shared message) was disproved by the next run, because the real text was
   lost on the way from the backend to the frontend message (`user_message::api_array` has no plain texts).
   When an assert fails with an empty message, the first fix is to make the text reach the test output; a
   cause without the text is only a guess and should be reported as one.
4. **A style refactor inside a behaviour change hides a change of behaviour.** The move to a single return at
   the end put the id reset of three mappers inside `if ($msg->is_ok())`. No test caught it; the review did. A
   statement that runs before the guard must stay before it, so a refactor for the rules should be checked line by
   line for this, or done in its own step.
5. **A failing run must not become the next expected result.** With `AUTO_UPDATE_TEST_FILES` on, the error
   answer of the api (`{"message": "A phrase could not be read …"}`) overwrote `type_lists.json` and the
   expected api files, so the next run showed other errors whose cause was the earlier one. An answer with an
   error message is never written as a fixture (`api_json_usable`). During a change that is expected to break
   loads, the auto update should be off.

## Lessons learned by human

1. Request less changes at once and force the llm to be more careful
2. Define the guidelines more precise to avoid dropping back the project to an average project

