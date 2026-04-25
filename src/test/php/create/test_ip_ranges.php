<?php

/*

    test/create/test_ip_ranges.php - create the test ip ranges
    ------------------------------


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

include_once paths::MODEL_SYSTEM . 'ip_range.php';
include_once paths::MODEL_SYSTEM . 'ip_range_list.php';
include_once paths::SHARED_CONST . 'ip_ranges.php';
include_once test_paths::CREATE . 'test_users.php';

use Zukunft\ZukunftCom\main\php\cfg\system\ip_range;
use Zukunft\ZukunftCom\main\php\cfg\system\ip_range_list;
use Zukunft\ZukunftCom\main\php\shared\const\ip_ranges;

class test_ip_ranges
{

    /*
     * map
     */

    /**
     * @return ip_range a ip_range entry with some dummy values
     */
    function ip_range(): ip_range
    {
        $t_usr = new test_users();
        $sys_usr = $t_usr->system_user();
        $ip_range = new ip_range();
        $ip_range->from = ip_ranges::TEST_START;
        $ip_range->to = ip_ranges::TEST_END;
        $ip_range->set_user($sys_usr);
        return $ip_range;
    }

    /**
     * @return ip_range a ip_range entry with all fields set
     */
    function ip_range_filled(): ip_range
    {
        $ip_range = $this->ip_range();
        $ip_range->reason = ip_ranges::TEST_REASON;
        $ip_range->active = false;
        return $ip_range;
    }

    /**
     * @return ip_range_list a list of ip_range entries with some dummy values
     */
    function ip_range_list(): ip_range_list
    {
        $ip_range_lst = new ip_range_list();
        $ip_range_lst->add($this->ip_range());
        return $ip_range_lst;
    }

}