<?php

/*

    test/unit/value_list.php - unit testing of the VALUE LIST functions
    ------------------------
  

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

include_once WEB_VALUE_PATH . 'value_list.php';
include_once MODEL_VALUE_PATH . 'value_list.php';

use cfg\db\sql_par;
use cfg\value\value_list;
use html\value\value_list as value_list_dsp;
use cfg\library;
use cfg\phrase;
use cfg\phrase_list;
use cfg\db\sql_db;
use cfg\word;

class value_list_unit_tests
{

    public test_cleanup $test;
    public value_list $lst;
    public sql_db $db_con;

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'value_list->';
        $t->resource_path = 'db/value/';
        $json_file = 'unit/value/travel_scoring_value_list.json';
        $usr->set_id(1);

        $t->header('Unit tests of the value list class (src/main/php/model/value/value_list.php)');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        // sql to load a list of value by ids
        $val_lst = new value_list($usr);
        $t->assert_sql_by_ids($db_con, $val_lst, [5,6]);
        $this->assert_sql_by_grp_lst($t, $db_con, $val_lst, $t->dummy_phrase_list_small());
        // sql to load all values related to a phrase list e.g. the inhabitants of Canton Zurich over time
        //$this->assert_sql_by_phr_lst($t, $db_con, $val_lst, $t->dummy_phrase_list_zh());

        $db_con->db_type = sql_db::POSTGRES;
        $this->test = $t;

        // sql to load a list of value by the word id
        $wrd = new word($usr);
        $wrd->set_id(1);
        $val_lst = new value_list($usr);
        $val_lst->phr = $wrd->phrase();
        $created_sql = $val_lst->load_old_sql($db_con)->sql;
        $expected_sql = $t->file('db/value/value_list_by_word_id.sql');
        // TODO activate (but based on new sql creator
        //$t->assert('value_list->load_sql by phrase id', $lib->trim($created_sql), $lib->trim($expected_sql));

        // sql to load a list of value by the phrase ids
        $val_lst = new value_list($usr);
        $val_lst->phr_lst = (new phrase_list_unit_tests)->get_phrase_list();
        // TODO change to load_sql_by_phr_lst
        $created_sql = $val_lst->load_by_phr_lst_sql_old($db_con);
        $expected_sql = $t->file('db/value/value_list_by_triple_id_list.sql');
        $t->assert('value_list->load_by_phr_lst_sql by group and time', $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($val_lst->load_by_phr_lst_sql_old($db_con, true));

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $val_lst->load_by_phr_lst_sql_old($db_con);
        $expected_sql = $t->file('db/value/value_list_by_triple_id_list_mysql.sql');
        $t->assert('value_list->load_by_phr_lst_sql by group and time for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));

        // TODO add a test to select a list of values that contains any phrase of the phrase list
        // TODO add a test to select a list of values that contains all phrase of the phrase list

        // sql to load a list of value by the phrase id
        $phr = $t->dummy_triple_pi()->phrase();
        $this->assert_sql_by_phr($t, $db_con, $val_lst, $phr);


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new value_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->dummy_value_list();
        $t->assert_api_to_dsp($trp_lst, new value_list_dsp());

    }

    /**
     * test the SQL statement creation for a value list
     * similar to assert_load_sql but for a phrase list
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param phrase_list $phr_lst the phrase list that should be used for the sql creation
     */
    private function assert_sql_by_phr_lst(test_cleanup $t, sql_db $db_con, object $usr_obj, phrase_list $phr_lst): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_phr_lst($db_con->sql_creator(), $phr_lst);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_phr_lst($db_con->sql_creator(), $phr_lst);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * test the SQL statement creation for a value list
     * similar to assert_load_sql but for a group list
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param phrase_list $phr_lst the phrase list that should be used for the sql creation
     */
    private function assert_sql_by_grp_lst(test_cleanup $t, sql_db $db_con, object $usr_obj, phrase_list $phr_lst): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_grp_lst($db_con->sql_creator(), $phr_lst);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_grp_lst($db_con->sql_creator(), $phr_lst);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * test the SQL statement creation for a value list
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param value_list $val_lst the value list object that should ve filled
     * @param phrase $phr filled with an id to be able to load
     * @return void
     */
    private function assert_sql_by_phr(test_cleanup $t, sql_db $db_con, value_list $val_lst, phrase $phr): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $val_lst->load_sql_by_phr($db_con->sql_creator(), $phr);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $val_lst->load_sql_by_phr($db_con->sql_creator(), $phr);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}