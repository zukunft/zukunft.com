<?php

/*

  api/source/index.php - the source API controller: send a word to the frontend
  -----------------
  
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
include_once PHP_PATH . 'zu_lib.php';

include_once SHARED_PATH . 'api.php';
include_once SHARED_TYPES_PATH . 'api_type.php';
include_once API_OBJECT_PATH . 'controller.php';
include_once API_OBJECT_PATH . 'api_message.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_REF_PATH . 'source.php';
include_once SHARED_TYPES_PATH . 'api_type.php';

use controller\controller;
use cfg\user\user;
use cfg\ref\source;
use shared\api;
use shared\types\api_type;

// open database
$db_con = prg_start("api/ref", "", false);

if ($db_con->is_open()) {

    // get the parameters
    $src_id = $_GET[api::URL_VAR_ID] ?? 0;
    $src_name = $_GET[api::URL_VAR_NAME] ?? '';
    $src_code_id = $_GET[api::URL_VAR_CODE_ID] ?? '';

    // load the session user parameters
    $msg = '';
    $usr = new user;
    $msg .= $usr->get();

    $ctrl = new controller();
    $result = ''; // reset the json message string


    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {

        // load the source from the database for GET, UPDATE and DELETE
        $src = new source($usr);
        if ($src_id > 0) {
            $src->load_by_id($src_id);
            $result = $src->api_json([api_type::HEADER], $usr);
        } elseif ($src_name != '') {
            $src->load_by_name($src_name);
            $result = $src->api_json([api_type::HEADER], $usr);
        } elseif ($src_code_id != '') {
            $src->load_by_code_id($src_code_id);
            $result = $src->api_json([api_type::HEADER], $usr);
        } else {
            $msg = 'Cannot load source because id, name and code id is missing';
        }

        // add, update or delete the source
        $ctrl->get_json($result, $msg);

    } else {
        $ctrl->not_permitted($msg);
    }

    prg_end_api($db_con);
}