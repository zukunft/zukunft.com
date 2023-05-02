<?php

/*

    api/user/index.php - the user im- and export API controller
    ------------------

    use GET to retrieve a JSON that can be imported into another zukunft.com pod
    use PUT to import data from a JSON in the zukunft.com exchange format

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

    TODO add multi level security check to prevent gaining access right

*/

use controller\controller;
use model\user;

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

include_once API_PATH . 'controller.php';
include_once API_PATH . 'message_header.php';
include_once MODEL_USER_PATH . 'user.php';

// open database
$db_con = prg_start("api/user", "", false);

// get the parameters
$usr_id = $_GET[controller::URL_VAR_ID] ?? 0;
$usr_name = $_GET[controller::URL_VAR_NAME] ?? '';
$usr_email = $_GET[controller::URL_VAR_EMAIL] ?? '';

$msg = '';
$result = ''; // reset the html code var

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $db_usr = new user();
    if ($usr_id != 0) {
        $db_usr->load_by_id($usr_id);
        $result = json_decode(json_encode($db_usr->api_obj()));
    } elseif ($usr_name != '') {
        $db_usr->load_by_name($usr_name);
        $result = json_decode(json_encode($db_usr->api_obj()));
    } elseif ($usr_email != '') {
        $db_usr->load_by_email($usr_email);
        $result = json_decode(json_encode($db_usr->api_obj()));
    } else {
        $msg = 'user id or name missing';
    }
}

$ctrl = new controller();
$ctrl->get_export($result, $msg);


prg_end_api($db_con);
