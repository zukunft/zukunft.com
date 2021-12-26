<?php

/*

  test/unit/formula.php - unit testing of the formula functions
  ---------------------
  

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

class formula_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $t->header('Unit tests of the formula class (src/main/php/model/formula/formula.php)');

        $t->subheader('SQL statement tests');

        $db_con = new sql_db();

        // sql to load the formula by id
        $frm = new formula($usr);
        $frm->id = 2;
        $frm->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $frm->load_sql($db_con);
        $expected_sql = $t->file('db/formula/formula_by_id.sql');
        $t->assert('formula->load_sql by formula id', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($frm->load_sql($db_con, true));

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $frm->load_sql($db_con);
        $expected_sql = $t->file('db/formula/formula_by_id_mysql.sql');
        $t->assert('formula->load_sql by formula id', $t->trim($created_sql), $t->trim($expected_sql));


        $t->subheader('Im- and Export tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/formula/scale_second_to_minute.json'), true);
        $frm = new formula($usr);
        $frm->import_obj($json_in, false);
        $json_ex = json_decode(json_encode($frm->export_obj(false)), true);
        $result = json_is_similar($json_in, $json_ex);
        $t->assert('formula->import check name', $result, true);

    }

}