<?php

/*

    test/php/unit_write/all_write_tests.php - add all db write tests to the test class
    ---------------------------------------
    
    the zukunft.com database write tests should test all class methods, that have not been tested by the unit and db read tests


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

namespace unit_write;

include_once SHARED_ENUM_PATH . 'user_profiles.php';
include_once SERVICE_PATH . 'config.php';
include_once TEST_CONST_PATH . 'files.php';

use cfg\import\import_file;
use cfg\system\ip_range;
use cfg\system\job;
use cfg\system\job_type_list;
use cfg\user\user;
use const\files as test_files;
use shared\enum\user_profiles;
use shared\library;
use test\all_tests;
use unit\lib_tests;
use unit_read\all_unit_read_tests;
use const test\ERROR_LIMIT;

class all_unit_write_tests extends all_unit_read_tests
{

    function run_db_write_tests(all_tests $t): void
    {
        global $usr;
        global $db_con;
        global $errors;

        $this->header('Start the zukunft.com database write tests');

        // switch to the test user
        // create the system user before the local user and admin to get the desired database id
        $usr->load_by_profile_code(user_profiles::TEST);
        if ($usr->id() <= 0) {

            // but only from localhost
            $ip_addr = '';
            if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
                $ip_addr = $_SERVER['REMOTE_ADDR'];
            }
            if ($ip_addr == user::SYSTEM_LOCAL_IP) {
                $db_con->import_system_users();
            }

            $usr->load_by_profile_code(user_profiles::TEST);
        }

        if ($usr->id() > 0) {

            // --------------------------------------
            // start testing the system functionality
            // --------------------------------------

            if ($errors <= ERROR_LIMIT) {
                run_system_test($t);
                run_user_test($t);
            }

            // test the api write functionality
            // TODO activate Prio 2
            //$this->test_api_write_no_rest_all();
            //$this->test_api_write_all();

            if ($errors <= ERROR_LIMIT) {
                run_db_link_test($t);
                run_sandbox_test($t);
            }

            if ($errors <= ERROR_LIMIT) {
                (new lib_tests)->run($t); // test functions not yet split into single unit tests

                // create the test dataset to check the basic write functions
                $t->set_users();
                $t->create_test_db_entries($t);
                // run the db write tests
                (new word_write_tests)->run($t);
                (new word_list_write_tests)->run($t);
                (new verb_write_tests)->run($t);
                (new triple_write_tests)->run($t);
                (new phrase_write_tests)->run($t);
                (new phrase_list_write_tests)->run($t);
                (new group_write_tests)->run($t);
                (new group_list_write_tests)->run($t);
                (new graph_tests)->run($t);
                (new term_write_tests)->run($t);
                //(new term_list_tests)->run($t);
                (new value_write_tests)->run($t);
                (new source_write_tests)->run($t);
                (new ref_write_tests)->run($t);
                (new expression_write_tests)->run($t);
                (new formula_write_tests)->run($t);
                (new formula_write_tests)->run_list($t);
                (new formula_link_write_tests)->run($t);
                (new formula_link_write_tests)->run_list($t);
                (new formula_trigger_tests)->run($t);
                (new result_write_tests)->run($t);
                // TODO activate Prio 1
                //(new result_write_tests)->run_list($t);
                (new element_write_tests)->run($t);
                (new element_write_tests)->run_list($t);
                (new element_group_write_tests)->run($t);
                (new job_write_tests)->run($t);
                (new job_write_tests)->run_list($t);
                (new view_write_tests)->run($t);
                (new view_link_write_tests)->run($this);
                (new component_write_tests)->run($t);
                (new component_link_write_tests)->run($t);

                // TODO activate Prio 2
                // run_export_test($t);
                // run_permission_test ($t);

                // TODO add a test the checks if import returns the expected error messages e.g. if a triple has the name of a word

                run_legacy_test($t);
                run_math_test($t);
                //run_value_ui_test($t);
                //run_formula_ui_test($t);

                // TODO activate Prio 2
                //$this->run_api_test();
                //run_word_ui_test($t);
                // TODO add a test to merge a separate opened phrase Canton Zürich with Zurich (Canton)
                run_word_display_test($t);

                $import = new import_file();
                $import->import_base_config($usr);
                $this->import_test_files($usr);
            }

            // testing cleanup to remove any remaining test records
            $t->cleanup();

            // start the integration tests by loading the base and sample data
            // TODO activate Prio 1
            //run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $t);

        }
    }

    /**
     * recreate the database to test the database setup script
     * TODO make shure that this can never be called in PROD
     *
     * @return void
     */
    function run_db_recreate(): void
    {
        global $db_con;
        global $usr;

        $this->header('Start database recreation');

        // create the testing users (needed for the reset db only run)
        $this->set_users();
        $usr = $this->usr1;

        // check if at least some database tables still exists
        $lib = new library();
        $ip_tbl_name = $lib->class_to_name(ip_range::class);
        if ($db_con->has_table($ip_tbl_name)) {
            $result = $usr->get();
        } else {
            $usr->set_id(SYSTEM_USER_ID);
            $usr->set_profile(user_profiles::ADMIN);
        }

        // remember the user
        $test_usr = clone $usr;

        // use the system user for the database updates
        if ($db_con->has_table($ip_tbl_name)) {
            $usr->load_by_id(SYSTEM_USER_ID);
        } else {
            $usr->set_id(SYSTEM_USER_ID);
            $usr->set_profile(user_profiles::ADMIN);
        }

        // drop all old database tables
        foreach (DB_TABLE_LIST as $table_name) {
            $db_con->drop_table($table_name);
        }
        $db_con->setup_db();

        // restore the test user
        $usr = clone $test_usr;

    }

    function reset_db(): void
    {
        global $usr;
        global $db_con;
        global $errors;

        // use the system user for the database updates
        $usr = new user;
        $usr->load_by_id(SYSTEM_USER_ID);
        $sys_usr = $usr;

        // run reset the main database tables
        $db_con->run_db_truncate($sys_usr);
        $db_con->truncate_table_all();
        $db_con->reset_seq_all();
        $db_con->reset_config();
        $db_con->import_system_users();

        // recreate the code link database rows
        $db_con->db_fill_code_links();
        $db_con->import_verbs($usr);

        // reopen the database to reload the list cache
        $db_con->close();
        $db_con = prg_restart("test_reset_db");

        // reload the session user parameters
        $usr = new user;
        $result = $usr->get();

        // reopen the database to reload the verb cache
        $db_con->close();
        $db_con = prg_restart("test_reset_db");

        // reload the base configuration
        $job = new job($sys_usr);
        $job_id = $job->add(job_type_list::BASE_IMPORT);

        $import = new import_file();
        $import->import_base_config($sys_usr);
        $import->import_config($usr);
        $import->import_config_yaml($usr);

        // use the system user again to create the database test datasets
        global $usr;
        $usr = new user;
        $usr->load_by_id(SYSTEM_USER_ID);
        $sys_usr = $usr;

        // create the test dataset to check the basic write functions
        $t = new all_tests();
        $t->set_users();
        $t->create_test_db_entries($t);

        // remove the test dataset for a clean database
        // TODO use the user message object instead of a string
        $cleanup_result = $t->cleanup();
        if (!$cleanup_result) {
            log_err('Cleanup not successful, because ...');
        } else {
            if (!$t->cleanup_check()) {
                log_err('Cleanup check not successful.');
            }
        }

        // reload the session user parameters
        $usr = new user;
        $result = $usr->get();

        /*
         * For testing the system setup
         */

        // drop the database

        // create the database from the sql structure file

        // reload the system database rows (all db rows, that have a code id)

        echo "\n";
        echo $errors . ' internal errors';

    }

    /**
     * TODO move HTML code to frontend
     * import some zukunft.com test json files
     */
    function import_test_files(user $usr): string
    {
        $result = '';
        log_info('test import',
            'import_test_files',
            'import of the some test json files',
            'import_test_files',
            $usr, true
        );

        $imf = new import_file();

        foreach (test_files::TEST_IMPORT_FILE_LIST as $filename) {
            $this->echo('load ' . basename($filename));
            $result .= $imf->json_file($filename, $usr, false);
        }
        foreach (test_files::TEST_DIRECT_IMPORT_FILE_LIST as $filename) {
            $this->echo('load ' . basename($filename));
            $result .= $imf->json_file($filename, $usr);
        }


        log_debug('import test ... done');

        return $result;
    }

    /**
     * display a message immediately to the user
     * @param string $txt the text that should be should to the user
     */
    function echo(string $txt): void
    {
        echo $txt;
        echo "\n";
    }

}