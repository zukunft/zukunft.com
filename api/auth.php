<?php

/*

  api/auth.php - the word API controller: send a word to the frontend
  ------------
  
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
include_once PHP_PATH . 'init.php';

use cfg\const\paths;
use html\const\paths as html_paths;

include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_TYPES . 'api_type.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';

use cfg\user\user;
use shared\const\rest_ctrl;

// open database
$db_con = prg_start("api/auth", "", false);

if ($db_con->is_open()) {

    // load the session user parameters
    $msg = '';
    $usr = new user;
    $msg .= $usr->get();

    // Basic Auth: check credentials
    if (!isset($_SERVER[rest_ctrl::PHP_AUTH_USER]) || !isset($_SERVER[rest_ctrl::PHP_AUTH_PW])) {
        send_auth_request();
    }

    $username = $_SERVER[rest_ctrl::PHP_AUTH_USER];
    $password = $_SERVER[rest_ctrl::PHP_AUTH_PW];

    prg_end_api($db_con);
}