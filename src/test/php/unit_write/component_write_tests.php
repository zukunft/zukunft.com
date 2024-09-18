<?php

/*

    test/php/unit_write/component_tests.php - write test COMPONENTS to the database and check the results
    ---------------------------------------
  

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

namespace unit_write;

include_once SHARED_TYPES_PATH . 'component_type.php';

use api\formula\formula as formula_api;
use api\view\view as view_api;
use cfg\user;
use shared\types\component_type as comp_type_shared;
use api\component\component as component_api;
use cfg\component\component;
use cfg\log\change_field_list;
use cfg\log\change;
use cfg\log\change_table_list;
use cfg\component\component_type;
use cfg\sandbox_named;
use test\test_cleanup;

class component_write_tests
{

    function run(test_cleanup $t): void
    {
        global $component_types;

        $t->header('component db write tests');

        $t->subheader('component prepared write');
        $test_name = 'add component ' . component_api::TN_ADD_VIA_SQL . ' via sql insert';
        $t->assert_write_via_func_or_sql($test_name, $t->component_add_by_sql(), false);
        $test_name = 'add component ' . component_api::TN_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t->component_add_by_func(), true);

        $t->subheader('component write sandbox tests for ' . component_api::TN_ADD);
        $t->assert_write_named($t->component_filled_add(), component_api::TN_ADD);

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
        $wrd->set_name(word_api::TN_ABB);
        $wrd->load();
        $result = $cmp->display($wrd);
        // check if the component contains the word name
        $target = word_api::TN_ABB;
        test_show_contains(', component->display "'.$cmp->name.'" for "'.$wrd->name.'" contains', $target, $result, $t::TIMEOUT_LIMIT_LONG);
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
        $result = $cmp->save()->get_last_message();
        if ($cmp->id() > 0) {
            $result = $cmp->description;
        }
        $target = 'Just added for testing';
        $t->display('component->save for adding "' . $cmp->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the component name has been saved
        $cmp_added = new component($t->usr1);
        $cmp_added->load_by_name(component_api::TN_ADD);
        $result = $cmp_added->description;
        $target = 'Just added for testing';
        $t->display('component->load the added "' . $cmp_added->name() . '"', $target, $result);

        // check if the component adding has been logged
        $log = new change($t->usr1);
        $log->set_class(component::class);
        $log->set_field(component::FLD_NAME);
        $log->row_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = user::SYSTEM_TEST_NAME . ' added "System Test View Component"';
        $t->display('component->save adding logged for "' . component_api::TN_ADD . '"', $target, $result);

        // check if adding the same component again creates a correct error message
        $cmp = new component($t->usr1);
        $cmp->set_name(component_api::TN_ADD);
        $result = $cmp->save()->get_last_message();
        // in case of other settings
        $target = 'A view component with the name "' . component_api::TN_ADD . '" already exists. Please use another name.';
        // for the standard settings
        $target = '';
        $t->display('component->save adding "' . $cmp->name() . '" again', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the component can be renamed
        $cmp = new component($t->usr1);
        $cmp->load_by_name(component_api::TN_ADD);
        $cmp->set_name(component_api::TN_RENAMED);
        $result = $cmp->save()->get_last_message();
        $target = '';
        $t->display('component->save rename "' . component_api::TN_ADD . '" to "' . component_api::TN_RENAMED . '".', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the component renaming was successful
        $cmp_renamed = new component($t->usr1);
        $cmp_renamed->load_by_name(component_api::TN_RENAMED);
        if ($cmp_renamed->id() > 0) {
            $cmp_renamed_reloaded = new component($t->usr1);
            $cmp_renamed_reloaded->load_by_id($cmp_renamed->id());
            $result = $cmp_renamed_reloaded->name();
        }
        $target = component_api::TN_RENAMED;
        $t->display('component->load renamed component "' . component_api::TN_RENAMED . '"', $target, $result);

        // check if the component renaming has been logged
        $log = new change($t->usr1);
        $log->set_table(change_table_list::VIEW_COMPONENT);
        $log->set_field(component::FLD_NAME);
        $log->row_id = $cmp_renamed->id();
        $result = $log->dsp_last(true);
        $target = user::SYSTEM_TEST_NAME . ' changed "System Test View Component" to "System Test View Component Renamed"';
        $t->display('component->save rename logged for "' . component_api::TN_RENAMED . '"', $target, $result);

        // check if the component parameters can be added
        $cmp_renamed = new component($t->usr1);
        $cmp_renamed->load_by_name(component_api::TN_RENAMED);
        $cmp_renamed->description = 'Just added for testing the user sandbox';
        $cmp_renamed->type_id = $component_types->id(comp_type_shared::PHRASE_NAME);
        $result = $cmp_renamed->save()->get_last_message();
        $target = '';
        $t->display('component->save all component fields beside the name for "' . component_api::TN_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_LONG);

        // check if the component parameters have been added
        $cmp_reloaded = new component($t->usr1);
        $cmp_reloaded->load_by_name(component_api::TN_RENAMED);
        $result = $cmp_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '"', $target, $result);
        $result = $cmp_reloaded->type_id;
        $target = $component_types->id(comp_type_shared::PHRASE_NAME);
        $t->display('component->load type_id for "' . component_api::TN_RENAMED . '"', $target, $result);

        // check if the component parameter adding have been logged
        $log = new change($t->usr1);
        $log->set_table(change_table_list::VIEW_COMPONENT);
        $log->set_field(sandbox_named::FLD_DESCRIPTION);
        $log->row_id = $cmp_reloaded->id();
        $result = $log->dsp_last(true);
        // TODO fix it
        $target = user::SYSTEM_TEST_NAME . ' added "Just added for testing the user sandbox"';
        if ($result != $target) {
            $target = user::SYSTEM_TEST_PARTNER_NAME . ' changed "Just added for testing the user sandbox" to "Just changed for testing the user sandbox"';
        }
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '" logged', $target, $result);
        $log->set_field(change_field_list::FLD_COMPONENT_TYPE);
        $result = $log->dsp_last(true);
        // TODO fix it
        $target = user::SYSTEM_TEST_NAME . ' added "word name"';
        if ($result != $target) {
            $target = user::SYSTEM_TEST_PARTNER_NAME . ' changed "word name" to "formulas"';
        }
        $t->display('component->load component_type_id for "' . component_api::TN_RENAMED . '" logged', $target, $result);

        // check if a user specific component is created if another user changes the component
        $cmp_usr2 = new component($t->usr2);
        $cmp_usr2->load_by_name(component_api::TN_RENAMED);
        $cmp_usr2->description = 'Just changed for testing the user sandbox';
        $cmp_usr2->type_id = $component_types->id(comp_type_shared::FORMULAS);
        $result = $cmp_usr2->save()->get_last_message();
        $target = '';
        $t->display('component->save all component fields for user 2 beside the name for "' . component_api::TN_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific component changes have been saved
        $cmp_usr2_reloaded = new component($t->usr2);
        $cmp_usr2_reloaded->load_by_name(component_api::TN_RENAMED);
        $result = $cmp_usr2_reloaded->description;
        $target = 'Just changed for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '"', $target, $result);
        $result = $cmp_usr2_reloaded->type_id;
        $target = $component_types->id(comp_type_shared::FORMULAS);
        $t->display('component->load type_id for "' . component_api::TN_RENAMED . '"', $target, $result);

        // check the component for the original user remains unchanged
        $cmp_reloaded = new component($t->usr1);
        $cmp_reloaded->load_by_name(component_api::TN_RENAMED);
        $result = $cmp_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '"', $target, $result);
        $result = $cmp_reloaded->type_id;
        $target = $component_types->id(comp_type_shared::PHRASE_NAME);
        $t->display('component->load type_id for "' . component_api::TN_RENAMED . '"', $target, $result);

        // check if undo all specific changes removes the user component
        $cmp_usr2 = new component($t->usr2);
        $cmp_usr2->load_by_name(component_api::TN_RENAMED);
        $cmp_usr2->description = 'Just added for testing the user sandbox';
        $cmp_usr2->type_id = $component_types->id(comp_type_shared::PHRASE_NAME);
        $result = $cmp_usr2->save()->get_last_message();
        $target = '';
        $t->display('component->save undo the user component fields beside the name for "' . component_api::TN_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific component changes have been saved
        $cmp_usr2_reloaded = new component($t->usr2);
        $cmp_usr2_reloaded->load_by_name(component_api::TN_RENAMED);
        $result = $cmp_usr2_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . component_api::TN_RENAMED . '"', $target, $result);
        //$result = $dsp_usr2_reloaded->type_id;
        //$target = cl(SQL_VIEW_TYPE_WORD_NAME);
        //$t->display('component->load type_id for "'.component::TEST_NAME_RENAMED.'"', $target, $result);

        // redo the user specific component changes
        // check if the user specific changes can be removed with one click

        // cleanup - fallback delete
        $cmp = new component($t->usr1);
        foreach (component_api::TEST_COMPONENTS as $cmp_name) {
            $t->write_named_cleanup($cmp, $cmp_name);
        }

    }

    function create_test_components(test_cleanup $t): void
    {
        $t->header('Check if all base view components are existing');

        $t->test_component(component_api::TN_TITLE, comp_type_shared::PHRASE_NAME);
        $t->test_component(component_api::TN_VALUES, comp_type_shared::VALUES_ALL);
        $t->test_component(component_api::TN_RESULTS, comp_type_shared::FORMULA_RESULTS);
        $t->test_component(component_api::TN_EXCLUDED, comp_type_shared::PHRASE_NAME);
        $t->test_component(component_api::TN_TABLE, comp_type_shared::NUMERIC_VALUE);

        // modify the special test cases
        global $usr;
        $cmp = new component($usr);
        $cmp->load_by_name(component_api::TN_EXCLUDED);
        $cmp->set_excluded(true);
        $cmp->save();

    }

}