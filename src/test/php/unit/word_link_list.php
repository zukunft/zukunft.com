<?php

/*

  test/unit/word_link_list.php - TESTing of the WORD LINK LIST functions
  ----------------------------
  

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class word_link_list_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'word_link_list->';
        $t->resource_path = 'db/triple/';
        $usr->id = 1;

        $t->header('Unit tests of the word link list class (src/main/php/model/word/word_link_list.php)');

        $t->subheader('Database query creation tests');

        // load by triple ids
        $trp_lst = new word_link_list($usr);
        $trp_ids = array(3,2,4);
        $this->assert_sql_by_ids($t, $db_con, $trp_lst, $trp_ids);

        // load by triple phr
        $trp_lst = new word_link_list($usr);
        $phr = new phrase($usr);
        $phr->id = 5;
        $this->assert_sql_by_phr($t, $db_con, $trp_lst, $phr);

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements
         */

        $db_con = new sql_db();
        $db_con->db_type = sql_db::POSTGRES;

        // sql to load by word link list by ids
        $wrd_lnk_lst = new word_link_list($usr);
        $wrd_lnk_lst->ids = [1, 2, 3];
        $created_sql = $wrd_lnk_lst->load_sql($db_con);
        //$expected_sql = $t->file('');
        $expected_sql = "SELECT 
                          l.word_link_id,
                          ul.word_link_id AS user_word_link_id,
                          l.user_id,
                          l.from_phrase_id,
                          l.verb_id,
                          l.word_type_id,
                          l.to_phrase_id,
                          l.name_given,
                          l.name_generated,
                          l.description,
                          l.values,
                          l.share_type_id,
                          l.protect_id,
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
                 ORDER BY l.verb_id, name_given;"; // order adjusted based on the number of usage
        $t->dsp('word_link_list->load_sql by IDs', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        //$t->assert_sql_name_unique($wrd_lnk_lst->load_sql_name());

        // sql to load by word link list by word and up
        $wrd = new word($usr);
        $wrd->id = 1;
        $wrd_lnk_lst = new word_link_list($usr);
        $wrd_lnk_lst->wrd = $wrd;
        $wrd_lnk_lst->direction = word_link_list::DIRECTION_UP;
        $created_sql = $wrd_lnk_lst->load_sql($db_con);
        $expected_sql = "SELECT 
                          l.word_link_id,
                          ul.word_link_id AS user_word_link_id,
                          l.user_id,
                          l.from_phrase_id,
                          l.verb_id,
                          l.word_type_id,
                          l.to_phrase_id,
                          l.name_given,
                          l.name_generated,
                          l.description,
                          l.values,
                          l.share_type_id,
                          l.protect_id,
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
                 ORDER BY l.verb_id, name_given;"; // order adjusted based on the number of usage
        $t->dsp('word_link_list->load_sql by word and up', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lnk_lst->load_sql_name());

        // sql to load by word link list by word and down
        $wrd = new word($usr);
        $wrd->id = 2;
        $wrd_lnk_lst = new word_link_list($usr);
        $wrd_lnk_lst->wrd = $wrd;
        $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
        $created_sql = $wrd_lnk_lst->load_sql($db_con);
        $expected_sql = "SELECT 
                          l.word_link_id,
                          ul.word_link_id AS user_word_link_id,
                          l.user_id,
                          l.from_phrase_id,
                          l.verb_id,
                          l.word_type_id,
                          l.to_phrase_id,
                          l.name_given,
                          l.name_generated,
                          l.description,
                          l.values,
                          l.share_type_id,
                          l.protect_id,
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
                 ORDER BY l.verb_id, name_given;"; // order adjusted based on the number of usage
        $t->dsp('word_link_list->load_sql by word and down', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lnk_lst->load_sql_name());

        // sql to load by word link list by word list and up
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 1;
        $wrd_lst->add($wrd);
        $wrd = new word($usr);
        $wrd->id = 2;
        $wrd_lst->add($wrd);
        $wrd_lnk_lst = new word_link_list($usr);
        $wrd_lnk_lst->wrd_lst = $wrd_lst;
        $wrd_lnk_lst->direction = word_link_list::DIRECTION_UP;
        $created_sql = $wrd_lnk_lst->load_sql($db_con);
        $expected_sql = "SELECT 
                          l.word_link_id,
                          ul.word_link_id AS user_word_link_id,
                          l.user_id,
                          l.from_phrase_id,
                          l.verb_id,
                          l.word_type_id,
                          l.to_phrase_id,
                          l.name_given,
                          l.name_generated,
                          l.description,
                          l.values,
                          l.share_type_id,
                          l.protect_id,
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
                 ORDER BY l.verb_id, name_given;"; // order adjusted based on the number of usage
        $t->dsp('word_link_list->load_sql by word list and up', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lnk_lst->load_sql_name());

        // sql to load by word link list by word list and down
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 2;
        $wrd_lst->add($wrd);
        $wrd = new word($usr);
        $wrd->id = 3;
        $wrd_lst->add($wrd);
        $wrd_lnk_lst = new word_link_list($usr);
        $wrd_lnk_lst->wrd_lst = $wrd_lst;
        $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
        $created_sql = $wrd_lnk_lst->load_sql($db_con);
        $expected_sql = "SELECT 
                          l.word_link_id,
                          ul.word_link_id AS user_word_link_id,
                          l.user_id,
                          l.from_phrase_id,
                          l.verb_id,
                          l.word_type_id,
                          l.to_phrase_id,
                          l.name_given,
                          l.name_generated,
                          l.description,
                          l.values,
                          l.share_type_id,
                          l.protect_id,
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
                 ORDER BY l.verb_id, name_given;"; // order adjusted based on the number of usage
        $t->dsp('word_link_list->load_sql by word list and down', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lnk_lst->load_sql_name());

        // sql to load by word link list by word list and down filtered by a verb
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 2;
        $wrd_lst->add($wrd);
        $wrd = new word($usr);
        $wrd->id = 3;
        $wrd_lst->add($wrd);
        $vrb = new verb();
        $vrb->id = 2;
        $wrd_lnk_lst = new word_link_list($usr);
        $wrd_lnk_lst->wrd_lst = $wrd_lst;
        $wrd_lnk_lst->vrb = $vrb;
        $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
        $created_sql = $wrd_lnk_lst->load_sql($db_con);
        $expected_sql = "SELECT 
                          l.word_link_id,
                          ul.word_link_id AS user_word_link_id,
                          l.user_id,
                          l.from_phrase_id,
                          l.verb_id,
                          l.word_type_id,
                          l.to_phrase_id,
                          l.name_given,
                          l.name_generated,
                          l.description,
                          l.values,
                          l.share_type_id,
                          l.protect_id,
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
                 ORDER BY l.verb_id, name_given;"; // order adjusted based on the number of usage
        $t->dsp('word_link_list->load_sql by word list and down filtered by a verb', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lnk_lst->load_sql_name());

        // sql to load by word link list by word list and down filtered by a verb list
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 2;
        $wrd_lst->add($wrd);
        $wrd = new word($usr);
        $wrd->id = 3;
        $wrd_lst->add($wrd);
        $vrb_lst = new verb_list($usr);
        $vrb_lst->ids = [1, 2];
        $wrd_lnk_lst = new word_link_list($usr);
        $wrd_lnk_lst->wrd_lst = $wrd_lst;
        $wrd_lnk_lst->vrb_lst = $vrb_lst;
        $wrd_lnk_lst->direction = word_link_list::DIRECTION_DOWN;
        $created_sql = $wrd_lnk_lst->load_sql($db_con);
        $expected_sql = "SELECT 
                          l.word_link_id,
                          ul.word_link_id AS user_word_link_id,
                          l.user_id,
                          l.from_phrase_id,
                          l.verb_id,
                          l.word_type_id,
                          l.to_phrase_id,
                          l.name_given,
                          l.name_generated,
                          l.description,
                          l.values,
                          l.share_type_id,
                          l.protect_id,
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
                 ORDER BY l.verb_id, name_given;"; // order adjusted based on the number of usage
        $t->dsp('word_link_list->load_sql by word list and down filtered by a verb list', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lnk_lst->load_sql_name());

    }

    /**
     * test the SQL statement creation for a triple list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_link_list $lst the empty triple list object
     * @param array $ids filled with a list of word ids to be used for the query creation
     * @return void
     */
    private function assert_sql_by_ids(testing $t, sql_db $db_con, word_link_list $lst, array $ids)
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_ids($db_con, $ids);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_ids($db_con, $ids);
        $t->assert_qp($qp, sql_db::MYSQL);
    }

    /**
     * test the SQL statement creation for a triple list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_link_list $lst the empty triple list object
     * @param phrase $phr the phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param string $direction to select either the parents, children or all related words ana triples
     * @return void
     */
    private function assert_sql_by_phr(
        testing $t,
        sql_db $db_con,
        word_link_list $lst,
        phrase $phr,
        ?verb $vrb = null,
        string $direction = word_link_list::DIRECTION_BOTH)
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_phr($db_con, $phr, $vrb, $direction);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_phr($db_con, $phr, $vrb, $direction);
        $t->assert_qp($qp, sql_db::MYSQL);
    }

}