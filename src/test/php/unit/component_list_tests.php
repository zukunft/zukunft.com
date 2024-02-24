<?php

/*

    test/unit/component_list_unit.php - TESTing of the COMPONENT LIST functions
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

use api\component\component as component_api;
use cfg\component\component;
use cfg\component\component_list;
use cfg\db\sql_db;
use test\test_cleanup;

class component_list_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'component_list->';
        $t->resource_path = 'db/component/';
        $usr->set_id(1);

        $t->header('Unit tests of the component list class (src/main/php/model/component/component_list.php)');


        $t->subheader('Database query creation tests');

        // load only the names
        $phr_lst = new component_list($usr);
        $t->assert_sql_names($db_con, $phr_lst, new component($usr));
        $t->assert_sql_names($db_con, $phr_lst, new component($usr), component_api::TN_READ);

        $cmp_lst = new component_list($usr);
        $this->assert_sql_by_view_id($t, $db_con, $cmp_lst);

    }

    /**
     * test the SQL statement creation for a component list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_list $lst the component list object for the sql creation
     * @return void
     */
    private function assert_sql_by_view_id(test_cleanup $t, sql_db $db_con, component_list $lst): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_view_id($db_con->sql_creator(), 1);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_view_id($db_con->sql_creator(), 1);
        $t->assert_qp($qp, $db_con->db_type);
    }

}