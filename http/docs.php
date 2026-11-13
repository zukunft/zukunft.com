<?php

/*

    docs.php - show a markdown document of /docs/ as html
    --------

    e.g. /http/docs.php?doc=faq shows docs/faq.md; only a file name of /docs/ without a path is
    accepted (see markdown::doc_path), so no other file can be read

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

use Zukunft\ZukunftCom\main\php\shared\url_var;

include_once WEB . 'frontend.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\markdown;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;

global $ui_sys;
global $debug;

// the requested document, read once here and passed on as a parameter
$doc = (string)($_GET[url_var::DOC] ?? markdown::DOC_DEFAULT);

// open database
$app = new frontend();
$msg_ui = new user_message_ui();
$db_con = $app->start("docs", $msg_ui);

// the session user for the user menu of the navbar and to decide if the edit icon is active
$usr_name = null;
$usr_role = null;
$is_admin = false;
if ($db_con->is_open()) {
    $usr = new user;
    $msg = new user_message();
    $usr->get($msg);
    if ($usr->id() > 0) {
        $usr_ui = new user_ui();
        $usr_ui->set_from_json($usr->api_json([], $msg), $msg_ui);
        $msg_ui->usr = $usr_ui;
        // the profile types for the role and the admin check of the user and the languages of the navbar
        $ui = new frontend('docs');
        $ui->load_cache($msg_ui);
        $ui_sys = $ui->dto;
        // the frontend reads the debug level of the url only from its cache, because web/ uses no other global
        $ui_sys->debug = (int)$debug;
        if (!$usr_ui->is_ip_only()) {
            $usr_name = $usr_ui->name();
            $usr_role = $usr_ui->navbar_role();
            $is_admin = $usr_ui->is_admin();
        }
    }
}

// a name that is no document of /docs/ is answered as not found
if (new markdown()->doc_path($doc) == '') {
    http_response_code(404);
}

// display the document
$html = new html_base();
echo $html->docs_page($doc, $msg_ui, $usr_name, $usr_role, $is_admin);

// close the database and measure the script loading time
$app->end($db_con, $start_time);
