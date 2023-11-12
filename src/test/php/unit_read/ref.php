<?php

/*

    test/php/unit_read/ref.php - database unit testing of reference types
    --------------------------


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

namespace test;

use api\system\source_api;
use cfg\log\library;
use cfg\log\phrase_type;
use cfg\log\ref_type_list;
use cfg\log\source_list;
use cfg\log\source_type;
use cfg\log\source_type_list;
use cfg\log\ref;

class ref_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $db_con;
        global $phrase_types;
        global $source_types;

        // init
        $lib = new library();
        $t->header('Unit database tests of the ref class (src/main/php/model/ref/ref.php)');
        $t->name = 'ref read db->';

        $t->subheader('Reference types tests');

        // load the ref types
        $lst = new ref_type_list();
        $result = $lst->load($db_con);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        // TODO check
        $result = $phrase_types->id(phrase_type::NORMAL);
        $t->assert('check ' . phrase_type::NORMAL, $result, 1);

        $t->subheader('API unit db tests');

        $ref = new ref($t->usr1);
        $ref->load_by_id(4);
        $t->assert_api($ref);


        $t->subheader('Source types tests');
        $t->name = 'source read db->';

        // load the source types
        $lst = new source_type_list();
        $result = $lst->load($db_con);
        $t->assert('load_source_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $source_types->id(source_type::XBRL);
        $t->assert('check ' . source_type::XBRL, $result, 2);


        $t->subheader('Source list tests');
        $t->name = 'source read db->';

        $test_name = 'loading by source list by ids ';
        $src_lst = new source_list($t->usr1);
        $src_lst->load_by_ids([1]);
        $result = $src_lst->name();
        $target = '"' . source_api::TN_READ . '"';
        $t->assert($test_name . $src_lst->dsp_id(), $result, $target);

        $test_name = 'loading the api message creation of the api index file for ';
        // TODO add this to all db read tests for all API call functions
        $result = json_decode(json_encode($src_lst->api_obj()), true);
        $class_for_file = $t->class_without_namespace(source_list::class);
        $target = json_decode($t->api_json_expected($class_for_file), true);
        $t->assert($test_name . $src_lst->dsp_id(), $lib->json_is_similar($target, $result), true);

        $test_name = 'loading by source list by pattern ';
        $src_lst = new source_list($t->usr1);
        $pattern = substr(source_api::TN_READ, 0, -1);
        $src_lst->load_like($pattern);
        $t->assert_contains($test_name, $src_lst->names(), source_api::TN_READ);

    }

}

