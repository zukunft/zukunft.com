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

        $wrd_lst = new word_list($usr);
        $wrd_ids = array(3,2,4);
        $this->assert_sql_by_ids($t, $db_con, $wrd_lst, $wrd_ids);

        $wrd_names = array(word::TN_READ, word::TN_ADD);
        $this->assert_sql_by_names($t, $db_con, $wrd_lst, $wrd_names);



        // sql to load by word list by ids
        $db_con->db_type = sql_db::POSTGRES;
        $wrd_lst = new word_list($usr);
        $created_sql = $wrd_lst->load_sql_by_ids($db_con, [1, 2, 3])->sql;
        $expected_sql = $t->file('db/word/word_list_by_id_list.sql');
        $t->assert('word_list->load_sql by IDs', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lst->load_sql_where($db_con, true));

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $wrd_lst->load_sql_by_ids($db_con, [1, 2, 3])->sql;
        $expected_sql = $t->file('db/word/word_list_by_id_list_mysql.sql');
        $t->assert('word_list->load_sql by IDs', $t->trim($created_sql), $t->trim($expected_sql));

        // sql to load by word list by phrase group
        $db_con->db_type = sql_db::POSTGRES;
        $wrd_lst = new word_list($usr);
        $wrd_lst->grp_id = 1;
        $created_sql = $wrd_lst->load_sql_where($db_con);
        $expected_sql = $t->file('db/word/word_list_by_phrase_group.sql');
        $t->assert('word_list->load_sql by phrase group', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lst->load_sql_where($db_con, true));

        // TODO add the missing word list loading SQL

        // SQL to add by word list by a relation e.g. for "Zurich" and direction "up" add "City", "Canton" and "Company"
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->id = 7;
        $wrd_lst->add($wrd);
        $created_sql = $wrd_lst->add_by_type_sql($db_con, 2, word_select_direction::UP);
        $expected_sql = $t->file('db/word/word_list_by_verb_up.sql');
        $t->assert('word_list->add_by_type_sql by verb and up', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lst->add_by_type_sql($db_con, 2, word_select_direction::UP, true));

    }

    /**
     * test the SQL statement creation for a value phrase link list in all SQL dialect
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
     * test the SQL statement creation for a value phrase link list in all SQL dialect
     * and check if the statement name is unique
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

}