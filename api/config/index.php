<?php

/*

    api/config/index.php - the API controller to send the system and user configuration to the frontend
    --------------------

    It must be possible to send th backend configuration to the frontend so that it can be changed e.g. by an admin
    If the user changes something only the user config is send to the frontend using a subscribed trigger
    on startup be default only the frontend config is sent to the frontend


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

include_once paths::SHARED . 'api.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type.php';
include_once paths::API_OBJECT . 'controller.php';
include_once paths::API_OBJECT . 'api_message.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::SHARED_CONST . 'users.php';

use cfg\helper\config_numbers;
use cfg\user\user_message;
use controller\controller;
use cfg\user\user;
use shared\enum\messages as msg_id;
use shared\api;
use shared\types\api_type;

// open database
$db_con = prg_start("api/config", "", false);

if ($db_con->is_open()) {

    // get the parameter which config part is requested
    $part = $_GET[api::URL_VAR_CONFIG_PART] ?? '';
    $with_phr = $_GET[api::URL_VAR_WITH_PHRASES] ?? '';

    $usr_msg = new user_message();
    $result = ''; // reset the html code var

    // load the session user parameters
    $usr = new user;
    $usr_msg->add_message_text($usr->get());

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {
        $cfg_lst = new config_numbers($usr);
        $usr_msg = new user_message();
        if ($part == api::CONFIG_ALL or $part == '') {
            $usr_msg = $cfg_lst->load_cfg($usr);
        } elseif ($part == api::CONFIG_FRONTEND) {
            $usr_msg = $cfg_lst->load_frontend_cfg($usr);
        } elseif ($part == api::CONFIG_USER) {
            $usr_msg = $cfg_lst->load_usr_cfg($usr);
        } else {
            $usr_msg->add_id_with_vars(msg_id::CONFIG_PART, [msg_id::VAR_PART => $part]);
        }
        if (!$usr_msg->is_ok()) {
            $usr_msg->add_id(msg_id::CONFIG_NOT_LOADED);
        } else {
            if ($cfg_lst->is_empty()) {
                $usr_msg->add_id(msg_id::CONFIG_EMPTY);
            }
        }
        if ($with_phr == api::URL_VAR_TRUE) {
            $result = $cfg_lst->api_json([api_type::INCL_PHRASES]);
        } else {
            $result = $cfg_lst->api_json([api_type::NO_KEY_FILL]);
        }
    }

    $ctrl = new controller();

    $ctrl->get_json($result, $usr_msg->get_last_message());

    prg_end_api($db_con);
}