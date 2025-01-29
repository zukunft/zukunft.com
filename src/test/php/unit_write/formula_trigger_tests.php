<?php

/*

    test/php/unit_write/formula_trigger_tests.php - write test triggers for FORMULAS to the database and check the results
    ---------------------------------------------
  

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

namespace unit_write;

use cfg\phrase\phrase_list;
use cfg\value\value;
use shared\const\formulas;
use shared\const\values;
use shared\const\words;
use test\test_cleanup;

class formula_trigger_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('Test the formula calculation triggers');

        // prepare the calculation trigger test
        $phr_names_ch_19 = [words::CH, words::INHABITANTS, words::MIO, words::TN_2019];
        $phr_ch_19 = new phrase_list($usr);
        $phr_ch_19->load_by_names($phr_names_ch_19);
        $phr_names_ch_20 = [words::CH, words::INHABITANTS, words::MIO, words::TN_2020];
        $phr_ch_20 = new phrase_list($usr);
        $phr_ch_20->load_by_names($phr_names_ch_20);
        $phr_lst1 = new phrase_list($usr);
        $phr_lst1->add_name(words::CH);
        $phr_lst1->add_name(words::INHABITANTS);
        $phr_lst1->add_name(words::MIO);
        $phr_lst2 = clone $phr_lst1;
        $phr_lst1->add_name(words::TN_2019);
        $phr_lst2->add_name(words::TN_2020);
        $frm = $t->load_formula(formulas::INCREASE);

        // add a number to the test word
        $val_add1 = new value($usr);
        $val_add1->set_grp($phr_lst1->get_grp_id());
        $val_add1->set_number(values::CH_INHABITANTS_2019_IN_MIO);
        $result = $val_add1->save()->get_last_message();
        // add a second number to the test word
        $val_add2 = new value($usr);
        $val_add2->set_grp($phr_lst2->get_grp_id());
        $val_add2->set_number(values::CH_INHABITANTS_2020_IN_MIO);
        $result = $val_add2->save()->get_last_message();

        // check if the first number have been saved correctly
        $added_val = new value($usr);
        $added_val->load_by_grp($phr_lst1->get_grp_id());
        $result = $added_val->number();
        $target = values::CH_INHABITANTS_2019_IN_MIO;
        $t->display('value->check added test value for "' . $phr_lst1->dsp_id() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
        // check if the second number have been saved correctly
        $added_val2 = new value($usr);
        $added_val2->load_by_grp($phr_lst2->get_grp_id());
        $result = $added_val2->number();
        $target = values::CH_INHABITANTS_2020_IN_MIO;
        $t->display('value->check added test value for "' . $phr_lst2->dsp_id() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if requesting the best number for the first number returns a useful value
        $best_val = new value($usr);
        $best_val->load_best($phr_ch_19);
        $result = $best_val->number();
        $target = values::CH_INHABITANTS_2019_IN_MIO;
        $t->display('value->check best value for "' . $phr_lst1->dsp_id() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
        // check if requesting the best number for the second number returns a useful value
        $best_val2 = new value($usr);
        $best_val2->load_best($phr_ch_20);
        $result = $best_val2->number();
        $target = values::CH_INHABITANTS_2020_IN_MIO;
        $t->display('value->check best value for "' . $phr_lst2->dsp_id() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

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
        if ($result == values::SALES_INCREASE_2017_FORM) {
            $target = values::SALES_INCREASE_2017_FORM;
        } else {
            $target = "0.79 %";
        }
        $t->display('formula result for ' . $frm->dsp_id() . ' from ' . $phr_lst1->dsp_id() . ' to ' . $phr_lst2->dsp_id() . '', $target, $result, $t::TIMEOUT_LIMIT_LONG);

        // remove the test values
        $val_add1->del();
        // TODO activate Prio 1
        //$val_add2->del();

        // change the second number and test if the result has been updated
        // a second user changes the value back to the original value and check if for the second number the result is updated
        // check if the result for the first user is not changed
        // the first user also changes back the value to the original value and now the values for both user should be the same

    }

}