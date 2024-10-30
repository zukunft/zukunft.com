<?php

/*

  api/phraseList/index.php - the phrase list API controller: send a list of phrases to the frontend
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

include_once API_PATH . 'api.php';
include_once API_PATH . 'controller.php';
include_once API_PATH . 'api_message.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once API_PHRASE_PATH . 'phrase_list.php';

use cfg\phrase;
use controller\controller;
use cfg\user;
use cfg\phr_ids;
use cfg\phrase_list;
use api\phrase\phrase_list as phrase_list_api;
use shared\api;

// open database
$db_con = prg_start("api/phraseList", "", false);

// get the parameters
$phr_ids = $_GET[api::URL_VAR_ID_LST] ?? '';
$phr_id = $_GET[api::URL_VAR_PHRASE] ?? '';
$pattern = $_GET[api::URL_VAR_PATTERN] ?? '';

$msg = '';
$result = new phrase_list_api(); // reset the html code var

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $lst = new phrase_list($usr);
    if ($phr_ids != '') {
        $lst->load_names_by_ids((new phr_ids(explode(",", $phr_ids))));
    } elseif ($phr_id != '') {
        $phr = new phrase($usr);
        $phr->set_id($phr_id);
        $lst->load_by_phr($phr);
    } else {
        $lst->load_like($pattern);
    }
    $result = $lst->api_obj();
}

$ctrl = new controller();
$ctrl->get_list($result, $msg);


prg_end_api($db_con);
