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

// standard zukunft header for callable php files to allow debugging and use of the library
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

// path for the general tests and test setup
const TEST_PHP_UTIL_PATH = TEST_PHP_PATH . 'utils' . DIRECTORY_SEPARATOR;

// load the base testing functions
include_once TEST_PHP_UTIL_PATH . 'test_base.php';

// load the main test control class
include_once TEST_PHP_UTIL_PATH . 'all_tests.php';

use cfg\user\user;
use test\all_tests;
use test\format;


global $db_con;

// open database and display header
$db_con = prg_start("db reset", '', false);

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

        // run the unit tests and reset the database
        $t->run_unit();
        $t->run_db_recreate();

        // display the test results
        if ($t->format == format::HTML) {
            $t->dsp_result_html();
        } else {
            $t->dsp_result();
        }

    } else {
        echo 'Only admin users are allowed to reset the database' . "\n";
    }
}

// Closing connection
prg_end($db_con, false);
