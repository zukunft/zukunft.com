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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED_ENUM . 'user_types.php';
include_once paths::SHARED_ENUM . 'user_statuum.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\enum\user_types;
use Zukunft\ZukunftCom\main\php\shared\enum\user_statuum;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateMalformedStringException;
use DateTime;

class test_users
{

    /*
     * init
     */

    // use the global test environment only used for cleanup, so in many cases just null
    private ?test_cleanup $env;

    function __construct(?test_cleanup $env = null)
    {
        $this->env = $env;
    }


    /*
     * cleanup
     */

    /**
     * delete any remaining test words for a clean test start
     */
    function cleanup(string $ts): void
    {
        $this->env->subheader($ts . 'cleanup');
        foreach (users::TEST_USERS as $usr_name) {
            $this->env->write_named_cleanup_user($usr_name, $this->env->usr_system);
        }
    }


    /*
     * unit
     */

    /**
     * @return user a user used for unit testing with has only the ip set
     */
    function user_ip(): user
    {
        $usr = new user();
        $usr->ip_addr = users::TEST_USER_IP;
        $usr->created = new DateTime(users::TEST_USER_LOGIN_TIME);
        return $usr;
    }

    /**
     * TODO Prio 1 fill up all used vars
     * @return user used for unit testing with all vars set
     */
    function user_filled(): user
    {
        global $sys;

        $t_trm = new test_terms($this->env);
        $t_msk = new test_views($this->env);
        $t_src = new test_sources($this->env);

        $usr = new user();
        $usr->name = users::TEST_USER_NAME;
        $usr->ip_addr = users::TEST_USER_IP;
        $usr->email = users::TEST_USER_MAIL;

        $pw_hash = hash('sha256',users::TEST_USER_PASSWORD);
        $usr->password = $pw_hash;
        $usr->activation_key = users::TEST_USER_ACTIVATION_KEY;
        $timeout = new DateTime(users::TEST_USER_LOGIN_TIME);
        try {
            // TODO Prio 1 get timeout duration from the system config
            $timeout->modify('+1 day');
        } catch (DateMalformedStringException $e) {
            log_err('timeout setting failed due to ' . $e->getMessage());
        }
        $usr->activation_timeout = $timeout;
        $usr->db_now = new DateTime(users::TEST_USER_LOGIN_TIME);
        $usr->last_login = new DateTime(users::TEST_USER_LOGIN_TIME);
        $usr->last_logoff = new DateTime(users::TEST_USER_LOGOFF_TIME);

        $usr->profile_id = $sys->typ_lst->usr_pro->id(user_profiles::NORMAL);
        $usr->code_id = users::TEST_USER_ACTIVATION_KEY;
        $usr->type_id = $sys->typ_lst->usr_typ->id(user_types::GUEST);
        $usr->right_level = user_profiles::NORMAL_LEVEL;
        $usr->status_id = $sys->typ_lst->usr_sta->id(user_statuum::ACTIVE);
        $usr->excluded = true;

        $usr->created = new DateTime(users::TEST_USER_LOGIN_TIME);
        $usr->description = users::TEST_USER_COM;
        $usr->first_name = users::TEST_USER_NAME;
        $usr->last_name = users::TEST_USER_LAST_NAME;

        $usr->trm = $t_trm->term();
        $usr->msk = $t_msk->view();
        $usr->src = $t_src->source();

        return $usr;
    }

    /**
     * @return user a user used for unit testing with the test profile
     */
    function user_sys_test(): user
    {
        global $sys;

        $usr = new user();
        $usr->set(users::SYSTEM_TEST_ID, users::SYSTEM_TEST_NAME, users::SYSTEM_TEST_EMAIL);
        $usr->profile_id = $sys->typ_lst->usr_pro->id(user_profiles::TEST);
        $usr->description = users::SYSTEM_TEST_COM;
        $usr->created = new DateTime(users::TEST_USER_LOGIN_TIME);
        return $usr;
    }

    /**
     * @return user a user used for unit testing with the admin profile
     */
    function user_sys_admin(): user
    {
        global $sys;

        $usr = new user();
        $usr->set(users::SYSTEM_ADMIN_ID, users::SYSTEM_ADMIN_NAME, users::SYSTEM_ADMIN_EMAIL);
        $usr->profile_id = $sys->typ_lst->usr_pro->id(user_profiles::ADMIN);
        return $usr;
    }

    function user_dev(user_message $msg): user
    {
        $usr = new user();
        $usr->set(users::DEV_ID, users::DEV_NAME, users::DEV_EMAIL);
        $usr->set_profile(user_profiles::DEV, $msg);
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