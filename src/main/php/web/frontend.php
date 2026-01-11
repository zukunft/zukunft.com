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
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox.php';
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
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
//include_once test_paths::CONST . 'files.php';
include_once paths::SHARED_CONST . 'files.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'language_codes.php';
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
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\group\group as group_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox as sandbox_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_named as sandbox_named_ui;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

use Zukunft\ZukunftCom\main\php\cfg\db\db_check;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object as data_object_backend;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\user\user as user_backend;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as backend_user_message;
use Exception;

class frontend
{

    /*
     * api const
     */

    const string PAR_VIEW_ID = 'view'; // if the user has selected a special view, use it


    /*
     * servers
     */

    // TODO Prio 1 review (get from .env and not move to application.yaml and detect and fix it on initial program start)
    const string HOST_DEV = 'http://localhost/';
    const string HOST_UAT = 'https://test.zukunft.com/';
    const string HOST_PROD = 'https://www.zukunft.com/';
    const string HOST_SYS_LOG = '';

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
     * @param string $title
     * @return sql_db
     */
    function start(string $code_name): sql_db
    {
        global $sys;
        global $errors;

        $sys->script = $code_name;
        $sys->times->switch(system_time_type::INIT);

        // TODO Prio 2 check if cookies are actually needed
        // resume session (based on cookies)
        session_start();

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
        /*
        if ($echo_env) {
            $lib = new library();
            echo $lib->env_to_log() . "\n";
            phpinfo(INFO_GENERAL);
        }
        */

        return $this->open_db($code_name);
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
            // TODO cache the system config json and detect
            $cfg = new config_numbers($usr_sys);
            $cfg->load_cfg($usr_sys);
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

        // just for cache loading
        // TODO Prio 2 switch to user setting later
        $mtr = new Translator(language_codes::SYS);
        $usr = $this->get_user();

        $this->load_cache();

        // html header
        $html = new html_base();
        echo $html->header($title, '', api::HOST_DEV, api::BS_PATH_DEV, api::BS_CSS_PATH_DEV);

        if (self::HOST_SYS_LOG != '') {
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

        if (self::HOST_SYS_LOG != '') {
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
        $usr_msg = new user_message();
        if ($this->dto?->typ_lst_cache == null) {
            $api_msg = $this->api_get(type_lists::class);
            if ($api_msg == '' or $api_msg == null) {
                $usr_msg->add_id_with_vars(msg_id::API_MESSAGE_EMPTY, [
                    msg_id::VAR_REQUEST => 'load cache'
                ]);
            } else {
                $this->set_type_cache($api_msg);
            }
        }
        $sys->times->switch(system_time_type::DEFAULT);
        return $usr_msg;
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
     * execute database updates via api
     *
     * @param string $action the standard action
     * @param user_ui $usr the session user who has requested the view
     * @param user_message $usr_msg to enrich with potential errors
     * @param data_object $dto the frontend cache used to reduce the backend loading for the html code creation
     * @return string the html code to show the page to the user
     */
    function url_to_action(
        string       $action,
        string       $step,
        db_object_ui $dbo,
        array        $url_array,
        user_message $usr_msg,
        string       $back
    ): string
    {
        $url = ''; // the follow-up url

        // save form action
        // if the save bottom has been pressed
        if ($step > 0 and $action == url_var::CRUD_CREATE) {
            $dbo->url_mapper($url_array, $usr_msg);
            $upd_result = new user_message();
            // $upd_result = $dbo->add_via_api();

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
                $msg = $upd_result->get_last_message();
            }
        }

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
     * @param user_ui $usr the session user who has requested the view
     * @param user_message $usr_msg to enrich with potential errors
     * @param data_object $dto the frontend cache used to reduce the backend loading for the html code creation
     * @return string the html code to show the page to the user
     */
    function url_to_html(
        array        $url_array,
        user_ui      $usr,
        user_message $usr_msg,
        data_object  $dto = new data_object()
    ): string
    {

        // init the view
        $result = ''; // reset the html code var
        $msg = ''; // to collect all messages that should be shown to the user immediately

        // detect the url format and map it to standard keys
        $url_map = new url_mapper();
        $url_array = $url_map->url_to_standard($url_array, $usr_msg);
        if (!$usr_msg->is_ok()) {
            $msg_txt = $usr_msg->var_message_text();
        }

        // get vars for the main entries just to make code more readable
        $view = $url_array[url_var::MASK];
        $step = $url_array[url_var::STEP];
        $action = $url_array[url_var::ACTION] ?? null;
        $id = $url_array[url_var::ID] ?? 0; // the database id of the prime object to display

        $new_view_id = $url_array[rest_ctrl::PAR_VIEW_NEW_ID] ?? '';
        $view_words = $url_array[url_var::WORDS] ?? '';
        $back = $url_array[url_var::BACK] ?? ''; // the word id from which this value change has been called (maybe later any page)

        // TODO move to the frontend __construct
        // get the fixed frontend config
        //$api_msg = $this->api_get(type_lists::class);
        //$frontend_cache = new type_lists($api_msg);

        // use default view if nothing is set
        if (($view == 0 or $view == '' or $view == null or $view == 'null') and $id == 0) {
            $view = views::START_ID;
        }

        // get the view id if the view code id is used
        // TODO Prio 1 move to url_to_standard
        if (is_numeric($view)) {
            $view_id = $view;
        } else {
            $msk = $this->dto->typ_lst_cache->get_view($view);
            $view_id = $msk->id();
        }

        // select the main object to display
        $dbo = $this->view_id_to_dbo_ui($view_id);

        // save form action
        // if the save bottom has been pressed
        if ($step > 0 and $action == url_var::CRUD_CREATE) {
            $dbo->url_mapper($url_array, $usr_msg, $dto);
            $upd_result = $dbo->add_via_api($usr, $usr_msg);

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
                $dbo->load_by_id($id);
            } else {
                $dbo->url_mapper($url_array, $usr_msg, $dto);
            }
        } else {
            // get last term used by the user or a default value
            $wrd = $usr->last_term();
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
                $dsp_text = $msk_ui->show($dbo, $dto, $back);

                // use a fallback if the view is empty
                if ($dsp_text == '' or $msk_ui->name() == '') {
                    $msk_ui = $this->dto->typ_lst_cache->get_view(views::START);
                    $dsp_text = $msk_ui->name_tip($dbo, $back);
                }
                if ($dsp_text == '') {
                    $result .= 'Please add a component to the view by clicking on Edit on the top right.';
                } else {
                    $html = new html_base();
                    $result .= $html->header($title, '');
                    $result .= $dsp_text;
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
        $url = self::HOST_DEV . url_var::API_PATH . $lib->camelize_ex_1($class);
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

    private
    function view_id_to_dbo_ui(int $view_id): sandbox_ui|sandbox_named_ui|db_object_ui
    {
        // select the main object to display
        if (in_array($view_id, views::WORD_MASKS_IDS)) {
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
        } else {
            $dbo_ui = new word_ui();
        }
        return $dbo_ui;
    }

}
