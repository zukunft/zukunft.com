<?php

/*

    test/unit/html/component.php - testing of the component display functions
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

use html\component\component_exe as component_dsp;
use html\html_base;
use shared\const\components;
use test\test_cleanup;

class component_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;
        $html = new html_base();

        $t->subheader('Component tests');

        //$wrd_pi = new word_dsp(2, words::TN_CONST);
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
        $cmp = new component_dsp();
        $cmp->set_id(0);
        $test_page .= 'add mask<br>';
        $test_page .= $cmp->form_edit('', '', '', '', '') . '<br>';
        $cmp = new component_dsp();
        $cmp->set_id(1);
        $cmp->set_name(components::WORD_NAME);
        $cmp->description = components::WORD_COM;
        $test_page .= 'edit mask<br>';
        $test_page .= $cmp->form_edit('', '', '', '', '') . '<br>';
        $t->html_test($test_page, 'component', 'component', $t);
    }

}