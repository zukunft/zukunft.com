<?php

/*

    test/php/unit_save/expression.php - TESTing of the expression function that only read from the database
    ---------------------------------
  

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

use api\formula\formula as formula_api;
use test\test_cleanup;

class expression_read_tests
{
    function run(test_cleanup $t): void
    {

        global $db_con;
        global $usr;

        // init
        $t->name = 'expression->';

        $t->header('Test the term class (src/main/php/model/formula/expression.php)');

        /*
        $frm = new formula($usr);
        $frm->load_by_name(formulas::TN_SECTOR);
        $result = $frm->usr_text;
        $target = '= "' . words::TN_COUNTRY . '" "differentiator" "' . words::TN_CANTON . '" / "' . words::TN_TOTAL . '"';
        $t->assert('expression->is_std if formula is changed by the user', $result, $target);
        */

    }
}

