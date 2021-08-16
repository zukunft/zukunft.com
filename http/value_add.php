<?php

/*

  value_add.php - add a value
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
$db_con = prg_start("value_add");

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
    $dsp->id = cl(db_cl::VIEW, view::VALUE_ADD);
    $dsp->usr = $usr;
    $dsp->load();
    $back = $_GET['back'];     // the word id from which this value change has been called (maybe later any page)

    // create the object to store the parameters so that if the add form is shown again it is already filled
    $val = new value;
    $val->usr = $usr;

    // before the value conversion, all phrases should be loaded to use the updated words for the conversion e.g. percent
    // get the linked phrases from url
    $phr_ids = array(); // suggested word for the new value that the user can change
    $type_ids = array(); // word to preselect the suggested words e.g. "Country" to list all their countries first for the suggested word
    // if the type id is -1 the word is not supposed to be adjusted e.g. when editing a table cell
    if (isset($_GET['phrase1'])) {
        $phr_pos = 1;
        while (isset($_GET['phrase' . $phr_pos])) {
            $phr_ids[] = $_GET['phrase' . $phr_pos];
            if (isset($_GET['type' . $phr_pos])) {
                $type_ids[] = $_GET['type' . $phr_pos];
            } else {
                $type_ids[] = 0;
            }
            $phr_pos++;
        }
        log_debug("value_add -> phrases " . implode(",", $phr_ids) . ".");
        log_debug("value_add -> types " . implode(",", $type_ids) . ".");
        $val->ids = $phr_ids;
    } elseif (isset($_GET['phrases'])) {
        $phr_ids = array();
        if ($_GET['phrases'] <> '') {
            $phr_ids = explode(",", $_GET['phrases']);
        }
        log_debug("value_add -> phrases " . implode(",", $phr_ids) . ".");
        $val->ids = $phr_ids;
    }

    // get the essential parameters for adding a value
    $new_val = $_GET['value'];    // the value as changed by the user

    // if the user has pressed "save" confirm is 1
    if ($_GET['confirm'] > 0 and $new_val <> '') {

        // adjust the user entries for the database
        $val->usr_value = $new_val;
        $val->convert();

        // add the new value to the database
        $upd_result = $val->save();

        // if update was successful ...
        if ($val->id > 0 and str_replace('1', '', $upd_result) == '') {
            log_debug("value_add -> save value done.");
            // update the parameters on the object, so that the object save can update the database
            // save the source id as changed by the user
            if (isset($_GET['source'])) {
                $val->source_id = $_GET['source'];
                if ($val->source_id > 0) {
                    log_debug("value_add -> save source" . $val->source_id . ".");
                    $usr->set_source($val->source_id);
                    $upd_result = $val->save();
                    log_debug("value_add -> save source done.");
                }
            }
        } else {
            $result .= log_err("Adding " . $new_val . " for words " . implode(",", $val->ids) . " failed (" . $upd_result . ").", "value_add");
        }

        log_debug("value_add -> go back to " . $back . ".");
        $result .= dsp_go_back($back, $usr);
    }

    // if nothing yet done display the add view (and any message on the top)
    if ($result == '') {
        // display the view header
        $result .= $dsp->dsp_navbar($back);
        $result .= dsp_err($msg);

        $result .= $val->dsp_edit($type_ids, $back);
    }
}

echo $result;

prg_end($db_con);
