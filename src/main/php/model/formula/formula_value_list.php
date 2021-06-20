<?php

/*

  formula_value_list.php - a list of formula results
  ----------------------
  
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

class formula_value_list {

  public $lst = array(); // list of the formula results
  
  // search fields
  public $usr    = NULL; // the person who wants to see the results 
  public $frm_id = NULL; // to get the results of this formula
  public $phr_id = NULL; // to get the results linked to a phrase
  public $grp_id = NULL; // to get the results linked to a phrase group

  // private in memory fields to reduce the number of function call parameters within this class
  public $frm    = NULL; // the formula object
  
  
  /*
  
    load functions
    --------------
    
  */

  // load formula results from the database related to one formula or one word
  // similar to load of the formula_value object, but to load many results at once
  function load ($limit = SQL_ROW_LIMIT, $debug = 0) {
    log_debug('formula_value_list->load', $debug-18);

    global $db_con;

    // check the minimal input parameters
    if (!isset($this->usr)) {
      log_err("The user id must be set to load a result list.", "formula_value_list->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->frm_id <= 0 AND $this->grp_id <= 0 AND $this->phr_id <= 0) {  
      log_err("Either the formula id (".$this->frm_id.") or the word id (".$this->phr_id.") and the user (".$this->usr->id.") must be set to get a result list.", "formula_value_list->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // set the where clause depending on the values given
      $sql_from = 'formula_values fv';
      $sql_where = '';
      $sql_group = '';
      if ($this->frm_id > 0) {
        $sql_where = "fv.formula_id = ".$this->frm_id;
      } elseif ($this->grp_id > 0) {
        // group links
        $sql_where = "fv.source_phrase_group_id = ".$this->grp_id;
        $sql_group = 'GROUP BY fv.formula_value_id';
      } elseif ($this->phr_id > 0) {
        // word links
        $sql_from .= ', phrase_group_word_links l
         LEFT JOIN user_phrase_group_word_links ul ON ul.phrase_group_word_link_id = l.phrase_group_word_link_id 
                                                  AND ul.user_id = '.$this->usr->id;
        $sql_where = "l.word_id = ".$this->phr_id."
                  AND l.phrase_group_id = fv.phrase_group_id
                  AND COALESCE(ul.excluded, 0) <> 1";
        $sql_group = 'GROUP BY fv.formula_value_id,
                               fv.phrase_group_id,
                               fv.time_word_id';
      } elseif ($this->phr_id < 0) {
        // triple links
        $triple_id = $this->phr_id * -1;
        $sql_from .= ', phrase_group_triple_links l
         LEFT JOIN user_phrase_group_triple_links ul ON ul.phrase_group_triple_link_id = l.phrase_group_triple_link_id 
                                                    AND ul.user_id = '.$this->usr->id;
        $sql_where = "l.triple_id = ".$triple_id."
                  AND l.phrase_group_id = fv.phrase_group_id
                  AND COALESCE(ul.excluded, 0) <> 1";
        $sql_group = 'GROUP BY fv.formula_value_id,
                               fv.phrase_group_id,
                               fv.time_word_id';
      }

      if ($sql_where == '') {
        log_err("Internal error in the where clause.", "formula_value_list->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else{  
        if ($limit <= 0) {
          $limit = SQL_ROW_LIMIT;
        }
        $sql = "SELECT fv.formula_value_id,
                       fv.user_id,
                       fv.formula_id,
                       fv.source_phrase_group_id,
                       fv.source_time_word_id,
                       fv.phrase_group_id,
                       fv.time_word_id,
                       fv.formula_value
                  FROM ".$sql_from." 
                 WHERE ".$sql_where."
                   AND (fv.user_id = ".$this->usr->id." OR fv.user_id = 0 OR fv.user_id IS NULL)
                       ".$sql_group." 
              ORDER BY last_update DESC 
                  LIMIT ".$limit.";";
        log_debug('formula_value_list->load sql '.$sql, $debug-10);
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;         
        $val_rows = $db_con->get($sql, $debug-5);  
        foreach ($val_rows AS $val_row) {
          $fv = New formula_value;
          $fv->usr            = $this->usr;
          $fv->id             = $val_row['formula_value_id'];
          $fv->frm_id         = $val_row['formula_id'];
          $fv->src_phr_grp_id = $val_row['source_phrase_group_id'];
          $fv->src_time_id    = $val_row['source_time_word_id'];
          $fv->phr_grp_id     = $val_row['phrase_group_id'];
          $fv->time_id        = $val_row['time_word_id'];
          $fv->value          = $val_row['formula_value'];
          // todo get user for the case that not all value are for the same unser
          //$fv->usr            = $val_row['user_id'];

          log_debug('formula_value_list->load_frm get words', $debug-10);
          $fv->load_phrases($debug-1);

          $this->lst[] = $fv;
        }
      }
    }  
  }
  
  /*
  
    display functions
    -----------------
    
  */

  // return best possible id for this element mainly used for debugging
  function dsp_id ($debug) {
    $result = '';
    if ($debug > 10) {
      if (isset($this->lst)) {
        foreach ($this->lst AS $fv) {
          $result .= $fv->name($debug-1);
          $result .= ' ('.$fv->id.') - ';
        }  
      }  
    } else {
      $nbr = 1;
      if (isset($this->lst)) {
        foreach ($this->lst AS $fv) {
          if ($nbr <= 5) {
            $result .= $fv->name($debug-1);
            $result .= ' ('.$fv->id.') - ';
          }
          $nbr++;
        }  
      }  
      if ($nbr > 5) {
        $result .= ' ... total '.count($this->lst);
      }
    }
    /*
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->name;
    }
    */
    return $result;
  }

  // return one string with all names of the list
  function name($debug) {

    $name_lst = array();
    if (isset($this->lst)) {
      foreach ($this->lst AS $fv) {
        $name_lst[] = $fv->name($debug-1);
      }
    }

  if ($debug > 10) {
      $result = '"'.implode('","',$name_lst).'"';
    } else {
      $result = '"'.implode('","',array_slice($name_lst, 0, 7));
      if (count($name_lst) > 8) {
        $result .= ' ... total '.count($this->lst);
      }
      $result .= '"';
    }
    return $result; 
  }
  
  // return a list of the formula result ids
  function ids() {
    $result = array();
    if (isset($this->lst)) {
      foreach ($this->lst AS $fv) {
        // use only valid ids
        if ($fv->id <> 0) {
          $result[] = $fv->id;
        }
      }
    }
    return $result; 
  }
  
  // return a list of the formula result names
  function names($debug) {
    $result = array();
    if (isset($this->lst)) {
      foreach ($this->lst AS $fv) {
        $result[] = $fv->name($debug-1);

        // check user consistency (can be switched off once the program ist stable)
        if (!isset($fv->usr)) {
          log_err('The user of a formula result list element differs from the list user.', 'fv_lst->names','The user of "'.$fv->name().'" is missing, but the list user is "'.$this->usr->name.'".' , (new Exception)->getTraceAsString(), $this->usr);
        } elseif ($fv->usr <> $this->usr) {
          log_err('The user of a formula result list element differs from the list user.', 'fv_lst->names','The user "'.$fv->usr->name.'" of "'.$fv->name().'" does not match the list user "'.$this->usr->name.'".' , (new Exception)->getTraceAsString(), $this->usr);
        }
      }
    }
    log_debug('fv_lst->names ('.implode(",",$result).')', $debug-19);
    return $result; 
  }
  
  // create the html code to show the formula results to the user
  function display ($back, $debug) {
    log_debug("fv_lst->display (".count($this->lst).")", $debug-10);
    $result = ''; // reset the html code var

    // prepare to show where the user uses different word than a normal viewer
    //$row_nbr = 0;
    $result .= dsp_tbl_start_half ();
    foreach ($this->lst AS $fv) {
      //$row_nbr++;
      $result .= '<tr>';
      /*if ($row_nbr == 1) {
        $result .= '<th>words</th>';
        $result .= '<th>value</th>';
      } */
      $fv->load_phrases($debug-1); // load any missing objects if needed
      $phr_lst = clone $fv->phr_lst;
      if (isset($fv->time_phr)) {
        log_debug("fv_lst->display -> add time ".$fv->time_phr->name.".", $debug-10);
        $phr_lst->add($fv->time_phr, $debug-1);
      }  
      $result .= '</tr><tr>';
      $result .= '<td>'.$phr_lst->name_linked().'</td>';
      $result .= '<td>'.$fv->display_linked($back, $debug).'</td>';
      $result .= '</tr>';
    }
    $result .= dsp_tbl_end ();

    log_debug("fv_lst->display -> done", $debug-1);
    return $result;
  }
  
  /*
  
    create functions - build new formula values
    ----------------
    
  */

  // add all formula results to the list for ONE formula based on 
  // - the word assigned to the formula ($phr_id)
  // - the word that are used in the formula ($frm_phr_ids)
  // - the formula ($frm_row) to provide parameters, but not for selection
  // - the user ($this->usr->id) to filter the results
  // and request on formula result for each word group
  // e.g. the formula is assigned to Company ($phr_id) and the "operating income" formula result should be calculated
  //      so Sales and Cost are words of the formula
  //      if Sales and Cost for 2016 and 2017 and EUR and CHF are in the database for one company (e.g. ABB)
  //      the "ABB" "operating income" for "2016" and "2017" should be calculated in "EUR" and "CHF"
  //      so the result would be to add 4 formula values to the list:
  //      1. calculate "operating income" for "ABB", "EUR" and "2016"
  //      2. calculate "operating income" for "ABB", "CHF" and "2016"
  //      3. calculate "operating income" for "ABB", "EUR" and "2017"
  //      4. calculate "operating income" for "ABB", "CHF" and "2017"
  // todo: check if a value is used in the formula
  //       exclude the time word and if needed loop over the time words
  //       if the value has been update, create a calculation request
  // ex zuc_upd_lst_val
  function add_frm_val($phr_id, $frm_phr_ids, $frm_row, $usr_id, $debug) {
    log_debug('fv_lst->add_frm_val(t'.$phr_id.','.implode(",",$frm_phr_ids).',u'.$this->usr->id.')', $debug-10);
    $result = array();
    
    // temp until the call is reviewed
    $wrd = new word_dsp;
    $wrd->id  = $phr_id;
    $wrd->usr = $this->usr;
    $wrd->load($debug-1);

    $val_lst = New value_list;
    $val_lst->usr = $this->usr;
    $value_lst = $val_lst->load_frm_related_grp_phrs($phr_id, $frm_phr_ids, $this->usr->id, $debug-5);
    
    foreach (array_keys($value_lst) AS $val_id) {
      /* maybe use for debugging */
      if ($debug > 0) {
        $debug_txt = "";
        $debug_phr_ids = $value_lst[$val_id][1];
        foreach ($debug_phr_ids AS $debug_phr_id) {
          $debug_wrd = new word_dsp;
          $debug_wrd->id = $debug_phr_id;
          $debug_wrd->usr = $this->usr;
          $debug_wrd->load($debug-1);
          $debug_txt .= ", ".$debug_wrd->name;
        }
      }
      log_debug('fv_lst->add_frm_val -> calc '.$frm_row['formula_name'].' for '.$wrd->name.' ('.$phr_id.')'.$debug_txt, $debug-10);

      // get the group words
      $phr_ids = $value_lst[$val_id][1];
      // add the formula assigned word if needed
      if (!in_array($phr_id, $phr_ids)) {
        $phr_ids[] = $phr_id;
      }
      
      // build the single calculation request
      $calc_row = array();
      $calc_row['usr_id']   = $this->usr->id;
      $calc_row['frm_id']   = $frm_row['formula_id'];
      $calc_row['frm_name'] = $frm_row['formula_name'];
      $calc_row['frm_text'] = $frm_row['formula_text'];
      $calc_row['phr_ids']  = $phr_ids;
      $result[] = $calc_row;
    }  
              
    log_debug('fv_lst->add_frm_val -> number of values added ('.count($result).')', $debug-10);
    return $result;
  }

  // add all formula results to the list that may needs to be updated if a formula is updated for one user
  // todo: only request the user specific calculation if needed
  function frm_upd_lst_usr($phr_lst_frm_assigned, $phr_lst_frm_used, $phr_grp_lst_used, $usr, $last_msg_time, $collect_pos, $debug) {
    log_debug('fv_lst->frm_upd_lst_usr('.$this->frm->name.',fat'.$phr_lst_frm_assigned->name($debug-1).',ft'.$phr_lst_frm_used->name($debug-1).','.$usr->name.')', $debug-9);
    $result = New batch_job_list;
    $added = 0;
    
    // todo: check if the assigned words are different for the user
    
    // todo: check if the formula words are different for the user

    // todo: check if the assigned words, formula words OR the user has different values or formula values

    // todo: filter the words if just a value has been updated
/*    if (!empty($val_wrd_lst)) {
      zu_debug('fv_lst->frm_upd_lst_usr -> update related words ('.implode(",",$val_wrd_lst).')', $debug-9);
      $used_word_ids = array_intersect($is_word_ids, array_keys($val_wrd_lst));
      zu_debug('fv_lst->frm_upd_lst_usr -> needed words ('.implode(",",$used_word_ids).' instead of '.implode(",",$is_word_ids).')', $debug-9);
    } else {
      $used_word_ids = $is_word_ids;
    } */
    
    // create the calc request 
    foreach ($phr_grp_lst_used->phr_lst_lst AS $phr_lst) {
      // remove the formula words from the word group list
      log_debug('remove the formula words "'.$phr_lst->name().'" from the request word list '.$phr_lst->name($debug-1), $debug-10);
      //$phr_lst->remove_wrd_lst($phr_lst_frm_used, $debug-1);
      $phr_lst->diff($phr_lst_frm_used, $debug-1);
      log_debug('removed -> '.$phr_lst->name().')', $debug-12);
      
      // remove double requests
    
      if (!empty($phr_lst->lst)) {
        $calc_request = New batch_job;
        $calc_request->frm     = $this->frm;
        $calc_request->usr     = $usr;
        $calc_request->phr_lst = $phr_lst;
        $result->add($calc_request, $debug-1);
        log_debug('request "'.$this->frm->name.'" for "'.$phr_lst->name().'"', $debug-7-$added);
        $added++;
      }
    }
      
    // loop over the word categories assigned to the formulas
    // get the words where the formula is used including the based on the assigned word e.g. Company or year
    //$sql_result = zuf_wrd_lst ($frm_lst->ids, $this->usr->id, $debug-9);
    //zu_debug('fv_lst->frm_upd_lst_usr -> number of formula assigned words '. mysqli_num_rows ($sql_result), $debug-9);
    //while ($frm_row = mysqli_fetch_array($sql_result, MYSQL_ASSOC)) {
      //zu_debug('fv_lst->frm_upd_lst_usr -> formula '.$frm_row['formula_name'].' ('.$frm_row['resolved_text'].') linked to '.zut_name($frm_row['word_id'], $this->usr->id), $debug-9);
      
    // also use the formula for all related words e.g. if the formula should be used for "Company" use it also for "ABB"
    //$is_word_ids = zut_ids_are($frm_row['word_id'], $this->usr->id, $debug-10); // should later be taken from the original array to increase speed
    
    // include also the main word in the testing
    //$is_word_ids[] = $frm_row['word_id'];
    
    /*
    $used_word_lst = New word_list;
    $used_word_lst->ids    = $used_word_ids;
    $used_word_lst->usr_id = $this->usr->id;
    $used_word_lst->load ($debug-8);
    
    // loop over the words assigned to the formulas
    zu_debug('the formula "'.$frm_row['formula_name'].'" is assigned to "'.zut_name($frm_row['word_id'], $this->usr->id).'", which are '.implode(",",$used_word_lst->names_linked()), $debug-10);
    foreach ($used_word_ids AS $phr_id) {
      $special_frm_phr_ids = array();
      
      if (zuf_has_verb($frm_row['formula_text'], $this->usr->id, $debug-8)) {
        // special case
        zu_debug('fv_lst->frm_upd_lst_usr -> formula has verb ('.$frm_row['formula_text'].')', $debug-10);
      } else {
      
        // include all results of the underlying formulas
        $all_frm_ids = zuf_frm_ids ($frm_row['formula_text'], $this->usr->id, $debug-10);
        
        // get fixed / special formulas
        $frm_ids = array();
        foreach ($all_frm_ids as $chk_frm_id) {
          if (zuf_is_special ($chk_frm_id, $this->usr->id, $debug-10)) {
            $special_frm_phr_ids = $this->frm_upd_lst_frm_special ($chk_frm_id, $frm_row['formula_text'], $this->usr->id, $phr_id, $debug-1);
            
            //get all values related to the words
          } else {
            $frm_ids[] = $chk_frm_id;
          }
        }
        
        // include the results of the underlying formulas, but only the once related to one of the words assigned to the formula
        $result_fv = zuc_upd_lst_fv($val_wrd_lst, $phr_id, $frm_ids, $frm_row, $this->usr->id, $debug-5);
        $result = array_merge($result, $result_fv);
                  
        // get all values related to assigned word and to the formula words
        // and based on this value get the unique word list
        // e.g. if the formula text contains the word "Sales" all values that are related to Sales should be taken into account
        //      $frm_phr_ids is the list of words for the value selection, so in this case it would contain "Sales"
        $frm_phr_ids = zuf_phr_ids ($frm_row['formula_text'], $this->usr->id, $debug-10);
        zu_debug('fv_lst->frm_upd_lst_usr -> frm_phr_ids1 ('.implode(",",$frm_phr_ids).')', $debug-10);
        
        // add word words for the special formulas
        // e.g. if the formula text contains the special word "prior" and the formula is linked to "Year" and "2016" is a "Year"
        //      than the "prior" of "2016" is "2015", so the word "2015" should be included in the value selection
        $frm_phr_ids = array_unique (array_merge ($frm_phr_ids, $special_frm_phr_ids));
        $frm_phr_ids = array_filter($frm_phr_ids);
        zu_debug('fv_lst->frm_upd_lst_usr -> frm_phr_ids2 ('.implode(",",$frm_phr_ids).')', $debug-10);
        
        $result_val = $this->add_frm_val($phr_id, $frm_phr_ids, $frm_row, $this->usr->id, $debug-5);
        // $result_val = zuc_upd_lst_val($phr_id, $frm_phr_ids, $frm_row, $this->usr->id, $debug-5);
        $result = array_merge($result, $result_val);

        // show the user the progress every two seconds
        $last_msg_time = zuc_upd_lst_msg($last_msg_time, $collect_pos, mysqli_num_rows($sql_result), $debug-1);
        $collect_pos++;

      }  
    }  */
    //}  

    //print_r($result);
    log_debug('fv_lst->frm_upd_lst_usr -> ('.count($result->lst).')', $debug-9);
    return $result;
  }

  // get the calculation requests if one formula has been updated
  // returns a batch_job_list with all formula results that may needs to be updated if a formula is updated
  // $frm - formulas that needs to be checked for update
  // $usr - to define which user view should be updated
  function frm_upd_lst($usr, $back, $debug) {
    log_debug('add '.$this->frm->dsp_id($debug-5).' to queue ...', $debug-5);

    // to inform the user about the progress
    $last_msg_time = time(); // the start time
    $collect_pos = 0;        // to calculate the progress in percent
    
    $result = Null;
    
    // get a list of all words and triples where the formula should be used (assigned words)
    // including all child phrases that should also be included in the assignment e.g. for "Year" include "2018"
    // e.g. if the formula is assigned to "Company" and "ABB is a Company" include ABB in the phrase list
    // check in frm_upd_lst_usr only if the user has done any modifications that may influence the word list
    $phr_lst_frm_assigned = $this->frm->assign_phr_lst($debug-1);
    log_debug('formula "'.$this->frm->name.'" is assigned to '.$phr_lst_frm_assigned->name().' for user '.$phr_lst_frm_assigned->usr->name.'', $debug);

    // get a list of all words, triples, formulas and verbs used in the formula
    // e.g. for the formula "net profit" the word "Sales" & "cost of sales" is used
    // for formulas the formula word is used
    $exp = $this->frm->expression($debug-1);
    $phr_lst_frm_used = $exp->phr_verb_lst($back, $debug-1);
    log_debug('formula "'.$this->frm->name.'" uses '.$phr_lst_frm_used->name_linked().' (taken from '.$this->frm->usr_text.')', $debug-1);
    
    // get the list of predefined "following" phrases/formulas like "prior" or "next"
    $phr_lst_preset_following = $exp->element_special_following($back, $debug-1);  
    $frm_lst_preset_following = $exp->element_special_following_frm($back, $debug-1); 

    // combine all used predefined phrases/formulas
    $phr_lst_preset = $phr_lst_preset_following;
    $frm_lst_preset = $frm_lst_preset_following;
    if (!empty($phr_lst_preset->lst)) { log_debug('predefined are '.$phr_lst_preset->name(), $debug-3); }
    
    // exclude the special elements from the phrase list to avoid double usage 
    $phr_lst_frm_used->diff($phr_lst_preset, $debug);
    if ($phr_lst_preset->name() <> '""') { log_debug('Excluding the predefined phrases '.$phr_lst_preset->name().' the formula uses '.$phr_lst_frm_used->name(), $debug-4); }

    // convert the special formulas to normal phrases e.g. use "2018" instead of "this" if the formula is assigned to "Year"
    foreach ($frm_lst_preset_following->lst AS $frm_special) {
      $frm_special->load($debug-1);
      log_debug('fv_lst->frm_upd_lst -> get preset phrases for formula '.$frm_special->dsp_id().' and phrases '.$phr_lst_frm_assigned->name(), $debug-16);
      $phr_lst_preset = $frm_special->special_phr_lst ($phr_lst_frm_assigned, $debug-1);
      log_debug('fv_lst->frm_upd_lst -> got phrases '.$phr_lst_preset->dsp_id(), $debug-14);
    }
    log_debug('the used '.$phr_lst_frm_used->name_linked().' are taken from '.$this->frm->usr_text, $debug-6);
    if ($phr_lst_preset->name() <> '""') { log_debug('the used predefined formulas '.$frm_lst_preset->name().' leading to '.$phr_lst_preset->name(), $debug-5); }
    
    // get the formula phrase name and the formula result phrases to exclude them already in the result phrase selection to avoid loops
    // e.g. to calculate the "increase" of "ABB,Sales" the formula results for "ABB,Sales,increase" should not be used 
    //      because the "increase" of an "increase" is a gradient not an "increase"
    
    // get the phrase name of the formula e.g. "increase"
    if (!isset($this->frm->name_wrd)) { $this->frm->load_wrd($debug-1); }
    $phr_frm = $this->frm->name_wrd; 
    log_debug('For '.$this->frm->usr_text.' formula results with the name '.$phr_frm->name().' should not be used for calculation to avoid loops', $debug-5);

    // get the phrase name of the formula e.g. "percent"
    $exp = $this->frm->expression($debug-1); 
    $phr_lst_fv = $exp->fv_phr_lst($debug-1); 
    if (isset($phr_lst_fv)) { log_debug('For '.$this->frm->usr_text.' formula results with the result phrases '.$phr_lst_fv->name().' should not be used for calculation to avoid loops', $debug-5); }
    
    // depending on the formula setting (all words or at least one word)
    // create a formula value list with all needed word combinations
    // to do this get all values that 
    // 1. have at least one assigned word and one formula word (one of each)
    // 2. remove all assigned words and formula words from the value word list
    // 3. aggregate the word list for all values
    // this is a kind of word group list, where for each word group list several results are possible,
    // because there may be one value and several formula values for the same word group
    log_debug('get all values used in the formula '.$this->frm->usr_text.' that are related to one of the phrases assigned '.$phr_lst_frm_assigned->name(), $debug-6);
    $phr_grp_lst_val = New phrase_group_list;
    $phr_grp_lst_val->usr = $this->usr; // by default the calling user is used, but if needed the value for other users also needs to be updated
    $phr_grp_lst_val->get_by_val_with_one_phr_each($phr_lst_frm_assigned, $phr_lst_frm_used, $phr_frm, $phr_lst_fv, $debug-1);      
    $phr_grp_lst_val->get_by_fv_with_one_phr_each ($phr_lst_frm_assigned, $phr_lst_frm_used, $phr_frm, $phr_lst_fv, $debug-1);
    $phr_grp_lst_val->get_by_val_special          ($phr_lst_frm_assigned, $phr_lst_preset, $phr_frm, $phr_lst_fv, $debug-1); // for predefined formulas ...
    $phr_grp_lst_val->get_by_fv_special           ($phr_lst_frm_assigned, $phr_lst_preset, $phr_frm, $phr_lst_fv, $debug-1); // ... such as "this"
    $phr_grp_lst_used = clone $phr_grp_lst_val;
    
    // first calculate the standard values for all user and than the user specific values
    // than loop over the users and check if the user has changed any value, formula or formula assignment
    $usr_lst = New user_list;
    $usr_lst->load_active ($debug-10);
    
    log_debug('active users ('.implode(",",$usr_lst->names()).')', $debug-8);
    foreach ($usr_lst->usr_lst AS $usr) {
      // check 
      $usr_calc_needed = False;
      if ($usr->id == $this->usr->id) {
        $usr_calc_needed = true;
      }
      if ($this->usr->id == 0 OR $usr_calc_needed) {
        log_debug('update values for user: '.$usr->name.' and formula '.$this->frm->name, $debug-6);

        $result = $this->frm_upd_lst_usr($phr_lst_frm_assigned, $phr_lst_frm_used, $phr_grp_lst_used, $usr, $last_msg_time, $collect_pos, $debug-1);
      }
    }  
    
    //flush();
    log_debug('fv_lst->frm_upd_lst -> ('.count($result->lst).')', $debug-8);
    return $result;
  }
  
  // create a list of all formula results that needs to be updated if a value is updated
  function val_upd_lst($val, $usr, $debug) {
    // check if the default value has been updated and if yes, update the default value
    // get all formula values
  }

  // to review
  // lists all formula values related to one value
  function val_phr_lst($val, $back, $phr_lst, $time_id, $debug) {
    global $db_con;

    $time_phr = New phrase;
    $time_phr->usr = $this->usr;
    $time_phr->id = $time_id;
    $time_phr->load($debug-1);
    log_debug("fv_lst->val_phr_lst ... for value ".$val->id, $debug-10);
    $result = '';

    // list all related formula results
    $formula_links = '';
    $sql = "SELECT l.formula_id, f.formula_text FROM value_formula_links l, formulas f WHERE l.value_id = ".$val->id." AND l.formula_id = f.formula_id;";
    //$db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_lst = $db_con->get($sql, $debug-10);  
    foreach ($db_lst AS $db_fv) {
      $frm_id = $db_fv['formula_id'];
      $formula_text = $db_fv['formula_text'];
      $formula_value = zuc_math_parse($formula_text, $phr_lst->ids, $time_phr, $debug-1);
      // if the formula value is empty use the id to be able to select the formula
      if ($formula_value == '') {
        $formula_value = $db_fv['formula_id'];
      }
      $formula_links .= ' <a href="/http/formula_edit.php?id='.$db_fv['formula_id'].'&back='.$back.'">'.$formula_value.'</a> ';
    }

    if ($formula_links <> '') {
      $result .= ' (or '.$formula_links.')';
    }

    log_debug("fv_lst->val_phr_lst ... done.", $debug-10);
    return $result;
  }

  // add one phrase to the phrase list, but only if it is not yet part of the phrase list
  function add($fv_to_add, $debug) {
    log_debug('phrase_list->add '.$fv_to_add->dsp_id(), $debug-10);
    if (!in_array($fv_to_add->id, $this->ids())) {
      if ($fv_to_add->id <> 0) {
        $this->lst[] = $fv_to_add;
      }
    } else {
      log_debug('phrase_list->add '.$fv_to_add->dsp_id().' not added, because it is already in the list', $debug-10);
    }
  }
  
  // combine two calculation queues
  function merge($lst_to_merge, $debug) {
    // todo remove always $debug from dsp_id
    log_debug('fv_lst->merge '.$lst_to_merge->dsp_id().' to '.$this->dsp_id($debug-1), $debug-12);
    if (isset($lst_to_merge->lst)) {
      foreach ($lst_to_merge->lst AS $new_fv) {
        log_debug('fv_lst->merge add '.$new_fv->dsp_id($debug-1), $debug-18);
        $this->add($new_fv, $debug-1);
      }
    }
    log_debug('fv_lst->merge -> to '.$this->dsp_id($debug-1), $debug-14);
    return $this;
  }
  
}

?>
