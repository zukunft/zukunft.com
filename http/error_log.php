<?php

/*

    error_log.php - for automatic tracking of internal errors
    -------------

    err_dsp - simply to display the status of one error


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

// for callable php files the standard zukunft.com header to load all classes and allow debugging
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use cfg\const\paths;
use html\const\paths as html_paths;

// load what is used here
include_once paths::API_OBJECT . 'controller.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::VIEW . 'view.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'views.php';

use cfg\system\sys_log;
use cfg\user\user;
use cfg\view\view;
use html\system\sys_log as sys_log_dsp;
use html\view\view as view_dsp;
use shared\api;
use shared\const\views;

$db_con = prg_start("error_log");

global $sys_msk_cac;

$result = ''; // reset the html code var

$err_id = $_GET[api::URL_VAR_ID] ?? 0;
$back = $_GET[api::URL_VAR_BACK] ?? '';

// load the session user parameters
$usr = new user;
$result .= $usr->get();

if ($back <= 0) {
    $back = 1; // replace with the fallback word id
}

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    if ($err_id > 0) {
        log_debug("error_log (" . $err_id . ")");

        $usr->load_usr_data();

        // prepare the display to edit the view
        $view_id = $sys_msk_cac->id(views::ERR_LOG);
        $msk = new view($usr);
        $msk->load_by_id($view_id);
        $msk->load_components();
        $msk_dsp = new view_dsp($msk->api_json());
        $result .= $msk_dsp->dsp_navbar($back);
        //$result .= " in \"zukunft.com\" that has been logged in the system automatically by you.";
        $result .= err_dsp($err_id, $usr->id());
    }
}

echo $result;

// Closing connection
prg_end($db_con);

function err_dsp($err_id, $user_id): string
{
    $log = new sys_log();
    $log->load_by_id($err_id);
    $dsp = new sys_log_dsp($log->api_json());
    return $dsp->page_view();
}

