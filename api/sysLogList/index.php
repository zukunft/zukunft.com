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

include_once paths::MODEL_SYSTEM . 'sys_log_list.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// read the request parameters once at the top of the entry-point script
$dsp_type = $_GET[url_var::LOG_STATUS] ?? sys_log_list::DSP_ALL;
$page = (int)($_GET[url_var::LOG_PAGE] ?? 0);
$size = (int)($_GET[url_var::LOG_SIZE] ?? shared_config::ROW_LIMIT);

// open database
$app = new application();
$db_con = $app->start_api("log", "", false);

if ($db_con->is_open()) {

    // load the session user parameters
    $usr = new user;
    $msg = $usr->get();

    $result = ''; // reset the json message string

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        $lst = new sys_log_list();
        $lst->set_user($usr);
        $lst->dsp_type = $dsp_type;
        $lst->page = $page;
        $lst->size = $size;
        $lst->load_all();
        $result = $lst->api_json([api_types::HEADER], $usr);
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con);
}