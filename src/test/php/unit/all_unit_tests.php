<?php

/*

    /test/php/unit/test_unit.php - add the unit tests to the main test class
    ----------------------------

    run all unit tests in a useful order
    the zukunft.com unit tests should test all class methods, that can be tested without database access


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

namespace unit;

include_once DB_PATH . 'sql_db.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_list.php';
include_once MODEL_USER_PATH . 'user_profile.php';
include_once MODEL_USER_PATH . 'user_type.php';
include_once MODEL_USER_PATH . 'user_official_type.php';
include_once MODEL_SYSTEM_PATH . 'ip_range.php';
include_once MODEL_SYSTEM_PATH . 'session.php';
include_once MODEL_SYSTEM_PATH . 'job_type.php';
include_once MODEL_SYSTEM_PATH . 'job_type_list.php';
include_once MODEL_SYSTEM_PATH . 'job_time.php';
include_once MODEL_SYSTEM_PATH . 'job.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_function.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status_list.php';
include_once MODEL_PHRASE_PATH . 'phrase_types.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_VERB_PATH . 'verb_list.php';
include_once MODEL_ELEMENT_PATH . 'element_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_link_type_list.php';
include_once MODEL_ELEMENT_PATH . 'element_type.php';
include_once MODEL_VIEW_PATH . 'view_sys_list.php';
include_once MODEL_VIEW_PATH . 'view_sys_list.php';
include_once MODEL_VIEW_PATH . 'view_link_type.php';
include_once MODEL_VIEW_PATH . 'view_type.php';
include_once MODEL_VIEW_PATH . 'view_type_list.php';
include_once MODEL_COMPONENT_PATH . 'component_link_type_list.php';
include_once MODEL_COMPONENT_PATH . 'component_type_list.php';
include_once MODEL_COMPONENT_PATH . 'position_type_list.php';
include_once MODEL_VIEW_PATH . 'view_term_link.php';
include_once MODEL_REF_PATH . 'ref_type_list.php';
include_once MODEL_REF_PATH . 'source_list.php';
include_once MODEL_REF_PATH . 'source_type_list.php';
include_once MODEL_SANDBOX_PATH . 'share_type_list.php';
include_once MODEL_SANDBOX_PATH . 'protection_type_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_form_list.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_LOG_PATH . 'change_action_list.php';
include_once MODEL_LOG_PATH . 'change_table.php';
include_once MODEL_LOG_PATH . 'change_table_list.php';
include_once MODEL_LOG_PATH . 'change_table_field.php';
include_once MODEL_LOG_PATH . 'change_field.php';
include_once MODEL_LOG_PATH . 'change_field_list.php';
include_once MODEL_LOG_PATH . 'change_link.php';
include_once MODEL_SYSTEM_PATH . 'sys_log.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_list.php';
include_once API_SANDBOX_PATH . 'sandbox_value.php';

use cfg\component\component_link_type_list;
use cfg\component\position_type_list;
use cfg\component\component_type_list;
use cfg\db\sql_db;
use cfg\element_type_list;
use cfg\formula_link_type_list;
use cfg\formula_type_list;
use cfg\job_type_list;
use cfg\language_form_list;
use cfg\language_list;
use cfg\log\change_action_list;
use cfg\log\change_field_list;
use cfg\log\change_table_list;
use cfg\phrase_types;
use cfg\protection_type_list;
use cfg\ref_type_list;
use cfg\share_type_list;
use cfg\source_type_list;
use cfg\sys_log_status_list;
use cfg\user;
use cfg\user\user_profile;
use cfg\user_list;
use cfg\user_profile_list;
use cfg\verb_list;
use cfg\view_link_type_list;
use cfg\view_sys_list;
use cfg\view_type_list;
use test\all_tests;
use test\test_cleanup;
use unit\html\change_log as change_log_html_tests;
use unit\html\component as component_html_tests;
use unit\html\component_list as component_list_html_tests;
use unit\html\figure as figure_html_tests;
use unit\html\figure_list as figure_list_html_tests;
use unit\html\formula as formula_html_tests;
use unit\html\formula_list as formula_list_html_tests;
use unit\html\job as job_html_tests;
use unit\html\language as language_html_tests;
use unit\html\phrase as phrase_html_tests;
use unit\html\phrase_group as phrase_group_html_tests;
use unit\html\phrase_list as phrase_list_html_tests;
use unit\html\reference as reference_html_tests;
use unit\html\result as result_html_tests;
use unit\html\result_list as result_list_html_tests;
use unit\html\source as source_html_tests;
use unit\html\sys_log as sys_log_html_tests;
use unit\html\system_views as system_views_html_tests;
use unit\html\term as term_html_tests;
use unit\html\term_list as term_list_html_tests;
use unit\html\triple as triple_html_tests;
use unit\html\triple_list as triple_list_html_tests;
use unit\html\type_lists as type_list_html_tests;
use unit\html\user as user_html_tests;
use unit\html\value as value_html_tests;
use unit\html\value_list as value_list_html_tests;
use unit\html\verb as verb_html_tests;
use unit\html\view as view_html_tests;
use unit\html\view_list as view_list_html_tests;
use unit\html\word as word_html_tests;
use unit\html\word_list as word_list_html_tests;
use unit\import_tests as import_tests;
use unit_read\component_read_tests;
use unit_read\formula_read_tests;
use unit_read\source_read_tests;
use unit_read\triple_read_tests;
use unit_read\view_read_tests;
use unit_read\word_read_tests;
use unit_ui\local_ui_tests;
use unit_write\component_write_tests;
use unit_write\formula_link_write_tests;
use unit_write\formula_write_tests;
use unit_write\source_write_tests;
use unit_write\triple_write_tests;
use unit_write\view_write_tests;
use unit_write\word_write_tests;
use html\types\formula_type_list as formula_type_list_web;

class all_unit_tests extends test_cleanup
{

    private int $seq_id = 0;

    /**
     * run a single test for faster debugging
     */
    function run_single(): void
    {

        /*
         * unit testing - prepare
         */

        // remember the global var for restore after the unit tests
        global $db_con;
        global $sql_names;
        global $usr;
        $global_db_con = $db_con;
        $global_sql_names = $sql_names;
        $global_usr = $usr;

        // prepare for unit testing
        $this->db_con_for_unit_tests();
        $this->users_for_unit_tests();
        $this->init_unit_tests();

        /*
         * unit testing - run
         */

        // run the selected unit tests
        //(new system_tests)->run($this);
        //(new import_tests)->run($this);

        // restore the global vars
        $db_con = $global_db_con;
        $sql_names = $global_sql_names;
        $usr = $global_usr;


        /*
         * db testing - prepare
         */

        // reload the setting lists after using dummy list for the unit tests
        $db_con->close();
        $db_con = prg_restart("reload cache after unit testing");

        // create the testing users
        $this->set_users();
        $usr = $this->usr1;

        if ($usr->id() > 0) {

            /*
             * db read testing - run
             */

            // run the selected db write
            (new word_read_tests())->run($this);
            //(new triple_read_tests())->run($this);
            (new source_read_tests())->run($this);
            (new formula_read_tests())->run($this);
            (new view_read_tests())->run($this);
            (new component_read_tests())->run($this);


            /*
             * db write testing - run
             */

            // run the selected db write
            (new word_write_tests)->run($this);
            //(new triple_write_tests)->run($this);
            //(new group_write_tests)->run($this);
            (new source_write_tests)->run($this);
            (new formula_write_tests)->run($this);
            //(new formula_link_write_tests)->run($this);
            (new view_write_tests)->run($this);
            (new component_write_tests)->run($this);
            //(new value_write_tests)->run($this);
            //(new view_write_tests)->run($this);
            //$import = new import_file();
            //$import->import_test_files($usr);
        }

        /*
        global $db_con;

        // to test the database upgrade
        $db_chk = new db_check();
        $db_chk->db_upgrade_0_0_3($db_con);
        */
    }

    /**
     * run all unit test in a useful order
     */
    function run_unit(): void
    {
        $this->header('Start unit tests');

        // remember the global var for restore after the unit tests
        global $db_con;
        global $sql_names;
        global $usr;
        $global_db_con = $db_con;
        $global_sql_names = $sql_names;
        $global_usr = $usr;

        // create a dummy db connection for testing
        $this->db_con_for_unit_tests();

        // create a dummy users for testing
        $this->users_for_unit_tests();

        // prepare the unit tests
        $this->init_unit_tests();

        // do the general unit tests
        $all = new all_tests();
        (new lib_tests)->run($all); // test functions not yet split into single unit tests
        (new math_tests)->run($this);
        (new system_tests)->run($this);
        (new sys_log_tests)->run($this); // TODO add assert_api_to_dsp
        (new change_log_tests)->run($this); // TODO add assert_api_to_dsp  // TODO for version 0.0.6 add import test
        (new job_tests)->run($this); // TODO add assert_api_to_dsp
        (new pod_tests)->run($this);
        (new user_tests)->run($this);
        (new user_list_tests)->run($this);
        (new sandbox_tests)->run($this);
        (new language_tests)->run($this); // TODO add assert_api_to_dsp
        (new type_tests)->run($this); // TODO add assert_api_to_dsp

        // do the user object unit tests
        (new word_tests)->run($this);
        (new word_list_tests)->run($this);
        (new verb_tests)->run($this);
        (new triple_tests)->run($this);
        (new triple_list_tests)->run($this);
        (new phrase_tests)->run($this);
        (new phrase_list_tests)->run($this);
        (new group_tests)->run($this); // TODO add assert_api_to_dsp
        (new group_list_tests)->run($this); // TODO add assert_api_to_dsp
        (new term_tests)->run($this);
        (new term_list_tests)->run($this);
        (new source_tests)->run($this);
        (new source_list_tests)->run($this);
        (new ref_tests)->run($this);
        (new value_tests)->run($this);
        (new value_list_tests)->run($this);
        (new value_phrase_link_tests)->run($this);
        (new formula_tests)->run($this);
        (new formula_list_tests)->run($this);
        (new formula_link_tests)->run($this); // TODO add assert_api_to_dsp
        (new element_tests)->run($this);
        (new element_list_tests)->run($this);
        (new expression_tests)->run($this);
        (new result_tests)->run($this);
        (new result_list_tests)->run($this);
        (new figure_tests)->run($this);
        (new figure_list_tests)->run($this);
        (new view_tests)->run($this);
        (new view_list_tests)->run($this); // TODO add assert_api_to_dsp
        (new view_term_link_tests())->run($this);
        (new component_tests ())->run($this);
        (new component_list_tests ())->run($this); // TODO add assert_api_to_dsp
        (new component_link_tests)->run($this); // TODO add assert_api_to_dsp
        (new component_link_list_tests)->run($this);

        // do the im- and export unit tests
        (new import_tests)->run($this);

        // db setup
        (new db_setup_tests)->run($this);

        // do the UI unit tests
        (new api_tests)->run_openapi_test($this);
        (new html_tests)->run($this);
        (new type_list_html_tests)->run($this);
        (new user_html_tests)->run($this);
        (new word_html_tests)->run($this);
        (new word_list_html_tests)->run($this);
        (new verb_html_tests())->run($this);
        (new triple_html_tests)->run($this);
        (new triple_list_html_tests)->run($this);
        (new phrase_html_tests)->run($this);
        (new phrase_list_html_tests)->run($this);
        (new phrase_group_html_tests)->run($this);
        (new term_html_tests)->run($this);
        (new term_list_html_tests)->run($this);
        (new value_html_tests)->run($this);
        (new value_list_html_tests)->run($this);
        (new formula_html_tests)->run($this);
        (new formula_list_html_tests)->run($this);
        (new result_html_tests)->run($this);
        (new result_list_html_tests)->run($this);
        (new figure_html_tests())->run($this);
        (new figure_list_html_tests)->run($this);
        (new view_html_tests)->run($this);
        (new view_list_html_tests)->run($this);
        (new component_html_tests)->run($this);
        (new component_list_html_tests)->run($this);
        (new source_html_tests)->run($this);
        (new reference_html_tests)->run($this);
        (new language_html_tests)->run($this);
        (new change_log_html_tests)->run($this);
        (new sys_log_html_tests)->run($this);
        (new job_html_tests)->run($this);
        (new system_views_html_tests)->run($this);

        (new local_ui_tests())->run($this);

        // restore the global vars
        $db_con = $global_db_con;
        $sql_names = $global_sql_names;
        $usr = $global_usr;
    }

    /**
     * create a dummy database connection for internal unit testing
     * @return void
     */
    private function db_con_for_unit_tests(): void
    {
        global $db_con;
        global $sql_names;

        // just to test the database abstraction layer, but without real connection to any database
        $db_con = new sql_db;
        $db_con->db_type = SQL_DB_TYPE;
        // create a list with all prepared sql queries to check if the name is unique
        $sql_names = array();

    }

    /**
     * create the dummy users for internal unit testing
     * @return void
     */
    private function users_for_unit_tests(): void
    {
        global $usr;
        global $usr_sys;

        // create a dummy user for testing
        $usr = new user;
        $usr->set_id(user::SYSTEM_TEST_ID);
        $usr->name = user::SYSTEM_TEST_NAME;
        $this->usr1 = $usr;

        // create a dummy system user for unit testing
        $usr_sys = new user;
        $usr_sys->set_id(user::SYSTEM_ID);
        $usr_sys->name = user::SYSTEM_NAME;

    }

    private function init_unit_tests(): void
    {
        global $usr;
        global $usr_sys;
        global $user_profiles;

        // prepare the unit tests
        $this->init_sys_log_status();
        $this->init_sys_users();
        $this->init_user_profiles();
        $this->init_job_types();

        // set the profile of the test users
        $usr->profile_id = $user_profiles->id(user_profile::NORMAL);
        $usr_sys->profile_id = $user_profiles->id(user_profile::SYSTEM);
        $usr->set_id(1);

        // continue with preparing unit tests
        $this->init_phrase_types();
        $this->init_verbs();
        $this->init_formula_types();
        $this->init_formula_html_types();
        $this->init_formula_link_types();
        $this->init_element_types();
        $this->init_views($usr);
        $this->init_view_types();
        $this->init_view_link_types();
        $this->init_component_types();
        $this->init_component_link_types();
        $this->init_component_pos_types();
        $this->init_ref_types();
        $this->init_source_types();
        $this->init_share_types();
        $this->init_protection_types();
        $this->init_languages();
        $this->init_language_forms();
        $this->init_job_types();
        $this->init_log_actions();
        $this->init_log_tables();
        $this->init_log_fields();

    }

    /**
     * create the system log status list for the unit tests without database connection
     */
    private function init_sys_log_status(): void
    {
        global $sys_log_stati;

        $sys_log_stati = new sys_log_status_list();
        $sys_log_stati->load_dummy();
    }

    /**
     * create the system user list for the unit tests without database connection
     */
    private function init_sys_users(): void
    {
        global $usr_sys;
        global $system_users;

        $system_users = new user_list($usr_sys);
        $system_users->load_dummy();
    }

    /**
     * create the user profiles for the unit tests without database connection
     */
    private function init_user_profiles(): void
    {
        global $user_profiles;

        $user_profiles = new user_profile_list();
        $user_profiles->load_dummy();

    }

    /**
     * create word type array for the unit tests without database connection
     */
    private function init_phrase_types(): void
    {
        global $phrase_types;

        $phrase_types = new phrase_types();
        $phrase_types->load_dummy();

    }

    /**
     * create verb array for the unit tests without database connection
     */
    private function init_verbs(): void
    {
        global $verbs;

        $verbs = new verb_list();
        $verbs->load_dummy();

    }

    /**
     * create formula type array for the unit tests without database connection
     */
    private function init_formula_types(): void
    {
        global $formula_types;

        $formula_types = new formula_type_list();
        $formula_types->load_dummy();

    }

    /**
     * create formula frontend type array for the unit tests without database connection
     */
    private function init_formula_html_types(): void
    {
        global $html_formula_types;
        global $formula_types;

        $html_formula_types = new formula_type_list_web();
        $html_formula_types->set_obj_from_json_array(json_decode($formula_types->api_json(), true));

    }

    /**
     * create formula link type array for the unit tests without database connection
     */
    private function init_formula_link_types(): void
    {
        global $formula_link_types;

        $formula_link_types = new formula_link_type_list();
        $formula_link_types->load_dummy();

    }

    /**
     * create formula element type array for the unit tests without database connection
     */
    private function init_element_types(): void
    {
        global $element_types;

        $element_types = new element_type_list();
        $element_types->load_dummy();

    }

    /**
     * create an array of the system views for the unit tests without database connection
     */
    private function init_views(user $usr): void
    {
        global $system_views;

        $system_views = new view_sys_list($usr);
        $system_views->load_dummy();

    }

    /**
     * create view type array for the unit tests without database connection
     */
    private function init_view_types(): void
    {
        global $view_types;

        $view_types = new view_type_list();
        $view_types->load_dummy();

    }

    /**
     * create view link type array for the unit tests without database connection
     */
    private function init_view_link_types(): void
    {
        global $view_link_types;

        $view_link_types = new view_link_type_list();
        $view_link_types->load_dummy();

    }

    /**
     * create view component type array for the unit tests without database connection
     */
    private function init_component_types(): void
    {
        global $component_types;

        $component_types = new component_type_list();
        $component_types->load_dummy();

    }

    /**
     * create view component position type array for the unit tests without database connection
     */
    private function init_component_pos_types(): void
    {
        global $position_types;

        $position_types = new position_type_list();
        $position_types->load_dummy();

    }

    /**
     * create view component link type array for the unit tests without database connection
     */
    private function init_component_link_types(): void
    {
        global $component_link_types;

        $component_link_types = new component_link_type_list();
        $component_link_types->load_dummy();

    }

    /**
     * create ref type array for the unit tests without database connection
     */
    private function init_ref_types(): void
    {
        global $ref_types;

        $ref_types = new ref_type_list();
        $ref_types->load_dummy();

    }

    /**
     * create source type array for the unit tests without database connection
     */
    private function init_source_types(): void
    {
        global $source_types;

        $source_types = new source_type_list();
        $source_types->load_dummy();

    }

    /**
     * create share type array for the unit tests without database connection
     */
    private function init_share_types(): void
    {
        global $share_types;

        $share_types = new share_type_list();
        $share_types->load_dummy();

    }

    /**
     * create protection type array for the unit tests without database connection
     */
    private function init_protection_types(): void
    {
        global $protection_types;

        $protection_types = new protection_type_list();
        $protection_types->load_dummy();

    }

    /**
     * create languages array for the unit tests without database connection
     */
    private function init_languages(): void
    {
        global $languages;

        $languages = new language_list();
        $languages->load_dummy();

    }

    /**
     * create language forms array for the unit tests without database connection
     */
    private function init_language_forms(): void
    {
        global $language_forms;

        $language_forms = new language_form_list();
        $language_forms->load_dummy();

    }

    /**
     * create the job types array for the unit tests without database connection
     */
    private function init_job_types(): void
    {
        global $job_types;

        $job_types = new job_type_list();
        $job_types->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    private function init_log_actions(): void
    {
        global $change_action_list;

        $change_action_list = new change_action_list();
        $change_action_list->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    private function init_log_tables(): void
    {
        global $change_table_list;

        $change_table_list = new change_table_list();
        $change_table_list->load_dummy();

    }

    /**
     * create log field array for the unit tests without database connection
     */
    private function init_log_fields(): void
    {
        global $change_field_list;

        $change_field_list = new change_field_list();
        $change_field_list->load_dummy();

    }

    /**
     * @return int the next dummy id for unit testing
     */
    function seq_id(): int
    {
        $this->seq_id++;
        return $this->seq_id;
    }

}