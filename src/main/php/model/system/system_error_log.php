<?php

/*

  system_log.php - object to handle an system error
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/


class system_error_log
{

    public ?int $id = null;             // the database id of the log entry
    public ?user $usr = null;           // the user who wants to see the error
    public ?int $usr_id = null;         // the user id who was logged in when the error happened
    public ?int $solver_id = null;      // the admin id who has solved the problem
    public ?DateTime $log_time = null;  // timestamp when the issue appeared
    public ?int $type_id = null;        // type of the error
    public ?int $function_id = null;    // the program function where the issue happened
    public ?string $log_text = null;    // the description of the problem
    public ?string $log_trace = null;   // the system trace
    public ?int $status_id = null;      // the status of the error

    public ?string $function_name = null;  //
    public ?string $status_name = null;    //

    private function load()
    {

        global $db_con;

        // at the moment it is only possible to select the error by the id
        $sql_where = '';
        if ($this->id > 0) {
            $sql_where = "l.sys_log_id = " . $this->id;
        }

        log_debug('system_error_log->load search by "' . $sql_where . '"');
        if ($sql_where == '') {
            log_err("The database ID must be set for loading a error entry.", "system_error_log->load");
        } else {
            $sql = "SELECT l.sys_log_id,
                     l.user_id,
                     l.solver_id,
                     l.sys_log_time,
                     l.sys_log_type_id,
                     l.sys_log_function_id,
                     f.sys_log_function_name,
                     l.sys_log_text,
                     l.sys_log_trace,
                     l.sys_log_status_id,
                     s.sys_log_status_name
                FROM sys_log l
           LEFT JOIN sys_log_status s    ON l.sys_log_status_id   = s.sys_log_status_id
           LEFT JOIN sys_log_functions f ON l.sys_log_function_id = f.sys_log_function_id
              WHERE " . $sql_where . ";";
            //$db_con = New mysql;
            $db_con->usr_id = $this->id;
            $db_row = $db_con->get1($sql);
            if ($db_row['sys_log_id'] > 0) {
                $this->usr_id = $db_row['user_id'];
                $this->solver_id = $db_row['solver_id'];
                $this->log_time = $db_row['sys_log_time'];
                $this->type_id = $db_row['sys_log_type_id'];
                $this->function_id = $db_row['sys_log_function_id'];
                $this->function_name = $db_row['sys_log_function_name'];
                $this->log_text = $db_row['sys_log_text'];
                $this->log_trace = $db_row['sys_log_trace'];
                $this->status_id = $db_row['sys_log_status_id'];
                $this->status_name = $db_row['sys_log_status_name'];
            }
            log_debug('system_error_log->load done');
        }
    }

    // set the main log entry parameters for updating one error field
    private function log_upd(): user_log
    {
        log_debug('system_error_log->log_upd');
        $log = new user_log;
        $log->usr = $this->usr;
        $log->action = 'update';
        $log->table = 'sys_log';

        return $log;
    }

    // actually update an error field in the main database record or the user sandbox
    private function save_field_do($db_con, $log): bool
    {
        $result = true;
        if ($log->add()) {
            $db_con->set_type(DB_TYPE_SYS_LOG);
            $result = $db_con->update($this->id, $log->field, $log->new_id);
        }
        log_debug('system_error_log->save_field_do -> done');
        return $result;
    }

    // set the update parameters for the error status
    private function save_field_status($db_con, $db_rec): bool
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
            $log->field = 'sys_log_status_id';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    function save(): string
    {

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(DB_TYPE_SYS_LOG);

        if ($this->id > 0) {
            $db_rec = new system_error_log;
            $db_rec->id = $this->id;
            $db_rec->usr = $this->usr;
            $db_rec->load();
            log_debug("system_error_log->save -> database entry loaded");

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