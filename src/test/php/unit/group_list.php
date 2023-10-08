<?php

/*

    test/unit/group_list.php - testing of the phrase group list functions
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

include_once MODEL_PHRASE_PATH . 'phrase_group_list.php';

use cfg\group\group_list;
use cfg\sql_db;

class group_list_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'group_list->';
        $t->resource_path = 'db/group/';
        $usr->set_id(1);

        $t->header('Unit tests of the phrase group list class (src/main/php/model/group/group_list.php)');

        $t->subheader('Database query creation tests');

        // load by triple ids
        $grp_lst = new group_list($usr);
        $t->assert_sql_by_ids($db_con, $grp_lst, array(3,2,4));
        $t->assert_sql_names_by_ids($db_con, $grp_lst, array(3,2,4));
        $this->assert_sql_by_phrase($t, $db_con, $grp_lst);

    }

    /**
     * check the SQL statements creation to get all phrase groups related to obe phrase
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param group_list $lst the phrase group list object used for testing
     * @return void true if all tests are fine
     */
    private function assert_sql_by_phrase(test_cleanup $t, sql_db $db_con, group_list $lst): void
    {
        // prepare
        $wrd = $t->dummy_word();
        $trp = $t->dummy_triple();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_phr($db_con->sql_creator(), $wrd->phrase());
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst->load_sql_by_phr($db_con->sql_creator(), $wrd->phrase());
            $t->assert_qp($qp, $db_con->db_type);
        }

        // ... and for a triple with Postgres query syntax
        if ($result) {
            $db_con->db_type = sql_db::POSTGRES;
            $qp = $lst->load_sql_by_phr($db_con->sql_creator(), $trp->phrase());
            $t->assert_qp($qp, $db_con->db_type);
        }

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst->load_sql_by_phr($db_con->sql_creator(), $trp->phrase());
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}