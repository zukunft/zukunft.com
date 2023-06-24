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

namespace test;

use api\word_api;
use cfg\word_list;

class word_list_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->name = 'word list read db->';

        $t->header('Test the word list class (classes/word_list.php)');

        // test load by word list by ids
        $wrd_lst = new word_list($t->usr1);
        $wrd_lst->load_by_ids(array(1,3));
        $result = $wrd_lst->name();
        $target = '"' . word_api::TN_READ . '","' . word_api::TN_PI . '"'; // order adjusted based on the number of usage
        $t->assert('load by ids for ' . $wrd_lst->dsp_id(), $result, $target);
    }

}

