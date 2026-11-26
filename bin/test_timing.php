#!/usr/bin/env php
<?php

/*

    bin/test_timing.php - write the time of each test section of a saved test log
    -------------------

    command line entry point that reads the text log of a test run and writes a markdown report
    with the time per test section to the standard output, so that the section that is worth to
    be run in parallel can be found; reads only the log file and needs no database

    this entry point only bootstraps the path consts and hands over to the
    test_timing class, so that the logic can be unit tested
    without the command line bootstrap

    usage: php test/test.php > test_run.log
           php bin/test_timing.php test_run.log [number of the slowest sections] > test_timing.md


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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\utils\test_timing;

// refuse to run via a web request; the report may only be created from the command line
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('test_timing.php can only be started from the command line' . PHP_EOL);
}

// no interactive debugging on the command line
global $debug;
$debug = 0;

// set the path const for the backend bootstrap (mirrors bin/test_differences.php)
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

// set the path const for the test code (mirrors test/test_const.php)
const TEST_PATH = paths::SRC . 'test' . DIRECTORY_SEPARATOR;
const TEST_PHP_PATH = TEST_PATH . 'php' . DIRECTORY_SEPARATOR;
const TEST_CONST_PATH = TEST_PHP_PATH . 'const' . DIRECTORY_SEPARATOR;
include_once TEST_CONST_PATH . 'paths.php';

include_once test_paths::UTILS . 'test_timing.php';

$log_path = $argv[1] ?? '';
if ($log_path == '' || !is_file($log_path)) {
    fwrite(STDERR, 'usage: php bin/test_timing.php <saved log of a test run> [number of the slowest sections]' . PHP_EOL);
    exit(1);
}
$top = (int)($argv[2] ?? test_timing::TOP_SECTIONS);
echo new test_timing()->md(file_get_contents($log_path), $top);
exit(0);
