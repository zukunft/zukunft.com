<?php

/*

    test/php/unit_write/word_list_tests.php - write test word lists to the database and check the results
    ---------------------------------------
  

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

use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_list_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $sys;

        // init
        $t_db = new test_db_load($t);
        $t_wrd = new test_words($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write word list ';
        $t->header($ts);

        /*
         * prepare
         */

        // create category test words for "Zurich is a canton" and "Zurich is a city"
        // which implies that canton contains Zurich and city contains Zurich
        // to avoid conflicts the test words actually used are 'System Test Word Category e.g. canton' as category word
        // and 'System Test Word Member e.g. Zurich' as member
        $wrd_canton = $t_db->test_word(word_names::CANTON);
        $wrd_city = $t_db->test_word(word_names::CITY);
        $wrd_ZH = $t_db->test_word(word_names::ZH);
        $t_db->test_triple(word_names::ZH, verbs::IS, word_names::CANTON);
        $t_db->test_triple(word_names::ZH, verbs::IS, word_names::CITY);

        // create the test words and relations for multi level contains
        // e.g. assets contain current assets which contains cash
        $t_db->test_word(word_names::TEST_ASSETS);
        $t_db->test_word(word_names::TEST_ASSETS_CURRENT);
        $t_db->test_word(word_names::TEST_CASH);
        $t_db->test_triple(word_names::TEST_CASH, verbs::PART_NAME, word_names::TEST_ASSETS_CURRENT);
        $t_db->test_triple(word_names::TEST_ASSETS_CURRENT, verbs::PART_NAME, word_names::TEST_ASSETS);

        // create the test words and relations for differentiators
        // e.g. energy can be a sector
        $t_db->test_word(word_names::TEST_SECTOR);
        $t_db->test_word(word_names::TEST_ENERGY);
        $t_db->test_word(word_names::TEST_WIND_ENERGY);
        $t_db->test_triple(word_names::TEST_SECTOR, verbs::CAN_CONTAIN, word_names::TEST_ENERGY);
        $t_db->test_triple(word_names::TEST_ENERGY, verbs::CAN_CONTAIN, word_names::TEST_WIND_ENERGY);

        // create the test words and relations for a parent child relation without inheritance
        // e.g. ...
        $wrd_cf = $t_db->test_word(word_names::TEST_CASH_FLOW);
        $wrd_tax = $t_db->test_word(word_names::TEST_TAX_REPORT);
        $wrd_time = $t_db->test_word(word_names::TEST_2021, phrase_type_shared::TIME);
        $t_db->test_triple(word_names::TEST_TAX_REPORT, verbs::PART_NAME, word_names::TEST_CASH_FLOW);

        // create the test words and relations many mixed relations
        // e.g. a financial report
        $t_db->test_word(word_names::TEST_FIN_REPORT);
        $t_db->test_triple(word_names::TEST_CASH_FLOW, verbs::IS, word_names::TEST_FIN_REPORT);

        // is measure
        $wrd_measure = $t_db->test_word(word_names::TEST_CHF, phrase_type_shared::MEASURE);
        $result = $wrd_measure->is_measure();
        $t->assert('word->is_measure for ' . word_names::TEST_CHF, $result, true);

        // add a test value
        $t_db->test_value(array(word_names::ZH, word_names::TEST_2021, word_names::TEST_CHF, word_names::MIO), values::SAMPLE_INT);
        $t_db->test_value(array(word_names::CANTON, word_names::TEST_2021, word_names::TEST_CHF, word_names::MIO), values::SAMPLE_FLOAT);

        /*
         * load
         */

        // test load by word list by names
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::ZH, word_names::TEST_2021, word_names::MIO));
        $result = $wrd_lst->name();
        $target = '"' . word_names::MIO . '","' . word_names::TEST_2021 . '","' . word_names::ZH . '"'; // order adjusted based on the number of usage
        $t->assert('word_list->load by names for ' . $wrd_lst->dsp_id(), $result, $target);

        $lib = new library();
        // test load by word list by group id
        /*$wrd_grp_id = $wrd_lst->grp_id;
        $wrd_lst = New word_list;
        $wrd_lst->usr = $usr;
        $wrd_lst->grp_id = $wrd_grp_id;
        $wrd_lst->load();
        $result = $lib->dsp_array($wrd_lst->names());
        $target = "million,sales,wrd"; // order adjusted based on the number of usage
        $t->assert('word_list->load by word group id for "'.$wrd_grp_id.'"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI); */

        // test add by verb e.g. "Zurich" "is a" "canton", "city" or "company"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::ZH));
        $wrd_lst_linked = $wrd_lst->load_linked_words($sys->typ_lst->vrb->get_verb(verbs::IS), foaf_direction::UP);
        $result = $lib->dsp_array($wrd_lst_linked->names());
        $target = word_names::CANTON . "," . word_names::CITY . "," . word_names::COMPANY; // order adjusted based on the number of usage
        $t->assert('word_list->load_linked_words for "' . word_names::ZH . '" "' . verbs::IS . '" up', $result, $target);

        // test getting all parents e.g. "Cash" is part of "Current Assets" and "Assets"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_CASH));
        $parents = $wrd_lst->foaf_parents($sys->typ_lst->vrb->get_verb(verbs::PART_NAME));
        $result = $lib->dsp_array($parents->names());
        $target = word_names::TEST_ASSETS_CURRENT . "," . word_names::TEST_ASSETS;
        $t->assert('word_list->foaf_parent for "' . word_names::ZH . '" "' . verbs::IS . '" up', $result, $target);

        // test add parent step 1
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_CASH));
        $parents = $wrd_lst->parents($sys->typ_lst->vrb->get_verb(verbs::PART_NAME), 1);
        $result = $lib->dsp_array($parents->names());
        $target = word_names::TEST_ASSETS_CURRENT;
        $t->assert('word_list->parents for "' . word_names::TEST_CASH . '" "' . verbs::PART_NAME . '" up', $result, $target);

        // test add parent step 2
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_CASH));
        $parents = $wrd_lst->parents($sys->typ_lst->vrb->get_verb(verbs::PART_NAME), 2);
        $result = $lib->dsp_array($parents->names());
        $target = word_names::TEST_ASSETS_CURRENT . "," . word_names::TEST_ASSETS;
        $t->assert('word_list->parents for "' . word_names::TEST_CASH . '" "' . verbs::PART_NAME . '" up', $result, $target);

        // test add child and contains
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON));
        $children = $wrd_lst->children($sys->typ_lst->vrb->get_verb(verbs::IS));
        $wrd = $t_db->load_word(word_names::ZH);
        $result = $children->does_contain($wrd);
        $t->assert('word_list->foaf_children is "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::ZH . ' ', $result, true);

        // test direct children
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON));
        $children = $wrd_lst->direct_children($sys->typ_lst->vrb->get_verb(verbs::IS));
        $wrd = $t_db->load_word(word_names::ZH);
        $result = $children->does_contain($wrd);
        $t->assert('word_list->children is "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::ZH . ' ', $result, true);

        // test is
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::ZH));
        $lst_is = $wrd_lst->is();
        $result = $lib->dsp_array($lst_is->names());
        $target = $lib->dsp_array(array(word_names::CANTON, word_names::CITY, word_names::COMPANY)); // order adjusted based on the number of usage
        $t->assert('word_list->is for ' . $wrd_lst->name() . ' up', $result, $target);

        // test "are" e.g. "cantons are Zurich and ..."
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON));
        $lst_are = $wrd_lst->are();
        $wrd = $t_db->load_word(word_names::ZH);
        $result = $lst_are->does_contain($wrd);
        $t->assert('word_list->are "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::ZH . ' ', $result, true);

        // test "contains" e.g. "Cash Flow Statement contains Taxes and ..."
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_CASH_FLOW));
        $lst_contains = $wrd_lst->contains();
        $wrd = $t_db->load_word(word_names::TEST_TAX_REPORT);
        $result = $lst_contains->does_contain($wrd);
        $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::TEST_TAX_REPORT, $result, true);

        // test "are and contains"
        // e.g. "a Cash Flow Statement is a Financial Report, and it contains the tax statement ..."
        // so the words related to "Financial Report" are "Cash Flow Statement" and "Tax Statement"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_FIN_REPORT));
        $lst_related = $wrd_lst->are_and_contains();
        $wrd_cf = $t_db->load_word(word_names::TEST_CASH_FLOW);
        $result = $lst_related->does_contain($wrd_cf);
        $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::TEST_CASH_FLOW, $result, true);
        $wrd_tax = $t_db->load_word(word_names::TEST_TAX_REPORT);
        $result = $lst_related->does_contain($wrd_tax);
        $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::TEST_TAX_REPORT, $result, true);

        // test "differentiators"
        // e.g. a "sector" "can contain" "Energy"
        // or the other way round "Energy" "can be a (differentiator for)" "sector"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_SECTOR));
        $lst_differentiators = $wrd_lst->differentiators();
        $wrd_energy = $t_db->load_word(word_names::TEST_ENERGY);
        $result = $lst_differentiators->does_contain($wrd_energy);
        // TODO Prio 1 activate
        //$t->assert('word_list->differentiators "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::TEST_ENERGY, $result, true);

        // test "differentiators_all"
        // e.g. a "sector" "can contain" "Energy" and "Wind Energy"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_SECTOR));
        $lst_differentiators = $wrd_lst->differentiators_all();
        $wrd_wind = $t_db->load_word(word_names::TEST_WIND_ENERGY);
        $result = $lst_differentiators->does_contain($wrd_wind);
        $t->assert('word_list->differentiators_all "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::TEST_WIND_ENERGY, $result, true);

        // test "differentiators_filtered"
        // e.g. a "sector" "can contain" "Wind Energy" and "Energy" can be filtered
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_SECTOR));
        $wrd_lst_filter = new word_list($usr);
        $wrd_lst_filter->load_by_names(array(word_names::TEST_ENERGY));
        $lst_differentiators = $wrd_lst->differentiators_filtered($wrd_lst_filter);
        $result = $lst_differentiators->does_contain($wrd_energy);
        $t->assert('word_list->differentiators_filtered "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::TEST_ENERGY, $result, true);
        $wrd_wind = $t_db->load_word(word_names::TEST_WIND_ENERGY);
        $result = $lst_differentiators->does_contain($wrd_wind);
        $t->assert('word_list->differentiators_filtered "' . implode('","', $wrd_lst->names()) . '", which contains not ' . word_names::TEST_WIND_ENERGY, $result, false);
        $wrd_energy = $t_db->load_word(word_names::TEST_ENERGY);

        // test "keep_only_specific" e.g. keep "Zurich" but remove "canton"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON, word_names::ZH));
        $lst_specific = $wrd_lst->keep_only_specific();
        $wrd_specific = $t_db->load_word(word_names::ZH);
        $result = $lst_specific->does_contain($wrd_specific);
        $t->assert('word_list->are "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_names::ZH . ' ', $result, true);
        $wrd = $t_db->load_word(word_names::CANTON);
        $result = $lst_specific->does_contain($wrd);
        $t->assert('word_list->keep_only_specific "' . implode('","', $wrd_lst->names()) . '", which contains not ' . word_names::CANTON . ' ', $result, false);


        $t->subheader($ts . 'info');

        // test "has time" for 2020 is supposed to be true
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::YEAR_2020));
        $result = $wrd_lst->has_time();
        $t->assert('word_list->has_time ' . $wrd_lst->dsp_id(), $result, true);

        // test "has time" for canton is supposed to be false
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON));
        $result = $wrd_lst->has_time();
        $t->assert('word_list->has_time ' . $wrd_lst->dsp_id(), $result, false);

        // test "has_measure" for CHF is supposed to be true
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::TEST_CHF));
        $result = $wrd_lst->has_measure();
        $t->assert('word_list->has_measure ' . $wrd_lst->dsp_id(), $result, true);

        // test "has_measure" for canton is supposed to be false
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON));
        $result = $wrd_lst->has_measure();
        $t->assert('word_list->has_measure ' . $wrd_lst->dsp_id(), $result, false);

        // test "has_scaling" for CHF is supposed to be true
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::MIO));
        $result = $wrd_lst->has_scaling();
        $t->assert('word_list->has_scaling ' . $wrd_lst->dsp_id(), $result, true);

        // test "has_scaling" for canton is supposed to be false
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON));
        $result = $wrd_lst->has_scaling();
        $t->assert('word_list->has_scaling ' . $wrd_lst->dsp_id(), $result, false);

        // test "has_percent" for CHF is supposed to be true
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(words::PCT));
        $result = $wrd_lst->has_percent();
        $t->assert('word_list->has_percent ' . $wrd_lst->dsp_id(), $result, true);

        // test "has_percent" for canton is supposed to be false
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON));
        $result = $wrd_lst->has_percent();
        $t->assert('word_list->has_percent ' . $wrd_lst->dsp_id(), $result, false);

        // ....

        // exclude types
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::ZH, word_names::TEST_2021, word_names::TEST_CHF, word_names::MIO));
        $wrd_lst_ex = clone $wrd_lst;
        $wrd_lst_ex->ex_time();
        $result = $wrd_lst_ex->name();
        $target = '"' . word_names::MIO . '","' . word_names::TEST_CHF . '","' . word_names::ZH . '"'; // the creation should be tested, but how?
        $t->assert('word_list->ex_time for ' . $wrd_lst->name(), $result, $target);

        // add a test value
        $t_db->test_value(array(word_names::ZH, word_names::TEST_2021, word_names::TEST_CHF, word_names::MIO), values::SAMPLE_INT);
        $t_db->test_value(array(word_names::CANTON, word_names::TEST_2021, word_names::TEST_CHF, word_names::MIO), values::SAMPLE_FLOAT);

        // test group id
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::ZH, word_names::TEST_2021, word_names::TEST_CHF, word_names::MIO));
        $grp = new group($usr);
        $grp->load_by_phr_lst($wrd_lst->phrase_list());
        $result = $grp->get_id();
        $target = 1; // the creation should be tested, but how?
        if ($result > 0) {
            $target = $result;
        }
        $t->assert('phrase_group->get_id for "' . implode('","', $wrd_lst->names()) . '"', $result, $target);

        // test word list value
        $val = $wrd_lst->value();
        $result = $val->number();
        $t->assert('word_list->value for ' . $wrd_lst->dsp_id(), $result, values::SAMPLE_INT);

        // test word list value scaled
        // TODO review !!!
        $val = $wrd_lst->value_scaled();
        $result = $val->number();
        $t->assert('word_list->value_scaled for ' . $wrd_lst->dsp_id(), $result, values::SAMPLE_INT);

        // test another group value
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::CANTON, word_names::TEST_2021, word_names::TEST_CHF, word_names::MIO));
        $val = $wrd_lst->value();
        $result = $val->number();
        $target = values::SAMPLE_FLOAT;
        $t->assert('word_list->value for ' . $wrd_lst->dsp_id(), $result, $target);

        // test assume time
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_names::ZH, word_names::TEST_2021, word_names::MIO));
        $abb_last_year = $wrd_lst->assume_time();
        if ($abb_last_year != null) {
            $result = $abb_last_year->name();
        } else {
            $result = '';
        }
        $target = word_names::TEST_2021;
        $t->assert('word_list->assume_time for ' . $wrd_lst->dsp_id(), $result, $target, $t::TIMEOUT_LIMIT_DB);


        // word sort
        $wrd_ZH = $t_db->load_word(word_names::ZH);
        $wrd_lst = $wrd_ZH->parents();
        $wrd_lst->name_sort();
        $target = '"' . word_names::CANTON . '","' . word_names::CITY . '","' . word_names::COMPANY . '"';
        $result = $wrd_lst->dsp_name();
        $t->assert_text_contains('word_list->sort for "' . word_names::ZH . '"', $result, $target);

        /*
         * test the class functions not yet tested above
        */
        // test the diff functions
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        ));
        $del_wrd_lst = new word_list($usr);
        $del_wrd_lst->load_by_names(array(
            "May",
            "June",
            "July",
            "August"
        ));
        $wrd_lst->remove($del_wrd_lst);
        $result = $wrd_lst->names();
        $target = array("April", "December", "February", "January", "March", "November", "October", "September");
        $t->assert('word_list->diff of ' . $wrd_lst->dsp_id() . ' with ' . $del_wrd_lst->dsp_id(), $result, $target, $t::TIMEOUT_LIMIT_DB);

        // cleanup - fallback delete
        $t_wrd->cleanup($ts);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($usr_msg);

    }

}