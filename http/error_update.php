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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

use controller\controller;
use html\html_base;
use html\view\view_dsp_old;
use model\system_log;
use model\system_log_list;
use model\user;
use model\user_profile;
use model\view;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

$db_con = prg_start("error_update");
$html = new html_base();

global $system_views;
global $user_profiles;

$result = ''; // reset the html code var

// get the parameters
$log_id = $_GET['id'];
$status_id = $_GET['status'];
$back = $_GET['back'];

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    $dsp = new view_dsp_old($usr);
    $dsp->set_id($system_views->id(controller::DSP_ERR_UPD));
    $result .= $dsp->dsp_navbar($back);

    if ($usr->id() > 0 and $usr->profile_id == $user_profiles->id(user_profile::ADMIN)) {
        // update the error if requested
        if ($log_id > 0 and $status_id > 0) {
            $err_entry = new system_log;
            $err_entry->set_user($usr);
            $err_entry->set_id($log_id);
            $err_entry->status_id = $status_id;
            $err_entry->save();
        }

        // display all program issues if the user is an admin
        $errors_all = '';
        $err_lst = new system_log_list;
        $err_lst->set_user($usr);
        $err_lst->dsp_type = system_log_list::DSP_ALL;
        $err_lst->page = 1;
        $err_lst->size = 20;
        $err_lst->back = $back;
        if ($err_lst->load()) {
            $errors_all = $err_lst->dsp_obj()->get_html();
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
