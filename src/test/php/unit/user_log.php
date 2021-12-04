<?php

/*

  test/unit/user_log.php - unit testing of the user log functions
  ----------------------
  

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

class user_log_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $t->header('Unit tests of the user log display class (src/main/php/web/user_log_display.php)');


        $t->subheader('SQL statement tests');

        $db_con = new sql_db();

        // sql to load the word by id
        $log_dsp = new user_log_display();
        $log_dsp->type = 'user';
        $log_dsp->usr = $usr;
        $log_dsp->size = SQL_ROW_LIMIT;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $log_dsp->dsp_hist_links_sql($db_con);
        $expected_sql = "SELECT 
                        c.change_link_id, 
                        c.change_time AS time, 
                        u.user_name, 
                        a.change_action_name AS type, 
                        c.new_text_link AS link, 
                        c.row_id, 
                        c.old_text_to AS old, 
                        c.new_text_to AS new
                   FROM change_links c,
                        change_actions a,
                        change_tables t,
                        users u
                  WHERE ( c.change_table_id = 3 )
                    AND c.change_table_id  = t.change_table_id
                    AND c.change_action_id = a.change_action_id 
                    AND c.user_id = u.user_id
                    AND c.user_id = 1  
               ORDER BY c.change_time DESC
                  LIMIT 20;";
        $t->dsp('user_log_display->dsp_hist_links_sql by ' . $log_dsp->type, $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $log_dsp->dsp_hist_links_sql($db_con, true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $target = true;
        $t->dsp('user_log_display->dsp_hist_links_sql by ' . $log_dsp->type . ' id check sql name', $result, $target);

    }

}
