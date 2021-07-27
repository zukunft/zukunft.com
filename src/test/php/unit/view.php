<?php

/*

  test/unit/view.php - unit testing of the view functions
  ------------------
  

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

use Swaggest\JsonDiff\JsonDiff;

function run_view_unit_tests()
{

    global $usr;
    global $exe_start_time;
    global $sql_names;

    test_header('Unit tests of the view class (src/main/php/model/value/view.php)');

    /*
     * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
     */

    test_subheader('SQL statement tests');

    $db_con = new sql_db();

    // sql to load the view by id
    $dsp = new view;
    $dsp->id = 2;
    $dsp->usr = $usr;
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = $dsp->load_sql($db_con);
    $expected_sql = "SELECT 
                            s.view_id,  
                            u.view_id AS user_view_id,  
                            s.user_id,  
                            CASE WHEN (u.view_name <> '' IS NOT TRUE) THEN s.view_name    ELSE u.view_name    END AS view_name,  
                            CASE WHEN (u.comment <> ''   IS NOT TRUE) THEN s.comment      ELSE u.comment      END AS comment,  
                            CASE WHEN (u.view_type_id    IS     NULL) THEN s.view_type_id ELSE u.view_type_id END AS view_type_id,  
                            CASE WHEN (u.excluded        IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded 
                       FROM views s LEFT JOIN user_views u ON s.view_id = u.view_id 
                                                          AND u.user_id = 1 
                      WHERE s.view_id = 2;";
    $exe_start_time = test_show_result('view->load_sql by view id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $dsp->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('view->load_sql by view id check sql name', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load the view by code id
    $dsp = new view;
    $dsp->id = 0;
    $dsp->code_id = DBL_VIEW_WORD;
    $dsp->usr = $usr;
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = $dsp->load_sql($db_con);
    $expected_sql = "SELECT 
                            s.view_id,  
                            u.view_id AS user_view_id,  
                            s.user_id,  
                            CASE WHEN (u.view_name <> '' IS NOT TRUE) THEN s.view_name    ELSE u.view_name    END AS view_name,  
                            CASE WHEN (u.comment <> ''   IS NOT TRUE) THEN s.comment      ELSE u.comment      END AS comment,  
                            CASE WHEN (u.view_type_id    IS     NULL) THEN s.view_type_id ELSE u.view_type_id END AS view_type_id,  
                            CASE WHEN (u.excluded        IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded 
                       FROM views s LEFT JOIN user_views u ON s.view_id = u.view_id 
                                                          AND u.user_id = 1 
                      WHERE s.code_id = '" . DBL_VIEW_WORD . "' 
                        AND s.code_id != NULL;";
    $exe_start_time = test_show_result('view->load_sql by code id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $dsp->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('view->load_sql by code id check sql name', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load the view by name
    $dsp = new view;
    $dsp->id = 0;
    $dsp->code_id = null;
    $dsp->name = view::TEST_NAME;
    $dsp->usr = $usr;
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = $dsp->load_sql($db_con);
    $expected_sql = "SELECT 
                            s.view_id,  
                            u.view_id AS user_view_id,  
                            s.user_id,  
                            CASE WHEN (u.view_name <> '' IS NOT TRUE) THEN s.view_name    ELSE u.view_name    END AS view_name,  
                            CASE WHEN (u.comment <> ''   IS NOT TRUE) THEN s.comment      ELSE u.comment      END AS comment,  
                            CASE WHEN (u.view_type_id    IS     NULL) THEN s.view_type_id ELSE u.view_type_id END AS view_type_id,  
                            CASE WHEN (u.excluded        IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded 
                       FROM views s LEFT JOIN user_views u ON s.view_id = u.view_id 
                                                          AND u.user_id = 1 
                      WHERE view_name = '" . view::TEST_NAME . "';";
    $exe_start_time = test_show_result('view->load_sql by name', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $dsp->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('view->load_sql by name check sql name', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load the view components
    $dsp = new view;
    $dsp->id = 2;
    $dsp->usr = $usr;
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = $dsp->load_components_sql($db_con);
    $expected_sql = "SELECT e.view_component_id, 
                    u.view_component_id AS user_entry_id,
                    e.user_id, 
                     CASE WHEN (y.order_nbr                 IS     NULL) THEN l.order_nbr              ELSE y.order_nbr              END AS order_nbr,
                     CASE WHEN (u.view_component_name <> '' IS NOT TRUE) THEN e.view_component_name    ELSE u.view_component_name    END AS view_component_name,
                     CASE WHEN (u.view_component_type_id    IS     NULL) THEN e.view_component_type_id ELSE u.view_component_type_id END AS view_component_type_id,
                     CASE WHEN (c.code_id <> ''             IS NOT TRUE) THEN t.code_id                ELSE c.code_id                END AS code_id,
                     CASE WHEN (u.word_id_row               IS     NULL) THEN e.word_id_row            ELSE u.word_id_row            END AS word_id_row,
                     CASE WHEN (u.link_type_id              IS     NULL) THEN e.link_type_id           ELSE u.link_type_id           END AS link_type_id,
                     CASE WHEN (u.formula_id                IS     NULL) THEN e.formula_id             ELSE u.formula_id             END AS formula_id,
                     CASE WHEN (u.word_id_col               IS     NULL) THEN e.word_id_col            ELSE u.word_id_col            END AS word_id_col,
                     CASE WHEN (u.word_id_col2              IS     NULL) THEN e.word_id_col2           ELSE u.word_id_col2           END AS word_id_col2,
                     CASE WHEN (y.excluded                  IS     NULL) THEN l.excluded               ELSE y.excluded               END AS link_excluded,
                     CASE WHEN (u.excluded                  IS     NULL) THEN e.excluded               ELSE u.excluded               END AS excluded
               FROM view_component_links l            
          LEFT JOIN user_view_component_links y ON y.view_component_link_id = l.view_component_link_id 
                                               AND y.user_id = 1, 
                    view_components e             
          LEFT JOIN user_view_components u ON u.view_component_id = e.view_component_id 
                                          AND u.user_id = 1 
          LEFT JOIN view_component_types t ON e.view_component_type_id = t.view_component_type_id
          LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
              WHERE l.view_id = 2 
                AND l.view_component_id = e.view_component_id 
           ORDER BY order_nbr;";
    $exe_start_time = test_show_result('view->load_components_sql by view id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $dsp->load_components_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('view->load_components_sql check sql name', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // ... and the same for MySQL by replication the SQL builder statements
    $db_con->db_type = DB_TYPE_MYSQL;
    $created_sql = $dsp->load_components_sql($db_con);
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " e.view_component_id, 
                    u.view_component_id AS user_entry_id,
                    e.user_id, 
                    IF(y.order_nbr IS NULL, l.order_nbr, y.order_nbr) AS order_nbr,
                    IF(u.view_component_name IS NULL,    e.view_component_name,    u.view_component_name)    AS view_component_name,
                    IF(u.view_component_type_id IS NULL, e.view_component_type_id, u.view_component_type_id) AS view_component_type_id,
                    IF(c.code_id IS NULL,                t.code_id,                c.code_id)                AS code_id,
                    IF(u.word_id_row IS NULL,            e.word_id_row,            u.word_id_row)            AS word_id_row,
                    IF(u.link_type_id IS NULL,           e.link_type_id,           u.link_type_id)           AS link_type_id,
                    IF(u.formula_id IS NULL,             e.formula_id,             u.formula_id)             AS formula_id,
                    IF(u.word_id_col IS NULL,            e.word_id_col,            u.word_id_col)            AS word_id_col,
                    IF(u.word_id_col2 IS NULL,           e.word_id_col2,           u.word_id_col2)           AS word_id_col2,
                    IF(y.excluded IS NULL,               l.excluded,               y.excluded)               AS link_excluded,
                    IF(u.excluded IS NULL,               e.excluded,               u.excluded)               AS excluded
               FROM view_component_links l            
          LEFT JOIN user_view_component_links y ON y.view_component_link_id = l.view_component_link_id 
                                               AND y.user_id = 1, 
                    view_components e             
          LEFT JOIN user_view_components u ON u.view_component_id = e.view_component_id 
                                          AND u.user_id = 1 
          LEFT JOIN view_component_types t ON e.view_component_type_id = t.view_component_type_id
          LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
              WHERE l.view_id = 2 
                AND l.view_component_id = e.view_component_id 
           ORDER BY order_nbr;";
    $exe_start_time = test_show_result('view->load_components_sql for MySQL', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    /*
     * Im- and Export tests
     */

    test_subheader('Im- and Export tests');

    $dsp_json = '{
      "name": "car cost agreement",
      "comment": "view the cost agreement of car usage",
      "type": "dsp_type_default",
      "view_components": [
        {
          "position": 1,
          "name": "Name",
          "type": "",
          "row": "",
          "column": "",
          "column2": "",
          "comment": ""
        }
      ]
    }';
    $json_import_array = json_decode($dsp_json, true);
    $dsp = new view_dsp;
    $dsp->import_obj($json_import_array, false);
    $json_export_string = json_encode($dsp->export_obj(false));
    $result = json_decode($dsp_json) == json_decode($json_export_string);
    $target = true;
    $exe_start_time = test_show_result('view->import check name', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    /*
     * Display tests
     */

    test_subheader('Display tests');

    /*
     * needs database connection
    $dsp = new view_dsp;
    $dsp->id = 1;
    $dsp->code_id = null;
    $dsp->name = view::TEST_NAME;
    $dsp->usr = $usr;
    $wrd = new word();
    $wrd->name = word::TEST_NAME;
    $result = $dsp->display($wrd, 1);
    $target = '';
    $exe_start_time = test_show_result('view->display', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    */

}

