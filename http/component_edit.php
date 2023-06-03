<?php

/*

  component_edit.php - adjust a view element
  -----------------------
  
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
use html\html_base;
use html\view\view_dsp_old;
use model\user;
use model\component_dsp_old;
use model\word;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("component_edit");
$html = new html_base();

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    $upd_result = '';

    $usr->load_usr_data();

    // get the view component id
    if (!isset($_GET['id'])) {
        log_info("The view component id must be set to display a view.", "component_edit.php", '', (new Exception)->getTraceAsString(), $usr);
    } else {
        // init the display object to show the standard elements such as the header
        $dsp = new view_dsp_old($usr);

        // create the view component object to apply the user changes to it
        $cmp = new component_dsp_old($usr);
        $result .= $cmp->load_by_id($_GET['id']);

        // get the word used as a sample to illustrate the changes
        $wrd = new word($usr);
        if (isset($_GET['word'])) {
            $result .= $wrd->load_by_id($_GET['word']);
        } else {
            // get the default word for the view $dsp
        }

        // the calling stack to move back to page where the user has come from after editing the view component is done
        $back = $_GET['back'];

        // save the direct changes
        // link or unlink a view
        $dsp_link_id = $_GET['link_view'];    // to link the view component to another view
        if ($dsp_link_id > 0) {
            $dsp_link = new view_dsp_old($usr);
            $result .= $dsp_link->load_by_id($dsp_link_id);
            $order_nbr = $cmp->next_nbr($dsp_link_id);
            $upd_result = $cmp->link($dsp_link, $order_nbr);
        }

        $dsp_unlink_id = $_GET['unlink_view'];  // to unlink a view component from the view
        if ($dsp_unlink_id > 0) {
            $dsp_unlink = new view_dsp_old($usr);
            $result .= $dsp_unlink->load_by_id($dsp_unlink_id);
            $upd_result .= $cmp->unlink($dsp_unlink);
        }

        // if the save button has been pressed (an empty view component name should never be saved; instead the view should be deleted)
        $cmp_name = $_GET['name'];
        if ($cmp_name <> '') {

            // save the user changes in the database
            $upd_result = '';

            // get other field parameters
            if (isset($_GET['name'])) {
                $cmp->set_name($_GET['name']);
            }
            if (isset($_GET['comment'])) {
                $cmp->description = $_GET['comment'];
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
            // in view edit views the view cannot be changed
            $result .= $dsp->dsp_navbar_no_view($wrd->id());
            $result .= $html->dsp_err($msg);

            // if the user has requested to use this display component also in another view, $add_link is greater than 0
            $add_link = 0;
            if (isset($_GET['add_link'])) {
                $add_link = $_GET['add_link'];
            }

            // show the word and its relations, so that the user can change it
            $result .= $cmp->dsp_edit($add_link, $wrd, $back);
        }
    }
}

echo $result;

prg_end($db_con);