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

// open database
$app = new application();
$db_con = $app->start_api("figure");

if ($db_con->is_open()) {

    // get the parameters
    $fig_id = $_GET[url_var::ID] ?? 0;

    $result = ''; // reset the json message string

    // load the session user parameters
    $usr = new user;
    $msg = new user_message();
    $msg->add_message_text($usr->get());
    // store the requesting user on the single message of this request as early as possible,
    // so every function below reads the requesting user from $msg->usr
    // (docs/llm/state-and-messages.md)
    $msg->usr = $usr;

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($fig_id > 0) {
            $val = new value($usr);
            $val->load_by_id($fig_id);
            $val->load_objects();
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
            $res->load_by_id($fig_id);
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