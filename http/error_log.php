<?php

/*

  error_log.php - for automatic tracking of internal errors
  -------------

  function prefix: zu_err_* 

  
  display functions
  -------
  
  err_dsp - simply to display the status of one error


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

use html\html_base;
use model\db_cl;
use model\user;
use model\view;
use model\word;

function err_dsp($err_id, $user_id)
{

    global $db_con;
    $result = "";
    $html = new html_base();

    $sql = "SELECT l.sys_log_text, l.sys_log_description, s.type_name AS sys_log_status_name, l.sys_log_trace
              FROM sys_log l 
         LEFT JOIN sys_log_status s ON l.sys_log_status_id = s.sys_log_status_id
             WHERE l.sys_log_id = " . $err_id . ";";
    //$db_con = New mysql;
    $db_con->usr_id = $user_id;
    $db_err = $db_con->get1_old($sql);

    $result .= $html->dsp_text_h2("Status of error #" . $err_id . ': ' . $db_err['sys_log_status_name']);
    $result .= '"' . $db_err['sys_log_text'] . '" <br>';
    if ($db_err['sys_log_description'] <> 'NULL') {
        $result .= '"' . $db_err['sys_log_description'] . '" <br>';
    }
    $result .= '<br>';
    $result .= 'Program trace:<br>';
    $result .= '' . $db_err['sys_log_trace'] . ' ';
    //echo "<font color=green>OK</font>" .$test_text;
    //echo "<font color=red>Error</font>".$test_text;

    return $result;
}


$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

$db_con = prg_start("error_log");

$result = ''; // reset the html code var

$err_id = $_GET['id'];
$back = $_GET['back'];

// load the session user parameters
$usr = new user;
$result .= $usr->get();

if ($back <= 0) {
    $back = 1; // replace with the fallback word id
}
$wrd = new word($usr);
$wrd->load_by_id($back);

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    if ($err_id > 0) {
        log_debug("error_log (" . $err_id . ")");

        load_usr_data();

        // prepare the display to edit the view
        $dsp = new view_dsp_old($usr);
        $dsp->set_id(cl(db_cl::VIEW, view::ERR_LOG));
        $result .= $dsp->dsp_navbar($back);
        //$result .= " in \"zukunft.com\" that has been logged in the system automatically by you.";
        $result .= err_dsp($err_id, $usr->id());
    }
}

echo $result;

// Closing connection
prg_end($db_con);