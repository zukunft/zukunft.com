<?php

/*

    test/unit/html/job.php - testing of the batch task display functions
    ----------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::SYSTEM . 'job_list.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuum;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\system\job;
use Zukunft\ZukunftCom\main\php\web\system\job_list;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\create\test_jobs;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class job_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;
        $html = new html_base();
        $t_job = new test_jobs($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html batch job ';
        $t->header($ts);

        // test the batch job html display functions
        $test_page = $html->text_h2('batch job display test');
        $job_lst = new job_list($t_job->job_list()->api_json());
        $test_page .= 'user view of a table with batch job entries<br>';
        $test_page .= $job_lst->display() . '<br>';

        // test the SYSTEM_ADMIN_JOBS_DELAYED component-type renderer (system_page::admin_jobs_delayed)
        $page = new system_page();
        $job_lst = new job_list($t_job->job_list_delayed()->api_json());
        $test_page .= $html->text_h2('admin_jobs_delayed test');
        $job_msg = new user_message();
        $test_page .= 'empty job list shows the no-open-jobs notice<br>';
        $test_page .= $page->admin_jobs_delayed($job_msg, true) . '<br>';
        $test_page .= 'job list with one open batch job sorted by request_time ascending<br>';
        $test_page .= $page->admin_jobs_delayed($job_msg, true, $job_lst) . '<br>';

        // test the SYSTEM_BODY_USER_JOBS and SYSTEM_BODY_ALL_JOBS renderers (system_page::user_jobs and all_jobs)
        $t_usr = new test_users($t);
        $msg_usr = new user_message();
        $msg_usr->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $msg_adm = new user_message();
        $msg_adm->usr = new user_ui($t_usr->user_sys_admin()->api_json());
        // the jobs of a user and of all users have one form per job each, so they are two pages
        // like in the frontend, because one page must not hold two forms with the same id
        $usr_page = $html->text_h2('user jobs test');
        $usr_page .= 'the jobs of a user without the upgrade button<br>';
        $usr_page .= $page->user_jobs($msg_usr, 0, true, $job_lst) . '<br>';
        $t->html_page_test($usr_page, 'user jobs', 'job_user', $msg);
        $test_page .= $html->text_h2('all jobs test');
        $test_page .= 'the jobs of all users for an admin<br>';
        $test_page .= $page->all_jobs($msg_adm, 0, true, $job_lst) . '<br>';
        $test_page .= 'the hint for a user who is not an admin<br>';
        $test_page .= $page->all_jobs($msg_usr, 0, true, $job_lst) . '<br>';

        $t->html_page_test($test_page, 'job', 'job', $msg);

        // the job lists of the user and of all users show the open jobs on top with the buttons to change them
        $t->subheader($ts . 'job list with actions');
        $cancel_prefix = job::ACTION_FORM_PREFIX . url_var::ACTION_JOB_CANCEL . '_';
        $upgrade_prefix = job::ACTION_FORM_PREFIX . url_var::ACTION_JOB_UPGRADE . '_';
        $test_name = 'an open job has the cancel button';
        $user_html = $job_lst->display_with_actions(false, 0);
        $t->assert_text_contains($test_name, $user_html, $cancel_prefix . '1');
        $test_name = 'a completed job has no cancel button';
        $t->assert_text_not_contains($test_name, $user_html, $cancel_prefix . '3');
        $test_name = 'a user who is not an admin has no upgrade button';
        $t->assert_text_not_contains($test_name, $user_html, $upgrade_prefix . '1');
        $test_name = 'the newer open job is shown before the older open job';
        $t->assert_text_order($test_name, $user_html, $cancel_prefix . '2', $cancel_prefix . '1');
        $test_name = 'a job button confirms the job change directly';
        $t->assert_text_contains($test_name, $user_html, $html->form_hidden(url_var::STEP, url_var::STEP_CONFIRMED));
        $test_name = 'an admin has the upgrade button';
        $admin_html = $job_lst->display_with_actions(true, 0);
        $t->assert_text_contains($test_name, $admin_html, $upgrade_prefix . '1');
        $test_name = 'a user who is not an admin gets the hint instead of the jobs of all users';
        $no_adm_html = $page->all_jobs($msg_usr, 0, true, $job_lst);
        $t->assert_text_contains($test_name, $no_adm_html, $mtr->txt(msg_id::JOB_LIST_ALL_ONLY_ADMIN));
        $test_name = 'an admin gets the jobs of all users with the upgrade button';
        $adm_html = $page->all_jobs($msg_adm, 0, true, $job_lst);
        $t->assert_text_contains($test_name, $adm_html, $upgrade_prefix . '1');
        $test_name = 'the jobs of a user have no upgrade button';
        $usr_html = $page->user_jobs($msg_usr, 0, true, $job_lst);
        $t->assert_text_not_contains($test_name, $usr_html, $upgrade_prefix . '1');
        // negative: a user without a job gets the hint instead of an empty table
        $test_name = 'a user without a job gets the no jobs hint';
        $t->assert_text_contains($test_name, $page->user_jobs($msg_usr, 0, true, new job_list()), $mtr->txt(msg_id::INFO_NO_JOBS));
        $test_name = 'the open jobs are on top of the completed job';
        $open_first = array_map(fn(job $job) => $job->id(), $job_lst->pending_first());
        $t->assert($test_name, implode(',', $open_first), '2,1,3');
        // negative: a user without a job has no job to put on top
        $test_name = 'an empty job list has no open job on top';
        $no_job_lst = new job_list();
        $no_open_first = $no_job_lst->pending_first();
        $t->assert($test_name, $no_open_first, []);

        $t->subheader($ts . 'api mapper');

        $test_name = 'the job mapper sets the priority';
        $job = new job();
        $job->api_mapper(json_decode($t_job->job()->api_json(), true), $msg);
        $t->assert($test_name, $job->priority(), job_statuum::PRIO_HIGHEST);
        $msg->reset();

        // negative: a job json without a priority leaves the job without priority
        $test_name = 'the job mapper sets no priority if the json has none';
        $api_json = json_decode($t_job->job()->api_json(), true);
        unset($api_json[json_fields::PRIORITY]);
        $job = new job();
        $job->api_mapper($api_json, $msg);
        $job_prio = $job->priority();
        $t->assert($test_name, $job_prio, 0);
        $msg->reset();

        // negative: the id is mandatory, so a job json without it is reported
        $test_name = 'the job mapper reports a json without id';
        $api_json = json_decode($t_job->job()->api_json(), true);
        unset($api_json[json_fields::ID]);
        $job = new job();
        $t->assert_false($test_name, $job->api_mapper($api_json, $msg));
        $msg->reset();

        $test_name = 'the job list mapper adds the jobs';
        $job_lst = new job_list();
        $job_lst->api_mapper(json_decode($t_job->job_list()->api_json(), true));
        $t->assert($test_name, $job_lst->count(), $t_job->job_list()->count());

        // negative: a job without id is reported via the returned message
        $test_name = 'the job list mapper reports a job without id';
        $api_json = json_decode($t_job->job_list()->api_json(), true);
        unset($api_json[0][json_fields::ID]);
        $job_lst = new job_list();
        $t->assert_false($test_name, $job_lst->api_mapper($api_json)->is_ok());
    }

}