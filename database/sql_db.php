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

// TODO Check that calling the update function always expects a boolean as return value
// TODO check that $db_con->get and $db_con->get1 always can handle a null row result
// TODO check that for all update and insert statement the user id is set correctly (use word user config as an example)
// TODO mailnly for data from the internet use prepared statements to prevent SQL injections

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

    // tables that does not have a name e.g. DB_TYPE_WORD_LINK is a link, but is nevertheless named
    const DB_TYPES_NOT_NAMED = [DB_TYPE_VALUE, DB_TYPE_FORMULA_LINK, DB_TYPE_VIEW_COMPONENT_LINK, DB_TYPE_REF];
    // tables that link two named tables
    // TODO set automatically by set_link_fields???
    const DB_TYPES_LINK = [DB_TYPE_WORD_LINK, DB_TYPE_FORMULA_LINK, DB_TYPE_VIEW_COMPONENT_LINK, DB_TYPE_REF];

    const USER_PREFIX = "user_";          // prefix used for tables where the user sandbox values are stored

    const STD_TBL = "s";                  // prefix used for the standard table where data for all users are stored
    const USR_TBL = "u";                  // prefix used for the standard table where the user sandbox data is stored
    const LNK_TBL = "l";                  // prefix used for the table which should be joined in the result
    const ULK_TBL = "c";                  // prefix used for the table which should be joined in the result of the user sandbox data

    const FLD_CODE_ID = "code_id";        // field name for the code link
    const FLD_USER_ID = "user_id";        // field name for the user table foreign key field

    const FLD_FORMAT_TEXT = "text";       // to force the text formatting of an value for the SQL statement formatting
    const FLD_FORMAT_VAL = "number";      // to force the numeric formatting of an value for the SQL statement formatting
    const FLD_FORMAT_BOOL = "boolean";    // to force the boolean formatting of an value for the SQL statement formatting

    public $db_type = '';                 // the database type which should be used for this connection e.g. postgreSQL or MYSQL
    public $link = null;                  // the link object to the database
    public $usr_id = null;                // the user id of the person who request the database changes
    private $usr_view_id = null;          // the user id of the person which values should be returned e.g. an admin might want to check the data of an user

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
    private $usr_bool_field_lst = [];     // list of user specific boolean / tinyint fields that should be returned in the next select query
    private $usr_only_field_lst = [];     // list of fields that are only in the user sandbox
    private $join_field_lst = [];         // list of fields that should be returned in the next select query that are taken from a joined table
    private $join_usr_field_lst = [];     // list of fields that should be returned in the next select query that are taken from a joined table
    private $join_usr_num_field_lst = []; // list of fields that should be returned in the next select query that are taken from a joined table
    private $join_type = '';              // the type name of the table to join
    private $usr_query = false;           // true, if the query is expected to retrieve user specific data
    private $usr_join_query = false;      // true, if the joined query is also expected to retrieve user specific data
    private $usr_only_query = false;      // true, if the query is expected to retrieve ONLY the user specific data without the standard values

    private $fields = '';                 // the fields               SQL statement that is used for the next select query
    private $from = '';                   // the from                 SQL statement that is used for the next select query
    private $join = '';                   // the join                 SQL statement that is used for the next select query
    private $where = '';                  // the where condition as a SQL statement that is used for the next select query

    private $prepared_sql_names = [];     // list of all SQL queries that have already been prepared during the open connection

    /*
     * basic interface function for the private class parameter
     */

    // define the table that should be used for the next select, insert, update or delete statement
    // resets all previous db query settings such as fields, user_fields, so this should be the first statement when defining a database query
    // $type is a string that is used to select the table name, the id field and the name field
    // if $usr_table is true the user table instead of the standard table is used
    // TODO check that this is always called directly before the query is created, so that
    function set_type($type, $usr_table = false): bool
    {
        global $usr;

        $this->reset();
        $this->type = $type;
        if ($usr == null) {
            $this->set_usr(SYSTEM_USER_ID); // if the session user is not yet set, use the system user id to test the database compatibility
        } else {
            $this->set_usr($usr->id); // by default use the session user id
        }
        $this->set_table($usr_table);
        $this->set_id_field();
        $this->set_name_field();
        return true;
    }

    function get_type(): string
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
    // must be set AFTER the set_usr_fields, set_usr_num_fields, set_usr_bool_fields, set_usr_bool_fields or set_usr_only_fields for correct handling of $this->usr_join_query
    // $field_lst are the field names that should be included in the result
    // $join_table is the table from where the fields should be taken; use the type name, not the table name
    // $join_field is the index field that should be used for the join that must exist in both tables, default is the id of the joined table
    function set_join_fields($join_field_lst, $join_type, $join_field = '')
    {
        $this->join_field_lst = $join_field_lst;
        $this->usr_join_query = false;
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
        $this->usr_join_query = true;
        $this->usr_field_lst = $usr_field_lst;
    }

    function set_usr_num_fields($usr_field_lst)
    {
        $this->usr_query = true;
        $this->usr_join_query = true;
        $this->usr_num_field_lst = $usr_field_lst;
    }

    function set_usr_bool_fields($usr_field_lst)
    {
        $this->usr_query = true;
        $this->usr_join_query = true;
        $this->usr_bool_field_lst = $usr_field_lst;
    }

    function set_usr_only_fields($field_lst)
    {
        $this->usr_query = true;
        $this->usr_join_query = true;
        $this->usr_only_field_lst = $field_lst;
    }

    private function set_field_sep()
    {
        if ($this->fields != '') {
            $this->fields .= ', ';
        }
    }

    // interface function for sql_usr_field
    function get_usr_field($field, $stb_tbl = sql_db::STD_TBL, $usr_tbl = sql_db::USR_TBL, $field_format = sql_db::FLD_FORMAT_TEXT, $as = ''): string
    {
        return $this->sql_usr_field($field, $field_format, $stb_tbl, $usr_tbl, $as);
    }

    // internal interface function for sql_usr_field using the class db type settings and text fields
    private function set_field_usr_text($field, $stb_tbl = sql_db::STD_TBL, $usr_tbl = sql_db::USR_TBL)
    {
        $this->fields .= $this->sql_usr_field($field, sql_db::FLD_FORMAT_TEXT, $stb_tbl, $usr_tbl);
    }

    // internal interface function for sql_usr_field using the class db type settings and number fields
    private function set_field_usr_num($field)
    {
        $this->fields .= $this->sql_usr_field($field, sql_db::FLD_FORMAT_VAL, sql_db::STD_TBL, sql_db::USR_TBL);
    }

    // internal interface function for sql_usr_field using the class db type settings and boolean / tinyint fields
    private function set_field_usr_bool($field)
    {
        $this->fields .= $this->sql_usr_field($field, sql_db::FLD_FORMAT_BOOL, sql_db::STD_TBL, sql_db::USR_TBL);
    }

    // return the SQL statement for a field taken from the user sandbox table or from the table with the common values
    // $db_type is the SQL database type which is in this case independent from the class setting to be able to use it anywhere
    private function sql_usr_field($field, $field_format, $stb_tbl, $usr_tbl, $as = ''): string
    {
        $result = '';
        if ($as == '') {
            $as = $field;
        }
        if ($this->db_type == DB_TYPE_POSTGRES) {
            if ($field_format == sql_db::FLD_FORMAT_TEXT) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " <> '' IS NOT TRUE) THEN " . $stb_tbl . "." . $field . " ELSE " . $usr_tbl . "." . $field . " END AS " . $as;
            } elseif ($field_format == sql_db::FLD_FORMAT_VAL) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " IS NULL) THEN " . $stb_tbl . "." . $field . " ELSE " . $usr_tbl . "." . $field . " END AS " . $as;
            } elseif ($field_format == sql_db::FLD_FORMAT_BOOL) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " IS NULL) THEN COALESCE(" . $stb_tbl . "." . $field . ",0) ELSE COALESCE(" . $usr_tbl . "." . $field . ",0) END AS " . $as;
            } else {
                log_err('Unexpected field format ' . $field_format);
            }
        } elseif ($this->db_type == DB_TYPE_MYSQL) {
            if ($field_format == sql_db::FLD_FORMAT_TEXT or $field_format == sql_db::FLD_FORMAT_VAL) {
                $result = '         IF(' . $usr_tbl . '.' . $field . ' IS NULL, ' . $stb_tbl . '.' . $field . ', ' . $usr_tbl . '.' . $field . ')    AS ' . $as;
            } elseif ($field_format == sql_db::FLD_FORMAT_BOOL) {
                $result = '         IF(' . $usr_tbl . '.' . $field . ' IS NULL, COALESCE(' . $stb_tbl . '.' . $field . ',0), COALESCE(' . $usr_tbl . '.' . $field . ',0))    AS ' . $as;
            } else {
                log_err('Unexpected field format ' . $field_format);
            }
        } else {
            log_err('Unexpected database type ' . $this->db_type);
        }
        return $result;
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
            if ($this->usr_query and $this->usr_join_query) {
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

        foreach ($this->usr_bool_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_bool($field);
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
    function open()
    {
        log_debug("db->open");

        if ($this->db_type == DB_TYPE_POSTGRES) {
            $this->link = pg_connect('host=localhost dbname=zukunft user=' . SQL_DB_USER . ' password=' . SQL_DB_PASSWD);
        } else {
            $this->link = mysqli_connect('localhost', SQL_DB_USER, SQL_DB_PASSWD, 'zukunft') or die('Could not connect: ' . mysqli_error());
        }

        log_debug("sql_db->open -> done");
        return $this->link;
    }

    // just to have all sql in one library
    function close()
    {
        if ($this->db_type == DB_TYPE_POSTGRES) {
            pg_close($this->link);
        } else {
            mysqli_close($this->link);
        }
        $this->link = null;

        log_debug("db->close -> done");
    }

    /*
     * setup the environment
     */

    // reset the previous settings
    private function reset()
    {
        $this->usr_view_id = null;
        $this->table = '';
        $this->id_field = '';
        $this->id_from_field = '';
        $this->id_to_field = '';
        $this->id_link_field = '';
        $this->name_field = '';
        $this->field_lst = [];
        $this->usr_field_lst = [];
        $this->usr_num_field_lst = [];
        $this->usr_bool_field_lst = [];
        $this->usr_only_field_lst = [];
        $this->join_field_lst = [];
        $this->join_usr_field_lst = [];
        $this->join_usr_num_field_lst = [];
        $this->join_type = '';
        $this->usr_query = false;
        $this->usr_only_query = false;
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
    function get_table_name($type): string
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
    private function set_table($usr_table = false)
    {
        if ($usr_table) {
            $this->table = sql_db::USER_PREFIX . $this->get_table_name($this->type);
            $this->usr_only_query = true;
        } else {
            $this->table = $this->get_table_name($this->type);
        }
        log_debug("sql_db->set_table to (" . $this->table . ")");
    }

    private function get_id_field_name($type): string
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

    function get_id_field(): string
    {
        return $this->id_field;
    }

    private function set_name_field()
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
        log_debug("sql_db->set_name_field to (" . $result . ")");
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
    function exe($sql, $sql_name = '', $sql_array = array(), $log_level = DBL_SYSLOG_ERROR)
    {
        log_debug("sql_db->exe (" . $sql . " named " . $sql_name . " for  user " . $this->usr_id . ")");

        $result = '';

        // check and improve the given parameters
        $function_trace = (new Exception)->getTraceAsString();

        if ($this->link == null) {
            log_fatal('database connection lost', 'sql_db->exe->' . $sql_name);
            // TODO try auto reconnect in 1, 2 4, 8, 16 ... and max 3600 sec
        } else {
            if ($this->db_type == DB_TYPE_POSTGRES) {
                $sql = str_replace("\n", "", $sql);
                if ($sql_name == '') {
                    $result = pg_query($this->link, $sql);
                    if (!$result) {
                        log_err('Database error ' . pg_last_error($this->link) . ' when preparing ' . $sql, 'sql_db->PostgreSQL->exe->' . $sql_name);
                    }

                } else {
                    if (in_array($sql_name, $this->prepared_sql_names)) {
                        $result = pg_prepare($this->link, $sql_name, $sql);
                        if ($result == false) {
                            log_err('Database error ' . pg_last_error($this->link) . ' when preparing ' . $sql, 'sql_db->PostgreSQL->exe->' . $sql_name);
                        } else {
                            $this->prepared_sql_names[] = $sql_name;
                        }
                    }
                    $result = pg_execute($this->link, $sql_name, $sql_array);
                    if ($result == false) {
                        log_err('Database error ' . pg_last_error($this->link) . ' when executing ' . $sql, 'sql_db->PostgreSQL->exe->' . $sql_name);
                    }
                }
            } elseif ($this->db_type == DB_TYPE_MYSQL) {
                // TODO review to used at least $sql_array
                if ($sql_name == '') {
                    $result = mysqli_query($sql);
                } else {
                    // TODO review this untested part, so that it can be used
                    $stmt = mysqli_prepare($this->link, $sql);
                    // TODO create function sql_array_to_types
                    mysqli_stmt_bind_param($stmt, 's', $sql_array);
                    $stmt->execute();
                    $result = $stmt->get_result();
                }
                if (!$result) {
                    $msg_text = mysqli_error();
                    $sql = str_replace("'", "", $sql);
                    $sql = str_replace("\"", "", $sql);
                    $msg_text .= " (" . $sql . ")";
                    $msg_type_id = cl($log_level);
                    $result = log_msg($msg_text, $msg_text . ' from ' . $sql_name, $msg_type_id, $sql_name, $function_trace, $this->usr_id);
                    log_debug("sql_db->exe -> error (" . $result . ")");
                }
            } else {
                log_err('Unknown database type "' . $this->db_type . '"', 'sql_db->fetch');
            }
        }

        return $result;
    }

    /*

      technical function to finally get data from the MySQL database

    */

    // fetch the first value from an SQL database (either PostgreSQL or MySQL at the moment)
    private function fetch($sql, $sql_name = '', $sql_array = array(), $fetch_all = false)
    {
        $result = null;

        if ($sql <> "") {
            if ($this->link == null) {
                log_warning('Database connection lost', 'sql_db->fetch');
                // TODO try auto reconnect in 1, 2 4, 8, 16 ... and max 3600 sec
            } else {
                if ($this->db_type == DB_TYPE_POSTGRES) {
                    $sql_result = $this->exe($sql, $sql_name, $sql_array);
                    if ($fetch_all) {
                        if ($sql_result) {
                            while ($sql_row = pg_fetch_array($sql_result)) {
                                $result[] = $sql_row;
                            }
                        }
                    } else {
                        $result = pg_fetch_array($sql_result);
                    }
                } elseif ($this->db_type == DB_TYPE_MYSQL) {
                    $sql_result = $this->exe($sql, $sql_name, $sql_array);
                    if ($fetch_all) {
                        while ($sql_row = mysqli_fetch_array($sql_result, MYSQLI_BOTH)) {
                            $result[] = $sql_row;
                        }
                    } else {
                        $result = mysqli_fetch_array($sql_result, MYSQLI_BOTH);
                    }
                } else {
                    log_err('Unknown database type "' . $this->db_type . '"', 'sql_db->fetch');
                }
            }
        }

        return $result;
    }

    // fetch the first row from an SQL database (either PostgreSQL or MySQL at the moment)
    private function fetch_first($sql, $sql_name = '', $sql_array = array())
    {
        //return $this->fetch($sql, $sql_name, $sql_array);
        return $this->fetch($sql);
    }

    // fetch the all value from an SQL database (either PostgreSQL or MySQL at the moment)
    private function fetch_all($sql)
    {
        return $this->fetch($sql, '', array(), true);
    }

    private function debug_msg($sql, $type)
    {
        global $debug;
        if ($debug > 20) {
            log_debug("sql_db->" . $type . " (" . $sql . ")");
        } else {
            log_debug("sql_db->" . $type . " (" . substr($sql, 0, 100) . " ... )");
        }
    }

    // returns all values of an SQL query in an array
    function get($sql)
    {
        $this->debug_msg($sql, 'get');
        return $this->fetch_all($sql);
    }

    // get only the first record from the database
    function get1($sql)
    {
        $this->debug_msg($sql, 'get1');

        // optimise the sql statement
        $sql = trim($sql);
        if (strpos($sql, "LIMIT") === FALSE) {
            if (substr($sql, -1) == ";") {
                $sql = substr($sql, 0, -1) . " LIMIT 1;";
            }
        }

        return $this->fetch_first($sql);
    }

    // returns first value of a simple SQL query
    function get_value($field_name, $id_name, $id)
    {
        $result = '';
        log_debug('sql_db->get_value ' . $field_name . ' from ' . $this->type . ' where ' . $id_name . ' = ' . $this->sf($id));

        if ($this->type <> '') {
            $this->set_table();

            // set fallback values
            if ($field_name == '') {
                $this->set_name_field();
                $field_name = $this->name_field;
            }
            if ($id_name == '') {
                $this->set_id_field();
                $id_name = $this->id_field;
            }

            //$sql = "SELECT " . $this->name_sql_esc($field_name) . " FROM " . $this->name_sql_esc($this->table) . " WHERE " . $id_name . " = $1 LIMIT 1;";
            //$sql_name = 'get_value_' . $id_name . '_' . $this->name_sql_esc($field_name) . '_' . $this->name_sql_esc($this->table);
            //$sql_array = array($this->sf($id));
            $sql = "SELECT " . $this->name_sql_esc($field_name) . " FROM " . $this->name_sql_esc($this->table) . " WHERE " . $id_name . " = " . $this->sf($id) . " LIMIT 1;";

            $sql_row = $this->fetch_first($sql);

            if ($sql_row) {
                if (count($sql_row) > 0) {
                    $result = $sql_row[0];
                }
            }

        } else {
            log_err("Type not set to get " . $id . " " . $id_name . ".", "sql_db->get_value", (new Exception)->getTraceAsString());
        }

        return $result;
    }

// similar to sql_db->get_value, but for two key fields
    function get_value_2key($field_name, $id1_name, $id1, $id2_name, $id2)
    {
        $result = '';
        log_debug('sql_db->get_value_2key ' . $field_name . ' from ' . $this->type . ' where ' . $id1_name . ' = ' . $id1 . ' and ' . $id2_name . ' = ' . $id2);

        $this->set_table();
        $sql = "SELECT " . $this->name_sql_esc($field_name) . " FROM " . $this->name_sql_esc($this->table) . " WHERE " . $this->name_sql_esc($id1_name) . " = '" . $id1 . "' AND " . $this->name_sql_esc($id2_name) . " = '" . $id2 . "' LIMIT 1;";
        $sql_name = 'get_value_' . $this->name_sql_esc($id1_name) . '_' . $this->name_sql_esc($id2_name) . '_' . $this->name_sql_esc($field_name) . '_' . $this->name_sql_esc($this->table);
        $sql_array = array($this->sf($id1), $this->sf($id2));

        $sql_row = $this->fetch_first($sql, $sql_name, $sql_array);

        if (count($sql_row) > 0) {
            $result = $sql_row[0];
        }

        return $result;
    }

// returns the id field of a standard table
// standard table means that the table name ends with 's', the name field is the table name plus '_name' and prim index ends with '_id'
// $name is the unique text that identifies one row e.g. for the $name "Company" the word id "1" is returned
    function get_id($name)
    {
        $result = '';
        log_debug('sql_db->get_id for "' . $name . '" of the db object "' . $this->type . '"');

        $this->set_table();
        $this->set_id_field();
        $this->set_name_field();
        $result .= $this->get_value($this->id_field, $this->name_field, $name);

        log_debug('sql_db->get_id is "' . $result . '"');
        return $result;
    }

    function get_id_from_code($code_id): string
    {
        $result = '';
        log_debug('sql_db->get_id_from_code for "' . $code_id . '" of the db object "' . $this->type . '"');

        $this->set_table();
        $this->set_id_field();
        $result .= $this->get_value($this->id_field, DBL_FIELD, $code_id);

        log_debug('sql_db->get_id_from_code is "' . $result . '"');
        return $result;
    }

    // similar to get_id, but the other way round
    function get_name($id)
    {
        $result = '';
        log_debug('sql_db->get_name for "' . $id . '" of the db object "' . $this->type . '"');

        $this->set_table();
        $this->set_id_field();
        $this->set_name_field();
        $result = $this->get_value($this->name_field, $this->id_field, $id);

        log_debug('sql_db->get_name is "' . $result . '"');
        return $result;
    }

    // similar to zu_sql_get_id, but using a second ID field
    function get_id_2key($name, $field2_name, $field2_value)
    {
        $result = '';
        log_debug('sql_db->get_id_2key for "' . $name . ',' . $field2_name . ',' . $field2_value . '" of the db object "' . $this->type . '"');

        $this->set_table();
        $this->set_id_field();
        $this->set_name_field();
        $result = $this->get_value_2key($this->id_field, $this->name_field, $name, $field2_name, $field2_value);

        log_debug('sql_db->get_id_2key is "' . $result . '"');
        return $result;
    }

// create a standard query for a list of database id and name while taking the user sandbox into account
    function sql_std_lst_usr()
    {
        log_debug("sql_db->sql_std_lst_usr (" . $this->type . ")");

        $this->set_table();
        $this->set_id_field();
        $this->set_name_field();
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
    function sql_std_lst()
    {
        log_debug("sql_db->sql_std_lst (" . $this->type . ")");

        $this->set_table();
        $this->set_id_field();
        $this->set_name_field();
        $sql = "SELECT " . $this->name_sql_esc($this->id_field) . " AS id,
                   " . $this->name_sql_esc($this->name_field) . " AS name
              FROM " . $this->name_sql_esc($this->table) . "
          ORDER BY " . $this->name_sql_esc($this->name_field) . ";";

        return $sql;
    }

// set the where statement for a later call of the select function
    function where($fields, $values)
    {
        $result = '';
        if (count($fields) != count($values)) {
            log_err('Number of fields does not match with the number of values', 'sql_db->where');
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
        if ($this->usr_only_query) {
            $result .= ' AND ' . sql_db::FLD_USER_ID . ' = ' . $this->usr_view_id;
        }

        if ($result == '') {
            log_err('Internal error: to find a ' . $this->type . ' either the id, name or code_id must be set', 'sql->set_where');
        } else {
            $this->where = ' WHERE ' . $result;
        }

        return $result;
    }

    // set the SQL WHERE statement for link tables
    // if $id_from or $id_to is null all links to the other side are selected
    // e.g. if for formula_links just the phrase id is set, all formulas linked to the given phrase are returned
    // TODO allow also to retrieve a list of linked objects
    // TOTO get the user specific list of linked objects
    function set_where_link($id, $id_from = 0, $id_to = 0, $id_type = 0): string
    {
        $result = '';

        // select one link by the prime id
        if ($id <> 0) {
            if ($this->usr_query or $this->join <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $result .= $this->id_field . " = " . $id;
            // select one link by the from and to id
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
        } elseif ($id_from <> 0) {
            if ($this->id_from_field == '') {
                log_err('Internal error: to find a ' . $this->type . ' the from field must be defined', 'sql->set_where_link');
            } else {
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $result .= $this->id_from_field . ' = ' . $id_from;
            }
        } elseif ($id_to <> 0) {
            if ($this->id_to_field == '') {
                log_err('Internal error: to find a ' . $this->type . ' the to field must be defined', 'sql->set_where_link');
            } else {
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $result .= $this->id_to_field . ' = ' . $id_to;
            }
        } else {
            log_err('Internal error: to find a ' . $this->type . ' the a field must be defined', 'sql->set_where_link');
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
    function get_where(): string
    {
        return $this->where;
    }

// set the where statement for a later call of the select function
// mainly used to overwrite the for special cases, where the set_where function cannot be used
// TODO prevent code injections
    function set_where_text($where_text)
    {
        $this->where = ' WHERE ' . $where_text;
    }

// create the SQL part for the selected fields
    private
    function sql_usr_fields($usr_field_lst)
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
            if ($this->usr_query and $this->usr_join_query) {
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
    function select($has_id = true): string
    {
        $sql = 'SELECT';
        $this->set_field_statement($has_id);
        $this->set_from();
        $sql .= $this->fields . $this->from . $this->join . $this->where . ';';
        return $sql;
    }

    // return all database ids, where the owner is not yet set
    function missing_owner()
    {
        log_debug("sql_db->missing_owner (" . $this->type . ")");
        $result = null;

        $this->set_table();
        $this->set_id_field();
        $sql = "SELECT " . $this->id_field . " AS id
              FROM " . $this->name_sql_esc($this->table) . "
             WHERE user_id IS NULL;";

        return $this->get($sql);
    }

    // return all database ids, where the owner is not yet set
    function set_default_owner()
    {
        log_debug("sql_db->set_default_owner (" . $this->type . ")");

        $this->set_table();
        $sql = "UPDATE " . $this->name_sql_esc($this->table) . "
               SET user_id = 1
             WHERE user_id IS NULL;";

        //return $this->exe($sql, 'user_default', array());
        return $this->exe($sql, '', array());
    }

    /*

      technical function to finally update data in the MySQL database

    */

    // insert a new record in the database
    // similar to exe, but returning the row id added to be able to update e.g. the log entry with the row id of the real row added
    // writing the changes to the log table for history rollback is done at the calling function also because zu_log also uses this function
    // TODO include the data retrieval part for creating this insert statement into the transaction statement
    // add the return type (allowed since php version 7.0
    // if $log_err is false, no further errors will reported to prevent endless looping from the error logging itself
    function insert($fields, $values, $log_err = true)
    {
        $result = 0;
        $sql = '';
        $this->set_table();

        if (is_array($fields)) {
            log_debug('sql_db->insert into "' . $this->type . '" SET "' . implode('","', $fields) . '" WITH "' . implode('","', $values) . '" for user ' . $this->usr_id);
            if (count($fields) <> count($values)) {
                if ($log_err) {
                    log_fatal('MySQL insert call with different number of fields (' . count($fields) . ': ' . implode(',', $fields) . ') and values (' . count($values) . ': ' . implode(',', $values) . ').', "user_log->add");
                }
            } else {
                foreach (array_keys($fields) as $i) {
                    $fields[$i] = $fields[$i];
                    $values[$i] = $this->sf($values[$i]);
                }
                $sql = 'INSERT INTO ' . $this->name_sql_esc($this->table) . ' (' . implode(',', $fields) . ') 
                                      VALUES (' . implode(',', $values) . ')';
            }
        } else {
            log_debug('sql_db->insert into "' . $this->type . '" SET "' . $fields . '" WITH "' . $values . '" for user ' . $this->usr_id);
            $sql = 'INSERT INTO ' . $this->name_sql_esc($this->table) . ' (' . $fields . ') 
                                 VALUES (' . $this->sf($values) . ')';
        }

        if ($sql <> '') {
            if ($this->link == null) {
                if ($log_err) {
                    log_err('Database connection lost', 'insert');
                }
            } else {
                if ($this->db_type == DB_TYPE_POSTGRES) {
                    $sql = $sql . ' RETURNING ' . $this->id_field . ';';

                    /*
                    try {
                        $stmt = $this->link->prepare($sql);
                        $this->link->beginTransaction();
                        $stmt->execute();
                        $this->link->commit();
                        $result = $this->link->lastInsertId();
                        log_debug('sql_db->insert -> done "' . $result . '"');
                    } catch (PDOExecption $e) {
                        $this->link->rollback();
                        log_debug('sql_db->insert -> failed (' . $sql . ')');
                    }
                    */
                    //$sql_result = $this->exe($sql);

                    // TODO catch SQL errors and report them
                    $sql_result = pg_query($this->link, $sql);
                    if ($sql_result) {
                        $sql_error = pg_result_error($sql_result);
                        if ($sql_error != '') {
                            if ($log_err) {
                                log_err('Execution of ' . $sql . ' failed due to ' . $sql_error);
                            }
                        } else {
                            $result = pg_fetch_array($sql_result)[0];
                        }
                    } else {
                        $sql_error = pg_last_error($this->link);
                        if ($log_err) {
                            log_err('Execution of ' . $sql . ' failed completely due to ' . $sql_error);
                        }
                    }

                    //if ($result == false) {                        die(pg_last_error());                    }

                } else {
                    $sql = $sql . ';';
                    //$sql_result = $this->exe($sql, 'insert_' . $this->name_sql_esc($this->table), array(), DBL_SYSLOG_FATAL_ERROR);
                    $sql_result = $this->exe($sql, '', array(), DBL_SYSLOG_FATAL_ERROR);
                    if ($sql_result) {
                        $result = mysqli_insert_id();
                        log_debug('sql_db->insert -> done "' . $result . '"');
                    } else {
                        $result = -1;
                        log_debug('sql_db->insert -> failed (' . $sql . ')');
                    }
                }
            }
        } else {
            $result = -1;
            log_debug('sql_db->insert -> failed (' . $sql . ')');
        }

        return $result;
    }


// add a new unique text to the database and return the id (similar to get_id)
    function add_id($name)
    {
        log_debug('sql_db->add_id ' . $name . ' to ' . $this->type);

        $this->set_table();
        $this->set_name_field();
        $result = $this->insert($this->name_field, $this->sf($name));

        log_debug('sql_db->add_id is "' . $result . '"');
        return $result;
    }

// similar to zu_sql_add_id, but using a second ID field
    function add_id_2key($name, $field2_name, $field2_value)
    {
        log_debug('sql_db->add_id_2key ' . $name . ',' . $field2_name . ',' . $field2_value . ' to ' . $this->type);

        $this->set_table();
        $this->set_name_field();
        //zu_debug('sql_db->add_id_2key add "'.$this->name_field.','.$field2_name.'" "'.$name.','.$field2_value.'"');
        $result = $this->insert(array($this->name_field, $field2_name), array($name, $field2_value));

        log_debug('sql_db->add_id_2key is "' . $result . '"');
        return $result;
    }

    // update some values in a table
    // $id is the primary id of the db table or an array with the ids of the primary keys
    // return false if the update has failed (and the error messages are logged)
    function update($id, $fields, $values): bool
    {
        global $debug;

        log_debug('sql_db->update of ' . $this->type . ' row ' . dsp_var($id) . ' ' . dsp_var($fields) . ' with "' . dsp_var($values) . '" for user ' . $this->usr_id);

        $result = true;

        // check parameter
        $par_ok = true;
        $this->set_table();
        $this->set_id_field();
        if ($debug > 0) {
            if ($this->table == "") {
                log_err("Table not valid for " . $fields . " at " . dsp_var($id) . ".", "zu_sql_update", (new Exception)->getTraceAsString());
                $par_ok = false;
            }
            if ($values === "") {
                log_err("Values missing for " . $fields . " in " . $this->table . ".", "zu_sql_update", (new Exception)->getTraceAsString());
                $par_ok = false;
            }
        }

        // set the where clause user sandbox? ('.substr($this->type,0,4).')');
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
            log_debug('sql_db->update sql "' . $sql . '"');
            //$result = $this->exe($sql, 'update_' . $this->name_sql_esc($this->table), array(), DBL_SYSLOG_FATAL_ERROR);
            $sql_result = $this->exe($sql, '', array(), DBL_SYSLOG_FATAL_ERROR);
            if (!$sql_result) {
                $result = false;
            }
        }

        log_debug('sql_db->update -> done (' . $result . ')');
        return $result;
    }


    function update_name($id, $name): bool
    {
        $this->set_name_field();
        return $this->update($id, $this->name_field, $name);
    }

    // call the MySQL delete action
    // returns false if the deletion has failed
    function delete($id_fields, $id_values): bool
    {
        if (is_array($id_fields)) {
            log_debug('sql_db->delete in "' . $this->type . '" WHERE "' . implode(",", $id_fields) . '" IS "' . implode(",", $id_values) . '" for user ' . $this->usr_id);
        } else {
            log_debug('sql_db->delete in "' . $this->type . '" WHERE "' . $id_fields . '" IS "' . $id_values . '" for user ' . $this->usr_id);

        }
        $result = false;

        $this->set_table();

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

        log_debug('sql_db->delete sql "' . $sql . '"');
        //$sql_result = $this->exe($sql, 'delete_' . $this->name_sql_esc($this->table), array(), DBL_SYSLOG_FATAL_ERROR);
        $sql_result = $this->exe($sql, '', array(), DBL_SYSLOG_FATAL_ERROR);
        if ($sql_result) {
            $result = true;
            log_debug('sql_db->delete -> done "' . $result . '"');
        } else {
            log_debug('sql_db->delete -> failed (' . $sql . ')');
        }

        return $result;
    }

    /*

      list functions to finally get data from the MySQL database

    */

    // load all types of a type/table at once
    function load_types($table, $additional_field_lst)
    {
        log_debug('sql_db->load_types');

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
        $result = $this->get($sql);

        log_debug('sql_db->load_types -> got ' . count($result));
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

    // Sql Format: format a value for a SQL statement
    // $field_value is the value that should be formatted
    // $force_type can be set to force the formatting e.g. for the time word 2021 to use '2021'
    // outside this module it should only be used to format queries that are not yet using the abstract form for all databases (MySQL, MariaSQL, Casandra, Droid)
    // TODO define where to prevent code injections: here?
    function sf($field_value, $forced_format = '')
    {
        if ($this->db_type == DB_TYPE_POSTGRES) {
            $result = $this->postgres_format($field_value, $forced_format);
        } else {
            $result = $this->mysqli_format($field_value, $forced_format);
        }
        return $result;
    }

// formats one value for the PostgreSQL statement
    function postgres_format($field_value, $forced_format)
    {
        $result = $field_value;

        // add the formatting for the sql statement
        if (trim($result) == "") {
            $result = "NULL";
        } else {
            if ($forced_format == sql_db::FLD_FORMAT_VAL) {
                if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
                    $result = substr($result, 1, -1);
                }
            } elseif ($forced_format == sql_db::FLD_FORMAT_TEXT or !is_numeric($result)) {

                // escape the text value for PostgreSQL
                $result = pg_escape_string($result);
                //$result = pg_real_escape_string($result);

                // undo the double high quote escape char, because this is not needed if the string is capsuled by single high quote
                $result = str_replace('\"', '"', $result);
                $result = "'" . $result . "'";
            } else {
                $result = strval($result);
            }
        }
        log_debug("sql_db->postgres_format -> done (" . $result . ")");

        return $result;
    }

    // formats one value for the MySQL statement
    function mysqli_format($field_value, $forced_format)
    {
        global $db_con;

        $result = $field_value;

        // add the formatting for the sql statement
        if (trim($result) == "") {
            $result = "NULL";
        } else {
            if ($forced_format == sql_db::FLD_FORMAT_VAL) {
                if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
                    $result = substr($result, 1, -1);
                }
            } elseif ($forced_format == sql_db::FLD_FORMAT_TEXT or !is_numeric($result)) {

                // escape the text value for MySQL
                if ($db_con->link == null) {
                    $result = $this->sql_escape($result);
                } else {
                    $result = mysqli_real_escape_string($db_con, $result);
                }

                // undo the double high quote escape char, because this is not needed if the string is capsuled by single high quote
                $result = str_replace('\"', '"', $result);
                $result = "'" . $result . "'";
            } else {
                $result = strval($result);
            }
        }

        // exceptions
        if ($result == "'Now()'") {
            $result = "Now()";
        }

        log_debug("mysqli_format -> done (" . $result . ")");

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

    // reset the seq number
    function seq_reset($type): string
    {
        $msg = '';
        $this->set_type($type);
        $sql_max = 'SELECT MAX(' . $this->name_sql_esc($this->id_field) . ') AS max_id FROM ' . $this->name_sql_esc($this->table) . ';';
        // $db_con->set_fields(array('MAX(value_id) AS max_id'));
        // $sql_max = $db_con->select();
        $max_row = $this->get1($sql_max);
        if ($max_row == null) {
            log_warning('Cannot get the max of values', 'test_cleanup->value_reset');
        } else {
            if ($max_row['max_id'] > 0) {
                $next_id = $max_row['max_id'] + 1;
                if ($this->db_type == DB_TYPE_POSTGRES) {
                    $seq_name = $this->table . '_' . $this->id_field . '_seq';
                    $sql = 'ALTER SEQUENCE ' . $seq_name . ' RESTART ' . $next_id . ';';
                    $this->exe($sql);
                } elseif ($this->db_type == DB_TYPE_MYSQL) {
                    $sql = 'ALTER TABLE ' . $this->name_sql_esc($this->table) . ' auto_increment = ' . $next_id . ';';
                    $this->exe($sql);
                    $msg = 'Next database id for ' . $this->table . ': ' . $next_id;
                } else {
                    log_err('Unexpected SQL type ' . $type);
                }

            }
        }
        return $msg;
    }
}


/*

  name shortcuts - rename some often used functions to make to code look nicer and not draw the focus away from the important part
  --------------

*/

// Sql Format: format a value for a SQL statement
// $field_value is the value that should be formatted
// $force_type can be set to force the formatting e.g. for the time word 2021 to use '2021'
// outside this module it should only be used to format queries that are not yet using the abstract form for all databases (MySQL, MariaSQL, Casandra, Droid)
// TODO only use the function inside the sql_db class
function sf($field_value, $force_type = '')
{
    global $db_con;

    $result = $field_value;
    if ($db_con->db_type == DB_TYPE_POSTGRES) {
        $result = $db_con->postgres_format($result, $force_type);
    } elseif ($db_con->db_type == DB_TYPE_POSTGRES) {
        $result = $db_con->mysqli_format($result, $force_type);
    } else {
        log_err('Unknown database type ' . $db_con->db_type);
    }
    return $result;
}


// SQL list: create a query string for the standard list
// e.g. the type "source" creates the SQL statement "SELECT source_id, source_name FROM sources ORDER BY source_name;"
function sql_lst($type): string
{
    global $db_con;
    $db_con->set_type($type);
    return $db_con->sql_std_lst();
}

// similar to "sql_lst", but taking the user sandbox into account
function sql_lst_usr($type, $usr): string
{
    global $db_con;
    $db_con->set_type($type);
    $db_con->usr_id = $usr->id;
    return $db_con->sql_std_lst_usr();
}
