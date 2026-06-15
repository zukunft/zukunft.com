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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_list_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $sys;

        // init
        $t_db = new test_db_load($t);
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write phrase list ';
        $t->header($ts);

        // TODO make prepare not needed any more
        $t_db->test_word(words::CHF, phrase_type_shared::MEASURE);
        $t_db->test_word(word_names::SALES);

        // load the main test word and verb
        $wrd_company = $t_db->test_word(word_names::MATH);
        $is_id = $sys->typ_lst->vrb->id(verbs::IS);

        // prepare test by loading Insurance Zurich
        $wrd_zh = $t_db->load_word(word_names::ZH);
        $lnk_company = new triple($usr);
        $lnk_company->load_by_link_id($wrd_zh->id(), $is_id, $wrd_company->id());
        $triple_sample_id = $lnk_company->id();

        // test the phrase loading via id
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::ABB, word_names::VESTAS));
        $id_lst = $wrd_lst->ids();
        $id_lst[] = $triple_sample_id * -1;
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_names_by_ids(new phr_ids($id_lst));
        $target = '"' . word_names::ABB . '","' . word_names::VESTAS . '","' . triples::COMPANY_ZURICH . '"';
        $target = '"' . word_names::ABB . '","' . word_names::VESTAS . '"';
        $result = $phr_lst->dsp_name();
        $t->assert('phrase->load via id', $result, $target);

        // ... the complete word list, which means split the triples into single words
        $wrd_lst_all = $phr_lst->wrd_lst_all();
        $target = '"' . word_names::ABB . '","' . word_names::VESTAS . '","' . word_names::ZH . '","' . word_names::COMPANY . '"';
        $target = '"' . word_names::ABB . '","' . word_names::VESTAS . '"';
        $result = $wrd_lst_all->name();
        $t->assert('phrase->wrd_lst_all of list above', $result, $target);


        // test getting the parent for phrase list with ABB
        $lib = new library();
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::ABB));
        $phr_lst = $wrd_lst->phrase_list();
        $lst_parents = $phr_lst->foaf_parents($sys->typ_lst->vrb->get_verb(verbs::IS));
        $result = $lib->dsp_array($lst_parents->names());
        $target = word_names::COMPANY; // order adjusted based on the number of usage
        $t->assert('phrase_list->foaf_parents for ' . $phr_lst->dsp_name() . ' up', $result, $target);

        // ... same using is
        $phr_lst = $wrd_lst->phrase_list();
        $lst_is = $phr_lst->is();
        $result = $lib->dsp_array($lst_is->names());
        $t->assert('phrase_list->is for ' . $phr_lst->dsp_name() . ' up', $result, $target);

        // ... same with Vestas
        $wrd_lst = new word_list($usr);
        $phr_lst->load_by_names(array(word_names::VESTAS));
        $phr_lst = $wrd_lst->phrase_list();
        $lst_is = $phr_lst->is();
        $result = $lib->dsp_array($lst_is->names());
        // TODO Prio 1 activate
        //$t->assert('phrase_list->is for ' . $phr_lst->dsp_name() . ' up', $result, $target);

        // test the excluding function
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_names::ABB, word_names::SALES, words::CHF, word_names::MIO, word_names::YEAR_2017));
        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_time();
        $result = $phr_lst_ex->names();
        $target = [word_names::ABB, word_names::SALES, words::CHF, word_names::MIO];
        $t->assert_contains('phrase_list->ex_time of ' . $phr_lst->dsp_name(), $result, $target);
        $result = $phr_lst_ex->names();
        $target = [word_names::YEAR_2017];
        $t->assert_contains_not('phrase_list->ex_time ex ' . $phr_lst->dsp_name(), $result, $target);

        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_measure();
        $result = $phr_lst_ex->names();
        $target = [word_names::ABB, word_names::SALES, word_names::MIO, word_names::YEAR_2017];
        $t->assert_contains('phrase_list->ex_measure of ' . $phr_lst->dsp_name(), $target, $result);
        $result = $phr_lst_ex->names();
        $target = [words::CHF];
        $t->assert_contains_not('phrase_list->ex_measure ex ' . $phr_lst->dsp_name(), $target, $result);

        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_scaling();
        $result = $phr_lst_ex->names();
        $target = [word_names::ABB, word_names::SALES, words::CHF, word_names::YEAR_2017];
        $t->assert_contains('phrase_list->ex_scaling of ' . $phr_lst->dsp_name(), $result, $target);
        $result = $phr_lst_ex->names();
        $target = [word_names::MIO];
        $t->assert_contains_not('phrase_list->ex_scaling ex ' . $phr_lst->dsp_name(), $result, $target);

        // cleanup - fallback delete
        $t_wrd->cleanup($ts);
        $t_trp->cleanup($ts);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($usr_msg);

    }

}