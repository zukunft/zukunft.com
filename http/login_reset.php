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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

//include("auth.php");
// all taken from
function getRandomKey($length = 20)
    {
        $chars = "A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6";
        $key = "";
        for ($i = 0; $i < $length; $i++) {
            $key .= $chars{mt_rand(0, strlen($chars) - 1)};
        }
        return $key;
    }


// open database 
$link = zu_start("login_reset", "center_form", $debug);

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($usr->id > 0) {

    $result = ''; // reset the html code var
    $msg = ''; 

    $_SESSION['logged'] = FALSE; 

    if(isset($_POST['submit'])){ 
        
      // Lets search the databse for the user name and password
      // don't use the sf shortcut here!
      $usr_name = mysql_real_escape_string($_POST['username']); 
      $usr_mail = mysql_real_escape_string($_POST['email']); 
      $sql = "SELECT * FROM users  
              WHERE user_name ='".$usr_name."' 
                  OR email ='".$usr_mail."'
                    LIMIT 1"; 
      $sql_result = mysql_query($sql); 
      if(mysql_num_rows($sql_result) == 1){ 
        $row = mysql_fetch_array($sql_result); 
        $user_id      = $row['user_id'];
        $user_email   = $row['email'];

        // save activation key
        $key = getRandomKey(20);
        $db_con = new mysql;         
        $db_con->type = "user";         
        $db_con->usr_id = $usr->id;         
        $sql_result = $db_con->update($user_id, array("activation_key","activation_key_timeout"), array(sf($key),'NOW() + INTERVAL 1 DAY'), $debug-1);
        /*
        $sql = "UPDATE users 
                SET activation_key = ".sf($key).", 
                    activation_key_timeout = NOW() + INTERVAL 1 DAY 
                WHERE user_id =".$user_id.";"; 
        $sql_result = $db_con->exe($sql, $usr->id, DBL_SYSLOG_ERROR, "login_reset.php", $debug-1);
        */

        $mail_to      = $row['email'];
        $mail_subject = 'zukunft.com - password reset request';
        // to be replaced by
        $mail_body    = sprintf('Hello, ' . "\n\n" . 'Please use the following activation key to reset your password: %4$s' . "\n\n" . 'Or use this link:' . "\n" . '%1$s/%2$s?id=%3$s&key=%4$s' . "\n\n" . 'If you did not request a password reset for %1$s recently, please ignore it.', 'www.zukunft.com', 'login_activate.php', $user_id, $key);
        $mail_header  = 'From: admin@zukunft.com' . "\r\n" .
                        'Reply-To: admin@zukunft.com' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();
        mail($mail_to, $mail_subject, $mail_body, $mail_header);
        // to do: ask if cockies are allowed: if yes, the session id does not need to be forwarded
        // if no, use the session id
        header("Location: http/login_activate.php?id=".$user_id.""); // Modify to go to the page you would like 
        //header("Location: view.php?sid=".SID.""); // Modify to go to the page you would like 
        exit; 
      } else {
        $msg .= '<font color="red">Username and email no found. Please try again.</font><br>'; 
      }  
    }  
  }  

  if (!$_SESSION['logged']) {
    $result .= dsp_form_center(); 
    $result .= dsp_logo_big(); 
    $result .= '<br><br>'; 
    $result .= '<form action="login_reset.php" method="post">'; 
    $result .= dsp_text_h2('Reset password<br>'); 
    $result .= 'Fill in one of the fields to receive a temporary password via email:<br><br> '; 
    $result .= 'Username:<br> '; 
    $result .= '<input type="username" name="username"><br><br> '; 
    $result .= 'Email address:<br> '; 
    $result .= '<input type="email" name="email"><br><br> '; 
    $result .= $msg; 
    $result .= '  <input type="submit" name="submit" value="Reset password"> '; 
    $result .= '</form>   ';
    $result .= '</div>   ';
  }

  // display the view
  echo $result;

// close the database  
zu_end($link, $debug);

?>
