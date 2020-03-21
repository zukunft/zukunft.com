<?php 

/*

  link_edit.php - adjust a word link
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
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$link = zu_start("link_edit", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_LINK_EDIT);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $back = $_GET['back']; // the original calling page that should be shown after the change if finished

    // create the link object to have an place to update the parameters
    $lnk = New word_link;
    $lnk->id  = $_GET['id'];
    $lnk->usr = $usr;
    $lnk->load($debug-1);
    
    // edit the link or ask for confirmation
    if ($lnk->id <= 0) {
      $result .= zu_err("No triple found to change because the id is missing.", "link_edit.php", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
    
      if ($_GET['confirm'] == 1) {
      
        // get the parameters
        $lnk->from_id = $_GET['phrase1']; // the word or triple linked from
        $lnk->verb_id = $_GET['verb'];    // the link type (verb)
        $lnk->to_id   = $_GET['phrase2']; // the word or triple linked to

        // save the changes
        $upd_result = $lnk->save($debug-1);
      
        // if update was successful ...
        if (str_replace ('1','',$upd_result) == '') {
          // ... display the calling view
          $result .= dsp_go_back($back, $usr, $debug-1);
        } else {
          // ... or in case of a problem prepare to show the message
          $msg .= $upd_result;
        }
      }  
        
      // if nothing yet done display the add view (and any message on the top)
      if ($result == '')  {
        // display the view header
        $result .= $dsp->dsp_navbar($back, $debug-1);
        $result .= dsp_err($msg);

        // display the word link to allow the user to change it
        $result .= $lnk->dsp_edit($back, $debug-1); 
      } 
    }
  }

  echo $result;

zu_end($link, $debug);
?>
