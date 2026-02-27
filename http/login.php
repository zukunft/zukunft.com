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

use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Random\RandomException;

// reset the html code var
$html_str = '';
$msg_txt = '';
$back = '';
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

        $_SESSION[url_var::SESSION_LOGGED] = FALSE;
        // the original calling page that should be shown after the login is finished
        if (isset($_POST[url_var::BACK])) {
            $back = filter_var($_POST[url_var::BACK] ?? '', FILTER_SANITIZE_URL);
        } else {
            $back = filter_var($_GET[url_var::BACK] ?? '', FILTER_SANITIZE_URL);
        }

        if (isset($_POST[url_var::POST_SUBMIT])) {

            $html = new html_base();

            // secure the vars
            $usr_name = htmlspecialchars($_POST[url_var::USERNAME_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);
            $pw = htmlspecialchars($_POST[url_var::USER_PASSWORD_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);

            // Let's search the database for the username and password
            $db_usr = new user;
            $db_usr->load_by_name($usr_name);
            if ($db_usr->has_db_id()) {
                if (!password_verify($pw, $db_usr->password)) {
                    $msg->add_id(msg_id::PASSWORD_WRONG);
                    $url = $html->url(rest_ctrl::LOGIN_RESET);
                    $ref = $html->ref($url, $mtr->txt(msg_id::PASSWORD_WRONG),
                        $mtr->txt(msg_id::PASSWORD_WRONG_TITLE));
                    $msg_txt .= $html->dsp_err($mtr->txt(msg_id::LOGIN_FAILED). ' ' . $ref);
                }
            } else {
                $msg->add(msg_id::USER_NAME_NOT_FOUND, [
                    msg_id::VAR_USER_NAME => $usr_name
                ]);
                $msg_txt .= $html->dsp_err($mtr->txt(msg_id::LOGIN_FAILED) . ' '
                    . $msg->get_last_message_translated());
            }

            if ($msg->is_ok()) {
                session_start();
                if (empty($_SESSION[url_var::SESSION_TOKEN])) {
                    try {
                        $_SESSION[url_var::SESSION_TOKEN] = bin2hex(random_bytes(32));
                    } catch (RandomException $e) {
                        log_err('RandomException ' . $e->getMessage());
                    }
                }
                $_SESSION[url_var::SESSION_USER_ID] = $db_usr->id();
                $_SESSION[url_var::USERNAME_HUMAN] = $db_usr->name();
                $_SESSION[url_var::SESSION_LOGGED] = TRUE;
                // TODO ask if cookies are allowed: if yes, the session id does not need to be forwarded
                // if no, use the session id
                if ($back <> '') {
                    header("Location: " . $back);
                } else {
                    header("Location: ../view.php");
                }
                //header("Location: ../view.php?sid=".SID."");
                exit;
            }
        }
    }

    $html = new html_base();
    if (!$_SESSION[url_var::SESSION_LOGGED]) {
        $form_str = $mtr->txt(msg_id::FORM_NAME_USER_NAME_OR_EMAIL) . $html->br();
        $form_str .= $html->form_input(html_base::INPUT_TEXT, url_var::USERNAME_HUMAN) . $html->br2();
        $form_str .= $mtr->txt(msg_id::FORM_NAME_PASSWORD) . $html->br();
        $form_str .= $html->form_input(html_base::INPUT_PASSWORD, url_var::USER_PASSWORD_HUMAN) . $html->br2();
        // TODO Prio 2 highlight message e.g. using color red
        $form_str .= $msg_txt;
        $form_str .= $html->form_hidden(url_var::BACK, $back);
        $form_str .= $html->form_hidden(url_var::SESSION_TOKEN, $_SESSION[url_var::SESSION_TOKEN]);
        $form_str .= $html->button_submit($mtr->txt(msg_id::FORM_NAME_LOGIN));
        $form_str = $html->form_simple($this_script . def::FILE_PHP, html_base::METHOD_POST, $form_str);

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