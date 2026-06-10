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
include_once html_paths::USER . 'user.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once test_paths::CREATE . 'test_sys_log.php';
include_once test_paths::UNIT . 'sys_log_tests.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\ui_log;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class user_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;

        $t_sys = new test_sys_log($t);
        $log = new ui_log();

        // start the test section (ts)
        $ts = 'unit ui html user ';
        $t->header($ts);

        $usr_ui = new user_ui();
        $usr_ui->id = 1;
        $usr_ui->name = 'zukunft.com';
        $usr_ui->email = 'heang@zukunft.com';
        $usr_ui->first_name = 'Heang';
        $usr_ui->last_name = 'Lor';
        $test_page = $usr_ui->form_edit(1) . '<br>';

        $t->subheader($ts . 'system errors');

        $test_name = 'the open system errors related to the user are listed';
        $err_html = $log->user_system_errors($t_sys->list_for_user_ui(), msg_id::USER_SYSTEM_ERRORS);
        $t->assert_text_contains($test_name, $err_html, sys_log_tests::TV_LOG_TEXT);
        $test_page .= $err_html . '<br>';

        $test_name = 'the error list is limited to the most relevant entries';
        $t->assert_text_not_contains($test_name, $t_sys->list_for_user_ui()->head(1)->get_html(), sys_log_tests::T2_LOG_TEXT);

        $test_name = 'without an open system error the user gets the no-error message';
        $err_html = $log->user_system_errors($t_sys->list_for_user_empty_ui(), msg_id::USER_SYSTEM_ERRORS);
        $t->assert_text_contains($test_name, $err_html, $mtr->txt(msg_id::USER_SYSTEM_ERRORS_NONE));

        $t->html_page_test($test_page, 'user', 'user', $t);
    }

}