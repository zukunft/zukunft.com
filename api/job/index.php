<?php

/*

    api/job/index.php - the job API controller: send a job to the frontend
    ------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'job_status.php';
include_once paths::MODEL_SYSTEM . 'job_type.php';
include_once paths::MODEL_SYSTEM . 'job_time.php';
include_once paths::MODEL_SYSTEM . 'job.php';
include_once paths::MODEL_HELPER . 'server_guard.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("job", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string
    $ctrl = new controller();

    // get the parameters; a put changes the priority of the job or cancels it e.g. {"id": 1, "action": "job_cancel"}
    $method = $_SERVER[rest_ctrl::REQUEST_METHOD] ?? rest_ctrl::GET;
    $job_id = $_GET[url_var::ID] ?? 0;
    $action = '';
    $chg_usr_id = 0;
    if ($method === rest_ctrl::PUT) {
        $json_body = $ctrl->request_json();
        $job_id = $json_body[json_fields::ID] ?? 0;
        $action = $json_body[json_fields::ACTION] ?? '';
        $chg_usr_id = $json_body[json_fields::USER_ID] ?? 0;
    }

    // a refused write is answered by change_permitted itself
    $permitted = true;

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($job_id <= 0) {
            $msg->add(msg_id::JOB_ROW_MISSING, [msg_id::VAR_NAME => (string)$job_id]);
        } elseif ($method !== rest_ctrl::PUT) {
            $job = new job($usr);
            $job->load_by_id($job_id, $msg);
            $result = $job->api_json([], $msg);
        } else {
            $permitted = $ctrl->change_permitted($msg);
            if ($permitted) {
                // the own html frontend changes the job for the browsing user whose session it has validated
                $chg_usr = $usr->data_user($chg_usr_id, $msg, server_guard::from_own_pod());
                $job = new job($chg_usr);
                $job->load_by_id($job_id, $msg);
                $job->change_by_user($action, $chg_usr, $msg);
                $result = $job->api_json([], $msg);
            }
        }
    }

    if ($permitted) {
        $ctrl->get_json($result, $msg);
    }


    $app->end_api($db_con, $msg);
}