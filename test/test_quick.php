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
$start_usr = new user;
$result = $start_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($start_usr->id > 0) {
    if ($start_usr->is_admin()) {

        // prepare testing
        $t = new testing();

        // switch to the test user
        $usr = new user;
        $usr->load_user_by_profile(user::SYSTEM_TEST);
        if ($usr->id > 0) {

            // create the testing users
            $t->set_users();

            // cleanup also before testing to remove any leftovers
            run_test_cleanup($t);

            // -----------------------------------------------
            // start testing the selected system functionality
            // -----------------------------------------------

            run_system_test($t); // testing of the basic system functions like ip blocking
            //run_user_test ();   // testing of the user display functions

            // creating the test data
            create_test_words($t);
            create_test_phrases($t);
            create_test_sources($t);
            create_base_times($t);
            create_test_formulas($t);
            create_test_formula_links($t);
            create_test_views($t);
            create_test_view_components($t);
            create_test_view_component_links($t);
            create_test_values($t);

            /*
            run_db_link_test($t);
            run_string_unit_tests($t); // test functions not yet split into single unit tests
            run_math_test($t);
            run_user_sandbox_test($t);
            run_word_tests($t);
            //run_word_ui_test($t);
            run_word_display_test ($t);
            run_word_list_test($t);
            run_word_link_test ($t);
            run_ref_test($t);
            run_phrase_test ($t);
            run_phrase_group_test ($t);
            run_phrase_group_list_test ($t);
            run_graph_test ($t);
            run_verb_test ($t);
            run_term_test ($t);
            run_value_test ($t);
            //run_value_ui_test ($t);
            run_source_test ($t);
            */
            run_expression_test ($t);
            run_formula_test ($t);
            run_formula_list_test ($t);
            //run_formula_ui_test ($t);
            run_formula_link_test ($t);
            run_formula_link_list_test ($t);
            run_formula_trigger_test ($t);
            run_formula_value_test ($t);
            run_formula_value_list_test ($t);
            run_formula_element_test ($t);
            run_formula_element_list_test ($t);
            run_formula_element_group_test ($t);
            /*
            run_batch_job_test ($t);
            run_batch_job_list_test ($t);
            run_view_test ($t);
            run_view_component_test ($t);
            run_view_component_link_test ($t);
            run_display_test ($t);
            run_export_test ($t);
            //run_permission_test ($t);
            run_legacy_test($t);
            */


            // testing cleanup to remove any remaining test records
            run_test_cleanup($t);

            // start the integration tests by loading the base and sample data
            //run_import_test(unserialize(TEST_IMPORT_FILE_LIST_QUICK), $t);
            //run_import_test(unserialize(TEST_IMPORT_FILE_LIST), $t);

            // display the test results
            $t->dsp_result_html();
            $t->dsp_result();
        }
    }
}

// Closing connection
prg_end($db_con);