<?php

/*

    test/unit/ref.php - unit testing of the reference  functions
    -----------------
  

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

use api\ref\source as source_api;
use cfg\db\sql;
use cfg\db\sql_type;
use cfg\ref_type;
use cfg\ref_type_list;
use cfg\source_list;
use cfg\source_type_list;
use api\ref\ref as ref_api;
use html\ref\ref as ref_dsp;
use html\ref\source as source_dsp;
use cfg\ref;
use cfg\source;
use cfg\db\sql_db;
use test\test_cleanup;

class ref_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init for reference
        $sc = new sql();
        $t->name = 'ref->';
        $t->resource_path = 'db/ref/';

        $t->header('reference unit tests');

        $t->subheader('reference sql setup');
        $ref = new ref($usr);
        $t->assert_sql_table_create($ref);
        $t->assert_sql_index_create($ref);
        $t->assert_sql_foreign_key_create($ref);

        $t->subheader('reference sql read');
        $t->assert_sql_by_id($sc, $ref);
        $this->assert_sql_link_ids($t, $sc, $ref);

        $t->subheader('reference sql read standard and user changes by id');
        $ref = new ref($usr);
        $ref->set_id(3);
        $t->assert_sql_standard($sc, $ref);

        $t->subheader('reference sql read all type');
        $ref_type_list = new ref_type_list();
        $t->assert_sql_all($sc, $ref_type_list);

        $t->subheader('reference sql write insert');
        $ref = $t->reference();
        $t->assert_sql_insert($sc, $ref);
        $t->assert_sql_insert($sc, $ref, [sql_type::LOG]);
        $ref_usr = $t->reference_user();
        $t->assert_sql_insert($sc, $ref_usr, [sql_type::USER]);
        $t->assert_sql_insert($sc, $ref_usr, [sql_type::LOG, sql_type::USER]);
        $ref_filled = $t->ref_filled();
        $t->assert_sql_insert($sc, $ref_filled, [sql_type::LOG]);
        $ref_filled_usr = $t->ref_filled_user();
        $t->assert_sql_insert($sc, $ref_filled_usr, [sql_type::LOG, sql_type::USER]);

        $t->subheader('reference sql write update');
        // TODO activate db write
        $ref = $t->reference_change();
        $ref_changed = $ref->cloned_linked(ref_api::TK_CHANGED);
        $t->assert_sql_update($sc, $ref_changed, $ref);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::USER]);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::LOG]);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::LOG, sql_type::USER]);

        $t->subheader('reference sql delete');
        // TODO activate db write and log deleting the link by logging the change of the external link to empty
        $t->assert_sql_delete($sc, $ref);
        $t->assert_sql_delete($sc, $ref, [sql_type::LOG, sql_type::USER]);

        $t->subheader('reference api unit tests');
        $ref = $t->reference1();
        $t->assert_api_json($ref);
        $ref = $t->reference_plus();
        $t->assert_api($ref);

        $t->subheader('reference frontend unit tests');
        $ref = $t->reference_plus();
        $t->assert_api_to_dsp($ref, new ref_dsp());

        $t->subheader('reference import and export tests');
        $json_file = 'unit/ref/wikipedia.json';
        $t->assert_json_file(new ref($usr), $json_file);

    }

    /**
     * test the SQL statement creation for a value phrase link list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql $sc the test database connection
     * @param ref $ref the reference object for which the load by link ids sql statement creation should be tested
     * @return void
     */
    private function assert_sql_link_ids(
        test_cleanup $t,
        sql $sc,
        ref $ref): void
    {
        // check the Postgres query syntax
        $sc->db_type = sql_db::POSTGRES;
        $qp = $ref->load_sql_by_link_ids($sc, 1, 2);
        $t->assert_qp($qp, $sc->db_type);

        // check the MySQL query syntax
        $sc->db_type = sql_db::MYSQL;
        $qp = $ref->load_sql_by_link_ids($sc, 1, 2);
        $t->assert_qp($qp, $sc->db_type);
    }

}

