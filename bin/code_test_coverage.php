#!/usr/bin/env php
<?php

/*

    bin/code_test_coverage.php - write the unit test coverage report to docs/code_test_coverage.md
    --------------------------

    command line entry point that lists all public source functions that do not yet have
    at least two unit test calls in src/test/php/unit (see docs/llm/testing.md: every
    function needs a positive and a negative test)

    this entry point only bootstraps the path consts and hands over to the
    code_test_coverage class, so that the scan logic can be unit tested
    without the command line bootstrap

    usage: php bin/code_test_coverage.php


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
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\utils\code_test_coverage;

// refuse to run via a web request; the report may only be created from the command line
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('code_test_coverage.php can only be started from the command line' . PHP_EOL);
}

// no interactive debugging on the command line
global $debug;
$debug = 0;

// set the path const for the backend bootstrap (mirrors bin/job_runner.php)
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

// set the path const for the test code (mirrors test/test_const.php)
const TEST_PATH = paths::SRC . 'test' . DIRECTORY_SEPARATOR;
const TEST_PHP_PATH = TEST_PATH . 'php' . DIRECTORY_SEPARATOR;
const TEST_CONST_PATH = TEST_PHP_PATH . 'const' . DIRECTORY_SEPARATOR;
include_once TEST_CONST_PATH . 'paths.php';

include_once test_paths::CONST . 'files.php';
include_once test_paths::UTILS . 'code_test_coverage.php';

// scan the source and the unit tests and write the coverage report
$cov = new code_test_coverage();
$md_txt = $cov->md();
file_put_contents(test_files::DOCS_TEST_COVERAGE, $md_txt);

// repeat the summary line of the report on the command line
$sum_txt = '';
foreach (explode("\n", $md_txt) as $line) {
    if ($sum_txt == '' and str_contains($line, 'public functions')) {
        $sum_txt = $line;
    }
}
echo $sum_txt . PHP_EOL;
echo 'written to ' . test_files::DOCS_TEST_COVERAGE . PHP_EOL;
exit(0);