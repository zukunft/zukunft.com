<?php

/*

    model/application.php - the main application object to start and stop the $app
    ---------------------

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

    Copyright (c) 1995-2018 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php;

use Random\RandomException;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'db_check.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_LOG . 'change_log.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_HELPER . 'Translator.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\db_check;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\system_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;

class test_app
{

    /**
     * start a test run
     * return null if the db connection fails or the db is not compatible
     * TODO create a separate class for starting the backend and frontend
     *
     * @param string $code_name the place that is displayed to the user e.g. add a word
     * @param bool $echo_env if true, log the environment
     * @return sql_db the open database connection
     */
    function start(
        string $code_name,
        bool   $echo_env = false
    ): sql_db
    {
        global $sys;
        global $errors;

        $sys->script = $code_name;
        $sys->times->switch(system_time_type::INIT);

        // TODO Prio 2 check if cookies are actually needed
        // resume session (based on cookies)
        session_start();
        if (empty($_SESSION[url_var::SESSION_TOKEN])) {
            try {
                $_SESSION[url_var::SESSION_TOKEN] = bin2hex(random_bytes(32));
            } catch (RandomException $e) {
                log_err('RandomException ' . $e->getMessage());
            }
        }

        /*
        require __DIR__ . '/vendor/autoload.php';
        // Looking for .env at the root directory
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        */

        // check if environment is loaded
        $env = getenv(ENVIRONMENT);
        if (!$env) {
            log_warning('no environment found using fallback values');
        } else {
            log_info('environment ' . getenv(ENVIRONMENT));
        }

        $sys->pod_name = $code_name;

        $errors = 0;

        log_debug($code_name . ': session_start');

        // log environment
        if ($echo_env) {
            $lib = new library();
            echo $lib->env_to_log() . "\n";
            phpinfo(INFO_GENERAL);
        }

        return $this->open_db($code_name);
    }

    /**
     * open the database connection and load the base cache
     * @param string $code_name the place that is displayed to the user e.g. add a word
     * @return sql_db the open database connection
     */
    function open_db(string $code_name): sql_db
    {

        global $sys;       // the global system time control including the preloaded types
        global $db_con;    // the database connection
        global $cac;       // the global user data cache including the system views
        global $cfg;       // the user configuration values
        global $mtr;       // the translation object

        // link to database
        $sys->times->switch(system_time_type::DB_OPEN);
        $db_con = new sql_db;
        $db_con->db_type = SQL_DB_TYPE;
        $sc = new sql_creator();
        $sc->set_db_type($db_con->db_type);
        $db_con->open();
        if (!$db_con->is_open()) {
            log_debug($code_name . ': start db setup');
            if ($db_con->setup()) {
                $db_con->open();
                if (!$db_con->is_open()) {
                    log_fatal('Cannot connect to database', 'prg_restart');
                }
            }
        } else {
            log_debug($code_name . ': db open');

            // check the system setup
            $sys->times->switch(system_time_type::DB_CHECK);
            $db_chk = new db_check();
            $usr_msg = $db_chk->db_check($db_con);
            if (!$usr_msg->is_ok()) {
                echo '\n';
                echo $usr_msg->all_message_text();
                $db_con->close();
                $db_con = null;
            }

            // create a virtual one-time system user to load the system users
            $usr_sys = new user();
            $usr_sys->id = users::SYSTEM_ID;
            $usr_sys->name = users::SYSTEM_NAME;

            // load system configuration
            $sys->times->switch(system_time_type::LOAD_SYS_CONFIG);
            // TODO cache the system config json and detect
            $cfg = new config_numbers($usr_sys);
            $cfg->load_cfg($usr_sys);
            $mtr = new Translator($cfg->language());

            // preload all types from the database
            $sys->times->switch(system_time_type::LOAD_TYPES);
            // the types are general so the system user can be used to load the types
            $cac = new data_object($usr_sys);
            // TODO Prio 1 review error and message handling
            /*
            if (!$sys->load_type_lists($db_con)) {
                log_err('Type loading incomplete due to ');
            }
            */
            $sys->load_type_lists($db_con);

            $log = new change_log($usr_sys);
            $db_changed = $log->create_log_references($db_con);

            // reload the type list if needed and trigger an update in the frontend
            // even tough the update of the preloaded list should already be done by the single adds
            if ($db_changed) {
                $sys->load_type_lists($db_con);
            }

        }
        $sys->times->switch(system_time_type::DEFAULT);
        return $db_con;
    }

    function end($db_con, $echo_header = true): void
    {

        if ($echo_header) {
            $html = new html_base();
            echo $html->footer();
        }

        $this->write_time($db_con);

        // Free result test
        //mysqli_free_result($result);

        // Closing connection
        $db_con->close();

        log_debug(' ... database link closed');
    }

    /**
     * write the execution time to the database if it is long
     */
    private function write_time($db_con): void
    {
        global $sys;

        $sys_time_end = microtime(true);
        if ($sys_time_end > $sys->time_limit) {
            $sys_script_id = $sys->typ_lst->sys_log_fnc->id_by_name($sys->script, false);
            if ($sys_script_id <= 0) {
                $sys_script = new system_time_type();
                $sys_script->name = $sys->script;
                $sys_script->code_id = $sys->script;
                $sys_usr = new user();
                $sys_usr->load_by_id(users::SYSTEM_ID);
                $msg = new user_message($sys_usr);
                $sys_script->save($msg);
                $sys_script_id = $sys_script->id();
            }
            $start_time_sql = date("Y-m-d H:i:s", $sys->start_time);
            $end_time_sql = date("Y-m-d H:i:s", $sys_time_end);
            $interval = $sys_time_end - $sys->start_time;
            $milliseconds = $interval;

            //$db_con->insert();
            if (in_array(rest_ctrl::REQUEST_URI, $_SERVER)) {
                $calling_uri = $_SERVER[rest_ctrl::REQUEST_URI];
            } else {
                $calling_uri = 'localhost';
            }
            // TODO Prio 1 add time report to database and calling uri
            $time_report = $sys->times->report();
            $sql = "INSERT INTO system_times (start_time, system_time_type_id, end_time, milliseconds) "
                . "VALUES ('" . $start_time_sql . "'," . $sys_script_id . ",'" . $end_time_sql . "', " . $milliseconds . ");";
            $db_con->exe($sql);
        }

        // free the global vars
        unset($sys);
    }

}
