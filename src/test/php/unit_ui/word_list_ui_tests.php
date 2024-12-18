<?php

/*

    test/unit/html/word_list.php - testing of the word list html frontend functions
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

namespace unit_ui;

include_once WEB_WORD_PATH . 'word_list.php';

use html\html_base;
use html\word\word_list as word_list_dsp;
use test\test_cleanup;

class word_list_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('Word list tests');

        // create the word list test set
        $lst = new word_list_dsp($t->word_list_short()->api_json());
        $lst_long = new word_list_dsp($t->word_list_all_types()->api_json());

        // test the word list display functions
        $test_page = $html->text_h1('Word list display test');
        $test_page .= 'names with links:<br>' . $lst->display() . '<br><br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        $test_page .= 'all word types: ' . '<br>' . $lst_long->display() . '<br><br>';
        $test_page .= 'ex measure and time: ' . '<br>' . $lst_long->ex_measure_and_time_lst()->display() . '<br><br>';
        $test_page .= 'measure and scaling: ' . '<br>' . $lst_long->measure_scale_lst()->display() . '<br><br>';

        $test_page .= '<br>' . $html->text_h2('Selector tests');
        $test_page .= $lst_long->selector('test_selector', '', 'No word selected') . '<br>';
        $test_page .= $lst_long->selector('2_selected', '', 'Pi selected', '', 3) . '<br>';

        $t->html_test($test_page, '', 'word_list', $t);
    }

}