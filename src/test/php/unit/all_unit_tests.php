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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once html_paths::WEB . 'frontend.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once test_paths::CREATE . 'test_types.php';
include_once test_paths::CREATE . 'unit_env.php';
include_once test_paths::UNIT . 'base_object_tests.php';
include_once test_paths::UNIT . 'coding_rule_tests.php';
include_once test_paths::UNIT . 'permission_tests.php';
include_once test_paths::UNIT_API . 'api_tests.php';
include_once test_paths::UNIT_UI . 'all_ui_tests.php';
include_once test_paths::UNIT_UI . 'base_ui_tests.php';
include_once test_paths::UNIT_UI . 'system_view_ui_tests.php';
include_once test_paths::UTILS . 'all_tests.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\test\php\create\test_types;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\unit_env;
use Zukunft\ZukunftCom\test\php\unit_api\api_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\base_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\system_view_ui_tests;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class all_unit_tests extends test_cleanup
{

    /**
     * run all unit test in a useful order
     */
    function run_unit(): void
    {
        // start the test section (ts)
        $ts = 'unit ';
        $this->header($ts);

        // remember the global var for restore after the unit tests
        global $db_con;
        global $usr;
        $global_db_con = $db_con;
        $global_usr = $usr;

        $t_typ = new test_types($this);

        // create a dummy db connection for testing
        $this->db_con_for_unit_tests();

        // create a dummy users for testing
        $this->users_for_unit_tests();

        // prepare the unit tests
        $tl = new test_lib();
        $tl->ui_test_cache($this->usr_dev, $this);
        $u_env = new unit_env();
        $u_env->init_unit_tests();

        // do the general unit tests
        $all = new all_tests();
        new lib_tests()->run($all); // test functions not yet split into single unit tests
        new math_tests()->run($this);
        new system_tests()->run($this);
        new ip_range_tests()->run($this);
        new coding_rule_tests()->run($this);
        new sql_tests()->run($this);
        new sys_log_tests()->run($this); // TODO add assert_api_to_ui
        new change_log_tests()->run($this); // TODO add assert_api_to_ui  // TODO for version 0.0.6 add import test
        new job_tests()->run($this); // TODO add assert_api_to_ui
        new pod_tests()->run($this);
        new user_tests()->run($this);
        new user_list_tests()->run($this);
        new base_object_tests()->run($this);
        new sandbox_tests()->run($this);
        new language_tests()->run($this); // TODO add assert_api_to_ui
        new type_tests()->run($this); // TODO add assert_api_to_ui

        // do the user object unit tests
        new horizontal_tests()->run($this);
        new permission_tests()->run($this);
        new system_view_ui_tests()->run($this);
        new word_tests()->run($this);
        new word_list_tests()->run($this);
        new verb_tests()->run($this);
        new triple_tests()->run($this);
        new triple_list_tests()->run($this);
        new phrase_tests()->run($this);
        new phrase_list_tests()->run($this);
        new group_tests()->run($this); // TODO add assert_api_to_ui
        new group_list_tests()->run($this); // TODO add assert_api_to_ui
        new term_tests()->run($this);
        new term_list_tests()->run($this);
        new source_tests()->run($this);
        new source_list_tests()->run($this);
        new ref_tests()->run($this);
        new value_tests()->run($this);
        new value_list_tests()->run($this);
        new formula_tests()->run($this);
        new formula_calc_tests()->run($this);
        new formula_list_tests()->run($this);
        new formula_link_tests()->run($this); // TODO add assert_api_to_ui
        new element_tests()->run($this);
        new element_list_tests()->run($this);
        new expression_tests()->run($this);
        new result_tests()->run($this);
        new result_list_tests()->run($this);
        new figure_tests()->run($this);
        new figure_list_tests()->run($this);
        new view_tests()->run($this);
        new view_list_tests()->run($this); // TODO add assert_api_to_ui
        new term_view_tests()->run($this);
        new component_tests()->run($this);
        new component_list_tests()->run($this); // TODO add assert_api_to_ui
        new component_link_tests()->run($this); // TODO add assert_api_to_ui
        new component_link_list_tests()->run($this);

        // do the im- and export unit tests
        new import_tests()->run($this);

        // db setup
        new db_setup_tests()->run($this);

        // do the UI unit tests
        new api_tests()->run_openapi_test($this);
        new base_ui_tests()->run($this);


        // restore the global vars
        $db_con = $global_db_con;
        $usr = $global_usr;
    }

    /**
     * create a dummy database connection for internal unit testing
     * @return void
     */
    private function db_con_for_unit_tests(): void
    {
        global $db_con;

        // just to test the database abstraction layer, but without real connection to any database
        $db_con = new sql_db;
        $db_con->db_type = SQL_DB_TYPE;
    }

    /**
     * create the dummy users for internal unit testing
     * @return void
     */
    private function users_for_unit_tests(): void
    {
        global $usr;
        // TODO Prio 1 remove global system user for security reasons
        global $usr_sys;

        // create a dummy user for testing
        $usr = new user;
        $usr->id = users::SYSTEM_TEST_ID;
        $usr->name = users::SYSTEM_TEST_NAME;
        $usr->set_profile(user_profiles::EMAIL);
        $this->usr1 = $usr;

        // create a second dummy user for testing
        $usr2 = new user;
        $usr2->id = users::SYSTEM_TEST_PARTNER_ID;
        $usr2->name = users::SYSTEM_TEST_PARTNER_NAME;
        $usr2->set_profile(user_profiles::EMAIL);
        $this->usr2 = $usr2;

        // create a dummy admin user for unit testing
        $usr_admin = new user;
        $usr_admin->id = users::SYSTEM_ADMIN_ID;
        $usr_admin->name = users::SYSTEM_ADMIN_NAME;
        $this->usr_admin = $usr_admin;

        // create a dummy system user for unit testing
        $usr_sys = new user;
        $usr_sys->id = users::SYSTEM_ID;
        $usr_sys->name = users::SYSTEM_NAME;
        $this->usr_system = $usr_sys;

        $t_usr = new test_users();
        $this->usr_dev = $t_usr->user_dev();
        $this->usr_normal = $t_usr->user_filled();

    }

}