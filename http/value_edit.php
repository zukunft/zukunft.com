<?php

/*

  value_edit.php - change a single value
  --------------

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
use controller\controller;
use html\html_base;
use html\view\view as view_dsp;
use cfg\user;
use cfg\value;
use cfg\view;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("value_edit");
$html = new html_base();

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(controller::DSP_VALUE_EDIT);
    $back = $_GET[controller::API_BACK];     // the word id from which this value change has been called (maybe later any page)

    // create the value object to store the parameters so that if the edit form is shown again it is already filled
    $val = new value($usr);
    $val->load_by_id($_GET[controller::URL_VAR_ID]); // to load any missing parameters of the edit view like the group and phrases from the database

    if ($val->id() <= 0) {
        $result .= log_err("Value id missing for value_edit called from " . $back, "value_edit.php");
    } else {

        // update the parameters on the object, so that the object save can update the database
        if (isset($_GET['value'])) {
            $val->usr_value = $_GET['value'];
        }  // the value as changed by the user
        if (isset($_GET['source'])) {
            $val->set_source_id($_GET['source']);
        } // the source id as changed by the user
        //if (isset($_GET['time']))   { $val->time_id   = $_GET['time']; }   // the time word separate to the other phrases

        // before the value conversion, all phrases should be loaded to use the updated words for the conversion e.g. percent
        // get the linked phrases from url
        $phr_ids = array(); // suggested words for the new value that the user can change; a negative value links to a triple
        $type_ids = array(); // word to preselect the suggested words e.g. "Country" to list all their countries first for the suggested word
        // if the type id is -1 the word is not supposed to be adjusted e.g. when editing a table cell
        if (isset($_GET['phrase1'])) {
            // ... either from the url to allow editing without saving to the database utils the user confirmed
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
            log_debug("phrases " . implode(",", $phr_ids) . ".");
            log_debug("types " . implode(",", $type_ids) . ".");

            $val->load_by_phr_ids($phr_ids);
        }

        // 'confirm' is 1 if the user has pressed "save"
        if ($_GET['confirm'] > 0) {

            // if a phrase is added or removed used the database value as a fallback
            if ($val->usr_value == '') {
                $val->usr_value = $val->number();
            }
            // an empty value should never be saved; instead the value should be deleted)
            if ($val->usr_value == '') {
                $msg .= 'An empty number should not be saved. Please delete/exclude the value instead.';
            } else {

                // adjust the user input using the phrases given
                $val->convert();

                // save the value change
                $upd_result = $val->save();

                // if update was successful ...
                if (str_replace('1', '', $upd_result) == '') {
                    //$result .= dsp_go_back($back, $usr);
                } else {
                    // ... or in case of a problem prepare to show the message
                    $msg .= $upd_result;
                }
            }
        }

        // if nothing yet done display the edit view (and any message on the top)
        if ($result == '') {
            // show the value and the linked words to edit the value (again after removing or adding a word)
            $msk_dsp = new view_dsp($msk->api_json());
            $result .= $msk_dsp->dsp_navbar($back);
            $result .= $html->dsp_err($msg);

            $result .= $val->dsp_edit($type_ids, $back);
        }
    }
}

echo $result;

prg_end($db_con);