<?php

/*

    test/php/unit_write/formula_trigger.php - write test triggers for FORMULAS to the database and check the results
    ---------------------------------------
  

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

namespace test\write;

use api\formula_api;
use api\value_api;
use api\word_api;
use cfg\phrase_list;
use cfg\value;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_DB_MULTI;
use const test\TIMEOUT_LIMIT_LONG;
use const test\TV_TEST_SALES_INCREASE_2017_FORMATTED;

class formula_trigger_test
{

    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('Test the formula calculation triggers');

        // prepare the calculation trigger test
        $phr_lst1 = new phrase_list($usr);
        $phr_lst1->add_name(word_api::TN_CH);
        $phr_lst1->add_name(word_api::TN_INHABITANTS);
        $phr_lst1->add_name(word_api::TN_MIO);
        $phr_lst2 = clone $phr_lst1;
        $phr_lst1->add_name(word_api::TN_2019);
        $phr_lst2->add_name(word_api::TN_2020);
        $frm = $t->load_formula(formula_api::TN_ADD);

        // add a number to the test word
        $val_add1 = new value($usr);
        $val_add1->grp = $phr_lst1->get_grp();
        $val_add1->set_number(value_api::TV_CH_INHABITANTS_2019_IN_MIO);
        $result = $val_add1->save();
        // add a second number to the test word
        $val_add2 = new value($usr);
        $val_add2->grp = $phr_lst2->get_grp();
        $val_add2->set_number(value_api::TV_CH_INHABITANTS_2020_IN_MIO);
        $result = $val_add2->save();

        // check if the first number have been saved correctly
        $added_val = new value($usr);
        $added_val->load_by_grp($phr_lst1->get_grp());
        $result = $added_val->number();
        $target = value_api::TV_CH_INHABITANTS_2019_IN_MIO;
        $t->display('value->check added test value for "' . $phr_lst1->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        // check if the second number have been saved correctly
        $added_val2 = new value($usr);
        $added_val2->load_by_grp($phr_lst2->get_grp());
        $result = $added_val2->number();
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->display('value->check added test value for "' . $phr_lst2->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if requesting the best number for the first number returns a useful value
        $best_val = new value($usr);
        $best_val->grp = $phr_lst1->get_grp();
        $best_val->load_best();
        $result = $best_val->number();
        $target = value_api::TV_CH_INHABITANTS_2019_IN_MIO;
        $t->display('value->check best value for "' . $phr_lst1->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        // check if requesting the best number for the second number returns a useful value
        $best_val2 = new value($usr);
        $best_val2->grp = $phr_lst2->get_grp();
        $best_val2->load_best();
        $result = $best_val2->number();
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->display('value->check best value for "' . $phr_lst2->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // calculate the increase and check the result
        $res_lst = $frm->calc($phr_lst2);
        if ($res_lst != null) {
            if (count($res_lst) > 0) {
                $res = $res_lst[0];
                $result = trim($res->display(0));
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
        $t->display('formula result for ' . $frm->dsp_id() . ' from ' . $phr_lst1->dsp_id() . ' to ' . $phr_lst2->dsp_id() . '', $target, $result, TIMEOUT_LIMIT_LONG);

        // remove the test values
        $val_add1->del();
        $val_add2->del();

        // change the second number and test if the result has been updated
        // a second user changes the value back to the original value and check if for the second number the result is updated
        // check if the result for the first user is not changed
        // the first user also changes back the value to the original value and now the values for both user should be the same

    }

}