<?php

/*

  test_units.php - UNIT TESTing for zukunft.com
  --------------
  

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

global $db_con;

function run_unit_tests()
{

    global $exe_start_time;

    test_header('Test the base library functions (zu_lib.php)');

    echo "<h3>version control</h3><br>";
    prg_version_is_newer_test();

    test_header('Unit tests of the database connector');

    $db_con = new sql_db();

    /*
     * General tests (one by one for each database)
     */

    // test a simple SQL select creation for PostgreSQL without the standard id and name identification
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_CONFIG);
    $db_con->set_fields(array('value'));
    $db_con->where(array('code_id'), array(CFG_VERSION_DB));
    $created_sql = $db_con->select(false);
    $expected_sql = "SELECT value FROM config WHERE code_id = 'version_database';";
    $exe_start_time = test_show_result('non id PostgreSQL select', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for MySQL
    $db_con->db_type = DB_TYPE_MYSQL;
    $db_con->set_type(DB_TYPE_CONFIG);
    $db_con->set_fields(array('value'));
    $db_con->where(array('code_id'), array(CFG_VERSION_DB));
    $created_sql = $db_con->select(false);
    $expected_sql = "SELECT `value` FROM config WHERE code_id = 'version_database';";
    $exe_start_time = test_show_result('non id MySQL select', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test a simple SQL select creation for PostgreSQL with the standard id and name identification
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_SOURCE_TYPE);
    $db_con->set_where(2);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT source_type_id, source_type_name
                FROM source_types
               WHERE source_type_id = 2;";
    $exe_start_time = test_show_result('PostgreSQL select based on id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for MySQL
    $db_con->db_type = DB_TYPE_MYSQL;
    $db_con->set_type(DB_TYPE_SOURCE_TYPE);
    $db_con->set_where(2);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT source_type_id, source_type_name
                FROM source_types
               WHERE source_type_id = 2;";
    $exe_start_time = test_show_result('MySQL select based on id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    /*
     * Start of the concrete database object test fpr PostgreSQL
     */

    // test a SQL select creation of user sandbox data for PostgreSQL
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_SOURCE);
    $db_con->set_fields(array('code_id'));
    $db_con->set_usr_fields(array('url', 'comment'));
    $db_con->set_usr_num_fields(array('source_type_id'));
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        CASE WHEN (u.source_name    <> '' IS NOT TRUE) THEN s.source_name    ELSE u.source_name    END AS source_name,
                        CASE WHEN (u.url            <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
                        CASE WHEN (u.comment        <> '' IS NOT TRUE) THEN s.comment        ELSE u.comment        END AS comment,
                        CASE WHEN (u.source_type_id IS           NULL) THEN s.source_type_id ELSE u.source_type_id END AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.source_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL user sandbox select', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for search by name
    $db_con->set_type(DB_TYPE_SOURCE);
    $db_con->set_fields(array('code_id'));
    $db_con->set_usr_fields(array('url', 'comment'));
    $db_con->set_usr_num_fields(array('source_type_id'));
    $db_con->set_where(0, 'wikidata');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        CASE WHEN (u.source_name    <> '' IS NOT TRUE) THEN s.source_name    ELSE u.source_name    END AS source_name,
                        CASE WHEN (u.url            <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
                        CASE WHEN (u.comment        <> '' IS NOT TRUE) THEN s.comment        ELSE u.comment        END AS comment,
                        CASE WHEN (u.source_type_id IS           NULL) THEN s.source_type_id ELSE u.source_type_id END AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.source_name = 'wikidata';";
    $exe_start_time = test_show_result('PostgreSQL user sandbox select by name', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for search by code_id
    $db_con->set_type(DB_TYPE_SOURCE);
    $db_con->set_fields(array('code_id'));
    $db_con->set_usr_fields(array('url', 'comment'));
    $db_con->set_usr_num_fields(array('source_type_id'));
    $db_con->set_where(0, '', 'wikidata');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        CASE WHEN (u.source_name    <> '' IS NOT TRUE) THEN s.source_name    ELSE u.source_name    END AS source_name,
                        CASE WHEN (u.url            <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
                        CASE WHEN (u.comment        <> '' IS NOT TRUE) THEN s.comment        ELSE u.comment        END AS comment,
                        CASE WHEN (u.source_type_id IS           NULL) THEN s.source_type_id ELSE u.source_type_id END AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.code_id = 'wikidata' AND s.code_id != NULL;";
    $exe_start_time = test_show_result('PostgreSQL user sandbox select by code_id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for all users by id
    $db_con->set_type(DB_TYPE_SOURCE);
    $db_con->set_fields(array('code_id', 'url', 'comment', 'source_type_id'));
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT
                        source_id,
                        source_name,
                        code_id,
                        url,
                        comment,
                        source_type_id
                   FROM sources 
                  WHERE source_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL all user select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... similar with joined fields
    $db_con->set_type(DB_TYPE_FORMULA);
    $db_con->set_fields(array(sql_db::FLD_USER_ID, 'formula_text', 'resolved_text', 'description', 'formula_type_id', 'all_values_needed', 'last_update', 'excluded'));
    $db_con->set_join_fields(array('code_id'), 'formula_type');
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT s.formula_id,
                     s.formula_name,
                     s.user_id,
                     s.formula_text,
                     s.resolved_text,
                     s.description,
                     s.formula_type_id,
                     s.all_values_needed,
                     s.last_update,
                     s.excluded,
                     l.code_id 
                FROM formulas s
           LEFT JOIN formula_types l ON s.formula_type_id = l.formula_type_id 
               WHERE formula_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL all user join select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for user sandbox data (should match with the parameters in formula->load)
    $db_con->set_type(DB_TYPE_FORMULA);
    $db_con->set_join_usr_fields(array('code_id'), 'formula_type');
    $db_con->set_usr_fields(array('formula_text', 'resolved_text', 'description'));
    $db_con->set_usr_num_fields(array('formula_type_id', 'all_values_needed', 'last_update', 'excluded'));
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT s.formula_id,
                       u.formula_id AS user_formula_id,
                       s.user_id,
                       CASE WHEN (u.formula_name      <> '' IS NOT TRUE) THEN s.formula_name      ELSE u.formula_name      END AS formula_name,
                       CASE WHEN (u.formula_text      <> '' IS NOT TRUE) THEN s.formula_text      ELSE u.formula_text      END AS formula_text,
                       CASE WHEN (u.resolved_text     <> '' IS NOT TRUE) THEN s.resolved_text     ELSE u.resolved_text     END AS resolved_text,
                       CASE WHEN (u.description       <> '' IS NOT TRUE) THEN s.description       ELSE u.description       END AS description,
                       CASE WHEN (u.formula_type_id   IS           NULL) THEN s.formula_type_id   ELSE u.formula_type_id   END AS formula_type_id,
                       CASE WHEN (u.all_values_needed IS           NULL) THEN s.all_values_needed ELSE u.all_values_needed END AS all_values_needed,
                       CASE WHEN (u.last_update       IS           NULL) THEN s.last_update       ELSE u.last_update       END AS last_update,
                       CASE WHEN (u.excluded          IS           NULL) THEN s.excluded          ELSE u.excluded          END AS excluded,
                       CASE WHEN (c.code_id           <> '' IS NOT TRUE) THEN l.code_id           ELSE c.code_id           END AS code_id
                  FROM formulas s
             LEFT JOIN user_formulas u ON s.formula_id = u.formula_id 
                                      AND u.user_id = 1 
             LEFT JOIN formula_types l ON s.formula_type_id = l.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
               WHERE s.formula_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL user sandbox join select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for the special case of a table without name e.g. the value table
    $db_con->set_type(DB_TYPE_VALUE);
    $db_con->set_fields(array('phrase_group_id', 'time_word_id'));
    $db_con->set_usr_num_fields(array('word_value', 'source_id', 'protection_type_id', 'last_update', 'excluded'));
    $db_con->set_usr_only_fields(array('share_type_id'));
    $db_con->set_where_text('s.phrase_group_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                    s.value_id,
                    u.value_id AS user_value_id,
                    s.user_id,
                    s.phrase_group_id,
                    s.time_word_id,
                    CASE WHEN (u.word_value         IS           NULL) THEN s.word_value         ELSE u.word_value         END AS word_value,
                    CASE WHEN (u.source_id          IS           NULL) THEN s.source_id          ELSE u.source_id          END AS source_id,
                    CASE WHEN (u.protection_type_id IS           NULL) THEN s.protection_type_id ELSE u.protection_type_id END AS protection_type_id,
                    CASE WHEN (u.last_update        IS           NULL) THEN s.last_update        ELSE u.last_update        END AS last_update,
                    CASE WHEN (u.excluded           IS           NULL) THEN s.excluded           ELSE u.excluded           END AS excluded,
                    u.share_type_id
               FROM values s 
          LEFT JOIN user_values u ON s.value_id = u.value_id 
                                 AND u.user_id = 1 
              WHERE s.phrase_group_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL user sandbox value select by where text', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for the a link table
    $db_con->set_type(DB_TYPE_WORD_LINK);
    $db_con->set_fields(array('from_phrase_id', 'to_phrase_id', 'verb_id'));
    $db_con->set_usr_fields(array('description', 'excluded'));
    $db_con->set_where_text('s.word_link_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT s.word_link_id,
                     u.word_link_id AS user_word_link_id,
                     s.user_id,
                     s.from_phrase_id,
                     s.to_phrase_id,
                     s.verb_id,
                     CASE WHEN (u.word_link_name <> '' IS NOT TRUE) THEN s.word_link_name ELSE u.word_link_name END AS word_link_name,
                     CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
                     CASE WHEN (u.excluded       <> '' IS NOT TRUE) THEN s.excluded       ELSE u.excluded       END AS excluded
                FROM word_links s 
           LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id 
                                      AND u.user_id = 1 
               WHERE s.word_link_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL user sandbox link select by where text', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view load_standard SQL creation
    $db_con->set_type(DB_TYPE_VIEW);
    $db_con->set_fields(array('comment', 'view_type_id', 'excluded'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT view_id,
                     view_name,
                     comment,
                     view_type_id,
                     excluded
                FROM views
               WHERE view_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL view load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view load SQL creation
    $db_con->set_type(DB_TYPE_VIEW);
    $db_con->set_usr_fields(array('comment'));
    $db_con->set_usr_num_fields(array('view_type_id', 'excluded'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.view_id, 
                        u.view_id AS user_view_id, 
                        s.user_id, 
                        CASE WHEN (u.view_name <> '' IS NOT TRUE) THEN s.view_name    ELSE u.view_name    END AS view_name, 
                        CASE WHEN (u.comment <> ''   IS NOT TRUE) THEN s.comment      ELSE u.comment      END AS comment, 
                        CASE WHEN (u.view_type_id    IS     NULL) THEN s.view_type_id ELSE u.view_type_id END AS view_type_id, 
                        CASE WHEN (u.excluded        IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded 
                   FROM views s 
              LEFT JOIN user_views u ON s.view_id = u.view_id 
                                    AND u.user_id = 1 
                  WHERE s.view_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL view load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view_component_link load_standard SQL creation
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
    $db_con->set_link_fields('view_id', 'view_component_id');
    $db_con->set_fields(array('order_nbr', 'position_type', 'excluded'));
    $db_con->set_where_link(1, 2, 3);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT view_component_link_id,
                     view_id,
                     view_component_id,
                     order_nbr,
                     position_type,
                     excluded
                FROM view_component_links 
               WHERE view_component_link_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL view_component_link load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same but select by the link ids
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
    $db_con->set_link_fields('view_id', 'view_component_id');
    $db_con->set_fields(array('order_nbr', 'position_type', 'excluded'));
    $db_con->set_where_link(0, 2, 3);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT view_component_link_id,
                     view_id,
                     view_component_id,
                     order_nbr,
                     position_type,
                     excluded
                FROM view_component_links 
               WHERE view_id = 2 AND view_component_id = 3;";
    $exe_start_time = test_show_result('PostgreSQL view_component_link load_standard select by link ids', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view_component_link load SQL creation
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
    $db_con->set_link_fields('view_id', 'view_component_id');
    $db_con->set_usr_num_fields(array('order_nbr', 'position_type', 'excluded'));
    $db_con->set_where_link(1, 2, 3);
    $created_sql = $db_con->select();
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
                  WHERE s.view_component_link_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL view_component_link load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the formula_link load_standard SQL creation
    $db_con->set_type(DB_TYPE_FORMULA_LINK);
    $db_con->set_link_fields('formula_id', 'phrase_id');
    $db_con->set_fields(array('link_type_id', 'excluded'));
    $db_con->set_where_link(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT formula_link_id,
                     formula_id,
                     phrase_id,
                     link_type_id,
                     excluded
                FROM formula_links 
               WHERE formula_link_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL formula_link load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the formula_link load SQL creation
    $db_con->set_type(DB_TYPE_FORMULA_LINK);
    $db_con->set_link_fields('formula_id', 'phrase_id');
    $db_con->set_usr_num_fields(array('link_type_id', 'excluded'));
    $db_con->set_where_link(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.formula_link_id, 
                        u.formula_link_id AS user_formula_link_id, 
                        s.user_id, 
                        s.formula_id, 
                        s.phrase_id, 
                        CASE WHEN (u.link_type_id IS NULL) THEN s.link_type_id ELSE u.link_type_id END AS link_type_id, 
                        CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END AS excluded 
                   FROM formula_links s 
              LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id 
                                            AND u.user_id = 1 
                  WHERE s.formula_link_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL formula_link load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view_component load_standard SQL creation
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT);
    $db_con->set_fields(array('comment', 'view_component_type_id', 'word_id_row','link_type_id','formula_id','word_id_col','word_id_col2','excluded'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT view_component_id,
                     view_component_name,
                     comment,
                     view_component_type_id,
                     word_id_row,
                     link_type_id,
                     formula_id,
                     word_id_col,
                     word_id_col2,
                     excluded
                FROM view_components
               WHERE view_component_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL view_component load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view_component load SQL creation
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT);
    $db_con->set_join_usr_fields(array('code_id'), 'view_component_type');
    $db_con->set_usr_fields(array('comment'));
    $db_con->set_usr_num_fields(array('view_component_type_id', 'word_id_row','link_type_id','formula_id','word_id_col','word_id_col2','excluded'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.view_component_id,
                        u.view_component_id AS user_view_component_id,  
                        s.user_id,  
                        CASE WHEN (u.view_component_name <> '' IS NOT TRUE) THEN s.view_component_name    ELSE u.view_component_name    END AS view_component_name,  
                        CASE WHEN (u.comment             <> '' IS NOT TRUE) THEN s.comment                ELSE u.comment                END AS comment,   
                        CASE WHEN (u.view_component_type_id    IS NULL)     THEN s.view_component_type_id ELSE u.view_component_type_id END AS view_component_type_id,  
                        CASE WHEN (u.word_id_row               IS NULL)     THEN s.word_id_row            ELSE u.word_id_row            END AS word_id_row,  
                        CASE WHEN (u.link_type_id              IS NULL)     THEN s.link_type_id           ELSE u.link_type_id           END AS link_type_id,  
                        CASE WHEN (u.formula_id                IS NULL)     THEN s.formula_id             ELSE u.formula_id             END AS formula_id,  
                        CASE WHEN (u.word_id_col               IS NULL)     THEN s.word_id_col            ELSE u.word_id_col            END AS word_id_col,  
                        CASE WHEN (u.word_id_col2              IS NULL)     THEN s.word_id_col2           ELSE u.word_id_col2           END AS word_id_col2,  
                        CASE WHEN (u.excluded                  IS NULL)     THEN s.excluded               ELSE u.excluded               END AS excluded,  
                        CASE WHEN (c.code_id <> ''             IS NOT TRUE) THEN l.code_id                ELSE c.code_id                END AS code_id 
                   FROM view_components s 
              LEFT JOIN user_view_components u ON s.view_component_id = u.view_component_id 
                                              AND u.user_id = 1 
              LEFT JOIN view_component_types l ON s.view_component_type_id = l.view_component_type_id 
              LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id 
                  WHERE s.view_component_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL view_component load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the word_link load_standard SQL creation
    $db_con->set_type(DB_TYPE_WORD_LINK);
    $db_con->set_link_fields('from_phrase_id','to_phrase_id','verb_id');
    $db_con->set_fields(array('description','excluded'));
    $db_con->set_where_text('word_link_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT word_link_id,
                     word_link_name,
                     from_phrase_id,
                     to_phrase_id,
                     verb_id,
                     description,
                     excluded
                FROM word_links 
               WHERE word_link_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL word_link load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the word_link load SQL creation
    $db_con->set_type(DB_TYPE_WORD_LINK);
    $db_con->set_link_fields('from_phrase_id','to_phrase_id','verb_id');
    $db_con->set_usr_fields(array('description'));
    $db_con->set_usr_num_fields(array('excluded'));
    $db_con->set_where_text('word_link_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.word_link_id, 
                        u.word_link_id AS user_word_link_id, 
                        s.user_id,
                        s.from_phrase_id,
                        s.to_phrase_id,
                        s.verb_id, 
                        CASE WHEN (u.word_link_name <> '' IS NOT TRUE) THEN s.word_link_name ELSE u.word_link_name END AS word_link_name, 
                        CASE WHEN (u.description <> ''    IS NOT TRUE) THEN s.description    ELSE u.description    END AS description, 
                        CASE WHEN (u.excluded             IS     NULL) THEN s.excluded       ELSE u.excluded       END AS excluded 
                   FROM word_links s 
              LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id 
                                         AND u.user_id = 1 
                  WHERE word_link_id = 1;";
    $exe_start_time = test_show_result('PostgreSQL word_link load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    /*
     * Start of the corresponding MySQL tests
     */

    // ... and search by id for MySQL
    $db_con->db_type = DB_TYPE_MYSQL;
    $db_con->set_type(DB_TYPE_SOURCE);
    $db_con->set_fields(array('code_id'));
    $db_con->set_usr_fields(array('url', 'comment'));
    $db_con->set_usr_num_fields(array('source_type_id'));
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        IF(u.source_name    IS NULL, s.source_name,    u.source_name)    AS source_name,
                        IF(u.`url`          IS NULL, s.`url`,          u.`url`)          AS `url`,
                        IF(u.comment        IS NULL, s.comment,        u.comment)        AS comment,
                        IF(u.source_type_id IS NULL, s.source_type_id, u.source_type_id) AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.source_id = 1;";
    $exe_start_time = test_show_result('MySQL user sandbox select', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for search by name
    $db_con->set_type(DB_TYPE_SOURCE);
    $db_con->set_fields(array('code_id'));
    $db_con->set_usr_fields(array('url', 'comment'));
    $db_con->set_usr_num_fields(array('source_type_id'));
    $db_con->set_where(0, 'wikidata');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        IF(u.source_name    IS NULL, s.source_name,    u.source_name)    AS source_name,
                        IF(u.`url`          IS NULL, s.`url`,          u.`url`)          AS `url`,
                        IF(u.comment        IS NULL, s.comment,        u.comment)        AS comment,
                        IF(u.source_type_id IS NULL, s.source_type_id, u.source_type_id) AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.source_name = 'wikidata';";
    $exe_start_time = test_show_result('MySQL user sandbox select by name', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for search by code_id
    $db_con->set_type(DB_TYPE_SOURCE);
    $db_con->set_fields(array('code_id'));
    $db_con->set_usr_fields(array('url', 'comment'));
    $db_con->set_usr_num_fields(array('source_type_id'));
    $db_con->set_where(0, '', 'wikidata');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        IF(u.source_name    IS NULL, s.source_name,    u.source_name)    AS source_name,
                        IF(u.`url`          IS NULL, s.`url`,          u.`url`)          AS `url`,
                        IF(u.comment        IS NULL, s.comment,        u.comment)        AS comment,
                        IF(u.source_type_id IS NULL, s.source_type_id, u.source_type_id) AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.code_id = 'wikidata';";
    $exe_start_time = test_show_result('MySQL user sandbox select by code_id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for all users by id
    $db_con->set_type(DB_TYPE_SOURCE);
    $db_con->set_fields(array('code_id', 'url', 'comment', 'source_type_id'));
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT
                        source_id,
                        source_name,
                        code_id,
                        `url`,
                        comment,
                        source_type_id
                   FROM sources 
                  WHERE source_id = 1;";
    $exe_start_time = test_show_result('MySQL all user select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... similar with joined fields
    $db_con->set_type(DB_TYPE_FORMULA);
    $db_con->set_fields(array(sql_db::FLD_USER_ID, 'formula_text', 'resolved_text', 'description', 'formula_type_id', 'all_values_needed', 'last_update', 'excluded'));
    $db_con->set_join_fields(array('code_id'), 'formula_type');
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT s.formula_id,
                     s.formula_name,
                     s.user_id,
                     s.formula_text,
                     s.resolved_text,
                     s.description,
                     s.formula_type_id,
                     s.all_values_needed,
                     s.last_update,
                     s.excluded,
                     l.code_id
                FROM formulas s
           LEFT JOIN formula_types l ON s.formula_type_id = l.formula_type_id 
               WHERE formula_id = 1;";
    $exe_start_time = test_show_result('MySQL all user join select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for user sandbox data
    $db_con->set_type(DB_TYPE_FORMULA);
    $db_con->set_join_usr_fields(array('code_id'), 'formula_type');
    $db_con->set_usr_fields(array('formula_text', 'resolved_text', 'description'));
    $db_con->set_usr_num_fields(array('formula_type_id', 'all_values_needed', 'last_update', 'excluded'));
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.formula_id, 
                        u.formula_id AS user_formula_id, 
                        s.user_id, 
                        IF(u.formula_name      IS NULL, s.formula_name,      u.formula_name)      AS formula_name, 
                        IF(u.formula_text      IS NULL, s.formula_text,      u.formula_text)      AS formula_text, 
                        IF(u.resolved_text     IS NULL, s.resolved_text,     u.resolved_text)     AS resolved_text, 
                        IF(u.description       IS NULL, s.description,       u.description)       AS description, 
                        IF(u.formula_type_id   IS NULL, s.formula_type_id,   u.formula_type_id)   AS formula_type_id, 
                        IF(u.all_values_needed IS NULL, s.all_values_needed, u.all_values_needed) AS all_values_needed, 
                        IF(u.last_update       IS NULL, s.last_update,       u.last_update)       AS last_update, 
                        IF(u.excluded          IS NULL, s.excluded,          u.excluded)          AS excluded, 
                        IF(c.code_id           IS NULL, l.code_id,           c.code_id)           AS code_id 
                   FROM formulas s 
              LEFT JOIN user_formulas u ON s.formula_id = u.formula_id 
                                       AND u.user_id = 1 
              LEFT JOIN formula_types l ON s.formula_type_id = l.formula_type_id 
              LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id 
                  WHERE s.formula_id = 1;";
    $exe_start_time = test_show_result('MySQL all user join select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for the special case of a table without name e.g. the value table
    $db_con->set_type(DB_TYPE_VALUE);
    $db_con->set_fields(array('phrase_group_id', 'time_word_id'));
    $db_con->set_usr_fields(array('word_value', 'source_id', 'last_update', 'protection_type_id', 'excluded'));
    $db_con->set_usr_only_fields(array('share_type_id'));
    $db_con->set_where_text('s.phrase_group_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                    s.value_id,
                    u.value_id AS user_value_id,
                    s.user_id,
                    s.phrase_group_id,
                    s.time_word_id,
                    IF(u.word_value         IS NULL, s.word_value,         u.word_value)         AS word_value,
                    IF(u.source_id          IS NULL, s.source_id,          u.source_id)          AS source_id,
                    IF(u.last_update        IS NULL, s.last_update,        u.last_update)        AS last_update,
                    IF(u.protection_type_id IS NULL, s.protection_type_id, u.protection_type_id) AS protection_type_id,
                    IF(u.excluded           IS NULL, s.excluded,           u.excluded)           AS excluded,
                    u.share_type_id
               FROM `values` s 
          LEFT JOIN user_values u ON s.value_id = u.value_id 
                                 AND u.user_id = 1 
              WHERE s.phrase_group_id = 1;";
    $exe_start_time = test_show_result('MySQL user sandbox value select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for the a link table
    $db_con->set_type(DB_TYPE_WORD_LINK);
    $db_con->set_fields(array('from_phrase_id', 'to_phrase_id', 'verb_id'));
    $db_con->set_usr_fields(array('description', 'excluded'));
    $db_con->set_where_text('s.word_link_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT s.word_link_id,
                     u.word_link_id AS user_word_link_id,
                     s.user_id,
                     s.from_phrase_id,
                     s.to_phrase_id,
                     s.verb_id,
                     IF(u.word_link_name IS NULL, s.word_link_name, u.word_link_name) AS word_link_name,
                     IF(u.description    IS NULL, s.description,    u.description)    AS description,
                     IF(u.excluded       IS NULL, s.excluded,       u.excluded)       AS excluded
                FROM word_links s 
           LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id 
                                      AND u.user_id = 1 
               WHERE s.word_link_id = 1;";
    $exe_start_time = test_show_result('MySQL user sandbox link select by where text', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view_component_link load_standard SQL creation
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
    $db_con->set_link_fields('view_id', 'view_component_id');
    $db_con->set_fields(array('order_nbr', 'position_type', 'excluded'));
    $db_con->set_where_link(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        view_component_link_id,
                        view_id,
                        view_component_id,
                        order_nbr,
                        position_type,
                        excluded
                   FROM view_component_links 
                  WHERE view_component_link_id = 1;";
    $exe_start_time = test_show_result('MySQL view_component_link load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view_component_link load SQL creation
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
    $db_con->set_link_fields('view_id', 'view_component_id');
    $db_con->set_usr_num_fields(array('order_nbr', 'position_type', 'excluded'));
    $db_con->set_where_link(1, 2, 3);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.view_component_link_id, 
                        u.view_component_link_id AS user_view_component_link_id, 
                        s.user_id, s.view_id, s.view_component_id, 
                        IF(u.order_nbr     IS NULL, s.order_nbr,     u.order_nbr)     AS order_nbr, 
                        IF(u.position_type IS NULL, s.position_type, u.position_type) AS position_type, 
                        IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded 
                   FROM view_component_links s 
              LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id 
                                                   AND u.user_id = 1 
                  WHERE s.view_component_link_id = 1;";
    $exe_start_time = test_show_result('MySQL view_component_link load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the formula_link load_standard SQL creation
    $db_con->set_type(DB_TYPE_FORMULA_LINK);
    $db_con->set_link_fields('formula_id', 'phrase_id');
    $db_con->set_fields(array('link_type_id', 'excluded'));
    $db_con->set_where_link(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT formula_link_id,
                     formula_id,
                     phrase_id,
                     link_type_id,
                     excluded
                FROM formula_links 
               WHERE formula_link_id = 1;";
    $exe_start_time = test_show_result('MySQL formula_link load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the formula_link load SQL creation
    $db_con->set_type(DB_TYPE_FORMULA_LINK);
    $db_con->set_link_fields('formula_id', 'phrase_id');
    $db_con->set_usr_num_fields(array('link_type_id', 'excluded'));
    $db_con->set_where_link(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.formula_link_id,  
                        u.formula_link_id AS user_formula_link_id,  
                        s.user_id,  
                        s.formula_id,  
                        s.phrase_id,          
                        IF(u.link_type_id IS NULL, s.link_type_id, u.link_type_id) AS link_type_id,          
                        IF(u.excluded     IS NULL, s.excluded,     u.excluded)     AS excluded 
                   FROM formula_links s 
              LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id 
                                            AND u.user_id = 1
                  WHERE s.formula_link_id = 1;";
    $exe_start_time = test_show_result('MySQL formula_link load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view_component load_standard SQL creation
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT);
    $db_con->set_fields(array('comment', 'view_component_type_id', 'word_id_row','link_type_id','formula_id','word_id_col','word_id_col2','excluded'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT view_component_id,
                     view_component_name,
                     comment,
                     view_component_type_id,
                     word_id_row,
                     link_type_id,
                     formula_id,
                     word_id_col,
                     word_id_col2,
                     excluded
                FROM view_components
               WHERE view_component_id = 1;";
    $exe_start_time = test_show_result('MySQL view_component load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the view_component load SQL creation
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT);
    $db_con->set_join_usr_fields(array('code_id'), 'view_component_type');
    $db_con->set_usr_fields(array('comment'));
    $db_con->set_usr_num_fields(array('view_component_type_id', 'word_id_row','link_type_id','formula_id','word_id_col','word_id_col2','excluded'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT s.view_component_id,
                       u.view_component_id AS user_view_component_id,
                       s.user_id,
                       IF(u.view_component_name IS NULL,    s.view_component_name,    u.view_component_name)    AS view_component_name,
                       IF(u.comment IS NULL,                s.comment,                u.comment)                AS comment,
                       IF(u.view_component_type_id IS NULL, s.view_component_type_id, u.view_component_type_id) AS view_component_type_id,
                       IF(u.word_id_row IS NULL,            s.word_id_row,            u.word_id_row)            AS word_id_row,
                       IF(u.link_type_id IS NULL,           s.link_type_id,           u.link_type_id)           AS link_type_id,
                       IF(u.formula_id IS NULL,             s.formula_id,             u.formula_id)             AS formula_id,
                       IF(u.word_id_col IS NULL,            s.word_id_col,            u.word_id_col)            AS word_id_col,
                       IF(u.word_id_col2 IS NULL,           s.word_id_col2,           u.word_id_col2)           AS word_id_col2,
                       IF(u.excluded IS NULL,               s.excluded,               u.excluded)               AS excluded,
                       IF(c.code_id IS NULL,                l.code_id,                c.code_id)                AS code_id
                  FROM view_components s
             LEFT JOIN user_view_components u ON s.view_component_id = u.view_component_id 
                                             AND u.user_id = 1 
             LEFT JOIN view_component_types l ON s.view_component_type_id = l.view_component_type_id
             LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
                 WHERE s.view_component_id = 1;";
    $exe_start_time = test_show_result('MySQL view_component load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the word_link load_standard SQL creation
    $db_con->set_type(DB_TYPE_WORD_LINK);
    $db_con->set_link_fields('from_phrase_id','to_phrase_id','verb_id');
    $db_con->set_fields(array('description','excluded'));
    $db_con->set_where_text('word_link_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        word_link_id,
                        word_link_name,
                        from_phrase_id,
                        to_phrase_id,
                        verb_id,
                        description,
                        excluded
                   FROM word_links 
                  WHERE word_link_id = 1;";
    $exe_start_time = test_show_result('MySQL word_link load_standard select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test the word_link load SQL creation
    $db_con->set_type(DB_TYPE_WORD_LINK);
    $db_con->set_link_fields('from_phrase_id','to_phrase_id','verb_id');
    $db_con->set_usr_fields(array('description'));
    $db_con->set_usr_num_fields(array('excluded'));
    $db_con->set_where_text('word_link_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                        s.word_link_id, 
                        u.word_link_id AS user_word_link_id, 
                        s.user_id, 
                        s.from_phrase_id,
                        s.to_phrase_id, 
                        s.verb_id, 
                        IF(u.word_link_name IS NULL, s.word_link_name, u.word_link_name) AS word_link_name, 
                        IF(u.description    IS NULL, s.description,    u.description)    AS description,
                        IF(u.excluded       IS NULL, s.excluded,       u.excluded)       AS excluded 
                   FROM word_links s 
              LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id 
                                         AND u.user_id = 1 
                  WHERE word_link_id = 1;";
    $exe_start_time = test_show_result('MySQL word_link load select by id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

}

function run_string_unit_tests()
{

    global $exe_start_time;

    test_header('Test the old basic zukunft functions (zu_lib.php)');

    echo "<h3>strings</h3><br>";

    // test zu_trim
    $text = "  This  text  has  many  spaces  ";
    $target = "This text has many spaces";
    $result = zu_trim($text);
    $exe_start_time = test_show_result(", zu_trim", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_left
    $text = "This are the left 4";
    $pos = 4;
    $target = "This";
    $result = zu_str_left($text, $pos);
    $exe_start_time = test_show_result(", zu_str_left: What are the left \"" . $pos . "\" chars of \"" . $text . "\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_right
    $text = "This are the right 7";
    $pos = 7;
    $target = "right 7";
    $result = zu_str_right($text, $pos);
    $exe_start_time = test_show_result(", zu_str_right: What are the right \"" . $pos . "\" chars of \"" . $text . "\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_left_of
    $text = "This is left of that ";
    $maker = " of that";
    $target = "This is left";
    $result = zu_str_left_of($text, $maker);
    $exe_start_time = test_show_result(", zu_str_left_of: What is left of \"" . $maker . "\" in \"" . $text . "\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_left_of
    $text = "This is left of that, but not of that";
    $maker = " of that";
    $target = "This is left";
    $result = zu_str_left_of($text, $maker);
    $exe_start_time = test_show_result(", zu_str_left_of: What is left of \"" . $maker . "\" in \"" . $text . "\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_right_of
    $text = "That is right of this";
    $maker = "That is right ";
    $target = "of this";
    $result = zu_str_right_of($text, $maker);
    $exe_start_time = test_show_result(", zu_str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_right_of
    $text = "00000";
    $maker = "0";
    $target = "0000";
    $result = zu_str_right_of($text, $maker);
    $exe_start_time = test_show_result(", zu_str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_right_of
    $text = "The formula id of {f23}.";
    $maker = "{f";
    $target = "23}.";
    $result = zu_str_right_of($text, $maker);
    $exe_start_time = test_show_result(", zu_str_right_of: What is right of \"" . $maker . "\" in \"" . $text . "\"", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_between
    $text = "The formula id of {f23}.";
    $maker_start = "{f";
    $maker_end = "}";
    $target = "23";
    $result = zu_str_between($text, $maker_start, $maker_end);
    $exe_start_time = test_show_result(", zu_str_between: " . $text . "", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // test zu_str_between
    $text = "The formula id of {f4} / {f5}.";
    $maker_start = "{f";
    $maker_end = "}";
    $target = "4";
    $result = zu_str_between($text, $maker_start, $maker_end);
    $exe_start_time = test_show_result(", zu_str_between: " . $text . "", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}