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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

include_once SHARED_PATH . 'views.php';

use cfg\value\value_base;
use html\html_base;
use html\view\view as view_dsp;
use html\value\value as value_dsp;
use cfg\user\user;
use cfg\view\view;
use shared\api;
use shared\views as view_shared;

// open database
$db_con = prg_start("value_add");
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
    $msk->load_by_code_id(view_shared::MC_VALUE_ADD);
    $back = $_GET[api::URL_VAR_BACK] = '';     // the word id from which this value change has been called (maybe later any page)

    // create the object to store the parameters so that if the add form is shown again it is already filled
    $val = new value($usr);

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
        log_debug("phrases " . implode(",", $phr_ids) . ".");
        log_debug("types " . implode(",", $type_ids) . ".");
        $val->load_by_phr_ids($phr_ids);
    } elseif (isset($_GET['phrases'])) {
        $phr_ids = array();
        if ($_GET['phrases'] <> '') {
            $phr_ids = explode(",", $_GET['phrases']);
        }
        log_debug("phrases " . implode(",", $phr_ids) . ".");
        $val->load_by_phr_ids($phr_ids);
    }

    // get the essential parameters for adding a value
    $new_val = $_GET['value'];    // the value as changed by the user

    // if the user has pressed "save" confirm is 1
    if ($_GET['confirm'] > 0 and $new_val <> '') {

        // adjust the user entries for the database
        $val->usr_value = $new_val;
        $val->convert();

        // add the new value to the database
        $upd_result = $val->save()->get_last_message();

        // if update was successful ...
        if ($val->id() > 0 and str_replace('1', '', $upd_result) == '') {
            log_debug("save value done.");
            // update the parameters on the object, so that the object save can update the database
            // save the source id as changed by the user
            if (isset($_GET['source'])) {
                $val->set_source_id($_GET['source']);
                if ($val->get_source_id() > 0) {
                    log_debug("save source" . $val->get_source_id() . ".");
                    $usr->set_source($val->get_source_id());
                    $upd_result = $val->save()->get_last_message();
                    log_debug("save source done.");
                }
            }
        } else {
            $result .= log_err("Adding " . $new_val . " for phrases " . $val->grp->dsp_id() . " failed (" . $upd_result . ").", "value_add");
        }

        log_debug("go back to " . $back . ".");
        $result .= $html->dsp_go_back($back, $usr);
    }

    // if nothing yet done display the add view (and any message on the top)
    if ($result == '') {
        // display the view header
        $msk_dsp = new view_dsp($msk->api_json());
        $result .= $msk_dsp->dsp_navbar($back);
        $result .= $html->dsp_err($msg);

        $val_dsp = new value_dsp($val->api_json());
        $result .= $val_dsp->dsp_edit($type_ids, $back);
    }
}

echo $result;

prg_end($db_con);
