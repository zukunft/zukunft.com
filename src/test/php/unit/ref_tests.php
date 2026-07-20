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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'refs.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class ref_tests
{
    function run(test_cleanup $t): void
    {

        // TODO Prio 1 use the users of $t->env->usr_... instead
        global $usr;
        global $usr_sys;

        // init for reference
        $sc = new sql_creator();
        $t_ref = new test_refs($t);
        $t->name = 'ref->';
        $t->resource_path = 'db/ref/';

        // start the test section (ts)
        $ts = 'unit reference ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $ref = new ref($usr);
        $t->assert_sql_table_create($ref);
        $t->assert_sql_index_create($ref);
        $t->assert_sql_foreign_key_create($ref);

        $t->subheader($ts . 'sql read');
        $t->assert_sql_by_id($sc, $ref);
        $t->assert_sql_by_link($sc, $ref);
        $this->assert_sql_link_ids($t, $sc, $ref);

        $t->subheader($ts . 'sql read standard and user changes by id');
        $ref = new ref($usr);
        $ref->id = 3;
        $t->assert_sql_standard($sc, $ref);
        $ref = $t_ref->reference();
        $t->assert_sql_standard_by_type_link($sc, $ref);

        $t->subheader($ts . 'sql read all type');
        $ref_type_list = new ref_type_list();
        $t->assert_sql_all($sc, $ref_type_list);

        $t->subheader($ts . 'sql write insert');
        $ref = $t_ref->reference();
        $t->assert_sql_insert($sc, $ref);
        $ref_usr = $t_ref->reference_user();
        $t->assert_sql_insert($sc, $ref_usr, [sql_type::USER]);
        $t->assert_sql_insert($sc, $ref_usr, [sql_type::LOG, sql_type::USER]);
        $ref_filled = $t_ref->ref_filled();
        $t->assert_sql_insert($sc, $ref_filled, [sql_type::LOG]);
        $ref_filled_usr = $t_ref->ref_filled_user();
        $t->assert_sql_insert($sc, $ref_filled_usr, [sql_type::LOG, sql_type::USER]);
        $ref = $t_ref->reference_incomplete();
        $t->assert_sql_insert_fail($sc, $ref, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $ref = $t_ref->reference_change();
        $ref_changed = $ref->cloned_linked(refs::CHANGE_NEW_KEY);
        $ref_changed->description = null;
        $t->assert_sql_update($sc, $ref_changed, $ref);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::USER]);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::LOG]);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql delete');
        // TODO log deleting the link by logging the change of the external link to empty
        $t->assert_sql_delete($sc, $ref);
        $t->assert_sql_delete($sc, $ref, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'base object handling');
        $ref = $t_ref->ref_filled();
        $t->assert_reset($ref);

        $t->subheader($ts . 'read access (share)');
        // a non-public reference must not be disclosed to another user via a related-reference list
        // (idor); ref extends sandbox (is_readable_by) and ref_list has its own filter_readable_by
        global $sys;
        $private_id = $sys->typ_lst->shr_typ->id(share_types::PRIVATE);
        $ref_priv = $t_ref->reference();
        $ref_priv->set_owner_id($t->usr1->id);
        $ref_priv->set_share_id($private_id);
        $test_name = 'the owner may read their own private reference';
        $t->assert_true($test_name, $ref_priv->is_readable_by($t->usr1));
        $test_name = 'another user may not read a private reference';
        $t->assert_false($test_name, $ref_priv->is_readable_by($t->usr2));
        $ref_pub = $t_ref->reference();
        $ref_pub->set_owner_id($t->usr1->id);
        $ref_lst = new ref_list($t->usr2);
        $ref_lst->set_lst([$ref_priv, $ref_pub]);
        $ref_lst->filter_readable_by($t->usr2);
        $test_name = 'the readable filter keeps only the public reference for another user';
        $t->assert($test_name, count($ref_lst->lst()), 1);

        $t->subheader($ts . 'api');
        $ref = $t_ref->reference1();
        $t->assert_api_json($ref);
        $ref = $t_ref->reference_plus();
        $t->assert_api($ref);

        $t->subheader($ts . 'frontend');
        $ref = $t_ref->reference_plus();
        $t->assert_api_to_ui($ref, new ref_ui());

        $t->subheader($ts . 'import and export');
        $t->assert_ex_and_import($t_ref->reference(), $usr_sys);
        $t->assert_ex_and_import($t_ref->ref_filled(), $usr_sys);
        $json_file = 'unit/ref/wikipedia.json';
        $t->assert_json_file(new ref($usr), $json_file);

    }

    /**
     * test the SQL statement creation for a value phrase link list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_creator $sc the test database connection
     * @param ref $ref the reference object for which the load by link ids sql statement creation should be tested
     * @return void
     */
    private function assert_sql_link_ids(
        test_cleanup $t,
        sql_creator  $sc,
        ref          $ref): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $ref->load_sql_by_link_ids($sc, 1, 2);
        $t->assert_qp($qp, $sc->db_type);

        // check the MySQL query syntax
        $sc->reset(sql_db::MYSQL);
        $qp = $ref->load_sql_by_link_ids($sc, 1, 2);
        $t->assert_qp($qp, $sc->db_type);
    }

}

