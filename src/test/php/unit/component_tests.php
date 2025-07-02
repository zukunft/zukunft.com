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

namespace unit;

include_once MODEL_COMPONENT_PATH . 'component.php';

use cfg\component\component;
use cfg\component\component_type;
use cfg\db\sql_creator;
use cfg\db\sql_type;
use html\component\component_exe as component_dsp;
use shared\const\components;
use shared\const\views;
use test\test_cleanup;

class component_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $sc = new sql_creator();
        $t->name = 'component->';
        $t->resource_path = 'db/component/';

        // start the test section (ts)
        $ts = 'unit component ';
        $t->header($ts);

        $t->subheader($ts . 'component sql setup');
        $cmp_typ = new component_type('');
        $t->assert_sql_table_create($cmp_typ);
        $t->assert_sql_index_create($cmp_typ);
        $cmp = $t->component();
        $t->assert_sql_table_create($cmp);
        $t->assert_sql_index_create($cmp);
        $t->assert_sql_foreign_key_create($cmp);

        $t->subheader($ts . 'component sql read');
        $cmp = new component($usr);
        $t->assert_sql_by_id($sc, $cmp);
        $t->assert_sql_by_name($sc, $cmp);

        $t->subheader($ts . 'component sql read standard and user changes by id');
        $cmp = new component($usr);
        $cmp->set_id(2);
        //$t->assert_sql_all($db_con, $cmp);
        $t->assert_sql_standard($sc, $cmp);
        $t->assert_sql_user_changes($sc, $cmp);

        $t->subheader($ts . 'component sql read standard by name');
        $cmp = new component($usr);
        $cmp->set_name(views::START_NAME);
        //$t->assert_sql_all($db_con, $cmp);
        $t->assert_sql_standard($sc, $cmp);

        $t->subheader($ts . 'component sql write insert');
        $cmp = $t->component();
        $t->assert_sql_insert($sc, $cmp);
        $t->assert_sql_insert($sc, $cmp, [sql_type::USER]);
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG, sql_type::USER]);
        $cmp = $t->component_word_add_title(); // a component with a code_id as it might be imported
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG]);
        $cmp = $t->component_filled();
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG]);

        $t->subheader($ts . 'component sql write update');
        $cmp = $t->component();
        $cmp_renamed = $cmp->cloned(components::TEST_RENAMED_NAME);
        $t->assert_sql_update($sc, $cmp_renamed, $cmp);
        $t->assert_sql_update($sc, $cmp_renamed, $cmp, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'component sql delete');
        $t->assert_sql_delete($sc, $cmp);
        $t->assert_sql_delete($sc, $cmp, [sql_type::LOG]);

        $t->subheader($ts . 'component base object handling');
        $cmp = $t->component_filled();
        $t->assert_reset($cmp);

        $t->subheader($ts . 'component api');
        $cmp = $t->component_filled();
        $t->assert_api_json($cmp);
        $cmp = $t->component();
        $t->assert_api($cmp);

        $t->subheader($ts . 'component frontend');
        $t->assert_api_to_dsp($cmp, new component_dsp());

        $t->subheader($ts . 'component im- and export');
        $t->assert_ex_and_import($t->component(), $usr_sys);
        $t->assert_ex_and_import($t->component_filled(), $usr_sys);
        $json_file = 'unit/view/component_import.json';
        $t->assert_json_file(new component($usr), $json_file);

    }

}