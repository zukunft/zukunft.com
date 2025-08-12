<?php

/*

    test/unit/html/view_list.php - testing of the html frontend functions for view lists
    -------------------------------


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

include_once html_paths::VIEW . 'view_list.php';

use html\html_base;
use html\view\view_list as view_list_dsp;
use shared\enum\messages;
use shared\enum\messages as msg_id;
use shared\url_var;
use test\test_cleanup;

class view_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html view list ';
        $t->header($ts);

        // test the view list display functions
        $form = 'view_list_test';
        $lst = new view_list_dsp($t->view_list()->api_json());
        $test_page = $html->text_h2('view list display test');
        $test_page .= 'view list with tooltip: ' . $lst->name_tip() . '<br>';
        $test_page .= 'view list with link: ' . $lst->name_link() . '<br>';

        $test_page .= '<br>' . $html->text_h2('Selector tests');
        $test_page .= $lst->selector($form, 0, url_var::VIEW_ID, msg_id::LABEL_VIEW) . '<br>';

        $t->html_test($test_page, 'view_list', 'view_list', $t);
    }

}