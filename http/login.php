<?php

/*

  login.php - display the login form
  ---------
  
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

use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user as user_dsp;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// reset the html code var
$html_str = '';
$msg_txt = '';
$next_url = '';
$msg = new Message();

// open database
$app = new frontend();
$this_script = 'login';
$db_con = $app->start($this_script, $msg);

global $debug;
global $sys;
global $cfg;

if ($db_con->is_open()) {

    // load the session user parameters
    $usr = new user;
    $html_str = $usr->get();

    $mtr = new Translator($cfg->language());

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        // in doughty the session is logged out
        $_SESSION[url_var::SESSION_LOGGED] = FALSE;

        // get the calling url based on the given back parameters for the result page
        $next_url = html_base::url_from_back($_GET);

        if (isset($_POST[url_var::POST_SUBMIT])) {

            $html = new html_base();

            // secure the vars
            $usr_name = htmlspecialchars($_POST[url_var::USERNAME_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);
            $pw = htmlspecialchars($_POST[url_var::USER_PASSWORD_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);

            $db_usr = new user;
            $login_msg = new user_message();
            if ($db_usr->login($usr_name, $pw, $login_msg)) {
                // TODO ask if cookies are allowed: if yes, the session id does not need to be forwarded
                if ($next_url != '') {
                    header("Location: " . $next_url);
                } else {
                    header("Location: ../view.php");
                }
                exit;
            } else {
                if ($db_usr->has_db_id()) {
                    // user found but password wrong — offer reset link
                    $url = $html->url(rest_ctrl::LOGIN_RESET);
                    $ref = $html->ref($url, $mtr->txt(msg_id::PASSWORD_WRONG),
                        $mtr->txt(msg_id::PASSWORD_WRONG_TITLE));
                    $msg_txt .= $html->dsp_err($mtr->txt(msg_id::LOGIN_FAILED) . ' ' . $ref);
                } else {
                    // user not found
                    $msg_txt .= $html->dsp_err($mtr->txt(msg_id::LOGIN_FAILED) . ' '
                        . $login_msg->get_last_message_translated());
                }
            }
        }
    }

    $html = new html_base();
    if (!$_SESSION[url_var::SESSION_LOGGED]) {
        $web_usr = new user_dsp();
        $form_str = $web_usr->form_login($msg_txt, $mtr);

        // TODO Prio 3 use a changing logo to show something positive of today or a person that has done something positive and is somehow linked to today
        $html_str = $html->logo_flex();
        $html_str .= $html->br2();
        $html_str .= $html->div($form_str, html_base::CLASS_INPUT_SECTION);
    }

    // create the page
    $html_str = $html->page_html(
        'en',
        $html->header_html($this_script, ''),
        $html->main_body($html_str),
        $html->footer_html(),
    );

    // display the view
    echo $html_str;

    // close the database
    $app->end($db_con);
} else {
    echo 'cannot connect to database because' . $msg->text();
}