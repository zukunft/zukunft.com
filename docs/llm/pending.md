# pending.md

## list of planned llm prompts

### word frontend

The default view for a word should have four column for width screens > 2800 pixel:
1. a group of components with the description, the aliases / symbols and other related phrases
2. a group with the most relevant value by impact and if it exists a chart on the top and the position type 'side_or_first_below'
3. a group with the most relevant formulas and results and a result charts  and the position type 'side_or_last_below'
4. a tab switch for the views with a miniature preview and two buttons: 'view' or 'switch'
5. a second tab with the change log with the latest changes on the top
6. maybe a preselected third tab with the user changes if the user has done some overwrites
   the tab switch has the position type 'side_or_last_below'

show the phrases related to a word in the default word page; create first a list of unit tests 
show the formulas assigned to a word in the default word page
show the value related to a word in the default word page
show the references related to a word in the default word page
show the changes of a word in the default word page
show the description of the word on the left 1/3 of the screen
add the component position type "side or below" that shows this component right of the previous component is not the screen size is too small, what is too small is taken from the config which the user can overwrite an the default value is 1000 pixel
show the views assigned to a word in the default word page


add the formulas assigned to a word to the word_default view using also 1/3 of the screen width

add the values as a table where the word ist used to the word_default view using 2/3 of the screen width where often used phrases are column heads and the phrases are shown using a tree view

mainly copy the word default view to the triple default view

create a job to update the usage of a word

### workflow

fix the error messages 'url key "mask_id" is missing, url mapper for "mask" is missing, url mapper for "id" is missing, url mapper for "back" is missing, url mapper for "confirm" is missing, url mapper for "Name" is missing, url mapper for "py" is missing, url mapper for "Description" is missing, url mapper for "Plural" is missing, url mapper for "d" is missing, url mapper for "s" is missing, url mapper for "sp" is missing' caused by calling the url 'http://localhost/http/view.php?mask=3&id=259&back=259&confirm=1&Name=USD&py=3&Description=ISO+4217+alphabetic+code+for+the+United+States+dollar.&Plural=&d=0&s=1&sp=1' ; the expected result is that it should show the "Confirm update" view with the changes that the user has done and after pressing confirm that database row should be updated and the user should see th original page again, but with the updates , create first unit tests for the workflow using src/test/php/unit_workflow/all_workflow_tests.php

add a '0' url prefix that is used to include the database values in the url for the url_to_html function to confirm the changes

### import

in json import a calc_validation list is created. Use this list to check if the results cen be reproduced based on the data_object $dto filled only with the values and formulas from the json import file. In case of any errors use the usual path via $msg to send a message to the user

in json import a calc_validation list is created. Use this list to reproduce the results after the import for the user

in json import add a view_validation part that contains the views in the pure text format created by the html_to_text function. use this to check if after the import the expected views for the user can be reproduced




### user frontend

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

### fix error and warnings



### general

check where in the frontend a parameter / configuration values is used that is not yet taken from the config.yaml / user_configuration and at least mark it with a TODO Prio 1