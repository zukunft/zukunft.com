<?php

/*

    error_update.php - to maintain the error list
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
include_once PHP_PATH . 'zu_lib.php';

include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_ENUM_PATH . 'user_profiles.php';

use cfg\system\sys_log;
use cfg\system\sys_log_list;
use cfg\user\user;
use cfg\view\view;
use html\html_base;
use html\view\view as view_dsp;
use shared\api;
use shared\const\views as view_shared;
use shared\enum\user_profiles;

$db_con = prg_start("error_update");
$html = new html_base();

global $sys_msk_cac;
global $usr_pro_cac;

$result = ''; // reset the html code var

// get the parameters
$log_id = $_GET[api::URL_VAR_ID];
$status_id = $_GET['status'];
$back = $_GET[api::URL_VAR_BACK] = '';

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    $msk = new view($usr);
    $msk->set_id($sys_msk_cac->id(view_shared::ERR_UPD));
    $msk_dsp = new view_dsp($msk->api_json());
    $result .= $msk_dsp->dsp_navbar($back);

    if ($usr->id() > 0 and $usr->is_admin()) {
        // update the error if requested
        if ($log_id > 0 and $status_id > 0) {
            $err_entry = new sys_log;
            $err_entry->set_user($usr);
            $err_entry->set_id($log_id);
            $err_entry->status_id = $status_id;
            $err_entry->save();
        }

        // display all program issues if the user is an admin
        $errors_all = '';
        $err_lst = new sys_log_list;
        $err_lst->set_user($usr);
        $err_lst->dsp_type = sys_log_list::DSP_ALL;
        $err_lst->page = 1;
        $err_lst->size = 20;
        $err_lst->back = $back;
        if ($err_lst->load()) {
            $err_lst_dsp = new sys_log_list($err_lst->api_json());
            $errors_all = $err_lst_dsp->get_html();
        }
        //$errors_all .= dsp_errors  ($usr->id(), $usr->profile_id, "all", $back);
        if ($errors_all <> "") {
            $result .= $html->dsp_text_h3("Program issues that other user have found, that have not yet been solved.");
            $result .= $errors_all;
        } else {
            $result .= $html->dsp_text_h3("There are no open errors left.");
        }

        if ($_SESSION['logged']) {
            $result .= '<br><br><a href="/http/logout.php">logout</a>';
        }
    } else {
        $result .= $html->dsp_text_h3("You are not permitted to update the error status. If you want to get the permission, please request it at admin@zukunft.com..");
    }
}

$result .= '<br><br>';
$result .= \html\btn_back($back);

echo $result;

// Closing connection
prg_end($db_con);
