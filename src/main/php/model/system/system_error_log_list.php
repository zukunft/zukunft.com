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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/


class system_error_log_list
{

    public ?array $lst = null;      // a list of system error objects
    public ?user $usr = null;       // the user who wants to see the errors
    public ?string $dsp_type = '';  //
    public ?int $page = null;       //
    public ?int $size = null;       //
    public ?string $back = '';      //

    // display the error that are related to the user, so that he can track when they are closed
    // or display the error that are related to the user, so that he can track when they are closed
    // called also from user_display.php/dsp_errors
    function display(): string
    {
        log_debug('system_error_log_list->display for user "' . $this->usr->name . '"');

        global $db_con;
        $result = ''; // reset the html code var

        // set default values
        if (!isset($this->size)) {
            $this->size = SQL_ROW_LIMIT;
        } else {
            if ($this->size <= 0) {
                $this->size = SQL_ROW_LIMIT;
            }
        }

        // set the filter for the requested display type
        if ($this->dsp_type == "all") {
            $user_sql = "";
        } else {
            if ($this->dsp_type == "other") {
                $user_sql = " (l.user_id <> " . $this->usr->id . " OR l.user_id IS NULL) AND ";
            } else {
                $user_sql = " (l.user_id = " . $this->usr->id . " OR l.user_id IS NULL) AND ";
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
             WHERE " . $user_sql . " 
                  (l.sys_log_status_id <> " . cl(db_cl::LOG_STATUS, sys_log_status::CLOSED) . " OR l.sys_log_status_id IS NULL)
          ORDER BY l.sys_log_time DESC
             LIMIT " . $this->size . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $db_lst = $db_con->get($sql);

        if (count($db_lst) > 0) {
            log_debug('system_error_log_list->display -> ' . count($db_lst) . ' rows');
            // prepare to show the word link
            $db_row = $db_lst[0];
            if ($db_row["sys_log_time"] <> '') {
                $result .= dsp_tbl_start();
                $row_nbr = 0;
                foreach ($db_lst as $db_row) {
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
                    $result .= '<td>' . $db_row["sys_log_time"] . '</td>';
                    $result .= '<td>' . $db_row["user_name"] . '</td>';
                    $result .= '<td>' . $db_row["sys_log_text"] . '</td>';
                    $result .= '<td>' . $db_row["sys_log_trace"] . '</td>';
                    $result .= '<td>' . $db_row["sys_log_function_name"] . '</td>';
                    $result .= '<td>' . $db_row["solver_name"] . '</td>';
                    $result .= '<td>' . $db_row["sys_log_status_name"] . '</td>';
                    if ($this->usr->profile_id == cl(db_cl::USER_PROFILE, user_profile::ADMIN)) {
                        $result .= '<td><a href="/http/error_update.php?id=' . $db_row["sys_log_id"] . '&status=' . cl(db_cl::LOG_STATUS, sys_log_status::CLOSED) . '&back=' . $this->back . '">close</a></td>';
                    }

                    $result .= '</tr>';
                }
                $result .= dsp_tbl_end();
            }
        }

        log_debug('system_error_log_list->display -> done');
        return $result;
    }

}