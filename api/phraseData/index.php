<?php

/*

  api/phraseData/index.php - the phrase data API controller: send all data of one phrase to the frontend
  ------------------------

  e.g. /api/phraseData/?id=-2780 for the words, triples, formulas, sources and values of the
  example PV installation with their database ids (see cfg/export/phrase_data.php);
  pw names the walk (phrase_data_walks, pwv for the values of the phrase by default or pwf for
  the triples on its from side) and is=1 adds the results

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

  Copyright (c) 1995-2026 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>

  http://zukunft.com

*/

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::EXPORT . 'phrase_data.php';
include_once paths::MODEL_HELPER . 'db_cache_page.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'phrase_data_walks.php';

use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\export\phrase_data;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\phrase_data_walks;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("phraseData", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    $phr_id = $_GET[url_var::ID] ?? '';
    $incl_res_text = $_GET[url_var::INCL_RESULTS] ?? '';
    $walk_text = $_GET[url_var::PHRASE_DATA_WALK] ?? '';
    $walk = phrase_data_walks::DEFAULT;
    if ($walk_text != '') {
        $walk = phrase_data_walks::tryFrom($walk_text);
        if ($walk == null) {
            $msg->add(msg_id::URL_MAP_MISSING, [
                msg_id::VAR_URL_KEY => $walk_text,
                msg_id::VAR_NAME => url_var::PHRASE_DATA_WALK
            ]);
        }
    }

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    $cac_key = '';
    $cac_page = '';
    if ($usr->id > 0 and $walk != null) {
        $cac_par = [
            url_var::ID => $phr_id,
            url_var::PHRASE_DATA_WALK => $walk->value,
            url_var::INCL_RESULTS => $incl_res_text
        ];
        $cac_key = db_cache_page::api_key(phrase_data::class, $cac_par, $usr->id);
        $cac_page = new db_cache_page();
        $result = $cac_page->json_by_key($cac_key, $msg);
    }
    // without a user or with an unknown walk the result stays empty, which is no cached answer
    $cac_ready = ($usr->id > 0 and $walk != null);
    $cac_hit = ($cac_ready and $result !== null);
    if ($cac_ready and $result === null) {
        // the snap is taken before the data is read, so a change during the read outdates the entry
        $snap_time = new DateTime();
        $phd = new phrase_data();
        $vars = $phd->export_json((int)$phr_id, $walk, $incl_res_text == url_var::TRUE, $msg);
        $result = json_encode($vars);
        // e.g. the answer for an unknown phrase id is not cached, so that the phrase can be added later
        if ($msg->is_ok()) {
            $cac_page->save_json($cac_key, $result, $snap_time, $msg);
        }
    }

    $ctrl = new controller();
    $ctrl->cache_header($cac_hit);
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con, $msg);
}
