<?php

/*

    web/system/job_list.php - the display extension of the system error log api object
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

namespace Zukunft\ZukunftCom\main\php\web\system;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::SYSTEM . 'job.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\sandbox\ListBase;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class job_list extends ListBase
{

    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api json array
     * TODO can be moved to list_dsp as soon as all list api message include the header
     * @param array $json_array an api list json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        // TODO Prio 3 activate
        //$ctrl = new controller();
        //$json_array = $ctrl->check_api_msg($json_array, controller::API_BODY_SYS_LOG);
        return parent::api_mapper_list($json_array, new job());
    }


    /*
     * load
     */

    /**
     * load the jobs of the requesting user via the api
     * @param user_message $msg to report a problem of the api call
     * @return bool true if at least one job has been found
     */
    function load_by_user(user_message $msg): bool
    {
        $rest = new rest_call();
        $json_body = $rest->api_get(self::class, $this->user_data($msg));
        return $this->api_load($json_body, $msg);
    }

    /**
     * load the jobs of all users via the api, which refuses the request of a user who is not an admin
     * @param user_message $msg to report a problem of the api call e.g. that only an admin can see all jobs
     * @return bool true if at least one job has been found
     */
    function load_all(user_message $msg): bool
    {
        $rest = new rest_call();
        $data = $this->user_data($msg);
        $data[url_var::JOB_LIST_ALL] = url_var::TRUE;
        $json_body = $rest->api_get(self::class, $data);
        return $this->api_load($json_body, $msg);
    }

    /**
     * @param user_message $msg with the requesting user
     * @return array the api request data with the requesting user, for whom the backend loads the jobs
     */
    private function user_data(user_message $msg): array
    {
        $data = [];
        if ($msg->usr != null) {
            $data[url_var::USER] = $msg->usr->id();
        }
        return $data;
    }

    /**
     * @param array $json_body the api answer with the jobs or the message why the jobs are not sent
     * @param user_message $msg to report the api message
     * @return bool true if at least one job has been found
     */
    private function api_load(array $json_body, user_message $msg): bool
    {
        if (array_key_exists(json_fields::MSG, $json_body)) {
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json_body[json_fields::MSG]]);
        } else {
            $msg->merge($this->api_mapper($json_body));
        }
        return !$this->is_empty();
    }


    /*
     * display
     */

    /**
     * @return string with a table of the batch job entries for users
     */
    function display(): string
    {
        $html = new html_base();
        $result = '';
        foreach ($this->lst() as $job) {
            if ($result == '') {
                $result .= $job->header();
            }
            $result .= $html->tr($job->display());
        }
        return $html->tbl($result);
    }

    /**
     * @param bool $is_admin true if the requesting user is an admin, who can also upgrade a job
     * @param int $msk_id the id of the shown view, so that a job button returns to this view
     * @return string with a table of the jobs with the pending jobs on top and the buttons to change an open job
     */
    function display_with_actions(bool $is_admin, int $msk_id): string
    {
        global $mtr;
        $html = new html_base();
        $rows = '';
        foreach ($this->pending_first() as $job) {
            $rows .= $html->tr($job->display_with_actions($is_admin, $msk_id));
        }
        if ($rows == '') {
            $rows = $html->tr($html->td($mtr->txt(msg_id::INFO_NO_JOBS)));
        }
        return $html->tbl(new job()->header(true) . $rows);
    }

    /**
     * @return array the jobs with the open jobs on top and within each group the newest request first
     */
    function pending_first(): array
    {
        $jobs = $this->lst();
        usort($jobs, fn(job $a, job $b) => [$b->is_open(), $b->request_time(), $b->id()]
            <=> [$a->is_open(), $a->request_time(), $a->id()]);
        return $jobs;
    }

    /*
     * to review
     */

    /**
     * show all batch_jobs of the list as table row (ex display)
     * @param array $url_arr the url vars of the calling page for the back link
     * @return string the html code with all batch_jobs of the list
     */
    function tbl(array $url_arr = []): string
    {
        $html = new html_base();
        $cols = '';
        // TODO check if and why the next line makes sense
        // $cols = $html->td('');
        foreach ($this->lst() as $job) {
            $lnk = $job->display_linked($url_arr);
            $cols .= $html->td($lnk);
        }
        return $html->tbl($html->tr($cols), styles::STYLE_BORDERLESS);
    }


}
