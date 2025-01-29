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

include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_CONST_PATH . 'triples.php';

use cfg\phrase\phrase;
use cfg\word\triple;
use cfg\word\word;
use html\phrase\phrase as phrase_dsp;
use shared\api;
use shared\library;
use shared\const\triples;
use shared\const\views;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class phrase_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $vrb_cac;
        $lib = new library();

        $t->header('Test the phrase class (src/main/php/model/phrase/phrase.php)');

        // load or create the test objects and remember the vars used for testing
        // load or create a word used to group phrases e.g. company
        $wrd = $t->test_word(words::TN_COMPANY);
        $company_id = $wrd->id();
        // load or create a word that can be parts of a group e.g. Zurich
        $wrd = $t->test_word(words::ZH);
        $zh_id = $wrd->id();
        $is_id = $vrb_cac->id(verbs::IS);
        // load a triple that is parts of a group e.g. Zurich Insurance
        $trp = new triple($usr);
        $trp->load_by_link_id($zh_id, $is_id, $company_id);
        $zh_company_id = $trp->phrase()->id();


        // test the phrase display functions for words
        $phr = new phrase($usr);
        $phr->set_user($usr);
        $phr->load_by_id($company_id);
        $result = $phr->name();
        $target = words::TN_COMPANY;
        $t->assert('phrase->load word by id ' . $company_id, $result, $target);

        $result = $lib->trim_html($phr->dsp_tbl());
        $url = '<td><a href="/http/view.php?' . api::URL_VAR_MASK . '=' . views::WORD_ID . '&' . api::URL_VAR_ID . '=';
        $target = $lib->trim_html($url . $company_id . '" title="' .
            words::TN_COMPANY . '">' . words::TN_COMPANY . '</a></td> ');
        $t->assert('phrase->dsp_tbl word for ' . words::TN_COMPANY, $result, $target);

        // test the phrase display functions for triples
        $phr = new phrase($usr);
        $phr->set_id_from_obj($zh_company_id, triple::class);
        $phr->load_by_id($zh_company_id);
        $result = $phr->name();
        $target = triples::COMPANY_ZURICH;
        $t->assert('phrase->load triple by id ' . $zh_company_id, $result, $target);

        $result = $lib->trim_html($phr->dsp_tbl());
        $target = $lib->trim_html(' <td> <a href="/http/view.php?link=' . $trp->id() . '" title="' .
            triples::COMPANY_ZURICH . '">' . triples::COMPANY_ZURICH . '</a></td> ');
        $t->assert('phrase->dsp_tbl triple for ' . $zh_company_id, $result, $target);

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
        $wrd->load_by_name(words::TN_COMPANY, word::class);
        $trp_ins = new triple($usr);
        $trp_ins->load_by_name(triples::COMPANY_ZURICH, triple::class);
        $phr = $wrd->phrase();
        $phr_dsp = new phrase_dsp($phr->api_json());
        $result = $phr->dsp_selector($phr_dsp, $form_name, $pos, '', $back);
        $target = $trp_ins->name();
        $t->dsp_contains(', phrase->dsp_selector of type ' . words::TN_COMPANY . ' is : ' .
            $result . ' which contains ' . triples::COMPANY_ZURICH,
            $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // test getting the parent for phrase Vestas
        $phr = $t->load_phrase(words::TN_VESTAS);
        $is_phr = $phr->is_mainly();
        if ($is_phr != null) {
            $result = $is_phr->name();
        } else {
            // TODO activate
            //log_err('Vestas type test failed');
            log_warning('Vestas type test failed');
        }
        $target = words::TN_COMPANY;
        $t->display('phrase->is_mainly for ' . $phr->name(), $target, $result);

    }

}