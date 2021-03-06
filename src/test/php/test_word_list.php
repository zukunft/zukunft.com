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
  
function run_word_list_test () {

  global $usr;
  global $exe_start_time;

  test_header('Test the word list class (classes/word_list.php)');

  // test load by word list by names
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->load();
  $result = $wrd_lst->name();
  $target = '"'.TW_MIO.'","'.TW_SALES.'","'.TW_ABB.'"'; // order adjusted based on the number of usage
  $exe_start_time = test_show_result('word_list->load by names for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test load by word list by group id
  /*$wrd_grp_id = $wrd_lst->grp_id;
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->grp_id = $wrd_grp_id;
  $wrd_lst->load();
  $result = implode(',',$wrd_lst->names());
  $target = "million,Sales,ABB"; // order adjusted based on the number of usage
  $exe_start_time = test_show_result('word_list->load by word group id for "'.$wrd_grp_id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); */

  // test add by type
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->load();
  $wrd_lst->add_by_type(Null, cl(DBL_LINK_TYPE_IS), "up");
  $result = implode(',',$wrd_lst->names());
  $target = TW_ABB.",".TEST_WORD; // order adjusted based on the number of usage
  $exe_start_time = test_show_result('word_list->add_by_type for "'.TW_ABB.'" up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test add parent
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->load();
  $wrd_lst->foaf_parents(cl(DBL_LINK_TYPE_IS));
  $result = implode(',',$wrd_lst->names());
  $target = TW_ABB.",".TEST_WORD; // order adjusted based on the number of usage
  $exe_start_time = test_show_result('word_list->foaf_parent for "'.TW_ABB.'" up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test add parent step
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->load();
  $wrd_lst->parents(cl(DBL_LINK_TYPE_IS), 1);
  $result = implode(',',$wrd_lst->names());
  $target = TW_ABB.",".TEST_WORD; // order adjusted based on the number of usage
  $exe_start_time = test_show_result('word_list->parents for "'.TW_ABB.'" up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test add child and contains
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TEST_WORD);
  $wrd_lst->load();
  $wrd_lst->foaf_children(cl(DBL_LINK_TYPE_IS));
  $ABB = load_word(TW_ABB);
  $result = $wrd_lst->does_contain($ABB);
  $target = true; 
  $exe_start_time = test_show_result('word_list->foaf_children is "'.implode('","',$wrd_lst->names()).'", which contains '.TW_ABB.' ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test direct children
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TEST_WORD);
  $wrd_lst->load();
  $wrd_lst->children(cl(DBL_LINK_TYPE_IS), 1,);
  $ABB = load_word(TW_ABB);
  $result = $wrd_lst->does_contain($ABB);
  $target = true; 
  $exe_start_time = test_show_result('word_list->children is "'.implode('","',$wrd_lst->names()).'", which contains '.TW_ABB.' ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test is
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->load();
  $lst_is = $wrd_lst->is();
  $result = implode(',',$lst_is->names());
  $target = TEST_WORD; // order adjusted based on the number of usage
  $exe_start_time = test_show_result('word_list->is for '.$wrd_lst->name().' up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test are
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TEST_WORD);
  $wrd_lst->load();
  $lst_are = $wrd_lst->are();
  $ABB = load_word(TW_ABB);
  $result = $lst_are->does_contain($ABB);
  $target = true; 
  $exe_start_time = test_show_result('word_list->are "'.implode('","',$wrd_lst->names()).'", which contains '.TW_ABB.' ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ....

  // exclude types
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load();
  $wrd_lst_ex = clone $wrd_lst;
  $wrd_lst_ex->ex_time();
  $result = $wrd_lst_ex->name();
  $target = '"'.TW_MIO.'","'.TW_CHF.'","'.TW_SALES.'","'.TW_ABB.'"'; // also the creation should be tested, but how?
  $exe_start_time = test_show_result('word_list->ex_time for '.$wrd_lst->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test group id
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load();
  $grp = New phrase_group;
  $grp->usr = $usr;         
  $grp->ids = $wrd_lst->ids;         
  $result = $grp->get_id();
  $target = "2116"; // also the creation should be tested, but how?
  $exe_start_time = test_show_result('phrase_group->get_id for "'.implode('","',$wrd_lst->names()).'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test word list value
  $val = $wrd_lst->value();
  $result = $val->number;
  $target = TV_ABB_SALES_2014;
  $exe_start_time = test_show_result('word_list->value for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test word list value scaled
  // review !!!
  $val = $wrd_lst->value_scaled();
  $result = $val->number;
  $target = TV_ABB_SALES_2014;
  $exe_start_time = test_show_result('word_list->value_scaled for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test another group value
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_NESN);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2016);
  $wrd_lst->load();
  $val = $wrd_lst->value();
  $result = $val->number;
  $target = TV_NESN_SALES_2016;
  $exe_start_time = test_show_result('word_list->value for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test assume time
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->load();
  $abb_last_year = $wrd_lst->assume_time();
  $result = $abb_last_year->name;
  $target = TW_2017;
  $exe_start_time = test_show_result('word_list->assume_time for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);


  // word sort
  $wrd_ZH = load_word(TW_ZH);
  $wrd_lst = $wrd_ZH->parents();
  $wrd_lst->wlsort();
  $target = '"'.TW_CANTON.'","'.TW_CITY.'","'.TEST_WORD.'"';
  $result = $wrd_lst->name();
  $exe_start_time = test_show_result('word_list->sort for "'.$wrd_ZH->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  /*
   * test the class functions not yet tested above
  */
  // test the diff functions
  $wrd_lst = New word_list;
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
  $del_wrd_lst = New word_list;
  $del_wrd_lst->usr = $usr;
  $del_wrd_lst->add_name("May");
  $del_wrd_lst->add_name("June");
  $del_wrd_lst->add_name("Juli");
  $del_wrd_lst->add_name("August");
  $del_wrd_lst->load();
  $wrd_lst->diff($del_wrd_lst);
  $result = $wrd_lst->names();
  $target = '';
  $exe_start_time = test_show_result('word_list->diff of '.$wrd_lst->dsp_id().' with '.$del_wrd_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

}

