<?php

/*

    model/verb/verb_list.php - al list of verb objects
    ------------------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once SHARED_PATH . 'library.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';

use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use html\html_base;
use shared\library;

global $verbs;

class verb_list extends type_list
{

    private ?user $usr = null; // the user object of the person for whom the verb list is loaded, so to say the viewer

    // search and load fields
    public ?word $wrd = null;  // to load a list related to this word
    public ?array $ids = array(); // list of the verb ids to load a list from the database


    /*
     * construct and map
     */

    /**
     * define the settings for this verb list object
     * @param user|null $usr the user who requested to see the verb list
     * @param bool $usr_can_add true by default to allow seariching by name for new added verbs
     */
    function __construct(?user $usr = null, bool $usr_can_add = true)
    {
        parent::__construct($usr_can_add);
        $this->set_user($usr);
    }


    /*
     * set and get
     */

    /**
     * set the user of the verb list
     *
     * @param user|null $usr the person who wants to access the verbs
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user|null the person who wants to see the verbs
     */
    function user(): ?user
    {
        return $this->usr;
    }


    /*
     * loading
     */

    /**
     * create the SQL to load a list of verbs by the object vars which means uds or wrb and direction
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param phrase $phr the phrase used as a base for selecting the verb list e.g. Zurich
     * @param foaf_direction $direction the direction towards the verbs should be selected e.g. for Zurich and UP the verb "is" should be in the list
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_linked_phrases_sql(sql_db $db_con, phrase $phr, foaf_direction $direction): sql_par
    {
        $qp = new sql_par(self::class);
        if ($phr->id() != 0) {
            $qp->name .= 'phr_id';
            if ($direction == foaf_direction::UP) {
                $qp->name .= '_up';
            } else {
                $qp->name .= '_down';
            }
        } else {
            log_err('The phrase id must be set to load a verb list');
            $qp->name = '';
        }

        if ($qp->name != '') {
            $db_con->set_class(triple::class);
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_usr_num_fields(array(sandbox::FLD_EXCLUDED));
            $db_con->set_join_fields(array_merge(verb::FLD_NAMES, array(verb::FLD_NAME)), verb::class);
            $db_con->set_fields(array(verb::FLD_ID));
            // set the where clause depending on the values given
            // definition of up: if "Zurich" is a City, then "Zurich" is "from" and "City" is "to", so staring from "Zurich" and "up", the result should include "is a"
            $db_con->add_par(sql_par_type::INT, $phr->id());
            if ($direction == foaf_direction::UP) {
                $qp->sql = $db_con->select_by_field(triple::FLD_FROM);
            } else {
                $qp->sql = $db_con->select_by_field(triple::FLD_TO);
            }
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * load a list of verbs that are used by a given word
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param phrase $phr the phrase used as a base for selecting the verb list e.g. Zurich
     * @param foaf_direction $direction the direction towards the verbs should be selected e.g. for Zurich and UP the verb "is" should be in the list
     * @return bool true if at least one verb is found
     */
    function load_by_linked_phrases(sql_db $db_con, phrase $phr, foaf_direction $direction): bool
    {

        $result = false;
        // check the all minimal input parameters
        if ($this->user() == null) {
            log_err("The user id must be set to load a list of verbs.", "verb_list->load");
            /*
            } elseif (!isset($this->wrd) OR $this->direction->value == '')  {
              zu_err("The word id, the direction and the user (".$this->user()->name.") must be set to load a list of verbs.", "verb_list->load");
            */
        } else {
            $qp = $this->load_by_linked_phrases_sql($db_con, $phr, $direction);
            if ($qp->name != '') {
                $vrb_lst = array(); // rebuild also the id list (actually only needed if loaded via word group id)
                $vrb_id_lst = array(); // tmp solution to prevent double entry utils query has nice distinct
                $db_vrb_lst = $db_con->get($qp);
                if ($db_vrb_lst != null) {
                    foreach ($db_vrb_lst as $db_vrb) {
                        if (!in_array($db_vrb[verb::FLD_ID], $vrb_id_lst)) {
                            $vrb = new verb;
                            $vrb->row_mapper_verb($db_vrb);
                            $vrb->set_user($this->usr);
                            $vrb_lst[] = $vrb;
                            $vrb_id_lst[] = $vrb->id();
                            log_debug('verb_list->load added (' . $vrb->name() . ')');
                        }
                    }
                }
                $this->set_lst($vrb_lst);
                if ($this->count() > 0) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * force to reload the complete list of verbs from the database
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the database name e.g. the table name without s
     * @return array the list of types
     */
    protected function load_list(sql_db $db_con, string $class): array
    {
        $this->reset();
        $qp = $this->load_sql_all($db_con->sql_creator(), $class);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $vrb = new verb();
                $vrb->row_mapper_verb($db_row);
                $this->add_verb($vrb);
            }
        }
        return $this->lst();
    }

    /**
     * adding the verbs used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        $vrb = new verb();
        $vrb->set_id(1);
        $vrb->set_name(verb::NOT_SET);
        $vrb->code_id = verb::NOT_SET;
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->set_id(2);
        $vrb->set_name(verb::IS);
        $vrb->code_id = verb::IS;
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->set_id(3);
        $vrb->set_name(verb::IS_PART_OF);
        $vrb->code_id = verb::IS_PART_OF;
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->set_id(4);
        $vrb->set_name(verb::IS_WITH);
        $vrb->code_id = verb::IS_WITH;
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->set_id(9);
        $vrb->set_name(verb::FOLLOW);
        $vrb->code_id = verb::FOLLOW;
        $this->add_verb($vrb);
    }


    /*
     * modify
     */

    /**
     * add a verb to the list
     * @param verb $vrb
     */
    function add_verb(verb $vrb): void
    {
        //$type_obj = new type_object($vrb->code_id, $vrb->name(), '', $vrb->id());
        $this->add($vrb);
    }

    /**
     * calculates how many times a word is used, because this can be helpful for sorting
     * @returns string error message for the user or an empty string
     */
    function calc_usage(): string
    {
        log_debug('verb_list->calc_usage');

        global $db_con;

        $sql = "UPDATE verbs v
                   SET words = ( SELECT COUNT(to_phrase_id) 
                                   FROM triples l
                                  WHERE v.verb_id = l.verb_id)
                 WHERE verb_id > 0;";
        $db_con->usr_id = $this->user()->id();
        return $db_con->exe_try('Calculation of the verb usage', $sql);
    }

    /*
     * extract
     */

    /**
     * @returns array with the names on the db keys
     */
    function db_id_list(): array
    {
        $result = array();
        foreach ($this->lst() as $obj) {
            $result[$obj->id()] = $obj->name();
        }
        return $result;
    }

    /**
     * @return array the list of the verb ids
     */
    function ids(): array
    {
        $result = array();
        if (!$this->is_empty()) {
            foreach ($this->lst() as $vrb) {
                if ($vrb->id() > 0) {
                    $result[] = $vrb->id();
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

    /**
     * get a single verb from this list selected by the code id
     * a kind of replacement for the user_type_list->get() function but for the verb object
     *
     * @param string $code_id
     * @return verb the verb object or null if no match is found
     */
    function get_verb(string $code_id): ?verb
    {
        $result = null;
        $id = $this->id($code_id);
        if ($id > 0) {
            $result = $this->get_verb_by_id($id);
        } else {
            log_debug('Verb id not set while try to get "' . $code_id . '"');
        }

        return $result;
    }

    /**
     * get a single verb from this list
     * a kind of replacement for the user_type_list->get_by_id() function but for the verb object
     *
     * @param int $id
     * @return verb|null the verb object or null if no match is found
     */
    function get_verb_by_id(int $id): ?verb
    {
        $result = null;
        if ($id > 0) {
            $vrb_lst = $this->lst();
            if (array_key_exists($id, $vrb_lst)) {
                $result = $vrb_lst[$id];
            } else {
                log_err('Verb with id ' . $id . ' not found in ' . $this->dsp_id());
            }
        } else {
            log_debug('Verb id not set while try to get "' . $id . '"');
        }

        return $result;
    }


    /*
      GUI interface
      -------------
    */

    function selector_list(): array
    {
        $result = array();
        if (!$this->is_empty()) {

            // create a list with the forward and backward version of the verb
            $combined_list = array();
            foreach ($this->lst() as $vrb) {
                if ($vrb->id() > 0) {
                    $select_row = array();
                    $select_name = $vrb->name();
                    /* has been an idea, but has actually caused more confusion
                    if ($vrb->reverse != '' and $select_name != '') {
                        $select_name .= ' (' . $vrb->reverse . ')';
                    }
                    */
                    $id = $vrb->id();
                    $select_row[] = $id;
                    $select_row[] = $select_name;
                    $select_row[] = $vrb->usage;
                    $combined_list[$id] = $select_row;

                    $select_row = array();
                    $select_name = $vrb->reverse;
                    /* like above ...
                    if ($vrb->name() != '' and $select_name != '') {
                        $select_name .= ' (' . $vrb->name() . ')';
                    }
                    */
                    if (trim($select_name) != '') {
                        $id = $vrb->id() * -1;
                        $select_row[] = $id;
                        $select_row[] = $select_name;
                        $select_row[] = $vrb->usage; // TODO separate the backward usage or separate the reverse form
                        $combined_list[$id] = $select_row;
                    }
                }
            }

            // put the three most used on the top
            $n = 3;
            $use_sorted = array();
            $use_sorted_id = array();
            $most_used_id = array();
            foreach ($combined_list as $row) {
                $use_sorted[] = $row[2];
                $use_sorted_id[$row[0]] = $row[2];
            }
            rsort($use_sorted);
            $most_used = array_slice($use_sorted, 0, $n);
            foreach ($most_used as $top_usage) {
                $id = array_search($top_usage, $use_sorted_id);
                $result[] = $combined_list[$id];
                unset($use_sorted_id[$id]);
                $most_used_id[] = $id;
            }

            // add the others sorted by select name
            $name_sorted = array();
            $name_sorted_id = array();
            foreach ($combined_list as $row) {
                $name_sorted[$row[0]] = $row[1];
                $name_sorted_id[$row[0]] = $row[1];
            }
            sort($name_sorted);
            foreach ($name_sorted as $next_name) {
                $id = array_search($next_name, $name_sorted_id);
                if (!in_array($id, $most_used_id)) {
                    $result[] = $combined_list[$id];
                }
            }

        }
        return $result;
    }


    /*
      display functions
      -----------------
    */

    /**
     * @return string list of the verb ids as a sql compatible text
     */
    function ids_txt(): string
    {
        $lib = new library();
        return $lib->sql_array($this->ids());
    }

    /**
     * @return string html code to display all verbs and allow an admin to change it
     */
    function dsp_list(): string
    {
        $html = new html_base();
        return $html->dsp_list($this->lst(), "link_type");
    }

}