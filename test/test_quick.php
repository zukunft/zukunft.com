<?php

/*

    test_quick.php - TESTing of selected integration tests
    --------------

    similar to test.php but only selecting critical parts for faster testing

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

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;

// load the main functions
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database and display header
$db_con = prg_start("test_quick");

// load the testing base functions
include_once '../src/test/php/utils/test_base.php';

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

        // run the unit tests without database connection (is so fast, that it can be tested always)
        $t->run_unit();

        // reload the setting lists after using dummy list for the unit tests
        $db_con->close();
        $db_con = prg_restart("reload cache after unit testing");

        // switch to the test user
        $usr = new user;
        $usr->load_user_by_profile(user::SYSTEM_TEST_PROFILE_CODE_ID, $db_con);
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

            $usr->load_user_by_profile(user::SYSTEM_TEST_PROFILE_CODE_ID, $db_con);
        }
        if ($usr->id() > 0) {

            // create the testing users
            $t->set_users();

            // cleanup also before testing to remove any leftovers
            $t->cleanup_check();

            // -----------------------------------------------
            // start testing the selected system functionality
            // -----------------------------------------------

            load_usr_data();
            $t->run_unit_db_tests($t);

            run_system_test($t);
            run_user_test($t);

            // test the api write functionality
            $t->test_api_write_no_rest_all();
            $t->test_api_write_all();

            /*
            create_test_words($t);
            create_test_phrases($t);
            create_test_sources($t);
            create_base_times($t);
            create_test_formulas($t);
            create_test_formula_links($t);
            create_test_views($t);
            create_test_view_components($t);
            create_test_view_component_links($t);
            create_test_values($t);

            run_db_link_test($t);
            (new string_unit_tests)->run($t); // test functions not yet split into single unit tests
            run_math_test($t);
            run_word_tests($t);
            //run_word_ui_test($t);
            run_word_display_test($t);
            run_word_list_test($t);
            run_triple_test($t);
            run_ref_test($t);
            run_phrase_test($t);
            run_phrase_group_test($t);
            run_phrase_group_list_test($t);
            run_graph_test($t);
            run_verb_test($t);
            run_term_test($t);
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
            */

            //import_base_config();

            // testing cleanup to remove any remaining test records
            //$t->cleanup();

            // start the integration tests by loading the base and sample data
            //run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $t);

            // display the test results
            $t->dsp_result_html();
            $t->dsp_result();
        }
    }
}

// Closing connection
prg_end($db_con);