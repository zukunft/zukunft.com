<?php

/*

    test/php/unit_write/job_tests.php - write test BATCH JOBS to the database and check the results
    ---------------------------------
  

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
use cfg\system\job;
use cfg\system\job_list;
use cfg\system\job_type_list;
use cfg\phrase\phrase_list;
use cfg\value\value;
use cfg\value\value_base;
use shared\formulas;
use shared\words;
use test\test_cleanup;

class job_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $job_typ_cac;

        $t->header('Test the batch job class (classes/job.php)');

        // make sure that the test value is set independent of any previous database tests
        $t->test_value(array(
            words::CH,
            words::INHABITANTS,
            words::MIO,
            words::TN_2020
        ),
            value_api::TV_CH_INHABITANTS_2020_IN_MIO);


        // prepare test adding a batch job via a list
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::MIO, words::TN_2020));
        $phr_lst->ex_time();
        $val = new value($usr);
        $val->load_by_grp($phr_lst->get_grp_id());
        $result = $val->number();
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->display('job->value to link', $target, $result);

        // test adding a batch job
        $job = new job($usr);
        $job->obj = $val;
        $job->set_type(job_type_list::VALUE_UPDATE);
        $result = $job->add();
        if ($result > 0) {
            $target = $result;
        }
        $t->display('job->add has number "' . $result . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

    }

    function run_list(test_cleanup $t): void
    {

        global $usr;

        $t->header('Test the batch job list class (classes/job_list.php)');

        // prepare test adding a batch job via a list
        $frm = $t->load_formula(formulas::INCREASE);
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::MIO, words::TN_2020));

        // test adding a batch job via a list
        $job_lst = new job_list($usr);
        $calc_request = new job($usr);
        $calc_request->frm = $frm;
        $calc_request->set_user($usr);
        $calc_request->phr_lst = $phr_lst;
        $result = $job_lst->add($calc_request);
        // TODO review
        $target = '';
        if ($result->is_ok()) {
            $target = $result->get_last_message();
        }
        $t->display('job->add has number "' . $result->get_last_message() . '"', $target, $result->get_last_message(), $t::TIMEOUT_LIMIT_DB_MULTI);

    }

}