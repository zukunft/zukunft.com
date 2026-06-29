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

namespace Zukunft\ZukunftCom\main\php\cfg\element;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::SHARED_CONST_FIELDS . 'formula_fields.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_PHRASE . 'term_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox_list.php';
include_once paths::MODEL_SYSTEM . 'list_db_write.php';
include_once paths::MODEL_SYSTEM . 'sys_log_level.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'sys_log_levels.php';
include_once paths::SHARED_HELPER . 'ListOfIdObjects.php';
include_once paths::SHARED_HELPER . 'Message.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\cfg\system\list_db_write;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_levels;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOfIdObjects;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;

class element_list extends sandbox_list
{

    /*
     * construct and map
     */

    /**
     * add the element object
     * to the parent function that fills the element list based on a database record
     *
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one element has been loaded
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new element($this->get_user()), $db_rows, $load_all);
    }


    /*
     * set and get
     */

    function term_list(): term_list
    {
        $trm_lst = new term_list($this->get_user());
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    private function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(element::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(element_db::FLD_NAMES);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of formula elements by the formula id
     * @param sql_creator $sc with the target db_type set
     * @param int $frm_id the id of the formula which elements should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_frm_id(sql_creator $sc, int $frm_id): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_id');
        if ($frm_id > 0) {
            $sc->add_where(formula_fields::FLD_ID, $frm_id);
            $sc->add_where(user_db::FLD_ID, $this->get_user()->id);
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_frm_and_type_id(sql_creator $sc, int $frm_id, int $elm_type_id): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_and_type_id');
        if ($frm_id > 0 and $elm_type_id != 0) {
            $sc->add_where(formula_fields::FLD_ID, $frm_id);
            $sc->add_where(element_db::FLD_TYPE, $elm_type_id);
            $sc->add_where(user_db::FLD_ID, $this->get_user()->id);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    function get_by_link_id(element $elm): element|null
    {
        $res_elm = null;
        foreach ($this->lst() as $chk_elm) {
            if ($res_elm == null) {
                if ($chk_elm->frm->id() == $elm->frm->id()
                    and $chk_elm->trm_id() == $elm->trm_id()) {
                    $res_elm = $elm;
                }
            }
        }
        return $res_elm;
    }


    /*
     * info
     */

    /**
     * get the first ids from the list e.g. to show it to humans
     *
     * @param ?int $limit the max number of ids to show
     * @return array with the database ids of all objects of this list
     */
    function ids(?int $limit = null): array
    {
        if ($limit == null and !$this->is_dirty()) {
            $result = array_keys($this->id_pos_lst());
        } else {
            $result = array();
            $pos = 0;
            foreach ($this->lst() as $sbx_obj) {
                if ($pos <= $limit or $limit == null) {
                    // use only valid ids
                    if ($sbx_obj->frm?->id() != 0) {
                        $id_txt = $sbx_obj->frm?->id();
                        if ($sbx_obj->obj?->id() != 0) {
                            $id_txt .= '/' . $sbx_obj->obj?->id();
                        }
                        $result[] = $id_txt;
                        $pos++;
                    }
                }
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add an object to the list that does
     * not yet have a database id
     * but has linked objects
     *
     * @param element|db_object_seq_id|null $to_add the object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @param Message $msg to report which entry is double
     * @returns bool true if the object has been added
     */
    function add_by_link(
        element|db_object_seq_id|null $to_add,
        bool                          $allow_duplicates = false,
        Message                       $msg = new Message()
    ): bool
    {
        $result = false;
        if ($allow_duplicates) {
            $result = $this->add($to_add);
        } else {
            $elm = $this->get_by_link_id($to_add);
            if ($elm == null) {
                $result = $this->add($to_add);
            }
        }
        return $result;
    }

    /**
     * add one formula element to the list and keep the order (contrary to the parent function)
     * @returns bool true the element has been added
     */
    function add(?element $elm_to_add): bool
    {
        parent::add_direct($elm_to_add);
        $this->set_hash_dirty();
        return true;
    }

    function merge(element_list $lst): void
    {
        foreach ($lst->lst() as $elm) {
            $this->add($elm);
        }
    }


    /*
     * filter
     */

    /**
     * get all objects that are not in the given list
     *
     * @param element_list|list_db_write|ListOfIdObjects $lst the list to compare with
     * @return element_list|list_db_write|ListOfIdObjects the list of objects that are only in this list
     */
    function diff(
        element_list|list_db_write|ListOfIdObjects $lst
    ): element_list|list_db_write|ListOfIdObjects
    {
        $lst = $this->clone_reset();
        foreach ($this->lst() as $elm) {
            if (!$lst->get_by_link_id($elm)) {
                $lst->add($elm);
            }
        }
        return $lst;
    }

    /**
     * get the formula elements from this list that use the verb following
     * to select the values
     * @return element_list with precoded formula elements using predicate "following"
     */
    function predefined_following(): element_list
    {
        $result = new element_list($this->get_user());
        foreach ($this->lst() as $elm) {
            if ($elm->is_formula()) {
                if ($elm->obj->uses_following()) {
                    $result->add($elm);
                }
            }
        }
        return $result;
    }

    /**
     * remove all duplicate links from this list
     * the element list created from the expression may contain the same link more than once
     * e.g. for correct number fillings.
     * whereas the element list loaded from the database contains each link only once
     * because the database table should only be used to select the the formula so no duplicates are needed
     *
     * @return element_list with only unique links
     */
    function unique(): element_list
    {
        $lst = $this->clone_reset();
        foreach ($this->lst() as $elm) {
            if (!$lst->get_by_link_id($elm)) {
                $lst->add($elm);
            }
        }
        return $lst;
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
            $db_con->exe_try('del elements', $qp->sql, $qp->name, $qp->par, sys_log_levels::FATAL_ID));
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
            element::class, new element($this->get_user())->id_field(), $this->ids());
    }

}

