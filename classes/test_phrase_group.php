<?php 

/*

  test_phrase_group.php - TESTing of the PHRASE GROUP functions
  ---------------------
  

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

function run_phrase_group_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the pharse group class (classes/phrase_group.php)</h2><br>";

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
  $exe_start_time = test_show_result(', phrase_group->load by ids for '.implode(",",$wrd_lst->names()), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... and if the time word is correctly excluded
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load($debug-1);
  $abb_grp = New phrase_group;
  $abb_grp->usr = $usr;
  $abb_grp->ids = $wrd_lst->ids;
  $abb_grp->load($debug-1);
  $result = $abb_grp->id;
  $target = '2116';
  $exe_start_time = test_show_result(', phrase_group->load by ids excluding time for '.implode(",",$wrd_lst->names()), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // load based on id
  if ($abb_grp->id > 0) {
    $abb_grp_reload = New phrase_group;
    $abb_grp_reload->usr = $usr;
    $abb_grp_reload->id = $abb_grp->id;
    $abb_grp_reload->load($debug-1);
    $abb_grp_reload->load_lst($debug-1);
    $wrd_lst_reloaded = $abb_grp_reload->wrd_lst;
    $result = implode(",",$wrd_lst_reloaded->names());
    $target = 'million,CHF,Sales,ABB';
    $exe_start_time = test_show_result(', phrase_group->load for id '.$abb_grp->id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  }

  // if a new group is created in needed when a triple is added
  $wrd_zh = load_word(TW_ZH, $debug-1);
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
  $exe_start_time = test_show_result(', phrase_group->load by ids for '.$lnk_company->name.' and '.implode(",",$wrd_lst->names()), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

  // test names
  $result = implode(",",$zh_ins_grp->names($debug-1));
  $target = 'million,CHF,Sales,Zurich Insurance';  // fix the issue after the libraries are excluded
  //$target = 'million,CHF,Sales,'.TP_ZH_INS.'';
  $exe_start_time = test_show_result(', phrase_group->names', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

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
  $exe_start_time = test_show_result(', phrase_group->load_link_ids for '.$phr_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

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
  $exe_start_time = test_show_result(', phrase_group->load_link_ids for '.$phr_lst->dsp_id(), $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

  // test value
  // test value_scaled


  // load based on wrd and lnk lst
  // load based on wrd and lnk ids
  // maybe if cleanup removes the unneeded group

  // test the user sandbox for the user names
  // test if the search links are correctly created

}

?>
