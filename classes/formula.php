<?php

/*

  formula.php - the main formula object
  -----------------
  
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

class formula extends user_sandbox  {

  /*
  // database fields
  public $id           = NULL;  // the database id of the formula, which is the same for the standard and the user specific formula
  public $usr_cfg_id   = NULL;  // the database id if there is alrady some user specific configuration for this formula
  public $usr          = NULL;  // if 0 (not NULL) the standard formula, otherwise the user specific formula
  public $owner_id     = NULL;  // the user id of the person who created the formula, which is the default formula
  public $name         = '';    // simply the formula name, which cannot be empty
  public $excluded     = NULL;  // for this object the excluded field is handled as a normal user sandbox field, but for the list excluded row are like deleted
  */
  public $ref_text     = '';    // the formula expression with the names replaced by database references
  public $usr_text     = '';    // the formula expression in the user format
  public $description  = '';    // describes to the user what this formula is doing
  public $type_id      = NULL;  // the formula type to link special behavier to special formulas like "this" or "next"
  public $need_all_val = false; // calculate and save the result only if all used values are not null
  public $last_update  = NULL;  // the time of the last update of fields that may influence the calculated results

  // in memory only fields
  public $type_cl      = '';    // the code id of the formula type 
  public $type_name    = '';    // the name of the formula type 
  public $name_wrd     = NULL;  // the word object for the formula name: 
                                // because values can only be assigned to words, also for the formula name a word must exist
  public $needs_fv_upd = false; // true if the formula results needs to be updated
  public $ref_text_r   = '';    // the part of the formula expression that is right of the equation sign (used as a work-in-progress field for calculation)
  
  function __construct() {
    $this->type      = 'named';
    $this->obj_name  = 'formula';

    $this->rename_can_switch = UI_CAN_CHANGE_FORMULA_NAME;
  }
    
  function reset($debug) {
    $this->id           = NULL;
    $this->usr_cfg_id   = NULL;
    $this->usr          = NULL;
    $this->owner_id     = NULL;
    $this->excluded     = NULL;
    
    $this->name         = '';

    $this->ref_text     = '';    
    $this->usr_text     = '';    
    $this->description  = '';    
    $this->type_id      = NULL;  
    $this->need_all_val = false; 
    $this->last_update  = NULL;  

    $this->type_cl      = '';    
    $this->type_name    = '';    
    $this->name_wrd     = NULL;  
                                
    $this->needs_fv_upd = false; 
    $this->ref_text_r   = '';    
  }

  // load the corresponding name word for the formula name
  function load_wrd($debug) {
    $do_load = true;
    if (isset($this->name_wrd)) {
      if ($this->name_wrd->name == $this->name) {
        $do_load = false;
      }
    }
    if ($do_load) {
      zu_debug('formula->load_wrd load '.$this->dsp_id(), $debug-12);
      $name_wrd = new word_dsp;
      $name_wrd->name = $this->name;
      $name_wrd->usr  = $this->usr;
      $name_wrd->load($debug-5);
      if ($name_wrd->id > 0) {
        $this->name_wrd = $name_wrd;
      }
    }
  }
  
  
  // create the corresponding name word for the formula name
  function create_wrd($debug) {
    zu_debug('formula->create_wrd create formula linked word '.$this->dsp_id(), $debug-6);
    // if the formula word is missing, try a word creating as a kind of auto recovery
    $name_wrd = new word_dsp;
    $name_wrd->name    = $this->name;
    $name_wrd->type_id = cl(SQL_WORD_TYPE_FORMULA_LINK);
    $name_wrd->usr     = $this->usr;
    $name_wrd->save($debug-1); 
    if ($name_wrd->id > 0) {
      //zu_info('Word with the formula name "'.$this->name.'" has been missing for id '.$this->id.'.','formula->calc', '', (new Exception)->getTraceAsString(), $this->usr);
      $this->name_wrd = $name_wrd;
    } else {
      zu_err('Word with the formula name "'.$this->name.'" missing for id '.$this->id.'.','formula->create_wrd', '', (new Exception)->getTraceAsString(), $this->usr);
    }
  }
  
  // load the formula parameters for all users
  function load_standard($debug) {
    $result = '';
    
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "f.formula_id = ".$this->id;
    } elseif ($this->name <> '') {
      $sql_where = "f.formula_name = ".sf($this->name);
    }

    if ($sql_where == '') {
      $result .= zu_err("ID missing to load the standard formula.", "formula->load_standard", '', (new Exception)->getTraceAsString(), $this->usr);
    } else{  
      $sql = "SELECT f.formula_id,
                     f.user_id,
                     f.formula_name,
                     f.formula_text,
                     f.resolved_text,
                     f.description,
                     f.formula_type_id,
                     t.code_id,
                     f.all_values_needed,
                     f.last_update,
                     f.excluded
                FROM formulas f
           LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id 
               WHERE ".$sql_where.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_rec = $db_con->get1($sql, $debug-5);  
      if ($db_rec['formula_id'] <= 0) {
        $this->reset($debug-1);
      } else {
        $this->id           = $db_rec['formula_id'];
        $this->owner_id     = $db_rec['user_id'];
        $this->name         = $db_rec['formula_name'];
        $this->ref_text     = $db_rec['formula_text'];
        $this->usr_text     = $db_rec['resolved_text'];
        $this->description  = $db_rec['description'];
        $this->type_id      = $db_rec['formula_type_id'];
        $this->type_cl      = $db_rec['code_id'];
        $this->last_update  = new DateTime($db_rec['last_update']);
        $this->excluded     = $db_rec['excluded'];
        zu_debug('formula->load_standard -> field set.', $debug-10);
        if ($db_rec['all_values_needed'] == 1) {
          $this->need_all_val = true;
        } else {
          $this->need_all_val = false;
        }
        
        // to review: try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
          $sql_set = "UPDATE formulas SET user_id = ".$this->usr->id." WHERE formula_id = ".$this->id.";";
          $sql_result = $db_con->exe($sql_set, DBL_SYSLOG_ERROR, "formula->load_standard", (new Exception)->getTraceAsString(), $debug-10);
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }  

      } 
    }  
    zu_debug('formula->load_standard -> done.', $debug-10);
    return $result;
  }
  
  // load the missing formula parameters from the database
  function load($debug) {
    
    // check the all minimal input parameters
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a formula.", "formula->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->id <= 0 AND $this->name == '')  {  
      zu_err("Either the database ID (".$this->id.") or the formula name (".$this->name.") and the user (".$this->usr->id.") must be set to load a formula.", "formula->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // set the where clause depending on the values given
      $sql_where = '';
      if ($this->id > 0 AND !is_null($this->usr->id)) {
        $sql_where = "f.formula_id = ".$this->id;
      }  
      if ($this->name <> '' AND !is_null($this->usr->id)) {
        $sql_where = "f.formula_name = ".sf($this->name);
      }

      if ($sql_where == '') {
        zu_err("Internal error in the where clause.", "formula->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        zu_debug('formula->load by "'.$sql_where.'".', $debug-10);
        // the formula name is excluded from the user sandbox to avoid confusion
        $sql = "SELECT f.formula_id,
                       u.formula_id AS user_formula_id,
                       f.user_id,
                       IF(u.formula_name IS NULL,      f.formula_name,      u.formula_name)      AS formula_name,
                       IF(u.formula_text IS NULL,      f.formula_text,      u.formula_text)      AS formula_text,
                       IF(u.resolved_text IS NULL,     f.resolved_text,     u.resolved_text)     AS resolved_text,
                       IF(u.description IS NULL,       f.description,       u.description)       AS description,
                       IF(u.formula_type_id IS NULL,   f.formula_type_id,   u.formula_type_id)   AS formula_type_id,
                       IF(c.code_id IS NULL,           t.code_id,           c.code_id)           AS code_id,
                       IF(u.all_values_needed IS NULL, f.all_values_needed, u.all_values_needed) AS all_values_needed,
                       IF(u.last_update IS NULL,       f.last_update,       u.last_update)       AS last_update,
                       IF(u.excluded IS NULL,          f.excluded,          u.excluded)          AS excluded
                  FROM formulas f
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = ".$this->usr->id."
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE ".$sql_where.";";
        zu_debug('formula->load sql "'.$sql.'".', $debug-18);
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $db_frm = $db_con->get1($sql, $debug-5);  
        if ($db_frm['formula_id'] <= 0) {
          $this->reset($debug-1);
        } else {
          $this->id           = $db_frm['formula_id'];
          $this->usr_cfg_id   = $db_frm['user_formula_id'];
          $this->owner_id     = $db_frm['user_id'];
          $this->name         = $db_frm['formula_name'];
          $this->ref_text     = $db_frm['formula_text'];
          $this->usr_text     = $db_frm['resolved_text'];
          $this->description  = $db_frm['description'];
          $this->type_id      = $db_frm['formula_type_id'];
          $this->type_cl      = $db_frm['code_id'];
          $this->last_update  = new DateTime($db_frm['last_update']);
          $this->excluded     = $db_frm['excluded'];
          if ($db_frm['all_values_needed'] == 1) {
            $this->need_all_val = true;
          } else {
            $this->need_all_val = false;
          }
          zu_debug('formula->load '.$this->dsp_id().' not excluded.', $debug-10);

          // load the formula name word object
          if ($this->id > 0 AND is_null($this->name_wrd)) {
            $this->load_wrd($debug-1);
          }
        }
      }
    }  
    zu_debug('formula->load -> done '.$this->dsp_id(), $debug-10);
  }

  // 
  function formula_type_name($debug) {
    zu_debug('formula->formula_type_name do.', $debug-16);
    if ($this->type_id > 0) {
      $sql = "SELECT name, description
                FROM formula_types
               WHERE formula_type_id = ".$this->type_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_type = $db_con->get1($sql, $debug-5);  
      $this->type_name = $db_type['name'];
    }
    zu_debug('formula->formula_type_name done '.$this->type_name, $debug-16);
    return $this->type_name;    
  }
  
  // return the true if the formula has a special type and the result is a kind of hardcoded
  // e.g. "this" or "next" where the value of this or the following time word is returned
  function is_special ($debug) {
    $result = false;
    if ($this->type_cl <> "") {
      $result = true;
      zu_debug('formula->is_special -> '.$this->dsp_id(), $debug-8);
    }  
    return $result;
  }

  // return the result of a special formula 
  // e.g. "this" or "next" where the value of this or the following time word is returned
  function special_result ($phr_lst, $time_phr, $debug) {
    zu_debug("formula->special_result (".$this->id.",t".$phr_lst->dsp_id().",time".$time_phr->name." and user ".$this->usr->name.")", $debug-10);    
    $val = Null;

    if ($this->type_id > 0) {
      zu_debug("formula->special_result -> type (".$this->type_cl.")", $debug-8);
      if ($this->type_cl == SQL_FORMULA_TYPE_THIS) {
        $val_phr_lst = clone $phr_lst;
        $val_phr_lst->add($time_phr, $debug-1); // the time word should be added at the end, because ...
        zu_debug("formula->special_result -> this (".$time_phr->name.")", $debug-8);
        $val = $val_phr_lst->value_scaled($debug-1);
      }  
      if ($this->type_cl == SQL_FORMULA_TYPE_NEXT) {
        $val_phr_lst = clone $phr_lst;
        $next_wrd = $time_phr->next();
        if ($next_wrd->id > 0) {
          $val_phr_lst->add($next_wrd, $debug-1); // the time word should be added at the end, because ...
          zu_debug("formula->special_result -> next (".$next_wrd->name.")", $debug-8);
          $val = $val_phr_lst->value_scaled($debug-1);
        }
      }  
      if ($this->type_cl == SQL_FORMULA_TYPE_PREV) {
        $val_phr_lst = clone $phr_lst;
        $prior_wrd = $time_phr->prior();
        if ($prior_wrd->id > 0) {
          $val_phr_lst->add($prior_wrd, $debug-1); // the time word should be added at the end, because ...
          zu_debug("formula->special_result -> prior (".$prior_wrd->name.")", $debug-8);
          $val = $val_phr_lst->value_scaled($debug-1);
        } 
      }  
    }

    zu_debug("formula->special_result -> (".$val->number.")", $debug-1);
    return $val;
  }

  // return the time word id used for the special formula results
  // e.g. "this" or "next" where the value of this or the following time word is returned
  function special_time_phr ($time_phr, $debug) {
    zu_debug('formula->special_time_phr "'.$this->type_cl.'" for '.$time_phr->dsp_id().'', $debug-10);
    $result = $time_phr;

    if ($this->type_id > 0) {
      if ($time_phr->id <= 0) {
        zu_err('No time defined for '.$time_phr->dsp_id().'.', 'formula->special_time_phr', '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        if ($this->type_cl == SQL_FORMULA_TYPE_THIS) {
          $result = $time_phr;
        }  
        if ($this->type_cl == SQL_FORMULA_TYPE_NEXT) {
          $this_wrd = $time_phr->main_word($debug-1);         
          $next_wrd = $this_wrd->next($debug-1);  
          $result = $next_wrd->phrase($debug-1);
        }  
        if ($this->type_cl == SQL_FORMULA_TYPE_PREV) {
          $this_wrd = $time_phr->main_word($debug-1);         
          $prior_wrd = $this_wrd->prior($debug-1);  
          $result = $prior_wrd->phrase($debug-1);
        }  
      }
    }

    zu_debug('formula->special_time_phr got '.$result->dsp_id(), $debug-12);
    return $result;
  }
  
  // get all phrases included by a special formula element for a list of phrases
  // e.g. if the list of phrases is "2016" and "2017" and the special formulas are "prior" and "next" the result should be "2015", "2016","2017" and "2018"
  function special_phr_lst ($phr_lst, $debug) {
    zu_debug('formula->special_phr_lst for '.$phr_lst->dsp_id(), $debug-12);
    $result = clone $phr_lst;
    
    foreach ($phr_lst->lst AS $phr) {
      // temp solution until the real reason is found why the phrase list elements are missing the user settings
      if (!isset($phr->usr)) {
        $phr->usr = $this->usr;
      }
      // get all special phrases
      $time_phr = $this->special_time_phr ($phr, $debug-1);
      if (isset($time_phr)) {
        $result->add($time_phr, $debug-1);
        zu_debug('formula->special_phr_lst -> added time '.$time_phr->dsp_id().' to '.$result->dsp_id(), $debug-18);
      }
    }
    
    zu_debug('formula->special_phr_lst -> '.$result->dsp_id(), $debug-10);
    return $result;
  }
  
  // lists of all words directly assigned to a formula and where the formula should be used 
  function assign_phr_glst_direct($sbx, $debug) {
    $phr_lst = Null;
    
    if ($this->id > 0 AND isset($this->usr)) {
      zu_debug('formula->assign_phr_glst_direct for formula '.$this->dsp_id().' and user "'.$this->usr->name.'".', $debug-12);
      $frm_lnk_lst = New formula_link_list;
      $frm_lnk_lst->usr = $this->usr;
      $frm_lnk_lst->frm = $this;
      $frm_lnk_lst->load($debug-1);
      $phr_ids = $frm_lnk_lst->phrase_ids ($sbx, $debug-1);
      
      if (count($phr_ids) > 0) {
        $phr_lst = New phrase_list;
        $phr_lst->ids = $phr_ids;
        $phr_lst->usr = $this->usr;
        $phr_lst->load($debug-1);
      }
      zu_debug("formula->assign_phr_glst_direct -> number of words ".count($phr_lst->lst), $debug-10);
    } else {
      zu_err("The user id must be set to list the formula links.", "formula->assign_phr_glst_direct", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $phr_lst;
  }

  // the complete list of a phrases assigned to a formula
  function assign_phr_lst_direct($debug) {
    $phr_lst = $this->assign_phr_glst_direct(false, $debug-1);
    return $phr_lst;
  }

  // the user specific list of a phrases assigned to a formula
  function assign_phr_ulst_direct($debug) {
    $phr_lst = $this->assign_phr_glst_direct(true, $debug-1);
    return $phr_lst;
  }

  // returns a list of all words that the formula is assigned to
  // e.g. if the formula is assigned to "Company" and "ABB is a Company" include ABB in the word list
  function assign_phr_glst($sbx, $debug) {
    $phr_lst = New phrase_list;
    $phr_lst->usr = $this->usr;

    if ($this->id > 0 AND isset($this->usr)) {
      $direct_phr_lst = $this->assign_phr_glst_direct($sbx, $debug-1);
      if (count($direct_phr_lst->lst) > 0) {
        zu_debug('formula->assign_phr_glst -> '.$this->dsp_id.' direct assigned words and triples '.$direct_phr_lst->dsp_id(), $debug-10);

        //$indirect_phr_lst = $direct_phr_lst->is($debug-1);
        $indirect_phr_lst = $direct_phr_lst->are($debug-1);
        zu_debug('formula->assign_phr_glst -> indirect assigned words and triples '.$indirect_phr_lst->dsp_id(), $debug-10);

        // merge direct and indirect assigns (maybe later using phrase_list->merge)
        $phr_ids = array_merge($direct_phr_lst->ids, $indirect_phr_lst->ids);
        $phr_ids = array_unique($phr_ids); 
        
        $phr_lst->ids = $phr_ids;
        $phr_lst->load($debug-1);
        zu_debug('formula->assign_phr_glst -> number of words and triples '. count ($phr_lst->lst), $debug-14);
      } else {
        zu_debug('formula->assign_phr_glst -> no words are assigned to '.$this->dsp_id, $debug-14);
      }
    } else {
      zu_err('The user id must be set to list the formula links.', 'formula->assign_phr_glst', '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $phr_lst;
  }


  // the complete list of a phrases assigned to a formula
  function assign_phr_lst($debug) {
    $phr_lst = $this->assign_phr_glst(false, $debug-1);
    return $phr_lst;
  }

  // the user specific list of a phrases assigned to a formula
  function assign_phr_ulst($debug) {
    $phr_lst = $this->assign_phr_glst(true, $debug-1);
    return $phr_lst;
  }

  
  public static function cmp($a, $b) {
    return strcmp($a->name, $b->name);
  }
    
  
  // delete all formula values (results) for this formula
  function fv_del($debug) {
    zu_debug("formula->fv_del (".$this->id.")", $debug-10);
    $result = '';
    $db_con = New mysql;
    $db_con->type = 'formula_value';
    $db_con->usr_id = $this->usr->id;         
    $result .= $db_con->delete('formula_id', $this->id, $debug-5);  
    return $result;    
  }
  
  
  // fill the formula in the reference format with numbers
  // to do: verbs
  function to_num($phr_lst, $debug) {
    zu_debug('get numbers for '.$this->name_linked($back, $debug-1).' and '.$phr_lst->name_linked(), $debug-4);
    
    // check 
    if ($this->ref_text_r == '' AND $this->ref_text <> '') {
      $exp = New expression;
      $exp->ref_text = $this->ref_text;
      $exp->usr      = $this->usr;
      $this->ref_text_r = ZUP_CHAR_CALC . $exp->r_part($debug-1);    
    }

    // guess the time if needed and exclude the time for consistent word groups
    $wrd_lst = $phr_lst->wrd_lst_all($debug-18); 
    $time_wrd = $wrd_lst->assume_time($debug-10); 
    if (isset($time_wrd)) { $time_phr = $time_wrd->phrase($debug-1); }
    $phr_lst_ex = clone $phr_lst; 
    $phr_lst_ex->ex_time($debug-10); 
    zu_debug('formula->to_num -> the phrases excluded time are '.$phr_lst_ex->dsp_id(), $debug-10);

    // create the formula value list
    $fv_lst = New formula_value_list;
    $fv_lst->usr = $this->usr;
    
    // create a master formula value object to only need to fill it with the numbers in the coee below
    $fv_init = New formula_value; // maybe move the contructor of formula_value_list?
    $fv_init->usr = $this->usr;
    $fv_init->frm      = $this;
    $fv_init->frm_id   = $this->id;
    $fv_init->ref_text = $this->ref_text_r;
    $fv_init->num_text = $this->ref_text_r;
    $fv_init->src_phr_lst = clone $phr_lst_ex;
    $fv_init->phr_lst = clone $phr_lst_ex;
    if (isset($time_phr)) { $fv_init->src_time_phr = clone $time_phr; }
    if (isset($time_phr)) { $fv_init->time_phr = clone $time_phr; }
    if ($fv_init->last_val_update < $this->last_update) { $fv_init->last_val_update = $this->last_update; }
 
    // load the formula element groups; similar parts is used in the explain method in formula_value
    // e.g. for "Sales differentiator Sector / Total Sales" the element groups are
    //      "Sales differentiator Sector" and "Total Sales" where 
    //      the element group "Sales differentiator Sector" has the elements: "Sales" (of type word), "differentiator" (verb), "Sector" (word)
    $exp = $this->expression($debug-1);
    $elm_grp_lst = $exp->element_grp_lst ("", $debug-1);
    zu_debug('formula->to_num -> in '.$exp->ref_text.' '.count($elm_grp_lst->lst).' element groups found.', $debug-8);

    // to check if all needed value are given
    $all_elm_grp_filled = true;
    
    // loop over the element groups and replace the symbol with a number
    foreach ($elm_grp_lst->lst AS $elm_grp) {

      // get the figures based on the context e.g. the formula element "Share Price" for the context "ABB" can be 23.11
      // a figure is either the user edited value or a calculated formula result)
      $elm_grp->phr_lst = clone $phr_lst_ex;
      if (isset($time_phr)) { $elm_grp->time_phr = clone $time_phr; }
      $elm_grp->build_symbol($debug-1);
      $fig_lst = $elm_grp->figures($debug-1);
      zu_debug('formula->to_num -> figures ', $debug-8);
      zu_debug('formula->to_num -> figures '.$fig_lst->dsp_id().' ('.count($fig_lst->lst).') for '.$elm_grp->dsp_id(), $debug-8);

      // fill the figure into the formula text and create as much formula values / results as needed
      if (count($fig_lst->lst) == 1) {
        // if no figure if found use the master result as placeholder
        if (count($fv_lst->lst) == 0) {
          $fv_lst->lst[] = $fv_init;
        }
        // fill each formula values created by any previous number filling
        foreach ($fv_lst->lst AS $fv) {
          // fill each formula values created by any previous number filling
          if ($fv->val_missing == False) {
            if ($fig_lst->fig_missing AND $this->need_all_val) {
              zu_debug('formula->to_num -> figure missing.', $debug-8);
              $fv->val_missing == True;
            } else {
              $fig = $fig_lst->lst[0];
              $fv->num_text = str_replace($fig->symbol, $fig->number, $fv->num_text);
              if ($fv->last_val_update < $fig->last_update) { $fv->last_val_update = $fig->last_update; }
              zu_debug('formula->to_num -> one figure "'.$fig->number.'" for "'.$fig->symbol.'" in "'.$fv->num_text.'".', $debug-8);
            }
          }
        }
      } elseif (count($fig_lst->lst) > 1) {
        // create the formula result object only if at least one figure if found
        if (count($fv_lst->lst) == 0) {
          $fv_lst->lst[] = $fv_init;
        }
        // if there is more than one number to fill replicate each previous result, so in fact it multiplicates the number of results
        foreach ($fv_lst->lst AS $fv) {
          $fig_nbr = 1;
          foreach ($fig_lst->lst AS $fig) {
            if ($fv->val_missing == False) {
              if ($fig_lst->fig_missing AND $this->need_all_val) {
                zu_debug('formula->to_num -> figure missing.', $debug-8);
                $fv->val_missing == True;
              } else {
                // for the first previous result, just fill in the first number
                if ($fig_nbr == 1) {
                  
                  // if the result has been the standard result until now 
                  if ($fv->is_std) {
                    // ... and the value is user specific
                    if (!$fig->is_std) { 
                      // split the result into a standard 
                      // get the standard value
                      // $fig_std = ...;
                      $fv_std = clone $fv;
                      $fv_std->usr = 0;
                      $fv_std->num_text = str_replace($fig_std->symbol, $fig_std->number, $fv_std->num_text);
                      if ($fv_std->last_val_update < $fig_std->last_update) { $fv_std->last_val_update = $fig_std->last_update; }
                      zu_debug('formula->to_num -> one figure "'.$fig->number.'" for "'.$fig->symbol.'" in "'.$fv->num_text.'".', $debug-8);
                      $fv_lst->lst[] = $fv_std;
                      // ... and split into a user specific part
                      $fv->is_std = false;
                    }
                  }  
                  
                  $fv->num_text = str_replace($fig->symbol, $fig->number, $fv->num_text);
                  if ($fv->last_val_update < $fig->last_update) { $fv->last_val_update = $fig->last_update; }
                  zu_debug('formula->to_num -> one figure "'.$fig->number.'" for "'.$fig->symbol.'" in "'.$fv->num_text.'".', $debug-8);
                  $fv_master = clone $fv;
                } else {
                  // if the result has been the standard result until now 
                  if ($fv_master->is_std) {
                    // ... and the value is user specific
                    if (!$fig->is_std) { 
                      // split the result into a standard 
                      // get the standard value
                      // $fig_std = ...;
                      $fv_std = clone $fv_master;
                      $fv_std->usr = 0;
                      $fv_std->num_text = str_replace($fig_std->symbol, $fig_std->number, $fv_std->num_text);
                      if ($fv_std->last_val_update < $fig_std->last_update) { $fv_std->last_val_update = $fig_std->last_update; }
                      zu_debug('formula->to_num -> one figure "'.$fig->number.'" for "'.$fig->symbol.'" in "'.$fv->num_text.'".', $debug-8);
                      $fv_lst->lst[] = $fv_std;
                      // ... and split into a user specific part
                      $fv_master->is_std = false;
                    }
                  }  
                  
                  // for all following result reuse the first result and fill with the next number
                  $fv_new = clone $fv_master;
                  $fv_new->num_text = str_replace($fig->symbol, $fig->number, $fv_new->num_text);
                  if ($fv->last_val_update < $fig->last_update) { $fv->last_val_update = $fig->last_update; }
                  zu_debug('formula->to_num -> one figure "'.$fig->number.'" for "'.$fig->symbol.'" in "'.$fv->num_text.'".', $debug-8);
                  $fv_lst->lst[] = $fv_new;
                }
                zu_debug('formula->to_num -> figure "'.$fig->number.'" for "'.$fig->symbol.'" in "'.$fv->num_text.'".', $debug-8);
                $fig_nbr++;
              }
            }
          }
        }
      } else {
        // if not figure found remember to switch off the result if needed
        zu_debug('formula->to_num -> no figures found for '.$elm_grp->dsp_id().' and '.$phr_lst_ex->dsp_id(), $debug-8);
        $all_elm_grp_filled = false;
      }
    }  
    
    // if some values are not filled and all are needed, switch off the incomplete formula results
    if ($this->need_all_val) {
      zu_debug('formula->to_num -> for '.$phr_lst_ex->dsp_id().' all value are needed.', $debug-18);
      if ($all_elm_grp_filled) {
        zu_debug('formula->to_num -> for '.$phr_lst_ex->dsp_id().' all value are filled.', $debug-18);
      } else {
        zu_debug('formula->to_num -> some needed values missing for '.$phr_lst_ex->dsp_id(), $debug-16);
        foreach ($fv_lst->lst AS $fv) {
          zu_debug('formula->to_num -> some needed values missing for '.$fv->dsp_id().' so switch off.', $debug-8);
          $fv->val_missing = True;
        }
      }
    }  

    // calculate the final numeric results
    foreach ($fv_lst->lst AS $fv) {
      // at least the formula update should be used
      if ($fv->last_val_update < $this->last_update) { $fv->last_val_update = $this->last_update; }
      // calculate only if any parameter has been updated since last calculation
      if ($fv->num_text == '') {
        // if num text is empty nothing needs to be done, but aktually this should never happen
      } else {
        if ($fv->last_val_update > $fv->last_update) {
          // check if all needed value exist
          $can_calc = false;
          if ($this->need_all_val) {
            zu_debug('calculate '.$this->name_linked($back, $debug-1).' only if all numbers are given.', $debug-8);
            if ($fv->val_missing) {
              zu_debug('got some numbers for '.$this->name_linked($back, $debug-1).' and '.implode(",",$fv->wrd_ids), $debug-2);
            } else {
              if ($fv->is_std) {
                zu_debug('got all numbers for '.$this->name_linked($back, $debug-1).' and '.$fv->name_linked().': '.$fv->num_text, $debug-2);
              } else {
                zu_debug('got all numbers for '.$this->name_linked($back, $debug-1).' and '.$fv->name_linked().': '.$fv->num_text.' (user specific).', $debug-2);
              }  
              $can_calc = true;
            }
          } else {
            zu_debug('always calculate '.$this->dsp_id(), $debug-8);
            $can_calc = true;
          }
          if ($can_calc == true AND isset($time_wrd)) {
            zu_debug('calculate '.$fv->num_text.' for '.$phr_lst_ex->dsp_id(), $debug-6);
            $fv->value = zuc_math_parse($fv->num_text, $phr_lst_ex->ids, $time_wrd->id, $debug-20);
            $fv->is_updated = true;
            zu_debug('the calculated '.$this->name_linked($back, $debug-1).' is '.$fv->value.' for '.$fv->phr_lst->name_linked(), $debug-1);
          }  
        }
      }
    }
    
    return $fv_lst; 
  }
  
  // create the calculation request for one formula and one usr
  /*
  function calc_requests($phr_lst, $debug) {
    $result = array();

    $calc_request = New batch_job;
    $calc_request->frm     = $this;
    $calc_request->usr     = $this->usr;
    $calc_request->phr_lst = $phr_lst;
    $result[] = $calc_request;
    zu_debug('request "'.$frm->name.'" for "'.$phr_lst->name($debug-1).'"', $debug-10);

    return $result; 
  }
  */
  
  
  // calculate the result for one formula for one user
  // and save the result in the database
  // the $phr_lst is the context for the value retrieval and it also contains any time words
  // the time words are only seperated right before saving to the database
  // always returns an array of formula values
  // todo: check if calculation is really needed
  //       if one of the result words is a scaling word, remove all value scaling words
  //       always create a default result (for the user 0)
  function calc($phr_lst, $debug) {
    $result = Null;
    
    // check the parameters
    if (!isset($phr_lst)) {
      zu_warning('The calculation context for '.$this->dsp_id().' is empty.', 'formula->calc', '', (new Exception)->getTraceAsString(), $this->usr);
    } else {  
      zu_debug('formula->calc '.$this->dsp_id().' for '.$phr_lst->dsp_id(), $debug-9);

      // check if an update of the result is needed
      /*
      $needs_update = true;
      if ($this->has_verb ($this->ref_text, $this->usr->id, $debug-1)) {
        $needs_update = true; // this case will be checked later
      } else {
        $frm_wrd_ids = $this->wrd_ids($this->ref_text, $this->usr->id, $debug-1);
      } */

      // reload the formula if needed, but this should be done by the calling function, so create an info message
      if ($this->name == '' OR is_null($this->name_wrd)) {
        $this->load($debug-1);
        zu_info('formula '.$this->dsp_id().' reloaded.','formula->calc', '', (new Exception)->getTraceAsString(), $this->usr);
      }

      // build the formula expression for calculating the result
      $exp = New expression;
      $exp->ref_text = $this->ref_text;
      $exp->usr      = $this->usr;
      
      // the phrase left of the equation sign should be added to the result
      $fv_add_phr_lst = $exp->fv_phr_lst($debug-1);
      if (isset($fv_add_phr_lst)) { zu_debug('formula->calc -> use words '.$fv_add_phr_lst->dsp_id().' for the result.', $debug-12); }
      // use only the part right of the equation sign for the result calculation
      $this->ref_text_r = ZUP_CHAR_CALC . $exp->r_part($debug-1);
      zu_debug('formula->calc got result words of '.$this->ref_text_r, $debug-12);

      // get the list of the numeric results
      // $fv_lst is a list of all results saved in the database
      $fv_lst = $this->to_num ($phr_lst, $debug-1);
      if (isset($fv_add_phr_lst)) { zu_debug('formula->calc -> '.count($fv_lst->lst).' formula results to save.', $debug-8); }

      // save the numeric results
      foreach ($fv_lst->lst AS $fv) {
        if ($fv->val_missing) {
          // check if fv needs to be remove from the database
          zu_debug('some values missing for '.$fv->dsp_id(), $debug-6);
        } else {
          if ($fv->is_updated) {
            zu_debug('formula result '.$fv->dsp_id().' is updated.', $debug-6);
            // add the formula result word
            // e.g. in the increase formula "percent" should be on the left side of the equation because the result is supposed to be in percent
            if (isset($fv_add_phr_lst)) {
              zu_debug('formula->calc -> add words '.$fv_add_phr_lst->dsp_id().' to the result.', $debug-12);
              foreach ($fv_add_phr_lst->lst AS $frm_result_wrd) {
                $fv->phr_lst->add($frm_result_wrd, $debug-1);
              }
              zu_debug('formula->calc -> added words '.$fv_add_phr_lst->dsp_id().' to the result '.$fv->phr_lst->dsp_id(), $debug-14);
            }  

            // make common assumtions on the word list    
            
            // apply general rules to the result words
            if (isset($fv_add_phr_lst)) {
              zu_debug('formula->calc -> result words "'.$fv_add_phr_lst->dsp_id().'" defined for '.$fv->phr_lst->dsp_id(), $debug-10);
              $fv_add_wrd_lst = $fv_add_phr_lst->wrd_lst_all($debug-1);

              // if the result words contains "percent" remove any measure word from the list, because a relative value is expected without measure
              if ($fv_add_wrd_lst->has_percent($debug-1)) {
                zu_debug('formula->calc -> has percent.', $debug-8);
                $fv->phr_lst->ex_measure($debug-1);
                zu_debug('formula->calc -> measure words removed from '.$fv->phr_lst->dsp_id(), $debug-8);
              }  

              // if in the formula is defined, that the result is in percent 
              // and the values used are in millions, the result is only in percent, but not in millions
              if ($fv_add_wrd_lst->has_percent($debug-1)) {
                $fv->phr_lst->ex_scaling($debug-1);
                zu_debug('formula->calc -> scaling words removed from '.$fv->phr_lst->dsp_id(), $debug-9);
                // maybe add the scaling word to the result words to remember based on which words the result has been created, 
                // but probably this is not needed, because the source words are also savef
                //$scale_wrd_lst = $fv_add_wrd_lst->scaling_lst ($debug-1);
                //$fv->phr_lst->merge($scale_wrd_lst->lst, $debug-1);
                //zu_debug('formula->calc -> added the scaling word "'.implode(",",$scale_wrd_lst->names()).'" to the result words "'.implode(",",$fv->phr_lst->names()).'".', $debug-8);
              }  
            }

            $fv = $fv->save_if_updated ($debug-1);

          }
        }
      }
      
      /*      
        // ??? add the formula name word also to the source words
        $src_phr_lst->add($this->name_wrd, $debug-1);
      */
      
      $result = $fv_lst->lst;
    }
    
    zu_debug('formula->calc -> done.', $debug-18);
    return $result;
  }

  // return the formula expression as an expression element
  function expression ($debug) {
    $exp = New expression;
    $exp->ref_text = $this->ref_text;
    $exp->usr_text = $this->usr_text;
    $exp->usr      = $this->usr;
    zu_debug('formula->expression '.$exp->ref_text.' for user '.$exp->usr->name, $debug-10);
    return $exp;
  }
  
  // create an object for the export
  function export_obj ($debug) {
    zu_debug('formula->export_obj', $debug-10);
    $result = Null;

    if ($this->name <> '')        { $result->name        = $this->name; }
    if ($this->usr_text <> '')    { $result->expression  = $this->usr_text; }
    if ($this->description <> '') { $result->description = $this->description; }
    $phr_lst = $this->assign_phr_lst_direct();
    foreach ($phr_lst->lst AS $phr) {
      if ($phr->id > 0) {
        $result->assigned_word   = $phr->name();
      } else {
        $result->assigned_triple = $phr->name();
      }
    }

    zu_debug('formula->export_obj -> '.json_encode($result), $debug-18);
    return $result;
  }
  
  // import a view from an object
  function import_obj ($debug) {
  }
  
  /*
  
  display functions
  
  */
  
  // return best possible identification for this formula mainly used for debugging
  function dsp_id ($debug) {
    $result = ''; 

    if ($this->name <> '') {
      $result .= '"'.$this->name.'"'; 
      if ($this->id > 0) {
        $result .= ' ('.$this->id.')';
      }  
    } else {
      $result .= $this->id;
    }
    /* the user is in most cases no extra info
    if (isset($this->usr)) {
      $result .= ' for user "'.$this->usr->name.'"';
    }
    */
    return $result;
  }

  // show the formula name to the user in the most simple form (without any ids)
  function name ($debug) {
    return $this->name;
  }
  
  // create the HTML code to display the formula name with the HTML link
  function name_linked ($back, $debug) {
    zu_debug("formula->name_linked", $debug-10);
    $result = '<a href="/http/formula_edit.php?id='.$this->id.'&back='.$back.'">'.$this->name.'</a>';
    return $result;    
  }

  // create the HTML code to display the formula text in the human readable format including links to the formula elements
  function dsp_text ($back, $debug) {
    zu_debug('formula->dsp_text', $debug-14);
    $result = $this->usr_text;
    
    $exp = $this->expression($debug-1);
    $elm_lst = $exp->element_lst($back, $debug-1);
    foreach ($elm_lst->lst AS $elm) {
      zu_debug("formula->display -> replace ".$elm->name." with ".$elm->name_linked($back, $debug-1).".", $debug-20);
      $result  = str_replace('"'.$elm->name.'"', $elm->name_linked($back, $debug-1), $result);
    }

    zu_debug('formula->dsp_text -> '.$result, $debug-18);
    return $result;
  }

  // display the most interesting formula result for one word
  function dsp_result ($wrd, $back, $debug) {
    zu_debug('formula->dsp_result for "'.$wrd->name.'" and formula '.$this->dsp_id(), $debug-14);
    $fv = New formula_value;
    $fv->frm  = $this;
    $fv->wrd  = $wrd;
    $fv->usr  = $this->usr;
    zu_debug('formula->dsp_result load fv.', $debug-14);
    $fv->load($debug-1);
    zu_debug('formula->dsp_result display.', $debug-14);
    $result = $fv->display($back, $debug-1);
    return $result;    
  }

  // create the HTML code for a button to change the formula
  function btn_edit ($back, $debug) {
    $result = btn_edit ('Change formula '.$this->name, '/http/formula_edit.php?id='.$this->id.'&back='.$back);
    return $result; 
  }

  // create the HTML code for a button to change the formula
  function btn_del ($back, $debug) {
    $result = btn_del ('Delete formula '.$this->name, '/http/formula_del.php?id='.$this->id.'&back='.$back);
    return $result; 
  }

  // allow the user to unlick a word
  function dsp_unlink_phr ($phr_id, $back, $debug) {
    zu_debug('formula->dsp_unlink_phr('.$link_id.')', $debug-10);
    $result  = '    <td>'."\n";
    $result .= btn_del ("unlink word", "/http/formula_edit.php?id=".$this->id."&unlink_phrase=".$phr_id."&back=".$back);
    $result .= '    </td>'."\n";
    return $result;
  }

  // display the formula type selector
  function dsp_type_selector($script, $class, $debug) {
    $result = ''; 
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $script;
    $sel->name       = "type";  
    $sel->label      = "Formula type:";  
    $sel->bs_class   = $class;  
    $sel->sql        = sql_lst ("formula_type", $this->usr, $debug-1);
    $sel->selected   = $this->type_id;
    $sel->dummy_text = 'select a predefined type if needed';
    $result .= $sel->display ($debug-1).' ';
    return $result;
  }

  // display the history of a formula
  function dsp_hist($page, $size, $call, $back, $debug) {
    zu_debug("formula->dsp_hist for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".", $debug-10);
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'formula';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist($debug-1);
    
    zu_debug("formula->dsp_hist -> done", $debug-1);
    return $result;
  }

  // display the link history of a formula
  function dsp_hist_links($page, $size, $call, $back, $debug) {
    zu_debug("formula->dsp_hist_links for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".", $debug-10);
    $result = ''; // reset the html code var

    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'formula';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist_links($debug-1);
    
    zu_debug("formula->dsp_hist_links -> done", $debug-1);
    return $result;
  }

  // list all words linked to the formula and allow to unlink or add new words
  function dsp_used4words ($add, $wrd, $back, $debug) {
    zu_debug("formula->dsp_used4words ".$this->ref_text." for ".$wrd->name.",back:".$back." and user ".$this->usr->name.".", $debug-10);
    $result = ''; 
    
    $phr_lst = $this->assign_phr_ulst_direct($debug-1);
    zu_debug("formula->dsp_used4words words linked loaded", $debug-10);
    
    // list all linked words
    $result .= dsp_tbl_start_half ();
    foreach ($phr_lst->lst AS $phr_linked) {
      $result .= '  <tr>'."\n";
      $result .= $phr_linked->dsp_tbl(0, $debug-1);
      $result .= $this->dsp_unlink_phr ($phr_linked->id, $back, $debug-1);
      $result .= '  </tr>'."\n";
    }

    // give the user the possibility to add a simular word
    zu_debug("formula->dsp_used4words user", $debug-10);
    $result .= '  <tr>';
    $result .= '    <td>';
    if ($add == 1 OR $wrd->id > 0) {
      $sel = New selector;
      $sel->usr        = $this->usr;
      $sel->form       = "formula_edit"; // ??? to review
      $sel->name       = 'link_phrase';  
      $sel->dummy_text = 'select a word where the formula should also be used';
      $sel->sql        = sql_lst_usr("word", $this->usr, $debug-1);
      if ($wrd->id > 0) {
        $sel->selected   = $wrd->id;
      } else {  
        $sel->selected   = 0;
      }
      $result .= $sel->display ($debug-1);
    } else {
      if ($this->id > 0) {
        $result .= '      '.btn_add ('add new', '/http/formula_edit.php?id='.$this->id.'&add_link=1&back='.$back);
      }
    }
    $result .= '    </td>';
    $result .= '  </tr>';

    $result .= dsp_tbl_end ();
      
    zu_debug("formula->dsp_used4words -> done", $debug-1);
    return $result;
  }
  
  // allow to test and refresh the formula and show some sample values
  function dsp_test_and_samples ($back, $debug) {
    zu_debug("formula->dsp_test_and_samples ".$this->ref_text.".", $debug-10);
    $result = '<br>'; 
    
    $result .= dsp_btn_text ("Test",            '/http/formula_test.php?id='.$this->id.'&user='.$this->usr->id.'&back='.$back);
    $result .= dsp_btn_text ("Refresh results", '/http/formula_test.php?id='.$this->id.'&user='.$this->usr->id.'&back='.$back.'&refresh=1');

    $result .= '<br><br>';

    // display some sample values
    zu_debug("formula->dsp_test_and_samples value list", $debug-10);
    $fv_lst = New formula_value_list;
    $fv_lst->frm_id = $this->id;
    $fv_lst->usr    = $this->usr;
    zu_debug("formula->dsp_test_and_samples load results for formula id (".$fv_lst->frm_id.")", $debug-12);
    $fv_lst->load (SQL_ROW_LIMIT, $debug-1);
    $sample_val = $fv_lst->display($back, $debug-1);
    if (trim($sample_val) <> "") {
      // just the be on the save side load the related word and create it if needed
      $this->load_wrd($debug-1); 
      if (!isset($this->name_wrd)) {
        $this->create_wrd($debug-1); 
      }
      
      $result .= dsp_text_h3("Results for ".$this->name_wrd->dsp_link($debug-1), "change_hist");
      $result .= $sample_val;
    }
    
    zu_debug("formula->dsp_test_and_samples -> done", $debug-1);
    return $result;
  }
  
  // create the HTML code for the form to adjust a formula
  // $add is the number of new words to be linked
  // $wrd is the word that should be linked (used for a new formula)
  function dsp_edit ($add, $wrd, $back, $debug) {
    zu_debug("formula->dsp_edit ".$this->ref_text." for ".$wrd->name.", back:".$back." and user ".$this->usr->name.".", $debug-10);
    $result = '';
    
    $resolved_text = str_replace('"','&quot;', $this->usr_text);

    // add new or change an existing formula
    if ($this->id <= 0) {
      $script = "formula_add";
      $result .= dsp_text_h2('Add new formula for '.$wrd->dsp_tbl_row($debug-1).' ');
    } else {
      $script = "formula_edit";
      $result .= dsp_text_h2('Formula "'.$this->name.'"');
    }
    $result .= '<div class="row">';

    // when changing a view show the fields only on the left side
    if ($this->id > 0) {
      $result .= '<div class="col-sm-7">';
    }  

    // formula fields
    $result .= dsp_form_start($script);
    $result .= dsp_form_hidden ("id", $this->id);
    $result .= dsp_form_hidden ("word", $wrd->id);
    $result .= dsp_form_hidden ("confirm", 1);
    if (trim($back) <> '') { $result .= dsp_form_hidden ("back", $back); }
    $result .= '<div class="form-row">';
    $result .= dsp_form_fld ("formula_name", $this->name, "Formula name:", "col-sm-8");
    $result .= $this->dsp_type_selector($script, "col-sm-4", $debug);    
    $result .= '</div>';
    $result .= dsp_form_fld ("description", $this->description, "Description:");
    // predefined formulas like "this" or "next" should only be changed by an admin
    if (!$this->is_special ($debug-1) OR $this->is_admin($debug-1)) {
      $result .= dsp_form_fld ("formula_text", $resolved_text, "Expression:");
    }
    $result .= dsp_form_fld_checkbox ("need_all_val", $this->need_all_val, "calculate only if all values used in the formula exist");
    $result .= '<br><br>';
    $result .= dsp_form_end('', $back);

    // list the assigned words
    if ($this->id > 0) {
      $result .= '</div>';
      
      // list all words linked to the formula and allow to unlink or add new words
      $comp_html = $this->dsp_used4words ($add, $wrd, $back, $debug);
      // allow to test and refresh the formula and show some sample values
      $nbrs_html = $this->dsp_test_and_samples ($back, $debug);
      // display the user changes 
      $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $hist_html = $changes;
      } else {
        $hist_html = 'Nothing changed yet.';
      }
      $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $link_html = $changes;
      } else {
        $link_html = 'No word have been added or removed yet.';
      }
      $result .= dsp_link_hist_box ('Usage',        $comp_html,
                                    'Test',         $nbrs_html,
                                    'Changes',      $hist_html,
                                    'Link changes', $link_html, $debug-1);
    }
    
    $result .= '</div>';   // of row
    $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away

    zu_debug("formula->dsp_edit -> done.", $debug-10);
    return $result;    
  }

  /* 

  probably to be replace with expression functions
  
  */
  
  // returns a positive word id if the formula string in the database format contains a word link
  function get_word ($formula, $debug) {
    zu_debug("formula->get_word (".$formula.")", $debug-10);
    $result = 0;

    $pos_start = strpos($formula, ZUP_CHAR_WORD_START);
    if ($pos_start === false) {
      $result = 0;
    } else {
      $r_part = zu_str_right_of($formula, ZUP_CHAR_WORD_START);
      $l_part = zu_str_left_of ($r_part,  ZUP_CHAR_WORD_END);
      if (is_numeric($l_part)) {
        $result = $l_part;
        zu_debug("formula->get_word -> ".$result, $debug-1);
      }
    }

    zu_debug("formula->get_word -> (".$result.")", $debug-10);
    return $result;
  }

  function get_formula ($formula, $debug) {
    zu_debug("formula->get_formula (".$formula.")", $debug-10);
    $result = 0;

    $pos_start = strpos($formula, ZUP_CHAR_FORMULA_START);
    if ($pos_start === false) {
      $result = 0;
    } else {
      $r_part = zu_str_right_of($formula, ZUP_CHAR_FORMULA_START);
      $l_part = zu_str_left_of ($r_part,  ZUP_CHAR_FORMULA_END);
      if (is_numeric($l_part)) {
        $result = $l_part;
        zu_debug("formula->get_formula -> ".$result, $debug-1);
      }
    }

    zu_debug("formula->get_formula -> (".$result.")", $debug-10);
    return $result;
  }

  // extracts an array with the word ids from a given formula text
  function wrd_ids ($frm_text, $user_id, $debug) {
    zu_debug('formula->wrd_ids ('.$frm_text.',u'.$user_id.')', $debug-5);
    $result = array();

    // add words to selection
    $new_wrd_id = $this->get_word($frm_text, $debug-10);
    while ($new_wrd_id > 0) {
      if (!in_array($new_wrd_id, $result)) {
        $result[] = $new_wrd_id; 
      }
      $frm_text = zu_str_right_of($frm_text, ZUP_CHAR_WORD_START.$new_wrd_id.ZUP_CHAR_WORD_END);
      $new_wrd_id = $this->get_word($frm_text, $debug-10);
    }

    zu_debug('formula->wrd_ids -> ('.implode(",",$result).')', $debug-1);
    return $result;
  }

  // extracts an array with the formula ids from a given formula text
  function frm_ids ($frm_text, $user_id, $debug) {
    zu_debug('formula->ids ('.$frm_text.',u'.$user_id.')', $debug-5);
    $result = array();

    // add words to selection
    $new_frm_id = $this->get_formula($frm_text, $debug-10);
    while ($new_frm_id > 0) {
      if (!in_array($new_frm_id, $result)) {
        $result[] = $new_frm_id; 
      }
      $frm_text = zu_str_right_of($frm_text, ZUP_CHAR_FORMULA_START.$new_frm_id.ZUP_CHAR_FORMULA_END);
      $new_frm_id = $this->get_formula($frm_text, $debug-10);
    }

    zu_debug('formula->ids -> ('.implode(",",$result).')', $debug-1);
    return $result;
  }

  // update formula links
  // part of element_refresh for one element type and one user
  function element_refresh_type ($frm_text, $element_type, $frm_usr_id, $db_usr_id, $debug) {
    zu_debug('formula->element_refresh_type (f'.$this->id.''.$frm_text.','.$element_type.',u'.$frm_usr_id.')', $debug-5);
    $result = '';

    // read the elements from the formula text
    $elm_type_id = cl($element_type);
    if ($element_type == SQL_FORMULA_PART_TYPE_WORD) {
      $elm_ids = $this->wrd_ids($frm_text, $frm_usr_id, $debug-1);
    }
    if ($element_type == SQL_FORMULA_PART_TYPE_FORMULA) {
      $elm_ids = $this->frm_ids($frm_text, $frm_usr_id, $debug-1);
    }
    zu_debug('formula->element_refresh_type -> got ('.implode(",",$elm_ids).') of type '.$element_type.' from text.', $debug-16);
    
    // read the existing elements from the database
    if ($frm_usr_id > 0) {
      $sql = "SELECT ref_id FROM formula_elements WHERE formula_id = ".$this->id." AND formula_element_type_id = ".$elm_type_id." AND user_id = ".$frm_usr_id.";";
    } else {
      $sql = "SELECT ref_id FROM formula_elements WHERE formula_id = ".$this->id." AND formula_element_type_id = ".$elm_type_id.";";
    }
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'formula_element';         
    $db_lst = $db_con->get($sql, $debug-5);  

    $elm_db_ids = array();
    foreach ($db_lst AS $db_row) {
      $elm_db_ids[] = $db_row['ref_id'];
    }
    zu_debug('formula->element_refresh_type -> got ('.implode(",",$elm_db_ids).') of type '.$element_type.' from database.', $debug-16);
    
    // add missing links
    $elm_add_ids = array_diff ($elm_ids, $elm_db_ids);
    zu_debug('formula->element_refresh_type -> add '.$element_type.' ('.implode(",",$elm_add_ids).')', $debug-1);
    foreach ($elm_add_ids AS $elm_add_id) {
      $field_names    = array();
      $field_values   = array();
      $field_names[]  = 'formula_id';
      $field_values[] =   $this->id;
      if ($frm_usr_id > 0) {
        $field_names[]  =    'user_id';
        $field_values[] = $frm_usr_id;
      }  
      $field_names[]  = 'formula_element_type_id';
      $field_values[] =             $elm_type_id;
      $field_names[]  =     'ref_id';
      $field_values[] = $elm_add_id;
      $add_result .= $db_con->insert($field_names, $field_values, $debug-1);
      // in this case the row id is not needed, but for testing the number of action should be indicated by adding a '1' to the result string
      if ($add_result > 0) { $result .= '1'; } 
    }

    // delete links not needed any more
    $elm_del_ids = array_diff ($elm_db_ids, $elm_ids);
    zu_debug('formula->element_refresh_type -> del '.$element_type.' ('.implode(",",$elm_del_ids).')', $debug-1);
    foreach ($elm_del_ids AS $elm_del_id) {
      $field_names    = array();
      $field_values   = array();
      $field_names[]  = 'formula_id';
      $field_values[] =   $this->id;
      if ($frm_usr_id > 0) {
        $field_names[]  =    'user_id';
        $field_values[] = $frm_usr_id;
      }  
      $field_names[]  = 'formula_element_type_id';
      $field_values[] =             $elm_type_id;
      $field_names[]  =     'ref_id';
      $field_values[] = $elm_del_id;
      $result .= $db_con->delete($field_names, $field_values, $debug-1);
    }  

    zu_debug('formula->element_refresh_type -> ('.zu_dsp_bool($result).')', $debug-1);
    return $result; 
  }

  // extracts an array with the word ids from a given formula text
  function element_refresh ($frm_text, $debug) {
    zu_debug('formula->element_refresh (f'.$this->id.''.$frm_text.',u'.$this->usr->id.')', $debug-5);
    $result = '';

    // refresh the links for the standard formula used if the user has not changed the formula
    if (str_replace ('1','',$result) == '') { $result .= $this->element_refresh_type ($frm_text, SQL_FORMULA_PART_TYPE_WORD, 0, $this->usr->id, $debug-1); }  
    // update formula links of the standard formula
    if (str_replace ('1','',$result) == '') { $result .= $this->element_refresh_type ($frm_text, SQL_FORMULA_PART_TYPE_FORMULA, 0, $this->usr->id, $debug-1); }  

    // refresh the links for the user specific formula
    $sql = "SELECT user_id FROM user_formulas WHERE formula_id = ".$this->id.";";
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_lst = $db_con->get($sql, $debug-5);  
    foreach ($db_lst AS $db_row) {
      // update word links of the user formula
      if (str_replace ('1','',$result) == '') { $result .= $this->element_refresh_type ($frm_text, SQL_FORMULA_PART_TYPE_WORD, $db_row['user_id'], $this->usr->id, $debug-1); }
      // update formula links of the standard formula
      if (str_replace ('1','',$result) == '') { $result .= $this->element_refresh_type ($frm_text, SQL_FORMULA_PART_TYPE_FORMULA, $db_row['user_id'], $this->usr->id, $debug-1); }
    }
    
    zu_debug('formula->element_refresh -> done'.$result, $debug-1);
    return $result; 
  }
  
  
  /*
  
  link functions - add or remove a link to a word (this is user specific, so use the user sandbox)
  
  */
  
  // link this formula to a word or triple
  function link_phr($phr, $debug) {
    $result = '';
    if (isset($phr) AND isset($this->usr)) {
      zu_debug('formula->link_phr link '.$this->dsp_id().' to "'.$phr->name.'" for user "'.$this->usr->name.'".', $debug-12);
      $frm_lnk = New formula_link;
      $frm_lnk->usr = $this->usr;
      $frm_lnk->fob = $this;
      $frm_lnk->tob = $phr;
      $result = $frm_lnk->save ($debug-1);
    }
    return $result; 
  }

  // unlink this formula from a word or triple
  function unlink_phr($phr, $debug) {
    $result = '';
    if (isset($phr) AND isset($this->usr)) {
      zu_debug('formula->unlink_phr unlink '.$this->dsp_id().' from "'.$phr->name.'" for user "'.$this->usr->name.'".', $debug-12);
      $frm_lnk = New formula_link;
      $frm_lnk->usr = $this->usr;
      $frm_lnk->fob = $this;
      $frm_lnk->tob = $phr;
      $result = $frm_lnk->del ($debug-1);
    } else {  
      $result .= zu_err("Cannot unlink formula, phrase is not set.", "formula.php", '', (new Exception)->getTraceAsString(), $usr);  
    }
    return $result; 
  }

  /*
  
  save functions - to update the formula in the database and for the user sandbox
  
  */
  
  // update the database reference text based on the user text
  function set_ref_text($debug) {
    $result = '';
    $exp = New expression;
    $exp->usr_text = $this->usr_text;
    $exp->usr      = $this->usr;
    $this->ref_text = $exp->get_ref_text ($debug-1);
    $result .= $exp->err_text;
    return $result; 
  }

  function is_used($debug) {
    return !$this->not_used($debug-1);
  }
  
  function not_used($debug) {
    zu_debug('formula->not_used ('.$this->id.')', $debug-10);  
    $result = true;
    
    $result = $this->not_changed($debug-1);
/*    $change_user_id = 0;
    $sql = "SELECT user_id 
              FROM user_formulas 
             WHERE formula_id = ".$this->id."
               AND user_id <> ".$this->owner_id."
               AND (excluded <> 1 OR excluded is NULL)";
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $change_user_id = $db_con->get1($sql, $debug-5);  
    if ($change_user_id > 0) {
      $result = false;
    } */
    return $result;
  }

  // true if no other user has modified the formula
  // assuming that in this case not confirmation from the other users for a formula rename is needed
  function not_changed($debug) {
    zu_debug('formula->not_changed ('.$this->id.')', $debug-10);  
    $result = true;
    
    if ($this->owner_id > 0) {
      $sql = "SELECT user_id 
                FROM user_formulas 
              WHERE formula_id = ".$this->id."
                AND user_id <> ".$this->owner_id."
                AND (excluded <> 1 OR excluded is NULL)";
    } else {
      $sql = "SELECT user_id 
                FROM user_formulas 
              WHERE formula_id = ".$this->id."
                AND (excluded <> 1 OR excluded is NULL)";
    }
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    if ($db_row['user_id'] > 0) {
      $result = false;
    }
    zu_debug('formula->not_changed for '.$this->id.' is '.zu_dsp_bool($result), $debug-10);  
    return $result;
  }

  // true if the user is the owner and noone else has changed the formula
  // because if another user has changed the formula and the original value is changed, maybe the user formula also needs to be updated
  function can_change($debug) {
    zu_debug('formula->can_change '.$this->dsp_id().' by user "'.$this->usr->name.'".', $debug-12);  
    $can_change = false;
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $can_change = true;
    }  
    zu_debug('formula->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);  
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

  // create a database record to save user specific settings for this formula
  function add_usr_cfg($debug) {
    $result = false;

    if (!$this->has_usr_cfg) {
      zu_debug('formula->add_usr_cfg for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);

      // check again if there ist not yet a record
      $sql = "SELECT formula_id FROM `user_formulas` WHERE formula_id = ".$this->id." AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      if ($db_row['formula_id'] <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_formula';
        $log_id = $db_con->insert(array('formula_id','user_id'), array($this->id,$this->usr->id), $debug-1);
        if ($log_id <= 0) {
          $result = 'Insert of user_formula failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  function del_usr_cfg_if_not_needed($debug) {
    $result = '';
    zu_debug('formula->del_usr_cfg_if_not_needed pre check for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-12);

    // check again if the user config is still needed (don't use $this->has_usr_cfg to include all updated)
    $sql = "SELECT formula_id,
                   formula_name,
                   formula_text,
                   resolved_text,
                   description,
                   formula_type_id,
                   all_values_needed,
                   excluded
              FROM user_formulas
             WHERE formula_id = ".$this->id." 
               AND user_id = ".$this->usr->id.";";
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $usr_cfg = $db_con->get1($sql, $debug-5);  
    zu_debug('formula->del_usr_cfg_if_not_needed check for "'.$this->dsp_id().' und user '.$this->usr->name.' with ('.$sql.').', $debug-12);
    if ($usr_cfg['formula_id'] > 0) {
      if ($usr_cfg['formula_text']      == ''
      AND $usr_cfg['resolved_text']     == ''
      AND $usr_cfg['description']       == ''
      AND $usr_cfg['formula_type_id']   == Null
      AND $usr_cfg['all_values_needed'] == Null
      AND $usr_cfg['excluded'] == Null) {
        // delete the entry in the user sandbox
        zu_debug('formula->del_usr_cfg_if_not_needed any more for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      } else {
        zu_debug('formula->del_usr_cfg_if_not_needed not true for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);
      }
    }  

    return $result;
  }

  // simply remove a user adjustment without check
  function del_usr_cfg_exe($db_con, $debug) {
    $result = '';

    $db_con->type = 'formula_element';         
    $result .= $db_con->delete(array('formula_id','user_id'), array($this->id,$this->usr->id), $debug-1);
    $db_con->type = 'user_formula';         
    $result .= $db_con->delete(array('formula_id','user_id'), array($this->id,$this->usr->id), $debug-1);
    if (str_replace('1','',$result) <> '') {
      $result .= 'Deletion of user formula '.$this->id.' failed for '.$this->usr->name.'.';
    }
    
    return $result;
  }
  
  // remove user adjustment and log it (used by user.php to undo the user changes)
  function del_usr_cfg($debug) {
    $result = '';

    if ($this->id > 0 AND $this->usr->id > 0) {
      zu_debug('formula->del_usr_cfg  "'.$this->id.' und user '.$this->usr->name, $debug-12);

      $db_type = 'user_formula';
      $log = $this->log_del($debug-1);
      if ($log->id > 0) {
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  

    } else {
      zu_err("The formula database ID and the user must be set to remove a user specific modification.", "formula->del_usr_cfg", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // set the log entry parameter for a new formula
  function log_add($debug) {
    zu_debug('formula->log_add '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'formulas';
    $log->field     = 'formula_name';
    $log->old_value = '';
    $log->new_value = $this->name;
    $log->row_id    = 0; 
    $log->add($debug-1);
    zu_debug('formula->log_add adding formula '.$this->dsp_id().' has been logged.', $debug-14);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one formula field
  function log_upd($debug) {
    zu_debug('formula->log_upd '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table     = 'formulas';
    } else {  
      $log->table     = 'user_formulas';
    }
    
    return $log;    
  }
  
  // set the log entry parameter to delete a formula
  function log_del($debug) {
    zu_debug('formula->log_del '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = 'formulas';
    $log->field     = 'formula_name';
    $log->old_value = $this->name;
    $log->new_value = '';
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // actually update a formula field in the main database record or the user sandbox
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
        $db_con->type = 'user_formula';
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-10);
      }
    }
    return $result;
  }
  
  // update the time stamp to trigger an update of the depending results
  function save_field_trigger_update($db_con, $debug) {
    $this->last_update = new DateTime(); 
    $result .= $db_con->update($this->id, 'last_update', 'Now()', $debug-1);
    zu_debug('formula->save_field_trigger_update timestamp of '.$this->id.' updated to "'.$this->last_update->format('Y-m-d H:i:s').'".', $debug-18);
    
    // save the pending update to the database for the batch calculation
  }
  
  // set the update parameters for the formula text as written by the user if needed
  function save_field_usr_text($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->usr_text <> $this->usr_text) {
      $this->needs_fv_upd = true;
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->usr_text;
      $log->new_value = $this->usr_text;
      $log->std_value = $std_rec->usr_text;
      $log->row_id    = $this->id; 
      $log->field     = 'resolved_text';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the formula in the database reference format
  function save_field_ref_text($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->ref_text <> $this->ref_text) {
      $this->needs_fv_upd = true;
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->ref_text;
      $log->new_value = $this->ref_text;
      $log->std_value = $std_rec->ref_text;
      $log->row_id    = $this->id; 
      $log->field     = 'formula_text';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
      // updating the reference expression is probably relevant for calculation, so force to update the timestamp
      $result .= $this->save_field_trigger_update($db_con, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the formula description
  function save_field_description($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->description <> $this->description) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->description;
      $log->new_value = $this->description;
      $log->std_value = $std_rec->description;
      $log->row_id    = $this->id; 
      $log->field     = 'description';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the formula type
  // todo: save the refrence also in the log
  function save_field_type($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->type_id <> $this->type_id) {
      $this->needs_fv_upd = true;
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->formula_type_name($debug-1);
      $log->old_id    = $db_rec->type_id;
      $log->new_value = $this->formula_type_name($debug-1);
      $log->new_id    = $this->type_id; 
      $log->std_value = $std_rec->formula_type_name($debug-1);
      $log->std_id    = $std_rec->type_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'formula_type_id';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters that define if all formula values are needed to calculate a result
  function save_field_need_all($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->need_all_val <> $this->need_all_val) {
      $this->needs_fv_upd = true;
      $log = $this->log_upd($debug-1);
      if  ($db_rec->need_all_val) { $log->old_value = '1'; } else { $log->old_value = '0'; }
      if    ($this->need_all_val) { $log->new_value = '1'; } else { $log->new_value = '0'; }
      if ($std_rec->need_all_val) { $log->std_value = '1'; } else { $log->std_value = '0'; }
      $log->row_id    = $this->id; 
      $log->field     = 'all_values_needed';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
      // if it is switch on that all fields are needed for the calculation, probably some formula results can be removed
      $result .= $this->save_field_trigger_update($db_con, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the formula word link excluded
  function save_field_excluded($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->excluded <> $this->excluded) {
      $this->needs_fv_upd = true;
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
        $db_con->type = 'user_formula';
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-10);
      }
      // excluding the number can be also relevant for calculation, so force to update the timestamp
      $result .= $this->save_field_trigger_update($db_con, $debug-1);
    }
    return $result;
  }
  
  // save all updated formula fields
  function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = ''; // to set result to a string so that each update adds '1' to '1' which gives '11' instead of 2
    $result .= $this->save_field_usr_text   ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_ref_text   ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_description($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_type       ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_need_all   ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded   ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('formula->save_fields "'.$result.'" fields for '.$this->dsp_id().' has been saved.', $debug-12);
    return $result;
  }
  
  // set the update parameters for the formula text as written by the user if needed
  function save_field_name($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->name <> $this->name) {
      zu_debug('formula->save_field_name to '.$this->dsp_id().' from "'.$db_rec->name.'".', $debug-12);
      $this->needs_fv_upd = true;
      if ($this->can_change($debug-1) AND $this->not_changed($debug-1)) {      
        $log = $this->log_upd($debug-1);
        $log->old_value = $db_rec->name;
        $log->new_value = $this->name;
        $log->std_value = $std_rec->name;
        $log->row_id    = $this->id; 
        $log->field     = 'formula_name';
        $result .= $this->save_field_do($db_con, $log, $debug-1);
        // in case a word link exist, change also the name of the word
        $wrd = new word_dsp;
        $wrd->name = $db_rec->name;
        $wrd->usr  = $this->usr;
        $wrd->load($debug-1);
        $wrd->name = $this->name;
        $result .= $wrd->save($debug-1);
        
      } else {
        // create a new formula 
        // and request the deleteion confirms for the old from all changers
        // ???? or update the user formula table 
      }
    }
    return $result;
  }
  
  // updated the view component name (which is the id field)
  // should only be called if the user is the owner and nobody has used the display component link
  function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->name <> $this->name) {
      zu_debug('formula->save_id_fields to '.$this->dsp_id().' from '.$db_rec->dsp_id().' (standard '.$std_rec->dsp_id().').', $debug-10);
      // in case a word link exist, change also the name of the word
      $wrd = new word_dsp;
      $wrd->name = $db_rec->name;
      $wrd->usr  = $this->usr;
      $wrd->load($debug-1);
      $wrd->name = $this->name;
      $result .= $wrd->save($debug-1);
      zu_debug('formula->save_id_fields word "'.$db_rec->name.'" renamed to '.$wrd->dsp_id(), $debug-10);

      // change the formula name  
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->name;
      $log->new_value = $this->name;
      $log->std_value = $std_rec->name;
      $log->row_id    = $this->id; 
      $log->field     = 'formula_name';
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("formula_name"),
                                              array($this->name), $debug-1);
      }
    }
    zu_debug('formula->save_id_fields for '.$this->dsp_id().' has been done.', $debug-12);
    return $result;
  }
  
  // get the term corresponding to this formula name
  // so in this case, if a word or verb with the same name already exists, get it
  function term($debug) {
    $trm = New term;
    $trm->name = $this->name;
    $trm->usr  = $this->usr;
    $trm->load($debug-1);
    zu_debug('formula->term loaded.', $debug-6);
    return $trm;    
  }

  // check if the id parameters are supposed to be changed 
  function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    zu_debug('formula->save_id_if_updated has name changed from "'.$db_rec->name.'" to '.$this->dsp_id(), $debug-14);
    $result = '';
    
    // if the name has changed, check if word, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
    if ($db_rec->name <> $this->name) {
      // check if a verb or word with the same name is already in the database
      $trm = $this->term($debug-1);      
      if ($trm->id > 0 AND $trm->type <> 'formula') {
        $result .= $trm->id_used_msg($debug-1);
        zu_debug('formula->save_id_if_updated name "'.$trm->name.'" used already as "'.$trm->type.'".', $debug-14);
      } else {
        
        // check if target formula already exists
        zu_debug('formula->save_id_if_updated check if target formula already exists '.$this->dsp_id().' (has been '.$db_rec->dsp_id().').', $debug-14);
        $db_chk = clone $this;
        $db_chk->id = 0; // to force the load by the id fields
        $db_chk->load_standard($debug-10);
        if ($db_chk->id > 0) {
          zu_debug('formula->save_id_if_updated target formula name already exists '.$db_chk->dsp_id(), $debug-14);
          if (UI_CAN_CHANGE_VIEW_COMPONENT_NAME) {
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
            zu_debug('formula->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add '.$this->dsp_id(), $debug-14);
          } else {
            $result .= 'A view component with the name "'.$this->name.'" already exists. Please use another name.';
          }  
        } else {
          zu_debug('formula->save_id_if_updated target formula name does not yet exists '.$db_chk->dsp_id(), $debug-14);
          if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
            // in this case change is allowed and done
            zu_debug('formula->save_id_if_updated change the existing display component link '.$this->dsp_id().' (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'").', $debug-14);
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
            zu_debug('formula->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add '.$this->dsp_id().' (standard "'.$std_rec->dsp_id().'").', $debug-14);
          }
        }
      }
    }  

    zu_debug('formula->save_id_if_updated for '.$this->dsp_id().' has been done.', $debug-12);
    return $result;
  }
  
  // create a new formula
  function add($db_con, $debug) {
    zu_debug('formula->add the formula '.$this->dsp_id(), $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the new formula
      $this->id = $db_con->insert(array("formula_name","user_id","last_update"), array($this->name,$this->usr->id,"Now()"), $debug-1);
      if ($this->id > 0) {
        zu_debug('formula->add formula '.$this->dsp_id().' has been added as '.$this->id, $debug-12);
        // update the id in the log for the correct reference
        $result .= $log->add_ref($this->id, $debug-1);
        // create the related formula word
        $this->create_wrd($debug-1); 

        // create an empty db_frm element to force saving of all set fields
        $db_rec = New formula;
        $db_rec->name = $this->name;
        $db_rec->usr  = $this->usr;
        $std_rec = clone $db_rec;
        // save the formula fields
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);
      } else {
        zu_err("Adding formula ".$this->name." failed.", "formula->add");
      }  
    }  
        
    return $result;
  }
  
  // add or update a formula in the database or create a user formula
  function save($debug) {
    zu_debug('formula->save >'.$this->usr_text.'< (id '.$this->id.') as '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $result = '';
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'formula';         
    
    // check if a new formula is supposed to be added
    if ($this->id <= 0) {
      // check if a verb, formula or word with the same name is already in the database
      zu_debug('formula->save -> add '.$this->dsp_id(), $debug-10);
      $trm = $this->term($debug-1);      
      if ($trm->id > 0) {
        if ($trm->type <> 'formula') {
          $result .= $trm->id_used_msg($debug-1);
        } else {
          $this->id = $trm->id;
          zu_debug('formula->save adding formula name '.$this->dsp_id().' is OK.', $debug-14);
        }  
      }
    }  
      
    // create a new formula or update an existing
    if ($this->id <= 0) {
      // convert the formula text to db format (any error messages should have been returned from the calling user script)
      $result .= $this->set_ref_text($debug-10);
        
      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug('formula->save -> update '.$this->id, $debug-10);
      // read the database values to be able to check if something has been changed; done first, 
      // because it needs to be done for user and general formulas
      $db_rec = New formula;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-10);
      zu_debug('formula->save -> database formula "'.$db_rec->name.'" ('.$db_rec->id.') loaded.', $debug-14);
      $std_rec = New formula;
      $std_rec->id  = $this->id;
      $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
      $std_rec->load_standard($debug-10);
      zu_debug('formula->save -> standard formula "'.$std_rec->name.'" ('.$std_rec->id.') loaded.', $debug-14);
      
      // for a correct user formula detection (function can_change) set the owner even if the formula has not been loaded before the save 
      if ($this->owner_id <= 0) {
        $this->owner_id = $std_rec->owner_id;
      }
      
      // ... and convert the formula text to db format (any error messages should have been returned from the calling user script)
      $result .= $this->set_ref_text($debug-10);

      // check if the id parameters are supposed to be changed 
      if ($result == '') {
        $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec, $debug-1);
      }

      // if a problem has appeared up to here, don't try to save the values
      // the problem is shown to the user by the calling interactive script
      if (str_replace ('1','',$result) == '') {
        $result .= $this->save_fields ($db_con, $db_rec, $std_rec, $debug-1);
      }
      
      // update the reference table for fast calculation
      // a '1' in the result only indicates that an update has been done for testing; '1' doesn't mean that there has been an error 
      if (str_replace ('1','',$result) == '') {
        $result .= $this->element_refresh ($this->ref_text, $debug-1);
      }
    }

    return $result;

  }
  
  // TODO user specific???
  function del_links($db_con, $debug) {
    $result = '';
    $frm_lnk_lst = New formula_link_list;
    $frm_lnk_lst->usr = $this->usr;         
    $frm_lnk_lst->frm = $this;         
    $frm_lnk_lst->load($debug-1);        
    $result .= $frm_lnk_lst->del_without_log($debug-1);        
    return $result;    
  }
  
}

?>
