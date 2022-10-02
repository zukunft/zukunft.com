<?php

/*

    test_unit.php - add the unit tests to the main test class
    -------------

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

class test_unit extends testing
{

    /**
     * run all unit test in a useful order
     */
    function run_unit(): void
    {
        $this->header('Start the zukunft.com unit tests');

        // remember the global var for restore after the unit tests
        global $db_con;
        global $sql_names;
        global $usr;
        $global_db_con = $db_con;
        $global_sql_names = $sql_names;
        $global_usr = $usr;

        // just to test the database abstraction layer, but without real connection to any database
        $db_con = new sql_db;
        $db_con->db_type = SQL_DB_TYPE;
        // create a list with all prepared sql queries to check if the name is unique
        $sql_names = array();
        // create a dummy user for unit testing
        $usr = new user;
        $usr->id = SYSTEM_USER_ID;


        // prepare the unit tests
        $this->init_sys_log_status();
        $this->init_sys_users();
        $this->init_user_profiles();
        $this->init_word_types();
        $this->init_verbs();
        $this->init_formula_types();
        $this->init_formula_link_types();
        $this->init_formula_element_types();
        $this->init_views($usr);
        $this->init_view_types();
        $this->init_view_component_types();
        $this->init_view_component_link_types();
        $this->init_view_component_pos_types();
        $this->init_ref_types();
        $this->init_share_types();
        $this->init_protection_types();
        $this->init_job_types();
        $this->init_log_tables();

        // do the unit tests
        (new string_unit_tests)->run($this); // test functions not yet split into single unit tests
        (new system_unit_tests)->run($this);
        (new word_unit_tests)->run($this);
        (new word_link_unit_tests)->run($this);
        (new word_list_unit_tests)->run($this);
        (new word_link_list_unit_tests)->run($this);
        (new phrase_unit_tests)->run($this);
        (new phrase_list_unit_tests)->run($this);
        (new phrase_group_unit_tests)->run($this);
        (new term_list_unit_tests)->run($this);
        (new view_unit_tests)->run($this);
        (new view_component_unit_tests())->run($this);
        (new view_component_link_unit_tests)->run($this);
        (new value_unit_tests)->run($this);
        (new value_phrase_link_unit_tests)->run($this);
        (new value_list_unit_tests)->run($this);
        (new formula_unit_tests)->run($this);
        (new formula_link_unit_tests)->run($this);
        (new formula_value_unit_tests)->run($this);
        (new formula_element_unit_tests)->run($this);
        (new figure_unit_tests)->run($this);
        (new expression_unit_tests)->run($this);
        (new user_sandbox_unit_tests)->run($this);
        (new verb_unit_tests)->run($this);
        (new ref_unit_tests)->run($this);
        (new user_log_unit_tests)->run($this);

        // do the UI unit tests
        (new html_unit_tests)->run($this);
        (new type_list_display_unit_tests)->run($this);
        (new word_display_unit_tests)->run($this);
        (new word_list_display_unit_tests)->run($this);
        (new triple_display_unit_tests)->run($this);
        (new phrase_list_display_unit_tests)->run($this);

        // restore the global vars
        $db_con = $global_db_con;
        $sql_names = $global_sql_names;
        $usr = $global_usr;
    }

    /**
     * create the system log status list for the unit tests without database connection
     */
    function init_sys_log_status()
    {
        global $sys_log_stati;

        $sys_log_stati = new sys_log_status();
        $sys_log_stati->load_dummy();

    }

    /**
     * create the system user list for the unit tests without database connection
     */
    function init_sys_users()
    {
        global $system_users;

        $system_users = new user_list();
        $system_users->load_dummy();

    }

    /**
     * create the user profiles for the unit tests without database connection
     */
    function init_user_profiles()
    {
        global $user_profiles;

        $user_profiles = new user_profile_list();
        $user_profiles->load_dummy();

    }

    /**
     * create word type array for the unit tests without database connection
     */
    function init_word_types()
    {
        global $word_types;

        $word_types = new word_type_list();
        $word_types->load_dummy();

    }

    /**
     * create verb array for the unit tests without database connection
     */
    function init_verbs()
    {
        global $verbs;

        $verbs = new verb_list();
        $verbs->load_dummy();

    }

    /**
     * create formula type array for the unit tests without database connection
     */
    function init_formula_types()
    {
        global $formula_types;

        $formula_types = new formula_type_list();
        $formula_types->load_dummy();

    }

    /**
     * create formula link type array for the unit tests without database connection
     */
    function init_formula_link_types()
    {
        global $formula_link_types;

        $formula_link_types = new formula_link_type_list();
        $formula_link_types->load_dummy();

    }

    /**
     * create formula element type array for the unit tests without database connection
     */
    function init_formula_element_types()
    {
        global $formula_element_types;

        $formula_element_types = new formula_element_type_list();
        $formula_element_types->load_dummy();

    }

    /**
     * create an array of the system views for the unit tests without database connection
     */
    function init_views(user $usr)
    {
        global $system_views;

        $system_views = new view_list($usr);
        $system_views->load_dummy();

    }

    /**
     * create view type array for the unit tests without database connection
     */
    function init_view_types()
    {
        global $view_types;

        $view_types = new view_type_list();
        $view_types->load_dummy();

    }

    /**
     * create view component type array for the unit tests without database connection
     */
    function init_view_component_types()
    {
        global $view_component_types;

        $view_component_types = new view_cmp_type_list();
        $view_component_types->load_dummy();

    }

    /**
     * create view component position type array for the unit tests without database connection
     */
    function init_view_component_pos_types()
    {
        global $view_component_position_types;

        $view_component_position_types = new view_cmp_pos_type_list();
        $view_component_position_types->load_dummy();

    }

    /**
     * create view component link type array for the unit tests without database connection
     */
    function init_view_component_link_types()
    {
        global $view_component_link_types;

        $view_component_link_types = new view_cmp_link_type_list();
        $view_component_link_types->load_dummy();

    }

    /**
     * create ref type array for the unit tests without database connection
     */
    function init_ref_types()
    {
        global $ref_types;

        $ref_types = new ref_type_list();
        $ref_types->load_dummy();

    }

    /**
     * create share type array for the unit tests without database connection
     */
    function init_share_types()
    {
        global $share_types;

        $share_types = new share_type_list();
        $share_types->load_dummy();

    }

    /**
     * create protection type array for the unit tests without database connection
     */
    function init_protection_types()
    {
        global $protection_types;

        $protection_types = new protection_type_list();
        $protection_types->load_dummy();

    }

    /**
     * create job type array for the unit tests without database connection
     */
    function init_job_types()
    {
        global $job_types;

        $job_types = new job_type_list();
        $job_types->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    function init_log_tables()
    {
        global $change_log_tables;

        $change_log_tables = new change_log_table();
        $change_log_tables->load_dummy();

    }

}