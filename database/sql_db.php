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
// TODO mainly for data from the internet use prepared statements to prevent SQL injections


class sql_db
{

    // these databases can be used at the moment
    const POSTGRES = "PostgreSQL";
    const MYSQL = "MySQL";

    // reserved words that are automatically escaped

    // based on https://www.postgresql.org/docs/current/sql-keywords-appendix.html from 2021-06-13
    const POSTGRES_RESERVED_NAMES = ['AND ', 'ANY ', 'ARRAY ', 'AS ', 'ASC ', 'ASYMMETRIC ', 'BOTH ', 'CASE ', 'CAST ', 'CHECK ', 'COLLATE ', 'COLUMN ', 'CONSTRAINT ', 'CREATE ', 'CURRENT_CATALOG ', 'CURRENT_DATE ', 'CURRENT_ROLE ', 'CURRENT_TIME ', 'CURRENT_TIMESTAMP ', 'CURRENT_USER ', 'DEFAULT ', 'DEFERRABLE ', 'DESC ', 'DISTINCT ', 'DO ', 'ELSE ', 'END ', 'EXCEPT ', 'FALSE ', 'FETCH ', 'FOR ', 'FOREIGN ', 'FROM ', 'GRANT ', 'GROUP ', 'HAVING ', 'IN ', 'INITIALLY ', 'INTERSECT ', 'INTO ', 'LATERAL ', 'LEADING ', 'LIMIT ', 'LOCALTIME ', 'LOCALTIMESTAMP ', 'NOT ', 'NULL ', 'OFFSET ', 'ON ', 'ONLY ', 'OR ', 'ORDER ', 'PLACING ', 'PRIMARY ', 'REFERENCES ', 'RETURNING ', 'SELECT ', 'SESSION_USER ', 'SOME ', 'SYMMETRIC ', 'TABLE ', 'THEN ', 'TO ', 'TRAILING ', 'TRUE ', 'UNION ', 'UNIQUE ', 'USER ', 'USING ', 'VARIADIC ', 'WHEN ', 'WHERE ', 'WINDOW ', 'WITH ',];
    // extra names for backward compatibility
    const POSTGRES_RESERVED_NAMES_EXTRA = ['USER'];

    // Based on MySQL version 8
    const MYSQL_RESERVED_NAMES = ['ACCESSIBLE', 'ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE', 'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BY', 'CALL', 'CASCADE', 'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION', 'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE', 'CROSS', 'CUME_DIST', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'DATABASE', 'DATABASES', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DELAYED', 'DELETE', 'DENSE_RANK', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW', 'DIV', 'DOUBLE', 'DROP', 'DUAL', 'EACH', 'ELSE', 'ELSEIF', 'EMPTY', 'ENCLOSED', 'ESCAPED', 'EXCEPT', 'EXCEPT', 'EXISTS', 'EXIT', 'EXPLAIN', 'FALSE', 'FETCH', 'FIRST_VALUE', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR', 'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 'GENERATED', 'GET', 'GRANT', 'GROUP', 'GROUPING', 'GROUPS', 'HAVING', 'HIGH_PRIORITY', 'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IF', 'IGNORE', 'IN', 'INDEX', 'INFILE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INT', 'INT1', 'INT2', 'INT3', 'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'IO_AFTER_GTIDS', 'IO_BEFORE_GTIDS', 'IS', 'ITERATE', 'JOIN', 'JSON_TABLE', 'KEY', 'KEYS', 'KILL', 'LAG', 'LAST_VALUE', 'LATERAL', 'LEAD', 'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY', 'MASTER_BIND', 'MASTER_SSL_VERIFY_SERVER_CERT', 'MATCH', 'MAXVALUE', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL', 'NO_WRITE_TO_BINLOG', 'NOT', 'NTH_VALUE', 'NTILE', 'NULL', 'NUMERIC', 'OF', 'ON', 'OPTIMIZE', 'OPTIMIZER_COSTS', 'OPTION', 'OPTIONALLY', 'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'OVER', 'PARTITION', 'PERCENT_RANK', 'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE', 'RANGE', 'RANK', 'READ', 'READ_WRITE', 'READS', 'REAL', 'RECURSIVE', 'REFERENCES', 'REGEXP', 'RELEASE', 'RENAME', 'REPEAT', 'REPLACE', 'REQUIRE', 'RESIGNAL', 'RESTRICT', 'RETURN', 'REVOKE', 'RIGHT', 'RLIKE', 'ROW_NUMBER', 'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE', 'SEPARATOR', 'SET', 'SHOW', 'SIGNAL', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SSL', 'STARTING', 'STORED', 'STRAIGHT_JOIN', 'SYSTEM', 'TABLE', 'TERMINATED', 'THEN', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE', 'UNDO', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING', 'VIRTUAL', 'WHEN', 'WHERE', 'WHILE', 'WINDOW', 'WITH', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL'];
    // extra names for backward compatibility
    const MYSQL_RESERVED_NAMES_EXTRA = ['VALUE', 'VALUES', 'URL'];

    // tables that does not have a name e.g. DB_TYPE_WORD_LINK is a link, but is nevertheless named
    const DB_TYPES_NOT_NAMED = [DB_TYPE_VALUE, DB_TYPE_FORMULA_LINK, DB_TYPE_VIEW_COMPONENT_LINK, DB_TYPE_REF, DB_TYPE_IP, DB_TYPE_SYS_LOG];
    // tables that link two named tables
    // TODO set automatically by set_link_fields???
    const DB_TYPES_LINK = [DB_TYPE_WORD_LINK, DB_TYPE_FORMULA_LINK, DB_TYPE_VIEW_COMPONENT_LINK, DB_TYPE_REF];

    const NULL_VALUE = 'NULL';

    const FLD_EXT_ID = '_id';
    const FLD_EXT_NAME = '_name';

    const USER_PREFIX = "user_";                  // prefix used for tables where the user sandbox values are stored

    const STD_TBL = "s";                          // prefix used for the standard table where data for all users are stored
    const USR_TBL = "u";                          // prefix used for the standard table where the user sandbox data is stored
    const LNK_TBL = "l";                          // prefix used for the table which should be joined in the result
    const LNK2_TBL = "l2";                        // prefix used for the second table which should be joined in the result
    const ULK_TBL = "c";                          // prefix used for the table which should be joined in the result of the user sandbox data

    const FLD_CODE_ID = "code_id";                // field name for the code link
    const FLD_USER_ID = "user_id";                // field name for the user table foreign key field
    const FLD_VALUE = "value";                    // field name e.g. for the configuration value
    const FLD_DESCRIPTION = "description";        // field name for the any description
    const FLD_TYPE_NAME = "type_name";            // field name for the user specific name of a type; types are used to assign code to a db row
    const FLD_SHARE = "share_type_id";            // field name for the share permission
    const FLD_PROTECT = "protection_type_id";     // field name for the protection level

    // formats to force the formatting of a value for an SQL statement e.g. convert true to 1 when using tinyint to save boolean values
    const FLD_FORMAT_TEXT = "text";               // to force the text formatting of a value for the SQL statement formatting
    const FLD_FORMAT_VAL = "number";              // to force the numeric formatting of a value for the SQL statement formatting
    const FLD_FORMAT_BOOL = "boolean";            // to force the boolean formatting of a value for the SQL statement formatting

    /*
     * object variables
     */

    public ?string $db_type = null;               // the database type which should be used for this connection e.g. postgreSQL or MYSQL
    public $postgres_link;                        // the link object to the database
    public mysqli $mysql;                         // the MySQL object to the database
    public ?int $usr_id = null;                   // the user id of the person who request the database changes
    private ?int $usr_view_id = null;             // the user id of the person which values should be returned e.g. an admin might want to check the data of an user

    private ?string $type = '';                   // based of this database object type the table name and the standard fields are defined e.g. for type "word" the field "word_name" is used
    private ?string $table = '';                  // name of the table that is used for the next query
    private ?string $id_field = '';               // primary key field of the table used
    private ?string $id_from_field = '';          // only for link objects the id field of the source object
    private ?string $id_to_field = '';            // only for link objects the id field of the destination object
    private ?string $id_link_field = '';          // only for link objects the id field of the link type object
    private ?string $name_field = '';             // unique text key field of the table used
    private ?array $field_lst = [];               // list of fields that should be returned to the next select query
    private ?array $usr_field_lst = [];           // list of user specific fields that should be returned to the next select query
    private ?array $usr_num_field_lst = [];       // list of user specific numeric fields that should be returned to the next select query
    private ?array $usr_bool_field_lst = [];      // list of user specific boolean / tinyint fields that should be returned to the next select query
    private ?array $usr_only_field_lst = [];      // list of fields that are only in the user sandbox
    private ?array $join_field_lst = [];          // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_field_lst = [];         // same as $join_field_lst but for the second join
    private ?array $join_usr_field_lst = [];      // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_usr_field_lst = [];     // same as $join_usr_field_lst but for the second join
    private ?array $join_usr_num_field_lst = [];  // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_usr_num_field_lst = []; // same as $join_usr_num_field_lst but for the second join
    private ?string $join_type = '';              // the type name of the table to join
    private ?string $join2_type = '';             // the type name of the second table to join (maybe later switch to join n tables)
    private bool $usr_query = false;              // true, if the query is expected to retrieve user specific data
    private bool $join_usr_query = false;         // true, if the joined query is also expected to retrieve user specific data
    private bool $join2_usr_query = false;        // same as $usr_join_query but for the second join
    private bool $usr_only_query = false;         // true, if the query is expected to retrieve ONLY the user specific data without the standard values

    private ?string $fields = '';                 // the fields                SQL statement that is used for the next select query
    private ?string $from = '';                   // the FROM                  SQL statement that is used for the next select query
    private ?string $join = '';                   // the JOIN                  SQL statement that is used for the next select query
    private ?string $where = '';                  // the WHERE condition as an SQL statement that is used for the next select query
    private ?string $order = '';                  // the WHERE condition as an SQL statement that is used for the next select query

    private ?array $prepared_sql_names = [];      // list of all SQL queries that have already been prepared during the open connection
    private ?array $prepared_stmt = [];           // list of the MySQL stmt

    /*
     * set up the environment
     */

    /**
     * reset the previous settings
     */
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
        $this->join2_field_lst = [];
        $this->join_usr_field_lst = [];
        $this->join2_usr_field_lst = [];
        $this->join_usr_num_field_lst = [];
        $this->join2_usr_num_field_lst = [];
        $this->join_type = '';
        $this->join2_type = '';
        $this->join_usr_query = false;
        $this->join2_usr_query = false;
        $this->usr_query = false;
        $this->usr_only_query = false;
        $this->fields = '';
        $this->from = '';
        $this->join = '';
        $this->where = '';
        $this->order = '';
    }

    /*
     * open/close the connection to MySQL
     */

    /**
     * open the database link
     */
    function open()
    {
        log_debug("db->open");

        if ($this->db_type == sql_db::POSTGRES) {
            $this->postgres_link = pg_connect('host=localhost dbname=zukunft user=' . SQL_DB_USER . ' password=' . SQL_DB_PASSWD);
        } elseif ($this->db_type == sql_db::MYSQL) {
            $this->mysql = mysqli_connect('localhost', SQL_DB_USER, SQL_DB_PASSWD, 'zukunft') or die('Could not connect: ' . mysqli_error($this->mysql));
        } else {
            log_err('Database type ' . $this->db_type . ' not yet implemented');
        }

        log_debug("sql_db->open -> done");
        return true;
    }

    /**
     * just to have all sql in one library
     */
    function close()
    {
        if ($this->db_type == sql_db::POSTGRES) {
            if ($this->postgres_link != null) {
                pg_close($this->postgres_link);
                $this->postgres_link = null;
            }
        } elseif ($this->db_type == sql_db::MYSQL) {
            if ($this->mysql != null) {
                mysqli_close($this->mysql);
                //$this->mysql = null;
            }
        } else {
            log_err('Database type ' . $this->db_type . ' not yet implemented');
        }


        log_debug("db->close -> done");
    }

    /*
     * basic interface function for the private class parameter
     */

    /**
     * define the table that should be used for the next select, insert, update or delete statement
     * resets all previous db query settings such as fields, user_fields, so this should be the first statement when defining a database query
     * TODO check that this is always called directly before the query is created, so that
     *
     * @param string $type is a string that is used to select the table name, the id field and the name field
     * @param bool $usr_table if it is true the user table instead of the standard table is used
     * @return bool true if setting the type was successful
     */
    function set_type(string $type, bool $usr_table = false): bool
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

    /**
     * set the user id of the user who has requested the database access
     * by default the user also should see his/her/its data
     */
    function set_usr(int $usr_id)
    {
        $this->usr_id = $usr_id;
        $this->usr_view_id = $usr_id;
    }

    /**
     * to change the user view independent of the session user (can only be called by admin users)
     */
    function set_view_usr($usr_id)
    {
        $this->usr_view_id = $usr_id;
    }

    /**
     * define the fields that should be returned in a select query
     */
    function set_fields($field_lst)
    {
        $this->field_lst = $field_lst;
    }

    /**
     * define the fields that are used to link two objects
     * the id_link_field is the type e.g. the verb for a word link
     */
    function set_link_fields($id_from_field, $id_to_field, $id_link_field = '')
    {
        $this->id_from_field = $id_from_field;
        $this->id_to_field = $id_to_field;
        $this->id_link_field = $id_link_field;
    }

    /**
     * add a list of fields to the result that are taken from another table
     * must be set AFTER the set_usr_fields, set_usr_num_fields, set_usr_bool_fields, set_usr_bool_fields or set_usr_only_fields for correct handling of $this->usr_join_query
     * @param array $join_field_lst are the field names that should be included in the result
     * @param string $join_type is the table from where the fields should be taken; use the type name, not the table name
     * @param string $join_field is the index field that should be used for the join that must exist in both tables, default is the id of the joined table
     *                           if empty the field will be guessed
     */
    function set_join_fields(array $join_field_lst, string $join_type, $join_field = '')
    {
        if ($this->join_type == '') {
            $this->join_type = $join_type;
            $this->join_field_lst = $join_field_lst;
            $this->join_usr_query = false;
        } elseif ($this->join2_type == '') {
            $this->join2_type = $join_type;
            $this->join2_field_lst = $join_field_lst;
            $this->join2_usr_query = false;
        } else {
            log_err('Max two table joins expected on version ' . PRG_VERSION);
        }
    }

    /**
     * similar to set_join_fields but for usr specific fields
     */
    function set_join_usr_fields($join_field_lst, $join_type, $join_field = '')
    {
        if ($this->join_type == '') {
            $this->join_type = $join_type;
            $this->join_usr_field_lst = $join_field_lst;
            $this->join_usr_query = true;
        } elseif ($this->join2_type == '') {
            $this->join2_type = $join_type;
            $this->join2_usr_field_lst = $join_field_lst;
            $this->join2_usr_query = true;
        } else {
            log_err('Max two table joins expected on version ' . PRG_VERSION);
        }
    }

    function set_join_usr_num_fields($join_field_lst, $join_type, $join_field = '')
    {
        if ($this->join_type == '') {
            $this->join_type = $join_type;
            $this->join_usr_num_field_lst = $join_field_lst;
            $this->join_usr_query = true;
        } elseif ($this->join2_type == '') {
            $this->join2_type = $join_type;
            $this->join2_usr_num_field_lst = $join_field_lst;
            $this->join2_usr_query = true;
        } else {
            log_err('Max two table joins expected on version ' . PRG_VERSION);
        }
    }

    /**
     * set the SQL statement for the user sandbox fields that should be returned in a select query which can be user specific
     */
    function set_usr_fields($usr_field_lst)
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_field_lst = $usr_field_lst;
    }

    function set_usr_num_fields($usr_field_lst)
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_num_field_lst = $usr_field_lst;
    }

    function set_usr_bool_fields($usr_field_lst)
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_bool_field_lst = $usr_field_lst;
    }

    function set_usr_only_fields($field_lst)
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_only_field_lst = $field_lst;
    }

    private function set_field_sep()
    {
        if ($this->fields != '') {
            $this->fields .= ', ';
        }
    }

    /**
     * interface function for sql_usr_field
     */
    function get_usr_field($field, $stb_tbl = sql_db::STD_TBL, $usr_tbl = sql_db::USR_TBL, $field_format = sql_db::FLD_FORMAT_TEXT, $as = ''): string
    {
        return $this->sql_usr_field($field, $field_format, $stb_tbl, $usr_tbl, $as);
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and text fields
     */
    private function set_field_usr_text($field, $stb_tbl = sql_db::STD_TBL, $usr_tbl = sql_db::USR_TBL)
    {
        $this->fields .= $this->sql_usr_field($field, sql_db::FLD_FORMAT_TEXT, $stb_tbl, $usr_tbl);
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and number fields
     */
    private function set_field_usr_num($field)
    {
        $this->fields .= $this->sql_usr_field($field, sql_db::FLD_FORMAT_VAL, sql_db::STD_TBL, sql_db::USR_TBL);
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and boolean / tinyint fields
     */
    private function set_field_usr_bool($field)
    {
        $this->fields .= $this->sql_usr_field($field, sql_db::FLD_FORMAT_BOOL, sql_db::STD_TBL, sql_db::USR_TBL);
    }

    /**
     * return the SQL statement for a field taken from the user sandbox table or from the table with the common values
     * $db_type is the SQL database type which is in this case independent of the class setting to be able to use it anywhere
     */
    private function sql_usr_field($field, $field_format, $stb_tbl, $usr_tbl, $as = ''): string
    {
        $result = '';
        if ($as == '') {
            $as = $field;
        }
        if ($this->db_type == sql_db::POSTGRES) {
            if ($field_format == sql_db::FLD_FORMAT_TEXT) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " <> '' IS NOT TRUE) THEN " . $stb_tbl . "." . $field . " ELSE " . $usr_tbl . "." . $field . " END AS " . $as;
            } elseif ($field_format == sql_db::FLD_FORMAT_VAL) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " IS NULL) THEN " . $stb_tbl . "." . $field . " ELSE " . $usr_tbl . "." . $field . " END AS " . $as;
            } elseif ($field_format == sql_db::FLD_FORMAT_BOOL) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " IS NULL) THEN COALESCE(" . $stb_tbl . "." . $field . ",0) ELSE COALESCE(" . $usr_tbl . "." . $field . ",0) END AS " . $as;
            } else {
                log_err('Unexpected field format ' . $field_format);
            }
        } elseif ($this->db_type == sql_db::MYSQL) {
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
                // allow also using the set_fields method for link fields e.g. for more complex where cases
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

        // add normal fields
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

        // add join fields
        foreach ($this->join_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->fields .= ' ' . sql_db::LNK_TBL . '.' . $field;
            if ($this->usr_query and $this->join_usr_query) {
                if ($this->fields != '') {
                    $this->fields .= ', ';
                }
                $this->fields .= ' ' . sql_db::ULK_TBL . '.' . $field;
            }
        }

        // add second join fields
        foreach ($this->join2_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->fields .= ' ' . sql_db::LNK2_TBL . '.' . $field;
            if ($this->usr_query and $this->join2_usr_query) {
                if ($this->fields != '') {
                    $this->fields .= ', ';
                }
                $this->fields .= ' ' . sql_db::ULK_TBL . '.' . $field;
            }
        }

        // add user specific fields
        foreach ($this->usr_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_text($field);
        }

        // add user specific numeric fields
        foreach ($this->usr_num_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_num($field);
        }

        // add user specific boolean fields
        foreach ($this->usr_bool_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_bool($field);
        }

        // add user specific join fields
        foreach ($this->join_usr_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_text($field, sql_db::LNK_TBL, sql_db::ULK_TBL);
        }

        // add user specific second join fields
        foreach ($this->join2_usr_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_text($field, sql_db::LNK_TBL, sql_db::ULK_TBL);
        }

        // add user specific numeric join fields
        foreach ($this->join_usr_num_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_num($field);
        }

        // add user specific numeric second join fields
        foreach ($this->join2_usr_num_field_lst as $field) {
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
      for all tables some standard fields such as "word_name" are used
      the function below set the standard fields based on the "table/type"
    */

    /**
     * functions for the standard naming of tables
     */
    function get_table_name($type): string
    {
        // set the standard table name based on the type
        $result = $type . "s";
        // exceptions from the standard table for 'nicer' names
        if ($result == 'value_time_seriess') {
            $result = 'value_time_series';
        }
        if ($result == 'value_ts_datas') {
            $result = 'value_ts_data';
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
        if ($result == 'user_valuess') {
            $result = 'user_values';
        }
        // for the database upgrade process only
        if ($result == 'calc_and_cleanup_task_typess') {
            $result = 'calc_and_cleanup_task_types';
        }
        if ($result == 'view_component_typess') {
            $result = 'view_component_types';
        }
        if ($result == 'user_profiless') {
            $result = 'user_profiles';
        }
        if ($result == 'verbss') {
            $result = 'verbs';
        }
        if ($result == 'viewss') {
            $result = 'views';
        }
        /*
        if ($this->db_type == self::MYSQL) {
            if ($result == 'values') {
                $result = '`values`';
            }
        }*/
        return $result;
    }

    /**
     * similar to get_table_name, but for direct use in sql statements
     */
    function get_table_name_esc($type): string
    {
        return $this->name_sql_esc($this->get_table_name($type));
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

    public function get_id_field_name($type): string
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
        if ($result == 'blocked_ip_id') {
            $result = 'user_blocked_id';
        }
        return $result;
    }

    private function set_id_field(string $given_name = '')
    {
        if ($given_name != '') {
            $this->id_field = $given_name;
        } else {
            $this->id_field = $this->get_id_field_name($this->type);
        }
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
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'word_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'view_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'view_component_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'formula_element_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'sys_log_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'formula_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'formula_link_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'ref_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'share_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'protection_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'profile_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'sys_log_status_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        if ($result == 'calc_and_cleanup_task_type_name') {
            $result = sql_db::FLD_TYPE_NAME;
        }
        log_debug("sql_db->set_name_field to (" . $result . ")");
        $this->name_field = $result;
    }

    /*
     * the main database call function including an automatic error tracking
     * this function should probably be private and not be called from another class
     * instead the function get, insert and update function below should be called
     */

    /**
     * execute an SQL statement on the active database (either PostgreSQL or MySQL)
     *
     * @param string $msg the description of the task that is executed
     * @param string $sql the sql statement that should be executed
     * @param string $sql_name the unique name of the sql statement
     * @param array $sql_array the values that should be used for executing the precompiled SQL statement
     * @param int $log_level the log level is given by the calling function because after some errors the program may nevertheless continue
     * @return string the message that should be shown to the user if something went wrong or an empty string
     */
    function exe_try(string $msg, string $sql, string $sql_name = '', array $sql_array = array(), int $log_level = sys_log_level::ERROR): string
    {
        $result = '';
        try {
            $sql_result = $this->exe($sql, $sql_name, $sql_array, $log_level);
            if ($sql_result === false) {
                $result .= $msg . log::MSG_ERR . $sql_result;
            }
        } catch (Exception $e) {
            $trace_link = log_err($msg . log::MSG_ERR_USING . $sql . log::MSG_ERR_BECAUSE . $e->getMessage());
            $result = $msg . log::MSG_ERR_INTERNAL . $trace_link;
        }
        return $result;
    }

    /**
     * execute an change SQL statement on the active database (either PostgreSQL or MySQL)
     * similar to exe_try, but without exception handling
     *
     * @param string $sql the sql statement that should be executed
     * @param string $sql_name the unique name of the sql statement
     * @param array $sql_array the values that should be used for executing the precompiled SQL statement
     * @param int $log_level the log level is given by the calling function because after some errors the program may nevertheless continue
     * @return bool|resource the message that should be shown to the user if something went wrong or an empty string
     * @throws Exception the message that should be shown to the system admin for debugging
     *
     * TODO add the writing of potential sql errors to the sys log table to the sql execution
     * TODO includes the user to be able to ask the user for details how the error has been created
     * TODO with php 8 switch to the union return type resource|false
     */
    function exe(string $sql, string $sql_name = '', array $sql_array = array(), int $log_level = sys_log_level::ERROR)
    {
        log_debug("sql_db->exe (" . $sql . " named " . $sql_name . " for  user " . $this->usr_id . ")");

        $result = null;

        // validate the parameters
        if ($sql_name == '') {
            // TODO switch to error when all SQL statements are named
            //log_warning('Name for SQL statement ' . $sql . ' is missing');
        }

        if ($this->db_type == sql_db::POSTGRES) {
            if ($this->postgres_link == null) {
                $msg = 'database connection lost';
                log_fatal($msg, 'sql_db->exe: ' . $sql_name);
                // TODO try auto reconnect in 1, 2 4, 8, 16 ... and max 3600 sec
                throw new Exception($msg);
            } else {
                $sql = str_replace("\n", "", $sql);
                if ($sql_name == '') {
                    $result = pg_query($this->postgres_link, $sql);
                    if ($result === false) {
                        throw new Exception('Database error ' . pg_last_error($this->postgres_link) . ' when querying ' . $sql);
                    }

                } else {
                    if (!in_array($sql_name, $this->prepared_sql_names)) {
                        $result = pg_prepare($this->postgres_link, $sql_name, $sql);
                        if ($result == false) {
                            throw new Exception('Database error ' . pg_last_error($this->postgres_link) . ' when preparing ' . $sql);
                        } else {
                            $this->prepared_sql_names[] = $sql_name;
                        }
                    }
                    $result = pg_execute($this->postgres_link, $sql_name, $sql_array);
                    if ($result == false) {
                        throw new Exception('Database error ' . pg_last_error($this->postgres_link) . ' when executing ' . $sql);
                    }
                }
            }
        } elseif ($this->db_type == sql_db::MYSQL) {
            if ($this->mysql == null) {
                $msg = 'database connection lost';
                log_fatal($msg, 'sql_db->exe->' . $sql_name);
                // TODO try auto reconnect in 1, 2 4, 8, 16 ... and max 3600 sec
                throw new Exception($msg);
            } else {
                if ($sql_name == '') {
                    $result = mysqli_query($this->mysql, $sql);
                } else {
                    if (in_array($sql_name, $this->prepared_sql_names)) {
                        $stmt = $this->prepared_stmt[$sql_name];
                    } else {
                        $stmt = mysqli_prepare($this->mysql, $sql);
                        $this->prepared_sql_names[] = $sql_name;
                        $this->prepared_stmt[$sql_name] = $stmt;
                    }
                    if ($stmt == null) {
                        throw new Exception('Database error ' . pg_last_error($this->postgres_link) . ' when executing ' . $sql);
                    } else {
                        // TODO review to use a generic transformation for $sql_array
                        if (count($sql_array) == 1) {
                            $stmt->bind_param($this->mysql_array_to_types($sql_array), $sql_array[0]);
                        } elseif (count($sql_array) == 2) {
                            $stmt->bind_param($this->mysql_array_to_types($sql_array), $sql_array[0], $sql_array[1]);
                        } else {
                            throw new Exception('Unexpected number of parameters in ' . $sql);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();
                    }
                }
                if ($result === false) {
                    $msg_text = mysqli_error($this->mysql);
                    $sql = str_replace("'", "", $sql);
                    $sql = str_replace("\"", "", $sql);
                    $msg_text .= " (" . $sql . ")";
                    // check and improve the given parameters
                    $function_trace = (new Exception)->getTraceAsString();
                    // set the global db connection to be able to report error also on db restart
                    $msg = log_msg($msg_text, $msg_text . ' from ' . $sql_name, $log_level, $sql_name, $function_trace, $this->usr_id);
                    throw new Exception("sql_db->exe -> error (" . $msg . ")");
                }
            }
        } else {
            throw new Exception('Unknown database type "' . $this->db_type . '"');
        }

        return $result;
    }

    /*
      technical function to finally get data from the MySQL database
    */

    private function mysql_array_to_types(array $sql_array): string
    {
        $result = '';
        foreach ($sql_array as $value) {
            if (gettype($value) == 'integer') {
                $result .= 'i';
            } elseif (gettype($value) == 'double') {
                $result .= 'd';
            } else {
                $result .= 's';
            }
        }
        return $result;
    }

    /**
     * fetch the first value from an SQL database (either PostgreSQL or MySQL at the moment)
     *
     * @param string $sql the sql statement that should be executed
     * @param string $sql_name the unique name of the sql statement
     * @param array $sql_array the values that should be used for executing the precompiled SQL statement
     * @param bool $fetch_all true all database rows are returned at once
     * @return array with one or all database records
     */
    private function fetch(string $sql, string $sql_name = '', array $sql_array = array(), bool $fetch_all = false): ?array
    {
        $result = array();

        if ($sql <> "") {
            if ($this->db_type == sql_db::POSTGRES) {
                if ($this->postgres_link == null) {
                    log_warning('Database connection lost', 'sql_db->fetch');
                    // TODO try auto reconnect in 1, 2 4, 8, 16 ... and max 3600 sec
                } else {
                    try {
                        $exe_result = $this->exe($sql, $sql_name, $sql_array);
                        if ($fetch_all) {
                            if ($exe_result) {
                                while ($sql_row = pg_fetch_array($exe_result)) {
                                    if ($sql_row != false) {
                                        $result[] = $sql_row;
                                    }
                                }
                            }
                        } else {
                            $sql_row = pg_fetch_array($exe_result);
                            if ($sql_row != false) {
                                $result = $sql_row;
                            }
                        }
                    } catch (Exception $e) {
                        $msg = 'Select';
                        $trace_link = log_err($msg . log::MSG_ERR_USING . $sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                        $result = $msg . log::MSG_ERR_INTERNAL . $trace_link;
                    }
                }
            } elseif ($this->db_type == sql_db::MYSQL) {
                if ($this->mysql == null) {
                    log_warning('Database connection lost', 'sql_db->fetch');
                    // TODO try auto reconnect in 1, 2 4, 8, 16 ... and max 3600 sec
                } else {
                    try {
                        $exe_result = $this->exe($sql, $sql_name, $sql_array);
                        if ($fetch_all) {
                            while ($sql_row = mysqli_fetch_array($exe_result, MYSQLI_BOTH)) {
                                $result[] = $sql_row;
                            }
                        } else {
                            $result = mysqli_fetch_array($exe_result, MYSQLI_BOTH);
                        }
                    } catch (Exception $e) {
                        $msg = 'Select';
                        $trace_link = log_err($msg . log::MSG_ERR_USING . $sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                        $result = $msg . log::MSG_ERR_INTERNAL . $trace_link;
                    }
                }
            } else {
                log_err('Unknown database type "' . $this->db_type . '"', 'sql_db->fetch');
            }
        }

        return $result;
    }

    /**
     * fetch the first row from an SQL database (either PostgreSQL or MySQL at the moment)
     */
    private function fetch_first(string $sql, string $sql_name = '', array $sql_array = array()): ?array
    {
        return $this->fetch($sql, $sql_name, $sql_array);
    }

    /**
     * fetch the all value from an SQL database (either PostgreSQL or MySQL at the moment)
     */
    private function fetch_all($sql, string $sql_name = '', array $sql_array = array()): array
    {
        return $this->fetch($sql, $sql_name, $sql_array, true);
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

    /**
     * returns all values of an SQL query in an array
     */
    function get($sql): array
    {
        $this->debug_msg($sql, 'get');
        return $this->fetch_all($sql);
    }

    /**
     * get only the first record from the database
     */
    function get1($sql): ?array
    {
        $this->debug_msg($sql, 'get1');

        // optimise the sql statement
        $sql = trim($sql);
        if (strpos($sql, "LIMIT") === FALSE) {
            if (substr($sql, -1) == ";") {
                $sql = substr($sql, 0, -1) . " LIMIT 1;";
            } else {
                $sql = $sql . " LIMIT 1;";
            }
        }

        return $this->fetch_first($sql);
    }

    /**
     * returns first value of a simple SQL query
     */
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

    /**
     * similar to sql_db->get_value, but for two key fields
     */
    function get_value_2key($field_name, $id1_name, $id1, $id2_name, $id2)
    {
        $result = '';
        log_debug('sql_db->get_value_2key ' . $field_name . ' from ' . $this->type . ' where ' . $id1_name . ' = ' . $id1 . ' and ' . $id2_name . ' = ' . $id2);

        $this->set_table();
        $sql = "SELECT " . $this->name_sql_esc($field_name) .
            "     FROM " . $this->name_sql_esc($this->table);
        if ($this->db_type == self::POSTGRES) {
            $sql .= " WHERE " . $this->name_sql_esc($id1_name) . " = $1 " .
                "       AND " . $this->name_sql_esc($id2_name) . " = $2 LIMIT 1;";
        } elseif ($this->db_type == self::MYSQL) {
            $sql .= " WHERE " . $this->name_sql_esc($id1_name) . " = ? " .
                "       AND " . $this->name_sql_esc($id2_name) . " = ? LIMIT 1;";
        }
        $sql_name = 'get_' . $field_name . '_from_' . $this->table . '_where_' . $id1_name . '_and_' . $id2_name;
        $sql_array = array($id1, $id2);

        $sql_row = $this->fetch_first($sql, $sql_name, $sql_array);

        if ($sql_row != false) {
            if (count($sql_row) > 0) {
                $result = $sql_row[0];
            }
        }

        return $result;
    }

    /**
     * returns the id field of a standard table
     * which means that the table name ends with 's', the name field is the table name plus '_name' and prim index ends with '_id'
     * $name is the unique text that identifies one row e.g. for the $name "Company" the word id "1" is returned
     */
    function get_id($name): string
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

    /**
     *
     */
    function get_id_from_code($code_id): string
    {
        $result = '';
        log_debug('sql_db->get_id_from_code for "' . $code_id . '" of the db object "' . $this->type . '"');

        $this->set_table();
        $this->set_id_field();
        $result .= $this->get_value($this->id_field, self::FLD_CODE_ID, $code_id);

        log_debug('sql_db->get_id_from_code is "' . $result . '"');
        return $result;
    }

    /**
     * similar to get_id, but the other way round
     */
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

    /**
     * similar to zu_sql_get_id, but using a second ID field
     */
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

    /**
     * create a standard query for a list of database id and name while taking the user sandbox into account
     */
    function sql_std_lst_usr(): string
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
        if ($this->db_type == sql_db::POSTGRES) {
            $sql = "SELECT id, name 
              FROM ( SELECT t." . $this->id_field . " AS id, 
                            CASE WHEN (u." . $this->name_field . " <> '' IS NOT TRUE) THEN t." . $this->name_field . " ELSE u." . $this->name_field . " END AS name,
                            CASE WHEN (u.excluded                        IS     NULL) THEN     COALESCE(t.excluded, 0) ELSE COALESCE(u.excluded, 0)     END AS excluded
                      FROM " . $this->name_sql_esc($this->table) . " t       
                  LEFT JOIN user_" . str_replace("`", "", $this->table) . " u ON u." . $this->id_field . " = t." . $this->id_field . " 
                                              AND u.user_id = " . $this->usr_id . " 
                            " . $sql_where . ") AS s
            WHERE excluded <> 1                                   
          ORDER BY name;";
        } else {
            $sql = "SELECT" . " id, name 
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

    /**
     * create a standard query for a list of database id and name
     */
    function sql_std_lst(): string
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

    /**
     * set the where statement for a later call of the select function
     */
    function where($fields, $values): string
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

    /**
     * set the standard where statement to select either by id or name or code_id
     * the type must have been already set e.g. to 'source'
     * TODO check why the request user must be set to search by code_id ?
     * TODO check if test for positive and negative id values is needed; because phrases can have a negative id ?
     */
    function set_where($id, $name = '', $code_id = ''): string
    {
        $result = '';

        if ($id <> 0) {
            if ($this->usr_query
                or $this->join <> ''
                or $this->join_type <> ''
                or $this->join2_type <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $result .= $this->id_field . " = " . $id;
        } elseif ($code_id <> '' and !is_null($this->usr_id)) {
            if ($this->usr_query or $this->join <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $result .= sql_db::FLD_CODE_ID . " = " . $this->sf($code_id);
            if ($this->db_type == sql_db::POSTGRES) {
                $result .= ' AND ';
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $result .= sql_db::FLD_CODE_ID . ' != NULL';
            }
        } elseif ($name <> '' and !is_null($this->usr_id)) {
            /*
             * because the object name can be user specific,
             * don't use the standard name for the selection e.g. s.view_name
             * use instead the user specific name e.g. view_name
             */
            if ($this->usr_query or $this->join <> '') {
                $result .= '(' . sql_db::USR_TBL . '.';
                $result .= $this->name_field . " = " . $this->sf($name, sql_db::FLD_FORMAT_TEXT);
                $result .= ' OR (' . sql_db::STD_TBL . '.';
                $result .= $this->name_field . " = " . $this->sf($name, sql_db::FLD_FORMAT_TEXT);
                $result .= ' AND ' . sql_db::USR_TBL . '.';
                $result .= $this->name_field . " IS NULL))";
            } else {
                $result .= $this->name_field . " = " . $this->sf($name, sql_db::FLD_FORMAT_TEXT);
            }
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
// TODO prevent code injections e.g. by using only predefined queries
    function set_where_text($where_text)
    {
        $this->where = ' WHERE ' . $where_text;
    }

// get the order SQL statement
    function get_order(): string
    {
        return $this->order;
    }

// set the order SQL statement
    function set_order_text($order_text)
    {
        $this->order = ' ORDER BY ' . $order_text;
    }


// create the SQL part for the selected fields
    private
    function sql_usr_fields($usr_field_lst)
    {
    }

// create the from SQL statement based on the type
    private
    function set_from()
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
            if ($this->usr_query and $this->join_usr_query) {
                $this->join .= ' LEFT JOIN ' . $join_table_name . ' ' . sql_db::ULK_TBL;
                $this->join .= ' ON ' . sql_db::USR_TBL . '.' . $join_id_field . ' = ' . sql_db::ULK_TBL . '.' . $join_id_field;
            }
        }
        if ($this->join2_type <> '') {
            $join2_table_name = $this->get_table_name($this->join2_type);
            $join2_id_field = $this->get_id_field_name($this->join2_type);
            $this->join .= ' LEFT JOIN ' . $join2_table_name . ' ' . sql_db::LNK2_TBL;
            $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $join2_id_field . ' = ' . sql_db::LNK2_TBL . '.' . $join2_id_field;
            if ($this->usr_query and $this->join2_usr_query) {
                $this->join .= ' LEFT JOIN ' . $join2_table_name . ' ' . sql_db::ULK_TBL;
                $this->join .= ' ON ' . sql_db::USR_TBL . '.' . $join2_id_field . ' = ' . sql_db::ULK_TBL . '.' . $join2_id_field;
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
        $sql .= $this->fields . $this->from . $this->join . $this->where . $this->order . ';';
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
        try {
            $result = $this->exe($sql, '', array());
        } catch (Exception $e) {
            $msg = 'Select';
            $trace_link = log_err($msg . log::MSG_ERR_USING . $sql . log::MSG_ERR_BECAUSE . $e->getMessage());
            $result = $msg . log::MSG_ERR_INTERNAL . $trace_link;
        }

        return $result;
    }

    /*
      technical function to finally update data in the MySQL database
    */

    /**
     * insert a new record in the database
     * similar to exe, but returning the row id added to be able to update
     * e.g. the log entry with the row id of the real row added
     * writing the changes to the log table for history rollback is done
     * at the calling function also because zu_log also uses this function
     * TODO include the data retrieval part for creating this insert statement into the transaction statement
     *      add the return type (allowed since php version 7.0, but array|string is allowed with 8.0 or higher
     *      if $log_err is false, no further errors will reported to prevent endless looping from the error logging itself
     */
    function insert($fields, $values, bool $log_err = true): int
    {
        $result = 0;
        $is_valid = false;

        // escape the fields and values and build the SQL statement
        $this->set_table();
        $sql = 'INSERT INTO ' . $this->name_sql_esc($this->table);

        if (is_array($fields)) {
            if (count($fields) <> count($values)) {
                if ($log_err) {
                    log_fatal('MySQL insert call with different number of fields (' . dsp_count($fields) . ': ' . dsp_array($fields) . ') and values (' . dsp_count($values) . ': ' . dsp_array($values) . ').', "user_log->add");
                }
            } else {
                foreach (array_keys($fields) as $i) {
                    $fields[$i] = $this->name_sql_esc($fields[$i]);
                    $values[$i] = $this->sf($values[$i]);
                }
                $sql .= ' (' . sql_array($fields) . ')
                 VALUES (' . sql_array($values) . ')';
                $is_valid = true;
            }
        } else {
            if ($fields != '') {
                $sql .= ' (' . $this->name_sql_esc($fields) . ')
             VALUES (' . $this->sf($values) . ')';
                $is_valid = true;
            }
        }

        if ($is_valid) {
            if ($this->db_type == sql_db::POSTGRES) {
                if ($this->postgres_link == null) {
                    if ($log_err) {
                        log_err('Database connection lost', 'insert');
                    }
                } else {
                    // return the database row id if the value is not a time series number
                    if ($this->type != DB_TYPE_VALUE_TIME_SERIES_DATA) {
                        $sql = $sql . ' RETURNING ' . $this->id_field . ';';
                    }

                    /*
                    try {
                        $stmt = $this->link->prepare($sql);
                        $this->link->beginTransaction();
                        $stmt->execute();
                        $this->link->commit();
                        $result = $this->link->lastInsertId();
                        log_debug('sql_db->insert -> done "' . $result . '"');
                    } catch (PDOException $e) {
                        $this->link->rollback();
                        log_debug('sql_db->insert -> failed (' . $sql . ')');
                    }
                    */
                    //$sql_result = $this->exe($sql);

                    // TODO catch SQL errors and report them
                    $sql_result = pg_query($this->postgres_link, $sql);
                    if ($sql_result) {
                        $sql_error = pg_result_error($sql_result);
                        if ($sql_error != '') {
                            if ($log_err) {
                                log_err('Execution of ' . $sql . ' failed due to ' . $sql_error);
                            }
                        } else {
                            if ($this->type != DB_TYPE_VALUE_TIME_SERIES_DATA) {
                                $result = pg_fetch_array($sql_result)[0];
                            } else {
                                $result = 1;
                            }
                        }
                    } else {
                        $sql_error = pg_last_error($this->postgres_link);
                        if ($log_err) {
                            log_err('Execution of ' . $sql . ' failed completely due to ' . $sql_error);
                        }
                    }

                    //if ($result == false) {                        die(pg_last_error());                    }
                }
            } elseif ($this->db_type == sql_db::MYSQL) {
                $sql = $sql . ';';
                //$sql_result = $this->exe($sql, 'insert_' . $this->name_sql_esc($this->table), array(), sys_log_level::FATAL);
                try {
                    $sql_result = $this->exe($sql, '', array(), sys_log_level::FATAL);
                    if ($sql_result) {
                        $result = mysqli_insert_id($this->mysql);
                        // user database row have a double unique index, but relevant
                        if ($result == 0) {
                            if (is_array($values)) {
                                $result = $values[0];
                            } else {
                                $result = $values;
                            }
                        }
                        log_debug('sql_db->insert -> done "' . $result . '"');
                    } else {
                        $result = -1;
                        log_debug('sql_db->insert -> failed (' . $sql . ')');
                    }
                } catch (Exception $e) {
                    $trace_link = log_err('Cannot insert with "' . $sql . '" because: ' . $e->getMessage());
                    $result = -1;
                }

            } else {
                log_err('Unknown database type "' . $this->db_type . '"', 'sql_db->fetch');
            }
        } else {
            $result = -1;
            log_debug('sql_db->insert -> failed (' . $sql . ')');
        }

        return $result;
    }


    /**
     * add a new unique text to the database and return the id (similar to get_id)
     */
    function add_id($name)
    {
        log_debug('sql_db->add_id ' . $name . ' to ' . $this->type);

        $this->set_table();
        $this->set_name_field();
        $result = $this->insert($this->name_field, $name);

        log_debug('sql_db->add_id is "' . $result . '"');
        return $result;
    }

    /**
     * similar to zu_sql_add_id, but using a second ID field
     */
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

    /**
     * update some values in a table
     * $id is the primary id of the db table or an array with the ids of the primary keys
     * @return bool false if the update has failed (and the error messages are logged)
     */
    function update($id, $fields, $values, string $id_field = ''): bool
    {
        global $debug;

        log_debug('sql_db->update of ' . $this->type . ' row ' . dsp_var($id) . ' ' . dsp_var($fields) . ' with "' . dsp_var($values) . '" for user ' . $this->usr_id);

        $result = true;

        // check parameter
        $par_ok = true;
        $this->set_table();
        $this->set_id_field($id_field);
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
            if ($this->type <> 'user' and $this->type <> 'user_profile') {
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
            //$result = $this->exe($sql, 'update_' . $this->name_sql_esc($this->table), array(), sys_log_level::FATAL);
            try {
                $sql_result = $this->exe($sql, '', array(), sys_log_level::FATAL);
                if (!$sql_result) {
                    $result = false;
                }
            } catch (Exception $e) {
                $msg = 'Update';
                $trace_link = log_err($msg . log::MSG_ERR_USING . $sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                $result = $msg . log::MSG_ERR_INTERNAL . $trace_link;
            }
        }

        log_debug('sql_db->update -> done (' . $result . ')');
        return $result;
    }


    /**
     * @throws Exception
     */
    function update_name($id, $name): bool
    {
        $this->set_name_field();
        return $this->update($id, $this->name_field, $name);
    }

    /**
     * delete action
     * @return string an empty string if the deletion has been successful
     *                or the error message that should be shown to the user
     *                which may include a link for error tracing
     */
    function delete($id_fields, $id_values): string
    {
        if (is_array($id_fields)) {
            log_debug('sql_db->delete in "' . $this->type . '" WHERE "' . dsp_array($id_fields) . '" IS "' . dsp_array($id_values) . '" for user ' . $this->usr_id);
        } else {
            log_debug('sql_db->delete in "' . $this->type . '" WHERE "' . $id_fields . '" IS "' . $id_values . '" for user ' . $this->usr_id);

        }

        $this->set_table();

        if (is_array($id_fields)) {
            $sql = 'DELETE FROM ' . $this->name_sql_esc($this->table);
            $sql_del = '';
            foreach (array_keys($id_fields) as $i) {
                $del_val = $id_values[$i];
                if (is_array($del_val)) {
                    $del_val_txt = ' IN (' . $this->sf(sql_array($del_val)) . ') ';
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
        return $this->exe_try('Deleting of ' . $this->type, $sql, '', array(), sys_log_level::FATAL);
    }

    /*
      list functions to finally get data from the MySQL database
    */

    /**
     * load all types of a type/table at once
     */
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

        log_debug('sql_db->load_types -> got ' . dsp_count($result));
        return $result;
    }

    /*
     * private supporting functions
     */

    /**
     * escape or reformat the reserved SQL names
     */
    private
    function name_sql_esc($field)
    {
        switch ($this->db_type) {
            case sql_db::POSTGRES:
                if (in_array(strtoupper($field), sql_db::POSTGRES_RESERVED_NAMES)
                    or in_array(strtoupper($field), sql_db::POSTGRES_RESERVED_NAMES_EXTRA)) {
                    $field = '"' . $field . '"';
                }
                break;
            case sql_db::MYSQL:
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

    /**
     * Sql Format: format a value for a SQL statement
     * TODO define where to prevent code injections: here?
     *
     * $field_value is the value that should be formatted
     * $force_type can be set to force the formatting e.g. for the time word 2021 to use '2021'
     * outside this module it should only be used to format queries that are not yet using the abstract form for all databases (MySQL, MariaSQL, Casandra, Droid)
     */
    function sf($field_value, $forced_format = '')
    {
        $result = $field_value;
        if ($this->db_type == sql_db::POSTGRES) {
            $result = $this->postgres_format($result, $forced_format);
        } elseif ($this->db_type == sql_db::MYSQL) {
            $result = $this->mysqli_format($result, $forced_format);
        } else {
            log_err('Unknown database type ' . $this->db_type);
        }
        return $result;
    }

    /**
     * formats one value for the PostgreSQL statement
     */
    function postgres_format($field_value, $forced_format)
    {
        $result = $field_value;

        // add the formatting for the sql statement
        if (trim($result) == "" or trim($result) == self::NULL_VALUE) {
            $result = self::NULL_VALUE;
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

    /**
     * formats one value for the MySQL statement
     */
    function mysqli_format($field_value, $forced_format): string
    {
        $result = $field_value;

        // add the formatting for the sql statement
        if (trim($result) == "" or trim($result) == self::NULL_VALUE) {
            $result = self::NULL_VALUE;
        } else {
            if ($forced_format == sql_db::FLD_FORMAT_VAL) {
                if (substr($result, 0, 1) == "'" and substr($result, -1, 1) == "'") {
                    $result = substr($result, 1, -1);
                }
            } elseif ($forced_format == sql_db::FLD_FORMAT_TEXT or !is_numeric($result)) {

                // escape the text value for MySQL
                if (!isset($this->mysql)) {
                    $result = $this->sql_escape($result);
                } else {
                    $result = mysqli_real_escape_string($this->mysql, $result);
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

    /**
     * fallback SQL string escape function if there is no database connection
     */
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
                $sql = '';
                if ($this->db_type == sql_db::POSTGRES) {
                    $seq_name = $this->table . '_' . $this->id_field . '_seq';
                    $sql = 'ALTER SEQUENCE ' . $seq_name . ' RESTART ' . $next_id . ';';
                } elseif ($this->db_type == sql_db::MYSQL) {
                    $sql = 'ALTER TABLE ' . $this->name_sql_esc($this->table) . ' auto_increment = ' . $next_id . ';';
                } else {
                    log_err('Unexpected SQL type ' . $type);
                }
                $this->exe_try('Resetting sequence for ' . $type, $sql);
                $msg = 'Next database id for ' . $this->table . ': ' . $next_id;

            }
        }
        return $msg;
    }

    /**
     * check if a table name exists
     * @param string $table_name
     * @return bool true if the table name exists
     */
    function has_table(string $table_name): bool
    {
        $result = false;
        $sql_check = 'SELECT' . ' TRUE FROM INFORMATION_SCHEMA.COLUMNS WHERE ';
        if ($this->db_type == sql_db::POSTGRES) {
            $sql_check .= "TABLE_NAME = '" . $table_name . "';";
        } elseif ($this->db_type == sql_db::MYSQL) {
            $sql_check .= "TABLE_SCHEMA = 'zukunft' AND TABLE_NAME = '" . $table_name . "';";
        } else {
            $msg = 'Unknown database type "' . $this->db_type . '"';
            log_err($msg, 'sql_db->has_column');
            $result .= $msg;
        }
        if ($sql_check != '') {
            $sql_result = $this->get1($sql_check);
            if ($sql_result) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * check if a column name exists
     * @param string $table_name
     * @param string $column_name
     * @return bool true if the column name exists in the given table
     */
    function has_column(string $table_name, string $column_name): bool
    {
        $result = false;
        $sql_check = '';
        if ($this->db_type == sql_db::POSTGRES) {
            $sql_check = "SELECT TRUE FROM pg_attribute WHERE attrelid = '" . $table_name . "'::regclass AND  attname = '" . $column_name . "' AND NOT attisdropped ";
        } elseif ($this->db_type == sql_db::MYSQL) {
            $sql_check = "SELECT TRUE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'zukunft' AND TABLE_NAME = '" . $table_name . "' AND COLUMN_NAME = '" . $column_name . "';";
        } else {
            $msg = 'Unknown database type "' . $this->db_type . '"';
            log_err($msg, 'sql_db->has_column');
            $result .= $msg;
        }
        if ($sql_check != '') {
            $sql_result = $this->get1($sql_check);
            if ($sql_result) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * check if a foreign key exists
     * @param string $table_name
     * @param string $key_name
     * @return bool true if the key name exists in the given table
     */
    function has_key(string $table_name, string $key_name): bool
    {
        $result = false;
        $sql_check = '';
        if ($this->db_type == sql_db::POSTGRES) {
            $sql_check = "SELECT" . " TRUE 
                            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                           WHERE CONSTRAINT_CATALOG = 'zukunft' 
                             AND CONSTRAINT_NAME = '" . $key_name . "';";
        } elseif ($this->db_type == sql_db::MYSQL) {
            $sql_check = "SELECT" . " TRUE 
                            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                           WHERE CONSTRAINT_SCHEMA = 'zukunft' 
                             AND TABLE_NAME = '" . $table_name . "' 
                             AND CONSTRAINT_NAME = '" . $key_name . "';";
        } else {
            $msg = 'Unknown database type "' . $this->db_type . '"';
            log_err($msg, 'sql_db->has_column');
            $result .= $msg;
        }
        if ($sql_check != '') {
            $sql_result = $this->get1($sql_check);
            if ($sql_result) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * add a foreign key to the database
     * @param string $key_name
     * @param string $from_table
     * @param string $from_column
     * @param string $to_table
     * @param string $to_column
     * @return string an empty string if the adding has been successful or is not added and an error message if the adding has failed
     */
    function add_foreign_key(string $key_name,
                             string $from_table,
                             string $from_column,
                             string $to_table,
                             string $to_column): string
    {
        $result = '';

        // adjust the parameters to the used database used
        $from_table = $this->get_table_name_esc($from_table);
        $to_table = $this->get_table_name_esc($to_table);

        // check if the old column name is still valid
        if (!$this->has_key($from_table, $key_name)) {

            // actually add the column
            $sql = '';
            if ($this->db_type == sql_db::POSTGRES) {
                $sql = 'ALTER TABLE ' . $from_table . ' ADD CONSTRAINT ' . $key_name . ' FOREIGN KEY (' . $from_column . ') REFERENCES ' . $to_table . ' (' . $to_column . ');';
            } elseif ($this->db_type == sql_db::MYSQL) {
                $sql = 'ALTER TABLE `' . $from_table . '` ADD CONSTRAINT `' . $key_name . '` FOREIGN KEY (`' . $from_column . '`) REFERENCES `' . $to_table . '`(`' . $to_column . '`) ON DELETE RESTRICT ON UPDATE RESTRICT; ';
            } else {
                $msg = 'Unknown database type "' . $this->db_type . '"';
                log_err($msg, 'sql_db->has_column');
                $result .= $msg;
            }
            $result .= $this->exe_try('Adding foreign key to ' . $from_table, $sql);
        }

        return $result;
    }

    /**
     * add a database column but only if needed
     * @param string $table_name
     * @param string $column_name
     * @param string $type_name
     * @return string an empty string if the adding has been successful or is not added and an error message if the adding has failed
     */
    function add_column(string $table_name, string $column_name, string $type_name): string
    {
        $result = '';

        // adjust the parameters to the used database used
        $table_name = $this->get_table_name($table_name);

        // check if the old column name is still valid
        if (!$this->has_column($table_name, $column_name)) {

            // adjust the type name for the use database
            if ($this->db_type == sql_db::MYSQL) {
                if ($type_name == 'bigint') {
                    $type_name = 'int(11)';
                }
            }

            // actually add the column
            $sql = 'ALTER TABLE ' . $this->name_sql_esc($table_name) . ' ADD COLUMN ' . $this->name_sql_esc($column_name) . ' ' . $type_name . ';';
            $result .= $this->exe_try('Adding column ' . $column_name . ' to ' . $table_name, $sql);
        }

        return $result;
    }

    /**
     * create an SQL statement to change the name of a column
     *
     * @param string $table_name
     * @param string $from_column_name
     * @param string $to_column_name
     * @return string an empty string if the renaming has been successful or is not needed
     */
    function change_column_name(string $table_name, string $from_column_name, string $to_column_name): string
    {
        $result = '';

        // adjust the parameters to the used database used
        $table_name = $this->get_table_name($table_name);

        // check if the old column name is still valid
        if ($this->has_column($table_name, $from_column_name)) {
            $sql = '';
            if ($this->db_type == sql_db::POSTGRES) {
                $sql = 'ALTER TABLE ' . $this->name_sql_esc($table_name) . ' RENAME ' . $this->name_sql_esc($from_column_name) . ' TO ' . $this->name_sql_esc($to_column_name) . ';';
            } elseif ($this->db_type == sql_db::MYSQL) {
                $pre_sql = "SELECT " . "CONCAT(COLUMN_TYPE,
                                    if(IS_NULLABLE='NO',' not null',''),
                                    if(COLUMN_DEFAULT is not null,concat(' default ',if(DATA_TYPE!='int','\'',''),COLUMN_DEFAULT,if(DATA_TYPE!='int','\'','')),'')) AS COL_TYPE 
                               FROM INFORMATION_SCHEMA.COLUMNS 
                              WHERE table_name = '" . $table_name . "' 
                                AND COLUMN_NAME = '" . $from_column_name . "';";
                $db_row = $this->get1($pre_sql);
                $db_format = $db_row['COL_TYPE'];
                $sql = "ALTER TABLE `" . $table_name . "` CHANGE `" . $from_column_name . "` `" . $to_column_name . "` " . $db_format . ";";
            } else {
                $msg = 'Unknown database type "' . $this->db_type . '"';
                log_err($msg, 'sql_db->change_column_name');
                $result .= $msg;
            }
            if ($sql != '') {
                $result .= $this->exe_try('Changing column name from ' . $from_column_name . ' to ' . $to_column_name . ' in ' . $table_name, $sql);
            }
        }

        return $result;
    }

    /**
     * create an SQL statement to change the name of a table and execute it
     *
     * @param string $table_name
     * @param string $to_table_name
     * @return string an empty string if the renaming has been successful or is not needed
     */
    function change_table_name(string $table_name, string $to_table_name): string
    {
        $result = '';

        // adjust the parameters to the used database name
        $to_table_name = $this->get_table_name($to_table_name);

        // check if the old table name is still valid
        if ($this->has_table($table_name)) {
            $sql = '';
            if ($this->db_type == sql_db::POSTGRES) {
                $sql = 'ALTER TABLE ' . $this->name_sql_esc($table_name) . ' RENAME TO ' . $this->name_sql_esc($to_table_name) . ';';
            } elseif ($this->db_type == sql_db::MYSQL) {
                $sql = 'RENAME TABLE ' . $this->name_sql_esc($table_name) . ' TO ' . $this->name_sql_esc($to_table_name) . ';';
            } else {
                $msg = 'Unknown database type "' . $this->db_type . '"';
                log_err($msg, 'sql_db->change_column_name');
                $result .= $msg;
            }
            if ($sql != '') {
                $result .= $this->exe_try('Changing table name from ' . $table_name . ' to ' . $to_table_name, $sql);
            }
        }

        return $result;
    }

    function column_allow_null(string $table_name, string $column_name): string
    {
        $result = '';

        // adjust the parameters to the used database name
        $table_name = $this->get_table_name($table_name);

        // check if the column name is still valid
        if ($this->has_column($table_name, $column_name)) {
            $sql = '';
            if ($this->db_type == sql_db::POSTGRES) {
                $sql = 'ALTER TABLE ' . $this->name_sql_esc($table_name) . ' ALTER COLUMN ' . $this->name_sql_esc($column_name) . ' DROP NOT NULL;';
            } elseif ($this->db_type == sql_db::MYSQL) {
                $pre_sql = "SELECT " . "CONCAT(COLUMN_TYPE,
                                    if(COLUMN_DEFAULT is not null,concat(' default ',if(DATA_TYPE!='int','\'',''),COLUMN_DEFAULT,if(DATA_TYPE!='int','\'','')),''),
                                    ' NULL ') AS COL_TYPE 
                               FROM INFORMATION_SCHEMA.COLUMNS 
                              WHERE table_name = '" . $table_name . "' 
                                AND COLUMN_NAME = '" . $column_name . "';";
                $db_row = $this->get1($pre_sql);
                $db_format = $db_row['COL_TYPE'];
                $sql = "ALTER TABLE `" . $table_name . "` CHANGE `" . $column_name . "` `" . $column_name . "` " . $db_format . ";";
                //$sql_a = 'ALTER TABLE `word_types` CHANGE `word_symbol` `word_symbol` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'e.g. for percent the symbol is %'; '
            } else {
                $msg = 'Unknown database type "' . $this->db_type . '"';
                log_err($msg, 'sql_db->change_column_name');
                $result .= $msg;
            }
            if ($sql != '') {
                $result .= $this->exe_try('Allowing NULL value for ' . $column_name . ' in ' . $table_name, $sql);
            }
        } else {
            log_warning('Cannot allow null in ' . $table_name . ' because ' . $column_name . ' is missing');
        }

        return $result;
    }

    function column_force_not_null(string $table_name, string $column_name): string
    {
        $result = '';

        // adjust the parameters to the used database name
        $table_name = $this->get_table_name($table_name);

        // check if the column name is still valid
        if ($this->has_column($table_name, $column_name)) {
            $sql = '';
            if ($this->db_type == sql_db::POSTGRES) {
                $sql = 'ALTER TABLE ' . $this->name_sql_esc($table_name) . ' ALTER COLUMN ' . $this->name_sql_esc($column_name) . ' SET NOT NULL;';
            } elseif ($this->db_type == sql_db::MYSQL) {
                $pre_sql = "SELECT " . "CONCAT(COLUMN_TYPE,
                                    ' not null',
                                    if(COLUMN_DEFAULT is not null,concat(' default ',if(DATA_TYPE!='int','\'',''),COLUMN_DEFAULT,if(DATA_TYPE!='int','\'','')),'')) AS COL_TYPE 
                               FROM INFORMATION_SCHEMA.COLUMNS 
                              WHERE table_name = '" . $table_name . "' 
                                AND COLUMN_NAME = '" . $column_name . "';";
                $db_row = $this->get1($pre_sql);
                $db_format = $db_row['COL_TYPE'];
                $sql = "ALTER TABLE `" . $table_name . "` CHANGE `" . $column_name . "` `" . $column_name . "` " . $db_format . ";";
            } else {
                $msg = 'Unknown database type "' . $this->db_type . '"';
                log_err($msg, 'sql_db->change_column_name');
                $result .= $msg;
            }
            if ($sql != '') {
                $result .= $this->exe_try('Remove allowing NULL value from ' . $column_name . ' in ' . $table_name, $sql);
            }
        } else {
            log_warning('Cannot force not null in ' . $table_name . ' because ' . $column_name . ' is missing');
        }

        return $result;
    }

    function remove_prefix(string $table_name, string $column_name, string $prefix_name): bool
    {
        $result = false;

        // adjust the parameters to the used database name
        $table_name = $this->get_table_name($table_name);

        $sql_select = "SELECT " . $this->name_sql_esc($column_name) . " FROM " . $this->name_sql_esc($table_name) . ";";
        $db_row_lst = $this->get($sql_select);
        foreach ($db_row_lst as $db_row) {
            $db_row_name = $db_row[$column_name];
            $new_name = zu_str_right_of($db_row_name, $prefix_name);
            if ($new_name != '' and $new_name != $db_row_name) {
                $result = $this->change_code_id($table_name, $db_row_name, $new_name);
            }
        }

        return $result;
    }

    function change_code_id(string $table_name, string $old_code_id, string $new_code_id): string
    {
        $result = '';

        // adjust the parameters to the used database name
        $table_name = $this->get_table_name_esc($table_name);

        if ($new_code_id != '' and $old_code_id != '' and $old_code_id != $new_code_id) {
            $sql = "UPDATE " . $table_name . " SET code_id = '" . $new_code_id . "' WHERE code_id = '" . $old_code_id . "';";
            $result = $this->exe_try('Changing code id from ' . $old_code_id . ' to ' . $new_code_id, $sql);
        }

        return $result;
    }

    function get_column_names(string $table_name): array
    {
        $result = array();
        $sql = 'SELECT' . ' column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE ';
        if ($this->db_type == sql_db::POSTGRES) {
            $sql .= " table_name = '" . $table_name . "';";
        } elseif ($this->db_type == sql_db::MYSQL) {
            $sql .= " TABLE_SCHEMA = 'zukunft' AND TABLE_NAME = '" . $table_name . "';";
        } else {
            $msg = 'Unknown database type "' . $this->db_type . '"';
            log_err($msg, 'sql_db->has_column');
            $result .= $msg;
        }
        if ($sql != '') {
            $col_rows = $this->get($sql);
            if ($col_rows != null) {
                foreach ($col_rows as $col_row) {
                    if ($this->db_type == sql_db::POSTGRES) {
                        $result[] = $col_row['column_name'];
                    } else {
                        $result[] = $col_row['COLUMN_NAME'];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * check if at least all given column names are in the table
     * @param string $table_name the name of the table which is expected to have the give column names
     * @param array $expected_columns of the column names that are expected to exist in the given table
     * @return bool true if everything is fine
     */
    function check_column_names(string $table_name, array $expected_columns): bool
    {
        $result = true;
        $real_columns = $this->get_column_names($table_name);
        $missing_columns = array_diff($expected_columns, $real_columns);
        if (count($missing_columns) > 0) {
            // TODO add $this
            log_err('Database column ' . dsp_array($missing_columns) . ' missing in ' . $table_name);
            $result = false;
        }
        return $result;
    }

}


/*

  name shortcuts - rename some often used functions to make to code look nicer and not draw the focus away from the important part
  --------------

*/


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
