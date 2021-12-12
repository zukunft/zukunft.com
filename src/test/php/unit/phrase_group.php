<?php

/*

  test/php/unit/phrase_group.php - unit tests related to a phrase group
  ------------------------------


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

class phrase_group_unit_tests
{
    function run(testing $t)
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
        $expected_sql = $t->file('db/phrase/phrase_group_by_id.sql');
        $t->assert('phrase_group->get_by_wrd_lst_sql by word id', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($phr_grp->get_by_wrd_lst_sql($db_con, true));

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
        $expected_sql = $t->file('db/phrase/phrase_group_by_id_list.sql');
        $t->assert('phrase_group->get_by_wrd_lst_sql by word list ids', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($phr_grp->get_by_wrd_lst_sql($db_con, true));

        // sql to load all phrase groups linked to a word
        $wrd = $t->load_word(word::TN_CITY);
        $wrd->id = 1; // dummy number just to test the SQL creation
        $phr_grp_lst = new phrase_group_list();
        $phr_grp_lst->usr = $usr;
        $phr_grp_lst->phr = $wrd->phrase();
        $created_sql = $phr_grp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/phrase/phrase_group_list_by_word.sql');
        $t->assert('phrase_group_list->load_all_word_linked', $t->trim($created_sql), $t->trim($expected_sql));

        // sql to load all phrase groups linked to a triple
        $lnk = $t->load_word_link(word::TN_ZH, verb::IS_A, word::TN_CITY);
        $lnk->id = 2; // dummy number just to test the SQL creation
        $phr_grp_lst = new phrase_group_list();
        $phr_grp_lst->usr = $usr;
        $phr_grp_lst->phr = $lnk->phrase();
        $created_sql = $phr_grp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/phrase/phrase_group_list_by_triple.sql');
        $t->assert('phrase_group_list->load_all_triple_linked', $t->trim($created_sql), $t->trim($expected_sql));

    }

}