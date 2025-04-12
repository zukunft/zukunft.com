<?php

/*

    test/unit/html/triple_list.php - testing of the html frontend functions for triples
    ------------------------------
  

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

include_once SHARED_TYPES_PATH . 'verbs.php';

use html\html_base;
use html\word\triple;
use html\word\triple_list as triple_list_dsp;
use test\test_cleanup;

class triple_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html triple list ';
        $t->header($ts);

        // fill the triple list based on the api message
        $db_lst = $t->triple_list();
        $lst = new triple_list_dsp($db_lst->api_json());
        $t->assert('HTML triple list names match backend names', $lst->names(), $db_lst->names());

        // create the triple list test set
        $lst = new triple_list_dsp();
        $phr_city = $t->zh_city();
        $phr_canton = $t->zh_canton();
        $phr_city_dsp = new triple($phr_city->api_json());
        $phr_canton_dsp = new triple($phr_canton->api_json());
        $lst->add($phr_city_dsp);
        $lst->add($phr_canton_dsp);

        // test the triple list display functions
        $test_page = $html->text_h2('triple list display test');
        /*
        $test_page .= 'names with links: ' . $lst->display() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        */

        $test_page .= 'selector: ' . '<br>';
        $test_page .= $lst->selector('', 0,
                'triple list test selector', 'please select') . '<br>';

        $t->html_test($test_page, 'triple_list', 'triple_list', $t);
    }

}