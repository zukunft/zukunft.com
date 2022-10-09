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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

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


