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

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use cfg\const\paths;
use html\const\paths as html_paths;

include_once html_paths::HTML . 'rest_call.php';
include_once paths::API_OBJECT . 'controller.php';
include_once paths::API_OBJECT . 'api_message.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_TYPES . 'api_type.php';
include_once paths::SHARED . 'api.php';

use controller\controller;
use cfg\user\user;
use cfg\word\word;
use html\rest_call;
use shared\api;
use shared\const\rest_ctrl;
use shared\types\api_type;

// open database
$db_con = prg_start("api/word", "", false);

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
    if ($usr->id() > 0) {

        $wrd = new word($usr);

        if ($method === rest_ctrl::GET) {
            // get the parameters
            $wrd_id = $_GET[api::URL_VAR_ID] ?? 0;
            $wrd_name = $_GET[api::URL_VAR_NAME] ?? '';

            if ($wrd_id > 0) {
                $wrd->load_by_id($wrd_id);
                $result = $wrd->api_json([api_type::HEADER], $usr);
            } elseif ($wrd_name != '') {
                $wrd->load_by_name($wrd_name);
                $result = $wrd->api_json([api_type::HEADER], $usr);
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

    prg_end_api($db_con);
}