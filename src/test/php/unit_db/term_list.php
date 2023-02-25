<?php

/*

    test/php/unit_db/term_list.php - TESTing of the TERM LIST functions that only read from the database
    ------------------------------
  

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

use api\triple_api;
use api\word_api;

class term_list_unit_db_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $t->name = 'term list read db->';

        $t->header('Test the term list class (classes/term_list.php)');

        // test load by term list by ids
        $trm_lst = new term_list($usr);
        $trm_lst->load_by_ids((new trm_ids([1, -1])));
        $result = $trm_lst->name();
        $target = '"' . word_api::TN_READ . '","' . triple_api::TN_READ_NAME . '"'; // order adjusted based on the number of usage
        $t->assert('load by ids for ' . $trm_lst->dsp_id(), $result, $target);
    }

}

