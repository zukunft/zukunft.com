<?php

/*

  view_edit.php - design a view by adding or moving the view elements
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

// open database
$db_con = prg_start("view_edit");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    $upd_result = '';

    load_usr_data();

    // prepare the display to edit the view
    $dsp = new view_dsp;
    $dsp->id = cl(db_cl::VIEW, view::ADD);
    $dsp->usr = $usr;
    $dsp->load();
    $back = $_GET['back'];

    // create the view object that the user can change
    $dsp_edit = new view_dsp;
    $dsp_edit->id = $_GET['id'];
    $dsp_edit->usr = $usr;
    $result .= $dsp_edit->load();

    // get the view id to adjust
    if ($dsp_edit->id <= 0) {
        log_info("The view id must be set to display a view.", "view_edit.php", '', (new Exception)->getTraceAsString(), $usr);
    } else {

        // get the word used as a sample the show the changes
        $wrd = new word($usr);
        $wrd->id = $_GET['word'];
        $result .= $wrd->load();

        // save the direct changes
        // ... of the element list
        if (isset($_GET['move_up'])) {
            $upd_result = $dsp_edit->entry_up($_GET['move_up']);
            if (str_replace('1', '', $upd_result) <> '') {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result;
            }
        }

        if (isset($_GET['move_down'])) {
            $upd_result .= $dsp_edit->entry_down($_GET['move_down']);
            if (str_replace('1', '', $upd_result) <> '') {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result;
            }
        }

        // unlink an entry
        if (isset($_GET['del'])) {
            $cmp = new view_cmp($usr);
            $cmp->id = $_GET['del'];
            $cmp->load();
            $cmp->unlink($dsp_edit);
        }

        // check if a existing view element should be added
        if (isset($_GET['add_view_component'])) {
            if ($_GET['add_view_component'] > 0) {
                $cmp = new view_cmp($usr);
                $cmp->id = $_GET['add_view_component'];
                $cmp->load();
                $order_nbr = $cmp->next_nbr($dsp_edit->id);
                $cmp->link($dsp_edit, $order_nbr);
            }
        }

        // check if a new view element should be added
        if (isset($_GET['entry_name']) and isset($_GET['new_entry_type'])) {
            if ($_GET['entry_name'] <> '' and $_GET['new_entry_type'] > 0) {
                $cmp = new view_cmp($usr);
                $cmp->name = $_GET['entry_name'];
                $add_result = $cmp->save();
                if ($add_result == '') {
                    $cmp->load();
                    if ($cmp->id > 0) {
                        $cmp->type_id = $_GET['new_entry_type'];
                        $cmp->save();
                        $order_nbr = $cmp->next_nbr($dsp_edit->id);
                        $cmp->link($dsp_edit, $order_nbr);
                    }
                }
            }
        }

        // if the save button has been pressed (an empty view name should never be saved; instead the view should be deleted)
        $dsp_name = $_GET['name'];
        if ($dsp_name <> '') {


            // get other field parameters that should be saved
            if (isset($_GET['name'])) {
                $dsp_edit->name = $_GET['name'];
            }
            if (isset($_GET['comment'])) {
                $dsp_edit->comment = $_GET['comment'];
            }
            if (isset($_GET['type'])) {
                $dsp_edit->type_id = $_GET['type'];
            } //

            // save the changes
            $upd_result = $dsp_edit->save();

            // if update was fine ...
            if (str_replace('1', '', $upd_result) == '') {
                // ... display the calling page (switched off because it seems more useful it the user goes back by selecting the related word)
                // $result .= dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result;
            }
        }

        // if nothing yet done display the add view (and any message on the top)
        if ($result == '') {
            // in view edit views the view cannot be changed
            $result .= $dsp->dsp_navbar_no_view($back);
            $result .= dsp_err($msg);

            // get parameters that change only dsp_edit
            // if the user has requested to add another display component to this view, $add_cmp is greater than 0
            $add_cmp = 0;
            if (isset($_GET['add_entry'])) {
                $add_cmp = $_GET['add_entry'];
            }

            // show the word and its relations, so that the user can change it
            $result .= $dsp_edit->dsp_edit($add_cmp, $wrd, $back);
        }
    }
}

echo $result;

prg_end($db_con);