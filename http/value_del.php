<?php

/*

  value_del.php - delete a value
  -------------
  
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

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use cfg\const\paths;

include_once paths::SHARED_CONST . 'views.php';

use cfg\user\user;
use cfg\value\value;
use cfg\view\view;
use html\button;
use html\html_base;
use html\rest_call;
use html\view\view as view_dsp;
use shared\url_var;
use shared\const\views as view_shared;
use shared\enum\messages as msg_id;

// to create the code for the html frontend
$html = new html_base();

// open database
$db_con = prg_start("value_del");

global $mtr;

$result = ''; // reset the html code var

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(view_shared::VALUE_DEL);
    $back = $_GET[url_var::BACK] = '';  // the page from which the value deletion has been called

    // get the parameters
    $val_id = $_GET[url_var::ID];
    $confirm = $_GET['confirm'];

    if ($val_id > 0) {

        // create the value object to have an object to update the parameters
        $val = new value($usr);
        $val->load_by_id($val_id);

        if ($confirm == 1) {
            // actually delete the value (at least for this user)
            $val->del();

            $result .= $html->dsp_go_back($back, $usr);
        } else {
            // display the view header
            $msk_dsp = new view_dsp($msk->api_json());
            $result .= $msk_dsp->dsp_navbar($back);

            $val->load_phrases();
            $url = $html->url(rest_ctrl::VALUE . rest_ctrl::REMOVE, $val_id, $back);
            $result .= (new button($url, $back))->yes_no(
                msg_id::VALUE_DEL->value, $val->number() . $mtr->txt(msg_id::FOR) . $val->phr_lst()->dsp_name() . '?');
        }
    } else {
        $result .= $html->dsp_go_back($back, $usr);
    }
}

echo $result;

prg_end($db_con);
