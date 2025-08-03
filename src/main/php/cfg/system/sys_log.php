<?php

/*

    model/system/sys_log.php - object to handle a system errors
    ----------------------

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\system;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_type_list;
use cfg\helper\db_object_seq_id;
use cfg\helper\type_list;
use cfg\helper\type_object;
use cfg\log\change;
use cfg\sandbox\sandbox;
use cfg\user\user;
use cfg\user\user_db;
use cfg\user\user_message;
use shared\enum\change_actions;
use shared\json_fields;
use shared\library;
use DateTime;
use DateTimeInterface;
use shared\types\api_type_list;

class sys_log extends db_object_seq_id
{

    /*
     * database link
     */

    // database and export JSON object field names
    // and comments used for the database creation
    // *_SQL_TYP is the sql data type used for the field
    const TBL_COMMENT = 'for system error tracking and to measure execution times';
    const FLD_ID = 'sys_log_id';
    const FLD_TIME_COM = 'timestamp of the creation';
    const FLD_TIME = 'sys_log_time';
    const FLD_TYPE_COM = 'the level e.g. debug, info, warning, error or fatal';
    const FLD_TYPE = 'sys_log_type_id';
    const FLD_FUNCTION_COM = 'the function or function group for the entry e.g. db_write to measure the db write times';
    const FLD_FUNCTION = 'sys_log_function_id';
    const FLD_TEXT_COM = 'the short text of the log entry to identify the error and to reduce the number of double entries';
    const FLD_TEXT = 'sys_log_text';
    const FLD_DESCRIPTION_COM = 'the long description with all details of the log entry to solve ti issue';
    const FLD_DESCRIPTION = 'sys_log_description';
    const FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;
    const FLD_TRACE_COM = 'the generated code trace to local the path to the error cause';
    const FLD_TRACE = 'sys_log_trace';
    const FLD_USER_COM = 'the id of the user who has caused the log entry';
    const FLD_SOLVER_COM = 'user id of the user that is trying to solve the problem';
    const FLD_SOLVER = 'solver_id';

    // join database and export JSON object field names
    const FLD_TIME_JSON = 'time';
    const FLD_TIMESTAMP_JSON = 'timestamp';
    const FLD_SOLVER_NAME = 'solver_name';

    // all database field names excluding the id
    // the extra user field is needed because it is common to check the log entries of others users e.g. for admin users
    const FLD_NAMES = array(
        user_db::FLD_ID,
        self::FLD_SOLVER,
        self::FLD_TIME,
        self::FLD_TYPE,
        self::FLD_FUNCTION,
        self::FLD_TEXT,
        self::FLD_DESCRIPTION,
        self::FLD_TRACE,
        sys_log_status::FLD_ID
    );

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [self::FLD_TIME, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_COM],
        [self::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TYPE_COM],
        [self::FLD_FUNCTION, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, sys_log_function::class, self::FLD_FUNCTION_COM],
        [self::FLD_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_TEXT_COM],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TRACE, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_TRACE_COM],
        [user_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [self::FLD_SOLVER, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, self::FLD_SOLVER_COM, user_db::FLD_ID],
        [sys_log_status::FLD_ID, sql_field_type::INT, sql_field_default::ONE, sql::INDEX, sys_log_status::class, ''],
    );


    /*
     * object vars
     */

    // object vars for the database fields
    private ?user $usr = null;           // the user who wants to see the error
    public ?int $usr_id = null;         // the user id who was logged in when the error happened
    public string $usr_name = '';       // the username who was logged in when the error happened
    public ?int $solver_id = null;      // the admin id who has solved the problem
    // TODO deprecate
    public ?string $solver_name = '';    // the admin id who has solved the problem
    public ?DateTime $log_time = null;  // timestamp when the issue appeared
    public ?int $type_id = null;        // type of the error
    public ?int $function_id = null;    // the program function where the issue happened
    public ?string $log_text = null;    // the description of the problem
    public ?string $log_description = null;    // the long description of the problem
    public string $log_trace = '';      // the system trace
    public ?int $status_id = null;      // the status of the error

    public ?string $function_name = '';  //


    /*
     * construct and map
     */

    /**
     * map the database fields to one system log entry to this log object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if a system log row is found
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $lib = new library();
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->usr_id = $db_row[user_db::FLD_ID];
            $this->usr_name = $db_row[sandbox::FLD_USER_NAME];
            $this->solver_id = $db_row[self::FLD_SOLVER];
            $this->solver_name = $db_row[self::FLD_SOLVER_NAME];
            $this->log_time = $lib->get_datetime($db_row[self::FLD_TIME]);
            $this->type_id = $db_row[self::FLD_TYPE];
            $this->function_id = $db_row[self::FLD_FUNCTION];
            $this->function_name = $db_row[type_list::FLD_NAME];
            $this->log_text = $db_row[self::FLD_TEXT];
            $this->log_description = $db_row[self::FLD_DESCRIPTION];
            $this->log_trace = $db_row[self::FLD_TRACE];
            $this->status_id = $db_row[sys_log_status::FLD_ID];
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the user of the error log
     *
     * @param user|null $usr the person who wants to see the error log
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user|null the person who wants to see the error log
     */
    function user(): ?user
    {
        return $this->usr;
    }


    /*
     * preloaded
     */

    /**
     * get the name of the system log entry status
     * @return string the name of the status
     */
    function status_name(): string
    {
        global $sys_log_sta_cac;
        return $sys_log_sta_cac->name($this->status_id);
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the tables of a system log table
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql_creator $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_table_create($sc);
        return $sql;
    }

    /**
     * the sql statement to create the database indices of a system log table
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql_creator $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_index_create($sc);
        return $sql;
    }

    /**
     * the sql statements to create all foreign keys of a system log table
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the foreign keys
     */
    function sql_foreign_key(sql_creator $sc): string
    {
        return $this->sql_foreign_key_create($sc, new sql_type_list());
    }


    /*
     * load
     */

    /**
     * create the SQL statement to load one system log entry
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the database depending on sql statement to load a system error from the log table
     *                 and the unique name for the query
     */
    function load_sql(sql_creator $sc, string $query_name = sql_db::FLD_ID, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name);
        $sc->set_class(sys_log::class);

        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_join_fields(array(sys_log_function::FLD_NAME), sys_log_function::class);
        $sc->set_join_fields(array(type_object::FLD_NAME), sys_log_status::class);
        $sc->set_join_fields(array(sandbox::FLD_USER_NAME), user::class);
        $sc->set_join_fields(array(sandbox::FLD_USER_NAME . ' AS ' . self::FLD_SOLVER_NAME), user::class, self::FLD_SOLVER);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a system log entry by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {

        $qp = $this->load_sql($sc, sql_db::FLD_ID, $class);
        $sc->add_where($this->id_field(), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a system error from the database e.g. to be able to display more details
     * @param int $id the id of the system log entry that should be loaded
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        log_debug();

        global $db_con;

        // at the moment it is only possible to select the error by the id
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        return $this->row_mapper($db_con->get1($qp));
    }

    /**
     * set the main log entry parameters for updating one error field
     * @return change the log object with the update presets
     */
    private function log_upd(): change
    {
        log_debug();
        $lib = new library();
        $tbl_name = $lib->class_to_name(sys_log::class);
        $log = new change($this->user());
        $log->set_action(change_actions::UPDATE);
        $log->set_table($tbl_name);

        return $log;
    }

    /**
     * @return string sys_log_id instead of sys_log_id
     */
    function id_field(): string
    {
        return self::FLD_ID;
    }


    /*
     * api
     */

    /**
     * create the array for the api message
     * which is on this level the same as the export json array
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);

        $vars[json_fields::ID] = $this->id();
        $vars[json_fields::TIME] = $this->log_time->format(DateTimeInterface::ATOM);
        $vars[json_fields::USER_NAME] = $this->usr_name;
        $vars[json_fields::TEXT] = $this->log_text;
        $vars[json_fields::DESCRIPTION] = $this->log_description;
        $vars[json_fields::TRACE] = $this->log_trace;
        $vars[json_fields::PRG_PART] = $this->function_name;
        //$vars[json_fields::ID] = $this->solver_name;
        $vars[json_fields::OWNER] = '';
        $vars[json_fields::STATUS] = $this->status_id;

        return $vars;
    }

    /**
     * actually update an error field in the main database record or the user sandbox
     * @param sql_db $db_con the active database connection
     * @param change $log the log object with the update presets
     * @return bool true if the field has been updated
     */
    private function save_field_do(sql_db $db_con, change $log): bool
    {
        log_debug();
        $result = true;
        if ($log->add()) {
            $db_con->set_class(sys_log::class);
            $result = $db_con->update_old($this->id(), $log->field(), $log->new_id);
        }
        return $result;
    }

    /**
     * set the update parameters for the error status
     * @param sql_db $db_con the active database connection
     * @param sys_log $db_rec the system log entry as saved in the database before the change
     * @return bool true if the status field has been updated
     */
    private function save_field_status(sql_db $db_con, sys_log $db_rec): bool
    {
        log_debug();
        global $sys_log_sta_cac;

        $result = false;
        if ($db_rec->status_id <> $this->status_id) {
            $log = $this->log_upd();
            $log->old_value = $sys_log_sta_cac->name($db_rec->status_id);
            $log->old_id = $db_rec->status_id;
            $log->new_value = $this->status_name();
            $log->new_id = $this->status_id;
            $log->row_id = $this->id();
            $log->set_field(sys_log_status::FLD_ID);
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return user_message either an empty string if saving has been successful or a message to the user with the reason, why it has failed
     */
    function save(?bool $use_func = null): user_message
    {
        log_debug();

        global $db_con;
        $usr_msg = new user_message();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->user()->id());
        $db_con->set_class(sys_log::class);

        if ($this->id() > 0) {
            $db_rec = new sys_log;
            $db_rec->set_user($this->user());
            if ($db_rec->load_by_id($this->id())) {
                log_debug("database entry loaded");
            }

            if (!$this->save_field_status($db_con, $db_rec)) {
                $usr_msg->add_message_text('saving the error log failed');
            }
        }

        if (!$usr_msg->is_ok()) {
            log_err($usr_msg->get_last_message());
        }

        return $usr_msg;
    }


    /*
     * debug
     */

    /**
     * @return string with the unique database id mainly for child dsp_id() functions
     */
    function dsp_id(): string
    {

        return 'system log id ' . $this->id()
            . ' at ' . $this->log_time->format(DateTimeInterface::ATOM)
            . ' row ' . $this->log_text;
    }

}