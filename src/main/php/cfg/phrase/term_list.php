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

namespace cfg\phrase;

use cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_list_named.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\formula\formula;
use cfg\sandbox\sandbox_list_named;
use cfg\word\triple;
use cfg\verb\verb;
use cfg\word\word;
use shared\library;

class term_list extends sandbox_list_named
{

    // array $lst is the array of the loaded term objects
    // (key is at the moment the database id, but it looks like this has no advantages,
    // so a normal 0 to n order could have more advantages)

    /*
     * construct and map
     */

    /**
     * fill the term list based on a database records
     * actually just set the term object for the parent function
     *
     * @param array|null $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded terms e.g. for admins
     * @return bool true if at least one term has been added
     */
    protected function rows_mapper(?array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new term($this->user()), $db_rows, $load_all);
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve a list of terms from the database
     * uses the term view which includes only the main fields
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(term::class);
        $sc->set_name($qp->name);

        $sc->set_fields(term::FLD_NAMES);
        $sc->set_usr_fields(term::FLD_NAMES_USR);
        $sc->set_usr_num_fields(term::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of terms from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param trm_ids $ids term ids that should be loaded
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        trm_ids     $ids,
        int         $limit = 0,
        int         $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(term::FLD_ID, $ids->lst);
        $sc->set_order(term::FLD_ID, sql::ORDER_ASC);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of terms from the database
     * uses the erm view which includes only the main fields
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_like(sql_creator $sc, string $pattern = ''): sql_par
    {
        $qp = $this->load_sql($sc, 'name_like');
        $sc->add_where(term::FLD_NAME, $pattern, sql_par_type::LIKE_R);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the terms that based on the given query parameters
     * @param sql_par $qp the query parameters created by the calling function
     * @param bool $load_all force to include also the excluded terms e.g. for admins
     * @return bool true if at least one term has been loaded
     */
    protected function load(sql_par $qp, bool $load_all = false): bool
    {
        global $db_con;
        $result = false;

        $trm_lst = $db_con->get($qp);
        foreach ($trm_lst as $db_row) {
            $trm = new term($this->user());
            $trm->row_mapper_sandbox($db_row);
            if ($trm->id() != 0) {
                $this->add($trm);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load a list of term names
     * @param string $pattern the pattern to filter the terms
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return bool true if at least one term found
     */
    function load_names(string $pattern = '', int $limit = 0, int $offset = 0): bool
    {
        return parent::load_sbx_names(new term($this->user()), $pattern, $limit, $offset);
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

        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $ids);
        return $this->load($qp);
    }

    /**
     * load the terms that matches the given pattern
     * @param string $pattern part of the name that should be used to select the terms
     */
    function load_like(string $pattern): bool
    {
        global $db_con;

        $qp = $this->load_sql_like($db_con->sql_creator(), $pattern);
        return $this->load($qp);
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
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $trm) {
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
     * get the terms of the list selected by the given ids
     * @param trm_ids $ids list of term ids to select the terms of this list
     * @return term_list the terms that have been found
     */
    function get_by_ids(trm_ids $ids): term_list
    {
        $trm_lst = new term_list($this->user());
        foreach ($ids->lst as $id) {
            $trm = $trm_lst->get_by_id($id);
            $trm_lst->add($trm);
        }
        return $trm_lst;
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
        $trm = new term(new word($this->user()));
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
        $trm = new term(new triple($this->user()));
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
        $trm = new term(new formula($this->user()));
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
        $trm = new term(new verb());
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
     * cast
     */

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
     * modify
     */

    /**
     * removes all terms from this list that are not in the given list
     * @param term_list $new_lst the terms that should remain in this list
     * @returns term_list with the terms of this list and the new list
     */
    function intersect(term_list $new_lst): term_list
    {
        if (!$new_lst->is_empty()) {
            if ($this->is_empty()) {
                $this->set_lst($new_lst->lst());
            } else {
                // next line would work if array_intersect could handle objects
                // $this->lst() = array_intersect($this->lst(), $new_lst->lst());
                $found_lst = new term_list($this->user());
                foreach ($new_lst->lst() as $trm) {
                    if (in_array($trm->id(), $this->id_lst())) {
                        $found_lst->add($trm);
                    }
                }
                $this->set_lst($found_lst->lst());
            }
        }
        return $this;
    }

    /**
     * remove all terms from the given list from this list
     * @param term_list $del_lst the terms that should be removed
     * @return term_list this
     */
    function remove(term_list $del_lst): term_list
    {
        if (!$del_lst->is_empty()) {
            // next line would work if array_intersect could handle objects
            // $this->lst() = array_intersect($this->lst(), $new_lst->lst());
            $remain_lst = new term_list($this->user());
            foreach ($this->lst() as $trm) {
                if (!in_array($trm->id(), $del_lst->id_lst())) {
                    $remain_lst->add($trm);
                }
            }
            $this->set_lst($remain_lst->lst());
        }
        return $this;
    }

    /**
     * add the terms of the given list to this list
     * but avoid duplicates by the name
     * merge as a function, because the array_merge does not create an object
     * @param term_list $lst_to_add with the terms to be added by the name
     * @return term_list with all terms of this list and the given list
     */
    function merge_by_name(term_list $lst_to_add): term_list
    {
        if (!$lst_to_add->is_empty()) {
            foreach ($lst_to_add->lst() as $trm_to_add) {
                $this->add_by_name($trm_to_add);
            }
        }
        return $this;
    }

    /**
     * leave only the valid words, triples, verbs and formulas in this list
     * @return void
     */
    function filter_valid(): void
    {
        $lst = [];
        foreach ($this->lst() as $trm) {
            if ($trm->is_valid()) {
                $lst[] = $trm;
            }
        }
        $this->set_lst($lst);
    }



    /*
     * display functions
     */

    /**
     * @param term_list|null $trm_lst a cached list of terms
     * @return string with the best possible id for this element mainly used for debugging
     */
    function dsp_id(?term_list $trm_lst = null): string
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
                $result .= ' ... total ' . $lib->dsp_count($this->lst());
            }
            $result .= '"';
        }
        return $result;
    }

    /**
     * @return string with all names of the list
     */
    function name(int $limit = null): string
    {
        $name_lst = $this->names();
        return '"' . implode('","', $name_lst) . '"';
    }

    /**
     * @return array a sorted list of the term names
     * this function is called from dsp_id, so no call of another function is allowed
     * TODO move to a parent object for phrase list and term list
     */
    function names(bool $ignore_excluded = false, int $limit = null): array
    {
        $name_lst = array();
        foreach ($this->lst() as $trm) {
            if (isset($trm)) {
                $name_lst[] = $trm->name();
            }
        }
        // TODO allow to fix the order
        asort($name_lst);
        return $name_lst;
    }

}
