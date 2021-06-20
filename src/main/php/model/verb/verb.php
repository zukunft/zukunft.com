<?php

/*

  verb.php - predicate object to link two words
  --------
  
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

class verb {

  public $id           = NULL;  // the database id of the word link type (verb)
  public $usr          = NULL;  // not used at the moment, because there should not be any user specific verbs
                                // otherwise if id is 0 (not NULL) the standard word link type, otherwise the user specific verb
  public $code_id      = '';    // the main id to detect verbs that have a special behavior
  public $name         = '';    // the verb name to build the "sentence" for the user, which cannot be empty
  public $formula_name = '';    // short name of the verb for the use in formulas 
  public $plural       = '';    // name used if more than one word is shown
                                // e.g. instead of "ABB" "is a" "company" 
                                //          use    "ABB", Nestlé" "are" "companies" 
  public $reverse      = '';    // name used if displayed the other way round 
                                // e.g. for "Country" "has a" "Human Development Index" 
                                //      the reverse would be "Human Development Index" "is used for" "Country"
  public $rev_plural   = '';    // the reverse name for many words
  public $description  = '';    // for the mouse over explain
  public $frm_name     = '';    // the name use in formulas, because there both sides are combined 
  
  // load the missing verb parameters from the database
  function load($debug) {

    global $db_con;
    $result = '';
    
    // set the where clause depending on the values given
    if ($this->code_id > 0) {
      $sql_where = "v.code_id = ".$this->code_id;
    }  
    elseif ($this->id > 0) {
      $sql_where = "v.verb_id = ".$this->id;
    }  
    elseif ($this->name <> '') {
      $sql_where = '( v.verb_name = '.sf($this->name).' OR v.formula_name = '.sf($this->name).')';
    }

    if ($sql_where == '') {
      log_err("Either the database ID or the verb name must be set for loading.", "verb->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      log_debug('verb->load by ('.$sql_where.')', $debug-14);
      // similar statement used in word_link_list->load, check if changes should be repeated in word_link_list.php
      $sql = "SELECT v.verb_id,
                     v.code_id,
                     v.verb_name,
                     v.formula_name,
                     v.name_plural,
                     v.name_reverse,
                     v.name_plural_reverse,
                     v.formula_name,
                     v.description
                FROM verbs v 
              WHERE ".$sql_where.";";
      //$db_con = new mysql;
      if (!isset($this->usr)) {
        log_err("User is missing", "verb->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        $db_con->usr_id = $this->usr->id;
      }
      $db_lnk = $db_con->get1($sql, $debug-5);  
      if ($db_lnk['verb_id'] > 0) {
        $this->id           = $db_lnk['verb_id'];
        $this->code_id      = $db_lnk['code_id'];
        $this->name         = $db_lnk['verb_name'];
        $this->formula_name = $db_lnk['formula_name'];
        $this->plural       = $db_lnk['name_plural'];
        $this->reverse      = $db_lnk['name_reverse'];
        $this->rev_plural   = $db_lnk['name_plural_reverse'];
        $this->frm_name     = $db_lnk['formula_name'];
        $this->description  = $db_lnk['description'];
      } else {
        $this->id           = 0;
      }
      log_debug('verb->load ('.$this->dsp_id().')', $debug-12);
    }  
    return $result;
  }
    
  /*
  
  display functions
  
  */
  
  // display the unique id fields (used also for debugging)
  function dsp_id () {
    $result = ''; 

    if ($this->name <> '') {
      $result .= '"'.$this->name.'"'; 
      if ($this->id > 0) {
        $result .= ' ('.$this->id.')';
      }  
    } else {
      $result .= $this->id;
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }
    return $result;
  }

  function name ($debug) {
    return $this->name;    
  }

  // create the HTML code to display the formula name with the HTML link
  function display ($back) {
    $result = '<a href="/http/verb_edit.php?id='.$this->id.'&back='.$back.'">'.$this->name.'</a>';
    return $result;    
  }

  // returns the html code to select a word link type
  // database link must be open
  function dsp_selector ($side, $form, $class, $back, $debug) {
    log_debug('verb->dsp_selector -> for verb id '.$this->id, $debug-10);
    $result = '';
    
    if ($side == 'forward') {
      $sql = "SELECT * FROM (
              SELECT verb_id AS id, 
                    IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                    words
                FROM verbs ) AS links
            ORDER BY words DESC, name;";
    } else {
    $sql = "SELECT * FROM (
            SELECT verb_id AS id, 
                   IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
    }      
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form;
    $sel->name       = 'verb';  
    $sel->label      = "Verb:";  
    $sel->bs_class   = $class;  
    $sel->sql        = $sql;
    $sel->selected   = $this->id;
    $sel->dummy_text = '';
    $result .= $sel->display ($debug-1);
    log_debug('verb->dsp_selector -> select sql '.$sql, $debug-16);

    log_debug('verb->dsp_selector -> admin id '.$this->id, $debug-10);
    if (isset($this->usr)) {
      if ($this->usr->is_admin ($debug-1)) {
        // admin users should always have the possibility to create a new verb / link type
        $result .= btn_add ('add new verb', '/http/verb_add.php?back='.$back);
      }
    }

    log_debug('verb->dsp_selector -> done verb id '.$this->id, $debug-10);
    return $result;
  }

  // show the html form to add or edit a new verb 
  function dsp_edit ($back, $debug) {
    log_debug('verb->dsp_edit '.$this->dsp_id(), $debug-10);
    $result = '';
    
    if ($this->id <= 0) {
      $script = "verb_add";
      $result .= dsp_text_h2('Add verb (word link type)');
      $result .= dsp_form_start($script);
    } else {  
      $script = "verb_edit";
      $result .= dsp_text_h2('Change verb (word link type)');
      $result .= dsp_form_start($script);
    }  
    $result .= dsp_tbl_start_half();
    $result .= '  <tr>';
    $result .= '    <td>';
    $result .= '      verb name:';
    $result .= '    </td>';
    $result .= '    <td>';
    $result .= '      <input type="text" name="name" value="'.$this->name.'">';
    $result .= '    </td>';
    $result .= '  </tr>';
    $result .= '  <tr>';
    $result .= '    <td>';
    $result .= '      verb plural:';
    $result .= '    </td>';
    $result .= '    <td>';
    $result .= '      <input type="text" name="plural" value="'.$this->plural.'">';
    $result .= '    </td>';
    $result .= '  </tr>';
    $result .= '  <tr>';
    $result .= '    <td>';
    $result .= '      reverse:';
    $result .= '    </td>';
    $result .= '    <td>';
    $result .= '      <input type="text" name="reverse" value="'.$this->reverse.'">';
    $result .= '    </td>';
    $result .= '  </tr>';
    $result .= '  <tr>';
    $result .= '    <td>';
    $result .= '      plural_reverse:';
    $result .= '    </td>';
    $result .= '    <td>';
    $result .= '      <input type="text" name="plural_reverse" value="'.$this->rev_plural.'">';
    $result .= '    </td>';
    $result .= '  </tr>';
    $result .= '  <input type="hidden" name="back" value="'.$back.'">';
    $result .= '  <input type="hidden" name="confirm" value="1">';
    $result .= dsp_tbl_end();
    $result .= dsp_form_end();

    log_debug('verb->dsp_edit ... done', $debug-10);
    return $result;
  }

  // get the term corresponding to this verb name
  // so in this case, if a word or formula with the same name already exists, get it
  private function term($debug) {
    $trm = New term;
    $trm->name = $this->name;
    $trm->usr  = $this->usr;
    $trm->load($debug-1);
    return $trm;    
  }

  /*
  
  save functions
  
  */

  // true if no one has used this verb
  private function not_used($debug) {
    log_debug('verb->not_used ('.$this->id.')', $debug-10);

    global $db_con;
    $result = true;
    
    // to review: additional check the database foreign keys
    $sql = "SELECT words 
              FROM verbs 
             WHERE verb_id = ".$this->id.";";
    //$db_con = new mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    $used_by_words = $db_row['words'];
    if ($used_by_words > 0) {
      $result = false;
    }
    
    return $result;
  }

  // true if no other user has modified the verb
  private function not_changed($debug) {
    log_debug('verb->not_changed ('.$this->id.') by someone else than the owner ('.$this->owner_id.')', $debug-10);

    global $db_con;
    $result = true;
    
    /*
    $change_user_id = 0;
    $sql = "SELECT user_id 
              FROM user_verbs 
             WHERE verb_id = ".$this->id."
               AND user_id <> ".$this->owner_id."
               AND (excluded <> 1 OR excluded is NULL)";
    //$db_con = new mysql;
    $db_con->usr_id = $this->usr->id;         
    $change_user_id = $db_con->get1($sql, $debug-5);  
    if ($change_user_id > 0) {
      $result = false;
    }
    */
    
    log_debug('verb->not_changed for '.$this->id.' is '.zu_dsp_bool($result), $debug-10);
    return $result;
  }

  // true if no one else has used the verb
  function can_change($debug) {
    log_debug('verb->can_change '.$this->id, $debug-10);
    $can_change = false;
    if ($this->not_used AND $this->not_changed) {
      $can_change = true;
    }  

    log_debug('verb->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);
    return $can_change;
  }

  // set the log entry parameter for a new verb
  private function log_add($debug) {
    log_debug('verb->log_add '.$this->dsp_id(), $debug-10);
    $log = New user_log;
    $log->usr       = $this->usr;
    $log->action    = 'add';
    $log->table     = 'verbs';
    $log->field     = 'verb_name';
    $log->old_value = '';
    $log->new_value = $this->name;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one verb field
  private function log_upd($debug) {
    log_debug('verb->log_upd '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr    = $this->usr;
    $log->action = 'update';
    $log->table  = 'verbs';
    
    return $log;    
  }
  
  // set the log entry parameter to delete a verb
  private function log_del($debug) {
    log_debug('verb->log_del '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr       = $this->usr;
    $log->action    = 'del';
    $log->table     = 'verbs';
    $log->field     = 'verb_name';
    $log->old_value = $this->name;
    $log->new_value = '';
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // actually update a formula field in the main database record or the user sandbox
  private function save_field_do($db_con, $log, $debug) {
    $result = '';
    if ($log->new_id > 0) {
      $new_value = $log->new_id;
      $std_value = $log->std_id;
    } else {
      $new_value = $log->new_value;
      $std_value = $log->std_value;
    }  
    if ($log->add($debug-1)) {
      if ($this->can_change($debug-1)) {
        $db_con->set_type(DB_TYPE_VERB);
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        // todo: create a new verb and request to delete the old
      }
    }
    return $result;
  }
  
  // set the update parameters for the verb name
  private function save_field_name($db_con, $db_rec, $debug) {
    $result = '';
    if ($db_rec->name <> $this->name) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->name;
      $log->new_value = $this->name;
      $log->std_value = $db_rec->name;
      $log->row_id    = $this->id; 
      $log->field     = 'verb_name';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the verb plural
  private function save_field_plural($db_con, $db_rec, $debug) {
    $result = '';
    if ($db_rec->plural <> $this->plural) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->plural;
      $log->new_value = $this->plural;
      $log->std_value = $db_rec->plural;
      $log->row_id    = $this->id; 
      $log->field     = 'name_plural';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the verb reverse
  private function save_field_reverse($db_con, $db_rec, $debug) {
    $result = '';
    if ($db_rec->reverse <> $this->reverse) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->reverse;
      $log->new_value = $this->reverse;
      $log->std_value = $db_rec->reverse;
      $log->row_id    = $this->id; 
      $log->field     = 'name_reverse';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the verb rev_plural
  private function save_field_rev_plural($db_con, $db_rec, $debug) {
    $result = '';
    if ($db_rec->rev_plural <> $this->rev_plural) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->rev_plural;
      $log->new_value = $this->rev_plural;
      $log->std_value = $db_rec->rev_plural;
      $log->row_id    = $this->id; 
      $log->field     = 'name_plural_reverse';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the verb description
  private function save_field_description($db_con, $db_rec, $debug) {
    $result = '';
    if ($db_rec->description <> $this->description) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->description;
      $log->new_value = $this->description;
      $log->std_value = $db_rec->description;
      $log->row_id    = $this->id; 
      $log->field     = 'description';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // save all updated verb fields excluding the name, because already done when adding a verb
  private function save_fields($db_con, $db_rec, $debug) {
    $result = '';
    $result .= $this->save_field_plural      ($db_con, $db_rec, $debug-1);
    $result .= $this->save_field_reverse     ($db_con, $db_rec, $debug-1);
    $result .= $this->save_field_rev_plural  ($db_con, $db_rec, $debug-1);
    $result .= $this->save_field_description ($db_con, $db_rec, $debug-1);
    log_debug('verb->save_fields all fields for '.$this->dsp_id().' has been saved', $debug-12);
    return $result;
  }
  
  // check if the id parameters are supposed to be changed 
  private function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
/*  
    todo:
    if ($db_rec->name <> $this->name) {
      // check if target link already exists
      zu_debug('verb->save_id_if_updated check if target link already exists '.$this->dsp_id().' (has been "'.$db_rec->dsp_id().'")', $debug-14);
      $db_chk = clone $this;
      $db_chk->id = 0; // to force the load by the id fields
      $db_chk->load_standard($debug-10);
      if ($db_chk->id > 0) {
        if (UI_CAN_CHANGE_VIEW_COMPONENT_NAME) {
          // ... if yes request to delete or exclude the record with the id parameters before the change
          $to_del = clone $db_rec;
          $result .= $to_del->del($debug-20);        
          // .. and use it for the update
          $this->id = $db_chk->id;
          $this->owner_id = $db_chk->owner_id;
          // force the include again
          $this->excluded = Null;
          $db_rec->excluded = '1';
          $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-20);
          zu_debug('verb->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add '.$this->dsp_id(), $debug-14);
        } else {
          $result .= 'A view component with the name "'.$this->name.'" already exists. Please use another name.';
        }  
      } else {
        if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
          // in this case change is allowed and done
          zu_debug('verb->save_id_if_updated change the existing display component link '.$this->dsp_id().' (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'")', $debug-14);
          //$this->load_objects($debug-1);
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
          zu_debug('verb->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add '.$this->dsp_id().' (standard "'.$std_rec->dsp_id().'")', $debug-14);
        }
      }
    }  
*/
    log_debug('verb->save_id_if_updated for '.$this->dsp_id().' has been done', $debug-12);
    return $result;
  }
  
  // create a new verb
  private function add($db_con, $debug) {
    log_debug('verb->add the verb '.$this->dsp_id(), $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the new verb
      $db_con->set_type(DB_TYPE_VERB);
      $this->id = $db_con->insert("verb_name", $this->name, $debug-1);
      if ($this->id > 0) {
        // update the id in the log
        $result .= $log->add_ref($this->id, $debug-1);

        // create an empty db_rec element to force saving of all set fields
        $db_rec = New verb;
        $db_rec->name = $this->name;
        $db_rec->usr  = $this->usr;
        // save the verb fields
        $result .= $this->save_fields($db_con, $db_rec, $debug-1);

      } else {
        log_err("Adding verb ".$this->name." failed.", "verb->save");
      }
    }  
        
    return $result;
  }
  
  // add or update a verb in the database (or create a user verb if the program settings allow this)
  function save($debug) {
    log_debug('verb->save '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);

    global $db_con;
    $result = '';
    
    // build the database object because the is anyway needed
    $db_con->set_usr($this->usr->id);
    $db_con->set_type(DB_TYPE_VERB);
    
    // check if a new word is supposed to be added
    if ($this->id <= 0) {
      // check if a word, formula or verb with the same name is already in the database
      $trm = $this->term($debug-1);      
      if ($trm->id > 0 AND $trm->type <> 'verb') {
        $result .= $trm->id_used_msg();
      } else {
        $this->id = $trm->id;
        log_debug('verb->save adding verb name '.$this->dsp_id().' is OK', $debug-14);
      }
    }  
      
    // create a new verb or update an existing
    if ($this->id <= 0) {
      $result .= $this->add($db_con, $debug-1);
    } else {  
      log_debug('verb->save update "'.$this->id.'"', $debug-12);
      // read the database values to be able to check if something has been changed; done first, 
      // because it needs to be done for user and general formulas
      $db_rec = New verb;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-1);
      log_debug("verb->save -> database verb loaded (".$db_rec->name.")", $debug-14);
      
      // if the name has changed, check if verb, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
      if ($db_rec->name <> $this->name) {
        // check if a verb, formula or verb with the same name is already in the database
        $trm = $this->term($debug-1);      
        if ($trm->id > 0 AND $trm->type <> 'verb') {
          $result .= $trm->id_used_msg();
        } else {
          if ($this->can_change($debug-1)) {
            $result .= $this->save_field_name        ($db_con, $db_rec, $debug-1);
          } else {  
            // todo: create a new verb and request to delete the old
          }
        }  
      }  

      // if a problem has appeared up to here, don't try to save the values
      // the problem is shown to the user by the calling interactive script
      if (str_replace ('1','',$result) == '') {
        $result .= $this->save_fields     ($db_con, $db_rec, $debug-1);        
      }
    }  
    
    return $result;    
  }

  // exclude or delete a verb
  function del($debug) {
    log_debug('verb->del', $debug-16);

    global $db_con;
    $result = '';

    $result .= $this->load($debug-1);
    if ($this->id > 0 AND $result == '') {
      log_debug('verb->del '.$this->dsp_id(), $debug-14);
      if ($this->can_change($debug-1)) {
        $log = $this->log_del($debug-1);
        if ($log->id > 0) {
          //$db_con = new mysql;
          $db_con->usr_id = $this->usr->id;         
          $db_con->set_type(DB_TYPE_VERB);
          $result .= $db_con->delete('verb_id', $this->id, $debug-1);
        }
      } else {
        // todo: create a new verb and request to delete the old
      }  
    }
    return $result;    
  }
  
}

?>
