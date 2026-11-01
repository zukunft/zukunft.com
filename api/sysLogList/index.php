<?php

/*

  api/log/index.php - the system exception log API controller: send a list of user changes to the frontend
  -----------------
  
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

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'server_guard.php';
include_once paths::MODEL_SYSTEM . 'sys_log_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// read the request parameters once at the top of the entry-point script
$dsp_type = $_GET[url_var::LOG_STATUS] ?? sys_log_list::DSP_ALL;
$page = (int)($_GET[url_var::LOG_PAGE] ?? 0);
$size = (int)($_GET[url_var::LOG_SIZE] ?? shared_config::ROW_LIMIT);

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("log", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // the system log holds the internal code trace, text and description of every error, so it is
    // admin-only data; an anonymous visitor always gets an auto created ip user with id > 0, so the
    // former id > 0 check let anyone read the full log - restrict it to an admin or system user,
    // matching the frontend that only shows the log to admins (web/system/sys_log.php);
    // the own html frontend calls this api server-to-server without the browser session, so like
    // api/jobList the browsing user it has validated is the data user in url_var::USER, which is
    // only trusted for a call of this pod itself (see user::data_user and server_guard::from_own_pod)
    $load_usr = $usr->data_user($_GET[url_var::USER] ?? 0, $msg, server_guard::from_own_pod());
    if ($load_usr->is_admin() or $load_usr->is_system()) {

        $lst = new sys_log_list();
        $lst->set_user($load_usr);
        $lst->dsp_type = $dsp_type;
        $lst->page = $page;
        $lst->size = $size;
        $lst->load_all($msg);
        $result = $lst->api_json([api_types::HEADER], $msg, $load_usr);
    } else {
        $msg->add(msg_id::SYS_LOG_LIST_ONLY_ADMIN, []);
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con, $msg);
}