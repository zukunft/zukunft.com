<?php

/*

  phrase_list_test.php - PHRASE LIST function  unit TESTs
  --------------------
  

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

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function run_phrase_list_test(testing $t)
{

    global $usr;

    $t->header('Test the phrase list class (src/main/php/model/phrase/phrase_list.php)');

    // load the main test word
    $wrd_company = $t->test_word(word::TN_READ);

    // prepare test by loading Insurance Zurich
    $wrd_zh = $t->load_word(word::TN_ZH);
    $lnk_company = new word_link;
    $lnk_company->from->id = $wrd_zh->id;
    $lnk_company->verb->id = cl(db_cl::VERB, verb::IS_A);
    $lnk_company->to->id = $wrd_company->id;
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
    $phr_lst = new phrase_list($usr);
    $phr_lst->ids = $id_lst;
    $phr_lst->load();
    $target = '"' . TW_ABB . '","' . TW_VESTAS . '","' . phrase::TN_ZH_COMPANY . '"';
    $result = $phr_lst->name();
    $t->dsp('phrase->load via id', $target, $result);

    // ... the complete word list, which means split the triples into single words
    $wrd_lst_all = $phr_lst->wrd_lst_all();
    $target = '"' . TW_ABB . '","' . TW_VESTAS . '","' . word::TN_ZH . '","' . TEST_WORD . '"';
    $result = $wrd_lst_all->name();
    $t->dsp('phrase->wrd_lst_all of list above', $target, $result);


    // test getting the parent for phrase list with ABB
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(TW_ABB);
    $wrd_lst->load();
    $phr_lst = $wrd_lst->phrase_lst();
    $lst_parents = $phr_lst->foaf_parents(cl(db_cl::VERB, verb::IS_A));
    $result = dsp_array($lst_parents->names());
    $target = TEST_WORD; // order adjusted based on the number of usage
    $t->dsp('phrase_list->foaf_parents for ' . $phr_lst->name() . ' up', $target, $result);

    // ... same using is
    $phr_lst = $wrd_lst->phrase_lst();
    $lst_is = $phr_lst->is();
    $result = dsp_array($lst_is->names());
    $target = TEST_WORD; // order adjusted based on the number of usage
    $t->dsp('phrase_list->is for ' . $phr_lst->name() . ' up', $target, $result);

    // ... same with Coca Cola
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(TW_VESTAS);
    $wrd_lst->load();
    $phr_lst = $wrd_lst->phrase_lst();
    $lst_is = $phr_lst->is();
    $result = dsp_array($lst_is->names());
    $target = TEST_WORD; // order adjusted based on the number of usage
    $t->dsp('phrase_list->is for ' . $phr_lst->name() . ' up', $target, $result);

    // test the excluding function
    $phr_lst = new phrase_list($usr);
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
    $t->dsp('phrase_list->ex_time of ' . $phr_lst->name(), $target, $result);

    $phr_lst_ex = clone $phr_lst;
    $phr_lst_ex->ex_measure();
    $target = '"' . TW_ABB . '","' . TW_SALES . '","' . TW_MIO . '","' . TW_2017 . '"';
    $result = $phr_lst_ex->name();
    $t->dsp('phrase_list->ex_measure of ' . $phr_lst->name(), $target, $result);

    $phr_lst_ex = clone $phr_lst;
    $phr_lst_ex->ex_scaling();
    $target = '"' . TW_ABB . '","' . TW_SALES . '","' . TW_CHF . '","' . TW_2017 . '"';
    $result = $phr_lst_ex->name();
    $t->dsp('phrase_list->ex_scaling of ' . $phr_lst->name(), $target, $result);

}