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

use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::SHARED . 'library.php';
include_once html_paths::HTML . 'button.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\component\component_exe;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\helper\MapObject;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
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
            // fill the unidirectional fields for test
            // TODO Prio 1 remove exception
            if ($filled_obj::class != user::class
                and $filled_obj::class != ref::class
                and $filled_obj::class != group::class
                and $filled_obj::class != value::class
                and $filled_obj::class != formula_link::class
                and $filled_obj::class != result::class
                and $filled_obj::class != view_relation::class
                and $filled_obj::class != component_link::class
                and $filled_obj::class != term_view::class) {
                $refilled_obj->usage = $filled_obj->usage;
            }
            // TODO Prio 1 remove exception
            if ($filled_obj::class == triple::class) {
                $refilled_obj->name_given = $filled_obj->name_given;
            }
            // TODO Prio 1 remove exception
            if ($filled_obj::class == component::class and $refilled_obj::class == component::class) {
                $refilled_obj->ui_msg_code_id = $filled_obj->ui_msg_code_id;
                $refilled_obj->ui_msg_code_id_vars = $filled_obj->ui_msg_code_id_vars;
                $refilled_obj->ui_msg_code_id_exception = $filled_obj->ui_msg_code_id_exception;
            }
            // TODO Prio 1 remove exception
            // fill the user fields that no user form transports, because e.g. the ip address
            // comes from the request and the login times from the login process
            if ($filled_obj::class == user::class and $refilled_obj::class == user::class) {
                $refilled_obj->ip_addr = $filled_obj->ip_addr;
                $refilled_obj->last_login = $filled_obj->last_login;
                $refilled_obj->last_logoff = $filled_obj->last_logoff;
                $refilled_obj->right_level = $filled_obj->right_level;
                // the type and the status are set by the verification and admin processes, not by
                // a form, and api_mapper keeps a missing field null instead of fabricating the
                // default (see docs/llm/constants.md), so the refilled user cannot know them;
                // only a null is filled so the diff still catches a wrongly mapped real value
                if ($refilled_obj->type_id === null) {
                    $refilled_obj->type_id = $filled_obj->type_id;
                }
                if ($refilled_obj->status_id === null) {
                    $refilled_obj->status_id = $filled_obj->status_id;
                }
                // the sandbox usage checkbox is only part of the admin user edit mask
                $refilled_obj->uses_sandbox = $filled_obj->uses_sandbox;
                $refilled_obj->created = $filled_obj->created;
                $refilled_obj->description = $filled_obj->description;
                $refilled_obj->trm = $filled_obj->trm;
                $refilled_obj->msk = $filled_obj->msk;
                $refilled_obj->src = $filled_obj->src;
            }
            // check the diff
            $diff = $filled_obj->diff_msg($refilled_obj);
            if (!$diff->is_ok()) {
                log_err($diff->all_message_text());
            }
            $t->assert_true($test_name, $diff->is_ok());
        }

        $t->subheader($ts . 'component types');
        $html = new html_base();
        $test_page = $html->text_h1('Component display test');
        // this catalog page stacks one form part per component type; count the parts up so
        // each part's field names/ids stay unique (production passes no counter -> name="k")
        $test_form_unique_id = 1;
        foreach ($ui->dto->typ_lst_cache->cmp_typ->lst() as $typ) {
            $test_page .= '<br><br>' . $html->dsp_text_h2($typ->name . ' (' . $typ->code_id . ')') . '<br><br><br>';
            $obj = $t_map->component_type_to_object($typ);
            if ($obj !== null) {
                $ui_obj = $t_map->class_to_ui_object($obj::class);
                $ui_obj->api_mapper($obj->api_json_array(new api_type_list([])), $msg_ui);
                $cmp = new component_exe();
                $cmp->set_type_id($typ->id());
                $cmp->code_id = $typ->code_id;
                // a valid, unique form name per part (no spaces) so the field 'form=' and the
                // form id stay valid and unique on this multi-part catalog page
                $form_name = 'component_type_test_' . $test_form_unique_id;
                // render in test mode so that no component triggers a backend call
                // TODO Prio 2 review and move the calls to the backend 'outside'
                $part = $cmp->dsp_entries($ui_obj, $form_name, views::WORD_EDIT_ID, $ui->dto,
                    null, '', '', true, [], $test_form_unique_id);
                // wrap a field part that references its form by id so the reference resolves
                if (str_contains($part, ' form="') and !str_contains($part, '<form')) {
                    $part = $html->form_start($form_name) . $part . $html->form_end();
                }
                // some component types render only layout scaffolding (a lone <div>/<form> open
                // or close that a sibling type balances on a real page); standalone here they
                // would leave a tag unclosed, so balance them to keep the catalog valid html
                $part = $this->balance_tags($part);
                $test_page .= $part;
                $test_form_unique_id++;
            } else {
                $test_page .= 'no object mapped for type ' .  $typ->name;
            }
        }
        $t->html_page_test($test_page, 'all component types', 'all_component_types', $t);
    }

    /**
     * balance the div and form tags of one catalog part so it is valid standalone html;
     * a layout-scaffolding component type renders only a lone open or close tag that a
     * sibling type balances on a real page, but in this catalog each part stands on its own
     * @param string $html the rendered catalog part
     * @return string the part with any unclosed div/form closed and any lone close opened
     */
    private function balance_tags(string $html): string
    {
        foreach (['div', 'form'] as $tag) {
            $open = substr_count($html, '<' . $tag);
            $close = substr_count($html, '</' . $tag . '>');
            if ($open > $close) {
                $html .= str_repeat('</' . $tag . '>', $open - $close);
            } elseif ($close > $open) {
                $html = str_repeat('<' . $tag . '>', $close - $open) . $html;
            }
        }
        return $html;
    }

}