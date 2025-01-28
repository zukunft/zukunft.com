<?php

/*

    test/php/unit_read/word_list.php - TESTing of the WORD LIST functions that only read from the database
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

include_once SHARED_PATH . 'formulas.php';
include_once SHARED_PATH . 'words.php';

use cfg\word\word_list;
use shared\formulas;
use shared\words;
use test\test_cleanup;

class word_list_read_tests
{

    function run(test_cleanup $t): void
    {

        // TODO change in all other tests and later here (like in element_list_tests):
        // TODO move the main object to init for all unit an read db tests
        // TODO start the test always with the test name
        // TODO create const whereever possible
        // TODO use the test user instead of the global user

        global $usr;

        // init
        $t->name = 'word list read db->';

        $t->header('word list database read unit tests');

        // test loading word names
        $wrd_lst = new word_list($t->usr1);
        $test_name = 'loading word names without pattern return more than two words';
        $wrd_lst->load_names();
        $t->assert_greater($test_name, 2, $wrd_lst->count());
        $test_name = 'loading word names with pattern return the expected word';
        $pattern = substr(words::MATH, 0, -1);
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_names($pattern);
        $t->assert_contains($test_name, $wrd_lst->names(), words::MATH);
        $test_name = 'loading word names with page size one return only one word';
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_names($pattern, 1, 0);
        $t->assert($test_name, $wrd_lst->count(), 1);
        $test_name = 'next page with page size one does not return the pattern word';
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_names($pattern, 1, 1);
        $t->assert_contains_not($test_name, $wrd_lst->names(), words::MATH);
        $test_name = 'formula names are not included in the normal word list';
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_names(formulas::SCALE_TO_SEC);
        $t->assert_contains_not($test_name, $wrd_lst->names(), formulas::SCALE_TO_SEC);


        // test load by word list by ids
        $test_name = 'load words by ids';
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_by_ids(array(1,words::PI_ID));
        $target = '"' . words::MATH . '","' . words::PI . '"'; // order adjusted based on the number of usage
        $t->assert($test_name, $wrd_lst->name(), $target);
        $test_name = 'load words by names';
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_by_names(array(words::MATH,words::PI));
        $t->assert_contains($test_name, $wrd_lst->ids(), array(1,words::PI_ID));
        $test_name = 'load words staring with P';
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_like('P');
        $t->assert_contains($test_name, $wrd_lst->names(), words::PI);

    }

}

