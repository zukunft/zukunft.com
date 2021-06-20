<?php 

/*

  test_view.php - TESTing of the VIEW class
  -------------
  

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

function create_base_views ($debug = 0) {
  echo "<h2>Check if all base views are exist</h2><br>";
  test_view(TD_COMPLETE, $debug-1);
  test_view(TD_COMPANY_LIST, $debug-1);
  echo "<br><br>";
}

function run_view_test ($debug = 0) {

  global $usr;
  global $usr2;
  global $exe_start_time;

  $back = 0;

  test_header('Test the view class (classes/view.php)');

  // test the creation and changing of a view

  // test loading of one view
  $dsp = new view_dsp;
  $dsp->usr = $usr;
  $dsp->name = 'complete';
  $dsp->load($debug-1);
  $result = $dsp->comment;
  $target = 'Show a word, all related words to edit the word tree and the linked formulas with some results';
  $exe_start_time = test_show_result('view->load the comment of "'.$dsp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test the complete view for one word
  $wrd = New word_dsp;
  $wrd->usr  = $usr;
  $wrd->name = TW_ABB;
  $wrd->load($debug-1);
  $result = $dsp->display($wrd, $back, $debug-1);
  // check if the view contains the word name
  $target = TW_ABB;
  $exe_start_time = test_show_contains(', view->display "'.$dsp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);
  // check if the view contains at least one value
  $target = '45\'548';
  $exe_start_time = test_show_contains(', view->display "'.$dsp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  // check if the view contains at least the main formulas
  $target = 'countryweight';
  $exe_start_time = test_show_contains(', view->display "'.$dsp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $target = 'Price Earning ratio';
  $exe_start_time = test_show_contains(', view->display "'.$dsp->name.'" for "'.$wrd->name.'" contains', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test adding of one view
  $dsp = new view;
  $dsp->name    = TM_ADD;
  $dsp->comment = 'Just added for testing';
  $dsp->usr = $usr;
  $result = $dsp->save($debug-1);
  if ($dsp->id > 0) {
    $result = $dsp->comment;
  }
  $target = 'Just added for testing';
  $exe_start_time = test_show_result('view->save for adding "'.$dsp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check if the view name has been saved
  $dsp = new view;
  $dsp->name = TM_ADD;
  $dsp->usr = $usr;
  $dsp->load($debug-1);
  $result = $dsp->comment;
  $target = 'Just added for testing';
  $exe_start_time = test_show_result('view->load the added "'.$dsp->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the view adding has been logged
  $log = New user_log;
  $log->table = 'views';
  $log->field = 'view_name';
  $log->row_id = $dsp->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job added Test Mask';
  $exe_start_time = test_show_result('view->save adding logged for "'.TM_ADD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if adding the same view again creates a correct error message
  $dsp = new view;
  $dsp->name = TM_ADD;
  $dsp->usr = $usr;
  $result = $dsp->save($debug-1);
  $target = 'A view with the name "'.TM_ADD.'" already exists. Please use another name.'; // is this error message really needed???
  $target = '1';
  $exe_start_time = test_show_result('view->save adding "'.$dsp->name.'" again', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

  // check if the view can be renamed
  $dsp = new view;
  $dsp->name = TM_ADD;
  $dsp->usr = $usr;
  $dsp->load($debug-1);
  $dsp->name = TM_ADD_RENAMED;
  $result = $dsp->save($debug-1);
  $target = '1';
  $exe_start_time = test_show_result('view->save rename "'.TM_ADD.'" to "'.TM_ADD_RENAMED.'".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check if the view renaming was successful
  $dsp_renamed = new view;
  $dsp_renamed->name = TM_ADD_RENAMED;
  $dsp_renamed->usr = $usr;
  $result = $dsp_renamed->load($debug-1);
  if ($result == '') {
    if ($dsp_renamed->id > 0) {
      $result = $dsp_renamed->name;
    }
  }
  $target = TM_ADD_RENAMED;
  $exe_start_time = test_show_result('view->load renamed view "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the view renaming has been logged
  $log = New user_log;
  $log->table = 'views';
  $log->field = 'view_name';
  $log->row_id = $dsp_renamed->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job changed Test Mask to Mask Test';
  $exe_start_time = test_show_result('view->save rename logged for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the view parameters can be added
  $dsp_renamed->comment = 'Just added for testing the user sandbox';
  $dsp_renamed->type_id = cl(DBL_VIEW_TYPE_WORD_DEFAULT);
  $result = $dsp_renamed->save($debug-1);
  $target = '11';
  $exe_start_time = test_show_result('view->save all view fields beside the name for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check if the view parameters have been added
  $dsp_reloaded = new view;
  $dsp_reloaded->name = TM_ADD_RENAMED;
  $dsp_reloaded->usr = $usr;
  $dsp_reloaded->load($debug-1);
  $result = $dsp_reloaded->comment;
  $target = 'Just added for testing the user sandbox';
  $exe_start_time = test_show_result('view->load comment for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $dsp_reloaded->type_id;
  $target = cl(DBL_VIEW_TYPE_WORD_DEFAULT);
  $exe_start_time = test_show_result('view->load type_id for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the view parameter adding have been logged
  $log = New user_log;
  $log->table = 'views';
  $log->field = 'comment';
  $log->row_id = $dsp_reloaded->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job added Just added for testing the user sandbox';
  $exe_start_time = test_show_result('view->load comment for "'.TM_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $log->field = 'view_type_id';
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job added word default';
  $exe_start_time = test_show_result('view->load view_type_id for "'.TM_ADD_RENAMED.'" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if a user specific view is created if another user changes the view
  $dsp_usr2 = new view;
  $dsp_usr2->name = TM_ADD_RENAMED;
  $dsp_usr2->usr = $usr2;
  $dsp_usr2->load($debug-1);
  $dsp_usr2->comment = 'Just changed for testing the user sandbox';
  $dsp_usr2->type_id = cl(DBL_VIEW_TYPE_ENTRY);
  $result = $dsp_usr2->save($debug-1);
  $target = '11';
  $exe_start_time = test_show_result('view->save all view fields for user 2 beside the name for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check if a user specific view changes have been saved
  $dsp_usr2_reloaded = new view;
  $dsp_usr2_reloaded->name = TM_ADD_RENAMED;
  $dsp_usr2_reloaded->usr = $usr2;
  $dsp_usr2_reloaded->load($debug-1);
  $result = $dsp_usr2_reloaded->comment;
  $target = 'Just changed for testing the user sandbox';
  $exe_start_time = test_show_result('view->load comment for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $dsp_usr2_reloaded->type_id;
  $target = cl(DBL_VIEW_TYPE_ENTRY);
  $exe_start_time = test_show_result('view->load type_id for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check the view for the original user remains unchanged
  $dsp_reloaded = new view;
  $dsp_reloaded->name = TM_ADD_RENAMED;
  $dsp_reloaded->usr = $usr;
  $dsp_reloaded->load($debug-1);
  $result = $dsp_reloaded->comment;
  $target = 'Just added for testing the user sandbox';
  $exe_start_time = test_show_result('view->load comment for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $dsp_reloaded->type_id;
  $target = cl(DBL_VIEW_TYPE_WORD_DEFAULT);
  $exe_start_time = test_show_result('view->load type_id for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if undo all specific changes removes the user view
  $dsp_usr2 = new view;
  $dsp_usr2->name = TM_ADD_RENAMED;
  $dsp_usr2->usr = $usr2;
  $dsp_usr2->load($debug-1);
  $dsp_usr2->comment = 'Just added for testing the user sandbox';
  $dsp_usr2->type_id = cl(DBL_VIEW_TYPE_WORD_DEFAULT);
  $result = $dsp_usr2->save($debug-1);
  $target = '11';
  $exe_start_time = test_show_result('view->save undo the user view fields beside the name for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check if a user specific view changes have been saved
  $dsp_usr2_reloaded = new view;
  $dsp_usr2_reloaded->name = TM_ADD_RENAMED;
  $dsp_usr2_reloaded->usr = $usr2;
  $dsp_usr2_reloaded->load($debug-1);
  $result = $dsp_usr2_reloaded->comment;
  $target = 'Just added for testing the user sandbox';
  $exe_start_time = test_show_result('view->load comment for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $result = $dsp_usr2_reloaded->type_id;
  $target = cl(DBL_VIEW_TYPE_WORD_DEFAULT);
  $exe_start_time = test_show_result('view->load type_id for "'.TM_ADD_RENAMED.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // redo the user specific view changes
  // check if the user specific changes can be removed with one click

}