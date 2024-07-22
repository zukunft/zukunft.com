<?php

/*

    test/php/unit_read/view.php - database unit testing of the view functions
    ---------------------------


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

namespace unit_read;

include_once SHARED_TYPES_PATH . 'view_type.php';
include_once SHARED_TYPES_PATH . 'component_type.php';

use shared\types\view_type as view_type_shared;
use shared\types\component_type as comp_type_shared;
use api\component\component as component_api;
use api\view\view as view_api;
use cfg\component\component;
use cfg\component\component_type;
use cfg\component\component_type_list;
use cfg\view;
use cfg\view_sys_list;
use cfg\view_type;
use cfg\view_type_list;
use controller\controller;
use test\test_cleanup;

class view_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $view_types;
        global $system_views;
        global $component_types;

        // init
        $t->header('Unit database tests of the view class (src/main/php/model/value/view.php)');
        $t->name = 'view read db->';


        $t->subheader('View db read tests');

        $test_name = 'load view ' . view_api::TN_READ . ' by name and id';
        $dsp = new view($t->usr1);
        $dsp->load_by_name(view_api::TN_READ, view::class);
        $dsp_by_id = new view($t->usr1);
        $dsp_by_id->load_by_id($dsp->id(), view::class);
        $t->assert($test_name, $dsp_by_id->name(), view_api::TN_READ);

        $test_name = 'load the components of view ' . view_api::TN_READ . ' contains ' . component_api::TN_READ;
        $dsp->load_components();
        $t->assert_contains($test_name, $dsp->component_link_list()->names(), component_api::TN_READ);


        $t->subheader('View types tests');

        // load the view types
        $lst = new view_type_list();
        $result = $lst->load($db_con);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $view_types->id(view_type_shared::DEFAULT);
        $t->assert('check type' . view_type_shared::DEFAULT, $result, 1);


        $t->subheader('View API object creation tests');

        $cmp = $t->load_word(view_api::TN_READ, $t->usr1);
        $t->assert_api_obj($cmp);


        $t->subheader('System view tests');
        $t->name = 'view list read db->';

        // load the views used by the system e.g. change word
        $lst = new view_sys_list($t->usr1);
        $lst->usr = $t->usr1;
        $result = $lst->load($db_con);
        $t->assert('load', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $system_views->id(controller::DSP_WORD);
        $target = 0;
        if ($result > 0) {
            $target = $result; // just check if the id is found
        }
        $t->assert('check' . controller::DSP_WORD, $result, $target);

        // check all system views
        // TODO activate Prio 2
        //$t->assert_view(controller::DSP_COMPONENT_ADD, $t->usr1);
        //$t->assert_view(controller::DSP_COMPONENT_EDIT, $t->usr1);
        //$t->assert_view(controller::DSP_COMPONENT_DEL, $t->usr1);



        $t->subheader('View component db read tests');

        $test_name = 'load view component ' . component_api::TN_READ . ' by name and id';
        $cmp = new component($t->usr1);
        $cmp->load_by_name(component_api::TN_READ, component::class);
        $cmp_by_id = new component($t->usr1);
        $cmp_by_id->load_by_id($cmp->id(), component::class);
        $t->assert($test_name, $cmp_by_id->name(), component_api::TN_READ);
        $t->assert($test_name, $cmp_by_id->description, component_api::TD_READ);


        $t->subheader('View component types tests');
        $t->name = 'view component read db->';

        // load the view component types
        $cmp_lst = new component_type_list();
        $result = $cmp_lst->load($db_con);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $component_types->id(comp_type_shared::TEXT);
        $t->assert('check type' . comp_type_shared::TEXT, $result, 3);
    }

}

