<?php

include_once 'zu_lib_passwords.php';

/*

  zu_lib_sql.php - olf ZUkunft.com LIBrary SQL link functions  (just just for regression code testing)
  --------------
    
  prefix: zu_sql_* 

  all functions that all directly the sql database

  
  General functions:
  -------
  zu_sql_open     - called from zu_start in all php scripts that can be called by the user
  zu_sql_close    - called at the end of all php scripts that can be called by the user
  zu_sql_add_user - add an new user for authentification and logging


  MySQL functions
  -----

  zu_sql_insert - the MariaSQL insert statement including error handling, but without logging (maybe add logging later)
  zu_sql_update - update one row using the standard zukunft.com id field


  reviewed internal functions without logging that should not be call only from *_db_* lib function that have already done the logging
  -----------------

  zudb_get - always get the complete query result as a named array
             thsi should replace zu_sql_get_all, zu_sql_get and zu_sql_get_lst


  internal change functions without logging that should not be call only from *_db_* lib function that have already done the logging
  --------------

  sql_insert       - insert only if row does not yet exsist (maybe replace by zu_sql_insert), 
  sql_set_no_log   - (to be renamed to zu_sql_upd_no_log)

  zu_sql_get_all   - backend functions that should only be used in this library
  zu_sql_get       - returns the first array of an SQL query
  zu_sql_get1      - get the first value of an SQL query
  zu_sql_get_lst   - get all values in an array
  zu_sql_get_value - same as zu_sql_get1 but only for one table field
  , zu_sql_get_value_2key
  : 
  zu_sql_word_unlink

  
  standard naming functions: 
  ---------------
  
  zu_sql_get_name, zu_sql_get_id, zu_sql_get_field, zu_sql_log_field
  
  table specific functions that can and should be called from other libraries
  --------------
  
  zu_sql_val_add         - add a new value and link it to words
  zu_sql_tbl_value       - return one value for a table
  zu_sql_word_values     - get only the values related to one word
  zu_sql_word_lst_value  - 
  zu_sql_value_lst_words - get all words related to a value list
  zu_sql_view_components    - all parts of a view 
  zu_sql_verbs           - get all possible word link types
  

  table specific list functions that return a list of items
  --------------
  
  zu_sql_word_ids_linked         - create a list of words that are foaf of the given word
  zu_sql_word_lst_linked         - same as zu_sql_word_ids_linked; only another function name
  
  

  
  table specific queries - this should be replace by functions that return a user and context specific list
  --------------
  
  zu_sql_views           - zu_sql_views
  zu_sql_views_user      - returns all non internal views
  zu_sql_view_types
  zu_sql_view_component_types - returns all view entry types 
  
  
  
  to dismiss: 
  ----------

  zu_sql_words - 
  zu_sql_views, zu_sql_view_types, zu_sql_view_component_types
  
  code ids
  preset records that are linked to the program code
  below a list of predefined verbs that have a fixed predefined behavier as const
  the const is the code_id that is also shown to to user if he/she wants to change the name

  verbs are also named as word_links
  

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

// General SQL functions
// ---------------------

// link to database
function zu_sql_open($debug) {
  log_debug("zu_sql_open", $debug-10);

  $link = mysql_connect('localhost', 'timon', SQL_DB_PASSWD) or die('Could not connect: ' . mysql_error());
  mysql_select_db('zukunft',   $link)                        or die('Could not select database');

  log_debug("zu_sql_open ... done", $debug-10);

  return $link;
}

// just to have all sql in one library
function zu_sql_close($link, $debug) {
  mysql_close($link);

  log_debug("zu_sql_close ... done", $debug-10);
}

// add the writing of potential sql errors to the sys log table to the sql execution
// includes the user to be able to ask the user for details how the error has been created
// the log level is given by the calling function because after some errors the program may nevertheless continue
function zu_sql_exe($sql, $user_id, $log_level, $function_name, $function_trace, $debug) {
  log_debug("zu_sql_exe (".$sql.",u".$user_id.",ll:".$log_level.",fn:".$function_name.",ft:".$function_trace.")", $debug-10);
  $result = mysql_query($sql);
  if (!$result) {
    $msg_text = mysql_error();
    $sql = str_replace("'", "", $sql);
    $sql = str_replace("\"", "", $sql);
    $msg_text .= " (".$sql.")";
    $msg_type_id = cl($log_level);
    $result = log_msg($msg_text, $msg_type_id, $function_name, $function_trace, $user_id);
    log_debug("zu_sql_exe -> error (".$result.")", $debug-10);
  }

  return $result;
}

// returns all values of an SQL query in an array
function zudb_get($sql, $user_id, $debug) {
  if ($debug > 10) {
    log_debug("zudb_get (".$sql.")", $debug-10);
  } else {
    log_debug("zudb_get (".substr($sql,0,100)." ... )", $debug-10);
  }
  
  $result = false;
  if ($sql <> "") {
    $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_FATAL_ERROR, "zudb_get", (new Exception)->getTraceAsString(), $debug-1);
    while ($sql_row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
      $result[] = $sql_row;
    }
  }
  
  log_debug("zudb_get -> done", $debug-10);
  return $result;
}

// get only the first record from the database
function zudb_get1($sql, $user_id, $debug) {
  if ($debug > 10) {
    log_debug("zudb_get1 (".$sql.")", $debug-10);
  } else {
    log_debug("zudb_get1 (".substr($sql,0,100)." ... )", $debug-10);
  }
  
  // optimise the sql statement
  $sql = trim($sql);
  if (strpos($sql, "LIMIT") === FALSE) {
    if (substr($sql, -1) == ";") {
      $sql = substr($sql, 0, -1) . " LIMIT 1;";
    }
  }
  
  $result = false;
  if ($sql <> "") {
    $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_FATAL_ERROR, "zudb_get1", (new Exception)->getTraceAsString(), $debug-1);
    $result = mysql_fetch_array($sql_result, MYSQL_ASSOC);
  }
  
  log_debug("zudb_get1 -> done", $debug-10);

  return $result;
}


// insert a new record in the database
// similar to zu_sql_exe, but returning the row id added to be able to update e.g. the log entry with the row id of the real row added
// writing the changes to the log table for history rollback is done at the calling function also because zu_log also uses this function
function zu_sql_insert($table, $fields, $values, $user_id, $debug) {
  log_debug("zu_sql_insert (".$table.",fld".$fields.",v".$values.",u".$user_id.")", $debug-10);

  // check parameter
  $par_ok = true;
  $table    = zu_sql_table_name  ($table, $debug-1);

  $sql = 'INSERT INTO '.$table.' ('.$fields.') ' 
       .                ' VALUES ('.$values.');';
  $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_FATAL_ERROR, "zu_sql_insert", (new Exception)->getTraceAsString(), $debug-1);
  if ($sql_result) {
    $result = mysql_insert_id();
  } else {
    $result = -1;
  }

  log_debug("zu_sql_insert -> done (".$result.")", $debug-10);
  return $result;
}

// add an new user for authentification and logging
function zu_sql_add_user($user_name, $debug) {
  log_debug("zu_sql_add_user (".$user_name.")", $debug-10);

  $sql = "INSERT INTO users (user_name) VALUES ('".$user_name."');";
  log_debug("zu_sql_update ... exec ".$sql, $debug-10);
  $sql_result = zu_sql_exe($sql, 0, DBL_SYSLOG_FATAL_ERROR, "zu_sql_add_user", (new Exception)->getTraceAsString(), $debug-1);
  // log the changes???
  $result = mysql_insert_id();

  log_debug("zu_sql_add_user ... done ".$result.".", $debug-10);

  return $result;
}

// update some values in a table
// and write the changes to the log table for history roleback
function zu_sql_update($table, $id, $fields, $values, $user_id, $debug) {
  log_debug("zu_sql_update (".$table.",".$id.",".$fields.",v".$values.",u".$user_id.")", $debug-10);
  
  // check parameter
  $par_ok = true;
  $table    = zu_sql_table_name  ($table, $debug-1);
  $type     = zu_sql_std_type    ($table, $debug-1);
  $id_field = zu_sql_std_id_field($type,  $debug-1);
  if ($debug > 0) {
    if ($table == "") {
      log_err("Table not valid for ".$fields." at ".$id.".", "zu_sql_update");
      $par_ok = false;
    } 
    if ($values == "") {
      log_err("Values missing for ".$fields." in ".$table.".", "zu_sql_update");
      $par_ok = false;
    } 
  }

  if ($par_ok) {
    if (is_array($fields)) {
      $sql = 'UPDATE '.$table;
      $sql_upd = '';
      foreach (array_keys($fields) AS $i) {
        if ($sql_upd == '') {
          $sql_upd .= ' SET '.$fields[$i].' = '.sf($values[$i]).' ';
        } else {
          $sql_upd .= ', SET '.$fields[$i].' = '.sf($values[$i]).' ';
        }
      }
      $sql = $sql.$sql_upd.' WHERE '.$id_field.' = '.sf($id).';';
    } else {
      $sql = 'UPDATE '.$table.' SET '.$fields.' = '.sf($values).' WHERE '.$id_field.' = '.sf($id).';';
    }
    $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_FATAL_ERROR, "zu_sql_update", (new Exception)->getTraceAsString(), $debug-1);
  }

  log_debug("zu_sql_update -> done (".$result.")", $debug-10);

  return $result;
}

// functions to review
// ---------------------

// insert only if row does not yet exsist
// used to add a view entry at the moment
function sql_insert($table, $id_field, $value_field, $new_value, $user_id, $debug) {
  log_debug("sql_insert (tbl:".$table.",id_fld:".$id_field.",".$value_field.",".$new_value.",".$user_id.")", $debug-10);

  $id_value = NULL;
  // don't insert empty lines
  if (trim($new_value) <> '') {
    // check if value is already added
    $id_value_lst = zu_sql_get("SELECT ".$id_field." FROM ".zu_sql_table_name ($table, $debug)." WHERE ".$value_field." = ".sf($new_value).";");
    $id_value = $id_value_lst[0];
    if ($id_value > 0) {
      log_debug("sql_insert -> ".$new_value."already exists", $debug-10);
    } else {
      log_debug("sql_insert -> do insert ".$new_value."", $debug-10);
      $sql = "INSERT INTO ".zu_sql_table_name ($table, $debug)." (".$value_field.") VALUES (".sf($new_value).");";
      $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "sql_insert", (new Exception)->getTraceAsString(), $debug-10);
      if (!$result) {
        if ($table <> 'events') {
          //echo event_add("Insert ".$table." ".$value_field." ".$new_value." failt", "Cannot insert into ".$table." the ".$value_field." ".$new_value." because: ".mysql_error().".", EVENT_TYPE_SQL_ERROR, date('Y-m-d H:i:s'), "Please contact your system administrator.", "", "", "", "", "");
        } else {
          echo "Error ".mysql_error()." when creating an event." ;
        }
      } else {
        log_debug("sql_insert -> get id for ".$new_value."", $debug-10);
        $id_value_lst = zu_sql_get("SELECT ".$id_field." FROM ".zu_sql_table_name ($table, $debug)." WHERE ".$value_field." = ".sf($new_value).";");
        $id_value = $id_value_lst[0];
        //echo "SELECT ".$value_field." FROM ".$table." WHERE ".$value_field." = '".$new_value."';<br>";
        //echo $id_value;
        //sql_log($table, $id_field, $id_value, $value_field, "", $new_value);
        //zu_sql_log_field ($table, $row_id, $user_id, $field_name, $new_value, $debug)
      }
    }
  }

  log_debug("sql_insert -> done (".$id_value.")", $debug-10);

  return $id_value;
}

// set a value in an sql table and without saving the changes (only used to update the last checked time of events)
function sql_set_no_log($table, $id_field, $id_value, $value_field, $new_value, $value_type, $debug) {
  log_debug("sql_set_no_log ... ", $debug-10);

  // get the existing value
  $db_value = zu_sql_get_value($table, $id_field, $id_value, $value_field);
  if ($value_type == 'date') {
    $db_value = strtotime($db_value);
    $new_value = date("Y-m-d", $new_value);
  }
  if ($db_value <> $new_value) {
    $sql_query = "UPDATE ".zu_sql_table_name ($table, $debug)." SET `".$value_field."` = '".$new_value."' WHERE `".$id_field."` = ".sf($id_value).";";
    //echo $sql_query;
    mysql_query($sql_query);
  }

  log_debug("sql_set_no_log ... done", $debug-10);

  return $new_value;
}

// returns all results of an SQL query 
function zu_sql_get_all($sql, $debug) {
  if ($debug > 10) {
    log_debug('zu_sql_get_all ('.$sql.')', $debug-10);
  } else {  
    log_debug('zu_sql_get_all ('.substr($sql,0, 100).' ... )', $debug-10);
  }

  $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_FATAL_ERROR, "zu_sql_get_all", (new Exception)->getTraceAsString(), $debug-10);

  log_debug("zu_sql_get_all ... done", $debug-10);

  return $result;
}

// returns the first result of an SQL query 
// e.g. in zutl_dsp all aspects of one word link are retrieved with this function
function zu_sql_get($query, $debug) {
  if ($debug > 10) {
    log_debug("zu_sql_get (".$query.")", $debug-10);
  } else {
    log_debug("zu_sql_get (".substr($query,0,100).")", $debug-10);
  }

  $sql_result =zu_sql_get_all($query, $debug-1);
  $result = mysql_fetch_array($sql_result, MYSQL_NUM); 

  log_debug("zu_sql_get ... done", $debug-10);

  return $result;
}

// returns the first result value of an SQL query 
function zu_sql_get1($query, $debug) {
  if ($debug > 10) {
    log_debug("zu_sql_get1 (".$query.")", $debug-10);
  } else {
    log_debug("zu_sql_get1 (".substr($query,0,100).")", $debug-10);
  }

  $sql_array = zu_sql_get($query, $debug-1);
  $result = $sql_array[0];

  log_debug("zu_sql_get1 ... done", $debug-10);

  return $result;
}

// returns the value list of an SQL query, where the array key is the database id and the value is the name
// e.g. 6 is the array keys and Sales the value
function zu_sql_get_lst($sql, $debug) {
  if ($debug > 10) {
    log_debug("zu_sql_get_lst (".$sql.")", $debug-10);
  } else {
    log_debug("zu_sql_get_lst (".substr($sql,0,100)." ... )", $debug-10);
  }
  
  $result = array();
  if ($sql <> "") {
    $user_id = zuu_id();
    $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_FATAL_ERROR, "zu_sql_get_lst", (new Exception)->getTraceAsString(), $debug-10);
    while ($value_entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
      $result[$value_entry[0]] = $value_entry[1];
    }
  }
  
  log_debug("zu_sql_get_lst ... done", $debug-10);

  return $result;
}

// similar to zu_sql_get_lst, but returns an array of results with the name and the type
function zu_sql_get_lst_2fld($query, $debug) {
  if ($debug > 10) {
    log_debug("zu_sql_get_lst_2fld (".$query.")", $debug-10);
  } else {
    log_debug("zu_sql_get_lst_2fld (".substr($query,0,100)." ... )", $debug-10);
  }
  
  $result = array();
  if ($query <> "") {
    $sql_result =zu_sql_get_all($query, $debug-1);
    while ($value_entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
      $row_result   = array();
      $row_result[] = $value_entry[1];
      $row_result[] = $value_entry[2];
      $result[$value_entry[0]] = $row_result;
      //zu_debug("zu_sql_get_lst_2fld -> added ".$value_entry[1]." (type ".$value_entry[2].")", $debug-10);
    }
  }
  
  log_debug("zu_sql_get_lst_2fld ... done (".zu_lst_dsp($result, $debug-1).")", $debug-10);

  return $result;
}

// returns the id list of an SQL query
// e.g. 6 is the array keys and Sales the value
function zu_sql_get_ids($sql, $debug) {
  if ($debug > 10) {
    log_debug("zu_sql_get_ids (".$sql.")", $debug-10);
  } else {
    log_debug("zu_sql_get_ids (".substr($sql,0,100)." ... )", $debug-10);
  }
  
  $result = array();
  if ($sql <> "") {
    $user_id = zuu_id();
    $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_FATAL_ERROR, "zu_sql_get_ids", (new Exception)->getTraceAsString(), $debug-10);
    while ($value_entry = mysql_fetch_array($sql_result, MYSQL_NUM)) {
      if (!in_array($value_entry[0], $result)) {
        $result[] = $value_entry[0];
      }  
    }
  }
  
  log_debug("zu_sql_get_ids -> (".implode(",",$result).")", $debug-10);

  return $result;
}

// returns first value of a simple SQL query 
function zu_sql_get_value ($table_name, $field_name, $id_name, $id, $debug) {
  log_debug("zu_sql_get_value(".$table_name.",".$field_name.",".$id_name.",".$id.")", $debug-10);

  $result = ''; 
  $query = "SELECT ".$field_name." FROM ".zu_sql_table_name ($table_name, $debug-10)." WHERE ".$id_name." = '".$id."';";
  $sql_array = zu_sql_get($query, $debug-10);
  $result = $sql_array[0];

  log_debug("zu_sql_get_value -> (".$result.")", $debug-10);

  return $result;
}

// similar to zu_sql_get_value, but for two key fields
function zu_sql_get_value_2key ($table_name, $field_name, $id1_name, $id1, $id2_name, $id2, $debug) {
  log_debug("zu_sql_get_value_2key ... ", $debug-10);

  $result = ''; 
  $query = "SELECT ".$field_name." FROM ".zu_sql_table_name ($table_name, $debug)." WHERE ".$id1_name." = '".$id1."' AND ".$id2_name." = '".$id2."';";
  $sql_array = zu_sql_get($query, $debug-1);
  $result = $sql_array[0];

  log_debug("zu_sql_get_value_2key ... done", $debug-10);

  return $result;
}



// returns one the name of a standard table
// standard table means that the table name ends with 's', the name field is the table name plus '_name' and prim index ends with '_id'
function zu_sql_get_name ($type, $id, $debug) {
  log_debug("zu_sql_get_name ... ", $debug-10);

  $result = '';
  $table_name = zu_sql_std_table      ($type, $debug-1);
  $id_name    = zu_sql_std_id_field   ($type, $debug-1);
  $field_name = zu_sql_std_name_field ($type, $debug-1);
  $result = zu_sql_get_value ($table_name, $field_name, $id_name, $id, $debug-1);

  log_debug("zu_sql_get_name ... ".$result.".", $debug-10);

  return $result;
}

// returns the id field of a standard table
// standard table means that the table name ends with 's', the name field is the table name plus '_name' and prim index ends with '_id'
function zu_sql_get_id ($type, $name, $debug) {
  log_debug("zu_sql_get_id (".$type.",".$name.")", $debug-10);

  $result = '';
  $table_name = zu_sql_std_table      ($type, $debug-1);
  $id_name    = zu_sql_std_id_field   ($type, $debug-1);
  $field_name = zu_sql_std_name_field ($type, $debug-1);
  $result = zu_sql_get_value ($table_name, $id_name, $field_name, $name, $debug-1);

  log_debug("zu_sql_get_id ... done (".$result.")", $debug-10);

  return $result;
}

// similar to zu_sql_get_id, but using a second ID field
function zu_sql_get_id_2key ($type, $name, $field2_name, $field2_value, $debug) {
  log_debug("zu_sql_get_id_2key (".$type.",".$name.",".$field2_name.",".$field2_value.")", $debug-10);

  $result = '';
  $table_name = zu_sql_std_table      ($type, $debug-1);
  $id_name    = zu_sql_std_id_field   ($type, $debug-1);
  $field_name = zu_sql_std_name_field ($type, $debug-1);
  $result = zu_sql_get_value_2key ($table_name, $id_name, $field_name, $name, $field2_name, $field2_value, $debug-1);

  log_debug("zu_sql_get_id_2key ... done (".$result.")", $debug-10);

  return $result;
}

// simple form of zu_sql_get_id_2key to get a user specific value, because this is used many times
function zu_sql_get_id_usr ($type, $name, $user_id, $debug) {
  log_debug("zu_sql_get_id_usr (t".$type.",n".$name.",u".$user_id.")", $debug-10);
  return zu_sql_get_id_2key ($type, $name, "user_id", $user_id, $debug-1);
}

function zu_sql_add_id ($type, $name, $user_id, $debug) {
  log_debug("zu_sql_add_id (".$type.",".$name.",".$user_id.")", $debug-10);

  $result = '';
  $table_name = zu_sql_std_table      ($type, $debug-1);
  $id_name    = zu_sql_std_id_field   ($type, $debug-1);
  $field_name = zu_sql_std_name_field ($type, $debug-1);
  $result = zu_sql_insert($table_name, $field_name, sf($name), $user_id, $debug-1);

  log_debug("zu_sql_add_id ... done (".$result.")", $debug-10);

  return $result;
}

// similar to zu_sql_add_id, but using a second ID field
function zu_sql_add_id_2key ($type, $name, $field2_name, $field2_value, $user_id, $debug) {
  log_debug("zu_sql_add_id_2key (".$type.",".$name.",".$field2_name.",".$field2_value.",".$user_id.")", $debug-10);

  $result = '';
  $table_name = zu_sql_std_table      ($type, $debug-1);
  $id_name    = zu_sql_std_id_field   ($type, $debug-1);
  $field_name = zu_sql_std_name_field ($type, $debug-1);
  $result = zu_sql_insert($table_name, $field_name.",".$field2_name, sf($name).",".sf($field2_value), $user_id, $debug-1);

  log_debug("zu_sql_add_id ... done (".$result.")", $debug-10);

  return $result;
}

// returns one field of a standard table
// standard table means that the table name ends with 's' and prim index ends with '_id'
function zu_sql_get_field ($type, $id, $field_name, $debug) {
  log_debug("zu_sql_get_field ... ", $debug-10);

  $result = '';
  $table_name = zu_sql_std_table    ($type, $debug-1);
  $id_name    = zu_sql_std_id_field ($type, $debug-1);
  $result = zu_sql_get_value ($table_name, $field_name, $id_name, $id, $debug-1);

  log_debug("zu_sql_get_field ... done", $debug-10);

  return $result;
}

// save a change in the log table
// must be called BEFORE the change is done in the database
function zu_sql_log_field ($table_name, $row_id, $user_id, $field_name, $new_value, $debug) {
  log_debug('zu_sql_log_field('.$table_name.','.$row_id.','.$user_id.','.$field_name.','.$new_value.')', $debug-10);

  $result = '';
  $table_id = zu_sql_get_value ("change_tables", "table_id", "name", $table_name, $debug-1);
  if ($table_id <= 0) {
    $result .= mysql_query("INSERT INTO change_tables (name) VALUES (".$table_name.")") or die('Query '.$query.' failed: ' . mysql_error());
    $table_id = zu_sql_get_value ("change_tables", "table_id", "name", $table_name, $debug-1);
  }

  $user_id = 0;
  $old_value = zu_sql_get_value ($table_name, $field_name, zu_sql_get_id_field ($table_name, $debug), $row_id, $debug-1);
  if ($table_id > 0) {
    $result .= mysql_query("INSERT INTO changes (table_id, row_id, user_id, field_name, old_value, new_value) VALUES (".$table_id.", ".$row_id.", ".$user_id.", ".$field_name.", ".$old_value.", ".$new_value.")") or die('Query '.$query.' failed: ' . mysql_error());
  }

  log_debug("zu_sql_log_field ... done", $debug-10);

  return $result;
}

// table specific functions
// ------------------------

// add a value and link it to the words
// returns the id of the new value or the error code as a negative value
// $new_value is sql ready the string of the value to added
// $word_lst is an array of word ids 
function zu_sql_val_add($new_value, $word_lst, $debug) {
  log_debug('zu_sql_val_add('.$new_value.','.implode(',',$word_lst).')', $debug-10);
  $result = 0;

  // todo: log the change
  $sql = "INSERT INTO `values` " 
       . "            (word_value)  " 
       . "     VALUES ('".$new_value."');";
  $ins_result = mysql_query($sql);
  $val_id = mysql_insert_id();
  if ($val_id > 0) {
    // to to: check if value was inserted correctly
    foreach ($word_lst as $word_id) {
      if ($word_id > 0) {
        $sql = "INSERT INTO value_phrase_links " 
             . "           (value_id   ,    phrase_id) " 
             . "    VALUES (".$val_id.", ".$word_id.");";
        $ins_result = mysql_query($sql);
      }
    }
  } else {
    $result = -1;
  } 
  log_debug('zu_sql_tbl_value ... done ('.$ins_result.')', $debug-10);

  return $result;
} 

function zu_sql_user_id($user_name, $debug) {
  $user_id = zu_sql_get_id('user', $user_name, $debug-1);
  return $user_id;
}  

// get the user_id based on the ip address
function zu_sql_user_id_by_ip($ip_address, $debug) {
  $user_id = zu_sql_get_value ("users", "user_id", "ip_address", $ip_address, $debug-1);
  return $user_id;
}  

// get the value for one table cell
function zu_sql_tbl_value($word_id, $row_word_id, $col_word_id, $user_id, $debug) {
  log_debug('zu_sql_tbl_value('.$word_id.','.$row_word_id.','.$col_word_id.','.$user_id.')', $debug-10);

  $query = "    SELECT v.`word_value`, "
         . "           v.`value_id`, "
         . "           tc.`words` "
         . "      FROM `value_phrase_links` l1, "
         . "           `value_phrase_links` l2, "
         . "           `value_phrase_links` l3, "
         . "           (SELECT l.`value_id`, "
         . "             count(l.`phrase_id`) as words "
         . "              FROM `value_phrase_links` l "
         . "          GROUP BY l.`value_id` ) as tc, "
         . "           `values` v "
         . " LEFT JOIN `user_values` u ON v.`value_id` = u.`value_id` AND u.`user_id` = ".$user_id." "
         . "     WHERE l1.`word_id` = ".$word_id." "
         . "       AND l2.`word_id` = ".$row_word_id." "
         . "       AND l3.`word_id` = ".$col_word_id." "
         . "       AND l1.`value_id` = v.`value_id` "
         . "       AND l2.`value_id` = v.`value_id` "
         . "       AND l3.`value_id` = v.`value_id` "
         . "       AND tc.`value_id` = v.`value_id` "
         . "       AND (u.`excluded` IS NULL OR u.`excluded` = 0) "
         . "  GROUP BY v.`value_id` "
         . "  ORDER BY tc.`words` ;";
  $result = zu_sql_get($query, $debug-1);

  log_debug('zu_sql_tbl_value ... done ('.$result.')', $debug-10);

  return $result;
}

// get the value for one table cell
function zu_sql_tbl_value_part($word_id, $row_word_id, $col_word_id, $part_word_id, $user_id, $debug) {
  log_debug('zu_sql_tbl_value_part('.$word_id.',r'.$row_word_id.',c'.$col_word_id.',p'.$part_word_id.',u'.$user_id.')', $debug-10);

  $sql = "    SELECT v.`word_value`, "
       . "           v.`value_id`, "
       . "           tc.`words` "
       . "     FROM  `value_phrase_links` l1, "
       . "           `value_phrase_links` l2, "
       . "           `value_phrase_links` l3, "
       . "           `value_phrase_links` l4, "
       . "           (SELECT l.`value_id`, "
       . "             count(l.`phrase_id`) as words "
       . "              FROM `value_phrase_links` l "
       . "          GROUP BY l.`value_id` ) as tc, "
       . "           `values` v "
       . " LEFT JOIN `user_values` u ON v.`value_id` = u.`value_id` AND u.`user_id` = ".$user_id." "
       . "     WHERE l1.`word_id` = ".$word_id." "
       . "       AND l2.`word_id` = ".$row_word_id." "
       . "       AND l3.`word_id` = ".$col_word_id." "
       . "       AND l4.`word_id` = ".$part_word_id." "
       . "       AND l1.`value_id` = v.`value_id` "
       . "       AND l2.`value_id` = v.`value_id` "
       . "       AND l3.`value_id` = v.`value_id` "
       . "       AND l4.`value_id` = v.`value_id` "
       . "       AND tc.`value_id` = v.`value_id` "
       . "       AND (u.`excluded` IS NULL OR u.`excluded` = 0) "
       . "  GROUP BY v.`value_id` "
       . "  ORDER BY tc.`words` ;";
  $result = zu_sql_get($sql, $debug-1);

  log_debug('zu_sql_tbl_value_part ... done ('.$result.')', $debug-10);

  return $result;
}

// get the value and a list of all words related to one value
function zu_sql_val($val_id, $user_id, $debug) {
  log_debug('zu_sql_val('.$val_id.',u'.$user_id.')', $debug-10);

  $sql = "    SELECT v.`value_id`, "
       . "           v.`word_value` "
       . "     FROM  `values` v "
       //. " LEFT JOIN `user_values` u ON v.`value_id` = u.`value_id` AND u.`user_id` = ".$user_id." "
       . "     WHERE v.`value_id` = ".$val_id." "
       //. "       AND (u.`excluded` IS NULL OR u.`excluded` = 0) "
       . "  GROUP BY v.`value_id` "
       . "  ORDER BY v.`value_id` ;";
  log_debug('zu_sql_val -> sql ('.$sql.')', $debug-10);
  $result = zu_sql_get_lst($sql, $debug-1);

  log_debug('zu_sql_val -> done ('.implode(",",$result).')', $debug-10);

  return $result;
}

// get the value and a list of all words related to one value
function zu_sql_val_wrd_lst($val_id, $user_id, $debug) {
  log_debug('zu_sql_val_wrd_lst('.$val_id.',u'.$user_id.')', $debug-10);

  $sql = "    SELECT t.`word_id`, "
       . "           t.`word_name` "
       . "     FROM  `value_phrase_links` l, "
       . "           `words` t "
       . " LEFT JOIN `user_words` u ON t.`word_id` = u.`word_id` AND u.`user_id` = ".$user_id." "
       . "     WHERE l.`value_id` = ".$val_id." "
       . "       AND l.`phrase_id`  = t.`word_id` "
       . "       AND (u.`excluded` IS NULL OR u.`excluded` = 0) "
       . "  GROUP BY t.`word_id` "
       . "  ORDER BY t.`word_id` ;";
  $result = zu_sql_get_lst($sql, $debug-1);

  log_debug('zu_sql_val_wrd_lst ... done ('.implode(",",$result).')', $debug-10);

  return $result;
}

// get a value that matches best all words in the list
// best match means the most special value is selected
// e.g if values for "ABB, Sales, Germany", " ABB, Sales, Suisse" and "ABB, Sales" are in the database
// and "ABB, Sales" is requested the value with the least words is returned, which would be "ABB, Sales" in this case
function zu_sql_wrd_ids_val($wrd_ids, $user_id, $debug) {
  log_debug('zu_sql_wrd_ids_val('.implode(",",$wrd_ids).',u'.$user_id.')', $debug-10);
  
  $result = false;

  // build the sql statement based in the number of words
  $sql_pos = 0;
  $sql_from = '';
  $sql_where = '';
  foreach ($wrd_ids AS $word_id) {
    if ($word_id > 0) {
      $sql_pos = $sql_pos + 1;
      $sql_from = $sql_from." `value_phrase_links` l".$sql_pos.", ";
      if ($sql_pos == 1) {
        $sql_where = $sql_where." WHERE l".$sql_pos.".`phrase_id` = ".$word_id." AND l".$sql_pos.".`value_id` = v.`value_id` ";
      } else {  
        $sql_where = $sql_where."   AND l".$sql_pos.".`phrase_id` = ".$word_id." AND l".$sql_pos.".`value_id` = v.`value_id` ";
      }
    }
  }
  if ($sql_where == '') {
    $sql_where  = " WHERE ";
  } else {  
    $sql_where .= " AND ";
  }
  
  // get the standard value
  $sql = " SELECT v.value_id    AS id, 
                  v.word_value  AS num, 
                  0             AS usr,  
                  v.last_update AS upd,  
                  tc.words 
              FROM `values` v, 
                  ".$sql_from."
                  (SELECT l.value_id, 
                    count(l.phrase_id) as words 
                    FROM value_phrase_links l 
                GROUP BY l.value_id ) as tc 
                  ".$sql_where."
                  tc.value_id = v.value_id 
          GROUP BY v.value_id 
          ORDER BY tc.words ;";
  //zu_debug($sql, $debug-10); 
  $wrd_val = zudb_get1($sql, $user_id, $debug-1);
  if ($wrd_val === false) {
    $result = false;
  } else {
    $val_id = $wrd_val['id'];
    if ($val_id > 0) {
      
  /*    echo "all".$val_id;
      print_r ( $wrd_val);
      echo "<br>"; */
      
      // check if there is a user specific value and if yes, return it as an array with the user
      $sql = "SELECT u.user_value  AS num,
                    u.last_update AS upd
                FROM user_values u
              WHERE u.value_id = ".$val_id."
                AND u.user_id = ".$user_id.";";
      $usr_val = zudb_get1($sql, $user_id, $debug-1);
      if ($usr_val <> false) {
  /*      echo "user".$usr_val['num'];
        print_r ( $usr_val);
        echo "<br>"; */
        $wrd_val['num'] = $usr_val['num'];
        $wrd_val['usr'] = $user_id;
        $wrd_val['upd'] = $usr_val['upd'];
      } 
    } 
  }
  
/*  if ($val_id > 0) {
    echo "out".$val_id." for ".$user_id;
    print_r ( $wrd_val);
    echo "<br>"; 
  } */
  return $wrd_val;
}

// the three functions should not be used any more

function zu_sql_word_ids_value($wrd_ids, $user_id, $debug) {
  $wrd_val = zu_sql_wrd_ids_val($wrd_ids, $user_id, $debug-1);  
  return $wrd_val['num'];
}

function zu_sql_word_lst_value($wrd_ids, $user_id, $debug) {
  $wrd_val = zu_sql_wrd_ids_val($wrd_ids, $user_id, $debug-1);
  return $wrd_val['num'];
}

function zu_sql_word_lst_value_id($wrd_ids, $user_id, $debug) {
  $wrd_val = zu_sql_wrd_ids_val($wrd_ids, $user_id, $debug-1);  
  return $wrd_val['id'];
}


// get only the values related to one word
function zu_sql_word_values($word_id, $user_id, $debug) {
  log_debug('zu_sql_word_values('.$word_id.',u'.$user_id.')', $debug-10);

  $result = array();
  if ($word_id > 0) {
    $sql = "    SELECT l.`value_id`, "
           . "           v.`word_value`, "
           . "           u.`user_value`, "
           . "           v.`excluded`, "
           . "           u.`excluded` AS user_excluded "
           . "      FROM `value_phrase_links` l, "
           . "           `values` v "
           . " LEFT JOIN `user_values` u ON v.`value_id` = u.`value_id` AND u.`user_id` = ".$user_id." "
           . "     WHERE l.`word_id` = ".$word_id." "
           . "       AND l.`value_id` = v.`value_id` "
           . "       AND (u.`excluded` IS NULL OR u.`excluded` = 0) "
           . "  GROUP BY l.`value_id` "
           . "  ORDER BY v.`word_value`;";
    $result = zu_sql_get_lst($sql, $debug-1);

    log_debug('zu_sql_word_values ... done ('.implode(",",$result).')', $debug-10);
  }

  return $result;
}

// get the word name for an array of word ids
function zu_sql_wrd_ids_to_lst_names($word_ids, $user_id, $debug) {
  log_debug('zu_sql_wrd_ids_to_lst_names('.implode(",",$word_ids).'u'.$user_id.')', $debug-10);

  $result = array();
  if (!empty($word_ids)) {
    $sql = "    SELECT t.`word_id`, "
         . "           IF(u.`word_name` IS NULL,t.`word_name`,u.`word_name`) AS word_name "
         . "      FROM `words` t "
         . " LEFT JOIN `user_words` u ON t.`word_id` = u.`word_id` AND u.`user_id` = ".$user_id." "
         . "     WHERE t.`word_id` IN (".implode(",",$word_ids).") "
         . "       AND (u.`excluded` IS NULL OR u.`excluded` = 0) "
         . "  GROUP BY t.`word_id` "
         . "  ORDER BY t.`word_name`;";
    $result = zu_sql_get_lst($sql, $debug-1);

    log_debug('zu_sql_wrd_ids_to_lst_names -> done ('.zu_lst_dsp($result).')', $debug-10);
  }

  return $result;
}

// get the word name for an array of word ids
function zu_sql_wrd_ids_to_lst($word_ids, $user_id, $debug) {
  log_debug('zu_sql_wrd_ids_to_lst('.implode(",",$word_ids).'u'.$user_id.')', $debug-10);

  $result = array();
  if (!empty($word_ids)) {
    $sql = "    SELECT t.`word_id`, "
         . "           IF(u.`word_name` IS NULL,t.`word_name`,u.`word_name`) AS word_name, "
         . "           t.`word_type_id`, "
         . "           u.`excluded` AS user_excluded "
         . "      FROM `words` t "
         . " LEFT JOIN `user_words` u ON t.`word_id` = u.`word_id` AND u.`user_id` = ".$user_id." "
         . "     WHERE t.`word_id` IN (".implode(",",$word_ids).") "
         . "       AND (u.`excluded` IS NULL OR u.`excluded` = 0) "
         . "  GROUP BY t.`word_id` "
         . "  ORDER BY t.`word_id`;";
    $result = zu_sql_get_lst_2fld($sql, $debug-1);

    log_debug('zu_sql_wrd_ids_to_lst -> done ('.zu_lst_dsp($result).')', $debug-10);
  }

  return $result;
}

// get a list of values related to a word with the word ids link to each value
function zu_sql_val_lst_wrd ($word_id, $user_id, $debug) {
  log_debug('zu_sql_val_lst_wrd('.$word_id.','.$user_id.')', $debug-10);

  $result = array();
  if ($word_id > 0) {
    $sql = " SELECT l.value_id, 
                    IF(u.user_value IS NULL,v.word_value,u.user_value) AS word_value, 
                    t.word_id, 
                    v.excluded, 
                    u.excluded AS user_excluded,
                    IF(u.user_value IS NULL,0,u.user_id) AS user_id
               FROM `value_phrase_links` l, 
                    `value_phrase_links` t, 
                    `values` v 
          LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = ".$user_id." 
              WHERE l.phrase_id = ".$word_id." 
                AND l.value_id = v.value_id 
                AND (u.excluded IS NULL OR u.excluded = 0) 
                AND v.value_id = t.value_id 
           GROUP BY l.value_id, t.word_id 
           ORDER BY l.value_id, t.word_id;";

    $result = array();
    if ($sql <> "") {
      //zu_debug('zu_sql_val_lst_wrd -> sql '.$sql.')', $debug-10);  
      $sql_result = zu_sql_get_all($sql, $debug-1);
      $value_id = -1; // set to an id that is never used to force the creation of a new entry at start
      while ($val_entry = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
        if ($value_id == $val_entry['value_id']) {
          $wrd_result[] = $val_entry['word_id'];
          //zu_debug('zu_sql_val_lst_wrd -> add word '.$val_entry['word_id'].' to ('.$value_id.')', $debug-10);  
        } else {  
          if ($value_id >= 0) {
            // remember the previous values
            $row_result[] = $wrd_result;
            $result[$value_id] = $row_result;
            //zu_debug('zu_sql_val_lst_wrd -> add value '.$value_id.'', $debug-10);  
          } 
          // remember the values for a new result row
          $value_id  = $val_entry['value_id'];
          $val_num = $val_entry['word_value'];
          $val_usr = $val_entry['user_id'];
          $row_result   = array();
          $row_result[] = $val_num;
          $row_result[] = $val_usr;
          $wrd_result   = array();
          $wrd_result[] = $val_entry['word_id'];
          //zu_debug('zu_sql_val_lst_wrd -> found value '.$value_id.'', $debug-10);  
        }  
      } 
      if ($value_id >= 0) {
        // remember the last values
        $row_result[] = $wrd_result;
        $result[$value_id] = $row_result;
      } 
    }

    log_debug('zu_sql_val_lst_wrd ... done ('.zu_lst_dsp($result).')', $debug-10);
  }
  
  return $result;
}

// select the values related to a word list
function zu_sql_word_lst_values($word_ids, $value_ids, $user_id, $debug) {
  log_debug('zu_sql_word_lst_values('.implode(",",$word_ids).'v'.implode(",",$value_ids).'u'.$user_id.')', $debug-10);

  if (sizeof($value_ids) > 0) {
    $sql = "   SELECT v.`value_id`, "
         . "          v.`word_value` "
         . "     FROM `value_phrase_links` l, "
         . "          `values` v "
         . "    WHERE l.`phrase_id`  IN (".implode(",",$word_ids).") "
         . "      AND l.`value_id` IN (".implode(",",$value_ids).") "
         . "      AND l.`value_id` = v.`value_id` "
         . " GROUP BY v.`value_id`;";
    log_debug('zu_sql_word_lst_values -> sql ('.$sql.')', $debug-10);
    $result = zu_sql_get_lst($sql, $debug-1);
  } else {
    $result = false;
  }

  log_debug('zu_sql_word_lst_values ... done ('.implode(",",$result).')', $debug-10);

  return $result;
}

// add extra words to row words if the extra word is a differentiator
function zu_sql_word_lst_add_differantiator($word_lst, $xtra_words, $debug) {
  log_debug('zu_sql_word_lst_add_differantiator('.$word_lst.','.$xtra_words.')', $debug-10);
  
  $is_a_type = sql_code_link(DBL_LINK_TYPE_IS);
  $differantiator_type = sql_code_link(DBL_LINK_TYPE_DIFFERENTIATOR);

  // add all words that are "is a" to the $differantiator list e.g. if the extra list contains Switzerland and Country is allowed as a differentiator Switzerland should be taken into account
  echo 'extra: '.implode(",",$xtra_words).'<br>';
  $added_words = zu_sql_word_lst_linked($xtra_words, $is_a_type, "down", $debug-1);
  $added_words = zu_lst_not_in($added_words, $xtra_words, $debug-1);
  // while (!empty($added_words)) {
  if (!empty($added_words)) {
    echo 'added: '.implode(",",$added_words).'<br>';
    $xtra_words = zu_lst_merge_with_key($added_words, $xtra_words);
    echo 'combi: '.implode(",",$xtra_words).'<br>';
    $added_words = zu_sql_word_lst_linked($xtra_words, $is_a_type, "down", $debug-10);
    $added_words = zu_lst_not_in($added_words, $xtra_words, $debug-1);
  }
  
  $differentiator_words = zu_sql_word_lst_linked($xtra_words, $differantiator_type, "down", $debug-1);
  echo '+diff: '.implode(",",$differentiator_words).'<br>';

  $result = array();
  foreach (array_keys($word_lst) as $lst_entry) {
    // add the original entry
    $result[$lst_entry] = $word_lst[$lst_entry];
    // add the extra word if it is a differentiator
    //if (!in_array($lst_entry, array_keys($differentiator_words))) {
    //  $result[$lst_entry] = $in_lst[$lst_entry];
    //}
  }    
  return $result;

  log_debug('zu_sql_word_lst_add_differantiator ... done ('.implode(",",$result).')', $debug-10);

  return $result;
}

// get all words related to a value list
function zu_sql_value_ids_words($val_ids, $user_id, $debug) {
  log_debug("zu_sql_value_ids_words(".implode(",",$val_ids).")", $debug-10);

  if (sizeof($val_ids) > 0) {
    $query = "   SELECT l.phrase_id, "
           . "          t.word_name, "
           . "          t.word_type_id "
           . "     FROM value_phrase_links l, "
           . "          words t "
           . "    WHERE l.value_id in (".implode(",",$val_ids).") "
           . "      AND l.phrase_id = t.word_id "
           . " GROUP BY l.phrase_id;";
    $result = zu_sql_get_lst_2fld($query, $debug-1);
  } else {
    $result = "";
  }

  log_debug("zu_sql_value_ids_words ... done (".zu_lst_dsp($result).")", $debug-10);

  return $result;
}

// similar to zu_sql_value_ids_words, but for a value list
function zu_sql_value_lst_words($val_lst, $user_id, $debug) {
  log_debug("zu_sql_value_lst_words(".implode(",",$val_lst).",u".$user_id.")", $debug-10);
  
  $val_sql = trim(implode(",",array_keys($val_lst)));

  if ($val_sql <> '') {
    $query = "   SELECT l.phrase_id AS word_id, "
           . "          t.word_name, "
           . "          t.word_type_id "
           . "     FROM value_phrase_links l, "
           . "          words t "
           . "    WHERE l.value_id in (".implode(",",array_keys($val_lst)).") "
           . "      AND l.phrase_id = t.word_id "
           . " GROUP BY l.phrase_id;";
    $result = zu_sql_get_lst_2fld($query, $debug-1);
  } else {
    $result = "";
  }

  log_debug("zu_sql_value_lst_words ... done (".zu_lst_dsp($result).")", $debug-10);

  return $result;
}

// loops over a value list and add the word ids to the array
function zu_sql_value_lst_add_words($val_lst, $user_id, $debug) {
  log_debug("zu_sql_value_lst_add_words(".implode(",",$val_lst).")", $debug-10);

  $result = array();
  if (sizeof($val_lst) > 0) {
    $query = "   SELECT l.phrase_id, "
           . "          t.word_name, "
           . "          t.word_type_id "
           . "     FROM value_phrase_links l, "
           . "          words t "
           . "    WHERE l.value_id in (".implode(",",array_keys($val_lst)).") "
           . "      AND l.phrase_id = t.word_id "
           . " GROUP BY l.phrase_id;";
    $result = zu_sql_get_lst_2fld($query, $debug-1);
  } else {
    $result = "";
  }

  log_debug("zu_sql_value_lst_add_words ... done (".zu_lst_dsp($result).")", $debug-10);

  return $result;
}

// get all words that are linked to all values of the value list
function zu_sql_value_lst_common_words($value_lst, $debug) {
  log_debug('zu_sql_value_lst_common_words('.implode(",",$value_lst).')', $debug-10);
  $result = array();
  
  if (count($value_lst) > 0) {
    $query = " /* get the words used for each value */ "
          . " SELECT v_usage.word_id, v_usage.word_name "
          . "   FROM ( /* get all words used and count the number of usage */ "
          . "          SELECT l.phrase_id, t.word_name, COUNT(l.value_id) AS word_usage "
          . "            FROM value_phrase_links l, words t  "
          . "           WHERE l.value_id IN (".implode(",",$value_lst).")   "
          . "             AND l.phrase_id = t.word_id "
          . "        GROUP BY l.phrase_id) AS v_usage "
          . "  WHERE v_usage.word_usage = "
          . "        ( /* get the max number of words really used */ "
          . "          SELECT MAX(word_usage) AS max_words "
          . "            FROM ( /* get the real number of words for each value */ "
          . "                   SELECT COUNT(l.value_id) AS word_usage "
          . "                     FROM value_phrase_links l, words t  "
          . "                    WHERE l.value_id IN (".implode(",",$value_lst).")  "
          . "             AND l.phrase_id = t.word_id "
          . "        GROUP BY l.phrase_id) AS t_usage);";
    $result = zu_sql_get_lst($query, $debug-1);
  }

  log_debug("zu_sql_value_lst_common_words ... done(".implode(",",$result).")", $debug-10);

  return $result;
}

// returns all parts of a view 
function zu_sql_view_components($view_id, $user_id, $debug) {
  log_debug('zu_sql_view_components('.$view_id.')', $debug-10);

  $sql = " SELECT e.view_component_name, e.word_id_row, e.link_type_id, e.view_component_type_id, e.formula_id, e.view_component_id, t.code_id, e.word_id_col 
               FROM view_components e, view_component_links l, view_component_types t 
              WHERE l.view_id = ".$view_id." 
                AND l.view_component_id = e.view_component_id 
                AND e.view_component_type_id = t.view_component_type_id 
           ORDER BY l.order_nbr;";
  log_debug("zu_sql_view_components ... ".$sql, $debug-12);
  $result = zu_sql_get_all($sql, $debug-1);

  log_debug("zu_sql_view_components ... done", $debug-10);

  return $result;
}

// returns the next free order number for a new view entry
function zu_sql_view_component_next_nbr($view_id, $user_id, $debug) {
  log_debug('zu_sql_view_component_next_nbr('.$view_id.')', $debug-10);

  $query = "   SELECT max(l.order_nbr) 
                 FROM view_component_links l 
                WHERE l.view_id = ".$view_id." 
             ORDER BY l.order_nbr;";
  $result = zu_sql_get1($query, $debug-1);
  
  // if nothing is found, assume one as the next free number
  if ($result <= 0) {
    $result = 1;
  }

  log_debug("zu_sql_view_component_next_nbr -> (".$result.")", $debug-10);

  return $result;
}

// get all possible word link types
function zu_sql_verbs($user_id, $debug) {
  log_debug("zu_sql_verbs(".$user_id.")", $debug-10);

  $sql = "   SELECT l.verb_id, "
       . "          l.verb_name "
       . "     FROM verbs l "
       . " ORDER BY l.type_name;";
    $result = zu_sql_get_lst($sql, $debug-1);

  log_debug("zu_sql_verbs ... done (".implode(",",$result).")", $debug-10);

  return $result;
}

/*
  word tree building
*/

// returns the words linked to a given word
function zu_sql_word_lst_linked($word_lst, $verb_id, $direction, $debug) {
  log_debug('zu_sql_word_lst_linked('.implode(",",$word_lst).','.$verb_id.','.$direction.')', $debug-10);

  $result = array();

  if (implode(",",array_keys($word_lst)) <> "") {
    if ($verb_id > 0) {
      $sql_link = " AND l.verb_id = ".$verb_id." ";
    } else {  
      $sql_link = " ";
    }
      
    if ($direction == "up") {
        $sql_dir = " l.from_phrase_id = t.word_id AND l.to_phrase_id   IN (".implode(",",array_keys($word_lst)).") ";
    } else {  
        $sql_dir = " l.to_phrase_id   = t.word_id AND l.from_phrase_id IN (".implode(",",array_keys($word_lst)).") ";
    }    

    $sql = "SELECT t.word_id, t.word_name, t.word_type_id, l.word_link_id "
        . "  FROM word_links l, words t "
        . " WHERE " . $sql_dir
        .             $sql_link
        . " ORDER BY t.word_name;";

    $result = zu_sql_get_lst($sql, $debug-1);
  }
       
  return $result;
}

// create a list of words that are foaf of the given word
function zu_sql_word_ids_linked($word_lst, $verb_id, $direction, $debug) {
  log_debug('zu_sql_word_ids_linked('.implode(",",$word_lst).','.$verb_id.','.$direction.')', $debug-10);

  $result = array();

  if (implode(",",$word_lst) <> "") {
    if ($verb_id > 0) {
      $sql_link = " AND l.verb_id = ".$verb_id." ";
    } else {  
      $sql_link = " ";
    }
      
    if ($direction == "up") {
        $sql_dir = " l.from_phrase_id = t.word_id AND l.to_phrase_id   IN (".implode(",",$word_lst).") ";
    } else {  
        $sql_dir = " l.to_phrase_id   = t.word_id AND l.from_phrase_id IN (".implode(",",$word_lst).") ";
    }    

    $sql = "SELECT t.word_id, t.word_name, t.word_type_id, l.word_link_id "
        . "  FROM word_links l, words t "
        . " WHERE " . $sql_dir
        .             $sql_link
        . " ORDER BY t.word_name;";

    $result = zu_sql_get_lst($sql, $debug-1);
  }
       
  return $result;
}

// simple query returning functions
// --------------------------------

// sql to returns all words used for the word selector in view entry edit
function zu_sql_words($user_id, $debug) {
  $query = "SELECT t.word_id, 
              IF ( u.word_name IS NULL, t.word_name, u.word_name ) AS name
              FROM words t
         LEFT JOIN user_words u ON (t.word_id = u.word_id 
                                AND u.user_id = ".$user_id."
                                AND (u.excluded is NULL OR u.excluded = 0))
            WHERE (t.excluded is NULL OR t.excluded = 0)  
          ORDER BY name;";
  return $query;
}

// returns the words linked to a given word
function zu_sql_words_linked($word_id, $verb_id, $direction, $user_id, $debug) {
  log_debug('zu_sql_words_linked(t'.$word_id.',v'.$verb_id.','.$direction.',u'.$user_id.')', $debug-10);

  if ($word_id > 0) {
    if ($verb_id > 0) {
      $sql_link = " AND l.verb_id = ".$verb_id." ";
    } else {  
      $sql_link = " ";
    }
      
    if ($direction == "down") {
        $sql_dir = " l.from_phrase_id = t.word_id AND l.to_phrase_id   = ".$word_id." ";
    } else {  
        $sql_dir = " l.to_phrase_id   = t.word_id AND l.from_phrase_id = ".$word_id." ";
    }    

    $sql = "SELECT t.word_id, t.word_name, t.word_type_id, l.word_link_id "
        . "  FROM word_links l, words t "
        . " WHERE " . $sql_dir
        .             $sql_link
        . " ORDER BY t.word_name;";
  } else {
    $sql = "";
  }
       
  return $sql;
}

//
function zu_sql_word_unlink($link_id, $debug) {
  log_debug('zu_sql_word_unlink('.$link_id.')', $debug-10);
  $sql = "DELETE FROM `word_links` WHERE word_link_id = ".$link_id.";";
  return mysql_query($sql);
}

// returns all views 
function zu_sql_views($debug) {
  $query = "SELECT view_id, view_name "
         . "  FROM views;";
  return $query;
}

// returns all non internal views 
function zu_sql_views_user($debug) {
  $query = "SELECT view_id, view_name "
         . "  FROM views "
         . " WHERE code_id IS NULL;";
  return $query;
}

// returns all view types 
function zu_sql_view_types($debug) {
  $query = "SELECT view_type_id, type_name "
         . "  FROM view_types;";
  return $query;
}

// returns all view entry types 
function zu_sql_view_component_types($debug) {
  $query = "SELECT view_component_type_id, view_component_type_name "
         . "  FROM view_component_types;";
  return $query;
}

// returns all view entries 
function zu_sql_view_component_lst($debug) {
  $query = "SELECT view_component_id, view_component_name "
         . "  FROM view_components;";
  return $query;
}

/*
general database function that are using the word, value and formula libraries
----------------
*/

// returns the id of a word, verb or formula including the internal formula maker
// used to prevent double entries
// it checks the name universe of each user seperately
function zu_sql_id($name, $user_id, $debug) {
  log_debug("zu_sql_id (".$name.",u".$user_id.")", $debug-10);

  $result = "";
  $wrd_id = zut_id($name, $user_id, $debug-1);
  if ($wrd_id > 0) {
    $result = ZUP_CHAR_WORD_START.$wrd_id.ZUP_CHAR_WORD_END;  
  }
  if ($result == '') {
    $lnk_id = zul_id($name, $debug-1);
    if ($lnk_id > 0) {
      $result = ZUP_CHAR_LINK_START.$lnk_id.ZUP_CHAR_LINK_END;  
    } else {
      $lnk_id = zu_sql_get_value ('verbs', 'link_type_id', 'formula_name', $name, $debug-1);
    }
    if ($lnk_id > 0) {
      $result = ZUP_CHAR_LINK_START.$lnk_id.ZUP_CHAR_LINK_END;  
    }
  }
  if ($result == '') {
    $frm_id = zuf_id($name, $user_id, $debug-1);
    if ($frm_id > 0) {
      $result = ZUP_CHAR_FORMULA_START.$frm_id.ZUP_CHAR_FORMULA_END;  
    }
  }
  return $result;
}

// linked to zu_sql_id and returns a message for the user for the double naming and offers a solution
function zu_sql_id_msg($id_txt, $id_name, $user_id, $debug) {
  log_debug("zu_sql_id_msg (".$id_txt.",".$id_name.",u".$user_id.")", $debug-10);

  $result = "";
  if (zu_str_is_left($id_txt, ZUP_CHAR_WORD_START)) {
    $wrd_id = zu_str_between($id_txt, ZUP_CHAR_WORD_START, ZUP_CHAR_WORD_END);
    if ($wrd_id > 0) {
      $result = zuh_err('A word with the name "'.$id_name.'" already exists. Please use another name.');
    }
  }
  // check if verb exists
  if (zu_str_is_left($id_txt, ZUP_CHAR_LINK_START)) {
    $lnk_id = zu_str_between($id_txt, ZUP_CHAR_LINK_START, ZUP_CHAR_LINK_END);
    if ($lnk_id > 0) {
      $result = zuh_err('A verb with the name "'.$id_name.'" already exists. Please use another name.');
    }
  }
  // check if word exists
  if (zu_str_is_left($id_txt, ZUP_CHAR_FORMULA_START)) {
    $frm_id = zu_str_between($id_txt, ZUP_CHAR_FORMULA_START, ZUP_CHAR_FORMULA_END);
    if ($frm_id > 0) {
      $result = zuh_err('A formula with name "'.$id_name.'" already exists. Please use another name.');
    }
  }
  return $result;
}
// 
// does a database consistency check to detect and repair code errors
function zu_sql_check($debug) {
  // check for double names and resolve the problem
}


?>
