<?php

/*

    test/create/test_jobs.php - create the test jobs entries
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_SYSTEM . 'job.php';
include_once paths::MODEL_SYSTEM . 'job_list.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UNIT . 'sys_log_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\system\job_list;
use Zukunft\ZukunftCom\main\php\cfg\system\job_type_list;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use DateTime;

class test_jobs
{

    /**
     * @return job a batch job entry with some dummy values
     */
    function job(): job
    {
        $t_usr = new test_users();
        $sys_usr = $t_usr->system_user();
        $job = new job($sys_usr, new DateTime(sys_log_tests::TV_TIME));
        $job->id = 1;
        $job->start_time = new DateTime(sys_log_tests::TV_TIME);
        $job->set_type(job_type_list::BASE_IMPORT, $sys_usr);
        return $job;
    }

    /**
     * @return job_list a list of batch job entries with some dummy values
     */
    function job_list(): job_list
    {
        $t_usr = new test_users();
        $sys_usr = $t_usr->system_user();
        $job_lst = new job_list($sys_usr);
        $job_lst->add($this->job());
        return $job_lst;
    }

}