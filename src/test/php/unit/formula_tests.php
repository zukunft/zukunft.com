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
include_once WEB_ELEMENT_PATH . 'element_group.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\formula\expression;
use cfg\formula\formula;
use cfg\phrase\phrase_list;
use cfg\phrase\term_list;
use cfg\word\word;
use html\element\element_group as element_group_dsp;
use html\formula\formula as formula_dsp;
use html\phrase\term_list as term_list_dsp;
use shared\const\formulas;
use shared\const\values;
use shared\const\words;
use shared\library;
use test\test_cleanup;

class formula_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $lib = new library();
        $sc = new sql_creator();
        $t->name = 'formula->';
        $t->resource_path = 'db/formula/';

        // start the test section (ts)
        $ts = 'unit formula ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $frm = $t->formula();
        $t->assert_sql_table_create($frm);
        $t->assert_sql_index_create($frm);
        $t->assert_sql_foreign_key_create($frm);

        $t->subheader($ts . 'sql read');
        $frm = new formula($usr);
        $t->assert_sql_by_id($sc, $frm);
        $t->assert_sql_by_name($sc, $frm);

        $t->subheader($ts . 'sql read default and user changes by id');
        $frm = new formula($usr);
        $frm->set_id(formulas::SCALE_HOUR_ID);
        $t->assert_sql_standard($sc, $frm);
        $t->assert_sql_not_changed($sc, $frm);
        $t->assert_sql_user_changes($sc, $frm);
        $this->assert_sql_user_changes_frm($t, $frm);

        $t->subheader($ts . 'sql read default by name');
        $frm = new formula($usr);
        $frm->set_name(formulas::SCALE_MIO_EXP);
        $t->assert_sql_standard($sc, $frm);

        $t->subheader($ts . 'sql write insert');
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

        $t->subheader($ts . 'sql write update');
        $frm = $t->formula_name_only();
        $frm_renamed = $frm->cloned(formulas::SYSTEM_TEST_RENAMED);
        $t->assert_sql_update($sc, $frm_renamed, $frm);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::USER]);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::LOG]);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $frm);
        $t->assert_sql_delete($sc, $frm, [sql_type::USER]);
        $t->assert_sql_delete($sc, $frm, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $frm, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'base object handling');
        $frm = $t->formula_filled();
        $t->assert_reset($frm);

        $t->subheader($ts . 'api');
        $frm = $t->formula_filled();
        $t->assert_api_json($frm);
        $frm->include();
        $t->assert_api($frm, 'formula_body');

        $t->subheader($ts . 'frontend');
        $frm = $t->formula();
        $t->assert_api_to_dsp($frm, new formula_dsp());

        $t->subheader($ts . 'im- and export');
        $t->assert_ex_and_import($t->formula(), $usr_sys);
        $t->assert_ex_and_import($t->formula_filled(), $usr_sys);
        $json_file = 'unit/formula/scale_second_to_minute.json';
        $t->assert_json_file(new formula($usr), $json_file);

        $t->subheader($ts . 'expression');

        $test_name = 'formula increase expression';
        $frm = $t->formula_increase();
        $frm_this = $t->formula_this();
        $frm_prior = $t->formula_prior();
        $wrd_pct = $t->word_percent();
        $trm_lst = $t->term_list_increase();

        // build the expression, which is in this case "percent" = ( "this" - "prior" ) / "prior"
        $exp = $frm->expression($trm_lst);

        $result = $exp->dsp_id();
        $target = '""' . words::PERCENT . '" = ( "'
            . words::THIS_NAME . '" - "'
            . words::PRIOR_NAME . '" ) / "'
            . words::PRIOR_NAME . '"" ({w'
            . $wrd_pct->id() . '}=({f'
            . $frm_this->id() . '}-{f'
            . $frm_prior->id() . '})/{f'
            . $frm_prior->id() . '})';
        $t->assert($test_name . ' for ' . $frm->dsp_id(), $result, $target);

        // build the element group list which is in this case "this" and "prior", but an element group can contain more than one word
        $test_name = 'formula increase: test the element group creation';
        $elm_grp_lst = $exp->element_grp_lst($trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = '"'
            . formulas::THIS_NAME . '" ('
            . $frm_this->id() . ') / "'
            . formulas::PRIOR . '" ('
            . $frm_prior->id() . ') / "'
            . formulas::PRIOR . '" ('
            . $frm_prior->id() . ')';
        $t->dsp_contains($test_name, $target, $result);

        $test_name = 'formula increase; test the display name that can be used for user debugging';
        $frm_html = new formula_dsp($frm->api_json());
        $trm_lst_dsp = new term_list_dsp($trm_lst->api_json());
        $back = 0;
        $result = $frm_html->dsp_text($back, $trm_lst_dsp);
        $target = '"' . words::PERCENT
            . '" = ( <a href="/http/formula_edit.php?id='
            . $frm_this->id() . '&back=0" title="'
            . words::THIS_NAME . '">'
            . words::THIS_NAME
            . '</a> - <a href="/http/formula_edit.php?id='
            . $frm_prior->id()
            . '&back=0" title=<a href="/http/formula_edit.php?id=20&back=0" title="'
            . words::PRIOR_NAME . '">'
            . words::PRIOR_NAME . '</a>>'
            . words::PRIOR_NAME
            . '</a> ) / <a href="/http/formula_edit.php?id=20&back=0" title=<a href="/http/formula_edit.php?id='
            . $frm_prior->id() . '&back=0" title="'
            . words::PRIOR_NAME . '">'
            . words::PRIOR_NAME . '</a>>'
            . words::PRIOR_NAME . '</a>';
        $t->assert($test_name, $result, $target);

        // define the element group object to retrieve the value
        // test the display name that can be used for user debugging
        if (count($elm_grp_lst->lst()) > 0) {
            // get "this" from the formula element group list
            $elm_grp = $elm_grp_lst->lst()[0];
            $elm_grp_dsp = new element_group_dsp($elm_grp->api_json());
            $result = $elm_grp_dsp->dsp_names();
            $target = '<a href="/http/formula_edit.php?id='
                . $frm_this->id() . '" title="'
                . words::THIS_NAME . '">'
                . words::THIS_NAME . '</a>';
            $t->display('element_group->dsp_names', trim($target), trim($result));
        }
        /*
        if (count($elm_grp_lst->lst()) > 0) {
            // get "this" from the formula element group list
            $elm_grp = $elm_grp_lst->lst()[0];
            $fig_lst = $elm_grp->figures($trm_lst);

            $test_name = 'formula increase; test if the values for an element group are displayed correctly';
            $frm_html = new formula_dsp($frm->api_json());
            $trm_lst_dsp = new term_list_dsp($trm_lst->api_json());
            $back = 0;
            $result = $frm_html->dsp_text($back, $trm_lst_dsp);
            $target = '<a href="/http/result_edit.php?id=' . $fig_lst->get_first_id() . '" title="8.51">8.51</a>';
            $t->assert($test_name, $result, $target);
        }
        */


        // TODO activate
        //$t->assert_true($ts . 'with at least one predefined formula', $t->formula_increase()->is_special());
        $t->assert_false($ts . 'without predefined formula', $t->formula()->is_special());

        // get the id of the phrases that should be added to the result based on the formula reference text
        $target = new phrase_list($usr);
        $trm_lst = new term_list($usr);
        $frm = $t->word_one();
        $target->add($frm->phrase());
        $trm_lst->add($frm->term());
        $exp = new expression($usr);
        $exp->set_ref_text('{w' . words::ONE_ID . '}={w' . words::MIO_ID . '}*1000000', $t->term_list_scale());
        $result = $exp->result_phrases($trm_lst);
        $t->assert('Expression->res_phr_lst for ' . formulas::SCALE_MIO_EXP, $result->dsp_id(), $target->dsp_id());

        // get the special formulas used in a formula to calculate the result
        // e.g. "next" is a special formula to get the following values
        /*
        $frm_next = new formula($usr);
        $frm_next->name = "next";
        $frm_next->type_id = $frm_typ_cac->id(formula_type::NEXT);
        $frm_next->id = 1;
        $frm_has_next = new formula($usr);
        $frm_has_next->usr_text = '=next';
        $t->assert('Expression->res_phr_lst for ' . formulas::TF_SCALE_MIO, $result->dsp_id(), $target->dsp_id());
        */

        $test_name = 'formula term list';
        $frm = $t->formula();
        $trm_lst = $frm->term_list($t->term_list_time());
        $t->assert($test_name, $trm_lst->dsp_id(),
            '"' . words::MINUTE . '","' . words::SECOND . '" ('
            . $lib->term_id(words::SECOND_ID, word::class) . ','
            . $lib->term_id(words::MINUTE_ID, word::class) . ')');

        // TODO add result display test

        // test the calculation of one value
        $trm_lst = $t->term_list_for_tests(array(
            words::PCT,
            formulas::THIS_NAME,
            formulas::PRIOR
        ));
        $phr_lst = $t->phrase_list_increase();

        $frm = $t->formula_increase();
        // TODO activate Prio 1
        // $res_lst = $frm->to_num($phr_lst);
        //$res = $res_lst->lst[0];
        //$result = $res->num_text;
        $target = '=(' . values::CH_INHABITANTS_2020_IN_MIO . '-' .
            values::CH_INHABITANTS_2019_IN_MIO . ')/' .
            values::CH_INHABITANTS_2019_IN_MIO;
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