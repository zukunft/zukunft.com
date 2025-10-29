<?php

/*

    test_part.php - run only selected unit and integration tests for a faster feedback cycle
    -------------

    checks that only developers and local admin can start the tests


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

include_once 'test_const.php';

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::UNIT_WRITE . 'a_selected_test.php';

use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log_format;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\test\php\unit_write\a_selected_test;


global $db_con;

// open database and display header
$db_con = prg_start("selected tests", '', false, true);

// load the session user parameters
$start_usr = new user;
$result = $start_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($start_usr->id() > 0) {
    if ($start_usr->is_admin()) {

        global $errors;

        // init tests
        $errors = 0;
        $t = new a_selected_test();
        $t->header('Run selected zukunft.com tests');

        // run a list of selected tests
        $t->run();

        // display the test results
        if ($t->format == text_log_format::HTML) {
            $t->dsp_result_html();
        } else {
            $t->dsp_result();
        }

    } else {
        echo 'Only admin users are allowed to start the system testing. Login as an admin for system testing.' . "\n";
    }
}

// Closing connection
prg_end($db_con, false);