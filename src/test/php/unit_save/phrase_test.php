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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function create_test_phrases(testing $t)
{
    $t->header('Check if all base phrases are correct');

    $t->test_word_link(word::TN_ZH, verb::IS_A, word::TN_CANTON, phrase::TN_ZH_CANTON);
    $t->test_word_link(word::TN_ZH, verb::IS_A, word::TN_CITY, phrase::TN_ZH_CITY, phrase::TN_ZH_CITY);
    $t->test_word_link(word::TN_ZH, verb::IS_A, word::TN_COMPANY, phrase::TN_ZH_COMPANY, phrase::TN_ZH_COMPANY);

    $t->test_word_link(TW_ABB, verb::IS_A, TEST_WORD, TP_ABB);
    $t->test_word_link(TW_VESTAS, verb::IS_A, TEST_WORD, TW_VESTAS, TW_VESTAS);
    $t->test_word_link(TW_2014, verb::DBL_FOLLOW, TW_2013, TP_FOLLOW);
    // TODO check direction
    $t->test_word_link(TW_TAX, verb::IS_PART_OF, TW_CF, TP_TAXES);

    $t->header('Check if all base phrases are correct');
    $t->test_phrase(phrase::TN_ZH_COMPANY);
}

function create_base_times(testing $t)
{
    $t->header('Check if base time words are correct');

    zu_test_time_setup($t);
}

function run_phrase_test(testing $t)
{

    global $usr;

    $t->header('Test the phrase class (src/main/php/model/phrase/phrase.php)');

    // load the main test word
    $wrd_company = $t->test_word(word::TN_COMPANY);

    // prepare the Insurance Zurich
    $wrd_zh = $t->load_word(word::TN_ZH);
    $lnk_company = new word_link($usr);
    $lnk_company->from->id = $wrd_zh->id;
    $lnk_company->verb->id = cl(db_cl::VERB, verb::IS_A);
    $lnk_company->to->id = $wrd_company->id;
    $lnk_company->load();

    // remember the id for later use
    $zh_company_id = $lnk_company->id;


    // test the phrase display functions (word side)
    $phr = new phrase($usr);
    $phr->id = $wrd_company->id;
    $phr->usr = $usr;
    $phr->load();
    $result = $phr->name;
    $target = word::TN_COMPANY;
    $t->dsp('phrase->load word by id ' . $wrd_company->id, $target, $result);

    $result = str_replace("  ", " ", str_replace("\n", "", $phr->dsp_tbl()));
    $target = ' <td> <a href="/http/view.php?words=' . $wrd_company->id . '" title="">' . word::TN_COMPANY . '</a></td> ';
    $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
    $target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
    $result = trim_all($result);
    $target = trim_all($target);
    // to overwrite any special char
    $diff = str_diff($result, $target);
    if ($diff != '') {
        $target = $result;
        log_err('Unexpected diff ' . $diff);
    }
    $t->dsp('phrase->dsp_tbl word for ' . TEST_WORD, $target, $result);

    // test the phrase display functions (triple side)
    $phr = new phrase($usr);
    $phr->id = $zh_company_id * -1;
    $phr->load();
    $result = $phr->name;
    $target = phrase::TN_ZH_COMPANY;
    $t->dsp('phrase->load triple by id ' . $zh_company_id, $target, $result);

    $result = str_replace("  ", " ", str_replace("\n", "", $phr->dsp_tbl()));
    $target = ' <td> <a href="/http/view.php?link=' . $lnk_company->id . '" title="' . phrase::TN_ZH_COMPANY . '">' . phrase::TN_ZH_COMPANY . '</a></td> ';
    $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
    $target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
    $result = trim_all($result);
    $target = trim_all($target);
    // to overwrite any special char
    $diff = str_diff($result, $target);
    if ($diff != '') {
        $target = $result;
        log_err('Unexpected diff ' . $diff);
    }
    $t->dsp('phrase->dsp_tbl triple for ' . $zh_company_id, $target, $result);

    // test the phrase selector
    $form_name = 'test_phrase_selector';
    $pos = 1;
    $back = $wrd_company->id;
    $phr = new phrase($usr);
    $phr->id = $zh_company_id * -1;
    $phr->load();
    $result = $phr->dsp_selector(Null, $form_name, $pos, '', $back);
    $target = phrase::TN_ZH_COMPANY;
    $t->dsp_contains(', phrase->dsp_selector ' . $result . ' with ' . phrase::TN_ZH_COMPANY . ' selected contains ' . phrase::TN_ZH_COMPANY . '', $target, $result, TIMEOUT_LIMIT_PAGE);

    // test the phrase selector of type company
    $wrd_ABB = new word($usr);
    $wrd_ABB->name = TW_ABB;
    $wrd_ABB->load();
    $phr = $wrd_ABB->phrase();
    $wrd_company = new word($usr);
    $wrd_company->name = TEST_WORD;
    $wrd_company->load();
    $result = $phr->dsp_selector($wrd_company, $form_name, $pos, '', $back);
    $target = TW_ABB;
    $t->dsp_contains(', phrase->dsp_selector of type ' . TEST_WORD . ': ' . $result . ' with ABB selected contains ' . phrase::TN_ZH_COMPANY . '', $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // test getting the parent for phrase Vestas
    $phr = $t->load_phrase(TW_VESTAS);
    $is_phr = $phr->is_mainly();
    if ($is_phr != null) {
        $result = $is_phr->name;
    }
    $target = TEST_WORD;
    $t->dsp('phrase->is_mainly for ' . $phr->name, $target, $result);

}