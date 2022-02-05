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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class system_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $db_con = new sql_db();
        // TODO move to __construct of unit test
        if ($usr->name == null) {
            $usr->name = user::SYSTEM_TEST;
        }
        if ($usr->profile_id == null) {
            $usr->profile_id = cl(db_cl::USER_PROFILE, user_profile::NORMAL);
        }

        $t->header('Unit tests of the system classes (src/main/php/model/system/ip_range.php)');

        $t->subheader('IP filter tests');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $ip_range = new ip_range();

        // sql to load by id
        $db_con->db_type = sql_db::POSTGRES;
        $ip_range->id = 1;
        $ip_range->usr = $usr;
        $created_sql = $ip_range->load_sql($db_con);
        $expected_sql = $t->file('db/system/ip_blocked.sql');
        $t->assert('ip_range->load_sql by id', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $ip_range->load_sql($db_con, true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert('ip_range->load_sql by id', $result, true);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $ip_range->load_sql($db_con);
        $expected_sql = $t->file('db/system/ip_blocked_mysql.sql');
        $t->assert('ip_range->load_sql by id for MySQL', $t->trim($created_sql), $t->trim($expected_sql));

        // sql to load by ip range
        $db_con->db_type = sql_db::POSTGRES;
        $ip_range->reset();
        $ip_range->from = '66.249.64.95';
        $ip_range->to = '66.249.64.95';
        $ip_range->usr = $usr;
        $created_sql = $ip_range->load_sql($db_con);
        $expected_sql = $t->file('db/system/ip_range.sql');
        $t->assert('ip_range->load_sql by ip range', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $ip_range->load_sql($db_con, true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert('ip_range->load_sql by id range', $result, true);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $ip_range->load_sql($db_con);
        $expected_sql = $t->file('db/system/ip_range_mysql.sql');
        $t->assert('ip_range->load_sql by id for MySQL', $t->trim($created_sql), $t->trim($expected_sql));

        /*
         * im- and export tests
         */

        $t->subheader('Im- and Export tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/system/ip_blacklist.json'), true);
        $ip_range = new ip_range();
        $ip_range->usr = $usr;
        $ip_range->import_obj($json_in, false);
        $json_ex = json_decode(json_encode($ip_range->export_obj()), true);
        $result = json_is_similar($json_in, $json_ex);
        $t->assert('ip_range->import check', $result, true);

        /*
         * system log SQL creation tests
         */

        $t->subheader('System log tests');

        $log = new system_error_log();

        // sql to load by id
        $db_con->db_type = sql_db::POSTGRES;
        $log->id = 1;
        $created_sql = $log->load_sql($db_con);
        $expected_sql = $t->file('db/system/error_log.sql');
        $t->assert('system_error_log->load_sql by id', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $log->load_sql($db_con, true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert('system_error_log->load_sql by id', $result, true);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $log->load_sql($db_con);
        $expected_sql = $t->file('db/system/error_log_mysql.sql');
        $t->assert('system_error_log->load_sql by id for MySQL', $t->trim($created_sql), $t->trim($expected_sql));

        $t->subheader('System log list tests');

        $log_lst = new system_error_log_list();

        // sql to load all
        $db_con->db_type = sql_db::POSTGRES;
        $log_lst->dsp_type = system_error_log_list::DSP_ALL;
        $created_sql = $log_lst->load_sql($db_con);
        $expected_sql = $t->file('db/system/error_log_list.sql');
        $t->assert('system_error_log_list->load_sql by id', $t->trim($created_sql), $t->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $log_lst->load_sql($db_con, true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert('system_error_log_list->load_sql all', $result, true);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $log_lst->load_sql($db_con);
        $expected_sql = $t->file('db/system/error_log_list_mysql.sql');
        $t->assert('system_error_log_list->load_sql by id for MySQL', $t->trim($created_sql), $t->trim($expected_sql));

        /*
         * system log frontend API tests
         */

        $t->subheader('System log frontend API tests');

        $log = new system_error_log();
        $log->id = 1;
        $log->log_time = new DateTime('2021-11-27 08:15:25');
        $log->usr_name = $usr->name;
        $log->log_text = 'system test text';
        //$log->log_trace = (new Exception)->getTraceAsString();
        $log->function_name = 'system test';
        $log->solver_name = $usr->name;
        $log->status_name =  cl(db_cl::LOG_STATUS, sys_log_status::NEW);
        $log_dsp = $log->get_dsp_obj();
        $created = $log_dsp->get_json();
        $expected = file_get_contents(PATH_TEST_IMPORT_FILES . 'api/system/error_log.json');
        $t->assert('system_error_log_dsp->get_json', $t->trim_json($created), $t->trim_json($expected));

        $created = $log_dsp->get_html($usr, '');
        $expected = file_get_contents(PATH_TEST_IMPORT_FILES . 'web/system/error_log.html');
        $t->assert('system_error_log_dsp->get_json', $t->trim_html($created), $t->trim_html($expected));

        // create a second system log entry to create a list
        $log2 = new system_error_log();
        $log2->id = 2;
        $log2->log_time = new DateTime('2021-11-27 12:49:34');
        $log2->usr_name = $usr->name;
        $log2->log_text = 'system test text 2';
        //$log2->log_trace = (new Exception)->getTraceAsString();
        $log2->function_name = 'system test 2';
        $log2->solver_name = $usr->name;
        $log2->status_name =  cl(db_cl::LOG_STATUS, sys_log_status::CLOSED);

        $log_lst = new system_error_log_list();
        $log_lst->add($log);
        $log_lst->add($log2);

        $log_lst_dsp = $log_lst->get_dsp_obj();
        $created = $log_lst_dsp->get_json();
        $expected = file_get_contents(PATH_TEST_IMPORT_FILES . 'api/system/error_log_list.json');
        $t->assert('system_error_log_list_dsp->get_json', $t->trim_json($created), $t->trim_json($expected));

        $created = $log_lst_dsp->get_html($usr, '');
        $expected = file_get_contents(PATH_TEST_IMPORT_FILES . 'web/system/error_log_list.html');
        $t->assert('system_error_log_list_dsp->display', $t->trim_html($created), $t->trim_html($expected));

        $created = $log_lst_dsp->get_html_page($usr, '');
        $expected = file_get_contents(PATH_TEST_IMPORT_FILES . 'web/system/error_log_list_page.html');
        $t->assert('system_error_log_list_dsp->display', $t->trim_html($created), $t->trim_html($expected));


        /*
         * SQL database link unit tests
         */

        $t->subheader('SQL database link tests');

        $db_con = new sql_db();
        $db_con->set_type(DB_TYPE_FORMULA);
        $created = $db_con->count_sql();
        $expected = file_get_contents(PATH_TEST_IMPORT_FILES . 'db/formula/formula_count.sql');
        $t->assert_sql('sql_db->count', $created, $expected);

    }

}