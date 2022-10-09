<?php

/*

  zu_lib_sql.php - olf ZUkunft.com LIBrary SQL link functions  (just just for regression code testing)
  --------------
    
  prefix: zu_sql_* 

  all functions that all directly the sql database

  
  General functions:
  -------
  zu_sql_open     - called from zu_start in all php scripts that can be called by the user
  zu_sql_close    - called at the end of all php scripts that can be called by the user
  zu_sql_add_user - add an new user for authentication and logging


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

  sql_insert       - insert only if row does not yet exist (maybe replace by zu_sql_insert),
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
  below a list of predefined verbs that have a fixed predefined behavior as const
  the const is the code_id that is also shown to to user if he/she wants to change the name

  verbs are also named as word_links
  

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

// General SQL functions
// ---------------------


// add the writing of potential sql errors to the sys log table to the sql execution
// includes the user to be able to ask the user for details how the error has been created
// the log level is given by the calling function because after some errors the program may nevertheless continue
function zu_sql_exe($sql, $user_id, $log_level, $function_name, $function_trace)
{
    global $db_con;
    log_debug("zu_sql_exe (" . $sql . ",u" . $user_id . ",ll:" . $log_level . ",fn:" . $function_name . ",ft:" . $function_trace . ")");
    $result = mysqli_query($db_con, $sql);
    if (!$result) {
        $msg_text = mysqli_error($db_con);
        $sql = str_replace("'", "", $sql);
        $sql = str_replace("\"", "", $sql);
        $msg_text .= " (" . $sql . ")";
        $result = log_msg($msg_text, '', $log_level, $function_name, $function_trace, $user_id);
        log_debug("zu_sql_exe -> error (" . $result . ")");
    }

    return $result;
}


// get only the first record from the database
function zudb_get1($sql, $user_id)
{
    global $debug;
    if ($debug > 10) {
        log_debug("zudb_get1 (" . $sql . ")");
    } else {
        log_debug("zudb_get1 (" . substr($sql, 0, 100) . " ... )");
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
        $sql_result = zu_sql_exe($sql, $user_id, sys_log_level::FATAL, "zudb_get1", (new Exception)->getTraceAsString());
        $result = mysqli_fetch_array($sql_result, MySQLi_ASSOC);
    }

    log_debug("zudb_get1 -> done");

    return $result;
}


// insert a new record in the database
// similar to zu_sql_exe, but returning the row id added to be able to update e.g. the log entry with the row id of the real row added
// writing the changes to the log table for history rollback is done at the calling function also because zu_log also uses this function
function zu_sql_insert($table, $fields, $values, $user_id)
{
    global $db_con;
    log_debug("zu_sql_insert (" . $table . ",fld" . $fields . ",v" . $values . ",u" . $user_id . ")");

    // check parameter
    $par_ok = true;
    $table = zu_sql_table_name($table);

    $sql = 'INSERT INTO ' . $table . ' (' . $fields . ') '
        . ' VALUES (' . $values . ');';
    $sql_result = zu_sql_exe($sql, $user_id, sys_log_level::FATAL, "zu_sql_insert", (new Exception)->getTraceAsString());
    if ($sql_result) {
        $result = mysqli_insert_id($db_con);
    } else {
        $result = -1;
    }

    log_debug("zu_sql_insert -> done (" . $result . ")");
    return $result;
}

// add a new user for authentication and logging
function zu_sql_add_user($user_name)
{
    global $db_con;
    log_debug("zu_sql_add_user (" . $user_name . ")");

    $sql = "INSERT INTO users (user_name) VALUES ('" . $user_name . "');";
    log_debug("zu_sql_update ... exec " . $sql);
    $sql_result = zu_sql_exe($sql, 0, sys_log_level::FATAL, "zu_sql_add_user", (new Exception)->getTraceAsString());
    // log the changes???
    $result = mysqli_insert_id($db_con);

    log_debug("zu_sql_add_user ... done " . $result . ".");

    return $result;
}

// update some values in a table
// and write the changes to the log table for history rollback
function zu_sql_update($table, $id, $fields, $values, $user_id): bool
{
    log_debug("zu_sql_update (" . $table . "," . $id . "," . $fields . ",v" . $values . ",u" . $user_id . ")");
    global $debug;
    global $db_con;
    $result = false;

    // check parameter
    $par_ok = true;
    $table = zu_sql_table_name($table);
    $type = zu_sql_std_type($table);
    $id_field = zu_sql_std_id_field($type,);
    if ($debug > 0) {
        if ($table == "") {
            log_err("Table not valid for " . $fields . " at " . $id . ".", "zu_sql_update");
            $par_ok = false;
        }
        if ($values == "") {
            log_err("Values missing for " . $fields . " in " . $table . ".", "zu_sql_update");
            $par_ok = false;
        }
    }

    if ($par_ok) {
        if (is_array($fields)) {
            $sql = 'UPDATE ' . $table;
            $sql_upd = '';
            foreach (array_keys($fields) as $i) {
                if ($sql_upd == '') {
                    $sql_upd .= ' SET ' . $fields[$i] . ' = ' . $db_con->sf($values[$i]) . ' ';
                } else {
                    $sql_upd .= ', SET ' . $fields[$i] . ' = ' . $db_con->sf($values[$i]) . ' ';
                }
            }
            $sql = $sql . $sql_upd . ' WHERE ' . $id_field . ' = ' . $db_con->sf($id) . ';';
        } else {
            $sql = 'UPDATE ' . $table . ' SET ' . $fields . ' = ' . $db_con->sf($values) . ' WHERE ' . $id_field . ' = ' . $db_con->sf($id) . ';';
        }
        $result = zu_sql_exe($sql, $user_id, sys_log_level::FATAL, "zu_sql_update", (new Exception)->getTraceAsString());
    }

    log_debug("zu_sql_update -> done (" . $result . ")");

    return $result;
}

// functions to review
// ---------------------


// returns all results of an SQL query 
function zu_sql_get_all($sql)
{
    global $db_con;
    global $debug;
    global $usr;

    if ($debug > 10) {
        log_debug('zu_sql_get_all (' . $sql . ')');
    } else {
        log_debug('zu_sql_get_all (' . substr($sql, 0, 100) . ' ... )');
    }

    try {
        $result = $db_con->exe($sql);
        //$result = zu_sql_exe($sql, $usr->id, sys_log_level::FATAL, "zu_sql_get_all", (new Exception)->getTraceAsString());
    } catch (Exception $e) {
        log_err('Cannot get all rows with "' . $sql . '" because: ' . $e->getMessage());
    }

    log_debug("zu_sql_get_all ... done");

    return $result;
}

// returns the first result of an SQL query 
// e.g. in zutl_dsp all aspects of one word link are retrieved with this function
function zu_sql_get($query)
{
    global $debug;
    if ($debug > 10) {
        log_debug("zu_sql_get (" . $query . ")");
    } else {
        log_debug("zu_sql_get (" . substr($query, 0, 100) . ")");
    }

    $sql_result = zu_sql_get_all($query);
    $result = mysqli_fetch_array($sql_result, MySQLi_NUM);

    log_debug("zu_sql_get ... done");

    return $result;
}

// returns the first result value of an SQL query 
function zu_sql_get1($query)
{
    global $debug;
    if ($debug > 10) {
        log_debug("zu_sql_get1 (" . $query . ")");
    } else {
        log_debug("zu_sql_get1 (" . substr($query, 0, 100) . ")");
    }

    $sql_array = zu_sql_get($query);
    $result = $sql_array[0];

    log_debug("zu_sql_get1 ... done");

    return $result;
}

// returns the value list of an SQL query, where the array key is the database id and the value is the name
// e.g. 6 is the array keys and Sales the value
function zu_sql_get_lst($sql)
{
    global $debug;
    if ($debug > 10) {
        log_debug("zu_sql_get_lst (" . $sql . ")");
    } else {
        log_debug("zu_sql_get_lst (" . substr($sql, 0, 100) . " ... )");
    }

    $result = array();
    if ($sql <> "") {
        $user_id = zuu_id();
        $sql_result = zu_sql_exe($sql, $user_id, sys_log_level::FATAL, "zu_sql_get_lst", (new Exception)->getTraceAsString());
        while ($value_entry = mysqli_fetch_array($sql_result, MySQLi_NUM)) {
            $result[$value_entry[0]] = $value_entry[1];
        }
    }

    log_debug("zu_sql_get_lst ... done");

    return $result;
}

// similar to zu_sql_get_lst, but returns an array of results with the name and the type
function zu_sql_get_lst_2fld($query)
{
    global $debug;
    if ($debug > 10) {
        log_debug("zu_sql_get_lst_2fld (" . $query . ")");
    } else {
        log_debug("zu_sql_get_lst_2fld (" . substr($query, 0, 100) . " ... )");
    }

    $result = array();
    if ($query <> "") {
        $sql_result = zu_sql_get_all($query);
        while ($value_entry = mysqli_fetch_array($sql_result, MySQLi_NUM)) {
            $row_result = array();
            $row_result[] = $value_entry[1];
            $row_result[] = $value_entry[2];
            $result[$value_entry[0]] = $row_result;
            //zu_debug("zu_sql_get_lst_2fld -> added ".$value_entry[1]." (type ".$value_entry[2].")");
        }
    }

    log_debug("zu_sql_get_lst_2fld ... done (" . zu_lst_dsp($result) . ")");

    return $result;
}


// returns first value of a simple SQL query 
function zu_sql_get_value($table_name, $field_name, $id_name, $id)
{
    global $db_con;

    log_debug("zu_sql_get_value(" . $table_name . "," . $field_name . "," . $id_name . "," . $id . ")");

    $result = '';
    $query = "SELECT " . $field_name . " FROM " . zu_sql_table_name($table_name) . " WHERE " . $id_name . " = '" . $id . "';";
    $sql_array = $db_con->get_old($query);
    //$sql_array = zu_sql_get($query);
    if ($sql_array != false) {
        $result = $sql_array[0];
    }

    log_debug("zu_sql_get_value -> (" . $result . ")");

    return $result;
}

// similar to zu_sql_get_value, but for two key fields
function zu_sql_get_value_2key($table_name, $field_name, $id1_name, $id1, $id2_name, $id2)
{
    log_debug("zu_sql_get_value_2key ... ");

    $result = '';
    $query = "SELECT " . $field_name . " FROM " . zu_sql_table_name($table_name) . " WHERE " . $id1_name . " = '" . $id1 . "' AND " . $id2_name . " = '" . $id2 . "';";
    $sql_array = zu_sql_get($query);
    $result = $sql_array[0];

    log_debug("zu_sql_get_value_2key ... done");

    return $result;
}


// returns the id field of a standard table
// standard table means that the table name ends with 's', the name field is the table name plus '_name' and prim index ends with '_id'
function zu_sql_get_id($type, $name)
{
    log_debug("zu_sql_get_id (" . $type . "," . $name . ")");

    $result = '';
    $table_name = zu_sql_std_table($type);
    $id_name = zu_sql_std_id_field($type);
    $field_name = zu_sql_std_name_field($type);
    $result = zu_sql_get_value($table_name, $id_name, $field_name, $name);

    log_debug("zu_sql_get_id ... done (" . $result . ")");

    return $result;
}

// similar to zu_sql_get_id, but using a second ID field
function zu_sql_get_id_2key($type, $name, $field2_name, $field2_value)
{
    log_debug("zu_sql_get_id_2key (" . $type . "," . $name . "," . $field2_name . "," . $field2_value . ")");

    $result = '';
    $table_name = zu_sql_std_table($type);
    $id_name = zu_sql_std_id_field($type);
    $field_name = zu_sql_std_name_field($type);
    $result = zu_sql_get_value_2key($table_name, $id_name, $field_name, $name, $field2_name, $field2_value);

    log_debug("zu_sql_get_id_2key ... done (" . $result . ")");

    return $result;
}

// simple form of zu_sql_get_id_2key to get a user specific value, because this is used many times
function zu_sql_get_id_usr($type, $name, $user_id)
{
    log_debug("zu_sql_get_id_usr (t" . $type . ",n" . $name . ",u" . $user_id . ")");
    return zu_sql_get_id_2key($type, $name, "user_id", $user_id);
}


// returns one field of a standard table
// standard table means that the table name ends with 's' and prim index ends with '_id'
function zu_sql_get_field($type, $id, $field_name)
{
    log_debug("zu_sql_get_field ... ");

    $result = '';
    $table_name = zu_sql_std_table($type);
    $id_name = zu_sql_std_id_field($type);
    $result = zu_sql_get_value($table_name, $field_name, $id_name, $id);

    log_debug("zu_sql_get_field ... done");

    return $result;
}

/*
// save a change in the log table
// must be called BEFORE the change is done in the database
function zu_sql_log_field($table_name, $row_id, $user_id, $field_name, $new_value)
{
    log_debug('zu_sql_log_field(' . $table_name . ',' . $row_id . ',' . $user_id . ',' . $field_name . ',' . $new_value . ')');

    $result = '';
    $table_id = zu_sql_get_value("change_tables", "table_id", "name", $table_name);
    if ($table_id <= 0) {
        $result .= mysqli_query("INSERT INTO change_tables (name) VALUES (" . $table_name . ")") or die('Query  failed: ' . mysqli_error());
        $table_id = zu_sql_get_value("change_tables", "table_id", "name", $table_name);
    }

    $user_id = 0;
    $old_value = zu_sql_get_value($table_name, $field_name, zu_sql_get_id_field($table_name), $row_id);
    if ($table_id > 0) {
        $result .= mysqli_query("INSERT INTO changes (table_id, row_id, user_id, field_name, old_value, new_value) VALUES (" . $table_id . ", " . $row_id . ", " . $user_id . ", " . $field_name . ", " . $old_value . ", " . $new_value . ")") or die('Query failed: ' . mysqli_error());
    }

    log_debug("zu_sql_log_field ... done");

    return $result;
}
*/

// table specific functions
// ------------------------

// add a value and link it to the words
// returns the id of the new value or the error code as a negative value
// $new_value is sql ready the string of the value to added
// $word_lst is an array of word ids 
function zu_sql_val_add($new_value, $word_lst)
{
    log_debug('zu_sql_val_add(' . $new_value . ',' . dsp_array($word_lst) . ')');
    $result = 0;

    // TODO: log the change
    $sql = "INSERT INTO `values` "
        . "            (word_value)  "
        . "     VALUES ('" . $new_value . "');";
    $ins_result = mysqli_query($sql);
    $val_id = mysqli_insert_id();
    if ($val_id > 0) {
        // to to: check if value was inserted correctly
        foreach ($word_lst as $word_id) {
            if ($word_id > 0) {
                $sql = "INSERT INTO value_phrase_links "
                    . "           (value_id   ,    phrase_id) "
                    . "    VALUES (" . $val_id . ", " . $word_id . ");";
                $ins_result = mysqli_query($sql);
            }
        }
    } else {
        $result = -1;
    }
    log_debug('zu_sql_tbl_value ... done (' . $ins_result . ')');

    return $result;
}

function zu_sql_user_id($user_name)
{
    $user_id = zu_sql_get_id('user', $user_name);
    return $user_id;
}

// get the user_id based on the ip address
function zu_sql_user_id_by_ip($ip_address)
{
    $user_id = zu_sql_get_value("users", "user_id", "ip_address", $ip_address);
    return $user_id;
}

// get the value for one table cell
function zu_sql_tbl_value_part($word_id, $row_word_id, $col_word_id, $part_word_id, $user_id)
{
    log_debug('zu_sql_tbl_value_part(' . $word_id . ',r' . $row_word_id . ',c' . $col_word_id . ',p' . $part_word_id . ',u' . $user_id . ')');

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
        . " LEFT JOIN `user_values` u ON v.`value_id` = u.`value_id` AND u.`user_id` = " . $user_id . " "
        . "     WHERE l1.`word_id` = " . $word_id . " "
        . "       AND l2.`word_id` = " . $row_word_id . " "
        . "       AND l3.`word_id` = " . $col_word_id . " "
        . "       AND l4.`word_id` = " . $part_word_id . " "
        . "       AND l1.`value_id` = v.`value_id` "
        . "       AND l2.`value_id` = v.`value_id` "
        . "       AND l3.`value_id` = v.`value_id` "
        . "       AND l4.`value_id` = v.`value_id` "
        . "       AND tc.`value_id` = v.`value_id` "
        . "       AND (u.`excluded` IS NULL OR u.`excluded` = 0) "
        . "  GROUP BY v.`value_id` "
        . "  ORDER BY tc.`words` ;";
    $result = zu_sql_get($sql);

    log_debug('zu_sql_tbl_value_part ... done (' . $result . ')');

    return $result;
}


// get a value that matches best all words in the list
// best match means the most special value is selected
// e.g if values for "ABB, Sales, Germany", " ABB, Sales, Suisse" and "ABB, Sales" are in the database
// and "ABB, Sales" is requested the value with the least words is returned, which would be "ABB, Sales" in this case
function zu_sql_wrd_ids_val($wrd_ids, $user_id)
{
    log_debug('zu_sql_wrd_ids_val(' . implode(",", $wrd_ids) . ',u' . $user_id . ')');

    $result = false;

    // build the sql statement based in the number of words
    $sql_pos = 0;
    $sql_from = '';
    $sql_where = '';
    foreach ($wrd_ids as $word_id) {
        if ($word_id > 0) {
            $sql_pos = $sql_pos + 1;
            $sql_from = $sql_from . " `value_phrase_links` l" . $sql_pos . ", ";
            if ($sql_pos == 1) {
                $sql_where = $sql_where . " WHERE l" . $sql_pos . ".`phrase_id` = " . $word_id . " AND l" . $sql_pos . ".`value_id` = v.`value_id` ";
            } else {
                $sql_where = $sql_where . "   AND l" . $sql_pos . ".`phrase_id` = " . $word_id . " AND l" . $sql_pos . ".`value_id` = v.`value_id` ";
            }
        }
    }
    if ($sql_where == '') {
        $sql_where = " WHERE ";
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
                  " . $sql_from . "
                  (SELECT l.value_id, 
                    count(l.phrase_id) as words 
                    FROM value_phrase_links l 
                GROUP BY l.value_id ) as tc 
                  " . $sql_where . "
                  tc.value_id = v.value_id 
          GROUP BY v.value_id 
          ORDER BY tc.words ;";
    //zu_debug($sql);
    $wrd_val = zudb_get1($sql, $user_id);
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
              WHERE u.value_id = " . $val_id . "
                AND u.user_id = " . $user_id . ";";
            $usr_val = zudb_get1($sql, $user_id);
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





// add extra words to row words if the extra word is a differentiator
function zu_sql_word_lst_add_differentiator($word_lst, $xtra_words)
{
    log_debug('zu_sql_word_lst_add_differentiator(' . $word_lst . ',' . $xtra_words . ')');

    $is_a_type = cl(db_cl::VERB, verb::IS_A);
    $differentiator_type = cl(db_cl::VERB, verb::CAN_CONTAIN);

    // add all words that are "is a" to the $differentiator list e.g. if the extra list contains Switzerland and Country is allowed as a differentiator Switzerland should be taken into account
    echo 'extra: ' . implode(",", $xtra_words) . '<br>';
    $added_words = zu_sql_word_lst_linked($xtra_words, $is_a_type, word_select_direction::DOWN);
    $added_words = zu_lst_not_in($added_words, $xtra_words);
    // while (!empty($added_words)) {
    if (!empty($added_words)) {
        echo 'added: ' . implode(",", $added_words) . '<br>';
        $xtra_words = zu_lst_merge_with_key($added_words, $xtra_words);
        echo 'combi: ' . implode(",", $xtra_words) . '<br>';
        $added_words = zu_sql_word_lst_linked($xtra_words, $is_a_type, word_select_direction::DOWN);
        $added_words = zu_lst_not_in($added_words, $xtra_words);
    }

    $differentiator_words = zu_sql_word_lst_linked($xtra_words, $differentiator_type, word_select_direction::DOWN);
    echo '+diff: ' . implode(",", $differentiator_words) . '<br>';

    $result = array();
    foreach (array_keys($word_lst) as $lst_entry) {
        // add the original entry
        $result[$lst_entry] = $word_lst[$lst_entry];
        // add the extra word if it is a differentiator
        //if (!in_array($lst_entry, array_keys($differentiator_words))) {
        //  $result[$lst_entry] = $in_lst[$lst_entry];
        //}
    }
    log_debug('zu_sql_word_lst_add_differentiator ... done (' . implode(",", $result) . ')');

    return $result;
}

// similar to zu_sql_value_ids_words, but for a value list
function zu_sql_value_lst_words($val_lst, $user_id)
{
    log_debug("zu_sql_value_lst_words(" . implode(",", $val_lst) . ",u" . $user_id . ")");

    $val_sql = trim(implode(",", array_keys($val_lst)));

    if ($val_sql <> '') {
        $query = "   SELECT l.phrase_id AS word_id, "
            . "          t.word_name, "
            . "          t.word_type_id "
            . "     FROM value_phrase_links l, "
            . "          words t "
            . "    WHERE l.value_id in (" . implode(",", array_keys($val_lst)) . ") "
            . "      AND l.phrase_id = t.word_id "
            . " GROUP BY l.phrase_id;";
        $result = zu_sql_get_lst_2fld($query);
    } else {
        $result = "";
    }

    log_debug("zu_sql_value_lst_words ... done (" . zu_lst_dsp($result) . ")");

    return $result;
}





/*
  word tree building
*/

// returns the words linked to a given word
function zu_sql_word_lst_linked($word_lst, $verb_id, $direction)
{
    log_debug('zu_sql_word_lst_linked(' . implode(",", $word_lst) . ',' . $verb_id . ',' . $direction . ')');

    $result = array();

    if (implode(",", array_keys($word_lst)) <> "") {
        if ($verb_id > 0) {
            $sql_link = " AND l.verb_id = " . $verb_id . " ";
        } else {
            $sql_link = " ";
        }

        if ($direction == word_select_direction::UP) {
            $sql_dir = " l.from_phrase_id = t.word_id AND l.to_phrase_id   IN (" . implode(",", array_keys($word_lst)) . ") ";
        } else {
            $sql_dir = " l.to_phrase_id   = t.word_id AND l.from_phrase_id IN (" . implode(",", array_keys($word_lst)) . ") ";
        }

        $sql = "SELECT t.word_id, t.word_name, t.word_type_id, l.word_link_id "
            . "  FROM word_links l, words t "
            . " WHERE " . $sql_dir
            . $sql_link
            . " ORDER BY t.word_name;";

        $result = zu_sql_get_lst($sql);
    }

    return $result;
}

