<?php

/*

  formula_value.php - the calculated numeric result of a formula
  -----------------
  
  todo: add these function
  
  set_dirty_on_value_update  - set all formula result value to dirty that are depending on an updated values via Apache Kafka messages not via database
  set_dirty_on_result_update - set all formula result value to dirty that are depending on an updated formula result
  set_cleanup_prios          - define which formula results needs to be updated first
  cleanup                    - update/calculated all dirty formula results
                               do the cleanup calculations always "in memory" 
                               drop the results in blocks to the database
  

  
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

class formula_value {

  // database fields
  public $id              = NULL;  // the unique id for each formula result 
                                   // (the second unique key is frm_id, src_phr_grp_id, src_time_id, phr_grp_id, time_id, usr_id)
  public $frm_id          = NULL;  // the formula database id used to calculate this result
  public $usr             = NULL;  // the user who wants to see the result because the formula and values can differ for each user; this is 
  public $owner_id        = NULL;  // the user for whom the result has been calculated; if Null the result is the standard result
  public $is_std          = True;  // true as long as no user specific value, formula or assignment is used for this result
  public $src_phr_grp_id  = NULL;  // the word group used for calculating the result
  public $src_time_id     = NULL;  // the time word id for calculating the result
  public $phr_grp_id      = NULL;  // the result word group as saved in the database
  public $time_id         = NULL;  // the result time word id as saved in the database, which can differ from the source time
  public $value           = NULL;  // ... and finally the numeric value
  public $last_update     = NULL;  // ... and the time of the last update; all updates up to this time are included in this result
  public $dirty           = NULL;  // true as long as an update is pending

  // in memory only fields (all methods except load and save should use the wrd_lst object not the ids and not the group id)
  public $frm             = NULL;  // the formula object used to calculate this result
  public $src_phr_lst     = NULL;  // the source word list obj (not a list of word objects) based on which the result has been calculated
  public $src_time_phr    = NULL;  // the time word object created while loading
  public $phr_lst         = NULL;  // the word list obj (not a list of word objects) filled while loading
  public $time_phr        = NULL;  // the time word object created while loading
  public $val_missing     = False; // true if at least one of the formula values is not set which means is NULL (but zero is a value)
  public $is_updated      = False; // true if the formula value has been calculated, but not yet saved
  public $ref_text        = '';    // the formula text in the database reference format on which the result is based
  public $num_text        = '';    // the formula text filled with numbers used for the result calculation
  public $last_val_update = NULL;  // the time of the last update of an underlying value, formula result or formula
                                   // if this is later than the last update the result needs to be updated

  public $wrd             = NULL;  // to get the most interesting result for this word

  // to be dismissed
  public $wrd_ids        = array(); // a array of word ids filled while loading the formula value to the memory
  
  // load the record from the database
  // in a separate function, because this can be called twice from the load function
  private function load_rec($sql_where, $debug) {
    global $db_con;

    $sql = "SELECT formula_value_id,
                    user_id,
                    formula_id,
                    source_phrase_group_id,
                    source_time_word_id,
                    phrase_group_id,
                    time_word_id,
                    last_update,
                    formula_value
              FROM formula_values 
              WHERE ".$sql_where.";";
    zu_debug('formula_value->load ('.$sql.' for user '.$this->usr->id.')', $debug-10);
    //$db_con = new mysql;
    $db_con->usr_id = $this->usr->id;         
    $val_rows = $db_con->get($sql, $debug-5);  
    if (count($val_rows) > 0) {
      $val_row = $val_rows[0];
      if ($val_row['formula_value_id'] <= 0) {
        $this->id = 0;
      } else {
        $this->id              = $val_row['formula_value_id'];
        $this->frm_id          = $val_row['formula_id'];
        $this->owner_id        = $val_row['user_id'];
        $this->src_phr_grp_id  = $val_row['source_phrase_group_id'];
        $this->src_time_id     = $val_row['source_time_word_id'];
        $this->phr_grp_id      = $val_row['phrase_group_id'];
        $this->time_id         = $val_row['time_word_id'];
        $this->last_update     = $val_row['last_update'];
        $this->last_val_update = $val_row['last_update'];
        $this->value           = $val_row['formula_value'];
      }
    }
  }
  
  // load the missing formula parameters from the database
  // to do: load user specific values
  function load($debug) {

    global $db_con;

    // check the all minimal input parameters
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a result.", "formula_value->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      
      // prepare the selection of the result
      $sql_where = "";
      if ($this->id > 0) {
        $sql_where = "formula_value_id = ".$this->id;
      } else {
        // prepare the search by getting the word group based on the word list
        zu_debug('formula_value->load not by id', $debug-14);

        // assume the source time if the source list is set, but not the source time
        if ($this->src_time_id <= 0 AND is_null($this->src_time_phr) AND !empty($this->src_phr_lst)) {
          $work_phr_lst = clone $this->src_phr_lst;
          $src_time_phr_lst = $work_phr_lst->time_lst($debug-1);
          if (count($src_time_phr_lst->lst) > 0) {
            $src_time_phr = $src_time_phr_lst->lst[0];
            $this->src_time_id = $src_time_phr->id;
            zu_debug('formula_value->load -> source time '.$this->src_time_id.' found for '.$src_time_phr_lst->name(), $debug-10); 
          } 
        } elseif ($this->src_time_id <= 0 AND !is_null($this->src_time_phr)) {
          $this->src_time_id = $this->src_time_phr->id;
        }

        // set the source group id if the source list is set, but not the group id
        $phr_grp = Null;
        if ($this->src_phr_grp_id <= 0 AND !empty($this->src_phr_lst->lst)) {
          $work_phr_lst = clone $this->src_phr_lst;
          $work_phr_lst->ex_time($debug-1); 
          $this->src_phr_lst = $work_phr_lst;
          $phr_grp = $work_phr_lst->get_grp($debug-1);
          if (isset($phr_grp)) { if ($phr_grp->id > 0) { $this->src_phr_grp_id = $phr_grp->id; } }            
          zu_debug('formula_value->load -> source group '.$this->src_phr_grp_id.' found for '.$work_phr_lst->name(), $debug-10);
        } 
        
        // assume the result time if the result phrase list is set, but not the result time
        if ($this->time_id <= 0 AND is_null($this->time_phr) AND !empty($this->phr_lst)) {
          $work_phr_lst = clone $this->phr_lst;
          $time_phr_lst = $work_phr_lst->time_lst($debug-1);
          if (count($time_phr_lst->lst) > 0) {
            $time_wrd = $time_phr_lst->lst[0];
            $this->time_id = $time_wrd->id;
            zu_debug('formula_value->load -> time '.$this->time_id.' found for '.$time_phr_lst->name(), $debug-10);
          } 
        } elseif ($this->time_id <= 0 AND !is_null($this->time_phr)) {
          $this->time_id = $this->time_phr->id;
        } 
        
        // set the result group id if the result list is set, but not the group id
        $phr_grp = Null;
        if ($this->phr_grp_id <= 0) {
          $phr_lst = Null;
          if(!empty($this->phr_lst->lst)) {
            $phr_lst = clone $this->phr_lst;
            $phr_lst->ex_time($debug-1); 
            zu_debug('formula_value->load -> get group by '.$phr_lst->name(), $debug-12);
          // ... or based on the phrase ids
          } elseif (!empty($this->wrd_ids)) {
            $phr_lst = New phrase_list;
            $phr_lst->ids = $this->wrd_ids;
            $phr_lst->usr = $this->usr;
            $phr_lst->load($debug-1);
            zu_debug('formula_value->load -> get group by ids '.implode(','.$phr_lst->ids), $debug-12);
          // ... or to get the most interesting result for this word
          } elseif (isset($this->wrd) AND isset($this->frm)) {
            if ($this->wrd->id > 0 AND $this->frm->id > 0 AND isset($this->frm->name_wrd)) {
              // get the best matching word group
              $phr_lst = New phrase_list;
              $phr_lst->usr = $this->usr;
              $phr_lst->add($this->wrd->phrase($debug-1));
              $phr_lst->add($this->frm->name_wrd->phrase($debug-1));
              zu_debug('formula_value->load -> get group by words '.$phr_lst->name(), $debug-12);
            }
          }
          if (isset($phr_lst)) {
            $this->phr_lst = $phr_lst;
            zu_debug('formula_value->load get group for '.$phr_lst->name().' (including formula name)', $debug-12);
            $phr_grp = $phr_lst->get_grp($debug-1);
            if (isset($phr_grp)) {
              if ($phr_grp->id > 0) {
                $this->phr_grp_id = $phr_grp->id;
              }
            }
          }
        }
        if ($this->phr_grp_id <= 0) {
          zu_debug('formula_value->load group not found!', $debug-12);
        }

        // include the source words in the search if requested
        if ($this->src_time_id > 0) {
          $sql_src_time = " source_time_word_id = ".$this->src_time_id." ";
        } else { 
          $sql_src_time = " (source_time_word_id = 0 OR source_time_word_id IS NULL) ";
        }
        if ($this->src_phr_grp_id > 0 AND $this->usr->id > 0) {
          $sql_src_wrd = " AND source_phrase_group_id = ".$this->src_phr_grp_id."
                           AND (user_id = ".$this->usr->id." OR user_id = 0 OR user_id IS NULL) AND ";
          $sql_order = " ORDER BY user_id DESC";
        } else {
          if ($this->src_phr_grp_id > 0) {
            $sql_src_wrd = " AND source_phrase_group_id = ".$this->src_phr_grp_id." AND ";
            $sql_order = " ORDER BY user_id";
          } else {
            $sql_src_wrd = "";
          }
        }
        // and include the result words in the search, because one source word list can result to two result word
        // e.g. one time specific and one general
        if ($this->time_id > 0) {
          $sql_time = " time_word_id = ".$this->time_id." ";
        } else { 
          $sql_time = " (time_word_id = 0 OR time_word_id IS NULL) ";
        }
        // select the result based on words
        $sql_wrd = "";
        if ($this->phr_grp_id > 0 AND $this->usr->id > 0) {
          $sql_wrd     = " AND phrase_group_id = ".$this->phr_grp_id."
                          AND (user_id = ".$this->usr->id." OR user_id = 0 OR user_id IS NULL)";
          $sql_order = " ORDER BY user_id DESC";
        } else {
          if ($this->phr_grp_id > 0) {
            $sql_wrd    = " AND phrase_group_id = ".$this->phr_grp_id." ";
            $sql_order = "ORDER BY user_id";
          } 
        }
        // include the formula in the search
        if ($this->frm_id > 0) {
          $sql_frm = " AND formula_id = ".$this->frm_id." ";
        } else {
          $sql_frm = " ";
        }
        //zu_debug('formula_value->load for '.$wrd->name.' and '.$this->id, $debug-14);
        if ($sql_src_wrd <> '' AND $sql_wrd <> '') {
          $sql_where = $sql_src_time
                    . $sql_src_wrd
                    . $sql_time
                    . $sql_wrd
                    . $sql_frm
                    . $sql_order;
        } elseif ($sql_wrd <> '') {
          // if only the target value list is set, get the "best" result
          // to do: define what is the best result
            $sql_where = $sql_time
                      . $sql_wrd
                      . $sql_frm
                      . $sql_order;
        }
      }

      // check if a valid identification is given and load the result
      if ($sql_where == '') {
        zu_err("Either the database ID (".$this->id.") or the source or result words or word group and the user (".$this->usr->id.") must be set to load a result.", "formula_value->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {  
        $this->load_rec($sql_where, $debug);
        
        // if no general value can be found, test if a more specific value can be found in the database
        // e.g. if ABB,Sales,2014 is requested, but there is only a value for ABB,Sales,2014,CHF,million get it
        // similar to the selection in value->load: maybe combine?
        zu_debug('formula_value->load check best guess', $debug-10);
        if ($this->id <= 0) {
          if (!isset($phr_lst)) {
            zu_debug('formula_value->no formula value found for '.$sql_where.', but phrase list is also not set', $debug-10);
          } else {
            zu_debug('formula_value->load try best guess', $debug-10);
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
              $sql_val = "SELECT formula_value_id 
                            FROM formula_values
                          WHERE phrase_group_id IN (".$sql_grp.") ".$sql_time.";";
              zu_debug('formula_value->load sql val "'.$sql_val.'"', $debug-12);
              //$db_con = new mysql;
              $db_con->usr_id = $this->usr->id;         
              $val_ids_rows = $db_con->get($sql_val, $debug-5);  
              if (count($val_ids_rows) > 0) {
                $val_id_row = $val_ids_rows[0];
                $this->id = $val_id_row['formula_value_id'];
                if ($this->id > 0) {
                  $sql_where = "formula_value_id = ".$this->id;
                  $this->load_rec($sql_where, $debug);
                  zu_debug('formula_value->load best guess id ('.$this->id.')', $debug-10);
                }
              }
            } 
          }
        }

        zu_debug('formula_value->load words', $debug-10);      
        $this->load_phrases($debug-1);
      }  
      zu_debug('formula_value->load got id '.$this->id.': '.$this->value, $debug-10);
    }
  }
  
  /* 
     word loading methods
     --------------------
  */
  
  // update the word list based on the source word group id ($this->phr_lst)
  private function load_phr_lst_src($debug) {
    if ($this->src_phr_grp_id > 0) {
      zu_debug('formula_value->load_phr_lst_src for source group "'.$this->src_phr_grp_id.'"', $debug-12);
      // to review to reduce the number of loads AND check if load is really needed correctly
      //if (!isset($this->src_phr_lst)) {
        $do_load = false;
        $phr_grp = New phrase_group;
        $phr_grp->id  = $this->src_phr_grp_id;
        $phr_grp->usr = $this->usr;
        $phr_grp->load($debug-1);
        $phr_grp->load_lst($debug-1);
        if (isset($phr_grp->phr_lst)) {
          $this->src_phr_lst = $phr_grp->phr_lst;
          zu_debug('formula_value->load_phr_lst_src source words '.$this->src_phr_lst->name().' loaded', $debug-10);
        } else {
          zu_debug('formula_value->load_phr_lst_src no source words found for '.$this->dsp_id(), $debug-10);
        }
      //}
    }  
    if (!isset($this->src_phr_lst)) {
      zu_warning("Missing source words for the calculated value ".$this->id.' (group id '.$this->src_phr_grp_id.').', "formula_value->load_phr_lst_src", '', (new Exception)->getTraceAsString(), $this->usr);
    }
  }
  
  // update the word list based on the word group id ($this->phr_lst)
  private function load_phr_lst($debug) {
    if ($this->phr_grp_id > 0) {
      zu_debug('formula_value->load_phr_lst for group "'.$this->phr_grp_id.'"', $debug-12);
      //if (!isset($this->phr_lst)) {
        $phr_grp = New phrase_group;
        $phr_grp->id  = $this->phr_grp_id;
        $phr_grp->usr = $this->usr;
        $phr_grp->load($debug-1);
        $phr_grp->load_lst($debug-1);
        if (isset($phr_grp->phr_lst)) {
          $this->phr_lst = $phr_grp->phr_lst;
          zu_debug('formula_value->load_phr_lst words '.$this->phr_lst->name().' loaded', $debug-10);
          // to be dismissed
          $this->wrd_ids = $phr_grp->phr_lst->ids;
        } else {
          zu_debug('formula_value->load_phr_lst no result words found for '.$this->dsp_id(), $debug-10);
        }
      //}
    }
    if (!isset($this->phr_lst)) {
      zu_warning("Missing result words for the calculated value ".$this->id,"formula_value->load_phr_lst", '', (new Exception)->getTraceAsString(), $this->usr);
    }  
  }
  
  // update the source time word object based on the source time word id ($this->src_time_phr)
  private function load_time_wrd_src($debug) {
    if ($this->src_time_id <> 0) {
      zu_debug('formula_value->load_time_wrd_src for source time "'.$this->src_time_id.'"', $debug-12);
      //if (!isset($this->src_time_phr)) {
        $time_phr = new phrase;
        $time_phr->id  = $this->src_time_id;
        $time_phr->usr = $this->usr;
        $time_phr->load($debug-1);
        if (isset($time_phr)) {
          $this->src_time_phr = $time_phr;
          if (isset($this->src_phr_lst)) {
            $this->src_phr_lst->add($time_phr, $debug-1);
            zu_debug('formula_value->load_time_wrd_src source time word "'.$time_phr->name.'" added', $debug-10);
          }
        }
      //}
    }  
  }
  
  // update the time word object based on the time word id ($this->time_phr)
  private function load_time_wrd($debug) {
    if ($this->time_id <> 0) {
      zu_debug('formula_value->load_phr_lst for time "'.$this->time_id.'"', $debug-12);
      //if (!isset($this->time_phr)) {
        $time_phr = new phrase;
        $time_phr->id  = $this->time_id;
        $time_phr->usr = $this->usr;
        $time_phr->load($debug-1);
        if (isset($time_phr)) {
          $this->time_phr = $time_phr;
          if (isset($this->phr_lst)) {
            $this->phr_lst->add($time_phr, $debug-1);
            zu_debug('formula_value->load_time_wrd time word "'.$time_phr->name.'" added', $debug-10);
          }
        }
      //}
    }  
  }
  
  // update the word objects based on the word ids  (usually done after loading the formula result from the database)
  function load_phrases($debug) {
    if ($this->id > 0) {
      zu_debug('formula_value->load_phrases for user '.$this->usr->name, $debug-14);
      $this->load_phr_lst_src($debug-1);
      $this->load_phr_lst($debug-1);
      $this->load_time_wrd_src($debug-1);
      $this->load_time_wrd($debug-1);
    }
  }
  
  // update the formulas objects based on the id
  private function load_formula($debug) {
    if ($this->frm_id > 0) {
      zu_debug('formula_value->load_formula for user '.$this->usr->name, $debug-14);
      $frm = New formula;
      $frm->id  = $this->frm_id;
      $frm->usr = $this->usr;
      $frm->load($debug-1);
      $this->frm = $frm;
    }
  }
  
  /* 
     methods to prepare the words for saving into the database
     ---------------------------------------------------------
  */
  
  // update the source word group id based on the word list ($this->phr_lst)
  private function save_prepare_phr_lst_src($debug) {
    if (isset($this->src_phr_lst)) {
      $this->src_phr_lst->load($debug-1);
      // remember the time if needed (but don't assume the time, because a value can be saved without timestamp)
      // separate the time word if not done already to reduce the number of word groups created and increase the request speed
      $time_phr_lst = $this->src_phr_lst->time_lst ($debug-1);
      if (count($time_phr_lst->lst) > 1) {
        zu_warning('More than one time word is not yet supported '.$time_phr_lst->name().' ('.$this->id.') is empty.', 'formula_value->save_prepare_phr_lst_src', '', (new Exception)->getTraceAsString(), $this->usr);
      }
      if (count($time_phr_lst->lst) == 1) {
        $time_wrd = $time_phr_lst->lst[0];
        if (isset($this->src_time_phr)) {
          if ($this->src_time_phr->id <> $time_wrd->id) {
            zu_warning('The word list suggested "'.$time_wrd->name.'", but the time is already set to  "'.$this->src_time_phr->name.'" ('.$this->id.').', 'formula_value->save_prepare_phr_lst_src', '', (new Exception)->getTraceAsString(), $this->usr);
          }
        } else {
          $this->src_time_phr = $time_wrd;
        }
      }
      // exclude all time words before the word group creation
      $this->src_phr_lst->ex_time ($debug-1);
      // get the word group id (and create the group if needed)
      // to do: include triples
      if (count($this->src_phr_lst->ids) > 0) {
        zu_debug("formula_value->save_prepare_phr_lst_src -> source group for ".$this->src_phr_lst->dsp_id().".", $debug-10);
        $grp = New phrase_group;
        $grp->usr = $this->usr;
        $grp->ids = $this->src_phr_lst->ids;
        $this->src_phr_grp_id = $grp->get_id ($debug-1);
      }
      zu_debug("formula_value->save_prepare_phr_lst_src -> source group id ".$this->src_phr_grp_id." for ".$this->src_phr_lst->name($debug-1).".", $debug-10);
    }
  }
  
  // update the word group id based on the word list ($this->phr_lst)
  private function save_prepare_phr_lst($debug) {
    if (isset($this->phr_lst)) {
      // remember the time if needed (but don't assume the time, because a value can be saved without timestamp)
      // separate the time word if not done already to reduce the number of word groups created and increase the request speed
      $time_phr_lst = $this->phr_lst->time_lst ($debug-1);
      if (count($time_phr_lst->lst) > 1) {
        zu_warning('More than one time word is not yet supported '.$time_phr_lst->name().' ('.$this->id.') is empty.', 'formula_value->save_prepare_phr_lst', '', (new Exception)->getTraceAsString(), $this->usr);
      }
      if (count($time_phr_lst->lst) == 1) {
        $time_wrd = $time_phr_lst->lst[0];
        if (isset($this->time_phr)) {
          if ($this->time_phr->id <> $time_wrd->id) {
            zu_warning('The word list suggested "'.$time_wrd->name.'", but the time is already set to  "'.$this->time_phr->name.'" ('.$this->id.').', 'formula_value->save_prepare_phr_lst', '', (new Exception)->getTraceAsString(), $this->usr);
          }
        } else {
          $this->time_phr = $time_wrd;
        }
      }
      // exclude all time words before the word group creation
      $this->phr_lst->ex_time ($debug-1);
      // get the word group id (and create the group if needed)
      // to do: include triples
      $grp = New phrase_group;
      $grp->usr = $this->usr;
      $grp->ids = $this->phr_lst->ids;
      $this->phr_grp_id = $grp->get_id ($debug-1);
      zu_debug("formula_value->save_prepare_phr_lst -> group id ".$this->phr_grp_id." for ".$this->phr_lst->name($debug-1).".", $debug-10);
      // to be dismissed
      $this->wrd_ids = $this->phr_lst->ids;
    }
  }
  
  // update the source time word id based on the source time word object ($this->src_time_phr)
  private function save_prepare_time_wrd_src($debug) {
    if (isset($this->src_time_phr)) {
      $this->src_time_id = $this->src_time_phr->id;
    }
  }
  
  // update the time word id based on the time word object ($this->time_phr)
  private function save_prepare_time_wrd($debug) {
    if (isset($this->time_phr)) {
      $this->time_id = $this->time_phr->id;
    }
  }
  
  // update the word ids based on the word objects (usually done before saving the formula result to the database)
  private function save_prepare_wrds($debug) {
    zu_debug("formula_value->save_prepare_wrds.", $debug-14);
    $this->save_prepare_phr_lst_src($debug-1);
    $this->save_prepare_phr_lst($debug-1);
    zu_debug("formula_value->save_prepare_wrds source done.", $debug-12);
    $this->save_prepare_time_wrd_src($debug-1);
    $this->save_prepare_time_wrd($debug-1);
    zu_debug("formula_value->save_prepare_wrds done.", $debug-12);
  }
  
  // depending on the word list format the numeric value
  // similar to the corresponding function in the "value" class
  function val_formatted($debug) {
    $result = '';

    if (!is_null($this->value)) {
      zu_debug('formula_value->val_formatted', $debug-12);
      if (!isset($this->phr_lst)) {
        $this->load($debug-1);
        zu_debug('formula_value->val_formatted loaded', $debug-12);
      }
      zu_debug('formula_value->val_formatted check '.$this->dsp_id(), $debug-12);
      if ($this->phr_lst->has_percent($debug-1)) {
        $result = round($this->value*100,2).' %';
        zu_debug('formula_value->val_formatted percent of '.$this->value, $debug-12);
      } else {
        if ($this->value >= 1000 OR $this->value <= -1000) {
          zu_debug('formula_value->val_formatted format', $debug-12);
          $result .= number_format($this->value,0,$this->usr->dec_point,$this->usr->thousand_sep);
        } else {  
          zu_debug('formula_value->val_formatted round', $debug-12);
          $result = round($this->value,2);
        }
      } 
    }
    zu_debug('formula_value->val_formatted done', $debug-12);
    return $result; 
  }
  
  // create and return the figure object for the value
  function figure($debug) {
    $fig = New figure;
    $fig->id          = $this->id;
    $fig->usr         = $this->usr;
    $fig->type        = 'result';
    $fig->number      = $this->value;
    $fig->last_update = $this->last_update;
    $fig->obj         = $this;
      
    return $fig;
  }
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
  function dsp_id () {
    $result = '';

    if (isset($this->phr_lst)) {
      $result .= $this->phr_lst->dsp_id();
    }
    if (isset($this->time_phr)) {
      $result .= '@'.$this->time_phr->dsp_id();
    }
    if ($result <> '') {
      $result .= ' ('.$this->id.')';
    } else {
      $result .= $this->id;
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }
    return $result;
  }

  // this function is called from dsp_id, so no other call is allowed
  function name($back, $debug) {
    $result = ''; 

    if (isset($this->phr_lst)) {
      $result .= $this->phr_lst->name($debug-1);
    }
    if (isset($this->time_phr)) {
      $result .= '@'.$this->time_phr->name($debug-1);
    }
    
    return $result;
  }
  
  function name_linked($back, $debug) {
    zu_debug('formula_value->name_linked ', $debug-10);
    $result = ''; 

    if (isset($this->phr_lst)) {
      $result .= $this->phr_lst->name_linked($debug-1);
    }
    if (isset($this->time_phr)) {
      $result .= '@'.$this->time_phr->name_linked($debug-1);
    }
    
    zu_debug('formula_value->name done', $debug-10);
    return $result;
  }
  
  // html code to show the value with the indication if the value is influence by the user input
  function display($back, $debug) {
    if (!is_null($this->value)) {
      $num_text = $this->val_formatted($debug-1);
      if ($this->owner_id > 0) {
        $result = '<font class="user_specific">'.$num_text.'</font>'."\n";
      } else {  
        $result = $num_text."\n";
      }  
    }
    return $result; 
  }
  
  // html code to show the value with the possibility to click for the result explanation
  function display_linked($back, $debug) {
    if (!is_null($this->value)) {
      $num_text = $this->val_formatted($debug-1);
      $link_format = '';
      if ($this->owner_id > 0) {
        $link_format = ' class="user_specific"';
      }
      // to review
      $lead_phr_id = $this->wrd_ids[0];
      $result = '<a href="/http/formula_result.php?id='.$this->id.'&phrase='.$lead_phr_id.'&group='.$this->phr_grp_id.'&back='.$back.'"'.$link_format.'>'.$num_text.'</a>';
    }
    return $result; 
  }
  
  // explain a formula result to the user
  // create a HTML page that shows different levels of detail information for one formula result to explain to the user how the value is calculated
  function explain($lead_phr_id, $back, $debug) {
    zu_debug('formula_value->explain '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $result = '';
    
    // display the leading word
    // $lead_wrd = 
    // $lead_wrd->id  = $lead_phr_id;
    // $lead_wrd->usr = $this->usr;
    // $lead_wrd->load($debug-1);
    //$result .= $lead_phr_id->name;

    // build the title
    $title = '';
    // add the words that specify the calculated value to the title
    $val_phr_lst = clone $this->phr_lst;
    $val_wrd_lst = $val_phr_lst->wrd_lst_all($debug-1);
    $val_wrd_lst->add($this->time_phr, $debug-5);
    $title .= implode(",",$val_wrd_lst->names_linked_ex_measure_and_time($debug-1));
    $time_phr = implode(",",$val_wrd_lst->names_linked_time($debug-1));
    if ($time_phr <> '') { $title .= ' ('.$time_phr.')'; }
    $title .= ': ';
    // add the value  to the title
    $title .= $this->display($back, $debug-1);
    $result .= dsp_text_h1 ($title);
    zu_debug('formula_value->explain -> explain the value for '.$val_phr_lst->name().' based on '.$this->src_phr_lst->name(), $debug-1);

    // display the measure and scaling of the value
    if ($val_wrd_lst->has_percent($debug-1)) {
      $result .= 'from '.implode(",",$val_wrd_lst->names_linked_measure($debug-1));
    } else {
      $result .= 'in '.implode(",",$val_wrd_lst->names_linked_measure($debug-1));
    }
    $result .= '</br></br>'."\n";

    // display the formula with links
    $frm = New formula;
    $frm->id  = $this->frm_id;
    $frm->usr = $this->usr;
    $frm->load($debug-1);
    $result .= ' based on</br>'.$frm->name_linked($back);
    $result .= ' '.$frm->dsp_text($back, $debug-1)."\n";
    $result .= ' '.$frm->btn_edit($back, $debug-1)."\n";
    $result .= '</br></br>'."\n";

    // load the formula element groups
    // each element group can contain several elements
    // e.g. for <journey time premium offset = "journey time average" / "journey time max premium" "percent"> 
    // <"journey time max premium" "percent"> is one element group with two elements
    // and these two elements together are use to select the value
    $exp = $frm->expression($debug-1);
    //$elm_lst = $exp->element_lst ($back, $debug-1);
    $elm_grp_lst = $exp->element_grp_lst ($back, $debug-1);
    zu_debug("formula_value->explain -> elements loaded (".count($elm_grp_lst->lst)." for ".$frm->ref_text.")", $debug-10);

    $result .= ' where</br>';

    // check the element consistency and if it fails, create a warning
    if (!isset($this->src_phr_lst)) {
      zu_warning("Missing source words for the calculated value ".$this->dsp_id(),"formula_value->explain", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      $elm_nbr = 0;
      foreach ($elm_grp_lst->lst AS $elm_grp) {

        // display the formula element names and create the element group object
        $result .= $elm_grp->dsp_names($back, $debug-1).' ';
        zu_debug('formula_value->explain -> elm grp name "'.$elm_grp->dsp_names($back, $debug-1).'" with back "'.$back.'"', $debug-10);
      

        // exclude the formula word from the words used to select the formula element values
        // so reverse what has been done when saving the result
        $src_phr_lst = clone $this->src_phr_lst;
        $frm_wrd_id = $frm->name_wrd->id;
        $src_phr_lst->diff_by_ids(array($frm_wrd_id), $debug-1);
        zu_debug('formula_value->explain -> formula word "'.$frm->name_wrd->name.'" excluded from '.$src_phr_lst->name($debug-1), $debug-10);

        // select or guess the element time word if needed
        zu_debug('formula_value->explain -> guess the time ... ', $debug-10);
        if ($this->src_time_id <= 0) {
          if ($this->time_id > 0) {
            $elm_time_phr = $this->time_phr; 
            zu_debug('formula_value->explain -> time '.$this->time_phr->name.' taken from the result', $debug-3);
          } else {  
            $elm_time_phr = $src_phr_lst->assume_time($debug-1); 
            zu_debug('formula_value->explain -> time '.$elm_time_phr->name.' assumed', $debug-3);
          }  
        } else {
          $elm_time_phr = $this->src_time_phr; 
          zu_debug('formula_value->explain -> time '.$elm_time_phr->name.' taken from the source', $debug-3);
        }
        
        $elm_grp->phr_lst  = $src_phr_lst;
        $elm_grp->time_phr = $elm_time_phr;
        $elm_grp->usr      = $this->usr;
        zu_debug('formula_value->explain -> words set '.$elm_grp->phr_lst->name($debug-1).' taken from the source and user "'.$elm_grp->usr->name.'"', $debug-3);
        
        // finally display the value used in the formula
        $result .= ' = '.$elm_grp->dsp_values($back, $this->time_phr, $debug-1);
        $result .= '</br>';
        zu_debug('formula_value->explain -> next element', $debug-5);
        $elm_nbr++;
      }
    }  

    return $result;
  }

  // update (calculate) all formula values that are depending 
  // e.g. if the PE ratio for ABB, 2018 has been updated, 
  //      the target price for ABB, 2018 needs to be updated if it is based on the PE ratio
  // so:  get a list of all formulas, where the formula value is used 
  //      based on the frm id and the word group
  function update_depending($debug) {
    zu_debug("formula_value->update_depending (f".$this->frm_id.",t".implode(",",$this->wrd_ids).",tt".$this->time_id.",v".$this->value." and user ".$this->usr->name.")", $debug-10);

    global $db_con;
    $result = array();
    
    // get depending formulas
    $frm_ids = array();
    $frm_elm_type = cl(SQL_FORMULA_PART_TYPE_FORMULA);
    $sql = "SELECT formula_id
              FROM formula_elements 
             WHERE ref_id = ".$this->frm_id."
               AND formula_element_type_id = ".$frm_elm_type.";";
    //$db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $frm_rows = $db_con->get($sql, $debug-5);  
    foreach ($frm_rows AS $frm_row) {
      $frm_ids[] = $frm_row['formula_id'];
    }
    // get formula results that may need an update (maybe include also word groups that have any word of the updated word group)
    if (!empty($frm_ids)) {  
      $sql = "SELECT formula_value_id,
                FROM formula_values 
               WHERE formula_id IN (".implode(",",$frm_ids).")
                 AND phrase_group_id = ".$this->phr_grp_id."
                 AND time_word_id    = ".$this->time_id."
                 AND user_id         = ".$this->usr->id.";";
      //$db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $val_rows = $db_con->get($sql, $debug-5);  
      foreach ($val_rows AS $val_row) {
        $frm_ids[] = $frm_row['formula_id'];
        $fv_upd = New formula_value;
        $fv_upd->usr = $this->usr;
        $fv_upd->id = $val_row['formula_value_id'];
        $fv_upd->load($debug-1);
        $fv_upd->update($debug-1);
        // if the value is really updated, remember the value is to check if this triggers more updates
        $result[] = $fv_upd->save($debug-1);
      }
    }

    return $result;
  }

  // update the result of this formula value (without loading or saving)
  function update($debug) {
    zu_debug('formula_value->update '.$this->dsp_id(), $debug-10);
    // check parameters
    if (!isset($this->phr_lst)) {
      zu_err("Phrase list is missing.","formula_value->update", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->frm_id <= 0) {
      zu_err("Formula ID is missing.","formula_value->update", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      // prepare update
      $this->load_phrases($debug-1);
      $this->load_formula($debug-1);
      
      $frm = $this->frm;
      $phr_lst = $this->src_phr_lst;
      $frm->calc($phr_lst, $debug-1);

      //$this->save_if_updated ($debug-1);
      zu_debug('formula_value->update '.$this->dsp_id().' to '.$this->value.' done', $debug-10);
    }
  }
  
  // check if a single formula result needs to be saved to the database
  function save_if_updated($debug) {
    // don't save the result if some needed numbers are missing
    if ($this->val_missing) {
      zu_debug('Some values are missing for '.$this->dsp_id(), $debug-6);
    } else {
      // save only if any parameter has been updated since last calculation
      if ($this->last_val_update <= $this->last_update) {
        if (isset($this->last_val_update) AND isset($this->last_update)) {
          zu_debug('formula_value->save_if_updated -> '.$this->dsp_id().' not saved because the result has been calculated at '.$this->last_update->format('Y-m-d H:i:s').' and after the last parameter update '.$this->last_val_update->format('Y-m-d H:i:s'), $debug-13);
        } else {
          zu_debug('formula_value->save_if_updated -> '.$this->dsp_id().' not saved because the result has been calculated after the last parameter update ', $debug-13);
        }  
        //zu_debug('formula_value->save_if_updated -> save '.$this->dsp_id().' not saved because the result has been calculated at '.$this->last_update.' which is after the last parameter update at '.$this->last_update, $debug-13);
      } else {
        if (isset($this->last_val_update) AND isset($this->last_update)) {
          zu_debug('formula_value->save_if_updated -> save '.$this->dsp_id().' because parameters have been updated at '.$this->last_val_update->format('Y-m-d H:i:s').' and the formula result update is from '.$this->last_update->format('Y-m-d H:i:s'), $debug-13);
        } else {
          if (isset($this->last_val_update)) {
            zu_debug('formula_value->save_if_updated -> save '.$this->dsp_id().' and result update time is set to '.$this->last_val_update->format('Y-m-d H:i:s'), $debug-13);
            $this->last_update = $this->last_val_update;
          } else {
            zu_debug('formula_value->save_if_updated -> save '.$this->dsp_id().' but times are missing', $debug-13);
          }  
        }
        // check the formula result consistency
        if (!isset($this->phr_lst))     { zu_warning('The result phrases for '.$this->dsp_id().' are missing.', 'formula_value->save_if_updated', '', (new Exception)->getTraceAsString(), $this->usr); }
        if (!isset($this->src_phr_lst)) { zu_warning('The source phrases for '.$this->dsp_id().' are missing.', 'formula_value->save_if_updated', '', (new Exception)->getTraceAsString(), $this->usr); }
        
        // add the formula name word
        // e.g. if the formula "country weight" is calculated the word "country weight" should be added to the result values
        zu_debug('formula_value->save_if_updated -> add the formula name '.$this->frm->dsp_id().' to the result phrases '.$this->phr_lst->dsp_id(), $debug-13);
        $this->phr_lst->add($this->frm->name_wrd->phrase($debug-1), $debug-1);

        // e.g. if the formula is a division and the values used have a measure word like meter or CHF, the result is only in percent, but not in meter or CHF
        // simplified version, that needs to be review to handle more complex formulas
        if (strpos($this->frm->ref_text_r, ZUP_OPER_DIV) !== false) {
          zu_debug('formula_value->save_if_updated -> check measure '.$this->phr_lst->dsp_id(), $debug-9);
          if ($this->phr_lst->has_measure($debug-1)) {
            $this->phr_lst->ex_measure($debug-1);
            zu_debug('formula_value->save_if_updated -> measure removed from words '.$this->phr_lst->dsp_id(), $debug-11);
          }
        }

        // build the formula result object
        //$this->frm_id = $this->frm->id;
        //$this->usr->id = $frm_result->result_user;
        zu_debug('formula_value->save_if_updated -> save "'.$this->value.'" for '.$this->phr_lst->dsp_id(), $debug-7);
        
        // get the default time for the words e.g. if the increase for ABB sales is calculated the last reported sales increase is assumed
        $lst_ex_time = $this->phr_lst->wrd_lst_all($debug-1);
        $lst_ex_time->ex_time($debug-1);
        $fv_default_time = $lst_ex_time->assume_time($debug-1); // must be the same function called used in 2num
        if (isset($fv_default_time)) {
          zu_debug('formula_value->save_if_updated -> save "'.$this->value.'" for '.$this->phr_lst->dsp_id().' and default time '.$fv_default_time->dsp_id(), $debug-9);
        } else {
          zu_debug('formula_value->save_if_updated -> save "'.$this->value.'" for '.$this->phr_lst->dsp_id(), $debug-9);
        }
        
        if (!isset($this->value)) {
          //zu_info('No result calculated for "'.$this->frm->name.'" based on '.$this->src_phr_lst->dsp_id().' for user '.$this->usr->id.'.', "formula_value->save_if_updated", '', (new Exception)->getTraceAsString(), $this->usr);
        } else {
          // save the default value if the result time is the "newest"
          if (isset($fv_default_time)) {
            zu_debug('check if result time '.$this->time_phr->dsp_id().' is the default time '.$fv_default_time->dsp_id(), $debug-6);
            if ($this->time_phr->id == $fv_default_time->id) {
              // if there is not yet a general value for all user, save it now
              
              $fv_no_time = clone $this;
              $fv_no_time->src_time_phr = Null;
              $fv_no_time->time_phr     = Null;
              $fv_id_no_time = $fv_no_time->save($debug-1);
              if ($debug > 6) {
                zu_debug('result = '.$fv_no_time->value.' saved without time for '.$fv_no_time->phr_lst->dsp_id().' as id "'.$fv_id_no_time.'" based on '.$fv_no_time->src_phr_lst->dsp_id().' for user '.$fv_no_time->usr->id.', because default time is '.$fv_default_time->name.' for '.$fv_no_time->phr_lst->dsp_id().' for '.$fv_no_time->phr_lst->dsp_id(), $debug-1);
              } else {
                zu_debug('result = '.$this->value.' saved without time for '.$this->phr_lst->name_linked(), $debug-1);
              }
            }  
          }
          // save the result
          $fv_id = $this->save($debug-1);
          
          if ($debug > 0) {
            $debug_txt = 'result = '.$this->value.' saved for '.$this->phr_lst->name_linked();
            if ($debug > 3) {
              $debug_txt .= ' (group id "'.$this->phr_grp_id.'" and the result time is '.$this->time_phr->name_linked().') as id "'.$fv_id.'" based on '.$this->src_phr_lst->name_linked().' (group id "'.$this->src_phr_grp_id.'" and the result time is '.$this->src_time_phr->name_linked().')';
            }
            if (!$this->is_std) {
              $debug_txt .= ' for user "'.$this->usr->name.'"';
            }
            zu_debug($debug_txt.'', $debug);
          }  
        }
      }
    }
    return $this; 
  }
  
  // save the formula result to the database
  // for the word selection the id list is the lead, not the object list and not the group
  function save($debug) {

    global $db_con;

    // check the parameters e.g. a result must always be linked to a formula
    if ($this->frm_id <= 0 ) {
      zu_err("Formula id missing.","formula_value->save", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif (empty($this->phr_lst)) {
      zu_err("No words for the result.","formula_value->save", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif (empty($this->src_phr_lst)) {
      zu_err("No words for the calculation.","formula_value->save", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif (!isset($this->usr)) {
      zu_err("User missing.","formula_value->save");
    } else {
      if ($debug > 0) {
        $debug_txt = 'formula_value->save ('.$this->value.' for formula '.$this->frm_id.' with '.$this->phr_lst->name().' based on '.$this->src_phr_lst->name($debug-1);
        if (!$this->is_std) { 
          $debug_txt .= ' and user '.$this->usr->id;
        }  
        $debug_txt .= ')';
        zu_debug($debug_txt, $debug-10);
      }

      $result = 0;
    
      // build the database object because the is anyway needed
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_con->type   = 'formula_value';         
      
      // build the word list if needed to separate the time word from the word list
      $this->save_prepare_wrds($debug-1);
      zu_debug("formula_value->save -> word list prepared (group id ".$this->phr_grp_id." and source group id ".$this->src_phr_grp_id.")", $debug-15);

      // to check if a database update is needed create a second fv object with the database values
      $fv_db = clone $this;
      $fv_db->load($debug-1); 
      $row_id = $fv_db->id;
      $db_val = $fv_db->value;

      // if value exists, check it an update is needed
      if ($row_id > 0) {
        if (sprintf($db_val) <> sprintf($this->value)) {
          $result .= $db_con->update($row_id, array('formula_value','last_update'), array($this->value,'Now()'), $debug-1);
          $sql_fields = '';
          zu_debug("formula_value->save -> update (".$db_val." to ".$this->value." for ".$row_id.")", $debug-6);
        } else {
          zu_debug("formula_value->save -> not update (".$db_val." to ".$this->value." for ".$row_id.")", $debug-6);
        }
        $this->id = $row_id;
        $result = $row_id;
      } else {  
        $field_names    = array();
        $field_values   = array();
        $field_names[]  = 'formula_id';
        $field_values[] = $this->frm_id;
        $field_names[]  = 'formula_value';
        $field_values[] = $this->value;
        $field_names[]  = 'phrase_group_id';
        $field_values[] = $this->phr_grp_id;
        $field_names[]  = 'time_word_id';
        $field_values[] = $this->time_id;
        $field_names[]  = 'source_phrase_group_id';
        $field_values[] = $this->src_phr_grp_id;
        $field_names[]  = 'source_time_word_id';
        $field_values[] = $this->src_time_id;
        if (!$this->is_std) { 
          $field_names[]  = 'user_id';
          $field_values[] = $this->usr->id;
        }
        $field_names[]  = 'last_update';
        //$field_values[] = 'Now()'; // replaced with time of last change that has been included in the calculation
        $field_values[] = $this->last_val_update->format('Y-m-d H:i:s'); 
        $id = $db_con->insert($field_names, $field_values, $debug-1);
        $this->id = $id;
        $result = $id;
      }
    }

    zu_debug("formula_value->save -> id (".$result.")", $debug-8);
    return $result;

  }
}

?>
