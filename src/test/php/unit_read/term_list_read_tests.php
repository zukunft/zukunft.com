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

namespace unit_read;

include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'formulas.php';
include_once SHARED_CONST_PATH . 'words.php';

use cfg\phrase\term_list;
use cfg\phrase\trm_ids;
use shared\library;
use shared\const\formulas;
use shared\const\triples;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class term_list_read_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $t->name = 'term list read db->';

        $t->header('term list database read unit tests');

        $test_name = 'loading phrase names with pattern return the expected word';
        $lst = new term_list($t->usr1);
        $pattern = substr(words::MATH, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), words::MATH);
        $test_name = 'loading phrase names with pattern return the expected verb';
        $lst = new term_list($t->usr1);
        $pattern = substr(verbs::TN_READ, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), verbs::TN_READ);
        $test_name = 'loading phrase names with pattern return the expected triple';
        $lst = new term_list($t->usr1);
        $pattern = substr(triples::MATH_CONST, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), triples::MATH_CONST);
        $test_name = 'loading phrase names with pattern return the expected formula';
        $lst = new term_list($t->usr1);
        $pattern = substr(formulas::SCALE_TO_SEC, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), formulas::SCALE_TO_SEC);

        $test_name = 'loading by term list by ids ';
        $trm_lst = new term_list($t->usr1);
        $trm_lst->load_by_ids((new trm_ids([1, -1, 2, -2])));
        $result = $trm_lst->name();
        $target = '"' . triples::MATH_CONST . '","' .
            words::MATH . '","' .
            verbs::TN_READ . '","' .
            formulas::SCALE_TO_SEC . '"'; // order adjusted based on the number of usage
        $t->assert($test_name . $trm_lst->dsp_id(), $result, $target);

        $test_name = 'loading the api message creation of the api index file for ';
        // TODO add this to all db read tests for all API call functions
        $json_txt = $trm_lst->api_json();
        $result = json_decode($json_txt, true);
        $class_for_file = $t->class_without_namespace(term_list::class);
        $target = json_decode($t->api_json_expected($class_for_file . '_without_link'), true);
        $t->assert_json($test_name . $trm_lst->dsp_id(), $result, $target);

        $test_name = 'loading by term list by pattern ';
        $trm_lst = new term_list($t->usr1);
        $pattern = substr(words::MATH, 0, -1);
        $trm_lst->load_like($pattern);
        $t->assert_contains($test_name, $trm_lst->names(), words::MATH);

    }

}

