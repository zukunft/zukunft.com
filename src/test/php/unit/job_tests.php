<?php

/*

    test/unit/job_tests.php - unit testing of the batch task functions
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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_SYSTEM . 'job_list.php';
include_once paths::MODEL_SYSTEM . 'job_runner.php';
include_once paths::SHARED_TYPES . 'job_statuum.php';
include_once paths::SHARED_TYPES . 'job_types.php';
include_once test_paths::UNIT . 'sys_log_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\cfg\system\job_time;
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\system\job_list;
use Zukunft\ZukunftCom\main\php\cfg\system\job_runner;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuum;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\test\php\create\test_jobs;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

class job_tests
{
    function run(test_cleanup $t): void
    {
        global $sys;


        // init
        $sc = new sql_creator();
        $t_job = new test_jobs($t);
        $t->name = 'job->';
        $t->resource_path = 'db/job/';

        $ts = 'unit job ';
        $t->header($ts);

        $t->subheader($ts . 'time sql setup');
        $job_tim = new job_time('');
        $t->assert_sql_table_create($job_tim);
        $t->assert_sql_index_create($job_tim);
        $t->assert_sql_foreign_key_create($job_tim);

        $t->subheader($ts . 'sql setup');
        $job = new job($t->usr1);
        $t->assert_sql_table_create($job);
        $t->assert_sql_index_create($job);
        $t->assert_sql_foreign_key_create($job);


        $t->subheader($ts . 'sql read');

        // sql to load one batch job
        $job = new job($t->usr1);
        $t->assert_sql_by_id($sc, $job);

        // sql to load a list of open batch jobs
        $t_usr = new test_users($t);
        $sys_usr = $t_usr->system_user();
        $job_lst = new job_list($sys_usr);
        $t->assert_sql_list_by_type($sc, $job_lst, job_types::BASE_IMPORT);

        // sql to load the jobs of one user and the jobs of all users
        $sc->reset(sql_db::POSTGRES);
        $t->assert_qp($job_lst->load_sql_by_user($sc), $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $t->assert_qp($job_lst->load_sql_by_user($sc), $sc->db_type);
        $sc->reset(sql_db::POSTGRES);
        $t->assert_qp($job_lst->load_sql_all($sc), $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $t->assert_qp($job_lst->load_sql_all($sc), $sc->db_type);

        $t->subheader($ts . 'sql write');
        $job = $t_job->job();
        // for job a log is not needed because the table rows are never expected to be deleted
        $t->assert_sql_insert($sc, $job);
        $job = $t_job->job_filled();
        $job_db = $job->clone_reset();
        $t->assert_sql_update($sc, $job, $job_db);

        $t->subheader($ts . 'api');

        $t_job = new test_jobs($t);
        $job = $t_job->job();
        $job->priority = job_statuum::PRIO_HIGHEST;
        $t->assert_api($job);

        $job_lst = $t_job->job_list();
        $t->assert_api($job_lst);


        // the user who has requested a job can downgrade or cancel it, an admin can also upgrade it
        $t->subheader($ts . 'priority change');
        $usr_normal = $t_usr->user_sys_normal();
        $usr_admin = $t_usr->user_sys_admin();
        $chg_msg = new user_message();

        $test_name = 'the user who has requested the job can downgrade it';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->apply_change(url_var::ACTION_JOB_DOWNGRADE, $usr_normal, $chg_msg);
        $t->assert_true($test_name, $chg_msg->is_ok());
        $chg_msg->reset();
        $test_name = '... which reduces the priority by one';
        $t->assert($test_name, $job->priority, job_statuum::PRIO_HIGHEST - 1);

        $test_name = 'the user who has requested the job cannot upgrade it';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->apply_change(url_var::ACTION_JOB_UPGRADE, $usr_normal, $chg_msg);
        $t->assert_false($test_name, $chg_msg->is_ok());
        $chg_msg->reset();

        $test_name = 'another user who is not an admin cannot cancel the job';
        $job = $t_job->job();
        $job->owner = $usr_admin;
        $job->apply_change(url_var::ACTION_JOB_CANCEL, $usr_normal, $chg_msg);
        $t->assert_false($test_name, $chg_msg->is_ok());
        $chg_msg->reset();

        $test_name = 'an admin can upgrade the job of another user';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->priority = job_statuum::PRIO_LOWEST;
        $job->apply_change(url_var::ACTION_JOB_UPGRADE, $usr_admin, $chg_msg);
        $t->assert_true($test_name, $chg_msg->is_ok());
        $chg_msg->reset();
        $test_name = '... which increases the priority by one';
        $t->assert($test_name, $job->priority, job_statuum::PRIO_LOWEST + 1);

        $test_name = 'the priority of a job with the highest priority is not increased further';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->apply_change(url_var::ACTION_JOB_UPGRADE, $usr_admin, $chg_msg);
        $t->assert($test_name, $job->priority, job_statuum::PRIO_HIGHEST);
        $chg_msg->reset();

        $test_name = 'a cancelled job gets an end time';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->apply_change(url_var::ACTION_JOB_CANCEL, $usr_normal, $chg_msg);
        $t->assert_true($test_name, $job->end_time != null);
        $chg_msg->reset();

        $test_name = 'an admin can cancel the job of another user';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->apply_change(url_var::ACTION_JOB_CANCEL, $usr_admin, $chg_msg);
        $t->assert_true($test_name, $chg_msg->is_ok());
        $chg_msg->reset();

        $test_name = 'an unknown job action is reported';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->apply_change(url_var::ACTION_CANCEL, $usr_normal, $chg_msg);
        $t->assert_false($test_name, $chg_msg->is_ok());
        $chg_msg->reset();

        $test_name = 'a job with a completed status cannot be changed';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->status_id = $sys->typ_lst->job_sta->id(job_statuum::STATUS_DONE);
        $job->apply_change(url_var::ACTION_JOB_CANCEL, $usr_normal, $chg_msg);
        $t->assert_false($test_name, $chg_msg->is_ok());
        $chg_msg->reset();

        $test_name = 'a job with the default priority zero is not raised by a downgrade';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->priority = 0;
        $job->apply_change(url_var::ACTION_JOB_DOWNGRADE, $usr_normal, $chg_msg);
        $t->assert($test_name, $job->priority, 0);
        $chg_msg->reset();

        $test_name = 'a job without a database id cannot be changed';
        $job = $t_job->job();
        $job->owner = $usr_admin;
        $job->id = 0;
        $job->apply_change(url_var::ACTION_JOB_UPGRADE, $usr_admin, $chg_msg);
        $t->assert_false($test_name, $chg_msg->is_ok());
        $chg_msg->reset();

        $test_name = 'the job list of an admin shows the user who has requested the job';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job_json = $job->api_json_array([], $chg_msg);
        $t->assert($test_name, $job_json[json_fields::USER_NAME], $usr_normal->name());
        $chg_msg->reset();

        $test_name = 'a completed job cannot be changed anymore';
        $job = $t_job->job();
        $job->owner = $usr_normal;
        $job->end_time = new DateTime(sys_log_tests::TV_TIME_CLOSED);
        $job->apply_change(url_var::ACTION_JOB_DOWNGRADE, $usr_normal, $chg_msg);
        $t->assert_false($test_name, $chg_msg->is_ok());
        $chg_msg->reset();


        $t->subheader($ts . 'due job selection');

        $sys_usr = new test_users($t)->system_user();
        $job_run = new job_runner($sys_usr);
        $now = new DateTime(sys_log_tests::TV_TIME_ASSIGNED);

        // new and past-due jobs are returned in descending priority order,
        // while a not-new job and a future job are filtered out
        $test_name = 'due jobs are returned in priority order';
        $jobs = $t_job->job_list_due_mix();
        $due = $job_run->due_jobs($jobs, $now);
        $due_ids = array_map(fn(job $due_job) => $due_job->id(), $due);
        $t->assert($test_name, implode(',', $due_ids), '12,11');

        $test_name = 'a job that is not new is not due';
        $t->assert_false($test_name, in_array(15, $due_ids));

        $test_name = 'a job scheduled in the future is not yet due';
        $t->assert_false($test_name, in_array(14, $due_ids));

        // a priority tie is broken by the request time (oldest first)
        $test_name = 'due jobs with the same priority run oldest first';
        $jobs = $t_job->job_list_due_same_priority();
        $due = $job_run->due_jobs($jobs, $now);
        $due_ids = array_map(fn(job $due_job) => $due_job->id(), $due);
        $t->assert($test_name, implode(',', $due_ids), '22,21');

        // an empty job list has no due jobs
        $test_name = 'an empty job list has no due jobs';
        $empty_lst = new job_list($sys_usr);
        $due = $job_run->due_jobs($empty_lst, $now);
        $t->assert_true($test_name, count($due) == 0);

    }

}
