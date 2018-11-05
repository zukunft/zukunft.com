<?php 

/*

  formula_result.php - explains one formula result
  ------------------
  
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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// for callable php files the standard zukunft.com header to load all classes and allow debugging
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

$link = zu_start("formula_result", "", $debug);

  $result = ''; // reset the html code var

  // load the session user parameters
  $session_usr = New user;
  $result .= $session_usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($session_usr->id > 0) {

    // show the header
    $dsp = new view_dsp;
    $dsp->usr = $session_usr;
    $dsp->id = cl(SQL_VIEW_FORMULA_EXPLAIN);
    $back = $_GET['back']; // the page (or phrase id) from which formula testing has been called
    $result .= $dsp->top_right($debug-1);
    
    // get the parameters
    $frm_val_id   = $_GET['id'];      // id of the formula result if known already
    $frm_id       = $_GET['formula']; // id of the formula which values should be explained
    $phr_id       = $_GET['word'];    // id of the leading word used to order the result explaining
    //$wrd_group_id = $_GET['group'];   // id of the word group (excluding and time word)
    $time_id      = $_GET['time'];    // id of the time word for which the value is valid (always the end of the period e.g. a vlaue for 2016 is valid at the end of the year)

    // explain the result
    if ($frm_val_id > 0 OR $frm_id > 0) {
      $fv = New formula_value;
      $fv->id = $frm_val_id;
      $fv->usr = $session_usr;
      $fv->load($debug-1);
      if ($fv->id > 0) {
        $result .= $fv->explain($phr_id, $back, $debug-1);
      } else {
        $result .= zu_err("Formula result with id ".$frm_val_id.' not found.', "formula_result.php");
      }
      zu_debug('formula_result.php explained (id'.$fv->id.' for user '.$session_usr->name.')', $debug-10);
    } else {
      // ... or complain about a wrong call
      $url_txt = "";
      foreach($_GET as $key => $value) {
        $url_txt .= $key.'='.$value.',';
      }
      $result .= zu_err("Wrong parameters: ".$url_txt, "formula_result.php");
    }
  }

  echo $result;

zu_end($link, $debug);
?>
