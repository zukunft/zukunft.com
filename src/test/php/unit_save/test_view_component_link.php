<?php 

/*

  test_view_component_link.php - TESTing of the VIEW COMPONENT LINK class
  ----------------------------
  

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

function create_test_view_component_links()
{
    test_header('Check if all base view component links are existing');

    test_view_cmp_lnk(view::TN_COMPLETE, view_cmp::TN_TITLE, 1);
    test_view_cmp_lnk(view::TN_COMPLETE, view_cmp::TN_VALUES, 2);
    test_view_cmp_lnk(view::TN_COMPLETE, view_cmp::TN_RESULTS, 3);

    test_view_cmp_lnk(view::TN_TABLE, view_cmp::TN_TITLE, 1);
    test_view_cmp_lnk(view::TN_TABLE, view_cmp::TN_TABLE, 2);


}

function run_view_component_link_test () {

  global $usr;
  global $usr2;

  test_header('Test the view component link class (classes/view_component_link.php)');
  
  // prepare testing by creating the view and components needed for testing
  $dsp = test_view          (view::TN_RENAMED);
  $cmp = test_view_component(view_cmp::TN_ADD);

  // link the test view component to another view
  $order_nbr = $cmp->next_nbr($dsp->id);
  $result = $cmp->link($dsp, $order_nbr);
  $target = '';
  test_dsp('view component_link->link "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // ... check the correct logging
  $log = New user_log_link;
  $log->table = 'view_component_links';
  $log->new_from_id = $dsp->id;
  $log->new_to_id = $cmp->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true);
  $target = 'zukunft.com system test linked System Test View Renamed to System Test View Component';
  test_dsp('view component_link->link_dsp logged for "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result);

  // ... check if the link is shown correctly
  $cmp = load_view_component(view_cmp::TN_ADD);
  $dsp_lst = $cmp->assign_dsp_ids();
  $result = $dsp->is_in_list($dsp_lst);
  $target = true; 
  test_dsp('view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr->name.'"', $target, $result);

  // ... check if the link is shown correctly also for the second user
  $cmp = load_view_component_usr(view_cmp::TN_ADD, $usr2);
  $dsp_lst = $cmp->assign_dsp_ids();
  $result = $dsp->is_in_list($dsp_lst);
  $target = true; 
  test_dsp('view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr2->name.'"', $target, $result);

  // ... check if the value update has been triggered

  // if second user removes the new link
  $cmp = load_view_component_usr(view_cmp::TN_ADD, $usr2);
  $dsp = new view;
  $dsp->name = view::TN_RENAMED;
  $dsp->usr = $usr2;
  $dsp->load();
  $result = $cmp->unlink($dsp);
  $target = '1';
  test_dsp('view component_link->unlink "'.$dsp->name.'" from "'.$cmp->name.'" by user "'.$usr2->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // ... check if the removal of the link for the second user has been logged
  $log = New user_log_link;
  $log->table = 'view_component_links';
  $log->old_from_id = $dsp->id;
  $log->old_to_id = $cmp->id;
  $log->usr = $usr2;
  $result = $log->dsp_last(true);
  $target = 'zukunft.com system test partner unlinked System Test View Renamed from System Test View Component';
  test_dsp('view component_link->unlink_dsp logged for "'.$dsp->name.'" to "'.$cmp->name.'" and user "'.$usr2->name.'"', $target, $result);


  // ... check if the link is really not used any more for the second user
  $cmp = load_view_component_usr(view_cmp::TN_ADD, $usr2);
  $dsp_lst = $cmp->assign_dsp_ids();
  $result = $dsp->is_in_list($dsp_lst);
  $target = false; 
  test_dsp('view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr2->name.'" not any more', $target, $result);


  // ... check if the value update for the second user has been triggered

  // ... check if the link is still used for the first user
  $cmp = load_view_component(view_cmp::TN_ADD);
  $dsp_lst = $cmp->assign_dsp_ids();
  $result = $dsp->is_in_list($dsp_lst);
  $target = true; 
  test_dsp('view component->assign_dsp_ids still contains "'.$dsp->name.'" for user "'.$usr->name.'"', $target, $result);

  // ... check if the values for the first user are still the same

  // if the first user also removes the link, both records should be deleted
  $result = $cmp->unlink($dsp);
  $target = '1';
  test_dsp('view component_link->unlink "'.$dsp->name.'" from "'.$cmp->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // check the correct logging
  $log = New user_log_link;
  $log->table = 'view_component_links';
  $log->old_from_id = $dsp->id;
  $log->old_to_id = $cmp->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true);
  $target = 'zukunft.com system test unlinked System Test View Renamed from System Test View Component';
  test_dsp('view component_link->unlink_dsp logged of "'.$dsp->name.'" from "'.$cmp->name.'"', $target, $result);

  // check if the view component is not used any more for both users
  $cmp = load_view_component(view_cmp::TN_ADD);
  $dsp_lst = $cmp->assign_dsp_ids();
  $result = $dsp->is_in_list($dsp_lst);
  $target = false; 
  test_dsp('view component->assign_dsp_ids contains "'.$dsp->name.'" for user "'.$usr->name.'" not any more', $target, $result);

  // --------------------------------------------------------------------
  // check if changing the view component order can be done for each user
  // --------------------------------------------------------------------

  // load the view and view component objects
  $dsp  = load_view          (view::TN_RENAMED,        );
  $dsp2 = load_view_usr      (view::TN_RENAMED, $usr2);
  $cmp  = load_view_component(view_cmp::TN_ADD,        );
  // create a second view element to be able to test the change of the view order
  $cmp2 = new view_cmp;
  $cmp2->name    = view_cmp::TN_ADD2;
  $cmp2->comment = 'Just added a second view component for testing';
  $cmp2->usr = $usr;
  $result = $cmp2->save();
  if ($cmp2->id > 0) {
    $result = $cmp2->comment;
  }
  $target = 'Just added a second view component for testing';
  test_dsp('view_component->save for adding a second one "'.$cmp2->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // insert the link again for the first user
  $order_nbr = $cmp->next_nbr($dsp->id);
  $result = $cmp->link($dsp, $order_nbr);
  $target = '';
  test_dsp('view component_link->link_dsp again for user 1 "'.$dsp->name.'" to "'.$cmp->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // add a second element for the first user to test the order change
  $order_nbr2 = $cmp2->next_nbr($dsp->id);
  $result = $cmp2->link($dsp, $order_nbr2);
  $target = '';
  test_dsp('view component_link->link_dsp the second for user 1 "'.$dsp->name.'" to "'.$cmp2->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // check if the order of the view components are correct for the first user
  if (isset($dsp)) {
    $pos = 1;
    $dsp->load_components();
    foreach ($dsp->cmp_lst AS $entry) {
      if ($pos == 1) {
        $target = view_cmp::TN_ADD;
      } else {
        $target = view_cmp::TN_ADD2;
      }
      $result = $entry->name;
      test_dsp('view component order for user 1', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
      $pos = $pos + 1;
    }
  }

  // check if the order of the view components are correct for the second user
  if (isset($dsp2)) {
    $pos = 1;
    $dsp2->load_components();
    foreach ($dsp2->cmp_lst AS $entry) {
      if ($pos == 1) {
        $target = view_cmp::TN_ADD;
      } else {
        $target = view_cmp::TN_ADD2;
      }
      $result = $entry->name;
      test_dsp('view component order for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
      $pos = $pos + 1;
    }
  }

  // ... if the second user changes the link e.g. the order
  $cmp_lnk = new view_cmp_link;
  $cmp_lnk->usr = $usr2;
  $cmp_lnk->fob = $dsp2;
  $cmp_lnk->tob = $cmp2;
  $cmp_lnk->load();
  if (isset($cmp_lnk)) {
    $result = $cmp_lnk->move_up(); // TODO force to reload the entry list
    //$result = $cmp_lnk->move_up(); // TODO force to reload the entry list
    $target = true;
    test_dsp('view component order changed for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
  }

  // check if the order of the view components is changed for the second user
  if (isset($dsp2)) {
    $pos = 1;
    $dsp->load_components();
    foreach ($dsp2->cmp_lst AS $entry) {
      if ($pos == 1) {
        $target = view_cmp::TN_ADD2;
      } else {
        $target = view_cmp::TN_ADD;
      }
      $result = $entry->name;
      test_dsp('view component order for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
      $pos = $pos + 1;
    }
  }

  // check if the order of the view components are still the same for the first user
  if (isset($dsp)) {
    $pos = 1;
    $dsp2->load_components();
    foreach ($dsp->cmp_lst AS $entry) {
      if ($pos == 1) {
        $target = view_cmp::TN_ADD;
      } else {
        $target = view_cmp::TN_ADD2;
      }
      $result = $entry->name;
      test_dsp('view component order for user 1', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
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
  $result = $cmp->unlink($dsp);
  $target = '1';
  test_dsp('view component_link->unlink again first component "'.$dsp->name.'" from "'.$cmp->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // unlink the second component
  $result = $cmp2->unlink($dsp);
  $target = '1';
  test_dsp('view component_link->unlink again second component "'.$dsp->name.'" from "'.$cmp2->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
  

  // the code changes and tests for view component link should be moved the view_component_link


}