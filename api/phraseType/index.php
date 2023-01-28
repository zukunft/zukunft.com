<?php

/*

    api/phraseType/index.php - the phrase type API controller
    ------------------------

    send phrase type to the frontend that has been added by the user


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

use api\phrase_list_api;
use cfg\phrase_type;
use controller\controller;

// standard zukunft header for callable php files to allow debugging and lib loading
const ROOT_PATH = __DIR__ . '/../../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';
$debug = $_GET[controller::URL_VAR_DEBUG] ?? 0;

// open database
$db_con = prg_start("api/phraseType", "", false);

// get the parameters
$phr_typ_id = $_GET[controller::URL_VAR_ID] ?? 0;

$msg = '';
$result = new phrase_list_api(); // reset the html code var

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    if ($phr_typ_id != '') {
        $phr_typ = new phrase_type(phrase_type::NORMAL);
        $phr_typ->load_by_id($phr_typ_id);
        $result = $phr_typ->api_obj();
    } else {
        $msg = 'phrase type id is missing';
    }
}

$ctrl = new controller();
$ctrl->get_export($result, $msg);


prg_end_api($db_con);
