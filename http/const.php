<?php

/*

    /http/test_const.php - set the main const for the frontend
    --------------------


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

// add as first step a global debug var to allow also interactive debugging
// of php script loading by adding &debug=9 to the url
// the requested level is NOT trusted yet: honoring it outside the dev
// environment would echo the live sql and the internal call graph to any
// anonymous visitor, so keep $debug at 0 until the environment is known
global $debug;
$debug_requested = $_GET['debug'] ?? 0;
$debug = 0;

// set the path const for the initial frontend settings
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
const WEB = PHP_PATH . 'web' . DIRECTORY_SEPARATOR;
include_once WEB . 'init_ui.php';

// init_ui.php has loaded the environment from .env, so the url debug level can now
// be honored - but only in the dev environment; any other environment stays
// silent to avoid leaking sql, table and column names or the call graph
if (getenv(ENVIRONMENT) == ENV_DEV) {
    $debug = $debug_requested;
}

// test path for the initial load of the test files
// TODO Prio 2 remove
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
const TEST_PATH = paths::SRC . 'test' . DIRECTORY_SEPARATOR;
// the test code path
const TEST_PHP_PATH = TEST_PATH . 'php' . DIRECTORY_SEPARATOR;
// the test const path
const TEST_CONST_PATH = TEST_PHP_PATH . 'const' . DIRECTORY_SEPARATOR;

// load test paths
include_once TEST_CONST_PATH . 'paths.php';
