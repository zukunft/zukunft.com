<?php 

/*

  test_legacy.php - TESTing of LEGACY functions
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

function run_legacy_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  test_header('Test sql base functions');

  // test sf (Sql Formatting) function
  $text = "'4'";
  $target = "4";
  $result = sf($text);
  $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  $text = "four";
  $target = "'four'";
  $result = sf($text);
  $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  $text = "'four'";
  $target = "'four'";
  $result = sf($text);
  $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  $text = " ";
  $target = "NULL";
  $result = sf($text);
  $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  /*
  echo "<h2>check word groups</h2><br>";

  echo zut_group_review(1);

  echo "<h2>done</h2><br>";
  */

  test_header('Test calc functions');


  /*
  // test zuc_get_formula
  $formula_part_text = "{f19}";
  $context_word_lst = array();
  $context_word_lst[] = $word_nesn;
  $time_word_id = $word_2016;
  $target = TV_NESN_SALES_2016;
  $result = zuc_get_formula($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuc_get_formula: the result for formula \"".$formula_part_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num_value
  $formula_part_text = "{t6}{l12}";
  $context_word_lst = array();
  $context_word_lst[] = $word_nesn;
  $time_word_id = $word_2016;
  $target = 5;
  $result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num_value
  $formula_part_text = "{t6}{l12}{t83}";
  $context_word_lst = array();
  $context_word_lst[] = $word_nesn;
  $time_word_id = $word_2016;
  $target = 5;
  $result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num_value
  $formula_part_text = "{f19}";
  $context_word_lst = array();
  $context_word_lst[] = $word_nesn;
  $time_word_id = $word_2016;
  $target = TV_NESN_SALES_2016;
  $result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num_value
  $formula_part_text = "/{f19}";
  $context_word_lst = array();
  $context_word_lst[] = $word_nesn;
  $time_word_id = $word_2016;
  $target = TV_NESN_SALES_2016;
  $result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test if zuf_2num still does a simple calculation
  $frm_id = 0;
  $math_text = "=(3 - 1) * 2";
  $word_array = array();
  $time_word_id = 0;
  $target = 4;
  $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num
  $frm_id = 0;
  $math_text = " 3 ";
  $word_array = array();
  $time_word_id = 0;
  $target = 3;
  $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num
  $frm_id = 0;
  $math_text = "=3 - 1";
  $word_array = array();
  $time_word_id = 0;
  $target = 2;
  $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num
  $frm_id = 0;
  $math_text = "={t6}{l12}/{f19}";
  $target = 1;
  $word_array = array($word_nesn);
  $time_word_id = $word_2016;
  $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num
  $frm_id = 0;
  $math_text = "=93686000000 - {f5}";
  $target = 1000000000;
  $word_array = array($word_abb,$word_revenues);
  $time_word_id = 0;
  $debug = false;
  $result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num
  $frm_id = 0;
  $math_text = "={f4} - {f5}";
  $target = 1100000000;
  $word_array = array($word_abb,$word_revenues);
  $time_word_id = 0;
  $debug = false;
  $result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num
  $frm_id = 0;
  $math_text = "={f2}";
  $target = 1100000000;
  $word_array = array($word_abb,$word_revenues);
  $time_word_id = 0;
  $debug = false;
  $result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num
  $frm_id = 0;
  $target = "1.19%";
  $word_array = array($word_abb,$word_revenues);
  $time_word_id = 0;
  $debug = false;
  $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula with id ".$frm_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuf_2num
  $frm_id = 0;
  $target = "1.19%";
  $word_array = array($word_abb,$word_revenues);
  $time_word_id = 0;
  $debug = false;
  $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuf_2num: the result for formula with id ".$frm_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_has_operator
  $math_text = "3 - 1";
  $target = true;
  $result = zuc_has_operator($math_text);
  $exe_start_time = test_show_result(", zuc_has_operator: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  */



  test_header('Test link functions');

  // test zul_name 
  $id = "2";
  $target = "is a";
  $result = zul_name($id);
  $exe_start_time = test_show_result(", zul_name of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zul_plural 
  $id = "2";
  $target = "are";
  $result = zul_plural($id);
  $exe_start_time = test_show_result(", zul_plural of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zul_reverse 
  $id = "2";
  $target = "are";
  $result = zul_reverse($id);
  $exe_start_time = test_show_result(", zul_reverse of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zul_reverse 
  $id = "1";
  $target = "is used for";
  $result = zul_reverse($id);
  $exe_start_time = test_show_result(", zul_reverse of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zul_plural_reverse 
  $id = "2";
  $target = "are";
  $result = zul_plural_reverse($id);
  $exe_start_time = test_show_result(", zul_plural_reverse of id ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zul_type
  /*$name = "is a";
  $target = "2";
  $result = zul_type($name);
  $exe_start_time = test_show_result(", zul_type of id ".$name, $target, $result, $exe_start_time, TIMEOUT_LIMIT); */

  // add functions
  // word_list / word chain
  // word matrix

  test_header('Old test functions');

  /*



  echo "<h2>finish all open database batch and consistency check batch jobs</h2><br>";
  $result = zut_calc_usage(); echo "word usage updated ... ".$result."<br>";
  $result = zul_calc_usage(); echo "verb usage updated ... ".$result."<br>";

  echo "refresh formula references ... ";
  echo "done<br>";
  */

  // load the database records used for testing
  test_header('check database records');

    function test_show_db_id($test_text, $result) {
      if ($result > 0) {
        echo "<font color=green>OK</font> " .$test_text." has id \"".$result."\"<br>";
      } else {
        echo "<font color=red>Error</font> ".$test_text." is missing<br>";
      }
    }


  // reserved word types: at least one word of the each reserved type must exist for proper usage, but there may exist several alias
  /*$word_other        = zu_sql_get_id ("word",    "other",       $debug); test_show_db_id("Word other",        $word_other);
  $word_next         = zu_sql_get_id ("word",    "other",       $debug); test_show_db_id("Word ABB",          $word_abb);
  $word_this         = zu_sql_get_id ("word",    "other",       $debug); test_show_db_id("Word ABB",          $word_abb);
  $word_previous     = zu_sql_get_id ("word",    "other",       $debug); test_show_db_id("Word ABB",          $word_abb);
  */
  // testing words
  $word_abb          = zu_sql_get_id ("word",    TW_ABB,         $debug); test_show_db_id("Word ABB",          $word_abb);
  $word_nesn         = zu_sql_get_id ("word",    TW_NESN,        $debug); test_show_db_id("Word Nestlé",       $word_nesn);
  $word_country      = zu_sql_get_id ("word",    "Country",      $debug); test_show_db_id("Word Country",      $word_country);
  $word_ch           = zu_sql_get_id ("word",    "Switzerland",  $debug); test_show_db_id("Word Switzerland",  $word_ch);
  $word_revenues     = zu_sql_get_id ("word",    TW_SALES,       $debug); test_show_db_id("Word Sales",        $word_revenues);
  $word_2013         = zu_sql_get_id ("word",    TW_2013,        $debug); test_show_db_id("Word 2013",         $word_2013);
  $word_2014         = zu_sql_get_id ("word",    TW_2014,        $debug); test_show_db_id("Word 2014",         $word_2014);
  $word_2016         = zu_sql_get_id ("word",    TW_2016,        $debug); test_show_db_id("Word 2016",         $word_2016);
  $word_mio          = zu_sql_get_id ("word",    TW_M,           $debug); test_show_db_id("Word mio",          $word_mio);
  $word_percent      = zu_sql_get_id ("word",    TW_PCT,         $debug); test_show_db_id("Word percent",      $word_percent);
  $word_CHF          = zu_sql_get_id ("word",    TW_CHF,         $debug); test_show_db_id("Word CHF",          $word_CHF);
  //$formula_value     = zu_sql_get_id ("formula", "value",       $debug); test_show_db_id("Formula Value",     $formula_value);
  $word_type_time    = cl(SQL_WORD_TYPE_TIME);                           test_show_db_id("Word Type Time",    $word_type_time);
  $word_type_percent = cl(SQL_WORD_TYPE_PERCENT);                        test_show_db_id("Word Type Percent", $word_type_percent);   
 
}

?>
