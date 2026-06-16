<?php

/*

    test/unit/db_cache_tests.php - unit testing of the database cache
    ----------------------------
  

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'db_cache_statuum.php';
include_once paths::SHARED_TYPES . 'db_cache_types.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache;
use Zukunft\ZukunftCom\test\php\create\test_db_caches;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class db_cache_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $sc = new sql_creator();
        $t_db_cache = new test_db_caches($t);
        $t->name = 'db_cache->';
        $t->resource_path = 'db/db_cache/';

        $ts = 'unit db_cache ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $cac = new db_cache($usr);
        $t->assert_sql_table_create($cac);
        $t->assert_sql_index_create($cac);
        $t->assert_sql_foreign_key_create($cac);


        $t->subheader($ts . 'sql read');

        // sql to load one batch db_cache
        $cac = new db_cache($usr);
        $t->assert_sql_by_id($sc, $cac);

        // sql to load a list of open batch db_caches
        $t_usr = new test_users($t);
        $sys_usr = $t_usr->system_user();

        $t->subheader($ts . 'sql write');
        $cac = $t_db_cache->db_cache();
        // for db_cache a log is not needed because the table rows are never expected to be deleted
        $t->assert_sql_insert($sc, $cac);
        $cac = $t_db_cache->db_cache_filled();
        $db_cache_db = $cac->clone_reset();
        $t->assert_sql_update($sc, $cac, $db_cache_db);
        $t->assert_sql_delete($sc, $cac);

        $t->subheader($ts . 'api');

        $t_db_cache = new test_db_caches($t);
        $cac = $t_db_cache->db_cache();
        $t->assert_api($cac);

    }

}
