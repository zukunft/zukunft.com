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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

// open database 
$db_con = prg_start("login_activate", "center_form");


$result = ''; // reset the html code var
$msg = '';

$_SESSION['logged'] = FALSE;

if (isset($_POST['submit'])) {
    $usr_id = $_POST['id'];
    $debug = $_POST['debug'];
    log_debug("login_activate (user: " . $usr_id . ")");

    $db_con = new sql_db;
    $db_con->usr_id = $usr_id;

    // check key
    $sql = "SELECT activation_key FROM users  
            WHERE user_id =" . $usr_id . ";";
    $db_row = $db_con->get1_old($sql);
    $db_key = $db_row['activation_key'];
    $sql = "SELECT activation_key_timeout FROM users  
            WHERE user_id =" . $usr_id . ";";
    $db_row = $db_con->get1_old($sql);
    $db_time_limit = $db_row['activation_key_timeout'];
    // get the server now
    $sql = "SELECT NOW() AS db_dow;";
    $db_row = $db_con->get1_old($sql);
    $db_now = $db_row['db_dow'];
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
            $pw_hash = hash('sha256', mysqli_real_escape_string($_POST['password']));
            //$pw_hash = password_hash($_POST['password'], password_DEFAULT);
            $db_con->set_type(DB_TYPE_USER);
            $db_con->set_usr(SYSTEM_USER_ID);
            $db_con->update($usr_id, array('password', 'activation_key', 'activation_key_timeout'), array($pw_hash, '', 'NOW()'));
            /*
            $sql = sprintf("UPDATE users
                          SET password       = '%s',
                              activation_key = '', 
                              activation_key_timeout = NOW()
                        WHERE user_id =" . $usr_id . ";",
                $pw_hash) or die(mysqli_error());
            $sql_result = mysql_query($sql);
            */

            $db_con->set_type(DB_TYPE_USER);
            $db_con->set_usr(SYSTEM_USER_ID);
            $db_con->set_where($usr_id);
            $sql = $db_con->select();

            $db_row = $db_con->get1_old($sql);
            $usr_name = $db_row['user_name'];

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
            $msg .= dsp_err($error) . '<br>';
        }
    } else {
        if ($db_key <> "") {
            //$msg .= dsp_err ('Error: activation key ('.$db_key.'/'.$_POST['key'].' for '.$usr_id.') does not match. Please request the password reset again.').'<br>';
            $msg .= dsp_err('Error: activation key does not match. Please request the password reset again.') . '<br>';
        } else {
            $msg .= dsp_err('Activation key is not valid any more. Please request the password reset again.') . '<br>';
        }
    }
}

if (!$_SESSION['logged']) {
    $usr_id = $_GET['id'];
    if ($usr_id <= 0) {
        if (isset($_POST['submit'])) {
            $usr_id = $_POST['id'];
        }
    }
    if ($usr_id > 0) {
        $result .= dsp_form_center();
        $result .= dsp_logo_big();
        $result .= '<br><br>';
        $result .= '<form action="login_activate.php" method="post">';
        $result .= '<input type="hidden" name="id" value="' . $usr_id . '">';
        if ($debug > 0) {
            $result .= '<input type="hidden" name="debug" value="' . $debug . '">';
        }
        $result .= dsp_text_h2('Change password<br>');

        $key = $_GET['key'];
        if ($key <> '') {
            $result .= '<input type="hidden" name="key" value="' . $key . '">';
        } else {
            $result .= 'Please enter the activation key sent via email or open the link in the email:<br><br> ';
            $result .= '<p>Activation key:<br><input type="reset key" name="key"></p>  ';
        }

        $result .= 'Please enter a new password:<br><br> ';
        $result .= '<p>password:<br><input type="password" name="password"></p>  ';
        $result .= '<p>Re-Type password:<br><input type="password" name="re_password"></p>  ';
        $result .= $msg;
        $result .= '  <input type="submit" name="submit" value="Change password"> ';
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