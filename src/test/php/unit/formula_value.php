<?php

/*

    test/unit/formula_value.php - unit testing of the FORMULA VALUE functions
    ---------------------------
  

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class formula_value_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'formula_value->';
        $t->resource_path = 'db/result/';
        $usr->id = 1;

        $t->header('Unit tests of the formula value class (src/main/php/model/formula/formula_value.php)');

        $t->subheader('SQL creation tests');

        // sql to load a formula value by the id
        $fv = new formula_value($usr);
        $fv->id = 1;
        $t->assert_load_sql($db_con, $fv);


        $t->header('Unit tests of the formula value list class (src/main/php/model/formula/formula_value_list.php)');

        $t->subheader('SQL creation tests');

        // sql to load a list of formula values by the formula id
        $fv_lst = new formula_value_list($usr);
        $frm = new formula($usr);
        $frm->id = 1;
        $t->assert_load_list_sql($db_con, $fv_lst, $frm);

        // sql to load a list of formula values by the phrase group id
        $fv_lst = new formula_value_list($usr);
        $grp = new phrase_group($usr);
        $grp->id = 2;
        $t->assert_load_list_sql($db_con, $fv_lst, $grp);

        // sql to load a list of formula values by the source phrase group id
        $fv_lst = new formula_value_list($usr);
        $grp = new phrase_group($usr);
        $grp->id = 2;
        $t->assert_load_list_sql($db_con, $fv_lst, $grp, true);

    }

}