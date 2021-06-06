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

function run_expression_test ($debug) {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the expression class (src/main/php/model/formula/expression.php)');

  $back = '';

  // load formulas for expression testing
  $frm        = load_formula(TF_INCREASE, $debug-1);
  $frm_pe     = load_formula(TF_PE,       $debug-1);
  $frm_sector = load_formula(TF_SECTOR,   $debug-1);

  $result = $frm_sector->usr_text;
  $target = '="Sales" "differentiator" "Sector"/"Total Sales"';
  $exe_start_time = test_show_result(', formula->user text', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);

  // create expressions for testing
  $exp = New expression;
  $exp->usr_text = $frm->usr_text;
  $exp->usr = $usr;
  $exp->ref_text = $exp->get_ref_text ($debug-1);

  $exp_pe = New expression;
  $exp_pe->usr_text = $frm_pe->usr_text;
  $exp_pe->usr = $usr;
  $exp_pe->ref_text = $exp_pe->get_ref_text ($debug-1);

  $exp_sector = New expression;
  $exp_sector->usr_text = $frm_sector->usr_text;
  $exp_sector->usr = $usr;
  $exp_sector->ref_text = $exp_sector->get_ref_text ($debug-1);

  // test the expression processing of the user readable part
  $target = '"percent"';
  $result = $exp->fv_part_usr ();
  $exe_start_time = test_show_result(', expression->fv_part_usr for "'.$frm->usr_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG); // ??? why???
  $target = '( "this" - "prior" ) / "prior"';
  $result = $exp->r_part_usr ();
  $exe_start_time = test_show_result(', expression->r_part_usr for "'.$frm->usr_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $target = 'true';
  $result = zu_dsp_bool($exp->has_ref ($debug-1));
  $exe_start_time = test_show_result(', expression->has_ref for "'.$frm->usr_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $target = '{t19}=({f3}-{f5})/{f5}';
  $result = $exp->get_ref_text ($debug-1);
  $exe_start_time = test_show_result(', expression->get_ref_text for "'.$frm->usr_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test the expression processing of the database reference
  $exp_db = New expression;
  $exp_db->ref_text = '{t19} = ( is.numeric( {f3} ) & is.numeric( {f5} ) ) ( {f3} - {f5} ) / {f5}';
  $exp_db->usr = $usr;
  $target = '{t19}';
  $result = $exp_db->fv_part ();
  $exe_start_time = test_show_result(', expression->fv_part_usr for "'.$exp_db->ref_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $target = '( is.numeric( {f3} ) & is.numeric( {f5} ) ) ( {f3} - {f5} ) / {f5}';
  $result = $exp_db->r_part ();
  $exe_start_time = test_show_result(', expression->r_part_usr for "'.$exp_db->ref_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  $target = '"percent"=( is.numeric( "this" ) & is.numeric( "prior" ) ) ( "this" - "prior" ) / "prior"';
  $result = $exp_db->get_usr_text ($debug-1);
  $exe_start_time = test_show_result(', expression->get_usr_text for "'.$exp_db->ref_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test getting phrases that should be added to the result of a formula
  $phr_lst_fv = $exp->fv_phr_lst ($debug-1);
  $result = $phr_lst_fv->name ($debug-1);
  $target = '"percent"';
  $exe_start_time = test_show_result(', expression->fv_phr_lst for "'.$exp->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG); // ??? why???

  // ... and the phrases used in the formula
  $phr_lst_fv = $exp_pe->phr_lst ($debug-1);
  $result = $phr_lst_fv->name ($debug-1);
  $target = '"Share price"';
  $exe_start_time = test_show_result(', expression->phr_lst for "'.$exp_pe->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... and all elements used in the formula
  $elm_lst = $exp_sector->element_lst ($back,  $debug-1);
  $result = $elm_lst->name ($debug-1);
  $target = 'Sales can be used as a differentiator for Sector Total Sales ';
  $exe_start_time = test_show_result(', expression->element_lst for "'.$exp_sector->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT); 

  // ... and all element groups used in the formula
  $elm_grp_lst = $exp_sector->element_grp_lst ($back, $debug-1);
  $result = $elm_grp_lst->name ($debug-1);
  $target = 'Sales,can be used as a differentiator for,Sector / Total Sales';
  $exe_start_time = test_show_result(', expression->element_grp_lst for "'.$exp_sector->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test getting the phrases if the formula contains a verb
  // not sure if test is correct!
  $phr_lst = $exp_sector->phr_verb_lst($back, $debug-1);
  $result = $phr_lst->name($debug-1);
  $target = '"Sales","Sector","Total Sales"';
  $exe_start_time = test_show_result(', expression->phr_verb_lst for "'.$exp_sector->ref_text.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test getting special phrases
  $phr_lst = $exp->element_special_following ($back, $debug-1);
  $result = $phr_lst->name ($debug-1);
  $target = '"this","prior"';
  $exe_start_time = test_show_result(', expression->element_special_following for "'.$exp->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

  // test getting for special phrases the related formula 
  $frm_lst = $exp->element_special_following_frm ($back, $debug-1);
  $result = $frm_lst->name ();
  $target = 'this,prior';
  $exe_start_time = test_show_result(', expression->element_special_following_frm for "'.$exp->dsp_id().'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_LONG);

}
