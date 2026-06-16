<?php

/*

    test/php/unit_write/sys_log_write_tests.php - write test for system log entries
    -------------------------------------------

    just the special test cases not covered by the horizontal write tests
  

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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql_db.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_function;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_functions;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class sys_log_write_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t_sys = new test_sys_log($t);
        $usr_msg = new user_message($t->usr1);
        $t->name = 'system log db write->';

        // start the test section (ts)
        $ts = 'db write system log ';
        $t->header($ts);
        $t_sys->cleanup($ts);

        $t->subheader($ts . 'functions / program parts');
        $test_name = 'add function ' . sys_log_functions::TEST_NAME . ' via sql function';
        $sys_log = new sys_log_function('sys_log_write_tests', sys_log_functions::TEST_NAME);
        $t->assert_insert($test_name, $sys_log, $usr_msg);
        $test_name = 'update description of function ' . sys_log_functions::TEST_NAME;
        $sys_log->description = sys_log_functions::TEST_COM;
        $t->assert_update($test_name, $sys_log, $usr_msg, [sql_type::LOG]);


        // cleanup - fallback delete
        $t_sys->cleanup($ts);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($usr_msg);

    }


}
