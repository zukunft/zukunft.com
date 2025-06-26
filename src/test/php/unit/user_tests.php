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

namespace unit;

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\system\job_time;
use cfg\user\user;
use cfg\user\user_list;
use shared\const\users;
use test\test_cleanup;

class user_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'user->';
        $t->resource_path = 'db/user/';
        $t->usr_admin = $t->user_sys_admin();


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
        $usr_ip = $t->user_ip();
        $t->assert_sql_insert($sc, $usr_ip, [sql_type::LOG]);
        $usr_test = $t->user_sys_test();
        $t->assert_sql_insert($sc, $usr_test, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $usr_changed = $usr_test->cloned(users::SYSTEM_TEST_PARTNER_NAME);
        $t->assert_sql_update($sc, $usr_changed, $usr_test, [sql_type::LOG]);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $usr_test, [sql_type::LOG]);

        $test_usr_list = new user_list($usr_test);
        // TODO include all value tables
        $this->assert_sql_count_changes($t, $db_con, $test_usr_list);


        $t->subheader($ts . 'api');

        $usr_test = $t->user_sys_test();
        $t->assert_api($usr_test);


        $t->subheader($ts . 'im- and export');
        $json_file = 'unit/user/user_import.json';
        $t->assert_json_file(new user(), $json_file);

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