<?php

/*

    test_unit_db.php - for unit testing that only read from the database
    ----------------

    because these tests are read from the database and it does not read any critical data we don't care if the is called by any user


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

// standard zukunft header for callable php files to allow debugging and lib loading
use cfg\log\user;
use test\test_unit_read_db;

global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
const PHP_TEST_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

// open database and display header
$db_con = prg_start("unit testing with database reading");

// load the testing functions
include_once '../src/test/php/utils/test_base.php';

// ---------------
// prepare testing
// ---------------

$start_time = microtime(true);
$exe_start_time = $start_time;

$error_counter = 0;
$timeout_counter = 0;
$total_tests = 0;

// load the session user parameters
$start_usr = new user;
$result = $start_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($start_usr->id() > 0) {
    if ($start_usr->is_admin()) {

        // --------------------------------------------------
        // start unit testing without writing to the database
        // --------------------------------------------------

        // prepare testing
        $t = new test_unit_read_db();

        // set the testing users
        $t->set_users();

        // add the database test entries to complete the testing setup
        // TODO remove ??
        $t->init_unit_db_tests();

        // load the predefined objects like the verbs and system views
        $t->usr1->load_usr_data();

        $t->run_unit_db_tests();

        // display the test results
        $t->dsp_result_html();
        $t->dsp_result();

        // remove the database entries only used for testing
        $t->clean_up_unit_db_tests();

    }
}

// Closing connection
prg_end($db_con);
