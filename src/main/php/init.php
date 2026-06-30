<?php

/*

    init.php - for initial loading of the needed php scripts
    --------

    the target start process has these steps
    1. set the start time in the script called by the user
       1.1 set the const path and code files with const.php in the same folder
       1.2 set the basis system vars with this init.php in the main backend, frontend or test folder
    2. load the environment that can only be changed by the server admin and a change requires a restart
       2.1 done by application.php, frontend.php or test_app.php
       2.2 these script open the database connection, the api connection or both for testing
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
global $debug;

// check php version
$version = explode('.', PHP_VERSION);
if ($version[0] < 8) {
    if ($version[1] < 4) {
        echo 'at least php version 8.4 is needed';
    }
}

// set all path for the backend program code here at once
const CONST_PATH = PHP_PATH . 'cfg' . DIRECTORY_SEPARATOR . 'const' . DIRECTORY_SEPARATOR;
include_once CONST_PATH . 'paths.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

// set all path for the frontend program code here at once
// TODO Prio 1 move to init_ui.php
const WEB_CONST_PATH = PHP_PATH . 'web' . DIRECTORY_SEPARATOR . 'const' . DIRECTORY_SEPARATOR;
include_once WEB_CONST_PATH . 'paths.php';
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

// global vars for system control
include_once paths::MODEL_HELPER . 'system_object.php';
use Zukunft\ZukunftCom\main\php\cfg\helper\system_object;
global $sys;
$sys = new system_object('init');

// text logging to standard io
include_once paths::MODEL_LOG_TEXT . 'text_log_functions.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_format.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_level.php';
include_once html_paths::LOG_TEXT . 'text_log.php';
use Zukunft\ZukunftCom\main\php\web\log_text\text_log;
global $log_txt; // the frontend log object for standard io logging (incl. the html header display)
$log_txt = new text_log();

// the main global vars to shorten the code by avoiding them in many function calls as parameter
global $db_con; // the database connection
global $usr;    // the session user

// TODO check if "sudo apt-get install php-curl" is done for testing
//phpinfo();

// database links
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'db_check.php';


// include all other libraries that are usually needed
include_once paths::MODEL_CONST . 'env.php';
include_once paths::SERVICE . 'db_cl.php';
include_once paths::SERVICE . 'config.php';

// to avoid circle include
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_LOG . 'change_link.php';



