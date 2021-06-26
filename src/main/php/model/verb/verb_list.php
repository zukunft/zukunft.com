<?php

/*

  verb_list.php - al list of verb objects
  -------------
  
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

class verb_list {

  public $lst        = array(); // array of the loaded word objects 
  public $usr        = NULL;    // the user object of the person for whom the word list is loaded, so to say the viewer

  // search and load fields
  public $wrd        = NULL;    // to load a list related to this word
  public $direction  = '';      // "up" or "down" to select the parents or children
  
  // load the word parameters from the database for a list of words
  function load() {

    global $db_con;

    // check the all minimal input parameters
    if (!isset($this->usr)) {
      log_err("The user id must be set to load a list of verbs.", "verb_list->load");
    /*  
    } elseif (!isset($this->wrd) OR $this->direction == '')  {  
      zu_err("The word id, the direction and the user (".$this->usr->name.") must be set to load a list of verbs.", "verb_list->load");
    */  
    } else {

      // set the where clause depending on the values given
      $sql_where = '';
      if ($this->direction == "up") {
        $sql_where = " AND l.to_phrase_id = ".$this->wrd->id;
      } else {  
        $sql_where = " AND l.from_phrase_id = ".$this->wrd->id;
      }
      $sql = "SELECT v.verb_id,
                     v.code_id,
                     v.verb_name,
                     v.name_plural,
                     v.name_reverse,
                     v.name_plural_reverse,
                     v.formula_name,
                     v.description
                FROM word_links l, verbs v  
               WHERE l.verb_id = v.verb_id 
                     ".$sql_where." 
            GROUP BY v.verb_id 
            ORDER BY v.verb_id;";
      //$db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_vrb_lst = $db_con->get($sql);  
      $this->lst = array(); // rebuild also the id list (actually only needed if loaded via word group id)
      foreach ($db_vrb_lst AS $db_vrb) {
        $vrb = New verb;
        $vrb->id          = $db_vrb['verb_id'];
        $vrb->usr      = $this->usr->id;
        $vrb->code_id     = $db_vrb['code_id'];
        $vrb->name        = $db_vrb['verb_name'];
        $vrb->plural      = $db_vrb['name_plural'];
        $vrb->reverse     = $db_vrb['name_reverse'];
        $vrb->rev_plural  = $db_vrb['name_plural_reverse'];
        $vrb->frm_name    = $db_vrb['formula_name'];
        $vrb->description = $db_vrb['description'];
        $this->lst[]      = $vrb;
        log_debug('verb_list->load added ('.$vrb->name.')');
      }
      log_debug('verb_list->load ('.count(".$sql_where."
                 ).')');
    }  
  }
        
  // calculates how many times a word is used, because this can be helpful for sorting
  function calc_usage () {
    log_debug('verb_list->calc_usage');

    global $db_con;

    $sql = "UPDATE verbs l
               SET `words` = ( 
            SELECT COUNT(to_word_id) 
              FROM word_links t
             WHERE l.verb_id = t.verb_id);";
    //$db_con = New mysql;
    $db_con->usr_id = $this->usr->id;
    //$result = $db_con->exe($sql, "verb_list->calc_usage", array());
    $result = $db_con->exe($sql);

    return $result;           
  }
  
  /*
    display functions
    -----------------
  */

  // return a list of the verb ids as an sql compatible text
  function ids_txt() {
    $ids = array();
    foreach ($this->lst AS $vrb) {
      if ($vrb->id > 0) {
        $ids[] = $vrb->id;
      }
    }
    $result = implode(',',$ids);
    return $result; 
  }

  // display all verbs and allow an admin to change it
  function dsp_list () {
    log_debug('verb_list->dsp_list('.$this->usr.')');
    $result  = "";

    $result .= dsp_list($this->lst, "link_type");

    return $result;
  }

  
}

?>
