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

namespace unit;

include_once MODEL_WORD_PATH . 'triple_list.php';
include_once WEB_WORD_PATH . 'triple_list.php';

use api\word\triple as triple_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\phrase;
use cfg\phrase_list;
use cfg\triple;
use cfg\triple_list;
use cfg\verb;
use html\word\triple_list as triple_list_dsp;
use shared\enum\foaf_direction;
use test\test_cleanup;

class triple_list_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'triple_list->';
        $t->resource_path = 'db/triple/';
        $json_file = 'unit/triple/triple_list.json';

        $t->header('Unit tests of the word link list class (src/main/php/model/word/triple_list.php)');

        $t->subheader('Database query creation tests');

        // load only the names
        $trp_lst = new triple_list($usr);
        $t->assert_sql_names($sc, $trp_lst, new triple($usr));
        $t->assert_sql_names($sc, $trp_lst, new triple($usr), triple_api::TD_READ);

        // load by triple ids
        $trp_lst = new triple_list($usr);
        $t->assert_sql_by_ids($sc, $trp_lst, array(3,2,4));

        // load by phr
        $trp_lst = new triple_list($usr);
        $phr = new phrase($usr);
        $phr->set_id(5);
        $this->assert_sql_by_phr($t, $db_con, $trp_lst, $phr);
        $vrb = new verb(1);
        $this->assert_sql_by_phr($t, $db_con, $trp_lst, $phr, $vrb, foaf_direction::UP);
        $this->assert_sql_by_phr($t, $db_con, $trp_lst, $phr, $vrb, foaf_direction::DOWN);

        // load by phrase list
        $trp_lst = new triple_list($usr);
        $phr = new phrase($usr);
        $phr->set_id(6);
        $phr2 = new phrase($usr);
        $phr2->set_id(7);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($phr);
        $phr_lst->add($phr2);
        $this->assert_sql_by_phr_lst($t, $db_con, $trp_lst, $phr_lst, null,  foaf_direction::UP);
        $this->assert_sql_by_phr_lst($t, $db_con, $trp_lst, $phr_lst);
        $vrb = new verb(1);
        $this->assert_sql_by_phr_lst($t, $db_con, $trp_lst, $phr_lst, $vrb, foaf_direction::UP);
        $this->assert_sql_by_phr_lst($t, $db_con, $trp_lst, $phr_lst, $vrb, foaf_direction::DOWN);
        // TODO activate Prio 1
        // $this->assert_sql_by_phr_lst($t, $db_con, $trp_lst, $phr_lst, $vrb);


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new triple_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->triple_list();
        $t->assert_api_to_dsp($trp_lst, new triple_list_dsp());

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
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return void
     */
    private function assert_sql_by_phr(
        test_cleanup   $t,
        sql_db         $db_con,
        triple_list    $lst,
        phrase         $phr,
        ?verb          $vrb = null,
        foaf_direction $direction = foaf_direction::BOTH): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_phr($db_con->sql_creator(), $phr, $vrb, $direction);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_phr($db_con->sql_creator(), $phr, $vrb, $direction);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation for a triple list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param triple_list $lst the empty triple list object
     * @param phrase_list $phr_lst a list of phrases which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return void
     */
    private function assert_sql_by_phr_lst(
        test_cleanup   $t,
        sql_db         $db_con,
        triple_list    $lst,
        phrase_list    $phr_lst,
        ?verb          $vrb = null,
        foaf_direction $direction = foaf_direction::BOTH): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_phr_lst($db_con->sql_creator(), $phr_lst, $vrb, $direction);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_phr_lst($db_con->sql_creator(), $phr_lst, $vrb, $direction);
        $t->assert_qp($qp, $db_con->db_type);
    }

}