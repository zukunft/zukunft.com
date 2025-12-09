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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'views.php';

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\const\views as views;

$app = new frontend();
$db_con = $app->start("formula_edit");
$html = new html_base();

// get the parameters
$frm_id = $_GET[url_var::ID] ?? 0;

$result = ''; // reset the html code var
$usr_msg = new user_message(); // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(views::FORMULA_EDIT);
    $back = $_GET[url_var::BACK] = '';

    // create the formula object to have a place to update the parameters
    $frm = new formula($usr);
    $frm->load_by_id($frm_id);

    // load the parameters to the formula object to display the user input again in case of an error
    if (isset($_GET['formula_name'])) {
        $frm->set_name($_GET['formula_name']);
    } // the new formula name
    if (isset($_GET[url_var::USER_EXPRESSION])) {
        $frm->usr_text = $_GET[url_var::USER_EXPRESSION];
    } // the new formula text in the user format
    if (isset($_GET[url_var::DESCRIPTION])) {
        $frm->description = $_GET[url_var::DESCRIPTION];
    }
    if (isset($_GET['type'])) {
        $frm->type_id = $_GET['type'];
    }
    if ($_GET[url_var::NEED_ALL] == 'on') {
        $frm->need_all_val = true;
    } else {
        if ($_GET['confirm'] == 1) {
            $frm->need_all_val = false;
        }
    }
    //if (isset($_GET[url_var::NEED_ALL]))  { if ($_GET[url_var::NEED_ALL] == 'on') { $frm->need_all_val = true; } else { $frm->need_all_val = false; } }

    if ($frm->id() <= 0) {
        $result .= log_err("No formula found to change because the id is missing.", "/http/formula_edit.php");
    } else {

        // do the direct changes initiated by other buttons than the save button
        // to link the formula to another word
        $link_phr_id = $_GET[url_var::LINK_PHRASE] ?? 0;
        if ($link_phr_id != 0) {
            $phr = new phrase($usr);
            $phr->load_by_id($link_phr_id);
            $upd_result = $frm->link_phr($phr);
        }

        // to unlink a word from the formula
        $unlink_phr_id = $_GET[url_var::UNLINK_PHRASE] ?? 0;
        if ($unlink_phr_id > 0) {
            $phr = new phrase($usr);
            $phr->load_by_id($unlink_phr_id);
            $upd_result = $frm->unlink_phr($phr);
        }

        // if the save button has been pressed at least the name is filled (an empty name should never be saved; instead the word should be deleted)
        if ($frm->usr_text <> '') {

            // update the formula if it has been changed
            $frm->save($usr_msg);

            // if update was successful ...
            if ($usr_msg->is_ok()) {
                // ... display the calling view
                // because formula changing may need several updates the edit view is shown again
                //$result .= dsp_go_back($back, $usr);

                // trigger to update the related results / results
                if ($frm->needs_res_upd) {
                    // update the formula results
                    $phr_lst = $frm->assign_phr_lst();
                    //$res_list = $frm->calc($phr_lst);
                }
            }
        }

        // if nothing yet done display the edit view (and any message on the top)
        if ($result == '') {
            // display the view header
            $msk_dsp = new view_ui($msk->api_json());
            $dto = new data_object();
            $result .= $msk_dsp->dsp_navbar($dto, $back);
            $result .= $html->dsp_err($usr_msg->all_message_text());

            // display the view to change the formula
            $frm->load_by_id($frm_id); // reload to formula object to display the real database values
            $add_link = $_GET['add_link'] ?? 0;
            $frm_dsp = new formula_ui($frm->api_json());
            $result .= $frm_dsp->dsp_edit($add_link, 0, $back); // with add_link to add a link and display a word selector
        }
    }
}

echo $result;

$app->end($db_con);
