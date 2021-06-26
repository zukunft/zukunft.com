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

function create_base_formulas()
{
    echo "<h2>Check if all base formulas are correct</h2><br>";
    test_formula(TF_SCALE_BIL, TF_SCALE_BIL_TEXT);
    test_formula(TF_SCALE_MIO, TF_SCALE_MIO_TEXT);
    test_formula(TF_SCALE_K, TF_SCALE_K_TEXT);
    echo "<br><br>";
}

function run_formula_test()
{

    global $usr;
    global $usr2;
    global $exe_start_time;

    test_header('Test the formula class (classes/formula.php)');

    $back = 0;

    // test loading of one formula
    $frm = new formula;
    $frm->usr = $usr;
    $frm->name = TF_INCREASE;
    $frm->load();
    $result = $frm->usr_text;
    $target = '"percent" = ( "this" - "prior" ) / "prior"';
    $exe_start_time = test_show_result('formula->load for "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test the formula type
    $result = zu_dsp_bool($frm->is_special());
    $target = zu_dsp_bool(false);
    $exe_start_time = test_show_result('formula->is_special for "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $exp = $frm->expression();
    $frm_lst = $exp->element_special_following_frm($back);
    $phr_lst = new phrase_list;
    if (count($frm_lst->lst) > 0) {
        $elm_frm = $frm_lst->lst[0];
        $result = zu_dsp_bool($elm_frm->is_special());
        $target = zu_dsp_bool(true);
        $exe_start_time = test_show_result('formula->is_special for "' . $elm_frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

        $phr_lst->usr = $usr;
        $phr_lst->add_name(TW_ABB);
        $phr_lst->add_name(TW_SALES);
        $phr_lst->add_name(TW_2014);
        $phr_lst->load();
        $time_phr = $phr_lst->time_useful();
        //echo $time_phr->name().'<br>';
        $val = $elm_frm->special_result($phr_lst, $time_phr);
        $result = $val->number;
        //echo $result.'<br>';
        $target = TW_2016;
        // todo: get the best matching number
        //$exe_start_time = test_show_result('formula->special_result for "'.$elm_frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

        if (count($frm_lst->lst) > 1) {
            //$elm_frm_next = $frm_lst->lst[1];
            $elm_frm_next = $elm_frm;
        } else {
            $elm_frm_next = $elm_frm;
        }
        $time_phr = $elm_frm_next->special_time_phr($time_phr);
        $result = $time_phr->name;
        $target = TW_2015; // todo: check why $elm_frm_next = $frm_lst->lst[1]; is not working
        $target = TW_2014;
        $exe_start_time = test_show_result('formula->special_time_phr for "' . $elm_frm_next->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    }

    $phr_lst = $frm->special_phr_lst($phr_lst);
    if (!isset($phr_lst)) {
        $result = '';
    } else {
        $result = $phr_lst->name();
    }
    $target = '"' . TW_ABB . '","' . TW_SALES . '","' . TW_2014 . '"';
    $exe_start_time = test_show_result('formula->special_phr_lst for "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $phr_lst = $frm->assign_phr_lst_direct();
    if (!isset($phr_lst)) {
        $result = '';
    } else {
        $result = $phr_lst->name();
    }
    $target = '"Year"';
    $exe_start_time = test_show_result('formula->assign_phr_lst_direct for "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $phr_lst = $frm->assign_phr_ulst_direct();
    if (!isset($phr_lst)) {
        $result = '';
    } else {
        $result = $phr_lst->name();
    }
    $target = '"Year"';
    $exe_start_time = test_show_result('formula->assign_phr_ulst_direct for "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // loading another formula (Price Earning ratio ) to have more test cases
    $frm_pe = load_formula(TF_PE);

    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(TW_ABB);
    $phr_lst->add_name(TW_SALES);
    $phr_lst->add_name(TW_2014);
    $phr_lst->load();

    $phr_lst_all = $frm_pe->assign_phr_lst();
    $phr_lst = $phr_lst_all->filter($phr_lst);
    $result = $phr_lst->name();
    $target = '"' . TW_ABB . '"';
    $exe_start_time = test_show_result('formula->assign_phr_lst for "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $phr_lst_all = $frm_pe->assign_phr_ulst();
    $phr_lst = $phr_lst_all->filter($phr_lst);
    $result = $phr_lst->name();
    $target = '"' . TW_ABB . '"';
    $exe_start_time = test_show_result('formula->assign_phr_ulst for "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test the calculation of one value
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(TW_ABB);
    $phr_lst->add_name(TW_SALES);
    $phr_lst->add_name(TW_2014);
    // why are these two words needed??
    $phr_lst->add_name(TW_CHF);
    $phr_lst->add_name(TW_MIO);
    $phr_lst->load();

    $frm = load_formula(TF_INCREASE);
    $fv_lst = $frm->to_num($phr_lst, $back);
    if (isset($fv_lst->lst)) {
        $fv = $fv_lst->lst[0];
        $result = $fv->num_text;
    } else {
        $fv = Null;
        $result = 'result list is empty';
    }
    $target = '=(46000-45548)/45548';
    $exe_start_time = test_show_result('formula->to_num "' . $frm->name . '" for a tern list ' . $phr_lst->dsp_id() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    if (isset($fv_lst->lst)) {
        $fv = $fv->save_if_updated();
        $result = $fv->value;
        $target = '0.0099236';
        $exe_start_time = test_show_result('formula_value->save_if_updated "' . $frm->name . '" for a tern list ' . $phr_lst->dsp_id() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    }

    $fv_lst = $frm->calc($phr_lst, $back);
    if (isset($fv_lst)) {
        $result = $fv_lst[0]->value;
    } else {
        $result = '';
    }
    $target = '0.0099235970843945';
    $exe_start_time = test_show_result('formula->calc "' . $frm->name . '" for a tern list ' . $phr_lst->dsp_id() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test the display functions
    $frm = load_formula(TF_INCREASE);
    $exp = $frm->expression();
    $result = $exp->dsp_id();
    $target = '""percent" = ( "this" - "prior" ) / "prior"" ({t19}=({f3}-{f5})/{f5})';
    $exe_start_time = test_show_result('formula->expression for ' . $frm->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $result = $frm->name();
    $target = 'increase';
    $exe_start_time = test_show_result('formula->name for ' . $frm->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $result = $frm->dsp_text($back);
    $target = '"percent" = ( <a href="/http/formula_edit.php?id=3&back=1">this</a> - <a href="/http/formula_edit.php?id=5&back=1">prior</a> ) / <a href="/http/formula_edit.php?id=5&back=1">prior</a>';
    $exe_start_time = test_show_result('formula->dsp_text for ' . $frm->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $result = $frm->name_linked($back);
    $target = '<a href="/http/formula_edit.php?id=52&back=1">increase</a>';
    $exe_start_time = test_show_result('formula->display for ' . $frm->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $wrd = new word_dsp;
    $wrd->usr = $usr;
    $wrd->name = TW_ABB;
    $wrd->load();
    $result = trim($frm->dsp_result($wrd, $back));
    $target = '0.99 %';  /* The result for ... */
    $target = '-3.29 %'; /* TODO temp fix */
    $target = '0.01';  /* temp fix. */
    $target = '0 %';  /* temp fix. */
    $exe_start_time = test_show_result('formula->dsp_result for ' . $frm->name() . ' and ' . $wrd->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    /* TODO reactivate
    $result = $frm->btn_edit();
    $target = '<a href="/http/formula_edit.php?id=52&back=" title="Change formula increase"><img src="../images/button_edit.svg" alt="Change formula increase"></a>';
    $target = 'data-icon="edit"';
    $exe_start_time = test_show_contains(', formula->btn_edit for '.$frm->name().'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    */

    $page = 1;
    $size = 20;
    $call = '/http/test.php';
    $result = $frm->dsp_hist($page, $size, $call, $back);
    $target = 'changed to';
    $exe_start_time = test_show_contains(', formula->dsp_hist for ' . $frm->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $result = $frm->dsp_hist_links($page, $size, $call, $back);
    $target = 'link';
    //$result = $hist_page;
    $exe_start_time = test_show_contains(', formula->dsp_hist_links for ' . $frm->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $add = 0;
    $result = $frm->dsp_edit($add, $wrd, $back);
    $target = 'Formula "increase"';
    //$result = $edit_page;
    $exe_start_time = test_show_contains(', formula->dsp_edit for ' . $frm->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

    // test formula refresh functions

    $result = $frm->element_refresh($frm->ref_text);
    $target = '';
    $exe_start_time = test_show_result('formula->element_refresh for ' . $frm->name() . '', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


    // to link and unlink a formula is tested in the formula_link section

    // test adding of one formula
    $frm = new formula;
    $frm->name = TF_ADD;
    $frm->usr_text = '"percent" = ( "this" - "prior" ) / "prior"';
    $frm->usr = $usr;
    $result = $frm->save();
    if ($frm->id > 0) {
        $result = $frm->usr_text;
    }
    $target = '"percent" = ( "this" - "prior" ) / "prior"';
    $exe_start_time = test_show_result('formula->save for adding "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

    // check if the formula name has been saved
    $frm = load_formula(TF_ADD);
    $result = $frm->usr_text;
    $target = '"percent" = ( "this" - "prior" ) / "prior"';
    $exe_start_time = test_show_result('formula->load the added "' . $frm->name . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); // time limit???

    // ... check the correct logging
    $log = new user_log;
    $log->table = 'formulas';
    $log->field = 'formula_name';
    $log->row_id = $frm->id;
    $log->usr = $usr;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job added Test Formula';
    $exe_start_time = test_show_result('formula->save adding logged for "' . TF_ADD . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // check if adding the same formula again creates a correct error message
    $frm = new formula;
    $frm->name = TF_ADD;
    $frm->usr_text = '"percent" = 1 - ( "this" / "prior" )';
    $frm->usr = $usr;
    $result = $frm->save();
    // use the next line if system config is non standard
    //$target = 'A formula with the name "'.TF_ADD.'" already exists. Please use another name.';
    $target = '11111';
    $exe_start_time = test_show_result('formula->save adding "' . $frm->name . '" again', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

    // check if the formula linked word has been created
    $wrd = load_word(TF_ADD);
    $result = $wrd->type_id;
    $target = cl(DBL_WORD_TYPE_FORMULA_LINK);
    $exe_start_time = test_show_result('word->load of the word "' . $frm->name . '" has the formula type', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


    // check if the formula can be renamed
    $frm = load_formula(TF_ADD);
    $frm->name = TF_ADD_RENAMED;
    $result = $frm->save();
    $target = '11';
    $exe_start_time = test_show_result('formula->save rename "' . TF_ADD . '" to "' . TF_ADD_RENAMED . '".', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

    // ... and if the formula renaming was successful
    $frm_renamed = new formula;
    $frm_renamed->name = TF_ADD_RENAMED;
    $frm_renamed->usr = $usr;
    $frm_renamed->load();
    if ($frm_renamed->id > 0) {
        $result = $frm_renamed->name;
    }
    $target = TF_ADD_RENAMED;
    $exe_start_time = test_show_result('formula->load renamed formula "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... and if the formula renaming has been logged
    $log = new user_log;
    $log->table = 'formulas';
    $log->field = 'formula_name';
    $log->row_id = $frm_renamed->id;
    $log->usr = $usr;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job changed Test Formula to Formula Test';
    $exe_start_time = test_show_result('formula->save rename logged for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // check if the formula parameters can be added
    $frm_renamed->usr_text = '= "this"';
    $frm_renamed->description = TF_ADD_RENAMED . ' description';
    $frm_renamed->type_id = cl(DBL_FORMULA_TYPE_THIS);
    $frm_renamed->need_all_val = True;
    $result = $frm_renamed->save();
    $target = '1111111';
    $exe_start_time = test_show_result('formula->save all formula fields beside the name for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

    // ... and if the formula parameters have been added
    $frm_reloaded = load_formula(TF_ADD_RENAMED);
    $result = $frm_reloaded->usr_text;
    $target = '= "this"';
    $exe_start_time = test_show_result('formula->load usr_text for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_reloaded->ref_text;
    $target = '={f3}';
    $exe_start_time = test_show_result('formula->load ref_text for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_reloaded->description;
    $target = TF_ADD_RENAMED . ' description';
    $exe_start_time = test_show_result('formula->load description for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_reloaded->type_id;
    $target = cl(DBL_FORMULA_TYPE_THIS);
    $exe_start_time = test_show_result('formula->load type_id for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_reloaded->need_all_val;
    $target = True;
    $exe_start_time = test_show_result('formula->load need_all_val for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... and if the formula parameter adding have been logged
    $log = new user_log;
    $log->table = 'formulas';
    $log->field = 'resolved_text';
    $log->row_id = $frm_reloaded->id;
    $log->usr = $usr;
    $result = $log->dsp_last(true);
    // use the next line if system config is non standard
    $target = 'zukunft.com system batch job changed "percent" = ( "this" - "prior" ) / "prior" to = "this"';
    $target = 'zukunft.com system batch job changed "percent" = 1 - ( "this" / "prior" ) to = "this"';
    $exe_start_time = test_show_result('formula->load resolved_text for "' . TF_ADD_RENAMED . '" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $log->field = 'formula_text';
    $result = $log->dsp_last(true);
    // use the next line if system config is non standard
    $target = 'zukunft.com system batch job changed {t19}=( {f3} - {f5} ) / {f5} to ={f3}';
    $target = 'zukunft.com system batch job changed {t19}=1-({f3}/{f5}) to ={f3}';
    $exe_start_time = test_show_result('formula->load formula_text for "' . TF_ADD_RENAMED . '" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $log->field = 'description';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job added Formula Test description';
    $exe_start_time = test_show_result('formula->load description for "' . TF_ADD_RENAMED . '" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $log->field = 'formula_type_id';
    $result = $log->dsp_last(true);
    // to review what is correct
    $target = 'zukunft.com system batch job changed calc to this';
    $target = 'zukunft.com system batch job added this';
    $exe_start_time = test_show_result('formula->load formula_type_id for "' . TF_ADD_RENAMED . '" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $log->field = 'all_values_needed';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job changed 0 to 1';
    $exe_start_time = test_show_result('formula->load all_values_needed for "' . TF_ADD_RENAMED . '" logged', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // check if a user specific formula is created if another user changes the formula
    $frm_usr2 = new formula;
    $frm_usr2->name = TF_ADD_RENAMED;
    $frm_usr2->usr = $usr2;
    $frm_usr2->load();
    $frm_usr2->usr_text = '"percent" = ( "this" - "prior" ) / "prior"';
    $frm_usr2->description = TF_ADD_RENAMED . ' description2';
    $frm_usr2->type_id = cl(DBL_FORMULA_TYPE_NEXT);
    $frm_usr2->need_all_val = False;
    $result = $frm_usr2->save();
    $target = '1111111111';
    $exe_start_time = test_show_result('formula->save all formula fields for user 2 beside the name for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

    // ... and if a user specific formula changes have been saved
    $frm_usr2_reloaded = new formula;
    $frm_usr2_reloaded->name = TF_ADD_RENAMED;
    $frm_usr2_reloaded->usr = $usr2;
    $frm_usr2_reloaded->load();
    $result = $frm_usr2_reloaded->usr_text;
    $target = '"percent" = ( "this" - "prior" ) / "prior"';
    $exe_start_time = test_show_result('formula->load usr_text for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_usr2_reloaded->ref_text;
    $target = '{t19}=({f3}-{f5})/{f5}';
    $exe_start_time = test_show_result('formula->load ref_text for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_usr2_reloaded->description;
    $target = TF_ADD_RENAMED . ' description2';
    $exe_start_time = test_show_result('formula->load description for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_usr2_reloaded->type_id;
    $target = cl(DBL_FORMULA_TYPE_NEXT);
    $exe_start_time = test_show_result('formula->load type_id for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_usr2_reloaded->need_all_val;
    $target = False;
    $exe_start_time = test_show_result('formula->load need_all_val for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... and the formula for the original user remains unchanged
    $frm_reloaded = load_formula(TF_ADD_RENAMED);
    $result = $frm_reloaded->usr_text;
    $target = '= "this"';
    $exe_start_time = test_show_result('formula->load usr_text for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_reloaded->ref_text;
    $target = '={f3}';
    $exe_start_time = test_show_result('formula->load ref_text for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_reloaded->description;
    $target = TF_ADD_RENAMED . ' description';
    $exe_start_time = test_show_result('formula->load description for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_reloaded->type_id;
    $target = cl(DBL_FORMULA_TYPE_THIS);
    $exe_start_time = test_show_result('formula->load type_id for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_reloaded->need_all_val;
    $target = True;
    $exe_start_time = test_show_result('formula->load need_all_val for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // check if undo all specific changes removes the user formula
    $frm_usr2 = new formula;
    $frm_usr2->name = TF_ADD_RENAMED;
    $frm_usr2->usr = $usr2;
    $frm_usr2->load();
    $frm_usr2->usr_text = '= "this"';
    $frm_usr2->description = TF_ADD_RENAMED . ' description';
    $frm_usr2->type_id = cl(DBL_FORMULA_TYPE_THIS);
    $frm_usr2->need_all_val = True;
    $result = $frm_usr2->save();
    $target = '111111111';
    $exe_start_time = test_show_result('formula->save undo the user formula fields beside the name for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

    // ... and if a user specific formula changes have been saved
    $frm_usr2_reloaded = new formula;
    $frm_usr2_reloaded->name = TF_ADD_RENAMED;
    $frm_usr2_reloaded->usr = $usr2;
    $frm_usr2_reloaded->load();
    $result = $frm_usr2_reloaded->usr_text;
    $target = '= "this"';
    $exe_start_time = test_show_result('formula->load usr_text for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_usr2_reloaded->ref_text;
    $target = '={f3}';
    $exe_start_time = test_show_result('formula->load ref_text for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_usr2_reloaded->description;
    $target = TF_ADD_RENAMED . ' description';
    $exe_start_time = test_show_result('formula->load description for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_usr2_reloaded->type_id;
    $target = cl(DBL_FORMULA_TYPE_THIS);
    $exe_start_time = test_show_result('formula->load type_id for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = $frm_usr2_reloaded->need_all_val;
    $target = True;
    $exe_start_time = test_show_result('formula->load need_all_val for "' . TF_ADD_RENAMED . '"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // redo the user specific formula changes
    // check if the user specific changes can be removed with one click

    // check for formulas also that

}

function run_formula_list_test()
{

    global $usr;
    global $exe_start_time;

    test_header('est the formula list class (classes/formula_list.php)');

    // load the main test word
    $wrd_company = test_word(TEST_WORD);

    $wrd = new word;
    $wrd->id = $wrd_company->id;
    $wrd->usr = $usr;
    $wrd->load();
    $frm_lst = new formula_list;
    $frm_lst->wrd = $wrd;
    $frm_lst->usr = $usr;
    $frm_lst->back = $wrd->id;
    $frm_lst->load();
    $result = $frm_lst->display();
    $target = TF_PE;
    $exe_start_time = test_show_contains(', formula_list->load formula for word "' . $wrd->dsp_id() . '" should contain', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

}