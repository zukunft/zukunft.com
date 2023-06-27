<?php

/*

  test/unit/triple_list.php - TESTing of the WORD LINK LIST functions
  ----------------------------
  

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

include_once MODEL_WORD_PATH . 'triple_list.php';
include_once WEB_WORD_PATH . 'triple_list.php';

use cfg\verb_list;
use html\word\triple_list as triple_list_dsp;
use cfg\library;
use cfg\phrase;
use cfg\sql_db;
use cfg\triple_list;
use cfg\verb;
use cfg\word;
use cfg\word_list;

class triple_list_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'triple_list->';
        $t->resource_path = 'db/triple/';
        $json_file = 'unit/triple/triple_list.json';
        $usr->set_id(1);

        $t->header('Unit tests of the word link list class (src/main/php/model/word/triple_list.php)');

        $t->subheader('Database query creation tests');

        // load by triple ids
        $trp_lst = new triple_list($usr);
        $trp_ids = array(3,2,4);
        $this->assert_sql_by_ids($t, $db_con, $trp_lst, $trp_ids);

        // load by triple phr
        $trp_lst = new triple_list($usr);
        $phr = new phrase($usr);
        $phr->set_id(5);
        $this->assert_sql_by_phr($t, $db_con, $trp_lst, $phr);

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements
         */

        $db_con = new sql_db();
        $db_con->db_type = sql_db::POSTGRES;

        // sql to load by word link list by ids
        $trp_lst = new triple_list($usr);
        $trp_lst->ids = [1, 2, 3];
        $created_sql = $trp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/triple/triple_list_by_ids_old.sql'); // order adjusted based on the number of usage
        $t->display('triple_list->load_sql by IDs', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        //$t->assert_sql_name_unique($trp_lst->load_sql_name());

        // sql to load by word link list by word and up
        $wrd = new word($usr);
        $wrd->set_id(1);
        $trp_lst = new triple_list($usr);
        $trp_lst->wrd = $wrd;
        $trp_lst->direction = triple_list::DIRECTION_UP;
        $created_sql = $trp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/triple/triple_list_by_up.sql');
        $t->display('triple_list->load_sql by word and up', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($trp_lst->load_sql_name());

        // sql to load by word link list by word and down
        $wrd = new word($usr);
        $wrd->set_id(2);
        $trp_lst = new triple_list($usr);
        $trp_lst->wrd = $wrd;
        $trp_lst->direction = triple_list::DIRECTION_DOWN;
        $created_sql = $trp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/triple/triple_list_by_down.sql');
        $t->display('triple_list->load_sql by word and down', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($trp_lst->load_sql_name());

        // sql to load by word link list by word list and up
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->set_id(1);
        $wrd_lst->add($wrd);
        $wrd = new word($usr);
        $wrd->set_id(2);
        $wrd_lst->add($wrd);
        $trp_lst = new triple_list($usr);
        $trp_lst->wrd_lst = $wrd_lst;
        $trp_lst->direction = triple_list::DIRECTION_UP;
        $created_sql = $trp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/triple/triple_list_by_list_up.sql');
        $t->display('triple_list->load_sql by word list and up', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($trp_lst->load_sql_name());

        // sql to load by word link list by word list and down
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->set_id(2);
        $wrd_lst->add($wrd);
        $wrd = new word($usr);
        $wrd->set_id(3);
        $wrd_lst->add($wrd);
        $trp_lst = new triple_list($usr);
        $trp_lst->wrd_lst = $wrd_lst;
        $trp_lst->direction = triple_list::DIRECTION_DOWN;
        $created_sql = $trp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/triple/triple_list_by_list_down.sql');
        $t->display('triple_list->load_sql by word list and down', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($trp_lst->load_sql_name());

        // sql to load by word link list by word list and down filtered by a verb
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->set_id(2);
        $wrd_lst->add($wrd);
        $wrd = new word($usr);
        $wrd->set_id(3);
        $wrd_lst->add($wrd);
        $vrb = new verb();
        $vrb->set_id(2);
        $trp_lst = new triple_list($usr);
        $trp_lst->wrd_lst = $wrd_lst;
        $trp_lst->vrb = $vrb;
        $trp_lst->direction = triple_list::DIRECTION_DOWN;
        $created_sql = $trp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/triple/triple_list_by_list_up_verb.sql');
        $t->display('triple_list->load_sql by word list and down filtered by a verb', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($trp_lst->load_sql_name());

        // sql to load by word link list by word list and down filtered by a verb list
        $wrd_lst = new word_list($usr);
        $wrd = new word($usr);
        $wrd->set_id(2);
        $wrd_lst->add($wrd);
        $wrd = new word($usr);
        $wrd->set_id(3);
        $wrd_lst->add($wrd);
        $vrb_lst = new verb_list($usr);
        $vrb_lst->ids = [1, 2];
        $trp_lst = new triple_list($usr);
        $trp_lst->wrd_lst = $wrd_lst;
        $trp_lst->vrb_lst = $vrb_lst;
        $trp_lst->direction = triple_list::DIRECTION_DOWN;
        $created_sql = $trp_lst->load_sql($db_con);
        $expected_sql = $t->file('db/triple/triple_list_by_list_down_verb.sql');
        $t->display('triple_list->load_sql by word list and down filtered by a verb list', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($trp_lst->load_sql_name());


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new triple_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->dummy_triple_list();
        $t->assert_api_to_dsp($trp_lst, new triple_list_dsp());

    }

    /**
     * test the SQL statement creation for a triple list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param triple_list $lst the empty triple list object
     * @param array $ids filled with a list of word ids to be used for the query creation
     * @return void
     */
    private function assert_sql_by_ids(test_cleanup $t, sql_db $db_con, triple_list $lst, array $ids): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_ids($db_con, $ids);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_ids($db_con, $ids);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation for a triple list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param triple_list $lst the empty triple list object
     * @param phrase $phr the phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param string $direction to select either the parents, children or all related words ana triples
     * @return void
     */
    private function assert_sql_by_phr(
        test_cleanup $t,
        sql_db       $db_con,
        triple_list  $lst,
        phrase       $phr,
        ?verb        $vrb = null,
        string       $direction = triple_list::DIRECTION_BOTH): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_phr($db_con, $phr, $vrb, $direction);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_phr($db_con, $phr, $vrb, $direction);
        $t->assert_qp($qp, $db_con->db_type);
    }

}