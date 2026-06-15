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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\expression;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class expression_tests
{
    function run(test_cleanup $t): void
    {

        $t_frm = new test_formulas($t);
        $t_trm = new test_terms($t);
        $usr_msg = new user_message();
        $lib = new library();

        // init
        $t->name = 'expression->';
        $trm_lst = $t_trm->term_list_all();

        // start the test section (ts)
        $ts = 'unit expression ';
        $t->header($ts);


        $t->subheader($ts . 'extract database id list');

        $test_name = 'get all terms needed calculating an expression';
        $frm = $t_frm->formula();
        $exp = $frm->expression();
        $trm_id_lst = $exp->term_id_list($usr_msg);
        $result = $trm_id_lst->dsp_id();
        $target = word_names::MINUTE_ID * 2 - 1;
        $t->assert($test_name, $result, $target);

        $test_name = 'reference text is invalid because a symbol is too short';
        $frm->ref_text = formulas::SCALE_TO_SEC_EXP_REF_SHORT_SYMBOL;
        $exp = $frm->expression();
        $exp->term_id_list($usr_msg);
        $result = $usr_msg->all_message_text();
        $target = 'the formula expression symbol "{w}" is too short';
        $t->assert($test_name, $result, $target);
        $usr_msg->reset();

        $test_name = 'reference text is invalid because the id is not a number';
        $frm->ref_text = formulas::SCALE_TO_SEC_EXP_REF_ID_NOT_A_NUMBER;
        $exp = $frm->expression();
        $exp->term_id_list($usr_msg);
        $result = $usr_msg->all_message_text();
        $target = 'the formula expression id wO is no a valid integer number';
        $t->assert($test_name, $result, $target);
        $usr_msg->reset();

        $test_name = 'reference text is invalid because the term type is not supported';
        $frm->ref_text = formulas::SCALE_TO_SEC_EXP_REF_SYMBOL_NOT_VALID;
        $exp = $frm->expression();
        $exp->term_id_list($usr_msg);
        $result = $usr_msg->all_message_text();
        $target = 'the formula expression symbol "d" is not valid. only word, triple, verb and formula are expected.';
        $t->assert($test_name, $result, $target);
        $usr_msg->reset();

        $test_name = 'reference text is invalid because it is an empty string';
        $frm->ref_text = '';
        $exp = $frm->expression();
        $exp->term_id_list($usr_msg);
        $result = $usr_msg->all_message_text();
        $target = 'the expression of formula "scale minute to sec" is empty';
        $t->assert($test_name, $result, $target);
        $usr_msg->reset();

        $test_name = 'get the phrase id that should be added to the results';
        $frm = $t_frm->formula();
        $exp = $frm->expression();
        $trm_id_lst = $exp->phrase_id_list($usr_msg);
        $result = $trm_id_lst->dsp_id();
        $target = 'phrase_id ' . triples::SECOND_ID * -1 . ' for user ' . users::SYSTEM_TEST_ID . ' (' . users::SYSTEM_TEST_NAME . ')';
        $t->assert($test_name, $result, $target);

        $test_name = 'phrase id is invalid because the id is not a number';
        $frm->ref_text = formulas::SCALE_TO_SEC_EXP_PHRASE_ID_NOT_VALID;
        $exp = $frm->expression();
        $exp->phrase_id_list($usr_msg);
        $result = $usr_msg->all_message_text();
        $target = 'the formula expression id wO is no a valid integer number';
        $t->assert($test_name, $result, $target);
        $usr_msg->reset();

        $test_name = 'get all terms including the phrases for the result';
        $frm = $t_frm->formula();
        $exp = $frm->expression();
        $trm_id_lst = $exp->term_id_list_all($usr_msg);
        $result = $trm_id_lst->dsp_id();
        $target = '"","" (' . triples::SECOND_ID * -2 + 1 . ',' . word_names::MINUTE_ID * 2 - 1 . ')';
        $t->assert($test_name, $result, $target);

        $test_name = 'id list of missing terms is empty if all terms are given';
        $frm = $t_frm->formula();
        $exp = $frm->expression();
        $trm_lst = $t_trm->term_list_all();
        $id_lst = $exp->terms_missing($usr_msg, $trm_lst);
        $t->assert_true($test_name, $id_lst->is_empty());
        $test_name = 'id list of missing terms is returning the missing term id';
        $trm_lst->unset_by_id(words::SECOND_ID);
        $id_lst = $exp->terms_missing($usr_msg, $trm_lst);
        $t->assert($test_name, $result, $target);


        $t->subheader($ts . 'extract term list');



        $test_name = 'report which terms are missing';

        $t->subheader($ts . 'interface');


        $t->subheader($ts . 'convert user text to database ref text and the other way round');

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


        $test_name = 'get the calc phrases';
        $frm = $t_frm->formula();
        $exp = new expression($frm);
        $exp->set_user_text(formulas::DIAMETER, $trm_lst);
        //$trm_names = $exp->get_usr_names();
        //$trm_lst = $t->term_list_for_tests($trm_names);
        $exp->ref_text($trm_lst);
        $usr_msg->reset();
        $phr_lst = $exp->terms($usr_msg, $trm_lst)->phrase_list();
        $result = $phr_lst->dsp_id();
        $target = '"' . word_names::CIRCUMFERENCE . '","'
            . word_names::PI . '" (phrase_id '
            . word_names::CIRCUMFERENCE_ID . ','
            . word_names::PI_ID . ') for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // test the phrase list of the left side
        $test_name = 'get the result phrases';
        $exp = new expression($frm);
        $exp->set_user_text(formulas::INCREASE_EXP, $trm_lst);
        $exp->ref_text($trm_lst);
        $phr_lst = $exp->load_result_phrases($trm_lst);
        $result = $phr_lst->dsp_id();
        $target = '"' . formulas::PERCENT
            . '" (phrase_id ' . words::PCT_ID . ') for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // the phrase list for the calc part should be empty, because it contains only formulas
        $trm_names = $exp->get_usr_names();
        $trm_lst_rev = $t->term_list_for_tests($trm_names);
        $usr_msg->reset();
        $phr_lst = $exp->terms($usr_msg, $trm_lst_rev)->phrase_list();
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
        $exp = new expression($frm);
        $exp->set_user_text(formulas::PARTS_IN_PERCENT_EXP, $trm_lst);
        $trm_names = $exp->get_usr_names();
        $exp->ref_text($trm_lst);
        $elm_grp_lst = $exp->element_grp_lst($trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = '"parts,' . verbs::OF_NAME . '" (' . word_names::PARTS_ID . ',' . verbs::OF_ID . ') / "total" (' . words::TOTAL_ID
            . ') for user 3 (zukunft.com system test)';
        //$target = '"' . formulas::TN_PERCENT . '" (1)';
        $t->assert($test_name, $result, $target);
        $usr_msg->reset();

        // test the element list of the right side
        $elm_grp_lst = $exp->element_list($usr_msg, $trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = '"parts","of","total" (element_id '
            . word_names::PARTS_ID . ',' . verbs::OF_ID . ',' . words::TOTAL_ID
            . ') for user 3 (zukunft.com system test)';
        $target = '"parts","of","total" (element_id 1/286,1/5,1/287) for user 3 (zukunft.com system test)';
        //$target = '"' . formulas::TN_PERCENT . '" (1)';
        $t->assert($test_name, $result, $target);

        // tests based on the increase formula
        $test_name = 'test the conversion of the user text to the database reference text with fixed formulas';
        $frm = $t_frm->formula_increase();
        $exp = $frm->expression();
        $exp->set_user_text(formulas::INCREASE_EXP, $trm_lst);
        $result = $exp->ref_text($trm_lst);
        $target = formulas::INCREASE_DB;
        $t->assert($test_name, $result, $target);

        $test_name = 'test getting the phrase ids';
        $result = implode(",", $exp->phr_id_lst($exp->ref_text())->lst);
        $target = implode(",", array(words::PCT_ID));
        $t->assert($test_name, $result, $target);

        $test_name = 'test the conversion of the database reference text to the user text';
        $result = $exp->user_text($trm_lst);
        $target = formulas::INCREASE_EXP;
        $t->assert($test_name, $result, $target);

        $test_name = 'test the formula element list';
        $elm_lst = $exp->element_list($usr_msg, $trm_lst);
        $result = $elm_lst->dsp_id();
        $target = '"' . formulas::THIS_NAME . '","'
            . formulas::PRIOR . '","'
            . formulas::PRIOR . '" (element_id 21/18,21/20,21/20) for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        $test_name = 'test the formula term list';
        $trm_lst = $exp->terms($usr_msg, $trm_lst);
        $result = $trm_lst->dsp_id();
        $target = '"' . words::PERCENT . '","'
            . formulas::PRIOR . '","'
            . formulas::THIS_NAME . '" (36,40,321)';
        $t->assert($test_name, $result, $target);

        // element_special_following
        $trm_lst->load_additional_by_id($exp->terms_missing($usr_msg, $trm_lst));
        $follow_lst = $exp->terms_following($usr_msg, $trm_lst);
        $result = $follow_lst->dsp_name();
        $target = '"' . formulas::PRIOR . '","' . formulas::THIS_NAME . '"';
        $t->assert('element_special_following for "' . $exp->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_LONG);

        // TODO element_special_following_frm
        //$target = '"time_prior","time_this"';

        $test_name = 'test the formula element group creation';
        $elm_grp_lst = $exp->element_grp_lst($trm_lst);

        // create the formulas for testing
        $frm_this = $trm_lst->get_by_name(formulas::THIS_NAME);
        $frm_prior = $trm_lst->get_by_name(formulas::PRIOR);

        $result = $elm_grp_lst->dsp_id();
        $target = '"' . formulas::THIS_NAME . '" (' . $frm_this->id_obj() . ') / "' . formulas::PRIOR . '" (' . $frm_prior->id_obj() . ') / "' . word_names::PRIOR_NAME . '" ('
            . $frm_prior->id_obj() . ')';
        $t->dsp_contains($test_name, $target, $result);

        $test_name = 'getting phrases that should be added to the result of a formula for "' . $exp->dsp_id() . '"';
        $phr_lst_res = $exp->load_result_phrases($trm_lst);
        $result = $phr_lst_res->dsp_name();
        $target = '"' . words::PCT . '"';
        $t->assert($test_name, $result, $target);

        // tests based on the pi formula
        $test_name = 'test the user text conversion with a triple';
        $exp = new expression($frm);
        $exp->set_user_text(formulas::DIAMETER, $t_trm->term_list_all());
        $trm_names = $exp->get_usr_names();
        $trm_lst_rev = $t->term_list_for_tests($trm_names);
        $result = $exp->ref_text($trm_lst_rev);
        $target = '={w' . word_names::CIRCUMFERENCE_ID . '}/{w' . word_names::PI_ID . '}';
        $t->assert($test_name, $result, $target);

        $test_name = 'source phrase list with id from the reference text';
        $exp_sector = new expression($frm);
        $exp_sector->set_ref_text(formulas::PARTS_IN_PERCENT_DB, $trm_lst);
        $phr_lst = $exp_sector->phr_id_lst_as_phr_lst($exp_sector->r_part());
        $result = $phr_lst->dsp_id();
        $target = '"","" (phrase_id ' . word_names::PARTS_ID . ',' . words::TOTAL_ID
            . ') for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        $test_name = 'result phrase list with id from the reference text';
        $exp_scale = new expression($frm);
        $exp_scale->set_ref_text(formulas::SCALE_MIO_DB, $trm_lst);
        $phr_lst = $exp_scale->phr_id_lst_as_phr_lst($exp_scale->res_part());
        $result = $phr_lst->dsp_id();
        $target = 'phrase_id ' . word_names::ONE_ID . ' for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

    }

    /**
     * @param test_cleanup $t just the testing object to count the number of errors and warnings
     * @param string $test_name the part that should be tested e.g. with fixed formulas
     * @param string $usr_frm_exp the formula expression in the human-readable format
     * @param string $db_ref_frm_exp the formula expression in the database reference format
     * @param ?term_list $in_trm_lst the term list cache to be used for the conversion
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
        $t_frm = new test_formulas($t);
        $frm = $t_frm->formula();

        $test_name = 'conversion of the user text to the database reference text ' . $test_name;
        $exp = new expression($frm);
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