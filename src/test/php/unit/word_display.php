<?php

/*

    test/unit/word_display.php - TESTing of the WORD DISPLAY functions
    --------------------------
  

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

namespace test;

use api\word_api;
use html\html_base;
use html\word\word as word_dsp;

class word_display_unit_tests
{
    function run(testing $t): void
    {
        global $usr;
        $html = new html_base();

        $t->subheader('Word tests');

        $wrd = new word_dsp(1, word_api::TN_READ);
        $wrd_pi = new word_dsp(2, word_api::TN_CONST);
        $test_page = $html->text_h2('Word display test');
        $test_page .= 'with tooltip: ' . $wrd->dsp() . '<br>';
        $test_page .= 'with link: ' . $wrd->dsp_link() . '<br>';
        $test_page .= 'del button: ' . $wrd->btn_del() . '<br>';
        $test_page .= 'table<br>';
        $test_page .= $html->tbl($wrd->th() . $wrd_pi->tr());
        $test_page .= 'del in columns: ' . $wrd->dsp_del() . '<br>';
        $test_page .= 'unlink in columns: ' . $wrd_pi->dsp_unlink($wrd->id()) . '<br>';
        $test_page .= 'view header<br>';
        $test_page .= $wrd->header() . '<br>';
        $test_page .= 'add mask<br>';
        $test_page .= $wrd->form_add('') . '<br>';
        $test_page .= 'edit mask<br>';
        $test_page .= $wrd->form_edit('', '', '', '') . '<br>';
        $test_page .= 'del mask<br>';
        $test_page .= $wrd->form_del('') . '<br>';
        $t->html_test($test_page, 'word', $t);
    }

}