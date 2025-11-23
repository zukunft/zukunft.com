<?php

/*

    init.php - for initial loading of the needed php scripts
    --------

    the target start process has these steps
    1. set the start time
       1.1 set the const path and code files with const.php in the same folder
       1.2 set the basis system vars with init.php in the main backend, frontend or test folder
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

use Zukunft\ZukunftCom\main\php\cfg\const\def;
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
include_once paths::MODEL_LOG_TEXT . 'text_log.php';
use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log;
global $log_txt; // the log object for standard io logging
$log_txt = new text_log();

// load environment
// open db connection
//$app = new application();


// parameters for internal testing and debugging
const LIST_MIN_NAMES = 4; // number of object names that should at least be shown
const LIST_MIN_NUM = 20; // number of object ids that should at least be shown
const DEBUG_SHOW_USER = 10; // starting from this debug level the user should be shown in the debug text



// the main global vars to shorten the code by avoiding them in many function calls as parameter
global $db_con; // the database connection
global $usr;    // the session user


// TODO check if "sudo apt-get install php-curl" is done for testing
//phpinfo();

// database links
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'db_check.php';

include_once html_paths::HTML . 'html_base.php';

// include all other libraries that are usually needed
include_once paths::MODEL_CONST . 'env.php';
include_once paths::SERVICE . 'db_cl.php';
include_once paths::SERVICE . 'config.php';

// to avoid circle include
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_LOG . 'change_link.php';

// preloaded lists
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_HELPER . 'type_lists.php';
include_once paths::MODEL_SYSTEM . 'BasicEnum.php';
include_once paths::MODEL_SYSTEM . 'sys_log_level.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::MODEL_USER . 'user_list.php';
include_once paths::MODEL_USER . 'user_profile_list.php';
include_once paths::MODEL_PHRASE . 'phrase_types.php';
include_once paths::MODEL_ELEMENT . 'element_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_link_type_list.php';
include_once paths::MODEL_VIEW . 'view_type.php';
include_once paths::MODEL_VIEW . 'view_type_list.php';
include_once paths::MODEL_COMPONENT . 'component_type_list.php';
include_once paths::MODEL_COMPONENT . 'position_type_list.php';
include_once paths::MODEL_REF . 'ref_type_list.php';
include_once paths::MODEL_REF . 'source_type_list.php';
include_once paths::MODEL_SANDBOX . 'share_type_list.php';
include_once paths::MODEL_SANDBOX . 'protection_type_list.php';
include_once paths::MODEL_LANGUAGE . 'language_list.php';
include_once paths::MODEL_LANGUAGE . 'language_form_list.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_LOG . 'change_action_list.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::MODEL_LOG . 'change_table_list.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_field_list.php';
include_once paths::MODEL_LOG_TEXT . 'text_log.php';
include_once paths::MODEL_LOG_TEXT . 'text_log_functions.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_VIEW . 'view_sys_list.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';


// used at the moment, but to be replaced with R-Project call
include_once paths::SERVICE_MATH . 'calc_internal.php';

// settings
//include_once paths::PHP_LIB . 'application.php';

// libraries that may be useful in the future
/*
include_once $root_path.'lib/test/zu_lib_auth.php';               if ($debug > 9) { echo 'user authentication loaded<br>'; }
include_once $root_path.'lib/test/config.php';             if ($debug > 9) { echo 'configuration loaded<br>'; }
*/

/*

Target is to have with version 0.1 a usable version for alpha testing. 
The roadmap for version 0.1 can be found here: https://zukunft.com/mantisbt/roadmap_page.php

The beta test is expected to start with version 0.7

*/

/*
if UI_CAN_CHANGE_... setting is true renaming an object may switch to an object with the new name
if false the user gets an error message that the object with the new name exists already

e.g. if this setting is true
     user 1 creates     "Nestle" with id 1
     and user 2 creates "Nestlé" with id 2
     now the user 1 changes "Nestle" to "Nestlé"
     1. "Nestle" will be deleted, because it is not used any more
     2. "Nestlé" with id 2 will not be excluded anymore
     
*/
const UI_CAN_CHANGE_VALUE = TRUE;
const UI_CAN_CHANGE_TIME_SERIES_VALUE = TRUE;
const UI_CAN_CHANGE_VIEW_NAME = TRUE;
const UI_CAN_CHANGE_VIEW_COMPONENT_NAME = TRUE; // dito for view components
const UI_CAN_CHANGE_VIEW_COMPONENT_LINK = TRUE; // dito for view component links
const UI_CAN_CHANGE_WORD_NAME = TRUE; // dito for words
const UI_CAN_CHANGE_triple_NAME = TRUE; // dito for phrases
const UI_CAN_CHANGE_FORMULA_NAME = TRUE; // dito for formulas
const UI_CAN_CHANGE_VERB_NAME = TRUE; // dito for verbs
const UI_CAN_CHANGE_SOURCE_NAME = TRUE; // dito for sources

// data retrieval settings
const MAX_LOOP = 10000; // maximal number of loops to avoid hanging while loops; used for example for the number of formula elements

// max number of recursive call to avoid endless looping in case of a program error
const MAX_RECURSIVE = 10;

const ZUC_MAX_CALC_LAYERS = '10000';    // max number of calculation layers



