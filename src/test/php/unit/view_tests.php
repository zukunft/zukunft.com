<?php

/*

    test/unit/view_tests.php - unit testing of the view functions
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace unit;

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\view\view;
use html\view\view as view_dsp;
use shared\library;
use shared\const\views;
use test\test_cleanup;

class view_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $sc = new sql_creator();
        $t->name = 'view->';
        $t->resource_path = 'db/view/';

        // start the test section (ts)
        $ts = 'unit view ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $msk = $t->view();
        $t->assert_sql_table_create($msk);
        $t->assert_sql_index_create($msk);
        $t->assert_sql_foreign_key_create($msk);

        $t->subheader($ts . 'sql read');
        $msk = new view($usr);
        $t->assert_sql_by_id($sc, $msk);
        $t->assert_sql_by_name($sc, $msk);
        $t->assert_sql_by_code_id($sc, $msk);
        $t->assert_sql_by_term($sc, $msk, $t->term());

        $t->subheader($ts . 'sql read standard and user changes by id');
        $msk = new view($usr);
        $msk->set_id(2);
        //$t->assert_load_sql($db_con, $msk);
        $t->assert_sql_standard($sc, $msk);
        $t->assert_sql_user_changes($sc, $msk);

        $t->subheader($ts . 'sql read standard and user changes by name');
        $msk = new view($usr);
        $msk->set_name(views::START_NAME);
        //$t->assert_load_sql($db_con, $msk);
        $t->assert_sql_standard($sc, $msk);

        $t->subheader($ts . 'sql write insert');
        $msk = $t->view_added();
        $t->assert_sql_insert($sc, $msk);
        $t->assert_sql_insert($sc, $msk, [sql_type::USER]);
        $t->assert_sql_insert($sc, $msk, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $msk, [sql_type::LOG, sql_type::USER]);
        $msk = $t->view(); // a view with a code_id as it might be imported
        $t->assert_sql_insert($sc, $msk, [sql_type::LOG]);
        $msk = $t->view_filled();
        $t->assert_sql_insert($sc, $msk, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $msk = $t->view_added();
        $msk_renamed = $msk->cloned(views::TEST_RENAMED_NAME);
        $t->assert_sql_update($sc, $msk_renamed, $msk);
        $t->assert_sql_update($sc, $msk_renamed, $msk, [sql_type::USER]);
        $t->assert_sql_update($sc, $msk_renamed, $msk, [sql_type::LOG]);
        $t->assert_sql_update($sc, $msk_renamed, $msk, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $msk);
        $t->assert_sql_delete($sc, $msk, [sql_type::USER]);
        $t->assert_sql_delete($sc, $msk, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $msk, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $msk, [sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $msk, [sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader($ts . 'base object handling');
        $msk = $t->view_filled();
        $t->assert_reset($msk);

        $t->subheader($ts . 'api');
        $msk = $t->view_filled();
        // remove the code id for the api compare test because the code id should not be updated over the api
        $msk->code_id = null;
        $t->assert_api_json($msk);
        $msk = $t->view_protected();
        $t->assert_api($msk);
        $t->assert_api_to_dsp($msk, new view_dsp());

        $t->subheader($ts . 'with components api');
        $msk = $t->view_with_components();
        $t->assert_api($msk, 'view_with_components');
        $t->assert_api_to_dsp($msk, new view_dsp());

        $t->subheader($ts . 'im- and export');
        $t->assert_ex_and_import($t->view(), $usr_sys);
        $t->assert_ex_and_import($t->view_filled(), $usr_sys);
        $json_file = 'unit/view/car_costs.json';
        $t->assert_json_file(new view($usr), $json_file);


        $test_name = 'view create from json string';
        $json = '{"id":1,"name":"Word","description":"the default view for words","code_id":"word"}';
        $msk_dsp = new view_dsp($json);
        $dsp_text = $msk_dsp->name_tip();
        $target = '<span title="the default view for words" data-toggle="tooltip">Word</span>';
        $t->assert($test_name, $dsp_text, $target);

        // sql to load the view components
        $msk = new view($usr);
        $msk->set_id(2);

        $lib = new library();
        $db_con = new sql_db();
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $msk->load_components_sql($db_con)->sql;
        $expected_sql = $t->file('db/component/components_by_view_id.sql');
        $t->display('view->load_components_sql by view id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($msk->load_components_sql($db_con)->name);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $msk->load_components_sql($db_con)->sql;
        $expected_sql = $t->file('db/component/components_by_view_id_mysql.sql');
        $t->display('view->load_components_sql for MySQL', $lib->trim($expected_sql), $lib->trim($created_sql));


        /*
         * Display tests
         */

        $t->subheader($ts . 'display');

        /*
         * needs database connection
        $msk = new view_dsp;
        $msk->id = 1;
        $msk->code_id = null;
        $msk->name = view::TEST_NAME_ADD;
        $msk->usr = $usr;
        $wrd = new word($usr);
        $wrd->set_name(word::TEST_NAME);
        $result = $msk->display($wrd, 1);
        $target = '';
        $t->display('view->display', $target, $result);
        */

    }

}