<?php

/*

    api/languageForm/index.php - the language form API controller
    ------------------------

    send language form to the frontend that has been added by the user


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

use cfg\language_form;
use controller\controller;

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET[controller::URL_VAR_DEBUG] ?? 0;
const ROOT_PATH = __DIR__ . '/../../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("api/languageForm", "", false);

// get the parameters
$lan_typ_id = $_GET['id'] ?? 0;

$msg = '';
$result = ''; // reset the html code var

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    if ($lan_typ_id != '') {
        $lan_typ = new language_form(language_form::PLURAL);
        $lan_typ->load_by_id($lan_typ_id);
        $result = $lan_typ->api_obj();
    } else {
        $msg = 'language form id is missing';
    }
}

$ctrl = new controller();
$ctrl->get_export($result, $msg);


prg_end_api($db_con);
