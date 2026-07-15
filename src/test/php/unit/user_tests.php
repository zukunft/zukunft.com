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

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile_list;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class user_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_usr = new test_users($t);
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

        $t->subheader($ts . 'sql write update');
        $usr_changed = $usr_test->cloned(users::SYSTEM_TEST_PARTNER_NAME);
        $usr_changed->created = $usr_test->created;
        $t->assert_sql_update($sc, $usr_changed, $usr_test, [sql_type::LOG]);
        $t->assert_sql_update($sc, $usr_changed, $usr_test);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $usr_test, [sql_type::LOG]);

        $test_usr_list = new user_list($usr_test);
        // TODO include all value tables
        $this->assert_sql_count_changes($t, $db_con, $test_usr_list);


        $t->subheader($ts . 'api');

        $usr_test = $t_usr->user_sys_test();
        $t->assert_api($usr_test);


        $t->subheader($ts . 'im- and export');
        $json_file = 'unit/user/user_import.json';
        $t->assert_json_file(new user(), $json_file, $t->usr_admin);


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


        $t->subheader($ts . 'sandbox usage');

        // the sandbox usage flag is part of every user row fetch,
        // so that the request routing can serve cached pages without an additional query
        $test_name = 'the sandbox usage flag is always part of the user row fetch';
        $t->assert_true($test_name, in_array(user_db::FLD_USES_SANDBOX, user_db::FLD_NAMES));
        $test_name = 'a user with sandbox changes is mapped to use the sandbox';
        $usr = new user();
        $usr->row_mapper($t_usr->to_db_row($t_usr->sandbox_user()));
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'a user without sandbox changes is mapped to not use the sandbox';
        $usr = new user();
        $usr->row_mapper($t_usr->to_db_row($t_usr->non_sandbox_user()));
        $t->assert_false($test_name, $usr->uses_sandbox);
        $test_name = 'a user row of a not yet upgraded pod does not use the sandbox';
        $usr = new user();
        $db_row = $t_usr->to_db_row($t_usr->sandbox_user());
        unset($db_row[user_db::FLD_USES_SANDBOX]);
        $usr->row_mapper($db_row);
        $t->assert_false($test_name, $usr->uses_sandbox);

        // adding a sandbox row switches the user to the sandbox usage (see sandbox->add_usr_cfg);
        // without a database id the flag is only changed in memory e.g. for this unit test
        $test_name = 'adding a sandbox row switches the user to sandbox usage';
        $usr = new user();
        $usr_msg = new user_message($t->usr_admin);
        $usr->set_uses_sandbox($usr_msg);
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'switching to sandbox usage reports no problem';
        $t->assert_true($test_name, $usr_msg->is_ok());
        $test_name = 'a user already using the sandbox is not saved again';
        $usr = $t_usr->sandbox_user();
        $usr->set_uses_sandbox($usr_msg);
        $t->assert_true($test_name, $usr->uses_sandbox and $usr_msg->is_ok());

        // the flag is part of the api json, so that an admin can switch it via the frontend
        $test_name = 'the sandbox usage flag reaches the frontend via the api json';
        $api_json = json_decode($t_usr->sandbox_user()->api_json(), true);
        $t->assert_true($test_name, $api_json[json_fields::USES_SANDBOX]);
        $test_name = 'the sandbox usage flag is mapped from the api json';
        $usr = new user();
        $usr_msg = new user_message($t->usr_admin);
        $usr->api_mapper([json_fields::USES_SANDBOX => 1], $usr_msg);
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'an api json without the flag maps to not use the sandbox';
        $usr = new user();
        $usr->api_mapper([], $usr_msg);
        $t->assert_false($test_name, $usr->uses_sandbox);

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
        $usr_msg = new user_message($t->usr_admin);
        $t->assert_false($test_name, $t_usr->sandbox_user()->no_diff($t_usr->non_sandbox_user(), $usr_msg));
        $test_name = 'an unchanged sandbox usage is no diff';
        $t->assert_true($test_name, $t_usr->sandbox_user()->no_diff($t_usr->sandbox_user(), $usr_msg));

        // the flag can be moved to another pod via im- and export
        $test_name = 'the sandbox usage flag is mapped from an import json';
        $usr = new user();
        $usr_msg = new user_message($t->usr_admin);
        $usr->import_mapper([json_fields::USES_SANDBOX => true], $usr_msg);
        $t->assert_true($test_name, $usr->uses_sandbox);
        $test_name = 'an import json without the flag maps to not use the sandbox';
        $usr = new user();
        $usr->import_mapper([], $usr_msg);
        $t->assert_false($test_name, $usr->uses_sandbox);
        $test_name = 'the sandbox usage flag is part of the export json';
        $t->assert_true($test_name, $t_usr->sandbox_user()->export_json()[json_fields::USES_SANDBOX] ?? false);
        $test_name = 'the default false is not exported';
        $t->assert_false(
            $test_name, key_exists(json_fields::USES_SANDBOX, $t_usr->non_sandbox_user()->export_json()));


        $t->subheader($ts . 'diff message');

        // the diff message tells a human which fields differ e.g. to explain a rejected update
        $test_name = 'equal users have no diff message';
        $usr_msg = $t_usr->sandbox_user()->diff_msg($t_usr->sandbox_user());
        $t->assert($test_name, $usr_msg->all_message_text(), '');
        $test_name = 'the diff message names the changed field';
        $usr_msg = $t_usr->sandbox_user()->diff_msg($t_usr->non_sandbox_user());
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), user_db::FLD_USES_SANDBOX);
        $test_name = 'the diff message contains the email change';
        $usr_chg = $t_usr->sandbox_user();
        $usr_chg->email = users::TEST_USER_MAIL_UPDATED;
        $usr_msg = $usr_chg->diff_msg($t_usr->sandbox_user());
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), users::TEST_USER_MAIL_UPDATED);
        $test_name = 'a changed password is never part of the diff message';
        $usr_chg = $t_usr->sandbox_user();
        $usr_chg->set_password_hash(users::TEST_USER_PASSWORD_FIX_HASH);
        $usr_msg = $usr_chg->diff_msg($t_usr->sandbox_user());
        $t->assert_false($test_name, str_contains(
            $usr_msg->all_message_text(), users::TEST_USER_PASSWORD_FIX_HASH));

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

}