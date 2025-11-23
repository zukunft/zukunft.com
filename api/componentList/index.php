<?php

/*

    api/componentList/index.php - the component list API controller: send a list of component to the frontend
    ---------------------------

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

include_once paths::MODEL_COMPONENT . 'component_list.php';

use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\cfg\component\component_list;

// open database
$app = new application();
$db_con = $app->start_api("componentList");

if ($db_con->is_open()) {

    // get the parameters
    $msk_id = $_GET[url_var::VIEW] ?? '';
    $pattern = $_GET[url_var::PATTERN] ?? '';

    $msg = '';
    $result = ''; // reset the json message string

    // load the session user parameters
    $usr = new user;
    $msg .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($msk_id != '') {
            $lst = new component_list($usr);
            $lst->load_by_view_id($msk_id);
            $result = $lst->api_json();
        } elseif ($pattern != '') {
            $lst = new component_list($usr);
            $lst->load_names(($pattern));
            $result = $lst->api_json();
        } else {
            $msg = 'view id and pattern missing';
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con);
}