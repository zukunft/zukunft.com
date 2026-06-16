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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_FORMULA . 'formula_list.php';
include_once html_paths::FORMULA . 'formula_list.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list as formula_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

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
        $sc = new sql_creator();
        $t_frm = new test_formulas($t);
        $t->name = 'formula_list->';
        $t->resource_path = 'db/formula/';

        // start the test section (ts)
        $ts = 'unit formula list ';
        $t->header($ts);

        $t->subheader($ts . 'sql statement creation');

        // load only the names
        $frm_lst = new formula_list($usr);
        $t->assert_sql_names($sc, $frm_lst, new formula($usr));
        $t->assert_sql_names($sc, $frm_lst, new formula($usr), formula_names::SCALE_TO_SEC);

        // sql to load a list of formulas by the id, name or ...
        $frm_lst = new formula_list($usr);
        $test_name = 'load formulas by ids';
        $t->assert_sql_by_ids($test_name, $sc, $frm_lst);
        $t->assert_sql_by_names($sc, $frm_lst, array(formula_names::INCREASE, formula_names::INCREASE));
        $t->assert_sql_like($sc, $frm_lst, 'i');
        $t->assert_sql_all_paged($db_con, $frm_lst);
        $this->assert_sql_by_word_ref($t, $db_con, $frm_lst);
        $this->assert_sql_by_triple_ref($t, $db_con, $frm_lst);
        $this->assert_sql_by_verb_ref($t, $db_con, $frm_lst);
        $this->assert_sql_by_formula_ref($t, $db_con, $frm_lst);
        $this->assert_sql_by_phr($t, $db_con, $frm_lst);
        $this->assert_sql_by_phr_lst($t, $db_con, $frm_lst);
        // TODO Prio 2 activate
        //$t->assert_sql_all($db_con, $frm);


        $t->subheader($ts . 'api');

        $frm_lst = $t_frm->formula_list_short();
        $t->assert_api($frm_lst);


        $t->subheader($ts . 'im- and export');
        $json_file = 'unit/formula/formula_list.json';
        $t->assert_json_file(new formula_list($usr), $json_file);


        $t->subheader($ts . 'html frontend');

        $trp_lst = $t_frm->formula_list_short();
        $t->assert_api_to_ui($trp_lst, new formula_list_ui());

        // an api error message keeps the frontend list empty and logs a warning on purpose
        // instead of stopping the rendering of the page
        $test_name = 'an api error message is not mapped to the list';
        $frm_lst = new formula_list_ui();
        $frm_lst->api_mapper(['message' => 'the phrase id is missing']);
        $t->assert($test_name, $frm_lst->count(), 0);

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
        $wrd->id = 1;

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
        $trp->id = 1;

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
        $vrb->id = 1;

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
        $frm->id = 1;

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
        $wrd->set(words::DEFAULT_WORD_ID,word_names::TEST_ADD);
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