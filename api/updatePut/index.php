<?php

/*

  api/updatePut/index.php - the update put API controller: save the changes of a message
  ------------------------

  e.g. a POST of the changes of the calculator editor with the api header and in the body the
  words, triples and values to add or change and the objects to delete, each with its database
  id if known (see cfg/import/update_put.php); the answer is the message with the database id
  of each saved object

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

include_once paths::MODEL_IMPORT . 'update_put.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';

use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\import\update_put;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("updatePut", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $ctrl = new controller();

    // only a post carries the changes; the cli / test call without a method changes nothing
    $method = $_SERVER[rest_ctrl::REQUEST_METHOD] ?? rest_ctrl::GET;
    if ($usr->id > 0 and $method === rest_ctrl::POST) {
        if ($ctrl->change_permitted($msg)) {
            $upd = new update_put();
            $ctrl->get_json($upd->api_json($ctrl->request_json(), $msg), $msg);
        }
    } else {
        $ctrl->not_permitted($msg);
    }

    $app->end_api($db_con, $msg);
}
