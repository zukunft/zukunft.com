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

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

include_once API_PATH . 'api.php';
include_once API_PATH . 'controller.php';
include_once API_PATH . 'api_message.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_COMPONENT_PATH . 'component_list.php';
include_once API_COMPONENT_PATH . 'component_list.php';

use controller\controller;
use cfg\user;
use cfg\component\component_list;
use api\component\component_list as component_list_api;

// open database
$db_con = prg_start("api/componentList", "", false);

// get the parameters
$msk_id = $_GET[controller::URL_VAR_VIEW_ID] ?? '';
$pattern = $_GET[controller::URL_VAR_PATTERN] ?? '';

$msg = '';
$result = new component_list_api(array());

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    if ($msk_id != '') {
        $lst = new component_list($usr);
        $lst->load_by_view_id($msk_id);
        $result = $lst->api_obj();
    } elseif ($pattern != '') {
        $lst = new component_list($usr);
        $lst->load_names(($pattern));
        $result = $lst->api_obj();
    } else {
        $msg = 'view id and pattern missing';
    }
}

$ctrl = new controller();
$ctrl->get_list($result, $msg);


prg_end_api($db_con);
