# http_old phase 3 (2026-11-13)

Not started: before the first code edit the developer gave all remaining parts a low priority, moved them to the
end of `docs/llm/pending_prio_2.md` ("## the remaining parts of the retired http_old scripts") and deleted all
scripts of `http_old/`. The parts below stay as the plan for when a section is taken up; their "deleted" rows of the
scripts are already done.

Phase 3 of the prompt "deprecate http_old" of `docs/llm/pending.md`: the remaining 10 scripts of `http_old/`, step 8
(the sections of `docs/llm/pending_prio_2.md` that name a retired script), step 9 (delete the scripts and the legacy
code that only they used) and step 11 (remove `http_old/` and the `TODO clean up http_old` line of `docs/todo.md:13`).
On request of the developer the parts are in one file; each part is one change with its own commit and at most three
expected changes. The tables were written before the first code edit; the line numbers are those of the start state
(commit `4a78a1c5a` with the working tree of 2026-11-13: the eight scripts marked "can be removed",
`error_log.php`, `source_edit.php`, `verbs.php` and `value.php` deleted, the guard of `source::del_links` changed).

Target of the phase: fewer lines. Every part ends with the delete of its scripts; a feature that a part does not
build is written down as a gap in `pending_prio_2.md` instead of rebuilt. The line balance of each part is reported
with its commit. The sys_log entry by the url id of `http_old/error_log.php` is declined by the developer, because the
user never sees a database id if it can be avoided (`docs/llm/frontend.md`), so the script is deleted without a
replacement. The message of `http_old/source_edit.php` for an edit view without an id has no priority, so it is
written down at the end of `pending_prio_2.md` ("## an edit or del view without an id") and the script is deleted.
The verb list of `http_old/verbs.php` has no priority either, so it is written down at the end of `pending_prio_2.md`
("## the verbs view lists the verbs") and the script is deleted. Opening a value by the names of its phrases
(`t=zurich,inhabitants` of `http_old/value.php`) is not needed any more, so `value.php` is deleted without a
replacement.

## value input

### Request

`pending.md` "#### value_edit.php and value_add.php", steps 1 and 2: the add button of a value table
carries the phrases of the row, and the value accepts the number in the format of the user and refuses an empty
number. Open decision for the developer: `value_base::convert` is removed (the conversion happens before the
backend) or used by the backend api for a number sent as text.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `web/html/button.php` | `add_value` 270-296 (the prefill `id_url_long` switched off at 286) | same function | the url carries the phrase ids of the row as `url_var::PHRASE_LIST`, like the 'more details' link (`ui_select.php:510`) |
| `web/sandbox/sandbox_value.php`, `web/value/value.php`, `cfg/value/value_base.php` | `url_mapper` 165, the numeric check 192-199; —; `convert` 1786 | the check after taking out the thousand separator and the decimal point of `$ui_sys->cfg`; new `input_valid`; `convert` by the decision | `1'000` gives 1000, a text that is still no number gets `msg_id::URL_VALUE_NOT_NUMERIC`, an empty number of the edit view gets the message of the old page instead of a save |
| `docs/llm/pending_prio_2.md` | `### value add view`, `### value edit view` | same sections | the done parts go, what stays open is named |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/php/unit_ui/value_ui_tests.php`, `src/test/php/unit/` | — | new test functions | the button with and without phrase list; the url mapping with `1'000`, `1'000.5`, ` 12 ` and `abc`; the edit view with an empty number and its negative |
| `src/test/resources/web/html/` value table snapshots, workflow `add_value` | — | — | regenerated with the add url of the row; the workflow gets a number with a thousand separator |
| `shared/enum/messages.php`, translations | — | new case and texts | the empty number message in en and de |

## value phrases

### Request

`pending.md` "#### value_edit.php and value_add.php", step 3. Decided by the developer: changing the phrases of a
value creates a new value and deletes the old (like a triple with another from, verb or to). After this part
`http_old/value_edit.php` and `value_add.php` are deleted.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `web/value/value.php`, `web/component/execute/system_form.php`, `messages/system_views.json` | `url_mapper` 153; the group name field (`url_var::GROUP_NAME`); the views `value_add` / `value_edit` | the phrase selector with add and remove, and the time selector of prio 2 `### value edit view` | the confirm excludes the value of the old phrases for the user through the logged `del()` and creates or updates the value of the new ones; a change to the phrases of another existing value tells the user instead of overwriting it |
| `http_old/value_edit.php`, `value_add.php` | whole files | deleted | the gaps that the developer does not want are written into `pending_prio_2.md` |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/php/unit_workflow/`, `src/test/php/unit_write_workflow/` | — | new workflow `change_value_phrases` and its write version | on the value of the `add_value` workflow, never on a seed value; the negative with the phrases of another value |
| `src/test/resources/web/html/views_by_id/value/` | — | — | the add and the edit view get the selector |

## user view

### Request

`pending.md` "#### user.php": the user view (`views::USER_ID`) shows the settings form instead of
`'user_setting placeholder'`, the latest changes, the import link for `user::can_import` and all open issues for an
admin; then `http_old/user.php` is deleted (its link to `/http/import.php`, `http_old/user.php:169`, goes with it).
Open decisions for the developer: `views::USER_EDIT` (`shared/const/views.php:612`) stays a code id of the views to
be created or becomes a real view; the confirmation of a changed email is part of this change or a gap of its own.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `web/component/execute/system_page.php`, `web/user/user.php`, `web/frontend.php` | `user_setting` 807-810; `form_edit` 945 | `user_setting` with the user of the view; `form_edit` posting to the user view with the confirm step; the confirm write | the own user (an admin any user) changes the settings like the signup writes a new user |
| `web/user/user.php`, `web/component/execute/system_page.php`, `messages/system_views.json` | `dsp_changes` 977, `dsp_errors` 987; the views `user` 9628 and the own changes view (`CHANGE_LOG_USER_ID`) | same functions; the components | the latest changes with a link to the own changes view with paging, the link to `views::IMPORT_ID` for `can_import`, all open issues for an admin and the own issues for any other user |
| `http_old/user.php` | whole file | deleted | the page is covered |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/views_by_id/user/123_user_9.html`, the own changes snapshot | — | — | regenerated with the form, the changes and the links for a normal user and an admin |
| `src/test/php/unit_write_workflow/` | — | new write workflow | changes the first name of a test user (never of a seed user), confirms and checks the database |
| `test/resources/unit/component_link/list.csv` and the type list baselines | — | — | new components shift the component link ids: `/test/reset_db.php` and the re-pin |

## result explain and formula test

### Request

`pending.md` "#### formula_result.php and formula_test.php": the result explain view (`views::RESULT_EXPLAIN_ID`)
explains a result and the formula test view (`views::FORMULA_TEST_ID`) shows the calculation step by step; both are
placeholders today (`system_page::result_explain` 783, `formula_test` 789). Open decisions for the developer: the
leading and the time phrase of the old url, and the admin only 'user' parameter of the old formula test. After this
part `http_old/formula_result.php` and `formula_test.php` are deleted.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `api/result/index.php`, `web/result/result.php`, `web/component/execute/system_page.php` | the GET; `explain` 441-538 (calls the missing `api_obj()` / `dsp_obj()`); `result_explain` 783 | the explanation on request (new url var); `explain` rewritten for the answer; the view component | the formula with its expression, the source phrases and per element group the names, the assumed time and the values, each with a link; a message for a missing or unknown id |
| `api/formula/index.php`, `web/component/execute/system_page.php`, `web/formula/formula.php` | the GET; `formula_test` 789; the dead `dsp_edit` path (`dsp_test_and_samples`, `dsp_used4words`, `dsp_hist`, `dsp_hist_links`) | the calculation without save (`cfg/formula/formula.php::calc` 1573) within the response time; the view component with the 'more details' level; the dead path removed | the queue is never calculated in the request; the 'refresh results' button creates a `job_types::FORMULA_UPDATE` job through the confirm flow |
| `http_old/formula_result.php`, `formula_test.php` | whole files | deleted | the pages are covered |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/main/resources/openapi/zukunft_com_api.yaml`, served copy `/var/www/html` | — | — | the new parameters of `api/result` and `api/formula`; the api tests call the served checkout |
| `views_by_id/result/63_result_*.html`, `views_by_id/formula/59_formula_formula_test.html` | — | — | regenerated with the explanation and the steps |
| `src/test/php/unit_api/`, `unit_ui/`, `unit_write/` | — | new test functions | the explanation of a seed result, the steps of a seed formula compared with the stored result and that nothing is written, the refresh creates one job and its negative for a user without the right to change the formula |

## view menu and add

### Request

`pending.md` "#### view_add.php, view_select.php and view_edit.php", steps 1 and 2. Open decisions for the developer:
the components linked to a view block its delete or are unlinked with it; the sample word is still wanted.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `web/view/view.php` | — (no `input_valid`; the word has it at `web/word/word.php:246`) | new `input_valid` | the delete of a view that is the default view of a word or a triple is refused with `msg_id::DELETE_IN_USE` |
| `web/sandbox/sandbox.php`, `src/test/php/utils/test_base.php`, `shared/types/component_types.php` with its three seed places | `menu_view_items_ui` 314; `assert_view_html`; `VIEW_TAB_BOX_COM` 495 | same | per alternative view a 'design the view' and, for a user who may delete it, a 'delete the view' entry; the test render passes the type cache and the page object; the description of the view tab box says that it shows the change log |
| `web/frontend.php` | the confirmed add of a view; `url_has_object_values` 2863 | same | after the add the view edit view of the new view opens; by the decision the sample word presets a term selector with a preview |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/resources/web/html/views_by_object/` | — | — | the 25 snapshots show the alternative views with their entries |
| `resources/db_code_links/component_types.csv`, `messages/base_views.json`, `system_views.json` | the description of the view tab box | same rows | the description reaches the database only with their import (the full run or `a_selected_test::SELECTED_IMPORT_FILES`) |
| workflow `add_view` (wf22) | — | — | ends on the edit view of the new view |

## view designer

### Request

`pending.md` "#### view_add.php, view_select.php and view_edit.php", steps 3 and 4. Open decisions for the developer:
the order number of the link edit view is enough instead of move buttons; the render without bootstrap
(`UI_USE_BOOTSTRAP` false) is kept. After this part `http_old/view_add.php`, `view_select.php` and `view_edit.php`
are deleted.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `web/component/execute/ui_list.php` (the component list of the view edit view), `web/frontend.php` | the component list | an add button to `views::COMPONENT_ADD_ID` with the view preset; by the decision a move up and down per row | the confirmed add links the new component at the last position; a move writes the order number of the two links through the confirm step |
| `web/view/view.php`, `web/view/view_exe.php`, `shared/api.php` | `dsp_navbar_no_view` 301, `dsp_navbar_html` 354, `dsp_navbar_html_no_view` 452, `dsp_edit` 475, `linked_components` 585, `selector_page` 691; `dsp_edit` 491, `linked_components` 604, `selector_page` 713; `DSP_COMPONENT_*` 183-187 | removed (the bootstrap-free render by the decision) | legacy designer code without caller, whose buttons link to retired pages |
| `http_old/view_add.php`, `view_select.php`, `view_edit.php` | whole files | deleted | the pages are covered or their gaps are written down |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `docs/code_functions_all.md` and the other generated `docs/code_*.md` | — | — | the generated function list loses the removed functions |
| `src/test/php/unit_workflow/` | — | new workflows | create a component from the edit view of a test view, move a component; the negatives at the first and the last position |

## admin jobs

### Request

`pending.md` "#### async_process.php and calculate.php" (step 6 of "Other", also part of the admin dashboard
prompt): an admin starts the import of the system data and the recalculation of all results as jobs and sees the
progress on the job control view, never as a long running request. Open decision for the developer: the setup sets
its `BASE_IMPORT` job to done after its own import (`cfg/db/sql_db.php:1254`) or creates none. After this part
`http_old/async_process.php` and `calculate.php` are deleted.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/system/job.php`, a new handler class like `job_cache_refresh`, `cfg/db/sql_db.php`, `api/job/index.php` | `exe` 675-698; —; 1254; PUT only | the `BASE_IMPORT` branch; the handler; the setup job by the decision; a POST for an admin and the job types an admin may start | the runner imports the system data once, never a second time after the setup |
| `shared/types/job_types.php`, `job_type_list`, `db_code_links/job_types.csv`, `cfg/system/job_runner.php`, the two `zukunft_structure.sql`, `cfg/db/db_check.php` | `FORMULA_UPDATE` 49; `run` 109, `run_job` 197; —; `db_upgrade_0_0_4` | a new type e.g. `RESULTS_UPDATE`; the runner continues a working job of this type; the fields `progress_done` and `progress_total` in the setup sql and in `db_upgrade_0_0_4` (no version raise) | the handler calculates the formulas block by block (`formula_list::calc_blocks` 778) and stops before the time budget, the next run continues; the same handler runs a `FORMULA_UPDATE` job of one formula |
| `web/component/execute/system_page.php`, `http_old/async_process.php`, `calculate.php` | `process_progress` 987, `process_list` 993 | the progress in percent, the list of the jobs (reusing `all_jobs` 958) and the admin buttons; deleted | a user who is no admin sees neither |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `resources/db_code_links/change_fields.csv` | — | — | only if the progress fields are logged; they are helper fields of a job and change no result, so probably not, like `users.verb_id` (`docs/llm/log/http_old_phase2_2026-11-12.md`, "Unexpected changes") |
| `src/main/resources/openapi/zukunft_com_api.yaml`, served copy `/var/www/html` | `/job` | + POST | the api tests call the served checkout |
| `test/resources/db/job/` sql fixtures, the job type baselines | — | — | the new fields and the new type change the job statements and the type list: `/test/reset_db.php` and the full run |
| `views_by_id/` job control snapshot | — | — | regenerated for an admin and for a user |

## finish

### Request

Steps 9 and 11 of "Other": after the parts above, remove the legacy code that only the deleted scripts used (after a
search for every caller, e.g. `btn_yesno`, `dsp_go_back`), keep every `include_once` that might still define a class,
remove `http_old/` and the `TODO clean up http_old` line, and remove the prompt "deprecate http_old" from
`pending.md`.

### Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `src/main/php/web/` (the functions found without caller) | — | removed | the legacy code of the deleted scripts goes with them |
| `http_old/`, `docs/todo.md` | the folder; line 13 | removed | the target of the prompt |
| `docs/llm/pending.md`, `docs/llm/pending_prio_2.md` | "## deprecate http_old"; the sections of the retired scripts | removed; only the written down gaps stay | the prompt is done |

### Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `src/test/php/unit/coding_rule_tests.php` | `retired_script_link_tests` 2701 | same | the check of `src/` for links to retired scripts must still pass; it reads `http/`, not `http_old/` |
| `docs/code_functions_all.md` and the other generated `docs/code_*.md` | — | — | the generated lists lose the removed functions |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
