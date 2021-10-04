<?php

/*

  test/unit/view_component_link.php - unit testing of the VIEW COMPONENT LINK functions
  ---------------------------------
  

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

function run_view_component_link_unit_tests()
{

    global $usr;
    global $sql_names;

    test_header('Unit tests of the view component link class (src/main/php/model/view/view_component_link.php)');

    /*
     * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
     */

    $db_con = new sql_db();
    $db_con->db_type = DB_TYPE_POSTGRES;

    // sql to load a list of value by the phrase ids
    $lnk = new view_component_link();
    $lnk->view_id = 1;
    $lnk->view_component_id = 2;
    $lnk->usr = $usr;
    $created_sql = $lnk->load_sql($db_con);
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
    test_dsp('view_component_link->load_sql by view and component', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $lnk->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    test_dsp('view_component_link->load_sql by view and component', $result, $target);

    // ... and the same for MySQL by replication the SQL builder statements
    $db_con->db_type = DB_TYPE_MYSQL;
    $lnk->usr = $usr;
    $created_sql = $lnk->load_sql($db_con);
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
    test_dsp('view_component_link->load_sql by view and component for MySQL', zu_trim($expected_sql), zu_trim($created_sql));

}

