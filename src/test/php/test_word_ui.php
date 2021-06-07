<?php 

/*

  test_word_ui.php - TESTing of the WORD User Interface
  ------------------
  

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

// --------------------------------------
// start testing the system functionality 
// --------------------------------------
  
function run_word_ui_test ($debug) {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the word frontend scripts (e.g. /word_add.php)');

  // call the add word page and check if at least some keywords are returned
  $wrd_ABB = New word_dsp;
  $wrd_ABB->name = TW_ABB;
  $wrd_ABB->usr = $usr;
  $wrd_ABB->load($debug-1);
  $vrb_is = cl(DBL_LINK_TYPE_IS);
  $wrd_type = cl(DBL_WORD_TYPE_NORMAL);
  $result = file_get_contents('https://zukunft.com/http/word_add.php?verb='.$vrb_is.'&word='.$wrd_ABB->id.'&type=1&back='.$wrd_ABB->id.'');
  $target = TW_ABB;
  $exe_start_time = test_show_contains(', frontend word_add.php '.$result.' contains at least '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  // test the edit word frontend
  $result = file_get_contents('https://zukunft.com/http/word_edit.php?id='.$wrd_ABB->id.'&back='.$wrd_ABB->id.'');
  $target = TW_ABB;
  $exe_start_time = test_show_contains(', frontend word_edit.php '.$result.' contains at least '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  // test the del word frontend
  $result = file_get_contents('https://zukunft.com/http/word_del.php?id='.$wrd_ABB->id.'&back='.$wrd_ABB->id.'');
  $target = TW_ABB;
  $exe_start_time = test_show_contains(', frontend word_del.php '.$result.' contains at least '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

  test_header('Test the display list class (classes/display_list.php)');

  // not yet used
  /*
  $phr_corp = load_phrase(TEST_WORD, $debug-1);
  $phr_ABB  = load_phrase(TW_ABB,    $debug-1);
  $sel = New selector;
  $sel->usr        = $usr;
  $sel->form       = 'test_form';
  $sel->name       = 'select_company';  
  $sel->sql        = $phr_corp->sql_list ($phr_corp, $debug-1);
  $sel->selected   = $phr_ABB->id;
  $sel->dummy_text = '... please select';
  $result .= $sel->display ($debug-1);
  $target = TW_ABB;
  $exe_start_time = test_show_contains(', display_selector->display of all '.$phr_corp->name.' with '.$wrd_ABB->name.' selected', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  */
  
}
