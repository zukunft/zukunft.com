<?php

/*

    term_list.php - a list of word, triple, verb or formula objects
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

use api\term_list_api;
use html\term_list_dsp;

class term_list
{

    // array of the loaded phrase objects
    // (key is at the moment the database id, but it looks like this has no advantages,
    // so a normal 0 to n order could have more advantages)
    public array $lst;
    public user $usr;  // the user object of the person for whom the phrase list is loaded, so to say the viewer

    /**
     * always set the user because a phrase list is always user specific
     * @param user $usr the user who requested to see this phrase list
     */
    function __construct(user $usr)
    {
        $this->lst = array();
        $this->usr = $usr;
    }

    /*
     * casting objects
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
    private function load(sql_par $qp): bool
    {
        global $db_con;
        $result = false;

        $trm_lst = $db_con->get($qp);
        foreach ($trm_lst as $db_row) {
            $trm = new term($this->usr);
            $trm->row_mapper($db_row);
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
     * modification function
     */

    /**
     * add one term to the term list, but only if it is not yet part of the term list
     * @returns bool true the term has been added
     */
    function add(?term $trm_to_add): bool
    {
        $result = false;
        // check parameters
        if ($trm_to_add->usr == null) {
            $trm_to_add->usr = $this->usr;
        }
        if ($trm_to_add != null) {
            log_debug($trm_to_add->dsp_id());
            if ($trm_to_add->id() <> 0 or $trm_to_add->name() != '') {
                if (count($this->id_lst()) > 0) {
                    if (!in_array($trm_to_add->id(), $this->id_lst())) {
                        $this->lst[] = $trm_to_add;
                        $result = true;
                    }
                } else {
                    $this->lst[] = $trm_to_add;
                    $result = true;
                }
            }
        }
        return $result;
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
        return $this->ids()->lst;
    }

    /**
     * return a list of the term list ids as sql compatible text
     */
    function ids_txt(): string
    {
        return dsp_array($this->id_lst());
    }

    /**
     * @return trm_ids with the sorted term ids where a triple has a negative id
     */
    function ids(): trm_ids
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
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }

        return $result;
    }

    /**
     * this function is called from dsp_id, so no call of another function is allowed
     * @return string with all names of the list
     */
    function name(): string
    {
        global $debug;
        $result = '';

        if ($debug > 10) {
            $result .= '"' . implode('","', $this->names()) . '"';
        } else {
            $result .= '"' . implode('","', array_slice($this->names(), 0, 7));
            if (count($this->names()) > 8) {
                $result .= ' ... total ' . dsp_count($this->lst);
            }
            $result .= '"';
        }
        return $result;
    }

    /**
     * this function is called from dsp_id, so no call of another function is allowed
     * @return array a list of the word names
     */
    function names(): array
    {
        $result = array();
        foreach ($this->lst as $trm) {
            if (isset($trm)) {
                $result[] = $trm->name();
            }
        }
        return $result;
    }

}
