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

include_once 'test_const.php';

// load the main test class to get the test environment
include_once TEST_PHP_PATH . 'test_app.php';
use Zukunft\ZukunftCom\test\php\test_app;

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

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

// open database and display header
$app = new test_app();
$db_con = $app->start("db reset", '', false, true);

if ($db_con->is_open()) {

    // load the session user parameters
    $start_usr = new user;
    $result = $start_usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($start_usr->id() > 0) {
        if ($start_usr->is_admin()) {

            global $errors;

            // init tests
            $errors = 0;
            $t = new all_tests();
            $t->header('drop and recreate zukunft.com database');

            // TODO Prio 0 allow only in DEV, in TEST copy db from PROD

            if (getenv(ENVIRONMENT) == ENV_DEV) {

                // run the unit tests and reset the database
                $t = new all_tests();
                $t->run_unit();
                $t->run_db_recreate();

                // recreate the type list api message based on the updated db
                // because this json is used for the unit tests
                // if the type_list created by this reset_db script differs
                // from the type_list created by the test.php differs
                // most likely new fields have not yet been added to the
                // src/main/resources/db_code_links/change_fields.csv of the predefined fields
                $t_db = new test_db_load($t);
                $t_db->type_list_recreate($t, $t->usr1);

                // display the test results
                if ($t->format == text_log_format::HTML) {
                    $t->dsp_result_html();
                } else {
                    $t->dsp_result();
                }
            } else {
                echo 'Only admin users are allowed to reset the database' . "\n";
            }

        } elseif (getenv(ENVIRONMENT) == ENV_UA) {
            echo 'planned is an automatic copy from the corresponding production database to this user acceptance test database, but it is not yet implemented' . "\n";
        } else {
            echo 'Only admin users are allowed to reset the database' . "\n";
        }
    }

    // Closing connection
    $app->end($db_con, false);
}