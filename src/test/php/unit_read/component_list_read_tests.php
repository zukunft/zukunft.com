<?php

/*

    test/php/unit_read/component_list.php - TESTing of the COMPONENT LIST functions that only read from the database
    --------------------------------
  

    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the words of the GNU General Public License as
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

use api\component\component as component_api;
use cfg\component\component_list;
use shared\library;
use test\test_cleanup;

class component_list_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        $lib = new library();

        // init
        $t->name = 'component list read db->';

        $t->header('Test the component list class (classes/component_list.php)');

        // test loading component names
        $test_name = 'loading component names with pattern return the expected component';
        $pattern = substr(component_api::TN_READ, 0, -1);
        $cmp_lst = new component_list($t->usr1);
        $cmp_lst->load_names($pattern);
        $t->assert_contains($test_name, $cmp_lst->names(), component_api::TN_READ);
        $test_name = 'system component are not included in the normal component list';
        $cmp_lst = new component_list($t->usr1);
        $cmp_lst->load_names(component_api::TN_FORM_TITLE);
        $t->assert_contains_not($test_name, $cmp_lst->names(), component_api::TN_FORM_TITLE);


        $test_name = 'loading by component list by view id ';
        $cmp_lst = new component_list($t->usr1);
        $cmp_lst->load_by_view_id(1);
        $result = $cmp_lst->name();
        $target = '"' . component_api::TN_READ . '"';
        $t->assert_text_contains($test_name . '1', $result, $target);

        $test_name = 'loading the api message creation of the api index file for ';
        // TODO add this to all db read tests for all API call functions
        $result = json_decode($cmp_lst->api_json(), true);
        $class_for_file = $t->class_without_namespace(component_list::class);
        $target = json_decode($t->api_json_expected($class_for_file), true);
        $t->assert_json($test_name . $cmp_lst->dsp_id(), $result, $target);

        $test_name = 'loading by component list by pattern ';
        $cmp_lst = new component_list($t->usr1);
        $pattern = substr(component_api::TN_READ, 0, -1);
        $cmp_lst->load_names($pattern);
        $t->assert_contains($test_name, $cmp_lst->names(), component_api::TN_READ);

        // test load by component list by ids
        /* TODO activate
        $test_name = 'load components by ids';
        $wrd_lst = new component_list($t->usr1);
        $wrd_lst->load_by_ids(array(1,3));
        $target = '"' . component_api::TN_READ . '","' . component_api::TN_READ . '"'; // order adjusted based on the number of usage
        $t->assert($test_name, $wrd_lst->name(), $target);
        $test_name = 'load components by names';
        $wrd_lst = new component_list($t->usr1);
        $wrd_lst->load_by_names(array(component_api::TN_READ,component_api::TN_READ_RATIO));
        $t->assert_contains($test_name, $wrd_lst->ids(), array(1,3));
        $test_name = 'load components staring with P';
        $wrd_lst = new component_list($t->usr1);
        $wrd_lst->load_like('W');
        $t->assert_contains($test_name, $wrd_lst->names(), component_api::TN_READ);
        */

    }

}

