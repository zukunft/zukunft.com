<?php

/*

    test/php/unit_ui/all_ui_tests.php - test the html frontend on localhost
    ---------------------------------
  

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

namespace unit_ui;

use const test\TEST_UNIT_UI_PATH;

include_once TEST_UNIT_UI_PATH . 'base_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'type_lists_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'user_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'word_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'word_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'verb_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'triple_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'triple_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'phrase_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'phrase_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'phrase_group_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'term_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'term_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'value_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'value_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'formula_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'formula_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'result_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'result_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'figure_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'figure_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'view_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'view_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'component_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'component_list_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'source_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'reference_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'language_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'change_log_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'sys_log_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'job_ui_tests.php';
include_once TEST_UNIT_UI_PATH . 'system_views_ui_tests.php';

use test\test_cleanup;
use unit\all_unit_tests;

class all_ui_tests extends all_unit_tests
{

    function run(test_cleanup $t): void
    {

        $t->header('html ui unit tests');

        $t->subheader('html ui unit base tests');
        (new base_ui_tests)->run($t);
        (new type_lists_ui_tests)->run($t);
        (new user_ui_tests)->run($t);

        $t->subheader('html ui unit page tests');
        (new word_ui_tests)->run($t);
        (new word_list_ui_tests)->run($t);
        (new verb_ui_tests())->run($t);
        (new triple_ui_tests)->run($t);
        (new triple_list_ui_tests)->run($t);
        (new phrase_ui_tests)->run($t);
        (new phrase_list_ui_tests)->run($t);
        (new phrase_group_ui_tests)->run($t);
        (new term_ui_tests)->run($t);
        (new term_list_ui_tests)->run($t);
        (new value_ui_tests)->run($t);
        (new value_list_ui_tests)->run($t);
        (new formula_ui_tests)->run($t);
        (new formula_list_ui_tests)->run($t);
        (new result_ui_tests)->run($t);
        (new result_list_ui_tests)->run($t);
        (new figure_ui_tests())->run($t);
        (new figure_list_ui_tests)->run($t);
        (new view_ui_tests)->run($t);
        (new view_list_ui_tests)->run($t);
        (new component_ui_tests)->run($t);
        (new component_list_ui_tests)->run($t);
        (new source_ui_tests)->run($t);
        (new reference_ui_tests)->run($t);
        (new language_ui_tests)->run($t);
        (new change_log_ui_tests)->run($t);
        (new sys_log_ui_tests)->run($t);
        (new job_ui_tests)->run($t);
        (new system_views_ui_tests)->run($t);

        $t->subheader('check about page e.g. to check the library');

        $test_name = 'check about page e.g. to check the library';
        $result = file_get_contents('http://localhost/http/about.php');
        $target = 'zukunft.com AG';
        $t->assert_text_contains($test_name, $result, $target);

    }
}