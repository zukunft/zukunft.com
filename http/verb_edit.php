<?php 

/*

  verb_edit.php - rename and adjust a verb
  ---------------
  
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
$link = zu_start("verb_edit", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately
  
  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_VEBR_EDIT);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $back = $_GET['back']; // the original calling page that should be shown after the change is finished
        
    // create the verb object to have an place to update the parameters
    $vrb = New verb;
    $vrb->id  = $_GET['id'];
    $vrb->usr = $usr;
    $vrb->load($debug-1);

    if ($vrb->id <= 0) {
      $result .= zu_err("No verb found to change because the id is missing.", "verb_edit.php", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // if the save botton has been pressed at least the name is filled (an empty name should never be saved; instead the word should be deleted)
      if ($_GET['name'] <> '') {

        // get the parameters (but if not set, use the database value)
        if (isset($_GET['name']))           { $vrb->name       = $_GET['name']; }
        if (isset($_GET['plural']))         { $vrb->plural     = $_GET['plural']; }
        if (isset($_GET['reverse']))        { $vrb->reverse    = $_GET['reverse']; }
        if (isset($_GET['plural_reverse'])) { $vrb->rev_plural = $_GET['plural_reverse']; }

        // save the changes
        $upd_result = $vrb->save($debug-1);
      
        // if update was successful ...
        if (str_replace ('1','',$upd_result) == '') {
          // remember the verb for the next values to add
          $usr->set_verb ($vrb_id, $debug-1);

          // ... and display the calling view
          $result .= dsp_go_back($back, $usr, $debug-1);
        } else {
          // ... or in case of a problem prepare to show the message
          $msg .= $upd_result;
        }
          
      } 

      // if nothing yet done display the add view (and any message on the top)
      if ($result == '')  {
        // show the header
        $result .= $dsp->dsp_navbar($back, $debug-1);
        $result .= dsp_err($msg);

        // show the verb and its relations, so that the user can change it
        $result .= $vrb->dsp_edit ($back, $debug-1);
      }  
    }
  }

  echo $result;

zu_end($link, $debug);
?>
