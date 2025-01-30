<?php

/*

    test/unit/sys_log_tests.php - unit testing of the user log functions
    ---------------------------
  

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

include_once MODEL_SYSTEM_PATH . 'system_time_type.php';
include_once MODEL_SYSTEM_PATH . 'system_time.php';

use cfg\db\sql_creator;
use cfg\system\sys_log;
use cfg\system\system_time;
use cfg\system\system_time_type;
use shared\library;
use shared\types\api_type;
use test\test_cleanup;

class sys_log_tests
{

    // const used for system testing
    CONST TV_TIME = '2023-01-03T20:59:59+0100'; // time for unit tests
    CONST TV_LOG_TEXT = 'the log text that describes the problem for the user or system admin';
    CONST TV_LOG_TRACE = 'the technical trace back description for debugging';
    CONST TV_FUNC_NAME = 'name of the function that has caused the exception';
    CONST TV_SOLVE_ID = 'code id of the suggested solver of the problem';
    CONST T2_TIME = '2023-01-03T21:45:01+0100'; // time for unit tests
    CONST T2_LOG_TEXT = 'the log 2 text that describes the problem for the user or system admin';
    CONST T2_LOG_TRACE = 'the technical trace 2 back description for debugging';
    CONST T2_FUNC_NAME = 'name 2 of the function that has caused the exception';
    CONST T2_SOLVE_ID = 'code id 2 of the suggested solver of the problem';

    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('Unit tests of the system exception log display class (src/main/php/log/sys_log_*.php)');

        $t->subheader('SQL statement tests');

        // init
        $lib = new library();
        $sc = new sql_creator();
        $t->name = 'sys_log->';
        $t->resource_path = 'db/sys_log/';


        $t->subheader('System log SQL setup statements');
        $log = new sys_log();
        $t->assert_sql_table_create($log);
        $t->assert_sql_index_create($log);
        $t->assert_sql_foreign_key_create($log);


        // sql to load one error by id
        $err = new sys_log();
        $t->assert_sql_by_id($sc, $err);


        $t->subheader('API unit tests');

        $log_lst = $t->sys_log_list();
        $t->assert_api($log_lst, '', [api_type::HEADER]);


        $t->subheader('System time type SQL setup statements');
        $sys_exe_typ = new system_time_type('');
        $t->assert_sql_table_create($sys_exe_typ);
        $t->assert_sql_index_create($sys_exe_typ);

        $t->subheader('System time SQL setup statements');
        $sys_exe = new system_time();
        $t->assert_sql_table_create($sys_exe);
        $t->assert_sql_index_create($sys_exe);
        $t->assert_sql_foreign_key_create($sys_exe);

    }

}
