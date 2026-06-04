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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_trigger_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write formula trigger ';
        $t->header($ts);

        // prepare the calculation trigger test
        $phr_names_ch_19 = [words::CH, words::INHABITANTS, words::MIO, words::YEAR_2019];
        $phr_ch_19 = new phrase_list($usr);
        $phr_ch_19->load_by_names($phr_names_ch_19);
        $phr_names_ch_20 = [words::CH, words::INHABITANTS, words::MIO, words::YEAR_2020];
        $phr_ch_20 = new phrase_list($usr);
        $phr_ch_20->load_by_names($phr_names_ch_20);
        $phr_lst1 = new phrase_list($usr);
        $phr_lst1->add_name(words::CH);
        $phr_lst1->add_name(words::INHABITANTS);
        $phr_lst1->add_name(words::MIO);
        $phr_lst2 = clone $phr_lst1;
        $phr_lst1->add_name(words::YEAR_2019);
        $phr_lst2->add_name(words::YEAR_2020);
        $frm = $t_db->load_formula(formulas::INCREASE);

        $test_name = 'add a number ' . values::CH_INHABITANTS_2019_IN_MIO . ' for 2019';
        $val_add1 = new value($usr);
        $val_add1->set_grp($phr_lst1->get_grp_id());
        $val_add1->set_number(values::CH_INHABITANTS_2019_IN_MIO);
        $t->assert_true($test_name, $val_add1->save($usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'add second number ' . values::CH_INHABITANTS_2020_IN_MIO . ' for 2020';
        $val_add2 = new value($usr);
        $val_add2->set_grp($phr_lst2->get_grp_id());
        $val_add2->set_number(values::CH_INHABITANTS_2020_IN_MIO);
        $t->assert_true($test_name, $val_add2->save($usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the first number have been saved correctly
        $added_val = new value($usr);
        $added_val->load_by_grp($phr_lst1->get_grp_id());
        $result = $added_val->number();
        $target = values::CH_INHABITANTS_2019_IN_MIO;
        $t->assert('value->check added test value for "' . $phr_lst1->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        // check if the second number have been saved correctly
        $added_val2 = new value($usr);
        $added_val2->load_by_grp($phr_lst2->get_grp_id());
        $result = $added_val2->number();
        $target = values::CH_INHABITANTS_2020_IN_MIO;
        $t->assert('value->check added test value for "' . $phr_lst2->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if requesting the best number for the first number returns a useful value
        $best_val = new value($usr);
        $best_val->load_best($phr_ch_19);
        $result = $best_val->number();
        $target = values::CH_INHABITANTS_2019_IN_MIO;
        $t->assert('value->check best value for "' . $phr_lst1->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        // check if requesting the best number for the second number returns a useful value
        $best_val2 = new value($usr);
        $best_val2->load_best($phr_ch_20);
        $result = $best_val2->number();
        $target = values::CH_INHABITANTS_2020_IN_MIO;
        $t->assert('value->check best value for "' . $phr_lst2->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // calculate the increase and check the result
        $res_lst = $frm->calc($phr_lst2);
        if ($res_lst != null) {
            if (count($res_lst) > 0) {
                $res = $res_lst[0];
                $res_dsp = new result($res->api_json([api_types::INCL_PHRASES]));
                $result = trim($res_dsp->val_formatted());
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
            $target = "0.79%";
        }
        // TODO Prio 0 activate
        //$t->assert('formula result for ' . $frm->dsp_id() . ' from ' . $phr_lst1->dsp_id() . ' to ' . $phr_lst2->dsp_id() . '', $result, $target, $t::TIMEOUT_LIMIT_LONG);

        // remove the test values
        $val_add1->del($usr_msg);
        $val_add2->del($usr_msg);

        // change the second number and test if the result has been updated
        // a second user changes the value back to the original value and check if for the second number the result is updated
        // check if the result for the first user is not changed
        // the first user also changes back the value to the original value and now the values for both user should be the same

    }

}