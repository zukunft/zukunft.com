<?php

/*

    test/php/unit_write/component_link.php - write test VIEW COMPONENT LINKS to the database and check the results
    --------------------------------------
  

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
use api\view\view as view_api;
use cfg\change_log_link;
use cfg\change_log_table;
use cfg\component\component;
use cfg\component_link;
use cfg\view;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_DB_MULTI;

class component_link_test
{

    function run(test_cleanup $t): void
    {

        $t->header('Test the view component link class (classes/component_link.php)');

        // prepare testing by creating the view and components needed for testing
        $dsp = $t->test_view(view_api::TN_RENAMED);
        $cmp = $t->test_component(component_api::TN_ADD);

        // link the test view component to another view
        $order_nbr = $cmp->next_nbr($dsp->id());
        $result = $cmp->link($dsp, $order_nbr);
        $target = '';
        $t->display('view component_link->link "' . $dsp->name() . '" to "' . $cmp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // ... check the correct logging
        $log = new change_log_link($t->usr1);
        $log->set_table(change_log_table::VIEW_LINK);
        $log->new_from_id = $dsp->id();
        $log->new_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test linked System Test View Renamed to System Test View Component';
        $t->display('view component_link->link_dsp logged for "' . $dsp->name() . '" to "' . $cmp->name() . '"', $target, $result);

        // ... check if the link is shown correctly
        $cmp = $t->load_component(component_api::TN_ADD);
        $dsp_lst = $cmp->assign_dsp_ids();
        $result = $dsp->is_in_list($dsp_lst);
        $target = true;
        $t->display('view component->assign_dsp_ids contains "' . $dsp->name() . '" for user "' . $t->usr1->name . '"', $target, $result);

        // ... check if the link is shown correctly also for the second user
        $cmp = $t->load_component(component_api::TN_ADD, $t->usr2);
        $dsp_lst = $cmp->assign_dsp_ids();
        $result = $dsp->is_in_list($dsp_lst);
        $target = true;
        $t->display('view component->assign_dsp_ids contains "' . $dsp->name() . '" for user "' . $t->usr2->name . '"', $target, $result);

        // ... check if the value update has been triggered

        // if second user removes the new link
        $cmp = $t->load_component(component_api::TN_ADD, $t->usr2);
        $dsp = new view($t->usr2);
        $dsp->load_by_name(view_api::TN_RENAMED, view::class);
        $result = $cmp->unlink($dsp);
        $target = '';
        $t->display('view component_link->unlink "' . $dsp->name() . '" from "' . $cmp->name() . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the removal of the link for the second user has been logged
        $log = new change_log_link($t->usr2);
        $log->set_table(change_log_table::VIEW_LINK);
        $log->old_from_id = $dsp->id();
        $log->old_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test partner unlinked System Test View Renamed from System Test View Component';
        $t->display('view component_link->unlink_dsp logged for "' . $dsp->name() . '" to "' . $cmp->name() . '" and user "' . $t->usr2->name . '"', $target, $result);


        // ... check if the link is really not used any more for the second user
        $cmp = $t->load_component(component_api::TN_ADD, $t->usr2);
        $dsp_lst = $cmp->assign_dsp_ids();
        $result = $dsp->is_in_list($dsp_lst);
        $target = false;
        $t->display('view component->assign_dsp_ids contains "' . $dsp->name() . '" for user "' . $t->usr2->name . '" not any more', $target, $result);


        // ... check if the value update for the second user has been triggered

        // ... check if the link is still used for the first user
        $cmp = $t->load_component(component_api::TN_ADD);
        $dsp_lst = $cmp->assign_dsp_ids();
        $result = $dsp->is_in_list($dsp_lst);
        $target = true;
        $t->display('view component->assign_dsp_ids still contains "' . $dsp->name() . '" for user "' . $t->usr1->name . '"', $target, $result);

        // ... check if the values for the first user are still the same

        // if the first user also removes the link, both records should be deleted
        $result = $cmp->unlink($dsp);
        $target = '';
        $t->display('view component_link->unlink "' . $dsp->name() . '" from "' . $cmp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check the correct logging
        $log = new change_log_link($t->usr1);
        $log->set_table(change_log_table::VIEW_LINK);
        $log->old_from_id = $dsp->id();
        $log->old_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test unlinked System Test View Renamed from System Test View Component';
        $t->display('view component_link->unlink_dsp logged of "' . $dsp->name() . '" from "' . $cmp->name() . '"', $target, $result);

        // check if the view component is not used any more for both users
        $cmp = $t->load_component(component_api::TN_ADD);
        $dsp_lst = $cmp->assign_dsp_ids();
        $result = $dsp->is_in_list($dsp_lst);
        $target = false;
        $t->display('view component->assign_dsp_ids contains "' . $dsp->name() . '" for user "' . $t->usr1->name . '" not any more', $target, $result);

        // --------------------------------------------------------------------
        // check if changing the view component order can be done for each user
        // --------------------------------------------------------------------

        // load the view and view component objects
        $dsp = $t->load_view(view_api::TN_RENAMED);
        $dsp2 = $t->load_view(view_api::TN_RENAMED, $t->usr2);
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
        $t->display('component->save for adding a second one "' . $cmp2->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // insert the link again for the first user
        $order_nbr = $cmp->next_nbr($dsp->id());
        $result = $cmp->link($dsp, $order_nbr);
        $target = '';
        $t->display('view component_link->link_dsp again for user 1 "' . $dsp->name() . '" to "' . $cmp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // add a second element for the first user to test the order change
        $order_nbr2 = $cmp2->next_nbr($dsp->id());
        $result = $cmp2->link($dsp, $order_nbr2);
        $target = '';
        $t->display('view component_link->link_dsp the second for user 1 "' . $dsp->name() . '" to "' . $cmp2->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if the order of the view components are correct for the first user
        if (isset($dsp)) {
            $pos = 1;
            $dsp->load_components();
            foreach ($dsp->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = component_api::TN_ADD;
                } else {
                    $target = component_api::TN_ADD2;
                }
                $result = $entry->name();
                $t->display('view component order for user 1', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
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
                $result = $entry->name();
                $t->display('view component order for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
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
            // TODO activate
            //$t->display('view component order changed for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // check if the order of the view components is changed for the second user
        if (isset($dsp2)) {
            $pos = 1;
            $dsp->load_components();
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
                $result = $entry->name();
                $t->display('view component order for user 2', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
                $pos = $pos + 1;
            }
        }

        // check if the order of the view components are still the same for the first user
        if (isset($dsp)) {
            $pos = 1;
            $dsp2->load_components();
            foreach ($dsp->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = component_api::TN_ADD;
                } else {
                    $target = component_api::TN_ADD2;
                }
                $result = $entry->name();
                $t->display('view component order for user 1', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
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
        $target = '';
        $t->display('view component_link->unlink again first component "' . $dsp->name() . '" from "' . $cmp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // unlink the second component
        $result = $cmp2->unlink($dsp);
        $target = '';
        $t->display('view component_link->unlink again second component "' . $dsp->name() . '" from "' . $cmp2->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);


        // the code changes and tests for view component link should be moved the component_link


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