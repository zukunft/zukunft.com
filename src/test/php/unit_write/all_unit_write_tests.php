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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\import\import_file;
use Zukunft\ZukunftCom\main\php\cfg\system\ip_range;
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_functions;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\unit\lib_tests;
use Zukunft\ZukunftCom\test\php\unit_read\all_unit_read_tests;
use Zukunft\ZukunftCom\test\php\unit_workflow\word_url_tests;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use const Zukunft\ZukunftCom\test\php\utils\ERROR_LIMIT;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_IMPORT . 'import_file.php';
include_once paths::MODEL_SYSTEM . 'ip_range.php';
include_once paths::MODEL_SYSTEM . 'job.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CREATE . 'test_db_load.php';
//include_once test_paths::UTILS . 'all_tests.php';
include_once test_paths::UNIT . 'lib_tests.php';
include_once test_paths::UNIT_READ . 'all_unit_read_tests.php';
include_once test_paths::UNIT_WORKFLOW . 'word_url_tests.php';

class all_unit_write_tests extends all_unit_read_tests
{

    function run_db_write_tests(all_tests $t): void
    {
        global $usr;
        global $db_con;
        global $sys;

        // init
        $t_db = new test_db_load($this);

        // start the test section (ts)
        $ts = 'db write start ';
        $this->header($ts);

        // switch to the test user
        // create the system user before the local user and admin to get the desired database id
        $usr->load_by_profile_code(user_profiles::TEST);
        if ($usr->id <= 0) {

            // but only from localhost
            $ip_addr = '';
            if (array_key_exists(rest_ctrl::REMOTE_ADDR, $_SERVER)) {
                $ip_addr = $_SERVER[rest_ctrl::REMOTE_ADDR];
            }
            if ($ip_addr == users::SYSTEM_ADMIN_IP) {
                $db_con->import_system_users();
            }

            $usr->load_by_profile_code(user_profiles::TEST);
        }

        if ($usr->id > 0) {

            // --------------------------------------
            // start testing the system functionality
            // --------------------------------------

            if ($sys->errors <= ERROR_LIMIT) {
                run_system_test($t);
                run_user_test($t);
            }

            // test the api write functionality
            // TODO Prio 2 activate
            //$this->test_api_write_no_rest_all();
            //$this->test_api_write_all();

            if ($sys->errors <= ERROR_LIMIT) {
                run_db_link_test($t);
                run_sandbox_test($t);
            }

            if ($sys->errors <= ERROR_LIMIT) {
                new lib_tests()->run($t); // test functions not yet split into single unit tests

                // create the test dataset to check the basic write functions
                $t->set_users();
                $t_db->create_test_db_entries($t);

                // run the general db write tests
                new user_write_tests()->run($t);
                new sys_log_write_tests()->run($t);
                // TODO Prio 0 activate
                //new horizontal_write_tests()->run($t);

                // run object specific db write tests
                new word_write_tests()->run($t);
                new word_list_write_tests()->run($t);
                // TODO Prio 1 activate
                //new verb_write_tests()->run($t);
                new triple_write_tests()->run($t);
                new phrase_write_tests()->run($t);
                new phrase_list_write_tests()->run($t);
                new group_write_tests()->run($t);
                new group_list_write_tests()->run($t);
                new graph_tests()->run($t);
                new term_write_tests()->run($t);
                //new term_list_tests()->run($t);
                new source_write_tests()->run($t);
                new ref_write_tests()->run($t);
                new value_write_tests()->run($t);
                //new value_list_write_tests()->run($t);
                new expression_write_tests()->run($t);
                new element_write_tests()->run($t);
                new element_write_tests()->run_list($t);
                // TODO Prio 1 activate
                //new element_group_write_tests()->run($t);
                new formula_write_tests()->run($t);
                new formula_write_tests()->run_list($t);
                new formula_link_write_tests()->run($t);
                new formula_link_write_tests()->run_list($t);
                new formula_trigger_tests()->run($t);
                new result_write_tests()->run($t);
                // TODO Prio 1 activate
                //new result_write_tests()->run_list($t);
                new job_write_tests()->run($t);
                new job_write_tests()->run_list($t);
                new view_write_tests()->run($t);
                new view_relation_write_tests()->run($this);
                new view_link_write_tests()->run($this);
                new component_write_tests()->run($t);
                new component_link_write_tests()->run($t);

                new api_write_tests()->run($t);
                new import_write_tests()->run($t);

                // url tests
                new word_url_tests()->run($t);

                // TODO Prio 2 activate
                // run_export_test($t);
                // run_permission_test ($t);

                // TODO add a test the checks if import returns the expected error messages e.g. if a triple has the name of a word

                run_legacy_test($t);
                run_math_test($t);
                //run_value_ui_test($t);
                //run_formula_ui_test($t);

                // TODO Prio 2 activate
                //$this->run_api_test();
                //run_word_ui_test($t);
                // TODO add a test to merge a separate opened phrase canton Zürich with Zurich (canton)
                run_word_display_test($t);

                // import_test_data() and import_base_data($usr) is intentionally not called here to keep test.php fast
                // run test_full_load.php to load all test data into the database
            }

            // testing cleanup to remove any remaining test records
            $usr_msg = new user_message($usr);
            $t->cleanup($usr_msg);

            // start the integration tests by loading the base and sample data
            // TODO Prio 1 activate
            //run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $t);

        }
    }

    /**
     * recreate the database to test the database setup script
     * TODO make sure that this can never be called in PROD
     *
     * @return void
     */
    function run_db_recreate(): void
    {
        global $db_con;
        global $usr;

        // start the test section (ts)
        $ts = 'db write database recreation ';
        $this->header($ts);

        // create the testing users (needed for the reset db only run)
        $this->set_users();
        $usr = $this->usr1;

        // check if at least some database tables still exists
        $lib = new library();
        $ip_tbl_name = $lib->class_to_name(ip_range::class);
        if ($db_con->has_table($ip_tbl_name)) {
            $usr->get();
        } else {
            // TODO Prio 2 avoid setting the system user profile directly
            $usr->id = users::SYSTEM_ID;
            $usr->profile_id = user_profiles::SYSTEM_ID;
        }

        // remember the user
        $test_usr = clone $usr;

        // use the system user for the database updates
        if ($db_con->has_table($ip_tbl_name)) {
            $usr->load_by_id(users::SYSTEM_ID);
        } else {
            // TODO Prio 2 avoid setting the system user profile directly
            $usr->id = users::SYSTEM_ID;
            $usr->profile_id = user_profiles::SYSTEM_ID;
        }

        // drop all old database tables (the least dependent tables first)
        foreach (def::DB_TABLE_LIST as $table_name) {
            $db_con->drop_table($table_name);
        }
        $db_con->setup_db();

        // restore the test user
        $usr = clone $test_usr;

    }

    /**
     * TODO fill a user_message with the warning and error messages and return true if successful
     * import all zukunft.com data via json files for the unit and db read testing
     * @param user $usr the user who should be owner of the data
     * @return string any error or warning message during import
     */
    function import_base_data(user $usr): string
    {
        $result = '';
        log_info('test import',
            sys_log_functions::IMPORT_TEST_CONFIG_NAME,
            'import of the some test json files',
            'import_test_files',
            $usr, true
        );

        $imf = new import_file();

        // import initial data used for the main demo pages e.g. the start page
        // the bare file names need the message path prepended (as in import_system_data)
        foreach (files::BASE_DATA_FILES as $filename) {
            $result .= $imf->json_file(files::MESSAGE_PATH . $filename, $usr, false)->get_last_message();
        }

        // import initial data used for the main demo pages e.g. the start page
        foreach (files::BASE_DATA_PATH_FILES as $filename) {
            $result .= $imf->json_file($filename, $usr, false)->get_last_message();
        }

        log_debug('import test data ... done');

        return $result;
    }

    /**
     * TODO fill a user_message with the warning and error messages and return true if successful
     * import all zukunft.com initial setup data vie json files
     * should not include data used for system testing to prevent volatile test data
     * @param user $usr the user who should be owner of the data
     * @return string any error or warning message during import
     */
    function import_test_data(user $usr): string
    {
        $result = '';
        log_info('test import',
            sys_log_functions::IMPORT_TEST_CONFIG_NAME,
            'import of the some test json files',
            'import_test_files',
            $usr, true
        );

        $imf = new import_file();

        // import json files to test the import and fill the database with initial data
        foreach (test_files::TEST_DATA_FILES as $filename) {
            $result .= $imf->json_file($filename, $usr, false)->get_last_message();
        }

        // TODO Prio 2 complete data object base import and move these file to TEST_IMPORT_FILES
        // import JSON files that cannot jet be fully imported via data object
        foreach (test_files::TEST_DATA_FILES_DIRECT as $filename) {
            // TODO Prio 1 fix error reports
            $result .= $imf->json_file($filename, $usr, true, true)->get_last_message();
        }

        // TODO Prio 3 test the import and if fine move these file to TEST_IMPORT_FILES
        // import JSON files that are not jet reviewed
        foreach (test_files::TEST_DATA_FILES_NOT_REVIEWED as $filename) {
            $result .= $imf->json_file($filename, $usr, false)->get_last_message();
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