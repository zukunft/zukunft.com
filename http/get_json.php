<?php

/*

  get_json.php - get data from zukunft.com in the json format many for internal use
  ------------


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

// open database
$db_con = prg_start_api("get_json");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    load_usr_data();

    // get the words that are supposed to be exported, sample "Nestlé 2 country weight"
    $phrases = $_GET['words'];
    log_debug("get_json(" . $phrases . ")");
    $word_names = explode(",", $phrases);

    // get all related Phrases
    $phr_lst = new phrase_list($usr);
    foreach ($word_names as $wrd_name) {
        if ($wrd_name <> '') {
            $phr_lst->add_name($wrd_name);
        }
    }

    if (count($phr_lst->lst) > 0) {
        $phr_lst->load();
        $phr_lst = $phr_lst->are();

        log_debug("get_json.php ... phrase loaded.");
        $json_export = new json_io;
        $json_export->usr = $usr;
        $json_export->phr_lst = $phr_lst;
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
