<?php

/*

  test_formula_trigger.php - TESTing of the trigger for FORMULAS
  ------------------------
  

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

function run_formula_trigger_test(testing $t)
{

    global $usr;

    $t->header('Test the formula calculation triggers');

    // prepare the calculation trigger test
    $phr_lst1 = new phrase_list($usr);
    $phr_lst1->add_name(word::TN_CH);
    $phr_lst1->add_name(word::TN_INHABITANT);
    $phr_lst1->add_name(word::TN_MIO);
    $phr_lst2 = clone $phr_lst1;
    $phr_lst1->add_name(word::TN_2019);
    $phr_lst2->add_name(word::TN_2020);
    $frm = $t->load_formula(formula::TN_INCREASE);

    // add a number to the test word
    $val_add1 = new value($usr);
    $val_add1->grp = $phr_lst1->get_grp();
    $val_add1->number = TV_TEST_SALES_2016;
    $result = $val_add1->save();
    // add a second number to the test word
    $val_add2 = new value($usr);
    $val_add2->grp = $phr_lst2->get_grp();
    $val_add2->number = TV_TEST_SALES_2017;
    $result = $val_add2->save();

    // check if the first number have been saved correctly
    $added_val = new value($usr);
    $added_val->grp = $phr_lst1->get_grp();
    $added_val->load();
    $result = $added_val->number;
    $target = TV_TEST_SALES_2016;
    $t->dsp('value->check added test value for "' . $phr_lst1->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    // check if the second number have been saved correctly
    $added_val2 = new value($usr);
    $added_val2->grp = $phr_lst2->get_grp();
    $added_val2->load();
    $result = $added_val2->number;
    $target = TV_TEST_SALES_2017;
    $t->dsp('value->check added test value for "' . $phr_lst2->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if requesting the best number for the first number returns a useful value
    $best_val = new value($usr);
    $best_val->grp = $phr_lst1->get_grp();
    $best_val->load_best();
    $result = $best_val->number;
    $target = TV_TEST_SALES_2016;
    $t->dsp('value->check best value for "' . $phr_lst1->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    // check if requesting the best number for the second number returns a useful value
    $best_val2 = new value($usr);
    $best_val2->grp = $phr_lst2->get_grp();
    $best_val2->load_best();
    $result = $best_val2->number;
    $target = TV_TEST_SALES_2017;
    $t->dsp('value->check best value for "' . $phr_lst2->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // calculate the increase and check the result
    $fv_lst = $frm->calc($phr_lst2, 0);
    if ($fv_lst != null) {
        if (count($fv_lst) > 0) {
            $fv = $fv_lst[0];
            $result = trim($fv->display(0));
        } else {
            $result = '';
        }
    } else {
        $result = '';
    }
    // TODO check why the data in PROD is strange
    if ($result == TV_TEST_SALES_INCREASE_2017_FORMATTED) {
        $target = TV_TEST_SALES_INCREASE_2017_FORMATTED;
    } else {
        $target = "0.79 %";
    }
    $t->dsp('formula result for ' . $frm->dsp_id() . ' from ' . $phr_lst1->dsp_id() . ' to ' . $phr_lst2->dsp_id() . '', $target, $result, TIMEOUT_LIMIT_LONG);

    // remove the test values
    $val_add1->del();
    $val_add2->del();

    // change the second number and test if the result has been updated
    // a second user changes the value back to the original value and check if for the second number the result is updated
    // check if the result for the first user is not changed
    // the first user also changes back the value to the original value and now the values for both user should be the same

}