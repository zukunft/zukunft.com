<?php 

/*

  verb_add.php - add a new link type / verb
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

/* standard zukunft header for callable php files to allow debugging and lib loading */
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

/* open database */
$db_con = prg_start("link_type_add");

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  echo $usr->get(); // if the usr identification fails, show any message immediately because this should never happen

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(DBL_VIEW_VERB_ADD);
    $dsp->usr = $usr;
    $dsp->load();
    $back = $_GET['back']; // the calling word which should be displayed after saving

    if (!$usr->is_admin()) {
      $result .= log_err("Only user with the administrator profile can add verbs (word link types).","verb_add.php");
    } else {

      // create the object to store the parameters so that if the add form is shown again it is already filled
      $vrb = New verb;
      $vrb->usr = $usr;
    
      // load the parameters to the verb object to display it again in case of an error
      if (isset($_GET['name']))           { $vrb->name       = $_GET['name']; }
      if (isset($_GET['plural']))         { $vrb->plural     = $_GET['plural']; }
      if (isset($_GET['reverse']))        { $vrb->reverse    = $_GET['reverse']; }
      if (isset($_GET['plural_reverse'])) { $vrb->rev_plural = $_GET['plural_reverse']; }
            
      if ($_GET['confirm'] > 0) {

        // check essential parameters
        if ($vrb->name == "") {
          $msg .= 'Name missing; Please press back and enter a verb name.';
        } else {

          // check if a verb, formula or word with the same name is already in the database
          $trm = New term;
          $trm->name = $vrb->name;
          $trm->usr  = $usr;
          $trm->load();
          if ($trm->id > 0) {
            $msg .= $trm->id_used_msg();
          }  

          // if the parameters are fine
          if ($msg == '') {
            // add the new verb
            $add_result = $vrb->save();

            // if adding was successful ...
            if (str_replace ('1','',$add_result) == '') {
              // ... and display the calling view
              $result .= dsp_go_back($back, $usr);
            } else {
              // ... or in case of a problem prepare to show the message
              $msg .= $add_result;
            }
          }  
        }
      }

      // if nothing yet done display the add view (and any message on the top)
      if ($result == '')  {
        // show the header
        $result .= $dsp->dsp_navbar($back);
        $result .= dsp_err($msg);

        // get the form to add a new verb
        $result .= $vrb->dsp_edit ($back);
      }  
    }
  }

  echo $result;

prg_end($db_con);