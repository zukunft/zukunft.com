<?php 

/*

  error_update.php - to maintain the error list
  ----------------


zukunft.com - calc with words

copyright 1995-2020 by zukunft.com AG, Zurich

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
include_once '../lib/zu_lib.php';  if ($debug > 1) { echo 'lib loaded<br>'; }

$link = zu_start("error_update", "", $debug);

  $result = ''; // reset the html code var

  // get the parameters
  $log_id    = $_GET['id'];
  $status_id = $_GET['status'];
  $back      = $_GET['back'];

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($usr->id > 0) {
    
    $dsp = new view_dsp;
    $dsp->usr = $usr;
    $dsp->id = cl(SQL_VIEW_ERR_UPD);
    $result .= $dsp->dsp_navbar($back, $debug-1);
    
    if ($usr->id > 0 AND $usr->profile_id == cl(SQL_USER_ADMIN)) {
      // update the error if requested
      if ($log_id > 0 AND $status_id > 0) {
        $err_entry = New system_error_log;
        $err_entry->usr       = $usr;
        $err_entry->id        = $log_id;
        $err_entry->status_id = $status_id;
        $err_entry->save($debug-1);
      }
    
      // display all program issues if the user is an admin
      $err_lst = New system_error_log_list;
      $err_lst->usr      = $usr;
      $err_lst->dsp_type = "all";
      $err_lst->page     = 1;
      $err_lst->size     = 20;
      $err_lst->back     = $back;
      $errors_all .= $err_lst->display($debug-1);
      //$errors_all .= zuu_dsp_errors  ($usr->id, $usr->profile_id, "all", $back, $debug-1);
      if ($errors_all <> "") {
        $result .= dsp_text_h3("Program issues that other user have found, that have not yet been solved.");
        $result .= $errors_all;
      } else {
        $result .= dsp_text_h3("There are no open errors left.");
      }

      if ($_SESSION['logged']) {
        $result .= '<br><br><a href="/http/logout.php">logout</a>';
      }  
    } else {
      $result .= dsp_text_h3("You are not permitted to update the error status. If you want to get the permission, please request it at admin@zukunft.com..");
    }
  }

  $result .= '<br><br>';
  $result .= btn_back($back);

  echo $result;

// Closing connection
zu_end($link, $debug);
?>
