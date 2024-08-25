<?php

/*

    test/php/unit_write/component_link_tests.php - write test VIEW COMPONENT LINKS to the database and check the results
    --------------------------------------------
  

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

use api\component\component as component_api;
use api\view\view as view_api;
use cfg\component\component;
use cfg\component\component_link;
use cfg\log\change_link;
use cfg\log\change_table_list;
use cfg\user;
use cfg\view;
use test\test_cleanup;

class component_link_write_tests
{

    function run(test_cleanup $t): void
    {


        $t->header('component link db write tests');

        $t->subheader('component link write sandbox tests for ' . view_api::TN_ADD . ' and ' . component_api::TN_ADD);
        // TODO activate (set object id instead of id)
        // $t->assert_write_link($t->component_link_filled_add());


        $t->subheader('prepare component link write');
        $msk = $t->test_view(view_api::TN_ADD);
        $cmp = $t->test_component(component_api::TN_ADD);

        $test_name = 'link the test view component "' . $cmp->name() . '" to view  (' . $msk->name() . ')';
        $order_nbr = $cmp->next_nbr($msk->id());
        $result = $cmp->link($msk, $order_nbr);
        $target = '';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check log of linking the component "' . $cmp->name() . '" to the view "' . $msk->name() . '"';
        $log = new change_link($t->usr1);
        $log->set_table(change_table_list::VIEW_LINK);
        $log->new_from_id = $msk->id();
        $log->new_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = user::SYSTEM_TEST_NAME . ' linked ' . view_api::TN_ADD . ' to ' . component_api::TN_ADD;
        $t->assert($test_name, $result, $target);

        $test_name = 'check list of linked views contains the added view for user "' . $t->usr1->dsp_id() . '"';
        $cmp = $t->load_component(component_api::TN_ADD);
        $dsp_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($dsp_lst);
        $t->assert($test_name, $result, true);

        $test_name = 'check if the link is shown correctly also for the second user "' . $t->usr2->dsp_id() . '"';
        $cmp = $t->load_component(component_api::TN_ADD, $t->usr2);
        $dsp_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($dsp_lst);
        $t->assert($test_name, $result, true);

        // ... check if the value update has been triggered

        // if second user removes the new link
        $cmp = $t->load_component(component_api::TN_ADD, $t->usr2);
        $msk = new view($t->usr2);
        $msk->load_by_name(view_api::TN_ADD, view::class);
        $result = $cmp->unlink($msk);
        $target = '';
        $t->display('view component_link->unlink "' . $msk->name() . '" from "' . $cmp->name() . '" by user "' . $t->usr2->name . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the removal of the link for the second user has been logged
        $log = new change_link($t->usr2);
        $log->set_table(change_table_list::VIEW_LINK);
        $log->old_from_id = $msk->id();
        $log->old_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        // TODO activate
        $target = $t->usr2->name() . ' unlinked ' . view_api::TN_ADD . ' from ' . component_api::TN_ADD;
        $target = $t->usr2->name() . ' ';
        $t->display('view component_link->unlink_dsp logged for "' . $msk->name() . '" to "' . $cmp->name() . '" and user "' . $t->usr2->name . '"', $target, $result);


        // ... check if the link is really not used any more for the second user
        $cmp = $t->load_component(component_api::TN_ADD, $t->usr2);
        $dsp_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($dsp_lst);
        $target = false;
        $t->display('view component->assign_dsp_ids contains "' . $msk->name() . '" for user "' . $t->usr2->name . '" not any more', $target, $result);


        // ... check if the value update for the second user has been triggered

        // ... check if the link is still used for the first user
        $cmp = $t->load_component(component_api::TN_ADD);
        $dsp_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($dsp_lst);
        $target = true;
        $t->display('view component->assign_dsp_ids still contains "' . $msk->name() . '" for user "' . $t->usr1->name . '"', $target, $result);

        // ... check if the values for the first user are still the same

        // if the first user also removes the link, both records should be deleted
        $result = $cmp->unlink($msk);
        $target = '';
        $t->display('view component_link->unlink "' . $msk->name() . '" from "' . $cmp->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_table_list::VIEW_LINK);
        $log->old_from_id = $msk->id();
        $log->old_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = user::SYSTEM_TEST_NAME . ' unlinked ' . view_api::TN_ADD . ' from ' . component_api::TN_ADD;
        $t->display('view component_link->unlink_dsp logged of "' . $msk->name() . '" from "' . $cmp->name() . '"', $target, $result);

        // check if the view component is not used any more for both users
        $cmp = $t->load_component(component_api::TN_ADD);
        $dsp_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($dsp_lst);
        $target = false;
        $t->display('view component->assign_dsp_ids contains "' . $msk->name() . '" for user "' . $t->usr1->name . '" not any more', $target, $result);

        // --------------------------------------------------------------------
        // check if changing the view component order can be done for each user
        // --------------------------------------------------------------------

        // load the view and view component objects
        $msk = $t->load_view(view_api::TN_ADD);
        $dsp2 = $t->load_view(view_api::TN_ADD, $t->usr2);
        $cmp = $t->load_component(component_api::TN_ADD,);
        // create a second view element to be able to test the change of the view order
        $cmp2 = new component($t->usr1);
        $cmp2->set_name(component_api::TN_ADD2);
        $cmp2->description = 'Just added a second view component for testing';
        $result = $cmp2->save();
        if ($cmp2->id() > 0) {
            $result = $cmp2->description;
        }
        $target = 'Just added a second view component for testing';
        $t->display('component->save for adding a second one "' . $cmp2->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // insert the link again for the first user
        $order_nbr = $cmp->next_nbr($msk->id());
        $result = $cmp->link($msk, $order_nbr);
        $target = '';
        $t->display('view component_link->link_dsp again for user 1 "' . $msk->name() . '" to "' . $cmp->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // add a second element for the first user to test the order change
        $order_nbr2 = $cmp2->next_nbr($msk->id());
        $result = $cmp2->link($msk, $order_nbr2);
        $target = '';
        $t->display('view component_link->link_dsp the second for user 1 "' . $msk->name() . '" to "' . $cmp2->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the order of the view components are correct for the first user
        if (isset($msk)) {
            $pos = 1;
            $msk->load_components();
            foreach ($msk->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = component_api::TN_ADD;
                } else {
                    $target = component_api::TN_ADD2;
                }
                $result = $entry->component()->name();
                $t->display('view component order for user 1', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
                $pos = $pos + 1;
            }
        }

        // check if the order of the view components are correct for the second user
        if (isset($dsp2)) {
            $pos = 1;
            $dsp2->load_components();
            foreach ($dsp2->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = component_api::TN_ADD;
                } else {
                    $target = component_api::TN_ADD2;
                }
                $result = $entry->component()->name();
                $t->display('view component order for user 2', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
                $pos = $pos + 1;
            }
        }

        // ... if the second user changes the link e.g. the order
        $cmp_lnk = new component_link($t->usr2);
        $cmp_lnk->load_by_link($dsp2, $cmp2);
        if ($cmp_lnk->id() > 0) {
            $result = $cmp_lnk->move_up(); // TODO force to reload the entry list
            //$result = $cmp_lnk->move_up(); // TODO force to reload the entry list
            $target = true;
            // TODO activate Prio 2
            //$t->display('view component order changed for user 2', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
        }

        // check if the order of the view components is changed for the second user
        if (isset($dsp2)) {
            $pos = 1;
            $msk->load_components();
            foreach ($dsp2->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = component_api::TN_ADD2;
                } else {
                    $target = component_api::TN_ADD;
                }
                // TODO check probably wrong
                if ($pos == 1) {
                    $target = component_api::TN_ADD;
                } else {
                    $target = component_api::TN_ADD2;
                }
                $result = $entry->component()->name();
                $t->display('view component order for user 2', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
                $pos = $pos + 1;
            }
        }

        // check if the order of the view components are still the same for the first user
        if (isset($msk)) {
            $pos = 1;
            $dsp2->load_components();
            foreach ($msk->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = component_api::TN_ADD;
                } else {
                    $target = component_api::TN_ADD2;
                }
                $result = $entry->component()->name();
                $t->display('view component order for user 1', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
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
        $result = $cmp->unlink($msk);
        $target = '';
        $t->display('view component_link->unlink again first component "' . $msk->name() . '" from "' . $cmp->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // unlink the second component
        $result = $cmp2->unlink($msk);
        $target = '';
        $t->display('view component_link->unlink again second component "' . $msk->name() . '" from "' . $cmp2->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);


        // the code changes and tests for view component link should be moved the component_link

        $t->subheader('cleanup component link write');
        $msk->del();
        $cmp->del();

    }

    function prepare(test_cleanup $t): void
    {

    }

    function create_test_component_links(test_cleanup $t): void
    {
        $t->header('Check if all base view component links are existing');

        $t->test_component_lnk(view_api::TN_COMPLETE, component_api::TN_TITLE, 1);
        $t->test_component_lnk(view_api::TN_COMPLETE, component_api::TN_VALUES, 2);
        $t->test_component_lnk(view_api::TN_COMPLETE, component_api::TN_RESULTS, 3);

        $t->test_component_lnk(view_api::TN_EXCLUDED, component_api::TN_EXCLUDED, 1);

        $t->test_component_lnk(view_api::TN_TABLE, component_api::TN_TITLE, 1);
        $t->test_component_lnk(view_api::TN_TABLE, component_api::TN_TABLE, 2);
    }

}