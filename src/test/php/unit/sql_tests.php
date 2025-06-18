<?php

/*

    test/unit/sql_tests.php - unit testing of the basic sql creation functions
    -----------------------

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

include_once TEST_CONST_PATH . 'files.php';

use cfg\db\sql_creator;
use cfg\formula\formula;
use html\user\user;
use test\test_cleanup;
use const\files as test_files;

class sql_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $sc = new sql_creator();
        $t->name = 'sql->';


        // start the test section (ts)
        $ts = 'unit sql ';
        $t->header($ts);

        $t->subheader($ts . 'count');
        $test_name = ' count of formulas';
        $sc->set_class(formula::class);
        $created = $sc->count_sql();
        $expected = file_get_contents(test_files::FORMULA_COUNT);
        $t->assert_sql($test_name, $created, $expected);
        $test_name = ' count of users';
        $sc->set_class(user::class);
        $created = $sc->count_sql();
        $expected = file_get_contents(test_files::USER_COUNT);
        $t->assert_sql($test_name, $created, $expected);

    }

}