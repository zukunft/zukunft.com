#!/usr/bin/env php
<?php

/*

    bin/math_functions.php - write the math function report to docs/math.md
    ----------------------

    command line entry point that lists the math functions with a tested example and reports a
    function that is missing in a calculation or an edge case with a different result e.g.
    between the backend and the browser; needs no database, but node for the javascript calculations

    this entry point only bootstraps the path consts and hands over to the
    math_function_report class, so that the logic can be unit tested
    without the command line bootstrap

    usage: php bin/math_functions.php


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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\utils\math_function_report;

// refuse to run via a web request; the report may only be created from the command line
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('math_functions.php can only be started from the command line' . PHP_EOL);
}

// no interactive debugging on the command line
global $debug;
$debug = 0;

// set the path const for the backend bootstrap (mirrors bin/job_runner.php)
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
const WEB = PHP_PATH . 'web' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

// the frontend path consts for the page calculation (mirrors test/test_const.php)
const WEB_CONST = WEB . 'const' . DIRECTORY_SEPARATOR;
include_once WEB_CONST . 'paths.php';
include_once html_paths::LOG_TEXT . 'text_log.php';
// first the file that loads the frontend package, because formula_calc needs the classes in this order
include_once html_paths::WEB . 'frontend.php';

// set the path const for the test code (mirrors test/test_const.php)
const TEST_PATH = paths::SRC . 'test' . DIRECTORY_SEPARATOR;
const TEST_PHP_PATH = TEST_PATH . 'php' . DIRECTORY_SEPARATOR;
const TEST_CONST_PATH = TEST_PHP_PATH . 'const' . DIRECTORY_SEPARATOR;
include_once TEST_CONST_PATH . 'paths.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::UTILS . 'math_function_report.php';

// calculate the examples and edge cases and write the report
$md_txt = new math_function_report()->md();
file_put_contents(test_files::DOCS_MATH, $md_txt);

// repeat the summary lines of the report on the command line
foreach (explode("\n", $md_txt) as $line) {
    if (str_starts_with($line, '- ') and str_contains($line, ': ') and is_numeric(substr(strrchr($line, ' '), 1))) {
        echo $line . PHP_EOL;
    }
}
echo 'written to ' . test_files::DOCS_MATH . PHP_EOL;
exit(0);
