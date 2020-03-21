<?php 

/*

  test_word.php - TESTing of the word class
  -------------
  

zukunft.com - calc with words

copyright 1995-2020 by zukunft.com AG, Zurich

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

function create_base_words ($debug) {
  echo "<br><br><h2>Check if all base words are correct</h2><br>";
  test_word(TW_ABB);
  test_word(TW_DAN);
  test_word(TW_NESN);
  test_word(TW_VESTAS);
  test_word(TW_ZH);
  test_word(TW_SALES);
  test_word(TW_SALES2);
  test_word(TW_CHF);
  test_word(TW_EUR);
  test_word(TW_YEAR);
  test_word(TW_2012);
  test_word(TW_2013);
  test_word(TW_2014);
  test_word(TW_2015);
  test_word(TW_2016);
  test_word(TW_2017);
  test_word(TW_2020);
  test_word(TW_BIL);
  test_word(TW_MIO);
  test_word(TW_K);
  test_word(TW_M);
  test_word(TW_PCT);
  test_word(TW_CF);
  test_word(TW_TAX);
  test_word(TW_SECT_AUTO);
  test_word(TW_BALANCE);
  echo "<br><br>";
}

function run_word_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the word class (classes/word.php)</h2><br>";

  // check the first predefined word "Company"
  // load by id
  $wrd1 = New word;
  $wrd1->id = TEST_WORD_ID;
  $wrd1->usr = $usr;
  $wrd1->load($debug-1);
  $target = TEST_WORD;
  $result = $wrd1->name;
  $exe_start_time = test_show_result(', word->load for id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // load by name
  $wrd_company = test_word(TEST_WORD, $debug-1);

  // main word from url
  $wrd = New word;
  $wrd->usr = $usr;
  $wrd->main_wrd_from_txt(TEST_WORD_ID.','.TEST_WORD_ID, $debug-1);
  $target = TEST_WORD;
  $result = $wrd1->name;
  $exe_start_time = test_show_result(', word->main_wrd_from_txt', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // display
  $back = 1;
  $target = '<a href="/http/view.php?words='.TEST_WORD_ID.'&back=1">'.TEST_WORD.'</a>';
  $result = $wrd_company->display ($back, $debug-1);
  $exe_start_time = test_show_result(', word->display "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // word type
  $wrd_2013 = test_word(TW_2013, $debug-1);
  $target = True;
  $result = $wrd_2013->is_type(SQL_WORD_TYPE_TIME, $debug-1);
  $exe_start_time = test_show_result(', word->is_type for '.TW_2013.' and "'.SQL_WORD_TYPE_TIME.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // is time
  $target = True;
  $result = $wrd_2013->is_time($debug-1);
  $exe_start_time = test_show_result(', word->is_time for '.TW_2013.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // is not measure
  $target = False;
  $result = $wrd_2013->is_measure($debug-1);
  $exe_start_time = test_show_result(', word->is_measure for '.TW_2013.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // is measure
  $wrd_CHF = test_word(TW_CHF, $debug-1);
  $target = True;
  $result = $wrd_CHF->is_measure($debug-1);
  $exe_start_time = test_show_result(', word->is_measure for '.TW_CHF.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // is not scaling
  $target = False;
  $result = $wrd_CHF->is_scaling($debug-1);
  $exe_start_time = test_show_result(', word->is_scaling for '.TW_CHF.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // is scaling
  $wrd_mio = test_word(TW_MIO, $debug-1);
  $target = True;
  $result = $wrd_mio->is_scaling($debug-1);
  $exe_start_time = test_show_result(', word->is_scaling for '.TW_MIO.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // is not percent
  $target = False;
  $result = $wrd_mio->is_percent($debug-1);
  $exe_start_time = test_show_result(', word->is_percent for '.TW_MIO.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // is percent
  $wrd_pct = test_word(TW_PCT, $debug-1);
  $target = True;
  $result = $wrd_pct->is_percent($debug-1);
  $exe_start_time = test_show_result(', word->is_percent for '.TW_PCT.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // next word
  $wrd_2014 = test_word(TW_2014, $debug-1);
  $target = $wrd_2014->name;
  $wrd_next = $wrd_2013->next($debug-1);
  $result = $wrd_next->name;
  $exe_start_time = test_show_result(', word->next for '.TW_2013.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // prior word
  $target = $wrd_2013->name;
  $wrd_prior = $wrd_2014->prior($debug-1);
  $result = $wrd_prior->name;
  $exe_start_time = test_show_result(', word->prior for '.TW_2014.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // word childs
  $wrd_company = test_word(TEST_WORD, $debug-1);
  $wrd_ABB = test_word(TW_ABB, $debug-1);
  $wrd_lst = $wrd_company->childs($debug-1);
  $target = $wrd_ABB->name;
  if ($wrd_lst->does_contain($wrd_ABB, $debug-1)) {
    $result = $wrd_ABB->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->childs for "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB, 'out of '.$wrd_lst->dsp_id().'');

  // ... word childs excluding the start word
  $target = '';
  if ($wrd_lst->does_contain($wrd_company, $debug-1)) {
    $result = $wrd_company->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->childs for "'.TEST_WORD.'" excluding the start word', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

  // word are
  $wrd_lst = $wrd_company->are($debug-1);
  $target = $wrd_ABB->name;
  if ($wrd_lst->does_contain($wrd_ABB, $debug-1)) {
    $result = $wrd_ABB->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->are for "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

  // ... word are including the start word
  $target = $wrd_company->name;
  if ($wrd_lst->does_contain($wrd_company, $debug-1)) {
    $result = $wrd_company->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->are for "'.TEST_WORD.'" including the start word', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

  // word parents
  $wrd_ABB = test_word(TW_ABB, $debug-1);
  $wrd_company = test_word(TEST_WORD, $debug-1);
  $wrd_lst = $wrd_ABB->parents($debug-1);
  $target = $wrd_company->name;
  if ($wrd_lst->does_contain($wrd_company, $debug-1)) {
    $result = $wrd_company->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->parents for "'.TW_ABB.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

  // ... word parents excluding the start word
  $target = '';
  if ($wrd_lst->does_contain($wrd_ABB, $debug-1)) {
    $result = $wrd_ABB->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->parents for "'.TW_ABB.'" excluding the start word', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

  // word is
  /*
  to change this causes other problems at the moment. cleanup needed
  $wrd_ZH = test_word(TW_ZH, $debug-1);
  $wrd_canton = test_word(TW_CANTON, $debug-1);
  $target = $wrd_canton->name;
  $wrd_lst = $wrd_ZH->is($debug-1);
  if ($wrd_lst->does_contain($wrd_canton, $debug-1)) {
    $result = $wrd_canton->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->is for "'.TW_ZH.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');
  */

  // ... word is including the start word
  $target = $wrd_ZH->name;
  if ($wrd_lst->does_contain($wrd_ZH, $debug-1)) {
    $result = $wrd_ZH->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->is for "'.TW_ZH.'" including the start word', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

  // word is part
  $wrd_cf = test_word(TW_CF, $debug-1);
  $wrd_tax = test_word(TW_TAX, $debug-1);
  $target = $wrd_cf->name;
  $wrd_lst = $wrd_tax->is_part($debug-1);
  if ($wrd_lst->does_contain($wrd_cf, $debug-1)) {
    $result = $wrd_cf->name;
  } else {
    $result = '';
  }
  $exe_start_time = test_show_result(', word->is_part for "'.TW_TAX.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

  // save a new word
  $wrd_new = New word;
  $wrd_new->name = TEST_WORD;
  $wrd_new->usr = $usr;
  $result = $wrd_new->save($debug-1);
  $target = 'A word with the name "'.TEST_WORD.'" already exists. Please use another name.';
  $target = '';
  $exe_start_time = test_show_result(', word->save for "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

  // test the creation of a new word
  $wrd_add = New word;
  $wrd_add->name = TW_ADD;
  $wrd_add->usr = $usr;
  $result = $wrd_add->save($debug-1);
  $target = '1';
  $exe_start_time = test_show_result(', word->save for "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

  echo "... and also testing the user log class (classes/user_log.php)<br>";

  // ... check if the word creation has been logged
  if ($wrd_add->id > 0) {
    $log = New user_log;
    $log->table = 'words';
    $log->field = 'word_name';
    $log->row_id = $wrd_add->id;
    $log->usr_id = $usr->id;
    $result = $log->dsp_last(true, $debug);
  }
  $target = 'zukunft.com system batch job added '.TW_ADD.'';
  $exe_start_time = test_show_result(', word->save logged for "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... test if the new word has been created
  $wrd_added = load_word(TW_ADD, $debug-1);
  $result = $wrd_added->load($debug-1);
  if ($result == '') {
    if ($wrd_added->id > 0) {
      $result = $wrd_added->name;
    }
  }
  $target = TW_ADD;
  $exe_start_time = test_show_result(', word->load of added word "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the word can be renamed
  $wrd_added->name = TW_ADD_RENAMED;
  $result = $wrd_added->save($debug-1);
  $target = '1';
  $exe_start_time = test_show_result(', word->save rename "'.TW_ADD.'" to "'.TW_ADD_RENAMED.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

  // check if the word renaming was successful
  $wrd_renamed = New word;
  $wrd_renamed->name = TW_ADD_RENAMED;
  $wrd_renamed->usr = $usr;
  $result = $wrd_renamed->load($debug-1);
  if ($result == '') {
    if ($wrd_renamed->id > 0) {
      $result = $wrd_renamed->name;
    }
  }
  $target = TW_ADD_RENAMED;
  $exe_start_time = test_show_result(', word->load renamed word "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the word renaming has been logged
  $log = New user_log;
  $log->table = 'words';
  $log->field = 'word_name';
  $log->row_id = $wrd_renamed->id;
  $log->usr_id = $usr->id;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job changed '.TW_ADD.' to '.TW_ADD_RENAMED.'';
  $exe_start_time = test_show_result(', word->save rename logged for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the word parameters can be added
  $wrd_renamed->plural      = TW_ADD_RENAMED.'s';
  $wrd_renamed->description = TW_ADD_RENAMED.' description';
  $wrd_renamed->ref_1       = TW_ADD_RENAMED.' ref_1';
  $wrd_renamed->ref_2       = TW_ADD_RENAMED.' ref_2';
  $wrd_renamed->type_id     = cl(SQL_WORD_TYPE_OTHER);
  $result = $wrd_renamed->save($debug-1);
  $target = '11111';
  $exe_start_time = test_show_result(', word->save all word fields beside the name for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check if the word parameters have been added
  $wrd_reloaded = load_word(TW_ADD_RENAMED, $debug-1);
  $result = $wrd_reloaded->plural;
  $target = TW_ADD_RENAMED.'s';
  $exe_start_time = test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_reloaded->description;
  $target = TW_ADD_RENAMED.' description';
  $exe_start_time = test_show_result(', word->load description for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_reloaded->ref_1;
  $target = TW_ADD_RENAMED.' ref_1';
  $exe_start_time = test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_reloaded->ref_2;
  $target = TW_ADD_RENAMED.' ref_2';
  $exe_start_time = test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_reloaded->type_id;
  $target = cl(SQL_WORD_TYPE_OTHER);
  $exe_start_time = test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the word parameter adding have been logged
  $log = New user_log;
  $log->table = 'words';
  $log->field = 'plural';
  $log->row_id = $wrd_reloaded->id;
  $log->usr_id = $usr->id;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job added '.TW_ADD_RENAMED.'s';
  $exe_start_time = test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $log->field = 'description';
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job added '.TW_ADD_RENAMED.' description';
  $exe_start_time = test_show_result(', word->load description for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $log->field = 'ref_url_1';
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job added '.TW_ADD_RENAMED.' ref_1';
  $exe_start_time = test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $log->field = 'ref_url_2';
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job added '.TW_ADD_RENAMED.' ref_2';
  $exe_start_time = test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $log->field = 'word_type_id';
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job added differentiator filler';
  $exe_start_time = test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if a user specific word is created if another user changes the word
  $wrd_usr2 = New word;
  $wrd_usr2->name = TW_ADD_RENAMED;
  $wrd_usr2->usr = $usr2;
  $wrd_usr2->load($debug-1);
  $wrd_usr2->plural      = TW_ADD_RENAMED.'s2';
  $wrd_usr2->description = TW_ADD_RENAMED.' description2';
  $wrd_usr2->ref_1       = TW_ADD_RENAMED.' ref_3';
  $wrd_usr2->ref_2       = TW_ADD_RENAMED.' ref_4';
  $wrd_usr2->type_id     = cl(SQL_WORD_TYPE_TIME);
  $result = $wrd_usr2->save($debug-1);
  $target = '11111';
  $exe_start_time = test_show_result(', word->save all word fields for user 2 beside the name for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check if a user specific word changes have been saved
  $wrd_usr2_reloaded = New word;
  $wrd_usr2_reloaded->name = TW_ADD_RENAMED;
  $wrd_usr2_reloaded->usr = $usr2;
  $wrd_usr2_reloaded->load($debug-1);
  $result = $wrd_usr2_reloaded->plural;
  $target = TW_ADD_RENAMED.'s2';
  $exe_start_time = test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_usr2_reloaded->description;
  $target = TW_ADD_RENAMED.' description2';
  $exe_start_time = test_show_result(', word->load description for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_usr2_reloaded->ref_1;
  $target = TW_ADD_RENAMED.' ref_3';
  $exe_start_time = test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_usr2_reloaded->ref_2;
  $target = TW_ADD_RENAMED.' ref_4';
  $exe_start_time = test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_usr2_reloaded->type_id;
  $target = cl(SQL_WORD_TYPE_TIME);
  $exe_start_time = test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check the word for the original user remains unchanged
  $wrd_reloaded = load_word(TW_ADD_RENAMED, $debug-1);
  $result = $wrd_reloaded->plural;
  $target = TW_ADD_RENAMED.'s';
  $exe_start_time = test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_reloaded->description;
  $target = TW_ADD_RENAMED.' description';
  $exe_start_time = test_show_result(', word->load description for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_reloaded->ref_1;
  $target = TW_ADD_RENAMED.' ref_1';
  $exe_start_time = test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_reloaded->ref_2;
  $target = TW_ADD_RENAMED.' ref_2';
  $exe_start_time = test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_reloaded->type_id;
  $target = cl(SQL_WORD_TYPE_OTHER);
  $exe_start_time = test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if undo all specific changes removes the user word
  $wrd_usr2 = New word;
  $wrd_usr2->name = TW_ADD_RENAMED;
  $wrd_usr2->usr = $usr2;
  $wrd_usr2->load($debug-1);
  $wrd_usr2->plural      = TW_ADD_RENAMED.'s';
  $wrd_usr2->description = TW_ADD_RENAMED.' description';
  $wrd_usr2->ref_1       = TW_ADD_RENAMED.' ref_1';
  $wrd_usr2->ref_2       = TW_ADD_RENAMED.' ref_2';
  $wrd_usr2->type_id     = cl(SQL_WORD_TYPE_OTHER);
  $result = $wrd_usr2->save($debug-1);
  $target = '111111';
  $exe_start_time = test_show_result(', word->save undo the user word fields beside the name for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check if a user specific word changes have been saved
  $wrd_usr2_reloaded = New word;
  $wrd_usr2_reloaded->name = TW_ADD_RENAMED;
  $wrd_usr2_reloaded->usr = $usr2;
  $wrd_usr2_reloaded->load($debug-1);
  $result = $wrd_usr2_reloaded->plural;
  $target = TW_ADD_RENAMED.'s';
  $exe_start_time = test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_usr2_reloaded->description;
  $target = TW_ADD_RENAMED.' description';
  $exe_start_time = test_show_result(', word->load description for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_usr2_reloaded->ref_1;
  $target = TW_ADD_RENAMED.' ref_1';
  $exe_start_time = test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_usr2_reloaded->ref_2;
  $target = TW_ADD_RENAMED.' ref_2';
  $exe_start_time = test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $wrd_usr2_reloaded->type_id;
  $target = cl(SQL_WORD_TYPE_OTHER);
  $exe_start_time = test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // redo the user specific word changes
  // check if the user specific changes can be removed with one click

  // check if the deletion request has been logged
  $wrd = New word;

  // check if the deletion has been requested
  $wrd = New word;

  // confirm the deletion requested
  $wrd = New word;

  // check if the confirm of the deletion requested has been logged
  $wrd = New word;

  // check if the word has been delete
  $wrd = New word;

}

?>
