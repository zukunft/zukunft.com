<?php

/*

  view_component_link.php - link a single display component/element to a view
  -----------------------
  
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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class view_component_link {

  // database fields
  public $id            = NULL; // the database id of the view component link, which is the same for the standard and the user specific view
  public $usr_cfg_id    = NULL; // the database id if there is alrady some user specific configuration for this word otherwise zero
  public $usr           = NULL; // the person who wants to see the display item / view component 
  public $owner_id      = NULL; // the user id of the person who created the view component, so if another user wants to change it, a user specific record is created
  public $view_id       = NULL; // the id of the view to which the display item should be linked
  public $view_entry_id = NULL; // the id of the linked display item
  public $order_nbr     = NULL; // to sort the display item
  public $pos_type_id   = NULL; // to to position the display item relative the the previous item (1= side, 2 = below)
  public $pos_code      = NULL; // side or below or ....
  public $excluded      = NULL; // for this object the excluded field is handled as a normal user sandbox field, but for the list excluded row are like deleted
                               
  // in memory only fields for searching and reference
  public $dsp           = NULL; // the display (view) object (used to save the correct name in the log)
  public $cmp           = NULL; // the display component (view entry) object (used to save the correct name in the log) 
  
  // reset the in memory fields used e.g. if some ids are updated
  private function reset_objects($debug) {
    $this->dsp = NULL;
    $this->cmp = NULL;
  }
  
  // load the view component parameters for all users
  private function load_standard($debug) {
    $result = '';
    
    // try to get the search values from the objects
    if ($this->id <= 0) {  
      if (isset($this->dsp) AND $this->view_id <= 0) {
        $this->view_id = $this->dsp->id;
      } 
      if (isset($this->cmp) AND $this->view_entry_id <= 0) {
        $this->view_entry_id = $this->cmp->id;
      } 
    }
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "l.view_entry_link_id = ".$this->id;
    } elseif ($this->view_id > 0 AND $this->view_entry_id > 0) {
      $sql_where = "l.view_id = ".$this->view_id." AND l.view_entry_id = ".$this->view_entry_id;
    }

    if ($sql_where == '') {
      // because this function is also used to test if a link is already around, this case is fine
    } else{  
      $sql = "SELECT l.view_entry_link_id,
                     l.user_id,
                     l.view_id,
                     l.view_entry_id,
                     l.order_nbr,
                     l.position_type,
                     l.excluded
                FROM view_entry_links l 
               WHERE ".$sql_where.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_dsp = $db_con->get1($sql, $debug-5);  
      if ($db_dsp['view_entry_link_id'] > 0) {
        $this->id            = $db_dsp['view_entry_link_id'];
        $this->owner_id      = $db_dsp['user_id'];
        $this->view_id       = $db_dsp['view_id'];
        $this->view_entry_id = $db_dsp['view_entry_id'];
        $this->order_nbr     = $db_dsp['order_nbr'];
        $this->position_type = $db_dsp['position_type'];
        $this->excluded      = $db_dsp['excluded'];

        // to review: try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } 
    }  
    return $result;
  }
  
  // load the missing view component parameters from the database
  function load($debug) {

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a view component link.", "view_component_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // try to get the search values from the objects
      if ($this->id <= 0 AND ($this->view_id <= 0 OR $this->view_entry_id <= 0)) {  
        if (isset($this->dsp) AND $this->view_id <= 0) {
          $this->view_id = $this->dsp->id;
        } 
        if (isset($this->cmp) AND $this->view_entry_id <= 0) {
          $this->view_entry_id = $this->cmp->id;
        } 
      }

      // if it still fails create an error message
      if ($this->id <= 0 AND ($this->view_id <= 0 OR $this->view_entry_id <= 0)) {  
        zu_err("The database ID (".$this->id.") or the view (".$this->view_id.") and item id (".$this->view_entry_id.") and the user (".$this->usr->id.") must be set to find a display item link.", "view_component_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {

        // set the where clause depending on the values given
        $sql_where = '';
        if ($this->id > 0) {
          $sql_where = "l.view_entry_link_id = ".$this->id;
        } elseif ($this->view_id > 0 AND $this->view_entry_id > 0) {
          $sql_where = "l.view_id = ".$this->view_id." AND l.view_entry_id = ".$this->view_entry_id;
        }

        if ($sql_where == '') {
          zu_err("Internal error on the where clause.", "view_component_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
        } else{  
          $sql = "SELECT l.view_entry_link_id,
                         u.view_entry_link_id AS user_link_id,
                         l.user_id,
                         l.view_id,
                         l.view_entry_id,
                         IF(u.order_nbr IS NULL,     l.order_nbr,     u.order_nbr)     AS order_nbr,
                         IF(u.position_type IS NULL, l.position_type, u.position_type) AS position_type,
                         IF(u.excluded IS NULL,      l.excluded,      u.excluded)      AS excluded
                    FROM view_entry_links l
               LEFT JOIN user_view_entry_links u ON u.view_entry_link_id = l.view_entry_link_id 
                                                AND u.user_id = ".$this->usr->id." 
                   WHERE ".$sql_where.";";
          $db_con = new mysql;         
          $db_con->usr_id = $this->usr->id;         
          $db_item = $db_con->get1($sql, $debug-5);  
          //if (is_null($db_item['excluded']) OR $db_item['excluded'] == 0) {
          $this->id            = $db_item['view_entry_link_id'];
          $this->usr_cfg_id    = $db_item['user_link_id'];
          $this->owner_id      = $db_item['user_id'];
          $this->view_id       = $db_item['view_id'];
          $this->view_entry_id = $db_item['view_entry_id'];
          $this->order_nbr     = $db_item['order_nbr'];
          $this->pos_type_id   = $db_item['position_type'];
          $this->excluded      = $db_item['excluded'];
          //} 
          zu_debug('view_component_link->load ('.$this->id.')', $debug-10); 
        }  
      }  
    }  
  }
    
  // to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
  function load_objects($debug) {
    if (!isset($this->dsp)) {
      $dsp = new view_dsp;
      $dsp->id  = $this->view_id;
      $dsp->usr = $this->usr;
      $dsp->load($debug-1); 
      $this->dsp = $dsp;
    }
    if (!isset($this->cmp)) {
      $cmp = new view_dsp;
      $cmp->id  = $this->view_entry_id;
      $cmp->usr = $this->usr;
      $cmp->load($debug-1); 
      $this->cmp = $cmp;
    }
  }
  
  // return the html code to display the link name
  function name_linked ($back, $debug) {
    $result = '';
    
    $this->load_objects($debug-1);
    if (isset($this->dsp) 
    AND isset($this->cmp)) {
      $result = $this->dsp->name_linked(NULL, $back, $debug-1).' to '.$this->cmp->name_linked($back, $debug-1);
    } else {
      $result .= zu_err("The view name or the component name cannot be loaded.", "view_component_link->name", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    
    return $result;    
  }
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
  function dsp_id ($debug) {
    $result = ''; 

    // get the link from the database
    $this->load_objects($debug-1);

    if ($this->dsp->name <> '' AND $this->cmp->name <> '') {
      $result .= $this->dsp->name.' '; // e.g. Company details
      $result .= $this->cmp->name;     // e.g. cash flow statment 
    }
    $result .= ' ('.$this->dsp->id.','.$this->cmp->id;
    if ($this->id > 0) {
      $result .= ' -> '.$this->id.')';
    }  
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->name;
    }
    return $result;
  }

  // 
  private function pos_type_name($debug) {
    zu_debug('view_component_link->pos_type_name do.', $debug-16);
    if ($this->type_id > 0) {
      $sql = "SELECT type_name, description
                FROM view_entry_position_types
               WHERE view_entry_position_type_id = ".$this->type_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_type = $db_con->get1($sql, $debug-5);  
      $this->type_name = $db_type['type_name'];
    }
    zu_debug('view_component_link->pos_type_name done.', $debug-16);
    return $this->type_name;    
  }
  
  // remember the move of a display component
  // up only the component that has been move by the user
  // and not all other component changed, because this would be more confusing
  private function log_move ($direction, $debug) {
  
  }
  
  // move one view component
  private function move ($direction, $debug) {
    $result = false;

    // load any missing parameters
    if (!isset($this->id) OR !isset($this->view_id)) {
      $this->load($debug-1);
    }

    // check the all minimal input parameters
    if ($this->id <= 0) {
      zu_err("Cannot load the view component link.", "view_component_link->move", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->view_id <= 0 OR $this->view_entry_id <= 0) {
      zu_err("The view component id and the view component id must be given to move it.", "view_component_link->move", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      zu_debug('move_up item '.$this->view_entry_id.' in view '.$this->view_id.' for '.$this->usr->name.'.', $debug-10);

      // set the database link object first, because it is used twice (read and write)
      $db_con = New mysql;
      $db_con->type = 'view_entry_link';         
      $db_con->usr_id = $this->usr->id;         

      // load all display components at once
      $sql = "SELECT view_entry_link_id, 
                     view_entry_id, 
                     order_nbr, 
                     order_nbr AS new_order_nbr  
                FROM view_entry_links l 
               WHERE view_id = ".$this->view_id." 
            ORDER BY order_nbr;";
      $rows = $db_con->get($sql, $debug-5);  

      // update all order numbers
      $pos = 0;
      $order_nbr = 0;
      for ($pos = 0; $pos < count($rows); $pos++) {
        zu_debug('view_component_link->move -> (check '.$pos.')', $debug-12);
        $row = $rows[$pos];
        if ($pos > 0) {
          if ($row['view_entry_id'] == $this->view_entry_id) {
            if ($direction == 'up') {
              $row['new_order_nbr'] = $order_nbr-1;
              $prior = $rows[$pos-1];
              zu_debug('view_component_link->move -> (move '.$prior['view_entry_id'].' from '.$prior['order_nbr'].' to '.$order_nbr.')', $debug-14);
              $prior['new_order_nbr'] = $order_nbr;        
              $rows[$pos-1] = $prior;
              zu_debug('view_component_link->move -> (move '.$row['view_entry_id'].' from '.$order_nbr.' to '.$row['new_order_nbr'].')', $debug-14);
            } else {
              $row['new_order_nbr'] = $order_nbr+1;
              $next = $rows[$pos+1];
              $next['new_order_nbr'] = $order_nbr;        
              zu_debug('view_component_link->move_down -> (move '.$next['view_entry_id'].' from '.$next['order_nbr'].' to '.$next['new_order_nbr'].')', $debug-14);
              $rows[$pos+1] = $next;
              zu_debug('view_component_link->move_down -> (move '.$row['view_entry_id'].' from '.$row['order_nbr'].' to '.$row['new_order_nbr'].')', $debug-14);
              $rows[$pos] = $row;
              // jump over the next row, because this value is already set
              $order_nbr++;
              $pos++;
            }
          } else {
            $row['new_order_nbr'] = $order_nbr;        
            if ($direction != 'up') { $rows[$pos] = $row; }  
          }
        } else {
          $row['new_order_nbr'] = $order_nbr;              
          if ($direction != 'up') { $rows[$pos] = $row; }  
        }  
        if ($direction == 'up') { $rows[$pos] = $row; }  
        $order_nbr++;
      }
      
      // update all needed order numbers
      foreach ($rows AS $row) {
        zu_debug('view_component_link->move -> (old'.$row['order_nbr'].',new'.$row['new_order_nbr'].')', $debug-10);
        if ($row['new_order_nbr'] <> $row['order_nbr']) {
          zu_debug('view_component_link->move(me'.$this->view_entry_id.',m'.$this->view_id.',u'.$this->usr->id.')', $debug-10);
          $rows = $db_con->get($sql, $debug-5);  
          $result = $db_con->update($row['view_entry_link_id'], 'order_nbr', $row['new_order_nbr'], $debug-5);  
        }
      }
    }

    return $result;
  }
  
  // move on view component up
  function move_up ($debug) {
    return $this->move('up', $debug-1);
  }

  // move on view component down
  function move_down ($debug) {
    return $this->move('down', $debug-1);
  }

  // true if noone has used this view component
  private function not_used($debug) {
    zu_debug('view_component_link->not_used ('.$this->id.')', $debug-10);  
    $result = true;
    
    // to review: maybe replace by a database foreign key check
    $result = $this->not_changed($debug-1);
    return $result;
  }

  // true if no other user has modified the view component
  private function not_changed($debug) {
    zu_debug('view_component_link->not_changed ('.$this->id.') by someone else than the onwer ('.$this->owner_id.').', $debug-10);  
    $result = true;
    
    if ($this->owner_id > 0) {
      $sql = "SELECT user_id 
                FROM user_view_entry_links 
               WHERE view_entry_link_id = ".$this->id."
                 AND user_id <> ".$this->owner_id."
                 AND excluded <> 1";
    } else {
      $sql = "SELECT user_id 
                FROM user_view_entry_links 
               WHERE view_entry_link_id = ".$this->id."
                 AND excluded <> 1";
    }
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    if ($db_row['user_id'] > 0) {
      $result = false;
    }
    zu_debug('view_component_link->not_changed for '.$this->id.' is '.zu_dsp_bool($result).'.', $debug-10);  
    return $result;
  }

  // true if the user is the owner and noone else has changed the view_entry_link
  // because if another user has changed the view_entry_link and the original value is changed, maybe the user view_entry_link also needs to be updated
  private function can_change($debug) {
    if (isset($this->dsp) AND isset($this->cmp)) {
      zu_debug('view_component_link->can_change "'.$this->dsp->name.'"/"'.$this->cmp->name.'" by user "'.$this->usr->name.'".', $debug-12);  
    } else {
      zu_debug('view_component_link->can_change "'.$this->id.'" by user "'.$this->usr->name.'".', $debug-12);  
    }
    $can_change = false;
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $can_change = true;
    }  
    zu_debug('view_component_link->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);  
    return $can_change;
  }

  // true if a record for a user specific configuration already exists in the database
  private function has_usr_cfg($debug) {
    $has_cfg = false;
    if ($this->usr_cfg_id > 0) {
      $has_cfg = true;
    }  
    return $has_cfg;
  }

  // create a database record to save user specific settings for this view_entry_link
  private function add_usr_cfg($debug) {
    $result = '';

    if (!$this->has_usr_cfg) {
      if (isset($this->dsp) AND isset($this->cmp)) {
        zu_debug('view_component_link->add_usr_cfg for "'.$this->dsp->name.'"/"'.$this->cmp->name.'" by user "'.$this->usr->name.'".', $debug-10);  
      } else {
        zu_debug('view_component_link->add_usr_cfg for "'.$this->id.'" and user "'.$this->usr->name.'".', $debug-10);  
      }

      // check again if there ist not yet a record
      $sql = "SELECT view_entry_link_id FROM `user_view_entry_links` WHERE view_entry_link_id = ".$this->id." AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      if ($db_row['view_entry_link_id'] <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_view_entry_link';
        $log_id = $db_con->insert(array('view_entry_link_id','user_id'), array($this->id,$this->usr->id), $debug-1);
        if ($log_id <= 0) {
          $result .= 'Insert of user_view_entry_link failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  private function del_usr_cfg_if_not_needed($debug) {
    $result = '';
    zu_debug('view_component_link->del_usr_cfg_if_not_needed pre check for "'.$this->name.' und user '.$this->usr->name.'.', $debug-12);

    //if ($this->has_usr_cfg) {

      // check again if there ist not yet a record
      $sql = "SELECT view_entry_link_id,
                     order_nbr,
                     position_type,
                     excluded
                FROM user_view_entry_links
               WHERE view_entry_link_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_cfg = $db_con->get1($sql, $debug-5);  
      zu_debug('view_component_link->del_usr_cfg_if_not_needed check for "'.$this->name.' und user '.$this->usr->name.' with ('.$sql.').', $debug-12);
      if ($usr_cfg['view_entry_link_id'] > 0) {
        if ($usr_cfg['order_nbr']     == Null
        AND $usr_cfg['position_type'] == Null
        AND $usr_cfg['excluded'] == Null) {
          // delete the entry in the user sandbox
          zu_debug('view_component_link->del_usr_cfg_if_not_needed any more for "'.$this->name.' und user '.$this->usr->name.'.', $debug-10);
          $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
        }  
      }  
    //}  
    return $result;
  }
  
  // simply remove a user adjustment without check
  private function del_usr_cfg_exe($db_con, $debug) {
    $result = '';

    $db_con->type = 'user_view_entry_link';
    $result .= $db_con->delete(array('view_entry_link_id','user_id'), array($this->id,$this->usr->id), $debug-1);
    if (str_replace('1','',$result) <> '') {
      $result .= 'Deletion of user view_entry_link '.$this->id.' failed for '.$this->usr->name.'.';
    }
    
    return $result;
  }
  
  // remove user adjustment and log it (used by user.php to undo the user changes)
  function del_usr_cfg($debug) {
    $result = '';

    if ($this->id > 0 AND $this->usr->id > 0) {
      zu_debug('view_component_link->del_usr_cfg  "'.$this->id.' und user '.$this->usr->name.'.', $debug-12);

      $db_type = 'user_view_entry_link';
      $log = $this->log_del($debug-1);
      if ($log->id > 0) {
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  

    } else {
      zu_err("The display component database ID and the user must be set to remove a user specific modification.", "view_component_link->del_usr_cfg", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // set the log entry parameter for a new value
  // e.g. that the user can see "added formula list to word view"
  private function log_add($debug) {
    zu_debug('view_component_link->log_add for "'.$this->dsp->name.'"/"'.$this->cmp->name.'" by user "'.$this->usr->name.'".', $debug-10);  
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'view_entry_links';
    $log->new_from  = $this->dsp;
    $log->new_to    = $this->cmp;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating the link ids itself
  private function log_upd($debug) {
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table     = 'view_entry_links';
    } else {  
      $log->table     = 'user_view_entry_links';
    }
    
    return $log;    
  }
  
  // set the log entry parameter to delete a view_entry
  // e.g. that the user can see "removed formula list from word view"
  private function log_del($debug) {
    zu_debug('view_component_link->log_del for "'.$this->dsp->name.'"/"'.$this->cmp->name.'" by user "'.$this->usr->name.'".', $debug-10);  
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = 'view_entry_links';
    $log->old_from  = $this->dsp;
    $log->old_to    = $this->cmp;
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one display component link field
  // e.g. that the user can see "moved formula list to position 3 in word view"
  private function log_upd_field($debug) {
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table     = 'view_entry_links';
    } else {  
      $log->table     = 'user_view_entry_links';
    }
    
    return $log;    
  }
  
  // actually update a formula field in the main database record or the user sandbox
  private function save_field_do($db_con, $log, $debug) {
    $result = '';
    zu_debug('view_component_link->save_field_do .', $debug-16);
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
        $db_con->type = 'user_view_entry_link';
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-1);
      }
    }
    zu_debug('view_component_link->save_field_do done.', $debug-16);
    return $result;
  }
  
  // set the update parameters for the view component order_nbr
  private function save_field_order_nbr($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->order_nbr <> $this->order_nbr) {
      $log = $this->log_upd_field($debug-1);
      $log->old_value = $db_rec->order_nbr;
      $log->new_value = $this->order_nbr;
      $log->std_value = $std_rec->order_nbr;
      $log->row_id    = $this->id; 
      $log->field     = 'order_nbr';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the word type
  private function save_field_type($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->pos_type_id <> $this->pos_type_id) {
      $log = $this->log_upd_field($debug-1);
      $log->old_value = $db_rec->pos_type_name($debug-1);
      $log->old_id    = $db_rec->pos_type_id;
      $log->new_value = $this->pos_type_name($debug-1);
      $log->new_id    = $this->pos_type_id; 
      $log->std_value = $std_rec->pos_type_name($debug-1);
      $log->std_id    = $std_rec->pos_type_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'position_type';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the formula word link excluded
  private function save_field_excluded($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->excluded <> $this->excluded) {
      if ($this->excluded == 1) {
        $log = $this->log_del($debug-1);
      } else {
        $log = $this->log_add($debug-1);
      }
      $new_value  = $this->excluded;
      $std_value  = $std_rec->excluded;
      $log->field = 'excluded';
      // also part of $this->save_field_do
      if ($this->can_change($debug-1)) {
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_view_entry_link';
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
  
  // save all updated view_entry_link fields excluding the name, because already done when adding a view_entry_link
  private function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    $result .= $this->save_field_order_nbr ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_type      ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded  ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('view_component_link->save_fields all fields for "'.$this->name.'" has been saved.', $debug-12);
    return $result;
  }
  
  // save updated the word_link id fields (dsp and cmp)
  // should only be called if the user is the owner and nobody has used the display component link
  private function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->dsp->id <> $this->dsp->id 
     OR $db_rec->cmp->id <> $this->cmp->id) {
      zu_debug('view_component_link->save_id_fields to "'.$this->dsp_id().'" from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().').', $debug-10);
      $log = $this->log_upd($debug-1);
      $log->old_from = $db_rec->dsp;
      $log->new_from = $this->dsp;
      $log->std_from = $std_rec->dsp;
      $log->old_to = $db_rec->cmp;
      $log->new_to = $this->cmp;
      $log->std_to = $std_rec->cmp;
      $log->row_id   = $this->id; 
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("view_id",     "view_entry_id"),
                                              array($this->dsp->id,$this->cmp->id), $debug-1);
      }
    }
    zu_debug('view_component_link->save_id_fields for "'.$this->name.'" has been done.', $debug-12);
    return $result;
  }
  
  // check if the id parameters are supposed to be changed 
  private function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    
    if ($db_rec->dsp->id <> $this->dsp->id 
     OR $db_rec->cmp->id <> $this->cmp->id) {
      $this->reset_objects($debug-1);
      // check if target link already exists
      zu_debug('view_component_link->save_id_if_updated check if target link already exists "'.$this->dsp_id().'" (has been "'.$db_rec->dsp_id().'").', $debug-14);
      $db_chk = clone $this;
      $db_chk->id = 0; // to force the load by the id fields
      $db_chk->load_standard($debug-10);
      if ($db_chk->id > 0) {
        // ... if yes request to delete or exclude the record with the id parameters before the change
        $to_del = clone $db_rec;
        $result .= $to_del->del($debug-20);        
        // .. and use it for the update
        $this->id = $db_chk->id;
        $this->owner_id = $db_chk->owner_id;
        // force the reinclude
        $this->excluded = Null;
        $db_rec->excluded = '1';
        $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-20);
        zu_debug('view_component_link->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add "'.$this->dsp_id().'".', $debug-14);
      } else {
        if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
          // in this case change is allowed and done
          zu_debug('view_component_link->save_id_if_updated change the existing display component link "'.$this->dsp_id().'" (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'").', $debug-14);
          $this->load_objects($debug-1);
          $result .= $this->save_id_fields($db_con, $db_rec, $std_rec, $debug-20);
        } else {
          // if the target link has not yet been created
          // ... request to delete the old
          $to_del = clone $db_rec;
          $result .= $to_del->del($debug-20);        
          // .. and create a deletion request for all users ???
          
          // ... and create a new display component link
          $this->id = 0;
          $this->owner_id = $this->usr->id;
          $result .= $this->add($db_con, $debug-20);
          zu_debug('view_component_link->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add "'.$this->dsp_id().'" (standard "'.$std_rec->dsp_id().'").', $debug-14);
        }
      }
    }  

    zu_debug('view_component_link->save_id_if_updated for "'.$this->name.'" has been done.', $debug-12);
    return $result;
  }
  
  // link a new component to a display view
  private function add($db_con, $debug) {
    zu_debug('view_component_link->add new link from "'.$this->cmp->name.'" to "'.$this->dsp->name.'".', $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the new view_component_link
      $this->id = $db_con->insert(array("view_id","view_entry_id","user_id"), array($this->dsp->id,$this->cmp->id,$this->usr->id), $debug-1);
      if ($this->id > 0) {
        // update the id in the log
        $result .= $log->add_ref($this->id, $debug-1);

        // create an empty db_rec element to force saving of all set fields
        $db_rec = new view_component_link;
        $db_rec->dsp = $this->dsp;
        $db_rec->cmp = $this->cmp;
        $db_rec->usr = $this->usr;
        $std_rec = clone $db_rec;
        // save the view_entry_link fields
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);

      } else {
        zu_err("Adding view_entry_link ".$this->name." failed.", "view_component_link->save");
      }
    }  
    
    return $result;
  }
  
  // update a view_entry_link in the database or create a user view_entry_link
  function save($debug) {
    zu_debug('view_component_link->save.', $debug-14);
    $result = "";
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'view_entry_link';         
    
    // check if a new value is supposed to be added
    if ($this->id <= 0) {
      zu_debug('view_component_link->save check if a new view_component_link for "'.$this->dsp->name.'" and "'.$this->cmp->name.'" needs to be created.', $debug-12);
      // check if a view_entry_link with the same view and component is already in the database
      $db_chk = new view_component_link;
      $db_chk->dsp = $this->dsp;
      $db_chk->cmp = $this->cmp;
      $db_chk->usr = $this->usr;
      $db_chk->load_standard($debug-1);
      if ($db_chk->id > 0) {
        zu_debug('view_component_link->save view_entry_link "'.$this->dsp->name.'" and "'.$this->cmp->name.'" are already linked.', $debug-12);
        $this->id = $db_chk->id;
      }
    }  
      
    if ($this->id <= 0) {
      zu_debug('view_component_link->save add new view_component_link for "'.$this->dsp->name.'" and "'.$this->cmp->name.'".', $debug-12);
      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug('view_component_link->save update "'.$this->id.'".', $debug-12);
      // read the database values to be able to check if something has been changed; done first, 
      // because it needs to be done for user and general formulas
      $db_rec = new view_component_link;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-1);
      $db_rec->load_objects($debug-1);
      zu_debug('view_component_link->save -> database view component link "'.$db_rec->name.'" ('.$db_rec->id.') loaded.', $debug-14);
      $std_rec = new view_component_link;
      $std_rec->id = $this->id;
      $std_rec->load_standard($debug-1);
      zu_debug('view_component_link->save -> standard view component link settings for "'.$std_rec->name.'" ('.$std_rec->id.') loaded.', $debug-14);
      
      // for a correct user view component link detection (function can_change) set the owner even if the view component link has not been loaded before the save 
      if ($this->owner_id <= 0) {
        $this->owner_id = $std_rec->owner_id;
      }
      
      // check if the id parameters are supposed to be changed 
      $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec, $debug-1);

      // if a problem has appeared up to here, don't try to save the values
      // the problem is shown to the user by the calling interactive script
      if (str_replace ('1','',$result) == '') {
        // update the order or link type
        $result .= $this->save_fields     ($db_con, $db_rec, $std_rec, $debug-1);        
      }
    }  
    
    return $result;    
  }

  // delete the complete view_entry_link (the calling function del must have checked that no one uses this view_entry_link)
  private function del_exe($debug) {
    zu_debug('view_component_link->del_exe.', $debug-16);
    $result = '';

    $log = $this->log_del($debug-1);
    if ($log->id > 0) {
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      // delete first all user configuration that have also been excluded
      $db_con->type = 'user_view_entry_link';
      $result .= $db_con->delete(array('view_entry_link_id','excluded'), array($this->id,'1'), $debug-1);
      $db_con->type   = 'view_entry_link';         
      $result .= $db_con->delete('view_entry_link_id', $this->id, $debug-1);
    }
    
    return $result;    
  }
  
  // exclude or delete a view_entry_link
  function del($debug) {
    zu_debug('view_component_link->del.', $debug-16);
    $result = '';
    $result .= $this->load($debug-1);
    if ($this->id > 0 AND $result == '') {
      zu_debug('view_component_link->del "'.$this->name.'".', $debug-14);
      if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
        $result .= $this->del_exe($debug-1);
      } else {
        $this->excluded = 1;
        $result .= $this->save($debug-1);        
      }
    }
    return $result;    
  }
  
}

?>
