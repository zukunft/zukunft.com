<?php

/*

  test/unit/word_link_list.php - TESTing of the WORD LINK LIST functions
  ----------------------------
  

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

function run_word_link_list_unit_tests()
{

    global $usr;
    global $exe_start_time;
    global $sql_names;

    test_header('Unit tests of the word link list class (src/main/php/model/word/word_link_list.php)');

    /*
     * SQL creation tests (mainly to use the IDE check for the generated SQL statements
     */

    // sql to load by word link list by ids
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->ids = [1, 2, 3];
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql();
    $expected_sql = "SELECT 
                          l.word_link_id,
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
                          t.word_id AS word_id,
                          t.user_id AS user_id,
                          CASE WHEN (u.word_name    <> '' IS NOT TRUE) THEN t.word_name     ELSE u.word_name     END AS word_name,
                          CASE WHEN (u.plural       <> '' IS NOT TRUE) THEN t.plural        ELSE u.plural        END AS plural,
                          CASE WHEN (u.description  <> '' IS NOT TRUE) THEN t.description   ELSE u.description   END AS description,
                          CASE WHEN (u.word_type_id       IS     NULL) THEN t.word_type_id  ELSE u.word_type_id  END AS word_type_id,
                          CASE WHEN (u.excluded           IS     NULL) THEN t.excluded      ELSE u.excluded      END AS excluded,
                          t.values AS values, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t      LEFT JOIN user_words u        ON u.word_id  = t.word_id 
                                                                    AND u.user_id  = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.from_phrase_id = t.word_id
                      AND l.to_phrase_id   = t2.word_id 
                      AND l.word_link_id  IN (1,2,3)                        
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $exe_start_time = test_show_result('word_link_list->load_sql by IDs', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('word_link_list->load_sql_name by IDs', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load by word link list by word and up
    $wrd = new word();
    $wrd->id = 1;
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd = $wrd;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_UP;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql();
    $expected_sql = "SELECT 
                          l.word_link_id,
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
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.to_phrase_id   = t2.word_id 
                      AND l.from_phrase_id = 1                        
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $exe_start_time = test_show_result('word_link_list->load_sql by word and up', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('word_link_list->load_sql_name by word and up', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load by word link list by word and down
    $wrd = new word();
    $wrd->id = 2;
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd = $wrd;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql();
    $expected_sql = "SELECT 
                          l.word_link_id,
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
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.from_phrase_id   = t2.word_id 
                      AND l.to_phrase_id = 2                        
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $exe_start_time = test_show_result('word_link_list->load_sql by word and down', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('word_link_list->load_sql_name by word and down', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load by word link list by word list and up
    $wrd_lst = new word_list();
    $wrd_lst->ids = [1, 2];
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd_lst = $wrd_lst;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_UP;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql();
    $expected_sql = "SELECT 
                          l.word_link_id,
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
                          t.word_id AS word_id,
                          t.user_id AS user_id,
                          CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN t.word_name ELSE u.word_name END AS word_name,
                          CASE WHEN (u.plural <> '' IS NOT TRUE) THEN t.plural ELSE u.plural END AS plural,
                          CASE WHEN (u.description <> '' IS NOT TRUE) THEN t.description ELSE u.description END AS description,
                          CASE WHEN (u.word_type_id IS NULL) THEN t.word_type_id ELSE u.word_type_id END AS word_type_id,
                          CASE WHEN (u.excluded IS NULL) THEN t.excluded ELSE u.excluded END AS excluded,
                          t.values AS values, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t      LEFT JOIN user_words u        ON u.word_id = t.word_id 
                                                                    AND u.user_id = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.from_phrase_id = t.word_id
                      AND l.to_phrase_id   = t2.word_id 
                      AND l.from_phrase_id IN (1,2)
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $exe_start_time = test_show_result('word_link_list->load_sql by word list and up', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('word_link_list->load_sql_name by word list and up', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load by word link list by word list and down
    $wrd_lst = new word_list();
    $wrd_lst->ids = [2, 3];
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd_lst = $wrd_lst;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql();
    $expected_sql = "SELECT 
                          l.word_link_id,
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
                          t.word_id AS word_id,
                          t.user_id AS user_id,
                          CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN t.word_name ELSE u.word_name END AS word_name,
                          CASE WHEN (u.plural <> '' IS NOT TRUE) THEN t.plural ELSE u.plural END AS plural,
                          CASE WHEN (u.description <> '' IS NOT TRUE) THEN t.description ELSE u.description END AS description,
                          CASE WHEN (u.word_type_id IS NULL) THEN t.word_type_id ELSE u.word_type_id END AS word_type_id,
                          CASE WHEN (u.excluded IS NULL) THEN t.excluded ELSE u.excluded END AS excluded,
                          t.values AS values, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t      LEFT JOIN user_words u        ON u.word_id = t.word_id 
                                                                    AND u.user_id = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.to_phrase_id   = t.word_id
                      AND l.from_phrase_id = t2.word_id 
                      AND l.to_phrase_id   IN (2,3)
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $exe_start_time = test_show_result('word_link_list->load_sql by word list and down', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('word_link_list->load_sql_name by word list and down', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load by word link list by word list and down filtered by a verb
    $wrd_lst = new word_list();
    $wrd_lst->ids = [2, 3];
    $vrb = new verb();
    $vrb->id = 2;
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd_lst = $wrd_lst;
    $wrd_lnk_lst->vrb = $vrb;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql();
    $expected_sql = "SELECT 
                          l.word_link_id,
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
                          t.word_id AS word_id,
                          t.user_id AS user_id,
                          CASE WHEN (u.word_name <> ''    IS NOT TRUE) THEN t.word_name     ELSE u.word_name     END AS word_name,
                          CASE WHEN (u.plural <> ''       IS NOT TRUE) THEN t.plural        ELSE u.plural        END AS plural,
                          CASE WHEN (u.description <> ''  IS NOT TRUE) THEN t.description   ELSE u.description   END AS description,
                          CASE WHEN (u.word_type_id       IS     NULL) THEN t.word_type_id  ELSE u.word_type_id  END AS word_type_id,
                          CASE WHEN (u.excluded           IS     NULL) THEN t.excluded      ELSE u.excluded      END AS excluded,
                          t.values AS values, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t      LEFT JOIN user_words u        ON u.word_id = t.word_id 
                                                                    AND u.user_id = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.to_phrase_id   = t.word_id
                      AND l.from_phrase_id = t2.word_id 
                      AND l.to_phrase_id   IN (2,3)
                      AND l.verb_id = 2 
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $exe_start_time = test_show_result('word_link_list->load_sql by word list and down filtered by a verb', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('word_link_list->load_sql_name by word list and down filtered by a verb', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

    // sql to load by word link list by word list and down filtered by a verb list
    $wrd_lst = new word_list();
    $wrd_lst->ids = [2, 3];
    $vrb_lst = new verb_list();
    $vrb_lst->ids = [1, 2];
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd_lst = $wrd_lst;
    $wrd_lnk_lst->vrb_lst = $vrb_lst;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql();
    $expected_sql = "SELECT 
                          l.word_link_id,
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
                          t.word_id AS word_id,
                          t.user_id AS user_id,
                          CASE WHEN (u.word_name <> ''    IS NOT TRUE) THEN t.word_name     ELSE u.word_name     END AS word_name,
                          CASE WHEN (u.plural <> ''       IS NOT TRUE) THEN t.plural        ELSE u.plural        END AS plural,
                          CASE WHEN (u.description <> ''  IS NOT TRUE) THEN t.description   ELSE u.description   END AS description,
                          CASE WHEN (u.word_type_id       IS     NULL) THEN t.word_type_id  ELSE u.word_type_id  END AS word_type_id,
                          CASE WHEN (u.excluded           IS     NULL) THEN t.excluded      ELSE u.excluded      END AS excluded,
                          t.values AS values, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t      LEFT JOIN user_words u        ON u.word_id = t.word_id 
                                                                    AND u.user_id = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.to_phrase_id   = t.word_id
                      AND l.from_phrase_id = t2.word_id 
                      AND l.to_phrase_id   IN (2,3)
                      AND l.verb_id IN (1,2) 
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $exe_start_time = test_show_result('word_link_list->load_sql by word list and down filtered by a verb list', zu_trim($expected_sql), zu_trim($created_sql), $exe_start_time, TIMEOUT_LIMIT);

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $exe_start_time = test_show_result('word_link_list->load_sql_name by word list and down filtered by a verb list', $result, $target, $exe_start_time, TIMEOUT_LIMIT);

}

