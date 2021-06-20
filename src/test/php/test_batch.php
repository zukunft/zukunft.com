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

function run_batch_job_test ($debug = 0) {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the batch job class (classes/batch_job.php)');

  // prepare test adding a batch job via a list
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ABB);
  $phr_lst->add_name(TW_SALES);
  $phr_lst->add_name(TW_CHF);
  $phr_lst->add_name(TW_MIO);
  $phr_lst->add_name(TW_2014);
  $phr_lst->load($debug-1);
  $val = New value;
  $val->ids = $phr_lst->ids;
  $val->usr = $usr;
  $val->load($debug-1);
  $result = $val->number;
  $target = TV_ABB_SALES_2014;
  $exe_start_time = test_show_result('batch_job->value to link', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test adding a batch job
  $job = new batch_job;
  $job->obj = $val;
  $job->type = cl(DBL_JOB_VALUE_UPDATE);
  $result = $job->add($debug-1);
  if ($result > 0) {
    $target = $result;
  }  
  $exe_start_time = test_show_result('batch_job->add has number "'.$result.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);
  
}

function run_batch_job_list_test ($debug = 0) {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the batch job list class (classes/batch_job_list.php)');

  // prepare test adding a batch job via a list
  $frm = load_formula(TF_INCREASE, $debug-1);
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ABB);
  $phr_lst->add_name(TW_SALES);
  $phr_lst->add_name(TW_CHF);
  $phr_lst->add_name(TW_MIO);
  $phr_lst->add_name(TW_2014);
  $phr_lst->load($debug-1);

  // test adding a batch job via a list
  $job_lst = new batch_job_list;
  $calc_request = New batch_job;
  $calc_request->frm     = $frm;
  $calc_request->usr     = $usr;
  $calc_request->phr_lst = $phr_lst;
  $result = $job_lst->add($calc_request, $debug-1);
  // todo review
  $target = 0;
  if ($result > 0) {
    $target = $result;
  }  
  $exe_start_time = test_show_result('batch_job->add has number "'.$result.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

}
