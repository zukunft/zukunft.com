<?php

/*

    api/phrase/index.php - the phrase API controller: send a phrase to the frontend
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
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once SHARED_TYPES_PATH . 'api_type.php';

use controller\controller;
use cfg\user\user;
use cfg\phrase\phrase;
use shared\api;
use shared\types\api_type;

// open database
$db_con = prg_start("api/phrase", "", false);

if ($db_con->is_open()) {

    // get the parameters
    $phr_id = $_GET[api::URL_VAR_ID] ?? 0;
    $phr_name = $_GET[api::URL_VAR_NAME] ?? '';

    // load the session user parameters
    $msg = '';
    $usr = new user;
    $msg .= $usr->get();

    $ctrl = new controller();
    $result = ''; // reset the json message string

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {

        $phr = new phrase($usr);
        if ($phr_id > 0) {
            $phr->load_by_id($phr_id);
            $result = $phr->api_json([api_type::HEADER], $usr);
        } elseif ($phr_name != '') {
            $phr->load_by_name($phr_name);
            $result = $phr->api_json([api_type::HEADER], $usr);
        } else {
            $msg = 'phrase id or name is missing';
        }

        // add, update or delete the phrase
        $ctrl->get_json($result, $msg);

    } else {
        $ctrl->not_permitted($msg);
    }

    prg_end_api($db_con);
}