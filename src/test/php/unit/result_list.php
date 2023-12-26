<?php

/*

    test/unit/result_list.php - unit testing of the FORMULA VALUE functions
    --------------------------------
  

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

use cfg\db\sql;
use cfg\formula;
use cfg\group\group;
use cfg\result\result_list;
use cfg\db\sql_db;
use cfg\triple;
use cfg\user;
use cfg\word;
use html\result\result_list as result_list_dsp;

class result_list_unit_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'result->';
        $t->resource_path = 'db/result/';
        $json_file = 'unit/result/result_list_import_part.json';
        $usr->set_id(1);


        $t->header('Unit tests of the result list class (src/main/php/model/formula/result_list.php)');

        $t->subheader('SQL creation tests');

        // sql to load a list of results by the phrase group id
        $res_lst = new result_list($usr);
        $grp = new group($usr);
        $grp->set_id(2);
        $t->assert_sql_by_group($db_con, $res_lst, $grp);
        $t->assert_sql_by_group($db_con, $res_lst, $grp, true);

        // sql to load a list of results by the formula id
        $this->assert_sql_by_frm($t);

        // sql to load a list of results by the phrase group id
        $res_lst = new result_list($usr);
        $grp = new group($usr);
        $grp->set_id(2);
        // TODO list the results for all users, formulas and sources
        //$t->assert_sql_list_by_ref($db_con, $res_lst, $grp);

        // sql to load a list of results by the source phrase group id
        $res_lst = new result_list($usr);
        $grp = new group($usr);
        $grp->set_id(2);
        // TODO activate
        //$t->assert_sql_list_by_ref($db_con, $res_lst, $grp, true);

        // sql to load a list of results by the word id
        $res_lst = new result_list($usr);
        $wrd = new word($usr);
        $wrd->set_id(2);
        // TODO activate
        //$t->assert_sql_list_by_ref($db_con, $res_lst, $wrd);

        // sql to load a list of results by the triple id
        $res_lst = new result_list($usr);
        $trp = new triple($usr);
        $trp->set_id(3);
        // TODO activate
        //$t->assert_sql_list_by_ref($db_con, $res_lst, $trp);


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new result_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $trp_lst = $t->dummy_result_list();
        $t->assert_api_to_dsp($trp_lst, new result_list_dsp());

    }

    /**
     * result list by formula
     * SQL statement creation test
     * TODO align the other assert sql function to this e.g. use sql
     *
     * not using assert_load_sql because unique for result list
     *
     * @param test_cleanup $t the forwarded testing object
     */
    private function assert_sql_by_frm(test_cleanup $t): void
    {
        // create objects
        $sc = new sql();
        $res_lst = new result_list(new user());
        $frm = $t->dummy_formula();

        // check the Postgres query syntax
        $sc->set_db_type(sql_db::POSTGRES);
        $qp = $res_lst->load_sql_by_frm($sc, $frm);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->set_db_type(sql_db::MYSQL);
            $qp = $res_lst->load_sql_by_frm($sc, $frm);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

}