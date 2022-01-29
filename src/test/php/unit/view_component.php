<?php

/*

    test/unit/view_component.php - unit testing of the view component functions
    ----------------------------
  

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class view_component_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'view_component->';
        $t->resource_path = 'db/view/';
        $json_file = 'unit/view/car_costs.json';
        $usr->id = 1;

        $t->header('Unit tests of the view class (src/main/php/model/value/view_component.php)');


        $t->subheader('SQL statement tests');

        // sql to load the view components by id
        $cmp = new view_cmp($usr);
        $cmp->id = 2;
        $t->assert_load_sql($db_con, $cmp);
        $t->assert_load_standard_sql($db_con, $cmp);

        // sql to load the view components by name
        $cmp = new view_cmp($usr);
        $cmp->name = view::TN_ADD;
        $t->assert_load_sql($db_con, $cmp);
        $t->assert_load_standard_sql($db_con, $cmp);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new view_dsp($usr), $json_file);

    }

}