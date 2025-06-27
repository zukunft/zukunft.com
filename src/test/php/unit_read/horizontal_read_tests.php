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

namespace unit_read;

include_once MODEL_CONST_PATH . 'def.php';

use cfg\const\def;
use test\test_cleanup;

class horizontal_read_tests
{
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'db read horizontal ';
        $t->header($ts);

        $t->subheader($ts . 'load');
        foreach (def::MAIN_CLASSES as $class) {
            $base_obj = $t->class_to_base_object($class);
            $t->assert_load_by_id($base_obj, $base_obj->id());
        }

    }

}