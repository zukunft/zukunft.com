<?php

/*

  user_sandbox.php - the superclass for handling user specific objects including the database saving
  ----------------
  
  This superclass should be used by the classes words, formula, ... to enable user specific values and links
  
  
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
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class user_sandbox {

  // fields to define the object; should be set in the constructor of the chield object
  public $obj_name          = '';   // the object type to create the correct database fields e.g. for the type "word" the database field for the id is "word_id"
  public $type              = '';   // either a "named" object or a "link" object
  public $rename_can_switch = True; // true if renaming an object can switch to another object with the new name
  
  // database fields that are used in all objects and that have a specific behavier
  public $id                = NULL; // the database id of the object, which is the same for the standard and the user specific object
  public $usr_cfg_id        = NULL; // the database id if there is alrady some user specific configuration for this object
  public $usr               = NULL; // the person for whom the object is loaded, so to say the viewer
  public $owner_id          = NULL; // the user id of the person who created the object, which is the default object
  public $share_id          = NULL; // id for public, personal, group or private
  public $protection_id     = NULL; // id for no, user, admin or full protection
  public $excluded          = NULL; // the user sandbox for object is implimented, but can be switched off for the complete instance 
                                    // but for calculation, use and display an excluded should not be used

  // only used for objects that have a name
  public $name              = '';   // simply the object name, which cannot be empty if it is a named object

  // only used for objects that link two objects
  public $fob               = NULL; // the object from which this linked object is creating the connection
  public $tob               = NULL; // the object to   which this linked object is creating the connection
  public $from_name         = '';   // the name of the from object type e.g. view for view_component_links
  public $to_name           = '';   // the name of the  to  object type e.g. view for view_component_links
  
  // to be overwritten by the chield object
  function __construct() {
    $this->type     = 'named';
  }
    
  // reset the the search values of this object
  // needed to search for the standard object, because the search is work, value, formula or ... specific 
  function reset($debug) {
    $this->id         = NULL;
    $this->usr_cfg_id = NULL;
    $this->usr        = NULL;
    $this->owner_id   = NULL;
    $this->excluded   = NULL;

    $this->name       = '';

    $this->fob        = NULL;
    $this->tob        = NULL;
  }
  
  /* 
    for functions that does not make sense to use in the superclass

    private function load_standard($debug) {
    }
    
    function load($debug) {
    }
      
    function dsp_id ($debug) {
    }
  */

  /*
  
  check functions
  
  */
  
  /*
  // check if the owner is set for all records of an user sandbox object
  // e.g. if the owner of a new word_link is set correctly at creation
  //      if not changes of another can overwrite the standard and by that influence the setup of the creator
  function chk_owner ($type, $correct, $debug) {
    zu_debug($this->obj_name.'->chk_owner for '.$type, $debug-12);
    $msg = '';
    
    // just to allow the call with one line
    if ($type <> '') {
      $this->obj_name = $type;
    }
    
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = $this->obj_name;         
      
    if ($correct === True) {
      // set the default owner for all records with a missing owner
      $change_txt = $db_con->set_default_owner($debug-1);
      if ($change_txt <> '') {
        $msg = 'Owner set for '.$change_txt.' '.$type.'s.';
      }
    } else {
      // get the list of records with a missing owner
      $id_lst = $db_con->missing_owner($debug-1);
      $id_txt = implode(",",$id_lst);
      if ($id_txt <> '') {
        $msg = 'Owner not set for '.$type.' ID '.$id_txt.'.';
      }
    }
    
    return $id_lst;
  }
  */

  /*
  
  type loading functions TODO load type lists upfront
  
  */

  // load the share type and return the share code id
  function share_type_code_id($debug) {
    $result = '';
    zu_debug('value->share_type_code_id for '.$this->dsp_id(), $debug-10);

    // use the default share type if not set
    if ($this->share_id <= 0) {
      $result = DBL_SHARE_PUBLIC;
    } else {    
      $sql = "SELECT code_id
                FROM share_types 
              WHERE share_type_id = ".$this->share_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      if (isset($db_row)) {
        $result = $db_row['code_id'];  
      } else {    
        $result = DBL_SHARE_PUBLIC;
      }
    }

    zu_debug('value->share_type_code_id for '.$this->dsp_id().' got '.$result, $debug-12);
    return $result;    
  }
  
  // load the share type and return the share code id
  function share_type_name($debug) {
    $result = '';
    zu_debug('value->share_type_name for '.$this->dsp_id(), $debug-10);

    // use the default share type if not set
    if ($this->share_id <= 0) {
      $this->share_id = cl(DBL_SHARE_PUBLIC);
    }
    
    $sql = "SELECT share_type_name 
              FROM share_types 
             WHERE share_type_id = ".$this->share_id.";";
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    if (isset($db_row)) {
      $result = $db_row['share_type_name'];  
    }

    zu_debug('value->share_type_name for '.$this->dsp_id().' got '.$result, $debug-12);
    return $result;    
  }
  
  // load the protection type and return the protection code id
  function protection_type_code_id($debug) {
    $result = '';
    zu_debug('value->protection_type_code_id for '.$this->dsp_id(), $debug-10);

    // use the default share type if not set
    if ($this->protection_id <= 0) {
      $result = DBL_PROTECT_NO;
    } else {    
      $sql = "SELECT code_id
                FROM protection_types 
              WHERE protection_type_id = ".$this->protection_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      if (isset($db_row)) {
        $result = $db_row['code_id'];  
      } else {    
        $result = DBL_PROTECT_NO;
      }
    }

    zu_debug('value->protection_type_code_id for '.$this->dsp_id().' got '.$result, $debug-12);
    return $result;    
  }
  
  // load the protection type and return the protection code id
  function protection_type_name($debug) {
    $result = '';
    zu_debug('value->protection_type_name for '.$this->dsp_id(), $debug-10);

    // use the default share type if not set
    if ($this->protection_id <= 0) {
      $this->protection_id = cl(DBL_PROTECT_NO);
    }
    
    $sql = "SELECT protection_type_name
              FROM protection_types 
             WHERE protection_type_id = ".$this->protection_id.";";
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    if (isset($db_row)) {
      $result = $db_row['protection_type_name'];  
    }

    zu_debug('value->protection_type_name for '.$this->dsp_id().' got '.$result, $debug-12);
    return $result;    
  }
  
  /*
  
  save functions
  
  */

  // if the object has been changed by someone else than the owner the user id is returned
  // but only return the user id if the user has not also excluded it
  function changer($debug) {
    zu_debug($this->obj_name.'->changer '.$this->dsp_id(), $debug-10);  
    
    if ($this->owner_id > 0) {
      $sql = 'SELECT user_id 
                FROM user_'.$this->obj_name.'s 
               WHERE '.$this->obj_name.'_id = '.$this->id.'
                 AND user_id <> '.$this->owner_id.'
                 AND (excluded <> 1 OR excluded is NULL)';
    } else {
      $sql = 'SELECT user_id 
                FROM user_'.$this->obj_name.'s 
               WHERE '.$this->obj_name.'_id = '.$this->id.'
                 AND (excluded <> 1 OR excluded is NULL)';
    }
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-10);  
    $user_id = $db_row['user_id'];

    zu_debug($this->obj_name.'->changer is '.$user_id, $debug-10);  
    return $user_id;
  }
  
  // get the user id of the most often used link (position) beside the standard (position)
  // 
  // TODO review, because the median is not taking into account the number of standard used values
  function median_user($debug) {
    $result = 0;
    zu_debug($this->obj_name.'->median_user '.$this->dsp_id().' beside the onwer ('.$this->owner_id.')', $debug-10);  
    
    $sql = 'SELECT user_id 
              FROM user_'.$this->obj_name.'s 
              WHERE '.$this->obj_name.'_id = '.$this->id.'
                AND (excluded <> 1 OR excluded is NULL)';
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-1);  
    if ($db_row['user_id'] > 0) {
      $result = $db_row['user_id'];
    } else {  
      if ($this->owner_id > 0) {
        $result = $this->owner_id;
      } else {  
        if ($this->usr->id > 0) {
          $result = $this->usr->id;
        }  
      }  
    }
    zu_debug($this->obj_name.'->median_user for '.$this->dsp_id().' -> '.$result, $debug-10);  
    return $result;
  }
  
  // create a user setting for all objects that does not match the new standard object
  // TODO review
  function usr_cfg_create_all($std, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->usr_cfg_create_all '.$this->dsp_id(), $debug-10);  

    // get a list of users that are using this object
    $usr_lst = $this->usr_lst($debug);
    foreach ($usr_lst AS $usr) {
      // create a usr cfg if needed
    }

    zu_debug($this->obj_name.'->usr_cfg_create_all for '.$this->dsp_id().' -> '.$result, $debug-10);  
    return $result;
  }

  // remove all user setting that are not needed any more based on the new standard object
  // TODO review
  function usr_cfg_cleanup($std, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->usr_cfg_cleanup '.$this->dsp_id(), $debug-10);  

    // get a list of users that have a user cfg of this object
    $usr_lst = $this->usr_lst($debug);
    foreach ($usr_lst AS $usr) {
      // remove the usr cfg if not needed any more
    }

    zu_debug($this->obj_name.'->usr_cfg_cleanup for '.$this->dsp_id().' -> '.$result, $debug-10);  
    return $result;
  }

  // if the user is an admin the user can force to be the owner of this object
  // TODO review
  function take_ownership($debug) {
    $result = '';
    zu_debug($this->obj_name.'->take_ownership '.$this->dsp_id(), $debug-10);  

    if ($this->usr->is_admin($debug)) {
      // TODO activate $result .= $this->usr_cfg_create_all($debug-1);
      $result .= $this->set_owner($this->usr->id, $debug-1); // TODO remove double getting of the user object
      // TODO activate $result .= $this->usr_cfg_cleanup($debug-1);
    }

    zu_debug($this->obj_name.'->take_ownership '.$this->dsp_id().' -> done', $debug-10);  
    return $result;
  }

  // change the owner of the object 
  // any calling function should make sure that taking setting the owner is allowed
  // and that all user values 
  // TODO review sql and object field compare of user and standard
  function set_owner($new_owner_id, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->set_owner '.$this->dsp_id().' to '.$new_owner_id, $debug-10);  

    if ($this->id > 0 AND $new_owner_id > 0) {
      // to recreate the calling object
      $std = clone $this;
      $std->reset();
      $std->id = $this->id;
      $std->load_standard($debug-1);
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $sql_set = 'UPDATE `'.$this->obj_name.'s` 
                     SET user_id = '.$new_owner_id.'
                   WHERE '.$this->obj_name.'_id = '.$this->id.';';
      $result .= $db_con->exe($sql_set, DBL_SYSLOG_ERROR, $this->obj_name.'->set_owner', (new Exception)->getTraceAsString(), $debug-10);
      $this->owner_id = $new_owner_id;
      $new_owner = New user;
      $new_owner->id = $new_owner_id;
      $new_owner->load_test_user($debug-1);
      $this->usr = $new_owner;
      
      zu_debug($this->obj_name.'->set_owner for '.$this->dsp_id().' to '.$new_owner_id.' -> number of db updates: '.$result, $debug-10);  
    }
    return $result;
  }
  
  // true if no other user has modified the object
  // assuming that in this case no confirmation from the other users for an object change is needed
  function not_changed($debug) {
    $result = true;
    zu_debug($this->obj_name.'->not_changed ('.$this->id.') by someone else than the onwer ('.$this->owner_id.')', $debug-10);  
    
    $other_usr_id = $this->changer($debug-1);  
    if ($other_usr_id > 0) {
      $result = false;
    }

    zu_debug($this->obj_name.'->not_changed -> ('.$this->id.' is '.zu_dsp_bool($result).')', $debug-10);  
    return $result;
  }

  // true if no one has used the object
  // TODO if this has been used for calculation, this is also used
  function not_used($debug) {
    $result = true;
    zu_debug($this->obj_name.'->not_used ('.$this->id.')', $debug-10);  
    
    $using_usr_id = $this->median_user($debug-1);  
    if ($using_usr_id > 0) {
      $result = false;
    }

    zu_debug($this->obj_name.'->not_used -> ('.zu_dsp_bool($result).')', $debug-10);  
    return $result;
  }

  // true if no else one has used the object
  // TODO if this should be true if no one else has been used this object e.g. for calculation
  function used_by_someon_else($debug) {
    $result = true;
    zu_debug($this->obj_name.'->used_by_someon_else ('.$this->id.')', $debug-10);  
    
    zu_debug($this->obj_name.'->used_by_someon_else owner is '.$this->owner_id.' and the change is requested by '.$this->usr->id, $debug-18);  
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $changer_id = $this->changer($debug-1);
      // removed "OR $changer_id <= 0" because if no one has changed the object jet does not mean that it can be changed
      zu_debug($this->obj_name.'->used_by_someon_else changer is '.$changer_id.' and the change is requested by '.$this->usr->id, $debug-18);  
      if ($changer_id == $this->usr->id OR $changer_id <= 0) {
        $result = false;
      }  
    }  

    zu_debug($this->obj_name.'->used_by_someon_else -> ('.zu_dsp_bool($result).')', $debug-10);  
    return $result;
  }

  // true if the user is the owner and no one else has changed the object
  // because if another user has changed the object and the original value is changed, maybe the user object also needs to be updated
  function can_change($debug) {
    $result = false;
    zu_debug($this->obj_name.'->can_change '.$this->dsp_id(), $debug-10);  

    // if the user who wants to change it, is the owner, he can do it
    // or if the owner is not set, he can do it (and the owner should be set, because every object should have an owner)
    zu_debug($this->obj_name.'->can_change owner is '.$this->owner_id.' and the change is requested by '.$this->usr->id, $debug-18);  
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $result = true;
    } else {  
      $changer_id = $this->changer($debug-10);
      // removed "OR $changer_id <= 0" because if no one has changed the object jet does not mean that it can be changed
      if ($changer_id == $this->usr->id) {
        $result = true;
      }  
    }  

    zu_debug($this->obj_name.'->can_change -> ('.zu_dsp_bool($result).')', $debug-10);  
    return $result;
  }

  // true if a record for a user specific configuration already exists in the database
  function has_usr_cfg($debug) {
    $result = false;
    zu_debug($this->obj_name.'->has_usr_cfg '.$this->dsp_id(), $debug-10);

    if ($this->usr_cfg_id > 0) {
      $result = true;
    }  

    zu_debug($this->obj_name.'->has_usr_cfg -> ('.zu_dsp_bool($result).')', $debug-10);  
    return $result;
  }

  // simply remove a user adjustment without check
  function del_usr_cfg_exe($db_con, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->del_usr_cfg_exe '.$this->dsp_id(), $debug-10);

    $db_con->type = 'user_'.$this->obj_name;
    $result .= $db_con->delete(array($this->obj_name.'_id', 'user_id'), 
                               array($this->id,             $this->usr->id), $debug-1);
    if (str_replace('1','',$result) <> '') {
      $result .= 'Deletion of user '.$this->obj_name.' '.$this->id.' failed for '.$this->usr->name.'.';
    }
    
    return $result;
  }
  
  // remove user adjustment and log it (used by user.php to undo the user changes)
  function del_usr_cfg($debug) {
    $result = '';
    zu_debug($this->obj_name.'->del_usr_cfg '.$this->dsp_id(), $debug-10);

    if ($this->id > 0 AND $this->usr->id > 0) {
      $log = $this->log_del($debug-1);
      if ($log->id > 0) {
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  

    } else {
      zu_err('The database ID and the user must be set to remove a user specific modification of '.$this->obj_name.'.', $this->obj_name.'->del_usr_cfg', '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // set the log entry parameter for a new named object
  // for all not named objects like links, this function is overwritten
  function log_add($debug) {
    zu_debug($this->obj_name.'->log_add '.$this->dsp_id(), $debug-10);
    if ($this->type == 'named') {
      $log = New user_log;
      $log->field     = $this->obj_name.'_name';
      $log->old_value = '';
      $log->new_value = $this->name;
    } elseif ($this->type == 'link') {
      $log = New user_log_link;
      $log->new_from  = $this->fob;
      $log->new_to    = $this->tob;
    }
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = $this->obj_name.'s';
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one field
  function log_upd($log, $debug) {
    zu_debug($this->obj_name.'->log_upd '.$this->dsp_id(), $debug-10);
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-10)) {
      $log->table   = $this->obj_name.'s';
    } else {  
      $log->table   = 'user_'.$this->obj_name.'s';
    }
    
    return $log;    
  }
  
  // log the update of link
  function log_upd_link($debug) {
    zu_debug($this->obj_name.'->log_upd_link '.$this->dsp_id(), $debug-18);
    $log = New user_log_link;
    $log = $this->log_upd($log, $debug-10);
    
    return $log;    
  }
  
  // log the update of an object field
  function log_upd_field($debug) {
    zu_debug($this->obj_name.'->log_upd_field '.$this->dsp_id(), $debug-18);
    $log = New user_log;
    $log = $this->log_upd($log, $debug-10);
    
    return $log;    
  }
  
  // set the log entry parameter to delete a object
  function log_del($debug) {
    zu_debug($this->obj_name.'->log_del '.$this->dsp_id(), $debug-10);
    if ($this->type == 'named') {
      $log = New user_log;
      $log->field     = $this->obj_name.'_name';
      $log->old_value = $this->name;
      $log->new_value = '';
    } elseif ($this->type == 'link') {
      $log = New user_log_link;
      $log->old_from  = $this->fob;
      $log->old_to    = $this->tob;
    }
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = $this->obj_name.'s';
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // actually update a field in the main database record or the user sandbox
  function save_field_do($db_con, $log, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->save_field_do '.$this->dsp_id(), $debug-10);
    
    if ($log->new_id > 0) {
      $new_value = $log->new_id;
      $std_value = $log->std_id;
    } else {
      $new_value = $log->new_value;
      $std_value = $log->std_value;
    }  
    if ($log->add($debug-1)) {
      if ($this->can_change($debug-1)) {
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_'.$this->obj_name;
        if ($new_value == $std_value) {
          zu_debug($this->obj_name.'->save_field_do remove user change', $debug-14);
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }  
        $result .= $this->del_usr_cfg_if_not_needed($debug-1);
      }
    }
    
    return $result;
  }
    
  // set the update parameters for the value excluded
  function save_field_excluded($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->save_field_excluded '.$this->dsp_id(), $debug-10);

    if ($db_rec->excluded <> $this->excluded) {
      if ($this->excluded == 1) {
        $log = $this->log_del($debug-1);
      } else {
        $log = $this->log_add($debug-1);
      }
      $new_value  = $this->excluded;
      $std_value  = $std_rec->excluded;
      $log->field = 'excluded';
      // similar to $this->save_field_do
      if ($this->can_change($debug-1)) {
        $db_con->type = $this->obj_name;
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_'.$this->obj_name;
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-1);
      }
    }
    return $result;
  }
  
  // save the share level in the database if allowed
  function save_field_share($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->save_field_share '.$this->dsp_id(), $debug-10);

    if ($db_rec->share_id <> $this->share_id) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->share_type_name($debug-1);
      $log->old_id    = $db_rec->share_id;
      $log->new_value = $this->share_type_name($debug-1);
      $log->new_id    = $this->share_id; 
      // TODO is the setting of the standard needed?
      $log->std_value = $std_rec->share_type_name($debug-1);
      $log->std_id    = $std_rec->share_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'share_type_id';

      // save_field_do is not used because the share type can only be set on the user record
      if ($log->new_id > 0) {
        $new_value = $log->new_id;
        $std_value = $log->std_id;
      } else {
        $new_value = $log->new_value;
        $std_value = $log->std_value;
      }  
      if ($log->add($debug-1)) {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_'.$this->obj_name;
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      }
    }

    zu_debug($this->obj_name.'->save_field_share '.$this->dsp_id(), $debug-10);
    return $result;
  }
  
  // save the protection level in the database if allowed
  // TODO is the setting of the standard needed?
  function save_field_protection($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->save_field_protection '.$this->dsp_id(), $debug-10);

    if ($db_rec->protection_id <> $this->protection_id) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->protection_type_name($debug-1);
      $log->old_id    = $db_rec->protection_id;
      $log->new_value = $this->protection_type_name($debug-1);
      $log->new_id    = $this->protection_id; 
      $log->std_value = $std_rec->protection_type_name($debug-1);
      $log->std_id    = $std_rec->protection_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'protection_type_id';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }

    zu_debug($this->obj_name.'->save_field_protection '.$this->dsp_id(), $debug-10);
    return $result;
  }
  
  // updated the object id fields (e.g. for a word or formula the name, and for a link the linked ids)
  // should only be called if the user is the owner and nobody has used the display component link
  function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->save_id_fields '.$this->dsp_id(), $debug-10);
    
    if ($this->is_id_updated($db_rec, $debug-1)) {
      zu_debug($this->obj_name.'->save_id_fields to '.$this->dsp_id().' from '.$db_rec->dsp_id().' (standard '.$std_rec->dsp_id().')', $debug-10);
      $log = $this->log_upd_field($debug-1);
      if ($this->type == 'named') {
        $log->old_value = $db_rec->name;
        $log->new_value = $this->name;
        $log->std_value = $std_rec->name;
        $log->field     = $this->obj_name.'_name';
      } elseif ($this->type == 'link') {
        $log->old_from = $db_rec->fob;
        $log->new_from = $this->fob;
        $log->std_from = $std_rec->fob;
        $log->old_to = $db_rec->tob;
        $log->new_to = $this->tob;
        $log->std_to = $std_rec->tob;
      }
      $log->row_id    = $this->id; 
      if ($log->add($debug-1)) {
        if ($this->type == 'named') {
          $result .= $db_con->update($this->id, array($this->obj_name.'_name'),
                                                array($this->name), $debug-1);
        } elseif ($this->type == 'link') {
          $result .= $db_con->update($this->id, array($this->from_name.'_id', $this->from_name.'_id'),
                                                array($this->fob->id, $this->tob->id), $debug-1);
        }
      }
    }
    zu_debug($this->obj_name.'->save_id_fields for '.$this->dsp_id().' done', $debug-12);
    return $result;
  }
  
  private function is_id_updated($db_rec, $debug) {
    $result = False;
    zu_debug($this->obj_name.'->is_id_updated '.$this->dsp_id(), $debug-10);

    if ($this->type == 'named') {
      zu_debug($this->obj_name.'->is_id_updated compare name '.$db_rec->name.' with '.$this->name, $debug-22);
      if ($db_rec->name <> $this->name) {
        $result = True;
      }
    } elseif ($this->type == 'link') {
      zu_debug($this->obj_name.'->is_id_updated compare id '.$db_rec->fob->id.'/'.$db_rec->tob->id.' with '.$this->fob->id.'/'.$this->tob->id, $debug-22);
      if ($db_rec->fob->id <> $this->fob->id 
       OR $db_rec->tob->id <> $this->tob->id) {
        $result = True;
        // TODO check if next line is needed
        // $this->reset_objects($debug-1);
      }
    }

    zu_debug($this->obj_name.'->is_id_updated -> ('.zu_dsp_bool($result).')', $debug-10);
    return $result;
  }
  
  // check if the id parameters are supposed to be changed 
  private function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->save_id_if_updated '.$this->dsp_id(), $debug-10);
    
    if ($this->is_id_updated($db_rec, $debug-1)) {
      // check if target key value already exists
      zu_debug($this->obj_name.'->save_id_if_updated check if target already exists '.$this->dsp_id().' (has been "'.$db_rec->dsp_id().'")', $debug-14);
      $db_chk = clone $this;
      $db_chk->id = 0; // to force the load by the id fields
      $db_chk->load_standard($debug-10);
      if ($db_chk->id > 0) {
        zu_debug($this->obj_name.'->save_id_if_updated target already exists', $debug-18);
        if ($this->rename_can_switch) {
          // ... if yes request to delete or exclude the record with the id parameters before the change
          $to_del = clone $db_rec;
          $result .= $to_del->del($debug-20);        
          // .. and use it for the update
          // TODO review the logging: from the user view this is a change not a delete and update
          $this->id = $db_chk->id;
          $this->owner_id = $db_chk->owner_id;
          // TODO check which links needs to be updated, because this is a kind of combine objects
          // force the reinclude
          $this->excluded = Null;
          $db_rec->excluded = '1';
          $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-20);
          zu_debug($this->obj_name.'->save_id_if_updated found a '.$this->obj_name.' target '.$db_chk->dsp_id().', so del '.$db_rec->dsp_id().' and add '.$this->dsp_id(), $debug-14);
        } else {
          if ($this->type == 'named') {
            $result .= 'A '.$this->obj_name.' with the name "'.$this->name.'" already exists. Please use another name.';
          } elseif ($this->type == 'link') {
            $result .= 'A '.$this->obj_name.' from '.$this->fob->dsp_id().' to '.$this->tob->dsp_id().' already exists.';
          }
        }  
      } else {
        zu_debug($this->obj_name.'->save_id_if_updated target does not yet exist', $debug-18);
        if ($this->can_change($debug-1)) {
          // in this case change is allowed and done
          zu_debug($this->obj_name.'->save_id_if_updated change the existing '.$this->obj_name.' '.$this->dsp_id().' (db '.$db_rec->dsp_id().', standard '.$std_rec->dsp_id().')', $debug-14);
          // TODO check if next line is needed
          //$this->load_objects($debug-1);
          $result .= $this->save_id_fields($db_con, $db_rec, $std_rec, $debug-1);
        } else {
          // if the target link has not yet been created
          // ... request to delete the old
          $to_del = clone $db_rec;
          $result .= $to_del->del($debug-10);        
          // TODO .. and create a deletion request for all users ???
          
          // ... and create a new display component link
          $this->id = 0;
          $this->owner_id = $this->usr->id;
          $result .= $this->add($db_con, $debug-10);
          zu_debug($this->obj_name.'->save_id_if_updated recreate the '.$this->obj_name.' del '.$db_rec->dsp_id().' add '.$this->dsp_id().' (standard '.$std_rec->dsp_id().')', $debug-14);
        }
      }
    }  

    return $result;
  }
  
  // create a new object
  function add($db_con, $debug) {
    $result = '';
    zu_debug($this->obj_name.'->add '.$this->dsp_id(), $debug-10);
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {

      // insert the new object and save the object key
      if ($this->type == 'named') {
        $this->id = $db_con->insert(array($this->obj_name.'_name',"user_id"), array($this->name,$this->usr->id), $debug-1);
      } elseif ($this->type == 'link') {
        $this->id = $db_con->insert(array($this->from_name.'_id',$this->to_name.'_id',"user_id"), array($this->fob->id,$this->tob->id,$this->usr->id), $debug-1);
      } else {
        $result .= zu_err('Method add cannot (yet) handle objects of type '.$this->type.'.', 'user_sandbox->add', '', (new Exception)->getTraceAsString(), $this->usr);
      }
      
      // save the object fields if saving the key was successful
      if ($this->id > 0) {
        zu_debug($this->obj_name.'->add '.$this->type.' '.$this->dsp_id().' has been added', $debug-12);
        // update the id in the log
        $result .= $log->add_ref($this->id, $debug-1);
        //$result .= $this->set_owner($new_owner_id, $debug);

        // create an empty db_rec element to force saving of all set fields
        $db_rec = clone $this;
        $db_rec->reset();
        if ($this->type == 'named') {
          $db_rec->name = $this->name;
        } elseif ($this->type == 'link') {
          $db_rec->fob = $this->fob;
          $db_rec->tob = $this->tob;
        }
        $db_rec->usr  = $this->usr;
        $std_rec = clone $db_rec;
        // save the object fields
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);

      } else {
        zu_err('Adding '.$this->type.' '.$this->dsp_id().' failed due to logging error.', 'user_sandbox->add');
      }
    }  
    
    return $result;
  }

  // check if an object with another unique key already exists
  // if no similar object is found NULL is returned
  // if a similar object is found, the object is returned
  // any warning or error message needs to be created in the calling function
  // TODO: temp for the word object (to be overwritten)
  function get_similar($debug) {
    $result = NULL;
    zu_debug($this->obj_name.'->get_similar '.$this->dsp_id(), $debug-10);

    if ($this->type == 'named') {
      if ($this->obj_name == 'word') {
        if ($this->type_id <> cl(SQL_WORD_TYPE_FORMULA_LINK)) {
          $trm = $this->term($debug-1);  
          $result = $trm;
        }  
        if ($result > 0) {
          if ($trm->obj_name <> 'word') {
            $result .= $trm->id_used_msg($debug-1);
          } else {
            $this->id = $trm->id;
            zu_debug($this->obj_name.'->get_similar adding word name "'.$this->dsp_id().'" is OK', $debug-14);
          }  
        } else {      
          zu_debug($this->obj_name.'->get_similar no msg for "'.$this->dsp_id().'"', $debug-12);
        }
      } else {
        // used for view, view_component, ...
        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->name = $this->name;
        $db_chk->usr = $this->usr;
        $db_chk->load_standard($debug-1); // TODO or simple load, because it is user specific??
        if ($db_chk->id > 0) {
          zu_debug($this->obj_name.'->get_similar a '.$this->obj_name.' with the name "'.$this->fob->name.'" already exists', $debug-12);
          $result = $db_chk;
        }
      }
    } elseif ($this->type == 'link') {
      if (!isset($this->fob) OR !isset($this->tob)) {
        zu_err('The linked objects for '.$this->dsp_id().' are missing.', 'user_sandbox->get_similar', '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->fob = $this->fob;
        $db_chk->tob = $this->tob;
        $db_chk->usr = $this->usr;
        $db_chk->load_standard($debug-1);
        if ($db_chk->id > 0) {
          zu_debug($this->obj_name.'->get_similar the '.$this->fob->name.' "'.$this->fob->name.'" is already linked to "'.$this->tob->name.'"', $debug-12);
          $result = $db_chk;
        }
      }
    } else {
      $result .= zu_err('Method get_similar cannot (yet) handle objects of type '.$this->type.'.', 'user_sandbox->get_similar', '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }
  
  // add or update a user sandbox object (word, value, formula or ...) in the database
  function save($debug) {
    $result = '';
    zu_debug($this->obj_name.'->save '.$this->dsp_id(), $debug-10);
    
    // build the database object because the is anyway needed (TODO get the global database connection)
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = $this->obj_name;         

    // check if a new object is supposed to be added
    if ($this->id <= 0) {
      // check possible dublicates before adding
      zu_debug($this->obj_name.'->save check possible dublicates before adding '.$this->dsp_id(), $debug-12);
      $similar = $this->get_similar($debug-1);
      if (isset($similar)) {
        if ($similar-> id <> 0) {
          $this->id = $similar->id;
        }
      }
    }  
      
    // create a new object or update an existing
    // TODO check if handling of negative ids is correct
    if ($this->id == 0) {
      zu_debug($this->obj_name.'->save add', $debug-12);
      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug($this->obj_name.'->save update', $debug-12);
      // read the database values to be able to check if something has been changed; 
      // done first, because it needs to be done for user and general object values
      $db_rec = clone $this;
      $db_rec->reset();
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-10);
      zu_debug($this->obj_name.'->save reloaded from db', $debug-14);
      if ($this->type == 'link') {
        $db_rec->load_objects($debug-10);
      }
      $std_rec = clone $this;
      $std_rec->reset();
      $std_rec->id = $this->id;
      $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
      $std_rec->load_standard($debug-10);
      zu_debug($this->obj_name.'->save standard loaded', $debug-14);
      
      // for a correct user setting detection (function can_change) set the owner even if the object has not been loaded before the save 
      if ($this->owner_id <= 0) {
        $this->owner_id = $std_rec->owner_id;
      }
      
      if ($this->type == 'named') {
        if ($this->obj_name == 'word' or $this->obj_name == 'verb' or $this->obj_name == 'formula') {
          // if the name has changed, check if word, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
          if ($db_rec->name <> $this->name) {
            // check if a verb, formula or word with the same name is already in the database
            $similar = $this->get_similar($debug-1);
            if (isset($similar)) {
              if ($similar-> id <> 0) {
                $result .= $similar->id_used_msg($debug-1);
              }
            }
          }  
        }  
      }  

      // check if the id parameters are supposed to be changed 
      if (str_replace ('1','',$result) == '') {
        $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec, $debug-1);
      }

      // if a problem has appeared up to here, don't try to save the values
      // the problem is shown to the user by the calling interactive script
      if (str_replace ('1','',$result) == '') {
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);        
      }
    }

    return $result;    
  }
  
  // delete the complete object (the calling function del must have checked that no one uses this object)
  private function del_exe($debug) {
    $result = '';
    zu_debug($this->obj_name.'->del_exe '.$this->dsp_id(), $debug-10);

    // log the deletion request
    $log = $this->log_del($debug-1);
    if ($log->id > 0) {
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;  
      
      // for formulas first delete all links
      if ($this->obj_name == 'formula') {
        $result .= $this->del_links($db_con, $debug-1);

        // and the corresponding formula elements
        $db_con->type = 'formula_element';         
        $result .= $db_con->delete('formula_id', $this->id, $debug-1);

        // and the corresponding word name
        $db_con->type = 'word';         
        $result .= $db_con->delete('word_name', $this->name, $debug-1);
      }

      // delete first all user configuration that have also been excluded
      $db_con->type = 'user_'.$this->obj_name;
      $result .= $db_con->delete(array($this->obj_name.'_id','excluded'), array($this->id,'1'), $debug-1);
      if (str_replace('1','',$result) <> '') {
        zu_err('Delete failed, because removing the user settings for '.$this->obj_name.' '.$this->dsp_id().' returns '.$result, $this->obj_name.'->del');
      } else {
        // finally delete the object
        $db_con->type   = $this->obj_name;         
        $result .= $db_con->delete($this->obj_name.'_id', $this->id, $debug-1);
        zu_debug($this->obj_name.'->del_exe of '.$this->dsp_id().' done', $debug-14);
      }
    }
    
    return $result;    
  }
  
  // exclude or delete an object
  // TODO if the owner deletes it, change the owner to the new median user 
  // TODO check if all have deleted the object 
  //      does not remove the user excluding if noone else is using it
  function del($debug) {
    $result = '';
    zu_debug($this->obj_name.'->del '.$this->dsp_id(), $debug-10);
    
    // refresh the object with the database to include all updates until now (TODO start of lock for commit here)
    // TODO it seems that the owner is not updated
    $result .= $this->load($debug-18);
    if ($result <> '') {
      zu_warning('Reload of '.$this->obj_name.' '.$this->dsp_id().' for deletion or exclude has unexpectedly lead to '.$result.'.', $this->obj_name.'->del');
    } else {
      zu_debug($this->obj_name.'->del reloaded '.$this->dsp_id(), $debug-12);
      // check if the object is still valid
      if ($this->id <= 0) {
        zu_warning('Delete failed, because it seems that the '.$this->obj_name.' '.$this->dsp_id().' has been deleted in the meantime.', $this->obj_name.'->del');
      } else {
        // check if the object simply can be deleted, because it has never been used
        if (!$this->used_by_someon_else($debug-1)) {
          $result .= $this->del_exe($debug-1);
        } else {
          // if the owner deletes the object find a new owner or delete the object completely
          if ($this->owner_id == $this->usr->id) {
            zu_debug($this->obj_name.'->del owner has requested the deletion', $debug-16);
            // get median user
            $new_owner_id = $this->median_user($debug);
            if ($new_owner_id == 0) {
              zu_err('Delete failed, because no median user found for '.$this->obj_name.' '.$this->dsp_id().' but change is nevertheless not allowed.', $this->obj_name.'->del');
            } else {
              zu_debug($this->obj_name.'->del set owner for '.$this->dsp_id().' to user id "'.$new_owner_id.'"', $debug-8);
              
              // TODO change the original object, so that it uses the configuration of the new owner
              
              // set owner
              $result .= $this->set_owner($new_owner_id, $debug);

              // delete all user records of the new owner
              // does not use del_usr_cfg because the deletion reqest has already been logged
              // TODO reduce the db connection opening
              $db_con = new mysql;         
              $db_con->usr_id = $this->usr->id;         
              $this->del_usr_cfg_exe($db_con, $debug-1);
              
            }
          }
          // check again after the owner change if the object simply can be deleted, because it has never been used
          // TODO check if "if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {" would be correct
          if (!$this->used_by_someon_else($debug-1)) {
            zu_debug($this->obj_name.'->del can delete '.$this->dsp_id().' after owner change', $debug-8);
            $result .= $this->del_exe($debug-1);
          } else {
            zu_debug($this->obj_name.'->del exclude '.$this->dsp_id(), $debug-8);
            $this->excluded = 1;
            
            // simple version TODO combine with save function
            $db_con = new mysql;         
            $db_con->usr_id = $this->usr->id;         
            
            $db_rec = clone $this;
            $db_rec->reset();
            $db_rec->id  = $this->id;
            $db_rec->usr = $this->usr;
            $db_rec->load($debug-10);
            zu_debug($this->obj_name.'->save reloaded '.$db_rec->dsp_id().' from database', $debug-14);
            if ($this->type == 'link') {
              $db_rec->load_objects($debug-10);
            }
            $std_rec = clone $this;
            $std_rec->reset();
            $std_rec->id = $this->id;
            $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
            $std_rec->load_standard($debug-10);
            zu_debug($this->obj_name.'->save loaded standard '.$std_rec->dsp_id(), $debug-14);
            $this->save_field_excluded($db_con, $db_rec, $std_rec, $debug);
            
            // original call that has caused an id update
            //$result .= $this->save($debug-1);
          }
        }
      }
      // TODO end of db commit and unlock the records
      zu_debug($this->obj_name.'->del done', $debug-12);
    }
    
    return $result;    
  }
  
}

?>
