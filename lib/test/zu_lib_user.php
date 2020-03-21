<?php

/*

  zu_lib_user.php - user and logging functions
  ---------------

  prefix: zuu_* 


  get functions
  ---
  
  zuu_ip              - get ip address of the user
  zuu_name            - either the user name or the IP if the user is not logged in
  zuu_id              - database id of the current user
  zuu_profile         - the user profile id


  info functions
  ----
  
  zuu_is_admin        - true if the user has admin rights


  config functions
  ------
  
  zuu_set_source      - remember the last source that the user has used
  zuu_last_source     - recall the last source that the user has used


  display functions
  -------
  
  zuu_dsp_par         - form with the user parameters such as name or email
  zuu_dsp_sandbox_wrd - show word changes by the user which are not (yet) standard 
  zuu_dsp_sandbox_frm - same as zuu_dsp_sandbox_wrd, but for formulas instead of words
  zuu_dsp_sandbox_val - same as zuu_dsp_sandbox_wrd, but for values instead of words
  zuu_dsp_sandbox     - combination of the three functions above
  zuu_dsp_changes     - the latest changes by the user
  zuu_dsp_errors      - errors that are related to the user, so that he can track when they are closed
  

  display functions
  -------
  
  zuu_upd_par         - update a single user parameter
  zuu_upd_pars        - update all user parameters


zukunft.com - calc with words

copyright 1995-2020 by zukunft.com AG, Zurich

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

function zuu_ip () {
  return $_SERVER['REMOTE_ADDR'];
}

function zuu_name () {
  if ($_SESSION['logged']) { 
    $user_name = $_SESSION['user_name'];
  } else {
    // else use the IP adress
    $user_name = zuu_ip();
  }  
  return $user_name;
}

function zuu_id ($debug) {
  // if the user has logged in use the logged in account
  if ($_SESSION['logged']) { 
    $user_id = $_SESSION['usr_id'];
    zu_debug('zuu_id -> use ('.$user_id.')', $debug);
  } else {
    // else use the IP adress
    $ip_address = zuu_ip();
    $user_id = zu_sql_user_id_by_ip($ip_address, 0);
    if ($user_id <= 0) {
      // use the ip address as the user name and add the user
      $user_id = zu_sql_add_user($ip_address, 0);
      // add the ip adress to the user
      zu_sql_update("users", $user_id, "ip_address", $ip_address, $user_id, $debug);
    }
  }
  return $user_id;
}

// return the user profile id
function zuu_profile ($user_id, $debug) {
  zu_debug('zuu_profile('.$user_id.')', $debug);
  return zu_sql_get_field ('user', $user_id, 'user_profile_id', $debug-1);
}

// return a list of all users that have done at least one modification compared to the standard
function zuu_active_lst ($debug) {
  zu_debug('zuu_active_lst', $debug);
  
  $sql = "SELECT u.user_id, u.user_name 
            FROM users u,
                 ( SELECT user_id 
                    FROM ( SELECT user_id 
                             FROM user_formulas
                         GROUP BY user_id 
                     UNION SELECT user_id 
                             FROM user_words
                         GROUP BY user_id 
                     UNION SELECT user_id 
                             FROM user_values
                         GROUP BY user_id ) AS cp
                GROUP BY user_id ) AS c
           WHERE u.user_id = c.user_id;";
  $result = zu_sql_get_lst($sql, $debug-1);         

  return $result;
}

// true if the user has admin rights
function zuu_is_admin ($user_id, $debug) {
  zu_debug('zuu_is_admin ('.$user_id.')', $debug);
  $result = false;

  $user_profile = zuu_profile($user_id, $debug-1);
  if ($user_profile == cl(SQL_USER_ADMIN)) {
    $result = true;
  }  
  return $result;
}

// remember the last source that the user has used
function zuu_set_source ($user_id, $source_id, $debug) {
  zu_debug('zuu_set_source('.$user_id.',s'.$source_id.')', $debug);
  $result = zu_sql_update('users', $user_id, 'source_id', $source_id, $user_id, $debug);
  return $result;
}

// return the last source that the user has used
function zuu_last_source ($user_id, $debug) {
  zu_debug('zuu_last_source('.$user_id.')', $debug);
  return zu_sql_get_field ('user', $user_id, 'source_id', $debug-1);
}

// display a form with the user parameters such as name or email
function zuu_dsp_par ($user_id, $debug) {
  zu_debug('zuu_dsp_par(u'.$user_id.')', $debug);
  $result = ''; // reset the html code var

  $sql = "SELECT user_name, email, first_name, last_name FROM users WHERE user_id = ".$user_id.";";
  $usr_row = zu_sql_get($sql, $debug-1);
  $result .= ''; // reset the html code var
  
  // display the user fields using a table and not using px in css to be independend from any screen solution
  $result .= zuh_text_h2('User "'.$usr_row[0].'"');
  $result .= zuh_form_start("user");
  $result .= '<table>';
  $result .=                             '<input type="hidden" name="id"    value="'.$user_id.'">';
  $result .= '<tr><td>username  </td><td> <input type="text"   name="name"  value="'.$usr_row[0].'"></td></tr>';
  $result .= '<tr><td>email     </td><td> <input type="text"   name="email" value="'.$usr_row[1].'"></td></tr>';
  $result .= '<tr><td>first name</td><td> <input type="text"   name="fname" value="'.$usr_row[2].'"></td></tr>';
  $result .= '<tr><td>last name </td><td> <input type="text"   name="lname" value="'.$usr_row[3].'"></td></tr>';
  $result .= '</table>';
  $result .= zuh_form_end();
  
  zu_debug('zuu_dsp_par -> done.', $debug-1);
  return $result;
}

// display word changes by the user which are not (yet) standard 
function zuu_dsp_sandbox_wrd ($user_id, $back_link, $debug) {
  zu_debug('zuu_dsp_sandbox_wrd(u'.$user_id.')', $debug);
  $result = ''; // reset the html code var

  // get word changes by the user that are not standard
  $sql = "SELECT u.word_name AS usr_word_name, 
                 t.word_name, 
                 t.word_id 
            FROM user_words u,
                 words t
           WHERE u.user_id = ".$user_id."
             AND u.word_id = t.word_id;";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // prepare to show the word link
  $row_nbr = 0;
  $result .= '<table>';
  while ($wrd_row = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    $row_nbr++;
    $result .= '<tr>';
    if ($row_nbr == 1) {
      $result .= '<th>Your name vs. </th><th>common name</th></tr><tr>';
    }
    $result .= '<td>'.$wrd_row[0].'</td><td>'.$wrd_row[1].'</td>';
    //$result .= '<td><a href="/http/user.php?id='.$user_id.'&undo_word='.$wrd_row[2].'&back='.$id.'"><img src="/images/button_del_small.jpg" alt="undo change"></a></td>';
    $url = "/http/user.php?id='.$user_id.'&undo_word='.$wrd_row[2].'&back='.$id.'";
    $result .= '<td>'.zuh_btn_del("Undo your change and use the standard word ".$wrd_row[1], $url).'</td>';
    $result .= '</tr>';
  }
  $result .= '</table>';
  
  zu_debug('zuu_dsp_sandbox_wrd -> done.', $debug-1);
  return $result;
}

// display formula changes by the user which are not (yet) standard 
function zuu_dsp_sandbox_frm ($user_id, $back_link, $debug) {
  zu_debug('zuu_dsp_sandbox_frm(u'.$user_id.')', $debug);
  $result = ''; // reset the html code var

  // get word changes by the user that are not standard
  $sql = "SELECT u.formula_name, 
                 u.resolved_text AS usr_formula_text, 
                 f.resolved_text AS formula_text, 
                 f.formula_id 
            FROM user_formulas u,
                 formulas f
           WHERE u.user_id = ".$user_id."
             AND u.formula_id = f.formula_id;";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // prepare to show the word link
  $row_nbr = 0;
  $result .= '<table>';
  while ($wrd_row = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    $row_nbr++;
    $result .= '<tr>';
    if ($row_nbr == 1) {
      $result .= '<th>Formula name </th>';
      $result .= '<th>Your formula vs. </th>';
      $result .= '<th>common formula</th>';
      $result .= '</tr><tr>';
    }
    $result .= '<td>'.$wrd_row[0].'</td>';
    $result .= '<td>'.$wrd_row[1].'</td>';
    $result .= '<td>'.$wrd_row[2].'</td>';
    //$result .= '<td><a href="/http/user.php?id='.$user_id.'&undo_formula='.$wrd_row[3].'&back='.$id.'"><img src="/images/button_del_small.jpg" alt="undo change"></a></td>';
    $url = "/http/user.php?id='.$user_id.'&undo_formula='.$wrd_row[3].'&back='.$id.'";
    $result .= '<td>'.zuh_btn_del("Undo your change and use the standard formula ".$wrd_row[2], $url).'</td>';
    $result .= '</tr>';
  }
  $result .= '</table>';
  
  zu_debug('zuu_dsp_sandbox_frm -> done.', $debug-1);
  return $result;
}

// display value changes by the user which are not (yet) standard 
function zuu_dsp_sandbox_val ($user_id, $back_link, $debug) {
  zu_debug('zuu_dsp_sandbox_val(u'.$user_id.')', $debug);
  $result = ''; // reset the html code var

  // get value changes by the user that are not standard
  $sql = "SELECT u.user_value, 
                 v.word_value, 
                 v.value_id, 
                 u.excluded
            FROM user_values u,
                 `values` v
           WHERE u.user_id = ".$user_id."
             AND u.value_id = v.value_id;";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // prepare to show where the user uses different value than a normal viewer
  $row_nbr = 0;
  $result .= '<table>';
  while ($wrd_row = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    $row_nbr++;
    if ($wrd_row[3] == 1) {
      $usr_val = "deleted";
    } else {
      $usr_val = $wrd_row[0];
    }
    $result .= '<tr>';
    if ($row_nbr == 1) {
      $result .= '<th>Your value vs. </th><th>common value</th><th>other user values</th><th></th><th>for</th></tr><tr>';
    }
    $result .= '<td><a href="/http/value_edit.php?id='.$wrd_row[2].'&back='.$back_link.'">'.$usr_val.'</a></td><td>'.$wrd_row[1].'</td>';
    $url     = '/http/user.php?id='.$user_id.'&undo_value='.$wrd_row[2].'&back='.$back_link;
    $result .= '<td>';
    $sql_usr_val = "SELECT u.user_id, 
                           u.user_value
                      FROM user_values u
                     WHERE u.user_id <> ".$user_id."
                       AND u.value_id = ".$wrd_row[2]."
                       AND (u.excluded <> 1 OR u.excluded is NULL);";
    $usr_val_lst =zu_sql_get_lst($sql_usr_val, $debug-1);
    foreach (array_keys($usr_val_lst) AS $usr_val_id) {
      $result .= '<a href="/http/user_value.php?id='.$usr_val_id.'&back='.$back_link.'">'.$usr_val_lst[$usr_val_id].'</a> ';
    }
    $result .= '</td>';
    $result .= '<td>'.zuh_btn_del("Undo your change and use the standard value ".$wrd_row[1], $url).'</td>';
    $wrd_lst = zuv_wrd_lst($wrd_row[2], $user_id, $debug-1);
    $result .= '<td>'.zut_dsp_lst_txt($wrd_lst).'</td>';
    $result .= '</tr>';
  }
  $result .= '</table>';
  
  zu_debug('zuu_dsp_sandbox_val -> done.', $debug-1);
  return $result;
}

// display changes by the user which are not (yet) standard 
function zuu_dsp_sandbox ($user_id, $back_link, $debug) {
  zu_debug('zuu_dsp_sandbox(u'.$user_id.',b'.$back_link.')', $debug);
  $result  = zuu_dsp_sandbox_val ($user_id, $back_link, $debug); 
  $result .= zuu_dsp_sandbox_frm ($user_id, $back_link, $debug); 
  $result .= zuu_dsp_sandbox_wrd ($user_id, $back_link, $debug); 
  return $result;
}

// display the latest changes by the user
function zuu_dsp_changes ($user_id, $back_link, $debug) {
  zu_debug('zuu_dsp_changes (u'.$user_id.',b'.$back_link.')', $debug);
  $result = ''; // reset the html code var

  // get value changes by the user that are not standard
  //$sql = "SELECT TOP 20 FROM 
  $sql = "SELECT c.change_time, 
                 a.change_action_name AS type, 
                 t.description AS type_table, 
                 f.description AS type_field, 
                 f.code_id, 
                 c.row_id, 
                 c.old_value AS old, 
                 c.new_value AS new
            FROM changes c,
                 change_actions a,
                 change_fields f,
                 change_tables t
           WHERE c.user_id = ".$user_id."
             AND c.change_action_id = a.change_action_id 
             AND c.change_field_id  = f.change_field_id 
             AND f.table_id  = t.change_table_id
        ORDER BY c.change_time DESC
           LIMIT 20;";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  // prepare to show where the user uses different value than a normal viewer
  $row_nbr = 0;
  $result .= '<table>';
  while ($wrd_row = mysql_fetch_array($sql_result, MYSQL_NUM)) {
    $row_nbr++;
    $result .= '<tr>';
    if ($row_nbr == 1) {
      $result .= '<th>time</th><th>action</th><th>from</th><th>to</th>';
    }
    $result .= '</tr>';
    $result .= '<tr>';
    $result .= '<td>'.$wrd_row[0].'</td>';
    if ($wrd_row[4] == "value") {
      if ($wrd_row[5] > 0) {
        $wrd_lst = zuv_wrd_lst($wrd_row[5], $user_id, $debug-1);
        $result .= '<td>'.$wrd_row[1].' '.zut_dsp_lst_txt($wrd_lst).'</td>';
      } else {
        $result .= '<td>'.$wrd_row[1].' number</td>';
      }
    } else {
      if ($wrd_row[3] <> "") {
        $result .= '<td>'.$wrd_row[1].' '.$wrd_row[3].'</td>';
      } else {
        $result .= '<td>'.$wrd_row[1].' '.$wrd_row[2].'</td>';
      }
    }
    $result .= '<td>'.$wrd_row[6].'</td>';
    $result .= '<td>'.$wrd_row[7].'</td>';
    $result .= '</tr>';
  }
  $result .= '</table>';
  
  zu_debug('zuu_dsp_changes -> done.', $debug-1);
  return $result;
}

// display the error that are related to the user, so that he can track when they are closed
// or display the error that are related to the user, so that he can track when they are closed
function zuu_dsp_errors ($user_id, $user_profile, $dsp_type, $back, $debug) {
  zu_debug('zuu_dsp_errors(u'.$user_id.')', $debug);
  $result = ''; // reset the html code var
  
  // set the filter for the requested display type
  if ($dsp_type == "all") {
    $user_sql = "";
  } else {
    if ($dsp_type == "other") {
      $user_sql = " (l.user_id <> ".$user_id." OR l.user_id IS NULL) AND ";
    } else {
      $user_sql = " (l.user_id = ".$user_id." OR l.user_id IS NULL) AND ";
    }  
  }

  // get word changes by the user that are not standard
  $sql = "SELECT l.sys_log_id, 
                 l.sys_log_time, 
                 l.sys_log_text, 
                 l.sys_log_trace, 
                 l.sys_log_function_id,
                 f.sys_log_function_name,
                 l.user_id,
                 u.user_name,
                 l.solver_id,
                 a.user_name AS solver_name,
                 l.sys_log_status_id,
                 s.sys_log_status_name
            FROM sys_log l 
       LEFT JOIN sys_log_status s    ON l.sys_log_status_id   = s.sys_log_status_id
       LEFT JOIN users u             ON l.user_id             = u.user_id
       LEFT JOIN users a             ON l.solver_id           = a.user_id
       LEFT JOIN sys_log_functions f ON l.sys_log_function_id = f.sys_log_function_id
           WHERE ".$user_sql." 
                (l.sys_log_status_id <> ".cl(DBL_SYSLOG_STATUS_CLOSE)." OR l.sys_log_status_id IS NULL);";
  $sql_result =zu_sql_get_all($sql, $debug-1);

  if (mysql_num_rows($sql_result) > 0) {
    // prepare to show the word link
    $result .= '<table>';
    $row_nbr = 0;
    while ($wrd_row = mysql_fetch_array($sql_result, MYSQL_ASSOC)) {
      $row_nbr++;
      $result .= '<tr>';
      if ($row_nbr == 1) {
        $result .= '<th> creation time     </th>';
        $result .= '<th> user              </th>';
        $result .= '<th> issue description </th>';
        $result .= '<th> trace             </th>';
        $result .= '<th> program part      </th>';
        $result .= '<th> owner             </th>';
        $result .= '<th> status            </th>';
      }
      $result .= '</tr><tr>';
      $result .= '<td>'.$wrd_row["sys_log_time"]         .'</td>';
      $result .= '<td>'.$wrd_row["user_name"]            .'</td>';
      $result .= '<td>'.$wrd_row["sys_log_text"]         .'</td>';
      $result .= '<td>'.$wrd_row["sys_log_trace"]        .'</td>';
      $result .= '<td>'.$wrd_row["sys_log_function_name"].'</td>';
      $result .= '<td>'.$wrd_row["solver_name"]          .'</td>';
      $result .= '<td>'.$wrd_row["sys_log_status_name"]  .'</td>';
      if ($user_profile == cl(SQL_USER_ADMIN)) {
        $result .= '<td><a href="/http/error_update.php?id='.$wrd_row["sys_log_id"].'&status='.cl(DBL_ERR_CLOSED).'&back='.$back.'">close</a></td>';
      }  

      //$result .= '<td><a href="/http/user.php?id='.$user_id.'&undo_word='.$wrd_row[2].'&back='.$id.'"><img src="/images/button_del_small.jpg" alt="undo change"></a></td>';
      $result .= '</tr>';
    }
    $result .= '</table>';
  }
  
  zu_debug('zuu_dsp_errors -> done.', $debug-1);
  return $result;
}

// check and update a single user parameter
function zuu_upd_par ($user_id, $usr_par, $usr_row, $fld_pos, $fld_name, $par_name, $debug) {
  if ($usr_row[$fld_pos] <> $usr_par[$par_name] AND $usr_par[$par_name] <> "") {
    if (zu_log($user_id, "update", "users", $fld_name, $usr_row[$fld_pos], $usr_par[$par_name], $user_id, $debug-1) > 0 ) {
      $result = zu_sql_update("users", $user_id, $fld_name, sf($usr_par[$par_name]), $user_id, $debug-1);
    }    
  }
  return $result;
}

// check and update all user parameters
function zuu_upd_pars ($user_id, $usr_par, $debug) {
  zu_debug('zuu_upd_pars(u'.$user_id.',p'.implode($usr_par).')', $debug);
  $result = ''; // reset the html code var

  $sql = "SELECT user_name, email, first_name, last_name FROM users WHERE user_id = ".$user_id.";";
  $usr_row = zu_sql_get($sql, $debug-1);
  
  zuu_upd_par ($user_id, $usr_par, $usr_row, 0, "user_name",  'name',  $debug);
  zuu_upd_par ($user_id, $usr_par, $usr_row, 1, "email",      'email', $debug);
  zuu_upd_par ($user_id, $usr_par, $usr_row, 2, "first_name", 'fname', $debug);
  zuu_upd_par ($user_id, $usr_par, $usr_row, 3, "last_name",  'lname', $debug);
    
  zu_debug('zuu_upd_pars -> done.', $debug-1);
  return $result;
}
?>
