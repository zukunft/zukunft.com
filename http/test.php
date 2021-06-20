<?php

/*

  test.php - for internal code consistency TESTing
  --------
  
  executes all class methods and all functions at least once 
  - in case of errors in the methods automatically a ticket is opened the the table sys_log
    with zukunft.com/error_update.php the tickets can be view and closed
  - and compares the result with the expected result
    in case of an unexpected result also a ticket is created
  - check the correct setup of the base words, numbers and formulas  
  
  TODO
  - use YAML export and import for testing the base sets
  - check that the order of the view items cannot be changed by another user
  - add get_xxx functions for all objects and use them
  - send daily report at 07:00 CET and report all errors via email
  - add all missing class functions with at lease one test case
  - check that a object function never changes a parameter 
    e.g. if a formula object is loaded the calculation of a result should not influence the loaded ref text
    instead use a copy of the ref text for the calculation
  - check the usage of "old" functions
  

  classes with a test process ready for version 0.1 (at least one test case for every function)

  user_list.php 
  user_log.php 
  user_log_link.php 
  word.php 
  expression.php 
  formula.php 
  formula_element.php 
  formula_element_list.php 
  formula_element_group_list.php 
  formula_element_group.php 
  formula_list.php 
  formula_link_list.php 
  figure.php 
  figure_list.php 
  display_selector.php 


  class test that does not yet have at least one test case for every function

  user.php 
  user_display.php 
  user_log_display.php 
  word_display.php 
  word_list.php 
  word_link.php 
  word_link_list.php 
  phrase.php 
  phrase_list.php 
  phrase_group.php 
  phrase_group_list.php 
  verb.php 
  verb_list.php 
  term.php
  value.php 
  value_list.php 
  value_list_display.php 
  source.php 
  formula_link.php 
  formula_value.php 
  formula_value_list.php 
  batch_job.php 
  batch_job_list.php 
  view.php 
  view_display.php 
  view_component.php (ex view_component)
  view_component_dsp.php
  view_component_link.php 
  display_button.php 
  display_html.php 
  json.php
  xml.php


  classes that can be tested with later, because they are used mainly for testing

  system_error_log.php 
  system_error_log_list.php 


  classes that can be tested with later, because they are not yet used

  display_list.php 
  value_phrase_link.php 


  Frontend scrips that needs to be tested
  test if frontend scripts at least produce a useful result

  formula_result.php
  formula_test.php
  ..

  
  
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

// open database and display header
$db_con = prg_start("unit and integration testing");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    if ($usr->is_admin($debug)) {

        // load the testing functions
        include_once '../src/test/php/test_base.php';
        if ($debug > 9) {
            echo 'test base loaded<br>';
        }

        // ---------------
        // prepare testing
        // ---------------

        // system test user to simulate the user sandbox
        // e.g. a value owned by the first user cannot be adjusted by the second user
        // instead a user specific value is created
        $usr = new user_dsp;
        $usr->id = TEST_USER_ID;
        $usr->load_test_user($debug - 1);

        $usr2 = new user_dsp;
        $usr2->id = TEST_USER_ID2;
        $usr2->load_test_user($debug - 1);

        // init the times to be able to detect potential timeouts
        $start_time = microtime(true);
        $exe_start_time = $start_time;

        // reset the error counters
        $error_counter = 0;
        $timeout_counter = 0;
        $total_tests = 0;

        // --------------------------------------
        // start testing the system functionality
        // --------------------------------------

        run_system_test($debug);
        run_user_test($debug);

        create_base_words();
        create_base_phrases();
        create_base_times();
        create_base_formulas();
        create_base_formula_links();
        create_base_views();

        run_db_link_test();
        //run_lib_test ();
        run_string_unit_tests(); // test functions not yet split into single unit tests
        run_math_test($debug);
        run_word_test($debug);
        run_word_ui_test($debug);
        run_word_display_test($debug);
        run_word_list_test($debug);
        run_word_link_test($debug);
        run_ref_test($debug);
        run_phrase_test($debug);
        run_phrase_group_test($debug);
        run_phrase_group_list_test($debug);
        run_graph_test($debug);
        run_verb_test($debug);
        run_term_test($debug);
        run_value_test($debug);
        run_value_ui_test($debug);
        run_source_test($debug);
        run_expression_test($debug);
        run_formula_test($debug);
        run_formula_list_test($debug);
        run_formula_ui_test($debug);
        run_formula_link_test($debug);
        run_formula_link_list_test($debug);
        run_formula_trigger_test($debug);
        run_formula_value_test($debug);
        run_formula_value_list_test($debug);
        run_formula_element_test($debug);
        run_formula_element_list_test($debug);
        run_formula_element_group_test($debug);
        run_batch_job_test($debug);
        run_batch_job_list_test($debug);
        run_view_test($debug);
        run_view_component_test($debug);
        run_view_component_link_test($debug);
        //run_display_test ($debug);
        run_export_test($debug);
        //run_permission_test ($debug);
        run_legacy_test($debug);

        // testing cleanup to remove any remaining test records
        run_test_cleanup($debug);

        // start the integration tests by loading the the base and sample data
        run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $debug);

        // display the test results
        zu_test_dsp_result();
    }
}

// Closing connection
prg_end($db_con, $debug);
