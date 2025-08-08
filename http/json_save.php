<?php

/*

  json_save.php - download a data file from zukunft.com in the json format
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

use cfg\phrase\phrase_list;
use cfg\user\user;
use cfg\export\json_io;
use shared\url_var;
use shared\library;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

// open database
$db_con = prg_start_api("json_save");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();
    $lib = new library();

    // get the words that are supposed to be exported, sample "Nestlé 2 country weight"
    $phrases = $_GET[url_var::WORDS];
    log_debug("json_save(" . $phrases . ")");
    $phr_names = $lib->array_trim(explode(",", $phrases));

    if (count($phr_names) > 0) {
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names($phr_names);
        $phr_lst = $phr_lst->are();

        log_debug("json_save.php ... phrase loaded.");
        $json_export = new json_io($usr, $phr_lst);
        $result = $json_export->export();
    } else {
        $result .= log_info('No JSON can be created, because no word or triple is given.', '', (new Exception)->getTraceAsString(), $this->usr);
    }

    if ($result <> '') {
        echo $result;
    } else {
        // TODO replace with proper error message
        print(json_encode($phrases));
    }

}

// Closing connection
prg_end_api($db_con);
