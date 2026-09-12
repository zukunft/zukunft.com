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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'const.php';
include_once WEB . 'frontend.php';

use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;

include_once paths::SHARED_CONST . 'views.php';

// open database
$app = new frontend();
$msg = new user_message();
global $sys;
$db_con = $app->start("view_edit");
$html = new html_base();

$result = ''; // reset the html code var
$usr_msg = new user_message(); // to collect all messages that should be shown to the user immediately
$msg_txt = '';

// load the session user parameters
$usr = new user;
$result .= $usr->get($msg);

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    $upd_result = '';

    $usr->load_usr_data($usr_msg);

    // prepare the display to edit the view
    $msk = new view($usr);
    $msk->load_by_code_id(views::VIEW_ADD, $usr_msg);
    $lib = new library();
    $back = $lib->filter_var($_GET[url_var::BACK]);

    // create the view object that the user can change
    $msk_edit = new view($usr);
    $result .= $msk_edit->load_by_id($_GET[url_var::ID], $usr_msg);

    // get the view id to adjust
    if ($msk_edit->id() <= 0) {
        log_info("The view id must be set to display a view.", "view_edit.php", '', (new Exception)->getTraceAsString(), $usr);
    } else {

        // get the word used as a sample the show the changes
        $wrd = new word($usr);
        $result .= $wrd->load_by_id($_GET['word'], $usr_msg);

        // save the direct changes
        // ... of the element list
        if (isset($_GET['move_up'])) {
            $upd_result = $msk_edit->entry_up($_GET['move_up'], $usr_msg);
            if (str_replace('1', '', $upd_result) <> '') {
                // ... or in case of a problem prepare to show the message
                $msg_txt .= $upd_result;
            }
        }

        if (isset($_GET['move_down'])) {
            $upd_result .= $msk_edit->entry_down($_GET['move_down'], $usr_msg);
            if (str_replace('1', '', $upd_result) <> '') {
                // ... or in case of a problem prepare to show the message
                $msg_txt .= $upd_result;
            }
        }

        // unlink an entry
        if (isset($_GET['del'])) {
            $cmp = new component($usr);
            $cmp->load_by_id($_GET['del'], $usr_msg);
            $cmp->unlink($msk_edit, $usr_msg);
        }

        // check if a existing view element should be added
        if (isset($_GET['add_component'])) {
            if ($_GET['add_component'] > 0) {
                $cmp = new component($usr);
                $cmp->load_by_id($_GET['add_component'], $usr_msg);
                $order_nbr = $cmp->next_nbr($msk_edit->id(), $msg);
                $cmp->link($msk_edit, $order_nbr, $usr_msg);
            }
        }

        // check if a new view element should be added
        if (isset($_GET['entry_name']) and isset($_GET['new_entry_type'])) {
            if ($_GET['entry_name'] <> '' and $_GET['new_entry_type'] > 0) {
                $cmp = new component($usr);
                $cmp_name = $_GET['entry_name'];
                $cmp->set_name($cmp_name);
                $add_result = $cmp->save($usr_msg);
                if ($add_result == '') {
                    $cmp->load_by_name($cmp_name, $usr_msg);
                    if ($cmp->id() > 0) {
                        $cmp->type_id = $_GET['new_entry_type'];
                        $cmp->save($usr_msg);
                        $order_nbr = $cmp->next_nbr($msk_edit->id(), $msg);
                        $cmp->link($msk_edit, $order_nbr, $usr_msg);
                    }
                }
            }
        }

        // if the save button has been pressed (an empty view name should never be saved; instead the view should be deleted)
        $dsp_name = $_GET[url_var::NAME];
        if ($dsp_name <> '') {


            // get other field parameters that should be saved
            if (isset($_GET[url_var::NAME])) {
                $msk_edit->set_name($_GET[url_var::NAME]);
            }
            if (isset($_GET[url_var::DESCRIPTION])) {
                $msk_edit->description = $_GET[url_var::DESCRIPTION];
            }
            if (isset($_GET['type'])) {
                $msk_edit->type_id = $_GET['type'];
            } //

            // save the changes
            $upd_result = $msk_edit->save($usr_msg);

            // if update was fine ...
            if (str_replace('1', '', $upd_result) == '') {
                // ... display the calling page (switched off because it seems more useful it the user goes back by selecting the related word)
                // $result .= dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg_txt .= $upd_result;
            }
        }

        // if nothing yet done display the add view (and any message on the top)
        if ($result == '') {
            // in view edit views the view cannot be changed
            $msk_ui = new view_ui($msk->api_json());
            $result .= $msk_ui->dsp_navbar_no_view($back);
            $result .= $html->dsp_err($usr_msg->all_message_text());

            // get parameters that change only dsp_edit
            // if the user has requested to add another display component to this view, $add_cmp is greater than 0
            $add_cmp = 0;
            if (isset($_GET['add_entry'])) {
                $add_cmp = $_GET['add_entry'];
            }

            // show the word and its relations, so that the user can change it
            $msk_edit_dsp = new view_ui($msk_edit->api_json());
            // the frontend display object needs a frontend message object
            // (frontend and backend are separate apps with separate user_message classes)
            $result .= $msk_edit_dsp->dsp_edit($add_cmp, $wrd, $back, new user_message_ui());
        }
    }
}

echo $result;

$app->end($db_con);