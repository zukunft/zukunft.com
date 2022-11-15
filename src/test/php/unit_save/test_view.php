<?php

/*

  test_view.php - TESTing of the VIEW class
  -------------
  

    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function create_test_views(testing $t)
{
    $t->header('Check if all base views are existing');

    foreach (view::TEST_VIEWS as $view_name) {
        $t->test_view($view_name);
    }

}

function run_view_test(testing $t)
{

    $back = 0;

    $t->header('Test the view class (classes/view.php)');

    // test the creation and changing of a view

    // test loading of one view
    $dsp = new view_dsp_old($t->usr1);
    $dsp->set_name(view::TN_COMPLETE);
    $result = $dsp->load_obj_vars();
    $t->dsp('view->load of "' . $dsp->name() . '"', true, $result);

    // test the complete view for one word
    $wrd = new word($t->usr1);
    $wrd->set_name(word::TN_CH);
    $wrd->load_obj_vars();
    $result = $dsp->display($wrd, $back);
    // check if the view contains the word name
    $target = word::TN_CH;
    $t->dsp_contains(', view->display "' . $dsp->name() . '" for "' . $wrd->name() . '" contains', $target, $result, TIMEOUT_LIMIT_LONG);
    // check if the view contains at least one value
    $target = 'back='.$wrd->id.'">8.51</a>';
    /* TODO fix the formula value display
    $t->dsp_contains(', view->display "' . $dsp->name . '" for "' . $wrd->name() . '" contains', $target, $result);
    // check if the view contains at least the main formulas
    $target = 'System Test Word Increase';
    $t->dsp_contains(', view->display "' . $dsp->name . '" for "' . $wrd->name() . '" contains', $target, $result);
    */
    /* TODO fix the formula value loading
    $target = 'back='.$wrd->id.'">0.79%</a>';
    $t->dsp_contains(', view->display "' . $dsp->name . '" for "' . $wrd->name() . '" contains', $target, $result);
    */

    // test adding of one view
    $dsp = new view($t->usr1);
    $dsp->set_name(view::TN_ADD);
    $dsp->comment = 'Just added for testing';
    $result = $dsp->save();
    if ($dsp->id > 0) {
        $result = $dsp->comment;
    }
    $target = 'Just added for testing';
    $t->dsp('view->save for adding "' . $dsp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the view name has been saved
    $dsp = new view($t->usr1);
    $dsp->load_by_name(view::TN_ADD, view::class);
    $result = $dsp->comment;
    $target = 'Just added for testing';
    $t->dsp('view->load the added "' . $dsp->name() . '"', $target, $result);

    // check if the view adding has been logged
    $log = new user_log_named;
    $log->table = 'views';
    $log->field = view::FLD_NAME;
    $log->row_id = $dsp->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added System Test View';
    $t->dsp('view->save adding logged for "' . view::TN_ADD . '"', $target, $result);

    // check if adding the same view again creates a correct error message
    $dsp = new view($t->usr1);
    $dsp->set_name(view::TN_ADD);
    $result = $dsp->save();
    $target = 'A view with the name "' . view::TN_ADD . '" already exists. Please use another name.'; // is this error message really needed???
    $target = '';
    $t->dsp('view->save adding "' . $dsp->name() . '" again', $target, $result, TIMEOUT_LIMIT_DB);

    // check if the view can be renamed
    $dsp = new view($t->usr1);
    $dsp->load_by_name(view::TN_ADD, view::class);
    $dsp->set_name(view::TN_RENAMED);
    $result = $dsp->save();
    $target = '';
    $t->dsp('view->save rename "' . view::TN_ADD . '" to "' . view::TN_RENAMED . '".', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the view renaming was successful
    $dsp_renamed = new view($t->usr1);
    $result = $dsp_renamed->load_by_name(view::TN_RENAMED, view::class);
    if ($result) {
        if ($dsp_renamed->id > 0) {
            $result = $dsp_renamed->name();
        }
    }
    $target = 'System Test View Renamed';
    $t->dsp('view->load renamed view "' . view::TN_RENAMED . '"', $target, $result);

    // check if the view renaming has been logged
    $log = new user_log_named;
    $log->table = 'views';
    $log->field = view::FLD_NAME;
    $log->row_id = $dsp_renamed->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test changed System Test View to System Test View Renamed';
    $t->dsp('view->save rename logged for "' . view::TN_RENAMED . '"', $target, $result);

    // check if the view parameters can be added
    $dsp_renamed->comment = 'Just added for testing the user sandbox';
    $dsp_renamed->type_id = cl(db_cl::VIEW_TYPE, view_type::WORD_DEFAULT);
    $result = $dsp_renamed->save();
    $target = '';
    $t->dsp('view->save all view fields beside the name for "' . view::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the view parameters have been added
    $dsp_reloaded = new view($t->usr1);
    $dsp_reloaded->load_by_name(view::TN_RENAMED, view::class);
    $result = $dsp_reloaded->comment;
    $target = 'Just added for testing the user sandbox';
    $t->dsp('view->load comment for "' . view::TN_RENAMED . '"', $target, $result);
    $result = $dsp_reloaded->type_id;
    $target = cl(db_cl::VIEW_TYPE, view_type::WORD_DEFAULT);
    $t->dsp('view->load type_id for "' . view::TN_RENAMED . '"', $target, $result);

    // check if the view parameter adding have been logged
    $log = new user_log_named;
    $log->table = 'views';
    $log->field = 'comment';
    $log->row_id = $dsp_reloaded->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added Just added for testing the user sandbox';
    $t->dsp('view->load comment for "' . view::TN_RENAMED . '" logged', $target, $result);
    $log->field = view::FLD_TYPE;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added word default';
    $t->dsp('view->load view_type_id for "' . view::TN_RENAMED . '" logged', $target, $result);

    // check if a user specific view is created if another user changes the view
    $dsp_usr2 = new view($t->usr2);
    $dsp_usr2->load_by_name(view::TN_RENAMED, view::class);
    $dsp_usr2->comment = 'Just changed for testing the user sandbox';
    $dsp_usr2->type_id = cl(db_cl::VIEW_TYPE, view_type::ENTRY);
    $result = $dsp_usr2->save();
    $target = '';
    $t->dsp('view->save all view fields for user 2 beside the name for "' . view::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific view changes have been saved
    $dsp_usr2_reloaded = new view($t->usr2);
    $dsp_usr2_reloaded->load_by_name(view::TN_RENAMED, view::class);
    $result = $dsp_usr2_reloaded->comment;
    $target = 'Just changed for testing the user sandbox';
    $t->dsp('view->load comment for "' . view::TN_RENAMED . '"', $target, $result);
    $result = $dsp_usr2_reloaded->type_id;
    $target = cl(db_cl::VIEW_TYPE, view_type::ENTRY);
    $t->dsp('view->load type_id for "' . view::TN_RENAMED . '"', $target, $result);

    // check the view for the original user remains unchanged
    $dsp_reloaded = new view($t->usr1);
    $dsp_reloaded->load_by_name(view::TN_RENAMED, view::class);
    $result = $dsp_reloaded->comment;
    $target = 'Just added for testing the user sandbox';
    $t->dsp('view->load comment for "' . view::TN_RENAMED . '"', $target, $result);
    $result = $dsp_reloaded->type_id;
    $target = cl(db_cl::VIEW_TYPE, view_type::WORD_DEFAULT);
    $t->dsp('view->load type_id for "' . view::TN_RENAMED . '"', $target, $result);

    // check if undo all specific changes removes the user view
    $dsp_usr2 = new view($t->usr2);
    $dsp_usr2->load_by_name(view::TN_RENAMED, view::class);
    $dsp_usr2->comment = 'Just added for testing the user sandbox';
    $dsp_usr2->type_id = cl(db_cl::VIEW_TYPE, view_type::WORD_DEFAULT);
    $result = $dsp_usr2->save();
    $target = '';
    $t->dsp('view->save undo the user view fields beside the name for "' . view::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific view changes have been saved
    $dsp_usr2_reloaded = new view($t->usr2);
    $dsp_usr2_reloaded->load_by_name(view::TN_RENAMED, view::class);
    $result = $dsp_usr2_reloaded->comment;
    $target = 'Just added for testing the user sandbox';
    $t->dsp('view->load comment for "' . view::TN_RENAMED . '"', $target, $result);
    $result = $dsp_usr2_reloaded->type_id;
    $target = cl(db_cl::VIEW_TYPE, view_type::WORD_DEFAULT);
    $t->dsp('view->load type_id for "' . view::TN_RENAMED . '"', $target, $result);

    // redo the user specific view changes
    // check if the user specific changes can be removed with one click

}