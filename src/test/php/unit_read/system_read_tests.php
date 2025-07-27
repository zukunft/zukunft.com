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

namespace unit_read;

use cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::DB . 'db_check.php';
include_once paths::SHARED_TYPES . 'api_type.php';
include_once paths::SHARED_ENUM . 'sys_log_statuus.php';

use cfg\formula\formula;
use cfg\system\job_type_list;
use cfg\system\sys_log_status_list;
use cfg\helper\type_lists;
use shared\enum\sys_log_statuus;
use shared\types\api_type;
use test\test_cleanup;

class system_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $sys_log_sta_cac;

        // init
        $t->name = 'system read db->';

        $t->header('Unit database tests of the system functions');

        $t->subheader('System error log tests');

        // load the log status list
        $lst = new sys_log_status_list();
        $result = $lst->load($db_con);
        $t->assert('load status', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $sys_log_sta_cac->id(sys_log_statuus::OPEN);
        $t->assert('check status ' . sys_log_statuus::OPEN, $result, 1);

        $t->subheader('System batch job type tests');

        // load the batch job type list
        $lst = new job_type_list();
        $result = $lst->load($db_con);
        $t->assert('load batch job', $result, true);

        // ... and check if at least the most critical is loaded
        global $job_typ_cac;
        $result = $job_typ_cac->id(job_type_list::VALUE_UPDATE);
        $t->assert('check batch job ' . job_type_list::VALUE_UPDATE, $result, 1);

        /*
         * SQL database read unit tests
         */

        $t->subheader('SQL database read tests');

        $t->assert_greater_zero('sql_db->count', $db_con->count(formula::class));

        /*
         * SQL database consistency tests
         */

        $t->subheader('SQL database consistency tests');

        $result = $db_con->db_check_missing_owner();
        $t->assert('db_consistency->check ', $result, true);

        $t->subheader('API unit db tests of preloaded types');
        $sys_typ_lst = new type_lists();
        $sys_typ_lst->load($db_con, $t->usr1);
        $t->assert_api($sys_typ_lst, '', [api_type::HEADER]);

    }

}

