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
include_once paths::SHARED_TYPES . 'job_types.php';
include_once paths::SHARED_TYPES . 'job_statuum.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UNIT . 'sys_log_tests.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\system\job_list;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuum;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

class test_jobs
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env)
    {
        $this->env = $env;
    }


    /*
     * map
     */

    /**
     * @return job a batch job entry with some dummy values
     */
    function job(): job
    {
        $t_usr = new test_users();
        $sys_usr = $t_usr->system_user();
        $job = new job($sys_usr, new DateTime(sys_log_tests::TV_TIME));
        $job->id = 1;
        $job->set_type(job_types::BASE_IMPORT, $sys_usr);
        $job->start_time = new DateTime(sys_log_tests::TV_TIME);
        $job->priority = job_statuum::PRIO_HIGHEST;
        return $job;
    }

    /**
     * @return job a batch job entry with all fields set
     */
    function job_filled(): job
    {
        $t_usr = new test_users($this->env);
        $t_src = new test_sources($this->env);
        $t_ref = new test_refs($this->env);
        $sys_usr = $t_usr->system_user();
        $job = new job($sys_usr, new DateTime(sys_log_tests::TV_TIME));
        $job->id = 2;
        $job->set_type(job_types::BASE_IMPORT, $sys_usr);
        $job->set_status(job_types::BASE_IMPORT, $sys_usr);
        $job->start_time = new DateTime(sys_log_tests::TV_TIME);
        $job->end_time = new DateTime(sys_log_tests::TV_TIME_TWO);
        $job->parameter = '1';
        $job->change_field = 2;
        $job->row_id = 3;
        $job->src = $t_src->source_reserved();
        $job->ref = $t_ref->reference();
        $job->priority = job_statuum::PRIO_HIGHEST;
        return $job;
    }

    /**
     * @return job_list a list of batch job entries with some dummy values
     */
    function job_list(): job_list
    {
        $t_usr = new test_users($this->env);
        $sys_usr = $t_usr->system_user();
        $job_lst = new job_list($sys_usr);
        $job_lst->add($this->job());
        return $job_lst;
    }

    /**
     * fixture for admin_jobs_delayed and similar consumers; mixes open jobs with different request
     * times against one already-closed job so callers can verify both the "open only" filter and the
     * "longest delay first" ordering; the jobs are appended in non-sorted insertion order on purpose
     * so any caller that sorts by request_time has to actually do the work
     *
     * @return job_list two delayed (open, end_time null) jobs and one not-delayed (closed) job
     */
    function job_list_delayed(): job_list
    {
        $t_usr = new test_users($this->env);
        $sys_usr = $t_usr->system_user();
        $job_lst = new job_list($sys_usr);

        // delayed job, requested later and already started but never ended → shorter delay
        $job_newer = new job($sys_usr, new DateTime(sys_log_tests::TV_TIME_TWO));
        $job_newer->id = 2;
        $job_newer->set_type(job_types::BASE_IMPORT, $sys_usr);
        $job_newer->start_time = new DateTime(sys_log_tests::TV_TIME_ASSIGNED);
        $job_newer->priority = job_statuum::PRIO_LOWEST;
        $job_lst->add_obj($job_newer);

        // not-delayed job, end_time set → must be filtered out by admin_jobs_delayed
        $job_closed = new job($sys_usr, new DateTime(sys_log_tests::TV_TIME_ASSIGNED));
        $job_closed->id = 3;
        $job_closed->set_type(job_types::BASE_IMPORT, $sys_usr);
        $job_closed->start_time = new DateTime(sys_log_tests::TV_TIME_ASSIGNED);
        $job_closed->end_time = new DateTime(sys_log_tests::TV_TIME_CLOSED);
        $job_closed->priority = job_statuum::PRIO_HIGHEST;
        $job_lst->add_obj($job_closed);

        // delayed job, requested first and never started → longest delay; added last to exercise the sort
        $job_oldest = new job($sys_usr, new DateTime(sys_log_tests::TV_TIME));
        $job_oldest->id = 1;
        $job_oldest->set_type(job_types::BASE_IMPORT, $sys_usr);
        $job_oldest->priority = job_statuum::PRIO_HIGHEST;
        $job_lst->add_obj($job_oldest);

        return $job_lst;
    }

}