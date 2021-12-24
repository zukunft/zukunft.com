<?php

/*

  test/unit/value.php - unit testing of the VALUE functions
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

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class value_unit_tests
{
    const TEST_NAME = 'value->';
    const PATH = 'db/value/';
    const FILE_EXT = '.sql';
    const FILE_MYSQL = '_mysql';

    public testing $test;
    public value $val;
    public sql_db $db_con;

    function run(testing $t)
    {

        global $usr;

        // init
        $this->db_con = new sql_db();
        $db_con = $this->db_con;
        $this->test = $t;

        $t->header('Unit tests of the value class (src/main/php/model/value/value.php)');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $t->subheader('Database query creation tests');

        // sql to load a user specific value by id
        $val = new value($usr);
        $val->id = 1;
        $this->assert_sql_all($val);

        // sql to load a user specific value by phrase group id
        $val->reset($usr);
        $val->grp->id = 2;
        $this->assert_sql_all($val);

        // sql to load a user specific value by phrase group and time id
        $val->reset($usr);
        $val->grp->id = 2;
        $val->set_time_id(4);
        $this->assert_sql_all($val);

        // sql to load a user specific value by phrase list and time id
        $val->reset($usr);
        $val->phr_lst = (new phrase_list_unit_tests)->get_phrase_list();
        $val->set_time_id(4);
        $this->assert_sql_all($val);

        // ... and the related default value
        $this->assert_sql_all($val, true);

        /*
         * im- and export tests
         */

        $t->subheader('Im- and Export tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/value/speed_of_light.json'), true);
        $val = new value($usr);
        $val->import_obj($json_in, false);
        $json_ex = json_decode(json_encode($val->export_obj(false)), true);
        $result = json_is_similar($json_in, $json_ex);
        $t->assert('view->import check name', $result, true);

        $t->header('Unit tests of the value time series class (src/main/php/model/value/value_time_series.php)');

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $t->subheader('Database query creation tests');

        // sql to load a user specific time series by id
        $vts = new value_time_series($usr);
        $vts->id = 1;
        $this->assert_sql_all($vts);

        // ... and the related default time series
        $this->assert_sql_all($vts, true);

        // sql to load a user specific time series by phrase group id
        $vts->reset($usr);
        $vts->grp->id = 2;
        $this->assert_sql_all($vts);

    }

    /**
     * test the SQL statement creation for a value in all SQL dialect
     * and check if the statement name is unique
     *
     * @param user_sandbox_value $val filled with an id to be able to load
     * @param bool $get_std true if the default value for all users should be loaded
     * @return void
     */
    private function assert_sql_all(user_sandbox_value $val, bool $get_std = false)
    {
        // check the PostgreSQL query syntax
        $this->assert_pg($val, $get_std);

        // ... and check if the prepared sql name is unique
        if ($get_std) {
            $sql_name = $val->load_standard_sql($this->db_con, get_class($val))->name;
        } else {
            $sql_name = $val->load_sql($this->db_con)->name;
        }
        $this->test->assert_sql_name_unique($sql_name);

        // check the MySQL query syntax
        $this->assert_mysql($val, $get_std);
    }

    /**
     * test the SQL statement creation for a value using PostgreSQL
     *
     * @param user_sandbox_value $val filled with an id to be able to load
     * @param bool $get_std true if the default value for all users should be loaded
     * @return void
     */
    private function assert_pg(user_sandbox_value $val, bool $get_std = false)
    {
        $this->db_con->db_type = sql_db::POSTGRES;
        $this->assert_sql($val, $get_std);
    }

    /**
     * test the SQL statement creation for a value using MySQL
     *
     * @param user_sandbox_value $val filled with an id to be able to load
     * @param bool $get_std true if the default value for all users should be loaded
     * @return void
     */
    private function assert_mysql(user_sandbox_value $val, bool $get_std = false)
    {
        $this->db_con->db_type = sql_db::MYSQL;
        $this->assert_sql($val, $get_std, self::FILE_MYSQL);
    }

    /**
     * test the SQL statement creation for a value
     *
     * @param user_sandbox_value $val filled with an id to be able to load
     * @param bool $get_std true if the default value for all users should be loaded
     * @param string $dialect if not PostgreSQL the name of the SQL dialect
     * @return void
     */
    private function assert_sql(user_sandbox_value $val, bool $get_std = false, string $dialect = '')
    {
        if ($get_std) {
            $created_qp = $val->load_standard_sql($this->db_con);
        } else {
            $created_qp = $val->load_sql($this->db_con);
        }
        $expected_sql = $this->test->file(self::PATH . $created_qp->name . $dialect . self::FILE_EXT);
        $this->test->assert(
            self::TEST_NAME . $created_qp->name . $dialect,
            $this->test->trim($created_qp->sql),
            $this->test->trim($expected_sql)
        );

    }

}