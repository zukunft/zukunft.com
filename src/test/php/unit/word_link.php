<?php

/*

  test/unit/word_link.php - unit testing of the word link / triple functions
  -----------------------
  

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

class word_link_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $t->header('Unit tests of the word class (src/main/php/model/word/word_link.php)');


        $t->subheader('SQL statement tests');

        $db_con = new sql_db();

        // sql to load the word by id
        $wrd = new word_link;
        $wrd->id = 2;
        $wrd->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $wrd->load_sql($db_con);
        $expected_sql = "SELECT 
                            s.word_link_id,  
                            u.word_link_id AS user_word_link_id,  
                            s.user_id,  
                            s.from_phrase_id,  
                            s.to_phrase_id,  
                            s.verb_id,  
                            s.word_type_id,  
                            CASE WHEN (u.word_link_name <> '' IS NOT TRUE) THEN s.word_link_name     ELSE u.word_link_name     END AS word_link_name,  
                            CASE WHEN (u.description <> ''    IS NOT TRUE) THEN s.description        ELSE u.description        END AS description,  
                            CASE WHEN (u.excluded             IS     NULL) THEN s.excluded           ELSE u.excluded           END AS excluded,
                            CASE WHEN (u.share_type_id        IS     NULL) THEN s.share_type_id      ELSE u.share_type_id      END AS share_type_id,  
                            CASE WHEN (u.protection_type_id   IS     NULL) THEN s.protection_type_id ELSE u.protection_type_id END AS protection_type_id 
                       FROM word_links s LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id 
                                                                    AND u.user_id = 1 
                      WHERE s.word_link_id = 2;";
        $t->dsp('word_link->load_sql by word id', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd->load_sql($db_con, true));


        $t->subheader('Im- and Export tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/triple/pi.json'), true);
        $lnk = new word_link;
        $lnk->import_obj($json_in, false);
        $json_ex = json_decode(json_encode($lnk->export_obj(false)), true);
        $result = json_is_similar($json_in, $json_ex);
        $target = true;
        $t->dsp('word_link->import check name', $target, $result);
    }

}