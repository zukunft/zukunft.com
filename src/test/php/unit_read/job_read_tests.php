<?php

/*

    test/php/unit_read/job.php - database unit testing of the batch job functions
    --------------------------


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

use Zukunft\ZukunftCom\main\php\cfg\system\job_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class job_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;

        // init
        $t->name = 'batch job read db->';

        // start the test section (ts)
        $ts = 'db read job ';
        $t->header($ts);

        $t->subheader($ts . 'load batch');

        // use the system user for the database updates
        $sys_usr = new user;
        $sys_usr->load_by_id(users::SYSTEM_ID);

        $test_name = 'check if at least one batch job has the base import id ' . job_types::BASE_IMPORT_ID;
        $job_lst = new job_list($sys_usr);
        $job_lst->load_by_type(job_types::BASE_IMPORT);
        $first_job = $job_lst->lst()[0];
        $t->assert($test_name, $first_job->type_id(), job_types::BASE_IMPORT_ID);


        $t->subheader($ts . 'api');

        $t->assert_api($job_lst);

    }

}

