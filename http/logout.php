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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

echo 'logging off ...'; // reset the html code var

// open database 
$link = zu_start("logoff", "center_form", $debug);

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1); // to check from which ip the user has logged in

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {
    $db_con = new mysql;         
    $db_con->type = "user";         
    $db_con->usr_id = $usr->id;         
    $sql_result = $db_con->update($usr->id, "last_logoff", "Now()", $debug-1);
  }
  
  // end the session
  session_unset(); 

// close the database  
zu_end($link, $debug);

echo 'logoff done.'; // reset the html code var

// show the main page without user being logged in
header("Location: view.php");
exit; 

?>
