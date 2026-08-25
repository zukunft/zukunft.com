<?php

/*

  api/phraseList/index.php - the phrase list API controller: send a list of phrases to the frontend
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';

use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("phraseList", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    $phr_ids = $_GET[url_var::ID_LST] ?? '';
    $phr_id = $_GET[url_var::PHRASE] ?? '';
    $phr_name = $_GET[url_var::NAME] ?? '';
    $direction_text = $_GET[url_var::DIRECTION] ?? '';
    // one level loads the direct links only, more levels also follow the links of the phrases
    // just found; load_by_phr_levels bounds the requested number
    $levels = (int)($_GET[url_var::LEVELS] ?? 1);
    $pattern = $_GET[url_var::PATTERN] ?? '';

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        $lst = new phrase_list($usr);
        // a related list is used structurally by the frontend, which reads the from, verb and to
        // of each linking triple (e.g. to find the table column definitions), so it needs the
        // phrase names; a list selected by id or by pattern only names its own entries
        $api_types = [];
        if ($phr_ids != '') {
            $lst->load_names_by_ids(new phr_ids(explode(",", $phr_ids)), $msg);
        } elseif ($phr_id != '' or $phr_name != '') {
            $phr = new phrase($usr);
            if ($phr_id != '') {
                $phr->set_id($phr_id);
            } else {
                // a system phrase is requested by its name, because the frontend knows the name
                // from a shared const but not the database id e.g. "mayor column (system)"
                $phr->load_by_name($phr_name, $msg);
            }
            // a missing or unknown direction must not leave $dir unset, because the load below
            // would then fail with a php error instead of returning the related phrases
            $dir = foaf_direction::BOTH;
            try {
                $dir = foaf_direction::from($direction_text);
            } catch (ValueError $error) {
                $msg->add_message_text($error->getMessage());
            }
            if ($phr->id() != 0) {
                $lst->load_by_phr_levels($phr, $msg, $dir, $levels);
                $api_types = [api_types::INCL_PHRASES];
            }
        } else {
            $lst->load_like($pattern, $msg);
        }
        // drop the phrases the requester may not read (idor); see phrase::is_readable_by
        $lst->filter_readable_by($usr);
        $result = $lst->api_json($api_types, $msg);
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con, $msg);
}