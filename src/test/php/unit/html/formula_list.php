<?php

/*

    test/unit/html/formula_list.php - testing of the html frontend functions for formula lists
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

namespace test\html;

use html\html_base;
use html\formula\formula_list as formula_list_dsp;
use test\testing;

class formula_list
{
    function run(testing $t): void
    {

        $html = new html_base();

        $t->subheader('formula list tests');

        // test the formula list display functions
        $lst = new formula_list_dsp($t->dummy_formula_list()->api_json());
        $test_page = $html->text_h2('formula list display test');
        $test_page .= 'formula list with tooltip: ' . $lst->display() . '<br>';
        $test_page .= 'formula list with link: ' . $lst->display_linked() . '<br>';
        $t->html_test($test_page, 'formula_list', $t);
    }

}