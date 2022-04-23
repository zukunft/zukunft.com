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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function zuu_ip()
{
    return $_SERVER['REMOTE_ADDR'];
}

function zuu_name()
{
    if ($_SESSION['logged']) {
        $user_name = $_SESSION['user_name'];
    } else {
        // else use the IP adress
        $user_name = zuu_ip();
    }
    return $user_name;
}

function zuu_id()
{
    // if the user has logged in use the logged in account
    if ($_SESSION['logged']) {
        $user_id = $_SESSION['usr_id'];
        log_debug('zuu_id -> use (' . $user_id . ')');
    } else {
        // else use the IP adress
        $ip_address = zuu_ip();
        $user_id = zu_sql_user_id_by_ip($ip_address, 0);
        if ($user_id <= 0) {
            // use the ip address as the user name and add the user
            $user_id = zu_sql_add_user($ip_address, 0);
            // add the ip adress to the user
            zu_sql_update("users", $user_id, "ip_address", $ip_address, $user_id);
        }
    }
    return $user_id;
}

// return the user profile id
function zuu_profile($user_id)
{
    log_debug('zuu_profile(' . $user_id . ')');
    return zu_sql_get_field('user', $user_id, 'user_profile_id');
}

// return a list of all users that have done at least one modification compared to the standard
function zuu_active_lst()
{
    log_debug('zuu_active_lst');

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
    $result = zu_sql_get_lst($sql);

    return $result;
}

// true if the user has admin rights
function zuu_is_admin($user_id)
{
    log_debug('zuu_is_admin (' . $user_id . ')');
    $result = false;

    $user_profile = zuu_profile($user_id);
    if ($user_profile == cl(db_cl::USER_PROFILE, user_profile::ADMIN)) {
        $result = true;
    }
    return $result;
}

// remember the last source that the user has used
function zuu_set_source($user_id, $source_id)
{
    log_debug('zuu_set_source(' . $user_id . ',s' . $source_id . ')');
    $result = zu_sql_update('users', $user_id, 'source_id', $source_id, $user_id);
    return $result;
}

// return the last source that the user has used
function zuu_last_source($user_id)
{
    log_debug('zuu_last_source(' . $user_id . ')');
    return zu_sql_get_field('user', $user_id, 'source_id');
}

// display a form with the user parameters such as name or email
function zuu_dsp_par($user_id)
{
    log_debug('zuu_dsp_par(u' . $user_id . ')');
    $result = ''; // reset the html code var

    $sql = "SELECT user_name, email, first_name, last_name FROM users WHERE user_id = " . $user_id . ";";
    $usr_row = zu_sql_get($sql);
    $result .= ''; // reset the html code var

    // display the user fields using a table and not using px in css to be independent from any screen solution
    $result .= zuh_text_h2('User "' . $usr_row[0] . '"');
    $result .= zuh_form_start("user");
    $result .= '<table>';
    $result .= '<input type="hidden" name="id"    value="' . $user_id . '">';
    $result .= '<tr><td>username  </td><td> <input type="text"   name="name"  value="' . $usr_row[0] . '"></td></tr>';
    $result .= '<tr><td>email     </td><td> <input type="text"   name="email" value="' . $usr_row[1] . '"></td></tr>';
    $result .= '<tr><td>first name</td><td> <input type="text"   name="fname" value="' . $usr_row[2] . '"></td></tr>';
    $result .= '<tr><td>last name </td><td> <input type="text"   name="lname" value="' . $usr_row[3] . '"></td></tr>';
    $result .= '</table>';
    $result .= zuh_form_end();

    log_debug('zuu_dsp_par -> done');
    return $result;
}




// check and update a single user parameter
function zuu_upd_par($user_id, $usr_par, $usr_row, $fld_pos, $fld_name, $par_name): bool
{
    global $db_con;
    $result = true;
    if ($usr_row[$fld_pos] <> $usr_par[$par_name] and $usr_par[$par_name] <> "") {
        if (zu_log($user_id, "update", "users", $fld_name, $usr_row[$fld_pos], $usr_par[$par_name], $user_id) > 0) {
            $result = zu_sql_update("users", $user_id, $fld_name, $db_con->sf($usr_par[$par_name]), $user_id);
        }
    }
    return $result;
}

// check and update all user parameters
function zuu_upd_pars($user_id, $usr_par)
{
    log_debug('zuu_upd_pars(u' . $user_id . ',p' . implode($usr_par) . ')');
    global $debug;
    $result = ''; // reset the html code var

    $sql = "SELECT user_name, email, first_name, last_name FROM users WHERE user_id = " . $user_id . ";";
    $usr_row = zu_sql_get($sql);

    zuu_upd_par($user_id, $usr_par, $usr_row, 0, "user_name", 'name', $debug);
    zuu_upd_par($user_id, $usr_par, $usr_row, 1, "email", 'email');
    zuu_upd_par($user_id, $usr_par, $usr_row, 2, "first_name", 'fname');
    zuu_upd_par($user_id, $usr_par, $usr_row, 3, "last_name", 'lname');

    log_debug('zuu_upd_pars -> done');
    return $result;
}

?>
