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

use cfg\const\paths;

include_once paths::SHARED_TYPES . 'verbs.php';

use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use shared\enum\messages;
use shared\enum\messages as msg_id;
use shared\url_var;
use test\test_cleanup;

class phrase_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html phrase list ';
        $t->header($ts);

        // fill the phrase list based on the api message
        $db_lst = $t->phrase_list();
        $lst = new phrase_list_dsp($db_lst->api_json());
        $t->assert('HTML phrase list names match backend names', $lst->names(), $db_lst->names());

        // create the phrase list test set
        $form = 'phrase_list_ui_test';
        $lst = new phrase_list_dsp();
        $phr_city = $t->zh_city()->phrase();
        $phr_canton = $t->zh_canton()->phrase();
        $phr_ch = $t->word_ch()->phrase();
        $phr_city_dsp = new phrase_dsp($phr_city->api_json());
        $phr_canton_dsp = new phrase_dsp($phr_canton->api_json());
        $phr_ch_dsp = new phrase_dsp($phr_ch->api_json());
        $lst->add_phrase($phr_city_dsp);
        $lst->add_phrase($phr_canton_dsp);
        $lst->add_phrase($phr_ch_dsp);

        // test the phrase list display functions
        $test_page = $html->text_h2('phrase list display test');
        /*
        $test_page .= 'names with links: ' . $lst->display() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        */

        $test_page .= 'selector: ' . '<br>';
        $test_page .= $lst->selector($form, 0, url_var::PHRASE_LONG, msg_id::LABEL_PHRASE) . '<br>';

        $t->html_test($test_page, 'phrase_list', 'phrase_list', $t);

        /*
         * TODO add a phrase selector if the phrase list is short and add an test
        // test the phrase selector
        $form_name = 'test_phrase_selector';
        $pos = 1;
        $back = $company_id;
        $phr = new phrase($usr);
        $phr->load_by_id($zh_company_id);
        $result = $phr->dsp_selector(Null, $form_name, $pos, '', $back);
        $target = triples::COMPANY_ZURICH;
        $t->dsp_contains(', phrase->dsp_selector ' . $result . ' with ' .
            triples::COMPANY_ZURICH . ' selected contains ' .
            triples::COMPANY_ZURICH, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test the phrase selector for the word company
        $wrd = new word($usr);
        $wrd->load_by_name(words::COMPANY, word::class);
        $trp_ins = new triple($usr);
        $trp_ins->load_by_name(triples::COMPANY_ZURICH, triple::class);
        $phr = $wrd->phrase();
        $phr_dsp = new phrase_dsp($phr->api_json());
        $result = $phr->dsp_selector($phr_dsp, $form_name, $pos, '', $back);
        $target = $trp_ins->name();
        $t->dsp_contains(', phrase->dsp_selector of type ' . words::COMPANY . ' is : ' .
            $result . ' which contains ' . triples::COMPANY_ZURICH,
            $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);
        */

    }

}