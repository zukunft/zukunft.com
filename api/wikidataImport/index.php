<?php

/*

    api/wikidataImport/index.php - the preview of the import of the values of wikidata
    ----------------------------

    a GET with the id of a phrase (url_var::PHRASE) or with a wikidata id (url_var::EXTERNAL_KEY)
    answers with the preview of the import (see cfg/import/import_wikidata_preview.php), so that the
    user sees the new words, triples and values and the conflicts before anything is written; the
    answer is an import and no changed phrase, so it has its own controller unlike the wikipedia
    refresh of api/word/index.php

    the confirmed import is written by the html frontend in its own process like every confirmed
    change (see frontend::import_confirmed), because the api write guard refuses a call of the
    frontend without the token of the session


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

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::MODEL_HELPER . 'server_guard.php';
include_once paths::MODEL_IMPORT . 'import_wikidata_preview.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata_preview;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("wikidataImport", $msg);

if ($db_con->is_open()) {

    // load the session user parameters and store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $ctrl = new controller();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {
        // the preview is for the browsing user, because a conflict depends on the own values of the user
        $load_usr = $usr->data_user($_GET[url_var::USER] ?? 0, $msg, server_guard::from_own_pod());
        $preview = new import_wikidata_preview($load_usr);
        $phr_id = (int)($_GET[url_var::PHRASE] ?? 0);
        $key = (string)($_GET[url_var::EXTERNAL_KEY] ?? '');
        $vars = [];
        if ($phr_id != 0) {
            $vars = $preview->preview_of_phrase($phr_id, $msg);
        } elseif ($key != '') {
            $vars = $preview->preview_of_key($key, $key, $msg);
        } else {
            $msg->add_message_text('phrase id or wikidata id is missing');
        }
        // with the message header like the other api answers, so that the frontend finds the body
        $api_msg = new api_message();
        $site = $api_msg->api_site_name($db_con);
        $result = json_encode($api_msg->api_header_array($site, import_wikidata_preview::class, $load_usr, $vars));
        $ctrl->get_json($result, $msg);
    } else {
        $ctrl->not_permitted($msg);
    }

    $app->end_api($db_con, $msg);
}
