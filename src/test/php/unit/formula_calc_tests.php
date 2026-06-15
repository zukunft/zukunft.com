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
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_FORMULA . 'expression.php';
include_once html_paths::ELEMENT . 'element_group.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\formula\expression;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\element\element_group as element_group_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list as term_list_ui;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_calc_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $lib = new library();
        $t_frm = new test_formulas($t);
        $t_wrd = new test_words($t);
        $t_phr = new test_phrases($t);
        $t_trm = new test_terms($t);
        $t->name = 'formula->';
        $t->resource_path = 'db/formula/';

        // start the test section (ts)
        $ts = 'unit formula calc ';
        $t->header($ts);

        $t->subheader($ts . 'expression');

        $test_name = 'formula increase expression';
        $frm = $t_frm->formula_increase();
        $frm_this = $t_frm->formula_this();
        $frm_prior = $t_frm->formula_prior();
        $wrd_pct = $t_wrd->word_percent();
        $trm_lst = $t_trm->term_list_increase();

        // build the expression, which is in this case "percent" = ( "this" - "prior" ) / "prior"
        $exp = $frm->expression($trm_lst);

        $result = $exp->dsp_id();
        $target = '""' . words::PERCENT . '" = ( "'
            . word_names::THIS_NAME . '" - "'
            . word_names::PRIOR_NAME . '" ) / "'
            . word_names::PRIOR_NAME . '"" ({w'
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
        $frm_html = new formula_ui($frm->api_json());
        $trm_lst_ui = new term_list_ui($trm_lst->api_json());
        $back = 0;
        $result = $frm_html->dsp_text($back, $trm_lst_ui);
        $frm_edit_url = api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::FORMULA_EDIT_ID . '&id=';
        $target = '"' . words::PERCENT
            . '" = ( <a href="' . $frm_edit_url
            . $frm_this->id() . '&back=0">'
            . word_names::THIS_NAME
            . '</a> - <a href="' . $frm_edit_url
            . $frm_prior->id()
            . '&back=0">'
            . word_names::PRIOR_NAME
            . '</a> ) / <a href="' . $frm_edit_url . '20&back=0">'
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
            $back = 0;
            $result = $frm_html->dsp_text($back, $trm_lst_ui);
            $target = '<a href="/http/result_edit.php?id=' . $fig_lst->get_first_id() . '" title="8.51">8.51</a>';
            $t->assert($test_name, $result, $target);
        }
        */


        // TODO Prio 2 activate
        //$t->assert_true($ts . 'with at least one predefined formula', $t_frm->formula_increase()->is_predefined());
        $t->assert_false($ts . 'without predefined formula', $t_frm->formula()->is_predefined());

        // get the id of the phrases that should be added to the result based on the formula reference text
        $target = new phrase_list($usr);
        $trm_lst = new term_list($usr);
        $frm->set_user($usr);
        $frm_wrd = $t_wrd->word_one();
        $target->add($frm_wrd->phrase());
        $trm_lst->add($frm_wrd->term());
        $exp = new expression($frm);
        $exp->set_ref_text('{w' . word_names::ONE_ID . '}={w' . word_names::MIO_ID . '}*1000000', $t_trm->term_list_scale());
        $result = $exp->load_result_phrases($trm_lst);
        $t->assert('Expression->res_phr_lst for ' . formulas::SCALE_MIO_EXP, $result->dsp_id(), $target->dsp_id());

        // get the special formulas used in a formula to calculate the result
        // e.g. "next" is a special formula to get the following values
        /*
        $frm_next = new formula($usr);
        $frm_next->name = "next";
        $frm_next->type_id = $sys->typ_lst->frm_typ->id(formula_type::NEXT);
        $frm_next->id = 1;
        $frm_has_next = new formula($usr);
        $frm_has_next->usr_text = '=next';
        $t->assert('Expression->res_phr_lst for ' . formulas::TF_SCALE_MIO, $result->dsp_id(), $target->dsp_id());
        */

        $test_name = 'formula term list';
        $frm = $t_frm->formula();
        $trm_lst = $frm->term_list($t_trm->term_list_time());
        $t->assert($test_name, $trm_lst->dsp_id(),
            '"' . word_names::MINUTE . '","' . triples::SECOND . '" ('
            . $lib->term_id(triples::SECOND_ID, triple::class) . ','
            . $lib->term_id(word_names::MINUTE_ID, word::class) . ')');

        // TODO add result display test

        // test the calculation of one value
        $trm_lst = $t->term_list_for_tests(array(
            words::PCT,
            formulas::THIS_NAME,
            formulas::PRIOR
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
        $result = $frm->get_ref_text();
        $target = '{w' . words::TOTAL_ID . '}=&sum;({w' . word_names::INHABITANT_ID . '}{v' . verbs::IS_ID . '}{w' . word_names::CITY_ID . '})';
        $t->assert($test_name, $result, $target);


        $t->subheader($ts . 'to_num_new');

        // result_can_calc is the gate used by to_num_new to decide if a result can be calculated;
        // when the formula does not require all values it may always be calculated
        $test_name = 'result_can_calc allows the calculation when not all values are needed';
        $frm = $t_frm->formula_increase();
        $frm->need_all_val = false;
        $res = new result($usr);
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