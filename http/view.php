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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

// load the main frontend class
include_once paths::WEB . 'frontend.php';

use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
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
global $ui_sys;
global $mtr;
$mtr = new Translator();
$msg = new user_message();

// prepare for static pages
// merge POST into GET so form submissions (e.g. login) reach url_to_action
// TODO llm: add other actions or maybe use $_REQUEST ?
// TODO llm: norm the url_array based on static function and const e.g. convert mask_id=login to m=61 but do not convert mask_id that does not have a const
// TODO llm: if the lan is given use it for $mtr
$url_array = empty($_POST) ? $_GET : array_merge($_GET, $_POST);
log_debug('view $_POST array: ' . library::dsp_array($_POST, true));

// TODO llm: if the request is a static page (views::STATIC_VIEWS), just show it e.g. from the html file stored in the root folder /login or /start and skip the database opening and closing
// TODO llm: create a process to refresh the static pages for via /http/update_static.php script that cal also be called by an admin user or a scheduled batch job (make sure that no other files are overwritten and that this cannot be user for code injections)


// open database
$app = new frontend();
global $sys;
$db_con = $app->start("view", $msg, $url_array);


if ($db_con->is_open()) {

    // load the session user parameters
    // TODO Prio 2 create a session object and include the user in the prg_start return object
    $usr = new user;
    $web_txt .= $usr->get();
    // TODO Prio 1 set the user of the $msg and make the the only place where the requesting user is stored


    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    // at minimum the IP address is used as the user id, so id() > 0 is always true for real requests
    if ($usr->id() > 0) {

        // TODO Prio o move loading of user data to frontend e.g. to skip it for the login page
        $usr->load_usr_data();

        $usr_ui = new user_ui();
        $usr_ui->set_from_json($usr->api_json(), $msg);

        $ui = new frontend('view');
        $ui->load_cache();

        // publish the loaded ui cache to the allowed global so renderers
        // (e.g. phrase_list::category_subtitle) can read the verb type cache
        $ui_sys = $ui->dto;

        // load the user-specific frontend configuration onto the ui cache
        // TODO Prio 1 load the config from cache if nothing has been changed
        $ui_sys->cfg = new config();
        $ui_sys->cfg->load($sys);

        // execute the user request and POST-Redirect-GET to prevent re-submission on reload
        $sys->times->switch(system_time_type::URL_TO_ACTION);
        $is_post_action = isset($url_array[url_var::POST_SUBMIT]);
        $is_get_action = in_array($url_array[url_var::MASK] ?? 0, views::GET_ACTION_IDS);
        if ($is_post_action || $is_get_action) {
            $url_array = $ui->url_to_action($url_array, $usr, $usr_ui, $msg, $ui->dto);
        }

        // show the result to the user
        $sys->times->switch(system_time_type::URL_TO_HTML);
        $web_txt .= $ui->url_to_html($url_array, $usr_ui, $msg, $ui->dto);
        $sys->times->switch(system_time_type::CLOSE);
    }

    // close the database
    $app->end($db_con, false);
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

