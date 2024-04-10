<?php

/*

  test/unit/view.php - unit testing of the view functions
  ------------------
  

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

use api\view\view as view_api;
use cfg\db\sql;
use cfg\view_link_type;
use cfg\view_term_link;
use cfg\view_type;
use html\view\view as view_dsp;
use cfg\library;
use cfg\db\sql_db;
use cfg\view;
use test\test_cleanup;

class view_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'view->';
        $t->resource_path = 'db/view/';
        $json_file = 'unit/view/car_costs.json';
        $usr->set_id(1);

        $t->header('Unit tests of the view class (src/main/php/model/view/view.php)');


        $t->subheader('SQL setup statements');
        $dsp_typ = new view_type('');
        $t->assert_sql_table_create($dsp_typ);
        $t->assert_sql_index_create($dsp_typ);
        $dsp = $t->dummy_view();
        $t->assert_sql_table_create($dsp);
        $t->assert_sql_index_create($dsp);
        $t->assert_sql_foreign_key_create($dsp);


        $t->subheader('SQL user sandbox statement tests');

        $dsp = new view($usr);
        $t->assert_sql_by_id($sc, $dsp);
        $t->assert_sql_by_name($sc, $dsp);
        $t->assert_sql_by_code_id($sc, $dsp);
        $t->assert_sql_by_term($sc, $dsp, $t->dummy_term());


        $t->subheader('SQL statement tests');

        // sql to load the view by id
        $dsp = new view($usr);
        $dsp->set_id(2);
        //$t->assert_load_sql($db_con, $dsp);
        $t->assert_sql_standard($sc, $dsp);
        $t->assert_sql_user_changes($sc, $dsp);

        // sql to load the view by name
        $dsp = new view($usr);
        $dsp->set_name(view_api::TN_ADD);
        //$t->assert_load_sql($db_con, $dsp);
        $t->assert_sql_standard($sc, $dsp);

        // sql to load the view components
        $dsp = new view($usr);
        $dsp->set_id(2);
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $dsp->load_components_sql($db_con)->sql;
        $expected_sql = $t->file('db/component/components_by_view_id.sql');
        $t->display('view->load_components_sql by view id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($dsp->load_components_sql($db_con)->name);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $dsp->load_components_sql($db_con)->sql;
        $expected_sql = $t->file('db/component/components_by_view_id_mysql.sql');
        $t->display('view->load_components_sql for MySQL', $lib->trim($expected_sql), $lib->trim($created_sql));

        $t->subheader('view sql write');
        // TODO activate db write
        //$t->assert_sql_insert($sc, $dsp);
        //$t->assert_sql_insert($sc, $dsp, true);
        // TODO activate db write
        //$t->assert_sql_update($sc, $dsp);
        //$t->assert_sql_update($sc, $dsp, true);
        // TODO activate db write
        //$t->assert_sql_delete($sc, $dsp);
        //$t->assert_sql_delete($sc, $dsp, true);

        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new view($usr), $json_file);


        $t->subheader('API and frontend cast unit tests for views');

        $dsp = $t->dummy_view();
        $t->assert_api($dsp);
        $t->assert_api_to_dsp($dsp, new view_dsp());

        $dsp = $t->dummy_view_with_components();
        $t->assert_api($dsp, 'view_with_components');
        // TODO activate Prio 1
        //$t->assert_api_to_dsp($dsp, new view_dsp());


        /*
         * Display tests
         */

        $t->subheader('Display tests');

        /*
         * needs database connection
        $dsp = new view_dsp;
        $dsp->id = 1;
        $dsp->code_id = null;
        $dsp->name = view::TEST_NAME_ADD;
        $dsp->usr = $usr;
        $wrd = new word($usr);
        $wrd->set_name(word::TEST_NAME);
        $result = $dsp->display($wrd, 1);
        $target = '';
        $t->display('view->display', $target, $result);
        */


        $t->header('Unit tests of the view term link class (src/main/php/model/view/view_term_link.php)');

        $t->subheader('View link SQL setup statements');
        $dsp_lnk_typ = new view_link_type('');
        $t->assert_sql_table_create($dsp_lnk_typ);
        $t->assert_sql_index_create($dsp_lnk_typ);
        $dsp_trm_lnk = new view_term_link($usr);
        $t->assert_sql_table_create($dsp_trm_lnk);
        $t->assert_sql_index_create($dsp_trm_lnk);
        $t->assert_sql_foreign_key_create($dsp_trm_lnk);

        $t->subheader('SQL user sandbox statement tests');
        $dsp = new view_term_link($usr);
        $t->assert_sql_by_id($sc, $dsp);
    }

}