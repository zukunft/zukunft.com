<?php

/*

    test/unit/user_list.php - unit testing of the user list functions
    -----------------------
  

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
use cfg\user\user;
use cfg\user\user_list;
use shared\const\users;
use shared\library;
use test\test_cleanup;

class user_list_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'user_list->';
        $t->resource_path = 'db/user/';

        // start the test section (ts)
        $ts = 'unit user list ';
        $t->header($ts);

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        // sql to load a list of value by ids
        $test_name = 'load users by ids';
        $usr_lst = new user_list($usr);
        $t->assert_sql_by_ids($test_name, $sc, $usr_lst);
        $t->assert_sql_by_code_id($sc, $usr_lst);
        $this->assert_sql_by_profile_and_higher($t, $db_con, $usr_lst);


        $t->subheader($ts . 'im- and export');

        // $t->assert_json_file(new value_list($usr), $json_file);


        $t->subheader($ts . 'html frontend');

        //$trp_lst = $t->dummy_value_list();
        //$t->assert_api_to_dsp($trp_lst, new value_list_dsp());

    }

    /**
     * check the SQL statements creation to get user by profile level
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param user_list $usr_lst the user sandbox object e.g. a result
     * @return void true if all tests are fine
     */
    private function assert_sql_by_profile_and_higher(test_cleanup $t, sql_db $db_con, user_list $usr_lst): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_lst->load_sql_by_profile_and_higher($db_con->sql_creator(), users::RIGHT_LEVEL_SYSTEM_TEST);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_lst->load_sql_by_profile_and_higher($db_con->sql_creator(), users::RIGHT_LEVEL_SYSTEM_TEST);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}