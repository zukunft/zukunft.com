<?php

/*

    test/unit/phrase.php - unit testing of the phrase functions
    ------------------


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

class phrase_unit_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'word->';
        $t->resource_path = 'db/phrase/';
        $json_file = 'unit/phrase/second.json';
        $usr->id = 1;

        $t->header('Unit tests of the word class (src/main/php/model/phrase/phrase.php)');


        $t->subheader('SQL statement tests');

        $phr = new phrase($usr);
        $t->assert_load_sql_id($db_con, $phr);
        $t->assert_load_sql_name($db_con, $phr);

        // sql to load the word by id
        $phr = new phrase($usr);
        $phr->id = 2;

        // check the PostgreSQL query syntax
        $wrd_company = new word($usr);
        $wrd_company->id = 2;
        $wrd_company->set_name(word::TN_COMPANY);
        $sql_name = 'phrase_list_related';
        $db_con->db_type = sql_db::POSTGRES;
        $file_name = $t->resource_path . $sql_name . test_base::FILE_EXT;
        $created_sql = $phr->sql_list($wrd_company);
        $expected_sql = $t->file($file_name);
        $result = $t->assert_sql($t->name . $sql_name, $created_sql, $expected_sql
        );

    }

}