<?php 

/*

  test_word_display.php - TESTing of the WORD DISPLAY functions
  ---------------
  

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
  
function run_word_display_test ($debug = 0) {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the word display class (classes/word_display.php)');

  // check the graph display
  // test uses the old function zum_word_list to compare, so it is a kind of double coding
  // correct test would be using a "fixed HTML text contains"
  $wrd_ABB = New word_dsp;
  $wrd_ABB->name = TW_ABB;
  $wrd_ABB->usr = $usr;
  $wrd_ABB->load($debug-1);
  $direction = 'up';
  $target = TW_ABB.' is a';
  $result = substr($wrd_ABB->dsp_graph ($direction, 0, $debug-1),0,8);
  $exe_start_time = test_show_result('word_dsp->dsp_graph '.$direction.' for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check the graph display
  $wrd_ABB = New word_dsp;
  $wrd_ABB->name = TW_ABB;
  $wrd_ABB->usr = $usr;
  $wrd_ABB->load($debug-1);
  $direction = 'down';
  $target = zut_html_list_related ($wrd_ABB->id, $direction, $usr->id, $debug-1);
  $result = $wrd_ABB->dsp_graph ($direction, 0, $debug-1);
  $exe_start_time = test_show_result('word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... and the other side
  $direction = 'up';
  $target = zut_html_list_related ($wrd_ABB->id, $direction, $usr->id, $debug-1);
  $result = $wrd_ABB->dsp_graph ($direction, 0, $debug-1);
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  $exe_start_time = test_show_result('word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  test_show_diff ($target, $result);

  // ... and the graph display for Zurich
  $wrd_ZH = New word_dsp;
  $wrd_ZH->name = TW_ZH;
  $wrd_ZH->usr = $usr;
  $wrd_ZH->load($debug-1);
  $direction = 'down';
  $target = zut_html_list_related ($wrd_ZH->id, $direction, $usr->id, $debug);
  $result = $wrd_ZH->dsp_graph ($direction, 0, $debug-1);
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  $exe_start_time = test_show_result('word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_ZH->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... and the other side
  $direction = 'up';
  $target = zut_html_list_related ($wrd_ZH->id, $direction, $usr->id, $debug);
  $result = $wrd_ZH->dsp_graph ($direction, 0, $debug-1);
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  $exe_start_time = test_show_result('word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_ZH->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... and the graph display for 2012
  $wrd_2012 = New word_dsp;
  $wrd_2012->name = TW_2012;
  $wrd_2012->usr = $usr;
  $wrd_2012->load($debug-1);
  $direction = 'down';
  $target = zut_html_list_related ($wrd_2012->id, $direction, $usr->id, $debug);
  $result = $wrd_2012->dsp_graph ($direction, 0, $debug-1);
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  $exe_start_time = test_show_result('word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_2012->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... and the other side
  $direction = 'up';
  $target = zut_html_list_related ($wrd_2012->id, $direction, $usr->id, $debug);
  $result = $wrd_2012->dsp_graph ($direction, 0, $debug-1);
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  $exe_start_time = test_show_result('word_dsp->dsp_graph compare to old '.$direction.' for '.$wrd_2012->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // the value table for ABB
  $wrd_ABB = New word_dsp;
  $wrd_ABB->name = TW_ABB;
  $wrd_ABB->usr = $usr;
  $wrd_ABB->load($debug-1);
  $wrd_year = New word_dsp;
  $wrd_year->name = TW_YEAR;
  $wrd_year->usr = $usr;
  $wrd_year->load($debug-1);
  /*
  $target = zut_dsp_list_wrd_val($wrd_ABB->id, $wrd_year->id, $usr->id, $debug-1);
  $target = substr($target,0,208);
  */
  $target = "ABB";
  $result = $wrd_ABB->dsp_val_list ($wrd_year, 0, $debug-1);
  //$exe_start_time = test_show_result('word_dsp->dsp_val_list compare to old for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);
  $exe_start_time = test_show_contains(', word_dsp->dsp_val_list compare to old for '.$wrd_ABB->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

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
  $exe_start_time = test_show_result('word_dsp->dsp_val_list compare to old for '.$wrd_company->name, $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  */


  test_header('Test the display selector class (classes/display_selector.php)');

  // for testing the selector display a company selector and select ABB
  $phr_corp = load_phrase(TEST_WORD, $debug-1);
  $phr_ABB = load_phrase(TW_ABB, $debug-1);
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

}