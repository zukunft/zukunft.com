<?php

/*

  sql_db.php - the SQL database link and abstraction layer
  ----------
  
  the database link is reduced to a very few basic functions that exists on all databases
  this way an apache droid or hadoop adapter should also be possible
  at the moment adapter to MySQL and Postgres are working
  
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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

namespace cfg\db;

include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_DB_PATH . 'sql.php';
include_once MODEL_SYSTEM_PATH . 'log.php';
include_once MODEL_IMPORT_PATH . 'import_file.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\component\component_link_type;
use cfg\component\position_type;
use cfg\component\component_type;
use cfg\config;
use cfg\element;
use cfg\element_type;
use cfg\formula;
use cfg\formula_link;
use cfg\formula_link_type;
use cfg\formula_type;
use cfg\group\group;
use cfg\group\group_id;
use cfg\import\import_file;
use cfg\ip_range;
use cfg\job;
use cfg\job_time;
use cfg\job_type;
use cfg\job_type_list;
use cfg\language;
use cfg\language_form;
use cfg\library;
use cfg\log;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_big_value;
use cfg\log\change_field;
use cfg\log\change_link;
use cfg\log\change_prime_value;
use cfg\log\change_norm_value;
use cfg\log\change_table;
use cfg\log\change_table_field;
use cfg\log\system_log;
use cfg\phrase;
use cfg\phrase_table;
use cfg\phrase_table_status;
use cfg\phrase_type;
use cfg\pod;
use cfg\pod_status;
use cfg\pod_type;
use cfg\protection_type;
use cfg\ref;
use cfg\ref_type;
use cfg\result\result;
use cfg\sandbox;
use cfg\session;
use cfg\share_type;
use cfg\source;
use cfg\source_type;
use cfg\sys_log_function;
use cfg\sys_log_level;
use cfg\sys_log_status;
use cfg\sys_log_type;
use cfg\system_time;
use cfg\system_time_type;
use cfg\term;
use cfg\triple;
use cfg\type_lists;
use cfg\user;
use cfg\user\user_profile;
use cfg\user\user_type;
use cfg\user_message;
use cfg\user_official_type;
use cfg\user_profile_list;
use cfg\value\value;
use cfg\value\value_phrase_link;
use cfg\value\value_time_series;
use cfg\value\value_ts_data;
use cfg\verb;
use cfg\verb_list;
use cfg\view;
use cfg\view_link_type;
use cfg\view_term_link;
use cfg\view_type;
use cfg\word;
use Exception;
use html\html_base;
use mysqli;
use mysqli_result;
use PDOException;
use unit_read\all_unit_read_tests;

class sql_db
{

    // these databases can be used at the moment (must be the same as in zu_lib)
    const POSTGRES = "Postgres";
    const MYSQL = "MySQL";
    const DB_LIST = [POSTGRES, MYSQL];

    const POSTGRES_PATH = "postgres";
    const MYSQL_PATH = "mysql";

    const POSTGRES_EXT = "";
    const MYSQL_EXT = "_mysql";

    // data retrieval settings
    const SQL_QUERY_NAME_MAX_LEN = 62; // the query name cannot be longer than 62 chars at least for some databases

    // default settings for sql
    const ROW_LIMIT = 20; // default number of rows per page/query if the user has not defined another limit
    const ROW_MAX = 2000; // the max number of rows per query to avoid long response times

    // SQL table and model object names used
    // the used database objects (the table name is in most cases with an extra 's', because each table contains the data for many objects)
    // TODO use const for all object names
    // TODO try to use the class name if possible
    const TBL_USER = 'user';
    const TBL_USER_TYPE = 'user_type';
    const TBL_USER_PROFILE = 'user_profile';
    const TBL_USER_OFFICIAL_TYPE = 'user_official_type';
    const TBL_WORD = 'word';
    const TBL_PHRASE_TYPE = 'phrase_type';
    const TBL_TRIPLE = 'triple';
    const TBL_VERB = 'verb';
    const TBL_PHRASE = 'phrase';
    const TBL_GROUP = 'group';
    const TBL_VALUE_TIME_SERIES = 'values_time_series';
    const TBL_VALUE_TIME_SERIES_DATA = 'value_ts_data';
    const TBL_VALUE_PHRASE_LINK = 'value_phrase_link';
    const TBL_SOURCE = 'source';
    const TBL_SOURCE_TYPE = 'source_type';
    const TBL_REF = 'ref';
    const TBL_REF_TYPE = 'ref_type';
    const TBL_FORMULA = 'formula';
    const TBL_FORMULA_TYPE = 'formula_type';
    const TBL_FORMULA_LINK = 'formula_link';
    const TBL_FORMULA_LINK_TYPE = 'formula_link_type';
    const TBL_ELEMENT = 'element';
    const TBL_ELEMENT_TYPE = 'element_type';
    const TBL_RESULT = 'result';
    const TBL_VIEW = 'view';
    const TBL_VIEW_TYPE = 'view_type';
    const TBL_COMPONENT = 'component';
    const TBL_COMPONENT_LINK = 'component_link';
    const TBL_COMPONENT_TYPE = 'component_type';
    const TBL_COMPONENT_LINK_TYPE = 'component_link_type';
    const TBL_COMPONENT_POS_TYPE = 'position_type';
    const TBL_VIEW_TERM_LINK = 'view_term_link';

    const TBL_CHANGE = 'change';
    const TBL_CHANGE_TABLE = 'change_table';
    const TBL_CHANGE_FIELD = 'change_field';
    const TBL_CHANGE_ACTION = 'change_action';
    const TBL_CHANGE_LINK = 'change_link';
    const TBL_CONFIG = 'config';
    const TBL_IP = 'ip_range';
    const TBL_SYS_LOG = 'sys_log';
    const TBL_SYS_LOG_STATUS = 'sys_log_status';
    const TBL_SYS_SCRIPT = 'system_time_type'; // to log the execution times for code optimising
    const TBL_TASK = 'job';
    const TBL_TASK_TYPE = 'job_type';

    const TBL_LANGUAGE = 'language';
    const TBL_LANGUAGE_FORM = 'language_form';

    const TBL_SHARE = 'share_type';
    const TBL_PROTECTION = 'protection_type';

    const TBL_USER_PREFIX = 'user_';

    // the synthetic view tables (VT) for union query creation
    const VT_PHRASE_GROUP_LINK = 'group_link'; // TODO deprecate
    const VT_TABLE_FIELD = 'change_table_field';

    // difference between the object name and the table name
    const TABLE_EXTENSION = 's';

    // reserved words that are automatically escaped

    // based on https://www.Postgres.org/docs/current/sql-keywords-appendix.html from 2021-06-13
    const POSTGRES_RESERVED_NAMES = ['AND ', 'ANY ', 'ARRAY ', 'AS ', 'ASC ', 'ASYMMETRIC ', 'BOTH ', 'CASE ', 'CAST ', 'CHECK ', 'COLLATE ', 'COLUMN ', 'CONSTRAINT ', 'CREATE ', 'CURRENT_CATALOG ', 'CURRENT_DATE ', 'CURRENT_ROLE ', 'CURRENT_TIME ', 'CURRENT_TIMESTAMP ', 'CURRENT_USER ', 'DEFAULT ', 'DEFERRABLE ', 'DESC ', 'DISTINCT ', 'DO ', 'ELSE ', 'END ', 'EXCEPT ', 'FALSE ', 'FETCH ', 'FOR ', 'FOREIGN ', 'FROM ', 'GRANT ', 'GROUP ', 'HAVING ', 'IN ', 'INITIALLY ', 'INTERSECT ', 'INTO ', 'LATERAL ', 'LEADING ', 'LIMIT ', 'LOCALTIME ', 'LOCALTIMESTAMP ', 'NOT ', 'NULL ', 'OFFSET ', 'ON ', 'ONLY ', 'OR ', 'ORDER ', 'PLACING ', 'PRIMARY ', 'REFERENCES ', 'RETURNING ', 'SELECT ', 'SESSION_USER ', 'SOME ', 'SYMMETRIC ', 'TABLE ', 'THEN ', 'TO ', 'TRAILING ', 'TRUE ', 'UNION ', 'UNIQUE ', 'USER ', 'USING ', 'VARIADIC ', 'WHEN ', 'WHERE ', 'WINDOW ', 'WITH ',];
    // extra names for backward compatibility
    const POSTGRES_RESERVED_NAMES_EXTRA = ['USER'];

    // Based on MySQL version 8
    const MYSQL_RESERVED_NAMES = ['ACCESSIBLE', 'ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE', 'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BY', 'CALL', 'CASCADE', 'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION', 'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE', 'CROSS', 'CUME_DIST', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'DATABASE', 'DATABASES', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DELAYED', 'DELETE', 'DENSE_RANK', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW', 'DIV', 'DOUBLE', 'DROP', 'DUAL', 'EACH', 'ELSE', 'ELSEIF', 'EMPTY', 'ENCLOSED', 'ESCAPED', 'EXCEPT', 'EXCEPT', 'EXISTS', 'EXIT', 'EXPLAIN', 'FALSE', 'FETCH', 'FIRST_VALUE', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR', 'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 'GENERATED', 'GET', 'GRANT', 'GROUP', 'GROUPING', 'GROUPS', 'HAVING', 'HIGH_PRIORITY', 'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IF', 'IGNORE', 'IN', 'INDEX', 'INFILE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INT', 'INT1', 'INT2', 'INT3', 'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'IO_AFTER_GTIDS', 'IO_BEFORE_GTIDS', 'IS', 'ITERATE', 'JOIN', 'JSON_TABLE', 'KEY', 'KEYS', 'KILL', 'LAG', 'LAST_VALUE', 'LATERAL', 'LEAD', 'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY', 'MASTER_BIND', 'MASTER_SSL_VERIFY_SERVER_CERT', 'MATCH', 'MAXVALUE', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL', 'NO_WRITE_TO_BINLOG', 'NOT', 'NTH_VALUE', 'NTILE', 'NULL', 'NUMERIC', 'OF', 'ON', 'OPTIMIZE', 'OPTIMIZER_COSTS', 'OPTION', 'OPTIONALLY', 'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'OVER', 'PARTITION', 'PERCENT_RANK', 'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE', 'RANGE', 'RANK', 'READ', 'READ_WRITE', 'READS', 'REAL', 'RECURSIVE', 'REFERENCES', 'REGEXP', 'RELEASE', 'RENAME', 'REPEAT', 'REPLACE', 'REQUIRE', 'RESIGNAL', 'RESTRICT', 'RETURN', 'REVOKE', 'RIGHT', 'RLIKE', 'ROW_NUMBER', 'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE', 'SEPARATOR', 'SET', 'SHOW', 'SIGNAL', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SSL', 'STARTING', 'STORED', 'STRAIGHT_JOIN', 'SYSTEM', 'TABLE', 'TERMINATED', 'THEN', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE', 'UNDO', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING', 'VIRTUAL', 'WHEN', 'WHERE', 'WHILE', 'WINDOW', 'WITH', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL'];
    // extra names for backward compatibility
    const MYSQL_RESERVED_NAMES_EXTRA = ['VALUE', 'VALUES', 'URL'];

    // setup header and footer
    const SETUP_HEADER = 'ALTER DATABASE zukunft SET search_path TO public;';
    const SETUP_HEADER_MYSQL = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"; SET time_zone = "+00:00"; /*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */; /*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */; /*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */; /*!40101 SET NAMES utf8 */; -- Database:`zukunft` ';

    const SETUP_FOOTER = '';
    const SETUP_FOOTER_MYSQL = '/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */; /*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */; /*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;';
    const SETUP_COMMENT = '--';
    const SETUP_INDEX = 'indexes for tables';
    const SETUP_INDEX_COM = 'remark: no index needed for preloaded tables such as phrase types';
    const SETUP_FOREIGN_KEY = 'foreign key constraints and auto_increment for tables';

    // classes that have a database table
    const DB_TABLE_CLASSES = [
        config::class,
        sys_log_type::class,
        sys_log_status::class,
        sys_log_function::class,
        system_log::class,
        system_time_type::class,
        system_time::class,
        job_type::class,
        job_time::class,
        job::class,
        user_type::class,
        user_profile::class,
        user_official_type::class,
        user::class,
        ip_range::class,
        session::class,
        change_action::class,
        change_table::class,
        change_field::class,
        change::class,
        change_prime_value::class,
        change_norm_value::class,
        change_big_value::class,
        change_link::class,
        pod_type::class,
        pod_status::class,
        pod::class,
        protection_type::class,
        share_type::class,
        language::class,
        language_form::class,
        word::class,
        verb::class,
        triple::class,
        phrase_table_status::class,
        phrase_table::class,
        phrase_type::class,
        group::class,
        source_type::class,
        source::class,
        ref_type::class,
        ref::class,
        value::class,
        value_ts_data::class,
        element_type::class,
        element::class,
        formula_type::class,
        formula::class,
        formula_link_type::class,
        formula_link::class,
        result::class,
        view_type::class,
        view::class,
        view_link_type::class,
        view_term_link::class,
        component_link_type::class,
        position_type::class,
        component_type::class,
        component::class,
        component_link::class
    ];
    // classes that use a database view
    const DB_VIEW_CLASSES = [
        phrase::class,
        term::class,
        change_table_field::class
    ];

    // classes that use a sql write script with log write
    const DB_WRITE_LOG_SCRIPT_CLASSES = [
        word::class,
        source::class];

    // classes that use a sql write script with log write
    const DB_WRITE_PREPARED = [
        word::class,
        source::class];

    // tables that do not have a name
    // e.g. sql_db::TBL_TRIPLE is a link which hase a name, but the generated name can be overwritten, so the standard field naming is not used
    // TODO use class
    const DB_TYPES_NOT_NAMED = [
        triple::class,
        value::class,
        value_time_series::class,
        formula_link::class,
        result::class,
        element::class,
        component_link::class,
        value_phrase_link::class,
        view_term_link::class,
        ref::class,
        sql_db::TBL_IP,
        change::class,
        change_link::class,
        sql_db::TBL_SYS_LOG,
        sql_db::TBL_TASK,
        sql_db::VT_PHRASE_GROUP_LINK
    ];
    const CLASSES_WITH_USER_CHANGES = [
        word::class,
        triple::class
    ];

    // tables that link two named tables
    // TODO set automatically by set_link_fields???
    const DB_TYPES_LINK = [
        triple::class,
        formula_link::class,
        component_link::class,
        ref::class
    ];


    // open used name extension for the prepared sql statements
    const FLD_ID = 'id';                          // used also to name the sql statements
    const FLD_NAME = 'name';                      // used      to name the sql statements
    const FLD_SEP = '_';                          // the separator for the SQL field name parts
    const FLD_EXT_ID = '_id';
    const FLD_EXT_NAME = '_name';
    const FLD_EXT_TYPE_ID = '_type_id';

    const USER_PREFIX = 'user_';                  // prefix used for tables where the user sandbox values are stored

    const STD_TBL = 's';                          // prefix used for the standard table where data for all users are stored
    const USR_TBL = 'u';                          // prefix used for the standard table where the user sandbox data is stored
    const LNK_TBL = 'l';                          // prefix used for the table which should be joined in the result
    const LNK2_TBL = 'l2';                        // prefix used for the second table which should be joined in the result
    const LNK3_TBL = 'l3';                        // prefix used for the third table which should be joined in the result
    const LNK4_TBL = 'l4';                        // prefix used for the fourth table which should be joined in the result
    const ULK_TBL = 'ul';                         // prefix used for the table which should be joined in the result of the user sandbox data
    const ULK2_TBL = 'ul2';                       // prefix used for the second user table which should be joined in the result
    const ULK3_TBL = 'ul3';                       // prefix used for the third user table which should be joined in the result
    const ULK4_TBL = 'ul4';                       // prefix used for the fourth user table which should be joined in the result
    const GRP_TBL = 'g';                          // prefix used for the standard table where data for all users are stored

    // formats to force the formatting of a value for an SQL statement e.g. convert true to 1 when using tinyint to save boolean values
    const FLD_FORMAT_TEXT = 'text';               // to force the text formatting of a value for the SQL statement formatting
    const FLD_FORMAT_VAL = 'number';              // to force the numeric formatting of a value for the SQL statement formatting
    const FLD_FORMAT_BOOL = 'boolean';            // to force the boolean formatting of a value for the SQL statement formatting

    const VAL_BOOL_TRUE = '1';

    /*
     * object variables
     */

    public ?string $db_type = null;                 // the database type which should be used for this connection e.g. Postgres or MYSQL
    // TODO change type to PgSql\Connection with php 8.1
    public $postgres_link;                          // the link object to the database
    public mysqli $mysql;                           // the MySQL object to the database

    private int $reconnect_delay = 0;               // number of seconds of the last reconnect retry delay

    public ?int $usr_id = null;                     // the user id of the person who request the database changes
    private ?int $usr_view_id = null;               // the user id of the person which values should be returned e.g. an admin might want to check the data of an user

    private ?string $class = '';                    // based of this database object type the table name and the standard fields are defined e.g. for type "word" the field "word_name" is used
    private ?string $table = '';                    // name of the table that is used for the next query
    private string|array|null $id_field = '';                 // primary key field of the table used
    private ?string $id_from_field = '';            // only for link objects the id field of the source object
    private ?string $id_to_field = '';              // only for link objects the id field of the destination object
    private ?string $id_link_field = '';            // only for link objects the id field of the link type object
    private ?string $name_field = '';               // unique text key field of the table used
    private ?string $query_name = '';               // unique name of the query to precompile and use the query
    private ?array $par_values = [];                // list of the parameter value to make sure they are in the same order as the parameter
    private ?array $par_types = [];                 // list of the parameter types, which also defines a precompiled query
    private ?array $par_use_link = [];              // array of bool, true if the parameter should be used on the linked table
    private array $par_named = [];                  // array of bool, true if the parameter placeholder is already used in the SQL statement
    private ?array $field_lst = [];                 // list of fields that should be returned to the next select query
    private ?array $usr_field_lst = [];             // list of user specific fields that should be returned to the next select query
    private ?array $usr_num_field_lst = [];         // list of user specific numeric fields that should be returned to the next select query
    private ?array $usr_bool_field_lst = [];        // list of user specific boolean / tinyint fields that should be returned to the next select query
    private ?array $usr_only_field_lst = [];        // list of fields that are only in the user sandbox
    private ?array $join_field_lst = [];            // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_field_lst = [];           // same as $join_field_lst but for the second join
    private ?array $join3_field_lst = [];           // same as $join_field_lst but for the third join
    private ?array $join4_field_lst = [];           // same as $join_field_lst but for the fourth join
    private string $join_field = '';                // if set the field name in the main table that should be used for the join; only needed, if the field name differs from the first target field
    private string $join2_field = '';               // same as $join_field but for the second join
    private string $join3_field = '';               // same as $join_field but for the third join
    private string $join4_field = '';               // same as $join_field but for the fourth join
    private string $join_to_field = '';             // if set the field name in the joined table that should be used for the join; only needed, if the joined field name differ from the type id field
    private string $join2_to_field = '';            // same as $join_field but for the second join
    private string $join3_to_field = '';            // same as $join_field but for the third join
    private string $join4_to_field = '';            // same as $join_field but for the fourth join
    private bool $join_force_rename = false;        // if true force the fields to be renamed to create unique fields e.g. if a similar object is linked
    private bool $join2_force_rename = false;       // same as $join_force_rename but for the second join
    private bool $join3_force_rename = false;       // same as $join_force_rename but for the third join
    private bool $join4_force_rename = false;       // same as $join_force_rename but for the fourth join
    private string $join_select_field = '';         // if set the field name in the joined table that should be used for a where selection
    private string $join2_select_field = '';        // same as $join_select_field but for the second join
    private string $join3_select_field = '';        // same as $join_select_field but for the third join
    private string $join4_select_field = '';        // same as $join_select_field but for the fourth join
    private int $join_select_id = 0;                // if $join_select_field is set the id (int) used for the selection
    private int $join2_select_id = 0;               // same as $join_select_id but for the second join
    private int $join3_select_id = 0;               // same as $join_select_id but for the third join
    private int $join4_select_id = 0;               // same as $join_select_id but for the fourth join
    private string $join_usr_par_name = '';         // the parameter name for the user id
    private ?array $join_usr_field_lst = [];        // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_usr_field_lst = [];       // same as $join_usr_field_lst but for the second join
    private ?array $join3_usr_field_lst = [];       // same as $join_usr_field_lst but for the third join
    private ?array $join4_usr_field_lst = [];       // same as $join_usr_field_lst but for the fourth join
    private ?array $join_usr_num_field_lst = [];    // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_usr_num_field_lst = [];   // same as $join_usr_num_field_lst but for the second join
    private ?array $join3_usr_num_field_lst = [];   // same as $join_usr_num_field_lst but for the third join
    private ?array $join4_usr_num_field_lst = [];   // same as $join_usr_num_field_lst but for the fourth join
    private ?array $join_usr_count_field_lst = [];  // list of fields that should be returned to the next select query where the count are taken from a joined table
    private ?string $join_type = '';                // the type name of the table to join
    private ?string $join2_type = '';               // the type name of the second table to join (maybe later switch to join n tables)
    private ?string $join3_type = '';               // the type name of the third table to join (maybe later switch to join n tables)
    private ?string $join4_type = '';               // the type name of the fourth table to join (maybe later switch to join n tables)
    private bool $all_query = false;                // true, if the query is expected to retrieve the standard and the user specific data
    private bool $usr_query = false;                // true, if the query is expected to retrieve user specific data
    private bool $join_usr_query = false;           // true, if the joined query is also expected to retrieve user specific data
    private bool $join2_usr_query = false;          // same as $usr_join_query but for the second join
    private bool $join3_usr_query = false;          // same as $usr_join_query but for the third join
    private bool $join4_usr_query = false;          // same as $usr_join_query but for the fourth join
    private bool $join_usr_added = false;           // true, if the user join statement has been created
    private bool $usr_only_query = false;           // true, if the query is expected to retrieve ONLY the user specific data without the standard values

    private ?string $fields = '';                   // the fields                SQL statement that is used for the next select query
    private ?string $from = '';                     // the FROM                  SQL statement that is used for the next select query
    private ?string $join = '';                     // the JOIN                  SQL statement that is used for the next select query
    private ?string $where = '';                    // the WHERE condition as an SQL statement that is used for the next select query
    private ?string $order = '';                    // the ORDER                 SQL statement that is used for the next select query
    private ?string $page = '';                     // the LIMIT and OFFSET      SQL statement that is used for the next select query
    private ?string $end = '';                      // the closing               SQL statement that is used for the next select query

    private ?array $prepared_sql_names = [];        // list of all SQL queries that have already been prepared during the open connection
    private ?array $prepared_stmt = [];             // list of the MySQL stmt

    /*
     * set up the environment
     */

    /**
     * reset the previous settings
     */
    private function reset(): void
    {
        $this->reconnect_delay = 0;
        $this->usr_view_id = null;
        $this->table = '';
        $this->id_field = '';
        $this->id_from_field = '';
        $this->id_to_field = '';
        $this->id_link_field = '';
        $this->name_field = '';
        $this->query_name = '';
        $this->par_values = [];
        $this->par_types = [];
        $this->par_use_link = [];
        $this->par_named = [];
        $this->field_lst = [];
        $this->usr_field_lst = [];
        $this->usr_num_field_lst = [];
        $this->usr_bool_field_lst = [];
        $this->usr_only_field_lst = [];
        $this->join_field = '';
        $this->join2_field = '';
        $this->join3_field = '';
        $this->join4_field = '';
        $this->join_to_field = '';
        $this->join2_to_field = '';
        $this->join3_to_field = '';
        $this->join4_to_field = '';
        $this->join_force_rename = false;
        $this->join2_force_rename = false;
        $this->join3_force_rename = false;
        $this->join4_force_rename = false;
        $this->join_select_field = '';
        $this->join2_select_field = '';
        $this->join3_select_field = '';
        $this->join4_select_field = '';
        $this->join_select_id = 0;
        $this->join2_select_id = 0;
        $this->join3_select_id = 0;
        $this->join4_select_id = 0;
        $this->join_field_lst = [];
        $this->join2_field_lst = [];
        $this->join3_field_lst = [];
        $this->join4_field_lst = [];
        $this->join_usr_par_name = '';
        $this->join_usr_field_lst = [];
        $this->join2_usr_field_lst = [];
        $this->join3_usr_field_lst = [];
        $this->join4_usr_field_lst = [];
        $this->join_usr_num_field_lst = [];
        $this->join2_usr_num_field_lst = [];
        $this->join3_usr_num_field_lst = [];
        $this->join4_usr_num_field_lst = [];
        $this->join_usr_count_field_lst = [];
        $this->join_type = '';
        $this->join2_type = '';
        $this->join3_type = '';
        $this->join4_type = '';
        $this->join_usr_query = false;
        $this->join2_usr_query = false;
        $this->join3_usr_query = false;
        $this->join4_usr_query = false;
        $this->join_usr_added = false;
        $this->all_query = false;
        $this->usr_query = false;
        $this->usr_only_query = false;
        $this->fields = '';
        $this->from = '';
        $this->join = '';
        $this->where = '';
        $this->order = '';
        $this->page = '';
        $this->end = '';
    }

    /*
     * set and get
     */


    function retry_delay(): int
    {
        $cfg = new config();
        if ($this->reconnect_delay = 0) {
            $this->reconnect_delay = $cfg->get(config::DB_RETRY_MIN);
        } else {
            $max_delay = $cfg->get(config::DB_RETRY_MAX);
            if ($this->reconnect_delay * 2 < $max_delay) {
                $this->reconnect_delay = $this->reconnect_delay * 2;
            }
        }
        return $this->reconnect_delay;
    }


    /*
     * open/close the connection to MySQL
     */

    /**
     * open the database link
     * @return bool true if the database has successfully been connected
     */
    function open(): bool
    {
        log_debug();

        $result = false;
        if ($this->db_type == sql_db::POSTGRES) {
            try {
                $this->postgres_link = pg_connect('host=localhost dbname=zukunft user=' . SQL_DB_USER . ' password=' . SQL_DB_PASSWD);
                $result = true;
            } catch (Exception $e) {
                log_fatal('Cannot connect to database due to ' . $e->getMessage(), 'sql_db open');
            }
        } elseif ($this->db_type == sql_db::MYSQL) {
            $this->mysql = mysqli_connect('localhost', SQL_DB_USER_MYSQL, SQL_DB_PASSWD_MYSQL, 'zukunft') or die('Could not connect: ' . mysqli_error($this->mysql));
            $result = true;
        } else {
            log_fatal('Database type ' . $this->db_type . ' not yet implemented', 'sql_db open');
        }

        return $result;
    }

    /**
     * retry to open the database many times to write a log message
     * @param string $msg_text
     * @param string $msg_description
     * @param string $function_name
     * @param string $function_trace
     * @param user|null $usr
     * @return bool
     */
    function open_with_retry(
        string $msg_text,
        string $msg_description = '',
        string $function_name = '',
        string $function_trace = '',
        ?user  $usr = null): bool
    {
        $result = $this->open();
        while (!$result) {
            log_fatal('No database connection to write' . $msg_text,
                $function_name,
                $msg_description,
                $function_trace,
                $usr);
            sleep($this->retry_delay());
            $result = $this->open();
        }
        return $result;
    }

    /**
     * just to have all sql in one library
     */
    function close(): void
    {
        if ($this->db_type == sql_db::POSTGRES) {
            // TODO null check can be removed once the type declaration is set to PgSql\Connection using php 8.1
            if ($this->postgres_link != null) {
                pg_close($this->postgres_link);
                $this->postgres_link = null;
            }
        } elseif ($this->db_type == sql_db::MYSQL) {
            mysqli_close($this->mysql);
        } else {
            log_err('Database type ' . $this->db_type . ' not yet implemented');
        }


        log_debug("done");
    }

    /**
     * create the technical database user and the database structure for the zukunft.com pod
     *
     * @return bool true if the pod setup has been successful
     */
    function setup(): bool
    {
        $result = false;
        // ask the user for the database server, admin user and pw
        $db_server = 'localhost';
        $db_admin_user = 'postgres';
        $db_admin_password = 'xxx';
        // connect with db admin user
        $this->postgres_link = pg_connect('host=' . $db_server . ' user=' . $db_admin_user . ' password=' . $db_admin_password);
        // create zukunft user
        $sql_name = 'db_setup_create_role';
        $sql = resource_file('db/setup/postgres/db_create_user.sql');
        try {
            $sql_result = $this->exe($sql);
            if (!$sql_result) {
                // show the error message direct to the setup user because database does not yet exist
                echo 'ERROR: creation of the technical pod user failed ';
                echo 'due to ' . $sql_result;
            }
        } catch (Exception $e) {
            // show the error message direct to the setup user because database does not yet exist
            echo 'FATAL ERROR: creation of the technical pod user failed ';
            echo 'due to ' . $e->getMessage();
        }
        $this->close();
        // connect with zukunft user
        $conn_str = 'host=' . $db_server . ' dbname=postgres user=' . SQL_DB_USER . ' password=' . SQL_DB_PASSWD;
        $this->postgres_link = pg_connect($conn_str);
        if ($this->postgres_link !== false) {
            $sql = resource_file('db/setup/postgres/db_create_database.sql');
            try {
                $sql_result = $this->exe($sql);
                if (!$sql_result) {
                    // show the error message direct to the setup user because database does not yet exist
                    echo 'ERROR: creation of the database failed ';
                    echo 'due to ' . $sql_result;
                }
            } catch (Exception $e) {
                // show the error message direct to the setup user because database does not yet exist
                echo 'FATAL ERROR: creation of the database failed ';
                echo 'due to ' . $e->getMessage();
            }
        }
        $this->close();
        // connect with zukunft user
        $conn_str = 'host=' . $db_server . ' dbname=zukunft user=' . SQL_DB_USER . ' password=' . SQL_DB_PASSWD;
        $this->postgres_link = pg_connect($conn_str);
        $db_tmp = new sql_db();
        if ($this->postgres_link !== false) {
            $sql = resource_file(DB_RES_PATH . DB_SETUP_PATH . $db_tmp->path(sql_db::POSTGRES) . DB_SETUP_SQL_FILE);
            try {
                $sql_result = $this->exe($sql);
                if (!$sql_result) {
                    // show the error message direct to the setup user because database does not yet exist
                    echo 'ERROR: creation of the database failed ';
                    echo 'due to ' . $sql_result;
                }
            } catch (Exception $e) {
                // show the error message direct to the setup user because database does not yet exist
                echo 'FATAL ERROR: creation of the database failed ';
                echo 'due to ' . $e->getMessage();
            }
            $result = true;
        }
        // create the tables and view
        return $result;
    }

    /**
     * create the database tables and fill it with the essential data
     * TODO remove or secure before moving to PROD
     *
     * @return user_message true if the database table have been created successful
     */
    function setup_db(): user_message
    {
        $html = new html_base();
        $usr_msg = new user_message();

        // create the tables, db indexes and foreign keys
        $sql = resource_file(DB_RES_PATH . DB_SETUP_PATH . $this->path(sql_db::POSTGRES) . DB_SETUP_SQL_FILE);
        try {
            $html->echo('Run db setup sql script');
            $sql_result = $this->exe_script($sql);
            // TODO review
            //if ($sql_result) {
            //    $usr_msg->add_message($sql_result);
            // }
        } catch (Exception $e) {
            $msg = ' creation of the database failed due to ' . $e->getMessage();
            log_fatal($msg, 'setup_db');
            $usr_msg->add_message($msg);
        }

        // fill the tables with the essential data
        if ($usr_msg->is_ok()) {
            $html->echo('Create system users');
            $this->reset_config();
            $this->import_system_users();

            // use the system user for the database updates
            global $usr;
            $usr = new user;
            $usr->load_by_id(SYSTEM_USER_ID);

            // recreate the code link database rows
            $html->echo('Create the code links');
            $this->db_fill_code_links();
            $sys_typ_lst = new type_lists();
            $sys_typ_lst->load($this, $usr);

            // reload the base configuration
            $job = new job($usr);
            $job_id = $job->add(job_type_list::BASE_IMPORT);

            $import = new import_file();
            $this->import_verbs($usr);
            $import->import_base_config($usr);
            $import->import_config($usr);
            $this->db_check_missing_owner();

            // create the test dataset to check the basic write functions
            $t = new all_unit_read_tests();
            $t->set_users();
            $t->create_test_db_entries($t);

            // remove the test dataset for a clean database
            // TODO use the user message object instead of a string
            $cleanup_result = $t->cleanup();
            if (!$cleanup_result) {
                log_err('Cleanup not successful, because ...');
            } else {
                if (!$t->cleanup_check()) {
                    log_err('Cleanup check not successful.');
                }
            }

            // reload the session user parameters
            $usr = new user;
            $usr->get();

            $cfg = new config();
            $cfg->set(config::LAST_CONSISTENCY_CHECK, gmdate(DATE_ATOM), $this);
        }
        return $usr_msg;
    }

    /**
     * @return bool true if the database connection is open
     */
    function connected(): bool
    {
        $result = false;
        if ($this->db_type == sql_db::POSTGRES) {
            if ($this->postgres_link != null) {
                $result = true;
            }
        } elseif ($this->db_type == sql_db::MYSQL) {
            if (isset($this->mysql)) {
                $result = true;
            }
        } else {
            log_fatal('Database type ' . $this->db_type . ' not yet implemented', 'sql_db->connected');
        }
        return $result;
    }

    /**
     * @return bool true if all user sandbox objects have an owner
     */
    function db_check_missing_owner(): bool
    {
        $result = true;

        foreach (sandbox::DB_TYPES as $db_type) {
            $this->set_class($db_type);
            $db_lst = $this->missing_owner();
            if ($db_lst != null) {
                $result = $this->set_default_owner();
            }
        }

        return $result;
    }


    /**
     * fill the database with all rows that have a code id and code linked
     */
    function db_fill_code_links(): void
    {
        // first of all set the database version if not yet done
        $cfg = new config();
        $cfg->check(config::VERSION_DB, PRG_VERSION, $this);

        // get the list of CSV and loop
        foreach (BASE_CODE_LINK_FILES as $csv_file_name) {
            $this->load_db_code_link_file($csv_file_name, $this);
        }

        // set the seq number if needed
        $this->seq_reset(sql_db::TBL_CHANGE_TABLE);
        $this->seq_reset(sql_db::TBL_CHANGE_FIELD);
        $this->seq_reset(sql_db::TBL_CHANGE_ACTION);
    }

    function load_db_code_link_file(string $csv_file_name): void
    {
        global $debug;
        $lib = new library();

        // load the csv
        $csv_path = PATH_BASE_CODE_LINK_FILES . $csv_file_name . BASE_CODE_LINK_FILE_TYPE;

        $row = 1;
        $table_name = $csv_file_name;
        // TODO change table names to singular form
        if ($table_name == 'sys_log_status') {
            $db_type = $table_name;
        } else {
            $db_type = substr($table_name, 0, -1);
        }
        // TODO ignore empty rows
        // TODO ignore comma within text e.g. allow 'one, two and three'
        log_debug('load "' . $table_name . '"', $debug - 6);
        if (($handle = fopen($csv_path, "r")) !== FALSE) {
            $continue = true;
            $id_col_name = '';
            $col_names = array();
            while (($data = fgetcsv($handle, 0, ",", "'")) !== FALSE) {
                if ($continue) {
                    if ($row == 1) {
                        // check if the csv column names match the table names
                        if (!$this->check_column_names($table_name, $lib->array_trim($data))) {
                            $continue = false;
                        } else {
                            $col_names = $lib->array_trim($data);
                        }
                        // check if the first column name is the id col
                        $id_col_name = $data[0];
                        if (!str_ends_with($id_col_name, sql_db::FLD_ID)) {
                            $continue = false;
                        }
                    } else {
                        // init row update
                        $update_col_names = array();
                        $update_col_values = array();
                        // get the row id which is expected to be always in the first column
                        $id = $data[0];
                        // check if the row id exists
                        $qp = $this->db_fill_code_link_sql($table_name, $id_col_name, $id);
                        $db_row = $this->get1($qp);
                        // check if the db row needs to be added
                        if ($db_row == null) {
                            // add the row
                            for ($i = 0; $i < count($data); $i++) {
                                $update_col_names[] = $col_names[$i];
                                $update_col_values[] = trim($data[$i]);
                            }
                            $this->set_class($db_type);
                            $this->insert_old($update_col_names, $update_col_values);
                        } else {
                            // check, which values need to be updates
                            for ($i = 1; $i < count($data); $i++) {
                                $col_name = $col_names[$i];
                                if (array_key_exists($col_name, $db_row)) {
                                    $db_value = $db_row[$col_name];
                                    if ($db_value != trim($data[$i]) and trim($data[$i]) != 'NULL') {
                                        $update_col_names[] = $col_name;
                                        $update_col_values[] = trim($data[$i]);
                                    }
                                } else {
                                    log_err('Column check did not work for ' . $col_name);
                                }
                            }
                            // update the values is needed
                            if (count($update_col_names) > 0) {
                                $this->set_class($db_type);
                                $this->update_old($id, $update_col_names, $update_col_values);
                            }
                        }
                    }
                }
                $row++;
            }
            fclose($handle);
        }
    }

    function db_fill_code_link_sql(string $table_name, string $id_col_name, int $id): sql_par
    {
        $qp = new sql_par($this::class);
        $qp->name .= 'fill_' . $id_col_name;
        $qp->sql = "PREPARE " . $qp->name . " (int) AS select * from " . $table_name . " where " . $id_col_name . " = $1;";
        $qp->par = array($id);
        return $qp;
    }

    /*
     * sql creator functions
     */

    /*
     * basic interface function for the private class parameter
     */

    /**
     * define the table that should be used for the next select, insert, update or delete statement
     * resets all previous db query settings such as fields, user_fields, so this should be the first statement when defining a database query
     * TODO check that this is always called directly before the query is created, so that
     * TODO should be deprecated and the sql creator should be used instead
     *
     * @param string $class is a string that is used to select the table name, the id field and the name field
     * @param bool $usr_table if it is true the user table instead of the standard table is used
     * @return bool true if setting the type was successful
     */
    function set_class(string $class, bool $usr_table = false, string $ext = ''): bool
    {
        global $usr;

        $this->reset();
        $this->class = $class;
        if ($usr == null) {
            $this->set_usr(SYSTEM_USER_ID); // if the session user is not yet set, use the system user id to test the database compatibility
        } else {
            if ($usr->id() == null) {
                $this->set_usr(0); // fallback for special cases
            } else {
                $this->set_usr($usr->id()); // by default use the session user id
            }
        }
        $this->set_table($usr_table, $ext);
        $this->set_id_field();
        $this->set_name_field();
        return true;
    }

    function get_class(): string
    {
        return $this->class;
    }

    /**
     * set the user id of the user who has requested the database access
     * by default the user also should see his/her/its data
     */
    function set_usr(int $usr_id): void
    {
        $this->usr_id = $usr_id;
        $this->usr_view_id = $usr_id;
    }

    /**
     * add a parameter for a prepared query
     * @param sql_par_type $par_type the SQL parameter type used e.g. for Postgres as int or text
     * @param string $value the int, float value or text value that is used for the concrete execution of the query
     * @param bool $named true if the parameter name is already used
     * @param bool $use_link true if the parameter should be applied on the linked table
     */
    function add_par(
        sql_par_type $par_type,
        string       $value,
        bool         $named = false,
        bool         $use_link = false): void
    {
        $this->par_types[] = $par_type;
        $this->par_values[] = $value;
        $this->par_named[] = $named;
        $this->par_use_link[] = $use_link;
    }

    /**
     * get the SQL parameter placeholder in the used SQL dialect
     *
     * @param int $pos to get the placeholder of another position than the last
     * @return string the SQL var name for the latest added query parameter
     */
    function par_name(int $pos = 0): string
    {
        // if the position is not given use the last parameter added
        if ($pos == 0) {
            $pos = count($this->par_types);
        }

        // remember that the parameter name has already been used
        $this->par_named[$pos - 1] = true;

        // create the parameter placeholder for the SQL dialect
        if ($this->db_type == sql_db::MYSQL) {
            return '?';
        } elseif ($this->db_type == sql_db::POSTGRES) {
            return '$' . $pos;
        } else {
            return '?';
        }
    }

    /**
     * get the SQL parameter value of a parameter position
     *
     * @param int $pos to get the placeholder of another position than the last
     * @return string the SQL var const
     */
    function par_value(int $pos = 0): string
    {
        // if the position is not given use the last parameter added
        if ($pos == 0) {
            $pos = count($this->par_types);
        }

        return $this->par_values[$pos - 1];
    }

    function set_name(string $query_name): void
    {
        // the query name cannot be longer than 62 chars
        $this->query_name = substr($query_name, 0, 62);
    }

    /**
     * define the fields that should be returned in a select query
     */
    function set_fields($field_lst): void
    {
        $this->field_lst = $field_lst;
    }

    /**
     * define the fields that are used to link two objects
     * the id_link_field is the type e.g. the verb for a word link
     */
    function set_link_fields($id_from_field, $id_to_field, $id_link_field = ''): void
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
    function set_join_fields(array  $join_field_lst,
                             string $join_type,
                             string $join_field = '',
                             string $join_to_field = '',
                             string $join_select_field = '',
                             int    $join_select_id = 0): void
    {
        $join_type = $this->class_to_name($join_type);
        // fill up the join field places or add settings to a matching join link
        if ($this->join_type == '' and !$this->join_force_rename
            or (($this->join_field == $join_field and $join_field != '')
                and ($this->join_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join_type = $join_type;
            $this->join_field_lst = $join_field_lst;
            $this->join_field = $join_field;
            $this->join_to_field = $join_to_field;
            $this->join_select_field = $join_select_field;
            $this->join_select_id = $join_select_id;
            $this->join_usr_query = false;
        } elseif ($this->join2_type == '' and !$this->join2_force_rename
            or (($this->join2_field == $join_field and $join_field != '')
                and ($this->join2_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join2_type = $join_type;
            $this->join2_field_lst = $join_field_lst;
            $this->join2_field = $join_field;
            $this->join2_to_field = $join_to_field;
            $this->join2_select_field = $join_select_field;
            $this->join2_select_id = $join_select_id;
            $this->join2_usr_query = false;
        } elseif ($this->join3_type == '' and !$this->join3_force_rename
            or (($this->join3_field == $join_field and $join_field != '')
                and ($this->join3_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join3_type = $join_type;
            $this->join3_field_lst = $join_field_lst;
            $this->join3_field = $join_field;
            $this->join3_to_field = $join_to_field;
            $this->join3_select_field = $join_select_field;
            $this->join3_select_id = $join_select_id;
            $this->join3_usr_query = false;
        } elseif ($this->join4_type == '' and !$this->join4_force_rename
            or (($this->join4_field == $join_field and $join_field != '')
                and ($this->join4_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join4_type = $join_type;
            $this->join4_field_lst = $join_field_lst;
            $this->join4_field = $join_field;
            $this->join4_to_field = $join_to_field;
            $this->join4_select_field = $join_select_field;
            $this->join4_select_id = $join_select_id;
            $this->join4_usr_query = false;
        } else {
            log_err('Max four table joins expected on version ' . PRG_VERSION);
        }
    }

    /**
     * similar to set_join_fields but for usr specific fields
     */
    function set_join_usr_fields(array  $join_field_lst,
                                 string $join_type,
                                 string $join_field = '',
                                 string $join_to_field = '',
                                 bool   $force_rename = false): void
    {
        $join_type = $this->class_to_name($join_type);
        // fill up the join field places or add settings to a matching join link
        // e.g. add the user fields to an existing not user specific join
        if ($this->join_type == ''
            or (($this->join_field == $join_field or $join_field == '')
                and ($this->join_to_field == $join_to_field or $join_to_field == ''))) {
            $this->join_type = $join_type;
            $this->join_usr_field_lst = $join_field_lst;
            $this->join_field = $join_field;
            $this->join_to_field = $join_to_field;
            $this->join_force_rename = $force_rename;
            $this->join_usr_query = true;
        } elseif ($this->join2_type == ''
            or (($this->join2_field == $join_field or $join_field == '')
                and ($this->join2_to_field == $join_to_field or $join_to_field == ''))) {
            $this->join2_type = $join_type;
            $this->join2_usr_field_lst = $join_field_lst;
            $this->join2_field = $join_field;
            $this->join2_to_field = $join_to_field;
            $this->join2_force_rename = $force_rename;
            $this->join2_usr_query = true;
        } elseif ($this->join3_type == ''
            or (($this->join3_field == $join_field or $join_field == '')
                and ($this->join3_to_field == $join_to_field or $join_to_field == ''))) {
            $this->join3_type = $join_type;
            $this->join3_usr_field_lst = $join_field_lst;
            $this->join3_field = $join_field;
            $this->join3_to_field = $join_to_field;
            $this->join3_force_rename = $force_rename;
            $this->join3_usr_query = true;
        } elseif ($this->join4_type == ''
            or (($this->join4_field == $join_field or $join_field == '')
                and ($this->join4_to_field == $join_to_field or $join_to_field == ''))) {
            $this->join4_type = $join_type;
            $this->join4_usr_field_lst = $join_field_lst;
            $this->join4_field = $join_field;
            $this->join4_to_field = $join_to_field;
            $this->join4_force_rename = $force_rename;
            $this->join4_usr_query = true;
        } else {
            log_err('Max four table joins expected in version ' . PRG_VERSION);
        }
    }

    function set_join_usr_num_fields(array  $join_field_lst,
                                     string $join_type,
                                     string $join_field = '',
                                     string $join_to_field = '',
                                     bool   $force_rename = false): void
    {
        $join_type = $this->class_to_name($join_type);
        // fill up the join field places or add settings to a matching join link
        // e.g. add the user fields to an existing not user specific join
        if ($this->join_type == ''
            or (($this->join_field == $join_field and $join_field != '')
                and ($this->join_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join_type = $join_type;
            $this->join_usr_num_field_lst = $join_field_lst;
            $this->join_field = $join_field;
            $this->join_to_field = $join_to_field;
            $this->join_force_rename = $force_rename;
            $this->join_usr_query = true;
        } elseif ($this->join2_type == ''
            or (($this->join2_field == $join_field and $join_field != '')
                and ($this->join2_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join2_type = $join_type;
            $this->join2_usr_num_field_lst = $join_field_lst;
            $this->join2_field = $join_field;
            $this->join2_to_field = $join_to_field;
            $this->join2_force_rename = $force_rename;
            $this->join2_usr_query = true;
        } elseif ($this->join3_type == ''
            or (($this->join3_field == $join_field and $join_field != '')
                and ($this->join3_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join3_type = $join_type;
            $this->join3_usr_num_field_lst = $join_field_lst;
            $this->join3_field = $join_field;
            $this->join3_to_field = $join_to_field;
            $this->join3_force_rename = $force_rename;
            $this->join3_usr_query = true;
        } elseif ($this->join4_type == ''
            or (($this->join4_field == $join_field and $join_field != '')
                and ($this->join4_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join4_type = $join_type;
            $this->join4_usr_num_field_lst = $join_field_lst;
            $this->join4_field = $join_field;
            $this->join4_to_field = $join_to_field;
            $this->join4_force_rename = $force_rename;
            $this->join4_usr_query = true;
        } else {
            log_err('Max four table joins expected in version ' . PRG_VERSION);
        }
    }

    function set_join_usr_count_fields(array  $join_field_lst,
                                       string $join_type): void
    {
        $join_type = $this->class_to_name($join_type);
        if ($this->join_type == '') {
            $this->join_type = $join_type;
            $this->join_usr_count_field_lst = $join_field_lst;
            $this->join_usr_query = true;
        } else {
            log_err('Max one table count joins expected in version ' . PRG_VERSION);
        }
    }

    function class_to_name(string $class): string
    {
        $lib = new library();
        return $lib->class_to_name($class);
    }

    /**
     * define that the SQL statement should return the standard value and the user specific changes of all users
     */
    function set_all(): void
    {
        $this->all_query = true;
    }

    /**
     * set the SQL statement for the user sandbox fields that should be returned in a select query which can be user specific
     */
    function set_usr_fields($usr_field_lst): void
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_field_lst = $usr_field_lst;
        $this->set_user_join();
    }

    function set_usr_num_fields($usr_field_lst): void
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_num_field_lst = $usr_field_lst;
        $this->set_user_join();
    }

    function set_usr_count_fields($usr_field_lst): void
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_num_field_lst = $usr_field_lst;
        $this->set_user_join();
    }

    function set_usr_bool_fields($usr_field_lst): void
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_bool_field_lst = $usr_field_lst;
        $this->set_user_join();
    }

    function set_usr_only_fields($field_lst): void
    {
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->usr_only_field_lst = $field_lst;
        $this->set_user_join();
    }

    private function set_field_sep(): void
    {
        if ($this->fields != '') {
            $this->fields .= ', ';
        }
    }

    /**
     * interface function for sql_usr_field
     * @param string $field the field name of the user specific field
     * @param string $field_format the enum of the sql field type e.g. INT
     * @param string $stb_tbl the table prefix for the table with the default values for all users
     * @param string $usr_tbl the table prefix for the table with the user specific values
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     */
    function get_usr_field(
        string $field, string $stb_tbl = sql_db::STD_TBL, string $usr_tbl = sql_db::USR_TBL,
        string $field_format = sql_db::FLD_FORMAT_TEXT, string $as = ''): string
    {
        return $this->sql_usr_field($field, $field_format, $stb_tbl, $usr_tbl, $as);
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and text fields
     * @param string $field the field name of the user specific field
     * @param string $stb_tbl the table prefix for the table with the default values for all users
     * @param string $usr_tbl the table prefix for the table with the user specific values
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     */
    private function set_field_usr_text(
        string $field, string $stb_tbl = sql_db::STD_TBL, string $usr_tbl = sql_db::USR_TBL, string $as = ''): void
    {
        $this->fields .= $this->sql_usr_field($field, sql_db::FLD_FORMAT_TEXT, $stb_tbl, $usr_tbl, $as);
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and number fields
     * @param string $field the field name of the user specific field
     * @param string $stb_tbl the table prefix for the table with the default values for all users
     * @param string $usr_tbl the table prefix for the table with the user specific values
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     */
    private function set_field_usr_num(
        string $field, string $stb_tbl = sql_db::STD_TBL, string $usr_tbl = sql_db::USR_TBL, string $as = ''): void
    {
        $this->fields .= $this->sql_usr_field($field, sql_db::FLD_FORMAT_VAL, $stb_tbl, $usr_tbl, $as);
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and number fields
     * @param string $field the field name of the user specific field
     * @param string $stb_tbl the table prefix for the table with the default values for all users
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     */
    private function set_field_usr_count(
        string $field, string $stb_tbl = sql_db::LNK_TBL, string $as = ''): void
    {
        $this->from = ' FROM ( SELECT ' . $this->fields . ', count(' . $stb_tbl . '.' . $field . ') AS ' . $as;
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and boolean / tinyint fields
     * @param string $field the field name of the user specific field
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     */
    private function set_field_usr_bool(
        string $field, string $as = ''): void
    {
        $this->fields .= $this->sql_usr_field(
            $field, sql_db::FLD_FORMAT_BOOL, sql_db::STD_TBL, sql_db::USR_TBL, $as);
    }

    /**
     * create the sql statement to get the user specific value if it is set or the value for all users
     * uses $db_type is the SQL database type which is in this case independent of the class setting to be able to use it anywhere
     * @param string $field the field name of the user specific field
     * @param string $field_format the enum of the sql field type e.g. INT
     * @param string $stb_tbl the table prefix for the table with the default values for all users
     * @param string $usr_tbl the table prefix for the table with the user specific values
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     * @return string the SQL statement for a field taken from the user sandbox table or from the table with the common values
     */
    private function sql_usr_field(
        string $field, string $field_format, string $stb_tbl, string $usr_tbl, string $as = ''): string
    {
        $result = '';
        if ($as == '') {
            $as = $field;
        }
        if ($this->db_type == sql_db::POSTGRES) {
            if ($field_format == sql_db::FLD_FORMAT_TEXT) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " <> '' IS NOT TRUE) THEN "
                    . $stb_tbl . "." . $field . " ELSE " . $usr_tbl . "." . $field . " END AS " . $as;
            } elseif ($field_format == sql_db::FLD_FORMAT_VAL) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " IS NULL) THEN "
                    . $stb_tbl . "." . $field . " ELSE " . $usr_tbl . "." . $field . " END AS " . $as;
            } elseif ($field_format == sql_db::FLD_FORMAT_BOOL) {
                $result = " CASE WHEN (" . $usr_tbl . "." . $field . " IS NULL) THEN COALESCE("
                    . $stb_tbl . "." . $field . ",0) ELSE COALESCE(" . $usr_tbl . "." . $field . ",0) END AS " . $as;
            } else {
                log_err('Unexpected field format ' . $field_format);
            }
        } elseif ($this->db_type == sql_db::MYSQL) {
            if ($field_format == sql_db::FLD_FORMAT_TEXT or $field_format == sql_db::FLD_FORMAT_VAL) {
                $result = '         IF(' . $usr_tbl . '.' . $field . ' IS NULL, '
                    . $stb_tbl . '.' . $field . ', ' . $usr_tbl . '.' . $field . ')    AS ' . $as;
            } elseif ($field_format == sql_db::FLD_FORMAT_BOOL) {
                $result = '         IF(' . $usr_tbl . '.' . $field . ' IS NULL, COALESCE('
                    . $stb_tbl . '.' . $field . ',0), COALESCE(' . $usr_tbl . '.' . $field . ',0))    AS ' . $as;
            } else {
                log_err('Unexpected field format ' . $field_format);
            }
        } else {
            log_err('Unexpected database type ' . $this->db_type);
        }
        return $result;
    }

    private function set_field_statement($has_id): void
    {
        $lib = new library();
        if ($has_id) {
            // add the fields that part of all standard tables so id and name on top of the field list
            $field_lst = [];
            $usr_field_lst = [];
            $field_lst[] = $this->id_field;
            if ($this->usr_query) {
                // user can change the name of an object, that's why the target field list is either $usr_field_lst or $field_lst
                if (!in_array($this->class, sql_db::DB_TYPES_NOT_NAMED)) {
                    $usr_field_lst[] = $this->name_field;
                }
                if (!$this->all_query) {
                    $field_lst[] = user::FLD_ID;
                }
            } else {
                if (!in_array($this->class, sql_db::DB_TYPES_NOT_NAMED)) {
                    $field_lst[] = $this->name_field;
                }
            }
            // user cannot change the links like they can change the name, instead a link is removed and another link is created
            if (in_array($this->class, sql_db::DB_TYPES_LINK)) {
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
                if (!in_array($field, $field_lst)) {
                    $field_lst[] = $field;
                }
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
            if (is_array($field)) {
                $fld_lst = array();
                foreach ($field as $fld) {
                    $fld_lst[] = $this->name_sql_esc($fld);
                }
                $field = $fld_lst;
            } else {
                $field = $this->name_sql_esc($field);
            }
            $this->set_field_sep();
            if ($this->usr_query or $this->join_type != '') {
                // TODO extract the common part
                if (is_array($field)) {
                    if (!is_array($this->id_field)) {
                        log_warning('The id field ' . $this->id_field . ' is expected to be an array');

                    } else {
                        if (count($field) != count($this->id_field)) {
                            log_warning('The number of id fields ' . $this->id_field . ' does not match the number of ids');
                        } else {
                            $pos = 0;
                            foreach ($field as $fld) {
                                $this->fields .= ' ' . sql_db::STD_TBL . '.' . $fld;
                                if ($field == $this->id_field) {
                                    // add the user sandbox id for user sandbox queries to find out if the user sandbox has already been created
                                    if ($this->all_query) {
                                        if ($this->fields != '') {
                                            $this->fields .= ', ';
                                        }
                                        $this->fields .= ' ' . sql_db::USR_TBL . '.' . user::FLD_ID;
                                    } else {
                                        if ($this->usr_query) {
                                            if ($this->fields != '') {
                                                $this->fields .= ', ';
                                            }
                                            $this->fields .= ' ' . sql_db::USR_TBL . '.' . $fld . ' AS ' . sql_db::USER_PREFIX . $this->id_field[$pos];
                                        }
                                    }
                                }
                                $pos++;
                            }
                        }
                    }
                } else {
                    $this->fields .= ' ' . sql_db::STD_TBL . '.' . $field;
                    if ($field == $this->id_field) {
                        // add the user sandbox id for user sandbox queries to find out if the user sandbox has already been created
                        if ($this->all_query) {
                            if ($this->fields != '') {
                                $this->fields .= ', ';
                            }
                            $this->fields .= ' ' . sql_db::USR_TBL . '.' . user::FLD_ID;
                        } else {
                            if ($this->usr_query) {
                                if ($this->fields != '') {
                                    $this->fields .= ', ';
                                }
                                $this->fields .= ' ' . sql_db::USR_TBL . '.' . $field . ' AS ' . sql_db::USER_PREFIX . $this->id_field;
                            }
                        }
                    }
                }
            } else {
                $this->fields .= ' ' . $field;
            }
        }

        // select the owner of the standard values in case of an overview query
        if ($this->all_query) {
            if ($this->fields != '') {
                $this->fields .= ', ';
            }
            $this->fields .= ' ' . sql_db::STD_TBL . '.' . user::FLD_ID . ' AS owner_id';
        }

        // add join fields
        foreach ($this->join_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->fields .= ' ' . sql_db::LNK_TBL . '.' . $field_esc;
            if ($this->join_force_rename) {
                $this->fields .= ' AS ' . $this->name_sql_esc($field . '1');
            } elseif ($this->usr_query and $this->join_usr_query) {
                if ($this->fields != '') {
                    $this->fields .= ', ';
                }
                $this->fields .= ' ' . sql_db::ULK_TBL . '.' . $field_esc;
            }
        }

        // add second join fields
        foreach ($this->join2_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->fields .= ' ' . sql_db::LNK2_TBL . '.' . $field_esc;
            if ($this->join2_force_rename) {
                $this->fields .= ' AS ' . $this->name_sql_esc($field . '2');
            } elseif ($this->usr_query and $this->join2_usr_query) {
                if ($this->fields != '') {
                    $this->fields .= ', ';
                }
                $this->fields .= ' ' . sql_db::ULK2_TBL . '.' . $field_esc;
            }
        }

        // add third join fields
        foreach ($this->join3_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->fields .= ' ' . sql_db::LNK3_TBL . '.' . $field_esc;
            if ($this->join3_force_rename) {
                $this->fields .= ' AS ' . $this->name_sql_esc($field . '3');
            } elseif ($this->usr_query and $this->join3_usr_query) {
                if ($this->fields != '') {
                    $this->fields .= ', ';
                }
                $this->fields .= ' ' . sql_db::ULK3_TBL . '.' . $field_esc;
            }
        }

        // add fourth join fields
        foreach ($this->join4_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->fields .= ' ' . sql_db::LNK4_TBL . '.' . $field_esc;
            if ($this->join4_force_rename) {
                $this->fields .= ' AS ' . $this->name_sql_esc($field . '4');
            } elseif ($this->usr_query and $this->join4_usr_query) {
                if ($this->fields != '') {
                    $this->fields .= ', ';
                }
                $this->fields .= ' ' . sql_db::ULK4_TBL . '.' . $field_esc;
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
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            if ($this->join_force_rename) {
                $this->set_field_usr_text($field_esc, sql_db::LNK_TBL, sql_db::ULK_TBL, $this->name_sql_esc($field . '1'));
            } else {
                $this->set_field_usr_text($field_esc, sql_db::LNK_TBL, sql_db::ULK_TBL);
            }
        }

        // add user specific numeric join fields
        foreach ($this->join_usr_num_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            if ($this->join_force_rename) {
                $this->set_field_usr_num($field_esc, sql_db::LNK_TBL, sql_db::ULK_TBL, $this->name_sql_esc($field . '1'));
            } else {
                $this->set_field_usr_num($field_esc, sql_db::LNK_TBL, sql_db::ULK_TBL);
            }
        }

        // add user specific second join fields
        foreach ($this->join2_usr_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_text($field_esc, sql_db::LNK2_TBL, sql_db::ULK2_TBL, $this->name_sql_esc($field . '2'));
        }

        // add user specific numeric second join fields
        foreach ($this->join2_usr_num_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_num($field_esc, sql_db::LNK2_TBL, sql_db::ULK2_TBL, $this->name_sql_esc($field . '2'));
        }

        // add user specific third join fields
        foreach ($this->join3_usr_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_text($field_esc, sql_db::LNK3_TBL, sql_db::ULK3_TBL, $this->name_sql_esc($field . '3'));
        }

        // add user specific numeric third join fields
        foreach ($this->join3_usr_num_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_num($field_esc, sql_db::LNK3_TBL, sql_db::ULK3_TBL, $this->name_sql_esc($field . '3'));
        }

        // add user specific fourth join fields
        foreach ($this->join4_usr_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_text($field_esc, sql_db::LNK4_TBL, sql_db::ULK4_TBL, $this->name_sql_esc($field . '4'));
        }

        // add user specific numeric fourth join fields
        foreach ($this->join4_usr_num_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_sep();
            $this->set_field_usr_num($field_esc, sql_db::LNK4_TBL, sql_db::ULK4_TBL, $this->name_sql_esc($field . '4'));
        }

        // add user specific count join fields
        foreach ($this->join_usr_count_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $this->set_field_usr_count($field_esc, sql_db::LNK_TBL, $this->name_sql_esc($field . '_count'));
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
    function get_table_name(string $class): string
    {
        $lib = new library();
        $type = $lib->class_to_name($class);

        // set the standard table name based on the type
        $result = $type . "s";
        // exceptions from the standard table for 'nicer' names
        if ($result == 'value_time_seriess') {
            $result = 'values_time_series';
        }
        if ($result == 'user_value_time_seriess') {
            $result = 'user_values_time_series';
        }
        if ($result == 'value_ts_datas') {
            $result = 'value_ts_data';
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
        if ($result == 'job_typess') {
            $result = 'job_types';
        }
        if ($result == 'component_typess') {
            $result = 'component_types';
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
    function get_table_name_esc(string $class): string
    {
        return $this->name_sql_esc($this->get_table_name($class));
    }

    /**
     * set the table name based on the already set type / class
     * TODO use always the user table flag
     * @param bool $usr_table
     * @param string $ext the table name extension e.g. to switch between standard and prime values
     * @return void
     */
    private function set_table(bool $usr_table = false, string $ext = ''): void
    {
        global $debug;

        $lib = new library();
        $type = $lib->class_to_name($this->class);

        if ($usr_table) {
            $this->table = sql_db::USER_PREFIX . $this->get_table_name($type);
            $this->usr_only_query = true;
        } else {
            $this->table = $this->get_table_name($type);
            $this->table .= $ext;
        }
        log_debug('to "' . $this->table . '"', $debug - 20);
    }

    function get_id_field_name(string $type): string
    {
        $lib = new library();

        // exceptions for user overwrite tables
        // but not for the user type table, because this is not part of the sandbox tables
        if (str_starts_with($type, sql_db::TBL_USER_PREFIX)
            and $type != sql_db::TBL_USER_TYPE
            and $type != sql_db::TBL_USER_OFFICIAL_TYPE
            and $type != sql_db::TBL_USER_PROFILE) {
            $type = $lib->str_right_of($type, sql_db::TBL_USER_PREFIX);
        }
        $result = $type . sql_db::FLD_EXT_ID;
        // exceptions for nice english
        if ($result == 'sys_log_statuss_id') {
            $result = 'sys_log_status_id';
        }
        if ($result == 'blocked_ip_id') {
            $result = 'ip_range_id';
        }
        return $result;
    }

    function set_id_field(string|array $given_name = ''): void
    {
        $lib = new library();
        $type = $lib->class_to_name($this->class);

        if ($given_name != '') {
            $this->id_field = $given_name;
        } else {
            $this->id_field = $this->get_id_field_name($type);
        }
        // exceptions to be adjusted
        if ($this->id_field == 'blocked_ips_id') {
            $this->id_field = 'ip_range_id';
        }
    }

    function get_name_field(string $type): string
    {
        global $debug;

        $lib = new library();

        // exceptions for user overwrite tables
        if (str_starts_with($type, sql_db::TBL_USER_PREFIX)) {
            $type = $lib->str_right_of($type, sql_db::TBL_USER_PREFIX);
        }
        $result = $type . '_name';
        // exceptions to be adjusted
        if ($result == 'link_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'system_time_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'phrase_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'view_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'component_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'position_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'element_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'sys_log_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'formula_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'formula_link_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'ref_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'source_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'share_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'protection_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'profile_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'sys_log_status_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        if ($result == 'job_type_name') {
            $result = sql::FLD_TYPE_NAME;
        }
        // temp solution until the standard field name for the name field is actually "name" (or something else not object specific)
        if ($result == 'triple_name') {
            $result = 'name';
        }
        log_debug('to "' . $result . '"', $debug - 20);
        return $result;
    }

    private function set_name_field(): void
    {
        global $debug;

        $lib = new library();
        $type = $lib->class_to_name($this->class);

        $result = $this->get_name_field($type);
        log_debug('to "' . $result . '"', $debug - 20);
        $this->name_field = $result;
    }

    /*
     * the main database call function including an automatic error tracking
     * this function should probably be private and not be called from another class
     * instead the function get, insert and update function below should be called
     */

    /**
     * execute an SQL statement on the active database (either Postgres or MySQL)
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
            if (!$sql_result) {
                $result .= $msg . log::MSG_ERR;
            }
        } catch (Exception $e) {
            if ($log_level == sys_log_level::FATAL) {
                log_fatal($msg . log::MSG_ERR_USING . $sql . log::MSG_ERR_BECAUSE . $e->getMessage(), 'exe_try');
            } else {
                $trace_link = log_err($msg . log::MSG_ERR_USING . $sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                $result = $msg . log::MSG_ERR_INTERNAL . $trace_link;
            }
        }
        return $result;
    }

    /**
     * @throws Exception the message that should be shown to the system admin for debugging
     */
    function exe_par(sql_par $qp): string
    {
        return $this->exe_try('', $qp->sql, $qp->name, $qp->par);
    }

    /**
     * execute an prepared SQL statement on the active database (either Postgres or MySQL)
     * similar to exe_try, but without exception handling
     *
     * @param string $sql the sql statement that should be executed
     * @param string $sql_name the unique name of the sql statement
     * @param array $sql_array the values that should be used for executing the precompiled SQL statement
     * @param int $log_level the log level is given by the calling function because after some errors the program may nevertheless continue
     * @return \PgSql\Result|mysqli_result the result of the sql statement
     * @throws Exception the message that should be shown to the system admin for debugging
     *
     * TODO add the writing of potential sql errors to the sys log table to the sql execution
     * TODO includes the user to be able to ask the user for details how the error has been created
     * TODO with php 8 switch to the union return type resource|false
     */
    function exe(
        string $sql,
        string $sql_name = '',
        array  $sql_array = array(),
        int    $log_level = sys_log_level::ERROR
    ): \PgSql\Result|mysqli_result
    {
        global $debug;
        $lib = new library();
        log_debug('"' . $sql . '" with "' . $lib->dsp_array($sql_array) . '" named "' . $sql_name . '" for  user ' . $this->usr_id, $debug - 15);

        // Postgres part
        if ($this->db_type == sql_db::POSTGRES) {
            $result = $this->exe_postgres($sql, $sql_name, $sql_array, $log_level);            // check database connection
        } elseif ($this->db_type == sql_db::MYSQL) {
            $result = $this->exe_mysql($sql, $sql_name, $sql_array, $log_level);            // check database connection
        } else {
            throw new Exception('Unknown database type "' . $this->db_type . '"');
        }

        return $result;
    }

    /**
     * execute directly an SQL script without further prepare
     * @param string $sql the sql script that should be executed
     * @return \PgSql\Result|mysqli_result
     * @throws Exception
     */
    function exe_script(string $sql): \PgSql\Result|mysqli_result
    {
        // execute on the connected database
        if ($this->db_type == sql_db::POSTGRES) {
            $result = pg_query($this->postgres_link, $sql);
        } elseif ($this->db_type == sql_db::MYSQL) {
            $result = mysqli_query($this->mysql, $sql);
        } else {
            throw new Exception('Unknown database type "' . $this->db_type . '"');
        }
        return $result;
    }

    /**
     * execute an change SQL statement on a Postgres database
     * similar to exe, but database specific because the return object differs depending on the database
     *
     * @param string $sql the sql statement that should be executed
     * @param string $sql_name the unique name of the sql statement
     * @param array $sql_array the values that should be used for executing the precompiled SQL statement
     * @param int $log_level the log level is given by the calling function because after some errors the program may nevertheless continue
     * @return \PgSql\Result the message that should be shown to the user if something went wrong or an empty string
     * @throws Exception the message that should be shown to the system admin for debugging
     *
     * TODO switch return type to bool|resource with PHP 8.0
     * TODO add the writing of potential sql errors to the sys log table to the sql execution
     * TODO includes the user to be able to ask the user for details how the error has been created
     * TODO with php 8 switch to the union return type resource|false
     */
    private function exe_postgres(
        string $sql,
        string $sql_name = '',
        array  $sql_array = array(),
        int    $log_level = sys_log_level::ERROR
    ): \PgSql\Result
    {
        global $debug;

        $result = null;

        // check database connection
        if ($this->postgres_link == null) {
            $msg = 'database connection lost';
            log_fatal_db($msg, 'sql_db->exe: ' . $sql_name);
            // TODO try auto reconnect in 1, 2 4, 8, 16 ... and max 3600 sec
            throw new Exception($msg);
        } else {
            // validate the parameters
            if ($sql_name == '') {
                // TODO switch to error when all SQL statements are named
                //log_warning('Name for SQL statement ' . $sql . ' is missing');
                log_debug('Name for SQL statement ' . $sql . ' is missing', $debug - 5);
            }

            // remove query formatting
            $sql = str_replace("\n", " ", $sql);
            if ($sql_name == '') {
                // simply execute old queries (to be deprecated)
                $result = pg_query($this->postgres_link, $sql);
            } else {
                // prepare the query if needed
                if (!$this->has_query($sql_name)) {
                    if (str_starts_with($sql, 'PREPARE')) {
                        $result = pg_query($this->postgres_link, $sql);
                    } else {
                        $result = pg_prepare($this->postgres_link, $sql_name, $sql);
                    }
                    if ($result === false) {
                        throw new Exception('Database error ' . pg_last_error($this->postgres_link) . ' when preparing ' . $sql);
                    } else {
                        $this->prepared_sql_names[] = $sql_name;
                    }
                }
                // execute the query
                /*
                $pg_array = array();
                $pg_array[] = '{';
                foreach ($sql_array as $item) {
                    $pg_array[] = $item;
                }
                $pg_array[] = '}';
                */
                $result = pg_execute($this->postgres_link, $sql_name, $sql_array);
            }
            if ($result === false) {
                throw new Exception('Database error ' . pg_last_error($this->postgres_link) . ' when querying ' . $sql);
            }
        }

        return $result;
    }

    /**
     * execute an change SQL statement on a MySQL database
     * similar to exe, but database specific because the return object differs depending on the database
     *
     * @param string $sql the sql statement that should be executed
     * @param string $sql_name the unique name of the sql statement
     * @param array $sql_array the values that should be used for executing the precompiled SQL statement
     * @param int $log_level the log level is given by the calling function because after some errors the program may nevertheless continue
     * @return mysqli_result the message that should be shown to the system admin for debugging
     * @throws Exception
     *
     * TODO switch return type to bool|resource with PHP 8.0
     * TODO add the writing of potential sql errors to the sys log table to the sql execution
     * TODO includes the user to be able to ask the user for details how the error has been created
     * TODO with php 8 switch to the union return type resource|false
     */
    private function exe_mysql(
        string $sql,
        string $sql_name = '',
        array  $sql_array = array(),
        int    $log_level = sys_log_level::ERROR): mysqli_result
    {
        $result = null;

        // check database connection
        if ($this->mysql == null) {
            $msg = 'database connection lost';
            $this->open_with_retry($msg);
            log_fatal($msg, 'sql_db->exe->' . $sql_name);
            // TODO try auto reconnect in 1, 2 4, 8, 16 ... and max 3600 sec
            throw new Exception($msg);
        } else {
            // validate the parameters
            if ($sql_name == '') {
                // TODO switch to error when all SQL statements are named
                //log_warning('Name for SQL statement ' . $sql . ' is missing');
                log_debug('Name for SQL statement ' . $sql . ' is missing');
            }

            if ($sql_name == '') {
                $result = mysqli_query($this->mysql, $sql);
            } else {
                if ($this->has_query($sql_name)) {
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
                $usr = new user();
                $usr->set_id($this->usr_id);
                $msg = log_msg($msg_text, $msg_text . ' from ' . $sql_name, $log_level, $sql_name, $function_trace, $usr);
                throw new Exception("sql_db->exe -> error (" . $msg . ")");
            }
        }

        return $result;
    }

    /**
     * check if a query is already prepared
     *
     * @param string $sql_name the unique name of the query
     * @return bool true if the query is prepared
     */
    function has_query(string $sql_name): bool
    {
        if (in_array($sql_name, $this->prepared_sql_names)) {
            return true;
        } else {
            return false;
        }
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
     * fetch the first value from an SQL database (either Postgres or MySQL at the moment)
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
                        $result = null;
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
     * fetch the first row from an SQL database (either Postgres or MySQL at the moment)
     */
    private function fetch_first(string $sql, string $sql_name = '', array $sql_array = array()): ?array
    {
        return $this->fetch($sql, $sql_name, $sql_array);
    }

    /**
     * fetch the all value from an SQL database (either Postgres or MySQL at the moment)
     */
    private function fetch_all($sql, string $sql_name = '', array $sql_array = array()): array
    {
        return $this->fetch($sql, $sql_name, $sql_array, true);
    }

    private function debug_msg($sql, $type): void
    {
        global $debug;
        if ($debug > 20) {
            log_debug("sql_db->" . $type . " (" . $sql . ")");
        } elseif ($debug > 10) {
            log_debug("sql_db->" . $type . " (" . substr($sql, 0, 100) . " ... )");
        }
    }

    /**
     * returns all values of an SQL query in an array
     */
    function get_old(string $sql, string $sql_name = '', array $sql_array = array()): array
    {
        $this->debug_msg($sql, 'get_old');
        return $this->fetch_all($sql, $sql_name, $sql_array);
    }

    /**
     * returns all values of an SQL query in an array
     */
    function get(sql_par $qp): array
    {
        $this->debug_msg($qp->sql, 'get');
        return $this->fetch_all($qp->sql, $qp->name, $qp->par);
    }

    /**
     * get only the first record from the database
     * based on a not prepared sql query
     * only for internal use where no parameter can be influenced by an user
     *
     * @param string $sql the sql statement to get the db row
     * @return array|null the database row or null
     */
    function get1_internal(string $sql): ?array
    {
        $this->debug_msg($sql, 'get1');

        // optimise the sql statement
        $sql = trim($sql);
        if (!str_contains($sql, "LIMIT")) {
            if (str_ends_with($sql, ";")) {
                $sql = substr($sql, 0, -1) . " LIMIT 1;";
            } else {
                $sql = $sql . " LIMIT 1;";
            }
        }

        return $this->fetch_first($sql, '', array());
    }

    /**
     * get only the first record from the database
     */
    function get1(sql_par $qp): ?array
    {
        $this->debug_msg($qp->sql, 'get1');

        // optimise the sql statement
        $sql = trim($qp->sql);
        if (!str_contains($sql, "LIMIT")) {
            if (str_ends_with($sql, ";")) {
                $sql = substr($sql, 0, -1) . " LIMIT 1;";
            } else {
                $sql = $sql . " LIMIT 1;";
            }
        }

        return $this->fetch_first($sql, $qp->name, $qp->par);
    }

    /**
     * get only the first numeric value from the database
     * @param sql_par $qp the query parameters (sql statement, query name and parameters) that is expected to return just one number
     * @return int|null the integer number received from the database
     */
    function get1_int(sql_par $qp): ?int
    {
        $result = null;
        $db_array = $this->get1($qp);
        if (count($db_array) > 0) {
            $result = $db_array[0];
        }
        return $result;
    }

    /**
     * returns first value of a simple SQL query
     */
    function get_value($field_name, $id_name, $id)
    {
        $result = '';
        log_debug($field_name . ' from ' . $this->class . ' where ' . $id_name . ' = ' . $this->sf($id));

        if ($this->class <> '') {

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
        log_debug($field_name . ' from ' . $this->class . ' where ' . $id1_name . ' = ' . $id1 . ' and ' . $id2_name . ' = ' . $id2);

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
        log_debug('for "' . $name . '" of the db object "' . $this->class . '"');

        $this->set_id_field();
        $this->set_name_field();
        $result .= $this->get_value($this->id_field, $this->name_field, $name);

        log_debug('is "' . $result . '"');
        return $result;
    }

    /**
     * similar to get_id, but the other way round
     */
    function get_name($id)
    {
        $result = '';
        log_debug('for "' . $id . '" of the db object "' . $this->class . '"');

        $this->set_id_field();
        $this->set_name_field();
        $result = $this->get_value($this->name_field, $this->id_field, $id);

        log_debug('is "' . $result . '"');
        return $result;
    }

    /**
     * similar to zu_sql_get_id, but using a second ID field
     */
    function get_id_2key($name, $field2_name, $field2_value)
    {
        $result = '';
        log_debug('for "' . $name . ',' . $field2_name . ',' . $field2_value . '" of the db object "' . $this->class . '"');

        $this->set_id_field();
        $this->set_name_field();
        $result = $this->get_value_2key($this->id_field, $this->name_field, $name, $field2_name, $field2_value);

        log_debug('is "' . $result . '"');
        return $result;
    }

    /**
     * create a standard query for a list of database id and name while taking the user sandbox into account
     */
    function sql_std_lst_usr(): string
    {
        log_debug($this->class);

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
        if ($this->class == 'view') {
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
        log_debug("sql_db->sql_std_lst (" . $this->class . ")");

        $this->set_id_field();
        $this->set_name_field();
        $sql = "SELECT " . $this->name_sql_esc($this->id_field) . " AS id,
                   " . $this->name_sql_esc($this->name_field) . " AS name
              FROM " . $this->name_sql_esc($this->table) . "
          ORDER BY " . $this->name_sql_esc($this->name_field) . ";";

        return $sql;
    }

    /**
     * create the SQL where statement for a list of parameter for a prepared query
     *
     * @param array $fields of the field names as string
     * @param array $values of the values just to detect the type
     * @param bool $is_join_query to force using the table name prefix
     * @param bool $is_join_field to force using the link table prefix for the field name
     * @return string the SQL WHERE statement
     */
    function where_par(
        array $fields,
        array $values,
        bool  $is_join_query = false,
        bool  $is_join_field = false
    ): string
    {
        $result = '';
        if ($this->usr_query
            or $this->join <> ''
            or $this->join_type <> ''
            or $this->join2_type <> ''
            or $is_join_query) {
            if ($is_join_field) {
                $result .= sql_db::LNK_TBL . '.';
            } else {
                $result .= sql_db::STD_TBL . '.';
            }
        }
        if ($fields == null) {
            log_err('At least one field must be set to create an SQL WHERE statement');
        } else {
            $pos = 0;
            foreach ($fields as $field) {
                // assume an int value as parameter if not stated
                if (count($values) < $pos + 1) {
                    $this->add_par(sql_par_type::INT, $values[$pos]);
                } else {
                    if (gettype($values[$pos]) == 'integer') {
                        $this->add_par(sql_par_type::INT, $values[$pos]);
                    } elseif (gettype($values[$pos]) == 'string') {
                        $this->add_par(sql_par_type::TEXT, $values[$pos]);
                    } else {
                        log_err('Unknown value type of ' . $values[$pos] . ' when creating SQL WHERE statement');
                    }
                }
                if ($pos > 0) {
                    $result .= " AND ";
                }
                $result .= $field . " = " . $this->par_name();
                $pos++;
            }
        }
        return $result;
    }

    /**
     * set the where statement to select based on a none standard id field
     *
     * @param string $id_field_name name of the id field which is usually self::FLD_ID
     * @param int $id the unique object id
     * @param bool $is_join_query to force using the table name prefix
     * @return string the SQL WHERE statement
     */
    function set_where_id(string $id_field_name, int $id, bool $is_join_query = false): string
    {
        $this->where = ' WHERE ' . $this->where_par(array($id_field_name), array($id), $is_join_query);
        return $this->where;
    }

    /**
     * set the user sandbox name where statement
     * the type must have been already set e.g. to 'source'
     */
    function set_where_name(string $name = '', string $name_field = ''): string
    {
        $result = '';

        if ($name <> '' and !is_null($this->usr_id)) {
            /*
             * because the object name can be user specific,
             * don't use the standard name for the selection e.g. s.view_name
             * use instead the user specific name e.g. view_name
             */
            $this->add_par(sql_par_type::TEXT, $name);
            if ($this->usr_query or $this->join <> '') {
                $result .= '(' . sql_db::USR_TBL . '.';
                $result .= $name_field . " = " . $this->par_name();
                $result .= ' OR (' . sql_db::STD_TBL . '.';
                if (SQL_DB_TYPE != sql_db::POSTGRES) {
                    $this->add_par(sql_par_type::TEXT, $name);
                }
                $result .= $name_field . " = " . $this->par_name();
                $result .= ' AND ' . sql_db::USR_TBL . '.';
                $result .= $name_field . " IS NULL))";
            } else {
                $result .= $name_field . " = " . $this->par_name();
            }
        }
        if ($this->usr_only_query) {
            if (!$this->all_query) {
                $this->add_par(sql_par_type::INT, $this->usr_view_id);
                $result .= ' AND ' . user::FLD_ID . ' = ' . $this->par_name();
            }
        }

        if ($result == '') {
            log_err('Internal error: to find a ' . $this->class . ' either the id, name or code_id must be set', 'sql->set_where');
        } else {
            $this->where = ' WHERE ' . $result;
        }

        return $result;
    }

    /**
     * set the SQL WHERE statement for link tables
     * if $id_from or $id_to is null all links to the other side are selected
     *    e.g. if for formula_links just the phrase id is set, all formulas linked to the given phrase are returned
     * TODO allow also to retrieve a list of linked objects
     * TODO get the user specific list of linked objects
     * TODO use always parameterized values
     */
    function set_where_link_no_fld(int $id = 0, int $id_from = 0, int $id_to = 0, int $id_type = 0): string
    {
        $result = '';

        // select one link by the prime id
        if ($id <> 0) {
            if ($this->usr_query or $this->join <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $this->add_par(sql_par_type::INT, $id);
            $result .= $this->id_field . " = " . $this->par_name();
            // select one link by the from and to id
        } elseif ($id_from <> 0 and $id_to <> 0) {
            if ($this->id_from_field == '' or $this->id_to_field == '') {
                log_err('Internal error: to find a ' . $this->class . ' the link fields must be defined', 'sql->set_where_link_no_fld');
            } else {
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $this->add_par(sql_par_type::INT, $id_from);
                $result .= $this->id_from_field . " = " . $this->par_name() . " AND ";
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $this->add_par(sql_par_type::INT, $id_to);
                $result .= $this->id_to_field . " = " . $this->par_name();
                if ($id_type <> 0) {
                    if ($this->id_link_field == '') {
                        log_err('Internal error: to find a ' . $this->class . ' the link type field must be defined', 'sql->set_where_link_no_fld');
                    } else {
                        $result .= ' AND ';
                        if ($this->usr_query or $this->join <> '') {
                            $result .= sql_db::STD_TBL . '.';
                        }
                        $this->add_par(sql_par_type::INT, $id_type);
                        $result .= $this->id_link_field . " = " . $this->par_name();
                    }

                }
            }
        } elseif ($id_from <> 0) {
            if ($this->id_from_field == '') {
                log_err('Internal error: to find a ' . $this->class . ' the from field must be defined', 'sql->set_where_link_no_fld');
            } else {
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $this->add_par(sql_par_type::INT, $id_from);
                $result .= $this->id_from_field . ' = ' . $this->par_name();
            }
        } elseif ($id_to <> 0) {
            if ($this->id_to_field == '') {
                log_err('Internal error: to find a ' . $this->class . ' the to field must be defined', 'sql->set_where_link_no_fld');
            } else {
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $this->add_par(sql_par_type::INT, $id_to);
                $result .= $this->id_to_field . ' = ' . $this->par_name();
            }
        } else {
            log_err('Internal error: to find a ' . $this->class . ' the a field must be defined', 'sql->set_where_link_no_fld');
        }

        if ($result == '') {
            log_err('Internal error: to find a ' . $this->class . ' either the id or the from and to id must be set', 'sql->set_where_link_no_fld');
        } else {
            $this->where = ' WHERE ' . $result;
        }

        return $result;
    }

    /**
     * set the standard where statement to select either by id or name or code_id
     * the type must have been already set e.g. to 'source'
     * TODO check why the request user must be set to search by code_id ?
     * TODO check if test for positive and negative id values is needed; because phrases can have a negative id ?
     */
    function set_where_std($id, $name = '', $code_id = ''): string
    {
        $result = '';

        if ($id <> 0) {
            if ($this->usr_query
                or $this->join <> ''
                or $this->join_type <> ''
                or $this->join2_type <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $this->add_par(sql_par_type::INT, $id);
            $result .= $this->id_field . " = " . $this->par_name();
        } elseif ($code_id <> '' and !is_null($this->usr_id)) {
            if ($this->usr_query or $this->join <> '') {
                $result .= sql_db::STD_TBL . '.';
            }
            $this->add_par(sql_par_type::TEXT, $code_id);
            $result .= sql::FLD_CODE_ID . " = " . $this->par_name();
            if ($this->db_type == sql_db::POSTGRES) {
                $result .= ' AND ';
                if ($this->usr_query or $this->join <> '') {
                    $result .= sql_db::STD_TBL . '.';
                }
                $result .= sql::FLD_CODE_ID . ' IS NOT NULL';
            }
        } elseif ($name <> '' and !is_null($this->usr_id)) {
            $result .= $this->set_where_name($name, $this->name_field);
        }
        if ($this->usr_only_query) {
            if (!$this->all_query) {
                $this->add_par(sql_par_type::INT, $this->usr_view_id);
                $result .= ' AND ' . user::FLD_ID . ' = ' . $this->par_name();
            }
        }

        if ($result == '') {
            log_err('Internal error: to find a ' . $this->class . ' either the id, name or code_id must be set', 'sql->set_where');
        } else {
            $this->where = ' WHERE ' . $result;
        }

        return $result;
    }

    /**
     * set the where statement based on the parameter set until now
     * @param array $id_fields the name of the primary id field that should be used or the list of link fields
     * @return void
     */
    private function set_where(array $id_fields): void
    {
        $open_or_flf_lst = false;
        // if nothing is defined assume to load the row by the main if
        if ($this->where == '') {
            if (count($this->par_types) > 0) {
                if (count($this->par_types) <> count($this->par_named)) {
                    log_err('Number of parameter types does not match the number of name parameter indicators');
                }
                if (count($this->par_types) <> count($this->par_use_link)) {
                    log_err('Number of parameter types does not match the number of link usage indicators');
                }
                $i = 0; // the position in the SQL parameter array
                $used_fields = 0; // the position of the fields used in the where statement
                foreach ($this->par_types as $par_type) {
                    // set the closing bracket around a or field list if needed
                    if ($open_or_flf_lst) {
                        if (!($par_type == sql_par_type::TEXT_OR
                            or $par_type == sql_par_type::INT_LIST_OR)) {
                            $this->where .= ' ) ';
                            $open_or_flf_lst = false;
                        }
                    }
                    if ($this->par_named[$i] == false) {
                        // start with the where statement
                        if ($this->where == '') {
                            $this->where = ' WHERE ';
                        } else {
                            if ($par_type == sql_par_type::TEXT_OR
                                or $par_type == sql_par_type::INT_LIST_OR) {
                                $this->where .= ' OR ';
                            } else {
                                $this->where .= ' AND ';
                            }
                        }
                        // set the opening bracket around a or field list if needed
                        if ($par_type == sql_par_type::TEXT_OR
                            or $par_type == sql_par_type::INT_LIST_OR) {
                            if (!$open_or_flf_lst) {
                                $this->where .= ' ( ';
                                $open_or_flf_lst = true;
                            }
                        }
                        // add the table
                        if ($this->usr_query
                            or $this->join <> ''
                            or $this->join_type <> ''
                            or $this->join2_type <> '') {
                            if ($this->par_use_link[$i]) {
                                $this->where .= sql_db::LNK_TBL . '.';
                            } else {
                                $this->where .= sql_db::STD_TBL . '.';
                            }
                        }
                        // add the field name
                        if ($par_type == sql_par_type::INT_LIST
                            or $par_type == sql_par_type::INT_LIST_OR
                            or $par_type == sql_par_type::TEXT_LIST) {
                            if ($this->db_type == sql_db::POSTGRES) {
                                $this->where .= $id_fields[$used_fields] . ' = ANY (' . $this->par_name($i + 1) . ')';
                            } else {
                                $this->where .= $id_fields[$used_fields] . ' IN (' . $this->par_name($i + 1) . ')';
                            }
                        } else {
                            if ($par_type == sql_par_type::LIKE_R
                                or $par_type == sql_par_type::LIKE
                                or $par_type == sql_par_type::LIKE_OR) {
                                $this->where .= $id_fields[$used_fields] . ' like ' . $this->par_name($i + 1);
                            } else {
                                if ($par_type == sql_par_type::CONST) {
                                    $this->where .= $this->par_value($i + 1);
                                } else {
                                    if ($par_type == sql_par_type::INT_NOT) {
                                        $this->where .= $id_fields[$used_fields] . ' <> ' . $this->par_name($i + 1);
                                    } else {
                                        $this->where .= $id_fields[$used_fields] . ' = ' . $this->par_name($i + 1);
                                    }
                                }
                            }
                        }

                        if ($par_type == sql_par_type::TEXT) {
                            if ($id_fields[$used_fields] == sql::FLD_CODE_ID) {
                                if ($this->db_type == sql_db::POSTGRES) {
                                    $this->where .= ' AND ';
                                    if ($this->usr_query or $this->join <> '') {
                                        $this->where .= sql_db::STD_TBL . '.';
                                    }
                                    $this->where .= sql::FLD_CODE_ID . ' IS NOT NULL';
                                }
                            }
                        }

                        $used_fields++;
                    }
                    $i++;
                }
                // close any open brackets
                if ($open_or_flf_lst) {
                    $this->where .= ' ) ';
                }

            }
        }
    }

    /**
     * get the where statement supposed to be used for the next query creation
     * mainly used to test if the search has valid parameters
     */
    function get_where(): string
    {
        return $this->where;
    }

    /**
     * set the where statement for a later call of the select function
     * mainly used to overwrite the for special cases, where the set_where function cannot be used
     * TODO prevent code injections e.g. by using only predefined queries
     */
    function set_where_text($where_text): void
    {
        if ($where_text != '') {
            $this->where = ' WHERE ' . $where_text;
        }
    }

    /**
     * set the order SQL statement based on the given field name
     * @param string $order_field the name of the order field
     * @param string $direction the SQL direction name (ASC or DESC)
     * @param string $table_prefix
     */
    function set_order(string $order_field, string $direction = '', string $table_prefix = ''): void
    {
        if ($direction <> sql::ORDER_DESC) {
            $direction = '';
        }
        if ($table_prefix == '') {
            if ($this->usr_query
                or $this->join <> ''
                or $this->join_type <> ''
                or $this->join2_type <> '') {
                $table_prefix .= self::STD_TBL . '.';
            }
        } else {
            $table_prefix .= '.';
        }

        $this->set_order_text(trim($table_prefix . $order_field . ' ' . $direction));
        if ($this->all_query) {
            $this->order .= ', ' . $table_prefix . user::FLD_ID;
        }
    }

    /**
     * set the order SQL statement
     */
    function set_order_text(string $order_text): void
    {
        $this->order = ' ORDER BY ' . $order_text;
    }

    /**
     * set the parameter for paged results
     * @param int $limit
     * @param int $page
     * @return void
     */
    function set_page_par(int $limit = 0, int $page = 0): void
    {
        // set default values
        if ($page < 0) {
            $page = 0;
        }
        if ($limit == 0) {
            $limit = sql_db::ROW_LIMIT;
        } else {
            if ($limit <= 0) {
                $limit = sql_db::ROW_LIMIT;
            }
        }

        $this->add_par(sql_par_type::INT, $limit);
        $this->page = ' LIMIT ' . $this->par_name();
        if ($page > 0) {
            $this->add_par(sql_par_type::INT, $page * $limit);
            $this->page .= ' OFFSET ' . $this->par_name();
        }
    }

    /**
     * create the "JOIN" SQL statement based on the joined user fields
     */
    private function set_user_join(): void
    {
        if ($this->usr_query) {
            if (!$this->join_usr_added) {
                $usr_table_name = $this->name_sql_esc(sql_db::USER_PREFIX . $this->table);
                $this->join .= ' LEFT JOIN ' . $usr_table_name . ' ' . sql_db::USR_TBL;
                if (is_array($this->id_field)) {
                    $join_link = '';
                    foreach ($this->id_field as $id_fld) {
                        if ($join_link == '') {
                            $join_link .= ' ON ';
                        } else {
                            $join_link .= ' AND ';
                        }
                        $join_link .= sql_db::STD_TBL . '.' . $id_fld . ' = ' . sql_db::USR_TBL . '.' . $id_fld;
                    }
                    $this->join .= $join_link;
                } else {
                    $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $this->id_field . ' = ' . sql_db::USR_TBL . '.' . $this->id_field;
                }
                if (!$this->all_query) {
                    $this->join .= ' AND ' . sql_db::USR_TBL . '.' . user::FLD_ID . ' = ';
                    if ($this->query_name == '') {
                        $this->join .= $this->usr_view_id;
                    } else {
                        $this->add_par(sql_par_type::INT, $this->usr_id);
                        $this->join_usr_par_name = $this->par_name();
                        $this->join .= $this->join_usr_par_name;
                    }
                }
                $this->join_usr_added = true;
            }
        }
    }

    /**
     * create the "FROM" SQL statement based on the type
     */
    private function set_from(): void
    {
        if ($this->join_type <> '') {
            $join_table_name = $this->name_sql_esc($this->get_table_name($this->join_type));
            $join_id_field = $this->name_sql_esc($this->get_id_field_name($this->join_type));
            if ($this->join_field == '') {
                $join_from_id_field = $join_id_field;
            } else {
                $join_from_id_field = $this->join_field;
            }
            if ($this->join_to_field != '') {
                $join_id_field = $this->join_to_field;
            }
            if (count($this->join_usr_count_field_lst) > 0) {
                $field_sql = '';
                foreach ($this->field_lst as $field_name) {
                    if ($field_sql != '') {
                        $field_sql .= ', ';
                    }
                    $field_sql .= sql_db::LNK_TBL . '.' . $field_name;
                }
                $field_count_sql = '';
                $field_order_sql = '';
                foreach ($this->join_usr_count_field_lst as $field_name) {
                    if ($field_count_sql != '') {
                        $field_count_sql .= ', ';
                    }
                    if ($field_order_sql != '') {
                        $field_order_sql .= ', ';
                    }
                    $field_name_as = $field_name . '_count';
                    $field_count_sql .= 'count(' . sql_db::LNK_TBL . '.' . $field_name . ') AS ' . $field_name_as;
                    $field_order_sql .= $field_name_as;
                }
                if (count($this->join_usr_count_field_lst) > 0) {
                    $this->from .= ' FROM ' . $this->name_sql_esc($this->table) . ' ' . sql_db::STD_TBL;
                    $this->from .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join_table_name . ' ' . sql_db::LNK_TBL;
                    $this->from .= ' ON ' . sql_db::LNK_TBL . '.' . $join_from_id_field . ' = ' . sql_db::STD_TBL . '.' . $join_id_field;
                    $this->add_par(sql_par_type::INT, $this->usr_id);
                    $this->from .= ' WHERE ' . sql_db::STD_TBL . '.' . $join_from_id_field . ' = ' . $this->par_name() . ' ';
                    $this->from .= ' GROUP BY ' . $this->fields . ' ';
                    $this->from .= ' ) AS ' . sql_db::STD_TBL;
                    $this->order = ' ORDER BY ' . $field_order_sql . ' DESC';
                } else {
                    $this->from = ' FROM ( SELECT ' . $this->name_sql_esc($this->table);
                    $this->from .= ' LEFT JOIN ' . $join_table_name . ' ' . sql_db::LNK_TBL;
                    $this->from .= ' ON ' . sql_db::STD_TBL . '.' . $join_from_id_field . ' = ' . sql_db::LNK_TBL . '.' . $join_id_field;
                    $this->add_par(sql_par_type::INT, $this->usr_id);
                    $this->from .= ' WHERE ' . sql_db::STD_TBL . '.' . $join_from_id_field . ' = ' . $this->par_name() . ' ';
                    $this->from .= ' GROUP BY ' . $field_sql . ' ';
                    $this->from .= ' ) AS c1';
                    $this->order = $field_order_sql . ' DESC';
                }
            } else {
                $this->join .= ' LEFT JOIN ' . $join_table_name . ' ' . sql_db::LNK_TBL;
                $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $join_from_id_field . ' = ' . sql_db::LNK_TBL . '.' . $join_id_field;
                if ($this->usr_query and $this->join_usr_query) {
                    $this->join .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join_table_name . ' ' . sql_db::ULK_TBL;
                    $this->join .= ' ON ' . sql_db::LNK_TBL . '.' . $join_id_field . ' = ' . sql_db::ULK_TBL . '.' . $join_id_field;
                    if (!$this->all_query) {
                        $this->join .= ' AND ' . sql_db::ULK_TBL . '.' . user::FLD_ID . ' = ';
                        if ($this->query_name == '') {
                            $this->join .= $this->usr_view_id;
                        } else {
                            // for MySQL the parameter needs to be repeated
                            if ($this->db_type == self::MYSQL) {
                                $this->add_par(sql_par_type::INT, $this->usr_id, true);
                            }
                            $this->join .= $this->join_usr_par_name;
                        }
                    }
                }
                if ($this->join_select_field != '') {
                    if ($this->where == '') {
                        $this->where = ' WHERE ';
                    } else {
                        $this->where .= ' AND ';
                    }
                    $this->add_par(sql_par_type::INT, $this->join_select_id);
                    $this->where .= sql_db::LNK_TBL . '.' . $this->join_select_field . ' = ' . $this->par_name();
                }
            }
        }
        if ($this->join2_type <> '') {
            $join2_table_name = $this->name_sql_esc($this->get_table_name($this->join2_type));
            $join2_id_field = $this->name_sql_esc($this->get_id_field_name($this->join2_type));
            if ($this->join2_field == '') {
                $join2_from_id_field = $join2_id_field;
            } else {
                $join2_from_id_field = $this->join2_field;
            }
            if ($this->join2_to_field != '') {
                $join2_id_field = $this->join2_to_field;
            }
            $this->join .= ' LEFT JOIN ' . $join2_table_name . ' ' . sql_db::LNK2_TBL;
            $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $join2_from_id_field . ' = ' . sql_db::LNK2_TBL . '.' . $join2_id_field;
            if ($this->usr_query and $this->join2_usr_query) {
                $this->join .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join2_table_name . ' ' . sql_db::ULK2_TBL;
                $this->join .= ' ON ' . sql_db::LNK2_TBL . '.' . $join2_id_field . ' = ' . sql_db::ULK2_TBL . '.' . $join2_id_field;
                if (!$this->all_query) {
                    $this->join .= ' AND ' . sql_db::ULK2_TBL . '.' . user::FLD_ID . ' = ';
                    if ($this->query_name == '') {
                        $this->join .= $this->usr_view_id;
                    } else {
                        // for MySQL the parameter needs to be repeated
                        if ($this->db_type == self::MYSQL) {
                            $this->add_par(sql_par_type::INT, $this->usr_id, true);
                        }
                        $this->join .= $this->join_usr_par_name;
                    }
                }
            }
            if ($this->join2_select_field != '') {
                if ($this->where == '') {
                    $this->where = ' WHERE ';
                } else {
                    $this->where .= ' AND ';
                }
                $this->add_par(sql_par_type::INT, $this->join2_select_id);
                $this->where .= sql_db::LNK2_TBL . '.' . $this->join2_select_field . ' = ' . $this->par_name();
            }
        }
        if ($this->join3_type <> '') {
            $join3_table_name = $this->name_sql_esc($this->get_table_name($this->join3_type));
            $join3_id_field = $this->name_sql_esc($this->get_id_field_name($this->join3_type));
            if ($this->join3_field == '') {
                $join3_from_id_field = $join3_id_field;
            } else {
                $join3_from_id_field = $this->join3_field;
            }
            if ($this->join3_to_field != '') {
                $join3_id_field = $this->join3_to_field;
            }
            $this->join .= ' LEFT JOIN ' . $join3_table_name . ' ' . sql_db::LNK3_TBL;
            $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $join3_from_id_field . ' = ' . sql_db::LNK3_TBL . '.' . $join3_id_field;
            if ($this->usr_query and $this->join3_usr_query) {
                $this->join .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join3_table_name . ' ' . sql_db::ULK3_TBL;
                $this->join .= ' ON ' . sql_db::LNK3_TBL . '.' . $join3_id_field . ' = ' . sql_db::ULK3_TBL . '.' . $join3_id_field;
                if (!$this->all_query) {
                    $this->join .= ' AND ' . sql_db::ULK3_TBL . '.' . user::FLD_ID . ' = ';
                    if ($this->query_name == '') {
                        $this->join .= $this->usr_view_id;
                    } else {
                        // for MySQL the parameter needs to be repeated
                        if ($this->db_type == self::MYSQL) {
                            $this->add_par(sql_par_type::INT, $this->usr_id, true);
                        }
                        $this->join .= $this->join_usr_par_name;
                    }
                }
            }
            if ($this->join3_select_field != '') {
                if ($this->where == '') {
                    $this->where = ' WHERE ';
                } else {
                    $this->where .= ' AND ';
                }
                $this->add_par(sql_par_type::INT, $this->join3_select_id);
                $this->where .= sql_db::LNK3_TBL . '.' . $this->join3_select_field . ' = ' . $this->par_name();
            }
        }
        if ($this->join4_type <> '') {
            $join4_table_name = $this->name_sql_esc($this->get_table_name($this->join4_type));
            $join4_id_field = $this->name_sql_esc($this->get_id_field_name($this->join4_type));
            if ($this->join4_field == '') {
                $join4_from_id_field = $join4_id_field;
            } else {
                $join4_from_id_field = $this->join4_field;
            }
            if ($this->join4_to_field != '') {
                $join4_id_field = $this->join4_to_field;
            }
            $this->join .= ' LEFT JOIN ' . $join4_table_name . ' ' . sql_db::LNK4_TBL;
            $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $join4_from_id_field . ' = ' . sql_db::LNK4_TBL . '.' . $join4_id_field;
            if ($this->usr_query and $this->join4_usr_query) {
                $this->join .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join4_table_name . ' ' . sql_db::ULK4_TBL;
                $this->join .= ' ON ' . sql_db::LNK4_TBL . '.' . $join4_id_field . ' = ' . sql_db::ULK4_TBL . '.' . $join4_id_field;
                if (!$this->all_query) {
                    $this->join .= ' AND ' . sql_db::ULK4_TBL . '.' . user::FLD_ID . ' = ';
                    if ($this->query_name == '') {
                        $this->join .= $this->usr_view_id;
                    } else {
                        // for MySQL the parameter needs to be repeated
                        if ($this->db_type == self::MYSQL) {
                            $this->add_par(sql_par_type::INT, $this->usr_id, true);
                        }
                        $this->join .= $this->join_usr_par_name;
                    }
                }
            }
            if ($this->join4_select_field != '') {
                if ($this->where == '') {
                    $this->where = ' WHERE ';
                } else {
                    $this->where .= ' AND ';
                }
                $this->add_par(sql_par_type::INT, $this->join4_select_id);
                $this->where .= sql_db::LNK4_TBL . '.' . $this->join4_select_field . ' = ' . $this->par_name();
            }
        }
        if ($this->from == '') {
            $this->from = ' FROM ' . $this->name_sql_esc($this->table);
            if ($this->join <> '') {
                $this->from .= ' ' . sql_db::STD_TBL;
            }
        }
    }

    /**
     * @return array with the parameter values in the same order as the given SQL parameter placeholders
     */
    function get_par(): array
    {
        $used_par_values = [];
        $i = 0; // the position in the SQL parameter array
        foreach ($this->par_types as $par_type) {
            if ($par_type != sql_par_type::CONST) {
                $used_par_values[] = $this->par_value($i + 1);;
            }
            $i++;
        }
        return $used_par_values;
    }

    /**
     * create a SQL select statement for the connected database
     * and select the standard and the user sandbox rows
     * @return string the created SQL statement in the previous set dialect
     */
    function select_all(): string
    {
        return $this->select_by(array($this->id_field));
    }

    /**
     * create a SQL select statement for the connected database
     * and select by the default id field
     * @param bool $has_id to be able to create also SQL statements for tables that does not have a single unique key
     * @return string the created SQL statement in the previous set dialect
     */
    function select_by_set_id(bool $has_id = true): string
    {
        return $this->select_by(array($this->id_field), $has_id);
    }

    /**
     * create a SQL select statement for the connected database
     * and force to use the code id instead of the id
     * @param bool $has_id to be able to create also SQL statements for tables that does not have a single unique key
     * @return string the created SQL statement in the previous set dialect
     */
    function select_by_code_id(bool $has_id = true): string
    {
        return $this->select_by(array(sql::FLD_CODE_ID), $has_id);
    }

    /**
     * create a SQL select statement for the connected database and force to use the ids of the linked objects instead of the id
     * and select by a given field
     * @param bool $has_id to be able to create also SQL statements for tables that does not have a single unique key
     * @return string the created SQL statement in the previous set dialect
     */
    function select_by_field(string $id_field, bool $has_id = true): string
    {
        return $this->select_by(array($id_field), $has_id);
    }

    /**
     * create a SQL select statement for the connected database and force to use the ids of the linked objects instead of the id
     * and select by a given field
     * @param string $field_name the field name of the database index field that should be used for the selection
     * @param int $id the database id of the row that should be selected
     * @return string the created SQL statement in the previous set dialect
     */
    function select_by_join_field(string $field_name, int $id): string
    {
        $this->join_select_field = $field_name;
        $this->join_select_id = $id;
        return $this->select_by();
    }

    /**
     * create a SQL select statement for the connected database and force to use the ids of the linked objects instead of the id
     * and select by a list of given fields
     * @param bool $has_id to be able to create also SQL statements for tables that does not have a single unique key
     * @return string the created SQL statement in the previous set dialect
     */
    function select_by_field_list(array $id_fields, bool $has_id = true): string
    {
        return $this->select_by($id_fields, $has_id);
    }

    /**
     * @return string the SQL statement to for the user specific data
     */
    function select_by_id_not_owner(int $id, ?int $owner_id = 0): string
    {
        $this->add_par(sql_par_type::INT, $id);
        if ($owner_id > 0) {
            $this->add_par(sql_par_type::INT_NOT, $owner_id);
        }
        $this->add_par(sql_par_type::CONST, '(excluded <> 1 OR excluded is NULL)');

        $this->set_field_statement(true);
        $this->set_from();
        $id_fld = $this->id_field;
        if ($owner_id > 0) {
            $this->set_where(array($this->id_field, user::FLD_ID));
        } else {
            $this->set_where(array($this->id_field));
        }

        // create a prepare SQL statement if possible
        $sql = $this->prepare_sql();

        $sql .= $this->fields . $this->from . $this->where . $this->order . $this->page;

        return $this->end_sql($sql);
    }

    /**
     * init the sql statement to get the users that has changed to value or result object
     * replaces the standard sql db function
     *
     * @return string the SQL statement to for the user specific data
     */
    function select_value_by_id_not_owner(int $id, ?int $owner_id = 0): string
    {
        $this->add_par(sql_par_type::INT, $id);
        if ($owner_id > 0) {
            $this->add_par(sql_par_type::INT_NOT, $owner_id);
        }
        $this->add_par(sql_par_type::CONST, '(excluded <> 1 OR excluded is NULL)');

        $this->set_field_statement(true);
        $this->set_from();
        if ($owner_id > 0) {
            $this->set_where(array($this->id_field, user::FLD_ID));
        } else {
            $this->set_where(array($this->id_field));
        }

        // create a prepare SQL statement if possible
        $sql = $this->prepare_sql();

        $sql .= $this->fields . $this->from . $this->where . $this->order . $this->page;

        return $this->end_sql($sql);
    }

    /*
    function select_union(array $db_cons): string
    {
        // create a prepare SQL statement if possible
        $sql = '';

        $fields_common = [];
        foreach ($db_cons as $db_con) {
            if ($sql != '') {
                $sql .= ' UNION ( ';
            }
            $sql .= $db_con->prepare_sql();
            $sql .= ' ) ';
        }

        return $this->end_sql($sql);
    }
    */

    /**
     * create the SQL parameters to count the number of rows related to a database table type
     * @return ?int the number of rows or null if something went wrong
     */
    function count(string $type_name = '', string $id_fld = ''): ?int
    {
        if ($type_name != '') {
            $this->set_class($type_name);
        }
        return $this->get1_int($this->count_qp());
    }

    /**
     * create the SQL parameters to count the number of rows related to a database table type
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function count_qp(string $class_name = '', string $id_fld = ''): sql_par
    {
        if ($class_name == '') {
            $class_name = $this->class;
        }
        $qp = new sql_par($class_name);
        $qp->name = $this->class . '_count';
        $qp->sql = $this->count_sql($qp->name, $id_fld);
        return $qp;
    }

    /**
     * create a SQL select statement to count the number of rows related to a database table type
     * the table type includes the table for the standard parameters and the user sandbox exceptions
     * @return string the created SQL statement in the previous set dialect
     */
    function count_sql(string $sql_name = '', string $id_fld = ''): string
    {
        $lib = new library();
        $class = $lib->class_to_name($this->class);
        if ($id_fld == '') {
            $id_fld = $class . self::FLD_EXT_ID;
        }
        if ($sql_name == '') {
            $sql_name = $class . '_count';
        }
        $sql = 'PREPARE ' . $sql_name . ' AS
                    SELECT count(' . self::STD_TBL . '.' . $id_fld . ') + count(' . self::USR_TBL . '.' . $id_fld . ') AS count
                      FROM ' . $this->table . ' ' . self::STD_TBL . '
                 LEFT JOIN ' . sql_db::USER_PREFIX . $this->table . '  ' . self::USR_TBL . ' ON ' . self::STD_TBL . '.' . $id_fld . ' = ' . self::USR_TBL . '.' . $id_fld . ';';
        return $sql;
    }

    /**
     * convert the parameter type list to make valid for postgres
     * TODO deprecate and use a function of the sql object instead
     *
     * @return array with the postgres parameter types
     */
    private function par_types_to_postgres(): array
    {
        $in_types = $this->par_types;
        $result = array();
        foreach ($in_types as $type) {
            switch ($type) {
                case sql_par_type::INT_LIST:
                case sql_par_type::INT_LIST_OR:
                    $result[] = 'bigint[]';
                    break;
                case sql_par_type::INT:
                case sql_par_type::INT_OR:
                case sql_par_type::INT_NOT:
                    $result[] = 'bigint';
                    break;
                case sql_par_type::INT_SMALL:
                    $result[] = 'smallint';
                    break;
                case sql_par_type::TEXT_LIST:
                    $result[] = 'text[]';
                    break;
                case sql_par_type::LIKE_R:
                case sql_par_type::LIKE:
                case sql_par_type::LIKE_OR:
                case sql_par_type::TEXT_OR:
                    $result[] = 'text';
                    break;
                case sql_par_type::CONST:
                    break;
                default:
                    $result[] = $type->value;
            }
        }
        return $result;
    }

    /**
     * @return string with the SQL prepare statement for the current query
     */
    private function prepare_sql(): string
    {
        $sql = '';
        if (count($this->par_types) > 0) {
            if ($this->db_type == sql_db::POSTGRES) {
                $par_types = $this->par_types_to_postgres();
                $sql = 'PREPARE ' . $this->query_name . ' (' . implode(', ', $par_types) . ') AS SELECT';
            } elseif ($this->db_type == sql_db::MYSQL) {
                $sql = "PREPARE " . $this->query_name . " FROM '" . sql::SELECT;
                $this->end = "';";
            } else {
                log_err('Prepare SQL not yet defined for SQL dialect ' . $this->db_type);
            }
        } else {
            log_err('Query name is given, but parameters types are missing for ' . $this->query_name);
        }
        return $sql;
    }

    /**
     * @return string with the SQL closing statement for the current query
     */
    private
    function end_sql(string $sql): string
    {
        if ($this->end == '') {
            $this->end = ';';
        }
        if (substr($sql, -1) != ";") {
            $sql .= $this->end;
        }
        return $sql;
    }

    /**
     * create a SQL select statement for the connected database
     * @param array $id_fields the name of the primary id field that should be used or the list of link fields
     * @param bool $has_id to be able to create also SQL statements for tables that does not have a single unique key
     * @return string the created SQL statement in the previous set dialect
     */
    private
    function select_by(array $id_fields = [], bool $has_id = true): string
    {
        $sql = '';
        $sql_end = ';';

        $this->set_field_statement($has_id);
        $this->set_from();
        $this->set_where($id_fields);

        // create a prepare SQL statement if possible
        if ($this->query_name != '') {
            $sql = $this->prepare_sql();
        } else {
            $sql = sql::SELECT;
            //log_info('SQL statement ' . $sql . $this->fields . $this->from . $this->join . $this->where . ' is not yet named, please consider using a prepared SQL statement');
        }

        $sql .= $this->fields . $this->from . $this->join . $this->where . $this->order . $this->page;

        return $this->end_sql($sql);
    }

    /**
     * create a SQL select statement for the connected database
     * to detect if someone else has used the object
     * @param int $id the unique database id if the object to check
     * @param int|null $owner_id the user id of the owner of the object
     * @param string $id_field the field name of the prime database key if not standard
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 in the previous set dialect
     */
    function load_sql_not_changed(int $id, ?int $owner_id = 0, string $id_field = ''): sql_par
    {
        $qp = new sql_par($this->class);
        $qp->name .= 'not_changed';
        if ($owner_id > 0) {
            $qp->name .= '_not_owned';
        }
        $this->set_name($qp->name);
        $this->set_usr($this->usr_id);
        $this->set_id_field($id_field);
        $this->set_fields(array(user::FLD_ID));
        if ($id == 0) {
            log_err('The id must be set to detect if the link has been changed');
        } else {
            $this->add_par(sql_par_type::INT, $id);
            $sql_mid = " " . user::FLD_ID .
                " FROM " . $this->name_sql_esc(sql_db::TBL_USER_PREFIX . $this->table) .
                " WHERE " . $this->id_field . " = " . $this->par_name() . "
                 AND (excluded <> 1 OR excluded is NULL)";
            if ($owner_id > 0) {
                $this->add_par(sql_par_type::INT, $owner_id);
                $sql_mid .= " AND " . user::FLD_ID . " <> " . $this->par_name();
            }
            $qp->sql = $this->prepare_sql() . $sql_mid;
            $qp->sql = $this->end_sql($qp->sql);
        }
        $qp->par = $this->get_par();

        return $qp;
    }

    /**
     * create a SQL select statement for the connected database
     * to detect if someone else has used the object
     * if the value can be stored in different tables
     *
     * @param int $id the unique database id if the object to check
     * @param int|null $owner_id the user id of the owner of the object
     * @param string|array $id_field the field name or field list of the prime database key if not standard
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 in the previous set dialect
     */
    function load_sql_not_changed_multi(
        int            $id,
        ?int           $owner_id = 0,
        string|array   $id_field = '',
        string         $ext = '',
        sql_type $tbl_typ = sql_type::MOST
    ): sql_par
    {
        $qp = new sql_par($this->class);
        $qp->name .= 'not_changed';
        if ($owner_id > 0) {
            $qp->name .= '_not_owned';
        }
        $this->set_name($qp->name);
        $this->set_usr($this->usr_id);
        $this->set_table(false, $tbl_typ->extension());
        $this->set_id_field($id_field);
        $this->set_fields(array(user::FLD_ID));
        if ($id == 0) {
            log_err('The id must be set to detect if the link has been changed');
        } else {
            // TODO review
            $sql_mid_where = '';
            if ($tbl_typ == sql_type::PRIME) {
                $grp_id = new group_id();
                $id_lst = $grp_id->get_array($id, true);
                if (is_array($this->id_field)) {
                    if (count($id_lst) != count($this->id_field)) {
                        log_err('the number of id and fields differ');
                    } else {
                        $pos = 0;
                        foreach ($id_lst as $id_item) {
                            if ($id_item == null) {
                                // TODO move null to const
                                $this->add_par(sql_par_type::INT_SMALL, '0');
                            } else {
                                $this->add_par(sql_par_type::INT_SMALL, $id_item);
                            }
                            if ($sql_mid_where == '') {
                                $sql_mid_where .= " WHERE ";
                            } else {
                                $sql_mid_where .= " AND ";
                            }
                            $sql_mid_where .= $this->id_field[$pos] . " = " . $this->par_name();
                            $pos++;
                        }
                    }
                } else {
                    log_err('the id fields are expected to be an array');
                }
            } elseif ($tbl_typ == sql_type::BIG) {
                $grp_id = new group_id();
                $id_lst = $grp_id->get_array($id, true);
                foreach ($id_lst as $id_item) {
                    $this->add_par(sql_par_type::INT, $id_item);
                }
            } else {
                $this->add_par(sql_par_type::INT, $id);
            }
            $sql_mid = " " . user::FLD_ID;
            $sql_mid .= " FROM " . $this->name_sql_esc(sql_db::TBL_USER_PREFIX . $this->table);
            if (!is_array($this->id_field)) {
                $sql_mid_where .= $this->id_field . " = " . $this->par_name();
            }
            $sql_mid .= $sql_mid_where . " AND (excluded <> 1 OR excluded is NULL)";
            if ($owner_id > 0) {
                $this->add_par(sql_par_type::INT, $owner_id);
                $sql_mid .= " AND " . user::FLD_ID . " <> " . $this->par_name();
            }
            $qp->sql = $this->prepare_sql() . $sql_mid;
            $qp->sql = $this->end_sql($qp->sql);
        }
        $qp->par = $this->get_par();

        return $qp;
    }

    /**
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement to find user sandbox objects where the owner is not set
     */
    function missing_owner_sql(): sql_par
    {
        $qp = new sql_par('missing_owner');
        $qp->name .= $this->class;
        $this->set_name($qp->name);
        $this->set_usr($this->usr_id);
        $this->set_id_field();
        $qp->sql = "SELECT " . $this->id_field . " AS id
                      FROM " . $this->name_sql_esc($this->table) . "
                     WHERE user_id IS NULL;";

        return $qp;
    }

    /**
     * @return array all database ids, where the owner is not yet set
     */
    function missing_owner(): array
    {
        global $debug;
        log_debug("sql_db->missing_owner (" . $this->class . ")", $debug - 4);
        $qp = $this->missing_owner_sql();
        return $this->get($qp);
    }

    /**
     * return all database ids, where the owner is not yet set
     */
    function set_default_owner(): bool
    {
        log_debug("sql_db->set_default_owner (" . $this->class . ")");
        $result = true;

        // get the system user id
        $sys_usr = new user();
        $sys_usr->load_by_name(user::SYSTEM_NAME);

        if ($sys_usr->id() <= 0) {
            log_err('Cannot load system used in set_default_owner');
            $result = false;
        } else {
            $sql = "UPDATE " . $this->name_sql_esc($this->table) . "
               SET user_id = " . $sys_usr->id() . "
             WHERE user_id IS NULL;";

            //return $this->exe($sql, 'user_default', array());
            try {
                $result = $this->exe($sql, '', array());
            } catch (Exception $e) {
                $msg = 'Select';
                log_err($msg . log::MSG_ERR_USING . $sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                $result = false;
            }
        }

        return $result;
    }

    /*
      technical function to finally update data in the MySQL database
    */

    /**
     * execute an insert sql statement
     * and return a message to the user if something has gone wrong
     * and a suggested solution to fix the issue
     * and alternative solution if possible
     * or the id of the created database row if successful
     *
     * @param sql_par $qp the sql statement with the name of the prepare query and parameter for this execution
     * @param string $description for the user to identify the statement
     * @return user_message
     */
    function insert(sql_par $qp, string $description): user_message
    {
        $result = new user_message();
        $err_msg = 'Insert of ' . $description . ' failed.';
        try {
            $sql_result = $this->exe($qp->sql, $qp->name, $qp->par);
            $db_id = 0;
            if ($this->db_type == sql_db::POSTGRES) {
                $sql_error = pg_result_error($sql_result);
                if ($sql_error != '') {
                    log_err($sql_error . ' while executing ' . $qp->sql);
                } else {
                    $db_id = pg_fetch_array($sql_result)[0];
                    //$result = $db_con->lastInsertId('yourIdColumn');
                }
            } else {
                $db_id = mysqli_fetch_array($sql_result, MYSQLI_BOTH);
            }
            if ($db_id == 0 or $db_id == '') {
                log_err($err_msg);
                $result->add_message($err_msg);
            } else {
                $result->set_db_row_id($db_id);
            }
        } catch (Exception $e) {
            $trace_link = log_err($err_msg . log::MSG_ERR_USING . $qp->sql . log::MSG_ERR_BECAUSE . $e->getMessage());
            $result->add_message($trace_link);
        }

        return $result;
    }

    /**
     * execute an update sql statement
     * and return a message to the user if something has gone wrong
     * and a suggested solution to fix the issue
     * and alternative solution if possible
     * or the true if successful
     *
     * @param sql_par $qp the sql statement with the name of the prepare query and parameter for this execution
     * @param string $description for the user to identify the statement
     * @return user_message
     */
    function update(sql_par $qp, string $description): user_message
    {
        $result = new user_message();
        $err_msg = 'Update of ' . $description . ' failed';
        try {
            $sql_result = $this->exe($qp->sql, $qp->name, $qp->par);
            if ($this->db_type == sql_db::POSTGRES) {
                $sql_error = pg_result_error($sql_result);
                if ($sql_error != '') {
                    $err_msg .= ' due to ' . $sql_error;
                    log_err($err_msg);
                    $result->add_message($err_msg);
                }
            }
        } catch (Exception $e) {
            $trace_link = log_err($err_msg . log::MSG_ERR_USING . $qp->sql . log::MSG_ERR_BECAUSE . $e->getMessage());
            $result->add_message($trace_link);
        }

        return $result;
    }

    /**
     * execute a delete sql statement
     * and return a message to the user if something has gone wrong
     * and a suggested solution to fix the issue
     * and alternative solution if possible
     * or the true if successful
     *
     * @param sql_par $qp the sql statement with the name of the prepare query and parameter for this execution
     * @param string $description for the user to identify the statement
     * @return user_message
     */
    function delete(sql_par $qp, string $description): user_message
    {
        $result = new user_message();
        $err_msg = 'Delete of ' . $description . ' failed';
        try {
            $sql_result = $this->exe($qp->sql, $qp->name, $qp->par);
            if ($this->db_type == sql_db::POSTGRES) {
                $sql_error = pg_result_error($sql_result);
                if ($sql_error != '') {
                    $err_msg .= ' due to ' . $sql_error;
                    log_err($err_msg);
                    $result->add_message($err_msg);
                }
            }
        } catch (Exception $e) {
            $trace_link = log_err($err_msg . log::MSG_ERR_USING . $qp->sql . log::MSG_ERR_BECAUSE . $e->getMessage());
            $result->add_message($trace_link);
        }

        return $result;
    }

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
    function insert_old($fields, $values, bool $log_err = true): int
    {
        $result = 0;
        $is_valid = false;
        $lib = new library();

        // escape the fields and values and build the SQL statement
        $sql = 'INSERT INTO ' . $this->name_sql_esc($this->table);

        if (is_array($fields)) {
            if (count($fields) <> count($values)) {
                if ($log_err) {
                    $lib = new library();
                    log_fatal_db(
                        'MySQL insert call with different number of fields (' . $lib->dsp_count($fields)
                        . ': ' . $lib->dsp_array($fields) . ') and values (' . $lib->dsp_count($values)
                        . ': ' . $lib->dsp_array($values) . ').', "user_log->add");
                }
            } else {
                foreach (array_keys($fields) as $i) {
                    $fields[$i] = $this->name_sql_esc($fields[$i]);
                    $values[$i] = $this->sf($values[$i]);
                }
                $sql_fld = $lib->sql_array($fields, ' (', ') ');
                $sql .= $lib->sql_array($values,
                    $sql_fld . ' VALUES (', ') ');
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
                    if ($this->class != sql_db::TBL_VALUE_TIME_SERIES_DATA
                        and $this->class != value::class
                        and $this->class != sql_db::TBL_RESULT) {
                        $sql = $sql . ' RETURNING ' . $this->id_field . ';';
                    }
                    if ($this->id_field == 'official_type_id') {
                        log_info('check');
                    }

                    /*
                    try {
                        $stmt = $this->link->prepare($sql);
                        $this->link->beginTransaction();
                        $stmt->execute();
                        $this->link->commit();
                        $result = $this->link->lastInsertId();
                        log_debug('done "' . $result . '"');
                    } catch (PDOException $e) {
                        $this->link->rollback();
                        log_debug('failed (' . $sql . ')');
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
                            if ($this->class != sql_db::TBL_VALUE_TIME_SERIES_DATA) {
                                if (is_resource($sql_result) or $sql_result::class == 'PgSql\Result') {
                                    try {
                                        $result = pg_fetch_array($sql_result);
                                        if ($result === false) {
                                            $result = 0;
                                        } else {
                                            if (is_array($result)) {
                                                $result = $result[0];
                                            }
                                        }
                                    } catch (PDOException $e) {
                                        log_err('failed result catch (' . $sql . ')');
                                    }
                                } else {
                                    // TODO get the correct db number
                                    $result = 0;
                                }
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
                        log_debug('done "' . $result . '"');
                    } else {
                        $result = -1;
                        log_debug('failed (' . $sql . ')');
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
            log_debug('failed (' . $sql . ')');
        }

        if ($result == null) {
            log_err('Unexpected result for "' . $this->db_type . '"', 'sql_db->fetch');
            $result = 0;
        }

        return $result;
    }


    /**
     * add a new unique text to the database and return the id (similar to get_id)
     */
    function add_id($name): int
    {
        log_debug($name . ' to ' . $this->class);

        $this->set_name_field();
        $result = $this->insert_old($this->name_field, $name);

        log_debug('is "' . $result . '"');
        return $result;
    }

    /**
     * similar to zu_sql_add_id, but using a second ID field
     */
    function add_id_2key($name, $field2_name, $field2_value): int
    {
        log_debug($name . ',' . $field2_name . ',' . $field2_value . ' to ' . $this->class);

        $this->set_name_field();
        //zu_debug('sql_db->add_id_2key add "'.$this->name_field.','.$field2_name.'" "'.$name.','.$field2_value.'"');
        $result = $this->insert_old(array($this->name_field, $field2_name), array($name, $field2_value));

        log_debug('is "' . $result . '"');
        return $result;
    }

    /**
     * update some values in a table
     * TODO separate the sql statement creation from the update statement execution
     *      e.g. to use the IDE check functionality for the created sql statements
     * $id is the primary id of the db table or an array with the ids of the primary keys
     * @return bool false if the update has failed (and the error messages are logged)
     */
    function update_old($id, $fields, $values, string $id_field = ''): bool
    {
        global $debug;
        $lib = new library();

        log_debug('of ' . $this->class . ' row ' . $lib->dsp_var($id) . ' ' . $lib->dsp_var($fields) . ' with "' . $lib->dsp_var($values) . '" for user ' . $this->usr_id, $debug - 7);

        $result = true;

        // check parameter
        $par_ok = true;
        $this->set_id_field($id_field);
        if ($debug > 0) {
            if ($this->table == "") {
                log_err("Table not valid for " . $fields . " at " . $lib->dsp_var($id) . ".", "zu_sql_update", (new Exception)->getTraceAsString());
                $par_ok = false;
            }
            if ($values === "") {
                log_err("Values missing for " . $fields . " in " . $this->table . ".", "zu_sql_update", (new Exception)->getTraceAsString());
                $par_ok = false;
            }
        }

        // set the where clause user sandbox? ('.substr($this->type,0,4).')');
        $sql_where = ' WHERE ' . $this->id_field . ' = ' . $this->sf($id);
        if (substr($this->class, 0, 4) == 'user') {
            // ... but not for the user table itself
            if ($this->class <> sql_db::TBL_USER
                and $this->class <> sql_db::TBL_USER_TYPE
                and $this->class <> sql_db::TBL_USER_PROFILE) {
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
            log_debug('sql "' . $sql . '"', $debug - 12);
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

        log_debug('done (' . $result . ')', $debug - 17);
        return $result;
    }


    /**
     * @throws Exception
     */
    function update_name($id, $name): bool
    {
        $this->set_name_field();
        return $this->update_old($id, $this->name_field, $name);
    }

    /**
     * delete action
     * @return string an empty string if the deletion has been successful
     *                or the error message that should be shown to the user
     *                which may include a link for error tracing
     */
    function delete_old($id_fields, $id_values): string
    {
        $lib = new library();
        if (is_array($id_fields)) {
            log_debug('in "' . $this->class . '" WHERE "' . $lib->dsp_array($id_fields) . '" IS "' . $lib->dsp_array($id_values) . '" for user ' . $this->usr_id);
        } else {
            log_debug('in "' . $this->class . '" WHERE "' . $id_fields . '" IS "' . $id_values . '" for user ' . $this->usr_id);

        }

        if (is_array($id_fields)) {
            $sql = 'DELETE ' . 'FROM ' . $this->name_sql_esc($this->table);
            $sql_del = '';
            foreach (array_keys($id_fields) as $i) {
                $del_val = $id_values[$i];
                if (is_array($del_val)) {
                    $del_val_txt = $lib->sql_array($del_val, ' IN (', ') ', true);
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

        log_debug('sql "' . $sql . '"');
        return $this->exe_try('Deleting of ' . $this->class, $sql, '', array(), sys_log_level::FATAL);
    }

    /*
      list functions to finally get data from the MySQL database
    */

    /*
     * private supporting functions
     */

    /**
     * escape or reformat the reserved SQL names
     */
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
     * TODO deprecate to prevent sql code injections
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
     * formats one value for the Postgres statement
     */
    function postgres_format($field_value, $forced_format)
    {
        global $debug;

        $result = $field_value;

        // add the formatting for the sql statement
        if (trim($result) == "" or trim($result) == sql::NULL_VALUE) {
            $result = sql::NULL_VALUE;
        } else {
            if ($forced_format == sql_db::FLD_FORMAT_VAL) {
                if (str_starts_with($result, "'") and str_ends_with($result, "'")) {
                    $result = substr($result, 1, -1);
                }
            } elseif ($forced_format == sql_db::FLD_FORMAT_TEXT or !is_numeric($result)) {

                // escape the text value for Postgres
                $result = pg_escape_string($result);
                //$result = pg_real_escape_string($result);

                // undo the double high quote escape char, because this is not needed if the string is capsuled by single high quote
                $result = str_replace('\"', '"', $result);
                $result = "'" . $result . "'";
            } else {
                $result = strval($result);
            }
        }
        log_debug("done (" . $result . ")", $debug - 25);

        return $result;
    }

    /**
     * formats one value for the MySQL statement
     */
    function mysqli_format($field_value, $forced_format): string
    {
        $result = $field_value;

        // add the formatting for the sql statement
        if (trim($result) == "" or trim($result) == sql::NULL_VALUE) {
            $result = sql::NULL_VALUE;
        } else {
            if ($forced_format == sql_db::FLD_FORMAT_VAL) {
                if (str_starts_with($result, "'") and str_ends_with($result, "'")) {
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

        log_debug("done (" . $result . ")");

        return $result;
    }

    /**
     * fallback SQL string escape function if there is no database connection
     */
    private
    function sql_escape($param)
    {
        if (is_array($param))
            return array_map(__METHOD__, $param);

        if (!empty($param) && is_string($param)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $param);
        }

        return $param;
    }

    /**
     * reset the seq number
     * @param string $type the class name to which the related table should be reset
     * @return string any warning message to be shown to the admin user
     */
    function seq_reset(string $type): string
    {
        $msg = '';
        $this->set_class($type);
        $sql_max = 'SELECT MAX(' . $this->name_sql_esc($this->id_field) . ') AS max_id FROM ' . $this->name_sql_esc($this->table) . ';';
        // $db_con->set_fields(array('MAX(group_id) AS max_id'));
        // $sql_max = $db_con->select();
        $max_row = $this->get1_internal($sql_max);
        if ($max_row == null) {
            log_warning('Cannot get the max of values', 'sql_db->seq_reset');
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
            $sql_result = $this->get1_internal($sql_check);
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
            $sql_result = $this->get1_internal($sql_check);
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
            $sql_result = $this->get1_internal($sql_check);
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
     * remove a database column but only if needed
     * @param string $table_name
     * @param string $field_name
     * @return user_message ok or the message that should be shown to the user
     */
    function del_field(string $table_name, string $field_name): user_message
    {
        $result = new user_message();

        // adjust the parameters to the used database used
        $table_name = $this->get_table_name($table_name);

        // check if the old column name is still valid
        if ($this->has_column($table_name, $field_name)) {

            // actually add the column
            $sql = 'ALTER TABLE IF EXISTS ' . $this->name_sql_esc($table_name) .
                ' DROP COLUMN IF EXISTS ' . $this->name_sql_esc($field_name) . ';';
            $result->add_message($this->exe_try('Deleting column ' . $field_name . ' of ' . $table_name, $sql));
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
                $db_row = $this->get1_internal($pre_sql);
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
                $db_row = $this->get1_internal($pre_sql);
                $db_format = $db_row['COL_TYPE'];
                $sql = "ALTER TABLE `" . $table_name . "` CHANGE `" . $column_name . "` `" . $column_name . "` " . $db_format . ";";
                //$sql_a = 'ALTER TABLE `phrase_types` CHANGE `word_symbol` `word_symbol` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'e.g. for percent the symbol is %'; '
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
                $db_row = $this->get1_internal($pre_sql);
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

    /**
     * SQL statement to remove a fixed first part of a table column
     *
     * @param string $type_name
     * @param string $column_name the name of the column where the prefix should be removed
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function remove_prefix_sql(string $type_name, string $column_name): sql_par
    {
        // adjust the parameters to the used database name
        $table_name = $this->get_table_name($type_name);

        $qp = new sql_par('remove_prefix');
        $qp->name .= $table_name . '_' . $column_name;
        $this->set_name($qp->name);
        $qp->sql = "SELECT " . $this->name_sql_esc($column_name) .
            " FROM " . $this->name_sql_esc($table_name) . ";";
        return $qp;
    }

    /**
     * remove a fixed first part of a table column
     *
     * @param string $type_name
     * @param string $column_name the name of the column where the prefix should be removed
     * @param string $prefix_name the prefix that should be removed
     * @return bool true if removing of the prefix has been successful
     */
    function remove_prefix(string $type_name, string $column_name, string $prefix_name): bool
    {
        $result = false;

        $lib = new library();

        // adjust the parameters to the used database name
        $table_name = $this->get_table_name($type_name);

        $qp = $this->remove_prefix_sql($type_name, $column_name);
        $db_row_lst = $this->get($qp);
        foreach ($db_row_lst as $db_row) {
            $db_row_name = $db_row[$column_name];
            $new_name = $lib->str_right_of($db_row_name, $prefix_name);
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
        $qp = new sql_par('get_column_names');
        $qp->sql = 'SELECT' . ' column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE ';
        if ($this->db_type == sql_db::POSTGRES) {
            $qp->sql .= " table_name = '" . $table_name . "';";
            $qp->name .= $table_name;
        } elseif ($this->db_type == sql_db::MYSQL) {
            $qp->sql .= " TABLE_SCHEMA = 'zukunft' AND TABLE_NAME = '" . $table_name . "';";
            $qp->name .= $table_name;
        } else {
            $qp->sql = '';
            $msg = 'Unknown database type "' . $this->db_type . '"';
            log_err($msg, 'sql_db->has_column');
        }
        $this->set_name($qp->name);
        if ($qp->sql != '') {
            $col_rows = $this->get($qp);
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
            $lib = new library();
            log_fatal('Database column ' . $lib->dsp_array($missing_columns)
                . ' missing in ' . $table_name, 'sql_db/check_column_names');
            $result = false;
        }
        return $result;
    }

    function sql_setup_header(): string
    {
        $sc = new sql();
        $sql = $sc->sql_separator();
        if ($this->db_type == sql_db::MYSQL) {
            $sql .= self::SETUP_HEADER_MYSQL;
        } else {
            $sql .= self::SETUP_HEADER;
        }
        return $sql;
    }

    function sql_setup_footer(): string
    {
        $sc = new sql();
        $sql = '';
        if ($this->db_type == sql_db::MYSQL) {
            $sql .= self::SETUP_FOOTER_MYSQL;
        } else {
            $sql .= self::SETUP_FOOTER;
        }
        return $sql;
    }

    function sql_separator_index(): string
    {
        $sc = new sql();
        $sql = $sc->sql_separator();
        $sql .= self::SETUP_COMMENT . ' ';
        $sql .= self::SETUP_COMMENT . ' ' . self::SETUP_INDEX . ' ';
        $sql .= self::SETUP_COMMENT . ' ' . self::SETUP_INDEX_COM . ' ';
        $sql .= self::SETUP_COMMENT . ' ';
        return $sql;
    }

    function sql_separator_foreign_key(): string
    {
        $sc = new sql();
        $sql = $sc->sql_separator();
        $sql .= self::SETUP_COMMENT . ' ';
        $sql .= self::SETUP_COMMENT . ' ' . self::SETUP_FOREIGN_KEY . ' ';
        $sql .= self::SETUP_COMMENT . ' ';
        $sql .= $sc->sql_separator();
        return $sql;
    }

    /**
     * @return sql with the same db_type
     */
    function sql_creator(): sql
    {
        $sc = new sql();
        if ($this->db_type == null) {
            $sc->set_db_type(sql_db::POSTGRES);
        } else {
            $sc->set_db_type($this->db_type);
        }
        return $sc;
    }

    /**
     * get the folder for db type specific files
     * @param string $db_type the sql db type name
     * @return string the folder name including the separator
     */
    function path(string $db_type): string
    {
        $path = self::POSTGRES_PATH . DIRECTORY_SEPARATOR;
        if ($db_type == self::MYSQL) {
            $path = self::MYSQL_PATH . DIRECTORY_SEPARATOR;
        }
        return $path;
    }

    /**
     * get the file extension
     * @param string $db_type the sql db type name
     * @return string the folder name including the separator
     */
    function ext(string $db_type): string
    {
        $path = self::POSTGRES_EXT;
        if ($db_type == self::MYSQL) {
            $path = self::MYSQL_EXT;
        }
        return $path;
    }

    function truncate_table_all(): void
    {
        // the sequence names of the tables to reset
        $html = new html_base();
        $html->echo('truncate ');
        foreach (DB_SEQ_LIST as $seq_name) {
            $this->reset_seq($seq_name);
        }
    }

    function truncate_table(string $table_name): void
    {
        $html = new html_base();
        $html->echo('TRUNCATE TABLE ' . $table_name);
        $sql = 'TRUNCATE ' . $this->get_table_name_esc($table_name) . ' CASCADE;';
        try {
            $this->exe($sql);
        } catch (Exception $e) {
            log_err('Cannot truncate table ' . $table_name . ' with "' . $sql . '" because: ' . $e->getMessage());
        }
    }

    function drop_table(string $table_name): void
    {
        $html = new html_base();
        $html->echo('DROP TABLE ' . $table_name);
        if ($this->has_table($table_name)) {
            $sql = 'drop table ' . $table_name . ' cascade;';
            try {
                $this->exe($sql);
            } catch (Exception $e) {
                //log_info('Cannot drop table ' . $table_name . ' with "' . $sql . '" because: ' . $e->getMessage());
            }
        }
    }

    function reset_seq_all(): void
    {
        // the sequence names of the tables to reset
        foreach (DB_SEQ_LIST as $seq_name) {
            $this->reset_seq($seq_name);
        }
    }

    function reset_seq(string $seq_name, int $start_id = 1): void
    {
        $html = new html_base();
        $html->echo('RESET SEQUENCE ' . $seq_name);
        $sql = 'ALTER SEQUENCE ' . $seq_name . ' RESTART ' . $start_id . ';';
        try {
            $this->exe($sql);
        } catch (Exception $e) {
            log_err('Cannot do sequence reset with "' . $sql . '" because: ' . $e->getMessage());
        }
    }

    /**
     * fill the user profiled with the default values for this program version
     * @return void
     */
    function load_user_profiles(): void
    {
        foreach (USER_CODE_LINK_FILES as $csv_file_name) {
            $this->load_db_code_link_file($csv_file_name, $this);
        }
        global $user_profiles;
        $user_profiles = new user_profile_list();
        $user_profiles->load($this);
    }

    /**
     * fill the config with the default value for this program version
     * @return void
     */
    function reset_config(): void
    {
        $cfg = new config();
        $cfg->set(config::VERSION_DB, PRG_VERSION, $this);
    }

    /**
     * @return bool true if the user has actuelly been imported
     */
    function import_system_users(): bool
    {
        $result = false;

        // allow adding only if there is not yet any system user in the database
        $usr = new user;
        $usr->load_by_id(SYSTEM_USER_ID);

        if ($usr->id() <= 0) {

            // check if there is really no user in the database with a system profile
            $check_usr = new user();
            if (!$check_usr->has_any_user_this_profile(user_profile::SYSTEM)) {
                // if the system users are missing always reset all users as a double line of defence to prevent system
                $this->load_user_profiles();
                $usr->set_profile(user_profile::SYSTEM);
                $imf = new import_file();
                $import_result = $imf->json_file(SYSTEM_USER_CONFIG_FILE, $usr);
                if (str_starts_with($import_result, ' done ')) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    function import_verbs(user $usr): bool
    {
        global $db_con;
        global $verbs;

        $result = false;

        if ($usr->is_admin() or $usr->is_system()) {
            $imf = new import_file();
            $import_result = $imf->json_file(SYSTEM_VERB_CONFIG_FILE, $usr);
            if (str_starts_with($import_result, ' done ')) {
                $result = true;
            }
        }

        $verbs = new verb_list($usr);
        $verbs->load($db_con);

        return $result;
    }


}
