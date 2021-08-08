<?php

/*

  test/unit/word_link.php - unit testing of the word link / triple functions
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

function run_word_link_unit_tests()
{

    global $usr;
    global $sql_names;

    test_header('Unit tests of the word class (src/main/php/model/word/word_link.php)');


    test_subheader('SQL statement tests');

    $db_con = new sql_db();

    // sql to load the word by id
    $wrd = new word_link;
    $wrd->id = 2;
    $wrd->usr = $usr;
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = $wrd->load_sql($db_con);
    $expected_sql = "SELECT 
                            s.word_link_id,  
                            u.word_link_id AS user_word_link_id,  
                            s.user_id,  
                            s.from_phrase_id,  
                            s.to_phrase_id,  
                            s.verb_id,  
                            CASE WHEN (u.word_link_name <> '' IS NOT TRUE) THEN s.word_link_name ELSE u.word_link_name END AS word_link_name,  
                            CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description ELSE u.description END AS description,  
                            CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END AS excluded 
                       FROM word_links s LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id 
                                                                    AND u.user_id = 1 
                      WHERE s.word_link_id = 2;";
    test_dsp('word_link->load_sql by word id', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $wrd->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    test_dsp('word_link->load_sql by word id check sql name', $result, $target);


    test_subheader('Im- and Export tests');

    $json_in_msg = file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/triple/pi.json');
    $json_in_array = json_decode($json_in_msg, true);
    $lnk = new word_link;
    $lnk->import_obj($json_in_array, false);
    $json_ex_msg = json_encode($lnk->export_obj(false));
    $result = json_decode($json_in_msg) == json_decode($json_ex_msg);
    $target = true;
    test_dsp('word_link->import check name', $target, $result);
}

