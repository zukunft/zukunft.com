<?php

/*

  test_quick.php - TESTing of selected integration tests
  --------------

  similar to test.php but only selecting critical parts for faster testing

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

// load the main functions
include_once '../src/main/php/zu_lib.php';

// open database and display header
$db_con = prg_start("test_quick");

// load the testing base functions
include_once '../src/test/php/utils/test_base.php';

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    if ($usr->is_admin()) {

        // prepare testing
        test_start();

        // -----------------------------------------------
        // start testing the selected system functionality
        // -----------------------------------------------

        // cleanup also before testing to remove any leftovers
        run_test_cleanup();

        run_system_test(); // testing of the basic system functions like ip blocking
        //run_user_test ();   // testing of the user display functions

        // creating the test data
        create_base_words();
        create_base_phrases();
        create_base_sources();
        create_base_times();
        create_base_formulas();
        create_base_formula_links();
        create_base_views();

        run_db_link_test();
        run_string_unit_tests(); // test functions not yet split into single unit tests
        run_math_test();
        run_user_sandbox_test();
        run_word_test();
        //run_word_ui_test();
        //run_word_display_test ();
        run_word_list_test ();
        //run_word_link_test ();
        run_ref_test ();
        /*
        run_phrase_test ();
        run_phrase_group_test ();
        run_phrase_group_list_test ();
        run_graph_test ();
        run_verb_test ();
        run_term_test ();
        run_value_test ();
        run_value_ui_test ();
        run_source_test ();
        run_expression_test ();
        run_formula_test ();
        run_formula_list_test ();
        run_formula_ui_test ();
        run_formula_link_test ();
        run_formula_link_list_test ();
        run_formula_trigger_test ();
        run_formula_value_test ();
        run_formula_value_list_test ();
        run_formula_element_test ();
        run_formula_element_list_test ();
        run_formula_element_group_test ();
        run_batch_job_test ();
        run_batch_job_list_test ();
        run_view_test ();
        run_view_component_test ();
        run_view_component_link_test ();
        //run_display_test ();
        run_export_test ();
        //run_permission_test ();
        run_legacy_test ();

        //run_import_test(unserialize(TEST_IMPORT_FILE_LIST_QUICK));
        run_value_test();
        //run_view_test ();
        //run_view_component_test ();
        //run_view_component_link_test ();
        //run_display_test ();
        //run_phrase_group_test ();
        //run_export_test ();
        //run_permission_test ();
        run_ref_test();

        */

        // testing cleanup to remove any remaining test records
        run_test_cleanup();

        // start the integration tests by loading the the base and sample data
        //run_import_test(unserialize(TEST_IMPORT_FILE_LIST));

        // display the test results
        zu_test_dsp_result();
    }
}

// Closing connection
prg_end($db_con);

test_dsp_result();