<?php

/*

    test/unit/element.php - TESTing of the FORMULA ELEMENT and formula element list functions
    ---------------------
  

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
use cfg\element_list;
use cfg\element_type;
use test\test_cleanup;

class element_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'element->';
        $t->resource_path = 'db/element/';
        $usr->set_id(1);

        $t->header('Unit tests of the formula element class (src/main/php/model/formula/element.php)');

        $t->subheader('Element SQL setup statements');
        $elm_typ = new element_type('');
        $t->assert_sql_table_create($elm_typ);
        $t->assert_sql_index_create($elm_typ);
        $elm = $t->dummy_element();
        $t->assert_sql_table_create($elm);
        $t->assert_sql_index_create($elm);
        $t->assert_sql_foreign_key_create($elm);

        $t->subheader('SQL creation tests');

        $elm = $t->dummy_element();
        $t->assert_sql_by_id($db_con, $elm);


        $t->subheader('Database query list creation tests');

        // load by formula id
        $frm_elm_lst = new element_list($usr);
        $frm_id = 5;
        $this->assert_sql_by_frm_id($t, $db_con, $frm_elm_lst, $frm_id);

        // load by formula id and filter by element type
        $frm_elm_lst = new element_list($usr);
        $elm_type_id = 7;
        $this->assert_sql_by_frm_and_type_id($t, $db_con, $frm_elm_lst, $frm_id, $elm_type_id);

        $t->subheader('element sql write');
        // TODO activate db write
        //$t->assert_sql_insert($db_con, $elm);
        //$t->assert_sql_insert($db_con, $elm, true);
        // TODO activate db write
        //$t->assert_sql_update($db_con, $elm);
        //$t->assert_sql_update($db_con, $elm, true);
        // TODO activate db write
        //$t->assert_sql_delete($db_con, $elm);
        //$t->assert_sql_delete($db_con, $elm, true);


        // JSON export list
        /*
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd_time);
        $wrd_lst->add($wrd_measure);
        $wrd_lst->add($wrd_scale);
        $json = json_encode($wrd_lst->export_obj());
        $t->assert($t->name . '->measure list', $json, '[{"plural":"","description":"","type":"time","view":"","refs":[],"name":"time_word","share":"","protection":""},{"plural":"","description":"","type":"measure","view":"","refs":[],"name":"measure_word","share":"","protection":""},{"plural":"","description":"","type":"scaling","view":"","refs":[],"name":"scale_word","share":"","protection":""}]');
        */

    }

    /**
     * test the SQL statement creation for a formula element list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param element_list $lst the empty formula element list object
     * @param int $frm_id id of the formula to be used for the query creation
     * @return void
     */
    private function assert_sql_by_frm_id(test_cleanup $t, sql_db $db_con, element_list $lst, int $frm_id): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_frm_id($db_con->sql_creator(), $frm_id);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_frm_id($db_con->sql_creator(), $frm_id);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation for a formula element list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param element_list $lst the empty formula element list object
     * @param int $frm_id id of the formula to be used for the query creation
     * @param int $elm_type_id
     * @return void
     */
    private function assert_sql_by_frm_and_type_id(test_cleanup $t,
                                                   sql_db       $db_con,
                                                   element_list $lst,
                                                   int          $frm_id,
                                                   int          $elm_type_id): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_frm_and_type_id($db_con->sql_creator(), $frm_id, $elm_type_id);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_frm_and_type_id($db_con->sql_creator(), $frm_id, $elm_type_id);
        $t->assert_qp($qp, $db_con->db_type);
    }

}