<?php

/*

  test/php/unit/phrase_group.php - unit tests related to a phrase group
  ------------------------------


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

function run_phrase_group_unit_tests(testing $t)
{

    global $usr;
    global $sql_names;

    $t->header('Unit tests of the phrase group class (src/main/php/model/phrase/word.php)');

    $t->subheader('SQL statement tests');

    $db_con = new sql_db();

    // sql to load the word by id
    $phr_grp = new phrase_group();
    $phr_grp->id = 1;
    $phr_grp->usr = $usr;
    $db_con->db_type = sql_db::POSTGRES;
    $created_sql = $phr_grp->get_by_wrd_lst_sql($db_con);
    $expected_sql = "SELECT phrase_group_id FROM phrase_groups WHERE phrase_group_id = 1 GROUP BY phrase_group_id;";
    $t->dsp('phrase_group->get_by_wrd_lst_sql by word id', $t->trim($expected_sql), $t->trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $phr_grp->get_by_wrd_lst_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('phrase_group->get_by_wrd_lst_sql by word id check sql name', $result, $target);

    // sql to load the word list ids
    $wrd_lst = new word_list();
    $wrd1 = new word();
    $wrd1->id = 1;
    $wrd_lst->lst[] = $wrd1;
    $wrd2 = new word();
    $wrd2->id = 2;
    $wrd_lst->lst[] = $wrd2;
    $wrd3 = new word();
    $wrd3->id = 3;
    $wrd_lst->lst[] = $wrd3;
    $phr_grp = new phrase_group();
    $phr_grp->id = null;
    $phr_grp->wrd_lst = $wrd_lst;
    $phr_grp->usr = $usr;
    $db_con->db_type = sql_db::POSTGRES;
    $created_sql = $phr_grp->get_by_wrd_lst_sql($db_con);
    $expected_sql = "SELECT l1.phrase_group_id 
                       FROM phrase_group_word_links l1, 
                            phrase_group_word_links l2, 
                            phrase_group_word_links l3
                      WHERE                             l1.word_id = 1 
                        AND l2.word_id = l1.word_id AND l2.word_id = 2 
                        AND l3.word_id = l2.word_id AND l3.word_id = 3 
                   GROUP BY l1.phrase_group_id;";
    $t->dsp('phrase_group->get_by_wrd_lst_sql by word id', $t->trim($expected_sql), $t->trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $phr_grp->get_by_wrd_lst_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    $t->dsp('phrase_group->get_by_wrd_lst_sql by word id check sql name', $result, $target);

}