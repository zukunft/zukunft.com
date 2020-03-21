<?php

/*

  view_component_link.php - link a single display component/element to a view
  -----------------------

  TODO
  if a link is owned by someone, who has deleted it, it can be changed by anyone else
  or another way to formulate this: if the owner deletes a link, the ownership should be move to the remaining users
  
  force to remove all user settings to be able to delete a link as an admin
  
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

class view_component_link extends user_sandbox {

  public $view_id           = NULL; // the id of the view to which the display item should be linked
  public $view_component_id = NULL; // the id of the linked display item
  public $order_nbr         = NULL; // to sort the display item
  public $pos_type_id       = NULL; // to to position the display item relative the the previous item (1= side, 2 = below)
  public $pos_code          = NULL; // side or below or ....
                               
  
  function __construct() {
    $this->type      = 'link';
    $this->obj_name  = 'view_component_link';
    $this->from_name = 'view';
    $this->to_name   = 'view_component';

    $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_LINK;
  }
    
  // reset the in memory fields used e.g. if some ids are updated
  private function reset_objects($debug) {
    $this->fob = NULL; // the display (view) object (used to save the correct name in the log)
    $this->tob = NULL; // the display component (view entry) object (used to save the correct name in the log) 
  }
  
  function reset($debug) {
    $this->id         = NULL;
    $this->usr_cfg_id = NULL;
    $this->usr        = NULL;
    $this->owner_id   = NULL;
    $this->excluded   = NULL;
    
    $this->view_id           = NULL;
    $this->view_component_id = NULL;
    $this->order_nbr         = NULL;
    $this->pos_type_id       = NULL;
    $this->pos_code          = NULL;
    
    $this->reset_objects();
  }

  // build the sql where string
  /*
  private function sql_where($debug) {
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "l.view_component_link_id = ".$this->id;
    } elseif ($this->view_id > 0 AND $this->view_component_id > 0) {
      $sql_where = "l.view_id = ".$this->view_id." AND l.view_component_id = ".$this->view_component_id;
    }
    return $sql_where;
  }
  */
  
  // load the view component parameters for all users
  function load_standard($debug) {
    $result = '';
    
    // try to get the search values from the objects
    if ($this->id <= 0) {  
      if (isset($this->fob) AND $this->view_id <= 0) {
        $this->view_id = $this->fob->id;
      } 
      if (isset($this->tob) AND $this->view_component_id <= 0) {
        $this->view_component_id = $this->tob->id;
      } 
    }
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "l.view_component_link_id = ".$this->id;
    } elseif ($this->view_id > 0 AND $this->view_component_id > 0) {
      $sql_where = "l.view_id = ".$this->view_id." AND l.view_component_id = ".$this->view_component_id;
    }

    if ($sql_where == '') {
      // because this function is also used to test if a link is already around, this case is fine
    } else{  
      $sql = "SELECT l.view_component_link_id,
                     l.user_id,
                     l.view_id,
                     l.view_component_id,
                     l.order_nbr,
                     l.position_type,
                     l.excluded
                FROM view_component_links l 
               WHERE ".$sql_where.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_dsl = $db_con->get1($sql, $debug-5);  
      if ($db_dsl['view_component_link_id'] > 0) {
        $this->id                = $db_dsl['view_component_link_id'];
        $this->owner_id          = $db_dsl['user_id'];
        $this->view_id           = $db_dsl['view_id'];
        $this->view_component_id = $db_dsl['view_component_id'];
        $this->order_nbr         = $db_dsl['order_nbr'];
        $this->position_type     = $db_dsl['position_type'];
        $this->excluded          = $db_dsl['excluded'];

        // to review: try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
          $sql_set = "UPDATE view_component_links SET user_id = ".$this->usr->id." WHERE view_component_link_id = ".$this->id.";";
          $sql_result = $db_con->exe($sql_set, DBL_SYSLOG_ERROR, "view_component_link->load_standard", (new Exception)->getTraceAsString(), $debug-10);
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } 
    }  
    return $result;
  }
  
  // load the missing view component parameters from the database for the requesting user
  function load($debug) {

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a view component link.", "view_component_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // try to get the search values from the objects
      if ($this->id <= 0 AND ($this->view_id <= 0 OR $this->view_component_id <= 0)) {  
        if (isset($this->fob) AND $this->view_id <= 0) {
          $this->view_id = $this->fob->id;
        } 
        if (isset($this->tob) AND $this->view_component_id <= 0) {
          $this->view_component_id = $this->tob->id;
        } 
      }

      // if it still fails create an error message
      if ($this->id <= 0 AND ($this->view_id <= 0 OR $this->view_component_id <= 0)) {  
        zu_err("The database ID (".$this->id.") or the view (".$this->view_id.") and item id (".$this->view_component_id.") and the user (".$this->usr->id.") must be set to find a display item link.", "view_component_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {

        // set the where clause depending on the values given
        $sql_where = '';
        if ($this->id > 0) {
          $sql_where = "l.view_component_link_id = ".$this->id;
        } elseif ($this->view_id > 0 AND $this->view_component_id > 0) {
          $sql_where = "l.view_id = ".$this->view_id." AND l.view_component_id = ".$this->view_component_id;
        }

        if ($sql_where == '') {
          zu_err("Internal error on the where clause.", "view_component_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
        } else{  
          $sql = "SELECT l.view_component_link_id,
                         u.view_component_link_id AS user_link_id,
                         l.user_id,
                         l.view_id,
                         l.view_component_id,
                         IF(u.order_nbr IS NULL,     l.order_nbr,     u.order_nbr)     AS order_nbr,
                         IF(u.position_type IS NULL, l.position_type, u.position_type) AS position_type,
                         IF(u.excluded IS NULL,      l.excluded,      u.excluded)      AS excluded
                    FROM view_component_links l
               LEFT JOIN user_view_component_links u ON u.view_component_link_id = l.view_component_link_id 
                                                AND u.user_id = ".$this->usr->id." 
                   WHERE ".$sql_where.";";
          $db_con = new mysql;         
          $db_con->usr_id = $this->usr->id;         
          $db_item = $db_con->get1($sql, $debug-5);  
          //if (is_null($db_item['excluded']) OR $db_item['excluded'] == 0) {
          $this->id            = $db_item['view_component_link_id'];
          $this->usr_cfg_id    = $db_item['user_link_id'];
          $this->owner_id      = $db_item['user_id'];
          $this->view_id       = $db_item['view_id'];
          $this->view_component_id = $db_item['view_component_id'];
          $this->order_nbr     = $db_item['order_nbr'];
          $this->pos_type_id   = $db_item['position_type'];
          $this->excluded      = $db_item['excluded'];
          //} 
          zu_debug('view_component_link->load of '.$this->id.' done', $debug-10); 
        }  
      }  
    }  
    zu_debug('view_component_link->load of '.$this->id.' done and quit', $debug-10); 
  }
    
  // to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
  function load_objects($debug) {
    if (!isset($this->fob) AND $this->view_id > 0) {
      $dsp = new view_dsp;
      $dsp->id  = $this->view_id;
      $dsp->usr = $this->usr;
      $dsp->load($debug-1); 
      $this->fob = $dsp;
    }
    if (!isset($this->tob) AND $this->view_component_id > 0) {
      $cmp = new view_dsp;
      $cmp->id  = $this->view_component_id;
      $cmp->usr = $this->usr;
      $cmp->load($debug-1); 
      $this->tob = $cmp;
    }
  }
  
  // return the html code to display the link name
  function name_linked ($back, $debug) {
    $result = '';
    
    $this->load_objects($debug-1);
    if (isset($this->fob) 
    AND isset($this->tob)) {
      $result = $this->fob->name_linked(NULL, $back, $debug-1).' to '.$this->tob->name_linked($back, $debug-1);
    } else {
      $result .= zu_err("The view name or the component name cannot be loaded.", "view_component_link->name", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    
    return $result;    
  }
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
  // NEVER call any methods from this function because this function is used for debugging and a call can cause an endless loop
  function dsp_id ($debug) {
    $result = ''; 

    if (isset($this->fob) AND isset($this->tob)) {
      if ($this->fob->name <> '' AND $this->tob->name <> '') {
        $result .= '"'.$this->tob->name.'" in "'; // e.g. Company details
        $result .= $this->fob->name.'"';     // e.g. cash flow statment 
      }
      if ($this->fob->id <> 0 AND $this->tob->id <> 0) {
        $result .= ' ('.$this->fob->id.','.$this->tob->id;
      }
      // fallback 
      if ($result == '') {
        $result .= $this->fob->dsp_id().' to '.$this->tob->dsp_id(); 
      }
    } else {
      $result .= 'objects not set'; 
    }
    if ($this->id > 0) {
      $result .= ' -> '.$this->id.')';
    } else {  
      $result .= ', but no link id)';
    }  
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }
    return $result;
  }

  // 
  private function pos_type_name($debug) {
    zu_debug('view_component_link->pos_type_name do.', $debug-16);
    if ($this->type_id > 0) {
      $sql = "SELECT type_name, description
                FROM view_component_position_types
               WHERE view_component_position_type_id = ".$this->type_id.";";
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
    $this->load_objects($debug-1);

    // check the all minimal input parameters
    if ($this->id <= 0) {
      zu_err("Cannot load the view component link.", "view_component_link->move", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->view_id <= 0 OR $this->view_component_id <= 0) {
      zu_err("The view component id and the view component id must be given to move it.", "view_component_link->move", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      zu_debug('view_component_link->move '.$direction.' '.$this->dsp_id(), $debug-10);

      // new reorder code that can create a seperate order for each user
      if (!isset($this->fob) OR !isset($this->tob)) {
        zu_err("The view component and the view component cannot be loaded to move them.", "view_component_link->move", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {  
        $this->fob->load_components($debug-1);
        
        // correct any wrong order numbers e.g. a missing number
        $order_number_corrected = false;
        zu_debug('view_component_link->move check order numbers for '.$this->fob->dsp_id(), $debug-10);
        $order_nbr = 0;
        foreach ($this->fob->cmp_lst AS $entry) {
          // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
          $cmp_lnk = new view_component_link;
          $cmp_lnk->fob = $this->fob;
          $cmp_lnk->tob = $entry;
          $cmp_lnk->usr = $this->usr;
          $cmp_lnk->load($debug-1);
          // fix any wrong order numbers
          if ($cmp_lnk->order_nbr != $order_nbr) {
            zu_debug('view_component_link->move check order number of the view component '.$entry->dsp_id().' corrected from '.$cmp_lnk->order_nbr.' to '.$order_nbr.' in '.$this->fob->dsp_id(), $debug-10);
            //zu_err('Order number of the view component "'.$entry->name.'" corrected from '.$cmp_lnk->order_nbr.' to '.$order_nbr.'.', "view_component_link->move", '', (new Exception)->getTraceAsString(), $this->usr);
            $cmp_lnk->order_nbr = $order_nbr;
            $cmp_lnk->save($debug-1);
            $order_number_corrected = true;
          }          
          zu_debug('view_component_link->move check order numbers checked for '.$this->fob->dsp_id().' and '.$entry->dsp_id().' at position '.$order_nbr, $debug-10);
          $order_nbr++;
        }
        if ($order_number_corrected) {
          zu_debug('view_component_link->move reload after correction', $debug-12);
          $this->fob->load_components($debug-1);
          // check if correction was succesful
          $order_nbr = 0;
          foreach ($this->fob->cmp_lst AS $entry) {
            $cmp_lnk = new view_component_link;
            $cmp_lnk->fob = $this->fob;
            $cmp_lnk->tob = $entry;
            $cmp_lnk->usr = $this->usr;
            $cmp_lnk->load($debug-1);
            if ($cmp_lnk->order_nbr != $order_nbr) {
              zu_err('Component link '.$cmp_lnk->dsp_id().' should have position '.$order_nbr.', but is '.$cmp_lnk->order_nbr, "view_component_link->move", '', (new Exception)->getTraceAsString(), $this->usr);
            }
          }
        }
        zu_debug('view_component_link->move order numbers checked for '.$this->fob->dsp_id(), $debug-10);
        
        // actuelly move the selected component
        // TODO what happens if the another user has deleted some components?
        $order_nbr = 0;
        $prev_entry = Null;
        $prev_entry_down = false;
        foreach ($this->fob->cmp_lst AS $entry) {
          // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
          $cmp_lnk = new view_component_link;
          $cmp_lnk->fob = $this->fob;
          $cmp_lnk->tob = $entry;
          $cmp_lnk->usr = $this->usr;
          $cmp_lnk->load($debug-1);
          if ($prev_entry_down) {
            if (isset($prev_entry)) {
              zu_debug('view_component_link->move order number of the view component '.$prev_entry->tob->dsp_id().' changed from '.$prev_entry->order_nbr.' to '.$order_nbr.' in '.$this->fob->dsp_id(), $debug-10);
              $prev_entry->order_nbr = $order_nbr;
              $prev_entry->save($debug-1);
              $prev_entry = Null;
            }  
            zu_debug('view_component_link->move order number of the view component "'.$cmp_lnk->tob->name.'" changed from '.$cmp_lnk->order_nbr.' to '.$order_nbr.' - 1 in "'.$this->fob->name.'"', $debug-10);
            $cmp_lnk->order_nbr = $order_nbr - 1;
            $cmp_lnk->save($debug-1);
            $result = true;
            $prev_entry_down = false;
          }
          if ($entry->id == $this->view_component_id) {
            if ($direction == 'up') {
              if ($cmp_lnk->order_nbr > 0) {
                zu_debug('view_component_link->move order number of the view component '.$cmp_lnk->tob->dsp_id().' changed from '.$cmp_lnk->order_nbr.' to '.$order_nbr.' - 1 in '.$this->fob->dsp_id(), $debug-10);
                $cmp_lnk->order_nbr = $order_nbr - 1;
                $cmp_lnk->save($debug-1);
                $result = true;
                if (isset($prev_entry)) {
                  zu_debug('view_component_link->move order number of the view component '.$prev_entry->tob->dsp_id().' changed from '.$prev_entry->order_nbr.' to '.$order_nbr.' in '.$this->fob->dsp_id(), $debug-10);
                  $prev_entry->order_nbr = $order_nbr;
                  $prev_entry->save($debug-1);
                }
              }
            } else {
              if ($cmp_lnk->order_nbr > 0) {
                $prev_entry = $cmp_lnk;
                $prev_entry_down = true;
              }
            }
          }
          $prev_entry = $cmp_lnk;
          $order_nbr++;
        }
      }
      
      // force to reload view components
      zu_debug('view_component_link->move reload', $debug-12);
      $this->fob->load_components($debug-1);
    }

    zu_debug('view_component_link->move done', $debug-12);
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

  // create a database record to save user specific settings for this view_component_link
  function add_usr_cfg($debug) {
    $result = '';

    if (!$this->has_usr_cfg) {
      if (isset($this->fob) AND isset($this->tob)) {
        zu_debug('view_component_link->add_usr_cfg for "'.$this->fob->name.'"/"'.$this->tob->name.'" by user "'.$this->usr->name.'".', $debug-10);  
      } else {
        zu_debug('view_component_link->add_usr_cfg for "'.$this->id.'" and user "'.$this->usr->name.'".', $debug-10);  
      }

      // check again if there ist not yet a record
      $sql = 'SELECT view_component_link_id 
                FROM `user_view_component_links` 
               WHERE view_component_link_id = '.$this->id.' 
                 AND user_id = '.$this->usr->id.';';
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      if ($db_row['view_component_link_id'] <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_view_component_link';
        $log_id = $db_con->insert(array('view_component_link_id','user_id'), array($this->id,$this->usr->id), $debug-1);
        if ($log_id <= 0) {
          $result .= 'Insert of user_view_component_link failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  function del_usr_cfg_if_not_needed($debug) {
    $result = '';
    zu_debug('view_component_link->del_usr_cfg_if_not_needed pre check for '.$this->dsp_id(), $debug-12);

    //if ($this->has_usr_cfg) {

      // check again if there ist not yet a record
      $sql = 'SELECT view_component_link_id,
                     order_nbr,
                     position_type,
                     excluded
                FROM user_view_component_links
               WHERE view_component_link_id = '.$this->id.' 
                 AND user_id = '.$this->usr->id.';';
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_cfg = $db_con->get1($sql, $debug-5);  
      zu_debug('view_component_link->del_usr_cfg_if_not_needed check for "'.$this->dsp_id().' with ('.$sql.').', $debug-12);
      if ($usr_cfg['view_component_link_id'] > 0) {
        if ($usr_cfg['order_nbr']     == Null
        AND $usr_cfg['position_type'] == Null
        AND $usr_cfg['excluded']      == Null) {
          // delete the entry in the user sandbox
          zu_debug('view_component_link->del_usr_cfg_if_not_needed any more for "'.$this->dsp_id(), $debug-10);
          $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
        }  
      }  
    //}  
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

  // save all updated view_component_link fields excluding the name, because already done when adding a view_component_link
  function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    $result .= $this->save_field_order_nbr ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_type      ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded  ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('view_component_link->save_fields all fields for '.$this->dsp_id().' has been saved', $debug-12);
    return $result;
  }
    
}

?>
