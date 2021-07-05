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

class verb_list
{

    public $lst = array(); // array of the loaded word objects
    public $usr = NULL;    // the user object of the person for whom the word list is loaded, so to say the viewer

    // search and load fields
    public $wrd = NULL;    // to load a list related to this word
    public $direction = '';      // "up" or "down" to select the parents or children

    // load the word parameters from the database for a list of words
    function load()
    {

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
            $sql_where = " s.from_phrase_id = " . $this->wrd->id;
            if ($this->direction == "up") {
                $sql_where = " s.to_phrase_id = " . $this->wrd->id;
            }
            $db_con->set_type(DB_TYPE_WORD_LINK);
            $db_con->set_usr($this->usr->id);
            $db_con->set_usr_num_fields(array('excluded'));
            $db_con->set_join_fields(array('code_id', 'verb_name', 'name_plural', 'name_reverse', 'name_plural_reverse', 'formula_name', 'description'), DB_TYPE_VERB);
            $db_con->set_fields(array('verb_id'));
            $db_con->set_where_text('s.to_phrase_id = 2');
            $sql = $db_con->select();
            $db_vrb_lst = $db_con->get($sql);
            $this->lst = array(); // rebuild also the id list (actually only needed if loaded via word group id)
            if ($db_vrb_lst != null) {
                $vrb_is_lst = array(); // tmp solution to prevent double entry until query has nice distinct
                foreach ($db_vrb_lst as $db_vrb) {
                    if (!in_array($db_vrb['verb_id'], $vrb_is_lst)) {
                        $vrb = new verb;
                        $vrb->row_mapper($db_vrb);
                        $vrb->usr = $this->usr->id;
                        $this->lst[] = $vrb;
                        $vrb_is_lst[] = $vrb->id;
                        log_debug('verb_list->load added (' . $vrb->name . ')');
                    }
                }
            }
            log_debug('verb_list->load (' . ".$sql_where." . ')');
        }
    }

    // calculates how many times a word is used, because this can be helpful for sorting
    function calc_usage()
    {
        log_debug('verb_list->calc_usage');

        global $db_con;

        $sql = "UPDATE verbs v
               SET words = ( 
            SELECT COUNT(to_phrase_id) 
              FROM word_links l
             WHERE v.verb_id = l.verb_id);";
        $db_con->usr_id = $this->usr->id;
        return $db_con->exe($sql);
    }

    /*
      display functions
      -----------------
    */

    // return a list of the verb ids as an sql compatible text
    function ids_txt(): string
    {
        $ids = array();
        foreach ($this->lst as $vrb) {
            if ($vrb->id > 0) {
                $ids[] = $vrb->id;
            }
        }
        return implode(',', $ids);
    }

    // display all verbs and allow an admin to change it
    function dsp_list(): string
    {
        return dsp_list($this->lst, "link_type");
    }


}

?>
