<?php

/*

  system_log_list.php - a list of system error objects
  -------------------
  
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


class system_error_log_list {

  public $lst         = NULL;  // a list of system error objects
  public $usr         = NULL;  // the user who wants to see the errors
  public $dsp_type    = '';  // 
  public $back        = '';  // 
  
  // display the error that are related to the user, so that he can track when they are closed
  // or display the error that are related to the user, so that he can track when they are closed
  // called also from user_display.php/dsp_errors
  function display ($debug) {
    zu_debug('system_error_log_list->display for user "'.$this->usr->name.'".', $debug-10);
    $result = ''; // reset the html code var
    
    // set the filter for the requested display type
    if ($this->dsp_type == "all") {
      $user_sql = "";
    } else {
      if ($this->dsp_type == "other") {
        $user_sql = " (l.user_id <> ".$this->usr->id." OR l.user_id IS NULL) AND ";
      } else {
        $user_sql = " (l.user_id = ".$this->usr->id." OR l.user_id IS NULL) AND ";
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
    $db_con = New mysql;
    $db_con->usr_id = $this->id;         
    $db_lst = $db_con->get($sql, $debug-5);  

    if (count($db_lst) > 0) {
      zu_debug('system_error_log_list->display -> '.count($db_lst).' rows.', $debug-1);
      // prepare to show the word link
      $db_row = $db_lst[0];
      if ($db_row["sys_log_time"] <> '') {
        $result .= '<table style="width:100%">';
        $row_nbr = 0;
        foreach ($db_lst AS $db_row) {
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
          $result .= '<td>'.$db_row["sys_log_time"]         .'</td>';
          $result .= '<td>'.$db_row["user_name"]            .'</td>';
          $result .= '<td>'.$db_row["sys_log_text"]         .'</td>';
          $result .= '<td>'.$db_row["sys_log_trace"]        .'</td>';
          $result .= '<td>'.$db_row["sys_log_function_name"].'</td>';
          $result .= '<td>'.$db_row["solver_name"]          .'</td>';
          $result .= '<td>'.$db_row["sys_log_status_name"]  .'</td>';
          if ($this->usr->profile_id == cl(SQL_USER_ADMIN)) {
            $result .= '<td><a href="/http/error_update.php?id='.$db_row["sys_log_id"].'&status='.cl(DBL_ERR_CLOSED).'&back='.$this->back.'">close</a></td>';
          }  

          $result .= '</tr>';
        }
        $result .= '</table>';
      }
    }
    
    zu_debug('system_error_log_list->display -> done.', $debug-1);
    return $result;
  }
    
}

?>
