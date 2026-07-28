<?php

/*

    web/init_ui.php - for initial loading of the php scripts needed by the html frontend
    ---------------

    the target start process has these steps
    1. set the start time in the script called by the user
       1.1 set the const path and code files with const.php in the same folder
       1.2 set the basis system vars with this init_ui.php for the frontend scripts
           (the backend and test scripts use init.php in the main php folder instead)
    2. load the environment that can only be changed by the server admin and a change requires a restart
       2.1 done by frontend.php
       2.2 which opens the api connection (and, until the frontend backend split is done, the database)
    3. load the system config which can be changed by the admin user online
    4. get the user and its permissions / role
    5. load the user configuration from cache if possible


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

// add as first step a global debug level var to allow also interactive debugging
global $debug; // to activate additional logging levels

// check php version
$version = explode('.', PHP_VERSION);
if ($version[0] < 8) {
    if ($version[1] < 4) {
        echo 'at least php version 8.4 is needed';
    }
}

// set all path for the frontend program code here at once
const WEB_CONST = WEB . 'const' . DIRECTORY_SEPARATOR;
include_once WEB_CONST . 'paths.php';
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

// load the backend path const that e.g. frontend.php still uses until the frontend backend split is done
include_once html_paths::CONST . 'paths.php';

// global vars for system control
include_once html_paths::MODEL_HELPER . 'system_object.php';
use Zukunft\ZukunftCom\main\php\cfg\helper\system_object;
global $sys;
$sys = new system_object('init');

// text logging to standard io
include_once html_paths::MODEL_LOG_TEXT . 'text_log_functions.php';
include_once html_paths::MODEL_LOG_TEXT . 'text_log_format.php';
include_once html_paths::MODEL_LOG_TEXT . 'text_log_level.php';
include_once html_paths::LOG_TEXT . 'text_log.php';
use Zukunft\ZukunftCom\main\php\web\log_text\text_log;
global $log_txt; // the frontend log object for standard io logging (incl. the html header display)
$log_txt = new text_log();

// load the environment settings e.g. to know if this is a dev, test or prod pod
// (env.php is in shared/const because the environment is read by the frontend and the backend)
include_once html_paths::SHARED_CONST . 'env.php';