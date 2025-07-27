<?php

/*

    model/element/element_list.php - a list of formula elements to place the name function
    ----------------------------

    The main sections of this object are
    - construct and map: including the mapping of the db row to this element object
    - load:              database access object (DAO) functions
    - modify:            change potentially all object and all variables of this list with one function call


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\element;

use cfg\const\paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_PHRASE . 'term_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log_level.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';

use cfg\db\sql_creator;
use cfg\db\sql_par;
use cfg\formula\formula_db;
use cfg\phrase\term_list;
use cfg\sandbox\sandbox_list;
use cfg\system\sys_log_level;
use cfg\user\user;
use cfg\user\user_message;

class element_list extends sandbox_list
{

    /*
     * construct and map
     */

    /**
     * add the element object
     * to the parent function that fills the element list based on a database records
     *
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one component has been loaded
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new element($this->user()), $db_rows, $load_all);
    }


    /*
     * set and get
     */

    function term_list(): term_list
    {
        $trm_lst = new term_list($this->user());
        foreach ($this->lst() as $elm) {
            $trm_lst->add($elm->term());
        }
        return $trm_lst;
    }


    /*
     * load
     */

    function load_by_frm(int $frm_id): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_frm_id($sc, $frm_id);
        return $this->load($qp);
    }

    function load_by_frm_and_type_id(int $frm_id, int $elm_type_id): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_frm_and_type_id($sc, $frm_id, $elm_type_id);
        return $this->load($qp);
    }

    /**
     * set the SQL query parameters to load a list of formula elements
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(element::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(element::FLD_NAMES);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formula elements by the formula id
     * @param sql_creator $sc with the target db_type set
     * @param int $frm_id the id of the formula which elements should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_id(sql_creator $sc, int $frm_id): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_id');
        if ($frm_id > 0) {
            $sc->add_where(formula_db::FLD_ID, $frm_id);
            $sc->add_where(user::FLD_ID, $this->user()->id());
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formula elements by the formula id and filter by the element type
     * @param sql_creator $sc with the target db_type set
     * @param int $frm_id the id of the formula which elements should be loaded
     * @param int $elm_type_id the id of the formula element type used to filter the elements
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_and_type_id(sql_creator $sc, int $frm_id, int $elm_type_id): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_and_type_id');
        if ($frm_id > 0 and $elm_type_id != 0) {
            $sc->add_where(formula_db::FLD_ID, $frm_id);
            $sc->add_where(element::FLD_TYPE, $elm_type_id);
            $sc->add_where(user::FLD_ID, $this->user()->id());
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }


    /*
     * modify
     */

    /**
     * add one formula element to the list and keep the order (contrary to the parent function)
     * @returns bool true the element has been added
     */
    function add(?element $elm_to_add): bool
    {
        $this->add_obj($elm_to_add, true);
        $this->set_lst_dirty();
        return true;
    }


    /*
     * del
     */

    function del_without_log(): user_message
    {
        global $db_con;

        $usr_msg = new user_message();
        $sc = $db_con->sql_creator();
        $qp = $this->del_sql_without_log($sc);
        $usr_msg->add_message_text(
            $db_con->exe_try('del elements', $qp->sql, '', array(), sys_log_level::FATAL));
        return $usr_msg;
    }

    /**
     * create a sql statement that deletes all formula elements of this list
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par
     */
    function del_sql_without_log(sql_creator $sc): sql_par
    {
        return $sc->del_sql_list_without_log(
            element::class, (new element($this->user()))->id_field(), $this->ids());
    }

}

