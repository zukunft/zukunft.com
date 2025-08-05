<?php

/*

    test.php - run all internal code consistency TESTs
    --------

    these unit and intergration test inlcudes reading must have data from the database
    and writing of some test data to the database, which are cleand up after the tests
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

// standard zukunft header for callable php files to allow debugging and use of the library
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

// path for the general tests and test setup
const TEST_PHP_UTIL_PATH = TEST_PHP_PATH . 'utils' . DIRECTORY_SEPARATOR;

// load the base testing functions
include_once TEST_PHP_UTIL_PATH . 'test_base.php';

// load the main test control class
include_once TEST_PHP_UTIL_PATH . 'all_tests.php';

use cfg\user\user;
use test\all_tests;


global $db_con;

// open database and display header
$db_con = prg_start("unit and integration testing", '', false, true);

if ($db_con->is_open()) {

    // load the session user parameters
    $start_usr = new user;
    $result = $start_usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($start_usr->id() > 0) {
        if ($start_usr->is_admin()) {

            // run all unit, read and write tests
            (new all_tests())->run_all_tests();

        } else {
            echo 'Only admin users are allowed to start the system testing. Login as an admin for system testing.' . "\n";
        }
    }

    // Closing connection
    prg_end($db_con, false);
}
