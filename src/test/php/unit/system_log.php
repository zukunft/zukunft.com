<?php

/*

    test/unit/error_log.php - unit testing of the user log functions
    ------------------------
  

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

namespace test;

use model\library;
use model\sql_db;
use model\system_log;

class system_log_unit_tests
{
    function run(testing $t): void
    {

        global $usr;

        $t->header('Unit tests of the system exception log display class (src/main/php/log/system_log_*.php)');

        $t->subheader('SQL statement tests');

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'system_log->';
        $t->resource_path = 'db/system_log/';
        $usr->set_id(1);

        // sql to load one error by id
        $err = new system_log();
        $t->assert_load_sql_id($db_con, $err);


        $t->subheader('API unit tests');

        $log_lst = $t->dummy_system_log_list();
        $t->assert_api($log_lst);

    }

}
