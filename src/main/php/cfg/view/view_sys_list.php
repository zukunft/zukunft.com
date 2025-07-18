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

namespace cfg\view;

include_once MODEL_HELPER_PATH . 'type_list.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'view_list.php';
include_once SHARED_CONST_PATH . 'views.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\helper\type_list;
use cfg\user\user;
use shared\const\views as view_shared;

class view_sys_list extends type_list
{

    public user $usr;   // the user object of the person for whom the verb list is loaded, so to say the viewer

    function __construct(user $usr)
    {
        parent::__construct();
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
     * load
     */

    /**
     * force to reload the list of views from the database that have a used code id
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the database name in this case view just for compatibility reasons
     * @return array the list of views used by the system
     */
    protected function load_list(sql_db $db_con, string $class): array
    {
        $this->reset();
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_list($sc);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $msk = new view($this->usr);
                $msk->row_mapper_sandbox($db_row);
                $msk->load_components($db_con);
                $this->add($msk);
            }
        }
        return $this->lst();
    }

    /**
     * set the SQL query parameters to load a list of views from the database that have a used code id
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_list(sql_creator $sc): sql_par
    {
        $this->reset();
        $dsp_lst = new view_list($this->usr);
        $qp = $dsp_lst->load_sql($sc, 'sys_views');
        $sc->set_name($qp->name);
        $msk = new view($this->user());
        $sc->set_id_field($msk->id_field());
        $sc->add_where(sql_db::FLD_CODE_ID, '', sql_par_type::NOT_NULL);
        $sc->set_order(view_db::FLD_ID);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * overwrite the general user sys list load function to keep the link to the table sys capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $class = view::class): bool
    {
        $result = false;
        $this->set_lst($this->load_list($db_con, $class));
        if ($this->count() > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * adding the system views used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $msk = new view($this->usr);
        $msk->set_id(2);
        $msk->set_name(view_shared::WORD);
        $msk->set_code_id_db(view_shared::WORD_CODE_ID);
        $this->add($msk);
    }

    /**
     * return the database id of the default view sys
     */
    function default_id(): int
    {
        return parent::id(view_shared::WORD);
    }

}

