<?php 

/*

  test_quick.php - for fast internal code consistency TESTing of a part
  --------------

zukunft.com - calc with words

copyright 1995-2020 by zukunft.com AG, Zurich

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
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 1) { echo 'lib loaded<br>'; }

// open database
$db_con = zu_start("test_quick", "", $debug);

// load the session user parameters
$usr = New user;
$result = $usr->get($debug-1);

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
  if ($usr->is_admin($debug)) {

    // load the testing functions
    include_once '../classes/test_base.php'; if ($debug > 9) { echo 'test base loaded<br>'; }

    // ---------------
    // prepare testing
    // ---------------
      
    // system test user to simulate the user sandbox
    // e.g. a value owned by the first user cannot be adjusted by the second user
    // instead a user specific value is created
    $usr = New user_dsp;
    $usr->id = TEST_USER_ID;
    $usr->load_test_user($debug-1);

    $usr2 = New user_dsp;
    $usr2->id = TEST_USER_ID2;
    $usr2->load_test_user($debug-1);

    $start_time = microtime(true);
    $exe_start_time = $start_time;

    $error_counter = 0;
    $timeout_counter = 0;
    $total_tests = 0;

    // --------------------------------------
    // start testing the system functionality 
    // --------------------------------------
      
    run_import_test (unserialize (TEST_IMPORT_FILE_LIST_QUICK), $debug);
    run_value_test ($debug);
    //run_view_test ($debug);
    //run_view_component_test ($debug);
    //run_view_component_link_test ($debug);
    //run_display_test ($debug);
    //run_phrase_group_test ($debug);
    //run_export_test ($debug);
    //run_permission_test ($debug);
    run_ref_test ($debug);
    run_lib_tests ($debug);
    run_lib_test_old ($debug); // test functions not yet split into single unit tests

    run_test_cleanup ($debug);

    // display the test results
    zu_test_dsp_result();
  }
}

// Closing connection
zu_end($db_con, $debug);
