<?php

/*

  test_word_list.php - TESTing of the WORD LIST functions
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

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

// --------------------------------------
// start testing the system functionality 
// --------------------------------------

function run_word_list_test(testing $t)
{

    global $usr;

    $t->header('Test the word list class (classes/word_list.php)');

    // test load by word list by names
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_ZH, word::TN_2021, word::TN_MIO));
    $result = $wrd_lst->name();
    $target = '"' . word::TN_MIO . '","' . word::TN_2021 . '","' . word::TN_ZH . '"'; // order adjusted based on the number of usage
    $t->assert('word_list->load by names for ' . $wrd_lst->dsp_id(), $result, $target);

    // test load by word list by group id
    /*$wrd_grp_id = $wrd_lst->grp_id;
    $wrd_lst = New word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->grp_id = $wrd_grp_id;
    $wrd_lst->load();
    $result = dsp_array($wrd_lst->names());
    $target = "million,Sales,wrd"; // order adjusted based on the number of usage
    $t->assert('word_list->load by word group id for "'.$wrd_grp_id.'"', $result, $target, TIMEOUT_LIMIT_DB_MULTI); */

    // test add by verb e.g. "Zurich" "is a" "Canton", "City" or "Company"
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_ZH));
    $wrd_lst_linked = $wrd_lst->load_linked_words(cl(db_cl::VERB, verb::IS_A), word_select_direction::UP);
    $result = dsp_array($wrd_lst_linked->names());
    $target = word::TN_CITY . "," . word::TN_CANTON . "," . word::TN_COMPANY; // order adjusted based on the number of usage
    $t->assert('word_list->load_linked_words for "' . word::TN_ZH . '" "' . verb::IS_A . '" up', $result, $target);

    // test getting all parents e.g. "Cash" is part of "Current Assets" and "Assets"
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_CASH));
    $parents = $wrd_lst->foaf_parents(cl(db_cl::VERB, verb::IS_PART_OF));
    $result = dsp_array($parents->names());
    $target = word::TN_ASSETS_CURRENT . "," . word::TN_ASSETS;
    $t->assert('word_list->foaf_parent for "' . word::TN_ZH . '" "' . verb::IS_A . '" up', $result, $target);

    // test add parent step 1
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_CASH));
    $parents = $wrd_lst->parents(cl(db_cl::VERB, verb::IS_PART_OF), 1);
    $result = dsp_array($parents->names());
    $target = word::TN_ASSETS_CURRENT;
    $t->assert('word_list->parents for "' . word::TN_CASH . '" "' . verb::IS_PART_OF . '" up', $result, $target);

    // test add parent step 2
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_CASH));
    $parents = $wrd_lst->parents(cl(db_cl::VERB, verb::IS_PART_OF), 2);
    $result = dsp_array($parents->names());
    $target = word::TN_ASSETS_CURRENT . "," . word::TN_ASSETS;
    $t->assert('word_list->parents for "' . word::TN_CASH . '" "' . verb::IS_PART_OF . '" up', $result, $target);

    // test add child and contains
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_CANTON));
    $children = $wrd_lst->foaf_children(cl(db_cl::VERB, verb::IS_A));
    $wrd = $t->load_word(word::TN_ZH);
    $result = $children->does_contain($wrd);
    $t->assert('word_list->foaf_children is "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_ZH . ' ', $result, true);

    // test direct children
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_CANTON));
    $children = $wrd_lst->children(cl(db_cl::VERB, verb::IS_A), 1);
    $wrd = $t->load_word(word::TN_ZH);
    $result = $children->does_contain($wrd);
    $t->assert('word_list->children is "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_ZH . ' ', $result, true);

    // test is
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_ZH));
    $lst_is = $wrd_lst->is();
    $result = dsp_array($lst_is->names());
    $target = dsp_array(array(word::TN_CITY, word::TN_CANTON)); // order adjusted based on the number of usage
    $target = dsp_array(array(word::TN_CITY, word::TN_CANTON, word::TN_COMPANY)); // order adjusted based on the number of usage
    $t->assert('word_list->is for ' . $wrd_lst->name() . ' up', $result, $target);

    // test "are" e.g. "Cantons are Zurich and ..."
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_CANTON));
    $lst_are = $wrd_lst->are();
    $wrd = $t->load_word(word::TN_ZH);
    $result = $lst_are->does_contain($wrd);
    $t->assert('word_list->are "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_ZH . ' ', $result, true);

    // test "contains" e.g. "Cash Flow Statement contains Taxes and ..."
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_CASH_FLOW));
    $lst_contains = $wrd_lst->contains();
    $wrd = $t->load_word(word::TN_TAX_REPORT);
    $result = $lst_contains->does_contain($wrd);
    $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_TAX_REPORT, $result, true);

    // test "are and contains"
    // e.g. "a Cash Flow Statement is a Financial Report, and it contains the tax statement ..."
    // so the words related to "Financial Report" are "Cash Flow Statement" and "Tax Statement"
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_FIN_REPORT));
    $lst_related = $wrd_lst->are_and_contains();
    $wrd_cf = $t->load_word(word::TN_CASH_FLOW);
    $result = $lst_related->does_contain($wrd_cf);
    $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_CASH_FLOW, $result, true);
    $wrd_tax = $t->load_word(word::TN_TAX_REPORT);
    $result = $lst_related->does_contain($wrd_tax);
    $t->assert('word_list->contains "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_TAX_REPORT, $result, true);

    // ....

    // exclude types
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_ZH, word::TN_2021, word::TN_CHF, word::TN_MIO));
    $wrd_lst_ex = clone $wrd_lst;
    $wrd_lst_ex->ex_time();
    $result = $wrd_lst_ex->name();
    $target = '"' . word::TN_CHF . '","' . word::TN_MIO . '","' . word::TN_ZH . '"'; // the creation should be tested, but how?
    $t->dsp('word_list->ex_time for ' . $wrd_lst->name(), $target, $result);

    // add a test value
    $t->test_value(array(word::TN_ZH, word::TN_2021, word::TN_CHF, word::TN_MIO), value::TEST_VALUE);
    $t->test_value(array(word::TN_CANTON, word::TN_2021, word::TN_CHF, word::TN_MIO), value::TEST_FLOAT);

    // test group id
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_ZH, word::TN_2021, word::TN_CHF, word::TN_MIO));
    $grp = new phrase_group($usr);
    $grp->load_by_ids((new phr_ids($wrd_lst->ids())));
    $result = $grp->get_id();
    $target = 1; // the creation should be tested, but how?
    if ($result > 0) {
        $target = $result;
    }
    $t->dsp('phrase_group->get_id for "' . implode('","', $wrd_lst->names()) . '"', $target, $result);

    // test word list value
    $val = $wrd_lst->value();
    $result = $val->number;
    $target = value::TEST_VALUE;
    $t->dsp('word_list->value for ' . $wrd_lst->dsp_id(), $target, $result);

    // test word list value scaled
    // TODO review !!!
    $val = $wrd_lst->value_scaled();
    $result = $val->number;
    $target = value::TEST_VALUE;
    $t->dsp('word_list->value_scaled for ' . $wrd_lst->dsp_id(), $target, $result);

    // test another group value
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_CANTON, word::TN_2021, word::TN_CHF, word::TN_MIO));
    $val = $wrd_lst->value();
    $result = $val->number;
    $target = value::TEST_FLOAT;
    $t->dsp('word_list->value for ' . $wrd_lst->dsp_id(), $target, $result);

    // test assume time
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_ZH, word::TN_2021, word::TN_MIO));
    $abb_last_year = $wrd_lst->assume_time();
    $result = $abb_last_year->name;
    $target = word::TN_2021;
    $t->dsp('word_list->assume_time for ' . $wrd_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_DB);


    // word sort
    $wrd_ZH = $t->load_word(word::TN_ZH);
    $wrd_lst = $wrd_ZH->parents();
    $wrd_lst->name_sort();
    $target = '"' . word::TN_CITY . '","' . word::TN_CANTON . '","' . word::TN_COMPANY . '"';
    $result = $wrd_lst->dsp_name();
    $t->dsp('word_list->sort for "' . word::TN_ZH . '"', $target, $result);

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
    $target = array("April","December","February","January","March","November","October","September");
    $t->dsp('word_list->diff of ' . $wrd_lst->dsp_id() . ' with ' . $del_wrd_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_DB);

}

