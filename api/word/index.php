<?php

/*

  api/word/index.php - the word API controller: send a word to the frontend
  ------------------
  
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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'rest_call.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// open database
$app = new application();
$db_con = $app->start_api("word", "", false);

if ($db_con->is_open()) {

    // load the session user parameters
    $msg = '';
    $usr = new user;
    $msg .= $usr->get();

    $ctrl = new controller();
    $rest_ctrl = new rest_call();
    $result = ''; // reset the json message string

    // TODO remove temp
    if (in_array(rest_ctrl::REQUEST_METHOD, $_SERVER)) {
        $method = $_SERVER[rest_ctrl::REQUEST_METHOD];
        if (in_array(rest_ctrl::REQUEST_URI, $_SERVER)) {
            $uri = $_SERVER[rest_ctrl::REQUEST_URI];
        } else {
            $uri = '';
        }
        $json_body = $rest_ctrl->request_json();
    } else {
        // for debugging only
        $method = rest_ctrl::GET;
        $json_body = [];
        $uri = '/api/word';
        //$method = rest_ctrl::POST;
        //$json_body = json_decode('{"id":0,"share":3,"protection":2,"name":"System Test Word","description":"Mathematics is an area of knowledge that includes the topics of numbers and formulas","type_id":7,"code_id":"mathematics","plural":"mathematics"}', true);
        //$uri = '/api/word/';
        //$method = rest_ctrl::PUT;
        //$json_body = json_decode('{"id":1295,"share":3,"protection":2,"name":"System Test Word","description":"Mathematics Updated Description"}', true);
        //$uri = '/api/word/1298';
        //$method = rest_ctrl::DELETE;
        //$json_body = [];
        //$uri = '/api/word/1298';
    }

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        // the session user may differ from the data user e.g. an admin wants to see the data
        // of a user; the data user is included in the request in url_var::USER
        $load_usr = $usr->data_user($_GET[url_var::USER] ?? 0);

        $wrd = new word($load_usr);

        if ($method === rest_ctrl::GET) {
            // get the parameters
            $wrd_id = $_GET[url_var::ID] ?? 0;
            $wrd_name = $_GET[url_var::NAME] ?? '';
            // INCL_RELATED is opt-in via the ?incl_related=1 url param so the default
            // single-word fetch stays cheap (no extra triple_list query); callers that
            // need the related phrases (e.g. the default word view) add the flag
            $typ_lst = api_type_list::from_url_array($_GET, [api_types::HEADER]);

            if ($wrd_id > 0) {
                $wrd->load_by_id($wrd_id);
                $result = $wrd->api_json($typ_lst, $load_usr);
            } elseif ($wrd_name != '') {
                $wrd->load_by_name($wrd_name);
                $result = $wrd->api_json($typ_lst, $load_usr);
            } else {
                $msg = 'word id or name is missing';
            }

            // return either the api json to fill the frontend object
            // or the message why the api json could not be created
            $ctrl->get_json($result, $msg);
        } elseif ($method === rest_ctrl::POST) {
            $ctrl->post_json($json_body, $wrd, $usr, $msg);
        } elseif ($method === rest_ctrl::PUT) {
            $ctrl->put_json(basename($uri), $json_body, $wrd, $usr, $msg);
        } elseif ($method === rest_ctrl::DELETE) {
            $ctrl->delete(basename($uri), $wrd, $usr, $msg);
        }

    } else {
        $ctrl->not_permitted($msg);
    }

    $app->end_api($db_con);
}