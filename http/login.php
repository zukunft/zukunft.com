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

use cfg\const\paths;

include_once paths::MODEL_USER . 'user_db.php';

use cfg\user\user_db;
use controller\controller;
use html\rest_call;
use html\html_base;
use cfg\user\user;
use shared\api;

// open database
$db_con = prg_start("login", "center_form");
$html = new html_base();

if ($db_con->is_open()) {

    // load the session user parameters
    $usr = new user;
    $result = $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {

        $result = ''; // reset the html code var
        $msg = '';

        $_SESSION['logged'] = FALSE;
        // the original calling page that should be shown after the login is finished
        if (isset($_POST[api::URL_VAR_BACK])) {
            $back = $_POST[api::URL_VAR_BACK];
        } else {
            $back = $_GET[api::URL_VAR_BACK] = '';
        }

        if (isset($_POST['submit'])) {

            $html = new html_base();

            // Let's search the database for the username and password
            // don't use the sf shortcut here!
            $usr = mysqli_real_escape_string($db_con->mysql, $_POST['username']);
            $pw_hash = hash('sha256', mysqli_real_escape_string($db_con->mysql, $_POST['password']));
            $sql = "SELECT * FROM users  
                  WHERE user_name='$usr'
                    AND password='$pw_hash'
                        LIMIT 1";
            $sql_result = mysqli_query($db_con->mysql, $sql);
            if (mysqli_num_rows($sql_result) == 1) {
                $row = mysqli_fetch_array($sql_result);
                session_start();
                $_SESSION['usr_id'] = $row[user_db::FLD_ID];
                $_SESSION['user_name'] = $row[user_db::FLD_NAME];
                $_SESSION['logged'] = TRUE;
                // TODO ask if cookies are allowed: if yes, the session id does not need to be forwarded
                // if no, use the session id
                if ($back <> '') {
                    header("Location: " . $back);
                } else {
                    header("Location: ../view.php");
                }
                //header("Location: ../view.php?sid=".SID."");
                exit;
            } else {
                $url = $html->url(rest_ctrl::LOGIN_RESET);
                $ref = $html->ref($url, 'Forgot password?', 'Send a new password via email.');
                $msg .= $html->dsp_err('Login failed. ' . $ref);
            }
        }
    }

    if (!$_SESSION['logged']) {
        $html = new html_base();
        $result .= $html->dsp_form_center();
        $result .= $html->logo_big();
        $result .= '<br><br>';
        $result .= '<form action="login.php" method="post">';
        $result .= '  User Name:<br> ';
        $result .= '  <input type="' . html_base::INPUT_TEXT . '" name="username"><br><br> ';
        $result .= '  password:<br> ';
        $result .= '  <input type="' . html_base::INPUT_PASSWORD . '" name="password"><br><br> ';
        $result .= '  <input type="' . html_base::INPUT_HIDDEN . '" name="back" value="' . $back . '"> ';
        $result .= $msg;
        $result .= '  <input type="' . html_base::INPUT_SUBMIT . '" name="submit" value="Login"> ';
        $result .= '</form>   ';
        $result .= '</div>   ';
    }

    // separate the footer, because this is a short page
    $result .= '<br><br>';

    // display the view
    echo $result;

    // close the database
    prg_end($db_con);
}