<?php

/*

    test_horizontal.php - run the horizontal tests for the main classes
    -------------------

    check the fill, reset, api, diff, import, sql and usage functions


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

// load the main test class to get the test environment
include_once TEST_PHP_PATH . 'test_app.php';

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

// load the base testing functions
include_once test_paths::UTILS . 'test_base.php';

// load the main test control class
include_once test_paths::UTILS . 'all_tests.php';

include_once test_paths::UNIT . 'horizontal_tests.php';
include_once test_paths::UNIT_READ . 'horizontal_read_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log_format;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\unit\horizontal_tests;
use Zukunft\ZukunftCom\test\php\unit_read\horizontal_read_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\horizontal_ui_tests;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use Zukunft\ZukunftCom\test\php\test_app;

global $db_con;
global $cac;

// open the session, database and load the environment
$app = new test_app();
$db_con = $app->start("horizontal tests", true);
if ($db_con->is_open()) {

    // load the session user parameters
    $start_usr = new user;
    $result = $start_usr->get();
    $cac->set_user($start_usr);

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($start_usr->id() > 0) {
        if ($start_usr->is_admin()) {

            global $t_cac;

            // init tests
            $t = new all_tests();
            $t->header('Start zukunft.com horizontal tests');
            $t->set_users();
            $usr_msg = new user_message();

            // run the horizontal tests
            new horizontal_tests()->run($t);
            new horizontal_read_tests()->run($t);
            new horizontal_ui_tests()->run($t);

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
    $app->end($db_con, false);

}