<?php

/*

  verbs.php - display a list of all verbs to allow an admin user to modify it
  ---------
  
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
// Zukunft.com verb list

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

include_once SHARED_PATH . 'views.php';

use cfg\verb\verb_list;
use html\view\view as view_dsp;
use cfg\user\user;
use cfg\view\view;
use shared\api;
use shared\views as view_shared;

// open database
$db_con = prg_start("verbs");

$result = ''; // reset the html code var
$back = $_GET[api::URL_VAR_BACK] = ''; // the word id from which this value change has been called (maybe later any page)

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $html = new \html\html_base();

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(view_shared::MC_VERBS);

    // show the header
    $msk_dsp = new view_dsp($msk->api_json());
    $result .= $msk_dsp->dsp_navbar($back);

    // display the verb list
    $result .= $html->dsp_text_h2("Word link types");
    $vrb_lst = new verb_list($usr);
    $vrb_lst->load($db_con);
    $result .= $vrb_lst->dsp_list();
    //$result .= zul_dsp_list ($usr->id());
}

echo $result;

// Closing connection
prg_end($db_con);
