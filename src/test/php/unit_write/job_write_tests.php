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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'job_types.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\system\job_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class job_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write job ';
        $t->header($ts);

        // make sure that the test value is set independent of any previous database tests
        $t_db->test_value(array(
            words::CH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2020
        ),
            values::CH_INHABITANTS_2020_IN_MIO);


        // prepare test adding a batch job via a list
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::MIO, words::YEAR_2020));
        $phr_lst->ex_time();
        $val = new value($usr);
        $val->load_by_grp($phr_lst->get_grp_id());
        $result = $val->number();
        $target = values::CH_INHABITANTS_2020_IN_MIO;
        $t->assert('job->value to link', $result, $target);

        // test adding a batch job
        $job = new job($usr);
        $job->row_id = $val->id();
        $job->set_type(job_types::VALUE_UPDATE, $usr);
        $result = $job->save($usr_msg);
        if ($result > 0) {
            $target = $result;
        }
        $t->assert('job->add has number "' . $result . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

    }

    function run_list(test_cleanup $t): void
    {

        global $usr;

        // init
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db write job list ';
        $t->header($ts);

        // prepare test adding a batch job via a list
        $frm = $t_db->load_formula(formulas::INCREASE);
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::MIO, words::YEAR_2020));

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
        $t->assert('job->add has number "' . $result->get_last_message() . '"', $target, $result->get_last_message(), $t::TIMEOUT_LIMIT_DB_MULTI);

    }

}