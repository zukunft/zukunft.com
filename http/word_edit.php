<?php 

/*

  word_edit.php - adjust a word
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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$db_con = prg_start("word_edit", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_WORD_EDIT);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $back = $_GET['back']; // the word id from which this value change has been called (maybe later any page)
        
    // create the word object to have an place to update the parameters
    $wrd = New word_dsp;
    $wrd->id  = $_GET['id'];
    $wrd->usr = $usr;
    $wrd->load($debug-1);
      
    if ($wrd->id <= 0) {
      $result .= log_info("The word id must be set to display a word.", "word_edit.php", '', (new Exception)->getTraceAsString(), $usr);
    } else {  

      // get all parameters (but if not set, use the database value)
      if (isset($_GET['name']))        { $wrd->name        = $_GET['name']; } // 
      if (isset($_GET['plural']))      { $wrd->plural      = $_GET['plural']; } // 
      if (isset($_GET['description'])) { $wrd->description = $_GET['description']; } // 
      if (isset($_GET['type']))        { $wrd->type_id     = $_GET['type']; }        // any functional code for special word is defined with the code_id of the word type

      // if the save bottom has been pressed
      if ($_GET['confirm'] > 0) {

        // an empty word name should never be saved; instead the word should be deleted)  
        if ($wrd->name == '') {
          $msg .= 'An empty name should never be saved. Please delete the word instead.';
        } else {  
          // save the changes
          $upd_result = $wrd->save($debug-1);
        
          // if update was fine ...
          if (str_replace ('1','',$upd_result) == '') {
            // ... display the calling page is switched of to keep the user on the edit view and see the implications of the change
            // switched off because maybe staying on the edit page is the expected behaviour
            //$result .= dsp_go_back($back, $usr, $debug-1);
          } else { 
            // ... or in case of a problem prepare to show the message
            $msg .= $upd_result;
          }
        }
      }      
    
      // if nothing yet done display the edit view (and any message on the top)
      if ($result == '')  {
        // show the header
        $result .= $dsp->dsp_navbar($back, $debug-1);
        $result .= dsp_err($msg);

        // show the word and its relations, so that the user can change it
        $result .= $wrd->dsp_edit ($back, $debug-1);
      }  
    }  
  }

  echo $result;

prg_end($db_con, $debug);
