<?php

/*

    view_pur.php - create the HTML code to display a zukunft.com view
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

include_once PHP_PATH . 'frontend.php';

// reset the html code var
$result = '';

// start the user session
$session = new frontend('view');
$result .= $session->start(); // e.g. if requested write to the system log server that a user has sent a new request

// check e.g. if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($result != '') {
    echo $result;
} else {

    // get the requested objects
    // TODO replace it with phrase and add other objects
    //$view_words = $_GET[api::PAR_VIEW_WORDS] ?? '';

    // select the view
    // TODO improve it based on view.php
    $view_id = $_GET[frontend::PAR_VIEW_ID] ?? '';

    // get the view from the backend if not a cache
    $result .= $session->show_view($view_id);

    // get the data to show if not in cache

    // TODO get the rest from view.php

}

echo $result;

// report the result to be displayed
$session->end();