<?php

/*

    reset_db.php - drop and recreate the database.
    ------------

    TODO FOR DEVELOPMENT ONLY! Remove completely before production.


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

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\test_app;

// load the base testing functions
include_once test_paths::UTILS . 'test_base.php';

// load the main test control class
include_once test_paths::UTILS . 'all_tests.php';

include_once test_paths::CREATE . 'test_db_load.php';

use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log_format;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\all_tests;

global $db_con;

// init main objects
$msg = new user_message();

// open database and display header
$app = new test_app();
$db_con = $app->start("db reset", $msg, true);

if ($db_con->is_open()) {

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
            $t->header('drop and recreate zukunft.com database');
            $ui = new frontend('reset db');
            $usr_ui = new user_ui($t->usr1->api_json());
            $msg_ui = new user_message_ui($usr_ui);
            $ui->load_dummy_cache_from_test_resources($msg_ui);

            if (getenv(ENVIRONMENT) == ENV_DEV) {

                // run the unit tests and reset the database
                $t = new all_tests();
                $t->run_unit($ui);
                $t->run_db_recreate($msg);

                // login after the database recreate so that the api calls of the following
                // checks are permitted also on a pod that blocks the changes of a user
                // without login; a login before the recreate would point to a dropped session user
                $t->api_login();

                // create the test dataset to check the basic write functions
                // TODO Prio 3 make sure that all are created by import instead
                $t_db = new test_db_load($t);
                $t_db->create_unit_test_db_entries($t);

                // check and update the fixed csv files
                // e.g. to have an indication which words might be missing due to the code changes
                // after a database reset and refill with the initial setup data
                // the main database tables should always contain the same rows
                // or there is a good reason due to some code changes
                // these fixed csv files help to detect the impact of code changes
                // e.g. if some words are missing due to different error handling
                $t_db->csv_recreate($msg);

                // check and update the api test files that still contain database ids which are
                // not yet fixed e.g. the component ids that shift as soon as a component is added
                // to a seed view, so that these files match the freshly reset database
                $msg->usr = $t->usr1;
                $t_db->update_files_with_not_yet_fixed_db_id($t, $msg);

                // as the last step verify the type list api message against the fully reset database
                // because src/test/resources/api/type_lists/type_lists.json is used by the unit tests
                // if the type_list created by this reset_db script differs
                // from the type_list created by test.php
                // most likely new fields have not yet been added to the
                // src/main/resources/db_code_links/change_fields.csv of the predefined fields
                $msg->usr = $t->usr1;
                $t_db->type_list_check($t, $msg);

               // end the admin session used for the api calls of the test scripts
                $t->api_logout();

                // display the test results
                if ($t->format == text_log_format::HTML) {
                    $t->dsp_result_html();
                } else {
                    $t->dsp_result();
                }
            } elseif (getenv(ENVIRONMENT) == ENV_UA) {
                echo 'planned is an automatic copy from the corresponding production database to this user acceptance test database, but it is not yet implemented' . "\n";
            } else {
                echo 'Only admin users are allowed to reset the database' . "\n";
            }

        } else {
            echo 'Only admin users are allowed to reset the database' . "\n";
        }
    }

    // Closing connection
    $app->end($db_con, false);
}