<?php

/*

    test/php/unit_write/batch_job_tests.php - write test BATCH JOBS to the database and check the results
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

namespace unit_write;

use api\formula\formula as formula_api;
use api\value\value as value_api;
use api\word\word as word_api;
use cfg\batch_job;
use cfg\batch_job_list;
use cfg\batch_job_type_list;
use cfg\phrase_list;
use cfg\value\value;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_DB_MULTI;

class batch_job_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $job_types;

        $t->header('Test the batch job class (classes/batch_job.php)');

        // make sure that the test value is set independent of any previous database tests
        $t->test_value(array(
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO,
            word_api::TN_2020
        ),
            value_api::TV_CH_INHABITANTS_2020_IN_MIO);


        // prepare test adding a batch job via a list
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020));
        $phr_lst->ex_time();
        $val = new value($usr);
        $val->load_by_grp($phr_lst->get_grp_id());
        $result = $val->number();
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->display('batch_job->value to link', $target, $result);

        // test adding a batch job
        $job = new batch_job($usr);
        $job->obj = $val;
        $job->set_type(batch_job_type_list::VALUE_UPDATE);
        $result = $job->add();
        if ($result > 0) {
            $target = $result;
        }
        $t->display('batch_job->add has number "' . $result . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    }

    function run_list(test_cleanup $t): void
    {

        global $usr;

        $t->header('Test the batch job list class (classes/batch_job_list.php)');

        // prepare test adding a batch job via a list
        $frm = $t->load_formula(formula_api::TN_ADD);
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020));

        // test adding a batch job via a list
        $job_lst = new batch_job_list($usr);
        $calc_request = new batch_job($usr);
        $calc_request->frm = $frm;
        $calc_request->set_user($usr);
        $calc_request->phr_lst = $phr_lst;
        $result = $job_lst->add($calc_request);
        // TODO review
        $target = '';
        if ($result->is_ok()) {
            $target = $result->get_last_message();
        }
        $t->display('batch_job->add has number "' . $result->get_last_message() . '"', $target, $result->get_last_message(), TIMEOUT_LIMIT_DB_MULTI);

    }

}