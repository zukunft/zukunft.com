<?php

/*

  api/view/index.php - the view API controller: send a view to the frontend
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

include_once paths::MODEL_VIEW . 'view.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// open database
$app = new application();
$db_con = $app->start_api("view", "", false);

if ($db_con->is_open()) {

    // get the parameters
    $dsp_id = $_GET[url_var::ID] ?? 0;
    $dsp_name = $_GET[url_var::NAME] ?? '';
    $cmp_lvl = $_GET[url_var::LEVELS] ?? 0;

    $msg = '';
    $result = ''; // reset the json message string

    // load the session user parameters
    $usr = new user;
    $msg .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        // the session user may differ from the data user e.g. an admin wants to see the data
        // of a user; the data user is included in the request in url_var::USER
        $load_usr = $usr->data_user($_GET[url_var::USER] ?? 0);

        $msk = new view($load_usr);
        if ($dsp_id > 0) {
            $msk->load_by_id($dsp_id);
            if ($cmp_lvl > 0) {
                $msk->load_components();
            }
            $result = $msk->api_json();
        } elseif ($dsp_name != '') {
            $msk->load_by_name($dsp_name);
            if ($cmp_lvl > 0) {
                $msk->load_components();
            }
            $result = $msk->api_json();
        } else {
            $msg = 'view id or name is missing';
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);

    $app->end_api($db_con);
}