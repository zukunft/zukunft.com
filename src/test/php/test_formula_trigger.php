<?php 

/*

  test_formula_trigger.php - TESTing of the trigger for FORMULAS
  ------------------------
  

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

function run_formula_trigger_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the formula calculation triggers</h2><br>";

  // prepare the calculation trigger test
  $phr_lst1 = New phrase_list;
  $phr_lst1->usr = $usr;
  $phr_lst1->add_name(TW_ADD_RENAMED);
  $phr_lst1->add_name(TW_SALES);
  $phr_lst1->add_name(TW_CHF);
  $phr_lst1->add_name(TW_MIO);
  $phr_lst2 = clone $phr_lst1;
  $phr_lst1->add_name(TW_2016);
  $phr_lst1->load($debug-1);
  $phr_lst2->add_name(TW_2017);
  $phr_lst2->load($debug-1);
  $frm = load_formula(TF_INCREASE, $debug-1);

  // add a number to the test word
  $val_add1 = New value;
  $val_add1->ids = $phr_lst1->ids;
  $val_add1->number = TV_TEST_SALES_2016;
  $val_add1->usr = $usr;
  $result = $val_add1->save($debug-1);
  // add a second number to the test word
  $val_add2 = New value;
  $val_add2->ids = $phr_lst2->ids;
  $val_add2->number = TV_TEST_SALES_2017;
  $val_add2->usr = $usr;
  $result = $val_add2->save($debug-1);

  // check if the first number have been save correctly
  $added_val = New value;
  $added_val->ids = $phr_lst1->ids;
  $added_val->usr = $usr;
  $added_val->load($debug-1);
  $result = $added_val->number;
  $target = TV_TEST_SALES_2016;
  $exe_start_time = test_show_result(', value->check added test value for "'.$phr_lst1->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  // check if the second number have been save correctly
  $added_val2 = New value;
  $added_val2->ids = $phr_lst2->ids;
  $added_val2->usr = $usr;
  $added_val2->load($debug-1);
  $result = $added_val2->number;
  $target = TV_TEST_SALES_2017;
  $exe_start_time = test_show_result(', value->check added test value for "'.$phr_lst2->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // check if requesting the best number for the first number returns a useful value
  $best_val = New value;
  $best_val->ids = $phr_lst1->ids;
  $best_val->usr = $usr;
  $best_val->load_best($debug-1);
  $result = $best_val->number;
  $target = TV_TEST_SALES_2016;
  $exe_start_time = test_show_result(', value->check best value for "'.$phr_lst1->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  // check if requesting the best number for the second number returns a useful value
  $best_val2 = New value;
  $best_val2->ids = $phr_lst2->ids;
  $best_val2->usr = $usr;
  $best_val2->load_best($debug-1);
  $result = $best_val2->number;
  $target = TV_TEST_SALES_2017;
  $exe_start_time = test_show_result(', value->check best value for "'.$phr_lst2->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // calculate the increase and check the result
  $fv_lst = $frm->calc($phr_lst2, 0, $debug-1);
  if (count($fv_lst) > 0) {
    $fv = $fv_lst[0];
    $result = trim($fv->display(0, $debug-1));
  } else {
    $result = '';
  }
  $target = TV_TEST_SALES_INCREASE_2017_FORMATTED;
  $exe_start_time = test_show_result(', formula result for '.$frm->dsp_id().' from '.$phr_lst1->dsp_id().' to '.$phr_lst2->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

  // remove the test values
  $val_add1->del($debug-1);
  $val_add2->del($debug-1);

  // change the second number and test if the result has been updated
  // a second user changes the value back to the originalvalue and check if for the second number the result is updated
  // check if the result for the first user is not changed
  // the first user also changes back the value to the original value and now the values for both user should be the same

}

?>
