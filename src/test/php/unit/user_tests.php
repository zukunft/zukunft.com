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
use cfg\job_time;
use cfg\user;
use cfg\user_list;
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

        $t->header('Unit tests of the user class (src/main/php/model/user/user.php)');


        $t->subheader('Job time SQL setup statements');
        $test_usr = new user();
        $t->assert_sql_table_create($test_usr);
        $t->assert_sql_index_create($test_usr);
        $t->assert_sql_foreign_key_create($test_usr);


        $t->subheader('SQL statement tests');

        $test_usr = new user();
        $t->assert_sql_by_id($sc, $test_usr);
        $t->assert_sql_by_name($sc, $test_usr);
        $this->assert_sql_by_email($t, $db_con, $test_usr);
        $this->assert_sql_by_name_or_email($t, $db_con, $test_usr);
        $this->assert_sql_by_ip($t, $db_con, $test_usr);
        $this->assert_sql_by_profile($t, $db_con, $test_usr);

        $test_usr_list = new user_list($test_usr);
        // TODO include all value tables
        $this->assert_sql_count_changes($t, $db_con, $test_usr_list);


        $t->subheader('API unit tests');

        $test_usr = $t->user_sys_test();
        $t->assert_api($test_usr);


        $t->subheader('Im- and Export tests');
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