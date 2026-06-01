<?php

/*

    web/frontend.php - the main html frontend application
    ----------------

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

namespace Zukunft\ZukunftCom\main\php\web;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::WEB_CONST . 'paths.php';

// get library that is shared between the backend and the html frontend
include_once paths::SHARED . 'library.php';

// get the api const that are shared between the backend and the html frontend
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

// get the pure html frontend objects
include_once html_paths::USER . 'user.php';

include_once html_paths::GROUP . 'group.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HELPER . 'url_mapper.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::COMPONENT . 'component_link.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_link.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SANDBOX . 'sandbox_list.php';
include_once html_paths::SANDBOX . 'sandbox_named.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'change_action_list.php';
include_once html_paths::TYPES . 'change_table_list.php';
include_once html_paths::TYPES . 'change_field_list.php';
include_once html_paths::TYPES . 'sys_log_status_list.php';
include_once html_paths::TYPES . 'job_type_list.php';
include_once html_paths::TYPES . 'language_list.php';
include_once html_paths::TYPES . 'language_form_list.php';
include_once html_paths::TYPES . 'share.php';
include_once html_paths::TYPES . 'protection.php';
include_once html_paths::TYPES . 'verbs.php';
include_once html_paths::TYPES . 'phrase_type_list.php';
include_once html_paths::TYPES . 'formula_type_list.php';
include_once html_paths::TYPES . 'formula_link_type_list.php';
include_once html_paths::TYPES . 'source_type_list.php';
include_once html_paths::TYPES . 'ref_type_list.php';
include_once html_paths::TYPES . 'view_type_list.php';
include_once html_paths::TYPES . 'view_link_type_list.php';
include_once html_paths::TYPES . 'component_type_list.php';
include_once html_paths::TYPES . 'component_link_type_list.php';
include_once html_paths::TYPES . 'position_type_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::SYSTEM . 'job.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::SYSTEM . 'sys_log.php';
include_once html_paths::VIEW . 'view_relation.php';
include_once html_paths::VIEW . 'term_view.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
//include_once test_paths::CONST . 'files.php';
include_once paths::SHARED_CONST . 'files.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'languages.php';
include_once paths::SHARED_ENUM . 'language_codes.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_HELPER . 'Translator.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

// TODO Prio 1 deprecate
include_once paths::DB . 'db_check.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_LOG . 'change_log.php';
include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';

// cfg group (alphabetic by FQN)
use Zukunft\ZukunftCom\main\php\cfg\db\db_check;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object as data_object_backend;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log as sys_log_backend;
use Zukunft\ZukunftCom\main\php\cfg\user\user as user_backend;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as backend_user_message;
// web group (alphabetic by FQN)
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\component\component_link as component_link_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link as formula_link_ui;
use Zukunft\ZukunftCom\main\php\web\group\group as group_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase as phrase_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named as combine_named_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox as sandbox_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list as sandbox_list_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_named as sandbox_named_ui;
use Zukunft\ZukunftCom\main\php\web\system\job as job_ui;
use Zukunft\ZukunftCom\main\php\web\system\language as language_ui;
use Zukunft\ZukunftCom\main\php\web\system\sys_log as sys_log_ui;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\view\term_view as term_view_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\view\view_relation as view_relation_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
// shared group (alphabetic by FQN)
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\url_var;
// test group (alphabetic by FQN)
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use DateTime;
use Exception;
use Random\RandomException;

class frontend
{

    /*
     * api const
     */

    const string PAR_VIEW_ID = 'view'; // if the user has selected a special view, use it


    /*
     * vars
     */

    private float $start_time; // the start time to detect long runners
    private string $code_name; // the name of the call script to locate issues
    private string $msg; // messages that should be shown to the user asap

    // the main data cache of the frontend
    public ?data_object $dto = null;


    /*
     * construct and map
     */

    /**
     * define the settings for this word object
     */
    function __construct(string $code_name = '')
    {
        $this->set_start_time();
        $this->set_code_name($code_name);
        $this->dto = new data_object();
    }


    /*
     * set and get
     */

    private function set_start_time(): void
    {
        $this->start_time = microtime(true);
    }

    private function set_code_name(string $code_name): void
    {
        $this->code_name = $code_name;
    }


    /*
     * session
     */

    /**
     * TODO to be deprecated
     * start a frontend session with direct db access
     *
     * @param string $code_name
     * @param Message $msg to collect any messages and suggested solutions for the user
     * @return sql_db
     */
    function start(string $code_name, Message $msg = new Message(), array $post_array = []): sql_db
    {
        global $sys;
        global $errors;

        $sys->script = $code_name;
        $sys->times->switch(system_time_type::INIT);

        // TODO Prio 2 check if cookies are actually needed
        // resume session (based on cookies)
        $session_is_fine = true;
        session_start();
        if (empty($_SESSION[url_var::SESSION_TOKEN])) {
            try {
                $_SESSION[url_var::SESSION_TOKEN] = bin2hex(random_bytes(32));
            } catch (RandomException $e) {
                log_err('RandomException ' . $e->getMessage());
            }
        } elseif (!empty($post_array[url_var::SESSION_TOKEN])) {
            // TODO Prio 0 add the session token to each frontend form
            if (!hash_equals($_SESSION[url_var::SESSION_TOKEN], $post_array[url_var::SESSION_TOKEN])) {
                $msg_txt = 'Suspect request. Please close browser, delete cache and login again.';
                log_fatal($msg_txt, 'view.php');
                log_fatal('session token is' . $_SESSION[url_var::SESSION_TOKEN] . ' but POST token is ' . $post_array[url_var::SESSION_TOKEN], 'view.php');
                $session_is_fine = false;
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

        $sys->pod_name = POD_NAME;

        $errors = 0;

        log_debug($code_name . ': session_start');

        // log environment
        /*
        if ($echo_env) {
            $lib = new library();
            echo $lib->env_to_log() . "\n";
            phpinfo(INFO_GENERAL);
        }
        */

        if ($session_is_fine) {
            return $this->open_db($code_name);
        } else {
            return new sql_db();
        }
    }

    /**
     * TODO to be deprecated
     * open the database connection and load the base cache
     * @param string $code_name the place that is displayed to the user e.g. add word
     * @return sql_db the open database connection
     */
    private function open_db(string $code_name): sql_db
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
            $usr_sys = new user_backend();
            $usr_sys->id = users::SYSTEM_ID;
            $usr_sys->name = users::SYSTEM_NAME;

            // load system configuration
            $sys->times->switch(system_time_type::LOAD_SYS_CONFIG);
            $sys->load_cache_type($db_con);
            // TODO cache the system config json and detect
            $cfg = new config_numbers($usr_sys);
            $cfg->load_cfg(null, $usr_sys);
            $mtr = new Translator($cfg->language());

            // preload all types from the database
            $sys->times->switch(system_time_type::LOAD_TYPES);
            // the types are general so the system user can be used to load the types
            $cac = new data_object_backend($usr_sys);
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

    /**
     * start a frontend session via api
     *
     * @param string $title the name of the called frontend view for logging
     * @return string the page header
     */
    function start_ui(string $title): string
    {
        global $mtr;
        $result = '';

        // resume session (based on cookies)
        // TODO review session start and end calls
        session_start();
        if (empty($_SESSION[url_var::SESSION_TOKEN])) {
            try {
                $_SESSION[url_var::SESSION_TOKEN] = bin2hex(random_bytes(32));
            } catch (RandomException $e) {
                log_err('RandomException ' . $e->getMessage());
            }
        }

        // just for cache loading
        // TODO Prio 2 switch to user setting later
        $mtr = new Translator(language_codes::SYS);
        $usr = $this->get_user();

        $this->load_cache();

        // html header
        $html = new html_base();
        echo $html->header($title, '', language_codes::SYS, THIS_URL);

        if (SYS_LOG_URL != '') {
            $result .= $this->log_info('start ' . $this->code_name);
        }
        return $result;
    }

    /**
     * write the execution time if it took too long
     * and because the frontend is using direct database access
     * close the database connection
     * @param sql_db $db_con the database connection open on start
     * @param float $start_time the start time of the calling script
     * @return string any error messages if the closing fails or if the execution time should be shown
     */
    function end(sql_db $db_con, float $start_time = 0): string
    {
        global $sys;
        if ($start_time != 0) {
            $sys->times->add($start_time - $this->start_time, 'script loading');
            $duration = microtime(true) - $start_time;
        } else {
            $duration = microtime(true) - $this->start_time;
        }
        // TODO Prio 0 review
        if ($duration > 1) {
            log_debug();
        }

        // Free result test
        //mysqli_free_result($result);

        // Closing connection
        $db_con->close();

        if (SYS_LOG_URL != '') {
            return $this->log_info('end ' . $this->code_name);
        } else {
            return '';
        }
    }

    /**
     * load the frontend cache once upfront via api
     * @return user_message
     */
    function load_cache(): user_message
    {
        global $sys;

        $sys->times->switch(system_time_type::LOAD_FRONTEND);
        $msg = new user_message();
        if ($this->dto?->typ_lst_cache == null) {
            $api_msg = $this->api_get(type_lists::class);
            if ($api_msg == '' or $api_msg == null) {
                $msg->add(msg_id::API_MESSAGE_EMPTY, [
                    msg_id::VAR_REQUEST => 'load cache'
                ]);
            } else {
                $this->set_type_cache($api_msg);
            }
        }
        $sys->times->switch(system_time_type::DEFAULT);
        return $msg;
    }

    function set_cache(data_object $dto): void
    {
        $this->dto = $dto;
    }

    /**
     * load the frontend cache from the test resource
     * TODO move to test to avoid usage of backend in frontend
     * @param user_backend $usr the backend user used for the import e.g. of the system views
     * @return void
     */
    function load_dummy_cache_from_test_resources(user_backend $usr): void
    {
        if ($this->dto?->typ_lst_cache == null) {
            $api_msg = file_get_contents(test_files::TYPE_LISTS_CACHE);
            $this->set_type_cache($api_msg);
        }
        if ($this->dto->msk_lst == null) {
            $imp = new import();
            $imp->usr = $usr;
            $usr_msg = new backend_user_message();
            $json_str = file_get_contents(files::SYSTEM_VIEWS);
            $size = strlen($json_str);
            $json_array = json_decode($json_str, true);
            $dto = $imp->get_data_object($json_array, $usr_msg, $size);
            $api_msg = $dto->view_list()->api_json();
            $this->set_view_cache($api_msg);
        }
    }

    /**
     * set the frontend cache once upfront base on the api message
     * used for the unit test without api calls
     *
     * @param string|null $api_msg with the api message as a string
     * @return void
     */
    function set_type_cache(?string $api_msg = null): void
    {
        if ($this->dto?->typ_lst_cache == null) {
            if ($this->dto == null) {
                $this->dto = new data_object();
            }
            $this->dto->typ_lst_cache = new type_lists($api_msg);
        }
    }

    /**
     * set the frontend view cache once upfront base on the api message
     * used for the unit test without api calls
     *
     * @param string|null $api_msg with the api message as a string
     * @return void
     */
    function set_view_cache(?string $api_msg = null): void
    {
        if ($this->dto?->msk_lst == null) {
            if ($this->dto == null) {
                $this->dto = new data_object();
            }
            $this->dto->msk_lst = new view_list($api_msg);
        }
    }


    /*
     * user
     */

    function get_user(): user_ui
    {
        global $usr;
        $usr = new user_ui();
        return $usr;
    }


    /*
     * execute
     */

    /**
     * execute the user request e.g. a database update and create the url for the next page
     * the execution should be done via api
     *
     * @param array $url_array the parsed url as an array
     * @param user_backend $usr_backend the backend user object updated in-place on successful login
     * @param user_ui $usr the frontend user object updated in-place on successful login
     * @param user_message $usr_msg to enrich with potential errors
     * @param data_object $dto the frontend cache used to reduce the backend loading for the html code creation
     * @param bool $do_it can be set to false for unit testing without executing the exaction
     * @return array the url array to display the result and the next step
     */
    function  url_to_action(
        array        $url_array,
        user_backend &$usr_backend,
        user_ui      &$usr,
        user_message $usr_msg,
        data_object  $dto = new data_object(),
        bool         $do_it = true
    ): array
    {
        // init the url to show the result to the user and for the next step
        $url = $url_array;

        // detect the url format and map it to standard keys
        $url_map = new url_mapper();
        $url_array = $url_map->url_to_standard($url_array, $usr_msg);

        // get vars for the main entries just to make code more readable
        $view = $url_array[url_var::MASK];
        $step = $url_array[url_var::STEP];
        $action = $url_array[url_var::ACTION] ?? null;
        $id = $url_array[url_var::ID] ?? 0; // the database id of the prime object to display
        $lan = $url_array[url_var::LANGUAGE] ?? languages::DEFAULT;

        match (true) {
            $view == views::LOGIN_ID => $url = $this->action_login($url_array, $usr_msg, $usr_backend, $usr, $do_it),
            $view == views::SIGNUP_ID => $url = $this->action_signup($url_array, $usr_msg, $usr_backend, $usr, $do_it),
            $view == views::LOGIN_ACTIVATE_ID => $url = $this->action_login_activate($url_array, $usr_msg, $usr_backend, $usr, $do_it),
            $view == views::LOGOUT_ID => $url = $this->action_logout($usr_backend, $usr, $usr_msg, $do_it),
            $view == views::LOGIN_RESET_ID => $url = $this->action_login_reset($url_array, $usr_msg, $do_it),
            $view == views::ERROR_UPDATE_ID => $url = $this->action_error_update($url_array, $usr_backend, $usr_msg, $do_it),
            in_array($view, views::ADD_MASKS_IDS) => $url = $this->action_crud(
                $url_array, $view, $usr, $usr_msg, $dto, url_var::CRUD_CREATE, $do_it),
            in_array($view, views::EDIT_MASKS_IDS) => $url = $this->action_crud(
                $url_array, $view, $usr, $usr_msg, $dto, url_var::CRUD_UPDATE, $do_it),
            in_array($view, views::DEL_MASKS_IDS) => $url = $this->action_crud(
                $url_array, $view, $usr, $usr_msg, $dto, url_var::CRUD_DELETE, $do_it),
            default => null
        };

        return $url;
    }


    /*
     * view
     */

    /**
     * create the HTML code based on the given url
     * TODO for the confirm action highlight the changes
     * TODO add the db update via api
     *
     * @param array $url_array the parsed url as an array
     * @param user_ui|null $usr the session user who has requested the view
     * @param user_message $usr_msg to enrich with potential errors
     * @param data_object $dto the frontend cache used to reduce the backend loading for the html code creation
     * @return string the html code to show the page to the user
     */
    function url_to_html(
        array        $url_array,
        user_ui|null      $usr,
        user_message $usr_msg,
        data_object  $dto = new data_object()
    ): string
    {
        $lib = new library();

        // init the view
        $result = ''; // reset the html code var
        $msg = ''; // to collect all messages that should be shown to the user immediately

        // detect the url format and map it to standard keys
        $url_map = new url_mapper();
        $url_array = $url_map->url_to_standard($url_array, $usr_msg);

        // get vars for the main entries just to make code more readable
        $view = $url_array[url_var::MASK];
        $step = $url_array[url_var::STEP];
        $action = $url_array[url_var::ACTION] ?? null;
        $id = $url_array[url_var::ID] ?? 0; // the database id of the prime object to display
        $lan = $url_array[url_var::LANGUAGE] ?? languages::DEFAULT;

        $new_view_id = $url_array[rest_ctrl::PAR_VIEW_NEW_ID] ?? '';
        $view_words = $url_array[url_var::WORDS] ?? '';
        if (array_key_exists(url_var::BACK, $url_array)) {
            $back = $lib->filter_var($url_array[url_var::BACK]); // the word id from which this value change has been called (maybe later any page)
        } else {
            $back = '';
        }

        // TODO move to the frontend __construct
        // get the fixed frontend config
        //$api_msg = $this->api_get(type_lists::class);
        //$frontend_cache = new type_lists($api_msg);

        // use default view if nothing is set
        if (($view == 0 or $view == '' or $view == null or $view == 'null') and $id == 0) {
            $view = views::START_ID;
        }

        // get the view, id and code if the view code id or id is used
        if (is_numeric($view)) {
            $view_id = $view;
            $msk = $this->dto->typ_lst_cache->get_view_by_id($view_id);
            $view_code_id = $msk?->code_id ?? '';
        } else {
            $msk = $this->dto->typ_lst_cache->get_view($view);
            $view_id = $msk->id();
            $view_code_id = $view;
        }

        // select the main object to display
        $dbo = $this->view_id_to_dbo_ui($view_id);

        // save form action
        // if the save bottom has been pressed
        if ($step > 0 and $action == url_var::CRUD_CREATE) {
            $dbo->url_mapper($url_array, $usr_msg, $dto);
            if ($usr != null) {
                $upd_result = $dbo->add_via_api($usr, $usr_msg);
            }

            // if update was fine ...
            if ($upd_result->is_ok()) {
                // TODO Prio 0 get the id from the result
                //$id = $dbo->id();
                $id = 0;
                // ... display the calling page is switched off to keep the user on the edit view and see the implications of the change
                // switched off because maybe staying on the edit page is the expected behaviour
                if ($back == '' or $back == 0) {
                    $view_id = views::START_ID;
                }
                //$result .= dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result->get_last_message();
            }
        }


        // get the main object to display
        if ($id != 0) {
            // if only the id is included in the url load the data via api
            // TODO Prio 1 why? better always reload from db
            if (count($url_array) <= 3) {
                if (in_array($view_code_id, views::VIEWS_WITHOUT_RELATED, true)) {
                    $dbo->load_by_id($id);
                } else {
                    $dbo->load_by_id_with_related($id);
                }
            } else {
                $dbo->url_mapper($url_array, $usr_msg, $dto);
            }
        } else {
            // get last term used by the user or a default value
            if ($usr != null) {
                $wrd = $usr->last_term();
            }
        }

        // select the view
        if (in_array($view_id, views::EDIT_DEL_MASKS_IDS)) {
            // TODO move as much a possible to backend functions
            if ($dbo->id() > 0) {
                // if the user has changed the view for this word, save it
                if ($new_view_id != '') {
                    $dbo->save_view($new_view_id);
                    $view_id = $new_view_id;
                } else {
                    // if the user has selected a special view, use it
                    if ($view_id == 0) {
                        // if the user has set a view for this word, use it
                        $view_id = $dbo->view_id();
                        if ($view_id <= 0) {
                            // if any user has set a view for this word, use the common view
                            $view_id = $dbo->calc_view_id();
                            if ($view_id <= 0) {
                                // if no one has set a view for this word, use the fallback view
                                $msk = $this->dto->typ_lst_cache->get_view(views::WORD_NAME);
                                $view_id = $msk->id();
                            }
                        }
                    }
                }
            } else {
                $result .= log_err("No word selected.", "view.php", '',
                    (new Exception)->getTraceAsString());
            }
        }

        // create a display object, select and load the view and display the word according to the view
        if ($view_id != 0) {
            // TODO first create the frontend object and call from the frontend object the api
            // TODO for system views avoid the backend call by using the cache from the frontend
            // TODO get the system view from the preloaded cache
            // TODO use the frontend not the backend cache
            $msk_ui = $this->dto->typ_lst_cache->get_view_by_id($view_id);
            if ($msk_ui == null) {
                $result .= log_err('No view for "' . $view_id . '" found.',
                    "view.php", '', (new Exception)->getTraceAsString());
            } else {
                $title = $msk_ui->title($dbo);
                $dsp_text = $msk_ui->show($dbo, $dto, $back, '', false, $url_array);

                // use a fallback if the view is empty
                if ($dsp_text == '' or $msk_ui->name() == '') {
                    $dsp_text = $msk_ui->name_tip();
                }
                if ($dsp_text == '') {
                    $result .= 'Please add a component to the view by clicking on Edit on the top right.';
                } else {
                    $html = new html_base();
                    $result .= $html->header($title, '', $lan);
                    if (!in_array($view_id, views::NO_NAVBAR_IDS)) {
                        $logged_in = $usr !== null && !$usr->is_ip_only();
                        $result .= $html->navbar($view_id, $url_array,
                            $logged_in ? $usr->name() : null,
                            $logged_in ? $usr->navbar_role() : null);
                    }
                    $result .= $html->main($dsp_text);
                    if ($usr_msg->has_info()) {
                        $msg_txt = $usr_msg->get_last_message_translated();
                        if ($msg_txt === '') {
                            $msg_txt = $usr_msg->get_last_message();
                        }
                        if ($msg_txt === '') {
                            $msg_txt = $usr_msg->get_last_info();
                        }
                        if ($msg_txt !== '') {
                            if ($usr_msg->has_msg_id(msg_id::PASSWORD_WRONG)) {
                                $reset_link = $html->ref(
                                    api::RESET_SCRIPT,
                                    msg_id::PASSWORD_WRONG->value,
                                    msg_id::PASSWORD_WRONG_TITLE->value
                                );
                                $notification_html = htmlspecialchars(msg_id::LOGIN_FAILED->value . '. ') . $reset_link;
                                $result .= $html->dsp_notification_html($notification_html);
                            } else {
                                $result .= $html->dsp_notification($msg_txt);
                            }
                        }
                    }
                    $result .= $html->footer();
                }
            }
        } else {
            $result .= log_err('No view for "' . $dbo->name() . '" found.',
                "view.php", '', (new Exception)->getTraceAsString());
        }

        return $result;
    }

    function show_view(int $id): string
    {
        return $this->dto->typ_lst_cache->get_html_by_id($id);
    }


    /*
     * execute
     */

    /**
     * validate credentials, start the session, and return the URL to redirect to after login
     * TODO Prio 2 review and try to avoid the backend frontend mix for user returns
     *
     * @param array $url_array the normalised URL params including username and password
     * @param user_message $usr_msg collects errors if login fails
     * @param user_backend $usr_backend updated in-place with the logged-in user on success
     * @param user_ui $usr_ui updated in-place from the backend user's api_json on success
     * @param bool $do_it false for unit tests that should not touch the session
     * @return array URL array pointing to the back page on success, or the original login URL (minus credentials) on failure
     */
    private function action_login(
        array        $url_array,
        user_message $usr_msg,
        user_backend &$usr_backend,
        user_ui      &$usr_ui,
        bool         $do_it
    ): array
    {
        // no 'htmlspecialchars()' to avoid converting usernames like O'Brien or a&b before writing to the database
        // SQL injection protection is done be using only prepared queries
        $usr_name = $url_array[url_var::USERNAME] ?? $url_array[url_var::USERNAME_HUMAN] ?? '';
        $pw = $url_array[url_var::USER_PASSWORD] ?? $url_array[url_var::USER_PASSWORD_HUMAN] ?? '';
        $logged_in = false;

        if ($do_it) {
            $db_usr = new user_backend();
            $login_msg = new backend_user_message();
            $logged_in = $db_usr->login($usr_name, $pw, $login_msg);
            if ($logged_in) {
                $usr_backend = $db_usr;
                $usr_ui->set_from_json($db_usr->api_json(), $usr_msg);
            } else {
                $dsp_login_msg = new user_message();
                $dsp_login_msg->api_mapper($login_msg->api_array());
                $usr_msg->merge($dsp_login_msg);
            }
        }

        if ($logged_in) {
            $back_array = html_base::url_par_from_back_part($url_array);
            $next_url = empty($back_array) ? [url_var::MASK => views::LOGIN_ID] : $back_array;
        } else {
            // strip credentials so they don't leak into the rendered page; preserve the mask and 9-prefixed back params
            $next_url = $url_array;
            unset($next_url[url_var::USERNAME], $next_url[url_var::USERNAME_HUMAN]);
            unset($next_url[url_var::USER_PASSWORD], $next_url[url_var::USER_PASSWORD_HUMAN]);
            unset($next_url[url_var::SESSION_TOKEN], $next_url[url_var::POST_SUBMIT]);
        }
        return $next_url;
    }

    /**
     * validate the signup form, create the user account, auto-login, and return the next URL
     *
     * @param array $url_array the normalised URL params including username, email, and passwords
     * @param user_message $usr_msg collects validation errors or save failures
     * @param user_backend $usr_backend updated in-place with the new user on success
     * @param user_ui $usr_ui updated in-place from the new user's api_json on success
     * @param bool $do_it false for unit tests that should not touch the database or session
     * @return array URL array pointing to the back page on success, or the signup page (minus passwords) on failure
     */
    private function action_signup(
        array        $url_array,
        user_message $usr_msg,
        user_backend &$usr_backend,
        user_ui      &$usr_ui,
        bool         $do_it
    ): array
    {
        // no htmlspecialchars() — SQL injection is handled by prepared queries; output escaping happens in form_input()
        $usr_name = $url_array[url_var::USERNAME] ?? $url_array[url_var::USERNAME_HUMAN] ?? '';
        $email = $url_array[url_var::EMAIL] ?? $url_array[url_var::EMAIL_HUMAN] ?? '';
        $pw = $url_array[url_var::USER_PASSWORD] ?? $url_array[url_var::USER_PASSWORD_HUMAN] ?? '';
        $pw_re = $url_array[url_var::USER_PASSWORD_RETYPE] ?? $url_array[url_var::USER_PASSWORD_RETYPE_HUMAN] ?? '';
        $signed_up = false;

        if ($do_it) {
            $existing = new user_backend();
            $existing->load_by_name($usr_name);
            if ($existing->has_db_id()) {
                $usr_msg->add(msg_id::SIGNUP_ERR_NAME_EXISTS, []);
            }
            if (empty($email)) {
                $usr_msg->add(msg_id::SIGNUP_ERR_EMAIL_EMPTY, []);
            }
            if (empty($pw)) {
                $usr_msg->add(msg_id::SIGNUP_ERR_PW_EMPTY, []);
            }
            if (empty($pw_re)) {
                $usr_msg->add(msg_id::SIGNUP_ERR_PW_RETYPE_EMPTY, []);
            }
            if (!empty($pw) && !empty($pw_re) && $pw !== $pw_re) {
                $usr_msg->add(msg_id::SIGNUP_ERR_PW_MISMATCH, []);
            }

            if ($usr_msg->is_ok()) {
                $signup_msg = new backend_user_message();
                $new_usr = new user_backend();
                $new_usr->name = $usr_name;
                $new_usr->email = $email;
                $new_usr->set_password($pw, $signup_msg);
                if ($signup_msg->is_ok()) {
                    $new_usr->save($signup_msg);
                    $usr_by_name = new user_backend();
                    $usr_by_name->load_by_name($usr_name);
                    $usr_id = $usr_by_name->id();
                    if ($usr_id > 0) {
                        session_start();
                        if (empty($_SESSION[url_var::SESSION_TOKEN])) {
                            try {
                                $_SESSION[url_var::SESSION_TOKEN] = bin2hex(random_bytes(32));
                            } catch (RandomException $e) {
                                log_err('RandomException ' . $e->getMessage());
                            }
                        }
                        $_SESSION[url_var::SESSION_USER_ID] = $usr_id;
                        $_SESSION[url_var::USERNAME_HUMAN] = $usr_name;
                        $_SESSION[url_var::SESSION_LOGGED] = true;
                        $usr_backend = $usr_by_name;
                        $usr_ui->set_from_json($usr_by_name->api_json(), $usr_msg);
                        $signed_up = true;
                    } else {
                        log_err('Cannot find id for ' . $usr_name . ' after signup.', 'action_signup');
                        $signup_msg->add(msg_id::SIGNUP_ERR_FAILED, []);
                    }
                }
                $dsp_signup_msg = new user_message();
                $dsp_signup_msg->api_mapper($signup_msg->api_array());
                $usr_msg->merge($dsp_signup_msg);
            }
        }

        if ($signed_up) {
            $back_array = html_base::url_par_from_back_part($url_array);
            $next_url = empty($back_array) ? [url_var::MASK => views::START_ID] : $back_array;
        } else {
            // strip passwords so they don't leak into the rendered page; preserve mask and 9-prefixed back params
            $next_url = $url_array;
            unset($next_url[url_var::USER_PASSWORD], $next_url[url_var::USER_PASSWORD_HUMAN]);
            unset($next_url[url_var::USER_PASSWORD_RETYPE], $next_url[url_var::USER_PASSWORD_RETYPE_HUMAN]);
            unset($next_url[url_var::SESSION_TOKEN], $next_url[url_var::POST_SUBMIT]);
        }
        return $next_url;
    }

    /**
     * validate the activation key, set the new password and auto-login the user
     * @param array $url_array the normalised URL params; expects id, key, and the two password fields
     * @param user_message $usr_msg collects validation and save errors shown to the user
     * @param user_backend $usr_backend updated in-place with the activated user on success
     * @param user_ui $usr_ui updated in-place from the activated user's api_json on success
     * @param bool $do_it false for unit tests that should not touch the database or session
     * @return array URL array pointing to the back page on success, or the activate page (minus passwords) on failure
     */
    private function action_login_activate(
        array        $url_array,
        user_message $usr_msg,
        user_backend &$usr_backend,
        user_ui      &$usr_ui,
        bool         $do_it
    ): array
    {
        global $mtr;

        $usr_id = (int)($url_array[url_var::ID] ?? 0);
        $post_key = $url_array[url_var::POST_KEY] ?? '';
        $pw = $url_array[url_var::USER_PASSWORD] ?? $url_array[url_var::USER_PASSWORD_HUMAN] ?? '';
        $pw_re = $url_array[url_var::USER_PASSWORD_RETYPE] ?? $url_array[url_var::USER_PASSWORD_RETYPE_HUMAN] ?? '';
        $activated = false;

        if ($do_it) {
            if ($usr_id <= 0) {
                $usr_msg->add_message($mtr->txt(msg_id::ACTIVATE_ERR_MISSING_ID));
            } else {
                $usr = new user_backend();
                $usr->load_by_id($usr_id);
                $db_key = $usr->activation_key ?? '';
                $db_timeout = $usr->activation_timeout;
                $db_now = $usr->db_now;

                if ($db_key === $post_key && $db_timeout !== null && $db_timeout > $db_now) {
                    if (empty($pw)) { $usr_msg->add_message($mtr->txt(msg_id::SIGNUP_ERR_PW_EMPTY)); }
                    if (empty($pw_re)) { $usr_msg->add_message($mtr->txt(msg_id::SIGNUP_ERR_PW_RETYPE_EMPTY)); }
                    if (!empty($pw) && !empty($pw_re) && $pw !== $pw_re) {
                        $usr_msg->add_message($mtr->txt(msg_id::SIGNUP_ERR_PW_MISMATCH));
                    }

                    if ($usr_msg->is_ok()) {
                        $activate_msg = new backend_user_message();
                        $usr->set_password($pw, $activate_msg);
                        if ($activate_msg->is_ok()) {
                            $usr->activation_key = '';
                            $usr->activation_timeout = new DateTime();
                            $usr->save($activate_msg);
                            $usr_by_id = new user_backend();
                            $usr_by_id->load_by_id($usr_id);
                            if ($usr_by_id->has_db_id()) {
                                session_start();
                                if (empty($_SESSION[url_var::SESSION_TOKEN])) {
                                    try {
                                        $_SESSION[url_var::SESSION_TOKEN] = bin2hex(random_bytes(32));
                                    } catch (RandomException $e) {
                                        log_err('RandomException ' . $e->getMessage());
                                    }
                                }
                                $_SESSION[url_var::SESSION_USER_ID] = $usr_id;
                                $_SESSION[url_var::USERNAME_HUMAN] = $usr_by_id->name();
                                $_SESSION[url_var::SESSION_LOGGED] = true;
                                $usr_backend = $usr_by_id;
                                $usr_ui->set_from_json($usr_by_id->api_json(), $usr_msg);
                                $activated = true;
                            } else {
                                log_err('Cannot find id ' . $usr_id . ' after password change.', 'action_login_activate');
                                $activate_msg->add_message_text($mtr->txt(msg_id::ACTIVATE_ERR_FAILED));
                            }
                        }
                        $dsp_activate_msg = new user_message();
                        $dsp_activate_msg->api_mapper($activate_msg->api_array());
                        $usr_msg->merge($dsp_activate_msg);
                    }
                } else {
                    if ($db_key !== '') {
                        $usr_msg->add_message($mtr->txt(msg_id::ACTIVATE_ERR_KEY_MISMATCH));
                    } else {
                        $usr_msg->add_message($mtr->txt(msg_id::ACTIVATE_ERR_KEY_EXPIRED));
                    }
                }
            }
        }

        if ($activated) {
            $back_array = html_base::url_par_from_back_part($url_array);
            $next_url = empty($back_array) ? [url_var::MASK => views::START_ID] : $back_array;
        } else {
            $next_url = $url_array;
            unset($next_url[url_var::USER_PASSWORD], $next_url[url_var::USER_PASSWORD_HUMAN]);
            unset($next_url[url_var::USER_PASSWORD_RETYPE], $next_url[url_var::USER_PASSWORD_RETYPE_HUMAN]);
            unset($next_url[url_var::SESSION_TOKEN], $next_url[url_var::POST_SUBMIT]);
        }
        return $next_url;
    }

    /**
     * record the logoff time, clear the session and reset both user objects to anonymous IP-only state
     * mirrors the login process: on login the users are set to the DB user; on logout they are reset to empty
     * @param user_backend $usr_backend the currently logged-in backend user; last_logoff is saved and object is reset
     * @param user_ui $usr_ui the frontend user object; reset to an empty (IP-only) object after logout
     * @param user_message $usr_msg collects errors from saving the logoff time
     * @param bool $do_it false for unit tests that should not touch the database or session
     * @return array URL array pointing to the logout confirmation view
     */
    private function action_logout(
        user_backend &$usr_backend,
        user_ui      &$usr_ui,
        user_message $usr_msg,
        bool         $do_it
    ): array
    {
        if ($do_it) {
            if ($usr_backend->has_db_id()) {
                $logoff_msg = new backend_user_message();
                $logoff_msg->usr = $usr_backend;
                $usr_backend->last_logoff = new DateTime();
                $usr_backend->save($logoff_msg);
                $dsp_logoff_msg = new user_message();
                $dsp_logoff_msg->api_mapper($logoff_msg->api_array());
                $usr_msg->merge($dsp_logoff_msg);
            }
            if (isset($_SESSION)) {
                $_SESSION = [];
                session_destroy();
            }
        }
        $usr_backend = new user_backend();
        $usr_ui = new user_ui();
        return [url_var::MASK => views::LOGOUT_ID];
    }

    /**
     * translate a message for use in outgoing emails: returns the user-language text, or
     * "user-language / English" when the user language differs from English
     * @param msg_id $id the message constant to translate
     * @return string the bilingual text suitable for an email body or subject line
     */
    private function mail_txt(msg_id $id): string
    {
        global $mtr;
        $user_txt = $mtr->txt($id);
        $en_txt = $mtr->txt($id, language_codes::EN);
        if ($user_txt === $en_txt) {
            return $user_txt;
        }
        return $user_txt . ' / ' . $en_txt;
    }

    /**
     * send a password-reset email and redirect to the activation page
     * @param array $url_array the normalised URL params (expects USERNAME_HUMAN and/or EMAIL_HUMAN)
     * @param user_message $usr_msg collects errors shown to the user
     * @param bool $do_it false for unit tests that should not touch the database or send email
     * @return array URL array for the next page
     */
    private function action_login_reset(
        array        $url_array,
        user_message $usr_msg,
        bool         $do_it
    ): array
    {
        global $mtr;

        $usr_name = $url_array[url_var::USERNAME_HUMAN] ?? '';
        $usr_mail = $url_array[url_var::EMAIL_HUMAN] ?? '';
        $db_usr = new user_backend();
        $key = '';
        $sent = false;

        if ($do_it) {
            if ($db_usr->load_by_name_or_email($usr_name, $usr_mail)) {
                $key_ok = true;
                try {
                    $key = bin2hex(random_bytes(10));
                } catch (RandomException $e) {
                    log_err('RandomException in action_login_reset: ' . $e->getMessage());
                    $usr_msg->add_message($mtr->txt(msg_id::RESET_ERR_KEY_GEN));
                    $key_ok = false;
                }
                if ($key_ok) {
                    $timeout = new DateTime();
                    try {
                        $timeout->modify('+1 day');
                    } catch (Exception $e) {
                        log_err('DateTime modify failed in action_login_reset: ' . $e->getMessage());
                    }
                    $db_usr->activation_key = $key;
                    $db_usr->activation_timeout = $timeout;
                    $reset_msg = new backend_user_message();
                    $db_usr->save($reset_msg);
                    $dsp_reset_msg = new user_message();
                    $dsp_reset_msg->api_mapper($reset_msg->api_array());
                    $usr_msg->merge($dsp_reset_msg);

                    if ($usr_msg->is_ok()) {
                        $activate_url = POD_NAME . api::LOGIN_ACTIVATE_FORWARD
                            . url_var::PAR . url_var::ID . url_var::EQ . $db_usr->id
                            . '&' . url_var::POST_KEY . url_var::EQ . $key;
                        $mail_subject = POD_NAME . ' - ' . $this->mail_txt(msg_id::RESET_MAIL_SUBJECT);
                        $mail_body = $this->mail_txt(msg_id::RESET_MAIL_HELLO) . "\n\n"
                            . $this->mail_txt(msg_id::RESET_MAIL_KEY_INTRO) . ' ' . $key . "\n\n"
                            . $this->mail_txt(msg_id::RESET_MAIL_LINK_INTRO) . "\n" . $activate_url . "\n\n"
                            . $this->mail_txt(msg_id::RESET_MAIL_IGNORE);
                        mail($db_usr->email, $mail_subject, $mail_body, users::mail_header());
                        $sent = true;
                    }
                }
            } else {
                $usr_msg->add_message($mtr->txt(msg_id::RESET_ERR_NOT_FOUND));
            }
        }

        if ($sent) {
            $next_url = [url_var::MASK => views::LOGIN_ACTIVATE_ID, url_var::ID => $db_usr->id];
        } else {
            $next_url = $url_array;
            unset($next_url[url_var::POST_SUBMIT]);
        }
        return $next_url;
    }

    /**
     * apply a sys_log status update on behalf of an admin; mirrors the action portion of
     * the legacy /http/error_update.php script: when an admin clicks "close" on a sys_log row
     * the id + status arrive as URL parameters and the matching entry is saved with the new
     * status; non-admins and incomplete parameters are ignored — the page is just re-rendered
     *
     * @param array $url_array the normalised URL params; expects ID (log id) and
     *                         rest_ctrl::PAR_LOG_STATUS (new status id)
     * @param user_backend $usr_backend the session user; only admins may perform this action
     * @param user_message $usr_msg collects backend errors so they surface in the notification bar
     * @param bool $do_it set to false in unit tests so the DB is not touched
     * @return array the URL array for the next page — stays on the error_update view with the
     *               action parameters stripped so a page reload does not re-submit the change
     */
    private function action_error_update(
        array        $url_array,
        user_backend $usr_backend,
        user_message $usr_msg,
        bool         $do_it
    ): array
    {
        if ($do_it and $usr_backend->is_admin()) {
            $log_id = (int)($url_array[url_var::ID] ?? 0);
            $status_id = (int)($url_array[rest_ctrl::PAR_LOG_STATUS] ?? 0);
            if ($log_id > 0 and $status_id > 0) {
                $err_entry = new sys_log_backend();
                $err_entry->set_user($usr_backend);
                $err_entry->id = $log_id;
                $err_entry->status_id = $status_id;
                $save_msg = new backend_user_message();
                $err_entry->save($save_msg);
                $dsp_msg = new user_message();
                $dsp_msg->api_mapper($save_msg->api_array());
                $usr_msg->merge($dsp_msg);
            }
        }
        $next_url = $url_array;
        unset($next_url[url_var::ID]);
        unset($next_url[rest_ctrl::PAR_LOG_STATUS]);
        unset($next_url[url_var::POST_SUBMIT]);
        $next_url[url_var::MASK] = views::ERROR_UPDATE_ID;
        return $next_url;
    }

    /**
     * execute a create, update, or delete action on a sandbox object and return the next URL
     * @param array $url_array the normalised URL params
     * @param int $view the view ID that determines the object type
     * @param user_ui $usr the session user executing the action
     * @param user_message $usr_msg collects errors
     * @param data_object $dto the frontend cache
     * @param string $crud one of url_var::CRUD_CREATE / CRUD_UPDATE / CRUD_DELETE
     * @param bool $do_it false for unit tests that should not touch the database
     * @return array URL array for the next page
     */
    private function action_crud(
        array        $url_array,
        int          $view,
        user_ui      $usr,
        user_message $usr_msg,
        data_object  $dto,
        string       $crud,
        bool         $do_it
    ): array
    {
        $next_url = html_base::url_from_back($url_array);
        $dbo = $this->view_id_to_dbo_ui($view);
        $dbo->url_mapper($url_array, $usr_msg, $dto);

        if ($do_it) {
            $result_msg = match ($crud) {
                url_var::CRUD_CREATE => $dbo->add_via_api($usr, $usr_msg),
                url_var::CRUD_UPDATE => $dbo->update($usr, $usr_msg),
                url_var::CRUD_DELETE => $dbo->del($usr, $usr_msg),
                default => new user_message()
            };
            if (!$result_msg->is_ok()) {
                $usr_msg->add_message($result_msg->get_last_message());
                // stay on the current view so the user can fix errors
                return $url_array;
            }
        }

        // on success: go back to the calling page or to the start view
        if ($next_url !== '') {
            parse_str(parse_url($next_url, PHP_URL_QUERY) ?? '', $next_array);
            return $next_array;
        }
        return [url_var::MASK => views::START_ID];
    }

    private function exe_process_step(
        sandbox_ui|sandbox_named_ui|db_object_ui $sbx,
        array                                    $url_array,
        user_message                             $usr_msg
    ): bool
    {

        return $usr_msg->is_ok();
    }

    /*
     * log
     */

    /**
     * send a log message to the system log server
     *
     * @param string $msg the message that should be sent
     * @return string if something is strange the message that should be shown to the user
     */
    private
    function log_info(string $msg): string
    {
        // TODO actually sent the message to the server
        return 'Info message to backend: ' . $msg;
    }


    /*
     * api
     */

    /**
     * get an api json as a string from the backend
     *
     * @param string $class the name of the class
     * @param array|string $ids
     * @param string $id_fld
     * @return string
     */
    function api_get(
        string       $class,
        array|string $ids = [],
        string       $id_fld = 'ids'
    ): string
    {
        $lib = new library();
        $class = $lib->class_to_name_pur($class);
        $url = THIS_URL . url_var::API_PATH . $lib->camelize_ex_1($class);
        if (is_array($ids)) {
            $data = array($id_fld => implode(",", $ids));
        } else {
            $data = array($id_fld => $ids);
        }
        $ctrl = new rest_call();
        return $ctrl->api_call(rest_ctrl::GET, $url, $data);
    }

    /*
     * internal
     */

    /**
     * create the frontend object that is the base for the given view id
     * @param int $view_id the id of the predefined view
     * @return sandbox_ui|sandbox_named_ui|db_object_ui|combine_named_ui|type_object|sandbox_list_ui the matching main frontend object
     */
    private function view_id_to_dbo_ui(int $view_id): sandbox_ui|sandbox_named_ui|db_object_ui|combine_named_ui|type_object|sandbox_list_ui
    {
        // select the main object to display
        if ($view_id == views::START_ID) {
            $dbo_ui = new word_ui();
        } elseif (in_array($view_id, views::WORD_MASKS_IDS)) {
            $dbo_ui = new word_ui();
        } elseif (in_array($view_id, views::VERB_MASKS_IDS)) {
            $dbo_ui = new verb_ui();
        } elseif (in_array($view_id, views::TRIPLE_MASKS_IDS)) {
            $dbo_ui = new triple_ui();
        } elseif (in_array($view_id, views::SOURCE_MASKS_IDS)) {
            $dbo_ui = new source_ui();
        } elseif (in_array($view_id, views::REF_MASKS_IDS)) {
            $dbo_ui = new ref_ui();
        } elseif (in_array($view_id, views::VALUE_MASKS_IDS)) {
            $dbo_ui = new value_ui();
        } elseif (in_array($view_id, views::GROUP_MASKS_IDS)) {
            $dbo_ui = new group_ui();
        } elseif (in_array($view_id, views::FORMULA_MASKS_IDS)) {
            $dbo_ui = new formula_ui();
        } elseif (in_array($view_id, views::RESULT_MASKS_IDS)) {
            $dbo_ui = new result_ui();
        } elseif (in_array($view_id, views::VIEW_MASKS_IDS)) {
            $dbo_ui = new view_ui();
        } elseif (in_array($view_id, views::COMPONENT_MASKS_IDS)) {
            $dbo_ui = new component_ui();
        } elseif (in_array($view_id, views::VIEW_RELATION_MASKS_IDS)) {
            $dbo_ui = new view_relation_ui();
        } elseif (in_array($view_id, views::VIEW_LINK_MASKS_IDS)) {
            $dbo_ui = new term_view_ui();
        } elseif (in_array($view_id, views::COMPONENT_LINK_MASKS_IDS)) {
            $dbo_ui = new component_link_ui();
        } elseif (in_array($view_id, views::FORMULA_LINK_MASKS_IDS)) {
            $dbo_ui = new formula_link_ui();
        } elseif (in_array($view_id, views::USER_MASKS_IDS)) {
            $dbo_ui = new user_ui();
        } elseif (in_array($view_id, views::LANGUAGE_MASKS_IDS)) {
            $dbo_ui = new language_ui(0, null);
        } elseif (in_array($view_id, views::CONFIRM_MASKS_IDS)) {
            $dbo_ui = new word_ui();
        } elseif (in_array($view_id, views::PHRASE_MASKS_IDS)) {
            $dbo_ui = new phrase_ui();
        } elseif (in_array($view_id, views::CHANGEABLE_PHRASE_VIEW_IDS)) {
            $dbo_ui = new phrase_ui();
        } elseif (in_array($view_id, views::CONTEXT_VIEW_IDS)) {
            $dbo_ui = new phrase_list_ui();
        } elseif (in_array($view_id, views::JOB_MASKS_IDS)) {
            $dbo_ui = new job_ui();
        } elseif (in_array($view_id, views::SYSTEM_LOG_VIEW_IDS)) {
            $dbo_ui = new sys_log_ui();
        } elseif ($view_id === views::ABOUT_ID
            or $view_id === views::SETUP_ID) {
            $dbo_ui = new db_object_ui();
        } elseif (in_array($view_id, views::USER_LOGIN_MASK_IDS)) {
            $dbo_ui = new user_ui();
        } elseif (in_array($view_id, views::ADMIN_MASK_IDS)) {
            $dbo_ui = new user_ui();
        } elseif ($view_id === views::ERROR_LOG_ID
            or $view_id === views::ERROR_UPDATE_ID) {
            $dbo_ui = new db_object_ui();
        } elseif ($view_id === views::WORD_FIND_ID
            or $view_id === views::SEARCH_FULL_ID) {
            $dbo_ui = new word_ui();
        } elseif ($view_id === views::SANDBOX_ID
            or $view_id === views::UNDO_ID) {
            $dbo_ui = new db_object_ui();
        } else {
            log_err('ui object missing for view id ' . $view_id);
            $dbo_ui = new word_ui();
        }
        return $dbo_ui;
    }

}
