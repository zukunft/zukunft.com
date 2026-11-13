<?php

/*

    test/unit/formula_calc.php - unit testing of the formula calculation functions
    --------------------------


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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_FORMULA . 'expression.php';
include_once paths::SHARED_CALC . 'prior_operand.php';
include_once html_paths::ELEMENT . 'element_group.php';
include_once html_paths::FORMULA . 'formula_calc.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\expression;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\element\element_group as element_group_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_calc as formula_calc_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list as term_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\main\php\shared\calc\prior_operand;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_calc_tests
{
    function run(test_cleanup $t): void
    {
        $msg = new user_message(); // a test is an entry point, so it creates the message the conversion reports into

        // init
        $lib = new library();
        $msg = new user_message();
        $msg_ui = new user_message_ui();
        $t_frm = new test_formulas($t);
        $t_wrd = new test_words($t);
        $t_phr = new test_phrases($t);
        $t_trm = new test_terms($t);
        $t->name = 'formula->';
        $t->resource_path = 'db/formula/';

        // start the test section (ts)
        $ts = 'unit formula calc ';
        $t->header($ts);

        $t->subheader($ts . 'stored result compare');
        $test_name = 'numbers on both sides of a rounding edge are the same result';
        $t->assert($test_name, formula_calc_ui::differs(0.035, 0.034999999), false);
        $test_name = 'a stored result that the page calculates the same does not differ';
        $t->assert($test_name, formula_calc_ui::differs(2.4, 2.4), false);
        // negative: a real difference is still reported
        $test_name = 'a result calculated from another number differs';
        $t->assert($test_name, formula_calc_ui::differs(2.4, 8.0), true);
        $test_name = '... also if it is only slightly bigger than the compared decimals';
        $t->assert($test_name, formula_calc_ui::differs(1.0, 1.02), true);

        $t->subheader($ts . 'prior operand');
        // Bern follows Zurich and Geneva follows Bern e.g. in the rows of a walk
        $pri = new prior_operand([word_names::BE => word_names::ZH, word_names::GE => word_names::BE]);
        $names = [word_names::INHABITANTS, phrase_types::PRIOR_NAME];

        $test_name = 'prior selects the previous phrase instead of the phrase of the context';
        [$sel_names, $sel_ctx] = $pri->select($names, [word_names::INHABITANTS, word_names::GE]);
        $t->assert($test_name, [$sel_names, $sel_ctx],
            [[word_names::INHABITANTS, word_names::BE], [word_names::INHABITANTS, word_names::BE]]);
        $test_name = '... and a row in the middle is no first row';
        $is_mid_first = $pri->is_first($names, [word_names::INHABITANTS, word_names::GE]);
        $t->assert($test_name, $is_mid_first, false);
        $test_name = 'the first row of a chain has no previous row';
        $is_first = $pri->is_first($names, [word_names::INHABITANTS, word_names::ZH]);
        $t->assert($test_name, $is_first, true);
        // negative: nothing changes for what is no row of one chain, so that the operand finds no number
        $test_name = '... but a context outside of a chain is no first row';
        $is_other_first = $pri->is_first($names, [word_names::INHABITANTS, word_names::CANTON]);
        $t->assert($test_name, $is_other_first, false);
        $test_name = '... and it selects no other phrase';
        $other_ctx = [word_names::INHABITANTS, word_names::CANTON];
        $other_sel = $pri->select($names, $other_ctx);
        $t->assert($test_name, $other_sel, [$names, $other_ctx]);
        $test_name = '... and two phrases of a chain in the context are not clear';
        $two_ctx = [word_names::BE, word_names::GE];
        $two_sel = $pri->select($names, $two_ctx);
        $t->assert($test_name, $two_sel, [$names, $two_ctx]);
        $test_name = '... and neither is a first row';
        $is_two_first = $pri->is_first($names, [word_names::ZH, word_names::GE]);
        $t->assert($test_name, $is_two_first, false);
        // negative: a plain prior stays the time jump of the backend e.g. in the formula increase
        $test_name = '... and a plain prior stays the time jump';
        $plain = [phrase_types::PRIOR_NAME];
        $plain_ctx = [word_names::BE];
        $plain_sel = $pri->select($plain, $plain_ctx);
        $t->assert($test_name, $plain_sel, [$plain, $plain_ctx]);
        // negative: another operand with several phrases is not changed
        $test_name = '... and an operand without prior is not changed';
        $no_pri = [word_names::INHABITANTS, word_names::BE];
        $no_pri_sel = $pri->select($no_pri, $plain_ctx);
        $t->assert($test_name, $no_pri_sel, [$no_pri, $plain_ctx]);
        // a phrase with two previous phrases has no clear row, whatever order the links are loaded in
        $test_name = 'a second link to the same previous phrase keeps the previous phrase';
        $one_link = prior_operand::with_link([], word_names::GE, word_names::BE);
        $t->assert($test_name, prior_operand::with_link($one_link, word_names::GE, word_names::BE), [word_names::GE => word_names::BE]);
        $test_name = '... but a link to another previous phrase is not clear';
        $two_links = prior_operand::with_link($one_link, word_names::GE, word_names::ZH);
        $three_links = prior_operand::with_link($two_links, word_names::GE, word_names::BE);
        $pri_two = new prior_operand($three_links);
        $unclear_ctx = [word_names::INHABITANTS, word_names::GE];
        $t->assert($test_name, $pri_two->select($names, $unclear_ctx), [$names, $unclear_ctx]);
        $test_name = '... and such a phrase is no first row';
        $is_unclear_first = $pri_two->is_first($names, $unclear_ctx);
        $t->assert($test_name, $is_unclear_first, false);

        $t->subheader($ts . 'expression');

        $test_name = 'formula increase expression';
        $frm = $t_frm->formula_increase();
        $frm_this = $t_frm->formula_this();
        $frm_prior = $t_frm->formula_prior();
        $wrd_pct = $t_wrd->word_percent();
        $trm_lst = $t_trm->term_list_increase();

        // build the expression, which is in this case "percent" = ( "this" - "prior" ) / "prior"
        $exp = $frm->expression($msg, $trm_lst);

        $result = $exp->dsp_id();
        $target = '""' . words::PERCENT . '" = ( "'
            . word_names::THIS_NAME . '" - "'
            . word_names::PRIOR_NAME . '" ) / "'
            . word_names::PRIOR_NAME . '"" ({w'
            . $wrd_pct->id() . '}=({f'
            . $frm_this->id() . '}-{f'
            . $frm_prior->id() . '})/{f'
            . $frm_prior->id() . '})';
        // building the expression above takes longer than a normal unit function, so a page timeout is used
        $t->assert($test_name . ' for ' . $frm->dsp_id(), $result, $target, $t::TIMEOUT_LIMIT_PAGE);

        // build the element group list which is in this case "this" and "prior", but an element group can contain more than one word
        $test_name = 'formula increase: test the element group creation';
        $elm_grp_lst = $exp->element_grp_lst($msg, $trm_lst);
        $result = $elm_grp_lst->dsp_id();
        $target = '"'
            . formula_names::THIS_NAME . '" ('
            . $frm_this->id() . ') / "'
            . formula_names::PRIOR . '" ('
            . $frm_prior->id() . ') / "'
            . formula_names::PRIOR . '" ('
            . $frm_prior->id() . ')';
        $t->dsp_contains($test_name, $target, $result);

        $test_name = 'formula increase; test the display name that can be used for user debugging';
        $frm_html = new formula_ui($frm->api_json());
        $trm_lst_ui = new term_list_ui($trm_lst->api_json());
        $result = $frm_html->dsp_text($msg_ui, [], $trm_lst_ui);
        $frm_edit_url = api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::FORMULA_EDIT_ID . '&amp;id=';
        // the links carry no back part, because the formula is shown without a calling page
        $target = '"' . words::PERCENT
            . '" = ( <a href="' . $frm_edit_url
            . $frm_this->id() . '">'
            . word_names::THIS_NAME
            . '</a> - <a href="' . $frm_edit_url
            . $frm_prior->id()
            . '">'
            . word_names::PRIOR_NAME
            . '</a> ) / <a href="' . $frm_edit_url . '20">'
            . word_names::PRIOR_NAME . '</a>';
        $t->assert($test_name, $result, $target);

        // define the element group object to retrieve the value
        // test the display name that can be used for user debugging
        if (count($elm_grp_lst->lst()) > 0) {
            // get "this" from the formula element group list
            $elm_grp = $elm_grp_lst->lst()[0];
            $elm_grp_ui = new element_group_ui($elm_grp->api_json());
            $result = $elm_grp_ui->dsp_names();
            $target = '<a href="' . $frm_edit_url
                . $frm_this->id() . '">'
                . word_names::THIS_NAME . '</a>';
            $t->assert('element_group->dsp_names', trim($result), trim($target));
        }
        /*
        if (count($elm_grp_lst->lst()) > 0) {
            // get "this" from the formula element group list
            $elm_grp = $elm_grp_lst->lst()[0];
            $fig_lst = $elm_grp->figures($trm_lst);

            $test_name = 'formula increase; test if the values for an element group are displayed correctly';
            $frm_html = new formula_dsp($frm->api_json());
            $trm_lst_ui = new term_list_dsp($trm_lst->api_json());
            $result = $frm_html->dsp_text($msg_ui, [], $trm_lst_ui);
            $target = '<a href="/http/view.php?m=' . views::RESULT_EDIT_ID . '&id=' . rawurlencode(group_id_url::to_url($fig_lst->get_first_id())) . '">8.67</a>';
            $t->assert($test_name, $result, $target);
        }
        */


        // TODO Prio 2 activate
        //$t->assert_true($ts . 'with at least one predefined formula', $t_frm->formula_increase()->is_predefined());
        $t->assert_false($ts . 'without predefined formula', $t_frm->formula()->is_predefined());

        // get the id of the phrases that should be added to the result based on the formula reference text
        $target = new phrase_list($t->usr1);
        $trm_lst = new term_list($t->usr1);
        $frm->set_user($t->usr1);
        $frm_wrd = $t_wrd->word_one();
        $target->add($frm_wrd->phrase());
        $trm_lst->add($frm_wrd->term());
        $exp = new expression($frm);
        $exp->set_ref_text('{w' . word_names::ONE_ID . '}={w' . word_names::MIO_ID . '}*1000000', $msg, $t_trm->term_list_scale());
        $result = $exp->load_result_phrases($msg, $trm_lst);
        $t->assert('Expression->res_phr_lst for ' . formula_names::SCALE_MIO_EXP, $result->dsp_id(), $target->dsp_id());

        // get the special formulas used in a formula to calculate the result
        // e.g. "next" is a special formula to get the following values
        /*
        $frm_next = new formula($t->usr1);
        $frm_next->name = "next";
        $frm_next->type_id = $sys->typ_lst->frm_typ->id(formula_type::NEXT);
        $frm_next->id = 1;
        $frm_has_next = new formula($t->usr1);
        $frm_has_next->usr_text = '=next';
        $t->assert('Expression->res_phr_lst for ' . formulas::TF_SCALE_MIO, $result->dsp_id(), $target->dsp_id());
        */

        $test_name = 'formula term list';
        $frm = $t_frm->formula();
        $trm_lst = $frm->term_list($t_trm->term_list_time(), $msg);
        $t->assert($test_name, $trm_lst->dsp_id(),
            '"' . word_names::MINUTE . '","' . triple_names::SECOND . '" ('
            . $lib->term_id(triple_names::SECOND_ID, triple::class) . ','
            . $lib->term_id(word_names::MINUTE_ID, word::class) . ')');

        // TODO Prio 2 add result display test

        // test the calculation of one value
        $trm_lst = $t->term_list_for_tests(array(
            words::PCT,
            formula_names::THIS_NAME,
            formula_names::PRIOR
        ));
        $phr_lst = $t_phr->phrase_list_increase();

        $frm = $t_frm->formula_increase();
        // TODO Prio 1 activate
        // $res_lst = $frm->to_num($phr_lst);
        //$res = $res_lst->lst[0];
        //$result = $res->num_text;
        $target = '=(' . values::CH_INHABITANTS_2020_IN_MIO . '-' .
            values::CH_INHABITANTS_2019_IN_MIO . ')/' .
            values::CH_INHABITANTS_2019_IN_MIO;
        //$t->assert('get numbers for formula ' . $frm->dsp_id() . ' based on term list ' . $trm_lst->dsp_id(), $result, $target);

        // TODO Prio 2 add calculation test
        $test_name = 'formula city population reference text';
        $frm = $t_frm->formula_city_population();
        $result = $frm->get_ref_text($msg);
        $target = '{w' . words::TOTAL_ID . '}=&sum;({w' . word_names::INHABITANT_ID . '}{v' . verbs::IS_ID . '}{w' . word_names::CITY_ID . '})';
        // building the reference text resolves the phrases, so a page timeout is used to avoid a false timeout
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_PAGE);


        $t->subheader($ts . 'to_num_new');

        // result_can_calc is the gate used by to_num_new to decide if a result can be calculated;
        // when the formula does not require all values it may always be calculated
        $test_name = 'result_can_calc allows the calculation when not all values are needed';
        $frm = $t_frm->formula_increase();
        $frm->need_all_val = false;
        $res = new result($t->usr1);
        $t->assert_true($test_name, $frm->result_can_calc($res));

        // ... but it denies the calculation when all values are needed and one is still missing
        $test_name = 'result_can_calc denies the calculation when a needed value is missing';
        $frm->need_all_val = true;
        $res->val_missing = true;
        $t->assert_false($test_name, $frm->result_can_calc($res));

        // the full to_num_new calculation (load_data_for_calc + filling the figures + parsing the
        // result) needs the values loaded from the database; see the commented to_num test above

    }

}