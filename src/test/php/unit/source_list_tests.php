<?php

/*

    test/unit/source_list_tests.php - unit testing for source lists
    -------------------------------
  

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

namespace unit;

use cfg\db\sql_creator;
use cfg\ref\source_list;
use test\test_cleanup;

class source_list_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init for source list
        $sc = new sql_creator();
        $t->name = 'source_list->';

        // start the test section (ts)
        $ts = 'unit source list ';
        $t->header($ts);

        $t->subheader($ts . 'sql read');
        $test_name = 'load sources by ids';
        $src_lst = new source_list($usr);
        $trm_ids = array(1, 2, 3);
        $t->assert_sql_by_ids($test_name, $sc, $src_lst, $trm_ids);
        $src_lst = new source_list($usr);
        $t->assert_sql_like($sc, $src_lst);

    }

}

