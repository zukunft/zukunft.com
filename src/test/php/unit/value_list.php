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

use html\value\value_list as value_list_dsp;
use model\library;
use model\phrase;
use model\sql_db;
use model\sql_par;
use model\value_list;
use model\word;

class value_list_unit_tests
{
    const TEST_NAME = 'value_list->';
    const PATH = 'db/value/';
    const FILE_EXT = '.sql';
    const FILE_MYSQL = '_mysql';

    public testing $test;
    public value_list $lst;
    public sql_db $db_con;

    function run(testing $t): void
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

        $db_con->db_type = sql_db::POSTGRES;
        $this->test = $t;

        // sql to load a list of value by the word id
        $wrd = new word($usr);
        $wrd->set_id(1);
        $val_lst = new value_list($usr);
        $val_lst->phr = $wrd->phrase();
        $created_sql = $val_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/value/value_list_by_word_id.sql');
        $t->assert('value_list->load_sql by phrase id', $lib->trim($created_sql), $lib->trim($expected_sql));

        // sql to load a list of value by the phrase ids
        $val_lst = new value_list($usr);
        $val_lst->phr_lst = (new phrase_list_unit_tests)->get_phrase_list();
        $created_sql = $val_lst->load_by_phr_lst_sql($db_con);
        $expected_sql = $t->file('db/value/value_list_by_triple_id_list.sql');
        $t->assert('value_list->load_by_phr_lst_sql by group and time', $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($val_lst->load_by_phr_lst_sql($db_con, true));

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $val_lst->load_by_phr_lst_sql($db_con);
        $expected_sql = $t->file('db/value/value_list_by_triple_id_list_mysql.sql');
        $t->assert('value_list->load_by_phr_lst_sql by group and time for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));


        // sql to load a list of value by the phrase id
        $phr = new phrase($usr);
        $phr->set_id(1);
        $qp = $this->assert_by_phr_sql($phr, sql_db::POSTGRES);
        $this->assert_by_phr_sql($phr, sql_db::MYSQL);
        $this->test->assert_sql_name_unique($qp->name);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new value_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->dummy_value_list();
        $t->assert_api_to_dsp($trp_lst, new value_list_dsp());

    }

    /**
     * test the SQL statement creation for a value list
     *
     * @param phrase $phr filled with an id to be able to load
     * @param string $dialect if not Postgres the name of the SQL dialect
     * @return void
     */
    private function assert_by_phr_sql(phrase $phr, string $dialect = ''): sql_par
    {
        global $usr;

        $lib = new library();

        $lst = new value_list($usr);
        $db_con = new sql_db();
        $db_con->db_type = $dialect;
        $dialect_ext = '';
        if ($dialect == sql_db::MYSQL) {
            $dialect_ext = self::FILE_MYSQL;
        }
        $qp = $lst->load_by_phr_sql($db_con, $phr);
        $expected_sql = $this->test->file(self::PATH . $qp->name . $dialect_ext . self::FILE_EXT);
        $this->test->assert(
            self::TEST_NAME . $qp->name . $dialect,
            $lib->trim($qp->sql),
            $lib->trim($expected_sql)
        );
        return $qp;
    }

}