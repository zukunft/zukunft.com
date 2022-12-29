<?php

/*

    zu_lib.php - the main ZUkunft.com LIBrary
    ----------

    for coding new features the target process is before committing:
    1. create a unit test for the new feature
    2. code the feature and fix the unit tests and code smells
    3. create and fix the database unit and integration test for the new feature
    4. commit

    but first this needs to be fixed:
    TODO unit test: create a unit test for all possible class functions next to review: formula expression
    TODO load_by_vars: replace the load_by_vars with more specific load_by_ functions
    TODO api load: expose all load functions to the api (with security check!)
    TODO use always prepared queries based on the value_phrase_link_list_by_phrase_id.sql sample
    TODO fix error in upgrade process for MySQL
    TODO fix syntax suggestions in existing code
    TODO add the view result at least as simple text to the JSON export

    after that this should be done while keeping step 1. to 4. for each commit:
    TODO use the sandbox list for all user lists
    TODO use in the frontend only the code id of types
    TODO use in the backend always the type object instead of the db type id
    TODO always use the frontend path CONST instead of 'http'
    TODO replace the fixed edit masks with a view call of a mask with a code id
    TODO review cast to display objects to always use display objects
    TODO make all vars of display objects private or protected
    TODO move display functions to frontend objects
    TODO check that all queries are parameterized by setting $db_con->set_name
    TODO add text report table to save a text related to a phrase group (and a timestamp)
    TODO check that all load function have an API and are added in the OpenAPI document
    TODO use the api functions and the html frontend function
    TODO create a vue.js based frontend
    TODO capsule (change from public to private or protected) all class vars that have dependencies e.g lst of user_type_list
    TODO split frontend and backend an connect them using api objects
    TODO add a text export format to the display objects and use it for JSON import validation e.g. for the travel list
    TODO add simple value list import example
    TODO add environment variables e.g. for the database connection
    TODO add a key store for secure saving of the passwords
    TODO add a trust store for the base url certificates to avoid man in the middle attacks
    TODO add simple value list table with the hashed phrase list as key and the value
    TODO add a calculation validation section to the import
    TODO add a text based view validation section to the import
    TODO add a simple UI API JSON to text frontend for the view validation
    TODO exclude any search objects from list objects e.g. remove the phrase from the value list which implies to split the list loading into single functions such as load_by_phr
    TODO use a key-value table without a phrase group if a value is not user specific and none of the default settings has been changed
         for the key-value table without a phrase group encode the key, so that automatically a virtual phrase group can be created
         e.g. convert -12,3,67 to something like 4c48d5685a7e with the possibility to reverse
    TODO move all sample SQL statements from the unit test to separate files for auto syntax check
    TODO check that all sample SQL statements are checked for the unique name and for mysql syntax
    TODO cleanup the objects and remove all vars not needed any more e.g. id arrays
    TODO if a functions failure needs some user action a string the the suggested action is returned e.g. save() and add()
    TODO if a function failure needs only admin or dev action an exception is raised and the function returns true or false
    TODO if an internal failure is expected not to be fixable without user interaction, the user should ge a failure link for the follow up actions
    TODO review the handling of excluded: suggestion for single object allow the loading of excluded, but for lists do not include it in the list
    TODO capsule in classes
    TODO create unit tests
    TODO cleanup object by removing duplicates
    TODO call include only if needed
    TODO use the git concept of merge and rebase for group changes e.g. if some formulas are assigned to a group these formulas can be used by all members of a group
    TODO additional to the git concept of merge allow also subscribe or auto merge
    TODO create a simple value table with the compressed phrase ids as a key and the value as a key-value table
    TODO check that all class function follow the setup suggested in user_message
    TODO move all tests to a class that is extended step by step e.g. test_unit extends test_base, ...
    TODO make sure that no word, phrase, verb and formula have the same name by using a name view table for each user
    TODO add JSON tests that check if a just imported JSON file can be exactly recreated with export
    TODO if a formula is supposed to be created with the same name of a phrase suggest to add (formula) at the end
    TODO create a test case where one user has created a word and another user has created a formula with the same name
    TODO all save and import functions should return an empty string, if everything is fine and otherwise the error message that should be shown to the user
    TODO in load_standard the user id of db_con does not need to be set -> remove it
    TODO create json config files for the default and system views
    TODO add JSON im- and export port for verbs
    TODO remove the database fields from the objects, that are already part of a linked object e.g. use ref->phr->id instead of ref->phr_id
    TODO allow to load user via im- and export, but make sure that no one can get higher privileges
    TODO replace to id search with object based search e.g. use wrd_lnk->from->id instead of wrd_lnk->from_id
    TODO add im- and export of users and move the system user loading to one json
    TODO create the unit tests for the core elements such as word, value, formula, view
    TODO review types again and capsule (move const to to base object e.g. the word type time to the word object)
    TODO for import offer to use all time phrases e.g. "year of fixation": 1975 for "speed of light"
    TODO split the database from the memory object to save memory
    TODO add an im- and export code_id that is only unique for each type
    TODO move init data to one class that creates the initial records for all databases and create the documentation for the wiki
    TODO use the type hash tables for words, formulas, view and components
    TODO create all export objects and add all import export unit tests
    TODO complete the database abstraction layer
    TODO create unit tests for all module classes
    TODO name all queries with user data as prepared queries to prevent SQL code injections
    TODO split the load and the load_sql functions to be able to add unit tests for all sql statements
    TODO crawl all public available information from the web and add it as user preset to the database
    TODO rename dsp_text in formula to display
    TODO rename name_linked in formula_element to name_linked
    TODO separate the API JSON from the HTML building e.g. dsp_graph should return an JSON file for the one page JS frontend, which can be converted to HTML code
    TODO use separate db users for the db creation (user zukunft_root), admin (user zukunft_admin), the other user roles and (zukunft_insert und zukunft_select) as s second line of defence
    TODO check all data from an URL or from a user form that it contains no SQL code
    TODO move the init database fillings to on class instead of on SQL statement for each database
    TODO prevent XSS attacks and script attacks
    TODO check the primary index of all user tables
    TODO load the config, that is not expected to be changed during a session once at startup
    TODO start the backend only once and react to REST calls from the frontend
    TODO make use of __DIR__ ?
    TODO check the install of needed packages e.g. to make sure curl_init() works
    TODO create a User Interface API
    TODO offer to use FreeOTP for two factor authentication
    TODO change config files from json to yaml to complete "reduce to the max"
    TODO create a user cache with all the data that the user usually uses for fast reactions
    TODO move the user fields to words with the reserved words with the prefix "system user"
    TODO for the registration mask first preselect the country based on the geolocation and offer to switch language, than select the places based on country and geolocation and the street
    TODO in the user registration mask allow to add places and streets on the fly and show a link to add missing street on open street map
    TODO use the object constructor if useful
    TODO capsule all critical functions in classes for security reason, to make sure that they never call be called without check e.g. database reset
    TODO to speed up create one database statement for each user action if possible
    TODO split the user sandbox object into a user sandbox base object and extend it either for a named or a link object
    TODO remove e.g. the word->type_id field and use word->type->id instead to reduce the number of fields
    TODO try to use interface function and make var private to have a well defined interface
    TODO remove all duplicates in objects like the list of ids and replace it by a creation function; if cache is needed do this in the calling function because this knows when to refresh
    TODO allow admin users to change IP blacklist
    TODO include IP blacklist by default for admin users
    TODO add log_info on all database actions to detect the costly code parts
    TODO move the environment variables to a setting YAML like application.yaml, application-dev.yaml, application-int.yaml or application-prod.yaml in springboot
    TODO create a sanity API for monitor tools like checkmk or platforms like openshift
    TODO create an "always on" thread for the backend
    TODO create a LaTeX extension for charts and values, so that studies can be recreated based on the LaTeX document
    TODO for fail over in the underlying technologies, create a another backend in python and java  and allow the user to select or auto select the backend technology
    TODO for fail over in the underlying database technologies, auto sync the casandra, hadoop, postgreSQL and mariaDB databases
    TODO auto create two triple for an OR condition in a value selection; this implies that to select a list of values only AND needs to be used and brackets are also not needed
    TODO add a phrase group to sources and allow to import it with "keys:"
    TODO allow to assign more phrases to a source for better suggestion of sources


    TODO create a table startup page with a
         Table with two col and two rows and four last used pages below. If now last used pages show the demo pages.
         Typing words in the top left cell select a word with the default page
         Typing in the top right cell adds one more column and two rows and typing offer to select a word and also adds related row names based on child words
         Typing in the lower left cell also adds one more row and two cols and typing shows related parent words as column headers
         Typing in the lower right cell starts the formula selection and an = is added as first char.
         Typing = in any cell starts the formula selection
         Typing an operator sign after a space starts the formula creation and a formula name is suggested


    TODO split the frontend from the backend
         the namespaced should be
         - api: for the frontend to backend api objects that e.g. does not contain data of other users and the access rights
         - html: for the pure html frontend
         - vue: for the vue.js based frontend, which can cache api objects for read only. This implies that the backend has an api to reload single objects
         - db: for the persistence layer

    TODO for all objects (in progress: user)
        1. complete phpDOCS
        2. add type to all function parameter
        3. create unit test for all functions
            a) prefer assert_qp vs assert_sql
            b) prefer assert vs dsp
        4. create db and api tests
        5. update the OpenAPI doc
        6. use parametrized queries
        7. use translated user interface messages
        8. use const
            a) for db fields
        9. remove class and function from debug
       10. capsule object vars
        done:


    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

use html\html_base;

// the fixed system user
const SYSTEM_USER_ID = 1; //

// parameters for internal testing and debugging
const LIST_MIN_NAMES = 4; // number of object names that should al least be shown
const DEBUG_SHOW_USER = 10; // starting from this debug level the user should be shown in the debug text


// the main global vars to shorten the code by avoiding them in many function calls as parameter
global $db_com; // the database connection
global $usr;    // the session user
global $debug;  // the debug level

// global vars for system control
global $sys_script;      // name php script that has been call this library
global $sys_trace;       // names of the php functions
global $sys_time_start;  // to measure the execution time
global $sys_time_limit;  // to write too long execution times to the log to improve the code
global $sys_log_msg_lst; // to avoid repeating the same message

$sys_script = "";
$sys_trace = "";
$sys_time_start = time();
$sys_time_limit = time() + 2;
$sys_log_msg_lst = array();


// set the paths of the program code
$path_php = ROOT_PATH . 'src/main/php/'; // path of the main php source code

// check php version
$version = explode('.', PHP_VERSION);
if ($version[0] < 8) {
    if ($version[1] < 1) {
        echo 'at least php version 8.1 is needed';
    }
}
//check if "sudo apt-get install php-curl" is done for testing
//phpinfo();

// database links
include_once $path_php . 'db/sql_db.php';
include_once $path_php . 'db/db_check.php';
// utils
include_once $path_php . 'utils/json_utils.php';
include_once $path_php . 'model/helper/library.php';
include_once $path_php . 'model/helper/object_type.php';
include_once $path_php . 'model/helper/db_object.php';
include_once $path_php . 'model/helper/db_object_named.php';
include_once $path_php . 'model/helper/type_lists.php';
include_once $path_php . 'model/user/user_type_list.php';
include_once $path_php . 'model/system/list.php';
include_once $path_php . 'model/system/type_list.php';
include_once $path_php . 'model/system/log.php';
include_once $path_php . 'model/system/system_utils.php';
include_once $path_php . 'model/system/system_error_log_status_list.php';
include_once $path_php . 'model/system/ip_range.php';
include_once $path_php . 'model/system/ip_range_list.php';
include_once $path_php . 'model/log/change_log.php';
include_once $path_php . 'model/log/change_log_named.php';
include_once $path_php . 'model/log/change_log_link.php';
include_once $path_php . 'model/log/change_log_action.php';
include_once $path_php . 'model/log/change_log_table.php';
include_once $path_php . 'model/log/change_log_field.php';
include_once $path_php . 'model/log/change_log_list.php';
include_once $path_php . 'model/log/error_log.php';
// service
include_once $path_php . 'service/import/import_file.php';
include_once $path_php . 'service/import/import.php';
include_once $path_php . 'service/export/export.php';
include_once $path_php . 'service/export/json.php';
include_once $path_php . 'service/export/xml.php';
// user sandbox classes
include_once $path_php . 'model/user/user.php';
include_once $path_php . 'model/user/user_message.php';
include_once $path_php . 'model/user/user_type.php';
include_once $path_php . 'model/user/user_profile.php';
include_once $path_php . 'model/user/user_profile_list.php';
include_once $path_php . 'model/user/user_list.php';
include_once $path_php . 'model/sandbox/user_sandbox.php';
include_once $path_php . 'model/sandbox/user_sandbox_named.php';
include_once $path_php . 'model/sandbox/user_sandbox_value.php';
include_once $path_php . 'model/sandbox/user_sandbox_link.php';
include_once $path_php . 'model/sandbox/user_sandbox_link_with_type.php';
include_once $path_php . 'model/sandbox/user_sandbox_link_named.php';
include_once $path_php . 'model/sandbox/user_sandbox_link_named_with_type.php';
include_once $path_php . 'model/sandbox/user_sandbox_named_with_type.php';
include_once $path_php . 'model/sandbox/user_sandbox_exp.php';
include_once $path_php . 'model/sandbox/user_sandbox_exp_named.php';
include_once $path_php . 'model/sandbox/user_sandbox_exp_link.php';
include_once $path_php . 'model/user/user_exp.php';
include_once $path_php . 'model/sandbox/user_sandbox_list.php';
include_once $path_php . 'model/sandbox/user_sandbox_list_named.php';
include_once $path_php . 'model/sandbox/share_type.php';
include_once $path_php . 'model/sandbox/share_type_list.php';
include_once $path_php . 'model/sandbox/protection_type.php';
include_once $path_php . 'model/sandbox/protection_type_list.php';
include_once $path_php . 'web/user_sandbox_display.php';
// system classes
include_once $path_php . 'model/system/system_error_log.php';
include_once $path_php . 'model/system/system_error_log_list.php';
include_once $path_php . 'model/system/batch_job.php';
include_once $path_php . 'model/system/batch_job_list.php';
include_once $path_php . 'model/system/batch_job_type_list.php';
include_once $path_php . 'model/helper/triple_object.php';
// model classes
include_once $path_php . 'model/word/word.php';
include_once $path_php . 'model/word/word_exp.php';
include_once $path_php . 'model/word/word_type.php';
include_once $path_php . 'model/word/word_type_list.php';
include_once $path_php . 'model/word/word_list.php';
include_once $path_php . 'model/word/word_change_list.php';
include_once $path_php . 'model/word/triple.php';
include_once $path_php . 'model/word/triple_exp.php';
include_once $path_php . 'model/word/triple_list.php';
include_once $path_php . 'model/phrase/phrase.php';
include_once $path_php . 'model/phrase/phrase_list.php';
include_once $path_php . 'model/phrase/phrase_list_dsp.php';
include_once $path_php . 'model/phrase/phrase_group.php';
include_once $path_php . 'model/phrase/phrase_group_list.php';
include_once $path_php . 'model/phrase/phrase_group_link.php';
include_once $path_php . 'model/phrase/phrase_group_word_link.php';
include_once $path_php . 'model/phrase/phrase_group_triple_link.php';
include_once $path_php . 'model/phrase/phrase_type.php';
include_once $path_php . 'model/phrase/term.php';
include_once $path_php . 'model/phrase/term_list.php';
include_once $path_php . 'model/verb/verb.php';
include_once $path_php . 'model/verb/verb_list.php';
include_once $path_php . 'model/value/value.php';
include_once $path_php . 'model/value/value_dsp.php';
include_once $path_php . 'model/value/value_exp.php';
include_once $path_php . 'model/value/value_list.php';
include_once $path_php . 'model/value/value_list_exp.php';
include_once $path_php . 'model/value/value_phrase_link.php';
include_once $path_php . 'model/value/value_phrase_link_list.php';
include_once $path_php . 'model/value/value_time_series.php';
include_once $path_php . 'model/ref/source.php';
include_once $path_php . 'model/ref/source_exp.php';
include_once $path_php . 'model/ref/ref.php';
include_once $path_php . 'model/ref/ref_list.php';
include_once $path_php . 'model/ref/ref_exp.php';
include_once $path_php . 'model/ref/ref_type.php';
include_once $path_php . 'model/ref/ref_type_list.php';
include_once $path_php . 'model/ref/source_type.php';
include_once $path_php . 'model/ref/source_type_list.php';
include_once $path_php . 'model/formula/expression.php';
include_once $path_php . 'model/formula/formula.php';
include_once $path_php . 'model/formula/formula_exp.php';
include_once $path_php . 'model/formula/formula_type.php';
include_once $path_php . 'model/formula/formula_type_list.php';
include_once $path_php . 'model/formula/formula_list.php';
include_once $path_php . 'model/formula/formula_link.php';
include_once $path_php . 'model/formula/formula_link_list.php';
include_once $path_php . 'model/formula/formula_link_type_list.php';
include_once $path_php . 'model/formula/formula_value.php';
include_once $path_php . 'model/formula/formula_value_exp.php';
include_once $path_php . 'model/formula/formula_value_list.php';
include_once $path_php . 'model/formula/formula_element.php';
include_once $path_php . 'model/formula/formula_element_type_list.php';
include_once $path_php . 'model/formula/formula_element_list.php';
include_once $path_php . 'model/formula/formula_element_group.php';
include_once $path_php . 'model/formula/formula_element_group_list.php';
include_once $path_php . 'model/formula/figure.php';
include_once $path_php . 'model/formula/figure_list.php';
include_once $path_php . 'model/view/view.php';
include_once $path_php . 'model/view/view_exp.php';
include_once $path_php . 'model/view/view_list.php';
include_once $path_php . 'model/view/view_sys_list.php';
include_once $path_php . 'model/view/view_type_list.php';
include_once $path_php . 'model/view/view_cmp.php';
include_once $path_php . 'model/view/view_cmp_exp.php';
include_once $path_php . 'model/view/view_cmp_dsp.php';
include_once $path_php . 'model/view/view_cmp_type.php';
include_once $path_php . 'model/view/view_cmp_type_list.php';
include_once $path_php . 'model/view/view_cmp_pos_type.php';
include_once $path_php . 'model/view/view_cmp_pos_type_list.php';
include_once $path_php . 'model/view/view_cmp_link.php';
include_once $path_php . 'model/view/view_cmp_link_list.php';
include_once $path_php . 'model/view/view_cmp_link_types.php';
// general frontend API classes
include_once $path_php . 'api/message_header.php';
include_once $path_php . 'api/controller.php';
include_once $path_php . 'api/system/error_log.php';
include_once $path_php . 'api/system/error_log_list.php';
include_once $path_php . 'api/system/type_lists.php';
include_once $path_php . 'api/system/batch_job.php';
include_once $path_php . 'api/sandbox/user_sandbox.php';
include_once $path_php . 'api/sandbox/user_sandbox_named.php';
include_once $path_php . 'api/sandbox/user_sandbox_named_with_type.php';
include_once $path_php . 'api/sandbox/user_sandbox_value.php';
include_once $path_php . 'api/sandbox/user_config.php';
include_once $path_php . 'api/sandbox/list.php';
include_once $path_php . 'api/sandbox/list_value.php';
include_once $path_php . 'api/user/user.php';
include_once $path_php . 'api/user/user_type.php';
include_once $path_php . 'api/user/user_type_list.php';
include_once $path_php . 'api/log/change_log.php';
include_once $path_php . 'api/log/change_log_named.php';
include_once $path_php . 'api/log/change_log_list.php';
// model frontend API classes
include_once $path_php . 'api/word/word.php';
include_once $path_php . 'api/word/word_list.php';
include_once $path_php . 'api/word/triple.php';
include_once $path_php . 'api/phrase/phrase.php';
include_once $path_php . 'api/phrase/phrase_list.php';
include_once $path_php . 'api/phrase/phrase_group.php';
include_once $path_php . 'api/phrase/term.php';
include_once $path_php . 'api/phrase/term_list.php';
include_once $path_php . 'api/verb/verb.php';
include_once $path_php . 'api/value/value.php';
include_once $path_php . 'api/value/value_list.php';
include_once $path_php . 'api/formula/formula.php';
include_once $path_php . 'api/formula/formula_list.php';
include_once $path_php . 'api/formula/formula_value.php';
include_once $path_php . 'api/formula/formula_value_list.php';
include_once $path_php . 'api/view/view.php';
include_once $path_php . 'api/view/view_list.php';
include_once $path_php . 'api/view/view_cmp.php';
include_once $path_php . 'api/view/view_cmp_list.php';
include_once $path_php . 'api/ref/ref.php';
include_once $path_php . 'api/ref/source.php';
// general HTML frontend classes
include_once $path_php . 'web/back_trace.php';
include_once $path_php . 'web/user_display_old.php';
include_once $path_php . 'web/log/change_log_named.php';
include_once $path_php . 'web/log/change_log_list.php';
include_once $path_php . 'web/user_log_display.php';
include_once $path_php . 'web/user/user.php';
include_once $path_php . 'web/user/user_type_list.php';
include_once $path_php . 'web/system/messages.php';
include_once $path_php . 'web/system/error_log_list.php';
include_once $path_php . 'web/html/api_const.php';
include_once $path_php . 'web/html/html_base.php';
include_once $path_php . 'web/html/button.php';
include_once $path_php . 'web/html/html_selector.php';
include_once $path_php . 'web/hist/hist_log_dsp.php';
// model HTML frontend classes
include_once $path_php . 'web/word/word.php';
include_once $path_php . 'web/word/word_list.php';
include_once $path_php . 'web/word/triple.php';
include_once $path_php . 'web/phrase/phrase.php';
include_once $path_php . 'web/phrase/phrase_list.php';
include_once $path_php . 'web/phrase/phrase_group.php';
include_once $path_php . 'web/phrase/term.php';
include_once $path_php . 'web/phrase/term_list.php';
include_once $path_php . 'web/verb/verb.php';
include_once $path_php . 'web/value/value.php';
include_once $path_php . 'web/value/value_list.php';
include_once $path_php . 'web/formula/formula.php';
include_once $path_php . 'web/formula/formula_list.php';
include_once $path_php . 'web/formula/formula_value.php';
include_once $path_php . 'web/formula/formula_value_list.php';
include_once $path_php . 'web/view/view.php';
include_once $path_php . 'web/view/view_list.php';
include_once $path_php . 'web/view/view_cmp.php';
include_once $path_php . 'web/view/view_cmp_link_dsp.php';
include_once $path_php . 'web/view/view_cmp_list.php';
include_once $path_php . 'web/ref/ref.php';
include_once $path_php . 'web/ref/source.php';

// deprecated HTML frontend classes
include_once $path_php . 'web/phrase/phrase_display.php';
include_once $path_php . 'web/display_list.php';
include_once $path_php . 'web/value_list_display.php';
include_once $path_php . 'web/formula_display.php';
include_once $path_php . 'web/view_display.php';
include_once $path_php . 'web/display_interface.php';
include_once $path_php . 'web/display_html.php';

// include all other libraries that are usually needed
include_once ROOT_PATH . 'db_link/zu_lib_sql_link.php';
include_once $path_php . 'service/db_code_link.php';
include_once $path_php . 'service/zu_lib_sql_code_link.php';
include_once $path_php . 'service/config.php';

// used at the moment, but to be replaced with R-Project call
include_once $path_php . 'service/math/calc_internal.php';

// settings
include_once $path_php . 'application.php';

// potentially to be loaded by composer
//include_once $path_php . 'utils/json-diff/JsonDiff.php';
//include_once $path_php . 'utils/json-diff/JsonPatch.php';
//include_once $path_php . 'utils/json-diff/JsonPointer.php';

// libraries that may be useful in the future
/*
include_once $root_path.'lib/test/zu_lib_auth.php';               if ($debug > 9) { echo 'user authentication loaded<br>'; }
include_once $root_path.'lib/test/config.php';             if ($debug > 9) { echo 'configuration loaded<br>'; }
*/

/*

Target is to have with version 0.1 a usable version for alpha testing. 
The roadmap for version 0.1 can be found here: https://zukunft.com/mantisbt/roadmap_page.php

The beta test is expected to start with version 0.7

*/

// global code settings
// TODO move the user interface setting to the user page, so that he can define which UI he wants to use
const UI_USE_BOOTSTRAP = 1; // IF FALSE a simple HTML frontend without javascript is used
const UI_MIN_RESPONSE_TIME = 2; // minimal time in seconds after that the user should see an update e.g. during long calculations every 2 sec the user should seen the screen updated
const UI_MAX_NAMES = 10;        // default number of names shown of a long list
const UI_TIMEOUT_START = 200;   // the max number of milliseconds after which the program should react to a user action
const UI_TIMEOUT_TARGET = 1000; // the target number of milliseconds between a screen update during a long process

/*
if UI_CAN_CHANGE_... setting is true renaming an object may switch to an object with the new name
if false the user gets an error message that the object with the new name exists already

e.g. if this setting is true
     user 1 creates     "Nestle" with id 1
     and user 2 creates "Nestlé" with id 2
     now the user 1 changes "Nestle" to "Nestlé"
     1. "Nestle" will be deleted, because it is not used any more
     2. "Nestlé" with id 2 will not be excluded anymore
     
*/
const UI_CAN_CHANGE_VALUE = TRUE;
const UI_CAN_CHANGE_TIME_SERIES_VALUE = TRUE;
const UI_CAN_CHANGE_VIEW_NAME = TRUE;
const UI_CAN_CHANGE_VIEW_COMPONENT_NAME = TRUE; // dito for view components
const UI_CAN_CHANGE_VIEW_COMPONENT_LINK = TRUE; // dito for view component links
const UI_CAN_CHANGE_WORD_NAME = TRUE; // dito for words
const UI_CAN_CHANGE_triple_NAME = TRUE; // dito for phrases
const UI_CAN_CHANGE_FORMULA_NAME = TRUE; // dito for formulas
const UI_CAN_CHANGE_VERB_NAME = TRUE; // dito for verbs
const UI_CAN_CHANGE_SOURCE_NAME = TRUE; // dito for sources

// data retrieval settings
const SQL_ROW_LIMIT = 20; // default number of rows per page/query if not defined
const SQL_ROW_MAX = 2000; // the max number of rows per query to avoid long response times

const MAX_LOOP = 10000; // maximal number of loops to avoid hanging while loops; used for example for the number of formula elements

// max number of recursive call to avoid endless looping in case of a program error
const MAX_RECURSIVE = 10;

// the standard word displayed to the user if she/he as not yet viewed any other word
const DEFAULT_WORD_ID = 1;
const DEFAULT_WORD_TYPE_ID = 1;
const DEFAULT_DEC_POINT = ".";
const DEFAULT_THOUSAND_SEP = "'";
const DEFAULT_PERCENT_DECIMALS = 2;

const ZUC_MAX_CALC_LAYERS = '10000';    // max number of calculation layers


// file links used
//const ZUH_IMG_ADD       = "/src/main/resources/images/button_add_small.jpg";
//const ZUH_IMG_EDIT      = "/src/main/resources/images/button_edit_small.jpg";
const ZUH_IMG_ADD = "/src/main/resources/images/button_add.svg";
const ZUH_IMG_EDIT = "/src/main/resources/images/button_edit.svg";
const ZUH_IMG_DEL = "/src/main/resources/images/button_del.svg";
const ZUH_IMG_UNDO = "/src/main/resources/images/button_undo.svg";

# list of JSON files that define the base configuration of zukunft.com that is supposed never to be changed
define("PATH_BASE_CONFIG_FILES", ROOT_PATH . 'src/main/resources/');
const PATH_BASE_CODE_LINK_FILES = PATH_BASE_CONFIG_FILES . 'db_code_links/';
define("BASE_CODE_LINK_FILES", serialize(array(
    'calc_and_cleanup_task_types',
    'change_actions',
    'change_tables',
    'change_fields',
    'formula_element_types',
    'formula_link_types',
    'formula_types',
    'language_forms',
    'languages',
    'protection_types',
    'ref_types',
    'share_types',
    'source_types',
    'sys_log_status',
    'sys_log_types',
    'user_official_types',
    'user_profiles',
    'user_types',
    'view_component_position_types',
    'view_component_types',
    'view_link_types',
    'view_types',
    'word_types'
)));
const BASE_CODE_LINK_FILE_TYPE = '.csv';
const SYSTEM_USER_CONFIG_FILE = PATH_BASE_CONFIG_FILES . 'users.json';
const SYSTEM_VERB_CONFIG_FILE = PATH_BASE_CONFIG_FILES . 'verbs.json';
const PATH_BASE_CONFIG_MESSAGE_FILES = PATH_BASE_CONFIG_FILES . 'messages/';
define("BASE_CONFIG_FILES", serialize(array(
    'system_views.json',
    'units.json',
    'scaling.json',
    'time_definition.json',
    'ip_blacklist.json',
    'country.json',
    'company.json'
)));

# list of all static import files for testing the system consistency
const PATH_TEST_FILES = ROOT_PATH . 'src/test/resources/';
const PATH_TEST_IMPORT_FILES = ROOT_PATH . 'src/test/resources/import/';
define("TEST_IMPORT_FILE_LIST", serialize(array(
    'wind_investment.json',
    'companies.json',
    'ABB_2013.json',
    'ABB_2017.json',
    'ABB_2019.json',
    'NESN_2019.json',
    'countries.json',
    'real_estate.json',
    'Ultimatum_game.json',
    'COVID-19.json',
    'personal_climate_gas_emissions_timon.json',
    'THOMY_test.json')));
define("TEST_IMPORT_FILE_LIST_ALL", serialize(array(
    'wind_investment.json',
    'companies.json',
    'ABB_2013.json',
    'ABB_2017.json',
    'ABB_2019.json',
    'NESN_2019.json',
    'countries.json',
    'real_estate.json',
    'travel_scoring.json',
    'travel_scoring_value_list.json',
    'Ultimatum_game.json',
    'COVID-19.json',
    'personal_climate_gas_emissions_timon.json',
    'THOMY_test.json')));

# list of import files for quick win testing
/*
define ("TEST_IMPORT_FILE_LIST_QUICK", serialize (array ('COVID-19.json',
                                                         'countries.json', 
                                                         'real_estate.json', 
                                                         'Ultimatum_game.json')));
define ("TEST_IMPORT_FILE_LIST_QUICK", serialize (array ('ABB_2013.json','work.json')));
*/
define("TEST_IMPORT_FILE_LIST_QUICK", serialize(array('car_costs.json')));

/**
 * for internal functions debugging
 * each complex function should call this at the beginning with the parameters and with -1 at the end with the result
 * called function should use $debug-1
 *
 * @param string $msg_text debug information additional to the class and function
 * @param int|null $debug_overwrite used to force the output
 * @return string the final output text
 */
function log_debug(string $msg_text = '', int $debug_overwrite = null): string
{
    global $debug;

    $debug_used = $debug;

    if ($debug_overwrite != null) {
        $debug_used = $debug_overwrite;
    }

    // add the standard prefix
    if ($msg_text != '') {
        $msg_text = ': ' . $msg_text;
    }
    if (array_key_exists('class', debug_backtrace()[1])) {
        $msg_text = debug_backtrace()[1]['class'] . '->' . debug_backtrace()[1]['function'] . $msg_text;
    } else {
        $msg_text = debug_backtrace()[1]['function'] . $msg_text;
    }

    if ($debug_used > 0) {
        echo $msg_text . '.<br>';
        //ob_flush();
        //flush();
    }

    return $msg_text;
}

/**
 * for system messages no debug calls to avoid loops
 * @param string $msg_text        is a short description that is used to group and limit the number of error messages
 * @param string $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
 * @param string $msg_type_id     is the criticality level e.g. debug, info, warning, error or fatal error
 * @param string $function_name   is the function name which has most likely caused the error
 * @param string $function_trace  is the complete system trace to get more details
 * @param int $user_id            is the user id who has probably seen the error message
 * return           the text that can be shown to the user in the navigation bar
 * TODO return the link to the log message so that the user can trace the bug fixing
 */
function log_msg(string $msg_text,
                 string $msg_description,
                 string $msg_log_level,
                 string $function_name,
                 string $function_trace,
                 int    $user_id): string
{

    global $sys_log_msg_lst;
    global $db_con;

    $result = '';

    if ($db_con == null) {
        echo 'FATAL ERROR! ' . $msg_text;
    } else {


        $lib = new library();

        // fill up fields with default values
        if ($msg_description == '') {
            $msg_description = $msg_text;
        }
        if ($function_name == '' or $function_name == null) {
            $function_name = (new Exception)->getTraceAsString();
            $function_name = $lib->str_right_of($function_name, '#1 /home/timon/git/zukunft.com/');
            $function_name = $lib->str_left_of($function_name, ': log_');
        }
        if ($function_trace == '') {
            $function_trace = (new Exception)->getTraceAsString();
        }
        if ($user_id <= 0) {
            $user_id = $_SESSION['usr_id'] ?? SYSTEM_USER_ID;
        }

        // assuming that the relevant part of the message is at the beginning of the message at least to avoid double entries
        $msg_type_text = $user_id . substr($msg_text, 0, 200);
        if (!in_array($msg_type_text, $sys_log_msg_lst)) {
            $db_con->usr_id = $user_id;
            $sys_log_id = 0;

            $sys_log_msg_lst[] = $msg_type_text;
            if ($msg_log_level > LOG_LEVEL) {
                $db_con->set_type(sql_db::TBL_SYS_LOG_FUNCTION);
                $function_id = $db_con->get_id($function_name);
                if ($function_id <= 0) {
                    $function_id = $db_con->add_id($function_name);
                }
                $msg_text = str_replace("'", "", $msg_text);
                $msg_description = str_replace("'", "", $msg_description);
                $function_trace = str_replace("'", "", $function_trace);
                $msg_text = $db_con->sf($msg_text);
                $msg_description = $db_con->sf($msg_description);
                $function_trace = $db_con->sf($function_trace);
                $fields = array();
                $values = array();
                $fields[] = "sys_log_type_id";
                $values[] = $msg_log_level;
                $fields[] = "sys_log_function_id";
                $values[] = $function_id;
                $fields[] = "sys_log_text";
                $values[] = $msg_text;
                $fields[] = "sys_log_description";
                $values[] = $msg_description;
                $fields[] = "sys_log_trace";
                $values[] = $function_trace;
                if ($user_id > 0) {
                    $fields[] = "user_id";
                    $values[] = $user_id;
                }
                $db_con->set_type(sql_db::TBL_SYS_LOG);
                $sys_log_id = $db_con->insert($fields, $values, false);
                //$sql_result = mysqli_query($sql) or die('zukunft.com system log failed by query '.$sql.': '.mysqli_error().'. If this happens again, please send this message to errors@zukunft.com.');
                //$sys_log_id = mysqli_insert_id();
            }
            if ($msg_log_level >= MSG_LEVEL) {
                echo "Zukunft.com has detected an critical internal error: <br><br>" . $msg_text . " by " . $function_name . ".<br><br>";
                if ($sys_log_id > 0) {
                    echo 'You can track the solving of the error with this link: <a href="/http/error_log.php?id=' . $sys_log_id . '">www.zukunft.com/http/error_log.php?id=' . $sys_log_id . '</a><br>';
                }
            } else {
                if ($msg_log_level >= DSP_LEVEL) {
                    $usr = new user();
                    $usr->id = $user_id;
                    $usr->load($db_con);
                    $dsp = new view_dsp_old($usr);
                    $result .= $dsp->dsp_navbar_simple();
                    $result .= $msg_text . " (by " . $function_name . ").<br><br>";
                }
            }
        }
    }
    return $result;
}

function get_user_id(?user $calling_usr = null): int
{
    global $usr;
    $user_id = 0;
    if ($calling_usr != null) {
        $user_id = $calling_usr->id;
    } else {
        if ($usr != null) {
            $user_id = $usr->id;
        }
    }
    return $user_id;
}

function log_info(string $msg_text,
                  string $function_name = '',
                  string $msg_description = '',
                  string $function_trace = '',
                  ?user  $calling_usr = null): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_level::INFO,
        $function_name, $function_trace,
        get_user_id($calling_usr));
}

function log_warning(string $msg_text,
                     string $function_name = '',
                     string $msg_description = '',
                     string $function_trace = '',
                     ?user  $calling_usr = null): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_level::WARNING,
        $function_name,
        $function_trace,
        get_user_id($calling_usr));
}

function log_err(string $msg_text,
                 string $function_name = '',
                 string $msg_description = '',
                 string $function_trace = '',
                 ?user  $calling_usr = null): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_level::ERROR,
        $function_name,
        $function_trace,
        get_user_id($calling_usr));
}

function log_fatal(string $msg_text,
                   string $function_name,
                   string $msg_description = '',
                   string $function_trace = '',
                   ?user  $calling_usr = null): string
{
    echo 'FATAL ERROR! ' . $msg_text;
    // TODO write first to the most secure system log because if the database connection is lost no writing to the database is possible
    return log_msg('FATAL ERROR! ' . $msg_text, $msg_description, sys_log_level::FATAL, $function_name, $function_trace, get_user_id($calling_usr));
}

/**
 * should be called from all code that can be accessed by an url
 * return null if the db connection fails or the db is not compatible
 *
 * @param string $code_name the place that is displayed to the user e.g. add word
 * @param string $style the display style used to show the place
 * @return sql_db the open database connection
 */
function prg_start(string $code_name, string $style = "", $echo_header = true): sql_db
{
    global $sys_time_start, $sys_script;

    // resume session (based on cookies)
    session_start();

    $sys_time_start = time();
    $sys_script = $code_name;

    log_debug($code_name . ': session_start');

    // html header
    if ($echo_header) {
        $html = new html_base();
        echo $html->header("", $style);
    }

    return prg_restart($code_name, $style);
}

/**
 * open the database connection and load the base cache
 * @param string $code_name the place that is displayed to the user e.g. add word
 * @return sql_db the open database connection
 */
function prg_restart(string $code_name): sql_db
{

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    $db_con->open();
    log_debug($code_name . ': db open');

    // check the system setup
    $result = db_check($db_con);
    if ($result != '') {
        echo '\n';
        echo $result;
        $db_con->close();
        $db_con = null;
    }

    // preload all types from the database
    $sys_typ_lst = new type_lists();
    $sys_typ_lst->load($db_con, null);

    return $db_con;
}

function prg_start_api($code_name): sql_db
{
    global $sys_time_start, $sys_script;

    log_debug($code_name . ' ..');

    $sys_time_start = time();
    $sys_script = $code_name;

    // resume session (based on cookies)
    session_start();

    // link to database
    $db_con = new sql_db;
    $db_con->open();
    log_debug($code_name . ' ... database link open');

    return $db_con;
}

/**
 * load the user specific data that is not supposed to be changed very rarely user
 * so if changed all data is reloaded once
 */
function load_usr_data(): void
{
    global $db_con;
    global $usr;
    global $verbs;
    global $system_views;

    $verbs = new verb_list($usr);
    $verbs->load($db_con);

    $system_views = new view_sys_list($usr);
    $system_views->load($db_con);

}

/**
 * write the execution time to the database if it is long
 */
function prg_end_write_time($db_con): void
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    $sys_time_end = time();
    if ($sys_time_end > $sys_time_limit) {
        $db_con->usr_id = SYSTEM_USER_ID;
        $db_con->set_type(sql_db::TBL_SYS_SCRIPT);
        $sys_script_id = $db_con->get_id($sys_script);
        if ($sys_script_id <= 0) {
            $sys_script_id = $db_con->add_id($sys_script);
        }
        $start_time_sql = date("Y-m-d H:i:s", $sys_time_start);
        //$db_con->insert();
        if (in_array('REQUEST_URI', $_SERVER)) {
            $calling_uri = $_SERVER['REQUEST_URI'];
        } else {
            $calling_uri = 'localhost';
        }
        $sql = "INSERT INTO sys_script_times (sys_script_start, sys_script_id, url) VALUES ('" . $start_time_sql . "'," . $sys_script_id . "," . $db_con->sf($calling_uri) . ");";
        $db_con->exe($sql);
    }

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);
}

function prg_end($db_con)
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    $html = new html_base();
    echo $html->footer();

    prg_end_write_time($db_con);

    // Free result test
    //mysqli_free_result($result);

    // Closing connection
    $db_con->close();

    log_debug(' ... database link closed');
}

// special page closing only for the about page
function prg_end_about($link)
{
    global $db_con;
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    $html = new html_base();
    echo $html->footer(true);

    prg_end_write_time($db_con);

    // Closing connection
    $db_con->close();

    log_debug(' ... database link closed');
}

// special page closing of api pages
// for the api e.g. the csv export no footer should be shown
function prg_end_api($link)
{
    global $db_con;
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    prg_end_write_time($db_con);

    // Closing connection
    $db_con->close();

    log_debug(' ... database link closed');
}

/*
 * display functions
 */

// to display a boolean var
function zu_dsp_bool($bool_var): string
{
    if ($bool_var) {
        $result = 'true';
    } else {
        $result = 'false';
    }
    return $result;
}

/*

version control functions

*/


/**
 * returns true if the version to check is older than this program version
 * used e.g. for import to allow importing of files of an older version without warning
 */
function prg_version_is_newer($prg_version_to_check, $this_version = PRG_VERSION): bool
{
    $is_newer = false;

    $this_prg_version_parts = explode(".", $this_version);
    $to_check = explode(".", $prg_version_to_check);
    $is_older = false;
    foreach ($this_prg_version_parts as $key => $this_part) {
        if (!$is_newer and !$is_older) {
            if ($this_part < $to_check[$key]) {
                $is_newer = true;
            } else {
                if ($this_part > $to_check[$key]) {
                    $is_older = true;
                }
            }
        }
    }

    return $is_newer;
}

/**
 * unit_test for prg_version_is_newer
 */
function prg_version_is_newer_test(testing $t)
{
    $result = zu_dsp_bool(prg_version_is_newer('0.0.1'));
    $target = 'false';
    $t->dsp('prg_version 0.0.1 is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer(PRG_VERSION));
    $target = 'false';
    $t->dsp('prg_version ' . PRG_VERSION . ' is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer(NEXT_VERSION));
    $target = 'true';
    $t->dsp('prg_version ' . NEXT_VERSION . ' is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer('0.1.0', '0.0.9'));
    $target = 'true';
    $t->dsp('prg_version 0.1.0 is newer than 0.0.9', $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer('0.2.3', '1.1.1'));
    $target = 'false';
    $t->dsp('prg_version 0.2.3 is newer than 1.1.1', $target, $result);
}

/*
string functions
*/

function zu_trim($text): string
{
    return trim(preg_replace('!\s+!', ' ', $text));
}


/*
string functions (to be dismissed)
*/


function zu_str_compute_diff($from, $to): array
{
    $diffValues = array();
    $diffMask = array();

    $dm = array();
    $n1 = count($from);
    $n2 = count($to);

    for ($j = -1; $j < $n2; $j++) $dm[-1][$j] = 0;
    for ($i = -1; $i < $n1; $i++) $dm[$i][-1] = 0;
    for ($i = 0; $i < $n1; $i++) {
        for ($j = 0; $j < $n2; $j++) {
            if ($from[$i] == $to[$j]) {
                $ad = $dm[$i - 1][$j - 1];
                $dm[$i][$j] = $ad + 1;
            } else {
                $a1 = $dm[$i - 1][$j];
                $a2 = $dm[$i][$j - 1];
                $dm[$i][$j] = max($a1, $a2);
            }
        }
    }

    $i = $n1 - 1;
    $j = $n2 - 1;
    while (($i > -1) || ($j > -1)) {
        if ($j > -1) {
            if ($dm[$i][$j - 1] == $dm[$i][$j]) {
                $diffValues[] = $to[$j];
                $diffMask[] = 1;
                $j--;
                continue;
            }
        }
        if ($i > -1) {
            if ($dm[$i - 1][$j] == $dm[$i][$j]) {
                $diffValues[] = $from[$i];
                $diffMask[] = -1;
                $i--;
                continue;
            }
        }
        {
            $diffValues[] = $from[$i];
            $diffMask[] = 0;
            $i--;
            $j--;
        }
    }

    $diffValues = array_reverse($diffValues);
    $diffMask = array_reverse($diffMask);

    return array('values' => $diffValues, 'mask' => $diffMask);
}

function zu_str_diff($original_text, $compare_text): string
{
    $diff = zu_str_compute_diff(str_split($original_text), str_split($compare_text));
    $diffval = $diff['values'];
    $diffmask = $diff['mask'];

    $n = count($diffval);
    $pmc = 0;
    $result = '';
    for ($i = 0; $i < $n; $i++) {
        $mc = $diffmask[$i];
        if ($mc != $pmc) {
            switch ($pmc) {
                case -1:
                    $result .= '</del>';
                    break;
                case 1:
                    $result .= '</ins>';
                    break;
            }
            switch ($mc) {
                case -1:
                    $result .= '<del>';
                    break;
                case 1:
                    $result .= '<ins>';
                    break;
            }
        }
        $result .= $diffval[$i];

        $pmc = $mc;
    }
    switch ($pmc) {
        case -1:
            $result .= '</del>';
            break;
        case 1:
            $result .= '</ins>';
            break;
    }

    return $result;
}

/*
function str_diff($text, $compare) {
  $result = '';
  $next = 0;
  for ($pos=0; $pos<strlen($text); $pos++) {
    if ($text[$i] == $compare[$i]) {
      $next = $next + 1;
    } else {  
      $result .= ' at '+ $pos + ': ';      
      $result .= $compare[$i] + ' instead of ' + $text[$i];      
    }
  }  
  return $result;
}
*/

/*

list functions (to be dismissed / replaced by objects)

*/

/**
 * remove all empty string entries from an array
 * @param array|null $in_array the array with empty strings or string with leading spaces
 * @return array the value comma seperated or "null" if the array is empty
 */
function array_trim(?array $in_array): array
{
    $result = array();
    if ($in_array != null) {
        foreach ($in_array as $item) {
            if (trim($item) <> '') {
                $result[] = trim($item);
            }
        }
    }
    return $result;
}


/**
 * create a human-readable string from an array
 * @param array|null $in_array the array that should be formatted
 * @return string the value comma seperated or "null" if the array is empty
 */
function dsp_array(?array $in_array): string
{
    $result = 'null';
    if ($in_array != null) {
        if (count($in_array) > 0) {
            $result = implode(',', $in_array);
        }
    }
    return $result;
}

function dsp_array_keys(?array $in_array): string
{
    $result = 'null';
    if ($in_array != null) {
        if (count($in_array) > 0) {
            $result = implode(',', array_keys($in_array));
        }
    }
    return $result;
}

function dsp_count(?array $in_array): int
{
    $result = 0;
    if ($in_array != null) {
        $result = count($in_array);
    }
    return $result;
}

/**
 * prepare an array for an SQL statement
 * @param array $in_array the array that should be formatted
 * @return string the values comma seperated or "" if the array is empty
 */
function sql_array(array $in_array): string
{
    $result = '';
    if ($in_array != null) {
        if (count($in_array) > 0) {
            $result = implode(',', $in_array);
        }
    }
    return $result;
}

function camelize(string $input, string $separator = '_'): string
{
    return str_replace($separator, '', lcfirst(ucwords($input, $separator)));
}

function camelize_ex_1(string $input, string $separator = '_'): string
{
    return str_replace($separator, '', ucwords($input, $separator));
}

// get all entries of the list that are not in the second list
function zu_lst_not_in($in_lst, $exclude_lst): array
{
    log_debug('zu_lst_not_in(' . dsp_array(array_keys($in_lst)) . ',ex' . dsp_array($exclude_lst) . ')');
    $result = array();
    foreach (array_keys($in_lst) as $lst_entry) {
        if (!in_array($lst_entry, $exclude_lst)) {
            $result[$lst_entry] = $in_lst[$lst_entry];
        }
    }
    return $result;
}

/**
 * similar to zu_lst_not_in, but looking at the array value not the key
 */
function zu_lst_not_in_no_key(array $in_lst, array $exclude_lst): array
{
    log_debug('zu_lst_not_in_no_key(' . dsp_array($in_lst) . 'ex' . dsp_array($exclude_lst) . ')');
    $result = array();
    foreach ($in_lst as $lst_entry) {
        if (!in_array($lst_entry, $exclude_lst)) {
            $result[] = $lst_entry;
        }
    }
    log_debug(dsp_array($result));
    return $result;
}

// get all entries of the list that are not in the second list
function zu_lst_in_ids($in_lst, $only_if_ids): array
{
    $result = array();
    foreach (array_keys($in_lst) as $lst_entry) {
        if (in_array($lst_entry, $only_if_ids)) {
            $result[$lst_entry] = $in_lst[$lst_entry];
        }
    }
    return $result;
}

// create an url parameter text out of an id array
function zu_ids_to_url($ids, $par_name): string
{
    $result = "";
    foreach (array_keys($ids) as $pos) {
        $nbr = $pos + 1;
        if ($ids[$pos] <> "" or $ids[$pos] === 0) {
            $result .= "&" . $par_name . $nbr . "=" . $ids[$pos];
        }
    }
    return $result;
}

// flattens a complex array; if the list entry is an array the first field is shown
function zu_lst_to_array($complex_lst): array
{
    //zu_debug("zu_lst_to_array");
    $result = array();
    foreach ($complex_lst as $lst_entry) {
        if (is_array($lst_entry)) {
            $result[] = $lst_entry[0];
            //zu_debug("zu_lst_to_array -> ".$lst_entry[0]." (first)");
        } else {
            $result[] = $lst_entry;
            //zu_debug("zu_lst_to_array -> ".$lst_entry);
        }
    }
    return $result;
}

// flattens a complex array; if the list entry is an array the first field is shown
function zu_ids_not_empty($old_ids): array
{
    // fix wrd_ids if needed
    $result = array();
    foreach ($old_ids as $old_id) {
        if ($old_id > 0) {
            $result[] = $old_id;
        }
    }
    return $result;
}

// flattens a complex array; if the list entry is an array the first field is shown
function zu_ids_not_zero($old_ids): array
{
    // fix wrd_ids if needed
    $result = array();
    foreach ($old_ids as $old_id) {
        if ($old_id <> 0) {
            $result[] = $old_id;
        }
    }
    return $result;
}

// gets on id list with all word ids from the value list, that already contain the word ids for each value
// no user id is needed because this is done already in the previous selection
function zu_val_lst_get_wrd_ids($val_lst): array
{
    //zu_debug("zu_val_lst_get_wrd_ids");
    $result = array();
    foreach ($val_lst as $val_entry) {
        if (is_array($val_entry->wrd_lst)) {
            $wrd_ids = $val_entry->wrd_lst->ids();
            if (is_array($wrd_ids)) {
                foreach ($wrd_ids as $wrd_id) {
                    $result[] = $wrd_id;
                }
            }
        }
    }

    return $result;
}

// maybe use array_filter ???
function zu_lst_common($id_lst1, $id_lst2): array
{
    //zu_debug("zu_lst_common (".implode(",",$id_lst1)."x".implode(",",$id_lst1).")");
    //zu_debug("zu_lst_to_array");
    $result = array();
    if (is_array($id_lst1) and is_array($id_lst2)) {
        foreach ($id_lst1 as $id1) {
            if (in_array($id1, $id_lst2)) {
                $result[] = $id1;
            }
        }
    }

    //zu_debug("zu_lst_common -> (".implode(",",$result).")");
    return $result;
}

// collects from an array in an array a list of common ids
// if this is used for a val_lst_wrd and the sub_array_pos is 1 the common list of word ids is returned
function zu_lst_get_common_ids($val_lst, $sub_array_pos)
{
    log_debug("zu_lst_get_common_ids (" . zu_lst_dsp($val_lst) . ")");
    $result = 0;
    //print_r ($val_lst);
    foreach ($val_lst as $val_entry) {
        if (is_array($val_entry)) {
            $wrd_ids = $val_entry[$sub_array_pos];
            if ($result == 0) {
                $result = $wrd_ids;
            } else {
                $result = zu_lst_common($result, $wrd_ids);
            }
        }
    }

    log_debug(dsp_array($result));
    return $result;
}

// collects from an array in an array a list of all ids similar to zu_lst_get_common_ids
// if this is used for a val_lst_wrd and the sub_array_pos is 1 the common list of word ids is returned
function zu_lst_all_ids($val_lst, $sub_array_pos)
{
    log_debug(zu_lst_dsp($val_lst) . ",pos" . $sub_array_pos);
    $result = array();
    foreach ($val_lst as $val_entry) {
        if (is_array($val_entry)) {
            $wrd_ids = $val_entry[$sub_array_pos];
            if (empty($result)) {
                $result = $wrd_ids;
            } else {
                foreach ($wrd_ids as $wrd_id) {
                    if (!in_array($wrd_id, $result)) {
                        $result[] = $wrd_id;
                    }
                }
            }
        }
    }

    log_debug(dsp_array($result));
    return $result;
}

// filter an array with a sub array by the id entries of the subarray
// if the subarray does not have any value of the filter id_lst it is not included in the result
// e.g. for a value list with all related words get only those values that are related to on of the time words given in the id_lst
function zu_lst_id_filter($val_lst, $id_lst, $sub_array_pos): array
{
    log_debug("zu_lst_id_filter (" . zu_lst_dsp($val_lst) . ",t" . zu_lst_dsp($id_lst) . ",pos" . $sub_array_pos . ")");
    $result = array();
    foreach (array_keys($val_lst) as $val_key) {
        $val_entry = $val_lst[$val_key];
        if (is_array($val_entry)) {
            $wrd_ids = $val_entry[$sub_array_pos];
            $found = false;
            foreach ($wrd_ids as $wrd_id) {
                if (!$found) {
                    log_debug("test (" . $wrd_id . " in " . zu_lst_dsp($id_lst) . ")");
                    if (array_key_exists($wrd_id, $id_lst)) {
                        $found = true;
                        log_debug("found (" . $wrd_id . " in " . zu_lst_dsp($id_lst) . ")");
                    }
                }
            }
            if ($found) {
                $result[$val_key] = $val_entry;
            }
        }
    }

    log_debug(zu_lst_dsp($result));
    return $result;
}

// flattens a complex array; if the list entry is an array the first field and the array key is returned
function zu_lst_to_flat_lst($complex_lst): array
{
    //zu_debug("zu_lst_to_array");
    $result = array();
    foreach (array_keys($complex_lst) as $lst_key) {
        $lst_entry = $complex_lst[$lst_key];
        if (is_array($lst_entry)) {
            $result[$lst_key] = $lst_entry[0];
            //zu_debug("".$lst_entry[0]." (first)");
        } else {
            $result[$lst_key] = $lst_entry;
            //zu_debug("".$lst_entry);
        }
    }
    return $result;
}

// display a list; if the list is an array the first field is shown
function zu_lst_dsp($lst_to_dsp)
{
    //zu_debug("zu_lst_dsp");
    if (is_array($lst_to_dsp)) {
        $result_array = zu_lst_to_array($lst_to_dsp);
        //zu_debug("zu_lst_dsp -> converted");
        $result = dsp_array($result_array);
    } else {
        $result = $lst_to_dsp;
    }
    return $result;
}

function zu_lst_merge_with_key($lst_1, $lst_2): array
{
    $result = array();
    foreach (array_keys($lst_1) as $lst_entry) {
        $result[$lst_entry] = $lst_1[$lst_entry];
    }
    foreach (array_keys($lst_2) as $lst_entry) {
        $result[$lst_entry] = $lst_2[$lst_entry];
    }
    return $result;
}

// best guess formatting of an value for debug lines
function dsp_var($var_to_format): string
{
    $result = '';
    if ($var_to_format != null) {
        if (is_array($var_to_format)) {
            $result = dsp_array($var_to_format);
        } else {
            $result = $var_to_format;
        }
    }
    return $result;
}

// port php 8 function to 7.4
/*
function str_starts_with(string $long_string, string $prefix): bool
{
    $result = false;
    if (substr($long_string, 0, strlen($prefix)) == $prefix) {
        $result = true;
    }
    return $result;
}
*/


// port php 8 function to 7.4
/*
function str_ends_with(string $long_string, string $postfix): bool
{
    $result = false;
    if (substr($long_string, strlen($postfix) * -1) == $postfix) {
        $result = true;
    }
    return $result;
}
*/


// port php 8 function to 7.4
/*
function str_contains(string $haystack, string $needle): bool
{
    $result = true;
    $pos = strpos($haystack, $needle);
    if ($pos == false) {
        $result = false;
    }
    return $result;
}
*/


/**
 * recursive count of the number of elements in an array but limited to a given level
 * @param array $json_array the array that should be analysed
 * @param int $levels the number of levels that should be taken into account
 * @param int $level used for the recursive
 * @return int the number of elements
 */
function count_recursive(array $json_array, int $levels, int $level = 1): int
{
    $result = 0;
    if ($json_array != null) {
        if ($level <= $levels) {
            foreach ($json_array as $sub_array) {
                $result++;
                if (is_array($sub_array)) {
                    $result = $result + count_recursive($sub_array, $levels, $level++);
                }
            }
        }
    }
    return $result;
}

function trim_all(string $to_trim): string
{
    $result = trim($to_trim);
    while (str_contains($result, '  ')) {
        $result = str_replace("  ", "", $result);
    }
    return $result;
}

/**
 * convert a database datetime string to a php DateTime object
 *
 * @param string $datetime_text the datetime as received from the database
 * @return DateTime the converted DateTime value or now()
 */
function get_datetime(string $datetime_text, string $obj_name = '', string $process = ''): DateTime
{
    $result = new DateTime();
    try {
        $result = new DateTime($datetime_text);
    } catch (Exception $e) {
        $msg = 'Failed to convert the database DateTime value ' . $datetime_text;
        if ($obj_name != '') {
            $msg .= ' for ' . $obj_name;
        }
        if ($process != '') {
            $msg .= ' during ' . $process;
        }
        $msg .= ', because ' . $e;
        $msg .= ' reset to now';
        log_err($msg);
    }
    return $result;
}

