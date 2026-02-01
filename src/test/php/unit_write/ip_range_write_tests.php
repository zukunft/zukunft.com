<?php

/*

    test/unit/ip_range_write_tests.php - db write tests for ip ranges
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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::MODEL_SYSTEM . 'ip_range.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once test_paths::CREATE . 'test_ip_ranges.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::CONST . 'files.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\system\ip_range;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\create\test_ip_ranges;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\main\php\shared\library;

class ip_range_write_tests
{
    function run(test_cleanup $t): void
    {

        // TODO Prio 2 add test if an admin changes an ip range so that it matches another ip range
        //             in this case, the admin should be ask, if he wants to delete the original ip address
        //             and if the changes should be written to the db row with the changed ip range

    }

}
