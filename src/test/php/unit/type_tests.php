<?php

/*

    test/unit/language.php - unit testing of the language functions
    ----------------------


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

use cfg\phrase_type;
use cfg\user\user_type;
use test\test_cleanup;

class type_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'type->';
        $t->resource_path = 'db/type/';


        $t->header('Unit tests of the type classes');

        // TODO add config, sys_log_types, sys_log_status, calc_and_cleanup_task_types

        $t->subheader('User type SQL setup statements');
        $usr_typ = new user_type('');
        $t->assert_sql_table_create($usr_typ);
        $t->assert_sql_index_create($usr_typ);

        $t->subheader('Phrase type SQL setup statements');
        $phr_typ = new phrase_type('');
        $t->assert_sql_table_create($phr_typ);
        $t->assert_sql_index_create($phr_typ);

    }

}