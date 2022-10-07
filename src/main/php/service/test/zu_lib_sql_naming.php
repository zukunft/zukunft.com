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

// zu_sql_std: fuctions for the standard naming of tables
function zu_sql_std_table ($type) {
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

function zu_sql_std_id_field ($type) {  
  log_debug("zu_sql_std_id_field (".$type.")");

  // exceptions for user overwrite tables
  if (zu_str_is_left($type, sql_db::TBL_USER_PREFIX)) {
    $type = zu_str_right_of($type, sql_db::TBL_USER_PREFIX);
  }
  $result = $type.'_id';
  // exceptions for nice english
  if ($type == 'view_entrie') {
    $result = 'view_component_id';
  }

  log_debug("zu_sql_std_id_field -> (".$result.")");
  return $result;
}

function zu_sql_std_name_field ($type) {
  log_debug("zu_sql_std_name_field (".$type.")");

  // exceptions for user overwrite tables
  if (zu_str_is_left($type, sql_db::TBL_USER_PREFIX)) {
    $type = zu_str_right_of($type, sql_db::TBL_USER_PREFIX);
  }
  $result = $type.'_name';
  // exceptions to be adjusted
  if ($result == 'link_type_name') {
    $result = 'type_name';
  }
  if ($result == 'word_type_name') {
    $result = 'type_name';
  }

  log_debug("zu_sql_std_name_field -> (".$result.")");
  return $result;
}

function zu_sql_std_type ($table_name) {
  log_debug("sql_code_link ... ");

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

  log_debug("zu_sql_std_type ... done");

  return $result;
}

// create a standard query for a list of database id and name
function zu_sql_std_lst ($type) {
  log_debug("zu_sql_std_lst (".$type.")");
  
  $table    = zu_sql_std_table      ($type);
  $id_fld   = zu_sql_std_id_field   ($type);
  $name_fld = zu_sql_std_name_field ($type);
  $sql = "SELECT ".$id_fld.", ".$name_fld." FROM ".$table." ORDER BY ".$name_fld.";";
  
  return $sql;
}

// formats the table name for the sql statement
function zu_sql_table_name ($table_name) {
  log_debug("zu_sql_table_name(".$table_name.")");

  $result = '';
  if (substr($table_name, 0, 1) == "`") {
    $result   = $table_name;
  } else {
    $result   = "`".$table_name."`";
  }  

  log_debug("zu_sql_table_name ... done (".$result.")");

  return $result;
}

// returns the pk for the given table
function zu_sql_get_id_field ($table_name) {
  log_debug("zu_sql_get_id_field ... ");

  $type   = zu_sql_std_type ($table_name);
  $result = zu_sql_std_id_field ($type);

  log_debug("zu_sql_get_id_field ... done");

  return $result;
}


?>
