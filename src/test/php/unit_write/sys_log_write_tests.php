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

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_function;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_functions;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
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
        $sys_log = new sys_log_function();
        $t->assert_insert($test_name, $sys_log);
        $test_name = 'update description of function ' . sys_log_functions::TEST_NAME;
        $sys_log->description = sys_log_functions::TEST_COM;
        $t->assert_update($test_name, $sys_log);


        // cleanup - fallback delete
        $t_sys->cleanup($ts);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($usr_msg);

    }


}
