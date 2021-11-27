<?php

/*

  test/unit/value.php - unit testing of the VALUE functions
  -------------------
  

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

function run_system_unit_tests(testing $t)
{

    global $usr;
    global $sql_names;

    $db_con = new sql_db();

    $t->header('Unit tests of the system classes (src/main/php/model/system/ip_range.php)');

    $t->subheader('IP filter tests');

    /*
     * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
     */

    $ip_range = new ip_range();

    // sql to load by id
    $db_con->db_type = sql_db::POSTGRES;
    $ip_range->id = 1;
    $ip_range->usr = $usr;
    $created_sql = $ip_range->load_sql($db_con);
    $expected_sql = "SELECT 
                            user_blocked_id,  
                            ip_from,  
                            ip_to,  
                            reason,  
                            is_active 
                       FROM user_blocked_ips 
                      WHERE user_blocked_id = 1;";
    $t->dsp('ip_range->load_sql by id', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $ip_range->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('ip_range->load_sql by id', $result, $target);

    // ... and the same for MySQL by replication the SQL builder statements
    $db_con->db_type = sql_db::MYSQL;
    $created_sql = $ip_range->load_sql($db_con);
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " 
                            user_blocked_id,  
                            ip_from,  
                            ip_to,  
                            reason,  
                            is_active 
                       FROM user_blocked_ips 
                      WHERE user_blocked_id = 1;";
    $t->dsp('ip_range->load_sql by id for MySQL', zu_trim($expected_sql), zu_trim($created_sql));

    // sql to load by ip range
    $db_con->db_type = sql_db::POSTGRES;
    $ip_range->reset();
    $ip_range->from = '66.249.64.95';
    $ip_range->to = '66.249.64.95';
    $ip_range->usr = $usr;
    $created_sql = $ip_range->load_sql($db_con);
    $expected_sql = "SELECT 
                            user_blocked_id,  
                            ip_from,  
                            ip_to,  
                            reason,  
                            is_active 
                       FROM user_blocked_ips 
                      WHERE ip_from = '66.249.64.95' and ip_to = '66.249.64.95';";
    $t->dsp('ip_range->load_sql by ip range', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $ip_range->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('ip_range->load_sql by id range', $result, $target);

    // ... and the same for MySQL by replication the SQL builder statements
    $db_con->db_type = sql_db::MYSQL;
    $created_sql = $ip_range->load_sql($db_con);
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " 
                            user_blocked_id,  
                            ip_from,  
                            ip_to,  
                            reason,  
                            is_active 
                       FROM user_blocked_ips 
                      WHERE ip_from = '66.249.64.95' and ip_to = '66.249.64.95';";
    $t->dsp('ip_range->load_sql by id for MySQL', zu_trim($expected_sql), zu_trim($created_sql));

    /*
     * im- and export tests
     */

    $t->subheader('Im- and Export tests');

    $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/system/ip_blacklist.json'), true);
    $ip_range = new ip_range();
    $ip_range->usr = $usr;
    $ip_range->import_obj($json_in, false);
    $json_ex = json_decode(json_encode($ip_range->export_obj()), true);
    $result = json_is_similar($json_in, $json_ex);
    $target = true;
    $t->dsp('ip_range->import check', $target, $result);

    /*
     * system log SQL creation tests
     */

    $t->subheader('System log tests');

    $log = new system_error_log();

    // sql to load by id
    $db_con->db_type = sql_db::POSTGRES;
    $log->id = 1;
    $created_sql = $log->load_sql($db_con);
    $expected_sql = "SELECT s.sys_log_id,
                     s.user_id,
                     s.solver_id,
                     s.sys_log_time,
                     s.sys_log_type_id,
                     s.sys_log_function_id,
                     s.sys_log_text,
                     s.sys_log_trace,
                     s.sys_log_status_id,
                     l.sys_log_function_name,
                     l2.type_name
                FROM sys_log s
           LEFT JOIN sys_log_functions l ON s.sys_log_function_id = l.sys_log_function_id
           LEFT JOIN sys_log_status l2    ON s.sys_log_status_id  = l2.sys_log_status_id
              WHERE s.sys_log_id = 1;";
    $t->dsp('system_error_log->load_sql by id', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $log->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('system_error_log->load_sql by id', $result, $target);

    // ... and the same for MySQL by replication the SQL builder statements
    $db_con->db_type = sql_db::MYSQL;
    $created_sql = $log->load_sql($db_con);
    $expected_sql = "SELECT s.sys_log_id,
                     s.user_id,
                     s.solver_id,
                     s.sys_log_time,
                     s.sys_log_type_id,
                     s.sys_log_function_id,
                     s.sys_log_text,
                     s.sys_log_trace,
                     s.sys_log_status_id,
                     l.sys_log_function_name,
                     l2.type_name
                FROM sys_log s
           LEFT JOIN sys_log_functions l ON s.sys_log_function_id = l.sys_log_function_id
           LEFT JOIN sys_log_status l2    ON s.sys_log_status_id  = l2.sys_log_status_id
              WHERE s.sys_log_id = 1;";
    $t->dsp('system_error_log->load_sql by id for MySQL', zu_trim($expected_sql), zu_trim($created_sql));

    $t->subheader('System log list tests');

    $log_lst = new system_error_log_list();

    // sql to load all
    $db_con->db_type = sql_db::POSTGRES;
    $log_lst->dsp_type = system_error_log_list::DSP_ALL;
    $created_sql = $log_lst->load_sql($db_con);
    $expected_sql = "SELECT 
                        s.sys_log_id, 
                        s.user_id,
                        s.solver_id,
                        s.sys_log_time, 
                        s.sys_log_type_id, 
                        s.sys_log_function_id,
                        s.sys_log_text, 
                        s.sys_log_trace, 
                        s.sys_log_status_id,
                        l.sys_log_function_name,
                        l2.type_name,
                        l3.user_name,
                        l4.user_name AS solver_name
                   FROM sys_log s 
              LEFT JOIN sys_log_functions l ON s.sys_log_function_id = l.sys_log_function_id
              LEFT JOIN sys_log_status l2   ON s.sys_log_status_id   = l2.sys_log_status_id
              LEFT JOIN users l3            ON s.user_id             = l3.user_id
              LEFT JOIN users l4            ON s.solver_id           = l4.user_id
                  WHERE (s.sys_log_status_id <> 3 OR s.sys_log_status_id IS NULL)
               ORDER BY s.sys_log_time DESC
                  LIMIT 20;";
    $t->dsp('system_error_log_list->load_sql by id', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $log_lst->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('system_error_log_list->load_sql all', $result, $target);

    // ... and the same for MySQL by replication the SQL builder statements
    $db_con->db_type = sql_db::MYSQL;
    $created_sql = $log_lst->load_sql($db_con);
    $expected_sql = "SELECT 
                        s.sys_log_id, 
                        s.user_id,
                        s.solver_id,
                        s.sys_log_time, 
                        s.sys_log_type_id, 
                        s.sys_log_function_id,
                        s.sys_log_text, 
                        s.sys_log_trace, 
                        s.sys_log_status_id,
                        l.sys_log_function_name,
                        l2.type_name,
                        l3.user_name,
                        l4.user_name AS solver_name
                   FROM sys_log s 
              LEFT JOIN sys_log_functions l ON s.sys_log_function_id = l.sys_log_function_id
              LEFT JOIN sys_log_status l2   ON s.sys_log_status_id   = l2.sys_log_status_id
              LEFT JOIN users l3            ON s.user_id             = l3.user_id
              LEFT JOIN users l4            ON s.solver_id           = l4.user_id
                  WHERE (s.sys_log_status_id <> 3 OR s.sys_log_status_id IS NULL)
               ORDER BY s.sys_log_time DESC
                  LIMIT 20;";
    $t->dsp('system_error_log_list->load_sql by id for MySQL', zu_trim($expected_sql), zu_trim($created_sql));

}

