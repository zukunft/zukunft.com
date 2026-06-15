<?php

/*

    test_full_load.php - run all tests and then load the full initial setup data
    ------------------

    this runs the same unit, read, api and write tests as test.php and, if the tests
    are fine, additionally imports the complete initial setup data via
    all_unit_write_tests::import_base_data() to fully fill the local database.
    import_base_data() is deliberately not part of test.php because it loads a large
    amount of data that is not needed for the regular test run.
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

// load the main test class to get the test environment
include_once TEST_PHP_PATH . 'test_app.php';
use Zukunft\ZukunftCom\test\php\test_app;

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

// load the base testing functions
include_once test_paths::UTILS . 'test_base.php';

// load the main test control class
include_once test_paths::UTILS . 'all_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log_format;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\helper\MapObject;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\test\php\unit_ui\all_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_workflow\all_workflow_tests;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use const Zukunft\ZukunftCom\test\php\utils\ERROR_LIMIT;
use const Zukunft\ZukunftCom\test\php\utils\FRONTEND_TEST;
use const Zukunft\ZukunftCom\test\php\utils\WORKFLOW_TEST;

global $db_con;

// open database and display header
$app = new test_app();
$db_con = $app->start("unit and integration testing with full data load", '', false, true);

if ($db_con->is_open()) {

    // load the session user parameters
    $start_usr = new user;
    $result = $start_usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($start_usr->id > 0) {
        if ($start_usr->is_admin()) {

            global $sys;

            // run the same test suites as run_all_tests() but without the database
            // read and database write tests, with the unit tests upfront
            $sys->errors = 0;
            $t = new all_tests();
            $t->set_users();
            $t->header('Start zukunft.com tests without database read or write tests');

            // prepare the frontend dummy cache used by the unit and ui tests
            $ui = new frontend('full data load tests');
            $ui->load_dummy_cache_from_test_resources($t->usr1);

            // run the unit tests upfront (no database connection needed)
            $t->run_unit($ui);

            // create the html frontend pages based on the url (no database access)
            if ($sys->errors <= ERROR_LIMIT and FRONTEND_TEST) {
                $ui = new frontend('unit ui tests');
                $ui->load_dummy_cache_from_test_resources($t->usr1);
                new all_ui_tests()->run($t, $ui);
            }

            // run the workflow tests
            if ($sys->errors <= ERROR_LIMIT and WORKFLOW_TEST) {
                $usr_msg_ui = new MapObject()->convertMsgToUi(new user_message());
                new all_workflow_tests()->run_workflow_tests($t, $t->usr1, $usr_msg_ui);
            }

            // display the test results
            if ($t->format == text_log_format::HTML) {
                $t->dsp_result_html();
            } else {
                $t->dsp_result();
            }

            // additionally load the complete initial setup data into the database
            $t->header('Load the full initial setup data');
            $result = $t->import_base_data($t->usr1);
            $result .= $t->import_test_data($t->usr1);
            if ($result != '') {
                echo $result . "\n";
            }

        } else {
            echo 'Only admin users are allowed to start the system testing. Login as an admin for system testing.' . "\n";
        }
    }

    // Closing connection
    $app->end($db_con, false);
}