<?php

/*

    test/php/unit_write/phrase_test.php - write test phrase to the database and check the results
    -----------------------------------
  

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

namespace unit_write;

use api\phrase\phrase as phrase_api;
use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\library;
use cfg\phrase;
use cfg\triple;
use cfg\verb;
use cfg\word;
use test\test_cleanup;
use html\phrase\phrase as phrase_dsp;
use const test\TIMEOUT_LIMIT_PAGE;
use const test\TIMEOUT_LIMIT_PAGE_SEMI;
use const test\TW_VESTAS;

class phrase_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $verbs;
        $lib = new library();

        $t->header('Test the phrase class (src/main/php/model/phrase/phrase.php)');

        // load or create the test objects and remember the vars used for testing
        // load or create a word used to group phrases e.g. company
        $wrd = $t->test_word(word_api::TN_COMPANY);
        $company_id = $wrd->id();
        // load or create a word that can be parts of a group e.g. Zurich
        $wrd = $t->test_word(word_api::TN_ZH);
        $zh_id = $wrd->id();
        $is_id = $verbs->id(verb::IS);
        // load a triple that is parts of a group e.g. Zurich Insurance
        $trp = new triple($usr);
        $trp->load_by_link_id($zh_id, $is_id, $company_id);
        $zh_company_id = $trp->phrase()->id();


        // test the phrase display functions for words
        $phr = new phrase($usr);
        $phr->set_user($usr);
        $phr->load_by_id($company_id);
        $result = $phr->name();
        $target = word_api::TN_COMPANY;
        $t->assert('phrase->load word by id ' . $company_id, $result, $target);

        $result = $lib->trim_html($phr->dsp_tbl());
        $target = $lib->trim_html('<td><a href="/http/view.php?words=' . $company_id . '" title="' .
            word_api::TN_COMPANY . '">' . word_api::TN_COMPANY . '</a></td> ');
        $t->assert('phrase->dsp_tbl word for ' . word_api::TN_COMPANY, $result, $target);

        // test the phrase display functions for triples
        $phr = new phrase($usr);
        $phr->set_id_from_obj($zh_company_id, triple::class);
        $phr->load_by_id($zh_company_id);
        $result = $phr->name();
        $target = triple_api::TN_ZH_COMPANY;
        $t->assert('phrase->load triple by id ' . $zh_company_id, $result, $target);

        $result = $lib->trim_html($phr->dsp_tbl());
        $target = $lib->trim_html(' <td> <a href="/http/view.php?link=' . $trp->id() . '" title="' .
            triple_api::TN_ZH_COMPANY . '">' . triple_api::TN_ZH_COMPANY . '</a></td> ');
        $t->assert('phrase->dsp_tbl triple for ' . $zh_company_id, $result, $target);

        // test the phrase selector
        $form_name = 'test_phrase_selector';
        $pos = 1;
        $back = $company_id;
        $phr = new phrase($usr);
        $phr->load_by_id($zh_company_id);
        $result = $phr->dsp_selector(Null, $form_name, $pos, '', $back);
        $target = triple_api::TN_ZH_COMPANY;
        $t->dsp_contains(', phrase->dsp_selector ' . $result . ' with ' .
            triple_api::TN_ZH_COMPANY . ' selected contains ' .
            triple_api::TN_ZH_COMPANY, $target, $result, TIMEOUT_LIMIT_PAGE);

        // test the phrase selector for the word company
        $wrd = new word($usr);
        $wrd->load_by_name(word_api::TN_COMPANY, word::class);
        $trp_ins = new triple($usr);
        $trp_ins->load_by_name(triple_api::TN_ZH_COMPANY, triple::class);
        $phr = $wrd->phrase();
        $phr_dsp = new phrase_dsp($phr->api_json());
        $result = $phr->dsp_selector($phr_dsp, $form_name, $pos, '', $back);
        $target = $trp_ins->name();
        $t->dsp_contains(', phrase->dsp_selector of type ' . word_api::TN_COMPANY . ' is : ' .
            $result . ' which contains ' . triple_api::TN_ZH_COMPANY,
            $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

        // test getting the parent for phrase Vestas
        $phr = $t->load_phrase(TW_VESTAS);
        $is_phr = $phr->is_mainly();
        if ($is_phr != null) {
            $result = $is_phr->name();
        }
        $target = word_api::TN_COMPANY;
        $t->display('phrase->is_mainly for ' . $phr->name(), $target, $result);

    }

}