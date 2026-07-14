# pending.md

## list of planned llm prompts

## high prio

check if config.yaml contains a parameter that prevents ip user from doing database changes and if not, add it

check that if the ip user cannot do database changes the actually prevents all database changes and if not suggest the steps to fix it

repeat a general security check and list the most urgent things to do before go live

check why in src/test/resources/web/html/views_by_object/triple/triple_default_triple_99.html the change log entry changes from '26-12-2022 18:23 zukunft.com system added "Zurich (canton)"' to '26-12-2022 18:23 zukunft.com system added "1"' and back. Or try to avoid that just the id is saved in the log if possible

### security improvements

add TOTP authentification for SERVER_ADMIN2 and 3, so that the first login can be done with the pure user name and password and than a page shows the QR code e.g. for an App like FreeOTP+ to add a second factor

### prepare denial-of-service protection

use the cache for read only pages

limit the number of read requests per time unit by the webbrowser and auto switch to ip whitelist mode

count the number of db write requests by user and ip

add to config.yaml the max age for each db_cache_type 

after each write to db_cache check if there are row older than defined in the setting and delete them using a prepared query

### denial of service test

The goal of this block is an end-to-end test that a flood of change requests first blacklists the abusing user and, if the abuse continues from more than one user, automatically raises the pod to user whitelist mode. The current protection is only the manual, file based whitelist in `src/main/php/web/server_guard.php` toggled from `http/server_admin.php` (state in `server_admin/state.json`); the automatic per-user rate limit, the database blacklist and the auto-switch to whitelist mode still have to be wired up. Build the prompts below in order: the first ones add the enforcement and the make-it-testable config knobs, the later ones are the actual DoS test that lowers those knobs, simulates the flood and asserts the reaction.

wire the existing `max change` config (config.yaml at `zukunft.com > system configuration > user > default > backend > max change > daily > ip user`, currently 1000 and only defined) to real enforcement: count the change requests a user has made in the configured period and reject a change that would exceed the limit. Add a second daily limit `logged in user` next to `ip user` so a registered user has its own threshold, and read both through the normal config accessor (the same path used for other `system configuration` values) instead of a hard coded number. Confirm no superglobals are read inside the enforcement method - the requesting user must be passed in as a parameter.

when a user exceeds its `max change` daily limit add that user to the database blacklist (the fallback that `server_guard.php` and `http/server_admin.php` already refer to as "the database based blacklist") and from then on reject every further change request from that user with a clear user_message that tells the user why the change was refused and how to contact the admin. Keep the blacklist in the database, not in the file based `user_whitelist.txt`, so the manual whitelist and the automatic blacklist stay independent.

add a new system configuration knob `user whitelist auto switch` = the number of distinct blacklisted users within the detection period that automatically activates the user whitelist mode (the same `user_whitelist_active` flag in `server_admin/state.json` that the admin page toggles). Default it to a high, production-safe value. When the number of freshly blacklisted users reaches this threshold, set `user_whitelist_active` to true exactly as the admin page would, and log a warning so the admins see why the pod switched to whitelist mode.

write the denial of service test itself (a dedicated test, e.g. under `src/test/php/` following the existing test structure, runnable via `php test_unit.php`, admin via the IP_ADMIN fallback on the CLI). As the first step the test must lower the two knobs to test values and remember the previous values so it can restore them at the end: set the `max change` daily limit for `ip user` and for `logged in user` to a very low number (e.g. 2 changes per period) and set `user whitelist auto switch` to 2 users. Assert that reading the knobs back returns the lowered values before continuing.

in the same test simulate one user that sends more change requests than the lowered `max change` limit in a short period. Assert that after crossing the limit the user is added to the database blacklist, and assert that the next change request from that already-blacklisted user is rejected (the change is not stored and the returned user_message says the request was refused). The user whitelist mode must still be inactive at this point because only one user has been blacklisted (threshold is 2).

extend the test with a second user that also sends too many change requests in a short period. Assert that once the second distinct user is blacklisted the `user whitelist auto switch` threshold of 2 is reached and `user_whitelist_active` in `server_admin/state.json` flips to true automatically. Then assert that an ip user (anonymous, not logged in) change request is now rejected by `server_guard.php` with the `optional/user_reject.html` reject page, and assert that the server admin page (`http/server_admin.php`) reports the user whitelist as active - check the rendered state that the page reads via `read_state()` / shows as "User whitelist: active".

as the final step of the test reset everything to the pre-test state: restore the `max change` daily limits and the `user whitelist auto switch` knob to their remembered default values, clear the two test users from the database blacklist, and switch the user whitelist mode off again through the same code path the server admin page uses to deactivate it (the `toggle user whitelist` POST action in `http/server_admin.php`), leaving `user_whitelist_active` false in `server_admin/state.json`. Assert that after the reset a normal change request from a fresh user succeeds again, so the test is self-cleaning and leaves no active whitelist or blacklist behind.

### distributed denial of service test

This block is the IP based sibling of the denial of service test above: instead of one logged in user flooding change requests, many different IP addresses each send too many requests in a short period, and the pod must blacklist each abusing IP and, once more than one IP is abusing, automatically raise the pod to IP whitelist mode. The existing pieces are the IP branch of `src/main/php/web/server_guard.php` (`ip_whitelist_active`, `server_admin/ip_whitelist.txt`, `ip_allowed()` with CIDR matching, `optional/ip_reject.html`), the IP toggle in `http/server_admin.php`, and the initial `ip_blacklist.json` (see `src/main/php/cfg/const/files.php::IP_BLACKLIST_FILE` and the `ip_ranges` test constants). The automatic per-IP request rate limit and the auto-switch to IP whitelist mode still have to be wired up. Build the prompts below in order, same as for the single-user test: enforcement and testable config knobs first, then the actual distributed test.

add a per-IP request rate limit: count the requests coming from one client IP (`$_SERVER['REMOTE_ADDR']`, passed in as a parameter, never read as a superglobal inside the enforcement method) within a configured period and reject requests from an IP that exceeds the limit. Add the knob to config.yaml next to the existing `max change` values, e.g. `zukunft.com > system configuration > user > default > backend > max requests > per minute > ip` for the raw request flood limit (distinct from `max change` which counts stored changes), and read it through the normal config accessor. Note that a DDoS is about request volume, not only stored changes, so this limit must also cover anonymous read/GET requests that never reach the change logic.

when an IP exceeds its request rate limit add that IP (or its /32 resp. /128 range) to the database IP blacklist that `server_guard.php` and `http/server_admin.php` already refer to as the fallback, reusing the same blacklist storage that `ip_blacklist.json` seeds. From then on reject every further request from that IP early in `server_guard.php` with `optional/ip_reject.html`, the same page an active IP whitelist uses for a non-listed IP. Keep the automatic IP blacklist independent of the file based `ip_whitelist.txt` so the manual whitelist and the automatic blacklist do not overwrite each other.

add a system configuration knob `ip whitelist auto switch` = the number of distinct blacklisted IPs within the detection period that automatically activates the IP whitelist mode (the `ip_whitelist_active` flag in `server_admin/state.json` that the admin page toggles). Default it to a high, production-safe value. When the number of freshly blacklisted IPs reaches the threshold, set `ip_whitelist_active` to true exactly as the admin page would, and log a warning so the admins see why the pod switched to IP whitelist mode. Note that activating an empty IP whitelist locks everyone out (the warning already emitted by `server_guard::warn_if_empty_ip_whitelist`), so on the auto-switch the current admin / server IPs must be seeded into `ip_whitelist.txt` first.

write the distributed denial of service test itself (a dedicated test under `src/test/php/`, runnable via `php test_unit.php`, admin via the IP_ADMIN fallback on the CLI). As the first step lower the two knobs to test values and remember the previous values for restore at the end: set the per-IP `max requests` limit to a very low number (e.g. 2 requests per period) and set `ip whitelist auto switch` to 2 IPs. Assert that reading the knobs back returns the lowered values before continuing. Since the CLI has no real remote address, drive the requests through a helper that lets the test set the client IP per request (the same value `server_guard.php` reads from `REMOTE_ADDR`), so the test can simulate distinct source IPs.

in the same test simulate one IP address that sends more requests than the lowered per-IP limit in a short period. Assert that after crossing the limit that IP is added to the database IP blacklist, and assert that the next request from that already-blacklisted IP is rejected by `server_guard.php` with the `optional/ip_reject.html` page (403). The IP whitelist mode must still be inactive at this point because only one IP has been blacklisted (threshold is 2).

extend the test with a second, different IP address that also sends too many requests in a short period. Assert that once the second distinct IP is blacklisted the `ip whitelist auto switch` threshold of 2 is reached and `ip_whitelist_active` in `server_admin/state.json` flips to true automatically. Then assert that a request from a further, non-whitelisted IP is now rejected with `optional/ip_reject.html`, that a request from an allowed IP in `ip_whitelist.txt` still passes, and that the server admin page (`http/server_admin.php`) reports the IP whitelist as active - check the rendered state it reads via `read_state()` and shows as "IP whitelist: active".

as the final step reset everything to the pre-test state: restore the per-IP `max requests` limit and the `ip whitelist auto switch` knob to their remembered default values, clear the test IPs from the database IP blacklist, and switch the IP whitelist mode off again through the same code path the server admin page uses to deactivate it (the `toggle ip whitelist` POST action in `http/server_admin.php`; note that only a full-access admin may switch the IP whitelist off, restricted admins may not), leaving `ip_whitelist_active` false in `server_admin/state.json`. Assert that after the reset a normal request from a fresh IP succeeds again, so the test is self-cleaning and leaves no active whitelist or blacklist behind.

## fine-tuning for next launch

before the program or database upgrade script are started the actual program version should be check and the execution should be rejected if there is no mathing script or no new version

before the database upgrade is executed the program should always be updated first, because the upgrade script is part of the program

if ip and user whitelist configuration are switched off and the server is back to the normal stat copy the backup /optional/index.html page to the www root to restore the normal frontpage.

create release scripts for mayor, main, minor and micro releases, which increase the specific release number and reset the minor release number e.g. main changes from 1.2.3.4 to 1.3.0.0

move the release number in a file that is included in git and that can be accessed from the sever admin page without db access

Create a page named 'create_release.php' that can be started on a development pc and starts test.php and if successful, increase the version and created a merge request to the release branche. This script can only be started if the development branch is checked out

Create a page named 'deploy_to_prod.php' that can be started on a test environment instance that starts test.php and if successful, deletes all test script (/test/* and /src/test/), creates a merge request to the master branche. This script can only be started if the .env ENV=test .

add to the 'create_release.php' page a refresh of the prod database to the test server

### tutorial

add a few tutorial pages that explain:

The main start page shows the "most relevant" global problems with the solution that most likely can fix these issues

"Most relevant" is defined by an extended UN DALY Index that includes physical needs and security called "Happy Time Point". Click on the blue Links for more details.

If you wonder how the list is calculated you can click on the number to see more details. The calculation is based on many assumptions and yes, they can be wrong.

Ich you create an account and log in, you can change the assumptions. The list is than adjusted based on your personal assumptions, but only for you. 

Other user still see the original list, but they can see, that you have done some changes and they can also use your assumptions if they want.

So each your has its own "sandbox", that never changes, but can see everything that other users have done.

### fill the screen 

for big screens add more data so that the screen is filled or increase the font size

### more

"more" should always be a link that shows more values

### speed 

check why the database loading takes longer if more data ist added and increase the cache usage


### workflow

add a formula del workflow similar to delete the added formula similar to src/test/resources/web/html/workflow/del_triple_wf6

add a formula add fail workflow to add a formula similar to src/test/resources/web/html/workflow/add_triple_fail_wf11

add a formula change fail workflow to change a formula similar to src/test/resources/web/html/workflow/chnage_triple_fail_wf12

add a formula del workflow fail similar to delete the added formula similar to src/test/resources/web/html/workflow/del_word_fail_wf10

add a value add workflow to add a value similar to src/test/resources/web/html/workflow/add_triple_wf4

add a value change workflow to change a value similar to src/test/resources/web/html/workflow/change_triple_wf5

add a value del workflow similar to delete the added value similar to src/test/resources/web/html/workflow/del_triple_wf6

add a value add fail workflow to add a value similar to src/test/resources/web/html/workflow/add_triple_fail_wf11

add a value change fail workflow to change a value similar to src/test/resources/web/html/workflow/chnage_triple_fail_wf12

add a value del workflow fail similar to delete the added value similar to src/test/resources/web/html/workflow/del_word_fail_wf10

add to /docs/llm/* that all function that could create an error that is relevanz for the user needs $msg as parameter to be able to return the specific error with the potential solution to the user. This is relevant for example for src/main/php/cfg/word/triple.php::verb_from_api_json

reduce the number of load and save calls

why does src/test/resources/web/html/workflow/change_word_wf2/wf2_show.html contain 'the name of the word must not be empty'? I guess this should not be the case.


check where something like '$usr_msg->add_message($result_msg->get_last_message());' is used and use instead the merge function  

check that save() never fails add() silently

in test_triples base the fill_url_array on the a ..._filled() function that returns an object and create the url_array using a object_to_url function. Note 

rename the src/test/resources/web/html/workflow/change_word_invalid_wf7 to change_word_fail_wf7 because 'fail' is shorter and it is inline with 'add_word_fail_wf9' and change the 'invalid' to 'fail' within this wf code

for each positiv workflow test create a negativ with the test cases that can fail. Let's start with 'add_word_wf1' and create 'add_word_fail_wf9' that has e.g. the steps 'wf9_edit_no_name_save'. 

add in the word_default view beside the 'view' and 'changes' a 'your' tab with the use overwrite of the shown e.g. word object, so basically the values from the 'user_words' table

make sure that all selectors create a hidden form field with the original values as done in sandbox::share_type_selector

use function like src/main/php/shared/helper/Translator.php::text_db_table and _action functions always if a db field name is shown to the user. Call the function as late as possible. And add this as a rule to /docs/llm/* for future code changes. 

create function like src/main/php/shared/helper/Translator.php::text_db_field for alle types that are part of src/main/resources/db_code_links



see /docs/llm/coding.md and in union queries created by the sql_creator the parameters are added to the par array, but if the parameter name matches, the parameter should not be repeated.


add to /docs/llm/* that the $test_name should always be unique. And write a php_code_check script that checks if the $test_names are unique for all tests

after adding a word the word as it has been saved in the database should be shown. Because the db id is not yet known, that word name should be used to load the word. this implies that the url for /src/test/resources/web/html/workflow/add_word_wf1/wf1_edit_back_edit_save_cancel_edit_save_add_confirmed_url.txt should contain '"url_part_back": {"mask_id": "word_default", "name": "System Test Word"}' using the short url var for the name 'k'  

update the confirm change view to shows the user changes (based on the '8' prefixed values) and call the page when in the word edit view save is pressed

complete the 'to_url_array' function for all word fields e.g. the sandbox fields and add a TODO that this should be moved the test object because it will probably only be used for testing. But this can only be decided after the workflows are complete 

add a snap timestamp to the change log. The snap timestamp is the time when the user has called the edit view that he used to apply the changes so the db snap that has been the base for the change. Even if live update of the edit view would be possible, this is not recommended not tp break the user workflow. The max would be to show a refresh icon so that the user could refresh the edit view, but this is prio 4.

in the view 'Change word' adjust the url on the save button so that it fix the error messages 'url key "mask_id" is missing, url mapper for "mask" is missing, url mapper for "id" is missing, url mapper for "back" is missing, url mapper for "confirm" is missing, url mapper for "Name" is missing, url mapper for "py" is missing, url mapper for "Description" is missing, url mapper for "Plural" is missing, url mapper for "d" is missing, url mapper for "s" is missing, url mapper for "sp" is missing' caused by calling the url 'http://localhost/http/view.php?mask=3&id=259&back=259&confirm=1&Name=USD&py=3&Description=ISO+4217+alphabetic+code+for+the+United+States+dollar.&Plural=&d=0&s=1&sp=1' ; the expected result is that it should show the "Confirm update" view with the changes that the user has done and after pressing confirm that database row should be updated and the user should see th original page again, but with the updates , create first unit tests for the workflow using src/test/php/unit_workflow/all_workflow_tests.php

test.php gates this call under the WORKFLOW_TEST const, while the other DB-write tests run under a separate WRITE_TEST const. By folding the write workflow into all_workflow_tests::run, the write workflow now runs whenever WORKFLOW_TEST is on — independent of WRITE_TEST. That matches your "never differ" goal (it's how test_workflow.php already behaves — no WRITE_TEST gate there), but it does mean a WORKFLOW_TEST=true, WRITE_TEST=false run will now touch the DB.

move the field name const for all objects that might use the change_confirm view to src/main/php/shared/const/fields similar to src/main/php/shared/const/fields and use the shared ALL_FIELDS const to order the fields in the confirm_change view e.g. user_db, component_links

create a const for all db field comments e.g. move 'the user-specific geolocation change' in sandbox_value to a VALUE_GEO_COM const

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

### frontend requests

If the frontend requests data from the backend, the backend sends cached data without refresh whenever possible

but the backend adds a refresh trigger th the cache

the triggers are picked up by the crontab job and the cache is refreshed

a pure html frontend tries retries after an increasing time period to get the updated cache from the backend and stops the retry if it has received the update

a js frontend can use subscribe to get the updated cache data

additional the cache can be updated by time or by trigger words without frontend request


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

### word add view (missing parts of the retired http_old/word_add.php)

the legacy controller http_old/word_add.php has been replaced by the word_add view called via /http/view.php?m=2 (views::WORD_ADD_ID). The plain "create a word" case is fully covered by the new view (it even adds description, plural, default view, share and protection type plus the confirm step), but the following features of the old controller have no equivalent yet. Add them to the new view.

allow the user to link the new word to an existing word while adding it, as the old controller did with the url parameters 'add', 'verb' and 'word': add a verb and a target phrase selector to the word_add form, read them in web/word/word.php::url_mapper via url_var::VERB and url_var::WORD and, after the word has been created, save the corresponding triple. Without this the link parameters are silently dropped

fix the "Add similar word" button in src/main/php/web/word/triple_list.php that already calls url_new(views::WORD_ADD_ID, ...) with 'verb=...&word=...&type=...': it uses the human readable keys instead of the url_var codes (url_var::VERB 'b', url_var::WORD 'w', url_var::TYPE 'y' resp. url_var::PHRASE_TYPE 'py'), so even after the mapper is extended the parameters would not be picked up. The preset of the phrase type via the url is part of this

add the alternative "link an existing word" path of the old controller: a submit without a new name but with an existing word, a verb and a target word must create only the triple and not a new word. Extend web/word/word.php::input_valid with the old combined check "Either enter a name for the new word or select an existing word to link" instead of only the empty name warning of sandbox_named::input_valid

add the duplicate name pre-check of the old controller to web/word/word.php::input_valid: check via the term namespace (word, triple, formula and verb) if the name is already used and show the message of cfg/phrase/term.php::id_used_msg_text on the edit form. At the moment a name collision is only detected by the backend during the save, so the user gets the error only after the confirm step

add the duplicate and reverse link check of the old controller to web/word/triple.php::input_valid (which today only checks that both phrases are set): warn with '... already exists' if the triple exists in the same direction and with 'The reverse of ... already exists. Do you really want to add both sides?' if only the reverse link exists. This is needed for the triple_add view as well, not only for the word_add view

### word edit view (missing parts of the retired http_old/word_edit.php)

the legacy controller http_old/word_edit.php has been replaced by the word_edit view called via /http/view.php?m=3 (views::WORD_EDIT_ID). All fields of the old controller (name, plural, description, phrase type and default view) are part of the new edit form and the save now runs through the confirm step. Its only remaining caller was the 'switch' button of the view box (web/view/view.php::switch_link, used by web/component/execute/ui_list.php::view_tab_box), which called /http/word_edit.php?id=<word>&d=<view>&confirm=1 and wrote the new default view with one click. The button now only opens the word edit form, so the following parts are still missing.

restore the one-click 'switch' of the default view of a word: web/view/view.php::switch_link knows the target view, but /http/view.php?m=3 cannot write it yet. frontend.php::url_to_html already contains the mechanism (rest_ctrl::PAR_VIEW_NEW_ID 'new_id' -> $dbo->save_view($new_view_id) for the EDIT_DEL_MASKS_IDS), but web/sandbox/sandbox_named.php::save_view is an empty stub that returns an ok user_message without writing anything and that does not even accept the view id it is called with. Implement save_view (via the api, the same path as db_object::update) and let switch_link call /http/view.php?m=3&id=<word>&new_id=<view>, or route the switch through the standard confirm flow. Add a test that the default view of the word is really changed in the database after the switch

alternatively (or additionally) preselect the target view when the switch button opens the word edit form. This is not possible at the moment, because frontend.php::url_has_object_values treats any non control var in the url (e.g. url_var::VIEW 'd') as "the url carries the object values", so the word is no longer loaded from the database and the form would be shown with an empty name, description and plural. Either mark a prefilled edit link explicitly (e.g. load from the url only if all fields of the object are given) or merge the url values into the object loaded by id

the 'switch' and 'view' buttons of the view box are also rendered for a triple (ui_list::view_tab_box handles word and triple), but switch_link always uses the word edit mask. Use the edit mask of the object type (views::WORD_EDIT_ID resp. views::TRIPLE_EDIT_ID) so that the switch also works for a triple

the old controller answered an empty name with the hint 'An empty name should never be saved. Please delete the word instead.'; the new flow only shows the generic msg_id::NAME_EMPTY warning of sandbox_named::input_valid. Add the hint to delete the object instead to the empty name warning of an edit view (not of an add view)

### view add view (missing parts of the retired http_old/view_add.php)

the legacy controller http_old/view_add.php has been replaced by the view_add view called via /http/view.php?m=30 (views::VIEW_ADD_ID), which the 'add view' button of the navbar already uses. The new form has all fields of the old controller (name, description and view type) plus the view style, the share and the protection type, and it saves through the confirm step. The remaining callers of the old controller were the legacy render path web/view/view.php::dsp_navbar_html (only used if html_base::UI_USE_BOOTSTRAP is false) and the 'add view component' button of view.php resp. view_exe.php::linked_components; both now call url_new(views::VIEW_ADD_ID, ...). The following parts of the old controller have no equivalent yet.

after a new view has been created open the view edit view (m=31) for it, so the user can directly add the components: the old controller showed the new view with its component list (view::dsp_edit) instead of just returning to the calling page, and it carried the TODO to set the new view as the default view of the sample term. Today /http/view.php?m=30 only creates the view and goes back, so a new view stays empty and the user has to find it again to add any component

add the sample word of the old controller ('word' url parameter, used to simulate how the new view looks for a real term) to the view add and the view edit view. Without it the user creates a view without seeing its effect

the phrase type resp. view type could be preset via the url ('type') in the old controller. This is not possible in the new view for the same reason as for the word add view (frontend::url_has_object_values, see the word edit view section above)

### view edit view (missing parts of the retired http_old/view_edit.php)

the legacy controller http_old/view_edit.php has been replaced by the view_edit view called via /http/view.php?m=31 (views::VIEW_EDIT_ID), which the 'change view' link of the navbar and the 'design the view' button of the view selector already use. The new form has all fields of the old controller (name, description and view type) plus the view style, the share and the protection type, and it saves through the confirm step. But the old controller was not only a form for these fields, it was the view designer, and that part is completely missing in the new view.

add the component list of a view to the view edit view (m=31), so that a view can be designed again. The old controller could, for the view given by the id: link an existing component ('add_component' with the component id), create a new component and link it ('entry_name' plus 'new_entry_type'), unlink a component ('del' with the component id) and change the order of the components ('move_up' / 'move_down' with the component id, using view::entry_up and entry_down). Today /http/view.php?m=31 shows only the fields of the view itself, so a view can neither get a component nor lose one, and the order of the components cannot be changed from the frontend. Reuse the existing component views (views::COMPONENT_ADD_ID ff) and the component_link views for the single steps and add a test per step

add the sample word to the view edit view as well ('word' url parameter of the old controller, the term used to simulate how the view looks with real data), see the view add view section above

remove the now unreachable legacy view designer code: web/view/view.php and web/view/view_exe.php contain a nearly identical private linked_components() (a DRY violation) plus dsp_edit(), which were only called by http_old/view_add.php and http_old/view_edit.php. Their remaining buttons link to api::DSP_COMPONENT_LINK / DSP_COMPONENT_ADD, i.e. to controllers that are also already retired to /http_old, so these links are dead. Remove dsp_edit / linked_components (and the DSP_COMPONENT_* consts in shared/api.php) once the component list is part of the view edit view. The same applies to view::dsp_navbar_html (only used if html_base::UI_USE_BOOTSTRAP is false) and to view::selector_page, which is only called by the legacy http/view_select.php

remove the dead class web/user/user_display_old.php: it is only included by src/test/php/utils/test_base.php, is never instantiated, uses rest_ctrl without importing it (so it would fail if it were called) and links to controllers that are retired (view_edit.php, value_edit.php, user_triple.php, user_value.php, user_formula_link.php)

### view del view (missing parts of the retired http_old/view_del.php)

the legacy controller http_old/view_del.php has been replaced by the view_del view called via /http/view.php?m=32 (views::VIEW_DEL_ID), which the 'delete the view' button of the view selector already uses. The new view shows the name of the view to delete and writes the delete through the confirm step, so it covers the yes/no dialog of the old controller completely. No feature of the old controller is missing, but the delete itself is not protected.

add an in-use check to the delete of a view: web/view/view.php has no input_valid override, so unlike web/word/word.php::input_valid (msg_id::DELETE_IN_USE via is_in_use) a view can be deleted even if it is still the default view of a word or a triple, or if components are still linked to it. Add the check to the frontend view object (and the matching negative test) so the user gets a warning instead of dangling references. The old controller did not check this either, so this is not a regression of the migration but a gap of both

### formula add view (missing parts of the retired http_old/formula_add.php)

the legacy controller http_old/formula_add.php has been replaced by the formula_add view called via /http/view.php?m=24 (views::FORMULA_ADD_ID), which the 'add formula' button already uses (web/formula/formula.php::VIEW_ADD). The new form has all fields of the old controller (name, formula expression, description, formula type and the 'need all values' flag) plus the default view, the share and the protection type, and it saves through the confirm step. The old controller had no caller left in the program code, only in a commented out block of src/test/php/unit_ui/formula_ui_tests.php that calls the page of the productive server via file_get_contents. The following parts have no equivalent yet.

assign the new formula to a phrase while adding it, as the old controller did with the 'word' url parameter (formula::link_phrase_and_save after the save): a formula that is not assigned to any phrase is not shown anywhere, so today a formula added via /http/view.php?m=24 disappears for the user. Add the phrase selector to the formula add form, read it in web/formula/formula.php::url_mapper and save the formula link after the formula. The old check 'Word missing; Internal error, because a formula should always be linked to a word or a list of words.' belongs to this, but as a user warning of formula::input_valid, not as an internal error

add the missing input checks to web/formula/formula.php::input_valid, which today does not exist, so only the empty name warning of sandbox_named::input_valid is shown: warn if the formula expression is empty ('Formula text missing; Please define how the calculation should be done.') and if the name is already used by a word, verb, triple or formula (the term namespace check of the old controller via formula::get_term and term::id_used_msg_text, see also the word add view section above)

remove the commented out block in src/test/php/unit_ui/formula_ui_tests.php that tests the retired formula_add.php and formula_edit.php pages of the productive server via file_get_contents('https://zukunft.com/...'); a unit test must never call an external server. Replace it with a page test of the formula add view (m=24) based on the local html snapshot

### formula edit view (missing parts of the retired http_old/formula_edit.php)

the legacy controller http_old/formula_edit.php has been replaced by the formula_edit view called via /http/view.php?m=25 (views::FORMULA_EDIT_ID), which the 'change formula' button already uses (web/formula/formula.php::VIEW_EDIT). The new form has all fields of the old controller (name, formula expression, description, formula type and the 'need all values' flag) plus the default view, the share and the protection type, it shows the assigned words and triples, the results, the related formulas and the change log, and it saves through the confirm step. The old controller had no caller left in the program code, only in commented out blocks of src/test/php/unit_ui/formula_ui_tests.php and src/test/php/unit_write/formula_write_tests.php. What is missing is the maintenance of the phrase assignments.

make the assigned words and triples of the formula edit view (m=25) changeable: today they are only shown as links. The old controller could link another phrase to the formula (url_var::LINK_PHRASE 'fl' -> formula::link_phrase_and_save), unlink a phrase (url_var::UNLINK_PHRASE -> formula::unlink_phrase) and show a phrase selector to pick the phrase to link ('add_link'). Add an unlink button per assigned phrase and one add button with a phrase selector, both writing through the standard confirm flow, and add a test per step. This is the same missing assignment as in the formula add view above, so solve both together

after a formula has been changed the results that depend on it must be updated: the old controller had the trigger for it (formula::needs_res_upd -> assign_phr_lst, with the calc call already commented out). Check whether the backend save of a formula updates the depending results and if not, add the update and a test that a changed formula expression changes the result of an assigned phrase

### formula del view (missing parts of the retired http_old/formula_del.php)

the legacy controller http_old/formula_del.php has been replaced by the formula_del view called via /http/view.php?m=26 (views::FORMULA_DEL_ID), which the 'delete formula' button already uses (web/formula/formula.php::VIEW_DEL). The new view shows the name of the formula to delete and writes the delete through the confirm step. The old controller had no caller left in the program code, only in the commented out block of src/test/php/unit_ui/formula_ui_tests.php. What is missing is that the user is told what really happens with the formula.

tell the user in the formula del view (m=26) if the formula is only excluded and not removed: the old controller asked 'Exclude "<name>"?' instead of 'Delete "<name>"?' if formula::is_used() was true, because a formula that is still used by another user or by a result can only be excluded, not deleted. The new view always says 'delete'. Show the exclude wording (and the reason) if the object is still in use, best as a shared solution for all del views, because the same applies e.g. to the view del view (see the section above) and to the word del view, where web/word/word.php::input_valid already warns with msg_id::DELETE_IN_USE via is_in_use

### value add view (missing parts of the retired http_old/value_add.php)

the legacy controller http_old/value_add.php has been replaced by the value_add view called via /http/view.php?m=18 (views::VALUE_ADD_ID), which the 'add value' button already uses (web/html/button.php::add_value with url_new(views::VALUE_ADD_ID)). The new form has the value, the description, the source, the value type, the view style, the default view, the share and the protection type and it saves through the confirm step, so it has more fields than the old controller. But the phrases of the value, which are its key, are only a free text 'group' field, and the conversion of the user entry is lost.

give the value add view (m=18) the phrases of the new value: the old controller took them from the url ('phrase1', 'phrase2', ... with the matching 'type1', 'type2', ... to preselect the phrase type, or 'phrases' as a comma separated id list) and preloaded the value with them, so that 'add a new value similar to <phrase list>' really preselected the phrases of the calling page. The new view has only the free text field url_var::GROUP_NAME ('gn'), and the prefill is switched off in the button itself (web/html/button.php::add_value, '// TODO Prio 2 activate  //$url_phr = $phr_lst->id_url_long();'). Add a phrase selector to the value add form, fill it from the url of the calling page and activate the prefill in the button

call the value conversion again when a value is saved: cfg/value/value_base.php::convert() (it removes spaces and thousand separators from the user entry and sets the number) is no longer called by any live code, only the retired controllers called it. Check where the user entry of the value form (url_var::VALUE 'v') is converted to the database number today, add the conversion to the write path of the value if it is missing and add a test with a user entry like "1'000"

save the source of a value as the new default source of the user: the old controller stored the selected source on the user (user::src) and saved the user, so that the next value of the same user got the same source suggested. The new value add view has the source selector, but does not remember the choice

### value edit view (missing parts of the retired http_old/value_edit.php)

the legacy controller http_old/value_edit.php has been replaced by the value_edit view called via /http/view.php?m=19 (views::VALUE_EDIT_ID), which the 'change value' button already uses (web/value/value.php::VIEW_EDIT_ID). The new form has the same fields as the value add view (value, description, source, value type, view style, default view, share and protection type) and it saves through the confirm step. The old controller had no caller left in the program code, only in commented out blocks of the value tests (and in the dead class web/user/user_display_old.php, see above). The missing parts are the same as for the value add view plus the check of an empty value.

show and change the phrases of a value in the value edit view (m=19): the old controller loaded the value by id including its phrases and let the user add or remove a phrase ('phrase1', 'phrase2', ... with the matching 'type1', 'type2', ...), showing the form again after each change. The new view has only the free text field url_var::GROUP_NAME ('gn'), which is even empty in the rendered page, so the phrases of a value can neither be seen nor changed. Solve it together with the phrase selector of the value add view (see the section above)

add web/value/value.php::input_valid, which does not exist today, so an empty number is saved without any warning: the old controller answered an empty value with 'An empty number should not be saved. Please delete/exclude the value instead.' and used the value from the database as a fallback if only the phrases had been changed. Add both, with a negative test for the empty value

### value del view (missing parts of the retired http_old/value_del.php)

the legacy controller http_old/value_del.php has been replaced by the value_del view called via /http/view.php?m=20 (views::VALUE_DEL_ID), which the 'delete value' button already uses (web/value/value.php::VIEW_DEL). The new view asks for the confirmation and writes the delete through the confirm step. The old controller had no caller left in the program code, only in the commented out block of src/test/php/unit_ui/value_ui_tests.php. What is missing is the phrases of the value in the question to the user.

show the phrases of the value in the value del view (m=20): the old controller loaded them (value::load_phrases) and asked '<number> for <phrase names>?', while the new view shows only the number, e.g. '3.14', so the user cannot see which of his values he is about to delete. Add the phrase list of the value to the delete question (and the same for the value edit view, see the section above). Note that a value has no name, so unlike a word the number alone does not identify it

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