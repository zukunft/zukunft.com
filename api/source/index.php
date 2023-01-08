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

use api\user_sandbox_api;
use controller\controller;

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("api/ref", "", false);

// get the parameters
$src_id = $_GET['id'] ?? 0;
$src_name = $_GET['name'] ?? '';
$src_code_id = $_GET['code_id'] ?? '';

$msg = '';
$ctrl = new controller();
$result = new api_message($db_con, 'source'); // create the message header

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    $result->set_user($usr);
    if ($src_id > 0) {
        $src = new source($usr);
        $src->load_by_id($src_id);
        $result->add_body($src->api_obj());
    } elseif ($src_name != '') {
        $src = new source($usr);
        $src->load_by_name($src_name);
        $result->add_body($src->api_obj());
    } elseif ($src_code_id != '') {
        $src = new source($usr);
        $src->load_by_code_id($src_code_id);
        $result->add_body($src->api_obj());
    } else {
        $msg = 'Cannot load source because id, name and code id is missing';
    }

    $ctrl->curl($result, $msg);

} else {
    $ctrl->not_permitted($result, $msg);
}

prg_end_api($db_con);
