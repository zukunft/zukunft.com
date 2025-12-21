<?php

/*

  api/log/index.php - the change log API controller: send a list of user changes to the frontend
  -----------------
  
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

include_once paths::MODEL_LOG . 'change_log_list.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\library;

// open database
$app = new application();
$db_con = $app->start_api("log");

if ($db_con->is_open()) {

    // get the parameters
    $class = $_GET[url_var::LOG_CLASS] ?? '';
    $id = $_GET[url_var::ID] ?? 0;
    $fld = $_GET[url_var::LOG_FIELD] ?? '';

    // TODO deprecate
    $wrd_id = $_GET[url_var::WORD] ?? 0;
    $wrd_fld = $_GET[url_var::LOG_FIELD] ?? '';

    $msg = '';
    $result = ''; // reset the json message string

    // load the session user parameters
    $usr = new user;
    $msg .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        if ($class != '') {
            $lib = new library();
            $class = $lib->api_name_to_class($class);
            $lst = new change_log_list();
            if (is_numeric($id)) {
                $id = (int)$id;
            }
            $lst->load_by_obj_fld($class, $id, $usr, $fld);
            $result = $lst->api_json();
        } else {
            // TODO deprecate
            if ($wrd_id != 0) {
                $wrd = new word($usr);
                $wrd->load_by_id($wrd_id);
                $lst = new change_log_list();
                $lst->load_by_fld_of_wrd($wrd, $usr, $wrd_fld);
                $result = $lst->api_json();
            } else {
                $msg = 'word id missing';
            }
        }
    }

    $ctrl = new controller();
    $ctrl->get_json($result, $msg);

    $app->end_api($db_con);
}
