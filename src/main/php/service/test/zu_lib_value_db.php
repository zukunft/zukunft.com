<?php

/*

  zu_lib_value_db.php - value related database handling functions
  ----------------

  function prefix: zuv_db_*  - to change the numeric value
  function prefix: zuvt_db_* - to change the link between the numeric value and the word


  db save functions - these functions log the change and perform it in the database
  -------
  
  zuv_db_add - add a value to the database and link the words
  zuv_db_upd - update a value
  zuv_db_del - switch off one value for one user

  
zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/*
  value owner and permission functions
  -----------
*/  

// get the owner of an value; the owner is the user who created the value who first changed the value if the original owner has exclude the value from his profile
function zuv_owner($val_id, $debug) {
  log_debug('zuv_owner (v'.$val_id.')', $debug);
  $user_id = zu_sql_get_value("values", "user_id", "value_id", $val_id, $debug-1);  
  return $user_id;
}

// if the value has been changed by someone else than the owner the user id is returned
// but only return the user id if the user has not also excluded it
function zuv_changer($val_id, $debug) {
  log_debug('zuv_changer (v'.$val_id.')', $debug);
  
  $sql = "SELECT user_id 
            FROM user_values 
           WHERE value_id = ".$val_id."
             AND (excluded <> 1 OR excluded is NULL)";
  $user_id = zu_sql_get1($sql, $debug-1);
  return $user_id;
}

// true if the user is the owner and noone else has changed the value
function zuv_can_change($val_id, $user_id, $debug) {
  log_debug('zuv_can_change (v'.$val_id.',u'.$user_id.')', $debug);
  $can_change = false;
  $val_owner = zuv_owner($val_id, $debug-10);
  if ($val_owner == $user_id OR $val_owner <= 0) {
    $val_user = zuv_changer($val_id, $debug-10);
    if ($val_user == $user_id OR $val_user <= 0) {
      $can_change = true;
    }  
  }  

  log_debug('zuv_can_change -> ('.zu_dsp_bool($can_change).')', $debug);
  return $can_change;
}


/*
  add value and value word links to database functions
  ---------
*/  

// add a value to the database and link the words
// to do: combine the sql statements in one commit
function zuv_db_add($new_value, $wrd_ids, $user_id, $debug) {
  log_debug("zuv_db_add (".$new_value.",t".implode(",",$wrd_ids).",u".$user_id.")", $debug);
  $result = false;

  // log the insert attempt first
  $log_id = zu_log($user_id, "add", "values", "word_value", "", $new_value, 0, $debug-1);
  if ($log_id > 0) {
    // insert the value
    $sql = "INSERT INTO `values` (word_value, user_id, last_update) VALUES ('".$new_value."', ".$user_id.", Now());";
    $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_add", (new Exception)->getTraceAsString(), $debug-1);
    if ($result) {
      // update the reference in the log
      $val_id = mysql_insert_id();
      $result = zu_log_upd($log_id, $val_id, $user_id, $debug-1);
      if ($val_id > 0 and $result) {
        // link the words
        foreach ($wrd_ids as $wrd_id) {
          if ($wrd_id > 0 and $result) {
            $result = zuvt_db_add($val_id, $wrd_id, $user_id, $debug-1);
          }
        }
      }  
    }
  }
  return $val_id;
}

// link an additional word to a value
function zuvt_db_add($val_id, $wrd_id, $user_id, $debug) {
  log_debug("zuvt_db_add (v".$val_id.",t".$wrd_id.",u".$user_id.")", $debug);
  $result = false;

  if (zuv_can_change($val_id, $user_id, $debug-1)) {
    // log the insert attempt first
    $new_text = zut_name($wrd_id, $user_id, $debug-1);
    $log_id = zu_log_link_ref($user_id, "add", "value_phrase_links", 
                              "",  "", "", 
                              $val_id, "", $wrd_id, $val_id, "word", $debug-1);
    if ($log_id > 0) {
      $sql = "INSERT INTO value_phrase_links (value_id, phrase_id) VALUES (".$val_id.", ".$wrd_id.");";
      $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuvt_db_add", (new Exception)->getTraceAsString(), $debug-1);
      if ($result) {
        // update the reference in the log
        $val_wrd_id = mysql_insert_id();
        // next line switched off because the row id should be the ref to the original value
        //$result = zu_log_link_upd($log_id, $val_wrd_id, $user_id, $debug-1);
        // todo: call the word group creation 
      }
    }
  } else {
    // add the link only for this user
  }  
  return $result;
}

/*
  update value and value word links to database functions
  ------------
*/  

// update a value
// todo: if noone else has ever changed the value, change to default value, else create a user overwrite
function zuv_db_upd($val_id, $new_value, $user_id, $debug) {
  log_debug("zuv_db_upd (v".$val_id.",".$new_value.",u".$user_id.")", $debug);
  $result = "";

  // read the database values to be able to check if something has been changed; done first, because it needs to be done for user and general values
  $old_value = zuv_value($val_id, $user_id, $debug-1);
  
  // if the user is the owner and no other user has adjusted the value, really delete the value in the database
  if ($old_value <> $new_value) {
    if (zuv_can_change($val_id, $user_id, $debug-1)) {
      if (zu_log($user_id, "update", "values", "word_value", $old_value, $new_value, $val_id, $debug-1) > 0 ) {
        $result = zu_sql_update("values", $val_id, "word_value", $new_value, $user_id, $debug-1);
        // check if user value can be removed
        $sql = "SELECT value_id FROM `user_values` WHERE value_id = ".$val_id." AND user_id = ".$user_id.";";
        $user_value_id = zu_sql_get($sql, $debug-1);
        if ($user_value_id > 0) {
          $std_value = zuv_value_all($val_id, $debug);
          if ($new_value == $std_value) {
            // remove the user execption
            $sql = "DELETE FROM `user_values` WHERE value_id = ".$val_id." AND  user_id = ".$user_id.";";
            $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_upd", (new Exception)->getTraceAsString(), $debug-1);
          }  
        }
      }
    } else {
      if (zu_log($user_id, "update", "user_values", "word_value", $old_value, $new_value, $val_id, $debug-1) > 0 ) {
        $sql = "SELECT value_id FROM `user_values` WHERE value_id = ".$val_id." AND user_id = ".$user_id.";";
        $user_value_id = zu_sql_get($sql, $debug-1);
        if ($user_value_id <= 0) {
          // create an entry in the user sandbox
          $sql = "INSERT INTO `user_values` (value_id, user_id, user_value, last_update) VALUES (".$val_id.",".$user_id.",".$new_value.", Now());";
          $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_upd", (new Exception)->getTraceAsString(), $debug-1);
        } else {
          $std_value = zuv_value_all($val_id, $debug);
          if ($new_value == $std_value) {
            // remove the user execption
            $sql = "DELETE FROM `user_values` WHERE value_id = ".$val_id." AND  user_id = ".$user_id.";";
            $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_upd", (new Exception)->getTraceAsString(), $debug-1);
          } else {
            $sql = "UPDATE `user_values` 
                       SET user_value = ".$new_value.", 
                           last_update = Now()
                     WHERE value_id = ".$val_id." 
                       AND user_id = ".$user_id.";";
            $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_upd", (new Exception)->getTraceAsString(), $debug-1);
          }
        }
      }  
    }  
  }

  log_debug("zuv_db_upd -> done(".$result.")", $debug-1);
  return $result;
}

// change a link of a word to a value
function zuvt_db_upd($link_id, $val_id, $wrd_new_id, $user_id, $debug) {
  log_debug("zuvt_db_upd (l".$link_id.",v".$val_id.",t".$wrd_new_id.",u".$user_id.")", $debug);

  // to do: move some parts to the calling function
  $wrd_old_id = zu_sql_get1("SELECT phrase_id FROM value_phrase_links WHERE value_phrase_link_id  = ".$link_id.";", $debug-1);
  if (zu_log_link_ref($user_id, "update", "value_phrase_links", 
                      $val_id, 0, $wrd_old_id, 
                      $val_id, 0, $wrd_new_id, $link_id, "word", $debug-1) > 0 ) {
    $sql = "UPDATE value_phrase_links 
               SET phrase_id  = ".$wrd_new_id."  
             WHERE value_id = ".$val_id." 
               AND value_phrase_link_id  = ".$link_id.";";
    $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuvt_db_upd", (new Exception)->getTraceAsString(), $debug-1);

    // check dublicates
    $link_id = zu_sql_get1("SELECT value_phrase_link_id FROM value_phrase_links WHERE value_id  = ".$val_id." AND phrase_id  = ".$wrd_old_id.";", $debug-1);
    if ($link_id > 0) {
      $sql = "DELETE FROM value_phrase_links 
               WHERE value_id = ".$val_id." 
                 AND phrase_id  = ".$wrd_old_id.";";
      $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuvt_db_upd", (new Exception)->getTraceAsString(), $debug-1);
      $link_id = zu_sql_get1("SELECT value_phrase_link_id FROM value_phrase_links WHERE value_id  = ".$val_id." AND phrase_id  = ".$wrd_old_id.";", $debug-1);
      if ($link_id > 0) {
        log_err("Dublicate words (".$wrd_old_id.") for value ".$val_id." found and the automatic removal failed.","zuvt_db_upd");
      } else {  
        log_warning("Dublicate words (".$wrd_old_id.") for value ".$val_id." found, but they have been removed automatically.","zuvt_db_upd", '', (new Exception)->getTraceAsString(), $this->usr);
      }  
    }
    $sql_result = mysql_query($sql);
  }      
  log_debug("zuvt_db_upd ... done", $debug-1);
}

// add the source of a new value
function zuvs_db_add($val_id, $src_id, $user_id, $debug) {
  log_debug("zuvs_db_add (".$val_id.",s".$src_id.",u".$user_id.")", $debug);
  $result = false;
  
  // if the user is the owner and no other user has adjusted the value, really delete the value in the database
  if (zuv_can_change($val_id, $user_id, $debug-1)) {
    if (zu_log_link_ref($user_id, "add", "values", 
                        "", "", "", 
                        $val_id, "", $src_id, $val_id, "source", $debug-1) > 0 ) {
      $result = zu_sql_update("values", $val_id, "source_id", $src_id, $user_id, $debug-1);
    }
  } else {
    //$sql = "SELECT user_value FROM `user_values` WHERE value_id = ".$val_id." AND  user_id = ".$user_id.";";
    //$user_value = zu_sql_get($sql, $debug-1);
  }

  return $result;
}

// update the source of the value
function zuvs_db_upd($val_id, $src_id, $user_id, $debug) {
  log_debug("zuvs_db_upd (".$val_id.",s".$src_id.",u".$user_id.")", $debug);
  $result = false;
  
  // if the user is the owner and no other user has adjusted the value, really delete the value in the database
  $old_id = zuv_source($val_id, $debug-1);
  if (zuv_can_change($val_id, $user_id, $debug-1) OR $old_id <= 0) {
    if ($old_id <> $src_id) {
      $old_text = zus_name($old_id, $debug-1);
      $new_text = zus_name($src_id, $debug-1);
      if (zu_log_link_ref($user_id, "update", "values", 
                          $val_id, "", sf($old_id), 
                          $val_id, "", $src_id, $val_id, "source", $debug-1) > 0 ) {
        $result = zu_sql_update("values", $val_id, "source_id", $src_id, $user_id, $debug-1);
      }
    }
  } else {
    log_err("Changing the source if the user is not permitted is not yet possible.","zuvs_db_upd");
    //$sql = "SELECT user_value FROM `user_values` WHERE value_id = ".$val_id." AND  user_id = ".$user_id.";";
    //$user_value = zu_sql_get($sql, $debug-1);
  }

  return $result;
}

/*
  delete value and value word links to database functions
  ------------
*/  

// check if the user value record is still needed and if not remove it
function zuv_db_usr_check ($val_id, $user_id, $debug) {
  log_debug("zuv_db_usr_check (v".$val_id.",u".$user_id.")", $debug);
  $result = false;

  $sql_std = "SELECT user_value, excluded FROM `values`      WHERE value_id = ".$val_id.";";
  $sql_usr = "SELECT user_value, excluded FROM `user_values` WHERE value_id = ".$val_id." AND user_id = ".$user_id.";";
  $result_std = zu_sql_get($sql, $debug-5);
  $result_usr = zu_sql_get($sql, $debug-5);
  if (($result_std[0] == $result_usr[0] OR $result_usr[0] === NULL)
  AND ($result_std[1] == $result_usr[1] OR $result_usr[1] === NULL)) {
    $sql_del = "DELETE FROM `user_values` WHERE value_id = ".$val_id." AND user_id = ".$user_id.";";
    $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_usr_check", (new Exception)->getTraceAsString(), $debug-1);
  }

  return $result;
}

// switch off one value for one user. if most user want to switch off one value switch it off by default
// no need to unlink the word from the value, because other users can do the with the unlink functions and for this user the value is anyway excluded
function zuv_db_del($val_id, $user_id, $debug) {
  log_debug('zuv_db_del ('.$val_id.',u'.$user_id.')', $debug);

  $result = '';

  $old_value = zuv_value($val_id, $user_id, $debug-1);
  if (zu_log($user_id, "del", "values", "word_value", $old_value, "", $val_id, $debug-1) > 0 ) {
    // check if value is already exluded by default
    $sql = "SELECT excluded FROM `values` WHERE value_id = ".$val_id.";";
    $all_excluded = zu_sql_get($sql, $debug-1);
    // check the user settings for this value
    $sql = "SELECT excluded FROM `user_values` WHERE value_id = ".$val_id." AND  user_id = ".$user_id.";";
    $user_excluded = zu_sql_get($sql, $debug-1);
    if ($all_excluded == 1) {
      if ($user_excluded == 1) {
        // remove the user exclusion, because maybe it is not needed any more
        $sql_result = zuv_db_usr_check ($val_id, $user_id, $debug);
      } else {  
        // this case is not expected if the database is clean
        log_warning("Looks like database cleanup for value ".$val_id." has not yet been done.", "zuv_db_del", '', (new Exception)->getTraceAsString(), $this->usr);
      }
    } else {
      if ($user_excluded == 1) {
        // this case is not expected if the database is clean
        log_warning("Looks like value ".$val_id." has been displayed to the user ".$user_id.", but the user has switched it off already.", "zuv_db_del", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {  
        // if the user is the owner and no other user has adjusted the value, really delete the value in the database
        if (zuv_can_change($val_id, $user_id, $debug-1)) {
          // delete all links (maybe log it ???); the function $vl->del (ex. zuvt_db_del) is not called, because this would check a clash with other values for each word link
          $sql = "DELETE FROM `value_phrase_links` WHERE value_id = ".$val_id.";";
          $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_del", (new Exception)->getTraceAsString(), $debug-1);        
          // delete the value itself
          $sql = "DELETE FROM `values` WHERE value_id = ".$val_id.";";
          $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_del", (new Exception)->getTraceAsString(), $debug-1); 
        } else {
          // add the user exclusion
          $sql = "INSERT INTO `user_values` (value_id, user_id, excluded, last_update) VALUES (".$val_id.", ".$user_id.", 1, NOW());";
          $sql_result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_del", (new Exception)->getTraceAsString(), $debug-1); 
          // request the exclude cleanup for this value because this may take longer due to depending formula results
          $sql_result = zuv_db_usr_check ($val_id, $user_id, $debug);
        }
      }
    }
  }

  return $result;
}

// remove a user adjustment
function zuv_db_user_del($val_id, $user_id, $debug) {
  // remove the user exclusion, because it is not needed
  if ($val_id > 0 AND $user_id > 0) {
    $old_value = zuv_value    ($val_id, $user_id, $debug-1);
    $new_value = zuv_value_all($val_id,           $debug-1);
    if (zu_log($user_id, "update", "values", "word_value", $old_value, $new_value, $val_id, $debug-1) > 0 ) {
      $sql = "DELETE FROM `user_values` WHERE value_id = ".$val_id." AND  user_id = ".$user_id.";";
      $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuv_db_user_del", (new Exception)->getTraceAsString(), $debug-1);
    }  
  }

  return $result;
}

/*
// change a link of a word to a value
function zuvt_db_del($val_id, $wrd_id, $user_id, $debug) {
  zu_debug("zuvt_db_del (v".$val_id.",t".$wrd_id.",u".$user_id.")", $debug);   
  $result = false;

  if (zuv_can_change($val_id, $user_id, $debug-1)) {
    // log the insert attempt first
    $log_id = zu_log_link_ref($user_id, "del", "value_phrase_links", 
                              $val_id, "", $wrd_id, 
                              "",  "", "", $val_id, "word", $debug-1);
    if ($log_id > 0) {
      $sql = "DELETE FROM `value_phrase_links` WHERE value_id = ".$val_id." AND word_id = ".$wrd_id.";";
      $result = zu_sql_exe($sql, $user_id, DBL_SYSLOG_ERROR, "zuvt_db_del", $debug-1);
      if ($result) {
        // todo: call the word group creation 
      }
    }  
  } else {
    // check if removing a word link is matching another value
    // if yes merge value with this value
    // if no create a new value
  }

  zu_debug("zuvt_db_del -> done", $debug-1);
}
*/
?>
