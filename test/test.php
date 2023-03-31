<?php

/*

    test.php - for internal code consistency TESTing
    --------

    seperated into:
    unit tests - test_units.php: for fast internal code consistency TESTing of the technical library functions without database connection
    db read tests - test_unit_db.php: for unit testing that only read from the database
    integration tests - test all processes that can be initiated by a user including database writing and database cleanup

    - target is to executes all class methods and all functions at least once
    - in case of errors in the methods automatically a ticket is opened the the table sys_log
    - with zukunft.com/error_update.php the tickets can be view and closed
    - and compares the result with the expected result
    - in case of an unexpected result also a ticket is created
    - check the correct setup of the base words, numbers and formulas
    - always synthetic reserved words and values are used for testing that are defined in each class and are removed after the tests

    TODO
    - use for testing only data that is supposed never to be used by any user e.g. use "TestWord" instead of "Company"
    - before starting the system test, check that really no user has used any test name and if create a warning and stop the test
    - use YAML export and import for testing the base sets
    - check that the order of the view items cannot be changed by another user
    - add get_xxx functions for all objects and use them
    - send daily report at 07:00 CET and report all errors via email
    - add all missing class functions with at lease one test case
    - check that a object function never changes a parameter
    e.g. if a formula object is loaded the calculation of a result should not influence the loaded ref text
    instead use a copy of the ref text for the calculation
    - check the usage of "old" functions


    classes with a test process ready for version 0.1 (at least one test case for every function)

    user_list.php
    user_log.php
    user_log_link.php
    word.php
    expression.php
    formula.php
    formula_element.php
    formula_element_list.php
    formula_element_group_list.php
    formula_element_group.php
    formula_list.php
    formula_link_list.php
    figure.php
    figure_list.php
    selector.php


    class test that does not yet have at least one test case for every function

    user.php
    user_display.php
    user_log_display.php
    word_display.php
    word_list.php
    triple.php
    triple_list.php
    phrase.php
    phrase_list.php
    phrase_group.php
    phrase_group_list.php
    verb.php
    verb_list.php
    term.php
    value.php
    value_list.php
    value_list_display.php
    source.php
    formula_link.php
    formula_value.php
    formula_value_list.php
    batch_job.php
    batch_job_list.php
    view.php
    view_display.php
    view_component.php (ex view_component)
    view_component_dsp.php
    view_component_link.php
    web/html/button.php
    display_html.php
    json.php
    xml.php


    classes that can be tested with later, because they are used mainly for testing

    system_error_log.php
    system_error_log_list.php


    classes that can be tested with later, because they are not yet used

    display_list.php
    value_phrase_link.php


    Frontend scrips that needs to be tested
    test if frontend scripts at least produce a useful result

    formula_result.php
    formula_test.php
    ..


  
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

use model\user;
use test\string_unit_tests;
use test\term_list_unit_db_tests;
use test\test_unit_read_db;

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

// TODO dismiss by refactoring phrase_list_dsp_old
include_once MODEL_PHRASE_PATH . 'phrase_list_dsp.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once SERVICE_IMPORT_PATH . 'import_file.php';

// load the testing base functions
const PHP_TEST_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_TEST_PATH . 'utils/test_base.php';

// open database and display header
$db_con = prg_start("unit and integration testing");

// load the session user parameters
$start_usr = new user;
$result = $start_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($start_usr->id() > 0) {
    if ($start_usr->is_admin()) {

        // prepare testing
        $usr = $start_usr;
        $t = new test_unit_read_db();
        $t->init_unit_db_tests();

        // run the unit tests without database connection
        $t->run_unit();

        // reload the setting lists after using dummy list for the unit tests
        $db_con->close();
        $db_con = prg_restart("reload cache after unit testing");

        // switch to the test user
        $usr = new user;
        $usr->load_by_profile_code(user::SYSTEM_TEST_PROFILE_CODE_ID, $db_con);
        if ($usr->id() <= 0) {
            // create the system user before the local user and admin to get the desired database id

            // but only from localhost
            $ip_addr = '';
            if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
                $ip_addr = $_SERVER['REMOTE_ADDR'];
            }
            if ($ip_addr == user::SYSTEM_LOCAL) {
                import_system_users();
            }

            $usr->load_by_profile_code(user::SYSTEM_TEST_PROFILE_CODE_ID, $db_con);
        }
        if ($usr->id() > 0) {

            // create the testing users
            $t->set_users();

            // cleanup also before testing to remove any leftovers
            $t->cleanup_check();

            // --------------------------------------
            // start testing the system functionality
            // --------------------------------------

            load_usr_data();
            $t->run_unit_db_tests($t);

            run_system_test($t);
            run_user_test($t);

            // test the api write functionality
            // TODO activate
            //$t->test_api_write_no_rest_all();
            //$t->test_api_write_all();

            create_test_words($t);
            test\create_test_phrases($t);
            create_test_sources($t);
            test\create_base_times($t);
            create_test_formulas($t);
            create_test_formula_links($t);
            create_test_views($t);
            create_test_view_components($t);
            create_test_view_component_links($t);
            create_test_values($t);

            run_db_link_test($t);
            run_sandbox_test($t);
            (new string_unit_tests)->run($t); // test functions not yet split into single unit tests
            run_math_test($t);
            run_word_tests($t);
            // TODO activate
            //$t->run_api_test();
            //run_word_ui_test($t);
            // TODO add a test to merge a separate opened phrase Kanton Zürich with Zurich (Canton)
            run_word_display_test($t);
            run_word_list_test($t);
            run_triple_test($t);
            run_ref_test($t);
            test\run_phrase_test($t);
            run_phrase_group_test($t);
            run_phrase_group_list_test($t);
            run_graph_test($t);
            run_verb_test($t);
            run_term_test($t);
            (new term_list_unit_db_tests)->run($t);
            run_value_test($t);
            //run_value_ui_test($t);
            run_source_test($t);
            run_expression_test($t);
            run_formula_test($t);
            run_formula_list_test($t);
            //run_formula_ui_test($t);
            run_formula_link_test($t);
            run_formula_link_list_test($t);
            run_formula_trigger_test($t);
            run_formula_value_test($t);
            run_formula_value_list_test($t);
            run_formula_element_test($t);
            run_formula_element_list_test($t);
            run_formula_element_group_test($t);
            run_batch_job_test($t);
            run_batch_job_list_test($t);
            run_view_test($t);
            run_view_component_test($t);
            run_view_component_link_test($t);
            run_display_test($t);
            run_export_test($t);
            //run_permission_test ($t);
            run_legacy_test($t);

            import_base_config($usr);

            // testing cleanup to remove any remaining test records
            $t->cleanup();

            // start the integration tests by loading the base and sample data
            run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $t);

            // display the test results
            $t->dsp_result_html();
            $t->dsp_result();
        }
    }
}

// Closing connection
prg_end($db_con);