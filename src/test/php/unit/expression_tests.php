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

include_once paths::SHARED_CONST . 'chars.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\element\element_group_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\expression;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class expression_tests
{
    function run(test_cleanup $t): void
    {

        $t_frm = new test_formulas($t);
        $t_trm = new test_terms($t);
        $msg = new user_message();
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
        $exp = $frm->expression($msg);
        $trm_id_lst = $exp->term_id_list($msg);
        $result = $trm_id_lst->dsp_id();
        $target = word_names::MINUTE_ID * 2 - 1;
        $t->assert($test_name, $result, $target);

        $test_name = 'reference text is invalid because a symbol is too short';
        $frm->ref_text = formula_names::SCALE_TO_SEC_EXP_REF_SHORT_SYMBOL;
        $exp = $frm->expression($msg);
        $exp->term_id_list($msg);
        $result = $msg->all_message_text();
        $target = 'the formula expression symbol "{w}" is too short';
        $t->assert($test_name, $result, $target);
        $msg->reset();

        $test_name = 'reference text is invalid because the id is not a number';
        $frm->ref_text = formula_names::SCALE_TO_SEC_EXP_REF_ID_NOT_A_NUMBER;
        $exp = $frm->expression($msg);
        $exp->term_id_list($msg);
        $result = $msg->all_message_text();
        $target = 'the formula expression id wO is no a valid integer number';
        $t->assert($test_name, $result, $target);
        $msg->reset();

        $test_name = 'reference text is invalid because the term type is not supported';
        $frm->ref_text = formula_names::SCALE_TO_SEC_EXP_REF_SYMBOL_NOT_VALID;
        $exp = $frm->expression($msg);
        $exp->term_id_list($msg);
        $result = $msg->all_message_text();
        $target = 'the formula expression symbol "d" is not valid. only word, triple, verb and formula are expected.';
        $t->assert($test_name, $result, $target);
        $msg->reset();

        // without the user text too, because else the expression recreates the reference text from it and reads
        // its terms from the database, which a unit test has not
        $test_name = 'reference text is invalid because it is an empty string';
        $frm->ref_text = '';
        $frm->usr_text = '';
        $exp = $frm->expression($msg);
        $exp->term_id_list($msg);
        $result = $msg->all_message_text();
        $target = 'the expression of formula "' . formula_names::SCALE_TO_SEC . '" is empty';
        $t->assert($test_name, $result, $target);
        $msg->reset();

        $test_name = 'get the phrase id that should be added to the results';
        $frm = $t_frm->formula();
        $exp = $frm->expression($msg);
        $trm_id_lst = $exp->phrase_id_list($msg);
        $result = $trm_id_lst->dsp_id();
        $target = 'phrase_id ' . triple_names::SECOND_ID * -1 . ' for user ' . users::SYSTEM_TEST_ID . ' (' . users::SYSTEM_TEST_NAME . ')';
        $t->assert($test_name, $result, $target);

        $test_name = 'phrase id is invalid because the id is not a number';
        $frm->ref_text = formula_names::SCALE_TO_SEC_EXP_PHRASE_ID_NOT_VALID;
        $exp = $frm->expression($msg);
        $exp->phrase_id_list($msg);
        $result = $msg->all_message_text();
        $target = 'the formula expression id wO is no a valid integer number';
        $t->assert($test_name, $result, $target);
        $msg->reset();

        $test_name = 'get all terms including the phrases for the result';
        $frm = $t_frm->formula();
        $exp = $frm->expression($msg);
        $trm_id_lst = $exp->term_id_list_all($msg);
        $result = $trm_id_lst->dsp_id();
        $target = '"","" (' . triple_names::SECOND_ID * -2 + 1 . ',' . word_names::MINUTE_ID * 2 - 1 . ')';
        $t->assert($test_name, $result, $target);

        $test_name = 'id list of missing terms is empty if all terms are given';
        $frm = $t_frm->formula();
        $exp = $frm->expression($msg);
        $trm_lst = $t_trm->term_list_all();
        $id_lst = $exp->terms_missing($msg, $trm_lst);
        $t->assert_true($test_name, $id_lst->is_empty());
        $test_name = 'id list of missing terms is returning the missing term id';
        $trm_lst->unset_by_id(words::SECOND_ID);
        $id_lst = $exp->terms_missing($msg, $trm_lst);
        $t->assert($test_name, $result, $target);


        $t->subheader($ts . 'extract term list');



        $test_name = 'report which terms are missing';

        $t->subheader($ts . 'interface');


        $t->subheader($ts . 'convert user text to database ref text and the other way round');

        $this->frm_exp_convert($t, $msg,
            'including a triple',
            formula_names::DIAMETER,
            formula_names::DIAMETER_DB,
            formula_names::DIAMETER_FROM_DB,
            $trm_lst
        );
        $this->frm_exp_convert($t, $msg,
            'including the min function',
            formula_names::MIN_CIRCUMFERENCE_PI,
            formula_names::MIN_CIRCUMFERENCE_PI_DB,
            formula_names::MIN_CIRCUMFERENCE_PI_FROM_DB,
            $trm_lst
        );
        $this->frm_exp_convert($t, $msg,
            'including the max function',
            formula_names::MAX_CIRCUMFERENCE_PI,
            formula_names::MAX_CIRCUMFERENCE_PI_DB,
            formula_names::MAX_CIRCUMFERENCE_PI_FROM_DB,
            $trm_lst
        );
        $this->frm_exp_convert($t, $msg,
            'including fixed formulas',
            formula_names::INCREASE_EXP,
            formula_names::INCREASE_DB,
            formula_names::INCREASE_FROM_DB,
            $trm_lst
        );
        $this->frm_exp_convert($t, $msg,
            'including verbs',
            formula_names::PARTS_IN_PERCENT_EXP,
            formula_names::PARTS_IN_PERCENT_DB,
            formula_names::PARTS_IN_PERCENT_FROM_DB,
            $trm_lst
        );


        $test_name = 'get the calc phrases';
        $frm = $t_frm->formula();
        $exp = new expression($frm);
        $exp->set_user_text(formula_names::DIAMETER, $msg, $trm_lst);
        //$trm_names = $exp->get_usr_names();
        //$trm_lst = $t->term_list_for_tests($trm_names);
        $exp->ref_text_ui($msg, $trm_lst);
        $msg->reset();
        $phr_lst = $exp->terms($msg, $trm_lst)->phrase_list();
        $result = $phr_lst->dsp_id();
        $target = '"' . word_names::CIRCUMFERENCE . '","'
            . word_names::PI . '" (phrase_id '
            . word_names::CIRCUMFERENCE_ID . ','
            . word_names::PI_ID . ') for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // a term that the preloaded terms do not know fails the conversion without a database read, because the
        // read could not change the result and would e.g. read in a unit test on the dummy connection
        $test_name = 'a term that the preloaded terms do not know is reported';
        $miss_msg = new user_message($t->usr1);
        $miss_msg->log_test_only = true;
        $exp = new expression($frm);
        $exp->set_user_text(formula_names::INCREASE_EXP, $miss_msg, $t_trm->term_list_time());
        $read_err = $miss_msg->log_test_txt;
        $t->assert_msg_false($test_name, $miss_msg, words::PERCENT);
        // negative: without a database read no internal error is kept on the message
        $test_name = '... without a database read';
        $t->assert($test_name, $read_err, '');

        // negative: an empty reference text is no conversion, so the user text stays as it is
        $test_name = 'an empty reference text does not change the user text';
        $exp_keep = new expression($frm);
        $exp_keep->set_user_text(formula_names::SCALE_TO_SEC_EXP, $msg, $t_trm->term_list_time());
        $exp_keep->set_ref_text('', $msg, $t_trm->term_list_time());
        $result = $exp_keep->user_text_ui($t_trm->term_list_time());
        $t->assert($test_name, $result, formula_names::SCALE_TO_SEC_EXP);
        $msg->reset();

        // test the phrase list of the left side
        $test_name = 'get the result phrases';
        $exp = new expression($frm);
        $exp->set_user_text(formula_names::INCREASE_EXP, $msg, $trm_lst);
        $exp->ref_text_ui($msg, $trm_lst);
        $phr_lst = $exp->load_result_phrases($msg, $trm_lst);
        $result = $phr_lst->dsp_id();
        $target = '"' . formula_names::PERCENT
            . '" (phrase_id ' . words::PCT_ID . ') for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // the phrase list for the calc part should be empty, because it contains only formulas
        $trm_names = $exp->get_usr_names();
        $trm_lst_rev = $t->term_list_for_tests($trm_names);
        $msg->reset();
        $phr_lst = $exp->terms($msg, $trm_lst_rev)->phrase_list();
        $result = $phr_lst->dsp_id();
        $target = '';
        $t->assert($test_name, $result, $target);
        $msg->reset();

        // test the element group list of the right side
        // TODO Prio 2 check with cantons of switzerland
        // TODO Prio 2 check if adjustment overwrite from some parts works
        //      e.g. if the total needs to be adjusted, because
        //      the sum of tax payers of all cantons can be higher than
        //      the total number of tax payers in Switzerland
        //      because one person can be tax payer in more than one canton
        $test_name = 'get the formula element group list';
        $exp = new expression($frm);
        $exp->set_user_text(formula_names::PARTS_IN_PERCENT_EXP, $msg, $trm_lst);
        $trm_names = $exp->get_usr_names();
        $exp->ref_text_ui($msg, $trm_lst);
        $elm_grp_lst = $exp->element_grp_lst($msg, $trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = '"parts,' . verbs::OF_NAME . '" (' . word_names::PARTS_ID . ',' . verbs::OF_ID . ') / "total" (' . words::TOTAL_ID
            . ') for user 3 (zukunft.com system test)';
        //$target = '"' . formulas::TN_PERCENT . '" (1)';
        $t->assert($test_name, $result, $target);
        $msg->reset();

        // test the element list of the right side
        $elm_grp_lst = $exp->element_list($msg, $trm_lst);
        $result = $elm_grp_lst->dsp_id();
        // an element is named by the formula it belongs to and the object it links, so its id
        // is the formula id and the phrase or verb id, e.g. "1/555" for "parts" of formula 1
        $frm_id = formula_names::SCALE_TO_SEC_ID . '/';
        $target = '"parts","of","total" (element_id '
            . $frm_id . word_names::PARTS_ID . ','
            . $frm_id . verbs::OF_ID . ','
            . $frm_id . words::TOTAL_ID
            . ') for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // tests based on the increase formula
        $test_name = 'test the conversion of the user text to the database reference text with fixed formulas';
        $frm = $t_frm->formula_increase();
        $exp = $frm->expression($msg);
        $exp->set_user_text(formula_names::INCREASE_EXP, $msg, $trm_lst);
        $result = $exp->ref_text_ui($msg, $trm_lst);
        $target = formula_names::INCREASE_DB;
        $t->assert($test_name, $result, $target);

        $test_name = 'test getting the phrase ids';
        $result = implode(",", $exp->phr_id_lst($exp->ref_text_ui($msg))->lst);
        $target = implode(",", array(words::PCT_ID));
        $t->assert($test_name, $result, $target);

        $test_name = 'test the conversion of the database reference text to the user text';
        $result = $exp->user_text_ui($trm_lst);
        $target = formula_names::INCREASE_EXP;
        $t->assert($test_name, $result, $target);

        $test_name = 'test the formula element list';
        $elm_lst = $exp->element_list($msg, $trm_lst);
        $result = $elm_lst->dsp_id();
        // the ids are built from the pinned ids like above, so that a new seed formula or word does not break the test
        $frm_id = formula_names::INCREASE_ID . '/';
        $target = '"' . formula_names::THIS_NAME . '","'
            . formula_names::PRIOR . '","'
            . formula_names::PRIOR . '" (element_id '
            . $frm_id . formula_names::THIS_ID . ','
            . $frm_id . formula_names::PRIOR_ID . ','
            . $frm_id . formula_names::PRIOR_ID
            . ') for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        $test_name = 'test the formula term list';
        $trm_lst = $exp->terms($msg, $trm_lst);
        $result = $trm_lst->dsp_id();
        // the names and the term ids are each listed in their sort order
        $trm_ids = [
            $lib->term_id(formula_names::THIS_ID, formula::class),
            $lib->term_id(formula_names::PRIOR_ID, formula::class),
            $lib->term_id(words::PCT_ID, word::class)
        ];
        sort($trm_ids);
        $target = '"' . words::PERCENT . '","'
            . formula_names::PRIOR . '","'
            . formula_names::THIS_NAME . '" (' . implode(',', $trm_ids) . ')';
        $t->assert($test_name, $result, $target);

        // element_special_following
        $trm_lst->load_additional_by_id($exp->terms_missing($msg, $trm_lst), $msg);
        $follow_lst = $exp->terms_following($msg, $trm_lst);
        $result = $follow_lst->dsp_name();
        $target = '"' . formula_names::PRIOR . '","' . formula_names::THIS_NAME . '"';
        $t->assert('element_special_following for "' . $exp->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_LONG);

        // TODO Prio 2 element_special_following_frm
        //$target = '"time_prior","time_this"';

        $test_name = 'test the formula element group creation';
        $elm_grp_lst = $exp->element_grp_lst($msg, $trm_lst);

        // create the formulas for testing
        $frm_this = $trm_lst->get_by_name(formula_names::THIS_NAME, $msg);
        $frm_prior = $trm_lst->get_by_name(formula_names::PRIOR, $msg);

        $result = $elm_grp_lst->dsp_id();
        $target = '"' . formula_names::THIS_NAME . '" (' . $frm_this->id_obj() . ') / "' . formula_names::PRIOR . '" (' . $frm_prior->id_obj() . ') / "' . word_names::PRIOR_NAME . '" ('
            . $frm_prior->id_obj() . ')';
        $t->dsp_contains($test_name, $target, $result);

        // the phrases of an operand joined by the separator select one number e.g. "cumulative deviation","prior"
        $test_name = 'an operand of several phrases is one element group';
        $trm_lst_prio = $t_trm->term_list_solution_prio();
        $exp_htp = new expression($frm);
        $exp_htp->set_user_text(formula_names::GLOBAL_HTP_EXP, $msg, $trm_lst_prio);
        $htp_grp_lst = $exp_htp->element_grp_lst($msg, $trm_lst_prio)->lst();
        $t->assert($test_name, count($htp_grp_lst), 2);
        $test_name = '... with the phrases of the operand';
        $htp_names = [word_names::HTP, words::PERCENT];
        $t->assert($test_name, $htp_grp_lst[0]->name(), $lib->dsp_array($htp_names));
        // the symbol is the text of the expression, so that the number of the operand replaces it
        $test_name = '... and the symbol with the separator, which is part of the expression';
        $htp_symbol = $htp_grp_lst[0]->build_symbol();
        $t->assert_text_contains($test_name, $exp_htp->r_part($msg), $htp_symbol);
        $test_name = '... and that names both phrases';
        $t->assert_text_contains($test_name, $htp_symbol, chars::SEPARATOR);
        $msg->reset();
        // negative: a math symbol between two phrases ends the group, so each phrase selects its own number
        $test_name = 'an operator between two phrases gives two element groups';
        $exp_two = new expression($frm);
        $two_txt = '"' . word_names::HTP . '" = "' . word_names::HTP . '" * "' . words::PERCENT . '"';
        $exp_two->set_user_text($two_txt, $msg, $trm_lst_prio);
        $two_grp_lst = $exp_two->element_grp_lst($msg, $trm_lst_prio)->lst();
        $t->assert($test_name, $two_grp_lst[0]->name(), word_names::HTP);
        $msg->reset();

        $test_name = 'getting phrases that should be added to the result of a formula for "' . $exp->dsp_id() . '"';
        $phr_lst_res = $exp->load_result_phrases($msg, $trm_lst);
        $result = $phr_lst_res->dsp_name();
        $target = '"' . words::PCT . '"';
        $t->assert($test_name, $result, $target);

        // tests based on the pi formula
        $test_name = 'test the user text conversion with a triple';
        $exp = new expression($frm);
        $exp->set_user_text(formula_names::DIAMETER, $msg, $t_trm->term_list_all());
        $trm_names = $exp->get_usr_names();
        $trm_lst_rev = $t->term_list_for_tests($trm_names);
        $result = $exp->ref_text_ui($msg, $trm_lst_rev);
        $target = '={w' . word_names::CIRCUMFERENCE_ID . '}/{w' . word_names::PI_ID . '}';
        $t->assert($test_name, $result, $target);

        $test_name = 'source phrase list with id from the reference text';
        $exp_sector = new expression($frm);
        // only the reference text, because only its ids are tested and the conversion to the user text would read
        // the terms that the term list does not contain from the database
        $exp_sector->set_ref_and_user_text(formula_names::PARTS_IN_PERCENT_DB, null);
        $phr_lst = $exp_sector->phr_id_lst_as_phr_lst($exp_sector->r_part($msg));
        $result = $phr_lst->dsp_id();
        $target = '"","" (phrase_id ' . word_names::PARTS_ID . ',' . words::TOTAL_ID
            . ') for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        $test_name = 'result phrase list with id from the reference text';
        $exp_scale = new expression($frm);
        $exp_scale->set_ref_and_user_text(formula_names::SCALE_MIO_DB, null);
        $phr_lst = $exp_scale->phr_id_lst_as_phr_lst($exp_scale->res_part($msg));
        $result = $phr_lst->dsp_id();
        $target = 'phrase_id ' . word_names::ONE_ID . ' for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);
        $test_name = '... and the calculation part has the millions as element group';
        $elm_scale_lst = $exp_scale->element_grp_lst($msg, $t_trm->term_list_all());
        $t->assert_text_contains($test_name, $elm_scale_lst->dsp_id(), '(' . word_names::MIO_ID . ')');
        $msg->reset();

        // negative: a text without the equal sign is reported and has neither a result nor a calculation part
        $test_name = 'an expression without an equal sign is reported';
        $exp_no_calc = new expression($frm);
        $exp_no_calc->set_user_text('"' . words::PERCENT . '"', $msg, $trm_lst);
        $t->assert_false($test_name, $msg->is_ok());
        $msg->reset();
        $test_name = '... and has no calculation part';
        $t->assert($test_name, $exp_no_calc->r_part($msg), '');
        $test_name = '... and no result part';
        $t->assert($test_name, $exp_no_calc->res_part($msg), '');
        $test_name = '... and no terms';
        $t->assert($test_name, $exp_no_calc->term_id_list_all($msg)->count(), 0);
        $msg->reset();
        $test_name = 'a text without a phrase has no phrase ids';
        $t->assert($test_name, $exp_no_calc->phr_id_lst('')->lst, []);
        // negative: a triple used twice in the text is one phrase, like a word used twice
        $test_name = 'a triple used twice is listed once with its negative phrase id';
        $trp_ref = chars::TRIPLE_START . triple_names::PI_ID . chars::TRIPLE_END;
        $t->assert($test_name, $exp_no_calc->phr_id_lst($trp_ref . $trp_ref)->lst, [-triple_names::PI_ID]);
        // a triple left of a word e.g. the result triple of "delivered electricity" = "direct use" + ...
        $test_name = 'a triple left of a word is listed after the word';
        $wrd_ref = chars::WORD_START . words::PCT_ID . chars::WORD_END;
        $t->assert($test_name, $exp_no_calc->phr_id_lst($trp_ref . $wrd_ref)->lst, [words::PCT_ID, -triple_names::PI_ID]);

        $t->subheader($ts . 'debug id');

        // the debug id shows the user text of the expression
        $test_name = 'debug id with the user text';
        $exp = $t_frm->expression();
        $dsp = $exp->dsp_id();
        $t->assert_text_contains($test_name, $dsp, formula_names::SCALE_TO_SEC_EXP);

        // the debug id adds the reference text with the term ids
        $test_name = 'debug id with the reference text';
        $id_txt = (string)word_names::MINUTE_ID;
        $exp = $t_frm->expression();
        $dsp = $exp->dsp_id();
        $t->assert_text_contains($test_name, $dsp, $id_txt);

        // negative: a user text without equal sign has no reference text, so no term id is shown
        $test_name = 'debug id without reference text';
        $exp = new expression($t_frm->formula());
        $exp->set_ref_and_user_text(null, word_names::MINUTE);
        $dsp = $exp->dsp_id();
        $t->assert_text_not_contains($test_name, $dsp, $id_txt);

        // negative: an empty element group list has an empty debug id
        $test_name = 'debug id of an empty element group list';
        $elm_grp_lst = new element_group_list($t->usr1);
        $dsp = $elm_grp_lst->dsp_id();
        $t->assert($test_name, $dsp, '');

    }

    /**
     * @param test_cleanup $t just the testing object to count the number of errors and warnings
     * @param user_message $msg to report the conversion problems, checked by the block assertions
     * @param string $test_name the part that should be tested e.g. with fixed formulas
     * @param string $usr_frm_exp the formula expression in the human-readable format
     * @param string $db_ref_frm_exp the formula expression in the database reference format
     * @param string $usr_frm_from_db the user text that the conversion of the reference text writes, without spaces
     * @param ?term_list $in_trm_lst the term list cache to be used for the conversion
     * @return void
     */
    private function frm_exp_convert(
        test_cleanup $t,
        user_message $msg,
        string       $test_name,
        string       $usr_frm_exp,
        string       $db_ref_frm_exp,
        string       $usr_frm_from_db,
        ?term_list   $in_trm_lst = null
    ): void
    {
        $t_frm = new test_formulas($t);
        $frm = $t_frm->formula();

        $case_name = $test_name;
        $test_name = 'conversion of the user text to the database reference text ' . $case_name;
        $exp = new expression($frm);
        $exp->set_user_text($usr_frm_exp, $msg, $in_trm_lst);
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->term_list_for_tests($trm_names);
        $result = $exp->ref_text_ui($msg, $trm_lst);
        $target = $db_ref_frm_exp;
        $t->assert($test_name, $result, $target);

        $test_name = 'conversion of the database reference text to the user text ' . $case_name;
        $result = $exp->user_text_ui($trm_lst);
        $target = $usr_frm_exp;
        $t->assert($test_name, $result, $target);

        // the same from an expression that only gets the reference text, with all its terms preloaded
        // by the list that has created the reference ids, because the test term list numbers its terms from one
        $test_name = 'the reference text sets the user text ' . $case_name;
        $ref_trm_lst = $trm_lst;
        if ($in_trm_lst != null) {
            $ref_trm_lst = $in_trm_lst;
        }
        $exp_ref = new expression($frm);
        $exp_ref->set_ref_text($db_ref_frm_exp, $msg, $ref_trm_lst);
        $result = $exp_ref->user_text_ui($ref_trm_lst);
        $t->assert($test_name, $result, $usr_frm_from_db);
    }

}