<?php

/*

  api/group/index.php - the group API controller: send a group to the frontend
  -------------------

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

include_once paths::MODEL_GROUP . 'group.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// open database
$app = new application();
$db_con = $app->start_api("group", "", false);

if ($db_con->is_open()) {

    // get the parameters
    $grp_id = $_GET[url_var::ID] ?? 0;

    $msg = '';
    $result = ''; // reset the api message

    // load the session user parameters
    $usr = new user;
    $msg .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        // the session user may differ from the data user e.g. an admin wants to see the data
        // of a user; the data user is included in the request in url_var::USER
        $load_usr = $usr->data_user($_GET[url_var::USER] ?? 0);

        if (is_numeric($grp_id)) {
            $grp_id = (int)$grp_id;
        }
        if ($grp_id != 0 and $grp_id != '') {
            $grp = new group($load_usr);
            $grp->load_by_id($grp_id);
            $result = $grp->api_json();
        } else {
            $msg = 'group id is missing';
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con);
}