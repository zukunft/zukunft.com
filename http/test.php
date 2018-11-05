<?php 

/*

  test.php - for internal code consistency TESTing
  --------
  
  executes all class methods and all functions at least once 
  - in case of errors in the methods automatically a ticket is opened the the table sys_log
    with zukunft.com/error_update.php the tickets can be view and closed
  - and compares the result with the expected result
    in case of an unexpected result also a ticket is created
  
  ToDo:
  - add all missing class functions with at lease one test case
  - check that a object function never changes a parameter 
    e.g. if a formula object is loaded the calculation of a result should not influence the loaded ref text
    instead use a copy of the ref text for the calculation
  - check the usage of "old" functions
  

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

// switch for the email testing
define("TEST_EMAIL",       FALSE);   

// the fixed system user used for testing
define("TEST_USER_ID",          "1");   
define("TEST_USER_NAME",        "zukunft.com system batch job");   
define("TEST_USER_DESCRIPTION", "standard user view for all users");   
define("TEST_USER_ID2",         "2");   
define("TEST_USER_IP",          "66.249.64.95"); // used to check th eblocking of an IP adress

// the basic test record for doing the pre check
// the word "Company" is assumed to have the ID 1
define("TEST_WORD_ID",      "1");   
define("TEST_WORD",         "Company");   
define("TEST_WORD_PLURAL",  "Companies");   
define("TEST_TRIPLE_ID",    "1");   
define("TEST_TRIPLE",       "Company");   

// some test words used for testing
define("TW_ABB",     "ABB");   
define("TW_DAN",     "Danone");   
define("TW_NESN",    "Nestlé");   
define("TW_VESTAS",  "Vestas");  
define("TW_ZH",      "Zurich");   
define("TW_ZH_INS",  "Zurich Insurance");  
define("TW_SALES",   "Sales");   
define("TW_SALES2",  "Revenues");   
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
define("TF_PE"      ,"Price Earning ratio");   
define("TF_SECTOR"  ,"sectorweight");   

// some numbers used to test the program
define("TV_ABB_SALES_2014", 46000);   

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

// max time expected for each function execution
define("TIMEOUT_LIMIT",          0.03); // time limit for normal functions
define("TIMEOUT_LIMIT_PAGE",     0.1);  // time limit for complete webpage
define("TIMEOUT_LIMIT_PAGE_SEMI",0.6);  // time limit for complete webpage
define("TIMEOUT_LIMIT_PAGE_LONG",1.2);  // time limit for complete webpage
define("TIMEOUT_LIMIT_DB",       0.2);  // time limit for database modification functions
define("TIMEOUT_LIMIT_DB_MULTI", 0.8);  // time limit for many database modifications
define("TIMEOUT_LIMIT_LONG",     3);    // time limit for complex functions

//define('ROOTPATH', __DIR__);

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 1) { echo 'lib loaded<br>'; }
$link = zu_start("start test.php", "", $debug-10);

// system test user to simulate the user sandbox
// e.g. a value owned by the first user cannot be adjusted by the second user
// instead a user specific value is created
$usr = New user_dsp;
$usr->id = TEST_USER_ID;
$usr->load_test_user($debug-1);

$usr2 = New user_dsp;
$usr2->id = TEST_USER_ID2;
$usr2->load_test_user($debug-1);

$start_time = microtime(true);
$exe_start_time = $start_time;

$error_counter = 0;
$timeout_counter = 0;
$total_tests = 0;

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

  // similar to zu_test_show_result, but the target only needs to be part of the result
  // e.g. "ABB" is part of the company word list
  function zu_test_show_contains($test_text, $target, $result, $exe_start_time, $exe_max_time, $comment) {
    if (strpos($result, $target) === false) {
      $result = $target.' not found in '.$result;
    } else {
      $result = $target;
    }
    $new_start_time = zu_test_show_result($test_text, $target, $result, $exe_start_time, $exe_max_time, $comment);
    return $new_start_time;
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


echo "<h2>Test the blocked IP adresses</h2><br>";

// check the first predefined word "Company"
// load by id
$usr_test = New user;
$usr_test->ip_addr = TEST_USER_IP;
$target = 'Your IP '.$usr_test->ip_addr.' is blocked at the moment because too much damage from this IP. If you think, this should not be the case, please request the unblocking with an email to admin@zukunft.com.';
$result .= $usr_test->get($debug-1);
if ($usr_test->id > 0) {
  $result = 'permitted!';
}
$exe_start_time = zu_test_show_result(', IP blocking for '.$usr_test->ip_addr, $target, $result, $exe_start_time, TIMEOUT_LIMIT); 


echo "<h2>Test the user class (classes/user.php)</h2><br>";

$target = '<a href="/http/user.php?id='.TEST_USER_ID.'">zukunft.com system batch job</a>';
$result = $usr->display($debug-1);
$exe_start_time = zu_test_show_result(', user->load for id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the user list class (classes/user_list.php)</h2><br>";

$usr_lst = New user_list;
$usr_lst->load_active($debug-1);
$result = $usr_lst->name($debug-1);
$target = TEST_USER_DESCRIPTION;
$exe_start_time = zu_test_show_contains(', user_list->load_active', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

echo "<h2>Test the word class (classes/word.php)</h2><br>";

// check the first predefined word "Company"
// load by id
$wrd1 = New word;
$wrd1->id = TEST_WORD_ID;
$wrd1->usr = $usr;
$wrd1->load($debug-1);
$target = TEST_WORD;
$result = $wrd1->name;
$exe_start_time = zu_test_show_result(', word->load for id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// load by name
$wrd_company = New word;
$wrd_company->name = TEST_WORD;
$wrd_company->usr = $usr;
$wrd_company->load($debug-1);
$target = TEST_WORD_ID;
$result = $wrd_company->id;
$exe_start_time = zu_test_show_result(', word->load for name "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// main word from url
$wrd = New word;
$wrd->usr = $usr;
$wrd->main_wrd_from_txt(TEST_WORD_ID.','.TEST_WORD_ID, $debug-1);
$target = TEST_WORD;
$result = $wrd1->name;
$exe_start_time = zu_test_show_result(', word->main_wrd_from_txt', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// display
$back = 1;
$target = '<a href="/http/view.php?words='.TEST_WORD_ID.'&back=1">'.TEST_WORD.'</a>';
$result = $wrd_company->display ($back, $debug-1);
$exe_start_time = zu_test_show_result(', word->display "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// word type
$wrd_2013 = New word;
$wrd_2013->name = TW_2013;
$wrd_2013->usr = $usr;
$wrd_2013->load($debug-1);
$target = True;
$result = $wrd_2013->is_type(SQL_WORD_TYPE_TIME, $debug-1);
$exe_start_time = zu_test_show_result(', word->is_type for '.TW_2013.' and "'.SQL_WORD_TYPE_TIME.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// is time
$target = True;
$result = $wrd_2013->is_time($debug-1);
$exe_start_time = zu_test_show_result(', word->is_time for '.TW_2013.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// is not measure
$target = False;
$result = $wrd_2013->is_measure($debug-1);
$exe_start_time = zu_test_show_result(', word->is_measure for '.TW_2013.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// is measure
$wrd_CHF = New word;
$wrd_CHF->name = TW_CHF;
$wrd_CHF->usr = $usr;
$wrd_CHF->load($debug-1);
$target = True;
$result = $wrd_CHF->is_measure($debug-1);
$exe_start_time = zu_test_show_result(', word->is_measure for '.TW_CHF.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// is not scaling
$target = False;
$result = $wrd_CHF->is_scaling($debug-1);
$exe_start_time = zu_test_show_result(', word->is_scaling for '.TW_CHF.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// is scaling
$wrd_mio = New word;
$wrd_mio->name = TW_MIO;
$wrd_mio->usr = $usr;
$wrd_mio->load($debug-1);
$target = True;
$result = $wrd_mio->is_scaling($debug-1);
$exe_start_time = zu_test_show_result(', word->is_scaling for '.TW_MIO.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// is not percent
$target = False;
$result = $wrd_mio->is_percent($debug-1);
$exe_start_time = zu_test_show_result(', word->is_percent for '.TW_MIO.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// is percent
$wrd_pct = New word;
$wrd_pct->name = TW_PCT;
$wrd_pct->usr = $usr;
$wrd_pct->load($debug-1);
$target = True;
$result = $wrd_pct->is_percent($debug-1);
$exe_start_time = zu_test_show_result(', word->is_percent for '.TW_PCT.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// next word
$wrd_2014 = New word;
$wrd_2014->name = TW_2014;
$wrd_2014->usr = $usr;
$wrd_2014->load($debug-1);
$target = $wrd_2014->name;
$wrd_next = $wrd_2013->next($debug-1);
$result = $wrd_next->name;
$exe_start_time = zu_test_show_result(', word->next for '.TW_2013.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// prior word
$target = $wrd_2013->name;
$wrd_prior = $wrd_2014->prior($debug-1);
$result = $wrd_prior->name;
$exe_start_time = zu_test_show_result(', word->prior for '.TW_2014.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// word childs
$wrd_company = New word;
$wrd_company->name = TEST_WORD;
$wrd_company->usr = $usr;
$wrd_company->load($debug-1);
$wrd_ABB = New word;
$wrd_ABB->name = TW_ABB;
$wrd_ABB->usr = $usr;
$wrd_ABB->load($debug-1);
$wrd_lst = $wrd_company->childs($debug-1);
$target = $wrd_ABB->name;
if ($wrd_lst->does_contain($wrd_ABB, $debug-1)) {
  $result = $wrd_ABB->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->childs for "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB, 'out of '.$wrd_lst->dsp_id().'');

// ... word childs excluding the start word
$target = '';
if ($wrd_lst->does_contain($wrd_company, $debug-1)) {
  $result = $wrd_company->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->childs for "'.TEST_WORD.'" excluding the start word', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

// word are
$wrd_lst = $wrd_company->are($debug-1);
$target = $wrd_ABB->name;
if ($wrd_lst->does_contain($wrd_ABB, $debug-1)) {
  $result = $wrd_ABB->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->are for "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

// ... word are including the start word
$target = $wrd_company->name;
if ($wrd_lst->does_contain($wrd_company, $debug-1)) {
  $result = $wrd_company->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->are for "'.TEST_WORD.'" including the start word', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

// word parents
$wrd_ABB = New word;
$wrd_ABB->name = TW_ABB;
$wrd_ABB->usr = $usr;
$wrd_ABB->load($debug-1);
$wrd_company = New word;
$wrd_company->name = TEST_WORD;
$wrd_company->usr = $usr;
$wrd_company->load($debug-1);
$wrd_lst = $wrd_ABB->parents($debug-1);
$target = $wrd_company->name;
if ($wrd_lst->does_contain($wrd_company, $debug-1)) {
  $result = $wrd_company->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->parents for "'.TW_ABB.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

// ... word parents excluding the start word
$target = '';
if ($wrd_lst->does_contain($wrd_ABB, $debug-1)) {
  $result = $wrd_ABB->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->parents for "'.TW_ABB.'" excluding the start word', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

// word is
/*
to change this causes other problems at the moment. cleanup needed
$wrd_ZH = New word;
$wrd_ZH->name = TW_ZH;
$wrd_ZH->usr = $usr;
$wrd_ZH->load($debug-1);
$wrd_canton = New word;
$wrd_canton->name = "Canton";
$wrd_canton->usr = $usr;
$wrd_canton->load($debug-1);
$target = $wrd_canton->name;
$wrd_lst = $wrd_ZH->is($debug-1);
if ($wrd_lst->does_contain($wrd_canton, $debug-1)) {
  $result = $wrd_canton->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->is for "'.TW_ZH.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');
*/

// ... word is including the start word
$target = $wrd_ZH->name;
if ($wrd_lst->does_contain($wrd_ZH, $debug-1)) {
  $result = $wrd_ZH->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->is for "'.TW_ZH.'" including the start word', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

// word is part
$wrd_cf = New word;
$wrd_cf->name = TW_CF;
$wrd_cf->usr = $usr;
$wrd_cf->load($debug-1);
$wrd_tax = New word;
$wrd_tax->name = TW_TAX;
$wrd_tax->usr = $usr;
$wrd_tax->load($debug-1);
$target = $wrd_cf->name;
$wrd_lst = $wrd_tax->is_part($debug-1);
if ($wrd_lst->does_contain($wrd_cf, $debug-1)) {
  $result = $wrd_cf->name;
} else {
  $result = '';
}
$exe_start_time = zu_test_show_result(', word->is_part for "'.TW_TAX.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT, 'out of '.$wrd_lst->dsp_id().'');

// save a new word
$wrd_new = New word;
$wrd_new->name = TEST_WORD;
$wrd_new->usr = $usr;
$result = $wrd_new->save($debug-1);
$target = 'A word with the name "'.TEST_WORD.'" already exists. Please use another name.';
$target = '';
$exe_start_time = zu_test_show_result(', word->save for "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the creation of a new word
$wrd_add = New word;
$wrd_add->name = TW_ADD;
$wrd_add->usr = $usr;
$result = $wrd_add->save($debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', word->save for "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

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
$target = 'zukunft.com system batch job added Test Company';
$exe_start_time = zu_test_show_result(', word->save logged for "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... test if the new word has been created
$wrd_added = New word;
$wrd_added->name = TW_ADD;
$wrd_added->usr = $usr;
$wrd_added->load($debug-1);
$result = $wrd_added->load($debug-1);
if ($result == '') {
  if ($wrd_added->id > 0) {
    $result = $wrd_added->name;
  }
}
$target = TW_ADD;
$exe_start_time = zu_test_show_result(', word->load of added word "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the word can be renamed
$wrd_added->name = TW_ADD_RENAMED;
$result = $wrd_added->save($debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', word->save rename "'.TW_ADD.'" to "'.TW_ADD_RENAMED.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

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
$exe_start_time = zu_test_show_result(', word->load renamed word "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the word renaming has been logged
$log = New user_log;
$log->table = 'words';
$log->field = 'word_name';
$log->row_id = $wrd_renamed->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job changed Test Company to Company Test';
$exe_start_time = zu_test_show_result(', word->save rename logged for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the word parameters can be added
$wrd_renamed->plural      = TW_ADD_RENAMED.'s';
$wrd_renamed->description = TW_ADD_RENAMED.' description';
$wrd_renamed->ref_1       = TW_ADD_RENAMED.' ref_1';
$wrd_renamed->ref_2       = TW_ADD_RENAMED.' ref_2';
$wrd_renamed->type_id     = cl(SQL_WORD_TYPE_OTHER);
$result = $wrd_renamed->save($debug-1);
$target = '11111';
$exe_start_time = zu_test_show_result(', word->save all word fields beside the name for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the word parameters have been added
$wrd_reloaded = New word;
$wrd_reloaded->name = TW_ADD_RENAMED;
$wrd_reloaded->usr = $usr;
$wrd_reloaded->load($debug-1);
$result = $wrd_reloaded->plural;
$target = TW_ADD_RENAMED.'s';
$exe_start_time = zu_test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_reloaded->description;
$target = TW_ADD_RENAMED.' description';
$exe_start_time = zu_test_show_result(', word->load description for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_reloaded->ref_1;
$target = TW_ADD_RENAMED.' ref_1';
$exe_start_time = zu_test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_reloaded->ref_2;
$target = TW_ADD_RENAMED.' ref_2';
$exe_start_time = zu_test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_reloaded->type_id;
$target = cl(SQL_WORD_TYPE_OTHER);
$exe_start_time = zu_test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the word parameter adding have been logged
$log = New user_log;
$log->table = 'words';
$log->field = 'plural';
$log->row_id = $wrd_reloaded->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Company Tests';
$exe_start_time = zu_test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'description';
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Company Test description';
$exe_start_time = zu_test_show_result(', word->load description for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'ref_url_1';
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Company Test ref_1';
$exe_start_time = zu_test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'ref_url_2';
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Company Test ref_2';
$exe_start_time = zu_test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'word_type_id';
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added differentiator filler';
$exe_start_time = zu_test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$exe_start_time = zu_test_show_result(', word->save all word fields for user 2 beside the name for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if a user specific word changes have been saved
$wrd_usr2_reloaded = New word;
$wrd_usr2_reloaded->name = TW_ADD_RENAMED;
$wrd_usr2_reloaded->usr = $usr2;
$wrd_usr2_reloaded->load($debug-1);
$result = $wrd_usr2_reloaded->plural;
$target = TW_ADD_RENAMED.'s2';
$exe_start_time = zu_test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_usr2_reloaded->description;
$target = TW_ADD_RENAMED.' description2';
$exe_start_time = zu_test_show_result(', word->load description for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_usr2_reloaded->ref_1;
$target = TW_ADD_RENAMED.' ref_3';
$exe_start_time = zu_test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_usr2_reloaded->ref_2;
$target = TW_ADD_RENAMED.' ref_4';
$exe_start_time = zu_test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_usr2_reloaded->type_id;
$target = cl(SQL_WORD_TYPE_TIME);
$exe_start_time = zu_test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check the word for the original user remains unchanged
$wrd_reloaded = New word;
$wrd_reloaded->name = TW_ADD_RENAMED;
$wrd_reloaded->usr = $usr;
$wrd_reloaded->load($debug-1);
$result = $wrd_reloaded->plural;
$target = TW_ADD_RENAMED.'s';
$exe_start_time = zu_test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_reloaded->description;
$target = TW_ADD_RENAMED.' description';
$exe_start_time = zu_test_show_result(', word->load description for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_reloaded->ref_1;
$target = TW_ADD_RENAMED.' ref_1';
$exe_start_time = zu_test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_reloaded->ref_2;
$target = TW_ADD_RENAMED.' ref_2';
$exe_start_time = zu_test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_reloaded->type_id;
$target = cl(SQL_WORD_TYPE_OTHER);
$exe_start_time = zu_test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'" unchanged for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$exe_start_time = zu_test_show_result(', word->save undo the user word fields beside the name for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if a user specific word changes have been saved
$wrd_usr2_reloaded = New word;
$wrd_usr2_reloaded->name = TW_ADD_RENAMED;
$wrd_usr2_reloaded->usr = $usr2;
$wrd_usr2_reloaded->load($debug-1);
$result = $wrd_usr2_reloaded->plural;
$target = TW_ADD_RENAMED.'s';
$exe_start_time = zu_test_show_result(', word->load plural for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_usr2_reloaded->description;
$target = TW_ADD_RENAMED.' description';
$exe_start_time = zu_test_show_result(', word->load description for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_usr2_reloaded->ref_1;
$target = TW_ADD_RENAMED.' ref_1';
$exe_start_time = zu_test_show_result(', word->load ref_1 for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_usr2_reloaded->ref_2;
$target = TW_ADD_RENAMED.' ref_2';
$exe_start_time = zu_test_show_result(', word->load ref_2 for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $wrd_usr2_reloaded->type_id;
$target = cl(SQL_WORD_TYPE_OTHER);
$exe_start_time = zu_test_show_result(', word->load type_id for "'.TW_ADD_RENAMED.'" unchanged now also for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// redo the user specific word changes
// check if the user specific changes can be removed with one click


// test the user display after the word changes to have a sample case
echo "<h2>Test the user display class (classes/user_display.php)</h2><br>";

$result = $usr->dsp_edit($back, $debug-1);
$target = TEST_USER_NAME;
$exe_start_time = zu_test_show_contains(', user_display->dsp_edit', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the word display class (classes/word_display.php)</h2><br>";

// check the graph display
// test uses the old function zum_word_list to compare, so it is a kind of double coding
// correct test would be using a "fixed HTML text contains"
$wrd_ABB = New word_dsp;
$wrd_ABB->name = TW_ABB;
$wrd_ABB->usr = $usr;
$wrd_ABB->load($debug-1);
$direction = 'up';
$target = TW_ABB.' is a';
$result = substr($wrd_ABB->dsp_graph ($direction, $debug-1),0,8);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_graph '.$direction.' for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check the graph display
$wrd_ABB = New word_dsp;
$wrd_ABB->name = TW_ABB;
$wrd_ABB->usr = $usr;
$wrd_ABB->load($debug-1);
$direction = 'down';
$target = zut_html_list_related ($wrd_ABB->id, $direction, $usr->id, $debug-1);
$result = $wrd_ABB->dsp_graph ($direction, $debug-1);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and the other side
$direction = 'up';
$target = zut_html_list_related ($wrd_ABB->id, $direction, $usr->id, $debug-1);
$result = $wrd_ABB->dsp_graph ($direction, $debug-1);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and the graph display for Zurich
$wrd_ZH = New word_dsp;
$wrd_ZH->name = TW_ZH;
$wrd_ZH->usr = $usr;
$wrd_ZH->load($debug-1);
$direction = 'down';
$target = zut_html_list_related ($wrd_ZH->id, $direction, $usr->id, $debug);
$result = $wrd_ZH->dsp_graph ($direction, $debug-1);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_ZH->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and the other side
$direction = 'up';
$target = zut_html_list_related ($wrd_ZH->id, $direction, $usr->id, $debug);
$result = $wrd_ZH->dsp_graph ($direction, $debug-1);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_ZH->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and the graph display for 2012
$wrd_2012 = New word_dsp;
$wrd_2012->name = TW_2012;
$wrd_2012->usr = $usr;
$wrd_2012->load($debug-1);
$direction = 'down';
$target = zut_html_list_related ($wrd_2012->id, $direction, $usr->id, $debug);
$result = $wrd_2012->dsp_graph ($direction, $debug-1);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_2012->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and the other side
$direction = 'up';
$target = zut_html_list_related ($wrd_2012->id, $direction, $usr->id, $debug);
$result = $wrd_2012->dsp_graph ($direction, $debug-1);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_2012->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// the value table for ABB
$wrd_ABB = New word_dsp;
$wrd_ABB->name = TW_ABB;
$wrd_ABB->usr = $usr;
$wrd_ABB->load($debug-1);
$wrd_year = New word_dsp;
$wrd_year->name = TW_YEAR;
$wrd_year->usr = $usr;
$wrd_year->load($debug-1);
$target = zut_dsp_list_wrd_val($wrd_ABB->id, $wrd_year->id, $usr->id, $debug-1);
$target = substr($target,0,208);
$result = $wrd_ABB->dsp_val_list ($wrd_year, $back, $debug-1);
$result = substr($result,0,208);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_val_list compare to old for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// the value table for Company
/*
$wrd_company = New word_dsp;
$wrd_company->name = "TEST_WORD";
$wrd_company->usr = $usr;
$wrd_company->load($debug-1);
$wrd_ratios = New word_dsp;
$wrd_ratios->name = "Company main ratio";
$wrd_ratios->usr = $usr;
$wrd_ratios->load($debug-1);
$target = zut_dsp_list_wrd_val($wrd_company->id, $wrd_ratios->id, $usr->id, $debug-1);
$target = substr($target,0,200);
$result = $wrd_company->dsp_val_list ($wrd_ratios, $back, $debug-1);
$result = substr($result,0,200);
$exe_start_time = zu_test_show_result(', word_dsp->dsp_val_list compare to old for '.$wrd_company->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);
*/


echo "<h2>Test the display selector class (classes/display_selector.php)</h2><br>";

// for testing the selector display a company selector and select ABB
$phr_corp = New phrase;
$phr_corp->name = TEST_WORD;
$phr_corp->usr = $usr;
$phr_corp->load($debug-1);
$phr_ABB = New phrase;
$phr_ABB->name = TW_ABB;
$phr_ABB->usr = $usr;
$phr_ABB->load($debug-1);
$sel = New selector;
$sel->usr        = $usr;
$sel->form       = 'test_form';
$sel->name       = 'select_company';  
$sel->sql        = $phr_corp->sql_list ($phr_corp, $debug-1);
$sel->selected   = $phr_ABB->id;
$sel->dummy_text = '... please select';
$result .= $sel->display ($debug-1);
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', display_selector->display of all '.$phr_corp->name.' with '.$wrd_ABB->name.' selected', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the display list class (classes/display_list.php)</h2><br>";

// not yet used
/*
$phr_corp = New phrase;
$phr_corp->name = TEST_WORD;
$phr_corp->usr = $usr;
$phr_corp->load($debug-1);
$phr_ABB = New phrase;
$phr_ABB->name = TW_ABB;
$phr_ABB->usr = $usr;
$phr_ABB->load($debug-1);
$sel = New selector;
$sel->usr        = $usr;
$sel->form       = 'test_form';
$sel->name       = 'select_company';  
$sel->sql        = $phr_corp->sql_list ($phr_corp, $debug-1);
$sel->selected   = $phr_ABB->id;
$sel->dummy_text = '... please select';
$result .= $sel->display ($debug-1);
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', display_selector->display of all '.$phr_corp->name.' with '.$wrd_ABB->name.' selected', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
*/

echo "<h2>Test the word frontend scripts (e.g. /word_add.php)</h2><br>";

// call the add word page and check if at least some keywords are returned
$wrd_ABB = New word_dsp;
$wrd_ABB->name = TW_ABB;
$wrd_ABB->usr = $usr;
$wrd_ABB->load($debug-1);
$vrb_is = cl(SQL_LINK_TYPE_IS);
$wrd_type = cl(SQL_WORD_TYPE_NORMAL);
$result = file_get_contents('https://zukunft.com/http/word_add.php?verb='.$vrb_is.'&word='.$wrd_ABB->id.'&type=1&back='.$wrd_ABB->id.'');
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', frontend word_add.php '.$result.' contains at least '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

// test the edit word frontend
$result = file_get_contents('https://zukunft.com/http/word_edit.php?id='.$wrd_ABB->id.'&back='.$wrd_ABB->id.'');
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', frontend word_edit.php '.$result.' contains at least '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

// test the del word frontend
$result = file_get_contents('https://zukunft.com/http/word_del.php?id='.$wrd_ABB->id.'&back='.$wrd_ABB->id.'');
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', frontend word_del.php '.$result.' contains at least '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


echo "<h2>Test the word list class (classes/word_list.php)</h2><br>";

// test load by word list by names
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->add_name(TW_SALES);
$wrd_lst->add_name(TW_MIO);
$wrd_lst->load($debug-1);
$result = $wrd_lst->name($debug-1);
$target = '"'.TW_MIO.'","'.TW_SALES.'","'.TW_ABB.'"'; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', word_list->load by names for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test load by word list by group id
/*$wrd_grp_id = $wrd_lst->grp_id;
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->grp_id = $wrd_grp_id;
$wrd_lst->load($debug-1);
$result = implode(',',$wrd_lst->names($debug-1));
$target = "million,Sales,ABB"; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', word_list->load by word group id for "'.$wrd_grp_id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); */

// test add by type
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->load($debug-1);
$wrd_lst->add_by_type(Null, cl(SQL_LINK_TYPE_IS), "up", $debug-1);
$result = implode(',',$wrd_lst->names($debug-1));
$target = TW_ABB.",".TEST_WORD; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', word_list->add_by_type for "'.$wrd->name.'" up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test add parent
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->load($debug-1);
$wrd_lst->foaf_parents(cl(SQL_LINK_TYPE_IS), $debug-1);
$result = implode(',',$wrd_lst->names($debug-1));
$target = TW_ABB.",".TEST_WORD; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', word_list->foaf_parent for "'.$wrd->name.'" up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test add parent step
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->load($debug-1);
$wrd_lst->parents(cl(SQL_LINK_TYPE_IS), 1, $debug-1);
$result = implode(',',$wrd_lst->names($debug-1));
$target = TW_ABB.",".TEST_WORD; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', word_list->parents for "'.$wrd->name.'" up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test add child and contains
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TEST_WORD);
$wrd_lst->load($debug-1);
$wrd_lst->foaf_childs(cl(SQL_LINK_TYPE_IS), $debug-1);
$ABB = New word;
$ABB->usr = $usr;
$ABB->name = TW_ABB;
$ABB->load($debug-1);
$result = $wrd_lst->does_contain($ABB, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', word_list->foaf_childs is "'.implode('","',$wrd_lst->names($debug-1)).'", which contains '.TW_ABB.' ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test direct childs
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TEST_WORD);
$wrd_lst->load($debug-1);
$wrd_lst->childs(cl(SQL_LINK_TYPE_IS), $debug-1);
$ABB = New word;
$ABB->usr = $usr;
$ABB->name = TW_ABB;
$ABB->load($debug-1);
$result = $wrd_lst->does_contain($ABB, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', word_list->childs is "'.implode('","',$wrd_lst->names($debug-1)).'", which contains '.TW_ABB.' ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test is
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->load($debug-1);
$lst_is = $wrd_lst->is($debug-1);
$result = implode(',',$lst_is->names($debug-1));
$target = TEST_WORD; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', word_list->is for '.$wrd_lst->name().' up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test are
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TEST_WORD);
$wrd_lst->load($debug-1);
$lst_are = $wrd_lst->are(cl(SQL_LINK_TYPE_IS), $debug-1);
$ABB = New word;
$ABB->usr = $usr;
$ABB->name = TW_ABB;
$ABB->load($debug-1);
$result = $lst_are->does_contain($ABB, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', word_list->are "'.implode('","',$wrd_lst->names($debug-1)).'", which contains '.TW_ABB.' ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ....

// exclude types
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->add_name(TW_SALES);
$wrd_lst->add_name(TW_CHF);
$wrd_lst->add_name(TW_MIO);
$wrd_lst->add_name(TW_2014);
$wrd_lst->load($debug-1);
$wrd_lst_ex = clone $wrd_lst;
$wrd_lst_ex->ex_time($debug-1);
$result = $wrd_lst_ex->name($debug-1);
$target = '"'.TW_MIO.'","'.TW_CHF.'","'.TW_SALES.'","'.TW_ABB.'"'; // also the creation should be tested, but how?
$exe_start_time = zu_test_show_result(', word_list->ex_time for '.$wrd_lst->name($debug-1), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test group id
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->add_name(TW_SALES);
$wrd_lst->add_name(TW_CHF);
$wrd_lst->add_name(TW_MIO);
$wrd_lst->add_name(TW_2014);
$wrd_lst->load($debug-1);
$grp = New phrase_group;
$grp->usr = $usr;         
$grp->ids = $wrd_lst->ids;         
$result = $grp->get_id($debug-1);
$target = "2116"; // also the creation should be tested, but how?
$exe_start_time = zu_test_show_result(', phrase_group->get_id for "'.implode('","',$wrd_lst->names($debug-1)).'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test word list value
$val = $wrd_lst->value($debug-1);
$result = $val->number;
$target = TV_ABB_SALES_2014;
$exe_start_time = zu_test_show_result(', word_list->value for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test word list value scaled
// review !!!
$val = $wrd_lst->value_scaled($debug-1);
$result = $val->number;
$target = TV_ABB_SALES_2014;
$exe_start_time = zu_test_show_result(', word_list->value_scaled for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test assume time
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->add_name(TW_SALES);
$wrd_lst->add_name(TW_MIO);
$wrd_lst->load($debug-1);
$abb_last_year = $wrd_lst->assume_time($debug-1);
$result = $abb_last_year->name;
$target = TW_2017;
$exe_start_time = zu_test_show_result(', word_list->assume_time for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);


// word sort
$wrd_ZH = New word;
$wrd_ZH->name = TW_ZH;
$wrd_ZH->usr = $usr;
$wrd_ZH->load($debug-1);
$wrd_lst = $wrd_ZH->parents($debug-1);
$wrd_lst->wlsort($debug-1);
$target = '"Canton","City","Company"';
$result = $wrd_lst->name($debug-1);
$exe_start_time = zu_test_show_result(', word_list->sort for "'.$wrd_ZH->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

echo "<h2>Test the word link class (classes/word_link.php)</h2><br>";

// check the triple usage for Zurich (City) and Zurich (Canton)
$wrd_zh = New word;
$wrd_zh->name= TW_ZH;
$wrd_zh->usr = $usr;
$wrd_zh->load($debug-1);
$wrd_city = New word;
$wrd_city->name= 'City';
$wrd_city->usr = $usr;
$wrd_city->load($debug-1);
$wrd_canton = New word;
$wrd_canton->name= 'Canton';
$wrd_canton->usr = $usr;
$wrd_canton->load($debug-1);
$lnk_city = New word_link;
$lnk_city->from_id = $wrd_zh->id;
$lnk_city->verb_id = cl(SQL_LINK_TYPE_IS);
$lnk_city->to_id   = $wrd_city->id;
$lnk_city->usr  = $usr;
$lnk_city->load($debug-1);
$target = TW_ZH.' (City)';
$result = $lnk_city->name;
$exe_start_time = zu_test_show_result(', triple->load for '.TW_ZH.' (City)', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

// ... now test the Canton Zurich
$lnk_city = New word_link;
$lnk_city->from_id = $wrd_zh->id;
$lnk_city->verb_id = cl(SQL_LINK_TYPE_IS);
$lnk_city->to_id   = $wrd_canton->id;
$lnk_city->usr  = $usr;
$lnk_city->load($debug-1);
$target = TW_ZH.' (Canton)';
$result = $lnk_city->name;
$exe_start_time = zu_test_show_result(', triple->load for Canton Zurich', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

// ... now test the Canton Zurich using the name function
$target = TW_ZH.' (Canton)';
$result = $lnk_city->name($debug-1);
$exe_start_time = zu_test_show_result(', triple->load for Canton Zurich using the function', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... now test the Insurance Zurich
$lnk_company = New word_link;
$lnk_company->from_id = $wrd_zh->id;
$lnk_company->verb_id = cl(SQL_LINK_TYPE_IS);
$lnk_company->to_id   = TEST_WORD_ID;
$lnk_company->usr  = $usr;
$lnk_company->load($debug-1);
$target = TW_ZH_INS;
$result = $lnk_company->name;
$exe_start_time = zu_test_show_result(', triple->load for '.TW_ZH_INS.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$triple_sample_id = $lnk_company->id;

// remember the id for later use
$zh_company_id = $lnk_company->id;

// ... now test the Insurance Zurich using the name function
$target = TW_ZH_INS;
$result = $lnk_company->name($debug-1);
$exe_start_time = zu_test_show_result(', triple->load for '.TW_ZH_INS.' using the function', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// link the added word to the test word
$wrd_added = New word;
$wrd_added->name = TW_ADD_RENAMED;
$wrd_added->usr = $usr;
$wrd_added->load($debug-1);
$wrd = New word;
$wrd->name = TEST_WORD;
$wrd->usr = $usr;
$wrd->load($debug-1);
$vrb = New verb;
$vrb->id= cl(SQL_LINK_TYPE_IS);
$vrb->usr_id = $usr->id;
$vrb->load($debug-1);
$lnk = New word_link;
$lnk->usr     = $usr;
$lnk->from_id = $wrd_added->id;
$lnk->verb_id = $vrb->id;
$lnk->to_id   = $wrd->id;
$result = $lnk->save($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', triple->save "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

echo "... and also testing the user log link class (classes/user_log_link.php)<br>";

// ... check the correct logging
$log = New user_log_link;
$log->table = 'word_links';
$log->new_from_id = $wrd_added->id;
$log->new_link_id = $vrb->id;
$log->new_to_id = $wrd->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job linked Company Test to Company';
$exe_start_time = zu_test_show_result(', triple->save logged for "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the link is shown correctly

$lnk = New word_link;
$lnk->usr     = $usr;
$lnk->from_id = $wrd_added->id;
$lnk->verb_id = $vrb->id;
$lnk->to_id   = $wrd->id;
$lnk->load($debug-1);
$result = $lnk->name;
$target = 'Company Test (Company)'; 
$exe_start_time = zu_test_show_result(', triple->load', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
// ... check if the link is shown correctly also for the second user
$lnk2 = New word_link;
$lnk2->usr     = $usr2;
$lnk2->from_id = $wrd_added->id;
$lnk2->verb_id = $vrb->id;
$lnk2->to_id   = $wrd->id;
$lnk2->load($debug-1);
$result = $lnk2->name;
$target = 'Company Test (Company)'; 
$exe_start_time = zu_test_show_result(', triple->load for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the value update has been triggert

// if second user removes the new link
$lnk = New word_link;
$lnk->usr     = $usr2;
$lnk->from_id = $wrd_added->id;
$lnk->verb_id = $vrb->id;
$lnk->to_id   = $wrd->id;
$lnk->load($debug-1);
$result = $lnk->del($debug-1);
$target = '111';
$exe_start_time = zu_test_show_result(', triple->del "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... check if the removal of the link for the second user has been logged
$log = New user_log_link;
$log->table = 'word_links';
$log->old_from_id = $wrd_added->id;
$log->old_link_id = $vrb->id;
$log->old_to_id = $wrd->id;
$log->usr_id = $usr2->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system test unlinked Company Test from Company';
$exe_start_time = zu_test_show_result(', triple->del logged for "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" and user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// ... check if the link is really not used any more for the second user
$lnk2 = New word_link;
$lnk2->usr     = $usr2;
$lnk2->from_id = $wrd_added->id;
$lnk2->verb_id = $vrb->id;
$lnk2->to_id   = $wrd->id;
$lnk2->load($debug-1);
$result = $lnk2->name($debug-1);
$target = ''; 
$exe_start_time = zu_test_show_result(', triple->load "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" for user "'.$usr2->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

// ... check if the value update for the second user has been triggert

// ... check all places where the word maybe used ...

// ... check if the link is still used for the first user
$lnk = New word_link;
$lnk->usr     = $usr;
$lnk->from_id = $wrd_added->id;
$lnk->verb_id = $vrb->id;
$lnk->to_id   = $wrd->id;
$lnk->load($debug-1);
$result = $lnk->name;
$target = 'Company Test (Company)'; 
$exe_start_time = zu_test_show_result(', triple->load of "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" is still used for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

// ... check if the values for the first user are still the same

// if the first user also removes the link, both records should be deleted
$lnk = New word_link;
$lnk->usr     = $usr;
$lnk->from_id = $wrd_added->id;
$lnk->verb_id = $vrb->id;
$lnk->to_id   = $wrd->id;
$lnk->load($debug-1);
$result = $lnk->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', triple->del "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check the correct logging
$log = New user_log_link;
$log->table = 'word_links';
$log->old_from_id = $wrd_added->id;
$log->old_link_id = $vrb->id;
$log->old_to_id = $wrd->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job unlinked Company Test from Company';
$exe_start_time = zu_test_show_result(', triple->del logged for "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" and user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the formula is not used any more for both users
$lnk = New word_link;
$lnk->usr     = $usr;
$lnk->from_id = $wrd_added->id;
$lnk->verb_id = $vrb->id;
$lnk->to_id   = $wrd->id;
$lnk->load($debug-1);
$result = $lnk->name;
$target = ''; 
$exe_start_time = zu_test_show_result(', triple->load of "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" for user "'.$usr->name.'" not used any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// ... and the values have been updated
/*
// insert the link again for the first user
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr = New phrase;
$phr->name = TW_ADD_RENAMED;
$phr->usr = $usr2;
$phr->load($debug-1);
$result = $frm->link_phr($phr, $debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', triple->link_phr "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
*/
// ... if the second user changes the link

// ... and the first user removes the link

// ... the link should still be active for the second user

// ... but not for the first user

// ... and the owner should now be the second user

// the code changes and tests for formula link should be moved the view_entry_link



echo "<h2>Test the graph class (classes/word_link_list.php)</h2><br>";

// get all phrase links use for a phrase e.g. all word links used for "Company" and its related values
// step 1: define the phrase list e.g. in this case only word "Company"
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TEST_WORD);
$phr_lst->load($debug-1);

// step 2: get all values related to the phrases
$val_lst = New value_list;
$val_lst->usr     = $usr;
$val_lst->phr_lst = $phr_lst;
$val_lst->load_all($debug-1);
$wrd_lst_all = $val_lst->phr_lst->wrd_lst_all($debug-1);

// step 3: get all phrases used for the value descriptions
$phr_lst_used      = New phrase_list;
$phr_lst_used->usr = $usr;
foreach ($wrd_lst_all->lst AS $wrd) {
  if (!array_key_exists($wrd->id, $phr_lst_used->ids)) {
    $phr_lst_used->add($wrd->phrase($debug-1), $debug-1);
  }
}
// step 4: get the word links for the used phrases
//         these are the word links that are needed for a complete export
$lnk_lst = New word_link_list;
$lnk_lst->usr       = $usr;
$lnk_lst->wrd_lst   = $phr_lst_used;
$lnk_lst->direction = 'up';
$lnk_lst->load($debug-1);
$result = $lnk_lst->name($debug-1);
$target = 'Company has a balance sheet,Company has a forecast,Company uses employee';
$exe_start_time = zu_test_show_contains(', word_link_list->load for '.$phr_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// similar to above, but just for ABB
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_MIO);
$phr_lst->load($debug-1);
$lnk_lst = New word_link_list;
$lnk_lst->usr       = $usr;
$lnk_lst->wrd_lst   = $phr_lst;
$lnk_lst->direction = 'up';
$lnk_lst->load($debug-1);
$result = $lnk_lst->name($debug-1);
$target = 'ABB (Company),million (scaling)';
$exe_start_time = zu_test_show_result(', word_link_list->load for '.$phr_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


// load the words related to ABB in compare with the old function
$ABB = New word_dsp;
$ABB->usr = $usr;
$ABB->name = TW_ABB;
$ABB->load($debug-1);
$is = New verb;
$is->id= cl(SQL_LINK_TYPE_IS);
$is->usr_id = $usr->id;
$is->load($debug-1);
$graph = New word_link_list;
$graph->wrd = $ABB;
$graph->vrb = $is;
$graph->usr = $usr;
$graph->direction = 'down';
$graph->load($debug-1);
$target = zut_html_list_related ($ABB->id, $graph->direction, $usr->id, $debug);
$result = $graph->display($back, $debug-1);
$exe_start_time = zu_test_show_result(', graph->load for ABB down is', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// the other side
$graph->direction = 'up';
$graph->load($debug-1);
$target = zut_html_list_related ($ABB->id, $graph->direction, $usr->id, $debug);
$result = $graph->display($back, $debug-1);
$exe_start_time = zu_test_show_result(', graph->load for ABB up is', $target, $result, $exe_start_time, TIMEOUT_LIMIT);



echo "<h2>Test the pharse class (classes/phrase.php)</h2><br>";

// test the phrase display functions (word side)
$phr = New phrase;
$phr->id  = TEST_WORD_ID;
$phr->usr = $usr;
$phr->load($debug-1);
$result = $phr->name;
$target = TEST_WORD;
$exe_start_time = zu_test_show_result(', phrase->load word by id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$result = str_replace("  "," ",str_replace("\n","",$phr->dsp_tbl($debug-1)));
$target = ' <td> <a href="/http/view.php?words=1" title="">Company</a> </td> ';
$result = str_replace("<","&lt;",str_replace(">","&gt;",$result));
$target = str_replace("<","&lt;",str_replace(">","&gt;",$target));
// to overwrite any special char
$diff = zu_str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
$exe_start_time = zu_test_show_result(', phrase->dsp_tbl word for '.TEST_WORD, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the phrase display functions (triple side)
$phr = New phrase;
$phr->id  = $zh_company_id * -1;
$phr->usr = $usr;
$phr->load($debug-1);
$result = $phr->name;
$target = TW_ZH_INS;
$exe_start_time = zu_test_show_result(', phrase->load triple by id '.$zh_company_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$result = str_replace("  "," ",str_replace("\n","",$phr->dsp_tbl($debug-1)));
$target = ' <td> <a href="/http/view.php?link=313" title="'.TW_ZH_INS.'">'.TW_ZH_INS.'</a> </td> ';
$result = str_replace("<","&lt;",str_replace(">","&gt;",$result));
$target = str_replace("<","&lt;",str_replace(">","&gt;",$target));
// to overwrite any special char
$diff = zu_str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
$exe_start_time = zu_test_show_result(', phrase->dsp_tbl triple for '.$zh_company_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the phrase selector
$form_name = 'test_phrase_selector';
$pos  = 1;
$back = TEST_WORD_ID;
$phr = New phrase;
$phr->id  = $zh_company_id * -1;
$phr->usr = $usr;
$phr->load($debug-1);
$result = $phr->dsp_selector (Null, $form_name, $pos, $back, $debug-1) ;
$target = TW_ZH_INS;
$exe_start_time = zu_test_show_contains(', phrase->dsp_selector '.$result.' with '.TW_ZH_INS.' selected contains '.TW_ZH_INS.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// test the phrase selector of type company
$wrd_ABB = New word_dsp;
$wrd_ABB->name = TW_ABB;
$wrd_ABB->usr = $usr;
$wrd_ABB->load($debug-1);
$phr = $wrd_ABB->phrase($debug-1);
$wrd_company = New word_dsp;
$wrd_company->name = TEST_WORD;
$wrd_company->usr = $usr;
$wrd_company->load($debug-1);
$result = $phr->dsp_selector ($wrd_company, $form_name, $pos, $back, $debug-1);
$target = TW_ZH_INS;
$exe_start_time = zu_test_show_contains(', phrase->dsp_selector of type '.TEST_WORD.': '.$result.' with ABB selected contains '.TW_ZH_INS.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

// test getting the parent for phrase Coca Cola
$phr = New phrase;
$phr->usr = $usr;
$phr->name = TW_VESTAS;
$phr->load($debug-1);
$is_phr = $phr->is_mainly($debug-1);
$result = $is_phr->name;
$target = TEST_WORD; 
$exe_start_time = zu_test_show_result(', phrase->is_mainly for '.$phr->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the pharse list class (classes/phrase_list.php)</h2><br>";

// test the phrase loading via id
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->add_name(TW_VESTAS);
$wrd_lst->load($debug-1);
$id_lst = $wrd_lst->ids;
$id_lst[] = $triple_sample_id * -1;
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->ids = $id_lst;
$phr_lst->load($debug-1);
$target = '"'.TW_ABB.'","'.TW_VESTAS.'","'.TW_ZH_INS.'"';
$result = $phr_lst->name($debug-1);
$exe_start_time = zu_test_show_result(', phrase->load via id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... the complete word list, which means split the triples into single words
$wrd_lst_all = $phr_lst->wrd_lst_all($debug-1);
$target = '"'.TW_ABB.'","'.TW_VESTAS.'","'.TW_ZH.'","'.TEST_WORD.'"';
$result = $wrd_lst_all->name($debug-1);
$exe_start_time = zu_test_show_result(', phrase->wrd_lst_all of list above', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// test getting the parent for phrase list with ABB
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->load($debug-1);
$phr_lst = $wrd_lst->phrase_lst($debug-1);
$lst_parents = $phr_lst->foaf_parents(cl(SQL_LINK_TYPE_IS), $debug-1);
$result = implode(',',$lst_parents->names($debug-1));
$target = TEST_WORD; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', phrase_list->foaf_parents for '.$phr_lst->name().' up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... same using is
$phr_lst = $wrd_lst->phrase_lst($debug-1);
$lst_is = $phr_lst->is($debug-1);
$result = implode(',',$lst_is->names($debug-1));
$target = TEST_WORD; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', phrase_list->is for '.$phr_lst->name().' up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... same with Coca Cola
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_VESTAS);
$wrd_lst->load($debug-1);
$phr_lst = $wrd_lst->phrase_lst($debug-1);
$lst_is = $phr_lst->is($debug-1);
$result = implode(',',$lst_is->names($debug-1));
$target = TEST_WORD; // order adjusted based on the number of usage
$exe_start_time = zu_test_show_result(', phrase_list->is for '.$phr_lst->name().' up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the excluding function
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_MIO);
$phr_lst->add_name(TW_2017);
$phr_lst->load($debug-1);
$phr_lst_ex = clone $phr_lst;
$phr_lst_ex->ex_time($debug-1);
$target = '"'.TW_ABB.'","'.TW_SALES.'","'.TW_CHF.'","'.TW_MIO.'"';
$result = $phr_lst_ex->name($debug-1);
$exe_start_time = zu_test_show_result(', phrase_list->ex_time of '.$phr_lst->name($debug-1), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$phr_lst_ex = clone $phr_lst;
$phr_lst_ex->ex_measure($debug-1);
$target = '"'.TW_ABB.'","'.TW_SALES.'","'.TW_MIO.'","'.TW_2017.'"';
$result = $phr_lst_ex->name($debug-1);
$exe_start_time = zu_test_show_result(', phrase_list->ex_measure of '.$phr_lst->name($debug-1), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$phr_lst_ex = clone $phr_lst;
$phr_lst_ex->ex_scaling($debug-1);
$target = '"'.TW_ABB.'","'.TW_SALES.'","'.TW_CHF.'","'.TW_2017.'"';
$result = $phr_lst_ex->name($debug-1);
$exe_start_time = zu_test_show_result(', phrase_list->ex_scaling of '.$phr_lst->name($debug-1), $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the pharse group class (classes/phrase_group.php)</h2><br>";

// test getting the group id based on ids
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->add_name(TW_SALES);
$wrd_lst->add_name(TW_CHF);
$wrd_lst->add_name(TW_MIO);
$wrd_lst->load($debug-1);
$abb_grp = New phrase_group;
$abb_grp->usr = $usr;
$abb_grp->ids = $wrd_lst->ids;
$abb_grp->load($debug-1);
$result = $abb_grp->id;
$target = '2116';
$exe_start_time = zu_test_show_result(', phrase_group->load by ids for '.implode(",",$wrd_lst->names()), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and if the time word is correctly excluded
$wrd_lst->add_name(TW_2014);
$wrd_lst->load($debug-1);
$abb_grp = New phrase_group;
$abb_grp->usr = $usr;
$abb_grp->ids = $wrd_lst->ids;
$abb_grp->load($debug-1);
$result = $abb_grp->id;
$target = '2116';
$exe_start_time = zu_test_show_result(', phrase_group->load by ids excluding time for '.implode(",",$wrd_lst->names()), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// load based on id
$abb_grp_reload = New phrase_group;
$abb_grp_reload->usr = $usr;
$abb_grp_reload->id = $abb_grp->id;
$abb_grp_reload->load($debug-1);
$abb_grp_reload->load_lst($debug-1);
$wrd_lst_reloaded = $abb_grp_reload->wrd_lst;
$result = implode(",",$wrd_lst_reloaded->names());
$target = 'million,CHF,Sales,ABB';
$exe_start_time = zu_test_show_result(', phrase_group->load for id '.$abb_grp->id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// if a new group is created in needed when a triple is added
$wrd_zh = New word;
$wrd_zh->name= TW_ZH;
$wrd_zh->usr = $usr;
$wrd_zh->load($debug-1);
$lnk_company = New word_link;
$lnk_company->from_id = $wrd_zh->id;
$lnk_company->verb_id = cl(SQL_LINK_TYPE_IS);
$lnk_company->to_id   = TEST_WORD_ID;
$lnk_company->usr  = $usr;
$lnk_company->load($debug-1);
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_SALES);
$wrd_lst->add_name(TW_CHF);
$wrd_lst->add_name(TW_MIO);
$wrd_lst->load($debug-1);
$zh_ins_grp = New phrase_group;
$zh_ins_grp->usr = $usr;
$zh_ins_grp->ids = $wrd_lst->ids;
$zh_ins_grp->ids[] = $lnk_company->id * - 1;
$result = $zh_ins_grp->get_id($debug-1);
$target = '3490';
$exe_start_time = zu_test_show_result(', phrase_group->load by ids for '.$lnk_company->name.' and '.implode(",",$wrd_lst->names()), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// test names
$result = implode(",",$zh_ins_grp->names($debug-1));
$target = 'million,CHF,Sales,Zurich Insurance';  // fix the issue after the libraries are excluded
//$target = 'million,CHF,Sales,'.TW_ZH_INS.'';
$exe_start_time = zu_test_show_result(', phrase_group->names', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test if the phrase group links are correctly recreated when a group is updated
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_2016);
$phr_lst->load($debug-1);
$grp = $phr_lst->get_grp($debug-1);
$grp_check = New phrase_group;
$grp_check->usr = $usr;
$grp_check->id  = $grp->id;
$grp_check->load($debug-1);
$result = $grp_check->load_link_ids($debug-1);
$target = $grp->ids;
$exe_start_time = zu_test_show_result(', phrase_group->load_link_ids for '.$phr_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// second test if the phrase group links are correctly recreated when a group is updated
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_MIO);
$phr_lst->add_name(TW_2016);
$phr_lst->load($debug-1);
$grp = $phr_lst->get_grp($debug-1);
$grp_check = New phrase_group;
$grp_check->usr = $usr;
$grp_check->id  = $grp->id;
$grp_check->load($debug-1);
$result = $grp_check->load_link_ids($debug-1);
$target = $grp->ids;
$exe_start_time = zu_test_show_result(', phrase_group->load_link_ids for '.$phr_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// test value
// test value_scaled


// load based on wrd and lnk lst
// load based on wrd and lnk ids
// maybe if cleanup removes the unneeded group

// test the user sandbox for the user names
// test if the search links are correctly created



echo "<h2>Test the pharse group list class (classes/phrase_group_list.php)</h2><br>";

// define some phrase groups for testing

// ABB Sales
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_MIO);
$phr_lst->load($debug-1);
$abb_grp = $phr_lst->get_grp($debug-1);

// Zurich taxes
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ZH);
$phr_lst->add_name(TW_TAX);
$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_MIO);
$phr_lst->load($debug-1);
$zh_grp = $phr_lst->get_grp($debug-1);

// Zurich Insurance taxes
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ZH_INS);
$phr_lst->add_name(TW_TAX);
$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_MIO);
$phr_lst->load($debug-1);
$ins_grp = $phr_lst->get_grp($debug-1);

// test add a phrase group to a phrase group list
$grp_lst = New phrase_group_list;
$grp_lst->usr = $usr;
$grp_lst->add($abb_grp, $debug-1);
$grp_lst->add($zh_grp, $debug-1);
$grp_lst->add($abb_grp, $debug-1);
$result = $grp_lst->name($debug-1);
$target = ''.TW_MIO.','.TW_CHF.','.TW_SALES.','.TW_ABB.' and '.TW_MIO.','.TW_CHF.','.TW_TAX.','.TW_ZH.'';
$exe_start_time = zu_test_show_result(', phrase_group_list->add of '.$abb_grp->name().', '.$zh_grp->name().', '.$abb_grp->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


// test add a phrase group to a phrase group list
$grp_lst = New phrase_group_list;
$grp_lst->usr = $usr;
$grp_lst->add($abb_grp, $debug-1);
$grp_lst->add($zh_grp, $debug-1);
$grp_lst->add($ins_grp, $debug-1);
$result = $grp_lst->name($debug-1);
$target = ''.TW_MIO.','.TW_CHF.','.TW_SALES.','.TW_ABB.' and '.TW_MIO.','.TW_CHF.','.TW_TAX.','.TW_ZH.' and '.TW_MIO.','.TW_CHF.','.TW_TAX.','.TW_ZH_INS.'';
$exe_start_time = zu_test_show_result(', phrase_group_list->add of '.$zh_grp->name().', '.$zh_grp->name().', '.$ins_grp->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// test getting the common phrases of several group
$grp_lst = New phrase_group_list;
$grp_lst->usr = $usr;
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ABB);
$wrd_lst->add_name(TW_SALES);
$wrd_lst->add_name(TW_CHF);
$wrd_lst->add_name(TW_MIO);
$wrd_lst->load($debug-1);
$abb_grp = $wrd_lst->get_grp($debug-1);
$grp_lst->add($abb_grp, $debug-1);
$wrd_lst = New word_list;
$wrd_lst->usr = $usr;
$wrd_lst->add_name(TW_ZH);
$wrd_lst->add_name(TW_TAX);
$wrd_lst->add_name(TW_CHF);
$wrd_lst->add_name(TW_MIO);
$wrd_lst->load($debug-1);
$zh_grp = $wrd_lst->get_grp($debug-1);
$grp_lst->add($zh_grp, $debug-1);
$phr_lst = $grp_lst->common_phrases($debug-1);
$result = $phr_lst->name($debug-1);
$target = '"'.TW_MIO.'","'.TW_CHF.'"';
$exe_start_time = zu_test_show_result(', phrase_group_list->common_phrases of '.$grp_lst->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the verb class (classes/verb.php)</h2><br>";

// check the loading of the "is a" verb
$vrb = New verb;
$vrb->id= cl(SQL_LINK_TYPE_IS);
$vrb->usr_id = $usr->id;
$vrb->load($debug-1);
$target = 'is a';
$result = $vrb->name;
$exe_start_time = zu_test_show_result(', verb->load ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the verb list class (classes/verb_list.php)</h2><br>";

// check the loading of the "is a" verb
$wrd_ABB = New word;
$wrd_ABB->name = TW_ABB;
$wrd_ABB->usr = $usr;
$wrd_ABB->load($debug-1);
$vrb_lst = $wrd_ABB->link_types ('up', $debug-1);
$target = 'is a';
$result = '';
// select the first verb
foreach ($vrb_lst->lst AS $vrb) {
  if ($result == '') {
    $result = $vrb->name;
  }
}
$exe_start_time = zu_test_show_result(', verb_list->load ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);



echo "<h2>Test the term class (classes/term.php)</h2><br>";

// check that adding the predefined word "Company" creates an error message
$term = New term;
$term->name= TEST_WORD;
$term->usr = $usr;
$term->load($debug-1);
$target = 'A word with the name "Company" already exists. Please use another name.';
$result = $term->id_used_msg($debug-1);
$exe_start_time = zu_test_show_result(', term->load for id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check also for a triple
$term = New term;
$term->name= 'Zurich (City)';
$term->usr = $usr;
$term->load($debug-1);
$target = 'A triple with the name "Zurich (City)" already exists. Please use another name.';
$result = $term->id_used_msg($debug-1);
$exe_start_time = zu_test_show_result(', term->load for id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check also for a verb
$term = New term;
$term->name= 'is a';
$term->usr = $usr;
$term->load($debug-1);
$target = 'A verb with the name "is a" already exists. Please use another name.';
$result = $term->id_used_msg($debug-1);
$exe_start_time = zu_test_show_result(', term->load for id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check also for a formula
$term = New term;
$term->name= TF_INCREASE;
$term->usr = $usr;
$term->load($debug-1);
// each formula name has also a word
$target = 'A formula with the name "increase" already exists. Please use another name.';
$result = $term->id_used_msg($debug-1);
$exe_start_time = zu_test_show_result(', term->load for id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the value class (classes/value.php)</h2><br>";

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
$target = '45548';
$exe_start_time = zu_test_show_result(', value->load for a tern list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test load by value id
$val = New value;
$val->id = $abb_sales->id;
$val->usr = $usr;
$val->load($debug-1);
$result = $val->number;
$target = '45548';
$exe_start_time = zu_test_show_result(', value->load for value id "'.$abb_sales->id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$exe_start_time = zu_test_show_result(', value->load for another word list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test load by value id
$val = New value;
$val->id = $abb_sales->id;
$val->usr = $usr;
$val->load($debug-1);
$result = $val->number;
$target = TV_ABB_SALES_2014;
$exe_start_time = zu_test_show_result(', value->load for value id "'.$abb_sales->id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test rebuild_grp_id by value id
$result = $val->check($debug-1);
$target = '';
$exe_start_time = zu_test_show_result(', value->check for value id "'.$abb_sales->id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// test another rebuild_grp_id by value id
$chk_wrd_lst = New word_list;
$chk_wrd_lst->usr = $usr;
$chk_wrd_lst->add_name(TW_ABB);
$chk_wrd_lst->add_name(TW_SALES);
$chk_wrd_lst->add_name(TW_CHF);
$chk_wrd_lst->add_name(TW_MIO);
$chk_wrd_lst->add_name(TW_2013);
$chk_wrd_lst->add_name('Discrete Automation and Motion');
$chk_wrd_lst->load($debug-1);
$chk_val = New value;
$chk_val->ids = $chk_wrd_lst->ids;
$chk_val->usr = $usr;
$chk_val->load($debug-1);
$result = $chk_val->check($debug-1);
$target = '';
$exe_start_time = zu_test_show_result(', value->check for value id "'.implode(",",$chk_wrd_lst->names()).'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... and check the number
$result = $chk_val->number;
$target = '9915';
$exe_start_time = zu_test_show_result(', value->load for "'.implode(',',$chk_wrd_lst->names()).'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and check the words loaded
$result = implode(',',$chk_val->wrd_lst->names());
$target = 'million,CHF,Sales,ABB,Discrete Automation and Motion';
$exe_start_time = zu_test_show_result(', value->load words', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and check the time word
$result = $chk_val->time_phr->name;
$target = TW_2013;
$exe_start_time = zu_test_show_result(', value->load time word', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and check the word reloading by group
$chk_val->wrd_lst = Null;
$chk_val->load_phrases($debug-1);
if (isset($chk_val->wrd_lst)) {
  $result = implode(',',$chk_val->wrd_lst->names());
} else {
  $result = '';
}
$target = 'million,CHF,Sales,ABB,Discrete Automation and Motion';
$exe_start_time = zu_test_show_result(', value->load_phrases reloaded words', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and check the time word reloading
$chk_val->time_phr = Null;
$chk_val->load_phrases($debug-1);
if (isset($chk_val->time_phr)) {
  $result = $chk_val->time_phr->name;
} else {
  $result = '';
}
$target = TW_2013;
$exe_start_time = zu_test_show_result(', value->load_phrases reloaded time word', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
if (isset($val->wrd_lst)) {
  $result = implode(',',$val->wrd_lst->names($debug-1));
} else {
  $result = '';
}
$target = implode(',',$wrd_lst->names($debug-1));
$exe_start_time = zu_test_show_result(', value->load for group id "'.$grp->id.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test load the word list object via word ids
$val->grp = 0;
$val->wrd_ids = $wrd_lst->ids;
$val->load($debug-1);
if (isset($val->wrd_lst)) {
  $result = implode(',',$val->wrd_lst->names($debug-1));
} else {
  $result = '';
}
$target = implode(',',$wrd_lst->names($debug-1));
$exe_start_time = zu_test_show_result(', value->load for ids '.implode(',',$wrd_lst->ids).'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$exe_start_time = zu_test_show_result(', value->val_formatted for a word list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$dest_wrd_lst->add_name('Thousand');
$dest_wrd_lst->load($debug-1);
$mio_val = New value;
$mio_val->ids = $wrd_lst->ids;
$mio_val->usr = $usr;
$mio_val->load($debug-1);
$result = $mio_val->scale($dest_wrd_lst, $debug-1);
$target = '46000000000';
$exe_start_time = zu_test_show_result(', value->val_scaling for a word list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$target = '<a href="/http/value_edit.php?id=171&back=1">46\'000</a>';
$exe_start_time = zu_test_show_result(', value->figure->display_linked for word list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the HTML code creation
$result = $mio_val->display($back, $debug-1);
$target = number_format(TV_ABB_SALES_2014,0,DEFAULT_DEC_POINT,DEFAULT_THOUSAND_SEP);
$exe_start_time = zu_test_show_result(', value->display', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the HTML code creation including the hyperlink
$result = $mio_val->display_linked('1', $$debug-1);
$target = '<a href="/http/value_edit.php?id=171&back=1">46\'000</a>';
$exe_start_time = zu_test_show_result(', value->display_linked', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// convert the user input for the database
$mio_val->usr_value = '46 000';
$result = $mio_val->convert($debug-1);
$target = TV_ABB_SALES_2014;
$exe_start_time = zu_test_show_result(', value->convert user input', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$exe_start_time = zu_test_show_result(', value->save '.$add_val->number.' for '.$wrd_lst->name().' by user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

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
$exe_start_time = zu_test_show_result(', value->save logged for "'.$wrd_lst->name().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the value has been added
$added_val = New value;
$added_val->ids = $phr_lst->ids;
$added_val->usr = $usr;
$added_val->load($debug-1);
$result = $added_val->number;
$target = '123456789';
$exe_start_time = zu_test_show_result(', value->load the value previous saved for "'.$wrd_lst->name().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
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
$exe_start_time = zu_test_show_result(', value->save '.$add_val2->number.' for '.$wrd_lst2->name().' by user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

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
$exe_start_time = zu_test_show_result(', value->save logged for "'.$wrd_lst2->name().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the value has been added
$added_val2 = New value;
$added_val2->ids = $phr_lst2->ids;
$added_val2->usr = $usr;
$added_val2->load($debug-1);
$result = $added_val2->number;
$target = '234567890';
$exe_start_time = zu_test_show_result(', value->load the value previous saved for "'.$phr_lst2->name().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
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
$exe_start_time = zu_test_show_result(', word->save update value id "'.$added_val_id.'" from  "'.$add_val->number.'" to "'.$added_val->number.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

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
$exe_start_time = zu_test_show_result(', value->save logged for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the value has really been updated
$added_val = New value;
$added_val->ids = $phr_lst->ids;
$added_val->usr = $usr;
$added_val->load($debug-1);
$result = $added_val->number;
$target = '987654321';
$exe_start_time = zu_test_show_result(', value->load the value previous updated for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

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
$exe_start_time = zu_test_show_result(', value->save '.$val_usr2->number.' for '.$wrd_lst->name().' and user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

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
$exe_start_time = zu_test_show_result(', value->save logged for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the value has really been updated
$added_val_usr2 = New value;
$added_val_usr2->ids = $phr_lst->ids;
$added_val_usr2->usr = $usr2;
$added_val_usr2->load($debug-1);
$result = $added_val_usr2->number;
$target = '23456';
$exe_start_time = zu_test_show_result(', value->load the value previous updated for "'.$wrd_lst->name().'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

// ... check if the value for the original user remains unchanged
$added_val = New value;
$added_val->ids = $phr_lst->ids;
$added_val->usr = $usr;
$added_val->load($debug-1);
$result = $added_val->number;
$target = '987654321';
$exe_start_time = zu_test_show_result(', value->load for user "'.$usr->name.'" is still', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

// check if undo all specific changes removes the user value
$added_val_usr2 = New value;
$added_val_usr2->ids = $phr_lst->ids;
$added_val_usr2->usr = $usr2;
$added_val_usr2->load($debug-1);
$added_val_usr2->number = 987654321;
$result = $added_val_usr2->save($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', value->save change to '.$val_usr2->number.' for '.$wrd_lst->name().' and user "'.$usr2->name.'" should undo the user change', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

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
$exe_start_time = zu_test_show_result(', value->save logged for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the value has really been changed back
$added_val_usr2 = New value;
$added_val_usr2->ids = $phr_lst->ids;
$added_val_usr2->usr = $usr2;
$added_val_usr2->load($debug-1);
$result = $added_val_usr2->number;
$target = '987654321';
$exe_start_time = zu_test_show_result(', value->load the value previous updated for "'.$wrd_lst->name().'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

// test adding a value
// if the word is not used, the user can add or remove words
// if a value is used adding adding another word should create a new value
// but if the new value with the added word already exists the values should be merged after a confirmation by the user

// test to remove a word from the value
/*$added_val = New value;
$added_val->id = $added_val_id;
$added_val->usr = $usr;
$added_val->load($debug-1);
$wrd_to_del = New word;
$wrd_to_del->name = TW_CHF;
$wrd_to_del->usr = $usr;
$wrd_to_del->load($debug-1);
$result = $added_val->del_wrd($wrd_to_del->id, $debug-1);
$wrd_lst = $added_val->wrd_lst;
$result = $wrd_lst->does_contain(TW_CHF, $debug-1);
$target = false;
$exe_start_time = zu_test_show_result(', value->add_wrd has "'.TW_CHF.'" been removed from the word list of the value', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

// test to link an additional word to a value
$added_val = New value;
$added_val->id = $added_val_id;
$added_val->usr = $usr;
$added_val->load($debug-1);
$wrd_to_add = New word;
$wrd_to_add->name = TW_EUR;
$wrd_to_add->usr = $usr;
$wrd_to_add->load($debug-1);
$result = $added_val->add_wrd($wrd_to_add->id, $debug-1);
// load word list
$wrd_lst = $added_val->wrd_lst;
// does the word list contain TW_EUR
$result = $wrd_lst->does_contain(TW_EUR, $debug-1);
$target = true;
$exe_start_time = zu_test_show_result(', value->add_wrd has "'.TW_EUR.'" been added to the word list of the value', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
*/


echo "<h2>Test the value frontend scripts (e.g. /value_add.php)</h2><br>";

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
$result = file_get_contents('https://zukunft.com/http/value_add.php?back='.$back.$phr_lst_added->id_url().'');
$target = TW_ADD_RENAMED;
$exe_start_time = zu_test_show_contains(', frontend value_add.php '.$result.' contains at least '.TW_ADD_RENAMED, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

$result = file_get_contents('https://zukunft.com/http/value_add.php?back='.$back.$phr_lst_abb->id_url().'');
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', frontend value_add.php '.$result.' contains at least '.TW_ABB, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

// test the edit value frontend
$result = file_get_contents('https://zukunft.com/http/value_edit.php?id='.$val_added->id.'&back='.$back.'');
$target = TW_ADD_RENAMED;
$exe_start_time = zu_test_show_contains(', frontend value_edit.php '.$result.' contains at least '.TW_ADD_RENAMED, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

$result = file_get_contents('https://zukunft.com/http/value_edit.php?id='.$val_ABB->id.'&back='.$back.'');
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', frontend value_edit.php '.$result.' contains at least '.TW_ABB, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

// test the del value frontend
$result = file_get_contents('https://zukunft.com/http/value_del.php?id='.$val_added->id.'&back='.$back.'');
$target = TW_ADD_RENAMED;
$exe_start_time = zu_test_show_contains(', frontend value_del.php '.$result.' contains at least '.TW_ADD_RENAMED, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

$result = file_get_contents('https://zukunft.com/http/value_del.php?id='.$val_ABB->id.'&back='.$back.'');
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', frontend value_del.php '.$result.' contains at least '.TW_ABB, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


echo "<h2>Test the value list class (classes/value_list.php)</h2><br>";

// check the database consistency for all values
$val_lst = New value_list;
$val_lst->usr = $usr;
$result = $val_lst->check_all($debug-1);
$target = '';
$exe_start_time = zu_test_show_result(', value_list->check_all', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

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
$exe_start_time = zu_test_show_result(', word_list->get_grp for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
$val = $val_lst->get_by_grp($grp, $wrd_time, $debug-1);
$result = $val->number;
$target = TV_ABB_SALES_2014;
$exe_start_time = zu_test_show_result(', value_list->get_by_grp for '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

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
$exe_start_time = zu_test_show_result(', value_list->time_lst is '.$time_lst->name().', which includes '.$wrd_2014->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    
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
$exe_start_time = zu_test_show_result(', value_list->time_lst is '.$used_time_lst->name().', which does not include '.$wrd_2014->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$exe_start_time = zu_test_show_result(', value_list->filter_by_phrase_lst is '.$used_time_lst->name().', but includes '.$wrd_2016->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
$exe_start_time = zu_test_show_result(', value_list->filter_by_phrase_lst is '.$used_phr_lst->name().', which does not include '.$wrd_auto->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT); 

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
$exe_start_time = zu_test_show_result(', value_list->filter_by_phrase_lst is '.$used_phr_lst->name().', but includes '.$wrd_power->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the value list display class (classes/value_list_display.php)</h2><br>";

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
$target = '89\'469';
$exe_start_time = zu_test_show_contains(', value_list_dsp->dsp_table for "'.$wrd->name.'" ('.$result.') contains '.$target.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);
//$result = $val_lst->dsp_table($wrd_col, $wrd->id, $debug-1);
//$target = zuv_table ($wrd->id, $wrd_col->id, $usr->id, $debug-1);
//$exe_start_time = zu_test_show_result(', value_list_dsp->dsp_table for "'.$wrd->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);


echo "<h2>Test the source class (classes/source.php)</h2><br>";

$src = New source;
$src->id = TS_NESN_2016_ID;
$src->usr = $usr;
$src->load($debug-1);
$result = $src->name;
$target = TS_NESN_2016_NAME;
$exe_start_time = zu_test_show_result(', source->load of ID "'.TS_NESN_2016_ID.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);


echo "<h2>Test the expression class (classes/expression.php)</h2><br>";

// load formulas for expression testing
$frm = New formula;
$frm->name = TF_INCREASE;
$frm->usr = $usr;
$frm->load($debug-1);

$frm_pe = New formula;
$frm_pe->name = TF_PE;
$frm_pe->usr = $usr;
$frm_pe->load($debug-1);

$frm_sector = New formula;
$frm_sector->name = TF_SECTOR;
$frm_sector->usr = $usr;
$frm_sector->load($debug-1);

// create expressions for testing
$exp = New expression;
$exp->usr_text = $frm->usr_text;
$exp->usr = $usr;
$exp->ref_text = $exp->get_ref_text ($debug-1);

$exp_pe = New expression;
$exp_pe->usr_text = $frm_pe->usr_text;
$exp_pe->usr = $usr;
$exp_pe->ref_text = $exp_pe->get_ref_text ($debug-1);

$exp_sector = New expression;
$exp_sector->usr_text = $frm_sector->usr_text;
$exp_sector->usr = $usr;
$exp_sector->ref_text = $exp_sector->get_ref_text ($debug-1);

// test the expression processing of the user readable part
$target = '"percent"';
$result = $exp->fv_part_usr ($debug-1);
$exe_start_time = zu_test_show_result(', expression->fv_part_usr for "'.$frm->usr_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG); // ??? why???
$target = '( "this" - "prior" ) / "prior"';
$result = $exp->r_part_usr ($debug-1);
$exe_start_time = zu_test_show_result(', expression->r_part_usr for "'.$frm->usr_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$target = 'true';
$result = zu_dsp_bool($exp->has_ref ($debug-1));
$exe_start_time = zu_test_show_result(', expression->has_ref for "'.$frm->usr_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$target = '{t19}=({f3}-{f5})/{f5}';
$result = $exp->get_ref_text ($debug-1);
$exe_start_time = zu_test_show_result(', expression->get_ref_text for "'.$frm->usr_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the expression processing of the database reference
$exp_db = New expression;
$exp_db->ref_text = '{t19} = ( is.numeric( {f3} ) & is.numeric( {f5} ) ) ( {f3} - {f5} ) / {f5}';
$exp_db->usr = $usr;
$target = '{t19}';
$result = $exp_db->fv_part ($debug-1);
$exe_start_time = zu_test_show_result(', expression->fv_part_usr for "'.$exp_db->ref_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$target = '( is.numeric( {f3} ) & is.numeric( {f5} ) ) ( {f3} - {f5} ) / {f5}';
$result = $exp_db->r_part ($debug-1);
$exe_start_time = zu_test_show_result(', expression->r_part_usr for "'.$exp_db->ref_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$target = '"percent"=( is.numeric( "this" ) & is.numeric( "prior" ) ) ( "this" - "prior" ) / "prior"';
$result = $exp_db->get_usr_text ($debug-1);
$exe_start_time = zu_test_show_result(', expression->get_usr_text for "'.$exp_db->ref_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test getting phrases that should be added to the result of a formula
$phr_lst_fv = $exp->fv_phr_lst ($debug-1);
$result = $phr_lst_fv->name ($debug-1);
$target = '"percent"';
$exe_start_time = zu_test_show_result(', expression->fv_phr_lst for "'.$exp->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG); // ??? why???

// ... and the phrases used in the formula
$phr_lst_fv = $exp_pe->phr_lst ($debug-1);
$result = $phr_lst_fv->name ($debug-1);
$target = '"Share price"';
$exe_start_time = zu_test_show_result(', expression->phr_lst for "'.$exp_pe->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and all elements used in the formula
$elm_lst = $exp_sector->element_lst ($back, $debug-1);
$result = $elm_lst->name ($debug-1);
$target = 'Sales can be used as a differentiator for Sector Total Sales ';
$exe_start_time = zu_test_show_result(', expression->element_lst for "'.$exp_sector->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT); 

// ... and all element groups used in the formula
$elm_grp_lst = $exp_sector->element_grp_lst ($back, $debug-1);
$result = $elm_grp_lst->name ($debug-1);
$target = 'Sales,can be used as a differentiator for,Sector / Total Sales';
$exe_start_time = zu_test_show_result(', expression->element_grp_lst for "'.$exp_sector->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test getting the phrases if the formula contains a verb
// not sure if test is correct!
$phr_lst = $exp_sector->phr_verb_lst($back, $debug-1);
$result = $phr_lst->name($debug-1);
$target = '"Sales","Sector","Total Sales"';
$exe_start_time = zu_test_show_result(', expression->phr_verb_lst for "'.$exp_sector->ref_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test getting special phrases
$phr_lst = $exp->element_special_following ($debug-1);
$result = $phr_lst->name ($debug-1);
$target = '"this","prior"';
$exe_start_time = zu_test_show_result(', expression->element_special_following for "'.$exp->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// test getting for special phrases the related formula 
$frm_lst = $exp->element_special_following_frm ($debug-1);
$result = $frm_lst->name ($debug-1);
$target = 'this,prior';
$exe_start_time = zu_test_show_result(', expression->element_special_following_frm for "'.$exp->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);



echo "<h2>Test the formula class (classes/formula.php)</h2><br>";

// test loading of one formula
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_INCREASE;
$frm->load($debug-1);
$result = $frm->usr_text;
$target = '"percent" = ( "this" - "prior" ) / "prior"';
$exe_start_time = zu_test_show_result(', formula->load for "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the formula type
$result = zu_dsp_bool($frm->is_special($debug-1));
$target = zu_dsp_bool(false);
$exe_start_time = zu_test_show_result(', formula->is_special for "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$exp = $frm->expression($debug-1);
$frm_lst = $exp->element_special_following_frm($debug-1);
if (count($frm_lst->lst) > 0) {
  $elm_frm = $frm_lst->lst[0];
  $result = zu_dsp_bool($elm_frm->is_special($debug-1));
  $target = zu_dsp_bool(true);
  $exe_start_time = zu_test_show_result(', formula->is_special for "'.$elm_frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ABB);
  $phr_lst->add_name(TW_SALES);
  $phr_lst->add_name(TW_2014);
  $phr_lst->load($debug-1);
  $time_phr = $phr_lst->time_useful($debug-1);
  //echo $time_phr->name().'<br>';
  $val = $elm_frm->special_result($phr_lst, $time_phr, $debug-1);
  $result = $val->number;
  //echo $result.'<br>';
  $target = TW_2016;
  // todo: get the best matching number
  //$exe_start_time = zu_test_show_result(', formula->special_result for "'.$elm_frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  if (count($frm_lst->lst) > 1) {
    //$elm_frm_next = $frm_lst->lst[1];
    $elm_frm_next = $elm_frm;
  } else {
    $elm_frm_next = $elm_frm;
  }
  $time_phr = $elm_frm_next->special_time_phr($time_phr, $debug-1);
  $result = $time_phr->name;
  $target = TW_2015; // todo: check why $elm_frm_next = $frm_lst->lst[1]; is not working
  $target = TW_2014;
  $exe_start_time = zu_test_show_result(', formula->special_time_phr for "'.$elm_frm_next->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
}

$phr_lst = $frm->special_phr_lst($phr_lst, $debug-1);
$result = $phr_lst->name();
$target = '"'.TW_ABB.'","'.TW_SALES.'","'.TW_2014.'"';
$exe_start_time = zu_test_show_result(', formula->special_phr_lst for "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$phr_lst = $frm->assign_phr_lst_direct($debug-1);
$result = $phr_lst->name();
$target = '"Year"';
$exe_start_time = zu_test_show_result(', formula->assign_phr_lst_direct for "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$phr_lst = $frm->assign_phr_ulst_direct($debug-1);
$result = $phr_lst->name();
$target = '"Year"';
$exe_start_time = zu_test_show_result(', formula->assign_phr_ulst_direct for "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// loading another formula (Price Earning ratio ) to have more test cases
$frm_pe = New formula;
$frm_pe->usr = $usr;
$frm_pe->name = TF_PE;
$frm_pe->load($debug-1);

$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_2014);
$phr_lst->load($debug-1);
  
$phr_lst_all = $frm_pe->assign_phr_lst($debug-1);
$phr_lst = $phr_lst_all->filter($phr_lst, $debug-1);
$result = $phr_lst->name($debug-1);
$target = '"'.TW_ABB.'"';
$exe_start_time = zu_test_show_result(', formula->assign_phr_lst for "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$phr_lst_all = $frm_pe->assign_phr_ulst($debug-1);
$phr_lst = $phr_lst_all->filter($phr_lst, $debug-1);
$result = $phr_lst->name();
$target = '"'.TW_ABB.'"';
$exe_start_time = zu_test_show_result(', formula->assign_phr_ulst for "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the calculation of one value
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_2014);
// why are these two words needed??
$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_MIO);
$phr_lst->load($debug-1);

$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_INCREASE;
$frm->load($debug-1);

$fv_lst = $frm->to_num($phr_lst, $debug-1);
if (isset($fv_lst->lst)) {
  $fv = $fv_lst->lst[0];
  $result = $fv->num_text;
} else {
  $fv = Null;
  $result = 'result list is empty';
}
$target = '=(46000-45548)/45548';
$exe_start_time = zu_test_show_result(', formula->to_num "'.$frm->name.'" for a tern list '.$phr_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

if (isset($fv_lst->lst)) {
  $fv = $fv->save_if_updated($debug-1);
  $result = $fv->value;
  $target = '0.0099236';
  $exe_start_time = zu_test_show_result(', formula_value->save_if_updated "'.$frm->name.'" for a tern list '.$phr_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
}

$fv_lst = $frm->calc($phr_lst, $debug-1);
if (isset($fv_lst)) {
  $result = $fv_lst[0]->value;
} else {
  $result = '';
}
$target = '0.0099235970843945';
$exe_start_time = zu_test_show_result(', formula->calc "'.$frm->name.'" for a tern list '.$phr_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the display functions
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_INCREASE;
$frm->load($debug-1);
$exp = $frm->expression($debug-1);
$result = $exp->dsp_id();
$target = '""percent" = ( "this" - "prior" ) / "prior"" ({t19}=({f3}-{f5})/{f5})';
$exe_start_time = zu_test_show_result(', formula->expression for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$result = $frm->name();
$target = 'increase';
$exe_start_time = zu_test_show_result(', formula->name for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$result = $frm->dsp_text($back, $debug-1);
$target = '"percent" = ( <a href="/http/formula_edit.php?id=3&back=1">this</a> - <a href="/http/formula_edit.php?id=5&back=1">prior</a> ) / <a href="/http/formula_edit.php?id=5&back=1">prior</a>';
$exe_start_time = zu_test_show_result(', formula->dsp_text for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$result = $frm->name_linked($back, $debug-1);
$target = '<a href="/http/formula_edit.php?id=52&back=1">increase</a>';
$exe_start_time = zu_test_show_result(', formula->display for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$wrd = New word_dsp;
$wrd->usr = $usr;
$wrd->name = TW_ABB;
$wrd->load($debug-1);
$result = trim($frm->dsp_result($wrd, $back, $debug-1));
$target = '0.99 %';
$target = '0.01';
$exe_start_time = zu_test_show_result(', formula->dsp_result for '.$frm->name().' and '.$wrd->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$result = $frm->btn_edit();
$target = '<a href="/http/formula_edit.php?id=52&back=" title="Change formula increase"><img src="../images/button_edit.svg" alt="Change formula increase"></a>';
$exe_start_time = zu_test_show_result(', formula->btn_edit for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$page = 1;
$size = 20;
$call = '/http/test.php';
$result = $frm->dsp_hist($page, $size, $call, $back, $debug-1);
$target = 'from';
$exe_start_time = zu_test_show_contains(', formula->dsp_hist for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$result = $frm->dsp_hist_links($page, $size, $call, $back, $debug-1);
$target = 'link';
//$result = $hist_page;
$exe_start_time = zu_test_show_contains(', formula->dsp_hist_links for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$add = 0;
$result = $frm->dsp_edit($add, $wrd, $back, $debug-1);
$target = 'Formula "increase"';
//$result = $edit_page;
$exe_start_time = zu_test_show_contains(', formula->dsp_edit for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// test formula refresh functions

$result = $frm->element_refresh($frm->ref_text, $debug-1);
$target = '';
$exe_start_time = zu_test_show_result(', formula->element_refresh for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// to link and unlink a formula is tested in the formula_link section

// test adding of one formula
$frm = New formula;
$frm->name = TF_ADD;
$frm->usr_text = '"percent" = ( "this" - "prior" ) / "prior"';
$frm->usr = $usr;
$result = $frm->save($debug10);
if ($frm->id > 0) {
  $result = $frm->usr_text;
}
$target = '"percent" = ( "this" - "prior" ) / "prior"';
$exe_start_time = zu_test_show_result(', formula->save for adding "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the formula name has been saved
$frm = New formula;
$frm->name = TF_ADD;
$frm->usr = $usr;
$frm->load($debug-1);
$result = $frm->usr_text;
$target = '"percent" = ( "this" - "prior" ) / "prior"';
$exe_start_time = zu_test_show_result(', formula->load the added "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); // time limit???

// ... check the correct logging
$log = New user_log;
$log->table = 'formulas';
$log->field = 'formula_name';
$log->row_id = $frm->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Test Formula';
$exe_start_time = zu_test_show_result(', formula->save adding logged for "'.TF_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if adding the same formula again creates a correct error message
$frm = New formula;
$frm->name = TF_ADD;
$frm->usr_text = '"percent" = 1 - ( "this" / "prior" )';
$frm->usr = $usr;
$result = $frm->save($debug-1);
// use the next line if system config is non standard
$target = 'A formula with the name "'.TF_ADD.'" already exists. Please use another name.';
$target = '11111';
$exe_start_time = zu_test_show_result(', formula->save adding "'.$frm->name.'" again', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the formula linked word has been created
$wrd = New word;
$wrd->name = TF_ADD;
$wrd->usr = $usr;
$wrd->load($debug-1);
$result = $wrd->type_id;
$target = cl(SQL_WORD_TYPE_FORMULA_LINK);
$exe_start_time = zu_test_show_result(', word->load of the word "'.$frm->name.'" has the formula type', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// check if the formula can be renamed
$frm = New formula;
$frm->name = TF_ADD;
$frm->usr = $usr;
$frm->load($debug-1);
$frm->name = TF_ADD_RENAMED;
$result = $frm->save($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', formula->save rename "'.TF_ADD.'" to "'.TF_ADD_RENAMED.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... and if the formula renaming was successful
$frm_renamed = New formula;
$frm_renamed->name = TF_ADD_RENAMED;
$frm_renamed->usr = $usr;
$result = $frm_renamed->load($debug-1);
if ($result == '') {
  if ($frm_renamed->id > 0) {
    $result = $frm_renamed->name;
  }
}
$target = TF_ADD_RENAMED;
$exe_start_time = zu_test_show_result(', formula->load renamed formula "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and if the formula renaming has been logged
$log = New user_log;
$log->table = 'formulas';
$log->field = 'formula_name';
$log->row_id = $frm_renamed->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job changed Test Formula to Formula Test';
$exe_start_time = zu_test_show_result(', formula->save rename logged for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the formula parameters can be added
$frm_renamed->usr_text     = '= "this"';
$frm_renamed->description  = TF_ADD_RENAMED.' description';
$frm_renamed->type_id      = cl(SQL_FORMULA_TYPE_THIS);
$frm_renamed->need_all_val = True;
$result = $frm_renamed->save($debug-0);
$target = '1111111';
$exe_start_time = zu_test_show_result(', formula->save all formula fields beside the name for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... and if the formula parameters have been added
$frm_reloaded = New formula;
$frm_reloaded->name = TF_ADD_RENAMED;
$frm_reloaded->usr = $usr;
$frm_reloaded->load($debug-1);
$result = $frm_reloaded->usr_text;
$target = '= "this"';
$exe_start_time = zu_test_show_result(', formula->load usr_text for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_reloaded->ref_text;
$target = '={f3}';
$exe_start_time = zu_test_show_result(', formula->load ref_text for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_reloaded->description;
$target = TF_ADD_RENAMED.' description';
$exe_start_time = zu_test_show_result(', formula->load description for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_reloaded->type_id;
$target = cl(SQL_FORMULA_TYPE_THIS);
$exe_start_time = zu_test_show_result(', formula->load type_id for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_reloaded->need_all_val;
$target = True;
$exe_start_time = zu_test_show_result(', formula->load need_all_val for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and if the formula parameter adding have been logged
$log = New user_log;
$log->table = 'formulas';
$log->field = 'resolved_text';
$log->row_id = $frm_reloaded->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
// use the next line if system config is non standard
$target = 'zukunft.com system batch job changed "percent" = ( "this" - "prior" ) / "prior" to = "this"';
$target = 'zukunft.com system batch job changed "percent" = 1 - ( "this" / "prior" ) to = "this"';
$exe_start_time = zu_test_show_result(', formula->load resolved_text for "'.TF_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'formula_text';
$result = $log->dsp_last(true, $debug-1);
// use the next line if system config is non standard
$target = 'zukunft.com system batch job changed {t19}=( {f3} - {f5} ) / {f5} to ={f3}';
$target = 'zukunft.com system batch job changed {t19}=1-({f3}/{f5}) to ={f3}';
$exe_start_time = zu_test_show_result(', formula->load formula_text for "'.TF_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'description';
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Formula Test description';
$exe_start_time = zu_test_show_result(', formula->load description for "'.TF_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'formula_type_id';
$result = $log->dsp_last(true, $debug-1);
// to review what is correct
$target = 'zukunft.com system batch job changed calc to this';
$target = 'zukunft.com system batch job added this';
$exe_start_time = zu_test_show_result(', formula->load formula_type_id for "'.TF_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'all_values_needed';
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job changed 0 to 1';
$exe_start_time = zu_test_show_result(', formula->load all_values_needed for "'.TF_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if a user specific formula is created if another user changes the formula
$frm_usr2 = New formula;
$frm_usr2->name = TF_ADD_RENAMED;
$frm_usr2->usr = $usr2;
$frm_usr2->load($debug-1);
$frm_usr2->usr_text     = '"percent" = ( "this" - "prior" ) / "prior"';
$frm_usr2->description  = TF_ADD_RENAMED.' description2';
$frm_usr2->type_id      = cl(SQL_FORMULA_TYPE_NEXT);
$frm_usr2->need_all_val = False;
$result = $frm_usr2->save($debug-1);
$target = '1111111111';
$exe_start_time = zu_test_show_result(', formula->save all formula fields for user 2 beside the name for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... and if a user specific formula changes have been saved
$frm_usr2_reloaded = New formula;
$frm_usr2_reloaded->name = TF_ADD_RENAMED;
$frm_usr2_reloaded->usr = $usr2;
$frm_usr2_reloaded->load($debug-1);
$result = $frm_usr2_reloaded->usr_text;
$target = '"percent" = ( "this" - "prior" ) / "prior"';
$exe_start_time = zu_test_show_result(', formula->load usr_text for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_usr2_reloaded->ref_text;
$target = '{t19}=({f3}-{f5})/{f5}';
$exe_start_time = zu_test_show_result(', formula->load ref_text for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_usr2_reloaded->description;
$target = TF_ADD_RENAMED.' description2';
$exe_start_time = zu_test_show_result(', formula->load description for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_usr2_reloaded->type_id;
$target = cl(SQL_FORMULA_TYPE_NEXT);
$exe_start_time = zu_test_show_result(', formula->load type_id for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_usr2_reloaded->need_all_val;
$target = False;
$exe_start_time = zu_test_show_result(', formula->load need_all_val for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... and the formula for the original user remains unchanged
$frm_reloaded = New formula;
$frm_reloaded->name = TF_ADD_RENAMED;
$frm_reloaded->usr = $usr;
$frm_reloaded->load($debug-1);
$result = $frm_reloaded->usr_text;
$target = '= "this"';
$exe_start_time = zu_test_show_result(', formula->load usr_text for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_reloaded->ref_text;
$target = '={f3}';
$exe_start_time = zu_test_show_result(', formula->load ref_text for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_reloaded->description;
$target = TF_ADD_RENAMED.' description';
$exe_start_time = zu_test_show_result(', formula->load description for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_reloaded->type_id;
$target = cl(SQL_FORMULA_TYPE_THIS);
$exe_start_time = zu_test_show_result(', formula->load type_id for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_reloaded->need_all_val;
$target = True;
$exe_start_time = zu_test_show_result(', formula->load need_all_val for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if undo all specific changes removes the user formula
$frm_usr2 = New formula;
$frm_usr2->name = TF_ADD_RENAMED;
$frm_usr2->usr = $usr2;
$frm_usr2->load($debug-1);
$frm_usr2->usr_text     = '= "this"';
$frm_usr2->description  = TF_ADD_RENAMED.' description';
$frm_usr2->type_id      = cl(SQL_FORMULA_TYPE_THIS);
$frm_usr2->need_all_val = True;
$result = $frm_usr2->save($debug-1);
$target = '111111111';
$exe_start_time = zu_test_show_result(', formula->save undo the user formula fields beside the name for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... and if a user specific formula changes have been saved
$frm_usr2_reloaded = New formula;
$frm_usr2_reloaded->name = TF_ADD_RENAMED;
$frm_usr2_reloaded->usr = $usr2;
$frm_usr2_reloaded->load($debug-1);
$result = $frm_usr2_reloaded->usr_text;
$target = '= "this"';
$exe_start_time = zu_test_show_result(', formula->load usr_text for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_usr2_reloaded->ref_text;
$target = '={f3}';
$exe_start_time = zu_test_show_result(', formula->load ref_text for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_usr2_reloaded->description;
$target = TF_ADD_RENAMED.' description';
$exe_start_time = zu_test_show_result(', formula->load description for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_usr2_reloaded->type_id;
$target = cl(SQL_FORMULA_TYPE_THIS);
$exe_start_time = zu_test_show_result(', formula->load type_id for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $frm_usr2_reloaded->need_all_val;
$target = True;
$exe_start_time = zu_test_show_result(', formula->load need_all_val for "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// redo the user specific formula changes
// check if the user specific changes can be removed with one click

// check for formulas also that 



echo "<h2>Test the formula calculation triggers</h2><br>";

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
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_INCREASE;
$frm->load($debug-1);

// add a number to the test word
$val_add1 = New value;
$val_add1->ids = $phr_lst1->ids;
$val_add1->number = 1234;
$val_add1->usr = $usr;
$result = $val_add1->save($debug-1);
// add a second number to the test word
$val_add2 = New value;
$val_add2->ids = $phr_lst2->ids;
$val_add2->number = 2345;
$val_add2->usr = $usr;
$result = $val_add2->save($debug-1);

// calculate the increase and check the result
$fv_lst = $frm->calc($phr_lst2, $debug-1);
if (count($fv_lst) > 0) {
  $fv = $fv_lst[0];
  $result = trim($fv->display($back, $debug-1));
} else {
  $result = '';
}
$target = '90.03 %';
$exe_start_time = zu_test_show_result(', formula result for '.$frm->dsp_id().' from '.$phr_lst1->dsp_id().' to '.$phr_lst2->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// remove the test values
$val_add1->del($debug-1);
$val_add2->del($debug-1);

// change the second number and test if the result has been updated
// a second user changes the value back to the originalvalue and check if for the second number the result is updated
// check if the result for the first user is not changed
// the first user also changes back the value to the original value and now the values for both user should be the same


echo "<h2>Test the formula frontend scripts (e.g. /formula_add.php)</h2><br>";

// call the add formula page and check if at least some keywords are returned
$frm = New formula;
$frm->name = TF_INCREASE;
$frm->usr = $usr;
$frm->load($debug-1);
$result = file_get_contents('https://zukunft.com/http/formula_add.php?word='.TEST_WORD_ID.'&back='.TEST_WORD_ID.'');
$target = 'Add new formula for';
$exe_start_time = zu_test_show_contains(', frontend formula_add.php '.$result.' contains at least the headline', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);
$target = TEST_WORD;
$exe_start_time = zu_test_show_contains(', frontend formula_add.php '.$result.' contains at least the linked word '.TEST_WORD, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// test the edit formula frontend
$result = file_get_contents('https://zukunft.com/http/formula_edit.php?id='.$frm->id.'&back='.TEST_WORD_ID.'');
$target = TF_INCREASE;
$exe_start_time = zu_test_show_contains(', frontend formula_edit.php '.$result.' contains at least '.$frm->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

// test the del formula frontend
$result = file_get_contents('https://zukunft.com/http/formula_del.php?id='.$frm->id.'&back='.TEST_WORD_ID.'');
$target = TF_INCREASE;
$exe_start_time = zu_test_show_contains(', frontend formula_del.php '.$result.' contains at least '.$frm->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


echo "<h2>Test the formula link class (classes/formula_link.php)</h2><br>";

// link the test formula to another word
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr = New phrase;
$phr->name = TW_ADD_RENAMED;
$phr->usr = $usr2;
$phr->load($debug-1);
$result = $frm->link_phr($phr, $debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', formula_link->link_phr "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... check the correct logging
$log = New user_log_link;
$log->table = 'formula_links';
$log->new_from_id = $frm->id;
$log->new_to_id = $phr->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job linked Formula Test to Company Test';
$exe_start_time = zu_test_show_result(', formula_link->link_phr logged for "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the link can be loaded by formula and phrase id and base on the id the correct formula and phrase objects are loaded
$frm_lnk = New formula_link;
$frm_lnk->usr = $usr;
$frm_lnk->frm = $frm;
$frm_lnk->phr = $phr;
$frm_lnk->load($debug-1);

$frm_lnk2 = New formula_link;
$frm_lnk2->usr = $usr;
$frm_lnk2->id  = $frm_lnk->id;
$frm_lnk2->load($debug-1);
$frm_lnk2->load_objects($debug-1);

// ... if form name is correct the chain of load via object, reload via id and load of the objects has worked
$result = $frm_lnk2->frm->name();
$target = $frm->name(); 
$exe_start_time = zu_test_show_result(', formula_link->load by formula id and link id "'.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$result = $frm_lnk2->phr->name();
$target = $phr->name(); 
$exe_start_time = zu_test_show_result(', formula_link->load by phrase id and link id "'.$phr->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the link is shown correctly
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr_lst = $frm->assign_phr_ulst($debug-1);
echo $phr_lst->dsp_id().'<br>';
$result = $phr_lst->does_contain($phr, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', formula->assign_phr_ulst contains "'.$phr->name.'" for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the link is shown correctly also for the second user
$frm = New formula;
$frm->usr = $usr2;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr_lst = $frm->assign_phr_ulst($debug-1);
$result = $phr_lst->does_contain($phr, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', formula->assign_phr_ulst contains "'.$phr->name.'" for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the value update has been triggert

// if second user removes the new link
$frm = New formula;
$frm->usr = $usr2;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr = New phrase;
$phr->name = TW_ADD_RENAMED;
$phr->usr = $usr2;
$phr->load($debug-1);
$result = $frm->unlink_phr($phr, $debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', formula_link->unlink_phr "'.$phr->name.'" from "'.$frm->name.'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... check if the removal of the link for the second user has been logged
$log = New user_log_link;
$log->table = 'formula_links';
$log->old_from_id = $frm->id;
$log->old_to_id = $phr->id;
$log->usr_id = $usr2->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system test unlinked Formula Test from Company Test';
$exe_start_time = zu_test_show_result(', formula_link->unlink_phr logged for "'.$phr->name.'" to "'.$frm->name.'" and user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// ... check if the link is really not used any more for the second user
$frm = New formula;
$frm->usr = $usr2;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr_lst = $frm->assign_phr_ulst($debug-1);
$result = $phr_lst->does_contain($phr, $debug-1);
$target = false; 
$exe_start_time = zu_test_show_result(', formula->assign_phr_ulst contains "'.$phr->name.'" for user "'.$usr2->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// ... check if the value update for the second user has been triggert

// ... check if the link is still used for the first user
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr_lst = $frm->assign_phr_ulst($debug-1);
$result = $phr_lst->does_contain($phr, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', formula->assign_phr_ulst still contains "'.$phr->name.'" for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the values for the first user are still the same

// if the first user also removes the link, both records should be deleted
$result = $frm->unlink_phr($phr, $debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', formula_link->unlink_phr "'.$phr->name.'" from "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check the correct logging
$log = New user_log_link;
$log->table = 'formula_links';
$log->old_from_id = $frm->id;
$log->old_to_id = $phr->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job unlinked Formula Test from Company Test';
$exe_start_time = zu_test_show_result(', formula_link->unlink_phr logged of "'.$phr->name.'" from "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the formula is not used any more for both users
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr_lst = $frm->assign_phr_ulst($debug-1);
$result = $phr_lst->does_contain($phr, $debug-1);
$target = false; 
$exe_start_time = zu_test_show_result(', formula->assign_phr_ulst contains "'.$phr->name.'" for user "'.$usr->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// ... and the values have been updated

// insert the link again for the first user
/*
$frm = New formula;
$frm->usr = $usr;
$frm->name = TF_ADD_RENAMED;
$frm->load($debug-1);
$phr = New phrase;
$phr->name = TW_ADD_RENAMED;
$phr->usr = $usr2;
$phr->load($debug-1);
$result = $frm->link_phr($phr, $debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', formula_link->link_phr "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
*/

// ... if the second user changes the link

// ... and the first user removes the link

// ... the link should still be active for the second user

// ... but not for the first user

// ... and the owner should now be the second user

// the code changes and tests for formula link should be moved the view_entry_link


echo "<h2>Test the formula link list class (classes/formula_link_list.php)</h2><br>";

$frm = New formula;
$frm->name = TF_INCREASE;
$frm->usr = $usr;
$frm->load($debug-1);
$frm_lnk_lst = New formula_link_list;
$frm_lnk_lst->frm = $frm;
$frm_lnk_lst->usr = $usr;
$frm_lnk_lst->load($debug-1);
$phr_ids = $frm_lnk_lst->phrase_ids(false, $debug-1);
$phr_lst = New phrase_list;
$phr_lst->ids = $phr_ids;
$phr_lst->usr = $usr;
$phr_lst->load($debug-1);
$result = $phr_lst->dsp_id();
$target = TW_YEAR;
$exe_start_time = zu_test_show_contains(', formula_link_list->load phrase linked to '.$frm->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);


echo "<h2>Test the formula value class (classes/formula_value.php)</h2><br>";

// test load result without time
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TF_INCREASE);
// why are these two words needed??
$phr_lst->add_name(TW_MIO);
//$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_PCT);
$abb_up_grp = $phr_lst->get_grp($debug-1);
if ($abb_up_grp->id > 0) {
  $abb_up = New formula_value;
  $abb_up->phr_grp_id = $abb_up_grp->id;
  $abb_up->usr = $usr;
  $abb_up->load($debug-1);
  $result = $abb_up->value;
} else {
  $result = '';
}
//$result = $abb_up->phr_grp_id;
$target = '-0.046588314872749';
$target = '';
$exe_start_time = zu_test_show_result(', value->val_formatted ex time for '.$phr_lst->dsp_id().' (group id '.$abb_up_grp->id.')', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// test load result with time
$phr_lst->add_name(TW_2014); 
$phr_lst->load($debug-1);
$time_phr = $phr_lst->time_useful($debug-1);
$abb_up_grp = $phr_lst->get_grp($debug-1);
if ($abb_up_grp->id > 0) {
  $abb_up = New formula_value;
  $abb_up->phr_grp_id = $abb_up_grp->id;
  $abb_up->time_id = $time_phr->id;
  //$abb_up->wrd_lst = $phr_lst;
  $abb_up->usr = $usr;
  $abb_up->usr_id = $usr->id; // temp solution until the value is save automatically for all users
  $abb_up->load($debug-1);
  $result = $abb_up->value;
} else {
  $result = '';
}
//$result = $abb_up->phr_grp_id;
$target = '0.0099235970843945';
$exe_start_time = zu_test_show_result(', value->val_formatted incl time for '.$phr_lst->dsp_id().' (group id '.$abb_up_grp->id.')', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the scaling
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
$dest_wrd_lst->add_name('Thousand');
$dest_wrd_lst->load($debug-1);
$mio_val = New value;
$mio_val->ids = $wrd_lst->ids;
$mio_val->usr = $usr;
$mio_val->load($debug-1);
zu_debug('value->scale value loaded.', $debug-1);
//$result = $mio_val->check($debug-1);
$result = $mio_val->scale($dest_wrd_lst, $debug-1);
$target = '46000000000';
$exe_start_time = zu_test_show_result(', value->val_scaling for a tern list '.$wrd_lst->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

// test getting the "best guess" value
// e.g. if ABB,Sales,2014 is requested, but there is only a value for ABB,Sales,2014,CHF,million get it
//      based
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_2014);
$phr_lst->load($debug-1);
$val_best_guess = New value;
$val_best_guess->ids = $phr_lst->ids;
$val_best_guess->usr = $usr;
$val_best_guess->load($debug-1);
$result = $val_best_guess->number;
$target = '46000';
$exe_start_time = zu_test_show_result(', value->load the best guess for '.$phr_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

/* 

Additional test cases for formula result

if a user changes a value the result for him should be updated and the result should be user specific
but the result for other user should not be changed
if the user undo the value change, the result should be updated

if the user changes a word link, formula link or formula the result should also be updated

*/


echo "<h2>Test the formula value list class (classes/formula_value_list.php)</h2><br>";

$frm = New formula;
$frm->name = TF_PE;
$frm->name = TF_INCREASE;
$frm->usr = $usr;
$frm->load($debug-1);
$fv_lst = New formula_value_list;
$fv_lst->frm_id = $frm->id;
$fv_lst->usr = $usr;
$fv_lst->load($debug-1);
$result = $fv_lst->dsp_id();
$target = '"Sales","percent","increase","Company Test","2017"';
$exe_start_time = zu_test_show_contains(', formula_value_list->load of the formula results for '.$frm->dsp_id().' is '.$result.' and should contain', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


echo "<h2>Test the formula element class (classes/formula_element.php)</h2><br>";

// load increase formula for testing
$frm = New formula;
$frm->name = TF_SECTOR;
$frm->usr = $usr;
$frm->load($debug-1);
$exp = $frm->expression($debug-1);
$elm_lst = $exp->element_lst ($back, $debug-1);

if (isset($elm_lst)) {
  if (isset($elm_lst->lst)) {
    $pos = 0;
    foreach ($elm_lst->lst AS $elm) {
      $elm->load($debug-1);
      
      $result = $elm->dsp_id();
      if ($pos == 0) {
        $target = 'word "Sales" (6) for user zukunft.com system batch job';
      } elseif ($pos == 1) {
        $target = 'verb "can be used as a differentiator for" (12) for user zukunft.com system batch job';
      } elseif ($pos == 2) {
        $target = 'word "Sector" (54) for user zukunft.com system batch job';
      } elseif ($pos == 3) {
        $target = 'formula "Total Sales" (19) for user zukunft.com system batch job';
      } 
      $exe_start_time = zu_test_show_result(', formula_element->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
      
      $result = $elm->name($debug-1);
      if ($pos == 0) {
        $target = 'Sales';
      } elseif ($pos == 1) {
        $target = 'can be used as a differentiator for';
      } elseif ($pos == 2) {
        $target = 'Sector';
      } elseif ($pos == 3) {
        $target = 'Total Sales';
      } 
      $exe_start_time = zu_test_show_result(', formula_element->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
      
      $result = $elm->name_linked($back, $debug-1);
      if ($pos == 0) {
        $target = '<a href="/http/view.php?words=6&back=1">Sales</a>';
      } elseif ($pos == 1) {
        $target = 'can be used as a differentiator for';
      } elseif ($pos == 2) {
        $target = '<a href="/http/view.php?words=54&back=1">Sector</a>';
      } elseif ($pos == 3) {
        $target = '<a href="/http/formula_edit.php?id=19&back=1">Total Sales</a>';
      } 
      $exe_start_time = zu_test_show_result(', formula_element->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
      
      $pos++;
    }
  } else {
    $result = 'formula element list is empty';
    $target = '';
    $exe_start_time = zu_test_show_result(', expression->element_lst', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  }
} else {
  $result = 'formula element list not set';
  $target = '';
  $exe_start_time = zu_test_show_result(', expression->element_lst', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
}


echo "<h2>Test the formula element list class (classes/formula_element_list.php)</h2><br>";

// load increase formula for testing
$frm = New formula;
$frm->name = TF_SECTOR;
$frm->usr = $usr;
$frm->load($debug-1);
$exp = $frm->expression($debug-1);
$elm_lst = $exp->element_lst ($back, $debug-1);

if (isset($elm_lst)) {
  $result = $elm_lst->dsp_id($debug-1);
  $target = 'Sales can be used as a differentiator for Sector Total Sales';
  $exe_start_time = zu_test_show_contains(', formula_element_list->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
} else {
  $result = 'formula element list not set';
  $target = '';
  $exe_start_time = zu_test_show_result(', formula_element_list->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
}


echo "<h2>Test the formula element group list class (classes/formula_element_group_list.php)</h2><br>";

// load increase formula for testing
$frm = New formula;
$frm->name = TF_INCREASE;
$frm->usr = $usr;
$frm->load($debug-1);

$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_MIO);
$phr_lst->add_name(TW_2015); 
$phr_lst->load($debug-1);

$phr_lst_next = New phrase_list;
$phr_lst_next->usr = $usr;
$phr_lst_next->add_name(TW_ABB);
$phr_lst_next->add_name(TW_SALES);
$phr_lst_next->add_name(TW_CHF);
$phr_lst_next->add_name(TW_MIO);
$phr_lst_next->add_name(TW_2016); 
$phr_lst_next->load($debug-1);

// build the expression which is in this case "percent" = ( "this" - "prior" ) / "prior" 
$exp = $frm->expression($debug-1);
// build the element group list which is in this case "this" and "prior", but an element group can contain more than one word
$elm_grp_lst = $exp->element_grp_lst ($back, $debug-1);

$result = $elm_grp_lst->dsp_id($debug-1);
$target = 'this / prior';
$exe_start_time = zu_test_show_contains(', formula_element_group_list->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the formula element group class (classes/formula_element_group.php)</h2><br>";

// define the element group object to retrieve the value
if (count($elm_grp_lst->lst) > 0) {
  $elm_grp = $elm_grp_lst->lst[0];
  $elm_grp->phr_lst  = clone $phr_lst;
  
  // test debug id first
  $result = $elm_grp->dsp_id($debug-1);
  $target = '"this" (3) and "ABB","Sales","CHF","million","2015"';
  $exe_start_time = zu_test_show_result(', formula_element_group->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test symbol for text replacement in the formula expression text
  $result = $elm_grp->build_symbol($debug-1);
  $target = '{f3}';
  $exe_start_time = zu_test_show_result(', formula_element_group->build_symbol', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test the display name that can be used for user debugging
  $result = trim($elm_grp->dsp_names($back, $debug-1));
  $target = trim('<a href="/http/formula_edit.php?id=3&back=1">this</a> ');
  $exe_start_time = zu_test_show_result(', formula_element_group->dsp_names', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  
  // test if the values for an element group are displayed correctly
  $time_phr = $phr_lst->assume_time($debug-1);
  $result = $elm_grp->dsp_values($back, $time_phr, $debug-1);
  $target = '<a href="/http/value_edit.php?id=438&back=1" class="user_specific">35\'481</a>';
  $exe_start_time = zu_test_show_result(', formula_element_group->dsp_values', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  $time_phr = $phr_lst_next->assume_time($debug-1);
  $result = $elm_grp->dsp_values($back, $time_phr, $debug-1);
  $target = '<a href="/http/value_edit.php?id=438&back=1" class="user_specific">35\'481</a> (2015)';
  $exe_start_time = zu_test_show_result(', formula_element_group->dsp_values', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  
  // remember the figure list for the figure and figure list class test
  $fig_lst = $elm_grp->figures($debug-1);
  
  echo "<h2>Test the figure class (classes/figure.php)</h2><br>";

  // get the figures (a value added by a user or a calculated formula result) for this element group and a context defined by a phrase list
  $fig_count = 0;
  if (isset($fig_lst)) {
    if (isset($fig_lst->lst)) {
      $fig_count = count($fig_lst->lst);
    }
  }
  if ($fig_count > 0) {
    $fig = $fig_lst->lst[0];

    if (isset($fig)) {
      $result = $fig->display($back, $debug-1);
      $target = "35'481";
      $exe_start_time = zu_test_show_result(', figure->display', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
      
      $result = $fig->display_linked($back, $debug-1);
      $target = '<a href="/http/value_edit.php?id=438&back=1" class="user_specific">35\'481</a>';
      $exe_start_time = zu_test_show_result(', figure->display_linked', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    }
  } else {
    $result = 'figure list is empty';
    $target = 'this (3) and "ABB","Sales","CHF","million","2015"@';
    $exe_start_time = zu_test_show_result(', formula_element_group->figures', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  }
  

  echo "<h2>Test the figure list class (classes/figure_lst.php)</h2><br>";

  $result = htmlspecialchars($fig_lst->dsp_id());
  $target = htmlspecialchars("<font class=\"user_specific\">35'481</font> (438)");
  $result = str_replace("<","&lt;",str_replace(">","&gt;",$result));
  $target = str_replace("<","&lt;",str_replace(">","&gt;",$target));
  // to overwrite any special char
  $diff = zu_str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  /*
  echo "*".implode("*",$diff['values'])."*";
  echo "$".implode("$",$diff['view'])."$";
  if (strpos($result,$target) > 0) { $result = $target; } else { $result = ''; }
  $result = str_replace("'","&#39;",$result);
  $target = str_replace("'","&#39;",$target);
  */
  $exe_start_time = zu_test_show_result(', figure_list->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  $result = $fig_lst->display();
  $target = "35'481 ";
  $exe_start_time = zu_test_show_result(', figure_list->display', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

} else {
  $result = 'formula element group list is empty';
  $target = 'this (3) and "ABB","Sales","CHF","million","2015"@';
  $exe_start_time = zu_test_show_result(', formula_element_group->dsp_names', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
}


echo "<h2>Test the formula list class (classes/formula_list.php)</h2><br>";

$wrd = New word;
$wrd->id = TEST_WORD_ID;
$wrd->usr = $usr;
$wrd->load($debug-1);
$frm_lst = New formula_list;
$frm_lst->wrd  = $wrd;
$frm_lst->usr  = $usr;
$frm_lst->back = $wrd->id;
$frm_lst->load($debug-1);
$result = $frm_lst->display($debug-1);
$target = TF_PE;
$exe_start_time = zu_test_show_contains(', formula_list->load formula for word "'.$wrd->dsp_id().'" should contain', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


echo "<h2>Test the batch job class (classes/batch_job.php)</h2><br>";

// preparetest adding a batch job via a list
$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TW_ABB);
$phr_lst->add_name(TW_SALES);
$phr_lst->add_name(TW_CHF);
$phr_lst->add_name(TW_MIO);
$phr_lst->add_name(TW_2014);
$phr_lst->load($debug-1);
$val = New value;
$val->ids = $phr_lst->ids;
$val->usr = $usr;
$val->load($debug-1);

// test adding a batch job
$job = new batch_job;
$job->obj = $val;
$job->type = cl(DBL_JOB_VALUE_UPDATE);
$result = $job->add($debug-1);
if ($result > 0) {
  $target = $result;
}  
$exe_start_time = zu_test_show_result(', batch_job->add has number "'.$result.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);


echo "<h2>Test the batch job list class (classes/batch_job_list.php)</h2><br>";

// preparetest adding a batch job via a list
$frm = New formula;
$frm->name = TF_INCREASE;
$frm->usr = $usr;
$frm->load($debug-1);

// test adding a batch job via a list
$job_lst = new batch_job_list;
$calc_request = New batch_job;
$calc_request->frm     = $frm;
$calc_request->usr     = $usr;
$calc_request->phr_lst = $phr_lst;
$result = $job_lst->add($calc_request, $debug-1);
if ($result > 0) {
  $target = $result;
}  
$exe_start_time = zu_test_show_result(', batch_job->add has number "'.$result.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);


echo "<h2>Test the view class (classes/view.php)</h2><br>";

// test the creation and changing of a view

// test loading of one view
$dsp = new view_dsp;
$dsp->usr = $usr;
$dsp->name = 'complete';
$dsp->load($debug-1);
$result = $dsp->comment;
$target = 'Show a word, all related words to edit the word tree and the linked formulas with some results';
$exe_start_time = zu_test_show_result(', view->load the comment of "'.$dsp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the complete view for one word
$wrd = New word_dsp;
$wrd->usr  = $usr;
$wrd->name = TW_ABB;
$wrd->load($debug-1);
$result = $dsp->display($wrd, $back, $debug-1);
// check if the view contains the word name
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', view->display "'.$dsp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);
// check if the view contains at least one value
$target = '45\'548';
$exe_start_time = zu_test_show_contains(', view->display "'.$dsp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
// check if the view contains at least the main formulas
$target = 'countryweight';
$exe_start_time = zu_test_show_contains(', view->display "'.$dsp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$target = 'Price Earning ratio';
$exe_start_time = zu_test_show_contains(', view->display "'.$dsp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test adding of one view
$dsp = new view;
$dsp->name    = TM_ADD;
$dsp->comment = 'Just added for testing';
$dsp->usr = $usr;
$result = $dsp->save($debug-1);
if ($dsp->id > 0) {
  $result = $dsp->comment;
}
$target = 'Just added for testing';
$exe_start_time = zu_test_show_result(', view->save for adding "'.$dsp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the view name has been saved
$dsp = new view;
$dsp->name = TM_ADD;
$dsp->usr = $usr;
$dsp->load($debug-1);
$result = $dsp->comment;
$target = 'Just added for testing';
$exe_start_time = zu_test_show_result(', view->load the added "'.$dsp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view adding has been logged
$log = New user_log;
$log->table = 'views';
$log->field = 'view_name';
$log->row_id = $dsp->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Test Mask';
$exe_start_time = zu_test_show_result(', view->save adding logged for "'.TM_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if adding the same view again creates a correct error message
$dsp = new view;
$dsp->name = TM_ADD;
$dsp->usr = $usr;
$result = $dsp->save($debug-1);
$target = 'A view with the name "'.TM_ADD.'" already exists. Please use another name.'; // is this error messsage really needed???
$target = '1';
$exe_start_time = zu_test_show_result(', view->save adding "'.$dsp->name.'" again', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

// check if the view can be renamed
$dsp = new view;
$dsp->name = TM_ADD;
$dsp->usr = $usr;
$dsp->load($debug-1);
$dsp->name = TM_ADD_RENAMED;
$result = $dsp->save($debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', view->save rename "'.TM_ADD.'" to "'.TM_ADD_RENAMED.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the view renaming was successful
$dsp_renamed = new view;
$dsp_renamed->name = TM_ADD_RENAMED;
$dsp_renamed->usr = $usr;
$result = $dsp_renamed->load($debug-1);
if ($result == '') {
  if ($dsp_renamed->id > 0) {
    $result = $dsp_renamed->name;
  }
}
$target = TM_ADD_RENAMED;
$exe_start_time = zu_test_show_result(', view->load renamed view "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view renaming has been logged
$log = New user_log;
$log->table = 'views';
$log->field = 'view_name';
$log->row_id = $dsp_renamed->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job changed Test Mask to Mask Test';
$exe_start_time = zu_test_show_result(', view->save rename logged for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view parameters can be added
$dsp_renamed->comment = 'Just added for testing the user sandbox';
$dsp_renamed->type_id = cl(SQL_VIEW_TYPE_WORD_DEFAULT);
$result = $dsp_renamed->save($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', view->save all view fields beside the name for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the view parameters have been added
$dsp_reloaded = new view;
$dsp_reloaded->name = TM_ADD_RENAMED;
$dsp_reloaded->usr = $usr;
$dsp_reloaded->load($debug-1);
$result = $dsp_reloaded->comment;
$target = 'Just added for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view->load comment for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $dsp_reloaded->type_id;
$target = cl(SQL_VIEW_TYPE_WORD_DEFAULT);
$exe_start_time = zu_test_show_result(', view->load type_id for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view parameter adding have been logged
$log = New user_log;
$log->table = 'views';
$log->field = 'comment';
$log->row_id = $dsp_reloaded->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Just added for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view->load comment for "'.TM_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'view_type_id';
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added word default';
$exe_start_time = zu_test_show_result(', view->load view_type_id for "'.TM_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if a user specific view is created if another user changes the view
$dsp_usr2 = new view;
$dsp_usr2->name = TM_ADD_RENAMED;
$dsp_usr2->usr = $usr2;
$dsp_usr2->load($debug-1);
$dsp_usr2->comment = 'Just changed for testing the user sandbox';
$dsp_usr2->type_id = cl(SQL_VIEW_TYPE_ENTRY);
$result = $dsp_usr2->save($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', view->save all view fields for user 2 beside the name for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if a user specific view changes have been saved
$dsp_usr2_reloaded = new view;
$dsp_usr2_reloaded->name = TM_ADD_RENAMED;
$dsp_usr2_reloaded->usr = $usr2;
$dsp_usr2_reloaded->load($debug-1);
$result = $dsp_usr2_reloaded->comment;
$target = 'Just changed for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view->load comment for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $dsp_usr2_reloaded->type_id;
$target = cl(SQL_VIEW_TYPE_ENTRY);
$exe_start_time = zu_test_show_result(', view->load type_id for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check the view for the original user remains unchanged
$dsp_reloaded = new view;
$dsp_reloaded->name = TM_ADD_RENAMED;
$dsp_reloaded->usr = $usr;
$dsp_reloaded->load($debug-1);
$result = $dsp_reloaded->comment;
$target = 'Just added for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view->load comment for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $dsp_reloaded->type_id;
$target = cl(SQL_VIEW_TYPE_WORD_DEFAULT);
$exe_start_time = zu_test_show_result(', view->load type_id for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if undo all specific changes removes the user view
$dsp_usr2 = new view;
$dsp_usr2->name = TM_ADD_RENAMED;
$dsp_usr2->usr = $usr2;
$dsp_usr2->load($debug-1);
$dsp_usr2->comment = 'Just added for testing the user sandbox';
$dsp_usr2->type_id = cl(SQL_VIEW_TYPE_WORD_DEFAULT);
$result = $dsp_usr2->save($debug-1);
$target = '111';
$exe_start_time = zu_test_show_result(', view->save undo the user view fields beside the name for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if a user specific view changes have been saved
$dsp_usr2_reloaded = new view;
$dsp_usr2_reloaded->name = TM_ADD_RENAMED;
$dsp_usr2_reloaded->usr = $usr2;
$dsp_usr2_reloaded->load($debug-1);
$result = $dsp_usr2_reloaded->comment;
$target = 'Just added for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view->load comment for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $dsp_usr2_reloaded->type_id;
$target = cl(SQL_VIEW_TYPE_WORD_DEFAULT);
$exe_start_time = zu_test_show_result(', view->load type_id for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// redo the user specific view changes
// check if the user specific changes can be removed with one click


echo "<h2>Test the view_display class (classes/view_display.php)</h2><br>";

// test the usage of a view to create the HTML code
$wrd = New word;
$wrd->name = TEST_WORD;
$wrd->usr = $usr;
$wrd->load($debug-1);
$wrd_abb = New word;
$wrd_abb->name = TW_ABB;
$wrd_abb->usr = $usr;
$wrd_abb->load($debug-1);
$dsp = new view;
$dsp->name = 'Company ratios';
$dsp->usr = $usr;
$dsp->load($debug-1);
//$result = $dsp->display($wrd, $back, $debug-1);
$target = true;
//$exe_start_time = zu_test_show_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the view component class (classes/view_entry.php)</h2><br>";
/*
// test loading of one view_entry
$cmp = new view_component_dsp;
$cmp->usr = $usr;
$cmp->name = 'complete';
$cmp->load($debug-1);
$result = $cmp->comment;
$target = 'Show a word, all related words to edit the word tree and the linked formulas with some results';
$exe_start_time = zu_test_show_result(', view_entry->load the comment of "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test the complete view_entry for one word
$wrd = New word_dsp;
$wrd->usr  = $usr;
$wrd->name = TW_ABB;
$wrd->load($debug-1);
$result = $cmp->display($wrd, $debug-1);
// check if the view_entry contains the word name
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', view_entry->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);
// check if the view_entry contains at least one value
$target = '45548';
$exe_start_time = zu_test_show_contains(', view_entry->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
// check if the view_entry contains at least the main formulas
$target = 'countryweight';
$exe_start_time = zu_test_show_contains(', view_entry->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$target = 'Price Earning ratio';
$exe_start_time = zu_test_show_contains(', view_entry->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
*/
// test adding of one view_entry
$cmp = new view_component;
$cmp->name    = TC_ADD;
$cmp->comment = 'Just added for testing';
$cmp->usr = $usr;
$result = $cmp->save($debug-1);
if ($cmp->id > 0) {
  $result = $cmp->comment;
}
$target = 'Just added for testing';
$exe_start_time = zu_test_show_result(', view_entry->save for adding "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the view_entry name has been saved
$cmp = new view_component;
$cmp->name = TC_ADD;
$cmp->usr = $usr;
$cmp->load($debug-1);
$result = $cmp->comment;
$target = 'Just added for testing';
$exe_start_time = zu_test_show_result(', view_entry->load the added "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view_entry adding has been logged
$log = New user_log;
$log->table = 'view_entries';
$log->field = 'view_entry_name';
$log->row_id = $cmp->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Test Mask Component';
$exe_start_time = zu_test_show_result(', view_entry->save adding logged for "'.TC_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if adding the same view_entry again creates a correct error message
$cmp = new view_component;
$cmp->name = TC_ADD;
$cmp->usr = $usr;
$result = $cmp->save($debug-1);
// in case of other settings
$target = 'A view component with the name "'.TC_ADD.'" already exists. Please use another name.';
// for the standard settings
$target = '1';
$exe_start_time = zu_test_show_result(', view_entry->save adding "'.$cmp->name.'" again', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the view_entry can be renamed
$cmp = new view_component;
$cmp->name = TC_ADD;
$cmp->usr = $usr;
$cmp->load($debug-1);
$cmp->name = TC_ADD_RENAMED;
$result = $cmp->save($debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', view_entry->save rename "'.TC_ADD.'" to "'.TC_ADD_RENAMED.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if the view_entry renaming was successful
$cmp_renamed = new view_component;
$cmp_renamed->name = TC_ADD_RENAMED;
$cmp_renamed->usr = $usr;
$result = $cmp_renamed->load($debug-1);
if ($result == '') {
  if ($cmp_renamed->id > 0) {
    $result = $cmp_renamed->name;
  }
}
$target = TC_ADD_RENAMED;
$exe_start_time = zu_test_show_result(', view_entry->load renamed view_entry "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view_entry renaming has been logged
$log = New user_log;
$log->table = 'view_entries';
$log->field = 'view_entry_name';
$log->row_id = $cmp_renamed->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job changed Test Mask Component to Mask Component Test';
$exe_start_time = zu_test_show_result(', view_entry->save rename logged for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view_entry parameters can be added
$cmp_renamed = new view_component;
$cmp_renamed->name = TC_ADD_RENAMED;
$cmp_renamed->usr = $usr;
$cmp_renamed->load($debug-1);
$cmp_renamed->comment = 'Just added for testing the user sandbox';
$cmp_renamed->type_id = cl(SQL_VIEW_TYPE_WORD_NAME);
$result = $cmp_renamed->save($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', view_entry->save all view_entry fields beside the name for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// check if the view_entry parameters have been added
$cmp_reloaded = new view_component;
$cmp_reloaded->name = TC_ADD_RENAMED;
$cmp_reloaded->usr = $usr;
$cmp_reloaded->load($debug-1);
$result = $cmp_reloaded->comment;
$target = 'Just added for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view_entry->load comment for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $cmp_reloaded->type_id;
$target = cl(SQL_VIEW_TYPE_WORD_NAME);
$exe_start_time = zu_test_show_result(', view_entry->load type_id for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view_entry parameter adding have been logged
$log = New user_log;
$log->table = 'view_entries';
$log->field = 'comment';
$log->row_id = $cmp_reloaded->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added Just added for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view_entry->load comment for "'.TC_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$log->field = 'view_entry_type_id';
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job added word name';
$exe_start_time = zu_test_show_result(', view_entry->load view_entry_type_id for "'.TC_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if a user specific view_entry is created if another user changes the view_entry
$cmp_usr2 = new view_component;
$cmp_usr2->name = TC_ADD_RENAMED;
$cmp_usr2->usr = $usr2;
$cmp_usr2->load($debug-1);
$cmp_usr2->comment = 'Just changed for testing the user sandbox';
$cmp_usr2->type_id = cl(SQL_VIEW_TYPE_FORMULAS);
$result = $cmp_usr2->save($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', view_entry->save all view_entry fields for user 2 beside the name for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if a user specific view_entry changes have been saved
$cmp_usr2_reloaded = new view_component;
$cmp_usr2_reloaded->name = TC_ADD_RENAMED;
$cmp_usr2_reloaded->usr = $usr2;
$cmp_usr2_reloaded->load($debug-1);
$result = $cmp_usr2_reloaded->comment;
$target = 'Just changed for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view_entry->load comment for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $cmp_usr2_reloaded->type_id;
$target = cl(SQL_VIEW_TYPE_FORMULAS);
$exe_start_time = zu_test_show_result(', view_entry->load type_id for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check the view_entry for the original user remains unchanged
$cmp_reloaded = new view_component;
$cmp_reloaded->name = TC_ADD_RENAMED;
$cmp_reloaded->usr = $usr;
$cmp_reloaded->load($debug-1);
$result = $cmp_reloaded->comment;
$target = 'Just added for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view_entry->load comment for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
$result = $cmp_reloaded->type_id;
$target = cl(SQL_VIEW_TYPE_WORD_NAME);
$exe_start_time = zu_test_show_result(', view_entry->load type_id for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if undo all specific changes removes the user view_entry
$cmp_usr2 = new view_component;
$cmp_usr2->name = TC_ADD_RENAMED;
$cmp_usr2->usr = $usr2;
$cmp_usr2->load($debug-1);
$cmp_usr2->comment = 'Just added for testing the user sandbox';
$cmp_usr2->type_id = cl(SQL_VIEW_TYPE_WORD_NAME);
$result = $cmp_usr2->save($debug-1);
$target = '111';
$exe_start_time = zu_test_show_result(', view_entry->save undo the user view_entry fields beside the name for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check if a user specific view_entry changes have been saved
$cmp_usr2_reloaded = new view_component;
$cmp_usr2_reloaded->name = TC_ADD_RENAMED;
$cmp_usr2_reloaded->usr = $usr2;
$cmp_usr2_reloaded->load($debug-1);
$result = $cmp_usr2_reloaded->comment;
$target = 'Just added for testing the user sandbox';
$exe_start_time = zu_test_show_result(', view_entry->load comment for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
//$result = $dsp_usr2_reloaded->type_id;
//$target = cl(SQL_VIEW_TYPE_WORD_NAME);
//$exe_start_time = zu_test_show_result(', view_entry->load type_id for "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// redo the user specific view_entry changes
// check if the user specific changes can be removed with one click


echo "<h2>Test the view component display class (classes/view_component_dsp.php)</h2><br>";

// test if a simple text component can be created
$cmp = new view_component_dsp;
$cmp->type_id = cl(SQL_VIEW_ENTRY_TEXT);
$cmp->name = TS_NESN_2016_NAME;
$result = $cmp->text($debug-1);
$target = ' '.TS_NESN_2016_NAME;
$exe_start_time = zu_test_show_result(', view_component_dsp->text', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the view component link class (classes/view_entry_link.php)</h2><br>";

// link the test view component to another view
$dsp = new view;
$dsp->name = TM_ADD_RENAMED;
$dsp->usr = $usr;
$dsp->load($debug-1);
$cmp = new view_component;
$cmp->usr = $usr;
$cmp->name = TC_ADD_RENAMED;
$cmp->load($debug-1);
$order_nbr = $cmp->next_nbr($dsp->id, $debug-1);
$result = $cmp->link($dsp, $order_nbr, $debug-1);
$target = '111';
$exe_start_time = zu_test_show_result(', view component_link->link "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... check the correct logging
$log = New user_log_link;
$log->table = 'view_entry_links';
$log->new_from_id = $dsp->id;
$log->new_to_id = $cmp->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job linked Mask Test to Mask Component Test';
$exe_start_time = zu_test_show_result(', view component_link->link_dsp logged for "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the link is shown correctly
$cmp = new view_component;
$cmp->usr = $usr;
$cmp->name = TC_ADD_RENAMED;
$cmp->load($debug-1);
$dsp_lst = $cmp->assign_dsp_ids($debug-1);
$result = $dsp->is_in_list($dsp_lst, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the link is shown correctly also for the second user
$cmp = new view_component;
$cmp->usr = $usr2;
$cmp->name = TC_ADD_RENAMED;
$cmp->load($debug-1);
$dsp_lst = $cmp->assign_dsp_ids($debug-1);
$result = $dsp->is_in_list($dsp_lst, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the value update has been triggert

// if second user removes the new link
$cmp = new view_component;
$cmp->usr = $usr2;
$cmp->name = TC_ADD_RENAMED;
$cmp->load($debug-1);
$dsp = new view;
$dsp->name = TM_ADD_RENAMED;
$dsp->usr = $usr2;
$dsp->load($debug-1);
$result = $cmp->unlink($dsp, $debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', view component_link->unlink "'.$dsp->name.'" from "'.$cmp->name.'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// ... check if the removal of the link for the second user has been logged
$log = New user_log_link;
$log->table = 'view_entry_links';
$log->old_from_id = $dsp->id;
$log->old_to_id = $cmp->id;
$log->usr_id = $usr2->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system test unlinked Mask Test from Mask Component Test';
$exe_start_time = zu_test_show_result(', view component_link->unlink_dsp logged for "'.$dsp->name.'" to "'.$cmp->name.'" and user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// ... check if the link is really not used any more for the second user
$cmp = new view_component;
$cmp->usr = $usr2;
$cmp->name = TC_ADD_RENAMED;
$cmp->load($debug-1);
$dsp_lst = $cmp->assign_dsp_ids($debug-1);
$result = $dsp->is_in_list($dsp_lst, $debug-1);
$target = false; 
$exe_start_time = zu_test_show_result(', view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr2->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// ... check if the value update for the second user has been triggert

// ... check if the link is still used for the first user
$cmp = new view_component;
$cmp->usr = $usr;
$cmp->name = TC_ADD_RENAMED;
$cmp->load($debug-1);
$dsp_lst = $cmp->assign_dsp_ids($debug-1);
$result = $dsp->is_in_list($dsp_lst, $debug-1);
$target = true; 
$exe_start_time = zu_test_show_result(', view component->assign_dsp_ids still contains "'.$dsp->name.'" for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// ... check if the values for the first user are still the same

// if the first user also removes the link, both records should be deleted
$result = $cmp->unlink($dsp, $debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', view component_link->unlink "'.$dsp->name.'" from "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

// check the correct logging
$log = New user_log_link;
$log->table = 'view_entry_links';
$log->old_from_id = $dsp->id;
$log->old_to_id = $cmp->id;
$log->usr_id = $usr->id;
$result = $log->dsp_last(true, $debug-1);
$target = 'zukunft.com system batch job unlinked Mask Test from Mask Component Test';
$exe_start_time = zu_test_show_result(', view component_link->unlink_dsp logged of "'.$dsp->name.'" from "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// check if the view component is not used any more for both users
$cmp = new view_component;
$cmp->usr = $usr;
$cmp->name = TC_ADD_RENAMED;
$cmp->load($debug-1);
$dsp_lst = $cmp->assign_dsp_ids($debug-1);
$result = $dsp->is_in_list($dsp_lst, $debug-1);
$target = false; 
$exe_start_time = zu_test_show_result(', view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// ... and the values have been updated

// insert the link again for the first user
/*
$cmp = new view_component;
$cmp->usr = $usr;
$cmp->name = TC_ADD_RENAMED;
$cmp->load($debug-1);
$dsp = new view;
$dsp->name = TM_ADD_RENAMED;
$dsp->usr = $usr;
$dsp->load($debug-1);
$result = $cmp->link_dsp($dsp, $debug-1);
$target = '1';
$exe_start_time = zu_test_show_result(', view component_link->link_dsp "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
*/

// ... if the second user changes the link

// ... and the first user removes the link

// ... the link should still be active for the second user

// ... but not for the first user

// ... and the owner should now be the second user

// the code changes and tests for view component link should be moved the view_entry_link


echo "<h2>Test the display button class (classes/display_button.php )</h2><br>";

$target = '<a href="/http/view.php" title="Add test"><img src="../images/button_add.svg" alt="Add test"></a>';
$result = btn_add('Add test', '/http/view.php');
$exe_start_time = zu_test_show_result(", btn_add", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$target = '<a href="/http/view.php" title="Edit test"><img src="../images/button_edit.svg" alt="Edit test"></a>';
$result = btn_edit('Edit test', '/http/view.php');
$exe_start_time = zu_test_show_result(", btn_edit", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$target = '<a href="/http/view.php" title="Del test"><img src="../images/button_del.svg" alt="Del test"></a>';
$result = btn_del('Del test', '/http/view.php');
$exe_start_time = zu_test_show_result(", btn_del", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$target = '<a href="/http/view.php" title="Undo test"><img src="../images/button_undo.svg" alt="Undo test"></a>';
$result = btn_undo('Undo test', '/http/view.php');
$exe_start_time = zu_test_show_result(", btn_undo", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$target = '<a href="/http/view.php" title="Find test"><img src="../images/button_find.svg" alt="Find test"></a>';
$result = btn_find('Find test', '/http/view.php');
$exe_start_time = zu_test_show_result(", btn_find", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$target = '<a href="/http/view.php" title="Show all test"><img src="../images/button_filter_off.svg" alt="Show all test"></a>';
$result = btn_unfilter('Show all test', '/http/view.php');
$exe_start_time = zu_test_show_result(", btn_unfilter", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$target = '<h3>YesNo test</h3><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
$result = btn_yesno('YesNo test', '/http/view.php');
$exe_start_time = zu_test_show_result(", btn_yesno", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$target = '<a href="/http/view.php?words=1" title="back"><img src="../images/button_back.svg" alt="back"></a>';
$result = btn_back('');
$exe_start_time = zu_test_show_result(", btn_back", $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test the display HTML class (classes/display_html.php )</h2><br>";

$target = htmlspecialchars(trim('<html> <head> <title>Header test (zukunft.com)</title> <link rel="stylesheet" type="text/css" href="../style/style.css" /> </head> <body class="center_form">'));
$target = htmlspecialchars(trim('<title>Header test (zukunft.com)</title>'));
$result = htmlspecialchars(trim(dsp_header('Header test', 'center_form')));
$exe_start_time = zu_test_show_contains(", dsp_header", $target, $result, $exe_start_time, TIMEOUT_LIMIT);


echo "<h2>Test general frontend scripts (e.g. /about.php)</h2><br>";

// check if the about page contains at least some basic keywords
$result = file_get_contents('https://www.zukunft.com/http/about.php?id=1');
$target = 'zukunft.com AG';
if (strpos($dsp_test, $target) > 0) {
  $result = $target;
} else {
  $result = '';
}
// about does not return a page for unknow reasons at the moment
//$exe_start_time = zu_test_show_contains(', frontend about.php '.$result.' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

$result = file_get_contents('https://zukunft.com/http/privacy_policy.html');
$target = 'Swiss purpose of data protection';
$exe_start_time = zu_test_show_contains(', frontend privacy_policy.php '.$result.' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

$result = file_get_contents('https://zukunft.com/http/error_update.php?id=1');
$target = 'not permitted';
$exe_start_time = zu_test_show_contains(', frontend error_update.php '.$result.' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

$result = file_get_contents('https://zukunft.com/http/find.php?pattern='.TW_ABB);
$target = TW_ABB;
$exe_start_time = zu_test_show_contains(', frontend find.php '.TW_ABB.' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


echo "<h2>Test the xml export class (classes/xml.php)</h2><br>";

$phr_lst = New phrase_list;
$phr_lst->usr = $usr;
$phr_lst->add_name(TEST_WORD);
$phr_lst->load($debug-1);
$xml_export = New xml_export;
$xml_export->usr     = $usr;
$xml_export->phr_lst = $phr_lst;
$xml = $xml_export->export($debug-1);
$result = $xml->asXML();
$target = '<word>ABB</word>';
$target = 'Company has a balance sheet';
$exe_start_time = zu_test_show_contains(', xml->export for '.$phr_lst->dsp_id().' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);



echo "<h2>Test the user permission level increase</h2><br>";

// if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
// if a user has added 3 values and at least one is accpected by another user, he can add words and formula and he must have a valid email
// if a user has added 2 formula and both are accpected by at least one other user and noone has complained, he can change formulas and words, including linking of words
// if a user has linked a 10 words and all got accepted by one other user and noone has complained, he can request new verbs and he must have an validated address

// if a user got 10 pending word or formula discussion, he can no longer add words or formula until the open discussions are less than 10
// if a user got 5 pending word or formula discussion, he can no longer change words or formula until the open discussions are less than 5
// if a user got 2 pending verb discussion, he can no longer add verbs until the open discussions are less than 2

// the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range


echo "<h2>Test database link functions (zu_lib_sql_code_link.php)</h2><br>";

// test zut_name 
$id = DBL_SYSLOG_TBL_WORD;
$target = 2;
$result = cl($id);
$exe_start_time = zu_test_show_result(", sql_code_link ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

echo "<h2>Test the basic zukunft functions (zu_lib.php)</h2><br>";

echo "<h3>strings</h3><br>";
// test zu_str_left
$text = "This are the left 4";
$pos = 4;
$target = "This";
$result = zu_str_left($text, $pos);
$exe_start_time = zu_test_show_result(", zu_str_left: What are the left \"".$pos."\" chars of \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zu_str_right
$text = "This are the right 7";
$pos = 7;
$target = "right 7";
$result = zu_str_right($text, $pos);
$exe_start_time = zu_test_show_result(", zu_str_right: What are the right \"".$pos."\" chars of \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zu_str_left_of
$text = "This is left of that ";
$maker = " of that";
$target = "This is left";
$result = zu_str_left_of($text, $maker);
$exe_start_time = zu_test_show_result(", zu_str_left_of: What is left of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zu_str_left_of
$text = "This is left of that, but not of that";
$maker = " of that";
$target = "This is left";
$result = zu_str_left_of($text, $maker);
$exe_start_time = zu_test_show_result(", zu_str_left_of: What is left of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zu_str_right_of
$text = "That is right of this";
$maker = "That is right ";
$target = "of this";
$result = zu_str_right_of($text, $maker);
$exe_start_time = zu_test_show_result(", zu_str_right_of: What is right of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zu_str_right_of
$text = "00000";
$maker = "0";
$target = "0000";
$result = zu_str_right_of($text, $maker);
$exe_start_time = zu_test_show_result(", zu_str_right_of: What is right of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zu_str_right_of
$text = "The formula id of {f23}.";
$maker = "{f";
$target = "23}.";
$result = zu_str_right_of($text, $maker);
$exe_start_time = zu_test_show_result(", zu_str_right_of: What is right of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zu_str_between
$text = "The formula id of {f23}.";
$maker_start = "{f";
$maker_end = "}";
$target = "23";
$result = zu_str_between($text, $maker_start, $maker_end);
$exe_start_time = zu_test_show_result(", zu_str_between: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zu_str_between
$text = "The formula id of {f4} / {f5}.";
$maker_start = "{f";
$maker_end = "}";
$target = "4";
$result = zu_str_between($text, $maker_start, $maker_end);
$exe_start_time = zu_test_show_result(", zu_str_between: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

echo "<h2>Cleanup the test</h2><br>";

// request to delete the added test value
$added_val = New value;
$added_val->id = $added_val_id;
$added_val->usr = $usr;
$added_val->load($debug-1);
$result = $added_val->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', value->del test value for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

// request to delete the added test value
$added_val2 = New value;
$added_val2->id = $added_val2_id;
$added_val2->usr = $usr;
$added_val2->load($debug-1);
$result = $added_val2->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', value->del test value for "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

// request to delete the added test view component
$cmp = new view_component;
$cmp->name = TC_ADD;
$cmp->usr = $usr;
$result = $cmp->del($debug-1);
$target = '';
$exe_start_time = zu_test_show_result(', view_entry->del of "'.TC_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// request to delete the renamed test view component
$cmp = new view_component;
$cmp->name = TC_ADD_RENAMED;
$cmp->usr = $usr;
$result = $cmp->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', view_entry->del of "'.TC_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

// request to delete the added test view
$dsp = new view;
$dsp->name = TM_ADD;
$dsp->usr = $usr;
$result = $dsp->del($debug-1);
$target = '';
$exe_start_time = zu_test_show_result(', view->del of "'.TM_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// request to delete the renamed test view
$dsp = new view;
$dsp->name = TM_ADD_RENAMED;
$dsp->usr = $usr;
$result = $dsp->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', view->del of "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

// request to delete the added test formula
$frm = New formula;
$frm->name = TF_ADD;
$frm->usr = $usr;
$result = $frm->del($debug-1);
$target = '';
$exe_start_time = zu_test_show_result(', formula->del of "'.TF_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// request to delete the renamed test formula
$frm = New formula;
$frm->name = TF_ADD_RENAMED;
$frm->usr = $usr;
$result = $frm->del($debug-1);
$target = '1111';
$exe_start_time = zu_test_show_result(', formula->del of "'.TF_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

// request to delete the added test word
$wrd = New word;
$wrd->name = TW_ADD;
$wrd->usr = $usr;
$result = $wrd->del($debug-1);
$target = '';
$exe_start_time = zu_test_show_result(', word->del of "'.TW_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// request to delete the renamed test word
$wrd = New word;
$wrd->name = TW_ADD_RENAMED;
$wrd->usr = $usr;
$result = $wrd->del($debug-1);
$target = '11';
$exe_start_time = zu_test_show_result(', word->del of "'.TW_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

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
$sql_max = 'SELECT MAX(view_entry_id) AS max_id FROM view_entries;';
$wrd_max_db = $db_con->get1($sql_max, $debug-1); 
if ($wrd_max_db['max_id'] > 0) {
  $next_id = $wrd_max_db['max_id'] + 1;
  $sql = 'ALTER TABLE `view_entries` auto_increment = '.$next_id.';';
  $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
}
echo 'Next database id for view_entries: '.$next_id.'<br>';

// for view component links
$sql_max = 'SELECT MAX(view_entry_link_id) AS max_id FROM view_entry_links;';
$wrd_max_db = $db_con->get1($sql_max, $debug-1); 
if ($wrd_max_db['max_id'] > 0) {
  $next_id = $wrd_max_db['max_id'] + 1;
  $sql = 'ALTER TABLE `view_entry_links` auto_increment = '.$next_id.';';
  $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "test.php", (new Exception)->getTraceAsString(), $debug-1);
}
echo 'Next database id for view_entry_links: '.$next_id.'<br>';

echo "<br>";
echo '<h2>Total time for testing';
$since_start = microtime(true) - $start_time;
echo ', took ';
echo round($since_start,4).' seconds</h2><br><br>';
echo '<h2>';
echo 'Total errors: '.$error_counter.'<br>';
echo 'Total timeouts: '.$timeout_counter;
echo "</h2><br>";
/*

classes with a test process ready for version 0.1 (at least one test case for every function)

user_list.php 
user_log.php 
user_log_link.php 
word.php 
expression.php 
formula.php 
formula_element.php 
formula_element_list.php 
formula_element_group_list.php 
formula_element_group.php 
formula_list.php 
formula_link_list.php 
figure.php 
figure_list.php 
display_selector.php 


class test that does not yet have at least one test case for every function

user.php 
user_display.php 
user_log_display.php 
word_display.php 
word_list.php 
word_link.php 
word_link_list.php 
phrase.php 
phrase_list.php 
phrase_group.php 
phrase_group_list.php 
verb.php 
verb_list.php 
term.php
value.php 
value_list.php 
value_list_display.php 
source.php 
formula_link.php 
formula_value.php 
formula_value_list.php 
batch_job.php 
batch_job_list.php 
view.php 
view_display.php 
view_component.php (ex view_entry)
view_component_dsp.php
view_component_link.php 
display_button.php 
display_html.php 
xml.php


classes that can be tested with later, because they are used mainly for testing

system_error_log.php 
system_error_log_list.php 


classes that can be tested with later, because they are not yet used

display_list.php 
value_phrase_link.php 


Frontend scrips that needs to be tested
test if frontend scripts at least produce a useful result

formula_result.php
formula_test.php
..



*/

echo "<h2>Old test functions</h2><br>";

/*



echo "<h2>finish all open database batch and consistency check batch jobs</h2><br>";
$result = zut_calc_usage(); echo "word usage updated ... ".$result."<br>";
$result = zul_calc_usage(); echo "verb usage updated ... ".$result."<br>";

echo "refresh formula references ... ";
echo "done<br>";
*/
echo "<h2>Test the internal math function (which should be replaced by RESTful R-Project call)</h2><br>";


// calculate the target price for nestle: 
// if there is a word with the formula name assume that this word is added to the result
// so target price for Nestle should save a value to the formula result table linked to nestle and target price

// build a list of all formula results that needs to be update



// test zuc_has_braket
$math_text = "(-10744--10744)/-10744";
$target = 0;
$result = zuc_math_parse($math_text, array(), Null, $debug);
$exe_start_time = zu_test_show_result(", zuc_math: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

// test zuc_parse
/*$formula_id = $formula_value;
$target = "45548";
$word_array =           array($word_abb,$word_revenues,$word_CHF);
$word_ids = zut_sql_ids(array($word_abb,$word_revenues,$word_CHF));
$time_word_id = $word_2013;
$debug = false;
$result = zuc_parse($formula_id, ZUP_RESULT_TYPE_VALUE, $word_ids, $time_word_id, $debug);
$exe_start_time = zu_test_show_result(", zuc_parse: the result for formula with id ".$formula_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT); */

// test zuc_is_text_only 
$formula = "\"this is just a text\"";
$target = true;
$result = zuc_is_text_only($formula);
$exe_start_time = zu_test_show_result(", zuc_is_text_only: a text like ".$formula, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_pos_seperator 
$formula = "1+(2-1)";
$seperator = "+";
$target = 1;
$result = zuc_pos_seperator($formula, $seperator);
$exe_start_time = zu_test_show_result(", zuc_pos_seperator: seperator ".$seperator." is in ".$formula." at ", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_has_braket
$math_text = "(2 - 1) * 2";
$target = true;
$result = zuc_has_braket($math_text);
$exe_start_time = zu_test_show_result(", zuc_has_braket: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_has_formula
$formula = "{f4} / {f5}";
$target = true;
$result = zuc_has_formula($formula, ZUP_RESULT_TYPE_VALUE, 0);
$exe_start_time = zu_test_show_result(", zuc_has_formula: the result for formula \"".$formula."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_is_date
$date_text = "01.02.2013";
$target = true;
$result = zuc_is_date($date_text);
$exe_start_time = zu_test_show_result(", zuc_is_date: the result for \"".$date_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);


// test zuc_pos_word
$formula_text = "{t6}";
$target = "0";
$result = zuc_pos_word($formula_text);
$exe_start_time = zu_test_show_result(", zuc_pos_word: the result for formula \"".$formula_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zut_keep_only_specific
/*$word_array = array();
$word_array[] = $word_revenues;
$word_array[] = $word_nesn;
$word_array[] = $word_ch;
$target = $word_array; // because 83 (Country) should be excluded
$word_array[] = $word_country;
$result = zut_keep_only_specific($word_array, $debug);
$exe_start_time = zu_test_show_result(", zut_keep_only_specific: the result for word array \"".implode(",",$word_array)."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);
*/


// test zuc_math_bracket
$math_text = "(3 - 1) * 2";
$target = "2 * 2";
$result = zuc_math_bracket($math_text, array(), 0, 0);
$exe_start_time = zu_test_show_result(", zuc_math_bracket: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_math_parse
$math_text = "3 - 1";
$target = 2;
$result = zuc_math_parse($math_text, ZUP_RESULT_TYPE_VALUE);
$exe_start_time = zu_test_show_result(", zuc_math_parse: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_math_parse
$math_text = "2 * 2";
$target = 4;
$result = zuc_math_parse($math_text, ZUP_RESULT_TYPE_VALUE);
$exe_start_time = zu_test_show_result(", zuc_math_parse: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_is_math_symbol_or_num
$formula_part_text = "/{f19}";
$target = 1;
$result = zuc_is_math_symbol_or_num($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuc_is_math_symbol_or_num: the result for formula \"".$formula_part_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_get_math_symbol
$formula_part_text = "/{f19}";
$target = "/";
$result = zuc_get_math_symbol($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuc_get_math_symbol: the result for formula \"".$formula_part_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);





/*

"percent" = if ( is.numeric( "this" ) & is.numeric( "prior" ) )  ( "this" - "prior" ) / "prior"

//$company_wrd_id = 7; // nesn
$company_wrd_id = 25; // abb

$calc_usr_id = 0; // 

$frm_wrd_ids = array();
$frm_wrd_ids[] = 6; // sales
//$frm_wrd_ids[] = 144; // costs
$frm_wrd_ids[] = 83; // country

foreach (array_keys($val_wrd_ids) AS $val_id) {
  $wrd_ids = $val_wrd_ids[$val_id][1];
  foreach ($wrd_ids AS $wrd_id) {
    echo zut_name($wrd_id).", ";
  }
  echo "<br>";
}
*/

// check the increase formula
/*
echo zuc_pos_math_symbol("ab)cd-ef", $debug-5)."<br>";
echo zuc_get_math_symbol(")fdf", $debug-5)."<br>";
$wrd_ids = array(7,44,70,76,170); //  Nestle, CHF, million, 2016, Financial income
  $in_result = zuc_frm(3, "", $wrd_ids, 0, 14, 8);
echo "zuc_frm3:".$in_result[0];
  $in_result = zuc_frm(5, "", $wrd_ids, 0, 14, 8);
echo "zuc_frm5:".$in_result[0];
  $in_result = zuc_frm(52, "{t19}=({f3}-{f5})/{f5}", $wrd_ids, 0, 14, 8);
echo "zuc_frm:".$in_result[0];
*/
/*
$frm_id = 31;
$frm_text = zuf_text($frm_id, $usr->id, $debug);
zuf_element_refresh($frm_id, $frm_text, $usr->id, 20);
*/


echo "Calculate value update ...<br>";

/*$val_ids_upd = array(348);
zuc_upd_val_lst($val_ids_upd, 14, $debug); */


// load the database records used for testing
echo "<h2>check database records</h2><br>";

  function zu_test_show_db_id($test_text, $result) {
    if ($result > 0) {
      echo "<font color=green>OK</font> " .$test_text." has id \"".$result."\"<br>";
    } else {
      echo "<font color=red>Error</font> ".$test_text." is missing<br>";
    }
  }


// reserved word types: at least one word of the each reserved type must exist for proper usage, but there may exist several alias
/*$word_other        = zu_sql_get_id ("word",    "other",       $debug); zu_test_show_db_id("Word other",        $word_other);
$word_next         = zu_sql_get_id ("word",    "other",       $debug); zu_test_show_db_id("Word ABB",          $word_abb);
$word_this         = zu_sql_get_id ("word",    "other",       $debug); zu_test_show_db_id("Word ABB",          $word_abb);
$word_previous     = zu_sql_get_id ("word",    "other",       $debug); zu_test_show_db_id("Word ABB",          $word_abb);
*/
// testing words
$word_abb          = zu_sql_get_id ("word",    TW_ABB,         $debug); zu_test_show_db_id("Word ABB",          $word_abb);
$word_nesn         = zu_sql_get_id ("word",    TW_NESN,        $debug); zu_test_show_db_id("Word Nestlé",       $word_nesn);
$word_country      = zu_sql_get_id ("word",    "Country",      $debug); zu_test_show_db_id("Word Country",      $word_country);
$word_ch           = zu_sql_get_id ("word",    "Switzerland",  $debug); zu_test_show_db_id("Word Switzerland",  $word_ch);
$word_revenues     = zu_sql_get_id ("word",    TW_SALES,       $debug); zu_test_show_db_id("Word Sales",        $word_revenues);
$word_2013         = zu_sql_get_id ("word",    TW_2013,        $debug); zu_test_show_db_id("Word 2013",         $word_2013);
$word_2014         = zu_sql_get_id ("word",    TW_2014,        $debug); zu_test_show_db_id("Word 2014",         $word_2014);
$word_2016         = zu_sql_get_id ("word",    TW_2016,        $debug); zu_test_show_db_id("Word 2016",         $word_2016);
$word_mio          = zu_sql_get_id ("word",    TW_M,           $debug); zu_test_show_db_id("Word mio",          $word_mio);
$word_percent      = zu_sql_get_id ("word",    TW_PCT,         $debug); zu_test_show_db_id("Word percent",      $word_percent);
$word_CHF          = zu_sql_get_id ("word",    TW_CHF,         $debug); zu_test_show_db_id("Word CHF",          $word_CHF);
//$formula_value     = zu_sql_get_id ("formula", "value",       $debug); zu_test_show_db_id("Formula Value",     $formula_value);
$word_type_time    = cl(SQL_WORD_TYPE_TIME);                           zu_test_show_db_id("Word Type Time",    $word_type_time);
$word_type_percent = cl(SQL_WORD_TYPE_PERCENT);                        zu_test_show_db_id("Word Type Percent", $word_type_percent);


// check the test record values in the database and correct it if needed  
echo "<h2>correct database records if needed</h2><br>";
  

echo "<h2>Test sql base functions</h2><br>";

// test sf (Sql Formatting) function
$text = "'4'";
$target = "4";
$result = sf($text);
$exe_start_time = zu_test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$text = "four";
$target = "'four'";
$result = sf($text);
$exe_start_time = zu_test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$text = "'four'";
$target = "'four'";
$result = sf($text);
$exe_start_time = zu_test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

$text = " ";
$target = "NULL";
$result = sf($text);
$exe_start_time = zu_test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

/*
echo "<h2>check word groups</h2><br>";

echo zut_group_review(1);

echo "<h2>done</h2><br>";
*/

echo "<h2>Test calc functions</h2><br>";


/*
// test zuc_get_formula
$formula_part_text = "{f19}";
$context_word_lst = array();
$context_word_lst[] = $word_nesn;
$time_word_id = $word_2016;
$target = 89469;
$result = zuc_get_formula($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuc_get_formula: the result for formula \"".$formula_part_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num_value
$formula_part_text = "{t6}{l12}";
$context_word_lst = array();
$context_word_lst[] = $word_nesn;
$time_word_id = $word_2016;
$target = 5;
$result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num_value
$formula_part_text = "{t6}{l12}{t83}";
$context_word_lst = array();
$context_word_lst[] = $word_nesn;
$time_word_id = $word_2016;
$target = 5;
$result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num_value
$formula_part_text = "{f19}";
$context_word_lst = array();
$context_word_lst[] = $word_nesn;
$time_word_id = $word_2016;
$target = 89469;
$result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num_value
$formula_part_text = "/{f19}";
$context_word_lst = array();
$context_word_lst[] = $word_nesn;
$time_word_id = $word_2016;
$target = 89469;
$result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test if zuf_2num still does a simple calculation
$frm_id = 0;
$math_text = "=(3 - 1) * 2";
$word_array = array();
$time_word_id = 0;
$target = 4;
$result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num
$frm_id = 0;
$math_text = " 3 ";
$word_array = array();
$time_word_id = 0;
$target = 3;
$result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num
$frm_id = 0;
$math_text = "=3 - 1";
$word_array = array();
$time_word_id = 0;
$target = 2;
$result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num
$frm_id = 0;
$math_text = "={t6}{l12}/{f19}";
$target = 1;
$word_array = array($word_nesn);
$time_word_id = $word_2016;
$result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num
$frm_id = 0;
$math_text = "=93686000000 - {f5}";
$target = 1000000000;
$word_array = array($word_abb,$word_revenues);
$time_word_id = 0;
$debug = false;
$result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num
$frm_id = 0;
$math_text = "={f4} - {f5}";
$target = 1100000000;
$word_array = array($word_abb,$word_revenues);
$time_word_id = 0;
$debug = false;
$result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num
$frm_id = 0;
$math_text = "={f2}";
$target = 1100000000;
$word_array = array($word_abb,$word_revenues);
$time_word_id = 0;
$debug = false;
$result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num
$frm_id = 0;
$target = "1.19%";
$word_array = array($word_abb,$word_revenues);
$time_word_id = 0;
$debug = false;
$result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula with id ".$frm_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuf_2num
$frm_id = 0;
$target = "1.19%";
$word_array = array($word_abb,$word_revenues);
$time_word_id = 0;
$debug = false;
$result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
$exe_start_time = zu_test_show_result(", zuf_2num: the result for formula with id ".$frm_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zuc_has_operator
$math_text = "3 - 1";
$target = true;
$result = zuc_has_operator($math_text);
$exe_start_time = zu_test_show_result(", zuc_has_operator: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

*/



echo "<h2>Test link functions</h2><br>";

// test zul_name 
$id = "2";
$target = "is a";
$result = zul_name($id);
$exe_start_time = zu_test_show_result(", zul_name of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zul_plural 
$id = "2";
$target = "are";
$result = zul_plural($id);
$exe_start_time = zu_test_show_result(", zul_plural of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zul_reverse 
$id = "2";
$target = "are";
$result = zul_reverse($id);
$exe_start_time = zu_test_show_result(", zul_reverse of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zul_reverse 
$id = "1";
$target = "is used for";
$result = zul_reverse($id);
$exe_start_time = zu_test_show_result(", zul_reverse of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zul_plural_reverse 
$id = "2";
$target = "are";
$result = zul_plural_reverse($id);
$exe_start_time = zu_test_show_result(", zul_plural_reverse of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

// test zul_type
/*$name = "is a";
$target = "2";
$result = zul_type($name);
$exe_start_time = zu_test_show_result(", zul_type of id ".$name, $target, $result, $exe_start_time, TIMEOUT_LIMIT); */

// add functions
// word_list / word chain
// word matrix


echo "<h2>Display user names</h2><br>";

// display user names
echo 'php user: '.$_SERVER['PHP_AUTH_USER'].'<br>';
echo 'remote user: '.$_SERVER['REMOTE_USER'].'<br>';
echo 'user id: '.$usr->id.'<br>';

echo "<br>";
echo '<h2>';
echo 'Total test cases: '.$total_tests.'<br>';
echo 'Total errors: '.$error_counter.'<br>';
echo 'Total timeouts: '.$timeout_counter;
echo "</h2><br>";
echo "<br>";
echo "end of test";
echo "<br>";

// Free resultset
mysql_free_result($result);

// Closing connection
zu_end($link, $debug);
?>
