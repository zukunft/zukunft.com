<?php

/*

  phrase.php - either a word or a triple
  ----------
  
  this is not save in a separate table
  e.g. to build a selector the entries are caught either from the words or word_links table
  
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

class phrase {

  public $id           = NULL; // if positive the database id of the word or if negative of a triple
  public $usr          = NULL; // the person for whom the word is loaded, so to say the viewer
  public $name         = '';   // simply the word or triple name to reduce the number of "->" on the code
  public $description  = '';   // simply the word or triple description to reduce the number of "->" on the code
  public $obj          = NULL; // if loaded the linked word or triple object
  
  // in memory only fields
  public $type_name    = '';   // 
  public $is_wrd       = NULL; // the main type object e.g. for "ABB" it is the word object for "Company"
  public $is_wrd_id    = NULL; // the id for the is object
  public $dsp_pos      = NULL; // position of the word on the screen
  public $dsp_lnk_id   = NULL; // position or link id based on which to item is displayed on the screen
  public $link_type_id = NULL; // used in the word list to know based on which relation the word was added to the list
  
  //  load either a word or triple
  function load ($debug) {
    log_debug('phrase->load '.$this->dsp_id(), $debug-10);
    $result = '';
    if ($this->id < 0) {
      $lnk = New word_link;
      $lnk->id  = $this->id * -1;
      $lnk->usr = $this->usr;
      $lnk->load($debug-1);
      $this->obj  = $lnk;
      $this->name = $lnk->name; // is this really useful? better save execution time and have longer code using ->obj->name
      log_debug('phrase->loaded triple '.$this->dsp_id(), $debug-14);
    } elseif ($this->id > 0) {
      $wrd = New word_dsp;
      $wrd->id  = $this->id;
      $wrd->usr = $this->usr;
      $wrd->load($debug-1);
      $this->obj  = $wrd;
      $this->name = $wrd->name;
      log_debug('phrase->loaded word '.$this->dsp_id(), $debug-14);
    } elseif ($this->name <> '') {
      // add to load word link
      $trm = New term;
      $trm->name = $this->name;
      $trm->usr  = $this->usr;
      $trm->load($debug-1);
      if ($trm->type == 'word') {
        $this->obj = $trm->obj;
        $this->id  = $trm->id;
        log_debug('phrase->loaded word '.$this->dsp_id().' by name', $debug-14);
      } elseif ($trm->type == 'triple') {
        $this->obj = $trm->obj;
        $this->id  = $trm->id * -1;
        log_debug('phrase->loaded triple '.$this->dsp_id().' by name', $debug-14);
      } elseif ($trm->type == 'formula') {
        if (isset($trm->obj->name_wrd)) {
          $this->obj = $trm->obj->name_wrd;
          $this->id  = $trm->obj->name_wrd->id;
          log_debug('phrase->loaded formula '.$this->dsp_id().' by name', $debug-14);
        }
      } else {
        if ($this->type_name == '') {
          // TODO check that this is never used for an error detection
          //zu_err('"'.$this->name.'" not found.', "phrase->load", '', (new Exception)->getTraceAsString(), $this->usr);
        } else {  
          log_err('"'.$this->name.'" has the type '.$this->type_name.' which is not expected for a phrase.', "phrase->load", '', (new Exception)->getTraceAsString(), $this->usr);
        }
      }
    }
    log_debug('phrase->load done '.$this->dsp_id(), $debug-14);
    return $result;
  }
  
  // 
  function main_word ($debug) {
    log_debug('phrase->main_word '.$this->dsp_id(), $debug-10);
    $result = Null;

    if ($this->id == 0 OR $this->name == '') {
      $this->load($debug-1); 
    }  
    if ($this->id < 0) {
      $lnk = $this->obj;
      $lnk->load_objects($debug-1); // try do be on the save side and it is anyway checked if loading is really needed
      $result = $lnk->from;
    } elseif ($this->id > 0) {
      $result = $this->obj;
    } else {
      log_err('"'.$this->name.'" has the type '.$this->type_name.' which is not expected for a phrase.', "phrase->main_word", '', (new Exception)->getTraceAsString(), $this->usr);
    }
    log_debug('phrase->main_word done '.$result->dsp_id(), $debug-14);
    return $result;
  }
  
  function type_id ($debug) {
    log_debug('phrase->type_id '.$this->dsp_id(), $debug-10);
    $result = Null;

    $wrd = $this->main_word($debug-1);
    $result = $wrd->type_id;
    
    log_debug('phrase->type_id for '.$this->dsp_id().' is '.$result, $debug-10);
    return $result;
  }
  
  /*
  
  data retrieval functions
  
  */
  
  // get a list of all values related to this phrase
  function val_lst ($debug) {
    log_debug('phrase->val_lst for '.$this->dsp_id().' and user "'.$this->usr->name.'"', $debug-12);
    $val_lst = New value_list;
    $val_lst->usr = $this->usr;
    $val_lst->phr = $this;
    $val_lst->page_size = SQL_ROW_MAX;
    $val_lst->load($debug-1);
    log_debug('phrase->val_lst -> got '.count($val_lst->lst), $debug-14);
    return $val_lst;    
  }
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
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

  // return the name (just because all objects should have a name function)
  function name () {
    //$result = $this->name;
    $result = '"'.$this->name.'"';
    return $result;    
  }

  function name_linked () {
    $result = '<a href="/http/view.php?words='.$this->id.'" title="'.$this->description.'">'.$this->name.'</a>';
    return $result;
  }
  
  function dsp_tbl ($debug) {
    if (!isset($this->obj)) { $this->load($debug-1); }
    log_debug('phrase->dsp_tbl for '.$this->dsp_id(), $debug-10);
    // the function dsp_tbl should exists for words and triples
    $result = $this->obj->dsp_tbl($debug-1);
    return $result;
  }
  
  function dsp_tbl_row ($debug) {
    // the function dsp_tbl_row should exists for words and triples
    if (isset($this->obj)) {
      $result = $this->obj->dsp_tbl_row($debug-1);
    } else {
      log_err('The phrase object is missing for '.$this->dsp_id().'.', "formula_value->load", '', (new Exception)->getTraceAsString(), $this->usr);
    }
    return $result;
  }

  // return the html code to display a word
  function display () {
    $result = '<a href="/http/view.php?words='.$this->id.'">'.$this->name.'</a>';
    return $result;    
  }

  // simply to display a single word or triple link
  function dsp_link () {
    $result = '<a href="/http/view.php?words='.$this->id.'" title="'.$this->description.'">'.$this->name.'</a>';
    return $result;
  }

  // similar to dsp_link 
  function dsp_link_style ($style) {
    $result = '<a href="/http/view.php?words='.$this->id.'" title="'.$this->description.'" class="'.$style.'">'.$this->name.'</a>';
    return $result;
  }

  // helper function that returns a word list object just with the word object
  function lst ($debug) {
    $phr_lst = New phrase_list;
    $phr_lst->usr = $this->usr;
    $phr_lst->add($this, $debug-1);
    log_debug('phrase->lst -> '.$phr_lst->name($debug-1), $debug-18);
    return $phr_lst;
  }

  // returns a list of phrase that are related to this word e.g. for "ABB" it will return "Company" (but not "ABB"???)
  function is ($debug) {
    $this_lst = $this->lst($debug-1);
    $phr_lst = $this_lst->is($debug-1);
    //$phr_lst->add($this,$debug-1);
    log_debug('phrase->is -> '.$this->dsp_id().' is a '.$phr_lst->name($debug-1), $debug-8);
    return $phr_lst;
  }

  public static function cmp($a, $b) {
    return strcmp($a->name, $b->name);
  }
    
  // returns a list of words that are related to this word e.g. for "ABB" it will return "Company" (but not "ABB"???)
/*  function is ($debug) {
    if ($this->id > 0) {
      $wrd_lst = $this->parents($debug-1);
    } else {
    }

    zu_debug('phrase->is -> '.$this->dsp_id().' is a '.$wrd_lst->name($debug-1), $debug-8);
    return $wrd_lst;
  } */

  // true if the word id has a "is a" relation to the related word
  // e.g.for the given word string
  function is_a ($related_phrase, $debug) {
    log_debug('phrase->is_a ('.$this->dsp_id().','.$related_phrase->name.')', $debug-10);

    $result = false;
    $is_phrases = $this->is($debug-1); // should be taken from the original array to increase speed
    if (in_array($related_phrase->id, $is_phrases->ids)) {
      $result = true;
    }
    
    log_debug('phrase->is_a -> '.zu_dsp_bool($result).''.$this->id, $debug-10);
    return $result;
  }

  // SQL to list the user phrases (related to a type if needed)
  function sql_list ($type, $debug) {
    log_debug('phrase->sql_list', $debug-10);
        
    $sql_type_from  = '';
    $sql_type_where = '';

    // if no phrase type is define, list all words and triples
    // todo: but if word has several types don't offer to the user to select the simple word
    $sql_words   = 'SELECT w.word_id AS id, 
                           IF(u.word_name IS NULL, w.word_name, u.word_name) AS name,    
                           IF(u.excluded IS NULL, COALESCE(w.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                      FROM words w   
                 LEFT JOIN user_words u ON u.word_id = w.word_id 
                                       AND u.user_id = '.$this->usr->id.'
                  GROUP BY name ';
    $sql_triples = 'SELECT l.word_link_id * -1 AS id, 
                           IF(u.name IS NULL, l.name, u.name) AS name,    
                           IF(u.excluded IS NULL, COALESCE(l.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                      FROM word_links l
                 LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                            AND u.user_id = '.$this->usr->id.'
                  GROUP BY name ';
                  
    if (isset($type)) {
      if ($type->id > 0) {

        // select all phrase ids of the given type e.g. ABB, DANONE, Zurich
        $sql_wrd_all = 'SELECT from_phrase_id FROM (
                        SELECT DISTINCT
                               l.from_phrase_id,    
                               IF(u.excluded IS NULL, COALESCE(l.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = '.$this->usr->id.'
                         WHERE l.to_phrase_id = '.$type->id.' 
                           AND l.verb_id = '.cl(DBL_LINK_TYPE_IS).' ) AS a 
                         WHERE (excluded <> 1 OR excluded is NULL) ';

        // ... out of all those get the phrase ids that have also other types e.g. Zurich (Canton)
        $sql_wrd_other = 'SELECT from_phrase_id FROM (
                          SELECT DISTINCT
                                 l.from_phrase_id,    
                                 IF(u.excluded IS NULL, COALESCE(l.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                            FROM word_links l
                       LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                  AND u.user_id = '.$this->usr->id.'
                           WHERE l.to_phrase_id <> '.$type->id.' 
                             AND l.verb_id = '.cl(DBL_LINK_TYPE_IS).'
                             AND l.from_phrase_id IN ('.$sql_wrd_all.')  
                        GROUP BY l.from_phrase_id ) AS o 
                           WHERE (excluded <> 1 OR excluded is NULL) ';

        // if a word has no other type, use the word                
        $sql_words = 'SELECT id, name, excluded FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                             IF(u.word_name IS NULL, w.word_name, u.word_name) AS name,    
                             IF(u.excluded IS NULL, COALESCE(w.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                        FROM ( '.$sql_wrd_all.' ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = '.$this->usr->id.'
                       WHERE w.word_id NOT IN ( '.$sql_wrd_other.')                                        
                         AND w.word_id = a.from_phrase_id    
                    GROUP BY name ) AS w 
                       WHERE (excluded <> 1 OR excluded is NULL) ';
                        
        // if a word has another type, use the triple
        $sql_triples = 'SELECT id, name, excluded FROM (
                        SELECT DISTINCT
                               l.word_link_id * -1 AS id, 
                               IF(u.name IS NULL, l.name, u.name) AS name,    
                               IF(u.excluded IS NULL, COALESCE(l.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = '.$this->usr->id.'
                         WHERE l.from_phrase_id IN ( '.$sql_wrd_other.')                                        
                           AND l.verb_id = '.cl(DBL_LINK_TYPE_IS).'
                           AND l.to_phrase_id = '.$type->id.'  
                      GROUP BY name ) AS t 
                         WHERE (excluded <> 1 OR excluded is NULL) ';
        /*                
        $sql_type_from = ', word_links t LEFT JOIN user_word_links ut ON ut.word_link_id = t.word_link_id 
                                                                     AND ut.user_id = '.$this->usr->id.'';
        $sql_type_where_words   = 'WHERE w.word_id = t.from_phrase_id  
                                     AND t.verb_id = '.cl(SQL_LINK_TYPE_IS).'
                                     AND t.to_phrase_id = '.$type->id.' ';
        $sql_type_where_triples = 'WHERE l.to_phrase_id = t.from_phrase_id  
                                     AND t.verb_id = '.cl(SQL_LINK_TYPE_IS).'
                                     AND t.to_phrase_id = '.$type->id.' ';
        $sql_words   = 'SELECT w.word_id AS id, 
                              IF(u.word_name IS NULL, w.word_name, u.word_name) AS name,    
                              IF(u.excluded IS NULL, COALESCE(w.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                          FROM words w   
                    LEFT JOIN user_words u ON u.word_id = w.word_id 
                                          AND u.user_id = '.$this->usr->id.'
                              '.$sql_type_from.'                                        
                              '.$sql_type_where_words.'                                        
                      GROUP BY name';
        $sql_triples = 'SELECT l.word_link_id * -1 AS id, 
                              IF(u.name IS NULL, l.name, u.name) AS name,    
                              IF(u.excluded IS NULL, COALESCE(l.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                          FROM word_links l
                    LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = '.$this->usr->id.'
                              '.$sql_type_from.'                                        
                              '.$sql_type_where_triples.'                                        
                      GROUP BY name';
                      */
      }
    }
    $sql = 'SELECT id, name
              FROM ( '.$sql_words.' UNION '.$sql_triples.' ) AS p
             WHERE p.excluded = 0
          GROUP BY p.name
          ORDER BY p.name;';
    log_debug('phrase->sql_list -> '.$sql, $debug-10);
    return $sql;
  }
  
  /*
  
  display functions
  
  */
  
  // create a selector that contains the words and triples
  // if one form contains more than one selector, $pos is used for identification
  // $type is a word to preselect the list to only those phrases matching this type
  function dsp_selector ($type, $form_name, $pos, $class, $back, $debug) {
    log_debug('phrase->dsp_selector -> type "'.$type->name.'" with id '.$this->id.' selected for form '.$form_name.''.$pos, $debug-10);
    $result = '';
    
    if ($pos > 0) {
      $field_name = "phrase".$pos;
    } else {
      $field_name = "phrase";
    }
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form_name;
    $sel->name       = $field_name;  
    if ($form_name == "value_add" or $form_name == "value_edit") {
      $sel->label    = "";  
    } else {
      if ($pos == 1) {
        $sel->label    = "From:";  
      } elseif ($pos == 2) {
        $sel->label    = "To:";  
      } else {
        $sel->label    = "Word:";  
      }
    }
    $sel->bs_class   = $class;  
    $sel->sql        = $this->sql_list ($type, $debug-1);
    $sel->selected   = $this->id;
    $sel->dummy_text = '... please select';
    $result .= $sel->display ($debug-1);
    
    log_debug('phrase->dsp_selector -> done ', $debug-10);
    return $result;
  }
    
  // simply to display a single word and allow to delete it
  // used by value->dsp_edit
  function dsp_name_del ($del_call, $debug) {
    log_debug('phrase->dsp_name_del', $debug-10);
    if ($this->id > 0) {
      $result = $this->dsp_name_del($del_call, $debug-1);
    } else {
    }
    return $result;
  }

  // button to add a new word similar to this phrase
  function btn_add ($back, $debug) {
    $wrd = $this->main_word($debug-1);
    $result = $wrd->btn_add($back, $debug-1); 
    return $result;    
  }
  
  // returns the best guess category for a word  e.g. for "ABB" it will return only "Company"
  function is_mainly ($debug) {
    $result = Null;
    $is_wrd_lst = $this->is($debug-1);
    if (count($is_wrd_lst->lst) >= 1) {
      $result = $is_wrd_lst->lst[0];
    }
    log_debug('phrase->is_mainly -> ('.$this->dsp_id().' is a '.$result->name.')', $debug-8);
    return $result;
  }
  
  /*
  
  word replication functions
  
  */
  
  function is_time ($debug) {
    $wrd = $this->main_word ($debug-1);
    $result = $wrd->is_time ($debug-1);
    return $result;    
  }

  // return true if the word has the type "measure" (e.g. "meter" or "CHF")
  // in case of a division, these words are excluded from the result
  // in case of add, it is checked that the added value does not have a different measure
  function is_measure ($debug) {
    $wrd = $this->main_word ($debug-1);
    $result = $wrd->is_measure ($debug-1);
    return $result;    
  }

  // return true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
  function is_scaling ($debug) {
    $wrd = $this->main_word ($debug-1);
    $result = $wrd->is_scaling ($debug-1);
    return $result;    
  }

  // return true if the word has the type "scaling_percent" (e.g. "percent")
  function is_percent ($debug) {
    $wrd = $this->main_word ($debug-1);
    $result = $wrd->is_percent ($debug-1);
    return $result;    
  }

  // create a selector that contains the time words
  // e.g. Q1 can be the first Quarter of a year and in this case the four quarters of a year should be the default selection
  //      if this is the triple "Q1 of 2018" a list of triples of this year should be the default selection 
  //      if Q1 is a wikidata qualifier a general time selector should be shown
  function dsp_time_selector ($type, $form_name, $pos, $back, $debug) {
    
    $wrd = $this->main_word ($debug-1);
    $result = $wrd->dsp_time_selector ($type, $form_name, $pos, $back, $debug-1);
    return $result;    
  }
  
  
}