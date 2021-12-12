<?php

/*

  test/unit/value_list.php - unit testing of the VALUE LIST functions
  ------------------------
  

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

class value_list_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $t->header('Unit tests of the value list class (src/main/php/model/value/value_list.php)');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $db_con = new sql_db();
        $db_con->db_type = sql_db::POSTGRES;

        // sql to load a list of value by the phrase id
        $wrd = new word();
        $wrd->id = 1;
        $val_lst = new value_list;
        $val_lst->phr = $wrd->phrase();
        $val_lst->usr = $usr;
        $created_sql = $val_lst->load_sql($db_con);
        $expected_sql = $t->file('db/value/value_list_by_word_id.sql');
        $t->assert('value_list->load_sql by phrase id', $t->trim($created_sql), $t->trim($expected_sql));

        // sql to load a list of value by the phrase ids
        $val_lst = new value_list;
        $val_lst->phr_lst = (new phrase_list_unit_tests)->get_phrase_list();
        $val_lst->phr_lst->ids = $val_lst->phr_lst->ids();
        $val_lst->usr = $usr;
        $created_sql = $val_lst->load_by_phr_lst_sql($db_con);
        $expected_sql = $t->file('db/value/value_list_by_triple_id_list.sql');
        $t->assert('value_list->load_by_phr_lst_sql by group and time', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($val_lst->load_by_phr_lst_sql($db_con, true));

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $val_lst->usr = $usr;
        $created_sql = $val_lst->load_by_phr_lst_sql($db_con);
        $expected_sql = $t->file('db/value/value_list_by_triple_id_list_mysql.sql');
        $t->assert('value_list->load_by_phr_lst_sql by group and time for MySQL', $t->trim($created_sql), $t->trim($expected_sql));

    }

}