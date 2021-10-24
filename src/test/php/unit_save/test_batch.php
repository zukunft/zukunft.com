<?php

/*

  test_batch.php - TESTing of the BATCH class
  --------------
  

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

function run_batch_job_test()
{

    global $usr;

    test_header('Test the batch job class (classes/batch_job.php)');

    // make sure that the test value is set independent of any previous database tests
    test_value(array(
        word::TN_CH,
        word::TN_INHABITANT,
        word::TN_MIO,
        word::TN_2020
    ),
        value::TV_CH_INHABITANTS_2020_IN_MIO);


    // prepare test adding a batch job via a list
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_CH);
    $phr_lst->add_name(word::TN_INHABITANT);
    $phr_lst->add_name(word::TN_MIO);
    $phr_lst->add_name(word::TN_2020);
    $phr_lst->load();
    $val = new value;
    $val->ids = $phr_lst->ids;
    $val->usr = $usr;
    $val->load();
    $result = $val->number;
    $target = value::TV_CH_INHABITANTS_2020_IN_MIO;
    test_dsp('batch_job->value to link', $target, $result);

    // test adding a batch job
    $job = new batch_job;
    $job->obj = $val;
    $job->type = cl(db_cl::JOB_TYPE, job_type_list::VALUE_UPDATE);
    $result = $job->add();
    if ($result > 0) {
        $target = $result;
    }
    test_dsp('batch_job->add has number "' . $result . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

}

function run_batch_job_list_test()
{

    global $usr;

    test_header('Test the batch job list class (classes/batch_job_list.php)');

    // prepare test adding a batch job via a list
    $frm = load_formula(formula::TN_INCREASE);
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_CH);
    $phr_lst->add_name(word::TN_INHABITANT);
    $phr_lst->add_name(word::TN_MIO);
    $phr_lst->add_name(word::TN_2020);
    $phr_lst->load();

    // test adding a batch job via a list
    $job_lst = new batch_job_list;
    $calc_request = new batch_job;
    $calc_request->frm = $frm;
    $calc_request->usr = $usr;
    $calc_request->phr_lst = $phr_lst;
    $result = $job_lst->add($calc_request);
    // todo review
    $target = 0;
    if ($result > 0) {
        $target = $result;
    }
    test_dsp('batch_job->add has number "' . $result . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

}
