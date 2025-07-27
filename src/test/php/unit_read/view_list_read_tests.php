<?php

/*

    test/php/unit_read/view_list.php - TESTing of the VIEW LIST functions that only read from the database
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

use cfg\view\view_list;
use shared\library;
use shared\const\views;
use test\test_cleanup;

class view_list_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        $lib = new library();

        // init
        $t->name = 'view list read db->';

        $t->header('view list database read unit tests');

        // test loading view names
        $test_name = 'loading view names with pattern return the expected view';
        $pattern = substr(views::TEST_ALL_NAME, 0, -1);
        $dsp_lst = new view_list($t->usr1);
        $dsp_lst->load_names($pattern);
        $t->assert_contains($test_name, $dsp_lst->names(), views::TEST_ALL_NAME);
        // TODO do not exclude all system views
        //      e.g. allow the user to select the system default view Word aka TN_READ
        //      but do not allow to select system forms aka TN_FORM
        $test_name = 'system view are not included in the normal view list';
        $dsp_lst = new view_list($t->usr1);
        $dsp_lst->load_names(views::TEST_FORM_NAME);
        $t->assert_contains_not($test_name, $dsp_lst->names(), views::TEST_FORM_NAME);


        $test_name = 'loading by view list by component id ';
        $msk_lst = new view_list($t->usr1);
        $msk_lst->load_by_component_id(1);
        $result = $msk_lst->names();
        $target = views::START_NAME;
        $t->assert_contains($test_name . '1', $result, $target);

        $test_name = 'loading the api message creation of the api index file for ';
        // TODO add this to all db read tests for all API call functions
        $result = json_decode($msk_lst->api_json(), true);
        $class_for_file = $t->class_without_namespace(view_list::class);
        $target = json_decode($t->api_json_expected($class_for_file), true);
        // TODO prio 1 use jso_contains and activate
        //$t->assert($test_name . $msk_lst->dsp_id(), $lib->json_is_similar($target, $result), true);

        $test_name = 'loading by component list by pattern ';
        $msk_lst = new view_list($t->usr1);
        $pattern = substr(views::START_NAME, 0, -1);
        $msk_lst->load_names($pattern);
        $t->assert_contains($test_name, $msk_lst->names(), views::START_NAME);

        // test load by view list by ids
        /* TODO activate
        $test_name = 'load views by ids';
        $wrd_lst = new view_list($t->usr1);
        $wrd_lst->load_by_ids(array(1,3));
        $target = '"' . views::TN_READ . '","' . views::TN_READ . '"'; // order adjusted based on the number of usage
        $t->assert($test_name, $wrd_lst->name(), $target);
        $test_name = 'load views by names';
        $wrd_lst = new view_list($t->usr1);
        $wrd_lst->load_by_names(array(views::TN_READ,views::TN_READ_RATIO));
        $t->assert_contains($test_name, $wrd_lst->ids(), array(1,3));
        $test_name = 'load views staring with P';
        $wrd_lst = new view_list($t->usr1);
        $wrd_lst->load_like('W');
        $t->assert_contains($test_name, $wrd_lst->names(), views::TN_READ);
        */

    }

}

