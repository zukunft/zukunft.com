<?php 

/*

  test.php - for internal code consistency TESTing
  --------
  
  executes all class methods and all functions once 
  - in case of errors in the methods automatically a ticket is opened the the table sys_log
    with zukunft.com/error_update.php the tickets can be view and closed
  - and compares the result with the expected result
    in case of an unexpected result also a ticket is created
  
  ToDo:
  check the usage of "old" functions
  

  used functions
  ----
  
  zu_test_exe_time    - show the execution time for the last test and create a warning if it took too long
  zu_test_show_result - simply to display the function test result
  zu_test_show_db_id  - to get a database id because this may differ from instance to instance


zukunft.com - calc with words

copyright 1995-2018 by zukunft.com AG, Zurich

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

// libraries that can be dismissed, but still used for test.php
include_once 'zu_lib_word_dsp.php';                   if ($debug > 9) { echo 'lib word display loaded<br>'; }
include_once 'zu_lib_sql.php';                        if ($debug > 9) { echo 'lib sql loaded<br>'; }
include_once 'zu_lib_link.php';                       if ($debug > 9) { echo 'lib link loaded<br>'; }
include_once 'zu_lib_sql_naming.php';                 if ($debug > 9) { echo 'lib sql naming loaded<br>'; }
include_once 'zu_lib_value.php';                      if ($debug > 9) { echo 'lib value loaded<br>'; }
include_once 'zu_lib_word.php';                       if ($debug > 9) { echo 'lib word loaded<br>'; }
include_once 'zu_lib_word_db.php';                    if ($debug > 9) { echo 'lib word database link loaded<br>'; }
include_once 'zu_lib_calc.php';                       if ($debug > 9) { echo 'lib calc loaded<br>'; }
include_once 'zu_lib_value_db.php';                   if ($debug > 9) { echo 'lib value database link loaded<br>'; }
include_once 'zu_lib_value_dsp.php';                  if ($debug > 9) { echo 'lib value display loaded<br>'; }
include_once 'zu_lib_user.php';                       if ($debug > 9) { echo 'lib user loaded<br>'; }
include_once 'zu_lib_html.php';                       if ($debug > 9) { echo 'lib html loaded<br>'; }

define("TEST_USER_ID",     "1");   
define("TEST_USER_ID2",    "2");   

// switch for the email testing
define("TEST_EMAIL",       FALSE);   

// the basic test record for doing the pre check
// the word Company must always be 1!
define("TEST_WORD",         "Company");   
define("TEST_WORD_ID",      "1");   
define("TEST_WORD_PLURAL",  "Companies");   

// some test words used for testing
define("TW_ABB",     "ABB");   
define("TW_NESN",    "Nestlé");   
define("TW_VESTAS",  "Vestas");  
define("TW_ZH",      "Zurich");   
define("TW_ZH_INS",  "Zurich Insurance");  
define("TW_SALES",   "Sales");   
define("TW_CHF",     "CHF");   
define("TW_EUR",     "EUR");   
define("TW_YEAR",    "Year");   
define("TW_2012",    "2012");   
define("TW_2013",    "2013");   
define("TW_2014",    "2014");   
define("TW_2015",    "2015");   
define("TW_2016",    "2016");   
define("TW_2017",    "2017");   
define("TW_MIO",     "million");   
define("TW_M",       "mio");   
define("TW_PCT",     "percent");   
define("TW_CF",      "cash flow statement");   
define("TW_TAX",     "Income taxes");   

// some formula parameter used for testing
define("TF_INCREASE","increase");   

// settings for add, change and deletion tests
// these names should not exist in the database
define("TW_ADD",                  "Test Company");   
define("TW_ADD_RENAMED",          "Company Test");   
define("TF_ADD",                  "Test Formula");   
define("TF_ADD_RENAMED",          "Formula Test");   
define("TM_ADD",                  "Test Mask");   
define("TM_ADD_RENAMED",          "Mask Test");   
define("TC_ADD",                  "Test Mask Component");   
define("TC_ADD_RENAMED",          "Mask Component Test");   

// settings for add, change and deletion tests
// these names should not exist in the database
define("TEST_WORD_ADD",        "Test Company");   
define("TEST_WORD_RENAMED",    "Company Test");   
define("TEST_FORMULA_ADD",     "Test Formula");   
define("TEST_FORMULA_RENAMED", "Formula Test");   
define("TEST_VIEW_ADD",        "Test Mask");   
define("TEST_VIEW_RENAMED",    "Mask Test");   
define("TEST_VIEW_ENTRY_ADD",     "Test Mask Component");   
define("TEST_VIEW_ENTRY_RENAMED", "Mask Component Test");   

// max time expected for each function execution
define("TIMEOUT_LIMIT", 0.03);   
define("TIMEOUT_LIMIT_PAGE",     0.1);  // time limit for complete webpage
define("TIMEOUT_LIMIT_PAGE_SEMI",0.6);  // time limit for complete webpage
define("TIMEOUT_LIMIT_PAGE_LONG",1.2);  // time limit for complete webpage
define("TIMEOUT_LIMIT_DB",       0.2);  // time limit for database modification functions
define("TIMEOUT_LIMIT_DB_MULTI", 0.6);  // time limit for many database modifications
define("TIMEOUT_LIMIT_LONG",     3);    // time limit for complex functions


if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 9) { echo 'libs loaded<br>'; }
$link = zu_start("start test.php", "", $debug-10);

// system test user to simulate the user sandbox
// e.g. a value owned by the first user cannot be adjusted by the second user
// instead a user specific value is created
$usr = New user;
$usr->id = TEST_USER_ID;
$usr->load_test_user($debug-1);

$usr2 = New user;
$usr2->id = TEST_USER_ID2;
$usr2->load_test_user($debug-1);

$start_time = microtime(true);
$exe_start_time = $start_time;

$error_counter = 0;
$timeout_counter = 0;

  function zu_test_show_result($test_text, $target, $result, $exe_start_time, $exe_max_time, $comment) {
    global $error_counter;
    global $timeout_counter;
    global $total_tests;
    
    $result = zu_test_uncolor($result);
    if (is_numeric($result) && is_numeric($target)) {
      $result = round($result,7);
      $target = round($target,7);
    }
    // check if executed in a reasonable time and if the result is fine
    $new_start_time = microtime(true);
    $since_start = $new_start_time - $exe_start_time;
    if ($result == $target) {
      if ($since_start > $exe_max_time) {
        echo "<font color=orange>TIMEOUT</font>" .$test_text;
        $timeout_counter++;
      } else {
        echo "<font color=green>OK</font>" .$test_text;
      }
    } else {
      echo "<font color=red>Error</font>".$test_text;
      $error_counter++;
      // todo: create a ticket
    }
    if (is_array($target)) {
      echo " should be \"".implode(",",$target)."\"";
    } else {
      echo " should be \"".$target."\"";
    }
    if ($result == $target) {
      echo " and it is ";
    } else {
      echo ", but it is ";
    }
    if (is_array($result)) {
      if (is_array($result[0])) {
        echo "\"";
        foreach ($result AS $result_item) {
          if ($result_item <> $result[0]) {
            echo ",";
          }
          echo implode(":",$result_item);
        }
        echo "\"";
      } else {
        echo "\"".implode(",",$result)."\"";
      }
    } else {
      echo "\"".$result."\"";
    }
    if ($comment <> '') {
      echo ' ('.$comment.')';
    }
    
    // show the execution time
    echo ', took ';
    echo round($since_start,4).' seconds';

    echo "<br>";
    flush();
    $total_tests++;
    return $new_start_time;
  }

  // remove color setting from the result to reduce confusion by missleading colors
  function zu_test_uncolor($result) {
    $result = str_replace('<font color="red">', '', $result);
    $result = str_replace('<font class="user_specific">', '', $result);
    $result = str_replace('</font>', '', $result);
    return $result;
  }

  // external string diff only for testing
  function zu_str_diff($from, $to)
  {
      $diffValues = array();
      $diffMask = array();

      $dm = array();
      $n1 = count($from);
      $n2 = count($to);

      for ($j = -1; $j < $n2; $j++) $dm[-1][$j] = 0;
      for ($i = -1; $i < $n1; $i++) $dm[$i][-1] = 0;
      for ($i = 0; $i < $n1; $i++)
      {
          for ($j = 0; $j < $n2; $j++)
          {
              if ($from[$i] == $to[$j])
              {
                  $ad = $dm[$i - 1][$j - 1];
                  $dm[$i][$j] = $ad + 1;
              }
              else
              {
                  $a1 = $dm[$i - 1][$j];
                  $a2 = $dm[$i][$j - 1];
                  $dm[$i][$j] = max($a1, $a2);
              }
          }
      }

      $i = $n1 - 1;
      $j = $n2 - 1;
      while (($i > -1) || ($j > -1))
      {
          if ($j > -1)
          {
              if ($dm[$i][$j - 1] == $dm[$i][$j])
              {
                  $diffValues[] = $to[$j];
                  $diffMask[] = 1;
                  $j--;  
                  continue;              
              }
          }
          if ($i > -1)
          {
              if ($dm[$i - 1][$j] == $dm[$i][$j])
              {
                  $diffValues[] = $from[$i];
                  $diffMask[] = -1;
                  $i--;
                  continue;              
              }
          }
          {
              $diffValues[] = $from[$i];
              $diffMask[] = 0;
              $i--;
              $j--;
          }
      }    

      $diffValues = array_reverse($diffValues);
      $diffMask = array_reverse($diffMask);

      return array('values' => $diffValues, 'view' => $diffMask);
  }  
  
$start_time = microtime(true);
$exe_start_time = $start_time;
  
echo "<h1>Consistency check of the \"zukunft.com\" code</h1><br>";

if (TEST_EMAIL == TRUE) {
  echo "<h2>Test mail sending</h2><br>";
  $mail_to      = 'timon@zukunft.com';
  $mail_subject = 'Test mailto';
  $mail_body    = 'Hello';
  $mail_header  = 'From: heang@zukunft.com' . "\r\n" .
                  'Reply-To: heang@zukunft.com' . "\r\n" .
                  'X-Mailer: PHP/' . phpversion();

  mail($mail_to, $mail_subject, $mail_body, $mail_header);
}


echo "<h2>Focused testing</h2><br>";



// test getting the "best guess" formula value
// e.g. if Nestlé,Earnings per share,2016 is requested, but there is only a value for Nestlé,Earnings per share,2016,CHF,million get it
//      based (7452)
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name('Nestlé');
$phr_lst->add_name('Profit attributable to shareholders scaled');
$phr_lst->add_name(TW_2016);
$phr_lst->load($debug-1);
$fv_best_guess = New formula_value;
$fv_best_guess->phr_lst = $phr_lst;
$fv_best_guess->usr = $usr;
$fv_best_guess->load($debug-1);
$result = $fv_best_guess->value;
$target = '-3995000000';
$exe_start_time = zu_test_show_result(', formula_value->load the best guess for '.$phr_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

/*
  test the recalculation logic by
  1. create a new word that is used to save the test values
  2. assing a value to the new word
  3. calculate a result based on the added value
  4. if the original user (user 1) changes the value, the result should be updated
  5. if another user (user 2) changes a value, the result only for this user should be updated and should be shown in green
  6. the result for the original user should stay as it is
  7. if the original user changes the value to the same amount as "user 2" the result should be updated
  8. and the result for the "user 2" should be the same, but no longer green
  9. check updating of depending results
*/ 


// 1. test the creation of a new word
$wrd_add = New word;
$wrd_add->name = TW_ADD;
$wrd_add->usr = $usr;
$result = $wrd_add->save($debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', word->save for "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

// check if the word can be renamed
$wrd_add->name = TW_ADD_RENAMED;
$result = $wrd_add->save($debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', word->save rename "'.TW_ADD.'" to "'.TW_ADD_RENAMED.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

// prepare the calculation trigger test by creating the phrase lists and the formula element
$phr_lst_usr1_chf16 = New phrase_list;
$phr_lst_usr1_chf16->usr = $usr;
$phr_lst_usr1_chf16->add_name(TW_ADD_RENAMED);
$phr_lst_usr1_chf16->add_name(TW_SALES);
$phr_lst_usr1_chf16->add_name(TW_MIO);
$phr_lst_usr1_chf17 = clone $phr_lst_usr1_chf16;
$phr_lst_usr1_eur16 = clone $phr_lst_usr1_chf16;
$phr_lst_usr1_eur17 = clone $phr_lst_usr1_chf16;
$phr_lst_usr1_chf16->add_name(TW_CHF);
$phr_lst_usr1_chf16->add_name(TW_2016);
$phr_lst_usr1_chf16->load($debug-1);
$phr_lst_usr1_chf17->add_name(TW_CHF);
$phr_lst_usr1_chf17->add_name(TW_2017);
$phr_lst_usr1_chf17->load($debug-1);
$phr_lst_usr1_eur16->add_name(TW_EUR);
$phr_lst_usr1_eur16->add_name(TW_2016);
$phr_lst_usr1_eur16->load($debug-1);
$phr_lst_usr1_eur17->add_name(TW_EUR);
$phr_lst_usr1_eur17->add_name(TW_2017);
$phr_lst_usr1_eur17->load($debug-1);

// same for the second user
$phr_lst_usr2_chf16 = New phrase_list;
$phr_lst_usr2_chf16->usr = $usr2;
$phr_lst_usr2_chf16->add_name(TW_ADD_RENAMED);
$phr_lst_usr2_chf16->add_name(TW_SALES);
$phr_lst_usr2_chf16->add_name(TW_MIO);
$phr_lst_usr2_chf17 = clone $phr_lst_usr2_chf16;
$phr_lst_usr2_eur16 = clone $phr_lst_usr2_chf16;
$phr_lst_usr2_eur17 = clone $phr_lst_usr2_chf16;
$phr_lst_usr2_chf16->add_name(TW_CHF);
$phr_lst_usr2_chf16->add_name(TW_2016);
$phr_lst_usr2_chf16->load($debug-1);
$phr_lst_usr2_chf17->add_name(TW_CHF);
$phr_lst_usr2_chf17->add_name(TW_2017);
$phr_lst_usr2_chf17->load($debug-1);
$phr_lst_usr2_eur16->add_name(TW_EUR);
$phr_lst_usr2_eur16->add_name(TW_2016);
$phr_lst_usr2_eur16->load($debug-1);
$phr_lst_usr2_eur17->add_name(TW_EUR);
$phr_lst_usr2_eur17->add_name(TW_2017);
$phr_lst_usr2_eur17->load($debug-1);
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_INCREASE;
$frm->load($debug-1);
$frm2 = New formula;
$frm2->usr = $usr2;
$frm2->name = TF_INCREASE;
$frm2->load($debug-1);

// 2. add the numbers to the test words
// add Sales for CHF 2016 
$val_add_chf16 = New value;
$val_add_chf16->ids = $phr_lst_usr1_chf16->ids;
$val_add_chf16->number = 1234;
$val_add_chf16->usr = $usr;
$result = $val_add_chf16->save($debug-1);
// check if the number has been added correctly
$val_chk_chf16 = New value;
$val_chk_chf16->ids = $phr_lst_usr1_chf16->ids;
$val_chk_chf16->usr = $usr;
$val_chk_chf16->load($debug-1);
$result = $val_chk_chf16->number;
$target = 1234;
$exe_start_time = zu_test_show_result(', save the value for '.$phr_lst_usr1_chf16->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// change the year
$val_add_chf17 = New value;
$val_add_chf17->ids = $phr_lst_usr1_chf17->ids;
$val_add_chf17->number = 1345;
$val_add_chf17->usr = $usr;
$result = $val_add_chf17->save($debug-1);
// check if the second number has been updated correctly
$val_chk_chf17 = New value;
$val_chk_chf17->ids = $phr_lst_usr1_chf17->ids;
$val_chk_chf17->usr = $usr;
$val_chk_chf17->load($debug-1);
$result = $val_chk_chf17->number;
$target = 1345;
$exe_start_time = zu_test_show_result(', save the value for '.$phr_lst_usr1_chf17->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// add Sales for EUR 2016 
$val_add_eur16 = New value;
$val_add_eur16->ids = $phr_lst_usr1_eur16->ids;
$val_add_eur16->number = 1456;
$val_add_eur16->usr = $usr;
$result = $val_add_eur16->save($debug-1);
// check if the second number has been added correctly
$val_chk_eur16 = New value;
$val_chk_eur16->ids = $phr_lst_usr1_eur16->ids;
$val_chk_eur16->usr = $usr;
$val_chk_eur16->load($debug-1);
$result = $val_chk_eur16->number;
$target = 1456;
$exe_start_time = zu_test_show_result(', save the value for '.$phr_lst_usr1_eur16->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// change the year
$val_add_eur17 = New value;
$val_add_eur17->ids = $phr_lst_usr1_eur17->ids;
$val_add_eur17->number = 1567;
$val_add_eur17->usr = $usr;
$result = $val_add_eur17->save($debug-1);
// check if the second number has been updated correctly
$val_chk_eur17 = New value;
$val_chk_eur17->ids = $phr_lst_usr1_eur17->ids;
$val_chk_eur17->usr = $usr;
$val_chk_eur17->load($debug-1);
$result = $val_chk_eur17->number;
$target = 1567;
$exe_start_time = zu_test_show_result(', save the value for  '.$phr_lst_usr1_eur17->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// calculate the chf increase and check the result
$fv_lst = $frm->calc($phr_lst_usr1_chf17, $debug-1);
if (count($fv_lst) > 0) {
  $fv = $fv_lst[0];
  $result = trim($fv->display($back, $debug-1));
} else {
  $result = '';
}
$target = '9 %';
$exe_start_time = zu_test_show_result(', formula result for '.$frm->dsp_id().' from '.$phr_lst_usr1_chf16->dsp_id().' to '.$phr_lst_usr1_chf17->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// calculate the eur increase and check the result
$fv_lst = $frm->calc($phr_lst_usr1_eur17, $debug-1);
if (count($fv_lst) > 0) {
  $fv = $fv_lst[0];
  $result = trim($fv->display($back, $debug-1));
} else {
  $result = '';
}
$target = '7.62 %';
$exe_start_time = zu_test_show_result(', formula result for '.$frm->dsp_id().' from '.$phr_lst_usr1_eur16->dsp_id().' to '.$phr_lst_usr1_eur17->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// change the second number and test if the result has been updated
$val_add_eur16->number = 1512;
$result = $val_add_eur16->save($debug-1);
// check if the second number has been updated correctly
$val_chk_eur16 = New value;
$val_chk_eur16->ids = $phr_lst_usr1_eur16->ids;
$val_chk_eur16->usr = $usr;
$val_chk_eur16->load($debug-1);
$result = $val_chk_eur16->number;
$target = 1512;
$exe_start_time = zu_test_show_result(', change the value for '.$phr_lst_usr1_eur16->name().' to ', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);
// check if the calculated result has also been updated
if (isset($fv)) {
  $fv->load($debug-1);
  $result = trim($fv->display_linked($back, $debug-30));
  $target = '<a href="/http/formula_result.php?id='.$fv->id.'&phrase=6&group='.$fv->phr_grp_id.'&back=">3.64 %</a>';
  $exe_start_time = zu_test_show_result(', the updated formula result for '.$frm->dsp_id().' from '.$phr_lst_usr1_chf16->dsp_id().' to '.$phr_lst_usr1_eur16->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);
}  

// calculate the increase again and check the result
$fv_lst = $frm->calc($phr_lst_usr1_eur17, $debug-1);
if (count($fv_lst) > 0) {
  $fv = $fv_lst[0];
  $result = trim($fv->display_linked($back, $debug-30));
} else {
  $result = '';
}
$target = '<a href="/http/formula_result.php?id='.$fv->id.'&phrase='.$wrd_add->id.'&group='.$fv->phr_grp_id.'&back=">3.64 %</a>';
$exe_start_time = zu_test_show_result(', the recalculated formula result for '.$fv->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);
//$exe_start_time = zu_test_show_result(', formula result for '.$frm->dsp_id().' from '.$phr_lst_usr1_chf16->dsp_id().' to '.$phr_lst_usr1_eur16->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// the second user changes the EUR 2016 number
$val_add_eur16->number = 1483;
$val_add_eur16->usr = $usr2;
$result = $val_add_eur16->save($debug-1);
// check if the EUR 2016 number for the second user has been updated correctly
$val_chk2_eur16 = New value;
$val_chk2_eur16->ids = $phr_lst_usr1_eur16->ids;
$val_chk2_eur16->usr = $usr2;
$val_chk2_eur16->load($debug-1);
$result = $val_chk2_eur16->number;
$target = 1483;
$exe_start_time = zu_test_show_result(', another user changed the value for '.$val_chk2_eur16->name().' to ', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);
// check if the second number for the first user is still the same
$val_chk_eur16 = New value;
$val_chk_eur16->ids = $phr_lst_usr1_eur16->ids;
$val_chk_eur16->usr = $usr;
$val_chk_eur16->load($debug-1);
$result = $val_chk_eur16->number;
$target = 1512;
$exe_start_time = zu_test_show_result(', for the original user the value for '.$phr_lst_usr1_eur16->name().' should still be ', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// check if the result for the second user has been updated
$fv_lst2 = $frm2->calc($phr_lst_usr2_eur17, $debug-1);
if (count($fv_lst2) > 0) {
  $fv2 = $fv_lst2[0];
  $result = trim($fv2->display_linked($back, $debug-30));
} else {
  $result = '';
}
$target = '<a href="/http/formula_result.php?id='.$fv2->id.'&phrase='.$wrd_add->id.'&group='.$fv2->phr_grp_id.'&back=">5.66 %</a>';
$exe_start_time = zu_test_show_result(', formula result for '.$fv2->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// check if the result for the first user is not changed
$fv_lst = $frm->calc($phr_lst_usr1_eur17, $debug-1);
if (count($fv_lst) > 0) {
  $fv = $fv_lst[0];
  $result = trim($fv->display_linked($back, $debug-30));
} else {
  $result = '';
}
$target = '<a href="/http/formula_result.php?id='.$fv->id.'&phrase='.$wrd_add->id.'&group='.$fv->phr_grp_id.'&back=">3.64 %</a>';
$exe_start_time = zu_test_show_result(', formula result for '.$fv->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// a second user changes the value back to the originalvalue and check if for the second number the result is updated
// the first user also changes back the value to the original value and now the values for both user should be the same


// remove the test values
$result = $val_add_chf16->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', value->del '.$phr_lst_usr1_chf16->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
$result = $val_add_chf17->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', value->del '.$phr_lst_usr1_chf17->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
$result = $val_add_eur16->del($debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', value->del '.$phr_lst_usr1_eur16->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
$result = $val_add_eur17->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', value->del '.$phr_lst_usr1_eur17->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);


// check if the word can be renamed
$result = $wrd_add->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', word->del ', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);



// to to: test changing the phrase list
// if no user has used or changed the value, the phrase list should simply be changed
// if a user has changed or used the value, a new value should be created




echo "<br>";
echo "end of quick test";
echo "<br>";

// Free resultset
mysql_free_result($result);

// Closing connection
zu_end($link, $debug);
?>
