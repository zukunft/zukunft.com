<?php

/*

  api/formula/index.php - the formula API controller: send a formula to the frontend
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

include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// open database
$app = new application();
$db_con = $app->start_api("formula");

if ($db_con->is_open()) {

    // get the parameters
    $frm_id = $_GET[url_var::ID] ?? 0;
    $frm_name = $_GET[url_var::NAME] ?? '';
    // INCL_RELATED is opt-in via the ?incl_related=1 url param so the default formula fetch stays
    // cheap; the default formula view adds the flag to get the assigned phrases (title subtitle)
    // and the latex terms (links of the expression_latex_link component)
    $typ_lst = api_type_list::from_url_array($_GET);

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

        $frm = new formula($load_usr);
        if ($frm_id > 0) {
            $frm->load_by_id($frm_id);
            $result = $frm->api_json($typ_lst, $load_usr);
        } elseif ($frm_name != '') {
            $frm->load_by_name($frm_name);
            $result = $frm->api_json($typ_lst, $load_usr);
        } else {
            $msg = 'formula id or name is missing';
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con);
}