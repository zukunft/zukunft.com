<?php

/*

    system_log.php - object to handle a system errors
    --------------

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


use api\system_error_log_api;

class system_error_log
{

    // database and export JSON object field names
    const FLD_ID = 'sys_log_id';
    const FLD_SOLVER = 'solver_id';
    const FLD_TIME = 'sys_log_time';
    const FLD_TYPE = 'sys_log_type_id';
    const FLD_FUNCTION = 'sys_log_function_id';
    const FLD_FUNCTION_NAME = 'sys_log_function_name';
    const FLD_TEXT = 'sys_log_text';
    const FLD_TRACE = 'sys_log_trace';
    const FLD_STATUS = 'sys_log_status_id';

    // join database and export JSON object field names
    const FLD_SOLVER_NAME = 'solver_name';

    // all database field names excluding the id
    // the extra user field is needed because it is common to check the log entries of others users e.g. for admin users
    const FLD_NAMES = array(
        user_sandbox::FLD_USER,
        self::FLD_SOLVER,
        self::FLD_TIME,
        self::FLD_TYPE,
        self::FLD_FUNCTION,
        self::FLD_TEXT,
        self::FLD_TRACE,
        self::FLD_STATUS
    );

    // object vars for the database fields
    public ?int $id = null;             // the database id of the log entry
    public ?user $usr = null;           // the user who wants to see the error
    public ?int $usr_id = null;         // the user id who was logged in when the error happened
    public string $usr_name = '';       // the username who was logged in when the error happened
    public ?int $solver_id = null;      // the admin id who has solved the problem
    public string $solver_name = '';    // the admin id who has solved the problem
    public ?DateTime $log_time = null;  // timestamp when the issue appeared
    public ?int $type_id = null;        // type of the error
    public ?int $function_id = null;    // the program function where the issue happened
    public ?string $log_text = null;    // the description of the problem
    public string $log_trace = '';      // the system trace
    public ?int $status_id = null;      // the status of the error

    public string $function_name = '';  //
    public string $status_name = '';    //

    /**
     * @return bool true if a row is found
     */
    function row_mapper(array $db_row): bool
    {
        if ($db_row[self::FLD_ID] > 0) {
            $this->id = $db_row[self::FLD_ID];
            $this->usr_id = $db_row[user_sandbox::FLD_USER];
            $this->usr_name = $db_row[user_sandbox::FLD_USER_NAME];
            $this->solver_id = $db_row[self::FLD_SOLVER];
            $this->solver_name = $db_row[self::FLD_SOLVER_NAME];
            $this->log_time = $db_row[self::FLD_TIME];
            $this->type_id = $db_row[self::FLD_TYPE];
            $this->function_id = $db_row[self::FLD_FUNCTION];
            $this->function_name = $db_row[type_list::FLD_NAME];
            $this->log_text = $db_row[self::FLD_TEXT];
            $this->log_trace = $db_row[self::FLD_TRACE];
            $this->status_id = $db_row[self::FLD_STATUS];
            $this->status_name = $db_row[user_type::FLD_NAME];
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return system_error_log_api a filled frontend api object
     */
    function get_dsp_obj(): system_error_log_api
    {
        $dsp_obj = new system_error_log_api();
        $dsp_obj->id = $this->id;
        $dsp_obj->time = $this->log_time->format('Y-m-d H:i:s');
        $dsp_obj->user = $this->usr_name;
        $dsp_obj->text = $this->log_text;
        $dsp_obj->trace = $this->log_trace;
        $dsp_obj->prg_part = $this->function_name;
        $dsp_obj->owner = $this->solver_name;
        $dsp_obj->status = $this->status_name;
        return $dsp_obj;
    }

    /**
     * create the SQL statement to load one system log entry
     * @param sql_db $db_con the database link as parameter to be able to simulate the different SQL database in the unit tests
     * @return sql_par the database depending on sql statement to load a system error from the log table
     *                 and the unique name for the query
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(self::class);
        if ($this->id > 0) {
            $qp->name .= 'id';

            $db_con->set_type(sql_db::TBL_SYS_LOG);
            $db_con->set_name($qp->name);
            $db_con->set_fields(self::FLD_NAMES);
            $db_con->set_join_fields(array(self::FLD_FUNCTION_NAME), sql_db::TBL_SYS_LOG_FUNCTION);
            $db_con->set_join_fields(array(user_type::FLD_NAME), sql_db::TBL_SYS_LOG_STATUS);
            $db_con->set_join_fields(array(user_sandbox::FLD_USER_NAME), sql_db::TBL_USER);
            $db_con->set_join_fields(array(user_sandbox::FLD_USER_NAME . ' AS ' . self::FLD_SOLVER_NAME), sql_db::TBL_USER, self::FLD_SOLVER);
            $db_con->set_where_std($this->id);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();

        } else {
            log_err("The database ID (" . $this->id . ") must be set to load a log message.", "system_error_log->load_sql");
            $qp->sql = '';
        }
        return $qp;
    }

    /**
     * load a system error from the database e.g. to be able to display more details
     * @return bool true if a database row is found
     */
    private function load(): bool
    {
        log_debug('system_error_log->load search');

        global $db_con;

        // at the moment it is only possible to select the error by the id
        $qp = $this->load_sql($db_con);
        if ($qp->sql != '') {
            $db_con->usr_id = $this->id;
            return $this->row_mapper($db_con->get1($qp));
        } else {
            return false;
        }
    }

    /**
     * set the main log entry parameters for updating one error field
     * @return user_log_named the log object with the update presets
     */
    private function log_upd(): user_log_named
    {
        log_debug('system_error_log->log_upd');
        $log = new user_log_named;
        $log->usr = $this->usr;
        $log->action = user_log::ACTION_UPDATE;
        $log->table = sql_db::TBL_SYS_LOG;

        return $log;
    }

    /**
     * actually update an error field in the main database record or the user sandbox
     * @param sql_db $db_con the active database connection
     * @param user_log_named $log the log object with the update presets
     * @return bool true if the field has been updated
     */
    private function save_field_do(sql_db $db_con, user_log_named $log): bool
    {
        log_debug('system_error_log->save_field_do');
        $result = true;
        if ($log->add()) {
            $db_con->set_type(sql_db::TBL_SYS_LOG);
            $result = $db_con->update($this->id, $log->field, $log->new_id);
        }
        return $result;
    }

    /**
     * set the update parameters for the error status
     * @param sql_db $db_con the active database connection
     * @param system_error_log $db_rec the system log entry as saved in the database before the change
     * @return bool true if the status field has been updated
     */
    private function save_field_status(sql_db $db_con, system_error_log $db_rec): bool
    {
        log_debug('system_error_log->save_field_status');
        $result = false;
        if ($db_rec->status_id <> $this->status_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->status_name;
            $log->old_id = $db_rec->status_id;
            $log->new_value = $this->status_name;
            $log->new_id = $this->status_id;
            $log->row_id = $this->id;
            $log->field = self::FLD_STATUS;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * @return string either an empty string if saving has been successful or a message to the user with the reason, why it has failed
     */
    function save(): string
    {
        log_debug('system_error_log->save');

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(sql_db::TBL_SYS_LOG);

        if ($this->id > 0) {
            $db_rec = new system_error_log;
            $db_rec->id = $this->id;
            $db_rec->usr = $this->usr;
            if ($db_rec->load()) {
                log_debug("system_error_log->save -> database entry loaded");
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

}