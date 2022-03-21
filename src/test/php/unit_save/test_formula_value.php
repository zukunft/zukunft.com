<?php

/*

    test_formula_value.php - TESTing of the FORMULA VALUE functions
    ----------------------

    TODO allow users to overwrite formula values
    TODO allow user to convert words or triples to formula names
        e.g. if one user creates the word increase and adds a number
             and later another user wants to create a formula, he should be allowed to do it
             the first user should see than a suggestion to calculate the increase instead of having it fixed


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

function run_formula_value_test(testing $t)
{

    global $usr;

    $t->header('Test the formula value class (classes/formula_value.php)');

    // test load result without time
    $phr_lst = new phrase_list($usr);
    $phr_lst->add_name(word::TN_CH);
    $phr_lst->add_name(formula::TN_INCREASE);
    // TODO check why are these two words needed??
    $phr_lst->add_name(word::TN_MIO);
    // TODO $phr_lst->add_name(word::TN_PCT);
    $phr_lst->add_name(word::TN_INHABITANT);
    $ch_up_grp = $phr_lst->get_grp();
    if ($ch_up_grp->id > 0) {
        $ch_increase = new formula_value($usr);
        $ch_increase->load_by_grp($ch_up_grp->id);
        $result = $ch_increase->value;
    } else {
        $result = 'no ' . word::TN_INHABITANT . ' ' . formula::TN_INCREASE . ' value found for ' . word::TN_CH;
    }
    // TODO review
    $target = '0.0078718332961637';
    $t->dsp('value->val_formatted ex time for ' . $phr_lst->dsp_id() . ' (group id ' . $ch_up_grp->id . ')', $target, $result, TIMEOUT_LIMIT_LONG);

    // test load result with time
    $phr_lst->add_name(word::TN_2020);
    $time_phr = $phr_lst->time_useful();
    $phr_lst->ex_time();
    $ch_up_grp = $phr_lst->get_grp();
    if ($ch_up_grp->id > 0) {
        $ch_increase = new formula_value($usr);
        $ch_increase->phr_grp_id = $ch_up_grp->id;
        $ch_increase->time_id = $time_phr->id;
        //$ch_increase->wrd_lst = $phr_lst;
        $ch_increase->usr->id = $usr->id; // temp solution utils the value is saved automatically for all users
        $ch_increase->load_by_vars();
        $result = $ch_increase->value;
    } else {
        $result = 'no ' . word::TN_2020 . ' ' . word::TN_INHABITANT . ' ' . formula::TN_INCREASE . ' value found for ' . word::TN_CH;
    }
    //$result = $ch_increase->phr_grp_id;
    $target = '0.0078718332961637';
    if (isset($time_phr) and isset($ch_up_grp)) {
        $t->dsp('value->val_formatted incl time (' . $time_phr->dsp_id() . ') for ' . $phr_lst->dsp_id() . ' (group id ' . $ch_up_grp->id . ')', $target, $result);
    } else {
        $t->dsp('value->val_formatted incl time for ', $target, $result);
    }

    // test the scaling
    // test the scaling of a value
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word::TN_CH, word::TN_INHABITANT, word::TN_2020, word::TN_IN_K));
    $phr_lst->ex_time();
    $ch_k_grp = $phr_lst->get_grp();
    /*
    $dest_wrd_lst = new word_list($usr);
    $dest_wrd_lst->add_name(word::TN_INHABITANT);
    $dest_wrd_lst->load();
    $mio_val = new value($usr);
    $mio_val->ids = $wrd_lst->ids;
    $mio_val->load();
    log_debug('value->scale value loaded');
    $result = $mio_val->scale($dest_wrd_lst);
    $result = $mio_val->scale($dest_wrd_lst);
    */
    $k_val = new formula_value($usr);
    //$result = $mio_val->check();
    $k_val->load_by_grp($ch_k_grp->id);
    $result = $k_val->value;
    $target = 8505.251;
    $t->dsp('value->val_scaling for a tern list ' . $phr_lst->dsp_id() . '', $target, $result, TIMEOUT_LIMIT_PAGE);

    // test getting the "best guess" value
    // e.g. if ABB,Sales,2014 is requested, but there is only a value for ABB,Sales,2014,CHF,million get it
    //      based
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word::TN_CH, word::TN_INHABITANT, word::TN_2020));
    $phr_lst->ex_time();
    $val_best_guess = new value($usr);
    $val_best_guess->grp = $phr_lst->get_grp();
    $val_best_guess->load();
    $result = $val_best_guess->number;
    // TODO check why this value sometimes switch
    /*
    $target = 0.18264281677284;
    if ($result != $target) {
        $target = 0.007871833296164;
    }
    $t->dsp('value->load the best guess for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
    */

    /*

    Additional test cases for formula result

    if a user changes a value the result for him should be updated and the result should be user specific
    but the result for other user should not be changed
    if the user undo the value change, the result should be updated

    if the user changes a word link, formula link or formula the result should also be updated

    */

}

function run_formula_value_list_test(testing $t)
{

    global $usr;

    $t->header('Test the formula value list class (classes/formula_value_list.php)');

    // load results by formula
    $frm = $t->load_formula(formula::TN_INCREASE);
    $fv_lst = new formula_value_list($usr);
    $fv_lst->load($frm);
    $result = $fv_lst->dsp_id();
    $target = '0.0078';
    $t->dsp_contains(', formula_value_list->load of the formula results for ' . $frm->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

    // load results by phrase group
    $grp = $t->load_phrase_group(array(word::TN_CH, word::TN_INHABITANT, word::TN_IN_K));
    $fv_lst = new formula_value_list($usr);
    $fv_lst->load($grp);
    $result = $fv_lst->dsp_id();
    $target = '8505.251';
    $t->dsp_contains(', formula_value_list->load of the formula results for ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

    // ... and also with time selection
    $time_phr = $t->load_phrase(word::TN_2020);
    $fv_lst = new formula_value_list($usr);
    $fv_lst->load($grp, $time_phr);
    $result = $fv_lst->dsp_id();
    $t->dsp_contains(', formula_value_list->load of the formula results for ' . $grp->dsp_id() . ' and ' . $time_phr->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

    // load results by source phrase group
    $grp = $t->load_phrase_group(array(word::TN_CH, word::TN_INHABITANT, word::TN_MIO));
    $fv_lst = new formula_value_list($usr);
    $fv_lst->load($grp, null, true);
    $result = $fv_lst->dsp_id();
    $target = '0.0078';
    $t->dsp_contains(', formula_value_list->load of the formula results for source ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

    // ... and also with time selection
    $time_phr = $t->load_phrase(word::TN_2020);
    $fv_lst = new formula_value_list($usr);
    $fv_lst->load($grp, $time_phr, true);
    $result = $fv_lst->dsp_id();
    $t->dsp_contains(', formula_value_list->load of the formula results for ' . $grp->dsp_id() . ' and ' . $time_phr->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

    // load results by word id
    $wrd = $t->load_word(word::TN_INHABITANT);
    $fv_lst = new formula_value_list($usr);
    $fv_lst->load($wrd);
    $result = $fv_lst->dsp_id();
    $target = '0.0078';
    $t->dsp_contains(', formula_value_list->load of the formula results for ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

    // TODO add PE frm test
    //$frm = $t->load_formula(TF_PE);
    $frm = $t->load_formula(formula::TN_INCREASE);
    $fv_lst = new formula_value_list($usr);
    $fv_lst->load($frm);
    $result = $fv_lst->dsp_id();
    $target = '"Sales","percent","increase","' . word::TN_RENAMED . '","2017"';
    $target = word::TN_INHABITANT;
    $t->dsp_contains(', formula_value_list->load of the formula results for ' . $frm->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

}