<?php

/*

    test/unit_read/horizontal_read_tests.php - database read testing of the functions that all main classes have
    ----------------------------------------

    the tests for all main objects include
    - load: if the object can be loaded from the database which includes testing the row_mapper



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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view_relation.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class horizontal_read_tests
{
    function run(test_cleanup $t): void
    {
        // init
        $t_map = new test_mappers($t);

        // start the test section (ts)
        $ts = 'db read horizontal ';
        $t->header($ts);

        $t->subheader($ts . 'load');
        foreach (def::MAIN_CLASSES as $class) {
            $base_obj = $t_map->class_to_base_object($class);
            $t->assert_load_by_id($base_obj, $base_obj->id());
        }
        // negative: the biggest id is never used, so no view relation is loaded
        $test_name = 'a view relation with an unknown id is not loaded';
        $msg = new user_message($t->usr1);
        $mrl = $t_map->class_to_base_object(view_relation::class);
        $loaded_id = $mrl->load_by_id(PHP_INT_MAX, $msg);
        $t->assert($test_name, $loaded_id, 0);
        $msg->reset();

        foreach (def::NAME_CLASSES as $class) {
            $base_obj = $t_map->class_to_base_object($class);
            $t->assert_load_by_name($base_obj, $base_obj->name());
        }

    }

}