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
$db_con = prg_start("login", "center_form");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    $result = ''; // reset the html code var
    $msg = '';

    $_SESSION['logged'] = FALSE;
    // the original calling page that should be shown after the login is finished
    if (isset($_POST['back'])) {
        $back = $_POST['back'];
    } else {
        $back = $_GET['back'];
    }

    if (isset($_POST['submit'])) {

        // Lets search the database for the user name and password
        // don't use the sf shortcut here!
        $usr = mysqli_real_escape_string($_POST['username']);
        $pw_hash = hash('sha256', mysqli_real_escape_string($_POST['password']));
        $sql = "SELECT * FROM users  
              WHERE user_name='$usr'
                AND password='$pw_hash'
                    LIMIT 1";
        $sql_result = mysqli_query($sql);
        if (mysqli_num_rows($sql_result) == 1) {
            $row = mysqli_fetch_array($sql_result);
            session_start();
            $_SESSION['usr_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['logged'] = TRUE;
            // to do: ask if cookies are allowed: if yes, the session id does not need to be forwarded
            // if no, use the session id
            if ($back <> '') {
                header("Location: " . $back);
            } else {
                header("Location: ../view.php");
            }
            //header("Location: ../view.php?sid=".SID."");
            exit;
        } else {
            $msg .= dsp_err('Login failed. <a href="/http/login_reset.php" title="Send a new password via email.">Forgot password?</a>');
        }
    }
}

if (!$_SESSION['logged']) {
    $result .= dsp_form_center();
    $result .= dsp_logo_big();
    $result .= '<br><br>';
    $result .= '<form action="login.php" method="post">';
    $result .= '  User Name:<br> ';
    $result .= '  <input type="text" name="username"><br><br> ';
    $result .= '  password:<br> ';
    $result .= '  <input type="password" name="password"><br><br> ';
    $result .= '  <input type="hidden" name="back" value="' . $back . '"> ';
    $result .= $msg;
    $result .= '  <input type="submit" name="submit" value="Login"> ';
    $result .= '</form>   ';
    $result .= '</div>   ';
}

// separate the footer, because this is a short page
$result .= '<br><br>';

// display the view
echo $result;

// close the database  
prg_end($db_con);
