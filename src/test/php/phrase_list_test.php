<?php

/*

  phrase_list_test.php - PHRASE LIST function  unit TESTs
  --------------------
  

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

function run_phrase_list_test()
{

    global $usr;

    test_header('Test the phrase list class (src/main/php/model/phrase/phrase_list.php)');

    // load the main test word
    $wrd_company = test_word(TEST_WORD);

    // prepare test by loading Insurance Zurich
    $wrd_zh = load_word(TW_ZH);
    $lnk_company = new word_link;
    $lnk_company->from_id = $wrd_zh->id;
    $lnk_company->verb_id = cl(DBL_LINK_TYPE_IS);
    $lnk_company->to_id = $wrd_company->id;
    $lnk_company->usr = $usr;
    $lnk_company->load();
    $triple_sample_id = $lnk_company->id;

    // test the phrase loading via id
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(TW_ABB);
    $wrd_lst->add_name(TW_VESTAS);
    $wrd_lst->load();
    $id_lst = $wrd_lst->ids;
    $id_lst[] = $triple_sample_id * -1;
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->ids = $id_lst;
    $phr_lst->load();
    $target = '"' . TW_ABB . '","' . TW_VESTAS . '","' . TP_ZH_INS . '"';
    $result = $phr_lst->name();
    test_dsp('phrase->load via id', $target, $result);

    // ... the complete word list, which means split the triples into single words
    $wrd_lst_all = $phr_lst->wrd_lst_all();
    $target = '"' . TW_ABB . '","' . TW_VESTAS . '","' . TW_ZH . '","' . TEST_WORD . '"';
    $result = $wrd_lst_all->name();
    test_dsp('phrase->wrd_lst_all of list above', $target, $result);


    // test getting the parent for phrase list with ABB
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(TW_ABB);
    $wrd_lst->load();
    $phr_lst = $wrd_lst->phrase_lst();
    $lst_parents = $phr_lst->foaf_parents(cl(DBL_LINK_TYPE_IS));
    $result = dsp_array($lst_parents->names());
    $target = TEST_WORD; // order adjusted based on the number of usage
    test_dsp('phrase_list->foaf_parents for ' . $phr_lst->name() . ' up', $target, $result);

    // ... same using is
    $phr_lst = $wrd_lst->phrase_lst();
    $lst_is = $phr_lst->is();
    $result = dsp_array($lst_is->names());
    $target = TEST_WORD; // order adjusted based on the number of usage
    test_dsp('phrase_list->is for ' . $phr_lst->name() . ' up', $target, $result);

    // ... same with Coca Cola
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(TW_VESTAS);
    $wrd_lst->load();
    $phr_lst = $wrd_lst->phrase_lst();
    $lst_is = $phr_lst->is();
    $result = dsp_array($lst_is->names());
    $target = TEST_WORD; // order adjusted based on the number of usage
    test_dsp('phrase_list->is for ' . $phr_lst->name() . ' up', $target, $result);

    // test the excluding function
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(TW_ABB);
    $phr_lst->add_name(TW_SALES);
    $phr_lst->add_name(TW_CHF);
    $phr_lst->add_name(TW_MIO);
    $phr_lst->add_name(TW_2017);
    $phr_lst->load();
    $phr_lst_ex = clone $phr_lst;
    $phr_lst_ex->ex_time();
    $target = '"' . TW_ABB . '","' . TW_SALES . '","' . TW_CHF . '","' . TW_MIO . '"';
    $result = $phr_lst_ex->name();
    test_dsp('phrase_list->ex_time of ' . $phr_lst->name(), $target, $result);

    $phr_lst_ex = clone $phr_lst;
    $phr_lst_ex->ex_measure();
    $target = '"' . TW_ABB . '","' . TW_SALES . '","' . TW_MIO . '","' . TW_2017 . '"';
    $result = $phr_lst_ex->name();
    test_dsp('phrase_list->ex_measure of ' . $phr_lst->name(), $target, $result);

    $phr_lst_ex = clone $phr_lst;
    $phr_lst_ex->ex_scaling();
    $target = '"' . TW_ABB . '","' . TW_SALES . '","' . TW_CHF . '","' . TW_2017 . '"';
    $result = $phr_lst_ex->name();
    test_dsp('phrase_list->ex_scaling of ' . $phr_lst->name(), $target, $result);

}