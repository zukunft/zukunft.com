<?php

/*

    test/php/unit/formula_list.php - unit tests related to a formula list
    ------------------------------


    zukunft.com - calc with words

    copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

namespace test;

include_once MODEL_FORMULA_PATH . 'formula_list.php';
include_once WEB_FORMULA_PATH . 'formula_list.php';

use api\formula_api;
use api\word_api;
use cfg\formula;
use html\formula\formula_list as formula_list_dsp;
use cfg\formula_list;
use cfg\sql_db;
use cfg\word;

class formula_list_unit_tests
{

    /**
     * execute all formula list unit tests and return the test result
     */
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'formula_list->';
        $t->resource_path = 'db/formula/';
        $json_file = 'unit/formula/formula_list.json';

        $t->header('Unit tests of the formula list class (src/main/php/model/formula/formula_list.php)');

        $t->subheader('SQL statement creation tests');

        // sql to load a list of formulas by the id
        $frm_lst = new formula_list($usr);
        $t->assert_load_sql_ids($db_con, $frm_lst);
        $this->assert_load_sql_by_formula_ref($t, $db_con, $frm_lst);

        // check the Postgres query syntax to load a list of formulas by the names
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_names($db_con, array(formula_api::TN_INCREASE, formula_api::TN_ADD));
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $qp = $frm_lst->load_sql_by_names($db_con, array(formula_api::TN_INCREASE, formula_api::TN_ADD));
        $t->assert_qp($qp, sql_db::MYSQL);

        // check the Postgres query syntax to load a list of formulas by phrase
        $wrd = new word($usr);
        $wrd->set(1,word_api::TN_ADD);
        $phr = $wrd->phrase();
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_phr($db_con, $phr);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $qp = $frm_lst->load_sql_by_phr($db_con, $phr);
        $t->assert_qp($qp, sql_db::MYSQL);

        // check the Postgres query syntax to load a list of formulas by phrase list
        $phr_lst = (new phrase_list_unit_tests)->get_phrase_list();
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_phr_lst($db_con, $phr_lst);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $qp = $frm_lst->load_sql_by_phr_lst($db_con, $phr_lst);
        $t->assert_qp($qp, sql_db::MYSQL);

        // check the Postgres query syntax to load a page of all formulas
        $frm_lst = new formula_list($usr);
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_all($db_con, 10, 2);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $qp = $frm_lst->load_sql_all($db_con, 10, 2);
        $t->assert_qp($qp, sql_db::MYSQL);


        $t->subheader('API unit tests');

        $frm_lst = $t->dummy_formula_list();
        $t->assert_api($frm_lst);


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new formula_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->dummy_formula_list();
        $t->assert_api_to_dsp($trp_lst, new formula_list_dsp());

    }

    /**
     * check the load SQL statements creation to get the formulas that use a given formula
     * similar to assert_load_sql of the test_base class
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_list $frm_lst the user sandbox object e.g. a word
     * @return bool true if all tests are fine
     */
    function assert_load_sql_by_formula_ref(test_cleanup $t, sql_db $db_con, formula_list $frm_lst): bool
    {
        // prepare
        $frm = new formula($t->usr1);
        $frm->set_id(1);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_formula_ref($db_con, $frm);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lst->load_sql_by_formula_ref($db_con, $frm);
            $result = $t->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

}