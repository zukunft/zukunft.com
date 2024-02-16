<?php

/*

    test/php/unit_write/phrase_list.php - write test PHRASE LISTS to the database and check the results
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

namespace test\write;

use api\phrase\phrase as phrase_api;
use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\phrase_type;
use cfg\library;
use cfg\phr_ids;
use cfg\phrase_list;
use cfg\triple;
use cfg\verb;
use cfg\word_list;
use test\test_cleanup;
use const test\TW_2017;
use const test\TW_ABB;
use const test\TW_CHF;
use const test\TW_MIO;
use const test\TW_SALES;
use const test\TW_VESTAS;

class phrase_list_test
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $verbs;

        $t->header('Test the phrase list class (src/main/php/model/phrase/phrase_list.php)');

        // TODO make prepare not needed any more
        $t->test_word(TW_CHF, phrase_type::MEASURE);
        $t->test_word(TW_SALES);

        // load the main test word and verb
        $wrd_company = $t->test_word(word_api::TN_READ);
        $is_id = $verbs->id(verb::IS);

        // prepare test by loading Insurance Zurich
        $wrd_zh = $t->load_word(word_api::TN_ZH);
        $lnk_company = new triple($usr);
        $lnk_company->load_by_link_id($wrd_zh->id(), $is_id, $wrd_company->id());
        $triple_sample_id = $lnk_company->id();

        // test the phrase loading via id
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(TW_ABB, TW_VESTAS));
        $id_lst = $wrd_lst->ids();
        $id_lst[] = $triple_sample_id * -1;
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_names_by_ids(new phr_ids($id_lst));
        $target = '"' . TW_ABB . '","' . TW_VESTAS . '","' . triple_api::TN_ZH_COMPANY . '"';
        $target = '"' . TW_ABB . '","' . TW_VESTAS . '"';
        $result = $phr_lst->dsp_name();
        $t->display('phrase->load via id', $target, $result);

        // ... the complete word list, which means split the triples into single words
        $wrd_lst_all = $phr_lst->wrd_lst_all();
        $target = '"' . TW_ABB . '","' . TW_VESTAS . '","' . word_api::TN_ZH . '","' . word_api::TN_COMPANY . '"';
        $target = '"' . TW_ABB . '","' . TW_VESTAS . '"';
        $result = $wrd_lst_all->name();
        $t->display('phrase->wrd_lst_all of list above', $target, $result);


        // test getting the parent for phrase list with ABB
        $lib = new library();
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(TW_ABB));
        $phr_lst = $wrd_lst->phrase_lst();
        $lst_parents = $phr_lst->foaf_parents($verbs->get_verb(verb::IS));
        $result = $lib->dsp_array($lst_parents->names());
        $target = word_api::TN_COMPANY; // order adjusted based on the number of usage
        $t->display('phrase_list->foaf_parents for ' . $phr_lst->dsp_name() . ' up', $target, $result);

        // ... same using is
        $phr_lst = $wrd_lst->phrase_lst();
        $lst_is = $phr_lst->is();
        $result = $lib->dsp_array($lst_is->names());
        $t->display('phrase_list->is for ' . $phr_lst->dsp_name() . ' up', $target, $result);

        // ... same with Vestas
        $wrd_lst = new word_list($usr);
        $phr_lst->load_by_names(array(TW_VESTAS));
        $phr_lst = $wrd_lst->phrase_lst();
        $lst_is = $phr_lst->is();
        $result = $lib->dsp_array($lst_is->names());
        // TODO activate Prio 1
        //$t->display('phrase_list->is for ' . $phr_lst->dsp_name() . ' up', $target, $result);

        // test the excluding function
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(TW_ABB, TW_SALES, TW_CHF, TW_MIO, TW_2017));
        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_time();
        $result = $phr_lst_ex->names();
        $target = [TW_ABB, TW_SALES, TW_CHF, TW_MIO];
        $t->assert_contains('phrase_list->ex_time of ' . $phr_lst->dsp_name(), $result, $target);
        $result = $phr_lst_ex->names();
        $target = [TW_2017];
        $t->assert_contains_not('phrase_list->ex_time ex ' . $phr_lst->dsp_name(), $result, $target);

        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_measure();
        $result = $phr_lst_ex->names();
        $target = [TW_ABB, TW_SALES, TW_MIO, TW_2017];
        $t->assert_contains('phrase_list->ex_measure of ' . $phr_lst->dsp_name(), $target, $result);
        $result = $phr_lst_ex->names();
        $target = [TW_CHF];
        $t->assert_contains_not('phrase_list->ex_measure ex ' . $phr_lst->dsp_name(), $target, $result);

        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_scaling();
        $result = $phr_lst_ex->names();
        $target = [TW_ABB, TW_SALES, TW_CHF, TW_2017];
        $t->assert_contains('phrase_list->ex_scaling of ' . $phr_lst->dsp_name(), $result, $target);
        $result = $phr_lst_ex->names();
        $target = [TW_MIO];
        $t->assert_contains_not('phrase_list->ex_scaling ex ' . $phr_lst->dsp_name(), $result, $target);

    }

}