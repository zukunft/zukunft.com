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

// TODO add checks that all id (name or link) changing return the correct error message if the new id already exists
// TODO build a cascading test classes and split the classes to sections less than 1000 lines of code

namespace test;

include_once SERVICE_PATH . 'config.php';
include_once MODEL_USER_PATH . 'user.php';
include_once WEB_HTML_PATH . 'styles.php';
include_once DB_PATH . 'sql_type.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_ENUM_PATH . 'user_profiles.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'api_type.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once TEST_CONST_PATH . 'files.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\component\component_list;
use cfg\config;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\element\element_list;
use cfg\formula\fig_ids;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\formula\formula_list;
use cfg\group\group;
use cfg\helper\combine_named;
use cfg\helper\combine_object;
use cfg\helper\db_object_seq_id;
use cfg\log\change;
use cfg\log\change_link;
use cfg\phrase\phr_ids;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\term;
use cfg\phrase\trm_ids;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\result\result;
use cfg\result\result_list;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_link;
use cfg\sandbox\sandbox_link_named;
use cfg\sandbox\sandbox_list_named;
use cfg\sandbox\sandbox_multi;
use cfg\sandbox\sandbox_named;
use cfg\sandbox\sandbox_value;
use cfg\user\user;
use cfg\value\value;
use cfg\value\value_base;
use cfg\value\value_list;
use cfg\verb\verb;
use cfg\view\view;
use cfg\view\view_list;
use cfg\view\term_view;
use cfg\word\triple;
use cfg\word\triple_list;
use cfg\word\word;
use cfg\word\word_list;
use Exception;
use html\component\component_exe as component_dsp;
use html\formula\formula as formula_dsp;
use html\helper\data_object as data_object_dsp;
use html\html_base;
use html\log\change_log_named as change_dsp;
use html\ref\ref as ref_dsp;
use html\ref\source as source_dsp;
use html\rest_ctrl;
use html\result\result as result_dsp;
use html\sandbox\db_object as db_object_dsp;
use html\styles;
use html\value\value as value_dsp;
use html\verb\verb as verb_dsp;
use html\view\view as view_dsp;
use html\word\triple as triple_dsp;
use html\word\word as word_dsp;
use shared\api;
use shared\enum\messages as msg_id;
use shared\enum\user_profiles;
use shared\enum\value_types;
use shared\library;
use shared\const\words;
use shared\types\api_type;
use shared\types\verbs;
use const\files as test_files;

// TODO activate
//use html\group\group as group_dsp;

// set all paths of the testing code
const TEST_UNIT_PATH = TEST_PHP_PATH . 'unit' . DIRECTORY_SEPARATOR;               // for unit tests
const TEST_UNIT_READ_PATH = TEST_PHP_PATH . 'unit_read' . DIRECTORY_SEPARATOR;     // for the unit tests with database read only
const TEST_UNIT_DSP_PATH = TEST_UNIT_PATH . 'html' . DIRECTORY_SEPARATOR;           // for the unit tests that create HTML code
const TEST_UNIT_HTML_PATH = TEST_PHP_PATH . 'unit_display' . DIRECTORY_SEPARATOR; // for the unit tests that create HTML code
const TEST_UNIT_UI_PATH = TEST_PHP_PATH . 'unit_ui' . DIRECTORY_SEPARATOR;        // for the unit tests that create JSON messages for the frontend
const TEST_UNIT_WRITE_PATH = TEST_PHP_PATH . 'unit_write' . DIRECTORY_SEPARATOR;  // for the unit tests that save to database (and cleanup the test data after completion)
const TEST_UNIT_INT_PATH = TEST_PHP_PATH . 'integration' . DIRECTORY_SEPARATOR;   // for integration tests
const TEST_DEV_PATH = TEST_PHP_PATH . 'dev' . DIRECTORY_SEPARATOR;                // for test still in development


// load the system config for testing
include_once SERVICE_PATH . 'config.php';

// load the other test utility modules (beside this base configuration module)
include_once TEST_PHP_UTIL_PATH . 'create_test_objects.php';
include_once TEST_PHP_UTIL_PATH . 'test_system.php';
include_once TEST_PHP_UTIL_PATH . 'test_db_link.php';
include_once TEST_PHP_UTIL_PATH . 'test_user.php';
include_once TEST_PHP_UTIL_PATH . 'test_user_sandbox.php';
include_once TEST_PHP_UTIL_PATH . 'test_api.php';
include_once TEST_PHP_UTIL_PATH . 'test_cleanup.php';

// load the unit testing modules
include_once TEST_UNIT_PATH . 'all_unit_tests.php';
include_once TEST_UNIT_PATH . 'lib_tests.php';
include_once TEST_UNIT_PATH . 'math_tests.php';
include_once TEST_UNIT_PATH . 'system_tests.php';
include_once TEST_UNIT_PATH . 'pod_tests.php';
include_once TEST_UNIT_PATH . 'user_tests.php';
include_once TEST_UNIT_PATH . 'user_list_tests.php';
include_once TEST_UNIT_PATH . 'sandbox_tests.php';
include_once TEST_UNIT_PATH . 'type_tests.php';
include_once TEST_UNIT_PATH . 'word_tests.php';
include_once TEST_UNIT_PATH . 'word_list_tests.php';
include_once TEST_UNIT_PATH . 'triple_tests.php';
include_once TEST_UNIT_PATH . 'triple_list_tests.php';
include_once TEST_UNIT_PATH . 'phrase_tests.php';
include_once TEST_UNIT_PATH . 'phrase_list_tests.php';
include_once TEST_UNIT_PATH . 'group_tests.php';
include_once TEST_UNIT_PATH . 'group_list_tests.php';
include_once TEST_UNIT_PATH . 'term_tests.php';
include_once TEST_UNIT_PATH . 'term_list_tests.php';
include_once TEST_UNIT_PATH . 'value_tests.php';
include_once TEST_UNIT_PATH . 'value_list_tests.php';
include_once TEST_UNIT_PATH . 'formula_tests.php';
include_once TEST_UNIT_PATH . 'formula_list_tests.php';
include_once TEST_UNIT_PATH . 'formula_link_tests.php';
include_once TEST_UNIT_PATH . 'result_tests.php';
include_once TEST_UNIT_PATH . 'result_list_tests.php';
include_once TEST_UNIT_PATH . 'element_tests.php';
include_once TEST_UNIT_PATH . 'element_list_tests.php';
include_once TEST_UNIT_PATH . 'figure_tests.php';
include_once TEST_UNIT_PATH . 'figure_list_tests.php';
include_once TEST_UNIT_PATH . 'expression_tests.php';
include_once TEST_UNIT_PATH . 'view_tests.php';
include_once TEST_UNIT_PATH . 'term_view_tests.php';
include_once TEST_UNIT_PATH . 'view_list_tests.php';
include_once TEST_UNIT_PATH . 'component_tests.php';
include_once TEST_UNIT_PATH . 'component_link_tests.php';
include_once TEST_UNIT_PATH . 'component_list_tests.php';
include_once TEST_UNIT_PATH . 'component_link_list_tests.php';
include_once TEST_UNIT_PATH . 'verb_tests.php';
include_once TEST_UNIT_PATH . 'source_tests.php';
include_once TEST_UNIT_PATH . 'source_list_tests.php';
include_once TEST_UNIT_PATH . 'ref_tests.php';
include_once TEST_UNIT_PATH . 'language_tests.php';
include_once TEST_UNIT_PATH . 'job_tests.php';
include_once TEST_UNIT_PATH . 'change_log_tests.php';
include_once TEST_UNIT_PATH . 'sys_log_tests.php';
include_once TEST_UNIT_PATH . 'import_tests.php';
include_once TEST_UNIT_PATH . 'db_setup_tests.php';
include_once TEST_UNIT_PATH . 'api_tests.php';

// load the testing functions for creating HTML code
include_once TEST_UNIT_UI_PATH . 'all_ui_tests.php';

// load the unit testing modules with database read only
include_once TEST_UNIT_READ_PATH . 'all_unit_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'system_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'sql_db_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'user_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'job_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'change_log_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'sys_log_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'word_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'word_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'triple_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'triple_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'verb_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'phrase_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'phrase_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'group_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'term_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'term_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'value_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'value_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'formula_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'formula_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'expression_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'element_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'view_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'view_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'component_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'component_list_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'source_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'ref_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'share_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'protection_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'language_read_tests.php';
include_once TEST_UNIT_READ_PATH . 'export_read_tests.php';

// load the testing functions that save data to the database
include_once TEST_UNIT_WRITE_PATH . 'all_unit_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'word_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'word_list_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'verb_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'triple_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'phrase_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'phrase_list_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'group_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'group_list_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'graph_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'term_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'value_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'source_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'ref_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'expression_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'formula_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'formula_link_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'formula_trigger_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'result_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'element_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'element_group_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'job_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'view_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'view_link_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'component_write_tests.php';
include_once TEST_UNIT_WRITE_PATH . 'component_link_write_tests.php';

include_once TEST_UNIT_WRITE_PATH . 'test_word_display.php';
include_once TEST_UNIT_WRITE_PATH . 'test_math.php';

//
include_once TEST_PHP_UTIL_PATH . 'all_tests.php';
include_once TEST_PHP_UTIL_PATH . 'format.php';
include_once TEST_PHP_UTIL_PATH . 'level.php';

// load the integration test functions
include_once TEST_UNIT_INT_PATH . 'test_import.php';
include_once TEST_UNIT_INT_PATH . 'test_export.php';

// load the test functions still in development
include_once TEST_DEV_PATH . 'test_legacy.php';

// TODO to be dismissed
include_once WEB_USER_PATH . 'user_display_old.php';


/*
 *   testing class - to check the words, values and formulas that should always be in the system
 *   -------------
*/

class test_base
{
    // the url which should be used for testing (maybe later https://test.zukunft.com/)
    const URL = 'https://zukunft.com/';

    const TEST_TYPE_CONTAINS = 'contains';
    const TEST_TYPE_NOT = 'not';
    const FILE_EXT = '.sql';
    const FILE_MYSQL = '_mysql';


    /*
     * test const
     */

    // add this to the object name to test if it can be renamed
    const EXT_RENAME = ' renamed';


    /*
     * Setting that should be moved to the system config table
     */

    // switch for the email testing
    const TEST_EMAIL = FALSE; // if set to true an email will be sent in case of errors and once a day an "everything fine" email is send

    // max time expected for each function execution
    const TIMEOUT_LIMIT = 0.03; // time limit for normal functions
    const TIMEOUT_LIMIT_PAGE = 0.1;  // time limit for complete webpage
    const TIMEOUT_LIMIT_FILE = 0.3;  // time limit for file reading function
    const TIMEOUT_LIMIT_CALC = 0.5;  // time limit for calculations
    const TIMEOUT_LIMIT_PAGE_SEMI = 0.6;  // time limit for complete webpage
    const TIMEOUT_LIMIT_PAGE_LONG = 1.2;  // time limit for complete webpage
    const TIMEOUT_LIMIT_DB = 0.2;  // time limit for database modification functions
    const TIMEOUT_LIMIT_DB_MULTI = 0.9;  // time limit for many database modifications
    const TIMEOUT_LIMIT_LONG = 3;    // time limit for complex functions
    const TIMEOUT_LIMIT_IMPORT = 12;    // time limit for complex import tests in seconds

    const TEST_TIMESTAMP = '2024-04-05T08:35:30+00:00'; // fixed timestamp used for testing


    public user $usr1; // the main user for testing
    public user $usr2; // a second testing user e.g. to test the user sandbox
    public user $usr_admin; // a user with the admin profile to test allow of admin functionality
    public user $usr_normal; // a user with the standard profile to test deny of admin functionality

    private float $start_time; // time when all tests have started
    private float $exe_start_time; // time when the single test has started (end the end time of all tests)

    // the counter of the error for the summery
    private int $error_counter;
    private int $timeout_counter;
    private int $total_tests;

    public string $name;
    public string $resource_path;

    private int $seq_nbr;

    public format $format = format::TEXT;
    public level $level = level::TIMEOUT;


    function __construct()
    {
        global $cfg;

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

        // load the test config
        //$this->format = format::from($cfg->get_by([words::TEST, triples::OUTPUT_FORMAT]));
        //$this->level = level::from($cfg->get_by([words::TEST, words::LEVEL]));
    }

    function set_users(): void
    {

        // create the system test user to simulate the user sandbox
        // e.g. a value owned by the first user cannot be adjusted by the second user instead a user specific value is created
        // instead a user specific value is created
        // for testing $usr is the user who has started the test ans $usr1 and $usr2 are the users used for simulation
        $this->usr1 = new user();
        $this->usr1->load_by_name(user::SYSTEM_TEST_NAME);

        $this->usr2 = new user();
        $this->usr2->load_by_name(user::SYSTEM_TEST_PARTNER_NAME);

        $this->usr_admin = new user();
        $this->usr_admin->load_by_name(user::SYSTEM_TEST_ADMIN_NAME);

        $this->usr_normal = new user();
        $this->usr_normal->load_by_name(user::SYSTEM_TEST_NORMAL_NAME);

    }


    /*
     * set and get
     */

    function start_time(): float
    {
        return $this->start_time;
    }


    /*
     * Display functions
     */

    /**
     * the HTML code to display the header text
     */
    function header(string $header_text): void
    {
        if ($this->format == format::TEXT) {
            echo $this->time_stamp() . $header_text . "\n";
        } else {
            echo '<br><br><h2>' . $this->time_stamp() . $header_text . '</h2><br>' . "\n";
        }
    }

    /**
     * the HTML code to display the subheader text
     */
    function subheader(string $header_text): void
    {
        if ($this->format == format::TEXT) {
            echo $this->time_stamp() . $header_text . "\n";
        } else {
            echo '<br><h3>' . $this->time_stamp() . $header_text . '</h3><br>' . "\n";
        }
    }

    private function time_stamp(): string
    {
        return sprintf('%08.4f', microtime(true) - $this->start_time) . ' ';
    }

    /**
     * check if the test result is as expected and display the test result to an admin user
     * TODO replace all dsp calls with this but the
     * TODO the first parameter should almost always be $ts . $test_name
     *
     * @param string $test_name (unique) description of the test
     * @param string|array|null $result the actual result
     * @param string|array|null $target the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert(
        string                 $test_name,
        string|bool|array|null $result,
        string|bool|array|null $target = '',
        float                  $exe_max_time = self::TIMEOUT_LIMIT,
        string                 $comment = '',
        string                 $test_type = ''): bool
    {
        // init the test result vars
        $lib = new library();

        // the result should never be null, but if, check it here not on each test
        if ($result === null) {
            $result = '';
            $comment .= 'result of test ' . $test_name . ' has been null';
        }

        // do the compare depending on the type
        if ($test_type == self::TEST_TYPE_CONTAINS) {
            $msg = $lib->explain_missing($result, $target);
        } elseif ($test_type == self::TEST_TYPE_NOT) {
            $msg = $lib->not_msg($result, $target);
        } else {
            $msg = $lib->diff_msg($result, $target);
        }

        // remove html colors to avoid misleading check display colors
        $msg = $this->test_remove_color($msg);

        // check if the test has been fine
        if ($msg == '') {
            $test_result = true;
        } else {
            $test_result = false;
        }

        // add info level comments to the result after the
        if ($comment <> '') {
            $test_name .= ' (' . $comment . ')';
        }

        return $this->assert_dsp($test_name, $test_result, $target, $result, $msg, $exe_max_time);
    }

    /**
     * check the object nor the id and nor the name is used
     *
     * @param string $test_name (unique) description of the test
     * @param string|array|null $result the actual result
     * @param string|array|null $target the expected result
     * @return bool the load object to use it for more tests
     */
    function assert_not(
        string            $test_name,
        string|array|null $result,
        string|array|null $target = ''
    ): bool
    {
        return $this->assert($test_name, $result, $target
            , self::TIMEOUT_LIMIT, '', self::TEST_TYPE_NOT);
    }

    /**
     * check if the result is true and format the result as a string
     *
     * @param string $msg (unique) description of the test
     * @param bool $result the result of the previous called test
     * @return bool true is the result is fine
     */
    function assert_true(
        string $msg,
        bool   $result
    ): bool
    {
        if ($result === true) {
            return true;
        } else {
            return $this->assert_dsp($msg, false, 'true', 'false', '');
        }
    }

    /**
     * check if the result is false and format the result as a string
     *
     * @param string $msg (unique) description of the test
     * @param bool $result the result of the previous called test
     * @return bool true is the result is fine
     */
    function assert_false(
        string $msg,
        bool   $result
    ): bool
    {
        if ($result === false) {
            return true;
        } else {
            return $this->assert_dsp($msg, false, 'false', 'true', '');
        }
    }

    /**
     * check if the result text is empty
     * e.g. because the result string is a difference message
     *
     * @param string $msg (unique) description of the test
     * @param string $err_message the error message which is expected to be an empty string
     * @return bool true is the result is fine
     */
    function assert_empty(
        string $msg,
        string $err_message,
        string $result_str = '',
        string $target_str = ''
    ): bool
    {
        if ($err_message == '') {
            return true;
        } else {
            return $this->assert_dsp($msg . $err_message, false, $target_str, $result_str, $err_message);
        }
    }

    /**
     * check if the result text contains at least the target text
     *
     * @param string $msg (unique) description of the test
     * @param string $haystack the expected result
     * @param string $needle the actual result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_text_contains(
        string $msg,
        string $haystack,
        string $needle,
        float  $exe_max_time = self::TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {
        $pos = strpos($haystack, $needle);
        if ($pos !== false) {
            $needle = $haystack;
        }
        return $this->display(', ' . $msg, $haystack, $needle, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the test results contains at least all expected results
     * or in other words if all needles can be found in the haystack
     *
     * @param string $msg (unique) description of the test
     * @param int $min the minimal expected number
     * @param int $actual the actual number
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_greater(
        string $msg,
        int    $min,
        int    $actual,
        float  $exe_max_time = self::TIMEOUT_LIMIT,
        string $comment = '',
        string $test_type = ''): bool
    {
        if ($actual > $min) {
            $actual = $min;
        } else {
            $actual = $min - 1;
        }
        // the array keys are not relevant if only a few elements should be checked
        return $this->assert($msg, $actual, $min, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the test results contains at least all expected results
     * or in other words if all needles can be found in the haystack
     *
     * @param string $msg (unique) description of the test
     * @param array $haystack the actual result
     * @param array|string $needle the expected minimal result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_contains(
        string       $msg,
        array        $haystack,
        array|string $needle,
        float        $exe_max_time = self::TIMEOUT_LIMIT,
        string       $comment = '',
        string       $test_type = ''): bool
    {
        if (is_string($needle)) {
            $needles = array($needle);
        } else {
            $needles = $needle;
        }
        // the array keys are not relevant if only a few elements should be checked
        $haystack = array_values(array_intersect($haystack, $needles));
        return $this->display(', ' . $msg, $needles, $haystack, $exe_max_time, $comment, $test_type);
    }

    /**
     * check if the test results contains at least all expected results
     *
     * @param string $msg (unique) description of the test
     * @param array $haystack the actual result
     * @param array|string $needle the expected result
     * @param float $exe_max_time the expected max time to create the result
     * @param string $comment
     * @param string $test_type
     * @return bool true is the result is fine
     */
    function assert_contains_not(
        string       $msg,
        array        $haystack,
        array|string $needle,
        float        $exe_max_time = self::TIMEOUT_LIMIT,
        string       $comment = '',
        string       $test_type = ''): bool
    {
        if (is_string($needle)) {
            $needles = array($needle);
        } else {
            $needles = $needle;
        }
        $haystack = array_diff($needles, $haystack);
        return $this->display(', ' . $msg, $needles, $haystack, $exe_max_time, $comment, $test_type);
    }


    /*
     * debug
     */

    /**
     * check if the debug function dsp_id() returns an usefully text
     * without calling other functions that might cause a loop (at least not db function)
     *
     * @param object $usr_obj any object with some sample vars set
     * @param string $msg the expected text for a unique identification
     * @return bool true if the created text matches the expected text without causing a loop
     */
    function assert_dsp_id(object $usr_obj, string $msg): bool
    {
        $lib = new library();
        $test_name = 'debug id for ' . $lib->class_to_name($usr_obj::class);
        return $this->assert($test_name, $usr_obj->dsp_id(), $msg);
    }


    /*
     * api
     */

    /**
     * check if the frontend API object can be created
     * and if the export based recreation of the backend object result to the similar object
     *
     * @param object $usr_obj the object which frontend API functions should be tested
     * @return bool true if the reloaded backend object has no relevant differences
     */
    function assert_export_reload(string $test_name, object $usr_obj): bool
    {
        $lib = new library();
        $original_json = $usr_obj->export_json();
        $db_obj = clone $usr_obj;
        $db_obj->reset();
        $db_obj->load_by_id($usr_obj->id());
        $recreated_json = $db_obj->export_json();
        $result = $lib->json_is_similar($original_json, $recreated_json);
        // TODO remove, for faster debugging only
        $json_in_txt = json_encode($original_json);
        $json_ex_txt = json_encode($recreated_json);
        return $this->assert_json($test_name . ' reloaded export json of ' . $usr_obj::class, $recreated_json, $original_json);
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
        $url = api::HOST_TESTING . api::URL_API_PATH . 'json';
        $data = array($fld => $id);
        $ctrl = new rest_ctrl();
        $actual = json_decode($ctrl->api_call(rest_ctrl::GET, $url, $data), true);
        // TODO remove next line (added for faster debugging only)
        $json_actual = json_encode($actual);
        $expected_text = $this->file('api/json/' . $test_name . '.json');
        $expected = json_decode($expected_text, true);
        return $this->assert($test_name . ' API GET', $lib->json_is_similar($actual, $expected), true);
    }


    /**
     * test a system view with a sample user object
     *
     * @param string $dsp_code_id the code id of the view that should be tested
     * @param user $usr to define for which user the view should be created
     * @param db_object_seq_id $dbo the database object that should be shown
     * @param int $id the id of the database object that should be loaded and send to the frontend
     * @param data_object_dsp|null $cfg the context that should be used to create the view
     *                              which can be fixed test data for stable test results
     * @return bool true if the generated view matches the expected
     */
    function assert_view(
        string           $dsp_code_id,
        user             $usr,
        db_object_seq_id $dbo,
        int              $id = 0,
        ?data_object_dsp $cfg = null
    ): bool
    {
        $lib = new library();

        // create the filename of the expected result
        $folder = '';
        $dbo_name = '';
        $class = '';
        if ($dbo != null) {
            $class = $lib->class_to_name($dbo::class);
            $folder = $class . '/';
            if ($id > 0) {
                $dbo_name = '_' . $class;
                $dbo_name .= '_' . $id;
            }
        }
        $filename = 'views/' . $folder . $dsp_code_id . $dbo_name;

        // load the view from the database
        $msk = new view($usr);
        $msk->load_by_code_id($dsp_code_id);
        $msk->load_components();

        // create the api message that send to the frontend
        $api_msg = $msk->api_json();
        if ($id != 0) {
            // add the database object json to the api message
            // to send only one message to the frontend
            $dbo->load_by_id($id);
        }
        $dbo_api_msg = $dbo->api_json();
        $api_msg = $lib->json_merge_str($api_msg, $dbo_api_msg, $class);
        $dbo_dsp = $this->frontend_obj_from_backend_object($dbo);
        if ($id != 0) {
            $dbo_dsp->set_from_json($dbo_api_msg);
        }

        // create the view for the user
        $dsp_html = new view_dsp;
        $dsp_html->set_from_json($api_msg);
        $actual = $dsp_html->show($dbo_dsp, $cfg, '', true);

        // check if the created view matches the expected view
        return $this->assert_html(
            $this->name . ' view ' . $dsp_code_id,
            $actual, $filename);
    }

    /**
     * the frontend object related to the given backend object
     * @param db_object_seq_id|sandbox_value $dbo the given backend object
     * @return false|db_object_dsp the corresponding frontend object
     */
    private function frontend_obj_from_backend_object(db_object_seq_id|sandbox_value $dbo): false|db_object_dsp
    {
        return match ($dbo::class) {
            word::class => new word_dsp(),
            verb::class => new verb_dsp(),
            triple::class => new triple_dsp(),
            source::class => new source_dsp(),
            ref::class => new ref_dsp(),
            value::class => new value_dsp(),
            //group::class => new group_dsp(),
            formula::class => new formula_dsp(),
            result::class => new result_dsp(),
            view::class => new view_dsp(),
            component::class => new component_dsp(),
            default => false,
        };
    }

    /**
     * check if an object json sample export file can be recreated by
     * importing the object and recreating the json with the export function
     *
     * @param object $usr_obj the object which json im- and export functions should be tested
     * @param string $json_file_name the resource path name to the json sample file
     * @return bool true if the json has no relevant differences
     */
    function assert_json_file(object $usr_obj, string $json_file_name): bool
    {
        global $usr_pro_cac;
        $lib = new library();
        $file_text = $this->file($json_file_name);
        $json_in = json_decode($file_text, true);
        if ($usr_obj::class == user::class) {
            $usr_obj->import_obj($json_in, $usr_pro_cac->id(user_profiles::ADMIN), $this);
        } else {
            $usr_obj->import_obj($json_in, $this);
        }
        $this->set_id_for_unit_tests($usr_obj);
        $json_ex = $usr_obj->export_json(false);
        // TODO remove, for faster debugging only
        $json_in_txt = json_encode($json_in);
        $json_ex_txt = json_encode($json_ex);
        return $this->assert_json($this->name . 'import check name', $json_in, $json_ex);
    }

    /**
     * check if an object can be recreated by exporting the object and
     * recreating the object based on the export json
     *
     * @param object $usr_obj the object which json im- and export functions should be tested
     * @return bool true if the object has no relevant differences
     */
    function assert_ex_and_import(object $usr_obj): bool
    {
        $json_before = $usr_obj->api_json([api_type::TEST_MODE]);
        $json_ex = $usr_obj->export_json(false);
        $new_obj = clone $usr_obj;
        $new_obj->reset();
        $new_obj->import_obj($json_ex, $this);
        $json_after = $usr_obj->api_json([api_type::TEST_MODE]);
        return $this->assert_json_string(
            'ex- and import test for ' . $usr_obj::class, $json_after, $json_before);
    }

    /**
     * check if an object json file can be recreated by importing the object and recreating the json with the export function
     *
     * @param string $test_name (unique) description of the test
     * @param array|null $result the actual json as array
     * @param array $target the expected json as array
     * @return bool true if the json has no relevant differences
     */
    function assert_json(string $test_name, array|null $result, array $target): bool
    {
        $lib = new library();
        $diff = '';
        if ($result == null) {
            $diff = 'json is empty';
        } else {
            if (!$lib->json_is_similar($result, $target)) {
                $diff = $lib->diff_msg($result, $target);
            }
        }
        $result_str = json_encode($result);
        $target_str = json_encode($target);
        return $this->assert_empty($test_name, $diff, $result_str, $target_str);
    }

    /**
     * check if an object json file can be recreated by importing the object and recreating the json with the export function
     *
     * @param string $test_name (unique) description of the test
     * @param string $result the actual json as string
     * @param string $target the expected json as string
     * @return bool true if the json has no relevant differences
     */
    function assert_json_string(string $test_name, string $result, string $target): bool
    {
        return $this->assert_json($test_name, json_decode($result, true), json_decode($target, true));
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


    /*
     * SQL for db_object
     */

    /**
     * check if the object can return the sql table names
     * for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_truncate(sql_creator $sc, object $usr_obj): bool
    {
        $lib = new library();
        $class = $lib->class_to_name($usr_obj::class);
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $name = $class . '_truncate';
        $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
        $actual_sql = $usr_obj->sql_truncate($sc, $class);
        $result = $this->assert_sql($name, $actual_sql, $expected_sql);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
            $actual_sql = $usr_obj->sql_truncate($sc, $class);
            $result = $this->assert_sql($name, $actual_sql, $expected_sql);
        }
        return $result;
    }

    /**
     * check the SQL statement to create the sql table
     * for all allowed SQL database dialects
     *
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_table_create(object $usr_obj): bool
    {
        $lib = new library();
        $class = $lib->class_to_name($usr_obj::class);
        // check the Postgres query syntax
        $sc = new sql_creator(sql_db::POSTGRES);
        $name = $class . '_create';
        $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
        $actual_sql = $usr_obj->sql_table($sc, $class);
        $result = $this->assert_sql($name, $actual_sql, $expected_sql);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
            $actual_sql = $usr_obj->sql_table($sc, $class);
            $result = $this->assert_sql($name, $actual_sql, $expected_sql);
        }
        return $result;
    }

    /**
     * check the SQL statement to create the indices related to a table
     * for all allowed SQL database dialects
     *
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_index_create(object $usr_obj): bool
    {
        $lib = new library();
        $class = $lib->class_to_name($usr_obj::class);
        // check the Postgres query syntax
        $sc = new sql_creator(sql_db::POSTGRES);
        $name = $class . '_index';
        $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
        $actual_sql = $usr_obj->sql_index($sc, $class);
        $result = $this->assert_sql($name, $actual_sql, $expected_sql);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
            $actual_sql = $usr_obj->sql_index($sc, $class);
            $result = $this->assert_sql($name, $actual_sql, $expected_sql);
        }
        return $result;
    }

    /**
     * check the SQL statement to create the foreign keys related to a table
     * for all allowed SQL database dialects
     *
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_foreign_key_create(object $usr_obj): bool
    {
        $lib = new library();
        $class = $lib->class_to_name($usr_obj::class);
        // check the Postgres query syntax
        $sc = new sql_creator(sql_db::POSTGRES);
        $name = $class . '_foreign_key';
        $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
        $actual_sql = $usr_obj->sql_foreign_key($sc, $class);
        $result = $this->assert_sql($name, $actual_sql, $expected_sql);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
            $actual_sql = $usr_obj->sql_foreign_key($sc, $class);
            $result = $this->assert_sql($name, $actual_sql, $expected_sql);
        }
        return $result;
    }

    /**
     * check the SQL statement to create the sql view
     * for all allowed SQL database dialects
     *
     * @param object $usr_obj the user sandbox object e.g. a phrase
     * @return bool true if all tests are fine
     */
    function assert_sql_view_create(object $usr_obj): bool
    {
        $lib = new library();
        $class = $lib->class_to_name($usr_obj::class);
        // check the Postgres query syntax
        $sc = new sql_creator(sql_db::POSTGRES);
        $name = $class . '_view';
        $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
        $actual_sql = $usr_obj->sql_view($sc, $class);
        $result = $this->assert_sql($name, $actual_sql, $expected_sql);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
            $actual_sql = $usr_obj->sql_view($sc, $class);
            $result = $this->assert_sql($name, $actual_sql, $expected_sql);
        }
        return $result;
    }

    /**
     * check the SQL statement to create the sql view that links tables
     * for all allowed SQL database dialects
     *
     * @param object $usr_obj the user sandbox object e.g. a phrase
     * @return bool true if all tests are fine
     */
    function assert_sql_view_link_create(object $usr_obj): bool
    {
        $lib = new library();
        $class = $lib->class_to_name($usr_obj::class);
        // check the Postgres query syntax
        $sc = new sql_creator(sql_db::POSTGRES);
        $name = $class . '_view';
        $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
        $actual_sql = $usr_obj->sql_view_link($sc, $usr_obj::FLD_LST_VIEW);
        $result = $this->assert_sql($name, $actual_sql, $expected_sql);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $expected_sql = $this->assert_sql_expected($name, $sc->db_type);
            $actual_sql = $usr_obj->sql_view_link($sc, $usr_obj::FLD_LST_VIEW);
            $result = $this->assert_sql($name, $actual_sql, $expected_sql);
        }
        return $result;
    }

    /**
     * check the SQL statement creation to save fields
     * via function in the database
     * for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param object $norm_obj the normal sandbox object for all new users
     * @param array $sc_par_lst_in the parameters for the sql statement creation
     * @return bool true if all tests are fine
     */
    function assert_sql_save_fields(sql_creator $sc, object $usr_obj, object $norm_obj, array $sc_par_lst_in = []): bool
    {
        // prepare like in save_fields_func
        $sc_par_lst = new sql_type_list($sc_par_lst_in);
        $sc_par_lst->add(sql_type::INSERT);
        $sc_par_lst->add(sql_type::NO_ID_RETURN);
        $all_fields = $usr_obj->db_fields_all();
        $fvt_lst = $usr_obj->db_fields_changed($norm_obj, $sc_par_lst);

        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->sql_insert_switch($sc, $fvt_lst, $all_fields, $sc_par_lst);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->sql_insert_switch($sc, $fvt_lst, $all_fields, $sc_par_lst);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statement creation to add a database row
     * for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param array $sc_par_lst_in the parameters for the sql statement creation
     * @return bool true if all tests are fine
     */
    function assert_sql_insert(sql_creator $sc, object $usr_obj, array $sc_par_lst_in = []): bool
    {
        $sc_par_lst = new sql_type_list($sc_par_lst_in);
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->sql_insert($sc, $sc_par_lst);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->sql_insert($sc, $sc_par_lst);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statement creation to update a database row
     * for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param object $db_obj must be the same object as the $usr_obj but with the values from the database before the update
     * @param array $sql_type_array the parameters for the sql statement creation
     * @return bool true if all tests are fine
     */
    function assert_sql_update(sql_creator $sc, object $usr_obj, object $db_obj, array $sql_type_array = []): bool
    {
        $sc_par_lst = new sql_type_list($sql_type_array);
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->sql_update($sc, $db_obj, $sc_par_lst);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->sql_update($sc, $db_obj, $sc_par_lst);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statement to delete a database row
     * for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param array $sc_par_lst_in the parameters for the sql statement creation
     * @return bool true if all tests are fine
     */
    function assert_sql_delete(sql_creator $sc, object $usr_obj, array $sc_par_lst_in = []): bool
    {
        $sc_par_lst = new sql_type_list($sc_par_lst_in);
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->sql_delete($sc, $sc_par_lst);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->sql_delete($sc, $sc_par_lst);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statement to load a db object by id
     * for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param array $sc_par_lst_in the parameters for the sql statement creation
     * @return bool true if all tests are fine
     */
    function assert_sql_by_id(sql_creator $sc, object $usr_obj, array $sc_par_lst_in = []): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_id($sc, $usr_obj->id());
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_id($sc, $usr_obj->id());
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_sql_by_id, but for the parent/formula id type
     *
     * @param sql_creator $sc the test database connection
     * @param element_list $lst the empty word list object
     * @param int $frm_id the formula id that should be used for selecting the elements
     * @param string $test_name the test name only for the test log
     * @return void
     */
    function assert_sql_by_frm_id(sql_creator $sc, element_list $lst, int $frm_id, string $test_name = ''): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $lst->load_sql_by_frm_id($sc, $frm_id);
        $result = $this->assert_qp($qp, $sc->db_type, $test_name);

        // check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $lst->load_sql_by_frm_id($sc, $frm_id, $test_name);
            $this->assert_qp($qp, $sc->db_type);
        }
    }

    /**
     * check the SQL statement to load the default object by id
     * for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param sandbox|sandbox_multi $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_standard(sql_creator $sc, sandbox|sandbox_multi $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_standard_sql($sc);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_standard_sql($sc);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statement to load the default object by id
     * for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param group $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_standard_by_name(sql_creator $sc, group $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_standard_by_name_sql($sc, $usr_obj->name());
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_standard_by_name_sql($sc, $usr_obj->name());
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements to get the user sandbox changes
     * e.g. the value a user has changed of word, triple, value or formulas
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param sandbox|sandbox_value $usr_obj the user sandbox object e.g. a word
     * @param array $sc_par_lst_in the parameters for the sql statement creation
     * @return bool true if all tests are fine
     */
    function assert_sql_user_changes(sql_creator $sc, sandbox|sandbox_value $usr_obj, array $sc_par_lst_in = []): bool
    {
        $sc_par_lst = new sql_type_list($sc_par_lst_in);
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_user_changes($sc, $sc_par_lst);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_user_changes($sc, $sc_par_lst);
            $result = $this->assert_qp($qp, $sc->db_type);
        }

        return $result;
    }

    /**
     * check the SQL statements to get all users that have changed the object
     * TODO add this test once to each user object type
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param sandbox|sandbox_value $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_changer(sql_creator $sc, sandbox|sandbox_value $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_changer($sc);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_changer($sc);
            $result = $this->assert_qp($qp, $sc->db_type);
        }

        return $result;
    }

    /**
     * check the SQL statements to get the users that has created the most often used db row
     * TODO add this test once to each relevant object type (at least once for named sandbox, link and value)
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param sandbox|sandbox_value $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_median_user(sql_creator $sc, sandbox|sandbox_value $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_median_user($sc);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_median_user($sc);
            $result = $this->assert_qp($qp, $sc->db_type);
        }

        return $result;
    }

    /**
     * check the SQL statements to get the users that have ever done a change
     * e.g. to clean up changes not needed any more
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param sandbox $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_changing_users(sql_creator $sc, sandbox $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_of_users_that_changed($sc);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_of_users_that_changed($sc);
            $result = $this->assert_qp($qp, $sc->db_type);
        }

        return $result;
    }


    /*
     * SQL for named
     */

    /**
     * check the SQL statement to load a db object by name for all allowed SQL database dialects
     * similar to assert_sql_by_id but select one row based on the name
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_by_name(sql_creator $sc, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_name($sc, 'System test', $usr_obj::class);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_name($sc, 'System test', $usr_obj::class);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements to load named sandbox objects by a pattern for the name
     * for all allowed SQL database dialects
     * TODO add unit and load test for triple, verb, view and component list
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param string $pattern the pattern for the name used for testing
     * @return bool true if all tests are fine
     */
    function assert_sql_like(sql_creator $sc, object $usr_obj, string $pattern = ''): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_like($sc, $pattern);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_like($sc, $pattern);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements to load a sandbox object by term
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param sandbox $usr_obj the user sandbox object e.g. a view
     * @param term $trm the term used for the sql statement creation
     * @return bool true if all tests are fine
     */
    function assert_sql_by_term(sql_creator $sc, sandbox $usr_obj, term $trm): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_term($sc, $trm);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_term($sc, $trm);
            $result = $this->assert_qp($qp, $sc->db_type);
        }

        return $result;
    }


    /*
     * SQL for code id
     */

    /**
     * check the object load by name SQL statements for all allowed SQL database dialects
     * similar to assert_load_sql but select one row based on the code id
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a verb
     * @return bool true if all tests are fine
     */
    function assert_sql_by_code_id(sql_creator $sc, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_code_id($sc, 'System test', $usr_obj::class);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_code_id($sc, 'System test', $usr_obj::class);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }


    /*
     * SQL for link
     */

    /**
     * check the SQL statements for user object load by linked objects for all allowed SQL database dialects
     * similar to assert_sql_by_id but select one row based on the linked components
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param sandbox_link $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_by_link(sql_creator $sc, sandbox_link $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        if ($usr_obj::class == ref::class) {
            $target_id = 'external key';
        } else {
            $target_id = 3;
        }
        $qp = $usr_obj->load_sql_by_link($sc, 1, 1, $target_id, $usr_obj::class);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_link($sc, 1, 1, $target_id, $usr_obj::class);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the object load SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_all_paged(sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_all($db_con, 10, 2);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_all($db_con, 10, 2);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }


    /*
     * SQL for preloaded types
     */

    /**
     * check the object load SQL statements for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param string $class to define the database type if it does not match the class
     * @return bool true if all tests are fine
     */
    function assert_sql_all(sql_creator $sc, object $usr_obj, string $class = ''): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_all($sc, $class);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_all($sc, $class);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }


    /*
     * SQL for log
     */

    /**
     * check the object load by id list SQL statements for all allowed SQL database dialects
     * similar to assert_load_sql but for a user
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_by_user(sql_creator $sc, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_user($sc, $this->usr1);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_user($sc, $this->usr1);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /*
     * SQL for id list
     */

    /**
     * check the object load by id list SQL statements for all allowed SQL database dialects
     * similar to assert_sql_by_id but for an id list
     *
     * @param string $test_name the description of the test
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param array|phr_ids|trm_ids|fig_ids|null $ids the ids that should be loaded
     * @param value_types|null $val_types if not null load only the types of this list
     * @return bool true if all tests are fine
     */
    function assert_sql_by_ids(
        string                             $test_name,
        sql_creator                        $sc,
        object                             $usr_obj,
        array|phr_ids|trm_ids|fig_ids|null $ids = array(1, 2),
        value_types|null                   $val_types = null
    ): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_ids($sc, $ids, 0, 0, false, $val_types);
        $result = $this->assert_qp($qp, $sc->db_type, $test_name);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_ids($sc, $ids, 0, 0, false, $val_types);
            $result = $this->assert_qp($qp, $sc->db_type, $test_name);
        }
        return $result;
    }

    /**
     * check the object load by id list SQL statements for all allowed SQL database dialects
     * similar to assert_sql_by_id but for an id list
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_names_by_ids(sql_creator $sc, object $usr_obj, ?array $ids = array(1, 2)): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_names_sql_by_ids($sc, $ids);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_names_sql_by_ids($sc, $ids);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the object load by id list SQL statements for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $lst_obj the user sandbox object e.g. a word
     * @param sandbox_named|sandbox_link_named|combine_named $sbx the user sandbox object e.g. a word
     * @param string $pattern the pattern to filter
     * @return bool true if all tests are fine
     */
    function assert_sql_names(
        sql_creator                                    $sc,
        object                                         $lst_obj,
        sandbox_named|sandbox_link_named|combine_named $sbx,
        string                                         $pattern = ''
    ): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $lst_obj->load_sql_names($sc, $sbx, $pattern);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $lst_obj->load_sql_names($sc, $sbx, $pattern);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements to load a list by name for all allowed SQL database dialects
     * similar to assert_sql_by_ids but for a name list
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param array $names with the names of the objects that should be loaded
     * @return bool true if all tests are fine
     */
    function assert_sql_by_names(sql_creator $sc, object $usr_obj, array $names): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_names($sc, $names);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_names($sc, $names);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements to load a group by phrase list for all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param phrase $phr with the names of the objects that should be loaded
     * @return bool true if all tests are fine
     */
    function assert_sql_by_phrase(sql_creator $sc, object $usr_obj, phrase $phr): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_phr($sc, $phr);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_phr($sc, $phr);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

    /**
     * test the SQL statement creation for a value or result list
     * similar to assert_load_sql but for a phrase list
     *
     * @param string $test_name does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param phrase_list $phr_lst the phrase list that should be used for the sql creation
     * @param bool $or if true all values are returned that are linked to any phrase of the list
     */
    function assert_sql_by_phr_lst(
        string      $test_name,
        object      $usr_obj,
        phrase_list $phr_lst,
        bool        $or = false
    ): void
    {
        // check the Postgres query syntax
        $sc = new sql_creator();
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_phr_lst($sc, $phr_lst, false, $or);
        $result = $this->assert_qp($qp, $sc->db_type, $test_name);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_phr_lst($sc, $phr_lst, false, $or);
            $this->assert_qp($qp, $sc->db_type, $test_name);
        }
    }

    /**
     * check the SQL statements to load a list of result by group
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param group $grp with the phrase to select the results
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if all tests are fine
     */
    function assert_sql_by_group(sql_creator $sc, object $usr_obj, group $grp, bool $by_source = false): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        if ($by_source) {
            $qp = $usr_obj->load_sql_by_src_grp($sc, $grp);
        } else {
            $qp = $usr_obj->load_sql_by_grp($sc, $grp);
        }
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            if ($by_source) {
                $qp = $usr_obj->load_sql_by_src_grp($sc, $grp);
            } else {
                $qp = $usr_obj->load_sql_by_grp($sc, $grp);
            }
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }


    /*
     * SQL for list by ...
     */

    /**
     * check the SQL statements for loading a list of objects in all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $lst_obj the list object e.g. a result list
     * @param object $select_obj the named user sandbox or phrase group object used for the selection e.g. a formula
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if all tests are fine
     */
    function assert_sql_list_by_ref(sql_db $db_con, object $lst_obj, object $select_obj, bool $by_source = false): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst_obj->load_sql_by_obj($db_con, $select_obj, $by_source);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst_obj->load_sql_by_obj($db_con, $select_obj, $by_source);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * check the SQL statements for loading a list of objects selected by the type in all allowed SQL database dialects
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param object $lst_obj the list object e.g. batch job list
     * @param string $type_code_id the type code id that should be used for the selection
     * @return bool true if all tests are fine
     */
    function assert_sql_list_by_type(sql_creator $sc, object $lst_obj, string $type_code_id): bool
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $lst_obj->load_sql_by_type($sc, $type_code_id, $lst_obj::class);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $lst_obj->load_sql_by_type($sc, $type_code_id, $lst_obj::class);
            $result = $this->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }


    /*
     * SQL check util
     */

    /**
     * test the SQL statement creation for a value
     *
     * @param sql_par $qp the query parameters that should be tested
     * @param string $dialect if not Postgres the name of the SQL dialect
     * @param string $test_name description of the test without the sql name
     * @return bool true if the test is fine
     */
    function assert_qp(
        sql_par $qp,
        string  $dialect = '',
        string  $test_name = ''
    ): bool
    {
        $expected_sql = $this->assert_sql_expected($qp->name, $dialect);
        $result = $this->assert_sql(
            $this->name . 'sql creation of ' . $qp->name . ' (' . $dialect . ') to ' . $test_name,
            $qp->sql . $qp->call_sql . ' ' . $qp->call,
            $expected_sql
        );

        // check if the prepared sql name is unique always based on the  Postgres query parameter creation
        if ($dialect == sql_db::POSTGRES) {
            $result = $this->assert_sql_name_unique($qp->name);
        }

        return $result;
    }

    /**
     * build the filename where the expected sql statement is saved
     *
     * @param string $name the unique name of the query
     * @param string $dialect the db dialect
     * @return string the filename including the resource path
     */
    function assert_sql_expected(string $name, string $dialect = ''): string
    {
        if ($dialect == sql_db::POSTGRES) {
            $file_name_ext = '';
        } elseif ($dialect == sql_db::MYSQL) {
            $file_name_ext = self::FILE_MYSQL;
        } else {
            $file_name_ext = $dialect;
        }
        $file_name = $this->resource_path . $name . $file_name_ext . self::FILE_EXT;
        $expected_sql = $this->file($file_name);
        if ($expected_sql == '') {
            $msg = 'File ' . $file_name . ' with the expected SQL statement is missing.';
            log_err($msg);
            $expected_sql = $msg;
        }
        return $expected_sql;
    }

    /**
     * test a SQL statement
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
     * test a SQL statement
     *
     * @param string $haystack the fixed SQL statement that is edit by hand
     * @param string $needle the created SQL statement that should be part of the hand combined sql setup script
     * @return bool true if the created SQL statement matches the expected SQL statement if the formatting is removed
     */
    function assert_sql_contains(string $name, string $haystack, string $needle): bool
    {
        $lib = new library();
        return $this->assert_text_contains($name, $lib->trim_sql($haystack), $lib->trim_sql($needle));
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


    /*
     * SQL checks to review
     */

    /**
     * similar to assert_load_sql but for the load_sql_obj_vars that
     * TODO should be replaced by assert_load_sql_id, assert_load_sql_name, assert_load_sql_all, ...
     * TODO check that all assert_load_sql_ use by more that one test are here
     * TODO in the assert_load_sql_ functions used for one test object only use the forwarded $t and $db_con vars
     *
     * check the object load SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param string $class to define the database type if it does not match the class
     * @return bool true if all tests are fine
     */
    function assert_sql_by_obj_vars(sql_db $db_con, object $usr_obj, string $class = ''): bool
    {
        if ($class == '') {
            $class = get_class($usr_obj);
        }

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_obj_vars($db_con, $class);
        $result = $this->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_obj_vars($db_con, $class);
            $result = $this->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }


    /*
     * db load
     */

    /**
     * check the object loading by id and name
     *
     * @param sandbox_named|sandbox_link_named $usr_obj the user sandbox object e.g. a word
     * @param string $name the name of the object
     * @param int $id the id of the object if not 1
     * @return bool the load object to use it for more tests
     */
    function assert_load(sandbox_named|sandbox_link_named $usr_obj, string $name = '', int $id = 1): bool
    {
        // check the loading via name and check the id
        $test_name = 'load ' . $usr_obj::class . ' by name ' . $name;
        $usr_obj->reset();
        $usr_obj->load_by_name($name);
        $result = $this->assert($test_name, $usr_obj->id(), $id);

        // ... and check the loading via id and check the name
        if ($result) {
            $test_name = 'load ' . $usr_obj::class . ' by id ' . $id;
            $usr_obj->reset();
            $usr_obj->load_by_id($id);
            $result = $this->assert($test_name, $usr_obj->name(), $name);
        }
        return $result;
    }

    function assert_load_by_code_id(sandbox_named $usr_obj, string $code_id = '', int $id = 1): bool
    {
        $test_name = 'load ' . $usr_obj::class . ' by code_id ' . $code_id;
        $usr_obj->reset();
        $usr_obj->load_by_code_id($code_id);
        return $this->assert($test_name, $usr_obj->id(), $id);
    }

    /**
     * check the object loading by id and name
     *
     * @param sandbox_link $usr_obj the user sandbox object e.g. a word
     * @param int $fid the id of the from object
     * @param int $typ the id of the link type
     * @param int|string $tid the id of the to object or the unique external key
     * @return bool the load object to use it for more tests
     */
    function assert_load_by_link(sandbox_link $usr_obj, int $fid = 0, int $typ = 1, int|string $tid = 0, int $id = 0): bool
    {
        // check the loading via name and check the id
        $lnk_id = $fid . '/' . $typ . '/' . $tid;
        $test_name = 'load ' . $usr_obj::class . ' by ' . $lnk_id;
        $usr_obj->reset();
        $usr_obj->load_by_link_id($fid, $typ, $tid);
        $result = $this->assert($test_name, $usr_obj->id(), $id);

        // ... and check the loading via id and check the name
        if ($result) {
            $test_name = 'load ' . $usr_obj::class . ' by id ' . $id;
            $usr_obj->reset();
            $usr_obj->load_by_id($id);
            $result = $this->assert($test_name, $usr_obj->link_id(), $lnk_id);
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
            $usr_obj->load_by_name($name);
            $result = $this->assert($usr_obj::class . '->load', $usr_obj->id(), 1);
        }
        return $result;
    }

    /**
     * check the object nor the id and nor the name is used
     *
     * @param sandbox_named $usr_obj the user sandbox object e.g. a word
     * @param string $name the name of the object
     * @param int $id the id of the object if not 1
     * @return bool the load object to use it for more tests
     */
    function assert_not_exist(sandbox_named $usr_obj, string $name = '', int $id = 1): bool
    {
        // check the loading via name and check the id
        $test_name = 'load ' . $usr_obj::class . ' by name ' . $name . ' returns zero';
        $usr_obj->reset();
        $usr_obj->load_by_name($name);
        $result = $this->assert($test_name, $usr_obj->id(), 0);

        // ... and check the loading via id and check the name
        if ($result) {
            $test_name = 'load ' . $usr_obj::class . ' by id ' . $id . ' returns an empty string';
            $usr_obj->reset();
            $usr_obj->load_by_id($id);
            $result = $this->assert($test_name, $usr_obj->name(), '');
        }
        return $result;
    }

    /**
     * create a sql statement to check if any user has uses another than the default value
     * e.g. word, triple, value or formulas has been renamed
     *
     * @param sql_creator $sc a sql creator object that can be empty
     * @param sandbox|sandbox_value $usr_obj the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_sql_not_changed(sql_creator $sc, sandbox|sandbox_value $usr_obj): bool
    {
        // check the Postgres query syntax
        $usr_obj->set_owner_id(0);
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->not_changed_sql($sc);
        $result = $this->assert_qp($qp, $sc->db_type);

        // ... and check with owner
        if ($result) {
            $usr_obj->set_owner_id(1);
            $qp = $usr_obj->not_changed_sql($sc);
            $result = $this->assert_qp($qp, $sc->db_type);
        }

        // ... and check the MySQL query syntax
        if ($result) {
            $usr_obj->set_owner_id(0);
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->not_changed_sql($sc);
            $result = $this->assert_qp($qp, $sc->db_type);
        }

        // ... and check with owner
        if ($result) {
            $usr_obj->set_owner_id(1);
            $qp = $usr_obj->not_changed_sql($sc);
            $result = $this->assert_qp($qp, $sc->db_type);
        }

        return $result;
    }

    /*
     * assert db write
     */

    /**
     * test adding a named sandbox object e.g. a word to the database
     * either via sql function with logging
     * or via prepared sql statement
     *
     * @param string $test_name the description of the test
     * @param sandbox_named|sandbox_link_named $sbx the sandbox object with the vars set for the test
     * @param bool $use_func true if the complex function including the logging should be used
     * @return bool true if the test has been successful
     */
    function assert_write_via_func_or_sql(string $test_name, sandbox_named|sandbox_link_named $sbx, bool $use_func): bool
    {
        // add the named object and remember the name
        $name = $sbx->name();
        $sbx->save($use_func);
        $sbx->reset();
        $sbx->load_by_name($name);
        $result = $this->assert_true($test_name, $sbx->is_loaded());

        // check the log
        if ($result) {
            $id = $sbx->id();
            if ($use_func) {
                $log_msg = $sbx->log_last_field_msg($this->usr1, $sbx->name_field());
                $result = $this->assert_text_contains($test_name . ' log add', $log_msg, $name);
                if ($result) {
                    $result = $this->assert_text_contains($test_name . ' log add', $log_msg, msg_id::LOG_ADD->value);
                }
            }
        }

        // update the name
        if ($result) {
            $sbx->set_name($name . self::EXT_RENAME);
            $sbx->save($use_func);
            $sbx->reset();
            $sbx->load_by_id($id);
            $result = $this->assert_true($test_name, $sbx->is_loaded());

        }

        // check the log
        if ($result and $use_func) {
            $log_msg = $sbx->log_last_msg($this->usr1);
            $result = $this->assert_text_contains($test_name . ' log update', $log_msg, $name);
            if ($result) {
                $result = $this->assert_text_contains($test_name . ' log update', $log_msg, msg_id::LOG_UPDATE->value);
            }
        }

        if ($result) {
            // delete the name
            $sbx->del($use_func);
        }

        // check the log
        if ($result and $use_func) {
            $log_msg = $sbx->log_last_msg($this->usr1);
            $result = $this->assert_text_contains($test_name . ' log delete', $log_msg, $name);
            if ($result) {
                $result = $this->assert_text_contains($test_name . ' log delete', $log_msg, msg_id::LOG_DEL->value);
            }
        }

        return $result;
    }

    /**
     * test the named user sandbox object by adding it
     * and simulate if different users change it
     *
     * @param sandbox_named|sandbox_link_named $sbx
     * @param string $name target name of the object
     * @return bool
     */
    function assert_write_named(sandbox_named|sandbox_link_named $sbx, string $name): bool
    {

        // check for leftovers
        $this->write_named_cleanup($sbx, $name, true);

        // remember mandatory fields
        if ($sbx::class == formula::class) {
            $usr_text = $sbx->usr_text;
            $ref_text = $sbx->ref_text;
        }

        /*
         * are all fields saved?
         */

        // add the named object for the test user 1
        $id = $this->write_named_add($sbx, $name, $this->usr1);

        // remember the api json for later compare
        $api_json = $sbx->api_json();

        // check the log
        if ($id != 0) {
            $result = $this->write_named_log($sbx, $sbx->name_field(), $name, msg_id::LOG_ADD->value);
        } else {
            $result = false;
        }

        // check reset
        if ($result) {
            $result = $this->assert_reset($sbx);
        }

        // check if user 1 can load the added object
        if ($result) {
            $result = $this->assert_load($sbx, $name, $id);
        }

        // check if no relevant fields a lost during save and reload
        if ($result) {
            $result = $this->assert('API json based compare', $sbx->api_json(), $api_json);
        }

        // check if the system reports correctly, that no one has changed the named object
        if ($result) {
            $result = $this->write_named_changed_by_noone($sbx);
        }


        /*
         * rename?
         */

        // check renaming the added object
        $new_name = '';
        if ($result) {
            $new_name = $this->write_named_rename($sbx, $id, $this->usr1);
        }

        // check the log
        if ($id != 0) {
            $result = $this->write_named_log($sbx, $sbx->name_field(), $new_name, msg_id::LOG_UPDATE->value, $name);
        } else {
            $result = false;
        }


        /*
         * rename by someone else?
         */

        // check user sandbox based on description
        $old_description = $sbx->description;
        $new_description = $old_description . self::EXT_RENAME;
        if ($result) {
            // if user 2 changes the description
            $result = $this->write_named_update_description($sbx, $this->usr2, $new_description);
        }
        if ($result) {
            // ... user 1 still see the old, because he has been owner of the standard
            $result = $this->write_named_check_description($sbx, $this->usr1, $old_description);
        }
        if ($result) {
            // ... but user 2 still see the new
            $result = $this->write_named_check_description($sbx, $this->usr2, $new_description);
        }
        if ($result) {
            // ... and the user 2 is reported as a changer
            $result = $this->write_named_check_changed_by($sbx, $this->usr2);
        }

        if ($result) {
            // if user 1 also changes the description
            $result = $this->write_named_update_description($sbx, $this->usr1, $new_description);
        }
        if ($result) {
            // ... user 1 see the new
            $result = $this->write_named_check_description($sbx, $this->usr1, $new_description);
        }
        if ($result) {
            // ... and user 2 also see the new
            $result = $this->write_named_check_description($sbx, $this->usr2, $new_description);
        }
        if ($result) {
            // if the owner changes the description and all have the same
            $result = $this->write_named_update_description($sbx, $this->usr1, $old_description);
        }
        if ($result) {
            // ... user 1 see the changed
            $result = $this->write_named_check_description($sbx, $this->usr1, $old_description);
        }
        if ($result) {
            // ... and user 2 also see the changed (TODO or not?)
            $result = $this->write_named_check_description($sbx, $this->usr2, $new_description);
        }


        /*
         * undo rename?
         */

        if ($result) {
            // if user 2 delete it
            $result = $this->write_named_del($sbx, $this->usr2);
        }
        if ($result) {
            // ... the description will be empty for user 2
            $result = $this->write_named_check_description($sbx, $this->usr2, '');
        }
        if ($result) {
            // ... but still exist for user 1
            $result = $this->write_named_check_description($sbx, $this->usr1, $old_description);
        }


        /*
         * ownership
         */

        if ($result) {
            // check if an admin can force to take over ownership
            $result = $this->write_named_ownership($sbx, $this->usr_admin, $this->usr1);
        }


        /*
         * all delete
         */

        if ($result) {
            // if the owner also deletes it
            $result = $this->write_named_del($sbx, $this->usr1);
        }
        if ($result) {
            // the name is empty and id is not used
            $result = $this->assert_not_exist($sbx, $name, $id);
        }
        if ($result) {
            // ... and the description for user 1 will also be empty
            $result = $this->write_named_check_description($sbx, $this->usr1, '');
        }


        /*
         * owner change
         */

        // restore mandetory fields
        if ($sbx::class == formula::class) {
            $sbx->usr_text = $usr_text;
            $sbx->ref_text = $ref_text;
        }

        if ($result) {
            // add the named object again for test user 1 for owner change test
            $id = $this->write_named_add($sbx, $name, $this->usr1);
        }
        // check the log
        if ($result) {
            if ($id != 0) {
                // user 2 changes the description again to be the fallback user
                $result = $this->write_named_add_description($sbx, $this->usr2, $new_description);
            } else {
                $result = false;
            }
        }

        if ($result) {
            // if the owner deletes it ...
            $result = $this->write_named_del($sbx, $this->usr1);
        }
        /*
        if ($result) {
            // ... user 2 is the owner who can change for all users
            $result = $this->write_sandbox_update_description($sbx, $this->usr2, $old_description);
        }
        if ($result) {
            // ... user 1 will also see the updated
            $result = $this->write_sandbox_check_description($sbx, $this->usr1, $old_description);
        }
        if ($result) {
            // ... and of course user 2
            $result = $this->write_sandbox_check_description($sbx, $this->usr2, $old_description);
        }

        if ($result) {
            // if the new owner deletes
            $result = $this->write_sandbox_del($sbx, $this->usr2);
        }
        */
        if ($result) {
            // the name is empty and id is not used
            $result = $this->assert_not_exist($sbx, $name, $id);
        }
        if ($result) {
            // ... and the description for user 1 will also be empty
            $result = $this->write_named_check_description($sbx, $this->usr1, '');
        }

        // fallback delete
        $this->write_named_cleanup($sbx, $name);

        return $result;
    }

    /**
     * test the link user sandbox object by adding it
     * and simulate if different users change it
     *
     * @param sandbox_link|triple $lnk
     * @param string $name target name of the object
     * @return bool
     */
    function assert_write_link(sandbox_link|triple $lnk, string $name = ''): bool
    {

        /*
         * prepare
         */

        // keep the original objects as given
        $ori = clone $lnk;

        // detect the related objects
        $fob = clone $ori->fob();
        if ($fob::class == phrase::class or $fob::class == term::class) {
            $add_from = new word($fob->user());
            $add_from->set_name($fob->name());
        } else {
            $add_from = $fob;
        }
        if ($lnk::class == ref::class) {
            $tob = $lnk->to_id();
            $add_to = $lnk->to_id();
        } else {
            $tob = clone $ori->tob();
            if ($tob::class == phrase::class or $tob::class == term::class) {
                $add_to = new word($tob->user());
                $add_to->set_name($tob->name());
            } else {
                $add_to = $tob;
            }
        }
        // check for leftovers
        $this->write_named_cleanup($add_from, $ori->from_name(), true);
        if ($lnk::class != ref::class) {
            $this->write_named_cleanup($add_to, $ori->to_name(), true);
        }
        // create the related objects
        $fid = $this->write_named_add($add_from, $fob->name(), $this->usr1);
        if ($lnk::class == ref::class) {
            $tid = $lnk->to_id();
        } else {
            $tid = $this->write_named_add($add_to, $tob->name(), $this->usr1);
        }


        /*
         * are all fields saved?
         */

        // add the named object for the test user 1
        if ($lnk::class == triple::class) {
            $id = $this->write_named_link_add($lnk, $ori, $name, $this->usr1);
        } else {
            $id = $this->write_link_add($lnk, $ori, $this->usr1);
        }

        // remember the api json for later compare
        $api_json = $lnk->api_json();

        // check the log
        if ($id != 0) {
            if ($lnk::class == triple::class) {
                $result = $this->write_named_link_log($lnk, msg_id::LOG_LINK->value);
            } else {
                $result = $this->write_link_log($lnk, msg_id::LOG_LINK->value);
            }
        } else {
            $result = false;
        }

        // check reset
        if ($result) {
            $result = $this->assert_reset($lnk);
        }

        // check if user 1 can load the added object
        if ($result) {
            $result = $this->assert_load_by_link($lnk, $fid, $ori->predicate_id(), $tid, $id);
        }

        // check if no relevant fields a lost during save and reload
        if ($result) {
            $result = $this->assert('API json based compare', $lnk->api_json(), $api_json);
        }

        /*
         * sandbox
         */

        if ($lnk::class == formula_link::class or $lnk::class == component_link::class) {
            $old_order_nbr = $lnk->order_nbr;
            $new_order_nbr = $old_order_nbr + 1;
            if ($result) {
                // if user 2 changes the order number
                $result = $this->write_link_update_order_nbr($lnk, $this->usr2, $new_order_nbr);
            }
            if ($result) {
                // ... user 1 still see the old, because he has been owner of the standard
                $result = $this->write_link_check_order_nbr($lnk, $this->usr1, $old_order_nbr);
            }
            if ($result) {
                // ... but user 2 still see the new
                $result = $this->write_link_check_order_nbr($lnk, $this->usr2, $new_order_nbr);
            }
            if ($result) {
                // if user 1 also changes the order number
                $result = $this->write_link_update_order_nbr($lnk, $this->usr1, $new_order_nbr);
            }
            if ($result) {
                // ... user 1 see the new
                $result = $this->write_link_check_order_nbr($lnk, $this->usr1, $new_order_nbr);
            }
            if ($result) {
                // ... and user 2 also see the new
                $result = $this->write_link_check_order_nbr($lnk, $this->usr2, $new_order_nbr);
            }
            if ($result) {
                // if the owner changes the order number and all have the same
                $result = $this->write_link_update_order_nbr($lnk, $this->usr1, $old_order_nbr);
            }
            if ($result) {
                // ... user 1 see the changed
                $result = $this->write_link_check_order_nbr($lnk, $this->usr1, $old_order_nbr);
            }
            if ($result) {
                // ... and user 2 also see the changed (TODO or not?)
                $result = $this->write_link_check_order_nbr($lnk, $this->usr2, $new_order_nbr);
            }
        } elseif ($lnk::class == term_view::class
            or $lnk::class == ref::class
            or $lnk::class == triple::class) {
            $old_description = $lnk->description;
            $new_description = $old_description . self::EXT_RENAME;
            if ($result) {
                // if user 2 changes the description
                $result = $this->write_link_update_description($lnk, $this->usr2, $new_description);
            }
            if ($result) {
                // ... user 1 still see the old, because he has been owner of the standard
                $result = $this->write_link_check_description($lnk, $this->usr1, $old_description);
            }
            if ($result) {
                // ... but user 2 see the new description
                $result = $this->write_link_check_description($lnk, $this->usr2, $new_description);
            }
            if ($result) {
                // if user 1 also changes the description
                $result = $this->write_link_update_description($lnk, $this->usr1, $new_description);
            }
            if ($result) {
                // ... user 1 see the new
                $result = $this->write_link_check_description($lnk, $this->usr1, $new_description);
            }
            if ($result) {
                // ... and user 2 also see the new
                $result = $this->write_link_check_description($lnk, $this->usr2, $new_description);
            }
            if ($result) {
                // if the owner changes the description and all have the same
                $result = $this->write_link_update_description($lnk, $this->usr1, $old_description);
            }
            if ($result) {
                // ... user 1 see the changed
                $result = $this->write_link_check_description($lnk, $this->usr1, $old_description);
            }
            if ($result) {
                // ... and user 2 also see the changed (TODO or not?)
                $result = $this->write_link_check_description($lnk, $this->usr2, $new_description);
            }
        } else {
            log_err('update test field for ' . $lnk::class . ' not yet defined');
        }


        // cleanup
        $this->write_link_cleanup($lnk, $id);
        $this->write_named_cleanup($ori->fob(), $ori->from_name());
        if ($lnk::class != ref::class) {
            $this->write_named_cleanup($add_to, $ori->to_name());
        }


        return $result;
    }


    /*
     * write test cleanup
     */

    /**
     * remove all remaining test rows of a named user sandbox object
     *
     * @param sandbox_named|sandbox_link_named|phrase $sbx the named user sandbox object e.g. a word
     * @param string $name the name of the user sandbox object that should be removed
     * @param bool $check if true an error message is created if the object needs to be removed
     *                    e.g. to detect incomplete cleanup of previous tests
     * @return void
     */
    function write_named_cleanup(sandbox_named|sandbox_link_named|phrase $sbx, string $name, bool $check = false): void
    {
        $this->write_named_cleanup_one($sbx, $this->usr1, $name, $check);
        $this->write_named_cleanup_one($sbx, $this->usr2, $name, $check);
        $this->write_named_cleanup_one($sbx, $this->usr1, $name . self::EXT_RENAME, $check);
        $this->write_named_cleanup_one($sbx, $this->usr2, $name . self::EXT_RENAME, $check);
    }

    /**
     * remove remaining test rows for one name and one user
     *
     * @param sandbox_named|sandbox_link_named|phrase $sbx the named user sandbox object e.g. a word
     * @param string $name the name of the user sandbox object that should be removed
     * @param user $usr the user configuration of this user should be removed
     * @param bool $check if true an error message is created if the object needs to be removed
     *                    e.g. to detect incomplete cleanup of previous tests
     * @return void
     */
    private function write_named_cleanup_one(
        sandbox_named|sandbox_link_named|phrase $sbx,
        user                                    $usr,
        string                                  $name,
        bool                                    $check = false
    ): void
    {
        $sbx->set_user($this->usr1);
        $sbx->load_by_name($name);
        if ($check) {
            if ($sbx->id() != 0) {
                log_warning('Unexpected cleanup of ' . $sbx->dsp_id());
            }
        }
        $sbx->del();
    }

    /**
     * remove all remaining link test rows without test
     *
     * @param sandbox_link $lnk the link objecz that should be deleted
     * @param int $id the id of the link object
     * @param bool $check if true an error message is created if the object needs to be removed
     * *                    e.g. to detect incomplete cleanup of previous tests
     * @return void
     */
    function write_link_cleanup(sandbox_link $lnk, int $id, bool $check = false): void
    {
        $lnk->set_user($this->usr1);
        $lnk->load_by_id($id);
        if ($check) {
            if ($lnk->id() != 0) {
                log_err('Unexpected cleanup of ' . $lnk->dsp_id());
            }
        }
        $lnk->del();
        $lnk->set_user($this->usr2);
        $lnk->load_by_id($id);
        if ($check) {
            if ($lnk->id() != 0) {
                log_err('Unexpected cleanup of ' . $lnk->dsp_id());
            }
        }
        $lnk->del();
    }


    /*
     * write test internal support
     */

    /**
     * add a named object to the database for the given user
     *
     * @param sandbox_named|sandbox_link_named $sbx
     * @param string $name
     * @param user $usr
     * @return int the id of the added object
     */
    private function write_named_add(sandbox_named|sandbox_link_named $sbx, string $name, user $usr): int
    {
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'add ' . $class . ' ' . $name . ' for user ' . $usr->dsp_id();
        $sbx->set_user($usr);
        $sbx->set_name($name);
        $result = $sbx->save()->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            return $sbx->id();
        } else {
            return 0;
        }
    }

    private function write_named_link_add(triple $sbx, triple $ori, string $name, user $usr): int
    {
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'add ' . $class . ' ' . $ori->dsp_id() . ' for user ' . $usr->dsp_id();

        $fob = clone $ori->fob();
        $fob->load_by_name($fob->name());
        $tob = clone $ori->tob();
        $tob->load_by_name($tob->name());
        $sbx->set_user($usr);
        $sbx->set_fob($fob);
        $sbx->set_tob($tob);
        $sbx->set_name($name);
        $sbx->set_predicate_id($ori->predicate_id());
        $result = $sbx->save()->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            return $sbx->id();
        } else {
            return 0;
        }
    }

    private function write_link_add(sandbox_link|ref $sbx, sandbox_link|ref $ori, user $usr): int
    {
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'add ' . $class . ' ' . $ori->dsp_id() . ' for user ' . $usr->dsp_id();

        $sbx->set_user($usr);
        $fob = clone $ori->fob();
        $fob->load_by_name($fob->name());
        $sbx->set_fob($fob);
        if ($ori::class == ref::class) {
            $sbx->set_to_id($ori->to_id());
        } else {
            $tob = clone $ori->tob();
            $tob->load_by_name($tob->name());
            $sbx->set_tob($tob);
        }
        $sbx->set_predicate_id($ori->predicate_id());
        $result = $sbx->save()->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            return $sbx->id();
        } else {
            return 0;
        }
    }

    /**
     * check if changing a named user sandbox object has been logged correctly
     *
     * @param sandbox_named|sandbox_link_named $sbx
     * @param string $fld
     * @param string $name
     * @param string $action
     * @param string|null $old_name
     * @return bool
     */
    private function write_named_log(
        sandbox_named|sandbox_link_named $sbx,
        string                           $fld,
        string                           $name,
        string                           $action,
        ?string                          $old_name = ''
    ): bool
    {
        $lib = new library();
        $result = $this->log_last_by_field($sbx, $fld, $sbx->id(), true);
        $target = $sbx->user()->name() . ' ' . $action . ' "';
        if ($action == msg_id::LOG_UPDATE->value) {
            $target .= $old_name . '" to "' . $name . '"';
        } else {
            $target .= $name . '"';
        }
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'check ' . $class . ' log of ' . $action . ' ' . $name;
        return $this->assert($test_name, $result, $target);
    }

    private function write_named_link_log(
        triple $lnk,
        string $action
    ): bool
    {
        $log = new change_link($lnk->user());
        $lib = new library();
        $tbl_name = $lib->class_to_table($lnk::class);
        $log->set_table($tbl_name);
        $log->row_id = $lnk->id();
        $result = $log->dsp_last(true);
        $target = $lnk->user()->name() . ' ' . $action . ' ';
        $target .= $lnk->from_name() . ' to ';
        $target .= $lnk->to_name();
        $class = $lib->class_to_name($lnk::class);
        $test_name = 'check ' . $class . ' log of ' . $action . ' ' . $lnk->dsp_id();
        return $this->assert($test_name, $result, $target);
    }

    private function write_link_log(
        sandbox_link $lnk,
        string       $action
    ): bool
    {
        $log = new change_link($lnk->user());
        $lib = new library();
        $tbl_name = $lib->class_to_table($lnk::class);
        $log->set_table($tbl_name);
        $log->row_id = $lnk->id();
        $result = $log->dsp_last(true);
        $target = $lnk->user()->name() . ' ' . $action . ' ';
        $target .= $lnk->from_name() . ' to ';
        $target .= $lnk->to_name();
        $class = $lib->class_to_name($lnk::class);
        $test_name = 'check ' . $class . ' log of ' . $action . ' ' . $lnk->dsp_id();
        return $this->assert($test_name, $result, $target);
    }

    /**
     * rename a named user sandbox object and check if the name has really been changed
     *
     * @param sandbox_named|sandbox_link_named $sbx
     * @param int $id
     * @param user $usr
     * @return string
     */
    private function write_named_rename(sandbox_named|sandbox_link_named $sbx, int $id, user $usr): string
    {
        $sbx->set_user($usr);
        $sbx->load_by_id($id);
        $name = $sbx->name();
        $new_name = $name . self::EXT_RENAME;
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'rename ' . $class . ' ' . $name . ' to ' . $new_name . ' for user ' . $usr->dsp_id();
        $sbx->set_name($new_name);
        $result = $sbx->save()->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            $sbx->reset();
            $sbx->load_by_name($new_name);
            if ($sbx->id() == $id) {
                if ($this->assert_load($sbx, $new_name, $id)) {
                    return $sbx->name();
                } else {
                    return '';
                }
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    private function write_named_add_description(sandbox_named|sandbox_link_named $sbx, user $usr, string $description): bool
    {
        $id = $sbx->id();
        $sbx->set_user($usr);
        $sbx->load_by_id($id);
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'add ' . $class . ' description ' . $description;
        $sbx->description = $description;
        $result = $sbx->save()->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            return $this->write_named_log($sbx, sandbox_named::FLD_DESCRIPTION, $description, msg_id::LOG_ADD);
        } else {
            return false;
        }
    }

    private function write_named_update_description(sandbox_named|sandbox_link_named $sbx, user $usr, string $new_description): bool
    {
        $id = $sbx->id();
        $sbx->set_user($usr);
        $sbx->load_by_id($id);
        $old_description = $sbx->description;
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'update ' . $class . ' description to ' . $new_description;
        $sbx->description = $new_description;
        $result = $sbx->save()->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            return $this->write_named_log($sbx,
                sandbox_named::FLD_DESCRIPTION, $new_description, msg_id::LOG_UPDATE->value, $old_description);
        } else {
            return false;
        }
    }

    private function write_named_check_description(sandbox_named|sandbox_link_named $sbx, user $usr, ?string $description): bool
    {
        $id = $sbx->id();
        $sbx->set_user($usr);
        $sbx->load_by_id($id);
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = $class . ' description for user ' . $usr->dsp_id() . ' is ' . $description;
        if ($this->assert($test_name, $sbx->description(), $description, $this::TIMEOUT_LIMIT_DB)) {
            return true;
        } else {
            return false;
        }
    }

    private function write_link_update_order_nbr(formula_link|component_link $lnk, user $usr, int $new_order_nbr): bool
    {
        $id = $lnk->id();
        $lnk->set_user($usr);
        $lnk->load_by_id($id);
        $old_order_nbr = $lnk->order_nbr;
        $lib = new library();
        $class = $lib->class_to_name($lnk::class);
        $test_name = 'update ' . $class . ' order number to ' . $new_order_nbr;
        $lnk->order_nbr = $new_order_nbr;
        $result = $lnk->save()->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            return $this->write_link_log_field($lnk,
                formula_link::FLD_ORDER, $new_order_nbr, msg_id::LOG_UPDATE->value, $old_order_nbr);
        } else {
            return false;
        }
    }

    private function write_link_check_order_nbr(formula_link|component_link $lnk, user $usr, ?string $order_nbr): bool
    {
        $id = $lnk->id();
        $lnk->set_user($usr);
        $lnk->load_by_id($id);
        $lib = new library();
        $class = $lib->class_to_name($lnk::class);
        $test_name = $class . ' order number for user ' . $usr->dsp_id() . ' is ' . $order_nbr;
        if ($this->assert($test_name, $lnk->order_nbr, $order_nbr, $this::TIMEOUT_LIMIT_DB)) {
            return true;
        } else {
            return false;
        }
    }

    private function write_link_update_description(term_view|ref|triple $lnk, user $usr, string $new_description): bool
    {
        $id = $lnk->id();
        $lnk->set_user($usr);
        $lnk->load_by_id($id);
        $old_description = $lnk->description;
        $lib = new library();
        $class = $lib->class_to_name($lnk::class);
        $test_name = 'update ' . $class . ' description to ' . $new_description;
        $lnk->description = $new_description;
        $result = $lnk->save()->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            return $this->write_link_log_field($lnk,
                sandbox_named::FLD_DESCRIPTION, $new_description, msg_id::LOG_UPDATE->value, $old_description);
        } else {
            return false;
        }
    }

    private function write_link_check_description(term_view|ref|triple $lnk, user $usr, ?string $description): bool
    {
        $id = $lnk->id();
        $lnk->set_user($usr);
        $lnk->load_by_id($id);
        $lib = new library();
        $class = $lib->class_to_name($lnk::class);
        $test_name = $class . ' description for user ' . $usr->dsp_id() . ' is ' . $description;
        if ($this->assert($test_name, $lnk->description, $description, $this::TIMEOUT_LIMIT_DB)) {
            return true;
        } else {
            return false;
        }
    }

    private function write_link_log_field(
        sandbox_link $sbx,
        string       $fld,
        string       $name,
        string       $action,
        ?string      $old_name = ''
    ): bool
    {
        $lib = new library();
        $result = $this->log_last_by_field($sbx, $fld, $sbx->id(), true);
        $target = $sbx->user()->name() . ' ' . $action . ' "';
        if ($action == msg_id::LOG_UPDATE->value) {
            $target .= $old_name . '" to "' . $name . '"';
        } else {
            $target .= $name . '"';
        }
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'check ' . $class . ' log of ' . $action . ' ' . $name;
        return $this->assert($test_name, $result, $target);
    }

    private function write_named_del(sandbox_named|sandbox_link_named $sbx, user $usr): bool
    {
        $id = $sbx->id();
        $name = $sbx->name();
        $sbx->set_user($usr);
        $sbx->load_by_id($id);
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = 'del ' . $class . ' ' . $name . ' for user ' . $usr->dsp_id();
        $msg = $sbx->del();
        $result = $msg->get_last_message();
        if ($this->assert($test_name, $result, '', $this::TIMEOUT_LIMIT_DB)) {
            return $this->write_named_log($sbx, $sbx->name_field(), $name, msg_id::LOG_DEL->value);
        } else {
            return false;
        }
    }

    private function write_named_changed_by_noone(
        sandbox_named|sandbox_link_named $sbx
    ): bool
    {
        // check if noone has changed it
        $usr_lst = $sbx->changed_by();

        if ($usr_lst->is_empty()) {
            return true;
        } else {
            return false;
        }
    }

    private function write_named_check_changed_by(
        sandbox_named|sandbox_link_named $sbx,
        user                             $usr
    ): bool
    {
        $test_name = 'user ' . $usr->dsp_id() . ' as reported as changer';
        $usr_lst = $sbx->changed_by();
        return $this->assert_contains($test_name, $usr_lst->names(), $usr->name());
    }

    private function write_named_ownership(
        sandbox_named|sandbox_link_named $sbx,
        user                             $admin,
        user                             $usr
    ): bool
    {
        // check if an admin can force to take over ownership
        $result = $sbx->take_ownership($admin);

        // check if taking ownership is rejected for normal user
        if ($result) {
            $result = !$sbx->take_ownership($usr);
        }

        return $result;
    }

    /**
     * check if all test objects that are using a fixed db id for testing are at the expected row in the database
     * @param string $test_name the name of the test
     * @param array $id_lst a two-dimensional array with the id and the name of the objects
     * @param sandbox_named|sandbox_link_named $sbx the named object for compare
     * @param sandbox_list_named $lst the named list object for db read
     * @return bool
     */
    function assert_db_test_id_list(
        string                           $test_name,
        array                            $id_lst,
        sandbox_named|sandbox_link_named $sbx,
        sandbox_list_named               $lst
    ): bool
    {
        // convert to a key value array
        $in_lst = [];
        foreach ($id_lst as $item) {
            $in_lst[$item[0]] = $item[1];
        }
        natcasesort($in_lst);
        $names = array_values($in_lst);

        // load list by the names to get the ids
        $lst->load_by_names($names);

        // check
        $db_lst = $lst->name_id_list();
        natcasesort($db_lst);

        $result = '';
        if ($db_lst != $in_lst) {
            $lib = new library();
            $result = $lib->diff_msg($in_lst, $db_lst);
            if ($result != '') {
                log_warning($test_name . 'diff is:' . $result);
            }
        }

        return $this->assert($test_name, $result, '');
    }

    /**
     * check if calling the reset function clear all relevant vars of the object
     * @param sandbox_named|sandbox_link|sandbox_value $sbx the object filled with all vars
     * @return bool true if the reset object creates an empty api message
     */
    function assert_reset(sandbox_named|sandbox_link|sandbox_value $sbx): bool
    {
        $lib = new library();
        $class = $lib->class_to_name($sbx::class);
        $test_name = $class . ' reset creates empty api json';
        $sbx->reset();
        $api_json = $sbx->api_json([api_type::TEST_MODE]);
        return $this->assert($test_name, $api_json, '{"id":0}');
    }


    /*
     * type id
     */

    function assert_verb_id(string $code_id, int $id, string $test_name): int
    {
        global $vrb_cac;
        $vrb_is_id = $vrb_cac->id($code_id);
        if ($this->assert($test_name, $vrb_is_id, $id)) {
            return $vrb_is_id;
        } else {
            return 0;
        }
    }


    /*
     * compare
     */

    /**
     * test if a integer is greater zero
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
     * should be replaced with the assert function
     *
     * @param string $test_name the message show to the admin / developer to identify the test
     * @param string|array|null $target the expected result
     * @param string|array|null $result the actual result
     * @param float $exe_max_time the expected time to create the result to identify unexpected slow functions
     * @param string $comment optional and additional information to explain the test
     * @param string $test_type 'contains' to check only that the expected target is part of the actual result
     * @return bool true if the test result is fine
     */
    function display(
        string            $test_name,
        string|array|null $target,
        string|array|null $result,
        float             $exe_max_time = self::TIMEOUT_LIMIT,
        string            $comment = '',
        string            $test_type = ''): bool
    {

        // init the test result vars
        $lib = new library();
        $msg = '';

        // precheck
        if ($target === null) {
            $msg = 'target should not be null';
        }
        if ($result === null) {
            $msg = 'result should not be null';
        }

        if ($msg == '') {
            // do the compare depending on the type
            if (is_string($result)) {
                $result = $this->test_remove_color($result);
            }
            if ($test_type == self::TEST_TYPE_CONTAINS) {
                $msg = $lib->explain_missing($result, $target);
            } else {
                $msg = $lib->diff_msg($result, $target);
            }

            // explain the check
            if ($msg != '') {
                if (is_array($target)) {
                    if ($test_type == self::TEST_TYPE_CONTAINS) {
                        $msg .= " should contain \"" . $lib->dsp_array($target) . "\"";
                    } else {
                        $msg .= " should be \"" . $lib->dsp_array($target) . "\"";
                    }
                } else {
                    if ($test_type == self::TEST_TYPE_CONTAINS) {
                        $msg .= " should contain \"" . $target . "\"";
                    } else {
                        $msg .= " should be \"" . $target . "\"";
                    }
                }
                if ($result == $target) {
                    if ($test_type == self::TEST_TYPE_CONTAINS) {
                        $msg .= " and it contains ";
                    } else {
                        $msg .= " and it is ";
                    }
                } else {
                    if ($test_type == self::TEST_TYPE_CONTAINS) {
                        $msg .= ", but ";
                    } else {
                        $test_name .= ", but it is ";
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
                }
                if ($comment <> '') {
                    $msg .= ' (' . $comment . ')';
                }
            }
        }

        if ($msg == '') {
            $test_result = true;
        } else {
            $test_result = false;
        }

        return $this->assert_dsp($test_name, $test_result, $target, $result, $msg, $exe_max_time);
    }

    /**
     * create the html code to display a unit test result
     *
     * @param string $test_name the message that describes the test for the developer
     * @param bool $test_result true if the test is fine
     * @param string|array|null $target the expected result (added here just for fast debugging)
     * @param string|array|null $result the actual result (added here just for fast debugging)
     * @param float $exe_max_time the expected time to create the result to identify unexpected slow functions
     * @return bool true if the test result is fine
     */
    private function assert_dsp(
        string            $test_name,
        bool              $test_result,
        string|array|null $target = '',
        string|array|null $result = '',
        string            $diff_msg = '',
        float             $exe_max_time = self::TIMEOUT_LIMIT): bool
    {
        // calculate the execution time
        $new_start_time = microtime(true);
        $since_start = $new_start_time - $this->exe_start_time;

        // display the result
        $final_msg = '';
        if ($test_result) {
            // check if executed in a reasonable time and if the result is fine
            if ($since_start > $exe_max_time) {
                if ($this->format == format::TEXT) {
                    $final_msg .= 'timeout ' . $test_name;
                } else {
                    $final_msg .= '<p style="color:orange">timeout</p><p>' . $test_name;
                }
                $this->timeout_counter++;
            } else {
                if ($this->level == level::ALL) {
                    if ($this->format == format::TEXT) {
                        $final_msg .= 'ok ' . $test_name;
                    } else {
                        $final_msg .= '<p style="color:green">ok</p><p>' . $test_name;
                    }
                }
                $test_result = true;
            }
        } else {
            if (is_array($result)) {
                $lib = new library();
                $result = $lib->dsp_array($result);
            }
            if (is_array($target)) {
                $lib = new library();
                $target = $lib->dsp_array($target);
            }
            if ($this->format == format::TEXT) {
                $final_msg .= 'ERROR ' . $test_name . ': ' . "\n";
            } else {
                $final_msg .= '<p style="color:red">ERROR</p>' . "\n";
                $final_msg .= '<p>' . $test_name . ': ' . "\n";
            }
            if ($diff_msg != '') {
                $final_msg .= 'diff: ' . $diff_msg . ', ' . "\n";
            }
            $final_msg .= 'actual: ' . $result . ', ' . "\n";
            $final_msg .= 'expected: ' . $target . "\n";
            $this->error_counter++;
            // TODO: create a ticket after version 0.1 where hopefully more than one developer is working on the project
        }

        // show the execution time
        if ($final_msg != '') {
            $final_msg = $this->time_stamp() . $final_msg;
            $final_msg .= ', took ';
            $final_msg .= round($since_start, 4) . ' seconds';

            // --- and finally display the test result
            if ($this->format == format::HTML) {
                $final_msg .= '</p>';
            }
            echo $final_msg;
            echo "\n";
        }
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
        float  $exe_max_time = self::TIMEOUT_LIMIT,
        string $comment = ''): bool
    {
        if (!str_contains($result, $target) and $result != '' and $target != '') {
            $result = $target . ' not found in ' . $result;
        } else {
            $result = $target;
        }
        return $this->display($test_text, $target, $result, $exe_max_time, $comment, 'contains');
    }


    function dsp_web_test(string $url_path, string $must_contain, string $msg, bool $is_connected = true): bool
    {
        $msg_net_off = 'Cannot gat the policy, probably not connected to the internet';
        if ($is_connected) {
            try {
                $result = file_get_contents(self::URL . $url_path);
            } catch (Exception $e) {
                $result = false;
                $msg_net_off .= ': ' . $e->getMessage();
            }
            if ($result === false) {
                $this->dsp_warning($msg_net_off);
                $is_connected = false;
            } else {
                $this->dsp_contains($msg, $must_contain, $result, self::TIMEOUT_LIMIT_PAGE_SEMI);
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
        $result = str_replace('<p class="' . styles::STYLE_USER . '">', '', $result);
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
        global $errors;
        global $sys_times;

        echo "\n";
        $since_start = microtime(true) - $this->start_time;
        echo round($since_start, 4) . ' seconds for testing zukunft.com';
        echo ' (' . $sys_times->report($since_start) . ')';
        echo "\n";
        echo $this->total_tests . ' test cases';
        echo "\n";
        echo $this->timeout_counter . ' timeouts';
        echo "\n";
        echo $this->error_counter . ' test errors';
        echo "\n";
        echo $errors . ' internal errors';
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
        if ($usr_obj::class == user::class) {
            if ($usr_obj->id() == 0) {
                $usr_obj->set_id($this->next_seq_nbr());
            }
        } elseif ($usr_obj::class == word::class
            or $usr_obj::class == triple::class
            or $usr_obj::class == verb::class
            or $usr_obj::class == view::class
            or $usr_obj::class == component::class
            or $usr_obj::class == source::class
            or $usr_obj::class == ref::class) {
            if ($usr_obj->id() == 0) {
                $usr_obj->set_id($this->next_seq_nbr());
            }
        } elseif ($usr_obj::class == value::class
            or $usr_obj::class == result::class) {
            $this->set_val_id_for_unit_tests($usr_obj);
        } elseif ($usr_obj::class == formula::class) {
            $this->set_frm_id_for_unit_tests($usr_obj);
        } elseif ($usr_obj::class == word_list::class
            or $usr_obj::class == triple_list::class
            or $usr_obj::class == phrase_list::class
            or $usr_obj::class == view_list::class
            or $usr_obj::class == component_list::class
            or $usr_obj::class == formula_list::class) {
            foreach ($usr_obj->lst() as $wrd) {
                if ($wrd->id() == 0) {
                    $wrd->set_id($this->next_seq_nbr());
                }
            }
        } elseif ($usr_obj::class == value_list::class
            or $usr_obj::class == result_list::class) {
            foreach ($usr_obj->lst() as $val) {
                $this->set_val_id_for_unit_tests($val);
            }
        } else {
            log_fatal('set id for unit tests not yet coded for ' . $usr_obj::class . ' object', 'set_id_for_unit_tests');
        }
    }

    /**
     * only for unit testing: set the id of a value model object
     * @param value_base|result $val the value or result object that
     * @return void nothing because the value object a modified
     */
    private function set_val_id_for_unit_tests(value_base|result $val): void
    {
        if (!$val->is_id_set()) {
            $val->set_id($this->next_seq_nbr());
        }
        if (!$val->grp()->is_id_set()) {
            $val->grp()->set_id($this->next_seq_nbr());
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

    /**
     * short text description of the last change of the given user
     * @param user|null $usr the user for whom the log entries should be selected
     * @return string the last log entry that the given user has done on a named object
     */
    function log_last_by_user(?user $usr = null): string
    {
        if ($usr == null) {
            $usr = $this->usr1;
        }
        $log = new change($this->usr1);
        $log->load_by_user($this->usr1);
        $log_dsp = new change_dsp($log->api_json());
        return $log_dsp->dsp(true);
    }

    /**
     * short text description of the
     * last change of the given named sandbox object and further
     * selected by the field and value if given
     * @param sandbox|sandbox_multi $sbx the sandbox object that should be used to filter the changes
     * @param string $fld the name if the field that should be used to filter the changes
     * @param int|string|null $id the field value if the given field name
     * @param bool $ex_time true if the change time should not be included in the text
     * @param bool $usr_only true if only user specific changes should be shown
     * @return string the last log entry that the given user has done on a named object
     */
    function log_last_by_field(
        sandbox|sandbox_multi $sbx,
        string                $fld = '',
        int|string|null       $id = null,
        bool                  $ex_time = false,
        bool                  $usr_only = false
    ): string
    {
        // TODO maybe use log_object?
        if ($sbx->is_value_obj()) {
            $log = $sbx->log_value_object();
        } else {
            $log = $sbx->log_object();
        }
        $log->load_by_field_row($sbx::class, $fld, $id, $usr_only);
        $log_dsp = new change_dsp($log->api_json());
        return $log_dsp->dsp($ex_time);
    }


    /*
     * resources
     */

    /**
     * @param string $test_resource_path the path of the file staring from the test resource path
     * @return string the content of the test resource file
     */
    function file(string $test_resource_path): string
    {
        $result = '';
        $filepath = test_files::RESOURCE_PATH . $test_resource_path;
        if ($this->has_file($test_resource_path)) {
            $result = file_get_contents($filepath);
            if ($result === false) {
                log_err('Cannot get file from ' . $filepath);
            }
        } else {
            log_err('file ' . $filepath . ' does not exist');
        }
        return $result;
    }

    /**
     * @param string $test_resource_path the path of the file staring from the test resource path
     * @return bool true if the test resource file exists
     */
    function has_file(string $test_resource_path): bool
    {
        return file_exists(test_files::RESOURCE_PATH . $test_resource_path);
    }

}


// -----------------------------------------------
// testing functions to create the main time value
// -----------------------------------------------

function zu_test_time_setup(test_cleanup $t): string
{
    global $db_con;

    $cfg = new config();
    $result = '';
    $this_year = intval(date('Y'));
    $prev_year = '';
    $test_years = intval($cfg->get_db(config::TEST_YEARS, $db_con));
    if ($test_years == '') {
        log_warning('Configuration of test years is missing', 'test_base->zu_test_time_setup');
    } else {
        $start_year = $this_year - $test_years;
        $end_year = $this_year + $test_years;
        for ($year = $start_year; $year <= $end_year; $year++) {
            $this_year = $year;
            $t->test_word(strval($this_year));
            $wrd_lnk = $t->test_triple(words::YEAR_CAP, verbs::IS, $this_year);
            $result = $wrd_lnk->name();
            if ($prev_year <> '') {
                $t->test_triple($prev_year, verbs::FOLLOW, $this_year);
            }
            $prev_year = $this_year;
        }
    }

    return $result;
}
