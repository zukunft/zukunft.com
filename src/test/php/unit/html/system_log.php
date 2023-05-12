<?php

/*

    test/unit/html/system_log.php - testing of the system log display functions
    -----------------------------
  

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

namespace test\html;

use html\html_base;
use html\log\system_log_list as system_log_list_dsp;
use test\test_cleanup;

class system_log
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('system log display unit tests');

        // test the system log html display functions
        $test_page = $html->text_h2('system log display test');
        $log_lst = new system_log_list_dsp($t->dummy_system_log_list()->api_json());
        $test_page .= 'user view of a table with system log entries<br>';
        $test_page .= $log_lst->display() . '<br>';
        $test_page .= 'admin view of a table with system log entries<br>';
        $test_page .= $log_lst->display_admin() . '<br>';

        $t->html_test($test_page, 'system_log', $t);
    }

}