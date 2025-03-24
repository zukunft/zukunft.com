<?php

/*

    test/unit/html/triple.php - testing of the html frontend functions for triples
    -------------------------
  

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

include_once SHARED_CONST_PATH . 'words.php';

use html\html_base;
use html\phrase\phrase_list;
use html\word\triple;
use shared\const\views;
use test\test_cleanup;

class triple_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('html triple unit tests');

        $trp = new triple($t->triple()->api_json());
        $phr_lst = new phrase_list($t->phrase_list()->api_json());
        $test_page = $html->text_h1('Triple display test');
        $test_page .= $html->text_h2('names');
        $test_page .= 'with tooltip: ' . $trp->name_tip() . '<br>';
        $test_page .= 'with link: ' . $trp->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $trp->btn_add() . '<br>';
        $test_page .= 'edit button: ' . $trp->btn_edit() . '<br>';
        $test_page .= 'del button: ' . $trp->btn_del() . '<br>';
        $test_page .= $html->text_h2('select');
        $from_rows = $trp->phrase_type_selector(views::TRIPLE_EDIT) . '<br>';
        $from_rows .= $trp->verb_selector(views::TRIPLE_EDIT) . '<br>';
        $from_rows .= $trp->phrase_selector_from(views::TRIPLE_EDIT, $phr_lst) . '<br>';
        $from_rows .= $trp->phrase_selector_to(views::TRIPLE_EDIT, $phr_lst) . '<br>';
        $test_page .= $html->form(views::TRIPLE_EDIT, $from_rows);
        $test_page .= $html->text_h2('table');
        $test_page .= $html->tbl($html->tr($trp->tr()));
        $t->html_test($test_page, 'triple', 'triple', $t);
    }

}