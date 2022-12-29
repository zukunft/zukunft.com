<?php

/*

    test/unit/user.php - unit testing of the user functions
    ------------------


    This file is part of zukunft.com - calc with users

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

use api\user_api;
use cfg\phrase_type;

class user_unit_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'user->';
        $t->resource_path = 'db/user/';
        $json_file = '';
        $usr->id = 1;

        $t->header('Unit tests of the user class (src/main/php/model/user/user.php)');


        $t->subheader('SQL statement tests');

        $test_usr = new user();
        $t->assert_load_sql_id($db_con, $test_usr);
        // $t->assert_load_sql_name($db_con, $test_usr);


        $t->subheader('API unit tests');

        $test_usr = $t->dummy_user();
        $t->assert_api($test_usr);


        $t->subheader('Im- and Export tests');

        //$t->assert_json(new user(), $json_file);

    }

}