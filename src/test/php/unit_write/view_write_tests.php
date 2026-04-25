<?php

/*

    test/php/unit_write/view_write_tests.php - write test VIEWS to the database and check the results
    ----------------------------------------
  

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

include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_VIEW . 'view_db.php';
include_once paths::SHARED_ENUM . 'change_tables.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_db;
use Zukunft\ZukunftCom\main\php\cfg\view\view_type;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class view_write_tests
{

    function run(test_cleanup $t): void
    {
        global $sys;
        global $db_con;

        // init
        $t_msk = new test_views($t);
        $usr_msg = new user_message($t->usr1);
        $t->name = 'db write view ';

        // start the test section (ts)
        $ts = 'db write view ';
        $t->header($ts);

        $t->subheader($ts . 'prepared sql');
        $test_name = 'add view ' . views::TEST_ADD_VIA_FUNC_NAME . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t_msk->view_add_by_func(), true);

        $t->subheader($ts . 'for ' . views::TEST_ADD_NAME);
        $t->assert_write_named($t_msk->view_filled_add(), views::TEST_ADD_NAME);


        $db_con->import_system_views($t->usr1);

        $this->create_test_views($t);


        $test_name = 'load view with the name ' . views::TEST_COMPLETE_NAME;
        $msk_db = new view($t->usr1);
        $result = $msk_db->load_by_name(views::TEST_COMPLETE_NAME);
        $msk = new view_ui($msk_db->api_json());
        $target = 0;
        if ($result > 0) {
            $target = $result;
        }
        $t->assert($test_name, $result, $target);

        // test the complete view for one word
        $wrd = new word($t->usr1);
        $wrd->load_by_name(words::CH);
        //$result = $msk->display($wrd, $back);
        // check if the view contains the word name
        $target = words::CH;
        // TODO review and activate
        //$t->dsp_contains(', view->display "' . $msk->name() . '" for "' . $wrd->name() . '" contains', $target, $result, $t::TIMEOUT_LIMIT_LONG);
        // check if the view contains at least one value
        $target = 'back=' . $wrd->id() . '">8.51</a>';
        /* TODO fix the result display
        $t->dsp_contains(', view->display "' . $msk->name . '" for "' . $wrd->name() . '" contains', $result, $target);
        // check if the view contains at least the main formulas
        $target = 'System Test Word Increase';
        $t->dsp_contains(', view->display "' . $msk->name . '" for "' . $wrd->name() . '" contains', $result, $target);
        */
        /* TODO fix the result loading
        $target = 'back='.$wrd->id.'">0.79%</a>';
        $t->dsp_contains(', view->display "' . $msk->name . '" for "' . $wrd->name() . '" contains', $result, $target);
        */

        $test_name = 'add view with the name ' . views::TEST_ADD_NAME;
        $msk = new view($t->usr1);
        $msk->set_name(views::TEST_ADD_NAME);
        $msk->description = 'Just added for testing';
        $t->assert_true($test_name, $msk->save($usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check if the view name has been saved for '. views::TEST_ADD_NAME;
        $msk = new view($t->usr1);
        $msk->load_by_name(views::TEST_ADD_NAME, view::class);
        $result = $msk->description;
        $target = 'Just added for testing';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if the view adding has been logged for '. views::TEST_ADD_NAME;
        $result = $t->log_last_by_field($msk, view_db::FLD_NAME, $msk->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "System Test View"';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if adding a view with name '. views::TEST_ADD_NAME . ' again creates a correct error message';
        $msk = new view($t->usr1);
        $msk->set_name(views::TEST_ADD_NAME);
        $msk->save($usr_msg);
        $result = $usr_msg->get_last_message();
        // TODO Prio 2 review
        $target = 'A view with the name "' . views::TEST_ADD_NAME . '" already exists. Please use another name.'; // is this error message really needed???
        $target = '';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB);

        $test_name = 'check if the view can be renamed to '. views::TEST_RENAMED_NAME;
        $msk = new view($t->usr1);
        $msk->load_by_name(views::TEST_ADD_NAME, view::class);
        $msk->set_name(views::TEST_RENAMED_NAME);
        $t->assert_true($test_name, $msk->save($usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check if the view renaming was successful to '. views::TEST_RENAMED_NAME;
        $msk_renamed = new view($t->usr1);
        $result = $msk_renamed->load_by_name(views::TEST_RENAMED_NAME, view::class);
        if ($result) {
            if ($msk_renamed->id() > 0) {
                $result = $msk_renamed->name();
            }
        }
        $target = 'System Test View Renamed';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if the view renaming has been logged to '. views::TEST_RENAMED_NAME;
        $result = $t->log_last_by_field($msk_renamed, view_db::FLD_NAME, $msk_renamed->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' changed "System Test View" to "System Test View Renamed"';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if the view parameters (e.g. type) can be added to '. views::TEST_RENAMED_NAME;
        $msk_renamed->description = 'Just added for testing the user sandbox';
        $msk_renamed->type_id = $sys->typ_lst->msk_typ->id(view_type::WORD_DEFAULT);
        $t->assert_true($test_name, $msk_renamed->save($usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check if the description view parameters have been added to '. views::TEST_RENAMED_NAME;
        $msk_reloaded = new view($t->usr1);
        $msk_reloaded->load_by_name(views::TEST_RENAMED_NAME, view::class);
        $result = $msk_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if the type view parameters have been added to '. views::TEST_RENAMED_NAME;
        $result = $msk_reloaded->type_id;
        $target = $sys->typ_lst->msk_typ->id(view_type::WORD_DEFAULT);
        $t->assert($test_name, $result, $target);

        $test_name = 'check if the description view parameter adding have been logged to '. views::TEST_RENAMED_NAME;
        $result = $t->log_last_by_field($msk_reloaded, sql_db::FLD_DESCRIPTION, $msk_reloaded->id(), true);
        $target = 'zukunft.com system test changed "Just added for testing" to "Just added for testing the user sandbox"';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if the view_type view parameter adding have been logged to '. views::TEST_RENAMED_NAME;
        $result = $t->log_last_by_field($msk_reloaded, view_db::FLD_TYPE, $msk_reloaded->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "word default"';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if a user-specific view is created if another user changes the view to ' . views::TEST_RENAMED_NAME;
        $msk_usr2 = new view($t->usr2);
        $msk_usr2->load_by_name(views::TEST_RENAMED_NAME);
        $msk_usr2->description = 'Just changed for testing the user sandbox';
        $msk_usr2->type_id = $sys->typ_lst->msk_typ->id(view_type::ENTRY);
        $t->assert_true($test_name, $msk_usr2->save($usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check if a user-specific view comment have been saved for ' . views::TEST_RENAMED_NAME;
        $msk_usr2_reloaded = new view($t->usr2);
        $msk_usr2_reloaded->load_by_name(views::TEST_RENAMED_NAME);
        $result = $msk_usr2_reloaded->description;
        $target = 'Just changed for testing the user sandbox';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if a user-specific view type_id have been saved for ' . views::TEST_RENAMED_NAME;
        $result = $msk_usr2_reloaded->type_id;
        $target = $sys->typ_lst->msk_typ->id(view_type::ENTRY);
        $t->assert($test_name, $result, $target);

        $test_name = 'check the view comment for the original user remains unchanged for ' . views::TEST_RENAMED_NAME;
        $msk_reloaded = new view($t->usr1);
        $msk_reloaded->load_by_name(views::TEST_RENAMED_NAME);
        $result = $msk_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->assert($test_name, $result, $target);

        $test_name = 'check the view type_id for the original user remains unchanged for ' . views::TEST_RENAMED_NAME;
        $result = $msk_reloaded->type_id;
        $target = $sys->typ_lst->msk_typ->id(view_type::WORD_DEFAULT);
        $t->assert($test_name, $result, $target);

        $test_name = 'check if undo all specific changes removes the user view for ' . views::TEST_RENAMED_NAME;
        $msk_usr2 = new view($t->usr2);
        $msk_usr2->load_by_name(views::TEST_RENAMED_NAME);
        $msk_usr2->description = 'Just added for testing the user sandbox';
        $msk_usr2->type_id = $sys->typ_lst->msk_typ->id(view_type::WORD_DEFAULT);
        $t->assert_true($test_name, $msk_usr2->save($usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check if a user-specific view comment changes have been saved for ' . views::TEST_RENAMED_NAME;
        $msk_usr2_reloaded = new view($t->usr2);
        $msk_usr2_reloaded->load_by_name(views::TEST_RENAMED_NAME);
        $result = $msk_usr2_reloaded->description;
        $target = 'Just added for testing the user sandbox';
        $t->assert($test_name, $result, $target);

        $test_name = 'check if a user-specific view type_id changes have been saved for ' . views::TEST_RENAMED_NAME;
        $result = $msk_usr2_reloaded->type_id;
        $target = $sys->typ_lst->msk_typ->id(view_type::WORD_DEFAULT);
        $t->assert($test_name, $result, $target);

        // redo the user-specific view changes
        // check if the user-specific changes can be removed with one click

        $this->delete_test_views($t);

        // cleanup - fallback delete
        $msk = new view($t->usr1);
        foreach (views::TEST_VIEWS as $msk_name) {
            $t->write_named_cleanup($msk, $msk_name);
        }

    }

    function create_test_views(test_cleanup $t): void
    {
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db create test views ';
        $t->header($ts);

        foreach (views::TEST_VIEWS_AUTO_CREATE as $view_name) {
            $t_db->test_view($view_name, $t->usr1, $usr_msg);
        }

        // modify the special test cases
        global $usr;
        $msk = new view($usr);
        $msk->load_by_name(views::TEST_EXCLUDED_NAME);
        $msk->excluded = true;
        $msk->save($usr_msg);
    }

    function delete_test_views(test_cleanup $t): void
    {
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db del test views ';
        $t->header($ts);

        foreach (views::TEST_VIEWS_AUTO_CREATE as $view_name) {
            $t_db->del_view($view_name, $t->usr1, $usr_msg);
        }
    }

}