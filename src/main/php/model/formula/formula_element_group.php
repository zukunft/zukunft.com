<?php

/*

  formula_element_group.php - a group for formula elements that in combination return a value or a list of values
  -------------------------
  
  e.g. for for "ABB", "differentiator" and "Sector" a list of all sector values is returned
  or in other words for each element group a where clause for value retrieval is created
  
  phrases are always used to select the smallest set of value (in SQL by using "AND" in the where clause)
  e.g. "ABB" "Sales" excludes the values for "ABB income tax" and "Danone Sales"
  
  verbs are always used to add a set of values
  e.g. "ABB", "Sales", "differentiator" and "Sector" will return a list of Sector sales for ABB
       so the SQL statement would be "... WHERE ("ABB" AND "Sales" AND "Sector1") OR ("ABB" AND "Sales" AND "Sector2") OR ....
  
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

class formula_element_group {

  public $lst      = NULL; // array of formula elements such as a word, verb or formula
  public $phr_lst  = NULL; // word list object with the context to retrieve the element number
  public $time_phr = NULL; // the time word for the element number selection
  public $usr      = NULL; // the formula values can differ for each user; this is the user who wants to see the result

  public $symbol   = NULL; // the formula reference text for this element group; used to fill in the numbers into the formula
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
  function dsp_id () {
    $id = implode(",",$this->ids());
    $name = implode(",",$this->names());
    $phr_name  = ''; if (isset($this->phr_lst))  { $phr_name  = $this->phr_lst->name(); }
    $time_name = ''; if (isset($this->time_phr)) { $time_name = $this->time_phr->name; }
    if ($name <> '') {
      $result = '"'.$name.'" ('.$id.')';
    } else {
      $result = 'id ('.$id.')';
    }
    if ($phr_name <> '') {
      $result .= ' and '.$phr_name;
    }  
    if ($time_name <> '') {
      $result .= '@'.$time_name;
    }  

    return $result;
  }  

  // show the element group name to the user in the most simple form (without any ids)
  function name () {
    $result = implode(",",$this->names());
    return $result;
  }
  
  // list of the formula element names independent from the element type
  // this function is called from dsp_id, so no other call is allowed
  private function names () {
    $result = array();

    foreach ($this->lst AS $frm_elm) {
      // display the formula element name
      $result[] .= $frm_elm->name;
    }      

    return $result;
  }  

  private function ids () {
    $result = array();
    if (isset($this->lst)) {
      foreach ($this->lst AS $frm_elm) {
        // use only valid ids
        if ($frm_elm->id <> 0) {
          $result[] = $frm_elm->id;
        }      
      }      
    }      
    return $result;
  }  

  // recreate the element group symbol based on the element list ($this->lst)
  function build_symbol ($debug) {
    $this->symbol = '';

    foreach ($this->lst AS $elm) {
      // build the symbol for the number replacement
      if ($this->symbol == '') {
        $this->symbol  = $elm->symbol;
      } else {
        $this->symbol .= ' '.$elm->symbol;
      }
      zu_debug('formula_element_group->build_symbol -> symbol "'.$elm->symbol.'" added to "'.$this->symbol.'"', $debug-21);
    }      

    return $this->symbol;
  }  

  // list of the formula element names independent from the element type
  function dsp_names ($back, $debug) {
    $result = '';

    foreach ($this->lst AS $frm_elm) {
      // display the formula element name
      $result .= $frm_elm->name_linked($back, $debug-1).' ';
    }      

    return $result;
  }  

  // set the time phrase based on a predefined formula such as "prior" or "next"
  // e.g. if the predefined formula "prior" is used and the time is 2017 than 2016 should be used
  private function set_formula_time_phrase($frm_elm, $val_phr_lst, $debug) {
    zu_debug('formula_element_group->set_formula_time_phrase for '.$frm_elm->dsp_id().' and '.$val_phr_lst->dsp_id(), $debug-10);
    // guess the time word if needed
    if (isset($this->time_phr)) {
      if ($this->time_phr->id == 0) {
        zu_debug('formula_element_group->set_formula_time_phrase -> assume time for '.$val_phr_lst->dsp_id(), $debug-14);
        $val_time_phr = $val_phr_lst->assume_time($debug-1); 
        if (isset($val_time_phr)) {
          $this->time_phr = $val_time_phr; 
        }
      } 
    } else {
      zu_debug('formula_element_group->set_formula_time_phrase -> assume time for '.$val_phr_lst->dsp_id(), $debug-14);
      $val_time_phr = $val_phr_lst->assume_time($debug-1); 
      if (isset($val_time_phr)) {
        $this->time_phr = $val_time_phr; 
      }
    }

    // adjust the element time word if forced by the special formula
    if (isset($this->time_phr)) {
      if ($this->time_phr->id == 0) {
        // switched off because it is not working for "this"
        //zu_err('No time found for "'.$frm_elm->obj->name.'".', 'formula_element_group->figures', '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        zu_debug('formula_element_group->set_formula_time_phrase -> get predefined time result', $debug-14);
        if (isset($frm_elm->obj)) {
          $val_time = $frm_elm->obj->special_time_phr ($this->time_phr, $debug-1);
          if ($val_time->id > 0) {
            $val_time_phr = $val_time;
            if ($val_time_phr->id == 0 OR $val_time_phr->name == '') { $val_time_phr->load($debug-1); }
            zu_debug('formula_element_group->set_formula_time_phrase -> add element word for special formula result '.$val_phr_lst->dsp_id().' taken from the result', $debug-24);
          }
        }
      }
    }
    if (isset($val_time_phr)) {
      // before adding a special time word, remove all other time words from the word list
      $val_phr_lst->ex_time($debug-20);
      $val_phr_lst->add($val_time_phr, $debug-20);
      $this->time_phr = $val_time_phr; 
      zu_debug('formula_element_group->set_formula_time_phrase -> got the special formula word "'.$val_time_phr->name.'" ('.$val_time_phr->id.')', $debug-18);
    }
    
    if (isset($val_time_phr)) {
      zu_debug('formula_element_group->set_formula_time_phrase -> got '.$val_time_phr->dsp_id(), $debug-12);
    }
    
    return $val_time_phr;
  }
  
  /*
  get a list of figures related to the formula element group and a context defined by a list of words
    e.g. for the formula elements <"journey time max premium" "percent"> and the context <"Zurich" "land lot" "minutes">
         the formula value for <"journey time max premium" "percent" "Zurich" "land lot"> should be returned
         and if no value is found, the next best match should be returned
    e.g. for the formula element <"Share price"> and the context <"Nestlé">
         the formula value for <"Share price" "Nestlé" "2016" "CHF"> should be returned 
         if the last share price is from 2016 and CHF is the most important (used) currency
  */
  function figures ($debug) {
    zu_debug('formula_element_group->figures '.$this->dsp_id(), $debug-10);
    
    // init the resulting figure list 
    $fig_lst = New figure_list;
  
    // add the words of the formula element group to the value selection
    // e.g. for the formula "= sales - cost" and the phrases "ABB" the ABB sales is requested
    foreach ($this->lst AS $frm_elm) {
    
      // init the word list for the figure selection because
      // the word list for the figure selection ($val_phr_lst) may differ from the requesting word list ($this->phr_lst) because 
      // e.g. if "percent" is requested and a measure word is part of the request, the measure words are ignored
      $val_phr_lst = clone $this->phr_lst;
      $val_time_phr = $this->time_phr;
      if (isset($val_time_phr)) {
        zu_debug('formula_element_group->figures -> for time '.$val_time_phr->dsp_id(), $debug-9);
      }

      // build the symbol for the number replacement before adding the formula elements
      if ($this->symbol == '') {
        $this->build_symbol($debug-1);
      }

      zu_debug('formula_element_group->figures -> use element '.$frm_elm->dsp_id().' also for value selection', $debug-9);
      
      // get the element word to be able to add it later to the value selection (differs for the element type)
      if ($frm_elm->type == 'word') {
        if ($frm_elm->id > 0) {
          $val_phr_lst->add($frm_elm->obj, $debug-1);
          zu_debug('formula_element_group->figures -> include '.$frm_elm->dsp_id().' in value selection', $debug-8);
        }
      }

      // get the formula related word to be able to add it later to the value selection (differs for the element type)
      if ($frm_elm->type == 'formula') {
        // at the moment the special formulas only change the time word, this is why val_wrd_id is not set here
        if ($frm_elm->obj->is_special($debug-1)) {
          $val_time_phr = $this->set_formula_time_phrase($frm_elm, $val_phr_lst, $debug-1);
          if (isset($val_time_phr)) {
            zu_debug('formula_element_group->figures -> adjusted time '.$val_time_phr->dsp_id(), $debug-9);
          }
        } else {
          if ($frm_elm->wrd_id > 0) {
            $val_phr_lst->add($frm_elm->wrd_obj, $debug-1);
          }
          zu_debug('formula_element_group->figures -> include formula word "'.$frm_elm->wrd_obj->name.'" ('.$frm_elm->wrd_id.')', $debug-8);
        }
      }
    
      // remember the time if adjusted by the formula
      if (isset($val_time_phr)) {
        $fig_lst->time_phr = $val_time_phr;
      }
      
      // exclude the time word from the word group finding, because the main time word should not be included in the word group to reduce the number of word groups
      $val_phr_lst->ex_time($debug-20);
      
      // get the word group
      if (isset($val_phr_lst)) { usort($val_phr_lst, array("phrase", "cmp")); }
      //asort($val_phr_lst);
      $val_phr_grp = $val_phr_lst->get_grp ($debug-10);
      zu_debug('formula_element_group->figures -> words group for "'.$val_phr_lst->name($debug-1).'" = '.$val_phr_grp->id, $debug-10);

      // try to get a normal value set by the user directly for the phrase list
      // display the word group value and offer the user to change it
      // e.g. if the user has overwritten a formula value use the user overwrite
      if (isset($val_time_phr)) {
        zu_debug('formula_element_group->figures -> load word value for '.$val_phr_grp->dsp_id().' and '.$val_time_phr->dsp_id(), $debug-10);
      } else {  
        zu_debug('formula_element_group->figures -> load word value for '.$val_phr_lst->dsp_id(), $debug-10);
      }
      $wrd_val = New value;
      $wrd_val->grp_id  = $val_phr_grp->id;
      $wrd_val->time_id = $val_time_phr->id;
      $wrd_val->usr = $this->usr;
      $wrd_val->load_best($debug-1);

      if ($wrd_val->id > 0) {
        // save the value to the result
        $fig = $wrd_val->figure($debug-10);
        $fig->symbol = $frm_elm->symbol;
        $fig_lst->lst[] = $fig;
        zu_debug('formula_element_group->figures -> value result for '.$val_phr_lst->dsp_id().' = '.$wrd_val->number.' (symbol '.$fig->symbol.')', $debug-8);
      } else {     
        // if there is no number that the user has entered for the word list, try to get the most useful formula result
      
        // temp solution only for the link
        if ($lead_wrd_id <= 0) {
          $lead_wrd = $val_phr_lst->lst[0];
          $lead_wrd_id = 1;
        }

        // get the word group result, which means a formula result
        zu_debug('formula_element_group->figures -> load formula value for '.$val_phr_lst->name($debug-1), $debug-8);
        $grp_fv = New formula_value;
        $grp_fv->phr_grp_id = $val_phr_grp->id;
        $grp_fv->time_id    = $val_time_phr->id;
        $grp_fv->usr        = $this->usr;
        $grp_fv->load($debug-10);
      
        // save the value to the result
        if ($grp_fv->id > 0) {
          $fig = $grp_fv->figure($debug-1);
          $fig->symbol = $this->symbol;
          $fig_lst->lst[] = $fig;

          zu_debug('formula_element_group->figures -> formula value for '.$val_phr_lst->name($debug-1).', time '.$val_time_phr->name.'" (word group '.$val_phr_grp->id.', user '.$this->usr->id.') = '.$grp_fv->value, $debug-9);
        } else {     
          // if there is also not a formula result at least one number of the formula is not valid
          $fig_lst->fig_missing = True;
          zu_debug('formula_element_group->figures -> figure missing', $debug-8);
        }
      }
    }
    
    zu_debug('formula_element_group->figures -> '.count($fig_lst->lst).' found', $debug-10);
    return $fig_lst;
  }

  // the HTML code to display a figure list
  function dsp_values ($back, $time_default, $debug) {
    zu_debug('formula_element_group->dsp_values', $debug-10);

    $result = '';
    
    $fig_lst = $this->figures($debug-1);
    zu_debug('formula_element_group->dsp_values -> got figures', $debug-10);
    
    // show the time if adjusted by a special formula element
    if (isset($fig_lst)) {   
      // build the html code to display the value with the link
      foreach ($fig_lst->lst AS $fig) {
        zu_debug('formula_element_group->dsp_values -> display figure', $debug-10);
        $result .= $fig->display_linked($back, $debug-1);
      }

      // todo: show the time phrase only if it differs from the main time phrase
      if (isset($fig_lst->time_phr) AND isset($time_default)) {
        if ($fig_lst->time_phr->id <> $time_default->id) {
          $result .= ' ('.$fig_lst->time_phr->name.')';
        }
      }

      // display alternative values

    }
    
    zu_debug('formula_element_group->dsp_values -> result "'.$result.'"', $debug-10);
    return $result;
  }  

}