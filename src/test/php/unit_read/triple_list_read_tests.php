<?php

/*

    test/php/unit_read/triple_list.php - TESTing of the TRIPLE LIST functions that only read from the database
    ----------------------------------
  

    This file is part of zukunft.com - calc with triples

    zukunft.com is free software: you can redistribute it and/or modify it
    under the triples of the GNU General Public License as
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

use cfg\const\paths;

include_once paths::SHARED_CONST . 'triples.php';

use cfg\word\triple;
use cfg\word\triple_list;
use shared\const\triples;
use test\test_cleanup;

class triple_list_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->name = 'triple list read db->';

        $t->header('triple list database read tests');

        // test loading triple names
        $test_name = 'loading triple names with pattern return the expected triple';
        $pattern = substr(triples::MATH_CONST, 0, -1);
        $trp_lst = new triple_list($t->usr1);
        $trp_lst->load_names($pattern);
        $t->assert_contains($test_name, $trp_lst->names(), triples::MATH_CONST);


        // test load by triple list by ids
        $test_name = 'load triples by ids';
        $trp_lst = new triple_list($t->usr1);
        $trp_lst->load_by_ids(array(triples::MATH_CONST_ID,triples::PI_SYMBOL_ID));
        $target = array(triples::MATH_CONST, triples::PI_SYMBOL_NAME); // order adjusted based on the number of usage
        $t->assert_contains($test_name, $trp_lst->names(), $target);
        /* TODO activate
        $test_name = 'load triples by names';
        $wrd_lst = new triple_list($t->usr1);
        $wrd_lst->load_by_names(array(triples::TN_READ,triples::TN_PI));
        $t->assert_contains($test_name, $wrd_lst->ids(), array(1,3));
        $test_name = 'load triples staring with P';
        $wrd_lst = new triple_list($t->usr1);
        $wrd_lst->load_like('P');
        $t->assert_contains($test_name, $wrd_lst->names(), triples::TN_PI);
        */

        $test_name = 'all expected test triples are in the database';
        $t->assert_db_test_id_list($test_name, triples::TEST_TRIPLE_IDS, new triple($t->usr1), new triple_list($t->usr1));

    }

}

