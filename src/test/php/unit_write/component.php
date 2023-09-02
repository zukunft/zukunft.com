<?php

/*

    test/php/unit_write/component.php - write test COMPONENTS to the database and check the results
    ---------------------------------
  

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

namespace test\write;

use api\component\component_api;
use cfg\change_log_field;
use cfg\change_log_named;
use cfg\change_log_table;
use cfg\component\component;
use cfg\component\component_type;
use cfg\sandbox_named;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_DB_MULTI;
use const test\TIMEOUT_LIMIT_LONG;

class component_test
{

    function run(test_cleanup $t): void
    {
        global $component_types;

        $t->header('Test the view component class (classes/component.php)');
        /*
        // test loading of one component
        $cmp = new component_dsp;
        $cmp->usr = $t->usr1;
        $cmp->name = 'complete';
        $cmp->load();
        $result = $cmp->comment;
        $target = 'Show a word, all related words to edit the word tree and the linked formulas with some results';
        $t->display('component->load the comment of "'.$cmp->name.'"', $target, $result);

        // test the complete component for one word
        $wrd = New word_dsp;
        $wrd->usr  = $t->usr1;
        $wrd->set_name(TW_ABB);
        $wrd->load();
        $result = $cmp->display($wrd);
        // check if the component contains the word name
        $target = TW_ABB;
        test_show_contains(', component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result, TIMEOUT_LIMIT_LONG);
        // check if the component contains at least one value
        $target = '45548';
        test_show_contains(', component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result);
        // check if the component contains at least the main formulas
        $target = 'countryweight';
        test_show_contains(', component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result);
        $target = 'Price Earning ratio';
        test_show_contains(', component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result);
        */
        // test adding of one component
        $cmp = new component($t->usr1);
        $cmp->set_name(component_api::TN_ADD);
        $cmp->description = 'Just added for testing';
        $result = $cmp->save();
        if ($cmp->id() > 0) {
            $result = $cmp->description;
        }
        $target = 'Just added for testing';
        $t->display('component->save for adding "' . $cmp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if the component name has been saved
        $cmp_added = new component($t->usr1);
        $cmp_added->load_by_name(component_api::TN_ADD, component::class);
        $result = $cmp_added->description;
        $target = 'Just added for testing';
        $t->display('component->load the added "' . $cmp_added->name() . '"', $target, $result);

        // check if the component adding has been logged
        $log = new change_log_named($t->usr1);
        $log->set_table(change_log_table::VIEW_COMPONENT);
        $log->set_field(component::FLD_NAME);
        $log->row_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test added System Test View Component';
        $t->display('component->save adding logged for "' . component_api::TN_ADD . '"', $target, $result);

        // check if adding the same component again creates a correct error message
        $cmp = new component($t->usr1);
        $cmp->set_name(component_api::TN_ADD);
        $result = $cmp->save();
        // in case of other settings
        $target = 'A view component with the name "' . component_api::TN_ADD . '" already exists. Please use another name.';
        // for the standard settings
        $target = '';
        $t->display('component->save adding "' . $cmp->name() . '" again', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if the component can be renamed
        $cmp = new component($t->usr1);
        $cmp->load_by_name(component_api::TN_ADD, component::class);
        $cmp->set_name(component_api::TN_RENAMED);
        $result = $cmp->save();
        $target = '';
        $t->display('component->save rename "' . component_api::TN_ADD . '" to "' . component_api::TN_RENAMED . '".', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if the component renaming was successful
        $cmp_renamed = new component($t->usr1);
        $cmp_renamed->load_by_name(component_api::TN_RENAMED, component::class);
        if ($cmp_renamed->id() > 0) {
            $cmp_renamed_reloaded = new component($t->usr1);
            $cmp_renamed_reloaded->load_by_id($cmp_renamed->id(), component::class);
            $result = $cmp_renamed_reloaded->name();
        }
        $target = component_api::TN_RENAMED;
        $t->display('component->load renamed component "' . component_api::TN_RENAMED . '"', $target, $result);

        // check if the component renaming has been logged
        $log = new change_log_named($t->usr1);
        $log->set_table(change_log_table::VIEW_COMPONENT);
        $log->set_field(component::FLD_NAME);
        $log->row_id = $cmp_renamed->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test changed System Test View Component to System Test View Component Renamed';
        $t->display('component->save rename logged for "' . component_api::TN_RENAMED . '"', $target, $result);

        // check if the component parameters can be added
        $cmp_renamed = new component($t->usr1);
        $cmp_renamed->load_by_name(component_api::TN_RENAMED, component::class);
        $cmp_renamed->description = 'Just added for testing the user sandbox';
        $cmp_renamed->type_id = $component_types->id(component_type::PHRASE_NAME);
        $result = $cmp_renamed->save();
        $target = '';
        $t->display('component->save all component fields beside the name for "' . component_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_LONG);

        // check if the component parameters have been added
        $cmp_reloaded = new component($t->usr1);
        $cmp_reloaded->load_by_name(component_api::TN_RENAMED, component::class);
        $result = $cmp_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '"', $target, $result);
        $result = $cmp_reloaded->type_id;
        $target = $component_types->id(component_type::PHRASE_NAME);
        $t->display('component->load type_id for "' . component_api::TN_RENAMED . '"', $target, $result);

        // check if the component parameter adding have been logged
        $log = new change_log_named($t->usr1);
        $log->set_table(change_log_table::VIEW_COMPONENT);
        $log->set_field(sandbox_named::FLD_DESCRIPTION);
        $log->row_id = $cmp_reloaded->id();
        $result = $log->dsp_last(true);
        //$target = 'zukunft.com system test added Just added for testing the user sandbox';
        $target = 'zukunft.com system test changed Just added for testing to Just added for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '" logged', $target, $result);
        $log->set_field(change_log_field::FLD_COMPONENT_TYPE);
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test added word name';
        $t->display('component->load component_type_id for "' . component_api::TN_RENAMED . '" logged', $target, $result);

        // check if a user specific component is created if another user changes the component
        $cmp_usr2 = new component($t->usr2);
        $cmp_usr2->load_by_name(component_api::TN_RENAMED, component::class);
        $cmp_usr2->description = 'Just changed for testing the user sandbox';
        $cmp_usr2->type_id = $component_types->id(component_type::FORMULAS);
        $result = $cmp_usr2->save();
        $target = '';
        $t->display('component->save all component fields for user 2 beside the name for "' . component_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific component changes have been saved
        $cmp_usr2_reloaded = new component($t->usr2);
        $cmp_usr2_reloaded->load_by_name(component_api::TN_RENAMED, component::class);
        $result = $cmp_usr2_reloaded->description;
        $target = 'Just changed for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '"', $target, $result);
        $result = $cmp_usr2_reloaded->type_id;
        $target = $component_types->id(component_type::FORMULAS);
        $t->display('component->load type_id for "' . component_api::TN_RENAMED . '"', $target, $result);

        // check the component for the original user remains unchanged
        $cmp_reloaded = new component($t->usr1);
        $cmp_reloaded->load_by_name(component_api::TN_RENAMED, component::class);
        $result = $cmp_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '"', $target, $result);
        $result = $cmp_reloaded->type_id;
        $target = $component_types->id(component_type::PHRASE_NAME);
        $t->display('component->load type_id for "' . component_api::TN_RENAMED . '"', $target, $result);

        // check if undo all specific changes removes the user component
        $cmp_usr2 = new component($t->usr2);
        $cmp_usr2->load_by_name(component_api::TN_RENAMED, component::class);
        $cmp_usr2->description = 'Just added for testing the user sandbox';
        $cmp_usr2->type_id = $component_types->id(component_type::PHRASE_NAME);
        $result = $cmp_usr2->save();
        $target = '';
        $t->display('component->save undo the user component fields beside the name for "' . component_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific component changes have been saved
        $cmp_usr2_reloaded = new component($t->usr2);
        $cmp_usr2_reloaded->load_by_name(component_api::TN_RENAMED, component::class);
        $result = $cmp_usr2_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '"', $target, $result);
        //$result = $dsp_usr2_reloaded->type_id;
        //$target = cl(SQL_VIEW_TYPE_WORD_NAME);
        //$t->display('component->load type_id for "'.component::TEST_NAME_RENAMED.'"', $target, $result);

        // redo the user specific component changes
        // check if the user specific changes can be removed with one click

    }

    function create_test_components(test_cleanup $t): void
    {
        $t->header('Check if all base view components are existing');

        $t->test_component(component_api::TN_TITLE, component_type::PHRASE_NAME);
        $t->test_component(component_api::TN_VALUES, component_type::VALUES_ALL);
        $t->test_component(component_api::TN_RESULTS, component_type::FORMULA_RESULTS);
        $t->test_component(component_api::TN_EXCLUDED, component_type::PHRASE_NAME);
        $t->test_component(component_api::TN_TABLE, component_type::NUMERIC_VALUE);

        // modify the special test cases
        global $usr;
        $cmp = new component($usr);
        $cmp->load_by_name(component_api::TN_EXCLUDED);
        $cmp->set_excluded(true);
        $cmp->save();

    }

}