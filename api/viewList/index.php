<?php

/*

    api/viewList/index.php - the view list API controller: send a list of views to the frontend
    ----------------------

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

include_once SHARED_PATH . 'api.php';
include_once SHARED_TYPES_PATH . 'api_type.php';
include_once API_OBJECT_PATH . 'controller.php';
include_once API_OBJECT_PATH . 'api_message.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_VIEW_PATH . 'view_list.php';

use controller\controller;
use cfg\user\user;
use cfg\view\view_list;
use shared\api;

// open database
$db_con = prg_start("api/viewList", "", false);

// get the parameters
$cmp_id = $_GET[api::URL_VAR_VIEW_ID] ?? '';
$pattern = $_GET[api::URL_VAR_PATTERN] ?? '';

$msg = '';
$result = ''; // reset the json message string

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    if ($cmp_id != '') {
        $lst = new view_list($usr);
        $lst->load_by_component_id($cmp_id);
        $result = $lst->api_json();
    } elseif ($_GET[api::URL_VAR_PATTERN] != null) {
        $lst = new view_list($usr);
        $lst->load_names(($pattern));
        $result = $lst->api_json();
    } else {
        $msg = 'view id and pattern missing';
    }
}

$ctrl = new controller();
$ctrl->get_json($result, $msg);


prg_end_api($db_con);
