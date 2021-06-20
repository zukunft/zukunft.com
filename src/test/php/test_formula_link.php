<?php 

/*

  test_formula_link.php - TESTing of the FORMULA LINK functions
  ---------------------
  

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

function create_base_formula_links () {
  echo "<h2>Check if all base formulas link correctly</h2><br>";
  test_formula_link(TF_SCALE_BIL, TW_BIL);
  test_formula_link(TF_SCALE_MIO, TW_MIO);
  test_formula_link(TF_SCALE_K,   TW_K);
  echo "<br><br>";
}

function run_formula_link_test ($debug = 0) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  test_header('Test the formula link class (classes/formula_link.php)');

  // link the test formula to another word
  $frm = load_formula(TF_ADD_RENAMED, $debug-1);
  $phr = New phrase;
  $phr->name = TW_ADD_RENAMED;
  $phr->usr = $usr2;
  $phr->load($debug-1);
  $result = $frm->link_phr($phr, $debug-1);
  $target = '1';
  $exe_start_time = test_show_result('formula_link->link_phr "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // ... check the correct logging
  $log = New user_log_link;
  $log->table = 'formula_links';
  $log->new_from_id = $frm->id;
  $log->new_to_id = $phr->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job linked Formula Test to '.TW_ADD_RENAMED.'';
  $exe_start_time = test_show_result('formula_link->link_phr logged for "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the link can be loaded by formula and phrase id and base on the id the correct formula and phrase objects are loaded
  $frm_lnk = New formula_link;
  $frm_lnk->usr = $usr;
  $frm_lnk->fob = $frm;
  $frm_lnk->tob = $phr;
  $frm_lnk->load($debug-1);

  $frm_lnk2 = New formula_link;
  $frm_lnk2->usr = $usr;
  $frm_lnk2->id  = $frm_lnk->id;
  $frm_lnk2->load($debug-1);
  $frm_lnk2->load_objects($debug-1);

  // ... if form name is correct the chain of load via object, reload via id and load of the objects has worked
  $result = $frm_lnk2->fob->name();
  $target = $frm->name(); 
  $exe_start_time = test_show_result('formula_link->load by formula id and link id "'.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  $result = $frm_lnk2->tob->name();
  $target = $phr->name(); 
  $exe_start_time = test_show_result('formula_link->load by phrase id and link id "'.$phr->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the link is shown correctly
  $frm = load_formula(TF_ADD_RENAMED, $debug-1);
  $phr_lst = $frm->assign_phr_ulst($debug-1);
  echo $phr_lst->dsp_id().'<br>';
  $result = $phr_lst->does_contain($phr, $debug-1);
  $target = true; 
  $exe_start_time = test_show_result('formula->assign_phr_ulst contains "'.$phr->name.'" for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the link is shown correctly also for the second user
  $frm = New formula;
  $frm->usr = $usr2;
  $frm->name = TF_ADD_RENAMED;
  $frm->load($debug-1);
  $phr_lst = $frm->assign_phr_ulst($debug-1);
  $result = $phr_lst->does_contain($phr, $debug-1);
  $target = true; 
  $exe_start_time = test_show_result('formula->assign_phr_ulst contains "'.$phr->name.'" for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the value update has been triggered

  // if second user removes the new link
  $frm = New formula;
  $frm->usr = $usr2;
  $frm->name = TF_ADD_RENAMED;
  $frm->load($debug-1);
  $phr = New phrase;
  $phr->name = TW_ADD_RENAMED;
  $phr->usr = $usr2;
  $phr->load($debug-1);
  $result = $frm->unlink_phr($phr, $debug-1);
  $target = '';
  $exe_start_time = test_show_result('formula_link->unlink_phr "'.$phr->name.'" from "'.$frm->name.'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // ... check if the removal of the link for the second user has been logged
  $log = New user_log_link;
  $log->table = 'formula_links';
  $log->old_from_id = $frm->id;
  $log->old_to_id = $phr->id;
  $log->usr = $usr2;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system test unlinked Formula Test from '.TW_ADD_RENAMED.'';
  $exe_start_time = test_show_result('formula_link->unlink_phr logged for "'.$phr->name.'" to "'.$frm->name.'" and user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // ... check if the link is really not used any more for the second user
  $frm = New formula;
  $frm->usr = $usr2;
  $frm->name = TF_ADD_RENAMED;
  $frm->load($debug-1);
  $phr_lst = $frm->assign_phr_ulst($debug-1);
  $result = $phr_lst->does_contain($phr, $debug-1);
  $target = false; 
  $exe_start_time = test_show_result('formula->assign_phr_ulst contains "'.$phr->name.'" for user "'.$usr2->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // ... check if the value update for the second user has been triggered

  // ... check if the link is still used for the first user
  $frm = load_formula(TF_ADD_RENAMED, $debug-1);
  $phr_lst = $frm->assign_phr_ulst($debug-1);
  $result = $phr_lst->does_contain($phr, $debug-1);
  $target = true; 
  $exe_start_time = test_show_result('formula->assign_phr_ulst still contains "'.$phr->name.'" for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the values for the first user are still the same

  // if the first user also removes the link, both records should be deleted
  $result = $frm->unlink_phr($phr, $debug-1);
  $target = '11';
  $exe_start_time = test_show_result('formula_link->unlink_phr "'.$phr->name.'" from "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check the correct logging
  $log = New user_log_link;
  $log->table = 'formula_links';
  $log->old_from_id = $frm->id;
  $log->old_to_id = $phr->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job unlinked Formula Test from '.TW_ADD_RENAMED.'';
  $exe_start_time = test_show_result('formula_link->unlink_phr logged of "'.$phr->name.'" from "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the formula is not used any more for both users
  $frm = load_formula(TF_ADD_RENAMED, $debug-1);
  $phr_lst = $frm->assign_phr_ulst($debug-1);
  $result = $phr_lst->does_contain($phr, $debug-1);
  $target = false; 
  $exe_start_time = test_show_result('formula->assign_phr_ulst contains "'.$phr->name.'" for user "'.$usr->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // ... and the values have been updated

  // insert the link again for the first user
  /*
  $frm = load_formula(TF_ADD_RENAMED, $debug-1);
  $phr = New phrase;
  $phr->name = TW_ADD_RENAMED;
  $phr->usr = $usr2;
  $phr->load($debug-1);
  $result = $frm->link_phr($phr, $debug-1);
  $target = '1';
  $exe_start_time = test_show_result('formula_link->link_phr "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  */

  // ... if the second user changes the link

  // ... and the first user removes the link

  // ... the link should still be active for the second user

  // ... but not for the first user

  // ... and the owner should now be the second user

  // the code changes and tests for formula link should be moved the view_component_link

}

function run_formula_link_list_test ($debug = 0) {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the formula link list class (classes/formula_link_list.php)');

  $frm = load_formula(TF_INCREASE, $debug-1);
  $frm_lnk_lst = New formula_link_list;
  $frm_lnk_lst->frm = $frm;
  $frm_lnk_lst->usr = $usr;
  $frm_lnk_lst->load($debug-1);
  $phr_ids = $frm_lnk_lst->phrase_ids(false, $debug-1);
  $phr_lst = New phrase_list;
  $phr_lst->ids = $phr_ids;
  $phr_lst->usr = $usr;
  $phr_lst->load($debug-1);
  $result = $phr_lst->dsp_id();
  $target = TW_YEAR;
  $exe_start_time = test_show_contains(', formula_link_list->load phrase linked to '.$frm->dsp_id().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);

}