<?php

/*

    api/sourceList/index.php - the source list API controller: send a list of source to the frontend
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_REF . 'source_list.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("sourceList", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    $src_ids = $_GET[url_var::ID_LST] ?? '';
    $pattern = $_GET[url_var::PATTERN] ?? '';

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($src_ids != '') {
            $lst = new source_list($usr);
            $lst->load_by_ids(explode(",", $src_ids), $msg);
            // drop the sources the requester may not read (idor); see sandbox::is_readable_by
            $lst->filter_readable_by($usr);
            $result = $lst->api_json();
        } elseif ($pattern != '') {
            $lst = new source_list($usr);
            $lst->load_like($pattern, $msg);
            // drop the sources the requester may not read (idor); see sandbox::is_readable_by
            $lst->filter_readable_by($usr);
            $result = $lst->api_json();
        } else {
            $msg->add_message_text('source ids and pattern missing');
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con);
}