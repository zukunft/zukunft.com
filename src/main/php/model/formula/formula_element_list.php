<?php

/*

    formula_element_list.php - a list of formula elements to place the name function
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class formula_element_list
{

    public array $lst; // the list of formula elements
    public user $usr;  // the person who has requested the formula elements

    /**
     * always set the user because a formula element list is always user specific
     * @param user $usr the user who requested to see the formula with the formula elements
     */
    function __construct(user $usr)
    {
        $this->lst = array();
        $this->usr = $usr;
    }

    /*
     * load functions
     */

    /**
     * set the SQL query parameters to load a list of formula elements
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_FORMULA_ELEMENT);
        $qp = new sql_par(self::class);
        $db_con->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(formula_element::FLD_NAMES);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formula elements by the formula id
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $frm_id the id of the formula which elements should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_id(sql_db $db_con, int $frm_id): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($frm_id > 0) {
            $qp->name .= 'frm_id';
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_INT, $frm_id);
            $db_con->add_par(sql_db::PAR_INT, $this->usr->id);
            $qp->sql = $db_con->select_by_field_list(array(formula::FLD_ID, user_sandbox::FLD_USER));
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formula elements by the formula id and filter by the element type
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $frm_id the id of the formula which elements should be loaded
     * @param int $elm_type_id the id of the formula element type used to filter the elements
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_and_type_id(sql_db $db_con, int $frm_id, int $elm_type_id): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($frm_id > 0) {
            $qp->name .= 'frm_and_type_id';
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_INT, $frm_id);
            $db_con->add_par(sql_db::PAR_INT, $elm_type_id);
            $db_con->add_par(sql_db::PAR_INT, $this->usr->id);
            $qp->sql = $db_con->select_by_field_list(array(formula::FLD_ID, formula_element::FLD_TYPE, user_sandbox::FLD_USER));
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    function load_by_frm_and_type_id(int $frm_id, int $elm_type_id): bool
    {
        global $db_con;
        $result = false;

        $qp = $this->load_sql_by_frm_and_type_id($db_con, $frm_id, $elm_type_id);
        $db_rows = $db_con->get($qp);
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                $elm = new formula_element($this->usr);
                $elm->row_mapper($db_row);
                $this->lst[] = $elm;
                $result = true;
            }
        }

        return $result;
    }

    /*
     * display functions
     */

    /**
     * return best possible identification for this element list mainly used for debugging
     */
    function dsp_id(): string
    {
        $id = dsp_array($this->ids());
        $name = $this->name();
        if ($name <> '""') {
            $result = $name . ' (' . $id . ')';
        } else {
            $result = $id;
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }

        return $result;
    }

    /**
     * to show the element name to the user in the most simple form (without any ids)
     * this function is called from dsp_id, so no other call is allowed
     */
    function name(): string
    {
        $result = '';
        foreach ($this->lst as $elm) {
            $result .= $elm->name() . ' ';
        }
        return $result;
    }

    /**
     * this function is called from dsp_id, so no other call is allowed
     */
    function ids(): array
    {
        $result = array();
        foreach ($this->lst as $elm) {
            // use only valid ids
            if ($elm->id <> 0) {
                $result[] = $elm->id;
            }
        }
        return $result;
    }

}

class formula_element_type extends BasicEnum
{
    const WORD = 1;
    const VERB = 2;
    const FORMULA = 3;
    const TRIPLE = 4;

    protected static function get_description($value): string
    {
        $result = 'formula element type "' . $value . '" not yet defined';

        switch ($value) {

            // system log
            case formula_element_type::WORD:
                $result = 'a reference to a simple word';
                break;
            case formula_element_type::VERB:
                $result = 'a reference to predicate';
                break;
            case formula_element_type::FORMULA:
                $result = 'a reference to another formula';
                break;
            case formula_element_type::TRIPLE:
                $result = 'a reference to word link';
                break;
        }

        return $result;
    }
}

