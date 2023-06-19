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

namespace test;

use api\user_api;
use cfg\phrase_type;
use model\sql_db;
use model\user;

class user_unit_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'user->';
        $t->resource_path = 'db/user/';
        $json_file = 'unit/user/user_import.json';
        $usr->set_id(1);

        $t->header('Unit tests of the user class (src/main/php/model/user/user.php)');


        $t->subheader('SQL statement tests');

        $test_usr = new user();
        $t->assert_sql_by_id($db_con, $test_usr);
        $t->assert_sql_by_name($db_con, $test_usr);
        $this->assert_load_sql_email($t, $db_con, $test_usr);
        $this->assert_load_sql_name_or_email($t, $db_con, $test_usr);
        $this->assert_load_sql_ip($t, $db_con, $test_usr);
        $this->assert_load_sql_profile($t, $db_con, $test_usr);


        $t->subheader('API unit tests');

        $test_usr = $t->dummy_user();
        $t->assert_api($test_usr);


        $t->subheader('Im- and Export tests');

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
     * @return bool true if all tests are fine
     */
    function assert_load_sql_email(test_cleanup $t, sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_email($db_con, 'System test', $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_email($db_con, 'System test', $usr_obj::class);
            $result = $t->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql of the testing class but select one user based on the name or email
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     * @return bool true if all tests are fine
     */
    function assert_load_sql_name_or_email(test_cleanup $t, sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_name_or_email($db_con, 'System test name', 'System test email', $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_name_or_email($db_con, 'System test name', 'System test email', $usr_obj::class);
            $result = $t->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql of the testing class but select first user with the given ip address
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     * @return bool true if all tests are fine
     */
    function assert_load_sql_ip(test_cleanup $t, sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_ip($db_con, 'System test', $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_ip($db_con, 'System test', $usr_obj::class);
            $result = $t->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql of the testing class but select the first user with the given profile
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     * @return bool true if all tests are fine
     */
    function assert_load_sql_profile(test_cleanup $t, sql_db $db_con, object $usr_obj): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_profile($db_con, 1, $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_profile($db_con, 1, $usr_obj::class);
            $result = $t->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

}