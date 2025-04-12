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

namespace unit_ui;

include_once WEB_USER_PATH . 'user.php';

use html\html_base;
use html\user\user as user_dsp;
use test\test_cleanup;

class user_ui_tests
{
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'unit ui html user ';
        $t->header($ts);

        $usr_dsp = new user_dsp();
        $usr_dsp->id = 1;
        $usr_dsp->name = 'zukunft.com';
        $usr_dsp->email = 'heang@zukunft.com';
        $usr_dsp->first_name = 'Heang';
        $usr_dsp->last_name = 'Lor';
        $test_page = $usr_dsp->form_edit(1) . '<br>';
        $t->html_test($test_page, 'user', 'user', $t);
    }

}