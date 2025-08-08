<?php

/*

    api/phraseType/index.php - the phrase type API controller
    ------------------------

    send phrase type to the frontend that has been added by the user


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

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use cfg\const\paths;
use cfg\phrase\phrase_type;
use cfg\user\user;
use controller\controller;
use shared\types\phrase_type as phrase_type_shared;
use shared\url_var;

include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_TYPES . 'api_type.php';
include_once paths::API_OBJECT . 'controller.php';
include_once paths::API_OBJECT . 'api_message.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_PHRASE . 'phrase_type.php';
include_once paths::SHARED_TYPES . 'phrase_type.php';

// open database
$db_con = prg_start("api/phraseType", "", false);

if ($db_con->is_open()) {

    // get the parameters
    $phr_typ_id = $_GET[url_var::ID] ?? 0;

    $msg = '';
    $result = ''; // reset the json message string

    // load the session user parameters
    $usr = new user;
    $msg .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {

        if ($phr_typ_id != 0) {
            $phr_typ = new phrase_type(phrase_type_shared::NORMAL);
            $phr_typ->load_by_id($phr_typ_id);
            $result = $phr_typ->api_json();
        } else {
            $msg = 'phrase type id is missing';
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    prg_end_api($db_con);
}