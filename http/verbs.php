<?php 

/*

  verbs.php - display a list of all verbs to allow an admin user to modify it
  ---------
  
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
// Zukunft.com verb list

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$db_con = zu_start("verbs", '', $debug);

  $result = ''; // reset the html code var
  $back = $_GET['back']; // the word id from which this value change has been called (maybe later any page)

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_VERBS);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
        
    // show the header
    $result .= $dsp->dsp_navbar($back, $debug-1);

    // display the verb list
    $result .= dsp_text_h2("Word link types");
    $dsp = New verb_list;
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $result .= $dsp->dsp_list($debug-1);
    //$result .= zul_dsp_list ($usr->id, $debug);
  }

  echo $result;

// Closing connection
zu_end($db_con, $debug);
