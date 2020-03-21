<?php 

/*

  test_math.php - TESTing of the MATHematical functions
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

function run_math_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the internal math function (which should be replaced by RESTful R-Project call)</h2><br>";


  // calculate the target price for nestle: 
  // if there is a word with the formula name assume that this word is added to the result
  // so target price for Nestle should save a value to the formula result table linked to nestle and target price

  // build a list of all formula results that needs to be update



  // test zuc_has_braket
  $math_text = "(-10744--10744)/-10744";
  $target = 0;
  $result = zuc_math_parse($math_text, array(), Null, $debug);
  $exe_start_time = test_show_result(", zuc_math: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

  // test zuc_parse
  /*$formula_id = $formula_value;
  $target = "45548";
  $word_array =           array($word_abb,$word_revenues,$word_CHF);
  $word_ids = zut_sql_ids(array($word_abb,$word_revenues,$word_CHF));
  $time_word_id = $word_2013;
  $debug = false;
  $result = zuc_parse($formula_id, ZUP_RESULT_TYPE_VALUE, $word_ids, $time_word_id, $debug);
  $exe_start_time = test_show_result(", zuc_parse: the result for formula with id ".$formula_id, $target, $result, $exe_start_time, TIMEOUT_LIMIT); */

  // test zuc_is_text_only 
  $formula = "\"this is just a text\"";
  $target = true;
  $result = zuc_is_text_only($formula);
  $exe_start_time = test_show_result(", zuc_is_text_only: a text like ".$formula, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_pos_seperator 
  $formula = "1+(2-1)";
  $seperator = "+";
  $target = 1;
  $result = zuc_pos_seperator($formula, $seperator);
  $exe_start_time = test_show_result(", zuc_pos_seperator: seperator ".$seperator." is in ".$formula." at ", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_has_braket
  $math_text = "(2 - 1) * 2";
  $target = true;
  $result = zuc_has_braket($math_text);
  $exe_start_time = test_show_result(", zuc_has_braket: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_has_formula
  $formula = "{f4} / {f5}";
  $target = true;
  $result = zuc_has_formula($formula, ZUP_RESULT_TYPE_VALUE, 0);
  $exe_start_time = test_show_result(", zuc_has_formula: the result for formula \"".$formula."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_is_date
  $date_text = "01.02.2013";
  $target = true;
  $result = zuc_is_date($date_text);
  $exe_start_time = test_show_result(", zuc_is_date: the result for \"".$date_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // test zuc_pos_word
  $formula_text = "{t6}";
  $target = "0";
  $result = zuc_pos_word($formula_text);
  $exe_start_time = test_show_result(", zuc_pos_word: the result for formula \"".$formula_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zut_keep_only_specific
  /*$word_array = array();
  $word_array[] = $word_revenues;
  $word_array[] = $word_nesn;
  $word_array[] = $word_ch;
  $target = $word_array; // because 83 (Country) should be excluded
  $word_array[] = $word_country;
  $result = zut_keep_only_specific($word_array, $debug);
  $exe_start_time = test_show_result(", zut_keep_only_specific: the result for word array \"".implode(",",$word_array)."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  */


  // test zuc_math_bracket
  $math_text = "(3 - 1) * 2";
  $target = "2 * 2";
  $result = zuc_math_bracket($math_text, array(), 0, 0);
  $exe_start_time = test_show_result(", zuc_math_bracket: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_math_parse
  $math_text = "3 - 1";
  $target = 2;
  $result = zuc_math_parse($math_text, ZUP_RESULT_TYPE_VALUE);
  $exe_start_time = test_show_result(", zuc_math_parse: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_math_parse
  $math_text = "2 * 2";
  $target = 4;
  $result = zuc_math_parse($math_text, ZUP_RESULT_TYPE_VALUE);
  $exe_start_time = test_show_result(", zuc_math_parse: the result for formula \"".$math_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_is_math_symbol_or_num
  $formula_part_text = "/{f19}";
  $target = 1;
  $result = zuc_is_math_symbol_or_num($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuc_is_math_symbol_or_num: the result for formula \"".$formula_part_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zuc_get_math_symbol
  $formula_part_text = "/{f19}";
  $target = "/";
  $result = zuc_get_math_symbol($formula_part_text, $context_word_lst, $time_word_id, $usr->id, $debug);
  $exe_start_time = test_show_result(", zuc_get_math_symbol: the result for formula \"".$formula_part_text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);





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

}

?>