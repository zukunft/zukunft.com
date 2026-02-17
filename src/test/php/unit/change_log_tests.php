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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_GROUP . 'group_db.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'changes_norm.php';
include_once paths::MODEL_LOG . 'changes_big.php';
include_once paths::MODEL_LOG . 'change_link.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::MODEL_WORD . 'triple_db.php';
include_once html_paths::LOG . 'user_log_display.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\group\group_db;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_value;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_prime;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_db;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_db;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class change_log_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_log = new test_log($t);
        $t_grp = new test_groups($t);
        $t_val = new test_values($t);
        $t_wrd = new test_words($t);
        $t->name = 'change_log->';
        $t->resource_path = 'db/log/';


        $ts = 'unit log ';
        $t->header($ts);

        $t->subheader($ts . 'action sql setup');
        $act = new change_action('');
        $t->assert_sql_table_create($act);
        $t->assert_sql_index_create($act);

        $t->subheader($ts . 'table sql setup');
        $tbl = new change_table('');
        $t->assert_sql_table_create($tbl);
        $t->assert_sql_index_create($tbl);

        $t->subheader($ts . 'field sql setup');
        $fld = new change_field('');
        $t->assert_sql_table_create($fld);
        $t->assert_sql_index_create($fld);
        $t->assert_sql_foreign_key_create($fld);

        $t->subheader($ts . 'table field view sql setup');
        $tbl_fld = new change_table_field();
        $t->assert_sql_view_link_create($tbl_fld);

        $t->subheader($ts . 'named sql setup');
        $log = $t_log->log_word_add();
        $t->assert_sql_table_create($log);
        $t->assert_sql_index_create($log);
        $t->assert_sql_foreign_key_create($log);
        // TODO add auto increment test for all mysql tables

        $t->subheader($ts . 'group name sql setup for values related to up to 16 phrases');
        $log = $t_log->log_norm();
        $t->assert_sql_table_create($log);
        $t->assert_sql_index_create($log);
        $t->assert_sql_foreign_key_create($log);

        $t->subheader($ts . 'group name sql setup for values related to more than 16 phrases');
        $log = $t_log->log_big();
        $t->assert_sql_table_create($log);
        $t->assert_sql_index_create($log);
        $t->assert_sql_foreign_key_create($log);

        foreach (change_log::LOG_CLASSES as $class) {
            $t->subheader($ts . '' . $lib->class_to_name($class) . ' sql setup');
            $log = $t_log->log_obj_from_class($class);
            $t->assert_sql_table_create($log);
            $t->assert_sql_index_create($log);
            $t->assert_sql_foreign_key_create($log);
        }

        $t->subheader($ts . 'link sql setup');
        $log_lnk = $t_log->log_link();
        $t->assert_sql_table_create($log_lnk);
        $t->assert_sql_index_create($log_lnk);
        $t->assert_sql_foreign_key_create($log_lnk);

        $t->subheader($ts . 'table and field sql write');
        $tbl = $t_log->log_table();
        $t->assert_sql_insert($sc, $tbl);
        $fld = $t_log->log_field();
        $t->assert_sql_insert($sc, $fld);

        $t->subheader($ts . 'named sql write');
        $log = $t_log->log_word_add();
        $t->assert_sql_insert($sc, $log);
        $t->assert_sql_insert($sc, $log, [sql_type::SUB]);
        $log = $t_log->log_word_update();
        $t->assert_sql_insert($sc, $log);
        $log = $t_log->log_word_delete();
        $t->assert_sql_insert($sc, $log);
        $log = $t_log->log_word_add_type();
        $t->assert_sql_insert($sc, $log);
        $log = $t_log->log_word_update_type();
        $t->assert_sql_insert($sc, $log);
        $log = $t_log->log_word_delete_type();
        $t->assert_sql_insert($sc, $log);
        $log = $t_log->log_norm();
        $t->assert_sql_insert($sc, $log);
        $log = $t_log->log_big();
        $t->assert_sql_insert($sc, $log);

        $t->subheader($ts . 'value sql write');
        $log_val = $t_log->log_value();
        $t->assert_sql_insert($sc, $log_val);
        $t->assert_sql_insert($sc, $log_val, [sql_type::SUB]);
        $log_val = $t_log->log_value_update();
        $t->assert_sql_insert($sc, $log_val);
        $log_val = $t_log->log_value_delete();
        $t->assert_sql_insert($sc, $log_val);
        $log_val = $t_log->log_value_prime();
        $t->assert_sql_insert($sc, $log_val);
        $t->assert_sql_insert($sc, $log_val, [sql_type::SUB]);
        $log_val = $t_log->log_value_big();
        $t->assert_sql_insert($sc, $log_val);
        $t->assert_sql_insert($sc, $log_val, [sql_type::SUB]);

        $t->subheader($ts . 'link sql write');
        $log_lnk = $t_log->log_link();
        $t->assert_sql_insert($sc, $log_lnk);
        $t->assert_sql_insert($sc, $log_lnk, [sql_type::SUB]);

        $t->subheader($ts . 'load by user');
        $log = new change($usr);
        $t->assert_sql_by_user($sc, $log);
        $log = new change_link($usr);
        $t->assert_sql_by_user($sc, $log);

        $t->subheader($ts . 'load list');
        $log_lst = new change_log_list();
        // TODO Prio 2 activate
        //$t->assert_sql_by_user($sc, $log_lst);
        //$this->assert_sql_list_last(word::class, word_db::FLD_NAME, $log_lst, $db_con, $t);
        $test_name = 'get the latest changes of an user';
        $test_name = 'get the latest 5 changes of an user';
        $test_name = 'get the second last change of an user';
        $test_name = 'get the first changes of an user';
        $test_name = 'get the latest changes related to a word';
        $this->assert_sql_list_by_field(word::class, '', 1, $log_lst, $db_con, $t, $test_name);
        $test_name = 'get the name changes of a word';
        $this->assert_sql_list_by_field(word::class, word_db::FLD_NAME, 1, $log_lst, $db_con, $t, $test_name);
        $this->assert_sql_list_by_field(triple::class, triple_db::FLD_NAME_GIVEN, 1, $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(group::class, group_db::FLD_NAME, $t_grp->group()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(group::class, group_db::FLD_NAME, $t_grp->group_16()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(group::class, group_db::FLD_NAME, $t_grp->group_17_plus()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(value::class, sandbox_multi::FLD_VALUE, $t_val->value()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(value::class, sandbox_multi::FLD_VALUE, $t_val->value_16()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(value::class, sandbox_multi::FLD_VALUE, $t_val->value_17_plus()->id(), $log_lst, $db_con, $t);

        // sql to load the word by id
        $test_name = 'user change log';
        //$log_dsp = new user_log_display();
        //$log_dsp->usr = $usr;
        //$log_dsp->type = $lib->class_to_name(user::class);
        //$log_dsp->size = sql_db::ROW_LIMIT;
        //$db_con->db_type = sql_db::POSTGRES;
        // TODO Prio 1 activate
        //$created_sql = $log_dsp->dsp_hist_links_sql($db_con);
        //$expected_sql = $t->file('db/log/change_log.sql');
        //$t->assert('user_log_display->dsp_hist_links_sql by ' . $log_dsp->type, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        //$t->assert_sql_name_unique($log_dsp->dsp_hist_links_sql($db_con, true));

        // sql to load a log entry by field and row id
        // TODO check that user-specific changes are included in the list of changes
        $log = new change($usr);
        $this->assert_sql_by_field_row($t, $db_con, $log);

        // sql to load a log entry by field and row id
        // TODO check that user-specific changes are included in the list of changes
        // TODO add tests for all value types
        $this->assert_sql_by_field_row($t, $db_con, new change_values_prime($usr));

        // sql to load a field by field name and table id
        $tbl = new change_table();
        $t->assert_sql_by_name($sc, $tbl);
        $t->assert_sql_by_code_id($sc, $tbl);

        // sql to load a field by field name and table id
        $fld = new change_field();
        $this->assert_sql_field_by_name_and_id($t, $db_con, $fld);

        // sql to load a log entry by field and row id
        $log = new change_link($usr);
        $this->assert_sql_link_by_table($t, $db_con, $log);

        $t->subheader($ts . 'sql list statement');

        // prepare the objects for the tests
        $wrd = $t_wrd->word();
        $trp = new triple($usr);
        $trp->set(triples::PI_ID, triples::PI_NAME);


        $t->subheader($ts . 'api');

        $log_lst = $t_log->log_list_named();
        $t->assert_api($log_lst);

    }

    /**
     * check the load SQL statements to get a named log entry by field row
     * for all allowed SQL database dialects
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     * @param change|change_value $log the user sandbox object e.g. a word
     */
    private function assert_sql_by_field_row(test_cleanup $t, sql_db $db_con, change|change_value $log): void
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
     * @param change_field $fld the user sandbox object e.g. a word
     */
    private function assert_sql_field_by_name_and_id(test_cleanup $t, sql_db $db_con, change_field $fld): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $fld->load_sql_by_name_and_table_id($db_con->sql_creator(), 'system_test_field_name', 1);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $fld->load_sql_by_name_and_table_id($db_con->sql_creator(), 'system_test_field_name', 1);
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
        $qp = $log->load_sql_by_table($db_con, 1);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $log->load_sql_by_table($db_con, 1);
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
    private function assert_sql_list_by_field(
        string          $class,
        string          $field_name,
        int|string      $id,
        change_log_list $log_lst,
        sql_db          $db_con,
        test_cleanup    $t,
        string          $test_name = ''
    ): void
    {
        $sc = $db_con->sql_creator();

        // check the Postgres query syntax
        $sc->db_type = sql_db::POSTGRES;
        $qp = $log_lst->load_sql_obj_fld(
            $sc,
            $class,
            $field_name,
            $id,
            $t->usr1);
        $result = $t->assert_qp($qp, $sc->db_type, $test_name);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->db_type = sql_db::MYSQL;
            $qp = $log_lst->load_sql_obj_fld(
                $sc,
                $class,
                $field_name,
                $id,
                $t->usr1);
            $t->assert_qp($qp, $sc->db_type, $test_name);
        }
    }

    /**
     * check the load SQL statements to get the last log entry
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     * @param change_log_list $log_lst the user sandbox object e.g. a word
     */
    private function assert_sql_list_last(
        string          $class,
        int|string      $id,
        change_log_list $log_lst,
        sql_db          $db_con,
        test_cleanup    $t): void
    {
        $sc = $db_con->sql_creator();

        // check the Postgres query syntax
        $sc->db_type = sql_db::POSTGRES;
        $qp = $log_lst->load_sql_obj_last(
            $sc,
            $class,
            $id,
            $t->usr1);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->db_type = sql_db::MYSQL;
            $qp = $log_lst->load_sql_obj_last(
                $sc,
                $class,
                $id,
                $t->usr1);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

}
