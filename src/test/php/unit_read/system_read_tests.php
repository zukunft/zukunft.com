<?php

/*

    test/php/unit_read/system.php - database unit testing of the system functions
    -----------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

include_once paths::MODEL_HELPER . 'type_lists.php';
include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::DB . 'db_check.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'job_types.php';
include_once paths::SHARED_ENUM . 'sys_log_statuum.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_lists;
use Zukunft\ZukunftCom\main\php\cfg\system\job_type_list;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status_list;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuum;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class system_read_tests
{

    function run(test_cleanup $t): void
    {

        global $sys;
        global $db_con;
        $msg = new user_message();

        // init
        $t->name = 'system read db->';

        // start the test section (ts)
        $ts = 'db read system ';
        $t->header($ts);

        $t->subheader($ts . 'error log');

        // load the log status list
        $lst = new sys_log_status_list();
        $result = $lst->load($db_con, $msg);
        $t->assert('load status', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $sys->typ_lst->sys_log_sta->id(sys_log_statuum::OPEN);
        $t->assert('check status ' . sys_log_statuum::OPEN, $result, 1);

        $t->subheader($ts . 'batch job type');

        // load the batch job type list
        $lst = new job_type_list();
        $result = $lst->load($db_con, $msg);
        $t->assert('load batch job', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $sys->typ_lst->job_typ->id(job_types::VALUE_UPDATE);
        $t->assert('check batch job ' . job_types::VALUE_UPDATE, $result, 1);

        $t->subheader($ts . 'type list cached load');

        // the type lists load with one read from the cached types json
        // or if the cache is missing or outdated with one select per type list
        $typ_lst = new type_lists();
        $test_name = 'the type lists load from the types cache or fresh from the database';
        $t->assert_true($test_name, $typ_lst->load_cached($db_con, $msg));
        $test_name = 'the cached or fresh load fills the phrase types';
        $t->assert_false($test_name, $typ_lst->phr_typ->is_empty());
        $test_name = 'the cached or fresh load fills the verbs';
        $t->assert_false($test_name, $typ_lst->vrb->is_empty());

        /*
         * SQL database read unit tests
         */

        $t->subheader($ts . 'SQL database read');

        $t->assert_greater_zero('sql_db->count', $db_con->count(formula::class, $msg));

        /*
         * SQL database consistency tests
         */

        $t->subheader($ts . 'SQL database consistency');

        $result = $db_con->db_check_missing_owner($msg);
        $t->assert('db_consistency->check ', $result, true);

    }

}

