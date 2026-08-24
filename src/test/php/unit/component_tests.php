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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_USER . 'user.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class component_tests
{
    function run(test_cleanup $t): void
    {


        // init
        $sc = new sql_creator();
        $t_cmp = new test_components($t);
        $t->name = 'component->';
        $t->resource_path = 'db/component/';

        // start the test section (ts)
        $ts = 'unit component ';
        $t->header($ts);

        $t->subheader($ts . 'component sql setup');
        $cmp_typ = new component_type('');
        $t->assert_sql_table_create($cmp_typ);
        $t->assert_sql_index_create($cmp_typ);
        $cmp = $t_cmp->component();
        $t->assert_sql_table_create($cmp);
        $t->assert_sql_index_create($cmp);
        $t->assert_sql_foreign_key_create($cmp);

        $t->subheader($ts . 'component sql read');
        $cmp = new component($t->usr1);
        $t->assert_sql_by_id($sc, $cmp);
        $t->assert_sql_by_name($sc, $cmp);

        $t->subheader($ts . 'component sql read standard and user changes by id');
        $cmp = new component($t->usr1);
        $cmp->id = 2;
        //$t->assert_sql_all($db_con, $cmp);
        $t->assert_sql_standard($sc, $cmp);
        $t->assert_sql_user_changes($sc, $cmp);
        // the same two queries for many objects at once, which the user page uses to read the
        // standard values and the other users of all changed objects of one type with one query
        $t->assert_sql_standard_by_ids($sc, $cmp);
        $t->assert_sql_changing_users_by_ids($sc, $cmp);

        $t->subheader($ts . 'component sql read standard by name');
        $cmp = new component($t->usr1);
        $cmp->set_name(views::START_NAME);
        //$t->assert_sql_all($db_con, $cmp);
        $t->assert_sql_standard_by_name($sc, $cmp);

        $t->subheader($ts . 'component sql write insert');
        $cmp = $t_cmp->component();
        $t->assert_sql_insert($sc, $cmp);
        $t->assert_sql_insert($sc, $cmp, [sql_type::USER]);
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG, sql_type::USER]);
        $cmp = $t_cmp->component_word_add_title(); // a component with a code_id as it might be imported
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG]);
        $cmp = $t_cmp->component_filled_all();
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG]);
        $cmp = $t_cmp->component_incomplete();
        $t->assert_sql_insert_fail($sc, $cmp, [sql_type::LOG]);

        $t->subheader($ts . 'component sql write update');
        $cmp = $t_cmp->component();
        $cmp_renamed = $cmp->cloned(components::TEST_RENAMED_NAME);
        $t->assert_sql_update($sc, $cmp_renamed, $cmp);
        $t->assert_sql_update($sc, $cmp_renamed, $cmp, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'component sql delete');
        $t->assert_sql_delete($sc, $cmp);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $cmp, [sql_type::LOG]);

        $t->subheader($ts . 'component base object handling');
        $cmp = $t_cmp->component_filled();
        $t->assert_reset($cmp);

        $t->subheader($ts . 'component api');
        $cmp = $t_cmp->component_filled();
        $t->assert_api_json($cmp);
        $cmp = $t_cmp->component();
        $t->assert_api($cmp);

        $t->subheader($ts . 'component frontend');
        $t->assert_api_to_ui($cmp, new component_ui());

        $t->subheader($ts . 'component im- and export');
        $t->assert_ex_and_import($t_cmp->component(), $t->usr_system);
        $t->assert_ex_and_import($t_cmp->component_filled(), $t->usr_system);
        $json_file = 'unit/view/component_import.json';
        $t->assert_json_file(new component($t->usr1), $json_file);

        // the layout phrases (row, column and sub column) of a component import are resolved by
        // their name via the import cache, so the imported component carries the phrase ids
        $test_name = 'the row phrase of a component import is resolved via the import cache';
        $msg_imp = new user_message($t->usr_system); // a buffer of this negative import test block, checked but not merged
        $t_phr = new test_phrases($t);
        $dto = new data_object($t->usr1);
        $dto->add_phrase($t_phr->year(), $msg_imp);
        $cmp_imp = new component($t->usr1);
        $cmp_imp->import_mapper([json_fields::ROW => words::YEAR_CAP], $msg_imp, $dto);
        $t->assert($test_name, $cmp_imp->row_phrase?->id(), $t_phr->year()->id());
        // a phrase that is not in the import cache is kept by name only ("not ready yet" is a
        // normal intermediate state of an import, see docs/llm/coding.md), never loaded from the db
        $test_name = 'a row phrase that is not in the import cache keeps the name without an id';
        $cmp_imp = new component($t->usr1);
        $cmp_imp->import_mapper([json_fields::ROW => words::YEAR_CAP], $msg_imp, new data_object($t->usr1));
        $t->assert($test_name, $cmp_imp->row_phrase?->name(), words::YEAR_CAP);
        $t->assert($test_name . ' and a zero id', $cmp_imp->row_phrase?->id(), 0);

        $t->subheader($ts . 'component no update import');

        // a no update import compares the import object filled up with the database values with
        // the database object filled up with the import values, so that only a field that both
        // have set to a different value is reported as an overwrite
        // (see sandbox_list_named::update)
        $cmp_row = $t_cmp->component();
        $cmp_row->set_row_phrase($t_phr->year());

        // a field that only the import has set is filled up, which is what a no update import
        // does e.g. for the row phrase that companies.json adds to a component of company.json
        $test_name = 'a field that only the import has set is no overwrite';
        $cmp_db = $t_cmp->component();
        $t->assert_true($test_name, $this->no_upd_diff($cmp_db, $cmp_row, $t->usr1)->is_ok());

        // a field that both have set to another value would overwrite the database value
        $test_name = 'a field that both have set to another value is an overwrite';
        $cmp_db = $t_cmp->component();
        $cmp_db->set_row_phrase($t_phr->canton());
        $t->assert_false($test_name, $this->no_upd_diff($cmp_db, $cmp_row, $t->usr1)->is_ok());

    }

    /**
     * the overwrite check of a no update import as sandbox_list_named::update does it:
     * both objects are filled up from the other one, so that they differ only where both
     * have set the same field to a different value
     *
     * @param component $cmp_db the component as it is in the database
     * @param component $cmp_imp the component as the import file defines it
     * @param user $usr_req the user who has requested the import
     * @return user_message the overwrites that the no update import must not do
     */
    private function no_upd_diff(component $cmp_db, component $cmp_imp, user $usr_req): user_message
    {
        $imp_filled = $cmp_imp->clone_all();
        $imp_filled->fill($cmp_db, $usr_req);
        $db_filled = $cmp_db->clone_all();
        $db_filled->fill($cmp_imp, $usr_req);
        return $db_filled->diff_msg($imp_filled, true);
    }

}