<?php

/*

  test_formula.php - TESTing of the FORMULA class
  ----------------
  

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

function create_test_formulas()
{
    test_header('Check if all base formulas are correct');

    test_formula(formula::TN_RATIO, formula::TF_RATIO);
    test_formula(formula::TN_SECTOR, formula::TF_SECTOR);
    test_formula(formula::TN_INCREASE, formula::TF_INCREASE);
    test_formula(formula::TN_SCALE_K, formula::TF_SCALE_K);
    test_formula(formula::TN_SCALE_TO_K, formula::TF_SCALE_TO_K);
    test_formula(formula::TN_SCALE_MIO, formula::TF_SCALE_MIO);
    test_formula(formula::TN_SCALE_BIL, formula::TF_SCALE_BIL);
}

function run_formula_test()
{

    global $usr;
    global $usr2;

    test_header('Test the formula class (classes/formula.php)');

    $back = 0;

    // test loading of one formula
    $frm = new formula;
    $frm->usr = $usr;
    $frm->name = formula::TN_INCREASE;
    $frm->load();
    $result = $frm->usr_text;
    $target = '"percent" = ( "this" - "prior" ) / "prior"';
    test_dsp('formula->load for "' . $frm->name . '"', $target, $result);

    // test the formula type
    $result = zu_dsp_bool($frm->is_special());
    $target = zu_dsp_bool(false);
    test_dsp('formula->is_special for "' . $frm->name . '"', $target, $result);

    $exp = $frm->expression();
    $frm_lst = $exp->element_special_following_frm($back);
    $phr_lst = new phrase_list;
    if ($frm_lst->lst != null) {
        if (count($frm_lst->lst) > 0) {
            $elm_frm = $frm_lst->lst[0];
            $result = zu_dsp_bool($elm_frm->is_special());
            $target = zu_dsp_bool(true);
            test_dsp('formula->is_special for "' . $elm_frm->name . '"', $target, $result);

            $phr_lst->usr = $usr;
            $phr_lst->add_name(word::TN_CH);
            $phr_lst->add_name(word::TN_INHABITANT);
            $phr_lst->add_name(word::TN_2019);
            $phr_lst->load();
            $time_phr = $phr_lst->time_useful();
            $val = $elm_frm->special_result($phr_lst, $time_phr);
            $result = $val->number;
            $target = word::TN_2019;
            // todo: get the best matching number
            //test_dsp('formula->special_result for "'.$elm_frm->name.'"', $target, $result);

            if (count($frm_lst->lst) > 1) {
                //$elm_frm_next = $frm_lst->lst[1];
                $elm_frm_next = $elm_frm;
            } else {
                $elm_frm_next = $elm_frm;
            }
            $time_phr = $elm_frm_next->special_time_phr($time_phr);
            $result = $time_phr->name;
            $target = word::TN_2019;
            test_dsp('formula->special_time_phr for "' . $elm_frm_next->name . '"', $target, $result);
        }
    }

    $phr_lst = $frm->special_phr_lst($phr_lst);
    if (!isset($phr_lst)) {
        $result = '';
    } else {
        $result = $phr_lst->name();
    }
    $target = '"' . word::TN_CH . '","' . word::TN_INHABITANT . '","' . word::TN_2019 . '"';
    test_dsp('formula->special_phr_lst for "' . $frm->name . '"', $target, $result);

    $phr_lst = $frm->assign_phr_lst_direct();
    if (!isset($phr_lst)) {
        $result = '';
    } else {
        $result = $phr_lst->name();
    }
    $target = '"System Test Time Word Category e.g. Year"';
    test_dsp('formula->assign_phr_lst_direct for "' . $frm->name . '"', $target, $result);

    $phr_lst = $frm->assign_phr_ulst_direct();
    if (!isset($phr_lst)) {
        $result = '';
    } else {
        $result = $phr_lst->name();
    }
    $target = '"System Test Time Word Category e.g. Year"';
    test_dsp('formula->assign_phr_ulst_direct for "' . $frm->name . '"', $target, $result);

    // loading another formula (Price Earning ratio ) to have more test cases
    $frm_pe = load_formula(formula::TN_RATIO);

    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_SHARE);
    $phr_lst->add_name(word::TN_CHF);
    $phr_lst->load();

    $phr_lst_all = $frm_pe->assign_phr_lst();
    $phr_lst = $phr_lst_all->filter($phr_lst);
    $result = $phr_lst->name();
    $target = '"' . word::TN_SHARE . '"';
    test_dsp('formula->assign_phr_lst for "' . $frm->name . '"', $target, $result);

    $phr_lst_all = $frm_pe->assign_phr_ulst();
    $phr_lst = $phr_lst_all->filter($phr_lst);
    $result = $phr_lst->name();
    $target = '"' . word::TN_SHARE . '"';
    test_dsp('formula->assign_phr_ulst for "' . $frm->name . '"', $target, $result);

    // test the calculation of one value
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_CH);
    $phr_lst->add_name(word::TN_INHABITANT);
    $phr_lst->add_name(word::TN_2020);
    // why is this word needed??
    $phr_lst->add_name(word::TN_MIO);
    $phr_lst->load();

    $frm = load_formula(formula::TN_INCREASE);
    $fv_lst = $frm->to_num($phr_lst, $back);
    if (isset($fv_lst->lst)) {
        $fv = $fv_lst->lst[0];
        $result = $fv->num_text;
    } else {
        $fv = null;
        $result = 'result list is empty';
    }
    $target = '=(8.505251-8.438822)/8.438822';
    test_dsp('formula->to_num "' . $frm->name . '" for a tern list ' . $phr_lst->dsp_id() . '', $target, $result);

    if ($fv_lst->lst != null) {
        $fv->save_if_updated();
        $result = $fv->value;
        $target = '0.0078718332961637';
        test_dsp('formula_value->save_if_updated "' . $frm->name . '" for a tern list ' . $phr_lst->dsp_id() . '', $target, $result);
    }

    $fv_lst = $frm->calc($phr_lst, $back);
    if (isset($fv_lst)) {
        $result = $fv_lst[0]->value;
    } else {
        $result = '';
    }
    $target = '0.0078718332961637';
    test_dsp('formula->calc "' . $frm->name . '" for a tern list ' . $phr_lst->dsp_id() . '', $target, $result);

    // test the scaling mainly to check the scaling handling of the results later
    // TODO remove any scaling words from the phrase list if the result word is of type scaling
    // TODO automatically check the fastest way to scale and avoid double scaling calculations
    $frm_scale_mio_to_one = load_formula(formula::TN_SCALE_MIO);
    $fv_lst = $frm_scale_mio_to_one->calc($phr_lst, $back);
    if (isset($fv_lst)) {
        $result = $fv_lst[0]->value;
    } else {
        $result = '';
    }
    $target = '8505251.0';
    test_dsp('formula->calc "' . $frm->name . '" for a tern list ' . $phr_lst->dsp_id() . '', $target, $result);

    // test the scaling back to thousand
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_CH);
    $phr_lst->add_name(word::TN_INHABITANT);
    $phr_lst->add_name(word::TN_2020);
    // why is this word needed??
    $phr_lst->add_name(word::TN_ONE);
    $phr_lst->load();
    $frm_scale_one_to_k = load_formula(formula::TN_SCALE_TO_K);
    $fv_lst = $frm_scale_one_to_k->calc($phr_lst, $back);
    if (isset($fv_lst)) {
        $result = $fv_lst[0]->value;
    } else {
        $result = '';
    }
    $target = 8505.251;
    test_dsp('formula->calc "' . $frm->name . '" for a tern list ' . $phr_lst->dsp_id() . '', $target, $result);


    // test the display functions
    $frm = load_formula(formula::TN_INCREASE);
    $frm_dsp = $frm->dsp_obj();
    $exp = $frm->expression();
    $result = $exp->dsp_id();
    $target = '""percent" = ( "this" - "prior" ) / "prior"" ({t5}=({f18}-{f20})/{f20})';
    test_dsp('formula->expression for ' . $frm->dsp_id() . '', $target, $result);

    $result = $frm->name;
    $target = 'System Test Formula Increase';
    test_dsp('formula->name for ' . $frm->dsp_id() . '', $target, $result);

    $result = $frm_dsp->dsp_text($back);
    $target = '"percent" = ( <a href="/http/formula_edit.php?id=18&back=0">this</a> - <a href="/http/formula_edit.php?id=20&back=0">prior</a> ) / <a href="/http/formula_edit.php?id=20&back=0">prior</a>';
    test_dsp('formula->dsp_text for ' . $frm->dsp_id() . '', $target, $result);

    $frm_increase = load_formula(formula::TN_INCREASE);
    $result = $frm_dsp->name_linked($back);
    $target = '<a href="/http/formula_edit.php?id=' . $frm_increase->id . '&back=0">' . formula::TN_INCREASE . '</a>';
    test_dsp('formula->display for ' . $frm->dsp_id() . '', $target, $result);

    $wrd = new word_dsp;
    $wrd->usr = $usr;
    $wrd->name = word::TN_CH;
    $wrd->load();
    $result = trim($frm_dsp->dsp_result($wrd, $back));
    $target = '0.79 %';
    $target = '0.01';
    test_dsp('formula->dsp_result for ' . $frm->dsp_id() . ' and ' . $wrd->name() . '', $target, $result);

    /* TODO reactivate
    $result = $frm->btn_edit();
    $target = '<a href="/http/formula_edit.php?id=52&back=" title="Change formula increase"><img src="../images/button_edit.svg" alt="Change formula increase"></a>';
    $target = 'data-icon="edit"';
    test_dsp_contains(', formula->btn_edit for '.$frm->name().'', $target, $result);
    */

    $page = 1;
    $size = 20;
    $call = '/http/test.php';
    $result = $frm_dsp->dsp_hist($page, $size, $call, $back);
    $target = 'changed to';
    test_dsp_contains(', formula->dsp_hist for ' . $frm->dsp_id() . '', $target, $result);

    $result = $frm_dsp->dsp_hist_links($page, $size, $call, $back);
    $target = 'link';
    //$result = $hist_page;
    test_dsp_contains(', formula->dsp_hist_links for ' . $frm->dsp_id() . '', $target, $result);

    $add = 0;
    $result = $frm_dsp->dsp_edit($add, $wrd, $back);
    $target = 'Formula "System Test Formula Increase"';
    //$result = $edit_page;
    test_dsp_contains(', formula->dsp_edit for ' . $frm->dsp_id() . '', $target, $result, TIMEOUT_LIMIT_PAGE);

    // test formula refresh functions

    $result = $frm->element_refresh($frm->ref_text);
    $target = true;
    test_dsp('formula->element_refresh for ' . $frm->dsp_id() . '', $target, $result);


    // to link and unlink a formula is tested in the formula_link section

    // test adding of one formula
    $frm = new formula;
    $frm->name = formula::TN_ADD;
    $frm->usr_text = '"percent" = ( "this" - "prior" ) / "prior"';
    $frm->usr = $usr;
    $result = $frm->save();
    if ($frm->id > 0) {
        $result = $frm->usr_text;
    }
    $target = '"percent" = ( "this" - "prior" ) / "prior"';
    test_dsp('formula->save for adding "' . $frm->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the formula name has been saved
    $frm = load_formula(formula::TN_ADD);
    $result = $frm->usr_text;
    $target = '"percent" = ( "this" - "prior" ) / "prior"';
    test_dsp('formula->load the added "' . $frm->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI); // time limit???

    // ... check the correct logging
    $log = new user_log;
    $log->table = 'formulas';
    $log->field = 'formula_name';
    $log->row_id = $frm->id;
    $log->usr = $usr;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added System Test Formula';
    test_dsp('formula->save adding logged for "' . formula::TN_ADD . '"', $target, $result);

    // check if adding the same formula again creates a correct error message
    $frm = new formula;
    $frm->name = formula::TN_ADD;
    $frm->usr_text = '"percent" = 1 - ( "this" / "prior" )';
    $frm->usr = $usr;
    $result = $frm->save();
    // use the next line if system config is non standard
    //$target = 'A formula with the name "'.formula::TN_ADD.'" already exists. Please use another name.';
    $target = '';
    test_dsp('formula->save adding "' . $frm->name . '" again', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the formula can be renamed
    $frm = load_formula(formula::TN_ADD);
    $frm->name = formula::TN_RENAMED;
    $result = $frm->save();
    $target = '';
    test_dsp('formula->save rename "' . formula::TN_ADD . '" to "' . formula::TN_RENAMED . '".', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... and if the formula renaming was successful
    $frm_renamed = new formula;
    $frm_renamed->name = formula::TN_RENAMED;
    $frm_renamed->usr = $usr;
    $frm_renamed->load();
    if ($frm_renamed->id > 0) {
        $result = $frm_renamed->name;
    }
    $target = formula::TN_RENAMED;
    test_dsp('formula->load renamed formula "' . formula::TN_RENAMED . '"', $target, $result);

    // ... and if the formula renaming has been logged
    $log = new user_log;
    $log->table = 'formulas';
    $log->field = 'formula_name';
    $log->row_id = $frm_renamed->id;
    $log->usr = $usr;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test changed System Test Formula to System Test Formula Renamed';
    test_dsp('formula->save rename logged for "' . formula::TN_RENAMED . '"', $target, $result);

    // check if the formula parameters can be added
    $frm_renamed->usr_text = '= "this"';
    $frm_renamed->description = formula::TN_RENAMED . ' description';
    $frm_renamed->type_id = cl(db_cl::FORMULA_TYPE, formula::THIS);
    $frm_renamed->need_all_val = True;
    $result = $frm_renamed->save();
    $target = '';
    test_dsp('formula->save all formula fields beside the name for "' . formula::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... and if the formula parameters have been added
    $frm_reloaded = load_formula(formula::TN_RENAMED);
    $result = $frm_reloaded->usr_text;
    $target = '= "this"';
    test_dsp('formula->load usr_text for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_reloaded->ref_text;
    $target = '={f18}';
    test_dsp('formula->load ref_text for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_reloaded->description;
    $target = formula::TN_RENAMED . ' description';
    test_dsp('formula->load description for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_reloaded->type_id;
    $target = cl(db_cl::FORMULA_TYPE, formula::THIS);
    test_dsp('formula->load type_id for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_reloaded->need_all_val;
    $target = True;
    test_dsp('formula->load need_all_val for "' . formula::TN_RENAMED . '"', $target, $result);

    // ... and if the formula parameter adding have been logged
    $log = new user_log;
    $log->table = 'formulas';
    $log->field = 'resolved_text';
    $log->row_id = $frm_reloaded->id;
    $log->usr = $usr;
    $result = $log->dsp_last(true);
    // use the next line if system config is non standard
    $target = 'zukunft.com system test changed "percent" = ( "this" - "prior" ) / "prior" to = "this"';
    $target = 'zukunft.com system test changed "percent" = 1 - ( "this" / "prior" ) to = "this"';
    test_dsp('formula->load resolved_text for "' . formula::TN_RENAMED . '" logged', $target, $result);
    $log->field = 'formula_text';
    $result = $log->dsp_last(true);
    // use the next line if system config is non standard
    $target = 'zukunft.com system test changed {t5}=( {f18} - {f5} ) / {f5} to ={f3}';
    $target = 'zukunft.com system test changed {t5}=1-({f18}/{f20}) to ={f18}';
    test_dsp('formula->load formula_text for "' . formula::TN_RENAMED . '" logged', $target, $result);
    $log->field = 'description';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added System Test Formula Renamed description';
    test_dsp('formula->load description for "' . formula::TN_RENAMED . '" logged', $target, $result);
    $log->field = 'formula_type_id';
    $result = $log->dsp_last(true);
    // to review what is correct
    $target = 'zukunft.com system test changed calc to this';
    $target = 'zukunft.com system test added this';
    test_dsp('formula->load formula_type_id for "' . formula::TN_RENAMED . '" logged', $target, $result);
    $log->field = 'all_values_needed';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test changed 0 to 1';
    test_dsp('formula->load all_values_needed for "' . formula::TN_RENAMED . '" logged', $target, $result);

    // check if a user specific formula is created if another user changes the formula
    $frm_usr2 = new formula;
    $frm_usr2->name = formula::TN_RENAMED;
    $frm_usr2->usr = $usr2;
    $frm_usr2->load();
    $frm_usr2->usr_text = '"percent" = ( "this" - "prior" ) / "prior"';
    $frm_usr2->description = formula::TN_RENAMED . ' description2';
    $frm_usr2->type_id = cl(db_cl::FORMULA_TYPE, formula::NEXT);
    $frm_usr2->need_all_val = False;
    $result = $frm_usr2->save();
    $target = '';
    test_dsp('formula->save all formula fields for user 2 beside the name for "' . formula::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... and if a user specific formula changes have been saved
    $frm_usr2_reloaded = new formula;
    $frm_usr2_reloaded->name = formula::TN_RENAMED;
    $frm_usr2_reloaded->usr = $usr2;
    $frm_usr2_reloaded->load();
    $result = $frm_usr2_reloaded->usr_text;
    $target = '"percent" = ( "this" - "prior" ) / "prior"';
    test_dsp('formula->load usr_text for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_usr2_reloaded->ref_text;
    $target = '{t5}=({f18}-{f20})/{f20}';
    test_dsp('formula->load ref_text for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_usr2_reloaded->description;
    $target = formula::TN_RENAMED . ' description2';
    test_dsp('formula->load description for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_usr2_reloaded->type_id;
    $target = cl(db_cl::FORMULA_TYPE, formula::NEXT);
    test_dsp('formula->load type_id for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_usr2_reloaded->need_all_val;
    $target = False;
    test_dsp('formula->load need_all_val for "' . formula::TN_RENAMED . '"', $target, $result);

    // ... and the formula for the original user remains unchanged
    $frm_reloaded = load_formula(formula::TN_RENAMED);
    $result = $frm_reloaded->usr_text;
    $target = '= "this"';
    test_dsp('formula->load usr_text for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_reloaded->ref_text;
    $target = '={f18}';
    test_dsp('formula->load ref_text for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_reloaded->description;
    $target = formula::TN_RENAMED . ' description';
    test_dsp('formula->load description for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_reloaded->type_id;
    $target = cl(db_cl::FORMULA_TYPE, formula::THIS);
    test_dsp('formula->load type_id for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_reloaded->need_all_val;
    $target = True;
    test_dsp('formula->load need_all_val for "' . formula::TN_RENAMED . '"', $target, $result);

    // check if undo all specific changes removes the user formula
    $frm_usr2 = new formula;
    $frm_usr2->name = formula::TN_RENAMED;
    $frm_usr2->usr = $usr2;
    $frm_usr2->load();
    $frm_usr2->usr_text = '= "this"';
    $frm_usr2->description = formula::TN_RENAMED . ' description';
    $frm_usr2->type_id = cl(db_cl::FORMULA_TYPE, formula::THIS);
    $frm_usr2->need_all_val = True;
    $result = $frm_usr2->save();
    $target = '';
    test_dsp('formula->save undo the user formula fields beside the name for "' . formula::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... and if a user specific formula changes have been saved
    $frm_usr2_reloaded = new formula;
    $frm_usr2_reloaded->name = formula::TN_RENAMED;
    $frm_usr2_reloaded->usr = $usr2;
    $frm_usr2_reloaded->load();
    $result = $frm_usr2_reloaded->usr_text;
    $target = '= "this"';
    test_dsp('formula->load usr_text for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_usr2_reloaded->ref_text;
    $target = '={f18}';
    test_dsp('formula->load ref_text for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_usr2_reloaded->description;
    $target = formula::TN_RENAMED . ' description';
    test_dsp('formula->load description for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_usr2_reloaded->type_id;
    $target = cl(db_cl::FORMULA_TYPE, formula::THIS);
    test_dsp('formula->load type_id for "' . formula::TN_RENAMED . '"', $target, $result);
    $result = $frm_usr2_reloaded->need_all_val;
    $target = True;
    test_dsp('formula->load need_all_val for "' . formula::TN_RENAMED . '"', $target, $result);

    // redo the user specific formula changes
    // check if the user specific changes can be removed with one click

    // check for formulas also that

}

function run_formula_list_test()
{

    global $usr;

    test_header('est the formula list class (classes/formula_list.php)');

    // load the main test word
    $wrd_share = test_word(word::TN_SHARE);

    $wrd = new word;
    $wrd->id = $wrd_share->id;
    $wrd->usr = $usr;
    $wrd->load();
    $frm_lst = new formula_list;
    $frm_lst->wrd = $wrd;
    $frm_lst->usr = $usr;
    $frm_lst->back = $wrd->id;
    $frm_lst->load();
    $result = $frm_lst->display();
    $target = formula::TN_RATIO;
    test_dsp_contains(', formula_list->load formula for word "' . $wrd->dsp_id() . '" should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

}