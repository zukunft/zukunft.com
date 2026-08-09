<?php

/*

  api/triple/index.php - the word API controller: send a triple to the frontend
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

include_once paths::MODEL_WORD . 'triple.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("triple", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        // get the parameters
        $trp_id = $_GET[url_var::ID] ?? 0;
        $trp_name = $_GET[url_var::NAME] ?? '';
        $usr_id = $_GET[url_var::USER] ?? 0;
        // e.g. ir=1 to include related objects like from, verb and to names
        $typ_lst = api_type_list::from_url_array($_GET);

        // the session user may differ from the data user e.g. an admin wants to see the data
        // of a user; the data user is included in the request in url_var::USER
        $load_usr = $usr->data_user($usr_id, $msg);


        $trp = new triple($load_usr);
        if ($trp_id > 0) {
            $trp->load_by_id($trp_id, $msg);
            $result = $trp->api_json($typ_lst, $msg, $load_usr);
        } elseif ($trp_name > 0) {
            $trp->load_by_name($trp_name, $msg);
            $result = $trp->api_json($typ_lst, $msg, $load_usr);
        } else {
            $msg->add_message_text('triple id or name is missing');
        }
    }

    // do not disclose another user's private triple loaded by id/name (idor); the same neutral
    // message as a missing id so the response does not confirm the object exists
    if ($result != '' and !$trp->is_readable_by($usr)) {
        $result = '';
        $msg->add_message_text('triple id or name is missing');
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);

    $app->end_api($db_con);
}