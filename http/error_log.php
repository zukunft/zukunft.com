<?php 

/*

  error_log.php - for automatic tracking of internal errors
  -------------

  function prefix: zu_err_* 

  
  display functions
  -------
  
  err_dsp - simply to display the status of one error


zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

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

  function err_dsp($err_id, $user_id) {

    global $db_con;
    $result = "";

    $sql = "SELECT l.sys_log_text, l.sys_log_description, s.sys_log_status_name, l.sys_log_trace
              FROM sys_log l 
         LEFT JOIN sys_log_status s ON l.sys_log_status_id = s.sys_log_status_id
             WHERE l.sys_log_id = ".$err_id.";";
    //$db_con = New mysql;
    $db_con->usr_id = $user_id;         
    $db_err = $db_con->get1($sql);  

    $result .= dsp_text_h2("Status of error #".$err_id.': '.$db_err['sys_log_status_name']);
    $result .= '"'.$db_err['sys_log_text'].'" <br>';
    if ($db_err['sys_log_description'] <> 'NULL') { $result .= '"'.$db_err['sys_log_description'].'" <br>'; }
    $result .= '<br>';
    $result .= 'Program trace:<br>';
    $result .= ''.$db_err['sys_log_trace'].' ';
    //echo "<font color=green>OK</font>" .$test_text;
    //echo "<font color=red>Error</font>".$test_text;
    
    return $result;
  }

  
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php';  if ($debug > 1) { echo 'lib loaded<br>'; }

$db_con = prg_start("error_log");

  $result = ''; // reset the html code var

  $err_id = $_GET['id'];           
  $back   = $_GET['back'];           
  
  // load the session user parameters
  $usr = New user;
  $result .= $usr->get();

  if ($back <= 0) {
    $back = 1; // replace with the fallback word id
  }
  $wrd = New word;
  $wrd->usr = $usr;  
  $wrd->id = $back;  
  $wrd->load();
  
  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {
    if ($err_id > 0) {
      log_debug("error_log -> (".$err_id.")");
      // prepare the display to edit the view
      $dsp = new view_dsp;
      $dsp->usr = $usr;
      $dsp->id = cl(DBL_VIEW_ERR_LOG);
      $result .= $dsp->dsp_navbar($back);
      //$result .= " in \"zukunft.com\" that has been logged in the system automatically by you.";
      $result .= err_dsp($err_id, $usr->id);
    }
  }

  echo $result;

// Closing connection
prg_end($db_con);