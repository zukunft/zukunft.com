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

class view_unit_tests
{
    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'view->';
        $t->resource_path = 'db/view/';
        $json_file = 'unit/view/car_costs.json';
        $usr->id = 1;

        $t->header('Unit tests of the view class (src/main/php/model/view/view.php)');


        $t->subheader('SQL user sandbox statement tests');

        $dsp = new view($usr);
        $t->assert_load_sql_id($db_con, $dsp);
        $t->assert_load_sql_name($db_con, $dsp);


        $t->subheader('SQL statement tests');

        // sql to load the view by id
        $dsp = new view($usr);
        $dsp->id = 2;
        //$t->assert_load_sql($db_con, $dsp);
        $t->assert_load_standard_sql($db_con, $dsp);
        $t->assert_user_config_sql($db_con, $dsp);

        // sql to load the view by name
        $dsp = new view($usr);
        $dsp->set_name(view::TN_ADD);
        //$t->assert_load_sql($db_con, $dsp);
        $t->assert_load_standard_sql($db_con, $dsp);

        // sql to load the view by code id
        $dsp = new view($usr);
        $dsp->code_id = view::WORD;
        $t->assert_load_sql($db_con, $dsp);

        // sql to load the view components
        $dsp = new view($usr);
        $dsp->id = 2;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $dsp->load_components_sql($db_con)->sql;
        $expected_sql = $t->file('db/view/view_components_by_view_id.sql');
        $t->dsp('view->load_components_sql by view id', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($dsp->load_components_sql($db_con)->name);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $dsp->load_components_sql($db_con)->sql;
        $expected_sql = $t->file('db/view/view_components_by_view_id_mysql.sql');
        $t->dsp('view->load_components_sql for MySQL', $t->trim($expected_sql), $t->trim($created_sql));

        $t->subheader('Convert tests');

        // casting API
        $dsp = new view($usr);
        $dsp->set(1, view::TN_READ);
        $t->assert_api($dsp);

        /*
         * im- and export tests
         */

        $t->subheader('Im- and Export tests');

        $t->assert_json(new view_dsp_old($usr), $json_file);

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
        $t->dsp('view->display', $target, $result);
        */

    }

}