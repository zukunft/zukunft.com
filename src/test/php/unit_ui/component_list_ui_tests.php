<?php

/*

    test/unit/html/component_list.php - testing of the html frontend functions for component lists
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

use html\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component_list.php';

use html\component\component_list as component_list_dsp;
use html\html_base;
use test\test_cleanup;

class component_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html component list ';
        $t->header($ts);

        // test the component list display functions
        $lst = new component_list_dsp($t->component_list()->api_json());
        $test_page = $html->text_h2('component list display test');
        $test_page .= 'component list with tooltip: ' . $lst->name_tip() . '<br>';
        $test_page .= 'component list with link: ' . $lst->name_link() . '<br>';

        $test_page .= '<br>' . $html->text_h2('Selector tests');
        $test_page .= $lst->selector('', 0, 'test_selector', 'No component selected') . '<br>';

        $t->html_test($test_page, 'component_list', 'component_list', $t);
    }

}