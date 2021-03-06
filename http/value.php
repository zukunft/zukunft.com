<?php 

/*

  value.php - display a value
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

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$db_con = prg_start("value");

  // get the parameters
  $wrd_names = $_GET['t']; 
  log_debug("value for ".$wrd_names);
  
  $result = ''; // reset the html code var

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get();

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(DBL_VIEW_VALUE_DISPLAY);
    $dsp->usr = $usr;
    $dsp->load();
    $back = $_GET['back']; // the page (or phrase id) from which formula testing has been called
        
    $result .= $dsp->dsp_navbar($back);

    if ($wrd_names <> '') {

      // load the words
      $wrd_lst = New word_list;
      $wrd_lst->name_lst = explode(",",$wrd_names);
      $wrd_lst->usr = $usr;
      $wrd_lst->load();
      
      $result .= $wrd_lst->name_linked();   
      $result .= ' = ';   
      $val = $wrd_lst->value();   
      $result .= $val->display_linked($back);   
    }  
  }

  echo $result;

prg_end($db_con);
