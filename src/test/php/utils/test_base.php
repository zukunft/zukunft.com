<?php

/*

    test_base.php - for internal code consistency TESTing the BASE functions and definitions
    -------------

    used functions
    ----

    test_exe_time    - show the execution time for the last test and create a warning if it took too long
    test_dsp - simply to display the function test result
    test_show_db_id  - to get a database id because this may differ from instance to instance

    the extension of the test classes

    test_base    - the basic test elements that are used everywhere
    test_new_obj - to create the objects used for testing
    test_api     - the test function for the api
    testing      - adding the cleanup function to have a useful and complete test set

    do sudo apt-get install php-curl


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

// TODO move the names and values for testing to the single objects and check that they cannot be used by an user
// TODO add checks that all id (name or link) changing return the correct error message if the new id already exists
// TODO build a cascading test classes and split the classes to sections less than 1000 lines of code

namespace test;

include_once MODEL_USER_PATH . 'user.php';

use cfg\config;
use controller\controller;
use html\html_base;
use model\combine_object;
use model\db_object;
use model\formula;
use model\library;
use model\phrase_list;
use model\ref;
use model\sandbox;
use model\source;
use model\sql_db;
use model\sql_par;
use model\triple;
use model\triple_list;
use model\trm_ids;
use model\user;
use model\value;
use model\value_list;
use model\verb;
use model\view;
use model\word;
use model\word_list;
use user_dsp_old;

const HOST_TESTING = 'http://localhost/';

global $debug;
global $root_path;

//const ROOT_PATH = __DIR__;

if ($root_path == '') {
    $root_path = '../';
}

// set the paths of the program code
$path_test = $root_path . 'src/test/php/';     // the test base path
$path_utils = $path_test . 'utils/';           // for the general tests and test setup
$path_unit = $path_test . 'unit/';             // for unit tests
$path_unit_db = $path_test . 'unit_db/';       // for the unit tests with database real only
$path_unit_dsp = $path_unit . 'html/';         // for the unit tests that create HTML code
$path_unit_dsp_old = $path_test . 'unit_display/'; // for the unit tests that create HTML code
$path_unit_ui = $path_test . 'unit_ui/';       // for the unit tests that create JSON messages for the frontend
$path_unit_save = $path_test . 'unit_save/';   // for the unit tests that save to database (and cleanup the test data after completion)
$path_it = $path_test . 'integration/';        // for integration tests
$path_dev = $path_test . 'dev/';               // for test still in development

include_once $root_path . 'src/main/php/service/config.php';

// load the other test utility modules (beside this base configuration module)
include_once $path_utils . 'create_test_objects.php';
include_once $path_utils . 'test_system.php';
include_once $path_utils . 'test_api.php';
include_once $path_utils . 'test_db_link.php';
include_once $path_utils . 'test_user.php';
include_once $path_utils . 'test_user_sandbox.php';
include_once $path_utils . 'test_cleanup.php';

// load the unit testing modules
include_once $path_unit . 'test_unit.php';
include_once $path_unit . 'test_lib.php';
include_once $path_unit . 'system.php';
include_once $path_unit . 'user.php';
include_once $path_unit . 'sandbox.php';
include_once $path_unit . 'word.php';
include_once $path_unit . 'word_list.php';
include_once $path_unit . 'triple.php';
include_once $path_unit . 'triple_list.php';
include_once $path_unit . 'phrase.php';
include_once $path_unit . 'phrase_list.php';
include_once $path_unit . 'phrase_group.php';
include_once $path_unit . 'term.php';
include_once $path_unit . 'term_list.php';
include_once $path_unit . 'value.php';
include_once $path_unit . 'value_phrase_link.php';
include_once $path_unit . 'value_list.php';
include_once $path_unit . 'formula.php';
include_once $path_unit . 'formula_list.php';
include_once $path_unit . 'formula_link.php';
include_once $path_unit . 'result.php';
include_once $path_unit . 'result_list.php';
include_once $path_unit . 'formula_element.php';
include_once $path_unit . 'figure.php';
include_once $path_unit . 'figure_list.php';
include_once $path_unit . 'expression.php';
include_once $path_unit . 'view.php';
include_once $path_unit . 'view_list.php';
include_once $path_unit . 'view_component.php';
include_once $path_unit . 'view_component_link.php';
include_once $path_unit . 'verb.php';
include_once $path_unit . 'ref.php';
include_once $path_unit . 'language.php';
include_once $path_unit . 'batch_job.php';
include_once $path_unit . 'change_log.php';
include_once $path_unit . 'system_log.php';

// load the testing functions for creating HTML code
include_once $path_unit . 'html.php';
include_once $path_unit_dsp . 'test_display.php';
include_once $path_unit_dsp . 'change_log.php';
include_once $path_unit_dsp . 'type_lists.php';
include_once $path_unit_dsp . 'user.php';
include_once $path_unit_dsp . 'word.php';
include_once $path_unit_dsp . 'word_list.php';
include_once $path_unit_dsp . 'verb.php';
include_once $path_unit_dsp . 'triple.php';
//include_once $path_unit_dsp . 'phrase.php';
include_once $path_unit_dsp . 'phrase_list.php';
//include_once $path_unit_dsp . 'phrase_group.php';
//include_once $path_unit_dsp . 'term.php';
//include_once $path_unit_dsp . 'term_list.php';
//include_once $path_unit_dsp . 'value.php';
include_once $path_unit_dsp . 'value_list.php';
//include_once $path_unit_dsp . 'formula.php';
//include_once $path_unit_dsp . 'formula_list.php';
//include_once $path_unit_dsp . 'result.php';
//include_once $path_unit_dsp . 'result_list.php';
include_once $path_unit_dsp . 'figure.php';
//include_once $path_unit_dsp . 'figure_list.php';
//include_once $path_unit_dsp . 'view.php';
//include_once $path_unit_dsp . 'view_list.php';
include_once $path_unit_dsp . 'component.php';
//include_once $path_unit_dsp . 'component_list.php';
//include_once $path_unit_dsp . 'source.php';
//include_once $path_unit_dsp . 'reference.php';
//include_once $path_unit_dsp . 'language.php';
//include_once $path_unit_dsp . 'change_log.php';
//include_once $path_unit_dsp . 'system_log.php';
//include_once $path_unit_dsp . 'batch_job.php';


// load the unit testing modules with database read only
include_once $path_unit_db . 'all.php';
include_once $path_unit_db . 'system.php';
include_once $path_unit_db . 'sql_db.php';
include_once $path_unit_db . 'user.php';
include_once $path_unit_db . 'batch_job.php';
include_once $path_unit_db . 'change_log.php';
include_once $path_unit_db . 'system_log.php';
include_once $path_unit_db . 'word.php';
include_once $path_unit_db . 'word_list.php';
include_once $path_unit_db . 'verb.php';
include_once $path_unit_db . 'phrase.php';
include_once $path_unit_db . 'phrase_group.php';
include_once $path_unit_db . 'term.php';
include_once $path_unit_db . 'term_list.php';
include_once $path_unit_db . 'value.php';
include_once $path_unit_db . 'formula.php';
include_once $path_unit_db . 'formula_list.php';
include_once $path_unit_db . 'expression.php';
include_once $path_unit_db . 'view.php';
include_once $path_unit_db . 'ref.php';
include_once $path_unit_db . 'share.php';
include_once $path_unit_db . 'protection.php';
include_once $path_unit_db . 'language.php';


// load the testing functions for creating JSON messages for the frontend code
include_once $path_unit_ui . 'test_formula_ui.php';
include_once $path_unit_ui . 'test_word_ui.php';
include_once $path_unit_ui . 'value_test_ui.php';

// load the testing functions that save data to the database
include_once $path_unit_save . 'test_math.php';
include_once $path_unit_save . 'test_word.php';
include_once $path_unit_save . 'test_word_display.php';
include_once $path_unit_save . 'test_word_list.php';
include_once $path_unit_save . 'test_triple.php';
include_once $path_unit_save . 'phrase_test.php';
include_once $path_unit_save . 'phrase_list_test.php';
include_once $path_unit_save . 'phrase_group_test.php';
include_once $path_unit_save . 'phrase_group_list_test.php';
include_once $path_unit_save . 'ref_test.php';
include_once $path_unit_save . 'test_graph.php';
include_once $path_unit_save . 'test_verb.php';
include_once $path_unit_save . 'test_term.php';
include_once $path_unit_save . 'value_test.php';
include_once $path_unit_save . 'test_source.php';
include_once $path_unit_save . 'test_expression.php';
include_once $path_unit_save . 'test_formula.php';
include_once $path_unit_save . 'test_formula_link.php';
include_once $path_unit_save . 'test_formula_trigger.php';
include_once $path_unit_save . 'test_result.php';
include_once $path_unit_save . 'test_formula_element.php';
include_once $path_unit_save . 'test_formula_element_group.php';
include_once $path_unit_save . 'test_batch.php';
include_once $path_unit_save . 'test_view.php';
include_once $path_unit_save . 'test_view_component.php';
include_once $path_unit_save . 'test_view_component_link.php';
include_once $path_unit_save . 'test_value.php';

// load the integration test functions
include_once $path_it . 'test_import.php';
include_once $path_it . 'test_export.php';

// load the test functions still in development
include_once $path_dev . 'test_legacy.php';

// TODO to be dismissed
include_once WEB_USER_PATH . 'user_display_old.php';

// the fixed system user used for testing
const TEST_USER_ID = "2";
const TEST_USER_DESCRIPTION = "standard user view for all users";
const TEST_USER_IP = "66.249.64.95"; // used to check the blocking of an IP address

/*
Setting that should be moved to the system config table
*/

// switch for the email testing
const TEST_EMAIL = FALSE; // if set to true an email will be sent in case of errors and once a day an "everything fine" email is send

// TODO move the test names to the single objects and check for reserved names to avoid conflicts
// the basic test record for doing the pre check
// the word "Company" is assumed to have the ID 1
const TEST_WORD = "Company";

// some test words used for testing
const TW_ABB = "ABB";
const TW_VESTAS = "Vestas";
const TW_SALES = "Sales";
const TW_CHF = "CHF";
const TW_YEAR = "Year";
const TW_2013 = "2013";
const TW_2014 = "2014";
const TW_2017 = "2017";
const TW_MIO = "million";
const TW_CF = "cash flow statement";
const TW_TAX = "Income taxes";

// some test phrases used for testing
const TP_ABB = "ABB (Company)";
const TP_FOLLOW = "2014 is follower of 2013";
const TP_TAXES = "Income taxes is part of cash flow statement";

// some formula parameter used for testing
const TF_SECTOR = "sectorweight";

// some numbers used to test the program
const TV_TEST_SALES_INCREASE_2017_FORMATTED = '90.03 %';
const TV_NESN_SALES_2016_FORMATTED = '89\'469';

// some source used to test the program
const TS_IPCC_AR6_SYNTHESIS = 'IPCC AR6 Synthesis Report: Climate Change 2022';
const TS_IPCC_AR6_SYNTHESIS_URL = 'https://www.ipcc.ch/report/sixth-assessment-report-cycle/';
const TS_NESN_2016_NAME = 'Nestlé Financial Statement 2016';


// max time expected for each function execution
const TIMEOUT_LIMIT = 0.03; // time limit for normal functions
const TIMEOUT_LIMIT_PAGE = 0.1;  // time limit for complete webpage
const TIMEOUT_LIMIT_PAGE_SEMI = 0.6;  // time limit for complete webpage
const TIMEOUT_LIMIT_PAGE_LONG = 1.2;  // time limit for complete webpage
const TIMEOUT_LIMIT_DB = 0.2;  // time limit for database modification functions
const TIMEOUT_LIMIT_DB_MULTI = 0.9;  // time limit for many database modifications
const TIMEOUT_LIMIT_LONG = 3;    // time limit for complex functions
const TIMEOUT_LIMIT_IMPORT = 12;    // time limit for complex import tests in seconds


// ---------------------------
// function to support testing
// ---------------------------


/**
 * highlight the first difference between two string
 * @param string|null $from the expected text
 * @param string|null $to the text to compare
 * @return string the first char that differs or an empty string
 */
function str_diff(?string $from, ?string $to): string
{
    $result = '';

    if ($from != null and $to != null) {
        if ($from != $to) {
            $f = str_split($from);
            $t = str_split($to);

            // add message if just one string is shorter
            if (count($f) < count($t)) {
                $result = 'pos ' . count($t) . ' less: ' . substr($to, count($f), count($t) - count($f));
            } elseif (count($t) < count($f)) {
                $result = 'pos ' . count($f) . ' additional: ' . substr($from, count($t), count($f) - count($t));
            }

            $i = 0;
            while ($i < count($f) and $i < count($t) and $result == '') {
                if ($f[$i] != $t[$i]) {
                    $result = 'pos ' . $i . ': ' . $f[$i] . ' (' . ord($f[$i]) . ') != ' . $t[$i] . ' (' . ord($t[$i]) . ')';
                    $result .= ', near ' . substr($from, $i - 10, 20);
                }
                $i++;
            }
        }
    } elseif ($from == null and $to != null) {
        $result = 'less: ' . $to;
    } elseif ($from != null and $to == null) {
        $result = 'additional: ' . $from;
    }


    return $result;
}

/*
 *   testing class - to check the words, values and formulas that should always be in the system
 *   -------------
*/

class test_base
{
    // the url which should be used for testing (maybe later https://test.zukunft.com/)
    const URL = 'https://zukunft.com/';

    const FILE_EXT = '.sql';
    const FILE_MYSQL = '_mysql';

    public user $usr1; // the main user for testing
    public user $usr2; // a second testing user e.g. to test the user sandbox

    private float $start_time; // time when all tests have started
    private float $exe_start_time; // time when the single test has started (end the end time of all tests)

    // the counter of the error for the summery
    private int $error_counter;
    private int $timeout_counter;
    private int $total_tests;

    public string $name;
    public string $resource_path;

    private int $seq_nbr;

    function __construct()
    {
        // init the times to be able to detect potential timeouts
        $this->start_time = microtime(true);
        $this->exe_start_time = $this->start_time;

        // reset the error counters
        $this->error_counter = 0;
        $this->timeout_counter = 0;
        $this->total_tests = 0;

        $this->seq_nbr = 0;

        $this->name = '';
        $this->resource_path = '';
    }

    function set_users(): void
    {

        // create the system test user to simulate the user sandbox
        // e.g. a value owned by the first user cannot be adjusted by the second user instead a user specific value is created
        // instead a user specific value is created
        // for testing $usr is the user who has started the test ans $usr1 and $usr2 are the users used for simulation
        $this->usr1 = new user_dsp_old;
        $this->usr1->load_by_name(user::SYSTEM_TEST_NAME);

        $this->usr2 = new user_dsp_old;
        $this->usr2->load_by_name(user::SYSTEM_NAME_TEST_PARTNER);

    }




    /*
     * Display functions
     */

    /**
     * the HTML code to display the header text
     */
    function header(string $header_text): void
    {
        echo '<br><br><h2>' . $header_text . '</h2><br>';
    }

    /**
     * the HTML code to display the subheader text
     */
    function subheader(string $header_text): void
    {
        echo '<br><h3>' . $header_text . '</h3><br>';
    }

    /**
     * @return string the content of the test resource file
     */
    function file(string $test_resource_path): string
    {
        $result = file_get_contents(PATH_TEST_FILES . $test_resource_path);
        if ($result === false) {
            $result = 'Cannot get file from ' . PATH_TEST_FILES . $test_resource_path;
        }
        return $result;
    }

    /**
     * check if the test result is as expected and display the test result to an admin user
     * TODO replace all dsp calls with this but the
     *
     * @param string $msg (unique) description of the test
     * @param string|array|null $result the actual result
     * @param string|array $target the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert(
        string            $msg,
        string|array|null $result,
        string|array      $target,
        float             $exe_max_time = TIMEOUT_LIMIT,
        string            $comment = '',
        string            $test_type = ''): bool
    {
        // the result should never be null, but if, check it here not on each call
        if ($result == null) {
            $result = '';
            log_warning('result of test ' . $msg . ' has been null');
        }
        return $this->dsp(', ' . $msg, $target, $result, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the result text contains at least the target text
     *
     * @param string $msg (unique) description of the test
     * @param string $result the actual result
     * @param string $target the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_text_contains(
        string $msg,
        string $result,
        string $target,
        float  $exe_max_time = TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {
        if (strpos($result, $target) !== null) {
            $result = $target;
        }
        return $this->dsp(', ' . $msg, $target, $result, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the test results contains at least all expected results
     *
     * @param string $msg (unique) description of the test
     * @param array $result the actual result
     * @param array $target the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_contains(
        string $msg,
        array  $result,
        array  $target,
        float  $exe_max_time = TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {
        $result = array_intersect($result, $target);
        return $this->dsp(', ' . $msg, $target, $result, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the test results contains at least all expected results
     *
     * @param string $msg (unique) description of the test
     * @param array $result the actual result
     * @param array $target the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_contains_not(
        string $msg,
        array  $result,
        array  $target,
        float  $exe_max_time = TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {
        $result = array_diff($target, $result);
        return $this->dsp(', ' . $msg, $target, $result, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the frontend API object can be created
     * and if the export based recreation of the backend object result to the similar object
     *
     * @param object $usr_obj the object which frontend API functions should be tested
     * @return bool true if the reloaded backend object has no relevant differences
     */
    function assert_api_obj(object $usr_obj): bool
    {
        $lib = new library();
        $original_json = json_decode(json_encode($usr_obj->export_obj(false)), true);
        $recreated_json = '';
        $api_obj = $usr_obj->api_obj();
        if ($api_obj->id() == $usr_obj->id()) {
            $db_obj = $api_obj->db_obj($usr_obj->user(), get_class($api_obj));
            $db_obj->load_by_id($usr_obj->id(), get_class($usr_obj));
            $recreated_json = json_decode(json_encode($db_obj->export_obj(false)), true);
        }
        $result = $lib->json_is_similar($original_json, $recreated_json);
        // TODO remove, for faster debugging only
        $json_in_txt = json_encode($original_json);
        $json_ex_txt = json_encode($recreated_json);
        return $this->assert($this->name . 'API check', $result, true);
    }

    /**
     * check if the
     *
     * @param object $usr_obj the api object used a a base for the message
     * @return bool true if the generated message matches in relevant parts the expected message
     */
    function assert_api_json_msg(object $api_obj): bool
    {
        $json_api_msg = json_encode($api_obj);
        return true;
    }

    /**
     * check if the REST GET call returns the expected export JSON message
     *
     * @param string $test_name the name of the object to test
     * @param string $fld the field name to select the export
     * @param int $id the database id of the db row that should be used for testing
     * @return bool true if the json has no relevant differences
     */
    function assert_api_get_json(string $test_name, string $fld = '', int $id = 1): bool
    {
        $lib = new library();
        $test_name = $lib->class_to_name($test_name);
        $url = HOST_TESTING . controller::URL_API_PATH . 'json';
        $data = array($fld => $id);
        $actual = json_decode($this->api_call("GET", $url, $data), true);
        // TODO remove next line (added for faster debugging only)
        $json_actual = json_encode($actual);
        $expected_text = $this->file('api/json/' . $test_name . '.json');
        $expected = json_decode($expected_text, true);
        return $this->assert($test_name . ' API GET', $lib->json_is_similar($actual, $expected), true);
    }


    /**
     * check if the REST curl calls are possible
     *
     * @param object $usr_obj the object to enrich which REST curl calls should be tested
     * @return bool true if the reloaded backend object has no relevant differences
     */
    function assert_rest(object $usr_obj): bool
    {
        $lib = new library();
        $obj_name = get_class($usr_obj);
        $url_read = 'api/' . $obj_name . '/index.php';
        $original_json = json_decode(json_encode($usr_obj->$usr_obj()), true);
        $recreated_json = '';
        $api_obj = $usr_obj->api_obj();
        if ($api_obj->id == $usr_obj->id) {
            $db_obj = $api_obj->db_obj($usr_obj->usr, get_class($api_obj));
            $recreated_json = json_decode(json_encode($db_obj->export_obj(false)), true);
        }
        $result = $lib->json_is_similar($original_json, $recreated_json);
        return $this->assert($this->name . 'REST check', $result, true);
    }

    /**
     * test a system view with a sample user object
     *
     * @param string $dsp_code_id the code id of the view that should be tested
     * @return bool true if the generated view matches the expected
     */
    function assert_view(string $dsp_code_id): bool
    {
        global $usr;

        $filename = 'views/' . $dsp_code_id;

        $dsp = new view($usr);
        $dsp->load_by_code_id($dsp_code_id);

        $actual = $dsp->dsp_obj()->dsp_system_view();
        return $this->assert_html($this->name . ' view ' . $dsp_code_id, $actual, $filename);
    }

    /**
     * check if an object json file can be recreated by importing the object and recreating the json with the export function
     *
     * @param object $usr_obj the object which json im- and export functions should be tested
     * @param string $json_file_name the resource path name to the json sample file
     * @return bool true if the json has no relevant differences
     */
    function assert_json(object $usr_obj, string $json_file_name): bool
    {
        $lib = new library();
        $file_text = file_get_contents(PATH_TEST_FILES . $json_file_name);
        $json_in = json_decode($file_text, true);
        $usr_obj->import_obj($json_in, false);
        $this->set_id_for_unit_tests($usr_obj);
        $json_ex = json_decode(json_encode($usr_obj->export_obj(false)), true);
        $result = $lib->json_is_similar($json_in, $json_ex);
        // TODO remove, for faster debugging only
        $json_in_txt = json_encode($json_in);
        $json_ex_txt = json_encode($json_ex);
        return $this->assert($this->name . 'import check name', $result, true);
    }

    /**
     * check if the created html matches a defined html file
     *
     * @param string $test_name the description of the test
     * @param string $body the body of a html page
     * @param string $filename the filename of the expected html page
     * @return bool true if the html has no relevant differences
     */
    function assert_html(string $test_name, string $body, string $filename): bool
    {
        $lib = new library();

        $actual = $this->html_page($body);
        $expected = $this->file('web/html/' . $filename . '.html');
        return $this->assert($test_name, $lib->trim_html($actual), $lib->trim_html($expected));
    }

    /**
     * similar to assert_load_sql but for the load_sql_obj_vars that
     * TODO should be replaced by assert_load_sql_id, assert_load_sql_name, assert_load_sql_all, ...
     *
     * check the object load SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param string $db_type to define the database type if it does not match the class
     * @return bool true if all tests are fine
     */
    function assert_load_sql_obj_vars(sql_db $db_con, object $usr_obj, string $db_type = ''): bool
    {
        if ($db_type == '') {
            $db_type = get_class($usr_obj);
        }

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_obj_vars($db_con, $db_type);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_obj_vars($db_con, $db_type);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but for an id
     * check the object load by id list SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_id(sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_id($db_con, 1, $usr_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_id($db_con, 1, $usr_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but for an id list
     * check the object load by id list SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_ids(sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_ids($db_con, array(1, 2));
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_ids($db_con, array(1, 2));
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql_ids but for a term id list
     * check the object load by id list SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_trm_ids(sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_ids($db_con, new trm_ids(array()));
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_ids($db_con, new trm_ids(array()));
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but select one row based on the name
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_name(sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_name($db_con, 'System test', $usr_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_name($db_con, 'System test', $usr_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but select one row based on the code id
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     * @return bool true if all tests are fine
     */
    function assert_load_sql_code_id(sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_code_id($db_con, 'System test', $usr_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_code_id($db_con, 'System test', $usr_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but select one row based on the linked components
     * check the SQL statements for user object load by linked objects for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_link(sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_link($db_con, 1, 0, 3, $usr_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_link($db_con, 1, 0, 3, $usr_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the object load SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param string $db_type to define the database type if it does not match the class
     * @return bool true if all tests are fine
     */
    function assert_load_sql_all(sql_db $db_con, object $usr_obj, string $db_type = ''): bool
    {
        $lib = new library();
        if ($db_type == '') {
            $db_type = get_class($usr_obj);
            $db_type = $lib->class_to_name($db_type);
        }

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_all($db_con, $db_type);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_all($db_con, $db_type);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql but for a name pattern
     * check the object load by id list SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_like(sql_db $db_con, object $usr_obj,): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_like($db_con, '');
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_like($db_con, '');
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements for loading a list of objects in all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $lst_obj the list object e.g. a result list
     * @param object $select_obj the named user sandbox or phrase group object used for the selection e.g. a formula
     * @param object|null $select_obj2 a second named object used for selection e.g. a time phrase
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if all tests are fine
     */
    function assert_load_list_sql(sql_db $db_con, object $lst_obj, object $select_obj, bool $by_source = false): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst_obj->load_sql($db_con, $select_obj, $by_source);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst_obj->load_sql($db_con, $select_obj, $by_source);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements for loading a list of objects selected by the type in all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $lst_obj the list object e.g. batch job list
     * @param string $type_code_id the type code id that should be used for the selection
     * @return bool true if all tests are fine
     */
    function assert_load_list_sql_type(sql_db $db_con, object $lst_obj, string $type_code_id): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst_obj->load_sql_by_type($db_con, $type_code_id, $lst_obj::class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst_obj->load_sql_by_type($db_con, $type_code_id, $lst_obj::class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the object load SQL statements to get the default object value for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param sandbox $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_standard_sql(sql_db $db_con, sandbox $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_standard_sql($db_con, get_class($usr_obj));
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_standard_sql($db_con, get_class($usr_obj));
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the object loading by id and name
     *
     * @param sandbox $usr_obj the user sandbox object e.g. a word
     * @param string $name the name
     * @return bool true if all tests are fine
     */
    function assert_load(db_object $usr_obj, string $name): bool
    {
        // check the loading via id and check the name
        $usr_obj->load_by_id(1, $usr_obj::class);
        $result = $this->assert($usr_obj::class . '->load', $usr_obj->name(), $name);

        // ... and check the loading via name and check the id
        if ($result) {
            $usr_obj->reset();
            $usr_obj->load_by_name($name, $usr_obj::class);
            $result = $this->assert($usr_obj::class . '->load', $usr_obj->id(), 1);
        }
        return $result;
    }

    /**
     * check the loading by id and name of a combine object
     *
     * @param combine_object $usr_obj the combine object e.g. a phrase, term or figure
     * @param string $name the name
     * @return bool true if all tests are fine
     */
    function assert_load_combine(combine_object $usr_obj, string $name): bool
    {
        // check the loading via id and check the name
        $usr_obj->load_by_id(1, $usr_obj::class);
        $result = $this->assert($usr_obj::class . '->load', $usr_obj->name(), $name);

        // ... and check the loading via name and check the id
        if ($result) {
            $usr_obj->reset();
            $usr_obj->load_by_name($name, $usr_obj::class);
            $result = $this->assert($usr_obj::class . '->load', $usr_obj->id(), 1);
        }
        return $result;
    }

    /**
     * check the not changed SQL statements of a user sandbox object e.g. word, triple, value or formulas
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param sandbox $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_not_changed_sql(sql_db $db_con, sandbox $usr_obj): bool
    {
        // check the Postgres query syntax
        $usr_obj->owner_id = 0;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->not_changed_sql($db_con);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check with owner
        if ($result) {
            $usr_obj->owner_id = 1;
            $qp = $usr_obj->not_changed_sql($db_con);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }

        // ... and check the MySQL query syntax
        if ($result) {
            $usr_obj->owner_id = 0;
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->not_changed_sql($db_con);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }

        // ... and check with owner
        if ($result) {
            $usr_obj->owner_id = 1;
            $qp = $usr_obj->not_changed_sql($db_con);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }

        return $result;
    }

    /**
     * check the SQL statements to get the user sandbox changes
     * e.g. the value a user has changed of word, triple, value or formulas
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param sandbox $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_user_config_sql(sql_db $db_con, sandbox $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->usr_cfg_sql($db_con);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->usr_cfg_sql($db_con);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }

        return $result;
    }

    /**
     * test the SQL statement creation for a value
     *
     * @param sql_par $qp the query parameters that should be tested
     * @param string $dialect if not Postgres the name of the SQL dialect
     * @return bool true if the test is fine
     */
    function assert_qp(sql_par $qp, string $dialect = ''): bool
    {
        if ($dialect == sql_db::POSTGRES) {
            $file_name_ext = '';
        } elseif ($dialect == sql_db::MYSQL) {
            $file_name_ext = self::FILE_MYSQL;
        } else {
            $file_name_ext = $dialect;
        }
        $file_name = $this->resource_path . $qp->name . $file_name_ext . self::FILE_EXT;
        $expected_sql = $this->file($file_name);
        if ($expected_sql == '') {
            $expected_sql = 'File ' . $file_name . ' with the expected SQL statement is missing.';
        }
        $result = $this->assert_sql(
            $this->name . $qp->name . '_' . $dialect,
            $qp->sql,
            $expected_sql
        );

        // check if the prepared sql name is unique always based on the  Postgres query parameter creation
        if ($dialect == sql_db::POSTGRES) {
            $result = $this->assert_sql_name_unique($qp->name);
        }

        return $result;
    }

    /**
     * test am SQL statement
     *
     * @param string $created the created SQL statement that should be checked
     * @param string $expected the fixed SQL statement that is supposed to be correct
     * @return bool true if the created SQL statement matches the expected SQL statement if the formatting is removed
     */
    function assert_sql(string $name, string $created, string $expected): bool
    {
        $lib = new library();
        return $this->assert($name, $lib->trim_sql($created), $lib->trim_sql($expected));
    }

    /**
     * test am SQL statement
     *
     * @param int $received an integer value that is expected to be greater zero
     * @return bool true if the value is actually greater zero
     */
    function assert_greater_zero(string $name, int $received): bool
    {
        $expected = 0;
        if ($received > 0) {
            $expected = $received;
        }
        return $this->assert($name, $received, $expected);
    }

    /**
     * check if the SQL query name is unique
     * should be called once per query, but not for each SQL dialect
     *
     * @param string $sql_name the SQL query name that is supposed to be unique
     * @return bool true if the name has not been tested before and is therefore expected to be unique
     */
    function assert_sql_name_unique(string $sql_name): bool
    {
        global $sql_names;

        $result = false;
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        return $this->assert('is SQL name ' . $sql_name . ' unique', $result, true);
    }

    /**
     * just report an assert error without additional check
     * @param string $msg
     * @return void
     */
    function assert_fail(string $msg): void
    {
        log_err('ERROR: ' . $msg);
        $this->error_counter++;
        $this->assert_dsp($msg, false);
    }

    /**
     * display the result of one test e.g. if adding a value has been successful
     *
     * @return bool true if the test result is fine
     */
    function dsp(
        string       $msg,
        string|array $target,
        string|array $result,
        float        $exe_max_time = TIMEOUT_LIMIT,
        string       $comment = '',
        string       $test_type = ''): bool
    {

        // init the test result vars
        $test_result = false;
        $txt = '';
        $test_diff = '';
        $lib = new library();

        // do the compare depending on the type
        if (is_array($target) and is_array($result)) {
            sort($target);
            sort($result);
            // in an array each value needs to be the same
            $test_result = true;
            foreach ($target as $key => $value) {
                if (array_key_exists($key, $result)) {
                    if ($value != $result[$key]) {
                        $test_result = false;
                    }
                } else {
                    $lib = new library();
                    log_err('Key ' . $key . ' missing in ' . $lib->dsp_array($result, true));
                }
            }
        } elseif (is_numeric($result) && is_numeric($target)) {
            $result = round($result, 7);
            $target = round($target, 7);
            if ($result == $target) {
                $test_result = true;
            }
        } else {
            if ($result != null) {
                $result = $this->test_remove_color($result);
            }
            if ($result == $target) {
                $test_result = true;
            } else {
                if ($target == '') {
                    log_err('Target is not expected to be empty ' . $result);
                } else {
                    $diff = $lib->str_diff($result, $target);
                    if ($diff == '') {
                        log_err('Unexpected diff ' . $diff);
                        $target = $result;
                    }
                }
            }
        }

        // explain the check
        if (is_array($target)) {
            if ($test_type == 'contains') {
                $msg .= " should contain \"" . $lib->dsp_array($target) . "\"";
            } else {
                $msg .= " should be \"" . $lib->dsp_array($target) . "\"";
            }
        } else {
            if ($test_type == 'contains') {
                $msg .= " should contain \"" . $target . "\"";
            } else {
                $msg .= " should be \"" . $target . "\"";
            }
        }
        if ($result == $target) {
            if ($test_type == 'contains') {
                $msg .= " and it contains ";
            } else {
                $txt .= " and it is ";
            }
        } else {
            if ($test_type == 'contains') {
                $msg .= ", but ";
            } else {
                $msg .= ", but it is ";
            }
        }
        if (is_array($result)) {
            if ($result != null) {
                if (is_array($result[0])) {
                    $msg .= "\"";
                    foreach ($result[0] as $result_item) {
                        if ($result_item <> $result[0]) {
                            $msg .= ",";
                        }
                        $msg .= implode(":", $lib->array_flat($result_item));
                    }
                    $msg .= "\"";
                } else {
                    $msg .= "\"" . $lib->dsp_array($result) . "\"";
                }
            }
        } else {
            $msg .= "\"" . $result . "\"";
            if ($test_diff != '') {
                $msg .= ' ' . $test_diff;
            }
        }
        if ($comment <> '') {
            $msg .= ' (' . $comment . ')';
        }

        return $this->assert_dsp($msg, $test_result, $exe_max_time);
    }

    /**
     * @param string $msg the message that describes the test for the developer
     * @param bool $test_result
     * @param float $exe_max_time
     * @return bool true if the test result is fine
     */
    private function assert_dsp(string $msg, bool $test_result, float $exe_max_time = TIMEOUT_LIMIT): bool
    {
        // calculate the execution time
        $final_msg = '';
        $new_start_time = microtime(true);
        $since_start = $new_start_time - $this->exe_start_time;

        // display the result
        if ($test_result) {
            // check if executed in a reasonable time and if the result is fine
            if ($since_start > $exe_max_time) {
                $final_msg .= '<p style="color:orange">TIMEOUT' . $msg;
                $this->timeout_counter++;
            } else {
                $final_msg .= '<p style="color:green">OK' . $msg;
                $test_result = true;
            }
        } else {
            $final_msg .= '<p style="color:red">Error' . $msg;
            $this->error_counter++;
            // TODO: create a ticket after version 0.1 where hopefully more than one developer is working on the project
        }

        // show the execution time
        $final_msg .= ', took ';
        $final_msg .= round($since_start, 4) . ' seconds';

        // --- and finally display the test result
        $final_msg .= '</p>';
        echo $final_msg;
        echo "\n";
        flush();

        $this->total_tests++;
        $this->exe_start_time = $new_start_time;

        return $test_result;
    }

    /**
     * similar to test_show_result, but the target only needs to be part of the result
     * e.g. "Zurich" is part of the canton word list
     */
    function dsp_contains(
        string $test_text,
        string $target,
        string $result,
        float  $exe_max_time = TIMEOUT_LIMIT,
        string $comment = ''): bool
    {
        if (!str_contains($result, $target) and $result != '' and $target != '') {
            $result = $target . ' not found in ' . $result;
        } else {
            $result = $target;
        }
        return $this->dsp($test_text, $target, $result, $exe_max_time, $comment, 'contains');
    }


    function dsp_web_test(string $url_path, string $must_contain, string $msg, bool $is_connected = true): bool
    {
        $msg_net_off = 'Cannot gat the policy, probably not connected to the internet';
        if ($is_connected) {
            $result = file_get_contents(self::URL . $url_path);
            if ($result === false) {
                $this->dsp_warning($msg_net_off);
                $is_connected = false;
            } else {
                $this->dsp_contains($msg, $must_contain, $result, TIMEOUT_LIMIT_PAGE_SEMI);
            }
        }
        return $is_connected;
    }

    /**
     * @param string $msg the message to display to the person who executes the system
     */
    function dsp_warning(string $msg): void
    {
        echo $msg;
        echo '<br>';
        echo '\n';
    }

    /**
     * remove color setting from the result to reduce confusion by misleading colors
     */
    function test_remove_color(string $result): string
    {
        $result = str_replace('<p style="color:red">', '', $result);
        $result = str_replace('<p class="user_specific">', '', $result);
        return str_replace('</p>', '', $result);
    }

    /**
     * display the test results in HTML format
     */
    function dsp_result_html(): void
    {
        echo '<br>';
        echo '<h2>';
        echo $this->total_tests . ' test cases<br>';
        echo $this->timeout_counter . ' timeouts<br>';
        if ($this->error_counter == 1) {
            echo $this->error_counter . ' error<br>';
        } else {
            echo $this->error_counter . ' errors<br>';
        }
        echo "<br>";
        $since_start = microtime(true) - $this->start_time;
        echo round($since_start, 4) . ' seconds for testing zukunft.com</h2>';
        echo '<br>';
        echo '<br>';
    }

    /**
     * display the test results in pure test format
     */
    function dsp_result(): void
    {

        echo "\n";
        $since_start = microtime(true) - $this->start_time;
        echo round($since_start, 4) . ' seconds for testing zukunft.com';
        echo "\n";
        echo $this->total_tests . ' test cases';
        echo "\n";
        echo $this->timeout_counter . ' timeouts';
        echo "\n";
        echo $this->error_counter . ' errors';
    }

    /**
     * @return int the next sequence number to simulate database auto increase for unit testing
     */
    protected function next_seq_nbr(): int
    {
        $this->seq_nbr++;
        return $this->seq_nbr;
    }

    /**
     * fill the object with dummy ids to enable correct and fast unit tests without db connect
     * @param object $usr_obj
     * @return void
     */
    private function set_id_for_unit_tests(object $usr_obj): void
    {
        // set the id for simple db objects without related objects
        if ($usr_obj::class == word::class
            or $usr_obj::class == triple::class
            or $usr_obj::class == source::class
            or $usr_obj::class == ref::class) {
            if ($usr_obj->id() == 0) {
                $usr_obj->set_id($this->next_seq_nbr());
            }
        } elseif ($usr_obj::class == value::class) {
            $this->set_val_id_for_unit_tests($usr_obj);
        } elseif ($usr_obj::class == formula::class) {
            $this->set_frm_id_for_unit_tests($usr_obj);
        } elseif ($usr_obj::class == word_list::class) {
            foreach ($usr_obj->lst() as $wrd) {
                if ($wrd->id() == 0) {
                    $wrd->set_id($this->next_seq_nbr());
                }
            }
        } elseif ($usr_obj::class == triple_list::class) {
            foreach ($usr_obj->lst() as $trp) {
                if ($trp->id() == 0) {
                    $trp->set_id($this->next_seq_nbr());
                }
            }
        } elseif ($usr_obj::class == phrase_list::class) {
            foreach ($usr_obj->lst() as $phr) {
                if ($phr->id() == 0) {
                    $phr->set_id($this->next_seq_nbr());
                }
            }
        } elseif ($usr_obj::class == value_list::class) {
            foreach ($usr_obj->lst() as $val) {
                $this->set_val_id_for_unit_tests($val);
            }
        } else {
            log_err('set id for unit tests not yet coded for ' . $usr_obj::class . ' object');
        }
    }

    /**
     * only for unit testing: set the id of a value model object
     * @param value $val the value object that
     * @return void nothing because the value object a modified
     */
    private function set_val_id_for_unit_tests(value $val): void
    {
        if ($val->id() == 0) {
            $val->set_id($this->next_seq_nbr());
        }
        if ($val->grp->id() == 0) {
            $val->grp->set_id($this->next_seq_nbr());
        }
        foreach ($val->phr_lst()->lst() as $phr) {
            if ($phr->id() == 0) {
                $phr->obj()->set_id($this->next_seq_nbr());
                if ($phr->obj()::class == word::class) {
                    $phr->set_id($phr->obj()->id());
                } else {
                    $phr->set_id($phr->obj()->id() * -1);
                }
            }
        }
    }

    /**
     * only for unit testing: set the id of a formula model object
     * @param formula $frm the formula object that
     * @return void nothing because the formula object a modified
     */
    private function set_frm_id_for_unit_tests(formula $frm): void
    {
        if ($frm->id() == 0) {
            $frm->set_id($this->next_seq_nbr());
        }
    }

    function api_call(string $method, string $url, array $data): string
    {
        $curl = curl_init();
        $data_json = json_encode($data);


        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                break;
            case "PUT":
                curl_setopt($curl,
                    CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json', 'Content-Length: ' . strlen($data_json)));
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                $url = sprintf("%s?%s", $url, http_build_query($data));
                break;
            default:
                $url = sprintf("%s?%s", $url, http_build_query($data));

        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    private function html_page(string $body): string
    {
        $html = new html_base();
        return $html->header_test('test') . $body . $html->footer();
    }

    function class_without_namespace(string $class_name_with_namespace): string
    {
        $lib = new library();
        return $lib->str_right_of_or_all($class_name_with_namespace, "\\");
    }

}


// -----------------------------------------------
// testing functions to create the main time value
// -----------------------------------------------

function zu_test_time_setup(testing $t): string
{
    global $db_con;

    $cfg = new config();
    $result = '';
    $this_year = intval(date('Y'));
    $prev_year = '';
    $test_years = intval($cfg->get(config::TEST_YEARS, $db_con));
    if ($test_years == '') {
        log_warning('Configuration of test years is missing', 'test_base->zu_test_time_setup');
    } else {
        $start_year = $this_year - $test_years;
        $end_year = $this_year + $test_years;
        for ($year = $start_year; $year <= $end_year; $year++) {
            $this_year = $year;
            $t->test_word(strval($this_year));
            $wrd_lnk = $t->test_triple(TW_YEAR, verb::IS_A, $this_year);
            $result = $wrd_lnk->name();
            if ($prev_year <> '') {
                $t->test_triple($prev_year, verb::FOLLOW, $this_year);
            }
            $prev_year = $this_year;
        }
    }

    return $result;
}
