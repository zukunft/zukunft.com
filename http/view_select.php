<?php 

/*

  view_select.php - define how a word should be displayed
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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$db_con = zu_start("view_select", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately
  
  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // in view edit views the view cannot be changed
    $dsp = new view_dsp;
    $dsp->usr = $usr;
    //$dsp->id = cl(SQL_VIEW_FORMULA_EXPLAIN);
    $back = $_GET['back']; // the original calling page that should be shown after the change if finished
    $result .= $dsp->dsp_navbar_no_view($back, $debug-1);
    $view_id = 0;
    $word_id = $back;

    // get the view id used until now and the word id
    if (isset($_GET['id'])) {
      $view_id = $_GET['id'];
    }
    if (isset($_GET['word'])) {
      $word_id = $_GET['word'];
    }

    // show the word name
    $wrd = New word_dsp;
    if ($word_id > 0) {
      $wrd->usr = $usr;
      $wrd->id  = $word_id;   
      $wrd->load($debug-1);
      $result .= dsp_text_h2 ('Select the display format for "'.$wrd->name.'"');
    } else {  
      $result .= dsp_text_h2 ('The word is missing for which the display format should be changed. If you can explain how to reproduce this error message, please report the steps on https://github.com/zukunft/zukunft.com/issues.');
    }

    // allow to change to type
    $dsp = new view;
    $dsp->usr = $usr;
    $dsp->id  = $view_id;   
    $result .= $dsp->selector_page ($word_id, $back, $debug-1);

    // show the changes
    $result .= $wrd->dsp_log_view ($back, $debug-1);
  }

  echo $result;
  
zu_end($db_con, $debug);
