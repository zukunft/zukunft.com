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
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use cfg\const\paths;
use html\const\paths as html_paths;

include_once paths::SHARED_CONST . 'views.php';
include_once html_paths::VERB . 'verb.php';

use cfg\user\user;
use cfg\verb\verb;
use cfg\view\view;
use html\html_base;
use html\verb\verb as verb_dsp;
use html\view\view as view_dsp;
use shared\api;
use shared\const\views as view_shared;

// open database
$db_con = prg_start("verb_edit");
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
    $msk->load_by_code_id(view_shared::VERB_EDIT);
    $back = $_GET[api::URL_VAR_BACK] = ''; // the original calling page that should be shown after the change is finished

    // create the verb object to have an place to update the parameters
    $vrb = new verb;
    $vrb->set_user($usr);
    $vrb->load_by_id($_GET[api::URL_VAR_ID]);

    if ($vrb->id() <= 0) {
        $result .= log_err("No verb found to change because the id is missing.", "verb_edit.php");
    } else {

        // if the save button has been pressed at least the name is filled (an empty name should never be saved; instead the word should be deleted)
        if ($_GET[api::URL_VAR_NAME] <> '') {

            // get the parameters (but if not set, use the database value)
            if (isset($_GET[api::URL_VAR_NAME])) {
                $vrb->set_name($_GET[api::URL_VAR_NAME]);
            }
            if (isset($_GET[api::URL_VAR_PLURAL])) {
                $vrb->set_plural($_GET[api::URL_VAR_PLURAL]);
            }
            if (isset($_GET[api::URL_VAR_REVERSE])) {
                $vrb->set_reverse($_GET[api::URL_VAR_REVERSE]);
            }
            if (isset($_GET[api::URL_VAR_REVERSE_PLURAL])) {
                $vrb->set_reverse_plural($_GET[api::URL_VAR_REVERSE_PLURAL]);
            }

            // save the changes
            $upd_result = $vrb->save()->get_last_message();

            // if update was successful ...
            if (str_replace('1', '', $upd_result) == '') {
                // remember the verb for the next values to add
                $usr->set_verb($vrb->id());

                // ... and display the calling view
                $result .= $html->dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result;
            }

        }

        // if nothing yet done display the add view (and any message on the top)
        if ($result == '') {
            // show the header
            $msk_dsp = new view_dsp($msk->api_json());
            $result .= $msk_dsp->dsp_navbar($back);
            $result .= $html->dsp_err($msg);

            // show the verb and its relations, so that the user can change it
            $vrb_dsp = new verb_dsp($vrb->api_json());
            $result .= $vrb_dsp->dsp_edit($back);
        }
    }
}

echo $result;

prg_end($db_con);