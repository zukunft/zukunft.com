<?php

/*

    model/formula/formula_element_list.php - a list of formula elements to place the name function
    --------------------------------------

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

use cfg\db\sql_par_type;

include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_FORMULA_PATH . 'formula_element.php';
include_once MODEL_FORMULA_PATH . 'parameter_type.php';

class formula_element_list extends sandbox_list
{

    // array $lst is the list of formula elements

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
        $db_con->set_usr($this->user()->id());
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
            $db_con->add_par(sql_par_type::INT, $frm_id);
            $db_con->add_par(sql_par_type::INT, $this->user()->id());
            $qp->sql = $db_con->select_by_field_list(array(formula::FLD_ID, user::FLD_ID));
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
            $db_con->add_par(sql_par_type::INT, $frm_id);
            $db_con->add_par(sql_par_type::INT, $elm_type_id);
            $db_con->add_par(sql_par_type::INT, $this->user()->id());
            $qp->sql = $db_con->select_by_field_list(array(formula::FLD_ID, formula_element::FLD_TYPE, user::FLD_ID));
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
                $elm = new formula_element($this->user());
                $elm->row_mapper($db_row);
                $this->lst[] = $elm;
                $result = true;
            }
        }

        return $result;
    }

    /*
     * modification function
     */

    /**
     * add one formula element to the list and keep the order (contrary to the parent function)
     * @returns bool true the element has been added
     */
    function add(?formula_element $elm_to_add): bool
    {
        $this->lst[] = $elm_to_add;
        $this->set_lst_dirty();
        return true;
    }

}

