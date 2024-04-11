<?php

/*

  api/word/index.php - the word API controller: send a word to the frontend
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
include_once MODEL_WORD_PATH . 'word.php';

use api\api_message;
use controller\controller;
use cfg\user;
use cfg\word;

// open database
$db_con = prg_start("api/word", "", false);

// get the parameters
$wrd_id = $_GET[controller::URL_VAR_ID] ?? 0;
$wrd_name = $_GET[controller::URL_VAR_NAME] ?? '';

// load the session user parameters
$msg = '';
$usr = new user;
$msg .= $usr->get();

$ctrl = new controller();
$result = new api_message($db_con, word::class, $usr); // create the message header

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $wrd = new word($usr);
    $result->set_user($usr);
    if ($wrd_id > 0) {
        $wrd->load_by_id($wrd_id);
        $result->add_body($wrd->api_obj());
    } elseif ($wrd_name != '') {
        $wrd->load_by_name($wrd_name);
        $result->add_body($wrd->api_obj());
    } else {
        $msg = 'word id or name is missing';
    }

    // add, update or delete the word
    $ctrl->curl($result, $msg, $wrd_id, $wrd);

} else {
    $ctrl->not_permitted($msg);
}

prg_end_api($db_con);
