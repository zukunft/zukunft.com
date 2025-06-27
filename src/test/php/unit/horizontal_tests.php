<?php

/*

    test/unit/horizontal_tests.php - unit testing of the functions that all main classes have
    ------------------------------

    the tests for all main objects include
    - fill: check if an imported object is filled correctly with the db object



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

namespace unit;

include_once MODEL_CONST_PATH . 'def.php';

use cfg\const\def;
use test\test_cleanup;

class horizontal_tests
{
    function run(test_cleanup $t): void
    {


        // start the test section (ts)
        $ts = 'unit horizontal ';
        $t->header($ts);

        $t->subheader($ts . 'fill');
        foreach (def::MAIN_CLASSES as $class) {
            $base_obj = $t->class_to_base_object($class);
            $filled_obj = $t->class_to_filled_object($class);
            $t->assert_fill($base_obj, $filled_obj);
        }

    }

}