<?php

/*

    test/unit/html/phrase_list.php - testing of the html frontend functions for phrase lists
    ------------------------------
  

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

include_once SHARED_TYPES_PATH . 'verbs.php';

use api\word\triple as triple_api;
use api\word\word as word_api;
use html\html_base;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use cfg\verb\verb;
use shared\types\verbs;
use test\test_cleanup;

class phrase_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();

        $t->subheader('HTML phrase list tests');

        // fill the phrase list based on the api message
        $db_lst = $t->phrase_list();
        $lst = new phrase_list_dsp($db_lst->api_json());
        $t->assert('HTML phrase list names match backend names', $lst->names(), $db_lst->names());

        // create the phrase list test set
        $lst = new phrase_list_dsp();
        $phr_city = $this->phrase_api_triple(1,  triples::TN_ZH_CITY_NAME,
            word_api::TN_ZH, verbs::IS, word_api::TN_CITY);
        $phr_canton = $this->phrase_api_triple(2,  triples::TN_ZH_CANTON_NAME,
            word_api::TN_ZH, verbs::IS, word_api::TN_CANTON);
        $phr_ch = $this->phrase_api_word(1, word_api::TN_CH);
        $lst->add_phrase($phr_city);
        $lst->add_phrase($phr_canton);
        $lst->add_phrase($phr_ch);

        // test the phrase list display functions
        $test_page = $html->text_h2('phrase list display test');
        /*
        $test_page .= 'names with links: ' . $lst->display() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        */

        $test_page .= 'selector: ' . '<br>';
        $test_page .= $lst->selector('', 0, 'phrase list test selector', 'please select') . '<br>';

        $t->html_test($test_page, 'phrase_list', 'phrase_list', $t);
    }

    function phrase_api_word(
        int $id,
        string $name
    ): phrase_dsp {
        $wrd = new word_api($id, $name);
        $wrd_dsp = new word_dsp($wrd->get_json());
        return $wrd_dsp->phrase();
    }

    function phrase_api_triple(
        int $id,
        string $name,
        string $from = '',
        string $verb = '',
        string $to = ''
    ): phrase_dsp {
        $trp = new triple_api($id, $name, $from, $verb, $to);
        $trp_dsp = new triple_dsp($trp->get_json());
        return $trp_dsp->phrase();
    }

}