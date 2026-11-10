# ref rows form (2026-11-10)

Steps 1 "an unchanged row is no change", 2 "the component that writes the rows" and 3 "the rows in the phrase edit
views" of the prompt "confirm the reference changes together with the phrase" of `docs/llm/pending.md`. The tables
were written before the first test; the line numbers are those of the start state (commit `aa2abf85b`). The code of
step 1 is done, steps 2 and 3 are planned; the test runs between the steps are those of the prompt: `test_part.php`
after step 1 and after step 2, the full run with re-pin after step 3.

## Request

### step 1: an unchanged row is no change

`url_var::ref_rows` (`shared/url_var.php:1147`) keeps every '6'-prefixed reference row with a crud action. The
phrase edit form of step 2 posts one row per stored reference and one empty add row, so without this step the
confirm page would list and the confirmed post would write every unchanged row and the empty add row. A row is
dropped if it is
- an add without external key: a reference cannot exist without one (`refs.external_key` is `NOT NULL`), so such
  a row is the untouched add row of the form
- an update whose fields all equal their '86' values (the stored values of the edit form, `url_var::PRE` plus
  `url_var::REF_ROW`); the action and the id of the reference have no stored value and are not compared

A delete row is always kept. A row without '86' values is kept like before: the confirmed post carries only the
row vars (`system_form::form_back`, `system_form.php:570`), so the decision is taken once, on the confirm page.

### step 2: the component that writes the rows

A new system form component type `system_form_ref_rows` writes the references of a word or triple as rows of its
edit form: per stored reference the hidden `6a<n>=u` and `6l<n>=<ref id>`, the type as read only text, an input with
its hidden '86' value for the external key, the url and the description, the source selector and, after the hidden
action, the delete checkbox `6a<n>=d` (the last value of a posted var wins); then one add row with the hidden
`6a<n>=a`, the type selector and the empty inputs without '86' values. The references come from the same place as
in the tab box (`ui_list::ref_list_word`, `ui_list.php:1248`). No view uses the type yet, so no page changes.

The type of a stored reference is read only, because the phrase and the type are the prime index of a reference
(`ref::link_key_fields`) and another type is another reference (delete and add), decided by the developer
before step 2, although the prompt names an input for each field of `url_var::REF_ROW_VARS`.

### step 3: the rows in the phrase edit views

The phrase edit views are `word_edit` (`messages/system_views.json:5076`) and `triple_edit` (`:5433`). The new
component goes to the end of the `components` block (after `system admin subtitle`, `:2636`) and into both views
after `system form select protection type` (`:5122`, `:5507`). The new component shifts the id of every component
imported after `system_views.json`, and the two view entries shift the component link ids of the later views, so
this step needs the re-pin of a full test run.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `shared/url_var.php`, `test/php/unit_ui/reference_ui_tests.php` (step 1) | `ref_rows` 1143-1151; section "reference rows" 342-370 | `ref_rows`, new private `is_ref_row_change` after it; same section, after 367 | `ref_rows` reads the '86' values of the rows with `ref_row_pre_values` and keeps a row only if `is_ref_row_change` says it adds, changes or deletes a reference, as in the request; `is_ref_row_change` keeps the check of `REF_ROW_ACTIONS` for a row without known action; the tests, each with its negative: an update row with all fields equal to their '86' values is no change, one changed field keeps it; an add row without external key is no change, one with an external key is kept; a delete row with '86' values is kept; an update row without '86' values is kept |
| `db_code_links/component_types.csv`, `shared/types/component_types.php`, `web/component/component_exe.php`, `shared/enum/messages.php`, `translations/en.yaml`, `translations/de.yaml`, `web/component/execute/ui_list.php`, `web/component/execute/system_form.php`, `web/html/html_base.php`, `test/php/unit_ui/reference_ui_tests.php` (step 2) | csv after row 294; `FORM_SELECT_REFS` 123-124, `SYSTEM_TYPES` 847-879, type ids 1120; `component_exe.php` 254; `ref_list_word` 1259-1265; `form_refs` 2447; `input_checkbox` 3209, `field_id` 3324 | the row `295, 'form reference rows', …, 'system_form_ref_rows'`; `FORM_REF_ROWS` and `FORM_REF_ROWS_ID = 295` in the three places; the arm `FORM_REF_ROWS => $form->form_ref_rows($dbo, $form_name, $msg, $cfg)`; the messages `FORM_REF_ROWS` and `FORM_FIELD_REF_DELETE` with en and de texts; public `ui_list::refs_of_phrase` called by `ref_list_word` and the component; `system_form::form_ref_rows` after `form_refs` with the helpers `ref_row_change`, `ref_row_add` and `ref_row_field`; an optional id extension for `form_field` and `input` and an optional value for `input_checkbox`; tests | the component writes the rows of the request; the fields reuse `FORM_FIELD_EXTERNAL_KEY`, `FORM_FIELD_URL`, `FORM_FIELD_DESCRIPTION`, `FORM_SELECT_SOURCE` and `FORM_FIELD_TYPE`; every row field gets the row number as id extension (e.g. `_1`), because `field_id` builds the html id from the label and the duplicate id check of the snapshots would fail; the delete checkbox posts `d` instead of `1`; on a re-render after an error a row field keeps its posted value and its posted '86' value like `form_field_tracked` (`system_form.php:1480`); the tests: the fragment of `form_ref_rows` for a phrase with two references as a new `object_pages/` snapshot and its negative (an object that is not a phrase gets no rows); the posted form of the fragment read back with `url_var::ref_rows` is empty (step 1), and with one changed external key, one checked delete box and one filled add row it gives the three rows |
| `messages/system_views.json`, `test/php/unit_workflow/ref_url_tests.php`, `test/php/unit_write_workflow/ref_write_url_tests.php` (step 3) | components block 2636; `word_edit` 5122, `triple_edit` 5507 | the component `system form ref rows` with the code id and the ui message code id `form_ref_rows`; the entry `"name": "system form ref rows"` with the next position in both views; a new workflow and its write version | the phrase edit views show the rows inside the form, so the save carries them to the confirm (`frontend.php:950`), the preview lists them (`ui_preview.php:371`), `form_back` carries them to the confirmed post and `action_crud` writes them with `phrase::save_ref_rows`; the workflow on the test triple of `pending_prio_2.md` ("the reference write workflows change the change log of a seed triple"), never on a seed phrase: show the edit view, change the external key of one reference, check the delete box of another and fill the add row, save, confirm with the preview of the three rows, confirmed, and check the three references in the database; its negative: save the edit view without a change and get the no change message of the confirm (`ui_preview.php:898`) |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `web/component/execute/ui_preview.php` | `ref_rows_section` 376-378, `has_change` 898 | — | no code change by step 1: the confirm page lists only the changing rows, and a confirm whose rows are all unchanged and whose phrase fields are unchanged shows the no change message, which is the target |
| `web/component/execute/system_form.php`, `web/phrase/phrase.php`, `web/frontend.php` | `form_back` 570, `save_ref_rows` 1121, `action_crud` 2901 | — | no code change by step 1: `form_back` carries only the changing rows, and the confirmed post has no '86' values, so `save_ref_rows` and the check of the rows left open after a failed write see the same rows as before |
| `web/helper/url_mapper.php` | `human_url_to_json` 176 | — | no code change by step 1: the human json of a url lists only the changing rows; no url of a test has an unchanged row today |
| `web/phrase/phrase.php` | `wikidata_row_url` 1046-1070 | — | no change by step 1: the update row of the wikipedia refresh carries the type and the external key with their '86' values; the type is equal and the key differs, so the row stays, and an unchanged key builds no row at all (`reference_ui_tests.php:280`); so the workflow snapshots of `ref_url_tests` and `ref_write_url_tests` do not change |
| `shared/url_var.php` | `is_ref_row_change` | — | the url values are strings, but a row built in the code may carry an int (e.g. the type id of `predicate_id()`); `array_diff_assoc` compares the string forms, so `5` and `'5'` are equal and no cast is needed |
| `test/php/unit_ui/reference_ui_tests.php` | 346, 350, 364, 367 | — | no change by step 1: the existing rows have no '86' values or are delete rows, so they are kept as before |
| `web/html/html_base.php` | `form_field`, `input` and their callers | same functions | step 2: the id extension is an optional last parameter with an empty default, so no other caller and no snapshot changes; the type and the source selectors (`type_list::selector`, `type_list.php:532`, and the selector of `source_list`) take the row var as name already, but their html id must get the row extension too, else the duplicate id check fails |
| `web/component/execute/system_form.php`, `web/component/component_exe.php` | `form_ref_rows` | same function | step 2: besides the cache the function takes the url of the edit view and the test mode: the url keeps a posted row on a re-render after an error, but only for the same reference id or the add action, and the test mode loads only the cached sources like `form_source`; the sources are loaded once for all rows, and the source selector of a row is the helper `ref_row_source` next to `ref_row_field`, because its row var differs from the fixed `url_var::SOURCE` of `ref::source_selector`; `system_form.php` includes `ui_list.php` and `data_object.php`, `ui_list.php` includes `ref_list.php` |
| `web/html/html_selector.php` | `start_selector` | — | step 2, no change: the dom id of a selector is its name plus the label enum (e.g. `6so1_form_select_source`), so the row var keeps the selectors of the rows unique without an id extension; a stored source without entry in the loaded source list has no selected option, so the browser posts the first source and the confirm shows a changed source; a reference without source posts the `0` of the please select entry, which equals its '86' value |
| `web/component/execute/system_form.php`, `web/ref/ref_list.php` | `form_ref_rows`, `sort_by_impact_and_type` | — | step 2: the rows are numbered in the order of the tab box, which `sort_by_impact_and_type` sets on the reference list of the phrase itself, like `ref_list::list` does |
| `test/resources/api/type_lists/type_lists.json`, `test/resources/api/ui_config/ui_config.json`, `test/resources/unit/component/list_types.csv`, `test/resources/web/html/object_pages/all_component_types.html`, `object_pages/types.html`, new `object_pages/reference_form_rows.html` | — | regenerated, new | step 2: the new component type is part of the type list api message, the list of the component types and the type pages, like the type 294 of commit `19a3ae9d5`; the fragment of the rows is a new snapshot; written by the run with `AUTO_UPDATE_TEST_FILES` on and reviewed by the developer |
| `test/php/unit_ui/reference_ui_tests.php` | — | `posted_vars` | step 2: the test of the posted form needs the vars that a browser posts, so a private helper reads the inputs, the checked boxes and the selected entries of the fragment with `DOMDocument`, like `html_page_diff` |
| `web/frontend.php`, `web/phrase/phrase.php` | `action_crud` 2885, `save_ref_rows` 1118 | — | step 3: a confirm that changes only the references writes the phrase without a change; `update` of the phrase must report no error for an unchanged phrase, else `action_crud` stops before `save_ref_rows`; checked by the workflow of step 3 |
| html snapshots of `word_edit` and `triple_edit`, `test/resources/` of the components, component links and views imported after `system_views.json` | — | regenerated | step 3: the snapshots of the two views show the rows, and the new component shifts the ids of the later components and component links; regenerated by the re-pin run (first `test/reset_db.php`, then at least twice `test/test.php`) and reviewed by the developer |
| `docs/code_functions_all.md`, `docs/code_test_coverage.md`, `docs/code_user_message_exceptions.md` | — | — | generated; the new functions of steps 1 and 2 |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | step 1: switch on `reference_ui_tests`, `ref_url_tests` and `ref_write_url_tests` (`docs/llm/pending.md`) and switch off the tests of the committed changes (`docs/llm/testing.md`): `change_log_tests`, `change_log_ui_tests`, `base_ui_tests`, `word_ui_tests`, `result_ui_tests`, `view_ui_tests`, `job_ui_tests` and `run_html_snapshots`; step 2: also `coding_rule_tests` (the new messages and their translations) and `run_html_snapshots` (the duplicate html id check of the new rows); the new code link row reaches the database by the code link fill at the start of `run` (`a_selected_test.php:387`) |
| `docs/llm/pending.md` | steps 1, 2 and 3 | same section | each step removed once done, the prompt with step 3 |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `test/resources/web/html/views_by_id/component/88_component_add_1.html`, `89_component_update_1.html`, `views_by_object/component/component_add.html`, `component_edit_component_1.html`, `workflow/add_component_wf25/wf25_edit*.html`, `workflow/change_component_wf26/wf26_show_edit*.html` and the same five under `workflow_write/` | — | +3 each | step 2: the component type selector of the component add and change views lists every type, so the new type 295 adds one option; regenerated by the run with `AUTO_UPDATE_TEST_FILES` on, as for the type 294 of commit `19a3ae9d5` |
| `test/resources/db/component/component_names.sql`, `component_names_like.sql`, `component_names_like_mysql.sql`, `component_names_mysql.sql` | 9 | 9 | step 2: the name list of the components excludes the system types, and the new type 295 is one of them |
| `docs/code_object_name_exceptions.md` | 62 | 62 | step 2: generated; the test var `$row_wrd` of `reference_ui_tests.php:399` does not follow the var name pattern of a word (`$wrd` or `$wrd_<role>`), so it is listed as an exception |

The llm saw only the part of the run output pasted by the developer and the files that the run changed.

## Lessons learned by llm

1. The fixtures of a new type are taken from the full file list of the commit that added the previous type of the
   same kind (`git show --stat 19a3ae9d5`), not from a part of it: the type selector of the component add and
   change views lists every type, so their snapshots and the wf25 and wf26 workflows change too.
2. A new system type is also checked against every reader of `component_types::SYSTEM_TYPES`: the type 294 of the
   template commit is no system type, so the excluded type list of the component name query was not in the
   template.
3. A new test var follows the name pattern of its class (`$wrd_<role>`), because a free name is added to the
   generated `docs/code_object_name_exceptions.md`.

## Lessons learned by human
