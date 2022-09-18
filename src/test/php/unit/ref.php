<?php

/*

    test/unit/ref.php - unit testing of the reference and source functions
    -----------------
  

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

class ref_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'ref->';
        $t->resource_path = 'db/ref/';
        $json_file = 'unit/ref/wikipedia.json';
        $usr->id = 1;

        $t->header('Unit tests of the Ref class (src/main/php/model/ref/ref.php)');

        $t->subheader('Im- and Export tests');

        $t->assert_json(new ref($usr), $json_file);


        $t->resource_path = 'db/ref/';
        $t->header('Unit tests of the source class (src/main/php/model/ref/source.php)');


        $t->subheader('SQL statement tests');

        // sql to load a source by id
        $src = new source($usr);
        $src->id = 4;
        $t->assert_load_sql($db_con, $src);
        $t->assert_load_standard_sql($db_con, $src);

        // sql to load a source by code id
        $src = new source($usr);
        $src->code_id = source::TN_READ;
        $t->assert_load_sql($db_con, $src);

        // sql to load a source by name
        $src = new source($usr);
        $src->name = source::TN_READ;
        $t->assert_load_sql($db_con, $src);
        $t->assert_load_standard_sql($db_con, $src);
        $src->id = 5;
        $t->assert_not_changed_sql($db_con, $src);
        $t->assert_user_config_sql($db_con, $src);

        // sql to load the ref types
        $ref_type_list = new ref_type_list();
        $t->assert_load_sql($db_con, $ref_type_list, DB_TYPE_REF_TYPE);

    }

}

