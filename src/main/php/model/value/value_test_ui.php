<?php 

/*

  value_test_ui.php - TESTing of the VALUE User Interface class
  -----------------
  

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

function run_value_ui_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the value frontend scripts (e.g. /value_add.php)</h2><br>";

  // prepare the frontend testing 
  $phr_lst_added = New phrase_list;
  $phr_lst_added->usr = $usr;
  $phr_lst_added->add_name(TW_SALES);
  $phr_lst_added->add_name(TW_CHF);
  $phr_lst_added->add_name(TW_MIO);
  $phr_lst_added->add_name(TW_2014);
  $phr_lst_abb = clone $phr_lst_added;
  $phr_lst_abb->add_name(TW_ABB);
  $phr_lst_abb->load($debug-1);
  $phr_lst_added->add_name(TW_ADD_RENAMED);
  $phr_lst_added->load($debug-1);
  $val_added = New value;
  $val_added->ids = $phr_lst_added->ids;
  $val_added->usr = $usr;
  $val_added->load($debug-1);
  $val_ABB = New value;
  $val_ABB->ids = $phr_lst_abb->ids;
  $val_ABB->usr = $usr;
  $val_ABB->load($debug-1);

  // call the add value page and check if at least some basic keywords are returned
  $result = file_get_contents('https://zukunft.com/http/value_add.php?back='.$back.$phr_lst_added->id_url_long().'');
  $target = TW_ADD_RENAMED;
  $exe_start_time = test_show_contains(', frontend value_add.php '.$result.' contains at least '.TW_ADD_RENAMED, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  $result = file_get_contents('https://zukunft.com/http/value_add.php?back='.$back.$phr_lst_abb->id_url_long().'');
  $target = TW_ABB;
  $exe_start_time = test_show_contains(', frontend value_add.php '.$result.' contains at least '.TW_ABB, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  // test the edit value frontend
  $result = file_get_contents('https://zukunft.com/http/value_edit.php?id='.$val_added->id.'&back='.$back.'');
  $target = TW_ADD_RENAMED;
  $exe_start_time = test_show_contains(', frontend value_edit.php '.$result.' contains at least '.TW_ADD_RENAMED, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  $result = file_get_contents('https://zukunft.com/http/value_edit.php?id='.$val_ABB->id.'&back='.$back.'');
  $target = TW_ABB;
  $exe_start_time = test_show_contains(', frontend value_edit.php '.$result.' contains at least '.TW_ABB, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  // test the del value frontend
  $result = file_get_contents('https://zukunft.com/http/value_del.php?id='.$val_added->id.'&back='.$back.'');
  $target = TW_ADD_RENAMED;
  $exe_start_time = test_show_contains(', frontend value_del.php '.$result.' contains at least '.TW_ADD_RENAMED, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

  $result = file_get_contents('https://zukunft.com/http/value_del.php?id='.$val_ABB->id.'&back='.$back.'');
  $target = TW_ABB;
  $exe_start_time = test_show_contains(', frontend value_del.php '.$result.' contains at least '.TW_ABB, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


  echo "<br><br><h2>Test the value list class (classes/value_list.php)</h2><br>";

  // check the database consistency for all values
  $val_lst = New value_list;
  $val_lst->usr = $usr;
  $result = $val_lst->check_all($debug-1);
  $target = '';
  $exe_start_time = test_show_result(', value_list->check_all', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

  // test get a single value from a value list by group and time
  // get all value for ABB
  $wrd = New word_dsp;
  $wrd->name = TW_ABB;
  $wrd->usr = $usr;
  $wrd->load($debug-1);
  $val_lst = $wrd->val_lst($debug-1);
  // build the phrase list to select the value Sales for 2014
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load($debug-1);
  $wrd_time = $wrd_lst->assume_time($debug-1);
  $grp = $wrd_lst->get_grp($debug-1);
  $result = $grp->id;
  $target = '2116';
  $exe_start_time = test_show_result(', word_list->get_grp for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  $val = $val_lst->get_by_grp($grp, $wrd_time, $debug-1);
  $result = $val->number;
  $target = TV_ABB_SALES_2014;
  $exe_start_time = test_show_result(', value_list->get_by_grp for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

  // ... get all times of the ABB values
  $time_lst = $val_lst->time_lst($debug-1);
  $wrd_2014 = New word_dsp;
  $wrd_2014->name = TW_2014;
  $wrd_2014->usr = $usr;
  $wrd_2014->load($debug-1);
  if ($time_lst->does_contain($wrd_2014, $debug-1)) {
    $result = true;
  } else {
    $result = false;
  }
  $target = true;
  $exe_start_time = test_show_result(', value_list->time_lst is '.$time_lst->name().', which includes '.$wrd_2014->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
      
  // ... and filter by times
  $time_lst = New word_list;
  $time_lst->usr = $usr;
  $time_lst->add_name(TW_2016);
  $time_lst->add_name(TW_2013);
  $time_lst->load($debug-1);
  $used_value_lst = $val_lst->filter_by_time($time_lst, $debug-1);
  $used_time_lst = $used_value_lst->time_lst($debug-1);
  if ($time_lst->does_contain($wrd_2014, $debug-1)) {
    $result = true;
  } else {
    $result = false;
  }
  $target = false;
  $exe_start_time = test_show_result(', value_list->time_lst is '.$used_time_lst->name().', which does not include '.$wrd_2014->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... but not 2016
  $wrd_2016 = New word_dsp;
  $wrd_2016->name = TW_2016;
  $wrd_2016->usr = $usr;
  $wrd_2016->load($debug-1);
  if ($time_lst->does_contain($wrd_2016, $debug-1)) {
    $result = true;
  } else {
    $result = false;
  }
  $target = true;
  $exe_start_time = test_show_result(', value_list->filter_by_phrase_lst is '.$used_time_lst->name().', but includes '.$wrd_2016->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... and filter by phrases
  $sector_lst = New word_list;
  $sector_lst->usr = $usr;
  $sector_lst->add_name('Low Voltage Products');
  $sector_lst->add_name('Power Products');
  $sector_lst->load($debug-1);
  $phr_lst = $sector_lst->phrase_lst($debug-1);
  $used_value_lst = $val_lst->filter_by_phrase_lst($phr_lst, $debug-1);
  $used_phr_lst = $used_value_lst->phr_lst($debug-1);
  $wrd_auto = New word_dsp;
  $wrd_auto->name = 'Discrete Automation and Motion';
  $wrd_auto->usr = $usr;
  $wrd_auto->load($debug-1);
  if ($used_phr_lst->does_contain($wrd_auto, $debug-1)) {
    $result = true;
  } else {
    $result = false;
  }
  $target = false;
  $exe_start_time = test_show_result(', value_list->filter_by_phrase_lst is '.$used_phr_lst->name().', which does not include '.$wrd_auto->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT); 

  // ... but not 2016
  $wrd_power = New word_dsp;
  $wrd_power->name = 'Power Products';
  $wrd_power->usr = $usr;
  $wrd_power->load($debug-1);
  if ($used_phr_lst->does_contain($wrd_power, $debug-1)) {
    $result = true;
  } else {
    $result = false;
  }
  $target = true;
  $exe_start_time = test_show_result(', value_list->filter_by_phrase_lst is '.$used_phr_lst->name().', but includes '.$wrd_power->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  echo "<br><br><h2>Test the value list display class (classes/value_list_display.php)</h2><br>";

  // test the value table
  $wrd = New word_dsp;
  $wrd->name = 'Nestlé';
  $wrd->usr = $usr;
  $wrd->load($debug-1);
  $wrd_col = New word_dsp;
  $wrd_col->name = TW_CF;
  $wrd_col->usr = $usr;
  $wrd_col->load($debug-1);
  $val_lst = New value_list_dsp;
  $val_lst->phr = $wrd->phrase($debug-1);
  $val_lst->usr = $usr;
  $result = $val_lst->dsp_table($wrd_col, $wrd->id, $debug-1);
  $target = TV_NESN_SALES_2016_FORMATTED;
  $exe_start_time = test_show_contains(', value_list_dsp->dsp_table for "'.$wrd->name.'" ('.$result.') contains '.$target.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);
  //$result = $val_lst->dsp_table($wrd_col, $wrd->id, $debug-1);
  //$target = zuv_table ($wrd->id, $wrd_col->id, $usr->id, $debug-1);
  //$exe_start_time = test_show_result(', value_list_dsp->dsp_table for "'.$wrd->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);


}

?>
