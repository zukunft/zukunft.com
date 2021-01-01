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


class system_error_log {

  public $id            = NULL;  // the database id of the log entry 
  public $usr           = NULL;  // the user who wants to see the error
  public $usr_id        = NULL;  // the user id who was logged in when the error happened
  public $solver_id     = NULL;  // the admin id who has solved the problem
  public $log_time      = NULL;  // timestamp when the issue appeared
  public $type_id       = NULL;  // type of the error
  public $function_id   = NULL;  // the program function where the issue happened
  public $log_text      = '';    // the description of the problem
  public $log_trace     = '';    // the system trace
  public $status_id     = NULL;  // the status of the error

  public $function_name = NULL;  // 
  public $status_name   = NULL;  // 

  private function load($debug) {
    // at the moment it is only possible to select the error by the id
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "l.sys_log_id = ".$this->id;
    } 
    
    zu_debug('system_error_log->load search by "'.$sql_where.'"', $debug-14);
    if ($sql_where == '') {
      zu_err("The database ID must be set for loading a error entry.", "system_error_log->load", '', (new Exception)->getTraceAsString(), $this->usr);
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
              WHERE ".$sql_where.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->id;         
      $db_row = $db_con->get1($sql, $debug-14);  
      if ($db_row['sys_log_id'] > 0) {
        $this->usr_id        = $db_row['user_id'];
        $this->solver_id     = $db_row['solver_id'];
        $this->log_time      = $db_row['sys_log_time'];
        $this->type_id       = $db_row['sys_log_type_id'];
        $this->function_id   = $db_row['sys_log_function_id'];
        $this->function_name = $db_row['sys_log_function_name'];
        $this->log_text      = $db_row['sys_log_text'];
        $this->log_trace     = $db_row['sys_log_trace'];
        $this->status_id     = $db_row['sys_log_status_id'];
        $this->status_name   = $db_row['sys_log_status_name'];
      } 
      zu_debug('system_error_log->load done', $debug-12);
    }  
  }

  // set the main log entry parameters for updating one error field
  private function log_upd($debug) {
    zu_debug('system_error_log->log_upd', $debug-10);
    $log = New user_log;
    $log->usr_id = $this->usr->id;  
    $log->action = 'update';
    $log->table  = 'sys_log';

    return $log;    
  }
  
  // actually update a error field in the main database record or the user sandbox
  private function save_field_do($db_con, $log, $debug) {
    zu_debug('system_error_log->save_field_do', $debug-10);
    $result = '';
    if ($log->add($debug-1)) {
      $result .= $db_con->update($this->id, $log->field, $log->new_id, $debug-1);
    }
    zu_debug('system_error_log->save_field_do -> done', $debug-10);
    return $result;
  }
  
  // set the update parameters for the error status
  private function save_field_status($db_con, $db_rec, $debug) {
    zu_debug('system_error_log->save_field_status', $debug-10);
    $result = '';
    if ($db_rec->status_id <> $this->status_id) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->status_name;
      $log->old_id    = $db_rec->status_id;
      $log->new_value = $this->status_name;
      $log->new_id    = $this->status_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'sys_log_status_id';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  function save($debug) {
    $result = "";
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'sys_log';         
    
    if ($this->id > 0) {
      $db_rec = New system_error_log;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-1);
      zu_debug("system_error_log->save -> database entry loaded (".$db_rec->name.")", $debug-14);

      $result .= $this->save_field_status ($db_con, $db_rec, $debug-1);
    }  
  }
    
}

?>
