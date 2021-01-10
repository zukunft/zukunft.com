<?php

/*

  phrase_group.php - a combination of a word list and a word_link_list
  ----------------
  
  a kind of phrase list, but separated into two different lists
  
  word groups are not part of the user sandbox, because this is a kind of hidden layer
  The main intention for word groups is to save space and execution time
  
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

class phrase_group {

  // database fields
  public $id           = NULL;    // the database id of the word group
  public $grp_name     = '';      // maybe later the user should have the possibility to overwrite the generic name, but this is not user at the moment
  public $auto_name    = '';      // the automatically created generic name for the word group, used for a quick display of values
  public $wrd_id_txt   = '';      // text of all linked words in ascending order for fast search (this is the master and the link table "value_phrase_links" is the slave)
  public $lnk_id_txt   = '';      // text of all linked triples in ascending order for fast search (as $wrd_id_txt this is the master and a negative id in "value_phrase_links" is the slave)
  public $id_order_txt = '';      // the ids from above in the order that the user wants to see them

  // in memory only fields
  public $usr          = NULL;    // the user object of the person for whom the word and triple list is loaded, so to say the viewer
  public $ids          = array(); // list of the phrase (word (positive id) or triple (negative id)) ids 
                                  // this is set by the frontend scripts and converted here to retrieve or create a group
                                  // the order is always ascending for be able to use this as a index to select the group
  public $id_order     = array(); // the ids from above in the order that the user wants to see them
  public $wrd_ids      = array(); // list of the word ids to load a list of words with one sql statement from the database
  public $lnk_ids      = array(); // list of the triple ids to load a list of words with one sql statement from the database
  public $phr_lst      = NULL;    // the phrase list object
  public $wrd_lst      = NULL;    // the word list object
  public $lnk_lst      = NULL;    // the triple (word_link) object 
  
  private function reset($debug) {
    $this->id           = NULL;
    $this->grp_name     = '';
    $this->auto_name    = '';
    $this->wrd_id_txt   = '';
    $this->lnk_id_txt   = '';
    $this->id_order_txt = '';
    
    $this->usr          = NULL;

    $this->ids          = array();   
    $this->id_order     = array();   
    $this->wrd_ids      = array();   
    $this->lnk_ids      = array();   

    $this->wrd_lst      = NULL;   
    $this->lnk_lst      = NULL; 
    $this->phr_lst      = NULL;     
  }

  /*
  
  load functions - the set functions are used to defined the loading selection criteria
  
  */

  // separate the words from the triples (word_links)
  // this also excludes automatically any empty ids
  private function set_ids_to_wrd_or_lnk_ids($debug) {
    $this->wrd_ids = array();
    $this->lnk_ids = array();
    foreach ($this->ids AS $id) {
      if ($id > 0) {
        $this->wrd_ids[] = $id;
      } elseif ($id < 0) {
        $this->lnk_ids[] = $id * -1;
      }
    }
    zu_debug('phrase_group->set_ids_to_wrd_or_lnk_ids split "'.implode(",",$this->ids).'" to "'.implode(",",$this->wrd_ids).'" and "'.implode(",",$this->lnk_ids).'"', $debug-16);
  }
  
  // the opposite of set_ids_to_wrd_or_lnk_ids
  private function set_ids_from_wrd_or_lnk_ids($debug) {
    zu_debug('phrase_group->set_ids_from_wrd_or_lnk_ids for "'.implode(",",$this->wrd_ids).'"', $debug-18);
    if (isset($this->wrd_ids)) {
      $this->ids = zu_ids_not_zero($this->wrd_ids, $debug-1);
    } else {
      $this->ids = array();
    }
    zu_debug('phrase_group->set_ids_from_wrd_or_lnk_ids done words "'.implode(",",$this->ids).'"', $debug-18);
    if (isset($this->lnk_ids)) {
      zu_debug('phrase_group->set_ids_from_wrd_or_lnk_ids try triples "'.implode(",",$this->lnk_ids).'"', $debug-18);
      foreach ($this->lnk_ids AS $id) {
        if (trim($id) <> '') {
          zu_debug('phrase_group->set_ids_from_wrd_or_lnk_ids try triple "'.$id.'"', $debug-18);
          if ($id == 0) {
            zu_warning('Zero triple id excluded in phrase group "'.$this->auto_name.'" (id '.$this->id.').', "phrase_group->set_ids_from_wrd_or_lnk_ids", '', (new Exception)->getTraceAsString(), $this->usr);
          } else {
            zu_debug('phrase_group->set_ids_from_wrd_or_lnk_ids add triple "'.$id.'"', $debug-18);
            $this->ids[] = $id * -1;
          }
        }
      }
    }
    zu_debug('phrase_group->set_ids_from_wrd_or_lnk_ids for "'.implode(",",$this->wrd_ids).'" done', $debug-18);
  }
  
  // load the word list based on the word id array
  private function set_wrd_lst($debug) {
    if (isset($this->wrd_ids)) {
      zu_debug('phrase_group->set_wrd_lst for "'.implode(",",$this->wrd_ids).'"', $debug-18);
      
      // ignore double word entries
      $this->wrd_ids = array_unique($this->wrd_ids);
    
      if (count($this->wrd_ids) > 0) {
        // make sure that there is not time word
        // maybe not needed if the calling function has done this already
        $wrd_lst = New word_list;
        $wrd_lst->ids = $this->wrd_ids;
        $wrd_lst->usr = $this->usr;
        $wrd_lst->load($debug-1);
        $wrd_lst->ex_time($debug-1);
        $this->wrd_lst = $wrd_lst;
        $this->wrd_ids = $wrd_lst->ids;
        // also fill the phrase list with the converted objects that are already loaded
        $phr_lst = $wrd_lst->phrase_lst($debug-1);
        if (isset($this->phr_lst) AND isset($phr_lst)) {
          $this->phr_lst = $this->phr_lst->concat_unique($phr_lst, $debug-1);
        } else {
          $this->phr_lst = $phr_lst;
        }
        zu_debug('phrase_group->set_wrd_lst got '.$this->wrd_lst->name($debug-1), $debug-18);
        zu_debug('phrase_group->set_wrd_lst got phrase '.$this->phr_lst->name($debug-1), $debug-18);
      }
    }  
  }
  
  // load the triple list based on the triple id array
  private function set_lnk_lst($debug) {    
    if (isset($this->lnk_ids)) {
      zu_debug('phrase_group->set_lnk_lst for "'.implode(",",$this->lnk_ids).'"', $debug-18);
      
      // ignore double word entries
      $this->lnk_ids = array_unique($this->lnk_ids);
    
      if (count($this->lnk_ids) > 0) {
        // make sure that there is not time word
        // maybe not needed if the calling function has done this already
        $lnk_lst = New word_link_list;
        $lnk_lst->ids = $this->lnk_ids;
        $lnk_lst->usr = $this->usr;
        $lnk_lst->load($debug-1);
        //$lnk_lst->ex_time($debug-1);
        $this->lnk_lst = $lnk_lst;
        $this->lnk_ids = $lnk_lst->ids;
        // also fill the phrase list with the converted objects that are already loaded
        $phr_lst = $lnk_lst->phrase_lst($debug-1);
        if (isset($this->phr_lst) AND isset($phr_lst)) {
          $this->phr_lst = $this->phr_lst->concat_unique($phr_lst, $debug-1);
        } else {
          $this->phr_lst = $phr_lst;
        }
        zu_debug('phrase_group->set_lnk_lst got '.$this->lnk_lst->name($debug-1), $debug-18);
        zu_debug('phrase_group->set_wrd_lst got phrase '.$this->phr_lst->name($debug-1), $debug-18);
      }
    }  
  }
  
  // create the wrd_id_txt based on the wrd_ids
  private function set_wrd_id_txt($debug) {
    zu_debug('phrase_group->set_wrd_id_txt for "'.implode(",",$this->wrd_ids).'"', $debug-18);

    // make sure that the ids have always the same order
    asort($this->wrd_ids);
    
    $wrd_id_txt = implode(",",$this->wrd_ids);
    zu_debug('phrase_group->set_wrd_id_txt test text "'.$wrd_id_txt.'"', $debug-22);
    
    if (strlen($wrd_id_txt) > 255) {
      zu_err('Too many words assigned to one value ("'.$wrd_id_txt.'" is longer than the max database size of 255).', "phrase_group->set_wrd_id_txt", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $this->wrd_id_txt = implode(",",$this->wrd_ids);
    }  
    zu_debug('phrase_group->set_wrd_id_txt to "'.$this->wrd_id_txt.'"', $debug-16);
  }
  
  // create the lnk_id_txt based on the lnk_ids
  private function set_lnk_id_txt($debug) {
    zu_debug('phrase_group->set_lnk_id_txt for "'.implode(",",$this->lnk_ids).'"', $debug-18);

    // make sure that the ids have always the same order
    asort($this->lnk_ids);
    
    $lnk_id_txt = implode(",",$this->lnk_ids);
    zu_debug('phrase_group->set_lnk_id_txt test text "'.$lnk_id_txt.'"', $debug-22);
    
    if (strlen($lnk_id_txt) > 255) {
      zu_err('Too many triples assigned to one value ("'.$lnk_id_txt.'" is longer than the db size of 255).', "phrase_group->set_lnk_id_txt", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $this->lnk_id_txt = implode(",",$this->lnk_ids);
    }  
    zu_debug('phrase_group->set_lnk_id_txt to "'.$this->lnk_id_txt.'"', $debug-16);
  }

  private function set_ids_from_wrd_or_lnk_lst($debug) {
    $this->wrd_ids = array();
    $this->lnk_ids = array();
    $this->ids     = array();
    if (isset($this->wrd_lst)) {
      zu_debug('phrase_group->set_ids_from_wrd_or_lnk_lst wrd ids for '.$this->wrd_lst->dsp_id(), $debug-14);
      // reload the words if needed
      //$this->wrd_lst->load($debug-1);
      if (count($this->wrd_lst->ids) > 0) {
        $wrd_lst = $this->wrd_lst;
        $wrd_lst->ex_time($debug-1);
        $this->wrd_ids = $wrd_lst->ids;
        zu_debug('phrase_group->set_ids_from_wrd_or_lnk_lst wrd ids '.implode(",",$this->wrd_ids), $debug-14);
        // also fill the phrase list with the converted objects that are already loaded
        $phr_lst = $wrd_lst->phrase_lst($debug-1);
        if (isset($this->phr_lst) AND isset($phr_lst)) {
          $this->phr_lst = $this->phr_lst->concat_unique($phr_lst, $debug-1);
        } else {
          $this->phr_lst = $phr_lst;
        }
      }
    }
    if (isset($this->lnk_lst)) {
      zu_debug('phrase_group->set_ids_from_wrd_or_lnk_lst lnk ids', $debug-14);
      // reload the words if needed
      //$this->lnk_lst->load($debug-1);
      if (count($this->lnk_lst->ids) > 0) {
        $lnk_lst = $this->lnk_lst;
        //$lnk_lst->ex_time($debug-1);
        $this->lnk_ids = $lnk_lst->ids;
        zu_debug('phrase_group->set_ids_from_wrd_or_lnk_lst lnk ids '.implode(",",$this->lnk_ids), $debug-14);
        // also fill the phrase list with the converted objects that are already loaded
        $phr_lst = $lnk_lst->phrase_lst($debug-1);
        if (isset($this->phr_lst) AND isset($phr_lst)) {
          $this->phr_lst = $this->phr_lst->concat_unique($phr_lst, $debug-1);
        } else {
          $this->phr_lst = $phr_lst;
        }
      }
    }
    $this->set_ids_from_wrd_or_lnk_ids($debug-1);
  }

  // set ids based on the phrase list
  private function set_ids_from_phr_lst($debug) {
    if  (isset($this->phr_lst) 
    AND !isset($this->wrd_lst) 
    AND !isset($this->lnk_lst)) {
      zu_debug('phrase_group->set_ids_from_phr_lst from '.$this->phr_lst->dsp_id(), $debug-14);
      // reload the phrases if needed
      if (!isset($this->phr_lst->ids)) {
        $this->phr_lst->load($debug-1);
      }
      if (count($this->phr_lst->ids) > 0) {
        $wrd_lst = $this->phr_lst->wrd_lst($debug-1);
        $wrd_lst->ex_time($debug-1);
        $this->wrd_ids = $wrd_lst->ids();

        $lnk_lst = $this->phr_lst->lnk_lst($debug-1);
        //$lnk_lst->ex_time($debug-1);
        $this->lnk_ids = $lnk_lst->ids();
      }
    }
    $this->set_ids_from_wrd_or_lnk_ids($debug-1);
  }

  // for building the where clause don't use the sf function to force the string format search
  private function set_lst_where($debug) {
    zu_debug('phrase_group->set_lst_where', $debug-16);
    $sql_where = '';
    if ($this->wrd_id_txt <> '' AND $this->lnk_id_txt <> '') {
      $sql_where = "g.word_ids   = '".$this->wrd_id_txt."'
                AND g.triple_ids = '".$this->lnk_id_txt."'";
    } elseif ($this->wrd_id_txt <> '') {
      $sql_where = "g.word_ids   = '".$this->wrd_id_txt."'";
    } elseif ($this->lnk_id_txt <> '') {
      $sql_where = "g.triple_ids = '".$this->lnk_id_txt."'";
    }          
    zu_debug('phrase_group->set_lst_where -> '.$sql_where, $debug-14);
    return $sql_where;
  }

  // this also excludes automatically any empty ids
  private function set_ids_to_lst_and_txt($debug) {
    zu_debug('phrase_group->set_ids_to_lst_and_txt '.$this->dsp_id(), $debug-14);
    $this->set_ids_to_wrd_or_lnk_ids($debug-1);
    $this->set_wrd_lst($debug-1);
    $this->set_lnk_lst($debug-1);
    $this->set_wrd_id_txt($debug-1);
    $this->set_lnk_id_txt($debug-1);
  }
  
  // set all parameters based on the combined id list
  // used by the frontend 
  private function load_by_ids($debug) {
    zu_debug('phrase_group->load_by_ids '.$this->dsp_id(), $debug-14);
    if (isset($this->ids)) {
      if (count($this->ids) > 0) {
        $this->set_ids_to_lst_and_txt($debug-1);
        $sql_where = $this->set_lst_where($debug-1);
      }
    }
    return $sql_where;
  }
  
  // set all parameters based on the separate id lists
  // used by the backend if the list object is not yet loaded
  private function load_by_wrd_or_lnk_ids($debug) {
    zu_debug('phrase_group->load_by_wrd_or_lnk_ids '.$this->dsp_id(), $debug-14);
    if (isset($this->wrd_ids) AND isset($this->lnk_ids)) {
      if (count($this->wrd_ids) > 0 OR count($this->lnk_ids) > 0) {
        $this->set_wrd_lst($debug-1);
        $this->set_lnk_lst($debug-1);
        $this->set_wrd_id_txt($debug-1);
        $this->set_lnk_id_txt($debug-1);
      }
    }
    $sql_where = $this->set_lst_where($debug-1);
  }
  
  // set all parameters based on the word and triple list objects
  // use by the backend, because in the backend the list objects are probably already loaded
  private function load_by_wrd_or_lnk_lst($debug) {
    zu_debug('phrase_group->load_by_wrd_or_lnk_lst '.$this->dsp_id(), $debug-14);
    $sql_where = '';
    if (isset($this->wrd_lst) AND isset($this->lnk_lst)) {
      if (count($this->wrd_lst) > 0 OR count($this->lnk_lst) > 0) {
        $this->set_ids_from_wrd_or_lnk_lst($debug-1);
        $this->set_wrd_id_txt($debug-1);
        $this->set_lnk_id_txt($debug-1);
        $sql_where = $this->set_lst_where($debug-1);
      }
    }
    return $sql_where;
  }
  
  // set all parameters based on the phrase list objects
  private function load_by_phr_lst($debug) {
    zu_debug('phrase_group->load_by_phr_lst '.$this->dsp_id(), $debug-14);
    $sql_where = '';
    if (isset($this->phr_lst)) {
      if (count($this->phr_lst) > 0) {
        $this->set_ids_from_phr_lst($debug-1);
        $this->set_wrd_id_txt($debug-1);
        $this->set_lnk_id_txt($debug-1);
        $sql_where = $this->set_lst_where($debug-1);
      }
    }
    return $sql_where;
  }
  
  // set all parameters based on the given setting
  private function load_by_selector($sql_where, $debug) {
    zu_debug('phrase_group->load_by_selector '.$this->dsp_id(), $debug-14);
    if ($sql_where == '') { $sql_where = $this->load_by_ids($debug-1); }
    if ($sql_where == '') { $sql_where = $this->load_by_wrd_or_lnk_ids($debug-1); }
    if ($sql_where == '') { $sql_where = $this->load_by_phr_lst($debug-1); }
    if ($sql_where == '') { $sql_where = $this->load_by_wrd_or_lnk_lst($debug-1); }
    return $sql_where;
  }
  
  // load the phrase group from the database by the id, the word and triple ids or the list objects
  function load($debug) {
    zu_debug('phrase_group->load '.$this->dsp_id(), $debug-14);

    global $db_con;
    $result = '';
    
        // check the all minimal input parameters
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a phrase group.", "phrase_group->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {  
      // build the database object because the is anyway needed
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_con->type   = 'view';         

      // set the where clause depending on the values given
      $sql_where = '';
      if ($this->id > 0)    { $sql_where = "g.phrase_group_id = ".$this->id; } 
      $sql_where = $this->load_by_selector($sql_where, $debug-1);
      zu_debug('phrase_group->load where '.$sql_where, $debug-16);

      /*
      } elseif (isset($this->wrd_lst) OR isset($this->lnk_lst)) {
        $sql_where = $this->load_by_wrd_or_lnk_lst($debug-1);
      } elseif (isset($this->wrd_ids) OR isset($this->lnk_ids)) {
        $sql_where = $this->load_by_wrd_or_lnk_ids($debug-1);
      }
      */
    

      if ($sql_where == '') {
        // the id list can be empty, because not needed to check this always in the calling function, so maybe in a later stage this could be an info
        zu_info("Eiter the database id or the phrase ids or list must be set to define a group.", "phrase_group->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        zu_debug('phrase_group->load select where '.$sql_where, $debug-12);
        $sql = "SELECT g.phrase_group_id,
                       g.phrase_group_name,
                       g.auto_description,
                       g.word_ids,
                       g.triple_ids
                  FROM phrase_groups g 
                 WHERE ".$sql_where.";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;         
        $db_grp = $db_con->get1($sql, $debug-5);  
        if ($db_grp['phrase_group_id'] <= 0) {
          $this->reset($debug-1);          
        } else {
          $this->id           = $db_grp['phrase_group_id'];
          $this->grp_name     = $db_grp['phrase_group_name'];
          $this->auto_name    = $db_grp['auto_description'];
          $this->wrd_id_txt   = $db_grp['word_ids'];
          $this->lnk_id_txt   = $db_grp['triple_ids'];
          $this->wrd_ids = explode(",",$this->wrd_id_txt);
          $this->lnk_ids = explode(",",$this->lnk_id_txt);
          $this->set_ids_from_wrd_or_lnk_ids($debug-1);
        }
        zu_debug('phrase_group->load got '.$this->dsp_id(), $debug-10);
      }  
    }  
    return $result;
  }

  // load the word and triple objects based on the ids load from the database
  function load_lst($debug) {
    zu_debug('phrase_group->load_lst', $debug-14);
    
    // load only if needed
    if ($this->wrd_id_txt <> '') {
      zu_debug('phrase_group->load_lst words for "'.$this->wrd_id_txt.'"', $debug-16);
      if ($this->wrd_ids <> explode(",",$this->wrd_id_txt) 
      OR !isset($this->wrd_lst)) {
        $this->wrd_ids = explode(",",$this->wrd_id_txt);
        $wrd_lst = New word_list;
        $wrd_lst->ids = $this->wrd_ids;
        $wrd_lst->usr     = $this->usr;
        $wrd_lst->load($debug-1);
        $this->wrd_lst = $wrd_lst;
        zu_debug('phrase_group->load_lst words ('.count($this->wrd_lst).')', $debug-12);
        // also fill the phrase list with the converted objects that are already loaded
        $phr_lst = $wrd_lst->phrase_lst($debug-1);
        if (isset($this->phr_lst) AND isset($phr_lst)) {
          $this->phr_lst = $this->phr_lst->concat_unique($phr_lst, $debug-1);
        } else {
          $this->phr_lst = $phr_lst;
        }
      }
    }

    if ($this->lnk_id_txt <> '') {
      zu_debug('phrase_group->load_lst triples for "'.$this->lnk_id_txt.'"', $debug-16);
      if ($this->lnk_ids <> explode(",",$this->lnk_id_txt) 
      OR !isset($this->lnk_lst)) {
        $this->lnk_ids = explode(",",$this->lnk_id_txt);
        $lnk_lst = New word_link_list;
        $lnk_lst->ids = $this->lnk_ids;
        $lnk_lst->usr = $this->usr;
        $lnk_lst->load($debug-1);
        $this->lnk_lst = $lnk_lst;
        zu_debug('phrase_group->load_lst triples ('.count($this->lnk_lst).')', $debug-12);
        // also fill the phrase list with the converted objects that are already loaded
        $phr_lst = $lnk_lst->phrase_lst($debug-1);
        if (isset($this->phr_lst) AND isset($phr_lst)) {
          $this->phr_lst = $this->phr_lst->concat_unique($phr_lst, $debug-1);
        } else {
          $this->phr_lst = $phr_lst;
        }
      }
    }
    zu_debug('phrase_group->load_lst ... done', $debug-16);
  }
  
  // internal function for testing the link for fast search
  function load_link_ids($debug) {

    global $db_con;
    $result = array();

    $sql = 'SELECT phrase_id 
              FROM phrase_group_phrase_links
             WHERE phrase_group_id = '.$this->id.';';
    //$db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $lnk_id_lst = $db_con->get($sql, $debug-5);  
    foreach ($lnk_id_lst AS $db_row) {
      $result[] = $db_row['phrase_id'];
    }  
    
    asort($result);
    return $result;    
  }
  
  // true if the current phrase group contains at least all phrases of the given $grp
  // e.g. $this ($val->grp) has the "ABB, Sales, million, CHF" and the table row ($grp) has "ABB, Sales" than this (value) can be used for this row
  function has_all_phrases_of ($grp, $debug) {
    zu_debug("phrase_group->has_all_phrases_of", $debug-10);
    $result = true;

    if (isset($grp->phr_lst)) {
      foreach ($grp->phr_lst->lst AS $phr) {
        if (!in_array($phr->id, $this->ids)) {
          zu_debug('phrase_group->has_all_phrases_of -> "'.$phr->id.'" is missing in '.implode(",",$this->ids), $debug-10);
          $result = false;
        }
      }
    }
    
    return $result;    
  }  
  
  /*
  
  get functions - to load or create with one call
  
  */
  
  // get the word/triple group (and create a new group if needed)
  // based on a string with the word and triple ids
  function get ($debug) {
    zu_debug('phrase_group->get '.$this->dsp_id(), $debug-10);
    $result = '';

    // get the id based on the given parameters
    $test_load = Clone $this;
    $result .= $test_load->load($debug-1);
    zu_debug('phrase_group->get loaded '.$this->dsp_id(), $debug-14);

    // use the loaded group or create the word group if it is missing
    if ($test_load->id > 0) {
      $this->id = $test_load->id;
      $result .= $this->load($debug-1); // TODO load twice should not be needed
    } else {
      zu_debug('phrase_group->get save '.$this->dsp_id(), $debug-16);
      $this->load_by_selector('', $debug-1);
      $result .= $this->save_id ($debug-9);
    } 
    
    // update the database for correct selection references
    if ($this->id > 0) {
      $result .= $this->save_links ($debug-9);  // update the database links for fast selection
      $result .= $this->generic_name($debug-9); // update the generic name if needed
    }
    
    zu_debug('phrase_group->get -> got '.$this->dsp_id(), $debug-12);
    return $result;    
  }

  // set the group id (and create a new group if needed)
  // ex grp_id that returns the id
  function get_id ($debug) {
    zu_debug('phrase_group->get_id '.$this->dsp_id(), $debug-10);
    $this->get($debug-1);
    return $this->id;    
  }

  // get the best matching group for a word list 
  // at the moment "best matching" is defined as the highest number of results
  private function get_by_wrd_lst ($debug) {

    global $db_con;
    $result = Null;
    
    if (isset($this->wrd_lst)) {
      if ($this->wrd_lst->lst > 0) {

        $pos = 1;
        $sql_from = '';
        $sql_where = '';
        foreach ($this->wrd_lst->ids AS $wrd_id) {
          if ($sql_from == '') {
            $sql_from .= 'phrase_group_word_links l'.$pos;
          } else {
            $sql_from .= ', phrase_group_word_links l'.$pos;
          }
          if ($sql_where == '') {
            $sql_where .= 'l'.$pos.'.word_id = '.$wrd_id;
          } else {  
            $sql_where .= ' AND l'.$pos.'.word_id = l'.$prev_pos.'.word_id AND l'.$pos.'.word_id = '.$wrd_id;
          }
          $prev_pos = $pos;
          $pos++;
        }
        $sql = "SELECT l1.phrase_group_id 
                  FROM ".$sql_from."
                 WHERE ".$sql_where."
              GROUP BY l1.phrase_group_id;";
        zu_debug('phrase_group->get_by_wrd_lst sql '.$sql, $debug-12);
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;         
        $db_grp = $db_con->get1($sql, $debug-5);  
        $this->id = $db_grp['phrase_group_id'];
        if ($this->id > 0) {
          zu_debug('phrase_group->get_by_wrd_lst got id '.$this->id, $debug-12);
          $result = $this->load($debug-1);
          zu_debug('phrase_group->get_by_wrd_lst '.$result.' found <'.$this->id.'> for '.$this->wrd_lst->name($debug-1).' and user '.$this->usr->name, $debug-12);
        } else {
          zu_warning('No group found for words '.$this->wrd_lst->name().'.', "phrase_group->get_by_wrd_lst", '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } else {
        zu_warning("Word list is empty.", "phrase_group->get_by_wrd_lst", '', (new Exception)->getTraceAsString(), $this->usr);
      }  
    } else {
      zu_warning("Word list is missing.", "phrase_group->get_by_wrd_lst", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $this;    
  }
  
  /*
  
  display functions
  
  */

  // return best possible id for this element mainly used for debugging
  function dsp_id ($debug) {
    $result = '';
    
    if ($this->name($debug-1) <> '') {
      $result .= '"'.$this->name($debug-1).'" ('.$this->id.')';
    } else {
      $result .= $this->id;
    }
    if ($this->grp_name <> '') {
      $result .= ' as "'.$this->grp_name.'"';
    }
    if ($result == '') {
      if (isset($this->phr_lst)) {
        $result .= ' for phrases '.$this->phr_lst->dsp_id();
      } elseif (count($this->ids) > 0) {
        $result .= ' for phrase ids '.implode(",",$this->ids);
      }  
    }
    if ($result == '') {
      if (isset($this->wrd_lst)) {
        $result .= ' for words '.$this->wrd_lst->dsp_id();
      } elseif (count($this->wrd_ids) > 0) {
        $result .= ' for word ids '.implode(",",$this->wrd_ids);
      } elseif ($this->wrd_id_txt <> '') {
        $result .= ' for word ids '.implode(",",$this->wrd_id_txt);
      }  
      if (isset($this->lnk_lst)) {
        $result .= ', triples '.$this->lnk_lst->dsp_id();
      } elseif (count($this->lnk_ids) > 0) {
        $result .= ', triple ids '.implode(",",$this->lnk_ids);
      } elseif ($this->lnk_id_txt <> '') {
        $result .= ', triple ids '.implode(",",$this->lnk_id_txt);
      }  
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }

    return $result;
  }

  // return a string with the group name
  function name($debug) {
    $result = '';
    
    if ($this->grp_name <> '') {
      // use the user defined description
      $result = $this->grp_name;
    } else {
      // or use the standard generic description
      $name_lst = array();
      if (isset($this->wrd_lst)) { $name_lst = array_merge($name_lst, $this->wrd_lst->names($debug-1)); }
      if (isset($this->lnk_lst)) { $name_lst = array_merge($name_lst, $this->lnk_lst->names($debug-1)); }
      $result = implode(",",$name_lst);
    }
    
    return $result;
  }
  
  // return a list of the word and triple names
  function names($debug) {
    zu_debug('phrase_group->names', $debug-14);

    // if not yet done, load, the words and triple list
    $this->load_lst($debug-1);
      
    $result = array();
    if (isset($this->wrd_lst)) { $result = array_merge($result, $this->wrd_lst->names($debug-1)); }
    if (isset($this->lnk_lst)) { $result = array_merge($result, $this->lnk_lst->names($debug-1)); }

    zu_debug('phrase_group->names -> '.implode(",",$result), $debug-14);
    return $result; 
  }
  
  // return the first value related to the word lst
  // or an array with the value and the user_id if the result is user specific
  function value($debug) {
    $val = New value;
    $val->wrd_lst = $this;
    $val->usr     = $this->usr;
    $val->load($debug-1);

    zu_debug('phrase_group->value '.$val->wrd_lst->name().' for "'.$this->usr->name.'" is '.$val->number, $debug-1);
    return $val;
  }

  // get the "best" value for the word list and scale it e.g. convert "2.1 mio" to "2'100'000"
  function value_scaled($debug) {
    //zu_debug("phrase_group->value_scaled (".$this->name()." for ".$this->usr->name.")", $debug-10);

    $val = $this->value($debug-1);
    
    // get all words related to the value id; in many cases this does not match with the value_words there are use to get the word: it may contains additional word ids
    if ($val->id > 0) {
      //zu_debug("phrase_group->value_scaled -> get word ids ".$this->name(), $debug-5);        
      $val->load_phrases($debug-1);
      // switch on after value->scale is working fine
      //$val->number = $val->scale($val->wrd_lst, $debug-5);      
    }

    return $val;
  }
    
  // 
  function result($time_wrd_id, $debug) {
    zu_debug("phrase_group->result (".$this->id.",time".$time_wrd_id.",u".$this->usr->name.")", $debug-10);

    global $db_con;
    $result = array();
    
    if ($time_wrd_id > 0) {
      $sql_time = " time_word_id = ".$time_wrd_id." ";
    } else {
      $sql_time = " (time_word_id IS NULL OR time_word_id = 0) ";
    }

    //$db_con = new mysql;
    $db_con->usr_id = $this->usr->id;         
    $sql = "SELECT formula_value_id AS id,
                   formula_value    AS num,
                   user_id          AS usr,
                   last_update      AS upd
              FROM formula_values 
             WHERE phrase_group_id = ".$this->id."
               AND ".$sql_time."
               AND user_id = ".$this->usr->id.";";
    $result = $db_con->get1($sql, $debug-5);  
    
    // if no user specific result is found, get the standard result
    if ($result === false) {
      $sql = "SELECT formula_value_id AS id,
                     formula_value    AS num,
                     user_id          AS usr,
                     last_update      AS upd
                FROM formula_values 
               WHERE phrase_group_id = ".$this->id."
                 AND ".$sql_time."
                 AND (user_id = 0 OR user_id IS NULL);";
      $result = $db_con->get1($sql, $debug-5);  

      // get any time value: to be adjusted to: use the latest
      if ($result === false) {
        $sql = "SELECT formula_value_id AS id,
                       formula_value    AS num,
                       user_id          AS usr,
                       last_update      AS upd
                  FROM formula_values 
                 WHERE phrase_group_id = ".$this->id."
                   AND (user_id = 0 OR user_id IS NULL);";
        $result = $db_con->get1($sql, $debug-5);  
        zu_debug("phrase_group->result -> (".$result['num'].")", $debug-1);
      } else {
        zu_debug("phrase_group->result -> (".$result['num'].")", $debug-1);
      }  
    } else {
      zu_debug("phrase_group->result -> (".$result['num']." for ".$this->usr->id.")", $debug-1);
    }
    
    return $result;
  }

  // create the generic group name (and update the database record if needed and possible)
  private function generic_name ($debug) {
    zu_debug('phrase_group->generic_name', $debug-14);

    global $db_con;
    $result = '';

    // if not yet done, load, the words and triple list
    $this->load_lst($debug-1);
      
    $word_name = '';
    if (isset($this->wrd_lst)) { 
      $word_name = $this->wrd_lst->name();
      zu_debug('phrase_group->generic_name word name '.$word_name, $debug-16);
    }  
    $triple_name = '';
    if (isset($this->lnk_lst)) { 
      $triple_name = $this->lnk_lst->name();
      zu_debug('phrase_group->generic_name triple name '.$triple_name, $debug-16);
    }
    if ($word_name <> '' AND $triple_name <> '') {
      $group_name = $word_name.','.$triple_name;
    } else {
      $group_name = $word_name.$triple_name;
    }
    
    // update the name if possible and needed
    if ($this->auto_name <> $group_name) {
      if ($this->id > 0) {
        // update the generic name in the database
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;         
        $db_con->type   = 'phrase_group';         
        $result .= $db_con->update($this->id, 'auto_description',$group_name, $debug-5);
        zu_debug('phrase_group->generic_name updated to '.$group_name, $debug-10);
      }
      $this->auto_name = $group_name;
    }
    zu_debug('phrase_group->generic_name ... group name '.$group_name, $debug-12);

    return $result;
  }
  
  // create the HTML code to select a phrase group be selecting a combination of words and triples
  private function selector ($debug) {
    $result = '';
    zu_debug('phrase_group->selector for '.$this->id.' and user "'.$this->usr->name.'"', $debug-12);
    
    /*
    new function: load_main_type to load all word and phrase types with one query
    
    Allow to remember the view order of words and phrases
    
    the form should create a url with the ids in the view order
    -> this is converted by this class to word ids, triple ids for selecting the group and saving the view order and the time for the value selection
    
    Create a new group if needed without asking the user
Create a new value if needed, but ask the user: abb sales of 46000, is still used by other users. Do you want to suggest the users to switch to abb revenues 4600? If yes, a request is created. If no, do you want to additional save abb revenues 4600 (and keep abb sales of 46000)? If no, nothing is saved and the form is shown again with a highlighted cancel or back button.

  update the link tables for fast selection
    
    */
    
    return $result;
  }
  
  
  /*
  
  save function - because the phrase group is a wrapper for a word and triple list the save function should not be called from outside this class
  
  */
  
  // save the user specific group name
  private function save ($debug) {
  }
  
  // create a new word group
  private function save_id ($debug) {
    zu_debug('phrase_group->save_id '.$this->dsp_id(), $debug-5);

    global $db_con;

    if ($this->id <= 0) {
      $this->generic_name($debug-1);
      
      // write new group
      if ($this->wrd_id_txt <> '' OR $this->lnk_id_txt <> '') {
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;         
        $db_con->type   = 'phrase_group';         
        $this->id = $db_con->insert(array(     'word_ids',     'triple_ids',        'auto_description'),
                                    array($this->wrd_id_txt,$this->lnk_id_txt,$this->auto_name), $debug-5);
      } else {
        zu_err('Either a word ('.$this->wrd_id_txt.') or triple ('.$this->lnk_id_txt.')  must be set to create a group for '.$this->dsp_id().'.','phrase_group->save_id', '', (new Exception)->getTraceAsString(), $this->usr);
      }
    } 

    return $this->id;    
  }

  // create the word group links for faster selection of the word groups based on single words
  private function save_links ($debug) {
    $this->save_phr_links('words', $debug-1);
    $this->save_phr_links('triples', $debug-1);
  }
  
  // create links to the group from words or triples for faster selection of the phrase groups based on single words or triples
  // word and triple links are saved in two different tables to be able use the database foreign keys
  private function save_phr_links ($type, $debug) {
    zu_debug('phrase_group->save_phr_links', $debug-10);

    global $db_con;
    $result = '';
    
    // create the db link object for all actions
    //$db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         

    // switch between the word and triple settings
    if ($type == 'words')  { 
      $table_name = 'phrase_group_word_links'; 
      $field_name = 'word_id';
    } else { 
      $table_name = 'phrase_group_triple_links';
      $field_name = 'triple_id';
    }
    
    // read all existing group links
    $sql = 'SELECT '.$field_name.'
              FROM '.$table_name.'
             WHERE phrase_group_id = '.$this->id.';';
    $grp_lnk_rows = $db_con->get($sql, $debug-1);  
    $db_ids = array();
    foreach ($grp_lnk_rows AS $grp_lnk_row) {
      $db_ids[] = $grp_lnk_row[$field_name];
    }
    zu_debug('phrase_group->save_phr_links -> found '.implode(",",$db_ids), $debug-12);
    
    // switch between the word and triple settings
    if ($type == 'words')  { 
      zu_debug('phrase_group->save_phr_links -> should have word ids '.implode(",",$this->wrd_ids), $debug-12);
      $add_ids = array_diff($this->wrd_ids, $db_ids);
      $del_ids = array_diff($db_ids, $this->wrd_ids);
    } else { 
      zu_debug('phrase_group->save_phr_links -> should have triple ids '.implode(",",$this->lnk_ids), $debug-12);
      $add_ids = array_diff($this->lnk_ids, $db_ids);
      $del_ids = array_diff($db_ids, $this->lnk_ids);
    }
    
    // add the missing links
    if (count($add_ids) > 0) {
      $add_nbr = 0;
      $sql = '';
      foreach ($add_ids AS $add_id) {
        if ($add_id <> '') {
          if ($sql == '') { $sql = 'INSERT INTO '.$table_name.' (phrase_group_id, '.$field_name.') VALUES '; }
          $sql .= " (".$this->id.",".$add_id.") ";
          $add_nbr++;
          if ($add_nbr < count($add_ids)) {
            $sql .= ",";
          } else {
            $sql .= ";";
          }
        }
      }
      if ($sql <> '') { 
        $sql_result = $db_con->exe($sql, DBL_SYSLOG_ERROR, "phrase_group->save_phr_links", (new Exception)->getTraceAsString(), $debug-5);
        if ($sql_result === False) {
          $result .= 'Error adding new group links "'.implode(',',$add_ids).'" for '.$this->id.'.';
        }
      }
    }  
    zu_debug('phrase_group->save_phr_links -> added links "'.implode(',',$add_ids).'" lead to '.implode(",",$db_ids), $debug-14);
    
    // remove the links not needed any more
    if (count($del_ids) > 0) {
      zu_debug('phrase_group->save_phr_links -> del '.implode(",",$del_ids).'', $debug-8);
      $del_nbr = 0;
      $sql = 'DELETE FROM '.$table_name.' 
               WHERE phrase_group_id = '.$this->id.'
                 AND '.$field_name.' IN ('.implode(',',$del_ids).');';
      $sql_result = $db_con->exe($sql, DBL_SYSLOG_ERROR, "phrase_group->save_phr_links", (new Exception)->getTraceAsString(), $debug-5);
      if ($sql_result === False) {
        $result .= 'Error removing group links "'.implode(',',$del_ids).'" from '.$this->id.'.';
      }
    }  
    zu_debug('phrase_group->save_phr_links -> deleted links "'.implode(',',$del_ids).'" lead to '.implode(",",$db_ids), $debug-14);
    
    return $result;    
  }

  
}

?>
