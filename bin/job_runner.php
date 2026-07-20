#!/usr/bin/env php
<?php

/*

    bin/job_runner.php - command line dispatcher for the non-interactive backend jobs
    ------------------

    started by the systemd zukunft-jobs.service (see deploy/systemd/) once per minute to execute the
    pending backend jobs (e.g. proactive cache refresh sweeps and database cleanup) without a user
    interaction; the reactive cache updates triggered by a user request stay out of the runner

    this entry point only bootstraps the backend and hands over to the job_runner class,
    so that the job selection logic can be unit tested without the command line bootstrap


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

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\system\job_runner;
use Zukunft\ZukunftCom\main\php\cfg\user\user;

// refuse to run via a web request; the dispatcher may only be started from the command line
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('job_runner.php can only be started from the command line' . PHP_EOL);
}

// no interactive debugging on the command line
global $debug;
$debug = 0;

// set the path const for the backend bootstrap (mirrors api/api_const.php)
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

include_once paths::MODEL . 'application.php';
include_once paths::MODEL_SYSTEM . 'job_runner.php';
include_once paths::MODEL_USER . 'user.php';

// open the backend database connection without a web session (restart = true skips session_start)
$app = new application();
$db_con = $app->start('job_runner', false, true);

// execute the due jobs as the system user and report the result to systemd via the exit code
$usr = user::system();
$job_run = new job_runner($usr);
try {
    $exit_code = $job_run->run();
} catch (Throwable $e) {
    log_err('job_runner run failed: ' . $e->getMessage(), 'bin/job_runner');
    fwrite(STDERR, 'job_runner run failed: ' . $e->getMessage() . PHP_EOL);
    $exit_code = job_runner::EXIT_ERROR;
}

$app->end($db_con);
exit($exit_code);
