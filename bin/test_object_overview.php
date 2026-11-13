#!/usr/bin/env php
<?php

/*

    bin/test_object_overview.php - write the matrix of the test object factories to docs/test_object_overview.md
    ----------------------------

    command line entry point that lists per main object e.g. word and per factory type
    e.g. *_filled if the create classes have the factory with the plain name, only factories
    with an extension or no factory; reads only the code and needs no database

    this entry point only bootstraps the path consts and hands over to the
    test_object_overview class, so that the logic can be unit tested
    without the command line bootstrap

    usage: php bin/test_object_overview.php


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
use Zukunft\ZukunftCom\test\php\utils\test_object_overview;

// refuse to run via a web request; the report may only be created from the command line
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('test_object_overview.php can only be started from the command line' . PHP_EOL);
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

include_once test_paths::CONST . 'files.php';
include_once paths::MODEL_CONST . 'def.php';
include_once test_paths::UTILS . 'test_object_overview.php';

// build the factory matrix and write the report
$md_txt = new test_object_overview()->md();
file_put_contents(test_files::DOCS_TEST_OBJECT_OVERVIEW, $md_txt);

// repeat the number of missing factories on the command line
echo substr_count($md_txt, '| ' . test_object_overview::MISSING . ' ') . ' missing factories' . PHP_EOL;
echo 'written to ' . test_files::DOCS_TEST_OBJECT_OVERVIEW . PHP_EOL;
exit(0);
