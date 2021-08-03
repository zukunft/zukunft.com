<?php

/*

  unit_tests.php - run all unit tests in a useful order
  --------------

  the zukunft.com unit tests should test all class methods, that can be tested without database access


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

/**
 * create word type array for the unit tests without database connection
 */
function unit_test_init_word_types()
{
    global $word_types;

    $word_types = new word_type_list();
    $word_types->load_dummy();

}

/**
 * create formula type array for the unit tests without database connection
 */
function unit_test_init_formula_types()
{
    global $formula_types;

    $formula_types = new formula_type_list();
    $formula_types->load_dummy();

}

/**
 * create view type array for the unit tests without database connection
 */
function unit_test_init_view_types()
{
    global $view_types;

    $view_types = new view_type_list();
    $view_types->load_dummy();

}

/**
 * create view component type array for the unit tests without database connection
 */
function unit_test_init_view_component_types()
{
    global $view_component_types;

    $view_component_types = new view_component_type_list();
    $view_component_types->load_dummy();

}

/**
 * create ref type array for the unit tests without database connection
 */
function unit_test_init_ref_types()
{
    global $ref_types;

    $ref_types = new ref_type_list();
    $ref_types->load_dummy();

}

/**
 * create share type array for the unit tests without database connection
 */
function unit_test_init_share_types()
{
    global $share_types;

    $share_types = new share_type_list();
    $share_types->load_dummy();

}

/**
 * create protection type array for the unit tests without database connection
 */
function unit_test_init_protection_types()
{
    global $protection_types;

    $protection_types = new protection_type_list();
    $protection_types->load_dummy();

}

/**
 * run all unit test in a useful order
 */
function run_unit_tests()
{
    test_header('Start the zukunft.com unit tests');

    // prepare the unit tests
    unit_test_init_word_types();
    unit_test_init_formula_types();
    unit_test_init_view_types();
    unit_test_init_view_component_types();
    unit_text_init_view_component_link_types();
    unit_test_init_ref_types();
    unit_test_init_share_types();
    unit_test_init_protection_types();

    // do the unit tests
    run_string_unit_tests(); // test functions not yet split into single unit tests
    run_word_unit_tests();
    run_word_list_unit_tests();
    run_word_link_list_unit_tests();
    run_view_unit_tests();
    //run_value_unit_tests();
    run_user_sandbox_unit_tests();
    run_ref_unit_tests();
}