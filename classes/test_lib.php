<?php 

/*

  test_lib.php - TESTing of the LIBrary functions
  ------------
  

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

function run_lib_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the basic zukunft functions (zu_lib.php)</h2><br>";

  echo "<h3>strings</h3><br>";
  // test zu_str_left
  $text = "This are the left 4";
  $pos = 4;
  $target = "This";
  $result = zu_str_left($text, $pos);
  $exe_start_time = test_show_result(", zu_str_left: What are the left \"".$pos."\" chars of \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zu_str_right
  $text = "This are the right 7";
  $pos = 7;
  $target = "right 7";
  $result = zu_str_right($text, $pos);
  $exe_start_time = test_show_result(", zu_str_right: What are the right \"".$pos."\" chars of \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zu_str_left_of
  $text = "This is left of that ";
  $maker = " of that";
  $target = "This is left";
  $result = zu_str_left_of($text, $maker);
  $exe_start_time = test_show_result(", zu_str_left_of: What is left of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zu_str_left_of
  $text = "This is left of that, but not of that";
  $maker = " of that";
  $target = "This is left";
  $result = zu_str_left_of($text, $maker);
  $exe_start_time = test_show_result(", zu_str_left_of: What is left of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zu_str_right_of
  $text = "That is right of this";
  $maker = "That is right ";
  $target = "of this";
  $result = zu_str_right_of($text, $maker);
  $exe_start_time = test_show_result(", zu_str_right_of: What is right of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zu_str_right_of
  $text = "00000";
  $maker = "0";
  $target = "0000";
  $result = zu_str_right_of($text, $maker);
  $exe_start_time = test_show_result(", zu_str_right_of: What is right of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zu_str_right_of
  $text = "The formula id of {f23}.";
  $maker = "{f";
  $target = "23}.";
  $result = zu_str_right_of($text, $maker);
  $exe_start_time = test_show_result(", zu_str_right_of: What is right of \"".$maker."\" in \"".$text."\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zu_str_between
  $text = "The formula id of {f23}.";
  $maker_start = "{f";
  $maker_end = "}";
  $target = "23";
  $result = zu_str_between($text, $maker_start, $maker_end);
  $exe_start_time = test_show_result(", zu_str_between: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test zu_str_between
  $text = "The formula id of {f4} / {f5}.";
  $maker_start = "{f";
  $maker_end = "}";
  $target = "4";
  $result = zu_str_between($text, $maker_start, $maker_end);
  $exe_start_time = test_show_result(", zu_str_between: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}

?>
