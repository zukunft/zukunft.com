<?php 

/*

  phrase_group_list_test.php - PHRASE GROUP LIST function unit TESTs
  --------------------------
  

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

function run_phrase_group_list_test () {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the phrase group list class (src/main/php/model/phrase/phrase_group_list.php)');

  // define some phrase groups for testing

  // ABB Sales
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ABB);
  $phr_lst->add_name(TW_SALES);
  $phr_lst->add_name(TW_CHF);
  $phr_lst->add_name(TW_MIO);
  $phr_lst->load();
  $abb_grp = $phr_lst->get_grp();

  // Zurich taxes
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ZH);
  $phr_lst->add_name(TW_TAX);
  $phr_lst->add_name(TW_CHF);
  $phr_lst->add_name(TW_MIO);
  $phr_lst->load();
  $zh_grp = $phr_lst->get_grp();

  // Zurich Insurance taxes
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TP_ZH_INS);
  $phr_lst->add_name(TW_TAX);
  $phr_lst->add_name(TW_CHF);
  $phr_lst->add_name(TW_MIO);
  $phr_lst->load();
  $ins_grp = $phr_lst->get_grp();

  // test add a phrase group to a phrase group list
  $grp_lst = New phrase_group_list;
  $grp_lst->usr = $usr;
  $grp_lst->add($abb_grp);
  $grp_lst->add($zh_grp);
  $grp_lst->add($abb_grp);
  $result = $grp_lst->name();
  $target = ''.TW_MIO.','.TW_CHF.','.TW_SALES.','.TW_ABB.' and '.TW_MIO.','.TW_CHF.','.TW_TAX.','.TW_ZH.'';
  $exe_start_time = test_show_result('phrase_group_list->add of '.$abb_grp->dsp_id().', '.$zh_grp->dsp_id().', '.$abb_grp->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);


  // test add a phrase group to a phrase group list
  $grp_lst = New phrase_group_list;
  $grp_lst->usr = $usr;
  $grp_lst->add($abb_grp);
  $grp_lst->add($zh_grp);
  $grp_lst->add($ins_grp);
  $result = $grp_lst->name();
  $target = ''.TW_MIO.','.TW_CHF.','.TW_SALES.','.TW_ABB.' and '.TW_MIO.','.TW_CHF.','.TW_TAX.','.TW_ZH.' and '.TW_MIO.','.TW_CHF.','.TW_TAX.','.TP_ZH_INS.'';
  $exe_start_time = test_show_result('phrase_group_list->add of '.$zh_grp->dsp_id().', '.$zh_grp->dsp_id().', '.$ins_grp->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // test getting the common phrases of several group
  $grp_lst = New phrase_group_list;
  $grp_lst->usr = $usr;
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->load();
  $abb_grp = $wrd_lst->get_grp();
  $grp_lst->add($abb_grp);
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ZH);
  $wrd_lst->add_name(TW_TAX);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->load();
  $zh_grp = $wrd_lst->get_grp();
  $grp_lst->add($zh_grp);
  $phr_lst = $grp_lst->common_phrases();
  $result = $phr_lst->name();
  $target = '"'.TW_MIO.'","'.TW_CHF.'"';
  $exe_start_time = test_show_result('phrase_group_list->common_phrases of '.$grp_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}