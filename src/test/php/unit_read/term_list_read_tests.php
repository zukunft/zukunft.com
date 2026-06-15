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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\trm_ids;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class term_list_read_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $t->name = 'term list read db->';

        // start the test section (ts)
        $ts = 'db read term list ';
        $t->header($ts);

        $test_name = 'loading phrase names with pattern return the expected word';
        $lst = new term_list($t->usr1);
        $pattern = substr(word_names::MATH, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), word_names::MATH);
        $test_name = 'loading phrase names with pattern return the expected verb';
        $lst = new term_list($t->usr1);
        $pattern = substr(verbs::NOT_SET, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), verbs::NOT_SET_NAME);
        $test_name = 'loading phrase names with pattern return the expected triple';
        $lst = new term_list($t->usr1);
        $pattern = substr(triple_names::MATH_CONST, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), triple_names::MATH_CONST);
        $test_name = 'loading phrase names with pattern return the expected formula';
        $lst = new term_list($t->usr1);
        $pattern = substr(formulas::SCALE_TO_SEC, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), formulas::SCALE_TO_SEC);

        $test_name = 'loading by term list by ids ';
        $trm_lst = new term_list($t->usr1);
        $trm_lst->load_by_ids((new trm_ids([1, -1, 2, -2])));
        $result = $trm_lst->name();
        $target = '"' . triple_names::MATH_CONST . '","' .
            word_names::MATH . '","' .
            verbs::NOT_SET_NAME . '","' .
            formulas::SCALE_TO_SEC . '"'; // order adjusted based on the number of usage
        $t->assert($test_name . $trm_lst->dsp_id(), $result, $target);

        $test_name = 'loading the api message creation of the api index file for ';
        // TODO add this to all db read tests for all API call functions
        $json_txt = $trm_lst->api_json();
        $result = json_decode($json_txt, true);
        $result = $t->json_remove_fields_only_to_ui($result);
        $class_for_file = $t->class_without_namespace(term_list::class);
        $target = json_decode($t->api_json_expected($class_for_file . '_without_link'), true);
        $t->assert_json($test_name . $trm_lst->dsp_id(), $result, $target);

        $test_name = 'loading by term list by pattern ';
        $trm_lst = new term_list($t->usr1);
        $pattern = substr(word_names::MATH, 0, -1);
        $trm_lst->load_like($pattern);
        $t->assert_contains($test_name, $trm_lst->names(), word_names::MATH);

    }

}

