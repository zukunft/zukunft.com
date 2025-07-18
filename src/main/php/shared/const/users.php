<?php

/*

    shared/const/users.php - users used by the system
    ----------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\const;

class users
{

    // users used by the system
    // *_NAME the fixed name of system users
    // *_COM is the tooltip/description of the link to the external reference
    // *_IP the internet protocol address of one user for system testing
    // *_TYPE is the code_id of the user group
    // *_ID the fixed database due to the initial setup

    // system users

    // the fixed system user
    // the system user that should only be used for internal processes and to log system tasks
    const SYSTEM_ID = 1;
    const SYSTEM_NAME = 'zukunft.com system';
    const SYSTEM_COM = 'the internal zukunft.com system user that should never log in and is mainly used for the initial setup';
    const SYSTEM_CODE_ID = 'system'; // unique id to select the user
    const SYSTEM_EMAIL = 'system@zukunft.com';

    // to prevent any user to use the name localhost
    const LOCALHOST_NAME = 'localhost';

    // the system admin user that should only be used in a break-glass event to recover other admin users
    const SYSTEM_ADMIN_ID = 2;
    const SYSTEM_ADMIN_NAME = 'zukunft.com local admin';
    const SYSTEM_ADMIN_COM = 'the fallback zukunft.com admin user that should only be used in a break-glass event to recover other admin users';
    const SYSTEM_ADMIN_CODE_ID = 'admin';
    const SYSTEM_ADMIN_EMAIL = 'admin@zukunft.com';
    const SYSTEM_ADMIN_IP = SYSTEM_ADMIN_IP;

    // the user that performs the system tests
    const SYSTEM_TEST_ID = 3;
    const SYSTEM_TEST_NAME = 'zukunft.com system test';
    const SYSTEM_TEST_EMAIL = 'test@zukunft.com';
    const SYSTEM_TEST_CODE_ID = 'test';
    const SYSTEM_TEST_COM = 'the internal zukunft.com user used for integration tests that should never be shown to the user but is used to check if integration test data is completely removed after the tests';

    // the user that acts as a partner for the system tests
    // so that multi-user behaviour can be tested
    const SYSTEM_TEST_PARTNER_ID = 4;
    const SYSTEM_TEST_PARTNER_NAME = 'zukunft.com system test partner'; // to test that the user sandbox is working e.g. that changes of the main test user has no impact of another user simulated by this test user
    const SYSTEM_TEST_PARTNER_CODE_ID = 'test_partner';
    const SYSTEM_TEST_PARTNER_EMAIL = 'test.partner@zukunft.com';

    // an admin user to test the allow of functions only allowed for administrators
    const SYSTEM_TEST_ADMIN_ID = 5;
    const SYSTEM_TEST_ADMIN_NAME = 'zukunft.com system test admin';
    const SYSTEM_TEST_ADMIN_CODE_ID = 'admin';
    const SYSTEM_TEST_ADMIN_EMAIL = 'test.admin@zukunft.com';

    // a normal user to test the deny of functions only allowed for administrators
    // and as a fallback owner
    const SYSTEM_TEST_NORMAL_ID = 6;
    const SYSTEM_TEST_NORMAL_NAME = 'zukunft.com system test no admin';
    const SYSTEM_TEST_NORMAL_CODE_ID = 'test_normal';
    const SYSTEM_TEST_NORMAL_EMAIL = 'support.normal@zukunft.com';

    // an internal zukunft.com user to automatically create normal users
    const SYSTEM_SIGNUP_CODE_ID = 'signup';


    // system testing
    const TEST_NAME = 'standard user view for all users';
    const TEST_IP = '66.249.64.95'; // used to check the blocking of an IP address

    // a test user for db write tests
    const TEST_USER_NAME = 'zukunft.com system write test user';
    const TEST_USER_COM = 'test description if it can be added to the user via import';
    // invalid address used to test creating a new user
    const TEST_USER_IP = '258.257.256.255';

    // list of predefined usernames used for the system and for testing that are expected to be never used or changed
    const RESERVED_NAMES = array(
        self::SYSTEM_NAME,
        self::SYSTEM_ADMIN_NAME,
        self::LOCALHOST_NAME,
        self::TEST_NAME,
        self::TEST_USER_NAME,
    );

    // array of usernames that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::SYSTEM_NAME
    );

    // list of usernames that are only used for system testing and that does not create log entries
    const TEST_NO_LOG = [
        //self::TEST_USER_NAME
    ];


    // change right levels to prevent access level gaining
    const RIGHT_LEVEL_USER = 10;
    const RIGHT_LEVEL_ADMIN = 60;
    const RIGHT_LEVEL_DEVELOPER = 80;
    const RIGHT_LEVEL_SYSTEM_TEST = 90;
    const RIGHT_LEVEL_SYSTEM = 99;

}
