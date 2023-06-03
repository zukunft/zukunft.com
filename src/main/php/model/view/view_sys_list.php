<?php

/*

    model/view/view_sys_list.php - list of predefined system views
    ----------------------------

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
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'view_list.php';

use api\view_list_api;
use controller\controller;
use model\sql_db;
use model\sql_par;
use model\user;
use model\view;
use model\view_list;

global $system_views;

class view_sys_list extends type_list
{

    public user $usr;   // the user object of the person for whom the verb list is loaded, so to say the viewer

    function __construct(user $usr)
    {
        $this->set_user($usr);
    }


    /*
     * set and get
     */

    /**
     * set the user of the phrase list
     *
     * @param user $usr the person who wants to access the phrases
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the phrases
     */
    function user(): user
    {
        return $this->usr;
    }


    /*
     * cast
     */

    /**
     * @return view_list_api the object type list frontend api object
     */
    function api_obj(): object
    {
        $api_obj = new view_list_api();
        foreach ($this->lst as $dsp) {
            $api_obj->add($dsp->api_obj());
        }
        return $api_obj;
    }


    /*
     * loading functions
     */

    /**
     * set the SQL query parameters to load a list of views from the database that have a used code id
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_list(sql_db $db_con): sql_par
    {
        $this->lst = [];
        $dsp_lst = new view_list($this->usr);
        $qp = $dsp_lst->load_sql($db_con, self::class);
        $qp->name .= 'sys_views';
        $db_con->set_name($qp->name);
        $db_con->set_where_text('code_id IS NOT NULL');
        $db_con->set_order(view::FLD_ID);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * force to reload the list of views from the database that have a used code id
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $db_type the database name in this case view just for compatibility reasons
     * @return array the list of views used by the system
     */
    private function load_list(sql_db $db_con, string $db_type): array
    {
        $this->lst = [];
        $qp = $this->load_sql_list($db_con);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $dsp = new view($this->usr);
                $dsp->row_mapper($db_row);
                $dsp->load_components();
                $this->lst[$db_row[$db_con->get_id_field_name($db_type)]] = $dsp;
            }
        }
        return $this->lst;
    }

    /**
     * overwrite the general user sys list load function to keep the link to the table sys capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_VIEW): bool
    {
        $result = false;
        $this->lst = $this->load_list($db_con, $db_type);
        $this->hash = $this->get_hash($this->lst);
        if (count($this->hash) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * adding the system views used for unit tests to the dummy list
     */
    function load_dummy(): void {
        parent::load_dummy();
        $dsp = new view($this->usr);
        $dsp->set_name(controller::DSP_WORD);
        $dsp->code_id = controller::DSP_WORD;
        $this->lst[2] = $dsp;
        $this->hash[controller::DSP_WORD] = 2;
    }

    /**
     * return the database id of the default view sys
     */
    function default_id(): int
    {
        return parent::id(controller::DSP_WORD);
    }

}

