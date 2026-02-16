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
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
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
        $t->name = 'horizontal db write->';

        // start the test section (ts)
        $ts = 'db write horizontal ';
        $t->header($ts);

        $t->subheader($ts . 'insert');
        foreach (def::MAIN_CLASSES as $class) {
            $test_name = 'insert ' . $lib->class_to_name($class) . ' via SQL function';
            $obj = $t_map->class_to_base_object($class);
            $sc_par_lst = [];
            if (!in_array($class, def::CLASSES_NO_CHANGE_LOG)) {
                $sc_par_lst = [sql_type::LOG];
            }
            $t->assert_insert($test_name, $obj, $msg, $sc_par_lst);

            $test_name = 'reload ' . $lib->class_to_name($class) . ' and check differences';
            $id = $obj->id();
            $check_obj = $obj->clone_reset();
            $check_obj->load_by_id($id);
            $diff = $check_obj->diff_msg($obj);
            $t->assert_true($test_name, $diff->is_ok());

            if (in_array($class, def::NAME_CLASSES)) {
                $test_name = 'reload ' . $lib->class_to_name($class) . ' by name and check differences';
                $check_obj->reset(true);
                $check_obj->load_by_id($obj->name());
                $diff = $check_obj->diff_msg($obj);
                $t->assert_true($test_name, $diff->is_ok());
            }

            $test_name = 'update ' . $lib->class_to_name($class) . ' via SQL function';
            $obj->fill($t_map->class_to_filled_object($class), $t->usr1);
            $t->assert_update($test_name, $obj, $msg, $sc_par_lst);

            $test_name = 'reload filled ' . $lib->class_to_name($class) . ' and check differences';
            $check_obj = $obj->clone_reset();
            $check_obj->load_by_id($id);
            $diff = $check_obj->diff_msg($obj);
            $t->assert_true($test_name, $diff->is_ok());

            if (in_array($class, def::NO_DELETE_CLASSES)) {
                $msg->usr = $t->usr_system;
            }

            // TODO Prio 2 delete changes caused by the test before deleting the test row

            $test_name = 'delete ' . $lib->class_to_name($class) . ' via SQL function';
            $t->assert_delete($test_name, $obj, $msg, $sc_par_lst);

            $test_name = 'reload ' . $lib->class_to_name($class) . ' and check that it has been remove';
            $t->assert_false($test_name, $check_obj->load_by_id($id));

        }

        $t->subheader($ts . 'save');
        $t->subheader($ts . 'remove');


        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($msg);

    }


}
