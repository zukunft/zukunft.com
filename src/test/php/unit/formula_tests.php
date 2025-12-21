<?php

/*

    test/unit/formula.php - unit testing of the formula database and map functions
    ---------------------

    TODO move the sql tests to a separate class
  

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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_FORMULA . 'expression.php';
include_once html_paths::ELEMENT . 'element_group.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $sc = new sql_creator();
        $t_frm = new test_formulas($t);
        $t->name = 'formula->';
        $t->resource_path = 'db/formula/';

        // start the test section (ts)
        $ts = 'unit formula map ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $frm = $t_frm->formula();
        $t->assert_sql_table_create($frm);
        $t->assert_sql_index_create($frm);
        $t->assert_sql_foreign_key_create($frm);

        $t->subheader($ts . 'sql read');
        $frm = new formula($usr);
        $t->assert_sql_by_id($sc, $frm);
        $t->assert_sql_by_name($sc, $frm);

        $t->subheader($ts . 'sql read default and user changes by id');
        $frm = new formula($usr);
        $frm->id = formulas::SCALE_HOUR_ID;
        $t->assert_sql_standard($sc, $frm);
        $t->assert_sql_not_changed($sc, $frm);
        $t->assert_sql_user_changes($sc, $frm);
        $this->assert_sql_user_changes_frm($t, $frm);

        $t->subheader($ts . 'sql read default by name');
        $frm = new formula($usr);
        $frm->set_name(formulas::SCALE_MIO_EXP);
        $t->assert_sql_standard($sc, $frm);

        $t->subheader($ts . 'sql write insert');
        $frm = $t_frm->formula();
        $t->assert_sql_insert($sc, $frm);
        $t->assert_sql_insert($sc, $frm, [sql_type::USER]);
        $t->assert_sql_insert($sc, $frm, [sql_type::LOG, sql_type::USER]);
        $frm = $t_frm->formula_name_only();
        $t->assert_sql_insert($sc, $frm);
        $frm = $t_frm->formula_filled();
        $t->assert_sql_insert($sc, $frm, [sql_type::LOG]);
        $frm = $t_frm->formula_incomplete();
        $t->assert_sql_insert_fail($sc, $frm, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $frm = $t_frm->formula_name_only();
        $frm_renamed = $frm->cloned(formulas::SYSTEM_TEST_RENAMED);
        $t->assert_sql_update($sc, $frm_renamed, $frm);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::USER]);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::LOG]);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $frm);
        $t->assert_sql_delete($sc, $frm, [sql_type::USER]);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $frm, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $frm, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'base object handling');
        $frm = $t_frm->formula_filled();
        $t->assert_reset($frm);
        $frm = $t_frm->formula_filled();
        $t->assert_db_ready($frm);
        $frm = $t_frm->formula_filled_not_db_ready();
        $t->assert_not_db_ready($frm);

        $t->subheader($ts . 'api');
        $frm = $t_frm->formula_filled();
        $t->assert_api_json($frm);
        $frm->include();
        $t->assert_api($frm, 'formula_body');

        $t->subheader($ts . 'frontend');
        $frm = $t_frm->formula();
        $t->assert_api_to_ui($frm, new formula_ui());

        $t->subheader($ts . 'im- and export');
        $t->assert_ex_and_import($t_frm->formula(), $usr_sys);
        $t->assert_ex_and_import($t_frm->formula_filled(), $usr_sys);
        $json_file = 'unit/formula/scale_second_to_minute.json';
        $t->assert_json_file(new formula($usr), $json_file);

    }

    /**
     * TODO check the diff to assert_sql_user_changes
     *
     * @param test_cleanup $t the test environment
     * @param formula $frm the user sandbox object e.g. a word
     */
    private function assert_sql_user_changes_frm(test_cleanup $t, formula $frm): void
    {
        $db_con = new sql_db();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm->load_sql_user_changes_frm($db_con);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm->load_sql_user_changes_frm($db_con);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}