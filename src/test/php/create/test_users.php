<?php

/*

    test/create/test_users.php - create the users for creating the test object and for the user management
    --------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED_HELPER . 'Config.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;

class test_users
{

    /**
     * @return user a user used for unit testing with has only the ip set
     */
    function user_ip(): user
    {
        $usr = new user();
        $usr->ip_addr = users::TEST_USER_IP;
        return $usr;
    }

    /**
     * TODO Prio 1 fill up all used vars
     * @return user used for unit testing with all vars set
     */
    function user_filled(): user
    {
        $usr = new user();
        $usr->set_name(users::TEST_USER_NAME);
        $usr->ip_addr = users::TEST_USER_IP;
        $usr->excluded = true;
        return $usr;
    }

    /**
     * @return user a user used for unit testing with the test profile
     */
    function user_sys_test(): user
    {
        $usr = new user();
        $usr->set(users::SYSTEM_TEST_ID, users::SYSTEM_TEST_NAME, users::SYSTEM_TEST_EMAIL);
        $usr->set_profile(user_profiles::TEST);
        $usr->set_description(users::SYSTEM_TEST_COM);
        return $usr;
    }

    /**
     * @return user a user used for unit testing with the admin profile
     */
    function user_sys_admin(): user
    {
        $usr = new user();
        $usr->set(users::SYSTEM_ADMIN_ID, users::SYSTEM_ADMIN_NAME, users::SYSTEM_ADMIN_EMAIL);
        $usr->set_profile(user_profiles::ADMIN);
        return $usr;
    }

    /**
     * @return user the system user for the database updates
     */
    function system_user(): user
    {
        $sys_usr = new user;
        $sys_usr->id = users::SYSTEM_ID;
        $sys_usr->name = users::SYSTEM_NAME;
        $sys_usr->code_id = users::SYSTEM_CODE_ID;
        $sys_usr->dec_point = shared_config::DEFAULT_DEC_POINT;
        $sys_usr->thousand_sep = shared_config::DEFAULT_THOUSAND_SEP;
        $sys_usr->percent_decimals = shared_config::DEFAULT_PERCENT_DECIMALS;
        $sys_usr->profile_id = user_profiles::SYSTEM_ID;
        return $sys_usr;
    }

}