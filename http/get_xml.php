<?php

/*

  get_xml.php - get data from zukunft.com in the xml format
  -----------


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

use cfg\phrase_list;
use cfg\user;
use controller\controller;
use shared\api;
use shared\library;

Header('Content-type: text/xml');

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start_api("get_xml");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    $xml = '';

    $usr->load_usr_data();
    $lib = new library();

    // get the words that are supposed to be exported, sample "Nestlé 2 country weight"
    $phrases = $_GET[api::URL_VAR_WORDS];
    log_debug("get_xml(" . $phrases . ")");
    $phr_names = $lib->array_trim(explode(",", $phrases));

    if (count($phr_names) > 0) {
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names($phr_names);
        // get all related Phrases
        $phr_lst = $phr_lst->are();

        log_debug("get_xml.php ... phrase loaded.");
        $xml_export = new xml_io;
        $xml_export->usr = $usr;
        $xml_export->phr_lst = $phr_lst;
        $xml = $xml_export->export();
    } else {
        $result .= log_info('No XML can be created, because no word or triple is given.', '', (new Exception)->getTraceAsString(), $this->usr);
    }

    if ($result <> '') {
        echo $result;
    } else {
        print($xml);
    }

}

// Closing connection
prg_end_api($db_con);
