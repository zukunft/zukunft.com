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

namespace test\html;

include_once WEB_WORD_PATH . 'word_list.php';

use api\word_api;
use cfg\phrase_type;
use html\html_base;
use html\word\word as word_dsp;
use html\word\word_list as word_list_dsp;
use test\testing;

class word_list
{
    function run(testing $t): void
    {
        $html = new html_base();

        $t->subheader('Word list tests');

        // create the word list test set
        $lst = new word_list_dsp();
        $lst_long = new word_list_dsp();
        $wrd = new word_dsp('{"id":1,"name":"' . word_api::TN_READ . '"}');
        $wrd_pi = new word_dsp('{"id":2,"name":"' . word_api::TN_CONST . '"}');
        $wrd_time = new word_dsp('{"id":3,"name":"' . word_api::TN_2019 . '"}');
        $wrd_one = new word_dsp('{"id":4,"name":"' . word_api::TN_ONE . '"}');
        $wrd_mio = new word_dsp('{"id":5,"name":"' . word_api::TN_MIO_SHORT . '"}');
        $wrd_pct = new word_dsp('{"id":6,"name":"' . word_api::TN_PCT . '"}');
        $wrd_time->set_type(phrase_type::TIME);
        $wrd_one->set_type(phrase_type::SCALING_HIDDEN);
        $wrd_mio->set_type(phrase_type::SCALING);
        $wrd_pct->set_type(phrase_type::PERCENT);
        $lst->add($wrd);
        $lst->add($wrd_pi);
        $lst_long->add($wrd);
        $lst_long->add($wrd_pi);
        $lst_long->add($wrd_time);
        $lst_long->add($wrd_one);
        $lst_long->add($wrd_mio);
        $lst_long->add($wrd_pct);

        // test the word list display functions
        $test_page = $html->text_h2('Word list display test');
        $test_page .= 'names with links: ' . $lst->display() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        $test_page .= 'all word types: ' . '<br>' . $lst_long->display() . '<br>';
        $test_page .= 'ex measure and time: ' . '<br>' . $lst_long->ex_measure_and_time_lst()->display() . '<br>';
        $test_page .= 'measure and scaling: ' . '<br>' . $lst_long->measure_scale_lst()->display() . '<br>';

        $test_page .= 'selector: ' . '<br>';
        $test_page .= $lst_long->selector() . '<br>';

        $t->html_test($test_page, 'word_list', $t);
    }

}