<?php

/*

    test/unit/batch_log.php - unit testing of the user log functions
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

namespace test;

include_once MODEL_SYSTEM_PATH . 'batch_job_list.php';

use api\word\triple as triple_api;
use cfg\batch_job_type_list;
use cfg\batch_job;
use cfg\batch_job_list;
use cfg\sql_db;

class batch_job_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('Unit tests of the batch job class (src/main/php/log/batch_job.php)');

        $t->subheader('SQL statement tests');

        // init
        $db_con = new sql_db();
        $t->name = 'batch_job->';
        $t->resource_path = 'db/job/';

        // sql to load one batch job
        $job = new batch_job($usr);
        $t->assert_sql_by_id($db_con, $job);

        // sql to load a list of open batch jobs
        $sys_usr = $t->system_user();
        $job_lst = new batch_job_list($sys_usr);
        $t->assert_sql_list_by_type($db_con, $job_lst, batch_job_type_list::BASE_IMPORT);


        $t->subheader('API unit tests');

        $job = $t->dummy_job();
        $t->assert_api($job);

        $job_lst = $t->dummy_job_list();
        $t->assert_api($job_lst);

    }

}
