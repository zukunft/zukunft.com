<?php

/*

    api/termList/index.php - the term list API controller: send a list of term to the frontend
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

use controller\controller;
use cfg\log\user;
use cfg\log\trm_ids;
use cfg\log\term_list;
use api\system\term_list_api;

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
include_once MODEL_PHRASE_PATH . 'trm_ids.php';
include_once MODEL_PHRASE_PATH . 'term_list.php';
include_once API_PHRASE_PATH . 'term_list.php';

// open database
$db_con = prg_start("api/termList", "", false);

// get the parameters
$trm_ids = $_GET[controller::URL_VAR_ID_LST] ?? '';
$pattern = $_GET[controller::URL_VAR_PATTERN] ?? '';

$msg = '';
$result = new term_list_api(); // reset the html code var

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    if ($trm_ids != '') {
        $lst = new term_list($usr);
        $lst->load_by_ids((new trm_ids(explode(",", $trm_ids))));
        $result = $lst->api_obj();
    } elseif ($pattern != '') {
        $lst = new term_list($usr);
        $lst->load_like($pattern);
        $result = $lst->api_obj();
    } else {
        $msg = 'term ids and pattern missing';
    }
}

$ctrl = new controller();
$ctrl->get_list($result, $msg);


prg_end_api($db_con);
