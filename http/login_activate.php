<?php

/*

  login_activate.php - to activate an login user name
  ------------------
  
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
include_once PHP_PATH . 'zu_lib.php';

use html\html_base;
use cfg\db\sql_db;
use cfg\user;
use shared\api;

// open database
$db_con = prg_start("login_activate", "center_form");
$html = new html_base();

$result = ''; // reset the html code var
$msg = '';

$_SESSION['logged'] = FALSE;

if (isset($_POST['submit'])) {
    $html = new html_base();

    $usr_id = $_POST[api::URL_VAR_ID];
    $debug = $_POST['debug'];
    log_debug("login_activate (user: " . $usr_id . ")");

    $db_con = new sql_db;
    $db_con->usr_id = $usr_id;

    // check key
    $usr = new user();
    $usr->load_by_id($usr_id);
    $db_key = $usr->activation_key;
    $db_time_limit = $usr->activation_timeout; // TODO check if and when the conversion to time should be done
    $db_now = $usr->db_now; // get the server now
    log_debug("login_activate (db: " . $db_key . ", post: " . $_POST['key'] . ", limit: " . $db_time_limit . ", db now:" . $db_now . ")");
    if ($db_key == $_POST['key'] and $db_time_limit > $db_now) {

        // check the user input
        $error = '';
        if (empty($_POST['password'])) {
            $error .= 'password can\'t be empty<br>';
        }
        if (empty($_POST['re_password'])) {
            $error .= 'You must re-type your password<br>';
        }
        if ($_POST['password'] != $_POST['re_password']) {
            $error .= 'passwords don\'t match<br>';
        }

        if ($error == '') {
            // If all fields are not empty, and the passwords match,
            // create a session, and session variables,
            $pw_hash = hash('sha256', mysqli_real_escape_string($db_con->mysql, $_POST['password']));
            //$pw_hash = password_hash($_POST['password'], password_DEFAULT);
            $db_con->set_class(user::class);
            $db_con->set_usr(SYSTEM_USER_ID);
            $db_con->update_old($usr_id, array('password', 'activation_key', 'activation_timeout'), array($pw_hash, '', 'NOW()'));
            /*
            $sql = sprintf("UPDATE users
                          SET password       = '%s',
                              activation_key = '', 
                              activation_timeout = NOW()
                        WHERE user_id =" . $usr_id . ";",
                $pw_hash) or die(mysqli_error());
            $sql_result = mysql_query($sql);
            */

            // TODO check if a system or admin user is needed to read any user
            $usr = new user();
            $usr->load_by_id($usr_id);
            $usr_name = $usr->name();

            if ($usr_id > 0 and $usr_name <> '') {
                // auto login
                session_start();
                $_SESSION['usr_id'] = $usr_id;
                $_SESSION['user_name'] = $usr_name;
                $_SESSION['logged'] = TRUE;
            } else {
                log_err("Cannot find id for " . $usr_name . " after password change.", "login_activate.php");
            }

            # Redirect the user to a main page
            header("Location: view.php");
            exit;
        } else {
            $msg .= $html->dsp_err($error) . '<br>';
        }
    } else {
        if ($db_key <> "") {
            //$msg .= dsp_err ('Error: activation key ('.$db_key.'/'.$_POST['key'].' for '.$usr_id.') does not match. Please request the password reset again.').'<br>';
            $msg .= $html->dsp_err('Error: activation key does not match. Please request the password reset again.') . '<br>';
        } else {
            $msg .= $html->dsp_err('Activation key is not valid any more. Please request the password reset again.') . '<br>';
        }
    }
}

if (!$_SESSION['logged']) {
    $usr_id = $_GET[api::URL_VAR_ID];
    if ($usr_id <= 0) {
        if (isset($_POST['submit'])) {
            $usr_id = $_POST[api::URL_VAR_ID];
        }
    }
    if ($usr_id > 0) {
        $result .= $html->dsp_form_center();
        $result .= $html->logo_big();
        $result .= '<br><br>';
        $result .= '<form action="login_activate.php" method="post">';
        $result .= '<input type="' . html_base::INPUT_HIDDEN . '" name="id" value="' . $usr_id . '">';
        if ($debug > 0) {
            $result .= '<input type="' . html_base::INPUT_HIDDEN . '" name="debug" value="' . $debug . '">';
        }
        $result .= $html->dsp_text_h2('Change password<br>');

        $key = $_GET['key'];
        if ($key <> '') {
            $result .= '<input type="' . html_base::INPUT_HIDDEN . '" name="key" value="' . $key . '">';
        } else {
            $result .= 'Please enter the activation key sent via email or open the link in the email:<br><br> ';
            $result .= '<p>Activation key:<br><input type="' . html_base::INPUT_TEXT . '" name="key"></p>  ';
        }

        $result .= 'Please enter a new password:<br><br> ';
        $result .= '<p>password:<br><input type="' . html_base::INPUT_PASSWORD . '" name="password"></p>  ';
        $result .= '<p>Re-Type password:<br><input type="' . html_base::INPUT_PASSWORD . '" name="re_password"></p>  ';
        $result .= $msg;
        $result .= '  <input type="' . html_base::INPUT_SUBMIT . '" name="submit" value="Change password"> ';
        $result .= '</form>   ';
        $result .= '</div>   ';
    } else {
        $result .= 'Error: missing user id. Please request the password reset again.';
    }
}

// display the view
echo $result;

// close the database  
prg_end($db_con);