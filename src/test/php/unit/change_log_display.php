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

namespace test;

use back_trace;
use html\html_base;

class change_log_display_unit_tests
{
    function run(testing $t): void
    {
        global $usr;
        $html = new html_base();

        $t->subheader('Change log display unit tests');

        //$wrd_pi = new word_dsp(2, word_api::TN_CONST);
        $test_page = $html->text_h2('Change log display test');

        // prepare test data
        $back = new back_trace();

        $test_page .= 'simple list of changes of a word<br>';
        $log_lst = $t->dummy_change_log_list_named();
        $log_dsp = $log_lst->dsp_obj();
        $test_page .= $log_dsp->tbl($back);

        $test_page .= 'condensed list of changes of a word<br>';
        $log_lst = $t->dummy_change_log_list_named();
        $log_dsp = $log_lst->dsp_obj();
        $back = new back_trace();
        $test_page .= $log_dsp->tbl($back, true, true);

        $t->html_test($test_page, 'change_log', $t);
    }

}