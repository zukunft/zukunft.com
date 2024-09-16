<?php

/*

    test/unit/element_list_tests.php - unit tests of for formula element lists
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

namespace unit;

include_once MODEL_ELEMENT_PATH . 'element_list.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\element\element_list;
use cfg\element\element_type;
use cfg\sandbox_list;
use test\test_cleanup;

class element_list_tests
{
    function run(test_cleanup $t): void
    {

        global $element_types;

        // init
        $sc = new sql();
        $t->name = 'element_list->';
        $t->resource_path = 'db/element/';
        $elm_lst = new element_list($t->usr1);


        $t->header('element list unit tests');

        $t->subheader('load tests');

        $test_name = 'sql to load all elements of one formula';
        $frm = $t->formula();
        $t->assert_sql_by_frm_id($sc, $elm_lst, $frm->id(), $test_name);

        $test_name = 'sql to load one type of elements related in one formula';
        $elm_type_id = $element_types->id(element_type::WORD_SELECTOR);
        $this->assert_sql_by_frm_and_type_id($t, $sc, $elm_lst, $frm->id(), $elm_type_id, $test_name);

        $test_name = 'sql to delete a list of elemets';
        $elm_lst = $t->element_list();
        $this->assert_sql_del_by_id_lst($t, $sc, $elm_lst, $test_name);

    }

    /**
     * test the SQL statement creation for a formula element list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql $sc the test database connection
     * @param element_list $lst the empty formula element list object
     * @param int $frm_id id of the formula to be used for the query creation
     * @param int $elm_type_id
     * @param string $test_name description of the test without the sql name
     * @return void
     */
    private function assert_sql_by_frm_and_type_id(
        test_cleanup $t,
        sql          $sc,
        element_list $lst,
        int          $frm_id,
        int          $elm_type_id,
        string       $test_name = ''): void
    {
        // check the Postgres query syntax
        $sc->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_frm_and_type_id($sc, $frm_id, $elm_type_id);
        $result = $t->assert_qp($qp, $sc->db_type, $test_name);

        // check the MySQL query syntax
        if ($result) {
            $sc->db_type = sql_db::MYSQL;
            $qp = $lst->load_sql_by_frm_and_type_id($sc, $frm_id, $elm_type_id);
            $t->assert_qp($qp, $sc->db_type, $test_name);
        }
    }

    /**
     * check the sql to delete row select by id
     *
     * @param test_cleanup $t the test environment
     * @param sql $sc the test database connection
     * @param element_list $lst the empty formula element list object
     * @param string $test_name the test name only for the test log
     * @return void
     */
    function assert_sql_del_by_id_lst(
        test_cleanup $t,
        sql $sc,
        element_list $lst,
        string $test_name = ''): void
    {
        // check the Postgres query syntax
        $sc->db_type = sql_db::POSTGRES;
        $qp = $lst->del_sql_without_log($sc);
        $result = $t->assert_qp($qp, $sc->db_type, $test_name);

        // check the MySQL query syntax
        if ($result) {
            $sc->db_type = sql_db::MYSQL;
            $qp = $lst->del_sql_without_log($sc);
            $t->assert_qp($qp, $sc->db_type, $test_name);
        }
    }

}