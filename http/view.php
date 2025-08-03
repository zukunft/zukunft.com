<?php

/*

    view.php - create the HTML code to display a zukunft.com view
    --------

    - the view contains the overall formatting like page size
    - the view component links to words, values or formulas
    - a view component can be linked to a view or a view component define by the view_link_type

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

// for callable php files the standard zukunft.com header to load all classes and allow debugging
// to allow debugging of errors in the library that only appear on the server
$debug = $_GET['debug'] ?? 0;
// get the root path from the path of this file (relative path)
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

// set the other path once for all scripts
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
// load once the common const and vars used almost every time
include_once PHP_PATH . 'init.php';

use cfg\const\paths;

// load the mian frontend class
include_once paths::WEB . 'frontend.php';

use html\frontend;
use cfg\user\user;
use html\helper\config;
use html\user\user as user_dsp;

// reset the html code var
$html_str = '';

// open database
$db_con = prg_start("view", '', false);

if ($db_con->is_open()) {

    // load the session user parameters
    $usr = new user;
    $html_str .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {
        $usr->load_usr_data();

        $usr_dsp = new user_dsp();
        $usr_dsp->set_from_json($usr->api_json());

        // load the user changeable configuration once via api
        // TODO Prio 1 load the config from cache if nothing has been changed
        global $cfg;
        $cfg = new config();
        $cfg->load();

        $main = new frontend('view');
        $html_str .= $main->url_to_html($_GET, $usr_dsp);
    }

    // close the database
    prg_end($db_con);
} else {
    $html_str .= 'database connection lost';
}

// show the page
echo $html_str;

