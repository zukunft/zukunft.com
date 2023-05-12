<?php

/*

  test/unit/component_link.php - unit testing of the VIEW COMPONENT LINK functions
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

namespace test;

include_once MODEL_VIEW_PATH . 'component_link_list.php';

use model\sql_db;
use model\view;
use model\component;
use model\component_link;
use model\component_link_list;

class component_link_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'view->';
        $t->resource_path = 'db/view/';
        $usr->set_id(1);

        $t->header('Unit tests of the view component link class (src/main/php/model/view/component_link.php)');


        $t->subheader('SQL user sandbox statement tests');

        // SQL creation tests (mainly to use the IDE check for the generated SQL statements)
        $vcl = new component_link($usr);
        $t->assert_load_sql_id($db_con, $vcl);
        $t->assert_load_sql_link($db_con, $vcl);


        $t->subheader('SQL statement tests');

        // sql to load a view component link by the id
        $lnk = new component_link($usr);
        $lnk->set_id(1);
        //$t->assert_load_sql($db_con, $lnk);
        $t->assert_user_config_sql($db_con, $lnk);

        // sql to load a list of value by the phrase ids
        $lnk = new component_link($usr);
        $lnk->dsp->set_id(1);
        $lnk->cmp->set_id(2);
        $t->assert_load_sql_obj_vars($db_con, $lnk);


        $t->subheader('Database list query creation tests');

        // sql to load a view component link list by view id
        $dsp_cmp_lnk_lst = new component_link_list($usr);
        $dsp = new view($usr);
        $dsp->set_id(2);
        $this->assert_lst_sql_all($t, $db_con, $dsp_cmp_lnk_lst, $dsp);

        // sql to load a view component link list by component id
        $dsp_cmp_lnk_lst = new component_link_list($usr);
        $cmp = new component($usr);
        $cmp->set_id(3);
        $this->assert_lst_sql_all($t, $db_con, $dsp_cmp_lnk_lst, null, $cmp);

    }

    /**
     * test the SQL statement creation for a value phrase link list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link_list $lst filled with an id to be able to load
     * @param view|null $dsp the view used for selection
     * @param component|null $cmp the component used for selection
     * @return void
     */
    private function assert_lst_sql_all(test_cleanup $t, sql_db $db_con, component_link_list $lst, ?view $dsp = null, ?component $cmp = null): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_sql($db_con, $dsp, $cmp);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $lst->load_sql($db_con, $dsp, $cmp);
        $t->assert_qp($qp, sql_db::MYSQL);
    }

}