<?php

/*

  view_component_add.php - adjust a view element
  ----------------------
  
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
$db_con = prg_start("view_component_add");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    $upd_result = '';

    load_usr_data();

    // init the display object to show the standard elements such as the header
    $dsp = new view_dsp;
    $dsp->id = cl(db_cl::VIEW, view::COMPONENT_ADD);
    $dsp->usr = $usr;
    $dsp->load();
    // the calling stack to move back to page where the user has come from after adding the view component is done
    $back = $_GET['back'];

    // create the view component object to apply the user changes to it
    $cmp = new view_component_dsp;
    $cmp->id = $_GET['id'];
    $cmp->usr = $usr;
    $result .= $cmp->load();

    // get the word used as a sample the illustrate the changes
    $wrd = new word;
    if (isset($_GET['word'])) {
        $wrd->id = $_GET['word'];
        $wrd->usr = $usr;
        $result .= $wrd->load();
    } else {
        // get the default word for the view $dsp
    }

    // save the direct changes
    // link or unlink a view
    $dsp_link_id = $_GET['link_view'];    // to link the view component to another view
    if ($dsp_link_id > 0) {
        $dsp_link = new view_dsp;
        $dsp_link->id = $dsp_link_id;
        $dsp_link->usr = $usr;
        $result .= $dsp_link->load();
        $order_nbr = $cmp->next_nbr($dsp_link_id);
        $upd_result = $cmp->link($dsp_link, $order_nbr);
    }

    $dsp_unlink_id = $_GET['unlink_view'];  // to unlink a view component from the view 
    if ($dsp_unlink_id > 0) {
        $dsp_unlink = new view_dsp;
        $dsp_unlink->id = $dsp_unlink_id;
        $dsp_unlink->usr = $usr;
        $result .= $dsp_unlink->load();
        $upd_result .= $cmp->unlink($dsp_unlink);
    }

    // if the save button has been pressed (an empty view component name should never be saved; instead the view should be deleted)
    $cmp_name = $_GET['name'];
    if ($cmp_name <> '') {

        // save the user changes in the database
        $upd_result = '';

        // get other field parameters
        if (isset($_GET['name'])) {
            $cmp->name = $_GET['name'];
        }
        if (isset($_GET['comment'])) {
            $cmp->comment = $_GET['comment'];
        }
        if (isset($_GET['type'])) {
            $cmp->type_id = $_GET['type'];
        } //
        if (isset($_GET['word_row'])) {
            $cmp->word_id_row = $_GET['word_row'];
        } //
        if (isset($_GET['word_col'])) {
            $cmp->word_id_col = $_GET['word_col'];
        } //

        // save the changes
        $upd_result .= $cmp->save();

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
        // in view add views the view cannot be changed
        $result .= $dsp->dsp_navbar_no_view($back);
        $result .= dsp_err($msg);

        // if the user has requested to use this display component also in another view, $add_link is greater than 0
        $add_link = 0;
        if (isset($_GET['add_link'])) {
            $add_link = $_GET['add_link'];
        }

        // show the word and its relations, so that the user can change it
        $result .= $cmp->dsp_add($add_link, $wrd, $back);
    }
}

echo $result;

prg_end($db_con);
