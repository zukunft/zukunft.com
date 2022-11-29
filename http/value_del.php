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
use html\api;
use html\button;
use html\html_base;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// to create the code for the html frontend
$html = new html_base();

// open database
$db_con = prg_start("value_del");

$result = ''; // reset the html code var

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->set_id(cl(db_cl::VIEW, view::VALUE_DEL));
    $dsp->load_obj_vars();
    $back = $_GET['back'];  // the page from which the value deletion has been called

    // get the parameters
    $val_id = $_GET['id'];
    $confirm = $_GET['confirm'];

    if ($val_id > 0) {

        // create the value object to have an object to update the parameters
        $val = new value($usr);
        $val->set_id($val_id);
        $val->load_obj_vars();

        if ($confirm == 1) {
            // actually delete the value (at least for this user)
            $val->del();

            $result .= dsp_go_back($back, $usr);
        } else {
            // display the view header
            $result .= $dsp->dsp_navbar($back);

            $val->load_phrases();
            $url = $html->url(api::VALUE . api::REMOVE, $val_id, $back);
            $result .= (new button('Delete ' . $val->number() . ' for ' . $val->phr_lst->dsp_name() . '? ', $url))->yesno();
        }
    } else {
        $result .= dsp_go_back($back, $usr);
    }
}

echo $result;

prg_end($db_con);
