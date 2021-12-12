<?php

/*

  test/unit/word_list.php - TESTing of the WORD LIST functions
  -----------------------
  

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

class word_list_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $t->header('Unit tests of the word list class (src/main/php/model/word/word_list.php)');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $db_con = new sql_db();
        $db_con->db_type = sql_db::POSTGRES;

        // sql to load by word list by ids
        $wrd_lst = new word_list;
        $wrd_lst->ids = [1, 2, 3];
        $wrd_lst->usr = $usr;
        $created_sql = $wrd_lst->load_sql($db_con);
        $expected_sql = $t->file('db/word/word_list_by_id_list.sql');
        $t->assert('word_list->load_sql by IDs', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lst->load_sql($db_con, true));

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $wrd_lst->load_sql($db_con);
        $expected_sql = $t->file('db/word/word_list_by_id_list_mysql.sql');
        $t->assert('word_list->load_sql by IDs', $t->trim($created_sql), $t->trim($expected_sql));

        // sql to load by word list by phrase group
        $db_con->db_type = sql_db::POSTGRES;
        $wrd_lst = new word_list;
        $wrd_lst->grp_id = 1;
        $wrd_lst->usr = $usr;
        $created_sql = $wrd_lst->load_sql($db_con);
        $expected_sql = $t->file('db/word/word_list_by_phrase_group.sql');
        $t->assert('word_list->load_sql by phrase group', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lst->load_sql($db_con, true));

        // TODO add the missing word list loading SQL

        // SQL to add by word list by a relation e.g. for "Zurich" and direction "up" add "City", "Canton" and "Company"
        $wrd_lst = new word_list;
        $wrd_lst->usr = $usr;
        $wrd_lst->ids = [7];
        $created_sql = $wrd_lst->add_by_type_sql($db_con, 2, verb::DIRECTION_UP);
        $expected_sql = $t->file('db/word/word_list_by_verb_up.sql');
        $t->assert('word_list->add_by_type_sql by verb and up', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($wrd_lst->add_by_type_sql($db_con, 2, verb::DIRECTION_UP, true));

    }

}