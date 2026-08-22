<?php

/*

  api/viewRelation/index.php - the view relation API controller: send a view relation to the frontend
  --------------------------

  a view relation has no name, so it can only be requested by its id

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
include_once paths::MODEL_VIEW . 'view_relation.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\api\controller;

use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("view_relation", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    $lnk_id = $_GET[url_var::ID] ?? 0;
    // e.g. ir=1 to include the linked views, the owner, the changes and the overwrites
    // of the view relation page tabs
    $typ_lst = api_type_list::from_url_array($_GET);

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        // the session user may differ from the data user e.g. an admin wants to see the data
        // of a user; the data user is included in the request in url_var::USER
        $load_usr = $usr->data_user($_GET[url_var::USER] ?? 0, $msg, server_guard::from_own_pod());

        $lnk = new view_relation($load_usr);
        if ($lnk_id > 0) {
            $lnk->load_by_id($lnk_id, $msg);
            $result = $lnk->api_json($typ_lst, $msg, $load_usr);
        } else {
            $msg->add_message_text('view relation id is missing');
        }
    }

    // do not disclose another user's private view relation loaded by id (idor); the same neutral
    // message as a missing id, so the response does not confirm the relation exists
    if ($result != '' and !$lnk->is_readable_by($usr)) {
        $result = '';
        $msg->add_message_text('view relation id is missing');
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con, $msg);

}
