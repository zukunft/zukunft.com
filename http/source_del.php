<?php 

/*

  source_del.php - delete a source
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$link = zu_start("source_del", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_SOURCE_DEL);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $back = $_GET['back']; // the original calling page that should be shown after the change if finished
        
    // get the parameters
    $src_id  = $_GET['id']; 
    $confirm = $_GET['confirm'];
  
    if ($src_id > 0) {

      // create the source object to have an object to update the parameters
      $src = New source;
      $src->id  = $src_id;
      $src->usr = $usr;
      $src->load($debug-1);
      
      if ($confirm == 1) {
        $src->del($debug-1);  
    
        $result .= dsp_go_back($back, $usr, $debug-1);
      } else {
        // display the view header
        $result .= $dsp->dsp_navbar($back, $debug-1);

        $result .= btn_yesno("Delete ".$src->name."? ", "/http/source_del.php?id=".$src_id."&back=".$back);
      }
    } else {
      $result .= dsp_go_back($back, $usr, $debug-1);
    }  
  }

  echo $result;

zu_end($link, $debug);
?>
