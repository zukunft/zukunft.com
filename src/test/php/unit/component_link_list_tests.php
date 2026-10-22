<?php

/*

    test/unit/component_link_list.php - testing the links between views and components
    ---------------------------------
  

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

include_once paths::MODEL_COMPONENT . 'component_link_list.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component_link_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class component_link_list_tests
{

    // the components of the split by view test
    const int TV_CMP_ID_1 = 1;
    const string TV_CMP_NAME_1 = 'first component of the start view';
    const int TV_CMP_ID_2 = 2;
    const string TV_CMP_NAME_2 = 'component of the math view';
    const int TV_CMP_ID_3 = 3;
    const string TV_CMP_NAME_3 = 'second component of the start view';

    // the links of the add test
    const int TV_LNK_ID_1 = 1;
    const int TV_LNK_ID_2 = 2;
    const int TV_POS_1 = 1;

    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'component_link_list->';
        $t->resource_path = 'db/component/';


        $ts = 'unit component link list ';
        $t->header($ts);

        $t->subheader($ts . 'sql query');

        // load by component_link ids
        $lst = new component_link_list($t->usr1);
        $this->assert_sql_by_ids($t, $db_con, $lst);

        // without an id the query has no name, so that the caller does not send it to the database
        $test_name = 'the component link list query of an empty id list is not prepared';
        $db_con->db_type = sql_db::POSTGRES;
        $t->assert($test_name, $lst->load_sql_by_ids($db_con->sql_creator(), [])->name, '');

        // load by view
        $lst = new component_link_list($t->usr1);
        $this->assert_sql_by_view($t, $db_con, $lst);

        // load by component
        $lst = new component_link_list($t->usr1);
        $this->assert_sql_by_component($t, $db_con, $lst);

        // load the links of all system views with one query
        $lst = new component_link_list($t->usr1);
        $this->assert_sql_by_view_ids($t, $db_con, $lst);
        $test_name = 'the component link list query of an empty view id list is not prepared';
        $db_con->db_type = sql_db::POSTGRES;
        $t->assert($test_name, $lst->load_sql_by_view_ids($db_con->sql_creator(), [])->name, '');

        // ... but with a view id the query is named, so that the caller sends it to the database
        $test_name = 'the component link list query of a view id list is prepared';
        $t_msk = new test_views($t);
        $view_ids = [$t_msk->view()->id()];
        $qp = $lst->load_sql_by_view_ids($db_con->sql_creator(), $view_ids);
        $t->assert_not($test_name, $qp->name, '');


        $t->subheader($ts . 'split by view');

        // the links of all system views are loaded at once and then split into the list of each view
        $t_msk = new test_views($t);
        $t_cmp = new test_components($t);
        $lst = new component_link_list($t->usr1);
        $lst->add(1, $t_msk->view(), $t_cmp->component_text(self::TV_CMP_ID_1, self::TV_CMP_NAME_1), 1);
        $lst->add(2, $t_msk->view_math(), $t_cmp->component_text(self::TV_CMP_ID_2, self::TV_CMP_NAME_2), 1);
        $lst->add(3, $t_msk->view(), $t_cmp->component_text(self::TV_CMP_ID_3, self::TV_CMP_NAME_3), 2);
        $test_name = 'the links of a view are split from the links of all views';
        $t->assert($test_name, $lst->by_view_id($t_msk->view()->id())->cmp_ids(), [self::TV_CMP_ID_1, self::TV_CMP_ID_3]);
        $test_name = '... and the links of another view are split as well';
        $t->assert($test_name, $lst->by_view_id($t_msk->view_math()->id())->cmp_ids(), [self::TV_CMP_ID_2]);
        // negative: a view without a component gets an empty list and not the links of another view
        $test_name = 'a view without a component link gets no link';
        $t->assert_true($test_name, $lst->by_view_id($t_msk->word()->id())->is_empty());

        $t->subheader($ts . 'add');

        // a component link is added to an empty list
        $test_name = 'add a component link';
        $lst = new component_link_list($t->usr1);
        $cmp = $t_cmp->component_text(self::TV_CMP_ID_1, self::TV_CMP_NAME_1);
        $added = $lst->add(self::TV_LNK_ID_1, $t_msk->view(), $cmp, self::TV_POS_1);
        $t->assert_true($test_name, $added);

        // negative: the same component at the same position of the same view is refused
        $test_name = 'add refuses the same component link';
        $lst = new component_link_list($t->usr1);
        $cmp = $t_cmp->component_text(self::TV_CMP_ID_1, self::TV_CMP_NAME_1);
        $lst->add(self::TV_LNK_ID_1, $t_msk->view(), $cmp, self::TV_POS_1);
        $added = $lst->add(self::TV_LNK_ID_2, $t_msk->view(), $cmp, self::TV_POS_1);
        $t->assert_false($test_name, $added);

        $t->subheader($ts . 'export');
        $msg = new user_message($t->usr1);

        // a component link list exports one entry per component link
        $test_name = 'export second link';
        $lst = $t_cmp->component_link_list();
        $exp = $lst->export_json($msg, [], false);
        $second = $exp[1];
        $cmp_exp = $second[json_fields::COMPONENT];
        $t->assert($test_name, $cmp_exp[json_fields::NAME], components::MATRIX_NAME);
        $msg->reset();

        // negative: a component link list exports no entry beyond its component links
        $test_name = 'export no third link';
        $lst = $t_cmp->component_link_list();
        $exp = $lst->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, 2, $exp);
        $msg->reset();

    }

    /**
     * test the SQL statement creation to load the component links of several views at once
     * e.g. of all system views, in all SQL dialect and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link_list $lst the empty component_link list object
     * @return void
     */
    private function assert_sql_by_view_ids(
        test_cleanup        $t,
        sql_db              $db_con,
        component_link_list $lst
    ): void
    {
        $t_msk = new test_views($t);
        $ids = [$t_msk->view()->id(), $t_msk->view_math()->id()];

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_view_ids($db_con->sql_creator(), $ids);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_view_ids($db_con->sql_creator(), $ids);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation to load the components of a view
     * in all SQL dialect and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link_list $lst the empty component_link list object
     * @return void
     */
    private function assert_sql_by_view(
        test_cleanup        $t,
        sql_db              $db_con,
        component_link_list $lst
    ): void
    {
        $t_msk = new test_views($t);
        $msk = $t_msk->view();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_view($db_con->sql_creator(), $msk);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_view($db_con->sql_creator(), $msk);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation to load the views of a component
     * in all SQL dialect and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link_list $lst the empty component_link list object
     * @return void
     */
    private function assert_sql_by_component(
        test_cleanup        $t,
        sql_db              $db_con,
        component_link_list $lst
    ): void
    {
        $t_cmp = new test_components($t);
        $cmp = $t_cmp->component();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_component($db_con->sql_creator(), $cmp);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_component($db_con->sql_creator(), $cmp);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation to load the component links by their ids
     * in all SQL dialect and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link_list $lst the empty component_link list object
     * @return void
     */
    private function assert_sql_by_ids(
        test_cleanup        $t,
        sql_db              $db_con,
        component_link_list $lst
    ): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql_by_ids($db_con->sql_creator(), [1, 2]);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql_by_ids($db_con->sql_creator(), [1, 2]);
        $t->assert_qp($qp, $db_con->db_type);
    }

}