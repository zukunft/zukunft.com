<?php

/*

    test/php/utils/all_tests.php - the main test object for all tests (unit, read, write, api, ui and connection)
    ----------------------------
    
    combines unit, read, write, api, ui and connection tests


    separated into:
    unit tests - test_units.php: for fast internal code consistency TESTing of the technical library functions without database connection
    db read tests - test_unit_db.php: for unit testing that only read from the database
    db write tests - test all processes that can be initiated by a user including database writing and database cleanup

    - target is to executes all class methods and all functions at least once
    - in case of errors in the methods automatically a ticket is opened the the table sys_log
    - with zukunft.com/error_update.php the tickets can be view and closed
    - and compares the result with the expected result
    - in case of an unexpected result also a ticket is created
    - check the correct setup of the base words, numbers and formulas
    - always synthetic reserved words and values are used for testing that are defined in each class and are removed after the tests

    TODO
    - move the tests to classes and call all tests with test.php but stop in case of an error
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
    element.php
    element_list.php
    element_group_list.php
    element_group.php
    formula_list.php
    formula_link_list.php
    parameter_type.php
    figure.php
    figure_list.php
    selector.php


    class test that does not yet have at least one test case for every function

    user.php
    user_display.php
    user_log_display.php
    word_display.php
    word_list.php
    triple.php
    triple_list.php
    phrase.php
    phrase_list.php
    group.php
    group_list.php
    verb.php
    verb_list.php
    term.php
    value.php
    value_list.php
    value_list_display.php
    source.php
    formula_link.php
    result.php
    result_list.php
    job.php
    job_list.php
    view.php
    view_display.php
    component.php
    component_link.php
    web/html/button.php
    json.php
    xml.php


    classes that can be tested with later, because they are used mainly for testing

    system_error_log.php
    system_error_log_list.php


    classes that can be tested with later, because they are not yet used

    display_list.php


    Frontend scrips that needs to be tested
    test if frontend scripts at least produce a useful result

    formula_result.php
    formula_test.php
    ..


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace test;


// main test settings
const ERROR_LIMIT = 0; // increase to 1 or more to detect more than one error message with one run
const ONLY_UNIT_TESTS = false; // set to true if only the unit tests should be performed
const RESET_DB = true; // if true the database is completely overwritten for testing; must always be false for UAT and PROD
const RESET_DB_ONLY = false; // true to force resetting the database without any other tests
const QUICK_TEST_ONLY = false; // true to run only a single test for faster debugging
const WRITE_TEST = true; // perform also the db write tests

include_once TEST_UNIT_WRITE_PATH . 'all_unit_write_tests.php';

use unit_write\all_unit_write_tests;

class all_tests extends all_unit_write_tests
{
    function run_all_tests(): void
    {
        global $errors;

        // init tests
        $errors = 0;
        $this->header('Start of all zukunft.com tests');

        if (QUICK_TEST_ONLY) {
            $this->run_single();
        }

        // run the unit tests without database connection
        if (!QUICK_TEST_ONLY) {
            $this->run_unit();
        }

        // run the database read tests
        if ($errors <= ERROR_LIMIT and !ONLY_UNIT_TESTS and !RESET_DB_ONLY and !QUICK_TEST_ONLY) {
            $this->run_unit_db_tests($this);
        }

        if (RESET_DB and $errors <= ERROR_LIMIT and !ONLY_UNIT_TESTS and !QUICK_TEST_ONLY) {
            $this->run_db_recreate();
        }

        if ($errors <= ERROR_LIMIT and !ONLY_UNIT_TESTS and !RESET_DB_ONLY and !QUICK_TEST_ONLY AND WRITE_TEST) {
            $this->run_db_write_tests($this);
        }

        // display the test results
        $this->dsp_result_html();
        $this->dsp_result();
    }

}