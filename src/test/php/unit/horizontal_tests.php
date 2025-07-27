<?php

/*

    test/unit/horizontal_tests.php - unit testing of the functions that all main classes have
    ------------------------------

    the tests for all main objects include these tests
    - fill: if an imported object is filled correctly with the db object
    - reset: if api json of an object after reset is an empty json
    - api: if the api json can be created, dropped to the related frontend object and if the api from the frontend object matches the original api json
    - diff: if a user readable message can be created what the difference between two objects is
    - import: if an import json is mapped to this object
    - sql load by id: if prepared sql statement to load the object can be created
    - usage: if the usage / relevance of the object can be calculated

    additional tests for sandbox objects
    -



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

use cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';

use cfg\const\def;
use cfg\db\sql_creator;
use cfg\db\sql_type;
use cfg\formula\formula;
use cfg\helper\data_object;
use cfg\ref\ref;
use cfg\result\result;
use cfg\user\user;
use cfg\value\value;
use cfg\view\view;
use cfg\word\triple;
use shared\library;
use shared\types\api_type;
use test\test_api;
use test\test_cleanup;

class horizontal_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();

        // start the test section (ts)
        $ts = 'unit horizontal ';
        $t->header($ts);

        $t->subheader($ts . 'fill');
        foreach (def::MAIN_CLASSES as $class) {
            $base_obj = $t->class_to_base_object($class);
            $filled_obj = $t->class_to_filled_object($class);
            $t->assert_fill($base_obj, $filled_obj);
        }

        $t->subheader($ts . 'reset');
        foreach (def::MAIN_CLASSES as $class) {
            $test_name = 'reset ' . $lib->class_to_name($class) . ' lead to an empty api_json';
            $filled_obj = $t->class_to_filled_object($class);
            $filled_obj->reset();
            $api_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $t->assert_json_string($test_name, $api_json,  test_api::JSON_ID_ONLY);
        }

        $t->subheader($ts . 'sql');
        foreach (def::MAIN_CLASSES as $class) {
            $test_name = 'sql creation for ' . $lib->class_to_name($class);
            $t->resource_path = $lib->class_to_resource_path($class);
            $obj = $t->class_to_base_object($class);
            $obj_changed = clone $obj;
            $obj_changed->reset();
            $t->assert_sql_table_create($obj);
            $t->assert_sql_index_create($obj);
            if (!in_array($class, def::NO_FOREIGN_DB_KEY_CLASSES)) {
                $t->assert_sql_foreign_key_create($obj);
            }
            // TODO maybe move here from the single class tests
            //$t->assert_sql_insert($sc, $obj, [sql_type::LOG]);
            //$t->assert_sql_update($sc, $obj_changed, $obj, [sql_type::LOG]);
            //$t->assert_sql_delete($sc, $obj, [sql_type::LOG]);

        }

        $t->subheader($ts . 'frontend api');
        foreach (def::MAIN_CLASSES as $class) {
            $test_name = 'frontend of ' . $lib->class_to_name($class) . ' can reproduce the same backend object';
            $filled_obj = $t->class_to_filled_object($class);
            if (in_array($class, def::SANDBOX_CLASSES)) {
                $filled_obj->include();
            }
            $check_obj = clone $filled_obj;
            $api_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $ui_obj = $t->frontend_obj_from_backend_object($filled_obj);
            $ui_obj->set_from_json($api_json);
            $check_obj->reset();
            $ui_json = $ui_obj->api_json();
            $check_obj->set_from_api($ui_json);
            $diff = $check_obj->diff_msg($filled_obj);
            if (!$diff->is_ok()) {
                log_err($diff->all_message_text());
            }
            $t->assert_true($test_name, $diff->is_ok());
        }

        $t->subheader($ts . 'im- and export');
        foreach (def::MAIN_CLASSES as $class) {
            $dto = new data_object($t->usr1);
            // TODO add test to im- and export objects with the owner and a user that differs from the owner
            $test_name = 'export ' . $lib->class_to_name($class) . ' lead not to an empty export json';
            $filled_obj = $t->class_to_filled_object($class);
            // remember the db id, because the db id is never included in the export
            $id = $filled_obj->id();
            // fill up cache to avoid db access in unit tests
            if ($class == triple::class) {
                $dto->add_phrase($filled_obj->from());
                $dto->add_phrase($filled_obj->to());
            } elseif ($class == ref::class) {
                $dto->add_phrase($filled_obj->phrase());
                $dto->add_source($filled_obj->source());
            } elseif ($class == value::class) {
                $dto->add_source($filled_obj->source());
            } elseif ($class == result::class) {
                $dto->add_formula($filled_obj->frm);
            }
            $ex_json = $filled_obj->export_json(false);
            $api_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $t->assert_not($test_name, $ex_json,  test_api::JSON_ID_ONLY);
            $test_name = 'cleared ' . $lib->class_to_name($class) . ' lead to an empty export json';
            $filled_obj->reset();
            $empty_json = json_encode($filled_obj->export_json(false));
            $empty_target_json = $lib->class_to_empty_json($class);
            $t->assert_json_string($test_name, $empty_json,  $empty_target_json);
            $test_name = 'after import ' . $lib->class_to_name($class) . ' the export json matches the original json';
            if (in_array($class, def::CODE_ID_CLASSES)) {
                // special case and more cases are covered in the separate user unit testing
                $sys_usr = $t->user_system();
                $filled_obj->import_mapper_user($ex_json, $sys_usr, $dto);
            } else {
                $filled_obj->import_mapper($ex_json, $dto);
            }
            // set the remembered id again , because the db id is never included in the export
            $filled_obj->set_id($id);
            $final_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $t->assert_json_string($test_name,  $final_json, $api_json);
        }

    }

}