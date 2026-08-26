<?php

/*

    api/valueList/index.php - the value list API controller
    ------------------------

    send a list of values to the frontend


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

include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED . 'api.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("valueList", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    // TODO use a json with the ids
    // TODO add load by formula and source
    $id_lst = $_GET[url_var::ID_LST] ?? '';
    $phr_ids = $_GET[api::JSON_LIST_PHRASE_IDS] ?? '';

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($phr_ids != '') {
            $phr_lst = new phrase_list($usr);
            $phr_lst->load_names_by_ids(new phr_ids(explode(",", $phr_ids)), $msg);
            $lst = new value_list($usr);
            // any value of any of the given phrases, e.g. the values of all global problems,
            // because a value normally names only one of the requested phrases
            $lst->load_by_phr_lst($phr_lst, $msg, true);
            // drop the values the requesting user may not read (idor); see value::is_readable_by
            $lst->filter_readable_by($usr);
            // the frontend names the rows and the columns of a value table by the phrases of each
            // value, so the group phrases are loaded and emitted with the values
            $lst->load_phrases($msg);
            $result = $lst->api_json([api_types::INCL_PHRASES], $msg);
        } elseif ($id_lst != '') {
            $lst = new value_list($usr);
            $lst->load_by_ids(explode(",", $id_lst), $msg);
            // drop the values the requesting user may not read, so listing ids cannot disclose
            // another user's private/personal value (idor); see value::is_readable_by
            $lst->filter_readable_by($usr);
            $result = $lst->api_json([], $msg);
        } else {
            $msg->add_message_text('value or phrase id list is missing');
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con, $msg);
}