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

namespace unit_ui;

use cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';

use cfg\const\def;
use cfg\verb\verb;
use shared\library;
use test\test_cleanup;

class horizontal_ui_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();

        // start the test section (ts)
        $ts = 'unit ui horizontal ';
        $t->header($ts);

        $t->subheader($ts . 'url');
        foreach (def::MAIN_CLASSES as $class) {
            $test_name = 'add url of ' . $lib->class_to_name($class) . ' can reproduce the same backend object';
            $url = $t->class_to_url_add($class);
            $url_part = parse_url($url);
            parse_str($url_part["query"], $url_array);
            $ui_obj = $t->class_to_ui_object($class);
            $filled_obj = $t->class_to_filled_object($class);
            $ui_obj->url_mapper($url_array);
            $api_msg = $ui_obj->api_array();
            $refilled_obj = clone $filled_obj;
            $refilled_obj->reset();
            $refilled_obj->api_mapper($api_msg);
            // fill the id that is not set by the add url
            $refilled_obj->set_id($filled_obj->id());
            // fill the exclude field that is set by the curl action
            if ($filled_obj::class != verb::class) {
                if ($filled_obj->is_excluded()) {
                    $refilled_obj->set_excluded($filled_obj->is_excluded());
                }
            }
            // fill the code id field that should not be set via url
            if (in_array($filled_obj::class, def::CODE_ID_CLASSES)) {
                $refilled_obj->set_code_id($filled_obj->code_id(), $t->usr_system);
            }
            //
            $diff = $filled_obj->diff_msg($refilled_obj);
            if (!$diff->is_ok()) {
                log_err($diff->all_message_text());
            }
            $t->assert_true($test_name, $diff->is_ok());
        }

    }

}