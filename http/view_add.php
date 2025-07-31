<?php

/*

  view_add.php - create a new view
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

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use cfg\const\paths;

include_once paths::SHARED_CONST . 'views.php';

use cfg\user\user;
use cfg\view\view;
use cfg\word\word;
use html\html_base;
use html\view\view as view_dsp;
use shared\api;
use shared\const\views as view_shared;

// open database
$db_con = prg_start("view_add");
$html = new html_base();

global $sys_msk_cac;

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
    $msk->load_by_id($sys_msk_cac->id(view_shared::VIEW_ADD));
    $back = $_GET[api::URL_VAR_BACK] = ''; //

    // create the object to store the parameters so that if the add form is shown again it is already filled
    $msk_add = new view($usr);

    // load the parameters to the view object to display the user input again in case of an error
    if (isset($_GET[api::URL_VAR_NAME])) {
        $msk_add->set_name($_GET[api::URL_VAR_NAME]);
    }    // name of the new view to add
    if (isset($_GET[api::URL_VAR_COMMENT])) {
        $msk_add->description = $_GET[api::URL_VAR_COMMENT];
    }
    if (isset($_GET['type'])) {
        $msk_add->type_id = $_GET['type'];
    }

    if ($_GET['confirm'] > 0) {

        // check essential parameters
        if ($_GET[api::URL_VAR_NAME] == "") {
            $msg .= 'Name missing; Please press back and enter a name for the new view.';
        } else {

            $add_result = $msk_add->save()->get_last_message();

            // if adding was successful ...
            if (str_replace('1', '', $add_result) == '') {
                // TODO call the dsp_edit view and set the new view as the default view for the sample term
                // display the calling view (or call the view component edit
                $result .= $html->dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $add_result;
            }
        }
    }

    // if nothing yet done display the add view (and any message on the top)
    if ($result == '') {
        // sample word that is used to simulate the view changes
        $wrd = new word($usr);
        //$wrd->type_id = $view_type;
        if ($_GET['word'] > 0) {
            $wrd->load_by_id($_GET['word']);
        }

        // show the header (in view edit views the view cannot be changed)
        $msk_dsp = new view_dsp($msk->api_json());
        $result .= $msk_dsp->dsp_navbar_no_view($wrd->id());
        $result .= $html->dsp_err($msg);

        // show the form to create a new view
        $msk_add_dsp = new view_dsp($msk_add->api_json());
        $result .= $msk_add_dsp->dsp_edit(0, $wrd, $back);
    }
}

echo $result;

prg_end($db_con);
