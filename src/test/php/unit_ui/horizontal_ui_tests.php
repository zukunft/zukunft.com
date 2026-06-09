<?php

/*

    test/unit/horizontal_ui_tests.php - testing of the user interface functions that all main classes have
    ---------------------------------

    to tests all user interface objects including these tests
    - url add: if the url can reproduce the filled backend object

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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\helper\MapObject;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\component\component_exe;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::SHARED . 'library.php';
include_once html_paths::HTML . 'button.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class horizontal_ui_tests
{
    function run(test_cleanup $t, frontend $ui): void
    {

        // init
        $lib = new library();
        $map = new MapObject();
        $t_map = new test_mappers($t);
        $usr_msg_ui = new user_message_ui();
        $usr_msg = new user_message($t->usr1);
        $msg_ui = $map->convertMsgToUi($usr_msg);
        $url_test = new test_mappers($t);
        $url_map = new url_mapper();

        // start the test section (ts)
        $ts = 'unit ui horizontal ';
        $t->header($ts);

        $t->subheader($ts . 'button');
        foreach (def::MAIN_CLASSES as $class) {
            $ui_obj = $t_map->class_to_ui_object($class);
            $test_name = 'add ' . $lib->class_to_name($class) . ' html code';
            if ($class != result::class) {
                // it should not be possible to add result via an ui button
                $t->assert_text_contains($test_name, $ui_obj->btn_add(), button::IMG_ADD_FA);
            }
            $test_name = 'edit ' . $lib->class_to_name($class) . ' html code';
            $t->assert_text_contains($test_name, $ui_obj->btn_edit(), button::IMG_EDIT_FA);
            $test_name = 'del ' . $lib->class_to_name($class) . ' html code';
            $t->assert_text_contains($test_name, $ui_obj->btn_del(), button::IMG_DEL_FA);
        }

        $t->subheader($ts . 'url');
        foreach (def::MAIN_CLASSES as $class) {
            $test_name = 'add url of ' . $lib->class_to_name($class) . ' can reproduce the same backend object';
            $url = $url_test->test_url($t_map->class_to_url_add($class, 1));
            $url_part = parse_url($url);
            parse_str($url_part["query"], $url_array);
            $url_array = $url_map->url_to_standard($url_array, $usr_msg_ui);
            $ui_obj = $t_map->class_to_ui_object($class);
            $filled_obj = $t_map->class_to_filled_object($class);
            $ui_obj->url_mapper($url_array, $usr_msg_ui);
            $api_msg = $ui_obj->api_array();
            $refilled_obj = clone $filled_obj;
            $refilled_obj->reset(true);
            $refilled_obj->api_mapper($api_msg, $usr_msg);
            // fill the id that is not set by the add url
            $refilled_obj->id = $filled_obj->id();
            // fill the exclude field that is set by the crud action
            if ($filled_obj::class != verb::class) {
                if ($filled_obj->is_excluded()) {
                    $refilled_obj->excluded = $filled_obj->excluded;
                }
            }
            // fill the code id field that should not be set via url
            if (in_array($filled_obj::class, def::CODE_ID_CLASSES)) {
                $refilled_obj->set_code_id($filled_obj->get_code_id(), $t->usr_system);
            }
            //
            $diff = $filled_obj->diff_msg($refilled_obj);
            if (!$diff->is_ok()) {
                log_err($diff->all_message_text());
            }
            $t->assert_true($test_name, $diff->is_ok());
        }

        $t->subheader($ts . 'component types');
        $html = new html_base();
        $test_page = $html->text_h1('Component display test');
        foreach ($ui->dto->typ_lst_cache->cmp_typ->lst() as $typ) {
            $test_page .= '<br><br>' . $html->dsp_text_h2($typ->name . ' (' . $typ->code_id . ')') . '<br><br><br>';
            $obj = $t_map->component_type_to_object($typ);
            if ($obj !== null) {
                $ui_obj = $t_map->class_to_ui_object($obj::class);
                $ui_obj->api_mapper($obj->api_json_array(new api_type_list([])), $msg_ui);
                $cmp = new component_exe();
                $cmp->set_type_id($typ->id());
                $cmp->code_id = $typ->code_id;
                $test_page .= $cmp->dsp_entries($ui_obj, 'component type tests', views::WORD_EDIT_ID, $ui->dto);
            } else {
                $test_page .= 'no object mapped for type ' .  $typ->name;
            }
        }
        $t->html_page_test($test_page, 'all component types', 'all_component_types', $t);
    }

}