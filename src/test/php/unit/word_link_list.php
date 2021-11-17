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

function run_word_link_list_unit_tests(testing $t)
{

    global $usr;
    global $sql_names;

    $t->header('Unit tests of the word link list class (src/main/php/model/word/word_link_list.php)');

    /*
     * SQL creation tests (mainly to use the IDE check for the generated SQL statements
     */

    $db_con = new sql_db();
    $db_con->db_type = sql_db::POSTGRES;

    // sql to load by word link list by ids
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->ids = [1, 2, 3];
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql($db_con);
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
                          v.words,
                          CASE WHEN (ul.excluded          IS     NULL) THEN l.excluded      ELSE ul.excluded     END AS excluded,
                          t1.word_id AS word_id1,
                          t1.user_id AS user_id1,
                          CASE WHEN (u1.word_name    <> '' IS NOT TRUE) THEN t1.word_name     ELSE u1.word_name     END AS word_name1,
                          CASE WHEN (u1.plural       <> '' IS NOT TRUE) THEN t1.plural        ELSE u1.plural        END AS plural1,
                          CASE WHEN (u1.description  <> '' IS NOT TRUE) THEN t1.description   ELSE u1.description   END AS description1,
                          CASE WHEN (u1.word_type_id       IS     NULL) THEN t1.word_type_id  ELSE u1.word_type_id  END AS word_type_id1,
                          CASE WHEN (u1.view_id            IS     NULL) THEN t1.view_id       ELSE u1.view_id       END AS view_id1,
                          CASE WHEN (u1.excluded           IS     NULL) THEN t1.excluded      ELSE u1.excluded      END AS excluded1,
                          t1.values AS values1, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.view_id           IS     NULL) THEN t2.view_id      ELSE u2.view_id      END AS view_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t1     LEFT JOIN user_words u1       ON u1.word_id  = t1.word_id 
                                                                    AND u1.user_id  = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.from_phrase_id = t1.word_id
                      AND l.to_phrase_id   = t2.word_id 
                      AND l.word_link_id  IN (1,2,3)                        
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $t->dsp('word_link_list->load_sql by IDs', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_link_list->load_sql_name by IDs', $result, $target);

    // sql to load by word link list by word and up
    $wrd = new word();
    $wrd->id = 1;
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd = $wrd;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_UP;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql($db_con);
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
                          v.words,
                          CASE WHEN (ul.excluded          IS     NULL) THEN l.excluded      ELSE ul.excluded     END AS excluded,
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.view_id           IS     NULL) THEN t2.view_id      ELSE u2.view_id      END AS view_id2,
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
    $t->dsp('word_link_list->load_sql by word and up', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_link_list->load_sql_name by word and up', $result, $target);

    // sql to load by word link list by word and down
    $wrd = new word();
    $wrd->id = 2;
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd = $wrd;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql($db_con);
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
                          v.words,
                          CASE WHEN (ul.excluded          IS     NULL) THEN l.excluded      ELSE ul.excluded     END AS excluded,
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.view_id           IS     NULL) THEN t2.view_id      ELSE u2.view_id      END AS view_id2,
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
    $t->dsp('word_link_list->load_sql by word and down', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_link_list->load_sql_name by word and down', $result, $target);

    // sql to load by word link list by word list and up
    $wrd_lst = new word_list();
    $wrd_lst->ids = [1, 2];
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd_lst = $wrd_lst;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_UP;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql($db_con);
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
                          v.words,
                          CASE WHEN (ul.excluded         IS     NULL) THEN l.excluded     ELSE ul.excluded    END AS excluded,
                          t1.word_id AS word_id1,
                          t1.user_id AS user_id1,
                          CASE WHEN (u1.word_name <> ''   IS NOT TRUE) THEN t1.word_name    ELSE u1.word_name    END AS word_name1,
                          CASE WHEN (u1.plural <> ''      IS NOT TRUE) THEN t1.plural       ELSE u1.plural       END AS plural1,
                          CASE WHEN (u1.description <> '' IS NOT TRUE) THEN t1.description  ELSE u1.description  END AS description1,
                          CASE WHEN (u1.word_type_id      IS     NULL) THEN t1.word_type_id ELSE u1.word_type_id END AS word_type_id1,
                          CASE WHEN (u1.view_id           IS     NULL) THEN t1.view_id      ELSE u1.view_id      END AS view_id1,
                          CASE WHEN (u1.excluded          IS     NULL) THEN t1.excluded     ELSE u1.excluded     END AS excluded1,
                          t1.values AS values1, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.view_id           IS     NULL) THEN t2.view_id      ELSE u2.view_id      END AS view_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t1     LEFT JOIN user_words u1       ON u1.word_id = t1.word_id 
                                                                    AND u1.user_id = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.from_phrase_id = t1.word_id
                      AND l.to_phrase_id   = t2.word_id 
                      AND l.from_phrase_id IN (1,2)
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $t->dsp('word_link_list->load_sql by word list and up', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_link_list->load_sql_name by word list and up', $result, $target);

    // sql to load by word link list by word list and down
    $wrd_lst = new word_list();
    $wrd_lst->ids = [2, 3];
    $wrd_lnk_lst = new word_link_list;
    $wrd_lnk_lst->wrd_lst = $wrd_lst;
    $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
    $wrd_lnk_lst->usr = $usr;
    $created_sql = $wrd_lnk_lst->load_sql($db_con);
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
                          v.words,
                          CASE WHEN (ul.excluded         IS     NULL) THEN l.excluded     ELSE ul.excluded    END AS excluded,
                          t1.word_id AS word_id1,
                          t1.user_id AS user_id1,
                          CASE WHEN (u1.word_name <> ''   IS NOT TRUE) THEN t1.word_name    ELSE u1.word_name    END AS word_name1,
                          CASE WHEN (u1.plural <> ''      IS NOT TRUE) THEN t1.plural       ELSE u1.plural       END AS plural1,
                          CASE WHEN (u1.description <> '' IS NOT TRUE) THEN t1.description  ELSE u1.description  END AS description1,
                          CASE WHEN (u1.word_type_id      IS     NULL) THEN t1.word_type_id ELSE u1.word_type_id END AS word_type_id1,
                          CASE WHEN (u1.view_id           IS     NULL) THEN t1.view_id      ELSE u1.view_id      END AS view_id1,
                          CASE WHEN (u1.excluded          IS     NULL) THEN t1.excluded     ELSE u1.excluded     END AS excluded1,
                          t1.values AS values1, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.view_id           IS     NULL) THEN t2.view_id      ELSE u2.view_id      END AS view_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t1     LEFT JOIN user_words u1       ON u1.word_id = t1.word_id 
                                                                    AND u1.user_id = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.to_phrase_id   = t1.word_id
                      AND l.from_phrase_id = t2.word_id 
                      AND l.to_phrase_id   IN (2,3)
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $t->dsp('word_link_list->load_sql by word list and down', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_link_list->load_sql_name by word list and down', $result, $target);

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
    $created_sql = $wrd_lnk_lst->load_sql($db_con);
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
                          v.words,
                          CASE WHEN (ul.excluded         IS     NULL) THEN l.excluded     ELSE ul.excluded    END AS excluded,
                          t1.word_id AS word_id1,
                          t1.user_id AS user_id1,
                          CASE WHEN (u1.word_name <> ''   IS NOT TRUE) THEN t1.word_name    ELSE u1.word_name    END AS word_name1,
                          CASE WHEN (u1.plural <> ''      IS NOT TRUE) THEN t1.plural       ELSE u1.plural       END AS plural1,
                          CASE WHEN (u1.description <> '' IS NOT TRUE) THEN t1.description  ELSE u1.description  END AS description1,
                          CASE WHEN (u1.word_type_id      IS     NULL) THEN t1.word_type_id ELSE u1.word_type_id END AS word_type_id1,
                          CASE WHEN (u1.view_id           IS     NULL) THEN t1.view_id      ELSE u1.view_id      END AS view_id1,
                          CASE WHEN (u1.excluded          IS     NULL) THEN t1.excluded     ELSE u1.excluded     END AS excluded1,
                          t1.values AS values1, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.view_id           IS     NULL) THEN t2.view_id      ELSE u2.view_id      END AS view_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t1     LEFT JOIN user_words u1       ON u1.word_id = t1.word_id 
                                                                    AND u1.user_id = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.to_phrase_id   = t1.word_id
                      AND l.from_phrase_id = t2.word_id 
                      AND l.to_phrase_id   IN (2,3)
                      AND l.verb_id = 2 
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $t->dsp('word_link_list->load_sql by word list and down filtered by a verb', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_link_list->load_sql_name by word list and down filtered by a verb', $result, $target);

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
    $created_sql = $wrd_lnk_lst->load_sql($db_con);
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
                          v.words,
                          CASE WHEN (ul.excluded         IS     NULL) THEN l.excluded     ELSE ul.excluded    END AS excluded,
                          t1.word_id AS word_id1,
                          t1.user_id AS user_id1,
                          CASE WHEN (u1.word_name <> ''   IS NOT TRUE) THEN t1.word_name    ELSE u1.word_name    END AS word_name1,
                          CASE WHEN (u1.plural <> ''      IS NOT TRUE) THEN t1.plural       ELSE u1.plural       END AS plural1,
                          CASE WHEN (u1.description <> '' IS NOT TRUE) THEN t1.description  ELSE u1.description  END AS description1,
                          CASE WHEN (u1.word_type_id      IS     NULL) THEN t1.word_type_id ELSE u1.word_type_id END AS word_type_id1,
                          CASE WHEN (u1.view_id           IS     NULL) THEN t1.view_id      ELSE u1.view_id      END AS view_id1,
                          CASE WHEN (u1.excluded          IS     NULL) THEN t1.excluded     ELSE u1.excluded     END AS excluded1,
                          t1.values AS values1, 
                          t2.word_id AS word_id2,
                          t2.user_id AS user_id2,
                          CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
                          CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
                          CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
                          CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
                          CASE WHEN (u2.view_id           IS     NULL) THEN t2.view_id      ELSE u2.view_id      END AS view_id2,
                          CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
                          t2.values AS values2
                     FROM word_links l LEFT JOIN user_word_links ul  ON ul.word_link_id = l.word_link_id 
                                                                    AND ul.user_id = 1,
                          verbs v, 
                          words t1     LEFT JOIN user_words u1       ON u1.word_id = t1.word_id 
                                                                    AND u1.user_id = 1 , 
                          words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id 
                                                                    AND u2.user_id = 1 
                    WHERE l.verb_id        = v.verb_id 
                      AND l.to_phrase_id   = t1.word_id
                      AND l.from_phrase_id = t2.word_id 
                      AND l.to_phrase_id   IN (2,3)
                      AND l.verb_id IN (1,2) 
                 ORDER BY l.verb_id, word_link_name;"; // order adjusted based on the number of usage
    $t->dsp('word_link_list->load_sql by word list and down filtered by a verb list', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd_lnk_lst->load_sql_name();
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('word_link_list->load_sql_name by word list and down filtered by a verb list', $result, $target);

}

