<?php

/*

    link_edit.php - change a triple
    ------------

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
use html\view\view as view_dsp;
use html\word\triple as triple_dsp;
use cfg\triple;
use cfg\user;
use cfg\view;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("link_edit");
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
    $msk->load_by_code_id(controller::MC_TRIPLE_EDIT);
    $back = $_GET[controller::API_BACK]; // the original calling page that should be shown after the change if finished

    // create the link object to have a place to update the parameters
    $trp = new triple($usr);
    $trp->load_by_id($_GET[controller::URL_VAR_ID]);

    // edit the link or ask for confirmation
    if ($trp->id() <= 0) {
        $result .= log_err("No triple found to change because the id is missing.", "link_edit.php");
    } else {

        if ($_GET['confirm'] == 1) {

            // get the parameters
            $trp->fob->set_id($_GET['phrase1']); // the word or triple linked from
            $trp->verb->set_id($_GET['verb']);    // the link type (verb)
            $trp->tob->set_id($_GET['phrase2']); // the word or triple linked to

            // save the changes
            $upd_result = $trp->save();

            // if update was successful ...
            if (str_replace('1', '', $upd_result) == '') {
                // ... display the calling view
                $result .= $html->dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result;
            }
        }

        // if nothing yet done display the add view (and any message on the top)
        if ($result == '') {
            // display the view header
            $msk_dsp = new view_dsp($msk->api_json());
            $result .= $msk_dsp->dsp_navbar($back);
            $result .= $html->dsp_err($msg);

            // display the triple to allow the user to change it
            $trp_html = new triple_dsp($trp->api_json());
            $result .= $trp_html->form_edit($back);
        }
    }
}

echo $result;

prg_end($db_con);