<?php

/*

  test/unit/word_list.php - TESTing of the WORD LIST functions
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

class word_list_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'word_list->';
        $t->resource_path = 'db/word/';
        $usr->id = 1;

        $t->header('Unit tests of the word list class (src/main/php/model/word/word_list.php)');

        $t->subheader('Database query creation tests');

        // load by word ids
        $wrd_lst = new word_list($usr);
        $wrd_ids = array(3,2,4);
        $this->assert_sql_by_ids($t, $db_con, $wrd_lst, $wrd_ids);

        // load by word names
        $wrd_lst = new word_list($usr);
        $wrd_names = array(word::TN_READ, word::TN_ADD);
        $this->assert_sql_by_names($t, $db_con, $wrd_lst, $wrd_names);

        // load by phrase group
        $wrd_lst = new word_list($usr);
        $grp_id = 1;
        $this->assert_sql_by_group_id($t, $db_con, $wrd_lst, $grp_id);

        // load by type
        $wrd_lst = new word_list($usr);
        $type_id = 1;
        $this->assert_sql_by_type_id($t, $db_con, $wrd_lst, $type_id);

        // the parent words
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 6;
        $wrd_lst->add($wrd);
        $verb_id = 0;
        $direction = word_select_direction::UP;
        $this->assert_sql_by_linked_words($t, $db_con, $wrd_lst, $verb_id, $direction);

        // the parent words filtered by verb
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 7;
        $wrd_lst->add($wrd);
        $verb_id = 1;
        $this->assert_sql_by_linked_words($t, $db_con, $wrd_lst, $verb_id, $direction);

        // the child words
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 8;
        $wrd_lst->add($wrd);
        $verb_id = 0;
        $direction = word_select_direction::DOWN;
        $this->assert_sql_by_linked_words($t, $db_con, $wrd_lst, $verb_id, $direction);

        // the child words filtered by verb
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 9;
        $wrd_lst->add($wrd);
        $verb_id = 1;
        $this->assert_sql_by_linked_words($t, $db_con, $wrd_lst, $verb_id, $direction);

        $t->subheader('Modify and filter word lists');

        // merge two lists
        $wrd1 = new word($usr);
        $wrd1->id = 1;
        $wrd1->name = 'word1';
        $wrd2 = new word($usr);
        $wrd2->id = 2;
        $wrd2->name = 'word2';
        $wrd3 = new word($usr);
        $wrd3->id = 3;
        $wrd3->name = 'word3';
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd1);
        $wrd_lst->add($wrd3);
        $wrd_lst2 = new word_list($usr);
        $wrd_lst2->add($wrd2);
        $wrd_lst2->add($wrd3);
        $wrd_lst->merge($wrd_lst2);
        $t->assert($t->name . '->merge and check by ids', $wrd_lst->ids(), array(1, 2, 3));

        // diff of two lists
        $wrd_lst->diff($wrd_lst2);
        $t->assert($t->name . '->diff and check by ids', $wrd_lst->ids(), array(1));

        // diff by ids
        $wrd_lst->merge($wrd_lst2);
        $wrd_lst->diff_by_ids(array(2));
        $t->assert($t->name . '->diff by id and check by ids', $wrd_lst->ids(), array(1, 3));

    }

    /**
     * test the SQL statement creation for a word list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_list $lst the empty word list object
     * @param array $ids filled with a list of word ids to be used for the query creation
     * @return void
     */
    private function assert_sql_by_ids(testing $t, sql_db $db_con, word_list $lst, array $ids)
    {
        global $usr;

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
     * similar to assert_sql_by_ids, but for word names
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_list $lst the empty word list object
     * @param array $words filled with a list of word names to be used for the query creation
     * @return void
     */
    private function assert_sql_by_names(testing $t, sql_db $db_con, word_list $lst, array $words)
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_names($db_con, $words);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_names($db_con, $words);
        $t->assert_qp($qp, sql_db::MYSQL);
    }

    /**
     * similar to assert_sql_by_ids, but for a phrase group
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_list $lst the empty word list object
     * @param int $grp_id the phrase group id that should be used for selecting the words
     * @return void
     */
    private function assert_sql_by_group_id(testing $t, sql_db $db_con, word_list $lst, int $grp_id)
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_grp_id($db_con, $grp_id);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_grp_id($db_con, $grp_id);
        $t->assert_qp($qp, sql_db::MYSQL);
    }

    /**
     * similar to assert_sql_by_ids, but for a type
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_list $lst the empty word list object
     * @param int $type_id the phrase group id that should be used for selecting the words
     * @return void
     */
    private function assert_sql_by_type_id(testing $t, sql_db $db_con, word_list $lst, int $type_id)
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_type($db_con, $type_id);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_type($db_con, $type_id);
        $t->assert_qp($qp, sql_db::MYSQL);
    }

    /**
     * similar to assert_sql_by_ids, but for a linked words
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_list $lst the empty word list object
     * @param int $verb_id to select only words linked with this verb
     * @param string $direction to define the link direction
     * @return void
     */
    private function assert_sql_by_linked_words(testing $t, sql_db $db_con, word_list $lst, int $verb_id, string $direction)
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_linked_words($db_con, $verb_id, $direction);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_linked_words($db_con, $verb_id, $direction);
        $t->assert_qp($qp, sql_db::MYSQL);
    }

}