#!/usr/bin/env php
<?php

/*

    bin/test_differences.php - write the differences of the tests of test.php and test_part.php to docs/test_differences.md
    ------------------------

    command line entry point that lists the tests that test/test_part.php calls in another way
    than test/test.php: test_part.php should call the same tests in the same order and switch off
    the tests that are not needed as a comment; reads only the code and needs no database

    this entry point only bootstraps the path consts and hands over to the
    test_differences class, so that the logic can be unit tested
    without the command line bootstrap

    usage: php bin/test_differences.php


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
use Zukunft\ZukunftCom\test\php\utils\test_differences;

// refuse to run via a web request; the report may only be created from the command line
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('test_differences.php can only be started from the command line' . PHP_EOL);
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
include_once test_paths::UTILS . 'test_differences.php';

// compare the tests of the two test runners and write the report
$md_txt = new test_differences()->md();
file_put_contents(test_files::DOCS_TEST_DIFFERENCES, $md_txt);

// repeat the summary lines of the report on the command line
foreach (explode("\n", $md_txt) as $line) {
    if (str_starts_with($line, '- ')) {
        echo $line . PHP_EOL;
    }
}
echo 'written to ' . test_files::DOCS_TEST_DIFFERENCES . PHP_EOL;
exit(0);
