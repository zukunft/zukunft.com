<?php

/*

    test/unit/expression.php - unit testing of the expression functions
    --------------------
  

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

use api\formula_api;
use api\word_api;
use model\expression;
use model\library;

class expression_unit_tests
{
    function run(testing $t): void
    {

        global $usr;
        $lib = new library();

        // init
        $t->name = 'expression->';

        $t->header('Unit tests of the formula expression class (src/main/php/model/formula/expression.php)');

        $t->subheader('convert user text to database ref text and the other way round');

        $this->frm_exp_convert($t,
            'including a triple',
            formula_api::TF_DIAMETER,
            formula_api::TR_DIAMETER
        );
        $this->frm_exp_convert($t,
            'including fixed formulas',
            formula_api::TF_INCREASE,
            formula_api::TR_INCREASE
        );
        $this->frm_exp_convert($t,
            'including verbs',
            formula_api::TF_PARTS_IN_PERCENT,
            formula_api::TR_PARTS_IN_PERCENT
        );


        $t->subheader('interface');

        // test the phrase list of the right side
        $test_name = 'get the calc phrases';
        $exp = new expression($usr);
        $exp->set_user_text(formula_api::TF_DIAMETER);
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->term_list_for_tests($trm_names);
        $exp->ref_text($trm_lst);
        $phr_lst = $exp->phr_lst($trm_lst);
        $result = $phr_lst->dsp_id();
        $target = '"' . formula_api::TN_PI . '","' . formula_api::TN_CIRCUMFERENCE . '" (-2,1)';
        $t->assert($test_name, $result, $target);

        // test the phrase list of the left side
        $test_name = 'get the result phrases';
        $exp = new expression($usr);
        $exp->set_user_text(formula_api::TF_INCREASE);
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->term_list_for_tests($trm_names);
        $exp->ref_text($trm_lst);
        $phr_lst = $exp->res_phr_lst($trm_lst);
        $result = $phr_lst->dsp_id();
        $target = '"' . formula_api::TN_PERCENT . '" (1)';
        $t->assert($test_name, $result, $target);

        // the phrase list for the calc part should be empty, because it contains only formulas
        $phr_lst = $exp->phr_lst($trm_lst);
        $result = $phr_lst->dsp_id();
        $target = 'null';
        $t->assert($test_name, $result, $target);

        // test the element group list of the right side
        // TODO check with cantons of switzerland
        // TODO check if adjustment overwrite from some parts works
        //      e.g. if the total needs to be adjusted, because
        //      the sum of tax payers of all cantons can be higher than
        //      the total number of tax payers in Switzerland
        //      because one person can be tax payer in more than one Canton
        $test_name = 'get the formula element group list';
        $exp = new expression($usr);
        $exp->set_user_text(formula_api::TF_PARTS_IN_PERCENT);
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->term_list_for_tests($trm_names);
        $exp->ref_text($trm_lst);
        $elm_grp_lst = $exp->element_grp_lst($trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = '"parts,of" (2,3) / "total" (4)';
        //$target = '"' . formula_api::TN_PERCENT . '" (1)';
        $t->assert($test_name, $result, $target);

        // test the element list of the right side
        $elm_grp_lst = $exp->element_list($trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = 'parts (2) / of (3) / total (4)';
        //$target = '"' . formula_api::TN_PERCENT . '" (1)';
        $t->assert($test_name, $result, $target);

        // tests based on the increase formula
        $test_name = 'test the conversion of the user text to the database reference text with fixed formulas';
        $exp = new expression($usr);
        $exp->set_user_text(formula_api::TF_INCREASE);
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->term_list_for_tests($trm_names);
        $result = $exp->ref_text($trm_lst);
        $target = formula_api::TR_INCREASE;
        $t->assert($test_name, $result, $target);

        $test_name = 'test getting the phrase ids';
        $result = implode(",", $exp->phr_id_lst($exp->ref_text())->lst);
        $target = implode(",", array(1));
        $t->assert($test_name, $result, $target);

        $test_name = 'test the conversion of the database reference text to the user text';
        $result = $exp->user_text($trm_lst);
        $target = formula_api::TF_INCREASE;
        $t->assert($test_name, $result, $target);

        $test_name = 'test the formula element list';
        $elm_lst = $exp->element_list($trm_lst);
        $result = $elm_lst->dsp_id();
        $target = 'this (18) / prior (20) / prior (20)';
        $t->assert($test_name, $result, $target);

        // element_special_following_frm
        $phr_lst = $exp->element_special_following($trm_lst);
        $result = $phr_lst->dsp_name();
        $target = '"time_prior","time_this"';
        $t->assert('element_special_following for "'.$exp->dsp_id().'"', $result, $target, TIMEOUT_LIMIT_LONG);

        $test_name = 'test the formula element group creation';
        $elm_grp_lst = $exp->element_grp_lst($trm_lst);

        // create the formulas for testing
        $frm_this = $trm_lst->get_by_name(formula_api::TN_READ_THIS);
        $frm_prior = $trm_lst->get_by_name(formula_api::TN_READ_PRIOR);

        $result = $elm_grp_lst->dsp_id();
        $target = '"this" ('.$frm_this->id_obj().') / "prior" ('.$frm_prior->id_obj().') / "prior" ('.$frm_prior->id_obj().')';
        $t->dsp_contains($test_name, $target, $result);

        $test_name = 'getting phrases that should be added to the result of a formula for "' . $exp->dsp_id() . '"';
        $phr_lst_res = $exp->res_phr_lst($trm_lst);
        $result = $phr_lst_res->dsp_name();
        $target = '"' . word_api::TN_PCT . '"';
        $t->assert($test_name, $result, $target);

        // tests based on the pi formula
        $test_name = 'test the user text conversion with a triple';
        $exp = new expression($usr);
        $exp->set_user_text(formula_api::TF_DIAMETER);
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->term_list_for_tests($trm_names);
        $result = $exp->ref_text($trm_lst);
        $target = '={w1}/{t2}';
        $t->assert($test_name, $result, $target);

        $test_name = 'source phrase list with id from the reference text';
        $exp_sector = new expression($usr);
        $exp_sector->set_ref_text(formula_api::TR_PARTS_IN_PERCENT);
        $phr_lst = $exp_sector->phr_id_lst_as_phr_lst($exp_sector->r_part());
        $result = $phr_lst->dsp_id();
        $target = '"","" (2,4)';
        $t->assert($test_name, $result, $target);

        $test_name = 'result phrase list with id from the reference text';
        $exp_scale = new expression($usr);
        $exp_scale->set_ref_text(formula_api::TR_SCALE_MIO);
        $phr_lst = $exp_scale->phr_id_lst_as_phr_lst($exp_scale->res_part());
        $result = $phr_lst->dsp_id();
        $target = '1';
        $t->assert($test_name, $result, $target);

    }

    /**
     * @param testing $t just the testing object to count the number of errors and warnings
     * @param string $test_name which part should be tested e.g. with fixed formulas
     * @param string $usr_frm_exp the formula expression in the human-readable format
     * @param string $db_ref_frm_exp the formula expression in the database reference format
     * @return void
     */
    private function frm_exp_convert(
        testing $t,
        string  $test_name,
        string  $usr_frm_exp,
        string  $db_ref_frm_exp
    ): void
    {
        global $usr;

        $test_name = 'conversion of the user text to the database reference text ' . $test_name;
        $exp = new expression($usr);
        $exp->set_user_text($usr_frm_exp);
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->term_list_for_tests($trm_names);
        $result = $exp->ref_text($trm_lst);
        $target = $db_ref_frm_exp;
        $t->assert($test_name, $result, $target);

        $test_name = 'conversion of the database reference text to the user text ' . $test_name;
        $result = $exp->user_text($trm_lst);
        $target = $usr_frm_exp;
        $t->assert($test_name, $result, $target);
    }

}