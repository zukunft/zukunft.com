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

use cfg\component\component;
use cfg\component\component_link;
use cfg\component\component_link_type;
use cfg\component\component_type;
use cfg\component\position_type_list;
use cfg\component\component_type_list;
use cfg\config;
use cfg\db\db_check;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\element;
use cfg\element_type;
use cfg\element_type_list;
use cfg\formula;
use cfg\formula_link;
use cfg\formula_link_type_list;
use cfg\formula_type;
use cfg\formula_type_list;
use cfg\group\group;
use cfg\import\import_file;
use cfg\job;
use cfg\job_type;
use cfg\job_type_list;
use cfg\language_form_list;
use cfg\language_list;
use cfg\library;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_action_list;
use cfg\log\change_field;
use cfg\log\change_field_list;
use cfg\log\change_link;
use cfg\log\change_table;
use cfg\log\change_table_list;
use cfg\phrase_type;
use cfg\phrase_types;
use cfg\protection_type;
use cfg\protection_type_list;
use cfg\ref;
use cfg\ref_type;
use cfg\ref_type_list;
use cfg\result\result;
use cfg\share_type;
use cfg\share_type_list;
use cfg\source;
use cfg\source_type;
use cfg\source_type_list;
use cfg\sys_log;
use cfg\sys_log_function;
use cfg\sys_log_status;
use cfg\triple;
use cfg\user;
use cfg\user\user_profile;
use cfg\value\value;
use cfg\verb;
use cfg\view;
use cfg\view_type;
use cfg\view_type_list;
use cfg\word;
use Exception;
use html\html_base;
use html\types\share;
use unit_read\all_unit_read_tests;
use cfg\ip_range;
use test\all_tests;
use unit\lib_tests;
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
                (new word_tests)->run($t);
                (new word_list_tests)->run($t);
                (new verb_tests)->run($t);
                (new triple_tests)->run($t);
                (new phrase_tests)->run($t);
                (new phrase_list_tests)->run($t);
                (new phrase_group_tests)->run($t);
                (new phrase_group_list_tests)->run($t);
                (new graph_tests)->run($t);
                (new term_tests)->run($t);
                //(new term_list_tests)->run($t);
                (new value_tests)->run($t);
                (new source_tests)->run($t);
                (new ref_tests)->run($t);
                (new expression_tests)->run($t);
                (new formula_tests)->run($t);
                (new formula_tests)->run_list($t);
                (new formula_link_tests)->run($t);
                (new formula_link_tests)->run_list($t);
                (new formula_trigger_tests)->run($t);
                (new result_tests)->run($t);
                // TODO activate Prio 1
                //(new result_tests)->run_list($t);
                (new element_tests)->run($t);
                (new element_tests)->run_list($t);
                (new element_group_tests)->run($t);
                (new job_tests)->run($t);
                (new job_tests)->run_list($t);
                (new view_tests)->run($t);
                (new component_tests)->run($t);
                (new component_link_tests)->run($t);

                run_display_test($t);
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
                $import->import_test_files($usr);
            }

            // testing cleanup to remove any remaining test records
            $t->cleanup();

            // start the integration tests by loading the base and sample data
            // TODO activate Prio 1
            //run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $t);

        }
    }

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
            $usr->set_profile(user_profile::ADMIN);
        }

        // remember the user
        $test_usr = clone $usr;

        // use the system user for the database updates
        if ($db_con->has_table($ip_tbl_name)) {
            $usr->load_by_id(SYSTEM_USER_ID);
        } else {
            $usr->set_id(SYSTEM_USER_ID);
            $usr->set_profile(user_profile::ADMIN);
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
        $this->run_db_truncate($sys_usr);
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
     * truncate all tables (use only for system testing)
     */
    function run_db_truncate(user $sys_usr): void
    {
        $lib = new library();

        // the tables in order to avoid the usage of CASCADE
        $table_names = array(
            [value::class, true],
            value::class,
            result::class,
            element::class,
            element_type::class,
            [formula_link::class, true],
            formula_link::class,
            [formula::class, true],
            formula::class,
            formula_type::class,
            [component_link::class, true],
            component_link::class,
            component_link_type::class,
            [component::class, true],
            component::class,
            component_type::class,
            [view::class, true],
            view::class,
            view_type::class,
            [group::class, true],
            group::class,
            verb::class,
            [triple::class, true],
            triple::class,
            [word::class, true],
            word::class,
            phrase_type::class,
            [source::class, true],
            source::class,
            source_type::class,
            ref::class,
            ref_type::class,
            change_link::class,
            change::class,
            change_action::class,
            change_field::class,
            change_table::class,
            config::class,
            job::class,
            job_type::class,
            //sql_db::TBL_SYS_SCRIPT,
            sys_log::class,
            sys_log_status::class,
            sys_log_function::class,
            share_type::class,
            protection_type::class,
            user::class,
            user_profile::class
        );
        $html = new html_base();
        $html->echo("\n");
        $html->echo('truncate ');
        $html->echo("\n");

        // truncate tables that have already a build in truncate statement creation
        $sql = '';
        $sc = new sql();
        $grp = new group($sys_usr);
        $sql .= $grp->sql_truncate($sc);

        global $db_con;

        try {
            $db_con->exe($sql);
        } catch (Exception $e) {
            log_err('Cannot truncate based on sql ' . $sql . '" because: ' . $e->getMessage());
        }

        // truncate the other tables
        foreach ($table_names as $entry) {
            $usr_tbl = false;
            if (is_array($entry)) {
                $class = $entry[0];
                $usr_tbl = $entry[1];
            } else {
                $class = $entry;
            }
            if ($usr_tbl) {
                $table_name = sql_db::TBL_USER_PREFIX . $lib->class_to_name($class);
            } else {
                $table_name = $lib->class_to_name($class);
            }
            $db_con->truncate_table($table_name);
        }

        // reset the preloaded data
        $this->run_preloaded_truncate();
    }

    function run_preloaded_truncate(): void
    {
        global $system_users;
        global $user_profiles;
        global $phrase_types;
        global $formula_types;
        global $formula_link_types;
        global $element_types;
        global $view_types;
        global $component_types;
        global $component_link_types;
        global $position_types;
        global $ref_types;
        global $source_types;
        global $share_types;
        global $protection_types;
        global $languages;
        global $language_forms;
        global $verbs;
        global $system_views;
        global $sys_log_stati;
        global $job_types;
        global $change_action_list;
        global $change_table_list;
        global $change_field_list;

        //$system_users =[];
        //$user_profiles =[];
        $phrase_types = new phrase_types();
        $formula_types = new formula_type_list();
        $formula_link_types = new formula_link_type_list();
        $element_types = new element_type_list();
        $view_types = new view_type_list();
        $component_types = new component_type_list();
        // not yet needed?
        //$component_link_types = new component_link_type_list();
        $position_types = new position_type_list();
        $ref_types = new ref_type_list();
        $source_types = new source_type_list();
        $share_types = new share_type_list();
        $protection_types = new protection_type_list();
        $languages = new language_list();
        $language_forms = new language_form_list();
        $job_types = new job_type_list();
        $change_action_list = new change_action_list();
        $change_table_list = new change_table_list();
        $change_field_list = new change_field_list();
    }

}