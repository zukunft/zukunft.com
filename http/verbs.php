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
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("verbs");

$result = ''; // reset the html code var
$back = $_GET['back']; // the word id from which this value change has been called (maybe later any page)

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->id = cl(db_cl::VIEW, view::VERBS);
    $dsp->set_user($usr);
    $dsp->load_obj_vars();

    // show the header
    $result .= $dsp->dsp_navbar($back);

    // display the verb list
    $result .= dsp_text_h2("Word link types");
    $vrb_lst = new verb_list($usr);
    $vrb_lst->load($db_con);
    $result .= $vrb_lst->dsp_list();
    //$result .= zul_dsp_list ($usr->id);
}

echo $result;

// Closing connection
prg_end($db_con);
