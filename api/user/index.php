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

*/

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_TYPES . 'api_types.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// open database
$app = new application();
$db_con = $app->start_api("user", "", false);

if ($db_con->is_open()) {

    // get the parameters
    $usr_id = $_GET[url_var::ID] ?? 0;
    $usr_name = $_GET[url_var::NAME] ?? '';
    $usr_email = $_GET[url_var::EMAIL] ?? '';

    $result = ''; // reset the json message string

    // load the session user parameters
    $usr = new user;
    $msg = new user_message();
    $msg->add_message_text($usr->get());
    // store the requesting user on the single message of this request as early as possible,
    // so every function below reads the requesting user from $msg->usr
    // (docs/llm/state-and-messages.md)
    $msg->usr = $usr;

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        $db_usr = new user();
        $found = false;
        if ($usr_id != 0) {
            $db_usr->load_by_id($usr_id);
            $found = true;
        } elseif ($usr_name != '') {
            $db_usr->load_by_name($usr_name);
            $found = true;
        } elseif ($usr_email != '') {
            $db_usr->load_by_email($usr_email);
            $found = true;
        } else {
            $msg->add_message_text('user id or name missing');
        }

        // only an admin or the user himself may read a user record; otherwise
        // an anonymous visitor (who always gets an auto created ip user) could
        // enumerate users and read the email, ip address and activation key
        if ($found) {
            if ($usr->is_admin() or $db_usr->id() == $usr->id) {
                $result = $db_usr->api_json();
            } else {
                $msg->add_message_text('not permitted');
            }
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con);
}