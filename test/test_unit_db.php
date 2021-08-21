<?php

/*

  test_unit_db.php - for unti testing that only read from the database
  ----------------

  because these tests are read from the database and it does not read any critical data we don't care if the is called by any user


zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

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
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    if ($usr->is_admin()) {

        // --------------------------------------------------
        // start unit testing without writing to the database
        // --------------------------------------------------

        load_usr_data();

        run_unit_db_tests();

        // display the test results
        zu_test_dsp_result();

    }
}

// Closing connection
prg_end($db_con);

test_dsp_result();
