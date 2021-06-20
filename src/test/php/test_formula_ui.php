<?php 

/*

  test_formula_ui.php - TESTing of the FORMULA User Interface class
  -------------------
  

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

function run_formula_ui_test ($debug = 0) {

  global $exe_start_time;
  
  test_header('Test the formula frontend scripts (e.g. /formula_add.php)');

  // call the add formula page and check if at least some keywords are returned
  $frm = load_formula(TF_INCREASE, $debug-1);
  $result = file_get_contents('https://zukunft.com/http/formula_add.php?word='.TEST_WORD_ID.'&back='.TEST_WORD_ID.'');
  $target = 'Add new formula for';
  $exe_start_time = test_show_contains(', frontend formula_add.php '.$result.' contains at least the headline', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);
  $target = TEST_WORD;
  $exe_start_time = test_show_contains(', frontend formula_add.php '.$result.' contains at least the linked word '.TEST_WORD, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

  // test the edit formula frontend
  $result = file_get_contents('https://zukunft.com/http/formula_edit.php?id='.$frm->id.'&back='.TEST_WORD_ID.'');
  $target = TF_INCREASE;
  $exe_start_time = test_show_contains(', frontend formula_edit.php '.$result.' contains at least '.$frm->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  // test the del formula frontend
  $result = file_get_contents('https://zukunft.com/http/formula_del.php?id='.$frm->id.'&back='.TEST_WORD_ID.'');
  $target = TF_INCREASE;
  $exe_start_time = test_show_contains(', frontend formula_del.php '.$result.' contains at least '.$frm->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

}