<?php

/*

    test/unit/user.php - unit testing of the user functions
    ------------------


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

use DateTime;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_log;
use Zukunft\ZukunftCom\main\php\cfg\user\user_log_sum_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile_list;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_types;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class user_tests
{

    function run(test_cleanup $t): void
    {
        global $sys;

        $msg = new user_message();

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_usr = new test_users($t);
        $t_val = new test_values($t);
        $t->name = 'user->';
        $t->resource_path = 'db/user/';
        $t->usr_admin = $t_usr->user_sys_admin();


        // start the test section (ts)
        $ts = 'unit user ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $usr_test = new user();
        $t->assert_sql_table_create($usr_test);
        $t->assert_sql_index_create($usr_test);
        $t->assert_sql_foreign_key_create($usr_test);

        $t->subheader($ts . 'log sql setup');
        $usr_log = new user_log();
        $t->assert_sql_table_create($usr_log);
        $t->assert_sql_index_create($usr_log);
        $t->assert_sql_foreign_key_create($usr_log);

        $t->subheader($ts . 'log sql');
        $usr_log = $t_usr->user_log();
        $t->assert_sql_insert($sc, $usr_log);
        // the counts of a request are added to the database values, so a request written at the same time is not lost
        $usr_log_db = $t_usr->user_log_open();
        $usr_log = clone $usr_log_db;
        $start = (float)strtotime(test_users::TV_USER_LOG_START) + 1;
        $usr_log->count_request(users::SYSTEM_TEST_ID, $start, $start + test_users::TV_USER_LOG_SEC, true);
        $t->assert_sql_update($sc, $usr_log, $usr_log_db);
        $usr_log = new user_log();
        $since = new DateTime(test_users::TV_USER_LOG_START);
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_log->load_sql_open($sc, users::SYSTEM_TEST_ID, $since);
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $qp = $usr_log->load_sql_open($sc, users::SYSTEM_TEST_ID, $since);
        $t->assert_qp($qp, $sc->db_type);
        // the oldest sessions are moved to the archive and removed without change log
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_log->load_sql_old($sc, $since, user_log::ARCHIVE_LIMIT);
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $qp = $usr_log->load_sql_old($sc, $since, user_log::ARCHIVE_LIMIT);
        $t->assert_qp($qp, $sc->db_type);
        // the sessions of the dashboard days summed per user for the users with the most server time
        $usr_log_sum_lst = new user_log_sum_list();
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_log_sum_lst->load_sql($sc, $since, def::FALLBACK_USER_LOG_DASHBOARD_TOP);
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $qp = $usr_log_sum_lst->load_sql($sc, $since, def::FALLBACK_USER_LOG_DASHBOARD_TOP);
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_log->del_sql_without_log($sc, [1, 2]);
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $qp = $usr_log->del_sql_without_log($sc, [1, 2]);
        $t->assert_qp($qp, $sc->db_type);
        // the sessions of a user are removed before the user is deleted
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_log->del_sql_by_user_without_log($sc, users::SYSTEM_TEST_ID);
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $qp = $usr_log->del_sql_by_user_without_log($sc, users::SYSTEM_TEST_ID);
        $t->assert_qp($qp, $sc->db_type);

        $t->subheader($ts . 'log count');
        $test_name = 'the first request starts the session with one request';
        $usr_log = $t_usr->user_log();
        $t->assert($test_name, $usr_log->requests, 1);
        $test_name = '... and its milliseconds';
        $t->assert($test_name, $usr_log->request_time, test_users::TV_USER_LOG_MS);
        $test_name = '... but a read request is not counted as update';
        $t->assert($test_name, $usr_log->updated, 0);
        $test_name = '... and its time neither';
        $t->assert($test_name, $usr_log->update_time, 0);
        $test_name = 'a write request is counted as update';
        $usr_log = $t_usr->user_log_open();
        $usr_log->count_request(users::SYSTEM_TEST_ID, $start, $start + test_users::TV_USER_LOG_SEC, true);
        $t->assert($test_name, $usr_log->updated, 1);
        $test_name = '... with its milliseconds';
        $t->assert($test_name, $usr_log->update_time, test_users::TV_USER_LOG_MS);
        $test_name = '... and as the second request of the open session';
        $t->assert($test_name, $usr_log->requests, 2);
        $test_name = '... which keeps the start of the session';
        $first_start = new DateTime(test_users::TV_USER_LOG_START);
        $t->assert($test_name, $usr_log->session_start->getTimestamp(), $first_start->getTimestamp());
        $test_name = '... but moves the end to the second request';
        $t->assert($test_name, $usr_log->session_end->getTimestamp(), (int)($start + test_users::TV_USER_LOG_SEC));
        // negative: a read request of the open session leaves the update count unchanged
        $test_name = 'a read request of the open session is not counted as update';
        $usr_log_read = $t_usr->user_log_open();
        $usr_log_read->count_request(users::SYSTEM_TEST_ID, $start, $start + test_users::TV_USER_LOG_SEC, false);
        $t->assert($test_name, $usr_log_read->updated, 0);

        $t->subheader($ts . 'log session gap');
        // the config.yaml gap is the same as the fallback
        $test_name = 'a request continues the session that has ended within the configured gap';
        $end = (float)strtotime(test_users::TV_USER_LOG_START);
        $since = user_log::since($end);
        $t->assert($test_name, $since->getTimestamp(), (int)$end - def::FALLBACK_USER_LOG_SESSION_GAP);
        $test_name = '... so a session that has ended before the request is not always continued';
        $t->assert_true($test_name, $since->getTimestamp() < (int)$end);

        $t->subheader($ts . 'log switches');
        $usr_normal = $t_usr->user_sys_normal();
        // the counted user is the session user, so no user is loaded
        // not user_sys_normal, because the normal profile is the ip only profile of a user without login
        $usr_login = $t_usr->user_sys_admin();
        $msg_login = new user_message($usr_login); // the request message of a user with login
        $usr_ip = $t_usr->user_ip_loaded();
        $msg_ip = new user_message($usr_ip); // the request message of a user without login
        $all_off = $t_val->config_cache_switch(config_numbers::USER_LOG_ALLOWED_NAMES, false);
        $ip_off = $t_val->config_cache_switch(config_numbers::IP_USER_LOG_ALLOWED_NAMES, false);
        $test_name = 'the user log is written during the setup without config';
        $t->assert_true($test_name, user_log::allowed($msg_login, $usr_login->id(), null));
        $test_name = 'the user log is written if no switch is set';
        $t->assert_true($test_name, user_log::allowed($msg_ip, $usr_ip->id(), $t_val->config_empty()));
        $test_name = 'the user log is not written if it is switched off for all users';
        $t->assert_false($test_name, user_log::allowed($msg_login, $usr_login->id(), $all_off));
        $test_name = 'the user log of a user with login is written if it is switched off for ip users';
        $t->assert_true($test_name, user_log::allowed($msg_login, $usr_login->id(), $ip_off));
        $test_name = 'the user log of an ip user is not written if it is switched off for ip users';
        $t->assert_false($test_name, user_log::allowed($msg_ip, $usr_ip->id(), $ip_off));

        $t->subheader($ts . 'log archive');
        $test_name = 'the archive is outside the web root by default';
        $t->assert($test_name, user_log::archive_dir(null), rtrim(paths::USER_LOG_ARCHIVE, DIRECTORY_SEPARATOR));
        $test_name = 'a pod without the archive path uses the default';
        $t->assert($test_name, user_log::archive_dir($t_val->config_empty()), user_log::archive_dir(null));
        $test_name = 'a session is archived after the default max age';
        $max_age = new DateTime()->modify('-' . def::FALLBACK_USER_LOG_MAX_AGE_DAYS . ' days');
        $diff = abs(user_log::archive_before(null)->getTimestamp() - $max_age->getTimestamp());
        // a few seconds can pass between the two times
        $t->assert_true($test_name, $diff < test_users::TV_USER_LOG_TOLERANCE_SEC);
        $test_name = 'a session that has ended one day within the max age is not archived';
        $young_end = new DateTime()->modify('-' . (def::FALLBACK_USER_LOG_MAX_AGE_DAYS - 1) . ' days');
        $t->assert_false($test_name, $young_end < user_log::archive_before(null));

        $t->subheader($ts . 'data user id');
        $test_name = 'an own pod call is counted for the browsing user';
        $t->assert($test_name, $usr_normal->data_user_id(users::SYSTEM_TEST_PARTNER_ID, true), users::SYSTEM_TEST_PARTNER_ID);
        $test_name = 'an admin may name the data user';
        $t->assert($test_name, $t->usr_admin->data_user_id(users::SYSTEM_TEST_PARTNER_ID), users::SYSTEM_TEST_PARTNER_ID);
        $test_name = 'a normal user cannot name another data user';
        $t->assert($test_name, $usr_normal->data_user_id(users::SYSTEM_TEST_PARTNER_ID), users::SYSTEM_TEST_NORMAL_ID);
        $test_name = 'a request without data user is counted for the session user';
        $t->assert($test_name, $usr_normal->data_user_id(0, true), users::SYSTEM_TEST_NORMAL_ID);


        $t->subheader($ts . 'sql read');
        $usr_test = new user();
        $t->assert_sql_by_id($sc, $usr_test);
        $t->assert_sql_by_name($sc, $usr_test);
        $this->assert_sql_by_email($t, $db_con, $usr_test);
        $this->assert_sql_by_name_or_email($t, $db_con, $usr_test);
        $this->assert_sql_by_ip($t, $db_con, $usr_test);
        $this->assert_sql_by_profile($t, $db_con, $usr_test);

        $t->subheader($ts . 'sql write insert');
        $usr_ip = $t_usr->user_filled($t);
        $t->assert_sql_insert($sc, $usr_ip, [sql_type::LOG]);
        $usr_test = $t_usr->user_sys_test();
        $t->assert_sql_insert($sc, $usr_test, [sql_type::LOG]);

        // negative: without a requesting user on the message the sql creation is refused
        // with a reported message instead of a fatal, because the change log needs the user
        $test_name = 'insert sql creation without a requesting user reports the missing user';
        $msg_no_usr = new user_message();
        $usr_ip->sql_insert($sc, $msg_no_usr, new sql_type_list([sql_type::LOG]));
        $t->assert_false($test_name, $msg_no_usr->is_ok());

        $t->subheader($ts . 'sql write update');
        $usr_changed = $usr_test->cloned(users::SYSTEM_TEST_PARTNER_NAME);
        $usr_changed->created = $usr_test->created;
        $t->assert_sql_update($sc, $usr_changed, $usr_test, [sql_type::LOG]);
        $t->assert_sql_update($sc, $usr_changed, $usr_test);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $usr_test, [sql_type::LOG]);

        $test_usr_list = new user_list($usr_test);
        // TODO Prio 2 include all value tables
        $this->assert_sql_count_changes($t, $db_con, $test_usr_list);
        $this->assert_sql_count_user_rows($t, $db_con, $test_usr_list);


        $t->subheader($ts . 'api');

        $usr_test = $t_usr->user_sys_test();
        $t->assert_api($usr_test);


        $t->subheader($ts . 'im- and export');
        $json_file = 'unit/user/user_import.json';
        $t->assert_json_file(new user(), $json_file, $t->usr_admin);

        // the activation key is the account activation secret, so it must never be serialized:
        // a user with the key set keeps it on the backend object but neither the export json nor
        // the api json may contain it, otherwise an export, backup or api read leaks a working key
        $usr_key = $t_usr->user_filled($t);
        $test_name = 'the activation key stays available on the backend object';
        $t->assert_true($test_name, $usr_key->activation_key == users::TEST_USER_ACTIVATION_KEY);
        $test_name = 'the export json does not leak the activation key';
        $t->assert_false($test_name, key_exists(json_fields::ACTIVATION_KEY, $usr_key->export_json($msg)));
        $test_name = 'the api json does not leak the activation key';
        $usr_key_api = json_decode($usr_key->api_json(), true);
        $t->assert_false($test_name, key_exists(json_fields::ACTIVATION_KEY, $usr_key_api));

        // the public core api json is shown to any requester e.g. as the user page title,
        // so it carries the name (which is public anyway as the author of every change log
        // entry) but never the email or any other personal field
        $test_name = 'the public core api json contains the user name';
        $usr_core_api = json_decode($usr_key->api_json_core([], $msg), true);
        $t->assert($test_name, $usr_core_api[json_fields::NAME] ?? '', users::TEST_USER_NAME);
        $test_name = 'the public core api json does not contain the email';
        $t->assert_false($test_name, key_exists(json_fields::EMAIL, $usr_core_api));
        // negative: an ip user has no name, and the api message drops the empty name
        $test_name = 'the public core api json of an ip user has no name';
        $usr_ip_core = $t_usr->user_ip();
        $usr_ip_core_api = json_decode($usr_ip_core->api_json_core([], $msg), true);
        $t->assert_false($test_name, key_exists(json_fields::NAME, $usr_ip_core_api));
        $msg->reset();


        $t->subheader($ts . 'change permission');

        // a user without login has the ip only profile, an admin has a login
        $test_name = 'a user with the ip only profile is an ip user';
        $usr_ip = $t_usr->user_filled($t);
        $t->assert_true($test_name, $usr_ip->is_ip_user());
        $test_name = 'the admin user is not an ip user';
        $t->assert_false($test_name, $t->usr_admin->is_ip_user());

        // by default config.yaml does not permit the changes of an ip user
        $test_name = 'the ip user is blocked while the pod does not permit the changes of an ip user';
        $t->assert_true($test_name, $usr_ip->is_blocked());
        $test_name = 'a user with a login is never blocked by the ip user permission';
        $t->assert_false($test_name, $t->usr_admin->is_blocked());

        // the default profile of a new user object is the ip user profile
        // so any system function that creates a user object without loading it from the database
        // needs to set the system profile to be able to change the database (e.g. sql_db->load_db_code_link_file)
        $test_name = 'a new user object without profile is an ip user';
        $t->assert_true($test_name, (new user())->is_ip_user());
        $test_name = 'the virtual system user is never an ip user';
        $t->assert_false($test_name, user::system()->is_ip_user());
        $test_name = 'the virtual system user of e.g. the initial database setup is not blocked';
        $t->assert_false($test_name, user::system()->is_blocked());

        // the database version check on the program start runs before
        // the user profiles can be loaded from the database,
        // so the virtual system user must be able to change data with an empty profile list
        global $sys;
        $usr_ip_loaded = $t_usr->user_ip_loaded();
        $usr_pro_loaded = $sys->typ_lst->usr_pro;
        $sys->typ_lst->usr_pro = new user_profile_list();
        $test_name = 'the virtual system user can change data before the user profiles are loaded';
        $t->assert_true($test_name, user::system()->is_unique());
        $test_name = 'a user with the ip profile cannot change data before the user profiles are loaded';
        $t->assert_false($test_name, $usr_ip_loaded->is_unique());
        $sys->typ_lst->usr_pro = $usr_pro_loaded;


        $t->subheader($ts . 'data user permission');

        // an api request may pass a data user id (url_var::USER) to load another user's data, but a
        // normal or ip user must not use it to read a foreign user's private data (idor); only an
        // admin or the system user may switch, otherwise the session user's own data is loaded
        $usr_attacker = new user();
        $usr_attacker->id = users::TEST_USER_ID;
        $usr_attacker->profile_id = $sys->typ_lst->usr_pro->id(user_profiles::NORMAL);
        $test_name = 'a normal user cannot load the system user via the data user parameter';
        $t->assert($test_name, $usr_attacker->data_user(users::SYSTEM_ID, $msg)->id(), users::TEST_USER_ID);
        $test_name = 'the data user parameter is ignored for the session user own id';
        $t->assert($test_name, $usr_attacker->data_user(users::TEST_USER_ID, $msg)->id(), users::TEST_USER_ID);
        // the own pod flag is set by the api entry scripts via server_guard::from_own_pod, never
        // from a request value, so an external caller can never combine it with the user parameter
        $test_name = 'without the own pod flag the data user request of a normal user stays blocked';
        $t->assert($test_name, $usr_attacker->data_user(users::SYSTEM_ID, $msg, false)->id(), users::TEST_USER_ID);
        $test_name = 'the own pod flag without a requested user keeps the session user';
        $t->assert($test_name, $usr_attacker->data_user(0, $msg, true)->id(), users::TEST_USER_ID);


        $t->subheader($ts . 'sandbox usage');

        // the sandbox usage flag is part of every user row fetch,
        // so that the request routing can serve cached pages without an additional query
        $test_name = 'the sandbox usage flag is always part of the user row fetch';
        $t->assert_true($test_name, in_array(user_db::FLD_USES_SANDBOX, user_db::FLD_NAMES));
        $test_name = 'a user with sandbox changes is mapped to use the sandbox';
        $usr = new user();
        $usr->row_mapper($t_usr->to_db_row($t_usr->sandbox_user()), $msg);
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'a user without sandbox changes is mapped to not use the sandbox';
        $usr = new user();
        $usr->row_mapper($t_usr->to_db_row($t_usr->non_sandbox_user()), $msg);
        $t->assert_false($test_name, $usr->uses_sandbox);
        $test_name = 'a user row of a not yet upgraded pod does not use the sandbox';
        $usr = new user();
        $db_row = $t_usr->to_db_row($t_usr->sandbox_user());
        unset($db_row[user_db::FLD_USES_SANDBOX]);
        $usr->row_mapper($db_row, $msg);
        $t->assert_false($test_name, $usr->uses_sandbox);

        // adding a sandbox row switches the user to the sandbox usage (see sandbox->add_usr_cfg);
        // without a database id the flag is only changed in memory e.g. for this unit test
        $test_name = 'adding a sandbox row switches the user to sandbox usage';
        $usr = new user();
        $msg = new user_message($t->usr_admin);
        $usr->set_uses_sandbox($msg);
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'switching to sandbox usage reports no problem';
        $t->assert_true($test_name, $msg->is_ok());
        $test_name = 'a user already using the sandbox is not saved again';
        $usr = $t_usr->sandbox_user();
        $usr->set_uses_sandbox($msg);
        $t->assert_true($test_name, $usr->uses_sandbox and $msg->is_ok());

        // the flag is part of the api json, so that an admin can switch it via the frontend
        $test_name = 'the sandbox usage flag reaches the frontend via the api json';
        $api_json = json_decode($t_usr->sandbox_user()->api_json(), true);
        $t->assert_true($test_name, $api_json[json_fields::USES_SANDBOX]);
        $test_name = 'the sandbox usage flag is mapped from the api json';
        $usr = new user();
        $msg = new user_message($t->usr_admin);
        $usr->api_mapper([json_fields::USES_SANDBOX => 1], $msg);
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'an api json without the flag maps to not use the sandbox';
        $usr = new user();
        $usr->api_mapper([], $msg);
        $t->assert_false($test_name, $usr->uses_sandbox);


        $t->subheader($ts . 'logon secrets preserved on save');

        // the api json never carries the logon secrets, so in a user rebuilt from it the
        // password and the activation key are null, meaning "not loaded", never "clear";
        // a save of such a user e.g. via set_uses_sandbox must keep the stored values
        $msg = new user_message($t->usr_admin);
        $db_usr = $t_usr->sandbox_user();
        $db_usr->set_password_hash(user::DUMMY_PW_HASH);
        $db_usr->activation_key = 'dummy activation key hash for unit test';
        $upd_usr = $t_usr->sandbox_user();
        $upd_usr->uses_sandbox = true;
        $db_usr->uses_sandbox = false;
        $chg_lst = $upd_usr->db_fields_changed($db_usr, $msg);
        $test_name = 'the sandbox usage switch is saved';
        $t->assert_true($test_name, $chg_lst->has_name(user_db::FLD_USES_SANDBOX));
        $test_name = 'a not loaded password never overwrites the stored hash';
        $t->assert_false($test_name, $chg_lst->has_name(user_db::FLD_PASSWORD));
        $test_name = 'a not loaded activation key never overwrites the stored key';
        $t->assert_false($test_name, $chg_lst->has_name(user_db::FLD_ACTIVATION_KEY));

        // a real password change (a new hash is set) is still detected and written
        $upd_usr->set_password_hash('changed dummy password hash for unit test');
        $chg_lst = $upd_usr->db_fields_changed($db_usr, $msg);
        $test_name = 'a new password hash is saved';
        $t->assert_true($test_name, $chg_lst->has_name(user_db::FLD_PASSWORD));
        // negative: the stored hash set again is no change
        $test_name = 'the stored password hash set again is not saved';
        $same_pw_usr = $t_usr->sandbox_user();
        $same_pw_usr->set_password_hash(user::DUMMY_PW_HASH);
        $chg_lst_same = $same_pw_usr->db_fields_changed($db_usr, $msg);
        $t->assert_false($test_name, $chg_lst_same->has_name(user_db::FLD_PASSWORD));

        // a used activation key is cleared with '' (see frontend::action_login_activate),
        // so the clearing is still detected and written
        $upd_usr->activation_key = '';
        $chg_lst = $upd_usr->db_fields_changed($db_usr, $msg);
        $test_name = 'clearing a used activation key is saved';
        $t->assert_true($test_name, $chg_lst->has_name(user_db::FLD_ACTIVATION_KEY));


        $t->subheader($ts . 'type and status preserved on save');

        // api_mapper keeps a missing type and status null (not specified), so a save of a
        // json-born user keeps the stored values instead of resetting them to guest and active
        $msg = new user_message($t->usr_admin);
        $json_usr = new user();
        $json_usr->api_mapper([json_fields::USES_SANDBOX => 1], $msg);
        $test_name = 'a missing type in the api json maps to null';
        $t->assert_true($test_name, $json_usr->type_id === null);
        $test_name = 'a missing status in the api json maps to null';
        $t->assert_true($test_name, $json_usr->status_id === null);
        $db_usr = $t_usr->non_sandbox_user();
        $db_usr->type_id = 2;
        $db_usr->status_id = 3;
        $chg_lst = $json_usr->db_fields_changed($db_usr, $msg);
        $test_name = 'a not specified type never overwrites the stored type';
        $t->assert_false($test_name, $chg_lst->has_name(user_db::FLD_TYPE_ID));
        $test_name = 'a not specified status never overwrites the stored status';
        $t->assert_false($test_name, $chg_lst->has_name(user_db::FLD_STATUS));

        // an explicit type and status change is still detected and written
        // and the status survives the api round trip (sent under json_fields::STATUS)
        $api_json = [json_fields::TYPE => 4, json_fields::STATUS => 5];
        $upd_usr = new user();
        $upd_usr->api_mapper($api_json, $msg);
        $test_name = 'the status is mapped from the api json';
        $t->assert_true($test_name, $upd_usr->status_id == 5);
        $chg_lst = $upd_usr->db_fields_changed($db_usr, $msg);
        $test_name = 'a changed type is saved';
        $t->assert_true($test_name, $chg_lst->has_name(user_db::FLD_TYPE_ID));
        $test_name = 'a changed status is saved';
        $t->assert_true($test_name, $chg_lst->has_name(user_db::FLD_STATUS));


        $t->subheader($ts . 'api mapper');

        $test_name = 'the user mapper sets the name';
        $json_usr = new user();
        $json_usr->api_mapper(json_decode($t_usr->user_sys_normal()->api_json(), true), $msg);
        $t->assert($test_name, $json_usr->name(), users::SYSTEM_TEST_NORMAL_NAME);
        $msg->reset();

        // negative: the profile is a critical field, so a json without it never keeps a higher profile
        $test_name = 'the user mapper sets the normal profile if the json has none';
        $api_json = json_decode($t_usr->user_sys_admin()->api_json(), true);
        unset($api_json[json_fields::PROFILE_ID]);
        $json_usr = new user();
        $json_usr->api_mapper($api_json, $msg);
        $t->assert($test_name, $json_usr->profile_id, user_profiles::NORMAL_ID);
        $msg->reset();

        $test_name = 'the message mapper sets the user';
        $map_msg = new user_message();
        $map_msg->api_mapper([json_fields::USER => json_decode($t->usr1->api_json(), true)]);
        $t->assert($test_name, $map_msg->usr?->name(), $t->usr1->name());

        // negative: a json without a user leaves the message without a user
        $test_name = 'the message mapper leaves a missing user empty';
        $map_msg = new user_message();
        $map_msg->api_mapper([]);
        $t->assert_null($test_name, $map_msg->usr?->name());


        $t->subheader($ts . 'api json array');

        // the email is sent to the user himself or an admin
        $test_name = 'email';
        $usr_arr = $t_usr->user_filled($t);
        $vars = $usr_arr->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::EMAIL] ?? null, users::TEST_USER_MAIL);
        $msg->reset();

        // negative: the activation key and the ip address are never sent over the api
        $test_name = 'no activation key';
        $usr_arr = $t_usr->user_filled($t);
        $vars = $usr_arr->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::ACTIVATION_KEY, $vars);
        $t->assert_key_not_exists($test_name . ' and no ip', json_fields::IP_ADDR, $vars);
        $msg->reset();

        // negative: an empty field is not sent at all
        $test_name = 'no empty email';
        $usr_arr = $t_usr->user_add();
        $vars = $usr_arr->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::EMAIL, $vars);
        $msg->reset();

        // the last used view of a user is sent by its id
        $test_name = 'view id';
        $usr_arr = $t_usr->user_filled($t);
        $trm = new test_terms($t)->term();
        $vars = $usr_arr->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::VIEW_ID] ?? null, views::START_ID);
        $t->assert($test_name . ' term', $vars[json_fields::TERM_ID], $trm->id());
        $t->assert($test_name . ' source', $vars[json_fields::SOURCE_ID] ?? null, sources::SIB_ID);
        $msg->reset();

        // negative: with the objects included the last used view is nested instead of its id
        $test_name = 'nested view';
        $usr_arr = $t_usr->user_filled($t);
        $typ_lst = [api_types::INCL_VIEWS, api_types::INCL_TERMS, api_types::INCL_SOURCES];
        $vars = $usr_arr->api_json_array($typ_lst, $msg);
        $t->assert($test_name, $vars[json_fields::VIEW][json_fields::ID] ?? null, views::START_ID);
        $t->assert_key_not_exists($test_name . ' without id', json_fields::VIEW_ID, $vars);
        $t->assert_key_not_exists($test_name . ' term without id', json_fields::TERM_ID, $vars);
        $t->assert_key_not_exists($test_name . ' source without id', json_fields::SOURCE_ID, $vars);
        $msg->reset();

        // the right level of a user profile is part of the api message
        $test_name = 'profile right level';
        $t_typ = new test_types($t);
        $usr_prf = $t_typ->user_profile();
        $vars = $usr_prf->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::RIGHT_LEVEL] ?? null, user_profiles::NORMAL_LEVEL);
        $msg->reset();

        // negative: a profile without a right level sends an empty right level
        $test_name = 'profile without right level';
        $usr_prf = $t_typ->user_profile();
        $usr_prf->right_level = null;
        $vars = $usr_prf->api_json_array([], $msg);
        $t->assert_null($test_name, $vars[json_fields::RIGHT_LEVEL] ?? null);
        $msg->reset();

        // the summed sessions of a user are sent with the user and the number of requests
        $test_name = 'log sum requests';
        $sum = $t_usr->log_sum();
        $vars = $sum->api_json_array();
        $t->assert($test_name, $vars[json_fields::REQUESTS] ?? null, test_users::TV_USER_LOG_REQUESTS);
        $t->assert($test_name . ' user', $vars[json_fields::ID] ?? null, users::SYSTEM_TEST_ID);
        $msg->reset();

        // negative: a sum row without a user sends no user and no requests
        $test_name = 'log sum without user';
        $sum = $t_usr->log_sum();
        $sum->row_mapper([]);
        $vars = $sum->api_json_array();
        $t->assert($test_name, $vars[json_fields::ID] ?? null, 0);
        $t->assert($test_name . ' and no requests', $vars[json_fields::REQUESTS] ?? null, 0);
        $msg->reset();

        // the list of the summed sessions sends one entry per user
        $test_name = 'log sum list';
        $sum_lst = $t_usr->log_sum_list();
        $vars = $sum_lst->api_json_array();
        $t->assert($test_name, $vars[0][json_fields::ID] ?? null, users::SYSTEM_TEST_ID);
        $msg->reset();

        // negative: an empty list of summed sessions sends an empty list
        $test_name = 'empty log sum list';
        $sum_lst = $t_usr->log_sum_list_empty();
        $vars = $sum_lst->api_json_array();
        $t->assert($test_name, $vars, []);
        $msg->reset();


        $t->subheader($ts . 'last used ip saved on login');

        // a logged in user connecting from a new ip must get its last used ip updated in the user
        // table; needs_ip_update decides this so get() can persist the new ip via save_user
        $ip_usr = $t_usr->user_ip_loaded();
        $test_name = 'a new ip of a logged in user is saved';
        $t->assert_true($test_name, $ip_usr->needs_ip_update(users::TEST_IP));

        $test_name = 'the unchanged ip is not saved again';
        $t->assert_false($test_name, $ip_usr->needs_ip_update(users::TEST_USER_IP));

        $test_name = 'an empty request ip never overwrites the stored ip';
        $t->assert_false($test_name, $ip_usr->needs_ip_update(''));

        // an anonymous user without a database id (id 0) is not persisted by ip
        $test_name = 'a user without a database id is not updated by ip';
        $t->assert_false($test_name, $t_usr->user_ip()->needs_ip_update(users::TEST_IP));


        $t->subheader($ts . 'profile privilege escalation');

        // the profile is a critical field that must not be trusted from the request json; api_mapper
        // maps it faithfully (a round trip must preserve the real profile) and the escalation is
        // refused before the database write in enforce_profile_privilege (called by save_user)
        $admin_profile_id = $sys->typ_lst->usr_pro->id(user_profiles::ADMIN);
        $test_name = 'api_mapper maps the requested profile faithfully';
        $usr = new user();
        $msg = new user_message($t->usr_normal);
        $usr->api_mapper([json_fields::PROFILE_ID => $admin_profile_id], $msg);
        $t->assert($test_name, $usr->profile_id, $admin_profile_id);

        // a normal user cannot raise a user record from the normal to the admin profile
        $test_name = 'a normal user cannot set the admin profile';
        $allowed = $usr->enforce_profile_privilege(
            $admin_profile_id, user_profiles::NORMAL_ID, $t->usr_normal, $msg);
        $t->assert($test_name, $allowed, user_profiles::NORMAL_ID);
        $test_name = 'the profile escalation attempt is reported to the user';
        $t->assert_text_contains($test_name, $msg->all_message_text(), 'cannot be updated due to missing privileges');

        // an admin may legitimately set the admin profile
        $test_name = 'an admin can set the admin profile';
        $msg = new user_message($t->usr_admin);
        $allowed = $usr->enforce_profile_privilege(
            $admin_profile_id, user_profiles::NORMAL_ID, $t->usr_admin, $msg);
        $t->assert($test_name, $allowed, $admin_profile_id);

        // fill takes the flag from the given object, because false also means not yet set
        $test_name = 'fill sets the sandbox usage from the given user';
        $usr = new user();
        $usr->fill($t_usr->sandbox_user(), $t->usr_admin);
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'fill does not unset the sandbox usage';
        $usr = $t_usr->sandbox_user();
        $usr->fill($t_usr->non_sandbox_user(), $t->usr_admin);
        $t->assert_true($test_name, $usr->uses_sandbox);

        // a changed flag is detected as a difference e.g. to select the fields to save
        $test_name = 'a changed sandbox usage is detected as a diff';
        $msg = new user_message($t->usr_admin);
        $t->assert_false($test_name, $t_usr->sandbox_user()->no_diff($t_usr->non_sandbox_user(), $msg));
        $test_name = 'an unchanged sandbox usage is no diff';
        $t->assert_true($test_name, $t_usr->sandbox_user()->no_diff($t_usr->sandbox_user(), $msg));

        // the flag can be moved to another pod via im- and export
        $test_name = 'the sandbox usage flag is mapped from an import json';
        $usr = new user();
        $msg = new user_message($t->usr_admin);
        $usr->import_mapper([json_fields::USES_SANDBOX => true], $msg);
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'an import json without the flag maps to not use the sandbox';
        $usr = new user();
        $usr->import_mapper([], $msg);
        $t->assert_false($test_name, $usr->uses_sandbox);
        $test_name = 'the sandbox usage flag is part of the export json';
        $t->assert_true($test_name, $t_usr->sandbox_user()->export_json($msg)[json_fields::USES_SANDBOX] ?? false);
        $test_name = 'the default false is not exported';
        $t->assert_false(
            $test_name, key_exists(json_fields::USES_SANDBOX, $t_usr->non_sandbox_user()->export_json($msg)));


        $t->subheader($ts . 'code id privilege');

        // a permission of a user is always based on the profile and never on the code id,
        // because the code id only selects a user e.g. the system test user 1 or 2
        // (see docs/llm/state-and-messages.md)
        $test_name = 'a user with the test profile is a system test user';
        $usr_sys_test = $t_usr->user_sys_test();
        $t->assert_true($test_name, $usr_sys_test->is_system_test());
        $test_name = 'a system test user can set the code id of an object';
        $t->assert_true($test_name, $usr_sys_test->can_set_code_id());
        $test_name = 'a user with the normal profile is not a system test user';
        $t->assert_false($test_name, $t->usr_normal->is_system_test());
        $test_name = 'a normal user cannot set the code id of an object';
        $t->assert_false($test_name, $t->usr_normal->can_set_code_id());


        $t->subheader($ts . 'activation key at rest');

        // a password reset / activation key is stored only as a sha256 hash with a short validity,
        // so a database read cannot recover a working key; the cleartext is compared via hash_equals
        $key = users::TEST_USER_ACTIVATION_KEY;
        $usr_key = new user();
        $usr_key->set_activation_key($key);
        $usr_key->db_now = new DateTime();
        $test_name = 'the stored key is the sha256 hash, not the cleartext';
        $t->assert($test_name, $usr_key->activation_key, hash('sha256', $key));
        $test_name = 'the cleartext key itself is never stored';
        $t->assert_false($test_name, $usr_key->activation_key == $key);
        $test_name = 'the correct key is accepted while valid';
        $t->assert_true($test_name, $usr_key->activation_key_valid($key));
        $test_name = 'a wrong key is rejected';
        $t->assert_false($test_name, $usr_key->activation_key_valid('000000'));
        // move the validity end into the past to check that an expired key is refused
        $timeout = new DateTime();
        $timeout->modify('-1 hour');
        $usr_key->activation_timeout = $timeout;
        $test_name = 'an expired key is no longer reported as active';
        $t->assert_false($test_name, $usr_key->has_active_activation_key());
        $test_name = 'the correct key is rejected once expired';
        $t->assert_false($test_name, $usr_key->activation_key_valid($key));


        $t->subheader($ts . 'activation key expiry without db_now');

        // a loaded user never has db_now set (it is not among the queried fields), so the expiry check
        // must fall back to the real now; otherwise an expired reset link stays a permanent takeover token
        $usr_load = new user();
        $usr_load->set_activation_key($key);
        $test_name = 'a fresh key is active even when db_now is not set';
        $t->assert_true($test_name, $usr_load->has_active_activation_key());
        $expired = new DateTime();
        $expired->modify('-1 hour');
        $usr_load->activation_timeout = $expired;
        $test_name = 'an expired key is inactive even when db_now is not set';
        $t->assert_false($test_name, $usr_load->has_active_activation_key());


        $t->subheader($ts . 'signup confirmation');

        // the key of the signup mail stays valid for a day, the reset key only for an hour
        $test_name = 'the signup key is still valid after the validity of a reset key';
        $usr_sgn_key = new user();
        $usr_sgn_key->set_activation_key($key, user::SIGNUP_KEY_VALIDITY);
        $reset_end = new DateTime();
        $reset_end->modify(user::ACTIVATION_KEY_VALIDITY);
        $t->assert_true($test_name, $usr_sgn_key->activation_timeout > $reset_end);
        $test_name = '... but the reset key is not';
        $t->assert_false($test_name, $usr_load->activation_timeout > $reset_end);

        // only the signup system user may raise a new account to the reserved name and the confirmed email
        $usr_sgn = $t_usr->user_signup();
        $test_name = 'the user with the signup profile is the signup user';
        $t->assert_true($test_name, $usr_sgn->is_signup());
        $usr_nrm = $t_usr->user_sys_normal();
        $test_name = 'a normal user is not the signup user';
        $t->assert_false($test_name, $usr_nrm->is_signup());
        $test_name = 'the signup user may confirm the email of an account';
        $t->assert_true($test_name, $usr_sgn->can_set_profile($sys->typ_lst->usr_pro->id(user_profiles::EMAIL)));
        $test_name = '... but may not make it an admin';
        $t->assert_false($test_name, $usr_sgn->can_set_profile($sys->typ_lst->usr_pro->id(user_profiles::ADMIN)));

        $test_name = 'the signup reserves the name of a new account';
        $usr_new = $t_usr->user_signed_up(user_profiles::IP_ONLY);
        $usr_new->raise_signup_profile(user_profiles::NAME_ONLY, $usr_sgn, $msg);
        $t->assert($test_name, $usr_new->profile_id, $sys->typ_lst->usr_pro->id(user_profiles::NAME_ONLY));
        $msg->reset();
        $test_name = 'the activation link confirms the email';
        $usr_new->raise_signup_profile(user_profiles::EMAIL, $usr_sgn, $msg);
        $t->assert($test_name, $usr_new->profile_id, $sys->typ_lst->usr_pro->id(user_profiles::EMAIL));
        $msg->reset();
        $test_name = 'a second signup step never lowers the confirmed email';
        $usr_new->raise_signup_profile(user_profiles::NAME_ONLY, $usr_sgn, $msg);
        $t->assert($test_name, $usr_new->profile_id, $sys->typ_lst->usr_pro->id(user_profiles::EMAIL));
        $msg->reset();
        $test_name = 'the activation link of an admin keeps the admin profile';
        $usr_adm = $t_usr->user_sys_admin();
        $usr_adm->raise_signup_profile(user_profiles::EMAIL, $usr_sgn, $msg);
        $t->assert($test_name, $usr_adm->profile_id, $sys->typ_lst->usr_pro->id(user_profiles::ADMIN));
        $msg->reset();
        $test_name = 'a normal user cannot confirm the email of another account';
        $usr_new = $t_usr->user_signed_up(user_profiles::NAME_ONLY);
        $t->assert_false($test_name, $usr_new->raise_signup_profile(user_profiles::EMAIL, $usr_nrm, $msg));
        $test_name = '... which is reported';
        $t->assert_text_contains($test_name, $msg->text(), users::SYSTEM_TEST_NORMAL_NAME);
        $test_name = '... and keeps the reserved name';
        $t->assert($test_name, $usr_new->profile_id, $sys->typ_lst->usr_pro->id(user_profiles::NAME_ONLY));
        $msg->reset();


        $t->subheader($ts . 'diff message');

        // the diff message tells a human which fields differ e.g. to explain a rejected update
        $test_name = 'equal users have no diff message';
        $msg = $t_usr->sandbox_user()->diff_msg($t_usr->sandbox_user());
        $t->assert($test_name, $msg->all_message_text(), '');
        $test_name = 'the diff message names the changed field';
        $msg = $t_usr->sandbox_user()->diff_msg($t_usr->non_sandbox_user());
        $t->assert_text_contains($test_name, $msg->all_message_text(), user_db::FLD_USES_SANDBOX);
        $test_name = 'the diff message contains the email change';
        $usr_chg = $t_usr->sandbox_user();
        $usr_chg->email = users::TEST_USER_MAIL_UPDATED;
        $msg = $usr_chg->diff_msg($t_usr->sandbox_user());
        $t->assert_text_contains($test_name, $msg->all_message_text(), users::TEST_USER_MAIL_UPDATED);
        $test_name = 'a changed password is never part of the diff message';
        $usr_chg = $t_usr->sandbox_user();
        $usr_chg->set_password_hash(users::TEST_USER_PASSWORD_FIX_HASH);
        $msg = $usr_chg->diff_msg($t_usr->sandbox_user());
        $t->assert_false($test_name, str_contains(
            $msg->all_message_text(), users::TEST_USER_PASSWORD_FIX_HASH));

    }

    /*
     * assert testing function only used for the user object
     */

    /**
     * similar to assert_load_sql of the testing class but select one user based on the email
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     */
    private function assert_sql_by_email(test_cleanup $t, sql_db $db_con, object $usr_obj): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_email($db_con->sql_creator(), 'System test', $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_email($db_con->sql_creator(), 'System test', $usr_obj::class);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * similar to assert_load_sql of the testing class but select one user based on the name or email
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     */
    private function assert_sql_by_name_or_email(test_cleanup $t, sql_db $db_con, object $usr_obj): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_name_or_email($db_con->sql_creator(), 'System test name', 'System test email', $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_name_or_email($db_con->sql_creator(), 'System test name', 'System test email', $usr_obj::class);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * similar to assert_load_sql of the testing class but select first user with the given ip address
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     */
    private function assert_sql_by_ip(test_cleanup $t, sql_db $db_con, object $usr_obj): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_ip($db_con->sql_creator(), 'System test', $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_ip($db_con->sql_creator(), 'System test', $usr_obj::class);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * similar to assert_load_sql of the testing class but select the first user with the given profile
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     */
    private function assert_sql_by_profile(test_cleanup $t, sql_db $db_con, object $usr_obj): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_profile($db_con->sql_creator(), 1, $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_profile($db_con->sql_creator(), 1, $usr_obj::class);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the SQL statements to count the changes by a user
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     */
    private function assert_sql_count_changes(test_cleanup $t, sql_db $db_con, object $usr_obj): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_count_changes($db_con->sql_creator());
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_count_changes($db_con->sql_creator());
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the SQL statements to count all sandbox rows one user has
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param user_list $usr_lst the user list object that creates the query
     */
    private function assert_sql_count_user_rows(test_cleanup $t, sql_db $db_con, user_list $usr_lst): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_lst->load_sql_count_user_rows($db_con->sql_creator(), users::SYSTEM_TEST_ID);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_lst->load_sql_count_user_rows($db_con->sql_creator(), users::SYSTEM_TEST_ID);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}