<?php

/*

    model/system/system_log.php - object to handle a system errors
    ---------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\log;

include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_function_list.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_value.php';
include_once MODEL_LOG_PATH . 'change_prime_value.php';
include_once MODEL_LOG_PATH . 'change_norm_value.php';
include_once MODEL_LOG_PATH . 'change_big_value.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_LOG_PATH . 'change_action_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once API_SANDBOX_PATH . 'sandbox_value.php';
include_once API_LOG_PATH . 'system_log.php';

use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql;
use cfg\db_object_seq_id;
use cfg\library;
use cfg\sandbox;
use cfg\db\sql_db;
use cfg\sys_log_function;
use cfg\sys_log_status;
use cfg\type_list;
use cfg\type_object;
use cfg\user;
use api\log\system_log as system_log_api;
use DateTime;
use DateTimeInterface;

class system_log extends db_object_seq_id
{

    /*
     * database link
     */

    // database and export JSON object field names
    // and comments used for the database creation
    const TBL_COMMENT = 'for system error tracking and to measure execution times';
    const FLD_ID = 'sys_log_id';
    const FLD_TIME_COM = 'timestamp of the creation';
    const FLD_TIME = 'sys_log_time';
    const FLD_TYPE_COM = 'the level e.g. debug, info, warning, error or fatal';
    const FLD_TYPE = 'sys_log_type_id';
    const FLD_FUNCTION_COM = 'the function or function group for the entry e.g. db_write to measure the db write times';
    const FLD_FUNCTION = 'sys_log_function_id';
    const FLD_TEXT_COM = 'the short text of the log entry to indentify the error and to reduce the number of double entries';
    const FLD_TEXT = 'sys_log_text';
    const FLD_DESCRIPTION_COM = 'the lond description with all details of the log entry to solve ti issue';
    const FLD_DESCRIPTION = 'sys_log_description';
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
        user::FLD_ID,
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
        [self::FLD_TYPE, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TYPE_COM],
        [self::FLD_FUNCTION, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, sys_log_function::class, self::FLD_FUNCTION_COM],
        [self::FLD_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_TEXT_COM],
        [self::FLD_DESCRIPTION, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TRACE, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_TRACE_COM],
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [self::FLD_SOLVER, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, self::FLD_SOLVER_COM, user::FLD_ID],
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
    public ?string $solver_name = '';    // the admin id who has solved the problem
    public ?DateTime $log_time = null;  // timestamp when the issue appeared
    public ?int $type_id = null;        // type of the error
    public ?int $function_id = null;    // the program function where the issue happened
    public ?string $log_text = null;    // the description of the problem
    public ?string $log_description = null;    // the long description of the problem
    public string $log_trace = '';      // the system trace
    public ?int $status_id = null;      // the status of the error

    public ?string $function_name = '';  //
    public string $status_name = '';    //


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
            $this->usr_id = $db_row[user::FLD_ID];
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
            $this->status_name = $db_row[type_object::FLD_NAME];
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
     * cast
     */

    /**
     * @return system_log_api a filled frontend api object
     */
    function get_api_obj(): system_log_api
    {
        $dsp_obj = new system_log_api();
        $dsp_obj->id = $this->id();
        $dsp_obj->time = $this->log_time->format('Y-m-d H:i:s');
        $dsp_obj->user = $this->usr_name;
        $dsp_obj->text = $this->log_text;
        $dsp_obj->description = $this->log_description;
        $dsp_obj->trace = $this->log_trace;
        $dsp_obj->prg_part = $this->function_name;
        //$dsp_obj->owner = $this->solver_name;
        $dsp_obj->status = $this->status_name;
        return $dsp_obj;
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the tables of a system log table
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_table_create($sc, false, [], '', false);
        return $sql;
    }

    /**
     * the sql statement to create the database indices of a system log table
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_index_create($sc, false, [],false);
        return $sql;
    }

    /**
     * the sql statements to create all foreign keysof a system log table
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the foreign keys
     */
    function sql_foreign_key(sql $sc): string
    {
        return $this->sql_foreign_key_create($sc, false, [],false);
    }


    /*
     * load
     */

    /**
     * create the SQL statement to load one system log entry
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the database depending on sql statement to load a system error from the log table
     *                 and the unique name for the query
     */
    function load_sql(sql $sc, string $query_name = sql_db::FLD_ID, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);
        $sc->set_class(sql_db::TBL_SYS_LOG);

        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_join_fields(array(sys_log_function::FLD_NAME), sys_log_function::class);
        $sc->set_join_fields(array(type_object::FLD_NAME), sql_db::TBL_SYS_LOG_STATUS);
        $sc->set_join_fields(array(sandbox::FLD_USER_NAME), user::class);
        $sc->set_join_fields(array(sandbox::FLD_USER_NAME . ' AS ' . self::FLD_SOLVER_NAME), user::class, self::FLD_SOLVER);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a system log entry by id from the database
     *
     * @param sql $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql $sc, int $id, string $class = self::class): sql_par
    {

        $qp = $this->load_sql($sc, sql_db::FLD_ID, $class);
        $sc->add_where($this->id_field(), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a system error from the database e.g. to be able to display more details
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        log_debug();

        global $db_con;

        // at the moment it is only possible to select the error by the id
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $class);
        return $this->row_mapper($db_con->get1($qp));
    }

    /**
     * set the main log entry parameters for updating one error field
     * @return change the log object with the update presets
     */
    private function log_upd(): change
    {
        log_debug();
        $log = new change($this->user());
        $log->action = change_action::UPDATE;
        $log->set_table(sql_db::TBL_SYS_LOG);

        return $log;
    }

    /**
     * @return string sys_log_id instead of system_log_id
     */
    function id_field(): string
    {
        return self::FLD_ID;
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
            $db_con->set_class(sql_db::TBL_SYS_LOG);
            $result = $db_con->update_old($this->id(), $log->field(), $log->new_id);
        }
        return $result;
    }

    /**
     * set the update parameters for the error status
     * @param sql_db $db_con the active database connection
     * @param system_log $db_rec the system log entry as saved in the database before the change
     * @return bool true if the status field has been updated
     */
    private function save_field_status(sql_db $db_con, system_log $db_rec): bool
    {
        log_debug();
        $result = false;
        if ($db_rec->status_id <> $this->status_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->status_name;
            $log->old_id = $db_rec->status_id;
            $log->new_value = $this->status_name;
            $log->new_id = $this->status_id;
            $log->row_id = $this->id();
            $log->set_field(sys_log_status::FLD_ID);
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * @return string either an empty string if saving has been successful or a message to the user with the reason, why it has failed
     */
    function save(): string
    {
        log_debug();

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_usr($this->user()->id());
        $db_con->set_class(sql_db::TBL_SYS_LOG);

        if ($this->id() > 0) {
            $db_rec = new system_log;
            $db_rec->set_user($this->user());
            if ($db_rec->load_by_id($this->id())) {
                log_debug("database entry loaded");
            }

            if (!$this->save_field_status($db_con, $db_rec)) {
                $result .= 'saving the error log failed';
            }
        }

        if ($result != '') {
            log_err($result);
        }

        return $result;
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