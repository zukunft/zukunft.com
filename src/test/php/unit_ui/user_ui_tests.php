<?php

/*

    test/unit/html/user.php - testing of the user html frontend functions
    -----------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::EXECUTE . 'ui_log.php';
include_once html_paths::EXECUTE . 'ui_preview.php';
include_once html_paths::USER . 'user.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once test_paths::CREATE . 'test_sys_log.php';
include_once test_paths::UNIT . 'sys_log_tests.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\ui_log;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_preview;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class user_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;

        $t_sys = new test_sys_log($t);
        $t_usr = new test_users();
        $log = new ui_log();
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html user ';
        $t->header($ts);

        $usr_ui = new user_ui($t_usr->user_sys_test()->api_json());
        $test_page = $usr_ui->form_edit(1) . '<br>';

        $t->subheader($ts . 'system errors');

        $test_name = 'the open system errors related to the user are listed';
        $err_html = $log->user_system_errors($t_sys->list_for_user_ui(), $msg, msg_id::USER_SYSTEM_ERRORS);
        $t->assert_text_contains($test_name, $err_html, sys_log_tests::TV_LOG_TEXT);
        $test_page .= $err_html . '<br>';

        $test_name = 'the error list is limited to the most relevant entries';
        $t->assert_text_not_contains($test_name, $t_sys->list_for_user_ui()->head(1)->get_html($msg), sys_log_tests::T2_LOG_TEXT);

        $test_name = 'without an open system error the user gets the no-error message';
        $err_html = $log->user_system_errors($t_sys->list_for_user_empty_ui(), $msg, msg_id::USER_SYSTEM_ERRORS);
        $t->assert_text_contains($test_name, $err_html, $mtr->txt(msg_id::USER_SYSTEM_ERRORS_NONE));

        $t->subheader($ts . 'profile rights');

        // the admin-only fields (the cached usage and impact numbers) are shown to a developer
        // but never to a user without an elevated profile (see user::sees_admin_fields)
        $test_name = 'a developer user has developer rights';
        $dev_ui = new user_ui($t->usr_dev->api_json());
        $t->assert_true($test_name, $dev_ui->is_developer());

        $test_name = 'a developer sees the admin-only fields';
        $t->assert_true($test_name, $dev_ui->sees_admin_fields());

        $test_name = 'an ip only user has no developer rights';
        $ip_ui = new user_ui($t_usr->user_ip()->api_json());
        $t->assert_false($test_name, $ip_ui->is_developer());

        $test_name = 'an ip only user does not see the admin-only fields';
        $t->assert_false($test_name, $ip_ui->sees_admin_fields());

        // the two normal test users carry the test profile only for the backend write privileges;
        // for the frontend display they act like a normal user (see user::is_system), so most test
        // pages render without the admin-only fields
        $test_name = 'the normal test user is not a system user for the display';
        $usr1_ui = new user_ui($t->usr1->api_json());
        $t->assert_false($test_name, $usr1_ui->is_system());

        $test_name = 'the normal test user does not see the admin-only fields';
        $t->assert_false($test_name, $usr1_ui->sees_admin_fields());

        // the test profile keeps the admin mask access (frontend::admin_mask_denied), because the
        // system view tests render the admin masks as the test user; only the display acts normal;
        // the user comes from the factory and not from $t->usr1, because $t->usr1 carries the test
        // profile only when it has been loaded from the database and the email profile when it is
        // the dummy of the unit tests (see docs/llm/testing.md)
        $test_name = 'a user with the test profile uses the admin masks like a system user';
        $sys_test_ui = new user_ui($t_usr->user_sys_test()->api_json());
        $t->assert_true($test_name, $sys_test_ui->is_system_test());

        $test_name = 'an ip only user is not a system test user';
        $t->assert_false($test_name, $ip_ui->is_system_test());

        $test_name = 'the system user itself keeps the system rights';
        $sys_ui = new user_ui($t_usr->system_user()->api_json());
        $t->assert_true($test_name, $sys_ui->is_system());

        $test_name = 'the system user sees the admin-only fields';
        $t->assert_true($test_name, $sys_ui->sees_admin_fields());

        $t->subheader($ts . 'popup form');

        // the popup form class must also accept a user (e.g. of the user settings form),
        // which is a db object but not a sandbox object
        $preview = new ui_preview();
        $test_name = 'the popup form class of a user form is the user class name';
        $t->assert_true($test_name, $preview->popup_class($usr_ui) != '');
        $test_name = 'without an object the popup form class is empty';
        $t->assert($test_name, $preview->popup_class(), '');

        $t->html_page_test($test_page, 'user', 'user', $msg);
    }

}