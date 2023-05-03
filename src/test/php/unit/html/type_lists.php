<?php

/*

    test/unit/html/type_list.php - testing of the type list html user interface functions
    ----------------------------
  

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

use cfg\formula_type_list;
use html\html_base;
use test\testing;

class type_list
{
    function run(testing $t): void
    {

        $html = new html_base();

        $t->subheader('Type list tests');

        // create the formula type list test set
        $lst = new formula_type_list();
        $lst->load_dummy();

        // test the type list display functions
        $test_page = $html->text_h2('type list display test');

        $test_page .= 'selector: ' . '<br>';
        $test_page .= $lst->dsp_obj()->selector() . '<br>';

        $t->html_test($test_page, 'types', $t);
    }

}