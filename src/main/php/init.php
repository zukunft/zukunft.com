<?php

/*

    init.php - for initial loading of the needed php scripts
    ----------


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

use cfg\const\paths;
use html\const\paths as html_paths;
use cfg\db\db_check;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\element\element;
use cfg\helper\config_numbers;
use cfg\helper\type_lists;
use cfg\log\change_action;
use cfg\log\change_field;
use cfg\log\change_link;
use cfg\log\change_log;
use cfg\log\change_table;
use cfg\log\change_value;
use cfg\log_text\text_log;
use cfg\system\job;
use cfg\system\session;
use cfg\system\sys_log_function;
use cfg\system\sys_log_status;
use cfg\system\sys_log_type;
use cfg\system\system_time;
use cfg\system\system_time_type;
use cfg\system_time_list;
use cfg\user\user;
use cfg\user\user_profile;
use cfg\user\user_profile_list;
use cfg\user\user_type;
use html\html_base;
use shared\const\rest_ctrl;
use shared\const\users;
use shared\helper\Translator;
use shared\library;
use test\test_cleanup;

// parameters for internal testing and debugging
const LIST_MIN_NAMES = 4; // number of object names that should at least be shown
const LIST_MIN_NUM = 20; // number of object ids that should at least be shown
const DEBUG_SHOW_USER = 10; // starting from this debug level the user should be shown in the debug text

// set all path for the backend program code here at once
const CONST_PATH = PHP_PATH . 'cfg' . DIRECTORY_SEPARATOR . 'const' . DIRECTORY_SEPARATOR;
include_once CONST_PATH . 'paths.php';

// set all path for the frontend program code here at once
const WEB_CONST_PATH = PHP_PATH . 'web' . DIRECTORY_SEPARATOR . 'const' . DIRECTORY_SEPARATOR;
include_once WEB_CONST_PATH . 'paths.php';

// test path for the initial load of the test files
const TEST_PATH = paths::SRC . 'test' . DIRECTORY_SEPARATOR;
// the test code path
const TEST_PHP_PATH = TEST_PATH . 'php' . DIRECTORY_SEPARATOR;
// the test const path
const TEST_CONST_PATH = TEST_PHP_PATH . 'const' . DIRECTORY_SEPARATOR;


// the main global vars to shorten the code by avoiding them in many function calls as parameter
global $db_con; // the database connection
global $usr;    // the session user
global $debug;  // the debug level

// logging
include_once paths::MODEL_LOG_TEXT . 'text_log_functions.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_format.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_level.php';
include_once paths::MODEL_LOG_TEXT . 'text_log.php';

// global vars for system control
global $sys_script;      // name php script that has been call this library
global $sys_trace;       // names of the php functions
global $sys_time_start;  // to measure the execution time
global $sys_time_limit;  // to write too long execution times to the log to improve the code
global $sys_log_msg_lst; // to avoid repeating the same message
global $log_txt; // the log object for standard io logging

$sys_script = "";
$sys_trace = "";
$sys_time_start = time();
$sys_time_limit = time() + 2;
$sys_log_msg_lst = array();
$log_txt = new text_log();

// check php version
$version = explode('.', PHP_VERSION);
if ($version[0] < 8) {
    if ($version[1] < 1) {
        echo 'at least php version 8.1 is needed';
    }
}
// TODO check if "sudo apt-get install php-curl" is done for testing
//phpinfo();

// database links
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'db_check.php';

include_once html_paths::HTML . 'html_base.php';

// include all other libraries that are usually needed
include_once paths::MODEL_CONST . 'env.php';
include_once paths::SERVICE . 'db_cl.php';
include_once paths::SERVICE . 'config.php';

// to avoid circle include
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_LOG . 'change_link.php';

// preloaded lists
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_HELPER . 'type_lists.php';
include_once paths::MODEL_SYSTEM . 'BasicEnum.php';
include_once paths::MODEL_SYSTEM . 'sys_log_level.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::MODEL_SYSTEM . 'system_time_type.php';
include_once paths::MODEL_USER . 'user_list.php';
include_once paths::MODEL_USER . 'user_profile_list.php';
include_once paths::MODEL_PHRASE . 'phrase_types.php';
include_once paths::MODEL_ELEMENT . 'element_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_link_type_list.php';
include_once paths::MODEL_VIEW . 'view_type.php';
include_once paths::MODEL_VIEW . 'view_type_list.php';
include_once paths::MODEL_COMPONENT . 'component_type_list.php';
include_once paths::MODEL_COMPONENT . 'position_type_list.php';
include_once paths::MODEL_REF . 'ref_type_list.php';
include_once paths::MODEL_REF . 'source_type_list.php';
include_once paths::MODEL_SANDBOX . 'share_type_list.php';
include_once paths::MODEL_SANDBOX . 'protection_type_list.php';
include_once paths::MODEL_LANGUAGE . 'language_list.php';
include_once paths::MODEL_LANGUAGE . 'language_form_list.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_LOG . 'change_action_list.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::MODEL_LOG . 'change_table_list.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_field_list.php';
include_once paths::MODEL_LOG_TEXT . 'text_log.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_functions.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_VIEW . 'view_sys_list.php';


// used at the moment, but to be replaced with R-Project call
include_once paths::SERVICE_MATH . 'calc_internal.php';

// settings
include_once paths::PHP_LIB . 'application.php';

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
const MAX_LOOP = 10000; // maximal number of loops to avoid hanging while loops; used for example for the number of formula elements

// max number of recursive call to avoid endless looping in case of a program error
const MAX_RECURSIVE = 10;

const ZUC_MAX_CALC_LAYERS = '10000';    // max number of calculation layers

// list of classes that use a database table but where the changes do not need to be logged
// TODO Prio 2 move to const/def class?
const CLASSES_NO_CHANGE_LOG = [
    sys_log_status::class,
    sys_log_function::class,
    sys_log_type::class,
    system_time_type::class,
    system_time::class,
    change_action::class,
    change_table::class,
    change_field::class,
    change_link::class,
    change_value::class,
    'change*',
    session::class,
    job::class,
    element::class,
    'phrase*',
    'user_phrase*',
    'prime_phrase*',
    'user_prime_phrase*',
    'term*',
    'user_term*',
    'prime_term*',
    'user_prime_term*',
    'result*',
    'user_result*',
];

// TODO Prio 2 move to const/def class?
const CLASS_WITH_USER_CODE_LINK_CSV = [
    user_profile::class,
    user_type::class
];
// list of all sequences used in the database
// TODO base the list on the class list const and a sequence name function
// TODO Prio 2 move to const/def class?
const DB_SEQ_LIST = [
    'sys_log_status_sys_log_status_id_seq',
    'sys_log_sys_log_id_seq',
    'elements_element_id_seq',
    'element_types_element_type_id_seq',
    'formula_links_formula_link_id_seq',
    'formulas_formula_id_seq',
    'formula_types_formula_type_id_seq',
    'component_links_component_link_id_seq',
    'component_link_types_component_link_type_id_seq',
    'components_component_id_seq',
    'component_types_component_type_id_seq',
    'views_view_id_seq',
    'view_types_view_type_id_seq',
    'verbs_verb_id_seq',
    'triples_triple_id_seq',
    'words_word_id_seq',
    'phrase_types_phrase_type_id_seq',
    'sources_source_id_seq',
    'source_types_source_type_id_seq',
    'refs_ref_id_seq',
    'ref_types_ref_type_id_seq',
    'change_links_change_link_id_seq',
    'changes_change_id_seq',
    'change_actions_change_action_id_seq',
    'change_fields_change_field_id_seq',
    'change_tables_change_table_id_seq',
    'config_config_id_seq',
    'job_types_job_type_id_seq',
    'jobs_job_id_seq',
    'sys_log_status_sys_log_status_id_seq',
    'sys_log_functions_sys_log_function_id_seq',
    'share_types_share_type_id_seq',
    'protection_types_protection_type_id_seq',
    'users_user_id_seq',
    'user_profiles_user_profile_id_seq'
];

// TODO Prio 2 move to const/def class?
const DB_TABLE_LIST = [
    'config',
    'sys_log_types',
    'sys_log',
    'sys_log_status',
    'sys_log_functions',
    'system_times',
    'system_time_types',
    'job_times',
    'jobs',
    'job_types',
    'user_official_types',
    'ip_ranges',
    'sessions',
    'changes',
    'changes_norm',
    'changes_big',
    'change_values_norm',
    'change_values_prime',
    'change_values_big',
    'change_values_time_norm',
    'change_values_time_prime',
    'change_values_time_big',
    'change_values_text_prime',
    'change_values_text_norm',
    'change_values_text_big',
    'change_values_geo_norm',
    'change_values_geo_prime',
    'change_values_geo_big',
    'change_fields',
    'change_links',
    'change_actions',
    'change_tables',
    'protection_types',
    'share_types',
    'language_forms',
    'user_words',
    'words',
    'user_triples',
    'phrase_tables',
    'pods',
    'pod_types',
    'pod_status',
    'triples',
    'phrase_types',
    'verbs',
    'phrase_table_status',
    'groups',
    'user_groups',
    'groups_prime',
    'user_groups_prime',
    'groups_big',
    'user_groups_big',
    'user_sources',
    'user_refs',
    'refs',
    'ref_types',
    'values_standard_prime',
    'values_standard',
    'values',
    'user_values',
    'values_prime',
    'user_values_prime',
    'values_big',
    'user_values_big',
    'values_text_standard_prime',
    'values_text_standard',
    'values_text',
    'user_values_text',
    'values_text_prime',
    'user_values_text_prime',
    'values_text_big',
    'user_values_text_big',
    'values_time_standard_prime',
    'values_time_standard',
    'values_time',
    'user_values_time',
    'values_time_prime',
    'user_values_time_prime',
    'values_time_big',
    'user_values_time_big',
    'values_geo_standard_prime',
    'values_geo_standard',
    'values_geo',
    'user_values_geo',
    'values_geo_prime',
    'user_values_geo_prime',
    'values_geo_big',
    'user_values_geo_big',
    'sources',
    'source_types',
    'user_values_time_series',
    'value_time_series_prime',
    'user_value_time_series_prime',
    'value_ts_data',
    'values_time_series',
    'elements',
    'element_types',
    'user_formulas',
    'user_formula_links',
    'formula_link_types',
    'formula_links',
    'results_standard_prime',
    'results_standard_main',
    'results_standard',
    'results',
    'user_results',
    'results_prime',
    'user_results_prime',
    'results_main',
    'user_results_main',
    'results_big',
    'user_results_big',
    'results_text_standard_prime',
    'results_text_standard_main',
    'results_text_standard',
    'results_text',
    'user_results_text',
    'results_text_prime',
    'user_results_text_prime',
    'results_text_main',
    'user_results_text_main',
    'results_text_big',
    'user_results_text_big',
    'results_time_standard_prime',
    'results_time_standard_main',
    'results_time_standard',
    'results_time',
    'user_results_time',
    'results_time_prime',
    'user_results_time_prime',
    'results_time_main',
    'user_results_time_main',
    'results_time_big',
    'user_results_time_big',
    'results_geo_standard_prime',
    'results_geo_standard_main',
    'results_geo_standard',
    'results_geo',
    'user_results_geo',
    'results_geo_prime',
    'user_results_geo_prime',
    'results_geo_main',
    'user_results_geo_main',
    'results_geo_big',
    'user_results_geo_big',
    'user_views',
    'languages',
    'component_link_types',
    'user_components',
    'user_component_links',
    'component_links',
    'position_types',
    'components',
    'formulas',
    'formula_types',
    'views',
    'users',
    'user_types',
    'user_profiles',
    'view_types',
    'view_styles',
    'component_types',
    'view_link_types',
    'term_views',
    'user_term_views',
    'value_formula_links',
    'value_time_series',
    'user_value_time_series',
    'values_time_series_prime',
    'user_values_time_series_prime',
    'values_time_series_big',
    'user_values_time_series_big',
    'results_time_series',
    'user_results_time_series',
    'results_time_series_prime',
    'user_results_time_series_prime',
    'results_time_series_big',
    'user_results_time_series_big'
];


/**
 * should be called from all code that can be accessed by an url
 * return null if the db connection fails or the db is not compatible
 * TODO create a separate class for starting the backend and frontend
 *
 * @param string $code_name the place that is displayed to the user e.g. add word
 * @param string $style the display style used to show the place
 * @param bool $echo_header if true start with a html header
 * @param bool $echo_env if true log the environment
 * @return sql_db the open database connection
 */
function prg_start(
    string $code_name,
    string $style = "",
    bool $echo_header = true,
    bool $echo_env = false
): sql_db
{
    global $sys_time_start, $sys_script, $errors;
    global $sys_times;

    // TODO check if cookies are actually needed
    // resume session (based on cookies)
    session_start();

    /*
    require __DIR__ . '/vendor/autoload.php';
    // Looking for .env at the root directory
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    */

    // check if environment is loaded
    $env = getenv(ENVIRONMENT);
    if (!$env) {
        log_warning('no environment found using fallback values');
    } else {
        log_info('environment ' . getenv(ENVIRONMENT));
    }

    $sys_time_start = time();
    $sys_times = new system_time_list();
    $sys_times->switch(system_time_type::DEFAULT);
    $sys_script = $code_name;
    $errors = 0;

    log_debug($code_name . ': session_start');

    // html header
    if ($echo_header) {
        $html = new html_base();
        echo $html->header("", $style);
    }

    // log environment
    if ($echo_env) {
        $lib = new library();
        echo $lib->env_to_log() . "\n";
        phpinfo(INFO_GENERAL);
    }

    return prg_restart($code_name);
}

/**
 * open the database connection and load the base cache
 * @param string $code_name the place that is displayed to the user e.g. add word
 * @return sql_db the open database connection
 */
function prg_restart(string $code_name): sql_db
{

    global $db_con;
    global $cfg;
    global $mtr;

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    $sc = new sql_creator();
    $sc->set_db_type($db_con->db_type);
    $db_con->open();
    if (!$db_con->is_open()) {
        log_debug($code_name . ': start db setup');
        if ($db_con->setup()) {
            $db_con->open();
            if (!$db_con->is_open()) {
                log_fatal('Cannot connect to database', 'prg_restart');
            }
        }
    } else {
        log_debug($code_name . ': db open');

        // check the system setup
        $db_chk = new db_check();
        $usr_msg = $db_chk->db_check($db_con);
        if (!$usr_msg->is_ok()) {
            echo '\n';
            echo $usr_msg->all_message_text();
            $db_con->close();
            $db_con = null;
        }

        // create a virtual one-time system user to load the system users
        $usr_sys = new user();
        $usr_sys->set_id(users::SYSTEM_ID);
        $usr_sys->name = users::SYSTEM_NAME;

        // load system configuration
        // TODO cache the system config json and detect
        $cfg = new config_numbers($usr_sys);
        $cfg->load_cfg($usr_sys);
        $mtr = new Translator($cfg->language());

        // preload all types from the database
        $sys_typ_lst = new type_lists();
        $sys_typ_lst->load($db_con, $usr_sys);

        $log = new change_log($usr_sys);
        $db_changed = $log->create_log_references($db_con);

        // reload the type list if needed and trigger an update in the frontend
        // even tough the update of the preloaded list should already be done by the single adds
        if ($db_changed) {
            $sys_typ_lst->load($db_con, $usr_sys);
        }

    }
    return $db_con;
}

function prg_start_api($code_name): sql_db
{
    global $sys_time_start, $sys_script, $usr_pro_cac;
    global $sys_times;

    log_debug($code_name . ' ..');

    $sys_time_start = time();
    $sys_times = new system_time_list();
    $sys_script = $code_name;

    // resume session (based on cookies)
    session_start();

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    $db_con->open();
    log_debug($code_name . ' ... database link open');

    return $db_con;
}

/**
 *
 * @param $code_name
 * @return sql_db
 */
function prg_start_system($code_name): sql_db
{
    global $sys_time_start, $sys_script, $usr_pro_cac;
    global $sys_times;

    log_debug($code_name . ' ..');

    $sys_time_start = time();
    $sys_times = new system_time_list();
    $sys_script = $code_name;

    // resume session (based on cookies)
    session_start();

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    $db_con->open();
    log_debug($code_name . ' ... database link open');

    // load user profiles
    $usr_pro_cac = new user_profile_list();
    $lib = new library();
    $tbl_name = $lib->class_to_name(user_profile::class);
    if ($db_con->has_table($tbl_name)) {
        $usr_pro_cac->load($db_con);
    } else {
        $usr_pro_cac->load_dummy();
    }

    return $db_con;
}

/**
 * write the execution time to the database if it is long
 */
function prg_end_write_time($db_con): void
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;
    global $sys_times;

    $time_report = $sys_times->report();
    $sys_time_end = time();
    if ($sys_time_end > $sys_time_limit) {
        $db_con->usr_id = users::SYSTEM_ID;
        $db_con->set_class(system_time_type::class);
        $sys_script_id = $db_con->get_id($sys_script);
        if ($sys_script_id <= 0) {
            $sys_script_id = $db_con->add_id($sys_script);
        }
        $start_time_sql = date("Y-m-d H:i:s", $sys_time_start);
        $end_time_sql = date("Y-m-d H:i:s", $sys_time_end);
        $interval = $sys_time_end - $sys_time_start;
        $milliseconds = $interval;

        //$db_con->insert();
        if (in_array(rest_ctrl::REQUEST_URI, $_SERVER)) {
            $calling_uri = $_SERVER[rest_ctrl::REQUEST_URI];
        } else {
            $calling_uri = 'localhost';
        }
        $sql = "INSERT INTO system_times (start_time, system_time_type_id, end_time, milliseconds) VALUES ('" . $start_time_sql . "'," . $sys_script_id . ",'" . $end_time_sql . "', " . $milliseconds . ");";
        $db_con->exe($sql);
    }

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);
}

function prg_end($db_con, $echo_header = true): void
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    if ($echo_header) {
        $html = new html_base();
        echo $html->footer();
    }

    prg_end_write_time($db_con);

    // Free result test
    //mysqli_free_result($result);

    // Closing connection
    $db_con->close();

    log_debug(' ... database link closed');
}

// special page closing only for the about page
function prg_end_about($link): void
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
function prg_end_api($link): void
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
    $result = file_get_contents(paths::RES . $resource_path);
    if ($result === false) {
        $result = 'Cannot get file from ' . paths::RES . $resource_path;
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

