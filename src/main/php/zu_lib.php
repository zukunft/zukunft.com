<?php

/*use html\phrase\phrase_group as phrase_group_dsp;
use html\phrase\phrase_group as phrase_group_dsp;


    zu_lib.php - the main ZUkunft.com LIBrary
    ----------

    for coding new features the target process is before committing:
    1. create a unit test for the new feature
    2. code the feature and fix the unit tests and code smells
    3. create and fix the database unit and integration test for the new feature
    4. commit

    but first this needs to be fixed:
    TODO add system and user config parameter that are e.g. 100 views a view is automatically freezed for the user
    TODO add a trigger to the message header to force the frontend update of types, verbs und user configuration if needed
    TODO use words and values also for the system and user config
    TODO create a config get function for the frontend
    TODO cleanup the object vars and use objects instead repeating ids
    TODO remove the old frontend objects based on the api object
    TODO remove the dsp_obj() functions (without api objects where it can be used for unit tests) and base the frontend objects only on the json api message
    TODO add at least one HTML test for each class
    TODO remove all dsp_obj functions from the model classes
    TODO make sure that im-and export and api check all objects fields
    TODO move all test const to the api class or a test class
    TODO check the all used object are loaded with include once
    TODO base the html frontend objects (_dsp) on the api JSON using the set_from_json function
    TODO check that in the API            messages the database id is used for all preloaded types e.g. phrase type
    TODO check that in the im- and export messages the     code id is used for all preloaded types e.g. phrase type
    TODO refactor the web classes (dismiss all _old classes)
    TODO always use a function of the test_new_obj class to create a object for testing
    TODO create unit tests for all display object functions
    TODO remove the set and get functions from the api objects and make them as simple as possible
    TODO move the include_once calls from zu_lib to the classes
    TODO check that the child classes do not repeat the parent functions
    TODO do not base the html frontend objects on the api object because the api object should be as small as possible
    TODO cast api object in model object and dsp object in api object and add the dsp_obj() function to model object
    TODO define all database field names as const
    TODO for reference field names use the destination object
            e.g. for the field name phrase_group_id use phrase_group::FLD_ID
    TODO move the time field of phrase groups to the group
    TODO check that all times include the time zone
    TODO load_obj_vars: replace the load_obj_vars with more specific load_by_ functions
    TODO unit test: create a unit test for all possible class functions next to review: formula expression
    TODO api load: expose all load functions to the api (with security check!)
    TODO use always prepared queries based on the value_phrase_link_list_by_phrase_id.sql sample
    TODO fix error in upgrade process for MySQL
    TODO fix syntax suggestions in existing code
    TODO add the view result at least as simple text to the JSON export
    TODO split Mathematical constant in Math and constant
    TODO per km in 'one' 'per' 'km'
    TODO split acronym in 'one to one' and 'one to many'
    TODO replace db field 'triple_name' with a virtual field based on name_generated and name_given
    TODO add api unit test (assert_api_to_dsp) to all objects
    TODO align the namespace with PSR-0 as much as possible

    after that this should be done while keeping step 1. to 4. for each commit:
    TODO use the json api message header for all api messages
    TODO check if reading triples should use a view to generate the triple name and the generated name
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
    TODO check the add foreign database keys are defined
    TODO check that all fields used in the frontend API are referenced from a controller::FLD const
    TODO check that all fields used for the export are referenced from a export::FLD const
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
    TODO create unit tests for all relevant functions
    TODO allow the triple name to be the same as the word name e.g. to define tha Pi and π are math const e.g implement the phrase type hidden_triple
    TODO order the phrase types by behaviors
    TODO create a least only test case for each phrase type
    TODO create a behavior table to assign several behaviors to one type
    TODO complete rename word_type to phrase_type
    TODO cleanup object by removing duplicates
    TODO call include only if needed
    TODO allow to link views, components and formulas to define a successor
    TODO for phrases define the successor via special verbs
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
    TODO create an automatic database split based on a phrase and auto sync overlapping values
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
    TODO create a sanity API for monitor tools like checkMK or platforms like openshift
    TODO create an "always on" thread for the backend
    TODO create a LaTeX extension for charts and values, so that studies can be recreated based on the LaTeX document
    TODO for fail over in the underlying technologies, create a another backend in python and java  and allow the user to select or auto select the backend technology
    TODO for fail over in the underlying database technologies, auto sync the casandra, hadoop, Postgres and mariaDB databases
    TODO auto create two triple for an OR condition in a value selection; this implies that to select a list of values only AND needs to be used and brackets are also not needed
    TODO add a phrase group to sources and allow to import it with "keys:"
    TODO allow to assign more phrases to a source for better suggestion of sources
    TODO add a request time to each frontend request to check the automatically the response times
    TODO check that all external links from external libraries are removed, so that the cookie disclaimer can be avoided
    TODO reduce the size of the api messages to improve speed
    TODO add a slider for admin to set the balance between speed and memory usage in the backend (with a default balanced setting and a auto optimize function)
    TODO add a slider for the user to set the balance between speed and memory usage in the frontend and display the effect in a chart with speed increase vs memory usage
    TODO add example how a tax at least in the height of the micro market share at the customer would prevent monopoly
    TODO add example why democracy sometimes do wrong decisions e.g. because the feedback loop is too long or to rare
    TODO explain why the target build up user needs to be intelligent, but without targeting power
    TODO add example why nobody should own more than the community is spending to save one persons life
    TODO add example how the car insurance uses the value of one person life to calculate the premium and the health insurance for the starting age for gastro check
    TODO make sure that "sudo apt-get install php-dom" is part of the install process
    TODO before deleting a word make sure that there are not depending triples
    TODO Include in the message the user@pot or usergroup@pot that can read, write and export the data and who is owner
    TODO Export of restricted data is always pgp secured and the header includes the access rights,
    TODO rename phrase_group to group
    TODO rename formula_element to element
    TODO create a undo und redo function for a change_log entry
    TODO for behavior that should apply to several types create a property/behavior table with an n:m reration to phrase types e.g. "show preferred as column" for time phrases
    TODO create a user view for contradicting behaviour e.g. if time should be shown in column, but days in rows
    TODO add a text table for string and prosa that never should be used for selection
    TODO add a date table to save dates in an efficient way
    TODO allow to assign users to an admin and offer each admin to use different settings for "his" users so that different behavior due to setting changes can be tested to the same pod


    TODO add data optimizers for read time, write time and space usage
         e.g. select the queries most often used with the longest exe time by data transferred
              if at least 1000 values share the same owner, share and protection parameters and context
              create a value group and a value group table for these values
              estimate the speed and size saving potential
              create a separate pure key-value data table
              copy the data to the optimized structure
              switch over the read and write queries
              check if the real query time match the estimates
              and adjust the parameters if needed
              if the time or space saving is real remove the old and unused data (fixed reorg)
              set the max number of value group tables per pod to e.g. 900
              check the context overlapping between two pods
              and suggest data transfer if this will reduce traffic


    TODO create a table startup page with a
         Table with two col and two rows and four last used pages below. If now last used pages show the demo pages.
         Typing words in the top left cell select a word with the default page
         Typing in the top right cell adds one more column and two rows and typing offer to select a word and also adds related row names based on child words
         Typing in the lower left cell also adds one more row and two cols and typing shows related parent words as column headers
         Typing in the lower right cell starts the formula selection and an = is added as first char.
         Typing = in any cell starts the formula selection
         Typing an operator sign after a space starts the formula creation and a formula name is suggested

    TODO add a multi unique key auto merge test case
        add Euro and Swissfranc and an Euro to Swissfrance rate of 1.1
        add EUR and CHF and a EUR/CHF rate of 1.0
        add a Name and ISO code unique Index to currencies
         -> use the later rate


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

    the target model object structure is:

    db_object - all database objects that have a unique id
        verb - named object not part of the user sandbox because each verb / predicate is expected to have it own behavior; user can only request new verbs
        phrase_group - a sorted list of phrases
        phrase_group_link - db index to find a phrase group by the phrase (not the db normal form to speed up)
            phrase_group_word_link - phrase_group_link for a word
            phrase_group_triple_link - phrase_group_link for a triple
        formula_element - the parameters / parts of a formula expression for fast finding of dependencies (not the db normal form to speed up)
        change_log - to log a change done by a user
            change_log_named - log of user changes in named objects e.g. word, triple, ...
            change_log_link - log of the link changes by a user
        system_log - log entries by the system to improve the setup and code
        batch_job - to handle processes that takes longer than the user is expected to wait
        ip_range - to filter requests from the internet
        sandbox - a user sandbox object
            sandbox_named - user sandbox objects that have a given name
                sandbox_typed - named sandbox object that have a type and a predefined behavior
                    word - the base object to find values
                    formulas - a calculation rule
                    view - to show an object to the user
                    component - an formatting element for the user view e.g. to show a word or number
                    source - a non automatic source for a value
            sandbox_Link - user sandbox objects that link two objects
                sandbox_link_named - user sandbox objects that link two objects
                    sandbox_link_typed - objects that have additional a type and a predefined behavior
                        triple - link two words with a predicate / verb
                        view_term_link - link a view to a term
                    sandbox_link_with_type - TODO combine with sandbox_link_typed?
                        formula_link - link a formula to a phrase
                        component_link - to assign a component to a view
                        ref - to link a value to an external source
            sandbox_value - to save a user specific numbers
                value - a single number added by the user
                result - one calculated numeric result
                value_time_series - a list of very similar numbers added by the user e.g. that only have a different timestamp  (TODO rename to series)
    base_list - a list with pages
        change_log_list - to forward changes to the UI
        system_log_list - to forward the system log entries to the UI
        batch_job_list - to forward the batch jobs to the UI
        ip_range_list - list of the ip ranges
        sandbox_list - a user specific paged list
            word_list - a list of words (TODO move to sandbox_list_named?)
            triple_list - a list of triples (TODO move to sandbox_list_named?)
            value_list - a list of values
            value_phrase_link_list - list of value_phrase_link
            formula_list - a list of formulas
            formula_element_list - a list of formula elements
            formula_element_group_list - a list of formula element groups
            formula_link_list - a list of formula links
            result_list - a list of results
            figure_list - a list of figures
            view_list - a list of views
            component_list - a list of components
            component_link_list - a list of component_links
            sandbox_list_named - a paged list of named objects
                phrase_list - a list of phrases
                term_list - a list of terms
    type_object - to assign program code to a single object
        word_type - to assign predefined behaviour to a single word (and its children) (TODO combine with phrase type?)
        phrase_type - to assign predefined behaviour to a single word (and its children)
        formula_type - to assign predefined behaviour to formulas
        ref_type - to assign predefined behaviour to reference
        source_type - to assign predefined behaviour to source
        language - to define how the UI should look like
        language_form - to differentiate the word and triple name forms e.g. plural
    type_list - list of type_objects that is only load once a startup in the frontend
        view_sys_list - list of all view used by the system itself
        word_type_list - list of all word types
        verb_list - list of all verbs
        formula_type_list - a list of all formula types
        formula_element_type_list - list of all formula element types
        formula_link_type_list - list of all formula link types
        view_type_list - list of all view types
        view_cmp_type_list - list of all component types
        view_cmp_link_type_list - list of all link types how to assign a component to a view
        view_cmp_pos_type_list - list of all view_cmp_pos_type
        ref_list - list of all refs (TODO use a sandbox_link list?)
        ref_type_list - list of all ref types
        source_type_list - list of all source types
        language_list - list of all UI languages
        language_form_list - list of all language forms
        change_log_action - list of all change types
        change_log_table - list of all db tables that can be changed by the user (including table of past versions)
        change_log_field - list of all fields in table that a user can change (including fields of past versions)
        job_type_list - list of all batch job types
    combine_object - a object that combines two objects
        combine_named - a combine object with a unique name
            phrase - a word or triple
            term - a word, triple, verb or formula
        figure - a value or result

    helpers
        phr_ids - just to avoid mixing a phrase with a triple id
        trm_ids - just to avoid mixing a term with a triple, verb or formula id
        fig_ids - just to avoid mixing a result with a figure id
        expression - to convert the user format of a formula to the internal reference format and backward

    model objects to be reviewed
        word_change_list
        phrase_group_list - a list of phrase group that is supposed to be a sandbox_list
        value_phrase_link - db index to find a valur by the phrase (not the db normal form to speed up)
        formula_element_group - to combine several formula elements that are depending on each other
        view_cmp_type - TODO rename to component_type and move to type_object?
        view_cmp_pos_type - TODO use a simple enum?
        ref_link_wikidata - the link to wikidata



    rules for this projects (target, but not yet done)

    - be open
    - always sort by priority
    - one place (e.g. git / issue tracker / wiki)
    - not more than 6 information block per page
    - automatic log (who has changed what and when)


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

use cfg\db_check;
use cfg\type_lists;
use cfg\verb_list;
use cfg\view_sys_list;
use html\html_base;
use html\view\view_dsp_old;
use cfg\change_log;
use cfg\library;
use cfg\sql_db;
use cfg\sys_log_level;
use cfg\user;
use test\test_cleanup;

// the fixed system user
const SYSTEM_USER_ID = 1; //
const SYSTEM_USER_TEST_ID = 2; //

// parameters for internal testing and debugging
const LIST_MIN_NAMES = 4; // number of object names that should al least be shown
const DEBUG_SHOW_USER = 10; // starting from this debug level the user should be shown in the debug text

const DB_LINK_PATH = ROOT_PATH . 'db_link' . DIRECTORY_SEPARATOR;
const DB_PATH = PHP_PATH . 'cfg/db' . DIRECTORY_SEPARATOR;
const UTIL_PATH = PHP_PATH . 'utils' . DIRECTORY_SEPARATOR;
const SERVICE_PATH = PHP_PATH . 'service' . DIRECTORY_SEPARATOR;
const SERVICE_IMPORT_PATH = SERVICE_PATH . 'import' . DIRECTORY_SEPARATOR;
const SERVICE_EXPORT_PATH = SERVICE_PATH . 'export' . DIRECTORY_SEPARATOR;
const SERVICE_MATH_PATH = SERVICE_PATH . 'math' . DIRECTORY_SEPARATOR;
const MODEL_PATH = PHP_PATH . 'cfg' . DIRECTORY_SEPARATOR; // path of the main model objects for db saving, api feed and processing
const MODEL_HELPER_PATH = MODEL_PATH . 'helper' . DIRECTORY_SEPARATOR;
const MODEL_SYSTEM_PATH = MODEL_PATH . 'system' . DIRECTORY_SEPARATOR;
const MODEL_LOG_PATH = MODEL_PATH . 'log' . DIRECTORY_SEPARATOR;
const MODEL_LANGUAGE_PATH = MODEL_PATH . 'language' . DIRECTORY_SEPARATOR;
const MODEL_USER_PATH = MODEL_PATH . 'user' . DIRECTORY_SEPARATOR;
const MODEL_SANDBOX_PATH = MODEL_PATH . 'sandbox' . DIRECTORY_SEPARATOR;
const MODEL_WORD_PATH = MODEL_PATH . 'word' . DIRECTORY_SEPARATOR;
const MODEL_PHRASE_PATH = MODEL_PATH . 'phrase' . DIRECTORY_SEPARATOR;
const MODEL_VERB_PATH = MODEL_PATH . 'verb' . DIRECTORY_SEPARATOR;
const MODEL_VALUE_PATH = MODEL_PATH . 'value' . DIRECTORY_SEPARATOR;
const MODEL_REF_PATH = MODEL_PATH . 'ref' . DIRECTORY_SEPARATOR;
const MODEL_FORMULA_PATH = MODEL_PATH . 'formula' . DIRECTORY_SEPARATOR;
const MODEL_RESULT_PATH = MODEL_PATH . 'result' . DIRECTORY_SEPARATOR;
const MODEL_VIEW_PATH = MODEL_PATH . 'view' . DIRECTORY_SEPARATOR;
const API_PATH = PHP_PATH . 'api' . DIRECTORY_SEPARATOR; // path of the api objects for the message creation to the frontend
const API_SANDBOX_PATH = API_PATH . 'sandbox' . DIRECTORY_SEPARATOR;
const API_SYSTEM_PATH = API_PATH . 'system' . DIRECTORY_SEPARATOR;
const API_USER_PATH = API_PATH . 'user' . DIRECTORY_SEPARATOR;
const API_LOG_PATH = API_PATH . 'log' . DIRECTORY_SEPARATOR;
const API_LANGUAGE_PATH = API_PATH . 'language' . DIRECTORY_SEPARATOR;
const API_WORD_PATH = API_PATH . 'word' . DIRECTORY_SEPARATOR;
const API_PHRASE_PATH = API_PATH . 'phrase' . DIRECTORY_SEPARATOR;
const API_VERB_PATH = API_PATH . 'verb' . DIRECTORY_SEPARATOR;
const API_VALUE_PATH = API_PATH . 'value' . DIRECTORY_SEPARATOR;
const API_FORMULA_PATH = API_PATH . 'formula' . DIRECTORY_SEPARATOR;
const API_RESULT_PATH = API_PATH . 'result' . DIRECTORY_SEPARATOR;
const API_VIEW_PATH = API_PATH . 'view' . DIRECTORY_SEPARATOR;
const API_REF_PATH = API_PATH . 'ref' . DIRECTORY_SEPARATOR;
const WEB_PATH = PHP_PATH . 'web' . DIRECTORY_SEPARATOR; // path of the pure html frontend objects
const WEB_LOG_PATH = WEB_PATH . 'log' . DIRECTORY_SEPARATOR;
const WEB_USER_PATH = WEB_PATH . 'user' . DIRECTORY_SEPARATOR;
const WEB_SYSTEM_PATH = WEB_PATH . 'system' . DIRECTORY_SEPARATOR;
const WEB_TYPES_PATH = WEB_PATH . 'types' . DIRECTORY_SEPARATOR;
const WEB_SANDBOX_PATH = WEB_PATH . 'sandbox' . DIRECTORY_SEPARATOR;
const WEB_HTML_PATH = WEB_PATH . 'html' . DIRECTORY_SEPARATOR;
const WEB_HIST_PATH = WEB_PATH . 'hist' . DIRECTORY_SEPARATOR;
const WEB_WORD_PATH = WEB_PATH . 'word' . DIRECTORY_SEPARATOR;
const WEB_PHRASE_PATH = WEB_PATH . 'phrase' . DIRECTORY_SEPARATOR;
const WEB_VERB_PATH = WEB_PATH . 'verb' . DIRECTORY_SEPARATOR;
const WEB_VALUE_PATH = WEB_PATH . 'value' . DIRECTORY_SEPARATOR;
const WEB_FORMULA_PATH = WEB_PATH . 'formula' . DIRECTORY_SEPARATOR;
const WEB_RESULT_PATH = WEB_PATH . 'result' . DIRECTORY_SEPARATOR;
const WEB_FIGURE_PATH = WEB_PATH . 'figure' . DIRECTORY_SEPARATOR;
const WEB_VIEW_PATH = WEB_PATH . 'view' . DIRECTORY_SEPARATOR;
const WEB_REF_PATH = WEB_PATH . 'ref' . DIRECTORY_SEPARATOR;

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
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'db_check.php';

include_once WEB_HTML_PATH . 'html_base.php';

// include all other libraries that are usually needed
include_once DB_LINK_PATH . 'zu_lib_sql_link.php';
include_once SERVICE_PATH . 'db_code_link.php';
include_once SERVICE_PATH . 'zu_lib_sql_code_link.php';
include_once SERVICE_PATH . 'config.php';

// preloaded lists
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_lists.php';
include_once MODEL_SYSTEM_PATH . 'BasicEnum.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_level.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status.php';
include_once MODEL_USER_PATH . 'user_list.php';
include_once MODEL_USER_PATH . 'user_profile_list.php';
include_once MODEL_WORD_PATH . 'word_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_link_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_element_type_list.php';
include_once MODEL_VIEW_PATH . 'view_type_list.php';
include_once MODEL_VIEW_PATH . 'view_cmp_type_list.php';
include_once MODEL_VIEW_PATH . 'view_cmp_pos_type_list.php';
include_once MODEL_REF_PATH . 'ref_type_list.php';
include_once MODEL_REF_PATH . 'source_type_list.php';
include_once MODEL_SANDBOX_PATH . 'share_type_list.php';
include_once MODEL_SANDBOX_PATH . 'protection_type_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_form_list.php';
include_once MODEL_SYSTEM_PATH . 'batch_job_type_list.php';
include_once MODEL_LOG_PATH . 'change_log_action.php';
include_once MODEL_LOG_PATH . 'change_log_table.php';
include_once MODEL_LOG_PATH . 'change_log_field.php';
include_once MODEL_VERB_PATH . 'verb_list.php';
include_once MODEL_VIEW_PATH . 'view_sys_list.php';


// used at the moment, but to be replaced with R-Project call
include_once SERVICE_MATH_PATH . 'calc_internal.php';

// settings
include_once PHP_PATH . 'application.php';

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

// the possible SQL DB names (must be the same as in sql_db)
const POSTGRES = "Postgres";
const MYSQL = "MySQL";
const SQL_DB_TYPE = POSTGRES;
// const SQL_DB_TYPE = sql_db::MYSQL;

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
    'component_position_types',
    'component_types',
    'view_link_types',
    'view_types',
    'word_types'
)));
const BASE_CODE_LINK_FILE_TYPE = '.csv';
const SYSTEM_USER_CONFIG_FILE = PATH_BASE_CONFIG_FILES . 'users.json';
const SYSTEM_VERB_CONFIG_FILE = PATH_BASE_CONFIG_FILES . 'verbs.json';
const SYSTEM_CONFIG_FILE = PATH_BASE_CONFIG_FILES . 'config.json';
const PATH_BASE_CONFIG_MESSAGE_FILES = PATH_BASE_CONFIG_FILES . 'messages/';
define("BASE_CONFIG_FILES", serialize(array(
    'system_views.json',
    'sources.json',
    'units.json',
    'scaling.json',
    'time_definition.json',
    'ip_blacklist.json',
    'country.json',
    'company.json'
)));

# list of all static import files for testing the system consistency
const PATH_RESOURCE_FILES = ROOT_PATH . 'src/main/resources/';
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
 * @param string $msg_text is a short description that is used to group and limit the number of error messages
 * @param string $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
 * @param string $msg_type_id is the criticality level e.g. debug, info, warning, error or fatal error
 * @param string $function_name is the function name which has most likely caused the error
 * @param string $function_trace is the complete system trace to get more details
 * @param int $user_id is the user id who has probably seen the error message
 * return           the text that can be shown to the user in the navigation bar
 * TODO return the link to the log message so that the user can trace the bug fixing
 */
function log_msg(string $msg_text,
                 string $msg_description,
                 string $msg_log_level,
                 string $function_name,
                 string $function_trace,
                 int    $user_id,
                 bool   $force_log = false,
                 ?sql_db $given_db_con = null): string
{

    global $sys_log_msg_lst;
    global $db_con;

    $used_db_con = $db_con;
    if ($given_db_con != null) {
        $used_db_con = $given_db_con;
    }


    $result = '';

    if ($used_db_con == null) {
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
            $used_db_con->usr_id = $user_id;
            $sys_log_id = 0;

            $sys_log_msg_lst[] = $msg_type_text;
            if ($msg_log_level > LOG_LEVEL or $force_log) {
                $used_db_con->set_type(sql_db::TBL_SYS_LOG_FUNCTION);
                $function_id = $used_db_con->get_id($function_name);
                if ($function_id <= 0) {
                    $function_id = $used_db_con->add_id($function_name);
                }
                $msg_text = str_replace("'", "", $msg_text);
                $msg_description = str_replace("'", "", $msg_description);
                $function_trace = str_replace("'", "", $function_trace);
                $msg_text = $used_db_con->sf($msg_text);
                $msg_description = $used_db_con->sf($msg_description);
                $function_trace = $used_db_con->sf($function_trace);
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
                $used_db_con->set_type(sql_db::TBL_SYS_LOG);

                $sys_log_id = $used_db_con->insert($fields, $values, false);
                //$sql_result = mysqli_query($sql) or die('zukunft.com system log failed by query '.$sql.': '.mysqli_error().'. If this happens again, please send this message to errors@zukunft.com.');
                //$sys_log_id = mysqli_insert_id();
            }
            if ($msg_log_level >= MSG_LEVEL) {
                echo "Zukunft.com has detected a critical internal error: <br><br>" . $msg_text . " by " . $function_name . ".<br><br>";
                if ($sys_log_id > 0) {
                    echo 'You can track the solving of the error with this link: <a href="/http/error_log.php?id=' . $sys_log_id . '">www.zukunft.com/http/error_log.php?id=' . $sys_log_id . '</a><br>';
                }
            } else {
                if ($msg_log_level >= DSP_LEVEL) {
                    $usr = new user();
                    $usr->load_by_id($user_id);
                    $dsp = new view_dsp_old($usr);
                    $result .= $dsp->dsp_navbar_simple();
                    $result .= $msg_text . " (by " . $function_name . ").<br><br>";
                }
            }
        }
    }
    return $result;
}

function get_user_id(?user $calling_usr = null): ?int
{
    global $usr;
    $user_id = 0;
    if ($calling_usr != null) {
        $user_id = $calling_usr->id();
    } else {
        if ($usr != null) {
            $user_id = $usr->id();
        }
    }
    return $user_id;
}

function log_info(string $msg_text,
                  string $function_name = '',
                  string $msg_description = '',
                  string $function_trace = '',
                  ?user  $calling_usr = null,
                  bool   $force_log = false): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_level::INFO,
        $function_name, $function_trace,
        get_user_id($calling_usr),
        $force_log);
}

function log_warning(string $msg_text,
                     string $function_name = '',
                     string $msg_description = '',
                     string $function_trace = '',
                     ?user  $calling_usr = null, 
                     ?sql_db $given_db_con = null): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_level::WARNING,
        $function_name,
        $function_trace,
        get_user_id($calling_usr),
        false,
        $given_db_con
    );
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
    if ($db_con->postgres_link === false) {
        log_debug($code_name . ': start db setup');
        $db_con->setup();
        $db_con->open();
        if ($db_con->postgres_link === false) {
            log_fatal('Cannot connect to database', 'prg_restart');
        }
    } else {
        log_debug($code_name . ': db open');

        // check the system setup
        $db_chk = new db_check();
        $result = $db_chk->db_check($db_con);
        if ($result != '') {
            echo '\n';
            echo $result;
            $db_con->close();
            $db_con = null;
        }

        // preload all types from the database
        $sys_typ_lst = new type_lists();
        $sys_typ_lst->load($db_con, null);

        $log = new change_log(null);
        $db_changed = $log->create_log_references($db_con);

        // reload the type list if needed and trigger an update in the frontend
        // even tough the update of the preloaded list should already be done by the single adds
        if ($db_changed) {
            $sys_typ_lst->load($db_con, null);
        }

    }
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

/**
 * @return string the content of a resource file
 */
function resource_file(string $resource_path): string
{
    $result = file_get_contents(PATH_RESOURCE_FILES . $resource_path);
    if ($result === false) {
        $result = 'Cannot get file from ' . PATH_RESOURCE_FILES . $resource_path;
    }
    return $result;
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
function prg_version_is_newer_test(test_cleanup $t): void
{
    $result = zu_dsp_bool(prg_version_is_newer('0.0.1'));
    $target = 'false';
    $t->display('prg_version 0.0.1 is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer(PRG_VERSION));
    $target = 'false';
    $t->display('prg_version ' . PRG_VERSION . ' is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer(NEXT_VERSION));
    $target = 'true';
    $t->display('prg_version ' . NEXT_VERSION . ' is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer('0.1.0', '0.0.9'));
    $target = 'true';
    $t->display('prg_version 0.1.0 is newer than 0.0.9', $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer('0.2.3', '1.1.1'));
    $target = 'false';
    $t->display('prg_version 0.2.3 is newer than 1.1.1', $target, $result);
}

/*
string functions
*/

function zu_trim($text): string
{
    return trim(preg_replace('!\s+!', ' ', $text));
}


/*
  name shortcuts - rename some often used functions to make to code look nicer and not draw the focus away from the important part
  --------------
*/


// SQL list: create a query string for the standard list
// e.g. the type "source" creates the SQL statement "SELECT source_id, source_name FROM sources ORDER BY source_name;"
function sql_lst($type): string
{
    global $db_con;
    $db_con->set_type($type);
    return $db_con->sql_std_lst();
}

// similar to "sql_lst", but taking the user sandbox into account
function sql_lst_usr($type, $usr): string
{
    global $db_con;
    $db_con->set_type($type);
    $db_con->usr_id = $usr->id();
    return $db_con->sql_std_lst_usr();
}
