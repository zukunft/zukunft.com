<?php

/*

    api/userLogSumList/index.php - the users with the most server time API controller
    ----------------------------

    send the users with the most server time of the configured last days to the admin dashboard


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

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'server_guard.php';
include_once paths::MODEL_USER . 'user_log_sum_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_log_sum_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// read the request parameters once at the top of the entry-point script
$data_usr_id = $_GET[url_var::USER] ?? 0;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("userLogSumList", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // the server time per user shows who uses the pod how much, so it is admin-only like the slow
    // requests; the own html frontend calls this api server-to-server, so the validated browsing
    // user in url_var::USER is only trusted for a call of this pod itself (see api/sysLogList)
    $load_usr = $usr->data_user($data_usr_id, $msg, server_guard::from_own_pod());
    if ($load_usr->is_admin() or $load_usr->is_system()) {
        $usr_log_sum_lst = new user_log_sum_list();
        $usr_log_sum_lst->load($msg);
        $result = $usr_log_sum_lst->api_json([api_types::HEADER], $load_usr);
    } else {
        $msg->add(msg_id::USER_LOG_SUM_LIST_ONLY_ADMIN, []);
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con, $msg);
}
