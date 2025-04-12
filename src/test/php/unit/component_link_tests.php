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

namespace unit;

include_once MODEL_COMPONENT_PATH . 'position_type.php';
include_once MODEL_COMPONENT_PATH . 'component_link_type.php';
include_once MODEL_COMPONENT_PATH . 'component_link.php';
include_once MODEL_COMPONENT_PATH . 'component_link_list.php';

use cfg\component\component_link;
use cfg\component\component_link_type;
use cfg\component\position_type;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_type;
use test\test_cleanup;

class component_link_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'component_link->';
        $t->resource_path = 'db/component/';

        // start the test section (ts)
        $ts = 'unit component link ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup statements');
        $cmp_lnk_typ = new component_link_type('');
        $t->assert_sql_table_create($cmp_lnk_typ);
        $t->assert_sql_index_create($cmp_lnk_typ);
        $cmp_pos_typ = new position_type('');
        $t->assert_sql_table_create($cmp_pos_typ);
        $t->assert_sql_index_create($cmp_pos_typ);
        $cmp_lnk = $t->component_link();
        $t->assert_sql_table_create($cmp_lnk);
        $t->assert_sql_index_create($cmp_lnk);
        $t->assert_sql_foreign_key_create($cmp_lnk);

        $t->subheader($ts . 'sql user sandbox statement');

        // SQL creation tests (mainly to use the IDE check for the generated SQL statements)
        $vcl = new component_link($usr);
        $t->assert_sql_by_id($sc, $vcl);
        $t->assert_sql_by_link($sc, $vcl);
        $this->assert_sql_link_and_pos($t, $db_con, $vcl);
        $this->assert_sql_max_pos($t, $db_con, $vcl);


        $t->subheader($ts . 'sql statement');

        // sql to load a view component link by the id
        $lnk = new component_link($usr);
        $lnk->set_id(1);
        $t->assert_sql_user_changes($sc, $lnk);

        $t->subheader($ts . 'component link sql write');
        $lnk = $t->component_link();
        $t->assert_sql_insert($sc, $lnk);
        $t->assert_sql_insert($sc, $lnk, [sql_type::USER]);
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG, sql_type::USER]);
        $lnk = $t->component_link();
        $lnk->exclude();
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG, sql_type::USER]);
        $lnk_filled = $t->component_link_filled();
        $t->assert_sql_insert($sc, $lnk_filled, [sql_type::LOG]);
        $lnk_reordered = clone $lnk;
        $lnk_reordered->order_nbr = 2;
        $t->assert_sql_update($sc, $lnk_reordered, $lnk);
        $t->assert_sql_update($sc, $lnk_reordered, $lnk, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $lnk);
        $t->assert_sql_delete($sc, $lnk, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $lnk, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'component link base object handling');
        $lnk = $t->component_link_filled();
        $t->assert_reset($lnk);

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
        test_cleanup   $t,
        sql_db         $db_con,
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
        test_cleanup   $t,
        sql_db         $db_con,
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