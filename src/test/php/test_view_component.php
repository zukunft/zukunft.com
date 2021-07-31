<?php 

/*

  test_view_component.php - TESTing of the VIEW COMPONENT class
  -----------------------
  

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

function run_view_component_test () {

  global $usr;
  global $usr2;

  test_header('Test the view component class (classes/view_component.php)');
  /*
  // test loading of one view_component
  $cmp = new view_component_dsp;
  $cmp->usr = $usr;
  $cmp->name = 'complete';
  $cmp->load();
  $result = $cmp->comment;
  $target = 'Show a word, all related words to edit the word tree and the linked formulas with some results';
  test_dsp('view_component->load the comment of "'.$cmp->name.'"', $target, $result);

  // test the complete view_component for one word
  $wrd = New word_dsp;
  $wrd->usr  = $usr;
  $wrd->name = TW_ABB;
  $wrd->load();
  $result = $cmp->display($wrd);
  // check if the view_component contains the word name
  $target = TW_ABB;
  test_show_contains(', view_component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result, TIMEOUT_LIMIT_LONG);
  // check if the view_component contains at least one value
  $target = '45548';
  test_show_contains(', view_component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result);
  // check if the view_component contains at least the main formulas
  $target = 'countryweight';
  test_show_contains(', view_component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result);
  $target = 'Price Earning ratio';
  test_show_contains(', view_component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result);
  */
  // test adding of one view_component
  $cmp = new view_component;
  $cmp->name    = TC_ADD;
  $cmp->comment = 'Just added for testing';
  $cmp->usr = $usr;
  $result = $cmp->save();
  if ($cmp->id > 0) {
    $result = $cmp->comment;
  }
  $target = 'Just added for testing';
  test_dsp('view_component->save for adding "'.$cmp->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // check if the view_component name has been saved
  $cmp_added = new view_component;
  $cmp_added->name = TC_ADD;
  $cmp_added->usr = $usr;
  $cmp_added->load();
  $result = $cmp_added->comment;
  $target = 'Just added for testing';
  test_dsp('view_component->load the added "'.$cmp_added->name.'"', $target, $result);

  // check if the view_component adding has been logged
  $log = New user_log;
  $log->table = 'view_components';
  $log->field = 'view_component_name';
  $log->row_id = $cmp->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true);
  $target = 'zukunft.com system batch job added Test Mask Component';
  test_dsp('view_component->save adding logged for "'.TC_ADD.'"', $target, $result);

  // check if adding the same view_component again creates a correct error message
  $cmp = new view_component;
  $cmp->name = TC_ADD;
  $cmp->usr = $usr;
  $result = $cmp->save();
  // in case of other settings
  $target = 'A view component with the name "'.TC_ADD.'" already exists. Please use another name.';
  // for the standard settings
  $target = '1';
  test_dsp('view_component->save adding "'.$cmp->name.'" again', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // check if the view_component can be renamed
  $cmp = new view_component;
  $cmp->name = TC_ADD;
  $cmp->usr = $usr;
  $cmp->load();
  $cmp->name = TC_ADD_RENAMED;
  $result = $cmp->save();
  $target = '1';
  test_dsp('view_component->save rename "'.TC_ADD.'" to "'.TC_ADD_RENAMED.'".', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // check if the view_component renaming was successful
  $cmp_renamed = new view_component;
  $cmp_renamed->name = TC_ADD_RENAMED;
  $cmp_renamed->usr = $usr;
  $result = $cmp_renamed->load();
  if ($result == '') {
    if ($cmp_renamed->id > 0) {
      $result = $cmp_renamed->name;
    }
  }
  $target = TC_ADD_RENAMED;
  test_dsp('view_component->load renamed view_component "'.TC_ADD_RENAMED.'"', $target, $result);

  // check if the view_component renaming has been logged
  $log = New user_log;
  $log->table = 'view_components';
  $log->field = 'view_component_name';
  $log->row_id = $cmp_renamed->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true);
  $target = 'zukunft.com system batch job changed Test Mask Component to Mask Component Test';
  test_dsp('view_component->save rename logged for "'.TC_ADD_RENAMED.'"', $target, $result);

  // check if the view_component parameters can be added
  $cmp_renamed = new view_component;
  $cmp_renamed->name = TC_ADD_RENAMED;
  $cmp_renamed->usr = $usr;
  $cmp_renamed->load();
  $cmp_renamed->comment = 'Just added for testing the user sandbox';
  $cmp_renamed->type_id = clo(DBL_VIEW_COMP_TYPE_WORD_NAME);
  $result = $cmp_renamed->save();
  $target = '11';
  test_dsp('view_component->save all view_component fields beside the name for "'.TC_ADD_RENAMED.'"', $target, $result, TIMEOUT_LIMIT_LONG);

  // check if the view_component parameters have been added
  $cmp_reloaded = new view_component;
  $cmp_reloaded->name = TC_ADD_RENAMED;
  $cmp_reloaded->usr = $usr;
  $cmp_reloaded->load();
  $result = $cmp_reloaded->comment;
  $target = 'Just added for testing the user sandbox';
  test_dsp('view_component->load comment for "'.TC_ADD_RENAMED.'"', $target, $result);
  $result = $cmp_reloaded->type_id;
  $target = clo(DBL_VIEW_COMP_TYPE_WORD_NAME);
  test_dsp('view_component->load type_id for "'.TC_ADD_RENAMED.'"', $target, $result);

  // check if the view_component parameter adding have been logged
  $log = New user_log;
  $log->table = 'view_components';
  $log->field = 'comment';
  $log->row_id = $cmp_reloaded->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true);
  $target = 'zukunft.com system batch job added Just added for testing the user sandbox';
  test_dsp('view_component->load comment for "'.TC_ADD_RENAMED.'" logged', $target, $result);
  $log->field = 'view_component_type_id';
  $result = $log->dsp_last(true);
  $target = 'zukunft.com system batch job added word name';
  test_dsp('view_component->load view_component_type_id for "'.TC_ADD_RENAMED.'" logged', $target, $result);

  // check if a user specific view_component is created if another user changes the view_component
  $cmp_usr2 = new view_component;
  $cmp_usr2->name = TC_ADD_RENAMED;
  $cmp_usr2->usr = $usr2;
  $cmp_usr2->load();
  $cmp_usr2->comment = 'Just changed for testing the user sandbox';
  $cmp_usr2->type_id = clo(DBL_VIEW_COMP_TYPE_FORMULAS);
  $result = $cmp_usr2->save();
  $target = '11';
  test_dsp('view_component->save all view_component fields for user 2 beside the name for "'.TC_ADD_RENAMED.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // check if a user specific view_component changes have been saved
  $cmp_usr2_reloaded = new view_component;
  $cmp_usr2_reloaded->name = TC_ADD_RENAMED;
  $cmp_usr2_reloaded->usr = $usr2;
  $cmp_usr2_reloaded->load();
  $result = $cmp_usr2_reloaded->comment;
  $target = 'Just changed for testing the user sandbox';
  test_dsp('view_component->load comment for "'.TC_ADD_RENAMED.'"', $target, $result);
  $result = $cmp_usr2_reloaded->type_id;
  $target = clo(DBL_VIEW_COMP_TYPE_FORMULAS);
  test_dsp('view_component->load type_id for "'.TC_ADD_RENAMED.'"', $target, $result);

  // check the view_component for the original user remains unchanged
  $cmp_reloaded = new view_component;
  $cmp_reloaded->name = TC_ADD_RENAMED;
  $cmp_reloaded->usr = $usr;
  $cmp_reloaded->load();
  $result = $cmp_reloaded->comment;
  $target = 'Just added for testing the user sandbox';
  test_dsp('view_component->load comment for "'.TC_ADD_RENAMED.'"', $target, $result);
  $result = $cmp_reloaded->type_id;
  $target = clo(DBL_VIEW_COMP_TYPE_WORD_NAME);
  test_dsp('view_component->load type_id for "'.TC_ADD_RENAMED.'"', $target, $result);

  // check if undo all specific changes removes the user view_component
  $cmp_usr2 = new view_component;
  $cmp_usr2->name = TC_ADD_RENAMED;
  $cmp_usr2->usr = $usr2;
  $cmp_usr2->load();
  $cmp_usr2->comment = 'Just added for testing the user sandbox';
  $cmp_usr2->type_id = clo(DBL_VIEW_COMP_TYPE_WORD_NAME);
  $result = $cmp_usr2->save();
  $target = '11';
  test_dsp('view_component->save undo the user view_component fields beside the name for "'.TC_ADD_RENAMED.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

  // check if a user specific view_component changes have been saved
  $cmp_usr2_reloaded = new view_component;
  $cmp_usr2_reloaded->name = TC_ADD_RENAMED;
  $cmp_usr2_reloaded->usr = $usr2;
  $cmp_usr2_reloaded->load();
  $result = $cmp_usr2_reloaded->comment;
  $target = 'Just changed for testing the user sandbox';
  test_dsp('view_component->load comment for "'.TC_ADD_RENAMED.'"', $target, $result);
  //$result = $dsp_usr2_reloaded->type_id;
  //$target = cl(SQL_VIEW_TYPE_WORD_NAME);
  //test_dsp('view_component->load type_id for "'.TC_ADD_RENAMED.'"', $target, $result);

  // redo the user specific view_component changes
  // check if the user specific changes can be removed with one click

}