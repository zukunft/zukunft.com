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

namespace Zukunft\ZukunftCom\main\php\cfg\verb;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
//include_once paths::MODEL_PHRASE . 'term_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_db.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_db;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;

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
     * @param bool $usr_can_add true by default to allow searching by name for new added verbs
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
    function get_user(): ?user
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
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
            $db_con->set_usr($this->get_user()->id);
            $db_con->set_usr_num_fields(array(sql_db::FLD_EXCLUDED));
            $db_con->set_join_fields(array_merge(verb_db::FLD_NAMES, array(verb_db::FLD_NAME)), verb::class);
            $db_con->set_fields(array(verb_db::FLD_ID));
            // set the where clause depending on the values given
            // definition of up: if "Zurich" is a City, then "Zurich" is "from" and "City" is "to", so staring from "Zurich" and "up", the result should include "is a"
            $db_con->add_par(sql_par_type::INT, $phr->id());
            if ($direction == foaf_direction::UP) {
                $qp->sql = $db_con->select_by_field(triple_db::FLD_FROM);
            } else {
                $qp->sql = $db_con->select_by_field(triple_db::FLD_TO);
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
        if ($this->get_user() == null) {
            log_err("The user id must be set to load a list of verbs.", "verb_list->load");
            /*
            } elseif (!isset($this->wrd) OR $this->direction->value == '')  {
              zu_err("The word id, the direction and the user (".$this->get_user()->name.") must be set to load a list of verbs.", "verb_list->load");
            */
        } else {
            $qp = $this->load_by_linked_phrases_sql($db_con, $phr, $direction);
            if ($qp->name != '') {
                $vrb_lst = array(); // rebuild also the id list (actually only needed if loaded via word group id)
                $vrb_id_lst = array(); // tmp solution to prevent double entry utils query has nice distinct
                $db_vrb_lst = $db_con->get($qp);
                if ($db_vrb_lst != null) {
                    foreach ($db_vrb_lst as $db_vrb) {
                        if (!in_array($db_vrb[verb_db::FLD_ID], $vrb_id_lst)) {
                            $vrb = new verb;
                            $vrb->row_mapper_verb($db_vrb);
                            $vrb->set_user($this->usr);
                            $vrb_lst[] = $vrb;
                            $vrb_id_lst[] = $vrb->id;
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
     * TODO use always the verb_api name const
     */
    function load_dummy(): void
    {
        $vrb = new verb();
        $vrb->id = verbs::NOT_SET_ID;
        $vrb->set_name(verbs::NOT_SET_NAME);
        $vrb->set_code_id_db(verbs::NOT_SET);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::IS_ID;
        $vrb->set_name(verbs::IS_NAME);
        $vrb->set_code_id_db(verbs::IS);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::PART_ID;
        $vrb->set_name(verbs::PART_NAME);
        $vrb->set_code_id_db(verbs::PART);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::CAN_BE_PART_OF_ID;
        $vrb->set_name(verbs::CAN_BE_PART_OF_NAME);
        $vrb->set_code_id_db(verbs::CAN_BE_PART_OF);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::OF_ID;
        $vrb->set_name(verbs::OF_NAME);
        $vrb->set_code_id_db(verbs::OF);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::WITH_ID;
        $vrb->set_name(verbs::WITH_NAME);
        $vrb->set_code_id_db(verbs::WITH_NAME);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::HAS_ID;
        $vrb->set_name(verbs::HAS_NAME);
        $vrb->set_code_id_db(verbs::HAS);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::TIME_STEP_ID;
        $vrb->set_name(verbs::TIME_STEP_NAME);
        $vrb->frm_name = verbs::TIME_STEP_NAME_FORMULA;
        $vrb->set_code_id_db(verbs::TIME_STEP);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::TERM_STEP_ID;
        $vrb->set_name(verbs::TERM_STEP_NAME);
        $vrb->set_code_id_db(verbs::TERM_STEP);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::TERM_NEED_STEP_ID;
        $vrb->set_name(verbs::TERM_NEED_STEP_NAME);
        $vrb->set_code_id_db(verbs::TERM_NEED_STEP);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::FOLLOW_ID;
        $vrb->set_name(verbs::FOLLOW_NAME);
        $vrb->set_code_id_db(verbs::FOLLOW);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::USES_ID;
        $vrb->set_name(verbs::USES_NAME);
        $vrb->set_code_id_db(verbs::USES);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::ISSUE_ID;
        $vrb->set_name(verbs::ISSUE_NAME);
        $vrb->set_code_id_db(verbs::ISSUE);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::MEASURE_ID;
        $vrb->set_name(verbs::MEASURE_NAME);
        $vrb->set_code_id_db(verbs::MEASURE);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::ACRONYM_ID;
        $vrb->set_name(verbs::ACRONYM_NAME);
        $vrb->set_code_id_db(verbs::ACRONYM);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::CAN_CONTAIN_ID;
        $vrb->set_name(verbs::CAN_CONTAIN_NAME);
        $vrb->set_code_id_db(verbs::CAN_CONTAIN);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::INFLUENCE_ID;
        $vrb->set_name(verbs::INFLUENCE_NAME);
        $vrb->set_code_id_db(verbs::INFLUENCE);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::ALIAS_ID;
        $vrb->set_name(verbs::ALIAS_NAME);
        $vrb->set_code_id_db(verbs::ALIAS);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::CAN_ID;
        $vrb->set_name(verbs::CAN_NAME);
        $vrb->set_code_id_db(verbs::CAN);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::CAN_BE_ID;
        $vrb->set_name(verbs::CAN_BE_NAME);
        $vrb->set_code_id_db(verbs::CAN_BE);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::CAN_GET_ID;
        $vrb->set_name(verbs::CAN_GET_NAME);
        $vrb->set_code_id_db(verbs::CAN_GET);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::CAN_CAUSE_ID;
        $vrb->set_name(verbs::CAN_CAUSE_NAME);
        $vrb->set_code_id_db(verbs::CAN_CAUSE);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::CAN_HAVE_ID;
        $vrb->set_name(verbs::CAN_HAVE_NAME);
        $vrb->set_code_id_db(verbs::CAN_HAVE);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::CAN_USE_ID;
        $vrb->set_name(verbs::CAN_USE_NAME);
        $vrb->set_code_id_db(verbs::CAN_USE);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::SCALED_ID;
        $vrb->set_name(verbs::SCALED_NAME);
        $vrb->set_code_id_db(verbs::SCALED);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::PER_ID;
        $vrb->set_name(verbs::PER_NAME);
        $vrb->set_code_id_db(verbs::PER);
        $this->add_verb($vrb);
        $vrb->id = verbs::TIMES_ID;
        $vrb->set_name(verbs::TIMES_NAME);
        $vrb->set_code_id_db(verbs::TIMES);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::SELECTOR_ID;
        $vrb->set_name(verbs::SELECTOR_NAME);
        $vrb->set_code_id_db(verbs::SELECTOR);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::SYMBOL_ID;
        $vrb->set_name(verbs::SYMBOL_NAME);
        $vrb->set_code_id_db(verbs::SYMBOL);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::AND_ID;
        $vrb->set_name(verbs::AND_NAME);
        $vrb->set_code_id_db(verbs::AND);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::ON_ID;
        $vrb->set_name(verbs::ON_NAME);
        $vrb->set_code_id_db(verbs::ON);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::IN_ID;
        $vrb->set_name(verbs::IN_NAME);
        $vrb->set_code_id_db(verbs::IN);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::TO_ID;
        $vrb->set_name(verbs::TO_NAME);
        $vrb->set_code_id_db(verbs::TO);
        $this->add_verb($vrb);
        $vrb = new verb();
        $vrb->id = verbs::RANK_ID;
        $vrb->set_name(verbs::RANK_NAME);
        $vrb->set_code_id_db(verbs::RANK);
        $this->add_verb($vrb);
    }


    /*
     * cast
     */

    function term_list(user $usr): term_list
    {
        $trm_lst = new term_list($usr);
        foreach ($this->lst() as $vrb) {
            $trm_lst->add($vrb->term());
        }
        return $trm_lst;
    }

    /*
     * info
     */

    /**
     * convert this verb list object into a term list object
     * and use the name as the unique key instead of the database id
     * used for the data_object based import
     * @return term_list with all verbs of this list as a term
     */
    function term_lst_of_names(user $usr): term_list
    {
        $trm_lst = new term_list($usr);
        foreach ($this->lst() as $vrb) {
            if ($vrb::class != verb::class) {
                log_err('unexpected class ' . $vrb::class . ' in verb list');
            } else {
                $trm_lst->add_by_key($vrb->term());
            }
        }
        return $trm_lst;
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
        //$type_obj = new type_object($vrb->code_id, $vrb->name(), '', $vrb->id);
        $this->add($vrb);
    }

    /**
     * add a verb to the list that does not yet have an id but has a name
     * @param verb $to_add the named verb that should be added
     * @returns bool true if the object has been added
     */
    function add_by_name(verb $to_add): bool
    {
        $result = false;
        if (!in_array($to_add->name(), array_keys($this->names()))) {
            $this->add_direct($to_add);
            $result = true;
        }
        return $result;
    }

    /**
     * calculates how many times a word is used, because this can be helpful for sorting
     * @returns string error message for the user or an empty string
     */
    function calc_usage(): string
    {
        log_debug('verb_list->calc_usage');

        global $sys;
        global $db_con;

        $sql = "UPDATE verbs v
                   SET words = ( SELECT COUNT(to_phrase_id) 
                                   FROM triples l
                                  WHERE v.verb_id = l.verb_id)
                 WHERE verb_id > 0;";
        $db_con->usr_id = $this->get_user()->id;
        $sys->times->switch(system_time_type::DB_WRITE);
        $result = $db_con->exe_try('Calculation of the verb usage', $sql);
        $sys->times->switch();
        return $result;
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
     * @param string $code_id the code id of the requested verb
     * @return verb|null the verb object or null if no match is found
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
                    if ($vrb->reverse() != '' and $select_name != '') {
                        $select_name .= ' (' . $vrb->reverse() . ')';
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
     * save
     */

    /**
     * simple loop to save all verbs of the list
     * because there are hopefully never many verbs to save
     *
     * @param user_message $usr_msg in case of an issue the problem description what has failed and a suggested solution
     * @return bool true if everything has been fine
     */
    function save(user_message $usr_msg): bool
    {
        if ($this->is_empty()) {
            $usr_msg->add_info_text('no verbs to save');
        } else {
            foreach ($this->lst() as $vrb) {
                // for each item of a list an empty user_message statement should be used
                // so that an issue in one item does not prevent other item from being saved
                $vrb_usr_msg = $usr_msg->clone_reset();
                // actual save the reference to the database
                $vrb->save($vrb_usr_msg);
                // collect the user message for a consolidated list for the user
                $usr_msg->merge($vrb_usr_msg);
            }
        }

        return $usr_msg->is_ok();
    }

}