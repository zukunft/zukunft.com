<?php

/*

    test/create/test_admin_dashboard.php - create the given data of the admin dashboard columns
    ------------------------------------

    the three data sets of the admin dashboard: an empty dashboard, a dashboard with some critical
    entries and not critical entries in every column, and a dashboard with critical and not critical
    entries in every column; each column has its own entries, because each column has its own rule
    what is critical (see web/component/execute/system_page::admin_*)


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_SYSTEM . 'job.php';
include_once paths::MODEL_SYSTEM . 'job_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::MODEL_SYSTEM . 'sys_log_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SYSTEM . 'job_list.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
include_once html_paths::SYSTEM . 'system_time_list.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_ENUM . 'sys_log_functions.php';
include_once paths::SHARED_ENUM . 'sys_log_levels.php';
include_once paths::SHARED_ENUM . 'sys_log_statuum.php';
include_once paths::SHARED_TYPES . 'job_statuum.php';
include_once paths::SHARED_TYPES . 'job_types.php';
include_once test_paths::CREATE . 'test_objects.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UNIT . 'sys_log_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\system\job_list;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_ui;
use Zukunft\ZukunftCom\main\php\web\system\job_list as job_list_ui;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list as sys_log_list_ui;
use Zukunft\ZukunftCom\main\php\web\system\system_time_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_functions;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_levels;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuum;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuum;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use DateTime;

class test_admin_dashboard extends test_objects
{

    // the time of the data snap of all three data sets, which decides if a job is delayed
    const string SNAP_TIME = sys_log_tests::TV_TIME_CLOSED;

    // the texts of the system errors, one per row, so that a test can find each row on the page
    const string ERR_FATAL_TEXT = 'the database connection has been lost during the import of a value list';
    const string ERR_ERROR_TEXT = 'the formula result of a value cannot be saved';
    const string ERR_WARNING_TEXT = 'a phrase of an imported value has no description';
    const string ERR_INFO_TEXT = 'the import of a value list has taken longer than expected';
    const string FIX_ERROR_TEXT = 'the view of a triple shows the reverse verb name';
    const string FIX_WARNING_TEXT = 'the tooltip of a component is missing';

    // the typed names of the failed logins
    const string LOGIN_ATTACKED = 'admin';
    const string LOGIN_TYPO = 'test_user_typo';

    // the urls of the request times
    const string URL_SLOW = '/http/view.php?m=129';
    const string URL_SLOWER = '/api/valueList/?ids=1,2,3';
    const string URL_FAST = '/http/view.php?m=1';


    /*
     * data sets
     */

    /**
     * @param data_object_ui $base the context of the unit test with the type and view cache
     * @return data_object_ui a context without any entry for the admin dashboard
     */
    function dto_empty(data_object_ui $base): data_object_ui
    {
        return $this->dto($base);
    }

    /**
     * some columns have a critical entry, but every column has a not critical entry
     *
     * @param data_object_ui $base the context of the unit test with the type and view cache
     * @param user_message_ui $msg to report the mapping problems of the frontend lists
     * @return data_object_ui a context with some critical and with not critical entries in all columns
     */
    function dto_mixed(data_object_ui $base, user_message_ui $msg): data_object_ui
    {
        $dto = $this->dto($base);
        $logs = [
            // unassigned errors: one critical error and one info
            $this->log(1, sys_log_levels::ERROR, sys_log_statuum::OPEN, self::ERR_ERROR_TEXT),
            $this->log(2, sys_log_levels::INFO, sys_log_statuum::OPEN, self::ERR_INFO_TEXT),
            // pending error fixes: only a resolved warning
            $this->log(3, sys_log_levels::WARNING, sys_log_statuum::RESOLVED, self::FIX_WARNING_TEXT, true),
        ];
        // failed logins: only one typo
        $logs[] = $this->login_fail(4, self::LOGIN_TYPO);
        $dto->adm_sys_log = $this->sys_log_ui($logs, $msg);
        // response times: one slow and one fast request
        $limit = system_time_list::critical_limit($msg);
        $dto->adm_sys_tim_lst = $this->times([
            [1, self::URL_SLOW, $limit + 500],
            [2, self::URL_FAST, intdiv($limit, 10)],
        ], $msg);
        // delayed jobs: only a job that is waiting less than the max delay
        $dto->adm_job_lst = $this->jobs(false);
        return $dto;
    }

    /**
     * @param data_object_ui $base the context of the unit test with the type and view cache
     * @param user_message_ui $msg to report the mapping problems of the frontend lists
     * @return data_object_ui a context with critical and with not critical entries in all columns
     */
    function dto_critical(data_object_ui $base, user_message_ui $msg): data_object_ui
    {
        $dto = $this->dto($base);
        $logs = [
            // unassigned errors: a fatal error, an error, a warning and an info
            $this->log(1, sys_log_levels::FATAL, sys_log_statuum::OPEN, self::ERR_FATAL_TEXT),
            $this->log(2, sys_log_levels::ERROR, sys_log_statuum::OPEN, self::ERR_ERROR_TEXT),
            $this->log(3, sys_log_levels::WARNING, sys_log_statuum::OPEN, self::ERR_WARNING_TEXT),
            $this->log(4, sys_log_levels::INFO, sys_log_statuum::OPEN, self::ERR_INFO_TEXT),
            // pending error fixes: an assigned error and a resolved warning
            $this->log(5, sys_log_levels::ERROR, sys_log_statuum::ASSIGNED, self::FIX_ERROR_TEXT, true),
            $this->log(6, sys_log_levels::WARNING, sys_log_statuum::RESOLVED, self::FIX_WARNING_TEXT, true),
        ];
        // failed logins: as many tries of the admin as the limit and one typo
        $id = 7;
        for ($i = 0; $i < sys_log_list_ui::login_fail_limit($msg); $i++) {
            $logs[] = $this->login_fail($id, self::LOGIN_ATTACKED, $i);
            $id++;
        }
        $logs[] = $this->login_fail($id, self::LOGIN_TYPO);
        $dto->adm_sys_log = $this->sys_log_ui($logs, $msg);
        // response times: two slow and one fast request
        $limit = system_time_list::critical_limit($msg);
        $dto->adm_sys_tim_lst = $this->times([
            [1, self::URL_SLOW, $limit + 500],
            [2, self::URL_SLOWER, $limit * 3],
            [3, self::URL_FAST, intdiv($limit, 10)],
        ], $msg);
        // delayed jobs: a job that is waiting longer than the max delay and one that is waiting less
        $dto->adm_job_lst = $this->jobs(true);
        return $dto;
    }


    /*
     * internal
     */

    /**
     * @param data_object_ui $base the context of the unit test with the type and view cache
     * @return data_object_ui a new context with the caches of the base and the fixed time of the data snap
     */
    private function dto(data_object_ui $base): data_object_ui
    {
        $dto = new data_object_ui();
        $dto->online = false;
        $dto->typ_lst_cache = $base->typ_lst_cache;
        $dto->msk_lst = $base->msk_lst;
        $dto->snap_time = new DateTime(self::SNAP_TIME);
        return $dto;
    }

    /**
     * @param int $id the unique id of the log entry within the data set
     * @param string $level the code id of the level e.g. sys_log_levels::ERROR
     * @param string $status the code id of the status e.g. sys_log_statuum::OPEN
     * @param string $text the log text that names the problem
     * @param bool $with_solver true if the admin has taken the error
     * @return sys_log a backend system log entry
     */
    private function log(int $id, string $level, string $status, string $text, bool $with_solver = false): sys_log
    {
        global $sys;
        $slg = new sys_log();
        $slg->id = $id;
        $slg->usr = $this->env->usr1;
        // a higher id is a newer entry, so the order of entries with the same level is fixed
        $slg->log_time = new DateTime(sys_log_tests::TV_TIME);
        $slg->log_time->modify('+' . $id . ' minutes');
        $slg->function_id = $sys->typ_lst->sys_log_fnc->id(sys_log_functions::IMPORT_BASE_CONFIG);
        $slg->level_id = $sys->typ_lst->sys_log_lvl->id($level);
        $slg->log_trace = sys_log_tests::TV_LOG_TRACE;
        $slg->log_text = $text;
        $slg->status_id = $sys->typ_lst->sys_log_sta->id($status);
        if ($with_solver) {
            $slg->solver = $this->env->usr_admin;
            $slg->update_time = new DateTime(sys_log_tests::TV_TIME_ASSIGNED);
            $slg->update_time->modify('+' . $id . ' minutes');
        }
        return $slg;
    }

    /**
     * @param int $id the unique id of the log entry within the data set
     * @param string $name the typed user name
     * @param int $try the number of the try of the same name, so that each try has its own time
     * @return sys_log a backend system log entry of a failed login as user::login writes it
     */
    private function login_fail(int $id, string $name, int $try = 0): sys_log
    {
        global $sys;
        $slg = new sys_log();
        $slg->id = $id;
        $slg->usr = $this->env->usr_system;
        $slg->log_time = new DateTime(sys_log_tests::TV_TIME_TWO);
        $slg->log_time->modify('+' . $try . ' seconds');
        $slg->function_id = $sys->typ_lst->sys_log_fnc->id(sys_log_functions::USER_LOGIN_FAILED);
        $slg->level_id = $sys->typ_lst->sys_log_lvl->id(sys_log_levels::WARNING);
        $slg->log_trace = sys_log_tests::TV_LOG_TRACE;
        $slg->log_text = sys_log_functions::LOGIN_FAILED_TEXT;
        $slg->log_description = $name;
        $slg->status_id = $sys->typ_lst->sys_log_sta->id(sys_log_statuum::OPEN);
        return $slg;
    }

    /**
     * @param array $logs the backend system log entries
     * @param user_message_ui $msg to report the mapping problems of the frontend list
     * @return sys_log_list_ui the entries as the frontend receives them from the api
     */
    private function sys_log_ui(array $logs, user_message_ui $msg): sys_log_list_ui
    {
        $lst = new sys_log_list();
        foreach ($logs as $slg) {
            $lst->add($slg);
        }
        $lst_ui = new sys_log_list_ui();
        $lst_ui->set_from_json($lst->api_json(), $msg);
        return $lst_ui;
    }

    /**
     * @param array $rows one array per request with the id, the url and the milliseconds
     * @param user_message_ui $msg to report the mapping problems of the frontend list
     * @return system_time_list the request times as the frontend receives them
     */
    private function times(array $rows, user_message_ui $msg): system_time_list
    {
        $json = [];
        foreach ($rows as [$id, $url, $milliseconds]) {
            $json[] = [
                json_fields::ID => $id,
                json_fields::TIME_START => sys_log_tests::TV_TIME,
                json_fields::URL => $url,
                json_fields::MILLISECONDS => $milliseconds,
            ];
        }
        $lst = new system_time_list();
        $lst->set_from_json_array($json, $msg);
        return $lst;
    }

    /**
     * @param bool $with_delayed true to add a job that is waiting longer than the max delay
     * @return job_list_ui the open jobs as the frontend receives them from the api
     */
    private function jobs(bool $with_delayed): job_list_ui
    {
        $t_usr = new test_users($this->env);
        $sys_usr = $t_usr->system_user();
        $job_lst = new job_list($sys_usr);
        // requested a minute before the data snap, so it waits less than any max delay above a minute
        $fresh_time = new DateTime(self::SNAP_TIME);
        $fresh_time->modify('-1 minute');
        $job_lst->add_obj($this->job($sys_usr, 1, $fresh_time, job_statuum::PRIO_LOWEST));
        if ($with_delayed) {
            // requested more than a day before the data snap and never started
            $job_lst->add_obj($this->job($sys_usr, 2, new DateTime(sys_log_tests::TV_TIME), job_statuum::PRIO_HIGHEST));
        }
        return new job_list_ui($job_lst->api_json());
    }

    /**
     * @param user $sys_usr the system user who has requested the job
     * @param int $id the unique id of the job within the data set
     * @param DateTime $request_time the time of the job request
     * @param int $priority the priority of the job
     * @return job an open batch job
     */
    private function job(user $sys_usr, int $id, DateTime $request_time, int $priority): job
    {
        $job = new job($sys_usr, $request_time);
        $job->id = $id;
        $job->set_type(job_types::BASE_IMPORT, $sys_usr);
        $job->priority = $priority;
        return $job;
    }

}
