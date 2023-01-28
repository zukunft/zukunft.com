<?php

/*

  logoff.php - just close the current user session and go back to the main page 
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

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

echo 'logging off ...'; // reset the html code var

// open database 
$db_con = prg_start("logoff", "center_form");

// load the session user parameters
$usr = new user;
$result = $usr->get(); // to check from which ip the user has logged in

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    $db_con->set_type(sql_db::TBL_USER);
    $db_con->set_usr($usr->id());
    if (!$db_con->update($usr->id(), "last_logoff", "Now()")) {
        log_err('Logout time update failed for ' . $usr->id());
    }
}

// end the session
session_unset();

// close the database  
prg_end($db_con);

echo 'logoff done.'; // reset the html code var

// show the main page without user being logged in
header("Location: view.php");
exit;