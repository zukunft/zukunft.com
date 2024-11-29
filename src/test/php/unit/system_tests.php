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

namespace unit;

include_once MODEL_SYSTEM_PATH . 'ip_range.php';
include_once MODEL_SYSTEM_PATH . 'ip_range_list.php';
include_once MODEL_SYSTEM_PATH . 'session.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_list.php';
include_once API_SYSTEM_PATH . 'sys_log.php';

use api\word\word as word_api;
use api\ref\ref as ref_api;
use cfg\config;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\formula;
use cfg\ip_range;
use cfg\ip_range_list;
use cfg\session;
use cfg\sys_log;
use cfg\sys_log_list;
use cfg\sys_log_status;
use cfg\sys_log_status_list;
use cfg\verb;
use controller\system\sys_log as sys_log_api;
use DateTime;
use shared\library;
use test\test_cleanup;

class system_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;
        global $sql_names;
        global $sys_log_stati;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'system->';
        $t->resource_path = 'db/system/';


        $t->header('Unit tests of objects');


        $t->subheader('Config SQL setup statements');
        $cfg = new config();
        $t->assert_sql_table_create($cfg);
        $t->assert_sql_index_create($cfg);

        $t->subheader('IP range SQL setup statements');
        $ipr = new ip_range();
        $t->assert_sql_table_create($ipr);
        $t->assert_sql_index_create($ipr);

        $t->subheader('Session SQL setup statements');
        $ses = new session();
        $t->assert_sql_table_create($ses);
        $t->assert_sql_index_create($ses);


        $t->subheader('Debug function tests');

        // create a dummy object of each object and test that the dsp_id debug function does not cause an infinite loop
        // TODO check that all objects are included in this list
        $t->assert_dsp_id($t->word(), '"Mathematics" (word_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->word_list(), '"Mathematics","constant","Pi","Euler\'s constant" (word_id 1,2,4,6) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->verb(), 'not set/not_set (verb_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->triple(), '"constant" "is part of" "Mathematics" (2,3,1 -> triple_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->triple_list(), '"Pi (math)" (triple_id 2) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->triple()->phrase(), '"constant" "is part of" "Mathematics" (2,3,1 -> triple_id 1) for user 1 (zukunft.com system test) as phrase');
        $t->assert_dsp_id($t->phrase_list(), '"Mathematical constant","Mathematics","Pi","Pi (math)","constant" (phrase_id 1,2,4,-1,-2) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->group(), '"Pi (math)" (group_id 32770) as "Pi (math)" for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->group_list(), ' ... total 1');
        $t->assert_dsp_id($t->term(), '"Mathematics" (word_id 1) for user 1 (zukunft.com system test) as term');
        $t->assert_dsp_id($t->term_list(), '"Mathematical constant","Mathematics","not set","scale minute to sec" (-2,-1,1,2)');
        $t->assert_dsp_id($t->value(), '"Pi (math)" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -2,,,) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->value_list(), '"Pi (math)" 3.1415926535898 / "inhabitant in the city of Zurich (2019)" 415367 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -2,,, / ' . word_api::TI_INHABITANT . ',' . word_api::TI_ZH . ',' . word_api::TI_2019 . ',) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->source(), '"The International System of Units" (source_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->reference(), 'ref of "Pi" to "wikidata" (' . ref_api::TI_PI . ')');
        $t->assert_dsp_id($t->formula(), '"scale minute to sec" (formula_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->formula_list(), 'scale minute to sec (formula_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->formula_link(), 'from "scale minute to sec" (formula_id 1) to "Mathematics" (word_id 1) as phrase as  (formula_link_id 1)');
        $t->assert_dsp_id($t->element(), 'word "minute" (' . word_api::TI_MINUTE . ') for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->element_list(), '"minute" (element_id ' . word_api::TI_MINUTE . ') for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->expression(), '""second" = "minute" * 60" ({w' . word_api::TI_SECOND . '}={w' . word_api::TI_MINUTE . '}*60)');
        $t->assert_dsp_id($t->result_simple(), '"Mathematics" 123456 (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = 1,,,) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->result_list(), '"Mathematics" 123456 / "percent" 0.01234 (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = 1,,, / 2,,,) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->figure_value(), 'value figure "Pi (math)" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -2,,,) for user 1 (zukunft.com system test) 2022-12-26 18:23:45');
        $t->assert_dsp_id($t->figure_list(), ' 3.1415926535898 Pi (math)  123456 "Mathematics"  (32770,-1)');
        $t->assert_dsp_id($t->view(), '"Start view" (view_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->view_list(), '"Start view","Add word" (view_id 1,3) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->component(), '"Word" (component_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->component_list(), '"Word","form field share type" (component_id 1,7) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->component_link(), 'from "Start view" (view_id 1) to "Word" (component_id 1) as (component_link_id 1) at pos 1');
        $t->assert_dsp_id($t->component_link_list(), '"Word","spreadsheet" (component_link_id 1,2) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t->language(), 'English/english (language_id 1)');
        $t->assert_dsp_id($t->change_log_named(), 'log add words,word_name Mathematics (id ) in row 1 at 2022-12-26T18:23:45+01:00');
        $t->assert_dsp_id($t->change_log_norm(), 'log add words,word_name Mathematics (id ) in row 1 at 2022-12-26T18:23:45+01:00');
        $t->assert_dsp_id($t->change_log_big(), 'log add words,word_name Mathematics (id ) in row 1 at 2022-12-26T18:23:45+01:00');
        $t->assert_dsp_id($t->change_log_list_named(), 'log add words,word_name Mathematics (id ) in row 1 at 2022-12-26T18:23:45+01:00');
        $t->assert_dsp_id($t->change_log_link(), 'user_log_link for user zukunft.com system (1) action add (1) table triples (7)');
        $t->assert_dsp_id($t->change_log_value(), 'log add values,numeric_value (-2,,,) 3.1415927');
        $t->assert_dsp_id($t->change_log_value_prime(), 'log add words,word_name  3.1415927');
        $t->assert_dsp_id($t->change_log_value_big(), 'log add words,word_name  3.1415927');
        $t->assert_dsp_id($t->sys_log(), 'system log id 1 at 2023-01-03T20:59:59+01:00 row the log text that describes the problem for the user or system admin');
        $t->assert_dsp_id($t->job(), 'base_import for id 1 (1) for user 1 (zukunft.com system)');


        $t->header('Unit tests of the system classes (src/main/php/model/system/ip_range.php)');

        $t->subheader('System function tests');
        $t->assert('default log message', log_debug(), 'unit\system_tests->run');
        $t->assert('debug log message', log_debug('additional info'), 'unit\system_tests->run: additional info');


        $t->subheader('IP filter tests');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $ip_range = new ip_range();
        $t->assert_sql_by_id($sc, $ip_range);

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
        $t->assert_true('ip_range->load_sql by id range', $result);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $ip_range->load_sql_by_vars($db_con)->sql;
        $expected_sql = $t->file('db/system/ip_range_mysql.sql');
        $t->assert('ip_range->load_sql by id for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));


        $t->subheader('ip list sql tests');

        $ip_lst = new ip_range_list();
        $t->assert_sql_by_obj_vars($db_con, $ip_lst);


        $t->subheader('user list loading sql tests');

        // check if the sql to load the complete list of all ... types is created as expected
        $sys_log_status = new sys_log_status_list();
        $t->assert_sql_all($sc, $sys_log_status);


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
        $phr_typ_cac = new phrase_types(true);
        $t->assert_sql_all($db_con, $phr_typ_cac);
        $formula_types = new formula_type_list();
        $t->assert_sql_all($db_con, $formula_types);
        $formula_link_types = new formula_link_type_list();
        $t->assert_sql_all($db_con, $formula_link_types);
        $element_types = new element_type_list();
        $t->assert_sql_all($db_con, $element_types);
        $view_types = new view_type_list();
        $t->assert_sql_all($db_con, $view_types);
        $component_types = new component_type_list();
        $t->assert_sql_all($db_con, $component_types);
        $ref_types = new ref_type_list();
        $t->assert_sql_all($db_con, $ref_types);
        $share_types = new share_type_list();
        $t->assert_sql_all($db_con, $share_types);
        $protection_types = new protection_type_list();
        $t->assert_sql_all($db_con, $protection_types);
        $job_types = new job_type_list();
        $t->assert_sql_all($db_con, $job_types);
        $change_table_list = new change_table_list();
        $t->assert_sql_all($db_con, $change_table_list);
        $change_field_list = new change_field_list();
        $t->assert_sql_all($db_con, $change_field_list);
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
        $t->assert_true('ip_range->import check', $result);


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
        $t->assert_true('ip_range->includes check', $result);

        // negative case before
        $test_ip = '66.249.64.94';
        $result = $ip_range->includes($test_ip);
        $t->assert_false('ip_range->includes check', $result);

        // negative case after
        $test_ip = '66.249.65.95';
        $result = $ip_range->includes($test_ip);
        $t->assert_false('ip_range->includes check', $result);


        /*
         * system consistency SQL creation tests
         */

        $t->subheader('System consistency tests');

        // sql to check the system consistency
        $db_con->set_class(formula::class);
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $db_con->missing_owner_sql();
        $expected_sql = $t->file('db/system/missing_owner_by_formula.sql');
        $t->assert('system_consistency->missing_owner_sql by formula', $lib->trim($qp->sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        if (!in_array($qp->name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert_true('system_consistency->missing_owner_sql by formula', $result);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $qp = $db_con->missing_owner_sql();
        $expected_sql = $t->file('db/system/missing_owner_by_formula_mysql.sql');
        $t->assert('sys_log->load_sql by id for MySQL', $lib->trim($qp->sql), $lib->trim($expected_sql));

        /*
         * database upgrade SQL creation tests
         */

        $t->subheader('Database upgrade tests');

        // sql to load by id
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $db_con->remove_prefix_sql($lib->class_to_name(verb::class), 'code_id');
        $expected_sql = $t->file('db/system/remove_prefix_by_verb_code_id.sql');
        $t->assert('database_upgrade->remove_prefix of verb code_id', $lib->trim($qp->sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        if (!in_array($qp->name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert_true('database_upgrade->remove_prefix of verb code_id name', $result);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $qp = $db_con->remove_prefix_sql($lib->class_to_name(verb::class), 'code_id');
        $expected_sql = $t->file('db/system/remove_prefix_by_verb_code_id_mysql.sql');
        $t->assert('database_upgrade->remove_prefix of verb code_id for MySQL', $lib->trim($qp->sql), $lib->trim($expected_sql));

        /*
         * system log SQL creation tests
         */

        $t->subheader('System log list tests');

        $log_lst = new sys_log_list();
        $log_lst->set_user($usr);

        // sql to load all
        $db_con->db_type = sql_db::POSTGRES;
        $log_lst->dsp_type = sys_log_list::DSP_ALL;
        $created_sql = $log_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/sys_log/sys_log_list.sql');
        $t->assert('sys_log_list->load_sql by id', $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $log_lst->load_sql($db_con)->name;
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $t->assert_true('sys_log_list->load_sql all', $result);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $log_lst->load_sql($db_con)->sql;
        $expected_sql = $t->file('db/sys_log/sys_log_list_mysql.sql');
        $t->assert('sys_log_list->load_sql by id for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));

        /*
         * system log frontend API tests
         */

        $t->subheader('System log frontend API tests');

        $log = new sys_log();
        $log->set_id(1);
        $log->log_time = new DateTime(sys_log_api::TV_TIME);
        $log->usr_name = $usr->name;
        $log->log_text = sys_log_api::TV_LOG_TEXT;
        //$log->log_trace = (new Exception)->getTraceAsString();
        $log->log_trace = sys_log_api::TV_LOG_TRACE;
        $log->function_name = sys_log_api::TV_FUNC_NAME;
        $log->solver_name = sys_log_api::TV_SOLVE_ID;
        $log->status_name = $sys_log_stati->id(sys_log_status::OPEN);
        $log_dsp = $log->get_api_obj();
        $created = $log_dsp->get_json();
        $expected = file_get_contents(PATH_TEST_FILES . 'api/system/sys_log.json');
        $t->assert('sys_log_dsp->get_json', $lib->trim_json($created), $lib->trim_json($expected));

        // html code for the system log entry for normal users
        $created = $log_dsp->get_html($usr_sys);
        $expected = file_get_contents(PATH_TEST_FILES . 'web/system/sys_log.html');
        $t->assert('sys_log_dsp->get_json', $lib->trim_html($created), $lib->trim_html($expected));

        // ... and the same for admin users
        $created = $log_dsp->get_html($usr_sys);
        $expected = file_get_contents(PATH_TEST_FILES . 'web/system/sys_log_admin.html');
        $t->assert('sys_log_dsp->get_json', $lib->trim_html($created), $lib->trim_html($expected));

        // create a second system log entry to create a list
        $log2 = new sys_log();
        $log2->set_id(2);
        $log2->log_time = new DateTime(sys_log_api::TV_TIME);
        $log2->usr_name = $usr->name;
        $log2->log_text = sys_log_api::T2_LOG_TEXT;
        //$log2->log_trace = (new Exception)->getTraceAsString();
        $log2->log_trace = sys_log_api::T2_LOG_TRACE;
        $log2->function_name = sys_log_api::T2_FUNC_NAME;
        $log2->solver_name = sys_log_api::TV_SOLVE_ID;
        $log2->status_name = $sys_log_stati->id(sys_log_status::CLOSED);

        $log_lst = new sys_log_list();
        $log_lst->add($log);
        $log_lst->add($log2);

        $log_lst_dsp = $log_lst->dsp_obj();
        $created = $log_lst_dsp->get_json();
        $expected = file_get_contents(PATH_TEST_FILES . 'api/sys_log_list/sys_log_list.json');
        $created = json_encode($t->json_remove_volatile(json_decode($created, true)));
        $t->assert('sys_log_list_dsp->get_json', $lib->trim_json($created), $lib->trim_json($expected));

        $created = $log_lst_dsp->get_html();
        $expected = file_get_contents(PATH_TEST_FILES . 'web/system/sys_log_list.html');
        $t->assert('sys_log_list_dsp->display', $lib->trim_html($created), $lib->trim_html($expected));

        $created = $log_lst_dsp->get_html_page();
        $expected = file_get_contents(PATH_TEST_FILES . 'web/system/sys_log_list_page.html');
        $t->assert('sys_log_list_dsp->display', $lib->trim_html($created), $lib->trim_html($expected));


        /*
         * SQL database link unit tests
         */

        $t->subheader('SQL database link tests');

        $db_con = new sql_db();
        $db_con->set_class(formula::class);
        $created = $db_con->count_sql();
        $expected = file_get_contents(PATH_TEST_FILES . 'db/formula/formula_count.sql');
        $t->assert_sql('sql_db->count', $created, $expected);

    }

}