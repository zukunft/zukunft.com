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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

use cfg\phrase_type;

class word_list_unit_tests
{
    function run(testing $t): void
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

        // load by pattern
        $wrd_lst = new word_list($usr);
        $name_pattern = 'M';
        $this->assert_sql_by_pattern($t, $db_con, $wrd_lst, $name_pattern);

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

        // create words for unit testing
        $wrd1 = new word($usr);
        $wrd1->id = 1;
        $wrd1->set_name('word1');
        $wrd2 = new word($usr);
        $wrd2->id = 2;
        $wrd2->set_name('word2');
        $wrd3 = new word($usr);
        $wrd3->id = 3;
        $wrd3->set_name('word3');
        $wrd_time = new word($usr);
        $wrd_time->id = 4;
        $wrd_time->set_name('time_word');
        $wrd_time->type_id = cl(db_cl::WORD_TYPE, phrase_type::TIME);
        $wrd_time2 = new word($usr);
        $wrd_time2->id = 5;
        $wrd_time2->set_name('time_word2');
        $wrd_time2->type_id = cl(db_cl::WORD_TYPE, phrase_type::TIME);
        $wrd_scale = new word($usr);
        $wrd_scale->id = 6;
        $wrd_scale->set_name('scale_word');
        $wrd_scale->type_id = cl(db_cl::WORD_TYPE, phrase_type::SCALING);
        $wrd_percent = new word($usr);
        $wrd_percent->id = 7;
        $wrd_percent->set_name('percent_word');
        $wrd_percent->type_id = cl(db_cl::WORD_TYPE, phrase_type::PERCENT);
        $wrd_measure = new word($usr);
        $wrd_measure->id = 8;
        $wrd_measure->set_name('measure_word');
        $wrd_measure->type_id = cl(db_cl::WORD_TYPE, phrase_type::MEASURE);

        // merge two lists
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

        // with time
        $wrd_lst_time = new word_list($usr);
        $wrd_lst_time->add($wrd1);
        $wrd_lst_time->add($wrd3);
        $wrd_lst_time->add($wrd_time);
        $t->assert($t->name . '->with time by ids', $wrd_lst_time->ids(), array(1, 3, 4));

        // ex time
        $wrd_lst_time->ex_time();
        $t->assert($t->name . '->ex_time by ids', $wrd_lst_time->ids(), array(1, 3));

        // with scale
        $wrd_lst_scale = new word_list($usr);
        $wrd_lst_scale->add($wrd2);
        $wrd_lst_scale->add($wrd_scale);
        $wrd_lst_scale->add($wrd3);
        $t->assert($t->name . '->with scale', $wrd_lst_scale->name(), '"word2","scale_word","word3"');

        // ex scale
        $wrd_lst_scale->ex_scaling();
        $t->assert($t->name . '->ex_time', $wrd_lst_scale->name(), '"word2","word3"');

        // with percent
        $wrd_lst_percent = new word_list($usr);
        $wrd_lst_percent->add($wrd1);
        $wrd_lst_percent->add($wrd2);
        $wrd_lst_percent->add($wrd_percent);
        $t->assert($t->name . '->with percent', $wrd_lst_percent->name(), '"word1","word2","percent_word"');

        // ex percent
        $wrd_lst_percent->ex_percent();
        $t->assert($t->name . '->ex_percent', $wrd_lst_percent->name(), '"word1","word2"');

        // unsorted
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd3);
        $wrd_lst->add($wrd1);
        $wrd_lst->add($wrd2);
        $t->assert($t->name . '->unsorted', $wrd_lst->name(), '"word3","word1","word2"');

        // sorted
        $wrd_lst->wlsort();
        $t->assert($t->name . '->sorted', $wrd_lst->name(), '"word1","word2","word3"');

        // unfiltered
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd3);
        $wrd_lst->add($wrd1);
        $wrd_lst->add($wrd2);
        $wrd_lst->add($wrd_time);
        $t->assert($t->name . '->unsorted', $wrd_lst->name(), '"word3","word1","word2","time_word"');

        // filtered
        $wrd_lst_filter = new word_list($usr);
        $wrd_lst_filter->add($wrd3);
        $wrd_lst_filter->add($wrd2);
        $wrd_lst_filter->add($wrd_percent);
        $wrd_lst_filtered = $wrd_lst->filter($wrd_lst_filter);
        $t->assert($t->name . '->sorted', $wrd_lst_filtered->name(), '"word3","word2"');

        // time list
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd_time);
        $wrd_lst->add($wrd2);
        $wrd_lst->add($wrd_time2);
        $wrd_lst_time = $wrd_lst->time_lst();
        $t->assert($t->name . '->time list', $wrd_lst_time->name(), '"time_word","time_word2"');

        // scaling list
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd_time);
        $wrd_lst->add($wrd_measure);
        $wrd_lst->add($wrd_scale);
        $wrd_lst_measure = $wrd_lst->measure_lst();
        $t->assert($t->name . '->measure list', $wrd_lst_measure->name(), '"measure_word"');

        // measure list
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd_scale);
        $wrd_lst_scaling = $wrd_lst->scaling_lst();
        $t->assert($t->name . '->scaling list', $wrd_lst_scaling->name(), '"scale_word"');

        // percent list
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd_scale);
        $wrd_lst_percent = $wrd_lst->percent_lst();
        $t->assert($t->name . '->percent list', $wrd_lst_percent->name(), '""');

        // JSON export list
        $wrd_lst = new word_list($usr);
        $wrd_lst->add($wrd_time);
        $wrd_lst->add($wrd_measure);
        $wrd_lst->add($wrd_scale);
        $json = json_decode(json_encode($wrd_lst->export_obj()));
        $json_expected = json_decode(file_get_contents(PATH_TEST_FILES . 'api/word/word_list.json'));
        $result = json_is_similar($json, $json_expected);
        $t->assert('JSON export word list', $result, true);

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
    private function assert_sql_by_ids(testing $t, sql_db $db_con, word_list $lst, array $ids): void
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
     * similar to assert_sql_by_ids, but for word names
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_list $lst the empty word list object
     * @param array $words filled with a list of word names to be used for the query creation
     * @return void
     */
    private function assert_sql_by_names(testing $t, sql_db $db_con, word_list $lst, array $words): void
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
    private function assert_sql_by_group_id(testing $t, sql_db $db_con, word_list $lst, int $grp_id): void
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
    private function assert_sql_by_type_id(testing $t, sql_db $db_con, word_list $lst, int $type_id): void
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
     * similar to assert_sql_by_ids, but for a type
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param word_list $lst the empty word list object
     * @param string $pattern the text pattern to select the words
     * @return void
     */
    private function assert_sql_by_pattern(testing $t, sql_db $db_con, word_list $lst, string $pattern): void
    {
        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_pattern($db_con, $pattern);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_pattern($db_con, $pattern);
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
    private function assert_sql_by_linked_words(testing $t, sql_db $db_con, word_list $lst, int $verb_id, string $direction): void
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