<?php

/*

    api/json/index.php - the json im- and export API controller
    ------------------

    use GET to retrieve a JSON that can be imported into another zukunft.com pod
    of the phrases given by name (wl) or by id (pl) with all related phrases, or the same as xml (ef=efx),
    or the csv of the results of the formulas given by name (ef=efc)
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

include_once paths::MODEL_WORD . 'word.php';
include_once paths::EXPORT . 'export.php';
include_once paths::MODEL_HELPER . 'server_guard.php';
include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\export\export;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("json", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $ctrl = new controller();
    $method = $_SERVER[rest_ctrl::REQUEST_METHOD] ?? rest_ctrl::GET;

    if ($method === rest_ctrl::PUT) {

        // import the json of the body with the standard import as the requesting user
        // e.g. the file of the import button of the calculator editor
        if ($usr->id > 0) {
            if ($ctrl->change_permitted($msg)) {
                $imp = new import();
                $imp->usr = $usr;
                // the import writes its progress for the console, which is no part of the api answer
                ob_start();
                $imp->put_json($ctrl->request_text(), $msg);
                ob_end_clean();
                $ctrl->get_json(json_encode([]), $msg);
            }
        } else {
            $ctrl->not_permitted($msg);
        }
    } else {

        // the phrases by name as a csv line, so that a name may contain a comma, the phrases by id and the format
        $lib = new library();
        $names_txt = $_GET[url_var::WORDS] ?? '';
        $ids_txt = $_GET[url_var::PHRASE_LIST] ?? '';
        $format = $_GET[url_var::EXPORT_FORMAT] ?? url_var::EXPORT_FORMAT_JSON;
        $phr_names = $lib->array_trim($lib->csv_line_to_array($names_txt));
        $phr_ids = $lib->array_trim(explode(',', $ids_txt));

        // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
        $result = '';
        if ($usr->id > 0) {
            // e.g. the export view of the own pod asks for the data of the browsing user (url_var::USER)
            $load_usr = $usr->data_user($_GET[url_var::USER] ?? 0, $msg, server_guard::from_own_pod());
            $exp_msg = new user_message($load_usr); // the export of the data user, merged into the message of the request
            $result = new export()->file_text($phr_names, $phr_ids, $format, $exp_msg);
            $msg->merge($exp_msg);
        }

        // a json answer goes through the controller, an xml or csv answer is sent with its own content type
        if ($format == url_var::EXPORT_FORMAT_JSON or !$msg->is_ok()) {
            $ctrl->get_json($result, $msg);
        } else {
            if (!headers_sent()) {
                header('Content-Type: ' . rest_ctrl::EXPORT_MIME_TYPES[$format]);
            }
            echo $result;
        }
    }


    $app->end_api($db_con, $msg);
}