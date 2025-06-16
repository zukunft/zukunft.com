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
include_once MODEL_VIEW_PATH . 'term_view.php';
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
include_once SHARED_ENUM_PATH . 'user_profiles.php';
include_once TEST_CONST_PATH . 'files.php';

use cfg\component\component_link_type_list;
use cfg\component\component_type_list;
use cfg\component\position_type_list;
use cfg\const\files;
use cfg\db\sql_db;
use cfg\element\element_type_list;
use cfg\formula\formula_link_type_list;
use cfg\formula\formula_type_list;
use cfg\import\import_file;
use cfg\phrase\phrase_list;
use cfg\system\job_type_list;
use cfg\language\language_form_list;
use cfg\language\language_list;
use cfg\log\change_action_list;
use cfg\log\change_field_list;
use cfg\log\change_table_list;
use cfg\phrase\phrase_types;
use cfg\ref\ref_type_list;
use cfg\sandbox\protection_type_list;
use cfg\sandbox\share_type_list;
use cfg\ref\source_type_list;
use cfg\system\sys_log_status_list;
use cfg\user\user;
use cfg\user\user_list;
use cfg\user\user_profile_list;
use cfg\verb\verb_list;
use cfg\view\view_link_type_list;
use cfg\view\view_sys_list;
use cfg\view\view_type_list;
use html\types\formula_type_list as formula_type_list_web;
use shared\const\words;
use shared\enum\user_profiles;
use test\all_tests;
use test\test_cleanup;
use unit\import_tests as import_tests;
use unit_read\triple_list_read_tests;
use unit_read\triple_read_tests;
use unit_read\value_read_tests;
use unit_read\word_list_read_tests;
use unit_ui\all_ui_tests;
use unit_ui\base_ui_tests;
use const\files as test_files;
use unit_write\triple_write_tests;
use unit_write\value_write_tests;
use unit_write\word_write_tests;

class all_unit_tests extends test_cleanup
{

    private int $seq_id = 0;

    /**
     * run a single test for faster debugging
     */
    function run_single(all_tests $t): void
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
        //(new formula_link_tests())->run($this);

        // restore the global vars that may be overwritten if additional tests are activated
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
             * part of system setup testing
             */

            $sys_usr = new user;
            $sys_usr->load_by_id(SYSTEM_USER_ID);
            //$import = new import_file();
            //$import->import_config_yaml($sys_usr);

            /*
             * prepare db testing
             */

            $this->create_test_db_entries($t);

            /*
             * import
             */

            // run the selected db import tests
            /*
            $test_name = 'validate config import';
            $imf = new import_file();
            $import_result = $imf->import_config_yaml($sys_usr, true);
            $t->assert($test_name, $import_result->is_ok(), true, $t::TIMEOUT_LIMIT_IMPORT);
            */
            //$this->file_import(test_files::IMPORT_TRAVEL_SCORING, $usr);
            //$this->file_import(test_files::IMPORT_CURRENCY, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::SYSTEM_VIEWS_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::UNITS_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::IP_BLACKLIST_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::TIME_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::BASE_VIEWS_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::START_PAGE_DATA_FILE, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::COMPANY_FILE, $usr);
            $this->file_import(test_files::IMPORT_COUNTRY_ISO, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::COUNTRY_FILE, $usr);
            //$this->file_import(test_files::IMPORT_COUNTRY_ISO, $usr);
            //$this->file_import(files::MESSAGE_PATH . files::START_PAGE_DATA_FILE, $usr);
            //$this->file_import(test_files::IMPORT_WIND_INVESTMENT, $usr);


            /*
             * db read
             */

            // run the selected db read tests
            //(new api_tests())->run($this);
            //(new word_read_tests())->run($this);
            //(new word_list_read_tests())->run($this);
            //(new triple_read_tests())->run($this);
            //(new triple_list_read_tests())->run($this);
            //(new source_read_tests())->run($this);
            //(new formula_read_tests())->run($this);
            //(new view_read_tests())->run($this);
            //(new component_read_tests())->run($this);
            //(new graph_tests())->run($this);
            //(new value_read_tests())->run($this);


            /*
             * db write
             */

            // run the selected db write tests
            (new word_write_tests)->run($this);
            //(new word_list_write_tests)->run($this);
            //(new triple_write_tests)->run($this);
            //(new group_write_tests)->run($this);
            //(new source_write_tests)->run($this);
            //(new ref_write_tests)->run($this);
            //(new value_write_tests)->run($this);
            //(new formula_write_tests)->run($this);
            //(new formula_link_write_tests)->run($this);
            //(new expression_write_tests)->run($this);
            //(new element_write_tests)->run($this);
            //(new element_write_tests)->run_list($this);
            //(new view_write_tests)->run($this);
            //(new view_link_write_tests)->run($this);
            //(new component_write_tests)->run($this);
            //(new component_link_write_tests)->run($this);


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

    private function file_import(string $filename, user $usr): void
    {
        $imf = new import_file();
        $imf->set_start_time($this->start_time());
        $usr_msg = $imf->json_file($filename, $usr, false);
        if (!$usr_msg->is_ok()) {
            log_warning($filename .  ' imported failed because ' . $usr_msg->all_message_text());
        }

    }

    /**
     * run all unit test in a useful order
     */
    function run_unit(): void
    {
        $this->header('unit');

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
        (new term_view_tests())->run($this);
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
        (new base_ui_tests)->run($this);

        // test the html ui on localhost without api
        (new all_ui_tests())->run($this);

        // test the html ui on localhost with api
        // (new all_ui_api_tests())->run($this);

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
        global $usr_pro_cac;

        // prepare the unit tests
        $this->init_sys_log_status();
        $this->init_sys_users();
        $this->init_user_profiles();
        $this->init_job_types();

        // set the profile of the test users
        $usr->profile_id = $usr_pro_cac->id(user_profiles::NORMAL);
        $usr_sys->profile_id = $usr_pro_cac->id(user_profiles::SYSTEM);
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
        global $sys_log_sta_cac;

        $sys_log_sta_cac = new sys_log_status_list();
        $sys_log_sta_cac->load_dummy();
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
        global $usr_pro_cac;

        $usr_pro_cac = new user_profile_list();
        $usr_pro_cac->load_dummy();

    }

    /**
     * create word type array for the unit tests without database connection
     */
    private function init_phrase_types(): void
    {
        global $phr_typ_cac;

        $phr_typ_cac = new phrase_types();
        $phr_typ_cac->load_dummy();

    }

    /**
     * create verb array for the unit tests without database connection
     */
    private function init_verbs(): void
    {
        global $vrb_cac;

        $vrb_cac = new verb_list();
        $vrb_cac->load_dummy();

    }

    /**
     * create formula type array for the unit tests without database connection
     */
    private function init_formula_types(): void
    {
        global $frm_typ_cac;

        $frm_typ_cac = new formula_type_list();
        $frm_typ_cac->load_dummy();

    }

    /**
     * create formula frontend type array for the unit tests without database connection
     */
    private function init_formula_html_types(): void
    {
        global $html_formula_types;
        global $frm_typ_cac;

        $html_formula_types = new formula_type_list_web();
        $html_formula_types->set_from_json_array($frm_typ_cac->api_json_array());

    }

    /**
     * create formula link type array for the unit tests without database connection
     */
    private function init_formula_link_types(): void
    {
        global $frm_lnk_typ_cac;

        $frm_lnk_typ_cac = new formula_link_type_list();
        $frm_lnk_typ_cac->load_dummy();

    }

    /**
     * create formula element type array for the unit tests without database connection
     */
    private function init_element_types(): void
    {
        global $elm_typ_cac;

        $elm_typ_cac = new element_type_list();
        $elm_typ_cac->load_dummy();

    }

    /**
     * create an array of the system views for the unit tests without database connection
     */
    private function init_views(user $usr): void
    {
        global $sys_msk_cac;

        $sys_msk_cac = new view_sys_list($usr);
        $sys_msk_cac->load_dummy();

    }

    /**
     * create view type array for the unit tests without database connection
     */
    private function init_view_types(): void
    {
        global $msk_typ_cac;

        $msk_typ_cac = new view_type_list();
        $msk_typ_cac->load_dummy();

    }

    /**
     * create view link type array for the unit tests without database connection
     */
    private function init_view_link_types(): void
    {
        global $msk_lnk_typ_cac;

        $msk_lnk_typ_cac = new view_link_type_list();
        $msk_lnk_typ_cac->load_dummy();

    }

    /**
     * create view component type array for the unit tests without database connection
     */
    private function init_component_types(): void
    {
        global $cmp_typ_cac;

        $cmp_typ_cac = new component_type_list();
        $cmp_typ_cac->load_dummy();

    }

    /**
     * create view component position type array for the unit tests without database connection
     */
    private function init_component_pos_types(): void
    {
        global $pos_typ_cac;

        $pos_typ_cac = new position_type_list();
        $pos_typ_cac->load_dummy();

    }

    /**
     * create view component link type array for the unit tests without database connection
     */
    private function init_component_link_types(): void
    {
        global $cmp_lnk_typ_cac;

        $cmp_lnk_typ_cac = new component_link_type_list();
        $cmp_lnk_typ_cac->load_dummy();

    }

    /**
     * create ref type array for the unit tests without database connection
     */
    private function init_ref_types(): void
    {
        global $ref_typ_cac;

        $ref_typ_cac = new ref_type_list();
        $ref_typ_cac->load_dummy();

    }

    /**
     * create source type array for the unit tests without database connection
     */
    private function init_source_types(): void
    {
        global $src_typ_cac;

        $src_typ_cac = new source_type_list();
        $src_typ_cac->load_dummy();

    }

    /**
     * create share type array for the unit tests without database connection
     */
    private function init_share_types(): void
    {
        global $shr_typ_cac;

        $shr_typ_cac = new share_type_list();
        $shr_typ_cac->load_dummy();

    }

    /**
     * create protection type array for the unit tests without database connection
     */
    private function init_protection_types(): void
    {
        global $ptc_typ_cac;

        $ptc_typ_cac = new protection_type_list();
        $ptc_typ_cac->load_dummy();

    }

    /**
     * create languages array for the unit tests without database connection
     */
    private function init_languages(): void
    {
        global $lan_cac;

        $lan_cac = new language_list();
        $lan_cac->load_dummy();

    }

    /**
     * create language forms array for the unit tests without database connection
     */
    private function init_language_forms(): void
    {
        global $lan_for_cac;

        $lan_for_cac = new language_form_list();
        $lan_for_cac->load_dummy();

    }

    /**
     * create the job types array for the unit tests without database connection
     */
    private function init_job_types(): void
    {
        global $job_typ_cac;

        $job_typ_cac = new job_type_list();
        $job_typ_cac->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    private function init_log_actions(): void
    {
        global $cng_act_cac;

        $cng_act_cac = new change_action_list();
        $cng_act_cac->load_dummy();

    }

    /**
     * create log table array for the unit tests without database connection
     */
    private function init_log_tables(): void
    {
        global $cng_tbl_cac;

        $cng_tbl_cac = new change_table_list();
        $cng_tbl_cac->load_dummy();

    }

    /**
     * create log field array for the unit tests without database connection
     */
    private function init_log_fields(): void
    {
        global $cng_fld_cac;

        $cng_fld_cac = new change_field_list();
        $cng_fld_cac->load_dummy();

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