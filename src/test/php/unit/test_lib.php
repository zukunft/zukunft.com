<?php

/*

  test_lib.php - TESTing the general zukunft.com Library functions
  ------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

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

global $db_con;


function run_string_unit_tests()
{

    test_header('Test the zukunft.com base functions (zu_lib.php)');

    test_subheader('strings');

    // test zu_trim
    $text = "  This  text  has  many  spaces  ";
    $target = "This text has many spaces";
    $result = zu_trim($text);
    test_dsp(", zu_trim", $target, $result);

    // test zu_str_left
    $text = "This are the left 4";
    $pos = 4;
    $target = "This";
    $result = zu_str_left($text, $pos);
    test_dsp(", zu_str_left: What are the left \"" . $pos . "\" chars of \"" . $text . "\"", $target, $result);

    // test zu_str_right
    $text = "This are the right 7";
    $pos = 7;
    $target = "right 7";
    $result = zu_str_right($text, $pos);
    test_dsp(", zu_str_right: What are the right \"" . $pos . "\" chars of \"" . $text . "\"", $target, $result);

    // test zu_str_left_of
    $text = "This is left of that ";
    $maker = " of that";
    $target = "This is left";
    $result = zu_str_left_of($text, $maker);
    test_dsp(", zu_str_left_of: What is left of \"" . $maker . "\" in \"" . $text . "\"", $target, $result);

    // test zu_str_left_of
    $text = "This is left of that, but not of that";
    $maker = " of that";
    $target = "This is left";
    $result = zu_str_left_of($text, $maker);
    test_dsp(", zu_str_left_of: What is left of \"" . $maker . "\" in \"" . $text . "\"", $target, $result);

    // test zu_str_right_of
    $text = "That is right of this";
    $maker = "That is right ";
    $target = "of this";
    $result = zu_str_right_of($text, $maker);
    test_dsp(", zu_str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $target, $result);

    // test zu_str_right_of
    $text = "00000";
    $maker = "0";
    $target = "0000";
    $result = zu_str_right_of($text, $maker);
    test_dsp(", zu_str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $target, $result);

    // test zu_str_right_of
    $text = "The formula id of {f23}.";
    $maker = "{f";
    $target = "23}.";
    $result = zu_str_right_of($text, $maker);
    test_dsp(", zu_str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $target, $result);

    // test zu_str_between
    $text = "The formula id of {f23}.";
    $maker_start = "{f";
    $maker_end = "}";
    $target = "23";
    $result = zu_str_between($text, $maker_start, $maker_end);
    test_dsp(", zu_str_between: " . $text . "", $target, $result);

    // test zu_str_between
    $text = "The formula id of {f4} / {f5}.";
    $maker_start = "{f";
    $maker_end = "}";
    $target = "4";
    $result = zu_str_between($text, $maker_start, $maker_end);
    test_dsp(", zu_str_between: " . $text . "", $target, $result);

    test_subheader('arrays and lists');

    // test dsp_array
    $test_array = [1,2,3];
    $target = '1,2,3';
    $result = dsp_array($test_array);
    test_dsp(", dsp_array: ", $target, $result);

    $test_array = ["A","B","C"];
    $target = 'A,B,C';
    $result = dsp_array($test_array);
    test_dsp(", dsp_array: ", $target, $result);

    $test_array = [];
    $target = 'null';
    $result = dsp_array($test_array);
    test_dsp(", dsp_array: ", $target, $result);

    $test_array = null;
    $target = 'null';
    $result = dsp_array($test_array);
    test_dsp(", dsp_array: ", $target, $result);
}