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

namespace unit\html;

use html\html_base;
use html\word\word as word_dsp;
use test\test_cleanup;

class system_views
{
    function run(test_cleanup $t): void
    {

        global $html_system_views;

        $html = new html_base();

        $t->subheader('System view tests');

        // test the type list display functions
        $test_page = $html->text_h2('add word');
        $back = '';
        $wrd = $t->word_dsp();

        // check if the system views have set
        $dsp = $html_system_views->get(word_dsp::FORM_ADD);
        $test_page .= $dsp->show($wrd, $back) . '<br>';

        // TODO review and combine with read db tests
        $t->html_view_test($test_page, word_dsp::FORM_ADD, $t);
    }

}