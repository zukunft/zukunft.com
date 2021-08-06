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

global $verbs;

class verb_list extends user_type_list
{

    // search directions to get related words (phrases)
    const DIRECTION_NO = '';
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    public array $hash = []; // hash list with the code id for fast selection
    public ?user $usr = null;   // the user object of the person for whom the verb list is loaded, so to say the viewer

    // search and load fields
    public ?word $wrd = null;  // to load a list related to this word
    public ?array $ids = array(); // list of the verb ids to load a list from the database
    public ?string $direction = self::DIRECTION_NO; // "up" or "down" to select the parents or children

    /**
     * overwrite the user_type_list function to include the specific fields like the name_plural
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $db_type the database name e.g. the table name without s
     * @return array the list of reference types
     */
    private function load_list(sql_db $db_con, string $db_type): array
    {
        $this->lst = [];
        // set the where clause depending on the values given
        // definition of up: if "Zurich" is a City, then "Zurich" is "from" and "City" is "to", so staring from "Zurich" and "up", the result should include "is a"
        $sql_where = " s.to_phrase_id = " . $this->wrd->id;
        if ($this->direction == self::DIRECTION_UP) {
            $sql_where = " s.from_phrase_id = " . $this->wrd->id;
        }
        $db_con->set_type($db_type);
        $db_con->set_usr($this->usr->id);
        $db_con->set_usr_num_fields(array('excluded'));
        $db_con->set_join_fields(array(sql_db::FLD_CODE_ID, 'verb_name', 'name_plural', 'name_reverse', 'name_plural_reverse', 'formula_name', sql_db::FLD_DESCRIPTION), DB_TYPE_VERB);
        $db_con->set_fields(array('verb_id'));
        $db_con->set_where_text($sql_where);
        $sql = $db_con->select();
        $db_vrb_lst = $db_con->get($sql);
        $this->lst = array(); // rebuild also the id list (actually only needed if loaded via word group id)
        if ($db_vrb_lst != null) {
            $vrb_is_lst = array(); // tmp solution to prevent double entry until query has nice distinct
            foreach ($db_vrb_lst as $db_vrb) {
                if (!in_array($db_vrb['verb_id'], $vrb_is_lst)) {
                    $vrb = new verb;
                    $vrb->row_mapper($db_vrb);
                    $vrb->usr = $this->usr;
                    $this->lst[] = $vrb;
                    $vrb_is_lst[] = $vrb->id;
                    log_debug('verb_list->load added (' . $vrb->name . ')');
                }
            }
        }
        log_debug('verb_list->load (' . ".$sql_where." . ')');
        return $this->lst;
    }


    // load the word parameters from the database for a list of words
    function load(sql_db $db_con, string $db_type = DB_TYPE_WORD_LINK): bool
    {

        $result = false;
        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a list of verbs.", "verb_list->load");
            /*
            } elseif (!isset($this->wrd) OR $this->direction == '')  {
              zu_err("The word id, the direction and the user (".$this->usr->name.") must be set to load a list of verbs.", "verb_list->load");
            */
        } else {
            $this->lst = $this->load_list($db_con, $db_type);
            $this->type_hash = $this->get_hash($this->lst);
            if (count($this->type_hash) > 0) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * adding the verbs used for unit tests to the dummy list
     */
    function load_dummy() {
        parent::load_dummy();
        $type = new verb();
        $type->name = verb::DBL_IS;
        $type->code_id = verb::DBL_IS;
        $this->lst[2] = $type;
        $this->type_hash[verb::DBL_IS] = 2;
    }

    // calculates how many times a word is used, because this can be helpful for sorting
    function calc_usage()
    {
        log_debug('verb_list->calc_usage');

        global $db_con;

        $sql = "UPDATE verbs v
                   SET words = ( SELECT COUNT(to_phrase_id) 
                                   FROM word_links l
                                  WHERE v.verb_id = l.verb_id)
                 WHERE verb_id > 0;";
        $db_con->usr_id = $this->usr->id;
        return $db_con->exe($sql);
    }

    /*
      extract functions
      -----------------
    */

    // return the list of the verb ids
    function ids(): array
    {
        $result = array();
        if ($this->lst != null) {
            foreach ($this->lst as $vrb) {
                if ($vrb->id > 0) {
                    $result[] = $vrb->id;
                }
            }
        }
        // fallback solution if the load is not yet called e.g. for unit testing
        if (count($result) <= 0) {
            if (count($this->ids) > 0) {
                $result = $this->ids;
            }
        }
        return $result;
    }

    /*
      display functions
      -----------------
    */

    // return a list of the verb ids as a sql compatible text
    function ids_txt(): string
    {
        return sql_array($this->ids());
    }

    // display all verbs and allow an admin to change it
    function dsp_list(): string
    {
        return dsp_list($this->lst, "link_type");
    }


}