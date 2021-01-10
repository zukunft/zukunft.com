<?php 

/*

  test_view_component_link.php - TESTing of the VIEW COMPONENT LINK class
  ----------------------------
  

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

function run_view_component_link_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  test_header('Test the view component link class (classes/view_component_link.php)');
  
  // prepare testing by creating the view and components needed for testing
  $dsp = get_view          (TM_ADD_RENAMED, $debug-1);
  $cmp = get_view_component(TC_ADD_RENAMED, $debug-1);

  // link the test view component to another view
  $order_nbr = $cmp->next_nbr($dsp->id, $debug-1);
  $result = $cmp->link($dsp, $order_nbr, $debug-1);
  $target = '111';
  $exe_start_time = test_show_result(', view component_link->link "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // ... check the correct logging
  $log = New user_log_link;
  $log->table = 'view_component_links';
  $log->new_from_id = $dsp->id;
  $log->new_to_id = $cmp->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job linked Mask Test to Mask Component Test';
  $exe_start_time = test_show_result(', view component_link->link_dsp logged for "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the link is shown correctly
  $cmp = load_view_component(TC_ADD_RENAMED, $debug-1);
  $dsp_lst = $cmp->assign_dsp_ids($debug-1);
  $result = $dsp->is_in_list($dsp_lst, $debug-1);
  $target = true; 
  $exe_start_time = test_show_result(', view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the link is shown correctly also for the second user
  $cmp = load_view_component_usr(TC_ADD_RENAMED, $usr2, $debug-1);
  $dsp_lst = $cmp->assign_dsp_ids($debug-1);
  $result = $dsp->is_in_list($dsp_lst, $debug-1);
  $target = true; 
  $exe_start_time = test_show_result(', view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the value update has been triggered

  // if second user removes the new link
  $cmp = load_view_component_usr(TC_ADD_RENAMED, $usr2, $debug-1);
  $dsp = new view;
  $dsp->name = TM_ADD_RENAMED;
  $dsp->usr = $usr2;
  $dsp->load($debug-1);
  $result = $cmp->unlink($dsp, $debug-1);
  $target = '';
  $exe_start_time = test_show_result(', view component_link->unlink "'.$dsp->name.'" from "'.$cmp->name.'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // ... check if the removal of the link for the second user has been logged
  $log = New user_log_link;
  $log->table = 'view_component_links';
  $log->old_from_id = $dsp->id;
  $log->old_to_id = $cmp->id;
  $log->usr = $usr2;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system test unlinked Mask Test from Mask Component Test';
  $exe_start_time = test_show_result(', view component_link->unlink_dsp logged for "'.$dsp->name.'" to "'.$cmp->name.'" and user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // ... check if the link is really not used any more for the second user
  $cmp = load_view_component_usr(TC_ADD_RENAMED, $usr2, $debug-1);
  $dsp_lst = $cmp->assign_dsp_ids($debug-1);
  $result = $dsp->is_in_list($dsp_lst, $debug-1);
  $target = false; 
  $exe_start_time = test_show_result(', view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr2->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // ... check if the value update for the second user has been triggered

  // ... check if the link is still used for the first user
  $cmp = load_view_component(TC_ADD_RENAMED, $debug-1);
  $dsp_lst = $cmp->assign_dsp_ids($debug-1);
  $result = $dsp->is_in_list($dsp_lst, $debug-1);
  $target = true; 
  $exe_start_time = test_show_result(', view component->assign_dsp_ids still contains "'.$dsp->name.'" for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the values for the first user are still the same

  // if the first user also removes the link, both records should be deleted
  $result = $cmp->unlink($dsp, $debug-1);
  $target = '11';
  $exe_start_time = test_show_result(', view component_link->unlink "'.$dsp->name.'" from "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check the correct logging
  $log = New user_log_link;
  $log->table = 'view_component_links';
  $log->old_from_id = $dsp->id;
  $log->old_to_id = $cmp->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job unlinked Mask Test from Mask Component Test';
  $exe_start_time = test_show_result(', view component_link->unlink_dsp logged of "'.$dsp->name.'" from "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the view component is not used any more for both users
  $cmp = load_view_component(TC_ADD_RENAMED, $debug-1);
  $dsp_lst = $cmp->assign_dsp_ids($debug-1);
  $result = $dsp->is_in_list($dsp_lst, $debug-1);
  $target = false; 
  $exe_start_time = test_show_result(', view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // --------------------------------------------------------------------
  // check if changing the view component order can be done for each user
  // --------------------------------------------------------------------

  // load the view and view component objects
  $dsp  = load_view          (TM_ADD_RENAMED,        $debug-1);
  $dsp2 = load_view_usr      (TM_ADD_RENAMED, $usr2, $debug-1);
  $cmp  = load_view_component(TC_ADD_RENAMED,        $debug-1);
  // create a second view element to be able to test the change of the view order
  $cmp2 = new view_component;
  $cmp2->name    = TC_ADD2;
  $cmp2->comment = 'Just added a second view component for testing';
  $cmp2->usr = $usr;
  $result = $cmp2->save($debug-1);
  if ($cmp2->id > 0) {
    $result = $cmp2->comment;
  }
  $target = 'Just added a second view component for testing';
  $exe_start_time = test_show_result(', view_component->save for adding a second one "'.$cmp2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // insert the link again for the first user
  $order_nbr = $cmp->next_nbr($dsp->id, $debug-1);
  $result = $cmp->link($dsp, $order_nbr, $debug-1);
  $target = '111';
  $exe_start_time = test_show_result(', view component_link->link_dsp again for user 1 "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // add a second element for the first user to test the order change
  $order_nbr2 = $cmp2->next_nbr($dsp->id, $debug-1);
  $result = $cmp2->link($dsp, $order_nbr2, $debug-1);
  $target = '111';
  $exe_start_time = test_show_result(', view component_link->link_dsp the second for user 1 "'.$dsp->name.'" to "'.$cmp2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 

  // check if the order of the view components are correct for the first user
  if (isset($dsp)) {
    $pos = 1;
    $dsp->load_components($debug-1);
    foreach ($dsp->cmp_lst AS $entry) {
      if ($pos == 1) {
        $target = TC_ADD_RENAMED;
      } else {
        $target = TC_ADD2;
      }
      $result = $entry->name;
      $exe_start_time = test_show_result(', view component order for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
      $pos = $pos + 1;
    }
  }

  // check if the order of the view components are correct for the second user
  if (isset($dsp2)) {
    $pos = 1;
    $dsp2->load_components($debug-1);
    foreach ($dsp2->cmp_lst AS $entry) {
      if ($pos == 1) {
        $target = TC_ADD_RENAMED;
      } else {
        $target = TC_ADD2;
      }
      $result = $entry->name;
      $exe_start_time = test_show_result(', view component order for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
      $pos = $pos + 1;
    }
  }

  // ... if the second user changes the link e.g. the order
  $cmp_lnk = new view_component_link;
  $cmp_lnk->usr = $usr2;
  $cmp_lnk->fob = $dsp2;
  $cmp_lnk->tob = $cmp2;
  $cmp_lnk->load($debug-1);
  if (isset($cmp_lnk)) {
    $result = $cmp_lnk->move_up($debug-1); // TODO force to reload the entry list
    //$result = $cmp_lnk->move_up($debug-1); // TODO force to reload the entry list
    $target = '1';
    $exe_start_time = test_show_result(', view component order changed for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  }

  // check if the order of the view components is changed for the second user
  if (isset($dsp2)) {
    $pos = 1;
    $dsp->load_components($debug-1);
    foreach ($dsp2->cmp_lst AS $entry) {
      if ($pos == 1) {
        $target = TC_ADD2;
      } else {
        $target = TC_ADD_RENAMED;
      }
      $result = $entry->name;
      $exe_start_time = test_show_result(', view component order for user 2', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
      $pos = $pos + 1;
    }
  }

  // check if the order of the view components are still the same for the first user
  if (isset($dsp)) {
    $pos = 1;
    $dsp2->load_components($debug-1);
    foreach ($dsp->cmp_lst AS $entry) {
      if ($pos == 1) {
        $target = TC_ADD_RENAMED;
      } else {
        $target = TC_ADD2;
      }
      $result = $entry->name;
      $exe_start_time = test_show_result(', view component order for user 1', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
      $pos = $pos + 1;
    }
  }

  /*
  */

  // ... the order for the first user should still be the same

  // ... and the first user removes the link

  // ... the link should still be active for the second user

  // ... but not for the first user

  // ... and the owner should now be the second user

  
  // cleanup the component link test
  // unlink the first component
  $result = $cmp->unlink($dsp, $debug-1);
  $target = '11';
  $exe_start_time = test_show_result(', view component_link->unlink again first component "'.$dsp->name.'" from "'.$cmp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // unlink the second component
  $result = $cmp2->unlink($dsp, $debug-1);
  $target = '111';
  $exe_start_time = test_show_result(', view component_link->unlink again second component "'.$dsp->name.'" from "'.$cmp2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
  

  // the code changes and tests for view component link should be moved the view_component_link


}