<?php

/*

  value_time_serie.php - the header object for time series values
  --------------------
  
  To save time and space values that have a timestamp are saved in a separate table
  
  
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

class value_time_serie extends user_sandbox_display {

  // database fields additional to the user sandbox fields for the value object
  public $source_id = NULL; // the id of source where the value is coming from
  public $grp_id    = NULL; // id of the group of phrases that are linked to this value for fast selections
  
  // in memory only fields

  function __construct() {
    $this->type      = 'value';
    $this->obj_name  = 'value_time_serie';

    $this->rename_can_switch = UI_CAN_CHANGE_VIEW_NAME;
  }
    
  /*
  
  database load functions that reads the object from the database
  
  */
  
  // load the standard value use by most users
  function load_standard($debug) {
    if ($this->id > 0) {
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_con->type = 'value_time_serie';         
      $sql = 'SELECT v.value_time_serie_id,
                     v.user_id,
                     v.source_id,
                     v.last_update,
                     v.excluded,
                     v.protection_type_id
                FROM `value_time_series` v 
               WHERE v.value_time_serie_id = '.$this->id.';';
      $db_val = $db_con->get1($sql, $debug-5);  
      if ($db_val['value_time_serie_id'] <= 0) {
        $this->reset($debug-1);
      } else {
        $this->id            = $db_val['value_time_serie_id'];
        $this->owner_id      = $db_val['user_id'];
        $this->source_id     = $db_val['source_id'];
        $this->last_update   = new DateTime($db_val['last_update']);
        $this->excluded      = $db_val['excluded'];
        $this->protection_id = $db_val['protection_type_id'];
        $this->share_id      = cl(DBL_SHARE_PUBLIC);
        $this->protection_id = cl(DBL_PROTECT_NO);

        // to review: try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
          $sql_set = "UPDATE `values` SET user_id = ".$this->usr->id." WHERE value_time_serie_id = ".$this->id.";";
          $sql_result = $db_con->exe($sql_set, DBL_SYSLOG_ERROR, "value_time_serie->load_standard", (new Exception)->getTraceAsString(), $debug-10);
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } 
    }  
  }
  
  // load the record from the database
  // in a separate function, because this can be called twice from the load function
  function load_rec($sql_where, $debug) {
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type = 'value_time_serie';         
    $sql = 'SELECT v.value_time_serie_id,
                    u.value_time_serie_id AS user_value_id,
                    v.user_id,
                    v.phrase_group_id,
                    v.time_word_id,
                    u.share_type_id,
                    IF(u.source_id IS NULL,          v.source_id,          u.source_id)          AS source_id,
                    IF(u.last_update IS NULL,        v.last_update,        u.last_update)        AS last_update,
                    IF(u.excluded IS NULL,           v.excluded,           u.excluded)           AS excluded,
                    IF(u.protection_type_id IS NULL, v.protection_type_id, u.protection_type_id) AS protection_type_id
              FROM `values` v 
          LEFT JOIN user_values u ON u.value_time_serie_id = v.value_time_serie_id 
                                AND u.user_id = '.$this->usr->id.' 
              WHERE '.$sql_where.';';
    zu_debug('value_time_serie->load_rec -> sql "'.$sql.'"', $debug-18);      
    $db_val = $db_con->get1($sql, $debug-5);  
    if ($db_val['value_time_serie_id'] <= 0) {
      $this->reset($debug-1);
    } else {
      $this->id            = $db_val['value_time_serie_id'];
      $this->usr_cfg_id    = $db_val['user_value_id'];
      $this->owner_id      = $db_val['user_id'];
      $this->source_id     = $db_val['source_id'];
      $this->share_id      = $db_val['share_type_id'];
      $this->protection_id = $db_val['protection_type_id'];
      $this->grp_id        = $db_val['phrase_group_id'];
      $this->time_id       = $db_val['time_word_id'];
      $this->last_update   = new DateTime($db_val['last_update']);
      $this->excluded      = $db_val['excluded'];
      zu_debug('value_time_serie->load_rec -> got '.$this->number.' with id '.$this->id, $debug-14);      
    } 
  }
  
  // insert or update a number in the database or save a user specific number
  function save($debug) {
    zu_debug('value->save "'.$this->number.'" for user '.$this->usr->name, $debug-10);
    $result = "";
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;  
    $db_con->type = 'value_time_serie';         
    
    // rebuild the value ids if needed e.g. if the front end function has just set a list of phrase ids get the responding group
    $result .= $this->set_grp_and_time_by_ids($debug-1);
    
    // check if a new value is supposed to be added
    if ($this->id <= 0) {
      zu_debug('value->save check if a value for "'.$this->name().'" and user '.$this->usr->name.' is already in the database', $debug-10);
      // check if a value for this words is already in the database
      $db_chk = New value;
      $db_chk->grp_id     = $this->grp_id;
      $db_chk->time_id    = $this->time_id;
      $db_chk->time_stamp = $this->time_stamp;
      $db_chk->usr        = $this->usr;
      $db_chk->load($debug-1);
      if ($db_chk->id > 0) {
        zu_debug('value->save value for "'.$this->grp->name().'"@"'.$this->time_phr->name.'" and user '.$this->usr->name.' is already in the database and will be updated', $debug-12);
        $this->id = $db_chk->id;
      }
    }  
    
    if ($this->id <= 0) {
      zu_debug('value->save "'.$this->name().'": '.$this->number.' for user '.$this->usr->name.' as a new value', $debug-10);

      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug('value->save update id '.$this->id.' to save "'.$this->number.'" for user '.$this->usr->id, $debug-10);
      // update a value
      // todo: if no one else has ever changed the value, change to default value, else create a user overwrite

      // read the database value to be able to check if something has been changed
      // done first, because it needs to be done for user and general values
      $db_rec = New value;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-1);
      zu_debug("value->save -> old database value loaded (".$db_rec->number.") with group ".$db_rec->grp_id.".", $debug-10);
      $std_rec = New value;
      $std_rec->id = $this->id;
      $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
      $std_rec->load_standard($debug-1);
      zu_debug("value->save -> standard value settings loaded (".$std_rec->number.")", $debug-14);

      // for a correct user value detection (function can_change) set the owner even if the value has not been loaded before the save 
      if ($this->owner_id <= 0) {
        $this->owner_id = $std_rec->owner_id;
      }
      
      // check if the id parameters are supposed to be changed 
      if ($result == '') {
        $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec, $debug-1);
      }

      // if a problem has appeared up to here, don't try to save the values
      // the problem is shown to the user by the calling interactive script
      if (str_replace ('1','',$result) == '') {
        // if the user is the owner and no other user has adjusted the value, really delete the value in the database
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);
      }

    }
    return $result;    
  }

}

?>
