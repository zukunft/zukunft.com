<?php

/*

  mysql.php - the MySQL database link
  ---------
  
  the database link is reduced to a very few basic functions that exists on all databases
  this way an apache droid or hadoop adapter should also be possible
  
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

class mysql
{

    public $link = NULL;   // the link to the MySQL database
    public $usr_id = NULL;   // the user id of the person who request the database changes

    public $type = '';       // based of this database object type the table name and the standard fields are defined
    // e.g. for type "word" the field "word_name" is used
    private $table = '';      // name of the table that is used
    private $id_field = '';   // primary key field of the table used
    private $name_field = ''; // unique text key field of the table used

    /*
       open/close the connection to MySQL
    */

    // link to database
    function open($debug)
    {
        log_debug("db->open", $debug - 15);

        if (SQL_DB_TYPE == 'postgres') {
            try {
                $this->link = new PDO('pgsql:dbname=zukunft host=localhost', SQL_DB_USER, SQL_DB_PASSWD);
            } catch (PDOException $e) {
                $err_msg = $e->getMessage();
                log_fatal($err_msg, 'mysql->exe');
            }
        } else {
            $this->link = mysql_connect('localhost', SQL_DB_USER, SQL_DB_PASSWD) or die('Could not connect: ' . mysql_error());
            mysql_select_db('zukunft', $this->link) or die('Could not select database');
        }

        log_debug("mysql->open -> done", $debug - 10);
        return $this->link;
    }

    // just to have all sql in one library
    function close($debug)
    {
        if (SQL_DB_TYPE == 'postgres') {
            $this->link = null;
        } else {
            mysql_close($this->link);
        }

        log_debug("db->close -> done", $debug - 10);
    }

    /*
     * setup the environment
     */

    function sql_of_code_linked_db_rows()
    {
        if (SQL_DB_TYPE == 'postgres') {
            $result = file_get_contents('../database/postgres/zukunft_init_data.sql', true);
        } else {
            $result = file_get_contents('../database/mysql/zukunft_init_data.sql', true);
        }
        return $result;
    }

    /*

      for all tables some standard fields such as "word_name" are used
      the function below set the standard fields based on the "table/type"

    */

    // functions for the standard naming of tables
    function set_table($debug)
    {
        $result = $this->type . "s";
        // exceptions
        if ($result == 'view_entrys') {
            $result = 'view_entries';
        }
        if ($result == 'user_view_entrys') {
            $result = 'user_view_entries';
        }
        if ($result == 'sys_logs') {
            $result = 'sys_log';
        }
        if ($result == 'sys_log_statuss') {
            $result = 'sys_log_status';
        }
        // formats the table name for the MySQL statement
        if (SQL_DB_TYPE != 'postgres') {
            if (substr($result, 0, 1) != "`") {
                $result = "`" . $result . "`";
            }
        }
        log_debug("mysql->set_table to (" . $result . ")", $debug - 20);
        $this->table = $result;
    }

    function set_id_field($debug)
    {
        $type = $this->type;
        // exceptions for user overwrite tables
        if (zu_str_is_left($type, 'user_')) {
            $type = zu_str_right_of($type, 'user_');
        }
        $result = $type . '_id';
        // exceptions for nice english
        if ($type == 'view_entrie') {
            $result = 'view_entry_id';
        }
        if ($result == 'sys_log_statuss_id') {
            $result = 'sys_log_status_id';
        }
        log_debug("mysql->set_id_field to (" . $result . ")", $debug - 20);
        $this->id_field = $result;
    }

    private function set_name_field($debug)
    {
        $type = $this->type;
        // exceptions for user overwrite tables
        if (zu_str_is_left($type, 'user_')) {
            $type = zu_str_right_of($type, 'user_');
        }
        $result = $type . '_name';
        // exceptions to be adjusted
        if ($result == 'link_type_name') {
            $result = 'type_name';
        }
        if ($result == 'word_type_name') {
            $result = 'type_name';
        }
        if ($result == 'view_type_name') {
            $result = 'type_name';
        }
        if ($result == 'view_entry_type_name') {
            $result = 'type_name';
        }
        if ($result == 'sys_log_type_name') {
            $result = 'type_name';
        }
        if ($result == 'formula_type_name') {
            $result = 'name';
        }
        if ($result == 'sys_log_statuss_name') {
            $result = 'sys_log_status_name';
        }
        log_debug("mysql->set_name_field to (" . $result . ")", $debug - 20);
        $this->name_field = $result;
    }

    /*

      the main database call function including an automatic error tracking
      this function should probably be private and not be called from another class
      instead the function get, insert and update function below should be called

    */

    // add the writing of potential sql errors to the sys log table to the sql execution
    // includes the user to be able to ask the user for details how the error has been created
    // the log level is given by the calling function because after some errors the program may nevertheless continue
    function exe($sql, $log_level, $function_name, $function_trace = '', $debug = 0)
    {
        log_debug("mysql->exe (" . $sql . ",u" . $this->usr_id . ",ll:" . $log_level . ",fn:" . $function_name . ",ft:" . $function_trace . ")", $debug - 20);

        $result = '';

        // check and improve the given parameters
        if ($function_trace == '') {
            $function_trace = (new Exception)->getTraceAsString();
        }

        if (SQL_DB_TYPE == 'postgres') {
            $sql = str_replace("\n", "", $sql);
            try {
                if ($this->link == null) {
                    log_fatal('database connection lost', 'mysql->exe');
                } else {
                    $stmt = $this->link->prepare($sql);
                    try {
                        $result = $stmt->execute();
                    } catch (PDOException $e) {
                        $err_msg = $e->getMessage();
                        log_fatal($err_msg, 'mysql->exe');
                    }
                }
            } catch (PDOException $e) {
                $err_msg = $e->getMessage();
                log_fatal($err_msg, 'mysql->exe');
            }
        } else {
            $result = mysql_query($sql);
            if (!$result) {
                $msg_text = mysql_error();
                $sql = str_replace("'", "", $sql);
                $sql = str_replace("\"", "", $sql);
                $msg_text .= " (" . $sql . ")";
                $msg_type_id = cl($log_level);
                $result = log_msg($msg_text, $msg_text . ' from ' . $function_name, $msg_type_id, $function_name, $function_trace, $this->usr_id);
                log_debug("mysql->exe -> error (" . $result . ")", $debug - 10);
            }
        }

        return $result;
    }

    /*

      technical function to finally get data from the MySQL database

    */

    // returns all values of an SQL query in an array
    function get($sql, $debug)
    {
        $result = false;
        if ($debug > 20) {
            log_debug("mysql->get (" . $sql . ")", $debug - 20);
        } else {
            log_debug("mysql->get (" . substr($sql, 0, 100) . " ... )", $debug - 10);
        }

        if ($sql <> "") {
            if (SQL_DB_TYPE == 'postgres') {
                if ($this->link == null) {
                    log_err('Database connection lost', 'get1');
                } else {
                    $sql_result = $this->link->query($sql);
                    // todo fetch seems to be invalid if no record is returned
                    if ($sql_result != false) {
                        while ($sql_row = $sql_result->fetch(\PDO::FETCH_ASSOC)) {
                            $result[] = $sql_row;
                        }
                    }
                }
            } else {
                $sql_result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->get", (new Exception)->getTraceAsString(), $debug - 1);
                while ($sql_row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
                    $result[] = $sql_row;
                }
            }
        }

        log_debug("mysql->get -> done", $debug - 11);
        return $result;
    }

    // get only the first record from the database
    function get1($sql, $debug)
    {
        $result = false;
        if ($debug > 20) {
            log_debug("mysql->get1 (" . $sql . ")", $debug - 30);
        } else {
            log_debug("mysql->get1 (" . substr($sql, 0, 100) . " ... )", $debug - 20);
        }

        // optimise the sql statement
        $sql = trim($sql);
        if (strpos($sql, "LIMIT") === FALSE) {
            if (substr($sql, -1) == ";") {
                $sql = substr($sql, 0, -1) . " LIMIT 1;";
            }
        }

        if ($sql <> "") {
            if (SQL_DB_TYPE == 'postgres') {
                if ($this->link == null) {
                    log_err('Database connection lost', 'get1');
                } else {
                    $sql_result = $this->link->query($sql);
                    // todo fetch seems to be invalid if no record is returned
                    if ($sql_result != false) {
                        $result = $sql_result->fetch(\PDO::FETCH_ASSOC);
                    }
                }
            } else {
                $sql_result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->get1", (new Exception)->getTraceAsString(), $debug - 1);
                $result = mysql_fetch_array($sql_result, MYSQL_ASSOC);
            }
        }

        log_debug("mysql->get1 -> done", $debug - 20);
        return $result;
    }

// returns first value of a simple SQL query
    function get_value($field_name, $id_name, $id, $debug)
    {
        $result = '';
        log_debug('mysql->get_value ' . $field_name . ' from ' . $this->type . ' where ' . $id_name . ' = ' . sf($id), $debug - 20);

        if ($this->type <> '') {
            $this->set_table($debug - 1);

            // set fallback values
            if ($field_name == '') {
                $this->set_name_field($debug - 1);
                $field_name = $this->name_field;
            }
            if ($id_name == '') {
                $this->set_id_field($debug - 1);
                $id_name = $this->id_field;
            }

            $sql = "SELECT " . $field_name . " FROM " . $this->table . " WHERE " . $id_name . " = " . sf($id) . " LIMIT 1;";

            if (SQL_DB_TYPE == 'postgres') {
                if ($this->link == null) {
                    log_err('Database connection lost', 'get1');
                } else {
                    $sql_result = $this->link->query($sql);
                    // todo fetch seems to be invalid if no record is returned
                    if ($sql_result != false) {
                        $sql_row = $sql_result->fetch(\PDO::FETCH_ASSOC);
                        $result = array_values($sql_row)[0];
                    }
                }
            } else {
                $sql_result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->get_value", (new Exception)->getTraceAsString(), $debug - 1);
                $sql_row = mysql_fetch_array($sql_result, MYSQL_NUM);
                // todo check if mysql really fill starting with 0
                $result = $sql_row[0];
            }
        } else {
            log_err("Type not set to get " . $id . " " . $id_name . ".", "mysql->get_value", (new Exception)->getTraceAsString());
        }

        return $result;
    }

// similar to mysql->get_value, but for two key fields
    function get_value_2key($field_name, $id1_name, $id1, $id2_name, $id2, $debug)
    {
        $result = '';
        log_debug('mysql->get_value_2key ' . $field_name . ' from ' . $this->type . ' where ' . $id1_name . ' = ' . $id1 . ' and ' . $id2_name . ' = ' . $id2, $debug - 20);

        $this->set_table($debug - 1);
        $sql = "SELECT " . $field_name . " FROM " . $this->table . " WHERE " . $id1_name . " = '" . $id1 . "' AND " . $id2_name . " = '" . $id2 . "' LIMIT 1;";

        if (SQL_DB_TYPE == 'postgres') {
            if ($this->link == null) {
                log_err('Database connection lost', 'get1');
            } else {
                $sql_result = $this->link->query($sql);
                // todo fetch seems to be invalid if no record is returned
                if ($sql_result != false) {
                    $sql_row = $sql_result->fetch(\PDO::FETCH_ASSOC);
                    $result .= array_values($sql_row)[0];
                }
            }
        } else {
            $sql_result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->get_value_2key", (new Exception)->getTraceAsString(), $debug - 1);
            $sql_row = mysql_fetch_array($sql_result, MYSQL_NUM);
            // todo check if mysql really fill starting with 0
            $result .= $sql_row[0];
        }

        return $result;
    }

// returns the id field of a standard table
// standard table means that the table name ends with 's', the name field is the table name plus '_name' and prim index ends with '_id'
// $name is the unique text that indentifies one row e.g. for the $name "Company" the word id "1" is returned
    function get_id($name, $debug = 0)
    {
        $result = '';
        log_debug('mysql->get_id for "' . $name . '" of the db object "' . $this->type . '"', $debug - 12);

        $this->set_table($debug - 1);
        $this->set_id_field($debug - 1);
        $this->set_name_field($debug - 1);
        $result .= $this->get_value($this->id_field, $this->name_field, $name, $debug - 1);

        log_debug('mysql->get_id is "' . $result . '"', $debug - 15);
        return $result;
    }

    function get_id_from_code($code_id, $debug)
    {
        $result = '';
        log_debug('mysql->get_id_from_code for "' . $code_id . '" of the db object "' . $this->type . '"', $debug - 12);

        $this->set_table($debug - 1);
        $this->set_id_field($debug - 1);
        $result .= $this->get_value($this->id_field, DBL_FIELD, $code_id, $debug - 1);

        log_debug('mysql->get_id_from_code is "' . $result . '"', $debug - 15);
        return $result;
    }

// similar to get_id, but the other way round
    function get_name($id, $debug)
    {
        $result = '';
        log_debug('mysql->get_name for "' . $id . '" of the db object "' . $this->type . '"', $debug - 12);

        $this->set_table($debug - 1);
        $this->set_id_field($debug - 1);
        $this->set_name_field($debug - 1);
        $result = $this->get_value($this->name_field, $this->id_field, $id, $debug - 1);

        log_debug('mysql->get_name is "' . $result . '"', $debug - 15);
        return $result;
    }

// similar to zu_sql_get_id, but using a second ID field
    function get_id_2key($name, $field2_name, $field2_value, $debug)
    {
        $result = '';
        log_debug('mysql->get_id_2key for "' . $name . ',' . $field2_name . ',' . $field2_value . '" of the db object "' . $this->type . '"', $debug - 12);

        $this->set_table($debug - 1);
        $this->set_id_field($debug - 1);
        $this->set_name_field($debug - 1);
        $result = $this->get_value_2key($this->id_field, $this->name_field, $name, $field2_name, $field2_value, $debug - 1);

        log_debug('mysql->get_id_2key is "' . $result . '"', $debug - 15);
        return $result;
    }

// create a standard query for a list of database id and name while taking the user sandbox into account
    function sql_std_lst_usr($debug)
    {
        log_debug("mysql->sql_std_lst_usr (" . $this->type . ")", $debug);

        $this->set_table($debug - 1);
        $this->set_id_field($debug - 1);
        $this->set_name_field($debug - 1);
        /* this query looks easier than the one below, but it does not word for user exclusions
        $sql = "SELECT t.".$this->id_field." AS id,
                       IF(u.".$this->name_field." IS NULL, t.".$this->name_field.", u.".$this->name_field.") AS name
                  FROM ".$this->table." t
             LEFT JOIN user_".str_replace("`","",$this->table)." u ON u.".$this->id_field." = t.".$this->id_field."
                                         AND u.user_id = ".$this->usr_id."
                 WHERE (u.excluded IS NULL AND (t.excluded IS NULL OR t.excluded = 0)) OR u.excluded = 0
              ORDER BY t.".$this->name_field.";";
        */
        $sql_where = '';
        if ($this->type == 'view') {
            $sql_where = ' WHERE t.code_id IS NULL ';
        }
        $sql = "SELECT id, name 
              FROM ( SELECT t." . $this->id_field . " AS id, 
                            IF(u." . $this->name_field . " IS NULL, t." . $this->name_field . ", u." . $this->name_field . ") AS name,
                            IF(u.excluded IS NULL,     COALESCE(t.excluded, 0), COALESCE(u.excluded, 0))          AS excluded
                      FROM " . $this->table . " t       
                  LEFT JOIN user_" . str_replace("`", "", $this->table) . " u ON u." . $this->id_field . " = t." . $this->id_field . " 
                                              AND u.user_id = " . $this->usr_id . " 
                            " . $sql_where . ") AS s
            WHERE excluded <> 1                                   
          ORDER BY name;";
        return $sql;
    }

// create a standard query for a list of database id and name
    function sql_std_lst($debug)
    {
        log_debug("mysql->sql_std_lst (" . $this->type . ")", $debug);

        $this->set_table($debug - 1);
        $this->set_id_field($debug - 1);
        $this->set_name_field($debug - 1);
        $sql = "SELECT " . $this->id_field . " AS id,
                   " . $this->name_field . " AS name
              FROM " . $this->table . "
          ORDER BY " . $this->name_field . ";";

        return $sql;
    }

// return all database ids, where the owner is not yet set
    function missing_owner($debug)
    {
        log_debug("mysql->missing_owner (" . $this->type . ")", $debug);
        $result = Null;

        $this->set_table($debug - 1);
        $this->set_id_field($debug - 1);
        $sql = "SELECT " . $this->id_field . " AS id
              FROM " . $this->table . "
             WHERE user_id IS NULL;";

        $result = $this->get($sql, $debug - 5);
        return $result;
    }

// return all database ids, where the owner is not yet set
    function set_default_owner($debug)
    {
        log_debug("mysql->set_default_owner (" . $this->type . ")", $debug);
        $result = Null;

        $this->set_table($debug - 1);
        $sql = "UPDATE " . $this->table . "
               SET user_id = 1
             WHERE user_id IS NULL;";

        $result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->set_default_owner", (new Exception)->getTraceAsString(), $debug - 1);
        return $result;
    }

    /*

      technical function to finally update data in the MySQL database

    */

// insert a new record in the database
// similar to exe, but returning the row id added to be able to update e.g. the log entry with the row id of the real row added
// writing the changes to the log table for history rollback is done at the calling function also because zu_log also uses this function
    function insert($fields, $values, $debug = 0)
    {
        $sql = '';
        $this->set_table($debug - 1);

        if (is_array($fields)) {
            log_debug('mysql->insert into "' . $this->type . '" SET "' . implode('","', $fields) . '" WITH "' . implode('","', $values) . '" for user ' . $this->usr_id, $debug - 10);
            if (count($fields) <> count($values)) {
                log_fatal('MySQL insert call with different number of fields (' . count($fields) . ': ' . implode(',', $fields) . ') and values (' . count($values) . ': ' . implode(',', $values) . ').', "user_log->add");
            } else {
                foreach (array_keys($fields) as $i) {
                    $fields[$i] = $fields[$i];
                    $values[$i] = sf($values[$i]);
                }
                $sql = 'INSERT INTO ' . $this->table . ' (' . implode(',', $fields) . ') 
                                      VALUES (' . implode(',', $values) . ');';
            }
        } else {
            log_debug('mysql->insert into "' . $this->type . '" SET "' . $fields . '" WITH "' . $values . '" for user ' . $this->usr_id, $debug - 10);
            $sql = 'INSERT INTO ' . $this->table . ' (' . $fields . ') 
                                 VALUES (' . sf($values) . ');';
        }

        if ($sql <> '') {
            if (SQL_DB_TYPE == 'postgres') {
                if ($this->link == null) {
                    log_err('Database connection lost', 'insert');
                } else {

                    try {
                        $stmt = $this->link->prepare($sql);
                        $this->link->beginTransaction();
                        $stmt->execute();
                        $this->link->commit();
                        $result = $this->link->lastInsertId();
                        log_debug('mysql->insert -> done "' . $result . '"', $debug - 12);
                    } catch (PDOExecption $e) {
                        $this->link->rollback();
                        log_debug('mysql->insert -> failed (' . $sql . ')', $debug - 12);
                    }
                }

            } else {
                $sql_result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->insert", (new Exception)->getTraceAsString(), $debug - 1);
                if ($sql_result) {
                    $result = mysql_insert_id();
                    log_debug('mysql->insert -> done "' . $result . '"', $debug - 12);
                } else {
                    $result = -1;
                    log_debug('mysql->insert -> failed (' . $sql . ')', $debug - 12);
                }
            }
        } else {
            $result = -1;
            log_debug('mysql->insert -> failed (' . $sql . ')', $debug - 12);
        }

        return $result;
    }


// add a new unique text to the database and return the id (similar to get_id)
    function add_id($name, $debug = 0)
    {
        log_debug('mysql->add_id ' . $name . ' to ' . $this->type, $debug - 10);

        $this->set_table($debug - 1);
        $this->set_name_field($debug - 1);
        $result = $this->insert($this->name_field, sf($name), $debug - 1);

        log_debug('mysql->add_id is "' . $result . '"', $debug - 12);
        return $result;
    }

// similar to zu_sql_add_id, but using a second ID field
    function add_id_2key($name, $field2_name, $field2_value, $debug)
    {
        log_debug('mysql->add_id_2key ' . $name . ',' . $field2_name . ',' . $field2_value . ' to ' . $this->type, $debug - 10);

        $this->set_table($debug - 1);
        $this->set_name_field($debug - 1);
        //zu_debug('mysql->add_id_2key add "'.$this->name_field.','.$field2_name.'" "'.$name.','.$field2_value.'"', $debug-12);
        $result = $this->insert(array($this->name_field, $field2_name), array($name, $field2_value), $debug - 1);

        log_debug('mysql->add_id_2key is "' . $result . '"', $debug - 12);
        return $result;
    }

// update some values in a table
    function update($id, $fields, $values, $debug)
    {
        log_debug('mysql->update of ' . $this->type . ' row ' . $id . ' ' . $fields . ' with "' . $values . '" for user ' . $this->usr_id, $debug - 10);

        $result = '';

        // check parameter
        $par_ok = true;
        $this->set_table($debug - 1);
        $this->set_id_field($debug - 1);
        if ($debug > 0) {
            if ($this->table == "") {
                log_err("Table not valid for " . $fields . " at " . $id . ".", "zu_sql_update", (new Exception)->getTraceAsString());
                $par_ok = false;
            }
            if ($values === "") {
                log_err("Values missing for " . $fields . " in " . $this->table . ".", "zu_sql_update", (new Exception)->getTraceAsString());
                $par_ok = false;
            }
        }

        // set the where clause user sandbox? ('.substr($this->type,0,4).')', $debug-16);
        $sql_where = ' WHERE ' . $this->id_field . ' = ' . sf($id);
        if (substr($this->type, 0, 4) == 'user') {
            // ... but not for the user table itself
            if ($this->type <> 'user') {
                $sql_where .= ' AND user_id = ' . $this->usr_id;
            }
        }

        if ($par_ok) {
            $sql_upd = 'UPDATE ' . $this->table;
            $sql_set = '';
            if (is_array($fields)) {
                foreach (array_keys($fields) as $i) {
                    if ($sql_set == '') {
                        $sql_set .= ' SET ' . $fields[$i] . ' = ' . sf($values[$i]);
                    } else {
                        $sql_set .= ', ' . $fields[$i] . ' = ' . sf($values[$i]);
                    }
                }
            } else {
                $sql_set .= ' SET ' . $fields . ' = ' . sf($values);
            }
            $sql = $sql_upd . $sql_set . $sql_where . ';';
            log_debug('mysql->update sql "' . $sql . '"', $debug - 14);
            $result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->update", (new Exception)->getTraceAsString(), $debug - 1);
        }

        log_debug('mysql->update -> done (' . $result . ')', $debug - 12);
        return $result;
    }

    function update_name($id, $name, $debug)
    {
        $this->set_name_field($debug - 1);
        $result = $this->update($id, $this->name_field, $name, $debug - 1);
        return $result;
    }

// call the MySQL delete action
    function delete($id_fields, $id_values, $debug)
    {
        log_debug('mysql->delete in "' . $this->type . '" WHERE "' . implode(",", $id_fields) . '" IS "' . implode(",", $id_values) . '" for user ' . $this->usr_id, $debug - 10);

        $this->set_table($debug - 1);

        if (is_array($id_fields)) {
            $sql = 'DELETE FROM ' . $this->table;
            $sql_del = '';
            foreach (array_keys($id_fields) as $i) {
                $del_val = $id_values[$i];
                if (is_array($del_val)) {
                    $del_val_txt = ' IN (' . sf(implode(",", $del_val)) . ') ';
                } else {
                    $del_val_txt = ' = ' . sf($del_val) . ' ';
                }
                if ($sql_del == '') {
                    $sql_del .= ' WHERE ' . $id_fields[$i] . $del_val_txt;
                } else {
                    $sql_del .= ' AND ' . $id_fields[$i] . $del_val_txt;
                }
            }
            $sql = $sql . $sql_del . ';';
        } else {
            $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $id_fields . ' = ' . sf($id_values) . ';';
        }

        log_debug('mysql->delete sql "' . $sql . '"', $debug - 14);
        $sql_result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->delete", (new Exception)->getTraceAsString(), $debug - 1);
        if ($sql_result) {
            $result = $sql_result;
            log_debug('mysql->delete -> done "' . $result . '"', $debug - 12);
        } else {
            $result = -1;
            log_debug('mysql->delete -> failed (' . $sql . ')', $debug - 12);
        }

        return $result;
    }

    /*

      list functions to finally get data from the MySQL database

    */

// load all types of a type/table at once
    function load_types($table, $additional_field_lst, $debug)
    {
        log_debug('mysql->load_types', $debug - 10);

        $additional_fields = '';
        if (count($additional_field_lst) > 0) {
            foreach ($additional_field_lst as $additional_field) {
                $additional_fields .= ', ';
                $additional_fields .= $additional_field;
            }
        }

        $sql = 'SELECT ' . $table . '_id,
                   ' . $table . '_name,
                   code_id,
                   description
                   ' . $additional_fields . '
              FROM ' . $table . 's 
          ORDER BY ' . $table . '_id;';
        $result = $this->get($sql, $debug - 1);

        log_debug('mysql->load_types -> got ' . count($result), $debug - 10);
        return $result;
    }

}

// formats one value for the sql statement
function postgres_format($field_value, $debug = 0)
{
    log_debug("mysql_format (" . $field_value . ")", $debug - 1);

// remove any previous formattings (if all code is fine, this may not be needed any more)
    $result = $field_value;
    if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
        $result = substr($result, 1, -1);
    }

// format the "real" value for sql
//$result = mysql_real_escape_string($result);

// add the formatting for the sql statement
    if (trim($result) == "") {
        $result = "NULL";
    } else {
        if (is_numeric($result)) {
            $result = $result;
        } else {
            // undo the double highqote escape char, because this is not needed if the string is capsuled by single highqote
            $result = str_replace('\"', '"', $result);
            $result = "'" . $result . "'";
        }
    }
    log_debug("postgres_format -> done (" . $result . ")", $debug - 1);

    return $result;
}

// formats one value for the sql statement
function mysql_format($field_value, $debug = 0)
{
    log_debug("mysql_format (" . $field_value . ")", $debug - 1);

    // remove any previous formattings (if all code is fine, this may not be needed any more)
    $result = $field_value;
    if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
        $result = substr($result, 1, -1);
    }

    // format the "real" value for sql
    $result = mysql_real_escape_string($result);

    // add the formatting for the sql statement
    if (trim($result) == "") {
        $result = "NULL";
    } else {
        if (is_numeric($result)) {
            $result = $result;
        } else {
            // undo the double highqote escape char, because this is not needed if the string is capsuled by single highqote
            $result = str_replace('\"', '"', $result);
            $result = "'" . $result . "'";
        }
    }

    // exceptions
    if ($result == "'Now()'") {
        $result = "Now()";
    }

    log_debug("mysql_format -> done (" . $result . ")", $debug - 1);

    return $result;
}

/*

  name shortcuts - rename some often used functions to make to code look nicer and not draw the focus away from the important part
  --------------
  
*/

// Sql Format: format a string for the MySQL database
// shortcut for mysql_format
// outside this module it should only be used to format queries that are not yet using the abstract form for all databases (MySQL, MariaSQL, Casandra, Droid)
function sf($field_value)
{
    $result = '';
    if (SQL_DB_TYPE == 'postgres') {
        $result = postgres_format($field_value);
    } else {
        $result = mysql_format($field_value);
    }
    return $result;
}

// SQL list: create a query string for the standard list
// e.g. the type "source" creates the SQL statement "SELECT source_id, source_name FROM sources ORDER BY source_name;"
function sql_lst($type, $debug)
{
    $db_con = new mysql;
    $db_con->type = $type;
    $sql = $db_con->sql_std_lst($debug - 1);
    return $sql;
}

// similar to "sql_lst", but taking the user sandbox into account
function sql_lst_usr($type, $usr, $debug)
{
    $db_con = new mysql;
    $db_con->usr_id = $usr->id;
    $db_con->type = $type;
    $sql = $db_con->sql_std_lst_usr($debug - 1);
    return $sql;
}

/* samples usage of sql_lst and sql_lst_usr
sql_lst("view_type", $debug-1); // ex sql_view_types($this->usr, $debug-1);
sql_lst("view_entry_type", $debug-1); 
sql_lst_usr("word", $this->usr, $debug-1);
sql_lst_usr("view", $this->usr, $debug-1);
*/

?>