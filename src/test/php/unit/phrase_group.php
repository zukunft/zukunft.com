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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace test;

include_once API_PHRASE_PATH . 'phrase_group.php';
include_once MODEL_PHRASE_PATH . 'phrase_group_word_link.php';
include_once MODEL_PHRASE_PATH . 'phrase_group_triple_link.php';
include_once MODEL_PHRASE_PATH . 'phrase_group_list.php';

use api\phrase_group_api;
use api\word_api;
use model\library;
use model\phrase_group;
use model\phrase_group_link;
use model\phrase_group_list;
use model\phrase_group_triple_link;
use model\phrase_group_word_link;
use model\phrase_list;
use model\sql_db;
use model\triple;
use model\verb;
use model\word;
use model\word_list;

class phrase_group_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'phrase_group->';
        $t->resource_path = 'db/phrase/';
        $usr->set_id(1);

        $t->header('Unit tests of the phrase group class (src/main/php/model/phrase/word.php)');

        $t->subheader('SQL statement tests');

        // sql to load the phrase group by id
        $phr_grp = new phrase_group($usr);
        $phr_grp->set_id(1);
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the phrase group by word ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add_by_ids('2,4,3','');
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the phrase group by triple ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add_by_ids(null,'2,4,3');
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the phrase group by word and triple ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add_by_ids('4,1,3','2');
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the phrase group by name
        $phr_grp = new phrase_group($usr);
        $phr_grp->grp_name = phrase_group_api::TN_READ;
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the word list ids
        $wrd_lst = new word_list($usr);
        $wrd1 = new word($usr);
        $wrd1->set_id(1);
        $wrd_lst->add($wrd1);
        $wrd2 = new word($usr);
        $wrd2->set_id(2);
        $wrd_lst->add($wrd2);
        $wrd3 = new word($usr);
        $wrd3->set_id(3);
        $wrd_lst->add($wrd3);
        $phr_grp = new phrase_group($usr);
        $phr_grp->set_id(0);
        $phr_grp->phr_lst = $wrd_lst->phrase_lst();
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $phr_grp->get_by_wrd_lst_sql();
        $expected_sql = $t->file('db/phrase/phrase_group_by_id_list.sql');
        $t->assert('phrase_group->get_by_wrd_lst_sql by word list ids', $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($phr_grp->get_by_wrd_lst_sql(true));

        // sql to load the phrase group word link by id
        $grp_wrd_lnk = new phrase_group_word_link();
        $grp_wrd_lnk->set_id(11);
        $t->assert_load_sql_obj_vars($db_con, $grp_wrd_lnk);

        // sql to load the phrase group triple link by id
        $grp_trp_lnk = new phrase_group_triple_link();
        $grp_trp_lnk->set_id(12);
        $t->assert_load_sql_obj_vars($db_con, $grp_trp_lnk);

        // sql to load all phrase groups linked to a word
        $db_con->db_type = sql_db::POSTGRES;
        $wrd = $t->create_word(word_api::TN_CITY);
        $wrd->set_id(1); // dummy number just to test the SQL creation
        $phr_grp_lst = new phrase_group_list($usr);
        $phr_grp_lst->phr = $wrd->phrase();
        $created_sql = $phr_grp_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/phrase/phrase_group_list_by_word.sql');
        $t->assert('phrase_group_list->load_all_triples', $lib->trim($created_sql), $lib->trim($expected_sql));

        // sql to load all phrase groups linked to a triple
        $lnk = $t->create_triple(word_api::TN_ZH, verb::IS_A, word_api::TN_CITY);
        $lnk->set_id(2); // dummy number just to test the SQL creation
        $phr_grp_lst = new phrase_group_list($usr);
        $phr_grp_lst->phr = $lnk->phrase();
        $created_sql = $phr_grp_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/phrase/phrase_group_list_by_triple.sql');
        $t->assert('phrase_group_list->load_all_triple_linked', $lib->trim($created_sql), $lib->trim($expected_sql));


        $t->header('Unit tests of the phrase group word link class (src/main/php/model/phrase/phrase_group_triple.php)');

        $t->subheader('SQL statement tests');

        // sql to load the phrase group word links related to a group
        $grp_wrd_lnk = new phrase_group_word_link();
        $phr_grp = new phrase_group($usr);
        $phr_grp->set_id(13);
        $this->assert_load_by_group_id_sql($t, $db_con, $grp_wrd_lnk, $phr_grp);

        // sql to load the phrase group triple links related to a group
        $grp_trp_lnk = new phrase_group_triple_link();
        $phr_grp->set_id(14);
        $this->assert_load_by_group_id_sql($t, $db_con, $grp_trp_lnk, $phr_grp);

    }

    /**
     * similar to $t->assert_load_sql but calling load_by_group_id_sql instead of load_sql
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param phrase_group_link $phr_grp_lnk the phrase group triple or word link object used for testing
     * @param phrase_group $grp the phrase group object to select the links
     */
    private function assert_load_by_group_id_sql(test_cleanup $t, sql_db $db_con, phrase_group_link $phr_grp_lnk, phrase_group $grp)
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $phr_grp_lnk->load_by_group_id_sql($db_con, $grp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $phr_grp_lnk->load_by_group_id_sql($db_con, $grp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}