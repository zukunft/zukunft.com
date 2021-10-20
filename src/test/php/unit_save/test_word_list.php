<?php

/*

  test_word_list.php - TESTing of the WORD LIST functions
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

// --------------------------------------
// start testing the system functionality 
// --------------------------------------

function run_word_list_test()
{

    global $usr;

    test_header('Test the word list class (classes/word_list.php)');

    // test load by word list by names
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->add_name(word::TN_2021);
    $wrd_lst->add_name(word::TN_MIO);
    $wrd_lst->load();
    $result = $wrd_lst->name();
    $target = '"' . word::TN_MIO . '","' . word::TN_2021 . '","' . word::TN_ZH . '"'; // order adjusted based on the number of usage
    test_dsp('word_list->load by names for ' . $wrd_lst->dsp_id() . '', $target, $result);

    // test load by word list by group id
    /*$wrd_grp_id = $wrd_lst->grp_id;
    $wrd_lst = New word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->grp_id = $wrd_grp_id;
    $wrd_lst->load();
    $result = dsp_array($wrd_lst->names());
    $target = "million,Sales,wrd"; // order adjusted based on the number of usage
    test_dsp('word_list->load by word group id for "'.$wrd_grp_id.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI); */

    // test add by type
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->load();
    $wrd_lst->add_by_type(Null, cl(db_cl::VERB, verb::IS_A), verb::DIRECTION_UP);
    $result = dsp_array($wrd_lst->names());
    $target = word::TN_ZH . "," . word::TN_CITY . "," . word::TN_CANTON . "," . word::TN_COMPANY; // order adjusted based on the number of usage
    test_dsp('word_list->add_by_type for "' . word::TN_ZH . '" up', $target, $result);

    // test add parent
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->load();
    $wrd_lst->foaf_parents(cl(db_cl::VERB, verb::IS_A));
    $result = dsp_array($wrd_lst->names());
    $target = word::TN_ZH . "," . word::TN_CITY . "," . word::TN_CANTON . "," . word::TN_COMPANY; // order adjusted based on the number of usage
    test_dsp('word_list->foaf_parent for "' . word::TN_ZH . '" up', $target, $result);

    // test add parent step
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->load();
    $wrd_lst->parents(cl(db_cl::VERB, verb::IS_A), 1);
    $result = dsp_array($wrd_lst->names());
    $target = word::TN_ZH . "," . word::TN_CITY . "," . word::TN_CANTON . "," . word::TN_COMPANY; // order adjusted based on the number of usage
    test_dsp('word_list->parents for "' . word::TN_ZH . '" up', $target, $result);

    // test add child and contains
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_CANTON);
    $wrd_lst->load();
    $wrd_lst->foaf_children(cl(db_cl::VERB, verb::IS_A));
    $wrd = load_word(word::TN_ZH);
    $result = $wrd_lst->does_contain($wrd);
    $target = true;
    test_dsp('word_list->foaf_children is "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_ZH . ' ', $target, $result);

    // test direct children
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_CANTON);
    $wrd_lst->load();
    $wrd_lst->children(cl(db_cl::VERB, verb::IS_A), 1,);
    $wrd = load_word(word::TN_ZH);
    $result = $wrd_lst->does_contain($wrd);
    $target = true;
    test_dsp('word_list->children is "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_ZH . ' ', $target, $result);

    // test is
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->load();
    $lst_is = $wrd_lst->is();
    $result = dsp_array($lst_is->names());
    $target = dsp_array(array(word::TN_CITY, word::TN_CANTON)); // order adjusted based on the number of usage
    $target = dsp_array(array(word::TN_CITY, word::TN_CANTON, word::TN_COMPANY)); // order adjusted based on the number of usage
    test_dsp('word_list->is for ' . $wrd_lst->name() . ' up', $target, $result);

    // test are
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_CANTON);
    $wrd_lst->load();
    $lst_are = $wrd_lst->are();
    $wrd = load_word(word::TN_ZH);
    $result = $lst_are->does_contain($wrd);
    $target = true;
    test_dsp('word_list->are "' . implode('","', $wrd_lst->names()) . '", which contains ' . word::TN_ZH . ' ', $target, $result);

    // ....

    // exclude types
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->add_name(word::TN_2021);
    $wrd_lst->add_name(word::TN_CHF);
    $wrd_lst->add_name(word::TN_MIO);
    $wrd_lst->load();
    $wrd_lst_ex = clone $wrd_lst;
    $wrd_lst_ex->ex_time();
    $result = $wrd_lst_ex->name();
    $target = '"' . word::TN_CHF . '","' . word::TN_MIO . '","' . word::TN_ZH . '"'; // also the creation should be tested, but how?
    test_dsp('word_list->ex_time for ' . $wrd_lst->name(), $target, $result);

    // add a test value
    test_value(array(word::TN_ZH, word::TN_2021, word::TN_CHF, word::TN_MIO), value::TEST_VALUE);
    test_value(array(word::TN_CANTON, word::TN_2021, word::TN_CHF, word::TN_MIO), value::TEST_FLOAT);

    // test group id
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->add_name(word::TN_2021);
    $wrd_lst->add_name(word::TN_CHF);
    $wrd_lst->add_name(word::TN_MIO);
    $wrd_lst->load();
    $grp = new phrase_group;
    $grp->usr = $usr;
    $grp->ids = $wrd_lst->ids;
    $result = $grp->get_id();
    $target = 1; // also the creation should be tested, but how?
    if ($result > 0) {
        $target = $result;
    }
    test_dsp('phrase_group->get_id for "' . implode('","', $wrd_lst->names()) . '"', $target, $result);

    // test word list value
    $val = $wrd_lst->value();
    $result = $val->number;
    $target = value::TEST_VALUE;
    test_dsp('word_list->value for ' . $wrd_lst->dsp_id() . '', $target, $result);

    // test word list value scaled
    // TODO review !!!
    $val = $wrd_lst->value_scaled();
    $result = $val->number;
    $target = value::TEST_VALUE;
    test_dsp('word_list->value_scaled for ' . $wrd_lst->dsp_id() . '', $target, $result);

    // test another group value
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_CANTON);
    $wrd_lst->add_name(word::TN_2021);
    $wrd_lst->add_name(word::TN_CHF);
    $wrd_lst->add_name(word::TN_MIO);
    $wrd_lst->load();
    $val = $wrd_lst->value();
    $result = $val->number;
    $target = value::TEST_FLOAT;
    test_dsp('word_list->value for ' . $wrd_lst->dsp_id() . '', $target, $result);

    // test assume time
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->add_name(word::TN_2021);
    $wrd_lst->add_name(word::TN_MIO);
    $wrd_lst->load();
    $abb_last_year = $wrd_lst->assume_time();
    $result = $abb_last_year->name;
    $target = word::TN_2021;
    test_dsp('word_list->assume_time for ' . $wrd_lst->dsp_id() . '', $target, $result, TIMEOUT_LIMIT_DB);


    // word sort
    $wrd_ZH = load_word(word::TN_ZH);
    $wrd_lst = $wrd_ZH->parents();
    $wrd_lst->osort();
    $target = '"' . word::TN_CITY . '","' . word::TN_CANTON . '","' . word::TN_COMPANY . '"';
    $result = $wrd_lst->name();
    test_dsp('word_list->sort for "' . word::TN_ZH . '"', $target, $result);

    /*
     * test the class functions not yet tested above
    */
    // test the diff functions
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name("January");
    $wrd_lst->add_name("February");
    $wrd_lst->add_name("March");
    $wrd_lst->add_name("April");
    $wrd_lst->add_name("May");
    $wrd_lst->add_name("June");
    $wrd_lst->add_name("Juli");
    $wrd_lst->add_name("August");
    $wrd_lst->add_name("September");
    $wrd_lst->add_name("October");
    $wrd_lst->add_name("November");
    $wrd_lst->add_name("December");
    $wrd_lst->load();
    $del_wrd_lst = new word_list;
    $del_wrd_lst->usr = $usr;
    $del_wrd_lst->add_name("May");
    $del_wrd_lst->add_name("June");
    $del_wrd_lst->add_name("Juli");
    $del_wrd_lst->add_name("August");
    $del_wrd_lst->load();
    $wrd_lst->diff($del_wrd_lst);
    $result = $wrd_lst->names();
    $target = array("April","December","February","January","March","November","October","September");
    test_dsp('word_list->diff of ' . $wrd_lst->dsp_id() . ' with ' . $del_wrd_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_DB);

}

