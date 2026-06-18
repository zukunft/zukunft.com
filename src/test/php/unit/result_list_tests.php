<?php

/*

    test/unit/result_list.php - unit testing of the FORMULA VALUE functions
    -------------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class result_list_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_res = new test_results($t);
        $t_phr = new test_phrases($t);
        $t_frm = new test_formulas($t);
        $t->name = 'result_list->';
        $t->resource_path = 'db/result/';
        $res_lst = new result_list($usr);

        // start the test section (ts)
        $ts = 'unit result list ';
        $t->header($ts);

        $t->subheader($ts . 'sql creation');

        $test_name = 'load a list of results that are a related to all phrases of a list '
            . 'e.g. the yearly increase of inhabitants of canton Zurich over time';
        $t->assert_sql_by_phr_lst($test_name, $res_lst, $t_phr->canton_zh_phrase_list());
        $test_name = 'load a list of results that are a related a formula '
            . 'e.g. to update the results if the formula has been updated';
        $this->assert_sql_by_frm($test_name, $t_frm->formula(), $t);
        $test_name = 'load a list of results that are a based on all phrases of a list '
            . 'e.g. to update the results if the value has been updated';
        $this->assert_sql_by_src($test_name, $t_phr->canton_zh_phrase_list(), $t);

        $grp = new group($usr);
        $grp->set_id(2);
        $t->assert_sql_by_group($sc, $res_lst, $grp);
        $t->assert_sql_by_group($sc, $res_lst, $grp, true);

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
        // TODO Prio 1 activate
        //$t->assert_sql_list_by_ref($db_con, $res_lst, $grp, true);

        // sql to load a list of results by the word id
        $res_lst = new result_list($usr);
        // TODO Prio 1 activate
        //$wrd = new word($usr);
        //$wrd->id = 2;
        //$t->assert_sql_list_by_ref($db_con, $res_lst, $wrd);

        // sql to load a list of results by the triple id
        $res_lst = new result_list($usr);
        // TODO Prio 1 activate
        //$trp = new triple($usr);
        //$trp->id = 3;
        //$t->assert_sql_list_by_ref($db_con, $res_lst, $trp);


        $t->subheader($ts . 'im- and export');

        $json_file = 'unit/result/result_list_import_part.json';
        $t->assert_json_file(new result_list($usr), $json_file);


        $t->subheader($ts . 'html frontend');

        $trp_lst = $t_res->result_list();
        $t->assert_api_to_ui($trp_lst, new result_list_ui());

    }

    /**
     * result list by formula
     * SQL statement creation test
     * TODO align the other assert sql function to this e.g. use sql
     *
     * not using assert_load_sql because unique for result list
     *
     * @param string $test_name the description of the test
     * @param test_cleanup $t the forwarded testing object
     */
    private function assert_sql_by_frm(
        string $test_name,
        formula $frm,
        test_cleanup $t): void
    {
        // create objects
        $sc = new sql_creator();
        $res_lst = new result_list(new user());

        // check the Postgres query syntax
        $sc->set_db_type(sql_db::POSTGRES);
        $qp = $res_lst->load_sql_by_frm($sc, $frm);
        $result = $t->assert_qp($qp, $sc->db_type, $test_name);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->set_db_type(sql_db::MYSQL);
            $qp = $res_lst->load_sql_by_frm($sc, $frm);
            $t->assert_qp($qp, $sc->db_type, $test_name);
        }
    }

    /**
     * result list by source
     * SQL statement creation test
     * TODO align the other assert sql function to this e.g. use sql
     *
     * not using assert_load_sql because unique for result list
     *
     * @param string $test_name the description of the test
     * @param phrase_list $phr_lst the list of source phrases
     * @param test_cleanup $t the forwarded testing object
     */
    private function assert_sql_by_src(
        string $test_name,
        phrase_list $phr_lst,
        test_cleanup $t): void
    {
        // create objects
        $sc = new sql_creator();
        $res_lst = new result_list(new user());

        // check the Postgres query syntax
        $sc->set_db_type(sql_db::POSTGRES);
        $qp = $res_lst->load_sql_by_src($sc, $phr_lst);
        $result = $t->assert_qp($qp, $sc->db_type, $test_name);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->set_db_type(sql_db::MYSQL);
            $qp = $res_lst->load_sql_by_src($sc, $phr_lst);
            $t->assert_qp($qp, $sc->db_type, $test_name);
        }
    }

}