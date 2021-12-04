<?php

/*

  test_db_upgrade.php - to test the database upgrades
  -------------------


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

// open database and display header
$db_con = prg_start("database upgrade testing");

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
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    if ($usr->is_admin()) {

        // prepare testing
        $t = new testing();

        db_upgrade_0_0_3($db_con);

        // display the test results
        $t->dsp_result_html();
        $t->dsp_result();

    }
}

// Closing connection
prg_end($db_con);
