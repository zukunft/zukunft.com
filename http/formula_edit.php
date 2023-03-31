<?php

/*

  formula_edit.php - change a formula
  ----------------
  
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

use controller\controller;
use html\html_base;
use model\formula;
use model\phrase;
use model\user;
use model\view;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

$db_con = prg_start("formula_edit");
$html = new html_base();

// get the parameters
$frm_id = $_GET[controller::URL_VAR_ID] ?? 0;

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->load_by_code_id(view::FORMULA_EDIT);
    $back = $_GET['back'];

    // create the formula object to have a place to update the parameters
    $frm = new formula($usr);
    $frm->load_by_id($frm_id);

    // load the parameters to the formula object to display the user input again in case of an error
    if (isset($_GET['formula_name'])) {
        $frm->set_name($_GET['formula_name']);
    } // the new formula name
    if (isset($_GET['formula_text'])) {
        $frm->usr_text = $_GET['formula_text'];
    } // the new formula text in the user format
    if (isset($_GET['description'])) {
        $frm->description = $_GET['description'];
    }
    if (isset($_GET['type'])) {
        $frm->type_id = $_GET['type'];
    }
    if ($_GET['need_all_val'] == 'on') {
        $frm->need_all_val = true;
    } else {
        if ($_GET['confirm'] == 1) {
            $frm->need_all_val = false;
        }
    }
    //if (isset($_GET['need_all_val']))  { if ($_GET['need_all_val'] == 'on') { $frm->need_all_val = true; } else { $frm->need_all_val = false; } }

    if ($frm->id() <= 0) {
        $result .= log_err("No formula found to change because the id is missing.", "/http/formula_edit.php");
    } else {

        // do the direct changes initiated by other buttons than the save button
        // to link the formula to another word
        if ($_GET['link_phrase'] > 0) {
            $phr = new phrase($usr);
            $phr->set_id($_GET['link_phrase']);
            $phr->load_by_obj_par();
            $upd_result = $frm->link_phr($phr);
        }

        // to unlink a word from the formula
        if ($_GET['unlink_phrase'] > 0) {
            $phr = new phrase($usr);
            $phr->set_id($_GET['unlink_phrase']);
            $phr->load_by_obj_par();
            $upd_result = $frm->unlink_phr($phr);
        }

        // if the save button has been pressed at least the name is filled (an empty name should never be saved; instead the word should be deleted)
        if ($frm->usr_text <> '') {

            // update the formula if it has been changed
            $upd_result = $frm->save();

            // if update was successful ...
            if (str_replace('1', '', $upd_result) == '') {
                // ... display the calling view
                // because formula changing may need several updates the edit view is shown again
                //$result .= dsp_go_back($back, $usr);

                // trigger to update the related formula values / results
                if ($frm->needs_fv_upd) {
                    // update the formula results
                    $phr_lst = $frm->assign_phr_lst();
                    //$fv_list = $frm->calc($phr_lst);
                }
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result;
            }
        }

        // if nothing yet done display the edit view (and any message on the top)
        if ($result == '') {
            // display the view header
            $result .= $dsp->dsp_navbar($back);
            $result .= $html->dsp_err($msg);

            // display the view to change the formula
            $frm->load_by_id($frm_id); // reload to formula object to display the real database values
            if (isset($_GET['add_link'])) {
                $add_link = $_GET['add_link'];
            } else {
                $add_link = 0;
            }
            $result .= $frm->dsp_edit($add_link, 0, $back); // with add_link to add a link and display a word selector
        }
    }
}

echo $result;

prg_end($db_con);
