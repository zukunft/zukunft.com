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
include_once paths::MODEL_LOG . 'change_log_link_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::MODEL_WORD . 'triple_db.php';
include_once html_paths::LOG . 'user_log_display.php';
include_once paths::SHARED_CONST_FIELDS . 'word_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'triple_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'group_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\group\group_db;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_link_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_value;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_prime;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_function;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_db;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_db;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\main\php\shared\const\fields\word_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\triple_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\group_fields;

class change_log_tests
{
    function run(test_cleanup $t): void
    {

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
        $log = new change($t->usr1);
        $t->assert_sql_by_user($sc, $log);
        $log = new change_link($t->usr1);
        $t->assert_sql_by_user($sc, $log);

        $t->subheader($ts . 'load list');
        $log_lst = new change_log_list();
        // TODO Prio 2 activate
        //$t->assert_sql_by_user($sc, $log_lst);
        // the last-change query must use its own prepared name (change_by_wrd_last), because the
        // all-changes query of a word (change_by_wrd) selects with one more parameter
        $this->assert_sql_list_last(word::class, 1, $log_lst, $db_con, $t);
        $test_name = 'get the latest changes of an user';
        $test_name = 'get the latest 5 changes of an user';
        $test_name = 'get the second last change of an user';
        $test_name = 'get the first changes of an user';
        $test_name = 'get the latest changes related to a word';
        $this->assert_sql_list_by_field(word::class, '', 1, $log_lst, $db_con, $t, $test_name);
        $test_name = 'get the name changes of a word';
        $this->assert_sql_list_by_field(word::class, word_fields::FLD_NAME, 1, $log_lst, $db_con, $t, $test_name);
        $this->assert_sql_list_by_field(triple::class, triple_fields::FLD_NAME_GIVEN, 1, $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(group::class, group_fields::FLD_NAME, $t_grp->group()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(group::class, group_fields::FLD_NAME, $t_grp->group_16()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(group::class, group_fields::FLD_NAME, $t_grp->group_17_plus()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(value::class, sandbox_multi::FLD_VALUE, $t_val->value()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(value::class, sandbox_multi::FLD_VALUE, $t_val->value_16()->id(), $log_lst, $db_con, $t);
        $this->assert_sql_list_by_field(value::class, sandbox_multi::FLD_VALUE, $t_val->value_17_plus()->id(), $log_lst, $db_con, $t);
        // a type row e.g. a sys log function logs to the changes table like the named objects,
        // so the test cleanup can remove the change log of a type test row via the same list load
        $this->assert_sql_list_by_field(sys_log_function::class, '', 1, $log_lst, $db_con, $t);

        // sql to load the link change history of an object (used by the default word/formula/view page)
        $t->subheader($ts . 'link change list by object');
        $cl_lst = new change_log_link_list();
        $test_name = 'sql to load the link changes of a word selects the change_links table';
        $sql_word = $cl_lst->load_sql_by_obj($db_con, word::class, 123, $t->usr1);
        $t->assert_text_contains($test_name, $sql_word, 'FROM change_links c');
        $test_name = 'the link changes of a word are selected by the from and to id';
        $t->assert_text_contains($test_name, $sql_word,
            '(c.old_from_id = 123 OR c.old_to_id = 123 OR c.new_from_id = 123 OR c.new_to_id = 123)');
        $test_name = 'the link changes of a component are selected by the to id only';
        $sql_cmp = $cl_lst->load_sql_by_obj($db_con, component::class, 45, $t->usr1);
        $t->assert_text_contains($test_name, $sql_cmp, '(c.old_to_id = 45 OR c.new_to_id = 45)');
        $test_name = 'an unknown object class creates no link change sql';
        $t->assert($test_name, $cl_lst->load_sql_by_obj($db_con, change_table::class, 1, $t->usr1), '');

        // a link change is sent to the frontend with the relevant side as old/new value
        $t->subheader($ts . 'link change api');
        $test_name = 'the new link target is sent as the new value';
        $log_lnk = $t_log->log_link();
        $log_lnk->new_text_to = word_names::MATH;
        $api = $log_lnk->api_json_array(new api_type_list([]));
        $t->assert($test_name, $api[json_fields::NEW_VALUE] ?? '', word_names::MATH);
        $test_name = 'a link change without a display text sends no new value';
        $log_empty = new change_link($t->usr1);
        $api = $log_empty->api_json_array(new api_type_list([]));
        $t->assert_true($test_name, ($api[json_fields::NEW_VALUE] ?? null) === null);

        // sql to load a log entry by field and row id
        // TODO check that user-specific changes are included in the list of changes
        $log = new change($t->usr1);
        $this->assert_sql_by_field_row($t, $db_con, $log);

        // sql to load a log entry by field and row id
        // TODO check that user-specific changes are included in the list of changes
        // TODO add tests for all value types
        $this->assert_sql_by_field_row($t, $db_con, new change_values_prime($t->usr1));

        // sql to load a field by field name and table id
        $tbl = new change_table();
        $t->assert_sql_by_name($sc, $tbl);
        $t->assert_sql_by_code_id($sc, $tbl);

        // sql to load a field by field name and table id
        $fld = new change_field();
        $this->assert_sql_field_by_name_and_id($t, $db_con, $fld);

        // sql to load a log entry by field and row id
        $log = new change_link($t->usr1);
        $this->assert_sql_link_by_table($t, $db_con, $log);

        // sql to delete the value change log of a value during the test cleanup
        $this->assert_value_change_log_del_qp($t, $db_con);

        // sql to delete the change log of already deleted test rows during the test cleanup
        $this->assert_change_log_deleted_qp($t, $db_con);

        // sql to delete the change log of a time series value during the test cleanup
        $this->assert_value_time_series_change_log_del_qp($t, $db_con);

        $t->subheader($ts . 'sql list statement');

        // prepare the objects for the tests
        $wrd = $t_wrd->word();
        $trp = new triple($t->usr1);
        $trp->set(triple_names::PI_ID, triple_names::PI_NAME);


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
     * TODO Prio 2 use sql files instead of fixed text to use the ide syntax check
     * check the sql statement creation of test_base::value_change_log_del_qp, which the test cleanup
     * uses to delete the change log of a value from one value change table
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     */
    private function assert_value_change_log_del_qp(test_cleanup $t, sql_db $db_con): void
    {
        // a norm value group has a text group id, so the delete of the change_values_norm change log
        // declares a text parameter (the change table name is resolved via the sql creator like the
        // test cleanup does in test_base::delete_value_change_log)
        $db_con->db_type = sql_db::POSTGRES;
        $sc = $db_con->sql_creator();
        $sc->set_class(change_values_norm::class);
        $tbl_norm = $sc->get_table();
        $qp = $t->value_change_log_del_qp($sc, change_values_norm::class, $tbl_norm, 'system_test_group_id');
        $test_name = 'delete value change log of a norm group (postgres)';
        $t->assert_sql($test_name, $qp->sql,
            'PREPARE change_values_norm_del_by_grp (text) AS DELETE FROM change_values_norm WHERE group_id = $1;');

        $test_name = 'delete value change log query name';
        $t->assert($test_name, $qp->name, 'change_values_norm_del_by_grp');

        $test_name = 'delete value change log passes the group id as the parameter';
        $t->assert($test_name, implode(',', $qp->par), 'system_test_group_id');

        // the same delete for mysql uses the question mark parameter (fresh creator per statement)
        $db_con->db_type = sql_db::MYSQL;
        $sc = $db_con->sql_creator();
        $sc->set_class(change_values_norm::class);
        $qp = $t->value_change_log_del_qp($sc, change_values_norm::class, $tbl_norm, 'system_test_group_id');
        $test_name = 'delete value change log of a norm group (mysql)';
        $t->assert_sql($test_name, $qp->sql,
            "PREPARE change_values_norm_del_by_grp FROM 'DELETE FROM change_values_norm WHERE group_id = ?';");

        // a prime value group has an integer group id, so the delete declares a bigint parameter and
        // targets the change_values_prime table
        $db_con->db_type = sql_db::POSTGRES;
        $sc = $db_con->sql_creator();
        $sc->set_class(change_values_prime::class);
        $tbl_prime = $sc->get_table();
        $qp = $t->value_change_log_del_qp($sc, change_values_prime::class, $tbl_prime, 1);
        $test_name = 'delete value change log of a prime group (postgres)';
        $t->assert_sql($test_name, $qp->sql,
            'PREPARE change_values_prime_del_by_grp (bigint) AS DELETE FROM change_values_prime WHERE group_id = $1;');

        // the prime delete declares a bigint parameter, not the text parameter of a norm group
        $test_name = 'the prime value change log delete declares a bigint not a text parameter';
        $t->assert_text_not_contains($test_name, $qp->sql, '(text)');
    }

    /**
     * check the sql statement creation of test_base::change_log_deleted_qp, which the test cleanup
     * uses to delete the change log of already deleted test rows by the reserved test name part
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     */
    private function assert_change_log_deleted_qp(test_cleanup $t, sql_db $db_con): void
    {
        // an already deleted test row is detected by the reserved test name part in the old or new
        // value, so the delete of the changes table declares two text like parameters
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $t->change_log_deleted_qp($db_con->sql_creator());
        $test_name = 'delete change log of deleted test rows (postgres)';
        $t->assert_sql($test_name, $qp->sql,
            'PREPARE change_del_by_test_name (text, text) AS DELETE FROM changes WHERE old_value LIKE $1 OR new_value LIKE $2;');

        $test_name = 'delete change log of deleted test rows query name';
        $t->assert($test_name, $qp->name, 'change_del_by_test_name');

        // both parameters are the reserved test name part followed by a wildcard
        $like = test_cleanup::TEST_ROW_NAME_PART . '%';
        $test_name = 'delete change log of deleted test rows passes the test name pattern as both parameters';
        $t->assert($test_name, implode(',', $qp->par), $like . ',' . $like);

        // the postgres statement uses numbered parameters, not the mysql question mark
        $test_name = 'the postgres deleted test row delete has no mysql question mark parameter';
        $t->assert_text_not_contains($test_name, $qp->sql, '?');

        // the same delete for mysql uses the question mark parameters
        $db_con->db_type = sql_db::MYSQL;
        $qp = $t->change_log_deleted_qp($db_con->sql_creator());
        $test_name = 'delete change log of deleted test rows (mysql)';
        $t->assert_sql($test_name, $qp->sql,
            "PREPARE change_del_by_test_name FROM 'DELETE FROM changes WHERE old_value LIKE ? OR new_value LIKE ?';");
    }

    /**
     * check the sql statement creation of test_base::value_time_series_change_log_del_qp, which the
     * test cleanup uses to delete the change log of a time series value from the changes table
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     */
    private function assert_value_time_series_change_log_del_qp(test_cleanup $t, sql_db $db_con): void
    {
        // a time series value logs to the changes table keyed by its value_time_series_id, so the
        // delete removes the changes of that row whose field belongs to one of the time series tables;
        // the sample table ids must be inlined into the subquery (see resources/db/log/changes_del_by_ts_id.sql)
        $tbl_ids = [1, 2];

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $t->value_time_series_change_log_del_qp($db_con->sql_creator(), 1, $tbl_ids);
        $result = $t->assert_qp($qp, $db_con->db_type);

        $test_name = 'delete time series change log passes the value time series id as the parameter';
        $t->assert($test_name, implode(',', $qp->par), '1');

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $t->value_time_series_change_log_del_qp($db_con->sql_creator(), 1, $tbl_ids);
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
