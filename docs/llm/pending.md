# pending.md

## list of planned llm prompts

## high prio

check why in src/test/resources/web/html/views_by_object/triple/triple_default_triple_99.html the change log entry changes from '26-12-2022 18:23 zukunft.com system added "Zurich (canton)"' to '26-12-2022 18:23 zukunft.com system added "1"' and back. Or try to avoid that just the id is saved in the log if possible

### workflow

check why 'please select ...' is missing in ' <input type="text" list="p_form_select_phrase_list" class="form-control" name="p" form="formula_link_add" id="p_form_select_phrase" > <datalist id="p_form_select_phrase_list">'

see /docs/llm/coding.md and in union queries created by the sql_creator the parameters are added to the par array, but if the parameter name matches, the parameter should not be repeatet.

add a fill step to the word workflow test. After the step that changes of the description the test word 'add_filled' should be used to create a url with all fields filled and this should be used to test the confirm view for all fields filled. the name of the step is fill. 

add to /docs/llm/* that the $test_name should always be unique. And write a php_code_check script that checks if the $test_names are unique for all tests

after adding a word the word as it has been saved in the database should be shown. Because the db id is not yet known, that word name should be used to load the word. this implies that the url for /src/test/resources/web/html/workflow/add_word_wf1/wf1_edit_back_edit_save_cancel_edit_save_add_confirmed_url.txt should contain '"url_part_back": {"mask_id": "word_default", "name": "System Test Word"}' using the short url var for the name 'k'  

update the confirm change view to shows the user changes (based on the '8' prefixed values) and call the page when in the word edit view save is pressed

complete the 'to_url_array' function for all word fields e.g. the sandbox fields and add a TODO that this should be moved the test object because it will probably only be used for testing. But this can only be decided after the workflows are complete 

add a snap timestamp to the change log. The snap timestamp is the time when the user has called the edit view that he used to apply the changes so the db snap that has been the base for the change. Even if live update of the edit view would be possible, this is not recommended not tp break the user workflow. The max would be to show a refresh icon so that the user could refresh the edit view, but this is prio 4.

in the view 'Change word' adjust the url on the save button so that it fix the error messages 'url key "mask_id" is missing, url mapper for "mask" is missing, url mapper for "id" is missing, url mapper for "back" is missing, url mapper for "confirm" is missing, url mapper for "Name" is missing, url mapper for "py" is missing, url mapper for "Description" is missing, url mapper for "Plural" is missing, url mapper for "d" is missing, url mapper for "s" is missing, url mapper for "sp" is missing' caused by calling the url 'http://localhost/http/view.php?mask=3&id=259&back=259&confirm=1&Name=USD&py=3&Description=ISO+4217+alphabetic+code+for+the+United+States+dollar.&Plural=&d=0&s=1&sp=1' ; the expected result is that it should show the "Confirm update" view with the changes that the user has done and after pressing confirm that database row should be updated and the user should see th original page again, but with the updates , create first unit tests for the workflow using src/test/php/unit_workflow/all_workflow_tests.php

test.php gates this call under the WORKFLOW_TEST const, while the other DB-write tests run under a separate WRITE_TEST const. By folding the write workflow into all_workflow_tests::run, the write workflow now runs whenever WORKFLOW_TEST is on — independent of WRITE_TEST. That matches your "never differ" goal (it's how test_workflow.php already behaves — no WRITE_TEST gate there), but it does mean a WORKFLOW_TEST=true, WRITE_TEST=false run will now touch the DB.

fill in all placeholder

add all missing


add a '8' url prefix that is used to include the database values in the url for the url_to_html function to confirm the changes

Add a hidden json to the get request to detect the value changed or use 0 prefix for url vars

use the '8' prefixed values (urlVar::PRE) to create a complex parallel change workflow. To detect the real user change requests and prevent overwriting other user change during the edit view is shown
-> test wordflow 
1. user_a opens the edit view
2. user_b open the edit view and changes the phrase type
3. user_b press save and the changed phrased type is written to the database
4. user_a changes the description and press save
5. the changes of user_a are checked against the status when the edit mask has been opened and only the description is updated
6. the phrase type is left as user_b has changed it

check that all api interfaces can load user specific data independent of the session user 

check the open api definition and the openapi check script and suggest updates in the definition and the script


### word frontend

add a 'Word all values' view that show the values related to a word in up to 4 columns. For the column headline the four phrases with the most number of related values  

add to /docs/llm/* that for all html tags that have open and closing tag e.g. <form...></form> a function in html_base should be used. The html_base function should use a const for the html tag.

apply the use of the html_base  

create a component with the related formula that should show the formulas of the parent object connected with the verb 'is a' and add this component to the default word and triple views below the direct linked formulas. this component should include a small subheadline with 'from' and the name of the parent object

rename component "change log word" to "change log term" or add "change log triple"

show the description of the word in the word default page on the left 1/3 of the screen

The default view for a word should have four columns for width screens > 2800 pixel (config parameter):
1. a group of components with the description, the aliases / symbols and other related phrases
2. a group with the most relevant value by impact and if it exists a chart on the top and the position type 'side_or_first_below'
3. a group with the most relevant formulas and results (and later a result charts, just create a TODO) and the position type 'side_or_last_below'
4. a tab switch for the views with a miniature preview and two buttons: 'view' or 'switch' (see src/main/php/web/html/html_base.php::dsp_link_hist_box)
5. a second tab with the change log with the latest changes on the top
second step:
6. maybe a preselected third tab with the user changes if the user has done some overwrites
   the tab switch has the position type 'side_or_last_below'

add the formulas assigned to the parent phrase to the word_default view using also 1/3 of the screen width

add the values as a table where the word ist used to the word_default view using 2/3 of the screen width where often used phrases are column heads and the phrases are shown using a tree view

mainly copy the word default view to the triple default view

### data load

are there any database or object fields that are not yet filled or set by one of the json import tests

add a table licences and add the field licence to the json message header with the possibility to overwrite the licence for each object

add the licence to the subtitle if not the standard cc0 

in the footer add dynamically other licences if used



### backend

in src/main/php/shared/json_fields.php rename 'view-validation' to 'view_validations' and 'calc-validation' to 'calc_validations' and 'value-list' to 'value_list' and 'ip-blacklist' to 'ip_blacklist' to always use '_" instead of '-' for json field names 

create a list CONST array "SAMPLE_VIEW_DATA_FILES" that contains test data for the unit tests of the views. These test data is used for unit tests without using the database id, so these files can be imported in setup_db after the import of the system config. At the moment this const array contains only this file: src/main/resources/messages/base_data/zurich.json . create a function for the import and call it after the config loading

add a config section to the json import format that can be used to overwrite the system and user config for the import and add positive and negative unit tests for the overwrite of the number of decimals

add in the float value object the var 'precision' which defines how accurate the value is. include this field not only in the database (but not for standard values), the api, the frontend and also in the im- end export json 

add to the json import a 'view_validation' section that contains some relevant screen outputs in the '.md' format based on a given human readable url

add to the json import a 'jobs_starts' section where jobs starts could be triggered before or after the import e.g. request adding a verb or creating a wikipedia article

add a job to add or link a new verb

add a script that updates the verb section in docs/llm/json_structure.md based on docs/llm/json_structure.md

split the jobs_starts into jobs_before and jobs_after

add the src/main/resources/messages/start_page/theses_complex_simple.json to the full import

add a job to create a wikipedia article

if type_list_check fails update the json and reload the config and try again

create a phrase_value_key table that contains the phrase_id and the value_table_id and the value key for a fast (db index based) selection of all values related to a phrase



add the default date format 'd-m-Y H:i' to the config.yaml that the user can overwrite to display a date and use the config value where 'd-m-Y H:i' is used until now. For any system tests used a fixed const to replace 'd-m-Y H:i'

move time zone setting to .env

create a job to update the usage of a word

add a config parameter that the api message should include the message header (or not) and apply this to the api

Allow the users to define their own workflow → which view follow which under which conditions

Add the component type 'form validation' that checks based on phrase list and formulas the changed form values and create an info, warning or error message and redirects the workflow if needed

add the user types 'corporation', 'government', ...

add the table 'user_relation' that defines the relation between two users e.g. if user_a has a high trust level for user_b

add the table 'user_relation_types' with the entires 'is part of', 'high trust', 'medium trust', 'low trust', 'ignores', ...

### import

create a copy of all words and triples used for system testing before the config import, so that adding a new config value with a new word or triple does not break the test cases

in json import a calc_validation list is created. Use this list to check if the results cen be reproduced based on the data_object $dto filled only with the values and formulas from the json import file. In case of any errors use the usual path via $msg to send a message to the user

in json import a calc_validation list is created. Use this list to reproduce the results after the import for the user

in json import add a view_validation part that contains the views in the pure text format created by the html_to_text function. use this to check if after the import the expected views for the user can be reproduced

fix the '// TODO add json_fields::VIEW_VALIDATION' in src/main/php/cfg/import/import.php (view-validation as the rendering twin of calc-validation). suggested steps (adjust as needed):
1. (done) add library::html_to_markdown() as the richer sibling of html_to_text (keeps headings, tables and lists) with a positive and a negative unit test in lib_tests.php; markdown is preferred over plain text because a table/heading structure makes the expected ".md" screenshot human readable and a mismatch easy to read
2. decide the format of one "view-validation" entry: the view selection (view name, or the human-readable url of pending line 93/95) plus the expected ".md" output; document it in docs/llm/json_structure.md next to calc-validation
3. add a view-check list to data_object (view_check_list + add_view_validation), filled at the TODO via a new dto_get_view_validation() that maps each entry to a view and stores its expected markdown (mirror dto_get_results(..., use_to_check=true) and result_check_list)
4. add data_object::validate_views(user_message) (mirror validate_results): render each imported view to HTML via its *_ui display function, normalise it with html_to_markdown, compare to the expected markdown and on a difference add a translatable msg_id error (new VIEW_VALIDATION_MISMATCH / VIEW_VALIDATION_VIEW_MISSING cases with en/de)
5. call validate_views at the end of get_data_object next to the validate_results block and count view_validations_done / view_validations_failed
6. add a small sample import file with a view-validation section and a $dto unit test in import_tests.php: one positive (the rendered view matches) and one negative (a wrong expected ".md" reports the mismatch via $msg)
open questions for review: render the view against the imported $dto only or the database; which user context; is html_to_markdown the right normalisation or should both text and markdown be supported

### user frontend

fill the placeholders

Add to src/test/php/unit_ui/user_ui_tests.php a test of a list of sys_log entries related to the user. This implies a new frontend component user_system_errors (new component_types const with code_id and a globally unique ui_msg_code_id, rendered via a new arm in component_exe.php) that shows the x most relevant open system errors linked to the user, where x comes from a new pod config value read via $ui_sys->cfg (never new config()). Reuse web/system/sys_log_list.php::get_html() for the rendering — do not duplicate its table code. Write the test first: build the list from a create/test_*.php factory (e.g. test_sys_log::list_for_user_ui()), positive test asserts the snapshot fragment in object_pages/user.html, negative test asserts that an empty list reports the documented empty result (not just "no exception"). Paging ($size, $page) and status filter ($dsp_type) are passed as explicit parameters to the backend API call, never read from superglobals.

dsp_sandbox_* family → one generic "user changes vs. standard" component

Add to src/test/php/unit_ui/user_ui_tests.php tests for a new frontend component user_sandbox that shows, per object type (value, formula, formula link, word, triple, view, component, view link, source), the user's changes that differ from the standard, with columns "your value / common value / other users" and an undo button (icon from web/const/icons.php, undo URL built from named url_var consts with the '9'-prefixed back param). Implement it once as a generic renderer over a list of sandbox-difference rows delivered by the backend API as JSON; the per-type functions reduce to thin typed wrappers, or better, to one parameter. Requirements implied:

1. Backend API endpoint (e.g. api/user/sandbox) that returns the user-vs-standard-vs-others diff list per object type — move all eight inline SQL statements out of web/ into prepared, parameterized SQL in the model layer (the current string-concatenated WHERE u.user_id = $id is also an injection risk).
2. The "if user value equals standard, call del_usr_cfg()" logic is a DB consistency cleanup, not display logic: move it to a backend job/check (e.g. into the system consistency checks) and remove it from the frontend entirely.
3. Column headers and the "deleted" marker become msg_id cases in messages.php with en/de translations.
4. Unit test first, per type one positive (a factory-built diff list renders the expected object_pages/user_sandbox.html fragment) and one negative (empty diff list → documented empty output); factories named like test_words::sandbox_diff_ui() without repeating the class object word.

or smaller tasks like:

- dsp_sandbox_wrd → "show words the user renamed vs. the common name, with an undo-to-standard button"
- dsp_sandbox_wrd_link → "show triples the user changed (name/excluded) vs. standard and other users' versions, undo button"
- dsp_sandbox_frm → "show formulas where the user's expression text differs from the standard, undo button"
- dsp_sandbox_frm_link → "show formula↔phrase link changes (link type/excluded) vs. standard and others, undo button"
- dsp_sandbox_val → "show values the user overrode (number/source/excluded) vs. standard and others, value linked to value_edit, undo button"
- dsp_sandbox_view → "show view changes (name/description/type/excluded) vs. standard and others, undo button" — note the old code has a real bug here (if ($usr_ui->set_name(...)) instead of a comparison) which the rewrite must not carry over
- dsp_sandbox_component → "show component changes vs. standard and others, undo button"
- dsp_sandbox_view_link → "show component-link changes (order/position) vs. standard and others, undo button" — the old function has dead code (if (SQL_DB_TYPE != POSTGRES) wrapping an if (== POSTGRES)), so on Postgres it currently renders nothing; treat the behaviour as new, not as a port
- dsp_sandbox_source → "show source changes (name/url/description/type) vs. standard and others, undo button" — resolve the open TODO whether sources get a real del_usr_cfg() in the backend instead of the frontend del() call

### remove the database access from src/main/php/web (load via the API only)

scan of 2026-06-13: the frontend must never open or query the database (see docs/llm/frontend.md "The frontend never accesses the database — load via the API"). The markers are `new sql_db` / `new sql_creator` / `global $db_con`; the coded check is coding_rule_tests::php_web_only_allowed_globals_tests. Remaining cases, solve step by step:

1. (live) web/log/user_log_display.php::dsp_hist_links() and its helper dsp_hist_links_sql() build raw SQL via `new sql_db()` to show the link/relation change history. Called live from the dsp_hist_links() wrappers of component, view, view_exe, formula and word. Replace with an API-based load like the already-migrated dsp_hist() (which uses change_log_list::load_by_object_field + change_log_list::tbl); extend the change-log list api loader for the link case if needed, then delete dsp_hist_links_sql().

2. (live) web/frontend.php open_db()/start() bootstrap opens the database connection directly (already marked "TODO Prio 1 to be deprecated and use the api only for the frontend"); it is the only file excluded from coding_rule_tests::php_web_only_allowed_globals_tests. Move the bootstrap behind the API so web/ no longer needs $sys/$db_con/$cac/$cfg, then remove the 'frontend.php' exception from that coded check.

3. (dead) web/log/user_log_display.php::dsp_hist_old() uses `new sql_db()` + raw SQL but is only referenced from commented-out callers and is superseded by dsp_hist(). Remove it. (side note: the live dsp_hist() builds $result but then `return '';` — fix while there.)

4. (dead) web/value/value.php::dsp_samples() uses `new sql_db()` + raw SQL but sits entirely inside a /* ... */ block comment (lines ~695-776). Remove it, or rebuild via the group/value API if the sample display is still wanted.

5. (dead) web/user/user_display_old.php contains 9 `new sql_db()` direct-DB display functions and is not referenced anywhere in src/main/php. Delete the file.

after each step src/main/php/web must stay free of `new sql_db` / `new sql_creator` / `global $db_con`.

### fix error and warnings



### general

check where in the frontend a parameter / configuration values is used that is not yet taken from the config.yaml / user_configuration and at least mark it with a TODO Prio 1

create a script that updates all caches e.g. src/test/resources/api/type_lists/type_lists.json and src/test/resources/api/ui_config/ui_config.json after a change of any parameter in src/main/resources/db_code_links 