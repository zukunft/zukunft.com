# wikipedia in line (2026-11-11)

The prompt "wikipedia link" of `docs/llm/pending.md`: if the sync with wikipedia does not suggest any update, the
user gets a message like "description and wikidata link are in line with wikipedia page from <time>. No update
needed". The tables were written before the first code edit; the line numbers are those of the start state
(commit `52eb329b1`); the files of the plan are unchanged since commit `8b60ac85d`, which the prompt names.

## Request

The refresh icon of a wikipedia reference calls `frontend::url_with_wikipedia_refresh` (`web/frontend.php:3262`),
which loads the stored phrase and the backend answer with the description and the references taken from wikipedia
(`phrase::wikipedia_confirm_url`, `web/phrase/phrase.php:982`) and builds the confirm page in
`phrase::confirm_url_of_refresh` (`:1028`). It stays on the page of the phrase only if wikipedia names no wikidata
item, else it always returns the confirm page, so a refresh without any difference shows a confirm page without a
change. The time of the page is the `timestamp` of the summary json (the time of the last revision of the page,
e.g. `2026-11-07T14:48:15Z` in `import/wikipedia/cache/summary_Geneva.json`), which the backend reads in
`ref_link_wikipedia::take_over` (`cfg/ref/ref_link_wikipedia.php:200-209`) but does not pass on.

The target: the backend passes the time of the page with the refresh answer, and a refresh whose description and
wikidata id are both equal to the stored ones stays on the page of the phrase with the info message and the time.
The developer has decided (2026-11-11): the time is shown in the format of the frontend config
(`$ui_sys->cfg->date_time_format()`, today `d-m-Y H:i`) and in the time zone of the pod, not as UTC.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/ref/ref_link_wikipedia.php`, `cfg/ref/ref.php`, `web/ref/ref.php`, `shared/json_fields.php`, `test/php/unit/wikipedia_tests.php`, `test/php/unit/ref_tests.php` | the summary fields 70-75, `take_over` 200-209; `reset` 228-241, `api_json_array` 490-495, `last_update` 754-768; 136-138, `api_mapper` 301-305; `LAST_UPDATE` 111 | the consts `WP_TIMESTAMP`, `WP_TIME_FORMAT` and `WP_TIME_ZONE`, a public `page_time(array $summary): ?DateTime` set by `take_over` on the wikipedia reference once the description is taken over; a not stored backend reference field `page_time` with `set_page_time` / `page_time`, reset by `reset` and written to the api json as `json_fields::PAGE_TIME` in the ATOM format only if set; the frontend `ref` reads it as the api text like `last_update` | the time of the wikipedia page reaches the frontend with the refresh answer; `ref::last_update` (`cfg/ref/ref.php:203`) stays the time of the last refresh by zukunft.com, which triggers the next refresh job; ATOM instead of the `Y-m-d H:i:s` of `last_update`, because the frontend converts the UTC time of wikipedia into the time zone of the pod; the tests: `page_time` of the frozen Geneva summary is `2026-11-07T14:48:15+00:00`, a summary without `timestamp` and one with an invalid month give null; the api json of a reference with and without the page time, and the frontend reference that reads it |
| `web/phrase/phrase.php`, `web/ref/ref.php`, `shared/enum/messages.php`, `translations/en.yaml`, `translations/de.yaml`, `test/php/unit_ui/reference_ui_tests.php`, `test/php/unit_workflow/ref_url_tests.php` | `confirm_url_of_refresh` 1016-1045; new; 240-244; 455-459; 1490-1494; 279-282, 327-335; `wikipedia_refresh_workflow` 388-495 | the named check `is_in_line_with_wikipedia($old, $new)` and the page of the phrase with the info message (`add_in_line_message`); the frontend `ref::page_time_text($msg)`, which converts the page time into the time zone of the pod and the format of the frontend config; `msg_id::WIKIPEDIA_IN_LINE` with `msg_id::VAR_NAME` and `msg_id::VAR_TIME`, and `msg_id::WIKIPEDIA_IN_LINE_NO_TIME` for an answer without page time, each with the en and de text; the tests | a refresh without any difference shows the page of the phrase with the message instead of an empty confirm page; no `add_info_with_vars` is needed, because `$msg->add($msg_id, $var_lst, true)` already adds a message with vars without changing the status (like `frontend.php:478`); the description is compared with `===`, because `==` compares numeric texts as numbers; the tests: the stored phrase as both sides gives the page of the phrase with the message without time, the answer with a page time names the time, its negatives: only the description differs gives the confirm page with the description, only the wikidata id differs the confirm page with the reference row; the tests that used the stored phrase on both sides for a confirm page without reference change ("an unchanged wikidata id is not confirmed again", "a confirm page without reference change lists no reference", "a confirm that changes only a reference") now use the answer that differs only in the description; the write workflow `wikipedia_refresh` (wf42) gets a second refresh after the confirmed step, which shows the message |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `test/resources/web/html/workflow_write/wikipedia_refresh_wf42/` | — | — | no change: the second refresh asserts the url and the message only, like the first refresh, because the live text can change |
| `test/php/unit_ui/reference_ui_tests.php` | `popup_impact` 332-335 | same test | the confirm that changes only a reference is now the answer that differs only in the wikidata id, so the merge of the reference rows of `$cnf_url` into the url of the stored phrase is no longer needed |
| `test/resources/api/` messages with a reference | — | — | no change: the page time is written to the api json only if it is set, which only the refresh does |
| `web/frontend.php` | `url_with_wikipedia_refresh` 3262 | — | no change: it shows whatever url `wikipedia_confirm_url` returns, the page of the phrase with the message included |
| `web/user/user_message.php` | `add_warning_with_vars` 223 | — | no change: the info message uses the existing `add` with `$ok = true` |
| `api/word/index.php`, `api/triple/index.php` | the refresh 127, 87 | — | no change: a refresh that has failed in the backend is answered with its message only, so `confirm_url_of_refresh` is reached only if the description has been taken over and a stored description can never be shown as in line by mistake |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md`, `docs/code_user_message_exceptions.md` | — | — | generated; the new functions and the new message |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | switched on: `wikipedia_tests`, `ref_tests`, `reference_ui_tests`, `ref_write_url_tests` (the live wikipedia page) and `coding_rule_tests` (the new message and its translations, already on); `group_tests`, `value_tests`, `result_tests` and `import_tests` stay on as found |
| `docs/llm/pending.md` | the prompt "wikipedia link" | — | removed once done |

No import and no id changes, so a `test/test_part.php` run is enough, then the full `test/test.php` before the
commit.

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `docs/code_object_name_exceptions.md` | 39 | 39 | `$ref_ui` is listed as a new var name for a `ref`: the frontend reference of the page time tests in `ref_tests.php` is named after its class alias `ref_ui`, which the report counts as a deviation from `$ref` |
| `test/resources/web/html/workflow_write/wikipedia_refresh_wf42/wf42_show.html`, `wikidata_refresh_wf45/wf45_show.html`, `ref_row_confirm_wf44/*_save_confirmed.html` | the change log tab | +65 rows and more | the change log tab of the snapshots shows the description overlays and their cleanup of the earlier `test_part.php` runs, because the database is not reset between two such runs; `wf45_show.html` also shows a description change and `ref/list.csv` a wikidata reference `Q12147` of health by the user 11 (heang), which no test writes; written over by `files::AUTO_UPDATE_TEST_FILES = true`, so not caused by this change and to be regenerated by the reset run |
| `test/resources/unit/ref/list.csv`, `user/list.csv` | 789, 12 | 790, 12 | the reference 796 of the user 11 above and the ip address of the user 11 after a login on the local pod: database state, not this change |
| `test/resources/unit/group/list.csv`, `value/list.csv`, `result/list.csv` | — | about 2'800 rows less | regenerated by `group_tests`, `value_tests` and `result_tests`, which were already switched on, from a database that has not been reset since the prime group id change of `213a5231d`; not this change, and not to be committed from this run |

| `test/php/utils/test_base.php`, `test/php/unit_workflow/ref_url_tests.php` | `delete_change_log_of_obj`, `cleanup_ref_overlays`; `cleanup_wikipedia_refresh`, `cleanup_wikidata_refresh`, `cleanup_ref_rows` | new `cleanup_change_log_of_user`, `is_change_of` | bug fix found by the second `test_part.php` run (wf42 failed again with two more change log rows): the cleanups removed the overlay of usr1 on the rows of the base data, but not its change log, against the rule that a cleanup removes the change log of the test row it deletes; now the entries of the test user on these rows go too, never those of the system user (the history of the base data) or of a real user |
| `test/resources/web/html/workflow_write/ref_row_confirm_wf44/*_save_confirmed.html` | the change log tab | 4 or 5 rows instead of up to 14 | regenerated by the run after the fix: the committed baselines had recorded the overlays of the earlier cases and of wf43 (deleted overlays, protection changes of usr1), so they had captured the bug; now each page shows the four rows of the base data and, for a case with description, only its own description change; wf42 and wf45 are again equal to the committed baselines; for the developer to review |
| `shared/const/refs.php`, `test/php/unit_workflow/ref_url_tests.php` | — | `SYSTEM_TEST_ROW_ADD_ID`, `after_action` | the next run still failed on the `*_save_confirmed` pages of the wf44 add cases, now only by the id of the reference that the confirm adds (807, then 812), because each run gives it a new id; the `after_action` hook of `url_test_base` learns this id after the confirm and `wf_norm_ids` replaces it by the fixed snapshot id 993, like `SYSTEM_TEST_ADD_ID` of the add workflow; the add pages then show `id=993` instead of the 792 of the committed baselines |

The code of this change has caused no unexpected change: the `test_part.php` run has passed, and the regenerated
files above show the state of the local database. They need the reset run (`/test/reset_db.php`, then
`/test/test.php` twice) before they can be compared with the committed baselines.

## Lessons learned by llm

- The plan had expected a new `add_info_with_vars`; reading `Message::add` first showed that `$ok = true` already is
  the info with vars, so the shared code is read before a new function is planned.
- A snapshot that shows the change log of a phrase also shows every earlier run on a database that has not been
  reset, so a write workflow change predicts the snapshot change only for a run after a reset.

## Lessons learned by human
