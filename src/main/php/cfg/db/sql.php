<?php

/*

    model/db/sql.php - create sql statements for different database dialects
    ----------------

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

include_once DB_PATH . 'sql_db.php';
include_once MODEL_SYSTEM_PATH . 'log.php';
include_once MODEL_DB_PATH . 'sql_field_type.php';
include_once MODEL_DB_PATH . 'sql_field_default.php';
include_once MODEL_DB_PATH . 'sql_where_type.php';
include_once MODEL_DB_PATH . 'sql_where.php';
include_once MODEL_DB_PATH . 'sql_where_list.php';
include_once MODEL_DB_PATH . 'sql_pg.php';

use cfg\component\component_link;
use cfg\element;
use cfg\formula_link;
use cfg\group\group;
use cfg\group\group_id;
use cfg\ip_range;
use cfg\ip_range_list;
use cfg\job;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_values_big;
use cfg\log\change_link;
use cfg\log\change_values_norm;
use cfg\log\change_values_prime;
use cfg\log\change_table;
use cfg\log\changes_big;
use cfg\log\changes_norm;
use cfg\ref;
use cfg\result\result;
use cfg\sandbox;
use cfg\sandbox_link;
use cfg\sandbox_link_typed;
use cfg\sandbox_value;
use cfg\sys_log;
use cfg\triple;
use cfg\user;
use cfg\user\user_profile;
use cfg\user\user_type;
use cfg\user_official_type;
use cfg\value\value;
use cfg\value\value_phrase_link;
use cfg\value\value_time_series;
use cfg\view_term_link;
use DateTime;
use shared\library;

class sql
{

    // common SQL const that must exist in all used sql dialects
    // or the say it another way: used SQL elements that are the same in all used dialects
    const SELECT = 'SELECT';
    const INSERT = 'INSERT';
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE FROM';
    const NOW = 'Now()';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    const NULL_VALUE = 'NULL';
    const INDEX = 'INDEX';
    const UNIQUE = 'UNIQUE INDEX';  // TODO check if UNIQUE needs to be used for word and triple names
    const PREPARE = 'PREPARE';
    const CREATE = 'CREATE OR REPLACE';
    const DROP_MYSQL = 'DROP PROCEDURE IF EXISTS';
    const FUNCTION = 'FUNCTION';
    const FUNCTION_MYSQL = 'CREATE PROCEDURE';
    const FUNCTION_NAME = '$$';
    const FUNCTION_DECLARE = 'DECLARE';
    const FUNCTION_BEGIN = 'BEGIN';
    const FUNCTION_BEGIN_MYSQL = 'BEGIN';
    const INTO = 'INTO';
    const FUNCTION_END = 'END $$ LANGUAGE plpgsql';
    const FUNCTION_END_MYSQL = 'END';
    const FUNCTION_RETURN_INT = 'RETURNS bigint AS';
    const FUNCTION_RETURN_INT_MYSQL = '';
    const FUNCTION_NO_RETURN = 'RETURNS void AS';
    const FUNCTION_NO_RETURN_MYSQL = '';
    const RETURNING = 'RETURNING';
    const RETURN = 'RETURN';
    const VIEW = 'VIEW';
    const AS = 'AS';
    const FROM = 'FROM';
    const WHERE = 'WHERE';
    const AND = 'AND';
    const OR = 'OR';
    const CONCAT = 'CONCAT';
    const CASE = 'CASE WHEN';
    const CASE_MYSQL = 'IF(';
    const THEN = 'THEN';
    const THEN_MYSQL = ',';
    const IS_NULL = 'IS NULL';
    const ELSE = 'ELSE';
    const ELSE_MYSQL = ',';
    const END = 'END';
    const END_MYSQL = ')';
    const UNION = 'UNION';
    const IN = 'IN';
    const SEP = ';';
    const TBL_SEP = '.';
    const WITH = 'WITH';

    const LAST_ID_MYSQL = 'SELECT LAST_INSERT_ID() AS ';
    const TRUE = '1'; // representing true in the where part for a smallint field
    const FALSE = '0'; // representing true in the where part for a smallint field
    const ID_NULL = 0; // the 'not set' value for an id; could have been null if postgres index would allow it

    // sql field names used for several classes
    const FLD_CODE_ID = 'code_id';     // field name for the code link e.g. for words used for the system configuration
    const FLD_CODE_ID_SQLTYP = sql_field_type::CODE_ID;
    const FLD_VALUE = 'value';         // field name e.g. for the configuration value
    const FLD_TYPE_NAME = 'type_name'; // field name for the user specific name of a type; types are used to assign code to a db row
    const FLD_CONST = 'const'; // for the view creation to indicate that the field name as a const

    // query name extentions to make the quere name unique
    const NAME_ALL = 'all'; // for queries that should return all rows without paging
    const NAME_SEP = '_'; // for snake case query and file names
    const NAME_BY = 'by'; // to seperate the query selection in the query name e.g. for (load) word_by_name
    const NAME_EXT_USER = '_user';
    const NAME_EXT_MEDIAN_USER = 'median_user'; // to get the user that is owner of the most often used db row
    const NAME_EXT_EX_OWNER = 'ex_owner'; // excluding the owner of the loaded db row
    const NAME_EXT_USER_CONFIG = 'usr_cfg';

    // for sql functions that do the change log and the actual change with on function
    const FLD_LOG_FIELD_PREFIX = 'field_id_'; // added to the field name to include the preloaded log field id
    const FLD_LOG_ID_PREFIX = 'id_'; // added to the field name to include the actual id changed in the log e.g. for type changes
    const PAR_PREFIX = '_'; // to seperate the parameter names e.g. _word_id instead of word_id for the given parameter
    const PAR_PREFIX_MYSQL = '@'; // for the new sequence id using MySQL
    const PAR_NEW_ID_PREFIX = 'new_'; // added to the field id name to remember the sequence id

    // query field prefixes to make the field name unique
    const FROM_FLD_PREFIX = 'from_';
    const TO_FLD_PREFIX = 'to_';

    // enum values used for the table creation
    const fld_type_ = '';

    // sql const used for this sql statement creator
    const MAX_PREFIX = 'max_';

    // postgres parameter types for prepared queries
    const PG_PAR_INT = 'bigint';
    const PG_PAR_LIST = '[]';
    const PG_PAR_INT_SMALL = 'smallint';
    const PG_PAR_TEXT = 'text';
    const PG_PAR_FLOAT = 'numeric';

    // placeholder for the class name in table or field comments
    const COMMENT_CLASS_NAME = '-=class=-';

    // internal const for unit testing
    const FILE_INSERT = 'insert'; // for insert (or create in curl notation) unit test files
    const FILE_UPDATE = 'update'; // for update unit test files
    const FILE_DELETE = 'delete'; // for delete (or remove in curl notation) unit test files
    const FILE_LOAD = 'load'; // for load unit test files

    // classes where the table that do not have a name
    // e.g. sql_db::TBL_TRIPLE is a link which hase a name, but the generated name can be overwritten, so the standard field naming is not used
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
        ip_range::class,
        ip_range_list::class,
        change::class,
        changes_norm::class,
        changes_big::class,
        change_link::class,
        sys_log::class,
        job::class,
        sql_db::VT_PHRASE_GROUP_LINK
    ];
    // classes where the tables have no auto increase id
    const DB_TYPES_NO_SEQ = [
        group::class,
        value::class,
        result::class
    ];

    // name the positions in the field definition array
    private const FLD_POS_NAME = 0;
    private const FLD_POS_TYPE = 1;
    private const FLD_POS_DEFAULT_VALUE = 2;
    private const FLD_POS_INDEX = 3;
    private const FLD_POS_FOREIGN_LINK = 4;
    private const FLD_POS_COMMENT = 5;
    private const FLD_POS_NAME_LINK = 6;

    // positions in the field, value and type array
    private const FVT_FLD = 0;
    private const FVT_VAL = 1;
    private const FVT_TYP = 2;

    // parameters for the sql creation that are set step by step with the functions of the sql creator
    private ?int $usr_id;           // the user id of the person who request the database changes
    private ?int $usr_view_id;      // the user id of the person which values should be returned e.g. an admin might want to check the data of an user
    public ?string $db_type;       // the database type which should be used for this connection e.g. Postgres or MYSQL
    private ?string $class;         // the object class name used for the table name and the standard fields are defined e.g. for type "cfg\word" the table "words" and the field "word_name" is used
    private ?string $table;         // name of the table that is used for the next query including the extension if one class lead to many tables e.g. values_prime
    private ?string $query_name;    // unique name of the query to precompile and use the query
    private bool $usr_query;        // true, if the query is expected to retrieve user specific data
    private bool $grp_query;        // true, if the query should calculate the value for a group of database rows; cannot be combined with other query types
    // TODO replace by a sc_par_lst
    private bool $sub_query;        // true, if the query is a sub query for another query
    private bool $list_query;       // true, if the query is part of a list of queries used for a with statement
    private bool $all_query;        // true, if the query is expected to retrieve the standard and the user specific data
    private string|array|null $id_field;  // primary key field or field list of the table used
    private string|array|null $id_field_dummy;  // an empty primary key field for a union query
    private string|array|null $id_field_num_dummy;  // an empty numeric primary key field for a union query
    private string|array|null $id_field_usr_dummy;  // an empty user primary key field for a union query
    private ?string $id_from_field; // only for link objects the id field of the source object
    private ?string $id_to_field;   // only for link objects the id field of the destination object
    private ?string $id_link_field; // only for link objects the id field of the link type object
    private ?sql_field_list $par_lst; // the list of parameter fields with values and types for the call of the complete sql statement
    private ?sql_where_list $par_where; // list of where parameters for one sql statement part e.g

    private ?array $par_use_link;   // array of bool, true if the parameter should be used on the linked table
    private array $par_named;       // array of bool, true if the parameter placeholder is already used in the SQL statement
    private array $par_name;        // array of the par name

    // internal parameters that depend on more than one function
    private ?string $join;     // the JOIN                  SQL statement that is used for the next select query
    private ?string $where;    // the WHERE condition as an SQL statement that is used for the next select query
    private ?string $order;    // the ORDER                 SQL statement that is used for the next select query
    private ?string $page;     // the LIMIT and OFFSET      SQL statement that is used for the next select query
    private ?string $end;      // the closing               SQL statement that is used for the next select query
    //private ?string $sub_sql;  // a complex sql statement used for the next select query
    private bool $use_page;    // true if the limit and offset statement should be added at the end

    // temp for handling the user fields
    private ?array $field_lst;                 // list of fields that should be returned to the next select query
    private ?array $field_lst_dummy;           // list of fields filled with dummy values for a union query
    private ?array $field_lst_num_dummy;       // list of numeric fields filled with dummy values for a union query
    private ?array $field_lst_date_dummy;      // list of datetime fields filled with dummy values for a union query
    private ?array $usr_field_lst;             // list of user specific fields that should be returned to the next select query
    private ?array $usr_num_field_lst;         // list of user specific numeric fields that should be returned to the next select query
    private ?array $usr_bool_field_lst;        // list of user specific boolean / tinyint fields that should be returned to the next select query
    private ?array $usr_only_field_lst;        // list of fields that are only in the user sandbox
    private ?array $grp_field_lst;             // list of fields where e.g. the min or max of a group should be calculated

    // temp for handling the table joins
    private ?array $join_field_lst;            // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_field_lst;           // same as $join_field_lst but for the second join
    private ?array $join3_field_lst;           // same as $join_field_lst but for the third join
    private ?array $join4_field_lst;           // same as $join_field_lst but for the fourth join
    private string $join_field;                // if set the field name in the main table that should be used for the join; only needed, if the field name differs from the first target field
    private string $join2_field;               // same as $join_field but for the second join
    private string $join3_field;               // same as $join_field but for the third join
    private string $join4_field;               // same as $join_field but for the fourth join
    private string $join_to_field;             // if set the field name in the joined table that should be used for the join; only needed, if the joined field name differ from the type id field
    private string $join2_to_field;            // same as $join_field but for the second join
    private string $join3_to_field;            // same as $join_field but for the third join
    private string $join4_to_field;            // same as $join_field but for the fourth join
    private bool $join_force_rename;           // if true force the fields to be renamed to create unique fields e.g. if a similar object is linked
    private bool $join2_force_rename;          // same as $join_force_rename but for the second join
    private bool $join3_force_rename;          // same as $join_force_rename but for the third join
    private bool $join4_force_rename;          // same as $join_force_rename but for the fourth join
    private string $join_select_field;         // if set the field name in the joined table that should be used for a where selection
    private string $join2_select_field;        // same as $join_select_field but for the second join
    private string $join3_select_field;        // same as $join_select_field but for the third join
    private string $join4_select_field;        // same as $join_select_field but for the fourth join
    private int $join_select_id = 0;           // if $join_select_field is set the id (int) used for the selection
    private int $join2_select_id = 0;          // same as $join_select_id but for the second join
    private int $join3_select_id = 0;          // same as $join_select_id but for the third join
    private int $join4_select_id = 0;          // same as $join_select_id but for the fourth join
    private string $join_usr_par_name;         // the parameter name for the user id
    private ?array $join_usr_field_lst;        // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_usr_field_lst;       // same as $join_usr_field_lst but for the second join
    private ?array $join3_usr_field_lst;       // same as $join_usr_field_lst but for the third join
    private ?array $join4_usr_field_lst;       // same as $join_usr_field_lst but for the fourth join
    private ?array $join_usr_num_field_lst;    // list of fields that should be returned to the next select query that are taken from a joined table
    private ?array $join2_usr_num_field_lst;   // same as $join_usr_num_field_lst but for the second join
    private ?array $join3_usr_num_field_lst;   // same as $join_usr_num_field_lst but for the third join
    private ?array $join4_usr_num_field_lst;   // same as $join_usr_num_field_lst but for the fourth join
    private ?array $join_usr_count_field_lst;  // list of fields that should be returned to the next select query where the count are taken from a joined table
    //private ?array $join2_usr_count_field_lst; // same as $join_usr_count_field_lst but for the second join
    //private ?array $join3_usr_count_field_lst; // same as $join_usr_count_field_lst but for the third join
    //private ?array $join4_usr_count_field_lst; // same as $join_usr_count_field_lst but for the fourth join
    private bool $join_usr_fields;             // true, if the joined query is also expected to retrieve user specific data
    private bool $join2_usr_fields;            // same as $join_usr_fields but for the second join
    private bool $join3_usr_fields;            // same as $join_usr_fields but for the third join
    private bool $join4_usr_fields;            // same as $join_usr_fields but for the fourth join
    private bool $join_usr_query;              // true, if the joined query is also based on a user specific data link
    private bool $join2_usr_query;             // same as $usr_join_query but for the second join
    private bool $join3_usr_query;             // same as $usr_join_query but for the third join
    private bool $join4_usr_query;             // same as $usr_join_query but for the fourth join
    private bool $join_sub_query;              // true, if the joined type contains is a sql query statement
    private bool $join2_sub_query;             // same as $usr_join_query but for the second join
    private bool $join3_sub_query;             // same as $usr_join_query but for the third join
    private bool $join4_sub_query;             // same as $usr_join_query but for the fourth join
    private bool $join_usr_added;              // true, if the user join statement has been created
    private ?string $join_type;                // the type name of the table to join
    private ?string $join2_type;               // the type name of the second table to join (maybe later switch to join n tables)
    private ?string $join3_type;               // the type name of the third table to join (maybe later switch to join n tables)
    private ?string $join4_type;               // the type name of the fourth table to join (maybe later switch to join n tables)


    /*
     * construct and map
     */

    /**
     * set the default sql_creator configuration
     */
    function __construct(string $db_type = '')
    {
        if ($db_type != '') {
            $this->db_type = $db_type;
        } else {
            $this->db_type = sql_db::POSTGRES;
        }
        $this->reset();
    }


    /**
     * reset the previous settings
     */
    public function reset(string $db_type = ''): void
    {
        if ($db_type != '') {
            $this->db_type = $db_type;
        }
        $this->usr_id = null;
        $this->usr_view_id = null;
        $this->class = '';
        $this->table = '';
        $this->query_name = '';
        $this->usr_query = false;
        $this->grp_query = false;
        $this->sub_query = false;
        $this->list_query = false;
        $this->all_query = false;
        $this->id_field = '';
        $this->id_field_dummy = '';
        $this->id_field_num_dummy = '';
        $this->id_field_usr_dummy = '';
        $this->id_from_field = '';
        $this->id_to_field = '';
        $this->id_link_field = '';
        $this->par_lst = new sql_field_list();
        $this->par_where = new sql_where_list();
        $this->par_use_link = [];
        $this->par_named = [];
        $this->par_name = [];

        $this->join = '';
        $this->where = '';
        $this->order = '';
        $this->page = '';
        $this->end = '';
        //$this->sub_sql = '';
        $this->use_page = false;

        $this->field_lst = [];
        $this->field_lst_dummy = [];
        $this->field_lst_num_dummy = [];
        $this->field_lst_date_dummy = [];
        $this->usr_field_lst = [];
        $this->usr_num_field_lst = [];
        $this->usr_bool_field_lst = [];
        $this->usr_only_field_lst = [];
        $this->grp_field_lst = [];

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
        $this->join_usr_fields = false;
        $this->join2_usr_fields = false;
        $this->join3_usr_fields = false;
        $this->join4_usr_fields = false;
        $this->join_usr_query = false;
        $this->join2_usr_query = false;
        $this->join3_usr_query = false;
        $this->join4_usr_query = false;
        $this->join_sub_query = false;
        $this->join2_sub_query = false;
        $this->join3_sub_query = false;
        $this->join4_sub_query = false;
        $this->join_usr_added = false;
        $this->join_type = '';
        $this->join2_type = '';
        $this->join3_type = '';
        $this->join4_type = '';
    }


    /*
     * set and get
     */

    /**
     * set the database type and reset the object
     *
     * @param string $db_type the database type as string
     * @return void
     */
    function set_db_type(string $db_type): void
    {
        $this->db_type = $db_type;
        $this->reset();
    }

    /**
     * @return string $db_type the database type as string
     */
    function db_type(): string
    {
        return $this->db_type;
    }

    /**
     * set the id field based on the table name of not overwritten
     * @param string|array $given_name to overwrite the id field name
     * @return void
     */
    function set_id_field(string|array $given_name = ''): void
    {
        if ($given_name != '') {
            $this->id_field = $given_name;
        } else {
            $this->id_field = $this->get_id_field_name($this->class);
        }
    }

    /**
     * set a dummy id field for a union query
     * @param string|array $given_name with the field name or list of field names
     * @return void
     */
    function set_id_field_dummy(string|array $given_name = ''): void
    {
        if ($given_name != '') {
            $this->id_field_dummy = $given_name;
        }
    }

    /**
     * set a dummy id field for a union query
     * @param string|array $given_name with the field name or list of field names
     * @return void
     */
    function set_id_field_num_dummy(string|array $given_name = ''): void
    {
        if ($given_name != '') {
            $this->id_field_num_dummy = $given_name;
        }
    }

    /**
     * set a dummy id field for a union query
     * @param string|array $given_name with the field name or list of field names
     * @return void
     */
    function set_id_field_usr_dummy(string|array $given_name = ''): void
    {
        if ($given_name != '') {
            $this->id_field_usr_dummy = $given_name;
        }
    }

    /**
     * set the complete par list e.g. for unition queries to get the parameters from the previous union part
     * @param sql_field_list $fvt_lst list of parameters already set
     * @return void
     */
    function set_par_list(sql_field_list $fvt_lst): void
    {
        $this->par_lst = $fvt_lst;
    }

    function par_list(): sql_field_list
    {
        return $this->par_lst;
    }

    function par_values(): array
    {
        return $this->par_lst->values();
    }

    /**
     * get the preloaded table id for change log entries
     *
     * @param string $class the class name including the namespace
     * @return int the database id of the table selected by the given class
     */
    function table_id(string $class): int
    {
        $lib = new library();
        global $change_table_list;
        return $change_table_list->id($this->get_table_name($class));
    }


    /*
     * basic interface function for the private class parameter
     */

    /**
     * create a sql parameter object with presets based on the class and a sql type list
     *
     * @param string $class the class name to which the sql statements should be created
     * @param sql_type_list $sc_par_lst list of sql types to specify which kind of sql statement should be created
     * @return sql_par set of sql parameters with some presets
     */
    function sql_par(string $class, sql_type_list $sc_par_lst): sql_par
    {
        $this->set_class($class, new sql_type_list([]));
        return new sql_par($class, $sc_par_lst);
    }

    /**
     * define the table that should be used for the next select, insert, update or delete statement
     * resets all previous db query settings such as fields, user_fields, so this should be the first statement when defining a database query
     * TODO check that this is always called directly before the query is created, so that
     * TODO check if this is called with the class name and if there are exceptions
     *
     * @param string $class is a string that is used to select the table name, the id field and the name field
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the table name extension that is not implied in the $sc_par_lst e.g. to switch between standard and prime values
     * @return bool true if setting the type was successful
     */
    function set_class(string $class, sql_type_list $sc_par_lst = new sql_type_list([]), string $ext = ''): bool
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
        $this->set_table($sc_par_lst, $ext);
        $this->set_id_field();
        return true;
    }

    /**
     * set the query name for the prepared query
     * @param string $query_name the suggested query name
     * @return void
     */
    function set_name(string $query_name): void
    {
        if (strlen($query_name) > sql_db::SQL_QUERY_NAME_MAX_LEN) {
            log_warning('SQL query name ' . $query_name . ' is longer than '
                . sql_db::SQL_QUERY_NAME_MAX_LEN . ' char, which is not supported');
        }
        // the query name cannot be longer than 62 chars
        $this->query_name = substr($query_name, 0, sql_db::SQL_QUERY_NAME_MAX_LEN);
    }

    /**
     * set the user id of the user who has requested the database access
     * by default the user also should see his/her/its data
     * @param int $usr_id the id of the user who ants to see the data
     */
    function set_usr(int $usr_id): void
    {
        $this->usr_id = $usr_id;
        $this->usr_view_id = $usr_id;
    }

    /**
     * define the fields that should be returned in a select query
     * @param array $field_lst list of the non-user specific fields that should be loaded from the database
     */
    function set_fields(array $field_lst): void
    {
        $this->field_lst = $field_lst;
    }

    /**
     * define the fields that should be returned in a select query with dummy values for a union query
     * @param array $field_lst list of the non-user specific fields that should be loaded from the database
     */
    function set_fields_dummy(array $field_lst): void
    {
        $this->field_lst_dummy = $field_lst;
    }

    /**
     * define the fields that should be returned in a select query with dummy values for a union query
     * @param array $field_lst list of the non-user specific fields that should be loaded from the database
     */
    function set_fields_num_dummy(array $field_lst): void
    {
        $this->field_lst_num_dummy = $field_lst;
    }

    /**
     * define the fields that should be returned in a select query with dummy values for a union query
     * @param array $field_lst list of the non-user specific fields that should be loaded from the database
     */
    function set_fields_date_dummy(array $field_lst): void
    {
        $this->field_lst_date_dummy = $field_lst;
    }

    /**
     * activate that in the SQL statement the user sandbox name field should be included
     * @param bool $std_fld true if the standard field e.g. the user id should no be added again
     * @param string $usr_par the name of the user id parameter e.g. $1 for some postgres queries for correct merge in union queries
     */
    function set_usr_query(bool $std_fld = true, string $usr_par = ''): void
    {
        if ($this->grp_query) {
            log_err('Group calculation cannot be combined with a user query');
        }
        $this->usr_query = true;
        $this->join_usr_query = true;
        $this->set_user_join($std_fld, $usr_par);
    }

    /**
     * define a field that is taken from a complex sub query that is not yet created
     * @param string $sql the sql of the sub query
     * @param array $join_field_lst the field names from the sub query that should be included in the main query
     * @param string $join_field the field that should be used to link the queries
     * @return void
     */
    function set_join_sql(string $sql, array $join_field_lst, string $join_field): void
    {
        //$this->sub_sql = $sql;
        if ($this->join_type == '' and !$this->join_force_rename
            or ($this->join_field == $join_field and $join_field != '')) {
            $this->join_type = $sql;
            $this->join_field_lst = $join_field_lst;
            $this->join_field = $join_field;
            $this->join_sub_query = true;
        } elseif ($this->join2_type == '' and !$this->join2_force_rename
            or ($this->join2_field == $join_field and $join_field != '')) {
            $this->join2_type = $sql;
            $this->join2_field_lst = $join_field_lst;
            $this->join2_field = $join_field;
            $this->join2_sub_query = true;
        } elseif ($this->join3_type == '' and !$this->join3_force_rename
            or ($this->join3_field == $join_field and $join_field != '')) {
            $this->join3_type = $sql;
            $this->join3_field_lst = $join_field_lst;
            $this->join3_field = $join_field;
            $this->join3_sub_query = true;
        } elseif ($this->join4_type == '' and !$this->join4_force_rename
            or ($this->join4_field == $join_field and $join_field != '')) {
            $this->join4_type = $sql;
            $this->join4_field_lst = $join_field_lst;
            $this->join4_field = $join_field;
            $this->join4_sub_query = true;
        } else {
            log_err('Max four table joins expected on version ' . PRG_VERSION);
        }


    }

    /**
     * activate that in the SQL statement the user sandbox name field should be included
     */
    function set_grp_query(): void
    {
        if ($this->usr_query) {
            log_err('Group calculation cannot be combined with other query types');
        }
        $this->grp_query = true;
    }

    /**
     * set the SQL statement for the user sandbox fields that should be returned in a select query
     * which can be user specific
     *
     * @param array $usr_field_lst list of the user specific fields that should be loaded from the database
     */
    function set_usr_fields(array $usr_field_lst): void
    {
        $this->usr_field_lst = $usr_field_lst;
        $this->set_usr_query();
    }

    /**
     * set the SQL statement for the numeric user sandbox fields that should be returned in a select query
     * which can be user specific
     *
     * @param array $usr_field_lst list of the numeric user specific fields that should be loaded from the database
     * @param bool $std_fld false if the standard fields e.g. the user id should no be added again
     * @param string $usr_par the name of the user id parameter e.g. $1 for some postgres queries for correct merge in union queries
     */
    function set_usr_num_fields(
        array  $usr_field_lst,
        bool   $std_fld = true,
        string $usr_par = ''): void
    {
        $this->usr_num_field_lst = $usr_field_lst;
        $this->set_usr_query($std_fld, $usr_par);
    }

    function set_usr_only_fields($field_lst): void
    {
        $this->usr_only_field_lst = $field_lst;
        $this->set_usr_query();
    }

    /**
     * set the order SQL statement
     * @param string $order_text the sql order statement
     */
    function set_order_text(string $order_text): void
    {
        $this->order = ' ORDER BY ' . $order_text;
    }

    /**
     * add a list of fields to the result that are taken from another table
     * must be set AFTER the set_usr_fields, set_usr_num_fields, set_usr_bool_fields, set_usr_bool_fields or set_usr_only_fields for correct handling of $this->usr_join_query
     * @param array $join_field_lst are the field names that should be included in the result
     * @param string $join_type is the table from where the fields should be taken; use the type name, not the table name
     * @param string $join_field is the index field that should be used for the join that must exist in both tables, default is the id of the joined table
     *                           if empty the field will be guessed
     * @param string $join_to_field if set the field name in the joined table that should be used for the join; only needed, if the joined field name differ from the type id field
     * @param string $join_select_field if set the field name in the joined table that should be used for a where selection
     * @param int $join_select_id if $join_select_field is set the id (int) used for the selection
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
            $this->join_sub_query = false;
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
            $this->join2_sub_query = false;
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
            $this->join3_sub_query = false;
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
            $this->join4_sub_query = false;
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
            $this->join_usr_fields = true;
        } elseif ($this->join2_type == ''
            or (($this->join2_field == $join_field or $join_field == '')
                and ($this->join2_to_field == $join_to_field or $join_to_field == ''))) {
            $this->join2_type = $join_type;
            $this->join2_usr_field_lst = $join_field_lst;
            $this->join2_field = $join_field;
            $this->join2_to_field = $join_to_field;
            $this->join2_force_rename = $force_rename;
            $this->join2_usr_fields = true;
        } elseif ($this->join3_type == ''
            or (($this->join3_field == $join_field or $join_field == '')
                and ($this->join3_to_field == $join_to_field or $join_to_field == ''))) {
            $this->join3_type = $join_type;
            $this->join3_usr_field_lst = $join_field_lst;
            $this->join3_field = $join_field;
            $this->join3_to_field = $join_to_field;
            $this->join3_force_rename = $force_rename;
            $this->join3_usr_fields = true;
        } elseif ($this->join4_type == ''
            or (($this->join4_field == $join_field or $join_field == '')
                and ($this->join4_to_field == $join_to_field or $join_to_field == ''))) {
            $this->join4_type = $join_type;
            $this->join4_usr_field_lst = $join_field_lst;
            $this->join4_field = $join_field;
            $this->join4_to_field = $join_to_field;
            $this->join4_force_rename = $force_rename;
            $this->join4_usr_fields = true;
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
            $this->join_usr_fields = true;
        } elseif ($this->join2_type == ''
            or (($this->join2_field == $join_field and $join_field != '')
                and ($this->join2_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join2_type = $join_type;
            $this->join2_usr_num_field_lst = $join_field_lst;
            $this->join2_field = $join_field;
            $this->join2_to_field = $join_to_field;
            $this->join2_force_rename = $force_rename;
            $this->join2_usr_fields = true;
        } elseif ($this->join3_type == ''
            or (($this->join3_field == $join_field and $join_field != '')
                and ($this->join3_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join3_type = $join_type;
            $this->join3_usr_num_field_lst = $join_field_lst;
            $this->join3_field = $join_field;
            $this->join3_to_field = $join_to_field;
            $this->join3_force_rename = $force_rename;
            $this->join3_usr_fields = true;
        } elseif ($this->join4_type == ''
            or (($this->join4_field == $join_field and $join_field != '')
                and ($this->join4_to_field == $join_to_field and $join_to_field != ''))) {
            $this->join4_type = $join_type;
            $this->join4_usr_num_field_lst = $join_field_lst;
            $this->join4_field = $join_field;
            $this->join4_to_field = $join_to_field;
            $this->join4_force_rename = $force_rename;
            $this->join4_usr_fields = true;
        } else {
            log_err('Max four table joins expected in version ' . PRG_VERSION);
        }
    }


    /*
     * where
     */

    /**
     * set the where condition based on a field. value and type list
     *
     * @param sql_par_field_list $fvt_lst list with the ids for the where condition
     * @return void
     */
    function add_where_fvt(sql_par_field_list $fvt_lst): void
    {
        foreach ($fvt_lst->lst as $fvt) {
            $this->add_where($fvt->name, $fvt->value, $fvt->type, '', $fvt->par_name);
        }
    }

    /**
     * add a where condition a list of id are one field or another
     * e.g. used to select both sides of a phrase tree
     * TODO move the table prefix to a separate parameter
     *
     * @param string $fld the field name used in the sql where statement
     * @param int|string|array|null $fld_val with the database id that should be selected
     * @param sql_par_type|null $spt to force using a non-standard parameter type e.g. OR instead of AND
     * @param string|null $tbl the table name or letter if not the standard table
     * @param string $name the unique name of the parameter to force to use the same parameter more than once
     * @return void
     */
    function add_where(
        string                $fld,
        int|string|array|null $fld_val,
        sql_par_type|null     $spt = null,
        string|null           $tbl = null,
        string                $name = '',
        int                   $offset = 0
    ): void
    {
        // set the default parameter type for the sql parameter type (spt)
        if ($spt == null) {
            $spt = $this->get_sql_par_type($fld_val);
        }

        // add the parameter for the where selection list
        $this->add_where_par($fld, $fld_val, $spt, $tbl, $name, $offset);
        // add the added parameter to the where list
        $this->add_where_no_par($tbl, $fld, $spt, $this->get_par_pos());
    }

    /**
     * add the parameter for a where condition a list of id are one field or another
     * e.g. used to select both sides of a phrase tree
     * TODO move the table prefix to a separate parameter
     *
     * @param string $fld the field name used in the sql where statement
     * @param int|string|array|null $fld_val with the database id that should be selected
     * @param sql_par_type|null $spt to force using a non-standard parameter type e.g. OR instead of AND
     * @param string|null $tbl the table name or letter if not the standard table
     * @param string $name the unique name of the parameter to force to use the same parameter more than once
     * @return void
     */
    function add_where_par(
        string                $fld,
        int|string|array|null $fld_val,
        sql_par_type|null     $spt = null,
        string|null           $tbl = null,
        string                $name = '',
        int                   $offset = 0
    ): void
    {

        // if the parameter has already been used
        if ($name == '') {
            if ($this->par_lst->has($fld)) {
                $name = $this->par_name($this->par_lst->pos($fld));
            } else {
                $pos = $this->par_lst->count() + 1 + $offset;
                $name = $this->par_name($pos);
            }
        }
        // add a null parameter e.g. for the value or result group id
        if ($fld_val === null) {
            if ($spt === null) {
                log_err('value and type missing in add_where');
            } else {
                $this->add_par($spt, 0, $name);
            }
        } else {

            // format the values if needed
            if ($spt == sql_par_type::INT_LIST
                or $spt == sql_par_type::INT_LIST_OR) {
                $this->add_par($spt, $this->int_array_to_sql_string($fld_val), $name);
            } elseif ($spt == sql_par_type::TEXT_LIST) {
                $this->add_par($spt, $this->str_array_to_sql_string($fld_val), $name);
            } elseif ($spt == sql_par_type::INT
                or $spt == sql_par_type::INT_SMALL
                or $spt == sql_par_type::INT_HIGHER
                or $spt == sql_par_type::INT_LOWER
                or $spt == sql_par_type::INT_OR
                or $spt == sql_par_type::INT_NOT
                or $spt == sql_par_type::INT_NOT_OR_NULL
                or $spt == sql_par_type::LIMIT
                or $spt == sql_par_type::OFFSET) {
                $this->add_par($spt, $fld_val, $name);
            } elseif ($spt == sql_par_type::INT_SAME
                or $spt == sql_par_type::INT_SAME_OR) {
                $this->add_par($spt, $fld_val, $name);
            } elseif ($spt->is_text()) {
                $this->add_par($spt, $fld_val, $name);
            } elseif ($spt == sql_par_type::CONST
                or $spt == sql_par_type::CONST_NOT
                or $spt == sql_par_type::CONST_NOT_IN) {
                $this->add_par($spt, $fld_val, $name);
                log_debug('For SQL parameter type const no parameter is needed');
            } elseif ($spt == sql_par_type::MIN
                or $spt == sql_par_type::MAX
                or $spt == sql_par_type::COUNT) {
                $this->add_par($spt, '', $name);
                log_debug('For group SQL parameter type and no parameter and value is needed');
            } elseif ($spt == sql_par_type::IS_NULL) {
                $this->add_par($spt, '', $name);
            } elseif ($spt == sql_par_type::NOT_NULL) {
                $this->add_par($spt, '', $name);
            } elseif ($spt == sql_par_type::LIKE_R) {
                $this->add_par($spt, $fld_val . '%', $name);
            } elseif ($spt == sql_par_type::LIKE
                or $spt == sql_par_type::LIKE_OR) {
                $this->add_par($spt, '%' . $fld_val . '%', $name);
            } else {
                log_err('SQL parameter type ' . $spt->value . ' not expected');
            }
        }
    }

    function add_where_no_par(?string $tbl, string $fld, sql_par_type $spt, int $pos): void
    {
        // add the parameter to the where list
        $pwh = new sql_where();
        $pwh->tbl = $tbl;
        $pwh->fld = $fld;
        $pwh->typ = $spt;
        $pwh->pos = $pos;
        $this->par_where->add($pwh);
    }


    /*
     * statement
     */

    /**
     * create a SQL select statement for the select database
     * base on the previous set parameters
     *
     * @param int $par_offset in case of a sub query the number of parameter set until here of the main query
     * @param bool $has_id to be able to create also SQL statements for tables that does not have a single unique key
     * @param bool $prepare can be set to false the created sql parts of a union query
     * @return string the created SQL statement in the previous set dialect
     */
    function sql(int $par_offset = 0, bool $has_id = true, bool $prepare = true): string
    {
        // check if the minimum parameters are set
        if ($this->query_name == '') {
            if ($par_offset > 0 or $this->sub_query) {
                log_info('SQL statement is not yet named, which is not required for a sub query');
            } else {
                log_err('SQL statement is not yet named');
            }
        }
        // prepare the SQL statement parts that have dependencies to each other
        $fields = $this->fields($has_id);
        $from = $this->from($fields, $par_offset);
        $where = $this->where($par_offset);

        // create a prepare SQL statement if possible
        if ($prepare) {
            $sql = $this->prepare_this_sql();
        } else {
            $sql = sql::SELECT;
        }

        $sql .= $fields . $from . $this->join . $where . $this->order . $this->get_page();

        if ($prepare) {
            return $this->end_sql($sql);
        } else {
            return $sql;
        }
    }

    /**
     * create the sql statement to add a row to the database
     * TODO add fields types for inserting prime values to change the id to smallint
     * TODO check if log_err is always set correctly
     *
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types to add
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param bool $log_err false if called from a log function to prevent loops
     * @param string $val_tbl name of the table to select the values to insert
     * @param string $chg_add_fld the field name of the field that should be added (only used for change log)
     * @param string $chg_row_fld the row name of the field that should be added (only used for change log)
     * @return string the prepared sql insert statement
     */
    function create_sql_insert(
        sql_par_field_list $fvt_lst,
        sql_type_list      $sc_par_lst = new sql_type_list([]),
        bool               $log_err = true,
        string             $val_tbl = '',
        string             $chg_add_fld = '',
        string             $chg_row_fld = '',
        string             $new_id_fld = '',
        string             $par_name_in = ''
    ): string
    {
        $lib = new library();

        // check if the minimum parameters are set
        if ($this->query_name == '') {
            log_err('SQL statement is not yet named');
        }

        // set the missing parameter from the list
        if ($sc_par_lst->is_sub_tbl() or $sc_par_lst->is_list_tbl()) {
            $this->sub_query = true;
        }
        if ($sc_par_lst->is_list_tbl()) {
            $this->list_query = true;
        }

        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $insert_part = $sc_par_lst->is_insert_part();
        $update_part = $sc_par_lst->is_update_part();
        $delete_part = $sc_par_lst->is_delete_part();

        $fld_names = $fvt_lst->names();
        $fld_names_esc = [];
        foreach ($fld_names as $fld_name) {
            $fld_names_esc[] = $this->name_sql_esc($fld_name);
        }
        $sql_fld = $lib->sql_array($fld_names_esc, ' (', ') ');

        // get the value parameter types
        $par_pos = 1;
        $use_named_par = $sc_par_lst->use_named_par();
        foreach ($fvt_lst->lst as $fvt) {
            $par_name = $fvt->name;
            $par_val = $fvt->value;
            $par_typ = $fvt->type;
            // for field not yet split use the id by default
            if ($fvt->id != null and $fvt->type_id != null) {
                $par_val = $fvt->id;
                $par_typ = $fvt->type_id;
            }
            $par = $fvt->par_name;
            if ($fvt->value != sql::NOW) {
                if ($par_typ == '') {
                    $par_typ = $this->get_sql_par_type($par_val);
                }
                if ($use_named_par) {
                    // TODO remove these exceptions
                    if ($par_name == change::FLD_FIELD_ID) {
                        $par_name = sql::FLD_LOG_FIELD_PREFIX . $chg_add_fld;
                    }
                    if ($par_name == change::FLD_OLD_VALUE) {
                        if ($par_name_in != '') {
                            $par_name = $par_name_in;
                        } else {
                            $par_name = $chg_add_fld;
                        }
                        if (!$delete_part) {
                            $par_name = $par_name . change::FLD_OLD_EXT;
                        }
                    }
                    if ($par_name == change::FLD_NEW_VALUE) {
                        if ($par_name_in != '') {
                            $par_name = $par_name_in;
                        } else {
                            $par_name = $chg_add_fld;
                        }
                    }
                    if ($par_name == change::FLD_OLD_ID) {
                        $par_name = $chg_add_fld . change::FLD_OLD_EXT;
                    }
                    if ($par_name == change::FLD_ROW_ID
                        and $val_tbl != ''
                        and !$sc_par_lst->use_select_for_insert()) {
                        $par_name = $val_tbl . '.' . $chg_row_fld;
                    } else {
                        if ($par_name == change::FLD_ROW_ID
                            and ($usr_tbl or $insert_part or $update_part or $delete_part)) {
                            if ($this->db_type == sql_db::MYSQL
                                and !$usr_tbl
                                and $insert_part) {
                                if ($par != '') {
                                    $par_name = sql::PAR_PREFIX_MYSQL . $par;
                                } else {
                                    if ($chg_row_fld == sql::NAME_SEP . group::FLD_ID) {
                                        $par_name = $chg_row_fld;
                                    } else {
                                        $par_name = sql::PAR_PREFIX_MYSQL . $chg_row_fld;
                                    }
                                }
                            } else {
                                if ($par != '') {
                                    $par_name = $par;
                                } else {
                                    $par_name = $chg_row_fld;
                                }
                            }
                        } else {
                            if ($par != '' and !$usr_tbl) {
                                $par_name = $par;
                            } else {
                                if (($par_name == change::FLD_OLD_ID or $par_name == change::FLD_NEW_ID) and $par_name != '') {
                                    $par_name = sql::PAR_PREFIX . $chg_add_fld;
                                } else {
                                    $par_name = sql::PAR_PREFIX . $par_name;
                                }
                            }
                        }
                    }
                } else {
                    $par_name = $this->par_name($par_pos);
                }
                $this->par_lst->add_field($par_name, $par_val, $par_typ);
                $par_pos++;
            } else {
                $this->par_lst->add_value($par_val);
            }
        }
        $sql_val = $this->par_lst->sql_names();

        // create a prepare SQL statement if possible
        $sql_type = self::INSERT;
        $sc_par_lst_end = [];
        if ($sc_par_lst->incl_log()) {
            $sql_type = self::FUNCTION;
        }
        $sql = $this->prepare_this_sql($sql_type);
        if ($sc_par_lst->incl_log()) {
            $sql = $this->prepare_this_sql(self::FUNCTION, $sc_par_lst);
            return $this->end_sql($sql, $sql_type);
        } else {
            $sql = $this->prepare_this_sql(self::INSERT);
            $sql .= ' INTO ' . $this->name_sql_esc($this->table);
            $sql .= $sql_fld;
            if ($sc_par_lst->use_select_for_insert() or $val_tbl != '' or $usr_tbl) {
                $sql .= ' ' . sql::SELECT . ' ';
                $sql .= $sql_val;
            } else {
                $sql .= ' VALUES ';
                $sql .= '(' . $sql_val . ')';
            }
            if ($val_tbl != '') {
                $sql .= ' ' . sql::FROM . ' ' . $val_tbl;
                $sql_type = self::FUNCTION;
            } else {
                // for log entries and user changes the change id does not need to be returned
                // to indicate this tell the sql end function that this is a log table
                if (($this->class == change::class or $this->class == change_link::class)
                    and $sc_par_lst->use_named_par()) {
                    $sc_par_lst->add(sql_type::NO_ID_RETURN);
                }
            }
            $sc_par_lst_end = $sc_par_lst;
        }

        return $this->end_sql($sql, $sql_type, $sc_par_lst_end, $new_id_fld);
    }

    /**
     * create the sql statement to update a row to the database
     * TODO use a sql_par_field for the id_field
     *
     * @param string|array $id_field name of the id field (or list of field names)
     * @param int|string|array $id the unique id of the row that should be updated
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param bool $log_err false if
     * @param string $val_tbl name of the table to select the values to insert
     * @param string $chg_row_fld the row name of the field that should be added (only used for change log)
     * @return string the prepared sql insert statement
     */
    function create_sql_update(
        string|array       $id_field,
        string|array|int   $id,
        sql_par_field_list $fvt_lst,
        array              $types = [],
        sql_type_list      $sc_par_lst = new sql_type_list([]),
        bool               $log_err = true,
        string             $val_tbl = '',
        string             $chg_row_fld = ''): string
    {

        $id_field_par = '';
        $use_named_par = $sc_par_lst->use_named_par();
        $usr_tbl = $sc_par_lst->is_usr_tbl();

        // check if the minimum parameters are set
        if ($this->query_name == '') {
            log_err('SQL statement is not yet named');
        }

        // gat the value parameter types
        $par_pos = 1;
        foreach ($fvt_lst->lst as $fvt) {
            $fld = $fvt->name;
            $val = $fvt->value;
            if ($fvt->type_id != null) {
                $typ = $fvt->type_id;
            } else {
                $typ = $fvt->type;
            }
            if ($val != sql::NOW) {
                if ($typ != '') {
                    $par_type = $typ;
                } else {
                    $par_type = $this->get_sql_par_type($val);
                }
                if ($use_named_par) {
                    $fld_name = $fld;
                    $fld_name = '_' . $fld_name;
                    $par_name = $fld_name;
                } else {
                    $par_name = $this->par_name($par_pos);
                }
                $this->par_lst->add_field($par_name, $val, $par_type);
                $par_pos++;
            }
        }
        $offset = $par_pos - 1;

        // prepare the where class
        // TODO maybe can be removed because done already in the calling function
        if (is_array($id_field)) {
            if (!is_array($id)) {
                $grp_id = new group_id();
                $id_lst = $grp_id->get_array($id, true);
                foreach ($id_lst as $key => $value) {
                    if ($value == null) {
                        $id_lst[$key] = 0;
                    }
                }
            } else {
                $id_lst = $id;
            }
            $sql_where = $this->sql_where($id_field, $id_lst, $offset, $id_field_par);
        } else {
            if ($use_named_par) {
                if ($sc_par_lst->is_insert()) {
                    $id_field_used = $this->table . '.' . $id_field;
                    $sql_where = $this->sql_where($id_field_used, $id, $offset, $id);
                } else {
                    if ($usr_tbl) {
                        $sql_where = $this->sql_where_no_par(
                            [$id_field, user::FLD_ID],
                            [$id, '_' . user::FLD_ID], $offset, $id, true);

                    } else {
                        $sql_where = $this->sql_where($id_field, $id, $offset, $id);
                    }
                }
            } else {
                $sql_where = $this->sql_where($id_field, $id, $offset, $id_field_par);
            }
        }

        // create a prepare SQL statement if possible
        $sql_type = self::UPDATE;
        if ($sc_par_lst->incl_log()) {
            $sql_type = self::FUNCTION;
        }
        if ($sc_par_lst->is_sub_tbl()) {
            $this->sub_query = true;
        }
        if ($sc_par_lst->incl_log()) {
            $sql = $this->prepare_this_sql(self::FUNCTION, $sc_par_lst);
        } else {
            if ($sc_par_lst->is_update_part()) {
                $sql = sql::UPDATE;
            } else {
                $sql = $this->prepare_this_sql($sql_type);
            }
            $sql .= ' ' . $this->name_sql_esc($this->table);
            $sql_set = '';
            $i = 0;
            foreach ($fvt_lst->lst as $fvt) {
                $fld = $fvt->name;
                $val = $fvt->value;
                if ($sql_set == '') {
                    $sql_set .= ' SET ';
                } else {
                    $sql_set .= ', ';
                }
                if ($val != sql::NOW) {
                    $sql_set .= $this->name_sql_esc($fld) . ' = ' . $this->name_sql_esc($this->par_lst->name($i));
                } else {
                    $sql_set .= $this->name_sql_esc($fld) . ' = ' . $val;
                }
                $i++;
            }
            $sql .= $sql_set;

            if ($val_tbl != '') {
                $sql .= ' ' . sql::FROM . ' ' . $this->name_sql_esc($val_tbl);
            }

            $sql .= $sql_where;

        }
        return $this->end_sql($sql, $sql_type);
    }

    /**
     * create the sql statement to update a row to the database
     * using sql_par_field for the id_field
     *
     * @param sql_par_field_list $fvt_lst_id name, value and type of the id field (or list of field names)
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $val_tbl name of the table to select the values to insert
     * @return string the prepared sql insert statement
     */
    function create_sql_update_fvt(
        sql_par_field_list $fvt_lst_id,
        sql_par_field_list $fvt_lst,
        sql_type_list      $sc_par_lst = new sql_type_list([]),
        string             $val_tbl = ''): string
    {

        $id_field = $fvt_lst_id->names();
        $id = $fvt_lst_id->values();

        $id_field_par = '';
        $use_named_par = $sc_par_lst->use_named_par();
        $usr_tbl = $sc_par_lst->is_usr_tbl();

        // check if the minimum parameters are set
        if ($this->query_name == '') {
            log_err('SQL statement is not yet named');
        }

        // get the value parameter types
        $par_pos = 1;
        foreach ($fvt_lst->lst as $fvt) {
            $fld = $fvt->name;
            $val = $fvt->value;
            if ($fvt->type_id != null) {
                $typ = $fvt->type_id;
            } else {
                $typ = $fvt->type;
            }
            if ($val != sql::NOW) {
                if ($typ != '') {
                    $par_type = $typ;
                } else {
                    $par_type = $this->get_sql_par_type($val);
                }
                if ($use_named_par) {
                    $fld_name = $fld;
                    $fld_name = sql::PAR_PREFIX . $fld_name;
                    $par_name = $fld_name;
                } else {
                    $par_name = $this->par_name($par_pos);
                }
                $this->par_lst->add_field($par_name, $val, $par_type);
                $par_pos++;
            }
        }
        $offset = $par_pos - 1;

        // prepare the where class
        // TODO maybe can be removed because done already in the calling function
        if (is_array($id_field)) {
            if (!is_array($id)) {
                $grp_id = new group_id();
                $id_lst = $grp_id->get_array($id, true);
                foreach ($id_lst as $key => $value) {
                    if ($value == null) {
                        $id_lst[$key] = 0;
                    }
                }
            } else {
                $id_lst = $id;
            }

            // set missing par names
            if ($use_named_par) {
                foreach ($fvt_lst_id->lst as $fvt) {
                    if ($fvt->par_name == '') {
                        $fvt->par_name = '_' . $fvt->name;
                    }
                }
            }

            $sql_where = $this->sql_where_fvt($fvt_lst_id, $offset, $id_field_par);
        } else {
            $id = $id[0];
            if ($use_named_par) {
                if ($sc_par_lst->is_insert()) {
                    $id_field_used = $this->table . '.' . $id_field;
                    $sql_where = $this->sql_where($id_field_used, $id, $offset, $id);
                } else {
                    if ($usr_tbl) {
                        $sql_where = $this->sql_where_no_par(
                            [$id_field, user::FLD_ID],
                            [$id, '_' . user::FLD_ID], $offset, $id, true);

                    } else {
                        $sql_where = $this->sql_where($id_field, $id, $offset, $id);
                    }
                }
            } else {
                $sql_where = $this->sql_where($id_field, $id, $offset, $id_field_par);
            }
        }

        // create a prepare SQL statement if possible
        $sql_type = self::UPDATE;
        if ($sc_par_lst->incl_log()) {
            $sql_type = self::FUNCTION;
        }
        if ($sc_par_lst->is_sub_tbl()) {
            $this->sub_query = true;
        }
        if ($sc_par_lst->incl_log()) {
            $sql = $this->prepare_this_sql(self::FUNCTION, $sc_par_lst);
        } else {
            if ($sc_par_lst->is_update_part()) {
                $sql = sql::UPDATE;
            } else {
                $sql = $this->prepare_this_sql($sql_type);
            }
            $sql .= ' ' . $this->name_sql_esc($this->table);
            $sql_set = '';
            $i = 0;
            foreach ($fvt_lst->lst as $fvt) {
                $fld = $fvt->name;
                $val = $fvt->value;
                if ($sql_set == '') {
                    $sql_set .= ' SET ';
                } else {
                    $sql_set .= ', ';
                }
                if ($val != sql::NOW) {
                    $sql_set .= $this->name_sql_esc($fld) . ' = ' . $this->name_sql_esc($this->par_lst->name($i));
                    $i++;
                } else {
                    $sql_set .= $this->name_sql_esc($fld) . ' = ' . $val;
                }
            }
            $sql .= $sql_set;

            if ($val_tbl != '') {
                $sql .= ' ' . sql::FROM . ' ' . $this->name_sql_esc($val_tbl);
            }

            $sql .= $sql_where;

        }
        return $this->end_sql($sql, $sql_type);
    }

    /**
     * @return string that starts a sql function
     */
    function sql_func_start(string $fld_new_id, sql_type_list $sc_par_lst): string
    {
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        if ($this->db_type == sql_db::POSTGRES) {
            $sql = sql::FUNCTION_NAME . ' ';
            if ($fld_new_id != '' and !$usr_tbl) {
                $sql .= sql::FUNCTION_DECLARE . ' ' . $fld_new_id . ' ' . sql_field_type::INT->value . '; ';
            }
            $sql .= sql::FUNCTION_BEGIN;
        } else {
            $sql = sql::FUNCTION_BEGIN_MYSQL;
        }
        return $sql;
    }

    /**
     * @return string that starts a sql function
     */
    function sql_func_end(): string
    {
        if ($this->db_type == sql_db::POSTGRES) {
            $sql = ' ' . sql::FUNCTION_END;
        } else {
            $sql = ' ' . sql::FUNCTION_END_MYSQL;
        }
        return $sql;
    }

    /**
     * create the sql function part to log the changes
     * @param string $class the class name of the calling object
     * @param user $usr the user who has requested the change
     * @param array $fld_lst list of field names that have been changed
     * @param sql_par_field_list $fvt_lst fields (with value and type) used for the change
     * @param sql_type_list $sc_par_lst
     * @return sql_par with the sql and the list of parameters actually used
     */
    function sql_func_log(
        string             $class,
        user               $usr,
        array              $fld_lst,
        sql_par_field_list $fvt_lst,
        sql_type_list      $sc_par_lst
    ): sql_par
    {
        // set some var names to shorten the code lines
        $id_field = $this->id_field_name();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $ext = sql::NAME_SEP . sql::FILE_INSERT;
        if ($class == value::class) {
            $id_fld_new = sql::NAME_SEP . $this->id_field_name();
        } else {
            $id_fld_new = $this->var_name_new_id($sc_par_lst);
        }

        // init the result
        $qp = new sql_par($this::class);
        $par_lst_out = new sql_par_field_list();
        $qp->sql = ' ';

        // set the parameters for the log sql statement creation
        $sc_log = clone $this;
        $sc_par_lst->add(sql_type::VALUE_SELECT);
        $sc_par_lst->add(sql_type::SELECT_FOR_INSERT);
        $sc_par_lst->add(sql_type::INSERT_PART);

        // create the log sql statements for each field
        foreach ($fld_lst as $fld) {
            // init the log object
            $log = new change($usr);
            $log->set_table_by_class($class);
            $log->set_field($fld);
            $log->new_value = $fvt_lst->get_value($fld);
            if ($fvt_lst->get_id($fld) != null) {
                $log->new_id = $fvt_lst->get_id($fld);
            }
            $log->old_value = $fvt_lst->get_old($fld);
            if ($fvt_lst->get_old_id($fld) != null) {
                $log->old_id = $fvt_lst->get_old_id($fld);
            }

            // create the sql for the log entry
            $qp_log = $log->sql_insert(
                $sc_log, $sc_par_lst, $ext . '_' . $fld, '', $fld, $id_fld_new, $fvt_lst->get_par_name($fld));

            // add the fields used to the list
            // maybe later get the fields used in the change log sql from the sql
            $qp->sql .= ' ' . $qp_log->sql;
            if (!str_ends_with($qp->sql, ';')) {
                $qp->sql .= '; ';
            }
            $par_lst_out->add_field(
                user::FLD_ID,
                $fvt_lst->get_value(user::FLD_ID),
                sql_par_type::INT);
            $par_lst_out->add_field(
                change_action::FLD_ID,
                $fvt_lst->get_value(change_action::FLD_ID),
                sql_par_type::INT_SMALL);
            $par_lst_out->add_field(
                sql::FLD_LOG_FIELD_PREFIX . $fld,
                $fvt_lst->get_value(sql::FLD_LOG_FIELD_PREFIX . $fld),
                sql_par_type::INT_SMALL);
            if ($fvt_lst->get_old($fld) != null) {
                if ($fvt_lst->get_old_id($fld) != null) {
                    $par_lst_out->add_field(
                        $fvt_lst->get_par_name($fld) . change::FLD_OLD_EXT,
                        $fvt_lst->get_old($fld),
                        $fvt_lst->get_type($fld));
                    $par_lst_out->add_field(
                        $fld . change::FLD_OLD_EXT,
                        $fvt_lst->get_old_id($fld),
                        $fvt_lst->get_type_id($fld));
                } else {
                    $par_lst_out->add_field(
                        $fld . change::FLD_OLD_EXT,
                        $fvt_lst->get_old($fld),
                        $fvt_lst->get_type($fld));
                }
            }
            if ($fvt_lst->get_id($fld) != null) {
                $par_lst_out->add_field(
                    $fvt_lst->get_par_name($fld),
                    $fvt_lst->get_value($fld),
                    $fvt_lst->get_type($fld));
                $par_lst_out->add_field(
                    $fld,
                    $fvt_lst->get_id($fld),
                    $fvt_lst->get_type_id($fld));
            } else {
                $par_lst_out->add($fvt_lst->get($fld));
            }
            if ($usr_tbl) {
                $par_lst_out->add($fvt_lst->get($id_field));
            }
        }

        // transfer the fields used to the calling function
        $qp->par_fld_lst = $par_lst_out;

        return $qp;
    }

    /**
     * create the sql function part to log adding a link
     * @param sandbox|sandbox_link|sandbox_link_typed $sbx the name of the calling class use for the query names
     * @param user $usr
     * @param sql_par_field_list $fvt_lst
     * @param sql_type_list $sc_par_lst
     * @return sql_par
     */
    function sql_func_log_link(
        sandbox|sandbox_link|sandbox_link_typed $sbx,
        user                                    $usr,
        sql_par_field_list                      $fvt_lst,
        sql_type_list                           $sc_par_lst
    ): sql_par
    {
        $log = new change_link($usr);
        $log->set_table_by_class($sbx::class);
        $log->new_from_id = $sbx->from_id();
        $log->new_text_from = $sbx->from_name();
        if ($sbx->is_link_type_obj()) {
            $log->new_link_id = $sbx->type_id();
            $log->new_text_link = $sbx->type_name();
        }
        if (is_int($sbx->to_id())) {
            $log->new_to_id = $sbx->to_id();
            $log->new_text_to = $sbx->to_name();
        } else {
            // for external links the id is a string
            $log->new_text_to = $sbx->to_id();
        }

        // set the parameters for the log sql statement creation
        $sc_log = clone $this;
        $sc_par_lst->add(sql_type::VALUE_SELECT);
        $sc_par_lst->add(sql_type::SELECT_FOR_INSERT);
        $sc_par_lst->add(sql_type::INSERT_PART);

        // create the sql for the log entry
        $qp = $log->sql_insert_link(
            $sc_log, $sc_par_lst, $sbx);

        $par_lst_out = new sql_par_field_list();
        $par_lst_out->add_field(
            user::FLD_ID,
            $fvt_lst->get_value(user::FLD_ID),
            sql_par_type::INT);
        $par_lst_out->add_field(
            change_action::FLD_ID,
            $fvt_lst->get_value(change_action::FLD_ID),
            sql_par_type::INT_SMALL);
        $par_lst_out->add_field(
            change_table::FLD_ID,
            $fvt_lst->get_value(change_table::FLD_ID),
            sql_par_type::INT_SMALL);
        $qp->par_fld_lst = $par_lst_out;

        return $qp;
    }

    /**
     * create the sql function part to log adding a link
     * @param sandbox|sandbox_link|sandbox_link_typed $sbx the name of the calling class use for the query names
     * @param user $usr
     * @param sql_par_field_list $fvt_lst
     * @param sql_type_list $sc_par_lst
     * @return sql_par
     */
    function sql_func_log_user_link(
        sandbox|sandbox_link|sandbox_link_typed $sbx,
        user                                    $usr,
        sql_par_field_list                      $fvt_lst,
        sql_type_list                           $sc_par_lst
    ): sql_par
    {
        // set the vars of the log link object
        $log = new change_link($usr);
        $log->set_table_by_class($sbx::class);
        $log->old_from_id = $sbx->from_id();
        $log->new_from_id = 0;
        $log->old_text_from = $sbx->from_name();
        $log->new_text_from = '';
        if ($sbx::class == triple::class) {
            // triples have a verb as type
            $log->old_link_id = $sbx->verb_id();
            $log->new_link_id = 0;
            $log->old_text_link = $sbx->verb_name();
            $log->new_text_link = '';
        } elseif ($sbx->is_link_type_obj()) {
            // other links can have a type
            $log->old_link_id = $sbx->type_id();
            $log->new_link_id = 0;
            $log->old_text_link = $sbx->type_name();
            $log->new_text_link = '';
        }
        if (is_int($sbx->to_id())) {
            $log->old_to_id = $sbx->to_id();
            $log->old_text_to = $sbx->to_name();
        } else {
            // for external links the "to_id" is a string
            $log->old_text_to = $sbx->to_id();
        }
        $log->new_to_id = 0;
        $log->new_text_to = '';

        // set the parameters for the log sql statement creation
        $sc_log = clone $this;
        $sc_par_lst->add(sql_type::VALUE_SELECT);
        $sc_par_lst->add(sql_type::SELECT_FOR_INSERT);
        $sc_par_lst->add(sql_type::INSERT_PART);

        // create the sql for the log entry
        $qp = $log->sql_insert_link(
            $sc_log, $sc_par_lst, $sbx);

        $par_lst_out = new sql_par_field_list();
        $par_lst_out->add_field(
            user::FLD_ID,
            $fvt_lst->get_value(user::FLD_ID),
            sql_par_type::INT);
        $par_lst_out->add_field(
            change_action::FLD_ID,
            $fvt_lst->get_value(change_action::FLD_ID),
            sql_par_type::INT_SMALL);
        $par_lst_out->add_field(
            change_table::FLD_ID,
            $fvt_lst->get_value(change_table::FLD_ID),
            sql_par_type::INT_SMALL);
        $qp->par_fld_lst = $par_lst_out;

        return $qp;
    }

    /**
     * create the sql function part to log adding a link
     * @param sandbox_value $sbx the name of the calling class use for the query names
     * @param user $usr the user who has requested the change
     * @param sql_par_field_list $fvt_lst list of fields, values and types to fill the log entry
     * @param sql_type_list $sc_par_lst sql parameters e.g. if the prime table should be used
     * @return sql_par the sql statement with the paraemeter to add the log entry
     */
    function sql_func_log_value(
        sandbox_value      $sbx,
        user               $usr,
        sql_par_field_list $fvt_lst,
        sql_type_list      $sc_par_lst
    ): sql_par
    {
        // get the change table id
        global $change_table_list;
        global $change_field_list;
        $lib = new library();
        $table_name = $lib->class_to_table($sbx::class);
        $table_id = $change_table_list->id($table_name);

        // select which log to use and set the parameters
        if ($sc_par_lst->is_prime()) {
            $log = new change_values_prime($usr);
        } elseif ($sc_par_lst->is_big()) {
            $log = new change_values_big($usr);
        } else {
            $log = new change_values_norm($usr);
        }
        $log->set_table_by_class($sbx::class);
        $log->set_field(sandbox_value::FLD_VALUE);

        $log->group_id = $fvt_lst->get_value(group::FLD_ID);
        $val_old = null;
        if ($sc_par_lst->is_update()) {
            $val_old = $fvt_lst->get_old(sandbox_value::FLD_VALUE);
            $log->old_value = $val_old;
        }
        $val_new = $fvt_lst->get_value(sandbox_value::FLD_VALUE);
        $log->new_value = $val_new;

        // set the parameters for the log sql statement creation
        $sc_log = clone $this;
        $sc_par_lst->add(sql_type::VALUE_SELECT);
        $sc_par_lst->add(sql_type::SELECT_FOR_INSERT);
        $sc_par_lst->add(sql_type::INSERT_PART);

        // create the sql for the log entry
        $qp = $log->sql_insert($sc_log, $sc_par_lst, '', '', sandbox_value::FLD_VALUE);

        // fill the parameter list in order of usage in the sql
        $par_lst_out = new sql_par_field_list();
        $usr_id = $fvt_lst->get_value(user::FLD_ID);
        if ($usr_id == null) {
            $usr_id = $usr->id();
        }
        $par_lst_out->add_field(
            user::FLD_ID,
            $usr_id,
            sql_par_type::INT);
        $par_lst_out->add_field(
            change_action::FLD_ID,
            $fvt_lst->get_value(change_action::FLD_ID),
            sql_par_type::INT_SMALL);
        $par_lst_out->add_field(
            sql::FLD_LOG_FIELD_PREFIX . sandbox_value::FLD_VALUE,
            $change_field_list->id($table_id . sandbox_value::FLD_VALUE),
            change::FLD_FIELD_ID_SQLTYP
        );
        if ($sc_par_lst->is_update()) {
            $par_lst_out->add_field(
                sandbox_value::FLD_VALUE . change::FLD_OLD_EXT,
                $val_old,
                sql_field_type::NUMERIC_FLOAT
            );
        }
        if (!$sc_par_lst->is_delete()) {
            $par_lst_out->add_field(
                sandbox_value::FLD_VALUE,
                $val_new,
                sql_field_type::NUMERIC_FLOAT
            );
        }
        if (is_numeric($log->group_id)) {
            $par_lst_out->add_field(
                group::FLD_ID,
                intval($log->group_id),
                sql_par_type::INT);
        } else {
            $par_lst_out->add_field(
                group::FLD_ID,
                $log->group_id,
                sql_par_type::TEXT);
        }
        $qp->par_fld_lst = $par_lst_out;

        return $qp;
    }

    /**
     * create the call statement for insert and update sql functions
     *
     * @param sql_par $qp the query parameter with the name already set
     * @param string $name the name of the function
     * @param sql_par_field_list $par_lst_out the list of parameter used for the call
     * @return sql_par with the call statement set
     */
    function sql_call(sql_par $qp, string $name, sql_par_field_list $par_lst_out): sql_par
    {
        // create the prepared call sql statement
        $qp->call_name = $name . '_call';
        $qp->call_sql = ' ' . sql::PREPARE . ' ' . $qp->call_name;
        if ($this->db_type == sql_db::POSTGRES) {
            $qp->call_sql .= ' (' . $par_lst_out->par_types($this) . ') ' . sql::AS . ' ';
        } else {
            $qp->call_sql .= ' ' . sql::FROM . " '";
        }
        $qp->call_sql .= sql::SELECT . ' ' . $name;
        $qp->call_sql .= ' (' . $par_lst_out->par_vars($this) . ')';
        if ($this->db_type == sql_db::POSTGRES) {
            $qp->call_sql .= ';';
        } else {
            $qp->call_sql .= "';";
        }

        // create a sample call for testing
        $qp->call = ' ' . sql::SELECT . ' ' . $name . ' (';
        $call_val_str = $par_lst_out->par_sql($this);
        $qp->call .= $call_val_str . ');';

        return $qp;
    }

    /**
     * create the sql function variable name for the db row id e.g. _word_id
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return string the sql function var name for the row id e.g. _word_id
     */
    function var_name_row_id(sql_type_list $sc_par_lst): string
    {
        $result = $this->id_field_name();
        $new_id_fld = $this->var_name_new_id($sc_par_lst);
        if ($this->db_type == sql_db::POSTGRES) {
            $result = $new_id_fld;
        } elseif ($this->db_type == sql_db::MYSQL) {
            if ($sc_par_lst->is_usr_tbl()) {
                $result = self::PAR_PREFIX . $this->id_field_name();
            } else {
                $result = self::PAR_PREFIX_MYSQL . $new_id_fld;
            }
        }
        return $result;
    }

    /**
     * create the sql function variable name for the new sequence db row id e.g. new_word_id
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return string the sql function var name for the new sequence db row id e.g. new_word_id
     */
    function var_name_new_id(sql_type_list $sc_par_lst): string
    {
        $id_field = $this->id_field_name();
        $result = self::PAR_NEW_ID_PREFIX . $id_field;
        if ($sc_par_lst->is_usr_tbl()) {
            $result = self::PAR_PREFIX . $id_field;
        }
        return $result;
    }

    /**
     * create a sql statement to delete or exclude a database row
     * @param sql_par_field_list $fvt_lst_id name, value and type of the id field (or list of field names)
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return string
     */
    function create_sql_delete_fvt(
        sql_par_field_list $fvt_lst_id,
        sql_type_list      $sc_par_lst = new sql_type_list([])
    ): string
    {

        $id_fields = $fvt_lst_id->names();
        $id_field = $id_fields[0];

        // check if the minimum parameters are set
        if ($this->query_name == '') {
            log_err('SQL statement is not yet named');
        }

        if ($sc_par_lst->use_named_par()) {
            if ($sc_par_lst->is_usr_tbl()) {

                $sql_where = $this->sql_where_no_par(
                    [$id_field, user::FLD_ID],
                    ['_' . $id_field, '_' . user::FLD_ID], 0, '_' . $id_field, true);
            } else {
                $sql_where = $this->sql_where_fvt($fvt_lst_id);
            }
        } else {
            $sql_where = $this->sql_where_fvt($fvt_lst_id);
        }

        if ($sc_par_lst->incl_log()) {
            if ($sc_par_lst->create_function()) {
                $sql = $this->prepare_this_sql(self::FUNCTION, $sc_par_lst);
            } else {
                $sql = sql::DELETE . ' ' . $this->table . ' ';
                $sql .= $sql_where;
                if ($sc_par_lst->exclude_sql()) {
                    $sql .= ' ' . sql::AND . ' ' . sandbox::FLD_EXCLUDED . ' = ' . sql::TRUE;
                }
            }
        } else {
            $sql = $this->prepare_this_sql(self::DELETE);
            $sql .= ' ' . $this->name_sql_esc($this->table);
            $sql .= $sql_where;

            if ($sc_par_lst->exclude_sql()) {
                $sql .= ' ' . sql::AND . ' ' . sandbox::FLD_EXCLUDED . ' = ' . sql::TRUE;
            }
        }

        if ($sc_par_lst->create_function()) {
            return $sql;
        } else {
            return $this->end_sql($sql, self::DELETE);
        }
    }

    /**
     * create a sql statement to delete or exclude a database row
     * @param sql_par_field_list $fvt_lst_id name, value and type of the id field (or list of field names)
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param sql_par_field_list $fvt_lst list of all parameters used for the function with name, value and type
     * @return string
     */
    function create_sql_delete_fvt_new(
        sql_par_field_list $fvt_lst_id,
        sql_type_list      $sc_par_lst = new sql_type_list([]),
        sql_par_field_list $fvt_lst = new sql_par_field_list()
    ): string
    {
        if ($sc_par_lst->create_function()) {
            $sql = $this->prepare_this_sql(self::FUNCTION, $sc_par_lst, $fvt_lst);
        } else {
            $sql = $this->prepare_this_sql(self::DELETE, $sc_par_lst);
            $sql .= ' ' . $this->name_sql_esc($this->table) . ' ';
            $sql .= $this->sql_where_fvt($fvt_lst_id);
            if ($sc_par_lst->exclude_sql()) {
                $sql .= ' ' . sql::AND . ' ' . sandbox::FLD_EXCLUDED . ' = ' . sql::TRUE;
            }
            $sql .= sql::SEP;
        }

        return $sql;
    }

    /**
     * create a sql statement to delete or exclude a database row
     * TODO deprecate and replace by create_sql_delete_fvt
     *
     * @param int|string|array $id_field the id field or id fields of the table from where the row should be deleted
     * @param int|string|array $id
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return string
     */
    function create_sql_delete(
        int|string|array   $id_field,
        int|string|array   $id,
        sql_type_list      $sc_par_lst = new sql_type_list([]),
        sql_par_field_list $fvt_lst = new sql_par_field_list(),
    ): string
    {
        $excluded = $sc_par_lst->exclude_sql();

        // check if the minimum parameters are set
        if (!$sc_par_lst->is_sub_tbl()) {
            if ($this->query_name == '') {
                log_err('SQL statement is not yet named');
            }
        }

        if ($sc_par_lst->use_named_par()) {
            if ($sc_par_lst->is_usr_tbl()) {
                $sql_where = $this->sql_where_no_par(
                    [$id_field, user::FLD_ID],
                    ['_' . $id_field, '_' . user::FLD_ID], 0, '_' . $id_field, true);
            } else {
                if (is_array($id_field)) {
                    $sql_where = $this->sql_where_no_par($id_field, $id);
                } else {
                    $sql_where = $this->sql_where_no_par($id_field, '_' . $id_field, 0, '_' . $id_field);
                }
            }
        } else {
            $sql_where = $this->sql_where($id_field, $id);
        }

        if ($sc_par_lst->incl_log()) {
            if ($sc_par_lst->create_function()) {
                $sql = $this->prepare_this_sql(self::FUNCTION, $sc_par_lst, $fvt_lst);
            } else {
                $sql = sql::DELETE . ' ' . $this->table . ' ';
                $sql .= $sql_where;
                if ($excluded) {
                    $sql .= ' AND ' . sandbox::FLD_EXCLUDED . ' = ' . sql::TRUE;
                }
            }
        } else {
            $sql = $this->prepare_this_sql(self::DELETE, $sc_par_lst);
            $sql .= ' ' . $this->name_sql_esc($this->table);
            $sql .= $sql_where;

            if ($excluded) {
                $sql .= ' ' . sql::AND . ' ' . sandbox::FLD_EXCLUDED . ' = ' . sql::TRUE;
            }
        }

        if ($sc_par_lst->create_function()) {
            return $sql;
        } else {
            return $this->end_sql($sql, self::DELETE);
        }
    }

    /**
     * @param string|array $id_field the id field or id fields of the table from where the row should be deleted
     * @param int|string|array $id
     * @param int $offset
     * @param string $id_field_par
     * @param bool $is_named = false true if named parameters are used
     * @return string with the where statement
     */
    private function sql_where(
        string|array     $id_field,
        int|string|array $id,
        int              $offset = 0,
        string           $id_field_par = '',
        bool             $is_named = false
    ): string
    {
        // gat the value parameter types
        if (is_array($id_field)) {
            foreach ($id as $id_item) {
                $pos = $this->par_lst->count() + 1;
                $this->par_lst->add_field($this->par_name($pos), $id_item, $this->get_sql_par_type($id_item));
            }
        } else {
            if (!in_array($id_field_par, $this->par_lst->names())) {
                $pos = $this->par_lst->count() + 1;
                if ($id_field_par == '') {
                    $id_field_par = $this->par_name($pos);
                }
                $this->par_lst->add_field($id_field_par, $id, $this->get_sql_par_type($id));
            }
        }

        // create a prepare SQL statement if possible
        return $this->sql_where_no_par($id_field, $id, $offset, $id_field_par, $is_named);
    }

    /**
     * @param sql_par_field_list $fvt_lst the id field or id fields of the table from where the row should be deleted
     * @param int $offset
     * @param string $id_field_par
     * @param bool $is_named = false true if named parameters are used
     * @return string with the where statement
     */
    private function sql_where_fvt(
        sql_par_field_list $fvt_lst,
        int                $offset = 0,
        string             $id_field_par = '',
        bool               $is_named = false
    ): string
    {
        // gat the value parameter types
        foreach ($fvt_lst->names() as $fld) {
            $pos = $this->par_lst->count() + 1;
            if ($fvt_lst->get_par_name($fld) == '') {
                $par_name = $this->par_name($pos);
            } else {
                $par_name = $fvt_lst->get_par_name($fld);
            }
            $this->par_lst->add_field($par_name, $fvt_lst->get_value($fld), $fvt_lst->get_type($fld));
        }

        // create a prepare SQL statement if possible
        return $this->sql_where_no_par($fvt_lst->names(), $fvt_lst->values(), $offset, $id_field_par, $is_named);
    }

    /**
     * create the where part of a sql statement
     *
     * @param string|array $id_field the id field or id fields of the table from where the row should be deleted
     * @param int|string|array $id the id or list of ids to select the row
     * @param int $offset for the par number e.g. 4 for $4
     * @param string $id_field_par with the parameter name for a single field e.g. "$4" or "new_word_id"
     * @param bool $is_named true if named paremeters like "_word_name" should be used
     * @return string with the where statement
     */
    private function sql_where_no_par(
        string|array     $id_field,
        int|string|array $id,
        int              $offset = 0,
        string           $id_field_par = '',
        bool             $is_named = false
    ): string
    {
        $sql_where = '';
        if (is_array($id_field)) {
            $pos = $offset;
            foreach ($id_field as $key => $id_fld) {
                if ($sql_where != '') {
                    $sql_where .= ' ' . sql::AND . ' ';
                } else {
                    $sql_where .= ' ' . sql::WHERE . ' ';
                }
                if ($is_named) {
                    $sql_where .= $id_fld . ' = ' . $id[$key];
                } else {
                    $sql_where .= $id_fld . ' = ' . $this->par_lst->name($pos);
                }
                $pos++;
            }
        } else {
            $sql_where = ' ' . sql::WHERE . ' ' . $id_field . ' = ' . $id_field_par;
        }
        return $sql_where;
    }

    /**
     * create the where part of a sql statement
     *
     * @param sql_par_field_list $fvt_lst_id list of id fields with the value and type
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param int $offset for the par number e.g. 4 for $4
     * @return string with the where statement
     */
    private function sql_where_fvt_new(
        sql_par_field_list $fvt_lst_id,
        sql_type_list      $sc_par_lst = new sql_type_list([]),
        int                $offset = 0
    ): string
    {
        $sql_where = '';
        $pos = $offset + 1;
        foreach ($fvt_lst_id as $fvt) {
            if ($sql_where != '') {
                $sql_where .= ' ' . sql::AND . ' ';
            } else {
                $sql_where .= ' ' . sql::WHERE . ' ';
            }
            if ($sc_par_lst->use_named_par()) {
                $sql_where .= $fvt->name . ' = ' . $fvt->par_name;
            } else {
                $sql_where .= $fvt->name . ' = ' . $this->par_name($pos);
            }
            $pos++;
        }
        return $sql_where;
    }

    /**
     * define the fields that should be returned in a select query
     * @param string $fld_name list of the non-user specific fields that should be loaded from the database
     * @param sql_par_type $spt the aggregation type for the field
     */
    function add_usr_grp_field(string $fld_name, sql_par_type $spt): void
    {
        // assuming that the user specific part is selected in the sub query
        $this->add_par(sql_par_type::INT_SUB, $this->usr_id, sql_db::USR_TBL . '.' . user::FLD_ID);

        $this->grp_field_lst[] = $fld_name;
        //$this->add_par($spt, '', $fld_name);
        $this->set_grp_query();
    }


    /*
     * internal where
     */

    private function has_field(string $fld): bool
    {
        if (in_array($fld, $this->par_lst->names())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * create the "JOIN" SQL statement based on the joined user fields
     * @param bool $std_fld false if the standard fields e.g. the user id should not be added again
     * @param string $usr_par the name of the user id parameter e.g. $1 for some postgres queries for correct merge in union queries
     */
    private function set_user_join(bool $std_fld, string $usr_par = ''): void
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
                    if ($this->query_name == '' and !$this->sub_query) {
                        $this->join .= $this->usr_view_id;
                    } else {
                        if ($std_fld) {
                            $usr_fld = '';
                            if ($usr_par == '') {
                                $usr_par = $this->par_name();
                                $usr_fld = sql_db::USR_TBL . '.' . user::FLD_ID;
                            }
                            $this->join_usr_par_name = $usr_par;
                            if ($usr_par == '' or !$this->has_field($usr_par)) {
                                $this->add_par(sql_par_type::INT, $this->usr_id, $usr_fld);
                            }
                        } else {
                            // TODO make the field pos of the user field more dynamic to cover more cases
                            $this->join_usr_par_name = $this->par_name(1, false);
                        }
                        $this->join .= $this->join_usr_par_name;
                    }
                }
                $this->join_usr_added = true;
            }
        }
    }

    /**
     * @param string|array|float|int|DateTime|null $fld_val the field value to detect the sql parameter type that should be used
     * @return sql_par_type the prime sql parameter type
     */
    public function get_sql_par_type(string|array|float|int|DateTime|null $fld_val): sql_par_type
    {
        $text_type = sql_par_type::TEXT;
        if ($fld_val == sql::NOW) {
            $text_type = sql_par_type::TIME;
        }
        return match (gettype($fld_val)) {
            "NULL", 'string' => $text_type,
            'double' => sql_par_type::FLOAT,
            'array' => sql_par_type::INT_LIST,
            default => sql_par_type::INT,
        };
    }

    /**
     * add a parameter for a prepared query
     * @param sql_par_type $par_type the SQL parameter type used e.g. for Postgres as int or text
     * @param string $value the int, float value or text value that is used for the concrete execution of the query
     * @param string $name the field name as used for the where condition including the table name if needed
     */
    private function add_par(
        sql_par_type $par_type,
        string       $value,
        string       $name = ''
    ): void
    {
        $this->par_lst->add_field($name, $value, $par_type);
    }

    /**
     * @return int with the last add parameter position (count minus 1 because the array starts with zero)
     */
    private function get_par_pos(): int
    {
        return $this->par_lst->count() - 1;
    }

    /**
     * convert an array of int values to a sql string that can be used for an IN condition
     * @param array $int_array
     * @return string
     */
    private function int_array_to_sql_string(array $int_array): string
    {
        return "{" . implode(",", $int_array) . "}";
    }

    /**
     * convert an array of int values to a sql string that can be used for an IN condition
     * @param array $str_array
     * @return string
     */
    private function str_array_to_sql_string(array $str_array): string
    {
        // TODO check how to escape ","
        return "{" . implode(",", $str_array) . "}";
    }

    /**
     * create the field statement based on the fields
     * @param $has_id
     * @return string the sql field statement
     */
    private function fields($has_id): string
    {
        // init
        $result = '';
        $field_lst = [];
        $usr_field_lst = [];

        if ($has_id) {
            // start with the dummy id field
            if ($this->id_field_dummy != '') {
                if (is_array($this->id_field)) {
                    foreach ($this->id_field_dummy as $id_fld) {
                        $field_lst[] = $id_fld;
                    }
                } else {
                    $field_lst[] = $this->id_field_dummy;
                }
            }

            // add the fields that part of all standard tables so id and name on top of the field list
            if (is_array($this->id_field)) {
                foreach ($this->id_field as $id_fld) {
                    $field_lst[] = $id_fld;
                }
            } else {
                $field_lst[] = $this->id_field;
            }

            // add the dummy usr id fields
            if ($this->id_field_usr_dummy != '') {
                if (is_array($this->id_field_usr_dummy)) {
                    foreach ($this->id_field_usr_dummy as $id_fld) {
                        $field_lst[] = $id_fld;
                    }
                } else {
                    $field_lst[] = $this->id_field_usr_dummy;
                }
            }

            // add the dummy num id fields
            if (is_array($this->id_field_num_dummy)) {
                foreach ($this->id_field_num_dummy as $id_fld) {
                    $field_lst[] = $id_fld;
                }
            }

            if ($this->usr_query) {
                // user can change the name of an object, that's why the target field list is either $usr_field_lst or $field_lst
                if (!in_array($this->class, self::DB_TYPES_NOT_NAMED)) {
                    $usr_field_lst[] = $this->name_field();
                }
                if (!$this->all_query) {
                    $field_lst[] = user::FLD_ID;
                }
            } else {
                if (!in_array($this->class, self::DB_TYPES_NOT_NAMED)) {
                    $field_lst[] = $this->name_field();
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
            foreach ($this->field_lst_num_dummy as $field) {
                if (!in_array($field, $field_lst)) {
                    $field_lst[] = $field;
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
            // escape the field name
            if (is_array($field)) {
                $fld_lst = array();
                foreach ($field as $fld) {
                    $fld_lst[] = $this->name_sql_esc($fld);
                }
                $field = $fld_lst;
            } else {
                $field = $this->name_sql_esc($field);
            }
            $result = $this->sep($result);

            // dummy id fields
            $fld_used = false;
            if ($this->id_field_dummy != '') {
                if (is_array($this->id_field_dummy)) {
                    if (in_array($field, $this->id_field_dummy)) {
                        if ($this->db_type() == sql_db::POSTGRES) {
                            $result .= " '' AS " . $field;
                        } else {
                            $result .= " NULL AS " . $field;
                        }
                        $fld_used = true;
                    }
                } else {
                    if ($field == $this->id_field_dummy) {
                        if ($this->db_type() == sql_db::POSTGRES) {
                            $result .= " '' AS " . $field;
                        } else {
                            $result .= " NULL AS " . $field;
                        }
                        $fld_used = true;
                    }
                }
            }

            if (!$fld_used) {
                if ($this->id_field_num_dummy != '') {
                    if (is_array($this->id_field_num_dummy)) {
                        if (in_array($field, $this->id_field_num_dummy)) {
                            $result .= ' 0 AS ' . $field;
                            $fld_used = true;
                        }
                    } else {
                        if ($field == $this->id_field_num_dummy) {
                            $result .= ' 0 AS ' . $field;
                            $fld_used = true;
                        }
                    }
                }
            }

            if (!$fld_used) {
                if ($this->id_field_usr_dummy != '') {
                    if (is_array($this->id_field_usr_dummy)) {
                        if (in_array($field, $this->id_field_usr_dummy)) {
                            if ($this->db_type() == sql_db::POSTGRES) {
                                $result .= " '' AS " . $field;
                            } else {
                                $result .= " NULL AS " . $field;
                            }
                            $fld_used = true;
                        }
                    } else {
                        if ($field == $this->id_field_usr_dummy) {
                            if ($this->db_type() == sql_db::POSTGRES) {
                                $result .= " '' AS " . $field;
                            } else {
                                $result .= " NULL AS " . $field;
                            }
                            $fld_used = true;
                        }
                    }
                }
            }

            // add datetime num fields
            if (!$fld_used) {
                if ($this->field_lst_num_dummy != '') {
                    if (is_array($this->field_lst_num_dummy)) {
                        if (in_array($field, $this->field_lst_num_dummy)) {
                            $result .= " 0 AS " . $field;
                            $fld_used = true;
                        }
                    } else {
                        if ($field == $this->field_lst_num_dummy) {
                            $result .= " 0 AS " . $field;
                            $fld_used = true;
                        }
                    }
                }
            }

            if (!$fld_used) {
                if ($this->usr_query or $this->join_type != '') {
                    $result .= ' ' . sql_db::STD_TBL . '.' . $field;
                    if ($field == $this->id_field) {
                        // add the user sandbox id for user sandbox queries to find out if the user sandbox has already been created
                        if ($this->all_query) {
                            $result = $this->sep($result);
                            $result .= ' ' . sql_db::USR_TBL . '.' . user::FLD_ID;
                        } else {
                            if ($this->usr_query) {
                                $result = $this->sep($result);
                                $result .= ' ' . sql_db::USR_TBL . '.' . $field . ' AS ' . sql_db::USER_PREFIX . $this->id_field;
                            }
                        }
                    }
                } else {
                    $result .= ' ' . $field;
                }
            }

        }

        // select the owner of the standard values in case of an overview query
        if ($this->all_query) {
            $result = $this->sep($result);
            $result .= ' ' . sql_db::STD_TBL . '.' . user::FLD_ID . ' AS owner_id';
        }

        // add group fields
        foreach ($this->grp_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $result = $this->sep($result);
            // TODO add min
            $result .= ' max(' . sql_db::GRP_TBL . '.' . $field . ') AS ' . sql::MAX_PREFIX . $field;
        }

        // add join fields
        foreach ($this->join_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= ' ' . sql_db::LNK_TBL . '.' . $field_esc;
            if ($this->join_force_rename) {
                $result .= ' AS ' . $this->name_sql_esc($field . '1');
            } elseif ($this->usr_query and $this->join_usr_query) {
                $result = $this->sep($result);
                $result .= ' ' . sql_db::ULK_TBL . '.' . $field_esc;
                // switched off because at the moment only the change sum should be calculated
                //} elseif ($this->join_sub_query) {
                //$result = $this->sep($result);
                //$result .= ' ' . sql_db::GRP_TBL . '.' . $field_esc;
            }
        }

        // add second join fields
        foreach ($this->join2_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= ' ' . sql_db::LNK2_TBL . '.' . $field_esc;
            if ($this->join2_force_rename) {
                $result .= ' AS ' . $this->name_sql_esc($field . '2');
            } elseif ($this->usr_query and $this->join2_usr_query) {
                $result = $this->sep($result);
                $result .= ' ' . sql_db::ULK2_TBL . '.' . $field_esc;
            } elseif ($this->join2_sub_query) {
                $result = $this->sep($result);
                $result .= ' ' . sql_db::GRP_TBL . '.' . $field_esc;
            }
        }

        // add third join fields
        foreach ($this->join3_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= ' ' . sql_db::LNK3_TBL . '.' . $field_esc;
            if ($this->join3_force_rename) {
                $result .= ' AS ' . $this->name_sql_esc($field . '3');
            } elseif ($this->usr_query and $this->join3_usr_query) {
                $result = $this->sep($result);
                $result .= ' ' . sql_db::ULK3_TBL . '.' . $field_esc;
            } elseif ($this->join3_sub_query) {
                $result = $this->sep($result);
                $result .= ' ' . sql_db::GRP_TBL . '.' . $field_esc;
            }
        }

        // add fourth join fields
        foreach ($this->join4_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= ' ' . sql_db::LNK4_TBL . '.' . $field_esc;
            if ($this->join4_force_rename) {
                $result .= ' AS ' . $this->name_sql_esc($field . '4');
            } elseif ($this->usr_query and $this->join4_usr_query) {
                if ($result != '') {
                    $result .= ', ';
                }
                $result .= ' ' . sql_db::ULK4_TBL . '.' . $field_esc;
            } elseif ($this->join4_sub_query) {
                $result = $this->sep($result);
                $result .= ' ' . sql_db::GRP_TBL . '.' . $field_esc;
            }
        }

        // add user specific fields
        foreach ($this->usr_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_text($field);
        }

        // add user specific numeric fields
        foreach ($this->usr_num_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_num($field);
        }

        // add user specific boolean fields
        foreach ($this->usr_bool_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_bool($field);
        }

        // add user specific join fields
        foreach ($this->join_usr_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            if ($this->join_force_rename) {
                $result .= $this->set_field_usr_text($field_esc, sql_db::LNK_TBL, sql_db::ULK_TBL, $this->name_sql_esc($field . '1'));
            } else {
                $result .= $this->set_field_usr_text($field_esc, sql_db::LNK_TBL, sql_db::ULK_TBL);
            }
        }

        // add user specific numeric join fields
        foreach ($this->join_usr_num_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            if ($this->join_force_rename) {
                $result .= $this->set_field_usr_num($field_esc, sql_db::LNK_TBL, sql_db::ULK_TBL, $this->name_sql_esc($field . '1'));
            } else {
                $result .= $this->set_field_usr_num($field_esc, sql_db::LNK_TBL, sql_db::ULK_TBL);
            }
        }

        // add user specific second join fields
        foreach ($this->join2_usr_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_text($field_esc, sql_db::LNK2_TBL, sql_db::ULK2_TBL, $this->name_sql_esc($field . '2'));
        }

        // add user specific numeric second join fields
        foreach ($this->join2_usr_num_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_num($field_esc, sql_db::LNK2_TBL, sql_db::ULK2_TBL, $this->name_sql_esc($field . '2'));
        }

        // add user specific third join fields
        foreach ($this->join3_usr_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_text($field_esc, sql_db::LNK3_TBL, sql_db::ULK3_TBL, $this->name_sql_esc($field . '3'));
        }

        // add user specific numeric third join fields
        foreach ($this->join3_usr_num_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_num($field_esc, sql_db::LNK3_TBL, sql_db::ULK3_TBL, $this->name_sql_esc($field . '3'));
        }

        // add user specific fourth join fields
        foreach ($this->join4_usr_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_text($field_esc, sql_db::LNK4_TBL, sql_db::ULK4_TBL, $this->name_sql_esc($field . '4'));
        }

        // add user specific numeric fourth join fields
        foreach ($this->join4_usr_num_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_usr_num($field_esc, sql_db::LNK4_TBL, sql_db::ULK4_TBL, $this->name_sql_esc($field . '4'));
        }

        // add user specific count join fields
        foreach ($this->join_usr_count_field_lst as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result .= $this->set_field_usr_count($result, $field_esc, sql_db::LNK_TBL, $this->name_sql_esc($field . '_count'));
        }

        foreach ($this->usr_only_field_lst as $field) {
            $field = $this->name_sql_esc($field);
            $result = $this->sep($result);
            if ($field == sandbox::FLD_CHANGE_USER) {
                $result .= ' ' . sql_db::USR_TBL . '.' . user::FLD_ID . ' AS ' . $field;
            } else {
                $result .= ' ' . sql_db::USR_TBL . '.' . $field;
            }
        }

        // add datetime dummy fields
        foreach ($this->field_lst_date_dummy as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_date_dummy($field_esc);
        }

        // add numeric dummy fields
        foreach ($this->field_lst_dummy as $field) {
            $field_esc = $this->name_sql_esc($field);
            $result = $this->sep($result);
            $result .= $this->set_field_num_dummy($field_esc);
        }

        return $result;
    }

    /**
     * create the "FROM" SQL statement based on the type
     * @param string $fields with a list of the query result field names for the group statement
     * @param int $par_offset in case of a sub query the number of parameter set until here of the main query
     * @return string the sql statement
     */
    private function from(string $fields, int $par_offset = 0): string
    {
        $result = '';
        $join_id_field = '';
        if ($this->grp_query) {
            $sc_sub = clone $this;
            $sc_sub->set_class($this->class);
            $sc_sub->sub_query = true;
            $sc_sub->set_usr($this->usr_id);
            $sc_sub->set_usr_num_fields($this->grp_field_lst);
            // move parameter to the sub query if possible
            $sc_sub->par_lst = $this->par_lst;
            $sc_sub->par_where = $this->par_where;

            $result = ' FROM ( ' . $sc_sub->sql(0, false) . ' ) AS ' . sql_db::GRP_TBL;
        }
        if ($this->join_type <> '') {
            if ($this->join_sub_query) {
                $join_table_name = $this->join_type;
                $join_from_id_field = $this->join_field;
                $join_id_field = $this->join_field;
            } else {
                $join_table_name = $this->name_sql_esc($this->get_table_name($this->join_type));
                $join_id_field = $this->name_sql_esc($this->get_id_field_name($this->join_type));
                if ($this->join_field == '') {
                    $join_from_id_field = $join_id_field;
                } else {
                    $join_from_id_field = $this->join_field;
                }
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
                    $result .= ' FROM ' . $this->name_sql_esc($this->table) . ' ' . sql_db::STD_TBL;
                    $result .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join_table_name . ' ' . sql_db::LNK_TBL;
                    $result .= ' ON ' . sql_db::LNK_TBL . '.' . $join_from_id_field . ' = ' . sql_db::STD_TBL . '.' . $join_id_field;
                    $this->add_par(sql_par_type::INT, $this->usr_id);
                    $result .= ' WHERE ' . sql_db::STD_TBL . '.' . $join_from_id_field . ' = ' . $this->par_name() . ' ';
                    $result .= ' GROUP BY ' . $fields . ' ';
                    $result .= ' ) AS ' . sql_db::STD_TBL;
                    $this->order = ' ORDER BY ' . $field_order_sql . ' DESC';
                } else {
                    $result = ' FROM ( SELECT ' . $this->name_sql_esc($this->table);
                    $result .= ' LEFT JOIN ' . $join_table_name . ' ' . sql_db::LNK_TBL;
                    $result .= ' ON ' . sql_db::STD_TBL . '.' . $join_from_id_field . ' = ' . sql_db::LNK_TBL . '.' . $join_id_field;
                    $this->add_par(sql_par_type::INT, $this->usr_id);
                    $result .= ' WHERE ' . sql_db::STD_TBL . '.' . $join_from_id_field . ' = ' . $this->par_name() . ' ';
                    $result .= ' GROUP BY ' . $field_sql . ' ';
                    $result .= ' ) AS c1';
                    $this->order = $field_order_sql . ' DESC';
                }
            } else {
                $this->join .= ' LEFT JOIN ' . $join_table_name . ' ' . sql_db::LNK_TBL;
                $this->join .= ' ON ' . sql_db::STD_TBL . '.' . $join_from_id_field . ' = ' . sql_db::LNK_TBL . '.' . $join_id_field;
                if ($this->usr_query and $this->join_usr_fields) {
                    $this->join .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join_table_name . ' ' . sql_db::ULK_TBL;
                    $this->join .= ' ON ' . sql_db::LNK_TBL . '.' . $join_id_field . ' = ' . sql_db::ULK_TBL . '.' . $join_id_field;
                    if (!$this->all_query) {
                        $this->join .= ' AND ' . sql_db::ULK_TBL . '.' . user::FLD_ID . ' = ';
                        if ($this->query_name == '') {
                            $this->join .= $this->usr_view_id;
                        } else {
                            // for MySQL the parameter needs to be repeated
                            if ($this->db_type == sql_db::MYSQL) {
                                $this->add_par(sql_par_type::INT, $this->usr_id, '', true);
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
            if ($this->usr_query and $this->join2_usr_fields) {
                $this->join .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join2_table_name . ' ' . sql_db::ULK2_TBL;
                $this->join .= ' ON ' . sql_db::LNK2_TBL . '.' . $join2_id_field . ' = ' . sql_db::ULK2_TBL . '.' . $join2_id_field;
                if (!$this->all_query) {
                    $this->join .= ' AND ' . sql_db::ULK2_TBL . '.' . user::FLD_ID . ' = ';
                    if ($this->query_name == '') {
                        $this->join .= $this->usr_view_id;
                    } else {
                        // for MySQL the parameter needs to be repeated
                        if ($this->db_type == sql_db::MYSQL) {
                            $this->add_par(sql_par_type::INT, $this->usr_id, '', true);
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
            if ($this->usr_query and $this->join3_usr_fields) {
                $this->join .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join3_table_name . ' ' . sql_db::ULK3_TBL;
                $this->join .= ' ON ' . sql_db::LNK3_TBL . '.' . $join3_id_field . ' = ' . sql_db::ULK3_TBL . '.' . $join3_id_field;
                if (!$this->all_query) {
                    $this->join .= ' AND ' . sql_db::ULK3_TBL . '.' . user::FLD_ID . ' = ';
                    if ($this->query_name == '') {
                        $this->join .= $this->usr_view_id;
                    } else {
                        // for MySQL the parameter needs to be repeated
                        if ($this->db_type == sql_db::MYSQL) {
                            $this->add_par(sql_par_type::INT, $this->usr_id, '', true);
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
            if ($this->usr_query and $this->join4_usr_fields) {
                $this->join .= ' LEFT JOIN ' . sql_db::TBL_USER_PREFIX . $join4_table_name . ' ' . sql_db::ULK4_TBL;
                $this->join .= ' ON ' . sql_db::LNK4_TBL . '.' . $join4_id_field . ' = ' . sql_db::ULK4_TBL . '.' . $join4_id_field;
                if (!$this->all_query) {
                    $this->join .= ' AND ' . sql_db::ULK4_TBL . '.' . user::FLD_ID . ' = ';
                    if ($this->query_name == '') {
                        $this->join .= $this->usr_view_id;
                    } else {
                        // for MySQL the parameter needs to be repeated
                        if ($this->db_type == sql_db::MYSQL) {
                            $this->add_par(sql_par_type::INT, $this->usr_id, '', true);
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
        if ($result == '') {
            $result = ' FROM ' . $this->name_sql_esc($this->table);
            if ($this->join <> '') {
                $result .= ' ' . sql_db::STD_TBL;
            }
        }
        return $result;
    }

    /**
     * set the where statement based on the parameter set until now
     * @param int $par_offset in case of a sub query the number of parameter set until here of the main query
     * @return string the sql where statement
     */
    private function where(int $par_offset = 0): string
    {
        if ($this->par_lst->count() > 0) {
            return $this->where_new($par_offset);
        } else {
            return $this->where_old($par_offset);
        }
    }

    /**
     * set the where statement based on the parameter set until now
     * in case of a sub query the where condition list is resetted, but the parameter list is continued
     *
     * @return string the sql where statement
     */
    private function where_new(int $par_offset = 0): string
    {
        $sql_where = '';

        // reset the number of open brackets
        $open_or_flf_lst = false;

        // loop over the where fields
        $pos = 0;
        foreach ($this->par_where->lst as $key => $where_fld) {
            $par = $this->par_lst->get($where_fld->pos);
            $typ = $where_fld->typ;

            // set the closing bracket around a or field list if needed
            if ($open_or_flf_lst) {
                if (!$typ->is_or()) {
                    $sql_where .= ' ) ';
                    $open_or_flf_lst = false;
                }
            }

            // start with the where statement
            $sql_where = $this->where_start($sql_where, $typ);

            // set the opening bracket around a or field list if needed
            $is_or = $typ->is_or();

            // check if the next is an or and if yes open the braket now
            if (!$typ->is_or()) {
                if (isset($this->par_where->lst[$key + 1])) {
                    $next_where_fld = $this->par_where->lst[$key + 1];
                    $is_or = $next_where_fld->typ->is_or();
                }
            }

            if ($is_or) {
                if (!$open_or_flf_lst) {
                    $sql_where .= ' ( ';
                    $open_or_flf_lst = true;
                }
            }

            // start with the where statement
            $sql_where .= $this->where_expression($where_fld, $par);

        }

        // close any open brackets
        if ($open_or_flf_lst) {
            $sql_where .= ' ) ';
        }

        return $sql_where;
    }

    /**
     * add WHERE to the sql statement (or OR or AND)
     * @param string $sql_where the sql where statement until now
     * @param sql_par_type $typ the parameter type
     * @return string the sql where statement with WHERE
     */
    private function where_start(string $sql_where, sql_par_type $typ): string
    {
        if (!$typ->is_function() and ($typ != sql_par_type::INT_SUB or $this->sub_query)) {
            if ($sql_where == '') {
                $sql_where = ' ' . sql::WHERE . ' ';
            } else {
                if ($typ->is_or()) {
                    $sql_where .= ' ' . sql::OR . ' ';
                } else {
                    $sql_where .= ' ' . sql::AND . ' ';
                }
            }
        }
        return $sql_where;
    }

    /**
     * @param sql_where $whp
     * @param sql_field $par
     * @return string
     */
    private function where_expression(sql_where $whp, sql_field $par): string
    {
        $sql_where = '';

        // shorten the var names
        $tbl = $whp->tbl;
        $fld = $whp->fld;
        $typ = $par->type;

        // add the standard table
        if ($tbl == null) {
            $tbl = '';
        }
        if ($tbl == '' and $this->is_multi_table()) {
            $tbl = sql_db::STD_TBL . sql::TBL_SEP;
        } else {
            // add the sql-table-field-separator to the table name
            if ($tbl != '' and !str_ends_with($tbl, sql::TBL_SEP)) {
                $tbl = $tbl . sql::TBL_SEP;
            }
        }

        // select by the user specific name
        if ($typ == sql_par_type::TEXT_USR) {
            $sql_where .= '(' . sql_db::USR_TBL . '.';
            $sql_where .= $fld . " = " . $par->name;
            $sql_where .= ' ' . sql::OR . ' (' . sql_db::STD_TBL . '.';
            /*
            if (SQL_DB_TYPE != sql_db::POSTGRES) {
                $this->add_par(sql_par_type::TEXT, $name);
            }
            */
            $sql_where .= $fld . " = " . $par->name;
            $sql_where .= ' ' . sql::AND . ' ' . sql_db::USR_TBL . '.';
            $sql_where .= $fld . " IS NULL))";
        } else {
            // add the other fields
            if ($typ->is_list()) {
                if ($this->db_type == sql_db::POSTGRES) {
                    $sql_where .= $tbl . $fld
                        . ' = ANY (' . $par->name . ')';
                } else {
                    $sql_where .= $tbl . $fld
                        . ' IN (' . $par->name . ')';
                }
            } elseif ($typ == sql_par_type::INT_SUB and $this->sub_query) {
                $sql_where .= $tbl . $fld
                    . ' = ' . $par->name;
            } elseif ($typ == sql_par_type::INT_SUB and !$this->sub_query) {
                //$par_offset--;
                $sql_where .= ''; // because added with the page statement
            } elseif ($typ == sql_par_type::INT_SUB_IN) {
                // $sql_where .= $tbl . $fld . ' IN (' . $this->par_value($i + 1) . ')';
                $sql_where .= $tbl . $fld . ' IN (' . $par->name . ')';
            } elseif ($typ == sql_par_type::MIN
                or $typ == sql_par_type::MAX
                or $typ == sql_par_type::COUNT
                or $typ == sql_par_type::LIMIT
                or $typ == sql_par_type::OFFSET) {
                // $par_offset--;
                $sql_where .= ''; // because added with the page statement
            } elseif ($typ == sql_par_type::LIKE_R
                or $typ == sql_par_type::LIKE
                or $typ == sql_par_type::LIKE_OR) {
                $sql_where .= $tbl . $fld . ' like ' . $par->name;
            } elseif ($typ == sql_par_type::CONST) {
                // $par_offset--;
                $sql_where .= $tbl . $fld . ' = ' . $par->value;
            } elseif ($typ == sql_par_type::CONST_NOT) {
                //$par_offset--;
                $sql_where .= $tbl . $fld . ' <> ' . $par->value;
            } elseif ($typ == sql_par_type::CONST_NOT_IN) {
                //$par_offset--;
                $sql_where .= ' ( ' . $tbl . $fld
                    . ' NOT IN (' . $par->value . ')'
                    . ' OR ' . $tbl . $fld . ' IS NULL )';
            } elseif ($typ == sql_par_type::IS_NULL) {
                //$par_offset--;
                $sql_where .= $tbl . $fld . ' IS NULL ';
            } elseif ($typ == sql_par_type::NOT_NULL) {
                //$par_offset--;
                // TODO review table prefix
                $sql_where .= sql_db::LNK_TBL . '.' . $fld . ' IS NOT NULL ';
            } elseif ($typ == sql_par_type::INT_NOT) {
                $sql_where .= $tbl . $fld . ' <> ' . $par->name;
            } elseif ($typ == sql_par_type::INT_NOT_OR_NULL) {
                $sql_where .= '( ' . $tbl . $fld . ' <> ' . $par->name
                    . ' OR ' . $tbl . $fld . ' IS NULL )';
            } elseif ($typ == sql_par_type::INT_HIGHER) {
                $sql_where .= $tbl . $fld . ' >= ' . $par->name;
            } elseif ($typ == sql_par_type::INT_LOWER) {
                $sql_where .= $tbl . $fld . ' =< ' . $par->name;
            } elseif ($typ == sql_par_type::INT_SAME
                or $typ == sql_par_type::INT_SAME_OR) {
                $sql_where .= $tbl . $fld . ' = ' . $par->name;
                //$par_offset--;
            } else {
                $sql_where .= $tbl . $fld . ' = ' . $par->name;
            }

            // include rows where code_id is null
            if ($typ == sql_par_type::TEXT OR $typ == sql_par_type::KEY_512) {
                if ($fld == sql::FLD_CODE_ID) {
                    if ($this->db_type == sql_db::POSTGRES) {
                        $sql_where .= ' AND ';
                        if ($this->usr_query or $this->join <> '') {
                            $sql_where .= sql_db::STD_TBL . '.';
                        }
                        $sql_where .= sql::FLD_CODE_ID . ' IS NOT NULL';
                    }
                }
            }
        }

        return $sql_where;
    }

    /**
     * set the where statement based on the parameter set until now
     * @param int $par_offset in case of a sub query the number of parameter set until here of the main query
     * @return string the sql where statement
     */
    private function where_old(int $par_offset = 0): string
    {

        $result = '';
        $open_or_flf_lst = false;
        // if nothing is defined assume to load the row by the main if
        if ($result == '') {
            if ($this->par_lst->count() > 0) {
                $i = 0; // the position in the SQL parameter array
                $used_fields = 0; // the position of the fields used in the where statement
                foreach ($this->par_lst->lst as $par_fld) {
                    $typ = $par_fld->type;
                    // set the closing bracket around a or field list if needed
                    if ($open_or_flf_lst) {
                        if (!$typ->is_or()) {
                            $result .= ' ) ';
                            $open_or_flf_lst = false;
                        }
                    }
                    // start with the where statement
                    $result = $this->where_start($result, $typ);

                    // set the opening bracket around a or field list if needed
                    if ($typ->is_or()) {
                        if (!$open_or_flf_lst) {
                            $result .= ' ( ';
                            $open_or_flf_lst = true;
                        }
                    }

                    $par_pos = $i + 1 + $par_offset;

                    // select by the user specific name
                    if ($typ == sql_par_type::TEXT_USR) {
                        $result .= '(' . sql_db::USR_TBL . '.';
                        $result .= $this->par_lst->name($i) . " = " . $this->par_name($par_pos);
                        $result .= ' ' . sql::OR . ' (' . sql_db::STD_TBL . '.';
                        /*
                        if (SQL_DB_TYPE != sql_db::POSTGRES) {
                            $this->add_par(sql_par_type::TEXT, $name);
                        }
                        */
                        $result .= $this->par_lst->name($i) . " = " . $this->par_name($par_pos);
                        $result .= ' ' . sql::AND . ' ' . sql_db::USR_TBL . '.';
                        $result .= $this->par_lst->name($i) . " IS NULL))";
                    } else {

                        // set the table prefix
                        $tbl_id = '';
                        if ($typ->is_function() and ($typ != sql_par_type::INT_SUB or $this->sub_query)) {
                            if ($this->usr_query
                                or $this->join <> ''
                                or $this->join_type <> ''
                                or $this->join2_type <> '') {
                                if (!str_contains($this->par_lst->name($i), '.')) {
                                    if ($this->par_use_link[$i]) {
                                        $tbl_id = sql_db::LNK_TBL . '.';
                                    } else {
                                        $tbl_id = sql_db::STD_TBL . '.';
                                    }
                                }
                            }
                        }

                        // add the other fields
                        if ($typ == sql_par_type::INT_LIST
                            or $typ == sql_par_type::INT_LIST_OR
                            or $typ == sql_par_type::TEXT_LIST) {
                            if ($this->db_type == sql_db::POSTGRES) {
                                $result .= $tbl_id . $this->par_lst->name($i)
                                    . ' = ANY (' . $this->par_name($par_pos) . ')';
                            } else {
                                $result .= $tbl_id . $this->par_lst->name($i)
                                    . ' IN (' . $this->par_name($par_pos) . ')';
                            }
                        } elseif ($typ == sql_par_type::INT_SUB and $this->sub_query) {
                            $result .= $tbl_id . $this->par_lst->name($i)
                                . ' = ' . $this->par_name($par_pos);
                        } elseif ($typ == sql_par_type::INT_SUB and !$this->sub_query) {
                            //$par_offset--;
                            $result .= ''; // because added with the page statement
                        } elseif ($typ == sql_par_type::INT_SUB_IN) {
                            $result .= $tbl_id . $this->par_lst->name($i)
                                . ' IN (' . $this->par_value($i + 1) . ')';
                        } elseif ($typ == sql_par_type::MIN
                            or $typ == sql_par_type::MAX
                            or $typ == sql_par_type::COUNT
                            or $typ == sql_par_type::LIMIT
                            or $typ == sql_par_type::OFFSET) {
                            $par_offset--;
                            $result .= ''; // because added with the page statement
                        } elseif ($typ == sql_par_type::LIKE_R
                            or $typ == sql_par_type::LIKE
                            or $typ == sql_par_type::LIKE_OR) {
                            $result .= $tbl_id . $this->par_lst->name($i) . ' like ';
                            if ($this->par_named[$i]) {
                                if ($this->par_name[$i] != '' and $this->db_type() != sql_db::MYSQL) {
                                    // if the same parameter is used more than once use the same placeholder again
                                    // e.g. if phrase_1 = $1 or phrase_2 = $1
                                    $result .= $this->par_name[$i];
                                } else {
                                    $result .= $this->par_name($par_pos);
                                }
                            } else {
                                $result .= $this->par_name($par_pos);
                            }
                        } elseif ($typ == sql_par_type::CONST) {
                            $par_offset--;
                            $result .= $tbl_id . $this->par_lst->name($i) . ' = ' . $this->par_value($i + 1);
                        } elseif ($typ == sql_par_type::CONST_NOT) {
                            $par_offset--;
                            $result .= $tbl_id . $this->par_lst->name($i) . ' <> ' . $this->par_value($i + 1);
                        } elseif ($typ == sql_par_type::CONST_NOT_IN) {
                            $par_offset--;
                            $result .= ' ( ' . $tbl_id . $this->par_lst->name($i)
                                . ' NOT IN (' . $this->par_value($i + 1) . ')'
                                . ' OR ' . $tbl_id . $this->par_lst->name($i) . ' IS NULL )';
                        } elseif ($typ == sql_par_type::IS_NULL) {
                            $par_offset--;
                            $result .= $tbl_id . $this->par_lst->name($i) . ' IS NULL ';
                        } elseif ($typ == sql_par_type::NOT_NULL) {
                            $par_offset--;
                            // TODO review table prefix
                            $result .= sql_db::LNK_TBL . '.' . $this->par_lst->name($i) . ' IS NOT NULL ';
                        } elseif ($typ == sql_par_type::INT_NOT) {
                            $result .= $tbl_id . $this->par_lst->name($i) . ' <> ' . $this->par_name($par_pos);
                        } elseif ($typ == sql_par_type::INT_NOT_OR_NULL) {
                            $result .= '( ' . $tbl_id . $this->par_lst->name($i) . ' <> ' . $this->par_name($par_pos)
                                . ' OR ' . $tbl_id . $this->par_lst->name($i) . ' IS NULL )';
                        } elseif ($typ == sql_par_type::INT_HIGHER) {
                            $result .= $tbl_id . $this->par_lst->name($i) . ' >= ' . $this->par_name($par_pos);
                        } elseif ($typ == sql_par_type::INT_LOWER) {
                            $result .= $tbl_id . $this->par_lst->name($i) . ' =< ' . $this->par_name($par_pos);
                        } elseif ($typ == sql_par_type::INT_SAME
                            or $typ == sql_par_type::INT_SAME_OR) {
                            $result .= $tbl_id . $this->par_lst->name($i) . ' = ';
                            if ($this->par_named[$i]) {
                                if ($this->par_name[$i] != '' and $this->db_type() != sql_db::MYSQL) {
                                    $result .= $this->par_name[$i];
                                } else {
                                    $result .= $this->par_name($par_pos);
                                }
                            } else {
                                $result .= $this->par_name($par_pos);
                            }
                            $par_offset--;
                        } else {
                            $result .= $tbl_id . $this->par_lst->name($i) . ' = ' . $this->par_name($par_pos);
                        }


                        // include rows where code_id is null
                        if ($typ == sql_par_type::TEXT OR $typ == sql_par_type::KEY_512) {
                            if ($this->par_lst->name($i) == sql::FLD_CODE_ID) {
                                if ($this->db_type == sql_db::POSTGRES) {
                                    $result .= ' AND ';
                                    if ($this->usr_query or $this->join <> '') {
                                        $result .= sql_db::STD_TBL . '.';
                                    }
                                    $result .= sql::FLD_CODE_ID . ' IS NOT NULL';
                                }
                            }
                        }
                    }

                    $i++;
                }
                // close any open brackets
                if ($open_or_flf_lst) {
                    $result .= ' ) ';
                }

            }
        }
        return $result;
    }

    /**
     * get the order SQL statement
     */
    function get_order(): string
    {
        return $this->order;
    }

    /**
     * @return bool true if the query is based on more than one table
     */
    private function is_multi_table(): bool
    {
        if ($this->usr_query
            or $this->join <> ''
            or $this->join_type <> ''
            or $this->join2_type <> '') {
            return true;
        } else {
            return false;
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
                $table_prefix .= sql_db::STD_TBL . '.';
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
     * set the limit and offset SQL statement for pagination
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     */
    function set_page(int $limit = 0, int $offset = 0): void
    {
        // set default values
        if ($offset <= 0) {
            $offset = 0;
        }
        if ($limit == 0) {
            $limit = sql_db::ROW_LIMIT;
        } else {
            if ($limit <= 0) {
                $limit = sql_db::ROW_LIMIT;
            }
        }
        $this->use_page = true;
        $this->add_par(sql_par_type::LIMIT, $limit, sql_par_type::LIMIT->value);
        $this->add_par(sql_par_type::OFFSET, $offset * $limit, sql_par_type::OFFSET->value);
    }

    /**
     * @return string the page statement that needs to be created after all other parameter have been requested
     */
    function get_page(): string
    {
        $result = '';
        if ($this->use_page) {
            // assuming that limit and offset are always the last two parameters
            $result .= ' LIMIT ' . $this->par_name($this->par_count() - 1);
            $result .= ' OFFSET ' . $this->par_name($this->par_count());
        }
        return $result;
    }

    /**
     * @return int the number of parameters used excluding constants
     */
    function par_count(): int
    {
        $result = 0;
        foreach ($this->par_lst->types() as $par_type) {
            if ($par_type != sql_par_type::CONST
                and $par_type != sql_par_type::CONST_NOT
                and $par_type != sql_par_type::CONST_NOT_IN
                and $par_type != sql_par_type::IS_NULL
                and $par_type != sql_par_type::NOT_NULL
                and $par_type != sql_par_type::MIN
                and $par_type != sql_par_type::MAX
                and $par_type != sql_par_type::COUNT) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * warp the prepare clause around a given sql statement
     * @param string $sql the sql statement that should be prepared
     * @return string the prepare sql statement
     */
    function prepare_sql(string $sql, string $query_name, array $par_types): string
    {
        if ($this->db_type == sql_db::POSTGRES) {
            $par_types = $this->par_types_to_postgres($par_types);
            if ($this->used_par_types() > 0) {
                $sql = sql::PREPARE . ' ' . $query_name . ' (' . implode(', ', $par_types) . ') AS ' . $sql;
            } else {
                $sql = sql::PREPARE . ' ' . $query_name . ' AS ' . $sql;
            }
            $sql .= ";";
        } elseif ($this->db_type == sql_db::MYSQL) {
            $sql = "PREPARE " . $query_name . " FROM '" . $sql;
            $sql .= "';";
        } else {
            log_err(sql::PREPARE . ' SQL not yet defined for SQL dialect ' . $this->db_type);
        }
        return $sql;
    }

    /**
     * create a prepared sql statement based to the already given sql creator parameters
     * @param string $sql_statement_type either SELECT, INSERT, UPDATE or DELETE
     * @return string with the SQL prepare statement for the current query
     */
    private function prepare_this_sql(
        string             $sql_statement_type = sql::SELECT,
        sql_type_list      $sc_par_lst = new sql_type_list([]),
        sql_par_field_list $fvt_lst = new sql_par_field_list()
    ): string
    {
        $sql = '';
        if (!$fvt_lst->is_empty()) {
            if ($sc_par_lst->create_function()) {
                $par_string = '(' . $fvt_lst->sql_par_names($this) . ')';
                if ($this->db_type == sql_db::POSTGRES) {
                    $sql = sql::CREATE . ' ' . sql::FUNCTION . ' ' . $this->query_name . ' '
                        . $par_string . ' ';
                    if ($sc_par_lst->no_id_return()) {
                        $sql .= sql::FUNCTION_NO_RETURN;
                    } else {
                        $sql .= sql::FUNCTION_RETURN_INT . ' ';
                    }
                } else {
                    $sql = sql::DROP_MYSQL . ' ' . $this->query_name . '; ';
                    $sql .= sql::FUNCTION_MYSQL . ' ' . $this->query_name . ' '
                        . $par_string;
                    if ($sc_par_lst->no_id_return()) {
                        $sql .= sql::FUNCTION_NO_RETURN_MYSQL;
                    } else {
                        $sql .= sql::FUNCTION_RETURN_INT_MYSQL . ' ';
                    }
                }
            } else {
                log_err('SQL statement creation not yet defined for SQL ' . $sc_par_lst->dsp_id());
            }

        } else {

            // TODO move this to fvt_lst based

            if ($this->sub_query or $sc_par_lst->is_sub_tbl()) {
                $sql = $sql_statement_type;
            } elseif ($this->par_lst->count() > 0
                or $this->join_sub_query
                or $this->join2_sub_query
                or $this->join3_sub_query
                or $this->join4_sub_query) {
                // used for sub queries
                if ($this->query_name == '') {
                    $sql = $sql_statement_type;
                } else {
                    if ($this->db_type == sql_db::POSTGRES) {
                        if ($this->used_par_types() > 0) {
                            $par_types = $this->par_types_to_postgres();
                            if ($sql_statement_type == sql::FUNCTION) {
                                $par_string = '(' . $this->par_named_types($par_types) . ')';
                                $sql = sql::CREATE . ' ' . sql::FUNCTION . ' ' . $this->query_name . ' '
                                    . $par_string . ' ';
                                if ($sc_par_lst->no_id_return()) {
                                    $sql .= sql::FUNCTION_NO_RETURN;
                                } else {
                                    $sql .= sql::FUNCTION_RETURN_INT;
                                }
                            } else {
                                $par_string = '(' . implode(', ', $par_types) . ')';
                                $sql = sql::PREPARE . ' ' . $this->query_name . ' '
                                    . $par_string . ' AS ' . $sql_statement_type;
                            }
                        } else {
                            $sql = sql::PREPARE . ' ' . $this->query_name . ' AS ' . $sql_statement_type;
                        }
                    } elseif ($this->db_type == sql_db::MYSQL) {
                        if ($this->used_par_types() > 0) {
                            $par_types = $this->par_types_to_postgres();
                            if ($sql_statement_type == sql::FUNCTION) {
                                $par_string = '(' . $this->par_named_types($par_types) . ')';
                                $sql = sql::DROP_MYSQL . ' ' . $this->query_name . '; ';
                                $sql .= sql::FUNCTION_MYSQL . ' ' . $this->query_name . ' '
                                    . $par_string . ' ';
                                if ($sc_par_lst->no_id_return()) {
                                    $sql .= sql::FUNCTION_NO_RETURN_MYSQL;
                                } else {
                                    $sql .= sql::FUNCTION_RETURN_INT_MYSQL;
                                }
                                $this->end = ' ';
                            } else {
                                $sql = sql::PREPARE . ' ' . $this->query_name . " FROM '" . $sql_statement_type;
                                $this->end = "';";
                            }
                        } else {
                            $sql = sql::PREPARE . ' ' . $this->query_name . " FROM '" . $sql_statement_type;
                            $this->end = "';";
                        }
                    } else {
                        log_err('Prepare SQL not yet defined for SQL dialect ' . $this->db_type);
                    }
                }
            } else {
                log_err('Query is not a sub query, but parameters types are missing for ' . $this->query_name);
            }
        }

        return $sql;
    }

    /**
     * @return string with the named parameter types for a function, statement or query
     */
    private function par_named_types(array $par_types): string
    {
        $result = '';
        if (count($par_types) != $this->par_lst->count()) {
            $lib = new library();
            log_err('the number of parameter names ' . $lib->dsp_array($this->par_lst->names())
                . ' does not match with the number of parameter types ' . $lib->dsp_array($this->par_lst->types())
                . ' for ' . $this->query_name);
        } else {
            foreach ($par_types as $i => $par_type) {
                if ($result != '') {
                    $result .= ', ';
                }
                $result .= $this->par_lst->name($i);
                $result .= ' ' . $par_type;
            }
        }
        return $result;
    }

    /**
     * generate a sql statement to create one database table
     *
     * @param array $fields with the field names, types and default value
     * @param string $type_name the name of the value type
     * @param string $tbl_comment describe the purpose of the table for the developer only
     * @param string $class the class name including the namespace
     * @param bool $usr_tbl true if the sql for the user overwrite table should be returned
     * @return string the sql statement to create a table
     */
    function table_create(
        array  $fields,
        string $type_name = '',
        string $tbl_comment = '',
        string $class = '',
        bool   $usr_tbl = false
    ): string
    {
        $sql = '';

        // escape the names depending on the db dialect
        $table_used = $this->name_sql_esc($this->table);

        // set the header comments
        $sql .= '-- ';
        $sql .= '-- table structure ';
        if ($tbl_comment != '') {
            if ($usr_tbl) {
                $sql .= 'to save user specific changes ' . $tbl_comment;
            } else {
                $sql .= $tbl_comment;
            }
        } else {
            if ($usr_tbl) {
                $sql .= 'for user specific changes of ' . $table_used;
            } else {
                $sql .= 'for ' . $table_used;
            }
        }
        $sql .= ' ';
        $sql .= '-- ';
        $sql .= ' ';

        // create the main sql
        $sql .= 'CREATE TABLE IF NOT EXISTS ' . $table_used . ' ';
        $sql .= '';

        // loop over the fields
        $sql .= '(';
        $sql_fields = '';
        foreach ($fields as $field) {
            if ($sql_fields != '') {
                $sql_fields .= ', ';
            }
            $name = $this->name_sql_esc($field[sql::FLD_POS_NAME]);
            $type = $field[sql::FLD_POS_TYPE];
            if ($this->db_type() == sql_db::POSTGRES) {
                $type_used = $type->pg_type();
            } elseif ($this->db_type() == sql_db::MYSQL) {
                $type_used = $type->mysql_type();
            } else {
                $type_used = 'field type for ' . $this->db_type() . ' missing';
            }
            $default = $field[sql::FLD_POS_DEFAULT_VALUE];
            $default_used = $default->pg_type();
            $comment = $field[sql::FLD_POS_COMMENT];
            if (($type->is_key() or $type->is_key_part()) and $type_name != '') {
                $comment = $this->comment_set_class($comment, '');
            } else {
                $comment = $this->comment_set_class($comment, $class);
            }
            if ($this->db_type() == sql_db::POSTGRES) {
                if ($type->is_key()) {
                    $default_used = sql_pg::FLD_KEY;
                }
            }
            $comment_used = '';
            if ($this->db_type() == sql_db::MYSQL) {
                if ($comment != '') {
                    $comment_used = " COMMENT '" . $comment;
                    if (($type->is_key() or $type->is_key_part()) and $type_name != '') {
                        $comment_used .= ' ' . $type_name;
                    }
                    $comment_used .= "'";
                }
            }
            $sql_fields .= '    ' . $name . ' ' . $type_used . ' ' . $default_used . $comment_used;
        }
        $sql .= $sql_fields . ')';
        if ($this->db_type() == sql_db::MYSQL) {
            $sql .= ' ENGINE = InnoDB DEFAULT CHARSET = utf8 ';
            $sql .= "COMMENT '" . $tbl_comment . "'";
        }
        $sql .= '; ';

        // add the table comment
        if ($this->db_type() == sql_db::POSTGRES) {
            $sql .= "COMMENT ON TABLE " . $table_used . " IS '" . $tbl_comment . "'; ";

            // loop over the comments
            foreach ($fields as $field) {
                $name = $field[sql::FLD_POS_NAME];
                $type = $field[sql::FLD_POS_TYPE];
                $comment = $field[sql::FLD_POS_COMMENT];
                if ($comment != '') {
                    if (($type->is_key() or $type->is_key_part()) and $type_name != '') {
                        $comment = $this->comment_set_class($comment, '');
                    } else {
                        $comment = $this->comment_set_class($comment, $class);
                    }
                    $sql .= "COMMENT ON COLUMN " . $table_used . "." . $name . " IS '" . $comment;
                    if (($type->is_key() or $type->is_key_part()) and $type_name != '') {
                        $sql .= ' ' . $type_name;
                    }
                    $sql .= "'; ";
                }
            }
        }

        // add auto increment if needed
        if ($this->db_type() == sql_db::MYSQL) {
            $sql .= $this->auto_increment($class, $fields);;
        }

        return $sql;
    }


    /**
     * replace the class placeholder in table of field comments with the class name
     *
     * @param string $comment_text the table of field comment
     * @param string $class the class including the namespace
     * @return string the comment string with the class name
     */
    private function comment_set_class(string $comment_text, string $class): string
    {
        $lib = new library();
        $name = $lib->class_to_name($class);
        return str_replace(sql::COMMENT_CLASS_NAME, $name, $comment_text);
    }

    /**
     * generate a sql statement to create the indices for one database table
     *
     * @param array $fields with the field names, types and default value
     * @param bool $null_in_key true if the primary key of the table with more than one field can have null fields
     * @return string the sql statement to create the indices for a table
     */
    function index_create(array $fields, bool $null_in_key = false): string
    {
        $sql = '';

        // set the header comments
        $sql .= '-- ';
        $sql .= '-- indexes for table ' . $this->table;
        $sql .= ' ';
        $sql .= '-- ';
        $sql .= ' ';

        // create the primary key sql
        $sql_table = '';
        $prime_keys = [];
        $index_fields = [];
        foreach ($fields as $field) {
            $type = $field[sql::FLD_POS_TYPE];
            $index = $field[sql::FLD_POS_INDEX];
            if ($type->is_key() or $type->is_key_part()) {
                $prime_keys[] = $field[sql::FLD_POS_NAME];
            }
            if ($index != '') {
                $index_fields[] = $field[sql::FLD_POS_NAME];
            }
        }
        if (count($prime_keys) > 0) {
            if ($this->db_type() == sql_db::POSTGRES) {
                if (count($prime_keys) > 1) {
                    if ($null_in_key) {
                        $sql .= 'CREATE UNIQUE INDEX ' . $this->table . '_pkey ON ';
                        $sql .= ' ' . $this->name_sql_esc($this->table) . ' (';
                    } else {
                        $sql .= 'ALTER TABLE ' . $this->name_sql_esc($this->table);
                        $sql .= ' ADD CONSTRAINT ' . $this->table . '_pkey PRIMARY KEY (';
                    }
                    $sql .= implode(', ', $prime_keys);
                    $sql .= '); ';
                }
            } elseif ($this->db_type() == sql_db::MYSQL) {
                $sql .= 'ALTER TABLE ' . $this->name_sql_esc($this->table);
                $sql .= ' ADD PRIMARY KEY (';
                $sql .= implode(', ', $prime_keys);
                if (count($index_fields) > 0) {
                    $sql .= '), ';
                } else {
                    $sql .= '); ';
                }
            }
        } else {
            if ($this->db_type() == sql_db::MYSQL) {
                $sql_table .= 'ALTER TABLE ' . $this->name_sql_esc($this->table);
            }
        }

        // create the unique index sql of combined fields
        $unique_fields = [];
        foreach ($fields as $field) {
            $type = $field[sql::FLD_POS_TYPE];
            if ($type->is_unique_part()) {
                $unique_fields[] = $field[sql::FLD_POS_NAME];
            }
        }
        if (count($unique_fields) > 0) {
            if ($this->db_type() == sql_db::POSTGRES) {
                $sql .= 'CREATE UNIQUE INDEX ' . $this->table . '_unique_idx ON ';
                $sql .= ' ' . $this->name_sql_esc($this->table) . ' (';
                $sql .= implode(', ', $unique_fields);
                $sql .= '); ';
            } elseif ($this->db_type() == sql_db::MYSQL) {
                $sql .= 'ADD UNIQUE KEY ' . $this->table . '_unique_idx (';
                $sql .= implode(', ', $unique_fields);
                if (count($index_fields) > 0) {
                    $sql .= '), ';
                } else {
                    $sql .= '); ';
                }
            }
        }

        // create the index create sql
        $sql_field = '';
        $field_lst = [];
        foreach ($fields as $field) {
            $name = $field[sql::FLD_POS_NAME];
            $index = $field[sql::FLD_POS_INDEX];
            if ($index != '') {
                if ($this->db_type() == sql_db::POSTGRES) {
                    $sql_field .= 'CREATE ' . $index . ' ' . $this->table . '_';
                    if (str_ends_with($name, '_id')) {
                        $sql_field .= $name . 'x';
                    } else {
                        $sql_field .= $name . '_idx';
                    }
                    $sql_field .= ' ON ' . $this->name_sql_esc($this->table) . ' (' . $name . '); ';
                } elseif ($this->db_type() == sql_db::MYSQL) {
                    $mysql_field = ' ADD KEY ' . $this->table . '_';
                    if (str_ends_with($name, '_id')) {
                        $mysql_field .= $name . 'x';
                    } else {
                        $mysql_field .= $name . '_idx';
                    }
                    $mysql_field .= ' (' . $name . ')';
                    $field_lst[] = $mysql_field;
                }
            }
        }
        if (count($field_lst) > 0) {
            $sql_field .= $sql_table . implode(', ', $field_lst) . '; ';
        }
        $sql .= $sql_field;
        return $sql;
    }

    private function auto_increment(string $class, array $fields): string
    {
        $sql = '';
        $id_fld = '';
        foreach ($fields as $field) {
            $type = $field[sql::FLD_POS_TYPE];
            if ($type->is_auto_increment()) {
                $id_fld = $field[sql::FLD_POS_NAME];
            }
        }
        if ($id_fld != '') {
            $sql .= '-- ' . "\n";
            $sql .= '-- AUTO_INCREMENT for table ' . $this->table . ' ' . "\n";
            $sql .= '-- ' . "\n";
            $sql .= 'ALTER ' . 'TABLE ' . $this->name_sql_esc($this->table) . ' ' . "\n";
            $sql .= '    MODIFY ' . $this->name_sql_esc($id_fld) . ' int(11) NOT NULL AUTO_INCREMENT; ' . "\n";
            $sql .= ' ' . "\n";
        }
        return $sql;

    }

    /**
     * generate a sql statement to create the foreign keys for one database table
     *
     * @param array $fields with the field names, types and default value
     * @return string the sql statement to create the indices for a table
     */
    function foreign_key_create(array $fields): string
    {
        $sql = '';
        $lib = new library();

        $sql_table = '';
        $sql_fields = '';
        $field_lst = [];
        $key_lst = [];

        // create the unique constraints
        foreach ($fields as $field) {
            $name = $field[sql::FLD_POS_NAME];
            $type = $field[sql::FLD_POS_TYPE];
            if ($type->is_unique()) {
                // set the header comments
                if ($sql == '') {
                    $sql .= '-- ';
                    $sql .= '-- constraints for table ' . $this->table . ' ';
                    $sql .= '-- ';
                    $sql_table .= 'ALTER TABLE ' . $this->name_sql_esc($this->table);
                }
                $sql_field = ' ADD CONSTRAINT ' . $this->table . '_' . $name . '_uk';
                $sql_field .= ' UNIQUE (' . $name . ')';
                $field_lst[] = $sql_field;
            }
        }

        // create the foreign key sql statements
        foreach ($fields as $field) {
            $name = $field[sql::FLD_POS_NAME];
            $name_foreign = $name;
            if (count($field) > sql::FLD_POS_NAME_LINK) {
                $name_link = $field[sql::FLD_POS_NAME_LINK];
                if ($name_link != '') {
                    $name_foreign = $name_link;
                }
            }
            $link = $field[sql::FLD_POS_FOREIGN_LINK];
            $link_used = $lib->class_to_name($link);
            if ($link_used != '') {
                // set the header comments
                if ($sql == '') {
                    $sql .= '-- ';
                    $sql .= '-- constraints for table ' . $this->table . ' ';
                    $sql .= '-- ';
                    $sql_table .= 'ALTER TABLE ' . $this->name_sql_esc($this->table);
                }
                $key = $this->table . '_' . $link_used . '_fk';
                $key_pos = 1;
                if (in_array($key, $key_lst)) {
                    $key_pos++;
                    $key = $this->table . '_' . $link_used . $key_pos . '_fk';
                    while (in_array($key, $key_lst)) {
                        $key_pos++;
                        $key = $this->table . '_' . $link_used . $key_pos . '_fk';
                    }
                }
                $key_lst[] = $key;
                $link_used = $this->get_table_name($link_used);
                if ($this->db_type() == sql_db::POSTGRES) {
                    $sql_field = ' ADD CONSTRAINT ' . $key;
                    $sql_field .= ' FOREIGN KEY (' . $name . ') REFERENCES ' . $link_used . ' (' . $name_foreign . ')';
                    $field_lst[] = $sql_field;
                } elseif ($this->db_type() == sql_db::MYSQL) {
                    $sql_field = ' ADD CONSTRAINT ' . $key;
                    $sql_field .= ' FOREIGN KEY (' . $name . ') REFERENCES ' . $link_used . ' (' . $name_foreign . ')';
                    $field_lst[] = $sql_field;
                }
            }
        }
        if (count($field_lst) > 0) {
            $sql_fields = $sql_table . implode(', ', $field_lst) . '; ';
        }
        $sql .= $sql_fields;
        return $sql;
    }

    /**
     * @return string a sql separator just to improve formatting
     */
    function sql_separator(): string
    {
        $sql = ' ';
        $sql .= '-- -------------------------------------------------------- ';
        $sql .= ' ';
        return $sql;
    }

    /**
     * @return string a sql separator just to improve formatting
     */
    function sql_view_header(string $view_name, string $view_comment = ''): string
    {
        $sql = '-- ';
        $sql .= '-- structure for view ' . $view_name . ' ';
        if ($view_comment != '') {
            $sql .= '(' . $view_comment . ') ';
        }
        $sql .= '-- ';
        return $sql;
    }

    /**
     * @return int the number of parameter types actually used e.g. excluding "is null"
     */
    private function used_par_types(): int
    {
        $result = 0;
        foreach ($this->par_types_to_postgres() as $par_type) {
            if ($par_type != '') {
                $result++;
            }
        }
        return $result;
    }

    /**
     * finish an sql statement
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return string with the SQL closing statement for the current query
     */
    private function end_sql(
        string        $sql,
        string        $sql_statement_type = sql::SELECT,
        sql_type_list $sc_par_lst = new sql_type_list([]),
        string        $new_id_fld = ''
    ): string
    {
        $lib = new library();
        $no_id_return = $sc_par_lst->no_id_return();
        if ($sql_statement_type == self::INSERT) {
            if ($this->db_type == sql_db::POSTGRES) {
                // return the database row id if the table uses auto id series
                if (!in_array($this::class, sql_db::DB_TABLE_WITHOUT_AUTO_ID)) {
                    // for the change log and user specific changes the id of the new change log entry is not needed
                    // because the id is combination of user_id amd the row_id and both are know already
                    if (!$no_id_return) {
                        $sql .= ' ' . sql::RETURNING . ' ';
                        if (is_array($this->id_field)) {
                            $sql .= implode(',', $this->id_field);
                        } else {
                            $sql .= $this->id_field;
                        }
                        if ($new_id_fld != '') {
                            $sql .= ' ' . sql::INTO . ' ' . $new_id_fld;
                        }
                        // TODO check if not the next line needs to be used
                        // $sql = $sql . " SELECT currval('" . $this->id_field . "_seq'); ";
                    }
                }
            } else {
                if ($sc_par_lst->use_named_par()) {
                    if (!$no_id_return) {
                        $sql .= ' ' . sql::RETURNING . ' ';
                        $sql .= $this->id_field;
                    }
                }
            }
        }
        if ($this->end == '' and !$this->list_query) {
            if ($sql_statement_type == self::FUNCTION) {
                $this->end = ' ';
            } else {
                $this->end = ';';
            }
        }
        if (!str_ends_with($sql, ";") and $this->query_name != '') {
            $sql .= $this->end;
        }
        return $sql;
    }

    /**
     * @return array with the parameter values in the same order as the given SQL parameter placeholders
     */
    function get_par(): array
    {
        $used_par_values = [];
        foreach ($this->par_lst->lst as $par_fld) {
            if ($par_fld->type != sql_par_type::CONST
                and $par_fld->type != sql_par_type::CONST_NOT
                and $par_fld->type != sql_par_type::CONST_NOT_IN
                and $par_fld->type != sql_par_type::IS_NULL
                and $par_fld->type != sql_par_type::NOT_NULL
                and $par_fld->type != sql_par_type::MIN
                and $par_fld->type != sql_par_type::MAX
                and $par_fld->type != sql_par_type::COUNT
                and $par_fld->type != sql_par_type::INT_SUB_IN) {
                if ($par_fld->name != '' and $this->db_type() != sql_db::MYSQL) {
                    $used_par_values[$par_fld->name] = $par_fld->value;
                } else {
                    $used_par_values[] = $par_fld->value;
                }
            }
        }
        return $used_par_values;
    }

    function get_par_types(): array
    {
        return $this->par_lst->types();
    }

    /**
     * remove the where condition at the given position $pos
     * used to move where parameters to a sub query
     * @param int $pos the array position which parameter should be removed
     * @return void
     */
    function move_where_to_sub(int $pos): void
    {
        /*
        unset($this->par_lst->name($pos));
        unset($this->par_lst->value($pos));
        unset($this->par_lst->type($pos));
        */
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
    function insert(array|string $fields, array|string $values, string $name, bool $log_err = true): int
    {
        global $db_con;

        $result = 0;
        $is_valid = false;
        $lib = new library();

        // escape the fields and values and build the SQL statement
        $this->set_table();
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
                if ($db_con->postgres_link == null) {
                    if ($log_err) {
                        log_err('Database connection lost', 'insert');
                    }
                } else {
                    // return the database row id if the table uses auto id series
                    if (!in_array($this::class, sql_db::DB_TABLE_WITHOUT_AUTO_ID)) {
                        $sql .= ' ' . sql::RETURNING . ' ' . $this->id_field . ';';
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
                    $sql_result = pg_query($db_con->postgres_link, $sql);
                    if ($sql_result) {
                        $sql_error = pg_result_error($sql_result);
                        if ($sql_error != '') {
                            if ($log_err) {
                                log_err('Execution of ' . $sql . ' failed due to ' . $sql_error);
                            }
                        } else {
                            if (!in_array($this->class, sql_db::DB_TABLE_WITHOUT_AUTO_ID)) {
                                if (is_resource($sql_result) or $sql_result::class == 'PgSql\Result') {
                                    $result = pg_fetch_array($sql_result)[0];
                                } else {
                                    // TODO get the correct db number
                                    $result = 0;
                                }
                            } else {
                                $result = 1;
                            }
                        }
                    } else {
                        $sql_error = pg_last_error($db_con->postgres_link);
                        if ($log_err) {
                            log_err('Execution of ' . $sql . ' failed completely due to ' . $sql_error);
                        }
                    }

                    //if ($result == false) {                        die(pg_last_error());                    }
                }
            } elseif ($this->db_type == sql_db::MYSQL) {
                $sql = $sql . ';';
                //$sql_result = $this->exe($sql, 'insert_' . $this->name_sql_esc($this->table), array(), sys_log_level::FATAL);

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
     * @return string the SQL statement to for the user specific data
     */
    function select_by_id_not_owner(int $id, ?int $owner_id = 0): string
    {
        $this->add_par(sql_par_type::INT, $id);
        if ($owner_id > 0) {
            $this->add_par(sql_par_type::INT_NOT, $owner_id);
        }
        $this->add_par(sql_par_type::CONST, '(excluded <> 1 OR excluded is NULL)');

        $fields = $this->fields(true);
        $from = $this->from($fields);
        $id_fld = $this->id_field;
        if ($owner_id > 0) {
            $where = $this->where();
        } else {
            $where = $this->where();
        }

        // create a prepare SQL statement if possible
        $sql = $this->sql();

        $sql .= $fields . $from . $where . $this->order . $this->page;

        return $this->end_sql($sql);
    }


    /*
     * public sql helpers
     */

    /**
     * escape or reformat the reserved SQL names
     * @param string $fld the name of the field that should be escaped
     * @return string the valid field name to the given sql dialect
     */
    function name_sql_esc(string $fld): string
    {
        switch ($this->db_type) {
            case sql_db::POSTGRES:
                if (in_array(strtoupper($fld), sql_db::POSTGRES_RESERVED_NAMES)
                    or in_array(strtoupper($fld), sql_db::POSTGRES_RESERVED_NAMES_EXTRA)) {
                    $fld = '"' . $fld . '"';
                }
                break;
            case sql_db::MYSQL:
                if (in_array(strtoupper($fld), sql_db::MYSQL_RESERVED_NAMES)
                    or in_array(strtoupper($fld), sql_db::MYSQL_RESERVED_NAMES_EXTRA)) {
                    $fld = '`' . $fld . '`';
                }
                break;
            default:
                log_err('Unexpected database type named "' . $this->db_type . '"');
                break;
        }
        return $fld;
    }

    /**
     * get the database field name of the primary index of a database table
     * @param string $class the sandbox object class name
     * @return string the database primary index field name base of the object name set before
     */
    function get_id_field_name(string $class): string
    {
        $lib = new library();
        $type = $lib->class_to_name($class);

        // exceptions for user overwrite tables
        // but not for the user type table, because this is not part of the sandbox tables
        if (str_starts_with($type, sql_db::TBL_USER_PREFIX)
            and $class != user_type::class
            and $class != user_official_type::class
            and $class != user_profile::class) {
            $type = $lib->str_right_of($type, sql_db::TBL_USER_PREFIX);
        }
        $result = $type . sql_db::FLD_EXT_ID;
        // standard exceptions for nice english
        if ($result == 'sys_log_statuss_id') {
            $result = 'sys_log_status_id';
        }
        if ($result == 'blocked_ip_id') {
            $result = 'ip_range_id';
        }
        if ($result == 'changes_norm_id') {
            $result = 'change_id';
        }
        if ($result == 'changes_big_id') {
            $result = 'change_id';
        }
        if ($result == 'change_values_prime_id') {
            $result = 'change_id';
        }
        if ($result == 'change_values_norm_id') {
            $result = 'change_id';
        }
        if ($result == 'change_values_big_id') {
            $result = 'change_id';
        }
        return $result;
    }


    /*
     * private sql helpers
     */

    /**
     * Sql Format: format a value for a SQL statement
     * TODO deprecate to prevent sql code injections
     *
     * $field_value is the value that should be formatted
     * $force_type can be set to force the formatting e.g. for the time word 2021 to use '2021'
     * outside this module it should only be used to format queries that are not yet using the abstract form for all databases (MySQL, MariaSQL, Casandra, Droid)
     */
    private function sf($field_value, $forced_format = '')
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
    private function postgres_format($field_value, $forced_format)
    {
        global $debug;

        $result = $field_value;

        // add the formatting for the sql statement
        if (trim($result) == "" or trim($result) == self::NULL_VALUE) {
            $result = self::NULL_VALUE;
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
    private function mysqli_format($field_value, $forced_format): string
    {
        $result = $field_value;

        // add the formatting for the sql statement
        if (trim($result) == "" or trim($result) == self::NULL_VALUE) {
            $result = self::NULL_VALUE;
        } else {
            if ($forced_format == sql_db::FLD_FORMAT_VAL) {
                if (str_starts_with($result, "'") and str_ends_with($result, "'")) {
                    $result = substr($result, 1, -1);
                }
            } elseif ($forced_format == sql_db::FLD_FORMAT_TEXT or !is_numeric($result)) {

                // escape the text value for MySQL
                if (!isset($this->mysql)) {
                    $result = $this->name_sql_esc($result);
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
     * set the table name and init some related parameters
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the table name extension e.g. to switch between standard and prime values
     * @return void
     */
    private function set_table(sql_type_list $sc_par_lst, string $ext = ''): void
    {
        global $debug;
        $this->table = '';

        $usr_tbl = $sc_par_lst->is_usr_tbl();
        if ($ext == '') {
            $ext = $sc_par_lst->ext_select();
        }
        if ($usr_tbl) {
            $this->table = sql_db::USER_PREFIX;
        }
        $this->table .= $this->get_table_name($this->class);
        if (!str_ends_with($this->table, $ext)) {
            $this->table .= $ext;
        }
        log_debug('to "' . $this->table . '"', $debug - 20);
    }

    /**
     * @return string the name of the table as defined by set_table, so including the prefix and extension
     */
    function get_table(): string
    {
        return $this->table;
    }


    /*
     * for all tables some standard fields such as "word_name" are used
     * the function below set the standard fields based on the "table/type"
    */

    /**
     * functions for the standard naming of tables
     *
     * @param string $class the database object name
     * @return string the database table name
     */
    function get_table_name(string $class): string
    {
        $lib = new library();
        $tbl_name = $lib->class_to_name($class);

        // set the standard table name based on the type
        $result = $tbl_name . "s";
        // exceptions from the standard table for 'nicer' names
        if ($result == 'configs') {
            $result = 'config';
        }
        if ($result == 'changes_norms') {
            $result = 'changes_norm';
        }
        if ($result == 'changes_bigs') {
            $result = 'changes_big';
        }
        if ($result == 'change_values_norms') {
            $result = 'change_values_norm';
        }
        if ($result == 'change_values_primes') {
            $result = 'change_values_prime';
        }
        if ($result == 'change_values_bigs') {
            $result = 'change_values_big';
        }
        if ($result == 'sys_log_statuss') {
            $result = 'sys_log_status';
        }
        if ($result == 'sys_logs') {
            $result = 'sys_log';
        }
        if ($result == 'sys_logs') {
            $result = 'sys_log';
        }
        if ($result == 'pod_statuss') {
            $result = 'pod_status';
        }
        if ($result == 'phrase_table_statuss') {
            $result = 'phrase_table_status';
        }
        if ($result == 'userss') {
            $result = 'users';
        }
        if ($result == 'value_time_seriess') {
            $result = 'values_time_series';
        }
        if ($result == 'user_value_time_seriess') {
            $result = 'user_values_time_series';
        }
        if ($result == 'value_ts_datas') {
            $result = 'value_ts_data';
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
     * the expect name field based on the given database object name
     * @return string the field name of the unique database name used as an index
     */
    private function name_field(): string
    {
        global $debug;

        $lib = new library();
        $type = $lib->class_to_name($this->class);

        // exceptions for user overwrite tables
        if (str_starts_with($type, sql_db::TBL_USER_PREFIX)) {
            $type = $lib->str_right_of($type, sql_db::TBL_USER_PREFIX);
        }
        $result = $type . '_name';
        // exceptions to be adjusted
        if ($result == 'link_type_name') {
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
            $pos = $this->par_lst->count() + 1;
        }

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
            $pos = $this->par_lst->count();
        }

        $par_values = array_values($this->par_lst->values());

        return $par_values[$pos - 1];
    }

    /**
     * convert the parameter type list to make valid for postgres
     * TODO move postgres const to a separate class sql_pg
     * @return array with the postgres parameter types
     */
    public function par_types_to_postgres(array $in_types = []): array
    {
        if ($in_types == []) {
            $in_types = $this->par_lst->types();
        }
        $result = array();
        foreach ($in_types as $type) {
            $pg_type = $this->par_type_to_postgres($type);
            // TODO review const excusion
            if ($pg_type != '') {
                $result[] = $this->par_type_to_postgres($type);
            }
        }
        return $result;
    }

    /**
     * convert one internal sql parameter type to a postgres db parameter type
     * @param sql_par_type|sql_field_type $type the internal type
     * @return string with the postgres parameter type
     */
    function par_type_to_postgres(sql_par_type|sql_field_type $type): string
    {
        $result = '';
        switch ($type) {
            case sql_par_type::INT_LIST:
            case sql_par_type::INT_LIST_OR:
                $result = self::PG_PAR_INT . self::PG_PAR_LIST;
                break;
            case sql_par_type::INT:
            case sql_par_type::INT_OR:
            case sql_par_type::INT_HIGHER:
            case sql_par_type::INT_LOWER:
            case sql_par_type::INT_NOT:
            case sql_par_type::INT_NOT_OR_NULL:
            case sql_par_type::INT_SUB:
            case sql_par_type::INT_SUB_IN:
            case sql_par_type::LIMIT:
            case sql_par_type::OFFSET:
                $result = self::PG_PAR_INT;
                break;
            case sql_par_type::INT_SMALL:
                $result = self::PG_PAR_INT_SMALL;
                break;
            case sql_par_type::TEXT_LIST:
                $result = self::PG_PAR_TEXT . self::PG_PAR_LIST;
                break;
            case sql_field_type::NAME:
            case sql_par_type::LIKE_R:
            case sql_par_type::LIKE:
            case sql_par_type::LIKE_OR:
            case sql_par_type::TEXT_OR:
            case sql_par_type::TEXT_USR:
            case sql_par_type::KEY_512:
                $result = self::PG_PAR_TEXT;
                break;
            case sql_par_type::CONST:
            case sql_par_type::CONST_NOT:
            case sql_par_type::CONST_NOT_IN:
            case sql_par_type::IS_NULL:
            case sql_par_type::NOT_NULL:
            case sql_par_type::MIN:
            case sql_par_type::MAX:
            case sql_par_type::COUNT:
                break;
            case sql_field_type::NUMERIC_FLOAT:
                $result = self::PG_PAR_FLOAT;
                break;
            default:
                $result = $type->value;
        }
        return $result;
    }

    /**
     * create a SQL select statement for the connected database
     * to detect if someone else has used the object
     * if the value can be stored in different tables
     *
     * @param int|string $id the unique database id if the object to check
     * @param int|null $owner_id the user id of the owner of the object
     * @param string|array $id_field the field name or field list of the prime database key if not standard
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 in the previous set dialect
     */
    function load_sql_not_changed_multi(
        int|string   $id,
        ?int         $owner_id = 0,
        string|array $id_field = '',
        string       $ext = '',
        sql_type     $tbl_typ = sql_type::MOST
    ): sql_par
    {
        $sc_par_lst = new sql_type_list([]);
        $sc_par_lst->add($tbl_typ);
        $qp = new sql_par($this->class, $sc_par_lst);
        $qp->name .= 'not_changed';
        if ($owner_id > 0) {
            $qp->name .= '_not_owned';
        }
        $this->set_name($qp->name);
        $this->set_table($sc_par_lst);
        $this->set_id_field($id_field);
        $this->set_fields(array(user::FLD_ID));
        $pos = $this->par_count();
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
                                $sql_mid_where .= ' ' . sql::WHERE . ' ';
                            } else {
                                $sql_mid_where .= ' ' . sql::AND . ' ';
                            }
                            $sql_mid_where .= $this->id_field[$pos] . " = " . $this->par_name($pos + 1);
                            $pos++;
                        }
                    }
                } else {
                    log_err('the id fields are expected to be an array');
                }
            } elseif ($tbl_typ == sql_type::BIG) {
                $sql_mid_where .= ' ' . sql::WHERE . ' ';
                $this->add_par(sql_par_type::TEXT, $id);
            } else {
                $sql_mid_where .= ' ' . sql::WHERE . ' ';
                $this->add_par(sql_par_type::INT, $id);
            }
            $sql_mid = " " . user::FLD_ID;
            $sql_mid .= " FROM " . $this->name_sql_esc(sql_db::TBL_USER_PREFIX . $this->table);
            if (!is_array($this->id_field)) {
                $pos++;
                $sql_mid_where .= $this->id_field . " = " . $this->par_name($pos);
            }
            $sql_mid .= $sql_mid_where . " AND (excluded <> 1 OR excluded is NULL)";
            if ($owner_id > 0) {
                $pos++;
                $this->add_par(sql_par_type::INT, $owner_id);
                $sql_mid .= " AND " . user::FLD_ID . " <> " . $this->par_name($pos);
            }
            $sql_mid = sql::SELECT . ' ' . $sql_mid;
            $qp->sql = $this->prepare_sql($sql_mid, $qp->name, $this->get_par_types());
        }
        $qp->par = $this->get_par();

        return $qp;
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
            // TODO use where function
            $pos = 1;
            $this->add_par(sql_par_type::INT, $id);
            $sql_mid = " " . user::FLD_ID .
                " FROM " . $this->name_sql_esc(sql_db::TBL_USER_PREFIX . $this->table) .
                " WHERE " . $this->id_field . " = " . $this->par_name($pos) . "
                 AND (excluded <> 1 OR excluded is NULL)";
            $pos++;
            if ($owner_id > 0) {
                $this->add_par(sql_par_type::INT, $owner_id);
                $sql_mid .= " AND " . user::FLD_ID . " <> " . $this->par_name($pos);
                $pos++;
            }
            $sql_mid = sql::SELECT . ' ' . $sql_mid;
            $qp->sql = $this->prepare_sql($sql_mid, $qp->name, $this->get_par_types());
            $qp->sql = $this->end_sql($qp->sql);
        }
        $qp->par = $this->get_par();

        return $qp;
    }


    /**
     * create a sql statment to delete all rows that have one of the given ids
     *
     * @param string $class the class name e.g. element not element_list
     * @param string $id_field the name of the id fields of the class
     * @param array $id_lst the list of id that should be deleted
     * @return sql_par the sql statement to delete the row selected by the id list
     */
    function del_sql_list_without_log(string $class, string $id_field, array $id_lst): sql_par
    {
        $lib = new library();

        $qp = new sql_par($class, new sql_type_list([sql_type::DELETE]));
        $this->set_class($class, new sql_type_list([]));
        $this->add_where($id_field, $id_lst, sql_par_type::INT_LIST);
        $sql = sql::DELETE . ' ' . $this->name_sql_esc($this->table) . ' ';
        $sql .= sql::WHERE . ' ' . $id_field . ' ';
        $sql .= sql::IN . ' (' . $this->par_name(1) . ')';
        $qp->name .= '_by_ids';
        $qp->sql = $this->prepare_sql($sql, $qp->name, [sql_par_type::INT_LIST]);
        $qp->par = [implode(',', $id_lst)];
        return $qp;
    }



    /*
     * user sandbox fields
     */

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
     * @return string the SQL statement for a field taken from the user sandbox table or from the table with the common values
     */
    private function set_field_usr_text(
        string $field, string $stb_tbl = sql_db::STD_TBL, string $usr_tbl = sql_db::USR_TBL, string $as = ''): string
    {
        return $this->sql_usr_field($field, sql_db::FLD_FORMAT_TEXT, $stb_tbl, $usr_tbl, $as);
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and number fields
     * @param string $field the field name of the user specific field
     * @param string $stb_tbl the table prefix for the table with the default values for all users
     * @param string $usr_tbl the table prefix for the table with the user specific values
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     * @return string the SQL statement for a field taken from the user sandbox table or from the table with the common values
     */
    private function set_field_usr_num(
        string $field, string $stb_tbl = sql_db::STD_TBL, string $usr_tbl = sql_db::USR_TBL, string $as = ''): string
    {
        return $this->sql_usr_field($field, sql_db::FLD_FORMAT_VAL, $stb_tbl, $usr_tbl, $as);
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and number fields
     * @param string $field the field name of the user specific field
     * @return string the SQL statement for a field taken from the user sandbox table or from the table with the common values
     */
    private function set_field_num_dummy(string $field): string
    {
        return " 0 AS " . $field;
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and datetime fields
     * @param string $field the field name of the user specific field
     * @return string the SQL statement for a field taken from the user sandbox table or from the table with the common values
     */
    private function set_field_date_dummy(string $field): string
    {
        return " now() AS " . $field;
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and number fields
     * @param string $fields with a list of the query result field names for the group statement
     * @param string $field the field name of the user specific field
     * @param string $stb_tbl the table prefix for the table with the default values for all users
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     * @return string the SQL statement for a field taken from the user sandbox table or from the table with the common values
     */
    private function set_field_usr_count(
        string $fields, string $field, string $stb_tbl = sql_db::LNK_TBL, string $as = ''): string
    {
        return ' FROM ( SELECT ' . $fields . ', count(' . $stb_tbl . '.' . $field . ') AS ' . $as;
    }

    /**
     * internal interface function for sql_usr_field using the class db type settings and boolean / tinyint fields
     * @param string $field the field name of the user specific field
     * @param string $as to overwrite the field name than contains the user specific value or the default value
     * @return string the SQL statement for a field taken from the user sandbox table or from the table with the common values
     */
    private function set_field_usr_bool(
        string $field, string $as = ''): string
    {
        return $this->sql_usr_field(
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


    /*
     * internal helper
     */

    /**
     * @param string $txt sql fields that should be seperated with a comma
     * @return string the original text with the sql separator if the text is not empty
     */
    private function sep(string $txt): string
    {
        if ($txt != '') {
            return $txt . ', ';
        } else {
            return '';
        }
    }

    /**
     * @param string $class where the namespace should be removed to get the db type name
     * @return string the db type name without the class namespace
     */
    function class_to_name(string $class): string
    {
        $lib = new library();
        return $lib->class_to_name($class);
    }

    /**
     * @param array $tbl_types list of sql table types that specifies the current case
     * @return bool true if the list of types specifies that the value has no user overwrites
     */
    function is_user(array $tbl_types): bool
    {
        if (in_array(sql_type::USER, $tbl_types)) {
            return true;
        } else {
            return false;
        }
    }

    function is_MySQL(): bool
    {
        if ($this->db_type == sql_db::MYSQL) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * function that can be overwritten by the child object
     * e.g. if the object name does not match the generated id field name
     * e.g. to group_id for values and results
     * @return string|array the field name(s) of the prime database index of the object
     */
    function id_field(): string|array
    {
        $lib = new library();
        return $lib->class_to_name($this::class) . sql_db::FLD_EXT_ID;
    }

    /**
     * @return string with the name of the id field
     */
    function id_field_name(): string
    {
        return $this->id_field;
    }



    /*
     * field, value and sql field type list
     */

    /**
     * @param array $fld_val_typ_lst an array with an array of the field name, value and the sql field type
     * @return array with the sql field names of the given list
     */
    function get_fields(array $fld_val_typ_lst): array
    {
        $fields = [];
        foreach ($fld_val_typ_lst as $fld_val) {
            $fields[] = $fld_val[0];
        }
        return $fields;
    }

    /**
     * @param array $fld_val_typ_lst an array with an array of the field name, value and the sql field type
     * @return array with the sql field values of the given list
     */
    function get_values(array $fld_val_typ_lst): array
    {
        $values = [];
        foreach ($fld_val_typ_lst as $fld_val) {
            $values[] = $fld_val[1];
        }
        return $values;
    }

    /**
     * @param array $fld_val_typ_lst an array with an array of the field name, value and the sql field type
     * @return array with the sql field types of the given list
     */
    function get_types(array $fld_val_typ_lst): array
    {
        $types = [];
        foreach ($fld_val_typ_lst as $fld_val) {
            $types[] = $fld_val[2];
        }
        return $types;
    }

}
