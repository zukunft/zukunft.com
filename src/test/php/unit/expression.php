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

use api\formula_api;

class expression_unit_tests
{
    function run(testing $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $t->name = 'expression->';

        $t->header('Unit tests of the formula expression class (src/main/php/model/formula/expression.php)');

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
            formula_api::TF_CANTON_WEIGHT,
            formula_api::TR_CANTON_WEIGHT
        );


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
        $target = 'this ('.$frm_this->id_obj().') / prior ('.$frm_prior->id_obj().') / prior ('.$frm_prior->id_obj().')';
        $t->dsp_contains(', formula_element_group_list->dsp_id', $target, $result);

        $test_name = 'test the user text conversion with a triple';
        $exp = new expression($usr);
        $exp->set_user_text(formula_api::TF_DIAMETER);
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->term_list_for_tests($trm_names);
        $result = $exp->ref_text($trm_lst);
        $target = '={w1}/{t2}';
        $t->assert($test_name, $result, $target);

        $test_name = 'getting phrases that should be added to the result of a formula for "' . $exp->dsp_id() . '"';
        $phr_lst_fv = $exp->fv_phr_lst();
        $result = $phr_lst_fv->dsp_name();
        $target = '"' . word::TN_READ_PERCENT . '"';
        $t->assert($test_name, $result, $target);

        $test_name = 'source phrase list with id from the reference text';
        $exp_sector = new expression($usr);
        $exp_sector->set_ref_text(formula_api::TR_CANTON_WEIGHT);
        $phr_lst = $exp_sector->phr_id_lst_as_phr_lst($exp_sector->r_part());
        $result = $phr_lst->dsp_id();
        $target = '"","","" (1,3,4)';
        $t->assert($test_name, $result, $target);

        $test_name = 'result phrase list with id from the reference text';
        $exp_scale = new expression($usr);
        $exp_scale->set_ref_text(formula_api::TR_SCALE_MIO);
        $phr_lst = $exp_scale->phr_id_lst_as_phr_lst($exp_scale->fv_part());
        $result = $phr_lst->dsp_id();
        $target = '1';
        $t->assert($test_name, $result, $target);

        /*
        $frm = new formula($usr);
        $frm->name = formula_api::TN_SECTOR;
        $frm->ref_text = formula_api::TF_SECTOR_REF;
        $frm->set_ref_text();
        $result = $frm->usr_text;
        $target = '= "' . word::TN_COUNTRY . '" "differentiator" "' . word::TN_CANTON . '" / "' . word::TN_TOTAL . '"';
        $t->assert('expression->is_std if formula is changed by the user', $result, $target);
        */

    }

    /**
     * @param testing $t just the testing object to count the
     * @param string $test_name which part should be tested e.g. with fixed formulas
     * @param string $usr_frm_exp the formula expression in the human-readable format
     * @param string $db_ref_frm_exp the formula expression in the database reference format
     * @return void
     */
    private function frm_exp_convert(
        testing $t,
        string $test_name,
        string $usr_frm_exp,
        string $db_ref_frm_exp
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