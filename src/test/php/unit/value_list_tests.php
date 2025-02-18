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

namespace unit;

include_once WEB_VALUE_PATH . 'value_list.php';
include_once MODEL_VALUE_PATH . 'value_list.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\value\value_list;
use html\value\value_list as value_list_dsp;
use shared\library;
use test\test_cleanup;

class value_list_tests
{

    public test_cleanup $test;
    public value_list $lst;
    public sql_db $db_con;

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'value_list->';
        $t->resource_path = 'db/value/';

        $t->header('value list unit tests');

        $t->subheader('info value list unit tests');
        $test_name = 'test the grp_ids function';
        $val_ids = $t->value_list()->grp_ids()->dsp_id();
        $t->assert($test_name, $val_ids, 'Pi (math) / Zurich City inhabitants (2019)');

        $t->subheader('modify value list unit tests');
        $time_val_lst = $t->value_list()->filter_by_time($t->phrase_list());

        $t->subheader('api value list unit tests');
        $test_name = 'test the api_json';
        $api_json = $t->value_list()->api_json();
        $val_lst_dsp = new value_list_dsp($api_json);
        $t->assert_json_string($test_name, $val_lst_dsp->api_json(), $api_json);

        $t->subheader('sql creation value list unit tests');
        $test_names = 'sql to load a list of value by ... ';
        $val_lst = new value_list($usr);
        $test_name = $test_names . 'a related to a phrase e.g. all value related to the City of Zurich';
        $phr = $t->phrase_zh_city();
        $this->assert_sql_by_phr($t, $db_con, $val_lst, $phr);
        $test_name = $test_names . 'a list of ids';
        $val_ids = $t->value_list()->id_lst();
        $t->assert_sql_by_ids($sc, $val_lst, $val_ids);
        $test_name = $test_names . 'a list of groups';
        $grp_lst = $t->phrase_list_small();
        $this->assert_sql_by_grp_lst($t, $db_con, $val_lst, $grp_lst);
        $test_name = 'load values related to all phrases of a list '
            . 'e.g. the inhabitants of Canton Zurich over time';
        $t->assert_sql_by_phr_lst($test_name, $val_lst, $t->canton_zh_phrase_list());
        $test_name = 'load values related to any phrase of a list '
            . 'e.g. the match const pi and e';
        // temp line until the function usage is checked correctly by the ide
        $sql = $t->value_list()->load_sql_by_phr_lst($sc, $t->phrase_list_math_const());
        $t->assert_sql_by_phr_lst($test_name, $val_lst, $t->phrase_list_math_const(), true);
        $test_name = 'load values related to any phrase of a longer word and triple list '
            . 'e.g. all phrase related to the math number pi';
        $t->assert_sql_by_phr_lst($test_name, $val_lst, $t->phrase_list(), true);


        $t->subheader('Im- and Export tests');
        $json_file = 'unit/value/travel_scoring_value_list.json';
        $t->assert_json_file(new value_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->value_list();
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
     * @param bool $or if true all values are returned that are linked to any phrase of the list
     */
    private function assert_sql_by_phr_lst(
        test_cleanup $t,
        sql_db       $db_con,
        object       $usr_obj,
        phrase_list  $phr_lst,
        bool         $or = false
    ): void
    {
        // check the Postgres query syntax
        $sc = $db_con->sql_creator();
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_phr_lst($sc, $phr_lst, false, $or);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_phr_lst($sc, $phr_lst, false, $or);
            $t->assert_qp($qp, $sc->db_type);
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
        $sc = $db_con->sql_creator();
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_grp_lst($sc, $phr_lst);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_by_grp_lst($sc, $phr_lst);
            $t->assert_qp($qp, $sc->db_type);
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
        $sc = $db_con->sql_creator();
        $sc->reset(sql_db::POSTGRES);
        $qp = $val_lst->load_sql_by_phr($sc, $phr);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $val_lst->load_sql_by_phr($sc, $phr);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

}