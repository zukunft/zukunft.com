<?php

/*

  api/figure/index.php - the value API controller: send a figure to the frontend
  --------------------
  
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

include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_VALUE . 'value.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("figure", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    $fig_id = $_GET[url_var::ID] ?? 0;

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($fig_id > 0) {
            $val = new value($usr);
            $val->load_by_id($fig_id, $msg);
            $val->load_objects($msg);
            // do not disclose another user's private value behind a figure id (idor); the same
            // neutral message as a missing id, so the response does not confirm it exists
            if ($val->is_readable_by($usr)) {
                $fig = $val->figure();
                $result = $fig->api_json();
            } else {
                $msg->add_message_text('figure id is missing');
            }
        } elseif ($fig_id < 0) {
            $res = new result($usr);
            $res->load_by_id($fig_id, $msg);
            if ($res->is_readable_by($usr)) {
                $fig = $res->figure();
                $result = $fig->api_json();
            } else {
                $msg->add_message_text('figure id is missing');
            }
        } else {
            $msg->add_message_text('figure id is missing');
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con);
}