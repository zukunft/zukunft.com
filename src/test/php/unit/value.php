<?php

/*

  test/unit/value.php - unit testing of the VALUE functions
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

class value_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $t->header('Unit tests of the value class (src/main/php/model/value/value.php)');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $db_con = new sql_db();

        // sql to load by word list by ids for PostgreSQL
        $val = new value;
        $val->phr_lst = (new phrase_list_unit_tests)->get_phrase_list();
        $val->time_id = 4;
        $val->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $val->load_sql($db_con);
        $expected_sql = "SELECT
                            value_id 
                       FROM values
                      WHERE phrase_group_id IN (SELECT l1.phrase_group_id 
                                                  FROM phrase_group_word_links l1 
                                                 WHERE l1.word_id = 1)  
                        AND time_word_id = 4 ;";
        $t->dsp('value->load_sql by group and time', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $val->load_sql($db_con, true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $target = true;
        $t->dsp('value->load_sql by group and time', $result, $target);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $val->time_id = 4;
        $val->usr = $usr;
        $created_sql = $val->load_sql($db_con);
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " value_id 
                           FROM `values`
                          WHERE phrase_group_id IN (SELECT l1.phrase_group_id 
                                                      FROM phrase_group_word_links l1 
                                                     WHERE l1.word_id = 1) 
                            AND time_word_id = 4 ;";
        $t->dsp('value->load_sql by group and time for MySQL', $t->trim($expected_sql), $t->trim($created_sql));

        /*
         * im- and export tests
         */

        $t->subheader('Im- and Export tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/value/speed_of_light.json'), true);
        $val = new value;
        $val->usr = $usr;
        $val->import_obj($json_in, false);
        $json_ex = json_decode(json_encode($val->export_obj(false)), true);
        $result = json_is_similar($json_in, $json_ex);
        $target = true;
        $t->dsp('view->import check name', $target, $result);

    }

}