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
if (isset($_GET['debug'])) {
    $debug = $_GET['debug'];
} else {
    $debug = 0;
}
include_once '../src/main/php/zu_lib.php';

// load the testing functions
include_once '../src/test/php/test_base.php';

// ---------------
// prepare testing
// ---------------

$start_time = microtime(true);
$exe_start_time = $start_time;

$error_counter = 0;
$timeout_counter = 0;
$total_tests = 0;

// just to test the database abstraction layer, but without real connection to any database

$db_con= New sql_db;
$db_con->db_type = SQL_DB_TYPE;
$usr = new user;
$usr->id = SYSTEM_USER_ID;

// --------------------------------------------------
// start unit testing without writing to the database
// --------------------------------------------------

run_unit_db_tests();