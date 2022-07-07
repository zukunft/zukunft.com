<?php

/*

  verb_edit.php - rename and adjust a verb
  ---------------
  
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
include_once '../src/main/php/zu_lib.php';

// open database
$db_con = prg_start("verb_edit");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(db_cl::VIEW, view::VERB_EDIT);
    $dsp->usr = $usr;
    $dsp->load();
    $back = $_GET['back']; // the original calling page that should be shown after the change is finished

    // create the verb object to have an place to update the parameters
    $vrb = new verb;
    $vrb->id = $_GET['id'];
    $vrb->usr = $usr;
    $vrb->load();

    if ($vrb->id <= 0) {
        $result .= log_err("No verb found to change because the id is missing.", "verb_edit.php");
    } else {

        // if the save button has been pressed at least the name is filled (an empty name should never be saved; instead the word should be deleted)
        if ($_GET['name'] <> '') {

            // get the parameters (but if not set, use the database value)
            if (isset($_GET['name'])) {
                $vrb->name = $_GET['name'];
            }
            if (isset($_GET['plural'])) {
                $vrb->plural = $_GET['plural'];
            }
            if (isset($_GET['reverse'])) {
                $vrb->reverse = $_GET['reverse'];
            }
            if (isset($_GET['plural_reverse'])) {
                $vrb->rev_plural = $_GET['plural_reverse'];
            }

            // save the changes
            $upd_result = $vrb->save();

            // if update was successful ...
            if (str_replace('1', '', $upd_result) == '') {
                // remember the verb for the next values to add
                $usr->set_verb($vrb->id);

                // ... and display the calling view
                $result .= dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result;
            }

        }

        // if nothing yet done display the add view (and any message on the top)
        if ($result == '') {
            // show the header
            $result .= $dsp->dsp_navbar($back);
            $result .= dsp_err($msg);

            // show the verb and its relations, so that the user can change it
            $result .= $vrb->dsp_edit($back);
        }
    }
}

echo $result;

prg_end($db_con);