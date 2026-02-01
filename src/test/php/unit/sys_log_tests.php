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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::MODEL_SYSTEM . 'system_time.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\system\system_time;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class sys_log_tests
{

    // const used for system testing
    CONST string TV_TIME = '2023-01-03T20:59:59+0100'; // time for unit tests
    CONST string TV_TIME_TO = '2023-01-03T21:58:58+0100'; // a second time for unit tests
    CONST string TV_LOG_TEXT = 'the log text that describes the problem for the user or system admin';
    CONST string TV_LOG_TRACE = 'the technical trace back description for debugging';
    CONST string TV_FUNC_NAME = 'name of the function that has caused the exception';
    CONST string TV_SOLVE_ID = 'code id of the suggested solver of the problem';
    CONST string T2_TIME = '2023-01-03T21:45:01+0100'; // time for unit tests
    CONST string T2_LOG_TEXT = 'the log 2 text that describes the problem for the user or system admin';
    CONST string T2_LOG_TRACE = 'the technical trace 2 back description for debugging';
    CONST string T2_FUNC_NAME = 'name 2 of the function that has caused the exception';
    CONST string T2_SOLVE_ID = 'code id 2 of the suggested solver of the problem';

    function run(test_cleanup $t): void
    {

        // init
        $sc = new sql_creator();
        $t->name = 'sys_log->';
        $t->resource_path = 'db/sys_log/';

        // start the test section (ts)
        $ts = 'unit log ';
        $t->header($ts);

        $t->subheader($ts . 'system sql setup');
        $log = new sys_log();
        $t->assert_sql_table_create($log);
        $t->assert_sql_index_create($log);
        $t->assert_sql_foreign_key_create($log);


        // sql to load one error by id
        $err = new sys_log();
        $t->assert_sql_by_id($sc, $err);


        $t->subheader($ts . 'api');

        $t_sys = new test_sys_log();
        $log_lst = $t_sys->sys_log_list();
        $t->assert_api($log_lst, '', [api_types::HEADER, api_types::INCL_COMPONENTS]);


        $t->subheader($ts . 'system time type sql setup');
        $sys_exe_typ = new system_time_type('');
        $t->assert_sql_table_create($sys_exe_typ);
        $t->assert_sql_index_create($sys_exe_typ);

        $t->subheader($ts . 'system time sql setup');
        $sys_exe = new system_time();
        $t->assert_sql_table_create($sys_exe);
        $t->assert_sql_index_create($sys_exe);
        $t->assert_sql_foreign_key_create($sys_exe);

    }

}
