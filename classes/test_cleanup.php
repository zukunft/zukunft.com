<?php 

/*

  test_cleanup.php - TESTing cleanup to remove any remaining test records
  ---------------
  

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

function run_test_cleanup ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  global $added_val_id;
  global $added_val2_id;

  // make sure that all test elements are removed even if some tests have failed to have a clean setup for the next test
  echo "<br><br><h2>Cleanup the test</h2><br>";

  if ($added_val_id > 0) {
    // request to delete the added test value
    $added_val = New value;
    $added_val->id = $added_val_id;
    $added_val->usr = $usr;
    $added_val->load($debug-1);
    $result = $added_val->del($debug-1);
    $target = '11';
    $exe_start_time = test_show_result(', value->del test value for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  }

  if ($added_val2_id > 0) {
    // request to delete the added test value
    $added_val2 = New value;
    $added_val2->id = $added_val2_id;
    $added_val2->usr = $usr;
    $added_val2->load($debug-1);
    $result = $added_val2->del($debug-1);
    $target = '11';
    $exe_start_time = test_show_result(', value->del test value for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  }

  // secure cleanup the test views
  // todo: if a user has changed the view during the test, delete also the user views

  // load the test view
  $dsp = load_view(TM_ADD, $debug-1);
  if ($dsp->id <= 0) { $dsp = load_view(TM_ADD_RENAMED, $debug-1); }

  // load the test view for user 2
  $dsp_usr2 = load_view_usr(TM_ADD, $usr2, $debug-1);
  if ($dsp_usr2->id <= 0) { $dsp_usr2 = load_view_usr(TM_ADD_RENAMED, $usr2, $debug-1); }

  // load the first test view component
  $cmp = load_view_component(TC_ADD, $debug-1);
  if ($cmp->id <= 0) { $cmp = load_view_component(TC_ADD_RENAMED, $debug-1); }

  // load the first test view component for user 2
  $cmp_usr2 = load_view_component_usr(TC_ADD, $usr2, $debug-1);
  if ($cmp_usr2->id <= 0) { $cmp_usr2 = load_view_component_usr(TC_ADD_RENAMED, $usr2, $debug-1); }

  // load the second test view component
  $cmp2 = load_view_component(TC_ADD2, $debug-1);

  // load the second test view component for user 2
  $cmp2_usr2 = load_view_component_usr(TC_ADD2, $usr2, $debug-1);

  // check if the test components have been unlinked
  if ($dsp->id > 0 and $cmp->id > 0) {
    $result = $cmp->unlink($dsp, $debug-1);
    $target = '';
    $exe_start_time = test_show_result(', cleanup: unlink first component "'.$cmp->name.'" from "'.$dsp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
  }

  // check if the test components have been unlinked for user 2
  if ($dsp_usr2->id > 0 and $cmp_usr2->id > 0) {
    $result = $cmp_usr2->unlink($dsp_usr2, $debug-1);
    $target = '';
    $exe_start_time = test_show_result(', cleanup: unlink first component "'.$cmp_usr2->name.'" from "'.$dsp_usr2->name.'" for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
  }

  // unlink the second component
  // error at the moment: if the second user is still using the link, 
  // the seconde user does not get the owner 
  // instead a foreign key error happens
  if ($dsp->id > 0 and $cmp2->id > 0) {
    $result = $cmp2->unlink($dsp, $debug-1);
    $target = '';
    $exe_start_time = test_show_result(', cleanup: unlink second component "'.$cmp2->name.'" from "'.$dsp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
  }

  // unlink the second component for user 2
  if ($dsp_usr2->id > 0 and $cmp2_usr2->id > 0) {
    $result = $cmp2_usr2->unlink($dsp_usr2, $debug-1);
    $target = '';
    $exe_start_time = test_show_result(', cleanup: unlink second component "'.$cmp2_usr2->name.'" from "'.$dsp_usr2->name.'" for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
  }

  // request to delete the test view component
  if ($cmp->id > 0) {
    $result = $cmp->del($debug-1);
    $target = '111';
    //$target = '';
    $exe_start_time = test_show_result(', cleanup: del of first component "'.TC_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  }

  // request to delete the test view component for user 2
  if ($cmp_usr2->id > 0) {
    $result = $cmp_usr2->del($debug-1);
    $target = '';
    $exe_start_time = test_show_result(', cleanup: del of first component "'.TC_ADD.'" for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  }

  // request to delete the second added test view component
  if ($cmp2->id > 0) {
    $result = $cmp2->del($debug-1);
    $target = '11';
    //$target = '';
    $exe_start_time = test_show_result(', cleanup: del of second component "'.TC_ADD2.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  }

  // request to delete the second added test view component for user 2
  if ($cmp2_usr2->id > 0) {
    $result = $cmp2_usr2->del($debug-1);
    $target = '';
    $exe_start_time = test_show_result(', cleanup: del of second component "'.TC_ADD2.'" for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  }

  // request to delete the added test view
  if ($dsp->id > 0) {
    $result = $dsp->del($debug-1);
    $target = '111';
    $exe_start_time = test_show_result(', cleanup: del of view "'.TM_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  }

  // request to delete the added test view for user 2
  if ($dsp_usr2->id > 0) {
    $result = $dsp_usr2->del($debug-1);
    $target = '';
    $exe_start_time = test_show_result(', cleanup: del of view "'.TM_ADD.'" for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  }

  // request to delete the added test formula
  $frm = load_formula(TF_ADD, $debug-1);
  if ($frm->id > 0) {
    $result = $frm->del($debug-1);
    $target = '';
    $exe_start_time = test_show_result(', formula->del of "'.TF_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  }

  // request to delete the renamed test formula
  $frm = load_formula(TF_ADD_RENAMED, $debug-1);
  if ($frm->id > 0) {
    $result = $frm->del($debug-1);
    $target = '1111';
    $exe_start_time = test_show_result(', formula->del of "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  }

  // request to delete the added test word
  // todo: if a user has changed the word during the test, delete also the user words
  $wrd = load_word(TW_ADD, $debug-1);
  if ($wrd->id > 0) {
    $result = $wrd->del($debug-1);
    $target = '';
    $exe_start_time = test_show_result(', word->del of "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  }

  // request to delete the renamed test word
  $wrd = load_word(TW_ADD_RENAMED, $debug-1);
  if ($wrd->id > 0) {
    $result = $wrd->del($debug-1);
    $target = '11';
    $exe_start_time = test_show_result(', word->del of "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
  }

  // reset the auto increase id to avoid too high numbers just by testing
  $db_con = new mysql;         
  $db_con->usr_id = $usr->id;         

  // for values
  $sql_max = 'SELECT MAX(value_id) AS max_id FROM `values`;';
  $val_max_db = $db_con->get1($sql_max, $debug-1); 
  if ($val_max_db['max_id'] > 0) {
    $next_id = $val_max_db['max_id'] + 1;
    $sql = 'ALTER TABLE `values` auto_increment = '.$next_id.';';
    $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
  }
  echo 'Next database id for values: '.$next_id.'<br>';


  // for words
  $sql_max = 'SELECT MAX(word_id) AS max_id FROM words;';
  $wrd_max_db = $db_con->get1($sql_max, $debug-1); 
  if ($wrd_max_db['max_id'] > 0) {
    $next_id = $wrd_max_db['max_id'] + 1;
    $sql = 'ALTER TABLE `words` auto_increment = '.$next_id.';';
    $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
  }
  echo 'Next database id for words: '.$next_id.'<br>';


  // for formulas
  $sql_max = 'SELECT MAX(formula_id) AS max_id FROM formulas;';
  $wrd_max_db = $db_con->get1($sql_max, $debug-1); 
  if ($wrd_max_db['max_id'] > 0) {
    $next_id = $wrd_max_db['max_id'] + 1;
    $sql = 'ALTER TABLE `formulas` auto_increment = '.$next_id.';';
    $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
  }
  echo 'Next database id for formulas: '.$next_id.'<br>';

  // for formula links
  $sql_max = 'SELECT MAX(formula_link_id) AS max_id FROM formula_links;';
  $wrd_max_db = $db_con->get1($sql_max, $debug-1); 
  if ($wrd_max_db['max_id'] > 0) {
    $next_id = $wrd_max_db['max_id'] + 1;
    $sql = 'ALTER TABLE `formula_links` auto_increment = '.$next_id.';';
    $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
  }
  echo 'Next database id for formula_links: '.$next_id.'<br>';

  // for views
  $sql_max = 'SELECT MAX(view_id) AS max_id FROM views;';
  $wrd_max_db = $db_con->get1($sql_max, $debug-1); 
  if ($wrd_max_db['max_id'] > 0) {
    $next_id = $wrd_max_db['max_id'] + 1;
    $sql = 'ALTER TABLE `views` auto_increment = '.$next_id.';';
    $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
  }
  echo 'Next database id for views: '.$next_id.'<br>';

  // for view components
  $sql_max = 'SELECT MAX(view_component_id) AS max_id FROM view_components;';
  $wrd_max_db = $db_con->get1($sql_max, $debug-1); 
  if ($wrd_max_db['max_id'] > 0) {
    $next_id = $wrd_max_db['max_id'] + 1;
    $sql = 'ALTER TABLE `view_components` auto_increment = '.$next_id.';';
    $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
  }
  echo 'Next database id for view_components: '.$next_id.'<br>';

  // for view component links
  $sql_max = 'SELECT MAX(view_component_link_id) AS max_id FROM view_component_links;';
  $wrd_max_db = $db_con->get1($sql_max, $debug-1); 
  if ($wrd_max_db['max_id'] > 0) {
    $next_id = $wrd_max_db['max_id'] + 1;
    $sql = 'ALTER TABLE `view_component_links` auto_increment = '.$next_id.';';
    $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
  }
  echo 'Next database id for view_component_links: '.$next_id.'<br>';

}

?>
