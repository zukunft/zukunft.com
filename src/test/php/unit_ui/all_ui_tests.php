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

use const\paths as test_paths;
use html\frontend;
use shared\api;

include_once test_paths::UNIT_UI . 'base_ui_tests.php';
//include_once test_paths::UNIT_UI . 'type_lists_ui_tests.php';
include_once test_paths::UNIT_UI . 'user_ui_tests.php';
include_once test_paths::UNIT_UI . 'word_ui_tests.php';
include_once test_paths::UNIT_UI . 'word_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'verb_ui_tests.php';
include_once test_paths::UNIT_UI . 'triple_ui_tests.php';
include_once test_paths::UNIT_UI . 'triple_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'phrase_ui_tests.php';
include_once test_paths::UNIT_UI . 'phrase_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'group_ui_tests.php';
include_once test_paths::UNIT_UI . 'term_ui_tests.php';
include_once test_paths::UNIT_UI . 'term_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'value_ui_tests.php';
include_once test_paths::UNIT_UI . 'value_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'formula_ui_tests.php';
include_once test_paths::UNIT_UI . 'formula_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'result_ui_tests.php';
include_once test_paths::UNIT_UI . 'result_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'figure_ui_tests.php';
include_once test_paths::UNIT_UI . 'figure_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'view_ui_tests.php';
include_once test_paths::UNIT_UI . 'view_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'component_ui_tests.php';
include_once test_paths::UNIT_UI . 'component_list_ui_tests.php';
include_once test_paths::UNIT_UI . 'source_ui_tests.php';
include_once test_paths::UNIT_UI . 'reference_ui_tests.php';
include_once test_paths::UNIT_UI . 'language_ui_tests.php';
include_once test_paths::UNIT_UI . 'change_log_ui_tests.php';
include_once test_paths::UNIT_UI . 'sys_log_ui_tests.php';
include_once test_paths::UNIT_UI . 'job_ui_tests.php';
include_once test_paths::UNIT_UI . 'system_views_ui_tests.php';
include_once test_paths::UNIT_UI . 'start_ui_tests.php';

use test\test_cleanup;
use unit\all_unit_tests;

class all_ui_tests extends all_unit_tests
{

    function run(test_cleanup $t, frontend $ui): void
    {

        // start the test section (ts)
        $ts = 'unit ui html ';
        $t->header($ts);

        $t->subheader($ts . 'base');
        (new base_ui_tests)->run($t);
        (new user_ui_tests)->run($t);
        (new horizontal_ui_tests)->run($t);

        $t->subheader($ts . 'page');
        (new word_ui_tests)->run($t, $ui->typ_lst_cache);
        (new word_list_ui_tests)->run($t);
        (new verb_ui_tests())->run($t);
        (new triple_ui_tests)->run($t, $ui);
        (new triple_list_ui_tests)->run($t);
        (new phrase_ui_tests)->run($t);
        (new phrase_list_ui_tests)->run($t);
        (new group_ui_tests)->run($t);
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
        (new view_ui_tests)->run($t, $ui);
        (new view_list_ui_tests)->run($t);
        (new component_ui_tests)->run($t);
        (new component_list_ui_tests)->run($t);
        (new source_ui_tests)->run($t);
        (new reference_ui_tests)->run($t);
        (new language_ui_tests)->run($t);
        (new change_log_ui_tests)->run($t);
        (new sys_log_ui_tests)->run($t);
        (new job_ui_tests)->run($t);

        // TODO compare with run_ui_test in all_unit_read_tests
        //(new start_ui_tests)->run($t);
        (new system_views_ui_tests)->run($t, $ui);

        $t->subheader($ts . 'check about page e.g. to check the library');

        $test_name = 'check about page e.g. to check the library';
        $result = file_get_contents(api::HOST_TESTING .  'http/about.php');
        $target = 'zukunft.com AG';
        $t->assert_text_contains($test_name, $result, $target);

    }
}