<?php

/*

  signup.php - display the signup form
  ----------
  
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

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Random\RandomException;

// reset the html code var
$html_str = '';
$msg_txt = '';
$back = '';
$msg = new user_message();

// open database
$app = new frontend();
$this_script = 'signup';
$db_con = $app->start($this_script, $msg);
$html = new html_base();

global $cfg;

if ($db_con->is_open()) {

    $html_str = ''; // reset the html code var

    // load the session user parameters
    $usr = new user;
    $html_str .= $usr->get();
    $msg->usr = $usr;

    $mtr = new Translator($cfg->language());

    // get the parameters
    if (isset($_POST[url_var::POST_SUBMIT])) {

        #check to see what fields have been left empty, and if the passwords match
        $usr_name = htmlspecialchars($_POST[url_var::USERNAME_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);
        $email = htmlspecialchars($_POST[url_var::EMAIL_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);
        $password = htmlspecialchars($_POST[url_var::USER_PASSWORD_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);
        $re_password = htmlspecialchars($_POST[url_var::USER_PASSWORD_RETYPE_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);

        # search the database to see if the username has been taken or not
        $db_usr = new user;
        $db_usr->load_by_name($usr_name);

        if ($db_usr->has_db_id() || empty($_POST[url_var::USERNAME_HUMAN])
            || empty($_POST[url_var::EMAIL_HUMAN])
            || empty($_POST[url_var::USER_PASSWORD_HUMAN])
            || empty($_POST[url_var::USER_PASSWORD_RETYPE_HUMAN])
            || $password != $re_password) {
            # if a field is empty, or the passwords don't match make a message
            $error = '<p>';
            if (empty($_POST[url_var::EMAIL_HUMAN])) {
                $error .= 'Email can\'t be empty<br>';
            } else {
                if (empty($_POST[url_var::USERNAME_HUMAN])) {
                    $usr_name = $_POST[url_var::EMAIL_HUMAN];
                }
            }
            if (empty($_POST[url_var::USER_PASSWORD_HUMAN])) {
                $error .= 'password can\'t be empty<br>';
            }
            if (empty($_POST[url_var::USER_PASSWORD_RETYPE_HUMAN])) {
                $error .= 'You must re-type your password<br>';
            }
            if ($_POST[url_var::USER_PASSWORD_HUMAN] != $_POST[url_var::USER_PASSWORD_RETYPE_HUMAN]) {
                $error .= 'passwords don\'t match<br>';
            }
            if ($db_usr->has_db_id()) {
                $error .= 'User Name already exists<br>';
            }
            $error .= '</p>';
        } else {
            # If all fields are not empty, and the passwords match,
            # create a session, and session variables,
            $usr_email = $_POST[url_var::EMAIL_HUMAN];
            $pw_hash = password_hash($_POST[url_var::USER_PASSWORD_HUMAN], PASSWORD_BCRYPT);
            $db_con->set_class(user::class);
            $db_con->set_usr(users::SYSTEM_ID);
            // TODO use user object and prepared query
            $new_usr = new user();
            $new_usr->name = $usr_name;
            $new_usr->email = $usr_email;
            $new_usr->password = $pw_hash;
            $new_usr->save($msg);
            $log_id = $new_usr->id();
            if ($log_id <= 0) {
                log_err('Insert of user ' . $usr_name . ' with email ' . $usr_email . ' failed.', 'signup');
            }
            /*
            $sql = sprintf("INSERT INTO users (`user_name`,`email`,`password`)
              VALUES('%s','%s','%s')",
              mysqli_real_escape_string($usr_name),
              mysqli_real_escape_string($_POST[url_var::EMAIL_HUMAN]),
              $pw_hash)or die(mysqli_error());
            $sql_result = mysqli_query($sql);
            */
            // get user id from the name
            $usr_by_name = new user();
            $usr_by_name->load_by_name($usr_name);
            $usr_id = $usr_by_name->id();
            if ($usr_id > 0) {
                // auto login
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
                $_SESSION[url_var::SESSION_LOGGED] = TRUE;
            } else {
                log_err("Cannot find id for " . $usr_name . " after signup.", "signup.php");
            }

            # Redirect the user to a main page
            header("Location: view.php");
            exit;
        }
    }

    # echo out each variable that was set from above,
    # then destroy the variable.
    if (isset($error)) {
        $html_str .= $error;
        unset($error);
    }
    // secure the vars
    $host = htmlspecialchars($_SERVER[rest_ctrl::PHP_SELF], ENT_QUOTES, def::ENCODING);
    $usr_name = htmlspecialchars($_POST[url_var::USERNAME_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);
    $email = htmlspecialchars($_POST[url_var::EMAIL_HUMAN] ?? '', ENT_QUOTES, def::ENCODING);

    $html = new html_base();
    $form_usr = $mtr->txt(msg_id::FORM_NAME_USER_NAME) . $html->br();
    $form_usr .= $html->form_input(html_base::INPUT_TEXT, url_var::USERNAME_HUMAN, $usr_name);
    $form_str = $html->p($form_usr);
    $form_mail = $mtr->txt(msg_id::FORM_NAME_USER_EMAIL) . $html->br();
    $form_mail .= $html->form_input(html_base::INPUT_TEXT, url_var::EMAIL_HUMAN, $email);
    $form_str .= $html->p($form_mail);
    $form_pw = $mtr->txt(msg_id::FORM_NAME_PASSWORD) . $html->br();
    $form_pw .= $html->form_input(html_base::INPUT_PASSWORD, url_var::USER_PASSWORD_HUMAN);
    $form_str .= $html->p($form_pw);
    $form_pwr = $mtr->txt(msg_id::FORM_NAME_PASSWORD_RE) . $html->br();
    $form_pwr .= $html->form_input(html_base::INPUT_PASSWORD, url_var::USER_PASSWORD_RETYPE_HUMAN);
    $form_str .= $html->p($form_pwr);
    $form_str .= $html->form_hidden(url_var::BACK, $back);
    $form_str .= $html->form_hidden(url_var::SESSION_TOKEN, $_SESSION[url_var::SESSION_TOKEN]);
    $form_str .= $html->button_submit($mtr->txt(msg_id::SIGN_UP));
    $form_str = $html->form_simple($this_script . def::FILE_PHP, html_base::METHOD_POST, $form_str);

    // TODO Prio 3 use a changing logo to show something positive of today or a person that has done something positive and is somehow linked to today
    $html_str = '<p>Please signup for <b>alpha testing</b> of zukunft.com.</p>';
    $html_str .= '<p>' . $html->dsp_err('Be aware that during this phase your <b>data may get lost</b> or is changed due to program errors or updates.') . '</p>';
    $html_str .= $html->logo_flex();
    $html_str .= $html->br2();
    $html_str .= $html->div($form_str, html_base::CLASS_INPUT_SECTION);

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
}