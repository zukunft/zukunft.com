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

namespace unit;

include_once SHARED_CONST_PATH . 'formulas.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_CONST_PATH . 'words.php';

use cfg\formula\expression;
use cfg\phrase\term_list;
use shared\library;
use shared\const\formulas;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class expression_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        $lib = new library();

        // init
        $t->name = 'expression->';
        $trm_lst = $t->term_list_all();

        $t->header('Unit tests of the formula expression class (src/main/php/model/formula/expression.php)');

        $t->subheader('convert user text to database ref text and the other way round');

        $this->frm_exp_convert($t,
            'including a triple',
            formulas::DIAMETER,
            formulas::DIAMETER_DB,
            $trm_lst
        );
        $this->frm_exp_convert($t,
            'including fixed formulas',
            formulas::INCREASE_EXP,
            formulas::INCREASE_DB,
            $trm_lst
        );
        $this->frm_exp_convert($t,
            'including verbs',
            formulas::PARTS_IN_PERCENT_EXP,
            formulas::PARTS_IN_PERCENT_DB,
            $trm_lst
        );


        $t->subheader('interface');

        // test the phrase list of the right side

        $test_name = 'get the calc phrases';
        $exp = new expression($usr);
        $exp->set_user_text(formulas::DIAMETER, $trm_lst);
        //$trm_names = $exp->get_usr_names();
        //$trm_lst = $t->term_list_for_tests($trm_names);
        $exp->ref_text($trm_lst);
        $phr_lst = $exp->phr_lst($trm_lst);
        $result = $phr_lst->dsp_id();
        $target = '"' . words::PI . '","' . words::CIRCUMFERENCE
            . '" (phrase_id ' . words::PI_ID . ',' . words::CIRCUMFERENCE_ID . ') for user 1 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // test the phrase list of the left side
        $test_name = 'get the result phrases';
        $exp = new expression($usr);
        $exp->set_user_text(formulas::INCREASE_EXP, $trm_lst);
        $exp->ref_text($trm_lst);
        $phr_lst = $exp->res_phr_lst($trm_lst);
        $result = $phr_lst->dsp_id();
        $target = '"' . formulas::PERCENT
            . '" (phrase_id ' . words::PCT_ID . ') for user 1 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // the phrase list for the calc part should be empty, because it contains only formulas
        $trm_names = $exp->get_usr_names();
        $trm_lst_rev = $t->term_list_for_tests($trm_names);
        $phr_lst = $exp->phr_lst($trm_lst_rev);
        $result = $phr_lst->dsp_id();
        $target = '';
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
        $exp->set_user_text(formulas::PARTS_IN_PERCENT_EXP, $trm_lst);
        $trm_names = $exp->get_usr_names();
        $exp->ref_text($trm_lst);
        $elm_grp_lst = $exp->element_grp_lst($trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = '"parts,of" (' . words::PARTS_ID . ',' . verbs::WITH_ID . ') / "total" (' . words::TOTAL_ID
            . ') for user 1 (zukunft.com system test)';
        //$target = '"' . formulas::TN_PERCENT . '" (1)';
        $t->assert($test_name, $result, $target);

        // test the element list of the right side
        $elm_grp_lst = $exp->element_list($trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = '"parts","of","total" (element_id '
            . words::PARTS_ID . ',' . verbs::WITH_ID . ',' . words::TOTAL_ID
            . ') for user 1 (zukunft.com system test)';
        //$target = '"' . formulas::TN_PERCENT . '" (1)';
        $t->assert($test_name, $result, $target);

        // tests based on the increase formula
        $test_name = 'test the conversion of the user text to the database reference text with fixed formulas';
        $exp = new expression($usr);
        $exp->set_user_text(formulas::INCREASE_EXP, $trm_lst);
        $result = $exp->ref_text($trm_lst);
        $target = formulas::INCREASE_DB;
        $t->assert($test_name, $result, $target);

        $test_name = 'test getting the phrase ids';
        $result = implode(",", $exp->phr_id_lst($exp->ref_text())->lst);
        $target = implode(",", array(words::PCT_ID, words::THIS_ID, words::PRIOR_ID));
        $t->assert($test_name, $result, $target);

        $test_name = 'test the conversion of the database reference text to the user text';
        $result = $exp->user_text($trm_lst);
        $target = formulas::INCREASE_EXP;
        $t->assert($test_name, $result, $target);

        $test_name = 'test the formula element list';
        $elm_lst = $exp->element_list($trm_lst);
        $result = $elm_lst->dsp_id();
        $target = '"this","' . words::PRIOR_NAME . '","' . words::PRIOR_NAME . '" (element_id '
            . words::THIS_ID . ',' . words::PRIOR_ID . ',' . words::PRIOR_ID
            . ') for user 1 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // element_special_following
        $phr_lst = $exp->element_special_following($trm_lst);
        $result = $phr_lst->dsp_name();
        $target = '"' . words::THIS_NAME . '","' . words::PRIOR_NAME . '"';
        $t->assert('element_special_following for "' . $exp->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_LONG);

        // TODO element_special_following_frm
        //$target = '"time_prior","time_this"';

        $test_name = 'test the formula element group creation';
        $elm_grp_lst = $exp->element_grp_lst($trm_lst);

        // create the formulas for testing
        $frm_this = $trm_lst->get_by_name(formulas::THIS_NAME);
        $frm_prior = $trm_lst->get_by_name(formulas::PRIOR);

        $result = $elm_grp_lst->dsp_id();
        $target = '"' . words::THIS_NAME . '" (' . $frm_this->id_obj() . ') / "' . words::PRIOR_NAME . '" (' . $frm_prior->id_obj() . ') / "' . words::PRIOR_NAME . '" ('
            . $frm_prior->id_obj() . ')';
        $t->dsp_contains($test_name, $target, $result);

        $test_name = 'getting phrases that should be added to the result of a formula for "' . $exp->dsp_id() . '"';
        $phr_lst_res = $exp->res_phr_lst($trm_lst);
        $result = $phr_lst_res->dsp_name();
        $target = '"' . words::PCT . '"';
        $t->assert($test_name, $result, $target);

        // tests based on the pi formula
        $test_name = 'test the user text conversion with a triple';
        $exp = new expression($usr);
        $exp->set_user_text(formulas::DIAMETER, $t->term_list_all());
        $trm_names = $exp->get_usr_names();
        $trm_lst_rev = $t->term_list_for_tests($trm_names);
        $result = $exp->ref_text($trm_lst_rev);
        $target = '={w' . words::CIRCUMFERENCE_ID . '}/{w' . words::PI_ID . '}';
        $t->assert($test_name, $result, $target);

        $test_name = 'source phrase list with id from the reference text';
        $exp_sector = new expression($usr);
        $exp_sector->set_ref_text(formulas::PARTS_IN_PERCENT_DB, $trm_lst);
        $phr_lst = $exp_sector->phr_id_lst_as_phr_lst($exp_sector->r_part());
        $result = $phr_lst->dsp_id();
        $target = '"","" (phrase_id ' . words::PARTS_ID . ',' . words::TOTAL_ID
            . ') for user 1 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        $test_name = 'result phrase list with id from the reference text';
        $exp_scale = new expression($usr);
        $exp_scale->set_ref_text(formulas::SCALE_MIO_DB, $trm_lst);
        $phr_lst = $exp_scale->phr_id_lst_as_phr_lst($exp_scale->res_part());
        $result = $phr_lst->dsp_id();
        $target = 'phrase_id ' . words::ONE_ID . ' for user 1 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

    }

    /**
     * @param test_cleanup $t just the testing object to count the number of errors and warnings
     * @param string $test_name which part should be tested e.g. with fixed formulas
     * @param string $usr_frm_exp the formula expression in the human-readable format
     * @param string $db_ref_frm_exp the formula expression in the database reference format
     * @return void
     */
    private function frm_exp_convert(
        test_cleanup $t,
        string       $test_name,
        string       $usr_frm_exp,
        string       $db_ref_frm_exp,
        ?term_list   $in_trm_lst = null
    ): void
    {
        global $usr;

        $test_name = 'conversion of the user text to the database reference text ' . $test_name;
        $exp = new expression($usr);
        $exp->set_user_text($usr_frm_exp, $in_trm_lst);
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