<?php

/*

  value.php - display a value
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

// standard zukunft header for callable php files to allow debugging and lib loading
use cfg\view;
use controller\controller;
use html\view\view as view_dsp;
use html\value\value as value_dsp;
use cfg\user;
use cfg\word_list;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("value");

// get the parameters
$wrd_names = $_GET['t'];
log_debug("value for " . $wrd_names);

$result = ''; // reset the html code var

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(controller::DSP_VALUE_DISPLAY);
    $back = $_GET[controller::API_BACK]; // the page (or phrase id) from which formula testing has been called

    $msk_dsp = new view_dsp($msk->api_json());
    $result .= $msk_dsp->dsp_navbar($back);

    if ($wrd_names <> '') {

        // load the words
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(explode(",", $wrd_names));

        $result .= $wrd_lst->dsp_obj()->display();
        $result .= ' = ';
        $val = $wrd_lst->value();
        $val_dsp = new value_dsp($val->api_json());
        $result .= $val_dsp->display_linked($back);
    }
}

echo $result;

prg_end($db_con);
