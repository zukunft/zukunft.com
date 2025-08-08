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

use cfg\component\component;
use cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::API_OBJECT . 'controller.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::MODEL_SYSTEM . 'system_time_type.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'api.php';


use cfg\const\def;
use cfg\formula\formula;
use cfg\group\group;
use cfg\helper\db_object;
use cfg\ref\source;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_multi;
use cfg\sandbox\sandbox_value;
use cfg\user\user;
use cfg\verb\verb;
use cfg\view\view;
use cfg\word\word;
use const\paths as test_paths;
use cfg\helper\data_object;
use cfg\ref\ref;
use cfg\result\result;
use cfg\value\value;
use cfg\word\triple;
use html\types\type_lists;
use html\user\user as user_dsp;
use html\frontend;
use shared\api;
use shared\const\views;
use shared\const\views as view_shared;
use shared\enum\change_actions;
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
            $t->assert_json_string($test_name, $api_json, test_api::JSON_ID_ONLY);
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
                $filled_obj->import_mapper_user($ex_json, $sys_usr, $dto);
            } else {
                $filled_obj->import_mapper($ex_json, $dto);
            }
            // set the remembered id again , because the db id is never included in the export
            $filled_obj->set_id($id);
            $final_json = $filled_obj->api_json([api_type::TEST_MODE]);
            $t->assert_json_string($test_name, $final_json, $api_json);
        }

        $t->subheader($ts . 'system views');
        $ui = new frontend('unit test');
        $ui->load_dummy_cache_from_test_resources();
        for ($id = views::MIN_TEST_ID; $id <= views::MAX_TEST_ID; $id++) {
            $dbo = $this->view_id_to_dbo($id, $t->usr1);
            $action = $this->view_id_to_url_action($id);
            $url = $t->class_to_filled_url($dbo::class, $id, $action);
            $url_part = parse_url($url);
            parse_str($url_part["query"], $url_array);
            $usr_dsp = new user_dsp();
            $usr_dsp->set_from_json($t->usr1->api_json());
            $html = $ui->url_to_html($url_array, $usr_dsp);
            $test_name = $action . ' ' . $lib->class_to_name($dbo::class) . ' view';
            // create the filename of the expected result
            $dbo_name = $id . '_';
            if ($dbo::class == db_object::class) {
                $folder = 'start_page' . DIRECTORY_SEPARATOR;
                $dbo_name .= 'start_page';
                $test_name = 'start_page view';
            } else {
                $class = $lib->class_to_name($dbo::class);
                $folder = $class . DIRECTORY_SEPARATOR;
                $dbo_name .= $class;
                $dbo_id = $url_array[api::URL_VAR_ID] ?? 0; // the database id of the prime object to display
                if ($action != change_actions::SHOW) {
                    $dbo_name .= '_' . $action;
                }
                if ($dbo_id != 0) {
                    $dbo_name .= '_' . $lib->str_to_file($dbo_id);
                }
            }
            $filename = test_paths::VIEWS_BY_ID . $folder . $dbo_name;
            $t->assert_html_page($test_name, $html, $filename);
        }

    }


    private function view_id_to_dbo(int $view_id, user $usr): sandbox|sandbox_multi|user|db_object
    {
        // select the backend object to display
        if (in_array($view_id, view_shared::WORD_MASKS_IDS)) {
            $dbo = new word($usr);
        } elseif (in_array($view_id, view_shared::VERB_MASKS_IDS)) {
            $dbo = new verb();
        } elseif (in_array($view_id, view_shared::TRIPLE_MASKS_IDS)) {
            $dbo = new triple($usr);
        } elseif (in_array($view_id, view_shared::SOURCE_MASKS_IDS)) {
            $dbo = new source($usr);
        } elseif (in_array($view_id, view_shared::REF_MASKS_IDS)) {
            $dbo = new ref($usr);
        } elseif (in_array($view_id, view_shared::VALUE_MASKS_IDS)) {
            $dbo = new value($usr);
        } elseif (in_array($view_id, view_shared::GROUP_MASKS_IDS)) {
            $dbo = new group($usr);
        } elseif (in_array($view_id, view_shared::FORMULA_MASKS_IDS)) {
            $dbo = new formula($usr);
        } elseif (in_array($view_id, view_shared::RESULT_MASKS_IDS)) {
            $dbo = new result($usr);
        } elseif (in_array($view_id, view_shared::VIEW_MASKS_IDS)) {
            $dbo = new view($usr);
        } elseif (in_array($view_id, view_shared::COMPONENT_MASKS_IDS)) {
            $dbo = new component($usr);
        } else {
            $dbo = new db_object();
        }
        return $dbo;
    }


    private function view_id_to_url_action(int $view_id): string
    {
        // select the backend object to display
        if (in_array($view_id, view_shared::SHOW_MASKS_IDS)) {
            $action = change_actions::SHOW;
        } elseif (in_array($view_id, view_shared::ADD_MASKS_IDS)) {
            $action = change_actions::ADD;
        } elseif (in_array($view_id, view_shared::EDIT_MASKS_IDS)) {
            $action = change_actions::UPDATE;
        } elseif (in_array($view_id, view_shared::DEL_MASKS_IDS)) {
            $action = change_actions::DELETE;
        } else {
            $action = 'unknown';
        }
        return $action;
    }

}