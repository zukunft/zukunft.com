<?php

/*

  zu_lib.php - the main ZUkunft.com LIBrary
  __________

TODO make sure that no word, phrase, verb and formula have the same name by using a name view table for each user
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
TODO create a User Interface API
TODO offer to use FreeOTP for two factor authentication
TODO change config files from json to yaml to complete "reduce to the max"
TODO create a user cache with all the data that the user usually usses for fast reactions
TODO move the user fields to words with the aord with the prefix "system user"
TODO for the registration mask first preselect the country based on the geolocation and offer to switch language, than select the places based on country and geolocation and the street
TODO in the user registration mask allow to add places and streets on the fly and show a link to add missing street on open street map
TODO use the object constructor if useful
TODO capsule all critical functions in classes for security reason, to make sure that they never call be called without check e.g. database reset
TODO to speed up create one database statement for each user action if possible


TODO creste a table startup page with a
     Table with two col and two rows and four last used pages below. If now last used pages show the demo pages.
     Typing words in the top left cell select a word with the default page
     Typing in the top right cell adds one more column and two rows and typing offer to select a word and also adds related row names based on child words
     Typing in the lower left cell also adds one more row and two cols and typing shows related parent words as column headers
     Typing in the lower right cell starts the formula selection and an = is added as first char.
     Typing = in any cell starts the formula selection
     Typing an operator sign after a space starts the formula creation and a formula name is suggested


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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com

*/

// the used database objects (the table name is in most cases with an extra 's', because each table contains the data for many objects)
// TODO use const for all object names
const DB_TYPE_USER = 'user';
const DB_TYPE_USER_PROFILE = 'user_profile';
const DB_TYPE_WORD = 'word';
const DB_TYPE_WORD_TYPE = 'word_type';
const DB_TYPE_WORD_LINK = 'word_link';
const DB_TYPE_VERB = 'verb';
const DB_TYPE_PHRASE = 'phrase';
const DB_TYPE_PHRASE_GROUP = 'phrase_group';
const DB_TYPE_VALUE = 'value';
const DB_TYPE_VALUE_TIME_SERIES = 'value_time_series';
const DB_TYPE_VALUE_PHRASE_LINK = 'value_phrase_link';
const DB_TYPE_SOURCE = 'source';
const DB_TYPE_SOURCE_TYPE = 'source_type';
const DB_TYPE_REF = 'ref';
const DB_TYPE_REF_TYPE = 'ref_type';
const DB_TYPE_FORMULA = 'formula';
const DB_TYPE_FORMULA_TYPE = 'formula_type';
const DB_TYPE_FORMULA_LINK = 'formula_link';
const DB_TYPE_FORMULA_ELEMENT = 'formula_element';
const DB_TYPE_FORMULA_ELEMENT_TYPE = 'formula_element_type';
const DB_TYPE_FORMULA_VALUE = 'formula_value';
const DB_TYPE_VIEW = 'view';
const DB_TYPE_VIEW_TYPE = 'view_type';
const DB_TYPE_VIEW_COMPONENT = 'view_component';
const DB_TYPE_VIEW_COMPONENT_LINK = 'view_component_link';
const DB_TYPE_VIEW_COMPONENT_TYPE = 'view_component_type';
const DB_TYPE_VIEW_COMPONENT_LINK_TYPE = 'view_component_link_type';

const DB_TYPE_CHANGE = 'change';
const DB_TYPE_CHANGE_TABLE = 'change_table';
const DB_TYPE_CHANGE_FIELD = 'change_field';
const DB_TYPE_CHANGE_ACTION = 'change_action';
const DB_TYPE_CHANGE_LINK = 'change_link';
const DB_TYPE_CONFIG = 'config';
const DB_TYPE_SYS_LOG = 'sys_log';
const DB_TYPE_SYS_LOG_STATUS = 'sys_log_status';
const DB_TYPE_SYS_LOG_FUNCTION = 'sys_log_function';
const DB_TYPE_SYS_SCRIPT = 'sys_script'; // to log the execution times for code optimising
const DB_TYPE_TASK = 'calc_and_cleanup_task';
const DB_TYPE_TASK_TYPE = 'calc_and_cleanup_task_type';

const DB_TYPE_SHARE = 'share_type';
const DB_TYPE_PROTECTION = 'protection_type';

const DB_TYPE_USER_PREFIX = 'user_';

const DB_FIELD_EXT_ID = '_id';
const DB_FIELD_EXT_NAME = '_name';

// the fixed system user
const SYSTEM_USER_ID = 1; //


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


global $root_path;

if ($root_path == '') {
    $root_path = '../';
}

// set the paths of the program code
$path_php = $root_path . 'src/main/php/'; // path of the main php source code

// database links
include_once $root_path . 'database/sql_db.php';
include_once $path_php . 'db/db_check.php';
// utils
include_once $path_php . 'utils/json_utils.php';
include_once $path_php . 'model/user/user_type_list.php';
include_once $path_php . 'model/system/system_utils.php';
include_once $path_php . 'model/system/system_error_log_status_list.php';
include_once $path_php . 'model/change/log_table.php';
// service
include_once $path_php . 'service/import/import_file.php';
include_once $path_php . 'service/import/import.php';
include_once $path_php . 'service/export/export.php';
include_once $path_php . 'service/export/json.php';
include_once $path_php . 'service/export/xml.php';
// classes
include_once $path_php . 'model/user/user.php';
include_once $path_php . 'model/user/user_type.php';
include_once $path_php . 'model/user/user_profile.php';
include_once $path_php . 'model/user/user_profile_list.php';
include_once $path_php . 'model/user/user_list.php';
include_once $path_php . 'model/user/user_log.php';
include_once $path_php . 'model/user/user_log_link.php';
include_once $path_php . 'web/user_display.php';
include_once $path_php . 'web/user_log_display.php';
include_once $path_php . 'model/sandbox/user_sandbox.php';
include_once $path_php . 'model/sandbox/user_sandbox_description.php';
include_once $path_php . 'model/sandbox/user_sandbox_exp_named.php';
include_once $path_php . 'model/sandbox/user_sandbox_exp_link.php';
include_once $path_php . 'model/sandbox/share_type_list.php';
include_once $path_php . 'model/sandbox/protection_type_list.php';
include_once $path_php . 'web/user_sandbox_display.php';
include_once $path_php . 'model/system/system_error_log.php';
include_once $path_php . 'model/system/system_error_log_list.php';
include_once $path_php . 'web/display_interface.php';
include_once $path_php . 'web/display_html.php';
include_once $path_php . 'web/display_button.php';
include_once $path_php . 'web/display_selector.php';
include_once $path_php . 'web/display_list.php';
include_once $path_php . 'model/helper/word_link_object.php';
include_once $path_php . 'model/word/word.php';
include_once $path_php . 'model/word/word_exp.php';
include_once $path_php . 'model/word/word_type_list.php';
include_once $path_php . 'web/word_display.php';
include_once $path_php . 'model/word/word_list.php';
include_once $path_php . 'model/word/word_link.php';
include_once $path_php . 'model/word/word_link_exp.php';
include_once $path_php . 'model/word/word_link_list.php';
include_once $path_php . 'model/phrase/phrase.php';
include_once $path_php . 'model/phrase/phrase_list.php';
include_once $path_php . 'model/phrase/phrase_group.php';
include_once $path_php . 'model/phrase/phrase_group_list.php';
include_once $path_php . 'model/verb/verb.php';
include_once $path_php . 'model/verb/verb_list.php';
include_once $path_php . 'model/phrase/term.php';
include_once $path_php . 'model/value/value.php';
include_once $path_php . 'model/value/value_dsp.php';
include_once $path_php . 'model/value/value_exp.php';
include_once $path_php . 'model/value/value_list.php';
include_once $path_php . 'web/value_list_display.php';
include_once $path_php . 'model/value/value_phrase_link.php';
include_once $path_php . 'model/ref/source.php';
include_once $path_php . 'model/ref/ref.php';
include_once $path_php . 'model/ref/ref_exp.php';
include_once $path_php . 'model/ref/ref_type.php';
include_once $path_php . 'model/ref/ref_type_list.php';
include_once $path_php . 'model/formula/expression.php';
include_once $path_php . 'model/formula/formula.php';
include_once $path_php . 'model/formula/formula_exp.php';
include_once $path_php . 'model/formula/formula_type_list.php';
include_once $path_php . 'model/formula/formula_list.php';
include_once $path_php . 'model/formula/formula_link.php';
include_once $path_php . 'model/formula/formula_link_list.php';
include_once $path_php . 'model/formula/formula_value.php';
include_once $path_php . 'model/formula/formula_value_list.php';
include_once $path_php . 'model/formula/formula_element.php';
include_once $path_php . 'model/formula/formula_element_list.php';
include_once $path_php . 'model/formula/formula_element_group.php';
include_once $path_php . 'model/formula/formula_element_group_list.php';
include_once $path_php . 'model/formula/figure.php';
include_once $path_php . 'model/formula/figure_list.php';
include_once $path_php . 'web/formula_display.php';
include_once $path_php . 'model/system/batch_job.php';
include_once $path_php . 'model/system/batch_job_list.php';
include_once $path_php . 'model/system/batch_job_type_list.php';
include_once $path_php . 'model/view/view.php';
include_once $path_php . 'model/view/view_exp.php';
include_once $path_php . 'model/view/view_list.php';
include_once $path_php . 'model/view/view_type_list.php';
include_once $path_php . 'web/view_display.php';
include_once $path_php . 'model/view/view_component.php';
include_once $path_php . 'model/view/view_component_exp.php';
include_once $path_php . 'model/view/view_component_dsp.php';
include_once $path_php . 'model/view/view_component_type_list.php';
include_once $path_php . 'model/view/view_component_link.php';
include_once $path_php . 'model/view/view_component_link_types.php';

// include all other libraries that are usually needed
include_once $root_path . 'db_link/zu_lib_sql_link.php';
include_once $path_php . 'service/db_code_link.php';
include_once $path_php . 'service/zu_lib_sql_code_link.php';
include_once $path_php . 'service/config.php';

// used at the moment, but to be replaced with R-Project call
include_once $path_php . 'service/zu_lib_calc_math.php';

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

// libraries that can be dismissed, but still used for regression testing (using test.php)
/*
include_once $root_path.'lib/test/zu_lib_word_dsp.php';           if ($debug > 9) { echo 'lib word display loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_sql.php';                if ($debug > 9) { echo 'lib sql loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_link.php';               if ($debug > 9) { echo 'lib link loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_sql_naming.php';         if ($debug > 9) { echo 'lib sql naming loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_value.php';              if ($debug > 9) { echo 'lib value loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_word.php';               if ($debug > 9) { echo 'lib word loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_word_db.php';            if ($debug > 9) { echo 'lib word database link loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_calc.php';               if ($debug > 9) { echo 'lib calc loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_value_db.php';           if ($debug > 9) { echo 'lib value database link loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_value_dsp.php';          if ($debug > 9) { echo 'lib value display loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_user.php';               if ($debug > 9) { echo 'lib user loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_html.php';               if ($debug > 9) { echo 'lib html loaded<br>'; }
*/

/*

Target is to have with version 0.1 a usable version for alpha testing. 
The roadmap for version 0.1 can be found here: https://zukunft.com/mantisbt/roadmap_page.php

The beta test is expected to start with version 0.7

*/

// global code settings
// TODO move the user interface setting to the user page, so that he can define which UI he wants to use
const UI_USE_BOOTSTRAP = 1; // IF FALSE a simple HTML frontend without javascript is used
const UI_MIN_RESPONSE_TIME = 2; // minimal time after that the user user should see an update e.g. during long calculations every 2 sec the user should seen the screen updated

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
const UI_CAN_CHANGE_WORD_LINK_NAME = TRUE; // dito for phrases
const UI_CAN_CHANGE_FORMULA_NAME = TRUE; // dito for formulas
const UI_CAN_CHANGE_VERB_NAME = TRUE; // dito for verbs
const UI_CAN_CHANGE_SOURCE_NAME = TRUE; // dito for sources

// program configuration names
const CFG_SITE_NAME = 'site_name';                           // the name of the pod
const CFG_VERSION_DB = 'version_database';                   // the version of the database at the moment to trigger an update script if needed
const CFG_LAST_CONSISTENCY_CHECK = 'last_consistency_check'; // datetime of the last database consistency check

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

// text conversion const (used to convert word, verbs or formula text to a database reference)
const ZUP_CHAR_WORD = '"';    // or a zukunft verb or a zukunft formula
const ZUP_CHAR_WORDS_START = '[';    //
const ZUP_CHAR_WORDS_END = ']';    //
const ZUP_CHAR_SEPERATOR = ',';    //
const ZUP_CHAR_RANGE = ':';    //
const ZUP_CHAR_TEXT_CONCAT = '&';    //

// to convert word, formula or verbs database reference to word or word list and in a second step to a value or value list
const ZUP_CHAR_WORD_START = '{t';   //
const ZUP_CHAR_WORD_END = '}';    //
const ZUP_CHAR_LINK_START = '{l';   //
const ZUP_CHAR_LINK_END = '}';    //
const ZUP_CHAR_FORMULA_START = '{f';   //
const ZUP_CHAR_FORMULA_END = '}';    //

const ZUC_MAX_CALC_LAYERS = '10000';    // max number of calculation layers

// math calc (probably not needed any more if r-project.org is used)
const ZUP_CHAR_CALC = '=';    //
const ZUP_OPER_ADD = '+';    //
const ZUP_OPER_SUB = '-';    //
const ZUP_OPER_MUL = '*';    //
const ZUP_OPER_DIV = '/';    //

const ZUP_OPER_AND = '&';    //
const ZUP_OPER_OR = '|';    //

// fixed functions
const ZUP_FUNC_IF = 'if';    //
const ZUP_FUNC_SUM = 'sum';    //
const ZUP_FUNC_ISNUM = 'is.numeric';    //

// text conversion const (used to convert word, formula or verbs text to a reference)
const ZUP_CHAR_BRAKET_OPEN = '(';    //
const ZUP_CHAR_BRAKET_CLOSE = ')';    //
const ZUP_CHAR_TXT_FIELD = '"';    // don't look for math symbols in text that is a high quotes


// file links used
//const ZUH_IMG_ADD       = "../images/button_add_small.jpg";
//const ZUH_IMG_EDIT      = "../images/button_edit_small.jpg";
const ZUH_IMG_ADD = "../images/button_add.svg";
const ZUH_IMG_EDIT = ".../images/button_edit.svg";
const ZUH_IMG_DEL = "../images/button_del.svg";
const ZUH_IMG_UNDO = "../images/button_undo.svg";
const ZUH_IMG_FIND = ".../images/button_find.svg";
const ZUH_IMG_UN_FILTER = "../images/button_filter_off.svg";
const ZUH_IMG_BACK = "../images/button_back.svg";
const ZUH_IMG_LOGO = "../images/ZUKUNFT_logo.svg";

const ZUH_IMG_ADD_FA = "fa-plus-square";
const ZUH_IMG_EDIT_FA = "fa-edit";
const ZUH_IMG_DEL_FA = "fa-times-circle";

# list of JSON files that define the base configuration of zukunft.com that is supposed never to be changed
define("PATH_BASE_CONFIG_FILES", $root_path . 'src/main/resources/');
const PATH_BASE_CODE_LINK_FILES = PATH_BASE_CONFIG_FILES . 'db_code_links/';
define("BASE_CODE_LINK_FILES", serialize(array(
    'calc_and_cleanup_task_types',
    'change_actions',
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
    'task_types',
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
    'time_definition.json',
    'country.json',
    'company.json'
)));

# list of all static import files for testing the system consistency
define("PATH_TEST_IMPORT_FILES", $root_path . 'src/test/resources/');
define("TEST_IMPORT_FILE_LIST", serialize(array(
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

# list of import files for quick win testing
/*
define ("TEST_IMPORT_FILE_LIST_QUICK", serialize (array ('COVID-19.json',
                                                         'countries.json', 
                                                         'real_estate.json', 
                                                         'Ultimatum_game.json')));
define ("TEST_IMPORT_FILE_LIST_QUICK", serialize (array ('ABB_2013.json','work.json')));
*/
define("TEST_IMPORT_FILE_LIST_QUICK", serialize(array('car_costs.json')));

// for internal functions debugging
// each complex function should call this at the beginning with the parameters and with -1 at the end with the result
// called function should use $debug-1
function log_debug($msg_text, $debug_overwrite = null)
{
    global $debug;

    $debug_used = $debug;

    if ($debug_overwrite != null) {
        $debug_used = $debug_overwrite;
    }

    if ($debug_used > 0) {
        echo $msg_text . '.<br>';
        //ob_flush();
        //flush();
    }
}

// for system messages no debug calls to avoid loops
// $msg_text        is a short description that is used to group and limit the number of error messages
// $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
// $msg_type_id     is the criticality level e.g. debug, info, warning, error or fatal error
// $function_name   is the function name which has most likely caused the error
// $function_trace  is the complete system trace to get more details
// $usr             is the user id who has probably seen the error message
// return           the text that can be shown to the user in the navigation bar
function log_msg($msg_text, $msg_description, $msg_log_level, $function_name, $function_trace, $usr): string
{

    global $sys_log_msg_lst;
    global $db_con;
    $result = '';

    // fill up fields with default values
    if ($msg_description == '') {
        $msg_description = $msg_text;
    }
    if ($function_trace == '') {
        $function_trace = (new Exception)->getTraceAsString();
    }
    $user_id = SYSTEM_USER_ID; // fallback
    if (isset($usr)) {
        $user_id = $usr->id;
    } elseif (isset($_SESSION['usr_id'])) {
        $user_id = $_SESSION['usr_id'];
    }

    // assuming that the relevant part of the message is at the beginning of the message at least to avoid double entries
    $msg_type_text = $user_id . substr($msg_text, 0, 200);
    if (!in_array($msg_type_text, $sys_log_msg_lst)) {
        $db_con->usr_id = $user_id;
        $sys_log_id = 0;

        $sys_log_msg_lst[] = $msg_type_text;
        if ($msg_log_level > LOG_LEVEL) {
            $db_con->set_type(DB_TYPE_SYS_LOG_FUNCTION);
            $function_id = $db_con->get_id($function_name);
            if ($function_id <= 0) {
                $function_id = $db_con->add_id($function_name);
            }
            $msg_text = str_replace("'", "", $msg_text);
            $msg_description = str_replace("'", "", $msg_description);
            $function_trace = str_replace("'", "", $function_trace);
            $msg_text = sf($msg_text);
            $msg_description = sf($msg_description);
            $function_trace = sf($function_trace);
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
            $db_con->set_type(DB_TYPE_SYS_LOG);
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
                $dsp = new view_dsp;
                $result .= $dsp->dsp_navbar_simple();
                $result .= $msg_text . " (by " . $function_name . ").<br><br>";
            }
        }
    }
    return $result;
}

function log_info($msg_text, $function_name = '', $msg_description = '', $function_trace = '', $usr = null): string
{
    return log_msg($msg_text, $msg_description, sys_log_level::INFO, $function_name, $function_trace, $usr);
}

function log_warning($msg_text, $function_name = '', $msg_description = '', $function_trace = '', $usr = null): string
{
    return log_msg($msg_text, $msg_description, sys_log_level::WARNING, $function_name, $function_trace, $usr);
}

function log_err($msg_text, $function_name = '', $msg_description = '', $function_trace = '', $usr = null): string
{
    return log_msg($msg_text, $msg_description, sys_log_level::ERROR, $function_name, $function_trace, $usr);
}

function log_fatal($msg_text, $function_name, $msg_description = '', $function_trace = '', $usr = null): string
{
    echo 'FATAL ERROR! ' . $msg_text;
    // TODO write first to the most secure system log because if the database connection is lost no writing to the database is possible
    return log_msg('FATAL ERROR! ' . $msg_text, $msg_description, sys_log_level::FATAL, $function_name, $function_trace, $usr);
}

/**
 * should be called from all code that can be accessed by an url
 * return null if the db connection fails or the db is not compatible
 *
 * @param string $code_name the place that is displayed to the user e.g. add word
 * @param string $style the display style used to show the place
 * @return sql_db the open database connection
 */
function prg_start(string $code_name, string $style = ""): sql_db
{
    global $sys_time_start, $sys_script;

    // resume session (based on cookies)
    session_start();

    log_debug($code_name . ' ...');

    $sys_time_start = time();
    $sys_script = $code_name;

    log_debug($code_name . ' ... session_start');

    // html header
    echo dsp_header("", $style);

    return prg_restart($code_name, $style);
}

/**
 * open the database connection and load the base cache
 * @param string $code_name the place that is displayed to the user e.g. add word
 * @return sql_db the open database connection
 */
function prg_restart(string $code_name): sql_db
{
    global $system_users;
    global $user_profiles;
    global $word_types;
    global $formula_types;
    global $view_types;
    global $view_component_types;
    global $view_component_link_types;
    global $ref_types;
    global $share_types;
    global $protection_types;
    global $verbs;
    global $system_views;
    global $sys_log_stati;
    global $job_types;
    global $change_log_tables;

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    log_debug($code_name . ' ... db set');
    $db_con->open();
    log_debug($code_name . ' ... database link open');

    // check the system setup
    $result = db_check($db_con);
    if ($result != '') {
        echo $result;
        $db_con->close();
        $db_con = null;
    }

    // load default records
    $sys_log_stati = new sys_log_status();
    $sys_log_stati->load($db_con);
    $system_users = new user_list();
    $system_users->load_system($db_con);

    // load the type database enum
    // these tables are expected to be so small that it is more efficient to load all database records once at start
    $user_profiles = new user_profile_list();
    $user_profiles->load($db_con);
    $word_types = new word_type_list();
    $word_types->load($db_con);
    $formula_types = new formula_type_list();
    $formula_types->load($db_con);
    $view_types = new view_type_list();
    $view_types->load($db_con);
    $view_component_types = new view_component_type_list();
    $view_component_types->load($db_con);
    // not yet needed?
    //$view_component_link_types = new view_component_link_type_list();
    //$view_component_link_types->load($db_con);
    $ref_types = new ref_type_list();
    $ref_types->load($db_con);
    $share_types = new share_type_list();
    $share_types->load($db_con);
    $protection_types = new protection_type_list();
    $protection_types->load($db_con);
    $job_types = new job_type_list();
    $job_types->load($db_con);
    $change_log_tables = new change_log_table();
    $change_log_tables->load($db_con);

    // preload the little more complex objects
    $verbs = new verb_list();
    $verbs->load($db_con);
    //$system_views = new view_list();
    //$system_views->load($db_con);

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
function load_usr_data()
{
    global $db_con;
    global $usr;
    global $verbs;
    global $system_views;

    $verbs = new verb_list();
    $verbs->usr = $usr;
    $verbs->load($db_con);

    $system_views = new view_list();
    $system_views->usr = $usr;
    $system_views->load($db_con);

}

function prg_end($db_con)
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    echo dsp_footer();

    // write the execution time to the database if it is long
    $sys_time_end = time();
    if ($sys_time_end > $sys_time_limit) {
        $db_con->usr_id = SYSTEM_USER_ID;
        $db_con->set_type(DB_TYPE_SYS_SCRIPT);
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
        $sql = "INSERT INTO sys_script_times (sys_script_start, sys_script_id, url) VALUES ('" . $start_time_sql . "'," . $sys_script_id . "," . sf($calling_uri) . ");";
        $db_con->exe($sql);
    }

    // Free result test
    //mysqli_free_result($result);

    // Closing connection
    $db_con->close();

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);

    log_debug(' ... database link closed');
}

// special page closing only for the about page
function prg_end_about($link)
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    echo dsp_footer(true);

    // Closing connection
    zu_sql_close($link);

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);

    log_debug(' ... database link closed');
}

// special page closing of api pages
// for the api e.g. the csv export no footer should be shown
function prg_end_api($link)
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    // Closing connection
    zu_sql_close($link);

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);

    log_debug(' ... database link closed');
}

/*

display functions

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


// returns true if the version to check is older than this program version
// used e.g. for import to allow importing of files of an older version without warning
function prg_version_is_newer($prg_version_to_check, $this_version = PRG_VERSION)
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

// unit_test for prg_version_is_newer
function prg_version_is_newer_test()
{
    $result = zu_dsp_bool(prg_version_is_newer('0.0.1'));
    $target = 'false';
    test_dsp('prg_version 0.0.1 is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer(PRG_VERSION));
    $target = 'false';
    test_dsp('prg_version ' . PRG_VERSION . ' is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer(NEXT_VERSION));
    $target = 'true';
    test_dsp('prg_version ' . NEXT_VERSION . ' is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer('0.1.0', '0.0.9'));
    $target = 'true';
    test_dsp('prg_version 0.1.0 is newer than 0.0.9', $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer('0.2.3', '1.1.1'));
    $target = 'false';
    test_dsp('prg_version 0.2.3 is newer than 1.1.1', $target, $result);
}

/*
string functions
*/

function zu_trim($text): string
{
    return trim(preg_replace('!\s+!', ' ', $text));
}

// 
function zu_str_left_of($text, $maker)
{
    $result = "";
    $pos = strpos($text, $maker);
    if ($pos > 0) {
        $result = substr($text, 0, strpos($text, $maker));
    }
    return $result;
}

function zu_str_right_of($text, $maker)
{
    $result = "";
    if ($text === $maker) {
        $result = "";
    } else {
        if (substr($text, strpos($text, $maker), strlen($maker)) === $maker) {
            $result = substr($text, strpos($text, $maker) + strlen($maker));
        }
    }
    return $result;
}

function zu_str_between($text, $maker_start, $maker_end)
{
    log_debug('zu_str_between "' . $text . '", start "' . $maker_start . '" end "' . $maker_end . '"');
    $result = zu_str_right_of($text, $maker_start);
    log_debug('zu_str_between -> "' . $result . '" is right of "' . $maker_start . '"');
    $result = zu_str_left_of($result, $maker_end);
    log_debug('zu_str_between -> "' . $result . '"');
    return $result;
}

/*
string functions (to be dismissed)
*/

// some small string related functions to shorten code and make the code clearer
function zu_str_left($text, $pos)
{
    return substr($text, 0, $pos);
}

function zu_str_right($text, $pos)
{
    return substr($text, $pos * -1);
}

// TODO rename to the php 8.0 function str_starts_with
function zu_str_is_left($text, $maker)
{
    $result = false;
    if (substr($text, 0, strlen($maker)) == $maker) {
        $result = true;
    }
    return $result;
}

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

/**
 * trim each array value and exclude empty values
 * @param array $in_array with leading spaces or empty strings
 * @return array without leading spaces or empty strings
 */
function array_trim(array $in_array): array
{
    $result = array();
    for ($i = 0; $i < count($in_array); $i++) {
        if (trim($in_array[$i]) != '') {
            $result[] = trim($in_array[$i]);
        }
    }
    return $result;
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

// similar to zu_lst_not_in, but looking at the array value not the key
function zu_lst_not_in_no_key($in_lst, $exclude_lst): array
{
    log_debug('zu_lst_not_in_no_key(' . dsp_array($in_lst) . 'ex' . dsp_array($exclude_lst) . ')');
    $result = array();
    foreach ($in_lst as $lst_entry) {
        if (!in_array($lst_entry, $exclude_lst)) {
            $result[] = $lst_entry;
        }
    }
    log_debug('zu_lst_not_in_no_key -> (' . dsp_array($result) . ')');
    return $result;
}

// similar to zu_lst_not_in, but excluding only one value (diff to in_array????)
function zu_lst_excluding($in_lst, $exclude_id): array
{
    log_debug('zu_lst_excluding(' . dsp_array($in_lst) . 'ex' . $exclude_id . ')');
    $result = array();
    foreach ($in_lst as $lst_entry) {
        if ($lst_entry <> $exclude_id) {
            $result[] = $lst_entry;
        }
    }
    log_debug('zu_lst_excluding -> (' . dsp_array($result) . ')');
    return $result;
}

// get all entries of the list that are not in the second list
function zu_lst_in($in_lst, $only_if_lst): array
{
    $result = array();
    foreach (array_keys($in_lst) as $lst_entry) {
        if (in_array($lst_entry, array_keys($only_if_lst))) {
            $result[$lst_entry] = $in_lst[$lst_entry];
        }
    }
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

    log_debug("zu_lst_get_common_ids -> (" . dsp_array($result) . ")");
    return $result;
}

// collects from an array in an array a list of all ids similar to zu_lst_get_common_ids
// if this is used for a val_lst_wrd and the sub_array_pos is 1 the common list of word ids is returned
function zu_lst_all_ids($val_lst, $sub_array_pos)
{
    log_debug("zu_lst_all_ids (" . zu_lst_dsp($val_lst) . ",pos" . $sub_array_pos . ")");
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

    log_debug("zu_lst_all_ids -> (" . dsp_array($result) . ")");
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
                    log_debug("zu_lst_id_filter -> test (" . $wrd_id . " in " . zu_lst_dsp($id_lst) . ")");
                    if (array_key_exists($wrd_id, $id_lst)) {
                        $found = true;
                        log_debug("zu_lst_id_filter -> found (" . $wrd_id . " in " . zu_lst_dsp($id_lst) . ")");
                    }
                }
            }
            if ($found) {
                $result[$val_key] = $val_entry;
            }
        }
    }

    log_debug("zu_lst_id_filter -> (" . zu_lst_dsp($result) . ")");
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
            //zu_debug("zu_lst_to_array -> ".$lst_entry[0]." (first)");
        } else {
            $result[$lst_key] = $lst_entry;
            //zu_debug("zu_lst_to_array -> ".$lst_entry);
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
function str_starts_with(string $long_string, string $prefix): bool
{
    $result = false;
    if (substr($long_string, 0, strlen($prefix)) == $prefix) {
        $result = true;
    }
    return $result;
}

// port php 8 function to 7.4
function str_ends_with(string $long_string, string $postfix): bool
{
    $result = false;
    if (substr($long_string, strlen($postfix) * -1) == $postfix) {
        $result = true;
    }
    return $result;
}

