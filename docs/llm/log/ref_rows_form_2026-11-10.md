# ref rows form (2026-11-10)

Steps 1 "an unchanged row is no change", 2 "the component that writes the rows" and 3 "the rows in the phrase edit
views" of the prompt "confirm the reference changes together with the phrase" and steps 1 "a unit test on a frozen
Geneva page", 2 "the wikipedia key is the url slug" and 3 "the references of Zurich (canton)" of the prompt
"wikipedia link" of `docs/llm/pending.md`. The tables of each step were written before its first test; the line
numbers are those of the start state of the step: commit `aa2abf85b` for the reference rows, commit `297b8fe78`
(with the uncommitted step 3 of the reference rows) for the wikipedia steps. The test runs between the steps are
those of the prompts: `test_part.php` after the reference rows steps 1 and 2, the full run with re-pin after the
reference rows step 3 and after the wikipedia steps 2 and 3, which change the seed refs.

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

### wikipedia step 1: a unit test on a frozen Geneva page

`unit/wikipedia_tests.php` reads only `import/wikipedia/cache/summary_Zurich.json`, a summary written by hand, and
Geneva is only compared with the live page in the write tests (`ref_url_tests::wikipedia_page_tests`). The
summary of the page `Geneva` is read once from the summary api of wikipedia (`ref_link_wikipedia::SUMMARY_URL`
with the user agent of `import_wikidata::USER_AGENT`) and saved unchanged as
`import/wikipedia/cache/summary_Geneva.json`, which no run updates. The unit test checks that
`ref_link_wikipedia::description` of this summary gives the first paragraph without the pronunciation and
`wikidata_id` gives `Q71` (`refs::GENEVA_KEY`), each with its negative.

### wikipedia step 2: the wikipedia key is the url slug

The external key of a wikipedia reference is the slug of the direct url of the page, e.g. `Canton_of_Zurich`,
not the title with spaces, e.g. `Canton of Zurich`, because the link is the url of the type plus the key
(`html_base::url_ex`, `html_base.php:1932`) and only a slug gives a valid href. 745 keys in 45 import and api files
of `src/main/resources/messages` and `src/test/resources` still have spaces (counted by the script and, as a
check, by a walk of the decoded json of each file). They are converted by a one-off script, decided by the
developer as an exception to the Edit-only rule for this step: the script changes only the `name` of a ref object
with `"type": "wikipedia"` (each space becomes `_`, the casing stays) and keeps the format of the files, so every
change is visible in `git diff`. The refresh reads the right page with both forms (`ref_link_wikipedia.php:175`),
so no code changes.

### wikipedia step 3: the references of Zurich (canton)

The references of the canton of Zurich have three keys and one of them sits on the wrong phrase:
- `base_data/zurich.json:41-52` gave the references of the canton of Zurich to the word "canton", the general
  concept; the developer has changed them to `Cantons_of_Switzerland` and `Q23058` before this step
- the word "city" of the same file (`:27-39`) still has the references of the city of Zurich (`Zurich`, `Q72`),
  which belong to the triple Zurich (city)
- the wikipedia key of the triple Zurich (canton) is `Canton of Zurich` in `base_phrases.json:293` and
  `base_data/zurich.json:312`, but the target is the slug of the direct url (wikipedia step 2), and the test
  const `refs::ZURICH_CANTON_WIKIPEDIA_KEY` is a third spelling, `canton_of_Zürich`

The keys follow the direct url of the page as the developer has done for "canton": `City` and `Q515` for the word
"city", `Canton_of_Zurich` for the triple Zurich (canton) in both import files and in the test const.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `shared/url_var.php`, `test/php/unit_ui/reference_ui_tests.php` (step 1) | `ref_rows` 1143-1151; section "reference rows" 342-370 | `ref_rows`, new private `is_ref_row_change` after it; same section, after 367 | `ref_rows` reads the '86' values of the rows with `ref_row_pre_values` and keeps a row only if `is_ref_row_change` says it adds, changes or deletes a reference, as in the request; `is_ref_row_change` keeps the check of `REF_ROW_ACTIONS` for a row without known action; the tests, each with its negative: an update row with all fields equal to their '86' values is no change, one changed field keeps it; an add row without external key is no change, one with an external key is kept; a delete row with '86' values is kept; an update row without '86' values is kept |
| `db_code_links/component_types.csv`, `shared/types/component_types.php`, `web/component/component_exe.php`, `shared/enum/messages.php`, `translations/en.yaml`, `translations/de.yaml`, `web/component/execute/ui_list.php`, `web/component/execute/system_form.php`, `web/html/html_base.php`, `test/php/unit_ui/reference_ui_tests.php` (step 2) | csv after row 294; `FORM_SELECT_REFS` 123-124, `SYSTEM_TYPES` 847-879, type ids 1120; `component_exe.php` 254; `ref_list_word` 1259-1265; `form_refs` 2447; `input_checkbox` 3209, `field_id` 3324 | the row `295, 'form reference rows', …, 'system_form_ref_rows'`; `FORM_REF_ROWS` and `FORM_REF_ROWS_ID = 295` in the three places; the arm `FORM_REF_ROWS => $form->form_ref_rows($dbo, $form_name, $msg, $cfg)`; the messages `FORM_REF_ROWS` and `FORM_FIELD_REF_DELETE` with en and de texts; public `ui_list::refs_of_phrase` called by `ref_list_word` and the component; `system_form::form_ref_rows` after `form_refs` with the helpers `ref_row_change`, `ref_row_add` and `ref_row_field`; an optional id extension for `form_field` and `input` and an optional value for `input_checkbox`; tests | the component writes the rows of the request; the fields reuse `FORM_FIELD_EXTERNAL_KEY`, `FORM_FIELD_URL`, `FORM_FIELD_DESCRIPTION`, `FORM_SELECT_SOURCE` and `FORM_FIELD_TYPE`; every row field gets the row number as id extension (e.g. `_1`), because `field_id` builds the html id from the label and the duplicate id check of the snapshots would fail; the delete checkbox posts `d` instead of `1`; on a re-render after an error a row field keeps its posted value and its posted '86' value like `form_field_tracked` (`system_form.php:1480`); the tests: the fragment of `form_ref_rows` for a phrase with two references as a new `object_pages/` snapshot and its negative (an object that is not a phrase gets no rows); the posted form of the fragment read back with `url_var::ref_rows` is empty (step 1), and with one changed external key, one checked delete box and one filled add row it gives the three rows |
| `messages/system_views.json`, `test/php/unit_workflow/ref_url_tests.php`, `test/php/unit_write_workflow/ref_write_url_tests.php` (step 3) | components block 2636; `word_edit` 5122, `triple_edit` 5507 | the component `system form ref rows` with the code id and the ui message code id `form_ref_rows`; the entry `"name": "system form ref rows"` with the next position in both views; a new workflow and its write version | the phrase edit views show the rows inside the form, so the save carries them to the confirm (`frontend.php:950`), the preview lists them (`ui_preview.php:371`), `form_back` carries them to the confirmed post and `action_crud` writes them with `phrase::save_ref_rows`; the workflow on the test triple of `pending_prio_2.md` ("the reference write workflows change the change log of a seed triple"), never on a seed phrase: show the edit view, change the external key of one reference, check the delete box of another and fill the add row, save, confirm with the preview of the three rows, confirmed, and check the three references in the database; its negative: save the edit view without a change and get the no change message of the confirm (`ui_preview.php:898`) |
| `test/resources/import/wikipedia/cache/summary_Geneva.json`, `test/php/const/files.php`, `test/php/unit/wikipedia_tests.php` (wikipedia step 1) | —; `IMPORT_WIKIPEDIA_SUMMARY_ZURICH` 513; `run` 75-200, consts 49-60 | new; + `IMPORT_WIKIPEDIA_SUMMARY_GENEVA`; + subheader "frozen page" | the summary of the page Geneva as wikipedia sent it, never updated by a run, so the unit test does not depend on the live page; the description of the frozen summary is its lead paragraph (`GENEVA_DESCRIPTION`) and the wikidata id is `refs::GENEVA_KEY`; the negatives: the same summary as a disambiguation page has no description, and without its wikidata item no wikidata id; the summary of 2026-11-10 has one paragraph and no pronunciation, because the summary api removes it already, so the removal of a pronunciation and of a second paragraph stays tested by the hand made texts and by `summary_Zurich.json` |
| `docs/llm/json_structure.md`, the import files of `src/main/resources/messages` with a wikipedia key with spaces (e.g. `physics.json`, `chemistry.json`, `sociology.json`, `math.json`, `engineering.json`), the import and api test files of `src/test/resources` except the html snapshots (e.g. `import/travel_scoring.json`, `import/portfolio_instruments.json`, `api/phrase_data/phrase_data_pv_install.json`) (wikipedia step 2) | 487, 494-497; the `name` of each ref with `"type": "wikipedia"` | same lines | the one external key rule asks for the slug of the direct url with `_` (`Standard_deviation`, not `Standard deviation`), and the casing example of the refs is a slug (`Canton_of_Zurich`); the key is the slug, so the stored link opens the page and each phrase has one key in all files; the test files get the same key as the base data, because the test data imports overwrite the key of the seed |
| `messages/base_data/zurich.json`, `messages/base_phrases.json`, `messages/system_unit_test_data/country.json`, `test/resources/import/base_test_data.json`, `messages/system_unit_test_data/zurich_htp_impact.json`, `shared/const/refs.php` (wikipedia step 3) | word "Zurich" 14-25, word "city" 27-39, 312; 293; 57; 85; 62-63; 78 | "Zurich" without refs, the other lines the same; htp without lines 62 and 63 | the word "city" gets the references of the general concept: `City` (wikipedia) and `Q515` (wikidata) instead of those of the city of Zurich, like the word "canton"; the word "Zurich" is the place name of both the city and the canton (`base_phrases.json:187`), so it has no references, decided by the developer, and `Q72` stays only with the triple Zurich (city); the wikipedia key of Zurich (canton) is the url slug `Canton_of_Zurich` in every import file that lists it (the one external key rule); `base_test_data.json` is imported after the seed as test data and overwrote the key with `canton_of_Zürich`, the spelling of the old test const; the triples "canton of Zurich" and "city of Zurich" of the htp impact test data lose `Q11943` and `Q72`, decided by the developer, because the items belong to Zurich (canton) and Zurich (city); `ZURICH_CANTON_WIKIPEDIA_KEY = 'Canton_of_Zurich'`, the key of the base data, so the stored ('86') values of the reference row workflows are the stored key |

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
| `docs/code_functions_all.md`, `docs/code_test_coverage.md`, `docs/code_user_message_exceptions.md` | — | — | generated; the new functions of steps 1 and 2; wikipedia step 1 adds no function, only more test calls of `description` and `wikidata_id` |
| `test/php/unit_write/a_selected_test.php` | `run` | same function | step 1: switch on `reference_ui_tests`, `ref_url_tests` and `ref_write_url_tests` (`docs/llm/pending.md`) and switch off the tests of the committed changes (`docs/llm/testing.md`): `change_log_tests`, `change_log_ui_tests`, `base_ui_tests`, `word_ui_tests`, `result_ui_tests`, `view_ui_tests`, `job_ui_tests` and `run_html_snapshots`; step 2: also `coding_rule_tests` (the new messages and their translations) and `run_html_snapshots` (the duplicate html id check of the new rows); the new code link row reaches the database by the code link fill at the start of `run` (`a_selected_test.php:387`); wikipedia step 1: `wikipedia_tests` with its include and use |
| `test/resources/import/wikipedia/cache/` | — | — | wikipedia step 1, no change of the json validation: the cache folder is skipped as json as received from wikipedia (`json_validation::SKIP_PATHS`) |
| `src/test/php`, `src/main/php` | — | — | wikipedia step 2: no change if no code or test names a converted key; checked by a search for each converted key with a space |
| `test/resources/unit/ref/list.csv` | 277, 466-471 | regenerated | wikipedia steps 2 and 3: the keys as slugs; the keys of Zurich (canton), of the word city and of the word canton; the two references of the word Zurich (ids 465 and 466) are no longer imported, so every later reference id moves by two (about 325 rows); no test const pins a reference id after 465 (`refs.php` pins 1, 2, 29, 276, 277 and 279), the snapshots with a later reference id are regenerated by the re-pin |
| the html snapshots that show a wikipedia link or the references of Zurich (canton), city or canton, e.g. `views_by_object/triple/triple_default_triple_142.html`, the api messages with references | — | regenerated | wikipedia steps 2 and 3: the keys and the hrefs show the slug, e.g. `https://en.wikipedia.org/wiki/Canton_of_Zurich`, and the word pages of city and canton show their new references; regenerated by the re-pin run and reviewed by the developer |
| `workflow/` and `workflow_write/` of `ref_row_confirm_wf44` and `ref_rows_form_wf46` | — | regenerated | wikipedia step 3: the stored ('86') value of the wikipedia row is the new const |
| `docs/llm/pending_prio_2.md` | "wikipedia" item 7 (2577-2586) | same item | wikipedia step 2: the item planned the title with spaces as the stored key; it keeps the open parts for the slug: map a space to `_` on the input of an import or a form, one url function with `rawurlencode` for the link and the summary, and the unit test of both spellings |
| `docs/llm/pending.md` | steps 1, 2 and 3 of both prompts | — | each step removed once done, each prompt with its last step |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `test/resources/web/html/views_by_id/component/88_component_add_1.html`, `89_component_update_1.html`, `views_by_object/component/component_add.html`, `component_edit_component_1.html`, `workflow/add_component_wf25/wf25_edit*.html`, `workflow/change_component_wf26/wf26_show_edit*.html` and the same five under `workflow_write/` | — | +3 each | step 2: the component type selector of the component add and change views lists every type, so the new type 295 adds one option; regenerated by the run with `AUTO_UPDATE_TEST_FILES` on, as for the type 294 of commit `19a3ae9d5` |
| `test/resources/db/component/component_names.sql`, `component_names_like.sql`, `component_names_like_mysql.sql`, `component_names_mysql.sql` | 9 | 9 | step 2: the name list of the components excludes the system types, and the new type 295 is one of them |
| `docs/code_object_name_exceptions.md` | 62 | 62 | step 2: generated; the test var `$row_wrd` of `reference_ui_tests.php:399` does not follow the var name pattern of a word (`$wrd` or `$wrd_<role>`), so it is listed as an exception |
| `test/php/create/test_mappers.php` | `component_type_to_object` | + `FORM_REF_ROWS` in the word branch | step 2, found in step 3: without a mapped object the type page `all_component_types.html` showed "no object mapped for type form reference rows" and the run logged a warning; a filled word renders the add row |
| `messages/system_views.json` | `triple_edit` 5507 | the rows at position 18 | step 3: in `triple_edit` the protection type is the last field of a formatter row (positions 13 to 17), so the rows follow the row end and not the protection type as planned; in `word_edit` they follow the protection type at position 11 |
| `test/php/unit_workflow/ref_url_tests.php`, `test/php/const/workflows.php`, `test/php/create/test_refs.php` | `ref_row_confirm_url` | `ref_rows_form_workflow`, `ref_rows_form_rows`, `zh_canton_save_url`, `WF_REF_ROWS_FORM_NBR = 46`, `ref_list_zh_canton_ui` | step 3: decided by the developer in step 3, the workflow only renders on the seed triple Zurich (canton) and has no write version yet, because the test triple of `pending_prio_2.md` does not exist; a read run has no references of Zurich (canton) in the request cache (its triple page lists none), so the workflow adds them and sets the cache back at the end; the base url of the save is taken out of `ref_row_confirm_url` for both workflows; the negative checks that the confirm page has no reference section, because a phrase confirm without change has no own message (`ui_preview.php:898` only leaves out the impact line) |
| `docs/llm/pending_prio_2.md` | "the reference write workflows change the change log of a seed triple", point 3 | + `ref_rows_form_workflow` | step 3: the workflow moves to the test triple too and then gets its write version |
| `shared/const/components.php`, `views_by_object/component/component_default_component_428.html` | 57 | `SOLUTION_PRIO_TITLE_ID = 429`; the snapshot renamed to `..._429.html` | step 3: the new component shifts "Title solution priority" from 428 to 429; the re-pin run with `AUTO_RE_PIN_OBJECT_ID` rewrote the const, and the tests of that run still used 428 (`component_ui_tests` and `test_pinned_ids` reported "related phrases" for 428 and "rerun the tests") |
| `test/resources/api/type_lists/type_lists.json`, `api/ui_config/ui_config.json`, the view snapshots (`views_by_id/view/85_view_update_1.html`, `87_view_1.html`, `views_by_object/view/*`, `workflow*/add_view_wf22`, `change_view_wf23`, `del_view_wf24`) | — | regenerated | step 3: the component and component link ids of the views imported after `system_views.json` move by one, and these files list them, e.g. the component selector of a view page (`<option value="428">` to `"429"`) |
| `docs/code_object_name_exceptions.md`, `docs/code_test_coverage.md` | — | — | step 3: generated; the var `$wp_ref` of `test_refs::ref_list_zh_canton_ui` is a name exception of the ref class, and `data_object::set_ref_list` is called by the test now |
| the edit view snapshots of a write run, e.g. `workflow_write/change_word_wf2/wf2_show_edit.html` | — | +1612 each | step 3: the source selector of each reference row lists all sources (about 520 options); noted in `pending_prio_2.md` ("the source selector of each reference row lists all sources") |
| `test/resources/web/html/views_by_object/word/word_default_word_228.html` | — | regenerated | wikipedia step 3: the page of the word Zurich no longer shows the wikipedia and the wikidata reference; the html row of the table names only the pages of Zurich (canton), city and canton, because it was written before the developer decided to remove the references of the word Zurich |

Wikipedia step 1 has no unexpected change: no file of the run shows a change by this step, and the pasted run
output contains no result of `wikipedia_tests`. Wikipedia step 2 has none either: the run changed
`unit/ref/list.csv` (the keys as slugs, e.g. `Mathematical_constant`) and the snapshots with a wikipedia link, both
named in the table. For wikipedia step 3 the ids of the references after 465 moved by two as expected (e.g.
`word_default_word_484.html`: 486 to 484), the Zurich (canton) link is
`https://en.wikipedia.org/wiki/Canton_of_Zurich` and the wf44 snapshots show the stored key `Canton_of_Zurich`.

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
4. A new seed component moves the id of every later component, so the plan lists each place that names a component
   id: the pinned consts (`components.php`), the type lists api message, the ui config and every page with a
   component selector, not only the snapshots of the changed views.
5. A position in a view is chosen after reading the layout components around it: in `triple_edit` the planned
   position "after the protection type" was inside a formatter row.
6. A negative test of a plan is checked against the code before it is planned: the planned "no change message" of
   the confirm does not exist, so the negative checks the missing reference section instead.
7. Before a selector is repeated per row, the size of its list in a write run is checked: the source list has
   about 520 entries, so each row adds 1612 lines to the edit page.
8. A read run renders from the request cache only, so the data that a workflow needs is taken from the snapshots
   of a similar workflow first (the wf44 triple page listed no reference), which led to the cached references
   that the wf46 snapshots now show.
9. A sample of an external api is fetched and read before the test of a plan is fixed: the plan asked for the
   removal of the pronunciation and of a second paragraph, but the summary api already sends the lead paragraph
   of Geneva without both, so the negatives check the page type and the wikidata item instead.
10. A frozen sample is saved byte for byte with the Write tool and compared with the download (`cmp`), so that the
    fixture is the answer of the api and not a reformatted copy.
11. A bulk change is counted by two independent methods before it is planned: the first grep estimate of the
    prompt (617 and 156 keys) was wrong, the script and a walk of the decoded json both counted 745 keys in 45
    files.
12. Before a rule is turned, the pending docs are searched for a plan in the other direction: item 7 of the
    wikipedia leftovers in `pending_prio_2.md` planned the title with spaces as the stored key.
13. A script that changes files by text keeps them reviewable if it compares the decoded json before and after
    each file and skips a file on any other difference.
14. Before a key of a seed phrase changes, every json file that lists it is searched, including the test data of
    `src/test/resources/import` that is imported after the seed: `base_test_data.json` overwrote the key of Zurich
    (canton) with `canton_of_Zürich`, which explains the third spelling of the test const.
15. A wrong reference is checked on the neighbours that share its structure (the defect rule of `coding.md`): the
    wrong references of the word canton led to those of the words city and Zurich and to the duplicated items of
    the htp impact test data.
16. A table row that names files is extended when the developer widens the request before the first test, e.g.
    the html row with the page of the word Zurich.

## Lessons learned by human

this was a bigger change including a small re-pin but in general it has been fine, so not adjustment needed 
(beside that the test run should be faster)
