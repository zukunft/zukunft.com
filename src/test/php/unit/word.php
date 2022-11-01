<?php

/*

    test/unit/word.php - unit testing of the word functions
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

class word_unit_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'word->';
        $t->resource_path = 'db/word/';
        $json_file = 'unit/word/second.json';
        $usr->id = 1;

        $t->header('Unit tests of the word class (src/main/php/model/word/word.php)');


        $t->subheader('API unit tests');

        $wrd = new word($usr, 1, word::TN_READ);
        $wrd->description = 'A mathematical constant that never changes e.g. Pi';
        $api_wrd = $wrd->api_obj();
        $t->assert($t->name . 'api->id', $api_wrd->id, $wrd->id());
        $t->assert($t->name . 'api->name', $api_wrd->name, $wrd->name());
        $t->assert($t->name . 'api->description', $api_wrd->description, $wrd->description);


        $t->subheader('SQL statement tests');

        // sql to load the word by id
        $wrd = new word($usr);
        $wrd->id = 2;
        $t->assert_load_sql($db_con, $wrd);
        $t->assert_load_standard_sql($db_con, $wrd);
        $t->assert_not_changed_sql($db_con, $wrd);

        // sql to load the word by name
        $wrd = new word($usr);
        $wrd->id = 0;
        $wrd->name = word::TN_READ;
        $t->assert_load_sql($db_con, $wrd);

        // get the most often used view
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $wrd->view_sql($db_con);
        $t->assert_qp($qp, $db_con->db_type);

        $db_con->db_type = sql_db::MYSQL;
        $qp = $wrd->view_sql($db_con);
        $t->assert_qp($qp, $db_con->db_type);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new word($usr), $json_file);

    }

}