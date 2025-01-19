<?php

/*

    test/unit/result.php - unit testing of the FORMULA VALUE functions
    ---------------------------
  

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

include_once API_RESULT_PATH . 'result.php';

use api\result\result as result_api;
use api\word\word as word_api;
use cfg\db\sql_creator;
use cfg\db\sql_type;
use cfg\formula\formula;
use cfg\group\group;
use cfg\group\group_list;
use cfg\phrase\phrase_list;
use cfg\db\sql_db;
use cfg\result\result;
use html\result\result as result_dsp;
use test\test_cleanup;

class result_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'result->';
        $t->resource_path = 'db/result/';


        $t->header('Unit tests of the result class (src/main/php/model/formula/result.php)');

        $t->subheader('SQL creation tests');
        $res = $t->result_simple();
        $t->assert_sql_table_create($res);
        $t->assert_sql_index_create($res);
        $t->assert_sql_foreign_key_create($res);

        // check the sql to load a result by the id
        $res_prime = $t->result_prime();
        $res = $t->result();
        $res_main = $t->result_main();
        $res_big = $t->result_big();
        $t->assert_sql_by_id($sc, $res_prime);
        $t->assert_sql_by_id($sc, $res);
        $t->assert_sql_by_id($sc, $res_main);
        $t->assert_sql_by_id($sc, $res_big);
        $this->assert_sql_by_group($t, $db_con, $res_prime);
        $this->assert_sql_by_group($t, $db_con, $res);
        $this->assert_sql_by_formula_and_group($t, $db_con, $res);
        $this->assert_sql_by_formula_and_group_list($t, $db_con, $res);
        $this->assert_sql_load_std_by_group_id($t, $db_con, $res);


        $t->subheader('SQL load default statement tests');

        // sql to load the standard result by id
        $t->assert_sql_standard($sc, $res_prime);
        $t->assert_sql_user_changes($sc, $res_prime);

        $t->subheader('result sql write');
        // result changes are not logged because potentially they can be reproduced
        // TODO check the move from prime and main if the source group does not fit the prime or main criterias (same for the formula id)
        $res_prime = $t->result_prime();
        $res_prime_max = $t->result_prime_max();
        $res_main = $t->result_main();
        $res_main_max = $t->result_main_max();
        $res_filled = $t->result_main_filled();
        $res = $t->result();
        $res_big = $t->result_big();
        // TODO activate db write
        $t->assert_sql_insert($sc, $res_prime, [sql_type::STANDARD]);
        $t->assert_sql_insert($sc, $res_prime);
        $t->assert_sql_insert($sc, $res_prime, [sql_type::USER]);
        $t->assert_sql_insert($sc, $res_prime_max);
        $t->assert_sql_insert($sc, $res_main);
        $t->assert_sql_insert($sc, $res_main_max);
        $t->assert_sql_insert($sc, $res_filled);
        $t->assert_sql_insert($sc, $res_filled, [sql_type::USER]);
        $t->assert_sql_insert($sc, $res);
        $t->assert_sql_insert($sc, $res, [sql_type::USER]);
        $t->assert_sql_insert($sc, $res_big);
        // TODO activate db write
        // TODO add tests for text, time and geo values
        $db_res_prime = $res_prime->cloned(result_api::TV_FLOAT);
        $db_res_prime_max = $res_prime_max->cloned(result_api::TV_FLOAT);
        $db_res_main = $res_main->cloned(result_api::TV_FLOAT);
        $db_res_filled = $res_filled->cloned(result_api::TV_FLOAT);
        $db_res = $res->cloned(result_api::TV_FLOAT);
        $db_res_big = $res_big->cloned(result_api::TV_FLOAT);
        // TODO activate db write
        $t->assert_sql_update($sc, $res_prime, $db_res_prime, [sql_type::STANDARD]);
        $t->assert_sql_update($sc, $res_prime, $db_res_prime);
        $t->assert_sql_update($sc, $res_prime, $db_res_prime, [sql_type::USER]);
        $t->assert_sql_update($sc, $res_prime_max, $db_res_prime_max);
        $t->assert_sql_update($sc, $res_main, $db_res_main);
        $t->assert_sql_update($sc, $res_main, $db_res_main, [sql_type::STANDARD]);
        $t->assert_sql_update($sc, $res_filled, $db_res_filled);
        $t->assert_sql_update($sc, $res, $db_res);
        $t->assert_sql_update($sc, $res_big, $db_res_big);
        $t->assert_sql_update($sc, $res_big, $db_res_big, [sql_type::USER]);
        // TODO activate db write
        $t->assert_sql_delete($sc, $res_prime);
        $t->assert_sql_delete($sc, $res_prime, [sql_type::USER]);
        $t->assert_sql_delete($sc, $res);
        $t->assert_sql_delete($sc, $res, [sql_type::USER]);

        $t->subheader('result base object handling');
        $res = $t->result_main_filled();
        $t->assert_reset($res);


        $t->subheader('Display tests');

        // test phrase based default formatter
        // ... for big values
        $wrd_const = $t->new_word(word_api::TN_READ);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($wrd_const->phrase());
        $res->grp()->set_phrase_list($phr_lst);
        $res->set_number(result_api::TV_INT);
        $t->assert('result->val_formatted test big numbers', $res->val_formatted(), "123'456");

        // ... for small values 12.35 instead of 12.34 due to rounding
        $res->set_number(result_api::TV_FLOAT);
        $t->assert('result->val_formatted test small numbers', $res->val_formatted(), "12.35");

        // ... for percent values
        $res = $t->result_pct();
        $t->assert('result->val_formatted test percent formatting', $res->val_formatted(), '1.23 %');


        $t->subheader('Im- and Export tests');
        $t->assert_ex_and_import($t->result());
        $t->assert_ex_and_import($t->result_main_filled());
        $json_file = 'unit/result/result_import_part.json';
        $t->assert_json_file(new result($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $res = $t->result_simple();
        $t->assert_api_to_dsp($res, new result_dsp());

    }

    /**
     * check the SQL statements creation to get the results by the phrase group
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param result $res the user sandbox object e.g. a result
     * @return void true if all tests are fine
     */
    private function assert_sql_by_group(test_cleanup $t, sql_db $db_con, result $res): void
    {
        // prepare
        $grp = $res->grp();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_by_grp($db_con->sql_creator(), $grp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $res->load_sql_by_grp($db_con->sql_creator(), $grp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the SQL statements creation to get the results
     * by the formula and phrase group
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param result $res the user sandbox object e.g. a result
     * @return void true if all tests are fine
     */
    private function assert_sql_by_formula_and_group(test_cleanup $t, sql_db $db_con, result $res): void
    {
        // prepare
        $frm = new formula($t->usr1);
        $frm->set_id(2);
        $grp = new group($t->usr1);
        $grp->set_id(3);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_by_frm_grp($db_con->sql_creator(), $frm, $grp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $res->load_sql_by_frm_grp($db_con->sql_creator(), $frm, $grp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the SQL statements creation to get the results
     * by the formula and phrase group
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param result $res the user sandbox object e.g. a result
     * @return void true if all tests are fine
     */
    private function assert_sql_by_formula_and_group_list(test_cleanup $t, sql_db $db_con, result $res): void
    {
        // prepare
        $frm = new formula($t->usr1);
        $frm->set_id(2);
        $grp1 = new group($t->usr1);
        $grp1->set_id(3);
        $grp2 = new group($t->usr1);
        $grp2->set_id(4);
        $lst = new group_list($t->usr1);
        $lst->add($grp1);
        $lst->add($grp2);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_by_frm_grp_lst($db_con->sql_creator(), $frm, $lst);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $res->load_sql_by_frm_grp_lst($db_con->sql_creator(), $frm, $lst);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * similar to $t->assert_sql_all but calling load_by_group_id_sql instead of load_sql
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param result $res the user sandbox object e.g. a result
     */
    private function assert_sql_load_std_by_group_id(
        test_cleanup $t,
        sql_db $db_con,
        result $res): void
    {
        $grp = new group($t->usr1);
        $grp->set_id(7);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_std_by_grp($db_con->sql_creator(), $grp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $res->load_sql_std_by_grp($db_con->sql_creator(), $grp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}