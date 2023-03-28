<?php

/*

    test/unit/change_log.php - unit testing of the user log functions
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace test;

include_once WEB_PATH . 'user_log_display.php';
include_once MODEL_LOG_PATH . 'change_log_named.php';
include_once MODEL_LOG_PATH . 'change_log_link.php';

use api\triple_api;
use model\change_log_field;
use model\change_log_link;
use model\change_log_list;
use model\change_log_named;
use model\change_log_table;
use model\library;
use model\sql_db;
use model\triple;
use model\user;
use model\word;
use user_log_display;

class change_log_unit_tests
{
    function run(testing $t): void
    {

        global $usr;

        $t->header('Unit tests of the user log display class (src/main/php/log/change_log_*.php)');

        $t->subheader('SQL statement tests');

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'change_log->';
        $t->resource_path = 'db/log/';
        $usr->set_id(1);

        // sql to load the word by id
        $log_dsp = new user_log_display($usr);
        $log_dsp->type = user::class;
        $log_dsp->size = SQL_ROW_LIMIT;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $log_dsp->dsp_hist_links_sql($db_con);
        $expected_sql = $t->file('db/log/change_log.sql');
        $t->dsp('user_log_display->dsp_hist_links_sql by ' . $log_dsp->type, $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($log_dsp->dsp_hist_links_sql($db_con, true));

        // sql to load a log entry by field and row id
        // TODO check that user specific changes are included in the list of changes
        $log = new change_log_named();
        $log->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log->load_sql_by_field_row($db_con, 1, 2);
        $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $log->load_sql_by_field_row($db_con, 1, 2);
        $t->assert_qp($qp, $db_con->db_type);

        // sql to load a log entry by field and row id
        $log = new change_log_link();
        $log->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log->load_sql($db_con, 1);
        $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $log->load_sql($db_con, 1);
        $t->assert_qp($qp, $db_con->db_type);

        // compare the new and the old query creation
        $log = new change_log_named();
        $log->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log->load_sql_by_field_row($db_con, 1, 2);
        $sql_expected = 'PREPARE change_log_named_by_field_row (int,int) AS ' . $log->load_sql_old(word::class)->sql;
        $t->assert_sql('word', $qp->sql, $sql_expected);

        $t->subheader('SQL list statement tests');

        // prepare the objects for the tests
        $wrd = $t->dummy_word();
        $trp = new triple($usr);
        $trp->set(1, triple_api::TN_READ);

        // sql to load a list of log entry by word
        $db_con->set_usr($usr->id());
        $log_lst = new change_log_list();
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log_lst->load_sql_obj_fld(
            $db_con,
            change_log_table::WORD,
            change_log_field::FLD_WORD_VIEW,
            'dsp_of_wrd',
            $wrd->id());
        $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $log_lst->load_sql_obj_fld(
            $db_con,
            change_log_table::WORD,
            change_log_field::FLD_WORD_VIEW,
            'dsp_of_wrd',
            $wrd->id());
        $t->assert_qp($qp, $db_con->db_type);

        // sql to load a list of log entry by phrase
        $db_con->set_usr($usr->id());
        $log_lst = new change_log_list();
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log_lst->load_sql_obj_fld(
            $db_con,
            change_log_table::TRIPLE,
            change_log_field::FLD_TRIPLE_VIEW,
            'dsp_of_trp',
            $trp->id());
        $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $log_lst->load_sql_obj_fld(
            $db_con,
            change_log_table::TRIPLE,
            change_log_field::FLD_TRIPLE_VIEW,
            'dsp_of_trp',
            $trp->id());
        $t->assert_qp($qp, $db_con->db_type);


        $t->subheader('API unit tests');

        $log_lst = $t->dummy_change_log_list_named();
        $t->assert_api($log_lst);

    }

}
