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

namespace unit_write;

include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'verbs.php';

use api\value\value as value_api;
use api\word\word as word_api;
use cfg\group\group;
use cfg\phrase\phrase_type;
use cfg\verb\verb;
use cfg\word\word_list;
use shared\enum\foaf_direction;
use shared\library;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\verbs;
use test\test_cleanup;

class word_list_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $vrb_cac;

        $t->header('Test the word list class (classes/word_list.php)');

        /*
         * prepare
         */

        // create category test words for "Zurich is a Canton" and "Zurich is a City"
        // which implies that Canton contains Zurich and City contains Zurich
        // to avoid conflicts the test words actually used are 'System Test Word Category e.g. Canton' as category word
        // and 'System Test Word Member e.g. Zurich' as member
        $wrd_canton = $t->test_word(word_api::TN_CANTON);
        $wrd_city = $t->test_word(word_api::TN_CITY);
        $wrd_ZH = $t->test_word(word_api::TN_ZH);
        $t->test_triple(word_api::TN_ZH, verbs::IS, word_api::TN_CANTON);
        $t->test_triple(word_api::TN_ZH, verbs::IS, word_api::TN_CITY);

        // create the test words and relations for multi level contains
        // e.g. assets contain current assets which contains cash
        $t->test_word(word_api::TN_ASSETS);
        $t->test_word(word_api::TN_ASSETS_CURRENT);
        $t->test_word(word_api::TN_CASH);
        $t->test_triple(word_api::TN_CASH, verbs::IS_PART_OF, word_api::TN_ASSETS_CURRENT);
        $t->test_triple(word_api::TN_ASSETS_CURRENT, verbs::IS_PART_OF, word_api::TN_ASSETS);

        // create the test words and relations for differentiators
        // e.g. energy can be a sector
        $t->test_word(word_api::TN_SECTOR);
        $t->test_word(word_api::TN_ENERGY);
        $t->test_word(word_api::TN_WIND_ENERGY);
        $t->test_triple(word_api::TN_SECTOR, verbs::CAN_CONTAIN, word_api::TN_ENERGY);
        $t->test_triple(word_api::TN_ENERGY, verbs::CAN_CONTAIN, word_api::TN_WIND_ENERGY);

        // create the test words and relations for a parent child relation without inheritance
        // e.g. ...
        $wrd_cf = $t->test_word(word_api::TWN_CASH_FLOW);
        $wrd_tax = $t->test_word(word_api::TN_TAX_REPORT);
        $wrd_time = $t->test_word(word_api::TN_2021, phrase_type_shared::TIME);
        $t->test_triple(word_api::TN_TAX_REPORT, verbs::IS_PART_OF, word_api::TWN_CASH_FLOW);

        // create the test words and relations many mixed relations
        // e.g. a financial report
        $t->test_word(word_api::TN_FIN_REPORT);
        $t->test_triple(word_api::TWN_CASH_FLOW, verbs::IS, word_api::TN_FIN_REPORT);

        // is measure
        $wrd_measure = $t->test_word(word_api::TWN_CHF, phrase_type_shared::MEASURE);
        $result = $wrd_measure->is_measure();
        $t->assert('word->is_measure for ' . word_api::TWN_CHF, $result, true);

        // add a test value
        $t->test_value(array(word_api::TN_ZH, word_api::TN_2021, word_api::TWN_CHF, word_api::TN_MIO), value_api::TV_INT);
        $t->test_value(array(word_api::TN_CANTON, word_api::TN_2021, word_api::TWN_CHF, word_api::TN_MIO), value_api::TV_FLOAT);

        /*
         * load
         */

        // test load by word list by names
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_2021, word_api::TN_MIO));
        $result = $wrd_lst->name();
        $target = '"' . word_api::TN_MIO . '","' . word_api::TN_2021 . '","' . word_api::TN_ZH . '"'; // order adjusted based on the number of usage
        $t->assert('word_list->load by names for ' . $wrd_lst->dsp_id(), $result, $target);

        $lib = new library();
        // test load by word list by group id
        /*$wrd_grp_id = $wrd_lst->grp_id;
        $wrd_lst = New word_list;
        $wrd_lst->usr = $usr;
        $wrd_lst->grp_id = $wrd_grp_id;
        $wrd_lst->load();
        $result = $lib->dsp_array($wrd_lst->names());
        $target = "million,Sales,wrd"; // order adjusted based on the number of usage
        $t->assert('word_list->load by word group id for "'.$wrd_grp_id.'"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI); */

        // test add by verb e.g. "Zurich" "is a" "Canton", "City" or "Company"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_ZH));
        $wrd_lst_linked = $wrd_lst->load_linked_words($vrb_cac->get_verb(verbs::IS), foaf_direction::UP);
        $result = $lib->dsp_array($wrd_lst_linked->names());
        $target = word_api::TN_CANTON . "," . word_api::TN_CITY . "," . word_api::TN_COMPANY; // order adjusted based on the number of usage
        $t->assert('word_list->load_linked_words for "' . word_api::TN_ZH . '" "' . verbs::IS . '" up', $result, $target);

        // test getting all parents e.g. "Cash" is part of "Current Assets" and "Assets"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CASH));
        $parents = $wrd_lst->foaf_parents($vrb_cac->get_verb(verbs::IS_PART_OF));
        $result = $lib->dsp_array($parents->names());
        $target = word_api::TN_ASSETS_CURRENT . "," . word_api::TN_ASSETS;
        $t->assert('word_list->foaf_parent for "' . word_api::TN_ZH . '" "' . verbs::IS . '" up', $result, $target);

        // test add parent step 1
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CASH));
        $parents = $wrd_lst->parents($vrb_cac->get_verb(verbs::IS_PART_OF), 1);
        $result = $lib->dsp_array($parents->names());
        $target = word_api::TN_ASSETS_CURRENT;
        $t->assert('word_list->parents for "' . word_api::TN_CASH . '" "' . verbs::IS_PART_OF . '" up', $result, $target);

        // test add parent step 2
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CASH));
        $parents = $wrd_lst->parents($vrb_cac->get_verb(verbs::IS_PART_OF), 2);
        $result = $lib->dsp_array($parents->names());
        $target = word_api::TN_ASSETS_CURRENT . "," . word_api::TN_ASSETS;
        $t->assert('word_list->parents for "' . word_api::TN_CASH . '" "' . verbs::IS_PART_OF . '" up', $result, $target);

        // test add child and contains
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON));
        $children = $wrd_lst->children($vrb_cac->get_verb(verbs::IS));
        $wrd = $t->load_word(word_api::TN_ZH);
        $result = $children->does_contain($wrd);
        $t->assert('word_list->foaf_children is "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_ZH . ' ', $result, true);

        // test direct children
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON));
        $children = $wrd_lst->direct_children($vrb_cac->get_verb(verbs::IS));
        $wrd = $t->load_word(word_api::TN_ZH);
        $result = $children->does_contain($wrd);
        $t->assert('word_list->children is "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_ZH . ' ', $result, true);

        // test is
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_ZH));
        $lst_is = $wrd_lst->is();
        $result = $lib->dsp_array($lst_is->names());
        $target = $lib->dsp_array(array(word_api::TN_CANTON, word_api::TN_CITY, word_api::TN_COMPANY)); // order adjusted based on the number of usage
        $t->assert('word_list->is for ' . $wrd_lst->name() . ' up', $result, $target);

        // test "are" e.g. "Cantons are Zurich and ..."
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON));
        $lst_are = $wrd_lst->are();
        $wrd = $t->load_word(word_api::TN_ZH);
        $result = $lst_are->does_contain($wrd);
        $t->assert('word_list->are "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_ZH . ' ', $result, true);

        // test "contains" e.g. "Cash Flow Statement contains Taxes and ..."
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TWN_CASH_FLOW));
        $lst_contains = $wrd_lst->contains();
        $wrd = $t->load_word(word_api::TN_TAX_REPORT);
        $result = $lst_contains->does_contain($wrd);
        $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_TAX_REPORT, $result, true);

        // test "are and contains"
        // e.g. "a Cash Flow Statement is a Financial Report, and it contains the tax statement ..."
        // so the words related to "Financial Report" are "Cash Flow Statement" and "Tax Statement"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_FIN_REPORT));
        $lst_related = $wrd_lst->are_and_contains();
        $wrd_cf = $t->load_word(word_api::TWN_CASH_FLOW);
        $result = $lst_related->does_contain($wrd_cf);
        $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TWN_CASH_FLOW, $result, true);
        $wrd_tax = $t->load_word(word_api::TN_TAX_REPORT);
        $result = $lst_related->does_contain($wrd_tax);
        $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_TAX_REPORT, $result, true);

        // test "differentiators"
        // e.g. a "Sector" "can contain" "Energy"
        // or the other way round "Energy" "can be a (differentiator for)" "Sector"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_SECTOR));
        $lst_differentiators = $wrd_lst->differentiators();
        $wrd_energy = $t->load_word(word_api::TN_ENERGY);
        $result = $lst_differentiators->does_contain($wrd_energy);
        $t->assert('word_list->differentiators "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_ENERGY, $result, true);

        // test "differentiators_all"
        // e.g. a "Sector" "can contain" "Energy" and "Wind Energy"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_SECTOR));
        $lst_differentiators = $wrd_lst->differentiators_all();
        $wrd_wind = $t->load_word(word_api::TN_WIND_ENERGY);
        $result = $lst_differentiators->does_contain($wrd_wind);
        $t->assert('word_list->differentiators_all "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_WIND_ENERGY, $result, true);

        // test "differentiators_filtered"
        // e.g. a "Sector" "can contain" "Wind Energy" and "Energy" can be filtered
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_SECTOR));
        $wrd_lst_filter = new word_list($usr);
        $wrd_lst_filter->load_by_names(array(word_api::TN_ENERGY));
        $lst_differentiators = $wrd_lst->differentiators_filtered($wrd_lst_filter);
        $result = $lst_differentiators->does_contain($wrd_energy);
        $t->assert('word_list->differentiators_filtered "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_ENERGY, $result, true);
        $wrd_wind = $t->load_word(word_api::TN_WIND_ENERGY);
        $result = $lst_differentiators->does_contain($wrd_wind);
        $t->assert('word_list->differentiators_filtered "' . implode('","', $wrd_lst->names()) . '", which contains not ' . word_api::TN_WIND_ENERGY, $result, false);
        $wrd_energy = $t->load_word(word_api::TN_ENERGY);

        // test "keep_only_specific" e.g. keep "Zurich" but remove "Canton"
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON, word_api::TN_ZH));
        $lst_specific = $wrd_lst->keep_only_specific();
        $wrd_specific = $t->load_word(word_api::TN_ZH);
        $result = $lst_specific->does_contain($wrd_specific);
        $t->assert('word_list->are "' . implode('","', $wrd_lst->names()) . '", which contains ' . word_api::TN_ZH . ' ', $result, true);
        $wrd = $t->load_word(word_api::TN_CANTON);
        $result = $lst_specific->does_contain($wrd);
        $t->assert('word_list->keep_only_specific "' . implode('","', $wrd_lst->names()) . '", which contains not ' . word_api::TN_CANTON . ' ', $result, false);


        $t->subheader('Test info functions');

        // test "has time" for 2020 is supposed to be true
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_2020));
        $result = $wrd_lst->has_time();
        $t->assert('word_list->has_time ' . $wrd_lst->dsp_id(), $result, true);

        // test "has time" for Canton is supposed to be false
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON));
        $result = $wrd_lst->has_time();
        $t->assert('word_list->has_time ' . $wrd_lst->dsp_id(), $result, false);

        // test "has_measure" for CHF is supposed to be true
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TWN_CHF));
        $result = $wrd_lst->has_measure();
        $t->assert('word_list->has_measure ' . $wrd_lst->dsp_id(), $result, true);

        // test "has_measure" for Canton is supposed to be false
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON));
        $result = $wrd_lst->has_measure();
        $t->assert('word_list->has_measure ' . $wrd_lst->dsp_id(), $result, false);

        // test "has_scaling" for CHF is supposed to be true
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_MIO));
        $result = $wrd_lst->has_scaling();
        $t->assert('word_list->has_scaling ' . $wrd_lst->dsp_id(), $result, true);

        // test "has_scaling" for Canton is supposed to be false
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON));
        $result = $wrd_lst->has_scaling();
        $t->assert('word_list->has_scaling ' . $wrd_lst->dsp_id(), $result, false);

        // test "has_percent" for CHF is supposed to be true
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_PCT));
        $result = $wrd_lst->has_percent();
        $t->assert('word_list->has_percent ' . $wrd_lst->dsp_id(), $result, true);

        // test "has_percent" for Canton is supposed to be false
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON));
        $result = $wrd_lst->has_percent();
        $t->assert('word_list->has_percent ' . $wrd_lst->dsp_id(), $result, false);

        // ....

        // exclude types
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_2021, word_api::TWN_CHF, word_api::TN_MIO));
        $wrd_lst_ex = clone $wrd_lst;
        $wrd_lst_ex->ex_time();
        $result = $wrd_lst_ex->name();
        $target = '"' . word_api::TN_MIO . '","' . word_api::TWN_CHF . '","' . word_api::TN_ZH . '"'; // the creation should be tested, but how?
        $t->display('word_list->ex_time for ' . $wrd_lst->name(), $target, $result);

        // add a test value
        $t->test_value(array(word_api::TN_ZH, word_api::TN_2021, word_api::TWN_CHF, word_api::TN_MIO), value_api::TV_INT);
        $t->test_value(array(word_api::TN_CANTON, word_api::TN_2021, word_api::TWN_CHF, word_api::TN_MIO), value_api::TV_FLOAT);

        // test group id
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_2021, word_api::TWN_CHF, word_api::TN_MIO));
        $grp = new group($usr);
        $grp->load_by_phr_lst($wrd_lst->phrase_lst());
        $result = $grp->get_id();
        $target = 1; // the creation should be tested, but how?
        if ($result > 0) {
            $target = $result;
        }
        $t->display('phrase_group->get_id for "' . implode('","', $wrd_lst->names()) . '"', $target, $result);

        // test word list value
        $val = $wrd_lst->value();
        $result = $val->number();
        $t->assert('word_list->value for ' . $wrd_lst->dsp_id(), $result, value_api::TV_INT);

        // test word list value scaled
        // TODO review !!!
        $val = $wrd_lst->value_scaled();
        $result = $val->number();
        $t->assert('word_list->value_scaled for ' . $wrd_lst->dsp_id(), $result, value_api::TV_INT);

        // test another group value
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_CANTON, word_api::TN_2021, word_api::TWN_CHF, word_api::TN_MIO));
        $val = $wrd_lst->value();
        $result = $val->number();
        $target = value_api::TV_FLOAT;
        $t->display('word_list->value for ' . $wrd_lst->dsp_id(), $target, $result);

        // test assume time
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_2021, word_api::TN_MIO));
        $abb_last_year = $wrd_lst->assume_time();
        if ($abb_last_year != null) {
            $result = $abb_last_year->name();
        } else {
            $result = '';
        }
        $target = word_api::TN_2021;
        $t->display('word_list->assume_time for ' . $wrd_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_DB);


        // word sort
        $wrd_ZH = $t->load_word(word_api::TN_ZH);
        $wrd_lst = $wrd_ZH->parents();
        $wrd_lst->name_sort();
        $target = '"' . word_api::TN_CANTON . '","' . word_api::TN_CITY . '","' . word_api::TN_COMPANY . '"';
        $result = $wrd_lst->dsp_name();
        $t->display('word_list->sort for "' . word_api::TN_ZH . '"', $target, $result);

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
            "Juli",
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
            "Juli",
            "August"
        ));
        $wrd_lst->diff($del_wrd_lst);
        $result = $wrd_lst->names();
        $target = array("April", "December", "February", "January", "March", "November", "October", "September");
        $t->display('word_list->diff of ' . $wrd_lst->dsp_id() . ' with ' . $del_wrd_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_DB);

    }

}