<?php

/*

    test/unit_db/system.php - database unit testing of the system functions
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

class system_unit_db_tests
{

    function run(testing $t): void
    {

        global $usr;
        global $db_con;

        // init
        $t->name = 'system read db->';

        $t->header('Unit database tests of the system functions');

        $t->subheader('System error log tests');

        // load the log status list
        $lst = new sys_log_status();
        $result = $lst->load($db_con);
        $t->assert('load status', $result, true);

        // ... and check if at least the most critical is loaded
        $result = cl(db_cl::LOG_STATUS, sys_log_status::NEW);
        $t->assert('check status ' . sys_log_status::NEW, $result, 1);

        $t->subheader('System batch job type tests');

        // load the batch job type list
        $lst = new job_type_list();
        $result = $lst->load($db_con);
        $t->assert('load batch job', $result, true);

        // ... and check if at least the most critical is loaded
        $result = cl(db_cl::JOB_TYPE, job_type_list::VALUE_UPDATE);
        $t->assert('check batch job ' . job_type_list::VALUE_UPDATE, $result, 1);

        /*
         * SQL database read unit tests
         */

        $t->subheader('SQL database read tests');

        $t->assert_greater_zero('sql_db->count', $db_con->count(sql_db::TBL_FORMULA));

        /*
         * SQL database consistency tests
         */

        $t->subheader('SQL database consistency tests');

        $result = db_check_missing_owner($db_con);
        $t->assert('db_consistency->check ', $result, true);

        $t->subheader('API unit db tests of preloaded types');
        $sys_typ_lst = new type_lists($usr);
        $sys_typ_lst->load($db_con, $usr);
        $t->assert_api($sys_typ_lst);

    }

}

