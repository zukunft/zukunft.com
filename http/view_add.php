<?php 

/*

  view_add.php - create a new view
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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$db_con = prg_start("view_add", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately
  
  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(DBL_VIEW_ADD);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $back = $_GET['back']; // 

    // create the object to store the parameters so that if the add form is shown again it is already filled
    $dsp_add = new view_dsp;
    $dsp_add->usr     = $usr;
    
    // load the parameters to the view object to display the user input again in case of an error
    if (isset($_GET['name']))    { $dsp_add->name    = $_GET['name']; }    // name of the new view to add
    if (isset($_GET['comment'])) { $dsp_add->comment = $_GET['comment']; }
    if (isset($_GET['type']))    { $dsp_add->type_id = $_GET['type']; }     

    if ($_GET['confirm'] > 0) {

      // check essential parameters
      if ($_GET['name'] == "") {
        $msg .= 'Name missing; Please press back and enter a name for the new view.';
      } else {

        $add_result = $dsp_add->save($debug-1);
        
        // if adding was successful ...
        if (str_replace ('1','',$add_result) == '') {
          // to do: call the dsp_edit view and set the new view as the default view for the sample term
          // display the calling view (or call the view component edit
          $result .= dsp_go_back($back, $usr, $debug-1);
        } else {
          // ... or in case of a problem prepare to show the message
          $msg .= $add_result;
        }
      }
    }

    // if nothing yet done display the add view (and any message on the top)
    if ($result == '')  {
      // sample word that is used to simulate the view changes
      $wrd = New word;
      $wrd->id      = $_GET['word'];
      $wrd->usr     = $usr;
      //$wrd->type_id = $view_type;
      if ($wrd->id > 0) { $wrd->load($debug-1); }

      // show the header (in view edit views the view cannot be changed)
      $result .= $dsp->dsp_navbar_no_view($wrd->id, $debug-1);
      $result .= dsp_err($msg);

      // show the form to create a new view
      $result .= $dsp_add->dsp_edit (0, $wrd, $back, $debug-1);
    }  
  }

  echo $result;

prg_end($db_con, $debug);
