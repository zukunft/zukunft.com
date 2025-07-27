<?php

/*

  component_edit.php - adjust a view element
  ------------------
  
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

use cfg\const\paths;

include_once paths::SHARED . 'json_fields.php';

use cfg\component\component;
use cfg\user\user;
use cfg\view\view;
use cfg\word\word;
use html\html_base;
use html\view\view as view_dsp;
use html\component\component_exe as component_dsp;
use shared\api;
use shared\json_fields;

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
    if (!isset($_GET[api::URL_VAR_ID])) {
        log_info("The view component id must be set to display a view.", "component_edit.php", '', (new Exception)->getTraceAsString(), $usr);
    } else {
        // init the display object to show the standard elements such as the header
        $msk = new view($usr);

        // create the view component object to apply the user changes to it
        $cmp = new component($usr);
        $result .= $cmp->load_by_id($_GET[api::URL_VAR_ID]);

        // get the word used as a sample to illustrate the changes
        $wrd = new word($usr);
        if (isset($_GET['word'])) {
            $result .= $wrd->load_by_id($_GET['word']);
        } else {
            // get the default word for the view $msk
        }

        // the calling stack to move back to page where the user has come from after editing the view component is done
        $back = $_GET[api::URL_VAR_BACK] = '';

        // save the direct changes
        // link or unlink a view
        $dsp_link_id = $_GET['link_view'];    // to link the view component to another view
        if ($dsp_link_id > 0) {
            $dsp_link = new view($usr);
            $result .= $dsp_link->load_by_id($dsp_link_id);
            $order_nbr = $cmp->next_nbr($dsp_link_id);
            $upd_result = $cmp->link($dsp_link, $order_nbr);
        }

        $dsp_unlink_id = $_GET['unlink_view'];  // to unlink a view component from the view
        if ($dsp_unlink_id > 0) {
            $dsp_unlink = new view($usr);
            $result .= $dsp_unlink->load_by_id($dsp_unlink_id);
            $upd_result .= $cmp->unlink($dsp_unlink);
        }

        // if the save button has been pressed (an empty view component name should never be saved; instead the view should be deleted)
        $cmp_name = $_GET[api::URL_VAR_NAME];
        if ($cmp_name <> '') {

            // save the user changes in the database
            $upd_result = '';

            // get other field parameters
            if (isset($_GET[api::URL_VAR_NAME])) {
                $cmp->set_name($_GET[api::URL_VAR_NAME]);
            }
            if (isset($_GET[api::URL_VAR_COMMENT])) {
                $cmp->description = $_GET[api::URL_VAR_COMMENT];
            }
            if (isset($_GET['type'])) {
                $cmp->type_id = $_GET['type'];
            } //
            if (isset($_GET[json_fields::PHRASE_ROW])) {
                $cmp->load_row_phrase($_GET[json_fields::PHRASE_ROW]);
            } //
            if (isset($_GET[json_fields::PHRASE_COL])) {
                $cmp->load_col_phrase($_GET[json_fields::PHRASE_ROW]);
            } //

            // save the changes
            $upd_result .= $cmp->save()->get_last_message();

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
            $msk_dsp = new view_dsp($msk->api_json());
            $result .= $msk_dsp->dsp_navbar_no_view($wrd->id());
            $result .= $html->dsp_err($msg);

            // if the user has requested to use this display component also in another view, $add_link is greater than 0
            $add_link = 0;
            if (isset($_GET['add_link'])) {
                $add_link = $_GET['add_link'];
            }

            // show the word and its relations, so that the user can change it
            $cmp_dsp = new component_dsp($cmp->api_json());
            $result .= $cmp_dsp->dsp_edit($add_link, $wrd, $back);
        }
    }
}

echo $result;

prg_end($db_con);