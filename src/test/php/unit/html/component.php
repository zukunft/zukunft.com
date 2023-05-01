<?php

/*

    test/unit/view_component_display.php - TESTing of the COMPONENT DISPLAY functions
    ------------------------------------
  

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

use api\view_cmp_api;
use html\html_base;
use html\view_cmp_dsp_old;
use test\testing;

class view_component_display_unit_tests
{
    function run(testing $t): void
    {
        global $usr;
        $html = new html_base();

        $t->subheader('Component tests');

        //$wrd_pi = new word_dsp(2, word_api::TN_CONST);
        $test_page = $html->text_h2('Component display test');
        /*
        $test_page .= 'with tooltip: ' . $wrd->display() . '<br>';
        $test_page .= 'with link: ' . $wrd->display_linked() . '<br>';
        $test_page .= 'del button: ' . $wrd->btn_del() . '<br>';
        $test_page .= 'table<br>';
        $test_page .= $html->tbl($wrd->th() . $wrd_pi->tr());
        $test_page .= 'del in columns: ' . $wrd->dsp_del() . '<br>';
        $test_page .= 'unlink in columns: ' . $wrd_pi->dsp_unlink($wrd->id()) . '<br>';
        $test_page .= 'view header<br>';
        $test_page .= $wrd->header() . '<br>';
        */
        $cmp = new view_cmp_dsp_old(0);
        $test_page .= 'add mask<br>';
        $test_page .= $cmp->form_edit('', '', '', '', '') . '<br>';
        $cmp = new view_cmp_dsp_old(1, view_cmp_api::TN_READ);
        $cmp->description = view_cmp_api::TD_READ;
        $test_page .= 'edit mask<br>';
        $test_page .= $cmp->form_edit('', '', '', '', '') . '<br>';
        $t->html_test($test_page, 'view_cmp', $t);
    }

}