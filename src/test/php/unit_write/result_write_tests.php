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

use cfg\formula\formula;
use cfg\phrase\phrase_list;
use cfg\result\result;
use cfg\result\result_list;
use cfg\result\results;
use cfg\value\value;
use shared\const\formulas;
use shared\const\words;
use test\test_cleanup;

class result_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('result database write tests');

        /*
         * prepare
         */

        // test adding of one formula
        $frm = new formula($t->usr1);
        $frm->set_name(formulas::SYSTEM_TEST_ADD);
        $frm->usr_text = formulas::INCREASE_EXP;
        $result = $frm->save()->get_last_message();
        if ($frm->id() > 0) {
            $result = $frm->usr_text;
        }
        $target = formulas::INCREASE_EXP;
        $t->display('formula->save for adding "' . $frm->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the formula can be renamed
        $frm = $t->load_formula(formulas::SYSTEM_TEST_ADD);
        $frm->set_name(formulas::SYSTEM_TEST_RENAMED);
        $result = $frm->save()->get_last_message();
        $target = '';
        $t->display('formula->save rename "' . formulas::SYSTEM_TEST_ADD . '" to "' . formulas::SYSTEM_TEST_RENAMED . '".', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);


        // test load result without time
        $phr_lst = new phrase_list($usr);
        $phr_lst->add_name(words::CH);
        //$phr_lst->add_name(formulas::TN_ADD);
        $phr_lst->add_name(formulas::SYSTEM_TEST_RENAMED);
        $phr_lst->add_name(words::PCT);
        $phr_lst->add_name(words::INHABITANTS);
        $ch_up_grp = $phr_lst->get_grp_id();
        if ($ch_up_grp->is_id_set()) {
            $ch_increase = new result($usr);
            $ch_increase->load_by_grp($ch_up_grp);
            $result = $ch_increase->number();
            if ($result == null) {
                $result = '';
            }
        } else {
            $result = 'no ' . words::INHABITANTS . ' ' . formulas::INCREASE . ' value found for ' . words::CH;
        }
        // TODO review
        $target = results::TV_INCREASE_LONG;
        // TODO activate Prio 1
        //$t->display('value->val_formatted ex time for ' . $phr_lst->dsp_id() . ' (group id ' . $ch_up_grp->id() . ')', $target, $result, $t::TIMEOUT_LIMIT_LONG);

        // test load result with time
        $phr_lst->add_name(words::YEAR_2020);
        $time_phr = $phr_lst->time_useful();
        $phr_lst->ex_time();
        $ch_up_grp = $phr_lst->get_grp_id();
        if ($ch_up_grp->is_id_set()) {
            $ch_increase = new result($usr);
            $ch_increase->load_by_grp($ch_up_grp, true);
            $result = $ch_increase->number();
            if ($result == null) {
                $result = '';
            }
        } else {
            $result = 'no ' . words::YEAR_2020 . ' ' . words::INHABITANTS . ' ' . formulas::INCREASE . ' value found for ' . words::CH;
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
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::YEAR_2020, words::TEST_IN_K));
        $phr_lst->ex_time();
        $ch_k_grp = $phr_lst->get_grp_id();
        /*
        $dest_wrd_lst = new word_list($usr);
        $dest_wrd_lst->add_name(words::TN_INHABITANTS);
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
        $result = $k_val->number();
        if ($result == null) {
            $result = '';
        }
        $target = 8505.251;
        // TODO activate Prio 1
        //$t->display('value->val_scaling for a tern list ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test getting the "best guess" value
        // e.g. if ABB,sales,2014 is requested, but there is only a value for ABB,sales,2014,CHF,million get it
        //      based
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::YEAR_2020));
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
        $t->display('value->load the best guess for ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);
        */

        /*

        Additional test cases for formula result

        if a user changes a value the result for him should be updated and the result should be user specific
        but the result for other user should not be changed
        if the user undo the value change, the result should be updated

        if the user changes a word link, formula link or formula the result should also be updated

        */

        // cleanup - fallback delete
        $frm = new formula($t->usr1);
        $frm->set_user($t->usr1);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $frm->del();
        $frm->set_user($t->usr2);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $frm->del();
        $frm->set_user($t->usr1);
        $frm->load_by_name(formulas::SYSTEM_TEST_RENAMED);
        $frm->del();
        $frm->set_user($t->usr2);
        $frm->load_by_name(formulas::SYSTEM_TEST_RENAMED);
        $frm->del();


    }

    function run_list(test_cleanup $t): void
    {

        global $usr;

        $t->header('result list database write tests');

        // load results by formula
        $frm = $t->load_formula(formulas::SYSTEM_TEST_RENAMED);
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($frm);
        $result = $res_lst->dsp_id();
        $target = '0.0078';
        $t->dsp_contains(', result_list->load of the formula results for ' . $frm->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // load results by phrase group
        $grp = $t->load_phrase_group(array(words::CH, words::INHABITANTS, words::TEST_IN_K));
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($grp);
        $result = $res_lst->dsp_id();
        $target = '8505.251';
        $t->dsp_contains(', result_list->load of the formula results for ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // ... and also with time selection
        $grp = $t->load_phrase_group(array(words::CH, words::INHABITANTS, words::TEST_IN_K, words::YEAR_2020));
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($grp);
        $result = $res_lst->dsp_id();
        $t->dsp_contains(', result_list->load of the formula results for ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // load results by source phrase group
        $grp = $t->load_phrase_group(array(words::CH, words::INHABITANTS, words::MIO));
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($grp, true);
        $result = $res_lst->dsp_id();
        $target = '0.0078';
        $t->dsp_contains(', result_list->load of the formula results for source ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // ... and also with time selection
        $time_phr = $t->load_phrase(words::YEAR_2020);
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($grp, true);
        $result = $res_lst->dsp_id();
        $t->dsp_contains(', result_list->load of the formula results for ' . $grp->dsp_id() . ' and ' . $time_phr->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // load results by word id
        $wrd = $t->load_word(words::INHABITANTS);
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($wrd);
        $result = $res_lst->dsp_id();
        $target = '0.0078';
        $t->dsp_contains(', result_list->load of the formula results for ' . $grp->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // TODO add PE frm test
        //$frm = $t->load_formula(TF_PE);
        $frm = $t->load_formula(formulas::INCREASE);
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($frm);
        $result = $res_lst->dsp_id();
        $target = '"sales","' . words::PCT . '","increase","' . words::TEST_RENAMED . '","2017"';
        $target = words::INHABITANTS;
        $t->dsp_contains(', result_list->load of the formula results for ' . $frm->dsp_id() . ' is ' . $result . ' and should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

    }

}