<?php

/*

    test/php/unit_write/expression.php - write test EXPRESSIONS to the database and check the results
    ----------------------------------
  

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

namespace test\write;

use api\formula\formula_api;
use api\word\word_api;
use cfg\log\expression;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_LONG;
use const test\TIMEOUT_LIMIT_PAGE_LONG;

class expression_test
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->name = 'expression->';

        $t->header('Test the expression class (src/main/php/model/formula/expression.php)');

        $back = '';

        // load formulas for expression testing
        $frm_this = $t->load_formula(formula_api::TN_THIS);
        $frm = $t->load_formula(formula_api::TN_ADD);
        $frm_pe = $t->load_formula(formula_api::TN_RATIO);
        $frm_sector = $t->load_formula(formula_api::TN_SECTOR);

        $result = $frm_sector->usr_text;
        $target = '= "' . word_api::TN_COUNTRY . '" "differentiator" "' . word_api::TN_CANTON . '" / "' . word_api::TN_TOTAL . '"';
        $t->assert('user text', $result, $target, TIMEOUT_LIMIT_PAGE_LONG);

        // create expressions for testing
        $exp = new expression($usr);
        $exp->set_user_text($frm->usr_text);

        $exp_pe = new expression($usr);
        $exp_pe->set_user_text($frm_pe->usr_text);

        $exp_sector = new expression($usr);
        $exp_sector->set_user_text($frm_sector->usr_text);

        // load the test ids
        $wrd_percent = $t->load_word(word_api::TN_PCT);
        $frm_this = $t->load_formula(formula_api::TN_READ_THIS);
        $frm_prior = $t->load_formula(formula_api::TN_READ_PRIOR);

        // test the expression processing of the user readable part
        $target = '"' . word_api::TN_PCT . '"';
        $result = $exp->res_part_usr();
        $t->assert('res_part_usr for "' . $frm->usr_text . '"', $result, $target, TIMEOUT_LIMIT_LONG); // ??? why???
        $target = '( "' . formula_api::TN_READ_THIS . '" - "' . formula_api::TN_READ_PRIOR . '" ) / "' . formula_api::TN_READ_PRIOR . '"';
        $result = $exp->r_part_usr();
        $t->assert('r_part_usr for "' . $frm->usr_text . '"', $result, $target);
        $target = 'true';
        $result = zu_dsp_bool($exp->has_ref());
        $t->assert('has_ref for "' . $frm->usr_text . '"', $result, $target);
        $target = '{w' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '}';
        $result = $exp->ref_text();
        $t->assert('get_ref_text for "' . $frm->usr_text . '"', $result, $target);

        // test the expression processing of the database reference
        $exp_db = new expression($usr);
        $exp_db->set_ref_text('{w' . $wrd_percent->id() . '} = ( is.numeric( {f' . $frm_this->id() . '} ) & is.numeric( {f' . $frm_prior->id() . '} ) ) ( {f' . $frm_this->id() . '} - {f' . $frm_prior->id() . '} ) / {f' . $frm_prior->id() . '}');
        $target = '"percent"=( is.numeric( "' . formula_api::TN_READ_THIS . '" ) & is.numeric( "' . formula_api::TN_READ_PRIOR . '" ) ) ( "' . formula_api::TN_READ_THIS . '" - "' . formula_api::TN_READ_PRIOR . '" ) / "' . formula_api::TN_READ_PRIOR . '"';
        $result = $exp_db->user_text();
        $t->assert('get_usr_text for "' . $exp_db->ref_text() . '"', $result, $target);

        // test getting phrases that should be added to the result of a formula
        $phr_lst_res = $exp->res_phr_lst();
        if ($phr_lst_res != null) {
            $result = $phr_lst_res->dsp_name();
        }
        $target = '"' . word_api::TN_PCT . '"';
        $t->assert('res_phr_lst for "' . $exp->dsp_id() . '"', $result, $target, TIMEOUT_LIMIT_LONG); // ??? why???

        // ... and the phrases used in the formula
        $phr_lst_res = $exp_pe->phr_lst();
        if ($phr_lst_res != null) {
            $result = $phr_lst_res->dsp_name();
        }
        $target = '"' . word_api::TN_EARNING . '","' . word_api::TN_PRICE . '"';
        $t->assert('phr_lst for "' . $exp_pe->dsp_id() . '"', $result, $target);

        // ... and all elements used in the formula
        $elm_lst = $exp_sector->element_list();
        $result = $elm_lst->name();
        $target = '"Country","can be used as a differentiator for","Canton","System Test Word Total"';
        $t->assert('element_lst for "' . $exp_sector->dsp_id() . '"', $result, $target);

        // ... and all element groups used in the formula
        $elm_grp_lst = $exp_sector->element_grp_lst();
        $result = $elm_grp_lst->name();
        $target = '"Country,can be used as a differentiator for,Canton","System Test Word Total"';
        $t->assert('element_grp_lst for "' . $exp_sector->dsp_id() . '"', $result, $target);

        // test getting the phrases if the formula contains a verb
        // not sure if test is correct!
        $phr_lst = $exp_sector->phr_verb_lst();
        $result = $phr_lst->dsp_name();
        $target = '"Canton","Country","System Test Word Total"';
        // TODO $t->assert('phr_verb_lst for "' . $exp_sector->ref_text() . '"', $result, $target);

        // test getting special phrases
        $phr_lst = $exp->element_special_following();
        $result = $phr_lst->dsp_name();
        $target = '"' . formula_api::TN_READ_THIS . '","' . formula_api::TN_READ_PRIOR . '"';
        // TODO $t->assert('element_special_following for "'.$exp->dsp_id().'"', $result, $target, TIMEOUT_LIMIT_LONG);

        // test getting for special phrases the related formula
        $frm_lst = $exp->element_special_following_frm();
        $result = $frm_lst->name();
        $target = '' . formula_api::TN_READ_THIS . ',' . formula_api::TN_READ_PRIOR . '';
        // TODO $t->assert('element_special_following_frm for "'.$exp->dsp_id().'"', $result, $target, TIMEOUT_LIMIT_LONG);

    }

}