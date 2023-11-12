<?php

/*

  api/verb/index.php - the verb API controller: send a verb to the frontend
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

use controller\controller;
use cfg\log\user;
use cfg\log\verb;
use api\system\verb_api;

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

include_once API_PATH . 'api.php';
include_once API_PATH . 'controller.php';
include_once API_PATH . 'message_header.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once API_VERB_PATH . 'verb.php';

// open database
$db_con = prg_start("api/verb", "", false);

// get the parameters
$vrb_id = $_GET[controller::URL_VAR_ID] ?? 0;
$vrb_name = $_GET[controller::URL_VAR_NAME] ?? '';

$msg = '';
$result = new verb_api(); // reset the html code var

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $vrb = new verb();
    if ($vrb_id > 0) {
        $vrb->load_by_id($vrb_id);
        $result = $vrb->api_verb_obj();
    } elseif ($vrb_name != '') {
        $vrb->load_by_name($vrb_name);
        $result = $vrb->api_verb_obj();
    } else {
        $msg = 'verb id or name is missing';
    }
}

$ctrl = new controller();
$ctrl->get($result, $msg);


prg_end_api($db_con);
