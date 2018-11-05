<?php

/*

  user.php - a person who uses zukunft.com
  --------

  if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
  if a user has added 3 values and at least one is accpected by another user, he can add words and formula and he must have a valid email
  if a user has added 2 formula and both are accpected by at least one other user and noone has complained, he can change formulas and words, including linking of words
  if a user has linked a 10 words and all got accepted by one other user and noone has complained, he can request new verbs and he must have an validated address

  if a user got 10 pending word or formula discussion, he can no longer add words or formula until the open discussions are less than 10
  if a user got 5 pending word or formula discussion, he can no longer change words or formula until the open discussions are less than 5
  if a user got 2 pending verb discussion, he can no longer add verbs until the open discussions are less than 2

  the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range

  
  
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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class user {

  // database fields
  public $id           = NULL;  // the database id of the word link type (verb)
  public $name         = '';    // simply the user name, which cannot be empty
  public $ip_addr      = '';    // simply the ip address used if no user name is given
  public $email        = '';    // 
  public $first_name   = '';    // 
  public $last_name    = '';    // 
  public $code_id      = '';    // the main id to detect system users
  public $thousand_sep = '';    // the thousand seperator user for this user
  public $profile_id   = NULL;  // id of the user profile
  public $source_id    = NULL;  // id of the last source used by the user

  // user setting parameters
  public $wrd_id      = '';    // id of the last word viewed by the user
  
  // in memory only fields 
  public $wrd         = NULL;    // the last word viewed by the user
  
  //   
  private function load_db($debug) {
    $db_usr = Null;
    // select the user either by id, code_id, name or ip
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "u.user_id = ".$this->id;
      zu_debug('user->load user id '.$this->id.'.', $debug-15);
    } elseif ($this->code_id > 0) {
      $sql_where = "u.code_id = ".$this->code_id;
    } elseif ($this->name <> '') {
      $sql_where = "u.user_name = ".sf($this->name);
    } elseif ($this->ip_addr <> '') {
      $sql_where = "u.ip_address = ".sf($this->ip_addr);
    }  

    zu_debug('user->load search by "'.$sql_where.'".', $debug-14);
    if ($sql_where == '') {
      zu_err("Either the database ID, the user name, the ip address or the code_id must be set for loading a user.", "user->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $sql = "SELECT u.user_id,
                     u.code_id,
                     u.user_name,
                     u.ip_address,
                     u.email,
                     u.first_name,
                     u.last_name,
                     u.last_word_id,
                     u.source_id,
                     u.user_profile_id
                FROM users u 
              WHERE ".$sql_where.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->id;         
      $db_usr = $db_con->get1($sql, $debug-14);  
    }   
    return $db_usr;
  }
  
  // load the missing user parameters from the database
  // private because the loading should be done via the get method
  private function load($debug) {
    $db_usr = $this->load_db($debug-1);
    if (isset($db_usr)) {
      if ($db_usr['user_id'] > 0) {
        $this->id           = $db_usr['user_id'];
        $this->code_id      = $db_usr['code_id'];
        $this->name         = $db_usr['user_name'];
        $this->ip_addr      = $db_usr['ip_address'];
        $this->email        = $db_usr['email'];
        $this->first_name   = $db_usr['first_name'];
        $this->last_name    = $db_usr['last_name'];
        $this->wrd_id       = $db_usr['last_word_id'];
        $this->source_id    = $db_usr['source_id'];
        $this->profile_id   = $db_usr['user_profile_id'];
        $this->dec_point    = DEFAULT_DEC_POINT;
        $this->thousand_sep = DEFAULT_THOUSAND_SEP;
      } 
      zu_debug('user->load ('.$this->name.')', $debug-12);
    }  
  }
  
  // special function to exposed the user loading for simulating test users for the automatic system test
  function load_test_user($debug) {
    return $this->load($debug-1);
  }  
  
  private function ip_in_range($min, $max) {
    return (ip2long($min) <= ip2long($this->ip_addr) && ip2long($this->ip_addr) <= ip2long($max));
  }  
  
  // return the message, why the if is not permitted
  private function ip_check ($debug) {
    zu_debug('user->ip_check ('.$this->ip_addr.')', $debug-10);
    $msg = '';
    $sql = "SELECT ip_from,
                   ip_to,
                   reason
              FROM user_blocked_ips 
             WHERE isactive = 1;";
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    $ip_lst = $db_con->get($sql, $debug-10);  
    foreach ($ip_lst AS $ip_range) {
      zu_debug('user->ip_check range ('.$ip_range['ip_from'].' to '.$ip_range['ip_to'].')', $debug-10);
      if ($this->ip_in_range($ip_range['ip_from'],$ip_range['ip_to'])) {
        zu_debug('user->ip_check blocked ('.$this->ip_addr.')', $debug-10);
        $msg = 'Your IP '.$this->ip_addr.' is blocked at the moment because '.$ip_range['reason'].'. If you think, this should not be the case, please request the unblocking with an email to admin@zukunft.com.';
        $this->id = 0; // switch off the permission
      }
    }
    return $msg;
  }

  // get the ip adress of the active user
  private function get_ip ($debug) {
    $this->ip_addr = $_SERVER['REMOTE_ADDR'];
    return $this->ip_addr;
  }

  // get the active session user object
  function get ($debug) {
    $result = ''; // for the result message e.g. if the user is blocked

    // test first if the IP is blockeed
    if ($this->ip_addr == '') {
      $this->get_ip($debug-1);
    } else {
      zu_debug('user->get ('.$this->ip_addr.')', $debug-10);
    }
    // even if the user has an open session, but the ip is blocked, drop the user
    $result = $this->ip_check($debug-10);

    if ($result == '') {
      // if the user has logged in use the logged in account
      if ($_SESSION['logged']) { 
        $this->id = $_SESSION['usr_id'];
        $this->load($debug-1);
        zu_debug('user->get -> use ('.$this->id.')', $debug-10);
      } else {
        // else use the IP adress (for testing don't overwrite any testing ip)
        $this->get_ip($debug-1);
        $this->load($debug-1);
        if ($this->id <= 0) {
          // use the ip address as the user name and add the user
          $this->name = $this->ip_addr;
          $upd_result .= $this->save($debug-1);
          // adding a new user automatically is normal, so the result does not need to be shown to the user
          if (str_replace ('1','',$upd_result) <> '') {
            $result = $upd_result;
          }  
        }
      }
    }
    zu_debug('user->got "'.$this->name.'" ('.$this->id.')', $debug-10);
    return $result;
  }

  // true if the user has admin rights
  function is_admin ($debug) {
    zu_debug('user->is_admin ('.$this->id.')', $debug-10);
    $result = false;

    if (!isset($this->profile_id)) { $this->load($debug-1); }
    if ($this->profile_id == cl(SQL_USER_ADMIN)) {
      $result = true;
    }  
    return $result;
  }

  // load the last word used by the user
  function last_wrd ($debug) {
    if ($this->wrd_id <= 0) {
      $this->wrd_id = DEFAULT_WORD_ID;
    }
    $wrd = new word_dsp;
    $wrd->id = $this->wrd_id;
    $wrd->usr = $this;
    $wrd->load($debug-1);
    $this->wrd = $wrd;
    return $wrd;
  }
  
  // set the parameters for the virtual user that represents the standard view for all users
  function dummy_all ($debug) {
    $this->id          = 0;
    $this->code_id     = 'all';
    $this->name        = 'standard user view for all users';
  }
  
  // create the display user object based on the object (no needed any more if always the display user object is used)
  function dsp_user ($debug) {
    $dsp_user = New user_dsp;
    $dsp_user->id = $this->id;
    $dsp_user->load($debug-1);
    return $dsp_user;    
  }

  // create the HTML code to display the user name with the HTML link
  function display ($debug) {
    $result = '<a href="/http/user.php?id='.$this->id.'">'.$this->name.'</a>';
    return $result;    
  }

  // remember the last source that the user has used
  function set_source ($source_id, $debug) {
    zu_debug('user->set_source('.$this->id.',s'.$source_id.')', $debug-10);
    $db_con = new mysql;         
    $db_con->usr_id = $this->id;         
    $db_con->type   = 'user';         
    $result .= $db_con->update($this->id, 'source_id', $source_id, $debug-1);
    return $result;
  }

  // set the main log entry parameters for updating one word field
  private function log_upd($debug) {
    zu_debug('user->log_upd user '.$this->name.'.', $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->id;  
    $log->action    = 'update';
    $log->table     = 'users';
    
    return $log;    
  }
  
  // check and update a single user parameter
  private function upd_par ($db_con, $usr_par, $db_row, $fld_name, $par_name, $debug) {
    if ($usr_par[$par_name] <> $db_row[$fld_name]
    AND $usr_par[$par_name] <> "") {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_row[$fld_name];
      $log->new_value = $usr_par[$par_name];
      $log->row_id    = $this->id; 
      $log->field     = $fld_name;
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, $log->field, $log->new_value, $debug-1);
      }    
    }
    return $result;
  }

  // check and update all user parameters
  function upd_pars ($usr_par, $debug) {
    zu_debug('user->upd_pars', $debug-10);
    zu_debug('user->upd_pars(u'.$this->id.',p'.implode(",",$usr_par).')', $debug-10);
    $result = ''; // reset the html code var

    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->id;         
    $db_con->type   = 'user';         
    
    $db_usr = New user;
    $db_usr->id  = $this->id;
    $db_row = $db_usr->load_db($debug-1);
    zu_debug('user->save -> database user loaded "'.$db_row['name'].'".', $debug-14);
    
    $this->upd_par ($db_con, $usr_par, $db_row, "user_name",  'name',  $debug-1);
    $this->upd_par ($db_con, $usr_par, $db_row, "email",      'email', $debug-1);
    $this->upd_par ($db_con, $usr_par, $db_row, "first_name", 'fname', $debug-1);
    $this->upd_par ($db_con, $usr_par, $db_row, "last_name",  'lname', $debug-1);
      
    zu_debug('user->upd_pars -> done.', $debug-1);
    return $result;
  }

  // create a new user or update the existing
  function save ($debug) {
    $result = '';

    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->id;         
    $db_con->type   = 'user';         
    
    if ($this->id <= 0) {
      zu_debug("user->save add (".$this->name.")", $debug-10);

      $this->id = $db_con->insert("user_name", $this->name, $debug-1);
      // log the changes???
      if ($this->id > 0) {
        // add the ip adress to the user
        $result .= $db_con->update($this->id, "ip_address", $this->get_ip($debug-1), $debug-1);
        zu_debug("user->save add ... done.".$result.".", $debug-10);
      } else {
        zu_debug("user->save add ... failed.".$result.".", $debug-10);
      }
    } else {
      // update the ip address and log the changes????
    }
    return $result;    
  }
}

?>
