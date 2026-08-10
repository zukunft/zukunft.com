<?php

/*

    api/formulaList/index.php - the formula list API controller: send a list of formulas to the frontend
    -------------------------

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

include_once paths::MODEL_FORMULA . 'formula_list.php';
include_once paths::MODEL_PHRASE . 'phrase.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("formulaList", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    $frm_ids = $_GET[url_var::ID_LST] ?? '';
    $phr_id = $_GET[url_var::PHRASE] ?? 0;

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($frm_ids != '') {
            $lst = new formula_list($usr);
            $lst->load_by_ids(explode(',', $frm_ids), $msg);
            // drop the formulas the requester may not read (idor); see sandbox::is_readable_by
            $lst->filter_readable_by($usr);
            $result = $lst->api_json([], $msg);
        } elseif ($phr_id != 0) {
            // the formulas assigned to a phrase e.g. for the default word page
            $phr = new phrase($usr);
            $phr->set_id((int)$phr_id);
            $lst = new formula_list($usr);
            $lst->load_by_phr($phr, $msg);
            // drop the formulas the requester may not read (idor); see sandbox::is_readable_by
            $lst->filter_readable_by($usr);
            $result = $lst->api_json([], $msg);
        } else {
            $msg->add_message_text('list of formula id or the phrase id is missing');
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);

    $app->end_api($db_con);
}