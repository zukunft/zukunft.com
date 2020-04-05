<?php 

/*

  link_add.php - create a triple
  ------------
  
  LINK a new word with a link type that has not yet been used
  means ADD a new link type, not simply link an additional word to a value
  
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
$link = zu_start("link_add", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  echo $usr->get($debug-1); // if the usr identification fails, show any message immediately because this should never happen

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_LINK_ADD);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $back = $_GET['back'];      // the calling word which should be displayed after saving

    // create the object to store the parameters so that if the add form is shown again it is already filled
    $lnk = New word_link;
    $lnk->usr     = $usr;

    // load the parameters to the triple object to display it again in case of an error
    if (isset($_GET['from']))   { $lnk->from_id = $_GET['from']; }   // the word or triple to be linked
    if (isset($_GET['verb']))   { $lnk->verb_id = $_GET['verb']; }   // the link type (verb)
    if (isset($_GET['phrase'])) { $lnk->to_id   = $_GET['phrase']; }
          
    // if the user has pressed save at least once
    if ($_GET['confirm'] == 1) {

      // check essential parameters
      if ($lnk->from_id == 0 OR $lnk->verb_id == 0 OR $lnk->to_id == 0) {
        $msg .= 'Please select two words and a verb.';
      } else {
    
        $add_result .= $lnk->save($debug-1);

        // if adding was successful ...
        if (str_replace ('1','',$add_result) == '') {
          // ... and display the calling view
          $result .= dsp_go_back($back, $usr, $debug-1);
        } else {
          // ... or in case of a problem prepare to show the message
          $msg .= $add_result;
        }
      }
    }
    
    // if nothing yet done display the add view (and any message on the top)
    if ($result == '')  {
      // display the add view again
      $result .= $dsp->dsp_navbar($back, $debug-1);
      $result .= dsp_err($msg);

      // display the form to create a new triple
      $result .= $lnk->dsp_add ($back, $debug-1);
    }
  }

  echo $result;

zu_end($link, $debug);
?>
