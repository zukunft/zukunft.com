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

function run_value_unit_tests()
{

    global $usr;
    global $sql_names;

    test_header('Unit tests of the value class (src/main/php/model/value/value.php)');

    /*
     * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
     */

    $db_con = new sql_db();

    // sql to load by word list by ids
    $val = new value;
    $val->phr_lst = test_unit_create_phrase_list();
    $val->time_id = 4;
    $val->usr = $usr;
    $created_sql = $val->load_sql();
    $expected_sql = "SELECT value_id 
                            FROM values
                          WHERE phrase_group_id IN (1) ;";
    test_dsp('value->load_sql by group and time', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $val->load_sql(true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    test_dsp('value->load_sql by group and time', $result, $target);

    // ... and the same for MySQL by replication the SQL builder statements
    $db_con->db_type = DB_TYPE_MYSQL;
    $val->time_id = 4;
    $val->usr = $usr;
    $created_sql = $val->load_sql();
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " value_id 
                            FROM `values`
                          WHERE phrase_group_id IN (1) ;";
    test_dsp('value->load_sql by group and time for MySQL', zu_trim($expected_sql), zu_trim($created_sql));

}

