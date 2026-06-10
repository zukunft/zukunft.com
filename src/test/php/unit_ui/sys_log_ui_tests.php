<?php

/*

    test/unit/html/sys_log.php - testing of the system log display functions
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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list as sys_log_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class sys_log_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_sys = new test_sys_log($t);

        $sys_usr = new user;
        $sys_usr->load_by_id(users::SYSTEM_ID);
        $sys_usr_ui = new user_ui($sys_usr->api_json());

        // start the test section (ts)
        $ts = 'unit ui html system log ';
        $t->header($ts);

        // test the system log html display functions
        $test_page = $html->text_h2('system log display test');
        $log_lst = new sys_log_list_ui($t_sys->sys_log_list()->api_json());
        $test_page .= 'user view of a table with system log entries<br>';
        $test_page .= $log_lst->display() . '<br>';
        $test_page .= 'admin view of a table with system log entries<br>';
        $test_page .= $log_lst->display_admin($sys_usr_ui) . '<br>';

        $t->html_page_test($test_page, 'sys_log', 'sys_log', $t);
    }

}