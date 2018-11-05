<?php

/*

  value_list.php - to show or modify a list of values
  --------------
  
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

class value_list {

  public $lst     = array(); // the list of values
  public $usr     = NULL;    // the person who wants to see ths value list
  
  // fields to select the values
  public $phr     = NULL;    // show the values related to this phrase
  public $phr_lst = NULL;    // show the values related to these phrases

  // display and select fields to increase the response time
  public $page_size = SQL_ROW_LIMIT; // if not defined, use the default page size
  public $page      = 0;             // start to display with this page 
  
  // the general load function (either by word, word list, formula or group)
  function load($debug) {
    // the id and the user must be set
    if ($this->phr->id > 0 AND !is_null($this->usr->id)) {
      zu_debug('value_list->load for "'.$this->phr->name.'".', $debug-10);
      $limit = $this->page_size;
      if ($limit <= 0) {
        $limit = SQL_ROW_LIMIT;
      }
      $sql = "SELECT v.value_id,
                     u.value_id AS user_value_id,
                     v.user_id,
                     IF(u.user_value IS NULL,    v.word_value,    u.user_value)    AS word_value,
                     IF(u.excluded IS NULL,      v.excluded,      u.excluded)      AS excluded,
                     IF(u.last_update IS NULL,   v.last_update,   u.last_update)   AS last_update,
                     IF(u.source_id IS NULL,     v.source_id,     u.source_id)     AS source_id,
                     v.phrase_group_id,
                     v.time_word_id,
                     g.word_ids,
                     g.triple_ids
                FROM phrase_groups g, `values` v 
           LEFT JOIN user_values u ON u.value_id = v.value_id 
                                  AND u.user_id = ".$this->usr->id." 
               WHERE g.phrase_group_id = v.phrase_group_id 
                 AND v.value_id IN ( SELECT value_id 
                                       FROM value_phrase_links 
                                      WHERE phrase_id = ".$this->phr->id." 
                                   GROUP BY value_id )
            ORDER BY v.phrase_group_id, v.time_word_id
               LIMIT ".$limit.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_val_lst = $db_con->get($sql, $debug-5);  
      foreach ($db_val_lst AS $db_val) {
        if (is_null($db_val['excluded']) OR $db_val['excluded'] == 0) {
          $val = New value;
          $val->id          = $db_val['value_id'];
          $val->usr_cfg_id  = $db_val['user_value_id'];
          $val->owner_id    = $db_val['user_id'];
          $val->usr         = $this->usr;
          $val->owner       = $db_val['user_id'];
          $val->number      = $db_val['word_value'];
          $val->source      = $db_val['source_id'];
          $val->last_update = new DateTime($db_val['last_update']);
          $val->grp_id      = $db_val['phrase_group_id'];
          $val->time_id     = $db_val['time_word_id'];
          $val->wrd_ids     = explode(",",$db_val['word_ids']);
          $val->lnk_ids     = explode(",",$db_val['triple_ids']);
          $this->lst[] = $val;
        } 
      } 
      zu_debug('value_list->load ('.count($this->lst).')', $debug-10);
    }  
  }

  // load a list of values that are related to a phrase or a list of phrases
  function load_by_phr($debug) {
    $sql_where = '';
    // the id and the user must be set
    if ($this->phr->id > 0 AND !is_null($this->usr->id)) {
      zu_debug('value_list->load for "'.$this->phr->name.'".', $debug-10);
      if ($limit <= 0) {
        $limit = SQL_ROW_LIMIT;
      }
      $sql = "SELECT v.value_id,
                     u.value_id AS user_value_id,
                     v.user_id,
                     IF(u.user_value IS NULL,    v.word_value,    u.user_value)    AS word_value,
                     IF(u.excluded IS NULL,      v.excluded,      u.excluded)      AS excluded,
                     IF(u.last_update IS NULL,   v.last_update,   u.last_update)   AS last_update,
                     IF(u.source_id IS NULL,     v.source_id,     u.source_id)     AS source_id,
                     v.phrase_group_id,
                     v.time_word_id
                FROM `values` v 
           LEFT JOIN user_values u ON u.value_id = v.value_id 
                                  AND u.user_id = ".$this->usr->id." 
               WHERE v.value_id IN ( SELECT value_id 
                                       FROM value_phrase_links 
                                      WHERE phrase_id = ".$this->phr->id." 
                                   GROUP BY value_id )
            ORDER BY v.phrase_group_id, v.time_word_id
               LIMIT ".$limit.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_val_lst = $db_con->get($sql, $debug-5);  
      foreach ($db_val_lst AS $db_val) {
        if (is_null($db_val['excluded']) OR $db_val['excluded'] == 0) {
          $val = New value;
          $val->id          = $db_val['value_id'];
          $val->usr_cfg_id  = $db_val['user_value_id'];
          $val->owner_id    = $db_val['user_id'];
          $val->usr         = $this->usr;
          $val->owner       = $db_val['user_id'];
          $val->number      = $db_val['word_value'];
          $val->source      = $db_val['source_id'];
          $val->last_update = new DateTime($db_val['last_update']);
          $val->grp_id      = $db_val['phrase_group_id'];
          $val->time_phr    = $db_val['time_word_id'];
          $this->lst[] = $val;
        } 
      } 
      zu_debug('value_list->load_by_phr ('.count($this->lst).')', $debug-10);
    }  
  }

  // load a list of values that are related to one 
  function load_all($debug) {
    // the id and the user must be set
    if (isset($this->phr_lst)) {
      if (count($this->phr_lst->ids) > 0 AND !is_null($this->usr->id)) {
        zu_debug('value_list->load_all for '.$this->phr_lst->dsp_id().'.', $debug-10);
        $sql = "SELECT v.value_id,
                      u.value_id AS user_value_id,
                      v.user_id,
                      IF(u.user_value IS NULL,    v.word_value,    u.user_value)    AS word_value,
                      IF(u.excluded IS NULL,      v.excluded,      u.excluded)      AS excluded,
                      IF(u.last_update IS NULL,   v.last_update,   u.last_update)   AS last_update,
                      IF(u.source_id IS NULL,     v.source_id,     u.source_id)     AS source_id,
                      v.phrase_group_id,
                      v.time_word_id
                  FROM `values` v 
            LEFT JOIN user_values u ON u.value_id = v.value_id 
                                    AND u.user_id = ".$this->usr->id." 
                WHERE v.value_id IN ( SELECT value_id 
                                        FROM value_phrase_links 
                                        WHERE phrase_id IN (".implode(",",$this->phr_lst->ids()).")
                                    GROUP BY value_id )
              ORDER BY v.phrase_group_id, v.time_word_id;";
        $db_con = New mysql;
        $db_con->usr_id = $this->usr->id;         
        $db_val_lst = $db_con->get($sql, $debug-5);  
        foreach ($db_val_lst AS $db_val) {
          if (is_null($db_val['excluded']) OR $db_val['excluded'] == 0) {
            $val = New value;
            $val->id          = $db_val['value_id'];
            $val->usr_cfg_id  = $db_val['user_value_id'];
            $val->owner_id    = $db_val['user_id'];
            $val->usr         = $this->usr;
            $val->owner       = $db_val['user_id'];
            $val->number      = $db_val['word_value'];
            $val->source      = $db_val['source_id'];
            $val->last_update = new DateTime($db_val['last_update']);
            $val->grp_id      = $db_val['phrase_group_id'];
            $val->time_phr    = $db_val['time_word_id'];
            $this->lst[] = $val;
          } 
        } 
        zu_debug('value_list->load_all ('.count($this->lst).')', $debug-10);
      }  
    }
    zu_debug('value_list->load_all -> done.', $debug-14);
  }

  // load a list of values that are related to all words of the list
  function load_by_phr_lst($debug) {
    // the word list and the user must be set
    if (count($this->phr_lst->ids) > 0 AND !is_null($this->usr->id)) {
      // build the sql statement based in the number of words
      $sql_where = '';
      $sql_from = '';
      $sql_pos = 0;
      foreach ($this->phr_lst->ids AS $phr_id) {
        if ($phr_id > 0) {
          $sql_pos = $sql_pos + 1;
          $sql_from = $sql_from." `value_phrase_links` l".$sql_pos.", ";
          if ($sql_pos == 1) {
            $sql_where = $sql_where." WHERE l".$sql_pos.".`phrase_id` = ".$phr_id." AND l".$sql_pos.".`value_id` = v.`value_id` ";
          } else {  
            $sql_where = $sql_where."   AND l".$sql_pos.".`phrase_id` = ".$phr_id." AND l".$sql_pos.".`value_id` = v.`value_id` ";
          }
        }
      }

      if ($sql_where <> '') {
        $sql = "SELECT DISTINCT v.value_id,
                       IF(u.user_value IS NULL,    v.word_value,    u.user_value)    AS word_value,
                       IF(u.excluded IS NULL,      v.excluded,      u.excluded)      AS excluded,
                       IF(u.last_update IS NULL,   v.last_update,   u.last_update)   AS last_update,
                       v.source_id,
                       v.user_id,
                       v.phrase_group_id,
                       v.time_word_id
                  FROM `values` v 
             LEFT JOIN user_values u ON u.value_id = v.value_id 
                                    AND u.user_id = ".$this->usr->id." 
                 WHERE v.value_id IN ( SELECT DISTINCT v.value_id 
                                         FROM ".$sql_from."
                                              `values` v
                                              ".$sql_where." )
              ORDER BY v.phrase_group_id, v.time_word_id;";
        zu_debug('value_list->load_by_phr_lst sql ('.$sql.')', $debug-16);
        $db_con = New mysql;
        $db_con->usr_id = $this->usr->id;         
        $db_val_lst = $db_con->get($sql, $debug-5);  
        foreach ($db_val_lst AS $db_val) {
          if (is_null($db_val['excluded']) OR $db_val['excluded'] == 0) {
            $val = New value;
            $val->id          = $db_val['value_id'];
            $val->usr         = $this->usr;
            $val->owner       = $db_val['user_id'];
            $val->number      = $db_val['word_value'];
            $val->source      = $db_val['source_id'];
            $val->last_update = new DateTime($db_val['last_update']);
            $val->grp_id      = $db_val['phrase_group_id'];
            $val->time_id     = $db_val['time_word_id'];
            $this->lst[] = $val;
          } 
        } 
      }
      zu_debug('value_list->load_by_phr_lst ('.count($this->lst).')', $debug-10);
    }  
  }

  // set the word objects for all value in the list if needed
  // not included in load, because sometimes loading of the word objects is not needed
  function load_phrases($debug) {
    // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
    foreach ($this->lst AS $val) {
      $val->load_phrases($debug-1);  
    }
  }
  
  /*
  
  data retrival functions
  
  */
  
  // get a list with all unique words used in the complete value list
  function phr_lst($debug) {
    zu_debug('value_list->phr_lst by ids (needs review).', $debug-4);
    $phr_lst = New phrase_list;
    $phr_lst->usr = $this->usr;

    // next line to be dismissed
    $all_ids = array();

    foreach ($this->lst AS $val) {
      if (isset($val->phr_lst)) {
        $phr_lst->merge($val->phr_lst, $debug-1);
      } else {
        $all_ids = array_unique (array_merge ($all_ids, $val->phr_ids));
      }
      
      // next lines to be dismissed
      if (count($all_ids) > 0) {
        $phr_lst->ids = $all_ids;
        $phr_lst->load($debug-1);
      }
    }
    
    zu_debug('value_list->phr_lst ('.count($phr_lst->lst).')', $debug-14);
    return $phr_lst;
  }
  
  // get a list with all time words used in the complete value list
  function time_lst($debug) {
    $all_ids = array();
    foreach ($this->lst AS $val) {
      $all_ids = array_unique (array_merge ($all_ids, array($val->time_id)));
    }
    $phr_lst = New word_list;
    $phr_lst->usr = $this->usr;
    if (count($all_ids) > 0) {
      $phr_lst->ids = $all_ids;
      $phr_lst->load($debug-1);
    }
    zu_debug('value_list->time_lst ('.count($phr_lst->lst).')', $debug-14);
    return $phr_lst;
  }
  
  /*
  
  filter and select functions
  
  */
  
  // return a value list object that contains only values that match the time word list
  function filter_by_time($time_lst, $debug) {
    zu_debug('value_list->filter_by_time.', $debug-14);
    $val_lst = array();
    foreach ($this->lst AS $val) {
      // only include time specific value
      if ($val->time_id > 0) {
        // only include values within the specific time periods 
        if (in_array($val->time_id, $time_lst->ids)) {
          $val_lst[] = $val;
          zu_debug('value_list->filter_by_time include '.$val->name().'.', $debug-18);
        } else {
          zu_debug('value_list->filter_by_time excluded '.$val->name().' because outside the specifid time periods.', $debug-16);
        }
      } else {
        zu_debug('value_list->filter_by_time excluded '.$val->name().' because this is not time specific.', $debug-14);
      }
    }
    $result = clone $this;
    $result->lst = $val_lst;

    zu_debug('value_list->filter_by_time ('.count($result->lst).')', $debug-14);
    return $result;
  }
  
  // return a value list object that contains only values that match at least one phrase from the phrase list
  function filter_by_phrase_lst($phr_lst, $debug) {
    zu_debug('value_list->filter_by_phrase_lst '.count($this->lst).' values by '.$phr_lst->name().'.', $debug-14);
    $result = array();
    foreach ($this->lst AS $val) {
      //$val->load_phrases($debug-20);
      $val_phr_lst = $val->phr_lst;
      if (isset($val_phr_lst)) {
        zu_debug('value_list->filter_by_phrase_lst val phrase list '.$val_phr_lst->name().'.', $debug-14);
      } else {
        zu_debug('value_list->filter_by_phrase_lst val no value pharse list.', $debug-14);
      }
      $found = false;
      foreach ($val_phr_lst->lst AS $phr) {
        //zu_debug('value_list->filter_by_phrase_lst val is '.$phr->name.' in '.$phr_lst->name().'.', $debug-14);
        if (in_array($phr->name, $phr_lst->names())) {
          if (isset($val_phr_lst)) {
            zu_debug('value_list->filter_by_phrase_lst val phrase list '.$val_phr_lst->name().' is found in '.$phr_lst->name().'.', $debug-14);
          } else {
            zu_debug('value_list->filter_by_phrase_lst val found, but no value pharse list.', $debug-14);
          }  
          $found = true; // to make sure that each value is only added once; an improval could be to stop searching after a phrase is found
        }
      }
      if ($found) {
        $result[] = $val;
      }
    }
    $this->lst = $result;

    zu_debug('value_list->filter_by_phrase_lst ('.count($this->lst).')', $debug-14);
    return $this;
  }
  
  // selects from a val_lst_phr the best matching value
  // best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
  // used by value_list_dsp->dsp_table
  function get_from_lst ($word_ids, $debug) {
    asort($word_ids);
    zu_debug("value_list->get_from_lst ids ".implode(",",$word_ids).".", $debug-10);

    $found = false;
    $result = Null;
    foreach ($this->lst AS $val) {
      if (!$found) {
        zu_debug("value_list->get_from_lst -> check ".implode(",",$word_ids)." with (".implode(",",$val->ids).")", $debug-10);
        $wrd_missing = zu_lst_not_in_no_key($word_ids, $val->ids, $debug-10);
        if (empty($wrd_missing)) {
          // potential result candidate, because the value has all needed words 
          zu_debug("value_list->get_from_lst -> can (".$val->number.")", $debug-10);
          $wrd_extra = zu_lst_not_in_no_key($val->ids, $word_ids, $debug-10);
          if (empty($wrd_extra)) {
            // if there is no extra word, it is the correct value 
            zu_debug("value_list->get_from_lst -> is (".$val->number.")", $debug-10);
            $found = true;
            $result = $val;
          } else {
            zu_debug("value_list->get_from_lst -> is not, because (".implode(",",$wrd_extra).")", $debug-10);
          }
        }
      }
    }

    zu_debug("value_list->get_from_lst -> done (".$result->number.")", $debug-10);
    return $result;
  }

  // selects from a val_lst_wrd the best matching value
  // best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
  // used by value_list_dsp->dsp_table
  function get_by_grp ($grp, $time, $debug) {
    zu_debug("value_list->get_by_grp ".$grp->auto_name.".", $debug-10);

    $found = false;
    $result = Null;
    $row = 0;
    foreach ($this->lst AS $val) {
      if (!$found) {
        // show only a few debug messages for a useful result
        if ($row < 6) {
          zu_debug("value_list->get_by_grp check if ".$val->grp_id." = ".$grp->id." and ".$val->time_id." = ".$time->id.".", $debug-10);
        }  
        if ($val->grp_id  == $grp->id 
        AND $val->time_id == $time->id) {
          $found = true;
          $result = $val;
        } else {
          if (!isset($val->grp)) {  
            zu_debug("value_list->get_by_grp -> load group", $debug-12);
            $val->load_phrases($debug-1);
          }
          if (isset($val->grp)) {  
            if ($row < 6) {
              zu_debug('value_list->get_by_grp -> check if all of '.$grp->name($debug-1).' are in '.$val->grp->name($debug-1).' and value should be used.', $debug-12);
            }  
            if ($val->grp->has_all_phrases_of($grp, $debug-1) 
            AND $val->time_id == $time->id) {
              zu_debug('value_list->get_by_grp -> all of '.$grp->name($debug-1).' are in '.$val->grp->name($debug-1).' so value is used.', $debug-12);
              $found = true;
              $result = $val;
            }
          }
        }
      }
      $row++;
    }

    zu_debug("value_list->get_by_grp -> done (".$result->number.")", $debug-10);
    return $result;
  }

  
  /*
    convert functions
    -----------------
  */

  // return a list of phrase groups for all values of this list
  function phrase_groups($debug) {
    zu_debug('value_list->phrase_groups', $debug-14);
    $grp_lst = New phrase_group_list;
    $grp_lst->usr = $this->usr;
    foreach ($this->lst AS $val) {
      if (!isset($val->grp)) { $this->load_grp_by_id($debug-1); }
      if (isset($val->grp)) {
        $grp_lst->lst[] = $val->grp;
      } else {
        zu_err("The phrase group for value ".$val->id." cannot be loaded.", "value_list->phrase_groups", '', (new Exception)->getTraceAsString(), $this->usr);
      }
    }

    zu_debug('value_list->phrase_groups ('.count($grp_lst->lst).')', $debug-14);
    return $grp_lst; 
  }

  
  // return a list of phrases used for each value
  function common_phrases($debug) {
    $grp_lst = $this->phrase_groups($debug-1);
    $phr_lst = $grp_lst->common_phrases($debug-1);
    zu_debug('value_list->common_phrases ('.count($phr_lst->lst).')', $debug-14);
    return $phr_lst; 
  }
  
  /*
  
  check / database consistency functions
  
  */
  
  // check the consistency for all values
  // so get the words and triples linked from the word group
  //    and update the slave table value_phrase_links (which should be renamed to value_phrase_links) 
  function check_all($debug) {
    $result = '';
    // the id and the user must be set
    $sql = "SELECT value_id
              FROM `values` v;";
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_val_lst = $db_con->get($sql, $debug-5);  
    foreach ($db_val_lst AS $db_val) {
      $val = New value;
      $val->id          = $db_val['value_id'];
      $val->usr         = $this->usr;
      $val->load($debug-1);
      $result .= $val->check($debug-1);
      zu_debug('value_list->load_by_phr ('.count($this->lst).')', $debug-10);
    }  
    zu_debug('value_list->check_all ('.count($this->lst).')', $debug-10);
    return $result; 
  }

  // to be integrated into load
  // list of values related to a formula 
  // described by the word to which the formula is assigned 
  // and the words used in the formula
  function load_frm_related($phr_id, $phr_ids, $user_id, $debug) {
    zu_debug("value_list->load_frm_related (".$phr_id.",ft".implode(",",$phr_ids).",u".$user_id.")", $debug-10);
    $result = array();

    if ($phr_id > 0 AND !empty($phr_ids)) {
      $sql = "SELECT l1.value_id
                FROM value_phrase_links l1,
                    value_phrase_links l2
              WHERE l1.value_id = l2.value_id
                AND l1.phrase_id = ".$phr_id."
                AND l2.phrase_id IN (".implode(",",$phr_ids).");";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_lst = $db_con->get($sql, $debug-10);  
      foreach ($db_lst AS $db_val) {
        $result = $db_val['value_id']; 
      }
    }
    
    zu_debug("value_list->load_frm_related -> (".implode(",",$result).")", $debug-1);
    return $result;
  }

  // group words
  // kind of similar to zu_sql_val_lst_wrd
  function load_frm_related_grp_phrs_part($val_ids, $phr_id, $phr_ids, $user_id, $debug) {
    zu_debug("value_list->load_frm_related_grp_phrs_part (v".implode(",",$val_ids).",t".$phr_id.",ft".implode(",",$phr_ids).",u".$user_id.")", $debug-10);
    $result = array();

    if ($phr_id > 0 AND !empty($phr_ids) AND !empty($val_ids)) {
      $phr_ids[] = $phr_id; // add the main word to the exclude words
      $sql = "SELECT l.value_id,
                    IF(u.user_value IS NULL,v.word_value,u.user_value) AS word_value, 
                    l.phrase_id, 
                    v.excluded, 
                    u.excluded AS user_excluded 
                FROM value_phrase_links l,
                    `values` v 
          LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = ".$user_id." 
              WHERE l.value_id = v.value_id
                AND l.phrase_id NOT IN (".implode(",",$phr_ids).")
                AND l.value_id IN (".implode(",",$val_ids).")
                AND (u.excluded IS NULL OR u.excluded = 0) 
            GROUP BY l.value_id, l.phrase_id;";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_lst = $db_con->get($sql, $debug-10);  
      $value_id = -1; // set to an id that is never used to force the creation of a new entry at start
      foreach ($db_lst AS $db_val) {
        if ($value_id == $db_val['value_id']) {
          $phr_result[] = $db_val['phrase_id'];
        } else {  
          if ($value_id >= 0) {
            // remember the previous values
            $row_result[] = $phr_result;
            $result[$value_id] = $row_result;
          } 
          // remember the values for a new result row
          $value_id  = $db_val['value_id'];
          $val_num = $db_val['word_value'];
          $row_result   = array();
          $row_result[] = $val_num;
          $phr_result   = array();
          $phr_result[] = $db_val['phrase_id'];
        }  
      } 
      if ($value_id >= 0) {
        // remember the last values
        $row_result[] = $phr_result;
        $result[$value_id] = $row_result;
      } 
    } 

    zu_debug("value_list->load_frm_related_grp_phrs_part -> (".zu_lst_dsp($result).")", $debug-10);
    return $result;
  }

  // to be integrated into load
  function load_frm_related_grp_phrs($phr_id, $phr_ids, $user_id, $debug) {
    zu_debug("value_list->load_frm_related_grp_phrs (".$phr_id.",ft".implode(",",$phr_ids).",u".$user_id.")", $debug-10);
    $result = array();

    if ($phr_id > 0 AND !empty($phr_ids)) {
      // get the relevant values
      $val_ids = $this->load_frm_related($phr_id, $phr_ids, $user_id, $debug-1);

      // get the word groups for which a formula result is expected
      // maybe exclude word groups already here where not all needed values for the formula are in the database
      $result = $this->load_frm_related_grp_phrs_part($val_ids, $phr_id, $phr_ids, $user_id, $debug-1);
    }
    
    zu_debug("value_list->load_frm_related_grp_phrs -> (".zu_lst_dsp($result).")", $debug-1);
    return $result;
  }

  // return the html code to display all values related to a given word
  // $phr->id is the related word that shoud not be included in the display
  // $this->usr->id is a parameter, because the viewer must not be the owner of the value
  // to do: add back
  function html ($back, $debug) {
    zu_debug('value_list->html ('.count($this->lst).')', $debug-10);
    $result = '';

    // get common words
    $common_phr_ids = array();
    foreach ($this->lst AS $val) {
      if ($val->check($debug-1) > 0) {
        zu_warning('The group id for value '.$val->id.' has not been updated, but should now be correct.', "value_list->html", '', (new Exception)->getTraceAsString(), $this->usr);
      }
      $val->load_phrases($debug-1);
      $val_phr_lst = $val->phr_lst;
      zu_debug('value_list->html -> get words '.$val->phr_lst->dsp_id().' for "'.$val->number.'" ('.$val->id.').', $debug-14);
      if (empty($common_phr_ids)) {
        $common_phr_ids = $val_phr_lst->ids;
      } else {  
        $common_phr_ids = array_intersect($common_phr_ids, $val_phr_lst->ids);
      }
    }

    $common_phr_ids = array_diff($common_phr_ids, array($this->phr->id));  // exclude the list word
    $common_phr_ids = array_values($common_phr_ids);            // cleanup the array
    
    // display the common words
    if (!empty($common_phr_ids)) {
      $commen_phr_lst = New word_list;
      $commen_phr_lst->ids = $common_phr_ids;
      $commen_phr_lst->usr = $this->usr;
      $commen_phr_lst->load($debug-1); 
      $result .= ' in ('.implode(",",$commen_phr_lst->names_linked()).')<br>';
    }
    
    // instead of the saved result maybe display the calculated result based on formulas that matches the word pattern
    $result .= '<table style="width:500px">';

    // the reused button object
    $btn = New button; 
    
    // to avoid repeating the same words in each line and to offer a useful "add new value"
    $last_phr_lst = array();

    foreach ($this->lst AS $val) {
      //$this->usr->id  = $val->usr->id;
      
      // get the words
      $val->load_phrases($debug-1); 
      $val_phr_lst = $val->phr_lst;
      
      // remove the main word from the list, because it should not be shown on each line
      $dsp_phr_lst = clone $val_phr_lst;
      $dsp_phr_lst->diff_by_ids(array($this->phr->id), $debug-1);      
      $dsp_phr_lst->diff_by_ids($common_phr_ids, $debug-1);      
      // remove the words of the privious row, because it should not be shown on each line
      $dsp_phr_lst->diff_by_ids($last_phr_lst->ids, $debug-1);
      
      //if (isset($val->time_phr)) {
      if ($val->time_id > 0) {
        $time_phr = new phrase;
        $time_phr->id  = $val->time_id;
        $time_phr->usr = $val->usr; 
        $time_phr->load($debug-1);
        $val->time_phr = $time_phr;
        $dsp_phr_lst->add($time_phr, $debug-1);    
        zu_debug('value_list->html -> add time word '.$val->time_phr->name, $debug-10);
      }
      
      $result .= '  <tr>';
      $result .= '    <td>';
      zu_debug('value_list->html -> linked words '.$val->id, $debug-10);
      $result .= '      '.$dsp_phr_lst->name_linked().' <a href="/http/value_edit.php?id='.$val->id.'&back='.$this->phr->id.'">'.$val->val_formatted($debug-1).'</a>';
      zu_debug('value_list->html -> linked words '.$val->id.' done.', $debug-16);
      // to review
      // list the related formula values
      $fv_lst = New formula_value_list;
      $fv_lst->usr = $this->usr;
      $result .= $fv_lst->val_phr_lst($val, $this->phr->id, $val_phr_lst, $val->time_id, $debug-1);
      $result .= '    </td>';
      zu_debug('value_list->html -> formula results '.$val->id.' loaded.', $debug-18);

      if ($last_phr_lst != $val_phr_lst) {
        $last_phr_lst = $val_phr_lst;
        $result .= '    <td>';
        $result .= btn_add_value ($val_phr_lst, Null, $this->phr->id, $debug-1); 

        $result .= '    </td>';
      }
      $result .= '    <td>';
      $result .= '      '.$btn->edit_value ($val_phr_lst, $val->id, $this->phr->id, $debug-1); 
      $result .= '    </td>';
      $result .= '    <td>';
      $result .= '      '.$btn->del_value ($val_phr_lst, $val->id, $this->phr->id, $debug-1); 
      $result .= '    </td>';
      $result .= '  </tr>';
    }

    $result .= '</table> ';
    
    // allow the user to add a completely new value 
    if (empty($common_phr_ids)) {
      $commen_phr_lst = New word_list;
      $common_phr_ids[] = $this->phr->id;
      $commen_phr_lst->ids = $common_phr_ids;
      $commen_phr_lst->usr = $this->usr;
      $commen_phr_lst->load($debug-1); 
    }

    $commen_phr_lst = $commen_phr_lst->phrase_lst($debug-1); 
    
    // to review probably wrong call from /var/www/default/classes/view.php(267): view_component_dsp->all(Object(word_dsp), 291, 17
    if (get_class($this->phr) == 'word' or get_class($this->phr) == 'word_dsp') {
      $this->phr = $this->phr->phrase($debug-1);
    }
    if (isset($commen_phr_lst)) {
      if (!empty($commen_phr_lst->lst)) {
        $commen_phr_lst->add($this->phr, $debug-1); 
        $result .= $commen_phr_lst->btn_add_value ($back, $debug-1); 
      }
    }

    zu_debug("value_list->html ... done", $debug-10);

    return $result;
  }

  
}

?>
