<?php

/*

    test/php/unit_write/expression_tests.php - write test EXPRESSIONS to the database and check the results
    ----------------------------------------
  

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

namespace unit_write;

use cfg\formula\expression;
use shared\const\formulas;
use shared\const\words;
use test\test_cleanup;

class expression_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->name = 'expression->';

        $t->header('Test the expression class (src/main/php/model/formula/expression.php)');

        $t->subheader('prepare expression write');
        $wrd_price = $t->test_word(words::TEST_PRICE);
        $wrd_earning = $t->test_word(words::TEST_EARNING);
        $wrd_pe = $t->test_word(words::TEST_PE);
        $frm_ratio = $t->test_formula(formulas::SYSTEM_TEXT_RATIO, formulas::SYSTEM_TEXT_RATIO_EXP);
        $wrd_total = $t->test_word(words::TEST_TOTAL);
        $frm_sector = $t->test_formula(formulas::SYSTEM_TEXT_SECTOR, formulas::SYSTEM_TEXT_SECTOR_EXP);

        $back = '';

        // load formulas for expression testing
        $frm_this = $t->load_formula(formulas::SYSTEM_TEXT_THIS);
        $frm = $t->load_formula(formulas::INCREASE);
        $frm_pe = $t->load_formula(formulas::SYSTEM_TEXT_RATIO);

        $result = $frm_sector->usr_text;
        $target = '= "' . words::COUNTRY . '" "differentiator" "' . words::CANTON . '" / "' . words::TEST_TOTAL . '"';
        $t->assert('user text', $result, $target, $t::TIMEOUT_LIMIT_PAGE_LONG);

        // create expressions for testing
        $exp = new expression($usr);
        $exp->set_user_text($frm->usr_text);

        $exp_pe = new expression($usr);
        $exp_pe->set_user_text($frm_pe->usr_text);

        $exp_sector = new expression($usr);
        $exp_sector->set_user_text($frm_sector->usr_text);

        // load the test ids
        $wrd_percent = $t->load_word(words::PCT);
        $frm_this = $t->load_formula(formulas::THIS_NAME);
        $frm_prior = $t->load_formula(formulas::PRIOR);

        // test the expression processing of the user readable part
        $target = '"' . words::PCT . '"';
        $result = $exp->res_part_usr();
        $t->assert('res_part_usr for "' . $frm->usr_text . '"', $result, $target, $t::TIMEOUT_LIMIT_LONG); // ??? why???
        $target = '( "' . formulas::THIS_NAME . '" - "' . formulas::PRIOR . '" ) / "' . formulas::PRIOR . '"';
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
        $target = '"' . words::PERCENT . '"=( is.numeric( "' . formulas::THIS_NAME . '" ) & is.numeric( "' . formulas::PRIOR . '" ) ) ( "' . formulas::THIS_NAME . '" - "' . formulas::PRIOR . '" ) / "' . formulas::PRIOR . '"';
        $result = $exp_db->user_text();
        $t->assert('get_usr_text for "' . $exp_db->ref_text() . '"', $result, $target);

        // test getting phrases that should be added to the result of a formula
        $phr_lst_res = $exp->result_phrases();
        if ($phr_lst_res != null) {
            $result = $phr_lst_res->dsp_name();
        }
        $target = '"' . words::PCT . '"';
        $t->assert('res_phr_lst for "' . $exp->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_LONG); // ??? why???

        // ... and the phrases used in the formula
        $phr_lst_res = $exp_pe->phr_lst();
        if ($phr_lst_res != null) {
            $result = $phr_lst_res->dsp_name();
        }
        $target = '"' . words::TEST_EARNING . '","' . words::TEST_PRICE . '"';
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
        $target = '"' . formulas::THIS_NAME . '","' . formulas::PRIOR . '"';
        // TODO $t->assert('element_special_following for "'.$exp->dsp_id().'"', $result, $target, $t::TIMEOUT_LIMIT_LONG);

        // test getting for special phrases the related formula
        $frm_lst = $exp->element_special_following_frm();
        $result = $frm_lst->name();
        $target = '' . formulas::THIS_NAME . ',' . formulas::PRIOR . '';
        // TODO $t->assert('element_special_following_frm for "'.$exp->dsp_id().'"', $result, $target, $t::TIMEOUT_LIMIT_LONG);

        $t->subheader('cleanup expression write');
        $frm_ratio->del();
        $wrd_price->del();
        $wrd_earning->del();
        $wrd_pe->del();
        $frm_sector->del();
        $wrd_total->del();

    }

}