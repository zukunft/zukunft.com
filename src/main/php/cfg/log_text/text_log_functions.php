<?php

/*

    model/system/text_log_functions.php - general functions for standard io logging
    -----------------------------------

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
use Zukunft\ZukunftCom\main\php\cfg\const\files;

//include_once paths::DB . 'sql_db.php';
//include_once paths::MODEL_LOG_TEXT . 'text_log.php';
//include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_SYSTEM . 'sys_log_level.php';
include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_VIEW . 'view.php';
//include_once html_paths::HTML . 'html_base.php';
//include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VIEW . 'view.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'sys_log_levels.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_function;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_levels;
use Zukunft\ZukunftCom\main\php\shared\library;

/**
 * for internal functions debugging
 * a message is shown once the url &debug=N level reaches the given minimum level; pass one of the
 * named url_var::DEBUG_LEVEL_* constants (e.g. DEBUG_LEVEL_DB_WRITE for &debug=6) or, for the deeper
 * call-graph tracing, the depth level a caller wants to become visible at
 * TODO focus debug on time consuming function calls e.g. all database accesses
 *
 * @param string $msg_text debug information additional to the class and function
 * @param int|null $debug_overwrite the minimum debug level at which the message is shown; null (the default) treats it as a deep call-graph trace shown only above the last named level (from &debug=11, DEBUG_LEVEL_MAX_FIXED + 1), while e.g. DEBUG_LEVEL_DB_WRITE shows it already from &debug=6 upward
 * @return string the final output text
 */
function log_debug(string $msg_text = '', ?int $debug_overwrite = null): string
{
    global $debug;
    global $sys;

    // the second parameter is the minimum debug level at which the message is shown; without it the
    // message is a deep call-graph trace shown only above the last named level (so from
    // &debug=11 upward, DEBUG_LEVEL_MAX_FIXED + 1), while a named level like DEBUG_LEVEL_DB_WRITE
    // shows the message already from &debug=6 upward (the named levels are in url_var)
    $min_level = $debug_overwrite ?? (url_var::DEBUG_LEVEL_MAX_FIXED + 1);

    // extract the relevant part from backtrace
    if ($msg_text == '') {

        // get the last script before this script
        $backtrace = debug_backtrace();
        if (array_key_exists(1, $backtrace)) {
            $last = $backtrace[1];
        } else {
            $last = $backtrace[0];
        }

        if ($last != null) {
            if (array_key_exists('class', $last)) {
                $msg_text = $last['class'] . '->' . $last['function'] . $msg_text;
            } else {
                $msg_text = $last['function'] . $msg_text;
            }
        } else {
            $msg_text = $last['function'] . $msg_text;
        }
    }

    if ($debug >= $min_level) {
        // escape the (possibly request-derived) message before echoing it into the html response (xss)
        echo htmlspecialchars($msg_text, ENT_QUOTES) . ' ' . $sys->times->show_total() . '<br>';
        //ob_flush();
        //flush();
    }

    return $msg_text;
}


/**
 * log an info message to the text log and the log table depending on the log settings
 * @param string $msg_text
 * @param string $function_name
 * @param string $msg_description
 * @param string $trace
 * @param user|null $calling_usr
 * @param bool $force_log
 * @return string
 */
function log_info(string $msg_text,
                  string $function_name = '',
                  string $msg_description = '',
                  string $trace = '',
                  ?user  $calling_usr = null,
                  bool   $force_log = false): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_levels::INFO_ID,
        $function_name, $trace,
        $calling_usr,
        $force_log);
}

function log_warning(string  $msg_text,
                     string  $function_name = '',
                     string  $msg_description = '',
                     string  $trace = '',
                     ?user   $calling_usr = null,
                     ?sql_db $given_db_con = null): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_levels::WARNING_ID,
        $function_name,
        $trace,
        $calling_usr,
        false,
        $given_db_con
    );
}

function log_err(string $msg_text,
                 string $function_name = '',
                 string $msg_description = '',
                 string $trace = '',
                 ?user  $calling_usr = null): string
{
    global $sys;
    if ($sys !== null) {
        $sys->errors++;
    }
    // TODO move the next lines to a class and a private function "get_function_name"
    $lib = new library();
    if ($function_name == '' or $function_name == null) {
        $function_name = (new Exception)->getTraceAsString();
        $function_name = $lib->str_right_of($function_name, '#1 ');
        $function_name = $lib->str_left_of($function_name, '): ');
        $function_name = $lib->str_right_of($function_name, '/main/php/');
        $function_name = $lib->str_left_of($function_name, '.php(');
    }
    if ($function_name == '' or $function_name == null) {
        $function_name = 'no function name detected';
    }
    if ($trace == '') {
        $trace = (new Exception)->getTraceAsString();
    }
    return log_msg($msg_text,
        $msg_description,
        sys_log_levels::ERROR_ID,
        $function_name,
        $trace,
        $calling_usr);
}

/**
 * log an error message and inform the user about the error
 * TODO Prio 1 use this if even possible
 * TODO Prio 1 limit the number of error message
 * @param string $msg_txt the error message text
 * @param user_message $msg the user message object that collect the messages for the user
 * @return void
 */
function log_err_msg(string $msg_txt, user_message $msg): void
{
    $log_lnk = log_err($msg_txt);
    $msg->add(msg_id::INTERNAL, [msg_id::VAR_LOG_LINK => $log_lnk]);
}

/**
 * log an error message via api and inform the user about the error
 * TODO Prio 1 use this if even possible
 * TODO Prio 1 limit the number of error message
 * @param string $msg_txt the error message text
 * @param user_message_ui $msg the frontend user message object that collect the messages for the user
 * @return void
 */
function log_err_msg_ui(string $msg_txt, user_message_ui $msg): void
{
    $log_lnk = log_err($msg_txt);
    $msg->add(msg_id::INTERNAL, [msg_id::VAR_LOG_LINK => $log_lnk]);
}

/**
 * if still possible, write the fatal error message to the database and stop the execution
 * @param string $msg_text is a short description used to group and limit the number of error messages
 * @param string $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
 * @param string $function_name is the function name which has most likely caused the error
 * @param string $trace is the complete system trace to get more details
 * @param user|null $calling_usr the user who has trigger the error
 * @return string
 */
function log_fatal_db(
    string $msg_text,
    string $function_name,
    string $msg_description = '',
    string $trace = '',
    ?user  $calling_usr = null): string
{
    // escape the (possibly request-derived) message before echoing it into the html response (xss)
    echo 'FATAL ERROR! ' . htmlspecialchars($msg_text, ENT_QUOTES);
    $lib = new library();
    if ($function_name == '' or $function_name == null) {
        $function_name = (new Exception)->getTraceAsString();
        $function_name = $lib->str_right_of($function_name, '/git/zukunft.com/');
        $function_name = $lib->str_left_of($function_name, ': log_');
    }
    if ($trace == '') {
        $trace = (new Exception)->getTraceAsString();
    }
    return log_msg(
        'FATAL ERROR! ' . $msg_text,
        $msg_description,
        sys_log_levels::FATAL_ID,
        $function_name,
        $trace,
        $calling_usr);
}

/**
 * try to write the error message to any possible out device if database connection is lost
 * TODO move to a log class and expose only the interface function
 * @param string $msg_text is a short description that is used to group and limit the number of error messages
 * @param string $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
 * @param string $function_name is the function name which has most likely caused the error
 * @param string $trace is the complete system trace to get more details
 * @param user|null $calling_usr the user who has trigger the error
 * @return string the message that should be shown to the user if possible
 */
function log_fatal(string $msg_text,
                   string $function_name,
                   string $msg_description = '',
                   string $trace = '',
                   ?user  $calling_usr = null): string
{
    $time = new DateTime()->format('c');
    // escape the browser echo (xss); the file log below keeps the raw text for the admin
    echo $time . ': FATAL ERROR! ' . htmlspecialchars($msg_text, ENT_QUOTES) . "\n";
    // the file log must work even if the database is broken, but must never kill the response
    // itself: the fixed root path keeps the log out of the web request working directory and
    // if the web server user cannot write the file the entry goes to the server log instead
    $log_file = fopen(files::ERROR_LOG, 'a');
    if ($log_file === false) {
        error_log($time . ': FATAL ERROR! ' . $msg_text
            . ' (and ' . files::ERROR_LOG . ' is not writable)');
    } else {
        fwrite($log_file, $time . ': FATAL ERROR! ' . $msg_text . "\n");
    }
    $write_with_more_info = false;
    $usr_txt = '';
    if ($calling_usr != null) {
        $usr_txt = $calling_usr->dsp_id();
        $write_with_more_info = true;
    }
    if ($write_with_more_info and $log_file !== false) {
        fwrite($log_file, $time . ': FATAL ERROR! ' . $msg_text
            . '", by user "' . $usr_txt . "\n");
    }
    $lib = new library();
    if ($function_name == '' or $function_name == null) {
        $function_name = (new Exception)->getTraceAsString();
        $function_name = $lib->str_right_of($function_name, '/git/zukunft.com/');
        $function_name = $lib->str_left_of($function_name, ': log_');
        $write_with_more_info = true;
    }
    if ($trace == '') {
        $trace = (new Exception)->getTraceAsString();
        $write_with_more_info = true;
    }
    if ($write_with_more_info and $log_file !== false) {
        fwrite($log_file, $time . ': FATAL ERROR! ' . $msg_text . "\n"
            . $msg_description . "\n"
            . 'function ' . $function_name . "\n"
            . 'trace ' . "\n" . $trace . "\n"
            . 'by user ' . $usr_txt . "\n");
    }
    if ($log_file !== false) {
        fclose($log_file);
    }
    return $msg_text;
}

/**
 * build the html shown to the user for a critical internal error with the (possibly request
 * derived) message html-escaped, so a crafted value - e.g. a url mask that reaches
 * log_err('view ' . $view . ' not found') - cannot inject script into the error response
 * (reflected xss); the escaping lives here at the output sink so every caller is covered. the
 * internal function / file name is not shown to the user, it is only recorded in the sys_log
 *
 * @param string $msg_text the short error message that may contain request input
 * @return string the escaped html line for the error response
 */
function critical_error_html(string $msg_text): string
{
    // the internal function and file name are deliberately not shown to the user (information
    // disclosure); they are still recorded in the sys_log for the admin. the message text is
    // html-escaped so a request-derived value cannot inject script into the response (reflected xss)
    return "Zukunft.com has detected a critical internal error: <br><br>"
        . htmlspecialchars($msg_text, ENT_QUOTES) . ".<br><br>";
}

/**
 * write a log message to the database and return the message that should be shown to the user
 * with the link for more details and to trace the resolution process
 * used also for system messages so no debug calls from here to avoid loops
 *
 * @param string $msg_text is a short description used to group and limit the number of error messages
 * @param string $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
 * @param int $msg_log_level is the criticality level e.g. debug, info, warning, error or fatal error
 * @param string $function_name is the function name which has most likely caused the error
 * @param string $trace is the complete system trace to get more details
 * @param user|null $usr is the user who has probably seen the error message
 * @return string the text that can be shown to the user in the navigation bar
 * TODO return the link to the log message so that the user can trace the bug fixing
 * TODO check that log_msg is never called from any function used here
 */
function log_msg(string  $msg_text,
                 string  $msg_description,
                 int     $msg_log_level,
                 string  $function_name,
                 string  $trace,
                 ?user   $usr = null,
                 bool    $force_log = false,
                 ?sql_db $given_db_con = null): string
{

    global $sys;
    global $db_con;

    $result = '';

    // use an alternative database connection if requested
    $used_db_con = $db_con;
    if ($given_db_con != null) {
        $used_db_con = $given_db_con;
    }

    // create a database object if needed
    if ($used_db_con == null) {
        $used_db_con = new sql_db();
    }
    // try to reconnect to the database
    // TODO Prio 3 activate
    /*
    if (!$used_db_con->connected()) {
        if (!$used_db_con->open_with_retry($msg_text, $msg_description, $function_name, $trace, $usr)) {
            log_fatal('Stopped database connection retry', 'log_msg');
        }
    }
    */

    if ($used_db_con->connected()) {

        $lib = new library();

        // fill up fields with default values
        if ($msg_description == '') {
            $msg_description = $msg_text;
        }
        if ($function_name == '' or $function_name == null) {
            $function_name = (new Exception)->getTraceAsString();
            $function_name = $lib->str_right_of($function_name, '/git/zukunft.com/');
            $function_name = $lib->str_left_of($function_name, ': log_');
        }
        if ($trace == '') {
            $trace = (new Exception)->getTraceAsString();
        }
        $user_id = 0;
        if ($usr != null) {
            $user_id = $usr->id;
        }
        if ($user_id <= 0) {
            $user_id = $_SESSION[url_var::SESSION_USER_ID] ?? users::SYSTEM_ID;
        }

        // assuming that the relevant part of the message is at the beginning of the message at least to avoid double entries
        $msg_type_text = $user_id . substr($msg_text, 0, 200);
        if (!in_array($msg_type_text, $sys->log_msg_lst)) {
            $msg = new user_message();
            $sys_log = new sys_log();

            $sys->log_msg_lst[] = $msg_type_text;
            if ($msg_log_level > text_log::LOG_LEVEL or $force_log) {

                $fnc = $sys->typ_lst->sys_log_fnc->get_by_name($function_name);
                if ($fnc == null) {
                    $sys_log_fnc = new sys_log_function();
                    $sys_log_fnc->name = $function_name;
                    $sys_log_fnc->code_id = $function_name;
                    $msg->usr = $sys->user_log();
                    // for saving a new function name a system user is needed
                    $sys_msg = clone $msg;
                    $sys_msg->usr = $sys->system_user();
                    $sys_log_fnc->save($sys_msg);
                    $msg->merge($sys_msg);
                    $sys->typ_lst->sys_log_fnc->add($sys_log_fnc, false);
                }

                $sys_log->set($user_id, $function_name, $trace, $msg_log_level, $msg_text, $msg_description, $msg);
                $sys_log->insert($msg);

            }
            if ($msg_log_level >= text_log::MSG_LEVEL) {
                echo critical_error_html($msg_text);
                if ($sys_log->id > 0) {
                    $html = new html_base();
                    $url_rel = api::MAIN_SCRIPT . url_var::PAR . url_var::MASK . url_var::EQ . views::ERROR_LOG_ID
                        . url_var::ADD . url_var::ID . url_var::EQ . $sys_log->id;
                    $url = THIS_URL . $url_rel;
                    $ref = $html->ref($url_rel, $url);
                    echo 'You can track the solving of the error with this link: ' . $ref . '<br>';
                }
            } else {
                if ($msg_log_level >= text_log::DSP_LEVEL) {
                    $usr = new user();
                    $usr->load_by_id($user_id);
                    $msk = new view($usr);
                    $msk_ui = new view_ui($msk->api_json());
                    $result .= $msk_ui->dsp_navbar_simple();
                    // like the critical path: escape the message and do not disclose the function name
                    $result .= htmlspecialchars($msg_text, ENT_QUOTES) . ".<br><br>";
                }
            }
        }
    }
    return $result;
}
