<?php

/*

    test/unit/html/result_list.php - testing of the html frontend functions for result lists
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

include_once SHARED_TYPES_PATH . 'api_type.php';

use html\html_base;
use html\result\result_list as result_list_dsp;
use shared\types\api_type;
use test\test_cleanup;

class result_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();

        $t->subheader('result list tests');

        // test the result list display functions
        $lst = new result_list_dsp($t->result_list()->api_json([api_type::TEST_MODE]));
        $test_page = $html->text_h2('result list display test');
        $test_page .= 'result list with tooltip: ' . $lst->display() . '<br>';
        $test_page .= 'result list with link: ' . $lst->display_linked() . '<br>';
        $t->html_test($test_page, 'result_list', 'result_list', $t);
    }

}