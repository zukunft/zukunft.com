<?php

/*

    test/unit/view_list.php - TESTing of the VIEW LIST functions
    -----------------------
  

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

use api\word_api;
use cfg\phrase_type;
use cfg\view;
use cfg\view_sys_list;
use cfg\sql_db;
use cfg\view_list;

class view_list_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'view_list->';
        $t->resource_path = 'db/view/';
        $json_file = 'unit/view/view_list.json';
        $usr->set_id(1);

        $t->header('Unit tests of the view list class (src/main/php/model/view/view_list.php)');

        $t->subheader('Database query creation tests');

        // load the system views
        $sys_dsp_lst = new view_sys_list($usr);
        $this->assert_sql_sys_views($t, $db_con, $sys_dsp_lst);

        // load of non system view
        $dsp_lst = new view_list($usr);
        $t->assert_sql_names($db_con, $dsp_lst, new view($usr));


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new view_list($usr), $json_file);

    }

    /**
     * test the SQL statement creation for the system view list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param view_sys_list $lst
     * @return void
     */
    private function assert_sql_sys_views(test_cleanup $t, sql_db $db_con, view_sys_list $lst): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_list($db_con);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_list($db_con);
        $t->assert_qp($qp, $db_con->db_type);
    }

}