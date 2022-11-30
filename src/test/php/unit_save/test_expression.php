<?php

/*

  test_expression.php - TESTing of the EXPRESSION class
  -------------------
  

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

function run_expression_test(testing $t): void
{

    global $usr;

    // init
    $t->name = 'expression->';

    $t->header('Test the expression class (src/main/php/model/formula/expression.php)');

    $back = '';

    // load formulas for expression testing
    $frm_this = $t->load_formula(formula::TN_THIS);
    $frm = $t->load_formula(formula::TN_INCREASE);
    $frm_pe = $t->load_formula(formula::TN_RATIO);
    $frm_sector = $t->load_formula(formula::TN_SECTOR);

    $result = $frm_sector->usr_text;
    $target = '= "' . word::TN_COUNTRY . '" "differentiator" "' . word::TN_CANTON . '" / "' . word::TN_TOTAL . '"';
    $t->assert('user text', $result, $target, TIMEOUT_LIMIT_PAGE_LONG);

    // create expressions for testing
    $exp = new expression($usr);
    $exp->usr_text = $frm->usr_text;
    $exp->ref_text = $exp->get_ref_text();

    $exp_pe = new expression($usr);
    $exp_pe->usr_text = $frm_pe->usr_text;
    $exp_pe->ref_text = $exp_pe->get_ref_text();

    $exp_sector = new expression($usr);
    $exp_sector->usr_text = $frm_sector->usr_text;
    $exp_sector->ref_text = $exp_sector->get_ref_text();

    // load the test ids
    $wrd_percent = $t->load_word('percent');
    $frm_this = $t->load_formula('this');
    $frm_prior = $t->load_formula('prior');

    // test the expression processing of the user readable part
    $target = '"percent"';
    $result = $exp->fv_part_usr();
    $t->assert('fv_part_usr for "' . $frm->usr_text . '"', $result, $target, TIMEOUT_LIMIT_LONG); // ??? why???
    $target = '( "this" - "prior" ) / "prior"';
    $result = $exp->r_part_usr();
    $t->assert('r_part_usr for "' . $frm->usr_text . '"', $result, $target);
    $target = 'true';
    $result = zu_dsp_bool($exp->has_ref());
    $t->assert('has_ref for "' . $frm->usr_text . '"', $result, $target);
    $target = '{t' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '}';
    $result = $exp->get_ref_text();
    $t->assert('get_ref_text for "' . $frm->usr_text . '"', $result, $target);

    // test the expression processing of the database reference
    $exp_db = new expression($usr);
    $exp_db->ref_text = '{t' . $wrd_percent->id() . '} = ( is.numeric( {f' . $frm_this->id() . '} ) & is.numeric( {f' . $frm_prior->id() . '} ) ) ( {f' . $frm_this->id() . '} - {f' . $frm_prior->id() . '} ) / {f' . $frm_prior->id() . '}';
    $target = '"percent"=( is.numeric( "this" ) & is.numeric( "prior" ) ) ( "this" - "prior" ) / "prior"';
    $result = $exp_db->get_usr_text();
    $t->assert('get_usr_text for "' . $exp_db->ref_text . '"', $result, $target);

    // test getting phrases that should be added to the result of a formula
    $phr_lst_fv = $exp->fv_phr_lst();
    if ($phr_lst_fv != null) {
        $result = $phr_lst_fv->dsp_name();
    }
    $target = '"' . word::TN_READ_PERCENT . '"';
    $t->assert('fv_phr_lst for "' . $exp->dsp_id() . '"', $result, $target, TIMEOUT_LIMIT_LONG); // ??? why???

    // ... and the phrases used in the formula
    $phr_lst_fv = $exp_pe->phr_lst();
    if ($phr_lst_fv != null) {
        $result = $phr_lst_fv->dsp_name();
    }
    $target = '"' . word::TN_EARNING . '","' . word::TN_PRICE . '"';
    $t->assert('phr_lst for "' . $exp_pe->dsp_id() . '"', $result, $target);

    // ... and all elements used in the formula
    $elm_lst = $exp_sector->element_lst($back,);
    $result = $elm_lst->name();
    $target = 'System Test Word Parent e.g. Country can be used as a differentiator for System Test Word Category e.g. Canton System Test Word Total ';
    $t->assert('element_lst for "' . $exp_sector->dsp_id() . '"', $result, $target);

    // ... and all element groups used in the formula
    $elm_grp_lst = $exp_sector->element_grp_lst($back);
    $result = $elm_grp_lst->name();
    $target = 'System Test Word Parent e.g. Country,can be used as a differentiator for,System Test Word Category e.g. Canton / System Test Word Total';
    $t->assert('element_grp_lst for "' . $exp_sector->dsp_id() . '"', $result, $target);

    // test getting the phrases if the formula contains a verb
    // not sure if test is correct!
    $phr_lst = $exp_sector->phr_verb_lst($back);
    $result = $phr_lst->dsp_name();
    $target = '"System Test Word Category e.g. Canton","System Test Word Parent e.g. Country","System Test Word Total"';
    $t->assert('phr_verb_lst for "' . $exp_sector->ref_text . '"', $result, $target);

    // test getting special phrases
    $phr_lst = $exp->element_special_following($back);
    $result = $phr_lst->dsp_name();
    $target = '"this","prior"';
    // TODO $t->assert('element_special_following for "'.$exp->dsp_id().'"', $result, $target, TIMEOUT_LIMIT_LONG);

    // test getting for special phrases the related formula
    $frm_lst = $exp->element_special_following_frm($back);
    $result = $frm_lst->name();
    $target = 'this,prior';
    // TODO $t->assert('element_special_following_frm for "'.$exp->dsp_id().'"', $result, $target, TIMEOUT_LIMIT_LONG);

}
