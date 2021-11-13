<?php

/*

  test/unit/word_list.php - TESTing of the WORD LIST functions
  -----------------------
  

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

function run_word_list_unit_tests(testing $t)
{

    global $usr;
    global $sql_names;

    $t->header('Unit tests of the word list class (src/main/php/model/word/word_list.php)');

    /*
     * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
     */

    $db_con = new sql_db();
    $db_con->db_type = sql_db::POSTGRES;

    // sql to load by word list by ids
    $wrd_lst = new word_list;
    $wrd_lst->ids = [1, 2, 3];
    $wrd_lst->usr = $usr;
    $created_sql = $wrd_lst->load_sql($db_con);
    $expected_sql = "SELECT 
                        s.word_id,
                        u.word_id AS user_word_id,
                        s.user_id,
                        s.values,
                        CASE WHEN (u.word_name    <> '' IS NOT TRUE) THEN s.word_name    ELSE u.word_name    END AS word_name,
                        CASE WHEN (u.plural       <> '' IS NOT TRUE) THEN s.plural       ELSE u.plural       END AS plural,
                        CASE WHEN (u.description  <> '' IS NOT TRUE) THEN s.description  ELSE u.description  END AS description,
                        CASE WHEN (u.word_type_id IS           NULL) THEN s.word_type_id ELSE u.word_type_id END AS word_type_id,
                        CASE WHEN (u.excluded     IS           NULL) THEN s.excluded     ELSE u.excluded     END AS excluded
                   FROM words s 
              LEFT JOIN user_words u ON s.word_id = u.word_id 
                                    AND u.user_id = 1 
                  WHERE s.word_id IN (1,2,3)
               ORDER BY s.values DESC, word_name;";
    $t->dsp('word_list->load_sql by IDs', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lst->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_list->load_sql_name by IDs', $result, $target);

    // ... and the same for MySQL by replication the SQL builder statements
    $db_con->db_type = sql_db::MYSQL;
    /*
    $db_con->set_type(DB_TYPE_WORD);
    $db_con->set_usr($usr->id);
    $db_con->set_usr_fields(array('plural',sql_db::FLD_DESCRIPTION));
    $db_con->set_usr_num_fields(array('word_type_id',user_sandbox::FLD_EXCLUDED));
    $db_con->set_fields(array('values'));
    $db_con->set_where_text('s.word_id IN (1,2,3)');
    $db_con->set_order_text('s.values DESC, word_name');
    $created_sql = $db_con->select();
    */
    $created_sql = $wrd_lst->load_sql($db_con);
    $sql_avoid_code_check_prefix = "SELECT";
    $expected_sql = $sql_avoid_code_check_prefix . " s.word_id,
                        u.word_id AS user_word_id,
                        s.user_id,
                        s.`values`,
                        IF(u.word_name    IS NULL,  s.word_name,     u.word_name)     AS word_name,
                        IF(u.plural       IS NULL,  s.plural,        u.plural)        AS plural,
                        IF(u.description  IS NULL,  s.description,   u.description)   AS description,
                        IF(u.word_type_id IS NULL,  s.word_type_id,  u.word_type_id)  AS word_type_id,
                        IF(u.excluded     IS NULL,  s.excluded,      u.excluded)      AS excluded
                   FROM words s
              LEFT JOIN user_words u ON s.word_id = u.word_id 
                                    AND u.user_id = 1 
                  WHERE s.word_id IN (1,2,3)
               ORDER BY s.values DESC, word_name;";
    $t->dsp('word_list->load_sql by IDs', zu_trim($expected_sql), zu_trim($created_sql));

    // sql to load by word list by phrase group
    $db_con->db_type = sql_db::POSTGRES;
    $wrd_lst = new word_list;
    $wrd_lst->grp_id = 1;
    $wrd_lst->usr = $usr;
    $created_sql = $wrd_lst->load_sql($db_con);
    $expected_sql = "SELECT 
                        s.word_id,
                        u.word_id AS user_word_id,
                        s.user_id,
                        s.values,
                        CASE WHEN (u.word_name    <> '' IS NOT TRUE) THEN s.word_name    ELSE u.word_name    END AS word_name,
                        CASE WHEN (u.plural       <> '' IS NOT TRUE) THEN s.plural       ELSE u.plural       END AS plural,
                        CASE WHEN (u.description  <> '' IS NOT TRUE) THEN s.description  ELSE u.description  END AS description,
                        CASE WHEN (u.word_type_id IS           NULL) THEN s.word_type_id ELSE u.word_type_id END AS word_type_id,
                        CASE WHEN (u.excluded     IS           NULL) THEN s.excluded     ELSE u.excluded     END AS excluded
                   FROM words s 
              LEFT JOIN user_words u ON s.word_id = u.word_id 
                                    AND u.user_id = 1 
                  WHERE s.word_id IN ( SELECT word_id 
                                         FROM phrase_group_word_links
                                        WHERE phrase_group_id = 1)
               ORDER BY s.values DESC, word_name;";
    $t->dsp('word_list->load_sql by phrase group', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lst->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_list->load_sql_name by phrase group', $result, $target);

    // TODO add the missing word list loading SQL

    // SQL to add by word list by a relation e.g. for "Zurich" and direction "up" add "City", "Canton" and "Company"
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->ids = [7];
    $created_sql = $wrd_lst->add_by_type_sql($db_con, 2, verb::DIRECTION_UP);
    $expected_sql = "SELECT s.word_id,
                     s.user_id,
                     CASE WHEN (u.word_name <> ''   IS NOT TRUE) THEN s.word_name    ELSE u.word_name    END AS word_name,
                     CASE WHEN (u.plural <> ''      IS NOT TRUE) THEN s.plural       ELSE u.plural       END AS plural,
                     CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description  ELSE u.description  END AS description,
                     CASE WHEN (u.word_type_id      IS     NULL) THEN s.word_type_id ELSE u.word_type_id END AS word_type_id,
                     CASE WHEN (u.excluded          IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded,
                     l.verb_id,
                     s.values
                FROM word_links l, 
                     words s 
           LEFT JOIN user_words u ON s.word_id = u.word_id 
                                 AND u.user_id = 1 
               WHERE l.to_phrase_id = s.word_id 
                 AND l.from_phrase_id IN (7)
                 AND l.verb_id = 2 
            ORDER BY s.values DESC, s.word_name;";
    $t->dsp('word_list->add_by_type_sql by verb and up', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lst->add_by_type_sql($db_con, 2, verb::DIRECTION_UP,true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_list->add_by_type_sql by verb and up', $result, $target);



}

