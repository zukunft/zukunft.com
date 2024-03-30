<?php

/*

    test/php/utils/all_tests.php - the main test object for all tests (unit, read, write, api, ui and connection)
    ----------------------------
    
    combines unit, read, write, api, ui and connection tests


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

namespace test;

// used paths
const ROOT_PATH = __DIR__;
const TEST_PATH = ROOT_PATH . '/../'; // the test base path
const UNIT_WRITE_PATH = TEST_PATH . 'unit_write/'; // for the unit tests that save to database (and cleanup the test data after completion)

include_once UNIT_WRITE_PATH . 'all_unit_write_tests.php';

use cfg\import\import_file;
use cfg\user;
use unit\lib_tests;
use unit_write\all_unit_write_tests;

class all_tests extends all_unit_write_tests
{
    function run_all_tests(): void
    {
        global $db_con;
        global $usr;
        global $errors;

        $errors = 0;
        $error_limit = 0;
        $this->header('Start all the zukunft.com tests');

        // run the unit tests without database connection
        $this->run_unit();

        // reload the setting lists after using dummy list for the unit tests
        if ($errors <= $error_limit) {
            $db_con->close();
            $db_con = prg_restart("reload cache after unit testing");
        }

        // create the testing users
        if ($errors <= $error_limit) {
            $this->set_users();
            $usr = $this->usr1;
        }

        // check that the main database test entries are still active
        if ($errors <= $error_limit) {
            $this->create_test_db_entries($this);
        }

        // run the unit database tests
        if ($errors <= $error_limit) {
            $this->init_unit_db_tests();
            $this->usr1->load_usr_data();
            $this->run_unit_db_tests($this);
        }

        // cleanup also before testing to remove any leftovers
        if ($errors <= $error_limit) {
            $this->clean_up_unit_db_tests();
        }

        // switch to the test user
        // create the system user before the local user and admin to get the desired database id
        if ($errors <= $error_limit) {
            $usr = new user;
            $usr->load_by_profile_code(user::SYSTEM_TEST_PROFILE_CODE_ID, $db_con);
            if ($usr->id() <= 0) {

                // but only from localhost
                $ip_addr = '';
                if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
                    $ip_addr = $_SERVER['REMOTE_ADDR'];
                }
                if ($ip_addr == user::SYSTEM_LOCAL) {
                    $db_con->import_system_users();
                }

                $usr->load_by_profile_code(user::SYSTEM_TEST_PROFILE_CODE_ID, $db_con);
            }
        }

        if ($usr->id() > 0) {

            // --------------------------------------
            // start testing the system functionality
            // --------------------------------------

            if ($errors <= $error_limit) {
                run_system_test($this);
                run_user_test($this);
            }

            // test the api write functionality
            // TODO activate Prio 2
            //$this->test_api_write_no_rest_all();
            //$this->test_api_write_all();

            if ($errors <= $error_limit) {
                run_db_link_test($this);
                run_sandbox_test($this);
            }

            if ($errors <= $error_limit) {
                (new lib_tests)->run($this); // test functions not yet split into single unit tests

                $this->run_db_write_tests($this);

                run_display_test($this);
                // TODO activate Prio 2
                // run_export_test($t);
                // run_permission_test ($t);

                // TODO add a test the checks if import returns the expected error messages e.g. if a triple has the name of a word

                run_legacy_test($this);
                run_math_test($this);
                //run_value_ui_test($t);
                //run_formula_ui_test($t);

                // TODO activate Prio 2
                //$this->run_api_test();
                //run_word_ui_test($t);
                // TODO add a test to merge a separate opened phrase Canton Zürich with Zurich (Canton)
                run_word_display_test($this);

                $import = new import_file();
                $import->import_base_config($usr);
            }

            // testing cleanup to remove any remaining test records
            $this->cleanup();

            // start the integration tests by loading the base and sample data
            // TODO activate Prio 1
            //run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $t);

            // display the test results
            $this->dsp_result_html();
            $this->dsp_result();
        }

    }

}