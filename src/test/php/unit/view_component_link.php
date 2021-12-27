<?php

/*

  test/unit/view_component_link.php - unit testing of the VIEW COMPONENT LINK functions
  ---------------------------------
  

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

class view_component_link_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $t->header('Unit tests of the view component link class (src/main/php/model/view/view_component_link.php)');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $db_con = new sql_db();
        $db_con->db_type = sql_db::POSTGRES;

        // sql to load a list of value by the phrase ids
        $lnk = new view_cmp_link($usr);
        $lnk->view_id = 1;
        $lnk->view_component_id = 2;
        $created_sql = $lnk->load_sql($db_con)->sql;
        $expected_sql = "SELECT 
                         s.view_component_link_id,  
                         u.view_component_link_id AS user_view_component_link_id,  
                         s.user_id,  
                         s.view_id,  
                         s.view_component_id,  
                         CASE WHEN (u.order_nbr     IS NULL) THEN s.order_nbr     ELSE u.order_nbr     END AS order_nbr,  
                         CASE WHEN (u.position_type IS NULL) THEN s.position_type ELSE u.position_type END AS position_type,  
                         CASE WHEN (u.excluded      IS NULL) THEN s.excluded      ELSE u.excluded      END AS excluded 
                    FROM view_component_links s 
               LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id 
                                                    AND u.user_id = 1 
                   WHERE s.view_id = 1 
                     AND s.view_component_id = 2;";
        $t->dsp('view_component_link->load_sql by view and component', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($lnk->load_sql($db_con)->name);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $lnk->usr = $usr;
        $created_sql = $lnk->load_sql($db_con)->sql;
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " 
                         s.view_component_link_id,  
                         u.view_component_link_id AS user_view_component_link_id,  
                         s.user_id,  
                         s.view_id,  
                         s.view_component_id,          
                         IF(u.order_nbr IS NULL, s.order_nbr, u.order_nbr)    AS order_nbr,          
                         IF(u.position_type IS NULL, s.position_type, u.position_type)    AS position_type,          
                         IF(u.excluded IS NULL, s.excluded, u.excluded)    AS excluded 
                    FROM view_component_links s 
               LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id 
                                                    AND u.user_id = 1 
                   WHERE s.view_id = 1 
                     AND s.view_component_id = 2;";
        $t->dsp('view_component_link->load_sql by view and component for MySQL', $t->trim($expected_sql), $t->trim($created_sql));

    }

}