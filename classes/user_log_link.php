<?php

/*

  user_log_link.php - object to save updates of references (links) by the user in the database in a format, so that is can fast be displayed to the user
  -----------------
  
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

A requirement for the expected behaviour of this setup is the strikt adherence of these rules in all classes:

1. never change a database ID
2. never delete a word


Other assumptions are:

Every user has its sandbox, means a list of all his changes

The normal word table contain the value, word, formula, verb or links that is used by most users
for each normal table there is an overwrite table with the user changes/overwrites
maybe for each huge table is also a log table with the hist of the user changes



*/


class user_log_link {

  public  $id            = NULL; // the database id of the log entry (used to update a log entry in case of an insert where the ref id is not yet know at insert)
  public  $usr_id        = NULL; // the user id who has done the change
  public  $action        = '';   // text for the user action e.g. "add", "update" or "delete"
  private $action_id     = NULL; // database id for the action text
  public  $table         = '';   // name of the table that has been updated
  private $table_id      = NULL; // database id for the table text
  // object set by the calling function
  public  $old_from      = NULL; // the from reference before the user change; should be the object, but is sometimes still the id
  public  $old_link      = NULL; // the reference type before the user change
  public  $old_to        = NULL; // the to reference before the user change
  public  $new_from      = NULL; // the from reference after the user change
  public  $new_link      = NULL; // the reference type after the user change
  public  $new_to        = NULL; // the to reference after the user change
  public  $std_from      = NULL; // the standard from reference for all users that does not have changed it
  public  $std_link      = NULL; // the standard reference type for all users that does not have changed it
  public  $std_to        = NULL; // the standard to reference for all users that does not have changed it
  public  $row_id        = NULL; // the reference id of the row in the database table
  // fields to save the database row that are filled here based on the object
  public  $old_from_id   = '';   // old id ref to the from record
  public  $old_link_id   = '';   // old id ref to the link record
  public  $old_to_id     = '';   // old id ref to the to record
  public  $old_text_from = '';   // fixed description for old_from
  public  $old_text_link = '';   // fixed description for old_link
  public  $old_text_to   = '';   // fixed description for old_to
  public  $new_from_id   = '';   // new id ref to the from record
  public  $new_link_id   = '';   // new id ref to the link record
  public  $new_to_id     = '';   // new id ref to the to record
  public  $new_text_from = '';   // fixed description for new_from
  public  $new_text_link = '';   // fixed description for new_link
  public  $new_text_to   = '';   // fixed description for new_to
  // to be replaced with new_text_link
  public  $link_text     = '';    // is used for fixed links such as the source for values
  
  // used until each call is done with the object instead of the id
  public  $usr        = NULL;  // 
  
  
  // identical to the functions in user_log (maybe move to a common object??)
  private function set_table($debug) {
    zu_debug('user_log_link->set_table "'.$this->table.'" for '.$this->usr_id, $debug-10);
    
    // check parameter
    if ($this->table == "") { zu_err("missing table name","user_log_link->set_table", '', (new Exception)->getTraceAsString(), $this->usr); }
    if ($this->usr_id <= 0) { zu_err("missing user","user_log_link->set_table", '', (new Exception)->getTraceAsString(), $this->usr); }
    
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

  private function set_action($debug) {
    zu_debug('user_log_link->set_action "'.$this->action.'" for '.$this->usr_id, $debug-10);
    
    // check parameter
    if ($this->action == "") { zu_err("missing action name","user_log_link->set_action", '', (new Exception)->getTraceAsString(), $this->usr); }
    if ($this->usr_id <= 0)  { zu_err("missing user","user_log_link->set_action", '', (new Exception)->getTraceAsString(), $this->usr); }
    
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
      zu_fatal("Insert to change log failed due to action id failure.","user_log_link->set_action", '', (new Exception)->getTraceAsString(), $this->usr);
    }
  }

  // functions used until each call is done with the object instead of the id
  private function set_usr($debug) {
    zu_debug('user_log_link->set_usr for '.$this->usr_id, $debug-12);
    if (!isset($this->usr)) {
      $usr = New user;
      $usr->id = $this->usr_id;
      $usr->load_test_user($debug-1);
      $this->usr = $usr;
      zu_debug('user_log_link->set_usr got '.$this->usr->name, $debug-14);
    }
  }
  private function word_name($id, $debug) {
    zu_debug('user_log_link->word_name for '.$id, $debug-12);
    $result = '';
    if ($id > 0) {
      $this->set_usr($debug-1);
      $wrd = new word_dsp;
      $wrd->id  = $id;
      $wrd->usr = $this->usr;
      $wrd->load($debug-1);
      $result = $wrd->name;
      zu_debug('user_log_link->word_name got '.$result, $debug-14);
    }
    return $result;
  }
  private function link_name($id, $debug) {
    $db_con = new mysql;         
    $db_con->type = "link_type";         
    $result = $db_con->get_name($id, $debug-1);
    return $result;
  }
  private function source_name($id, $debug) {
    $db_con = new mysql;         
    $db_con->type = "source";         
    $result = $db_con->get_name($id, $debug-1);
    return $result;
  }
  

  // this should be dismissed
  function add_link_ref($debug) {
    $this->add($debug-1);
  }
  
  // log a user change of a link / verb
  // this should be dismissed, instead use add, which also save the text reference for fast and reliable displaying
  function add_link($debug) {
    zu_debug("user_log_link->add_link (u".$this->usr_id." ".$this->action." ".$this->table.
                                    ",of".$this->old_from.",ol".$this->old_link.",ot".$this->old_to.
                                    ",nf".$this->new_from.",nl".$this->new_link.",nt".$this->new_to.",r".$this->row_id.")", $debug-10);

    $this->set_table($debug-1);
    $this->set_action($debug-1);
    
    $sql_fields = array();
    $sql_values = array();
    $sql_fields[] =          "user_id";
    $sql_values[] =     $this->usr_id;
    $sql_fields[] = "change_action_id";
    $sql_values[] =  $this->action_id;
    $sql_fields[] =  "change_field_id";
    $sql_values[] =   $this->field_id;

    $sql_fields[] =       "old_from_id";
    $sql_values[] = $this->old_from;
    $sql_fields[] =       "old_link_id";
    $sql_values[] = $this->old_link;
    $sql_fields[] =       "old_to_id";
    $sql_values[] = $this->old_to;
    
    $sql_fields[] =       "new_from_id";
    $sql_values[] = $this->new_from;
    $sql_fields[] =       "new_link_id";
    $sql_values[] = $this->new_link;
    $sql_fields[] =       "new_to_id";
    $sql_values[] = $this->new_to;
    
    $sql_fields[] =       "row_id";
    $sql_values[] = $this->row_id;
    
    $db_con = new mysql;         
    $db_con->type = "change_link";         
    $db_con->usr_id = $this->usr_id;         
    $log_id = $db_con->insert($sql_fields, $sql_values, $debug-1);

    if ($log_id <= 0) {
      // write the error message in steps to get at least some message if the parameters has caused the error
      zu_fatal("Insert to change link log failed.","user_log_link->add_link");
      zu_fatal("Insert to change link log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->link_text.")","user_log_link->add_link", '', (new Exception)->getTraceAsString(), $this->usr);
      zu_fatal("Insert to change link log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->link_text.",".$this->old_to.",".$this->new_to.",".$this->row_id.")","user_log_link->add_link", '', (new Exception)->getTraceAsString(), $this->usr);
      $result = False;
    } else {
      $this->id = $log_id;
      $result = True;
    }

    zu_debug('user_log_link->add_link -> ('.zu_dsp_bool($result).')', $debug-10);  
    return $result;
  }

  // display the last change related to one object (word, formula, value, verb, ...)
  // mainly used for testing
  function dsp_last($ex_time, $debug) {
    $result = '';

    $this->set_table($debug-1);
    
    $sql_where = '';
    if ($this->old_from_id > 0) {
      $sql_where .= ' AND c.old_from_id = '.$this->old_from_id;
    }
    if ($this->old_from_id > 0) {
      $sql_where .= ' AND c.old_to_id = '.$this->old_to_id;
    }
    if ($this->new_from_id > 0) {
      $sql_where .= ' AND c.new_from_id = '.$this->new_from_id;
    }
    if ($this->new_from_id > 0) {
      $sql_where .= ' AND c.new_to_id = '.$this->new_to_id;
    }

    $sql = "SELECT c.change_time,
                   u.user_name,
                   c.old_text_from,
                   c.old_from_id,
                   c.old_text_link,
                   c.old_link_id,
                   c.old_text_to,
                   c.old_to_id,
                   c.new_text_from,
                   c.new_from_id,
                   c.new_text_link,
                   c.new_link_id,
                   c.new_text_to,
                   c.new_to_id
              FROM change_links c, users u
             WHERE c.change_table_id = ".$this->table_id."
               AND c.user_id = u.user_id
               AND u.user_id = ".$this->usr_id."
                   ".$sql_where."
          ORDER BY c.change_link_id DESC;";
    zu_debug("user_log->dsp_last get sql (".$sql.")", $debug-14);
    $db_con = new mysql;         
    $db_con->type = "change_link";         
    $db_con->usr_id = $this->usr_id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    if (!$ex_time) {
      $result .= $db_row['change_time'].' ';
    }  
    if ($db_row['user_name'] <> '') {
      $result .= $db_row['user_name'].' ';
    }  
    if ($db_row['new_text_from'] <> '' AND $db_row['new_text_to'] <> '') {
      $result .= 'linked '.$db_row['new_text_from'].' to '.$db_row['new_text_to'];
    } elseif ($db_row['old_text_from'] <> '' AND $db_row['old_text_to'] <> '') {
      $result .= 'unlinked '.$db_row['old_text_from'].' from '.$db_row['old_text_to'];
    }
    return $result;
  }
  
  // similar to add_link, but additional fix the references as a text for fast displaying
  // $link_text is used for fixed links such as the source for values
  function add($debug) {
    zu_debug('user_log_link->add do "'.$this->action.'" of "'.$this->table.'" for user '.$this->usr_id, $debug-10); 

    $this->set_table($debug-1);
    $this->set_action($debug-1);
    
    // set the table specific references
    zu_debug('user_log_link->add -> set fields', $debug-16);
    if ($this->table == "words" 
     OR $this->table == "word_links") {
      if ($this->action == "add" OR $this->action == "update") {
        $this->new_text_from = $this->new_from->name; 
        $this->new_text_link = $this->new_link->name; 
        $this->new_text_to   = $this->new_to->name; 
        $this->new_from_id   = $this->new_from->id; 
        $this->new_link_id   = $this->new_link->id; 
        $this->new_to_id     = $this->new_to->id; 
      }
      if ($this->action == "del" OR $this->action == "update") {
        $this->old_text_from = $this->old_from->name; 
        $this->old_text_link = $this->old_link->name; 
        $this->old_text_to   = $this->old_to->name; 
        $this->old_from_id   = $this->old_from->id; 
        $this->old_link_id   = $this->old_link->id; 
        $this->old_to_id     = $this->old_to->id; 
      }
    }
    if ($this->table == "refs") {
      if ($this->action == "add" OR $this->action == "update") {
        $this->new_text_from = $this->new_from->name; 
        $this->new_text_link = $this->new_link->name; 
        $this->new_text_to   = $this->new_to->external_key; 
        $this->new_from_id   = $this->new_from->id; 
        $this->new_link_id   = $this->new_link->id; 
        $this->new_to_id     = $this->new_to->id; 
      }
      if ($this->action == "del" OR $this->action == "update") {
        $this->old_text_from = $this->old_from->name; 
        $this->old_text_link = $this->old_link->name; 
        $this->old_text_to   = $this->old_to->external_key; 
        $this->old_from_id   = $this->old_from->id; 
        $this->old_link_id   = $this->old_link->id; 
        $this->old_to_id     = $this->old_to->id; 
      }
    }
    if ($this->table == "view_component_links" 
     OR $this->table == "value_phrase_links" 
     OR $this->table == "formula_links") {
      if ($this->action == "add" OR $this->action == "update") {
        $this->new_text_from = $this->new_from->name; 
        $this->new_text_to   = $this->new_to->name; 
        $this->new_from_id   = $this->new_from->id; 
        $this->new_to_id     = $this->new_to->id; 
      }
      if ($this->action == "del" OR $this->action == "update") {
        $this->old_text_from = $this->old_from->name; 
        $this->old_text_to   = $this->old_to->name; 
        $this->old_from_id   = $this->old_from->id; 
        $this->old_to_id     = $this->old_to->id; 
      }
    }
    if ($this->table == "values" AND $this->link_text == "source") {
      if ($this->old_to > 0) { $this->old_text_to = $this->source_name($this->old_to, $debug-1); }
      if ($this->new_to > 0) { $this->new_text_to = $this->source_name($this->new_to, $debug-1); }
    }
    zu_debug('user_log_link->add -> set fields done', $debug-16);
      
    $sql_fields = array();
    $sql_values = array();
    $sql_fields[] =          "user_id";
    $sql_values[] =     $this->usr_id;
    $sql_fields[] = "change_action_id";
    $sql_values[] =  $this->action_id;
    $sql_fields[] =  "change_table_id";
    $sql_values[] =   $this->table_id;

    $sql_fields[] =       "old_from_id";
    $sql_values[] = $this->old_from_id;
    $sql_fields[] =       "old_link_id";
    $sql_values[] = $this->old_link_id;
    $sql_fields[] =       "old_to_id";
    $sql_values[] = $this->old_to_id;
    
    $sql_fields[] =       "new_from_id";
    $sql_values[] = $this->new_from_id;
    $sql_fields[] =       "new_link_id";
    $sql_values[] = $this->new_link_id;
    $sql_fields[] =       "new_to_id";
    $sql_values[] = $this->new_to_id;
    
    $sql_fields[] =       "old_text_from";
    $sql_values[] = $this->old_text_from;
    $sql_fields[] =       "old_text_link";
    $sql_values[] = $this->old_text_link;
    $sql_fields[] =       "old_text_to";
    $sql_values[] = $this->old_text_to;
    
    $sql_fields[] =       "new_text_from";
    $sql_values[] = $this->new_text_from;
    $sql_fields[] =       "new_text_link";
    $sql_values[] = $this->new_text_link;
    $sql_fields[] =       "new_text_to";
    $sql_values[] = $this->new_text_to;
    
    $sql_fields[] =       "row_id";
    $sql_values[] = $this->row_id;
    
    $db_con = new mysql;         
    $db_con->type = "change_link";         
    $db_con->usr_id = $this->usr_id;         
    $log_id = $db_con->insert($sql_fields, $sql_values, $debug-1);

    if ($log_id <= 0) {
      // write the error message in steps to get at least some message if the parameters causes an additional the error
      zu_fatal("Insert to change link log failed.","user_log_link->add");
      zu_fatal("Insert to change link log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->link_text.")","user_log_link->add", '', (new Exception)->getTraceAsString(), $this->usr);
      zu_fatal("Insert to change link log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->link_text.",".$this->old_to.",".$this->new_to.",".$this->row_id.")","user_log_link->add", '', (new Exception)->getTraceAsString(), $this->usr);
      $result = False;
    } else {
      $this->id = $log_id;
      $result = True;
    }

    zu_debug('user_log_link->add -> ('.zu_dsp_bool($result).')', $debug-10);  
    return $result;
  }

  // add the row id to an existing log entry 
  // e.g. because the row id is know after the adding of the real record, 
  // but the log entry has been created upfront to make sure that logging is complete
  function add_ref($row_id, $debug) {
    zu_debug("user_log_link->add_ref (".$row_id." to ".$this->id." for user ".$this->usr_id.")", $debug-10);
    $db_con = new mysql;         
    $db_con->type = "change_link";         
    $db_con->usr_id = $this->usr_id;         
    $log_id = $db_con->update($this->id, "row_id", $row_id, $debug-1);
    if ($log_id <= 0) {
      // write the error message in steps to get at least some message if the parameters causes an additional the error
      zu_fatal("Update of reference in the change log failed.","user_log_link->add_ref");
      zu_fatal("Update of reference in the change log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->field.")","user_log_link->add_ref", '', (new Exception)->getTraceAsString(), $this->usr);
      zu_fatal("Update of reference in the change log failed with (".$this->usr_id.",".$this->action.",".$this->table.",".$this->field.",".$this->old_value.",".$this->new_value.",".$this->row_id.")","user_log_link->add_ref", '', (new Exception)->getTraceAsString(), $this->usr);
      $result = False;
    } else {
      $this->id = $log_id;
      $result = True;
    }
    return $result;
  }

  
}

?>
