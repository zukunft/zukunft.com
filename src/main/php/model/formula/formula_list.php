<?php

/*

  formula_list.php - a simple list of formulas
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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class formula_list {

  public $lst = array(); // the list of the loaded formula objects
  public $usr = Null;    // if 0 (not NULL) for standard formulas, otherwise for a user specific formulas
  
  // fields to select the formulas
  public $wrd     = NULL;    // show the formulas related to this word
  public $phr_lst = NULL;    // show the formulas related to this phrase list
  public $ids     = array(); // a list of formula ids to load all formulas at once

  // in memory only fields
  public $back = NULL;    // the calling stack

  // load the missing formula parameters from the database
  // todo: if this list contains already some formula, don't add them again!
  function load($debug) {

    global $db_con;

    // check the all minimal input parameters
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a list of formulas.", "formula_list->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {  

      // set the where clause depending on the given selection parameters
      $sql_from = '';
      $sql_where = '';
      if (count($this->ids) > 0) {
        $sql_from = 'formulas f';
        $sql_where = 'f.formula_id IN ('.implode(',',$this->ids).')';
      } elseif (isset($this->wrd)) {
        $sql_from = 'formula_links l, formulas f';
        $sql_where = 'l.phrase_id = '.$this->wrd->id.' AND l.formula_id = f.formula_id';
      } elseif (isset($this->phr_lst)) {
        if ($this->phr_lst->ids_txt() <> '') {
          $sql_from = 'formula_links l, formulas f';
          $sql_where = 'l.phrase_id IN ('.$this->phr_lst->ids_txt().') AND l.formula_id = f.formula_id';
        } else {
          zu_err("A phrase list is set (".$this->phr_lst->dsp_id()."), but the id list is ".$this->phr_lst->ids_txt().".", "formula_list->load", '', (new Exception)->getTraceAsString(), $this->usr);
        
          $sql_from = 'formula_links l, formulas f';
          $sql_where = 'l.formula_id = f.formula_id';
        }
      } else {
        // load all formulas to check all formula results
        $sql_from = 'formulas f';
        $sql_where = 'f.formula_id > 0';
      }

      if ($sql_where == '') {
        // activate this error message for page loading of the complete formula list
        zu_err("Either the word or the ID list must be set for loading.", "formula_list->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        zu_debug('formula_list->load by ('.$sql_where.')', $debug-22);
        // the formula name is excluded from the user sandbox to avoid confusion
        $sql = "SELECT f.formula_id,
                       f.formula_name,
                       IF(u.formula_text IS NULL,      f.formula_text,      u.formula_text)      AS formula_text,
                       IF(u.resolved_text IS NULL,     f.resolved_text,     u.resolved_text)     AS resolved_text,
                       IF(u.description IS NULL,       f.description,       u.description)       AS description,
                       IF(u.formula_type_id IS NULL,   f.formula_type_id,   u.formula_type_id)   AS formula_type_id,
                       IF(c.code_id IS NULL,           t.code_id,           c.code_id)           AS code_id,
                       IF(u.all_values_needed IS NULL, f.all_values_needed, u.all_values_needed) AS all_values_needed,
                       IF(u.last_update IS NULL,       f.last_update,       u.last_update)       AS last_update,
                       IF(u.excluded IS NULL,          f.excluded,          u.excluded)          AS excluded
                  FROM ".$sql_from." 
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = ".$this->usr->id." 
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE ".$sql_where."
              GROUP BY f.formula_id;";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;         
        $db_frm_lst = $db_con->get($sql, $debug-14);  
        foreach ($db_frm_lst AS $db_frm) {
          if (is_null($db_frm['excluded']) OR $db_frm['excluded'] == 0) {
            $frm = New formula;
            $frm->usr         = $this->usr;
            $frm->id          = $db_frm['formula_id'];
            $frm->name        = $db_frm['formula_name'];
            $frm->ref_text    = $db_frm['formula_text'];
            $frm->usr_text    = $db_frm['resolved_text'];
            $frm->description = $db_frm['description'];
            $frm->type_id     = $db_frm['formula_type_id'];
            $frm->type_cl     = $db_frm['code_id'];
            $frm->last_update = new DateTime($db_frm['last_update']);
            //$frm->excluded    = $db_frm['excluded'];
            if ($db_frm['all_values_needed'] == 1) {
              $frm->need_all_val = true;
            } else {
              $frm->need_all_val = false;
            }
            /*
            if ($frm->type_id > 0) {
              $sql_type = "SELECT code_id 
                            FROM formula_types 
                            WHERE formula_type_id = ".$frm->type_id.";";
              $db_type = $db_con->get1($sql_type, $frm->usr_id, $debug-14);  
              $frm->type_cl  = $db_type['code_id'];
            } 
            */
            if ($frm->name <> '') {
              $name_wrd = new word_dsp;
              $name_wrd->name = $frm->name;
              $name_wrd->usr  = $this->usr;
              $name_wrd->load($debug-16);
              $frm->name_wrd = $name_wrd;
            }              
            $this->lst[] = $frm;
          }              
        }
      }  
    }  
  }
  
  // rename the name function to be inline with the other classes
  function dsp_id () {
    $result = $this->name();
    if ($result <> '') {
      $result = '"'.$result.'"';
    }
    return $result;
  }
  
  function name () {
    $result = implode(",",$this->names());
    return $result;
  }
  
  // this function is called from dsp_id, so no other call is allowed
  function names () {
    $result = array();
    foreach ($this->lst AS $frm) {
      $result[] = $frm->name;
    }
    return $result;
  }
  
  // lists all formulas with results related to a word
  function display($debug) {
    zu_debug('formula_list->display '.$this->dsp_id(), $debug-10);
    $result = '';

    $type = 'short';
    if (isset($this->wrd)) {
      // list all related formula results
      usort($this->lst, array("formula", "cmp"));
      foreach ($this->lst AS $frm) {
        // formatting should be moved
        //$resolved_text = str_replace('"','&quot;', $frm->usr_text);
        //$resolved_text = str_replace('"','&quot;', $frm->dsp_text($this->back, $debug-1));
        $formula_value = $frm->dsp_result($this->wrd, $this->back, $debug-1);
        // if the formula value is empty use the id to be able to select the formula
        if ($formula_value == '') {
          $formula_value = $frm->id;
        }
        if ($type == 'short') {
          $result .= ' '.$frm->name_linked($this->back, $debug-1);
          $result .= ' '.$frm->btn_del ($this->back, $debug-1);
          $result .= ', ';
        } else {
          $result .= ' '.$frm->name_linked($this->back, $debug-1);
          $result .= ' ('.$frm->dsp_text($this->back, $debug-1).')';
          $result .= ' '.$frm->btn_del ($this->back, $debug-1);
          $result .= ' <br> ';
        }
      }
    }  

    zu_debug("formula_list->display ... done (".$result.")", $debug-1);
    return $result;
  }

}