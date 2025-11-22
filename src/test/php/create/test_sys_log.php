<?php

/*

    test/create/test_sys_log.php - create the test system log objects
    ----------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::MODEL_SYSTEM . 'sys_log_list.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'sys_log_statuus.php';
include_once test_paths::UNIT . 'sys_log_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuus;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use DateTime;

class test_sys_log
{

    /**
     * @return sys_log an open system error log entry
     */
    function sys_log(): sys_log
    {
        global $sys;
        $slg = new sys_log();
        $slg->id = 1;
        $slg->log_time = new DateTime(sys_log_tests::TV_TIME);
        $slg->usr_name = users::SYSTEM_TEST_NAME;
        $slg->log_text = sys_log_tests::TV_LOG_TEXT;
        $slg->log_trace = sys_log_tests::TV_LOG_TRACE;
        $slg->function_name = sys_log_tests::TV_FUNC_NAME;
        $slg->solver_name = sys_log_tests::TV_SOLVE_ID;
        $slg->status_id = $sys->typ_lst->sys_log_sta->id(sys_log_statuus::OPEN);
        return $slg;
    }

    /**
     * @return sys_log a closed system error log entry
     */
    function sys_log_closed(): sys_log
    {
        global $sys;
        $slg = new sys_log();
        $slg->id = 2;
        $slg->log_time = new DateTime(sys_log_tests::TV_TIME);
        $slg->usr_name = users::SYSTEM_TEST_NAME;
        $slg->log_text = sys_log_tests::T2_LOG_TEXT;
        $slg->log_trace = sys_log_tests::T2_LOG_TRACE;
        $slg->function_name = sys_log_tests::T2_FUNC_NAME;
        $slg->solver_name = sys_log_tests::TV_SOLVE_ID;
        $slg->status_id = $sys->typ_lst->sys_log_sta->id(sys_log_statuus::CLOSED);
        return $slg;
    }

    /**
     * @return sys_log_list a list of system error entries with some dummy values
     */
    function sys_log_list(): sys_log_list
    {
        $sys_lst = new sys_log_list();
        $sys_lst->add($this->sys_log());
        $sys_lst->add($this->sys_log_closed());
        return $sys_lst;
    }

}