<?php 

/*

  calculate.php - update all formula results
  -------------
  
  The batch version of formula_test.php


zukunft.com - calc with words

copyright 1995-2018 by zukunft.com AG, Zurich

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$link = zu_start("calculate", "", $debug);

  // load the requesting user
  $usr = New user;
  $usr_id    = $_GET['user']; // to force another user view for testing the formula calculation
  if ($usr_id <= 0) {
    echo $usr->get($debug-1);
  }
  if ($usr_id <= 0) {
    $usr->id = TEST_USER_ID; // fallback user
    echo $usr->get($debug-1);
  }

  // start displaying while calculating
  $calc_pos = 0;
  $last_msg_time = time();
  ob_implicit_flush(true);
  ob_end_flush();
  zu_debug("create the calculation queue ... ", $debug-1);
    
  // load the formulas to calculate
  $frm_lst = New formula_list;
  $frm_lst->usr = $usr;
  $frm_lst->load($debug-10);
  echo "Calculate ".count($frm_lst->lst)." formulas<br>";
  
  foreach ($frm_lst AS $frm_request) {

    // build the calculation queue
    $calc_fv_lst = New formula_value_list;
    $calc_fv_lst->usr = $usr;
    $calc_fv_lst->frm = $frm_request;
    $calc_lst = $calc_fv_lst->frm_upd_lst($frm_request, $usr, $back, $debug-2);
    zu_debug("calculate queue is build (number of values to check: ".count($calc_lst->lst).")", $debug-1);
      
    // execute the queue
    foreach ($calc_lst->lst AS $r) {

      // calculate one formula result
      $frm = clone $r->frm;
      $fv_lst = $frm->calc($r->wrd_lst, $debug-1);

      // show the user the progress every two seconds
      if ($last_msg_time + UI_MIN_RESPONSE_TIME < time()) {
        $calc_pct = ($calc_pos/sizeof($calc_lst->lst)) * 100;
        echo "".round($calc_pct,2)."% calculated (".$r->frm->name." for ".$r->wrd_lst->name_linked()." = ".$fv->display_linked($back, $debug-1).")<br>";
        ob_flush();
        flush();       
        $last_msg_time = time();
      }
            
      $calc_pos++;
    }
  }

  ob_end_flush();

  // display the finish message
  echo "<br>";
  echo "calculation finished.";

// Closing connection
zu_end($link, $debug);
?>
