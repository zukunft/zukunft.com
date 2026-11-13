# exclude used phrase (2026-11-08)

Commit `e4988d819` (2026-11-09), part of #247. This log was written after the commit: the request is the
prompt that the commit removed from `docs/llm/pending.md`, and the expected changes are reconstructed from it,
because no table of expected changes was written before the change started. The commit also carries other
topics (formula view and impact, the test report scripts, the delete and the dependent error rules); they are
listed below as unexpected, because the request does not name them.

## Request

"Delete a removed triple only if it is really not used": a removed triple is deleted through `del()` if no
other user has changed it (`used_by_someone_else`), and the triple `del_links` then also deletes its values, so
the remove of e.g. an argument whose triple other values or results still name loses them. Add the check
whether a value, a result, a formula or another triple still uses the triple (values and results first), delete
it only if it is really not used and else exclude it for the user.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/word/triple.php` | after `not_used` 2911-2922 | `used_by_someone_else` 2930, `used_by_data` 2941-2970 | a triple used by a value, a result, a formula or another triple counts as used by someone else |
| `cfg/sandbox/sandbox.php` | `del` 3505-3533 | 3553-3563 | the exclude branch of `del()` tells the user that the object has only been excluded |
| `shared/enum/messages.php` + `en.yaml` / `de.yaml` | 2953 | 2954-2956 | `DELETE_EXCLUDED_IN_USE`, added with ok = true |
| `unit_write/triple_write_tests.php` | after `run` 334-344 | `run_del_used` 357-420 | a used triple is only excluded, another user still sees it, the value is kept; an unused triple is deleted |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `cfg/word/triple.php` | includes 101-104, uses 164-167 | 101-106, 166-170 | `result_list` for the result check |
| `unit_write/a_selected_test.php` | `run` 185-500 | 209-720 | switch on the triple write tests for `test_part.php` |
| `unit_write/triple_write_tests.php` | `run` 160-252 | 166-256 | the change log targets of the unlink after an exclusion |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `cfg/word/word.php` | after `not_used` 1896-1910 | `used_by_someone_else` 1921, `used_by_data` 1932-1962 | the same check for a word, because a word delete also deletes its values and triples (the rule that a defect travels to the sibling) |
| `cfg/result/result_list.php` | `load_by_phrase` 200-205, `load_sql_by_phrase` 396-428, `load_sql_by_obj_old` 717-761 | 200-212, removed, 683-715 | the old sql selected the results by a word or triple id field, which no result table has; the first real caller (`used_by_data`) would have failed, so it loads by the phrase list now |
| `cfg/sandbox/sandbox.php` | `del` 3508-3531 | `usr_row_excluded` 3427, `exclude_exe` 3441-3463 | the exclusion was written by the legacy `save_fields_func` (top rule 5); now one prepared statement |
| `cfg/sandbox/sandbox.php` | after `sql_delete` 3795 | `sql_exclude` 3836-3859 | the prepared exclude statement, as user row update or insert with its change log |
| `resources/db/word/`, `resources/db/triple/` | — | 8 new fixtures | `*_update_log_excluded_*` and `*_insert_log_*` for the exclude statement |
| `test/php/utils/test_base.php` | after `assert_sql_update` 2478 | `assert_sql_exclude` 2504-2550 | unit test helper that compares the exclude statement with its fixture |
| `unit/word_tests.php`, `unit/triple_tests.php` | 273, 154 | 276-283, 157-164 | unit tests of the exclude sql and of a phrase without id |
| `cfg/sandbox/sandbox.php` | `set_owner` 1636 | 1639-1642 | the standard load sets only the ids of the linked objects, but the update checks them; found by the new test `run_del_owner_change` (new 422-450) |
| `cfg/sandbox/sandbox.php` | 3320-3328 | 3340-3348 | the docblock of `load_db` stood above `refuse_link_change` |
| `unit_write/word_write_tests.php` | `run` 95-102, 273-280, 622-700 | 107-123, 301-312, 654-823 | write tests for the used word and helpers `used_by_text`, `excluded_notice` |
| `test/php/utils/test_cleanup.php` | `cleanup` 173-500, `cleanup_check_query` 618-650 | `remember_created_rows` 112, `cleanup_unnamed_test_triples` 506, `cleanup_test_values` 540, `cleanup_check_rows` 717 | test rows are used by other test rows, so their delete became an exclusion and left rows behind; each delete now gets its own message and the rows of `create_test_db_entries` are no leftover |
| `test/php/utils/test_base.php` | `write_named_cleanup_force` 5021-5034 | 5095-5113 | a forced cleanup deletes as the owner, because the delete of another user is only an exclusion |
| `test/php/create/test_db_load.php`, `test/php/create/test_words.php` | `del_triple` 503-515, `add_value` 961-968 | 503-517, 963-974, `cleanup_without_created` 108-120 | a fresh message per delete; the value check reloads from the db; a word cleanup that keeps the created words |
| `unit_write/formula_trigger_tests.php` | `run` 77-95, 137-145 | 77-86, 128-131 | the test added and deleted seed values of `country.json`; the delete would now exclude them for all later tests, so the test only reads them |
| `unit_write/value_write_tests.php`, `job_write_tests.php` | 107-151, 64-77 | 108-169, 64-84 | the word cleanup without the created words, more info on a failed load, one seed independent test value |
| `unit_read/phrase_list_read_tests.php` | 122, 179 | 124-128, 182, `uses_test_word` 356-365 | the read tests ignore test rows that a former run has left because they are now excluded instead of deleted |
| `test/php/utils/test_base.php` | `assert_view_html` 1680-1705 | 1681-1720 | the page title is built with the context of the test, because the meaning of the ambiguous symbol `m` depended on the tests before |
| `resources/web/html/use_cases/*.html` | 3 files, 1 line each | — | `metre` → `m` from the fixed page context |
| `resources/web/html/views_by_object/` | 5 files | — | the test views are no longer left in the view selectors; the values of words 212 and 228 differ, because `formula_trigger_tests` writes no values any more |
| `cfg/formula/formula_db.php` | `FLD_NAMES_NUM_USR` 126-131 | 126-133 | own topic: load the view and the impact of a formula |
| `resources/db/formula/*.sql` | 32 fixtures | — | the two new fields in every formula select |
| `resources/web/html/workflow_write/*formula*`, `validate_*` | 13 files, 26 lines each | — | the change log rows of the formula written by the write workflows (cause of the new rows not checked) |
| `bin/test_differences.php`, `bin/test_object_overview.php`, `test/php/utils/test_*.php` | — | 4 new files, 1'489 lines | own topic: reports of the tests missing in `a_selected_test` and of the factory types per object |
| `docs/llm/delete.md`, `dependent-errors.md`, `testing.md`, `coding.md`, `architecture.md` | — | — | own topic: the delete rules and the base rule that an error stops the following actions |
| `docs/llm/pending.md` | 1-6 | 1-644 | the follow-up prompts, among them the delete rules that the code does not follow yet and the expected errors of the next `test.php` run |

## Lessons learned

1. **A change of what a delete does changes the tests more than the code.** The core change is about 300
   changed lines in `triple.php`, `word.php`, `sandbox.php` and `result_list.php`; the tests and their cleanups
   needed about 960 more, without the report scripts and `a_selected_test.php`.
   The test rows use each other (values on test words, triples between test words), so after the change their
   cleanup deletes became exclusions and left rows behind. For the expected table: a change of `del()`, of
   `used_by_someone_else` or of the exclusion lists every cleanup that deletes a row that another test row
   uses, and the cleanup order.
2. **A test must never write seed rows.** `formula_trigger_tests` added and deleted the seed numbers for 2019
   and 2020. Before the change this worked by chance; after it the delete excluded the seed values for the test
   user and changed every later read and the html snapshots of the words 212 and 228.
3. **A new caller of an old function finds its bugs.** `result_list::load_by_phrase` selected by a field that
   no result table has, and `set_owner` had never been reached for a link object. Before relying on an
   existing function, check that a database test proves it works; if none does, add it to the expected table
   as "fix if needed".
4. **The smallest diff and the top rules conflict.** The request needed only a new condition, but the
   exclusion it triggers went through the legacy `save_fields_func`, which top rule 5 forbids. The new prepared
   `sql_exclude` with its fixtures was the right call, but it should be named in the expected table as a
   consequence of the rule, so that the reviewer sees why the diff is bigger than the request.
5. **One commit with six topics cannot be compared with one request.** Formula view and impact, two report
   scripts, the delete rules and the dependent error rules have no link to the request. Each topic should have
   its own log file and, if possible, its own commit.
6. **Committed without a full green run.** `pending.md` lists the expected errors of the next `test.php` run,
   e.g. the unconfirmed cleanups and a timeout of 182 seconds. A log file of a change should say whether the
   result was confirmed by `test_part.php` or `test.php`, so that the next change does not start on an unknown
   state.
