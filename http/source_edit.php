<?php

/*

  source_edit.php - rename and adjust a source
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

// open database
$db_con = prg_start("source_edit");

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
    $dsp->id = clo(DBL_VIEW_SOURCE_EDIT);
    $dsp->usr = $usr;
    $dsp->load();
    $back = $_GET['back']; // the original calling page that should be shown after the change if finished

    // create the source object to have an place to update the parameters
    $src = new source;
    $src->id = $_GET['id'];
    $src->usr = $usr;
    $src->load();

    if ($src->id <= 0) {
        $result .= log_err("No source found to change because the id is missing.", "source_edit.php");
    } else {

        // if the save button has been pressed at least the name is filled (an empty name should never be saved; instead the word should be deleted)
        if ($_GET['name'] <> '') {

            // get the parameters (but if not set, use the database value)
            if (isset($_GET['name'])) {
                $src->name = $_GET['name'];
            }
            if (isset($_GET['url'])) {
                $src->url = $_GET['url'];
            }
            if (isset($_GET['comment'])) {
                $src->comment = $_GET['comment'];
            }

            // save the changes
            $upd_result = $src->save();

            // if update was successful ...
            if (str_replace('1', '', $upd_result) == '') {
                // remember the source for the next values to add
                $usr->set_source($src->id);

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

            // show the source and its relations, so that the user can change it
            $result .= $src->dsp_edit($back);
        }
    }
}

echo $result;

prg_end($db_con);
