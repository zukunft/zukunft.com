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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database 
$db_con = prg_start("signup", "center_form", $debug);

  $result = ''; // reset the html code var

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // get the parameters
  if (isset($_POST['submit'])) { 
    # search the database to see if the user name has been taken or not 
    $sql = sprintf("SELECT * FROM users WHERE user_name='%s' LIMIT 1",mysqli_real_escape_string($_POST['user_name'])); 
    $sql_result = mysqli_query($sql); 
    $row = mysqli_fetch_array($sql_result); 
    #check too see what fields have been left empty, and if the passwords match 
    $usr_name = $_POST['user_name']; 
    if ($row || empty($_POST['user_name'])
             || empty($_POST['email'])
             || empty($_POST['password'])
             || empty($_POST['re_password'])
             ||$_POST['password']!=$_POST['re_password']) { 
      # if a field is empty, or the passwords don't match make a message 
      $error = '<p>'; 
      if(empty($_POST['email'])){ 
        $error .= 'Email can\'t be empty<br>'; 
      } else {
        if (empty($_POST['user_name'])) { 
          $usr_name = $_POST['email']; 
        }
      } 
      if(empty($_POST['password'])){ 
        $error .= 'password can\'t be empty<br>'; 
      } 
      if(empty($_POST['re_password'])){ 
        $error .= 'You must re-type your password<br>'; 
      } 
      if($_POST['password']!=$_POST['re_password']){ 
        $error .= 'passwords don\'t match<br>'; 
      } 
      if($row){ 
        $error .= 'User Name already exists<br>'; 
      } 
      $error .= '</p>'; 
    }else{ 
      # If all fields are not empty, and the passwords match, 
      # create a session, and session variables, 
      $pw_hash = hash('sha256', mysqli_real_escape_string($_POST['password'])); 
      //$pw_hash = password_hash($_POST['password'], password_DEFAULT);
      $sql = sprintf("INSERT INTO users (`user_name`,`email`,`password`) 
        VALUES('%s','%s','%s')", 
        mysqli_real_escape_string($usr_name), 
        mysqli_real_escape_string($_POST['email']), 
        $pw_hash)or die(mysqli_error()); 
      $sql_result = mysqli_query($sql); 
      // get user id
      $sql = "SELECT user_id FROM users  
              WHERE user_name='".$usr_name."' LIMIT 1"; 
      //$db_con = new mysql;
      $db_con->usr_id = SYSTEM_USER_ID;         
      $db_row = $db_con->get1($sql, $debug-5);  
      $usr_id = $db_row['user_id'];
      if ($usr_id > 0) {
        // auto login
        session_start(); 
        $_SESSION['usr_id'] = $usr_id; 
        $_SESSION['user_name'] = $usr_name; 
        $_SESSION['logged'] = TRUE; 
      } else {
        log_err("Cannot find id for ".$usr_name." after signup.","signup.php");
      }
      
      # Redirect the user to a main page 
      header("Location: view.php"); 
      exit; 
    } 
  } 
  
  # echo out each variable that was set from above, 
  # then destroy the variable. 
  if(isset($error)){ 
    $result .=  $error; 
    unset($error); 
  } 
  $result .= dsp_form_center(); 
  $result .= dsp_logo_big(); 
  $result .= '<br><br>'; 
  $result .= '<p>Please signup for <b>alpha testing</b> of zukunft.com.</p>'; 
  $result .= '<p>'.dsp_err ('Be aware that during this phase your <b>data may get lost</b> or is changed due to program errors or updates.').'</p>'; 
  $result .= '<form action="'.$_SERVER['PHP_SELF'].'" method="post"> ';
  $result .= '<p>User Name:<br><input type="text" name="user_name" value="'.$_POST['user_name'].'"></p> '; 
  $result .= '<p>Email:<br><input type="text" name="email" value="'.$_POST['email'].'"></p>  ';
  $result .= '<p>password:<br><input type="password" name="password"></p>  ';
  $result .= '<p>Re-Type password:<br><input type="password" name="re_password"></p>  ';
  $result .= '<p><input type="submit" name="submit" value="Sign Up"></p>  '; 
  $result .= '</form>  '; 
  $result .= '</div>   ';
  
  // display the view
  echo $result;

// close the database  
prg_end($db_con, $debug);

?>
