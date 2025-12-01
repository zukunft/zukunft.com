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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_TYPES . 'api_type.php';
include_once html_paths::USER . 'user_message.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UTILS . 'test_api.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_api;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class horizontal_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $tl = new test_lib();
        $t_usr = new test_users();
        $t_map = new test_mappers($t);

        // start the test section (ts)
        $ts = 'unit horizontal ';
        $t->header($ts);
        $t->usr1 = $t_usr->user_sys_test();

        $t->subheader($ts . 'fill');
        foreach (def::MAIN_CLASSES as $class) {
            $base_obj = $t_map->class_to_base_object($class);
            $filled_obj = $t_map->class_to_filled_object($class);
            $t->assert_fill($base_obj, $filled_obj);
        }

        $t->subheader($ts . 'reset');
        foreach (def::MAIN_CLASSES as $class) {
            $test_name = 'reset ' . $lib->class_to_name($class) . ' lead to an empty api_json';
            $filled_obj = $t_map->class_to_filled_object($class);
            $filled_obj->reset();
            $api_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $t->assert_json_string($test_name, $api_json, test_api::JSON_ID_ONLY);
        }

        $t->subheader($ts . 'sql');
        foreach (def::MAIN_CLASSES as $class) {
            $test_name = 'sql creation for ' . $lib->class_to_name($class);
            $t->resource_path = $lib->class_to_resource_path($class);
            $obj = $t_map->class_to_base_object($class);
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
            $usr_msg = new user_message($t->usr1);
            $usr_msg_ui = new user_message_ui();
            $filled_obj = $t_map->class_to_filled_object($class);
            if (in_array($class, def::SANDBOX_CLASSES)) {
                $filled_obj->include();
            }
            // use the clone function instead of pure clone of the object to clone also the child objects like the group of the value
            $check_obj = $filled_obj->clone_all();
            $api_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $ui_obj = $tl->obj_to_ui_obj($filled_obj);
            $ui_obj->set_from_json($api_json, $usr_msg_ui);
            $check_obj->reset();
            $ui_json = $ui_obj->api_json();
            $api_json_ui = json_encode($t->json_remove_fields_only_to_ui(json_decode($api_json, true)));
            $check_obj->set_from_api($ui_json, $usr_msg);
            $diff = $check_obj->diff_msg($filled_obj);
            if (!$diff->is_ok()) {
                log_err($diff->all_message_text());
            } else {
                $t->assert_json_string($test_name, $ui_json, $api_json_ui);
            }
            $t->assert_true($test_name, $diff->is_ok());
        }

        $t->subheader($ts . 'im- and export');
        foreach (def::MAIN_CLASSES as $class) {
            $dto = new data_object($t->usr1);
            $usr_msg = new user_message($t->usr1);
            // TODO add test to im- and export objects with the owner and a user that differs from the owner
            $test_name = 'export ' . $lib->class_to_name($class) . ' lead not to an empty export json';
            $filled_obj = $t_map->class_to_filled_object($class);
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
            } elseif ($class == view_relation::class) {
                $dto->add_view($filled_obj->fob());
                $dto->add_view($filled_obj->tob());
            }
            $ex_json = $filled_obj->export_json(false);
            $api_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $t->assert_not($test_name, $ex_json, test_api::JSON_ID_ONLY);
            $test_name = 'cleared ' . $lib->class_to_name($class) . ' lead to an empty export json';
            $filled_obj->reset();
            $empty_json = json_encode($filled_obj->export_json(false));
            $empty_target_json = $lib->class_to_empty_json($class);
            $t->assert_json_string($test_name, $empty_json, $empty_target_json);
            $test_name = 'after import ' . $lib->class_to_name($class) . ' the export json matches the original json';
            if (in_array($class, def::CODE_ID_CLASSES)) {
                // special case and more cases are covered in the separate user unit testing
                $sys_usr = $t->user_system();
                $filled_obj->import_mapper_user($ex_json, $sys_usr, $usr_msg, $dto);
            } else {
                $filled_obj->import_mapper($ex_json, $usr_msg, $dto);
            }
            // set the remembered id again , because the db id is never included in the export
            $filled_obj->id = $id;
            $final_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $api_json_ex = json_encode($t->json_remove_fields_only_to_ui(json_decode($api_json, true)));
            $t->assert_json_string($test_name, $final_json, $api_json_ex);
        }

    }

}