<?php

/*

    /test/php/unit_db/formula_list.php - TESTing of the FORMULA LIST functions that only read from the database
    ----------------------------------
  

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

use api\formula_api;

class formula_list_unit_db_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $t->name = 'formula list read db->';

        $t->header('Test the formula list class (classes/formula_list.php)');

        // test load by formula list by ids
        $frm_lst = new formula_list($usr);
        $frm_lst->load_by_ids([1, 2]);
        $result = $frm_lst->name();
        $target = formula_api::TN_READ . ',' . formula_api::TN_READ_ANOTHER; // order adjusted based on the number of usage
        $t->assert('load by ids for ' . $frm_lst->dsp_id(), $result, $target);
    }

}

