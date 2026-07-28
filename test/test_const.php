<?php

/*

    /test/test_const.php - set the main const for internal testing
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

// this file is included by every test runner; refuse a direct web request so the test
// tree can never be executed over http even if the .htaccess rules are not deployed
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script may only be run from the command line.');
}

// add as first step a global debug var to allow also interactive debugging
// of php script loading by adding &debug=9 to the url
global $debug;
$debug = $_GET['debug'] ?? 0;

// pin the timezone for all tests so that date-dependent results (e.g. the ATOM
// offset of a timezone-less db timestamp) are deterministic regardless of the
// host's php.ini date.timezone setting
date_default_timezone_set('UTC');

// tell the code below (e.g. SYSTEM_PAGE_VERSION in env.php) that this is a test run
// so that the pages show the minor version and a micro release does not change all html test files
const SYSTEM_TEST_RUN = true;

// set the path const for the initial backend and frontend settings
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
const WEB = PHP_PATH . 'web' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

// the tests simulate the frontend too, so create the frontend log object
// (incl. the html header display) that init_ui.php provides to the ui scripts
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\main\php\web\log_text\text_log;

const WEB_CONST = WEB . 'const' . DIRECTORY_SEPARATOR;
include_once WEB_CONST . 'paths.php';
include_once html_paths::LOG_TEXT . 'text_log.php';
global $log_txt;
$log_txt = new text_log();

// test path for the initial load of the test files
const TEST_PATH = paths::SRC . 'test' . DIRECTORY_SEPARATOR;
// the test code path
const TEST_PHP_PATH = TEST_PATH . 'php' . DIRECTORY_SEPARATOR;
// the test const path
const TEST_CONST_PATH = TEST_PHP_PATH . 'const' . DIRECTORY_SEPARATOR;

// load test paths
include_once TEST_CONST_PATH . 'paths.php';
