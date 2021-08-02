<?php

/*

  test/unit/word.php - unit testing of the word functions
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

function run_word_unit_tests()
{

    global $usr;
    global $sql_names;

    test_header('Unit tests of the word class (src/main/php/model/word/word.php)');


    test_subheader('SQL statement tests');

    $db_con = new sql_db();

    // sql to load the word by id
    $wrd = new word;
    $wrd->id = 2;
    $wrd->usr = $usr;
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = $wrd->load_sql($db_con);
    $expected_sql = "SELECT 
                            s.word_id, 
                            u.word_id AS user_word_id, 
                            s.user_id, 
                            s.values, 
                            CASE WHEN (u.word_name   <> '' IS NOT TRUE) THEN s.word_name    ELSE u.word_name    END AS word_name, 
                            CASE WHEN (u.plural      <> '' IS NOT TRUE) THEN s.plural       ELSE u.plural       END AS plural, 
                            CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description  ELSE u.description  END AS description, 
                            CASE WHEN (u.word_type_id      IS     NULL) THEN s.word_type_id ELSE u.word_type_id END AS word_type_id, 
                            CASE WHEN (u.view_id           IS     NULL) THEN s.view_id      ELSE u.view_id      END AS view_id, 
                            CASE WHEN (u.excluded          IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded 
                       FROM words s LEFT JOIN user_words u ON s.word_id = u.word_id 
                                                          AND u.user_id = 1 
                      WHERE s.word_id = 2;";
    test_dsp('word->load_sql by word id', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    test_dsp('word->load_sql by word id check sql name', $result, $target);


    test_subheader('Im- and Export tests');

    $dsp_json = '{
      "name": "second",
      "type": "measure",
      "plural": "seconds",
      "description": "The second (symbol: s, abbreviation: sec) is the base unit of time in the International System of Units",
      "share": "",
      "protection": "admin_protection",
      "view": "measure",
      "refs": [
        {
          "name": "Second",
          "type": "wikipedia"
        }
      ]
    }';
    $json_import_array = json_decode($dsp_json, true);
    $wrd = new word_dsp;
    $wrd->import_obj($json_import_array, false);
    $json_export_string = json_encode($wrd->export_obj(false));
    $result = json_decode($dsp_json) == json_decode($json_export_string);
    $target = true;
    test_dsp('word->import check name', $target, $result);

}

