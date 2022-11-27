<?php

/*

  test_view_component.php - TESTing of the VIEW COMPONENT class
  -----------------------
  

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

function create_test_view_components(testing $t): void
{
    $t->header('Check if all base view components are existing');

    $t->test_view_component(view_cmp::TN_TITLE, view_cmp_type::PHRASE_NAME);
    $t->test_view_component(view_cmp::TN_VALUES, view_cmp_type::VALUES_ALL);
    $t->test_view_component(view_cmp::TN_RESULTS, view_cmp_type::FORMULA_RESULTS);
    $t->test_view_component(view_cmp::TN_TABLE, view_cmp_type::WORD_VALUE);

}

function run_view_component_test(testing $t): void
{

    $t->header('Test the view component class (classes/view_component.php)');
    /*
    // test loading of one view_component
    $cmp = new view_component_dsp;
    $cmp->usr = $t->usr1;
    $cmp->name = 'complete';
    $cmp->load();
    $result = $cmp->comment;
    $target = 'Show a word, all related words to edit the word tree and the linked formulas with some results';
    $t->dsp('view_component->load the comment of "'.$cmp->name.'"', $target, $result);

    // test the complete view_component for one word
    $wrd = New word_dsp;
    $wrd->usr  = $t->usr1;
    $wrd->set_name(TW_ABB);
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
    $cmp = new view_cmp($t->usr1);
    $cmp->set_name(view_cmp::TN_ADD);
    $cmp->description = 'Just added for testing';
    $result = $cmp->save();
    if ($cmp->id > 0) {
        $result = $cmp->description;
    }
    $target = 'Just added for testing';
    $t->dsp('view_component->save for adding "' . $cmp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the view_component name has been saved
    $cmp_added = new view_cmp($t->usr1);
    $cmp_added->load_by_name(view_cmp::TN_ADD, view_cmp::class);
    $result = $cmp_added->description;
    $target = 'Just added for testing';
    $t->dsp('view_component->load the added "' . $cmp_added->name() . '"', $target, $result);

    // check if the view_component adding has been logged
    $log = new user_log_named;
    $log->table = 'view_components';
    $log->field = view_cmp::FLD_NAME;
    $log->row_id = $cmp->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added System Test View Component';
    $t->dsp('view_component->save adding logged for "' . view_cmp::TN_ADD . '"', $target, $result);

    // check if adding the same view_component again creates a correct error message
    $cmp = new view_cmp($t->usr1);
    $cmp->set_name(view_cmp::TN_ADD);
    $result = $cmp->save();
    // in case of other settings
    $target = 'A view component with the name "' . view_cmp::TN_ADD . '" already exists. Please use another name.';
    // for the standard settings
    $target = '';
    $t->dsp('view_component->save adding "' . $cmp->name() . '" again', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the view_component can be renamed
    $cmp = new view_cmp($t->usr1);
    $cmp->load_by_name(view_cmp::TN_ADD, view_cmp::class);
    $cmp->set_name(view_cmp::TN_RENAMED);
    $result = $cmp->save();
    $target = '';
    $t->dsp('view_component->save rename "' . view_cmp::TN_ADD . '" to "' . view_cmp::TN_RENAMED . '".', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the view_component renaming was successful
    $cmp_renamed = new view_cmp($t->usr1);
    $result = $cmp_renamed->load_by_name(view_cmp::TN_RENAMED, view_cmp::class);
    if ($result == '') {
        if ($cmp_renamed->id > 0) {
            $result = $cmp_renamed->name();
        }
    }
    $target = 13;
    $t->dsp('view_component->load renamed view_component "' . view_cmp::TN_RENAMED . '"', $target, $result);

    // check if the view_component renaming has been logged
    $log = new user_log_named;
    $log->table = 'view_components';
    $log->field = view_cmp::FLD_NAME;
    $log->row_id = $cmp_renamed->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test changed System Test View Component to System Test View Component Renamed';
    $t->dsp('view_component->save rename logged for "' . view_cmp::TN_RENAMED . '"', $target, $result);

    // check if the view_component parameters can be added
    $cmp_renamed = new view_cmp($t->usr1);
    $cmp_renamed->load_by_name(view_cmp::TN_RENAMED, view_cmp::class);
    $cmp_renamed->description = 'Just added for testing the user sandbox';
    $cmp_renamed->type_id = cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::PHRASE_NAME);
    $result = $cmp_renamed->save();
    $target = '';
    $t->dsp('view_component->save all view_component fields beside the name for "' . view_cmp::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_LONG);

    // check if the view_component parameters have been added
    $cmp_reloaded = new view_cmp($t->usr1);
    $cmp_reloaded->load_by_name(view_cmp::TN_RENAMED, view_cmp::class);
    $result = $cmp_reloaded->description;
    $target = 'Just added for testing the user sandbox';
    $t->dsp('view_component->load comment for "' . view_cmp::TN_RENAMED . '"', $target, $result);
    $result = $cmp_reloaded->type_id;
    $target = cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::PHRASE_NAME);
    $t->dsp('view_component->load type_id for "' . view_cmp::TN_RENAMED . '"', $target, $result);

    // check if the view_component parameter adding have been logged
    $log = new user_log_named;
    $log->table = 'view_components';
    $log->field = 'comment';
    $log->row_id = $cmp_reloaded->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    //$target = 'zukunft.com system test added Just added for testing the user sandbox';
    $target = 'zukunft.com system test changed Just added for testing to Just added for testing the user sandbox';
    $t->dsp('view_component->load comment for "' . view_cmp::TN_RENAMED . '" logged', $target, $result);
    $log->field = 'view_component_type_id';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added word name';
    $t->dsp('view_component->load view_component_type_id for "' . view_cmp::TN_RENAMED . '" logged', $target, $result);

    // check if a user specific view_component is created if another user changes the view_component
    $cmp_usr2 = new view_cmp($t->usr2);
    $cmp_usr2->load_by_name(view_cmp::TN_RENAMED, view_cmp::class);
    $cmp_usr2->description = 'Just changed for testing the user sandbox';
    $cmp_usr2->type_id = cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::FORMULAS);
    $result = $cmp_usr2->save();
    $target = '';
    $t->dsp('view_component->save all view_component fields for user 2 beside the name for "' . view_cmp::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific view_component changes have been saved
    $cmp_usr2_reloaded = new view_cmp($t->usr2);
    $cmp_usr2_reloaded->load_by_name(view_cmp::TN_RENAMED, view_cmp::class);
    $result = $cmp_usr2_reloaded->description;
    $target = 'Just changed for testing the user sandbox';
    $t->dsp('view_component->load comment for "' . view_cmp::TN_RENAMED . '"', $target, $result);
    $result = $cmp_usr2_reloaded->type_id;
    $target = cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::FORMULAS);
    $t->dsp('view_component->load type_id for "' . view_cmp::TN_RENAMED . '"', $target, $result);

    // check the view_component for the original user remains unchanged
    $cmp_reloaded = new view_cmp($t->usr1);
    $cmp_reloaded->load_by_name(view_cmp::TN_RENAMED, view_cmp::class);
    $result = $cmp_reloaded->description;
    $target = 'Just added for testing the user sandbox';
    $t->dsp('view_component->load comment for "' . view_cmp::TN_RENAMED . '"', $target, $result);
    $result = $cmp_reloaded->type_id;
    $target = cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::PHRASE_NAME);
    $t->dsp('view_component->load type_id for "' . view_cmp::TN_RENAMED . '"', $target, $result);

    // check if undo all specific changes removes the user view_component
    $cmp_usr2 = new view_cmp($t->usr2);
    $cmp_usr2->load_by_name(view_cmp::TN_RENAMED, view_cmp::class);
    $cmp_usr2->description = 'Just added for testing the user sandbox';
    $cmp_usr2->type_id = cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::PHRASE_NAME);
    $result = $cmp_usr2->save();
    $target = '';
    $t->dsp('view_component->save undo the user view_component fields beside the name for "' . view_cmp::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific view_component changes have been saved
    $cmp_usr2_reloaded = new view_cmp($t->usr2);
    $cmp_usr2_reloaded->load_by_name(view_cmp::TN_RENAMED, view_cmp::class);
    $result = $cmp_usr2_reloaded->description;
    $target = 'Just added for testing the user sandbox';
    $t->dsp('view_component->load comment for "' . view_cmp::TN_RENAMED . '"', $target, $result);
    //$result = $dsp_usr2_reloaded->type_id;
    //$target = cl(SQL_VIEW_TYPE_WORD_NAME);
    //$t->dsp('view_component->load type_id for "'.view_component::TEST_NAME_RENAMED.'"', $target, $result);

    // redo the user specific view_component changes
    // check if the user specific changes can be removed with one click

}