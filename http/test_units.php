<?php

/*

  test_units.php - for fast internal code consistency TESTing of the technical library functions without database connection
  ------------

  because these tests are done without the database, we don't care if the is called by anybody, so we don't need the admin user test


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
if (isset($_GET['debug'])) {
    $debug = $_GET['debug'];
} else {
    $debug = 0;
}
include_once '../src/main/php/zu_lib.php';
if ($debug > 1) {
    echo 'lib loaded<br>';
}


// load the testing functions
include_once '../src/test/php/test_base.php';
if ($debug > 9) {
    echo 'test base loaded<br>';
}

// ---------------
// prepare testing
// ---------------

$start_time = microtime(true);
$exe_start_time = $start_time;

// create a list with all prepared sql queries to check if the name is unique
$sql_names = array();

$error_counter = 0;
$timeout_counter = 0;
$total_tests = 0;

// just to test the database abstraction layer, but without real connection to any database

$db_con= New sql_db;
$db_con->db_type = SQL_DB_TYPE;
$usr = new user;
$usr->id = SYSTEM_USER_ID;

// ------------------
// start unit testing
// ------------------

run_string_unit_tests (); // test functions not yet split into single unit tests
run_word_link_list_unit_tests();
run_unit_tests();
