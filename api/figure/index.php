<?php

/*

  api/figure/index.php - the value API controller: send a figure to the frontend
  --------------------
  
  This file is part of zukunft.com - calc with values

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

use api\figure_api;
use controller\controller;
use model\user;
use model\value;

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

include_once API_PATH . 'controller.php';
include_once API_PATH . 'message_header.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once API_FORMULA_PATH . 'figure.php';

// open database
$db_con = prg_start("api/figure", "", false);

// get the parameters
$fig_id = $_GET[controller::URL_VAR_ID] ?? 0;

$msg = '';
$result = new figure_api(); // reset the html code var

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    if ($fig_id > 0) {
        $val = new value($usr);
        $val->load_by_id($fig_id);
        $val->load_objects();
        $fig = $val->figure();
        $result = $fig->api_obj();
    } elseif ($fig_id < 0) {
        $fv = new result($usr);
        $fv->load_by_id($fig_id);
        $fig = $fv->figure();
        $result = $fig->api_obj();
    } else {
        $msg = 'figure id is missing';
    }
}

$ctrl = new controller();
$ctrl->get($result, $msg);


prg_end_api($db_con);
