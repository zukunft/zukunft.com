<?php

/*

    test/unit/word.php - unit testing of the word functions
    ------------------


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

include_once DB_PATH . 'sql_db.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once API_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'word.php';

use api\formula\formula as formula_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\phrase_type;
use cfg\word;
use api\word\word as word_api;
use html\word\word as word_dsp;
use test\test_cleanup;

class word_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'word->';
        $t->resource_path = 'db/word/';
        $json_file = 'unit/word/second.json';
        $usr->set_id(1);

        $t->header('word unit tests');

        $t->subheader('word sql setup');
        $wrd = $t->word();
        $t->assert_sql_table_create($wrd);
        $t->assert_sql_index_create($wrd);
        $t->assert_sql_foreign_key_create($wrd);

        $t->subheader('word sql read');
        $wrd = new word($usr);
        $t->assert_sql_by_id($sc, $wrd);
        $t->assert_sql_by_name($sc, $wrd);
        $this->assert_sql_formula_name($t, $db_con, $wrd);

        $t->subheader('word sql read default and user changes');
        $wrd = new word($usr);
        $wrd->set_id(word_api::TI_CONST);
        $t->assert_sql_standard($sc, $wrd);
        $t->assert_sql_not_changed($sc, $wrd);
        $t->assert_sql_user_changes($sc, $wrd);
        $t->assert_sql_changing_users($sc, $wrd);
        $this->assert_sql_view($t, $db_con, $wrd);

        $t->subheader('word sql write');
        $wrd = $t->word();
        $t->assert_sql_insert($sc, $wrd);
        $t->assert_sql_insert($sc, $wrd, [sql_type::USER]);
        // TODO activate db write with log
        $t->assert_sql_insert($sc, $wrd, [sql_type::LOG, sql_type::NAMED_PAR]);
        //$t->assert_sql_insert($sc, $wrd, [sql_type::LOG, sql_type::USER]);
        $wrd_renamed = $wrd->cloned(word_api::TN_RENAMED);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd);
        //$t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::USER]);
        // TODO activate db write with log
        //$t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::LOG]);
        //$t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $wrd);
        $t->assert_sql_delete($sc, $wrd, [sql_type::USER]);
        // TODO activate db write with log
        //$t->assert_sql_delete($sc, $wrd, [sql_type::LOG]);
        //$t->assert_sql_delete($sc, $wrd, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::USER, sql_type::EXCLUDE]);
        // TODO activate db write with log
        //$t->assert_sql_delete($sc, $wrd, [sql_type::LOG], true);
        //$t->assert_sql_delete($sc, $wrd, [sql_type::LOG, sql_type::USER], true);


        $t->subheader('word api unit tests');

        $wrd = new word($usr);
        $wrd->set(1, word_api::TN_READ, phrase_type::MATH_CONST);
        $wrd->description = word_api::TD_READ;
        $api_wrd = $wrd->api_obj();
        $t->assert($t->name . 'api->id', $api_wrd->id, $wrd->id());
        $t->assert($t->name . 'api->name', $api_wrd->name, $wrd->name_dsp());
        $t->assert($t->name . 'api->description', $api_wrd->description, $wrd->description);


        $t->subheader('word im- and export unit tests');

        $t->assert_json_file(new word($usr), $json_file);

        $test_name = 'check if database would not be updated if only the name is given in import';
        $in_wrd = $t->word_name_only();
        $db_wrd = $t->word_filled();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_wrd->needs_db_update($db_wrd), false);


        $t->subheader('word HTML frontend unit tests');

        $wrd = $t->word();
        $t->assert_api_to_dsp($wrd, new word_dsp());

    }

    /**
     * check the load SQL statements creation to get the word corresponding to the formula name
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param word $wrd the user sandbox object e.g. a word
     * @return void true if all tests are fine
     */
    private function assert_sql_formula_name(test_cleanup $t, sql_db $db_con, word $wrd): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $wrd->load_sql_by_formula_name($db_con->sql_creator(), formula_api::TN_READ);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $wrd->load_sql_by_formula_name($db_con->sql_creator(), formula_api::TN_READ);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements creation to get the view
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param word $wrd the user sandbox object e.g. a word
     * @return void true if all tests are fine
     */
    private function assert_sql_view(test_cleanup $t, sql_db $db_con, word $wrd): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $wrd->view_sql($db_con);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $wrd->view_sql($db_con);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}