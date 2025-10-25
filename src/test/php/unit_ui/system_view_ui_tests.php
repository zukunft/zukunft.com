<?php

/*

    test/unit_ui/system_view_ui_tests.php - test if the system view still look the same without using the api
    -------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once html_paths::HELPER . 'data_object.php';
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
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object;
use Zukunft\ZukunftCom\main\php\shared\const\views as view_shared;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class system_view_ui_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $tl = new test_lib();
        $t_usr = new test_users();
        $t_map = new test_mappers($t);

        // start the test section (ts)
        $ts = 'unit ui system views ';
        $t->header($ts);
        $t->usr1 = $t_usr->user_sys_test();

        // test the system views by id
        // similar to horizontal_ui_tests which tests the curl view for the main objects
        $t->subheader($ts . 'by id');
        $ui = new frontend('unit test');
        $dto = $tl->ui_test_cache($t->usr1, $t);
        $ui->set_cache($dto);
        // TODO Prio 1 deprecate
        $ui->load_dummy_cache_from_test_resources($t->usr1);
        for ($id = views::MIN_TEST_ID; $id <= views::MAX_TEST_ID; $id++) {
            $dbo = $this->view_id_to_dbo($id, $t->usr1);
            $action = $this->view_id_to_url_action($id);
            $url = $t_map->class_to_filled_url($dbo::class, $id, $action);
            $url_part = parse_url($url);
            parse_str($url_part["query"], $url_array);
            $usr_dsp = $tl->cast_user($t->usr1);
            // TODO Prio 0 remove temp
            //if ($id == 9) {
            //    log_info('triple edit');
            //}
            $html = $ui->url_to_html($url_array, $usr_dsp, $ui->dto);
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
                $dbo_id = $url_array[url_var::ID] ?? 0; // the database id of the prime object to display
                if ($action != change_actions::SHOW) {
                    $dbo_name .= '_' . $action;
                }
                if ($dbo_id != 0) {
                    $dbo_name .= '_' . $lib->str_to_file($dbo_id);
                }
            }
            $file_path = test_paths::VIEWS_BY_ID . $folder . $dbo_name;
            $t->assert_html_page($test_name, $html, $file_path);
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