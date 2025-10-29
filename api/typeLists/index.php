<?php

/*

    api/type_lists/index.php - the API controller to send all preloaded types to the frontend
    ------------------------

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

include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_TYPES . 'api_type.php';
include_once paths::API_OBJECT . 'controller.php';
include_once paths::API_OBJECT . 'api_message.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_HELPER . 'type_lists.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_lists;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\types\api_type;

// open database
$db_con = prg_start_api("typeLists", "", false);

if ($db_con->is_open()) {

    // no parameters needed

    $msg = '';
    $result = ''; // reset the json message string

    // load the session user parameters
    $usr = new user;
    $msg .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {
        $sys_typ_lst = new type_lists();
        $sys_typ_lst->load($db_con, $usr);
        $result = $sys_typ_lst->api_json([api_type::HEADER], $usr);
    }

    $ctrl = new controller();

    $ctrl->get_json($result, $msg);

    prg_end_api($db_con);
}