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
include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'sys_log_functions.php';
include_once paths::SHARED_ENUM . 'sys_log_levels.php';
include_once paths::SHARED_ENUM . 'sys_log_statuum.php';
include_once test_paths::UNIT . 'sys_log_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_function;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_functions;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_levels;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuum;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use DateTime;

class test_sys_log extends test_objects
{

    /*
     * cleanup
     */

    /**
     * delete any remaining sys log test entries like test functions for a clean test start
     */
    function cleanup(string $ts): void
    {
        parent::cleanup_objects($ts, [sys_log_functions::TEST_NAME], new sys_log_function());
    }


    /**
     * @return sys_log an open system error log entry
     */
    function sys_log(): sys_log
    {
        global $sys;
        $slg = new sys_log();
        $slg->id = 1;
        $slg->usr = $this->env->usr1;
        $slg->log_time = new DateTime(sys_log_tests::TV_TIME);
        $slg->function_id = $sys->typ_lst->sys_log_fnc->id(sys_log_functions::IMPORT_BASE_CONFIG);
        $slg->level_id = $sys->typ_lst->sys_log_lvl->id(sys_log_levels::INFO);
        $slg->log_trace = sys_log_tests::TV_LOG_TRACE;
        $slg->log_text = sys_log_tests::TV_LOG_TEXT;
        $slg->status_id = $sys->typ_lst->sys_log_sta->id(sys_log_statuum::OPEN);
        return $slg;
    }

    /**
     * @return sys_log a closed system error log entry
     */
    function sys_log_two(): sys_log
    {
        global $sys;
        $slg = new sys_log();
        $slg->id = 2;
        $slg->usr = $this->env->usr2;
        $slg->log_time = new DateTime(sys_log_tests::TV_TIME_TWO);
        $slg->function_id = $sys->typ_lst->sys_log_fnc->id(sys_log_functions::IMPORT_TEST_CONFIG);
        $slg->level_id = $sys->typ_lst->sys_log_lvl->id(sys_log_levels::ERROR);
        $slg->log_trace = sys_log_tests::T2_LOG_TRACE;
        $slg->log_text = sys_log_tests::T2_LOG_TEXT;
        $slg->solver = $this->env->usr_admin;
        $slg->status_id = $sys->typ_lst->sys_log_sta->id(sys_log_statuum::ASSIGNED);
        return $slg;
    }

    /**
     * @return sys_log a closed system error log entry
     */
    function sys_log_filled(): sys_log
    {
        global $sys;
        $slg = $this->sys_log_two();
        $slg->update_time = new DateTime(sys_log_tests::TV_TIME_ASSIGNED);
        $slg->log_description = sys_log_tests::TV_DESCRIPTION;
        $slg->solver = $this->env->usr_system;
        $slg->status_id = $sys->typ_lst->sys_log_sta->id(sys_log_statuum::RESOLVED);
        return $slg;
    }

    /**
     * @return sys_log a closed system error log entry
     */
    function sys_log_closed(): sys_log
    {
        global $sys;
        $slg = $this->sys_log_filled();
        $slg->status_id = $sys->typ_lst->sys_log_sta->id(sys_log_statuum::CLOSED);
        return $slg;
    }

    /**
     * @return sys_log_list a list of system error entries with some dummy values
     */
    function sys_log_list(): sys_log_list
    {
        $sys_lst = new sys_log_list();
        $sys_lst->add($this->sys_log());
        $sys_lst->add($this->sys_log_filled());
        return $sys_lst;
    }

}