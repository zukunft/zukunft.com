<?php

/*

    triple.php - display a RDF triple
    ----------


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

use cfg\user\user;
use cfg\word\triple;
use shared\url_var;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

$result = ''; // reset the html code var

// open database
$db_con = prg_start("triple");

if (!$db_con->connected()) {
    $result = log_fatal("Cannot connect to " . SQL_DB_TYPE . " database with user " . SQL_DB_USER_MYSQL, "find.php");
} else {
    $back = $_GET[url_var::BACK] = '';
    $id = $_GET['triples'];

    // load the session user parameters
    $usr = new user;
    $result .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {

        $usr->load_usr_data();

        // show view header
        $trp = new triple($usr);
        $trp->load_by_id($id);

        $result .= $trp->dsp_id();

    }
}

echo $result;

prg_end($db_con);
