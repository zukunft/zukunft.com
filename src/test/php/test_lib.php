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

    test_header('Start the zukunft.com unit tests');

    test_subheader('Test usr sandbox functions that does not need a database connection');

    // test if two sources are supposed to be the same
    $src1 = new source;
    $src1->id = 1;
    $src1->name = TS_IPCC_AR6_SYNTHESIS;
    $src2 = new source;
    $src2->id = 2;
    $src2->name = TS_IPCC_AR6_SYNTHESIS;
    $target = true;
    $result = $src1->is_same($src2);
    $exe_start_time = test_show_result("are two sources supposed to be the same", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... and they are of course also similar
    $target = true;
    $result = $src1->is_similar($src2);
    $exe_start_time = test_show_result("... and similar", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // a source can have the same name as a word
    $wrd1 = new word;
    $wrd1->id = 1;
    $wrd1->name = TS_IPCC_AR6_SYNTHESIS;
    $src2 = new source;
    $src2->id = 2;
    $src2->name = TS_IPCC_AR6_SYNTHESIS;
    $target = false;
    $result = $wrd1->is_same($src2);
    $exe_start_time = test_show_result("a source is not the same as a word even if they have the same name", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // but a formula should not have the same name as a word
    $wrd = new word;
    $wrd->name = TW_MIO;
    $frm = new formula();
    $frm->name = TW_MIO;
    $target = true;
    $result = $wrd->is_similar($frm);
    $exe_start_time = test_show_result("a formula should not have the same name as a word", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // ... but they are not the same
    $target = false;
    $result = $wrd->is_same($frm);
    $exe_start_time = test_show_result("... but they are not the same", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    // a word with the name 'millions' is similar to a formulas named 'millions', but not the same, so

    test_subheader('Test sql base functions');

    // test sf (Sql Formatting) function
    $text = "'4'";
    $target = "'''4'''";
    $result = sf($text);
    $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $text = "'4'";
    $target = "4";
    $result = sf($text, sql_db::FLD_FORMAT_VAL);
    $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $text = "2021";
    $target = "'2021'";
    $result = sf($text, sql_db::FLD_FORMAT_TEXT);
    $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $text = "four";
    $target = "'four'";
    $result = sf($text);
    $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $text = "'four'";
    $target = "'''four'''";
    $result = sf($text);
    $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    $text = " ";
    $target = "NULL";
    $result = sf($text);
    $exe_start_time = test_show_result(", sf: ".$text."", $target, $result, $exe_start_time, TIMEOUT_LIMIT);

    test_subheader('Test the version control');

    prg_version_is_newer_test();

    test_header('Unit tests of the database connector');

    $db_con = new sql_db();

    /*
     * General tests (one by one for each database)
     */

    // test a simple SQL user select query for PostgreSQL by name
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_USER);
    $db_con->set_usr(SYSTEM_USER_ID);
    $db_con->set_where(null,'Test User');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT user_id, user_name FROM users WHERE user_name = 'Test User';";
    $exe_start_time = test_show_result('PostgreSQL select max', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for MySQL
    $db_con->db_type = DB_TYPE_MYSQL;
    $db_con->set_type(DB_TYPE_USER);
    $db_con->set_usr(SYSTEM_USER_ID);
    $db_con->set_where(null,'Test User');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT user_id, user_name FROM users WHERE user_name = 'Test User';";
    $exe_start_time = test_show_result('MySQL select max', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test a simple SQL max select creation for PostgreSQL without where
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_VALUE);
    $db_con->set_fields(array('MAX(value_id) AS max_id'));
    $created_sql = $db_con->select(false);
    $expected_sql = "SELECT MAX(value_id) AS max_id FROM values;";
    $exe_start_time = test_show_result('PostgreSQL select max', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for MySQL
    $db_con->db_type = DB_TYPE_MYSQL;
    $db_con->set_type(DB_TYPE_VALUE);
    $db_con->set_fields(array('MAX(value_id) AS max_id'));
    $created_sql = $db_con->select(false);
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix. " MAX(value_id) AS max_id FROM `values`;";
    $exe_start_time = test_show_result('MySQL select max', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

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

    // test a simple SQL select of the user defined word for PostgreSQL by the id
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_WORD, true);
    $db_con->set_usr(1);
    $db_con->set_fields(array('plural','description','word_type_id','view_id'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = 'SELECT word_id,
                     word_name,
                     plural,
                     description,
                     word_type_id,
                     view_id
                FROM user_words
               WHERE word_id = 1 
                 AND user_id = 1;';
    $exe_start_time = test_show_result('PostgreSQL user word select based on id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for MySQL
    $db_con->db_type = DB_TYPE_MYSQL;
    $db_con->set_type(DB_TYPE_WORD, true);
    $db_con->set_usr(1);
    $db_con->set_fields(array('plural','description','word_type_id','view_id'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = 'SELECT word_id,
                     word_name,
                     plural,
                     description,
                     word_type_id,
                     view_id
                FROM user_words
               WHERE word_id = 1 
                 AND user_id = 1;';
    $exe_start_time = test_show_result('MySQL user word select based on id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test a very simple SQL select of the user defined word for PostgreSQL by the id
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_WORD, true);
    $db_con->set_usr(1);
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = 'SELECT word_id,
                     word_name
                FROM user_words
               WHERE word_id = 1 
                 AND user_id = 1;';
    $exe_start_time = test_show_result('PostgreSQL user word id select based on id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for MySQL
    $db_con->db_type = DB_TYPE_MYSQL;
    $db_con->set_type(DB_TYPE_WORD, true);
    $db_con->set_usr(1);
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $expected_sql = 'SELECT word_id,
                     word_name
                FROM user_words
               WHERE word_id = 1 
                 AND user_id = 1;';
    $exe_start_time = test_show_result('MySQL user word id select based on id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test a simple SQL select the formulas linked to a phrase
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_FORMULA_LINK);
    $db_con->set_link_fields('formula_id', 'phrase_id');
    $db_con->set_where_link(null,null, 1);
    $created_sql = $db_con->select();
    $expected_sql = 'SELECT 
                        formula_link_id,  
                        formula_id,  
                        phrase_id
                   FROM formula_links
                  WHERE phrase_id = 1;';
    $exe_start_time = test_show_result('PostgreSQL formulas linked to a phrase select based on phrase id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... same for MySQL
    $db_con->db_type = DB_TYPE_MYSQL;
    $db_con->set_type(DB_TYPE_FORMULA_LINK);
    $db_con->set_link_fields('formula_id', 'phrase_id');
    $db_con->set_where_link(null,null, 1);
    $created_sql = $db_con->select();
    $expected_sql = 'SELECT 
                    formula_link_id,  
                    formula_id,  
                    phrase_id
               FROM formula_links
              WHERE phrase_id = 1;';
    $exe_start_time = test_show_result('MySQL formulas linked to a phrase select based on phrase id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // test a list of links SQL select creation for PostgreSQL selected by a linked object
    /*
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_WORD_LINK);
    $db_con->set_join_fields(array('code_id', 'name_plural','name_reverse','name_plural_reverse','formula_name','description'), DB_TYPE_VERB);
    $db_con->set_where(2);
    $created_sql = $db_con->select();
    $expected_sql = "SELECT l.verb_id,
                     l.code_id,
                     l.verb_name,
                     l.name_plural,
                     l.name_reverse,
                     l.name_plural_reverse,
                     l.formula_name,
                     l.description
                FROM word_links s
           LEFT JOIN verbs ON s.verb_id = l.verb_id
                     ".$sql_where." 
            GROUP BY v.verb_id 
            ORDER BY v.verb_id;";
    $exe_start_time = test_show_result('PostgreSQL select based on id', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);
    */

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
    $db_con->set_usr_num_fields(array('formula_type_id', 'all_values_needed', 'last_update'));
    $db_con->set_usr_bool_fields(array('excluded'));
    $db_con->set_where(1, '');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT s.formula_id,
                       u.formula_id AS user_formula_id,
                       s.user_id,
                       CASE WHEN (u.formula_name      <> '' IS NOT TRUE) THEN s.formula_name         ELSE u.formula_name         END AS formula_name,
                       CASE WHEN (u.formula_text      <> '' IS NOT TRUE) THEN s.formula_text         ELSE u.formula_text         END AS formula_text,
                       CASE WHEN (u.resolved_text     <> '' IS NOT TRUE) THEN s.resolved_text        ELSE u.resolved_text        END AS resolved_text,
                       CASE WHEN (u.description       <> '' IS NOT TRUE) THEN s.description          ELSE u.description          END AS description,
                       CASE WHEN (u.formula_type_id   IS           NULL) THEN s.formula_type_id      ELSE u.formula_type_id      END AS formula_type_id,
                       CASE WHEN (u.all_values_needed IS           NULL) THEN s.all_values_needed    ELSE u.all_values_needed    END AS all_values_needed,
                       CASE WHEN (u.last_update       IS           NULL) THEN s.last_update          ELSE u.last_update          END AS last_update,
                       CASE WHEN (u.excluded          IS           NULL) THEN COALESCE(s.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded,
                       CASE WHEN (c.code_id           <> '' IS NOT TRUE) THEN l.code_id              ELSE c.code_id              END AS code_id
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
    $db_con->set_usr_num_fields(array('word_value', 'source_id', 'protection_type_id', 'last_update'));
    $db_con->set_usr_bool_fields(array('excluded'));
    $db_con->set_usr_only_fields(array('share_type_id'));
    $db_con->set_where_text('s.phrase_group_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                    s.value_id,
                    u.value_id AS user_value_id,
                    s.user_id,
                    s.phrase_group_id,
                    s.time_word_id,
                    CASE WHEN (u.word_value         IS           NULL) THEN s.word_value           ELSE u.word_value           END AS word_value,
                    CASE WHEN (u.source_id          IS           NULL) THEN s.source_id            ELSE u.source_id            END AS source_id,
                    CASE WHEN (u.protection_type_id IS           NULL) THEN s.protection_type_id   ELSE u.protection_type_id   END AS protection_type_id,
                    CASE WHEN (u.last_update        IS           NULL) THEN s.last_update          ELSE u.last_update          END AS last_update,
                    CASE WHEN (u.excluded           IS           NULL) THEN COALESCE(s.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded,
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
    $db_con->set_join_usr_fields(array('code_id'), DB_TYPE_VIEW_COMPONENT_TYPE);
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

    // test the verb_list load SQL creation
    $db_con->set_type(DB_TYPE_WORD_LINK);
    $db_con->set_usr_num_fields(array('excluded'));
    $db_con->set_join_fields(array('code_id','verb_name','name_plural','name_reverse','name_plural_reverse','formula_name','description'), DB_TYPE_VERB);
    $db_con->set_fields(array('verb_id'));
    $db_con->set_where_text('s.to_phrase_id = 2');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT 
                            s.word_link_id, 
                            u.word_link_id AS user_word_link_id, 
                            s.user_id, 
                            s.verb_id, 
                            l.code_id, 
                            l.verb_name, 
                            l.name_plural, 
                            l.name_reverse, 
                            l.name_plural_reverse, 
                            l.formula_name, 
                            l.description,
                            CASE WHEN (u.word_link_name <> '' IS NOT TRUE) THEN s.word_link_name ELSE u.word_link_name END AS word_link_name, 
                            CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END AS excluded 
                       FROM word_links s 
                  LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id 
                                             AND u.user_id = 1 
                  LEFT JOIN verbs l ON s.verb_id = l.verb_id 
                      WHERE s.to_phrase_id = 2;";
    $exe_start_time = test_show_result('PostgreSQL verb_list load', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " 
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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . "
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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . "
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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " 
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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " 
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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " s.word_link_id,
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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " 
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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " 
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
    $db_con->set_join_usr_fields(array('code_id'), DB_TYPE_VIEW_COMPONENT_TYPE);
    $db_con->set_usr_fields(array('comment'));
    $db_con->set_usr_num_fields(array('view_component_type_id', 'word_id_row','link_type_id','formula_id','word_id_col','word_id_col2','excluded'));
    $db_con->set_where(1);
    $created_sql = $db_con->select();
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " s.view_component_id,
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
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " 
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

    /*
     * Build sample queries in the PostgreSQL format to use the database syntax check of the IDE
     */

    // the formula list load query
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_from = 'formula_links l, formulas f';
    $sql_where = 'l.phrase_id = 1 AND l.formula_id = f.formula_id';
    $created_sql = "SELECT 
                       f.formula_id,
                       f.formula_name,
                    " . $db_con->get_usr_field('formula_text', 'f', 'u') . ",
                    " . $db_con->get_usr_field('resolved_text', 'f', 'u') . ",
                    " . $db_con->get_usr_field('description', 'f', 'u') . ",
                    " . $db_con->get_usr_field('formula_type_id', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('code_id', 't', 'c') . ",
                    " . $db_con->get_usr_field('all_values_needed', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('last_update', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('excluded', 'f', 'u', sql_db::FLD_FORMAT_VAL) . "
                  FROM " . $sql_from . " 
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = 1 
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE " . $sql_where . "
              GROUP BY f.formula_id;";
    $expected_sql = "SELECT f.formula_id,
                       f.formula_name,
                       CASE WHEN (u.formula_text      <> '' IS NOT TRUE) THEN f.formula_text      ELSE u.formula_text      END AS formula_text,
                       CASE WHEN (u.resolved_text     <> '' IS NOT TRUE) THEN f.resolved_text     ELSE u.resolved_text     END AS resolved_text,
                       CASE WHEN (u.description       <> '' IS NOT TRUE) THEN f.description       ELSE u.description       END AS description,
                       CASE WHEN (u.formula_type_id         IS     NULL) THEN f.formula_type_id   ELSE u.formula_type_id   END AS formula_type_id,
                       CASE WHEN (c.code_id           <> '' IS NOT TRUE) THEN t.code_id           ELSE c.code_id           END AS code_id,
                       CASE WHEN (u.all_values_needed       IS     NULL) THEN f.all_values_needed ELSE u.all_values_needed END AS all_values_needed,
                       CASE WHEN (u.last_update             IS     NULL) THEN f.last_update       ELSE u.last_update       END AS last_update,
                       CASE WHEN (u.excluded                IS     NULL) THEN f.excluded          ELSE u.excluded          END AS excluded
                  FROM formula_links l, formulas f 
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = 1 
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE l.phrase_id = 1 AND l.formula_id = f.formula_id
              GROUP BY f.formula_id;";
    $exe_start_time = test_show_result('formula list load query', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the value list load query
    $db_con->db_type = DB_TYPE_POSTGRES;
    $limit = 10;
    $created_sql = "SELECT v.value_id,
                     u.value_id AS user_value_id,
                     v.user_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('excluded', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('last_update', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('source_id', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                     v.phrase_group_id,
                     v.time_word_id,
                     g.word_ids,
                     g.triple_ids
                FROM phrase_groups g, " . $db_con->get_table_name(DB_TYPE_VALUE) . " v 
           LEFT JOIN user_values u ON u.value_id = v.value_id 
                                  AND u.user_id = 1
               WHERE g.phrase_group_id = v.phrase_group_id 
                 AND v.value_id IN ( SELECT value_id 
                                       FROM value_phrase_links 
                                      WHERE phrase_id = 1
                                   GROUP BY value_id )
            ORDER BY v.phrase_group_id, v.time_word_id
               LIMIT ".$limit.";";
    $expected_sql = "SELECT v.value_id,
                     u.value_id AS user_value_id,
                     v.user_id,
                     CASE WHEN (u.word_value  IS NULL) THEN v.word_value  ELSE u.word_value  END AS word_value,
                     CASE WHEN (u.excluded    IS NULL) THEN v.excluded    ELSE u.excluded    END AS excluded,
                     CASE WHEN (u.last_update IS NULL) THEN v.last_update ELSE u.last_update END AS last_update,
                     CASE WHEN (u.source_id   IS NULL) THEN v.source_id   ELSE u.source_id   END AS source_id,
                     v.phrase_group_id,
                     v.time_word_id,
                     g.word_ids,
                     g.triple_ids
                FROM phrase_groups g, values v 
           LEFT JOIN user_values u ON u.value_id = v.value_id 
                                  AND u.user_id = 1 
               WHERE g.phrase_group_id = v.phrase_group_id 
                 AND v.value_id IN ( SELECT value_id 
                                       FROM value_phrase_links 
                                      WHERE phrase_id = 1
                                   GROUP BY value_id )
            ORDER BY v.phrase_group_id, v.time_word_id
               LIMIT 10;";
    $exe_start_time = test_show_result('value list load query', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the phrase load word query
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = 'SELECT w.word_id AS id, 
                    ' . $db_con->get_usr_field("word_name", "w", "u") . ',
                    ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                      FROM words w   
                 LEFT JOIN user_words u ON u.word_id = w.word_id
                                       AND u.user_id = 1
                  GROUP BY word_name ;';
    $expected_sql = "SELECT w.word_id AS id, 
                       CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name    ELSE          u.word_name   END AS word_name,
                       CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM words w   
                  LEFT JOIN user_words u ON u.word_id = w.word_id
                                        AND u.user_id = 1
                   GROUP BY word_name ;";
    $exe_start_time = test_show_result('phrase load word query', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the phrase load word link query
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = 'SELECT l.word_link_id * -1 AS id,
                    ' . $db_con->get_usr_field("word_link_name", "l", "u") . ',
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                      FROM word_links l
                 LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                            AND u.user_id = 1
                  GROUP BY word_link_name ;';
    $expected_sql = "SELECT l.word_link_id * -1 AS id,
                       CASE WHEN (u.word_link_name  <> '' IS NOT TRUE) THEN          l.word_link_name ELSE          u.word_link_name   END AS word_link_name,
                       CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM word_links l
                 LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                            AND u.user_id = 1
                  GROUP BY word_link_name ;";
    $exe_start_time = test_show_result('phrase load word link query', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the phrase load word link query by type
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_where_exclude = '(excluded <> 1 OR excluded is NULL)';
    $created_sql = 'SELECT from_phrase_id FROM (
                        SELECT DISTINCT
                               l.from_phrase_id,    
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = 1
                         WHERE l.to_phrase_id = 2 
                           AND l.verb_id = 2 ) AS a 
                         WHERE ' . $sql_where_exclude . ';';
    $expected_sql = "SELECT from_phrase_id FROM (
                        SELECT DISTINCT
                               l.from_phrase_id,    
                    CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(l.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = 1
                         WHERE l.to_phrase_id = 2 
                           AND l.verb_id = 2 ) AS a 
                         WHERE (excluded <> 1 OR excluded is NULL);";
    $exe_start_time = test_show_result('phrase load word link query by type', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the view component link query by type (used in word_display->assign_dsp_ids)
    $db_con->db_type = DB_TYPE_POSTGRES;
    $db_con->set_type(DB_TYPE_VIEW_COMPONENT_LINK);
    //$db_con->set_join_fields(array('position_type'), 'position_type');
    $db_con->set_fields(array('view_id','view_component_id'));
    $db_con->set_usr_num_fields(array('order_nbr','position_type','excluded'));
    $db_con->set_where_text('s.view_component_id = 1');
    $created_sql = $db_con->select();
    $expected_sql = "SELECT s.view_component_link_id,
                     u.view_component_link_id AS user_view_component_link_id,
                     s.user_id,
                     s.view_id, 
                     s.view_component_id,
                     CASE WHEN (u.order_nbr   IS NULL) THEN s.order_nbr   ELSE u.order_nbr   END AS order_nbr,
                     CASE WHEN (u.position_type   IS NULL) THEN s.position_type   ELSE u.position_type   END AS position_type,
                     CASE WHEN (u.excluded   IS NULL) THEN s.excluded   ELSE u.excluded   END AS excluded
                FROM view_component_links s
           LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id 
                                            AND u.user_id = 1  
               WHERE s.view_component_id = 1;";
    $exe_start_time = test_show_result('phrase load word link query by type', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the view component link max order number query (used in word_display->next_nbr)
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_avoid_code_check_prefix = "SELECT";
    $created_sql = $sql_avoid_code_check_prefix . " max(m.order_nbr) AS max_order_nbr
                FROM ( SELECT 
                              " . $db_con->get_usr_field("order_nbr", "l", "u", sql_db::FLD_FORMAT_VAL) . " 
                          FROM view_component_links l 
                    LEFT JOIN user_view_component_links u ON u.view_component_link_id = l.view_component_link_id 
                                                      AND u.user_id = 1 
                        WHERE l.view_id = 1 ) AS m;";
    $expected_sql = "SELECT max(m.order_nbr) AS max_order_nbr
                       FROM ( SELECT CASE WHEN (u.order_nbr   IS NULL) THEN l.order_nbr   ELSE u.order_nbr   END AS order_nbr
                                FROM view_component_links l 
                           LEFT JOIN user_view_component_links u ON u.view_component_link_id = l.view_component_link_id 
                                                                AND u.user_id = 1
                               WHERE l.view_id = 1 ) AS m;";
    $exe_start_time = test_show_result('phrase load word link query by type', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the phrase load word link query by phrase
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_field_names = 'id, name, excluded';
    $sql_where_exclude = '(excluded <> 1 OR excluded is NULL)';
    $sql_wrd_all = 'SELECT from_phrase_id AS id FROM (
                        SELECT DISTINCT
                               l.from_phrase_id,    
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = 1
                         WHERE l.to_phrase_id = 1 
                           AND l.verb_id = 2 ) AS a 
                         WHERE ' . $sql_where_exclude . '  ';
    $sql_wrd_other = 'SELECT from_phrase_id FROM (
                          SELECT DISTINCT
                                 l.from_phrase_id,    
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                            FROM word_links l
                       LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                  AND u.user_id = 1
                           WHERE l.to_phrase_id <> 1 
                             AND l.verb_id = 2
                             AND l.from_phrase_id IN ( ' . $sql_wrd_all . ' )  
                        GROUP BY l.from_phrase_id ) AS o 
                           WHERE ' . $sql_where_exclude . ' ';
    $created_sql = 'SELECT ' . $sql_field_names . ' FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM ( ' . $sql_wrd_all . ' ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = 1
                       WHERE w.word_id NOT IN ( ' . $sql_wrd_other . ')                                        
                         AND w.word_id = a.id    
                    GROUP BY word_name ) AS w 
                       WHERE ' . $sql_where_exclude . ';';
    $expected_sql = "SELECT id, name, excluded FROM (
                         SELECT DISTINCT
                                w.word_id AS id, 
                                CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name ELSE          u.word_name   END AS name,
                                CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                           FROM ( SELECT from_phrase_id AS id FROM (
                                      SELECT DISTINCT
                                             l.from_phrase_id,
                                             CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
                                        FROM word_links l LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                              AND u.user_id = 1
                                       WHERE l.to_phrase_id = 1 
                                         AND l.verb_id = 2 ) AS a 
                         WHERE (excluded <> 1 OR excluded is NULL)  ) a, words w LEFT JOIN user_words u ON u.word_id = w.word_id 
                                                                   AND u.user_id = 1
                          WHERE w.word_id NOT IN ( SELECT from_phrase_id FROM (
                                                       SELECT DISTINCT
                                                              l.from_phrase_id,    
                                                              CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(l.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                                                         FROM word_links l LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                                                      AND u.user_id = 1
                                                        WHERE l.to_phrase_id <> 1 
                                                          AND l.verb_id = 2
                                                          AND l.from_phrase_id IN ( SELECT from_phrase_id AS id FROM (
                                                                                            SELECT DISTINCT
                                                                                                   l.from_phrase_id,
                                                                                                   CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
                                                                                              FROM word_links l LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                                                                                           AND u.user_id = 1
                                                                                             WHERE l.to_phrase_id = 1 
                                                                                               AND l.verb_id = 2 ) AS a 
                                                                                     WHERE (excluded <> 1 OR excluded is NULL)  
                                                                                  )  
                                                     GROUP BY l.from_phrase_id ) AS o 
                                                        WHERE (excluded <> 1 OR excluded is NULL)
                                                 )                                        
                            AND w.word_id = a.id    
                       GROUP BY word_name ) AS w 
                    WHERE (excluded <> 1 OR excluded is NULL);";
    $exe_start_time = test_show_result('phrase load word link query by type', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the time word selector query by type (used in word_display->dsp_time_selector)
    // $sql_avoid_code_check_prefix is used to avoid SQL code checks by the IDE on the query building process,
    // which is not needed because the check is done on the $expected_sql and the $created_sql is compared with the checked
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_from = "word_links l, words w";
    $sql_where_and = "AND w.word_id = l.from_phrase_id 
                        AND l.verb_id = 2             
                        AND l.to_phrase_id = 14 ";
    $sql_avoid_code_check_prefix = "SELECT";
    $created_sql = $sql_avoid_code_check_prefix . " id, name 
              FROM ( SELECT w.word_id AS id, 
                            " . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ",    
                            " . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . "
                       FROM " . $sql_from . "   
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                                        AND u.user_id = 1 
                      WHERE w.word_type_id = 2
                        " . $sql_where_and . "            
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)                                    
          ORDER BY name;";
    $expected_sql = "SELECT id, name 
              FROM ( SELECT w.word_id AS id, 
                                CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name ELSE          u.word_name   END AS name,
                                CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM word_links l, words w   
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                                        AND u.user_id = 1 
                      WHERE w.word_type_id = 2
                        AND w.word_id = l.from_phrase_id 
                        AND l.verb_id = 2              
                        AND l.to_phrase_id = 14            
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)                                    
          ORDER BY name;";
    $exe_start_time = test_show_result('time word selector query by type', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the verb selector query (used in word_display->selector_link)
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_name = "CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name";
    $sql_avoid_code_check_prefix = "SELECT";
    $created_sql = $sql_avoid_code_check_prefix . " * FROM (
            SELECT verb_id AS id, 
                   " . $sql_name . ",
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
    $expected_sql = "SELECT * FROM (
            SELECT verb_id AS id, 
                   CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name,
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
    $exe_start_time = test_show_result('verb selector query', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the word link list load query (used in word_link_list->load)
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_where = 'l.to_phrase_id   = 3';
    $sql_type = 'AND l.verb_id = 2';
    $sql_wrd1_fields = '';
    $sql_wrd1_from = '';
    $sql_wrd1 = '';
    $sql_wrd2_fields = "t2.word_id AS word_id2,
                t2.user_id AS user_id2,
                 CASE WHEN (u2.word_name <> '' IS NOT TRUE) THEN t2.word_name ELSE u2.word_name END AS word_name,
                 CASE WHEN (u2.plural <> '' IS NOT TRUE) THEN t2.plural ELSE u2.plural END AS plural,
                 CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description ELSE u2.description END AS description,
                 CASE WHEN (u2.word_type_id IS NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id,
                 CASE WHEN (u2.excluded IS NULL) THEN t2.excluded ELSE u2.excluded END AS excluded,
                  t2.values AS values2";
    $sql_wrd2_from = ' words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id 
                                                       AND u2.user_id = 1 ';
    $sql_wrd2 = 'l.from_phrase_id = t2.word_id';
    $created_sql = "SELECT l.word_link_id,
                       l.from_phrase_id,
                       l.verb_id,
                       l.to_phrase_id,
                       l.description,
                       l.word_link_name,
                       v.verb_id,
                       v.code_id,
                       v.verb_name,
                       v.name_plural,
                       v.name_reverse,
                       v.name_plural_reverse,
                       v.formula_name,
                       v.description,
                       " . $db_con->get_usr_field('excluded', 'l', 'ul', sql_db::FLD_FORMAT_VAL) . ",
                       " . $sql_wrd1_fields . "
                       " . $sql_wrd2_fields . "
                  FROM word_links l
             LEFT JOIN user_word_links ul ON ul.word_link_id = l.word_link_id 
                                        AND ul.user_id = 1,
                       verbs v, 
                       " . $sql_wrd1_from . "
                       " . $sql_wrd2_from . "
                 WHERE l.verb_id = v.verb_id 
                       " . $sql_wrd1 . "
                   AND " . $sql_wrd2 . " 
                   AND " . $sql_where . "
                       " . $sql_type . " 
              GROUP BY t2.word_id, l.verb_id
              ORDER BY l.verb_id, word_name;";
    $expected_sql = "SELECT l.word_link_id,
                       l.from_phrase_id,
                       l.verb_id,
                       l.to_phrase_id,
                       l.description,
                       l.word_link_name,
                       v.verb_id,
                       v.code_id,
                       v.verb_name,
                       v.name_plural,
                       v.name_reverse,
                       v.name_plural_reverse,
                       v.formula_name,
                       v.description,
                       CASE WHEN (ul.excluded          IS     NULL) THEN l.excluded      ELSE ul.excluded     END AS excluded,
                       t2.word_id AS word_id2,
                       t2.user_id AS user_id2,
                       CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name,
                       CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural,
                       CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description,
                       CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id,
                       CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded,
                       t2.values AS values2
                  FROM word_links l
             LEFT JOIN user_word_links ul ON ul.word_link_id = l.word_link_id 
                                        AND ul.user_id = 1,
                       verbs v, 
                       words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id 
                                                       AND u2.user_id = 1 
                 WHERE l.verb_id = v.verb_id 
                   AND l.from_phrase_id = t2.word_id 
                   AND l.to_phrase_id   = 3
                       AND l.verb_id = 2 
              GROUP BY t2.word_id, l.verb_id
              ORDER BY l.verb_id, word_name;";
    $exe_start_time = test_show_result('word link list load query', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the phrase load word link query by ...
    // TODO check if and how GROUP BY t2.word_id, l.verb_id can / should be added
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_where = 'l.to_phrase_id   = 3';
    $sql_type = 'AND l.verb_id = 2';
    $sql_wrd1_fields = '';
    $sql_wrd1_from = '';
    $sql_wrd1 = '';
    $sql_wrd2_fields = "t2.word_id AS word_id2,
                t2.user_id AS user_id2,
                 CASE WHEN (u2.word_name <> '' IS NOT TRUE) THEN t2.word_name ELSE u2.word_name END AS word_name,
                 CASE WHEN (u2.plural <> '' IS NOT TRUE) THEN t2.plural ELSE u2.plural END AS plural,
                 CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description ELSE u2.description END AS description,
                 CASE WHEN (u2.word_type_id IS NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id,
                 CASE WHEN (u2.excluded IS NULL) THEN t2.excluded ELSE u2.excluded END AS excluded,
                  t2.values AS values2";
    $sql_wrd2_from = ' words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id 
                                                       AND u2.user_id = 1 ';
    $sql_wrd2 = 'l.from_phrase_id = t2.word_id';
    $created_sql = "SELECT l.word_link_id,
                       l.from_phrase_id,
                       l.verb_id,
                       l.to_phrase_id,
                       l.description,
                       l.word_link_name,
                       v.verb_id,
                       v.code_id,
                       v.verb_name,
                       v.name_plural,
                       v.name_reverse,
                       v.name_plural_reverse,
                       v.formula_name,
                       v.description,
                       " . $db_con->get_usr_field('excluded', 'l', 'ul', sql_db::FLD_FORMAT_VAL) . ",
                       " . $sql_wrd1_fields . "
                       " . $sql_wrd2_fields . "
                  FROM word_links l
             LEFT JOIN user_word_links ul ON ul.word_link_id = l.word_link_id 
                                        AND ul.user_id = 1,
                       verbs v, 
                       " . $sql_wrd1_from . "
                       " . $sql_wrd2_from . "
                 WHERE l.verb_id = v.verb_id 
                       " . $sql_wrd1 . "
                   AND " . $sql_wrd2 . " 
                   AND " . $sql_where . "
                       " . $sql_type . " 
              ORDER BY l.verb_id, word_name;";
    $expected_sql = "SELECT l.word_link_id,
                            l.from_phrase_id,                       
                            l.verb_id,                       
                            l.to_phrase_id,                       
                            l.description,
                            l.word_link_name,
                            v.verb_id,
                            v.code_id,
                            v.verb_name,
                            v.name_plural,
                            v.name_reverse,
                            v.name_plural_reverse,
                            v.formula_name,
                            v.description,
                            CASE WHEN (ul.excluded          IS     NULL) THEN l.excluded      ELSE ul.excluded     END AS excluded,
                            t2.word_id AS word_id2,
                            t2.user_id AS user_id2,
                            CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name,
                            CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural,
                            CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description,
                            CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id,
                            CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded,
                            t2.values AS values2                  
                       FROM word_links l LEFT JOIN user_word_links ul ON ul.word_link_id = l.word_link_id
                                                                     AND ul.user_id = 1,
                            verbs v,
                            words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id
                                                            AND u2.user_id = 1
                      WHERE l.verb_id = v.verb_id
                        AND l.from_phrase_id = t2.word_id
                        AND l.to_phrase_id   = 3
                        AND l.verb_id = 2
                   ORDER BY l.verb_id, word_name;";
    $exe_start_time = test_show_result('phrase load word link query by ...', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the general phrase list query (as created in phrase->sql_list)
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_words = 'SELECT DISTINCT w.word_id AS id, 
                                  ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                                  ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                             FROM words w   
                        LEFT JOIN user_words u ON u.word_id = w.word_id 
                                              AND u.user_id = 1 ';
    $sql_triples = 'SELECT DISTINCT l.word_link_id * -1 AS id, 
                                    ' . $db_con->get_usr_field("word_link_name", "l", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                               FROM word_links l
                          LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                     AND u.user_id = 1 ';
    $sql_avoid_code_check_prefix = "SELECT";
    $created_sql = $sql_avoid_code_check_prefix . " DISTINCT id, phrase_name
              FROM ( " . $sql_words . " UNION " . $sql_triples . " ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
    $expected_sql = "SELECT DISTINCT 
                            id, phrase_name
              FROM ( SELECT DISTINCT
                            w.word_id AS id, 
                            CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN w.word_name            ELSE u.word_name            END AS phrase_name,
                            CASE WHEN (u.excluded        IS     NULL) THEN COALESCE(w.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM words w   
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                                        AND u.user_id = 1
               UNION SELECT DISTINCT
                            l.word_link_id * -1 AS id, 
                            CASE WHEN (u.word_link_name   <> '' IS NOT TRUE) THEN l.word_link_name       ELSE u.word_link_name       END AS phrase_name,
                            CASE WHEN (u.excluded               IS     NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM word_links l
                  LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                             AND u.user_id = 1
                   ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
    $exe_start_time = test_show_result('general phrase list query', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // the general phrase list query by type (as created in phrase->sql_list)
    $db_con->db_type = DB_TYPE_POSTGRES;
    $sql_where_exclude = 'excluded = 0';
    $sql_field_names = 'id, phrase_name, excluded';
    $sql_wrd_all = 'SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id = 2
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE ' . $sql_where_exclude . ' ';
    $sql_wrd_other = 'SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id <> 2
                                           AND l.verb_id = 2
                                           AND l.from_phrase_id IN (' . $sql_wrd_all . ') ) AS o 
                                         WHERE ' . $sql_where_exclude . ' ';
    $sql_words = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM ( ' . $sql_wrd_all . ' ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = 1
                       WHERE w.word_id NOT IN ( ' . $sql_wrd_other . ' )                                        
                         AND w.word_id = a.id ) AS w 
                       WHERE ' . $sql_where_exclude . ' ';
    $sql_triples = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                        SELECT DISTINCT
                               l.word_link_id * -1 AS id, 
                               ' . $db_con->get_usr_field("word_link_name", "l", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = 1
                         WHERE l.from_phrase_id IN ( ' . $sql_wrd_other . ')                                        
                           AND l.verb_id = 2
                           AND l.to_phrase_id = 2 ) AS t 
                         WHERE ' . $sql_where_exclude . ' ';
    $sql_avoid_code_check_prefix = "SELECT";
    $created_sql = $sql_avoid_code_check_prefix . " DISTINCT id, phrase_name
              FROM ( " . $sql_words . " UNION " . $sql_triples . " ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
    $expected_sql = "SELECT DISTINCT id, phrase_name
              FROM ( SELECT DISTINCT id, phrase_name, excluded FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                              CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN w.word_name            ELSE u.word_name            END AS phrase_name,
                              CASE WHEN (u.excluded        IS     NULL) THEN COALESCE(w.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                        FROM ( SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id = 2 
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE excluded = 0  ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = 1
                       WHERE w.word_id NOT IN ( SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id <> 2 
                                           AND l.verb_id = 2
                                           AND l.from_phrase_id IN (SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id = 2 
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE excluded = 0 ) ) AS o 
                                         WHERE excluded = 0  )                                        
                         AND w.word_id = a.id ) AS w 
                       WHERE excluded = 0  UNION SELECT DISTINCT id, phrase_name, excluded FROM (
                        SELECT DISTINCT
                               l.word_link_id * -1 AS id, 
                                CASE WHEN (u.word_link_name <> '' IS NOT TRUE) THEN l.word_link_name       ELSE u.word_link_name       END AS phrase_name,
                                CASE WHEN (u.excluded             IS     NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = 1
                         WHERE l.from_phrase_id IN ( SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id <> 2 
                                           AND l.verb_id = 2
                                           AND l.from_phrase_id IN (SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id = 2 
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE excluded = 0 ) ) AS o 
                                         WHERE excluded = 0 )                                        
                           AND l.verb_id = 2
                           AND l.to_phrase_id = 2 ) AS t 
                         WHERE excluded = 0  ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
    $exe_start_time = test_show_result('general phrase list query by type', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

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