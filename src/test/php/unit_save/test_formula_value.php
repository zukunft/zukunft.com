<?php 

/*

  test_formula_value.php - TESTing of the FORMULA VALUE functions
  ----------------------
  

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

function run_formula_value_test () {

  global $usr;

  test_header('Test the formula value class (classes/formula_value.php)');

  // test load result without time
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ABB);
  $phr_lst->add_name(TW_SALES);
  $phr_lst->add_name(TF_INCREASE);
  // why are these two words needed??
  $phr_lst->add_name(TW_MIO);
  //$phr_lst->add_name(TW_CHF);
  $phr_lst->add_name(TW_PCT);
  $abb_up_grp = $phr_lst->get_grp();
  if ($abb_up_grp->id > 0) {
    $abb_up = New formula_value;
    $abb_up->phr_grp_id = $abb_up_grp->id;
    $abb_up->usr = $usr;
    $abb_up->load();
    $result = $abb_up->value;
  } else {
    $result = 'no '.TW_SALES.' '.TF_INCREASE.' value found for '.TW_ABB;
  }
  // todo review
  //$result = $abb_up->phr_grp_id;
  $target = '-0.046588314872749';
  $target = '';
  test_dsp('value->val_formatted ex time for '.$phr_lst->dsp_id().' (group id '.$abb_up_grp->id.')', $target, $result, TIMEOUT_LIMIT_LONG);

  // test load result with time
  $phr_lst->add_name(TW_2014); 
  $phr_lst->load();
  $time_phr = $phr_lst->time_useful();
  $abb_up_grp = $phr_lst->get_grp();
  if ($abb_up_grp->id > 0) {
    $abb_up = New formula_value;
    $abb_up->phr_grp_id = $abb_up_grp->id;
    $abb_up->time_id = $time_phr->id;
    //$abb_up->wrd_lst = $phr_lst;
    $abb_up->usr = $usr;
    $abb_up->usr->id = $usr->id; // temp solution utils the value is saved automatically for all users
    $abb_up->load();
    $result = $abb_up->value;
  } else {
    $result = 'no '.TW_2014.' '.TW_SALES.' '.TF_INCREASE.' value found for '.TW_ABB;
  }
  //$result = $abb_up->phr_grp_id;
  $target = '0.0099235970843945';
  if (isset($time_phr) and isset($phr_lst) and isset($abb_up_grp)) {
    test_dsp('value->val_formatted incl time ('.$time_phr->dsp_id().') for '.$phr_lst->dsp_id().' (group id '.$abb_up_grp->id.')', $target, $result);
  } else {
    test_dsp('value->val_formatted incl time for ', $target, $result);
  }

  // test the scaling
  // test the scaling of a value
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_SALES);
  $wrd_lst->add_name(TW_CHF);
  $wrd_lst->add_name(TW_MIO);
  $wrd_lst->add_name(TW_2014);
  $wrd_lst->load();
  $dest_wrd_lst = New word_list;
  $dest_wrd_lst->usr = $usr;
  $dest_wrd_lst->add_name(TW_SALES);
  $dest_wrd_lst->add_name(TW_K);
  $dest_wrd_lst->load();
  $mio_val = New value;
  $mio_val->ids = $wrd_lst->ids;
  $mio_val->usr = $usr;
  $mio_val->load();
  log_debug('value->scale value loaded');
  //$result = $mio_val->check();
  $result = $mio_val->scale($dest_wrd_lst);
  $target = '46000000000';
  test_dsp('value->val_scaling for a tern list '.$wrd_lst->dsp_id().'', $target, $result, TIMEOUT_LIMIT_PAGE);

  // test getting the "best guess" value
  // e.g. if ABB,Sales,2014 is requested, but there is only a value for ABB,Sales,2014,CHF,million get it
  //      based
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ABB);
  $phr_lst->add_name(TW_SALES);
  $phr_lst->add_name(TW_2014);
  $phr_lst->load();
  $val_best_guess = New value;
  $val_best_guess->ids = $phr_lst->ids;
  $val_best_guess->usr = $usr;
  $val_best_guess->load();
  $result = $val_best_guess->number;
  $target = '46000';
  test_dsp('value->load the best guess for '.$phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

  /* 

  Additional test cases for formula result

  if a user changes a value the result for him should be updated and the result should be user specific
  but the result for other user should not be changed
  if the user undo the value change, the result should be updated

  if the user changes a word link, formula link or formula the result should also be updated

  */

}

function run_formula_value_list_test () {

  global $usr;

  test_header('Test the formula value list class (classes/formula_value_list.php)');

  // todo add PE frm test
  //$frm = load_formula(TF_PE);
  $frm = load_formula(TF_INCREASE);
  $fv_lst = New formula_value_list;
  $fv_lst->frm_id = $frm->id;
  $fv_lst->usr = $usr;
  $fv_lst->load();
  $result = $fv_lst->dsp_id();
  $target = '"Sales","percent","increase","'.word::TEST_NAME_CHANGED.'","2017"';
  test_dsp_contains(', formula_value_list->load of the formula results for '.$frm->dsp_id().' is '.$result.' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

}