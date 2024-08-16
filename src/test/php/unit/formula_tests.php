<?php

/*

  test/unit/formula.php - unit testing of the formula functions
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

include_once MODEL_FORMULA_PATH . 'expression.php';

use api\formula\formula as formula_api;
use api\value\value as value_api;
use api\word\word as word_api;
use cfg\db\sql;
use cfg\db\sql_type;
use cfg\expression;
use cfg\formula;
use cfg\phrase_list;
use cfg\db\sql_db;
use cfg\term_list;
use cfg\word;
use html\formula\formula as formula_dsp;
use test\test_cleanup;

class formula_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $sc = new sql();
        $t->name = 'formula->';
        $t->resource_path = 'db/formula/';

        $t->header('formula unit tests');

        $t->subheader('formula sql setup');
        $frm = $t->formula();
        $t->assert_sql_table_create($frm);
        $t->assert_sql_index_create($frm);
        $t->assert_sql_foreign_key_create($frm);

        $t->subheader('formula sql read');
        $frm = new formula($usr);
        $t->assert_sql_by_id($sc, $frm);
        $t->assert_sql_by_name($sc, $frm);

        $t->subheader('formula sql read default and user changes by id');
        $frm = new formula($usr);
        $frm->set_id(formula_api::TI_READ_ANOTHER);
        $t->assert_sql_standard($sc, $frm);
        $t->assert_sql_not_changed($sc, $frm);
        $t->assert_sql_user_changes($sc, $frm);
        $this->assert_sql_user_changes_frm($t, $frm);

        $t->subheader('formula sql read default by name');
        $frm = new formula($usr);
        $frm->set_name(formula_api::TF_READ_SCALE_MIO);
        $t->assert_sql_standard($sc, $frm);

        $t->subheader('formula sql write insert');
        $frm = $t->formula_name_only();
        $t->assert_sql_insert($sc, $frm);
        $t->assert_sql_insert($sc, $frm, [sql_type::USER]);
        $t->assert_sql_insert($sc, $frm, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $frm, [sql_type::LOG, sql_type::USER]);
        $frm = $t->formula();
        $t->assert_sql_insert($sc, $frm);
        $t->assert_sql_insert($sc, $frm, [sql_type::LOG]);
        $frm = $t->formula_filled();
        $t->assert_sql_insert($sc, $frm, [sql_type::LOG]);

        $t->subheader('formula sql write update');
        $frm = $t->formula_name_only();
        $frm_renamed = $frm->cloned(formula_api::TN_RENAMED);
        $t->assert_sql_update($sc, $frm_renamed, $frm);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::USER]);
        // TODO activate db write with log
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::LOG]);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::LOG, sql_type::USER]);

        $t->subheader('formula sql write delete');
        // TODO activate db write
        $t->assert_sql_delete($sc, $frm);
        $t->assert_sql_delete($sc, $frm, [sql_type::USER]);

        $t->subheader('formula api unit tests');
        $frm = $t->formula_filled();
        $t->assert_api_json($frm);
        $frm->excluded = false;
        $t->assert_api($frm, 'formula_body');

        $t->subheader('formula frontend unit tests');
        $frm = $t->formula();
        $t->assert_api_to_dsp($frm, new formula_dsp());

        $t->subheader('formula im- and export unit tests');
        $json_file = 'unit/formula/scale_second_to_minute.json';
        $t->assert_json_file(new formula($usr), $json_file);

        $t->subheader('Expression tests');

        // get the id of the phrases that should be added to the result based on the formula reference text
        $target = new phrase_list($usr);
        $trm_lst = new term_list($usr);
        $frm = $t->word_one();
        $target->add($frm->phrase());
        $trm_lst->add($frm->term());
        $exp = new expression($usr);
        $exp->set_ref_text('{w' . word_api::TI_ONE . '}={w' . word_api::TI_MIO . '}*1000000', $t->term_list_scale());
        $result = $exp->res_phr_lst($trm_lst);
        $t->assert('Expression->res_phr_lst for ' . formula_api::TF_READ_SCALE_MIO, $result->dsp_id(), $target->dsp_id());

        // get the special formulas used in a formula to calculate the result
        // e.g. "next" is a special formula to get the following values
        /*
        $frm_next = new formula($usr);
        $frm_next->name = "next";
        $frm_next->type_id = $formula_types->id(formula_type::NEXT);
        $frm_next->id = 1;
        $frm_has_next = new formula($usr);
        $frm_has_next->usr_text = '=next';
        $t->assert('Expression->res_phr_lst for ' . formula_api::TF_SCALE_MIO, $result->dsp_id(), $target->dsp_id());
        */

        // test the calculation of one value
        //$phr_lst = $t->phrase_list_for_tests(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_2020, word_api::TN_MIO));
        $trm_lst = $t->term_list_for_tests(array(
            word_api::TN_PCT,
            formula_api::TN_READ_THIS,
            formula_api::TN_READ_PRIOR
        ));
        $phr_lst = $t->phrase_list_increase();

        $frm = $t->increase_formula();
        // TODO activate Prio 1
        // $res_lst = $frm->to_num($phr_lst);
        //$res = $res_lst->lst[0];
        //$result = $res->num_text;
        $target = '=(' . value_api::TV_CH_INHABITANTS_2020_IN_MIO . '-' .
            value_api::TV_CH_INHABITANTS_2019_IN_MIO . ')/' .
            value_api::TV_CH_INHABITANTS_2019_IN_MIO;
        //$t->assert('get numbers for formula ' . $frm->dsp_id() . ' based on term list ' . $trm_lst->dsp_id(), $result, $target);

    }

    /**
     * TODO check the diff to assert_sql_user_changes
     *
     * @param test_cleanup $t the test environment
     * @param formula $frm the user sandbox object e.g. a word
     */
    private function assert_sql_user_changes_frm(test_cleanup $t, formula $frm): void
    {
        $db_con = new sql_db();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm->load_sql_user_changes_frm($db_con);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm->load_sql_user_changes_frm($db_con);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}