<?php

/*

    api/viewList/index.php - the view list API controller: send a list of views to the frontend
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

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_VIEW . 'view_list.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("viewList", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    $cmp_id = $_GET[url_var::MASK] ?? '';
    $pattern = $_GET[url_var::PATTERN] ?? '';

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($cmp_id != '') {
            $lst = new view_list($usr);
            $lst->load_by_component_id($cmp_id, $msg);
            // drop the views the requester may not read (idor); see sandbox::is_readable_by
            $lst->filter_readable_by($usr);
            $result = $lst->api_json();
        } elseif ($pattern != null) {
            $lst = new view_list($usr);
            // load with the full field set (incl. the view type) so the frontend can
            // filter the views that can be assigned to a word (ex_system / ex_non_phrase)
            $lst->load_by_pattern($pattern, $msg);
            // drop the views the requester may not read (idor); see sandbox::is_readable_by
            $lst->filter_readable_by($usr);
            $result = $lst->api_json();
        } else {
            $msg->add_message_text('view id and pattern missing');
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con);
}