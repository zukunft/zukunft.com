<?php

/*

  login_reset.php - reset the password of a login user name
  ---------------
  
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
global $debug;
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\cfg\user\user;

$html = new html_base();
$msg_txt = '';

// TODO include("auth.php");
// all taken from
function getRandomKey(int $length = 20): string
{
    $chars = "A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6";
    $key = "";
    for ($i = 0; $i < $length; $i++) {
        $key .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $key;
}


// open database 
$app = new frontend();
$db_con = $app->start("login_reset", "center_form");

if ($db_con->is_open()) {

    // load the session user parameters
    $usr = new user;
    $result = $usr->get();
    $usr_msg = new user_message();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id > 0) {

        $result = ''; // reset the html code var

        $_SESSION[url_var::SESSION_LOGGED] = FALSE;

        if (isset($_POST[url_var::POST_SUBMIT])) {

            // Lets search the database for the user name and password
            // don't use the sf shortcut here!
            // TODO prevent code injection
            $usr_name = $_POST[url_var::USERNAME_HUMAN];
            $usr_mail = $_POST[url_var::EMAIL_HUMAN];
            $db_usr = new user();
            if ($db_usr->load_by_name_or_email($usr_name, $usr_mail)) {

                // save activation key
                $key = getRandomKey();
                $timeout = new DateTime();
                try {
                    // TODO Prio 1 get timeout duration from the system config
                    $timeout->modify('+1 day');
                } catch (DateMalformedStringException $e) {
                    log_err('timeout setting failed due to ' . $e->getMessage());
                }

                $db_usr->activation_key = $key;
                $db_usr->activation_timeout = $timeout;
                $db_usr->save($usr_msg);

                $mail_to = $db_usr->email;
                $mail_subject = 'zukunft.com - password reset request';
                // to be replaced by
                $activate_url = 'www.zukunft.com' . api::LOGIN_ACTIVATE_SCRIPT
                    . '&' . url_var::ID . '=' . $db_usr->id
                    . '&' . url_var::POST_KEY . '=' . $key;
                $mail_body = 'Hello,' . "\n\n"
                    . 'Please use the following activation key to reset your password: ' . $key . "\n\n"
                    . 'Or use this link:' . "\n" . $activate_url . "\n\n"
                    . 'If you did not request a password reset for www.zukunft.com recently, please ignore it.';
                $mail_header = 'From: admin@zukunft.com' . "\r\n" .
                    'Reply-To: admin@zukunft.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                mail($db_usr->email, $mail_subject, $mail_body, $mail_header);
                // TODO ask if cookies are allowed: if yes, the session id does not need to be forwarded
                // if no, use the session id
                header("Location: " . api::LOGIN_ACTIVATE_SCRIPT . "&" . url_var::ID . "=" . $db_usr->id);
                //header("Location: view.php?sid=".SID.""); // Modify to go to the page you would like
                exit;
            } else {
                $msg_txt .= '<p style="color:red">Username and email no found. Please try again.</p><br>';
            }
        }
    }

    if (!$_SESSION[url_var::SESSION_LOGGED]) {
        $html = new html_base();
        $result .= $html->dsp_form_center();
        $result .= $html->logo_big();
        $result .= '<br><br>';
        $result .= '<form action="login_reset.php" method="post">';
        $result .= $html->dsp_text_h2('Reset password<br>');
        $result .= 'Fill in one of the fields to receive a temporary password via email:<br><br> ';
        $result .= 'Username:<br> ';
        $result .= '<input name="username"><br><br> ';
        $result .= 'Email address:<br> ';
        $result .= '<input type="' . html_base::INPUT_EMAIL . '" name="email"><br><br> ';
        $result .= $msg_txt;
        $result .= '  <input type="' . html_base::INPUT_SUBMIT . '" name="submit" value="Reset password"> ';
        $result .= '</form>   ';
        $result .= '</div>   ';
    }

    // display the view
    echo $result;

    // close the database
    $app->end($db_con);
}