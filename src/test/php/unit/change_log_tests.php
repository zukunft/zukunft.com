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

namespace unit;

include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once WEB_LOG_PATH . 'user_log_display.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_link.php';

use api\word\triple as triple_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_field;
use cfg\log\change_link;
use cfg\log\change_log_list;
use cfg\log\change_table;
use cfg\log\change_table_field;
use cfg\triple;
use cfg\user;
use html\log\user_log_display;
use shared\library;
use test\test_cleanup;

class change_log_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'change_log->';
        $t->resource_path = 'db/log/';
        $usr->set_id(1);


        $t->header('Unit tests of the user log display class (src/main/php/log/change_log_*.php)');

        $t->subheader('Log action SQL setup statements');
        $act = new change_action('');
        $t->assert_sql_table_create($act);
        $t->assert_sql_index_create($act);

        $t->subheader('Log table SQL setup statements');
        $tbl = new change_table('');
        $t->assert_sql_table_create($tbl);
        $t->assert_sql_index_create($tbl);

        $t->subheader('Log field SQL setup statements');
        $fld = new change_field('');
        $t->assert_sql_table_create($fld);
        $t->assert_sql_index_create($fld);
        $t->assert_sql_foreign_key_create($fld);

        $t->subheader('Log table field view SQL setup statements');
        $tbl_fld = new change_table_field();
        $t->assert_sql_view_link_create($tbl_fld);

        $t->subheader('SQL statement creation tests for logging named objects e.g. formula');
        $log = $t->change_log_named();
        $t->assert_sql_table_create($log);
        $t->assert_sql_index_create($log);
        $t->assert_sql_foreign_key_create($log);
        // TODO add auto increment test for all mysql tables

        $t->subheader('SQL statement creation tests for logging standard value');
        $log_val_std = $t->change_log_value();
        $t->assert_sql_table_create($log_val_std);
        $t->assert_sql_index_create($log_val_std);
        $t->assert_sql_foreign_key_create($log_val_std);

        $t->subheader('SQL statement creation tests for logging prime value');
        $log_val_prm = $t->dummy_change_log_value_prime();
        $t->assert_sql_table_create($log_val_prm);
        $t->assert_sql_index_create($log_val_prm);
        $t->assert_sql_foreign_key_create($log_val_prm);

        $t->subheader('SQL statement creation tests for logging big value');
        $log_val_big = $t->dummy_change_log_value_big();
        $t->assert_sql_table_create($log_val_big);
        $t->assert_sql_index_create($log_val_big);
        $t->assert_sql_foreign_key_create($log_val_big);

        $t->subheader('SQL statement creation tests for logging link changes');
        $log_lnk = $t->change_log_link();
        $t->assert_sql_table_create($log_lnk);
        $t->assert_sql_index_create($log_lnk);
        $t->assert_sql_foreign_key_create($log_lnk);

        $t->subheader('change log sql write');
        $log = $t->change_log_named();
        $t->assert_sql_insert($sc, $log);
        $t->assert_sql_insert($sc, $log, [sql_type::SUB]);

        $t->subheader('change link log sql write');
        $log_lnk = $t->change_log_link();
        $t->assert_sql_insert($sc, $log_lnk);
        $t->assert_sql_insert($sc, $log_lnk, [sql_type::SUB]);

        $t->subheader('SQL statement tests');
        $log = new change($usr);
        $t->assert_sql_by_user($sc, $log);

        $log = new change_link($usr);
        $t->assert_sql_by_user($sc, $log);

        // sql to load the word by id
        $log_dsp = new user_log_display($usr);
        $log_dsp->type = $lib->class_to_name(user::class);
        $log_dsp->size = sql_db::ROW_LIMIT;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $log_dsp->dsp_hist_links_sql($db_con);
        $expected_sql = $t->file('db/log/change_log.sql');
        $t->display('user_log_display->dsp_hist_links_sql by ' . $log_dsp->type, $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($log_dsp->dsp_hist_links_sql($db_con, true));

        // sql to load a log entry by field and row id
        // TODO check that user specific changes are included in the list of changes
        $log = new change($usr);
        $this->assert_sql_named_by_field_row($t, $db_con, $log);

        // sql to load a log entry by field and row id
        $log = new change_link($usr);
        $this->assert_sql_link_by_table($t, $db_con, $log);

        $t->subheader('SQL list statement tests');

        // prepare the objects for the tests
        $wrd = $t->word();
        $trp = new triple($usr);
        $trp->set(1, triple_api::TN_PI);

        // sql to load a list of log entry by word
        $db_con->set_usr($usr->id());
        $log_lst = new change_log_list();
        // TODO activate Prio 2
        //$this->assert_sql_list_by_obj_field($t, $db_con, $log_lst,            change_table_list::WORD, change_field_list::FLD_WORD_VIEW);
        //$this->assert_sql_list_by_obj_field($t, $db_con, $log_lst,            change_table_list::TRIPLE, change_field_list::FLD_TRIPLE_VIEW);


        $t->subheader('API unit tests');

        $log_lst = $t->dummy_change_log_list_named();
        $t->assert_api($log_lst);

    }

    /**
     * check the load SQL statements to get a named log entry by field row
     * for all allowed SQL database dialects
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     * @param change $log the user sandbox object e.g. a word
     */
    private function assert_sql_named_by_field_row(test_cleanup $t, sql_db $db_con, change $log): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log->load_sql_by_field_row($db_con->sql_creator(), 1, 2);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $log->load_sql_by_field_row($db_con->sql_creator(), 1, 2);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements to get a link log entry by table
     * for all allowed SQL database dialects
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     * @param change_link $log the user sandbox object e.g. a word
     */
    private function assert_sql_link_by_table(test_cleanup $t, sql_db $db_con, change_link $log): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log->load_sql_by_vars($db_con, 1);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $log->load_sql_by_vars($db_con, 1);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements to get a list of log entries by object field
     * for all allowed SQL database dialects
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     * @param change_log_list $log_lst the user sandbox object e.g. a word
     */
    private function assert_sql_list_by_obj_field(
        test_cleanup $t,
        sql_db $db_con,
        change_log_list $log_lst,
        string $table_name,
        string $field_name): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $log_lst->load_sql_obj_fld(
            $db_con->sql_creator(),
            $table_name,
            $field_name,
            1,
            $t->usr1);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $log_lst->load_sql_obj_fld(
                $db_con->sql_creator(),
                $table_name,
                $field_name,
                1,
                $t->usr1);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}
