<?php

/*

  ref.php - a link between a phrase and another system such as wikidata
  -------
  
  TODO add to UI; add unit tests
  
  
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

class ref {

  // database fields
  public $id           = NULL; // the database id of the reference
  public $phr_id       = NULL; // the database id of the word, verb or formula
  public $external_key = '';   // the unique key in the external system
  public $ref_type_id  = NULL; // the id of the ref type

  // in memory only fields
  public $usr      = NULL;     // just needed for logging the changes
  public $phr      = NULL;     // the phrase object
  public $ref_type = NULL;     // the ref type object
  
  function reset($debug) {
    $this->id            = NULL;
    $this->phr_id        = NULL;
    $this->external_key  = '';
    $this->ref_type_id   = NULL;
                        
    $this->usr           = NULL; 
    $this->phr           = NULL; 
    $this->ref_type      = NULL; 
  }

  // test if the name is used already
  public function load ($debug) {
    zu_debug('ref->load ('.$this->dsp_id().')', $debug-10);
    $result = NULL;

    // check if the minimal input parameters are set
    if ($this->id <= 0 AND ($this->phr_id <= 0 OR $this->ref_type_id <= 0)) {  
      zu_err('Either the database ID ('.$this->id.') or the phrase id ('.$this->phr_id.') AND the reference type id ('.$this->ref_type_id.') must be set to load a reference.', 'ref->load', '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
    
      // set the where clause depending on the values given
      $sql_where = '';
      if ($this->id > 0) {
        $sql_where = 'ref_id = '.$this->id;
      } elseif ($this->phr_id > 0 AND $this->ref_type_id > 0) {
        $sql_where = 'phrase_id = '.sf($this->phr_id).' AND ref_type_id = '.$this->ref_type_id;
      }

      if ($sql_where == '') {
        zu_err('Internal error on the where clause.', 'ref->load', '', (new Exception)->getTraceAsString(), $this->usr);
      } else {  
        $sql = 'SELECT ref_id,
                       phrase_id,
                       external_key,
                       ref_type_id
                  FROM refs 
                 WHERE '.$sql_where.';';
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $db_ref = $db_con->get1($sql, $debug-5);  
        if ($db_ref['ref_id'] <= 0) {
          $this->reset($debug-1);
        } else {
          $this->id           = $db_ref['ref_id'];
          $this->phr_id       = $db_ref['phrase_id'];
          $this->external_key = $db_ref['external_key'];
          $this->ref_type_id  = $db_ref['ref_type_id'];
          $this->load_objects($debug-1);
        } 
      }  
    }  
    zu_debug('ref->load -> done '.$this->dsp_id(), $debug-16);
    
    return $result;    
  }

  // to load the related objects if the reference object is loaded
  private function load_objects($debug) {
    zu_debug('ref->load_objects', $debug-10);

    if (!isset($this->phr)) {
      if ($this->phr_id <> 0) {
        $phr = New phrase;
        $phr->id  = $this->phr_id;
        $phr->usr = $this->usr;
        $phr->load($debug-1);
        $this->phr = $phr;
        zu_debug('ref->load_objects -> phrase '.$this->phr->dsp_id().' loaded', $debug-14);
      }
    }
    if (!isset($this->ref_type)) {
      if ($this->ref_type_id > 0) {
        $this->ref_type = get_ref_type($this->ref_type_id, $debug-1);
        zu_debug('ref->load_objects -> ref_type '.$this->ref_type->dsp_id().' loaded', $debug-14);
      }
    }

    zu_debug('ref->load_objects -> done', $debug-12);
  }
  
  // import a link to external database from a imported object
  function import_obj ($json_obj, $debug) {
    zu_debug('ref->import_obj', $debug-10);
    $result = '';
    
    foreach ($json_obj AS $key => $value) {
      if ($key == 'name') { $this->external_key = $value; }
      if ($key == 'type') { 
        $this->ref_type = get_ref_type_by_name($value, $debug-1); 
        if (!isset($this->ref_type)) {
          zu_err('Reference type for '.$value.' not found', 'ref->import_obj', '', (new Exception)->getTraceAsString(), $this->usr);
        } else {
          $this->ref_type_id = $this->ref_type->id; 
        }
        zu_debug('ref->import_obj -> ref_type set based on '.$value.' ('.$this->ref_type_id.')', $debug-14);
      }
    }
    if ($result == '') {
      $this->load_objects($debug-1); // to be able to log the object names
      $this->save($debug-1);
      zu_debug('ref->import_obj -> '.$this->dsp_id(), $debug-18);
    } else {
      zu_debug('ref->import_obj -> '.$result, $debug-18);
    }

    return $result;
  }
  
  /*
  
  display functions
  
  */
  
  // create the unique name
  function name ($debug) {
    $result = ''; 

    if (isset($this->phr)) {
      $result .= 'ref of "'.$this->phr->name.'"'; 
    } else {  
      if (isset($this->phr_id)) {
        if ($this->phr_id > 0) {
          $result .= 'ref of phrase id '.$this->phr_id.' '; 
        }
      }
    }
    if (isset($this->ref_type)) {
      $result .= 'to "'.$this->ref_type->name.'"'; 
    } else {  
      if (isset($this->ref_type_id)) {
        if ($this->ref_type_id > 0) {
          $result .= 'to type id '.$this->ref_type_id.' '; 
        }
      }
    }
    return $result;
  }

  // display the unique id fields
  function dsp_id ($debug) {
    $result = ''; 

    $result .= $this->name(); 
    if ($result <> '') {
      if ($this->id > 0) {
        $result .= ' ('.$this->id.')';
      }  
    } else {
      $result .= $this->id;
    }
    return $result;
  }

  // set the log entry parameter for a new reference
  function log_add($debug) {
    zu_debug('ref->log_add '.$this->dsp_id(), $debug-10);
    
    // check that the minimal parameters are set
    if (!isset($this->phr)) {
      zu_err('The phrase object must be set to log adding an external reference.', 'ref->log_add', '', (new Exception)->getTraceAsString(), $this->usr);
    }
    if (!isset($this->ref_type)) {
      zu_err('The reference type object must be set to log adding an external reference.', 'ref->log_add', '', (new Exception)->getTraceAsString(), $this->usr);
    }
    
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'refs';
    // TODO review in log_link
    // TODO object must be loaded before it can be logged
    $log->new_from  = $this->phr;
    $log->new_link  = $this->ref_type;
    $log->new_to    = $this;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one reference field
  function log_upd($db_rec, $debug) {
    zu_debug('ref->log_upd '.$this->dsp_id(), $debug-10);
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    $log->table     = 'refs';
    $log->old_from  = $db_rec->phr;
    $log->old_link  = $db_rec->ref_type;
    $log->old_to    = $db_rec;
    $log->new_from  = $this->phr;
    $log->new_link  = $this->ref_type;
    $log->new_to    = $this;
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the log entry parameter to delete a reference
  function log_del($debug) {
    zu_debug('ref->log_del '.$this->dsp_id(), $debug-10);
    
    // check that the minimal parameters are set
    if (!isset($this->phr)) {
      zu_err('The phrase object must be set to log deletion of an external reference.', 'ref->log_del', '', (new Exception)->getTraceAsString(), $this->usr);
    }
    if (!isset($this->ref_type)) {
      zu_err('The reference type object must be set to log deletion of an external reference.', 'ref->log_del', '', (new Exception)->getTraceAsString(), $this->usr);
    }
    
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = 'refs';
    $log->old_from  = $this->phr;
    $log->old_link  = $this->ref_type;
    $log->old_to    = $this;
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // update a ref in the database or update the existing
  private function add($debug) {
    zu_debug('ref->add '.$this->dsp_id(), $debug-10);
    $result = '';

    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      zu_debug('ref->add -> insert', $debug-10);
      // insert the new reference
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_con->type = 'ref';         
      $this->id = $db_con->insert(array('phrase_id','external_key','ref_type_id'),
                                  array($this->phr_id,$this->external_key,$this->ref_type_id), $debug-1);
      if ($this->id > 0) {
        // update the id in the log for the correct reference
        $result .= $log->add_ref($this->id, $debug-1);
      } else {
        zu_err('Adding reference '.$this->dsp_id().' failed.', 'ref->add');
      }  
    }
    
    return $result;    
  }

  function get_similar($debug) {
    $result = NULL;
    zu_debug('ref->get_similar '.$this->dsp_id(), $debug-10);

    $db_chk = clone $this;
    $db_chk->reset();
    $db_chk->phr_id      = $this->phr_id;
    $db_chk->ref_type_id = $this->ref_type_id;
    $db_chk->usr         = $this->usr;
    $db_chk->load($debug-1); 
    if ($db_chk->id > 0) {
      zu_debug('ref->get_similar an external reference for '.$this->dsp_id().' already exists', $debug-12);
      $result = $db_chk;
    }

    return $result;
  }
  
  // update a ref in the database or update the existing
  function save($debug) {
    zu_debug('ref->save '.$this->dsp_id(), $debug-10);
    $result = '';

    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->id;         
    $db_con->type   = 'ref';         
    
    // check if the external reference is supposed to be added
    if ($this->id <= 0) {
      // check possible dublicates before adding
      zu_debug('ref->save check possible dublicates before adding '.$this->dsp_id(), $debug-12);
      $similar = $this->get_similar($debug-1);
      if (isset($similar)) {
        if ($similar->id <> 0) {
          $this->id = $similar->id;
        }
      }
    }  
      
    // create a new object or update an existing
    if ($this->id <= 0) {
      zu_debug('ref->save add', $debug-12);
      $result .= $this->add($debug-1);
    } else {  
      zu_debug('ref->save update', $debug-12);
      
      // read the database values to be able to check if something has been changed; 
      // done first, because it needs to be done for user and general object values
      $db_rec = clone $this;
      $db_rec->reset();
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-10);
      zu_debug('ref->save reloaded from db', $debug-14);

      // if needed log the change and update the database
      if ($this->external_key <> $db_rec->external_key) {
        $log = $this->log_upd($db_rec, $debug-1);
        if ($log->id > 0) {
          $result .= $db_con->update($this->id, 'external_key', $this->external_key, $debug-1);
          zu_debug('ref->save update ... done.'.$result.'', $debug-10);
        }
      }
    }
    return $result;    
  }
  
  function del($debug) {
    $result = '';
    zu_debug('ref->del '.$this->dsp_id(), $debug-10);
    
    $result .= $this->load($debug-18);
    if ($result <> '') {
      zu_warning('Reload of ref '.$this->dsp_id().' for deletion unexpectedly lead to '.$result.'.', 'ref->del');
    } else {
      if ($this->id <= 0) {
        zu_warning('Delete failed, because it seems that the ref '.$this->dsp_id().' has been deleted in the meantime.', 'ref->del');
      } else {  
        $log = $this->log_del($debug-1);
        if ($log->id > 0) {
          $result .= $db_con->delete('ref_id', $this->id, $debug-1);
          zu_debug('ref->del update -> done.'.$result.'', $debug-12);
        }
      }
    }
    
    return $result;    
  }
}

?>
