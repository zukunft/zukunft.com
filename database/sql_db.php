<?php

/*

  sql_db.php - the SQL database link and abstraction layer
  ----------
  
  the database link is reduced to a very few basic functions that exists on all databases
  this way an apache droid or hadoop adapter should also be possible
  at the moment adapter to MySQL and PostgreSQL are working
  
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

const DB_TYPE_POSTGRES = "PostgreSQL";
const DB_TYPE_MYSQL = "MySQL";


class sql_db
{

    // based on https://www.postgresql.org/docs/current/sql-keywords-appendix.html from 2021-06-13
    const POSTGRES_RESERVED_NAMES = ['AND ', 'ANY ', 'ARRAY ', 'AS ', 'ASC ', 'ASYMMETRIC ', 'BOTH ', 'CASE ', 'CAST ', 'CHECK ', 'COLLATE ', 'COLUMN ', 'CONSTRAINT ', 'CREATE ', 'CURRENT_CATALOG ', 'CURRENT_DATE ', 'CURRENT_ROLE ', 'CURRENT_TIME ', 'CURRENT_TIMESTAMP ', 'CURRENT_USER ', 'DEFAULT ', 'DEFERRABLE ', 'DESC ', 'DISTINCT ', 'DO ', 'ELSE ', 'END ', 'EXCEPT ', 'FALSE ', 'FETCH ', 'FOR ', 'FOREIGN ', 'FROM ', 'GRANT ', 'GROUP ', 'HAVING ', 'IN ', 'INITIALLY ', 'INTERSECT ', 'INTO ', 'LATERAL ', 'LEADING ', 'LIMIT ', 'LOCALTIME ', 'LOCALTIMESTAMP ', 'NOT ', 'NULL ', 'OFFSET ', 'ON ', 'ONLY ', 'OR ', 'ORDER ', 'PLACING ', 'PRIMARY ', 'REFERENCES ', 'RETURNING ', 'SELECT ', 'SESSION_USER ', 'SOME ', 'SYMMETRIC ', 'TABLE ', 'THEN ', 'TO ', 'TRAILING ', 'TRUE ', 'UNION ', 'UNIQUE ', 'USER ', 'USING ', 'VARIADIC ', 'WHEN ', 'WHERE ', 'WINDOW ', 'WITH ',];
    // extra names for backward compatibility
    const POSTGRES_RESERVED_NAMES_EXTRA = ['USER'];

    // Based on MySQL version 8
    const MYSQL_RESERVED_NAMES = ['ACCESSIBLE', 'ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE', 'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BY', 'CALL', 'CASCADE', 'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION', 'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE', 'CROSS', 'CUME_DIST', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'DATABASE', 'DATABASES', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DELAYED', 'DELETE', 'DENSE_RANK', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW', 'DIV', 'DOUBLE', 'DROP', 'DUAL', 'EACH', 'ELSE', 'ELSEIF', 'EMPTY', 'ENCLOSED', 'ESCAPED', 'EXCEPT', 'EXCEPT', 'EXISTS', 'EXIT', 'EXPLAIN', 'FALSE', 'FETCH', 'FIRST_VALUE', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR', 'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 'GENERATED', 'GET', 'GRANT', 'GROUP', 'GROUPING', 'GROUPS', 'HAVING', 'HIGH_PRIORITY', 'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IF', 'IGNORE', 'IN', 'INDEX', 'INFILE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INT', 'INT1', 'INT2', 'INT3', 'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'IO_AFTER_GTIDS', 'IO_BEFORE_GTIDS', 'IS', 'ITERATE', 'JOIN', 'JSON_TABLE', 'KEY', 'KEYS', 'KILL', 'LAG', 'LAST_VALUE', 'LATERAL', 'LEAD', 'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY', 'MASTER_BIND', 'MASTER_SSL_VERIFY_SERVER_CERT', 'MATCH', 'MAXVALUE', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL', 'NO_WRITE_TO_BINLOG', 'NOT', 'NTH_VALUE', 'NTILE', 'NULL', 'NUMERIC', 'OF', 'ON', 'OPTIMIZE', 'OPTIMIZER_COSTS', 'OPTION', 'OPTIONALLY', 'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'OVER', 'PARTITION', 'PERCENT_RANK', 'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE', 'RANGE', 'RANK', 'READ', 'READ_WRITE', 'READS', 'REAL', 'RECURSIVE', 'REFERENCES', 'REGEXP', 'RELEASE', 'RENAME', 'REPEAT', 'REPLACE', 'REQUIRE', 'RESIGNAL', 'RESTRICT', 'RETURN', 'REVOKE', 'RIGHT', 'RLIKE', 'ROW_NUMBER', 'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE', 'SEPARATOR', 'SET', 'SHOW', 'SIGNAL', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SSL', 'STARTING', 'STORED', 'STRAIGHT_JOIN', 'SYSTEM', 'TABLE', 'TERMINATED', 'THEN', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE', 'UNDO', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING', 'VIRTUAL', 'WHEN', 'WHERE', 'WHILE', 'WINDOW', 'WITH', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL'];
    // extra names for backward compatibility
    const MYSQL_RESERVED_NAMES_EXTRA = ['VALUE', 'VALUES', 'URL'];

    // tables that does not have a name
    const DB_TYPES_NOT_NAMED = [DB_TYPE_VALUE, DB_TYPE_FORMULA_LINK, DB_TYPE_VIEW_COMPONENT_LINK];
    // tables that link two named tables
    const DB_TYPES_LINK = [DB_TYPE_WORD_LINK, DB_TYPE_FORMULA_LINK, DB_TYPE_VIEW_COMPONENT_LINK];

    const USER_PREFIX = "user_";          // prefix used for tables where the user sandbox values are stored

    const STD_TBL = "s";                  // prefix used for the standard table where data for all users are stored
    const USR_TBL = "u";                  // prefix used for the standard table where the user sandbox data is stored
    const LNK_TBL = "l";                  // prefix used for the table which should be joined in the result
    const ULK_TBL = "c";                  // prefix used for the table which should be joined in the result of the user sandbox data

    const FLD_CODE_ID = "code_id";        // field name for the code link
    const FLD_USER_ID = "user_id";        // field name for the user table foreign key field

    const FLD_FORMAT_TEXT = "text";       // to force the text formatting of an value

    public $db_type = '';                 // the database type which should be used for this connection e.g. postgreSQL or MYSQL
    public $link = NULL;                  // the link object to the database
    public $usr_id = NULL;                // the user id of the person who request the database changes
    private $usr_view_id = NULL;          // the user id of the person which values should be returned e.g. an admin might want to check the data of an user

    private $type = '';                   // based of this database object type the table name and the standard fields are defined e.g. for type "word" the field "word_name" is used
    private $table = '';                  // name of the table that is used for the next query
    private $id_field = '';               // primary key field of the table used
    private $id_from_field = '';          // only for link objects the id field of the source object
    private $id_to_field = '';            // only for link objects the id field of the destination object
    private $id_link_field = '';          // only for link objects the id field of the link type object
    private $name_field = '';             // unique text key field of the table used
    private $field_lst = [];              // list of fields that should be returned in the next select query
    private $usr_field_lst = [];          // list of user specific fields that should be returned in the next select query
    private $usr_num_field_lst = [];      // list of user specific numeric fields that should be returned in the next select query
    private $usr_only_field_lst = [];     // list of fields that are only in the user sandbox
    private $join_field_lst = [];         // list of fields that should be returned in the next select query that are taken from a joined table
    private $join_usr_field_lst = [];     // list of fields that should be returned in the next select query that are taken from a joined table
    private $join_usr_num_field_lst = []; // list of fields that should be returned in the next select query that are taken from a joined table
    private $join_type = '';              // the type name of the table to join
    private $usr_query = false;           // true, if the query is expected to retrieve user specific data

    private $fields = '';                 // the fields               SQL statement that is used for the next select query
    private $from = '';                   // the from                 SQL statement that is used for the next select query
    private $join = '';                   // the join                 SQL statement that is used for the next select query
    private $where = '';                  // the where condition as a SQL statement that is used for the next select query

    /*
     * basic interface function for the private class parameter
     */

    // define the table that should be used for the next select, insert, update or delete statement
    // resets all previous db query settings such as fields, user_fields, so this should be the first statement when defining a database query
    // TODO check that this is always called directly before the query is created, so that
    function set_type($type)
    {
        global $usr;

        $this->reset();
        $this->type = $type;
        if ($usr == null) {
            $this->set_usr(SYSTEM_USER_ID); // if the session user is not yet set, use the system user id to test the database compatibility
        } else {
            $this->set_usr($usr->id); // by default use the session user id
        }
        $this->set_id_field();
        $this->set_name_field();
    }

    function get_type()
    {
        return $this->type;
    }

    // set the user id of the user who has requested the database access
    // by default the user also should see his/her/its data
    function set_usr($usr_id)
    {
        $this->usr_id = $usr_id;
        $this->usr_view_id = $usr_id;
    }

    // to change the user view independent from the session user (can only be called by admin users)
    function set_view_usr($usr_id)
    {
        $this->usr_view_id = $usr_id;
    }

    // define the fields that should be returned in an select query
    function set_fields($field_lst)
    {
        $this->field_lst = $field_lst;
    }

    // define the fields that are used to link two objects
    // the id_link_field is the type e.g. the verb for a word link
    function set_link_fields($id_from_field, $id_to_field, $id_link_field = '')
    {
        $this->id_from_field = $id_from_field;
        $this->id_to_field = $id_to_field;
        $this->id_link_field = $id_link_field;
    }

    // add a list of fields to the result that are taken from another table
    // $field_lst are the field names that should be included in the result
    // $join_table is the table from where the fields should be taken; use the type name, not the table name
    // $join_field is the index field that should be used for the join that must exist in both tables, default is the id of the joined table
    function set_join_fields($join_field_lst, $join_type, $join_field = '')
    {
        $this->join_field_lst = $join_field_lst;
        $this->join_type = $join_type;
    }

    // similar to set_join_fields but for usr specific fields
    function set_join_usr_fields($join_field_lst, $join_type, $join_field = '')
    {
        $this->join_usr_field_lst = $join_field_lst;
        $this->join_type = $join_type;
    }

    function set_join_usr_num_fields($join_field_lst, $join_type, $join_field = '')
    {
        $this->join_usr_num_field_lst = $join_field_lst;
        $this->join_type = $join_type;
    }

    // set the SQL statement for the user sandbox fields that should be returned in an select query which can be user specific
    function set_usr_fields($usr_field_lst)
    {
        $this->usr_query = true;
        $this->usr_field_lst = $usr_field_lst;
    }

    function set_usr_num_fields($usr_field_lst)
    {
        $this->usr_query = true;
        $this->usr_num_field_lst = $usr_field_lst;
    }

    function set_usr_only_fields($field_lst)
    {
        $this->usr_query = true;
        $this->usr_only_field_lst = $field_lst;
    }

    private function set_field_sep()
    {
        if ($this->fields != '') {
            $this->fields .= ', ';
        }
    }

    private function set_field_usr_text($field, $stb_tbl = sql_db::STD_TBL, $usr_tbl = sql_db::USR_TBL)
    {
        if ($this->db_type == DB_TYPE_POSTGRES) {
            $this->fields .= " CASE WHEN (" . $usr_tbl . "." . $field . " <> '' IS NOT TRUE) THEN " . $stb_tbl . "." . $field . " ELSE " . $usr_tbl . "." . $field . " END AS " . $field;
        } else {
            $this->fields .= '         IF(' . $usr_tbl . '.' . $field . ' IS NULL, ' . $stb_tbl . '.' . $field . ', ' . $usr_tbl . '.' . $field . ')    AS ' . $field;
        }
    }

    private function set_field_usr_num($field)
    {
        if ($this->db_type == DB_TYPE_POSTGRES) {
            $this->fields .= " CASE WHEN (" . sql_db::USR_TBL . "." . $field . " IS NULL) THEN " . sql_db::STD_TBL . "." . $field . " ELSE " . sql_db::USR_TBL . "." . $field . " END AS " . $field;
        } else {
            $this->fields .= '         IF(' . sql_db::USR_TBL . '.' . $field . ' IS NULL, ' . sql_db::STD_TBL . '.' . $field . ', ' . sql_db::USR_TBL . '.' . $field . ')    AS ' . $field;
        }
    }

    private function set_field_statement($has_id)
    {
        if ($has_id) {
            // add the fields that part of all standard tables so id and name on top of the field list
            $field_lst = [];
            $usr_field_lst = [];
            $field_lst[] = $this->id_field;
            if ($this->usr_query) {
                // user can change the name of an object, that's why the target field list is either $usr_field_lst or $field_lst
                if (!in_array($this->type, sql_db::DB_TYPES_NOT_NAMED)) {
                    $usr_field_lst[] = $this->name_field;
                }
                $field_lst[] = sql_db::FLD_USER_ID;
            } else {
                if (!in_array($this->type, sql_db::DB_TYPES_NOT_NAMED)) {
                    $field_lst[] = $this->name_field;
                }
            }
            // user cannot change the links like they can change the name, instead a link is removed and another link is created
            if (in_array($this->type, sql_db::DB_TYPES_LINK)) {
                // allow to use also the set_fields method for link fields e.g. for more complex where cases
                if ($this->id_from_field <> '') {
                    $field_lst[] = $this->id_from_field;
                }
                if ($this->id_to_field <> '') {
                    $field_lst[] = $this->id_to_field;
                }
                if ($this->id_link_field <> '') {
                    $field_lst[] = $this->id_link_field;
                }
            }

            // add the given fields at the end
            foreach ($this->field_lst as $field) {
                $field_lst[] = $field;
            }
            $this->field_lst = $field_lst;

            // add the given user fields at the end
            foreach ($this->usr_field_lst as $field) {
                $usr_field_lst[] = $field;
            }
            $this->usr_field_lst = $usr_field_lst;
        }

        foreach ($this->field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            if ($this->usr_query or $this->join_type != '') {
                $this->fields .= ' ' . sql_db::STD_TBL . '.' . $field;
                if ($field == $this->id_field) {
                    // add the user sandbox id for user sandbox queries to find out if the user sandbox has already been created
                    if ($this->usr_query) {
                        if ($this->fields != '') {
                            $this->fields .= ', ';
                        }
                        $this->fields .= ' ' . sql_db::USR_TBL . '.' . $field . ' AS ' . sql_db::USER_PREFIX . $this->id_field;
                    }
                }
            } else {
                $this->fields .= ' ' . $field;

            }
        }

        foreach ($this->join_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->fields .= ' ' . sql_db::LNK_TBL . '.' . $field;
            if ($this->usr_query) {
                if ($this->fields != '') {
                    $this->fields .= ', ';
                }
                $this->fields .= ' ' . sql_db::ULK_TBL . '.' . $field;
            }
        }

        foreach ($this->usr_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_text($field);
        }

        foreach ($this->usr_num_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_num($field);
        }

        foreach ($this->join_usr_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_text($field, sql_db::LNK_TBL, sql_db::ULK_TBL);
        }

        foreach ($this->join_usr_num_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_num($field);
        }

        foreach ($this->usr_only_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            if ($this->fields != '') {
                $this->fields .= ', ';
            }
            $this->fields .= ' ' . sql_db::USR_TBL . '.' . $field;
        }

    }

    /*
       open/close the connection to MySQL
    */

    // link to database
    function open($debug)
    {
        log_debug("db->open", $debug - 15);

        if ($this->db_type == DB_TYPE_POSTGRES) {
            try {
                $this->link = new PDO('pgsql:dbname=zukunft host=localhost',
                    SQL_DB_USER,
                    SQL_DB_PASSWD);
            } catch (PDOException $e) {
                $err_msg = $e->getMessage();
                log_fatal($err_msg, 'mysql->exe');
            }
        } else {
            $this->link = mysqli_connect('localhost', SQL_DB_USER, SQL_DB_PASSWD, 'zukunft') or die('Could not connect: ' . mysqli_error());
        }

        log_debug("mysql->open -> done", $debug - 10);
        return $this->link;
    }

    // just to have all sql in one library
    function close($debug)
    {
        if ($this->db_type == DB_TYPE_POSTGRES) {
            $this->link = null;
        } else {
            mysqli_close($this->link);
        }

        log_debug("db->close -> done", $debug - 10);
    }

    /*
     * setup the environment
     */

    // reset the previous settings
    private function reset()
    {
        $this->usr_view_id = NULL;
        $this->table = '';
        $this->id_field = '';
        $this->id_from_field = '';
        $this->id_to_field = '';
        $this->id_link_field = '';
        $this->name_field = '';
        $this->field_lst = [];
        $this->usr_field_lst = [];
        $this->usr_num_field_lst = [];
        $this->usr_only_field_lst = [];
        $this->join_field_lst = [];
        $this->join_usr_field_lst = [];
        $this->join_usr_num_field_lst = [];
        $this->join_type = '';
        $this->usr_query = false;
        $this->fields = '';
        $this->from = '';
        $this->join = '';
        $this->where = '';
    }

    function sql_of_code_linked_db_rows()
    {
        if ($this->db_type == DB_TYPE_POSTGRES) {
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
    private function get_table_name($type)
    {
        // set the standard table name based on the type
        $result = $type . "s";
        // exceptions from the standard table for 'nicer' names
        if ($result == 'value_time_seriess') {
            $result = 'value_time_series';
        }
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
        if ($result == 'configs') {
            $result = 'config';
        }
        return $result;
    }

    //
    private function set_table($debug)
    {
        $this->table = $this->get_table_name($this->type);
        log_debug("mysql->set_table to (" . $this->table . ")", $debug - 20);
    }

    private function get_id_field_name($type)
    {
        // exceptions for user overwrite tables
        if (zu_str_is_left($type, DB_TYPE_USER_PREFIX)) {
            $type = zu_str_right_of($type, DB_TYPE_USER_PREFIX);
        }
        $result = $type . '_id';
        // exceptions for nice english
        if ($type == 'view_entrie') {
            $result = 'view_entry_id';
        }
        if ($result == 'sys_log_statuss_id') {
            $result = 'sys_log_status_id';
        }
        return $result;
    }

    private function set_id_field()
    {
        $this->id_field = $this->get_id_field_name($this->type);
    }

    private function set_name_field($debug = 0)
    {
        $type = $this->type;
        // exceptions for user overwrite tables
        if (zu_str_is_left($type, DB_TYPE_USER_PREFIX)) {
            $type = zu_str_right_of($type, DB_TYPE_USER_PREFIX);
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

        if ($this->db_type == DB_TYPE_POSTGRES) {
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
            $result = mysqli_query($sql);
            if (!$result) {
                $msg_text = mysqli_error();
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
            if ($this->db_type == DB_TYPE_POSTGRES) {
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
                while ($sql_row = mysqli_fetch_array($sql_result, MYSQL_ASSOC)) {
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
            if ($this->db_type == DB_TYPE_POSTGRES) {
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
                $result = mysqli_fetch_array($sql_result, MYSQL_ASSOC);
            }
        }

        log_debug("mysql->get1 -> done", $debug - 20);
        return $result;
    }

// returns first value of a simple SQL query
    function get_value($field_name, $id_name, $id, $debug)
    {
        $result = '';
        log_debug('mysql->get_value ' . $field_name . ' from ' . $this->type . ' where ' . $id_name . ' = ' . $this->sf($id), $debug - 20);

        if ($this->type <> '') {
            $this->set_table($debug - 1);

            // set fallback values
            if ($field_name == '') {
                $this->set_name_field($debug - 1);
                $field_name = $this->name_field;
            }
            if ($id_name == '') {
                $this->set_id_field();
                $id_name = $this->id_field;
            }

            $sql = "SELECT " . $this->name_sql_esc($field_name) . " FROM " . $this->name_sql_esc($this->table) . " WHERE " . $id_name . " = " . $this->sf($id) . " LIMIT 1;";

            if ($this->db_type == DB_TYPE_POSTGRES) {
                if ($this->link == null) {
                    log_err('Database connection lost', 'get1');
                } else {
                    $sql_result = $this->link->query($sql);
                    // todo fetch seems to be invalid if no record is returned
                    if ($sql_result != false) {
                        $sql_row = $sql_result->fetch(\PDO::FETCH_ASSOC);
                        if ($sql_row != false) {
                            $result = array_values($sql_row)[0];
                        }
                    }
                }
            } else {
                $sql_result = $this->exe($sql, DBL_SYSLOG_FATAL_ERROR, "mysql->get_value", (new Exception)->getTraceAsString(), $debug - 1);
                $sql_row = mysqli_fetch_array($sql_result, MYSQL_NUM);
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
        $sql = "SELECT " . $this->name_sql_esc($field_name) . " FROM " . $this->name_sql_esc($this->table) . " WHERE " . $this->name_sql_esc($id1_name) . " = '" . $id1 . "' AND " . $this->name_sql_esc($id2_name) . " = '" . $id2 . "' LIMIT 1;";

        if ($this->db_type == DB_TYPE_POSTGRES) {
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
            $sql_row = mysqli_fetch_array($sql_result, MYSQL_NUM);
            // todo check if mysql really fill starting with 0
            $result .= $sql_row[0];
        }

        return $result;
    }

// returns the id field of a standard table
// standard table means that the table name ends with 's', the name field is the table name plus '_name' and prim index ends with '_id'
// $name is the unique text that identifies one row e.g. for the $name "Company" the word id "1" is returned
    function get_id($name, $debug = 0)
    {
        $result = '';
        log_debug('mysql->get_id for "' . $name . '" of the db object "' . $this->type . '"', $debug - 12);

        $this->set_table($debug - 1);
        $this->set_id_field();
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
        $this->set_id_field();
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
        $this->set_id_field();
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
        $this->set_id_field();
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
        $this->set_id_field();
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
        if ($this->db_type == DB_TYPE_POSTGRES) {
            $sql = "SELECT id, name 
              FROM ( SELECT t." . $this->id_field . " AS id, 
                            CASE WHEN (u." . $this->name_field . " <> '' IS NOT TRUE) THEN t." . $this->name_field . " ELSE u." . $this->name_field . " END AS name,
                            CASE WHEN (u.excluded                  <> '' IS NOT TRUE) THEN     COALESCE(t.excluded, 0) ELSE COALESCE(u.excluded, 0)     END AS excluded
                      FROM " . $this->name_sql_esc($this->table) . " t       
                  LEFT JOIN user_" . str_replace("`", "", $this->table) . " u ON u." . $this->id_field . " = t." . $this->id_field . " 
                                              AND u.user_id = " . $this->usr_id . " 
                            " . $sql_where . ") AS s
            WHERE excluded <> 1                                   
          ORDER BY name;";
        } else {
            $sql = "SELECT id, name 
              FROM ( SELECT t." . $this->id_field . " AS id, 
                            IF(u." . $this->name_field . " IS NULL, t." . $this->name_field . ", u." . $this->name_field . ") AS name,
                            IF(u.excluded                  IS NULL,     COALESCE(t.excluded, 0), COALESCE(u.excluded, 0))     AS excluded
                      FROM " . $this->name_sql_esc($this->table) . " t       
                  LEFT JOIN user_" . str_replace("`", "", $this->table) . " u ON u." . $this->id_field . " = t." . $this->id_field . " 
                                              AND u.user_id = " . $this->usr_id . " 
                            " . $sql_where . ") AS s
            WHERE excluded <> 1                                   
          ORDER BY name;";
        }
        return $sql;
    }

// create a standard query for a list of database id and name
    function sql_std_lst($debug)
    {
        log_debug("mysql->sql_std_lst (" . $this->type . ")", $debug);

        $this->set_table($debug - 1);
        $this->set_id_field();
        $this->set_name_field($debug - 1);
        $sql = "SELECT " . $this->name_sql_esc($this->id_field) . " AS id,
                   " . $this->name_sql_esc($this->name_field) . " AS name
              FROM " . $this->name_sql_esc($this->table) . "
          ORDER BY " . $this->name_sql_esc($this->name_field) . ";";

        return $sql;
    }

    // set the where statement for a later call of the select function
    function where($fields, $values, $debug = 0)
    {
        $result = '';
        if (count($fields) != count($values)) {
            log_err('Number of fields does not match with the number of values', 'mysql->where');
        } else {
            foreach (array_keys($fields) as $i) {
                $field = $this->name_sql_esc($fields[$i]);
                if ($result == '') {
                    $result .= ' WHERE ' . $field . ' = ' . $this->sf($values[$i]);
                } else {
                    $result .= ' AND ' . $field . ' = ' . $this->sf($values[$i]);
                }
            }
            $this->where = $result;
        }
        return $result;
    }

    // set the standard where statement to select either by id or name or code_id
    // the type must have been already set e.g. to 'source'
    // TODO check why the request user must be set to search by code_id ?
    // TODO check if test for positive and negative id values is needed; because phrases can have a negative id ?
    function set_where($id, $name = '', $code_id = '')
    {
        $result = '';

        if ($id <> 0) {
            if ($this->usr_query or $this->join <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $result .= $this->id_field . " = " . $id;
        } elseif ($code_id <> '' and !is_null($this->usr_id)) {
            if ($this->usr_query or $this->join <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $result .= sql_db::FLD_CODE_ID . " = " . $this->sf($code_id);
            if ($this->db_type == DB_TYPE_POSTGRES) {
                $result .= ' AND ';
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $result .= sql_db::FLD_CODE_ID . ' != NULL';
            }
        } elseif ($name <> '' and !is_null($this->usr_id)) {
            if ($this->usr_query or $this->join <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $result .= $this->name_field . " = " . $this->sf($name, sql_db::FLD_FORMAT_TEXT);
            /*
            if ($this->db_type == DB_TYPE_POSTGRES) {
                $result .= ' AND ';
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                  $result .= $this->name_field . ' != NULL';
            }
            */
        }

        if ($result == '') {
            log_err('Internal error: to find a ' . $this->type . ' either the id, name or code_id must be set', 'sql->set_where');
        } else {
            $this->where = ' WHERE ' . $result;
        }

        return $result;
    }

    function set_where_link($id, $id_from = 0, $id_to = 0, $id_type = 0)
    {
        $result = '';

        if ($id <> 0) {
            if ($this->usr_query or $this->join <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $result .= $this->id_field . " = " . $id;
        } elseif ($id_from <> 0 and $id_to <> 0) {
            if ($this->id_from_field == '' or $this->id_to_field == '') {
                log_err('Internal error: to find a ' . $this->type . ' the link fields must be defined', 'sql->set_where_link');
            } else {
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $result .= $this->id_from_field . " = " . $id_from . " AND ";
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $result .= $this->id_to_field . " = " . $id_to;
                if ($id_type <> 0) {
                    if ($this->id_link_field == '') {
                        log_err('Internal error: to find a ' . $this->type . ' the link type field must be defined', 'sql->set_where_link');
                    } else {
                        $result .= ' AND ';
                        if ($this->usr_query or $this->join <> '') {
                            $result .= sql_db::STD_TBL . '.';
                        }
                        $result .= $this->id_link_field . " = " . $id_type;
                    }

                }
            }
        }

        if ($result == '') {
            log_err('Internal error: to find a ' . $this->type . ' either the id or the from and to id must be set', 'sql->set_where_link');
        } else {
            $this->where = ' WHERE ' . $result;
        }

        return $result;
    }

    // get the where statement supposed to be used for the next query creation
    // mainly used to test if the search has valid parameters
    function get_where()
    {
        return $this->where;
    }

    // set the where statement for a later call of the select function
    // mainly used to overwrite the for special cases, where the set_where function cannot be used
    // TODO prevent code injections
    function set_where_text($where_text, $debug = 0)
    {
        $this->where = ' WHERE ' . $where_text;
    }

    // create the SQL part for the selected fields
    private function sql_usr_fields($usr_field_lst)
    {
    }

    // create the from SQL statement based on the type
    private function set_from()
    {
        if ($this->usr_query) {
            $usr_table_name = $this->name_sql_esc(sql_db::USER_PREFIX . $this->table);
            $this->join .= ' LEFT JOIN ' . $usr_table_name . ' ' . sql_db::USR_TBL;
            $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $this->id_field . ' = ' . sql_db::USR_TBL . '.' . $this->id_field;
            $this->join .= ' AND ' . sql_db::USR_TBL . '.' . sql_db::FLD_USER_ID . ' = ' . $this->usr_view_id;
        }
        if ($this->join_type <> '') {
            $join_table_name = $this->get_table_name($this->join_type);
            $join_id_field = $this->get_id_field_name($this->join_type);
            $this->join .= ' LEFT JOIN ' . $join_table_name . ' ' . sql_db::LNK_TBL;
            $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $join_id_field . ' = ' . sql_db::LNK_TBL . '.' . $join_id_field;
            if ($this->usr_query) {
                $this->join .= ' LEFT JOIN ' . $join_table_name . ' ' . sql_db::ULK_TBL;
                $this->join .= ' ON ' . sql_db::USR_TBL . '.' . $join_id_field . ' = ' . sql_db::ULK_TBL . '.' . $join_id_field;
            }
        }
        $this->from = ' FROM ' . $this->name_sql_esc($this->table);
        if ($this->join <> '') {
            $this->from .= ' ' . sql_db::STD_TBL;
        }
    }

    // create a SQL select statement for the connected database
    function select($has_id = true, $debug = 0)
    {
        $sql = 'SELECT';
        $this->set_table($debug - 1);
        $this->set_field_statement($has_id);
        $this->set_from();
        $sql .= $this->fields . $this->from . $this->join . $this->where . ';';
        return $sql;
    }

    // return all database ids, where the owner is not yet set
    function missing_owner($debug)
    {
        log_debug("mysql->missing_owner (" . $this->type . ")", $debug);
        $result = Null;

        $this->set_table($debug - 1);
        $this->set_id_field();
        $sql = "SELECT " . $this->id_field . " AS id
              FROM " . $this->name_sql_esc($this->table) . "
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
        $sql = "UPDATE " . $this->name_sql_esc($this->table) . "
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
                    $values[$i] = $this->sf($values[$i]);
                }
                $sql = 'INSERT INTO ' . $this->name_sql_esc($this->table) . ' (' . implode(',', $fields) . ') 
                                      VALUES (' . implode(',', $values) . ');';
            }
        } else {
            log_debug('mysql->insert into "' . $this->type . '" SET "' . $fields . '" WITH "' . $values . '" for user ' . $this->usr_id, $debug - 10);
            $sql = 'INSERT INTO ' . $this->name_sql_esc($this->table) . ' (' . $fields . ') 
                                 VALUES (' . $this->sf($values) . ');';
        }

        if ($sql <> '') {
            if ($this->db_type == DB_TYPE_POSTGRES) {
                if ($this->link == null) {
                    log_err('Database connection lost', 'insert');
                } else {

                    try {
                        // TODO catch SQL errors and report them
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
                    $result = mysqli_insert_id();
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
        $result = $this->insert($this->name_field, $this->sf($name), $debug - 1);

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
        $this->set_id_field();
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
        $sql_where = ' WHERE ' . $this->id_field . ' = ' . $this->sf($id);
        if (substr($this->type, 0, 4) == 'user') {
            // ... but not for the user table itself
            if ($this->type <> 'user') {
                $sql_where .= ' AND user_id = ' . $this->usr_id;
            }
        }

        if ($par_ok) {
            $sql_upd = 'UPDATE ' . $this->name_sql_esc($this->table);
            $sql_set = '';
            if (is_array($fields)) {
                foreach (array_keys($fields) as $i) {
                    if ($sql_set == '') {
                        $sql_set .= ' SET ' . $fields[$i] . ' = ' . $this->sf($values[$i]);
                    } else {
                        $sql_set .= ', ' . $fields[$i] . ' = ' . $this->sf($values[$i]);
                    }
                }
            } else {
                $sql_set .= ' SET ' . $fields . ' = ' . $this->sf($values);
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
            $sql = 'DELETE FROM ' . $this->name_sql_esc($this->table);
            $sql_del = '';
            foreach (array_keys($id_fields) as $i) {
                $del_val = $id_values[$i];
                if (is_array($del_val)) {
                    $del_val_txt = ' IN (' . $this->sf(implode(",", $del_val)) . ') ';
                } else {
                    $del_val_txt = ' = ' . $this->sf($del_val) . ' ';
                }
                if ($sql_del == '') {
                    $sql_del .= ' WHERE ' . $id_fields[$i] . $del_val_txt;
                } else {
                    $sql_del .= ' AND ' . $id_fields[$i] . $del_val_txt;
                }
            }
            $sql = $sql . $sql_del . ';';
        } else {
            $sql = 'DELETE FROM ' . $this->name_sql_esc($this->table) . ' WHERE ' . $id_fields . ' = ' . $this->sf($id_values) . ';';
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

    /*
     * private supporting functions
     */

    // escape or reformat the reserved SQL names
    private function name_sql_esc($field)
    {
        switch ($this->db_type) {
            case DB_TYPE_POSTGRES:
                if (in_array(strtoupper($field), sql_db::POSTGRES_RESERVED_NAMES)
                    or in_array(strtoupper($field), sql_db::POSTGRES_RESERVED_NAMES_EXTRA)) {
                    $field = '"' . $field . '"';
                }
                break;
            case DB_TYPE_MYSQL:
                if (in_array(strtoupper($field), sql_db::MYSQL_RESERVED_NAMES)
                    or in_array(strtoupper($field), sql_db::MYSQL_RESERVED_NAMES_EXTRA)) {
                    $field = '`' . $field . '`';
                }
                break;
            default:
                log_err('Unexpected database type named "' . $this->db_type . '"', 'sql_db::name_sql_esc');
                break;
        }
        return $field;
    }

    //
    function sf($field_value, $forced_format = '', $debug = 0)
    {
        if ($this->db_type == DB_TYPE_POSTGRES) {
            $result = $this->postgres_format($field_value, $forced_format);
        } else {
            $result = $this->mysqli_format($field_value, $forced_format);
        }
        return $result;
    }

    // formats one value for the sql statement
    function postgres_format($field_value, $forced_format, $debug = 0)
    {
        log_debug("mysqli_format (" . $field_value . ")", $debug - 1);

        // remove any previous formatting (if all code is fine, this may not be needed any more)
        $result = $field_value;
        if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
            $result = substr($result, 1, -1);
        }

        // format the "real" value for sql
        $result = pg_escape_string($result);

        // add the formatting for the sql statement
        if (trim($result) == "") {
            $result = "NULL";
        } else {
            if (!is_numeric($result) or $forced_format == sql_db::FLD_FORMAT_TEXT) {
                // undo the double high quote escape char, because this is not needed if the string is capsuled by single high quote
                $result = str_replace('\"', '"', $result);
                $result = "'" . $result . "'";
            } else {
                $result = $result;
            }
        }
        log_debug("postgres_format -> done (" . $result . ")", $debug - 1);

        return $result;
    }

    // fallback SQL string escape function if there is no database connection
    private function sql_escape($param)
    {
        if (is_array($param))
            return array_map(__METHOD__, $param);

        if (!empty($param) && is_string($param)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $param);
        }

        return $param;
    }

    // formats one value for the sql statement
    function mysqli_format($field_value, $forced_format, $debug = 0)
    {
        log_debug("mysqli_format (" . $field_value . ")", $debug - 1);

        global $db_con;

        // remove any previous formatting (if all code is fine, this may not be needed any more)
        $result = $field_value;
        if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
            $result = substr($result, 1, -1);
        }

        // format the "real" value for sql
        if ($db_con->link == null) {
            $result = $this->sql_escape($result);
        } else {
            $result = mysqli_real_escape_string($db_con, $result);
        }

        // add the formatting for the sql statement
        if (trim($result) == "") {
            $result = "NULL";
        } else {
            if (!is_numeric($result) or $forced_format == sql_db::FLD_FORMAT_TEXT) {
                // undo the double high quote escape char, because this is not needed if the string is capsuled by single high quote
                $result = str_replace('\"', '"', $result);
                $result = "'" . $result . "'";
            }
        }

        // exceptions
        if ($result == "'Now()'") {
            $result = "Now()";
        }

        log_debug("mysqli_format -> done (" . $result . ")", $debug - 1);

        return $result;

    }
}


/*

  name shortcuts - rename some often used functions to make to code look nicer and not draw the focus away from the important part
  --------------
  
*/

// Sql Format: format a string for the MySQL database
// shortcut for mysqli_format
// outside this module it should only be used to format queries that are not yet using the abstract form for all databases (MySQL, MariaSQL, Casandra, Droid)
// TODO only use the function inside the sql_db class
function sf($field_value)
{
    global $db_con;
    if ($db_con->db_type == DB_TYPE_POSTGRES) {
        $result = postgres_format($field_value);
    } else {
        $result = mysqli_format($field_value);
    }
    return $result;
}

// formats one value for the sql statement
// TODO only use the function inside the sql_db class
function postgres_format($field_value, $debug = 0)
{
    log_debug("mysqli_format (" . $field_value . ")", $debug - 1);

    // remove any previous formatting (if all code is fine, this may not be needed any more)
    $result = $field_value;
    if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
        $result = substr($result, 1, -1);
    }

    // format the "real" value for sql
    //$result = mysqli_real_escape_string($result);

    // add the formatting for the sql statement
    if (trim($result) == "") {
        $result = "NULL";
    } else {
        if (is_numeric($result)) {
            $result = $result;
        } else {
            // undo the double high quote escape char, because this is not needed if the string is capsuled by single high quote
            $result = str_replace('\"', '"', $result);
            $result = "'" . $result . "'";
        }
    }
    log_debug("postgres_format -> done (" . $result . ")", $debug - 1);

    return $result;
}

// formats one value for the sql statement
// TODO only use the function inside the sql_db class
function mysqli_format($field_value, $debug = 0)
{
    log_debug("mysqli_format (" . $field_value . ")", $debug - 1);

    global $db_con;

    // remove any previous formatting (if all code is fine, this may not be needed any more)
    $result = $field_value;
    if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
        $result = substr($result, 1, -1);
    }

    // format the "real" value for sql
    if ($db_con->db_type == DB_TYPE_POSTGRES) {
        $result = pg_escape_string($result);
    } else {
        $result = mysqli_real_escape_string($result);
    }

    // add the formatting for the sql statement
    if (trim($result) == "") {
        $result = "NULL";
    } else {
        if (is_numeric($result)) {
            $result = $result;
        } else {
            // undo the double high quote escape char, because this is not needed if the string is capsuled by single high quote
            $result = str_replace('\"', '"', $result);
            $result = "'" . $result . "'";
        }
    }

    // exceptions
    if ($result == "'Now()'") {
        $result = "Now()";
    }

    log_debug("mysqli_format -> done (" . $result . ")", $debug - 1);

    return $result;

}

// SQL list: create a query string for the standard list
// e.g. the type "source" creates the SQL statement "SELECT source_id, source_name FROM sources ORDER BY source_name;"
function sql_lst($type, $debug)
{
    global $db_con;
    $db_con->set_type($type);
    $sql = $db_con->sql_std_lst($debug - 1);
    return $sql;
}

// similar to "sql_lst", but taking the user sandbox into account
function sql_lst_usr($type, $usr, $debug)
{
    global $db_con;
    $db_con->set_type($type);
    $db_con->usr_id = $usr->id;
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