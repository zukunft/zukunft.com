<?php

/*

  link_add.php - create a triple
  ------------
  
  LINK a new word with a link type that has not yet been used
  means ADD a new link type, not simply link an additional word to a value
  
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
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("link_add");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
echo $usr->get(); // if the usr identification fails, show any message immediately because this should never happen

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->id = cl(db_cl::VIEW, view::LINK_ADD);
    $dsp->load();
    $back = $_GET['back'];      // the calling word which should be displayed after saving

    // create the object to store the parameters so that if the add form is shown again it is already filled
    $lnk = new word_link($usr);

    // load the parameters to the triple object to display it again in case of an error
    if (isset($_GET['from'])) {
        $lnk->from->id = $_GET['from'];
    }   // the word or triple to be linked
    if (isset($_GET['verb'])) {
        $lnk->verb->id = $_GET['verb'];
    }   // the link type (verb)
    if (isset($_GET['phrase'])) {
        $lnk->to->id = $_GET['phrase'];
    }

    // if the user has pressed save at least once
    if ($_GET['confirm'] == 1) {

        // check essential parameters
        if ($lnk->from->id == 0 or $lnk->verb->id == 0 or $lnk->to->id == 0) {
            $msg .= 'Please select two words and a verb.';
        } else {

            $add_result = $lnk->save();

            // if adding was successful ...
            if (str_replace('1', '', $add_result) == '') {
                // ... and display the calling view
                $result .= dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $add_result;
            }
        }
    }

    // if nothing yet done display the add view (and any message on the top)
    if ($result == '') {
        // display the add view again
        $result .= $dsp->dsp_navbar($back);
        $result .= dsp_err($msg);

        // display the form to create a new triple
        $result .= $lnk->dsp_add($back);
    }
}

echo $result;

prg_end($db_con);
