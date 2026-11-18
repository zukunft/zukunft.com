# XBRL import, test cleanup and reset consistency (2026-11-14)

A series of "try to fix" prompts on branch `feature/247-frontend-workflow` from 2026-11-14 to 2026-11-16, each with
an error of the developer's test run (`test/test.php`, later `test/test_part.php` and `test/reset_db.php`). The agents
never ran a `/test/*` script and never queried the database, so every cause was found from the pasted errors, the
code and the resource files. Several errors looked like code errors but were a database state or a test design
issue. The changes were made one prompt after another and not planned up front, so the tables name the functions
instead of the line numbers. The fixes of 2026-11-16 (requests 7 to 13) are not yet confirmed by a run; they are
listed at the top of `docs/llm/pending.md`.

## Request

### 1. XBRL concepts that the pod already defines as a triple with another link

Errors: the import refused the link change of "order backlog" and "shares outstanding" (`link_change_refused`),
and "cash cash" was not found.

- "order backlog" and "shares outstanding" are defined by the use case `share_buy_sell_abb.json`, which is imported
  before the fileset.
- "cash" "and" "cash" was created by "CashAndCash" without a name and by "CashCashEquivalents" with the name
  "cash cash"; `unique_triples` dropped the named one.
- Open (`pending_prio_2.md`, "the XBRL converter does not know the triples of the pod"): the converter should get the
  triples of the pod by name instead of the hand made list, and two different names of one link still lose one.

### 2. Verb rename without privileges

The test "rename verb without privileges returns error" got "already exists" instead of "cannot be changed", because
the admin-only rename of `def::ONLY_ADMIN_CAN_RENAME_CLASSES` was never implemented. Decided by the developer: the
rename is refused before the duplicate name check.

### 3. XBRL cleanup leftovers: the message of the converter

Errors: the source "ABB XBRL files for 2013" and three to five values were "left behind", later also formulas,
formula links, a result and three triples.

The search went through several wrong hypotheses (name resolution, owner, delete order, value tables) until an
assert that reloads every deleted value showed about 2400 values that were still in the database, each with the
message text of nine "conflicting values" reports. Cause: `xbrl_write_tests::run` passed the message of the converter
to the cleanup; the conflicts had made it not ok, and `sandbox_multi::del_exe` skips the delete if the message is not
ok, without a new error. Most values vanished later together with their triples, which hid the problem. The nine
conflicts were no conflicts: each was the same fact in CHF and in USD (e.g. `abb:ShareCapital` of
`I2013_ParentCompanyMember`).

Open (`pending_prio_2.md`): a member with the name of the concept ("short term investments") gives the same phrase
group as the total, so the later fact overwrites it.

### 4. Expected html pages that flip from run to run

Eleven pages with a phrase search list (e.g. `views_by_id/triple/50_triple_add_1.html`,
`views_by_id/im_export/151_export_json.html`) lost three triples in one run and got them back in the next, without a
code change. The triples were XBRL leftovers: after a reset the database was clean, then the XBRL test left them
behind, and the ui tests of the next run, which run before the XBRL test, showed them. The auto update rewrote the
expected files each time.

### 5. Descriptions that the setup and the json files define differently

Errors: the re-import of `solution_prio.json`, `currencies.json` and `currency.json` in `a_selected_test`, as the
system user in the no update mode, reported e.g. `description is "the one used if nothing else is selected" instead
of "The setting used if nothing else is specified." for word "default"`.

`sql_db::create_internal_words` writes the descriptions of `config_numbers::INTERNAL_COMMENTS` over the json
descriptions. "CHF per EUR", "CHF per USD" and "USD per EUR" had other descriptions in
`portfolio_split_calculator.json` and `GDP.json` than in `currencies.json`.

Open: "earnings per share" (`accounting.json`, `share_buy_sell_abb.json`).

### 6. The XBRL fileset in the development reset and the re-pin

The cleanup was only needed because the XBRL test imported 722 words, 2875 triples and 2396 values into a database
that the other tests share. Planned in `pending.md` and done on request. Before the change `reset_db.php` and
`test_re_pin.php` created a database without the XBRL rows, whereas `test.php` and `test_part.php` tested with them,
so a re-pin could pin ids of a database that no test run has.

### 7. Extra history rows in the workflow snapshots (2026-11-16)

Errors: the `*_confirmed` pages of wf31 to wf33 and wf35 showed an extra history row "deleted 123456" of
07-06-1997.

The cleanup resets the sequences, so the next run creates the test phrases and their value with the same ids, and
the change log of the value deleted in the run before shows up in the history of the new one.

### 8. Live wikipedia and wikidata reads without a network

Errors: the live pages of Geneva and Health could not be read, and wf42, wf45 and wf47 failed with follow-up errors.
"No value of Zurich (city) is imported" came from the cleanup of an empty entity json.

The reason was no code issue: "getaddrinfo ... Temporary failure in name resolution". The machine had lost its
network for a short time, and with it its dns servers. Asked by the developer: skip the workflows after a failed
read, use a copy of the pages in the repository as fallback and add a "connection missing" message. The seed gap is
on purpose: Geneva has no description and health no wikidata reference, so that the workflows have something to
take over; their cleanups must bring the gap back after each run (`testing.md`).

### 9. The XBRL import in the reset "hangs"

Errors: the reset stopped after "select the triples to add of 2875 triples", later after "rename the link
conflicts" and "update the changed of 2875 triples", and went on after about 1750 s.

There were three separate causes, found one after the other:
- the throttle hid the step: a step that starts within a second of the last line was not shown, so the log ended at
  the step before
- quadratic list adds: `sandbox_list_named::add_by_name_direct` and `add_by_key` marked the name hash dirty after each
  add, so the next lookup rebuilt it from all names
- 4^depth fills: `triple::fill` filled the from and to phrases again after `sandbox_link::fill` had filled them
  already, so each level of nested triples cost four times the level below; this hit the second, no-update import of
  `xbrl_write_tests` in `sandbox_list_named::update`

A wrong guess on the way: the php garbage collection. A probe showed 0.3 s for 6 million objects.

### 10. Ids in a list of ids in the workflow snapshots

Errors: wf31 snapshots differed in ",3377" against ",3272". `url_test_base::normalize_ids` replaced only an id after
`=`, in quotes or after "id ", so the second id of a list such as `pl=3271,3272` changed with every shift of the test
word ids.

### 11. The id shift after the XBRL import works in the reset

Errors: `type_lists.json` had 3419 instead of 3290; then the coverage doc, the user message doc and the
`unit/value/list.csv` baseline, which had 2401 new rows of source 527 "ABB XBRL files for 2013" and 1608 rows with a
new group id.

Until the fix of request 9, the reset never finished the XBRL import, so all baselines had been made without its
rows. Now the reset adds 129 words before the test words, and every later id moves. No fix needed: the run rewrote
the files. All old values were checked to be unchanged except for the group id.

### 12. Test values left by a stopped run

Error: after the final cleanup the test words "Share", "Earnings", "Increase" and "CHF" were "still used" and only
hidden.

`test_db_load::add_value` registered a value for the cleanup only if it added it. `a_selected_test` does not
recreate the database, and a ^C during the long XBRL write test skips the final cleanup. The next run found the
values, did not register them, and so could never remove them or their words.

### 13. A unit test that expected a wrong behaviour

Error: "... and reported as a double entry" in `triple_list_tests`.

The test, written in request 9, assumed that `add_by_name_direct` reports a repeated name. For a triple without an
id it skips the repeat silently, and that is right: `triples_to_add_to_db` adds the same from or to triple once for
each triple that links to it. An error would stop the import.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/import/import_convert_xbrl.php` (1, 3) | `CONCEPT_TRIPLES`, `unique_triples`, `facts_json` | same functions | "order backlog", "shares outstanding" and the other concepts that the seed defines as a triple are in `CONCEPT_TRIPLES` with the link of the seed; an unnamed kept triple takes the name of the removed one; the unit is part of the key of a fact, so a fact in two currencies is no conflict |
| `cfg/verb/verb.php` (2) | `save` | `can_be_renamed_by` | only an admin or the system user can rename a verb; checked before the duplicate name check |
| `test/php/unit_write/horizontal_write_tests.php`, `test/php/utils/test_base.php` (2) | `assert_true($diff->is_ok())` | `assert_msg($diff)` | the reload checks print the difference instead of hiding it |
| `test/php/unit_write/xbrl_write_tests.php` (3, 6) | `run`, `cleanup` | `run` | first the cleanup got its own message; then the cleanup was removed: the test converts the zip, checks that the convert reports no conflict and imports the json again in the no update mode, expecting no change |
| `cfg/helper/config_numbers.php`, `shared/const/words.php`, `GDP.json`, `portfolio_split_calculator.json` (5) | `INTERNAL_COMMENTS`; `*_COM` of "default", "average", "max", "most", "relevant", "year" | same | the setup uses the json text (decided by the developer); the currency rates use the text of `currencies.json` |
| `test/php/unit_write/all_unit_write_tests.php` (6) | `run_db_recreate` | same function | imports the converted json as the system user right after `setup_db`, as the last import so that the pinned ids stay; every development reset uses it (`test.php`, `reset_db.php`, `test_re_pin.php`, and `reset_db_forced.php` through its include of `reset_db.php`); a pod is set up by `setup_db` alone and never gets the XBRL data |
| `test/php/utils/test_cleanup.php` (7) | `cleanup_test_values` | same function | calls `cleanup_change_log_of_deleted` after each delete, which removes only the log of a row that cannot be loaded any more |
| `test/php/unit_workflow/ref_url_tests.php`, `test/php/unit_write_workflow/ref_write_url_tests.php`, `test/php/const/files.php` (8) | `assert_wikipedia_page`, `cleanup_wikidata_import`, `run` | `wikipedia_page_tests`, `wikidata_import_live` | each run with network writes a copy of the page (`import/wikipedia/cache/live_<page>.json`); if the read fails, the page test uses the copy and names the reason in a step line; wf42, wf45 and wf47 are then skipped, but their cleanups still run |
| `cfg/import/import_wikidata.php`, `cfg/import/import_wikidata_preview.php`, `cfg/ref/ref_link_wikipedia.php`, `shared/enum/messages.php`, `translations/en.yaml`, `de.yaml` (8) | `request_json`, `preview_of_key`, `summary` | `request_clear`, `request_failure`, `no_answer`, `missing_host` | a request without any http answer gives the message `connection_missing` with the host instead of "the page cannot be read"; the log names the reason of a failed request |
| `cfg/word/triple_list.php`, `cfg/helper/data_object.php`, `cfg/import/import.php` (9) | `save_with_cache`, `triples_to_add_to_db`, `rename_link_conflicts`, `save`; `display_progress` | `step_note`, `on_note_timer`, `gc_note` and their helpers | a note for each step; a pcntl timer shows a skipped note after a second and repeats the note of a running step every ten seconds with the functions that run; a long garbage collection is named |
| `cfg/sandbox/sandbox_list_named.php`, `cfg/phrase/phrase_list.php`, `cfg/phrase/term_list.php` (9) | `add_by_name_direct`, `add_by_key`, `add_direct`, `name_pos_lst` | `name_index_keys` | `add_direct` updates the name hash through the new hook, which the phrase and term lists override for the plural and the other name forms, so an add no longer rebuilds the hash |
| `cfg/word/triple.php` (9) | `fill` | same function | the from and to fill is left to `sandbox_link::fill` |
| `test/php/unit_workflow/url_test_base.php` (10) | `normalize_ids` | `normalize_id_lists`, `normalize_list_ids` | each known id of a list is replaced, also with the separator `%2C`, but never a part of a longer number |
| `test/php/create/test_db_load.php` (12) | `add_value` | `has_test_word_name` | a value that is already there is registered for the cleanup if one of its words is a test word; a value of only seed words never is, so a seed value is never deleted |
| `test/php/unit/triple_list_tests.php` (13) | "... and reported as a double entry" | "... and the repeat is no error" | the test expects no message for a repeated triple without id |
| unit tests: `coding_rule_tests`, `import_tests`, `phrase_list_tests`, `triple_list_tests`, `triple_tests`, `wikidata_tests`; `test/php/create/test_triples.php` | — | new tests; `triple_nested` | the id lists, the step note, the plural of an added word, the add by name, the fill of a nested triple within the time limit, the request failure and the missing connection, the test value registration |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `test/resources/unit/*/list.csv`, `test/resources/api/type_lists/type_lists.json` (5, 6, 9) | — | — | generated; the swapped names of `INTERNAL_COMMENTS` move the id of every later word by three and need a re-pin (`words::VERBS_ID` 835); the XBRL rows of the reset move the ids of the test words by 129 |
| `test/resources/import/xbrl/.../abb-2013-xbrl_fileset-20131231.json` (1, 3) | — | — | written by the converter; the reset imports the json on disk, so after a converter change the json must be converted once (a `test_part.php` run) before the reset and the re-pin |
| html snapshots under `test/resources/web/html/` (6, 10) | — | — | the pages with a phrase search list change once, then stay; the normalized id lists change the workflow snapshots once |
| `test/resources/import/wikipedia/cache/live_Geneva.json`, `live_Health.json` (8) | — | new files | written by each run that reaches wikipedia, so they change with the live page |
| `docs/code_test_coverage.md`, `docs/code_functions_all.md`, `docs/code_user_message_exceptions.md` | — | — | generated; the new public functions and unit tests and the moved line numbers |
| `docs/llm/testing.md` (8) | "Two seed phrases miss data on purpose" | same section | the seed gap must be back after every run, the live copies and the skips |
| `docs/llm/pending.md` | "planned change: load the XBRL fileset with the test database setup" | same section | removed after the confirming run of `test_part.php` with the XBRL test switched on in `a_selected_test` |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `cfg/helper/config_numbers.php` (5) | `INTERNAL_COMMENTS` of "lists", "most", "relevant" | swapped back | name and description were swapped, so the setup created three words named after the descriptions (ids 768 to 770 of `unit/word/list.csv`) and never set the real words |
| `test/resources/import/wikipedia/refresh_*.json` (8) | — | restored by the developer | an unreadable page wrote empty values over the expected json of the last run; the compare is now skipped if the page cannot be read |
| `test/resources/web/html/views_by_object/word/word_default_word_212.html`, `word_default_word_228.html` (12) | — | rewritten by the run | expected files written by an earlier run while the leftover test values were in the database (the inhabitants page with Switzerland, Zurich "... and 149 more"); after `reset_db.php` the run wrote them back to the committed version |
| `cfg/verb/verb.php` (2) | `can_be_renamed_by` | same function | found by the review: it called `is_admin()` on the user of the message on every verb update, which can be null; the user is now checked only for a rename and with `?->` |
| `cfg/sandbox/sandbox_list_named.php` (13) | doc comment of `add_by_name_direct` | same function | said that `$msg` reports a double entry; now names the silent skip of a repeated object without id |
| `cfg/sandbox/sandbox_link.php`, `cfg/helper/db_object_seq_id.php`, `test/php/unit/triple_tests.php` (9) | `clone_all` of the base; `sql_insert` | `clone_all`, `clone_reset`, `clone_linked` | found by the review: the no-update loop of `sandbox_list_named::update` fills a `clone_all` of each triple, but the clone shared its from and to phrases with the import and the database objects, so the fill changed them too; `clone_all` now clones the linked objects as its doc comment promises, `clone_reset` and `sql_insert` skip that copy, because the reset removes the links anyway; a unit test checks that the fill of a clone leaves the original unchanged |
| `cfg/sandbox/sandbox_list_named.php`, `test/php/unit/triple_list_tests.php` (9) | `names` | same function | a side effect of the hash update of `add_by_name_direct`: `names()` returned the keys of a clean name hash, which is keyed by the name without the excluded objects, so `names(true)` of the import gave an empty name for an excluded triple, which was then never loaded, updated or deleted; `names()` now always reads the objects, and a unit test checks the excluded triple |
| `cfg/word/triple.php`, `test/php/unit/triple_tests.php` (9) | `import_mapper` "finally generate the name if needed" | same function | the real cause why the unused test triple was not deleted, found after the `names()` fix did not help: the parent mapper sets `excluded` before the name, so the check `name() == null` was true for every excluded triple and the name was replaced by the generated name, which is empty without from and to; the undo triple was therefore not even added to the import list; the check now uses `name(true)`, and a unit test imports an excluded triple with only the name |
| `.gitignore` | — | `/cache/cfg_3.json` | found by the review: a runtime cache like `cfg_1.json` and `cfg_2.json` |
| `cfg/db/sql_db.php`, `cfg/import/import_file.php`, `cfg/const/files.php`, `messages/base_data/abb-2013-xbrl_fileset-20131231.json`, `test/php/unit_write/all_unit_write_tests.php`, `test/php/unit_write/xbrl_write_tests.php` (6) | `run_db_recreate` | `setup_db`, `import_xbrl_data`, `XBRL_ABB_2013_FILE` | decided by the developer on 2026-11-16, replacing "never on a pod" of request 6: with the XBRL data only in the development reset, the workflow test files differ depending on whether `reset_db.php` and then `test_part.php`, or `test.php` has run before; every pod now imports a copy of the converted json in the main resources as the last import of `setup_db`, and `xbrl_write_tests` checks that the copy is the converted fileset |

## Lessons learned by llm

1. A repeated error after a fix was twice an old run; the modification time of the converted json showed when the
   run had converted.
2. An independent action must not get a message that an earlier step has made not ok, because the following actions
   are skipped silently; this was the cause of all leftovers of request 3.
3. Add the check that names the failing row (reload after delete) early instead of reasoning about the code.
4. Before writing an assert that expects a list of findings, check the findings in the source data; an expected list
   can make a bug the expected result.
5. An expected file written from a database with leftovers must not be committed; commit an expected html page only
   from a run after a reset.
6. The message of a no update import is `description is <import> instead of <database>` (`$dbc->diff_msg($sbc)`);
   the error stayed after the fix, because the database still had the old text, and only a reset writes it again
   (`test_part.php` never resets); the same error was pasted three times before this was clear.
7. `unit/word/list.csv` is the expected state of the database, not its seed.
8. A search for the callers of a function misses a script that includes another script: `reset_db_forced.php` was
   first reported as an exception, but it includes `reset_db.php`.
9. Test data that many tests see belongs to the reset, not to one write test with a cleanup.
10. Read the reason of a failed http request (`import_wikidata::request_failure`) before reading the code.
11. A throttled log can hide exactly the step that hangs; each step needs a start note that is shown later if
    skipped.
12. A "hang" may be a fast but repeated function; the functions named by the timer (`note_location`) showed the
    recursion, a guess about the cause did not.
13. A fix that makes a step work for the first time changes every expected file after it; compare the content without
    the ids before looking for a bug.
14. Before writing an assert, check what the callers need, not what the doc comment says.

## Lessons learned by human
