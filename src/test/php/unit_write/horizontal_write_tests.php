<?php

/*

    test/php/unit_write/horizontal_write_tests.php - common write test for the main objects
    ----------------------------------------------

    perform the same database write test for the main database objects
  

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

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql_db.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class horizontal_write_tests
{

    function run(test_cleanup $t): void
    {
        global $sys;

        // init
        $lib = new library();
        $msg = new user_message($t->usr1);
        $t_map = new test_mappers($t);
        $t_wrd = new test_words($t);
        $cac = new data_object($t->usr1);
        $t->name = 'horizontal db write->';

        // start the test section (ts)
        $ts = 'db write horizontal ';
        $t->header($ts);

        $ids = [];

        $t->subheader($ts . 'insert');
        foreach (def::MAIN_CLASSES as $class) {

            // TODO Prio 1 add link and value classes
            if (in_array($class, def::NAME_CLASSES)) {

                $test_name = 'insert ' . $lib->class_to_name($class) . ' via SQL function';
                $obj = $t_map->class_to_add_object($class, $msg, $cac);
                $t->assert_save($test_name, $obj, $msg);

                // remember the word to be able to use it for a proper triple
                if ($obj::class == word::class) {
                    $cac->add_word($obj);
                }

                // remember the verb to be able to use it for a proper triple
                if ($obj::class == verb::class) {
                    $cac->add_verb($obj);
                    $sys->add_verb($obj);
                }

                // create a second word for a proper triple
                if ($class == word::class) {
                    $wrd2 = $t_wrd->word_add_via_api();
                    $wrd2->save($msg);
                    $cac->add_word($wrd2);
                }

                $test_name = 'reload ' . $lib->class_to_name($class) . ' and check differences';
                $ids[$class] = $obj->id();  // remember id for update and delete loops
                $check_obj = $obj->clone_reset(true);
                $check_obj->load_by_id($ids[$class]);
                $diff = $check_obj->diff_msg($obj);
                $t->assert_true($test_name, $diff->is_ok());

                $test_name = 'reload ' . $lib->class_to_name($class) . ' by name and check differences';
                $check_obj->reset(true);
                $check_obj->load_by_name($obj->name());
                $diff = $check_obj->diff_msg($obj);
                $t->assert_true($test_name, $diff->is_ok());
            }
        }

        $t->subheader($ts . 'update');
        foreach (def::MAIN_CLASSES as $class) {

            // TODO Prio 1 add link and value classes
            if (in_array($class, def::NAME_CLASSES)) {
                // reload the object
                $obj = $t_map->class_to_object($class, $t->usr1);
                $obj->load_by_id($ids[$class]);

                // set the sql creation types
                $sc_par_lst = [];
                if (!in_array($class, def::CLASSES_NO_CHANGE_LOG)) {
                    $sc_par_lst = [sql_type::LOG];
                }

                $test_name = 'update ' . $lib->class_to_name($class) . ' via SQL function';
                $obj->fill($t_map->class_to_add_filled_object($class, $cac), $t->usr1);
                $t->assert_save($test_name, $obj, $msg, $sc_par_lst);

                $test_name = 'reload filled ' . $lib->class_to_name($class) . ' and check differences';
                $check_obj = $obj->clone_reset(true);
                $check_obj->load_by_id($ids[$class]);
                $diff = $check_obj->diff_msg($obj);
                $t->assert_true($test_name, $diff->is_ok());

                $msg_upd = $msg->clone_reset();
                if (in_array($obj::class, def::ONLY_ADMIN_CAN_RENAME_CLASSES)) {
                    $test_name = 'rename ' . $lib->class_to_name($class) . ' without privileges returns error';
                    $existing_name = $t_map->class_to_base_object($class)->name();
                    $obj->set_name($existing_name);
                    $t->assert_false($test_name, $obj->save($msg));
                    $t->assert_text_contains($test_name, $msg->text(), msg_id::NO_PRIVILEGES->value);
                    $msg_upd->usr = $t->user_system();
                }

                $test_name = 'rename ' . $lib->class_to_name($class) . ' to existing name returns error';
                $existing_name = $t_map->class_to_base_object($class)->name();
                $obj->set_name($existing_name);
                $t->assert_false($test_name, $obj->save($msg_upd));
                $t->assert_text_contains($test_name, $msg_upd->text(), msg_id::ALREADY_EXISTS->value);

                // reset the message for the next test
                $msg->reset();

                // TODO Prio 1 test if renaming to name that this user has excluded does not returns an error message

            }
        }

        $t->subheader($ts . 'delete');
        $class_lst_rev = array_reverse(def::MAIN_CLASSES);
        foreach ($class_lst_rev as $class) {

            // TODO Prio 1 add link and value classes
            if (in_array($class, def::NAME_CLASSES)) {

                // reload the object
                $obj = $t_map->class_to_object($class, $t->usr1);
                $obj->load_by_id($ids[$class]);

                // set the sql creation types
                $sc_par_lst = [];
                if (!in_array($class, def::CLASSES_NO_CHANGE_LOG)) {
                    $sc_par_lst = [sql_type::LOG];
                }

                // use the system user to delete critical classes
                if (in_array($class, def::NO_DELETE_CLASSES)) {
                    $msg->usr = $t->usr_system;
                } else {
                    $msg->usr = $t->usr1;
                }

                // TODO Prio 2 delete changes caused by the test before deleting the test row

                $test_name = 'delete ' . $lib->class_to_name($class) . ' via SQL function';
                $t->assert_delete($test_name, $obj, $msg, $sc_par_lst);

                $test_name = 'reload ' . $lib->class_to_name($class) . ' and check that it has been remove';
                $t->assert_false($test_name, $obj->load_by_id($ids[$class]));

                // delete the second word used for a proper triple creation
                if ($class == word::class) {
                    $wrd2 = $t_wrd->word_add_via_api();
                    $wrd2->load_by_name($wrd2->name());
                    $wrd2->del($msg);
                }
            }

        }

        // TODO Prio 2 fill in the tests
        $t->subheader($ts . 'save');
        $t->subheader($ts . 'remove');

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($msg);

    }


}
