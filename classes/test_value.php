<?php 

/*

  test_value.php - TESTing of the VALUE class
  --------------
  

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

function run_value_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the value class (classes/value.php)</h2><br>";

  // test load by word list first to get the value id
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2013);
  $wrd_lst->load($debug-1);
  $abb_sales = New value;
  $abb_sales->ids = $wrd_lst->ids;
  $abb_sales->usr = $usr;
  $abb_sales->load($debug-1);
  $result = $abb_sales->number;
  $target = TV_ABB_SALES_2013;
  $exe_start_time = test_show_result(', value->load for a tern list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  if ($abb_sales->id > 0) {
    // test load by value id
    $val = New value;
    $val->id = $abb_sales->id;
    $val->usr = $usr;
    $val->load($debug-1);
    $result = $val->number;
    $target = TV_ABB_SALES_2013;
    $exe_start_time = test_show_result(', value->load for value id "'.$abb_sales->id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test load by word list first to get the value id
    $wrd_lst = New word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(TW_ABB);
    $wrd_lst->add_name(TW_SALES);
    $wrd_lst->add_name(TW_CHF);
    $wrd_lst->add_name(TW_MIO);
    $wrd_lst->add_name(TW_2014);
    $wrd_lst->load($debug-1);
    $abb_sales = New value;
    $abb_sales->ids = $wrd_lst->ids;
    $abb_sales->usr = $usr;
    $abb_sales->load($debug-1);
    $result = $abb_sales->number;
    $target = TV_ABB_SALES_2014;
    $exe_start_time = test_show_result(', value->load for another word list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test load by value id
    $val = New value;
    $val->id = $abb_sales->id;
    $val->usr = $usr;
    $val->load($debug-1);
    $result = $val->number;
    $target = TV_ABB_SALES_2014;
    $exe_start_time = test_show_result(', value->load for value id "'.$abb_sales->id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test rebuild_grp_id by value id
    $result = $val->check($debug-1);
    $target = '';
    $exe_start_time = test_show_result(', value->check for value id "'.$abb_sales->id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
  }

  // test another rebuild_grp_id by value id
  $chk_wrd_lst = New word_list;
  $chk_wrd_lst->usr = $usr;
  $chk_wrd_lst->add_name(TW_ABB);
  $chk_wrd_lst->add_name(TW_SALES);
  $chk_wrd_lst->add_name(TW_CHF);
  $chk_wrd_lst->add_name(TW_MIO);
  $chk_wrd_lst->add_name(TW_2013);
  $chk_wrd_lst->add_name(TW_SECT_AUTO);
  $chk_wrd_lst->load($debug-1);
  $chk_val = New value;
  $chk_val->ids = $chk_wrd_lst->ids;
  $chk_val->usr = $usr;
  $chk_val->load($debug-1);
  $target = '';
  if ($chk_val->id <= 0) {
    $result = 'No value found for '.$chk_wrd_lst->dsp_id().'.';
    $exe_start_time = test_show_result(', value->check for value id "'.implode(",",$chk_wrd_lst->names()).'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
  } else {
    $result = $chk_val->check($debug-1);
    $exe_start_time = test_show_result(', value->check for value id "'.implode(",",$chk_wrd_lst->names()).'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

    // ... and check the number
    $result = $chk_val->number;
    $target = TV_ABB_SALES_AUTO_2013;
    $exe_start_time = test_show_result(', value->load for "'.implode(',',$chk_wrd_lst->names()).'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... and check the words loaded
    $result = implode(',',$chk_val->wrd_lst->names());
    $target = 'million,CHF,Sales,ABB,Discrete Automation and Motion';
    $exe_start_time = test_show_result(', value->load words', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... and check the time word
    $result = $chk_val->time_phr->name;
    $target = TW_2013;
    $exe_start_time = test_show_result(', value->load time word', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... and check the word reloading by group
    $chk_val->wrd_lst = Null;
    $chk_val->load_phrases($debug-1);
    if (isset($chk_val->wrd_lst)) {
      $result = implode(',',$chk_val->wrd_lst->names());
    } else {
      $result = '';
    }
    $target = 'million,CHF,Sales,ABB,Discrete Automation and Motion';
    $exe_start_time = test_show_result(', value->load_phrases reloaded words', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... and check the time word reloading
    $chk_val->time_phr = Null;
    $chk_val->load_phrases($debug-1);
    if (isset($chk_val->time_phr)) {
      $result = $chk_val->time_phr->name;
    } else {
      $result = '';
    }
    $target = TW_2013;
    $exe_start_time = test_show_result(', value->load_phrases reloaded time word', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  }

  // test load the word list object
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load($debug-1);
  $wrd_lst->ex_time($debug-1);
  $grp = $wrd_lst->get_grp($debug-1);
  $val->grp = $grp;
  $val->grp_id = $grp->id;
  $val->load($debug-1);
  $result = '';
  if ($val->id <= 0) {
    $result = 'No value found for '.$val->dsp_id().'.';
  } else {
    if (isset($val->wrd_lst)) {
      $result = implode(',',$val->wrd_lst->names($debug-1));
    }
  }
  $target = implode(',',$wrd_lst->names($debug-1));
  $exe_start_time = test_show_result(', value->load for group id "'.$grp->id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test load the word list object via word ids
  $val->grp = 0;
  $val->wrd_ids = $wrd_lst->ids;
  $val->load($debug-1);
  $result = '';
  if ($val->id > 0) {
    if (isset($val->wrd_lst)) {
      $result = implode(',',$val->wrd_lst->names($debug-1));
    }
  }
  $target = implode(',',$wrd_lst->names($debug-1));
  $exe_start_time = test_show_result(', value->load for ids '.implode(',',$wrd_lst->ids).'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  

  // test the formatting of a value (percent)
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_DAN);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_PCT);
  $wrd_lst->add_name('United States');
  $wrd_lst->add_name(TW_2016);
  $wrd_lst->load($debug-1);
  $pct_val = New value;
  $pct_val->ids = $wrd_lst->ids;
  $pct_val->usr = $usr;
  $pct_val->load($debug-1);
  $result = $pct_val->display($back, $debug-1);
  $target = '11%';
  $exe_start_time = test_show_result(', value->val_formatted for a word list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test the scaling of a value
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load($debug-1);
  $dest_wrd_lst = New word_list;
  $dest_wrd_lst->usr = $usr;
  $dest_wrd_lst->add_name(TW_SALES);
  $dest_wrd_lst->add_name(TW_K);
  $dest_wrd_lst->load($debug-1);
  $mio_val = New value;
  $mio_val->ids = $wrd_lst->ids;
  $mio_val->usr = $usr;
  $mio_val->load($debug-1);
  $result = $mio_val->scale($dest_wrd_lst, $debug-1);
  $target = TV_ABB_SALES_2014 * 1000000;
  $exe_start_time = test_show_result(', value->val_scaling for a word list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test the figure object creation
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load($debug-1);
  $mio_val = New value;
  $mio_val->ids = $wrd_lst->ids;
  $mio_val->usr = $usr;
  $mio_val->load($debug-1);
  $fig = $mio_val->figure($debug-1);
  $result = $fig->display_linked('1', $debug-1);
  $target = '<a class="user_specific" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  $exe_start_time = test_show_result(', value->figure->display_linked for word list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test the HTML code creation
  $result = $mio_val->display($back, $debug-1);
  $target = number_format(TV_ABB_SALES_2014,0,DEFAULT_DEC_POINT,DEFAULT_THOUSAND_SEP);
  $exe_start_time = test_show_result(', value->display', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test the HTML code creation including the hyperlink
  $result = $mio_val->display_linked('1', $$debug-1);
  $target = '<a class="user_specific" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  $exe_start_time = test_show_result(', value->display_linked', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // convert the user input for the database
  $mio_val->usr_value = '46 000';
  $result = $mio_val->convert($debug-1);
  $target = TV_ABB_SALES_2014;
  $exe_start_time = test_show_result(', value->convert user input', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test adding a value in the database 
  // as it is call from value_add.php with all phrases in an id list including the time phrase, 
  // so the time phrase must be excluded
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ADD_RENAMED);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load($debug-1);
  $phr_lst = $wrd_lst->phrase_lst($debug-1);
  $add_val = New value;
  $add_val->ids = $phr_lst->ids;
  $add_val->number = 123456789;
  $add_val->usr = $usr;
  $result = $add_val->save($debug-1);
  $target = '1';
  $exe_start_time = test_show_result(', value->save '.$add_val->number.' for '.$wrd_lst->name().' by user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // ... check if the value adding has been logged
  if ($add_val->id > 0) {
    $log = New user_log;
    $log->table = 'values';
    $log->field = 'word_value';
    $log->row_id = $add_val->id;
    $log->usr_id = $usr->id;
    $result = $log->dsp_last(true, $debug-1);
  }
  $target = 'zukunft.com system batch job added 123456789';
  $exe_start_time = test_show_result(', value->save logged for "'.$wrd_lst->name().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the value has been added
  $added_val = New value;
  $added_val->ids = $phr_lst->ids;
  $added_val->usr = $usr;
  $added_val->load($debug-1);
  $result = $added_val->number;
  $target = '123456789';
  $exe_start_time = test_show_result(', value->load the value previous saved for "'.$wrd_lst->name().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  // remember the added value id to be able to remove the test
  $added_val_id = $added_val->id;

  // test if a value with the same phrases, but different time can be added
  $wrd_lst2 = New word_list;
  $wrd_lst2->usr = $usr;
  $wrd_lst2->add_name(TW_ADD_RENAMED);
  $wrd_lst2->add_name(TW_SALES);
  $wrd_lst2->add_name(TW_CHF);
  $wrd_lst2->add_name(TW_MIO);
  $wrd_lst2->add_name(TW_2015);
  $wrd_lst2->load($debug-1);
  $phr_lst2 = $wrd_lst2->phrase_lst($debug-1);
  $add_val2 = New value;
  $add_val2->ids = $phr_lst2->ids;
  $add_val2->number = 234567890;
  $add_val2->usr = $usr;
  $result = $add_val2->save($debug-1);
  $target = '1';
  $exe_start_time = test_show_result(', value->save '.$add_val2->number.' for '.$wrd_lst2->name().' by user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // ... check if the value adding has been logged
  if ($add_val->id > 0) {
    $log = New user_log;
    $log->table = 'values';
    $log->field = 'word_value';
    $log->row_id = $add_val2->id;
    $log->usr_id = $usr->id;
    $result = $log->dsp_last(true, $debug-1);
  }
  $target = 'zukunft.com system batch job added 234567890';
  $exe_start_time = test_show_result(', value->save logged for "'.$wrd_lst2->name().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the value has been added
  $added_val2 = New value;
  $added_val2->ids = $phr_lst2->ids;
  $added_val2->usr = $usr;
  $added_val2->load($debug-1);
  $result = $added_val2->number;
  $target = '234567890';
  $exe_start_time = test_show_result(', value->load the value previous saved for "'.$phr_lst2->name().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  // remember the added value id to be able to remove the test
  $added_val2_id = $added_val2->id;

  // check if the value can be changed
  $added_val = New value;
  $added_val->id = $added_val_id;
  $added_val->usr = $usr;
  $added_val->load($debug-1);
  $added_val->number = 987654321;
  $result = $added_val->save($debug-1);
  $target = '1';
  $exe_start_time = test_show_result(', word->save update value id "'.$added_val_id.'" from  "'.$add_val->number.'" to "'.$added_val->number.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // ... check if the value change has been logged
  if ($added_val->id > 0) {
    $log = New user_log;
    $log->table = 'values';
    $log->field = 'word_value';
    $log->row_id = $added_val->id;
    $log->usr_id = $usr->id;
    $result = $log->dsp_last(true, $debug-1);
  }
  $target = 'zukunft.com system batch job changed 123456789 to 987654321';
  $exe_start_time = test_show_result(', value->save logged for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the value has really been updated
  $added_val = New value;
  $added_val->ids = $phr_lst->ids;
  $added_val->usr = $usr;
  $added_val->load($debug-1);
  $result = $added_val->number;
  $target = '987654321';
  $exe_start_time = test_show_result(', value->load the value previous updated for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // check if a user specific value is created if another user changes the value
  /*$wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ADD_RENAMED);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load($debug-1);
  $phr_lst = $wrd_lst->phrase_lst($debug-1); */
  $val_usr2 = New value;
  //$val_usr2->ids = $phr_lst->ids;
  $val_usr2->id = $added_val_id;
  $val_usr2->usr = $usr2;
  $val_usr2->load($debug-1);
  $val_usr2->number = 23456;
  $result = $val_usr2->save($debug-1);
  $target = '1';
  $exe_start_time = test_show_result(', value->save '.$val_usr2->number.' for '.$wrd_lst->name().' and user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // ... check if the value change for the other user has been logged
  $val_usr2 = New value;
  $val_usr2->id = $added_val_id;
  $val_usr2->usr = $usr2;
  $val_usr2->load($debug-1);
  if ($val_usr2->id > 0) {
    $log = New user_log;
    $log->table = 'user_values';
    $log->field = 'word_value';
    $log->row_id = $val_usr2->id;
    $log->usr_id = $usr2->id;
    $result = $log->dsp_last(true, $debug-1);
  }
  $target = 'zukunft.com system test changed 987654321 to 23456';
  $exe_start_time = test_show_result(', value->save logged for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the value has really been updated
  $added_val_usr2 = New value;
  $added_val_usr2->ids = $phr_lst->ids;
  $added_val_usr2->usr = $usr2;
  $added_val_usr2->load($debug-1);
  $result = $added_val_usr2->number;
  $target = '23456';
  $exe_start_time = test_show_result(', value->load the value previous updated for "'.$wrd_lst->name().'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // ... check if the value for the original user remains unchanged
  $added_val = New value;
  $added_val->ids = $phr_lst->ids;
  $added_val->usr = $usr;
  $added_val->load($debug-1);
  $result = $added_val->number;
  $target = '987654321';
  $exe_start_time = test_show_result(', value->load for user "'.$usr->name.'" is still', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // check if undo all specific changes removes the user value
  $added_val_usr2 = New value;
  $added_val_usr2->ids = $phr_lst->ids;
  $added_val_usr2->usr = $usr2;
  $added_val_usr2->load($debug-1);
  $added_val_usr2->number = 987654321;
  $result = $added_val_usr2->save($debug-1);
  $target = '11';
  $exe_start_time = test_show_result(', value->save change to '.$val_usr2->number.' for '.$wrd_lst->name().' and user "'.$usr2->name.'" should undo the user change', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // ... check if the value change for the other user has been logged
  $val_usr2 = New value;
  $val_usr2->ids = $phr_lst->ids;
  $val_usr2->usr = $usr2;
  $val_usr2->load($debug-1);
  if ($val_usr2->id > 0) {
    $log = New user_log;
    $log->table = 'user_values';
    $log->field = 'word_value';
    $log->row_id = $val_usr2->id;
    $log->usr_id = $usr2->id;
    $result = $log->dsp_last(true, $debug-1);
  }
  $target = 'zukunft.com system test changed 23456 to 987654321';
  $exe_start_time = test_show_result(', value->save logged for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the value has really been changed back
  $added_val_usr2 = New value;
  $added_val_usr2->ids = $phr_lst->ids;
  $added_val_usr2->usr = $usr2;
  $added_val_usr2->load($debug-1);
  $result = $added_val_usr2->number;
  $target = '987654321';
  $exe_start_time = test_show_result(', value->load the value previous updated for "'.$wrd_lst->name().'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // test adding a value
  // if the word is not used, the user can add or remove words
  // if a value is used adding adding another word should create a new value
  // but if the new value with the added word already exists the values should be merged after a confirmation by the user

  // test to remove a word from the value
  /*$added_val = New value;
  $added_val->id = $added_val_id;
  $added_val->usr = $usr;
  $added_val->load($debug-1);
  $wrd_to_del = load_word(TW_CHF, $debug-1);
  $result = $added_val->del_wrd($wrd_to_del->id, $debug-1);
  $wrd_lst = $added_val->wrd_lst;
  $result = $wrd_lst->does_contain(TW_CHF, $debug-1);
  $target = false;
  $exe_start_time = test_show_result(', value->add_wrd has "'.TW_CHF.'" been removed from the word list of the value', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // test to link an additional word to a value
  $added_val = New value;
  $added_val->id = $added_val_id;
  $added_val->usr = $usr;
  $added_val->load($debug-1);
  $wrd_to_add = load_word(TW_EUR, $debug-1);
  $result = $added_val->add_wrd($wrd_to_add->id, $debug-1);
  // load word list
  $wrd_lst = $added_val->wrd_lst;
  // does the word list contain TW_EUR
  $result = $wrd_lst->does_contain(TW_EUR, $debug-1);
  $target = true;
  $exe_start_time = test_show_result(', value->add_wrd has "'.TW_EUR.'" been added to the word list of the value', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  */


}

?>
