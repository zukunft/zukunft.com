<?php

/*

  user_log.php - object to save the user changes in the database in a format, so that is can fast be displayed to the user
  ------------
  
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

/*

Rules:
Never change a ID
never delete a word

Every user has its sandbox, means a list of all his changes


The normal word table contain the value, word, formula, verb or links that is used by most users
for each normal table there is an overwrite table with the user changes/overwrites
maybe for each huge table is also a log table with the hist of the user changes

todo:

cache table, field and action id to speed up, because this will never change

*/


class user_log {

  public  $id          = NULL;  // the database id of the log entry (used to update a log entry in case of an insert where the ref id is not yet know at insert)
  public  $usr_id      = NULL;  // the user id who has done the change
  public  $action      = '';    // text for the user action e.g. "add", "update" or "delete"
  private $action_id   = NULL;  // database id for the action text
  public  $table       = '';    // name of the table that has been updated
  private $table_id    = NULL;  // database id for the table text
  public  $field       = '';    // name of the field that has been updated 
  private $field_id    = NULL;  // database id for the field text
  public  $old_value   = '';    // the field value before the user change
  public  $old_id      = NULL;  // the reference id before the user change e.g. for fields using a subtable such as status
  public  $new_value   = '';    // the field value after the user change
  public  $new_id      = NULL;  // the reference id after the user change e.g. for fields using a subtable such as status
  public  $std_value   = '';    // the standard field value for all users that does not have changed it
  public  $std_id      = NULL;  // the standard reference id for all users that does not have changed it
  public  $row_id      = NULL;  // the reference id of the row in the database table
  
  // to save database space the table name is saved as a reference id in the log table
  private function set_table($debug) {
    zu_debug('user_log->set_table "'.$this->table.'" for '.$this->usr_id, $debug-10);
    
    // check parameter
    if ($this->table == "") { zu_err("missing table name","user_log->set_table", '', (new Exception)->getTraceAsString(), $this->usr); }
    if ($this->usr_id <= 0) { zu_err("missing user","user_log->set_table", '', (new Exception)->getTraceAsString(), $this->usr); }
    
    // if e.g. a "value" is changed $this->table is "values" and the reference 1 is saved in the log to save space
    $db_con = new mysql;         
    $db_con->type = "change_table";         
    $db_con->usr_id = $this->usr_id;         
    $table_id = $db_con->get_id($this->table, $debug-1);

    // add new table name if needed
    if ($table_id <= 0) {
      $table_id = $db_con->add_id ($this->table, $debug-1);
    }
    if ($table_id > 0) {
      $this->table_id = $table_id;
    } else {
      zu_fatal("Insert to change log failed due to table id failure.","user_log->add", '', (new Exception)->getTraceAsString(), $this->usr);
    }
  }

  private function set_field($debug) {
    zu_debug('user_log->set_field "'.$this->field.'" for table "'.$this->table.'" ('.$this->table_id.') and user '.$this->usr_id, $debug-10);
    
    // check parameter
    if ($this->table_id <= 0) { zu_err("missing table_id","user_log->set_field", '', (new Exception)->getTraceAsString(), $this->usr); }
    if ($this->field == "")   { zu_err("missing field name","user_log->set_field", '', (new Exception)->getTraceAsString(), $this->usr); }
    if ($this->usr_id <= 0)   { zu_err("missing user","user_log->set_field", '', (new Exception)->getTraceAsString(), $this->usr); }

    $db_con = new mysql;         
    $db_con->type = "change_field";         
    $db_con->usr_id = $this->usr_id;         
    $field_id = $db_con->get_id_2key($this->field, "table_id", $this->table_id, $debug-1);

    // add new field name if needed
    if ($field_id <= 0) {
      $field_id = $db_con->add_id_2key ($this->field, "table_id", $this->table_id, $debug-1);
    }
    if ($field_id > 0) {
      $this->field_id = $field_id;
    } else {
      zu_fatal("Insert to change log failed due to field id failure.","user_log->add", '', (new Exception)->getTraceAsString(), $this->usr);
    }
  }

  private function set_action($debug) {
    zu_debug('user_log->set_action "'.$this->action.'" for '.$this->usr_id, $debug-10);
    
    // check parameter
    if ($this->action == "") { zu_err("missing action name","user_log->set_action", '', (new Exception)->getTraceAsString(), $this->usr); }
    if ($this->usr_id <= 0)  { zu_err("missing user","user_log->set_action", '', (new Exception)->getTraceAsString(), $this->usr); }
    
    // if e.g. the action is "add" the reference 1 is saved in the log table to save space
    $db_con = new mysql;         
    $db_con->type = "change_action";         
    $db_con->usr_id = $this->usr_id;         
    $action_id = $db_con->get_id($this->action, $debug-1);

    // add new action name if needed
    if ($action_id <= 0) {
      $action_id = $db_con->add_id ($this->action, $debug-1);
    }
    if ($action_id > 0) {
      $this->action_id = $action_id;
    } else {
      zu_fatal("Insert to change log failed due to action id failure.","user_log->set_action", '', (new Exception)->getTraceAsString(), $this->usr);
    }
  }

  // display the last change related to one object (word, formula, value, verb, ...)
  // mainly used for testing
  // to do: if changes on table values are requested include also the table "user_values"
  function dsp_last($ex_time, $debug) {
    $result = '';

    $this->set_table($debug-1);
    $this->set_field($debug-1);

    $sql = "SELECT c.change_time,
                   u.user_name,
                   c.old_value,
                   c.old_id,
                   c.new_value,
                   c.new_id
              FROM changes c, users u
             WHERE c.change_field_id = ".$this->field_id."
               AND c.row_id = ".$this->row_id."
               AND c.user_id = u.user_id
          ORDER BY c.change_id DESC;";
    zu_debug("user_log->dsp_last get sql (".$sql.")", $debug-14);
    $db_con = new mysql;         
    $db_con->type = "change";         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    if (!$ex_time) {
      $result .= $db_row['change_time'].' ';
    }  
    if ($db_row['user_name'] <> '') {
      $result .= $db_row['user_name'].' ';
    }  
    if ($db_row['old_value'] <> '') {
      if ($db_row['new_value'] <> '') {
        $result .= 'changed '.$db_row['old_value'].' to '.$db_row['new_value'];
      } else {
        $result .= 'deleted '.$db_row['old_value'];
      }
    } else {
      $result .= 'added '.$db_row['new_value'];
    }
    return $result;
  }
  
  // log a user change of a word, value or formula
  function add($debug) {
    zu_debug('user_log->add do "'.$this->action.'" in "'.$this->table.','.$this->field.'" log change from "'.$this->old_value.'" (id '.$this->old_id.') to "'.$this->new_value.'" (id '.$this->new_id.') in row '.$this->row_id.' and for user '.$this->usr_id, $debug-10);

    $this->set_table($debug-10);
    $this->set_field($debug-10);
    $this->set_action($debug-10);
    
    $sql_fields = array();
    $sql_values = array();
    $sql_fields[] =          "user_id";
    $sql_values[] =     $this->usr_id;
    $sql_fields[] = "change_action_id";
    $sql_values[] =  $this->action_id;
    $sql_fields[] =  "change_field_id";
    $sql_values[] =   $this->field_id;

    $sql_fields[] =       "old_value";
    $sql_values[] = $this->old_value;
    $sql_fields[] =       "new_value";
    $sql_values[] = $this->new_value;

    if ($this->old_id > 0 OR $this->new_id > 0) {
      $sql_fields[] =       "old_id";
      $sql_values[] = $this->old_id;
      $sql_fields[] =       "new_id";
      $sql_values[] = $this->new_id;
    }

    $sql_fields[] =       "row_id";
    $sql_values[] = $this->row_id;
    
    $db_con = new mysql;         
    $db_con->type = "change";         
    $db_con->usr_id = $this->usr_id;         
    $log_id = $db_con->insert($sql_fields, $sql_values, $debug-10);

    if ($log_id <= 0) {
      // write the error message in steps to get at least some message if the parameters has caused the error
      zu_fatal("Insert to change log failed.","user_log->add");
      zu_fatal("Insert to change log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->field.")","user_log->add", '', (new Exception)->getTraceAsString(), $this->usr);
      zu_fatal("Insert to change log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->field.",".$this->old_value.",".$this->new_value.",".$this->row_id.")","user_log->add", '', (new Exception)->getTraceAsString(), $this->usr);
      $result = False;
    } else {
      $this->id = $log_id;
      $result = True;
    }
    
    return $result;
  }

  // add the row id to an existing log entry 
  // e.g. because the row id is know after the adding of the real record, 
  // but the log entry has been created upfront to make sure that logging is complete
  function add_ref($row_id, $debug) {
    zu_debug("user_log->add_ref (".$row_id." to ".$this->id." for user ".$this->usr_id.")", $debug-10);
    $db_con = new mysql;         
    $db_con->type = "change";         
    $db_con->usr_id = $this->usr_id;         
    $log_id = $db_con->update($this->id, "row_id", $row_id, $debug-1);
    if ($log_id <= 0) {
      // write the error message in steps to get at least some message if the parameters has caused the error
      zu_fatal("Update of reference in the change log failed.","user_log->add_ref");
      zu_fatal("Update of reference in the change log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->field.")","user_log->add_ref", '', (new Exception)->getTraceAsString(), $this->usr);
      zu_fatal("Update of reference in the change log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->field.",".$this->old_value.",".$this->new_value.",".$this->row_id.")","user_log->add_ref", '', (new Exception)->getTraceAsString(), $this->usr);
      $result = False;
    } else {
      $this->id = $log_id;
      $result = True;
    }
    return $result;
  }

  
}

?>
