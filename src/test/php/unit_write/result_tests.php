<?php

/*

    test/php/unit_write/result_tests.php - write test RESULTS to the database and check the results
    ------------------------------------

    TODO allow users to overwrite results
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

namespace unit_write;

use api\formula\formula as formula_api;
use api\result\result as result_api;
use api\word\word as word_api;
use cfg\phrase_list;
use cfg\result\result;
use cfg\result\result_list;
use cfg\value\value;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_LONG;
use const test\TIMEOUT_LIMIT_PAGE;

class result_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('Test the result class (classes/result.php)');

        // test load result without time
        $phr_lst = new phrase_list($usr);
        $phr_lst->add_name(word_api::TN_CH);
        //$phr_lst->add_name(formula_api::TN_ADD);
        $phr_lst->add_name(formula_api::TN_RENAMED);
        $phr_lst->add_name(word_api::TN_PCT);
        $phr_lst->add_name(word_api::TN_INHABITANTS);
        $ch_up_grp = $phr_lst->get_grp_id();
        if ($ch_up_grp->is_id_set()) {
            $ch_increase = new result($usr);
            $ch_increase->load_by_grp($ch_up_grp);
            $result = $ch_increase->value;
            if ($result == null) {
                $result = '';
            }
        } else {
            $result = 'no ' . word_api::TN_INHABITANTS . ' ' . formula_api::TN_ADD . ' value found for ' . word_api::TN_CH;
        }
        // TODO review
        $target = result_api::TV_INCREASE_LONG;
        // TODO activate Prio 1
        //$t->display('value->val_formatted ex time for ' . $phr_lst->dsp_id() . ' (group id ' . $ch_up_grp->id() . ')', $target, $result, TIMEOUT_LIMIT_LONG);

        // test load result with time
        $phr_lst->add_name(word_api::TN_2020);
        $time_phr = $phr_lst->time_useful();
        $phr_lst->ex_time();
        $ch_up_grp = $phr_lst->get_grp_id();
        if ($ch_up_grp->is_id_set()) {
            $ch_increase = new result($usr);
            $ch_increase->load_by_grp($ch_up_grp, $time_phr->id());
            $result = $ch_increase->value;
            if ($result == null) {
                $result = '';
            }
        } else {
            $result = 'no ' . word_api::TN_2020 . ' ' . word_api::TN_INHABITANTS . ' ' . formula_api::TN_ADD . ' value found for ' . word_api::TN_CH;
        }
        //$result = $ch_increase->phr_grp_id;
        if (isset($time_phr)) {
            // TODO activate Prio 1
            //$t->display('value->val_formatted incl time (' . $time_phr->dsp_id() . ') for ' . $phr_lst->dsp_id() . ' (group id ' . $ch_up_grp->id() . ')', $target, $result);
        } else {
            $t->display('value->val_formatted incl time for ', $target, $result);
        }

        // test the scaling
        // test the scaling of a value
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_2020, word_api::TN_IN_K));
        $phr_lst->ex_time();
        $ch_k_grp = $phr_lst->get_grp_id();
        /*
        $dest_wrd_lst = new word_list($usr);
        $dest_wrd_lst->add_name(word_api::TN_INHABITANTS);
        $dest_wrd_lst->load();
        $mio_val = new value($usr);
        $mio_val->ids = $wrd_lst->ids;
        $mio_val->load();
        log_debug('value->scale value loaded');
        $result = $mio_val->scale($dest_wrd_lst);
        $result = $mio_val->scale($dest_wrd_lst);
        */
        $k_val = new result($usr);
        //$result = $mio_val->check();
        $k_val->load_by_grp($ch_k_grp);
        $result = $k_val->value;
        if ($result == null) {
            $result = '';
        }
        $target = 8505.251;
        // TODO activate Prio 1
        //$t->display('value->val_scaling for a tern list ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

        // test getting the "best guess" value
        // e.g. if ABB,Sales,2014 is requested, but there is only a value for ABB,Sales,2014,CHF,million get it
        //      based
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_2020));
        $phr_lst->ex_time();
        $val_best_guess = new value($usr);
        $val_best_guess->load_by_grp($phr_lst->get_grp_id());
        $result = $val_best_guess->number();
        // TODO check why this value sometimes switch
        /*
        $target = 0.18264281677284;
        if ($result != $target) {
            $target = 0.007871833296164;
        }
        $t->display('value->load the best guess for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
        */

        /*

        Additional test cases for formula result

        if a user changes a value the result for him should be updated and the result should be user specific
        but the result for other user should not be changed
        if the user undo the value change, the result should be updated

        if the user changes a word link, formula link or formula the result should also be updated

        */

    }

    function run_list(test_cleanup $t): void
    {

        global $usr;

        $t->header('Test the result list class (classes/result_list.php)');

        // load results by formula
        $frm = $t->load_formula(formula_api::TN_RENAMED);
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($frm);
        $result = $res_lst->dsp_id();
        $target = '0.0078';
        $t->dsp_contains(', result_list->load of the formula results for ' . $frm->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

        // load results by phrase group
        $grp = $t->load_phrase_group(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_IN_K));
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($grp);
        $result = $res_lst->dsp_id();
        $target = '8505.251';
        $t->dsp_contains(', result_list->load of the formula results for ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

        // ... and also with time selection
        $grp = $t->load_phrase_group(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_IN_K, word_api::TN_2020));
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($grp);
        $result = $res_lst->dsp_id();
        $t->dsp_contains(', result_list->load of the formula results for ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

        // load results by source phrase group
        $grp = $t->load_phrase_group(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO));
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($grp, true);
        $result = $res_lst->dsp_id();
        $target = '0.0078';
        $t->dsp_contains(', result_list->load of the formula results for source ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

        // ... and also with time selection
        $time_phr = $t->load_phrase(word_api::TN_2020);
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($grp, true);
        $result = $res_lst->dsp_id();
        $t->dsp_contains(', result_list->load of the formula results for ' . $grp->dsp_id() . ' and ' . $time_phr->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

        // load results by word id
        $wrd = $t->load_word(word_api::TN_INHABITANTS);
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($wrd);
        $result = $res_lst->dsp_id();
        $target = '0.0078';
        $t->dsp_contains(', result_list->load of the formula results for ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

        // TODO add PE frm test
        //$frm = $t->load_formula(TF_PE);
        $frm = $t->load_formula(formula_api::TN_ADD);
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($frm);
        $result = $res_lst->dsp_id();
        $target = '"Sales","' . word_api::TN_PCT . '","increase","' . word_api::TN_RENAMED . '","2017"';
        $target = word_api::TN_INHABITANTS;
        $t->dsp_contains(', result_list->load of the formula results for ' . $frm->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, TIMEOUT_LIMIT_PAGE);

    }

}