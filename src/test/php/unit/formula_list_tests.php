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

namespace unit;

include_once MODEL_FORMULA_PATH . 'formula_list.php';
include_once WEB_FORMULA_PATH . 'formula_list.php';

use api\formula\formula as formula_api;
use api\word\word as word_api;
use cfg\db\sql;
use cfg\formula;
use cfg\triple;
use cfg\verb;
use html\formula\formula_list as formula_list_dsp;
use cfg\formula_list;
use cfg\db\sql_db;
use cfg\word;
use test\test_cleanup;

class formula_list_tests
{

    /**
     * execute all formula list unit tests and return the test result
     */
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'formula_list->';
        $t->resource_path = 'db/formula/';
        $json_file = 'unit/formula/formula_list.json';

        $t->header('Unit tests of the formula list class (src/main/php/model/formula/formula_list.php)');

        $t->subheader('SQL statement creation tests');

        // load only the names
        $frm_lst = new formula_list($usr);
        $t->assert_sql_names($sc, $frm_lst, new formula($usr));
        $t->assert_sql_names($sc, $frm_lst, new formula($usr), formula_api::TN_READ);

        // sql to load a list of formulas by the id, name or ...
        $frm_lst = new formula_list($usr);
        $t->assert_sql_by_ids($sc, $frm_lst);
        $t->assert_sql_by_names($sc, $frm_lst, array(formula_api::TN_INCREASE, formula_api::TN_INCREASE));
        $t->assert_sql_like($sc, $frm_lst, 'i');
        $t->assert_sql_all_paged($db_con, $frm_lst);
        $this->assert_sql_by_word_ref($t, $db_con, $frm_lst);
        $this->assert_sql_by_triple_ref($t, $db_con, $frm_lst);
        $this->assert_sql_by_verb_ref($t, $db_con, $frm_lst);
        $this->assert_sql_by_formula_ref($t, $db_con, $frm_lst);
        $this->assert_sql_by_phr($t, $db_con, $frm_lst);
        $this->assert_sql_by_phr_lst($t, $db_con, $frm_lst);


        $t->subheader('API unit tests');

        $frm_lst = $t->formula_list();
        $t->assert_api($frm_lst);


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new formula_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->formula_list();
        $t->assert_api_to_dsp($trp_lst, new formula_list_dsp());

    }

    /**
     * check the load SQL statements creation to get the formulas that
     * use a given word
     * similar to assert_sql_all of the test_base class
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_list $frm_lst the user sandbox object e.g. a word
     */
    private function assert_sql_by_word_ref(test_cleanup $t, sql_db $db_con, formula_list $frm_lst): void
    {
        // prepare
        $wrd = new word($t->usr1);
        $wrd->set_id(1);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_word_ref($db_con->sql_creator(), $wrd);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lst->load_sql_by_word_ref($db_con->sql_creator(), $wrd);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements creation to get the formulas that
     * use a given triple
     * similar to assert_sql_all of the test_base class
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_list $frm_lst the user sandbox object e.g. a word
     */
    private function assert_sql_by_triple_ref(test_cleanup $t, sql_db $db_con, formula_list $frm_lst): void
    {
        // prepare
        $trp = new triple($t->usr1);
        $trp->set_id(1);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_triple_ref($db_con->sql_creator(), $trp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lst->load_sql_by_triple_ref($db_con->sql_creator(), $trp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements creation to get the formulas that
     * use a given verb
     * similar to assert_sql_all of the test_base class
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_list $frm_lst the user sandbox object e.g. a word
     */
    private function assert_sql_by_verb_ref(test_cleanup $t, sql_db $db_con, formula_list $frm_lst): void
    {
        // prepare
        $vrb = new verb();
        $vrb->set_id(1);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_verb_ref($db_con->sql_creator(), $vrb);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lst->load_sql_by_verb_ref($db_con->sql_creator(), $vrb);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements creation to get the formulas that
     * use a given formula
     * similar to assert_sql_all of the test_base class
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_list $frm_lst the user sandbox object e.g. a word
     */
    private function assert_sql_by_formula_ref(test_cleanup $t, sql_db $db_con, formula_list $frm_lst): void
    {
        // prepare
        $frm = new formula($t->usr1);
        $frm->set_id(1);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_formula_ref($db_con->sql_creator(), $frm);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lst->load_sql_by_formula_ref($db_con->sql_creator(), $frm);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements creation to get the formulas that
     * use value related to the given phrase
     * similar to assert_sql_all of the test_base class
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_list $frm_lst the user sandbox object e.g. a word
     */
    private function assert_sql_by_phr(test_cleanup $t, sql_db $db_con, formula_list $frm_lst): void
    {
        // prepare
        $wrd = new word($t->usr1);
        $wrd->set(1,word_api::TN_ADD);
        $phr = $wrd->phrase();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_phr($db_con->sql_creator(), $phr);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lst->load_sql_by_phr($db_con->sql_creator(), $phr);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements creation to get the formulas that
     * use value related to the given phrase list
     * similar to assert_sql_all of the test_base class
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_list $frm_lst the user sandbox object e.g. a word
     */
    private function assert_sql_by_phr_lst(test_cleanup $t, sql_db $db_con, formula_list $frm_lst): void
    {
        // prepare
        $phr_lst = (new phrase_list_tests)->get_phrase_list();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lst->load_sql_by_phr_lst($db_con->sql_creator(), $phr_lst);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lst->load_sql_by_phr_lst($db_con->sql_creator(), $phr_lst);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}