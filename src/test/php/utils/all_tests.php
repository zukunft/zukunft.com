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
    - with zukunft.com/view.php?m=66 (views::ERROR_UPDATE_ID) the tickets can be viewed and closed
    - and compares the result with the expected result
    - in case of an unexpected result also a ticket is created
    - check the correct setup of the base words, numbers and formulas
    - always synthetic reserved words and values are used for testing that are defined in each class and are removed after the tests

    TODO
    - move the tests to classes and call all tests with test.php but stop in case of an error
    - use for testing only data that is supposed never to be used by any user e.g. use "TestWord" instead of "company"
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

namespace Zukunft\ZukunftCom\test\php\utils;

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

// main test settings
const ERROR_LIMIT = 0; // increase to 1 or more to detect more than one error message with one run
const ONLY_UNIT_TESTS = true; // set to true if only the unit tests should be performed
const ONLY_UNIT_TESTS_DEV = false; // dito for development
const RESET_DB = false; // if true the database is completely overwritten for testing; must always be false for UAT and PROD
const RESET_DB_DEV = true; // dito for development
const QUICK_TEST_ONLY = false; // true to run only a single test for faster debugging
const API_TEST = true; // perform also the api requests and check the results
const FRONTEND_TEST = true; // create the HTML frontend pages b ase on the URLs
const WORKFLOW_TEST = true; // perform also the workflow tests
const WRITE_TEST = true; // perform also the db write tests
const INTEGRATION_TEST = true; // perform also actual requests to external systems like wikidata and check if the responses are as expected


include_once test_paths::UNIT_WRITE . 'all_unit_write_tests.php';
include_once test_paths::UNIT_API . 'all_api_tests.php';
include_once test_paths::UNIT_WORKFLOW . 'all_workflow_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log_format;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\unit_api\all_api_tests;
use Zukunft\ZukunftCom\test\php\unit_ui\all_ui_tests;
use Zukunft\ZukunftCom\test\php\unit_workflow\all_workflow_tests;
use Zukunft\ZukunftCom\test\php\unit_write\a_selected_test;
use Zukunft\ZukunftCom\test\php\unit_write\all_unit_write_tests;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\helper\MapObject;
use Zukunft\ZukunftCom\main\php\web\frontend;

class all_tests extends all_unit_write_tests
{

    function run_all_tests(): void
    {
        global $sys;

        // init
        $sys->errors = 0;
        $t_db = new test_db_load($this);
        $usr_msg = new user_message();
        $map = new MapObject();
        $usr_msg_ui = $map->convertMsgToUi($usr_msg);

        // start the test section (ts)
        $ts = 'Start of all zukunft.com tests ';
        $this->header($ts);
        $this->set_users();
        $ui = new frontend('all tests');
        $ui->load_dummy_cache_from_test_resources($this->usr1);

        // if requested only run some selected tests
        if (QUICK_TEST_ONLY) {
            $t_sel = new a_selected_test();
            $t_sel->run();
        } else {
            // ... otherwise run the test starting with internal unit test

            // first run the unit tests without database connection
            $this->run_unit($ui);

            // run the database read tests also to check if the test results are influenced by any leftovers
            if ($sys->errors <= ERROR_LIMIT) {
                $this->run_unit_db_tests($this);
            }

            // check if database reading via api still produces the expected results
            if ($sys->errors <= ERROR_LIMIT and API_TEST) {
                $t_api = new all_api_tests();
                $t_api->run_api_tests($this, $this->usr1, $usr_msg_ui);
            }

            // database reset is switched off here for better detection of leftovers
            // it can be started via reset_db
            if ($this->db_reset_allowed() and $sys->errors <= ERROR_LIMIT and !$this->only_unit_tests()) {
                $this->run_db_recreate();
            }

            // html page creation based on the url
            if ($sys->errors <= ERROR_LIMIT and FRONTEND_TEST) {
                // test the html ui on localhost without api
                $ui = new frontend('unit ui tests');
                $ui->load_dummy_cache_from_test_resources($this->usr1);
                new all_ui_tests()->run($this, $ui);
            }

            if ($sys->errors <= ERROR_LIMIT and WORKFLOW_TEST) {
                $t_wf = new all_workflow_tests();
                $t_wf->run_workflow_tests($this, $this->usr1, $usr_msg_ui);
            }

            if ($sys->errors <= ERROR_LIMIT and WRITE_TEST) {
                $this->run_db_write_tests($this);
            }

            // recreate the type list api message based on the updated db
            // because this json is used for the unit tests
            $t_db->type_list_check($this, $this->usr1);
        }

        // display the test results
        if ($this->format == text_log_format::HTML) {
            $this->dsp_result_html();
        } else {
            $this->dsp_result();
        }
    }

    private function only_unit_tests(): bool
    {
        if (getenv(ENVIRONMENT) == ENV_DEV) {
            return ONLY_UNIT_TESTS_DEV;
        } else {
            return ONLY_UNIT_TESTS;
        }
    }

    private function db_reset_allowed(): bool
    {
        if (getenv(ENVIRONMENT) == ENV_DEV) {
            return RESET_DB_DEV;
        } else {
            return RESET_DB;
        }
    }

}