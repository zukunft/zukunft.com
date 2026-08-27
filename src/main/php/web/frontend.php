<?php

/*

    web/frontend.php - the main html frontend application
    ----------------

    $ui is the suggested var name

    The main sections of this object are
    - api const:         const for the backend api link
    - vars:              the variables of this frontend object
    - construct and map: set the vars of this frontend object to the initial value
    - set and get:       to capsule the vars from unexpected changes
    - session:           start and end a frontend session e.g. incl. the user login
    - user:              get the user of this frontend session
    - execute:           forward a user action to the backend and create the url of the next page
    - view:              create the html code for a view
    - cached page:       serve view-only pages from the cached html pages to reduce the response time
    - log:               forward the log messages to the backend
    - api:               get json messages from the backend
    - internal:          helper functions e.g. to map a view id to the main frontend object

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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

// get library that is shared between the backend and the html frontend
include_once html_paths::SHARED . 'library.php';

// get the api const that are shared between the backend and the html frontend
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';

// get the pure html frontend objects
include_once html_paths::USER . 'user.php';

include_once html_paths::GROUP . 'group.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HELPER . 'url_mapper.php';
include_once html_paths::HELPER . 'user_request.php';
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
// to avoid that names used for testing are used in production
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once html_paths::SHARED_CONST . 'files.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'users.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_ENUM . 'languages.php';
include_once html_paths::SHARED_ENUM . 'language_codes.php';
include_once html_paths::SHARED_HELPER . 'Message.php';
include_once html_paths::SHARED_HELPER . 'Translator.php';
include_once html_paths::SHARED_TYPES . 'system_time_type.php';

// TODO Prio 1 deprecate
include_once html_paths::DB . 'db_check.php';
include_once html_paths::DB . 'sql_creator.php';
include_once html_paths::DB . 'sql_db.php';
include_once html_paths::MODEL_HELPER . 'config_numbers.php';
include_once html_paths::MODEL_HELPER . 'data_object.php';
// server admin whitelist, tls and session hardening (file based IP / user whitelist)
include_once html_paths::MODEL_HELPER . 'server_guard.php';
include_once html_paths::MODEL_HELPER . 'db_cache_page.php';
include_once html_paths::SHARED_TYPES . 'db_cache_types.php';
include_once html_paths::MODEL_IMPORT . 'import.php';
include_once html_paths::MODEL_LOG . 'change_log.php';
include_once html_paths::MODEL_SYSTEM . 'job.php';
include_once html_paths::MODEL_SYSTEM . 'sys_log.php';
include_once html_paths::MODEL_USER . 'user.php';
include_once html_paths::MODEL_USER . 'user_message.php';
include_once html_paths::SHARED_TYPES . 'job_types.php';
include_once html_paths::SHARED_TYPES . 'view_types.php';

// cfg group (alphabetic by FQN)
use Zukunft\ZukunftCom\main\php\cfg\db\db_check;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object as data_object_backend;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_types;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\system\job as job_backend;
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
use Zukunft\ZukunftCom\main\php\web\helper\user_request;
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
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
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
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
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

    // false if the request's anti-csrf session token did not match the session token any more
    // (e.g. the session has expired); set by start() and read by http/view.php to recover the
    // session gracefully instead of running the action (see session_recovery_url)
    public bool $session_token_valid = true;

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
     * TODO Prio 3 to be deprecated and replaced with ?
     * start a frontend session with direct db access
     *
     * @param string $code_name the unique identifier for the initial called code part
     * @param Message $msg to collect any messages and suggested solutions for the user
     * @param array $url_arr the parameters given with the url for the request
     * @return sql_db
     */
    function start(string $code_name, Message $msg, array $url_arr = []): sql_db
    {
        global $sys;
        $sys->script = $code_name;
        // show the main processing steps from '&debug=9' upward (url_var::DEBUG_LEVEL_MAIN_STEP)
        // to see the request lifecycle without the message flood of the levels above
        log_debug('start script ' . $code_name, url_var::DEBUG_LEVEL_MAIN_STEP);
        $sys->times->switch(system_time_type::INIT);

        // TODO Prio 2 check if cookies are actually needed
        // resume session (based on cookies)
        // in prod/test upgrade a plain-http request to https first, then harden the session cookie
        // (httponly/secure/samesite, use_strict_mode and hsts on tls) before the session starts
        server_guard::enforce_tls();
        server_guard::harden_session();
        session_start();
        if (empty($_SESSION[url_var::SESSION_TOKEN])) {
            // no (or an expired) session: create a new token so the page can be shown again
            try {
                $_SESSION[url_var::SESSION_TOKEN] = bin2hex(random_bytes(32));
            } catch (RandomException $e) {
                log_err('RandomException ' . $e->getMessage());
            }
        }
        // a data change (a submit of an add, edit or delete mask) must carry the session token that
        // every crud form emits as a hidden field; when it is missing or wrong the action is never
        // run (fail closed against csrf), so an attacker cannot csrf a victim into a change; the
        // recovery (show the login page for a logged in user, else the page again) is done in
        // http/view.php based on this flag (see session_recovery_url)
        $this->session_token_valid = self::request_token_valid($url_arr, $_SESSION[url_var::SESSION_TOKEN] ?? '');
        if (!$this->session_token_valid) {
            log_warning('request for mask ' . ($url_arr[url_var::MASK] ?? 0) . ' with a missing or invalid session token');
        }

        // enforce the file based IP / user whitelist activated on the server admin page;
        // done before opening the database so an IP reject also works while the db is offline
        server_guard::enforce();

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

        log_debug($code_name . ': session_start');

        // log environment
        /*
        if ($echo_env) {
            $lib = new library();
            echo $lib->env_to_log() . "\n";
            phpinfo(INFO_GENERAL);
        }
        */

        // an invalid session token no longer blocks the db open: the page is still shown (the action
        // is skipped in http/view.php), so the user gets a helpful page instead of a hard failure
        return $this->open_db($code_name);
    }

    /**
     * true if the request will trigger a state change through url_to_action, i.e. it is either a
     * form submit (the post submit marker, e.g. a crud change, login, signup, import or paste) or a
     * get action mask (views::GET_ACTION_IDS: logout and error_update, which act on a plain get).
     * this is the single decision shared by the dispatch in view.php and the anti-csrf token gate
     * below, so the two can never drift apart and leave an action reachable without a token
     *
     * @param array $url_arr the parameters given with the url for the request
     * @return bool true if the request triggers an action (and therefore must carry the session token)
     */
    static function request_triggers_action(array $url_arr): bool
    {
        $is_post_action = isset($url_arr[url_var::POST_SUBMIT]);
        $is_get_action = in_array($url_arr[url_var::MASK] ?? 0, views::GET_ACTION_IDS);
        $result = $is_post_action || $is_get_action;
        return $result;
    }

    /**
     * decide whether a request may proceed with respect to the anti-csrf session token
     * every request that triggers an action (see request_triggers_action) - a crud change, a login,
     * signup, import or paste submit, but also a get action mask like logout or error_update - must
     * carry the session token that the form emits as a hidden field or the action link appends as a
     * url param; without it an attacker could csrf a victim into an action, so a missing or wrong
     * token is rejected (fail closed). samesite=lax still sends the cookie on a top-level cross-site
     * get, so the get actions need the token too. a plain get navigation triggers no action and needs
     * no token; a non-action request that still sends a token is rejected only when it does not match
     *
     * @param array $url_arr the parameters given with the url for the request
     * @param string $session_token the anti-csrf token stored in the current session
     * @return bool true if the request may proceed
     */
    static function request_token_valid(array $url_arr, string $session_token): bool
    {
        $sent_token = $url_arr[url_var::SESSION_TOKEN] ?? '';
        $token_required = self::request_triggers_action($url_arr);
        $result = true;
        if ($token_required or $sent_token != '') {
            $result = $session_token != '' && hash_equals($session_token, $sent_token);
        }
        return $result;
    }

    /**
     * decide how to recover a request whose session token is not valid any more (see start()):
     * - a non-ip user that has been logged in is sent to the login page with the requested page as
     *   the '9'-prefixed back target, so after re-login the user returns to where they were
     * - for any other request (no login hint or an ip user) null is returned: the caller just shows
     *   the requested page again (a new token was created at session start) and skips the action
     *
     * @param bool $token_valid whether the session token of the request is still valid
     * @param bool $is_logged_in true if the session indicates a logged-in (non-ip) user
     * @param array $url_array the parameters of the requested page, used as the back target
     * @return array|null the login page url with the back params, or null to show the page as usual
     */
    static function session_recovery_url(bool $token_valid, bool $is_logged_in, array $url_array): ?array
    {
        $result = null;
        if (!$token_valid and $is_logged_in) {
            $back = html_base::back_url_array(html_base::page_url_array($url_array));
            $result = array_merge([url_var::MASK => views::LOGIN_ID], $back);
        }
        return $result;
    }

    /**
     * central authorization for the admin only masks (views::ADMIN_MASK_IDS, e.g. the admin main and
     * the complete system view): only an admin (or the higher system user, or the reserved system
     * test user that keeps the system privileges but displays like a normal user) may render or act
     * on them, so the dispatch refuses the request once here instead of relying on scattered per
     * renderer is_admin checks that each admin mask would otherwise have to repeat (see url_to_html / url_to_action)
     *
     * @param int|string $view_id the resolved view id (or code id) of the request
     * @param user_message_ui $msg_ui carries the requesting user (null for an anonymous request) and tells the user why the admin mask is not shown
     * @return bool true if the request is for an admin mask that the user may not access
     */
    private function admin_mask_denied(int|string $view_id, user_message_ui $msg_ui): bool
    {
        $usr = $msg_ui->usr;
        $denied = false;
        if (in_array($view_id, views::ADMIN_MASK_IDS)) {
            if ($usr == null
                or (!$usr->is_admin() and !$usr->is_system() and !$usr->is_system_test())) {
                $msg_ui->add(msg_id::ADMIN_MASK_DENIED, []);
                $denied = true;
            }
        }
        return $denied;
    }

    /**
     * TODO Prio 1 to be deprecated and use the api only for the frontend
     * open the database connection and load the base cache
     * @param string $code_name the place that is displayed to the user e.g. add word
     * @return sql_db the open database connection
     */
    private function open_db(string $code_name): sql_db
    {

        global $db_con;    // the database connection
        global $sys;       // the system time control including the preloaded types and system configuration that change rarely and is not user-specific and for easy check how many times the code writes
        global $cac;       // the backend cache of user-specific data_object
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

            // check the system setup as the virtual system user, because this is a system call
            $sys->times->switch(system_time_type::DB_CHECK);
            $db_chk = new db_check();
            $sys_msg = new backend_user_message(user_backend::system());
            if (!$db_chk->db_check($db_con, $sys_msg)) {
                echo '\n';
                echo $sys_msg->all_message_text();
                $db_con->close();
                $db_con = null;
            }

            // skip the start-up loading if the database check has failed and the connection has been closed,
            // because continuing without a database would end in a fatal crash that hides the fail message
            if ($db_con != null) {

                // create a virtual one-time system user to load the system users
                $usr_sys = new user_backend();
                $usr_sys->id = users::SYSTEM_ID;
                $usr_sys->name = users::SYSTEM_NAME;

                // preload all types, with one database read from the cached types json when available
                // or with one select per type list if the cache is missing or outdated
                $sys->times->switch(system_time_type::LOAD_TYPES);
                $sys->load_type_lists_cached($db_con, $sys_msg);

                // load system configuration
                $sys->times->switch(system_time_type::LOAD_SYS_CONFIG);
                // TODO cache the system config json and detect
                $cfg = new config_numbers($usr_sys);
                $cfg->load_cfg($sys_msg, null, $usr_sys);
                $mtr = new Translator($cfg->language());

                // honor the pod switch for the types cache, which is only known once the config is loaded
                $sys->typ_lst->reload_if_cache_denied($db_con, $sys_msg, $cfg->cache_allowed(db_cache_types::TYPES));

                $cac = new data_object_backend($usr_sys);
                if (!$sys->typ_lst->from_cache()) {
                    // check the change log references only after a fresh type load, because
                    // they can only be incomplete if the types have changed in the database
                    $log = new change_log($usr_sys);
                    $db_changed = $log->create_log_references($db_con, $sys_msg);

                    // reload the type list if needed and trigger an update in the frontend
                    // even tough the update of the preloaded list should already be done by the single adds
                    if ($db_changed) {
                        $sys->load_type_lists($db_con, $sys_msg);
                    }
                }
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
    function start_ui(string $title, user_message_ui $msg_ui): string
    {
        global $mtr;
        $result = '';

        // resume session (based on cookies)
        // TODO review session start and end calls
        // enforce tls (prod/test) then harden the session cookie before the session starts
        server_guard::enforce_tls();
        server_guard::harden_session();
        session_start();
        if (empty($_SESSION[url_var::SESSION_TOKEN])) {
            try {
                $_SESSION[url_var::SESSION_TOKEN] = bin2hex(random_bytes(32));
            } catch (RandomException $e) {
                log_err('RandomException ' . $e->getMessage());
            }
        }

        // enforce the file based IP / user whitelist activated on the server admin page
        server_guard::enforce();

        // just for cache loading
        // TODO Prio 2 switch to user setting later
        $mtr = new Translator(language_codes::SYS);
        $usr = $this->get_user();

        $this->load_cache($msg_ui);

        // html header
        $html = new html_base();
        echo $html->header($title, $msg_ui, '', language_codes::SYS, THIS_URL);

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
            // the time from the first line of the calling script until this frontend object has been
            // created, i.e. the includes and the const setup that no other section measures
            $sys->times->add($this->start_time - $start_time, system_time_type::SCRIPT_LOADING);
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

        // Closing connection (which reports itself at url_var::DEBUG_LEVEL_MAIN_STEP)
        $db_con->close();

        log_debug('end script ' . $sys->script, url_var::DEBUG_LEVEL_MAIN_STEP);

        if (SYS_LOG_URL != '') {
            return $this->log_info('end ' . $this->code_name);
        } else {
            return '';
        }
    }

    /**
     * load the frontend cache once upfront via api
     * @param user_message_ui $msg_ui to collect the load errory
     * @return bool true if all is loaded without problems
     */
    function load_cache(user_message_ui $msg_ui): bool
    {
        global $sys;
        $sys->times->switch(system_time_type::LOAD_FRONTEND);
        if ($this->dto?->typ_lst_cache == null) {
            $api_msg = $this->api_get(type_lists::class);
            if ($api_msg == '' or $api_msg == null) {
                $msg_ui->add(msg_id::API_MESSAGE_EMPTY, [
                    msg_id::VAR_REQUEST => 'load cache'
                ]);
            } else {
                $this->set_type_cache($api_msg, $msg_ui);
            }
        }
        $sys->times->switch(system_time_type::DEFAULT);
        return $msg_ui->is_ok();
    }

    function set_cache(data_object $dto): void
    {
        $this->dto = $dto;
    }

    /**
     * load the frontend cache from the test resource
     * TODO move to test to avoid usage of backend in frontend
     * @param user_message_ui $msg_ui the backend user used for the import e.g. of the system views
     * @return void
     */
    function load_dummy_cache_from_test_resources(user_message_ui $msg_ui): void
    {
        if ($this->dto?->typ_lst_cache == null) {
            $api_msg = file_get_contents(test_files::TYPE_LISTS_CACHE);
            $this->set_type_cache($api_msg, $msg_ui);
        }
        // load the system view from resource json if not already included in the cache
        if ($this->dto->msk_lst == null) {
            $imp = new import();
            $imp->usr = $msg_ui->usr;
            $msg = new backend_user_message();
            $json_str = file_get_contents(files::SYSTEM_VIEWS);
            $size = strlen($json_str);
            $json_array = json_decode($json_str, true);
            $dto = $imp->get_data_object($json_array, $msg, $size);
            $api_msg = $dto->view_list()->api_json([], $msg);
            $this->set_view_cache($api_msg);
            $msg_ui->merge($msg);
        }
    }

    /**
     * set the frontend cache once upfront base on the api message
     * used for the unit test without api calls
     *
     * @param string|null $api_msg with the api message as a string
     * @param user_message_ui $msg_ui to collect the mapping errors
     * @return void
     */
    function set_type_cache(?string $api_msg = null, user_message_ui $msg_ui = new user_message_ui()): void
    {
        if ($this->dto?->typ_lst_cache == null) {
            if ($this->dto == null) {
                $this->dto = new data_object();
            }
            $this->dto->typ_lst_cache = new type_lists();
            if ($api_msg != null) {
                $this->dto->typ_lst_cache->set_from_json($api_msg, $msg_ui);
            }
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
        $usr = new user_ui();
        return $usr;
    }


    /*
     * execute
     */

    /**
     * execute the user request e.g. a database update and create the url for the next page
     * the execution should be done via api
     * TODO Prio 0 deprecate $usr_backend and find another way to switch the user after login or signup
     *
     * @param array $url_array the parsed url as an array
     * @param user_backend $usr_backend the backend user object updated in-place on successful login
     * @param user_message_ui $msg_ui to enrich with potential errors; carries the requesting user, which is replaced on successful login
     * @param data_object $dto the frontend cache used to reduce the backend loading for the html code creation
     * @param bool $do_it can be set to false for unit testing without executing the exaction
     * @return array the url array to display the result and the next step
     */
    function url_to_action(
        array           $url_array,
        user_backend    &$usr_backend,
        user_message_ui $msg_ui,
        data_object     $dto = new data_object(),
        bool            $do_it = true
    ): array
    {
        // the requesting user of this request (docs/llm/state-and-messages.md); a request without
        // a known user (e.g. before the first login) acts as an anonymous ip-only user, and the
        // login actions below replace the local var by reference, so the switched user is written
        // back to the message after the dispatch
        $usr_ui = $msg_ui->usr ?? new user_ui();

        // init the url to show the result to the user and for the next step
        $url = $url_array;

        // detect the url format and map it to standard keys
        $url_map = new url_mapper();
        $url_array = $url_map->url_to_standard($url_array, $msg_ui);

        // get vars for the main entries just to make code more readable
        $view = $url_array[url_var::MASK];
        $step = $url_array[url_var::STEP];
        $action = $url_array[url_var::ACTION] ?? null;
        $id = $url_array[url_var::ID] ?? 0; // the database id of the prime object to display
        $lan = $url_array[url_var::LANGUAGE] ?? languages::DEFAULT;

        // central admin mask authorization: refuse to act on an admin only view for a non-admin user
        // and send them to the start view, so an admin action cannot be triggered without the rights
        if ($this->admin_mask_denied($view, $msg_ui)) {
            return [url_var::MASK => views::START_ID];
        }

        // an unconfirmed change to a sandbox object is first shown in the confirm change view
        // so the user can check the impact before it is written to the database; the change
        // fields stay in the url so the confirm view can show the pending change
        $confirm_view = $this->confirm_view_id($view, $step);
        if ($confirm_view != 0) {
            // before showing the confirm view validate the entered data so the user gets an orange
            // warning on the edit view (e.g. for an empty name) instead of confirming an invalid change;
            // the crud action is passed so checks that do not apply are skipped, e.g. an empty name
            // when the object is being deleted
            // TODO Prio 1 add an error message e.g. if the $dbo is null
            $dbo = $this->dbo_for_url($view, $url_array);
            if ($dbo instanceof db_object_ui) {
                $crud = match (true) {
                    in_array($view, views::DEL_MASKS_IDS) => url_var::CRUD_DELETE,
                    in_array($view, views::ADD_MASKS_IDS) => url_var::CRUD_CREATE,
                    default => url_var::CRUD_UPDATE,
                };
                $dbo->url_mapper($url_array, $msg_ui, $dto);
                if (!$dbo->input_valid($msg_ui, $crud, $url_array)) {
                    return $url;
                }
            }
            $url[url_var::MASK] = $confirm_view;
            // the confirm mask does not encode the object type and the confirm view has no back target
            // of its own, so set the back target to the object's own default view + id (derived from the
            // originating edit mask); the confirm view uses it to show the real object, and cancel and
            // the post-write redirect return to it via the standard '9'-prefixed back mechanism
            // TODO Prio 2 review
            $views = new views();
            $url[url_var::BACK . url_var::MASK] =
                $views->code_id_to_id($views->system_to_base($views->id_to_code_id($view)));
            if ($id != 0) {
                $url[url_var::BACK . url_var::ID] = $id;
            }
            $url[url_var::STEP] = url_var::STEP_CONFIRMED;
            return $url;
        }

        match (true) {
            $view == views::LOGIN_ID => $url = $this->action_login($url_array, $msg_ui, $usr_backend, $usr_ui, $do_it),
            $view == views::SIGNUP_ID => $url = $this->action_signup($url_array, $msg_ui, $usr_backend, $usr_ui, $do_it),
            $view == views::LOGIN_ACTIVATE_ID => $url = $this->action_login_activate($url_array, $msg_ui, $usr_backend, $usr_ui, $do_it),
            $view == views::LOGOUT_ID => $url = $this->action_logout($usr_backend, $usr_ui, $msg_ui, $do_it, $url_array),
            $view == views::LOGIN_RESET_ID => $url = $this->action_login_reset($url_array, $msg_ui, $do_it),
            $view == views::ERROR_UPDATE_ID => $url = $this->action_error_update($url_array, $msg_ui, $do_it),
            // a confirmed delete request: triggered by a del mask or by an explicit delete action; the
            // explicit action overrules the crud action derived from the mask, because e.g. the delete
            // of a just added object is posted with the add mask of the object
            $action == url_var::CRUD_DELETE and $step == url_var::STEP_CONFIRMED,
                in_array($view, views::DEL_MASKS_IDS) and $step == url_var::STEP_CONFIRMED => $url = $this->action_crud(
                $url_array, $view, $msg_ui, $dto, url_var::CRUD_DELETE, $do_it),
            // a confirmed create request: triggered by an add mask or by an explicit create action
            $action == url_var::CRUD_CREATE and $step == url_var::STEP_CONFIRMED,
                in_array($view, views::ADD_MASKS_IDS) and $step == url_var::STEP_CONFIRMED => $url = $this->action_crud(
                $url_array, $view, $msg_ui, $dto, url_var::CRUD_CREATE, $do_it),
            in_array($view, views::EDIT_MASKS_IDS) and $step == url_var::STEP_CONFIRMED => $url = $this->action_crud(
                $url_array, $view, $msg_ui, $dto, url_var::CRUD_UPDATE, $do_it),
            default => $this->log_ignored_write_step($view, $step, $msg_ui)
        };

        // a login, signup, activation or logout has replaced the local user var by reference, so
        // store the (possibly switched) requesting user back on the message: from here on every
        // function of this request sees the new user via $usr_msg->usr (the user switch on login
        // is the one sanctioned change of the requesting user after the entry point assignment)
        $msg_ui->usr = $usr_ui;

        return $url;
    }


    /*
     * view
     */

    /**
     * the effective view id for a request, defaulting to the start view when neither a view nor an
     * object is given; used by url_to_html for the rendering and by url_cache_key for the cache key
     * so both resolve the default landing page the same way
     *
     * @param int|string|null $view the requested view id or code id, or an empty value if none is given
     * @param int|string $id the requested object id, or 0 if none is given
     * @return int|string|null the requested view if set, otherwise the start view id
     */
    private static function default_view_id(int|string|null $view, int|string $id = 0): int|string|null
    {
        $result = $view;
        if (($view == 0 or $view == '' or $view == null or $view == 'null') and $id == 0) {
            $result = views::START_ID;
        }
        return $result;
    }

    /**
     * create the HTML code based on the given url
     * TODO for the confirm action highlight the changes
     * TODO add the db update via api
     *
     * @param array $url_array the parsed url as an array
     * @param user_message_ui $msg_ui to enrich with potential errors; carries the requesting user (null for an anonymous request)
     * @param data_object $dto the frontend cache used to reduce the backend loading for the html code creation
     * @param bool $test_mode true to render a reproducible page without backend calls e.g. for a snapshot test
     * @return string the html code to show the page to the user
     */
    function url_to_html(
        array           $url_array,
        user_message_ui $msg_ui,
        data_object     $dto = new data_object(),
        bool            $test_mode = false
    ): string
    {
        // the requesting user of this request; null renders the page for an anonymous user
        // (docs/llm/state-and-messages.md)
        $usr = $msg_ui->usr;

        // publish the requesting user as the session user of the request cache ($ui_sys->usr),
        // because the renderers that cannot take the message read the session user from there,
        // e.g. the 'my' tab of the view tab box (ui_preview::user_overwrites_table) and the
        // admin-only field filter (change_log_list::filter_admin_fields); without this the
        // cache keeps its empty constructor user and every page renders as not logged in
        if ($usr != null) {
            $dto->usr = $usr;
        }

        $lib = new library();

        // init the view
        $result = ''; // reset the html code var

        // detect the url format and map it to standard keys
        $url_map = new url_mapper();
        $url_array = $url_map->url_to_standard($url_array, $msg_ui);

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

        // TODO Prio 1 move to the frontend __construct
        // get the fixed frontend config
        //$api_msg = $this->api_get(type_lists::class);
        //$frontend_cache = new type_lists($api_msg);

        // use the default start view if neither a view nor an object is set
        $view = self::default_view_id($view, $id);

        // the view cache must be loaded (via load_cache or load_dummy_cache_from_test_resources) before rendering
        if ($this->dto?->typ_lst_cache == null) {
            return log_err('frontend view cache not loaded before url_to_html for view "' . $view . '"',
                'frontend->url_to_html');
        }

        // get the view, id and code if the view code id or id is used
        if (is_numeric($view)) {
            $view_id = $view;
            $msk = $this->dto->typ_lst_cache->get_view_by_id($view_id);
            $view_code_id = $msk?->code_id ?? '';
        } else {
            $msk = $this->dto->typ_lst_cache->get_view($view);
            if ($msk == null) {
                log_err('view ' . $view . ' not found');
                $view_id = views::START_ID;
                $view_code_id = views::START_CODE;
            } else {
                $view_id = $msk->id();
                $view_code_id = $view;
            }
        }

        // central admin mask authorization: an admin only view is shown to no one but an admin (or
        // system) user, so a non-admin request is sent to the start view with a message instead of
        // rendering the admin page (which would otherwise leak the admin content to anyone)
        if ($this->admin_mask_denied($view_id, $msg_ui)) {
            $view_id = views::START_ID;
            $view_code_id = views::START_CODE;
        }

        // select the main object to display (object-type-aware also for a confirm view, see dbo_for_url)
        $dbo = $this->dbo_for_url($view_id, $url_array);

        // an unconfirmed create, update or delete request that the user has submitted (marked by the
        // named submit button, see url_var::POST_SUBMIT) is first shown in the matching confirm view,
        // so the user can check the change before it is written to the database; without the submit
        // marker the url just renders the requested form, e.g. the add view with the given values
        if ($action != null and $step <= 0 and array_key_exists(url_var::POST_SUBMIT, $url_array)) {
            $confirm_view_id = $this->confirm_view_id($view_id, url_var::STEP_CONFIRM);
            if ($confirm_view_id != 0) {
                $view_id = $confirm_view_id;
            }
        }

        // get the main object to display
        if ($id != 0) {
            // load the object from the database unless the url carries object field values (e.g. a
            // form submit, a confirm view url or a prefilled edit link), because a control var like
            // the debug flag must not switch the render from the loaded object to the incomplete url
            // values; only a single db object can be loaded by the id, a list (e.g. of phrases)
            // always takes the values from the url
            if (!$this->url_has_object_values($url_array) and $dbo instanceof db_object_ui
                and !$test_mode) {
                // pass the session user id so the backend loads the user-related object (the user's
                // sandbox overlay), not the default derived from the api caller
                $usr_id = $usr?->id() ?? 0;
                if (in_array($view_code_id, views::VIEWS_WITHOUT_RELATED, true)) {
                    $dbo->load_by_id($id, $msg_ui, [], $usr_id);
                } else {
                    $dbo->load_by_id_with_related($id, $msg_ui, $usr_id);
                }
            } else {
                // a url with object values can be partial (e.g. the my tab undo link carries only
                // the changed field), so load the object by id first and overlay the url values,
                // otherwise e.g. the confirm page could not show the object name; in test mode the
                // page must render without a backend call, so the render uses the url values only
                // in test mode the object is filled from the url values only, because a test render
                // must be reproducible without backend calls; a backend call for a dummy test id
                // (e.g. word 999 of the workflow tests) would return an empty api json and add a
                // mandatory-field-missing message to the rendered test page
                if (!$test_mode and $dbo instanceof db_object_ui) {
                    $usr_id = $usr?->id() ?? 0;
                    // the url may ask the backend for more than the stored object, e.g. the formula
                    // form asks to recalculate the latex based on the entered expression
                    $dbo->load_by_id($id, $msg_ui, $dbo->api_par_from_url($url_array), $usr_id);
                }
                $dbo->url_mapper($url_array, $msg_ui, $dto);
            }
        } else {
            // get last term used by the user or a default value
            if ($usr != null) {
                $wrd = $usr->last_term();
            }
        }

        // an admin protected object can still be changed by a normal user (the change creates the
        // user's own sandbox overlay), so the edit view opens without any protection message; only
        // the ownership takeover and the change of the protection level itself are admin only and
        // are enforced in the backend save path (see sandbox::check_protection and take_ownership
        // and "Admin protection does not block user changes" in docs/llm/architecture.md)

        // select the view
        // an edit or del mask is the view that the user has requested, so it is never overwritten here
        // and only a view that the user has selected for the object needs to be saved
        if (in_array($view_id, views::EDIT_DEL_MASKS_IDS)) {
            // TODO move as much a possible to backend functions
            if ($dbo->id() === 0 or $dbo->id() === '' or $dbo->id() === null) {
                $result .= log_err("id of " . library::class_to_name($dbo::class) . " is empty", "view.php", '',
                    (new Exception)->getTraceAsString());
            } elseif ($new_view_id != '' and $new_view_id != 0) {
                $dbo->save_view($new_view_id);
                $view_id = $new_view_id;
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
                $title = $msk_ui->title($dbo, $msg_ui);
                $dsp_text = $msk_ui->show($dbo, $msg_ui, $dto, $back, '', $test_mode, $url_array);

                // use a fallback if the view is empty
                if ($dsp_text == '' or $msk_ui->name() == '') {
                    $dsp_text = $msk_ui->name_tip();
                }
                if ($dsp_text == '') {
                    $result .= 'Please add a component to the view by clicking on Edit on the top right.';
                } else {
                    $html = new html_base();
                    $result .= $html->header($title, $msg_ui, '', $lan);
                    if (!in_array($view_id, views::NO_NAVBAR_IDS)) {
                        $logged_in = $usr !== null && !$usr->is_ip_only();
                        $result .= $html->navbar($view_id, $url_array,
                            $logged_in ? $usr->name() : null,
                            $logged_in ? $usr->navbar_role() : null);
                    }
                    $result .= $html->main($dsp_text);
                    $result .= $this->user_msg_html($msg_ui);
                    $result .= $html->footer();
                }
            }
        } else {
            $result .= log_err('No view for "' . $dbo->name() . '" found.',
                "view.php", '', (new Exception)->getTraceAsString());
        }

        return $result;
    }


    /*
     * cached page
     */

    /**
     * the fast path of the request routing that serves an already cached html page
     * before the heavy frontend setup (loading the user views, the type cache and the
     * frontend config) so that a user without own data changes gets a view-only page
     * with only the system config, the user and this cached page read from the database
     *
     * returns null if the page is not (yet) cached or must not be served from the cache,
     * so the caller does the full setup and renders the page live
     *
     * @param array $url_array the parsed url as an array
     * @param user_message_ui $msg_ui with the messages of this request that are added to the cached page and the requesting user with the uses_sandbox flag loaded
     * @return string|null the cached html page or null if the page cannot be served from the cache
     */
    function cached_page_or_null(array $url_array, user_message_ui $msg_ui): ?string
    {
        $result = null;
        // only a user without own data changes may get the standard cached page; an unknown
        // user (null) has no own data changes, so the shared page is the correct answer
        $uses_sandbox = $msg_ui->usr?->uses_sandbox ?? false;
        // a logged in (non-ip) user gets a personalised page (e.g. the dark blue person icon,
        // the logout link and the my tab), which the shared cached page does not contain,
        // so the page of a logged in user is always rendered live; the login state is read
        // from the session, because this fast path runs before the type cache is loaded
        // that a profile based check like is_ip_only() would need
        // TODO Prio 1 use the page cache also for logged in users as soon as the auto refresh
        //      job and the cache setup handle the user specific parts of the page
        $logged_in = !empty($_SESSION[url_var::SESSION_LOGGED]);
        if (!$uses_sandbox and !$logged_in) {
            $url_key = $this->url_cache_key($url_array);
            if ($url_key != '') {
                $cac_page = new db_cache_page();
                // TODO Prio 1 avoid the backend bridge
                $msg = new backend_user_message();
                $cached_html = $cac_page->html_by_url($url_key, $msg);
                $msg_ui->merge($msg);
                if ($cached_html !== null) {
                    // fill in the reading user's own anti-csrf token so the shared page does not
                    // carry the token of whoever first rendered and cached it (see request_token_valid)
                    $result = db_cache_page::restore_session_token($cached_html, self::session_token());
                    // a cached page never contains a message (see save_html_page), so add the
                    // message of this request e.g. that a change without login is not allowed
                    $result = db_cache_page::add_user_msg($result, $this->user_msg_html($msg_ui));
                }
            }
        }
        return $result;
    }

    /**
     * the anti-csrf token of the current session, read from the session here (the request/session
     * boundary, like html_base::form_session_token) so a cached html page can be personalised with
     * the reading user's token instead of the token of whoever first rendered and cached the page
     * @return string the current session token or '' if none is set yet
     */
    private static function session_token(): string
    {
        return $_SESSION[url_var::SESSION_TOKEN] ?? '';
    }

    /**
     * create the html code for the given url and use the cached html pages
     * of the view-only requests to reduce the response time
     *
     * - a request that changes data is always rendered live
     * - for a user without own data changes (uses_sandbox is false)
     *   the cached html page is served if available and created if it is missing
     * - for a user with own data changes (uses_sandbox is true)
     *   the cached html page is served immediately with a refresh flag
     *   and the rendering of the user specific page is requested as a backend job
     *
     * @param array $url_array the parsed url as an array
     * @param user_message_ui $msg_ui to enrich with potential errors; carries the requesting user with the uses_sandbox flag loaded
     * @param bool $is_action true if the request has changed data so the result must be rendered live
     * @param data_object $dto the frontend cache used to reduce the backend loading for the html code creation
     * @return string the html code to show the page to the user
     */
    function url_to_html_cached(
        array           $url_array,
        user_message_ui $msg_ui,
        bool            $is_action = false,
        data_object     $dto = new data_object()
    ): string
    {
        // an unknown user (null) has no own data changes, so the shared cached page is served
        $uses_sandbox = $msg_ui->usr?->uses_sandbox ?? false;
        // a logged in (non-ip) user gets a personalised page (e.g. the dark blue person icon,
        // the logout link and the my tab), so it is always rendered live and never stored as
        // the shared cached page; the login state is read from the session like in
        // cached_page_or_null, so both cache gates always decide the same way
        // TODO Prio 1 use the page cache also for logged in users as soon as the auto refresh
        //      job and the cache setup handle the user specific parts of the page
        $logged_in = !empty($_SESSION[url_var::SESSION_LOGGED]);
        $result = '';
        // an action request is always rendered live because the data has just been changed
        $url_key = '';
        if (!$is_action and !$logged_in) {
            $url_key = $this->url_cache_key($url_array);
        }
        // get the last cached html page for the url and fill in the reading user's own anti-csrf
        // token so the shared page does not carry the token of whoever cached it (see request_token_valid)
        $cac_page = new db_cache_page();
        $cached_html = null;
        if ($url_key != '') {
            $msg = new backend_user_message();
            $cached_html = $cac_page->html_by_url($url_key, $msg);
            $msg_ui->merge($msg);
            if ($cached_html !== null) {
                $cached_html = db_cache_page::restore_session_token($cached_html, self::session_token());
            }
        }
        // route the request based on the user sandbox usage and the cache state
        if ($url_key == '') {
            $result = $this->url_to_html($url_array, $msg_ui, $dto);
        } elseif (!$uses_sandbox) {
            if ($cached_html !== null) {
                // a cached page never contains a message (see save_html_page),
                // so add the message of this request if there is one
                $result = db_cache_page::add_user_msg($cached_html, $this->user_msg_html($msg_ui));
            } else {
                // remember the rendered page for the next request of any user without sandbox data
                $result = $this->url_to_html($url_array, $msg_ui, $dto);
                $this->save_html_page($cac_page, $url_key, $result);
            }
        } else {
            if ($cached_html !== null) {
                // serve the standard page immediately and request the user specific rendering
                $result = db_cache_page::add_user_msg($cached_html, $this->user_msg_html($msg_ui))
                    . api::PAGE_REFRESH_FLAG;
                // the refresh job is a backend write for the requesting user, so it needs the
                // backend user object (with the profile for the job type permission), which the
                // frontend message does not carry; until the job is requested via the api the
                // backend requesting user is taken from the db connection of this request
                // TODO Prio 1 request the job via the api instead of the direct backend call
                global $db_con;
                if ($db_con->usr_req != null) {
                    $this->request_page_refresh($cac_page, $db_con->usr_req);
                } else {
                    log_err('page refresh for ' . $url_key . ' skipped,'
                        . ' because the backend requesting user is missing');
                }
            } else {
                // no cached page yet, so render the user specific page live
                $result = $this->url_to_html($url_array, $msg_ui, $dto);
            }
        }
        return $result;
    }

    /**
     * the canonical cache key of a view-only page request
     * e.g. 'm=1&id=2' for the word view of the word zurich
     *
     * @param array $url_array the parsed url as an array
     * @return string the cache key or an empty string if the request must not be cached
     */
    function url_cache_key(array $url_array): string
    {
        global $cfg;

        $result = '';
        $mask_id = $url_array[url_var::MASK] ?? 0;
        $obj_id = $url_array[url_var::ID] ?? 0;
        $lan = $url_array[url_var::LANGUAGE] ?? '';
        // a request without a view and without an object shows the default start view, so cache it
        // under the start view key so the bare landing page (view.php with no mask) and an explicit
        // start request (view.php?m=1) share the same cached start page
        $mask_id = self::default_view_id($mask_id, $obj_id);
        // a request with more than the view, object and language is not cached; the anti-csrf token
        // is per session, the debug level only controls out-of-band debug output (log_debug echoes,
        // never part of the rendered html), and a process step of 0 (no action started) does not
        // change a view-only page, so all three are allowed without preventing the cache and are not
        // part of the cache key - so e.g. ?m=2&debug=6 takes the same cached path as ?m=2
        // the same applies to the cache switch itself, which is checked below instead
        $is_view_only = true;
        foreach ($url_array as $url_key => $url_val) {
            $is_key_param = in_array($url_key, [url_var::MASK, url_var::ID, url_var::LANGUAGE,
                url_var::SESSION_TOKEN, url_var::DEBUG, url_var::NO_CACHE]);
            $is_show_step = ($url_key == url_var::STEP and $url_val == url_var::STEP_BASE);
            if (!$is_key_param and !$is_show_step) {
                $is_view_only = false;
            }
        }
        // 'nc=1' (or 'nocache=1' in the human-readable url) switches the cache off for this request:
        // an empty cache key makes the caller render the page live and skip the cache write, so an
        // admin can compare the live page with the cached one without emptying the cache table
        if (($url_array[url_var::NO_CACHE] ?? '') == url_var::NO_CACHE_ON) {
            $is_view_only = false;
        }
        // the pod setting from config.yaml switches the html page cache off for all requests;
        // an empty key covers read and write, because a page is only cached with a non-empty key
        if (!($cfg?->page_cache_allowed() ?? true)) {
            $is_view_only = false;
        }
        // a request that shows a change or process step view is not cached
        if (in_array($mask_id, views::CHANGE_MASKS_IDS)) {
            $is_view_only = false;
        }
        // a process step view is not cached, unless it is a login or signup form: the plain form has
        // no started process step, its per-session token is stripped and restored by db_cache_page and
        // the submit is a POST action that is never cached (see views::PAGE_CACHE_ALLOWED_MASKS_IDS)
        if (in_array($mask_id, views::PROCESS_STEP_MASKS_IDS)
            and !in_array($mask_id, views::PAGE_CACHE_ALLOWED_MASKS_IDS)) {
            $is_view_only = false;
        }
        if (in_array($mask_id, views::GET_ACTION_IDS)) {
            $is_view_only = false;
        }
        if ($is_view_only) {
            $result = url_var::MASK . url_var::EQ . $mask_id . url_var::ADD_ID . $obj_id;
            if ($lan != '') {
                $result .= url_var::ADD . url_var::LANGUAGE . url_var::EQ . $lan;
            }
        }
        return $result;
    }

    /**
     * create the html notification for the user messages of the current request
     * used to render the message into a live page and to add it to a page loaded from the cache
     *
     * @param user_message_ui $msg_ui with the messages collected during the request
     * @return string the html code of the notification or an empty string if there is no message
     */
    private function user_msg_html(user_message_ui $msg_ui): string
    {
        $result = '';
        $html = new html_base();
        if ($msg_ui->has_info()) {
            $msg_txt = $msg_ui->get_last_message_translated();
            if ($msg_txt === '') {
                $msg_txt = $msg_ui->get_last_message();
            }
            if ($msg_txt === '') {
                $msg_txt = $msg_ui->get_last_info();
            }
            if ($msg_txt !== '') {
                if ($msg_ui->has_msg_id(msg_id::PASSWORD_WRONG)) {
                    $reset_link = $html->ref(
                        api::RESET_SCRIPT,
                        msg_id::PASSWORD_WRONG->value,
                        msg_id::PASSWORD_WRONG_TITLE->value
                    );
                    $notification_html = htmlspecialchars(msg_id::LOGIN_FAILED->value . '. ') . $reset_link;
                    $result = $html->dsp_notification_html($notification_html);
                } else {
                    $result = $html->dsp_notification($msg_txt);
                }
            }
        }
        return $result;
    }

    /**
     * remember the rendered html page for the next request of the same url
     * the cache row is written as the system user because filling the cache is
     * a system action that must also work for an ip user who cannot change data
     * (public so that the db write test can check exactly this permission case)
     * a failure is only logged because the user already has the rendered page
     *
     * @param db_cache_page $cac_page the cache page object used to check the cache
     * @param string $url_key the canonical cache key of the request
     * @param string $html the rendered html page that should be cached
     * @return void
     */
    function save_html_page(
        db_cache_page $cac_page,
        string        $url_key,
        string        $html
    ): void
    {
        // store the page with the session token replaced by a placeholder so the shared cache does
        // not carry this session's anti-csrf token to another session (see restore_session_token)
        $html = db_cache_page::strip_session_token($html, self::session_token());
        // store the page without the user message, because a message belongs to one request
        // and must never be repeated to another user (see add_user_msg)
        $html = db_cache_page::strip_user_msg($html);
        $save_msg = new backend_user_message(user_backend::system());
        $cac_page->save_html($url_key, $html, $save_msg);
        if (!$save_msg->is_ok()) {
            log_warning('caching the html page for ' . $url_key
                . ' failed because ' . $save_msg->get_message());
        }
    }

    /**
     * request the background rendering of the user specific html page
     * a failure is only logged because the user already has the standard page
     *
     * @param db_cache_page $cac_page the cached page that should be rendered again
     * @param user_backend $usr the session user for whom the page should be rendered
     * @return void
     */
    private function request_page_refresh(
        db_cache_page $cac_page,
        user_backend  $usr
    ): void
    {
        $job = new job_backend($usr);
        $job->set_type(job_types::PAGE_REFRESH, $usr);
        $job->row_id = $cac_page->id();
        $job_msg = new backend_user_message($usr);
        $job->save($job_msg);
        if (!$job_msg->is_ok()) {
            log_warning('page refresh job for ' . $cac_page->dsp_id()
                . ' failed because ' . $job_msg->get_message());
        }
    }

    /**
     * react to a user action such as pressing the save button on an edit form:
     * the action const sets the user process step, then the request is run through
     * url_to_action (which for a still unconfirmed change returns the confirm change view url)
     * and the resulting url is rendered via url_to_html.
     * This is the two step dispatch of http/view.php wrapped in one call for the workflow tests.
     *
     * @param array $url_arr the parsed url of the user action e.g. the submitted edit form
     * @param user_request $req the bundled request context (users, message, cache and the do_it flag)
     * @return string the html code of the next page shown to the user
     */
    function execute_and_next(
        array        $url_arr,
        user_request $req
    ): string
    {
        global $sys;

        // measure the action and the rendering separately, so a slow request shows which of the two
        // is slow; an interleaved db read or write still counts as db_read / db_write because its
        // own switch() restores this section
        $sys->times->switch(system_time_type::URL_TO_ACTION);
        $next_url = $this->url_to_action($url_arr, $req->usr_backend, $req->msg, $req->dto, $req->do_it);
        $sys->times->switch(system_time_type::URL_TO_HTML);
        $result = $this->url_to_html($next_url, $req->msg, $req->dto, $req->test_mode);
        // return to the default section for whatever the caller does next
        $sys->times->switch(system_time_type::DEFAULT);
        return $result;
    }

    function show_view(int $id, user_message_ui $msg_ui): string
    {
        return $this->dto->typ_lst_cache->get_html_by_id($id, $msg_ui);
    }


    /*
     * execute
     */

    /**
     * validate credentials, start the session, and return the URL to redirect to after login
     * TODO Prio 2 review and try to avoid the backend frontend mix for user returns
     *
     * @param array $url_array the normalised URL params including username and password
     * @param user_message_ui $msg_ui collects errors if login fails
     * @param user_backend $usr_backend updated in-place with the logged-in user on success
     * @param user_ui $usr_ui updated in-place from the backend user's api_json on success
     * @param bool $do_it false for unit tests that should not touch the session
     * @return array URL array pointing to the back page (or the start view if no back target) on success, or the original login URL (minus credentials) on failure
     */
    private function action_login(
        array           $url_array,
        user_message_ui $msg_ui,
        user_backend    &$usr_backend,
        user_ui         &$usr_ui,
        bool            $do_it
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
                $usr_ui->set_from_json($db_usr->api_json([], $login_msg), $msg_ui);
            } else {
                $msg_login_ui = new user_message_ui();
                $msg_login_ui->api_mapper($login_msg->api_array($login_msg), $msg_ui);
                $msg_ui->merge($msg_login_ui);
            }
        }

        if ($logged_in) {
            // reject at once if a user whitelist is active and this user is not on it
            server_guard::enforce_user((string)$usr_backend->id(), $usr_name);
            // without a back target show the start view after the login, not the login view again
            $back_array = html_base::url_par_from_back_part($url_array);
            $next_url = empty($back_array) ? [url_var::MASK => views::START_ID] : $back_array;
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
     * @param user_message_ui $msg_ui collects validation errors or save failures
     * @param user_backend $usr_backend updated in-place with the new user on success
     * @param user_ui $usr_ui updated in-place from the new user's api_json on success
     * @param bool $do_it false for unit tests that should not touch the database or session
     * @return array URL array pointing to the back page on success, or the signup page (minus passwords) on failure
     */
    private function action_signup(
        array           $url_array,
        user_message_ui $msg_ui,
        user_backend    &$usr_backend,
        user_ui         &$usr_ui,
        bool            $do_it
    ): array
    {
        // no htmlspecialchars() — SQL injection is handled by prepared queries; output escaping happens in form_input()
        $usr_name = $url_array[url_var::USERNAME] ?? $url_array[url_var::USERNAME_HUMAN] ?? '';
        $email = $url_array[url_var::EMAIL] ?? $url_array[url_var::EMAIL_HUMAN] ?? '';
        $pw = $url_array[url_var::USER_PASSWORD] ?? $url_array[url_var::USER_PASSWORD_HUMAN] ?? '';
        $pw_re = $url_array[url_var::USER_PASSWORD_RETYPE] ?? $url_array[url_var::USER_PASSWORD_RETYPE_HUMAN] ?? '';
        $signed_up = false;

        if ($do_it) {
            // reject a user name with a path or control character so it can never be used to build
            // a file path (e.g. the config file cache keys by user id now, but a raw name must also
            // never travel into a path) or break out of an output context; the check stays a lenient
            // deny-list so the reserved names (which contain spaces and dots) remain valid
            if (str_contains($usr_name, '/')
                or str_contains($usr_name, '\\')
                or preg_match('/[\x00-\x1f]/', $usr_name) === 1) {
                $msg_ui->add(msg_id::SIGNUP_ERR_NAME_INVALID, []);
            }
            // block signup up front if a user whitelist is active and this name is not on it;
            // no account is created and the user is told how to get access (see is_ok() gate below)
            if (server_guard::user_rejected('', $usr_name)) {
                $msg_ui->add(msg_id::SIGNUP_ERR_WHITELIST, []);
            }
            $existing = new user_backend();
            $signup_msg = new backend_user_message();
            $existing->load_by_name($usr_name, $signup_msg);
            if ($existing->has_db_id()) {
                // the distinct message reveals that the name is taken (user enumeration), unlike
                // the neutral reset flow (see action_login_reset); a conscious trade-off because
                // without it the user cannot pick a free name, so signup would be impossible;
                // the message points a returning user to the password reset instead, and the
                // planned per-ip request rate limit will bound the probing speed (see pending.md)
                $msg_ui->add(msg_id::SIGNUP_ERR_NAME_EXISTS, []);
            }
            if (empty($email)) {
                $msg_ui->add(msg_id::SIGNUP_ERR_EMAIL_EMPTY, []);
            }
            if (empty($pw)) {
                $msg_ui->add(msg_id::SIGNUP_ERR_PW_EMPTY, []);
            }
            if (empty($pw_re)) {
                $msg_ui->add(msg_id::SIGNUP_ERR_PW_RETYPE_EMPTY, []);
            }
            if (!empty($pw) && !empty($pw_re) && $pw !== $pw_re) {
                $msg_ui->add(msg_id::SIGNUP_ERR_PW_MISMATCH, []);
            }

            if ($msg_ui->is_ok()) {
                $new_usr = new user_backend();
                $new_usr->name = $usr_name;
                $new_usr->email = $email;
                $new_usr->set_password($pw, $signup_msg);
                if ($signup_msg->is_ok()) {
                    $new_usr->save($signup_msg);
                    $usr_by_name = new user_backend();
                    $usr_by_name->load_by_name($usr_name, new backend_user_message());
                    $usr_id = $usr_by_name->id();
                    if ($usr_id > 0) {
                        session_start();
                        // regenerate the session id on this authentication transition so a planted
                        // session id cannot become authenticated (session fixation), matching login
                        session_regenerate_id(true);
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
                        $usr_ui->set_from_json($usr_by_name->api_json([], $signup_msg), $msg_ui);
                        $signed_up = true;
                    } else {
                        log_err('Cannot find id for ' . $usr_name . ' after signup.', 'action_signup');
                        $signup_msg->add(msg_id::SIGNUP_ERR_FAILED, []);
                    }
                }
                $msg_signup_ui = new user_message_ui();
                $msg_signup_ui->api_mapper($signup_msg->api_array($signup_msg), $msg_ui);
                $msg_ui->merge($msg_signup_ui);
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
     * @param user_message_ui $msg_ui collects validation and save errors shown to the user
     * @param user_backend $usr_backend updated in-place with the activated user on success
     * @param user_ui $usr_ui updated in-place from the activated user's api_json on success
     * @param bool $do_it false for unit tests that should not touch the database or session
     * @return array URL array pointing to the back page on success, or the activate page (minus passwords) on failure
     */
    private function action_login_activate(
        array           $url_array,
        user_message_ui $msg_ui,
        user_backend    &$usr_backend,
        user_ui         &$usr_ui,
        bool            $do_it
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
                $msg_ui->add_message($mtr->txt(msg_id::ACTIVATE_ERR_MISSING_ID));
            } else {
                $usr = new user_backend();
                $activate_msg = new backend_user_message();
                $usr->load_by_id($usr_id, $activate_msg);

                // compare the stored key hash with the hash of the posted key in constant time
                if ($usr->activation_key_valid($post_key)) {
                    if (empty($pw)) {
                        $msg_ui->add_message($mtr->txt(msg_id::SIGNUP_ERR_PW_EMPTY));
                    }
                    if (empty($pw_re)) {
                        $msg_ui->add_message($mtr->txt(msg_id::SIGNUP_ERR_PW_RETYPE_EMPTY));
                    }
                    if (!empty($pw) && !empty($pw_re) && $pw !== $pw_re) {
                        $msg_ui->add_message($mtr->txt(msg_id::SIGNUP_ERR_PW_MISMATCH));
                    }

                    if ($msg_ui->is_ok()) {
                        $usr->set_password($pw, $activate_msg);
                        if ($activate_msg->is_ok()) {
                            $usr->activation_key = '';
                            $usr->activation_timeout = new DateTime();
                            $usr->save($activate_msg);
                            $usr_by_id = new user_backend();
                            $usr_by_id->load_by_id($usr_id, new backend_user_message());
                            if ($usr_by_id->has_db_id()) {
                                session_start();
                                // regenerate the session id on this authentication transition so a
                                // planted session id cannot become authenticated (session fixation)
                                session_regenerate_id(true);
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
                                // reject at once if a user whitelist is active and this user is not on it
                                server_guard::enforce_user((string)$usr_id, $usr_by_id->name());
                                $usr_backend = $usr_by_id;
                                $usr_ui->set_from_json($usr_by_id->api_json([], $activate_msg), $msg_ui);
                                $activated = true;
                            } else {
                                log_err('Cannot find id ' . $usr_id . ' after password change.', 'action_login_activate');
                                $activate_msg->add_message_text($mtr->txt(msg_id::ACTIVATE_ERR_FAILED));
                            }
                        }
                        $msg_activate_ui = new user_message_ui();
                        $msg_activate_ui->api_mapper($activate_msg->api_array($activate_msg), $msg_ui);
                        $msg_ui->merge($msg_activate_ui);
                    }
                } else {
                    // a still valid key that did not match is a wrong key; otherwise it is absent
                    // or timed out, so the user is asked to request a new reset link
                    if ($usr->has_active_activation_key()) {
                        $msg_ui->add_message($mtr->txt(msg_id::ACTIVATE_ERR_KEY_MISMATCH));
                    } else {
                        $msg_ui->add_message($mtr->txt(msg_id::ACTIVATE_ERR_KEY_EXPIRED));
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
     * @param user_message_ui $msg_ui collects errors from saving the logoff time
     * @param bool $do_it false for unit tests that should not touch the database or session
     * @return array URL array pointing to the logout confirmation view
     */
    private function action_logout(
        user_backend    &$usr_backend,
        user_ui         &$usr_ui,
        user_message_ui $msg_ui,
        bool            $do_it,
        array           $url_array = []
    ): array
    {
        if ($do_it) {
            if ($usr_backend->has_db_id()) {
                $logoff_msg = new backend_user_message($usr_backend);
                $usr_backend->last_logoff = new DateTime();
                $usr_backend->save($logoff_msg);
                $msg_logoff_ui = new user_message_ui();
                $msg_logoff_ui->api_mapper($logoff_msg->api_array($logoff_msg), $msg_ui);
                $msg_ui->merge($msg_logoff_ui);
            }
            if (isset($_SESSION)) {
                $_SESSION = [];
                session_destroy();
            }
        }
        $usr_backend = new user_backend();
        $usr_ui = new user_ui();
        // keep the '9'-prefixed back target of the logout request in the logout page url, so the
        // logout page (and a login from there) can send the user back to the original page
        $url = [url_var::MASK => views::LOGOUT_ID];
        foreach ($url_array as $key => $val) {
            if (str_starts_with($key, url_var::BACK)) {
                $url[$key] = $val;
            }
        }
        return $url;
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
     * @param user_message_ui $msg_ui collects errors shown to the user
     * @param bool $do_it false for unit tests that should not touch the database or send email
     * @return array URL array for the next page
     */
    private function action_login_reset(
        array           $url_array,
        user_message_ui $msg_ui,
        bool            $do_it
    ): array
    {
        global $mtr;

        $usr_name = $url_array[url_var::USERNAME_HUMAN] ?? '';
        $usr_mail = $url_array[url_var::EMAIL_HUMAN] ?? '';
        $db_usr = new user_backend();
        $key = '';

        if ($do_it) {
            // only a matching account gets a reset mail, but the user is told the same either way
            // (see the neutral message below), so the reset never reveals whether the account exists
            if ($db_usr->load_by_name_or_email($usr_name, $usr_mail, new backend_user_message())) {
                $key_ok = true;
                try {
                    $key = bin2hex(random_bytes(10));
                } catch (RandomException $e) {
                    log_err('RandomException in action_login_reset: ' . $e->getMessage());
                    $key_ok = false;
                }
                if ($key_ok) {
                    // store only the sha256 hash of the key with a short validity; the cleartext
                    // $key is never persisted and is sent to the user by email below
                    $db_usr->set_activation_key($key);
                    $reset_msg = new backend_user_message();
                    $db_usr->save($reset_msg);
                    // a save failure is logged, not shown, so the response stays identical for an
                    // existing and a non-existing account (do not merge it into the user message)
                    if ($reset_msg->is_ok()) {
                        $activate_url = POD_NAME . api::LOGIN_ACTIVATE_FORWARD
                            . url_var::PAR . url_var::ID . url_var::EQ . $db_usr->id
                            . '&' . url_var::POST_KEY . url_var::EQ . $key;
                        $mail_subject = POD_NAME . ' - ' . $this->mail_txt(msg_id::RESET_MAIL_SUBJECT);
                        $mail_body = $this->mail_txt(msg_id::RESET_MAIL_HELLO) . "\n\n"
                            . $this->mail_txt(msg_id::RESET_MAIL_KEY_INTRO) . ' ' . $key . "\n\n"
                            . $this->mail_txt(msg_id::RESET_MAIL_LINK_INTRO) . "\n" . $activate_url . "\n\n"
                            . $this->mail_txt(msg_id::RESET_MAIL_IGNORE);
                        mail($db_usr->email, $mail_subject, $mail_body, users::mail_header());
                    } else {
                        log_err('password reset save failed: ' . $reset_msg->all_message_text());
                    }
                }
            }
            // the same neutral confirmation for a found and a not-found account (user enumeration)
            $msg_ui->add_message($mtr->txt(msg_id::RESET_MAIL_SENT));
        }

        // the same next page in both cases; a real account received the reset link (with its id and
        // key) by email, so the redirect carries no user id, which would leak that the account exists
        return [url_var::MASK => views::LOGIN_ID];
    }

    /**
     * apply a sys_log status update on behalf of an admin; mirrors the action portion of
     * the legacy /http/error_update.php script: when an admin clicks "close" on a sys_log row
     * the id + status arrive as URL parameters and the matching entry is saved with the new
     * status; non-admins and incomplete parameters are ignored — the page is just re-rendered
     *
     * @param array $url_array the normalised URL params; expects ID (log id) and
     *                         rest_ctrl::PAR_LOG_STATUS (new status id)
     * @param user_message_ui $msg_ui collects backend errors so they surface in the notification bar; carries the requesting user and only admins may perform this action
     * @param bool $do_it set to false in unit tests so the DB is not touched
     * @return array the URL array for the next page — stays on the error_update view with the
     *               action parameters stripped so a page reload does not re-submit the change
     */
    private function action_error_update(
        array           $url_array,
        user_message_ui $msg_ui,
        bool            $do_it
    ): array
    {
        $usr = $msg_ui->usr;
        if ($do_it and $usr != null and $usr->is_admin()) {
            $log_id = (int)($url_array[url_var::ID] ?? 0);
            $status_id = (int)($url_array[rest_ctrl::PAR_LOG_STATUS] ?? 0);
            if ($log_id > 0 and $status_id > 0) {
                $err_entry = new sys_log_backend();
                $err_entry->set_user_id($usr->id());
                $err_entry->id = $log_id;
                $err_entry->status_id = $status_id;
                $save_msg = new backend_user_message();
                $err_entry->save($save_msg);
                $msg_ui->api_mapper($save_msg->api_array($save_msg), $msg_ui);
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
     * @param user_message_ui $usr_msg collects errors and carries the requesting user executing the action
     * @param data_object $dto the frontend cache
     * @param string $crud one of url_var::CRUD_CREATE / CRUD_UPDATE / CRUD_DELETE
     * @param bool $do_it false for unit tests that should not touch the database
     * @return array URL array for the next page
     */
    /**
     * the confirm view that matches a crud mask for a change that still needs user confirmation
     *
     * @param int|string $view the requested edit / add / delete mask
     * @param string $step the user process step; only STEP_CONFIRM needs a confirm view
     * @return int the confirm view id or 0 if the change does not need a confirm step
     */
    private function confirm_view_id(int|string $view, string $step): int
    {
        $confirm_view = 0;
        if ($step == url_var::STEP_CONFIRM) {
            if (in_array($view, views::ADD_MASKS_IDS)) {
                $confirm_view = views::CONFIRM_ADD_ID;
            } elseif (in_array($view, views::EDIT_MASKS_IDS)) {
                $confirm_view = views::CONFIRM_EDIT_ID;
            } elseif (in_array($view, views::DEL_MASKS_IDS)) {
                $confirm_view = views::CONFIRM_DEL_ID;
            }
        }
        return $confirm_view;
    }

    /**
     * true if the url carries object field values (e.g. of a form submit, a confirm view url or a
     * prefilled edit link) and not only the control vars that select the view, the object and the
     * render mode; the '9'-prefixed back vars are navigation targets and no object values either
     *
     * @param array $url_array the parsed url
     * @return bool true if at least one url key is an object field value
     */
    private function url_has_object_values(array $url_array): bool
    {
        $result = false;
        foreach ($url_array as $key => $val) {
            if (!in_array($key, url_var::CONTROL_VARS)
                and $key != rest_ctrl::PAR_VIEW_NEW_ID
                and !str_starts_with($key, url_var::BACK)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * log a confirm or confirmed step that no action handles, because these steps are a write request
     * and ignoring one silently would hide the missing database change from the user: the returned url
     * just re-renders the requested view, which looks exactly like the redirect after a successful
     * write (see docs/llm/structure.md); the plain navigation steps (show, edit, back, cancel) are
     * expected to fall through without an action, so they are not logged
     *
     * @param int|string $view the requested view that no action arm has matched
     * @param string $step the user process step of the request
     * @param user_message_ui $msg to inform the user that the request has been ignored
     */
    private function log_ignored_write_step(int|string $view, string $step, user_message_ui $msg): void
    {
        if ($step == url_var::STEP_CONFIRM or $step == url_var::STEP_CONFIRMED) {
            log_err_msg_ui('the ' . $step . ' step for view ' . $view . ' has been ignored,'
                . ' because the view is not an add, edit or del mask, so nothing has been saved',
                $msg);
        }
    }

    private function action_crud(
        array           $url_array,
        int             $view,
        user_message_ui $msg_ui,
        data_object     $dto,
        string          $crud,
        bool            $do_it
    ): array
    {
        // a confirmed create/update/delete writes the object, so the back mask that carries its type is
        // required here (unlike a standalone confirm view render)
        $dbo = $this->dbo_for_url($view, $url_array, true);
        $dbo->url_mapper($url_array, $msg_ui, $dto);

        // a delete request by name (e.g. right after the confirmed add of the object, when the url
        // does not yet carry the assigned id) resolves the database id first
        if ($crud == url_var::CRUD_DELETE and $dbo instanceof sandbox_named_ui
            and $dbo->id() == 0 and $dbo->name() != '') {
            $dbo->load_by_name($dbo->name(), $msg_ui);
        }

        if ($do_it) {
            $result_msg = match ($crud) {
                url_var::CRUD_CREATE => $dbo->add_via_api($msg_ui),
                url_var::CRUD_UPDATE => $dbo->update($msg_ui),
                url_var::CRUD_DELETE => $dbo->del($msg_ui),
                default => new user_message_ui()
            };
            if (!$result_msg->is_ok()) {
                $msg_ui->merge($result_msg);
                // stay on the current view so the user can fix errors
                return $url_array;
            }
        }

        // on success go back to the calling page: the confirm view set the object's own default view +
        // id as the '9'-prefixed back target, so the user returns to the changed object; the id of
        // the just saved object is preferred over the back id, because the id can change with the
        // write, e.g. a rename by a user that cannot change the standard row creates a new database
        // row and the old id of the back target would show an empty view
        $back_url = $this->url_to_back_url($url_array);
        if ($crud != url_var::CRUD_DELETE
            and $dbo instanceof db_object_ui
            and $dbo->id() != 0
            and array_key_exists(url_var::ID, $back_url)) {
            $back_url[url_var::ID] = $dbo->id();
        }
        return $back_url;
    }

    /**
     * the previous page of a url built from its '9'-prefixed back targets, ready to be used directly as
     * the next page (e.g. the object's own view after a confirmed change); falls back to the start view
     * if the url carries no back target
     *
     * @param array $url_array the url with the '9'-prefixed back targets (e.g. 9m for the view, 9id for the id)
     * @return array the previous page as a standard url array e.g. [m => 90, id => 159]
     */
    function url_to_back_url(array $url_array): array
    {
        $back_url = html_base::url_par_from_back_part($url_array);
        if (empty($back_url)) {
            $back_url = [url_var::MASK => views::START_ID];
        }
        return $back_url;
    }

    /**
     * // TODO Prio 1 review
     * the main frontend object to display or change for a view: normally the object of the requested
     * view, but for a confirm view (whose mask does not encode the object type) the object of the
     * '9'-prefixed back target view (the object's own default view), so the confirm view and its write
     * are object-type-aware (a word change writes a word, a triple change a triple)
     *
     * @param int $view_id the requested view id
     * @param array $url_array the url that may carry the '9'-prefixed back target
     * @return sandbox_ui|sandbox_named_ui|db_object_ui|combine_named_ui|type_object|sandbox_list_ui the matching frontend object
     */
    private function dbo_for_url(int $view_id, array $url_array, bool $for_action = false): sandbox_ui|sandbox_named_ui|db_object_ui|combine_named_ui|type_object|sandbox_list_ui
    {
        $dbo = $this->view_id_to_dbo_ui($view_id);
        // a confirm view does not encode its own object type, so it takes the type from the '9'-prefixed
        // back mask (the object's own default view). without it view_id_to_dbo_ui has fallen back to a
        // word, which would let a confirmed change or delete target the wrong object, so log the
        // inconsistency instead of defaulting silently (see docs/llm/structure.md). only a confirm view
        // that triggers a write ($for_action) needs the back mask; rendering one standalone (e.g. the
        // view catalog test) legitimately has none, so it is not logged
        if (in_array($view_id, views::CONFIRM_MASKS_IDS)) {
            if (array_key_exists(url_var::BACK . url_var::MASK, $url_array)) {
                $dbo = $this->view_id_to_dbo_ui((int)$url_array[url_var::BACK . url_var::MASK]);
            } elseif ($for_action) {
                log_err('confirm view ' . $view_id . ' reached without a back mask, '
                    . 'so its object type is unknown and defaults to a word');
            }
        }
        // TODO Prio 2 review
        // stamp the prime object id from the url onto the dbo so it already knows which row it
        // represents (lists and type objects have no single id, so only db objects get it; the
        // value is left uncast so a string group key survives).
        // an add view creates a new object, so it must keep id 0: stamping the url id there would
        // make every sub-object selector (phrase, ref, from/to, ...) read the object id as a
        // pre-selected entry and drop the "please select ..." default option
        if ($dbo instanceof db_object_ui
            and array_key_exists(url_var::ID, $url_array)
            and !in_array($view_id, views::ADD_MASKS_IDS)) {
            $dbo->set_id($url_array[url_var::ID]);
        }
        // a phrase view (e.g. the calculator) shows a word or a triple, and the frontend objects
        // of a view are typed (see system_form::title_phrase), so the phrase id of the url decides
        // the object: a negative phrase id is a triple, a positive one a word
        if ($dbo instanceof phrase_ui and array_key_exists(url_var::ID, $url_array)) {
            $dbo = $this->phrase_id_to_dbo_ui((int)$url_array[url_var::ID]);
        }
        return $dbo;
    }

    /**
     * the typed page object of a phrase id: the triple of a negative phrase id or else the word
     *
     * @param int $phr_id the phrase id of the url, negative for a triple
     * @return word_ui|triple_ui the object with the id of the word or triple
     */
    private function phrase_id_to_dbo_ui(int $phr_id): word_ui|triple_ui
    {
        if ($phr_id < 0) {
            $dbo = new triple_ui();
        } else {
            $dbo = new word_ui();
        }
        $dbo->set_id(abs($phr_id));
        return $dbo;
    }

    private function exe_process_step(
        sandbox_ui|sandbox_named_ui|db_object_ui $sbx,
        array                                    $url_array,
        user_message_ui                          $msg
    ): bool
    {

        return $msg->is_ok();
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
            $dbo_ui = $this->dbo_ui_by_view_type($view_id);
        }
        return $dbo_ui;
    }

    /**
     * the page object of a view that no system view id list names, e.g. a view defined by a
     * data file such as the use case view of "PV in Switzerland": the type of the view says
     * which object the page shows, so a view typed "triple" gets a triple and a view typed
     * "word" a word; any other type falls back to a word and is reported, because the page
     * would show the wrong object
     *
     * @param int $view_id the id of the view to show
     * @return word_ui|triple_ui the object the view shows
     */
    private function dbo_ui_by_view_type(int $view_id): word_ui|triple_ui
    {
        $msk = $this->dto?->typ_lst_cache?->get_view_by_id($view_id);
        $typ_id = $msk?->type_id(new user_message_ui());
        if ($typ_id == $this->dto?->typ_lst_cache?->msk_typ?->id(view_types::TRIPLE)) {
            $dbo_ui = new triple_ui();
        } elseif ($typ_id == $this->dto?->typ_lst_cache?->msk_typ?->id(view_types::WORD)) {
            $dbo_ui = new word_ui();
        } else {
            log_err('ui object missing for view id ' . $view_id);
            $dbo_ui = new word_ui();
        }
        return $dbo_ui;
    }

}
