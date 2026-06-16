# pending.md

## list of planned llm prompts

### general

see /docs/llm/coding.md and note in /docs/llm/ that function names should be the long version because a function call is usually more time cost intensive e.g. it should be load_by_phrase_list instead of load_by_phr_lst.

see /docs/llm/coding.md and note in /docs/llm/ that within a class section like /* function should be sorted 'top down' means often used public functions should be on the top and rarely used private function at the buttom e.g. load_by_phrase should be before load_sql_by_phrase


### remove the database access from src/main/php/web (load via the API only)

scan of 2026-06-13: the frontend must never open or query the database (see docs/llm/frontend.md "The frontend never accesses the database — load via the API"). The markers are `new sql_db` / `new sql_creator` / `global $db_con`; the coded check is coding_rule_tests::php_web_only_allowed_globals_tests. Remaining cases, solve step by step:

1. (live) web/log/user_log_display.php::dsp_hist_links() and its helper dsp_hist_links_sql() build raw SQL via `new sql_db()` to show the link/relation change history. Called live from the dsp_hist_links() wrappers of component, view, view_exe, formula and word. Replace with an API-based load like the already-migrated dsp_hist() (which uses change_log_list::load_by_object_field + change_log_list::tbl); extend the change-log list api loader for the link case if needed, then delete dsp_hist_links_sql().

2. (live) web/frontend.php open_db()/start() bootstrap opens the database connection directly (already marked "TODO Prio 1 to be deprecated and use the api only for the frontend"); it is the only file excluded from coding_rule_tests::php_web_only_allowed_globals_tests. Move the bootstrap behind the API so web/ no longer needs $sys/$db_con/$cac/$cfg, then remove the 'frontend.php' exception from that coded check.

3. (dead) web/log/user_log_display.php::dsp_hist_old() uses `new sql_db()` + raw SQL but is only referenced from commented-out callers and is superseded by dsp_hist(). Remove it. (side note: the live dsp_hist() builds $result but then `return '';` — fix while there.)

4. (dead) web/value/value.php::dsp_samples() uses `new sql_db()` + raw SQL but sits entirely inside a /* ... */ block comment (lines ~695-776). Remove it, or rebuild via the group/value API if the sample display is still wanted.

5. (dead) web/user/user_display_old.php contains 9 `new sql_db()` direct-DB display functions and is not referenced anywhere in src/main/php. Delete the file.

after each step src/main/php/web must stay free of `new sql_db` / `new sql_creator` / `global $db_con`.

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
for the change log use a fixed date for the creation of the unit test files
to display a date use a format from config that the user can overwrite
show the changes of a word in the default word page
show the description of the word on the left 1/3 of the screen
add the component position type "side or below" that shows this component right of the previous component is not the screen size is too small, what is too small is taken from the config which the user can overwrite an the default value is 1000 pixel
show the views assigned to a word in the default word page

move time zone setting to .env


add the formulas assigned to the parent phrase to the word_default view using also 1/3 of the screen width

add the values as a table where the word ist used to the word_default view using 2/3 of the screen width where often used phrases are column heads and the phrases are shown using a tree view

mainly copy the word default view to the triple default view

create a job to update the usage of a word

### workflow

fix the error messages 'url key "mask_id" is missing, url mapper for "mask" is missing, url mapper for "id" is missing, url mapper for "back" is missing, url mapper for "confirm" is missing, url mapper for "Name" is missing, url mapper for "py" is missing, url mapper for "Description" is missing, url mapper for "Plural" is missing, url mapper for "d" is missing, url mapper for "s" is missing, url mapper for "sp" is missing' caused by calling the url 'http://localhost/http/view.php?mask=3&id=259&back=259&confirm=1&Name=USD&py=3&Description=ISO+4217+alphabetic+code+for+the+United+States+dollar.&Plural=&d=0&s=1&sp=1' ; the expected result is that it should show the "Confirm update" view with the changes that the user has done and after pressing confirm that database row should be updated and the user should see th original page again, but with the updates , create first unit tests for the workflow using src/test/php/unit_workflow/all_workflow_tests.php

add a '0' url prefix that is used to include the database values in the url for the url_to_html function to confirm the changes

Add a hidden json to the get request to detect the value changed or use 0 prefix for url vars

### import

create a copy of all words and triples used for system testing before the config import, so that adding a new config value with a new word or triple does not break the test cases

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