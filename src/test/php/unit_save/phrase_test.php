<?php

/*

  phrase_test.php - PHRASE class unit TESTs
  ---------------
  

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

use api\phrase_api;
use api\triple_api;
use api\word_api;
use model\library;
use model\phrase;
use model\triple;
use model\verb;
use model\word;

function create_test_triples(test_cleanup $t): void
{
    $t->header('Check if all base phrases are correct');

    // activate the excluded objects to check the setup
    $trp = new triple($t->usr2);
    $trp->load_by_name(triple_api::TN_EXCLUDED);
    if ($trp->id() != 0) {
        $trp->set_excluded(false);
        $trp->save();
    }

    // check if the standard samples for triples still exist and if not, create the samples
    $t->test_triple(word_api::TN_ZH, verb::IS_A, word_api::TN_CANTON, triple_api::TN_ZH_CANTON, triple_api::TN_ZH_CANTON);
    $t->test_triple(word_api::TN_ZH, verb::IS_A, word_api::TN_CITY, triple_api::TN_ZH_CITY, triple_api::TN_ZH_CITY);
    $t->test_triple(word_api::TN_ZH, verb::IS_A, word_api::TN_COMPANY, triple_api::TN_ZH_COMPANY, triple_api::TN_ZH_COMPANY);
    $t->test_triple(triple_api::TN_ZH_CANTON, verb::IS_PART_OF, word_api::TN_CH);
    $t->test_triple(triple_api::TN_ZH_CITY, verb::IS_PART_OF, triple_api::TN_ZH_CANTON);
    $t->test_triple(triple_api::TN_ZH_COMPANY, verb::IS_PART_OF, triple_api::TN_ZH_CITY, triple_api::TN_EXCLUDED, triple_api::TN_EXCLUDED);

    $t->test_triple(TW_ABB, verb::IS_A, word_api::TN_COMPANY, TP_ABB);
    // TODO check why it is possible to create a triple with the same name as a word
    //$t->test_triple(TW_VESTAS, verb::IS_A, TEST_WORD, TW_VESTAS, TW_VESTAS);
    $t->test_triple(TW_VESTAS, verb::IS_A, word_api::TN_COMPANY, triple_api::TN_VESTAS_COMPANY, triple_api::TN_VESTAS_COMPANY);
    $t->test_triple(TW_2014, verb::FOLLOW, TW_2013, TP_FOLLOW);
    // TODO check direction
    $t->test_triple(TW_TAX, verb::IS_PART_OF, TW_CF, TP_TAXES);

    $t->header('Check if all base phrases are correct');
    $t->test_phrase(triple_api::TN_ZH_COMPANY);

    // exclude some to test the handling of exclude objects
    $trp = new triple($t->usr2);
    $trp->load_by_name(triple_api::TN_EXCLUDED);
    $trp->set_excluded(true);
    $trp->save();
}

function create_base_times(test_cleanup $t): void
{
    $t->header('Check if base time words are correct');

    zu_test_time_setup($t);
}

function run_phrase_test(test_cleanup $t): void
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
    $is_id = $verbs->id(verb::IS_A);
    // load a triple that is parts of a group e.g. Zurich Insurance
    $trp = new triple($usr);
    $trp->load_by_link($zh_id, $is_id, $company_id);
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
    $wrd_ins = new word($usr);
    $wrd_ins->load_by_name(triple_api::TN_ZH_COMPANY, word::class);
    $phr = $wrd_ins->phrase();
    $result = $phr->dsp_selector($wrd, $form_name, $pos, '', $back);
    $target = $wrd_ins->name();
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