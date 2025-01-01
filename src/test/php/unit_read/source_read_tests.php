<?php

/*

    test/php/unit_read/source_read_tests.php - database unit testing of sources
    ----------------------------------------


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

use api\ref\source as source_api;
use cfg\ref\source;
use cfg\ref\source_type;
use cfg\source_list;
use cfg\ref\source_type_list;
use shared\library;
use test\test_cleanup;

class source_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $src_typ_cac;

        // init
        $lib = new library();
        $t->name = 'source db read->';

        $t->header('source db read tests');

        $t->subheader('source load');
        $src = new source($t->usr1);
        $t->assert_load($src, source_api::TN_READ);
        $test_name = 'check description of source ' . source_api::TN_READ;
        $t->assert($test_name, $src->description, source_api::TD_READ);
        $t->assert_load_by_code_id($src, source_api::TC_READ);

        $t->subheader('source load types');
        $lst = new source_type_list();
        $result = $lst->load($db_con);
        $t->assert('load_source_types', $result, true);
        $test_name = '... and check if at least ' . source_type::XBRL . ' is loaded';
        $t->assert($test_name, $src_typ_cac->id(source_type::XBRL), source_type::XBRL_ID);

        $t->subheader('source list tests');
        $test_name = 'loading by source list by ids ';
        $src_lst = new source_list($t->usr1);
        $src_lst->load_by_ids([source_api::TI_READ_REF]);
        $t->assert($test_name . $src_lst->dsp_id(), $src_lst->name(), '"' . source_api::TN_READ_REF . '"');

        $test_name = 'loading the api message creation of the api index file for ';
        // TODO add this to all db read tests for all API call functions
        $result = json_decode(json_encode($src_lst->api_obj()), true);
        $class_for_file = $t->class_without_namespace(source_list::class);
        $target = json_decode($t->api_json_expected($class_for_file), true);
        $t->assert($test_name . $src_lst->dsp_id(), $lib->json_is_similar($target, $result), true);

        $test_name = 'loading by source list by pattern ';
        $src_lst = new source_list($t->usr1);
        $pattern = substr(source_api::TN_READ_REF, 0, -1);
        $src_lst->load_like($pattern);
        $t->assert_contains($test_name, $src_lst->names(), source_api::TN_READ_REF);

    }

}

