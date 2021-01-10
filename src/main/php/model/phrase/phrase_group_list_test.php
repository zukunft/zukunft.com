<?php 

/*

  phrase_group_list_test.php - PHRASE GROUP LIST function unit TESTs
  --------------------------
  

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

function run_phrase_group_list_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the phrase group list class (classes/phrase_group_list.php)</h2><br>";

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
  $phr_lst->add_name(TP_ZH_INS);
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
  $exe_start_time = test_show_result(', phrase_group_list->add of '.$abb_grp->name().', '.$zh_grp->name().', '.$abb_grp->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


  // test add a phrase group to a phrase group list
  $grp_lst = New phrase_group_list;
  $grp_lst->usr = $usr;
  $grp_lst->add($abb_grp, $debug-1);
  $grp_lst->add($zh_grp, $debug-1);
  $grp_lst->add($ins_grp, $debug-1);
  $result = $grp_lst->name($debug-1);
  $target = ''.TW_MIO.','.TW_CHF.','.TW_SALES.','.TW_ABB.' and '.TW_MIO.','.TW_CHF.','.TW_TAX.','.TW_ZH.' and '.TW_MIO.','.TW_CHF.','.TW_TAX.','.TP_ZH_INS.'';
  $exe_start_time = test_show_result(', phrase_group_list->add of '.$zh_grp->name().', '.$zh_grp->name().', '.$ins_grp->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT);


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
  $exe_start_time = test_show_result(', phrase_group_list->common_phrases of '.$grp_lst->name(), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}

?>
