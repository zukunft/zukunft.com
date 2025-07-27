<?php

/*

    test/php/unit_write/phrase_list_tests.php - write test PHRASE LISTS to the database and check the results
    -----------------------------------------
  

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

use cfg\const\paths;

include_once paths::SHARED_TYPES . 'phrase_type.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';

use cfg\phrase\phr_ids;
use cfg\phrase\phrase_list;
use cfg\word\triple;
use cfg\word\word_list;
use shared\library;
use shared\const\triples;
use shared\const\words;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\verbs;
use test\test_cleanup;

class phrase_list_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $vrb_cac;

        $t->header('phrase list database write tests');

        // TODO make prepare not needed any more
        $t->test_word(words::CHF, phrase_type_shared::MEASURE);
        $t->test_word(words::SALES);

        // load the main test word and verb
        $wrd_company = $t->test_word(words::MATH);
        $is_id = $vrb_cac->id(verbs::IS);

        // prepare test by loading Insurance Zurich
        $wrd_zh = $t->load_word(words::ZH);
        $lnk_company = new triple($usr);
        $lnk_company->load_by_link_id($wrd_zh->id(), $is_id, $wrd_company->id());
        $triple_sample_id = $lnk_company->id();

        // test the phrase loading via id
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(words::ABB, words::VESTAS));
        $id_lst = $wrd_lst->ids();
        $id_lst[] = $triple_sample_id * -1;
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_names_by_ids(new phr_ids($id_lst));
        $target = '"' . words::ABB . '","' . words::VESTAS . '","' . triples::COMPANY_ZURICH . '"';
        $target = '"' . words::ABB . '","' . words::VESTAS . '"';
        $result = $phr_lst->dsp_name();
        $t->display('phrase->load via id', $target, $result);

        // ... the complete word list, which means split the triples into single words
        $wrd_lst_all = $phr_lst->wrd_lst_all();
        $target = '"' . words::ABB . '","' . words::VESTAS . '","' . words::ZH . '","' . words::COMPANY . '"';
        $target = '"' . words::ABB . '","' . words::VESTAS . '"';
        $result = $wrd_lst_all->name();
        $t->display('phrase->wrd_lst_all of list above', $target, $result);


        // test getting the parent for phrase list with ABB
        $lib = new library();
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(words::ABB));
        $phr_lst = $wrd_lst->phrase_list();
        $lst_parents = $phr_lst->foaf_parents($vrb_cac->get_verb(verbs::IS));
        $result = $lib->dsp_array($lst_parents->names());
        $target = words::COMPANY; // order adjusted based on the number of usage
        $t->display('phrase_list->foaf_parents for ' . $phr_lst->dsp_name() . ' up', $target, $result);

        // ... same using is
        $phr_lst = $wrd_lst->phrase_list();
        $lst_is = $phr_lst->is();
        $result = $lib->dsp_array($lst_is->names());
        $t->display('phrase_list->is for ' . $phr_lst->dsp_name() . ' up', $target, $result);

        // ... same with Vestas
        $wrd_lst = new word_list($usr);
        $phr_lst->load_by_names(array(words::VESTAS));
        $phr_lst = $wrd_lst->phrase_list();
        $lst_is = $phr_lst->is();
        $result = $lib->dsp_array($lst_is->names());
        // TODO activate Prio 1
        //$t->display('phrase_list->is for ' . $phr_lst->dsp_name() . ' up', $target, $result);

        // test the excluding function
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::ABB, words::SALES, words::CHF, words::MIO, words::YEAR_2017));
        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_time();
        $result = $phr_lst_ex->names();
        $target = [words::ABB, words::SALES, words::CHF, words::MIO];
        $t->assert_contains('phrase_list->ex_time of ' . $phr_lst->dsp_name(), $result, $target);
        $result = $phr_lst_ex->names();
        $target = [words::YEAR_2017];
        $t->assert_contains_not('phrase_list->ex_time ex ' . $phr_lst->dsp_name(), $result, $target);

        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_measure();
        $result = $phr_lst_ex->names();
        $target = [words::ABB, words::SALES, words::MIO, words::YEAR_2017];
        $t->assert_contains('phrase_list->ex_measure of ' . $phr_lst->dsp_name(), $target, $result);
        $result = $phr_lst_ex->names();
        $target = [words::CHF];
        $t->assert_contains_not('phrase_list->ex_measure ex ' . $phr_lst->dsp_name(), $target, $result);

        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_scaling();
        $result = $phr_lst_ex->names();
        $target = [words::ABB, words::SALES, words::CHF, words::YEAR_2017];
        $t->assert_contains('phrase_list->ex_scaling of ' . $phr_lst->dsp_name(), $result, $target);
        $result = $phr_lst_ex->names();
        $target = [words::MIO];
        $t->assert_contains_not('phrase_list->ex_scaling ex ' . $phr_lst->dsp_name(), $result, $target);

    }

}