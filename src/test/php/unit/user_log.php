<?php

/*

  test/unit/user_log.php - unit testing of the user log functions
  ----------------------
  

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

use api\word_api;

class user_log_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        $t->header('Unit tests of the user log display class (src/main/php/web/user_log_display.php)');

        $t->subheader('SQL statement tests');

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'word->';
        $t->resource_path = 'db/user/';
        $usr->id = 1;

        // sql to load the word by id
        $log_dsp = new user_log_display($usr);
        $log_dsp->type = user::class;
        $log_dsp->size = SQL_ROW_LIMIT;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $log_dsp->dsp_hist_links_sql($db_con);
        $expected_sql = $t->file('db/user/user_log.sql');
        $t->dsp('user_log_display->dsp_hist_links_sql by ' . $log_dsp->type, $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($log_dsp->dsp_hist_links_sql($db_con, true));

        // sql to load a log entry by field and row id
        // TODO check that user specific changes are included in the list of changes
        $log = new user_log_named();
        $log->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log->load_sql_by_field_row($db_con, 1, 2);
        $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $log->load_sql_by_field_row($db_con, 1, 2);
        $t->assert_qp($qp, $db_con->db_type);

        // sql to load a log entry by field and row id
        $log = new user_log_link();
        $log->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log->load_sql($db_con, 1);
        $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $log->load_sql($db_con, 1);
        $t->assert_qp($qp, $db_con->db_type);

        // compare the new and the old query creation
        $log = new user_log_named();
        $log->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log->load_sql_by_field_row($db_con, 1, 2);
        $sql_expected = 'PREPARE user_log_named_by_field_row (int,int) AS ' . $log->load_sql_old(word::class, )->sql;
        $t->assert_sql('word', $qp->sql, $sql_expected);

        // sql to load a list of log entry by phrase
        $db_con->set_usr($usr->id);
        $wrd = new word($usr);
        $wrd->set(1, word_api::TN_READ);
        $log_lst = new change_log_list();
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log_lst->load_sql_dsp_of_phr($db_con, $wrd->phrase());
        $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $log_lst->load_sql_dsp_of_phr($db_con, $wrd->phrase());
        $t->assert_qp($qp, $db_con->db_type);


    }

}
