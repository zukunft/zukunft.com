<?php

/*

    test/unit/word_list_display.php - TESTing of the WORD LIST DISPLAY functions
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

use html\html_base;
use html\word_dsp;
use html\word_list_dsp;

class word_list_display_unit_tests
{
    function run(testing $t): void
    {
        $html = new html_base();

        $t->subheader('Word list tests');

        $lst = new word_list_dsp();
        $wrd = new word_dsp(1, word::TN_READ);
        $wrd_pi = new word_dsp(2, word::TN_CONST_DSP);
        $lst->add($wrd);
        $lst->add($wrd_pi);

        $test_page = $html->text_h2('Word list display test');
        $test_page .= 'names with links: ' . $lst->dsp() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        $t->html_test($test_page, 'word_list', $t);
    }

}