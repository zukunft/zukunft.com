<?php

/*

  test_expression.php - TESTing of the EXPRESSION class
  -------------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function run_expression_test(testing $t)
{

    global $usr;

    $t->header('Test the expression class (src/main/php/model/formula/expression.php)');

    $back = '';

    // load formulas for expression testing
    $frm = $t->load_formula(formula::TN_INCREASE);
    $frm_pe = $t->load_formula(formula::TN_RATIO);
    $frm_sector = $t->load_formula(formula::TN_SECTOR);

    $result = $frm_sector->usr_text;
    $target = '= "' . word::TN_COUNTRY . '" "differentiator" "' . word::TN_CANTON . '" / "' . word::TN_TOTAL . '"';
    $t->dsp('formula->user text', $target, $result, TIMEOUT_LIMIT_PAGE_LONG);

    // create expressions for testing
    $exp = new expression;
    $exp->usr_text = $frm->usr_text;
    $exp->usr = $usr;
    $exp->ref_text = $exp->get_ref_text();

    $exp_pe = new expression;
    $exp_pe->usr_text = $frm_pe->usr_text;
    $exp_pe->usr = $usr;
    $exp_pe->ref_text = $exp_pe->get_ref_text();

    $exp_sector = new expression;
    $exp_sector->usr_text = $frm_sector->usr_text;
    $exp_sector->usr = $usr;
    $exp_sector->ref_text = $exp_sector->get_ref_text();

    // test the expression processing of the user readable part
    $target = '"percent"';
    $result = $exp->fv_part_usr();
    $t->dsp('expression->fv_part_usr for "' . $frm->usr_text . '"', $target, $result, TIMEOUT_LIMIT_LONG); // ??? why???
    $target = '( "this" - "prior" ) / "prior"';
    $result = $exp->r_part_usr();
    $t->dsp('expression->r_part_usr for "' . $frm->usr_text . '"', $target, $result);
    $target = 'true';
    $result = zu_dsp_bool($exp->has_ref());
    $t->dsp('expression->has_ref for "' . $frm->usr_text . '"', $target, $result);
    $target = '{t5}=({f18}-{f20})/{f20}';
    $result = $exp->get_ref_text();
    $t->dsp('expression->get_ref_text for "' . $frm->usr_text . '"', $target, $result);

    // test the expression processing of the database reference
    $exp_db = new expression;
    $exp_db->ref_text = '{t5} = ( is.numeric( {f18} ) & is.numeric( {f20} ) ) ( {f18} - {f20} ) / {f20}';
    $exp_db->usr = $usr;
    $target = '{t5}';
    $result = $exp_db->fv_part();
    $t->dsp('expression->fv_part_usr for "' . $exp_db->ref_text . '"', $target, $result);
    $target = '( is.numeric( {f18} ) & is.numeric( {f20} ) ) ( {f18} - {f20} ) / {f20}';
    $result = $exp_db->r_part();
    $t->dsp('expression->r_part_usr for "' . $exp_db->ref_text . '"', $target, $result);
    $target = '"percent"=( is.numeric( "this" ) & is.numeric( "prior" ) ) ( "this" - "prior" ) / "prior"';
    $result = $exp_db->get_usr_text();
    $t->dsp('expression->get_usr_text for "' . $exp_db->ref_text . '"', $target, $result);

    // test getting phrases that should be added to the result of a formula
    $phr_lst_fv = $exp->fv_phr_lst();
    $result = $phr_lst_fv->name();
    $target = '"percent"';
    $t->dsp('expression->fv_phr_lst for "' . $exp->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_LONG); // ??? why???

    // ... and the phrases used in the formula
    $phr_lst_fv = $exp_pe->phr_lst();
    $result = $phr_lst_fv->name();
    $target = '"System Test Word Share Price","System Test Word Earnings"';
    $t->dsp('expression->phr_lst for "' . $exp_pe->dsp_id() . '"', $target, $result);

    // ... and all elements used in the formula
    $elm_lst = $exp_sector->element_lst($back,);
    $result = $elm_lst->name();
    $target = 'System Test Word Parent e.g. Country can be used as a differentiator for System Test Word Category e.g. Canton System Test Word Total ';
    $t->dsp('expression->element_lst for "' . $exp_sector->dsp_id() . '"', $target, $result);

    // ... and all element groups used in the formula
    $elm_grp_lst = $exp_sector->element_grp_lst($back);
    $result = $elm_grp_lst->name();
    $target = 'System Test Word Parent e.g. Country,can be used as a differentiator for,System Test Word Category e.g. Canton / System Test Word Total';
    $t->dsp('expression->element_grp_lst for "' . $exp_sector->dsp_id() . '"', $target, $result);

    // test getting the phrases if the formula contains a verb
    // not sure if test is correct!
    $phr_lst = $exp_sector->phr_verb_lst($back);
    $result = $phr_lst->name();
    $target = '"System Test Word Parent e.g. Country","System Test Word Category e.g. Canton","System Test Word Total"';
    $t->dsp('expression->phr_verb_lst for "' . $exp_sector->ref_text . '"', $target, $result);

    // test getting special phrases
    $phr_lst = $exp->element_special_following($back);
    $result = $phr_lst->name();
    $target = '"this","prior"';
    // TODO $t->dsp('expression->element_special_following for "'.$exp->dsp_id().'"', $target, $result, TIMEOUT_LIMIT_LONG);

    // test getting for special phrases the related formula
    $frm_lst = $exp->element_special_following_frm($back);
    $result = $frm_lst->name();
    $target = 'this,prior';
    // TODO $t->dsp('expression->element_special_following_frm for "'.$exp->dsp_id().'"', $target, $result, TIMEOUT_LIMIT_LONG);

}
