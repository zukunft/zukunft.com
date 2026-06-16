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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'change_tables.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class component_link_write_tests
{

    function run(test_cleanup $t): void
    {
        // init
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write component link ';
        $t->header($ts);

        $t->subheader($ts . 'component link write sandbox tests for ' . views::TEST_ADD_NAME . ' and ' . components::TEST_ADD_NAME);
        // TODO Prio 2 activate (set object id instead of id)
        // $t->assert_write_link($t_cmp->component_link_filled_add());


        $t->subheader($ts . 'prepare');
        $msk = $t_db->test_view(views::TEST_ADD_NAME, $t->usr1, $usr_msg);
        $cmp = $t_db->test_component(components::TEST_ADD_NAME);

        $test_name = 'link the test view component "' . $cmp->name() . '" to view  (' . $msk->name() . ')';
        $order_nbr = $cmp->next_nbr($msk->id());
        $result = $cmp->link($msk, $order_nbr, $usr_msg);
        $t->assert_true($test_name, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check log of linking the component "' . $cmp->name() . '" to the view "' . $msk->name() . '"';
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::VIEW_LINK);
        $log->new_from_id = $msk->id();
        $log->new_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = users::SYSTEM_TEST_NAME . ' linked ' . views::TEST_ADD_NAME . ' to ' . components::TEST_ADD_NAME;
        $t->assert($test_name, $result, $target);

        $test_name = 'check list of linked views contains the added view for user "' . $t->usr1->dsp_id() . '"';
        $cmp = $t_db->load_component(components::TEST_ADD_NAME);
        $msk_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($msk_lst);
        $t->assert($test_name, $result, true);

        $test_name = 'check if the link is shown correctly also for the second user "' . $t->usr2->dsp_id() . '"';
        $cmp = $t_db->load_component(components::TEST_ADD_NAME, $t->usr2);
        $msk_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($msk_lst);
        $t->assert($test_name, $result, true);

        // ... check if the value update has been triggered

        // if second user removes the new link
        $test_name = 'view component_link->unlink "' . $msk->name() . '" from "' . $cmp->name() . '" by user "' . $t->usr2->name . '"';
        $cmp = $t_db->load_component(components::TEST_ADD_NAME, $t->usr2);
        $msk = new view($t->usr2);
        $msk->load_by_name(views::TEST_ADD_NAME, view::class);
        $result = $cmp->unlink($msk, $usr_msg);
        $t->assert_true($test_name, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the removal of the link for the second user has been logged
        $log = new change_link($t->usr2);
        $log->set_table(change_tables::VIEW_LINK);
        $log->old_from_id = $msk->id();
        $log->old_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        // TODO Prio 2 activate
        $target = $t->usr2->name() . ' unlinked ' . views::TEST_ADD_NAME . ' from ' . components::TEST_ADD_NAME;
        $target = $t->usr2->name() . ' ';
        $t->assert('view component_link->unlink_dsp logged for "' . $msk->name() . '" to "' . $cmp->name() . '" and user "' . $t->usr2->name . '"', $result, $target);


        // ... check if the link is really not used any more for the second user
        $cmp = $t_db->load_component(components::TEST_ADD_NAME, $t->usr2);
        $msk_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($msk_lst);
        $target = false;
        $t->assert('view component->assign_dsp_ids contains "' . $msk->name() . '" for user "' . $t->usr2->name . '" not any more', $result, $target);


        // ... check if the value update for the second user has been triggered

        // ... check if the link is still used for the first user
        $cmp = $t_db->load_component(components::TEST_ADD_NAME);
        $msk_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($msk_lst);
        $target = true;
        $t->assert('view component->assign_dsp_ids still contains "' . $msk->name() . '" for user "' . $t->usr1->name . '"', $result, $target);

        // ... check if the values for the first user are still the same

        // if the first user also removes the link, both records should be deleted
        $test_name = 'view component_link->unlink "' . $msk->name() . '" from "' . $cmp->name() . '"';
        $result = $cmp->unlink($msk, $usr_msg);
        $t->assert_true($test_name, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::VIEW_LINK);
        $log->old_from_id = $msk->id();
        $log->old_to_id = $cmp->id();
        $result = $log->dsp_last(true);
        $target = users::SYSTEM_TEST_NAME . ' unlinked ' . views::TEST_ADD_NAME . ' from ' . components::TEST_ADD_NAME;
        $t->assert('view component_link->unlink_dsp logged of "' . $msk->name() . '" from "' . $cmp->name() . '"', $result, $target);

        // check if the view component is not used any more for both users
        $cmp = $t_db->load_component(components::TEST_ADD_NAME);
        $msk_lst = $cmp->assigned_msk_ids();
        $result = $msk->is_in_list($msk_lst);
        $target = false;
        $t->assert('view component->assign_dsp_ids contains "' . $msk->name() . '" for user "' . $t->usr1->name . '" not any more', $result, $target);

        // --------------------------------------------------------------------
        // check if changing the view component order can be done for each user
        // --------------------------------------------------------------------

        // load the view and view component objects
        $msk = $t_db->load_view(views::TEST_ADD_NAME);
        $dsp2 = $t_db->load_view(views::TEST_ADD_NAME, $t->usr2);
        $cmp = $t_db->load_component(components::TEST_ADD_NAME,);
        // create a second view element to be able to test the change of the view order
        $cmp2 = new component($t->usr1);
        $cmp2->set_name(components::TEST_ADD_2_NAME);
        $cmp2->description = 'Just added a second view component for testing';
        $cmp2->save($usr_msg);
        $result = $usr_msg->get_last_message();
        if ($cmp2->id() > 0) {
            $result = $cmp2->description;
        }
        $target = 'Just added a second view component for testing';
        $t->assert('component->save for adding a second one "' . $cmp2->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // insert the link again for the first user
        $test_name = 'view component_link->link_dsp again for user 1 "' . $msk->name() . '" to "' . $cmp->name() . '"';
        $order_nbr = $cmp->next_nbr($msk->id());
        $result = $cmp->link($msk, $order_nbr, $usr_msg);
        $t->assert_true($test_name, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // add a second element for the first user to test the order change
        $test_name = 'view component_link->link_dsp the second for user 1 "' . $msk->name() . '" to "' . $cmp2->name() . '"';
        $order_nbr2 = $cmp2->next_nbr($msk->id());
        $result = $cmp2->link($msk, $order_nbr2, $usr_msg);
        $t->assert_true($test_name, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the order of the view components are correct for the first user
        if (isset($msk)) {
            $pos = 1;
            $msk->load_components();
            foreach ($msk->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = components::TEST_ADD_NAME;
                } else {
                    $target = components::TEST_ADD_2_NAME;
                }
                $result = $entry->get_component()->name();
                $t->assert('view component order for user 1', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
                $pos = $pos + 1;
            }
        }

        // check if the order of the view components are correct for the second user
        if (isset($dsp2)) {
            $pos = 1;
            $dsp2->load_components();
            foreach ($dsp2->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = components::TEST_ADD_NAME;
                } else {
                    $target = components::TEST_ADD_2_NAME;
                }
                $result = $entry->get_component()->name();
                $t->assert('view component order for user 2', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
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
            // TODO Prio 2 activate
            //$t->assert('view component order changed for user 2', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        }

        // check if the order of the view components is changed for the second user
        if (isset($dsp2)) {
            $pos = 1;
            $msk->load_components();
            foreach ($dsp2->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = components::TEST_ADD_2_NAME;
                } else {
                    $target = components::TEST_ADD_NAME;
                }
                // TODO check probably wrong
                if ($pos == 1) {
                    $target = components::TEST_ADD_NAME;
                } else {
                    $target = components::TEST_ADD_2_NAME;
                }
                $result = $entry->get_component()->name();
                $t->assert('view component order for user 2', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
                $pos = $pos + 1;
            }
        }

        // check if the order of the view components are still the same for the first user
        if (isset($msk)) {
            $pos = 1;
            $dsp2->load_components();
            foreach ($msk->cmp_lnk_lst->lst() as $entry) {
                if ($pos == 1) {
                    $target = components::TEST_ADD_NAME;
                } else {
                    $target = components::TEST_ADD_2_NAME;
                }
                $result = $entry->get_component()->name();
                $t->assert('view component order for user 1', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
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
        $test_name = 'view component_link->unlink again first component "' . $msk->name() . '" from "' . $cmp->name() . '"';
        $t->assert_true($test_name, $cmp->unlink($msk, $usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        // unlink the second component
        $test_name = 'view component_link->unlink again second component "' . $msk->name() . '" from "' . $cmp2->name() . '"';
        $t->assert_true($test_name, $cmp2->unlink($msk, $usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);


        // the code changes and tests for view component link should be moved the component_link
        $t->subheader($ts . 'cleanup component link write');
        $msk->del($usr_msg);
        $cmp->del($usr_msg);

    }

    function prepare(test_cleanup $t): void
    {

    }

    function create_test_component_links(test_cleanup $t): void
    {
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db create component links ';
        $t->header($ts);

        $t_db->test_component_lnk(views::TEST_COMPLETE_NAME, components::TEST_TITLE_NAME, 1);
        $t_db->test_component_lnk(views::TEST_COMPLETE_NAME, components::TEST_VALUES_NAME, 2);
        $t_db->test_component_lnk(views::TEST_COMPLETE_NAME, components::TEST_RESULTS_NAME, 3);

        $t_db->test_component_lnk(views::TEST_EXCLUDED_NAME, components::TEST_EXCLUDED_NAME, 1);

        $t_db->test_component_lnk(views::TEST_TABLE_NAME, components::TEST_TITLE_NAME, 1);
        $t_db->test_component_lnk(views::TEST_TABLE_NAME, components::TEST_TABLE_NAME, 2);
    }

}