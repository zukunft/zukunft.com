<?php

/*

  test/unit/formula_link.php - unit testing of the formula link functions
  --------------------------
  

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

include_once MODEL_FORMULA_PATH . 'formula_link_type.php';
include_once MODEL_FORMULA_PATH . 'formula_link_list.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\formula_link;
use cfg\formula_link_list;
use cfg\formula_link_type;
use shared\library;
use test\test_cleanup;

class formula_link_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'formula_link->';
        $t->resource_path = 'db/formula/';

        // TODO use assert_sql_all if possible

        $t->header('Unit tests of the formula link class (src/main/php/model/formula/formula_link.php)');

        $t->subheader('Formula link type SQL setup statements');
        $frm_lnk_typ = new formula_link_type('');
        $t->assert_sql_table_create($frm_lnk_typ);
        $t->assert_sql_index_create($frm_lnk_typ);
        $frm_lnk = $t->formula_link();
        $t->assert_sql_table_create($frm_lnk);
        $t->assert_sql_index_create($frm_lnk);
        $t->assert_sql_foreign_key_create($frm_lnk);

        $t->subheader('SQL user sandbox statement tests');

        // SQL creation tests (mainly to use the IDE check for the generated SQL statements)
        $flk = new formula_link($usr);
        $t->assert_sql_by_id($sc, $flk);
        $t->assert_sql_by_link($sc, $flk);


        $t->subheader('SQL load default statement tests');

        // sql to load the standard formula link by id
        $lnk = new formula_link($usr);
        $lnk->set_id(1);
        $t->assert_sql_standard($sc, $lnk);
        $t->assert_sql_not_changed($sc, $lnk);

        // sql to load the user formula link by id
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $lnk->load_sql_user_changes($db_con->sql_creator())->sql;
        $expected_sql = $t->file('db/formula/formula_link_by_usr_cfg.sql');
        $t->assert('formula_link->load_user_sql by formula link id', $lib->trim($created_sql), $lib->trim($expected_sql));

        $t->subheader('formula link sql write');
        $lnk = $t->formula_link();
        $t->assert_sql_insert($sc, $lnk);
        $t->assert_sql_insert($sc, $lnk, [sql_type::USER]);
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG, sql_type::USER]);
        $lnk_filled = $t->formula_link_filled();
        $t->assert_sql_insert($sc, $lnk_filled, [sql_type::LOG]);
        $lnk_reordered = clone $lnk;
        $lnk_reordered->order_nbr = 1;
        $t->assert_sql_update($sc, $lnk_reordered, $lnk);
        $t->assert_sql_update($sc, $lnk_reordered, $lnk, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $lnk);
        $t->assert_sql_delete($sc, $lnk, [sql_type::LOG, sql_type::USER]);

        /*
        $t->subheader('Im- and Export tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/formula/scale_second_to_minute.json'), true);
        $lnk = new formula($usr);
        $lnk->import_obj($json_in, $t);
        $json_ex = json_decode(json_encode($lnk->export_json(false)), true);
        $result = json_is_similar($json_in, $json_ex);
        $target = true;
        $t->display('formula_link->import check name', $target, $result);
        */

        $t->name = 'formula_link_list->';

        $t->header('Unit tests of the formula link list class (src/main/php/model/formula/formula_link_list.php)');

        $t->subheader('SQL statement tests');

        // sql to load the formula link list by formula id
        $frm_lnk_lst = new formula_link_list($usr);
        $this->assert_sql_by_frm_id($t, $db_con, $frm_lnk_lst);

    }

    /**
     * check the load SQL statements to get a named log entry by field row
     * for all allowed SQL database dialects
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_link_list $frm_lnk
     */
    private function assert_sql_by_frm_id(test_cleanup $t, sql_db $db_con, formula_link_list $frm_lnk): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lnk->load_sql_by_frm_id($db_con->sql_creator(), 7);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lnk->load_sql_by_frm_id($db_con->sql_creator(), 7);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}