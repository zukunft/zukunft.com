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

function create_base_phrases()
{
    echo "<h2>Check if all base phrases are correct</h2><br>";
    test_word_link(TW_ZH, DBL_LINK_TYPE_IS, TW_CANTON, TP_ZH_CANTON);
    test_word_link(TW_ZH, DBL_LINK_TYPE_IS, TW_CITY, TP_ZH_CITY);
    test_word_link(TW_ZH, DBL_LINK_TYPE_IS, TEST_WORD, TP_ZH_INS, TP_ZH_INS);
    test_word_link(TW_ABB, DBL_LINK_TYPE_IS, TEST_WORD, TP_ABB);
    test_word_link(TW_2014, DBL_LINK_TYPE_FOLLOW, TW_2013, TP_FOLLOW);
    // TODO check direction
    test_word_link(TW_TAX, DBL_LINK_TYPE_CONTAIN, TW_CF,TP_TAXES);
    echo "<br><br>";

    echo "<h2>Check if all base phrases are correct</h2><br>";
    test_phrase(TP_ZH_INS);
    echo "<br><br>";
}

function create_base_times()
{
    echo "<h2>Check if all base word links are correct</h2><br>";
    zu_test_time_setup();
    echo "<br><br>";
}

function run_phrase_test()
{

    global $usr;
    global $exe_start_time;

    test_header('Test the phrase class (src/main/php/model/phrase/phrase.php)');

    // load the main test word
    $wrd_company = test_word(TEST_WORD);

    // prepare the Insurance Zurich
    $wrd_zh = load_word(TW_ZH);
    $lnk_company = new word_link;
    $lnk_company->from_id = $wrd_zh->id;
    $lnk_company->verb_id = cl(DBL_LINK_TYPE_IS);
    $lnk_company->to_id = $wrd_company->id;
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
    $target = TEST_WORD;
    $exe_start_time = test_show_result('phrase->load word by id ' . $wrd_company->id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $result = str_replace("  ", " ", str_replace("\n", "", $phr->dsp_tbl()));
    $target = ' <td> <a href="/http/view.php?words=1" title="">' . TEST_WORD . '</a> </td> ';
    $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
    $target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
    // to overwrite any special char
    $diff = str_diff($result, $target);
    if ($diff['view'][0] == 0) {
        $target = $result;
    }
    $exe_start_time = test_show_result('phrase->dsp_tbl word for ' . TEST_WORD, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test the phrase display functions (triple side)
    $phr = new phrase;
    $phr->id = $zh_company_id * -1;
    $phr->usr = $usr;
    $phr->load();
    $result = $phr->name;
    $target = TP_ZH_INS;
    $exe_start_time = test_show_result('phrase->load triple by id ' . $zh_company_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $result = str_replace("  ", " ", str_replace("\n", "", $phr->dsp_tbl()));
    $target = ' <td> <a href="/http/view.php?link=313" title="' . TP_ZH_INS . '">' . TP_ZH_INS . '</a> </td> ';
    $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
    $target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
    // to overwrite any special char
    $diff = str_diff($result, $target);
    if ($diff['view'][0] == 0) {
        $target = $result;
    }
    $exe_start_time = test_show_result('phrase->dsp_tbl triple for ' . $zh_company_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test the phrase selector
    $form_name = 'test_phrase_selector';
    $pos = 1;
    $back = $wrd_company->id;
    $phr = new phrase;
    $phr->id = $zh_company_id * -1;
    $phr->usr = $usr;
    $phr->load();
    $result = $phr->dsp_selector(Null, $form_name, $pos, '', $back);
    $target = TP_ZH_INS;
    $exe_start_time = test_show_contains(', phrase->dsp_selector ' . $result . ' with ' . TP_ZH_INS . ' selected contains ' . TP_ZH_INS . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

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
    $target = TP_ZH_INS;
    $exe_start_time = test_show_contains(', phrase->dsp_selector of type ' . TEST_WORD . ': ' . $result . ' with ABB selected contains ' . TP_ZH_INS . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

    // test getting the parent for phrase Vestas
    $phr = load_phrase(TW_VESTAS);
    $is_phr = $phr->is_mainly();
    $result = $is_phr->name;
    $target = TEST_WORD;
    $exe_start_time = test_show_result('phrase->is_mainly for ' . $phr->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}