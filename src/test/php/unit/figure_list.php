<?php

/*

    test/unit/result_list.php - unit testing of the FORMULA VALUE functions
    --------------------------------
  

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

include_once WEB_FIGURE_PATH . 'figure_list.php';

use model\figure;
use model\figure_list;
use model\sql_db;
use html\figure\figure_list as figure_list_dsp;

class figure_list_unit_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'figure->';
        $t->resource_path = 'db/figure/';
        $json_file = 'unit/figure/figure_list_import.json';
        $usr->set_id(1);


        $t->header('Unit tests of the figure list class (src/main/php/model/figure/figure_list.php)');

        $t->subheader('SQL statement creation tests');

        // load by figase ids
        $fig_lst = new figure_list($usr);
        $this->assert_sql_by_ids($t, $db_con, $fig_lst, array(1, -1));


        $t->subheader('Im- and Export tests');
        // TODO active
        //$t->assert_json(new figure_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        // TODO active
        //$fig_lst = $t->dummy_figure_list();
        //$t->assert_api_to_dsp($fig_lst, new figure_list_dsp());

    }

    /**
     * test the SQL statement creation for a figure list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param figure_list $lst the empty figure list object
     * @param array $ids filled with a list of word ids to be used for the query creation
     * @return void true if all tests are fine
     */
    private function assert_sql_by_ids(testing $t, sql_db $db_con, figure_list $lst, array $ids): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_ids($db_con, $ids);
        $result = $t->assert_qp($qp, sql_db::POSTGRES);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst->load_sql_by_ids($db_con, $ids);
            $result = $t->assert_qp($qp, sql_db::MYSQL);
        }
    }
}