<?php

/*

    model/phrase/term_list.php - a list of word, triple, verb or formula objects
    --------------------------


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg;

include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once WEB_PHRASE_PATH . 'term_list.php';
include_once API_PHRASE_PATH . 'term_list.php';

use api\term_list_api;
use html\phrase\term_list as term_list_dsp;

;

class term_list extends sandbox_list_named
{

    // array $lst is the array of the loaded term objects
    // (key is at the moment the database id, but it looks like this has no advantages,
    // so a normal 0 to n order could have more advantages)


    /*
     * cast
     */

    /**
     * @return term_list_api the word list object with the display interface functions
     */
    function api_obj(): term_list_api
    {
        $api_obj = new term_list_api();
        foreach ($this->lst as $trm) {
            $api_obj->add($trm->api_obj());
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return term_list_dsp the word object with the display interface functions
     */
    function dsp_obj(): term_list_dsp
    {
        $dsp_obj = new term_list_dsp();
        foreach ($this->lst as $trm) {
            $dsp_obj->add($trm->dsp_obj());
        }
        return $dsp_obj;
    }

    /**
     * get the phrases out of a term list
     * @return phrase_list the list of phrases picked from the term list
     */
    function phrase_list(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        foreach ($this->lst() as $trm) {
            if ($trm->is_word() or $trm->is_triple()) {
                $phr_lst->add($trm->phrase());
            }
        }
        return $phr_lst;
    }

    /*
     * load function
     */

    /**
     * create the common part of an SQL statement to retrieve a list of terms from the database
     * uses the term view which includes only the main fields
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $query_name the name of the query use to prepare and call the query
     */
    private function load_sql(sql_db $db_con, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::VT_TERM);
        $db_con->set_name($qp->name);

        $db_con->set_fields(term::FLD_NAMES);
        $db_con->set_usr_fields(term::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(term::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of terms from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql_db $db_con, trm_ids $ids): sql_par
    {
        $qp = $this->load_sql($db_con, 'ids');
        $db_con->add_par_in_int($ids->lst);
        $db_con->set_order(term::FLD_ID, sql_db::ORDER_ASC);
        $qp->sql = $db_con->select_by_field(term::FLD_ID);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of terms from the database
     * uses the erm view which includes only the main fields
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_like(sql_db $db_con, string $pattern = ''): sql_par
    {
        $qp = $this->load_sql($db_con, 'name_like');
        $db_con->add_name_pattern($pattern);
        $qp->sql = $db_con->select_by_field(term::FLD_NAME);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the terms that based on the given query parameters
     * @param sql_par $qp the query parameters created by the calling function
     * @return bool true if at least one term has been loaded
     */
    protected function load(sql_par $qp): bool
    {
        global $db_con;
        $result = false;

        $trm_lst = $db_con->get($qp);
        foreach ($trm_lst as $db_row) {
            $trm = new term($this->user());
            $trm->set_obj_from_id($db_row[term::FLD_ID]);
            $trm->row_mapper_obj($db_row, $trm->obj()::class, term::FLD_ID, term::FLD_NAME, term::FLD_TYPE);
            if ($trm->id() != 0) {
                $this->add($trm);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load the terms selected by the id
     *
     * @param trm_ids $ids of term ids that should be loaded
     * @return bool true if at least one term has been loaded
     */
    function load_by_ids(trm_ids $ids): bool
    {
        global $db_con;

        $qp = $this->load_sql_by_ids($db_con, $ids);
        return $this->load($qp);
    }

    /**
     * load the terms that matches the given pattern
     * @param string $pattern part of the name that should be used to select the terms
     */
    function load_like(string $pattern): bool
    {
        global $db_con;

        $qp = $this->load_sql_like($db_con, $pattern);
        return $this->load($qp);
    }


    /*
     * search
     */

    /**
     * cast the finding by name for terms
     *
     * @param string $name the term name that should be returned
     * @return term|null the found term or null if no name is found
     */
    function get_by_name(string $name): ?term
    {
        return parent::get_obj_by_name($name);
    }


    /*
     * modification
     */

    /**
     * add one term to the term list, but only if it is not yet part of the term list
     * @param term|null $trm_to_add the term backend object that should be added
     * @returns bool true the term has been added
     */
    function add(?term $trm_to_add): bool
    {
        return parent::add_obj($trm_to_add);
    }

    /*
     * get function
     */

    /**
     * @returns array the phrase ids as an array
     * switch to ids() if possible
     */
    function id_lst(): array
    {
        return $this->term_ids()->lst;
    }

    /**
     * return a list of the term list ids as sql compatible text
     */
    function ids_txt(): string
    {
        $lib = new library();
        return $lib->dsp_array($this->id_lst());
    }

    /**
     * @return trm_ids with the sorted term ids where a triple has a negative id
     */
    function term_ids(): trm_ids
    {
        $lst = array();
        if (count($this->lst) > 0) {
            foreach ($this->lst as $trm) {
                // use only valid ids
                if ($trm->id_obj() <> 0) {
                    $lst[] = $trm->id();
                }
            }
        }
        asort($lst);
        return (new trm_ids($lst));
    }

    /**
     * get a term from the term list selected by the word, triple, formula or verb id
     *
     * @param int $id the word, triple, formula or verb id (not the term id!)
     * @param string $class the word, triple, formula or verb class name
     * @return term|null the word object from the list or null
     */
    function term_by_obj_id(int $id, string $class): ?term
    {
        $trm = new term($this->user());
        $trm->set_obj_from_class($class);
        $trm->set_obj_id($id);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get_by_id($trm_id);
        }
        return $trm;
    }

    /**
     * get a word from the term list selected by the word id
     *
     * @param int $id the word id (not the term id!)
     * @return word|null the word object from the list or null
     */
    function word_by_id(int $id): ?word
    {
        $wrd = null;
        $trm = new term($this->user());
        $trm->set_id_from_obj($id, word::class);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get_by_id($trm_id);
            if ($trm != null) {
                $wrd = $trm->get_word();
            }
        }
        return $wrd;
    }

    /**
     * get a triple from the term list selected by the triple id
     *
     * @param int $id the triple id (not the term id!)
     * @return triple|null the triple object from the list or null
     */
    function triple_by_id(int $id): ?triple
    {
        $trp = null;
        $trm = new term($this->user());
        $trm->set_id_from_obj($id, triple::class);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get_by_id($trm_id);
            if ($trm != null) {
                $trp = $trm->get_triple();
            }
        }
        return $trp;
    }

    /**
     * get a formula from the term list selected by the formula id
     *
     * @param int $id the formula id (not the term id!)
     * @return formula|null the formula object from the list or null
     */
    function formula_by_id(int $id): ?formula
    {
        $frm = null;
        $trm = new term($this->user());
        $trm->set_id_from_obj($id, formula::class);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get_by_id($trm_id);
            if ($trm != null) {
                $frm = $trm->get_formula();
            }
        }
        return $frm;
    }

    /**
     * get a verb from the term list selected by the verb id
     *
     * @param int $id the verb id (not the term id!)
     * @return verb|null the verb object from the list or null
     */
    function verb_by_id(int $id): ?verb
    {
        $vrb = null;
        $trm = new term($this->user());
        $trm->set_id_from_obj($id, verb::class);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get_by_id($trm_id);
            if ($trm != null) {
                $vrb = $trm->get_verb();
            }
        }
        return $vrb;
    }


    /*
     * display functions
     */

    /**
     * @return string with the best possible id for this element mainly used for debugging
     */
    function dsp_id(): string
    {
        $id = $this->ids_txt();
        if ($this->name() <> '""') {
            $result = $this->name() . ' (' . $id . ')';
        } else {
            $result = $id;
        }
        return $result;
    }

    /**
     * @return string with all names of the list
     * this function is called from dsp_id, so no call of another function is allowed
     */
    function dsp_name(): string
    {
        global $debug;
        $lib = new library();

        $name_lst = $this->names();
        if ($debug > 10) {
            $result = '"' . implode('","', $name_lst) . '"';
        } else {
            $result = '"' . implode('","', array_slice($name_lst, 0, 7));
            if (count($name_lst) > 8) {
                $result .= ' ... total ' . $lib->dsp_count($this->lst);
            }
            $result .= '"';
        }
        return $result;
    }

    /**
     * @return string with all names of the list
     */
    function name(): string
    {
        $name_lst = $this->names();
        return '"' . implode('","', $name_lst) . '"';
    }

    /**
     * @return array a sorted list of the term names
     * this function is called from dsp_id, so no call of another function is allowed
     * TODO move to a parent object for phrase list and term list
     */
    function names(): array
    {
        $name_lst = array();
        foreach ($this->lst as $trm) {
            if (isset($trm)) {
                $name_lst[] = $trm->name();
            }
        }
        // TODO allow to fix the order
        asort($name_lst);
        return $name_lst;
    }

}
