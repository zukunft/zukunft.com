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

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SYSTEM . 'sys_log_db.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'sys_log_statuus.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuus;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use DateTime;
use DateTimeInterface;

class sys_log extends db_object_seq_id
{

    /*
     * database link
     */

    // database and export JSON object field names
    // and comments used for the database creation
    // *_SQL_TYP is the sql data type used for the field
    const string TBL_COMMENT = 'for system error tracking and to measure execution times';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = sys_log_db::FLD_ID;
    const array FLD_NAMES = sys_log_db::FLD_NAMES;
    const array FLD_LST_ALL = sys_log_db::FLD_LST_ALL;


    /*
     * object vars
     */

    // object vars for the database fields
    public ?DateTime $log_time = null;      // timestamp when the issue appeared
    public ?user $usr = null;               // not using the parent user from db_object_seq_id_user because in extreme cases the log should be written also without user
    public ?int $function_id = null;        // the id of the program function where the issue happened
    public string $log_trace = '';          // the system trace
    public ?int $level_id = null;           // the id of the impact of the issue on the process
    public ?DateTime $update_time = null;   // timestamp when the issue has been updated
    public ?string $log_text = null;        // the unique description of the problem
    public ?string $log_description = null; // the long explanation of the problem
    public ?user $solver = null;            // the admin id who has solved the problem
    public ?int $status_id = null;          // the id of the status of the problem solving


    /*
     * construct and map
     */

    /**
     * reset the vars of this system log entry
     * @param bool $keep_user set to true to keep the original user for sandbox objects
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->log_time = null;
        $this->usr = null;
        $this->function_id = null;
        $this->log_trace = '';
        $this->level_id = null;
        $this->update_time = null;
        $this->log_text = null;
        $this->log_description = null;
        $this->solver = null;
        $this->status_id = null;
    }

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
        $result = parent::row_mapper($db_row, sys_log_db::FLD_ID);
        if ($result) {
            $this->log_time = $lib->get_datetime($db_row[sys_log_db::FLD_TIME]);
            if ($db_row[user_db::FLD_ID] > 0) {
                $usr = new user();
                $usr->id = $db_row[user_db::FLD_ID];
                $usr->name = $db_row[user_db::FLD_NAME];
                $this->usr = $usr;
            }
            $this->function_id = $db_row[sys_log_function::FLD_ID];
            $this->log_trace = $db_row[sys_log_db::FLD_TRACE];
            $this->level_id = $db_row[sys_log_level::FLD_ID];

            if ($db_row[sys_log_db::FLD_TIME_UPDATE] != null) {
                $this->update_time = $lib->get_datetime($db_row[sys_log_db::FLD_TIME_UPDATE]);
            } else {
                $this->update_time = null;
            }
            $this->log_text = $db_row[sys_log_db::FLD_TEXT];
            $this->log_description = $db_row[sys_log_db::FLD_DESCRIPTION];
            if ($db_row[sys_log_db::FLD_SOLVER] > 0) {
                $solver = new user();
                $solver->id = $db_row[sys_log_db::FLD_SOLVER];
                $this->solver = $solver;
            } else {
                $this->solver = null;
            }

            $this->status_id = $db_row[sys_log_status::FLD_ID];
        }
        return $result;
    }

    private function get_user_by_id(int $usr_id): user
    {
        global $sys;
        $usr = $sys->usr_sys->get_by_id($usr_id);
        if ($usr == null) {
            // TODO Prio 2 try to get the user from cache
            $usr = new user();
            if (!$usr->load_by_id($usr_id)) {
                log_warning('db user id ' . $usr_id . ' not found');
            }
        }
        return $usr;
    }


    /*
     * set and get
     */

    /**
     * set the main system-log vars and prepare database writing
     * @param int $usr_id
     * @param string $func_name
     * @param string $trace
     * @param int $level_id
     * @param string $text
     * @param string $description
     * @return void
     */
    function set(
        int          $usr_id,
        string       $func_name,
        string       $trace,
        int          $level_id,
        string       $text,
        string       $description,
        user_message $msg
    ): void
    {

        if ($this->log_time == null) {
            $this->log_time = new DateTime();
        }
        $this->set_user_id($usr_id);
        $this->set_function_by_name($func_name, $msg);
        $this->log_trace = $trace;
        $this->level_id = $level_id;
        $this->log_text = $text;
        $this->log_description = $description;
    }

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
     * set only the user just to write a log entry
     *
     * @param int $id the database id of the person who has faced the issue
     * @return void
     */
    function set_user_id(int $id): void
    {
        $usr = new user();
        $usr->id = $id;
        $this->usr = $usr;
    }

    /**
     * @return user|null the person who wants to see the error log
     */
    function get_user(): ?user
    {
        return $this->usr;
    }

    function set_function_by_name(string $func_name, user_message $msg): bool
    {
        global $sys;

        $fnc = $sys->typ_lst->sys_log_fnc->get_by_name($func_name);
        if ($fnc == null) {
            $fnc = new sys_log_function($func_name, $func_name, '', 0);
            if ($fnc->save($msg)) {
                $sys->typ_lst->sys_log_fnc->add($fnc);
                // TODO Prio 2 trigger update of the types in frontend
                $this->function_id = $fnc->id;
            }
        } else {
            $this->function_id = $fnc->id;
        }
        return $msg->is_ok();
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
        global $sys;
        return $sys->typ_lst->sys_log_sta->name($this->status_id);
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
        $sc->set_fields(sys_log_db::FLD_NAMES);
        $sc->set_join_fields(array(sys_log_function::FLD_NAME), sys_log_function::class);
        $sc->set_join_fields(array(sys_log_status::FLD_NAME), sys_log_statuus::class, sys_log_status::FLD_ID, sys_log_status::FLD_ID);
        $sc->set_join_fields(array(sandbox::FLD_USER_NAME), user::class);
        $sc->set_join_fields(array(sandbox::FLD_USER_NAME . ' AS ' . sys_log_db::FLD_SOLVER_NAME), user::class, sys_log_db::FLD_SOLVER);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a system log entry by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
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
        $log = new change($this->get_user());
        $log->set_action(change_actions::UPDATE);
        $log->set_table($tbl_name);

        return $log;
    }

    /**
     * @return string sys_log_id instead of sys_log_id
     */
    function id_field(): string
    {
        return sys_log_db::FLD_ID;
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
        $vars[json_fields::USER_ID] = $this->usr->id();
        $vars[json_fields::FUNCTION_ID] = $this->function_id;
        $vars[json_fields::TRACE] = $this->log_trace;
        $vars[json_fields::TYPE] = $this->level_id;

        $vars[json_fields::TIME_UPDATE] = $this->update_time?->format(DateTimeInterface::ATOM);
        $vars[json_fields::TEXT] = $this->log_text;
        $vars[json_fields::DESCRIPTION] = $this->log_description;
        $vars[json_fields::SOLVER] = $this->solver?->id();
        $vars[json_fields::STATUS] = $this->status_id;

        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
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
        $usr_msg = new user_message();
        if ($log->add($usr_msg)) {
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
        global $sys;

        $result = false;
        if ($db_rec->status_id <> $this->status_id) {
            $log = $this->log_upd();
            $log->old_value = $sys->typ_lst->sys_log_sta->name($db_rec->status_id);
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
     * add a new system log entry to the stdio text log and the database
     * @param user_message $msg
     * @return bool
     */
    function insert(user_message $msg): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc, $msg);
        $db_con->insert($qp, 'add syslog', $msg);
        return $msg->is_ok();
    }

    /**
     * @param user_message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @param sql_type_list|array $sc_par_lst the parameters for the sql statement creation
     * @return bool true if everything has been fine
     */
    function save(
        user_message        $msg,
        sql_type_list|array $sc_par_lst = []
    ): bool
    {
        log_debug();

        global $db_con;

        // build the database object because the is anyway needed
        $db_con->set_usr($this->get_user()->id);
        $db_con->set_class(sys_log::class);

        if ($this->id() > 0) {
            $db_rec = new sys_log;
            $db_rec->set_user($this->get_user());
            if ($db_rec->load_by_id($this->id())) {
                log_debug("database entry loaded");
            }

            if (!$this->save_field_status($db_con, $db_rec)) {
                $msg->add_message_text('saving the error log failed');
            }
        }

        if (!$msg->is_ok()) {
            log_err($msg->get_last_message());
        }

        return $msg->is_ok();
    }


    /*
    * sql write fields
    */

    /**
     * to get a list of all database fields that might be changed,
     * a field list must be corresponding to the db_fields_changed fields
     *
     * @return array list of all database field names that might have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                sys_log_db::FLD_TIME,
                user_db::FLD_ID,
                sys_log_function::FLD_ID,
                sys_log_db::FLD_TRACE,
                sys_log_level::FLD_ID,
                sys_log_db::FLD_TIME_UPDATE,
                sys_log_db::FLD_TEXT,
                sys_log_db::FLD_DESCRIPTION,
                sys_log_db::FLD_SOLVER,
                sys_log_status::FLD_ID
            ],
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sys_log|db_object_seq_id $obj the compare value to detect the changed fields
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sys_log|db_object_seq_id $obj,
        user_message             $msg,
        sql_type_list            $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        // some fields cannot be changed but are always expected on insert
        if ($sc_par_lst->is_insert()) {

            // and on insert the time is also always expected to be added
            $lst->add_field(
                sys_log_db::FLD_TIME,
                $this->log_time?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                null
            );

            // on insert the user is always added
            $lst->add_field(
                user_db::FLD_ID,
                $this->usr?->id,
                db_object_seq_id::FLD_ID_SQL_TYP,
                null
            );

            // and the causing function can also not be changed
            if ($obj->function_id !== $this->function_id) {
                if ($this->function_id < 0) {
                    $msg->add(msg_id::SYS_LOG_FUNCTION_MISSING, [
                        msg_id::VAR_TYPE => $this->function_id,
                        msg_id::VAR_NAME => $this->dsp_id()
                    ]);
                }
                $lst->add_type_field(
                    sys_log_function::FLD_ID,
                    sys_log_function::FLD_NAME,
                    $this->function_id,
                    $obj->function_id,
                    $sys->typ_lst->sys_log_fnc);
            }

            // the trace entry cannot be change but might be missing
            if ($obj->log_trace !== $this->log_trace) {
                $lst->add_field(
                    sys_log_db::FLD_TRACE,
                    $this->log_trace,
                    sql_field_type::TEXT,
                    $obj->log_trace
                );
            }

            // the original criticality level should also not be changed
            if ($obj->level_id !== $this->level_id) {
                if ($this->level_id < 0) {
                    $msg->add(msg_id::SYS_LOG_TYPE_MISSING, [
                        msg_id::VAR_TYPE => $this->level_id,
                        msg_id::VAR_NAME => $this->dsp_id()
                    ]);
                }
                $lst->add_type_field(
                    sys_log_level::FLD_ID,
                    sys_log_level::FLD_NAME,
                    $this->level_id,
                    $obj->level_id,
                    $sys->typ_lst->sys_log_lvl);
            }
        }

        // the update time is repeated on the syslog row for fast access
        if ($obj->update_time != $this->update_time) {
            $lst->add_field(
                sys_log_db::FLD_TIME_UPDATE,
                $this->update_time?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->update_time?->format(sql_db::DATE_FORMAT)
            );
        }

        // the unique issue text is expected to be changed only in rare cases by an admin
        if ($do_log) {
            $lst->add_field(
                sql::FLD_LOG_FIELD_PREFIX . sys_log_db::FLD_TEXT,
                $sys->typ_lst->cng_fld->id($table_id . sys_log_db::FLD_TEXT),
                change::FLD_FIELD_ID_SQL_TYP
            );
        }
        if ($obj->log_text !== $this->log_text) {
            $lst->add_field(
                sys_log_db::FLD_TEXT,
                $this->log_text,
                sql_field_type::TEXT,
                $obj->log_text
            );
        }

        // the detail description might also be changed by an user e.g. to describe in more details what has caused the issue
        if ($obj->log_description !== $this->log_description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sys_log_db::FLD_DESCRIPTION,
                    $sys->typ_lst->cng_fld->id($table_id . sys_log_db::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sys_log_db::FLD_DESCRIPTION,
                $this->log_description,
                sql_field_type::TEXT,
                $obj->log_description
            );
        }

        if ($obj->solver?->id !== $this->solver?->id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sys_log_db::FLD_SOLVER,
                    $sys->typ_lst->cng_fld->id($table_id . sys_log_db::FLD_SOLVER),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($obj->solver?->id == 0 or $obj->solver?->id == null) {
                $old_solver_id = null;
            } else {
                $old_solver_id = $obj->solver?->id;
            }
            $lst->add_field(
                sys_log_db::FLD_SOLVER,
                $this->solver?->id,
                db_object_seq_id::FLD_ID_SQL_TYP,
                $old_solver_id
            );
        }

        if ($obj->status_id !== $this->status_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sys_log_status::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . sys_log_status::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($this->status_id < 0) {
                $msg->add(msg_id::SYS_LOG_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->status_id,
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                sys_log_status::FLD_ID,
                sys_log_status::FLD_NAME,
                $this->status_id,
                $obj->status_id,
                $sys->typ_lst->sys_log_sta);
        }

        return $lst;
    }


    /*
      * debug
      */

    /**
     * @return string with the unique database id mainly for child dsp_id() functions
     */
    function dsp_id(): string
    {

        if ($this->update_time != null) {
            return 'system log id ' . $this->id()
                . ' updated at ' . $this->update_time->format(DateTimeInterface::ATOM)
                . ' row ' . $this->log_text;
        } else {
            return 'system log id ' . $this->id()
                . ' at ' . $this->log_time->format(DateTimeInterface::ATOM)
                . ' row ' . $this->log_text;

        }
    }

}