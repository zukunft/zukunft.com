<?php

/*

    test/unit/component.php - unit testing of the view component functions
    ----------------------------
  

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

include_once MODEL_COMPONENT_PATH . 'component.php';

use api\view\view as view_api;
use cfg\component\component;
use cfg\db\sql_db;
use html\component\component as component_dsp;

class component_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'component->';
        $t->resource_path = 'db/component/';
        $json_file = 'unit/view/component_import.json';
        $usr->set_id(1);

        $t->header('Unit tests of the view component class (src/main/php/model/view/component.php)');


        $t->subheader('SQL setup statements');
        $cmp = $t->dummy_component();
        $t->assert_sql_table_create($cmp);
        $t->assert_sql_index_create($cmp);
        $t->assert_sql_foreign_key_create($cmp);


        $t->subheader('SQL user sandbox statement tests');

        $cmp = new component($usr);
        $t->assert_sql_by_id($db_con, $cmp);
        $t->assert_sql_by_name($db_con, $cmp);


        $t->subheader('SQL statement tests');

        // sql to load the view components by id
        $cmp = new component($usr);
        $cmp->set_id(2);
        //$t->assert_sql_all($db_con, $cmp);
        $t->assert_sql_standard($db_con, $cmp);
        $t->assert_sql_user_changes($db_con, $cmp);

        // sql to load the view components by name
        $cmp = new component($usr);
        $cmp->set_name(view_api::TN_ADD);
        //$t->assert_sql_all($db_con, $cmp);
        $t->assert_sql_standard($db_con, $cmp);


        $t->subheader('Convert tests');

        // casting API
        $cmp = $t->dummy_component();
        $t->assert_api($cmp);
        $t->assert_api_to_dsp($cmp, new component_dsp());


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new component($usr), $json_file);

    }

}