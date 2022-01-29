<?php

/*

  test/unit_db/system.php - database unit testing of the system functions
  -----------------------


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function run_system_unit_db_tests(testing $t)
{

    global $db_con;

    $t->header('Unit database tests of the system functions');

    $t->subheader('System error log tests');

    // load the log status list
    $lst = new sys_log_status();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_sys_log->load_stati', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::LOG_STATUS, sys_log_status::NEW);
    $target = 1;
    $t->dsp('unit_db_sys_log->check ' . sys_log_status::NEW, $result, $target);

    $t->subheader('System batch job type tests');

    // load the batch job type list
    $lst = new job_type_list();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_sys_job_type->load', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::JOB_TYPE, job_type_list::VALUE_UPDATE);
    $target = 1;
    $t->dsp('unit_db_sys_job_type->check ' . job_type_list::VALUE_UPDATE, $result, $target);

}

