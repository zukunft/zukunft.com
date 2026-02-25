<?php

/*

    test/php/unit_read/user.php - database unit testing of the user profile handling
    ---------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'user_profiles.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_profile_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_status_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_type_list;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class user_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;

        // init
        $t->name = 'unit read db->';

        // start the test section (ts)
        $ts = 'db read user ';
        $t->header($ts);

        $t->subheader($ts . 'load');

        $test_name = 'load user ' . users::SYSTEM_TEST_NAME . ' by name and id';
        $usr = new user();
        $usr->load_by_name(users::SYSTEM_TEST_NAME);
        $usr_by_id = new user();
        $usr_by_id->load_by_id($usr->id, user::class);
        $t->assert($test_name, $usr_by_id->name, users::SYSTEM_TEST_NAME);
        //$t->assert($test_name, $usr_by_id->email, users::SYSTEM_TEST_EMAIL);

        $test_name = 'load user ' . users::SYSTEM_TEST_NAME . ' by email';
        $usr = new user();
        $usr->load_by_email(users::SYSTEM_TEST_EMAIL);
        $usr_by_id = new user();
        $usr_by_id->load_by_id($usr->id, user::class);
        $t->assert($test_name, $usr_by_id->name, users::SYSTEM_TEST_NAME);

        // TODO test type and view


        $t->subheader($ts . 'profile');

        $test_name = 'load the user_profiles';
        $lst = new user_profile_list();
        $result = $lst->load($db_con);
        $t->assert($test_name, $result, true);
        $test_name = 'load the user_types';
        $lst = new user_type_list();
        $result = $lst->load($db_con);
        $t->assert($test_name, $result, true);
        $test_name = 'load the user_statuus';
        $lst = new user_status_list();
        $result = $lst->load($db_con);
        $t->assert($test_name, $result, true);

        // ... and check if at least the most critical is loaded
        global $sys;
        $result = $sys->typ_lst->usr_pro->id(user_profiles::NORMAL);
        $t->assert('user profile check ' . user_profiles::NORMAL, $result, user_profiles::NORMAL_ID);
    }

}

