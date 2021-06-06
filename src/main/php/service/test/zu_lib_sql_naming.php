<?php

/*

  zu_lib_sql.php - old ZUkunft.com LIBrary SQL NAMING convention functions  (just just for regression code testing)
  --------------
  
  These functions do NOT call the database, they are just used to define the database naming convention for zukunft.com
  prefix: zu_sql_std_* 

  STanDard naming functions: 
  ---------------
  
  zu_sql_std_table      -
  zu_sql_std_id_field   -
  zu_sql_std_name_field -
  zu_sql_std_type       - 
  zu_sql_table_name     - 
  zu_sql_get_id_field   - 
  

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

// zu_sql_std: fuctions for the standard naming of tables
function zu_sql_std_table ($type, $debug) {
  $result =$type."s";
  // exceptions
  if ($result == 'sys_logs') {
    $result = 'sys_log';
  }
  if ($result == 'sys_log_statuss') {
    $result = 'sys_log_status';
  }
  $result = zu_sql_table_name($result);
  return $result;
}

function zu_sql_std_id_field ($type, $debug) {  
  log_debug("zu_sql_std_id_field (".$type.")", $debug);

  // exceptions for user overwrite tables
  if (zu_str_is_left($type, 'user_')) {
    $type = zu_str_right_of($type, 'user_');
  }
  $result = $type.'_id';
  // exceptions for nice english
  if ($type == 'view_entrie') {
    $result = 'view_component_id';
  }

  log_debug("zu_sql_std_id_field -> (".$result.")", $debug);
  return $result;
}

function zu_sql_std_name_field ($type, $debug) {
  log_debug("zu_sql_std_name_field (".$type.")", $debug);

  // exceptions for user overwrite tables
  if (zu_str_is_left($type, 'user_')) {
    $type = zu_str_right_of($type, 'user_');
  }
  $result = $type.'_name';
  // exceptions to be adjusted
  if ($result == 'link_type_name') {
    $result = 'type_name';
  }
  if ($result == 'word_type_name') {
    $result = 'type_name';
  }

  log_debug("zu_sql_std_name_field -> (".$result.")", $debug);
  return $result;
}

function zu_sql_std_type ($table_name, $debug) {
  log_debug("sql_code_link ... ", $debug);

  $result = '';
  if (substr($table_name, 0, 1) == "`") {
    $result   = substr($table_name, 1, -2);
  } else {
    $result   = substr($table_name, 0, -1); // remove the "s" at the end 
  }  
  // exceptions to be adjusted
  if ($result == 'sys_lo') {
    $result = 'sys_log';
  }

  log_debug("zu_sql_std_type ... done", $debug-1);

  return $result;
}

// create a standard query for a list of database id and name
function zu_sql_std_lst ($type, $debug) {
  log_debug("zu_sql_std_lst (".$type.")", $debug);
  
  $table    = zu_sql_std_table      ($type, $debug-1);
  $id_fld   = zu_sql_std_id_field   ($type, $debug-1);
  $name_fld = zu_sql_std_name_field ($type, $debug-1);
  $sql = "SELECT ".$id_fld.", ".$name_fld." FROM ".$table." ORDER BY ".$name_fld.";";
  
  return $sql;
}

// formats the table name for the sql statement
function zu_sql_table_name ($table_name, $debug) {
  log_debug("zu_sql_table_name(".$table_name.")", $debug-1);

  $result = '';
  if (substr($table_name, 0, 1) == "`") {
    $result   = $table_name;
  } else {
    $result   = "`".$table_name."`";
  }  

  log_debug("zu_sql_table_name ... done (".$result.")", $debug-1);

  return $result;
}

// returns the pk for the given table
function zu_sql_get_id_field ($table_name, $debug) {
  log_debug("zu_sql_get_id_field ... ", $debug);

  $type   = zu_sql_std_type ($table_name, $debug);
  $result = zu_sql_std_id_field ($type, $debug);

  log_debug("zu_sql_get_id_field ... done", $debug-1);

  return $result;
}


?>
