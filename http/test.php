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
  - separate unit, db_unit and integration tests
  - use always synthetic reserved words and values for testing that are removed after the tests
  - use for testing only data that is supposed never to be used by any user e.g. use "TestWord" instead of "Company"
  - before starting the system test, check that really no user has used any test name and if create a warning and stop the test
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
global $debug;

if (isset($_GET['debug'])) {
    $debug = $_GET['debug'];
} else {
    $debug = 0;
}

// load the main functions
include_once '../src/main/php/zu_lib.php';

// open database and display header
$db_con = prg_start("unit and integration testing");

// load the testing base functions
include_once '../src/test/php/test_base.php';

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    if ($usr->is_admin()) {

        // prepare testing
        test_start();

        // --------------------------------------
        // start testing the system functionality
        // --------------------------------------

        run_system_test();
        run_user_test();

        import_base_config();
        create_base_words();
        create_base_phrases();
        create_base_times();
        create_base_formulas();
        create_base_formula_links();
        create_base_views();

        run_db_link_test();
        //run_lib_test ();
        run_string_unit_tests(); // test functions not yet split into single unit tests
        run_math_test();
        run_word_test();
        run_word_ui_test();
        run_word_display_test();
        run_word_list_test();
        run_word_link_test();
        run_ref_test();
        run_phrase_test();
        run_phrase_group_test();
        run_phrase_group_list_test();
        run_graph_test();
        run_verb_test();
        run_term_test();
        run_value_test();
        run_value_ui_test();
        run_source_test();
        run_expression_test();
        run_formula_test();
        run_formula_list_test();
        run_formula_ui_test();
        run_formula_link_test();
        run_formula_link_list_test();
        run_formula_trigger_test();
        run_formula_value_test();
        run_formula_value_list_test();
        run_formula_element_test();
        run_formula_element_list_test();
        run_formula_element_group_test();
        run_batch_job_test();
        run_batch_job_list_test();
        run_view_test();
        run_view_component_test();
        run_view_component_link_test();
        //run_display_test ();
        run_export_test();
        //run_permission_test ();
        run_legacy_test();

        // testing cleanup to remove any remaining test records
        run_test_cleanup();

        // start the integration tests by loading the the base and sample data
        run_import_test(unserialize(TEST_IMPORT_FILE_LIST));

        // display the test results
        zu_test_dsp_result();
    }
}

// Closing connection
prg_end($db_con);
