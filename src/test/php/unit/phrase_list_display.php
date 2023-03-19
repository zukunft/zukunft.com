<?php

/*

    test/unit/phrase_list_display.php - TESTing of the PHRASE LIST DISPLAY functions
    ---------------------------------
  

    This file is part of zukunft.com - calc with phrases

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

use api\phrase_api;
use api\word_api;
use html\html_base;
use html\phrase_dsp;
use html\phrase_list_dsp;
use html\word_dsp;

class phrase_list_display_unit_tests
{
    function run(testing $t): void
    {

        $html = new html_base();

        $t->subheader('Phrase list tests');

        // create the phrase list test set
        $lst = new phrase_list_dsp();
        $phr_city = new phrase_api(-1,  phrase_api::TN_ZH_CITY_NAME,
            word_api::TN_ZH, verb::IS_A, word_api::TN_CITY);
        $phr_canton = new phrase_api(-2,  phrase_api::TN_ZH_CANTON_NAME,
            word_api::TN_ZH, verb::IS_A, word_api::TN_CANTON);
        $phr_ch = new phrase_api(1, word_api::TN_CH);
        $lst->add_phrase($phr_city->dsp_obj());
        $lst->add_phrase($phr_canton->dsp_obj());
        $lst->add_phrase($phr_ch->dsp_obj());

        // test the phrase list display functions
        $test_page = $html->text_h2('phrase list display test');
        /*
        $test_page .= 'names with links: ' . $lst->dsp() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        */

        $test_page .= 'selector: ' . '<br>';
        $test_page .= $lst->selector('phrase list test selector', '', 'please select') . '<br>';

        $t->html_test($test_page, 'phrase_list', $t);
    }

}