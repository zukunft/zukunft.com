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

        // init
        $db_con = new sql_db();
        $t->name = 'phrase_group->';
        $t->resource_path = 'db/phrase/';
        $usr->id = 1;

        $t->header('Unit tests of the phrase group class (src/main/php/model/phrase/word.php)');

        $t->subheader('SQL statement tests');

        // sql to load the phrase group by id
        $phr_grp = new phrase_group($usr);
        $phr_grp->id = 1;
        $t->assert_load_sql($db_con, $phr_grp);

        // sql to load the phrase group by word ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add_by_ids('2,4,3','');
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql($db_con, $phr_grp);

        // sql to load the phrase group by triple ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add_by_ids(null,'2,4,3');
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql($db_con, $phr_grp);

        // sql to load the phrase group by word and triple ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add_by_ids('4,1,3','2');
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql($db_con, $phr_grp);

        // sql to load the phrase group by name
        $phr_grp = new phrase_group($usr);
        $phr_grp->grp_name = phrase_group::TN_READ;
        $t->assert_load_sql($db_con, $phr_grp);

        // sql to load the word list ids
        $wrd_lst = new word_list($usr);
        $wrd1 = new word($usr);
        $wrd1->id = 1;
        $wrd_lst->lst[] = $wrd1;
        $wrd2 = new word($usr);
        $wrd2->id = 2;
        $wrd_lst->lst[] = $wrd2;
        $wrd3 = new word($usr);
        $wrd3->id = 3;
        $wrd_lst->lst[] = $wrd3;
        $phr_grp = new phrase_group($usr);
        $phr_grp->id = null;
        $phr_grp->phr_lst = $wrd_lst->phrase_lst();
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $phr_grp->get_by_wrd_lst_sql();
        $expected_sql = $t->file('db/phrase/phrase_group_by_id_list.sql');
        $t->assert('phrase_group->get_by_wrd_lst_sql by word list ids', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($phr_grp->get_by_wrd_lst_sql(true));

        // sql to load the phrase group word link by id
        $grp_wrd_lnk = new phrase_group_word_link();
        $grp_wrd_lnk->id = 11;
        $t->assert_load_sql($db_con, $grp_wrd_lnk);

        // sql to load the phrase group triple link by id
        $grp_trp_lnk = new phrase_group_triple_link();
        $grp_trp_lnk->id = 12;
        $t->assert_load_sql($db_con, $grp_trp_lnk);

        // sql to load all phrase groups linked to a word
        $db_con->db_type = sql_db::POSTGRES;
        $wrd = $t->load_word(word::TN_CITY);
        $wrd->id = 1; // dummy number just to test the SQL creation
        $phr_grp_lst = new phrase_group_list();
        $phr_grp_lst->usr = $usr;
        $phr_grp_lst->phr = $wrd->phrase();
        $created_sql = $phr_grp_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/phrase/phrase_group_list_by_word.sql');
        $t->assert('phrase_group_list->load_all_word_linked', $t->trim($created_sql), $t->trim($expected_sql));

        // sql to load all phrase groups linked to a triple
        $lnk = $t->load_word_link(word::TN_ZH, verb::IS_A, word::TN_CITY);
        $lnk->id = 2; // dummy number just to test the SQL creation
        $phr_grp_lst = new phrase_group_list();
        $phr_grp_lst->usr = $usr;
        $phr_grp_lst->phr = $lnk->phrase();
        $created_sql = $phr_grp_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/phrase/phrase_group_list_by_triple.sql');
        $t->assert('phrase_group_list->load_all_triple_linked', $t->trim($created_sql), $t->trim($expected_sql));

    }

}