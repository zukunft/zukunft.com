<?php

/*

    test/php/unit_read/formula.php - database unit testing of the formula functions
    ------------------------------


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

    Copyright (c) 1995-2023 zukunft.com AG, Switzerland
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

include_once paths::MODEL_ELEMENT . 'element.php';
include_once paths::MODEL_ELEMENT . 'element_group.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SERVICE_MATH . 'calc_internal.php';
include_once paths::SHARED_CONST . 'chars.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\element\element_group;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\service\math\calc_internal;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_read_tests
{

    function run(test_cleanup $t): void
    {

        global $sys;
        global $db_con;
        $msg = new user_message();

        // init
        $t_db = new test_db_load($t);
        $t->name = 'formula read db->';

        // start the test section (ts)
        $ts = 'db read formula ';
        $t->header($ts);

        $t->subheader($ts . 'load' );
        $test_name = formula_names::SCALE_TO_SEC;
        $frm = new formula($t->usr1);
        $t->assert_load($frm, formula_names::SCALE_TO_SEC);

        $t->subheader($ts . 'by name');

        /*
        // ... check if the link is shown correctly also for the second user
        // ... the second user has excluded the word at this point, so even if the word is linked the word link is nevertheless false
        // TODO Prio 2 check what that the word is linked if the second user activates the word
        $phr = new phrase($t->usr1);
        $phr->load_by_name(words::TN_READ);
        $frm = new formula($t->usr2);
        $frm->load_by_name(formulas::TN_RENAMED);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        $t->assert('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr2->name . '"', $result, $target);
        */


        $t->subheader($ts . 'types');

        // load the formula types
        $lst = new formula_type_list();
        $result = $lst->load($db_con, $msg);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $sys->typ_lst->frm_typ->id(formula_type::CALC);
        $target = 1;
        $t->assert('check ' . formula_type::CALC, $result, 1);

        // check the estimates for the calculation blocks
        $calc_blocks = new formula_list($t->usr1)->calc_blocks($db_con, $msg);
        $t->assert_greater_zero('calc_blocks', $calc_blocks);

        $t->subheader($ts . 'api');

        $test_name = formula_names::INCREASE;
        $frm = $t_db->load_formula(formula_names::INCREASE);
        if ($frm->name() != '') {
            $t->assert_export_reload($ts . $test_name, $frm);
        } else {
            log_err($ts . $test_name . ' failed');
        }

        $t->subheader($ts . 'link reload');
        // the delete log names the linked phrase and its sql function is created only once per name,
        // so the phrase is reloaded also if the message already reports a problem of the request
        $test_name = 'the phrase of a formula link is reloaded also if the message already reports a problem';
        $frm_sec = $t_db->load_formula(formula_names::SCALE_TO_SEC);
        $phr_min = new phrase($t->usr1);
        $phr_min->load_by_name(word_names::MINUTE, $msg);
        $lnk_id = new formula_link($t->usr1)->load_by_link($frm_sec, $phr_min, $msg);
        $lnk = new formula_link($t->usr1);
        $lnk->load_by_id($lnk_id, $msg);
        $err_msg = new user_message($t->usr1);
        $err_msg->add(msg_id::LOAD_FORMULA_BY_ID_FAILED, [msg_id::VAR_FORMULA => $frm_sec->dsp_id()]);
        $lnk->reload_objects($err_msg);
        $t->assert($test_name, $lnk->to_name(), word_names::MINUTE);
        $test_name = 'a formula link loaded by id without a reload has no phrase name';
        $lnk_raw = new formula_link($t->usr1);
        $lnk_raw->load_by_id($lnk_id, $msg);
        $t->assert_null($test_name, $lnk_raw->to_name());

        $t->subheader($ts . 'list function');
        // the turnover of the nine currency rows of currencies.json, because no write test adds such
        // a value, unlike the inhabitants of Switzerland, which value_write_tests also adds without a year
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::DAILY_TURNOVER, $msg);
        $t_ctx = [words::TOTAL_PRE, word_names::MIO, triple_names::IN_USD, triple_names::NET_NET_BASIS, triple_names::APRIL_2022];
        $calc = new calc_internal();
        $test_name = 'sum takes all stored values of the operand and the context';
        $num_txt = $this->list_text($t_db, $t, chars::FUNC_SUM, $trp, $t_ctx, $msg);
        $t->assert($test_name, round((float)$calc->parse($num_txt, $msg), 2), 15011983.61);
        $test_name = 'count counts these values';
        $num_txt = $this->list_text($t_db, $t, chars::FUNC_COUNT, $trp, $t_ctx, $msg);
        $t->assert($test_name, (float)$calc->parse($num_txt, $msg), 9.0);
        $test_name = 'mean is the average of these values';
        $num_txt = $this->list_text($t_db, $t, chars::FUNC_MEAN, $trp, $t_ctx, $msg);
        $t->assert($test_name, round((float)$calc->parse($num_txt, $msg), 2), 1667998.18);
        // negative: a list without a value is not filled, so the symbol never becomes a number
        $test_name = 'a list without a stored value is not filled';
        $inh = new word($t->usr1);
        $inh->load_by_name(word_names::INHABITANTS, $msg);
        $no_val_ctx = [words::CH, word_names::MIO, word_names::YEAR_2019, word_names::YEAR_2020];
        $t->assert($test_name, $this->list_text($t_db, $t, chars::FUNC_SUM, $inh, $no_val_ctx, $msg), '');
        // negative: a formula has no stored values, and results are not part of a list
        $test_name = 'a formula as the operand of a list is not filled';
        $frm_elm = $t_db->load_formula(formula_names::INCREASE);
        $t->assert($test_name, $this->list_text($t_db, $t, chars::FUNC_SUM, $frm_elm, [words::CH], $msg), '');

        $t->subheader($ts . 'update latex');
        // the increase formula "percent" = ( "this" - "prior" ) / "prior" creates a fraction
        // with each term wrapped in \text and the result is stored in the latex object field
        $test_name = 'the division creates a \frac';
        $frm = $t_db->load_formula(formula_names::INCREASE);
        $latex = $frm->update_latex();
        $t->assert_text_contains($test_name, $latex, '\frac{');
        $test_name = 'each term is wrapped in \text';
        $t->assert_text_contains($test_name, $latex, '\text{');
        $test_name = 'the created latex is stored in the latex field';
        $t->assert($test_name, $frm->get_latex(), $latex);
        // a formula without an expression cannot create a latex and returns an empty string
        $test_name = 'no expression creates no latex';
        $frm_empty = new formula($t->usr1);
        $t->assert($test_name, $frm_empty->update_latex(), '');

        // the import creates the latex of a formula that the import file defines without one
        // (see formula_list::refresh_ref_text), so the page of every imported formula shows a
        // latex format, whereas a latex given by the import file is kept as it is written;
        // the latex is written once by the import, so an empty latex here means that the
        // database has been recreated with a code version before the import created it
        $test_name = 'the import has created the latex of ' . formula_names::SCALE_THOUSAND_TO_ONE
            . ', which the import file defines without a latex';
        $frm_scale = $t_db->load_formula(formula_names::SCALE_THOUSAND_TO_ONE);
        $t->assert_text_contains($test_name, $frm_scale->get_latex() ?? '', '\text{',
            $t::TIMEOUT_LIMIT_DB,
            'an empty latex means the database has been recreated before formula_list::refresh_ref_text created the latex on import, so recreate the database');
        $test_name = 'a latex given by the import file is not replaced by the created latex';
        $frm_power = $t_db->load_formula(formula_names::PROPAGATION_POWER);
        $t->assert($test_name, $frm_power->get_latex(), formula_names::PROPAGATION_POWER_LATEX);

        $t->subheader($ts . 'latex terms');
        // the \text{} tokens of the latex and the names of the expression
        // are resolved to terms so the frontend can link them
        $test_name = 'the latex terms of the increase formula are loaded';
        $frm->load_latex_terms($msg);
        $t->assert_greater_zero($test_name, $frm->latex_terms->count());
        // a formula without a latex and without an expression has no terms to link
        $test_name = 'no latex has no latex terms';
        $frm_empty->load_latex_terms($msg);
        $t->assert($test_name, $frm_empty->latex_terms->is_empty(), true);
    }

    /**
     * @param test_db_load $t_db to load the context phrases
     * @param test_cleanup $t the test environment with the user
     * @param string $func the name of the list function e.g. sum
     * @param word|triple|formula $obj the operand of the list function
     * @param array $ctx_names the names of the phrases of the calculation context
     * @param user_message $msg to collect the problems of the load
     * @return string the expression with all numbers of the list or an empty text if the list is not filled
     */
    private function list_text(
        test_db_load $t_db,
        test_cleanup $t,
        string       $func,
        word|triple|formula $obj,
        array        $ctx_names,
        user_message $msg
    ): string
    {
        $symbol = chars::TERM_START . 'w1' . chars::TERM_END;
        $elm = new element($t->usr1);
        $elm->obj = $obj;
        $elm->symbol = $symbol;
        $elm_grp = new element_group();
        $elm_grp->usr = $t->usr1;
        $elm_grp->add_obj($elm, true);
        $elm_grp->build_symbol();
        $ctx_lst = $t_db->load_phrase_list($ctx_names);
        // a list only takes a result with an id, which comes from its group like in formula::create_result
        $res = new result($t->usr1);
        $res->grp()->set_phrase_list(clone $ctx_lst);
        $res->num_text = $func . chars::BRACKET_OPEN . $symbol . chars::BRACKET_CLOSE;
        $res_lst = new result_list($t->usr1);
        $frm = new formula($t->usr1);
        $filled = $frm->fill_list_element_group($res_lst, $elm_grp, $ctx_lst, $res, $msg);
        return $filled ? ($res_lst->lst()[0]->num_text ?? '') : '';
    }

}

