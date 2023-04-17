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

namespace test;

include_once MODEL_FORMULA_PATH . 'expression.php';

use api\formula_api;
use api\value_api;
use api\word_api;
use cfg\formula_type;
use model\expression;
use model\formula;
use model\phrase_list;
use model\sql_db;
use model\term_list;
use model\word;

class formula_unit_tests
{
    function run(testing $t): void
    {

        global $usr;
        global $formula_types;

        // init
        $db_con = new sql_db();
        $t->name = 'formula->';
        $t->resource_path = 'db/formula/';
        $json_file = 'unit/formula/scale_second_to_minute.json';
        $usr->set_id(1);

        $t->header('Unit tests of the formula class (src/main/php/model/formula/formula.php)');


        $t->subheader('SQL user sandbox statement tests');

        $frm = new formula($usr);
        $t->assert_load_sql_id($db_con, $frm);
        $t->assert_load_sql_name($db_con, $frm);


        $t->subheader('SQL statement tests');

        // sql to load the formula by id
        $frm = new formula($usr);
        $frm->set_id(2);
        //$t->assert_load_sql($db_con, $frm);
        $t->assert_load_standard_sql($db_con, $frm);
        $t->assert_not_changed_sql($db_con, $frm);
        $t->assert_user_config_sql($db_con, $frm);

        // sql to load the formula by name
        $frm = new formula($usr);
        $frm->set_name(formula_api::TF_READ_SCALE_MIO);
        //$t->assert_load_sql($db_con, $frm);
        $t->assert_load_standard_sql($db_con, $frm);


        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm->load_user_sql($db_con);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $frm->load_user_sql($db_con);
        $t->assert_qp($qp, sql_db::MYSQL);


        $t->subheader('Convert tests');

        // casting API
        $frm = new formula($usr);
        $frm->set(1, formula_api::TN_READ, formula_type::CALC);
        $t->assert_api($frm);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new formula($usr), $json_file);


        $t->subheader('Expression tests');

        // get the id of the phrases that should be added to the result based on the formula reference text
        $target = new phrase_list($usr);
        $trm_lst = new term_list($usr);
        $wrd = new word($usr);
        $wrd->set_id(205);
        $target->add($wrd->phrase());
        $trm_lst->add($wrd->term());
        $exp = new expression($usr);
        $exp->set_ref_text('{w205}={w203}*1000000');
        $result = $exp->fv_phr_lst($trm_lst);
        $t->assert('Expression->fv_phr_lst for ' . formula_api::TF_READ_SCALE_MIO, $result->dsp_id(), $target->dsp_id());

        // get the special formulas used in a formula to calculate the result
        // e.g. "next" is a special formula to get the following values
        /*
        $frm_next = new formula($usr);
        $frm_next->name = "next";
        $frm_next->type_id = $formula_types->id(formula_type::NEXT);
        $frm_next->id = 1;
        $frm_has_next = new formula($usr);
        $frm_has_next->usr_text = '=next';
        $t->assert('Expression->fv_phr_lst for ' . formula_api::TF_SCALE_MIO, $result->dsp_id(), $target->dsp_id());
        */

        // test the calculation of one value
        //$phr_lst = $t->phrase_list_for_tests(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_2020, word_api::TN_MIO));
        $trm_lst = $t->term_list_for_tests(array(
            word_api::TN_PCT,
            formula_api::TN_READ_THIS,
            formula_api::TN_READ_PRIOR
        ));
        $phr_lst = $t->phrase_list_for_tests(array(
            word_api::TN_PCT,
            formula_api::TN_READ_THIS,
            formula_api::TN_READ_PRIOR,
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_2020,
            word_api::TN_MIO
        ));

        $frm = $t->new_formula(formula_api::TN_ADD, 1);
        $frm->set_user_text(formula_api::TF_INCREASE, $trm_lst);
        $fv_lst = $frm->to_num($phr_lst);
        // TODO activate
        //$fv = $fv_lst->lst[0];
        //$result = $fv->num_text;
        $target = '=(' . value_api::TV_CH_INHABITANTS_2020_IN_MIO . '-' .
            value_api::TV_CH_INHABITANTS_2019_IN_MIO . ')/' .
            value_api::TV_CH_INHABITANTS_2019_IN_MIO;
        //$t->assert('get numbers for formula ' . $frm->dsp_id() . ' based on term list ' . $trm_lst->dsp_id(), $result, $target);

    }

}