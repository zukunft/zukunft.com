<?php

/*

  value.php - the main number object
  ---------
  
  Common object for the tables values and user_values
  in the database the object is save in two tables 
  because it is expected that there will be much less user values than standard values
  
  To Do: what happens if a user (not the value owner) is adding a word to the value
  
  if the value is not used at all the adding of the new word is logged and the group change is updated without logging
  if the value is used, adding, changing or deleting a word creates a new value or updates an existing value 
     and the logging is done according new value (add all words) or existing value (value modified by the user)
  
  
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

class value extends user_sandbox_display {

  // database fields additional to the user sandbox fields for the value object
  public $number        = NULL; // simply the numeric value
  public $source_id     = NULL; // the id of source where the value is comming from
  public $grp_id        = NULL; // id of the group of pharses that are linked to this value for fast selections
  public $time_id       = NULL; // id of the main time period word for fast time seres creation selections
  public $time_stamp    = NULL; // the time stamp for this value (if this is set, the time wrd is supposed to be empty)
  public $last_update   = NULL; // the time of the last update of fields that may influence the calculated results

  // derived database fields for fast selection (needs to be verified from time to time to check the database consistency and detect program errors)
  // field set by the front end scripts such as value_add.php or value_edit.php
  public $ids           = NULL; // list of the word or triple ids (if > 0 id of a word if < 0 id of a triple)
  public $phr_lst       = NULL; // the phrase object list for this value
  //public $phr_ids       = NULL; // the phrase id list for this value loaded directly from the group
  public $wrd_lst       = NULL; // the word object list for this value
  public $wrd_ids       = NULL; // the word id list for this value loaded directly from the group
  public $lnk_lst       = NULL; // the triple object list  for this value
  public $lnk_ids       = NULL; // the triple id list  for this value loaded directly from the group
  // public $phr_all_lst  = NULL; // $phr_lst including the time wrd
  // public $phr_all_ids  = NULL; // $phr_ids including the time id
  public $grp           = NULL; // phares (word or triple) group object for this value
  public $time_phr      = NULL; // the time (period) word object for this value
  public $update_time   = NULL; // time of the last update, which could also be taken from the change log
  public $source        = NULL; // the source object

  // field for user interaction
  public $usr_value     = '';    // the raw value as the user has entered it including formatting chars such as the thousand seperator
  
  
  function __construct() {
    $this->type      = 'value';
    $this->obj_name  = 'value';

    $this->rename_can_switch = UI_CAN_CHANGE_VALUE;
  } 
  
  function reset($debug) {
    $this->id            = NULL;
    $this->usr_cfg_id    = NULL;
    $this->usr           = NULL;
    $this->owner_id      = NULL;
    $this->excluded      = NULL;
                        
    $this->number        = NULL; 
    $this->source_id     = NULL; 
    $this->grp_id        = NULL; 
    $this->time_id       = NULL; 
    $this->time_stamp    = NULL; 
    $this->last_update   = NULL; 
                        
    $this->ids           = NULL; 
    $this->phr_lst       = NULL; 
    $this->wrd_lst       = NULL; 
    $this->wrd_ids       = NULL; 
    $this->lnk_lst       = NULL; 
    $this->lnk_ids       = NULL; 
    $this->grp           = NULL; 
    $this->time_phr      = NULL; 
    $this->update_time   = NULL; 
    $this->source        = NULL; 
    $this->share_id      = NULL; 
    $this->protection_id = NULL; 

    $this->usr_value     = '';   
  }


  /*
  
  database load functions that reads the object from the database
  
  */
  
  // load the standard value use by most users
  function load_standard($debug) {
    if ($this->id > 0) {
      $sql = "SELECT v.value_id,
                     v.user_id,
                     v.word_value,
                     v.source_id,
                     v.last_update,
                     v.excluded,
                     v.protection_type_id
                FROM `values` v 
               WHERE v.value_id = ".$this->id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_val = $db_con->get1($sql, $debug-5);  
      if ($db_val['value_id'] <= 0) {
        $this->reset($debug-1);
      } else {
        $this->id            = $db_val['value_id'];
        $this->owner_id      = $db_val['user_id'];
        $this->number        = $db_val['word_value'];
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
          $sql_set = "UPDATE `values` SET user_id = ".$this->usr->id." WHERE value_id = ".$this->id.";";
          $sql_result = $db_con->exe($sql_set, DBL_SYSLOG_ERROR, "value->load_standard", (new Exception)->getTraceAsString(), $debug-10);
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } 
    }  
  }
  
  // load the record from the database
  // in a seperate function, because this can be called twice from the load function
  function load_rec($sql_where, $debug) {
    $sql = 'SELECT v.value_id,
                    u.value_id AS user_value_id,
                    v.user_id,
                    v.phrase_group_id,
                    v.time_word_id,
                    u.share_type_id,
                    IF(u.user_value IS NULL,         v.word_value,         u.user_value)         AS word_value,
                    IF(u.source_id IS NULL,          v.source_id,          u.source_id)          AS source_id,
                    IF(u.last_update IS NULL,        v.last_update,        u.last_update)        AS last_update,
                    IF(u.excluded IS NULL,           v.excluded,           u.excluded)           AS excluded,
                    IF(u.protection_type_id IS NULL, v.protection_type_id, u.protection_type_id) AS protection_type_id
              FROM `values` v 
          LEFT JOIN user_values u ON u.value_id = v.value_id 
                                AND u.user_id = '.$this->usr->id.' 
              WHERE '.$sql_where.';';
    zu_debug('value->load -> sql "'.$sql.'".', $debug-18);      
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_val = $db_con->get1($sql, $debug-5);  
    if ($db_val['value_id'] <= 0) {
      $this->reset($debug-1);
    } else {
      $this->id            = $db_val['value_id'];
      $this->usr_cfg_id    = $db_val['user_value_id'];
      $this->owner_id      = $db_val['user_id'];
      $this->number        = $db_val['word_value'];
      $this->source_id     = $db_val['source_id'];
      $this->share_id      = $db_val['share_type_id'];
      $this->protection_id = $db_val['protection_type_id'];
      $this->grp_id        = $db_val['phrase_group_id'];
      $this->time_id       = $db_val['time_word_id'];
      $this->last_update   = new DateTime($db_val['last_update']);
      $this->excluded      = $db_val['excluded'];
      zu_debug('value->load -> got id '.$this->id, $debug-14);      
    } 
  }
  
  // load the missing value parameters from the database
  function load($debug) {

    // check the all minimal input parameters
    if (!isset($this->usr)) {
      zu_err('The user id must be set to load a result.', 'value->load', '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      zu_debug('value->load.', $debug-18);      

      $sql_where = '';
      if ($this->id > 0) {
        $sql_where = 'v.value_id = '.$this->id;
      } elseif ($this->grp_id > 0) {
        $sql_where = 'v.phrase_group_id = '.$this->grp_id;
        if ($this->time_id > 0) {
          $sql_where  .= ' AND v.time_word_id = '.$this->time_id.' ';
        }
      } elseif ( !empty($this->ids) ) {
        $result .= $this->set_grp_and_time_by_ids($debug-1);
        if ($this->grp_id > 0) {
          $sql_where = 'v.phrase_group_id = '.$this->grp_id;
          if ($this->time_id > 0) {
            $sql_where  .= ' AND v.time_word_id = '.$this->time_id.' ';
          }
        }
      } else {
        // if no value for a word group is found it is not an error, this is why here the error message is not at the same point as in other load methods
        zu_err('Either the database ID ('.$this->id.'), the word group ('.$this->grp_id.') or the word list ('.implode(",",$this->ids).') and the user ('.$this->usr->id.') must be set to load a value.', 'value->load', '', (new Exception)->getTraceAsString(), $this->usr);
      }

      // check if a valid indentification is given and load the result
      if ($sql_where <> '') {
        zu_debug('value->load -> by "'.$sql_where.'".', $debug-16);      
        $this->load_rec($sql_where, $debug);
        
        // if not direct value is found try to get a more specific value
        // similar to formula_value
        if ($this->id <= 0 and isset($this->phr_lst)) {
          zu_debug('value->load try best guess.', $debug-10);
          $phr_lst = clone $this->phr_lst;
          if ($this->time_id <= 0) {
            $time_phr = $this->phr_lst->time_useful($debug-1);
            $this->time_id = $time_phr->id;
          }
          $phr_lst->ex_time($debug-1); 
          if (count($phr_lst->lst) > 0) {
            // the phrase groups with the least number of additional words that have at least one formula value
            $sql_grp_from = '';
            $sql_grp_where = '';
            $pos = 1;
            foreach ($phr_lst->lst AS $phr) {
              if ($sql_grp_from <> '') { $sql_grp_from .= ','; }
              $sql_grp_from .= 'phrase_group_word_links l'.$pos;
              $pos_prior = $pos - 1;
              if ($sql_grp_where <> '') { $sql_grp_where .= ' AND l'.$pos_prior.'.phrase_group_id = l'.$pos.'.phrase_group_id AND '; }
              $sql_grp_where .= ' l'.$pos.'.word_id = '.$phr->id;
              $pos++;
            }
            $sql_grp = 'SELECT l1.phrase_group_id 
                          FROM '.$sql_grp_from.' 
                         WHERE '.$sql_grp_where;
            // todo:
            // count the number of phrases per group
            // and add the user specific phrase links
            // select also the time
            $sql_time = '';
            if ($this->time_id > 0) {
              $sql_time = ' AND time_word_id = '.$this->time_id.' ';
            }
            $sql_val = "SELECT value_id 
                          FROM `values`
                         WHERE phrase_group_id IN (".$sql_grp.") ".$sql_time.";";
            zu_debug('value->load sql val "'.$sql_val.'".', $debug-12);
            $db_con = new mysql;         
            $db_con->usr_id = $this->usr->id;         
            $val_ids_rows = $db_con->get($sql_val, $debug-5);  
            if (count($val_ids_rows) > 0) {
              $val_id_row = $val_ids_rows[0];
              $this->id = $val_id_row['value_id'];
              if ($this->id > 0) {
                $sql_where = "v.value_id = ".$this->id;
                $this->load_rec($sql_where, $debug);
                zu_debug('value->loaded best gues id ('.$this->id.').', $debug-10);
              }
            }
          } 
        }
      }
    }
  }
  
  // get the best matching value
  // 1. try to find a value with simply a different scaling e.g. if the number of share are requested, but this is in millions in the database use and scale it
  // 2. check if another measure type can be converted      e.g. if the share price in USD is requested, but only in EUR is in the database convert it
  // e.g. for "ABB","Sales","2014" the value for "ABB","Sales","2014","million","CHF" will be loaded,
  //      because most values for "ABB", "Sales" are in ,"million","CHF"
  function load_best($debug) {
    zu_debug('value->load_best for '.$this->dsp_id(), $debug-10);
    $this->load($debug-10);
    // if not found try without scaling
    if ($this->id <= 0) {
      $this->load_phrases($debug-10);
      if (!isset($this->phr_lst)) {
        zu_err('No phrases found for '.$this->dsp_id().'.', 'value->load_best', '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        // try to get a value with another scaling 
        $phr_lst_unscaled = clone $this->phr_lst;
        $phr_lst_unscaled->ex_scaling($debug-10);
        zu_debug('value->load_best try unscaled with '.$phr_lst_unscaled->dsp_id(), $debug-10);
        $grp_unscale = $phr_lst_unscaled->get_grp($debug-1);
        $this->grp_id = $grp_unscale->id;
        $this->load($debug-1);
        // if not found try with coverted measure
        if ($this->id <= 0) {
          // try to get a value with another measure 
          $phr_lst_converted = clone $phr_lst_unscaled;
          $phr_lst_converted->ex_measure($debug-10);
          zu_debug('value->load_best try converted with '.$phr_lst_converted->dsp_id(), $debug-10);
          $grp_unscale = $phr_lst_converted->get_grp($debug-1);
          $this->grp_id = $grp_unscale->id;
          $this->load($debug-1);
          // todo:
          // check if there are any matching values at all
          // if yes, get the most often used phrase
          // repeat adding a phrase until a number is found
        }
      }
    }
    zu_debug('value->load_best got '.$this->number.' for '.$this->dsp_id(), $debug-12);
  }
  
  /*
  
  load object functions that extends the database load functions
  
  */
  
  // load the phrase objects for this value if needed
  // not included in load, because sometimes loading of the word objects is not needed
  // maybe rename to load_objects
  // NEVER call the dsp_id function from this function or any called function, because this would lead to an endless loop
  function load_phrases($debug) {
    zu_debug('value->load_phrases', $debug-18);
    // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
    if ($this->grp_id > 0) {
      $this->load_grp_by_id($debug-1);
    }
    zu_debug('value->load_phrases load time', $debug-18);
    $this->load_time_phrase($debug-1);
    zu_debug('value->load_phrases -> done ('.(new Exception)->getTraceAsString().').', $debug-16);
  }
  
  // load the source object
  // what happens if a source is updated
  function load_source($debug) {
    $src = Null;
    zu_debug('value->load_source for '.$this->dsp_id($debug-1), $debug-10);
    
    $do_load = false;
    if (isset($this->source)) {
      if ($this->source_id == $this->source->id) {
        $src = $this->source;
      } else {
        $do_load = true;
      }
    } else {
      $do_load = true;
    }
    if ($do_load) {
      if ($this->source_id > 0) {
        $src = New source;
        $src->id  = $this->source_id;
        $src->usr = $this->usr;
        $src->load($debug-1); 
        $this->source = $src;
      } else {
        $this->source = Null;
      }
    }
    
    if (isset($src)) {
      zu_debug('value->load_source -> '.$src->dsp_id($debug-1), $debug-10);
    } else {
      zu_debug('value->load_source done', $debug-10);
    }
    return $src;
  }
  
  // rebuild the word and triple list based on the group id
  function load_grp_by_id($debug) {
    // if the group object is missing
    if (!isset($this->grp)) {
      if ($this->grp_id > 0) {
        // ... load the group related objects means the word and triple list
        $grp = New phrase_group;
        $grp->id  = $this->grp_id;
        $grp->usr = $this->usr; // in case the word names and word links can be user specific maybe the owner should be used here
        $grp->get($debug-1); 
        $grp->load_lst($debug-1); // to make sure that the word and triple object lists are loaded
        if ($grp->id > 0) {
          $this->grp = $grp;
        }  
      }
    }  

    // if a list object is missing
    if (!isset($this->wrd_lst) OR !isset($this->lnk_lst)) {
      if (isset($this->grp)) {
        $this->set_lst_by_grp($debug-1);
        
        // these if's are only needed for debuging to avoid accessing an unset object, which would cause a crash
        if (isset($this->phr_lst)) {
          zu_debug('value->load_grp_by_id got '.$this->phr_lst->name($debug-1).' from group '.$this->grp_id.' for "'.$this->usr->name.'".', $debug-12);
        }  
        if (isset($this->wrd_lst)) {
          if (isset($this->lnk_lst)) {
            zu_debug('value->load_grp_by_id -> both.', $debug-16);
            zu_debug('value->load_grp_by_id with words '.$this->wrd_lst->name($debug-1).' .', $debug-12);
            zu_debug('value->load_grp_by_id with words '.$this->wrd_lst->name($debug-1).' and triples '.$this->lnk_lst->name($debug-1).' .', $debug-12);
            zu_debug('value->load_grp_by_id with words '.$this->wrd_lst->name($debug-1).' and triples '.$this->lnk_lst->name($debug-1).' by group '.$this->grp_id.' for "'.$this->usr->name.'".', $debug-12);
          } else {  
            zu_debug('value->load_grp_by_id with words '.$this->wrd_lst->name($debug-1).' by group '.$this->grp_id.' for "'.$this->usr->name.'".', $debug-12);
          }  
        } else {  
          zu_debug('value->load_grp_by_id '.$this->grp_id.' for "'.$this->usr->name.'".', $debug-12);
        }
      }
    }  
    zu_debug('value->load_grp_by_id -> done.', $debug-16);
  }

  // set the list objects based on the loaded phrase group
  // function to set depending objects based on loaded objects
  function set_lst_by_grp($debug) {
    if (isset($this->grp)) {
      $this->grp_id  = $this->grp->id;
      if (!isset($this->phr_lst)) { $this->phr_lst = $this->grp->phr_lst; }
      if (!isset($this->wrd_lst)) { $this->wrd_lst = $this->grp->wrd_lst; }
      if (!isset($this->lnk_lst)) { $this->lnk_lst = $this->grp->lnk_lst; }
      $this->ids     = $this->grp->ids;
    }  
  }
  
  // just load the time word object based on the id loaded from the database
  function load_time_phrase($debug) {
    zu_debug('value->load_time_phrase', $debug-1);
    $do_load = false;
    
    if (isset($this->time_phr)) {
      if ($this->time_phr->id <> $this->time_id) {
        $do_load = true;
      }
    } else {
      $do_load = true;
    }
    if ($do_load) {
      if ($this->time_id <> 0) {
        zu_debug('value->load_time_phrase -> load', $debug-1);
        $time_phr = new phrase;
        $time_phr->id  = $this->time_id;
        $time_phr->usr = $this->usr; 
        $time_phr->load($debug-1);
        $this->time_phr = $time_phr;
        zu_debug('value->load_time_phrase -> got '.$time_phr->dsp_id($debug-1), $debug-1);
      } else {
        $this->time_phr = null;
      }
    }
    zu_debug('value->load_time_phrase done', $debug-1);
  }
  
  // load the source and return the source name
  function source_name($debug) {
    $result = '';
    zu_debug('value->source_name', $debug-10);
    zu_debug('value->source_name for '.$this->dsp_id($debug-1), $debug-10);

    if ($this->source_id > 0) {
      $this->load_source($debug-1);
      if (isset($this->source)) {
        $result = $this->source->name;
      }  
    }
    return $result;    
  }
  
  /*
  
  load object functions that extends the frontend functions
  
  */
  
  // 
  function set_grp_and_time_by_ids($debug) {
    // 1. load the phrases parameters based on the ids
    $result .= $this->set_phr_lst_by_ids($debug-1);
    // 2. extract the time from the phrase list
    $result .= $this->set_time_by_phr_lst($debug-10);
    // 3. get the group based on the phrase list
    $result .= $this->set_grp_by_ids($debug-1);
    zu_debug('value->set_grp_and_time_by_ids "'.implode(",",$this->ids).'" to "'.$this->grp_id.'" and '.$this->time_id, $debug-16);
  }
  
  // rebuild the phrase list based on the phrase ids
  function set_phr_lst_by_ids($debug) {
    zu_debug('value->set_phr_lst_by_ids for ids "'.implode(",",$this->ids).'" for "'.$this->usr->name.'".', $debug-16);
    $result = '';
    if (!isset($this->phr_lst)) {
      if (!empty($this->ids)) {
        $phr_lst = New phrase_list;
        $phr_lst->ids = $this->ids;
        $phr_lst->usr = $this->usr;
        $phr_lst->load($debug-1);
        $this->phr_lst = $phr_lst;
      }
    }  
    return $result;     
  }

  // get the time based on the phrase id list
  function set_time_by_phr_lst($debug) {
    $result = '';
    if (isset($this->phr_lst)) {
      zu_debug('value->set_time_by_phr_lst from '.$this->phr_lst->name(), $debug-16);
      if (!isset($this->time_id)) {
        if (isset($this->time_phr)) {
          $this->time_id = $this->time_phr->id;
        } else {  
          $wrd_lst = $this->phr_lst->wrd_lst_all($debug-1);
          $this->time_phr = $wrd_lst->assume_time($debug-1);
          $this->time_id = $this->time_phr->id;
          zu_debug('value->set_time_by_phr_lst got '.$this->time_phr->name.' for user '.$this->time_phr->usr->name, $debug-14);
        }  
      }
    }  
    return $result;     
  }
  
  // rebuild the word and triple list based on the word and triple ids
  // add set the time_id if needed
  function set_grp_by_ids($debug) {
    zu_debug('value->set_grp_by_ids for ids "'.implode(",",$this->ids).'" for "'.$this->usr->name.'".', $debug-16);
    if (!isset($this->grp)) {
      if (!empty($this->ids)) {
        $grp = New phrase_group;
        $grp->ids = $this->ids;
        $grp->usr = $this->usr; // in case the word names and word links can be user specific maybe the owner should be used here
        $grp->get($debug-1);
        if ($grp->id > 0) {
          $this->grp    = $grp;
          $this->grp_id = $grp->id;
          /* actuallay not needed
          $this->set_lst_by_grp($debug-1);
          if (isset($this->wrd_lst)) {
            zu_debug('value->set_grp_by_ids -> got '.$this->wrd_lst->name().' for '.implode(',',$this->ids).'', $debug-12);
          }
          */
        }  
      }
    }  
    zu_debug('value->set_grp_by_ids -> group set to id '.$this->grp_id, $debug-16);
  }

  // exclude the time period word from the phrase list
  function set_phr_lst_ex_time($debug) {
    zu_debug('value->set_phr_lst_ex_time for "'.$this->phr_lst->name($debug-1).'" for "'.$this->usr->name.'".', $debug-16);
    $this->phr_lst->ex_time($debug);
  }

  /*
  
  
  */
  
  // to be dismissed
  // set the word list object for this value if needed
  // to be dismissed, but used by value_list->html at the moment
  function load_wrd_lst($debug) {
    zu_debug('value->load_wrd_lst.', $debug-12);
    if ($this->wrd_lst == NUll) {
      // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
      if ($this->grp_id > 0) {
        zu_debug('value->load_wrd_lst by group id.', $debug-14);
        $this->load_grp_by_id($debug-1);
        zu_debug('value->load_wrd_lst by group id done.', $debug-14);
      } else {
        // when adding new values only the word IDs are known
        zu_debug('value->load_wrd_lst by ids.', $debug-14);
        if (isset($this->ids)) {
          zu_debug('value->load_wrd_lst by ids do.', $debug-14);
          $this->set_grp_by_ids($debug-1);
          zu_debug('value->load_wrd_lst '.$this->wrd_lst->name($debug-1).'" by ids '.$this->ids.' for "'.$this->usr->name.'".', $debug-10);
        } else {
          if ($this->id > 0) {
            // rebuild word ids based on the link table
            $sql = "SELECT phrase_id FROM value_phrase_links WHERE value_id = ".$this->id." GROUP BY phrase_id;";
            $db_con = new mysql;         
            $db_con->usr_id = $this->usr->id;         
            $wrd_lnk_lst = $db_con->get($sql, $debug-5); 
            $wrd_ids = array();
            foreach ($wrd_lnk_lst AS $wrd_lnk) {
              $wrd_ids[] = $wrd_lnk['phrase_id'];
            }
            // todo: add the triple links
            $this->ids = $wrd_ids;
            $this->set_grp_by_ids($debug-1);
          } else {
            zu_err("Missing value id","value->load_wrd_lst");
          }
        }
      }
    }
    zu_debug('value->load_wrd_lst -> done (trace '.(new Exception)->getTraceAsString().').', $debug-14);
  }

  // to be dismissed
  // a list of all word links related to a given value with the id of the linked word
  // used by value_edit.php 
  function wrd_link_lst($debug) {
    zu_debug("value->wrd_link_lst (".$this->id." and user ".$this->usr->name.")", $debug-10);
    $result = array();

    if ($this->id > 0) {
      $sql = "SELECT l.value_phrase_link_id,
                    t.word_id
                FROM value_phrase_links l
          LEFT JOIN words t      ON l.phrase_id = t.word_id  
          LEFT JOIN user_words u ON t.phrase_id = u.word_id AND u.user_id  = ".$this->usr->id."  
              WHERE l.value_id = ".$this->id." 
            GROUP BY t.word_id
            ORDER BY t.values, t.word_name;";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_lst = $db_con->get($sql, $debug-5);  
      foreach ($db_lst AS $db_row) {
        $id = $db_row['value_phrase_link_id'];
        $result[$id] = $db_row['word_id'];
      }
    } else {
      zu_err("Missing value id","value->wrd_link_lst");
    }

    return $result;
  }

  /*
  
  consistency check functions
  
  */
  
  // check the data consistency of this user value
  // e.g. update the value_phrase_links database table based on the group id
  function check($debug) {

    // reload the value to include all changes
    zu_debug('value->check id '.$this->id.', for user '.$this->usr->name, $debug-10);
    $this->load($debug-1);
    zu_debug('value->check load pharses.', $debug-10);
    $this->load_phrases($debug-1);
    zu_debug('value->check pharses loaded.', $debug-10);
    
    // remove dublicate entries in value phrase link table
    $result .= $this->upd_phr_links($debug-1);
  
    zu_debug('value->check done.', $debug-18);
    return $changes; 
  }
  
  // scale a value for the target words
  // e.g. if the target words contains "millions" "2'100'000" is converted to "2.1"
  //      if the target words are empty convert "2.1 mio" to "2'100'000"
  // once this is working switch on the call in word_list->value_scaled
  function scale ($target_wrd_lst, $debug) {
    zu_debug('value->scale '.$this->number, $debug-12);
    // fallback value
    $result = $this->number;
    
    $this->load_phrases($debug-1);
    
    // check input parameters
    if (is_null($this->number)) {
      // this test should be done in the calling function if needed
      // zu_info("To scale a value the number should not be empty.", "value->scale", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif (is_null($this->usr->id)) {
      zu_warning("To scale a value the user must be defined.", "value->scale", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif (is_null($this->wrd_lst)) {
      zu_warning("To scale a value the word list should be loaded by the calling method.", "value->scale", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {  
      zu_debug('value->scale '.$this->number.' for '.$this->wrd_lst->name($debug-1).' (user '.$this->usr->id.')', $debug-10);

      // if it has a scaling word, scale it to one
      if ($this->wrd_lst->has_scaling($debug-1)) {
        zu_debug('value->scale value words have a scaling words.', $debug-14);
        // get any scaling words related to the value
        $scale_wrd_lst = $this->wrd_lst->scaling_lst($debug-1);
        if (count($scale_wrd_lst->lst) > 1) {
          zu_warning('Only one scale word can be taken into account in the current version, but not a list like '.$this->scale_wrd_lst->name($debug-1).'.', "value->scale", '', (new Exception)->getTraceAsString(), $this->usr);
        } else {
          if (count($scale_wrd_lst->lst) == 1) {
            $scale_wrd = $scale_wrd_lst->lst[0]; 
            zu_debug('value->scale -> word ('.$scale_wrd->name.')', $debug-10);
            if ($scale_wrd->id > 0) {
              $frm = $scale_wrd->formula($debug-1);
              $frm->usr = $this->usr; // temp solution until the bug of not setting is found
              if (!isset($frm)) {
                zu_warning('No scaling formula defined for "'.$scale_wrd->name.'".', "value->scale", '', (new Exception)->getTraceAsString(), $this->usr);
              } else {
                $formula_text = $frm->ref_text;
                zu_debug('value->scale -> scaling formula "'.$frm->name.'" ('.$frm->id.'): '.$formula_text, $debug-12);
                if ($formula_text <> "") {
                  $l_part = zu_str_left_of($formula_text, ZUP_CHAR_CALC);
                  $r_part = zu_str_right_of($formula_text, ZUP_CHAR_CALC);
                  $exp = New expression;
                  $exp->ref_text = $frm->ref_text;
                  $exp->usr      = $this->usr;
                  $fv_phr_lst = $exp->fv_phr_lst($debug-1);
                  $phr_lst = $exp->phr_lst($debug-1);
                  if (isset($fv_phr_lst) AND isset($phr_lst)) { 
                    $fv_wrd_lst = $fv_phr_lst->wrd_lst_all($debug-1);
                    $wrd_lst = $phr_lst->wrd_lst_all($debug-1);
                    if (count($fv_wrd_lst->lst) == 1 AND count($wrd_lst->lst) == 1) {
                      $fv_wrd = $fv_wrd_lst->lst[0];
                      $r_wrd  = $wrd_lst->lst[0];
                      
                      // test if it is a valid scale formula
                      if ($fv_wrd->is_type(SQL_WORD_TYPE_SCALING_HIDDEN, $debug-1) 
                      AND $r_wrd->is_type(SQL_WORD_TYPE_SCALING, $debug-1) ) {
                        $wrd_symbol = ZUP_CHAR_WORD_START.$r_wrd->id.ZUP_CHAR_WORD_END;
                        zu_debug('value->scale -> replace ('.$wrd_symbol.' in '.$r_part.' with '.$this->number.')', $debug-1);
                        $r_part = str_replace($wrd_symbol,$this->number,$r_part);
                        zu_debug('value->scale -> replace done ('.$r_part.')', $debug-1);
                        $result = zuc_math_parse($r_part, $value_words, Null, $debug-1);
                      } else {
                        zu_err ('Formula "'.$formula_text.'" seems to be not a valid scaling formula, because the words are not defined as scaling words.');
                      }
                    } else {
                      zu_err ('Formula "'.$formula_text.'" seems to be not a valid scaling formula, because only one word should be on both sides of the equation.');
                    }
                  } 
                }
              }
            }
          }
        }
      }

      // todo: scale the number to the target scaling
      // if no target scaling is defined leave the scaling at one
      //if ($target_wrd_lst->has_scaling($debug-1)) {
      //}
      
    }
    return $result;
  }
  
  // create an object for the export
  function export_obj ($debug) {
    zu_debug('value->export_obj', $debug-10);
    $result = Null;

    // reload the value parameters
    $this->load($debug-10);
    zu_debug('value->export_obj load phrases', $debug-18);
    $this->load_phrases($debug-10);

    // add the words
    zu_debug('value->export_obj get words', $debug-18);
    $wrd_lst = array();
    foreach ($this->wrd_lst->lst AS $wrd) {
      $wrd_lst[] = $wrd->name();
    }
    if (count($wrd_lst) > 0) {
      $result->words = $wrd_lst;
    }

    // add the triples
    $triples_lst = array();
    foreach ($this->lnk_lst->lst AS $lnk) {
      $triples_lst[] = $lnk->name();
    }
    if (count($triples_lst) > 0) {
      $result->triples = $triples_lst;
    }  
    
    // add the time
    if (isset($this->time_phr)) {
      $phr = New phrase;
      $phr->usr = $this->usr;
      $phr->id  = $this->time_id;
      $phr->load($debug-1);
      $result->time = $phr->name; 
      zu_debug('value->export_obj got time '.$this->time_phr->dsp_id($debug-1), $debug-18);
    }

    // add the value itself
    $result->number = $this->number;

    // add the share type
    zu_debug('value->export_obj get share', $debug-18);
    if ($this->share_id > 0 and $this->share_id <> cl(DBL_SHARE_PUBLIC)) {
      $result->share = $this->share_type_code_id($debug-1);
    }  

    // add the protection type
    zu_debug('value->export_obj get protection', $debug-18);
    if ($this->protection_id > 0 and $this->protection_id <> cl(DBL_PROTECT_NO)) {
      $result->protection = $this->protection_type_code_id($debug-1);
    }  

    // add the source
    zu_debug('value->export_obj get source', $debug-18);
    if ($this->source_id > 0) {
      $result->source = $this->source_name($debug-1);
    }
    
    zu_debug('value->export_obj -> '.json_encode($result), $debug-18);
    return $result;
  }
  
  // import a value from an external object
  function import_obj ($json_obj, $debug) {
    zu_debug('value->import_obj', $debug-10);
    $result = '';
    
    $get_ownership = false;
    foreach ($json_obj AS $key => $value) {

      if ($key == 'words') {
        $phr_lst = New phrase_list;
        $phr_lst->usr = $this->usr;
        foreach ($value AS $phr_name) {
          $phr = New phrase;
          $phr->name = $phr_name;
          $phr->usr  = $this->usr;
          $phr->load($debug-1);
          if ($phr->id == 0) {
            $wrd = New word;
            $wrd->name = $phr_name;
            $wrd->usr  = $this->usr;
            $wrd->load($debug-1);
            if ($wrd->id == 0) {
              $wrd->name = $phr_name;
              $wrd->type_id = cl(SQL_WORD_TYPE_NORMAL);
              $wrd->save($debug-1);
            }
            if ($wrd->id == 0) {
              zu_err('Cannot add word "'.$phr_name.'" when importing '.$this->dsp_id(), 'value->import_obj', '', (new Exception)->getTraceAsString(), $this->usr);
            } else {
              $phr_lst->add($wrd, $debug-1);
            }
          } else {         
            $phr_lst->add($phr, $debug-1);
          }  
        }  
        zu_debug('value->import_obj got words '.$phr_lst->dsp_id(), $debug-16);
        $phr_grp = $phr_lst->get_grp($debug-1);
        zu_debug('value->import_obj got word group '.$phr_grp->dsp_id(), $debug-18);
        $this->grp = $phr_grp; 
        $this->grp_id = $phr_grp->id; 
        $this->phr_lst = $phr_lst; 
        zu_debug('value->import_obj set grp id to '.$this->grp_id, $debug-14);
      }
      
      if ($key == 'time') {
        $phr = New phrase;
        $phr->name = $value;
        $phr->usr  = $this->usr;
        $phr->load($debug-1);
        if ($phr->id == 0) {
          $wrd = New word;
          $wrd->name = $value;
          $wrd->usr  = $this->usr;
          $wrd->load($debug-1);
          if ($wrd->id == 0) {
            $wrd->name = $value;
            $wrd->type_id = cl(SQL_WORD_TYPE_TIME);
            $wrd->save($debug-1);
          }
          if ($wrd->id == 0) {
            zu_err('Cannot add time word "'.$value.'" when importing '.$this->dsp_id(), 'value->import_obj', '', (new Exception)->getTraceAsString(), $this->usr);
          } else {
            $this->time_phr = $wrd->pharse($debug-1); 
            $this->time_id  = $wrd->id; 
          }
        } else {         
          $this->time_phr = $phr; 
          $this->time_id  = $phr->id; 
        }  
      }
      
      if ($key == 'number') {
        $this->number  = $value; 
      }

      if ($key == 'share') {
        $this->share_id  = cl($value); 
      }

      if ($key == 'protection') {
        $this->protection_id  = cl($value);
        if ($value <> DBL_PROTECT_NO) {
          $get_ownership = true;
        }  
      }
    }
    
    if ($result == '') {
      $this->save($debug-1);
      zu_debug('value->import_obj -> '.$this->dsp_id(), $debug-18);
    } else {
      zu_debug('value->import_obj -> '.$result, $debug-18);
    }
    
    // try to get the ownership if requested
    if ($get_ownership) {
      $this->take_ownership($debug-1);
    }

    return $result;
  }
  
  /*
  
  display functions
  
  */
  
  // create and return the description for this value for debugging
  // TODO seems to crash if value is only partly loaded
  function dsp_id($debug) {
    $result = '';
    zu_debug('value->dsp_id', $debug-28);
    
    //$this->load_phrases($debug-1);
    if (isset($this->grp)) {
      zu_debug('value->dsp_id group', $debug-8);
      $result .= $this->grp->dsp_id();
    }
    if (isset($this->time_phr)) {
      if ($result <> '') { $result .= '@'; }
      zu_debug('value->dsp_id time', $debug-8);
      //zu_debug('value->dsp_id time for '.$this->time_phr, $debug-8);
      zu_debug('value->dsp_id time of type '.gettype($this->time_phr), $debug-8);
      if (gettype($this->time_phr) <> 'object') {
        zu_err('Cannot show time phrase "'.$this->time_phr.'" because is of type '.gettype($this->time_phr), 'value->dsp_id', '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        $result .= $this->time_phr->dsp_id($debug-1);
      }
    }

    zu_debug('value->dsp_id done', $debug-28);
    return $result;
  }
  
  // create and return the description for this value
  function name($debug) {
    zu_debug('value->name', $debug-16);
    $result = '';
    $this->load_phrases($debug-1);
    if (isset($this->grp)) {
      $result .= $this->grp->name($debug-1);
    }
    if (isset($this->time_phr)) {
      if ($result <> '') { $result .= ','; }
      $result .= $this->time_phr->name;
    }
      
    return $result;
  }
  
  // return the html code to display a value
  // this is the opposite of the convert function 
  function display ($back, $debug) {
    if (!is_null($this->number)) {
      $this->load_phrases($debug-1);
      $num_text = $this->val_formatted($debug-1);
      if (!$this->is_std($debug-1)) {
        $result = '<font class="user_specific">'.$num_text.'</font>';
        //$result = $num_text;
      } else {  
        $result = $num_text;
      }  
    }
    return $result;    
  }

  // html code to show the value with the possibility to click for the result explaination
  function display_linked($back, $debug) {
    zu_debug('value->display_linked ('.$this->id.',u'.$this->usr->id.')', $debug-18);
    if (!is_null($this->number)) {
      $num_text = $this->val_formatted($debug-1);
      $link_format = '';
      if (isset($this->usr)) {
        if (!$this->is_std($debug-1)) {
          $link_format = ' class="user_specific"';
        }
      }
      // to review
      $result .= '<a href="/http/value_edit.php?id='.$this->id.'&back='.$back.'"'.$link_format.'>'.$num_text.'</a>';
    }
    zu_debug('value->display_linked -> done.', $debug-18);
    return $result; 
  }
  
  // offer the user to add a new value similar to this value
  function btn_add ($back, $debug) {
    $result = '';

    $val_btn_title = '';
    $url_phr = '';
    $this->load_phrases($debug-1);
    if (isset($this->phr_lst)) {
      if (!empty($this->phr_lst->lst)) {
        $val_btn_title = "add new value similar to ".htmlentities($this->phr_lst->name($debug-1));
      } else {
        $val_btn_title = "add new value";
      }  
      $url_phr = $this->phr_lst->id_url_long();
    }  
    
    $val_btn_call  = '/http/value_add.php?back='.$back.$url_phr;
    $result .= btn_add ($val_btn_title, $val_btn_call); 
    
    return $result;    
  }
  
  /*
  
  get functions that returns other linked objects
  
  */
  
  // create and return the figure object for the value
  function figure($debug) {
    zu_debug('value->figure', $debug-16);
    $fig = New figure;
    $fig->id          = $this->id;
    $fig->usr         = $this->usr;
    $fig->type        = 'value';
    $fig->number      = $this->number;
    $fig->last_update = $this->last_update;
    $fig->obj         = $this;
    zu_debug('value->figure -> done.', $debug-16);
      
    return $fig;
  }
  
  // convert a user entry for a value to a useful database number
  // e.g. remove leading spaces and tabulators
  // if the value contains a single quote "'" the function asks once if to use it as a commy or a tausend operator
  // once the user has given an answer it saves the answer in the database and uses it for the next values
  // if the type of the value differs the user should be asked again
  function convert ($debug) {
    zu_debug('value->convert ('.$this->usr_value.',u'.$this->usr->id.')', $debug-10);
    $result = $this->usr_value;
    $result = str_replace(" ", "", $result);
    $result = str_replace("'", "", $result);
    //$result = str_replace(".", "", $result);
    $this->number = $result;
    return $result;
  }

  /*
    display functions
    -------
  */

  // depending on the word list format the numeric value
  // format the value for on screen display
  // similar to the corresponding function in the "formula_value" class
  function val_formatted($debug) {
    $result = '';

    $this->load_phrases($debug-1);
    
    if (!is_null($this->number)) {
      if (is_null($this->wrd_lst)) {
        $this->load($debug-1);
      }
      if ($this->wrd_lst->has_percent($debug-1)) {
        $result = round($this->number*100,2)."%";
      } else {
        if ($this->number >= 1000 OR $this->number <= -1000) {
          $result .= number_format($this->number,0,$this->usr->dec_point,$this->usr->thousand_sep);
        } else {  
          $result = round($this->number,2);
        }
      } 
    }
    return $result; 
  }
    
  // the same as btn_del_value, but with another icon
  function btn_undo_add_value ($back, $debug) {
    $result = btn_undo ('delete this value', '/http/value_del.php?id='.$this->id.'&back='.$back.'');
    return $result;
  }

  // display a value, means create the HTML code that allows to edit the value
  function dsp_tbl_std ($back, $debug) {
    zu_debug('value->dsp_tbl_std .', $debug-10);
    $result  = '';
    $result .= '    <td>'."\n";
    $result .= '      <div align="right"><a href="/http/value_edit.php?id='.$this->id.'&back='.$back.'">'.$this->val_formatted($debug-1).'</a></div>'."\n";
    $result .= '    </td>'."\n";
    return $result;
  }

  // same as dsp_tbl_std, but in the user specific color
  function dsp_tbl_usr ($back, $debug) {
    zu_debug('value->dsp_tbl_usr.', $debug-10);
    $result  = '';
    $result .= '    <td>'."\n";
    $result .= '      <div align="right"><a href="/http/value_edit.php?id='.$this->id.'&back='.$back.'" class="user_specific">'.$this->val_formatted($debug-1).'</a></div>'."\n";
    $result .= '    </td>'."\n";
    return $result;
  }

  function dsp_tbl ($back, $debug) {
    zu_debug('value->dsp_tbl_std .', $debug-10);
    if ($this->is_std($debug-1)) {
      $result .= $this->dsp_tbl_std($back, $debug-1);
    } else {
      $result .= $this->dsp_tbl_usr($back, $debug-1);
    }  
    return $result;
  }
  
  // display the history of a value
  function dsp_hist($page, $size, $call, $back, $debug) {
    zu_debug("value->dsp_hist for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".", $debug-10);
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->obj  = $this;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'value';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist($debug-1);
    
    zu_debug("value->dsp_hist -> done", $debug-1);
    return $result;
  }

  // display the history of a value
  function dsp_hist_links($page, $size, $call, $back, $debug) {
    zu_debug("value->dsp_hist_links (".$this->id.",size".$size.",b".$size.")", $debug-10);
    $result = ''; // reset the html code var

    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'value';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist_links($debug-1);
    
    zu_debug("value->dsp_hist_links -> done", $debug-1);
    return $result;
  }

  // display some value samples related to the wrd_id
  // with a preference of the start_word_ids
  function dsp_samples($wrd_id, $start_wrd_ids, $size, $back, $debug) {
    zu_debug("value->dsp_samples (".$wrd_id.",rt".implode(",",$start_wrd_ids).",size".$size.")", $debug-10);
    $result = ''; // reset the html code var
    
    // get value changes by the user that are not standard
    $sql = "SELECT v.value_id,
                   IF(u.user_value IS NULL,v.word_value,u.user_value) AS word_value, 
                   t.word_id,
                   t.word_name
              FROM value_phrase_links l,
                   value_phrase_links lt,
                   words t,
                   `values` v
         LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = ".$this->usr->id." 
             WHERE l.phrase_id = ".$wrd_id."
               AND l.value_id = v.value_id
               AND v.value_id = lt.value_id
               AND lt.phrase_id <> ".$wrd_id."
               AND lt.phrase_id = t.word_id
               AND (u.excluded IS NULL OR u.excluded = 0) 
             LIMIT ".$size.";";
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_lst = $db_con->get($sql, $debug-5);  

    // prepare to show where the user uses different value than a normal viewer
    $row_nbr = 0;
    $value_id = 0;
    $word_names = "";
    $result .= dsp_tbl_start_hist ();
    foreach ($db_lst AS $db_row) {
      // display the headline first if there is at least on entry
      if ($row_nbr == 0) {
        $result .= '<tr>';
        $result .= '<th>samples</th>';
        $result .= '<th>for</th>';
        $result .= '</tr>';
        $row_nbr++;
      }

      $new_value_id = $db_row["value_id"];
      $wrd = New word_dsp;
      $wrd->usr  = $this->usr;
      $wrd->id   = $db_row["word_id"];
      $wrd->name = $db_row["word_name"];
      if ($value_id <> $new_value_id) {
        if ($word_names <> "") {
          // display a row if the value has changed and 
          $result .= '<tr>';
          $result .= '<td><a href="/http/value_edit.php?id='.$value_id.'&back='.$back.'" class="grey">'.$row_value.'</a></td>';
          $result .= '<td>'.$word_names.'</td>';
          $result .= '</tr>';
          $row_nbr++;
        }
        // prepare a new value display
        $row_value = $db_row["word_value"];
        $word_names = $wrd->dsp_link_style("grey");
        $value_id = $new_value_id;
      } else {
        $word_names .= ", ".$wrd->dsp_link_style("grey");
      }
    }
    // display the last row if there has been at least one word
    if ($word_names <> "") {
      $result .= '<tr>';
      $result .= '<td><a href="/http/value_edit.php?id='.$value_id.'&back='.$back.'" class="grey">'.$row_value.'</a></td>';
      $result .= '<td>'.$word_names.'</td>';
      $result .= '</tr>';
    }
    $result .= dsp_tbl_end ();
    
    zu_debug("value->dsp_samples -> done.", $debug-16);
    return $result;
  }  

  // simple modal box to add a value
  function dsp_add_fast($back, $debug) {
    $result = '';

    $result .= '  <h2>Modal Example</h2>';
    $result .= '  <!-- Button to Open the Modal -->';
    //$result .= '  <a href="/http/value_add.php?back=2" title="add"><img src="'.$icon.'" alt="'.$this->title.'"></a>';
    $result .= '';  

    return $result;  
  }
  
  // lists all phrases related to a given value except the given phrase
  // and offer to add a formula to the value as an alternative
  // $wrd_add is onöy optional to display the last added phrase at the end
  // todo: take user unlink of phrases into account
  // save data to the database only if "save" is pressed add and remove the phrase links "on the fly", which means that after the first call the edit view is more or less the same as the add view
  function dsp_edit($type_ids, $back, $debug) {
    $result = ''; // reset the html code var
        
    // set main display parameters for the add or edit view
    if ($this->id <= 0) { 
      $script = "value_add";
      $result .= dsp_form_start($script);
      $result .= dsp_text_h3("Add value for"); 
      zu_debug("value->dsp_edit new for phrase ids ".implode(",",$this->ids)." and user ".$this->usr->id.".", $debug-10);
    } else { 
      $script = "value_edit";
      $result .= dsp_form_start($script);
      $result .= dsp_text_h3("Change value for"); 
      if (count($this->ids) <= 0) {
        $this->load_phrases($debug-1);
        zu_debug('value->dsp_edit id '.$this->id.' with "'.$this->grp->name().'"@"'.$this->time_phr->name.'"and user '.$this->usr->id, $debug-10);
      } else {  
        $this->load_time_phrase($debug-1);
        zu_debug('value->dsp_edit id '.$this->id.' with phrase ids '.implode(',',$this->ids).' and user '.$this->usr->id, $debug-10);
      }
    }
    $this_url = '/http/'.$script.'.php?id='.$this->id.'&back='.$back; // url to call this display again to display the user changes
    
    // display the words and triples
    $result .= dsp_tbl_start_select();
    if (count($this->ids) > 0) {
      $url_pos = 1; // the phrase position (combined number for fixed, type and free phrases)
      // if the form is confirmed, save the value or the other way round: if with the plus sign only a new phrase is added, do not yet save the value
      $result .= '  <input type="hidden" name="id" value="'.$this->id.'">';
      $result .= '  <input type="hidden" name="confirm" value="1">';
      
      // reset the phrase sample settings
      $main_wrd == Null;
      zu_debug("value->dsp_edit main wrd", $debug-10);
      
      // rebuild the value ids if needed 
      // 1. load the phrases parameters based on the ids
      $result .= $this->set_phr_lst_by_ids($debug-1);
      // 2. extract the time from the phrase list
      $result .= $this->set_time_by_phr_lst($debug-1);
      zu_debug("value->dsp_edit phrase list incl. time ".$this->phr_lst->name(), $debug-10);
      $result .= $this->set_phr_lst_ex_time($debug-1);
      zu_debug("value->dsp_edit phrase list excl. time ".$this->phr_lst->name(), $debug-10);
      $phr_lst = $this->phr_lst;
      
      /*
      // load the phrase list
      $phr_lst = New phrase_list;
      $phr_lst->ids = $this->ids;
      $phr_lst->usr = $this->usr;
      $phr_lst->load($debug-1);
      
      // seperate the time if needed
      if ($this->time_id <= 0) {
        $this->time_phr = $phr_lst->time_useful($debug-1);
        $phr_lst->del($this->time_phr, $debug-1);
        $this->time_id = $this->time_phr->id; // not really needed ...
      }
      */
      
      // assign the type to the phrases
      foreach ($phr_lst->lst AS $phr) {
        $phr->usr = $this->usr;
        foreach (array_keys($this->ids) AS $pos) {
          if ($phr->id == $this->ids[$pos]) {
            $phr->is_wrd_id = $type_ids[$pos];
            $is_wrd = New word_dsp;
            $is_wrd->id  = $phr->is_wrd_id;
            $is_wrd->usr = $this->usr;
            $phr->is_wrd = $is_wrd;
            $phr->dsp_pos = $pos;
          }
        }
        // guess the missing phrase types 
        if ($phr->is_wrd_id == 0) {
          zu_debug('value->dsp_edit -> guess type for "'.$phr->name.'".', $debug-10);
          $phr->is_wrd = $phr->is_mainly($debug-1);
          if ($phr->is_wrd->id > 0) {
            $phr->is_wrd_id = $phr->is_wrd->id;
            zu_debug('value->dsp_edit -> guessed type for '.$phr->name.': '.$phr->is_wrd->name, $debug-10);
          }  
        }
      }
  
      // show first the phrases, that are not supposed to be changed
      //foreach (array_keys($this->ids) AS $pos) {
      zu_debug('value->dsp_edit -> show fixed phrases.', $debug-16);
      foreach ($phr_lst->lst AS $phr) {
        //if ($type_ids[$pos] < 0) {
        if ($phr->is_wrd_id < 0) {
          zu_debug('value->dsp_edit -> show fixed phrase "'.$phr->name.'".', $debug-10);
          // allow the user to change also the fixed phrases
          $type_ids_adj = $type_ids;
          $type_ids_adj[$phr->dsp_pos] = 0;
          $used_url = $this_url.zu_ids_to_url($this->ids,   "phrase", $debug-1).
                                zu_ids_to_url($type_ids_adj,"type",   $debug-1);
          $result .= $phr->dsp_name_del($used_url, $debug-1);
          $result .= '  <input type="hidden" name="phrase'.$url_pos.'" value="'.$phr->id.'">';
          $url_pos++;
        } 
      }

      // show the phrases that the user can change: first the non specific ones, that the phrases of a selective type and new phrases at the end
      zu_debug('value->dsp_edit -> show phrases.', $debug-16);
      for ($dsp_type = 0; $dsp_type <= 1; $dsp_type++) {
        foreach ($phr_lst->lst AS $phr) {
          /*
          // build a list of suggested phrases
          $phr_lst_sel_old = array();
          if ($phr->is_wrd_id > 0) {
            // prepare the selector for the type phrase
            $phr->is_wrd->usr = $this->usr;
            $phr_lst_sel = $phr->is_wrd->childs($debug-1);
            zu_debug("value->dsp_edit -> suggested phrases for ".$phr->name.": ".$phr_lst_sel->name().".", $debug-10);
          } else {
            // if no phrase group is found, use the phrase type time if the phrase is a time phrase
            if ($phr->is_time()) {
              $phr_lst_sel = New phrase_list;
              $phr_lst_sel->usr = $this->usr;
              $phr_lst_sel->phrase_type_id = cl(SQL_WORD_TYPE_TIME);
              $phr_lst_sel->load($debug-1);
            }
          } */

          // build the url for the case that this phrase should be removed
          zu_debug('value->dsp_edit -> build url.', $debug-18);
          $phr_ids_adj  = $this->ids;
          $type_ids_adj = $type_ids;
          array_splice($phr_ids_adj,  $phr->dsp_pos, 1);
          array_splice($type_ids_adj, $phr->dsp_pos, 1);
          $used_url = $this_url.zu_ids_to_url($phr_ids_adj, "phrase", $debug-1).
                                zu_ids_to_url($type_ids_adj,"type", $debug-1).
                                '&confirm=1';
          // url for the case that this phrase should be renamed   
          if ($phr->id > 0) {
            $phrase_url = '/http/word_edit.php?id='.$phr->id.'&back='.$back;
          } else {
            $lnk_id = $phr->id * -1;
            $phrase_url = '/http/link_edit.php?id='.$lnk_id.'&back='.$back;
          }
          
          // show the phrase selector
          $result .= '  <tr>';

          // show the phrases that have a type
          if ($dsp_type == 0) {
            if ($phr->is_wrd->id > 0) {
              zu_debug('value->dsp_edit -> id '.$phr->id.' has a type.', $debug-18);
              $result .= '    <td>';
              $result .= $phr->is_wrd->name.':';
              $result .= '    </td>';
              //$result .= '    <input type="hidden" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
              $result .= '    <td>';
              /*if (!empty($phr_lst_sel->lst)) {
                $result .= '      '.$phr_lst_sel->dsp_selector("phrase".$url_pos, $script, $phr->id, $debug-1);
              } else {  */
              $result .= '      '.$phr->dsp_selector ($phr->is_wrd, $script, $url_pos, '', $back, $debug-1);
              //}
              $url_pos++;
              
              $result .= '    </td>';
              $result .= '    <td>'.btn_del  ("Remove ".$phr->name, $used_url, $debug-1).'</td>';
              $result .= '    <td>'.btn_edit ("Rename ".$phr->name, $phrase_url, $debug-1).'</td>';
            }
          }

          // show the phrases that don't have a type
          if ($dsp_type == 1) {
            if ($phr->is_wrd->id == 0 AND $phr->id > 0) {
              zu_debug('value->dsp_edit -> id '.$phr->id.' has no type.', $debug-18);
              if (!isset($main_wrd)) {
                $main_wrd = $phr;
              }
              //$result .= '    <input type="hidden" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
              $result .= '    <td colspan="2">';
              $result .= '      '.$phr->dsp_selector (0, $script, $url_pos, '', $back, $debug-1);
              $url_pos++;
              
              $result .= '    </td>';
              $result .= '    <td>'.btn_del  ("Remove ".$phr->name, $used_url, $debug-1).'</td>';
              $result .= '    <td>'.btn_edit ("Rename ".$phr->name, $phrase_url, $debug-1).'</td>';
            }
          }  


          $result .= '  </tr>';
        } 
      } 

      // show the time word
      zu_debug('value->dsp_edit -> show time', $debug-18);
      if ($this->time_id > 0) {
        if (isset($this->time_phr)) {
          $result .= '  <tr>';
          if ($phr_id == 0) {
            $result .= '    <td colspan="2">';

            zu_debug('value->dsp_edit -> show time selector', $debug-18);
            $result .= $this->time_phr->dsp_time_selector (0, $script, $url_pos, $back, $debug-1) ;
            $url_pos++;
            
            $result .= '    </td>';
            $result .= '    <td>'.btn_del  ("Remove ".$this->time_phr->name, $used_url, $debug-1).'</td>';
          }
          $result .= '  </tr>';
        }  
      }  
      
      // show the new phrases
      zu_debug('value->dsp_edit -> show new phrases', $debug-18);
      foreach ($this->ids AS $phr_id) {
        $result .= '  <tr>';
        if ($phr_id == 0) {
          $result .= '    <td colspan="2">';

          $phr_new = New phrase;
          $phr_new->usr = $this->usr;
          $result .= $phr_new->dsp_selector (0, $script, $url_pos, '', $back, $debug-1) ;
          $url_pos++;
          
          $result .= '    </td>';
          $result .= '    <td>'.btn_del  ("Remove new", $used_url, $debug-1).'</td>';
        }
        $result .= '  </tr>';
      }  
    }

    $result .= dsp_tbl_end ();

    zu_debug('value->dsp_edit -> table ended', $debug-18);
    $phr_ids_new    = $this->ids;
    //$phr_ids_new[]  = $new_phrase_default;
    $phr_ids_new[]  = 0;
    $type_ids_new   = $type_ids;
    $type_ids_new[] = 0;
    $used_url = $this_url.zu_ids_to_url($phr_ids_new, "phrase", $debug-1).
                          zu_ids_to_url($type_ids_new,"type", $debug-1);
    $result .= '  '.btn_add ("Add another phrase", $used_url);
    $result .= '  <br><br>';
    $result .= '  <input type="hidden" name="back" value="'.$back.'">';
    if ($this->id > 0) {   
      $result .= '  to <input type="text" name="value" value="'.$this->number.'">';
    } else {
      $result .= '  is <input type="text" name="value">';
    }
    $result .= dsp_form_end("Save");
    $result .= '<br><br>';
    zu_debug('value->dsp_edit -> load source', $debug-18);
    $src = $this->load_source($debug-1);
    if (isset($src)) {
      $result .= $src->dsp_select($script, $back, $debug-1);
      $result .= '<br><br>';
    }
    
    // display the share type
    $result .= $this->dsp_share($script, $back, $debug-1);

    // display the protection type
    $result .= $this->dsp_protection($script, $back, $debug-1);
    
    $result .= '<br>';
    $result .= btn_back($back);
    
    // display the user changes 
    zu_debug('value->dsp_edit -> user changes', $debug-18);
    if ($this->id > 0) { 
      $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $result .= dsp_text_h3("Latest changes related to this value", "change_hist");
        $result .= $changes;
      }
      $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $result .= dsp_text_h3("Latest link changes related to this value", "change_hist");
        $result .= $changes;
      }
    } else {
      // display similar values as a sample for the user to force a consistent type of entry e.g. cost should always be a negative number
      if (isset($main_wrd)) {
        $main_wrd->load($debug-1);
        $samples = $this->dsp_samples($main_wrd->id, $this->ids, 10, $back, $debug-1);
        zu_debug("value->dsp_edit samples.", $debug-10);
        if (trim($samples) <> "") {
          $result .= dsp_text_h3('Please have a look at these other "'.$main_wrd->dsp_link_style("grey").'" values as an indication', 'change_hist');
          $result .= $samples;
        }
      }
    }

    zu_debug("value->dsp_edit -> done", $debug-10);
    return $result;
  }


  /*
  
    Select functions
    
  */  
  
  // get a list of all formula results that are depending on this value
  // todo: add a loop over the calculation if the are more formula results needs to be updated than defined with SQL_ROW_MAX
  function fv_lst_depending($debug) {
    zu_debug('value->fv_lst_depending group id "'.$this->grp_id.'" for user '.$this->usr->name.'', $debug-10);      
    $fv_lst = New formula_value_list;
    $fv_lst->usr    = $this->usr;
    $fv_lst->grp_id = $this->grp_id;
    $fv_lst->load(SQL_ROW_MAX, $debug-1);

    zu_debug('value->fv_lst_depending -> done.', $debug-10);      
    return $fv_lst;
  }
  
    
  /*
  
    Save functions
    
    changer      - true if another user is using this record (value in theis case)
    can_change   - true if the actual user is allowed to change the record
    log_add      - set the log object for adding a new record
    log_upd      - set the log object for changing this record
    log_del      - set the log object for excluding this record
    need_usr_cfg - true if at least one field differs between the standard record and the user specific record 
    has_usr_cfg  - true if a record for user specific setting exists 
    add_usr_cfg  - to created a record for user specific settings 
    del_usr_cfg  - to delete the record for user specific settings, because it is not needed any more
    
    Default steps to save a value
    1. if the id is not set
    2. get the word and triple ids
    3. get or create a word group for the word and triple combination
    4. get the time (period) or time stamp
    5. check if a value for the word group and time already exist
    6. if not, create the value
    7. 
    
    cases for user
    1) user a creates a value -> he can change it
    2) user b changes the value -> the change is saved only for this user
    3a) user a changes the original value -> the change is save in the original record -> user a is still the onwer
    3b) user a changes the original value to the same value as b -> the user specific record is removed -> user a is still the onwer
    3c) user b changes the value -> the user specific record is updated
    3d) user b changes the value to the same value as a -> the user specific record is removed
    3e) user a excludes the value -> b gets the owner and a user specific exclusion for a is created
    
  */
  
  // true if noone has used this value
  function not_used($debug) {
    zu_debug('value->not_used ('.$this->id.')', $debug-10);  
    $result = true;
    
    // to review: maybe replace by a database foreign key check
    $result = $this->not_changed($debug-1);
    return $result;
  }

  // true if no other user has modified the value
  function not_changed($debug) {
    zu_debug('value->not_changed id '.$this->id.' by someone else than the onwer ('.$this->owner_id.').', $debug-10);  
    $result = true;
    
    $change_user_id = 0;
    if ($this->owner_id > 0) {
      $sql = "SELECT user_id 
                FROM user_values 
               WHERE value_id = ".$this->id."
                 AND user_id <> ".$this->owner_id."
                 AND (excluded <> 1 OR excluded is NULL)";
    } else {
      $sql = "SELECT user_id 
                FROM user_values 
               WHERE value_id = ".$this->id."
                 AND (excluded <> 1 OR excluded is NULL)";
    }
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    $change_user_id = $db_row['user_id'];
    if ($change_user_id > 0) {
      $result = false;
    }
    zu_debug('value->not_changed for '.$this->id.' is '.zu_dsp_bool($result), $debug-10);  
    return $result;
  }

  // search for the median (not average) value
  function get_std($debug) {
  }
  
  // this value object is defined as the standard value
  function set_std($debug) {
    // if a user has been using the standard value until now, just create a message, that the standard value has been changes and offer him to use the old standard value also in the future
    // delete all user values that are matching the new standard
    // save the new standard value in the database
  }
  
  // true if the loaded value is not user specific
  // todo: check the difference between is_std and can_change
  function is_std($debug) {
    $result = false;
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $result = true;
    }  

    zu_debug('value->is_std -> ('.zu_dsp_bool($result).')', $debug-10);  
    return $result;
  }
  
  // true if the user is the owner and noone else has changed the value
  function can_change($debug) {
    zu_debug('value->can_change id '.$this->id.' by user '.$this->usr->name, $debug-10);  
    $can_change = false;
    zu_debug('value->can_change id '.$this->id.' owner '.$this->owner_id.' = '.$this->usr->id.'?', $debug-14);  
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $can_change = true;
    }  

    zu_debug('value->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);  
    return $can_change;
  }

  // true if a record for a user specific configuration already exists in the database
  function has_usr_cfg($debug) {
    $has_cfg = false;
    if ($this->usr_cfg_id > 0) {
      $has_cfg = true;
    }  
    return $has_cfg;
  }

  // create a database record to save a user specific value
  function add_usr_cfg($debug) {
    $result = '';

    if (!$this->has_usr_cfg) {
      zu_debug('value->add_usr_cfg for "'.$this->id.' und user '.$this->usr->name, $debug-10);

      // check again if there ist not yet a record
      $sql = 'SELECT user_id 
                FROM user_values
               WHERE value_id = '.$this->id.' 
                 AND user_id = '.$this->usr->id.';';
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      $usr_db_id = $db_row['user_id'];
      if ($usr_db_id <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_value';
        $log_id = $db_con->insert(array('value_id','user_id'), array($this->id,$this->usr->id), $debug-1);
        if ($log_id <= 0) {
          $result .= 'Insert of user_value failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed 
  // exposed at the moment to user_display.php for consistency check, but this hsould not be needed
  function del_usr_cfg_if_not_needed($debug) {
    $result = '';
    zu_debug('value->del_usr_cfg_if_not_needed pre check for "'.$this->id.' und user '.$this->usr->name, $debug-12);

    // check again if the user config is still needed (don't use $this->has_usr_cfg to include all updated)
    $sql = "SELECT value_id,
                   user_value,
                   source_id,
                   excluded
              FROM user_values
             WHERE value_id = ".$this->id." 
               AND user_id = ".$this->usr->id.";";
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $usr_cfg = $db_con->get1($sql, $debug-5);  
    zu_debug('value->del_usr_cfg_if_not_needed check for "'.$this->id.' und user '.$this->usr->name.' with ('.$sql.').', $debug-12);
    if ($usr_cfg['value_id'] > 0) {
      if ($usr_cfg['user_value'] == Null
      AND $usr_cfg['source_id']  == Null
      AND $usr_cfg['excluded'] == Null) {
        // delete the entry in the user sandbox
        zu_debug('value->del_usr_cfg_if_not_needed any more for "'.$this->id.' und user '.$this->usr->name, $debug-10);
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  
    }  

    return $result;
  }

  // simply remove a user adjustment without check
  function del_usr_cfg_exe($db_con, $debug) {
    $result = '';

    $db_con->type = 'user_value';         
    $result .= $db_con->delete(array('value_id','user_id'), array($this->id,$this->usr->id), $debug-1);
    if (str_replace('1','',$result) <> '') {
      $result .= 'Deletion of user value '.$this->id.' failed for '.$this->usr->name.'.';
    }
    
    return $result;
  }
  
  // remove user adjustment and log it (used by user.php to undo the user changes)
  function del_usr_cfg($debug) {
    $result = '';

    if ($this->id > 0 AND $this->usr->id > 0) {
      zu_debug('value->del_usr_cfg  "'.$this->id.' und user '.$this->usr->name, $debug-12);

      $db_type = 'user_value';
      $log = $this->log_del($db_type, $debug-1);
      if ($log->id > 0) {
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  

    } else {
      zu_err("The value database ID and the user must be set to remove a user specific modification.", "value->del_usr_cfg", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // set the log entry parameter for a new value
  function log_add($debug) {
    zu_debug('value->log_add "'.$this->number.'" for user '.$this->usr->id, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'values';
    $log->field     = 'word_value';
    $log->old_value = '';
    $log->new_value = $this->number;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the log entry parameters for a value update
  function log_upd($db_number, $debug) {
    zu_debug('value->log_upd "'.$this->number.'" for user '.$this->usr->id, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table   = 'values';
    } else {  
      $log->table   = 'user_values';
    }
    
    return $log;    
  }
  
  // set the log entry parameter to delete a value
  function log_del($db_type, $debug) {
    zu_debug('value->log_del "'.$this->id.'" for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = $db_type;
    $log->field     = 'word_value';
    $log->old_value = $this->number;
    $log->new_value = '';
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // update the phrase links to the value based on the group and time for faster searching
  // e.g. if the value "46'000" is linked to the group "2116 (ABB, SALES, CHF, MIO)" it is checked that lins to all phrases to the value are in the database 
  //      to be able to search the value by a single phrase
  // to do: make it user specific!
  function upd_phr_links($debug) {
    zu_debug('value->upd_phr_links', $debug-10);
    $result = '';
    
    // create the db link object for all actions
    $db_con = New mysql;          
    $db_con->usr_id = $this->usr->id;         

    $table_name = 'value_phrase_links'; 
    $field_name = 'phrase_id';
    
    // read all existing phrase to value links
    $sql = 'SELECT '.$field_name.'
              FROM '.$table_name.'
             WHERE value_id = '.$this->id.';';
    $grp_lnk_rows = $db_con->get($sql, $debug-1);  
    $db_ids = array();
    foreach ($grp_lnk_rows AS $grp_lnk_row) {
      $db_ids[] = $grp_lnk_row[$field_name];
    }

    zu_debug('value->upd_phr_links -> links found in database '.implode(",",$db_ids), $debug-12);
    
    // add the time phrase to the target link list
    if (!isset($this->phr_lst)) { $this->load_phrases($debug-1); }
    if (!isset($this->phr_lst)) {
      // zu_err('Cannot load phrases for group "'.$this->phr_grp_id.'".', "value->upd_phr_links", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $phr_ids_used = $this->phr_lst->ids();
      if ($this->time_id <> 0) {
        if (!in_array($this->time_id, $phr_ids_used)) {
          $phr_ids_used[] = $this->time_id;
        }
      }
    }
    zu_debug('value->upd_phr_links -> phrases loaded based on value '.implode(",",$phr_ids_used), $debug-12);
    
    // get what needs added or removed
    zu_debug('value->upd_phr_links -> should have phrase ids '.implode(",",$phr_ids_used), $debug-12);
    $add_ids = array_diff($phr_ids_used, $db_ids);
    $del_ids = array_diff($db_ids, $phr_ids_used);
    zu_debug('value->upd_phr_links -> add ids '.implode(",",$add_ids), $debug-12);
    zu_debug('value->upd_phr_links -> del ids '.implode(",",$del_ids), $debug-12);
    
    // add the missing links
    if (count($add_ids) > 0) {
      $add_nbr = 0;
      $sql = '';
      foreach ($add_ids AS $add_id) {
        if ($add_id <> '') {
          if ($sql == '') { $sql = 'INSERT INTO '.$table_name.' (value_id, '.$field_name.') VALUES '; }
          $sql .= " (".$this->id.",".$add_id.") ";
          $add_nbr++;
          if ($add_nbr < count($add_ids)) {
            $sql .= ",";
          } else {
            $sql .= ";";
          }
        }
      }
      zu_debug('value->upd_phr_links -> add sql.', $debug-12);
      if ($sql <> '') { 
        $sql_result = $db_con->exe($sql, DBL_SYSLOG_ERROR, "value->upd_phr_links", (new Exception)->getTraceAsString(), $debug-5);
        if ($sql_result === False) {
          $result .= 'Error adding new group links "'.implode(',',$add_ids).'" for '.$this->id.'.';
        }
      }
    }  
    zu_debug('value->upd_phr_links -> added links "'.implode(',',$add_ids).'" lead to '.implode(",",$db_ids), $debug-14);
    
    // remove the links not needed any more
    if (count($del_ids) > 0) {
      zu_debug('value->upd_phr_links -> del '.implode(",",$del_ids).'', $debug-8);
      $del_nbr = 0;
      $sql = 'DELETE FROM '.$table_name.' 
               WHERE value_id = '.$this->id.'
                 AND '.$field_name.' IN ('.implode(',',$del_ids).');';
      $sql_result = $db_con->exe($sql, DBL_SYSLOG_ERROR, "value->upd_phr_links", (new Exception)->getTraceAsString(), $debug-5);
      if ($sql_result === False) {
        $result .= 'Error removing group links "'.implode(',',$del_ids).'" from '.$this->id.'.';
      }
    }  
    zu_debug('value->upd_phr_links -> deleted links "'.implode(',',$del_ids).'" lead to '.implode(",",$db_ids), $debug-14);
    
    zu_debug('value->upd_phr_links -> done.', $debug-12);
    return $result;    
  }
  
  /*
  // set the parameter for the log entry to link a word to value
  function log_add_link($wrd_id, $debug) {
    zu_debug('value->log_add_link word "'.$wrd_id.'" to value '.$this->id, $debug-10);
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'value_phrase_links';
    $log->new_from  = $this->id;
    $log->new_to    = $wrd_id;
    $log->row_id    = $this->id; 
    $log->link_text = 'word'; 
    $log->add_link_ref($debug-1);
    
    return $log;    
  }
  
  // set the parameter for the log entry to unlink a word to value
  function log_del_link($wrd_id, $debug) {
    zu_debug('value->log_del_link word "'.$wrd_id.'" from value '.$this->id, $debug-10);
    $log = New user_log_link;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = 'value_phrase_links';
    $log->old_from  = $this->id;
    $log->old_to    = $wrd_id;
    $log->row_id    = $this->id; 
    $log->link_text = 'word'; 
    $log->add_link_ref($debug-1);
    
    return $log;    
  }
  
  // link an additional phrase the value
  function add_wrd($phr_id, $debug) {
    zu_debug("value->add_wrd add ".$phr_id." to ".$this->name().",t for user ".$this->usr->name.".", $debug-10);   
    $result = false;

    if ($this->can_change($debug-1)) {
      // log the insert attempt first
      $log = $this->log_add_link($phr_id, $debug-1);
      if ($log->id > 0) {
        // insert the link
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $db_con->type   = 'value_phrase_link';         
        $val_wrd_id = $db_con->insert(array("value_id","phrase_id"), array($this->id,$phr_id), $debug-1);
        if ($val_wrd_id > 0) {
          // get the link id, but updating the reference in the log should not be done, because the row id should be the ref to the original value
          // todo: call the word group creation 
        }
      }
    } else {
      // add the link only for this user
    }  
    return $result;
  }

  // unlink a phrase from the value
  function del_wrd($wrd, $debug) {
    zu_debug('value->del_wrd from id '.$this->id.' the phrase "'.$wrd->name.'" by user '.$this->usr->name, $debug-10);   
    $result = '';

    if ($this->can_change($debug-1)) {
      // log the delete attempt first
      $log = $this->log_del_link($wrd->id, $debug-1);
      if ($log->id > 0) {
        // remove the link
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $db_con->type   = 'value_phrase_link';         
        $result = $db_con->delete(array("value_id","phrase_id"), array($this->id,$wrd->id), $debug-1);
        //$result = str_replace ('1','',$result);
      }
    } else {
      // add the link only for this user
    }  
    return $result;
  }
  */

  // actually update a value field in the main database record or the user sandbox
  function save_field_do($db_con, $log, $debug) {
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
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_value';
        // field name exception that should be removed
        if ($log->field == 'word_value') {
          $log->field     = 'user_value';
        }
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
  
  // update the time stamp to trigger an update of the depending results
  function save_field_trigger_update($db_con, $debug) {
    $this->last_update = new DateTime(); 
    $result .= $db_con->update($this->id, 'last_update', 'Now()', $debug-1);
    zu_debug('value->save_field_trigger_update timestamp of '.$this->id.' updated to "'.$this->last_update->format('Y-m-d H:i:s').'".', $debug-18);
    
    // trigger the batch job
    // save the pending update to the database for the batch calculation  
    zu_debug('value->save_field_trigger_update group id "'.$this->grp_id.'" for user '.$this->usr->name.'', $debug-10);      
    if ($this->id > 0) {
      $job = New batch_job;
      $job->type = cl(DBL_JOB_VALUE_UPDATE);
      //$job->usr  = $this->usr;
      $job->obj  = $this;
      $job->add($debug-1);
    }
    zu_debug('value->save_field_trigger_update -> done.', $debug-18);      
    
  }
  
  // set the update parameters for the number
  function save_field_number($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->number <> $this->number) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->number;
      $log->new_value = $this->number;
      $log->std_value = $std_rec->number;
      $log->row_id    = $this->id; 
      $log->field     = 'word_value';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
      // updating the number is definitely relevant for calculation, so force to update the timestamp
      zu_debug('value->save_field_number -> trigger update.', $debug-18);      
      $result .= $this->save_field_trigger_update($db_con, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the source link
  function save_field_source($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->source_id <> $this->source_id) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->source_name($debug-1);
      $log->old_id    = $db_rec->source_id;
      $log->new_value = $this->source_name($debug-1);
      $log->new_id    = $this->source_id; 
      $log->std_value = $std_rec->source_name($debug-1);
      $log->std_id    = $std_rec->source_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'source_id';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }

  // set the update parameters for the value excluded
  function save_field_excluded($db_con, $db_rec, $std_rec, $debug) {
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
      // similar to $this->save_field_do
      if ($this->can_change($debug-1)) {
        $db_con->type = 'formula';
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_value';
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-1);
      }
      // excluding the number can be also relevant for calculation, so force to update the timestamp
      $result .= $this->save_field_trigger_update($db_con, $debug-1);
    }
    return $result;
  }
  
  // save the value number and the source
  function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    $result .= $this->save_field_number     ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_source     ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_share      ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_protection ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded   ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('value->save_fields all fields for "'.$this->id.'" has been saved.', $debug-12);
    return $result;
  }
    
  // updated the view component name (which is the id field)
  // should only be called if the user is the owner and nobody has used the display component link
  function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    zu_debug('value->save_id_fields.', $debug-18);
    $result = '';

    // to load any missng objects
    $db_rec->load_phrases($debug-1);
    $this->load_phrases($debug-1);
    $std_rec->load_phrases($debug-1);
      
    if ($db_rec->grp_id <> $this->grp_id) {
      zu_debug('value->save_id_fields to '.$this->dsp_id().' from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().').', $debug-10);
      
      $log = $this->log_upd($debug-1);
      if (isset($db_rec->grp))  { $log->old_value = $db_rec->grp->name($debug-1); }
      if (isset($this->grp))    { $log->new_value = $this->grp->name($debug-1); }
      if (isset($std_rec->grp)) { $log->std_value = $std_rec->grp->name($debug-1); }
      $log->old_id    = $db_rec->grp_id;
      $log->new_id    = $this->grp_id;
      $log->std_id    = $std_rec->grp_id;
      $log->row_id    = $this->id; 
      $log->field     = 'phrase_group_id';
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("phrase_group_id"),
                                              array($this->grp_id), $debug-1);
      }
    }
    zu_debug('value->save_id_fields group updated for '.$this->dsp_id(), $debug-12);
    
    if ($db_rec->time_id <> $this->time_id) {
      zu_debug('value->save_id_fields to '.$this->dsp_id().' from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().').', $debug-10);
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->time_phr->name($debug-1);
      $log->old_id    = $db_rec->time_id;
      $log->new_value = $this->time_phr->name($debug-1);
      $log->new_id    = $this->time_id;
      $log->std_value = $std_rec->time_phr->name($debug-1);
      $log->std_id    = $std_rec->time_id;
      $log->row_id    = $this->id; 
      $log->field     = 'time_word_id';
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("time_word_id"),
                                              array($this->time_id), $debug-1);
      }
    }
    zu_debug('value->save_id_fields time updated for '.$this->dsp_id(), $debug-12);
    
    // update the phrase links for fast searching
    $result .=$this->upd_phr_links($debug-1);
        
    // not yet active
    /*
    if ($db_rec->time_stamp <> $this->time_stamp) {
      zu_debug('value->save_id_fields to '.$this->dsp_id().' from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().').', $debug-10);
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->time_stamp;
      $log->new_value = $this->time_stamp;
      $log->std_value = $std_rec->time_stamp;
      $log->row_id    = $this->id; 
      $log->field     = 'time_stamp';
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("time_word_id"),
                                              array($this->time_stamp), $debug-1);
      }
    }
    */
    zu_debug('value->save_id_fields time updated for '.$this->dsp_id(), $debug-12);
    
    return $result;
  }
  
  // check if the id parameters are supposed to be changed 
  function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    zu_debug('value->save_id_if_updated has name changed from "'.$db_rec->dsp_id($debug-1).'" to "'.$this->dsp_id($debug-1).'".', $debug-14);
    $result = '';
    
    // if the phrases or time has changed, check if value with the same phrases/time already exists
    if ($db_rec->grp_id <> $this->grp_id OR $db_rec->time_id <> $this->time_id OR $db_rec->time_stamp <> $this->time_stamp) {
      // check if a value with the same phrases/time is already in the database
      $chk_val = New value;
      $chk_val->grp_id     = $this->grp_id;
      $chk_val->time_id    = $this->time_id;
      $chk_val->time_stamp = $this->time_stamp;
      $chk_val->usr        = $this->usr;
      $chk_val->load($debug-1);
      zu_debug('value->save_id_if_updated check value loaded.', $debug-14);
      if ($chk_val->id > 0) {
        // if the target value is already in the database combine the user changes with this values
        $this->id = $chk_val->id;
        $result .= $this->save($debug-1);
        zu_debug('value->save_id_if_updated update the existing '.$chk_val->dsp_id(), $debug-14);
      } else {
        
        zu_debug('value->save_id_if_updated target value name does not yet exists for '.$this->dsp_id(), $debug-14);
        if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
          // in this case change is allowed and done
          zu_debug('value->save_id_if_updated change the existing display component link '.$this->dsp_id().' (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'").', $debug-14);
          //$this->load_objects($debug-1);
          $result .= $this->save_id_fields($db_con, $db_rec, $std_rec, $debug-1);
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
          zu_debug('value->save_id_if_updated recreate the value "'.$db_rec->dsp_id().'" as '.$this->dsp_id().' (standard "'.$std_rec->dsp_id().'").', $debug-14);
        }
      }
    } else {
      zu_debug('value->save_id_if_updated no id field updated (group '.$db_rec->grp_id.'='.$this->grp_id.', time '.$db_rec->time_id.'='.$this->time_id.').', $debug-14);
    }

    zu_debug('value->save_id_if_updated for '.$this->dsp_id().' has been done.', $debug-12);
    return $result;
  }
  
  // create a new value
  function add($db_con, $debug) {
    zu_debug('value->add the value '.$this->dsp_id(), $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the value
      $this->id = $db_con->insert(array("phrase_group_id","time_word_id",     "user_id","word_value","last_update"), 
                                  array(   $this->grp_id,$this->time_id,$this->usr->id,$this->number,"Now()"), $debug-1);
      if ($this->id > 0) {
        // update the reference in the log
        $result .= $log->add_ref($this->id, $debug-1);

        // update the phrase links for fast searching
        $result .=$this->upd_phr_links($debug-1);
        
        // create an empty db_rec element to force saving of all set fields
        $db_val = New value;
        $db_val->id     = $this->id;
        $db_val->usr    = $this->usr;
        $db_val->number = $this->number; // ... but not the field saved already with the insert
        $std_val = clone $db_val;
        // save the value fields
        $result .= $this->save_fields($db_con, $db_val, $std_val, $debug-1);

      } else {
        zu_err("Adding value ".$this->id." failed.", "value->save");
      }
    }
        
    return $result;
  }
  
  // insert or update a number in the database or save a user specific number
  function save($debug) {
    zu_debug('value->save "'.$this->number.'" for user '.$this->usr->name, $debug-10);
    $result = "";
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;  
    if (isset($this->time_stamp)) {
      $db_con->type = 'value_ts_data';         
    } else {  
      $db_con->type = 'value';         
    }
    
    // rebuild the value ids if needed e.g. if the front end function has just set a list of phrase ids get the responding group
    $result .= $this->set_grp_and_time_by_ids($debug-1);
    
    // check if a new value is supposed to be added
    if ($this->id <= 0) {
      zu_debug('value->save check if a value for "'.$this->name().'" and user '.$this->usr->name.' is already in the database.', $debug-10);
      // check if a value for this words is already in the database
      $db_chk = New value;
      $db_chk->grp_id     = $this->grp_id;
      $db_chk->time_id    = $this->time_id;
      $db_chk->time_stamp = $this->time_stamp;
      $db_chk->usr        = $this->usr;
      $db_chk->load($debug-1);
      if ($db_chk->id > 0) {
        zu_debug('value->save value for "'.$this->grp->name().'"@"'.$this->time_phr->name.'" and user '.$this->usr->name.' is already in the database and will be updated.', $debug-12);
        $this->id = $db_chk->id;
      }
    }  
    
    if ($this->id <= 0) {
      zu_debug('value->save "'.$this->name().'": '.$this->number.' for user '.$this->usr->name.' as a new value.', $debug-10);

      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug('value->save update id '.$this->id.' to save "'.$this->number.'" for user '.$this->usr->id, $debug-10);
      // update a value
      // todo: if noone else has ever changed the value, change to default value, else create a user overwrite

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
        $result .= $this->save_fields     ($db_con, $db_rec, $std_rec, $debug-1);
      }

    }
    return $result;    
  }

      
  // delete the complete value (the calling function del must have checked that no one uses this value)
  function del_exe($debug) {
    zu_debug('value->del_exe.', $debug-16);
    $result = '';

    $db_type = 'value';
    $log = $this->log_del($db_type, $debug-1);
    if ($log->id > 0) {
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      // delete first all user configuration that have also been excluded
      $db_con->type = 'user_value';         
      $result .= $db_con->delete(array('value_id','excluded'), array($this->id,'1'), $debug-1);
      $db_con->type   = $db_type;         
      $result .= $db_con->delete('value_id', $this->id, $debug-1);
    }
    
    return $result;    
  }
  
  /*
  // exclude or delete a value
  function del($debug) {
    zu_debug('value->del.', $debug-16);
    $result = '';
    $result .= $this->load($debug-1);
    if ($this->id > 0 AND $result == '') {
      zu_debug('value->del id '.$this->id, $debug-14);
      if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
        $result .= $this->del_exe($debug-1);
      } else {
        $this->excluded = 1;
        $result .= $this->save($debug-1);        
      }
    }
    return $result;    
  }
  */
  
}

?>
