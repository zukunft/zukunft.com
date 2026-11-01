<?php

/*

    test_re_pin.php - recreate the database and run the complete import to re-pin the database ids
    ---------------

    the fast cycle to re-baseline the list.csv fixtures and the *_ID consts after an import json
    has changed: the unit tests upfront, the database recreation with the seed import, the csv
    and api fixture refresh and the complete import of the base and test data, but without the
    frontend, workflow and db write tests of test.php

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

use Zukunft\ZukunftCom\test\php\test_app;

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

// load the base testing functions
include_once test_paths::UTILS . 'test_base.php';

// load the main test control class
include_once test_paths::UTILS . 'all_tests.php';
include_once test_paths::CREATE . 'test_db_load.php';

use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log_format;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\all_tests;


global $db_con;

// open database and display header
$app = new test_app();
$msg = new user_message();
$db_con = $app->start("re-pin tests", $msg);

// load the session user parameters
$start_usr = new user;
$result = $start_usr->get($msg);

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($start_usr->id() > 0) {
    if ($start_usr->is_admin()) {

        global $errors;

        // init tests
        $errors = 0;
        $t = new all_tests();
        $t->set_users();
        $t->header('Re-pin the zukunft.com database ids');
        $ui = new frontend('re-pin tests');
        $usr_ui = new user_ui($t->usr1->api_json());
        $msg_ui = new user_message_ui($usr_ui);
        $ui->load_dummy_cache_from_test_resources($msg_ui, file_get_contents(test_files::TYPE_LISTS_CACHE));

        // the database is dropped, so like reset_db.php this runs only on a development pod
        if (getenv(ENVIRONMENT) == ENV_DEV) {

            // the unit tests upfront, because a re-pin on a broken code base is useless
            $t->run_unit($ui);

            // the database recreation with the complete seed import, which assigns the ids
            $t->run_db_recreate($msg);
            // login after the recreate, because a login before would point to a dropped session user
            $t->api_login();
            $t_db = new test_db_load($t);
            $t_db->create_unit_test_db_entries($t);

            // the frontend and workflow tests of test.php are not needed to re-pin the ids
            //new all_ui_tests()->run($t, $ui);
            //new all_workflow_tests()->run($t, $t->usr1, new MapObject()->convertMsgToUi($msg));

            // refresh the list.csv fixtures, the api files with not yet fixed ids and the type
            // list api message, so that the shifted ids show up as a diff of the fixtures
            $t_db->csv_recreate($msg);
            $msg->usr = $t->usr1;
            $t_db->update_files_with_not_yet_fixed_db_id($t, $msg);
            $t_db->type_list_check($t, $msg);

            // the complete import of the base and test data after the fixtures, because the
            // fixtures hold the seed rows only (see test_full_load.php)
            $t->header('Load the full initial setup data');
            $result = $t->import_base_data($t->usr1);
            $result .= $t->import_test_data($t->usr1);
            if ($result != '') {
                echo $result . "\n";
            }

            // end the admin session used for the api calls of the test scripts
            $t->api_logout();
        } else {
            echo 'The database recreation is only allowed on a development pod.' . "\n";
        }

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