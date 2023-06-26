<?php

/*

    test/unit/system.php - unit testing of the system functions
    -------------------

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

include_once MODEL_SYSTEM_PATH . 'ip_range.php';
include_once MODEL_SYSTEM_PATH . 'ip_range_list.php';
include_once MODEL_LOG_PATH . 'system_log_list.php';
include_once API_LOG_PATH . 'system_log.php';

use cfg\config;
use controller\log\system_log_api;
use DateTime;
use cfg\ip_range;
use cfg\ip_range_list;
use cfg\library;
use cfg\sql_db;
use cfg\sys_log_status;
use cfg\system_log;
use cfg\system_log_list;

class system_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $sql_names;
        global $sys_log_stati;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'system->';
        $t->resource_path = 'db/system/';
        $usr->set_id(1);


        $t->header('Unit tests of the system classes (src/main/php/model/system/ip_range.php)');

        $t->subheader('System function tests');
        $t->assert('default log message', log_debug(), 'test\system_unit_tests->run');
        $t->assert('debug log message', log_debug('additional info'), 'test\system_unit_tests->run: additional info');


        $t->subheader('IP filter tests');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $ip_range = new ip_range();
        $t->assert_sql_by_id($db_con, $ip_range);

        // sql to load by ip range
        $db_con->db_type = sql_db::POSTGRES;
        $ip_range->reset();
        $ip_range->from = '66.249.64.95';
        $ip_range->to = '66.249.64.95';
        $ip_range->set_user($usr);
        $created_sql = $ip_range->load_sql_by_vars($db_con)->sql;
        $expected_sql = $t->file('db/system/ip_range.sql');
        $t->assert('ip_range->load_sql by ip range', $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $ip_range->load_sql_by_vars($db_con)->name;
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert('ip_range->load_sql by id range', $result, true);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $ip_range->load_sql_by_vars($db_con)->sql;
        $expected_sql = $t->file('db/system/ip_range_mysql.sql');
        $t->assert('ip_range->load_sql by id for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));


        $t->subheader('ip list sql tests');

        $ip_lst = new ip_range_list();
        $t->assert_load_sql_obj_vars($db_con, $ip_lst);


        $t->subheader('user list loading sql tests');

        // check if the sql to load the complete list of all ... types is created as expected
        $sys_log_status = new sys_log_status();
        $t->assert_sql_all($db_con, $sys_log_status);


        $t->subheader('system config sql tests');

        $db_con->db_type = sql_db::POSTGRES;
        $cfg = new config();
        $created_sql = $cfg->get_sql($db_con, config::VERSION_DB)->sql;
        $expected_sql = $t->file('db/system/cfg_get.sql');
        $t->assert('config->get_sql', $lib->trim($created_sql), $lib->trim($expected_sql));

        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $cfg->get_sql($db_con, config::VERSION_DB)->sql;
        $expected_sql = $t->file('db/system/cfg_get_mysql.sql');
        $t->assert('config->get_sql for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));

        /*
         * these tests are probably not needed because not problem is expected
         * activate if nevertheless an issue occurs
        $system_users = new user_list();
        $t->assert_sql_all($db_con, $system_users);
        $user_profiles = new user_profile_list();
        $t->assert_sql_all($db_con, $user_profiles);
        $word_types = new word_type_list();
        $t->assert_sql_all($db_con, $word_types);
        $formula_types = new formula_type_list();
        $t->assert_sql_all($db_con, $formula_types);
        $formula_link_types = new formula_link_type_list();
        $t->assert_sql_all($db_con, $formula_link_types);
        $formula_element_types = new formula_element_type_list();
        $t->assert_sql_all($db_con, $formula_element_types);
        $view_types = new view_type_list();
        $t->assert_sql_all($db_con, $view_types);
        $component_types = new view_cmp_type_list();
        $t->assert_sql_all($db_con, $component_types);
        $ref_types = new ref_type_list();
        $t->assert_sql_all($db_con, $ref_types);
        $share_types = new share_type_list();
        $t->assert_sql_all($db_con, $share_types);
        $protection_types = new protection_type_list();
        $t->assert_sql_all($db_con, $protection_types);
        $job_types = new job_type_list();
        $t->assert_sql_all($db_con, $job_types);
        $change_log_tables = new change_log_table();
        $t->assert_sql_all($db_con, $change_log_tables);
        $change_log_fields = new change_log_field();
        $t->assert_sql_all($db_con, $change_log_fields);
         */

        /*
         * im- and export tests
         */

        $t->subheader('Im- and Export tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_FILES . 'unit/system/ip_blacklist.json'), true);
        $ip_range = new ip_range();
        $ip_range->set_user($usr);
        $ip_range->import_obj($json_in, $t);
        $json_ex = json_decode(json_encode($ip_range->export_obj()), true);
        $result = $lib->json_is_similar($json_in, $json_ex);
        $t->assert('ip_range->import check', $result, true);


        /*
         * ip range tests
         */

        $t->subheader('ip range tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_FILES . 'unit/system/ip_blacklist.json'), true);
        $ip_range = new ip_range();
        $ip_range->set_user($usr);
        $ip_range->import_obj($json_in, $t);
        $test_ip = '66.249.64.95';
        $result = $ip_range->includes($test_ip);
        $t->assert('ip_range->includes check', $result, true);

        // negative case before
        $test_ip = '66.249.64.94';
        $result = $ip_range->includes($test_ip);
        $t->assert('ip_range->includes check', $result, false);

        // negative case after
        $test_ip = '66.249.65.95';
        $result = $ip_range->includes($test_ip);
        $t->assert('ip_range->includes check', $result, false);


        /*
         * system consistency SQL creation tests
         */

        $t->subheader('System consistency tests');

        // sql to check the system consistency
        $db_con->set_type(sql_db::TBL_FORMULA);
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $db_con->missing_owner_sql();
        $expected_sql = $t->file('db/system/missing_owner_by_formula.sql');
        $t->assert('system_consistency->missing_owner_sql by formula', $lib->trim($qp->sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        if (!in_array($qp->name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert('system_consistency->missing_owner_sql by formula', $result, true);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $qp = $db_con->missing_owner_sql();
        $expected_sql = $t->file('db/system/missing_owner_by_formula_mysql.sql');
        $t->assert('system_log->load_sql by id for MySQL', $lib->trim($qp->sql), $lib->trim($expected_sql));

        /*
         * database upgrade SQL creation tests
         */

        $t->subheader('Database upgrade tests');

        // sql to load by id
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $db_con->remove_prefix_sql(sql_db::TBL_VERB, 'code_id');
        $expected_sql = $t->file('db/system/remove_prefix_by_verb_code_id.sql');
        $t->assert('database_upgrade->remove_prefix of verb code_id', $lib->trim($qp->sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        if (!in_array($qp->name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert('database_upgrade->remove_prefix of verb code_id name', $result, true);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $qp = $db_con->remove_prefix_sql(sql_db::TBL_VERB, 'code_id');
        $expected_sql = $t->file('db/system/remove_prefix_by_verb_code_id_mysql.sql');
        $t->assert('database_upgrade->remove_prefix of verb code_id for MySQL', $lib->trim($qp->sql), $lib->trim($expected_sql));

        /*
         * system log SQL creation tests
         */

        $t->subheader('System log list tests');

        $log_lst = new system_log_list();
        $log_lst->set_user($usr);

        // sql to load all
        $db_con->db_type = sql_db::POSTGRES;
        $log_lst->dsp_type = system_log_list::DSP_ALL;
        $created_sql = $log_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/system_log/system_log_list.sql');
        $t->assert('system_log_list->load_sql by id', $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $log_lst->load_sql($db_con)->name;
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert('system_log_list->load_sql all', $result, true);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $log_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/system_log/system_log_list_mysql.sql');
        $t->assert('system_log_list->load_sql by id for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));

        /*
         * system log frontend API tests
         */

        $t->subheader('System log frontend API tests');

        $log = new system_log();
        $log->set_id(1);
        $log->log_time = new DateTime(system_log_api::TV_TIME);
        $log->usr_name = $usr->name;
        $log->log_text = system_log_api::TV_LOG_TEXT;
        //$log->log_trace = (new Exception)->getTraceAsString();
        $log->log_trace = system_log_api::TV_LOG_TRACE;
        $log->function_name = system_log_api::TV_FUNC_NAME;
        $log->solver_name = system_log_api::TV_SOLVE_ID;
        $log->status_name = $sys_log_stati->id(sys_log_status::OPEN);
        $log_dsp = $log->get_api_obj();
        $created = $log_dsp->get_json();
        $expected = file_get_contents(PATH_TEST_FILES . 'api/system/system_log.json');
        $t->assert('system_log_dsp->get_json', $lib->trim_json($created), $lib->trim_json($expected));

        $created = $log_dsp->get_html($usr);
        $expected = file_get_contents(PATH_TEST_FILES . 'web/system/system_log.html');
        $t->assert('system_log_dsp->get_json', $lib->trim_html($created), $lib->trim_html($expected));

        // create a second system log entry to create a list
        $log2 = new system_log();
        $log2->set_id(2);
        $log2->log_time = new DateTime(system_log_api::TV_TIME);
        $log2->usr_name = $usr->name;
        $log2->log_text = system_log_api::T2_LOG_TEXT;
        //$log2->log_trace = (new Exception)->getTraceAsString();
        $log2->log_trace = system_log_api::T2_LOG_TRACE;
        $log2->function_name = system_log_api::T2_FUNC_NAME;
        $log2->solver_name = system_log_api::TV_SOLVE_ID;
        $log2->status_name = $sys_log_stati->id(sys_log_status::CLOSED);

        $log_lst = new system_log_list();
        $log_lst->add($log);
        $log_lst->add($log2);

        $log_lst_dsp = $log_lst->dsp_obj();
        $created = $log_lst_dsp->get_json();
        $expected = file_get_contents(PATH_TEST_FILES . 'api/system_log_list/system_log_list.json');
        $created = json_encode($t->json_remove_volatile(json_decode($created, true)));
        $t->assert('system_log_list_dsp->get_json', $lib->trim_json($created), $lib->trim_json($expected));

        $created = $log_lst_dsp->get_html();
        $expected = file_get_contents(PATH_TEST_FILES . 'web/system/system_log_list.html');
        $t->assert('system_log_list_dsp->display', $lib->trim_html($created), $lib->trim_html($expected));

        $created = $log_lst_dsp->get_html_page();
        $expected = file_get_contents(PATH_TEST_FILES . 'web/system/system_log_list_page.html');
        $t->assert('system_log_list_dsp->display', $lib->trim_html($created), $lib->trim_html($expected));


        /*
         * SQL database link unit tests
         */

        $t->subheader('SQL database link tests');

        $db_con = new sql_db();
        $db_con->set_type(sql_db::TBL_FORMULA);
        $created = $db_con->count_sql();
        $expected = file_get_contents(PATH_TEST_FILES . 'db/formula/formula_count.sql');
        $t->assert_sql('sql_db->count', $created, $expected);

    }

}