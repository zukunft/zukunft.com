<?php

/*

  api/phrase/read.php - send a word to the frontend
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../../src/main/php/zu_lib.php';

// open database
$db_con = prg_start("api/word");

// get the parameters
$wrd_id = $_GET['id'] ?? 0;

$msg = '';
$result = new \api\word_api(); // reset the html code var

// load the session user parameters
$usr = new user;
$msg .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    if ($wrd_id > 0) {
        $wrd = new word($usr);
        $wrd->id = $wrd_id;
        $wrd->load();
        $result = $wrd->api_obj();
    } else {
        $msg = 'word id is missing';
    }
}

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// return the word json or the error message
if ($msg == '') {

    // set response code - 200 OK
    http_response_code(200);

    // return the word object
    echo json_encode($result);

} else {

    // set response code - 400 Bad Request
    http_response_code(400);

    // tell the user no products found
    echo json_encode(
        array("message" => $msg)
    );

}

prg_end($db_con);
