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

    $t->header('Unit tests of the system classes (src/main/php/model/system/ip_range.php)');

    /*
     * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
     */

    $db_con = new sql_db();
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

}

