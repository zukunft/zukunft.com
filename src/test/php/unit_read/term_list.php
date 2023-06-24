<?php

/*

    test/php/unit_read/term_list.php - TESTing of the TERM LIST functions that only read from the database
    --------------------------------
  

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

use api\word_api;
use api\triple_api;
use api\formula_api;
use api\verb_api;
use cfg\library;
use cfg\term_list;
use cfg\trm_ids;

class term_list_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $t->name = 'term list read db->';

        $t->header('Test the term list class (classes/term_list.php)');

        // test load by term list by ids
        $trm_lst = new term_list($t->usr1);
        $trm_lst->load_by_ids((new trm_ids([1, -1, 2, -2])));
        $result = $trm_lst->name();
        $target = '"' . triple_api::TN_READ . '","' .
            word_api::TN_READ . '","' .
            verb_api::TN_READ . '","' .
            formula_api::TN_READ . '"'; // order adjusted based on the number of usage
        $t->assert('load by ids for ' . $trm_lst->dsp_id(), $result, $target);

        // test the api message creation of the api index file
        // TODO add this to all db read tests for all API call functions
        $result = json_decode(json_encode($trm_lst->api_obj()), true);
        $class_for_file = $t->class_without_namespace(term_list::class);
        $target = json_decode($t->api_json_expected($class_for_file), true);
        $t->assert('api json based on id loading for ' . $trm_lst->dsp_id(), $lib->json_is_similar($target, $result), true);

    }

}

