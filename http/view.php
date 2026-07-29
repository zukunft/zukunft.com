<?php

/*

    /http/view.php - create the HTML code to show a zukunft.com view to the user
    --------------

    - the view contains the overall formatting like page size
    - the view component links to words, values or formulas
    - a view component can be linked to a view or a view component define by the view_link_type

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

$start_time = microtime(true);

include_once 'const.php';

// load the main frontend class
include_once WEB . 'frontend.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

// reset the html code var
$web_txt = '';

// init global objects for the database connection until the api is completed
global $debug;
global $sys;

// init global frontend objects
global $ui_sys; // the frontend cache of this request incl. the preloaded types and the user configuration
global $mtr;
$mtr = new Translator();
$msg = new user_message();

// prepare for static pages
// merge POST into GET so form submissions (e.g. login) reach url_to_action
// TODO llm: add other actions or maybe use $_REQUEST ?
// TODO llm: norm the url_array based on static function and const e.g. convert mask_id=login to m=61 but do not convert mask_id that does not have a const
// TODO llm: if the lan is given use it for $mtr
$url_array = empty($_POST) ? $_GET : array_merge($_GET, $_POST);
if (!empty($_POST)) {
    // redact the unhashed password before logging so a login post never leaks it into the log
    log_debug('view $_POST array: ' . library::dsp_array(url_var::without_secrets($_POST), true));
}

// TODO llm: if the request is a static page (views::STATIC_VIEWS), just show it e.g. from the html file stored in the root folder /login or /start and skip the database opening and closing
// TODO llm: create a process to refresh the static pages for via /http/update_static.php script that cal also be called by an admin user or a scheduled batch job (make sure that no other files are overwritten and that this cannot be user for code injections)


// open database
$app = new frontend();
$db_con = $app->start("view", $msg, $url_array);


if ($db_con->is_open()) {

    // load the session user parameters
    // TODO Prio 2 create a session object and include the user in the prg_start return object
    $usr = new user;
    $web_txt .= $usr->get();
    // TODO Prio 1 make this the only place where the requesting user is stored (the user is set on the
    //   message once the frontend user is loaded below)


    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    // at minimum the IP address is used as the user id, so id() > 0 is always true for real requests
    if ($usr->id() > 0) {

        // store the requesting user on the message before anything is decided or rendered:
        // this http entry point is the only place where the requesting user of a request is
        // set, so every function below takes $msg as a parameter and reads the requesting
        // user from $msg->usr instead of receiving it separately or reading a global
        // (see docs/llm/state-and-messages.md); set here and not further down, so that the
        // blocked-request branch and the cached page also know who is asking
        $usr_ui = new user_ui();
        $usr_ui->set_from_json($usr->api_json(), $msg);
        $msg->usr = $usr_ui;

        $ui = new frontend('view');

        // if the session token is not valid any more (e.g. the session has expired) recover
        // gracefully instead of running the action: a non-ip user that has been logged in is sent to
        // the login page with the requested page as the back target, so after re-login the user
        // returns to where they were; an anonymous / ip user just gets the requested page again (a new
        // token was created at session start) - the action is never executed for an invalid token
        // (see the request_triggers_action gate below), so a csrf attempt still changes nothing
        if (!$app->session_token_valid) {
            $is_logged_in = !empty($_SESSION[url_var::SESSION_LOGGED]);
            $login_url = frontend::session_recovery_url(false, $is_logged_in, $url_array);
            if ($login_url !== null) {
                $url_array = $login_url;
            }
        }

        // block a data changing request of a user without login before any change is done
        // if this pod does not permit the changes of an ip user
        // (config.yaml: system configuration > pod > permissions > database change > ip user > allowed)
        // beside add, edit and del this covers e.g. the import, paste, undo and job views;
        // the blocked request is answered with the calling page taken from the '9'-prefixed
        // back params of the blocked url, so the user stays on the page where the change
        // link has been clicked and only the message is added; the back mask is user input,
        // so it is only used if it is not itself a blocked mask; a request without a back
        // e.g. a typed url falls back to the default view of the target object (word edit
        // mask -> word default view) and masks without an object view fall back to the
        // start page; checked before the page cache probe, so that the blocked request is
        // answered with the cached page and the message is added to it (see cached_page_or_null)
        $mask_id = $url_array[url_var::MASK] ?? 0;
        if (in_array($mask_id, views::IP_BLOCKED_MASKS_IDS) and $usr->is_blocked()) {
            log_warning('change view ' . $mask_id . ' requested by the blocked user ' . $usr->dsp_id());
            $msg->add(msg_id::CHANGE_BLOCKED_FOR_IP_USER, []);
            $back_url = html_base::url_par_from_back_part($url_array);
            $back_msk_id = (int)($back_url[url_var::MASK] ?? 0);
            if ($back_msk_id > 0 and !in_array($back_msk_id, views::IP_BLOCKED_MASKS_IDS)) {
                $url_array = $back_url;
            } else {
                $show_url = [url_var::MASK => (new views())->change_to_show_id($mask_id)];
                if (isset($url_array[url_var::ID])) {
                    $show_url[url_var::ID] = $url_array[url_var::ID];
                }
                $url_array = $show_url;
            }
        }

        // fast path: serve an already cached view-only page before the heavy frontend setup
        // so that a user without own data changes gets the page with a few database reads only
        // (the cached types json, the system config, the user incl. the uses_sandbox flag
        // and this cached page); the message of this request is added to the cached page
        $cached_page = $ui->cached_page_or_null($url_array, $msg);
        if ($cached_page !== null) {
            $web_txt .= $cached_page;
        } else {

            // TODO Prio o move loading of user data to frontend e.g. to skip it for the login page
            $usr->load_usr_data();

            $ui->load_cache();

            // publish the loaded ui cache to the allowed global so renderers
            // (e.g. phrase_list::category_subtitle) can read the verb type cache
            $ui_sys = $ui->dto;

            // load the user-specific frontend configuration onto the ui cache
            // TODO Prio 1 load the config from cache if nothing has been changed
            $ui_sys->cfg = new config();
            $ui_sys->cfg->load($sys);

            // execute the user request and POST-Redirect-GET to prevent re-submission on reload
            // the same predicate gates the anti-csrf token check in frontend::request_token_valid, so an
            // action is never dispatched here without a token having been required at session start
            $sys->times->switch(system_time_type::URL_TO_ACTION);
            $is_post_action = isset($url_array[url_var::POST_SUBMIT]);
            $is_get_action = in_array($url_array[url_var::MASK] ?? 0, views::GET_ACTION_IDS);
            // never run the action of a request whose session token is not valid any more (fail closed
            // against csrf); the user still gets the page or the login form (see session_recovery_url)
            $is_action = ($is_post_action or $is_get_action) && $app->session_token_valid;
            if ($is_action) {
                if (frontend::request_triggers_action($url_array)) {
                    $url_array = $ui->url_to_action($url_array, $usr, $msg, $ui->dto);
                }
            }

            // show the result to the user
            // and use the cached html pages for view-only requests to reduce the response time
            $sys->times->switch(system_time_type::URL_TO_HTML);
            $web_txt .= $ui->url_to_html_cached($url_array, $msg, $is_action, $ui->dto);
            $sys->times->switch(system_time_type::CLOSE);
        }
    }

    // close the database and measure the script loading time before the frontend has been created
    $app->end($db_con, $start_time);
} else {
    $web_txt .= 'database connection lost';
}

if ($debug == url_var::DEBUG_EXE_TIME_REPORT) {
    // TODO Prio 2 remove temp overwrite for debug
    $end_time = microtime(true);
    $duration = $end_time - $start_time;
    $web_txt .= '<br>Execution times for debugging: ' . $sys->times->report($duration);
}

// show the page
echo $web_txt;

