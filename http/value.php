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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

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
if ($usr->id > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp($usr);
    $dsp->id = cl(db_cl::VIEW, view::VALUE_DISPLAY);
    $dsp->load();
    $back = $_GET['back']; // the page (or phrase id) from which formula testing has been called

    $result .= $dsp->dsp_navbar($back);

    if ($wrd_names <> '') {

        // load the words
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(explode(",", $wrd_names));

        $result .= $wrd_lst->name_linked();
        $result .= ' = ';
        $val = $wrd_lst->value();
        $result .= $val->display_linked($back);
    }
}

echo $result;

prg_end($db_con);
