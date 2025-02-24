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
include_once SHARED_ENUM_PATH . 'change_tables.php';
include_once SHARED_ENUM_PATH . 'change_fields.php';

use cfg\component\component;
use cfg\log\change;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use shared\const\components;
use shared\enum\change_fields;
use shared\enum\change_tables;
use shared\types\component_type as comp_type_shared;
use test\test_cleanup;

class component_write_tests
{

    function run(test_cleanup $t): void
    {
        global $cmp_typ_cac;

        $t->header('component db write tests');

        $t->subheader('component prepared write');
        $test_name = 'add component ' . components::TEST_ADD_VIA_SQL_NAME . ' via sql insert';
        $t->assert_write_via_func_or_sql($test_name, $t->component_add_by_sql(), false);
        $test_name = 'add component ' . components::TEST_ADD_VIA_FUNC_NAME . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t->component_add_by_func(), true);

        $t->subheader('component write sandbox tests for ' . components::TEST_ADD_NAME);
        $t->assert_write_named($t->component_filled_add(), components::TEST_ADD_NAME);

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
        $wrd->set_name(words::TN_ABB);
        $wrd->load();
        $result = $cmp->display($wrd);
        // check if the component contains the word name
        $target = words::TN_ABB;
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
        $cmp->set_name(components::TEST_ADD_NAME);
        $cmp->description = 'Just added for testing';
        $result = $cmp->save()->get_last_message();
        if ($cmp->id() > 0) {
            $result = $cmp->description;
        }
        $target = 'Just added for testing';
        $t->display('component->save for adding "' . $cmp->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the component name has been saved
        $cmp_added = new component($t->usr1);
        $cmp_added->load_by_name(components::TEST_ADD_NAME);
        $result = $cmp_added->description;
        $target = 'Just added for testing';
        $t->display('component->load the added "' . $cmp_added->name() . '"', $target, $result);

        // check if the component adding has been logged
        $result = $t->log_last_by_field($cmp, component::FLD_NAME, $cmp->id(), true);
        $target = user::SYSTEM_TEST_NAME . ' added "System Test View Component"';
        $t->display('component->save adding logged for "' . components::TEST_ADD_NAME . '"', $target, $result);

        // check if adding the same component again creates a correct error message
        $cmp = new component($t->usr1);
        $cmp->set_name(components::TEST_ADD_NAME);
        $result = $cmp->save()->get_last_message();
        // in case of other settings
        $target = 'A view component with the name "' . components::TEST_ADD_NAME . '" already exists. Please use another name.';
        // for the standard settings
        $target = '';
        $t->display('component->save adding "' . $cmp->name() . '" again', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the component can be renamed
        $cmp = new component($t->usr1);
        $cmp->load_by_name(components::TEST_ADD_NAME);
        $cmp->set_name(components::TEST_RENAMED_NAME);
        $result = $cmp->save()->get_last_message();
        $target = '';
        $t->display('component->save rename "' . components::TEST_ADD_NAME . '" to "' . components::TEST_RENAMED_NAME . '".', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the component renaming was successful
        $cmp_renamed = new component($t->usr1);
        $cmp_renamed->load_by_name(components::TEST_RENAMED_NAME);
        if ($cmp_renamed->id() > 0) {
            $cmp_renamed_reloaded = new component($t->usr1);
            $cmp_renamed_reloaded->load_by_id($cmp_renamed->id());
            $result = $cmp_renamed_reloaded->name();
        }
        $target = components::TEST_RENAMED_NAME;
        $t->display('component->load renamed component "' . components::TEST_RENAMED_NAME . '"', $target, $result);

        // check if the component renaming has been logged
        $result = $t->log_last_by_field($cmp_renamed, component::FLD_NAME, $cmp_renamed->id(), true);
        $target = user::SYSTEM_TEST_NAME . ' changed "System Test View Component" to "System Test View Component Renamed"';
        $t->display('component->save rename logged for "' . components::TEST_RENAMED_NAME . '"', $target, $result);

        // check if the component parameters can be added
        $cmp_renamed = new component($t->usr1);
        $cmp_renamed->load_by_name(components::TEST_RENAMED_NAME);
        $cmp_renamed->description = 'Just added for testing the user sandbox';
        $cmp_renamed->type_id = $cmp_typ_cac->id(comp_type_shared::PHRASE_NAME);
        $result = $cmp_renamed->save()->get_last_message();
        $target = '';
        $t->display('component->save all component fields beside the name for "' . components::TEST_RENAMED_NAME . '"', $target, $result, $t::TIMEOUT_LIMIT_LONG);

        // check if the component parameters have been added
        $cmp_reloaded = new component($t->usr1);
        $cmp_reloaded->load_by_name(components::TEST_RENAMED_NAME);
        $result = $cmp_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . components::TEST_RENAMED_NAME . '"', $target, $result);
        $result = $cmp_reloaded->type_id;
        $target = $cmp_typ_cac->id(comp_type_shared::PHRASE_NAME);
        $t->display('component->load type_id for "' . components::TEST_RENAMED_NAME . '"', $target, $result);

        // check if the component parameter adding have been logged
        // TODO for testing always use the latest table name
        // TODO create an additional test based on change_tables and change_fields to receive data for an deprecated table or field
        $result = $t->log_last_by_field($cmp_reloaded, sandbox_named::FLD_DESCRIPTION, $cmp_reloaded->id(), true);
        // TODO fix it
        $target = user::SYSTEM_TEST_NAME . ' added "Just added for testing the user sandbox"';
        if ($result != $target) {
            $target = user::SYSTEM_TEST_PARTNER_NAME . ' changed "Just added for testing the user sandbox" to "Just changed for testing the user sandbox"';
        }
        $t->display('component->load comment for "' . components::TEST_RENAMED_NAME . '" logged', $target, $result);
        $result = $t->log_last_by_field($cmp_reloaded, change_fields::FLD_COMPONENT_TYPE, $cmp_reloaded->id(), true);
        // TODO fix it
        $target = user::SYSTEM_TEST_NAME . ' added "word name"';
        if ($result != $target) {
            $target = user::SYSTEM_TEST_PARTNER_NAME . ' changed "word name" to "formulas"';
        }
        $t->display('component->load component_type_id for "' . components::TEST_RENAMED_NAME . '" logged', $target, $result);

        // check if a user specific component is created if another user changes the component
        $cmp_usr2 = new component($t->usr2);
        $cmp_usr2->load_by_name(components::TEST_RENAMED_NAME);
        $cmp_usr2->description = 'Just changed for testing the user sandbox';
        $cmp_usr2->type_id = $cmp_typ_cac->id(comp_type_shared::FORMULAS);
        $result = $cmp_usr2->save()->get_last_message();
        $target = '';
        $t->display('component->save all component fields for user 2 beside the name for "' . components::TEST_RENAMED_NAME . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific component changes have been saved
        $cmp_usr2_reloaded = new component($t->usr2);
        $cmp_usr2_reloaded->load_by_name(components::TEST_RENAMED_NAME);
        $result = $cmp_usr2_reloaded->description;
        $target = 'Just changed for testing the user sandbox';
        $t->display('component->load comment for "' . components::TEST_RENAMED_NAME . '"', $target, $result);
        $result = $cmp_usr2_reloaded->type_id;
        $target = $cmp_typ_cac->id(comp_type_shared::FORMULAS);
        $t->display('component->load type_id for "' . components::TEST_RENAMED_NAME . '"', $target, $result);

        // check the component for the original user remains unchanged
        $cmp_reloaded = new component($t->usr1);
        $cmp_reloaded->load_by_name(components::TEST_RENAMED_NAME);
        $result = $cmp_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . components::TEST_RENAMED_NAME . '"', $target, $result);
        $result = $cmp_reloaded->type_id;
        $target = $cmp_typ_cac->id(comp_type_shared::PHRASE_NAME);
        $t->display('component->load type_id for "' . components::TEST_RENAMED_NAME . '"', $target, $result);

        // check if undo all specific changes removes the user component
        $cmp_usr2 = new component($t->usr2);
        $cmp_usr2->load_by_name(components::TEST_RENAMED_NAME);
        $cmp_usr2->description = 'Just added for testing the user sandbox';
        $cmp_usr2->type_id = $cmp_typ_cac->id(comp_type_shared::PHRASE_NAME);
        $result = $cmp_usr2->save()->get_last_message();
        $target = '';
        $t->display('component->save undo the user component fields beside the name for "' . components::TEST_RENAMED_NAME . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific component changes have been saved
        $cmp_usr2_reloaded = new component($t->usr2);
        $cmp_usr2_reloaded->load_by_name(components::TEST_RENAMED_NAME);
        $result = $cmp_usr2_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->display('component->load comment for "' . components::TEST_RENAMED_NAME . '"', $target, $result);
        //$result = $dsp_usr2_reloaded->type_id;
        //$target = cl(SQL_VIEW_TYPE_WORD_NAME);
        //$t->display('component->load type_id for "'.component::TEST_NAME_RENAMED.'"', $target, $result);

        // redo the user specific component changes
        // check if the user specific changes can be removed with one click

        // cleanup - fallback delete
        $cmp = new component($t->usr1);
        foreach (components::TEST_COMPONENTS as $cmp_name) {
            $t->write_named_cleanup($cmp, $cmp_name);
        }

    }

    function create_test_components(test_cleanup $t): void
    {
        $t->header('Check if all base view components are existing');

        $t->test_component(components::TEST_TITLE_NAME, comp_type_shared::PHRASE_NAME);
        $t->test_component(components::TEST_VALUES_NAME, comp_type_shared::VALUES_ALL);
        $t->test_component(components::TEST_RESULTS_NAME, comp_type_shared::FORMULA_RESULTS);
        $t->test_component(components::TEST_EXCLUDED_NAME, comp_type_shared::PHRASE_NAME);
        $t->test_component(components::TEST_TABLE_NAME, comp_type_shared::NUMERIC_VALUE);

        // modify the special test cases
        global $usr;
        $cmp = new component($usr);
        $cmp->load_by_name(components::TEST_EXCLUDED_NAME);
        $cmp->set_excluded(true);
        $cmp->save();

    }

}