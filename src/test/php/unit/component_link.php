<?php

/*

  test/unit/component_link.php - unit testing of the VIEW COMPONENT LINK functions
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

namespace test;

include_once MODEL_VIEW_PATH . 'component_link_list.php';

use cfg\component_link;
use cfg\db\sql_db;

class component_link_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'component_link->';
        $t->resource_path = 'db/component/';
        $usr->set_id(1);

        $t->header('Unit tests of the view component link class (src/main/php/model/view/component_link.php)');


        $t->subheader('SQL user sandbox statement tests');

        // SQL creation tests (mainly to use the IDE check for the generated SQL statements)
        $vcl = new component_link($usr);
        $t->assert_sql_by_id($db_con, $vcl);
        $t->assert_sql_by_link($db_con, $vcl);
        $this->assert_sql_link_and_type($t, $db_con, $vcl);
        $this->assert_sql_link_and_pos($t, $db_con, $vcl);
        $this->assert_sql_max_pos($t, $db_con, $vcl);


        $t->subheader('SQL statement tests');

        // sql to load a view component link by the id
        $lnk = new component_link($usr);
        $lnk->set_id(1);
        $t->assert_sql_user_changes($db_con, $lnk);

    }

    /**
     * test the SQL statement creation to retrieve a component link by view, component and link type
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link $vcl
     * @return void
     */
    private function assert_sql_link_and_type(
        test_cleanup $t,
        sql_db $db_con,
        component_link $vcl): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $vcl->load_sql_by_link_and_type($db_con->sql_creator(), 1, 2, 3);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $vcl->load_sql_by_link_and_type($db_con->sql_creator(), 1, 2, 3);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation to retrieve a component link by view, component and pos
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link $vcl
     * @return void
     */
    private function assert_sql_link_and_pos(
        test_cleanup $t,
        sql_db $db_con,
        component_link $vcl): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $vcl->load_sql_by_link_and_pos($db_con->sql_creator(), 1, 2, 3);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $vcl->load_sql_by_link_and_pos($db_con->sql_creator(), 1, 2, 3);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation to retrieve the max order number of one view
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link $vcl
     * @return void
     */
    private function assert_sql_max_pos(
        test_cleanup $t,
        sql_db $db_con,
        component_link $vcl): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $vcl->load_sql_max_pos($db_con->sql_creator(), 1);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $vcl->load_sql_max_pos($db_con->sql_creator(), 1);
        $t->assert_qp($qp, $db_con->db_type);
    }

}