<?php

/*

    test_unit.php - run the internal unit tests without db read or write
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

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script may only be run from the command line.');
}

include_once 'test_const.php';

// load the main test class to get the test environment
include_once TEST_PHP_PATH . 'test_app.php';

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\test_app;

// load the base testing functions
include_once test_paths::UTILS . 'test_base.php';

// load the main test control class
include_once test_paths::UTILS . 'all_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log_format;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\utils\all_tests;

global $db_con;
global $cac;

// open database and display header
$app = new test_app();
$msg = new user_message();
$db_con = $app->start("unit tests", $msg);

if ($db_con->is_open()) {

    // load the session user parameters
    $start_usr = new user;
    $result = $start_usr->get($msg);
    $cac->set_user($start_usr);

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($start_usr->id() > 0) {
        if ($start_usr->is_admin()) {

            global $t_cac;

            // init tests
            $t = new all_tests();
            $t->set_users();
            $t->header('Start zukunft.com unit tests');
            // login so that the api calls of the test scripts are permitted
            // also on a pod that blocks the changes of a user without login
            $t->api_login();
            $ui = new frontend('unit tests');
            $usr_ui = new user_ui($t->usr1->api_json());
            $msg_ui = new user_message_ui($usr_ui);
            $ui->load_dummy_cache_from_test_resources($msg_ui);

            // run a list of selected tests
            $t->run_unit($ui);

            // end the admin session used for the api calls of the test scripts
            $t->api_logout();

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