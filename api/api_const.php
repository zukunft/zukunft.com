<?php

/*

    /api/api_const.php - set the main const for the api
    ------------------


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
// the requested level is NOT trusted yet: honoring it outside the dev environment
// would echo the live sql, table and column names and the internal call graph to
// any anonymous api caller, so keep $debug at 0 until the environment is known
global $debug;
$debug_requested = $_GET['debug'] ?? 0;
$debug = 0;

// set the path const for the initial backend and frontend settings
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

// the last safety net for a Throwable that a layer has let travel, which is a defect on its own
// (see docs/llm/structure.md): without it the api response just ends and the reason is only in
// the web server log, which the admin of the pod cannot read
set_exception_handler('log_php_exception_to_error_log');

// init.php has loaded the environment from .env, so the url debug level can now be
// honored - but only in the dev environment; any other environment stays silent to
// avoid leaking sql, table and column names or the call graph (see http/const.php)
if (getenv(ENVIRONMENT) == ENV_DEV) {
    $debug = $debug_requested;
}

// include classes used for all api calls
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
include_once paths::API_OBJECT . 'controller.php';
include_once paths::MODEL . 'application.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED . 'url_var.php';
