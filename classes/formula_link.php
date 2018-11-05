<?php

/*

  formula_link.php - link a formula to a word
  ----------------
  
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

class formula_link {

  // database fields
  public $id            = NULL; // the database id of the formula link, which is the same for the standard and the user specific link
  public $usr_cfg_id    = NULL; // the database id if there is alrady some user specific configuration for this link otherwise zero
  public $usr           = NULL; // the person who wants to use the formula 
  public $owner_id      = NULL; // the user id of the person who created the link, so if another user wants to change it, a user specific record is created
  public $formula_id    = NULL; // the id of the formula to which the word or triple should be linked
  public $phrase_id     = NULL; // the id of the linked word or triple
  public $excluded      = NULL; // for this object the excluded field is handled as a normal user sandbox field, but for the list excluded row are like deleted
  public $link_type_id  = NULL; // define a special behavier for this link (maybe not needed at the moment)
  public $link_name     = '';   // ???
                               
  // in memory only fields for searching and reference
  public $frm           = NULL; // the formula object (used to save the correct name in the log)
  public $phr           = NULL; // the word object (used to save the correct name in the log) 
  
  // reset the in memory fields used e.g. if some ids are updated
  private function reset_objects($debug) {
    $this->frm = NULL;
    $this->phr = NULL;
  }
  
  // load the formula parameters for all users
  private function load_standard($debug) {
    $result = '';
    
    // try to get the search values from the objects
    if ($this->id <= 0) {  
      if (isset($this->frm) AND $this->formula_id <= 0) {
        $this->formula_id = $this->frm->id;
      } 
      if (isset($this->phr) AND $this->phrase_id <= 0) {
        $this->phrase_id = $this->phr->id;
      } 
    }
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "l.formula_link_id = ".$this->id;
    } elseif ($this->formula_id > 0 AND $this->phrase_id > 0) {
      $sql_where = "l.formula_id = ".$this->formula_id." AND l.phrase_id = ".$this->phrase_id;
    }

    if ($sql_where == '') {
      // because this function is also used to test if a link is already around, this case is fine
    } else{  
      $sql = "SELECT l.formula_link_id,
                     l.user_id,
                     l.formula_id,
                     l.phrase_id,
                     l.link_type_id,
                     l.excluded
                FROM formula_links l 
               WHERE ".$sql_where.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_frm = $db_con->get1($sql, $debug-5);  
      if ($db_frm['formula_link_id'] > 0) {
        $this->id           = $db_frm['formula_link_id'];
        $this->owner_id     = $db_frm['user_id'];
        $this->formula_id   = $db_frm['formula_id'];
        $this->phrase_id    = $db_frm['phrase_id'];
        $this->link_type_id = $db_frm['link_type_id'];
        $this->excluded     = $db_frm['excluded'];

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
  
  // load the missing formula parameters from the database
  function load($debug) {

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a formula link.", "formula_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // try to get the search values from the objects
      if ($this->id <= 0 AND ($this->formula_id <= 0 OR $this->phrase_id <= 0)) {  
        if (isset($this->frm) AND $this->formula_id <= 0) {
          $this->formula_id = $this->frm->id;
        } 
        if (isset($this->phr) AND $this->phrase_id <= 0) {
          $this->phrase_id = $this->phr->id;
        } 
      }

      // if it still fails create an error message
      if ($this->id <= 0 AND ($this->formula_id <= 0 OR $this->phrase_id <= 0)) {  
        zu_err("The database ID (".$this->id.") or the formula (".$this->formula_id.") and word id (".$this->phrase_id.") and the user (".$this->usr->id.") must be set to find a word link.", "formula_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {

        // set the where clause depending on the values given
        $sql_where = '';
        if ($this->id > 0) {
          $sql_where = "l.formula_link_id = ".$this->id;
        } elseif ($this->formula_id > 0 AND $this->phrase_id > 0) {
          $sql_where = "l.formula_id = ".$this->formula_id." AND l.phrase_id = ".$this->phrase_id;
        }

        if ($sql_where == '') {
          zu_err("Internal error on the where clause.", "formula_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
        } else{  
          $sql = "SELECT l.formula_link_id,
                         u.formula_link_id AS user_link_id,
                         l.user_id,
                         l.formula_id, 
                         l.phrase_id,
                         IF(u.link_type_id IS NULL, l.link_type_id, u.link_type_id) AS link_type_id,
                         IF(u.excluded IS NULL,     l.excluded,     u.excluded)     AS excluded
                    FROM formula_link_types t, formula_links l
               LEFT JOIN user_formula_links u ON u.formula_link_id = l.formula_link_id 
                                             AND u.user_id = ".$this->usr->id." 
                   WHERE ".$sql_where.";";
          $db_con = new mysql;         
          $db_con->usr_id = $this->usr->id;         
          $db_row = $db_con->get1($sql, $debug-5);  
          //if (is_null($db_row['excluded']) OR $db_row['excluded'] == 0) {
          $this->id            = $db_row['formula_link_id'];
          $this->usr_cfg_id    = $db_row['user_link_id'];
          $this->owner_id      = $db_row['user_id'];
          $this->formula_id    = $db_row['formula_id'];
          $this->phrase_id     = $db_row['phrase_id'];
          $this->link_type_id  = $db_row['link_type_id'];
          $this->excluded      = $db_row['excluded'];
          //} 
          zu_debug('formula_link->load ('.$this->id.')', $debug-10); 
        }  
      }  
    }  
  }
    
  // to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
  function load_objects($debug) {
    if (!isset($this->frm)) {
      if ($this->formula_id > 0) {
        $frm = New formula;
        $frm->id  = $this->formula_id;
        $frm->usr = $this->usr;
        $frm->load($debug-1);
        $this->frm = $frm;
      }
    }
    if (!isset($this->phr)) {
      if ($this->phrase_id > 0) {
        $phr = new phrase;
        $phr->id  = $this->phrase_id;
        $phr->usr = $this->usr;
        $phr->load($debug-1);
        $this->phr = $phr;
      }
    }
    $this->link_type_name($debug-1);
  }
  
  // 
  function link_type_name($debug) {
    zu_debug('formula_link->link_type_name do.', $debug-16);
    if ($this->type_id > 0 AND $this->link_name == '') {
      $sql = "SELECT type_name, description
                FROM formula_link_types
               WHERE formula_link_type_id = ".$this->type_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_type = $db_con->get1($sql, $debug-5);  
      $this->link_name = $db_type['type_name'];
    }
    zu_debug('formula_link->link_type_name done.', $debug-16);
    return $this->link_name;    
  }
  
  /*
  
  display functions
  
  */
  
  // return the html code to display the link name
  function name_linked ($back, $debug) {
    $result = '';
    
    $this->load_objects($debug-1);
    if (isset($this->frm) 
    AND isset($this->phr)) {
      $result = $this->frm->name_linked($back, $debug-1).' to '.$this->phr->dsp_link($debug-1);
    } else {
      $result .= zu_err("The formula or the linked word cannot be loaded.", "formula_link->name", '', (new Exception)->getTraceAsString(), $this->usr);
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

    if ($this->frm->name <> '' AND $this->phr->name <> '') {
      $result .= $this->frm->name.' '; // e.g. Company details
      $result .= $this->phr->name;     // e.g. cash flow statment 
    }
    $result .= ' ('.$this->frm->id.','.$this->phr->id;
    if ($this->id > 0) {
      $result .= ' -> '.$this->id.')';
    }  
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->name;
    }
    return $result;
  }

  /*
  
  save functions
  
  */
  
  // true if noone has used this formula
  function not_used($debug) {
    zu_debug('formula_link->not_used ('.$this->id.')', $debug-10);  
    $result = true;
    
    // to review: maybe replace by a database foreign key check
    $result = $this->not_changed($debug-1);
    return $result;
  }

  // true if no other user has modified the formula
  private function not_changed($debug) {
    zu_debug('formula_link->not_changed ('.$this->id.') by someone else than the onwer ('.$this->owner_id.').', $debug-10);  
    $result = true;
    
    if ($this->owner_id > 0) {
      $sql = "SELECT user_id 
                FROM user_formula_links 
               WHERE formula_link_id = ".$this->id."
                 AND user_id <> ".$this->owner_id."
                 AND excluded <> 1";
    } else {
      $sql = "SELECT user_id 
                FROM user_formula_links 
               WHERE formula_link_id = ".$this->id."
                 AND excluded <> 1";
    }             
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    if ($db_row['user_id'] > 0) {
      $result = false;
    }
    zu_debug('formula_link->not_changed for '.$this->id.' is '.zu_dsp_bool($result).'.', $debug-10);  
    return $result;
  }

  // true if the user is the owner and noone else has changed the formula_link
  // because if another user has changed the formula_link and the original value is changed, maybe the user formula_link also needs to be updated
  function can_change($debug) {
    if (isset($this->frm) AND isset($this->phr)) {
      zu_debug('formula_link->can_change "'.$this->frm->name.'"/"'.$this->phr->name.'" by user "'.$this->usr->name.'" (id '.$this->usr->id.', owner id '.$this->owner_id.').', $debug-12);  
    } else {
      zu_debug('formula_link->can_change "'.$this->id.'" by user "'.$this->usr->name.'" (id '.$this->usr->id.', owner id '.$this->owner_id.').', $debug-12);  
    }
    $can_change = false;
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $can_change = true;
    }  
    zu_debug('formula_link->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);  
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

  // create a database record to save user specific settings for this formula_link
  private function add_usr_cfg($debug) {
    $result = '';

    if (!$this->has_usr_cfg) {
      if (isset($this->frm) AND isset($this->phr)) {
        zu_debug('formula_link->add_usr_cfg for "'.$this->frm->name.'"/"'.$this->phr->name.'" by user "'.$this->usr->name.'".', $debug-10);  
      } else {
        zu_debug('formula_link->add_usr_cfg for "'.$this->id.'" and user "'.$this->usr->name.'".', $debug-10);  
      }

      // check again if there ist not yet a record
      $sql = "SELECT formula_link_id 
                FROM user_formula_links 
               WHERE formula_link_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      if ($db_row['formula_link_id'] <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_formula_link';
        $log_id = $db_con->insert(array('formula_link_id','user_id'), array($this->id,$this->usr->id), $debug-1);
        if ($log_id <= 0) {
          $result .= 'Insert of user_formula_link failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  private function del_usr_cfg_if_not_needed($debug) {
    $result = '';
    zu_debug('formula_link->del_usr_cfg_if_not_needed pre check for "'.$this->name.' und user '.$this->usr->name.'.', $debug-12);

    //if ($this->has_usr_cfg) {

      // check again if there ist not yet a record
      $sql = "SELECT formula_link_id,
                     link_type_id,
                     excluded
                FROM user_formula_links
               WHERE formula_link_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      zu_debug('formula_link->del_usr_cfg_if_not_needed check for '.$this->name.' und user '.$this->usr->name.' with ('.$sql.').', $debug-12);
      if ($db_row['formula_link_id'] > 0) {
        if ($db_row['link_type_id'] == Null
        AND $db_row['excluded']     == Null) {
          // delete the entry in the user sandbox
          zu_debug('formula_link->del_usr_cfg_if_not_needed any more for '.$this->name.' und user '.$this->usr->name.'.', $debug-10);
          $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
        }  
      }  
    //}  
    return $result;
  }
  
  // simply remove a user adjustment without check
  private function del_usr_cfg_exe($db_con, $debug) {
    $result = '';

    $db_con->type = 'user_formula_link';         
    $result .= $db_con->delete(array('formula_link_id','user_id'), array($this->id,$this->usr->id), $debug-1);
    if (str_replace('1','',$result) <> '') {
      $result .= 'Deletion of user formula phrase assign '.$this->id.' failed for '.$this->usr->name.'.';
    }
    
    return $result;
  }
  
  // remove user adjustment and log it (used by user.php to undo the user changes)
  function del_usr_cfg($debug) {
    $result = '';

    if ($this->id > 0 AND $this->usr->id > 0) {
      zu_debug('formula_link->del_usr_cfg  "'.$this->id.' und user '.$this->usr->name.'.', $debug-12);

      $db_type = 'user_formula_link';
      $log = $this->log_del($debug-1);
      if ($log->id > 0) {
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  

    } else {
      zu_err("The formula_link database ID and the user must be set to remove a user specific modification.", "formula_link->del_usr_cfg", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // set the log entry parameter for a new value
  // e.g. that the user can see "added formula list to phrase view"
  private function log_add($debug) {
    zu_debug('formula_link->log_add for "'.$this->frm->name.'"/"'.$this->phr->name.'" by user "'.$this->usr->name.'".', $debug-10);  
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'formula_links';
    $log->new_from  = $this->frm;
    $log->new_to    = $this->phr;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one display phrase link field
  // e.g. that the user can see "moved formula list to position 3 in phrase view"
  private function log_upd($debug) {
    // zu_debug('formula_link->log_upd "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table   = 'formula_links';
    } else {  
      $log->table   = 'user_formula_links';
    }
    
    return $log;    
  }
  
  // set the log entry parameter to delete a formula
  // e.g. that the user can see "removed formula list from word view"
  private function log_del($debug) {
    zu_debug('formula_link->log_del for "'.$this->frm->name.'"/"'.$this->phr->name.'" by user "'.$this->usr->name.'".', $debug-10);  
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = 'formula_links';
    $log->old_from  = $this->frm;
    $log->old_to    = $this->phr;
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one display word link field
  // e.g. that the user can see "moved formula list to position 3 in word view"
  private function log_upd_field($debug) {
    // zu_debug('formula_link->log_upd_field "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table   = 'formula_links';
    } else {  
      $log->table   = 'user_formula_links';
    }
    
    return $log;    
  }
  
  // actually update a formula field in the main database record or the user sandbox
  private function save_field_do($db_con, $log, $debug) {
    $result = '';
    zu_debug('formula_link->save_field_do .', $debug-16);
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
        $db_con->type = 'user_formula_link';
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-1);
      }
    }
    zu_debug('formula_link->save_field_do done.', $debug-16);
    return $result;
  }
  
  // set the update parameters for the word type
  private function save_field_type($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->link_type_id <> $this->link_type_id) {
      $log = $this->log_upd_field($debug-1);
      $log->old_value = $db_rec->link_type_name($debug-1);
      $log->old_id    = $db_rec->link_type_id;
      $log->new_value = $this->link_type_name($debug-1);
      $log->new_id    = $this->link_type_id; 
      $log->std_value = $std_rec->link_type_name($debug-1);
      $log->std_id    = $std_rec->link_type_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'link_type_id';
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
        $db_con->type = 'user_formula_link';
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-30);
      }
    }
    return $result;
  }
  
  // save all updated formula_link fields excluding the name, because already done when adding a formula_link
  private function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    // link type not used at the moment
    //$result .= $this->save_field_type     ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('formula_link->save_fields all fields for "'.$this->frm->name.'" to "'.$this->phr->name.'" has been saved.', $debug-12);
    return $result;
  }
  
  // save updated the word_link id fields (frm and phr)
  // should only be called if the user is the owner and nobody has used the display component link
  private function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->frm->id <> $this->frm->id 
     OR $db_rec->phr->id <> $this->phr->id) {
      zu_debug('formula_link->save_id_fields to "'.$this->dsp_id().'" from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().').', $debug-10);
      $log = $this->log_upd($debug-1);
      $log->old_from = $db_rec->frm;
      $log->new_from = $this->frm;
      $log->std_from = $std_rec->frm;
      $log->old_to = $db_rec->phr;
      $log->new_to = $this->phr;
      $log->std_to = $std_rec->phr;
      $log->row_id   = $this->id; 
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("formula_id",        "phrase_id"),
                                              array($this->frm->id,$this->phr->id), $debug-1);
      }
    }
    zu_debug('formula_link->save_id_fields for "'.$this->name.'" has been done.', $debug-12);
    return $result;
  }
  
  // check if the id parameters are supposed to be changed 
  private function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    
    if ($db_rec->frm->id <> $this->frm->id 
     OR $db_rec->phr->id <> $this->phr->id) {
      $this->reset_objects($debug-1);
      // check if target link already exists
      zu_debug('formula_link->save_id_if_updated check if target link already exists "'.$this->dsp_id().'" (has been "'.$db_rec->dsp_id().'").', $debug-14);
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
        zu_debug('formula_link->save_id_if_updated found a formula link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add "'.$this->dsp_id().'".', $debug-14);
      } else {
        if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
          // in this case change is allowed and done
          zu_debug('formula_link->save_id_if_updated change the existing formula link "'.$this->dsp_id().'" (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'").', $debug-14);
          $this->load_objects($debug-1);
          $result .= $this->save_id_fields($db_con, $db_rec, $std_rec, $debug-20);
        } else {
          // if the target link has not yet been created
          // ... request to delete the old
          $to_del = clone $db_rec;
          $result .= $to_del->del($debug-20);        
          // .. and create a deletion request for all users ???
          
          // ... and create a new formula link
          $this->id = 0;
          $this->owner_id = $this->usr->id;
          $result .= $this->add($db_con, $debug-20);
          zu_debug('formula_link->save_id_if_updated recreate the formula link del "'.$db_rec->dsp_id().'" add "'.$this->dsp_id().'" (standard "'.$std_rec->dsp_id().'").', $debug-14);
        }
      }
    }  

    zu_debug('formula_link->save_id_fields for "'.$this->name.'" has been done.', $debug-12);
    return $result;
  }
  
  // link the formula to another word
  private function add($db_con, $debug) {
    zu_debug('formula_link->add new link from "'.$this->frm->name.'" to "'.$this->phr->name.'".', $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the new formula_link
      $this->id = $db_con->insert(array("formula_id","phrase_id","user_id"), array($this->frm->id,$this->phr->id,$this->usr->id), $debug-1);
      if ($this->id > 0) {
        // update the id in the log
        $result .= $log->add_ref($this->id, $debug-1);

        // create an empty db_rec element to force saving of all set fields
        $db_rec = New formula_link;
        $db_rec->frm = $this->frm;
        $db_rec->phr = $this->phr;
        $db_rec->usr = $this->usr;
        $std_rec = clone $db_rec;
        // save the formula_link fields
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);

      } else {
        zu_err("Adding formula_link ".$this->id." failed.", "formula_link->save");
      }
    }  
    
    return $result;
  }
  
  // update a formula_link in the database or create a user formula_link
  function save($debug) {
    // check if the required parameters are set
    if (isset($this->frm) AND isset($this->phr)) {
      zu_debug('formula_link->save "'.$this->frm->name.'" to "'.$this->phr->name.'" (id '.$this->id.') for user '.$this->usr->name.'.', $debug-10);
    } elseif ($this->id > 0) {
      zu_debug('formula_link->save id '.$this->id.' for user '.$this->usr->name.'.', $debug-10);
    } else {
      zu_err("Either the formula and the word or the id must be set to link a formula to a word.", "formula_link->save", '', (new Exception)->getTraceAsString(), $this->usr);
    }
    $result = "";
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'formula_link';         
    
    // check if a new value is supposed to be added
    if ($this->id <= 0) {
      zu_debug('formula_link->save check if a new formula_link for "'.$this->frm->name.'" and "'.$this->phr->name.'" needs to be created.', $debug-12);
      // check if a formula_link with the same formula and word is already in the database
      $db_chk = New formula_link;
      $db_chk->frm = $this->frm;
      $db_chk->phr = $this->phr;
      $db_chk->usr = $this->usr;
      $db_chk->load_standard($debug-1);
      if ($db_chk->id > 0) {
        $this->id = $db_chk->id;
      }
    }  
      
    if ($this->id <= 0) {
      zu_debug('formula_link->save new link from "'.$this->frm->name.'" to "'.$this->phr->name.'".', $debug-12);
      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug('formula_link->save update "'.$this->id.'".', $debug-12);
      // read the database values to be able to check if something has been changed; done first, 
      // because it needs to be done for user and general formulas
      $db_rec = New formula_link;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-1);
      $db_rec->load_objects($debug-1);
      zu_debug("formula_link->save -> database formula loaded (".$db_rec->id.")", $debug-14);
      $std_rec = New formula_link;
      $std_rec->id = $this->id;
      $std_rec->load_standard($debug-1);
      zu_debug("formula_link->save -> standard formula settings loaded (".$std_rec->id.")", $debug-14);
      
      // for a correct user formula link detection (function can_change) set the owner even if the formula link has not been loaded before the save 
      if ($this->owner_id <= 0) {
        $this->owner_id = $std_rec->owner_id;
      }
      
      // it should not be possible to change the formula or the word, but nevertheless check
      // instead of changing the formula or the word, a new link should be created and the old deleted
      if ($db_rec->frm->id <> $this->frm->id 
       OR $db_rec->phr->id <> $this->phr->id) {
        zu_debug("formula_link->save -> update link settings for id ".$this->id.": change formula ".$db_rec->formula_id." to ".$this->frm->id." and ".$db_rec->phrase_id." to ".$this->phr->id, $debug-14);
        $result .= zu_info('The formula link "'.$db_rec->frm->name.'" with "'.$db_rec->phr->name.'" (id '.$db_rec->frm->id.','.$db_rec->phr->id.') " cannot be changed to "'.$this->frm->name.'" with "'.$this->phr->name.'" (id '.$this->frm->id.','.$this->phr->id.'). Instead the program should have created a new link.', "formula_link->save");
      }  

      // check if the id parameters are supposed to be changed 
      $this->load_objects($debug-1);
      $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec, $debug-1);

      // if a problem has appeared up to here, don't try to save the values
      // the problem is shown to the user by the calling interactive script
      if (str_replace ('1','',$result) == '') {
        // update the order or link type
        zu_debug("formula_link->save -> update fields (".$db_rec->id.")", $debug-14);
        $result .= $this->save_fields ($db_con, $db_rec, $std_rec, $debug-1);        
      }
    }  
    
    return $result;    
  }

  // delete the complete formula_link (the calling function del must have checked that no one uses this link)
  private function del_exe($debug) {
    zu_debug('value->del_exe.', $debug-16);
    $result = '';

    $log = $this->log_del($debug-1);
    if ($log->id > 0) {
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      // delete first all user configuration that have also been excluded
      $db_con->type = 'user_formula_link';
      $result .= $db_con->delete(array('formula_link_id','excluded'), array($this->id,'1'), $debug-1);
      $db_con->type   = 'formula_link';         
      $result .= $db_con->delete('formula_link_id', $this->id, $debug-1);
    }
    
    return $result;    
  }
  
  // exclude or delete a formula_link
  function del($debug) {
    zu_debug('formula_link->del.', $debug-16);
    $result = '';
    $result .= $this->load($debug-1);
    if ($this->id > 0 AND $result == '') {
      zu_debug('formula_link->del "'.$this->id.'".', $debug-14);
      if ($this->can_change($debug-30) AND $this->not_used($debug-1)) {
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
