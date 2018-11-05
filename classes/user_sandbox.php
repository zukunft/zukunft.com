<?php

/*

  user_sandbox.php - the superclass for saving user specific objects to the database
  ----------------
  
  This superclass is used by the classes words, formula, ... to enable user specific value  
  
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

class user_sandbox {

  // database fields that are used in all objects and that have a specific behavier
  public $id           = NULL; // the database id of the word, which is the same for the standard and the user specific word
  public $usr_cfg_id   = NULL; // the database id if there is alrady some user specific configuration for this word
  public $usr          = NULL; // the person for whom the word is loaded, so to say the viewer
  public $owner_id     = NULL; // the user id of the person who created the word, which is the default word
  public $name         = '';   // simply the word name, which cannot be empty
  public $excluded     = NULL; // the user sandbox for words is implimented, but can be switched off for the complete instance 
                               // when loading the word and saving the excluded field is handled as a normal user sandbox field, 
                               // but for calculation, use and display an excluded should not be used
                               
  public $type         = '';   // the object type to create the correct database fields e.g. for the type "word" the database field for the id is "word_id"
  public $cfg_fld_lst  = Null; // a list of the object specific configuration fields e.g. plural for the word object                             
  

  // load the word parameters for all users
  private function load_standard($debug) {
    $result = '';
    
    // check the parameters
    db_con = New MySQL;
    dd_con->type = $this->type; 
    
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "word_id = ".$this->id;
    } elseif ($this->name <> '') {
      $sql_where = "word_name = ".sf($this->name);
    }

    if ($sql_where == '') {
      $result .= zu_err("ID missing to load the standard word.", "word->load_standard", '', (new Exception)->getTraceAsString(), $this->usr);
    } else{  
      $sql = "SELECT word_id,
                     user_id,
                     word_name,
                     plural,
                     description,
                     ref_url_1,
                     ref_url_2,
                     word_type_id,
                     view_id,
                     excluded
                FROM words
               WHERE ".$sql_where.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_wrd = $db_con->get1($sql, $debug-5);  
      if ($db_wrd['word_id'] > 0) {
        $this->id           = $db_wrd['word_id'];
        $this->owner_id     = $db_wrd['user_id'];
        $this->name         = $db_wrd['word_name'];
        $this->plural       = $db_wrd['plural'];
        $this->description  = $db_wrd['description'];
        $this->ref_1        = $db_wrd['ref_url_1'];
        $this->ref_2        = $db_wrd['ref_url_2'];
        $this->type_id      = $db_wrd['word_type_id'];
        $this->view_id      = $db_wrd['view_id'];
        $this->excluded     = $db_wrd['excluded'];

        // to review: try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } 
    }  
    return $result;
  }
  
  // load the missing word parameters from the database
  function load($debug) {

  // check the all minimal input parameters
    if (!isset($this->usr)) {
      // don't use too specific error text, because for each unique error text a new message is created
      //zu_err('The user id must be set to load word '.$this->dsp_id().'.', "word->load", '', (new Exception)->getTraceAsString(), $this->usr);
      zu_err('The user id must be set to load word.', "word->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->id <= 0 AND $this->name == '')  {  
      zu_err("Either the database ID (".$this->id.") or the word name (".$this->name.") and the user (".$this->usr->id.") must be set to load a word.", "word->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // set the where clause depending on the values given
      $sql_where = '';
      if ($this->id > 0 AND !is_null($this->usr->id)) {
        $sql_where = "w.word_id = ".$this->id;
      }  
      if ($this->name <> '' AND !is_null($this->usr->id)) {
        $sql_where = "w.word_name = ".sf($this->name);
      }

      if ($sql_where == '') {
        zu_err("Internal error in the where clause.", "word->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else{  
        // similar statement used in word_link_list->load, check if changes should be repeated in word_link_list.php
        $sql = "SELECT w.word_id,
                       u.word_id AS user_word_id,
                       w.user_id,
                       IF(u.word_name IS NULL,     w.word_name,     u.word_name)     AS word_name,
                       IF(u.plural IS NULL,        w.plural,        u.plural)        AS plural,
                       IF(u.description IS NULL,   w.description,   u.description)   AS description,
                       IF(u.ref_url_1 IS NULL,     w.ref_url_1,     u.ref_url_1)     AS ref_url_1,
                       IF(u.ref_url_2 IS NULL,     w.ref_url_2,     u.ref_url_2)     AS ref_url_2,
                       IF(u.word_type_id IS NULL,  w.word_type_id,  u.word_type_id)  AS word_type_id,
                       IF(u.view_id IS NULL,       w.view_id,       u.view_id)       AS view_id,
                       w.values,
                       IF(u.excluded IS NULL,      w.excluded,      u.excluded)      AS excluded
                  FROM words w 
             LEFT JOIN user_words u ON u.word_id = w.word_id 
                                   AND u.user_id = ".$this->usr->id." 
                 WHERE ".$sql_where.";";
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $db_wrd = $db_con->get1($sql, $debug-5);  
        if (is_null($db_wrd['excluded']) OR $db_wrd['excluded'] == 0) {
          $this->id           = $db_wrd['word_id'];
          $this->usr_cfg_id   = $db_wrd['user_word_id'];
          $this->owner_id     = $db_wrd['user_id'];
          $this->name         = $db_wrd['word_name'];
          $this->plural       = $db_wrd['plural'];
          $this->description  = $db_wrd['description'];
          $this->ref_1        = $db_wrd['ref_url_1'];
          $this->ref_2        = $db_wrd['ref_url_2'];
          $this->type_id      = $db_wrd['word_type_id'];
          $this->values       = $db_wrd['values'];
          $this->view_id      = $db_wrd['view_id'];
          $this->excluded     = $db_wrd['excluded'];
          $this->type_name($debug-1);
        } 
        zu_debug('word->loaded '.$this->dsp_id().'.', $debug-12);
      }  
    }  
  }
    
  // return the main word object based on a id text e.g. used in view.php to get the word to display
  function main_wrd_from_txt ($id_txt, $debug) {
    if ($id_txt <> '') {
      zu_debug('word->main_wrd_from_txt from "'.$id_txt.'".', $debug-12);
      $wrd_ids = explode(",",$id_txt);
      zu_debug('word->main_wrd_from_txt check if "'.$wrd_ids[0].'" is a number.', $debug-12);
      if (is_numeric($wrd_ids[0])) {
        $this->id = $wrd_ids[0];
        zu_debug('word->main_wrd_from_txt from "'.$id_txt.'" got id '.$this->id.'.', $debug-14);
      } else {
        $this->name = $wrd_ids[0];
        zu_debug('word->main_wrd_from_txt from "'.$id_txt.'" got name '.$this->name.'.', $debug-14);
      }
      $this->load($debug-1);
    }  
  }

  /*
  
  data retrival functions
  
  */
  
  // get a list of all values related to this word
  function val_lst ($debug) {
    zu_debug('word->val_lst for "'.$this->name.'" and user "'.$this->usr->name.'".', $debug-12);
    $val_lst = New value_list;
    $val_lst->usr = $this->usr;
    $val_lst->phr = $this->phrase($debug-1);
    $val_lst->page_size = SQL_ROW_MAX;
    $val_lst->load($debug-1);
    zu_debug('word->val_lst -> got '.count($val_lst->lst).'.', $debug-14);
    return $val_lst;    
  }
  
  // if there is just one formula linked to the word, get it
  function formula ($debug) {
    zu_debug('word->formula for "'.$this->name.'" and user "'.$this->usr->name.'".', $debug-10);

    $sql = "SELECT formula_id
              FROM formula_links
              WHERE phrase_id = ".$this->id.";";
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $frm_id = $db_con->get1($sql, $debug-5);  
    $frm = New formula;
    $frm->id = $frm_id['formula_id'];         
    $frm->usr = $this->usr;         
    $frm->load($debug-1);       

    return $frm;    
  }

  /*
  
  display functions
  
  */
  
  // display the unique id fields
  function dsp_id ($debug) {
    $result = ''; 

    if ($this->name <> '') {
      $result .= $this->name.' '; 
      if ($this->id > 0) {
        $result .= '('.$this->id.')';
      }  
    } else {
      $result .= $this->id;
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->name;
    }
    return $result;
  }

  // return the name (just because all objects should have a name function)
  function name ($debug) {
    $result = $this->name;
    return $result;    
  }

  // return the html code to display a word
  function display ($back, $debug) {
    $result = '<a href="/http/view.php?words='.$this->id.'&back='.$back.'">'.$this->name.'</a>';
    return $result;    
  }

  // to add a word linked to this word
  // e.g. if this word is "Company" to add another company
  function btn_add ($back, $debug) {
    $vrb_is = cl(SQL_LINK_TYPE_IS);
    $wrd_type = cl(SQL_WORD_TYPE_NORMAL); // maybe base it on the other linked words
    $wrd_add_title = "add a new ".$this->name;
    $wrd_add_call = "/http/word_add.php?verb=".$vrb_is."&word=".$this->id."&type=".$wrd_type."&back=".$back."";
    $result .= btn_add ($wrd_add_title, $wrd_add_call); 
    return $result;    
  }
  
  // 
  private function type_name($debug) {
    if ($this->type_id > 0) {
      $sql = "SELECT type_name, description
                FROM word_types
               WHERE word_type_id = ".$this->type_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_type = $db_con->get1($sql, $debug-5);  
      $this->type_name = $db_type['type_name'];
    }
    return $this->type_name;    
  }
  
  // return true if the word has the given type
  function is_type ($type, $debug) {
    zu_debug('word->is_type ('.$this->name.' is '.$type.')', $debug-10);

    $result = false;
    if ($this->type_id == cl($type)) {
      $result = true;
      zu_debug('word->is_type ('.$this->name.' is '.$type.')', $debug-12);
    }
    return $result;    
  }

  // return true if the word has the type "time"
  function is_time ($debug) {
    $result = $this->is_type (SQL_WORD_TYPE_TIME, $debug-1);
    return $result;    
  }

  // return true if the word has the type "measure" (e.g. "meter" or "CHF")
  // in case of a devision, these words are excluded from the result
  // in case of add, it is checked that the added value does not have a different measure
  function is_measure ($debug) {
    zu_debug('word->is_measure '.$this->dsp_id().'.', $debug-10);
    $result = false;
    if ($result = $this->is_type (SQL_WORD_TYPE_MEASURE, $debug-1)) {
      $result = true;
    }
    return $result;    
  }

  // return true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
  function is_scaling ($debug) {
    $result = false;
    if ($result = $this->is_type (SQL_WORD_TYPE_SCALING,        $debug-1)
     OR $result = $this->is_type (SQL_WORD_TYPE_SCALING_HIDDEN, $debug-1)) {
      $result = true;
    }
    return $result;    
  }

  // return true if the word has the type "scaling_percent" (e.g. "percent")
  function is_percent ($debug) {
    $result = false;
    if ($result = $this->is_type (SQL_WORD_TYPE_SCALING_PCT, $debug-1)) {
      $result = true;
    }
    return $result;    
  }

  // just to fix a problem if a phrase list contains a word
  function type_id ($debug) {
    $result = $this->type_id;
    return $result;
  }
  
  /*
    tree building function
    ----------------------
    
    Overview for words, triples and phrases and it's lists
    
               childs and            parents return the direct parents and childs   without the original phrase(s)
          foaf_childs and       foaf_parents return the    all parents and childs   without the original phrase(s)
                  are and                 is return the    all parents and childs including the original phrase(s) for the specific verb "is a"
             contains                        return the    all             childs including the original phrase(s) for the specific verb "contains" 
                                  is part of return the    all parents              without the original phrase(s) for the specific verb "contains" 
                 next and              prior return the direct parents and childs   without the original phrase(s) for the specific verb "follows"
          followed_by and        follower_of return the    all parents and childs   without the original phrase(s) for the specific verb "follows"
    differentiated_by and differentiator_for return the    all parents and childs   without the original phrase(s) for the specific verb "can_contain"
        
    Samples
    
    the      parents of  "ABB" can be "public limited company"
    the foaf_parents of  "ABB" can be "public limited company" and "company"
                "is" of  "ABB" can be "public limited company" and "company" and "ABB" (used to get all related values)
    the       childs for "company" can include "public limited company"
    the  foaf_childs for "company" can include "public limited company" and "ABB"
               "are" for "company" can include "public limited company" and "ABB" and "company" (used to get all related values)

          "contains" for "balance sheet" is "assets" and "liabilities" and "company" and "balance sheet" (used to get all related values)
        "is part of" for "assets" is "balance sheet" but not "assets" 

              "next" for "2016" is "2017" 
             "prior" for "2017" is "2016" 
    "is followed by" for "2016" is "2017" and "2018"
    "is follower of" for "2016" is "2015" and "2014"

    "wind energy" and "energy" "can be differentiator for" "sector"
                      "sector" "can be differentiated_by"  "wind energy" and "energy"
    
    if "wind energy" "is part of" "energy"
    
  */

  // helper function that returns a word list object just with the word object
  function lst ($debug) {
    $wrd_lst = New word_list;
    $wrd_lst->usr = $this->usr;
    $wrd_lst->add($this, $debug-1);
    return $wrd_lst;
  }

  // returns a list of words that are related to this word e.g. for "Zurich" it will return "Canton", "City" and "Company", but not "Zurich"
  function parents ($debug) {
    zu_debug('word->parents for '.$this->name.' and user '.$this->usr->id.'.', $debug-12);
    $wrd_lst = $this->lst($debug-1);
    $parent_wrd_lst = $wrd_lst->foaf_parents (cl(SQL_LINK_TYPE_IS), $debug-1);
    zu_debug('word->parents are '.$parent_wrd_lst->name().' for '.$this->name.'.', $debug-10);
    return $parent_wrd_lst;
  }
  
  // returns a list of words that are related to this word e.g. for "ABB" it will return "Company" (but not "ABB"???)
  function is ($debug) {
    $wrd_lst = $this->parents($debug-1);
    //$wrd_lst->add($this,$debug-1);
    zu_debug('word->is -> '.$this->name.' is a '.$wrd_lst->name($debug-1).'.', $debug-8);
    return $wrd_lst;
  }

  // returns the best guess category for a word  e.g. for "ABB" it will return only "Company"
  function is_mainly ($debug) {
    $result = Null;
    $is_wrd_lst = $this->is($debug-1);
    if (count($is_wrd_lst->lst) >= 1) {
      $result = $is_wrd_lst->lst[0];
    }
    zu_debug('word->is_mainly -> ('.$this->name.' is a '.$result->name.')', $debug-8);
    return $result;
  }
  
  // returns a list of words that are related to this word e.g. for "Company" it will return "ABB" and others, but not "Company"
  function childs ($debug) {
    zu_debug('word->childs for '.$this->name.' and user '.$this->usr->id.'.', $debug-12);
    $wrd_lst = $this->lst($debug-1);
    $child_wrd_lst = $wrd_lst->foaf_childs (cl(SQL_LINK_TYPE_IS), $debug-1);
    zu_debug('word->childs are '.$child_wrd_lst->name().' for '.$this->name.'.', $debug-10);
    return $child_wrd_lst;
  }
  
  // returns a list of words that are related to this word e.g. for "Company" it will return "ABB" and "Company"
  function are ($debug) {
    $wrd_lst = $this->childs($debug-1);
    $wrd_lst->add($this,$debug-1);
    return $wrd_lst;
  }

  // makes sure that all combinations of "are" and "conatins" are included
  function are_and_contains ($debug) {
    zu_debug('word->are_and_contains for '.$this->name.'.', $debug-18);

    // this first time get all related items
    $wrd_lst = $this->lst();
    $wrd_lst   = $wrd_lst->are     ($debug-1);
    $wrd_lst   = $wrd_lst->contains($debug-1);
    $added_lst = $wrd_lst->diff($this->lst(), $debug-1);
    // ... and after that get only for the new
    if (count($added_lst->lst) > 0) {
      $loops = 0;
      zu_debug('word->are_and_contains -> added '.$added_lst->name().' to '.$wrd_lst->name().'.', $debug-18);
      do {
        $next_lst  = clone $added_lst;
        $next_lst  = $next_lst->are     ($debug-1);
        $next_lst  = $next_lst->contains($debug-1);
        $added_lst = $next_lst->diff($wrd_lst, $debug-1);
        if (count($added_lst->lst) > 0) { zu_debug('word->are_and_contains -> add '.$added_lst->name().' to '.$wrd_lst->name().'.', $debug-18); }  
        $wrd_lst->merge($added_lst, $debug-1);
        $loops++;
      } while (count($added_lst->lst) > 0 AND $loops < MAX_LOOP);
    }
    zu_debug('word->are_and_contains -> '.$this->name.' are_and_contains '.$wrd_lst->name().'.', $debug-8);
    return $wrd_lst;
  }
  
  // return the follow word id based on the predefined verb following
  function next ($debug) {
    zu_debug('word->next '.$this->name.' and user '.$this->usr->name.'.', $debug-10);
    $result = New word_dsp;
    $link_id = cl(SQL_LINK_TYPE_FOLLOW);
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'word_link';         
    $result->id = $db_con->get_value_2key('from_phrase_id', 'to_phrase_id', $this->id, 'verb_id', $link_id, $debug-1);
    $result->usr = $this->usr;
    if ($result->id > 0) {
      $result->load($debug-1);
    }
    return $result;
  }
    
  // return the follow word id based on the predefined verb following
  function prior ($debug) {
    zu_debug('word->prior('.$this->name.',u'.$this->usr->id.')', $debug-10);
    $result = New word_dsp;
    $link_id = cl(SQL_LINK_TYPE_FOLLOW);
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'word_link';         
    $result->id = $db_con->get_value_2key('to_phrase_id', 'from_phrase_id', $this->id, 'verb_id', $link_id, $debug-1);
    $result->usr = $this->usr;
    if ($result->id > 0) {
      $result->load($debug-1);
    }
    return $result;
  }
    
  // returns the more general word as defined by "is part of"
  // e.g. for "Meilen (District) it will return "Zürich (Canton)"
  // for the value selection this should be tested level by level
  // to use by default the most specific value
  function is_part ($debug) {
    zu_debug('word->is('.$this->name.', user '.$this->usr->id.')', $debug-10);
    $link_type_id = cl(SQL_LINK_TYPE_CONTAIN);
    $wrd_lst = $this->lst($debug-1);
    $is_wrd_lst = $wrd_lst->foaf_parents ($link_type_id, $debug-1);

    zu_debug('word->is -> ('.$this->name.' is a '.$is_wrd_lst->name($debug-1).')', $debug-8);
    return $is_wrd_lst;
  }
  
  // returns a list of the link types related to this word e.g. for "Company" the link "are" will be returned, because "ABB" "is a" "Company"
  function link_types ($direction, $debug) {
    zu_debug('word->link_types '.$this->name.' and user '.$this->usr->id.'.', $debug-12);
    $vrb_lst = New verb_list;
    $vrb_lst->wrd       = clone $this;
    $vrb_lst->usr       = $this->usr;
    $vrb_lst->direction = $direction;
    $vrb_lst->load($debug-1);
    return $vrb_lst;
  }
  
  /*
  
  convert functions
  
  */
  
  // convert the word object into a phrase object
  function phrase ($debug) {
    $phr = New phrase;
    $phr->usr  = $this->usr;
    $phr->id   = $this->id;
    $phr->name = $this->name;
    $phr->obj  = $this;
    zu_debug('word->phrase of '.$this->name.'.', $debug-12);
    return $phr;
  }

  /*
  
  save functions
  
  */
  
  private function not_used($debug) {
    zu_debug('word->not_used ('.$this->id.')', $debug-10);  
    $result = true;
    
    $result = $this->not_changed($debug-1);
/*    $change_user_id = 0;
    $sql = "SELECT user_id 
              FROM user_words 
             WHERE word_id = ".$this->id."
               AND user_id <> ".$this->owner_id."
               AND excluded <> 1";
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $change_user_id = $db_con->get1($sql, $debug-5);  
    if ($change_user_id > 0) {
      $result = false;
    } */
    return $result;
  }

  // true if no other user has modified the word
  // assuming that in this case not confirmation from the other users for a word rename is needed
  private function not_changed($debug) {
    zu_debug('word->not_changed ('.$this->id.') by someone else than the onwer ('.$this->owner_id.').', $debug-10);  
    $result = true;
    
    $change_user_id = 0;
    if ($this->owner_id > 0) {
      $sql = "SELECT user_id 
                FROM user_words 
               WHERE word_id = ".$this->id."
                 AND user_id <> ".$this->owner_id."
                 AND excluded <> 1";
    } else {
      $sql = "SELECT user_id 
                FROM user_words 
               WHERE word_id = ".$this->id."
                 AND excluded <> 1";
    }
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $change_user_id = $db_con->get1($sql, $debug-5);  
    if ($change_user_id > 0) {
      $result = false;
    }
    zu_debug('word->not_changed for '.$this->id.' is '.zu_dsp_bool($result).'.', $debug-10);  
    return $result;
  }

  // to be dismissed!
  // if the value has been changed by someone else than the owner the user id is returned
  // but only return the user id if the user has not also excluded it
  function changer($debug) {
    zu_debug('word->changer ('.$this->id.')', $debug-10);  
    
    $sql = "SELECT user_id 
              FROM user_words 
             WHERE word_id = ".$this->id."
               AND excluded <> 1";
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $user_id = $db_con->get1($sql, $debug-5);  
    return $user_id;
  }

  // true if the user is the owner and noone else has changed the word
  // because if another user has changed the word and the original value is changed, maybe the user word also needs to be updated
  function can_change($debug) {
    zu_debug('word->can_change ('.$this->id.',u'.$this->usr->id.')', $debug-10);  
    $can_change = false;
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $wrd_user = $this->changer($debug-1);
      if ($wrd_user == $this->usr->id OR $wrd_user <= 0) {
        $can_change = true;
      }  
    }  

    zu_debug('word->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);  
    return $can_change;
  }

  // true if a record for a user specific configuration already exists in the database
  private function has_usr_cfg($debug) {
    $has_cfg = false;
    if ($this->usr_cfg_id > 0) {
      $has_cfg = true;
    }  
    return $has_cfg;
  }

  // create a database record to save user specific settings for this word
  private function add_usr_cfg($debug) {
    $result = false;

    if (!$this->has_usr_cfg) {
      zu_debug('word->add_usr_cfg for "'.$this->name.' und user '.$this->usr->name.'.', $debug-10);

      // check again if there ist not yet a record
      $sql = "SELECT word_id FROM `user_words` WHERE word_id = ".$this->id." AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_wrd_id = $db_con->get1($sql, $debug-5);  
      if ($usr_wrd_id <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_word';
        $log_id = $db_con->insert(array('word_id','user_id'), array($this->id,$this->usr->id), $debug-1);
        if ($log_id <= 0) {
          $result = 'Insert of user_word failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  private function del_usr_cfg_if_not_needed($debug) {
    $result = '';
    zu_debug('word->del_usr_cfg_if_not_needed pre check for "'.$this->name.' und user '.$this->usr->name.'.', $debug-12);

    //if ($this->has_usr_cfg) {

      // check again if there ist not yet a record
      $sql = "SELECT word_id,
                     word_name,
                     plural,
                     description,
                     ref_url_1,
                     ref_url_2,
                     word_type_id,
                     view_id
                FROM user_words
               WHERE word_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_wrd_cfg = $db_con->get1($sql, $debug-5);  
      zu_debug('word->del_usr_cfg_if_not_needed check for "'.$this->name.' und user '.$this->usr->name.' with ('.$sql.').', $debug-12);
      if ($usr_wrd_cfg['word_id'] > 0) {
        if ($usr_wrd_cfg['plural']       == ''
        AND $usr_wrd_cfg['description']  == ''
        AND $usr_wrd_cfg['ref_url_1']    == ''
        AND $usr_wrd_cfg['ref_url_2']    == ''
        AND $usr_wrd_cfg['word_type_id'] == Null
        AND $usr_wrd_cfg['view_id']      == Null) {
          // delete the entry in the user sandbox
          zu_debug('word->del_usr_cfg_if_not_needed any more for "'.$this->name.' und user '.$this->usr->name.'.', $debug-10);
          $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
        }  
      }  
    //}  
    return $result;
  }

  // simply remove a user adjustment without check
  private function del_usr_cfg_exe($db_con, $debug) {
    $result = '';

    $db_con->type = 'user_word';
    $result .= $db_con->delete(array('word_id','user_id'), array($this->id,$this->usr->id), $debug-1);
    if (str_replace('1','',$result) <> '') {
      $result .= 'Deletion of user word '.$this->id.' failed for '.$this->usr->name.'.';
    }
    
    return $result;
  }
  
  // remove user adjustment and log it (used by user.php to undo the user changes)
  function del_usr_cfg($debug) {
    $result = '';

    if ($this->id > 0 AND $this->usr->id > 0) {
      zu_debug('word->del_usr_cfg  "'.$this->id.' und user '.$this->usr->name.'.', $debug-12);

      $db_type = 'user_word';
      $log = $this->log_del($debug-1);
      if ($log->id > 0) {
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  

    } else {
      zu_err("The word database ID and the user must be set to remove a user specific modification.", "word->del_usr_cfg", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // set the log entry parameter for a new value
  private function log_add($debug) {
    zu_debug('word->log_add "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'words';
    $log->field     = 'word_name';
    $log->old_value = '';
    $log->new_value = $this->name;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one word field
  private function log_upd($debug) {
    zu_debug('word->log_upd "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table     = 'words';
    } else {  
      $log->table     = 'user_words';
    }
    
    return $log;    
  }
  
  // set the log entry parameter to delete a word
  private function log_del($debug) {
    zu_debug('word->log_del "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = 'words';
    $log->field     = 'word_name';
    $log->old_value = $this->name;
    $log->new_value = '';
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the log entry parameters for a value update
  private function log_upd_view($view_id, $debug) {
    zu_debug('word->log_upd "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    if ($this->view_id > 0) {
      $dsp_old = new view_dsp;
      $dsp_old->id = $this->view_id;
      $dsp_old->usr = $this->usr;
      $dsp_old->load($debug-1);
    }
    $dsp_new = new view_dsp;
    $dsp_new->id = $view_id;
    $dsp_new->usr = $this->usr;
    $dsp_new->load($debug-1);
    
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    $log->table     = 'words';
    $log->field     = 'view_id';
    if ($this->view_id > 0) {
      $log->old_value = $dsp_old->name;
      $log->old_id    = $dsp_old->id;
    } else {
      $log->old_value = '';
      $log->old_id    = 0;
    }
    $log->new_value = $dsp_new->name;
    $log->new_id    = $dsp_new->id;
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // remember the word view, which means to save the view id for this word
  // each user can define set the view individually, so this is user specific
  function save_view($view_id, $debug) {
    if ($this->id > 0 AND $view_id > 0 AND $view_id <> $this->view_id) {
      zu_debug('word->save_view '.$view_id.' for "'.$this->name.'" and user '.$this->usr->id.'.', $debug-10);
      if ($this->log_upd_view($view_id, $debug-1) > 0 ) {
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        if ($this->can_change($debug-1)) {
          $db_con->type   = 'word';         
          $db_con->update($this->id, "view_id", $view_id, $debug-1);
        } else {
          if (!$this->has_usr_cfg($debug-1)) {
            $this->add_usr_cfg($debug-1);
          }
          $db_con->type   = 'user_word';         
          $db_con->update($this->id, "view_id", $view_id, $debug-1);
        }
      }
    }
  }
  
  // actually update a word field in the main database record or the user sandbox
  private function save_field_do($db_con, $log, $debug) {
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
        $db_con->type = 'user_word';
        if ($new_value == $std_value) {
          zu_debug('word->save_field_do remove user change', $debug-14);
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }  
        $result .= $this->del_usr_cfg_if_not_needed($debug-1);
      }
    }
    return $result;
  }
  
  // set the update parameters for the word plural
  private function save_field_plural($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->plural <> $this->plural) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->plural;
      $log->new_value = $this->plural;
      $log->std_value = $std_rec->plural;
      $log->row_id    = $this->id; 
      $log->field     = 'plural';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the word description
  private function save_field_description($db_con, $db_rec, $std_rec, $debug) {
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
  
  // set the update parameters for the word ref_url_1
  private function save_field_ref_url_1($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->ref_1 <> $this->ref_1) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->ref_1;
      $log->new_value = $this->ref_1;
      $log->std_value = $std_rec->ref_1;
      $log->row_id    = $this->id; 
      $log->field     = 'ref_url_1';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the word ref_url_2
  private function save_field_ref_url_2($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->ref_2 <> $this->ref_2) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->ref_2;
      $log->new_value = $this->ref_2;
      $log->std_value = $std_rec->ref_2;
      $log->row_id    = $this->id; 
      $log->field     = 'ref_url_2';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the word type
  // to do: log the ref
  private function save_field_type($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->type_id <> $this->type_id) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->type_name($debug-1);
      $log->old_id    = $db_rec->type_id;
      $log->new_value = $this->type_name($debug-1);
      $log->new_id    = $this->type_id; 
      $log->std_value = $std_rec->type_name($debug-1);
      $log->std_id    = $std_rec->type_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'word_type_id';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
      zu_debug('word->save_field_type changed type to "'.$log->new_value.'" ('.$log->new_id.').', $debug-12);
    }
    return $result;
  }
  
  // set the update parameters for the word view_id
  private function save_field_view($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->view_id <> $this->view_id) {
      $result .= $this->save_view($this->view_id, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the value excluded
  private function save_field_excluded($db_con, $db_rec, $std_rec, $debug) {
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
        $db_con->type = 'word';
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_word';
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
  
  // save all updated word fields
  private function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    $result .= $this->save_field_plural      ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_description ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_ref_url_1   ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_ref_url_2   ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_type        ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_view        ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded    ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('word->save_fields all fields for "'.$this->name.'" has been saved.', $debug-12);
    return $result;
  }
  
  // if the word is not really used, update the name
  // otherwise create a new word and request to delete the old word
  private function save_field_name($db_con, $db_rec, $debug) {
    $result = '';
    zu_debug('word->save_field_name change name from "'.$db_rec->name.'" to "'.$this->name.'"?', $debug-14);
    if ($db_rec->name <> $this->name) {
      if ($this->can_change($debug-1) AND $this->not_changed($debug-1)) {      
        zu_debug('word->save_field_name change name to "'.$this->name.'".', $debug-12);
        $log = $this->log_upd($debug-1);
        $log->old_value = $db_rec->name;
        $log->new_value = $this->name;
        $log->row_id    = $this->id; 
        $log->field     = 'word_name';
        $result .= $this->save_field_do($db_con, $log, $debug-1);
      } else {
        // create a new word 
        // and request the deletion confirms for the old from all changers
        // ???? or update the user word table 
      }
    }
    return $result;
  }
  
  // updated the view component name (which is the id field)
  // should only be called if the user is the owner and nobody has used the display component link
  private function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->name <> $this->name) {
      zu_debug('word->save_id_fields to "'.$this->dsp_id().'" from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().').', $debug-10);
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->name;
      $log->new_value = $this->name;
      $log->std_value = $std_rec->name;
      $log->row_id    = $this->id; 
      $log->field     = 'word_name';
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("word_name"),
                                              array($this->name), $debug-1);
      }
    }
    zu_debug('word->save_id_fields for "'.$this->name.'" has been done.', $debug-12);
    return $result;
  }
  
  // get the term corresponding to this word name
  // so in this case, if a formula or verb with the same name already exists, get it
  private function term($debug) {
    $trm = New term;
    $trm->name = $this->name;
    $trm->usr  = $this->usr;
    $trm->load($debug-1);
    return $trm;    
  }

  // check if the id parameters are supposed to be changed 
  private function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    
    if ($db_rec->name <> $this->name) {
      // check if target link already exists
      zu_debug('word->save_id_if_updated check if target link already exists "'.$this->dsp_id().'" (has been "'.$db_rec->dsp_id().'").', $debug-14);
      $db_chk = clone $this;
      $db_chk->id = 0; // to force the load by the id fields
      $db_chk->load_standard($debug-10);
      if ($db_chk->id > 0) {
        if (UI_CAN_CHANGE_VIEW_ENTRY_NAME) {
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
          zu_debug('word->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add "'.$this->dsp_id().'".', $debug-14);
        } else {
          $result .= 'A view component with the name "'.$this->name.'" already exists. Please use another name.';
        }  
      } else {
        if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
          // in this case change is allowed and done
          zu_debug('word->save_id_if_updated change the existing display component link "'.$this->dsp_id().'" (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'").', $debug-14);
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
          zu_debug('word->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add "'.$this->dsp_id().'" (standard "'.$std_rec->dsp_id().'").', $debug-14);
        }
      }
    }  

    zu_debug('word->save_id_if_updated for "'.$this->name.'" has been done.', $debug-12);
    return $result;
  }
  
  // create a new word
  private function add($db_con, $debug) {
    zu_debug('word->add the word "'.$this->name.'".', $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the new word
      $this->id = $db_con->insert(array("word_name","user_id"), array($this->name,$this->usr->id), $debug-1);
      if ($this->id > 0) {
        zu_debug('word->save word "'.$this->name.'" has been added as '.$this->id.'.', $debug-12);
        // update the id in the log
        $result .= $log->add_ref($this->id, $debug-1);

        // create an empty db_rec element to force saving of all set fields
        $db_rec = New word_dsp;
        $db_rec->name = $this->name;
        $db_rec->usr  = $this->usr;
        $std_rec = clone $db_rec;
        // save the word fields
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);

      } else {
        zu_err("Adding word ".$this->name." failed.", "word->save");
      }
    }  
    
    return $result;
  }
  
/*

 a word rename creates a new word and a word deletion request
 a word is deleted after all users have confirmed
 words with an active deletion request are listed at the end
 a word can have a formula linked
 values and formulas can be linked to a word, a triple or a word group
 verbs needs a confirmation for creation (but the name can be reserved)
 all other parameters beside the word/verb name can be user specific

 time words are seperated from the word groups to reduce the number of word groups
 for daily data or shorter a normal date or time field is used
 a time word can also describe a period
 
*/
  
  // add or update a word in the database (or create a user word if the program settings allow this)
  function save($debug) {
    zu_debug('word->save "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $result = '';
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'word';         
    
    // check if a new word is supposed to be added
    if ($this->id <= 0) {
      zu_debug('word->save add new word "'.$this->name.'".', $debug-12);
      // check if a word, formula or verb with the same name is already in the database
      // but not if the formula linked word is supposed to be created
      $trm_id = 0;
      if ($this->type_id <> cl(SQL_WORD_TYPE_FORMULA_LINK)) {
        $trm = $this->term($debug-1);  
        $trm_id = $trm->id;
      }  
      if ($trm_id > 0) {
        if ($trm->type <> 'word') {
          $result .= $trm->id_used_msg($debug-1);
        } else {
          $this->id = $trm->id;
          zu_debug('word->save adding word name "'.$this->name.'" is OK.', $debug-14);
        }  
      } else {      
        zu_debug('word->save no msg for "'.$this->name.'".', $debug-12);
      }  
    }  
      
    // create a new formula or update an existing
    if ($this->id <= 0) {
      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug('word->save update "'.$this->id.'".', $debug-12);
      // read the database values to be able to check if something has been changed; done first, 
      // because it needs to be done for user and general formulas
      $db_rec = New word_dsp;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-1);
      zu_debug('word->save -> database word "'.$db_rec->name.'" ('.$db_rec->id.') loaded.', $debug-14);
      $std_rec = New word_dsp;
      $std_rec->id = $this->id;
      $std_rec->load_standard($debug-1);
      zu_debug('word->save -> standard word settings for "'.$std_rec->name.'" ('.$std_rec->id.') loaded.', $debug-14);
      
      // for a correct user word detection (function can_change) set the owner even if the word has not been loaded before the save 
      if ($this->owner_id <= 0) {
        $this->owner_id = $std_rec->owner_id;
      }
      
      // if the name has changed, check if word, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
      if ($db_rec->name <> $this->name) {
        // check if a verb, formula or word with the same name is already in the database
        $trm_id = 0;
        if ($this->type_id <> cl(SQL_WORD_TYPE_FORMULA_LINK)) {
          $trm = $this->term($debug-1);  
          $trm_id = $trm->id;
        }  
        if ($trm_id > 0 AND $trm->type <> 'word') {
          $result .= $trm->id_used_msg($debug-1);
        }
      }  

      // check if the id parameters are supposed to be changed 
      if (str_replace ('1','',$result) == '') {
        $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec, $debug-1);
      }

      // if a problem has appeared up to here, don't try to save the values
      // the problem is shown to the user by the calling interactive script
      if (str_replace ('1','',$result) == '') {
        $result .= $this->save_fields     ($db_con, $db_rec, $std_rec, $debug-1);        
      }
    }

    return $result;    
  }

  // delete the complete word (the calling function del must have checked that no one uses this word)
  private function del_exe($debug) {
    zu_debug('word->del_exe.', $debug-16);
    $result = '';

    $log = $this->log_del($debug-1);
    if ($log->id > 0) {
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      // delete first all user configuration that have also been excluded
      $db_con->type = 'user_word';
      $result .= $db_con->delete(array('word_id','excluded'), array($this->id,'1'), $debug-1);
      $db_con->type   = 'word';         
      zu_debug('word->del do delete "'.$this->name.'".', $debug-14);
      $result .= $db_con->delete('word_id', $this->id, $debug-1);
    }
    
    return $result;    
  }
  
  // exclude or delete a word
  function del($debug) {
    zu_debug('word->del.', $debug-16);
    $result = '';
    $result .= $this->load($debug-1);
    if ($this->id > 0 AND $result == '') {
      zu_debug('word->del "'.$this->name.'".', $debug-14);
      if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
        zu_debug('word->del can delete "'.$this->name.'".', $debug-14);
        $result .= $this->del_exe($debug-1);
      } else {
        $this->excluded = 1;
        $result .= $this->save($debug-1);        
      }
    }
    return $result;    
  }
  
}

?>
