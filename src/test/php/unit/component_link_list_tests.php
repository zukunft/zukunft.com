<?php

/*

    test/unit/component_link_list.php - testing the links between views and components
    ---------------------------------
  

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

namespace unit;

include_once MODEL_COMPONENT_PATH . 'component_link_list.php';

use cfg\component\component_link_list;
use cfg\db\sql;
use cfg\db\sql_db;
use shared\library;
use test\test_cleanup;

class component_link_list_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'component_link_list->';
        $t->resource_path = 'db/component/';
        $usr->set_id(1);

        $t->header('Unit tests of the view component link list class (src/main/php/model/component/component_link_list.php)');

        $t->subheader('Database query creation tests');

        // load by component_link ids
        $lst = new component_link_list($usr);
        //$t->assert_sql_by_ids($sc, $lst, array(3, 2, 4));

        // load by view
        $lst = new component_link_list($usr);
        $this->assert_sql_by_view($t, $db_con, $lst);

        // load by component
        $lst = new component_link_list($usr);
        $this->assert_sql_by_component($t, $db_con, $lst);

    }

    /**
     * test the SQL statement creation to load the components of a view
     * in all SQL dialect and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link_list $lst the empty component_link list object
     * @return void
     */
    private function assert_sql_by_view(
        test_cleanup        $t,
        sql_db              $db_con,
        component_link_list $lst
    ): void
    {
        $msk = $t->dummy_view();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_view($db_con->sql_creator(), $msk);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_view($db_con->sql_creator(), $msk);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation to load the views of a component
     * in all SQL dialect and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link_list $lst the empty component_link list object
     * @return void
     */
    private function assert_sql_by_component(
        test_cleanup        $t,
        sql_db              $db_con,
        component_link_list $lst
    ): void
    {
        $cmp = $t->dummy_component();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_component($db_con->sql_creator(), $cmp);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_component($db_con->sql_creator(), $cmp);
        $t->assert_qp($qp, $db_con->db_type);
    }

}