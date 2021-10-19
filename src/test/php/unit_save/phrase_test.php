<?php

/*

  phrase_test.php - PHRASE class unit TESTs
  ---------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function create_test_phrases()
{
    test_header('Check if all base phrases are correct');

    test_word_link(word::TN_ZH, verb::IS_A, word::TN_CANTON, phrase::TN_ZH_CANTON);
    test_word_link(word::TN_ZH, verb::IS_A, word::TN_CITY_AS_CATEGORY, phrase::TN_ZH_CITY, phrase::TN_ZH_CITY);
    test_word_link(word::TN_ZH, verb::IS_A, word::TN_COMPANY_AS_CATEGORY, phrase::TN_ZH_COMPANY, phrase::TN_ZH_COMPANY);

    test_word_link(TW_ABB, verb::IS_A, TEST_WORD, TP_ABB);
    test_word_link(TW_VESTAS, verb::IS_A, TEST_WORD, TW_VESTAS, TW_VESTAS);
    test_word_link(TW_2014, verb::DBL_FOLLOW, TW_2013, TP_FOLLOW);
    // TODO check direction
    test_word_link(TW_TAX, verb::IS_PART_OF, TW_CF, TP_TAXES);

    test_header('Check if all base phrases are correct');
    test_phrase(phrase::TN_ZH_COMPANY);
}

function create_base_times()
{
    test_header('Check if base time words are correct');

    zu_test_time_setup();
}

function run_phrase_test()
{

    global $usr;

    test_header('Test the phrase class (src/main/php/model/phrase/phrase.php)');

    // load the main test word
    $wrd_company = test_word(word::TN_COMPANY_AS_CATEGORY);

    // prepare the Insurance Zurich
    $wrd_zh = load_word(word::TN_ZH);
    $lnk_company = new word_link;
    $lnk_company->from->id = $wrd_zh->id;
    $lnk_company->verb->id = cl(db_cl::VERB, verb::IS_A);
    $lnk_company->to->id = $wrd_company->id;
    $lnk_company->usr = $usr;
    $lnk_company->load();

    // remember the id for later use
    $zh_company_id = $lnk_company->id;


    // test the phrase display functions (word side)
    $phr = new phrase;
    $phr->id = $wrd_company->id;
    $phr->usr = $usr;
    $phr->load();
    $result = $phr->name;
    $target = word::TN_COMPANY_AS_CATEGORY;
    test_dsp('phrase->load word by id ' . $wrd_company->id, $target, $result);

    $result = str_replace("  ", " ", str_replace("\n", "", $phr->dsp_tbl()));
    $target = ' <td> <a href="/http/view.php?words='. $wrd_company->id . '" title="">' . word::TN_COMPANY_AS_CATEGORY . '</a></td> ';
    $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
    $target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
    $result = trim_all($result);
    $target = trim_all($target);
    // to overwrite any special char
    $diff = str_diff($result, $target);
    if (in_array('view', $diff)) {
        if (in_array(0, $diff['view'])) {
            if ($diff['view'][0] == 0) {
                $target = $result;
            }
        }
    }
    test_dsp('phrase->dsp_tbl word for ' . TEST_WORD, $target, $result);

    // test the phrase display functions (triple side)
    $phr = new phrase;
    $phr->id = $zh_company_id * -1;
    $phr->usr = $usr;
    $phr->load();
    $result = $phr->name;
    $target = phrase::TN_ZH_COMPANY;
    test_dsp('phrase->load triple by id ' . $zh_company_id, $target, $result);

    $result = str_replace("  ", " ", str_replace("\n", "", $phr->dsp_tbl()));
    $target = ' <td> <a href="/http/view.php?link=' . $lnk_company->id . '" title="' . phrase::TN_ZH_COMPANY . '">' . phrase::TN_ZH_COMPANY . '</a></td> ';
    $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
    $target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
    $result = trim_all($result);
    $target = trim_all($target);
    // to overwrite any special char
    $diff = str_diff($result, $target);
    if (in_array('view', $diff)) {
        if (in_array(0, $diff['view'])) {
            if ($diff['view'][0] == 0) {
                $target = $result;
            }
        }
    }
    test_dsp('phrase->dsp_tbl triple for ' . $zh_company_id, $target, $result);

    // test the phrase selector
    $form_name = 'test_phrase_selector';
    $pos = 1;
    $back = $wrd_company->id;
    $phr = new phrase;
    $phr->id = $zh_company_id * -1;
    $phr->usr = $usr;
    $phr->load();
    $result = $phr->dsp_selector(Null, $form_name, $pos, '', $back);
    $target = phrase::TN_ZH_COMPANY;
    test_dsp_contains(', phrase->dsp_selector ' . $result . ' with ' . phrase::TN_ZH_COMPANY . ' selected contains ' . phrase::TN_ZH_COMPANY . '', $target, $result, TIMEOUT_LIMIT_PAGE);

    // test the phrase selector of type company
    $wrd_ABB = new word_dsp;
    $wrd_ABB->name = TW_ABB;
    $wrd_ABB->usr = $usr;
    $wrd_ABB->load();
    $phr = $wrd_ABB->phrase();
    $wrd_company = new word_dsp;
    $wrd_company->name = TEST_WORD;
    $wrd_company->usr = $usr;
    $wrd_company->load();
    $result = $phr->dsp_selector($wrd_company, $form_name, $pos, '', $back);
    $target = TW_ABB;
    test_dsp_contains(', phrase->dsp_selector of type ' . TEST_WORD . ': ' . $result . ' with ABB selected contains ' . phrase::TN_ZH_COMPANY . '', $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // test getting the parent for phrase Vestas
    $phr = load_phrase(TW_VESTAS);
    $is_phr = $phr->is_mainly();
    if ($is_phr != null) {
        $result = $is_phr->name;
    }
    $target = TEST_WORD;
    test_dsp('phrase->is_mainly for ' . $phr->name, $target, $result);

}