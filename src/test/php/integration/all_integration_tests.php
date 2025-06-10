<?php

/*

    test/php/integration/all_integration_tests.php - add all integration tests to the test class
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

namespace integration;

include_once SHARED_ENUM_PATH . 'user_profiles.php';
include_once SERVICE_PATH . 'config.php';
include_once TEST_CONST_PATH . 'files.php';

use test\all_tests;
use unit_write\all_unit_write_tests;

class all_integration_tests extends all_unit_write_tests
{

    function run_integration_tests(all_tests $t): void
    {
        // start the test section (ts)
        $ts = 'integration ';
        $t->header($ts);

        // do the database unit tests
        (new import_tests)->run($this);

    }

}