<?php 

/*

  test_base.php - for internal code consistency TESTing the BASE functions and definitions
  -------------
  
  used functions
  ----
  
  test_exe_time    - show the execution time for the last test and create a warning if it took too long
  test_show_result - simply to display the function test result
  test_show_db_id  - to get a database id because this may differ from instance to instance


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

include_once '../lib/config.php';   if ($debug > 9) { echo 'lib config loaded<br>'; }

// load the testing functions
include_once '../classes/test_system.php';                if ($debug > 9) { echo 'system test loaded<br>'; }
include_once '../classes/test_db_link.php';               if ($debug > 9) { echo 'database link test loaded<br>'; }
include_once '../classes/test_lib.php';                   if ($debug > 9) { echo 'library test loaded<br>'; }
include_once '../classes/test_math.php';                  if ($debug > 9) { echo 'mathematical test loaded<br>'; }
include_once '../classes/test_user.php';                  if ($debug > 9) { echo 'user test loaded<br>'; }
include_once '../classes/test_word.php';                  if ($debug > 9) { echo 'word test loaded<br>'; }
include_once '../classes/test_word_ui.php';               if ($debug > 9) { echo 'word user interface test loaded<br>'; }
include_once '../classes/test_word_display.php';          if ($debug > 9) { echo 'word display test loaded<br>'; }
include_once '../classes/test_word_list.php';             if ($debug > 9) { echo 'word list test loaded<br>'; }
include_once '../classes/test_word_link.php';             if ($debug > 9) { echo 'word link test loaded<br>'; }
include_once '../classes/test_phrase.php';                if ($debug > 9) { echo 'phrase test loaded<br>'; }
include_once '../classes/test_phrase_list.php';           if ($debug > 9) { echo 'phrase list test loaded<br>'; }
include_once '../classes/test_phrase_group.php';          if ($debug > 9) { echo 'phrase group test loaded<br>'; }
include_once '../classes/test_phrase_group_list.php';     if ($debug > 9) { echo 'phrase group list test loaded<br>'; }
include_once '../classes/test_graph.php';                 if ($debug > 9) { echo 'graph test loaded<br>'; }
include_once '../classes/test_verb.php';                  if ($debug > 9) { echo 'verb test loaded<br>'; }
include_once '../classes/test_term.php';                  if ($debug > 9) { echo 'term test loaded<br>'; }
include_once '../classes/test_value.php';                 if ($debug > 9) { echo 'value test loaded<br>'; }
include_once '../classes/test_value_ui.php';              if ($debug > 9) { echo 'value user interface test loaded<br>'; }
include_once '../classes/test_source.php';                if ($debug > 9) { echo 'source test loaded<br>'; }
include_once '../classes/test_expression.php';            if ($debug > 9) { echo 'expression test loaded<br>'; }
include_once '../classes/test_formula.php';               if ($debug > 9) { echo 'formula test loaded<br>'; }
include_once '../classes/test_formula_ui.php';            if ($debug > 9) { echo 'formula user interface test loaded<br>'; }
include_once '../classes/test_formula_link.php';          if ($debug > 9) { echo 'formula link test loaded<br>'; }
include_once '../classes/test_formula_trigger.php';       if ($debug > 9) { echo 'formula trigger test loaded<br>'; }
include_once '../classes/test_formula_value.php';         if ($debug > 9) { echo 'formula value test loaded<br>'; }
include_once '../classes/test_formula_element.php';       if ($debug > 9) { echo 'formula element test loaded<br>'; }
include_once '../classes/test_formula_element_group.php'; if ($debug > 9) { echo 'formula element group test loaded<br>'; }
include_once '../classes/test_batch.php';                 if ($debug > 9) { echo 'batch job test loaded<br>'; }
include_once '../classes/test_view.php';                  if ($debug > 9) { echo 'view test loaded<br>'; }
include_once '../classes/test_view_component.php';        if ($debug > 9) { echo 'view component test loaded<br>'; }
include_once '../classes/test_view_component_link.php';   if ($debug > 9) { echo 'view component link test loaded<br>'; }
include_once '../classes/test_display.php';               if ($debug > 9) { echo 'display test loaded<br>'; }
include_once '../classes/test_import.php';                if ($debug > 9) { echo 'import test loaded<br>'; }
include_once '../classes/test_export.php';                if ($debug > 9) { echo 'export test loaded<br>'; }
include_once '../classes/test_legacy.php';                if ($debug > 9) { echo 'test legacy loaded<br>'; }
include_once '../classes/test_cleanup.php';               if ($debug > 9) { echo 'test cleanup loaded<br>'; }

// libraries that can be dismissed, but still used to compare the result with the resault of the legacy funtion
include_once '../lib/test/zu_lib_word_dsp.php';   if ($debug > 9) { echo 'lib word display loaded<br>'; }
include_once '../lib/test/zu_lib_sql.php';        if ($debug > 9) { echo 'lib sql loaded<br>'; }
include_once '../lib/test/zu_lib_link.php';       if ($debug > 9) { echo 'lib link loaded<br>'; }
include_once '../lib/test/zu_lib_sql_naming.php'; if ($debug > 9) { echo 'lib sql naming loaded<br>'; }
include_once '../lib/test/zu_lib_value.php';      if ($debug > 9) { echo 'lib value loaded<br>'; }
include_once '../lib/test/zu_lib_word.php';       if ($debug > 9) { echo 'lib word loaded<br>'; }
include_once '../lib/test/zu_lib_word_db.php';    if ($debug > 9) { echo 'lib word database link loaded<br>'; }
include_once '../lib/test/zu_lib_calc.php';       if ($debug > 9) { echo 'lib calc loaded<br>'; }
include_once '../lib/test/zu_lib_value_db.php';   if ($debug > 9) { echo 'lib value database link loaded<br>'; }
include_once '../lib/test/zu_lib_value_dsp.php';  if ($debug > 9) { echo 'lib value display loaded<br>'; }
include_once '../lib/test/zu_lib_user.php';       if ($debug > 9) { echo 'lib user loaded<br>'; }
include_once '../lib/test/zu_lib_html.php';       if ($debug > 9) { echo 'lib html loaded<br>'; }

// the fixed system user used for testing
define("TEST_USER_ID",          "1");   
define("TEST_USER_NAME",        "zukunft.com system batch job");   
define("TEST_USER_DESCRIPTION", "standard user view for all users");   
define("TEST_USER_ID2",         "2");   
define("TEST_USER_IP",          "66.249.64.95"); // used to check the blocking of an IP adress

/*

Setting that should be moved to the system config table

*/

// switch for the email testing
define("TEST_EMAIL",       FALSE); // if set to true an email will be send in case of errors and once a day an "everything fine" email is send

// the basic test record for doing the pre check
// the word "Company" is assumed to have the ID 1
define("TEST_WORD_ID",      "1");   
define("TEST_WORD",         "Company");   
define("TEST_WORD_PLURAL",  "Companies");   
define("TEST_TRIPLE_ID",    "1");   
define("TEST_TRIPLE",       "Company");   

// some test words used for testing
define("TW_ABB",       "ABB");   
define("TW_DAN",       "Danone");   
define("TW_NESN",      "Nestlé");   
define("TW_VESTAS",    "Vestas");  
define("TW_ZH",        "Zurich");   
define("TW_SALES",     "Sales");   
define("TW_SALES2",    "Revenues");   
define("TW_CHF",       "CHF");   
define("TW_EUR",       "EUR");   
define("TW_YEAR",      "Year");   
define("TW_2012",      "2012");   
define("TW_2013",      "2013");   
define("TW_2014",      "2014");   
define("TW_2015",      "2015");   
define("TW_2016",      "2016");   
define("TW_2017",      "2017");   
define("TW_2020",      "2020");   
define("TW_BIL",       "billion");   
define("TW_MIO",       "million");   
define("TW_K",         "thousand");   
define("TW_M",         "mio");   
define("TW_PCT",       "percent");   
define("TW_CF",        "cash flow statement");   
define("TW_TAX",       "Income taxes");   
define("TW_SECT_AUTO", "Discrete Automation and Motion");   
define("TW_BALANCE",   "balance sheet");   

// some test words used for testing phrases
define("TW_CANTON",    "Canton");  
define("TW_CITY",      "City");  

// some test phrases used for testing
define("TP_ZH_CANTON", "Zurich (Canton)");  
define("TP_ZH_CITY",   "Zurich (City)");  
define("TP_ZH_INS",    "Zurich Insurance");  

// some formula parameter used for testing
define("TF_INCREASE"  ,"increase");   
define("TF_PE"        ,"Price Earning ratio");   
define("TF_SECTOR"    ,"sectorweight");   
define("TF_SCALE_BIL" ,"scale billions to one");   
define("TF_SCALE_MIO" ,"scale mio to one");   
define("TF_SCALE_K"   ,"scale thousand to one");   

// some numbers used to test the program
define("TV_TEST_SALES_2016",     1234);   
define("TV_TEST_SALES_2017",     2345);   
define("TV_ABB_SALES_2013",      45548);   
define("TV_ABB_SALES_2014",      46000);   
define("TV_NESN_SALES_2016",     89469);   
define("TV_ABB_SALES_AUTO_2013",  9915);   

define("TV_TEST_SALES_INCREASE_2017_FORMATTED", '90.03 %');   
define("TV_NESN_SALES_2016_FORMATTED",          '89\'469');   

// some source used to test the program
define("TS_NESN_2016_ID",   1);   
define("TS_NESN_2016_NAME", 'Nestlé Financial Statement 2016');   


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
define("TC_ADD2",                 "Test Mask Component two");   

// max time expected for each function execution
define("TIMEOUT_LIMIT",          0.03); // time limit for normal functions
define("TIMEOUT_LIMIT_PAGE",     0.1);  // time limit for complete webpage
define("TIMEOUT_LIMIT_PAGE_SEMI",0.6);  // time limit for complete webpage
define("TIMEOUT_LIMIT_PAGE_LONG",1.2);  // time limit for complete webpage
define("TIMEOUT_LIMIT_DB",       0.2);  // time limit for database modification functions
define("TIMEOUT_LIMIT_DB_MULTI", 0.9);  // time limit for many database modifications
define("TIMEOUT_LIMIT_LONG",     3);    // time limit for complex functions
define("TIMEOUT_LIMIT_IMPORT",   4);    // time limit for complex import tests

// views used for testing
define("TD_COMPLETE",      "complete");                      // the default and base view for all words
define("TD_COMPANY_LIST",  "Company list with main ratios"); // the defualt view for the company list

//define('ROOTPATH', __DIR__);

// ---------------------------
// function to suppurt testing
// ---------------------------

  // display the result of one test e.g. if adding a value has been successful
  function test_show_result($test_text, $target, $result, $exe_start_time, $exe_max_time, $comment, $test_type) {
    global $error_counter;
    global $timeout_counter;
    global $total_tests;
    
    $result = test_uncolor($result);
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
      if ($test_type == 'contains') {
        echo " should contain \"".implode(",",$target)."\"";
      } else {
        echo " should be \"".implode(",",$target)."\"";
      }
    } else {
      if ($test_type == 'contains') {
        echo " should contain \"".$target."\"";
      } else {
        echo " should be \"".$target."\"";
      }
    }
    if ($result == $target) {
      if ($test_type == 'contains') {
        echo " and it contains ";
      } else {
        echo " and it is ";
      }
    } else {
      if ($test_type == 'contains') {
        echo ", but does not contain ";
      } else {
        echo ", but it is ";
      }
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
  
  // display the difference between strings excluding non display chars
  function test_show_diff($target, $result) {
    $diff_lst = str_diff($target, $result);
    foreach(array_keys($diff_lst) as $diff_key) {
      echo $diff_key;
    }    
    echo '<br>';
  }

  // remove color setting from the result to reduce confusion by missleading colors
  function test_uncolor($result) {
    $result = str_replace('<font color="red">', '', $result);
    $result = str_replace('<font class="user_specific">', '', $result);
    $result = str_replace('</font>', '', $result);
    return $result;
  }

  // similar to test_show_result, but the target only needs to be part of the result
  // e.g. "ABB" is part of the company word list
  function test_show_contains($test_text, $target, $result, $exe_start_time, $exe_max_time, $comment) {
    if (strpos($result, $target) === false) {
      $result = $target.' not found in '.$result;
    } else {
      $result = $target;
    }
    $new_start_time = test_show_result($test_text, $target, $result, $exe_start_time, $exe_max_time, $comment, 'contains');
    return $new_start_time;
  }
  
  // external string diff only for testing
  function str_diff($from, $to)
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
  
// ---------------------------------------------------------------------------------------------
// testing functions to check the words, values and formulas that should always be in the system
// ---------------------------------------------------------------------------------------------
  
  function load_word($wrd_name, $debug) {
    global $usr;
    $wrd = New word;
    $wrd->usr = $usr;
    $wrd->name = $wrd_name;
    $wrd->load($debug-1);
    return $wrd;
  }

  function test_word($wrd_name, $debug) {
    global $exe_start_time;
    $wrd = load_word($wrd_name, $debug-1);
    $target = $wrd_name;
    $exe_start_time = test_show_result(', word', $target, $wrd->name, $exe_start_time, TIMEOUT_LIMIT);
    return $wrd;
  }

  function load_formula($frm_name, $debug) {
    global $usr;
    $frm = New formula;
    $frm->usr = $usr;
    $frm->name = $frm_name;
    $frm->load($debug-1);
    return $frm;
  }

  function test_formula($frm_name, $debug) {
    global $exe_start_time;
    $frm = load_formula($frm_name, $debug-1);
    $target = $frm_name;
    $exe_start_time = test_show_result(', formula', $target, $frm->name, $exe_start_time, TIMEOUT_LIMIT);
    return $frm;
  }

  function load_phrase($phr_name, $debug) {
    global $usr;
    $phr = New phrase;
    $phr->usr = $usr;
    $phr->name = $phr_name;
    $phr->load($debug-1);
    return $phr;
  }

  function test_phrase($phr_name, $debug) {
    global $exe_start_time;
    $phr = load_phrase($phr_name, $debug-1);
    $target = $phr_name; 
    $exe_start_time = test_show_result(', phrase', $target, $phr->name, $exe_start_time, TIMEOUT_LIMIT);
    return $phr;
  }

  function load_view($dsp_name, $debug) {
    global $usr;
    $dsp = New view_dsp;
    $dsp->usr = $usr;
    $dsp->name = $dsp_name;
    $dsp->load($debug-1);
    return $dsp;
  }

  // same as load_view but for a specific usr
  function load_view_usr($dsp_name, $usr, $debug) {
    $dsp = New view_dsp;
    $dsp->usr = $usr;
    $dsp->name = $dsp_name;
    $dsp->load($debug-1);
    return $dsp;
  }

  function get_view($dsp_name, $debug) {
    global $usr;
    $dsp = load_view($dsp_name, $debug);
    if ($dsp->id == 0 OR $dsp->id == Null) {
      $dsp->usr = $usr;
      $dsp->name = $dsp_name;
      $dsp->save($debug-1);
    }
    return $dsp;
  }

  function test_view($dsp_name, $debug) {
    global $exe_start_time;
    $dsp = load_view($dsp_name, $debug-1);
    $target = $dsp_name;
    $exe_start_time = test_show_result(', view', $target, $dsp->name, $exe_start_time, TIMEOUT_LIMIT);
    return $dsp;
  }

  function load_view_component($cmp_name, $debug) {
    global $usr;
    $cmp = New view_component;
    $cmp->usr = $usr;
    $cmp->name = $cmp_name;
    $cmp->load($debug-1);
    return $cmp;
  }

  // same as load_view_component but for a specific usr
  function load_view_component_usr($cmp_name, $usr, $debug) {
    $cmp = New view_component;
    $cmp->usr = $usr;
    $cmp->name = $cmp_name;
    $cmp->load($debug-1);
    return $cmp;
  }

  function get_view_component($cmp_name, $debug) {
    global $usr;
    $cmp = load_view_component($cmp_name, $debug);
    if ($cmp->id == 0 OR $cmp->id == Null) {
      $cmp->usr = $usr;
      $cmp->name = $cmp_name;
      $cmp->save($debug-1);
    }
    return $cmp;
  }

  function test_component($cmp_name, $debug) {
    global $exe_start_time;
    $cmp = load_view($cmp_name, $debug-1);
    $target = $cmp_name;
    $exe_start_time = test_show_result(', view component', $target, $cmp->name, $exe_start_time, TIMEOUT_LIMIT);
    return $cmp;
  }

  // check if a word link exists and if not and requested create it
  function test_word_link($from, $verb, $to, $autocreate, $phrase_name) {
    global $usr;
    global $exe_start_time;
    $wrd_from = load_word($from, $debug-1);
    if ($wrd_from->id <= 0 and $autocreate) {
      $wrd_from->name= $from;
      $wrd_from->save($debug-1);
      $wrd_from->load($debug-1);
    }
    $wrd_to = load_word($to, $debug-1);
    if ($wrd_to->id <= 0 and $autocreate) {
      $wrd_to->name= $to;
      $wrd_to->save($debug-1);
      $wrd_to->load($debug-1);
    }
    $vrb = New verb;
    $vrb->id= cl($verb);
    $vrb->usr_id = $usr->id;
    $vrb->load($debug-1);
    $lnk_test = New word_link;
    if ($wrd_from->id > 0 and $wrd_to->id) {
      $lnk_test->from_id = $wrd_from->id;
      $lnk_test->verb_id = cl($verb);
      $lnk_test->to_id   = $wrd_to->id;
      $lnk_test->usr  = $usr;
      $lnk_test->load($debug-1);
      if ($lnk_test->id > 0) {
        $result = $lnk_test;
        $target = $from.' '.$vrb->reverse.' '.$to;
      } else {  
        $lnk_test->from_id = $wrd_to->id;
        $lnk_test->verb_id = cl($verb);
        $lnk_test->to_id   = $wrd_from->id;
        $lnk_test->usr  = $usr;
        $lnk_test->load($debug-1);
        $result = $lnk_test;
        if ($verb == SQL_LINK_TYPE_IS) {
          $target = $to.' ('.$from.')';
        } else {
          $target = $to.' '.$vrb->name.' '.$from;
        }
        if ($lnk_test->id <= 0 and $autocreate) {
          $lnk_test->from_id = $wrd_to->id;
          $lnk_test->verb_id = cl($verb);
          $lnk_test->to_id   = $wrd_from->id;
          $lnk_test->save($debug-1);
          $lnk_test->load($debug-1);
          if ($lnk_test->id <> 0 and $phrase_name <> '') {
            $lnk_test->description = $phrase_name;
            $lnk_test->save($debug-1);
          }
        }
      }
    }
    if ($phrase_name <> '') {
      $target = $phrase_name;
    }
    $exe_start_time = test_show_result(', word link', $target, $result->name, $exe_start_time, TIMEOUT_LIMIT_DB);
    return $result;
  }


  function test_formula_link($formula_name, $word_name, $autocreate) {
    global $usr;
    global $exe_start_time;
    $frm = New formula;
    $frm->usr = $usr;
    $frm->name = $formula_name;
    $frm->load($debug-1);
    $phr = New word;
    $phr->name = $word_name;
    $phr->usr = $usr;
    $phr->load($debug-1);
    if ($frm->id > 0 and $phr->id <> 0) {
      $frm_lnk = New formula_link;
      $frm_lnk->usr = $usr;
      $frm_lnk->fob = $frm;
      $frm_lnk->tob = $phr;
      $frm_lnk->load($debug-1);
      if ($frm_lnk->id > 0) {
        $result = $frm_lnk->fob->name().' is linked to '.$frm_lnk->tob->name();
        $target = $formula_name.' is linked to '.$word_name; 
        $exe_start_time = test_show_result(', formula_link', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
      }  
    }  
    return $result;
  }


// -----------------------------------------------
// testing functions to create the main time value
// -----------------------------------------------
  
  function zu_test_time_setup() {
    global $usr;
    $result = '';
    $this_year = date('Y');
    $prev_year = '';
    $test_years = cfg_get('test_years', $usr, $debug-10);
    $start_year = $this_year - $test_years;
    $end_year = $this_year + $test_years;
    for ($year = $start_year; $year <= $end_year; $year++) {
      $this_year = $year;
      $wrd = test_word(strval($this_year));
      $wrd_lnk = test_word_link(TW_YEAR, SQL_LINK_TYPE_IS, $this_year, true);
      $result = $wrd_lnk->name;
      if ($prev_year <> '') {
        test_word_link($prev_year, SQL_LINK_TYPE_FOLLOW, $this_year, true);
      }
      $prev_year = $this_year;
    }
    return $result;
  }

  // display the test results
  function zu_test_dsp_result() {

    global $start_time;
    global $error_counter;
    global $timeout_counter;
    global $total_tests;
    
    echo '<br>';
    echo '<h2>';
    echo $total_tests.' test cases<br>';
    echo $timeout_counter.' timeouts<br>';
    echo $error_counter.' errors<br>';
    echo "<br>";
    $since_start = microtime(true) - $start_time;
    echo round($since_start,4).' seconds for testing zukunft.com</h2>';
    echo '<br>';
    echo '<br>';
  }
  
?>
