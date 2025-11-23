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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SERVICE . 'config.php';
include_once paths::MODEL_SYSTEM . 'ip_range.php';
include_once paths::MODEL_SYSTEM . 'ip_range_list.php';
include_once paths::MODEL_SYSTEM . 'session.php';
include_once paths::MODEL_SYSTEM . 'sys_log_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'sys_log_statuus.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'words.php';
include_once test_paths::CREATE . 'test_components.php';
include_once test_paths::CREATE . 'test_figures.php';
include_once test_paths::CREATE . 'test_formulas.php';
include_once test_paths::CREATE . 'test_groups.php';
include_once test_paths::CREATE . 'test_jobs.php';
include_once test_paths::CREATE . 'test_languages.php';
include_once test_paths::CREATE . 'test_log.php';
include_once test_paths::CREATE . 'test_phrases.php';
include_once test_paths::CREATE . 'test_refs.php';
include_once test_paths::CREATE . 'test_results.php';
include_once test_paths::CREATE . 'test_sources.php';
include_once test_paths::CREATE . 'test_sys_log.php';
include_once test_paths::CREATE . 'test_terms.php';
include_once test_paths::CREATE . 'test_triples.php';
include_once test_paths::CREATE . 'test_values.php';
include_once test_paths::CREATE . 'test_verbs.php';
include_once test_paths::CREATE . 'test_views.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\system\ip_range;
use Zukunft\ZukunftCom\main\php\cfg\system\ip_range_list;
use Zukunft\ZukunftCom\main\php\cfg\system\session;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\system\sys_log as sys_log_dsp;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list as sys_log_list_dsp;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuus;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_type;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_figures;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\create\test_jobs;
use Zukunft\ZukunftCom\test\php\create\test_languages;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use DateTime;

class system_tests
{

    // use path that does not need to be included
    const array PATH_NO_INCLUDE = [
        'PgSql\Connection',
        'Zukunft\ZukunftCom\main\php\cfg\const\paths',
        'Zukunft\ZukunftCom\main\php\web\const\paths',
        'Zukunft\ZukunftCom\test\php\const\paths'
    ];

    function run(test_cleanup $t): void
    {

        global $usr;
        // TODO move system user to a test object vars
        global $usr_sys;
        global $sys;
        global $mtr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'system->';
        $t->resource_path = 'db/system/';
        $t->usr_system = $t->user_system();

        // start the test section (ts)
        $ts = 'unit objects ';
        $t->header($ts);

        $t->subheader($ts . 'config SQL setup');
        $cfg = new config();
        $t->assert_sql_table_create($cfg);
        $t->assert_sql_index_create($cfg);

        $t->subheader($ts . 'ip range SQL setup');
        $ipr = new ip_range();
        $t->assert_sql_table_create($ipr);
        $t->assert_sql_index_create($ipr);

        $t->subheader($ts . 'session SQL setup');
        $ses = new session();
        $t->assert_sql_table_create($ses);
        $t->assert_sql_index_create($ses);


        $t->subheader($ts . 'debug functions');

        // create a dummy object of each object and test that the dsp_id debug function does not cause an infinite loop
        // TODO check that all objects are included in this list
        $t_wrd = new test_words($t);
        $t_vrb = new test_verbs($t);
        $t_trp = new test_triples($t);
        $t_phr = new test_phrases($t);
        $t_grp = new test_groups($t);
        $t_trm = new test_terms($t);
        $t_val = new test_values($t);
        $t_src = new test_sources($t);
        $t_ref = new test_refs($t);
        $t_frm = new test_formulas($t);
        $t_res = new test_results($t);
        $t_fig = new test_figures($t);
        $t_msk = new test_views($t);
        $t_cmp = new test_components($t);
        $t_lan = new test_languages();
        $t_log = new test_log($t);
        $t_sys = new test_sys_log();
        $t_job = new test_jobs();
        $t->assert_dsp_id($t_wrd->word(), '"mathematics" (word_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_wrd->word_list(), '"mathematics","constant","π","𝑒" (word_id 1,2,5,6) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_vrb->verb(), 'not set/not_set (verb_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_trp->triple(), '"constant" "is part of" "mathematics" (2,3,1 -> triple_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_trp->triple_list_short(), '"π (unit symbol)","global warming potential" (triple_id 1,2,100) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_trp->triple()->phrase(), '"constant" "is part of" "mathematics" (2,3,1 -> triple_id 1) for user 1 (zukunft.com system test) as phrase');
        $t->assert_dsp_id($t_phr->phrase_list_prime(), '"mathematics","constant","mathematical constant","π (unit symbol)" (phrase_id 1,2,-1,-2) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_phr->phrase_list_long(), '"mathematics","constant","π" ... total 13 (phrase_id 1,2,5,18,139,4,157,159,-1,-51,-94,-95,-96) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_grp->group(), '"Pi (math)" (group_id 32819) as "Pi (math)" for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_grp->group_list(), 'Pi (math)');
        $t->assert_dsp_id($t_grp->group_list_long(), 'Pi (math) / Zurich City inhabitants (2019) / Zurich City inhabitants (2019) in million / System Test Word Increase in Switzerland\'s inhabitants from 2019 to 2020 in percent ... total 6');
        $t->assert_dsp_id($t_trm->term(), '"mathematics" (word_id 1) for user 1 (zukunft.com system test) as term');
        $t->assert_dsp_id($t_trm->term_list_short(), '"mathematical constant","mathematics","not set","scale minute to sec" (-2,-1,1,2)');
        $t->assert_dsp_id($t_val->value(), 'Pi (math): 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -51,,,) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_val->value_list_short(), 'Pi (math): 3.1415926535898 / Zurich City inhabitants (2019): 415367 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -51,,, / 213,196,139,) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_src->source(), '"The International System of Units" (source_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_ref->reference(), 'ref of "Pi" to "wikidata" (' . refs::PI_ID . ')');
        $t->assert_dsp_id($t_frm->formula(), '"scale minute to sec" (formula_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_frm->formula_list_short(), 'scale minute to sec (formula_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_frm->formula_link(), 'from "scale minute to sec" (formula_id 1) to "minute" (word_id 104) as phrase as (formula_link_id 1)');
        $t->assert_dsp_id($t_frm->element(), 'word "minute" (' . words::MINUTE_ID . ') for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_frm->element_list(), '"minute" (element_id ' . words::MINUTE_ID . ') for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_frm->expression(), '""second" = "minute" * 60" ({w' . words::SECOND_ID . '}={w' . words::MINUTE_ID . '}*60)');
        $t->assert_dsp_id($t_res->result_simple_1(), 'mathematics: 123456 (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = 1,,,) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_res->result_list(), 'mathematics: 123456 / ' . words::PERCENT . ': 0.01234 (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = 1,,, / ' . words::PCT_ID . ',,,) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_fig->figure_value(), 'value figure Pi (math): 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -51,,,) for user 1 (zukunft.com system test) 2022-12-26 18:23:45');
        $t->assert_dsp_id($t_fig->figure_list(), ' 3.1415926535898 Pi (math)  123456 "mathematics"  (32819,-1)');
        $t->assert_dsp_id($t_msk->view(), '"Start view" (view_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_msk->view_list(), '"Start view","Add word" (view_id 1,3) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_cmp->component(), '"Word" (component_id 1) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_cmp->component_list(), '"Word","form field share type" (component_id 1,7) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_cmp->component_link(), 'from "Start view" (view_id 1) to "Word" (component_id 1) as (component_link_id 1) at pos 1');
        $t->assert_dsp_id($t_cmp->component_link_list(), '"Word","spreadsheet" (component_link_id 1,2) for user 1 (zukunft.com system test)');
        $t->assert_dsp_id($t_lan->language(), 'English/english (language_id 1)');
        $t->assert_dsp_id($t_log->log_word_add(), 'log add words,word_name mathematics (id ) in row 1 at 2022-12-26T18:23:45+01:00');
        $t->assert_dsp_id($t_log->log_norm(), 'log add words,word_name mathematics (id ) in row 1 at 2022-12-26T18:23:45+01:00');
        $t->assert_dsp_id($t_log->log_big(), 'log add words,word_name mathematics (id ) in row 1 at 2022-12-26T18:23:45+01:00');
        $t->assert_dsp_id($t_log->log_list_short(), 'log add words,word_name mathematics (id ) in row 1 at 2022-12-26T18:23:45+01:00 / log add verbs,verb_name is (id ) in row 2 at 2022-12-26T18:23:45+01:00 / log add triples,triple_name mathematical constant (id ) in row 1 at 2022-12-26T18:23:45+01:00');
        $t->assert_dsp_id($t_log->log_link(), 'user_log_link for user zukunft.com system (1) action add (1) table triples (7)');
        $t->assert_dsp_id($t_log->log_value(), 'log add values,numeric_value (-51,,,) 3.1415927');
        $t->assert_dsp_id($t_log->log_value_prime(), 'log add words,word_name  3.1415927');
        $t->assert_dsp_id($t_log->log_value_big(), 'log add words,word_name  3.1415927');
        $t->assert_dsp_id($t_sys->sys_log(), 'system log id 1 at 2023-01-03T20:59:59+01:00 row the log text that describes the problem for the user or system admin');
        $t->assert_dsp_id($t_job->job(), 'base_import for id 1 (1) for user 1 (zukunft.com system)');


        $ts = 'unit translation ';
        $t->header($ts);

        $test_name = 'show a message in the system language';
        $t->assert($test_name, $mtr->txt(msg_id::DONE), msg_id::DONE->value);
        $test_name = 'translate a message in the system language';
        $t->assert($test_name, $mtr->txt(msg_id::IS_RESERVED), msg_id::IS_RESERVED_TXT->value);
        $test_name = 'translate a message';
        $t->assert($test_name, $mtr->txt(msg_id::DONE, language_codes::DE), "erledigt");

        $t->subheader($ts . 'system function');
        $t->assert('default log message', log_debug(), 'Zukunft\ZukunftCom\test\php\unit\system_tests->run');


        $ts = 'unit system ';
        $t->header($ts);

        $t->subheader($ts . 'log');
        $t->assert('default log message', log_debug(), 'Zukunft\ZukunftCom\test\php\unit\system_tests->run');
        $t->assert('debug log message', log_debug('additional info'), 'Zukunft\ZukunftCom\test\php\unit\system_tests->run: additional info');

        $t->subheader($ts . 'def');
        $t->assert_true('word is a sandbox class', $lib->class_is_sandbox(word::class));
        $t->assert_false('user is not a sandbox class', $lib->class_is_sandbox(user::class));


        $t->subheader($ts . 'IP filter');

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
        if (!in_array($sql_name, $t->unique_sql_names)) {
            $result = true;
            $t->unique_sql_names[] = $sql_name;
        }
        $t->assert_true('ip_range->load_sql by id range', $result);

        // ... and the same for MySQL by replication the SQL builder statements
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $ip_range->load_sql_by_vars($db_con)->sql;
        $expected_sql = $t->file('db/system/ip_range_mysql.sql');
        $t->assert('ip_range->load_sql by id for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));


        $t->subheader($ts . 'ip list sql');

        $ip_lst = new ip_range_list();
        $t->assert_sql_by_obj_vars($db_con, $ip_lst);


        $t->subheader($ts . 'user list loading sql');

        // check if the sql to load the complete list of all ... types is created as expected
        $sys_log_status = new sys_log_status_list();
        $t->assert_sql_all($sc, $sys_log_status);

        $t->subheader($ts . 'user message');

        $usr_msg = new user_message();
        $test_name = 'message is translated';
        $usr_msg->add_id(msg_id::CHECK);
        $t->assert($test_name, $usr_msg->all_message_text(), msg_id::CHECK->value);


        $t->subheader($ts . 'system config sql');

        $db_con->db_type = sql_db::POSTGRES;
        $cfg = new config();
        $created_sql = $cfg->get_sql($db_con, config::VERSION_DB)->sql;
        $expected_sql = $t->file('db/system/cfg_get.sql');
        $t->assert('config->get_sql', $lib->trim($created_sql), $lib->trim($expected_sql));

        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $cfg->get_sql($db_con, config::VERSION_DB)->sql;
        $expected_sql = $t->file('db/system/cfg_get_mysql.sql');
        $t->assert('config->get_sql for MySQL', $lib->trim($created_sql), $lib->trim($expected_sql));

        $test_name = 'sql type unit tests';
        $val_typ = sql_type::INSERT;
        $t->assert_false($test_name, $val_typ->is_val_type());
        $t->assert_true($test_name, $val_typ->is_sql_change());
        $val_typ = sql_type::NUMERIC;
        $t->assert_true($test_name, $val_typ->is_val_type());
        $t->assert_false($test_name, $val_typ->is_sql_change());
        $val_typ = sql_type::LOG;
        $t->assert_false($test_name, $val_typ->is_val_type());
        $t->assert_false($test_name, $val_typ->is_sql_change());

        /*
         * these tests are probably not needed because not problem is expected
         * activate if nevertheless an issue occurs
        $system_users = new user_list();
        $t->assert_sql_all($db_con, $system_users);
        $sys->typ_lst->usr_pro = new user_profile_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->usr_pro);
        $sys->typ_lst->phr_typ = new phrase_types(true);
        $t->assert_sql_all($db_con, $sys->typ_lst->phr_typ);
        $sys->typ_lst->frm_typ = new formula_type_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->frm_typ);
        $sys->typ_lst->frm_lnk_typ = new formula_link_type_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->frm_lnk_typ);
        $sys->typ_lst->elm_typ = new element_type_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->elm_typ);
        $sys->typ_lst->msk_typ = new view_type_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->msk_typ);
        $sys->typ_lst->cmp_typ = new component_type_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->cmp_typ);
        $sys->typ_lst->ref_typ = new ref_type_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->ref_typ);
        $sys->typ_lst->shr_typ = new share_type_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->shr_typ);
        $sys->typ_lst->ptc_typ = new protection_type_list();
        $t->assert_sql_all($db_con, $sys->typ_lst->ptc_typ);
        $job_typ_cac = new job_type_list();
        $t->assert_sql_all($db_con, $job_typ_cac);
        $cng_tbl_cac = new change_table_list();
        $t->assert_sql_all($db_con, $cng_tbl_cac);
        $cng_fld_cac = new change_field_list();
        $t->assert_sql_all($db_con, $cng_fld_cac);
         */

        /*
         * im- and export tests
         */

        $t->subheader($ts . 'im- and export');

        $usr_msg = new user_message();

        $json_in = json_decode(file_get_contents(test_files::IP_BLACKLIST), true);
        $ip_range = new ip_range();
        $ip_range->set_user($usr);
        // switch to system user for import
        $usr_tmp = $usr;
        $usr = $usr_sys;
        $ip_range->import_obj($json_in, $usr_msg, new data_object($usr), $t);
        // switch back to original user
        $usr = $usr_tmp;
        $json_ex = $ip_range->export_json();
        $result = $lib->json_is_similar($json_in, $json_ex);
        $t->assert_true('ip_range->import check', $result);


        /*
         * ip range tests
         */

        $t->subheader($ts . 'ip range');


        $json_in = json_decode(file_get_contents(test_files::IP_BLACKLIST), true);
        $ip_range = new ip_range();
        $ip_range->set_user($usr);
        // switch to system user for import
        $usr_tmp = $usr;
        $usr = $usr_sys;
        $ip_range->import_obj($json_in, $usr_msg, new data_object($usr), $t);
        // switch back to original user
        $usr = $usr_tmp;
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

        $t->subheader($ts . 'system consistency');

        // sql to check the system consistency
        $db_con->set_class(formula::class);
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $db_con->missing_owner_sql();
        $expected_sql = $t->file('db/system/missing_owner_by_formula.sql');
        $t->assert('system_consistency->missing_owner_sql by formula', $lib->trim($qp->sql), $lib->trim($expected_sql));

        $test_name = 'check that the docs with all objects is updated';
        $md_txt = $this->php_class_tree();
        $doc_txt = file_get_contents(test_files::DOCS_OBJECTS);
        $t->assert($test_name, $md_txt, $doc_txt);

        $this->php_include_tests($t, paths::MODEL);
        // TODO Prio 1 activate but take into account the const
        //$this->php_include_tests($t, paths::API);
        $this->php_include_tests($t, paths::WEB);
        $this->php_include_tests($t, test_paths::CREATE);
        $this->php_class_section_tests($t, paths::MODEL_COMPONENT);

        // ... and check if the prepared sql name is unique
        if (!in_array($qp->name, $t->unique_sql_names)) {
            $result = true;
            $t->unique_sql_names[] = $sql_name;
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

        $t->subheader($ts . 'database upgrade');

        // sql to load by id
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $db_con->remove_prefix_sql($lib->class_to_name(verb::class), 'code_id');
        $expected_sql = $t->file('db/system/remove_prefix_by_verb_code_id.sql');
        $t->assert('database_upgrade->remove_prefix of verb code_id', $lib->trim($qp->sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        if (!in_array($qp->name, $t->unique_sql_names)) {
            $result = true;
            $t->unique_sql_names[] = $sql_name;
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

        $t->subheader($ts . 'system log list');

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
        if (!in_array($sql_name, $t->unique_sql_names)) {
            $result = true;
            $t->unique_sql_names[] = $sql_name;
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

        $t->subheader($ts . 'system log frontend API');

        $t_sys = new test_sys_log();
        $log = $t_sys->sys_log();
        $api_msg = $log->api_json();
        $log_dsp = new sys_log_dsp($api_msg);
        $created = $log_dsp->api_json();
        $expected = file_get_contents(test_files::SYS_LOG);
        $t->assert('sys_log_dsp->get_json', $lib->trim_json($created), $lib->trim_json($expected));

        // html code for the system log entry for normal users
        $created = $log_dsp->display();
        $expected = file_get_contents(test_files::SYS_LOG_HTML);
        $t->assert('sys_log_dsp->get_json', $lib->trim_html($created), $lib->trim_html($expected));

        // ... and the same for admin users
        $usr_sys_dsp = new user($usr_sys->api_json());
        $created = $log_dsp->display_admin($usr_sys_dsp);
        $expected = file_get_contents(test_files::SYS_LOG_ADMIN);
        $t->assert('sys_log_dsp->get_json', $lib->trim_html($created), $lib->trim_html($expected));

        // create a second system log entry to create a list
        $log2 = new sys_log();
        $log2->id = 2;
        $log2->log_time = new DateTime(sys_log_tests::TV_TIME);
        $log2->usr_name = $usr->name;
        $log2->log_text = sys_log_tests::T2_LOG_TEXT;
        //$log2->log_trace = (new Exception)->getTraceAsString();
        $log2->log_trace = sys_log_tests::T2_LOG_TRACE;
        $log2->function_name = sys_log_tests::T2_FUNC_NAME;
        $log2->solver_name = sys_log_tests::TV_SOLVE_ID;
        $log2->status_id = $sys->typ_lst->sys_log_sta->id(sys_log_statuus::CLOSED);

        $log_lst = new sys_log_list();
        $log_lst->add($log);
        $log_lst->add($log2);

        $log_lst_dsp = new sys_log_list_dsp($log_lst->api_json());
        $usr1_dsp = new user($t->usr1->api_json());
        $created = $log_lst_dsp->api_json([api_type::HEADER], $usr1_dsp);
        $expected = file_get_contents(test_files::SYS_LOG_LIST_API);
        $created = json_encode($t->json_remove_volatile(json_decode($created, true)));
        $t->assert('sys_log_list_dsp->get_json', $lib->trim_json($created), $lib->trim_json($expected));

        $created = $log_lst_dsp->get_html($usr1_dsp);
        $expected = file_get_contents(test_files::SYS_LOG_LIST_HTML);
        $t->assert('sys_log_list_dsp->display', $lib->trim_html($created), $lib->trim_html($expected));

        $created = $log_lst_dsp->get_html_page($usr1_dsp);
        $expected = file_get_contents(test_files::SYS_LOG_LIST_PAGE);
        $t->assert('sys_log_list_dsp->display', $lib->trim_html($created), $lib->trim_html($expected));

    }

    function php_class_tree(): string
    {
        $test_name = 'c';
        $class_lst = [];
        $class_lst = array_merge($class_lst, $this->php_classes(paths::MODEL, paths::MODEL_SECTION));
        $class_lst = array_merge($class_lst, $this->php_classes(paths::SHARED, paths::SHARED_SECTION));
        $class_lst = array_merge($class_lst, $this->php_classes(paths::WEB, paths::WEB_SECTION));
        $class_tree = $this->classTree($class_lst);
        $class_parents = $this->classTreeParents($class_lst);
        return $this->php_class_list_to_md($class_tree);
    }

    private function php_class_list_to_md(array $class_tree): string
    {
        $md_txt = '# Objects' . "\n";
        $md_txt .= "\n";
        $md_txt .= '## Object structure' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'the object structure is:' . "\n";
        $md_txt .= "\n";
        $md_txt .= '```' . "\n";
        $md_txt .= $this->php_class_list_to_md_row($class_tree);
        $md_txt .= '```' . "\n";
        return $md_txt;
    }

    private function php_class_list_to_md_row(array $class_tree, string $intent = '+-- '): string
    {
        $md_txt = '';
        foreach ($class_tree as $child => $info_lst) {
            if (is_string($info_lst)) {
                $md_txt .= $intent . $child . ' - ' . $info_lst . "\n";
            } else {
                if ($intent == '+-- ') {
                    $this_intent = '\-- ';
                    $next_intent = '    ' . $this_intent;
                } else {
                    $this_intent = $intent;
                    $next_intent = '    ' . $intent;
                }
                $md_txt .= $this_intent . $child . "\n";
                $md_txt .= $this->php_class_list_to_md_row($info_lst, $next_intent);
            }
        }
        return $md_txt;
    }

    private function php_classes(string $path, string $section): array
    {
        $lib = new library();
        $file_array = $lib->dir_to_array($path);
        $code_files = $lib->array_to_path($file_array);
        $class_lst = [];
        // create parent child class list upfront for a complete check
        foreach ($code_files as $code_file) {
            $file_path = str_replace('//','/', $path . $code_file);
            $ctrl_code = file($path . $code_file);
            $class_info = $lib->php_code_parent($ctrl_code, $section, $file_path);
            if ($class_info != []) {
                $class_lst = array_merge($class_lst, $class_info);
            }
        }
        return $class_lst;
    }

    /**
     * check if all used classes are also included once within the same file
     * TODO add a child parent list and make sure that a parent never includes a child object
     *      but the child always includes the parent
     *      and make sure that all not needed deactivated includes are removed
     *
     * @param test_cleanup $t
     * @param string $base_path path name of the folder with the php scripts that should be checked
     * @return void
     */
    function php_include_tests(test_cleanup $t, string $base_path): void
    {
        $lib = new library();
        $file_array = $lib->dir_to_array($base_path);
        $code_files = $lib->array_to_path($file_array);
        $pos = 1;
        foreach ($code_files as $code_file) {
            log_debug($code_file);
            $ctrl_code = file($base_path . $code_file);
            $use_classes = $lib->php_code_use($ctrl_code);
            // the use code lines sorted by name for copy and paste to code
            $use_sorted = implode("\n", $lib->php_code_use_sorted($ctrl_code));
            // the include code lines sorted by name for copy and paste to code
            $use_converted = implode("\n", $lib->php_code_use_converted($ctrl_code));
            $include_classes = $lib->php_code_include($ctrl_code);
            foreach ($use_classes as $use) {
                $class = $use[0];
                $path = $use[1];
                if ($path != '') {
                    $found = false;
                    foreach ($include_classes as $include) {
                        $class_incl = $include[0];
                        $path_incl = $include[1];
                        if ($class == $class_incl) {
                            $path_conv = $lib->php_path_convert($path);
                            if ($path_conv == $path_incl or $path_conv == '') {
                                $found = true;
                            }
                        }
                    }
                    if (!$found) {
                        if (!in_array($path . '\\' . $class, self::PATH_NO_INCLUDE)) {
                            $sub_path = $lib->str_right_of($base_path, '../');
                            $test_name = 'includes missing in ' . $path . '\\' . $class
                                . ' in ' . $sub_path . $code_file
                                . ' (' . $pos . ' of ' . count($code_files) . ')';
                            $t->assert($test_name, '', $class);
                        }
                    }
                } else {
                    log_debug($class . ' is expected to be a PHP default library');
                }
            }
            $pos++;
        }
    }

    private function classTree(array $map): array
    {
        $root = [];
        foreach ($map as $child => $info_lst) {
            $parent = $info_lst[0];
            if ($parent == '') {
                $root[$child] = $info_lst;
            }
        }
        $tree = [];
        foreach ($root as $parent => $info_lst) {
            $description = $info_lst[2];
            $children = $this->classTreeChildren($map, $parent);
            if (count($children) == 0) {
                $tree[$parent] = $description;
            } else {
                $tree[$parent] = $children;
            }
        }
        return $tree;
    }

    private function classTreeChildren(
        array  $map,
        string $opa
    ): array|string
    {
        $children = [];
        foreach ($map as $child => $info_lst) {
            $parent = $info_lst[0];
            $description = $info_lst[2];
            if ($opa == $parent) {
                $grants = $this->classTreeChildren($map, $child);
                if (count($grants) == 0) {
                    $children[$child] = $description;
                } else {
                    $children[$child] = $grants;
                }
            }
        }
        return $children;
    }

    private function classTreeParents(array $map): array
    {
        $lst = [];
        foreach ($map as $child => $info_lst) {
            $parent = $info_lst[0];
            if ($parent == '') {
                $lst[$child] = $parent;
            }
        }
        $tree = [];
        foreach ($lst as $class => $info_lst) {
            if (is_array($info_lst)) {
                $parent = $info_lst[0];
            } else {
                $parent = $info_lst;
            }
            $tree = array_merge($tree, $this->classTreeGrants($map, $class, $parent, []));
        }
        return $tree;
    }

    private function classTreeGrants(
        array  $map,
        string $class,
        string $parent,
        array  $tree
    ): array|string
    {
        if ($parent == '') {
            // if it does not have a parent just add it to the list if not yet done
            if (!in_array($class, $tree)) {
                $tree[$class] = '';
            }
        } else {
            // if it has an opa add the family tree
            if (array_key_exists($parent, $map)) {
                $opa = $map[$parent];
                $tree[$class] = $this->classTreeGrants($map, $parent, $opa, $tree);
            } else {
                if (!in_array($class, $tree)) {
                    $tree[$class] = $parent;
                }
            }
        }
        return $tree;
    }

    /**
     * check if the functions in the classes are grouped by sections
     * if the sections are in the same order
     * and if the sections are described in the class header
     * TODO check that all sections have a description in the header
     * TODO check that the sections match the order in the header
     * TODO check that the header section list match the general order
     * TODO check that no function is in an unexpected section
     *
     * @param test_cleanup $t
     * @param string $base_path path name of the folder with the php scripts that should be checked
     * @return void
     */
    function php_class_section_tests(test_cleanup $t, string $base_path): void
    {
        $lib = new library();
        $file_array = $lib->dir_to_array($base_path);
        $code_files = $lib->array_to_path($file_array);
        // loop over the code files
        foreach ($code_files as $code_file) {
            log_debug($code_file);
            $ctrl_code = file($base_path . $code_file);
            $function_section_names = $lib->php_code_function($ctrl_code);
            // check the mandatory function are in the correct section
            foreach ($function_section_names as $function_section_name) {
                $function_name = $function_section_name[0];
                $section_name = $function_section_name[1];
                $section_expected = $lib->php_expected_function_section($function_name);
                // if a class has more than 100 lines the functions should be grouped in sections
                if (count($ctrl_code) > 100) {
                    if ($section_name == '' and $function_name != '') {
                        log_err('section for function ' . $function_name . ' missing');
                    }
                    // check if the function is in the expected section
                    if ($section_name != $section_expected) {
                        if ($section_expected == '') {
                            if ($section_name != '') {
                                log_warning('section for function ' . $function_name
                                    . ' not yet defined that it should be ' . $section_name . ' in ' . $code_file);
                            } else {
                                log_err('section for function ' . $function_name
                                    . ' not yet defined' . ' in ' . $code_file);
                            }
                        } else {
                            log_err('section for function ' . $function_name
                                . ' is expected to be ' . $section_expected . ' in ' . $code_file);
                        }
                    }
                }
            }
        }
    }

}